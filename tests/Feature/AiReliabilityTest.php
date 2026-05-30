<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Department;
use App\Models\State;
use App\Models\Qualification;
use App\Models\ScrapingSource;
use App\Models\JobPost;
use App\Models\ScrapingLog;
use App\Models\AiAuditLog;
use App\Domains\Scrapers\Services\AIService;
use App\Domains\Scrapers\Services\ScrapingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiReliabilityTest extends TestCase
{
    use RefreshDatabase;

    protected AIService $aiService;
    protected ScrapingService $scrapingService;
    protected ScrapingSource $source;

    protected function setUp(): void
    {
        parent::setUp();

        $this->aiService = $this->app->make(AIService::class);
        $this->scrapingService = $this->app->make(ScrapingService::class);

        // Seed parents
        $state = State::create(['name' => 'Delhi', 'code' => 'DL']);
        $cat = Category::create(['name' => 'SSC Exam Jobs', 'slug' => 'ssc-exam-jobs']);
        $dept = Department::create(['name' => 'Staff Selection Board', 'code' => 'SSB-01']);
        $qual = Qualification::create(['name' => 'High School Degree', 'slug' => 'high-school']);

        // Scraping Source
        $this->source = ScrapingSource::create([
            'name' => 'Direct Reliable Feed',
            'source_url' => 'https://ssb-portal.gov.in/jobs',
            'source_type' => 'html',
            'selectors_config' => [
                'default_category_id' => $cat->id,
                'default_department_id' => $dept->id,
                'default_state_id' => $state->id,
                'default_qualification_id' => $qual->id
            ]
        ]);
    }

    /**
     * Test Task 1 & 2: AI must never generate/hallucinate facts and only summarize/extract entities
     */
    public function test_ai_never_generates_facts_and_returns_null_when_absent(): void
    {
        $rawText = "SSB recruitment announcement for High School graduates. Apply online before 2026-12-31.";

        $extracted = $this->aiService->runDeterministicAISimulator($rawText);

        // Verified that absent facts strictly resolve to null instead of generating fake placeholder facts
        $this->assertNull($extracted['age_limit']);
        $this->assertNull($extracted['salary_min']);
        $this->assertNull($extracted['salary_max']);
        $this->assertNull($extracted['selection_process']);
        $this->assertNull($extracted['exam_pattern']);
        $this->assertEquals(0.00, $extracted['application_fee']); // default fee is 0.00
    }

    /**
     * Test Task 3: Confidence scoring logic
     */
    public function test_confidence_scoring_engine_computes_accurately(): void
    {
        $rawText = "SSB Senior Officer Job. Salary 50000 - 80000. Age limit 21-35 years. Last date 2026-10-31.";

        // 1. High Confidence Case (Extracted parameters match physical source text perfectly)
        $perfectExtraction = [
            'title' => 'SSB Senior Officer Job',
            'description' => 'SSB Senior Officer Job listing.',
            'salary_min' => 50000.0,
            'salary_max' => 80000.0,
            'age_limit' => '21-35 Years',
            'last_date_to_apply' => '2026-10-31',
            'application_fee' => 0.00,
            'vacancy_count' => 0,
            'selection_process' => null, // Absent and correctly null => awarded 100%
            'exam_pattern' => null,      // Absent and correctly null => awarded 100%
        ];

        $confidence = $this->aiService->computeConfidence($perfectExtraction, $rawText);
        $this->assertGreaterThanOrEqual(90.0, $confidence['overall']);

        // 2. Hallucinated Case (Extracted parameters are NOT present in the source text)
        $hallucinatedExtraction = $perfectExtraction;
        $hallucinatedExtraction['salary_min'] = 99999.0; // Hallucinated figure!
        $hallucinatedExtraction['age_limit'] = '45-50 Years'; // Hallucinated age range!

        $lowConfidence = $this->aiService->computeConfidence($hallucinatedExtraction, $rawText);
        $this->assertLessThan($confidence['overall'], $lowConfidence['overall']);
        $this->assertEquals(0.0, $lowConfidence['scores']['salary_min']);
    }

    /**
     * Test Task 4 & 5: Human review fallback via automatic quarantine + audit logging
     */
    public function test_low_confidence_trigger_quarantine_fallback_and_logs_audit_telemetry(): void
    {
        // Raw text contains some info, but the simulated parsed output contains hallucinated/unmatched details
        $rawItem = [
            'title' => 'SSB Telecom Assistant Recruitment 2026',
            'deadline_raw' => '31-10-2026',
            'fee_raw' => 'Rs. 500',
            'official_link' => 'https://ssb.gov.in',
            'apply_link' => 'https://ssb.gov.in/apply',
            'raw_text' => 'SSB Telecom Assistant Recruitment 2026. Age: 18-35. Salary is Rs 40000.'
        ];

        // We stub AIService call to return low-confidence/hallucinated data
        $lowConfidenceData = [
            'title' => 'SSB Telecom Assistant Recruitment 2026',
            'description' => 'Assumed role overview.',
            'age_limit' => '21-30 Years', // Doesn't match 18-35 in raw text => low score
            'salary_min' => 99999.00,      // Hallucinated number => score 0%
            'salary_max' => 150000.00,     // Hallucinated number => score 0%
            'vacancy_count' => 1000,       // Hallucinated number => score 0%
            'application_fee' => 99.00,    // Unmatched with Rs. 500 => score 0%
            'last_date_to_apply' => '2026-10-31',
            'selection_process' => 'Hallucinated selection process', // Hallucinated => low score
            'exam_pattern' => 'Hallucinated exam pattern',           // Hallucinated => low score
        ];

        // Bind mock AIService returning low-confidence details
        $mockAiService = $this->getMockBuilder(AIService::class)->onlyMethods(['callAIEngine', 'cleanAndSummarize'])->getMock();
        $mockAiService->method('cleanAndSummarize')->willReturn($lowConfidenceData);
        $this->app->instance(AIService::class, $mockAiService);

        // Resolve scrapingService with the mocked AIService
        $scrapingService = $this->app->make(ScrapingService::class);

        $reflection = new \ReflectionClass(ScrapingService::class);
        $method = $reflection->getMethod('processScrapedItem');
        $method->setAccessible(true);

        $result = $method->invokeArgs($scrapingService, [$this->source, $rawItem]);

        // Asserts low-confidence extraction is quarantined
        $this->assertEquals('quarantined', $result['status']);

        // Asserts audit log was populated
        $this->assertDatabaseHas('scraping_logs', [
            'scraping_source_id' => $this->source->id,
            'status' => 'quarantined',
            'error_message' => 'AI Reliability confidence check failed.'
        ]);

        // Verify that the validation error array explains the low confidence score
        $log = ScrapingLog::where('status', 'quarantined')->first();
        $this->assertNotNull($log->validation_errors);
        $this->assertStringContainsString('AI Confidence Score', $log->validation_errors[0]);

        // Assert AiAuditLog records perfect telemetry trace
        $auditLog = AiAuditLog::first();
        $this->assertNotNull($auditLog);
        $this->assertEquals($this->source->id, $auditLog->scraping_source_id);
        $this->assertEquals('failed_confidence', $auditLog->status);
        $this->assertLessThan(85.0, $auditLog->overall_score);
        $this->assertArrayHasKey('salary_min', $auditLog->confidence_scores);
    }
}
