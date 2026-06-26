<?php

namespace App\Domains\Scrapers\Drivers;

use App\Models\ScrapingSource;

class HighCourtScraperDriver extends AbstractScraperDriver
{
    public function supports(ScrapingSource $source): bool
    {
        $url = strtolower($source->source_url);
        $name = strtolower($source->name);
        $driver = $source->selectors_config['driver'] ?? '';

        return str_contains($url, 'court') || 
               str_contains($url, 'hc.nic') || 
               str_contains($name, 'court') || 
               str_contains($name, 'judicial') || 
               $driver === 'high_court';
    }

    public function parse(string $content, ScrapingSource $source): array
    {
        $config = $source->selectors_config;
        $extracted = $this->parseWithSelectors($content, $config);

        if (empty($extracted)) {
            if ($this->shouldAllowMockFallback()) {
                return [
                    [
                        'title'         => 'Delhi High Court Senior Judicial Assistant Ingest 2026',
                        'deadline_raw'  => '28-10-2026',
                        'fee_raw'       => 'Rs 500',
                        'official_link' => 'https://delhihighcourt.nic.in',
                        'apply_link'    => 'https://delhihighcourt.nic.in/apply',
                        'category_name' => 'Judicial Services',
                        'department_name'=> 'Delhi High Court Board',
                        'raw_text'      => 'Delhi High Court Senior Judicial Assistant Recruitment 2026. Required Law Graduate. Apply by 28-10-2026. Application Fee Rs 500.',
                    ]
                ];
            }
            throw new \App\Domains\Scrapers\Exceptions\ParserValidationException("High Court scraper driver failed to parse content: Selectors yielded no matching elements.");
        }

        return $extracted;
    }
}
