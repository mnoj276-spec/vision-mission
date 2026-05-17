<?php

namespace App\Services;

use App\Models\ScrapingSource;
use App\Models\ScrapingLog;
use App\Repositories\Contracts\JobRepositoryInterface;
use App\Services\AIService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ScrapingService
{
    protected JobRepositoryInterface $jobRepo;
    protected AIService $aiService;

    public function __construct(JobRepositoryInterface $jobRepo, AIService $aiService)
    {
        $this->jobRepo = $jobRepo;
        $this->aiService = $aiService;
    }

    /**
     * Run scraper for a given source configuration.
     */
    public function scrapeSource(ScrapingSource $source): array
    {
        $logs = [];
        $url = $source->source_url;
        
        try {
            Log::info("Starting scrape run for source: {$source->name} [URL: {$url}]");
            
            // 1. Fetch raw page payload (supports HTTP client simulation)
            $response = Http::timeout(30)->get($url);
            if ($response->failed()) {
                throw new \Exception("HTTP Request failed with status code: " . $response->status());
            }
            
            $htmlContent = $response->body();
            
            // 2. Parse elements (simulate extracting HTML nodes based on source selector configs)
            // For production, this integrates Goutte / symfony/dom-crawler
            $rawJobs = $this->extractJobPostNodes($htmlContent, $source->selectors_config);
            
            $successCount = 0;
            $duplicateCount = 0;
            $quarantineCount = 0;
            $failCount = 0;

            foreach ($rawJobs as $rawJobData) {
                // Multi-Stage Processing Pipeline
                $result = $this->processScrapedItem($source, $rawJobData);
                
                if ($result['status'] === 'success') {
                    $successCount++;
                } elseif ($result['status'] === 'duplicate') {
                    $duplicateCount++;
                } elseif ($result['status'] === 'quarantined') {
                    $quarantineCount++;
                } else {
                    $failCount++;
                }
                
                $logs[] = $result;
            }

            Log::info("Completed scrape run: Success = {$successCount}, Duplicates = {$duplicateCount}, Quarantined = {$quarantineCount}, Failed = {$failCount}");

            return [
                'success' => true,
                'summary' => [
                    'success' => $successCount,
                    'duplicate' => $duplicateCount,
                    'quarantined' => $quarantineCount,
                    'failed' => $failCount
                ]
            ];

        } catch (\Exception $e) {
            Log::error("Scraper crash for source {$source->name}: " . $e->getMessage());
            
            // Write a failure scraping log
            ScrapingLog::create([
                'scraping_source_id' => $source->id,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'raw_payload' => ['url' => $url]
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Stage-by-Stage Processing and Verification for a Scraped Job Item
     */
    protected function processScrapedItem(ScrapingSource $source, array $rawData): array
    {
        $rawLogPayload = $rawData; // Archiving copy
        
        try {
            // --- STAGE 1: Deterministic Pre-Parsing (Deterministic Regex) ---
            $parsedData = $this->runDeterministicPreParser($rawData);

            // --- STAGE 2: Factual AI Synthesis & Cleanup ---
            // Calls AI service to summarize description and fill missing metadata with low temperature
            $aiData = $this->aiService->cleanAndSummarize($parsedData['raw_text'] ?? $parsedData['title']);
            
            // Merge deterministic parameters over AI data to avoid hallucinations on critical values
            $finalJobData = array_merge($aiData, [
                'title' => $parsedData['title'] ?? $aiData['title'] ?? null,
                'last_date_to_apply' => $parsedData['last_date_to_apply'] ?? $aiData['last_date_to_apply'] ?? null,
                'application_fee' => $parsedData['application_fee'] ?? $aiData['application_fee'] ?? 0.00,
                'official_website_link' => $parsedData['official_website_link'] ?? $aiData['official_website_link'] ?? null,
                'apply_link' => $parsedData['apply_link'] ?? $aiData['apply_link'] ?? null,
            ]);

            // Resilient Semantic Mapping with robust database fallback strategies
            $textForMapping = ($parsedData['title'] ?? '') . ' ' . ($parsedData['raw_text'] ?? '');
            
            $defaultCat = $source->selectors_config['default_category_id'] ?? 1;
            $defaultDept = $source->selectors_config['default_department_id'] ?? 1;
            $defaultState = $source->selectors_config['default_state_id'] ?? 1;
            $defaultQual = $source->selectors_config['default_qualification_id'] ?? 1;

            $finalJobData['category_id'] = $this->mapCategorySemantically($textForMapping, $defaultCat);
            $finalJobData['department_id'] = $finalJobData['department_id'] ?? $defaultDept;
            $finalJobData['state_id'] = $this->mapStateSemantically($textForMapping, $defaultState);
            $finalJobData['qualification_id'] = $this->mapQualificationSemantically($textForMapping, $defaultQual);
            $finalJobData['status'] = 'draft'; // Stored as draft initially for safety

            // --- STAGE 3 & 4: Strict Schema Verification & Auto-Quarantine ---
            $validationErrors = $this->validateScrapedJobSchema($finalJobData);
            
            if (!empty($validationErrors)) {
                // Create a QUARANTINE record
                $log = ScrapingLog::create([
                    'scraping_source_id' => $source->id,
                    'status' => 'quarantined',
                    'raw_payload' => $rawLogPayload,
                    'validation_errors' => $validationErrors,
                    'error_message' => 'Failed strict schema validation criteria.'
                ]);
                
                return [
                    'status' => 'quarantined',
                    'errors' => $validationErrors,
                    'log_id' => $log->id
                ];
            }

            // --- STAGE 5: Duplicate Detection Check ---
            $exists = $this->jobRepo->exists(
                $finalJobData['title'] ?? '',
                $finalJobData['department_id'],
                $finalJobData['last_date_to_apply'] ?? ''
            );

            if ($exists) {
                // Log skipped duplicate
                $log = ScrapingLog::create([
                    'scraping_source_id' => $source->id,
                    'status' => 'duplicate',
                    'raw_payload' => $rawLogPayload,
                    'error_message' => 'Duplicate posting skipped.'
                ]);
                
                return [
                    'status' => 'duplicate',
                    'log_id' => $log->id
                ];
            }

            // --- ALL TESTS PASSED: Insert draft post ---
            $jobPost = DB::transaction(function() use ($finalJobData, $source, $rawLogPayload) {
                // Generate slug safely
                $finalJobData['slug'] = str()->slug($finalJobData['title']) . '-' . rand(100, 999);
                
                $jobPost = $this->jobRepo->create($finalJobData);

                // Audit Log Scraper Success
                ScrapingLog::create([
                    'scraping_source_id' => $source->id,
                    'job_post_id' => $jobPost->id,
                    'status' => 'success',
                    'raw_payload' => $rawLogPayload
                ]);

                return $jobPost;
            });

            return [
                'status' => 'success',
                'job_post_id' => $jobPost->id
            ];

        } catch (\Exception $e) {
            Log::error("Failed parsing scraped item: " . $e->getMessage());
            
            $log = ScrapingLog::create([
                'scraping_source_id' => $source->id,
                'status' => 'failed',
                'raw_payload' => $rawLogPayload,
                'error_message' => $e->getMessage()
            ]);

            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'log_id' => $log->id
            ];
        }
    }

    /**
     * Stage 1 Parser: Extracts fields using exact PHP regex patterns
     */
    protected function runDeterministicPreParser(array $rawData): array
    {
        $parsed = $rawData;
        
        // 1. Strict Deadline Regex Extraction (Standardize e.g. "Apply before 15-06-2026" or "15/06/2026")
        if (!empty($rawData['deadline_raw'])) {
            $parsed['last_date_to_apply'] = $this->parseDateDeterministic($rawData['deadline_raw']);
        }

        // 2. Strict Fee Regex Extraction (Matches e.g. "INR 500", "Rs 150", "Fee: 100/-")
        if (!empty($rawData['fee_raw'])) {
            $parsed['application_fee'] = $this->parseFeeDeterministic($rawData['fee_raw']);
        }

        // 3. Link Sanitization
        if (!empty($rawData['official_link'])) {
            $parsed['official_website_link'] = filter_var($rawData['official_link'], FILTER_VALIDATE_URL) ? $rawData['official_link'] : null;
        }
        if (!empty($rawData['apply_link'])) {
            $parsed['apply_link'] = filter_var($rawData['apply_link'], FILTER_VALIDATE_URL) ? $rawData['apply_link'] : null;
        }

        return $parsed;
    }

    /**
     * Strict Database Schema Validator
     */
    protected function validateScrapedJobSchema(array $data): array
    {
        $errors = [];

        // 1. Title Validation
        if (empty($data['title']) || strlen($data['title']) < 15) {
            $errors['title'] = 'Title is empty or too short (must be at least 15 characters).';
        }

        // 2. Critical References Checks
        if (empty($data['department_id']) || !is_numeric($data['department_id'])) {
            $errors['department_id'] = 'Missing or invalid department specification.';
        }
        if (empty($data['state_id']) || !is_numeric($data['state_id'])) {
            $errors['state_id'] = 'Missing or invalid state location ID.';
        }

        // 3. Date Validation (Must be valid future date)
        if (empty($data['last_date_to_apply'])) {
            $errors['last_date_to_apply'] = 'Application deadline is required and could not be parsed.';
        } else {
            try {
                $deadline = Carbon::parse($data['last_date_to_apply']);
                if ($deadline->isPast() && !$deadline->isToday()) {
                    $errors['last_date_to_apply'] = "Scraped deadline [{$data['last_date_to_apply']}] has already expired.";
                }
            } catch (\Exception $e) {
                $errors['last_date_to_apply'] = 'Invalid date format parsed: ' . $data['last_date_to_apply'];
            }
        }

        // 4. Link Safety Boundary
        if (empty($data['official_website_link']) && empty($data['apply_link'])) {
            $errors['urls'] = 'A valid official website URL or apply link must be present.';
        }

        return $errors;
    }

    /**
     * Regex Helper to capture and standardize dates
     */
    protected function parseDateDeterministic(string $rawDateString): ?string
    {
        // Matches DD-MM-YYYY, DD/MM/YYYY, YYYY-MM-DD
        if (preg_match('/(\d{2})[-.\/](\d{2})[-.\/](\d{4})/', $rawDateString, $matches)) {
            return "{$matches[3]}-{$matches[2]}-{$matches[1]}"; // Convert to YYYY-MM-DD
        }
        if (preg_match('/(\d{4})[-.\/](\d{2})[-.\/](\d{2})/', $rawDateString, $matches)) {
            return "{$matches[1]}-{$matches[2]}-{$matches[3]}";
        }
        
        try {
            return Carbon::parse($rawDateString)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Regex Helper to capture exact fee decimal value
     */
    protected function parseFeeDeterministic(string $rawFeeString): float
    {
        if (preg_match('/(?:Rs\.?|INR|₹)\s*([\d,]+)/i', $rawFeeString, $matches)) {
            return (float) str_replace(',', '', $matches[1]);
        }
        if (preg_match('/([\d,]+)\s*(?:Rupees|Rs)/i', $rawFeeString, $matches)) {
            return (float) str_replace(',', '', $matches[1]);
        }
        if (preg_match('/\b(\d+)\b/', $rawFeeString, $matches)) {
            return (float) $matches[1];
        }
        return 0.00;
    }

    /**
     * Parse HTML elements helper
     */
    protected function extractJobPostNodes(string $html, array $config): array
    {
        // In a real crawler, we would load: $crawler = new Crawler($html);
        // This function parses tables or RSS feeds.
        // We will seed mock job items representing high-quality scraped government recruitments for the seeder/scaffold
        return [
            [
                'title' => 'UPSC IAS Notification 2026 Recruitment',
                'deadline_raw' => '15-08-2026',
                'fee_raw' => 'Rs. 100',
                'official_link' => 'https://upsc.gov.in',
                'apply_link' => 'https://upsconline.nic.in',
                'raw_text' => 'Union Public Service Commission UPSC Civil Services Exam (IAS/IFS) 2026. Age limit: 21-32 years. Vacancy: 1056 posts. Required Qualification: Graduate Degree. Last date: 15-08-2026. Application Fee Rs 100.'
            ],
            [
                'title' => 'SSC CGL Tier 1 Combined Graduate Level',
                'deadline_raw' => '30-07-2026',
                'fee_raw' => 'Rs 100',
                'official_link' => 'https://ssc.gov.in',
                'apply_link' => 'https://ssc.gov.in/apply',
                'raw_text' => 'Staff Selection Commission (SSC) Combined Graduate Level (CGL) Examination 2026. Age: 18-30. Required Qualification: Bachelor degree. Last date to apply: 30-07-2026. Fee Rs 100.'
            ],
            [
                'title' => 'RBI Grade B Officer vacancies',
                'deadline_raw' => '25-06-2026',
                'fee_raw' => 'Rs. 850',
                'official_link' => 'https://rbi.org.in',
                'apply_link' => 'https://rbi.org.in/apply',
                'raw_text' => 'Reserve Bank of India RBI Officer in Grade B General/DEPR/DSIM. Vacancy: 250 posts. Age: 21-30. Required Qualification: 60% in Graduation. Last date: 25-06-2026. Fee Rs. 850.'
            ]
        ];
    }

    /**
     * Resilient semantic category mapper
     */
    protected function mapCategorySemantically(?string $text, int $defaultId): int
    {
        if (empty($text)) {
            return $defaultId;
        }

        $lower = strtolower($text);

        $category = null;
        if (str_contains($lower, 'bank') || str_contains($lower, 'finance') || str_contains($lower, 'sbi') || str_contains($lower, 'rbi') || str_contains($lower, 'clerk') || str_contains($lower, 'po')) {
            $category = \App\Models\Category::where('slug', 'banking-finance')->first();
        } elseif (str_contains($lower, 'railway') || str_contains($lower, 'rrb') || str_contains($lower, 'alp') || str_contains($lower, 'loco')) {
            $category = \App\Models\Category::where('slug', 'railway-jobs')->first();
        } elseif (str_contains($lower, 'defense') || str_contains($lower, 'police') || str_contains($lower, 'soldier') || str_contains($lower, 'constable') || str_contains($lower, 'army')) {
            $category = \App\Models\Category::where('slug', 'defense-jobs')->first();
        } elseif (str_contains($lower, 'upsc') || str_contains($lower, 'ssc') || str_contains($lower, 'civil') || str_contains($lower, 'commission')) {
            $category = \App\Models\Category::where('slug', 'upsc-ssc-jobs')->first();
        }

        return $category ? $category->id : $defaultId;
    }

    /**
     * Resilient semantic state mapper
     */
    protected function mapStateSemantically(?string $text, int $defaultId): int
    {
        if (empty($text)) {
            return $defaultId;
        }

        $lower = strtolower($text);

        $state = null;
        if (str_contains($lower, 'uttar pradesh') || str_contains($lower, 'up')) {
            $state = \App\Models\State::where('code', 'UP')->first();
        } elseif (str_contains($lower, 'maharashtra') || str_contains($lower, 'mh') || str_contains($lower, 'mumbai')) {
            $state = \App\Models\State::where('code', 'MH')->first();
        } elseif (str_contains($lower, 'delhi') || str_contains($lower, 'nct') || str_contains($lower, 'dl')) {
            $state = \App\Models\State::where('code', 'DL')->first();
        } elseif (str_contains($lower, 'karnataka') || str_contains($lower, 'ka') || str_contains($lower, 'bengaluru')) {
            $state = \App\Models\State::where('code', 'KA')->first();
        }

        return $state ? $state->id : $defaultId;
    }

    /**
     * Resilient semantic qualification mapper
     */
    protected function mapQualificationSemantically(?string $text, int $defaultId): int
    {
        if (empty($text)) {
            return $defaultId;
        }

        $lower = strtolower($text);

        $qual = null;
        if (str_contains($lower, 'post graduate') || str_contains($lower, 'master') || str_contains($lower, 'm.tech') || str_contains($lower, 'm.ca') || str_contains($lower, 'm.sc')) {
            $qual = \App\Models\Qualification::where('slug', 'post-graduate')->first();
        } elseif (str_contains($lower, 'graduate') || str_contains($lower, 'bachelor') || str_contains($lower, 'degree') || str_contains($lower, 'b.tech') || str_contains($lower, 'b.ca') || str_contains($lower, 'b.sc')) {
            $qual = \App\Models\Qualification::where('slug', 'graduate')->first();
        } elseif (str_contains($lower, '12th') || str_contains($lower, 'intermediate') || str_contains($lower, 'hsc')) {
            $qual = \App\Models\Qualification::where('slug', '12th-pass')->first();
        } elseif (str_contains($lower, '10th') || str_contains($lower, 'high school') || str_contains($lower, 'ssc') || str_contains($lower, 'pass')) {
            $qual = \App\Models\Qualification::where('slug', '10th-pass')->first();
        }

        return $qual ? $qual->id : $defaultId;
    }
}
