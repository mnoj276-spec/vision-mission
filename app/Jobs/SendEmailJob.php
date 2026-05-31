<?php

namespace App\Jobs;

use App\Mail\AdmitCardAlertMail;
use App\Mail\JobAlertMail;
use App\Mail\ReEngagementMail;
use App\Mail\ResultAlertMail;
use App\Mail\WeeklyDigestMail;
use App\Mail\WelcomeSeriesMail;
use App\Models\EmailLog;
use App\Models\JobPost;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $recipientEmail,
        public string $campaignType,
        public array $data = []
    ) {
        $this->onQueue('notifications');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $token = Str::random(32);
        
        // Resolve recipient name and user reference
        $userId = $this->data['user_id'] ?? null;
        $recipientName = $this->data['name'] ?? 'Candidate';

        // 1. Create or retrieve the log in 'queued' status
        $log = EmailLog::create([
            'user_id' => $userId,
            'subscriber_email' => $userId ? null : $this->recipientEmail,
            'campaign_type' => $this->campaignType,
            'subject' => $this->getSubject(),
            'status' => 'queued',
            'tracking_token' => $token,
        ]);

        try {
            $mailable = $this->resolveMailable($recipientName, $token);
            
            // 2. Dispatch email
            Mail::to($this->recipientEmail)->send($mailable);

            // 3. Mark sent
            $log->update([
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            Log::info("Email automation dispatched successfully: {$this->campaignType} to {$this->recipientEmail}");
        } catch (Throwable $e) {
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage() . "\n" . $e->getTraceAsString(),
            ]);
            Log::error("Failed to dispatch email automation: {$this->campaignType} to {$this->recipientEmail}. Reason: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        // Failures are already logged in catch block, but fallback safe updates
        Log::error("SendEmailJob completely failed after all retries: {$this->campaignType} to {$this->recipientEmail}");
    }

    /**
     * Determine subject line based on campaign type.
     */
    protected function getSubject(): string
    {
        return match ($this->campaignType) {
            'welcome_1' => 'Welcome to Sarkari Vision Mission! Your Career Launchpad 🚀',
            'welcome_2' => 'Custom Alert Preferences: Setup Guide ⚡',
            'welcome_3' => 'GovJobs Active Alert Dashboard: Top Recruitments 🎯',
            'job_alert' => 'New Govt Job Openings Matched For You! 🔥',
            'result_alert' => 'Official Exam Results Declared! Check Status 📢',
            'admit_card_alert' => 'Admit Cards / Hall Tickets Released! Download Now 🎫',
            'weekly_digest' => 'Sarkari Vision Mission: Weekly Government Careers Digest 📅',
            're_engagement' => 'We Miss You! Check These New Government Openings 💼',
            default => 'Sarkari Vision Mission Career Alert',
        };
    }

    /**
     * Construct the proper Mailable class depending on campaign type.
     */
    protected function resolveMailable(string $name, string $token): mixed
    {
        $subject = $this->getSubject();

        switch ($this->campaignType) {
            case 'welcome_1':
                return new WelcomeSeriesMail($name, 1, $subject, $token);
            case 'welcome_2':
                return new WelcomeSeriesMail($name, 2, $subject, $token);
            case 'welcome_3':
                return new WelcomeSeriesMail($name, 3, $subject, $token);

            case 'job_alert':
                $jobIds = $this->data['job_ids'] ?? [];
                $jobs = JobPost::with(['department', 'state'])->whereIn('id', $jobIds)->get();
                return new JobAlertMail($name, $jobs, $subject, $token);

            case 'result_alert':
                $jobIds = $this->data['job_ids'] ?? [];
                $jobs = JobPost::with(['department', 'state'])->whereIn('id', $jobIds)->get();
                return new ResultAlertMail($name, $jobs, $subject, $token);

            case 'admit_card_alert':
                $jobIds = $this->data['job_ids'] ?? [];
                $jobs = JobPost::with(['department', 'state'])->whereIn('id', $jobIds)->get();
                return new AdmitCardAlertMail($name, $jobs, $subject, $token);

            case 'weekly_digest':
                $recentJobIds = $this->data['recent_job_ids'] ?? [];
                $admitCardIds = $this->data['admit_card_ids'] ?? [];
                $resultIds = $this->data['result_ids'] ?? [];

                $recentJobs = JobPost::with(['department', 'state'])->whereIn('id', $recentJobIds)->get();
                $admitCards = JobPost::with(['department', 'state'])->whereIn('id', $admitCardIds)->get();
                $results = JobPost::with(['department', 'state'])->whereIn('id', $resultIds)->get();

                return new WeeklyDigestMail($name, $recentJobs, $admitCards, $results, $subject, $token);

            case 're_engagement':
                $jobIds = $this->data['job_ids'] ?? [];
                $jobs = JobPost::with(['department', 'state'])->whereIn('id', $jobIds)->get();
                return new ReEngagementMail($name, $jobs, $subject, $token);

            default:
                throw new \InvalidArgumentException("Mailable not defined for campaign: {$this->campaignType}");
        }
    }
}
