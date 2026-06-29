<?php

namespace App\Domains\Extraction\Services\Parsers;

use Illuminate\Support\Facades\Log;

/**
 * Enterprise PDF Parser
 *
 * Uses smalot/pdf-parser for production-grade text extraction with:
 * - Page-by-page awareness
 * - Table detection via line-alignment heuristics
 * - Multi-column layout merging
 * - Metadata extraction (title, author, page count)
 * - Scanned PDF detection (triggers OCR fallback)
 * - Graceful fallback to raw stream regex extraction
 */
class PdfParser
{
    /**
     * Minimum characters per page to consider a PDF as text-based (not scanned).
     */
    protected const SCANNED_THRESHOLD = 150;

    /**
     * Extract raw text from a PDF file.
     * Backward-compatible method signature.
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

        $result = $this->extractStructured($filePath);
        return $result['text'] ?? '';
    }

    /**
     * Extract structured data from a PDF file.
     *
     * @param string $filePath
     * @return array ['text', 'pages', 'tables', 'metadata', 'is_scanned', 'page_count']
     */
    public function extractStructured(string $filePath): array
    {
        if (!file_exists($filePath)) {
            Log::error("PDF file not found: {$filePath}");
            return $this->emptyStructuredResult();
        }

        // Check if smalot/pdf-parser is available
        if (!class_exists(\Smalot\PdfParser\Parser::class)) {
            Log::warning("smalot/pdf-parser not installed. Falling back to raw stream extraction.");
            $text = $this->extractViaRawStreams($filePath);
            return array_merge($this->emptyStructuredResult(), ['text' => $text]);
        }

        try {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($filePath);

            // Extract metadata
            $details = $pdf->getDetails();
            $metadata = [
                'title'        => $details['Title'] ?? null,
                'author'       => $details['Author'] ?? null,
                'creator'      => $details['Creator'] ?? null,
                'producer'     => $details['Producer'] ?? null,
                'creation_date' => $details['CreationDate'] ?? null,
                'page_count'   => $details['Pages'] ?? null,
            ];

            // Extract text page-by-page
            $pages = $pdf->getPages();
            $pageTexts = [];
            $allText = '';
            $tables = [];

            foreach ($pages as $pageIndex => $page) {
                $pageText = $page->getText();
                $pageTexts[] = $pageText;

                // Detect tables using line-alignment heuristics
                $pageTables = $this->detectTablesInText($pageText);
                if (!empty($pageTables)) {
                    foreach ($pageTables as $table) {
                        $tables[] = array_merge($table, ['page' => $pageIndex + 1]);
                    }
                }

                $allText .= $pageText . "\n\n";
            }

            // Multi-column merging heuristic
            $allText = $this->mergeMultiColumnText($allText);

            // Clean up the text
            $allText = $this->cleanExtractedText($allText);

            // Scanned PDF detection
            $pageCount = count($pages) ?: 1;
            $avgCharsPerPage = mb_strlen(trim($allText)) / $pageCount;
            $isScanned = $avgCharsPerPage < self::SCANNED_THRESHOLD;

            if ($isScanned) {
                Log::info("PDF detected as scanned document (avg {$avgCharsPerPage} chars/page). OCR fallback recommended.");
            }

            return [
                'text'       => trim($allText),
                'pages'      => $pageTexts,
                'tables'     => $tables,
                'metadata'   => $metadata,
                'is_scanned' => $isScanned,
                'page_count' => $pageCount,
            ];

        } catch (\Throwable $e) {
            Log::warning("smalot/pdf-parser extraction failed, falling back to raw stream extraction: {$e->getMessage()}");
            $text = $this->extractViaRawStreams($filePath);
            return array_merge($this->emptyStructuredResult(), ['text' => $text]);
        }
    }

    /**
     * Detect table structures in page text using alignment heuristics.
     * Government PDFs commonly contain vacancy tables with consistent spacing.
     *
     * @param string $pageText
     * @return array Array of detected tables, each with 'rows' and 'columns'
     */
    protected function detectTablesInText(string $pageText): array
    {
        $lines = explode("\n", $pageText);
        $tables = [];
        $currentTable = [];
        $prevColumnCount = 0;

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (empty($trimmed)) {
                // End of potential table block
                if (count($currentTable) >= 2) {
                    $tables[] = [
                        'rows'    => $currentTable,
                        'columns' => $prevColumnCount,
                    ];
                }
                $currentTable = [];
                $prevColumnCount = 0;
                continue;
            }

            // Detect columns by multiple whitespace separators (2+ spaces or tabs)
            $columns = preg_split('/\s{2,}|\t+/', $trimmed);
            $columnCount = count($columns);

            if ($columnCount >= 2) {
                // Consistent column count suggests tabular data
                if ($prevColumnCount === 0 || abs($columnCount - $prevColumnCount) <= 1) {
                    $currentTable[] = $columns;
                    $prevColumnCount = $columnCount;
                } else {
                    // Column count changed significantly — flush previous table
                    if (count($currentTable) >= 2) {
                        $tables[] = [
                            'rows'    => $currentTable,
                            'columns' => $prevColumnCount,
                        ];
                    }
                    $currentTable = [$columns];
                    $prevColumnCount = $columnCount;
                }
            } else {
                // Single-column line — flush if we had a table building
                if (count($currentTable) >= 2) {
                    $tables[] = [
                        'rows'    => $currentTable,
                        'columns' => $prevColumnCount,
                    ];
                }
                $currentTable = [];
                $prevColumnCount = 0;
            }
        }

        // Flush remaining table
        if (count($currentTable) >= 2) {
            $tables[] = [
                'rows'    => $currentTable,
                'columns' => $prevColumnCount,
            ];
        }

        return $tables;
    }

    /**
     * Merge multi-column text layouts.
     * Government gazette PDFs often use 2-column layouts.
     * Heuristic: detect consistent mid-page gaps and interleave columns.
     *
     * @param string $text
     * @return string
     */
    protected function mergeMultiColumnText(string $text): string
    {
        // Simple heuristic: if lines consistently have large internal whitespace gaps
        // (indicating side-by-side columns), re-flow them into sequential paragraphs.
        $lines = explode("\n", $text);
        $merged = [];
        $leftBuffer = '';
        $rightBuffer = '';

        foreach ($lines as $line) {
            // Detect if line has a large gap in the middle (20+ spaces)
            if (preg_match('/^(.{20,}?)\s{20,}(.{20,})$/', $line, $m)) {
                $leftBuffer .= ' ' . trim($m[1]);
                $rightBuffer .= ' ' . trim($m[2]);
            } else {
                // Flush buffers if we were accumulating columns
                if (!empty($leftBuffer) || !empty($rightBuffer)) {
                    $merged[] = trim($leftBuffer);
                    $merged[] = trim($rightBuffer);
                    $leftBuffer = '';
                    $rightBuffer = '';
                }
                $merged[] = $line;
            }
        }

        // Final flush
        if (!empty($leftBuffer) || !empty($rightBuffer)) {
            $merged[] = trim($leftBuffer);
            $merged[] = trim($rightBuffer);
        }

        return implode("\n", $merged);
    }

    /**
     * Clean extracted text: normalize whitespace, remove control characters.
     *
     * @param string $text
     * @return string
     */
    protected function cleanExtractedText(string $text): string
    {
        // Remove null bytes and other control characters (except newline/tab)
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);

        // Collapse multiple blank lines into double newline
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        // Collapse multiple spaces into single space (per line)
        $lines = explode("\n", $text);
        $lines = array_map(function ($line) {
            return preg_replace('/[ \t]+/', ' ', trim($line));
        }, $lines);

        return implode("\n", $lines);
    }

    /**
     * Legacy fallback: extract text from raw PDF stream objects.
     * Used when smalot/pdf-parser is unavailable or fails.
     *
     * @param string $filePath
     * @return string
     */
    protected function extractViaRawStreams(string $filePath): string
    {
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
                $decoded = @gzinflate(substr($stream, 2));
            }
            if (!$decoded) {
                $decoded = $stream;
            }

            // PDF text objects inside streams: (text) Tj or [(text)] TJ
            preg_match_all('/(?<=\()([^\)]*)(?=\))/s', $decoded, $textMatches);
            foreach ($textMatches[0] as $textMatch) {
                $extractedText .= $textMatch . ' ';
            }
        }

        // Clean up PDF escape sequences and control characters
        $extractedText = str_replace(['\\(', '\\)', '\\\\'], ['(', ')', '\\'], $extractedText);
        $extractedText = preg_replace('/[^a-zA-Z0-9\s,\.\-\/\(\)\:\@\&\#\=\_\'\`\"]/', '', $extractedText);

        return trim(preg_replace('/\s+/', ' ', $extractedText));
    }

    /**
     * Return an empty structured result template.
     *
     * @return array
     */
    protected function emptyStructuredResult(): array
    {
        return [
            'text'       => '',
            'pages'      => [],
            'tables'     => [],
            'metadata'   => [],
            'is_scanned' => false,
            'page_count' => 0,
        ];
    }
}
