<?php

namespace App\Domains\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\SendEmailJob;
use App\Models\EmailLog;
use App\Models\JobPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MarketingController extends Controller
{
    /**
     * Fetch aggregated statistics for the marketing automation dashboard.
     */
    public function getStats(): JsonResponse
    {
        // 1. Overall engagement aggregates
        $overall = DB::table('email_logs')
            ->selectRaw("
                COUNT(case when status = 'sent' then 1 end) as total_sent,
                COUNT(case when status = 'failed' then 1 end) as total_failed,
                COUNT(opened_at) as total_opened,
                COUNT(clicked_at) as total_clicked
            ")
            ->first();

        $totalSent = (int) $overall->total_sent;
        $totalOpened = (int) $overall->total_opened;
        $totalClicked = (int) $overall->total_clicked;

        $openRate = $totalSent > 0 ? round(($totalOpened / $totalSent) * 100, 1) : 0;
        $ctr = $totalSent > 0 ? round(($totalClicked / $totalSent) * 100, 1) : 0;

        // 2. Metrics broken down by campaign/feature type
        $campaignBreakdown = DB::table('email_logs')
            ->select('campaign_type')
            ->selectRaw("
                COUNT(case when status = 'sent' then 1 end) as sent,
                COUNT(opened_at) as opened,
                COUNT(clicked_at) as clicked
            ")
            ->groupBy('campaign_type')
            ->get()
            ->map(function ($row) {
                $sent = (int) $row->sent;
                $opened = (int) $row->opened;
                $clicked = (int) $row->clicked;
                return [
                    'campaign_type' => $row->campaign_type,
                    'sent' => $sent,
                    'opened' => $opened,
                    'clicked' => $clicked,
                    'open_rate' => $sent > 0 ? round(($opened / $sent) * 100, 1) : 0,
                    'ctr' => $sent > 0 ? round(($clicked / $sent) * 100, 1) : 0,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => [
                'overall' => [
                    'sent' => $totalSent,
                    'failed' => (int) $overall->total_failed,
                    'opened' => $totalOpened,
                    'clicked' => $totalClicked,
                    'open_rate' => $openRate,
                    'ctr' => $ctr,
                ],
                'campaigns' => $campaignBreakdown,
            ],
        ]);
    }

    /**
     * Retrieve paginated list of recently sent emails.
     */
    public function getLogs(Request $request): JsonResponse
    {
        $perPage = $request->integer('per_page', 10);

        $logs = EmailLog::with('user:id,name,email')
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'status' => 'success',
            'data' => $logs,
        ]);
    }

    /**
     * Manually trigger a test campaign email to a specified destination.
     */
    public function triggerTest(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'campaign_type' => 'required|string|in:welcome_1,welcome_2,welcome_3,job_alert,result_alert,admit_card_alert,weekly_digest,re_engagement',
        ]);

        $email = $request->input('email');
        $campaignType = $request->input('campaign_type');

        Log::info("Admin manually triggered test campaign: {$campaignType} to {$email}");

        $payload = [
            'name' => 'Admin Tester',
        ];

        // Retrieve mock vacancies depending on mail features
        switch ($campaignType) {
            case 'job_alert':
            case 're_engagement':
                $payload['job_ids'] = JobPost::published()->jobs()->latest()->take(3)->pluck('id')->toArray();
                break;

            case 'result_alert':
                $payload['job_ids'] = JobPost::published()->results()->latest()->take(2)->pluck('id')->toArray();
                break;

            case 'admit_card_alert':
                $payload['job_ids'] = JobPost::published()->admitCards()->latest()->take(2)->pluck('id')->toArray();
                break;

            case 'weekly_digest':
                $payload['recent_job_ids'] = JobPost::published()->jobs()->latest()->take(3)->pluck('id')->toArray();
                $payload['admit_card_ids'] = JobPost::published()->admitCards()->latest()->take(2)->pluck('id')->toArray();
                $payload['result_ids'] = JobPost::published()->results()->latest()->take(2)->pluck('id')->toArray();
                break;
        }

        // Dispatch SendEmailJob onto the queue
        SendEmailJob::dispatch($email, $campaignType, $payload);

        return response()->json([
            'status' => 'success',
            'message' => 'Test email job successfully queued! Check storage/logs/laravel.log or Horizon.',
        ]);
    }
}
