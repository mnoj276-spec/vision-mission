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
            if ($this->shouldAllowMockFallback()) {
                return [
                    [
                        'title'         => 'Coal India Management Trainee Job Ingestion 2026',
                        'deadline_raw'  => '22-12-2026',
                        'fee_raw'       => 'Rs 1000',
                        'official_link' => 'https://coalindia.in',
                        'apply_link'    => 'https://coalindia.in/careers',
                        'category_name' => 'Natural Resources',
                        'department_name'=> 'Coal India Limited Board',
                        'raw_text'      => 'Coal India Limited recruitment for Management Trainees. Engineering / MBA degree. Apply online by 22-12-2026. Fee Rs 1000.',
                    ]
                ];
            }
            throw new \App\Domains\Scrapers\Exceptions\ParserValidationException("Natural Resources scraper driver failed to parse content: Selectors yielded no matching elements.");
        }

        return $extracted;
    }
}
