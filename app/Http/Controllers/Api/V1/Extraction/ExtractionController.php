<?php

namespace App\Http\Controllers\Api\V1\Extraction;

use App\Http\Controllers\Controller;
use App\Models\ExtractedNotification;
use App\Models\JobPost;
use App\Models\Category;
use App\Models\State;
use App\Models\Department;
use App\Models\Qualification;
use App\Domains\Extraction\Jobs\ProcessNotificationExtractionJob;
use App\Domains\Scrapers\Services\FingerprintService;
use App\Services\UrlSecurity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ExtractionController extends Controller
{
    /**
     * Upload a notification file or provide a URL to extract.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required_without:url|file|max:20480', // max 20MB
            'url'  => 'required_without:file|url',
        ]);

        try {
            $filePath = null;
            $originalName = null;
            $fileType = null;

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $originalName = $file->getClientOriginalName();
                $fileType = strtolower($file->getClientOriginalExtension());
                
                // Store file securely
                $storedPath = $file->store('extractions', 'local');
                $filePath = Storage::disk('local')->path($storedPath);
            } else {
                $url = $request->input('url');
                
                // Mitigate SSRF
                if (!UrlSecurity::isSafeUrl($url)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'SSRF Block: The URL domain is not allowed.',
                    ], 400);
                }

                // Download file
                Log::info("Universal Extraction Engine: Downloading remote file from {$url}");
                $response = Http::timeout(30)->get($url);
                if ($response->failed()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to download file from the provided URL.',
                    ], 400);
                }

                $originalName = basename(parse_url($url, PHP_URL_PATH) ?: 'notification');
                $fileType = strtolower(pathinfo($originalName, PATHINFO_EXTENSION)) ?: 'html';

                $storedPath = 'extractions/' . uniqid() . '_' . $originalName;
                Storage::disk('local')->put($storedPath, $response->body());
                $filePath = Storage::disk('local')->path($storedPath);
            }

            // Create ExtractedNotification record
            $notification = ExtractedNotification::create([
                'file_path'          => $filePath,
                'original_filename'  => $originalName,
                'file_type'          => $fileType,
                'status'             => 'pending',
                'validation_status'  => 'pending',
            ]);

            // Dispatch extraction queue job
            ProcessNotificationExtractionJob::dispatch($notification);

            return response()->json([
                'success' => true,
                'id'      => $notification->id,
                'message' => 'Notification uploaded successfully and queued for processing.',
            ], 202);

        } catch (\Exception $e) {
            Log::error("Failed to upload/download notification: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get the processing status and extraction details of a notification.
     */
    public function status($id)
    {
        $notification = ExtractedNotification::find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found.',
            ], 404);
        }

        return response()->json([
            'success'           => true,
            'status'            => $notification->status,
            'file_type'         => $notification->file_type,
            'raw_text'          => $notification->raw_text,
            'extracted_data'    => $notification->extracted_data,
            'validation_status' => $notification->validation_status,
            'validation_errors' => $notification->validation_errors,
            'job_post_id'       => $notification->job_post_id,
        ]);
    }

    /**
     * Approve and publish the extracted metadata to job_posts.
     */
    public function approve(Request $request, $id)
    {
        $notification = ExtractedNotification::find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification not found.',
            ], 404);
        }

        if ($notification->status === 'approved') {
            return response()->json([
                'success'     => false,
                'message'     => 'This notification is already approved.',
                'job_post_id' => $notification->job_post_id,
            ], 422);
        }

        if ($notification->status !== 'processed') {
            return response()->json([
                'success' => false,
                'message' => 'Notification extraction is not yet complete or has failed.',
            ], 422);
        }

        $extractedData = $notification->extracted_data;
        if (empty($extractedData)) {
            return response()->json([
                'success' => false,
                'message' => 'No extracted metadata is available to approve.',
            ], 422);
        }

        try {
            $jobPost = DB::transaction(function () use ($notification, $extractedData, $request) {
                // Compile combined text for mapping semantic categories and states
                $textForMapping = ($extractedData['title'] ?? '') . ' ' . ($extractedData['department'] ?? '');

                // 1. Map Department
                $departmentVal = $extractedData['department'] ?? 'General Department';
                $department = Department::where('name', trim($departmentVal))->first();
                if (!$department) {
                    $code = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $departmentVal), 0, 4));
                    $baseCode = $code ?: 'DEPT';
                    $finalCode = $baseCode;
                    $counter = 1;
                    while (Department::where('code', $finalCode)->exists()) {
                        $finalCode = substr($baseCode, 0, 3) . $counter;
                        $counter++;
                    }
                    $department = Department::create([
                        'name' => trim($departmentVal),
                        'code' => $finalCode,
                    ]);
                }

                // 2. Map Qualification
                $qualificationVal = $extractedData['qualification'] ?? 'Graduate';
                $qualification = Qualification::where('name', trim($qualificationVal))
                    ->orWhere('slug', Str::slug($qualificationVal))
                    ->first();
                if (!$qualification) {
                    $qualification = Qualification::create([
                        'name' => trim($qualificationVal),
                        'slug' => Str::slug($qualificationVal),
                    ]);
                }

                // 3. Map Category semantically or fallback
                $categoryId = $this->mapCategorySemantically($textForMapping, 1);

                // 4. Map State semantically or fallback
                $stateId = $this->mapStateSemantically($textForMapping, 1);

                // 5. Parse Salary details
                $salaryText = $extractedData['salary'] ?? '';
                $salaryMin = null;
                $salaryMax = null;
                if (!empty($salaryText)) {
                    if (preg_match('/(?:Rs\.?|INR|₹)?\s*([\d,]+)\s*(?:-|to)\s*(?:Rs\.?|INR|₹)?\s*([\d,]+)/i', $salaryText, $m)) {
                        $salaryMin = (float) str_replace(',', '', $m[1]);
                        $salaryMax = (float) str_replace(',', '', $m[2]);
                    } elseif (preg_match('/(?:Rs\.?|INR|₹)?\s*([\d,]+)/i', $salaryText, $m)) {
                        $salaryMin = (float) str_replace(',', '', $m[1]);
                    }
                }

                // 6. Gather Important dates
                $dates = $extractedData['important_dates'] ?? [];
                $lastDate = $dates['last_date_to_apply'] ?? null;
                $startDate = $dates['start_date'] ?? null;
                $examDate = $dates['exam_date'] ?? null;
                $resultDate = $dates['result_date'] ?? null;

                // 7. Verify duplicates via Fingerprint Service
                $fingerprintService = app(FingerprintService::class);
                $officialLink = $extractedData['official_website'] ?? 'http://localhost/extraction';
                
                $fingerprint = $fingerprintService->generate([
                    'title'         => $extractedData['title'],
                    'department_id' => $department->id,
                    'source_url'    => $officialLink,
                    'publish_date'  => $lastDate ?? '',
                ]);

                $existingJob = JobPost::where('fingerprint', $fingerprint)->first();
                if ($existingJob) {
                    throw new \Exception("Duplicate job posting detected. Fingerprint matches Job Post #{$existingJob->id}.", 409);
                }

                // 8. Create Job Post
                $description = "Recruitment notification for " . ($extractedData['title'] ?? 'Job Post') . " at " . $department->name . ".";
                $slug = Str::slug($extractedData['title']) . '-' . rand(100, 999);

                $postType = $request->input('post_type') ?: $this->classifyPostType(
                    $extractedData['title'] ?? '', 
                    $notification->raw_text ?? ''
                );

                $jobPost = JobPost::create([
                    'title'                 => $extractedData['title'],
                    'slug'                  => $slug,
                    'description'           => $description,
                    'department_id'         => $department->id,
                    'state_id'              => $stateId,
                    'qualification_id'      => $qualification->id,
                    'category_id'           => $categoryId,
                    'post_type'             => $postType,
                    'vacancy_count'         => $extractedData['vacancy_count'] ?? 0,
                    'application_fee'       => $extractedData['application_fee'] ?? 0.00,
                    'official_website_link' => $officialLink,
                    'apply_link'            => $officialLink,
                    'age_limit'             => $extractedData['age_limit'] ?? null,
                    'salary_min'            => $salaryMin,
                    'salary_max'            => $salaryMax,
                    'last_date_to_apply'    => $lastDate,
                    'start_date'            => $startDate,
                    'exam_date'             => $examDate,
                    'result_date'           => $resultDate,
                    'status'                => 'published',
                    'published_at'          => now(),
                    'fingerprint'           => $fingerprint,
                ]);

                // Save relationship to extraction log
                $notification->update([
                    'status'      => 'approved',
                    'job_post_id' => $jobPost->id,
                ]);

                return $jobPost;
            });

            return response()->json([
                'success'   => true,
                'job_post'  => $jobPost,
                'message'   => 'Job notification approved and published successfully.',
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to approve job notification: " . $e->getMessage());
            
            $code = $e->getCode() === 409 ? 409 : 500;
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $code);
        }
    }

    /**
     * Map Category semantically.
     */
    protected function mapCategorySemantically(?string $text, int $defaultId): int
    {
        if (empty($text)) return $defaultId;
        $l = strtolower($text);
        if (str_contains($l, 'bank') || str_contains($l, 'sbi') || str_contains($l, 'rbi')) {
            $c = Category::where('slug', 'banking-finance')->first();
        } elseif (str_contains($l, 'railway') || str_contains($l, 'rrb')) {
            $c = Category::where('slug', 'railway-jobs')->first();
        } elseif (str_contains($l, 'defense') || str_contains($l, 'police') || str_contains($l, 'constable')) {
            $c = Category::where('slug', 'defense-jobs')->first();
        } elseif (str_contains($l, 'upsc') || str_contains($l, 'ssc') || str_contains($l, 'commission')) {
            $c = Category::where('slug', 'upsc-ssc-jobs')->first();
        }
        return isset($c) && $c ? $c->id : $defaultId;
    }

    /**
     * Map State semantically.
     */
    protected function mapStateSemantically(?string $text, int $defaultId): int
    {
        if (empty($text)) return $defaultId;
        $l = strtolower($text);
        if (str_contains($l, 'uttar pradesh')) {
            $s = State::where('code', 'UP')->first();
        } elseif (str_contains($l, 'maharashtra')) {
            $s = State::where('code', 'MH')->first();
        } elseif (str_contains($l, 'delhi')) {
            $s = State::where('code', 'DL')->first();
        } elseif (str_contains($l, 'karnataka')) {
            $s = State::where('code', 'KA')->first();
        }
        return isset($s) && $s ? $s->id : $defaultId;
    }

    /**
     * Classify the post type dynamically based on title and raw text.
     */
    protected function classifyPostType(string $title, string $rawText): string
    {
        $t = strtolower($title . ' ' . $rawText);
        if (str_contains($t, 'admit card') || str_contains($t, 'hall ticket') || str_contains($t, 'call letter')) return 'admit_card';
        if (str_contains($t, 'result') || str_contains($t, 'merit list') || str_contains($t, 'cutoff') || str_contains($t, 'scorecard')) return 'result';
        if (str_contains($t, 'answer key') || str_contains($t, 'response sheet')) return 'answer_key';
        if (str_contains($t, 'syllabus') || str_contains($t, 'exam pattern') || str_contains($t, 'scheme of examination')) return 'syllabus';
        if (str_contains($t, 'admission') || str_contains($t, 'entrance exam') || str_contains($t, 'counseling')) return 'admission';
        if (str_contains($t, 'scholarship') || str_contains($t, 'fellowship') || str_contains($t, 'stipend')) return 'scholarship';
        if (str_contains($t, 'notice') || str_contains($t, 'circular') || str_contains($t, 'corrigendum') || str_contains($t, 'postponement')) return 'notice';
        return 'job';
    }
}
