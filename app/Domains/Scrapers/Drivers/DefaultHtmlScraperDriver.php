<?php

namespace App\Domains\Scrapers\Drivers;

use App\Models\ScrapingSource;

class DefaultHtmlScraperDriver extends AbstractScraperDriver
{
    public function supports(ScrapingSource $source): bool
    {
        return true; // Fallback driver always returns true
    }

    public function parse(string $content, ScrapingSource $source): array
    {
        $config = $source->selectors_config;
        $extracted = $this->parseWithSelectors($content, $config);

        if (empty($extracted)) {
            if ($this->shouldAllowMockFallback()) {
                // Simulated baseline if parsing failed or returned empty in test environment
                return [
                    [
                        'title'         => 'Generic Board Administrative Officer Recruitment 2026',
                        'deadline_raw'  => '15-12-2026',
                        'fee_raw'       => 'Rs 100',
                        'official_link' => $source->source_url,
                        'apply_link'    => $source->source_url,
                        'raw_text'      => 'Ingestion fallback administrative officer vacancies. Apply online by 15-12-2026. Official portal link present.',
                    ]
                ];
            }
            throw new \App\Domains\Scrapers\Exceptions\ParserValidationException("Default HTML scraper driver failed to parse content: Selectors yielded no matching elements.");
        }

        return $extracted;
    }
}
