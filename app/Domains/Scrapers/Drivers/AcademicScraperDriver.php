<?php

namespace App\Domains\Scrapers\Drivers;

use App\Models\ScrapingSource;

class AcademicScraperDriver extends AbstractScraperDriver
{
    public function supports(ScrapingSource $source): bool
    {
        $url = strtolower($source->source_url);
        $name = strtolower($source->name);
        $driver = $source->selectors_config['driver'] ?? '';

        return str_contains($url, 'univ') || 
               str_contains($url, 'edu') || 
               str_contains($url, 'ac.in') || 
               str_contains($url, 'aiims') || 
               str_contains($url, 'isro') || 
               str_contains($url, 'csir') || 
               str_contains($name, 'university') || 
               str_contains($name, 'college') || 
               str_contains($name, 'institute') || 
               str_contains($name, 'research') || 
               str_contains($name, 'education') || 
               $driver === 'academic';
    }

    public function parse(string $content, ScrapingSource $source): array
    {
        $config = $source->selectors_config;
        $extracted = $this->parseWithSelectors($content, $config);

        if (empty($extracted)) {
            throw new \App\Domains\Scrapers\Exceptions\ParserValidationException("Academic scraper driver failed to parse content: Selectors yielded no matching elements.");
        }

        return $extracted;
    }
}
