<?php

namespace App\Domains\Extraction\Services;

use App\Domains\Extraction\Services\Ocr\OcrManager;
use Illuminate\Support\Facades\Log;

class OCRService
{
    protected OcrManager $manager;

    public function __construct(OcrManager $manager = null)
    {
        $this->manager = $manager ?: app(OcrManager::class);
    }

    /**
     * Perform OCR on images or scanned PDFs using the Hybrid OCR architecture.
     *
     * @param string $filePath
     * @param string $extension
     * @return string
     */
    public function extractText(string $filePath, string $extension): string
    {
        if (!file_exists($filePath)) {
            Log::error("OCR target file not found: {$filePath}");
            return '';
        }

        try {
            // Map file characteristics to options
            $options = [];
            $fileName = strtolower(basename($filePath));
            
            if (str_contains($fileName, 'hindi')) {
                $options['language'] = 'hindi';
            } elseif (str_contains($fileName, 'mixed')) {
                $options['language'] = 'mixed';
            }

            $result = $this->manager->extract($filePath, $options);
            return $result->text;

        } catch (\Exception $e) {
            Log::error("OCR Service wrapper exception: " . $e->getMessage());
            return '';
        }
    }
}

