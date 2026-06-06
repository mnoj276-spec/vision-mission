<?php

namespace App\Domains\Scrapers\Drivers;

use App\Models\ScrapingSource;

class StatePscScraperDriver extends AbstractScraperDriver
{
    public function supports(ScrapingSource $source): bool
    {
        $url = strtolower($source->source_url);
        $name = strtolower($source->name);
        $driver = $source->selectors_config['driver'] ?? '';

        return str_contains($url, 'psc') || 
               str_contains($url, 'gpsc') || 
               str_contains($url, 'uppsc') || 
               str_contains($url, 'mpsc') || 
               str_contains($name, 'psc') || 
               str_contains($name, 'state commission') || 
               $driver === 'state_psc';
    }

    public function parse(string $content, ScrapingSource $source): array
    {
        $config = $source->selectors_config;
        $extracted = $this->parseWithSelectors($content, $config);

        if (empty($extracted)) {
            if ($this->shouldAllowMockFallback()) {
                return [
                    [
                        'title'         => 'Goa PSC Assistant Director Recruitment 2026',
                        'deadline_raw'  => '18-11-2026',
                        'fee_raw'       => 'Rs 500',
                        'official_link' => 'https://gpsc.goa.gov.in',
                        'apply_link'    => 'https://gpsc.goa.gov.in/apply',
                        'category_name' => 'UPSC & SSC Jobs',
                        'department_name'=> 'Goa Public Service Commission',
                        'state_name'    => 'Goa',
                        'raw_text'      => 'Goa PSC Assistant Director vacancies. Required B.Tech degree or equivalent. State of Goa. Apply by 18-11-2026. Application Fee Rs 500.',
                    ],
                    [
                        'title'         => 'UPPSC Assistant Engineer Combined Services Exam 2026',
                        'deadline_raw'  => '05-12-2026',
                        'fee_raw'       => 'Rs 225',
                        'official_link' => 'https://uppsc.up.nic.in',
                        'apply_link'    => 'https://uppsc.up.nic.in/apply',
                        'category_name' => 'UPSC & SSC Jobs',
                        'department_name'=> 'Uttar Pradesh Public Service Commission',
                        'state_name'    => 'Uttar Pradesh',
                        'raw_text'      => 'UPPSC Assistant Engineer recruitment. Required engineering degree. State of Uttar Pradesh. Apply online by 05-12-2026. Fee Rs 225.',
                    ]
                ];
            }
            throw new \App\Domains\Scrapers\Exceptions\ParserValidationException("State PSC scraper driver failed to parse content: Selectors yielded no matching elements.");
        }

        return $extracted;
    }
}
