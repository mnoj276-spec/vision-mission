<?php

namespace App\Providers;

use App\Models\Menu;
use App\Models\SocialLink;
use App\Models\CmsPage;
use App\Models\Advertisement;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Load settings helper functions
        require_once app_path('Helpers/SettingsHelper.php');
        require_once app_path('Helpers/FeatureToggle.php');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // 1. Dynamic SMTP config override
        $hasEmailSettingsTable = false;
        try {
            $hasEmailSettingsTable = Cache::rememberForever('has_email_settings_table', function () {
                return Schema::hasTable('email_settings');
            });
        } catch (\Exception $e) {
            // DB/Cache connection not available yet or not migrated
        }

        if ($hasEmailSettingsTable) {
            try {
                $host = email_setting('smtp_host');
                if (!empty($host)) {
                    config([
                        'mail.default' => 'smtp',
                        'mail.mailers.smtp.host' => $host,
                        'mail.mailers.smtp.port' => email_setting('smtp_port', 2525),
                        'mail.mailers.smtp.username' => email_setting('smtp_username'),
                        'mail.mailers.smtp.password' => email_setting('smtp_password'),
                        'mail.mailers.smtp.encryption' => email_setting('smtp_encryption'),
                        'mail.from.address' => email_setting('sender_email', 'hello@govjobs.com'),
                        'mail.from.name' => email_setting('sender_name', 'GovJobs'),
                    ]);
                }
            } catch (\Exception $e) {
                // Failsafe
            }
        }

        // 2. View Composer to share settings, menus, social links and ads to all views
        try {
            View::composer('*', function ($view) {
                $hasSettingsTable = false;
                try {
                    $hasSettingsTable = Cache::rememberForever('has_settings_table', function () {
                        return Schema::hasTable('settings');
                    });
                } catch (\Exception $e) {
                    // DB/Cache connection not available yet or not migrated
                }

                if ($hasSettingsTable) {
                    try {
                        $composerData = Cache::rememberForever('settings_composer_data', function () {
                            // Pull menus
                            $headerMenu = Menu::where('location', 'header')
                                ->where('is_active', true)
                                ->first();
                            $headerItems = $headerMenu 
                                ? $headerMenu->rootItems()->with('children')->get() 
                                : collect();

                            $footerMenu1 = Menu::where('location', 'footer_col1')->where('is_active', true)->first();
                            $footerItems1 = $footerMenu1 ? $footerMenu1->rootItems()->get() : collect();

                            $footerMenu2 = Menu::where('location', 'footer_col2')->where('is_active', true)->first();
                            $footerItems2 = $footerMenu2 ? $footerMenu2->rootItems()->get() : collect();

                            $footerMenu3 = Menu::where('location', 'footer_col3')->where('is_active', true)->first();
                            $footerItems3 = $footerMenu3 ? $footerMenu3->rootItems()->get() : collect();

                            $footerMenu4 = Menu::where('location', 'footer_col4')->where('is_active', true)->first();
                            $footerItems4 = $footerMenu4 ? $footerMenu4->rootItems()->get() : collect();

                            // Pull social links
                            $socialLinks = SocialLink::where('is_active', true)
                                ->orderBy('order_index')
                                ->get();

                            // Pull CMS pages
                            $cmsPagesList = CmsPage::where('is_active', true)->get();

                            // Pull ads
                            $ads = Advertisement::where('is_active', true)
                                ->get()
                                ->keyBy('slot_name');

                            return [
                                'headerMenu' => $headerItems,
                                'footerMenu1' => $footerItems1,
                                'footerMenu2' => $footerItems2,
                                'footerMenu3' => $footerItems3,
                                'footerMenu4' => $footerItems4,
                                'socialLinks' => $socialLinks,
                                'cmsPagesList' => $cmsPagesList,
                                'activeAds' => $ads,
                            ];
                        });

                        $view->with($composerData);
                    } catch (\Exception $e) {
                        // Failsafe
                    }
                }
            });
        } catch (\Exception $e) {
            // Failsafe
        }
    }
}
