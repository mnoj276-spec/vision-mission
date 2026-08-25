<?php

namespace App\Domains\Scrapers\Drivers;

use App\Models\ScrapingSource;

class MunicipalScraperDriver extends AbstractScraperDriver
{
    public function supports(ScrapingSource $source): bool
    {
        $url = strtolower($source->source_url);
        $name = strtolower($source->name);
        $driver = $source->selectors_config['driver'] ?? '';

        return str_contains($url, 'municipal') || 
               str_contains($url, 'corporation') || 
               str_contains($url, 'smartcity') || 
               str_contains($url, 'smart-city') || 
               str_contains($name, 'municipal') || 
               str_contains($name, 'corporation') || 
               str_contains($name, 'smart city') || 
               $driver === 'municipal';
    }

    public function parse(string $content, ScrapingSource $source): array
    {
        $config = $source->selectors_config;
        $extracted = $this->parseWithSelectors($content, $config);

        if (empty($extracted)) {

            throw new \App\Domains\Scrapers\Exceptions\ParserValidationException("Municipal scraper driver failed to parse content: Selectors yielded no matching elements.");
        }

        return $extracted;
    }
}
