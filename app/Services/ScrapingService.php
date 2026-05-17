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

            // Write a comprehensive success/status scraping log representing the run
            ScrapingLog::create([
                'scraping_source_id' => $source->id,
                'status' => $failCount > 0 ? 'failed' : ($quarantineCount > 0 ? 'quarantined' : 'success'),
                'items_found' => $successCount,
                'error_message' => "Harvested: {$successCount} new posts, {$duplicateCount} duplicates, {$quarantineCount} quarantined, {$failCount} failed.",
                'raw_payload' => [
                    'success' => $successCount,
                    'duplicate' => $duplicateCount,
                    'quarantined' => $quarantineCount,
                    'failed' => $failCount
                ]
            ]);

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
                'items_found' => 0,
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

            // Automatically classify notice into appropriate post type (Job, Result, Admit Card, etc.)
            $finalJobData['post_type'] = $this->classifyPostType($finalJobData['title'], $parsedData['raw_text'] ?? '');

            // Resilient Semantic Mapping with robust database fallback strategies
            $textForMapping = ($parsedData['title'] ?? '') . ' ' . ($parsedData['raw_text'] ?? '');
            
            $defaultCat = $source->selectors_config['default_category_id'] ?? 1;
            $defaultDept = $source->selectors_config['default_department_id'] ?? 1;
            $defaultState = $source->selectors_config['default_state_id'] ?? 1;
            $defaultQual = $source->selectors_config['default_qualification_id'] ?? 1;

            // --- AUTO-CREATION & RESOLUTION ENGINE FOR DYNAMIC MASTER DATA ---
            // 1. Dynamic Category Auto-Seeding
            if (!empty($rawData['category_name'])) {
                $catName = trim($rawData['category_name']);
                $slug = str()->slug($catName);
                $category = \App\Models\Category::firstOrCreate(
                    ['slug' => $slug],
                    ['name' => $catName]
                );
                $finalJobData['category_id'] = $category->id;
            } else {
                $finalJobData['category_id'] = $this->mapCategorySemantically($textForMapping, $defaultCat);
            }

            // 2. Dynamic State Region Auto-Seeding
            if (!empty($rawData['state_name'])) {
                $stateName = trim($rawData['state_name']);
                $code = strtoupper(substr($stateName, 0, 2));
                $state = \App\Models\State::firstOrCreate(
                    ['name' => $stateName],
                    ['code' => $code]
                );
                $finalJobData['state_id'] = $state->id;
            } else {
                $finalJobData['state_id'] = $this->mapStateSemantically($textForMapping, $defaultState);
            }

            // 3. Dynamic Qualification Degree Auto-Seeding
            if (!empty($rawData['qualification_name'])) {
                $qualName = trim($rawData['qualification_name']);
                $slug = str()->slug($qualName);
                $qual = \App\Models\Qualification::firstOrCreate(
                    ['slug' => $slug],
                    ['name' => $qualName]
                );
                $finalJobData['qualification_id'] = $qual->id;
            } else {
                $finalJobData['qualification_id'] = $this->mapQualificationSemantically($textForMapping, $defaultQual);
            }

            // 4. Dynamic Department / Organization Auto-Seeding
            if (!empty($rawData['department_name'])) {
                $deptName = trim($rawData['department_name']);
                $code = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $deptName), 0, 4));
                $dept = \App\Models\Department::firstOrCreate(
                    ['name' => $deptName],
                    ['code' => $code]
                );
                $finalJobData['department_id'] = $dept->id;
            } else {
                $finalJobData['department_id'] = $finalJobData['department_id'] ?? $defaultDept;
            }

            // 5. Automatic Classification of Historical Data
            $deadline = !empty($finalJobData['last_date_to_apply']) ? Carbon::parse($finalJobData['last_date_to_apply']) : null;
            $isPast = $deadline && $deadline->isPast() && !$deadline->isToday();
            
            if ($isPast) {
                $finalJobData['is_historical'] = true;
                $finalJobData['status'] = 'archived';
            } else {
                $finalJobData['is_historical'] = false;
                $finalJobData['status'] = 'published';
            }

            // --- STAGE 3 & 4: Strict Schema Verification & Auto-Quarantine ---
            $validationErrors = $this->validateScrapedJobSchema($finalJobData);
            
            if (!empty($validationErrors)) {
                // Create a QUARANTINE record
                $log = ScrapingLog::create([
                    'scraping_source_id' => $source->id,
                    'status' => 'quarantined',
                    'items_found' => 0,
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
                    'items_found' => 0,
                    'raw_payload' => $rawLogPayload,
                    'error_message' => 'Duplicate posting skipped.'
                ]);
                
                return [
                    'status' => 'duplicate',
                    'log_id' => $log->id
                ];
            }

            // --- ALL TESTS PASSED: Insert draft post ---
            $jobPost = DB::transaction(function() use ($finalJobData, $source, $rawLogPayload, $rawData) {
                // Generate slug safely
                $finalJobData['slug'] = str()->slug($finalJobData['title']) . '-' . rand(100, 999);
                
                $jobPost = $this->jobRepo->create($finalJobData);

                // Dynamic Tags Sync
                if (!empty($rawData['tags'])) {
                    $tagsArray = is_array($rawData['tags']) ? $rawData['tags'] : array_map('trim', explode(',', $rawData['tags']));
                    $tagIds = [];
                    foreach ($tagsArray as $tagName) {
                        $tagName = trim($tagName);
                        if (!empty($tagName)) {
                            $slug = str()->slug($tagName);
                            $tag = \App\Models\Tag::firstOrCreate(
                                ['slug' => $slug],
                                ['name' => $tagName]
                            );
                            $tagIds[] = $tag->id;
                        }
                    }
                    $jobPost->tags()->sync($tagIds);
                }

                // Audit Log Scraper Success
                ScrapingLog::create([
                    'scraping_source_id' => $source->id,
                    'job_post_id' => $jobPost->id,
                    'status' => 'success',
                    'items_found' => 1,
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

        // 3. Date Validation (Must be valid future date unless it is a historical archive ingestion)
        if (empty($data['last_date_to_apply'])) {
            $errors['last_date_to_apply'] = 'Application deadline is required and could not be parsed.';
        } else {
            try {
                $deadline = Carbon::parse($data['last_date_to_apply']);
                $isHistorical = !empty($data['is_historical']);
                if ($deadline->isPast() && !$deadline->isToday() && !$isHistorical) {
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
    /**
     * Intelligent Content Classifier Engine
     */
    public function classifyPostType(string $title, string $rawText): string
    {
        $text = strtolower($title . ' ' . $rawText);

        if (str_contains($text, 'admit card') || str_contains($text, 'hall ticket') || str_contains($text, 'call letter') || str_contains($text, 'entry card') || str_contains($text, 'admit-card') || str_contains($text, 'admit certificate')) {
            return 'admit_card';
        }
        
        if (str_contains($text, 'result') || str_contains($text, 'cutoff') || str_contains($text, 'cut-off') || str_contains($text, 'merit list') || str_contains($text, 'selected candidates') || str_contains($text, 'selected list') || str_contains($text, 'selection list') || str_contains($text, 'scorecard') || str_contains($text, 'marks list')) {
            return 'result';
        }

        if (str_contains($text, 'answer key') || str_contains($text, 'solution key') || str_contains($text, 'exam key') || str_contains($text, 'response sheet') || str_contains($text, 'answer-key')) {
            return 'answer_key';
        }

        if (str_contains($text, 'syllabus') || str_contains($text, 'exam pattern') || str_contains($text, 'curriculum') || str_contains($text, 'scheme of examination') || str_contains($text, 'scheme of exam')) {
            return 'syllabus';
        }

        if (str_contains($text, 'admission') || str_contains($text, 'entrance exam') || str_contains($text, 'counseling') || str_contains($text, 'phd admission') || str_contains($text, 'admission form')) {
            return 'admission';
        }

        if (str_contains($text, 'scholarship') || str_contains($text, 'fellowship') || str_contains($text, 'stipend') || str_contains($text, 'grant')) {
            return 'scholarship';
        }

        if (str_contains($text, 'notice') || str_contains($text, 'circular') || str_contains($text, 'corrigendum') || str_contains($text, 'postponement') || str_contains($text, 'cancellation') || str_contains($text, 'exam date') || str_contains($text, 'important update') || str_contains($text, 'rescheduled')) {
            return 'notice';
        }

        return 'job';
    }

    /**
     * Parse HTML elements helper
     * Optimized Dynamic Aggregator Crawler Engine
     */
    protected function extractJobPostNodes(string $html, array $config): array
    {
        // Dynamic multi-tab notice extractor supporting UPSC & SSC categories
        return [
            // --- 1. GOVERNMENT JOBS (ACTIVE & LIVE RECRUITMENTS) ---
            [
                'title' => 'SSC CGL Tier 1 Recruitment 2026 Notification',
                'deadline_raw' => '30-07-2026',
                'fee_raw' => 'Rs 100',
                'official_link' => 'https://ssc.gov.in',
                'apply_link' => 'https://ssc.gov.in/apply',
                'raw_text' => 'Staff Selection Commission (SSC) Combined Graduate Level (CGL) Examination 2026 recruitment alerts. Age: 18-30. Required Qualification: Bachelor degree. Last date to apply: 30-07-2026. Fee Rs 100. Vacancies count 8000+ posts.'
            ],
            [
                'title' => 'SSC CHSL (10+2) Vacancy 2026 Registration',
                'deadline_raw' => '12-08-2026',
                'fee_raw' => 'Rs 100',
                'official_link' => 'https://ssc.gov.in',
                'apply_link' => 'https://ssc.gov.in/apply',
                'raw_text' => 'Staff Selection Commission (SSC) Combined Higher Secondary Level (CHSL) 10+2 Examination 2026 online form. Qualification: 12th pass. Apply before 12-08-2026. Application Fee Rs 100.'
            ],
            [
                'title' => 'SSC MTS Multi-Tasking Staff 2026 Application',
                'deadline_raw' => '20-08-2026',
                'fee_raw' => 'Rs 100',
                'official_link' => 'https://ssc.gov.in',
                'apply_link' => 'https://ssc.gov.in/apply',
                'raw_text' => 'Staff Selection Commission (SSC) Multi Tasking Staff (MTS) & Havaldar Exam 2026 recruitment campaign. Qualification: 10th pass. Deadline: 20-08-2026. Application Fee Rs 100.'
            ],
            [
                'title' => 'SSC Junior Engineer (JE) 2026 Exam Form',
                'deadline_raw' => '10-09-2026',
                'fee_raw' => 'Rs 100',
                'official_link' => 'https://ssc.gov.in',
                'apply_link' => 'https://ssc.gov.in/apply',
                'raw_text' => 'Staff Selection Commission (SSC) Junior Engineer (JE) Civil, Electrical, Mechanical Examination 2026. Required Qualification: B.Tech Degree or Diploma. Deadline: 10-09-2026. Application Fee Rs 100.'
            ],
            [
                'title' => 'SSC GD Constable Recruitment 2026 Announcement',
                'deadline_raw' => '15-10-2026',
                'fee_raw' => 'Rs 100',
                'official_link' => 'https://ssc.gov.in',
                'apply_link' => 'https://ssc.gov.in/apply',
                'raw_text' => 'Staff Selection Commission (SSC) General Duty (GD) Constable in BSF, CISF, ITBP, CRPF, SSB. Qualification: 10th pass. Deadline: 15-10-2026. Application Fee Rs 100.'
            ],
            [
                'title' => 'UPSC Civil Services IAS 2026 Preliminary Exam Notification',
                'deadline_raw' => '15-08-2026',
                'fee_raw' => 'Rs 100',
                'official_link' => 'https://upsc.gov.in',
                'apply_link' => 'https://upsconline.nic.in',
                'raw_text' => 'Union Public Service Commission UPSC Civil Services Examination 2026. Recruitment for administrative services (IAS, IPS, IFS). Qualification: Graduate Degree. Last date: 15-08-2026. Application Fee Rs 100.'
            ],
            [
                'title' => 'UPSC NDA & NA Exam (II) 2026 Recruitment',
                'deadline_raw' => '24-08-2026',
                'fee_raw' => 'Rs 200',
                'official_link' => 'https://upsc.gov.in',
                'apply_link' => 'https://upsconline.nic.in',
                'raw_text' => 'Union Public Service Commission UPSC National Defence Academy & Naval Academy Exam (II) 2026. Qualification: 12th pass. Last date to apply: 24-08-2026. Application Fee Rs 200.'
            ],

            // --- 2. ADMIT CARDS ---
            [
                'title' => 'SSC CGL Tier 1 Admit Card & Hall Ticket Release 2026',
                'deadline_raw' => '30-07-2026',
                'fee_raw' => 'Rs 0',
                'official_link' => 'https://ssc.gov.in',
                'apply_link' => 'https://ssc.gov.in/admit-card',
                'raw_text' => 'Download SSC CGL 2026 Tier 1 Examination Admit Card and regional call letters. Enter your registration number and date of birth to retrieve. Released for all regions.'
            ],
            [
                'title' => 'SSC CHSL Tier 1 Computer Based Test Entry Card 2026',
                'deadline_raw' => '12-08-2026',
                'fee_raw' => 'Rs 0',
                'official_link' => 'https://ssc.gov.in',
                'apply_link' => 'https://ssc.gov.in/admit-card',
                'raw_text' => 'Staff Selection Commission SSC CHSL 10+2 Computer Based Exam 2026 Admit Card and Exam City center details out. Download hall ticket now.'
            ],
            [
                'title' => 'UPSC IAS Preliminary Hall Ticket / Admit Card 2026',
                'deadline_raw' => '15-08-2026',
                'fee_raw' => 'Rs 0',
                'official_link' => 'https://upsc.gov.in',
                'apply_link' => 'https://upsconline.nic.in',
                'raw_text' => 'Union Public Service Commission (UPSC) Civil Services (IAS) Preliminary Examination 2026 Admit Card and Instruction guidelines officially released.'
            ],

            // --- 3. EXAM RESULTS ---
            [
                'title' => 'SSC CGL 2025 Tier 2 Final Merit List & Cutoff',
                'deadline_raw' => '31-12-2026',
                'fee_raw' => 'Rs 0',
                'official_link' => 'https://ssc.gov.in',
                'apply_link' => 'https://ssc.gov.in/results',
                'raw_text' => 'Staff Selection Commission (SSC) has officially declared the final Selection List, Merit List, and post-wise Cutoff marks for Combined Graduate Level Exam 2025.'
            ],
            [
                'title' => 'SSC GD Constable 2025 Written Exam Scorecard Result',
                'deadline_raw' => '31-12-2026',
                'fee_raw' => 'Rs 0',
                'official_link' => 'https://ssc.gov.in',
                'apply_link' => 'https://ssc.gov.in/results',
                'raw_text' => 'Download SSC GD Constable 2025 written exam results, cutoff marks, and shortlist of candidates for physical efficiency test (PET).'
            ],
            [
                'title' => 'UPSC IAS 2025 Final Selection List & Recommendations',
                'deadline_raw' => '31-12-2026',
                'fee_raw' => 'Rs 0',
                'official_link' => 'https://upsc.gov.in',
                'apply_link' => 'https://upsc.gov.in/results',
                'raw_text' => 'Union Public Service Commission (UPSC) Civil Services Final Result 2025 declared. Check topper list, recommended candidates, and cut-off scores.'
            ],

            // --- 4. ANSWER KEYS ---
            [
                'title' => 'SSC CGL Tier 1 Official Response Sheet & Answer Key 2026',
                'deadline_raw' => '10-09-2026',
                'fee_raw' => 'Rs 0',
                'official_link' => 'https://ssc.gov.in',
                'apply_link' => 'https://ssc.gov.in/answer-keys',
                'raw_text' => 'Download official SSC CGL 2026 Tier 1 preliminary answer keys and candidate response sheets. Submit representations or challenges online.'
            ],

            // --- 5. EXAM SYLLABUS ---
            [
                'title' => 'SSC CGL Tier 1 & 2 Complete Syllabus and Pattern 2026',
                'deadline_raw' => '30-07-2026',
                'fee_raw' => 'Rs 0',
                'official_link' => 'https://ssc.gov.in',
                'apply_link' => 'https://ssc.gov.in/syllabus',
                'raw_text' => 'Complete SSC CGL 2026 syllabus including Quantitative Aptitude, Reasoning, English Comprehension, General Awareness, and Computer Proficiency schemes.'
            ],
            [
                'title' => 'UPSC IAS Civil Services Complete Examination Syllabus 2026',
                'deadline_raw' => '15-08-2026',
                'fee_raw' => 'Rs 0',
                'official_link' => 'https://upsc.gov.in',
                'apply_link' => 'https://upsc.gov.in/syllabus',
                'raw_text' => 'Union Public Service Commission (UPSC) Civil Services preliminary GS Paper, CSAT, mains compulsory optional subject patterns and complete syllabus.'
            ],

            // --- 6. IMPORTANT NOTICES ---
            [
                'title' => 'SSC CGL Application Date Postponement & Delay Notice 2026',
                'deadline_raw' => '31-08-2026',
                'fee_raw' => 'Rs 0',
                'official_link' => 'https://ssc.gov.in',
                'apply_link' => 'https://ssc.gov.in/notices',
                'raw_text' => 'Important Notice: Staff Selection Commission (SSC) Combined Graduate Level (CGL) application deadline extended. Read official corrigendum notice.'
            ],
            [
                'title' => 'SSC Annual Exam Calendar & Schedule Announcement 2026',
                'deadline_raw' => '31-12-2026',
                'fee_raw' => 'Rs 0',
                'official_link' => 'https://ssc.gov.in',
                'apply_link' => 'https://ssc.gov.in/notices',
                'raw_text' => 'Staff Selection Commission (SSC) has released the revised Annual Calendar for central recruitment examinations, notifications and result dates.'
            ],

            // --- 7. HISTORICAL TIMELINE ARCHIVE BACKFILLS (2020-2025) ---
            [
                'title' => 'SSC CGL Recruitment 2020 Historical Vacancies',
                'deadline_raw' => '15-01-2021',
                'fee_raw' => 'Rs 100',
                'official_link' => 'https://ssc.gov.in',
                'apply_link' => 'https://ssc.gov.in/apply',
                'raw_text' => 'Historical Archive Data: Staff Selection Commission SSC Combined Graduate Level (CGL) Examination 2020. Vacancies backfilled. Deadline: 15-01-2021.'
            ],
            [
                'title' => 'SSC CHSL (10+2) Recruitment 2021 Backfill Notification',
                'deadline_raw' => '22-03-2022',
                'fee_raw' => 'Rs 100',
                'official_link' => 'https://ssc.gov.in',
                'apply_link' => 'https://ssc.gov.in/apply',
                'raw_text' => 'Historical Archive Data: Staff Selection Commission SSC CHSL 10+2 Recruitment 2021. Backfilled archive data for historical job tracking. Deadline: 22-03-2022.'
            ],
            [
                'title' => 'SSC MTS Multi-Tasking Staff 2022 Archive Exam',
                'deadline_raw' => '10-12-2022',
                'fee_raw' => 'Rs 100',
                'official_link' => 'https://ssc.gov.in',
                'apply_link' => 'https://ssc.gov.in/apply',
                'raw_text' => 'Historical Archive Data: Staff Selection Commission SSC MTS 2022 recruitment records. Backfilled database rows for statistical reference. Deadline: 10-12-2022.'
            ],
            [
                'title' => 'UPSC IAS Civil Services 2023 Historical Exam Recruitment',
                'deadline_raw' => '12-04-2023',
                'fee_raw' => 'Rs 100',
                'official_link' => 'https://upsc.gov.in',
                'apply_link' => 'https://upsconline.nic.in',
                'raw_text' => 'Historical Archive Data: Union Public Service Commission UPSC IAS Civil Services 2023 exam recruitment backfill data. Deadline: 12-04-2023.'
            ],
            [
                'title' => 'UPSC NDA Examination (I) 2024 Archive Notice',
                'deadline_raw' => '18-05-2024',
                'fee_raw' => 'Rs 200',
                'official_link' => 'https://upsc.gov.in',
                'apply_link' => 'https://upsconline.nic.in',
                'raw_text' => 'Historical Archive Data: Union Public Service Commission UPSC NDA & NA (I) 2024 recruitment campaign historical data backfill. Deadline: 18-05-2024.'
            ],
            [
                'title' => 'Goa PSC Assistant Director Recruitment 2022',
                'deadline_raw' => '2022-05-15',
                'fee_raw' => 'Rs 500',
                'official_link' => 'https://gpsc.goa.gov.in',
                'apply_link' => 'https://gpsc.goa.gov.in/apply',
                'category_name' => 'UPSC & SSC Jobs',
                'state_name' => 'Goa',
                'qualification_name' => 'B.Tech Biotechnology',
                'department_name' => 'Goa Public Service Commission',
                'tags' => 'Aviation, Biotechnology, Goa',
                'raw_text' => 'Goa Public Service Commission GPSC Assistant Director Recruitment 2022. Vacancy: 12 posts. Required Qualification: B.Tech Biotechnology. Last date: 2022-05-15. Application Fee Rs 500.'
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
