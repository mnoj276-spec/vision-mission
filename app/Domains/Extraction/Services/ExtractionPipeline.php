<?php

namespace App\Domains\Extraction\Services;

use App\Models\ExtractedNotification;
use App\Domains\Extraction\Services\Parsers\PdfParser;
use App\Domains\Extraction\Services\Parsers\DocumentParserService;
use Illuminate\Support\Facades\Log;

class ExtractionPipeline
{
    public function __construct(
        protected PdfParser $pdfParser,
        protected DocumentParserService $docParser,
        protected OCRService $ocrService,
        protected AiStructuringService $aiStructuringService,
        protected ValidationService $validationService
    ) {}

    /**
     * Run the extraction pipeline on a pending notification.
     *
     * @param ExtractedNotification $notification
     * @return ExtractedNotification
     */
    public function process(ExtractedNotification $notification): ExtractedNotification
    {
        $notification->update(['status' => 'processing']);

        try {
            $filePath = $notification->file_path;
            if (empty($filePath) || !file_exists($filePath)) {
                throw new \Exception("File path '{$filePath}' is empty or does not exist.");
            }

            // 1. File Type Detection
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            if (empty($extension)) {
                $extension = strtolower($notification->file_type);
            }

            // 2. Parser Selection & Initial Extraction
            $rawText = '';
            Log::info("Universal Extraction Engine: Parsing file {$filePath} as {$extension}");

            if ($extension === 'html' || $extension === 'htm') {
                $rawText = $this->extractHtmlText($filePath);
            } elseif ($extension === 'pdf') {
                $rawText = $this->pdfParser->extractText($filePath);
            } elseif (in_array($extension, ['docx', 'xlsx', 'doc', 'xls'])) {
                $rawText = $this->docParser->extractText($filePath, $extension);
            } elseif (in_array($extension, ['png', 'jpg', 'jpeg'])) {
                // For images, extract text using OCR directly
                $rawText = $this->ocrService->extractText($filePath, $extension);
            } else {
                throw new \Exception("Unsupported file type: {$extension}");
            }

            // 3. OCR Fallback for empty/short texts (scanned PDF / image-only doc)
            if ($extension === 'pdf' && strlen(trim($rawText)) < 150) {
                Log::info("Universal Extraction Engine: Standard PDF parser returned very short text. Falling back to OCR.");
                $rawText = $this->ocrService->extractText($filePath, 'pdf');
            }

            if (empty(trim($rawText))) {
                throw new \Exception("Extraction failed: could not parse text from file.");
            }

            // 4. AI Structuring
            Log::info("Universal Extraction Engine: Calling AI Structuring Service.");
            $structuredData = $this->aiStructuringService->structureText($rawText);

            // 5. Validation
            Log::info("Universal Extraction Engine: Validating structured data.");
            $validationResult = $this->validationService->validate($structuredData);

            // 6. Update Storage Record
            $notification->update([
                'raw_text'          => $rawText,
                'extracted_data'    => $structuredData,
                'validation_status' => $validationResult['isValid'] ? 'valid' : 'invalid',
                'validation_errors' => $validationResult['errors'] ?: null,
                'status'            => 'processed',
            ]);

            Log::info("Universal Extraction Engine: Finished processing record #{$notification->id}. Valid: " . ($validationResult['isValid'] ? 'yes' : 'no'));

            return $notification;

        } catch (\Throwable $e) {
            Log::error("Extraction Pipeline failed for record #{$notification->id}: " . $e->getMessage(), [
                'exception' => $e->getTraceAsString(),
            ]);

            $notification->update([
                'status'            => 'failed',
                'validation_status' => 'invalid',
                'validation_errors' => ['pipeline' => [$e->getMessage()]],
            ]);

            return $notification;
        }
    }

    /**
     * Helper to clean up HTML content.
     */
    protected function extractHtmlText(string $filePath): string
    {
        $html = file_get_contents($filePath);
        if (empty($html)) {
            return '';
        }
        // Remove style and script contents
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
        $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $html);
        
        $text = strip_tags($html);
        return trim(preg_replace('/\s+/', ' ', $text));
    }
}
