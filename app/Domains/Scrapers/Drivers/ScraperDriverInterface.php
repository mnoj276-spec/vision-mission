<?php

namespace App\Domains\Scrapers\Drivers;

use App\Models\ScrapingSource;

interface ScraperDriverInterface
{
    /**
     * Determine if this driver supports the given scraping source.
     */
    public function supports(ScrapingSource $source): bool;

    /**
     * Parse HTML/Response content and extract raw job post nodes.
     *
     * @param string $content Raw HTML/RSS/JSON body
     * @param ScrapingSource $source
     * @return array[] List of job post candidate structures
     */
    public function parse(string $content, ScrapingSource $source): array;
}
