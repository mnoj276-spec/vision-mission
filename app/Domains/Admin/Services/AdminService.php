<?php

namespace App\Domains\Admin\Services;

use App\Domains\Admin\Repositories\Contracts\AuditLogRepositoryInterface;
use App\Domains\Admin\Services\Contracts\AdminServiceInterface;
use App\Domains\Scrapers\Repositories\Contracts\ScrapingSourceRepositoryInterface;
use Illuminate\Support\Facades\Log;

/**
 * AdminService
 *
 * Extracts admin-domain business logic that previously lived directly in
 * AdminController: analytics assembly, SEO file cache, and audit logging.
 * Controllers are now thin HTTP adapters.
 */
class AdminService implements AdminServiceInterface
{
    public function __construct(
        protected ScrapingSourceRepositoryInterface $scraperRepo,
        protected AuditLogRepositoryInterface $auditLogRepo
    ) {}

    /**
     * Fetch all data for the admin dashboard panel.
     */
    public function getDashboardData(): array
    {
        $allSources = $this->scraperRepo->getAll();

        $sources = $allSources->map(fn ($src) => [
            'id'        => $src->id,
            'name'      => $src->name,
            'url'       => $src->source_url,
            'cron'      => $src->cron_expression,
            'is_active' => $src->is_active,
        ]);

        $logs = $allSources->map(function ($src) {
            $log = $src->latestLog;
            if (!$log) {
                return null;
            }
            return [
                'id'            => $log->id,
                'source_name'   => $src->name,
                'status'        => $log->status,
                'items_found'   => $log->items_found,
                'error_message' => $log->error_message ?? 'N/A',
                'time'          => $log->created_at->format('d M Y H:i:s'),
            ];
        })->filter()->values();

        $quarantines = $this->scraperRepo->getQuarantinedLogs()->map(fn ($q) => [
            'id'          => $q->id,
            'source_name' => $q->source->name ?? 'Unknown Feed',
            'raw_payload' => $q->raw_payload,
            'errors'      => $q->validation_errors,
            'time'        => $q->created_at->format('d M Y H:i'),
        ]);

        return [
            'sources'     => $sources,
            'logs'        => $logs,
            'quarantines' => $quarantines,
            'metrics'     => $this->scraperRepo->getMetrics(),
        ];
    }

    /**
     * Persist SEO meta settings to JSON cache file.
     */
    public function updateSeoSettings(array $settings): void
    {
        $filePath = storage_path('app/seo_settings.json');
        file_put_contents($filePath, json_encode($settings, JSON_PRETTY_PRINT));
    }

    /**
     * Load SEO settings from the JSON cache with sensible defaults.
     */
    public function getSeoSettings(): array
    {
        $seoPath = storage_path('app/seo_settings.json');

        return file_exists($seoPath)
            ? json_decode(file_get_contents($seoPath), true)
            : [
                'meta_title'       => 'GovJobs - Premium Government Jobs Portal',
                'meta_description' => 'Browse and search live verified government recruitments across multiple departments.',
                'meta_keywords'    => 'government jobs, state recruitments, dynamic portal',
            ];
    }

    /**
     * Retrieve paginated admin activity logs.
     */
    public function getActivityLogs(int $perPage = 10): array
    {
        $logs = $this->auditLogRepo->getPaginated($perPage);

        return [
            'logs'         => $logs->items(),
            'current_page' => $logs->currentPage(),
            'last_page'    => $logs->lastPage(),
            'total'        => $logs->total(),
        ];
    }

    /**
     * Create an audit log entry.
     * Deduplicates the logAction() copy-paste from JobManagementController and MasterDataController.
     */
    public function logAction(int $userId, string $ip, string $userAgent, string $action, string $details): void
    {
        $this->auditLogRepo->create([
            'user_id'    => $userId,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'action'     => $action,
            'details'    => $details,
        ]);

        Log::info("Admin audit: [{$action}] {$details}");
    }
}
