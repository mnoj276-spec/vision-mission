<?php

namespace App\Domains\Extraction\Services\Ocr;

interface OcrEngineInterface
{
    /**
     * Perform OCR on the target file.
     *
     * @param string $filePath Absolute path to the document file.
     * @param array $options Heuristics or instructions like ['language' => 'hindi'].
     * @return OcrResult
     */
    public function extract(string $filePath, array $options = []): OcrResult;

    /**
     * Get the identifier name of the engine.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Check if the engine is available (binaries / keys configured).
     *
     * @return bool
     */
    public function isAvailable(): bool;
}
