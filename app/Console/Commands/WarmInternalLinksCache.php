<?php

namespace App\Console\Commands;

use App\Domains\Jobs\Services\InternalLinkingService;
use Illuminate\Console\Command;

class WarmInternalLinksCache extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'internal-links:warm-cache
                            {--type= : Warm only a specific post type (job, result, admit_card, etc.)}
                            {--flush : Flush all caches before warming}';

    /**
     * The console command description.
     */
    protected $description = 'Pre-compute and cache internal link sets for all published posts';

    /**
     * Execute the console command.
     */
    public function handle(InternalLinkingService $service): int
    {
        $this->components->info('Internal Links Cache Warmup');

        // Flush if requested
        if ($this->option('flush')) {
            $this->components->task('Flushing existing caches', function () use ($service) {
                $service->flushCache();
                return true;
            });
        }

        $postType = $this->option('type');

        if ($postType) {
            $this->components->info("Warming cache for post type: {$postType}");
        } else {
            $this->components->info('Warming cache for all published posts');
        }

        $startTime = microtime(true);

        $count = $service->warmCache($postType);

        $elapsed = round(microtime(true) - $startTime, 2);

        $this->components->info("Warmed {$count} link sets in {$elapsed}s");

        return Command::SUCCESS;
    }
}
