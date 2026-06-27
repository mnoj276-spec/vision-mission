<?php

namespace App\Domains\Admin\Controllers;

use App\Domains\Admin\Services\SettingsService;
use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\CmsPage;
use App\Models\SocialLink;
use App\Models\Setting;
use App\Models\ThemeSetting;
use App\Models\SeoSetting;
use App\Models\EmailSetting;
use App\Models\ApiSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SettingsManagementController extends Controller
{
    public function __construct(
        protected SettingsService $settingsService,
        protected \App\Domains\Admin\Services\Contracts\AdminServiceInterface $adminService
    ) {}

    /**
     * Get all structured settings.
     */
    public function getSettings(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'general' => Setting::all()->pluck('value', 'key'),
                'theme' => ThemeSetting::all()->pluck('value', 'key'),
                'seo' => SeoSetting::all()->pluck('value', 'key'),
                'email' => EmailSetting::all()->pluck('value', 'key'),
                // Only return redacted values for secret API settings
                'api' => collect(ApiSetting::all())->mapWithKeys(function ($item) {
                    $val = $item->value; // decrypts automatically
                    if ($item->is_encrypted && !empty($val)) {
                        $val = substr($val, 0, 4) . str_repeat('*', 12);
                    }
                    return [$item->key => $val];
                }),
                'social' => SocialLink::orderBy('order_index')->get(),
            ]
        ]);
    }

    /**
     * Update General settings.
     */
    public function updateGeneralSettings(Request $request): JsonResponse
    {
        $data = $request->validate([
            'website_name' => 'required|string|max:100',
            'website_title' => 'required|string|max:200',
            'website_tagline' => 'nullable|string|max:200',
            'website_description' => 'nullable|string',
            'website_keywords' => 'nullable|string',
            'website_author' => 'nullable|string|max:100',
            'website_contact_email' => 'required|email|max:100',
            'website_contact_mobile' => 'nullable|string|max:30',
            'support_email' => 'nullable|email|max:100',
            'support_phone' => 'nullable|string|max:30',
            'office_address' => 'nullable|string',
            'copyright_text' => 'required|string|max:255',
            'timezone' => 'required|string|max:100',
            'date_format' => 'required|string|max:50',
            'currency' => 'required|string|max:10',
            'language' => 'required|string|max:10',
            'maintenance_mode' => 'required|in:0,1',
            'maintenance_message' => 'nullable|string',
            
            // Notification configurations
            'email_notifications' => 'required|in:0,1',
            'push_notifications' => 'required|in:0,1',
            'admin_notifications' => 'required|in:0,1',
            'user_notifications' => 'required|in:0,1',

            // Custom Code Inject
            'header_scripts' => 'nullable|string',
            'footer_scripts' => 'nullable|string',
        ]);

        $this->settingsService->updateGeneralSettings($data);

        $this->adminService->logAction(
            Auth::id(),
            $request->ip(),
            $request->userAgent() ?? 'N/A',
            'Update General Settings',
            'Modified core system configurations'
        );

        return response()->json([
            'status' => 'success',
            'message' => 'General Settings updated successfully!'
        ]);
    }

    /**
     * Upload dynamic logos.
     */
    public function uploadLogo(Request $request): JsonResponse
    {
        $request->validate([
            'key' => 'required|string|in:main_logo,header_logo,footer_logo,mobile_logo,favicon,apple_touch_icon',
            'file' => 'required|image|mimes:jpeg,png,gif,ico,svg,webp|max:2048',
        ]);

        $path = $this->settingsService->updateLogo($request->key, $request->file('file'));

        $this->adminService->logAction(
            Auth::id(),
            $request->ip(),
            $request->userAgent() ?? 'N/A',
            'Upload Asset Logo',
            "Updated logo for slot: {$request->key}"
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Image uploaded and configured successfully!',
            'data' => ['path' => asset($path)]
        ]);
    }

    /**
     * Update Theme settings.
     */
    public function updateThemeSettings(Request $request): JsonResponse
    {
        $data = $request->validate([
            'primary_color' => 'required|string|max:20',
            'secondary_color' => 'required|string|max:20',
            'accent_color' => 'required|string|max:20',
            'background_color' => 'required|string|max:20',
            'text_color' => 'required|string|max:20',
            'dark_primary_color' => 'required|string|max:20',
            'dark_background_color' => 'required|string|max:20',
        ]);

        $this->settingsService->updateThemeSettings($data);

        $this->adminService->logAction(
            Auth::id(),
            $request->ip(),
            $request->userAgent() ?? 'N/A',
            'Update Theme Settings',
            'Modified system color scheme properties'
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Theme colors updated successfully!'
        ]);
    }

    /**
     * Update SEO settings.
     */
    public function updateSeoSettings(Request $request): JsonResponse
    {
        $data = $request->validate([
            'meta_title' => 'required|string|max:200',
            'meta_description' => 'required|string',
            'meta_keywords' => 'required|string',
            'og_title' => 'required|string|max:200',
            'og_description' => 'required|string',
            'og_image' => 'nullable|string',
            'twitter_title' => 'required|string|max:200',
            'twitter_description' => 'required|string',
            'twitter_image' => 'nullable|string',
            'robots_txt' => 'nullable|string',
        ]);

        $this->settingsService->updateSeoSettings($data);

        // Update robots.txt in public path
        if ($request->has('robots_txt')) {
            try {
                File::put(public_path('robots.txt'), $request->robots_txt);
            } catch (\Exception $e) {}
        }

        $this->adminService->logAction(
            Auth::id(),
            $request->ip(),
            $request->userAgent() ?? 'N/A',
            'Update SEO Configurations',
            'Synchronized search meta templates and social tags'
        );

        return response()->json([
            'status' => 'success',
            'message' => 'SEO Meta Configurations updated successfully!'
        ]);
    }

    /**
     * Update Email settings.
     */
    public function updateEmailSettings(Request $request): JsonResponse
    {
        $data = $request->validate([
            'smtp_host' => 'required|string',
            'smtp_port' => 'required|integer',
            'smtp_username' => 'nullable|string',
            'smtp_password' => 'nullable|string',
            'smtp_encryption' => 'nullable|string',
            'sender_name' => 'required|string',
            'sender_email' => 'required|email',
        ]);

        $this->settingsService->updateEmailSettings($data);

        $this->adminService->logAction(
            Auth::id(),
            $request->ip(),
            $request->userAgent() ?? 'N/A',
            'Update SMTP Configs',
            'Modified mail dispatch variables'
        );

        return response()->json([
            'status' => 'success',
            'message' => 'SMTP Configurations updated successfully!'
        ]);
    }

    /**
     * Test SMTP Connection.
     */
    public function testSmtpConnection(Request $request): JsonResponse
    {
        $request->validate([
            'smtp_host' => 'required|string',
            'smtp_port' => 'required|integer',
            'smtp_username' => 'nullable|string',
            'smtp_password' => 'nullable|string',
            'smtp_encryption' => 'nullable|string',
            'sender_name' => 'required|string',
            'sender_email' => 'required|email',
            'test_recipient' => 'required|email',
        ]);

        $config = $request->only([
            'smtp_host', 'smtp_port', 'smtp_username', 'smtp_password',
            'smtp_encryption', 'sender_name', 'sender_email'
        ]);

        // Verify connection
        $testResult = $this->settingsService->verifySmtpConnection($config);
        if (!$testResult['success']) {
            return response()->json([
                'status' => 'error',
                'message' => $testResult['message']
            ], 422);
        }

        // Try dispatching test mail
        try {
            $this->settingsService->sendTestEmail($request->test_recipient, $config);
            return response()->json([
                'status' => 'success',
                'message' => 'SMTP verification & test email dispatch successful!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'SMTP test connection succeeded, but mail send failed. Reason: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update API settings.
     */
    public function updateApiSettings(Request $request): JsonResponse
    {
        $data = $request->validate([
            'google_api_keys' => 'nullable|string',
            'maps_api' => 'nullable|string',
            'sms_gateway_api' => 'nullable|string',
            'whatsapp_api' => 'nullable|string',
        ]);

        // Filter out redacted/masked keys before saving
        $saveData = [];
        foreach ($data as $key => $value) {
            if ($value && str_contains($value, '*********')) {
                // Keep original encrypted value
                continue;
            }
            $saveData[$key] = $value;
        }

        $this->settingsService->updateApiSettings($saveData);

        $this->adminService->logAction(
            Auth::id(),
            $request->ip(),
            $request->userAgent() ?? 'N/A',
            'Update API Settings',
            'Modified Google Maps / SMS API credentials'
        );

        return response()->json([
            'status' => 'success',
            'message' => 'API Credentials updated successfully!'
        ]);
    }

    /**
     * Update Social Link list.
     */
    public function updateSocialLinks(Request $request): JsonResponse
    {
        $request->validate([
            'links' => 'required|array',
            'links.*.platform' => 'required|string',
            'links.*.url' => 'nullable|string',
            'links.*.is_active' => 'required|boolean',
        ]);

        foreach ($request->links as $index => $linkData) {
            SocialLink::updateOrCreate(
                ['platform' => $linkData['platform']],
                [
                    'url' => $linkData['url'],
                    'is_active' => $linkData['is_active'],
                    'order_index' => $index,
                ]
            );
        }

        settings_clear_cache();

        return response()->json([
            'status' => 'success',
            'message' => 'Social Links list synchronized successfully!'
        ]);
    }

    /**
     * Get Menus and Menu Items.
     */
    public function getMenus(): JsonResponse
    {
        $menus = Menu::with('items')->get();
        return response()->json([
            'status' => 'success',
            'data' => $menus
        ]);
    }

    /**
     * Save Menu Item.
     */
    public function saveMenuItem(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'nullable|integer|exists:menu_items,id',
            'menu_id' => 'required|integer|exists:menus,id',
            'parent_id' => 'nullable|integer|exists:menu_items,id',
            'title' => 'required|string|max:100',
            'url' => 'required|string',
            'icon' => 'nullable|string|max:50',
            'target' => 'required|in:_self,_blank',
            'is_active' => 'required|boolean',
        ]);

        $itemData = $request->only(['menu_id', 'parent_id', 'title', 'url', 'icon', 'target', 'is_active']);

        if (!$request->id) {
            // Get order index
            $itemData['order_index'] = MenuItem::where('menu_id', $request->menu_id)
                ->where('parent_id', $request->parent_id)
                ->count();
        }

        $menuItem = MenuItem::updateOrCreate(
            ['id' => $request->id],
            $itemData
        );

        settings_clear_cache();

        $action = $request->id ? 'Edit Menu Item' : 'Add Menu Item';
        $this->adminService->logAction(
            Auth::id(),
            $request->ip(),
            $request->userAgent() ?? 'N/A',
            $action,
            "Modified menu list item: {$menuItem->title}"
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Menu Item saved successfully!',
            'data' => $menuItem
        ]);
    }

    /**
     * Reorder Menu Items.
     */
    public function reorderMenuItems(Request $request): JsonResponse
    {
        $request->validate([
            'menu_id' => 'required|integer|exists:menus,id',
            'items' => 'required|array',
            'items.*.id' => 'required|integer|exists:menu_items,id',
            'items.*.parent_id' => 'nullable|integer',
        ]);

        foreach ($request->items as $index => $itemData) {
            MenuItem::where('id', $itemData['id'])->update([
                'parent_id' => $itemData['parent_id'],
                'order_index' => $index,
            ]);
        }

        settings_clear_cache();

        return response()->json([
            'status' => 'success',
            'message' => 'Menu navigation sequence updated successfully!'
        ]);
    }

    /**
     * Delete Menu Item.
     */
    public function deleteMenuItem(Request $request, int $id): JsonResponse
    {
        $item = MenuItem::findOrFail($id);
        $title = $item->title;
        $item->delete();

        settings_clear_cache();

        $this->adminService->logAction(
            Auth::id(),
            $request->ip(),
            $request->userAgent() ?? 'N/A',
            'Delete Menu Item',
            "Removed menu list item: {$title}"
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Menu Item deleted successfully!'
        ]);
    }

    /**
     * Get CMS pages.
     */
    public function getCmsPages(): JsonResponse
    {
        $pages = CmsPage::orderBy('id', 'desc')->get();
        return response()->json([
            'status' => 'success',
            'data' => $pages
        ]);
    }

    /**
     * Get single CMS page detail.
     */
    public function getCmsPageDetail(int $id): JsonResponse
    {
        $page = CmsPage::findOrFail($id);
        return response()->json([
            'status' => 'success',
            'data' => $page
        ]);
    }

    /**
     * Create / Update CMS page.
     */
    public function saveCmsPage(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'nullable|integer|exists:cms_pages,id',
            'title' => 'required|string|max:150',
            'content' => 'required|string',
            'meta_title' => 'nullable|string|max:200',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'is_active' => 'required|boolean',
        ]);

        $pageData = $request->only(['title', 'content', 'meta_title', 'meta_description', 'meta_keywords', 'is_active']);

        if (!$request->id) {
            $pageData['slug'] = Str::slug($request->title);
            // Ensure slug is unique
            $slugCount = CmsPage::where('slug', 'like', $pageData['slug'] . '%')->count();
            if ($slugCount > 0) {
                $pageData['slug'] .= '-' . ($slugCount + 1);
            }
        }

        $page = CmsPage::updateOrCreate(
            ['id' => $request->id],
            $pageData
        );

        settings_clear_cache();

        $action = $request->id ? 'Edit CMS Page' : 'Create CMS Page';
        $this->adminService->logAction(
            Auth::id(),
            $request->ip(),
            $request->userAgent() ?? 'N/A',
            $action,
            "Saved page details for slug: {$page->slug}"
        );

        return response()->json([
            'status' => 'success',
            'message' => 'CMS Page details saved successfully!',
            'data' => $page
        ]);
    }

    /**
     * Delete CMS Page.
     */
    public function deleteCmsPage(Request $request, int $id): JsonResponse
    {
        $page = CmsPage::findOrFail($id);
        $slug = $page->slug;
        $page->delete();

        settings_clear_cache();

        $this->adminService->logAction(
            Auth::id(),
            $request->ip(),
            $request->userAgent() ?? 'N/A',
            'Delete CMS Page',
            "Removed custom pages template: {$slug}"
        );

        return response()->json([
            'status' => 'success',
            'message' => 'CMS Page deleted successfully!'
        ]);
    }

    /**
     * Get Media files.
     */
    public function getMedia(Request $request): JsonResponse
    {
        $path = $request->string('path', '');
        $mediaList = $this->settingsService->listMedia($path);
        
        return response()->json([
            'status' => 'success',
            'data' => $mediaList
        ]);
    }

    /**
     * Upload File in Media Manager.
     */
    public function uploadMedia(Request $request): JsonResponse
    {
        $request->validate([
            'path' => 'nullable|string',
            'file' => 'required|file|max:5120', // Max 5MB
        ]);

        $path = $request->string('path', '');
        $baseDir = public_path('uploads/media');
        $targetDir = empty($path) ? $baseDir : $baseDir . '/' . str_replace('..', '', $path);

        if (!File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        $file = $request->file('file');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->move($targetDir, $filename);

        return response()->json([
            'status' => 'success',
            'message' => 'File uploaded to media manager successfully!'
        ]);
    }

    /**
     * Create Folder in Media Manager.
     */
    public function createFolder(Request $request): JsonResponse
    {
        $request->validate([
            'path' => 'nullable|string',
            'folder_name' => 'required|string|max:50',
        ]);

        $path = $request->string('path', '');
        $baseDir = public_path('uploads/media');
        $targetDir = empty($path) ? $baseDir : $baseDir . '/' . str_replace('..', '', $path);
        
        $newDir = $targetDir . '/' . Str::slug($request->folder_name);

        if (File::exists($newDir)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Folder already exists!'
            ], 422);
        }

        File::makeDirectory($newDir, 0755, true);

        return response()->json([
            'status' => 'success',
            'message' => 'Folder created successfully!'
        ]);
    }

    /**
     * Delete Media File/Folder.
     */
    public function deleteMedia(Request $request): JsonResponse
    {
        $request->validate([
            'path' => 'required|string',
        ]);

        $filePath = public_path('uploads/media/' . str_replace('..', '', $request->path));

        if (!File::exists($filePath)) {
            return response()->json([
                'status' => 'error',
                'message' => 'File or Directory does not exist.'
            ], 404);
        }

        if (File::isDirectory($filePath)) {
            File::deleteDirectory($filePath);
        } else {
            File::delete($filePath);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Deleted successfully!'
        ]);
    }

    /**
     * Get Database Backups list.
     */
    public function getBackups(): JsonResponse
    {
        $backupDir = storage_path('app/backups');
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $files = File::files($backupDir);
        $backups = [];

        foreach ($files as $file) {
            $name = $file->getFilename();
            if (str_ends_with($name, '.sql')) {
                $backups[] = [
                    'filename' => $name,
                    'size' => $file->getSize(),
                    'created_at' => date('d M Y H:i:s', $file->getMTime()),
                ];
            }
        }

        // Sort backups by time desc
        usort($backups, function ($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return response()->json([
            'status' => 'success',
            'data' => $backups
        ]);
    }

    /**
     * Trigger Database Backup creation.
     */
    public function generateBackup(Request $request): JsonResponse
    {
        try {
            $filename = $this->settingsService->generateBackup();

            $this->adminService->logAction(
                Auth::id(),
                $request->ip(),
                $request->userAgent() ?? 'N/A',
                'Generate Database Backup',
                "Created backup file: {$filename}"
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Database SQL backup generated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Backup generation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore Database from backup.
     */
    public function restoreBackup(Request $request): JsonResponse
    {
        $request->validate([
            'filename' => 'required|string',
        ]);

        try {
            $this->settingsService->restoreBackup($request->filename);

            $this->adminService->logAction(
                Auth::id(),
                $request->ip(),
                $request->userAgent() ?? 'N/A',
                'Restore Database Backup',
                "Restored backup state from file: {$request->filename}"
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Database state restored successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Restore failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download Backup file.
     */
    public function downloadBackup(string $filename)
    {
        $baseDir = storage_path('app/backups');
        $filename = basename($filename);
        $filePath = $baseDir . '/' . $filename;

        if (!\Illuminate\Support\Facades\File::exists($filePath)) {
            abort(404);
        }

        return response()->download($filePath);
    }

    /**
     * Delete Backup file.
     */
    public function deleteBackup(Request $request, string $filename): JsonResponse
    {
        $baseDir = storage_path('app/backups');
        $filename = basename($filename);
        $filePath = $baseDir . '/' . $filename;

        if (!\Illuminate\Support\Facades\File::exists($filePath)) {
            abort(404);
        }

        \Illuminate\Support\Facades\File::delete($filePath);

        $this->adminService->logAction(
            Auth::id(),
            $request->ip(),
            $request->userAgent() ?? 'N/A',
            'Delete Database Backup File',
            "Deleted backup SQL file: {$filename}"
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Backup file deleted successfully!'
        ]);
    }
}
