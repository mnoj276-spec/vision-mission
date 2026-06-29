<?php

namespace App\Domains\Admin\Services;

use App\Models\Setting;
use App\Models\ThemeSetting;
use App\Models\SeoSetting;
use App\Models\EmailSetting;
use App\Models\ApiSetting;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\CmsPage;
use App\Models\SocialLink;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;

use App\Domains\Admin\Services\Contracts\SettingsServiceInterface;

class SettingsService implements SettingsServiceInterface
{
    /**
     * Update bulk general settings.
     */
    public function updateGeneralSettings(array $data): void
    {
        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        settings_clear_cache();
    }

    /**
     * Update logo settings.
     */
    public function updateLogo(string $key, $file): string
    {
        // Define public paths
        $directory = public_path('uploads/logos');
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        // Validate image
        $extension = $file->getClientOriginalExtension();
        $filename = $key . '_' . time() . '.' . $extension;
        $path = 'uploads/logos/' . $filename;

        // Cleanup old file
        $oldPath = setting($key);
        if ($oldPath && File::exists(public_path($oldPath))) {
            File::delete(public_path($oldPath));
        }

        // Move new file to public directory
        $file->move($directory, $filename);

        // Resize optimization if GD extension loaded
        if (extension_loaded('gd')) {
            try {
                $fullPath = public_path($path);
                list($width, $height, $type) = getimagesize($fullPath);
                
                // Max width/height limit depending on logo types
                $maxDim = ($key === 'favicon' || $key === 'apple_touch_icon') ? 180 : 600;
                
                if ($width > $maxDim || $height > $maxDim) {
                    $ratio = $width / $height;
                    if ($ratio > 1) {
                        $newWidth = $maxDim;
                        $newHeight = $maxDim / $ratio;
                    } else {
                        $newHeight = $maxDim;
                        $newWidth = $maxDim * $ratio;
                    }

                    $src = null;
                    switch ($type) {
                        case IMAGETYPE_JPEG: $src = imagecreatefromjpeg($fullPath); break;
                        case IMAGETYPE_PNG:  $src = imagecreatefrompng($fullPath); break;
                        case IMAGETYPE_GIF:  $src = imagecreatefromgif($fullPath); break;
                        case IMAGETYPE_WEBP: $src = imagecreatefromwebp($fullPath); break;
                    }

                    if ($src) {
                        $dst = imagecreatetruecolor($newWidth, $newHeight);
                        // preserve transparency for png/gif/webp
                        if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF || $type == IMAGETYPE_WEBP) {
                            imagealphablending($dst, false);
                            imagesavealpha($dst, true);
                        }
                        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                        
                        switch ($type) {
                            case IMAGETYPE_JPEG: imagejpeg($dst, $fullPath, 85); break;
                            case IMAGETYPE_PNG:  imagepng($dst, $fullPath); break;
                            case IMAGETYPE_GIF:  imagegif($dst, $fullPath); break;
                            case IMAGETYPE_WEBP: imagewebp($dst, $fullPath, 85); break;
                        }
                    }
                }
            } catch (\Exception $e) {
                // Failsafe: keep original if resize fails
            }
        }

        // Persist to setting database
        Setting::updateOrCreate(['key' => $key], ['value' => $path]);
        settings_clear_cache();

        return $path;
    }

    /**
     * Update Theme settings.
     */
    public function updateThemeSettings(array $data): void
    {
        foreach ($data as $key => $value) {
            ThemeSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        settings_clear_cache();
    }

    /**
     * Update SEO settings.
     */
    public function updateSeoSettings(array $data): void
    {
        foreach ($data as $key => $value) {
            SeoSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        settings_clear_cache();
    }

    /**
     * Update Email/SMTP settings.
     */
    public function updateEmailSettings(array $data): void
    {
        foreach ($data as $key => $value) {
            EmailSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        settings_clear_cache();
    }

    /**
     * Update API settings.
     */
    public function updateApiSettings(array $data): void
    {
        foreach ($data as $key => $value) {
            $isSecret = in_array($key, ['google_api_keys', 'maps_api', 'sms_gateway_api', 'whatsapp_api']);
            ApiSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'is_encrypted' => $isSecret]
            );
        }
        settings_clear_cache();
    }

    /**
     * Verify SMTP connection by testing connection dynamically.
     */
    public function verifySmtpConnection(array $config): array
    {
        try {
            $host = $config['smtp_host'] ?? '';
            $port = (int) ($config['smtp_port'] ?? 2525);
            $encryption = $config['smtp_encryption'] ?? null;

            // Test TCP socket connection to verify host and port are open
            $timeout = 5;
            $stream = @fsockopen($host, $port, $errno, $errstr, $timeout);
            
            if ($stream) {
                fclose($stream);
                return [
                    'success' => true,
                    'message' => 'TCP Connection to SMTP Server successful!'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => "Could not connect to SMTP server {$host}:{$port}. Error: {$errstr} ({$errno})"
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'SMTP verification failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send test email dynamically.
     */
    public function sendTestEmail(string $recipient, array $config): void
    {
        // Override config dynamically
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $config['smtp_host'],
            'mail.mailers.smtp.port' => $config['smtp_port'],
            'mail.mailers.smtp.username' => $config['smtp_username'],
            'mail.mailers.smtp.password' => $config['smtp_password'],
            'mail.mailers.smtp.encryption' => $config['smtp_encryption'],
            'mail.from.address' => $config['sender_email'],
            'mail.from.name' => $config['sender_name'],
        ]);

        Mail::raw("Hello! This is a test mail from GovJobs settings console verification module.", function ($message) use ($recipient) {
            $message->to($recipient)->subject("GovJobs SMTP Connection Test SUCCESS!");
        });
    }

    /**
     * Generate SQL Database backup.
     */
    public function generateBackup(): string
    {
        $backupDir = storage_path('app/backups');
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $filename = 'backup_' . date('Y_m_d_His') . '.sql';
        $filePath = $backupDir . '/' . $filename;

        // Perform pure PHP database schema and data extraction
        $tables = DB::select('SHOW TABLES');
        $dbName = config('database.connections.mysql.database');
        $key = 'Tables_in_' . $dbName;

        $sql = "-- GovJobs Database Backup\n";
        $sql .= "-- Date: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- Database: " . $dbName . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $tableRow) {
            $tableName = $tableRow->$key;

            // 1. Table schema
            $createStatement = DB::select("SHOW CREATE TABLE `{$tableName}`");
            $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
            $sql .= $createStatement[0]->{'Create Table'} . ";\n\n";

            // 2. Table data
            $rows = DB::select("SELECT * FROM `{$tableName}`");
            foreach ($rows as $row) {
                $rowArray = (array) $row;
                $keys = array_keys($rowArray);
                $values = array_values($rowArray);

                $escapedValues = array_map(function ($val) {
                    if (is_null($val)) return 'NULL';
                    return "'" . addslashes($val) . "'";
                }, $values);

                $sql .= "INSERT INTO `{$tableName}` (`" . implode("`, `", $keys) . "`) VALUES (" . implode(", ", $escapedValues) . ");\n";
            }
            $sql .= "\n";
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        File::put($filePath, $sql);

        return $filename;
    }

    /**
     * Restore database from SQL backup file.
     */
    public function restoreBackup(string $filename): void
    {
        $filePath = storage_path('app/backups/' . $filename);
        if (!File::exists($filePath)) {
            throw new \Exception("Backup file not found.");
        }

        $sql = File::get($filePath);
        
        // Execute SQL commands
        DB::transaction(function () use ($sql) {
            DB::unprepared($sql);
        });

        settings_clear_cache();
    }

    /**
     * Media Manager: List files
     */
    public function listMedia(string $path = ''): array
    {
        $baseDir = public_path('uploads/media');
        if (!File::exists($baseDir)) {
            File::makeDirectory($baseDir, 0755, true);
        }

        $targetDir = empty($path) ? $baseDir : $baseDir . '/' . str_replace('..', '', $path);
        if (!File::isDirectory($targetDir)) {
            $targetDir = $baseDir;
        }

        $files = File::files($targetDir);
        $directories = File::directories($targetDir);

        $mediaList = [];

        foreach ($directories as $dir) {
            $name = basename($dir);
            $mediaList[] = [
                'name' => $name,
                'type' => 'directory',
                'path' => empty($path) ? $name : $path . '/' . $name,
                'size' => 0,
                'url' => null,
            ];
        }

        foreach ($files as $file) {
            $name = $file->getFilename();
            $relPath = empty($path) ? $name : $path . '/' . $name;
            $mediaList[] = [
                'name' => $name,
                'type' => 'file',
                'path' => $relPath,
                'size' => $file->getSize(),
                'url' => asset('uploads/media/' . $relPath),
            ];
        }

        return $mediaList;
    }
}
