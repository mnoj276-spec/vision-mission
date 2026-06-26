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
            if ($this->shouldAllowMockFallback()) {
                return [
                    [
                        'title'         => 'UP Police Sub Inspector SI Recruitment Ingestion 2026',
                        'deadline_raw'  => '12-12-2026',
                        'fee_raw'       => 'Rs 400',
                        'official_link' => 'https://uppbpb.gov.in',
                        'apply_link'    => 'https://uppbpb.gov.in/apply',
                        'category_name' => 'Defense & Police',
                        'department_name'=> 'Uttar Pradesh Police Recruitment Board',
                        'raw_text'      => 'Uttar Pradesh Police Recruitment Board (UPPRPB) Sub Inspector recruitment. Graduate required. Apply online by 12-12-2026. Fee Rs 400.',
                    ]
                ];
            }
            throw new \App\Domains\Scrapers\Exceptions\ParserValidationException("Police scraper driver failed to parse content: Selectors yielded no matching elements.");
        }

        return $extracted;
    }
}
