<?php

namespace App\Domains\Scrapers\Drivers;

use App\Models\ScrapingSource;

abstract class AbstractScraperDriver implements ScraperDriverInterface
{
    /**
     * Parse HTML using PHP native DOMDocument and DOMXPath.
     */
    protected function parseWithSelectors(string $html, array $config): array
    {
        if (empty($html) || empty($config['item_selector'])) {
            return [];
        }

        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        // Suppress warnings for malformed HTML common in govt portals
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        $itemXpath = $this->cssToXpath($config['item_selector']);
        $nodes = $xpath->query($itemXpath);

        if (!$nodes || $nodes->length === 0) {
            return [];
        }

        $results = [];
        foreach ($nodes as $node) {
            $titleSelector = $config['title_selector'] ?? '';
            $linkSelector = $config['link_selector'] ?? 'a';
            $deadlineSelector = $config['deadline_selector'] ?? '';

            $title = $this->queryNodeText($xpath, $titleSelector, $node);
            $link = $this->queryNodeAttribute($xpath, $linkSelector, 'href', $node);
            $deadline = $this->queryNodeText($xpath, $deadlineSelector, $node);

            // Clean up extracted links
            if ($link && !str_starts_with($link, 'http')) {
                $base = parse_url($config['source_url'] ?? '', PHP_URL_SCHEME) . '://' . parse_url($config['source_url'] ?? '', PHP_URL_HOST);
                $link = rtrim($base, '/') . '/' . ltrim($link, '/');
            }

            if (!empty($title) && strlen($title) >= 5) {
                $results[] = [
                    'title'         => trim($title),
                    'deadline_raw'  => trim($deadline),
                    'fee_raw'       => 'Rs 100',
                    'official_link' => $link ?: ($config['source_url'] ?? ''),
                    'apply_link'    => $link ?: ($config['source_url'] ?? ''),
                    'raw_text'      => trim($title . ' ' . $deadline . ' Apply online before ' . $deadline),
                ];
            }
        }

        if (empty($results)) {
            $results = $this->heuristicFallbackExtract($html, $config);
        }

        return $results;
    }

    protected function heuristicFallbackExtract(string $html, array $config): array
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        libxml_clear_errors();

        $xpath = new \DOMXPath($dom);
        $links = $xpath->query('//a[@href]');
        
        $results = [];
        $seen = [];
        
        if ($links) {
            foreach ($links as $linkNode) {
                $href = trim($linkNode->getAttribute('href'));
                $title = trim($linkNode->nodeValue);
                
                if (empty($href) || empty($title) || strlen($title) < 10) continue;
                if (str_starts_with($href, '#') || str_starts_with($href, 'javascript:')) continue;
                
                $isPdf = str_ends_with(strtolower(parse_url($href, PHP_URL_PATH) ?? ''), '.pdf');
                $hasKeyword = preg_match('/(recruitment|notice|result|admit card|apply|vacancy|syllabus|advertisement|post|exam|commission|officer)/i', $title);
                
                if ($isPdf || $hasKeyword) {
                    $parent = $linkNode->parentNode;
                    $textContext = $title;
                    while ($parent && !in_array(strtolower($parent->nodeName), ['tr', 'li', 'body'])) {
                        $parent = $parent->parentNode;
                    }
                    if ($parent && in_array(strtolower($parent->nodeName), ['tr', 'li'])) {
                        $textContext = trim($parent->nodeValue);
                    }
                    
                    $deadline = '';
                    if (preg_match('/(\d{2}[\/.-]\d{2}[\/.-]\d{4}|\d{4}[\/.-]\d{2}[\/.-]\d{2})/', $textContext, $m)) {
                        $deadline = $m[1];
                    }
                    
                    if (!str_starts_with($href, 'http')) {
                        $base = parse_url($config['official_url'] ?? $config['source_url'] ?? '', PHP_URL_SCHEME) . '://' . parse_url($config['official_url'] ?? $config['source_url'] ?? '', PHP_URL_HOST);
                        $href = rtrim($base, '/') . '/' . ltrim($href, '/');
                    }
                    
                    $hash = md5($href . $title);
                    if (!isset($seen[$hash])) {
                        $seen[$hash] = true;
                        $results[] = [
                            'title'         => $title,
                            'deadline_raw'  => $deadline,
                            'official_link' => $href,
                            'apply_link'    => $href,
                            'raw_text'      => $textContext,
                        ];
                    }
                    
                    if (count($results) >= 20) break;
                }
            }
        }
        
        return $results;
    }

    /**
     * Convert basic CSS selectors to XPath expressions.
     */
    protected function cssToXpath(string $css, bool $isRelative = false): string
    {
        $css = trim($css);
        if (empty($css)) {
            return $isRelative ? '.' : '/';
        }

        // e.g. "table.views-table tr" => ".//table[contains(@class,'views-table')]//tr"
        $parts = preg_split('/\s+/', $css);
        $xpathParts = [];

        foreach ($parts as $part) {
            if (preg_match('/^([a-zA-Z0-9\-*]+)?(?:\.([a-zA-Z0-9\-_]+))?$/', $part, $matches)) {
                $tag = !empty($matches[1]) ? $matches[1] : '*';
                $class = !empty($matches[2]) ? $matches[2] : null;

                if ($class) {
                    $xpathParts[] = "{$tag}[contains(concat(' ', normalize-space(@class), ' '), ' {$class} ')]";
                } else {
                    $xpathParts[] = $tag;
                }
            } else {
                $xpathParts[] = '*';
            }
        }

        $prefix = $isRelative ? './/' : '//';
        return $prefix . implode('//', $xpathParts);
    }

    /**
     * Query inner text of a node matching basic CSS selector relative to reference node.
     */
    protected function queryNodeText(\DOMXPath $xpath, string $cssSelector, \DOMNode $contextNode): string
    {
        if (empty($cssSelector)) {
            return $contextNode->nodeValue ? trim($contextNode->nodeValue) : '';
        }
        $relXpath = $this->cssToXpath($cssSelector, true);
        $nodes = $xpath->query($relXpath, $contextNode);
        return ($nodes && $nodes->length > 0) ? trim($nodes->item(0)->nodeValue) : '';
    }

    /**
     * Query attribute value of a node matching basic CSS selector relative to reference node.
     */
    protected function queryNodeAttribute(\DOMXPath $xpath, string $cssSelector, string $attribute, \DOMNode $contextNode): string
    {
        if (empty($cssSelector)) {
            if ($contextNode instanceof \DOMElement) {
                return $contextNode->getAttribute($attribute);
            }
            return '';
        }
        $relXpath = $this->cssToXpath($cssSelector, true);
        $nodes = $xpath->query($relXpath, $contextNode);
        if ($nodes && $nodes->length > 0 && $nodes->item(0) instanceof \DOMElement) {
            return $nodes->item(0)->getAttribute($attribute);
        }
        return '';
    }

}
