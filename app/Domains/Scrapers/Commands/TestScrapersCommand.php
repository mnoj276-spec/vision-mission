<?php

namespace App\Domains\Scrapers\Commands;

use App\Models\ScrapingSource;
use App\Domains\Scrapers\Services\HybridScrapingEngine;
use App\Domains\Scrapers\Drivers\ScraperDriverManager;
use App\Domains\Scrapers\Services\AIService;
use App\Domains\Jobs\Repositories\Contracts\JobRepositoryInterface;
use App\Models\Category;
use App\Models\State;
use App\Models\Qualification;
use App\Models\Department;
use Illuminate\Console\Command;
use Carbon\Carbon;
use ReflectionClass;

class TestScrapersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraper:test 
                            {--source= : Limit test to a specific ScrapingSource ID} 
                            {--limit=5 : Max items to display and validate in detail per source} 
                            {--use-ai : Call live AI APIs for cleaning & summarization (caution: costs API credits)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dry-runs scrapers on sources, captures output, performs schema and duplicate validation, and generates a quality report.';

    /**
     * Execute the console command.
     */
    public function handle(
        HybridScrapingEngine $hybridEngine,
        ScraperDriverManager $driverManager,
        AIService $aiService,
        JobRepositoryInterface $jobRepo
    ): int {
        $sourceId = $this->option('source');
        $limit = (int) $this->option('limit');
        $useAi = $this->option('use-ai');

        if ($sourceId) {
            $sources = ScrapingSource::where('id', $sourceId)->get();
            if ($sources->isEmpty()) {
                $this->error("Scraping source with ID {$sourceId} not found.");
                return Command::FAILURE;
            }
        } else {
            $sources = ScrapingSource::where('is_active', true)->get();
            if ($sources->isEmpty()) {
                $this->warn("No active scraping sources found in the database.");
                return Command::SUCCESS;
            }
        }

        $this->info("================================================================================");
        $this->info("                       SCRAPER DRY-RUN & VALIDATION TEST                        ");
        $this->info("================================================================================");
        $this->info("Total Sources to Test: " . $sources->count());
        $this->info("Using " . ($useAi ? "Live AI Services (Gemini/OpenAI)" : "Local AI Simulator (Deterministic)"));
        $this->info("================================================================================");

        // Reflection setup to call protected ScrapingService methods
        $scrapingService = app(\App\Domains\Scrapers\Services\ScrapingService::class);
        $reflection = new ReflectionClass(\App\Domains\Scrapers\Services\ScrapingService::class);
        
        $preParser = $reflection->getMethod('runDeterministicPreParser');
        $preParser->setAccessible(true);
        
        $validator = $reflection->getMethod('validateScrapedJobSchema');
        $validator->setAccessible(true);
        
        $postTypeClassifier = $reflection->getMethod('classifyPostType');
        $postTypeClassifier->setAccessible(true);

        $mapCategory = $reflection->getMethod('mapCategorySemantically');
        $mapCategory->setAccessible(true);

        $mapState = $reflection->getMethod('mapStateSemantically');
        $mapState->setAccessible(true);

        $mapQual = $reflection->getMethod('mapQualificationSemantically');
        $mapQual->setAccessible(true);

        $fingerprintService = app(\App\Domains\Scrapers\Services\FingerprintService::class);

        $report = [];

        foreach ($sources as $source) {
            $this->comment("Testing Source: [ID: {$source->id}] {$source->name}");
            $this->line("Target URL: {$source->source_url}");

            $startTime = microtime(true);
            try {
                // 1. Run Scraper: Fetch page content
                $this->line("Fetching page content via hybrid engine...");
                $html = $hybridEngine->fetch($source);
                $fetchTime = round(microtime(true) - $startTime, 2);
                $engineUsed = $source->preferred_engine ?? 'unknown';
                $this->line("Fetched successfully in {$fetchTime}s using engine '{$engineUsed}'.");

                // 2. Resolve Driver and parse
                $driver = $driverManager->getDriverFor($source);
                $driverClass = class_basename($driver);
                $this->line("Resolving parser driver: {$driverClass}");
                
                $rawItems = $driver->parse($html, $source);
                $itemsCount = count($rawItems);
                $this->line("Parsed {$itemsCount} raw item(s) from content.");

                $validCount = 0;
                $invalidCount = 0;
                $duplicateCount = 0;

                // Validate each item (detailed print up to the limit, validate all for final stats)
                foreach ($rawItems as $index => $rawData) {
                    $itemIndex = $index + 1;
                    $shouldPrintDetail = $itemIndex <= $limit;

                    // 2a. Run pre-parser
                    $parsedData = $preParser->invoke($scrapingService, $rawData);

                    // 2b. Clean & Summarize (AI or Simulator)
                    if ($useAi) {
                        $aiData = $aiService->cleanAndSummarize($parsedData['raw_text'] ?? $parsedData['title']);
                    } else {
                        $aiData = $aiService->runDeterministicAISimulator($parsedData['raw_text'] ?? $parsedData['title']);
                    }

                    // Merge final data
                    $finalJobData = array_merge($aiData, [
                        'title'                 => $parsedData['title']                 ?? $aiData['title']                 ?? null,
                        'last_date_to_apply'    => $parsedData['last_date_to_apply']    ?? $aiData['last_date_to_apply']    ?? null,
                        'application_fee'       => $parsedData['application_fee']       ?? $aiData['application_fee']       ?? 0.00,
                        'official_website_link' => $parsedData['official_website_link'] ?? $aiData['official_website_link'] ?? null,
                        'apply_link'            => $parsedData['apply_link']            ?? $aiData['apply_link']            ?? null,
                        'age_min'               => $parsedData['age_min']               ?? $aiData['age_min']               ?? null,
                        'age_max'               => $parsedData['age_max']               ?? $aiData['age_max']               ?? null,
                    ]);

                    if (empty($finalJobData['description'])) {
                        $finalJobData['description'] = $parsedData['raw_text'] ?? $rawData['raw_text'] ?? $finalJobData['title'] ?? 'No description available.';
                    }
                    if (!isset($finalJobData['vacancy_count']) || $finalJobData['vacancy_count'] === null || $finalJobData['vacancy_count'] === '') {
                        $finalJobData['vacancy_count'] = 0;
                    }

                    // Map defaults to satisfy schema validation
                    $defaultCat   = $source->selectors_config['default_category_id']     ?? 1;
                    $defaultDept  = $source->selectors_config['default_department_id']   ?? 1;
                    $defaultState = $source->selectors_config['default_state_id']        ?? 1;
                    $defaultQual  = $source->selectors_config['default_qualification_id'] ?? 1;

                    // Apply semantic mapping
                    $textForMapping = ($parsedData['title'] ?? '') . ' ' . ($parsedData['raw_text'] ?? '');
                    
                    $finalJobData['category_id'] = !empty($rawData['category_name'])
                        ? (Category::where('name', trim($rawData['category_name']))->first()?->id ?? $defaultCat)
                        : $mapCategory->invoke($scrapingService, $textForMapping, $defaultCat);

                    $finalJobData['state_id'] = !empty($rawData['state_name'])
                        ? (State::where('name', trim($rawData['state_name']))->first()?->id ?? $defaultState)
                        : $mapState->invoke($scrapingService, $textForMapping, $defaultState);

                    $finalJobData['qualification_id'] = !empty($rawData['qualification_name'])
                        ? (Qualification::where('name', trim($rawData['qualification_name']))->first()?->id ?? $defaultQual)
                        : $mapQual->invoke($scrapingService, $textForMapping, $defaultQual);

                    $finalJobData['department_id'] = !empty($rawData['department_name'])
                        ? (Department::where('name', trim($rawData['department_name']))->first()?->id ?? $defaultDept)
                        : ($finalJobData['department_id'] ?? $defaultDept);

                    $finalJobData['post_type'] = $postTypeClassifier->invoke($scrapingService, $finalJobData['title'] ?? '', $parsedData['raw_text'] ?? '');

                    // Setup historical status flag
                    $deadline = !empty($finalJobData['last_date_to_apply']) ? Carbon::parse($finalJobData['last_date_to_apply']) : null;
                    $isPast   = $deadline && $deadline->isPast() && !$deadline->isToday();
                    $finalJobData['is_historical'] = $isPast;

                    // 3. Duplicate checks
                    $incomingFingerprint = $fingerprintService->generate([
                        'title'         => $finalJobData['title'],
                        'department_id' => $finalJobData['department_id'],
                        'source_url'    => $source->source_url,
                        'publish_date'  => $finalJobData['last_date_to_apply'] ?? '',
                    ]);
                    $finalJobData['fingerprint'] = $incomingFingerprint;

                    $isDuplicate = false;
                    $dupReason = '';

                    $masterByFingerprint = $jobRepo->findByFingerprint($incomingFingerprint);
                    if ($masterByFingerprint) {
                        $isDuplicate = true;
                        $dupReason = 'Exact fingerprint collision';
                    } else {
                        $recentPosts = $jobRepo->findFuzzyDuplicates($finalJobData['department_id']);
                        $fuzzyHit = $fingerprintService->isFuzzyDuplicate($finalJobData['title'], $recentPosts);
                        if ($fuzzyHit) {
                            $isDuplicate = true;
                            $dupReason = "Fuzzy duplicate (score: {$fuzzyHit['score']}%)";
                        } else {
                            $variantHit = $fingerprintService->detectTitleVariant($finalJobData['title'], $recentPosts);
                            if ($variantHit) {
                                $isDuplicate = true;
                                $dupReason = "Title variant (score: {$variantHit['score']}%, variant: '{$variantHit['variant']}')";
                            }
                        }
                    }

                    // 4. Run validations
                    $errors = $validator->invoke($scrapingService, $finalJobData);
                    
                    // 5. Confidence Score check
                    $rawText = $parsedData['raw_text'] ?? $parsedData['title'] ?? '';
                    $confidence = $aiService->computeConfidence($finalJobData, $rawText);
                    $confidenceScore = $confidence['overall'];
                    
                    if ($confidenceScore < 85.0) {
                        $errors['confidence'] = "AI Confidence Score ({$confidenceScore}%) is below 85%.";
                    }

                    if ($isDuplicate) {
                        $duplicateCount++;
                    }

                    if (empty($errors)) {
                        $validCount++;
                        $statusText = "<info>[✓] Passed</info>";
                    } else {
                        $invalidCount++;
                        $statusText = "<error>[✗] Failed Schema Validation</error>";
                    }

                    if ($shouldPrintDetail) {
                        $this->line("  Item #{$itemIndex}: \"{$finalJobData['title']}\"");
                        $this->line("    Post Type:  {$finalJobData['post_type']}");
                        $this->line("    Deadline:   " . ($finalJobData['last_date_to_apply'] ?? 'N/A'));
                        $this->line("    Fee:        Rs. {$finalJobData['application_fee']}");
                        $this->line("    Validation: {$statusText}");
                        foreach ($errors as $field => $errMsg) {
                            $this->line("      - <fg=red>{$field}</>: {$errMsg}");
                        }
                        $this->line("    Confidence: " . ($confidenceScore >= 85 ? "<info>{$confidenceScore}%</info>" : "<error>{$confidenceScore}%</error>"));
                        if ($isDuplicate) {
                            $this->line("    Duplicates: <comment>[Duplicate]</comment> - {$dupReason}");
                        } else {
                            $this->line("    Duplicates: <info>[New]</info> - Unique item");
                        }
                        $this->line("");
                    }
                }

                if ($itemsCount > $limit) {
                    $this->line("  ... and " . ($itemsCount - $limit) . " more item(s) processed and validated silently.");
                }

                $report[] = [
                    'id' => $source->id,
                    'name' => $source->name,
                    'engine' => $engineUsed,
                    'status' => $itemsCount > 0 ? 'SUCCESS' : 'NO_ITEMS',
                    'found' => $itemsCount,
                    'valid' => $validCount,
                    'invalid' => $invalidCount,
                    'duplicate' => $duplicateCount,
                    'error' => '',
                ];

            } catch (\Exception $e) {
                $this->error("Failed testing source [ID: {$source->id}]: " . $e->getMessage());
                $report[] = [
                    'id' => $source->id,
                    'name' => $source->name,
                    'engine' => $source->preferred_engine ?? 'unknown',
                    'status' => 'ERROR',
                    'found' => 0,
                    'valid' => 0,
                    'invalid' => 0,
                    'duplicate' => 0,
                    'error' => $e->getMessage(),
                ];
            }
            $this->line("--------------------------------------------------------------------------------");
        }

        // Generate the final Report Table
        $this->info("================================================================================");
        $this->info("                            FINAL QUALITY REPORT                                ");
        $this->info("================================================================================");
        
        $headers = ['ID', 'Source Name', 'Engine', 'Status', 'Found', 'Valid', 'Invalid', 'Duplicate'];
        $rows = [];
        
        foreach ($report as $r) {
            $statusStr = $r['status'];
            if ($statusStr === 'SUCCESS') {
                $statusStr = "<info>SUCCESS</info>";
            } elseif ($statusStr === 'NO_ITEMS') {
                $statusStr = "<comment>NO_ITEMS</comment>";
            } else {
                $statusStr = "<error>ERROR</error>";
            }

            $rows[] = [
                $r['id'],
                $r['name'],
                $r['engine'],
                $statusStr,
                $r['found'],
                $r['valid'],
                $r['invalid'],
                $r['duplicate']
            ];
        }

        $this->table($headers, $rows);

        // Print details about errors if any
        $errorsFound = false;
        foreach ($report as $r) {
            if ($r['status'] === 'ERROR') {
                if (!$errorsFound) {
                    $this->error("\nDetailed Failure Information:");
                    $errorsFound = true;
                }
                $this->line("  - <fg=red>Source [ID: {$r['id']}] {$r['name']}</>: {$r['error']}");
            }
        }

        $this->info("================================================================================");

        return Command::SUCCESS;
    }
}
