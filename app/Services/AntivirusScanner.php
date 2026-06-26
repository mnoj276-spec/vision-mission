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

        $contents = @file_get_contents($realPath);
        if ($contents === false) {
            return false;
        }

        // 1. Check for EICAR standard antivirus test signature
        $eicarSignature = 'X5O!P%@AP[4\PZX54(P^)7CC)7}$EICAR-STANDARD-ANTIVIRUS-TEST-FILE!$H+H*';
        if (str_contains($contents, $eicarSignature)) {
            Log::warning("VIRUS DETECTED: EICAR malware test string detected in file: " . $file->getClientOriginalName());
            return false;
        }

        // 2. Validate PDF Magic Bytes (prevent extension spoofing MZ/EXE as PDF)
        $extension = strtolower($file->getClientOriginalExtension());
        if ($extension === 'pdf') {
            if (strpos($contents, '%PDF') !== 0) {
                Log::warning("SECURITY ALERT: PDF magic bytes mismatch (spoofing check failed) for file: " . $file->getClientOriginalName());
                return false;
            }
        }

        // 3. Prevent PHP/script injection attacks in document uploads
        $suspiciousPatterns = [
            '<?php',
            '<?=',
            '#!/bin/bash',
            '#!/bin/sh',
            '<script',
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (str_contains($contents, $pattern)) {
                Log::warning("SECURITY ALERT: Suspicious scripting pattern '{$pattern}' detected in file: " . $file->getClientOriginalName());
                return false;
            }
        }

        // 4. ClamAV TCP Daemon Scan (if host is configured)
        $clamavHost = config('services.clamav.host');
        $clamavPort = config('services.clamav.port', 3310);

        if (!empty($clamavHost)) {
            try {
                $socket = @fsockopen($clamavHost, $clamavPort, $errno, $errstr, 4);
                if ($socket) {
                    // Send SCAN command to ClamD daemon
                    fwrite($socket, "SCAN " . $realPath . "\n");
                    $response = fgets($socket, 1024);
                    fclose($socket);

                    if (str_contains($response, 'FOUND')) {
                        Log::warning("VIRUS DETECTED by ClamAV: " . trim($response) . " in file: " . $file->getClientOriginalName());
                        return false;
                    }
                }
            } catch (\Exception $e) {
                Log::error("ClamAV daemon TCP scan connection failed: " . $e->getMessage());
            }
        }

        return true;
    }
}
