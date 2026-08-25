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
        
        // The API response may be wrapped in HTML (e.g. <html><body><pre>{json}</pre>...)
        // Extract the raw JSON if present
        $jsonContent = $content;
        if (preg_match('/<pre[^>]*>(.*?)<\/pre>/s', $content, $matches)) {
            $jsonContent = html_entity_decode($matches[1]);
        }
        
        $decoded = json_decode($jsonContent, true);
        if (json_last_error() === JSON_ERROR_NONE && isset($decoded['data'])) {
            $extracted = [];
            foreach ($decoded['data'] as $item) {
                $attachmentUrl = '';
                if (!empty($item['attachments'][0]['path'])) {
                    // API paths look like "uploads\masterData\NoticeBoards\file.pdf"
                    $normalizedPath = str_replace('\\', '/', $item['attachments'][0]['path']);
                    $attachmentUrl = 'https://ssc.gov.in/api/attachment/' . $normalizedPath;
                }
                
                $extracted[] = [
                    'title' => $item['headline'] ?? '',
                    'url' => $attachmentUrl,
                    'deadline' => $item['endDate'] ?? null,
                    'published_at' => $item['createdAt'] ?? null,
                ];
            }
            return $extracted;
        }

        // Fallback to HTML parsing if not JSON
        $extracted = $this->parseWithSelectors($content, $config);

        if (empty($extracted)) {
            if (str_contains($content, 'Headless Engine Disabled')) {
                throw new \App\Domains\Scrapers\Exceptions\ParserValidationException("SSC scraper driver failed: Headless browser mock was disabled. Real Playwright implementation is required.");
            }
            if (str_contains($content, '<app-root></app-root>')) {
                throw new \App\Domains\Scrapers\Exceptions\ParserValidationException("SSC scraper driver failed: Target page is an Angular SPA requiring client-side JavaScript rendering, but standard HTTP client was used.");
            }
            throw new \App\Domains\Scrapers\Exceptions\ParserValidationException("SSC scraper driver failed to parse content: Selectors yielded no matching elements.");
        }

        return $extracted;
    }
}
