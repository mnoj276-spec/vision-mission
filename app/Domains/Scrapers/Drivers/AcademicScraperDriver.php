<?php

namespace App\Domains\Scrapers\Drivers;

use App\Models\ScrapingSource;

class AcademicScraperDriver extends AbstractScraperDriver
{
    public function supports(ScrapingSource $source): bool
    {
        $url = strtolower($source->source_url);
        $name = strtolower($source->name);
        $driver = $source->selectors_config['driver'] ?? '';

        return str_contains($url, 'univ') || 
               str_contains($url, 'edu') || 
               str_contains($url, 'ac.in') || 
               str_contains($url, 'aiims') || 
               str_contains($url, 'isro') || 
               str_contains($url, 'csir') || 
               str_contains($name, 'university') || 
               str_contains($name, 'college') || 
               str_contains($name, 'institute') || 
               str_contains($name, 'research') || 
               str_contains($name, 'education') || 
               $driver === 'academic';
    }

    public function parse(string $content, ScrapingSource $source): array
    {
        $config = $source->selectors_config;
        $extracted = $this->parseWithSelectors($content, $config);

        if (empty($extracted)) {
            if ($this->shouldAllowMockFallback()) {
                return [
                    [
                        'title'         => 'Delhi University Assistant Professor Recruitment Ingest 2026',
                        'deadline_raw'  => '30-11-2026',
                        'fee_raw'       => 'Rs 500',
                        'official_link' => 'https://du.ac.in',
                        'apply_link'    => 'https://rec.uod.ac.in',
                        'category_name' => 'Academic & Research',
                        'department_name'=> 'Delhi University Board',
                        'raw_text'      => 'Delhi University recruitment for Assistant Professors. Ph.D./NET required. Apply online by 30-11-2026. Fee Rs 500.',
                    ]
                ];
            }
            throw new \App\Domains\Scrapers\Exceptions\ParserValidationException("Academic scraper driver failed to parse content: Selectors yielded no matching elements.");
        }

        return $extracted;
    }
}
