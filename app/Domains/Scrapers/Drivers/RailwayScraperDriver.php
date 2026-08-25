<?php

namespace App\Domains\Scrapers\Drivers;

use App\Models\ScrapingSource;

class RailwayScraperDriver extends AbstractScraperDriver
{
    public function supports(ScrapingSource $source): bool
    {
        $url = strtolower($source->source_url);
        $name = strtolower($source->name);
        $driver = $source->selectors_config['driver'] ?? '';

        return str_contains($url, 'railway') || 
               str_contains($url, 'rrb') || 
               str_contains($name, 'railway') || 
               str_contains($name, 'rrb') || 
               $driver === 'railway';
    }

    public function parse(string $content, ScrapingSource $source): array
    {
        $config = $source->selectors_config;
        $extracted = $this->parseWithSelectors($content, $config);

        if (empty($extracted)) {
            throw new \App\Domains\Scrapers\Exceptions\ParserValidationException("Railway scraper driver failed to parse content: Selectors yielded no matching elements.");
        }

        return $extracted;
    }
}
