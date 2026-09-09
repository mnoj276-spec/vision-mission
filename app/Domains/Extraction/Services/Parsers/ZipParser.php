<?php

namespace App\Domains\Extraction\Services\Parsers;

use Illuminate\Support\Facades\Log;

class ZipParser
{
    /**
     * Extract a zip file to a temporary directory and return paths.
     *
     * @param string $filePath
     * @return array Array of extracted file paths
     */
    public function extractFiles(string $filePath): array
    {
        if (!file_exists($filePath)) {
            Log::error("ZIP file not found: {$filePath}");
            return [];
        }

        $tempDir = storage_path('app/temp/zip_' . uniqid());
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777, true);
        }

        $zip = new \ZipArchive();
        if ($zip->open($filePath) === true) {
            $maxUncompressedSize = 50 * 1024 * 1024; // 50MB limit
            $totalSize = 0;
            
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                if ($stat !== false) {
                    if (isset($stat['size'])) {
                        $totalSize += $stat['size'];
                    }
                    
                    // Zip Slip Prevention: Check for directory traversal or absolute paths
                    $filename = $stat['name'];
                    if (str_contains($filename, '..') || str_starts_with($filename, '/') || str_starts_with($filename, '\\')) {
                        $zip->close();
                        throw new \Exception("Zip Slip vulnerability detected. Invalid file path: {$filename}");
                    }
                }
                
                if ($totalSize > $maxUncompressedSize) {
                    $zip->close();
                    throw new \Exception("ZIP Bomb detected: Uncompressed size exceeds 50MB limit.");
                }
            }

            $zip->extractTo($tempDir);
            $zip->close();
            
            return $this->getFilesRecursively($tempDir);
        }
        
        Log::error("Failed to open ZIP file: {$filePath}");
        return [];
    }

    protected function getFilesRecursively(string $dir): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                continue;
            }
            $files[] = $file->getPathname();
        }
        return $files;
    }
}
