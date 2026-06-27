<?php

namespace App\Domains\Extraction\Services\Parsers;

use Illuminate\Support\Facades\Log;

/**
 * Enterprise Document Parser Service
 *
 * Uses PhpWord for DOCX/DOC and PhpSpreadsheet for XLSX/XLS/CSV
 * to provide production-grade document parsing with:
 * - Full paragraph/table/list extraction from Word documents
 * - Header/footer content extraction
 * - Cell-by-cell spreadsheet extraction with formatting
 * - Merged cell awareness
 * - Multi-sheet support
 * - CSV auto-delimiter detection
 * - XML DOM-based extraction
 * - Graceful fallback to legacy extractors
 */
class DocumentParserService
{
    /**
     * Extract text from a document based on type.
     * Backward-compatible method signature.
     *
     * @param string $filePath
     * @param string $type (docx, doc, xlsx, xls, csv, xml)
     * @return string
     */
    public function extractText(string $filePath, string $type): string
    {
        if (!file_exists($filePath)) {
            Log::error("Document file not found: {$filePath}");
            return '';
        }

        try {
            $result = $this->extractStructured($filePath, $type);
            return $result['text'] ?? '';
        } catch (\Throwable $e) {
            Log::warning("Enterprise document parser failed for {$type}, falling back to legacy: {$e->getMessage()}");
            return $this->legacyExtract($filePath, $type);
        }
    }

    /**
     * Extract structured data from a document.
     *
     * @param string $filePath
     * @param string $type
     * @return array ['text', 'tables', 'headers', 'lists', 'metadata']
     */
    public function extractStructured(string $filePath, string $type): array
    {
        if (!file_exists($filePath)) {
            Log::error("Document file not found: {$filePath}");
            return $this->emptyStructuredResult();
        }

        return match (strtolower($type)) {
            'docx'  => $this->extractDocxEnterprise($filePath),
            'doc'   => $this->extractDocEnterprise($filePath),
            'xlsx'  => $this->extractXlsxEnterprise($filePath),
            'xls'   => $this->extractXlsEnterprise($filePath),
            'csv'   => $this->extractCsvEnterprise($filePath),
            'xml'   => $this->extractXmlEnterprise($filePath),
            default => $this->emptyStructuredResult(),
        };
    }

    // =========================================================================
    // DOCX — PhpWord
    // =========================================================================

    /**
     * Extract DOCX using PhpWord IOFactory.
     * Preserves paragraphs, tables, headers, lists, styling.
     */
    protected function extractDocxEnterprise(string $filePath): array
    {
        if (!class_exists(\PhpOffice\PhpWord\IOFactory::class)) {
            Log::warning("phpoffice/phpword not installed. Falling back to legacy DOCX parser.");
            $text = $this->legacyExtractDocx($filePath);
            return array_merge($this->emptyStructuredResult(), ['text' => $text]);
        }

        try {
            $phpWord = \PhpOffice\PhpWord\IOFactory::load($filePath, 'Word2007');
            $result = $this->processPhpWordDocument($phpWord);
            if (empty(trim($result['text']))) {
                Log::info("PhpWord returned empty text for DOCX, trying legacy DOCX extractor.");
                $result['text'] = $this->legacyExtractDocx($filePath);
            }
            return $result;
        } catch (\Throwable $e) {
            Log::warning("PhpWord load failed for DOCX: {$e->getMessage()}. Falling back to legacy DOCX extractor.");
            $text = $this->legacyExtractDocx($filePath);
            return array_merge($this->emptyStructuredResult(), ['text' => $text]);
        }
    }

    /**
     * Extract binary DOC using PhpWord's MsDoc reader.
     */
    protected function extractDocEnterprise(string $filePath): array
    {
        if (!class_exists(\PhpOffice\PhpWord\IOFactory::class)) {
            Log::warning("phpoffice/phpword not installed. Falling back to legacy DOC parser.");
            $text = $this->legacyExtractDoc($filePath);
            return array_merge($this->emptyStructuredResult(), ['text' => $text]);
        }

        try {
            $phpWord = \PhpOffice\PhpWord\IOFactory::load($filePath, 'MsDoc');
            $result = $this->processPhpWordDocument($phpWord);
            if (empty(trim($result['text']))) {
                Log::info("PhpWord returned empty text for DOC, trying legacy DOC extractor.");
                $result['text'] = $this->legacyExtractDoc($filePath);
            }
            return $result;
        } catch (\Throwable $e) {
            Log::warning("PhpWord MsDoc reader failed: {$e->getMessage()}. Falling back to legacy DOC extractor.");
            $text = $this->legacyExtractDoc($filePath);
            return array_merge($this->emptyStructuredResult(), ['text' => $text]);
        }
    }

    /**
     * Process a loaded PhpWord document into structured output.
     */
    protected function processPhpWordDocument(\PhpOffice\PhpWord\PhpWord $phpWord): array
    {
        $text = '';
        $tables = [];
        $headers = [];
        $lists = [];
        $metadata = [];

        // Extract document properties
        $properties = $phpWord->getDocInfo();
        if ($properties) {
            $metadata = [
                'title'    => $properties->getTitle(),
                'creator'  => $properties->getCreator(),
                'company'  => $properties->getCompany(),
                'subject'  => $properties->getSubject(),
                'category' => $properties->getCategory(),
            ];
        }

        foreach ($phpWord->getSections() as $section) {
            // Process headers
            $sectionHeaders = $section->getHeaders();
            foreach ($sectionHeaders as $header) {
                foreach ($header->getElements() as $element) {
                    $headerText = $this->extractPhpWordElementText($element);
                    if (!empty(trim($headerText))) {
                        $headers[] = trim($headerText);
                        $text .= "## " . trim($headerText) . "\n";
                    }
                }
            }

            // Process body elements
            foreach ($section->getElements() as $element) {
                if ($element instanceof \PhpOffice\PhpWord\Element\Table) {
                    $tableData = $this->extractPhpWordTable($element);
                    if (!empty($tableData)) {
                        $tables[] = $tableData;
                        $text .= $this->renderTableAsText($tableData) . "\n\n";
                    }
                } elseif ($element instanceof \PhpOffice\PhpWord\Element\ListItemRun || $element instanceof \PhpOffice\PhpWord\Element\ListItem) {
                    $listText = $this->extractPhpWordElementText($element);
                    if (!empty(trim($listText))) {
                        $lists[] = trim($listText);
                        $text .= "• " . trim($listText) . "\n";
                    }
                } elseif ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
                    $paragraphText = $this->extractPhpWordElementText($element);
                    if (!empty(trim($paragraphText))) {
                        $text .= trim($paragraphText) . "\n";
                    }
                } elseif ($element instanceof \PhpOffice\PhpWord\Element\Text) {
                    $t = $element->getText();
                    if (!empty(trim($t))) {
                        $text .= trim($t) . "\n";
                    }
                } else {
                    // Generic element: try to extract text
                    $elementText = $this->extractPhpWordElementText($element);
                    if (!empty(trim($elementText))) {
                        $text .= trim($elementText) . "\n";
                    }
                }
            }

            // Process footers
            $footers = $section->getFooters();
            foreach ($footers as $footer) {
                foreach ($footer->getElements() as $element) {
                    $footerText = $this->extractPhpWordElementText($element);
                    if (!empty(trim($footerText))) {
                        $text .= trim($footerText) . "\n";
                    }
                }
            }
        }

        return [
            'text'     => trim($text),
            'tables'   => $tables,
            'headers'  => $headers,
            'lists'    => $lists,
            'metadata' => $metadata,
        ];
    }

    /**
     * Extract text from a PhpWord element recursively.
     */
    protected function extractPhpWordElementText($element): string
    {
        if ($element instanceof \PhpOffice\PhpWord\Element\Text) {
            return $element->getText();
        }

        if ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
            $text = '';
            foreach ($element->getElements() as $child) {
                $text .= $this->extractPhpWordElementText($child);
            }
            return $text;
        }

        $text = '';
        if (method_exists($element, 'getText')) {
            $t = $element->getText();
            if (is_string($t)) {
                return $t;
            }
        }

        if (method_exists($element, 'getElements')) {
            foreach ($element->getElements() as $child) {
                $text .= $this->extractPhpWordElementText($child);
            }
        }

        return $text;
    }

    /**
     * Extract table data from a PhpWord Table element.
     */
    protected function extractPhpWordTable(\PhpOffice\PhpWord\Element\Table $table): array
    {
        $rows = [];
        foreach ($table->getRows() as $row) {
            $cells = [];
            foreach ($row->getCells() as $cell) {
                $cellText = '';
                foreach ($cell->getElements() as $element) {
                    $cellText .= $this->extractPhpWordElementText($element) . ' ';
                }
                $cells[] = trim($cellText);
            }
            $rows[] = $cells;
        }
        return ['rows' => $rows, 'columns' => count($rows[0] ?? [])];
    }

    // =========================================================================
    // XLSX — PhpSpreadsheet
    // =========================================================================

    /**
     * Extract XLSX using PhpSpreadsheet IOFactory.
     * Full cell-by-cell extraction with formatting and multi-sheet support.
     */
    protected function extractXlsxEnterprise(string $filePath): array
    {
        if (!class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            Log::warning("phpoffice/phpspreadsheet not installed. Falling back to legacy XLSX parser.");
            $text = $this->legacyExtractXlsx($filePath);
            return array_merge($this->emptyStructuredResult(), ['text' => $text]);
        }

        try {
            $result = $this->extractSpreadsheet($filePath, 'Xlsx');
            if (empty(trim($result['text']))) {
                Log::info("PhpSpreadsheet returned empty text for XLSX, trying legacy XLSX extractor.");
                $result['text'] = $this->legacyExtractXlsx($filePath);
            }
            return $result;
        } catch (\Throwable $e) {
            Log::warning("PhpSpreadsheet XLSX reader failed: {$e->getMessage()}. Falling back to legacy XLSX extractor.");
            $text = $this->legacyExtractXlsx($filePath);
            return array_merge($this->emptyStructuredResult(), ['text' => $text]);
        }
    }

    /**
     * Extract binary XLS using PhpSpreadsheet Xls reader.
     */
    protected function extractXlsEnterprise(string $filePath): array
    {
        if (!class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            Log::warning("phpoffice/phpspreadsheet not installed. Falling back to legacy XLS parser.");
            $text = $this->legacyExtractXls($filePath);
            return array_merge($this->emptyStructuredResult(), ['text' => $text]);
        }

        try {
            $result = $this->extractSpreadsheet($filePath, 'Xls');
            if (empty(trim($result['text']))) {
                Log::info("PhpSpreadsheet returned empty text for XLS, trying legacy XLS extractor.");
                $result['text'] = $this->legacyExtractXls($filePath);
            }
            return $result;
        } catch (\Throwable $e) {
            Log::warning("PhpSpreadsheet XLS reader failed: {$e->getMessage()}. Falling back to legacy XLS extractor.");
            $text = $this->legacyExtractXls($filePath);
            return array_merge($this->emptyStructuredResult(), ['text' => $text]);
        }
    }

    /**
     * Extract CSV using PhpSpreadsheet Csv reader.
     */
    protected function extractCsvEnterprise(string $filePath): array
    {
        if (!class_exists(\PhpOffice\PhpSpreadsheet\IOFactory::class)) {
            Log::warning("phpoffice/phpspreadsheet not installed. Using basic CSV parsing.");
            return $this->basicCsvExtract($filePath);
        }

        return $this->extractSpreadsheet($filePath, 'Csv');
    }

    /**
     * Common spreadsheet extraction logic for XLSX/XLS/CSV.
     */
    protected function extractSpreadsheet(string $filePath, string $readerType): array
    {
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($readerType);

        // CSV-specific configuration
        if ($readerType === 'Csv') {
            $delimiter = $this->detectCsvDelimiter($filePath);
            $reader->setDelimiter($delimiter);
            $reader->setEnclosure('"');
        }

        $spreadsheet = $reader->load($filePath);
        $text = '';
        $tables = [];
        $metadata = [
            'sheet_count' => $spreadsheet->getSheetCount(),
            'sheet_names' => $spreadsheet->getSheetNames(),
        ];

        foreach ($spreadsheet->getAllSheets() as $sheetIndex => $sheet) {
            $sheetName = $sheet->getTitle();
            $text .= "--- Sheet: {$sheetName} ---\n";

            $sheetTable = ['rows' => [], 'columns' => 0, 'sheet' => $sheetName];
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();
            $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);

            $sheetTable['columns'] = $highestColumnIndex;

            for ($row = 1; $row <= $highestRow; $row++) {
                $rowData = [];
                $rowText = '';

                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $cellCoordinate = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row;
                    $cell = $sheet->getCell($cellCoordinate);

                    // Get calculated value (evaluates formulas)
                    $value = '';
                    try {
                        $value = $cell->getCalculatedValue();
                    } catch (\Throwable) {
                        $value = $cell->getValue();
                    }

                    $value = (string)($value ?? '');
                    $rowData[] = $value;
                    $rowText .= $value . "\t";
                }

                // Skip entirely empty rows
                $nonEmpty = array_filter($rowData, fn($v) => trim($v) !== '');
                if (!empty($nonEmpty)) {
                    $sheetTable['rows'][] = $rowData;
                    $text .= trim($rowText) . "\n";
                }
            }

            if (!empty($sheetTable['rows'])) {
                $tables[] = $sheetTable;
            }
            $text .= "\n";
        }

        return [
            'text'     => trim($text),
            'tables'   => $tables,
            'headers'  => [],
            'lists'    => [],
            'metadata' => $metadata,
        ];
    }

    /**
     * Auto-detect CSV delimiter by sampling the first few lines.
     */
    protected function detectCsvDelimiter(string $filePath): string
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return ',';
        }

        $sample = '';
        $lineCount = 0;
        while (($line = fgets($handle)) !== false && $lineCount < 5) {
            $sample .= $line;
            $lineCount++;
        }
        fclose($handle);

        $delimiters = [',' => 0, ';' => 0, "\t" => 0, '|' => 0];
        foreach ($delimiters as $delimiter => &$count) {
            $count = substr_count($sample, $delimiter);
        }

        arsort($delimiters);
        return array_key_first($delimiters);
    }

    /**
     * Basic CSV extraction fallback (no PhpSpreadsheet).
     */
    protected function basicCsvExtract(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return $this->emptyStructuredResult();
        }

        $delimiter = $this->detectCsvDelimiter($filePath);
        $text = '';
        $rows = [];

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rows[] = $row;
            $text .= implode("\t", $row) . "\n";
        }
        fclose($handle);

        return [
            'text'     => trim($text),
            'tables'   => [['rows' => $rows, 'columns' => count($rows[0] ?? [])]],
            'headers'  => [],
            'lists'    => [],
            'metadata' => ['delimiter' => $delimiter],
        ];
    }

    // =========================================================================
    // XML — DOM-based extraction
    // =========================================================================

    /**
     * Extract XML content using DOMDocument.
     */
    protected function extractXmlEnterprise(string $filePath): array
    {
        $content = file_get_contents($filePath);
        if (empty($content)) {
            return $this->emptyStructuredResult();
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
        $doc->loadXML($content, LIBXML_NONET);
        libxml_clear_errors();

        $text = '';
        $headers = [];

        $this->walkDomNode($doc->documentElement, $text, $headers, 0);

        return [
            'text'     => trim($text),
            'tables'   => [],
            'headers'  => $headers,
            'lists'    => [],
            'metadata' => ['root_element' => $doc->documentElement ? $doc->documentElement->nodeName : null],
        ];
    }

    /**
     * Recursively walk DOM nodes extracting text content.
     */
    protected function walkDomNode(?\DOMNode $node, string &$text, array &$headers, int $depth): void
    {
        if ($node === null) {
            return;
        }

        if ($node->nodeType === XML_TEXT_NODE) {
            $val = trim($node->nodeValue);
            if (!empty($val)) {
                $text .= $val . "\n";
            }
            return;
        }

        if ($node->nodeType === XML_ELEMENT_NODE) {
            $nodeName = $node->nodeName;

            // Treat element name as a section header if it has child text
            if ($node->hasChildNodes() && $depth <= 3) {
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

            foreach ($node->childNodes as $child) {
                $this->walkDomNode($child, $text, $headers, $depth + 1);
            }
        }
    }

    // =========================================================================
    // Utility Methods
    // =========================================================================

    /**
     * Render a table data array as pipe-delimited text.
     */
    protected function renderTableAsText(array $tableData): string
    {
        $output = '';
        foreach ($tableData['rows'] ?? [] as $rowIndex => $row) {
            $output .= '| ' . implode(' | ', $row) . " |\n";
            if ($rowIndex === 0) {
                $output .= '| ' . implode(' | ', array_fill(0, count($row), '---')) . " |\n";
            }
        }
        return $output;
    }

    /**
     * Return an empty structured result template.
     */
    protected function emptyStructuredResult(): array
    {
        return [
            'text'     => '',
            'tables'   => [],
            'headers'  => [],
            'lists'    => [],
            'metadata' => [],
        ];
    }

    // =========================================================================
    // Legacy Fallback Methods (from original implementation)
    // =========================================================================

    /**
     * Legacy fallback dispatcher.
     */
    protected function legacyExtract(string $filePath, string $type): string
    {
        return match (strtolower($type)) {
            'docx'  => $this->legacyExtractDocx($filePath),
            'xlsx'  => $this->legacyExtractXlsx($filePath),
            'doc'   => $this->legacyExtractDoc($filePath),
            'xls'   => $this->legacyExtractXls($filePath),
            'csv'   => $this->basicCsvExtractText($filePath),
            'xml'   => $this->legacyExtractXml($filePath),
            default => '',
        };
    }

    /**
     * Legacy DOCX: Parse using ZipArchive and extract text from word/document.xml.
     */
    protected function legacyExtractDocx(string $filePath): string
    {
        $zip = new \ZipArchive();
        if ($zip->open($filePath) === true) {
            if (($index = $zip->locateName('word/document.xml')) !== false) {
                $stat = $zip->statIndex($index);
                if ($stat && isset($stat['size']) && $stat['size'] > 20971520) { // 20MB Limit
                    $zip->close();
                    throw new \Exception("Zip bomb warning: word/document.xml size exceeds 20MB limit.");
                }
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
     * Legacy XLSX: Parse using ZipArchive and extract text from sheet worksheets.
     */
    protected function legacyExtractXlsx(string $filePath): string
    {
        $zip = new \ZipArchive();
        $extractedText = '';

        if ($zip->open($filePath) === true) {
            $sharedStrings = [];
            if (($index = $zip->locateName('xl/sharedStrings.xml')) !== false) {
                $stat = $zip->statIndex($index);
                if ($stat && isset($stat['size']) && $stat['size'] > 20971520) { // 20MB Limit
                    $zip->close();
                    throw new \Exception("Zip bomb warning: xl/sharedStrings.xml size exceeds 20MB limit.");
                }
                $xmlContent = $zip->getFromIndex($index);
                preg_match_all('/<t[^>]*>(.*?)<\/t>/', $xmlContent, $matches);
                $sharedStrings = $matches[1];
            }

            $sheetIndex = 1;
            while (($index = $zip->locateName("xl/worksheets/sheet{$sheetIndex}.xml")) !== false) {
                $stat = $zip->statIndex($index);
                if ($stat && isset($stat['size']) && $stat['size'] > 20971520) { // 20MB Limit
                    $zip->close();
                    throw new \Exception("Zip bomb warning: xl/worksheets/sheet{$sheetIndex}.xml size exceeds 20MB limit.");
                }
                $xmlContent = $zip->getFromIndex($index);

                preg_match_all('/<c[^>]*t="s"[^>]*><v>(\d+)<\/v><\/c>/', $xmlContent, $sMatches);
                foreach ($sMatches[1] as $idx) {
                    if (isset($sharedStrings[$idx])) {
                        $extractedText .= html_entity_decode($sharedStrings[$idx]) . ' ';
                    }
                }

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
     * Legacy DOC: Parse binary DOC using ASCII filtering.
     */
    protected function legacyExtractDoc(string $filePath): string
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

        $cleaned = preg_replace('/[^a-zA-Z0-9\s,\.\-\/\(\)\:\@\&\#\=\_\'\`\"]/', '', $extractedText);
        return trim(preg_replace('/\s+/', ' ', $cleaned));
    }

    /**
     * Legacy XLS: Parse binary XLS using ASCII filtering.
     */
    protected function legacyExtractXls(string $filePath): string
    {
        $fileHandle = fopen($filePath, 'r');
        if (!$fileHandle) {
            return '';
        }
        $content = fread($fileHandle, filesize($filePath));
        fclose($fileHandle);

        $cleaned = preg_replace('/[^a-zA-Z0-9\s,\.\-\/\(\)\:\@\&\#\=\_\'\`\"]/', ' ', $content);
        return trim(preg_replace('/\s+/', ' ', $cleaned));
    }

    /**
     * Basic CSV text extraction (no PhpSpreadsheet).
     */
    protected function basicCsvExtractText(string $filePath): string
    {
        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return '';
        }

        $text = '';
        while (($row = fgetcsv($handle)) !== false) {
            $text .= implode(' ', $row) . "\n";
        }
        fclose($handle);
        return trim($text);
    }

    /**
     * Legacy XML extraction.
     */
    protected function legacyExtractXml(string $filePath): string
    {
        $content = file_get_contents($filePath);
        if (empty($content)) {
            return '';
        }
        return trim(strip_tags($content));
    }
}
