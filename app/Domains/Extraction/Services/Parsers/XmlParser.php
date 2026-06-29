<?php

namespace App\Domains\Extraction\Services\Parsers;

use Illuminate\Support\Facades\Log;

/**
 * Enterprise XML Parser
 *
 * DOM-based XML parser using DOMDocument with:
 * - Recursive DOM tree traversal
 * - Element names as section headers
 * - RSS/Atom feed support (common in government portals)
 * - Namespace-aware parsing
 * - Malformed XML graceful handling
 */
class XmlParser
{
    /**
     * Extract text from an XML file.
     *
     * @param string $filePath
     * @return string
     */
    public function extractText(string $filePath): string
    {
        if (!file_exists($filePath)) {
            Log::error("XML file not found: {$filePath}");
            return '';
        }

        $result = $this->extractStructured($filePath);
        return $result['text'] ?? '';
    }

    /**
     * Extract structured data from an XML file.
     *
     * @param string $filePath
     * @return array ['text', 'tables', 'headers', 'items', 'metadata']
     */
    public function extractStructured(string $filePath): array
    {
        if (!file_exists($filePath)) {
            Log::error("XML file not found: {$filePath}");
            return $this->emptyResult();
        }

        $content = file_get_contents($filePath);
        if (empty($content)) {
            return $this->emptyResult();
        }

        // Strip BOM if present
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

        $doc = new \DOMDocument();
        $doc->preserveWhiteSpace = false;
        libxml_use_internal_errors(true);

        if (function_exists('libxml_set_external_entity_loader')) {
            libxml_set_external_entity_loader(function ($public, $system, $context) {
                return null;
            });
        }

        if (!$doc->loadXML($content, LIBXML_NONET)) {
            // Try loading as HTML fallback for malformed XML
            if (!$doc->loadHTML($content, LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET)) {
                $errors = libxml_get_errors();
                libxml_clear_errors();
                Log::warning("XML parsing failed with " . count($errors) . " errors. Falling back to strip_tags.");
                return array_merge($this->emptyResult(), ['text' => trim(strip_tags($content))]);
            }
        }
        libxml_clear_errors();

        $rootElement = $doc->documentElement;
        if (!$rootElement) {
            return $this->emptyResult();
        }

        $rootName = strtolower($rootElement->nodeName);

        // Check for RSS/Atom feed formats
        if ($rootName === 'rss' || $rootName === 'feed') {
            return $this->parseRssFeed($doc, $rootName);
        }

        // Generic XML extraction
        $text = '';
        $headers = [];
        $this->walkNode($rootElement, $text, $headers, 0);

        return [
            'text'     => trim($text),
            'tables'   => [],
            'headers'  => $headers,
            'lists'    => [],
            'items'    => [],
            'metadata' => [
                'root_element'  => $rootElement->nodeName,
                'encoding'      => $doc->encoding,
                'version'       => $doc->xmlVersion,
                'namespace'     => $rootElement->namespaceURI,
            ],
        ];
    }

    /**
     * Parse RSS 2.0 or Atom feed into structured items.
     */
    protected function parseRssFeed(\DOMDocument $doc, string $feedType): array
    {
        $xpath = new \DOMXPath($doc);
        $text = '';
        $items = [];
        $headers = [];

        if ($feedType === 'rss') {
            // RSS 2.0 format
            $channelTitle = $xpath->query('//channel/title');
            if ($channelTitle->length > 0) {
                $headers[] = 'Feed: ' . $channelTitle->item(0)->textContent;
                $text .= "# " . $channelTitle->item(0)->textContent . "\n\n";
            }

            $itemNodes = $xpath->query('//channel/item');
            foreach ($itemNodes as $item) {
                $itemData = [
                    'title'       => $this->getNodeText($item, 'title'),
                    'link'        => $this->getNodeText($item, 'link'),
                    'description' => $this->getNodeText($item, 'description'),
                    'pubDate'     => $this->getNodeText($item, 'pubDate'),
                    'category'    => $this->getNodeText($item, 'category'),
                ];
                $items[] = $itemData;

                $text .= "## " . ($itemData['title'] ?: 'Untitled') . "\n";
                if (!empty($itemData['pubDate'])) {
                    $text .= "Date: " . $itemData['pubDate'] . "\n";
                }
                if (!empty($itemData['description'])) {
                    $text .= strip_tags($itemData['description']) . "\n";
                }
                if (!empty($itemData['link'])) {
                    $text .= "Link: " . $itemData['link'] . "\n";
                }
                $text .= "\n";
            }
        } elseif ($feedType === 'feed') {
            // Atom format
            $xpath->registerNamespace('atom', 'http://www.w3.org/2005/Atom');

            $feedTitle = $xpath->query('//atom:title | //title');
            if ($feedTitle->length > 0) {
                $headers[] = 'Feed: ' . $feedTitle->item(0)->textContent;
                $text .= "# " . $feedTitle->item(0)->textContent . "\n\n";
            }

            $entryNodes = $xpath->query('//atom:entry | //entry');
            foreach ($entryNodes as $entry) {
                $itemData = [
                    'title'   => $this->getNodeText($entry, 'title'),
                    'link'    => '',
                    'summary' => $this->getNodeText($entry, 'summary') ?: $this->getNodeText($entry, 'content'),
                    'updated' => $this->getNodeText($entry, 'updated'),
                ];

                // Get link href from attribute
                $linkNodes = $entry->getElementsByTagName('link');
                if ($linkNodes->length > 0) {
                    $itemData['link'] = $linkNodes->item(0)->getAttribute('href');
                }

                $items[] = $itemData;

                $text .= "## " . ($itemData['title'] ?: 'Untitled') . "\n";
                if (!empty($itemData['updated'])) {
                    $text .= "Updated: " . $itemData['updated'] . "\n";
                }
                if (!empty($itemData['summary'])) {
                    $text .= strip_tags($itemData['summary']) . "\n";
                }
                $text .= "\n";
            }
        }

        return [
            'text'     => trim($text),
            'tables'   => [],
            'headers'  => $headers,
            'lists'    => [],
            'items'    => $items,
            'metadata' => [
                'feed_type'  => $feedType === 'rss' ? 'RSS 2.0' : 'Atom',
                'item_count' => count($items),
            ],
        ];
    }

    /**
     * Get text content of a child element by tag name.
     */
    protected function getNodeText(\DOMNode $parent, string $tagName): string
    {
        $nodes = $parent->getElementsByTagName($tagName);
        if ($nodes->length > 0) {
            return trim($nodes->item(0)->textContent);
        }
        return '';
    }

    /**
     * Recursively walk DOM nodes extracting text content.
     */
    protected function walkNode(\DOMNode $node, string &$text, array &$headers, int $depth): void
    {
        if ($node->nodeType === XML_TEXT_NODE) {
            $val = trim($node->nodeValue);
            if (!empty($val)) {
                $text .= $val . "\n";
            }
            return;
        }

        if ($node->nodeType === XML_ELEMENT_NODE) {
            $nodeName = $node->nodeName;

            // Capture element names as headers for top-level elements
            if ($depth <= 2 && $node->hasChildNodes()) {
                $directText = '';
                foreach ($node->childNodes as $child) {
                    if ($child->nodeType === XML_TEXT_NODE && !empty(trim($child->nodeValue))) {
                        $directText = trim($child->nodeValue);
                        break;
                    }
                }
                if (!empty($directText) && strlen($nodeName) > 1) {
                    $headers[] = $nodeName . ': ' . $directText;
                }
            }

            // Add element name as label for deeper context
            if ($depth > 0 && $depth <= 3 && $node->childNodes->length > 0) {
                $hasElementChildren = false;
                foreach ($node->childNodes as $child) {
                    if ($child->nodeType === XML_ELEMENT_NODE) {
                        $hasElementChildren = true;
                        break;
                    }
                }
                if ($hasElementChildren) {
                    $text .= str_repeat('#', min($depth + 1, 6)) . " " . $nodeName . "\n";
                }
            }

            foreach ($node->childNodes as $child) {
                $this->walkNode($child, $text, $headers, $depth + 1);
            }
        }
    }

    /**
     * Return an empty result template.
     */
    protected function emptyResult(): array
    {
        return [
            'text'     => '',
            'tables'   => [],
            'headers'  => [],
            'lists'    => [],
            'items'    => [],
            'metadata' => [],
        ];
    }
}
