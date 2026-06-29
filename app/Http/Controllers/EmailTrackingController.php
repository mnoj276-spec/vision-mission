<?php

namespace App\Http\Controllers;

use App\Models\EmailLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EmailTrackingController extends Controller
{
    /**
     * Track an email open event via an embedded 1x1 transparent tracking pixel.
     */
    public function trackOpen(string $token): Response
    {
        $log = EmailLog::where('tracking_token', $token)->first();

        if ($log && !$log->opened_at) {
            $log->update([
                'opened_at' => now(),
            ]);
        }

        // Return a 1x1 transparent GIF image
        $pixel = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');

        return response($pixel)
            ->header('Content-Type', 'image/gif')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    /**
     * Track an email link click event and redirect the candidate to their destination.
     */
    public function trackClick(string $token, Request $request): RedirectResponse
    {
        $log = EmailLog::where('tracking_token', $token)->first();

        if ($log && !$log->clicked_at) {
            $log->update([
                'clicked_at' => now(),
            ]);
        }

        $destinationUrl = $request->get('url', url('/'));

        // Validate redirect target to prevent Open Redirect
        $host = parse_url($destinationUrl, PHP_URL_HOST);
        $localHost = parse_url(config('app.url'), PHP_URL_HOST);
        
        $isLocal = empty($host) || strcasecmp($host, $localHost) === 0;
        $isApproved = \App\Services\UrlSecurity::isSafeUrl($destinationUrl);

        if (!$isLocal && !$isApproved) {
            $destinationUrl = url('/');
        }

        // Perform clean redirection
        return redirect()->to($destinationUrl);
    }
}
