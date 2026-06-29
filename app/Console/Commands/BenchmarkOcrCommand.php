<?php

namespace App\Console\Commands;

use App\Domains\Extraction\Services\Ocr\OcrManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BenchmarkOcrCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:benchmark-ocr {file? : Custom document path to benchmark} {--lang= : Language constraint (english, hindi, mixed)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run a performance, latency, and cost benchmark across hybrid OCR engines';

    /**
     * Execute the console command.
     */
    public function handle(OcrManager $ocrManager): int
    {
        $customFilePath = $this->argument('file');
        $langOption = $this->option('lang');

        $this->info("=================================================================");
        $this->info("           HYBRID OCR ENGINE ARCHITECTURE BENCHMARK              ");
        $this->info("=================================================================");

        // Establish the files to benchmark
        $filesToBenchmark = [];

        if ($customFilePath) {
            if (!file_exists($customFilePath)) {
                $this->error("Custom file not found: {$customFilePath}");
                return 1;
            }
            $filesToBenchmark[] = [
                'name' => basename($customFilePath),
                'path' => $customFilePath,
                'lang' => $langOption ?: 'mixed',
                'type' => pathinfo($customFilePath, PATHINFO_EXTENSION),
            ];
        } else {
            // Setup default benchmark files in storage/app/ocr_benchmarks/
            $benchDir = 'ocr_benchmarks';
            Storage::disk('local')->makeDirectory($benchDir);

            $files = [
                'english_notice.png' => [
                    'content' => "Notification Details:\nJob Title: English Technical Assistant Recruitment 2026\nDepartment: DST\nVacancy: 45 Posts\nSalary: Rs 45000",
                    'lang' => 'english',
                    'type' => 'png'
                ],
                'hindi_notice.png' => [
                    'content' => "सरकारी नौकरी भर्ती अधिसूचना २०२६\nपद: तकनीकी सहायक भर्ती २०२६\nविभाग: विज्ञान और प्रौद्योगिकी विभाग\nपद संख्या: ४५\nवेतनमान: रु. ३५,४००",
                    'lang' => 'hindi',
                    'type' => 'png'
                ],
                'mixed_notice.png' => [
                    'content' => "Recruitment Notification 2026 (भर्ती सूचना २०२६)\nJob: Technical Assistant / तकनीकी सहायक\nDepartment: Science Dept (विज्ञान विभाग)\nSalary: Rs 35400 (रु ३५४००)",
                    'lang' => 'mixed',
                    'type' => 'png'
                ],
                'government_notice.pdf' => [
                    // Small mock PDF content structure
                    'content' => "%PDF-1.4\n%...\nJob Title: Government PDF Officer Recruitment 2026\nVacancy: 100 Posts\nSelection Process: Written Test and Interview\n%%EOF",
                    'lang' => 'mixed',
                    'type' => 'pdf'
                ]
            ];

            foreach ($files as $name => $meta) {
                $path = Storage::disk('local')->path($benchDir . '/' . $name);
                file_put_contents($path, $meta['content']);
                $filesToBenchmark[] = [
                    'name' => $name,
                    'path' => $path,
                    'lang' => $meta['lang'],
                    'type' => $meta['type'],
                ];
            }
        }

        $engines = ['tesseract', 'paddleocr', 'easyocr', 'gemini', 'openai'];

        foreach ($filesToBenchmark as $fileInfo) {
            $this->newLine();
            $this->info("Benchmarking File: {$fileInfo['name']} [Language: {$fileInfo['lang']}, Type: {$fileInfo['type']}]");
            $this->comment(str_repeat('-', 80));

            $headers = ['Engine Tier', 'Engine Name', 'Status', 'Confidence (%)', 'Latency (s)', 'Cost ($)', 'Features / Notes'];
            $rows = [];

            foreach ($engines as $engineName) {
                $tier = in_array($engineName, ['tesseract', 'paddleocr', 'easyocr']) ? 'Local' : 'Cloud / LLM';
                
                try {
                    // Force the engine and bypass cache to get real metric
                    $result = $ocrManager->extract($fileInfo['path'], [
                        'language' => $fileInfo['lang'],
                        'force_engine' => $engineName,
                        'skip_cache' => true,
                    ]);

                    $note = $result->metadata['note'] ?? ($result->metadata['simulated'] ? 'Simulated' : 'Natively Executed');
                    if (isset($result->metadata['model'])) {
                        $note .= " (" . $result->metadata['model'] . ")";
                    }

                    $rows[] = [
                        $tier,
                        ucfirst($engineName),
                        'SUCCESS',
                        sprintf("%.2f%%", $result->confidence),
                        sprintf("%.3fs", $result->duration),
                        sprintf("$%.6f", $result->cost),
                        $note,
                    ];
                } catch (\Throwable $e) {
                    $rows[] = [
                        $tier,
                        ucfirst($engineName),
                        'FAILED',
                        '0.00%',
                        '0.000s',
                        '$0.000000',
                        $e->getMessage(),
                    ];
                }
            }

            $this->table($headers, $rows);
        }

        $this->newLine();
        $this->info("=================================================================");
        $this->info("                     COST & PERFORMANCE ANALYTICS                ");
        $this->info("=================================================================");
        $this->comment("1. Tesseract (Local): Highly optimized for pure English images. Free & Fast.");
        $this->comment("2. PaddleOCR & EasyOCR (Local): Best bilingual local engines. Outstanding for Hindi/Mixed script.");
        $this->comment("3. Gemini (Cloud/LLM): Premium accuracy for Multi-page Scanned Government PDFs. Native layout preservation.");
        $this->comment("4. OpenAI Vision (Cloud/LLM): Premium accuracy for single image OCR. Does not natively support PDF.");
        $this->info("=================================================================");

        return 0;
    }
}
