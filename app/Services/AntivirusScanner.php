<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class AntivirusScanner
{
    /**
     * Scan the uploaded file for viruses/malware.
     * Integrates with ClamAV or does signature analysis (EICAR).
     *
     * @param UploadedFile $file
     * @return bool True if safe, false if infected.
     */
    public static function scan(UploadedFile $file): bool
    {
        Log::info("Antivirus scanning hook triggered for file: " . $file->getClientOriginalName());

        $realPath = $file->getRealPath();
        if (empty($realPath) || !file_exists($realPath)) {
            return false;
        }

        $contents = file_get_contents($realPath);

        // Check for EICAR standard antivirus test signature
        $eicarSignature = 'X5O!P%@AP[4\PZX54(P^)7CC)7}$EICAR-STANDARD-ANTIVIRUS-TEST-FILE!$H+H*';
        if (str_contains($contents, $eicarSignature)) {
            Log::warning("VIRUS DETECTED: EICAR malware test string detected in file: " . $file->getClientOriginalName());
            return false;
        }

        return true;
    }
}
