<?php

namespace App\Domains\Jobs\Services\Ai;

use App\Domains\Jobs\Services\Ai\Contracts\AiProviderInterface;
use App\Domains\Jobs\Services\Ai\Providers\ClaudeProvider;
use App\Domains\Jobs\Services\Ai\Providers\GeminiProvider;
use App\Domains\Jobs\Services\Ai\Providers\OpenAiProvider;

class AiManager
{
    /**
     * Resolve the requested AI provider driver.
     *
     * @param string|null $driver
     * @return AiProviderInterface
     */
    public function driver(?string $driver = null): AiProviderInterface
    {
        $driver = $driver ?: config('services.ai.provider', 'gemini');

        return match (strtolower($driver)) {
            'openai' => new OpenAiProvider(),
            'claude'  => new ClaudeProvider(),
            'gemini'  => new GeminiProvider(),
            default   => throw new \InvalidArgumentException("AI Provider [{$driver}] is not supported."),
        };
    }
}
