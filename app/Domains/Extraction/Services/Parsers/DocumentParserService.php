<?php

namespace App\Domains\Extraction\Services\Parsers;

use Illuminate\Support\Facades\Log;

class DocumentParserService
{
    /**
     * Extract text from a document based on type.
     *
     * @param string $filePath
     * @param string $type (docx, doc, xlsx, xls)
     * @return string
     */
    public function extractText(string $filePath, string $type): string
    {
        if (!file_exists($filePath)) {
            Log::error("Document file not found: {$filePath}");
            return '';
        }

        return match (strtolower($type)) {
            'docx'  => $this->extractDocx($filePath),
            'xlsx'  => $this->extractXlsx($filePath),
            'doc'   => $this->extractDoc($filePath),
            'xls'   => $this->extractXls($filePath),
            default => '',
        };
    }

    /**
     * Parse DOCX using ZipArchive and extract text from word/document.xml.
     */
    protected function extractDocx(string $filePath): string
    {
        $zip = new \ZipArchive();
        if ($zip->open($filePath) === true) {
            if (($index = $zip->locateName('word/document.xml')) !== false) {
                $xmlContent = $zip->getFromIndex($index);
                $zip->close();

                // Extract all contents inside <w:t> tags
                preg_match_all('/<w:t[^>]*>(.*?)<\/w:t>/', $xmlContent, $matches);
                return trim(implode(' ', $matches[1]));
            }
            $zip->close();
        }
        return '';
    }

    /**
     * Parse XLSX using ZipArchive and extract text from sheet worksheets.
     */
    protected function extractXlsx(string $filePath): string
    {
        $zip = new \ZipArchive();
        $extractedText = '';

        if ($zip->open($filePath) === true) {
            $sharedStrings = [];
            // Parse sharedStrings first
            if (($index = $zip->locateName('xl/sharedStrings.xml')) !== false) {
                $xmlContent = $zip->getFromIndex($index);
                preg_match_all('/<t[^>]*>(.*?)<\/t>/', $xmlContent, $matches);
                $sharedStrings = $matches[1];
            }

            // Loop and parse worksheets (sheet1.xml, sheet2.xml, etc.)
            $sheetIndex = 1;
            while (($index = $zip->locateName("xl/worksheets/sheet{$sheetIndex}.xml")) !== false) {
                $xmlContent = $zip->getFromIndex($index);

                // 1. Get shared strings by index
                preg_match_all('/<c[^>]*t="s"[^>]*><v>(\d+)<\/v><\/c>/', $xmlContent, $sMatches);
                foreach ($sMatches[1] as $idx) {
                    if (isset($sharedStrings[$idx])) {
                        $extractedText .= html_entity_decode($sharedStrings[$idx]) . ' ';
                    }
                }

                // 2. Get non-shared values (numbers, inline strings, etc.)
                preg_match_all('/<c[^>]*>(?!.*t="s")<v>(.*?)<\/v><\/c>/', $xmlContent, $vMatches);
                foreach ($vMatches[1] as $val) {
                    $extractedText .= $val . ' ';
                }

                $sheetIndex++;
            }
            $zip->close();
        }

        return trim(preg_replace('/\s+/', ' ', $extractedText));
    }

    /**
     * Parse binary DOC using ASCII filtering.
     */
    protected function extractDoc(string $filePath): string
    {
        $fileHandle = fopen($filePath, 'r');
        if (!$fileHandle) {
            return '';
        }
        $line = fread($fileHandle, filesize($filePath));
        fclose($fileHandle);

        $lines = explode(chr(0x0d), $line);
        $extractedText = '';
        foreach ($lines as $thisline) {
            $pos = strpos($thisline, chr(0x00));
            if (($pos !== false) || (strlen($thisline) == 0)) {
                continue;
            }
            $extractedText .= $thisline . ' ';
        }

        // Clean up formatting
        $cleaned = preg_replace('/[^a-zA-Z0-9\s,\.\-\/\(\)\:\@\&\#\=\_\'\`\"]/', '', $extractedText);
        return trim(preg_replace('/\s+/', ' ', $cleaned));
    }

    /**
     * Parse binary XLS using ASCII filtering.
     */
    protected function extractXls(string $filePath): string
    {
        // Simple stream decoding for older XLS format
        $fileHandle = fopen($filePath, 'r');
        if (!$fileHandle) {
            return '';
        }
        $content = fread($fileHandle, filesize($filePath));
        fclose($fileHandle);

        // Filter printable characters
        $cleaned = preg_replace('/[^a-zA-Z0-9\s,\.\-\/\(\)\:\@\&\#\=\_\'\`\"]/', ' ', $content);
        return trim(preg_replace('/\s+/', ' ', $cleaned));
    }
}
