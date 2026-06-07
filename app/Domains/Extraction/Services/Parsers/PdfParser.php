<?php

namespace App\Domains\Extraction\Services\Parsers;

use Illuminate\Support\Facades\Log;

class PdfParser
{
    /**
     * Extract raw text from a PDF file.
     *
     * @param string $filePath
     * @return string
     */
    public function extractText(string $filePath): string
    {
        if (!file_exists($filePath)) {
            Log::error("PDF file not found: {$filePath}");
            return '';
        }

        $content = file_get_contents($filePath);
        if (empty($content)) {
            return '';
        }

        // Search for stream blocks
        preg_match_all("/stream[\r\n]+(.*?)[\r\n]+endstream/is", $content, $matches);
        $extractedText = '';

        foreach ($matches[1] as $stream) {
            // Decrypt streams using FlateDecode
            $decoded = @gzuncompress($stream);
            if (!$decoded) {
                // Try skipping the 2-byte zlib header if gzuncompress failed
                $decoded = @gzinflate(substr($stream, 2));
            }
            if (!$decoded) {
                $decoded = $stream; // Keep raw stream as fallback
            }

            // PDF text objects inside streams are represented between parentheses: (text) Tj or [(text)-10(text)] TJ
            preg_match_all('/(?<=\()([^\)]*)(?=\))/s', $decoded, $textMatches);
            foreach ($textMatches[0] as $textMatch) {
                $extractedText .= $textMatch . ' ';
            }
        }

        // Clean up basic PDF escape sequences and control characters
        $extractedText = str_replace(['\\(', '\\)', '\\\\'], ['(', ')', '\\'], $extractedText);
        $extractedText = preg_replace('/[^a-zA-Z0-9\s,\.\-\/\(\)\:\@\&\#\=\_\'\`\"]/', '', $extractedText);

        return trim(preg_replace('/\s+/', ' ', $extractedText));
    }
}
