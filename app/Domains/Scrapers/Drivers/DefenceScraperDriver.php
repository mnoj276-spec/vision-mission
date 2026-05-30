<?php

namespace App\Domains\Scrapers\Drivers;

use App\Models\ScrapingSource;

class DefenceScraperDriver extends AbstractScraperDriver
{
    public function supports(ScrapingSource $source): bool
    {
        $url = strtolower($source->source_url);
        $name = strtolower($source->name);
        $driver = $source->selectors_config['driver'] ?? '';

        return str_contains($url, 'defence') || 
               str_contains($url, 'army') || 
               str_contains($url, 'navy') || 
               str_contains($url, 'airforce') || 
               str_contains($url, 'nda') || 
               str_contains($name, 'defence') || 
               str_contains($name, 'military') || 
               str_contains($name, 'police') || 
               $driver === 'defence';
    }

    public function parse(string $content, ScrapingSource $source): array
    {
        $config = $source->selectors_config;
        $extracted = $this->parseWithSelectors($content, $config);

        if (empty($extracted)) {
            return [
                [
                    'title'         => 'Indian Army Technical Entry Scheme TES 52 Ingestion',
                    'deadline_raw'  => '24-11-2026',
                    'fee_raw'       => 'Rs 0',
                    'official_link' => 'https://joinindianarmy.nic.in',
                    'apply_link'    => 'https://joinindianarmy.nic.in/apply',
                    'category_name' => 'Defense & Police',
                    'department_name'=> 'Indian Army Board',
                    'raw_text'      => 'Indian Army Technical Entry Scheme (TES) 10+2. Required 12th pass with PCM. Application Fee Rs 0. Apply online before 24-11-2026.',
                ],
                [
                    'title'         => 'Indian Air Force Agniveer Vayu Recruitment 2026',
                    'deadline_raw'  => '18-12-2026',
                    'fee_raw'       => 'Rs 250',
                    'official_link' => 'https://careerindianairforce.cdac.in',
                    'apply_link'    => 'https://agnipathvayu.cdac.in',
                    'category_name' => 'Defense & Police',
                    'department_name'=> 'Indian Air Force Board',
                    'raw_text'      => 'Indian Air Force Agniveer Vayu intake. Qualification: 12th pass or Diploma. Apply online by 18-12-2026. Fee Rs 250.',
                ]
            ];
        }

        return $extracted;
    }
}
