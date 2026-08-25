<?php

namespace App\Domains\Scrapers\Drivers;

use App\Models\ScrapingSource;

class SscScraperDriver extends AbstractScraperDriver
{
    public function supports(ScrapingSource $source): bool
    {
        $url = strtolower($source->source_url);
        $name = strtolower($source->name);
        $driver = $source->selectors_config['driver'] ?? '';

        return str_contains($url, 'ssc.gov') || 
               str_contains($url, 'ssc.nic') || 
               str_contains($name, 'ssc') || 
               $driver === 'ssc';
    }

    public function parse(string $content, ScrapingSource $source): array
    {
        $config = $source->selectors_config;
        $extracted = $this->parseWithSelectors($content, $config);

        if (empty($extracted)) {
            if (str_contains($content, 'Headless Engine Disabled')) {
                throw new \App\Domains\Scrapers\Exceptions\ParserValidationException("SSC scraper driver failed: Headless browser mock was disabled. Real Playwright implementation is required.");
            }
            if (str_contains($content, '<app-root></app-root>')) {
                throw new \App\Domains\Scrapers\Exceptions\ParserValidationException("SSC scraper driver failed: Target page is an Angular SPA requiring client-side JavaScript rendering, but standard HTTP client was used.");
            }
            throw new \App\Domains\Scrapers\Exceptions\ParserValidationException("SSC scraper driver failed to parse content: Selectors yielded no matching elements.");
        }

        return $extracted;
    }
}
