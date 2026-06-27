<?php

namespace App\Domains\Scrapers\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Log;

class GenerateScalingReportCommand extends Command
{
    protected $signature = 'scraper:scaling-report';
    protected $description = 'Analyzes performance logs, queue sizes, delta savings, and outputs a horizontal scaling capacity report.';

    public function handle(): int
    {
        $this->info("================================================================================");
        $this->info("                    SCRAPING INFRASTRUCTURE SCALING REPORT                      ");
        $this->info("================================================================================");
        
        Log::info("Generating scraping infrastructure scaling report.");

        // 1. Core Source Statistics
        $totalActive = DB::table('scraping_sources')->where('is_active', true)->count();
        $totalInactive = DB::table('scraping_sources')->where('is_active', false)->count();
        
        $highPriority = DB::table('scraping_sources')->where('is_active', true)->where('priority', 'high')->count();
        $defaultPriority = DB::table('scraping_sources')->where('is_active', true)->where('priority', 'default')->count();
        $lowPriority = DB::table('scraping_sources')->where('is_active', true)->where('priority', 'low')->count();

        $this->line("Active Scraping Sources: " . $this->coloredValue($totalActive, 'cyan'));
        $this->line("  - High Priority:       " . $this->coloredValue($highPriority, 'yellow'));
        $this->line("  - Default Priority:    " . $this->coloredValue($defaultPriority, 'green'));
        $this->line("  - Low Priority:        " . $this->coloredValue($lowPriority, 'blue'));
        $this->line("Inactive Sources:        " . $this->coloredValue($totalInactive, 'red'));
        $this->line("--------------------------------------------------------------------------------");

        // 2. Queue Backlog Metrics
        $highSize = Queue::size('scrapers-high');
        $defaultSize = Queue::size('scrapers-default');
        $lowSize = Queue::size('scrapers-low');
        $totalBacklog = $highSize + $defaultSize + $lowSize;
        
        $failedJobsCount = DB::table('failed_jobs')->where('payload', 'like', '%RunWebScraper%')->count();

        $this->line("Queue Backlogs (Pending Tasks):");
        $this->line("  - scrapers-high:       " . $this->coloredValue($highSize, $highSize > 50 ? 'red' : 'green'));
        $this->line("  - scrapers-default:    " . $this->coloredValue($defaultSize, $defaultSize > 100 ? 'red' : 'green'));
        $this->line("  - scrapers-low:        " . $this->coloredValue($lowSize, $lowSize > 200 ? 'red' : 'green'));
        $this->line("  - Total Backlog:       " . $this->coloredValue($totalBacklog, $totalBacklog > 100 ? 'red' : 'green'));
        $this->line("Failed Scraping Jobs:    " . $this->coloredValue($failedJobsCount, $failedJobsCount > 0 ? 'red' : 'green'));
        $this->line("--------------------------------------------------------------------------------");

        // 3. Performance & Logging Analysis (Last 24 Hours)
        $oneDayAgo = now()->subDay();
        $logs = DB::table('scraping_logs')
            ->where('created_at', '>=', $oneDayAgo)
            ->get();

        $totalRuns = $logs->count();
        if ($totalRuns > 0) {
            $success = $logs->where('status', 'success')->count();
            $duplicate = $logs->where('status', 'duplicate')->count();
            $failed = $logs->where('status', 'failed')->count();
            $quarantined = $logs->where('status', 'quarantined')->count();

            $successRate = number_format(($success / $totalRuns) * 100, 2);
            $dupRate = number_format(($duplicate / $totalRuns) * 100, 2);
            $failRate = number_format(($failed / $totalRuns) * 100, 2);
            $quarRate = number_format(($quarantined / $totalRuns) * 100, 2);

            // 4. Incremental / Delta Crawl Savings calculation
            // We search logs where error_message contains '[Delta Crawl] 304' or raw_payload has unchanged = true
            $deltaSkips = 0;
            foreach ($logs as $log) {
                $payload = json_decode($log->raw_payload, true) ?: [];
                if (($payload['unchanged'] ?? false) === true || str_contains($log->error_message ?? '', '304 Not Modified')) {
                    $deltaSkips++;
                }
            }
            
            $deltaRate = number_format(($deltaSkips / $totalRuns) * 100, 2);
            // Estimate average webpage size is 150 KB, and headless renders consume ~2 seconds of CPU time
            $estimatedBandwidthSavedKB = $deltaSkips * 150;
            $estimatedBandwidthSavedMB = number_format($estimatedBandwidthSavedKB / 1024, 2);
            $estimatedCpuSavedSeconds = $deltaSkips * 2;

            $this->line("Execution Metrics (Last 24 Hours):");
            $this->line("  - Total Executed Jobs: " . $totalRuns);
            $this->line("  - Success Rate:        " . $this->coloredValue($successRate . '%', $successRate > 90 ? 'green' : 'yellow'));
            $this->line("  - Duplicate Rate:      " . $dupRate . '%');
            $this->line("  - Quarantine Rate:     " . $quarRate . '%');
            $this->line("  - Failure Rate:        " . $this->coloredValue($failRate . '%', $failRate < 5 ? 'green' : 'red'));
            $this->line("--------------------------------------------------------------------------------");
            $this->line("Delta Crawl Savings (304 Cache Hits):");
            $this->line("  - Total Skips:         " . $this->coloredValue($deltaSkips, 'green') . " ({$deltaRate}% of all runs)");
            $this->line("  - Bandwidth Saved:     " . $this->coloredValue($estimatedBandwidthSavedMB . " MB", 'green') . " (approx 150KB/page)");
            $this->line("  - Worker CPU Time:     " . $this->coloredValue($estimatedCpuSavedSeconds . " seconds", 'green'));
        } else {
            $this->warn("No scraper executions logged in the last 24 hours.");
        }
        $this->line("--------------------------------------------------------------------------------");

        // 5. Intelligent Recommendations
        $this->info("Capacity & Scaling Recommendations:");
        
        $hasRec = false;
        if ($totalBacklog > 50) {
            $this->line("  [!] " . $this->coloredValue("HIGH QUEUE CONGESTION", 'red') . ": Current queue backlog is {$totalBacklog}.");
            $this->line("      Recommendation: Scale up Laravel Horizon processes for supervisor-1 workers.");
            $this->line("      Action: Increase 'maxProcesses' in config/horizon.php for the scraper priority pools.");
            $hasRec = true;
        }

        if ($failedJobsCount > 5) {
            $this->line("  [!] " . $this->coloredValue("ELEVATED WORKER FAILURES", 'red') . ": Detected {$failedJobsCount} dead queue jobs.");
            $this->line("      Recommendation: Perform target check or verify proxy health.");
            $this->line("      Action: Execute 'php artisan scraper:recover-dead-queue' to test recovery.");
            $hasRec = true;
        }

        if ($totalRuns > 0 && ($failed / $totalRuns) > 0.1) {
            $this->line("  [!] " . $this->coloredValue("HIGH RECENT FAILURE RATE", 'red') . ": Scraper failure rate is " . ($failRate) . "%.");
            $this->line("      Recommendation: Rotate proxy servers or inspect HTML parser selector drift.");
            $hasRec = true;
        }
        
        if ($totalRuns > 0 && ($deltaSkips / $totalRuns) < 0.2) {
            $this->line("  [*] " . $this->coloredValue("LOW CACHE HIT RATIO", 'yellow') . ": Delta crawls represent only {$deltaRate}% of execution runs.");
            $this->line("      Note: Some target portals might not support ETag/Last-Modified conditional headers.");
            $hasRec = true;
        }

        if (!$hasRec) {
            $this->line("  [✓] " . $this->coloredValue("SYSTEM HEALTHY", 'green') . ": All worker processes, crawl frequencies, and queues are aligned.");
        }

        $this->info("================================================================================");
        return Command::SUCCESS;
    }

    private function coloredValue($val, $color): string
    {
        $codes = [
            'green' => "\033[32m",
            'red' => "\033[31m",
            'yellow' => "\033[33m",
            'cyan' => "\033[36m",
            'blue' => "\033[34m",
            'reset' => "\033[0m"
        ];
        return ($codes[$color] ?? '') . $val . $codes['reset'];
    }
}
