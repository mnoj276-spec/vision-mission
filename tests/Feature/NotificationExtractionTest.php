<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Department;
use App\Models\JobPost;
use App\Models\State;
use App\Models\Qualification;
use App\Models\ExtractedNotification;
use App\Domains\Extraction\Services\Parsers\PdfParser;
use App\Domains\Extraction\Services\Parsers\DocumentParserService;
use App\Domains\Extraction\Services\OCRService;
use App\Domains\Extraction\Services\AiStructuringService;
use App\Domains\Extraction\Services\ValidationService;
use App\Domains\Extraction\Services\ExtractionPipeline;
use App\Domains\Extraction\Jobs\ProcessNotificationExtractionJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NotificationExtractionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic DB records required for mapping
        State::create(['name' => 'Pan India', 'code' => 'CENTRAL']);
        Category::create(['name' => 'Banking & Finance', 'slug' => 'banking-finance']);
        Department::create(['name' => 'Department of Science and Technology', 'code' => 'DST']);
        Qualification::create(['name' => 'Bachelor of Technology', 'slug' => 'graduate']);
    }

    /**
     * Test direct PDF stream parsing.
     */
    public function test_pdf_parser_extracts_text_streams(): void
    {
        $parser = new PdfParser();
        
        // Create a dummy PDF layout with standard text streams
        $tempFile = tempnam(sys_get_temp_dir(), 'pdf');
        $pdfContent = "%PDF-1.4\n1 0 obj\n<< /Length 50 >>\nstream\n(Job Title: Senior Engineer) Tj\nendstream\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF";
        file_put_contents($tempFile, $pdfContent);

        $extracted = $parser->extractText($tempFile);
        @unlink($tempFile);

        $this->assertStringContainsString('Job Title: Senior Engineer', $extracted);
    }

    /**
     * Test DOCX unzipping and XML tag text extraction.
     */
    public function test_docx_parser_extracts_paragraphs(): void
    {
        $parser = new DocumentParserService();
        $tempFile = tempnam(sys_get_temp_dir(), 'docx');

        // Build a dummy zip archive representing a DOCX file
        $zip = new \ZipArchive();
        if ($zip->open($tempFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            $xml = '<?xml version="1.0" encoding="UTF-8"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body><w:p><w:r><w:t>Recruitment Notification: Assistant Officer</w:t></w:r></w:p></w:body></w:document>';
            $zip->addFromString('word/document.xml', $xml);
            $zip->close();
        }

        $extracted = $parser->extractText($tempFile, 'docx');
        @unlink($tempFile);

        $this->assertEquals('Recruitment Notification: Assistant Officer', $extracted);
    }

    /**
     * Test XLSX unzipping and cells/shared strings extraction.
     */
    public function test_xlsx_parser_extracts_cell_data(): void
    {
        $parser = new DocumentParserService();
        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx');

        // Build a dummy zip representing XLSX sheet
        $zip = new \ZipArchive();
        if ($zip->open($tempFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            $sharedStrings = '<?xml version="1.0" encoding="UTF-8"?><sst count="1" uniqueCount="1"><si><t>Manager Vacancy</t></si></sst>';
            $sheet = '<?xml version="1.0" encoding="UTF-8"?><worksheet><sheetData><row><c r="A1" t="s"><v>0</v></c><c r="B1"><v>50000</v></c></row></sheetData></worksheet>';
            $zip->addFromString('xl/sharedStrings.xml', $sharedStrings);
            $zip->addFromString('xl/worksheets/sheet1.xml', $sheet);
            $zip->close();
        }

        $extracted = $parser->extractText($tempFile, 'xlsx');
        @unlink($tempFile);

        $this->assertStringContainsString('Manager Vacancy', $extracted);
        $this->assertStringContainsString('50000', $extracted);
    }

    /**
     * Test OCR and AI structuring fallback simulator.
     */
    public function test_ocr_and_ai_services_simulator_fallbacks(): void
    {
        $ocr = new OCRService();
        $tempFile = tempnam(sys_get_temp_dir(), 'ocr');
        file_put_contents($tempFile, "Dummy OCR target file content.");

        // OCR simulator test
        $ocrText = $ocr->extractText($tempFile, 'png');
        @unlink($tempFile);
        $this->assertStringContainsString('Technical Assistant Recruitment 2026', $ocrText);

        // AI structuring simulator test
        $ai = new AiStructuringService();
        $structured = $ai->structureText($ocrText);

        $this->assertEquals('Technical Assistant Recruitment 2026', $structured['title']);
        $this->assertEquals('Department of Science and Technology', $structured['department']);
        $this->assertEquals(45, $structured['vacancy_count']);
        $this->assertEquals(500.00, $structured['application_fee']);
        $this->assertEquals('2026-07-15', $structured['important_dates']['last_date_to_apply']);
    }

    /**
     * Test validation rules logic.
     */
    public function test_validation_rules(): void
    {
        $validator = new ValidationService();

        // Valid data
        $validData = [
            'title' => 'Senior Technical Assistant Recruitment 2026',
            'department' => 'Department of Science and Technology',
            'vacancy_count' => 12,
            'qualification' => 'Bachelor of Technology',
            'application_fee' => 250.00,
            'important_dates' => [
                'start_date' => '2026-06-10',
                'last_date_to_apply' => '2026-07-20',
            ],
            'official_website' => 'http://dst.gov.in',
        ];
        $res = $validator->validate($validData);
        $this->assertTrue($res['isValid']);

        // Invalid data (short title, missing department, missing last date)
        $invalidData = [
            'title' => 'Short',
            'vacancy_count' => -1,
            'application_fee' => -100,
            'important_dates' => [
                'start_date' => 'invalid-date',
            ],
        ];
        $res = $invalidDataResult = $validator->validate($invalidData);
        $this->assertFalse($res['isValid']);
        $this->assertArrayHasKey('title', $res['errors']);
        $this->assertArrayHasKey('department', $res['errors']);
        $this->assertArrayHasKey('vacancy_count', $res['errors']);
        $this->assertArrayHasKey('important_dates.last_date_to_apply', $res['errors']);
    }

    /**
     * Test upload API queues the background job correctly.
     */
    public function test_upload_api_queues_extraction_job(): void
    {
        Queue::fake();
        Storage::fake('local');

        $uploadedFile = \Illuminate\Http\UploadedFile::fake()->create('notification.pdf', 100);

        $response = $this->postJson(route('api.v1.extraction.upload'), [
            'file' => $uploadedFile,
        ]);

        $response->assertStatus(202);
        $response->assertJsonStructure(['success', 'id', 'message']);

        $notificationId = $response->json('id');
        $this->assertDatabaseHas('extracted_notifications', [
            'id' => $notificationId,
            'status' => 'pending',
            'file_type' => 'pdf',
        ]);

        Queue::assertPushed(ProcessNotificationExtractionJob::class, function ($job) use ($notificationId) {
            // Check matching ID via reflection or accessor
            $reflection = new \ReflectionClass($job);
            $prop = $reflection->getProperty('notification');
            $prop->setAccessible(true);
            $notification = $prop->getValue($job);
            return $notification->id === $notificationId;
        });
    }

    /**
     * Test full execution of the pipeline via the Queue job.
     */
    public function test_extraction_pipeline_runs_smoothly(): void
    {
        Storage::fake('local');

        // Create dummy file inside storage path
        $tempPath = 'extractions/test_notification.png';
        Storage::disk('local')->put($tempPath, 'Dummy image file content.');
        $absolutePath = Storage::disk('local')->path($tempPath);

        $notification = ExtractedNotification::create([
            'file_path'          => $absolutePath,
            'original_filename'  => 'test_notification.png',
            'file_type'          => 'png',
            'status'             => 'pending',
            'validation_status'  => 'pending',
        ]);

        // Run the job synchronously
        $job = new ProcessNotificationExtractionJob($notification);
        app()->call([$job, 'handle']);

        $this->assertDatabaseHas('extracted_notifications', [
            'id'                => $notification->id,
            'status'            => 'processed',
            'validation_status' => 'valid',
        ]);

        $updated = ExtractedNotification::find($notification->id);
        $this->assertNotNull($updated->raw_text);
        $this->assertEquals('Technical Assistant Recruitment 2026', $updated->extracted_data['title']);
    }

    /**
     * Test approval endpoint saves and publishes a new Job Post correctly.
     */
    public function test_approval_api_saves_job_post(): void
    {
        $notification = ExtractedNotification::create([
            'file_path'          => '/path/to/file.pdf',
            'original_filename'  => 'file.pdf',
            'file_type'          => 'pdf',
            'status'             => 'processed',
            'validation_status'  => 'valid',
            'extracted_data'     => [
                'title' => 'Technical Assistant Recruitment 2026',
                'department' => 'Department of Science and Technology',
                'vacancy_count' => 45,
                'qualification' => 'Bachelor of Technology',
                'age_limit' => '21 to 30 Years',
                'salary' => 'Rs. 35,400 to Rs. 1,12,400',
                'application_fee' => 500.00,
                'selection_process' => 'Written test and interview.',
                'important_dates' => [
                    'start_date' => '2026-06-10',
                    'last_date_to_apply' => '2026-07-15',
                ],
                'official_website' => 'http://dst.gov.in',
            ],
        ]);

        $response = $this->postJson(route('api.v1.extraction.approve', ['id' => $notification->id]));

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'job_post', 'message']);

        $this->assertDatabaseHas('job_posts', [
            'title'           => 'Technical Assistant Recruitment 2026',
            'status'          => 'published',
            'vacancy_count'   => 45,
            'application_fee' => 500.00,
            'salary_min'      => 35400.00,
            'salary_max'      => 112400.00,
        ]);

        $updatedNotification = ExtractedNotification::find($notification->id);
        $this->assertEquals('approved', $updatedNotification->status);
        $this->assertNotNull($updatedNotification->job_post_id);
    }

    /**
     * Test duplicate fingerprint blocks approval.
     */
    public function test_duplicate_fingerprint_blocks_approval(): void
    {
        $notification1 = ExtractedNotification::create([
            'file_path'          => '/path/to/file.pdf',
            'original_filename'  => 'file.pdf',
            'file_type'          => 'pdf',
            'status'             => 'processed',
            'validation_status'  => 'valid',
            'extracted_data'     => [
                'title' => 'Technical Assistant Recruitment 2026',
                'department' => 'Department of Science and Technology',
                'vacancy_count' => 45,
                'qualification' => 'Bachelor of Technology',
                'application_fee' => 500.00,
                'important_dates' => [
                    'last_date_to_apply' => '2026-07-15',
                ],
                'official_website' => 'http://dst.gov.in',
            ],
        ]);

        // First approval: should succeed
        $response1 = $this->postJson(route('api.v1.extraction.approve', ['id' => $notification1->id]));
        $response1->assertStatus(200);

        // Second duplicate approval: should be rejected
        $notification2 = ExtractedNotification::create([
            'file_path'          => '/path/to/file2.pdf',
            'original_filename'  => 'file2.pdf',
            'file_type'          => 'pdf',
            'status'             => 'processed',
            'validation_status'  => 'valid',
            'extracted_data'     => [
                'title' => 'Technical Assistant Recruitment 2026',
                'department' => 'Department of Science and Technology',
                'vacancy_count' => 45,
                'qualification' => 'Bachelor of Technology',
                'application_fee' => 500.00,
                'important_dates' => [
                    'last_date_to_apply' => '2026-07-15',
                ],
                'official_website' => 'http://dst.gov.in',
            ],
        ]);

        $response2 = $this->postJson(route('api.v1.extraction.approve', ['id' => $notification2->id]));
        $response2->assertStatus(409);
        $response2->assertJsonFragment([
            'success' => false,
        ]);
        $this->assertStringContainsString('Duplicate job posting detected', $response2->json('message'));
    }
}
