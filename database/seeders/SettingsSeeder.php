<?php

namespace Database\Seeders;

use App\Models\SettingGroup;
use App\Models\Setting;
use App\Models\ThemeSetting;
use App\Models\SeoSetting;
use App\Models\EmailSetting;
use App\Models\ApiSetting;
use App\Models\SocialLink;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\CmsPage;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Setting Groups
        $groupGeneral = SettingGroup::firstOrCreate(['name' => 'general'], ['display_name' => 'General Settings', 'description' => 'Core website configuration options.']);
        $groupLogos = SettingGroup::firstOrCreate(['name' => 'logos'], ['display_name' => 'Logo Settings', 'description' => 'Website logos and identity assets.']);
        $groupNotifications = SettingGroup::firstOrCreate(['name' => 'notifications'], ['display_name' => 'Notification Toggles', 'description' => 'Enable or disable system alert dispatches.']);
        $groupCustomCode = SettingGroup::firstOrCreate(['name' => 'custom_code'], ['display_name' => 'Custom Scripts', 'description' => 'Injected scripts for header and footer.']);

        // 2. Seed Settings
        $settings = [
            [
                'group_id' => $groupGeneral->id,
                'key' => 'website_name',
                'value' => 'GovJobs',
                'type' => 'text',
                'display_name' => 'Website Name',
                'description' => 'The brand name of the website.'
            ],
            [
                'group_id' => $groupGeneral->id,
                'key' => 'website_title',
                'value' => 'Government Jobs Portal',
                'type' => 'text',
                'display_name' => 'Website Title',
                'description' => 'Title shown in browser titlebar.'
            ],
            [
                'group_id' => $groupGeneral->id,
                'key' => 'website_tagline',
                'value' => 'Find Your Dream Government Job',
                'type' => 'text',
                'display_name' => 'Website Tagline',
                'description' => 'Tagline or brand slogan.'
            ],
            [
                'group_id' => $groupGeneral->id,
                'key' => 'website_description',
                'value' => 'The primary destination for searching active recruitment notifications, exam schedules, and department results in India.',
                'type' => 'textarea',
                'display_name' => 'Website Description',
                'description' => 'Short meta overview description of the portal.'
            ],
            [
                'group_id' => $groupGeneral->id,
                'key' => 'website_keywords',
                'value' => 'government jobs, upsc, ssc, banking, rrb, sbi, defense, exam results',
                'type' => 'text',
                'display_name' => 'Website Keywords',
                'description' => 'Comma separated meta keywords.'
            ],
            [
                'group_id' => $groupGeneral->id,
                'key' => 'website_author',
                'value' => 'GovJobs Team',
                'type' => 'text',
                'display_name' => 'Website Author',
                'description' => 'Author metadata tag.'
            ],
            [
                'group_id' => $groupGeneral->id,
                'key' => 'website_contact_email',
                'value' => 'info@govjobs.com',
                'type' => 'text',
                'display_name' => 'Contact Email Address',
                'description' => 'Main public email address.'
            ],
            [
                'group_id' => $groupGeneral->id,
                'key' => 'website_contact_mobile',
                'value' => '+91 98765 43210',
                'type' => 'text',
                'display_name' => 'Contact Mobile Number',
                'description' => 'Main public mobile contact.'
            ],
            [
                'group_id' => $groupGeneral->id,
                'key' => 'support_email',
                'value' => 'support@govjobs.com',
                'type' => 'text',
                'display_name' => 'Support Email Address',
                'description' => 'Helpdesk support email destination.'
            ],
            [
                'group_id' => $groupGeneral->id,
                'key' => 'support_phone',
                'value' => '+91 98765 43210',
                'type' => 'text',
                'display_name' => 'Support Phone Number',
                'description' => 'Helpdesk support contact number.'
            ],
            [
                'group_id' => $groupGeneral->id,
                'key' => 'office_address',
                'value' => '123, Central Plaza, Connaught Place, New Delhi, 110001',
                'type' => 'textarea',
                'display_name' => 'Office Address',
                'description' => 'Headquarters physical address details.'
            ],
            [
                'group_id' => $groupGeneral->id,
                'key' => 'copyright_text',
                'value' => '© 2026 GovJobs. All rights reserved.',
                'type' => 'text',
                'display_name' => 'Copyright Text',
                'description' => 'Copyright notice shown in website footer.'
            ],
            [
                'group_id' => $groupGeneral->id,
                'key' => 'timezone',
                'value' => 'Asia/Kolkata',
                'type' => 'text',
                'display_name' => 'System Timezone',
                'description' => 'Default system timezone.'
            ],
            [
                'group_id' => $groupGeneral->id,
                'key' => 'date_format',
                'value' => 'Y-m-d',
                'type' => 'text',
                'display_name' => 'System Date Format',
                'description' => 'Default formatting representation for dates.'
            ],
            [
                'group_id' => $groupGeneral->id,
                'key' => 'currency',
                'value' => 'INR',
                'type' => 'text',
                'display_name' => 'System Currency',
                'description' => 'Global default currency signifier.'
            ],
            [
                'group_id' => $groupGeneral->id,
                'key' => 'language',
                'value' => 'en',
                'type' => 'text',
                'display_name' => 'System Language',
                'description' => 'Default locale string.'
            ],
            [
                'group_id' => $groupGeneral->id,
                'key' => 'maintenance_mode',
                'value' => '0',
                'type' => 'boolean',
                'display_name' => 'Maintenance Mode Enabled',
                'description' => 'Block public traffic to show maintenance error page.'
            ],
            [
                'group_id' => $groupGeneral->id,
                'key' => 'maintenance_message',
                'value' => 'Our website is undergoing scheduled maintenance. Please try again soon.',
                'type' => 'textarea',
                'display_name' => 'Maintenance Message',
                'description' => 'Message to display on maintenance lock screen.'
            ],

            // Notification configurations
            [
                'group_id' => $groupNotifications->id,
                'key' => 'email_notifications',
                'value' => '1',
                'type' => 'boolean',
                'display_name' => 'Email Alerts Active',
                'description' => 'Allows mailing out system newsletters/alerts.'
            ],
            [
                'group_id' => $groupNotifications->id,
                'key' => 'push_notifications',
                'value' => '0',
                'type' => 'boolean',
                'display_name' => 'Push Notifications Active',
                'description' => 'Allows sending out browser notification triggers.'
            ],
            [
                'group_id' => $groupNotifications->id,
                'key' => 'admin_notifications',
                'value' => '1',
                'type' => 'boolean',
                'display_name' => 'Admin Action Logs Active',
                'description' => 'Log administrator panel operations.'
            ],
            [
                'group_id' => $groupNotifications->id,
                'key' => 'user_notifications',
                'value' => '1',
                'type' => 'boolean',
                'display_name' => 'User Interactions Alerts Active',
                'description' => 'Email notification alert for user activities.'
            ],

            // Custom Code Inject
            [
                'group_id' => $groupCustomCode->id,
                'key' => 'header_scripts',
                'value' => '',
                'type' => 'textarea',
                'display_name' => 'Header Script Injection',
                'description' => 'Append raw code inside the <head> tags.'
            ],
            [
                'group_id' => $groupCustomCode->id,
                'key' => 'footer_scripts',
                'value' => '',
                'type' => 'textarea',
                'display_name' => 'Footer Script Injection',
                'description' => 'Append raw code right before the closing </body> tag.'
            ],

            // Logos Settings
            [
                'group_id' => $groupLogos->id,
                'key' => 'main_logo',
                'value' => '',
                'type' => 'file',
                'display_name' => 'Main Branding Logo',
                'description' => 'The site primary logo image path.'
            ],
            [
                'group_id' => $groupLogos->id,
                'key' => 'header_logo',
                'value' => '',
                'type' => 'file',
                'display_name' => 'Header Top Navigation Logo',
                'description' => 'Branding logo path within site header.'
            ],
            [
                'group_id' => $groupLogos->id,
                'key' => 'footer_logo',
                'value' => '',
                'type' => 'file',
                'display_name' => 'Footer Bottom Branding Logo',
                'description' => 'Branding logo path within site footer.'
            ],
            [
                'group_id' => $groupLogos->id,
                'key' => 'mobile_logo',
                'value' => '',
                'type' => 'file',
                'display_name' => 'Mobile View Adaptive Logo',
                'description' => 'Optimized logo path for mobile device rendering.'
            ],
            [
                'group_id' => $groupLogos->id,
                'key' => 'favicon',
                'value' => '',
                'type' => 'file',
                'display_name' => 'Tab Favicon Shortcut Asset',
                'description' => 'Icon displayed inside the browser tab.'
            ],
            [
                'group_id' => $groupLogos->id,
                'key' => 'apple_touch_icon',
                'value' => '',
                'type' => 'file',
                'display_name' => 'Apple Touch iOS Home Icon',
                'description' => 'iOS home shortcut icon representation.'
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }

        // 3. Seed Theme Settings
        $themeSettings = [
            'primary_color' => '#1e3a8a',
            'secondary_color' => '#0f766e',
            'accent_color' => '#f59e0b',
            'background_color' => '#f8fafc',
            'text_color' => '#0f172a',
            'dark_primary_color' => '#3b82f6',
            'dark_background_color' => '#0f172a',
        ];

        foreach ($themeSettings as $key => $value) {
            ThemeSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        // 4. Seed SEO Settings
        $seoSettings = [
            'meta_title' => 'GovJobs - Latest Government Jobs Recruitment & Exams',
            'meta_description' => 'Search latest UPSC, SSC, Banking, Railways, Police, Defense, and state government jobs.',
            'meta_keywords' => 'sarkari result, government jobs, recruitment, exams',
            'og_title' => 'GovJobs - Search & Apply to Government Jobs Online',
            'og_description' => 'Get instant updates on new recruitments, admit cards, exam patterns, selection processes.',
            'og_image' => '',
            'twitter_title' => 'GovJobs - Search & Apply to Government Jobs Online',
            'twitter_description' => 'Get instant updates on new recruitments, admit cards, exam patterns, selection processes.',
            'twitter_image' => '',
        ];

        foreach ($seoSettings as $key => $value) {
            SeoSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        // 5. Seed Email Settings
        $emailSettings = [
            'smtp_host' => '127.0.0.1',
            'smtp_port' => '2525',
            'smtp_username' => '',
            'smtp_password' => '',
            'smtp_encryption' => 'tls',
            'sender_name' => 'GovJobs',
            'sender_email' => 'hello@govjobs.com',
        ];

        foreach ($emailSettings as $key => $value) {
            EmailSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        // 6. Seed API Settings
        $apiSettings = [
            'google_api_keys' => '',
            'maps_api' => '',
            'sms_gateway_api' => '',
            'whatsapp_api' => '',
        ];

        foreach ($apiSettings as $key => $value) {
            ApiSetting::updateOrCreate(['key' => $key], [
                'value' => $value,
                'is_encrypted' => true
            ]);
        }

        // 7. Seed Social Links
        $socials = [
            ['platform' => 'Facebook', 'url' => 'https://facebook.com/govjobs', 'icon' => 'fab fa-facebook', 'order_index' => 0],
            ['platform' => 'Twitter', 'url' => 'https://twitter.com/govjobs', 'icon' => 'fab fa-twitter', 'order_index' => 1],
            ['platform' => 'Instagram', 'url' => 'https://instagram.com/govjobs', 'icon' => 'fab fa-instagram', 'order_index' => 2],
            ['platform' => 'LinkedIn', 'url' => 'https://linkedin.com/company/govjobs', 'icon' => 'fab fa-linkedin', 'order_index' => 3],
            ['platform' => 'YouTube', 'url' => 'https://youtube.com/govjobs', 'icon' => 'fab fa-youtube', 'order_index' => 4],
        ];

        foreach ($socials as $social) {
            SocialLink::updateOrCreate(['platform' => $social['platform']], $social);
        }

        // 8. Seed Menus
        $menus = [
            ['name' => 'Header Main Navigation Menu', 'location' => 'header', 'is_active' => true],
            ['name' => 'Footer Column 1 Menu', 'location' => 'footer_col1', 'is_active' => true],
            ['name' => 'Footer Column 2 Menu', 'location' => 'footer_col2', 'is_active' => true],
            ['name' => 'Footer Column 3 Menu', 'location' => 'footer_col3', 'is_active' => true],
            ['name' => 'Footer Column 4 Menu', 'location' => 'footer_col4', 'is_active' => true],
        ];

        $menuModels = [];
        foreach ($menus as $m) {
            $menuModels[$m['location']] = Menu::updateOrCreate(['location' => $m['location']], $m);
        }

        // Header Menu Items
        $headerItems = [
            ['title' => 'Home', 'url' => '/', 'icon' => 'fas fa-home', 'order_index' => 0],
            ['title' => 'All Jobs', 'url' => '/search', 'icon' => 'fas fa-briefcase', 'order_index' => 1],
            ['title' => 'UPSC & SSC', 'url' => '/search/category/upsc-ssc-jobs', 'icon' => 'fas fa-gavel', 'order_index' => 2],
            ['title' => 'Banking', 'url' => '/search/category/banking-finance', 'icon' => 'fas fa-university', 'order_index' => 3],
            ['title' => 'Railways', 'url' => '/search/category/railway-jobs', 'icon' => 'fas fa-subway', 'order_index' => 4],
        ];

        foreach ($headerItems as $item) {
            MenuItem::updateOrCreate([
                'menu_id' => $menuModels['header']->id,
                'title' => $item['title'],
            ], array_merge($item, ['target' => '_self', 'is_active' => true]));
        }

        // Footer Column 1 Items (Top Job Categories)
        $footerCol1Items = [
            ['title' => 'SSC Jobs', 'url' => '/jobs/ssc', 'order_index' => 0],
            ['title' => 'UPSC Jobs', 'url' => '/jobs/upsc', 'order_index' => 1],
            ['title' => 'Banking Jobs', 'url' => '/jobs/banking', 'order_index' => 2],
            ['title' => 'Railway Jobs', 'url' => '/jobs/railway', 'order_index' => 3],
            ['title' => 'Defence Jobs', 'url' => '/jobs/defence', 'order_index' => 4],
            ['title' => 'PSU Jobs', 'url' => '/jobs/psu', 'order_index' => 5],
        ];

        foreach ($footerCol1Items as $item) {
            MenuItem::updateOrCreate([
                'menu_id' => $menuModels['footer_col1']->id,
                'title' => $item['title'],
            ], array_merge($item, ['target' => '_self', 'is_active' => true]));
        }

        // Footer Column 2 Items (Exam Resources)
        $footerCol2Items = [
            ['title' => 'Admit Cards', 'url' => '/admit-cards', 'order_index' => 0],
            ['title' => 'Exam Results', 'url' => '/results', 'order_index' => 1],
            ['title' => 'Answer Keys', 'url' => '/answer-keys', 'order_index' => 2],
            ['title' => 'Syllabus & Patterns', 'url' => '/syllabus', 'order_index' => 3],
            ['title' => 'Exam Calendars', 'url' => '/exam-calendars', 'order_index' => 4],
        ];

        foreach ($footerCol2Items as $item) {
            MenuItem::updateOrCreate([
                'menu_id' => $menuModels['footer_col2']->id,
                'title' => $item['title'],
            ], array_merge($item, ['target' => '_self', 'is_active' => true]));
        }

        // 9. Seed Default CMS Pages
        $pages = [
            [
                'title' => 'About Us',
                'slug' => 'about-us',
                'content' => '<h1>About GovJobs Portal</h1><p>Welcome to GovJobs, India\'s trusted platform for aggregating public sector employment notifications. We help job seekers find official government recruitment details efficiently by structuring complex notification documents into accessible formats.</p>',
                'meta_title' => 'About GovJobs - Dedicated Public Sector Jobs aggregator',
                'meta_description' => 'Read about our mission to organize public job notification listings across India.',
                'meta_keywords' => 'about us, job portal mission'
            ],
            [
                'title' => 'Contact Us',
                'slug' => 'contact-us',
                'content' => '<h1>Contact Us</h1><p>If you have any questions, suggestions, or wish to report an error in any job listing, please reach out to us.</p><p>Email: support@example-govjobs-portal.com</p><p><strong>Please Note:</strong> We do not accept physical job applications. All applications must be submitted directly to the official recruiting boards.</p>',
                'meta_title' => 'Contact Us - GovJobs',
                'meta_description' => 'Contact the GovJobs portal team for support or error reporting.',
                'meta_keywords' => 'contact us, support'
            ],
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'content' => '<h1>Privacy Policy</h1><p>At GovJobs, one of our main priorities is the privacy of our visitors. This Privacy Policy document details the types of information we collect and how we use it to provide you with personalized job alerts and dashboard features.</p>',
                'meta_title' => 'Privacy Policy - GovJobs',
                'meta_description' => 'Learn how we collect and process visitor user data and bookmarks data.',
                'meta_keywords' => 'privacy policy, user data'
            ],
            [
                'title' => 'Terms of Service',
                'slug' => 'terms-of-service',
                'content' => '<h1>Terms of Service</h1><p>These terms outline the rules and regulations for using the GovJobs Website. By accessing this platform, we assume you accept these terms in full. We provide aggregated information for convenience; users must independently verify all recruitment timelines on official board websites.</p>',
                'meta_title' => 'Terms of Service - GovJobs Portal Regulations',
                'meta_description' => 'Read user agreement policies and service access rules.',
                'meta_keywords' => 'terms of service, legal user agreement'
            ],
            [
                'title' => 'Disclaimer',
                'slug' => 'disclaimer',
                'content' => '<h1>Disclaimer</h1><p>GovJobs is a private informational platform. <strong>We are NOT affiliated, associated, authorized, endorsed by, or in any way officially connected with the Government of India or any State Government.</strong> The information provided on this website is for general informational purposes only and sourced from publicly available official notifications. We do not guarantee the completeness or accuracy of job listings.</p>',
                'meta_title' => 'Disclaimer - GovJobs Information Policy',
                'meta_description' => 'Read disclaimer details about government associations.',
                'meta_keywords' => 'disclaimer, official listings'
            ],
            [
                'title' => 'Contact Us',
                'slug' => 'contact-us',
                'content' => '<h1>Contact Us</h1><p>Have any questions, issues with application links, or feedback? Reach out to us at support@govjobs.com or visit our office at 123, Central Plaza, Connaught Place, New Delhi.</p>',
                'meta_title' => 'Contact Us - GovJobs Support Helpdesk',
                'meta_description' => 'Reach out to the GovJobs customer support team or office.',
                'meta_keywords' => 'contact details, helpdesk'
            ],
            [
                'title' => 'Careers',
                'slug' => 'careers',
                'content' => '<h1>Work with Us</h1><p>We are always looking for passionate writers, developers, and data analysis experts to join the GovJobs team. Interested candidates can share their resumes with our recruitment desk.</p>',
                'meta_title' => 'Careers - Join GovJobs Team',
                'meta_description' => 'Explore active job vacancies inside our data analytics and content teams.',
                'meta_keywords' => 'careers, hiring team'
            ]
        ];

        foreach ($pages as $p) {
            CmsPage::updateOrCreate(['slug' => $p['slug']], array_merge($p, ['is_active' => true]));
        }
    }
}
