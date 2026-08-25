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
            throw new \App\Domains\Scrapers\Exceptions\ParserValidationException("Default HTML scraper driver failed to parse content: Selectors yielded no matching elements.");
        }

        return $extracted;
    }
}
