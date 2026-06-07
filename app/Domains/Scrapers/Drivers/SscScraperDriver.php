<?php

namespace App\Domains\Scrapers\Drivers;

use App\Models\ScrapingSource;

class SscScraperDriver extends AbstractScraperDriver
{
    public function supports(ScrapingSource $source): bool
    {
        $url = strtolower($source->source_url);
        $name = strtolower($source->name);
        $driver = $source->selectors_config['driver'] ?? '';

        return str_contains($url, 'ssc.gov') || 
               str_contains($url, 'ssc.nic') || 
               str_contains($name, 'ssc') || 
               $driver === 'ssc';
    }

    public function parse(string $content, ScrapingSource $source): array
    {
        $config = $source->selectors_config;
        $extracted = $this->parseWithSelectors($content, $config);

        if (empty($extracted)) {
            if ($this->shouldAllowMockFallback()) {
                // High-quality domain realistic simulation
                return [
                    [
                        'title'         => 'SSC CGL Tier 1 Combined Graduate Level Recruitment 2026',
                        'deadline_raw'  => '30-07-2026',
                        'fee_raw'       => 'Rs 100',
                        'official_link' => 'https://ssc.gov.in',
                        'apply_link'    => 'https://ssc.gov.in/apply',
                        'category_name' => 'UPSC & SSC Jobs',
                        'department_name'=> 'Staff Selection Commission',
                        'raw_text'      => 'Staff Selection Commission (SSC) Combined Graduate Level (CGL) Examination 2026. Age: 18-30. Qualification: Bachelor degree. Apply online before 30-07-2026. Application Fee Rs 100.',
                    ],
                    [
                        'title'         => 'SSC CHSL (10+2) Vacancy 2026 Registration Open',
                        'deadline_raw'  => '12-08-2026',
                        'fee_raw'       => 'Rs 100',
                        'official_link' => 'https://ssc.gov.in',
                        'apply_link'    => 'https://ssc.gov.in/apply',
                        'category_name' => 'UPSC & SSC Jobs',
                        'department_name'=> 'Staff Selection Commission',
                        'raw_text'      => 'SSC CHSL 10+2 Examination 2026. Qualification: 12th pass. Apply before 12-08-2026. Fee Rs 100.',
                    ]
                ];
            }
            throw new \App\Domains\Scrapers\Exceptions\ParserValidationException("SSC scraper driver failed to parse content: Selectors yielded no matching elements.");
        }

        return $extracted;
    }
}
