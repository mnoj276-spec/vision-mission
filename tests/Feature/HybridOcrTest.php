<?php

namespace Tests\Feature;

use App\Domains\Extraction\Services\Ocr\OcrManager;
use App\Domains\Extraction\Services\Ocr\OcrResult;
use App\Domains\Extraction\Services\Ocr\Engines\TesseractEngine;
use App\Domains\Extraction\Services\Ocr\Engines\GeminiOcrEngine;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HybridOcrTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    /**
     * Test Auto Routing rules based on language and extensions.
     */
    public function test_ocr_routing_rules_are_applied_correctly(): void
    {
        $manager = app(OcrManager::class);

        // Access internal method via reflection to check priority chain
        $ref = new \ReflectionClass(OcrManager::class);
        $method = $ref->getMethod('resolvePriorityChain');
        $method->setAccessible(true);

        // 1. PDF files should start with Gemini
        $pdfChain = $method->invokeArgs($manager, ['dummy_notice.pdf', ['language' => 'english']]);
        $this->assertEquals('gemini', $pdfChain[0]);

        // 2. Hindi images should prioritize PaddleOCR
        $hindiChain = $method->invokeArgs($manager, ['dummy_notice.png', ['language' => 'hindi']]);
        $this->assertEquals('paddleocr', $hindiChain[0]);

        // 3. English images should prioritize Tesseract
        $englishChain = $method->invokeArgs($manager, ['dummy_notice.png', ['language' => 'english']]);
        $this->assertEquals('tesseract', $englishChain[0]);
    }

    /**
     * Test caching of OCR results.
     */
    public function test_ocr_results_are_cached_and_retrieved(): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'ocr_cache_test');
        file_put_contents($tempFile, "English job post notification text.");

        $manager = app(OcrManager::class);

        // First run: Cache Miss
        $result1 = $manager->extract($tempFile, [
            'language' => 'english',
            'force_engine' => 'tesseract',
        ]);

        $this->assertFalse(isset($result1->metadata['cached']));

        // Second run: Cache Hit
        $result2 = $manager->extract($tempFile, [
            'language' => 'english',
            'force_engine' => 'tesseract',
        ]);

        $this->assertTrue($result2->metadata['cached']);
        $this->assertEquals($result1->text, $result2->text);
        
        @unlink($tempFile);
    }

    /**
     * Test fallback mechanism when an engine drops below minimum confidence.
     */
    public function test_ocr_falls_back_when_confidence_drops_below_threshold(): void
    {
        config(['ocr.min_confidence' => 80.0]);

        // Mock Tesseract to return 50.0% confidence, which is below 80%
        $mockTesseract = $this->createMock(TesseractEngine::class);
        $mockTesseract->method('getName')->willReturn('tesseract');
        $mockTesseract->method('isAvailable')->willReturn(true);
        $mockTesseract->method('extract')->willReturn(
            new OcrResult('Low confidence English text', 50.0, 'tesseract', 0.1)
        );

        // Bind mock Tesseract
        $this->app->instance(TesseractEngine::class, $mockTesseract);

        $tempFile = tempnam(sys_get_temp_dir(), 'ocr_fallback_test');
        file_put_contents($tempFile, "Fallback trigger file content.");

        $manager = app(OcrManager::class);

        // Run OCR. Since Tesseract yields 50% confidence, it cascades to the next engine: paddleocr (simulated -> 90% confidence)
        $result = $manager->extract($tempFile, [
            'language' => 'english', // priority: tesseract -> paddleocr -> easyocr -> gemini -> openai
        ]);

        // Asserts that the final selected result is NOT tesseract, but rather paddleocr or next
        $this->assertNotEquals('tesseract', $result->engine);
        $this->assertGreaterThanOrEqual(80.0, $result->confidence);
        $this->assertNotEmpty($result->metadata['fallback_traces']);
        
        // Assert trace recorded tesseract cascade
        $tesseractTrace = collect($result->metadata['fallback_traces'])->firstWhere('engine', 'tesseract');
        $this->assertNotNull($tesseractTrace);
        $this->assertEquals('success', $tesseractTrace['status']);
        $this->assertEquals(50.0, $tesseractTrace['confidence']);

        @unlink($tempFile);
    }

    /**
     * Test retry mechanism for API/transient timeouts.
     */
    public function test_ocr_retries_on_transient_api_failures(): void
    {
        // Mock Gemini to throw connection timeout exceptions
        $mockGemini = $this->getMockBuilder(GeminiOcrEngine::class)
            ->onlyMethods(['extract', 'isAvailable'])
            ->getMock();
        $mockGemini->method('isAvailable')->willReturn(true);
        
        $attempts = 0;
        $mockGemini->method('extract')->will($this->returnCallback(function () use (&$attempts) {
            $attempts++;
            if ($attempts < 3) {
                throw new \Exception("Connection timed out. API Server unresponsive.");
            }
            return new OcrResult('Gemini final transcript after retries', 99.0, 'gemini', 0.5);
        }));

        $this->app->instance(GeminiOcrEngine::class, $mockGemini);

        $tempFile = tempnam(sys_get_temp_dir(), 'ocr_retry_test');
        file_put_contents($tempFile, "Retry testing document.");

        config(['ocr.retry.attempts' => 3]);
        config(['ocr.retry.backoff_ms' => 1]); // keep fast for testing

        $manager = app(OcrManager::class);
        $result = $manager->extract($tempFile, [
            'force_engine' => 'gemini'
        ]);

        $this->assertEquals('gemini', $result->engine);
        $this->assertEquals(3, $attempts, 'Manager should have retried the API twice and succeeded on the third attempt.');

        @unlink($tempFile);
    }

    /**
     * Test benchmark console command execution.
     */
    public function test_benchmark_console_command_runs_successfully(): void
    {
        $this->artisan('app:benchmark-ocr')
            ->assertExitCode(0);
    }
}
