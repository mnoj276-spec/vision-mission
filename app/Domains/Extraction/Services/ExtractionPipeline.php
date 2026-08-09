<?php

namespace App\Domains\Extraction\Services;

use App\Models\ExtractedNotification;
use App\Domains\Extraction\Services\Parsers\PdfParser;
use App\Domains\Extraction\Services\Parsers\DocumentParserService;
use App\Domains\Extraction\Services\Parsers\CsvParser;
use App\Domains\Extraction\Services\Parsers\XmlParser;
use App\Domains\Extraction\Services\Parsers\HtmlParser;
use App\Domains\Extraction\Services\Parsers\ImageParser;
use App\Domains\Extraction\Services\Parsers\ZipParser;
use Illuminate\Support\Facades\Log;

class ExtractionPipeline
{
    public function __construct(
        protected PdfParser $pdfParser,
        protected DocumentParserService $docParser,
        protected OCRService $ocrService,
        protected AiStructuringService $aiStructuringService,
        protected ValidationService $validationService,
        protected CsvParser $csvParser,
        protected XmlParser $xmlParser,
        protected HtmlParser $htmlParser,
        protected ImageParser $imageParser,
        protected ZipParser $zipParser
    ) {}

    /**
     * Run the extraction pipeline on a pending notification.
     *
     * @param ExtractedNotification $notification
     * @return ExtractedNotification
     */
    public function process(ExtractedNotification $notification): ExtractedNotification
    {
        $notification->update(['status' => 'processing']);
        $startTime = microtime(true);

        try {
            $filePath = $notification->file_path;
            if (empty($filePath) || !file_exists($filePath)) {
                throw new \Exception("File path '{$filePath}' is empty or does not exist.");
            }

            // 1. File Type Detection (Magic Bytes)
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $filePath);
            finfo_close($finfo);

            $extension = $this->getExtensionFromMime($mime, $filePath);
            if (empty($extension)) {
                $extension = strtolower($notification->file_type);
            }

            Log::info("Universal Extraction Engine: Parsing file {$filePath} as {$extension} (MIME: {$mime})");

            $parsedData = $this->parseFile($filePath, $extension);
            $rawText = $parsedData['rawText'];
            $tables = $parsedData['tables'];
            $headers = $parsedData['headers'];
            $parserUsed = $parsedData['parserUsed'];
            $isScanned = $parsedData['isScanned'];

            if (empty(trim($rawText))) {
                throw new \Exception("Extraction failed: could not parse text from file.");
            }

            // Calculate duration
            $duration = microtime(true) - $startTime;

            // 4. AI Structuring with Context
            Log::info("Universal Extraction Engine: Calling AI Structuring Service with Context.");
            $structuredData = $this->aiStructuringService->structureWithContext($rawText, $tables, $headers);

            // Add parser accuracy and parsing metadata
            $structuredData['_metadata'] = [
                'parser_used' => $parserUsed,
                'parse_duration_seconds' => round($duration, 4),
                'text_length' => strlen($rawText),
                'table_count' => count($tables),
                'is_scanned' => $isScanned,
            ];

            // 5. Validation
            Log::info("Universal Extraction Engine: Validating structured data.");
            $validationResult = $this->validationService->validate($structuredData);

            // 6. Update Storage Record
            $notification->update([
                'raw_text'          => $rawText,
                'extracted_data'    => $structuredData,
                'validation_status' => $validationResult['isValid'] ? 'valid' : 'invalid',
                'validation_errors' => $validationResult['errors'] ?: null,
                'status'            => 'processed',
            ]);

            Log::info("Universal Extraction Engine: Finished processing record #{$notification->id}. Valid: " . ($validationResult['isValid'] ? 'yes' : 'no'));

            return $notification;

        } catch (\Throwable $e) {
            Log::error("Extraction Pipeline failed for record #{$notification->id}: " . $e->getMessage(), [
                'exception' => $e->getTraceAsString(),
            ]);

            $notification->update([
                'status'            => 'failed',
                'validation_status' => 'invalid',
                'validation_errors' => ['pipeline' => [$e->getMessage()]],
            ]);

            return $notification;
        }
    }

    protected function parseFile(string $filePath, string $extension): array
    {
        $rawText = '';
        $tables = [];
        $headers = [];
        $parserUsed = '';
        $isScanned = false;
        $scannedPages = [];

        if ($extension === 'zip') {
            $parserUsed = 'ZipParser';
            $extractedFiles = $this->zipParser->extractFiles($filePath);
            foreach ($extractedFiles as $file) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file);
                finfo_close($finfo);
                $ext = $this->getExtensionFromMime($mime, $file);
                if (empty($ext)) continue;

                try {
                    $subRes = $this->parseFile($file, $ext);
                    $rawText .= "\n\n" . $subRes['rawText'];
                    $tables = array_merge($tables, $subRes['tables']);
                    $headers = array_merge($headers, $subRes['headers']);
                    $parserUsed .= ', ' . $subRes['parserUsed'];
                    $isScanned = $isScanned || $subRes['isScanned'];
                } catch (\Exception $e) {
                    Log::warning("Failed to parse extracted file {$file}: " . $e->getMessage());
                }
            }
            return compact('rawText', 'tables', 'headers', 'parserUsed', 'isScanned');
        }

        if ($extension === 'html' || $extension === 'htm') {
            $parserUsed = 'HtmlParser';
            $res = $this->htmlParser->extractStructured($filePath);
            $rawText = $res['text'] ?? '';
            $tables = $res['tables'] ?? [];
            $headers = $res['headers'] ?? [];
        } elseif ($extension === 'pdf') {
            $parserUsed = 'PdfParser';
            $res = $this->pdfParser->extractStructured($filePath);
            $rawText = $res['text'] ?? '';
            $tables = $res['tables'] ?? [];
            $isScanned = $res['is_scanned'] ?? false;
            $scannedPages = $res['scanned_pages'] ?? [];
        } elseif (in_array($extension, ['docx', 'xlsx', 'doc', 'xls'])) {
            $parserUsed = 'DocumentParserService';
            $res = $this->docParser->extractStructured($filePath, $extension);
            $rawText = $res['text'] ?? '';
            $tables = $res['tables'] ?? [];
            $headers = $res['headers'] ?? [];
        } elseif ($extension === 'csv') {
            $parserUsed = 'CsvParser';
            $res = $this->csvParser->extractStructured($filePath);
            $rawText = $res['text'] ?? '';
            $tables = $res['tables'] ?? [];
            $headers = $res['headers'] ?? [];
        } elseif ($extension === 'xml') {
            $parserUsed = 'XmlParser';
            $res = $this->xmlParser->extractStructured($filePath);
            $rawText = $res['text'] ?? '';
            $tables = $res['tables'] ?? [];
            $headers = $res['headers'] ?? [];
        } elseif (in_array($extension, ['png', 'jpg', 'jpeg', 'tiff', 'bmp', 'webp', 'tif'])) {
            $parserUsed = 'ImageParser';
            $res = $this->imageParser->extractStructured($filePath);
            $rawText = $res['text'] ?? '';
            $isScanned = true;
        } else {
            throw new \Exception("Unsupported file type: {$extension}");
        }

        // OCR Fallback
        if (($extension === 'pdf' && ($isScanned || !empty($scannedPages) || strlen(trim($rawText)) < 150)) || empty(trim($rawText))) {
            if ($extension === 'pdf' || in_array($extension, ['png', 'jpg', 'jpeg', 'tiff', 'bmp', 'webp', 'tif'])) {
                Log::info("Universal Extraction Engine: Standard parser returned empty/short text or scanned pages detected. Falling back to OCR.");
                
                // Pass scanned pages to OCR Service if available so it can process only those pages
                $ocrOptions = !empty($scannedPages) ? ['pages' => $scannedPages] : [];
                $ocrText = $this->ocrService->extractText($filePath, $extension); // Note: Assuming OCRService ignores options for now unless updated
                
                if (!empty(trim($ocrText))) {
                    if (!empty($scannedPages)) {
                        $rawText .= "\n\n--- OCR EXTRACTED CONTENT ---\n\n" . $ocrText;
                    } else {
                        $rawText = $ocrText;
                    }
                    $parserUsed .= ' + OCR';
                    $isScanned = true;
                }
            }
        }

        return compact('rawText', 'tables', 'headers', 'parserUsed', 'isScanned', 'scannedPages');
    }

    protected function getExtensionFromMime(string $mime, string $filePath): string
    {
        $map = [
            'application/pdf' => 'pdf',
            'application/zip' => 'zip',
            'application/x-zip-compressed' => 'zip',
            'text/html' => 'html',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/vnd.ms-excel' => 'xls',
            'text/csv' => 'csv',
            'text/xml' => 'xml',
            'application/xml' => 'xml',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/tiff' => 'tiff',
            'image/webp' => 'webp',
            'image/bmp' => 'bmp',
        ];
        
        if (isset($map[$mime])) {
            return $map[$mime];
        }
        
        return strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    }
}
