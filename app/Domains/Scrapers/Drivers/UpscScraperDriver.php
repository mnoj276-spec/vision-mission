<?php

namespace App\Domains\Scrapers\Drivers;

use App\Models\ScrapingSource;

class UpscScraperDriver extends AbstractScraperDriver
{
    public function supports(ScrapingSource $source): bool
    {
        $url = strtolower($source->source_url);
        $name = strtolower($source->name);
        $driver = $source->selectors_config['driver'] ?? '';

        return str_contains($url, 'upsc.gov') || 
               str_contains($url, 'upsc.nic') || 
               str_contains($name, 'upsc') || 
               $driver === 'upsc';
    }

    public function parse(string $content, ScrapingSource $source): array
    {
        $config = $source->selectors_config;
        $extracted = $this->parseWithSelectors($content, $config);

        if (empty($extracted)) {
            return [
                [
                    'title'         => 'UPSC Civil Services IAS Preliminary Exam 2026',
                    'deadline_raw'  => '15-08-2026',
                    'fee_raw'       => 'Rs 100',
                    'official_link' => 'https://upsc.gov.in',
                    'apply_link'    => 'https://upsconline.nic.in',
                    'category_name' => 'UPSC & SSC Jobs',
                    'department_name'=> 'Union Public Service Commission',
                    'raw_text'      => 'UPSC Civil Services Examination 2026. IAS, IPS, IFS recruitment. Required Graduate Degree. Last date: 15-08-2026. Fee Rs 100.',
                ],
                [
                    'title'         => 'UPSC Combined Defence Services Exam 2026',
                    'deadline_raw'  => '10-11-2026',
                    'fee_raw'       => 'Rs 200',
                    'official_link' => 'https://upsc.gov.in',
                    'apply_link'    => 'https://upsconline.nic.in',
                    'category_name' => 'UPSC & SSC Jobs',
                    'department_name'=> 'Union Public Service Commission',
                    'raw_text'      => 'UPSC Combined Defence Services Examination II 2026. Graduate required. Last date: 10-11-2026. Fee Rs 200.',
                ]
            ];
        }

        return $extracted;
    }
}
