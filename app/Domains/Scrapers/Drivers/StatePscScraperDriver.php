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
        $url = strtolower($source->source_url);
        
        // Custom UPPSC parsing
        if (str_contains($url, 'uppsc.up.nic.in')) {
            return $this->parseUppsc($content, $source);
        }

        $config = $source->selectors_config;
        $extracted = $this->parseWithSelectors($content, $config);

        if (empty($extracted)) {
            throw new \App\Domains\Scrapers\Exceptions\ParserValidationException("State PSC scraper driver failed to parse content: Selectors yielded no matching elements.");
        }

        return $extracted;
    }

    private function parseUppsc(string $html, ScrapingSource $source): array
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        libxml_clear_errors();
        
        $xpath = new \DOMXPath($dom);
        // Look for links containing Open_PDF.aspx
        $links = $xpath->query('//a[contains(@href, "Open_PDF.aspx")]');
        
        $results = [];
        $seen = [];
        
        if ($links) {
            foreach ($links as $linkNode) {
                $href = trim($linkNode->getAttribute('href'));
                // Inner text often has spans with dates, e.g. "08 Sep 2026 NOTICE..."
                $title = trim(preg_replace('/\s+/', ' ', $linkNode->nodeValue));
                
                if (empty($href) || empty($title)) continue;
                
                // Construct absolute URL
                $base = 'https://uppsc.up.nic.in/';
                $absoluteLink = $base . ltrim($href, '/');
                
                $hash = md5($absoluteLink . $title);
                if (!isset($seen[$hash])) {
                    $seen[$hash] = true;
                    
                    // Extract deadline if present in the text (e.g. "08 Sep 2026")
                    $deadline = '';
                    if (preg_match('/(\d{2}\s+[a-zA-Z]{3}\s+\d{4})/', $title, $m)) {
                        $deadline = $m[1];
                    }
                    
                    $results[] = [
                        'title'         => $title,
                        'deadline_raw'  => $deadline,
                        'official_link' => $absoluteLink,
                        'apply_link'    => $absoluteLink,
                        'raw_text'      => $title,
                    ];
                }
            }
        }
        
        if (empty($results)) {
            throw new \App\Domains\Scrapers\Exceptions\ParserValidationException("UPPSC scraper driver failed to parse content: Could not find Open_PDF links.");
        }
        
        
        return $results;
    }
}
