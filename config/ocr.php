<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default OCR Engine Priority Chain
    |--------------------------------------------------------------------------
    |
    | The default order of engines used when parsing documents.
    | The system will cascade through this list on failure or low confidence.
    |
    */
    'priority' => [
        'tesseract',
        'paddleocr',
        'easyocr',
        'gemini',
        'openai',
    ],

    /*
    |--------------------------------------------------------------------------
    | Confidence Thresholds
    |--------------------------------------------------------------------------
    |
    | Thresholds for determining when an OCR result is of sufficient quality.
    | If the calculated confidence score falls below this minimum, the system
    | will trigger the fallback to the next engine in the priority chain.
    |
    */
    'min_confidence' => 75.0,

    /*
    |--------------------------------------------------------------------------
    | Caching Configuration
    |--------------------------------------------------------------------------
    |
    | Caching OCR results reduces costs and latency for duplicate files.
    |
    */
    'cache' => [
        'enabled' => env('OCR_CACHE_ENABLED', true),
        'ttl' => env('OCR_CACHE_TTL', 1440), // in minutes (24 hours)
        'prefix' => 'ocr_cache:',
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    |
    | Transient/network errors will be retried before falling back.
    |
    */
    'retry' => [
        'attempts' => 3,
        'backoff_ms' => 500, // exponential backoff base delay
    ],

    /*
    |--------------------------------------------------------------------------
    | Engine Specific Configs
    |--------------------------------------------------------------------------
    |
    | Configuration details, simulated speeds, costs, and paths for engines.
    |
    */
    'engines' => [
        'tesseract' => [
            'enabled' => env('OCR_TESSERACT_ENABLED', true),
            'command' => env('OCR_TESSERACT_PATH', 'tesseract'),
            // Cost is $0 for local open-source
            'cost_per_page' => 0.0,
            'simulated_speed' => 0.8, // seconds
            'simulated_confidence' => [
                'english' => 85.0,
                'hindi' => 40.0, // Low native Hindi support without devnagari traineddata
                'mixed' => 55.0,
                'pdf' => 80.0,
            ],
        ],

        'paddleocr' => [
            'enabled' => env('OCR_PADDLEOCR_ENABLED', true),
            'command' => env('OCR_PADDLEOCR_PATH', 'paddleocr'),
            'cost_per_page' => 0.0,
            'simulated_speed' => 1.5, // seconds
            'simulated_confidence' => [
                'english' => 90.0,
                'hindi' => 88.0, // Excellent bilingual/multilingual
                'mixed' => 89.0,
                'pdf' => 91.0,
            ],
        ],

        'easyocr' => [
            'enabled' => env('OCR_EASYOCR_ENABLED', true),
            'command' => env('OCR_EASYOCR_PATH', 'easyocr'),
            'cost_per_page' => 0.0,
            'simulated_speed' => 1.8, // seconds
            'simulated_confidence' => [
                'english' => 88.0,
                'hindi' => 80.0,
                'mixed' => 83.0,
                'pdf' => 85.0,
            ],
        ],

        'gemini' => [
            'enabled' => env('OCR_GEMINI_ENABLED', true),
            // $0.075 / 1M input tokens + $0.002 per page (image)
            'cost_per_page' => 0.003, 
            'simulated_speed' => 2.2, // seconds
            'simulated_confidence' => [
                'english' => 98.0,
                'hindi' => 97.0,
                'mixed' => 98.0,
                'pdf' => 99.0,
            ],
        ],

        'openai' => [
            'enabled' => env('OCR_OPENAI_ENABLED', true),
            // $0.15 / 1M input tokens + $0.003 per page (image)
            'cost_per_page' => 0.005,
            'simulated_speed' => 2.5, // seconds
            'simulated_confidence' => [
                'english' => 97.0,
                'hindi' => 95.0,
                'mixed' => 96.0,
                'pdf' => 97.0,
            ],
        ],
    ],
];
