<?php

namespace App\Domains\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ResumeDownloadController extends Controller
{
    /**
     * Download the candidate resume securely.
     * Accessible by admins only (protected via auth/admin middleware).
     */
    public function download(Request $request, int $id)
    {
        $application = JobApplication::findOrFail($id);

        if (empty($application->resume_path)) {
            abort(404, 'No resume file uploaded for this application.');
        }

        // Verify the file exists on the private local disk
        if (!Storage::disk('local')->exists($application->resume_path)) {
            abort(404, 'The resume file could not be located in private storage.');
        }

        // Secure download with client headers
        return Storage::disk('local')->download($application->resume_path);
    }
}
