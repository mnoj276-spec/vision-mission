<?php

namespace App\Services;

class HtmlSanitizer
{
    /**
     * Sanitize plain text (strips all HTML tags and encodes/decodes special characters).
     *
     * @param string|null $value
     * @return string
     */
    public static function sanitizeString(?string $value): string
    {
        if ($value === null) {
            return '';
        }
        // Completely strip script tags and their contents
        $clean = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $value);
        return htmlspecialchars(strip_tags($clean), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitize rich HTML to prevent stored XSS while preserving formatting tags.
     *
     * @param string|null $html
     * @return string
     */
    public static function sanitizeHtml(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        // Create DOMDocument
        $doc = new \DOMDocument();
        $doc->preserveWhiteSpace = true;
        
        // Suppress errors for malformed HTML
        libxml_use_internal_errors(true);
        
        // Secure DTD / entities loading
        if (function_exists('libxml_set_external_entity_loader')) {
            libxml_set_external_entity_loader(function () {
                return null;
            });
        }

        // Wrap content with root div and UTF-8 encoding marker to maintain encoding
        $wrappedHtml = '<?xml encoding="utf-8" ?><div>' . $html . '</div>';
        $doc->loadHTML($wrappedHtml, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING);
        libxml_clear_errors();

        $root = $doc->getElementsByTagName('div')->item(0);
        if (!$root) {
            return '';
        }

        // Allowed safe presentation tags
        $allowedTags = ['p', 'br', 'b', 'strong', 'i', 'em', 'ul', 'ol', 'li', 'span', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'];

        self::cleanNode($root, $allowedTags);

        // Export contents of the root div
        $outputHtml = '';
        foreach ($root->childNodes as $child) {
            $outputHtml .= $doc->saveHTML($child);
        }

        return $outputHtml;
    }

    /**
     * Recursively walk DOM nodes and sanitize tags and strip attributes.
     */
    private static function cleanNode(\DOMNode $node, array $allowedTags): void
    {
        // Iterate backward to allow modifying children safely
        for ($i = $node->childNodes->length - 1; $i >= 0; $i--) {
            $child = $node->childNodes->item($i);
            
            if ($child->nodeType === XML_ELEMENT_NODE) {
                $tagName = strtolower($child->nodeName);
                
                if (!in_array($tagName, $allowedTags, true)) {
                    // Strip the tag but keep its child contents
                    self::cleanNode($child, $allowedTags);
                    while ($child->childNodes->length > 0) {
                        $node->insertBefore($child->childNodes->item(0), $child);
                    }
                    $node->removeChild($child);
                } else {
                    // Allowed tag, but strip all attributes to prevent inline handler XSS
                    if ($child->hasAttributes()) {
                        $attrs = [];
                        foreach ($child->attributes as $attr) {
                            $attrs[] = $attr->nodeName;
                        }
                        foreach ($attrs as $attrName) {
                            $child->removeAttribute($attrName);
                        }
                    }
                    self::cleanNode($child, $allowedTags);
                }
            } elseif ($child->nodeType === XML_TEXT_NODE) {
                // Text node, keep it
            } else {
                // Remove comments, CDATA, DTD, etc.
                $node->removeChild($child);
            }
        }
    }
}
