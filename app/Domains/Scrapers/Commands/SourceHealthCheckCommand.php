<?php

namespace App\Domains\Scrapers\Commands;

use App\Models\ScrapingSource;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SourceHealthCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scraper:health-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Displays the current health status of all scraping sources.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sources = ScrapingSource::orderByRaw(
            "FIELD(health_status, 'critical', 'degraded', 'healthy', 'inactive')"
        )->orderBy('last_failed_at', 'desc')->get();

        if ($sources->isEmpty()) {
            $this->info("No scraping sources found.");
            return Command::SUCCESS;
        }

        $headers = ['ID', 'Name', 'Status', 'Fails', 'Last Success', 'Last Failure', 'Reason'];
        
        $rows = $sources->map(function ($source) {
            $statusStr = match($source->health_status) {
                'healthy'  => '<info>Healthy</info>',
                'degraded' => '<comment>Degraded</comment>',
                'critical' => '<error>Critical</error>',
                default    => '<fg=gray>Inactive</>',
            };

            $lastSuccess = $source->last_succeeded_at ? $source->last_succeeded_at->diffForHumans() : 'Never';
            $lastFailure = $source->last_failed_at ? $source->last_failed_at->diffForHumans() : 'N/A';
            $reason = Str::limit($source->last_failure_reason ?? '-', 40);

            return [
                $source->id,
                Str::limit($source->name, 25),
                $statusStr,
                $source->consecutive_failures,
                $lastSuccess,
                $lastFailure,
                $reason,
            ];
        })->toArray();

        $this->line('');
        $this->table($headers, $rows);
        $this->line('');
        
        $counts = $sources->countBy('health_status');
        $this->info(sprintf(
            "Summary: <info>%d Healthy</info> | <comment>%d Degraded</comment> | <error>%d Critical</error> | <fg=gray>%d Inactive</>",
            $counts->get('healthy', 0),
            $counts->get('degraded', 0),
            $counts->get('critical', 0),
            $counts->get('inactive', 0)
        ));
        
        return Command::SUCCESS;
    }
}
