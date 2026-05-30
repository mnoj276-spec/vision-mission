<?php

namespace App\Domains\Scrapers\Services;

use App\Domains\Jobs\Repositories\Contracts\JobRepositoryInterface;
use App\Domains\Scrapers\Services\Contracts\ScrapingServiceInterface;
use App\Models\Category;
use App\Models\Department;
use App\Models\DuplicateAuditLog;
use App\Models\Qualification;
use App\Models\ScrapingLog;
use App\Models\ScrapingSource;
use App\Models\State;
use App\Models\Tag;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ScrapingService implements ScrapingServiceInterface
{
    public function __construct(
        protected JobRepositoryInterface $jobRepo,
        protected AIService $aiService,
        protected FingerprintService $fingerprintService
    ) {}

    public function scrapeSource(ScrapingSource $source, int $attempt = 1): array
    {
        $url = $source->source_url;
        try {
            if ($attempt > 1) {
                Log::warning("Retrying scrape run for source: {$source->name} [URL: {$url}] (Attempt #{$attempt})");
            } else {
                Log::info("Starting scrape run for source: {$source->name} [URL: {$url}]");
            }
            
            // Mitigate SSRF: enforce strict allowed domains and reject local loopback
            if (!\App\Services\UrlSecurity::isSafeUrl($url)) {
                throw new \Exception("SSRF Block: The source URL '{$url}' is not a permitted domain.");
            }

            $response = Http::timeout(30)->get($url);
            if ($response->failed()) {
                throw new \Exception("HTTP Request failed with status: " . $response->status());
            }
            $rawJobs = $this->extractJobPostNodes($response->body(), $source);
            $s = $d = $q = $f = 0;
            foreach ($rawJobs as $rawJobData) {
                $result = $this->processScrapedItem($source, $rawJobData);
                match ($result['status']) {
                    'success'     => $s++,
                    'duplicate'   => $d++,
                    'quarantined' => $q++,
                    default       => $f++,
                };
            }
            Log::info("Scrape done: success={$s}, dups={$d}, quarantined={$q}, failed={$f}");
            ScrapingLog::create([
                'scraping_source_id' => $source->id,
                'status'             => $f > 0 ? 'failed' : ($q > 0 ? 'quarantined' : 'success'),
                'items_found'        => $s,
                'error_message'      => "Harvested: {$s} new, {$d} dups, {$q} quarantined, {$f} failed.",
                'raw_payload'        => [
                    'success' => $s,
                    'duplicate' => $d,
                    'quarantined' => $q,
                    'failed' => $f,
                    'attempt' => $attempt,
                    'retried' => $attempt > 1,
                ],
            ]);
            return ['success' => true, 'summary' => ['success' => $s, 'duplicate' => $d, 'quarantined' => $q, 'failed' => $f]];
        } catch (\Exception $e) {
            Log::error("Scraper crash for {$source->name}: " . $e->getMessage());
            ScrapingLog::create([
                'scraping_source_id' => $source->id,
                'status' => 'failed', 'items_found' => 0,
                'error_message' => $e->getMessage(),
                'raw_payload' => [
                    'url' => $url,
                    'attempt' => $attempt,
                    'retried' => $attempt > 1,
                    'trace' => substr($e->getTraceAsString(), 0, 1000)
                ],
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function processScrapedItem(ScrapingSource $source, array $rawData): array
    {
        $rawLogPayload = $rawData;
        try {
            $parsedData = $this->runDeterministicPreParser($rawData);
            $aiData     = $this->aiService->cleanAndSummarize($parsedData['raw_text'] ?? $parsedData['title']);
            $finalJobData = array_merge($aiData, [
                'title'                 => $parsedData['title']                 ?? $aiData['title']                 ?? null,
                'last_date_to_apply'    => $parsedData['last_date_to_apply']    ?? $aiData['last_date_to_apply']    ?? null,
                'application_fee'       => $parsedData['application_fee']       ?? $aiData['application_fee']       ?? 0.00,
                'official_website_link' => $parsedData['official_website_link'] ?? $aiData['official_website_link'] ?? null,
                'apply_link'            => $parsedData['apply_link']            ?? $aiData['apply_link']            ?? null,
            ]);

            // Prevent Stored XSS from scraped rich-text or title strings
            if (isset($finalJobData['title'])) {
                $finalJobData['title'] = \App\Services\HtmlSanitizer::sanitizeString($finalJobData['title']);
            }
            if (isset($finalJobData['description'])) {
                $finalJobData['description'] = \App\Services\HtmlSanitizer::sanitizeHtml($finalJobData['description']);
            }
            if (isset($finalJobData['exam_pattern'])) {
                $finalJobData['exam_pattern'] = \App\Services\HtmlSanitizer::sanitizeHtml($finalJobData['exam_pattern']);
            }
            if (isset($finalJobData['selection_process'])) {
                $finalJobData['selection_process'] = \App\Services\HtmlSanitizer::sanitizeHtml($finalJobData['selection_process']);
            }
            if (isset($finalJobData['age_limit'])) {
                $finalJobData['age_limit'] = \App\Services\HtmlSanitizer::sanitizeString($finalJobData['age_limit']);
            }

            $finalJobData['post_type'] = $this->classifyPostType($finalJobData['title'], $parsedData['raw_text'] ?? '');
            $textForMapping = ($parsedData['title'] ?? '') . ' ' . ($parsedData['raw_text'] ?? '');
            $defaultCat   = $source->selectors_config['default_category_id']     ?? 1;
            $defaultDept  = $source->selectors_config['default_department_id']   ?? 1;
            $defaultState = $source->selectors_config['default_state_id']        ?? 1;
            $defaultQual  = $source->selectors_config['default_qualification_id'] ?? 1;

            $finalJobData['category_id'] = !empty($rawData['category_name'])
                ? Category::firstOrCreate(['slug' => str()->slug($rawData['category_name'])], ['name' => trim($rawData['category_name'])])->id
                : $this->mapCategorySemantically($textForMapping, $defaultCat);

            $finalJobData['state_id'] = !empty($rawData['state_name'])
                ? State::firstOrCreate(['name' => trim($rawData['state_name'])], ['code' => strtoupper(substr(trim($rawData['state_name']), 0, 2))])->id
                : $this->mapStateSemantically($textForMapping, $defaultState);

            $finalJobData['qualification_id'] = !empty($rawData['qualification_name'])
                ? Qualification::firstOrCreate(['slug' => str()->slug($rawData['qualification_name'])], ['name' => trim($rawData['qualification_name'])])->id
                : $this->mapQualificationSemantically($textForMapping, $defaultQual);

            $finalJobData['department_id'] = !empty($rawData['department_name'])
                ? Department::firstOrCreate(['name' => trim($rawData['department_name'])], ['code' => strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $rawData['department_name']), 0, 4))])->id
                : ($finalJobData['department_id'] ?? $defaultDept);

            $deadline = !empty($finalJobData['last_date_to_apply']) ? Carbon::parse($finalJobData['last_date_to_apply']) : null;
            $isPast   = $deadline && $deadline->isPast() && !$deadline->isToday();
            $finalJobData['is_historical'] = $isPast;
            $finalJobData['status']        = $isPast ? 'archived' : 'published';

            $errors = $this->validateScrapedJobSchema($finalJobData);
            if (!empty($errors)) {
                $log = ScrapingLog::create(['scraping_source_id' => $source->id, 'status' => 'quarantined', 'items_found' => 0, 'raw_payload' => $rawLogPayload, 'validation_errors' => $errors, 'error_message' => 'Failed schema validation.']);
                return ['status' => 'quarantined', 'errors' => $errors, 'log_id' => $log->id];
            }

            // Compute AI Reliability & Programmatic Confidence Scores
            $rawText = $parsedData['raw_text'] ?? $parsedData['title'] ?? '';
            $confidence = $this->aiService->computeConfidence($finalJobData, $rawText);
            $overallScore = $confidence['overall'];

            // Save structured AI Audit Log
            \App\Models\AiAuditLog::create([
                'scraping_source_id' => $source->id,
                'raw_text'           => $rawText,
                'extracted_json'     => $finalJobData,
                'confidence_scores'  => $confidence['scores'],
                'overall_score'      => $overallScore,
                'status'             => $overallScore >= 85.0 ? 'passed' : 'failed_confidence',
            ]);

            // Human Review Fallback: Quarantine low-confidence extraction
            if ($overallScore < 85.0) {
                Log::warning("AI Reliability Fallback: Low confidence detected ({$overallScore}%). Quarantining listing.");
                $errors = ["AI Confidence Score ({$overallScore}%) is below the reliability threshold of 85%."];
                $log = ScrapingLog::create([
                    'scraping_source_id' => $source->id,
                    'status'             => 'quarantined',
                    'items_found'        => 0,
                    'raw_payload'        => $rawLogPayload,
                    'validation_errors'  => $errors,
                    'error_message'      => 'AI Reliability confidence check failed.'
                ]);
                return ['status' => 'quarantined', 'errors' => $errors, 'log_id' => $log->id];
            }

            // -----------------------------------------------------------------
            // STAGE 1 — Exact SHA-256 Fingerprint Gate
            // -----------------------------------------------------------------
            $incomingFingerprint = $this->fingerprintService->generate([
                'title'         => $finalJobData['title'],
                'department_id' => $finalJobData['department_id'],
                'source_url'    => $source->source_url,
                'publish_date'  => $finalJobData['last_date_to_apply'] ?? '',
            ]);
            $finalJobData['fingerprint'] = $incomingFingerprint;

            $masterByFingerprint = $this->jobRepo->findByFingerprint($incomingFingerprint);
            if ($masterByFingerprint) {
                $log = ScrapingLog::create([
                    'scraping_source_id' => $source->id,
                    'status'             => 'duplicate',
                    'items_found'        => 0,
                    'raw_payload'        => $rawLogPayload,
                    'error_message'      => '[Stage 1] Exact fingerprint collision.',
                ]);
                DuplicateAuditLog::create([
                    'master_job_post_id'  => $masterByFingerprint->id,
                    'detection_method'    => 'fingerprint',
                    'incoming_fingerprint'=> $incomingFingerprint,
                    'master_fingerprint'  => $masterByFingerprint->fingerprint,
                    'raw_payload'         => $rawLogPayload,
                ]);
                return ['status' => 'duplicate', 'method' => 'fingerprint', 'log_id' => $log->id];
            }

            // -----------------------------------------------------------------
            // STAGE 2 — Fuzzy Similarity Gate (similar_text ≥ 85 %)
            // -----------------------------------------------------------------
            $recentPosts   = $this->jobRepo->findFuzzyDuplicates($finalJobData['department_id']);
            $fuzzyHit      = $this->fingerprintService->isFuzzyDuplicate($finalJobData['title'], $recentPosts);
            if ($fuzzyHit) {
                $log = ScrapingLog::create([
                    'scraping_source_id' => $source->id,
                    'status'             => 'duplicate',
                    'items_found'        => 0,
                    'raw_payload'        => $rawLogPayload,
                    'error_message'      => "[Stage 2] Fuzzy duplicate detected (score: {$fuzzyHit['score']}%).",
                ]);
                DuplicateAuditLog::create([
                    'master_job_post_id'  => $fuzzyHit['post']->id,
                    'detection_method'    => 'fuzzy',
                    'similarity_score'    => $fuzzyHit['score'],
                    'incoming_fingerprint'=> $incomingFingerprint,
                    'master_fingerprint'  => $fuzzyHit['post']->fingerprint,
                    'raw_payload'         => $rawLogPayload,
                ]);
                return ['status' => 'duplicate', 'method' => 'fuzzy', 'score' => $fuzzyHit['score'], 'log_id' => $log->id];
            }

            // -----------------------------------------------------------------
            // STAGE 3 — Title Variant Gate (year-stripped / acronym-expanded)
            // -----------------------------------------------------------------
            $variantHit = $this->fingerprintService->detectTitleVariant($finalJobData['title'], $recentPosts);
            if ($variantHit) {
                $log = ScrapingLog::create([
                    'scraping_source_id' => $source->id,
                    'status'             => 'duplicate',
                    'items_found'        => 0,
                    'raw_payload'        => $rawLogPayload,
                    'error_message'      => "[Stage 3] Title variant duplicate (score: {$variantHit['score']}%, variant: '{$variantHit['variant']}').",
                ]);
                DuplicateAuditLog::create([
                    'master_job_post_id'  => $variantHit['post']->id,
                    'detection_method'    => 'title_variant',
                    'similarity_score'    => $variantHit['score'],
                    'incoming_fingerprint'=> $incomingFingerprint,
                    'master_fingerprint'  => $variantHit['post']->fingerprint,
                    'raw_payload'         => $rawLogPayload,
                ]);
                return ['status' => 'duplicate', 'method' => 'title_variant', 'score' => $variantHit['score'], 'log_id' => $log->id];
            }

            // -----------------------------------------------------------------
            // ALL GATES PASSED — Insert with DB unique constraint as final net
            // -----------------------------------------------------------------
            $finalJobData['source_id']  = $source->id;
            $finalJobData['expires_at'] = $finalJobData['last_date_to_apply'] ?? null;

            try {
                $jobPost = DB::transaction(function () use ($finalJobData, $source, $rawLogPayload, $rawData) {
                    $finalJobData['slug'] = str()->slug($finalJobData['title']) . '-' . rand(100, 999);
                    $jobPost = $this->jobRepo->create($finalJobData);
                    if (!empty($rawData['tags'])) {
                        $tagsArray = is_array($rawData['tags']) ? $rawData['tags'] : array_map('trim', explode(',', $rawData['tags']));
                        $tagIds = [];
                        foreach (array_filter(array_map('trim', $tagsArray)) as $tagName) {
                            $tagIds[] = Tag::firstOrCreate(['slug' => str()->slug($tagName)], ['name' => $tagName])->id;
                        }
                        $jobPost->tags()->sync($tagIds);
                    }
                    ScrapingLog::create(['scraping_source_id' => $source->id, 'job_post_id' => $jobPost->id, 'status' => 'success', 'items_found' => 1, 'raw_payload' => $rawLogPayload]);
                    return $jobPost;
                });
            } catch (\Illuminate\Database\QueryException $e) {
                // Race condition: another worker inserted the same fingerprint between
                // our Stage 1 check and this INSERT. Treat as a fingerprint duplicate.
                if (str_contains($e->getMessage(), 'uq_job_posts_fingerprint') ||
                    str_contains($e->getMessage(), 'UNIQUE constraint failed') ||
                    str_contains($e->getMessage(), 'Duplicate entry')) {
                    Log::warning("Fingerprint race-condition caught for: {$finalJobData['title']}");
                    $masterByFingerprint = $this->jobRepo->findByFingerprint($incomingFingerprint);
                    $log = ScrapingLog::create([
                        'scraping_source_id' => $source->id,
                        'status'             => 'duplicate',
                        'items_found'        => 0,
                        'raw_payload'        => $rawLogPayload,
                        'error_message'      => '[Stage 1 Race] Unique constraint caught concurrent duplicate insert.',
                    ]);
                    DuplicateAuditLog::create([
                        'master_job_post_id'  => $masterByFingerprint?->id,
                        'detection_method'    => 'fingerprint',
                        'incoming_fingerprint'=> $incomingFingerprint,
                        'master_fingerprint'  => $masterByFingerprint?->fingerprint,
                        'raw_payload'         => $rawLogPayload,
                    ]);
                    return ['status' => 'duplicate', 'method' => 'fingerprint_race', 'log_id' => $log->id];
                }
                throw $e; // Re-throw unrelated DB errors
            }

            return ['status' => 'success', 'job_post_id' => $jobPost->id];
        } catch (\Exception $e) {
            Log::error("Failed parsing scraped item: " . $e->getMessage());
            $log = ScrapingLog::create(['scraping_source_id' => $source->id, 'status' => 'failed', 'raw_payload' => $rawLogPayload, 'error_message' => $e->getMessage()]);
            return ['status' => 'failed', 'error' => $e->getMessage(), 'log_id' => $log->id];
        }
    }

    protected function runDeterministicPreParser(array $rawData): array
    {
        $parsed = $rawData;
        if (!empty($rawData['deadline_raw'])) $parsed['last_date_to_apply'] = $this->parseDateDeterministic($rawData['deadline_raw']);
        if (!empty($rawData['fee_raw']))      $parsed['application_fee']    = $this->parseFeeDeterministic($rawData['fee_raw']);
        if (!empty($rawData['official_link'])) $parsed['official_website_link'] = filter_var($rawData['official_link'], FILTER_VALIDATE_URL) ? $rawData['official_link'] : null;
        if (!empty($rawData['apply_link']))    $parsed['apply_link']             = filter_var($rawData['apply_link'],    FILTER_VALIDATE_URL) ? $rawData['apply_link']    : null;
        return $parsed;
    }

    protected function validateScrapedJobSchema(array $data): array
    {
        $errors = [];
        if (empty($data['title']) || strlen($data['title']) < 15) $errors['title'] = 'Title too short (min 15 chars).';
        if (empty($data['department_id']) || !is_numeric($data['department_id'])) $errors['department_id'] = 'Missing department.';
        if (empty($data['state_id'])      || !is_numeric($data['state_id']))      $errors['state_id']      = 'Missing state.';
        if (empty($data['last_date_to_apply'])) {
            $errors['last_date_to_apply'] = 'Deadline required.';
        } else {
            try {
                $d = Carbon::parse($data['last_date_to_apply']);
                if ($d->isPast() && !$d->isToday() && empty($data['is_historical'])) $errors['last_date_to_apply'] = "Deadline {$data['last_date_to_apply']} expired.";
            } catch (\Exception) { $errors['last_date_to_apply'] = 'Invalid date format.'; }
        }
        if (empty($data['official_website_link']) && empty($data['apply_link'])) $errors['urls'] = 'A valid URL must be present.';
        return $errors;
    }

    protected function parseDateDeterministic(string $raw): ?string
    {
        if (preg_match('/(\d{2})[-.\\/](\d{2})[-.\\/](\d{4})/', $raw, $m)) return "{$m[3]}-{$m[2]}-{$m[1]}";
        if (preg_match('/(\d{4})[-.\\/](\d{2})[-.\\/](\d{2})/', $raw, $m)) return "{$m[1]}-{$m[2]}-{$m[3]}";
        try { return Carbon::parse($raw)->format('Y-m-d'); } catch (\Exception) { return null; }
    }

    protected function parseFeeDeterministic(string $raw): float
    {
        if (preg_match('/(?:Rs\.?|INR|₹)\s*([\d,]+)/i', $raw, $m)) return (float) str_replace(',', '', $m[1]);
        if (preg_match('/([\d,]+)\s*(?:Rupees|Rs)/i',   $raw, $m)) return (float) str_replace(',', '', $m[1]);
        if (preg_match('/\b(\d+)\b/', $raw, $m))                    return (float) $m[1];
        return 0.00;
    }

    public function classifyPostType(string $title, string $rawText): string
    {
        $t = strtolower($title . ' ' . $rawText);
        if (str_contains($t, 'admit card') || str_contains($t, 'hall ticket') || str_contains($t, 'call letter')) return 'admit_card';
        if (str_contains($t, 'result') || str_contains($t, 'merit list') || str_contains($t, 'cutoff') || str_contains($t, 'scorecard')) return 'result';
        if (str_contains($t, 'answer key') || str_contains($t, 'response sheet')) return 'answer_key';
        if (str_contains($t, 'syllabus') || str_contains($t, 'exam pattern') || str_contains($t, 'scheme of examination')) return 'syllabus';
        if (str_contains($t, 'admission') || str_contains($t, 'entrance exam') || str_contains($t, 'counseling')) return 'admission';
        if (str_contains($t, 'scholarship') || str_contains($t, 'fellowship') || str_contains($t, 'stipend')) return 'scholarship';
        if (str_contains($t, 'notice') || str_contains($t, 'circular') || str_contains($t, 'corrigendum') || str_contains($t, 'postponement')) return 'notice';
        return 'job';
    }

    protected function extractJobPostNodes(string $html, ScrapingSource $source): array
    {
        $driver = app(\App\Domains\Scrapers\Drivers\ScraperDriverManager::class)->getDriverFor($source);
        return $driver->parse($html, $source);
    }

    protected function mapCategorySemantically(?string $text, int $defaultId): int
    {
        if (empty($text)) return $defaultId;
        $l = strtolower($text);
        if (str_contains($l, 'bank') || str_contains($l, 'sbi') || str_contains($l, 'rbi'))
            $c = Category::where('slug', 'banking-finance')->first();
        elseif (str_contains($l, 'railway') || str_contains($l, 'rrb'))
            $c = Category::where('slug', 'railway-jobs')->first();
        elseif (str_contains($l, 'defense') || str_contains($l, 'police') || str_contains($l, 'constable'))
            $c = Category::where('slug', 'defense-jobs')->first();
        elseif (str_contains($l, 'upsc') || str_contains($l, 'ssc') || str_contains($l, 'commission'))
            $c = Category::where('slug', 'upsc-ssc-jobs')->first();
        return isset($c) && $c ? $c->id : $defaultId;
    }

    protected function mapStateSemantically(?string $text, int $defaultId): int
    {
        if (empty($text)) return $defaultId;
        $l = strtolower($text);
        if (str_contains($l, 'uttar pradesh'))     $s = State::where('code', 'UP')->first();
        elseif (str_contains($l, 'maharashtra'))   $s = State::where('code', 'MH')->first();
        elseif (str_contains($l, 'delhi'))         $s = State::where('code', 'DL')->first();
        elseif (str_contains($l, 'karnataka'))     $s = State::where('code', 'KA')->first();
        return isset($s) && $s ? $s->id : $defaultId;
    }

    protected function mapQualificationSemantically(?string $text, int $defaultId): int
    {
        if (empty($text)) return $defaultId;
        $l = strtolower($text);
        if (str_contains($l, 'post graduate') || str_contains($l, 'master'))
            $q = Qualification::where('slug', 'post-graduate')->first();
        elseif (str_contains($l, 'graduate') || str_contains($l, 'bachelor') || str_contains($l, 'b.tech'))
            $q = Qualification::where('slug', 'graduate')->first();
        elseif (str_contains($l, '12th') || str_contains($l, 'intermediate'))
            $q = Qualification::where('slug', '12th-pass')->first();
        elseif (str_contains($l, '10th') || str_contains($l, 'high school'))
            $q = Qualification::where('slug', '10th-pass')->first();
        return isset($q) && $q ? $q->id : $defaultId;
    }
}
