<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ScrapingSource;
use App\Models\ScrapingLog;
use App\Models\JobPost;
use App\Jobs\RunWebScraper;
use App\Repositories\Contracts\JobRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdminController extends Controller
{
    protected JobRepositoryInterface $jobRepo;

    public function __construct(JobRepositoryInterface $jobRepo)
    {
        $this->jobRepo = $jobRepo;
    }

    /**
     * Check administrative roles middleware
     */
    protected function checkAdminAuthorization(): ?JsonResponse
    {
        if (!Auth::check() || \Illuminate\Support\Facades\Gate::denies('admin-access')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Access Denied: Only authenticated administrators can inspect this panel.'
            ], 403);
        }
        return null;
    }

    /**
     * Retrieve Admin Dashboard analytics and tables (AJAX)
     */
    public function getAdminData(): JsonResponse
    {
        if ($authCheck = $this->checkAdminAuthorization()) {
            return $authCheck;
        }

        // 1. Fetch crawler sources
        $sources = ScrapingSource::all()->map(function ($src) {
            return [
                'id' => $src->id,
                'name' => $src->name,
                'url' => $src->source_url,
                'cron' => $src->cron_expression,
                'is_active' => $src->is_active
            ];
        });

        // 2. Fetch last 10 scraping logs
        $logs = ScrapingLog::with('source')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'source_name' => $log->source->name ?? 'Unknown Feed',
                    'status' => $log->status,
                    'items_found' => $log->items_found,
                    'error_message' => $log->error_message ?? 'N/A',
                    'time' => $log->created_at->format('d M Y H:i:s')
                ];
            });

        // 3. Fetch quarantined scraper items needing manual reviews
        $quarantines = ScrapingLog::with('source')
            ->where('status', 'quarantined')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($q) {
                return [
                    'id' => $q->id,
                    'source_name' => $q->source->name ?? 'Unknown Feed',
                    'raw_payload' => $q->raw_payload,
                    'errors' => $q->validation_errors,
                    'time' => $q->created_at->format('d M Y H:i')
                ];
            });

        // 4. Calculate total status metrics
        $metrics = [
            'total_sources' => ScrapingSource::count(),
            'active_sources' => ScrapingSource::where('is_active', true)->count(),
            'total_jobs_posted' => JobPost::count(),
            'success_runs' => ScrapingLog::where('status', 'success')->count(),
            'quarantine_runs' => ScrapingLog::where('status', 'quarantined')->count(),
            'failed_runs' => ScrapingLog::where('status', 'failed')->count()
        ];

        return response()->json([
            'status' => 'success',
            'data' => [
                'sources' => $sources,
                'logs' => $logs,
                'quarantines' => $quarantines,
                'metrics' => $metrics
            ]
        ]);
    }

    /**
     * Toggle Active State of a Scraping Target (AJAX)
     */
    public function toggleScraper(int $id): JsonResponse
    {
        if ($authCheck = $this->checkAdminAuthorization()) {
            return $authCheck;
        }

        $source = ScrapingSource::find($id);
        if (!$source) {
            return response()->json(['status' => 'error', 'message' => 'Scraper target not found.'], 404);
        }

        $source->is_active = !$source->is_active;
        $source->save();

        Log::info("Admin toggled Scraper ID: {$source->id} to state: " . ($source->is_active ? 'ACTIVE' : 'INACTIVE'));

        return response()->json([
            'status' => 'success',
            'is_active' => $source->is_active,
            'message' => "Scraper schedule successfully " . ($source->is_active ? 'activated' : 'deactivated')
        ]);
    }

    /**
     * Manually Trigger Background Crawling Task (AJAX)
     */
    public function runScraper(int $id): JsonResponse
    {
        if ($authCheck = $this->checkAdminAuthorization()) {
            return $authCheck;
        }

        $source = ScrapingSource::find($id);
        if (!$source) {
            return response()->json(['status' => 'error', 'message' => 'Scraper target not found.'], 404);
        }

        // Dispatch background crawling job
        RunWebScraper::dispatch($source);

        Log::info("Admin manually triggered crawling job for Scraper ID: {$source->id}");

        return response()->json([
            'status' => 'success',
            'message' => 'Crawling job successfully dispatched to background queue workers!'
        ]);
    }

    /**
     * Manual Override: Rescue and Publish a Quarantined Job Listing (AJAX)
     */
    public function rescueQuarantine(Request $request, int $logId): JsonResponse
    {
        if ($authCheck = $this->checkAdminAuthorization()) {
            return $authCheck;
        }

        $log = ScrapingLog::where('status', 'quarantined')->find($logId);
        if (!$log) {
            return response()->json(['status' => 'error', 'message' => 'Quarantine audit log not found.'], 404);
        }

        // Validate override data supplied by administrator
        $validator = \Validator::make($request->all(), [
            'title' => 'required|string|min:15',
            'last_date_to_apply' => 'required|date|after_or_equal:today',
            'official_website_link' => 'required|url',
            'apply_link' => 'nullable|url',
            'application_fee' => 'required|numeric|min:0',
            'vacancy_count' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'validation_error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $rawPayload = $log->raw_payload ?? [];
            $source = ScrapingSource::find($log->scraping_source_id);

            // Synthesize corrected job data
            $jobData = [
                'category_id' => $source->selectors_config['default_category_id'] ?? 1,
                'department_id' => $source->selectors_config['default_department_id'] ?? 1,
                'state_id' => $source->selectors_config['default_state_id'] ?? 1,
                'qualification_id' => $source->selectors_config['default_qualification_id'] ?? 1,
                'title' => $request->title,
                'slug' => str()->slug($request->title) . '-' . rand(100, 999),
                'description' => $rawPayload['raw_text'] ?? 'Manual override publish from quarantined audit listing.',
                'age_limit' => '21 - 32 Years',
                'salary_min' => 35400.00,
                'salary_max' => 112400.00,
                'vacancy_count' => $request->vacancy_count,
                'application_fee' => $request->application_fee,
                'official_website_link' => $request->official_website_link,
                'apply_link' => $request->apply_link ?? $request->official_website_link,
                'last_date_to_apply' => $request->last_date_to_apply,
                'status' => 'published', // Publish instantly on admin override
                'published_at' => Carbon::now()
            ];

            // Create recruitment post
            $jobPost = $this->jobRepo->create($jobData);

            // Update quarantine log to success state
            $log->update([
                'status' => 'success',
                'job_post_id' => $jobPost->id,
                'error_message' => 'Manual override approved and published by Admin.'
            ]);

            DB::commit();

            Log::info("Admin successfully rescued and published Quarantined Log ID: {$logId}");

            return response()->json([
                'status' => 'success',
                'message' => 'Quarantined listing successfully corrected, rescued, and published live!'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Rescue quarantine failure: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Critical database error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retrieve all registered users for role/active management (AJAX)
     */
    public function getUsersList(): JsonResponse
    {
        if ($authCheck = $this->checkAdminAuthorization()) {
            return $authCheck;
        }

        $users = \App\Models\User::orderBy('id', 'desc')->get()->map(function ($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'phone' => $u->phone ?? 'N/A',
                'role' => $u->role,
                'is_active' => $u->is_active
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'users' => $users
            ]
        ]);
    }

    /**
     * Manage user role or activation state (AJAX)
     */
    public function updateUser(Request $request, int $userId): JsonResponse
    {
        if ($authCheck = $this->checkAdminAuthorization()) {
            return $authCheck;
        }

        $user = \App\Models\User::find($userId);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User not found.'], 404);
        }

        // Prevent self-lockout or deactivation
        if ($user->id === Auth::id()) {
            return response()->json(['status' => 'error', 'message' => 'You cannot deactivate or alter your own administrator session!'], 400);
        }

        $validator = \Validator::make($request->all(), [
            'role' => 'nullable|string|in:admin,candidate',
            'is_active' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'validation_error',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->has('role')) {
            $user->role = $request->role;
        }

        if ($request->has('is_active')) {
            $user->is_active = (bool)$request->is_active;
        }

        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => "User parameters successfully synchronized!"
        ]);
    }

    /**
     * Publish a new manual job post (AJAX)
     */
    public function storeJob(Request $request): JsonResponse
    {
        if ($authCheck = $this->checkAdminAuthorization()) {
            return $authCheck;
        }

        $validator = \Validator::make($request->all(), [
            'title' => 'required|string|min:10',
            'category_id' => 'required|integer',
            'department_id' => 'required|integer',
            'state_id' => 'required|integer',
            'qualification_id' => 'required|integer',
            'description' => 'required|string|min:20',
            'salary_min' => 'required|numeric|min:0',
            'salary_max' => 'required|numeric|min:0',
            'vacancy_count' => 'required|integer|min:1',
            'application_fee' => 'required|numeric|min:0',
            'last_date_to_apply' => 'required|date|after_or_equal:today',
            'official_website_link' => 'required|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'validation_error',
                'errors' => $validator->errors()
            ], 422);
        }

        $jobData = [
            'category_id' => $request->category_id,
            'department_id' => $request->department_id,
            'state_id' => $request->state_id,
            'qualification_id' => $request->qualification_id,
            'title' => $request->title,
            'slug' => str()->slug($request->title) . '-' . rand(100, 999),
            'description' => $request->description,
            'age_limit' => '18 - 35 Years',
            'salary_min' => $request->salary_min,
            'salary_max' => $request->salary_max,
            'vacancy_count' => $request->vacancy_count,
            'application_fee' => $request->application_fee,
            'official_website_link' => $request->official_website_link,
            'apply_link' => $request->official_website_link,
            'last_date_to_apply' => $request->last_date_to_apply,
            'status' => 'published',
            'published_at' => Carbon::now()
        ];

        $jobPost = $this->jobRepo->create($jobData);

        return response()->json([
            'status' => 'success',
            'message' => 'Manual job announcement published successfully!',
            'job' => $jobPost
        ]);
    }

    /**
     * Save dynamic SEO configuration parameters (AJAX)
     */
    public function updateSeoSettings(Request $request): JsonResponse
    {
        if ($authCheck = $this->checkAdminAuthorization()) {
            return $authCheck;
        }

        $validator = \Validator::make($request->all(), [
            'meta_title' => 'required|string|max:255',
            'meta_description' => 'required|string|max:500',
            'meta_keywords' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'validation_error',
                'errors' => $validator->errors()
            ], 422);
        }

        $settings = [
            'meta_title' => $request->meta_title,
            'meta_description' => $request->meta_description,
            'meta_keywords' => $request->meta_keywords
        ];

        // Cache parameters to local JSON config
        $filePath = storage_path('app/seo_settings.json');
        file_put_contents($filePath, json_encode($settings));

        return response()->json([
            'status' => 'success',
            'message' => 'SEO Meta tags synchronized and cached successfully!'
        ]);
    }

    /**
     * Dedicated Admin Dashboard View Layout Page
     */
    public function dashboardView()
    {
        if (\Illuminate\Support\Facades\Gate::denies('admin-access')) {
            abort(403, 'Unauthorized access.');
        }

        $categories = \App\Models\Category::orderBy('name')->get();
        $departments = \App\Models\Department::orderBy('name')->get();
        $qualifications = \App\Models\Qualification::orderBy('id')->get();
        $states = \App\Models\State::orderBy('name')->get();

        // Load SEO configurations if cached
        $seoPath = storage_path('app/seo_settings.json');
        $seo = file_exists($seoPath) ? json_decode(file_get_contents($seoPath), true) : [
            'meta_title' => 'GovJobs - Premium Government Jobs Portal',
            'meta_description' => 'Browse and search live verified government recruitments across multiple departments.',
            'meta_keywords' => 'government jobs, state recruitments, dynamic portal'
        ];

        return view('admin.index', compact('categories', 'departments', 'qualifications', 'states', 'seo'));
    }

    /**
     * Get dynamic list of scrapers (AJAX)
     */
    public function getScrapersList(): JsonResponse
    {
        if ($authCheck = $this->checkAdminAuthorization()) {
            return $authCheck;
        }

        $sources = ScrapingSource::orderBy('id', 'desc')->get();
        return response()->json([
            'status' => 'success',
            'data' => $sources
        ]);
    }

    /**
     * Add new scraping target (AJAX)
     */
    public function storeScraper(Request $request): JsonResponse
    {
        if ($authCheck = $this->checkAdminAuthorization()) {
            return $authCheck;
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'source_url' => 'required|url',
            'cron_expression' => 'required|string|max:100',
            'is_active' => 'required|boolean',
            'default_category_id' => 'required|integer',
            'default_department_id' => 'required|integer',
            'default_state_id' => 'required|integer',
            'default_qualification_id' => 'required|integer',
            'title_selector' => 'required|string',
            'row_selector' => 'required|string',
            'link_selector' => 'required|string',
        ]);

        $selectorsConfig = [
            'row_selector' => $request->row_selector,
            'title_selector' => $request->title_selector,
            'link_selector' => $request->link_selector,
            'date_selector' => $request->date_selector ?? '',
            'default_category_id' => (int)$request->default_category_id,
            'default_department_id' => (int)$request->default_department_id,
            'default_state_id' => (int)$request->default_state_id,
            'default_qualification_id' => (int)$request->default_qualification_id,
        ];

        $source = ScrapingSource::create([
            'name' => $request->name,
            'source_url' => $request->source_url,
            'cron_expression' => $request->cron_expression,
            'is_active' => $request->is_active,
            'selectors_config' => $selectorsConfig,
        ]);

        // Audit Log
        \App\Models\AuditLog::create([
            'user_id' => Auth::id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent() ?? 'N/A',
            'action' => 'Create Crawler Target',
            'details' => "Created crawler target '{$source->name}' (ID: {$source->id})"
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'New Scraping target registered successfully!',
            'data' => $source
        ]);
    }

    /**
     * Update scraping source (AJAX)
     */
    public function updateScraperSource(Request $request, int $id): JsonResponse
    {
        if ($authCheck = $this->checkAdminAuthorization()) {
            return $authCheck;
        }

        $source = ScrapingSource::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'source_url' => 'required|url',
            'cron_expression' => 'required|string|max:100',
            'is_active' => 'required|boolean',
            'default_category_id' => 'required|integer',
            'default_department_id' => 'required|integer',
            'default_state_id' => 'required|integer',
            'default_qualification_id' => 'required|integer',
            'title_selector' => 'required|string',
            'row_selector' => 'required|string',
            'link_selector' => 'required|string',
        ]);

        $selectorsConfig = [
            'row_selector' => $request->row_selector,
            'title_selector' => $request->title_selector,
            'link_selector' => $request->link_selector,
            'date_selector' => $request->date_selector ?? '',
            'default_category_id' => (int)$request->default_category_id,
            'default_department_id' => (int)$request->default_department_id,
            'default_state_id' => (int)$request->default_state_id,
            'default_qualification_id' => (int)$request->default_qualification_id,
        ];

        $source->update([
            'name' => $request->name,
            'source_url' => $request->source_url,
            'cron_expression' => $request->cron_expression,
            'is_active' => $request->is_active,
            'selectors_config' => $selectorsConfig,
        ]);

        // Audit Log
        \App\Models\AuditLog::create([
            'user_id' => Auth::id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent() ?? 'N/A',
            'action' => 'Update Crawler Target',
            'details' => "Updated crawler target '{$source->name}' (ID: {$source->id})"
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Scraping source parameters updated successfully!',
            'data' => $source
        ]);
    }

    /**
     * Delete scraping source (AJAX)
     */
    public function deleteScraper(Request $request, int $id): JsonResponse
    {
        if ($authCheck = $this->checkAdminAuthorization()) {
            return $authCheck;
        }

        $source = ScrapingSource::findOrFail($id);
        $name = $source->name;
        $source->delete();

        // Audit Log
        \App\Models\AuditLog::create([
            'user_id' => Auth::id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent() ?? 'N/A',
            'action' => 'Delete Crawler Target',
            'details' => "Deleted crawler target '{$name}' (ID: {$id})"
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Scraping target deleted successfully!'
        ]);
    }

    /**
     * Get system activity logs (AJAX pagination)
     */
    public function getActivityLogs(Request $request): JsonResponse
    {
        if ($authCheck = $this->checkAdminAuthorization()) {
            return $authCheck;
        }

        $perPage = $request->integer('per_page', 10);
        $logs = \App\Models\AuditLog::with('user')
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => [
                'logs' => $logs->items(),
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'total' => $logs->total()
            ]
        ]);
    }
}

