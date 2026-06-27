<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Department;
use App\Models\State;
use App\Models\Qualification;
use App\Models\ExtractedNotification;
use App\Domains\Extraction\Services\Parsers\PdfParser;
use App\Domains\Extraction\Services\Parsers\DocumentParserService;
use App\Domains\Extraction\Services\Parsers\CsvParser;
use App\Domains\Extraction\Services\Parsers\XmlParser;
use App\Domains\Extraction\Services\Parsers\HtmlParser;
use App\Domains\Extraction\Services\Parsers\ImageParser;
use App\Domains\Extraction\Services\ExtractionPipeline;
use App\Domains\Extraction\Services\OCRService;
use App\Domains\Extraction\Services\AiStructuringService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnterpriseDocumentParserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic DB records required for mapping
        State::firstOrCreate(['name' => 'Pan India', 'code' => 'CENTRAL']);
        Category::firstOrCreate(['name' => 'Banking & Finance', 'slug' => 'banking-finance']);
        Department::firstOrCreate(['name' => 'Department of Science and Technology', 'code' => 'DST']);
        Qualification::firstOrCreate(['name' => 'Bachelor of Technology', 'slug' => 'graduate']);
    }

    /**
     * Test PDF Parser with dummy layout and fallback.
     */
    public function test_pdf_parser_extracts_structured_data(): void
    {
        $parser = new PdfParser();
        
        $tempFile = tempnam(sys_get_temp_dir(), 'pdf');
        $pdfContent = "%PDF-1.4\n1 0 obj\n<< /Length 50 >>\nstream\n(Job Title: Senior Engineer) Tj\nendstream\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF";
        file_put_contents($tempFile, $pdfContent);

        // Test backward compat
        $text = $parser->extractText($tempFile);
        $this->assertStringContainsString('Job Title: Senior Engineer', $text);

        // Test structured
        $structured = $parser->extractStructured($tempFile);
        @unlink($tempFile);

        $this->assertIsArray($structured);
        $this->assertArrayHasKey('text', $structured);
        $this->assertArrayHasKey('tables', $structured);
        $this->assertArrayHasKey('metadata', $structured);
        $this->assertArrayHasKey('is_scanned', $structured);
    }

    /**
     * Test DOCX Parser with PhpWord.
     */
    public function test_docx_parser_extracts_structured_data(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'docx') . '.docx';

        // Write a valid DOCX using PhpWord
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection();
        $section->addText("Recruitment Notification: Technical Assistant");
        
        $table = $section->addTable();
        $table->addRow();
        $table->addCell()->addText("Post Name");
        $table->addCell()->addText("Vacancies");
        $table->addRow();
        $table->addCell()->addText("Technical Assistant");
        $table->addCell()->addText("45");
        
        $section->addListItem("Bachelor of Technology");
        
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($tempFile);

        $parser = new DocumentParserService();

        // Backward compat
        $text = $parser->extractText($tempFile, 'docx');
        $this->assertStringContainsString('Recruitment Notification: Technical Assistant', $text);
        $this->assertStringContainsString('Technical Assistant', $text);
        $this->assertStringContainsString('45', $text);

        // Structured
        $structured = $parser->extractStructured($tempFile, 'docx');
        @unlink($tempFile);

        $this->assertIsArray($structured);
        $this->assertNotEmpty($structured['tables']);
        $this->assertEquals(45, $structured['tables'][0]['rows'][1][1]);
        $this->assertContains('Bachelor of Technology', $structured['lists']);
    }

    /**
     * Test XLSX Parser with PhpSpreadsheet.
     */
    public function test_xlsx_parser_extracts_sheet_data(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx') . '.xlsx';

        // Write a valid XLSX using PhpSpreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Post Name');
        $sheet->setCellValue('B1', 'Technical Assistant');
        $sheet->setCellValue('A2', 'Vacancy Count');
        $sheet->setCellValue('B2', '45');
        $sheet->setCellValue('A3', 'Last Date to Apply');
        $sheet->setCellValue('B3', '2026-07-15');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempFile);

        $parser = new DocumentParserService();

        // Backward compat
        $text = $parser->extractText($tempFile, 'xlsx');
        $this->assertStringContainsString('Technical Assistant', $text);
        $this->assertStringContainsString('45', $text);

        // Structured
        $structured = $parser->extractStructured($tempFile, 'xlsx');
        @unlink($tempFile);

        $this->assertIsArray($structured);
        $this->assertNotEmpty($structured['tables']);
        $this->assertEquals('45', $structured['tables'][0]['rows'][1][1]);
    }

    /**
     * Test CSV Parser.
     */
    public function test_csv_parser_detects_delimiter_and_parses(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'csv');
        $csvContent = "Job Title,Technical Assistant Recruitment 2026\nVacancy Count,45\nLast Date to Apply,2026-07-15";
        file_put_contents($tempFile, $csvContent);

        $parser = new CsvParser();
        $structured = $parser->extractStructured($tempFile);
        @unlink($tempFile);

        $this->assertIsArray($structured);
        $this->assertStringContainsString('Technical Assistant Recruitment 2026', $structured['text']);
        $this->assertEquals('45', $structured['tables'][0]['rows'][1][1]);
        $this->assertEquals(',', $structured['metadata']['delimiter']);
    }

    /**
     * Test XML Parser.
     */
    public function test_xml_parser_extracts_nested_data(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'xml');
        $xmlContent = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><rss version=\"2.0\"><channel><title>Gov Recruitment</title><item><title>Technical Assistant Recruitment 2026</title><description>Vacancies: 45. Last Date: 2026-07-15</description></item></channel></rss>";
        file_put_contents($tempFile, $xmlContent);

        $parser = new XmlParser();
        $structured = $parser->extractStructured($tempFile);
        @unlink($tempFile);

        $this->assertIsArray($structured);
        $this->assertStringContainsString('Technical Assistant Recruitment 2026', $structured['text']);
        $this->assertNotEmpty($structured['items']);
        $this->assertEquals('Technical Assistant Recruitment 2026', $structured['items'][0]['title']);
    }

    /**
     * Test HTML Parser.
     */
    public function test_html_parser_cleans_tags_and_preserves_structure(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'html');
        $htmlContent = "<html><head><title>Notification Title</title><style>body {color: red;}</style></head><body><h1>Technical Assistant Recruitment 2026</h1><table><tr><td>Vacancy Count</td><td>45</td></tr></table><ul><li>Bachelor of Technology</li></ul><script>console.log('test');</script></body></html>";
        file_put_contents($tempFile, $htmlContent);

        $parser = new HtmlParser();
        $structured = $parser->extractStructured($tempFile);
        @unlink($tempFile);

        $this->assertIsArray($structured);
        $this->assertStringContainsString('Technical Assistant Recruitment 2026', $structured['text']);
        $this->assertStringNotContainsString('console.log', $structured['text']);
        $this->assertStringNotContainsString('color: red', $structured['text']);
        $this->assertNotEmpty($structured['tables']);
        $this->assertEquals('45', $structured['tables'][0]['rows'][0][1]);
    }

    /**
     * Test Image Parser.
     */
    public function test_image_parser_delegates_to_ocr(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'png') . '.png';
        if (function_exists('imagecreatetruecolor')) {
            $im = imagecreatetruecolor(500, 500);
            imagepng($im, $tempFile);
            imagedestroy($im);
        } else {
            file_put_contents($tempFile, "fake image stream");
        }

        $ocrService = new OCRService();
        $parser = new ImageParser($ocrService);
        $structured = $parser->extractStructured($tempFile);
        @unlink($tempFile);

        $this->assertIsArray($structured);
        $this->assertStringContainsString('Technical Assistant Recruitment 2026', $structured['text']);
        $this->assertEquals(function_exists('imagecreatetruecolor') ? 'high' : 'low', $structured['confidence']);
    }

    /**
     * Test full pipeline integration with fallback.
     */
    public function test_extraction_pipeline_executes_successfully(): void
    {
        // 1. Create a dummy HTML notification document
        $tempFile = tempnam(sys_get_temp_dir(), 'html') . '.html';
        $htmlContent = "<html><body>"
            . "<h1>Technical Assistant Recruitment 2026</h1>"
            . "<p>Department: Department of Science and Technology</p>"
            . "<table>"
            . "<tr><td>Vacancy Count</td><td>45</td></tr>"
            . "<tr><td>Last Date to Apply</td><td>2026-07-15</td></tr>"
            . "<tr><td>Fee</td><td>500</td></tr>"
            . "</table>"
            . "<ul><li>Qualification: Bachelor of Technology</li></ul>"
            . "</body></html>";
        file_put_contents($tempFile, $htmlContent);

        // 2. Create pending ExtractedNotification model
        $notification = ExtractedNotification::create([
            'file_path' => $tempFile,
            'original_filename' => 'notification.html',
            'file_type' => 'html',
            'status' => 'pending',
        ]);

        // 3. Resolve pipeline and process
        $pipeline = app(ExtractionPipeline::class);
        $processed = $pipeline->process($notification);

        @unlink($tempFile);

        $this->assertEquals('processed', $processed->status);
        $this->assertEquals('valid', $processed->validation_status);
        
        $data = $processed->extracted_data;
        $this->assertEquals('Technical Assistant Recruitment 2026', $data['title']);
        $this->assertEquals('Department of Science and Technology', $data['department']);
        $this->assertEquals(45, $data['vacancy_count']);
        $this->assertEquals(500.00, $data['application_fee']);

        // Check metadata attributes
        $this->assertArrayHasKey('_metadata', $data);
        $this->assertEquals('HtmlParser', $data['_metadata']['parser_used']);
        $this->assertGreaterThan(0, $data['_metadata']['parse_duration_seconds']);
        $this->assertGreaterThan(0, $data['_metadata']['text_length']);
        $this->assertEquals(1, $data['_metadata']['table_count']);
    }
}
