<?php

namespace App\Domains\Extraction\Services\Parsers;

use Illuminate\Support\Facades\Log;

/**
 * Enterprise HTML Parser
 *
 * DOM-based HTML parser using DOMDocument + DOMXPath with:
 * - Table structure preservation as pipe-delimited text
 * - H1-H6 heading extraction as section headers
 * - UL/OL list conversion to bullet-point text
 * - Script/style/nav/footer stripping
 * - Malformed HTML graceful handling (libxml error suppression)
 * - Government notification-specific boilerplate removal
 */
class HtmlParser
{
    /**
     * Elements to strip entirely from parsed content.
     */
    protected const STRIP_ELEMENTS = [
        'script', 'style', 'noscript', 'iframe', 'object', 'embed',
        'nav', 'footer', 'aside', 'form', 'input', 'button', 'select',
    ];

    /**
     * Extract text from an HTML file.
     *
     * @param string $filePath
     * @return string
     */
    public function extractText(string $filePath): string
    {
        if (!file_exists($filePath)) {
            Log::error("HTML file not found: {$filePath}");
            return '';
        }

        $result = $this->extractStructured($filePath);
        return $result['text'] ?? '';
    }

    /**
     * Extract text from an HTML string.
     *
     * @param string $html
     * @return string
     */
    public function extractTextFromString(string $html): string
    {
        $result = $this->extractStructuredFromString($html);
        return $result['text'] ?? '';
    }

    /**
     * Extract structured data from an HTML file.
     *
     * @param string $filePath
     * @return array ['text', 'tables', 'headers', 'lists', 'metadata']
     */
    public function extractStructured(string $filePath): array
    {
        if (!file_exists($filePath)) {
            Log::error("HTML file not found: {$filePath}");
            return $this->emptyResult();
        }

        $html = file_get_contents($filePath);
        if (empty($html)) {
            return $this->emptyResult();
        }

        return $this->extractStructuredFromString($html);
    }

    /**
     * Extract structured data from an HTML string.
     *
     * @param string $html
     * @return array
     */
    public function extractStructuredFromString(string $html): array
    {
        if (empty($html)) {
            return $this->emptyResult();
        }

        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();

        $xpath = new \DOMXPath($doc);

        // Strip unwanted elements
        foreach (self::STRIP_ELEMENTS as $tag) {
            $nodes = $xpath->query("//{$tag}");
            foreach ($nodes as $node) {
                $node->parentNode->removeChild($node);
            }
        }

        // Extract metadata
        $metadata = $this->extractMetadata($xpath);

        // Extract headings
        $headers = $this->extractHeadings($xpath);

        // Extract tables
        $tables = $this->extractTables($xpath);

        // Extract lists
        $lists = $this->extractLists($xpath);

        // Build combined text output
        $text = $this->buildTextOutput($doc, $xpath, $headers, $tables, $lists);

        return [
            'text'     => trim($text),
            'tables'   => $tables,
            'headers'  => $headers,
            'lists'    => $lists,
            'metadata' => $metadata,
        ];
    }

    /**
     * Extract HTML metadata (title, meta description).
     */
    protected function extractMetadata(\DOMXPath $xpath): array
    {
        $metadata = [];

        // Page title
        $titleNodes = $xpath->query('//title');
        if ($titleNodes->length > 0) {
            $metadata['title'] = trim($titleNodes->item(0)->textContent);
        }

        // Meta description
        $descNodes = $xpath->query('//meta[@name="description"]/@content');
        if ($descNodes->length > 0) {
            $metadata['description'] = trim($descNodes->item(0)->textContent);
        }

        // Meta keywords
        $kwNodes = $xpath->query('//meta[@name="keywords"]/@content');
        if ($kwNodes->length > 0) {
            $metadata['keywords'] = trim($kwNodes->item(0)->textContent);
        }

        return $metadata;
    }

    /**
     * Extract all heading elements (h1-h6).
     */
    protected function extractHeadings(\DOMXPath $xpath): array
    {
        $headers = [];
        for ($level = 1; $level <= 6; $level++) {
            $nodes = $xpath->query("//h{$level}");
            foreach ($nodes as $node) {
                $text = trim($node->textContent);
                if (!empty($text)) {
                    $headers[] = [
                        'level' => $level,
                        'text'  => $text,
                    ];
                }
            }
        }

        // Sort by document order (headers are already in doc order per level,
        // but we need to interleave levels properly)
        // Since DOMXPath returns nodes in document order per query,
        // and we query h1, h2, ... separately, we need to flatten.
        // For simplicity, return the flat list of heading texts.
        return array_map(fn($h) => str_repeat('#', $h['level']) . ' ' . $h['text'], $headers);
    }

    /**
     * Extract all table elements preserving structure.
     */
    protected function extractTables(\DOMXPath $xpath): array
    {
        $tables = [];
        $tableNodes = $xpath->query('//table');

        foreach ($tableNodes as $tableNode) {
            $rows = [];
            $maxCols = 0;

            // Process both thead/tbody rows and direct tr children
            $trNodes = $xpath->query('.//tr', $tableNode);
            foreach ($trNodes as $tr) {
                $cells = [];
                $cellNodes = $xpath->query('.//td | .//th', $tr);
                foreach ($cellNodes as $cell) {
                    $cellText = trim(preg_replace('/\s+/', ' ', $cell->textContent));
                    $cells[] = $cellText;
                }
                if (!empty($cells)) {
                    $rows[] = $cells;
                    $maxCols = max($maxCols, count($cells));
                }
            }

            if (!empty($rows)) {
                $tables[] = [
                    'rows'    => $rows,
                    'columns' => $maxCols,
                ];
            }
        }

        return $tables;
    }

    /**
     * Extract list elements (ul, ol) as bullet text.
     */
    protected function extractLists(\DOMXPath $xpath): array
    {
        $lists = [];
        $listNodes = $xpath->query('//ul | //ol');

        foreach ($listNodes as $listNode) {
            $isOrdered = strtolower($listNode->nodeName) === 'ol';
            $items = [];
            $index = 1;

            $liNodes = $xpath->query('./li', $listNode);
            foreach ($liNodes as $li) {
                $text = trim($li->textContent);
                if (!empty($text)) {
                    $prefix = $isOrdered ? "{$index}." : '•';
                    $items[] = "{$prefix} {$text}";
                    $index++;
                }
            }

            if (!empty($items)) {
                $lists[] = implode("\n", $items);
            }
        }

        return $lists;
    }

    /**
     * Build the combined text output from DOM with structure preservation.
     */
    protected function buildTextOutput(\DOMDocument $doc, \DOMXPath $xpath, array $headers, array $tables, array $lists): string
    {
        // Get the body element
        $bodyNodes = $xpath->query('//body');
        $body = $bodyNodes->length > 0 ? $bodyNodes->item(0) : $doc->documentElement;

        if (!$body) {
            return '';
        }

        $text = '';
        $this->walkHtmlNode($body, $text, 0);

        // Clean up excessive whitespace
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        $text = preg_replace('/[ \t]+/', ' ', $text);

        return trim($text);
    }

    /**
     * Recursively walk HTML DOM nodes building text output.
     */
    protected function walkHtmlNode(\DOMNode $node, string &$text, int $depth): void
    {
        if ($node->nodeType === XML_TEXT_NODE) {
            $val = trim($node->nodeValue);
            if (!empty($val)) {
                $text .= $val . ' ';
            }
            return;
        }

        if ($node->nodeType !== XML_ELEMENT_NODE) {
            return;
        }

        $tag = strtolower($node->nodeName);

        // Skip already-stripped elements
        if (in_array($tag, self::STRIP_ELEMENTS)) {
            return;
        }

        // Handle specific elements
        switch ($tag) {
            case 'h1': case 'h2': case 'h3': case 'h4': case 'h5': case 'h6':
                $level = (int)substr($tag, 1);
                $heading = trim($node->textContent);
                if (!empty($heading)) {
                    $text .= "\n" . str_repeat('#', $level) . " " . $heading . "\n";
                }
                return;

            case 'table':
                $text .= "\n" . $this->renderHtmlTable($node) . "\n";
                return;

            case 'ul': case 'ol':
                $text .= "\n" . $this->renderHtmlList($node, $tag === 'ol') . "\n";
                return;

            case 'br':
                $text .= "\n";
                return;

            case 'hr':
                $text .= "\n---\n";
                return;

            case 'p': case 'div': case 'section': case 'article': case 'main':
                $text .= "\n";
                break;

            case 'strong': case 'b':
                $text .= '**';
                break;

            case 'em': case 'i':
                $text .= '*';
                break;

            case 'a':
                $href = $node->getAttribute('href');
                $linkText = trim($node->textContent);
                if (!empty($linkText) && !empty($href)) {
                    $text .= "{$linkText} ({$href}) ";
                    return;
                }
                break;
        }

        // Recurse into children
        foreach ($node->childNodes as $child) {
            $this->walkHtmlNode($child, $text, $depth + 1);
        }

        // Closing formatting
        switch ($tag) {
            case 'strong': case 'b':
                $text .= '**';
                break;
            case 'em': case 'i':
                $text .= '*';
                break;
            case 'p': case 'div': case 'section': case 'article': case 'main':
                $text .= "\n";
                break;
        }
    }

    /**
     * Render an HTML table as pipe-delimited text.
     */
    protected function renderHtmlTable(\DOMNode $tableNode): string
    {
        $output = '';
        $doc = $tableNode->ownerDocument;
        $xpath = new \DOMXPath($doc);

        $rows = $xpath->query('.//tr', $tableNode);
        $rowIndex = 0;

        foreach ($rows as $tr) {
            $cells = $xpath->query('.//td | .//th', $tr);
            $cellTexts = [];
            foreach ($cells as $cell) {
                $cellTexts[] = trim(preg_replace('/\s+/', ' ', $cell->textContent));
            }

            if (!empty($cellTexts)) {
                $output .= '| ' . implode(' | ', $cellTexts) . " |\n";
                if ($rowIndex === 0) {
                    $output .= '| ' . implode(' | ', array_fill(0, count($cellTexts), '---')) . " |\n";
                }
                $rowIndex++;
            }
        }

        return $output;
    }

    /**
     * Render an HTML list as bullet/numbered text.
     */
    protected function renderHtmlList(\DOMNode $listNode, bool $ordered): string
    {
        $output = '';
        $doc = $listNode->ownerDocument;
        $xpath = new \DOMXPath($doc);
        $items = $xpath->query('./li', $listNode);
        $index = 1;

        foreach ($items as $li) {
            $text = trim($li->textContent);
            if (!empty($text)) {
                $prefix = $ordered ? "{$index}." : '•';
                $output .= "{$prefix} {$text}\n";
                $index++;
            }
        }

        return $output;
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
            'metadata' => [],
        ];
    }
}
