<?php

namespace App\Domains\Extraction\Services\Ocr\Engines;

use App\Domains\Extraction\Services\Ocr\OcrResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiOcrEngine extends BaseEngine
{
    protected string $name = 'openai';

    public function isAvailable(): bool
    {
        return !empty(config('services.ai.openai.key')) || !empty(config('services.ai.key'));
    }

    public function extract(string $filePath, array $options = []): OcrResult
    {
        $startTime = microtime(true);
        $language = $options['language'] ?? 'english';
        
        $apiKey = config('services.ai.openai.key') ?: config('services.ai.key');
        $model = config('services.ai.openai.model', 'gpt-4o-mini');

        if (empty($apiKey) || app()->environment('testing')) {
            // Simulator fallback
            $duration = $this->config['simulated_speed'] ?? 2.5;
            usleep((int)($duration * 1000000));
            
            $text = $this->getSimulatedText($language, $filePath);
            $confidence = $this->computeConfidenceHeuristic($text, $language);
            $cost = $this->config['cost_per_page'] ?? 0.005;

            return new OcrResult($text, $confidence, $this->getName(), $duration, $cost, [
                'simulated' => true,
                'model' => $model,
                'note' => 'OpenAI API keys unavailable or running in testing context. Simulated.'
            ]);
        }

        try {
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            
            if (in_array($extension, ['pdf'])) {
                // OpenAI Chat API doesn't support PDF uploads natively for image input,
                // we'd have to convert PDF pages to images. Let's warn and fall back.
                Log::warning("OpenAI vision driver does not natively support PDF. Simulating or failing.");
                throw new \Exception("OpenAI Vision driver does not support native PDF inputs. Convert PDF to PNG first or use Gemini.");
            }

            $mimeType = $this->getMimeType($extension);
            $fileData = base64_encode(file_get_contents($filePath));

            $url = 'https://api.openai.com/v1/chat/completions';

            $payload = [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text', 
                                'text' => 'Perform OCR on the attached image and extract all text verbatim. Maintain formatting structure where possible. Do not summarize or add notes.'
                            ],
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => "data:{$mimeType};base64,{$fileData}"
                                ]
                            ]
                        ]
                    ]
                ],
                'temperature' => 0.0,
            ];

            $response = Http::withToken($apiKey)->timeout(30)->post($url, $payload);
            $duration = microtime(true) - $startTime;

            if ($response->successful()) {
                $result = $response->json();
                $text = $result['choices'][0]['message']['content'] ?? '';
                
                // Calculate token-based cost
                // OpenAI GPT-4o-mini: $0.150 per 1M input tokens, $0.600 per 1M output tokens
                // Image cost: low-detail is flat 85 tokens. High-detail is resolved by size. Let's assume low detail.
                $inputTokens = 85 + 50; // image + prompt
                $outputTokens = (int)(strlen($text) / 4);
                $inputCost = ($inputTokens / 1000000) * 0.15;
                $outputCost = ($outputTokens / 1000000) * 0.60;
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

            throw new \Exception("OpenAI API call failed with status: " . $response->status() . " Body: " . $response->body());

        } catch (\Throwable $e) {
            $duration = microtime(true) - $startTime;
            Log::error("OpenAI OCR Service Exception: " . $e->getMessage());
            throw new \Exception("OpenAI OCR driver failure: " . $e->getMessage(), 0, $e);
        }
    }

    protected function getMimeType(string $extension): string
    {
        return match (strtolower($extension)) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            default => 'image/jpeg',
        };
    }
}
