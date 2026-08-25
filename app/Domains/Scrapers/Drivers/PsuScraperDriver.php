<?php

namespace App\Domains\Scrapers\Drivers;

use App\Models\ScrapingSource;

class PsuScraperDriver extends AbstractScraperDriver
{
    public function supports(ScrapingSource $source): bool
    {
        $url = strtolower($source->source_url);
        $name = strtolower($source->name);
        $driver = $source->selectors_config['driver'] ?? '';

        return str_contains($url, 'psu') || 
               str_contains($url, 'ntpc') || 
               str_contains($url, 'ongc') || 
               str_contains($url, 'bhel') || 
               str_contains($url, 'sail') || 
               str_contains($name, 'psu') || 
               str_contains($name, 'public sector') || 
               $driver === 'psu';
    }

    public function parse(string $content, ScrapingSource $source): array
    {
        $config = $source->selectors_config;
        $extracted = $this->parseWithSelectors($content, $config);

        if (empty($extracted)) {
            throw new \App\Domains\Scrapers\Exceptions\ParserValidationException("PSU scraper driver failed to parse content: Selectors yielded no matching elements.");
        }

        return $extracted;
    }
}
