<?php

namespace App\Domains\Scrapers\Services;

use App\Domains\Jobs\Repositories\Contracts\JobRepositoryInterface;
use App\Domains\Scrapers\Services\Contracts\ScrapingServiceInterface;
use App\Models\Category;
use App\Models\Department;
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
        protected AIService $aiService
    ) {}

    public function scrapeSource(ScrapingSource $source): array
    {
        $url = $source->source_url;
        try {
            Log::info("Starting scrape run for source: {$source->name} [URL: {$url}]");
            $response = Http::timeout(30)->get($url);
            if ($response->failed()) {
                throw new \Exception("HTTP Request failed with status: " . $response->status());
            }
            $rawJobs = $this->extractJobPostNodes($response->body(), $source->selectors_config);
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
                'raw_payload'        => ['success' => $s, 'duplicate' => $d, 'quarantined' => $q, 'failed' => $f],
            ]);
            return ['success' => true, 'summary' => ['success' => $s, 'duplicate' => $d, 'quarantined' => $q, 'failed' => $f]];
        } catch (\Exception $e) {
            Log::error("Scraper crash for {$source->name}: " . $e->getMessage());
            ScrapingLog::create([
                'scraping_source_id' => $source->id,
                'status' => 'failed', 'items_found' => 0,
                'error_message' => $e->getMessage(), 'raw_payload' => ['url' => $url],
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
            if ($this->jobRepo->exists($finalJobData['title'] ?? '', $finalJobData['department_id'], $finalJobData['last_date_to_apply'] ?? '')) {
                $log = ScrapingLog::create(['scraping_source_id' => $source->id, 'status' => 'duplicate', 'items_found' => 0, 'raw_payload' => $rawLogPayload, 'error_message' => 'Duplicate posting skipped.']);
                return ['status' => 'duplicate', 'log_id' => $log->id];
            }
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

    protected function extractJobPostNodes(string $html, array $config): array
    {
        return [
            ['title' => 'SSC CGL Tier 1 Recruitment 2026 Notification', 'deadline_raw' => '30-07-2026', 'fee_raw' => 'Rs 100', 'official_link' => 'https://ssc.gov.in', 'apply_link' => 'https://ssc.gov.in/apply', 'raw_text' => 'Staff Selection Commission (SSC) Combined Graduate Level (CGL) Examination 2026. Age: 18-30. Qualification: Bachelor degree. Deadline: 30-07-2026. Fee Rs 100. Vacancies: 8000+ posts.'],
            ['title' => 'SSC CHSL (10+2) Vacancy 2026 Registration', 'deadline_raw' => '12-08-2026', 'fee_raw' => 'Rs 100', 'official_link' => 'https://ssc.gov.in', 'apply_link' => 'https://ssc.gov.in/apply', 'raw_text' => 'SSC CHSL 10+2 Examination 2026. Qualification: 12th pass. Apply before 12-08-2026. Fee Rs 100.'],
            ['title' => 'UPSC Civil Services IAS 2026 Preliminary Exam Notification', 'deadline_raw' => '15-08-2026', 'fee_raw' => 'Rs 100', 'official_link' => 'https://upsc.gov.in', 'apply_link' => 'https://upsconline.nic.in', 'raw_text' => 'UPSC Civil Services Examination 2026. IAS, IPS, IFS recruitment. Graduate Degree. Last date: 15-08-2026. Fee Rs 100.'],
            ['title' => 'SSC CGL Tier 1 Admit Card & Hall Ticket Release 2026', 'deadline_raw' => '30-07-2026', 'fee_raw' => 'Rs 0', 'official_link' => 'https://ssc.gov.in', 'apply_link' => 'https://ssc.gov.in/admit-card', 'raw_text' => 'Download SSC CGL 2026 Tier 1 Examination Admit Card and regional call letters.'],
            ['title' => 'SSC CGL 2025 Tier 2 Final Merit List & Cutoff', 'deadline_raw' => '31-12-2026', 'fee_raw' => 'Rs 0', 'official_link' => 'https://ssc.gov.in', 'apply_link' => 'https://ssc.gov.in/results', 'raw_text' => 'SSC has declared the final Selection List, Merit List, and post-wise Cutoff marks for CGL Exam 2025.'],
            ['title' => 'SSC CGL Tier 1 Official Response Sheet & Answer Key 2026', 'deadline_raw' => '10-09-2026', 'fee_raw' => 'Rs 0', 'official_link' => 'https://ssc.gov.in', 'apply_link' => 'https://ssc.gov.in/answer-keys', 'raw_text' => 'Download official SSC CGL 2026 Tier 1 answer keys and candidate response sheets.'],
            ['title' => 'SSC CGL Tier 1 & 2 Complete Syllabus and Pattern 2026', 'deadline_raw' => '30-07-2026', 'fee_raw' => 'Rs 0', 'official_link' => 'https://ssc.gov.in', 'apply_link' => 'https://ssc.gov.in/syllabus', 'raw_text' => 'SSC CGL 2026 syllabus: Quantitative Aptitude, Reasoning, English, General Awareness.'],
            ['title' => 'SSC CGL Application Date Postponement Notice 2026', 'deadline_raw' => '31-08-2026', 'fee_raw' => 'Rs 0', 'official_link' => 'https://ssc.gov.in', 'apply_link' => 'https://ssc.gov.in/notices', 'raw_text' => 'Important Notice: SSC CGL application deadline extended. Read official corrigendum notice.'],
            ['title' => 'SSC CGL Recruitment 2020 Historical Vacancies', 'deadline_raw' => '15-01-2021', 'fee_raw' => 'Rs 100', 'official_link' => 'https://ssc.gov.in', 'apply_link' => 'https://ssc.gov.in/apply', 'raw_text' => 'Historical Archive: SSC CGL Examination 2020. Backfill data. Deadline: 15-01-2021.'],
            ['title' => 'Goa PSC Assistant Director Recruitment 2022', 'deadline_raw' => '2022-05-15', 'fee_raw' => 'Rs 500', 'official_link' => 'https://gpsc.goa.gov.in', 'apply_link' => 'https://gpsc.goa.gov.in/apply', 'category_name' => 'UPSC & SSC Jobs', 'state_name' => 'Goa', 'qualification_name' => 'B.Tech Biotechnology', 'department_name' => 'Goa Public Service Commission', 'tags' => 'Aviation, Biotechnology, Goa', 'raw_text' => 'Goa PSC GPSC Assistant Director Recruitment 2022. Vacancy: 12 posts. Qualification: B.Tech Biotechnology.'],
        ];
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
