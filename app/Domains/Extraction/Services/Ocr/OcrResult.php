<?php

namespace App\Domains\Extraction\Services\Ocr;

class OcrResult
{
    public string $text;
    public float $confidence;
    public string $engine;
    public float $duration;
    public float $cost;
    public array $metadata;

    public function __construct(
        string $text,
        float $confidence,
        string $engine,
        float $duration,
        float $cost = 0.0,
        array $metadata = []
    ) {
        $this->text = $text;
        $this->confidence = $confidence;
        $this->engine = $engine;
        $this->duration = $duration;
        $this->cost = $cost;
        $this->metadata = $metadata;
    }

    /**
     * Convert the result structure to array representation.
     */
    public function toArray(): array
    {
        return [
            'text' => $this->text,
            'confidence' => $this->confidence,
            'engine' => $this->engine,
            'duration_seconds' => $this->duration,
            'cost' => $this->cost,
            'metadata' => $this->metadata,
        ];
    }
}
