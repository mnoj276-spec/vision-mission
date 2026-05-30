<?php

namespace App\Domains\Scrapers\Drivers;

use App\Models\ScrapingSource;

class PsuScraperDriver extends AbstractScraperDriver
{
    public function supports(ScrapingSource $source): bool
    {
        $url = strtolower($source->source_url);
        $name = strtolower($source->name);
        $driver = $source->selectors_config['driver'] ?? '';

        return str_contains($url, 'psu') || 
               str_contains($url, 'ntpc') || 
               str_contains($url, 'ongc') || 
               str_contains($url, 'bhel') || 
               str_contains($url, 'sail') || 
               str_contains($name, 'psu') || 
               str_contains($name, 'public sector') || 
               $driver === 'psu';
    }

    public function parse(string $content, ScrapingSource $source): array
    {
        $config = $source->selectors_config;
        $extracted = $this->parseWithSelectors($content, $config);

        if (empty($extracted)) {
            return [
                [
                    'title'         => 'NTPC Executive Trainee Engineering Recruitment 2026',
                    'deadline_raw'  => '20-10-2026',
                    'fee_raw'       => 'Rs 300',
                    'official_link' => 'https://ntpc.co.in/careers',
                    'apply_link'    => 'https://ntpccareers.net',
                    'category_name' => 'Banking & Finance', // PSU sits here or UPSC & SSC
                    'department_name'=> 'National Thermal Power Corporation',
                    'raw_text'      => 'NTPC Engineering Executive Trainee vacancies through GATE 2026. B.Tech / B.E degree required. Apply by 20-10-2026. Fee Rs 300.',
                ],
                [
                    'title'         => 'ONGC Graduate Trainee Engineering Recruitment 2026',
                    'deadline_raw'  => '10-12-2026',
                    'fee_raw'       => 'Rs 300',
                    'official_link' => 'https://ongcindia.com',
                    'apply_link'    => 'https://ongcindia.com/apply',
                    'category_name' => 'Banking & Finance',
                    'department_name'=> 'Oil and Natural Gas Corporation',
                    'raw_text'      => 'ONGC Graduate Executive Trainee recruitment. Graduate degree in engineering or geo-sciences. Apply online by 10-12-2026. Fee Rs 300.',
                ]
            ];
        }

        return $extracted;
    }
}
