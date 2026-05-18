<?php

namespace App\Domains\Scrapers\Services\Contracts;

use App\Models\ScrapingSource;

interface ScrapingServiceInterface
{
    /**
     * Run the full scraping pipeline for a given source.
     *
     * @return array{success: bool, summary?: array, error?: string}
     */
    public function scrapeSource(ScrapingSource $source): array;

    /**
     * Classify a scraped post into its canonical post type
     * (job, result, admit_card, answer_key, syllabus, notice, admission, scholarship).
     */
    public function classifyPostType(string $title, string $rawText): string;
}
