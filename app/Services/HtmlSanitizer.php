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
        if ($html === null) {
            return '';
        }

        // Completely strip script tags and their contents
        $clean = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);

        // Allow only safe presentation tags
        $allowedTags = '<p><br><b><strong><i><em><ul><ol><li><span><h1><h2><h3><h4><h5><h6>';

        // 1. Strip all tags not in allowed list
        $clean = strip_tags($clean, $allowedTags);

        // 2. Prevent XSS via malicious attributes (e.g., onload, onerror, onclick, etc.)
        $clean = preg_replace('/on\w+\s*=\s*["\'][^"\']*["\']/i', '', $clean);
        $clean = preg_replace('/on\w+\s*=\s*[^\s>]+/i', '', $clean);

        // 3. Prevent XSS via javascript: URLs
        $clean = preg_replace('/href\s*=\s*["\']\s*javascript\s*:[^"\']*["\']/i', 'href="#"', $clean);
        
        return $clean;
    }
}
