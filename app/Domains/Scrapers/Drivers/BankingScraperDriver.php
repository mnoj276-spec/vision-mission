<?php

namespace App\Domains\Scrapers\Drivers;

use App\Models\ScrapingSource;

class BankingScraperDriver extends AbstractScraperDriver
{
    public function supports(ScrapingSource $source): bool
    {
        $url = strtolower($source->source_url);
        $name = strtolower($source->name);
        $driver = $source->selectors_config['driver'] ?? '';

        return str_contains($url, 'bank') || 
               str_contains($url, 'sbi') || 
               str_contains($url, 'rbi') || 
               str_contains($url, 'ibps') || 
               str_contains($name, 'bank') || 
               str_contains($name, 'finance') || 
               $driver === 'banking';
    }

    public function parse(string $content, ScrapingSource $source): array
    {
        $config = $source->selectors_config;
        $extracted = $this->parseWithSelectors($content, $config);

        if (empty($extracted)) {
            throw new \App\Domains\Scrapers\Exceptions\ParserValidationException("Banking scraper driver failed to parse content: Selectors yielded no matching elements.");
        }

        return $extracted;
    }
}
