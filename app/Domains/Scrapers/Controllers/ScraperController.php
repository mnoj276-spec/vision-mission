<?php

namespace App\Domains\Scrapers\Controllers;

use App\Domains\Admin\Services\Contracts\AdminServiceInterface;
use App\Domains\Jobs\Repositories\Contracts\JobRepositoryInterface;
use App\Domains\Scrapers\Jobs\RunWebScraper;
use App\Domains\Scrapers\Repositories\Contracts\ScrapingSourceRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Models\ScrapingLog;
use App\Models\ScrapingSource;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ScraperController — scraper CRUD, toggle, run, and quarantine rescue.
 * Extracted from the fat AdminController. Authorization via EnsureAdmin middleware.
 */
class ScraperController extends Controller
{
    public function __construct(
        protected ScrapingSourceRepositoryInterface $scraperRepo,
        protected JobRepositoryInterface            $jobRepo,
        protected AdminServiceInterface             $adminService
    ) {}

    public function index(): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => $this->scraperRepo->getAll()]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'                   => 'required|string|max:255',
            'source_url'             => 'required|url',
            'cron_expression'        => 'required|string|max:100',
            'is_active'              => 'required|boolean',
            'default_category_id'    => 'required|integer',
            'default_department_id'  => 'required|integer',
            'default_state_id'       => 'required|integer',
            'default_qualification_id'=> 'required|integer',
            'title_selector'         => 'required|string',
            'row_selector'           => 'required|string',
            'link_selector'          => 'required|string',
        ]);

        $source = $this->scraperRepo->create([
            'name'             => $request->name,
            'source_url'       => $request->source_url,
            'cron_expression'  => $request->cron_expression,
            'is_active'        => $request->is_active,
            'selectors_config' => [
                'row_selector'            => $request->row_selector,
                'title_selector'          => $request->title_selector,
                'link_selector'           => $request->link_selector,
                'date_selector'           => $request->date_selector ?? '',
                'default_category_id'     => (int) $request->default_category_id,
                'default_department_id'   => (int) $request->default_department_id,
                'default_state_id'        => (int) $request->default_state_id,
                'default_qualification_id'=> (int) $request->default_qualification_id,
            ],
        ]);

        $this->adminService->logAction(Auth::id(), $request->ip(), $request->userAgent() ?? 'N/A', 'Create Crawler Target', "Created '{$source->name}' (ID: {$source->id})");
        return response()->json(['status' => 'success', 'message' => 'New scraping target registered!', 'data' => $source]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $source = $this->scraperRepo->findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255', 'source_url' => 'required|url',
            'cron_expression' => 'required|string|max:100', 'is_active' => 'required|boolean',
            'default_category_id' => 'required|integer', 'default_department_id' => 'required|integer',
            'default_state_id' => 'required|integer', 'default_qualification_id' => 'required|integer',
            'title_selector' => 'required|string', 'row_selector' => 'required|string', 'link_selector' => 'required|string',
        ]);
        $source = $this->scraperRepo->update($source, [
            'name' => $request->name, 'source_url' => $request->source_url,
            'cron_expression' => $request->cron_expression, 'is_active' => $request->is_active,
            'selectors_config' => ['row_selector' => $request->row_selector, 'title_selector' => $request->title_selector, 'link_selector' => $request->link_selector, 'date_selector' => $request->date_selector ?? '', 'default_category_id' => (int)$request->default_category_id, 'default_department_id' => (int)$request->default_department_id, 'default_state_id' => (int)$request->default_state_id, 'default_qualification_id' => (int)$request->default_qualification_id],
        ]);
        $this->adminService->logAction(Auth::id(), $request->ip(), $request->userAgent() ?? 'N/A', 'Update Crawler Target', "Updated '{$source->name}' (ID: {$source->id})");
        return response()->json(['status' => 'success', 'message' => 'Scraping source updated!', 'data' => $source]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $source = $this->scraperRepo->findOrFail($id);
        $name   = $source->name;
        $this->scraperRepo->delete($source);
        $this->adminService->logAction(Auth::id(), $request->ip(), $request->userAgent() ?? 'N/A', 'Delete Crawler Target', "Deleted '{$name}' (ID: {$id})");
        return response()->json(['status' => 'success', 'message' => 'Scraping target deleted!']);
    }

    public function toggle(int $id): JsonResponse
    {
        $source = $this->scraperRepo->findOrFail($id);
        $source = $this->scraperRepo->toggle($source);
        Log::info("Admin toggled Scraper ID: {$source->id} to: " . ($source->is_active ? 'ACTIVE' : 'INACTIVE'));
        return response()->json(['status' => 'success', 'is_active' => $source->is_active, 'message' => 'Scraper schedule ' . ($source->is_active ? 'activated' : 'deactivated')]);
    }

    public function run(int $id): JsonResponse
    {
        $source = $this->scraperRepo->findOrFail($id);
        RunWebScraper::dispatch($source);
        Log::info("Admin manually triggered crawling job for Scraper ID: {$source->id}");
        return response()->json(['status' => 'success', 'message' => 'Crawling job dispatched to background queue!']);
    }

    public function rescue(Request $request, int $logId): JsonResponse
    {
        $log = $this->scraperRepo->findQuarantinedLog($logId);
        if (!$log) return response()->json(['status' => 'error', 'message' => 'Quarantine log not found.'], 404);

        $request->validate([
            'title'                 => 'required|string|min:15',
            'last_date_to_apply'    => 'required|date|after_or_equal:today',
            'official_website_link' => 'required|url',
            'apply_link'            => 'nullable|url',
            'application_fee'       => 'required|numeric|min:0',
            'vacancy_count'         => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();
            $rawPayload = $log->raw_payload ?? [];
            $source     = ScrapingSource::find($log->scraping_source_id);
            $jobData    = [
                'category_id'           => $source->selectors_config['default_category_id']      ?? 1,
                'department_id'         => $source->selectors_config['default_department_id']    ?? 1,
                'state_id'              => $source->selectors_config['default_state_id']         ?? 1,
                'qualification_id'      => $source->selectors_config['default_qualification_id'] ?? 1,
                'title'                 => $request->title,
                'slug'                  => str()->slug($request->title) . '-' . rand(100, 999),
                'description'           => $rawPayload['raw_text'] ?? 'Manual override publish from quarantine.',
                'age_limit'             => '21 - 32 Years',
                'salary_min'            => 35400.00,
                'salary_max'            => 112400.00,
                'vacancy_count'         => $request->vacancy_count,
                'application_fee'       => $request->application_fee,
                'official_website_link' => $request->official_website_link,
                'apply_link'            => $request->apply_link ?? $request->official_website_link,
                'last_date_to_apply'    => $request->last_date_to_apply,
                'status'                => 'published',
                'published_at'          => Carbon::now(),
            ];
            $jobPost = $this->jobRepo->create($jobData);
            $log->update(['status' => 'success', 'job_post_id' => $jobPost->id, 'error_message' => 'Manual override approved by Admin.']);
            DB::commit();
            Log::info("Admin rescued Quarantined Log ID: {$logId}");
            return response()->json(['status' => 'success', 'message' => 'Quarantined listing rescued and published!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Rescue quarantine failure: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()], 500);
        }
    }
}
