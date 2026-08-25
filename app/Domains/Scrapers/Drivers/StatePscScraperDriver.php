<?php

namespace App\Domains\Scrapers\Drivers;

use App\Models\ScrapingSource;

class StatePscScraperDriver extends AbstractScraperDriver
{
    public function supports(ScrapingSource $source): bool
    {
        $url = strtolower($source->source_url);
        $name = strtolower($source->name);
        $driver = $source->selectors_config['driver'] ?? '';

        return str_contains($url, 'psc') || 
               str_contains($url, 'gpsc') || 
               str_contains($url, 'uppsc') || 
               str_contains($url, 'mpsc') || 
               str_contains($name, 'psc') || 
               str_contains($name, 'state commission') || 
               $driver === 'state_psc';
    }

    public function parse(string $content, ScrapingSource $source): array
    {
        $config = $source->selectors_config;
        $extracted = $this->parseWithSelectors($content, $config);

        if (empty($extracted)) {
            throw new \App\Domains\Scrapers\Exceptions\ParserValidationException("State PSC scraper driver failed to parse content: Selectors yielded no matching elements.");
        }

        return $extracted;
    }
}
