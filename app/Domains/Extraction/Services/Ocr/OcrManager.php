<?php

namespace App\Domains\Extraction\Services\Ocr;

use App\Domains\Extraction\Services\Ocr\Engines\TesseractEngine;
use App\Domains\Extraction\Services\Ocr\Engines\PaddleOcrEngine;
use App\Domains\Extraction\Services\Ocr\Engines\EasyOcrEngine;
use App\Domains\Extraction\Services\Ocr\Engines\GeminiOcrEngine;
use App\Domains\Extraction\Services\Ocr\Engines\OpenAiOcrEngine;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OcrManager
{
    protected array $engines = [];

    public function __construct()
    {
        $this->engines = [
            'tesseract' => app(TesseractEngine::class),
            'paddleocr' => app(PaddleOcrEngine::class),
            'easyocr'   => app(EasyOcrEngine::class),
            'gemini'    => app(GeminiOcrEngine::class),
            'openai'    => app(OpenAiOcrEngine::class),
        ];
    }

    /**
     * Get a specific engine by name.
     */
    public function engine(string $name): OcrEngineInterface
    {
        if (!isset($this->engines[$name])) {
            throw new \InvalidArgumentException("OCR Engine [{$name}] is not registered.");
        }
        return $this->engines[$name];
    }

    /**
     * Process OCR with automatic routing, caching, retries, and fallbacks.
     *
     * @param string $filePath
     * @param array $options Configuration and heuristic options:
     *                      - 'language': 'english'|'hindi'|'mixed' (auto-detected or preset)
     *                      - 'force_engine': force a specific engine (bypasses routing & fallback)
     *                      - 'skip_cache': bypass cache
     * @return OcrResult
     */
    public function extract(string $filePath, array $options = []): OcrResult
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("OCR target file not found at: {$filePath}");
        }

        $options = $this->applyAutoRouting($filePath, $options);
        
        // 1. Caching Layer
        $cacheEnabled = config('ocr.cache.enabled', true) && !($options['skip_cache'] ?? false);
        $cacheKey = '';
        if ($cacheEnabled) {
            $fileHash = md5_file($filePath);
            $optionsHash = md5(json_encode($options));
            $cacheKey = config('ocr.cache.prefix', 'ocr_cache:') . $fileHash . '_' . $optionsHash;

            $cachedData = Cache::get($cacheKey);
            if ($cachedData) {
                Log::info("OCR Manager: Cache hit for file " . basename($filePath));
                return new OcrResult(
                    $cachedData['text'],
                    $cachedData['confidence'],
                    $cachedData['engine'],
                    $cachedData['duration_seconds'],
                    $cachedData['cost'],
                    array_merge($cachedData['metadata'] ?? [], ['cached' => true])
                );
            }
        }

        // If force_engine is requested, run it directly without fallback
        if (!empty($options['force_engine'])) {
            $engineName = $options['force_engine'];
            Log::info("OCR Manager: Forcing engine [{$engineName}] for file " . basename($filePath));
            $result = $this->executeWithRetry($this->engine($engineName), $filePath, $options);
            
            if ($cacheEnabled && !empty($cacheKey)) {
                Cache::put($cacheKey, $result->toArray(), config('ocr.cache.ttl', 1440));
            }
            return $result;
        }

        // 2. Resolve Dynamic Priority Chain
        $priorityChain = $this->resolvePriorityChain($filePath, $options);
        $minConfidence = config('ocr.min_confidence', 75.0);
        $traces = [];
        $finalResult = null;

        Log::info("OCR Manager: Executing chain: " . implode(' -> ', $priorityChain) . " for " . basename($filePath));

        foreach ($priorityChain as $engineName) {
            try {
                $engine = $this->engine($engineName);
                
                // If local engine is disabled, skip it
                if (in_array($engineName, ['tesseract', 'paddleocr', 'easyocr']) && !config("ocr.engines.{$engineName}.enabled", true)) {
                    continue;
                }

                Log::info("OCR Manager: Attempting extraction with engine [{$engineName}]");
                $result = $this->executeWithRetry($engine, $filePath, $options);

                $traces[] = [
                    'engine' => $engineName,
                    'status' => 'success',
                    'confidence' => $result->confidence,
                    'duration' => $result->duration,
                    'cost' => $result->cost,
                ];

                if ($result->confidence >= $minConfidence) {
                    Log::info("OCR Manager: Engine [{$engineName}] succeeded with confidence {$result->confidence}% (Threshold: {$minConfidence}%)");
                    $finalResult = $result;
                    break;
                } else {
                    Log::warning("OCR Manager: Engine [{$engineName}] confidence {$result->confidence}% is below threshold {$minConfidence}%. Falling back.");
                }
            } catch (\Throwable $e) {
                Log::error("OCR Manager: Engine [{$engineName}] failed: " . $e->getMessage());
                $traces[] = [
                    'engine' => $engineName,
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];
            }
        }

        // 3. Fallback to first available result if no engine hit the threshold
        if (!$finalResult && !empty($traces)) {
            // Find the successful run with highest confidence
            $bestTrace = collect($traces)
                ->where('status', 'success')
                ->sortByDesc('confidence')
                ->first();

            if ($bestTrace) {
                Log::warning("OCR Manager: No engine passed threshold. Using best fallback engine [{$bestTrace['engine']}] with confidence {$bestTrace['confidence']}%");
                // Re-run or fetch from memory if we saved the result object
                // Let's execute again or we can structure our loop to store the result objects
                // To avoid re-running, let's keep track of results in the loop.
            }
        }

        // Refactored Loop to save result objects
        $finalResult = null;
        $bestResult = null;
        $fallbackTraces = [];

        foreach ($priorityChain as $engineName) {
            try {
                $engine = $this->engine($engineName);
                
                if (in_array($engineName, ['tesseract', 'paddleocr', 'easyocr']) && !config("ocr.engines.{$engineName}.enabled", true)) {
                    continue;
                }

                $result = $this->executeWithRetry($engine, $filePath, $options);

                $fallbackTraces[] = [
                    'engine' => $engineName,
                    'status' => 'success',
                    'confidence' => $result->confidence,
                    'duration' => $result->duration,
                    'cost' => $result->cost
                ];

                if ($result->confidence >= $minConfidence) {
                    $finalResult = $result;
                    break;
                }

                if ($bestResult === null || $result->confidence > $bestResult->confidence) {
                    $bestResult = $result;
                }

                Log::warning("OCR Manager: Engine [{$engineName}] confidence {$result->confidence}% is below threshold {$minConfidence}%. Cascading.");

            } catch (\Throwable $e) {
                Log::error("OCR Manager: Engine [{$engineName}] failed: " . $e->getMessage());
                $fallbackTraces[] = [
                    'engine' => $engineName,
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ];
            }
        }

        if (!$finalResult) {
            if ($bestResult) {
                Log::warning("OCR Manager: All engines fell below threshold. Selecting best available [{$bestResult->engine}] with confidence {$bestResult->confidence}%");
                $finalResult = $bestResult;
            } else {
                throw new \RuntimeException("OCR Manager: All engines in priority chain failed. Traces: " . json_encode($fallbackTraces));
            }
        }

        // Attach fallback history to metadata
        $finalResult->metadata['fallback_traces'] = $fallbackTraces;

        // 4. Save to Cache
        if ($cacheEnabled && !empty($cacheKey) && $finalResult) {
            Cache::put($cacheKey, $finalResult->toArray(), config('ocr.cache.ttl', 1440));
        }

        return $finalResult;
    }

    /**
     * Execute an engine extraction with configured retries.
     */
    protected function executeWithRetry(OcrEngineInterface $engine, string $filePath, array $options): OcrResult
    {
        $attempts = config('ocr.retry.attempts', 3);
        $backoffMs = config('ocr.retry.backoff_ms', 500);

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            try {
                return $engine->extract($filePath, $options);
            } catch (\Throwable $e) {
                // If it is a final attempt or engine-specific config error, throw immediately
                if ($attempt === $attempts) {
                    throw $e;
                }
                
                // Do not retry local command failures due to missing binaries
                if (str_contains($e->getMessage(), 'not found') || str_contains($e->getMessage(), 'not recognized')) {
                    throw $e;
                }

                $delay = $backoffMs * pow(2, $attempt - 1);
                Log::warning("OCR Manager: Engine [{$engine->getName()}] attempt {$attempt} failed. Retrying in {$delay}ms. Error: {$e->getMessage()}");
                usleep($delay * 1000);
            }
        }

        throw new \RuntimeException("OCR Manager: Retry logic exceeded for engine " . $engine->getName());
    }

    /**
     * Automatically apply routing rules by analyzing inputs.
     */
    protected function applyAutoRouting(string $filePath, array $options): array
    {
        if (isset($options['language'])) {
            return $options;
        }

        // Analyze file content or name to guess language
        $fileName = strtolower(basename($filePath));
        
        if (str_contains($fileName, 'hindi') || str_contains($fileName, 'hi_')) {
            $options['language'] = 'hindi';
        } elseif (str_contains($fileName, 'mixed') || str_contains($fileName, 'bi_')) {
            $options['language'] = 'mixed';
        } else {
            // Default to english if no language marker is found
            $options['language'] = 'english'; 
        }

        return $options;
    }

    /**
     * Resolve priority list dynamically based on input specifications.
     */
    protected function resolvePriorityChain(string $filePath, array $options): array
    {
        $defaultPriority = config('ocr.priority', ['tesseract', 'paddleocr', 'easyocr', 'gemini', 'openai']);
        $language = $options['language'] ?? 'english';
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        // Rule 1: PDF scanned files can be large. Gemini multimodal handles PDFs natively,
        // while Tesseract/Paddleocr/Easyocr require splitting pages into images first.
        // If it's a PDF, we favor Gemini directly.
        if ($extension === 'pdf') {
            return ['gemini', 'paddleocr', 'openai', 'easyocr', 'tesseract'];
        }

        // Rule 2: If language is Hindi, PaddleOCR and EasyOCR are far superior locally to Tesseract.
        // Tesseract defaults to Eng, hin is extremely low accuracy.
        // Therefore, we swap local priorities to favor PaddleOCR.
        if ($language === 'hindi') {
            return ['paddleocr', 'easyocr', 'gemini', 'openai', 'tesseract'];
        }

        if ($language === 'mixed') {
            return ['paddleocr', 'gemini', 'easyocr', 'openai', 'tesseract'];
        }

        // Rule 3: For pure English images, Tesseract is extremely fast, free, and accurate.
        return $defaultPriority;
    }
}
