<?php

namespace App\Domains\Extraction\Services\Ocr\Engines;

use App\Domains\Extraction\Services\Ocr\OcrResult;
use Illuminate\Support\Facades\Process;

class EasyOcrEngine extends BaseEngine
{
    protected string $name = 'easyocr';

    public function isAvailable(): bool
    {
        if (app()->environment('testing')) {
            return false;
        }

        try {
            $cmd = $this->config['command'] ?? 'easyocr';
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
            $duration = $this->config['simulated_speed'] ?? 1.8;
            usleep((int)($duration * 1000000));
            
            $text = $this->getSimulatedText($language, $filePath);
            $confidence = $this->computeConfidenceHeuristic($text, $language);
            $cost = $this->config['cost_per_page'] ?? 0.0;

            return new OcrResult($text, $confidence, $this->getName(), $duration, $cost, [
                'simulated' => true,
                'note' => 'EasyOCR CLI/library unavailable. Falling back to local simulator.'
            ]);
        }

        // Real EasyOCR CLI Execution
        try {
            $cmd = $this->config['command'] ?? 'easyocr';
            
            // Languages: hi, en
            $langs = 'en';
            if ($language === 'hindi') {
                $langs = 'hi';
            } elseif ($language === 'mixed') {
                $langs = 'en hi';
            }

            // Command syntax: easyocr -l en hi -f [filePath]
            $processResult = Process::run("{$cmd} -l {$langs} -f \"{$filePath}\"");
            
            $duration = microtime(true) - $startTime;
            $cost = 0.0;

            if ($processResult->successful()) {
                $output = $processResult->output();
                
                // Parse standard EasyOCR line text. EasyOCR cli output format:
                // ([[coords], "text", confidence], ...)
                // Or simplified list text: "text"
                $text = $output;
                $confidence = $this->computeConfidenceHeuristic($text, $language);

                return new OcrResult($text, $confidence, $this->getName(), $duration, $cost, [
                    'cli_output' => $output,
                    'simulated' => false
                ]);
            }

            throw new \Exception("EasyOCR CLI failed: " . $processResult->error());

        } catch (\Throwable $e) {
            $duration = microtime(true) - $startTime;
            throw new \Exception("EasyOCR runtime exception: " . $e->getMessage(), 0, $e);
        }
    }
}
