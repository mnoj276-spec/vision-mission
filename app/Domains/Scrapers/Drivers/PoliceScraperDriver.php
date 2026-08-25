<?php

namespace App\Domains\Scrapers\Drivers;

use App\Models\ScrapingSource;

class PoliceScraperDriver extends AbstractScraperDriver
{
    public function supports(ScrapingSource $source): bool
    {
        $url = strtolower($source->source_url);
        $name = strtolower($source->name);
        $driver = $source->selectors_config['driver'] ?? '';

        return str_contains($url, 'police') || 
               str_contains($url, 'ksp.gov') || 
               str_contains($url, 'uppbpb') || 
               str_contains($name, 'police') || 
               str_contains($name, 'constable') || 
               $driver === 'police';
    }

    public function parse(string $content, ScrapingSource $source): array
    {
        $config = $source->selectors_config;
        $extracted = $this->parseWithSelectors($content, $config);

        if (empty($extracted)) {
            throw new \App\Domains\Scrapers\Exceptions\ParserValidationException("Police scraper driver failed to parse content: Selectors yielded no matching elements.");
        }

        return $extracted;
    }
}
