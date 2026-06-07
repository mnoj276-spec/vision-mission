<?php

use App\Models\Setting;
use App\Models\ThemeSetting;
use App\Models\SeoSetting;
use App\Models\EmailSetting;
use App\Models\ApiSetting;
use Illuminate\Support\Facades\Cache;

if (!function_exists('setting')) {
    function setting(string $key, $default = null)
    {
        $settings = Cache::rememberForever('site_settings_general', function () {
            return Setting::all()->pluck('value', 'key')->toArray();
        });

        return $settings[$key] ?? $default;
    }
}

if (!function_exists('theme_setting')) {
    function theme_setting(string $key, $default = null)
    {
        $settings = Cache::rememberForever('site_settings_theme', function () {
            return ThemeSetting::all()->pluck('value', 'key')->toArray();
        });

        return $settings[$key] ?? $default;
    }
}

if (!function_exists('seo_setting')) {
    function seo_setting(string $key, $default = null)
    {
        $settings = Cache::rememberForever('site_settings_seo', function () {
            return SeoSetting::all()->pluck('value', 'key')->toArray();
        });

        return $settings[$key] ?? $default;
    }
}

if (!function_exists('email_setting')) {
    function email_setting(string $key, $default = null)
    {
        $settings = Cache::rememberForever('site_settings_email', function () {
            return EmailSetting::all()->pluck('value', 'key')->toArray();
        });

        return $settings[$key] ?? $default;
    }
}

if (!function_exists('api_setting')) {
    function api_setting(string $key, $default = null)
    {
        // Secret values decrypt on the fly inside model
        // So we retrieve the models instead of simple pluck to let attributes cast
        $settings = Cache::rememberForever('site_settings_api', function () {
            $list = [];
            foreach (ApiSetting::all() as $item) {
                $list[$item->key] = $item->value; // uses accessor getValueAttribute
            }
            return $list;
        });

        return $settings[$key] ?? $default;
    }
}

if (!function_exists('settings_clear_cache')) {
    function settings_clear_cache()
    {
        Cache::forget('site_settings_general');
        Cache::forget('site_settings_theme');
        Cache::forget('site_settings_seo');
        Cache::forget('site_settings_email');
        Cache::forget('site_settings_api');
        Cache::forget('settings_composer_data');
        Cache::forget('has_settings_table');
        Cache::forget('has_email_settings_table');
        Cache::forget('homepage_data');
        Cache::forget('sitemap_xml');
    }
}
