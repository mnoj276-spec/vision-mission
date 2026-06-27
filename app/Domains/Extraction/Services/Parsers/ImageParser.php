<?php

namespace App\Domains\Extraction\Services\Parsers;

use App\Domains\Extraction\Services\OCRService;
use Illuminate\Support\Facades\Log;

/**
 * Enterprise Image Parser
 *
 * Thin wrapper delegating to OCRService with pre-processing:
 * - Validates image format (PNG, JPEG, TIFF, BMP, WebP)
 * - Extracts EXIF metadata (dimensions, DPI) for quality assessment
 * - Delegates to OCRService::extractText() for actual OCR
 * - Returns structured output including confidence flags
 */
class ImageParser
{
    protected OCRService $ocrService;

    public function __construct(OCRService $ocrService)
    {
        $this->ocrService = $ocrService;
    }

    /**
     * Extract text from an image file.
     *
     * @param string $filePath
     * @return string
     */
    public function extractText(string $filePath): string
    {
        $result = $this->extractStructured($filePath);
        return $result['text'] ?? '';
    }

    /**
     * Extract structured data from an image file.
     *
     * @param string $filePath
     * @return array
     */
    public function extractStructured(string $filePath): array
    {
        if (!file_exists($filePath)) {
            Log::error("Image file not found: {$filePath}");
            return $this->emptyResult();
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if (!$this->isValidFormat($extension)) {
            Log::error("Unsupported image format: {$extension} for file: {$filePath}");
            return $this->emptyResult();
        }

        // Get metadata
        $metadata = $this->extractMetadata($filePath, $extension);

        // Run OCR
        try {
            $text = $this->ocrService->extractText($filePath, $extension);
        } catch (\Exception $e) {
            Log::error("OCR extraction failed in ImageParser: " . $e->getMessage());
            $text = '';
        }

        // Determine confidence flag based on metadata and text quality
        $confidence = $this->calculateConfidence($text, $metadata);

        return [
            'text' => $text,
            'metadata' => $metadata,
            'confidence' => $confidence,
            'is_scanned' => true,
        ];
    }

    /**
     * Check if the image extension is supported.
     */
    protected function isValidFormat(string $extension): bool
    {
        return in_array($extension, ['png', 'jpg', 'jpeg', 'gif', 'bmp', 'tiff', 'tif', 'webp']);
    }

    /**
     * Extract metadata from image file.
     */
    protected function extractMetadata(string $filePath, string $extension): array
    {
        $metadata = [
            'extension' => $extension,
            'file_size' => filesize($filePath),
        ];

        // Try getting dimensions
        $imageSize = @getimagesize($filePath);
        if ($imageSize !== false) {
            $metadata['width'] = $imageSize[0] ?? null;
            $metadata['height'] = $imageSize[1] ?? null;
            $metadata['mime'] = $imageSize['mime'] ?? null;
        }

        // Try extracting EXIF metadata
        if (function_exists('exif_read_data') && in_array($extension, ['jpg', 'jpeg', 'tiff', 'tif'])) {
            try {
                $exif = @exif_read_data($filePath);
                if ($exif !== false) {
                    $metadata['exif'] = [
                        'Make' => $exif['Make'] ?? null,
                        'Model' => $exif['Model'] ?? null,
                        'DateTime' => $exif['DateTime'] ?? null,
                        'Software' => $exif['Software'] ?? null,
                        'XResolution' => $exif['XResolution'] ?? null,
                        'YResolution' => $exif['YResolution'] ?? null,
                    ];
                }
            } catch (\Exception $e) {
                // Ignore EXIF read errors
            }
        }

        return $metadata;
    }

    /**
     * Calculate parsing confidence score/flag.
     */
    protected function calculateConfidence(string $text, array $metadata): string
    {
        if (empty($text)) {
            return 'low';
        }

        // Standard checks: file size and resolution
        $width = $metadata['width'] ?? 0;
        $height = $metadata['height'] ?? 0;

        if ($width < 500 || $height < 500) {
            return 'low';
        }

        if (strlen($text) < 50) {
            return 'low';
        }

        // If the text looks normal/has common words
        if (str_contains(strtolower($text), 'recruitment') || str_contains(strtolower($text), 'vacancy') || str_contains(strtolower($text), 'notification')) {
            return 'high';
        }

        return 'medium';
    }

    /**
     * Return empty result structure.
     */
    protected function emptyResult(): array
    {
        return [
            'text' => '',
            'metadata' => [],
            'confidence' => 'low',
            'is_scanned' => true,
        ];
    }
}
