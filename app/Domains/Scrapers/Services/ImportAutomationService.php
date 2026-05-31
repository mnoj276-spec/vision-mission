<?php

namespace App\Domains\Scrapers\Services;

use App\Models\ScrapingSource;
use Illuminate\Support\Facades\Log;

class ImportAutomationService
{
    public function __construct(protected ScrapingService $scrapingService) {}

    public function importJobs(array $payload, ScrapingSource $source): array
    {
        Log::info("Importing job postings via automation pipeline...");
        $payload['post_type'] = 'job';
        return $this->scrapingService->processScrapedItem($source, $payload);
    }

    public function importResults(array $payload, ScrapingSource $source): array
    {
        Log::info("Importing exam results via automation pipeline...");
        $payload['post_type'] = 'result';
        return $this->scrapingService->processScrapedItem($source, $payload);
    }

    public function importAdmitCards(array $payload, ScrapingSource $source): array
    {
        Log::info("Importing admit cards via automation pipeline...");
        $payload['post_type'] = 'admit_card';
        return $this->scrapingService->processScrapedItem($source, $payload);
    }

    public function importAnswerKeys(array $payload, ScrapingSource $source): array
    {
        Log::info("Importing answer keys via automation pipeline...");
        $payload['post_type'] = 'answer_key';
        return $this->scrapingService->processScrapedItem($source, $payload);
    }

    public function importSyllabi(array $payload, ScrapingSource $source): array
    {
        Log::info("Importing syllabi via automation pipeline...");
        $payload['post_type'] = 'syllabus';
        return $this->scrapingService->processScrapedItem($source, $payload);
    }

    public function importCutoffs(array $payload, ScrapingSource $source): array
    {
        Log::info("Importing cutoffs via automation pipeline...");
        $payload['post_type'] = 'cutoff';
        return $this->scrapingService->processScrapedItem($source, $payload);
    }
}
