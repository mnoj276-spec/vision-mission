<?php

namespace App\Domains\Scrapers\Commands;

use App\Models\ScrapingSource;
use App\Models\ExtractedNotification;
use App\Domains\Scrapers\Services\HybridScrapingEngine;
use App\Domains\Scrapers\Drivers\ScraperDriverManager;
use App\Domains\Extraction\Services\Parsers\PdfParser;
use App\Domains\Extraction\Services\Parsers\HtmlParser;
use App\Domains\Extraction\Services\Parsers\DocumentParserService;
use App\Domains\Extraction\Services\OCRService;
use App\Domains\Extraction\Services\AiStructuringService;
use App\Domains\Extraction\Services\ValidationService;
use App\Domains\Scrapers\Services\FingerprintService;
use App\Domains\Jobs\Repositories\Contracts\JobRepositoryInterface;
use App\Services\UrlSecurity;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use ReflectionClass;

class TestPipelineCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraper:test-pipeline 
                            {--source= : Scraping Source ID to test} 
                            {--url= : Direct URL of a document or web page to test the pipeline} 
                            {--use-ai : Call live AI APIs for structuring (caution: costs API credits)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Executes a comprehensive live check of every ingestion pipeline step (Reachability, Discovery, Download, Parsing, OCR, AI Extraction, Validation, Duplicate Checks, DB saving).';

    /**
     * Execute the console command.
     */
    public function handle(
        HybridScrapingEngine $hybridEngine,
        ScraperDriverManager $driverManager,
        PdfParser $pdfParser,
        HtmlParser $htmlParser,
        DocumentParserService $docParser,
        OCRService $ocrService,
        AiStructuringService $aiStructuringService,
        ValidationService $validationService,
        FingerprintService $fingerprintService,
        JobRepositoryInterface $jobRepo
    ): int {
        $sourceId = $this->option('source');
        $inputUrl = $this->option('url');
        $useAi = $this->option('use-ai');

        $this->info("================================================================================");
        $this->info("                    INGESTION PIPELINE MULTI-STEP VERIFIER                     ");
        $this->info("================================================================================");

        // Resolve Scraping Source context
        $source = null;
        if ($sourceId) {
            $source = ScrapingSource::find($sourceId);
            if (!$source) {
                $this->error("Scraping source with ID {$sourceId} not found.");
                return Command::FAILURE;
            }
        } else {
            // Find first active source with a default config as fallback context
            $source = ScrapingSource::where('is_active', true)->first();
            if (!$source) {
                $this->warn("No active scraping sources found. Creating a temporary testing context.");
                $source = ScrapingSource::create([
                    'name' => 'Temporary Ingestion Test Source',
                    'source_url' => 'https://upsc.gov.in/recruitment/active-jobs-feed',
                    'source_type' => 'html',
                    'selectors_config' => [
                        'driver' => 'upsc',
                        'default_category_id' => 1,
                        'default_department_id' => 1,
                        'default_state_id' => 1,
                        'default_qualification_id' => 1
                    ],
                    'is_active' => false,
                ]);
            }
        }

        $this->comment("Target Context: [ID: {$source->id}] {$source->name}");
        $this->line("Target Source URL: {$source->source_url}");
        $this->line("AI Mode: " . ($useAi ? "Live AI Services" : "Local AI Simulator (Deterministic)"));
        $this->info("--------------------------------------------------------------------------------");

        $steps = [];
        $selectedDocUrl = null;
        $tempFilePath = null;
        $fileExtension = null;
        $extractedRawText = '';
        $isScanned = false;
        $parserUsed = '';
        $structuredData = [];
        $validationResult = [];
        $isDuplicate = false;
        $dupReason = '';
        $saveRecordId = null;

        // =====================================================================
        // STEP 1: Website Reachable?
        // =====================================================================
        $this->line("Testing Step 1: Website Reachable?");
        $step1Url = $inputUrl ?: $source->source_url;
        $startTime = microtime(true);
        try {
            $response = Http::timeout(15)->get($step1Url);
            $duration = round(microtime(true) - $startTime, 2);
            if ($response->successful()) {
                $this->info("  [✓] Website Reachable! Status code: {$response->status()} (took {$duration}s)");
                $steps['Reachable'] = [true, "Status {$response->status()} ({$duration}s)"];
            } else {
                $this->error("  [✗] Website Returned Error Code: {$response->status()} (took {$duration}s)");
                $steps['Reachable'] = [false, "Status {$response->status()}"];
            }
        } catch (\Exception $e) {
            $this->error("  [✗] Website Connection Failed: " . $e->getMessage());
            $steps['Reachable'] = [false, "Connection Failed"];
        }

        // =====================================================================
        // STEP 2: Notification Found?
        // =====================================================================
        $this->line("\nTesting Step 2: Notification Found?");
        if ($inputUrl) {
            $selectedDocUrl = $inputUrl;
            $this->info("  [✓] Notification link provided directly via --url parameter: {$selectedDocUrl}");
            $steps['NotificationFound'] = [true, "Provided: " . basename($selectedDocUrl)];
        } else {
            try {
                $this->line("  Running scraper page fetch & parse...");
                $html = $hybridEngine->fetch($source);
                $driver = $driverManager->getDriverFor($source);
                $rawItems = $driver->parse($html, $source);

                if (!empty($rawItems)) {
                    // Try to find the first item that has a valid URL
                    foreach ($rawItems as $item) {
                        $potentialLink = $item['official_link'] ?? $item['apply_link'] ?? null;
                        if ($potentialLink && filter_var($potentialLink, FILTER_VALIDATE_URL)) {
                            $selectedDocUrl = $potentialLink;
                            break;
                        }
                    }

                    if (!$selectedDocUrl) {
                        // Default to the source URL itself if items have no direct links
                        $selectedDocUrl = $source->source_url;
                    }

                    $this->info("  [✓] Notification Found! Parsed " . count($rawItems) . " item(s). Target link: {$selectedDocUrl}");
                    $steps['NotificationFound'] = [true, "Found (" . count($rawItems) . " items)"];
                } else {
                    $this->warn("  [!] Scraper parse completed successfully, but returned 0 items.");
                    $steps['NotificationFound'] = [false, "No items found"];
                }
            } catch (\Exception $e) {
                $this->error("  [✗] Scraper execution failed: " . $e->getMessage());
                $steps['NotificationFound'] = [false, "Scraper Failed"];
            }
        }

        // =====================================================================
        // STEP 3: Download Successful?
        // =====================================================================
        $this->line("\nTesting Step 3: Download Successful?");
        
        $downloadUrl = $selectedDocUrl;
        if (!$downloadUrl) {
            $this->error("  [✗] Download bypassed: No target notification URL discovered.");
            $steps['DownloadSuccessful'] = [false, "No URL"];
        } else {
            try {
                $this->line("  Downloading target document from: {$downloadUrl}");
                $originalName = basename(parse_url($downloadUrl, PHP_URL_PATH) ?: 'notification');
                $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION)) ?: 'html';

                // SSRF restriction check (if checking live URLs, except testing localhost/reserved URLs is blocked)
                if (!UrlSecurity::isSafeUrl($downloadUrl)) {
                    throw new \Exception("SSRF Guard Blocked access to unsafe URL: {$downloadUrl}");
                }

                $storedPath = 'extractions/' . Str::uuid() . '.' . $fileExtension;
                $tempPath = Storage::disk('local')->path($storedPath);

                if (!\Illuminate\Support\Facades\File::isDirectory(dirname($tempPath))) {
                    \Illuminate\Support\Facades\File::makeDirectory(dirname($tempPath), 0755, true, true);
                }

                $client = new \GuzzleHttp\Client();
                $response = $client->request('GET', $downloadUrl, [
                    'stream' => true,
                    'timeout' => 20,
                    'allow_redirects' => [
                        'max' => 5,
                        'protocols' => ['http', 'https'],
                        'on_redirect' => function(\Psr\Http\Message\RequestInterface $req, \Psr\Http\Message\ResponseInterface $res, \Psr\Http\Message\UriInterface $uri) {
                            $redirectUrl = (string)$uri;
                            if (!UrlSecurity::isSafeUrl($redirectUrl)) {
                                throw new \Exception("SSRF Block: Redirected to unsafe domain: " . $redirectUrl);
                            }
                        }
                    ]
                ]);

                if ($response->getStatusCode() !== 200) {
                    throw new \Exception("HTTP download failed with status " . $response->getStatusCode());
                }

                $body = $response->getBody();
                $outStream = fopen($tempPath, 'wb');
                if ($outStream === false) {
                    throw new \Exception("Failed to open file for writing: " . $tempPath);
                }

                $totalBytes = 0;
                $maxBytes = 20 * 1024 * 1024; // 20MB limit
                while (!$body->eof()) {
                    $chunk = $body->read(8192);
                    $totalBytes += strlen($chunk);
                    if ($totalBytes > $maxBytes) {
                        fclose($outStream);
                        unlink($tempPath);
                        throw new \Exception("File size limit of 20MB exceeded.");
                    }
                    fwrite($outStream, $chunk);
                }
                fclose($outStream);

                $tempFilePath = $tempPath;
                $sizeKb = round($totalBytes / 1024, 2);
                $this->info("  [✓] Download Successful! Saved file (Size: {$sizeKb} KB, Extension: {$fileExtension})");
                $steps['DownloadSuccessful'] = [true, "Success ({$sizeKb} KB)"];
            } catch (\Exception $e) {
                $this->error("  [✗] Download Failed: " . $e->getMessage());
                $steps['DownloadSuccessful'] = [false, "Failed: " . substr($e->getMessage(), 0, 40)];
            }
        }

        // =====================================================================
        // STEP 4: Parser Successful?
        // =====================================================================
        $this->line("\nTesting Step 4: Parser Successful?");
        if (!$tempFilePath || !file_exists($tempFilePath)) {
            $this->error("  [✗] Parser skipped: No valid file available.");
            $steps['ParserSuccessful'] = [false, "No file to parse"];
        } else {
            try {
                $this->line("  Running extraction parser for extension: {$fileExtension}");
                $rawText = '';
                $tables = [];
                $headers = [];

                if ($fileExtension === 'html' || $fileExtension === 'htm') {
                    $parserUsed = 'HtmlParser';
                    $res = $htmlParser->extractStructured($tempFilePath);
                    $rawText = $res['text'] ?? '';
                    $tables = $res['tables'] ?? [];
                    $headers = $res['headers'] ?? [];
                } elseif ($fileExtension === 'pdf') {
                    $parserUsed = 'PdfParser';
                    $res = $pdfParser->extractStructured($tempFilePath);
                    $rawText = $res['text'] ?? '';
                    $tables = $res['tables'] ?? [];
                    $isScanned = $res['is_scanned'] ?? false;
                } elseif (in_array($fileExtension, ['docx', 'xlsx', 'doc', 'xls'])) {
                    $parserUsed = 'DocumentParserService';
                    $res = $docParser->extractStructured($tempFilePath, $fileExtension);
                    $rawText = $res['text'] ?? '';
                    $tables = $res['tables'] ?? [];
                    $headers = $res['headers'] ?? [];
                } else {
                    $this->error("  [✗] Parser Failed: Unsupported extension ($fileExtension)");
                    $steps['ParserSuccessful'] = [false, "Unsupported extension"];
                }

                $extractedRawText = $rawText;
                $extractedCharCount = strlen($extractedRawText);

                if ($extractedCharCount > 0) {
                    $this->info("  [✓] Parser Successful! Extracted {$extractedCharCount} characters via {$parserUsed}.");
                    $steps['ParserSuccessful'] = [true, "Success (parsed {$extractedCharCount} chars)"];
                } else {
                    $this->warn("  [!] Parser returned empty text. Fallback required.");
                    $steps['ParserSuccessful'] = [false, "Parsed empty text"];
                }
            } catch (\Exception $e) {
                $this->error("  [✗] Parser Failed: " . $e->getMessage());
                $steps['ParserSuccessful'] = [false, "Parser Failed"];
            }
        }

        // =====================================================================
        // STEP 5: OCR Needed?
        // =====================================================================
        $this->line("\nTesting Step 5: OCR Needed?");
        $avgCharsPerPage = 0;
        $ocrNeeded = false;
        
        if (!$tempFilePath) {
            $ocrNeeded = false;
        } elseif ($fileExtension === 'pdf') {
            $pageCount = isset($res['page_count']) ? $res['page_count'] : 1;
            $avgCharsPerPage = $pageCount > 0 ? strlen($extractedRawText) / $pageCount : 0;
            $ocrNeeded = $isScanned || $avgCharsPerPage < 150;
        } elseif (empty(trim($extractedRawText))) {
            $ocrNeeded = true;
        }

        if ($ocrNeeded) {
            $this->info("  [✓] OCR Needed (Page is scanned or text is very short: avg {$avgCharsPerPage} chars/page).");
            $steps['OcrNeeded'] = [true, "Yes (Scanned/Short)"];
        } else {
            $this->line("  [ ] OCR Not Needed (Standard text-based PDF/document containing sufficient characters).");
            $steps['OcrNeeded'] = [true, "No (Text Sufficient)"];
        }

        // =====================================================================
        // STEP 6: OCR Successful?
        // =====================================================================
        $this->line("\nTesting Step 6: OCR Successful?");
        if ($ocrNeeded) {
            try {
                $this->line("  Running OCRService on file...");
                $ocrText = $ocrService->extractText($tempFilePath, $fileExtension);
                
                if (!empty(trim($ocrText))) {
                    $extractedRawText = $ocrText;
                    $ocrCharCount = strlen($extractedRawText);
                    $this->info("  [✓] OCR Successful! Extracted {$ocrCharCount} characters from scanned document.");
                    $steps['OcrSuccessful'] = [true, "Success ({$ocrCharCount} chars)"];
                } else {
                    $this->error("  [✗] OCR Failed: OCR engine returned empty results.");
                    $steps['OcrSuccessful'] = [false, "Empty OCR text"];
                }
            } catch (\Exception $e) {
                $this->error("  [✗] OCR Engine Error: " . $e->getMessage());
                $steps['OcrSuccessful'] = [false, "OCR failed: " . substr($e->getMessage(), 0, 40)];
            }
        } else {
            $this->line("  [ ] OCR skipped (Not needed).");
            $steps['OcrSuccessful'] = [true, "Not Needed"];
        }

        // =====================================================================
        // STEP 7: Fields Extracted?
        // =====================================================================
        $this->line("\nTesting Step 7: Fields Extracted?");
        if (empty(trim($extractedRawText))) {
            $this->error("  [✗] AI extraction skipped: Raw text content is completely empty.");
            $steps['FieldsExtracted'] = [false, "Empty input text"];
        } else {
            try {
                $this->line("  Structuring text via AiStructuringService...");
                
                // Call AI or local simulator
                if ($useAi) {
                    $structuredData = $aiStructuringService->structureWithContext($extractedRawText, $tables ?? [], $headers ?? []);
                } else {
                    $reflection = new ReflectionClass(get_class($aiStructuringService));
                    $simulator = $reflection->getMethod('runDeterministicStructuringSimulatorWithContext');
                    $simulator->setAccessible(true);
                    $structuredData = $simulator->invoke($aiStructuringService, $extractedRawText, $tables ?? [], $headers ?? []);
                }

                // Append parsing metadata
                $structuredData['_metadata'] = [
                    'parser_used' => $parserUsed . ($ocrNeeded ? ' + OCR' : ''),
                    'parse_duration_seconds' => 0.5,
                    'text_length' => strlen($extractedRawText),
                    'table_count' => count($tables ?? []),
                    'is_scanned' => $isScanned || $ocrNeeded,
                ];

                $fieldsFound = [];
                foreach ($structuredData as $k => $v) {
                    if ($v !== null && $k !== '_metadata' && $v !== '') {
                        $fieldsFound[] = $k;
                    }
                }

                $this->info("  [✓] Fields Extracted! Fields found: " . implode(', ', $fieldsFound));
                $steps['FieldsExtracted'] = [true, "Extracted " . count($fieldsFound) . " fields"];
            } catch (\Exception $e) {
                $this->error("  [✗] Structuring failed: " . $e->getMessage());
                $steps['FieldsExtracted'] = [false, "AI Structuring Failed"];
            }
        }

        // =====================================================================
        // STEP 8: Validation Passed?
        // =====================================================================
        $this->line("\nTesting Step 8: Validation Passed?");
        if (empty($structuredData)) {
            $this->error("  [✗] Validation skipped: No structured data available.");
            $steps['ValidationPassed'] = [false, "No data to validate"];
        } else {
            try {
                $this->line("  Running ValidationService schemas...");
                $validationResult = $validationService->validate($structuredData);

                if ($validationResult['isValid']) {
                    $this->info("  [✓] Validation Passed! No schema errors found.");
                    $steps['ValidationPassed'] = [true, "Passed"];
                } else {
                    $this->warn("  [!] Validation Failed: Schema validation errors detected.");
                    foreach ($validationResult['errors'] as $field => $errorsList) {
                        $this->line("    - <fg=red>{$field}</>: " . implode(', ', $errorsList));
                    }
                    $steps['ValidationPassed'] = [false, "Schema failed"];
                }
            } catch (\Exception $e) {
                $this->error("  [✗] ValidationService engine crashed: " . $e->getMessage());
                $steps['ValidationPassed'] = [false, "Validation crashed"];
            }
        }

        // =====================================================================
        // STEP 9: Duplicate?
        // =====================================================================
        $this->line("\nTesting Step 9: Duplicate?");
        if (empty($structuredData) || empty($structuredData['title'])) {
            $this->error("  [✗] Duplicate check skipped: Title is missing.");
            $steps['Duplicate'] = [false, "Missing title"];
        } else {
            try {
                $this->line("  Running fingerprint, fuzzy, and acronym lookbacks...");
                
                $defaultDept = $source->selectors_config['default_department_id'] ?? 1;
                $deadlineStr = $structuredData['important_dates']['last_date_to_apply'] ?? '';

                $incomingFingerprint = $fingerprintService->generate([
                    'title'         => $structuredData['title'],
                    'department_id' => $defaultDept,
                    'source_url'    => $selectedDocUrl ?: $source->source_url,
                    'publish_date'  => $deadlineStr,
                ]);

                $masterByFingerprint = $jobRepo->findByFingerprint($incomingFingerprint);
                if ($masterByFingerprint) {
                    $isDuplicate = true;
                    $dupReason = 'Exact fingerprint collision';
                } else {
                    $recentPosts = $jobRepo->findFuzzyDuplicates($defaultDept);
                    $fuzzyHit = $fingerprintService->isFuzzyDuplicate($structuredData['title'], $recentPosts);
                    if ($fuzzyHit) {
                        $isDuplicate = true;
                        $dupReason = "Fuzzy duplicate (score: {$fuzzyHit['score']}%)";
                    } else {
                        $variantHit = $fingerprintService->detectTitleVariant($structuredData['title'], $recentPosts);
                        if ($variantHit) {
                            $isDuplicate = true;
                            $dupReason = "Title variant (score: {$variantHit['score']}% variant: '{$variantHit['variant']}')";
                        }
                    }
                }

                if ($isDuplicate) {
                    $this->warn("  [!] Duplicate Item Detected: Skipped ingestion (Reason: {$dupReason})");
                    $steps['Duplicate'] = [true, "Duplicate: " . substr($dupReason, 0, 30)];
                } else {
                    $this->info("  [✓] Unique Item! The entry is unique and can be safely ingested.");
                    $steps['Duplicate'] = [true, "Unique"];
                }
            } catch (\Exception $e) {
                $this->error("  [✗] Duplicate check failed: " . $e->getMessage());
                $steps['Duplicate'] = [false, "Duplicate Check Failed"];
            }
        }

        // =====================================================================
        // STEP 10: Saved?
        // =====================================================================
        $this->line("\nTesting Step 10: Saved?");
        try {
            $this->line("  Simulating database transaction insert...");
            
            DB::beginTransaction();
            
            $notificationRecord = ExtractedNotification::create([
                'file_path'          => $tempFilePath ?: '',
                'original_filename'  => $downloadUrl ? basename($downloadUrl) : '',
                'file_type'          => $fileExtension ?: 'html',
                'raw_text'           => $extractedRawText ?: '',
                'extracted_data'     => $structuredData ?: null,
                'validation_status'  => isset($validationResult['isValid']) && $validationResult['isValid'] ? 'valid' : 'invalid',
                'validation_errors'  => isset($validationResult['errors']) ? $validationResult['errors'] : null,
                'status'             => 'processed',
            ]);

            $saveRecordId = $notificationRecord->id;
            
            // Rollback the transaction to keep the database clean
            DB::rollBack();

            $this->info("  [✓] Database Ingestion Successful! Created ExtractedNotification record ID: {$saveRecordId} (transaction rolled back safely).");
            $steps['Saved'] = [true, "Success (Rolled back)"];
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("  [✗] Database Ingestion Failed: " . $e->getMessage());
            $steps['Saved'] = [false, "Save Failed"];
        }



        // =====================================================================
        // FINAL PIPELINE VERIFICATION REPORT
        // =====================================================================
        $this->info("\n================================================================================");
        $this->info("                        PIPELINE CHECKLIST REPORT                              ");
        $this->info("================================================================================");
        
        $checklist = [
            'Reachable'          => 'Website Reachable?',
            'NotificationFound'  => 'Notification Found?',
            'DownloadSuccessful' => 'Download Successful?',
            'ParserSuccessful'   => 'Parser Successful?',
            'OcrNeeded'          => 'OCR Needed?',
            'OcrSuccessful'      => 'OCR Successful?',
            'FieldsExtracted'    => 'Fields Extracted?',
            'ValidationPassed'   => 'Validation Passed?',
            'Duplicate'          => 'Duplicate Check?',
            'Saved'              => 'Database Saved?'
        ];

        foreach ($checklist as $key => $title) {
            $status = $steps[$key] ?? [false, 'Bypassed'];
            $indicator = $status[0] ? "<info>[✓]</info>" : "<error>[✗]</error>";
            $this->line("  {$indicator} " . str_pad($title, 25) . " : " . $status[1]);
        }

        $this->info("================================================================================");

        return Command::SUCCESS;
    }


}
