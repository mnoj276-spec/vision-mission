<?php

namespace App\Domains\Extraction\Services\Ocr\Engines;

use App\Domains\Extraction\Services\Ocr\OcrResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiOcrEngine extends BaseEngine
{
    protected string $name = 'gemini';

    public function isAvailable(): bool
    {
        return !empty(config('services.ai.gemini.key')) || !empty(config('services.ai.key'));
    }

    public function extract(string $filePath, array $options = []): OcrResult
    {
        $startTime = microtime(true);
        $language = $options['language'] ?? 'english';
        
        $apiKey = config('services.ai.gemini.key') ?: config('services.ai.key');
        $model = config('services.ai.gemini.model', 'gemini-2.5-flash');

        if (empty($apiKey) || app()->environment('testing')) {
            // Simulator fallback
            $duration = $this->config['simulated_speed'] ?? 2.2;
            usleep((int)($duration * 1000000));
            
            $text = $this->getSimulatedText($language, $filePath);
            $confidence = $this->computeConfidenceHeuristic($text, $language);
            $cost = $this->config['cost_per_page'] ?? 0.003;

            return new OcrResult($text, $confidence, $this->getName(), $duration, $cost, [
                'simulated' => true,
                'model' => $model,
                'note' => 'Gemini API keys unavailable or running in testing context. Simulated.'
            ]);
        }

        try {
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $mimeType = $this->getMimeType($extension);
            
            if (!file_exists($filePath)) {
                throw new \Exception("File not found: {$filePath}");
            }
            $fileData = base64_encode(file_get_contents($filePath));

            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
            
            $payload = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => 'Perform OCR on the attached file. Transcribe all text verbatim. Maintain structure/layout where possible. Do not summarize, format, or introduce any extra commentary.'],
                            [
                                'inlineData' => [
                                    'mimeType' => $mimeType,
                                    'data' => $fileData,
                                ]
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.0,
                ]
            ];

            $response = Http::timeout(30)->post($url, $payload);
            $duration = microtime(true) - $startTime;

            if ($response->successful()) {
                $result = $response->json();
                $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
                
                // Calculate token-based cost
                // Gemini: $0.075 per 1M input tokens, image is approx 258 tokens + prompt (~60 tokens)
                // Let's model input token count at ~350, output token count at length of text / 4.
                $inputTokens = 350;
                $outputTokens = (int)(strlen($text) / 4);
                $inputCost = ($inputTokens / 1000000) * 0.075;
                $outputCost = ($outputTokens / 1000000) * 0.30;
                $cost = round($inputCost + $outputCost, 6);

                $confidence = $this->computeConfidenceHeuristic($text, $language);

                return new OcrResult($text, $confidence, $this->getName(), $duration, $cost, [
                    'model' => $model,
                    'simulated' => false,
                    'response_status' => $response->status(),
                    'input_tokens' => $inputTokens,
                    'output_tokens' => $outputTokens
                ]);
            }

            throw new \Exception("Gemini API call failed with status: " . $response->status() . " Body: " . $response->body());

        } catch (\Throwable $e) {
            $duration = microtime(true) - $startTime;
            Log::error("Gemini OCR Service Exception: " . $e->getMessage());
            throw new \Exception("Gemini OCR driver failure: " . $e->getMessage(), 0, $e);
        }
    }

    protected function getMimeType(string $extension): string
    {
        return match (strtolower($extension)) {
            'pdf' => 'application/pdf',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            default => 'application/octet-stream',
        };
    }
}
