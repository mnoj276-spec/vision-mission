<?php

namespace App\Domains\Extraction\Services\Ocr\Engines;

use App\Domains\Extraction\Services\Ocr\OcrResult;
use Illuminate\Support\Facades\Process;

class TesseractEngine extends BaseEngine
{
    protected string $name = 'tesseract';

    public function isAvailable(): bool
    {
        if (app()->environment('testing')) {
            return false;
        }

        try {
            $cmd = $this->config['command'] ?? 'tesseract';
            $result = Process::run("{$cmd} --version");
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
            $duration = $this->config['simulated_speed'] ?? 0.8;
            usleep((int)($duration * 1000000)); // sleep to simulate latency
            
            $text = $this->getSimulatedText($language, $filePath);
            $confidence = $this->computeConfidenceHeuristic($text, $language);
            $cost = $this->config['cost_per_page'] ?? 0.0;

            return new OcrResult($text, $confidence, $this->getName(), $duration, $cost, [
                'simulated' => true,
                'note' => 'Tesseract binary unavailable. Falling back to local simulator.'
            ]);
        }

        // Real Tesseract Execution
        try {
            $cmd = $this->config['command'] ?? 'tesseract';
            $tempOutput = tempnam(sys_get_temp_dir(), 'tess_out');
            
            // Map our generic language parameter to Tesseract language tags
            $langTag = 'eng';
            if ($language === 'hindi') {
                $langTag = 'hin';
            } elseif ($language === 'mixed') {
                $langTag = 'eng+hin';
            }

            // Command syntax: tesseract [inputfile] [outputbase] -l [lang]
            $processResult = Process::run("{$cmd} \"{$filePath}\" \"{$tempOutput}\" -l {$langTag}");
            
            $duration = microtime(true) - $startTime;
            $cost = 0.0;

            // Tesseract appends .txt automatically to the output base
            $txtFile = $tempOutput . '.txt';

            if ($processResult->successful() && file_exists($txtFile)) {
                $text = file_get_contents($txtFile);
                @unlink($txtFile);
                @unlink($tempOutput);
                
                $confidence = $this->computeConfidenceHeuristic($text, $language);
                return new OcrResult($text, $confidence, $this->getName(), $duration, $cost, [
                    'cli_output' => $processResult->output(),
                    'simulated' => false
                ]);
            }

            @unlink($tempOutput);
            throw new \Exception("Tesseract CLI failed to parse text: " . $processResult->error());

        } catch (\Throwable $e) {
            $duration = microtime(true) - $startTime;
            throw new \Exception("Tesseract runtime exception: " . $e->getMessage(), 0, $e);
        }
    }
}
