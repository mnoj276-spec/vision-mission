<?php

namespace App\Domains\Extraction\Services\Parsers;

use Illuminate\Support\Facades\Log;

/**
 * Enterprise CSV Parser
 *
 * Dedicated CSV parser using PhpSpreadsheet's Csv reader with:
 * - Auto-delimiter detection (comma, semicolon, tab, pipe)
 * - BOM marker handling
 * - Encoding normalization (UTF-8, Windows-1252)
 * - Header-column mapping
 */
class CsvParser
{
    /**
     * Extract text from a CSV file.
     *
     * @param string $filePath
     * @return string
     */
    public function extractText(string $filePath): string
    {
        if (!file_exists($filePath)) {
            Log::error("CSV file not found: {$filePath}");
            return '';
        }

        $result = $this->extractStructured($filePath);
        return $result['text'] ?? '';
    }

    /**
     * Extract structured data from a CSV file.
     *
     * @param string $filePath
     * @return array ['text', 'tables', 'headers', 'metadata']
     */
    public function extractStructured(string $filePath): array
    {
        if (!file_exists($filePath)) {
            Log::error("CSV file not found: {$filePath}");
            return $this->emptyResult();
        }

        $content = file_get_contents($filePath);
        if (empty($content)) {
            return $this->emptyResult();
        }

        // Strip BOM markers (UTF-8, UTF-16 LE/BE)
        $content = $this->stripBom($content);

        // Detect encoding and normalize to UTF-8
        $content = $this->normalizeEncoding($content);

        // Detect delimiter
        $delimiter = $this->detectDelimiter($content);

        // Write normalized content to temp file for parsing
        $tempFile = tempnam(sys_get_temp_dir(), 'csv_');
        file_put_contents($tempFile, $content);

        try {
            // Try PhpSpreadsheet first
            if (class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
                return $this->parseWithSpreadsheet($tempFile, $delimiter);
            }

            // Fallback to native PHP
            return $this->parseNative($tempFile, $delimiter);
        } finally {
            @unlink($tempFile);
        }
    }

    /**
     * Parse CSV using PhpSpreadsheet.
     */
    protected function parseWithSpreadsheet(string $filePath, string $delimiter): array
    {
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Csv');
        $reader->setDelimiter($delimiter);
        $reader->setEnclosure('"');
        $reader->setInputEncoding('UTF-8');

        $spreadsheet = $reader->load($filePath);
        $sheet = $spreadsheet->getActiveSheet();

        $text = '';
        $rows = [];
        $headers = [];

        $highestRow = $sheet->getHighestRow();
        $highestCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sheet->getHighestColumn());

        for ($row = 1; $row <= $highestRow; $row++) {
            $rowData = [];
            for ($col = 1; $col <= $highestCol; $col++) {
                $coord = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row;
                $value = (string)($sheet->getCell($coord)->getValue() ?? '');
                $rowData[] = $value;
            }

            $nonEmpty = array_filter($rowData, fn($v) => trim($v) !== '');
            if (!empty($nonEmpty)) {
                if ($row === 1) {
                    $headers = $rowData;
                }
                $rows[] = $rowData;
                $text .= implode("\t", $rowData) . "\n";
            }
        }

        return [
            'text'     => trim($text),
            'tables'   => [['rows' => $rows, 'columns' => $highestCol]],
            'headers'  => $headers,
            'lists'    => [],
            'metadata' => [
                'delimiter'  => $delimiter,
                'row_count'  => count($rows),
                'col_count'  => $highestCol,
            ],
        ];
    }

    /**
     * Parse CSV using native PHP fgetcsv.
     */
    protected function parseNative(string $filePath, string $delimiter): array
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return $this->emptyResult();
        }

        $text = '';
        $rows = [];
        $headers = [];
        $rowIndex = 0;

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $nonEmpty = array_filter($row, fn($v) => trim($v) !== '');
            if (!empty($nonEmpty)) {
                if ($rowIndex === 0) {
                    $headers = $row;
                }
                $rows[] = $row;
                $text .= implode("\t", $row) . "\n";
                $rowIndex++;
            }
        }
        fclose($handle);

        return [
            'text'     => trim($text),
            'tables'   => [['rows' => $rows, 'columns' => count($headers)]],
            'headers'  => $headers,
            'lists'    => [],
            'metadata' => [
                'delimiter'  => $delimiter,
                'row_count'  => count($rows),
                'col_count'  => count($headers),
            ],
        ];
    }

    /**
     * Detect the most likely CSV delimiter from content.
     */
    protected function detectDelimiter(string $content): string
    {
        // Sample first 5 lines
        $lines = array_slice(explode("\n", $content), 0, 5);
        $sample = implode("\n", $lines);

        $delimiters = [',' => 0, ';' => 0, "\t" => 0, '|' => 0];
        foreach ($delimiters as $delimiter => &$count) {
            $count = substr_count($sample, $delimiter);
        }

        arsort($delimiters);
        return array_key_first($delimiters);
    }

    /**
     * Strip BOM markers from content.
     */
    protected function stripBom(string $content): string
    {
        // UTF-8 BOM
        if (str_starts_with($content, "\xEF\xBB\xBF")) {
            return substr($content, 3);
        }
        // UTF-16 LE BOM
        if (str_starts_with($content, "\xFF\xFE")) {
            $content = substr($content, 2);
            return mb_convert_encoding($content, 'UTF-8', 'UTF-16LE');
        }
        // UTF-16 BE BOM
        if (str_starts_with($content, "\xFE\xFF")) {
            $content = substr($content, 2);
            return mb_convert_encoding($content, 'UTF-8', 'UTF-16BE');
        }
        return $content;
    }

    /**
     * Normalize encoding to UTF-8.
     */
    protected function normalizeEncoding(string $content): string
    {
        $detected = mb_detect_encoding($content, ['UTF-8', 'Windows-1252', 'ISO-8859-1', 'ASCII'], true);
        if ($detected && $detected !== 'UTF-8') {
            return mb_convert_encoding($content, 'UTF-8', $detected);
        }
        return $content;
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
