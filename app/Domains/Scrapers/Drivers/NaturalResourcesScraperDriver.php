<?php

namespace App\Domains\Scrapers\Drivers;

use App\Models\ScrapingSource;

class NaturalResourcesScraperDriver extends AbstractScraperDriver
{
    public function supports(ScrapingSource $source): bool
    {
        $url = strtolower($source->source_url);
        $name = strtolower($source->name);
        $driver = $source->selectors_config['driver'] ?? '';

        return str_contains($url, 'icar') || 
               str_contains($url, 'forest') || 
               str_contains($url, 'coal') || 
               str_contains($url, 'mining') || 
               str_contains($url, 'nmdc') || 
               str_contains($name, 'agriculture') || 
               str_contains($name, 'forest') || 
               str_contains($name, 'mining') || 
               str_contains($name, 'coal') || 
               $driver === 'natural_resources';
    }

    public function parse(string $content, ScrapingSource $source): array
    {
        $config = $source->selectors_config;
        $extracted = $this->parseWithSelectors($content, $config);

        if (empty($extracted)) {
            throw new \App\Domains\Scrapers\Exceptions\ParserValidationException("Natural Resources scraper driver failed to parse content: Selectors yielded no matching elements.");
        }

        return $extracted;
    }
}
