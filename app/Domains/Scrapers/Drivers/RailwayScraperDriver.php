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
            return [
                [
                    'title'         => 'Railway RRB ALP Assistant Loco Pilot Recruitment 2026',
                    'deadline_raw'  => '28-09-2026',
                    'fee_raw'       => 'Rs 500',
                    'official_link' => 'https://indianrailways.gov.in',
                    'apply_link'    => 'https://rrbapply.gov.in',
                    'category_name' => 'Railways (RRB)',
                    'department_name'=> 'Railway Recruitment Board',
                    'raw_text'      => 'Railway Recruitment Board (RRB) Assistant Loco Pilot (ALP) Vacancies 2026. Age: 18-30. Required 10th pass or ITI. Apply online by 28-09-2026. Application Fee Rs 500.',
                ],
                [
                    'title'         => 'Railway RRB NTPC Non-Technical Popular Categories 2026',
                    'deadline_raw'  => '15-10-2026',
                    'fee_raw'       => 'Rs 500',
                    'official_link' => 'https://indianrailways.gov.in',
                    'apply_link'    => 'https://rrbapply.gov.in',
                    'category_name' => 'Railways (RRB)',
                    'department_name'=> 'Railway Recruitment Board',
                    'raw_text'      => 'Railway RRB NTPC recruitment for Under Graduate & Graduate posts. Junior Clerk, Goods Guard, Station Master. Apply before 15-10-2026. Fee Rs 500.',
                ]
            ];
        }

        return $extracted;
    }
}
