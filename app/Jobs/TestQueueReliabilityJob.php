<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TestQueueReliabilityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 5;

    public function __construct(
        public bool $shouldFail = false,
        public string $uid = ''
    ) {
        $this->uid = empty($uid) ? uniqid() : $uid;
    }

    public function handle(): void
    {
        Log::info("TestQueueReliabilityJob executing. UID: {$this->uid}, shouldFail: " . ($this->shouldFail ? 'yes' : 'no'));
        
        if ($this->shouldFail) {
            throw new \Exception("Simulated job failure for UID: {$this->uid}");
        }

        Log::info("TestQueueReliabilityJob completed successfully. UID: {$this->uid}");
    }
}
