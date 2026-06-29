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
            if ($this->shouldAllowMockFallback()) {
                return [
                    [
                        'title'         => 'MCD Assistant Engineer Civil Recruitment Ingestion 2026',
                        'deadline_raw'  => '15-11-2026',
                        'fee_raw'       => 'Rs 200',
                        'official_link' => 'https://mcdonline.nic.in',
                        'apply_link'    => 'https://mcdonline.nic.in/careers',
                        'category_name' => 'Municipal & Local Boards',
                        'department_name'=> 'Municipal Corporation of Delhi',
                        'raw_text'      => 'Municipal Corporation of Delhi (MCD) Assistant Engineer recruitment. Required B.E./B.Tech Civil. Apply online by 15-11-2026. Fee Rs 200.',
                    ]
                ];
            }
            throw new \App\Domains\Scrapers\Exceptions\ParserValidationException("Municipal scraper driver failed to parse content: Selectors yielded no matching elements.");
        }

        return $extracted;
    }
}
