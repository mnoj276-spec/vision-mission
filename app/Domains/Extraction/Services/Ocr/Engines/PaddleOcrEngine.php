<?php

namespace App\Domains\Extraction\Services\Ocr\Engines;

use App\Domains\Extraction\Services\Ocr\OcrResult;
use Illuminate\Support\Facades\Process;

class PaddleOcrEngine extends BaseEngine
{
    protected string $name = 'paddleocr';

    /**
     * Resolve the path to the Python executable inside the paddleocr_env venv.
     */
    protected function getVenvPythonPath(): string
    {
        $basePath = base_path('paddleocr_env');
        
        // Windows venv structure
        $windowsPath = $basePath . DIRECTORY_SEPARATOR . 'Scripts' . DIRECTORY_SEPARATOR . 'python.exe';
        if (file_exists($windowsPath)) {
            return $windowsPath;
        }
        
        // Linux/macOS venv structure
        $unixPath = $basePath . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'python';
        if (file_exists($unixPath)) {
            return $unixPath;
        }

        return '';
    }

    /**
     * Resolve the path to the paddleocr_wrapper.py script.
     */
    protected function getWrapperScriptPath(): string
    {
        return base_path('scripts' . DIRECTORY_SEPARATOR . 'paddleocr_wrapper.py');
    }

    public function isAvailable(): bool
    {
        if (app()->environment('testing')) {
            return false;
        }

        try {
            $pythonPath = $this->getVenvPythonPath();
            $wrapperPath = $this->getWrapperScriptPath();

            if (empty($pythonPath) || !file_exists($pythonPath)) {
                return false;
            }
            if (!file_exists($wrapperPath)) {
                return false;
            }

            // Quick check: can we import paddleocr?
            $result = Process::run("\"{$pythonPath}\" -c \"import paddleocr; print('ok')\"");
            return $result->successful() && str_contains($result->output(), 'ok');
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
                'note' => 'PaddleOCR venv or wrapper unavailable. Falling back to local simulator.'
            ]);
        }

        // Real PaddleOCR Execution via Python wrapper
        try {
            $pythonPath = $this->getVenvPythonPath();
            $wrapperPath = $this->getWrapperScriptPath();
            
            // Map our generic language to PaddleOCR tags
            $langTag = 'en';
            if ($language === 'hindi') {
                $langTag = 'hi';
            } elseif ($language === 'mixed') {
                $langTag = 'en+hi';
            }

            // Build command
            $cmd = "\"{$pythonPath}\" \"{$wrapperPath}\" --image \"{$filePath}\" --lang {$langTag} --use_angle_cls";

            // Pass specific pages if provided
            if (!empty($options['pages']) && is_array($options['pages'])) {
                $pagesStr = implode(',', $options['pages']);
                $cmd .= " --pages {$pagesStr}";
            }

            $processResult = Process::timeout(120)->run($cmd);
            
            $duration = microtime(true) - $startTime;
            $cost = 0.0;

            if ($processResult->successful()) {
                $output = trim($processResult->output());
                $jsonResult = json_decode($output, true);

                if (json_last_error() === JSON_ERROR_NONE && !empty($jsonResult['text'])) {
                    $text = $jsonResult['text'];
                    $confidence = $jsonResult['confidence'] ?? $this->computeConfidenceHeuristic($text, $language);

                    return new OcrResult($text, $confidence, $this->getName(), $duration, $cost, [
                        'cli_output' => $output,
                        'simulated' => false,
                        'line_count' => $jsonResult['line_count'] ?? 0,
                    ]);
                }

                // JSON decode failed or empty text — check for error in JSON
                if (is_array($jsonResult) && !empty($jsonResult['error'])) {
                    throw new \Exception("PaddleOCR wrapper error: " . $jsonResult['error']);
                }

                // Fallback: try to parse raw output
                $text = $output;
                $confidence = $this->computeConfidenceHeuristic($text, $language);
                return new OcrResult($text, $confidence, $this->getName(), $duration, $cost, [
                    'cli_output' => $output,
                    'simulated' => false,
                ]);
            }

            throw new \Exception("PaddleOCR wrapper failed: " . $processResult->errorOutput());

        } catch (\Throwable $e) {
            $duration = microtime(true) - $startTime;
            throw new \Exception("PaddleOCR runtime exception: " . $e->getMessage(), 0, $e);
        }
    }
}
