<?php

namespace App\Domains\Extraction\Services\Ocr\Engines;

use App\Domains\Extraction\Services\Ocr\OcrResult;
use Illuminate\Support\Facades\Process;

class PaddleOcrEngine extends BaseEngine
{
    protected string $name = 'paddleocr';

    public function isAvailable(): bool
    {
        if (app()->environment('testing')) {
            return false;
        }

        try {
            $cmd = $this->config['command'] ?? 'paddleocr';
            $result = Process::run("{$cmd} --help");
            return $result->successful();
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function extract(string $filePath, array $options = []): OcrResult
    {
        $startTime = microtime(true);
        $language = $options['language'] ?? 'english';
        
        if (!$this->isAvailable()) {
            // Run simulated execution
            $duration = $this->config['simulated_speed'] ?? 1.5;
            usleep((int)($duration * 1000000));
            
            $text = $this->getSimulatedText($language, $filePath);
            $confidence = $this->computeConfidenceHeuristic($text, $language);
            $cost = $this->config['cost_per_page'] ?? 0.0;

            return new OcrResult($text, $confidence, $this->getName(), $duration, $cost, [
                'simulated' => true,
                'note' => 'PaddleOCR CLI/library unavailable. Falling back to local simulator.'
            ]);
        }

        // Real PaddleOCR CLI Execution
        try {
            $cmd = $this->config['command'] ?? 'paddleocr';
            
            // Map our generic language to PaddleOCR tags: `hi`, `en`, `structure` (for layout analysis)
            $langTag = 'en';
            if ($language === 'hindi' || $language === 'mixed') {
                $langTag = 'hi';
            }

            // Command syntax: paddleocr --image_dir [filePath] --lang [lang]
            $processResult = Process::run("{$cmd} --image_dir \"{$filePath}\" --lang={$langTag} --use_angle_cls=true");
            
            $duration = microtime(true) - $startTime;
            $cost = 0.0;

            if ($processResult->successful()) {
                // PaddleOCR logs coordinates, confidence, and text to stdout.
                // We parse the output to assemble lines of text.
                $output = $processResult->output();
                $lines = [];
                
                // Sample format: [ [[x,y],[x,y]...], ("text", confidence) ]
                preg_match_all('/\(\'(.*?)\'\s*,\s*([\d\.]+)\)/', $output, $matches);
                if (!empty($matches[1])) {
                    $text = implode("\n", $matches[1]);
                    $confidence = count($matches[2]) > 0 ? (array_sum($matches[2]) / count($matches[2])) * 100 : 90.0;
                } else {
                    // Try simple fallback line assembly
                    $text = $output;
                    $confidence = $this->computeConfidenceHeuristic($text, $language);
                }

                return new OcrResult($text, $confidence, $this->getName(), $duration, $cost, [
                    'cli_output' => $output,
                    'simulated' => false
                ]);
            }

            throw new \Exception("PaddleOCR CLI failed: " . $processResult->error());

        } catch (\Throwable $e) {
            $duration = microtime(true) - $startTime;
            throw new \Exception("PaddleOCR runtime exception: " . $e->getMessage(), 0, $e);
        }
    }
}
