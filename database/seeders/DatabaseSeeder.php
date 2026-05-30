<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\State;
use App\Models\Category;
use App\Models\Department;
use App\Models\Qualification;
use App\Models\ScrapingSource;
use App\Models\JobPost;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create default Admin user for Dashboard access
        User::firstOrCreate(
            ['email' => 'admin@govjobs.com'],
            [
                'name' => 'Portal Administrator',
                'phone' => '9999999999',
                'password' => Hash::make('Admin@12345'),
                'role' => 'admin',
                'is_active' => true
            ]
        );

        // 2. Create core master States (unique on name)
        $statePanIndia = State::firstOrCreate(['name' => 'Pan India Central'], ['code' => 'CENTRAL']);
        $stateUP = State::firstOrCreate(['name' => 'Uttar Pradesh'], ['code' => 'UP']);
        $stateMaha = State::firstOrCreate(['name' => 'Maharashtra'], ['code' => 'MH']);
        $stateDelhi = State::firstOrCreate(['name' => 'Delhi NCT'], ['code' => 'DL']);
        $stateKar = State::firstOrCreate(['name' => 'Karnataka'], ['code' => 'KA']);

        // 3. Create core Categories (unique on name/slug)
        $catCivil = Category::firstOrCreate(['name' => 'UPSC & SSC Jobs'], ['slug' => 'upsc-ssc-jobs']);
        $catBank = Category::firstOrCreate(['name' => 'Banking & Finance'], ['slug' => 'banking-finance']);
        $catRail = Category::firstOrCreate(['name' => 'Railways (RRB)'], ['slug' => 'railway-jobs']);
        $catDef = Category::firstOrCreate(['name' => 'Defense & Police'], ['slug' => 'defense-jobs']);

        // 4. Create core Departments (unique on name)
        $deptUPSC = Department::firstOrCreate(['name' => 'Union Public Service Commission'], ['code' => 'UPSC']);
        $deptSSC = Department::firstOrCreate(['name' => 'Staff Selection Commission'], ['code' => 'SSC']);
        $deptRBI = Department::firstOrCreate(['name' => 'Reserve Bank of India'], ['code' => 'RBI']);
        $deptSBI = Department::firstOrCreate(['name' => 'State Bank of India'], ['code' => 'SBI']);
        $deptRRB = Department::firstOrCreate(['name' => 'Railway Recruitment Board'], ['code' => 'RRB']);
        $deptNTPC = Department::firstOrCreate(['name' => 'National Thermal Power Corporation'], ['code' => 'NTPC']);
        $deptGPSC = Department::firstOrCreate(['name' => 'Goa Public Service Commission'], ['code' => 'GPSC']);
        $deptArmy = Department::firstOrCreate(['name' => 'Indian Army Board'], ['code' => 'ARMY']);

        // 5. Create Qualifications (unique on name)
        $qTen = Qualification::firstOrCreate(['name' => '10th Pass (High School)'], ['slug' => '10th-pass']);
        $qTwelve = Qualification::firstOrCreate(['name' => '12th Pass (Intermediate)'], ['slug' => '12th-pass']);
        $qGrad = Qualification::firstOrCreate(['name' => 'Graduate Degree (Bachelor)'], ['slug' => 'graduate']);
        $qPost = Qualification::firstOrCreate(['name' => 'Post Graduate Degree (Master)'], ['slug' => 'post-graduate']);

        // 6. Create Automation Scraping Sources (unique on source_url)
        $srcUpsc = ScrapingSource::firstOrCreate(
            ['source_url' => 'https://upsc.gov.in/recruitment/active-jobs-feed'],
            [
                'name' => 'UPSC Official Recruitment Feed',
                'source_type' => 'html',
                'selectors_config' => [
                    'item_selector' => 'table.views-table tr',
                    'title_selector' => 'td.title',
                    'deadline_selector' => 'td.last-date',
                    'default_category_id' => $catCivil->id,
                    'default_department_id' => $deptUPSC->id,
                    'default_state_id' => $statePanIndia->id,
                    'default_qualification_id' => $qGrad->id
                ],
                'cron_expression' => '*/5 * * * *', // Every 5 minutes
                'is_active' => true
            ]
        );

        $srcSsc = ScrapingSource::firstOrCreate(
            ['source_url' => 'https://ssc.gov.in/portal/active-recruitments'],
            [
                'name' => 'SSC Staff Selection Board',
                'source_type' => 'table',
                'selectors_config' => [
                    'item_selector' => 'table.ssc-table tbody tr',
                    'title_selector' => 'td.job-title',
                    'deadline_selector' => 'td.deadline',
                    'default_category_id' => $catCivil->id,
                    'default_department_id' => $deptSSC->id,
                    'default_state_id' => $statePanIndia->id,
                    'default_qualification_id' => $qTwelve->id
                ],
                'cron_expression' => '0 0 * * *', // Daily
                'is_active' => true
            ]
        );

        $srcRailway = ScrapingSource::firstOrCreate(
            ['source_url' => 'https://rrbapply.gov.in/recruitment-feed'],
            [
                'name' => 'RRB Recruitment Board Feed',
                'source_type' => 'html',
                'selectors_config' => [
                    'item_selector' => 'div.rrb-item',
                    'title_selector' => 'h3.rrb-title',
                    'deadline_selector' => 'span.rrb-date',
                    'default_category_id' => $catRail->id,
                    'default_department_id' => $deptRRB->id,
                    'default_state_id' => $statePanIndia->id,
                    'default_qualification_id' => $qTen->id
                ],
                'cron_expression' => '0 12 * * *',
                'is_active' => true
            ]
        );

        $srcBanking = ScrapingSource::firstOrCreate(
            ['source_url' => 'https://sbi.co.in/careers/active-listings'],
            [
                'name' => 'SBI Careers Recruitment Portal',
                'source_type' => 'html',
                'selectors_config' => [
                    'item_selector' => 'div.sbi-job',
                    'title_selector' => 'a.sbi-title',
                    'deadline_selector' => 'span.sbi-deadline',
                    'default_category_id' => $catBank->id,
                    'default_department_id' => $deptSBI->id,
                    'default_state_id' => $statePanIndia->id,
                    'default_qualification_id' => $qGrad->id
                ],
                'cron_expression' => '0 6 * * *',
                'is_active' => true
            ]
        );

        $srcPsu = ScrapingSource::firstOrCreate(
            ['source_url' => 'https://ntpccareers.net/active-jobs'],
            [
                'name' => 'NTPC Career Ingestion Feed',
                'source_type' => 'html',
                'selectors_config' => [
                    'item_selector' => 'div.ntpc-job',
                    'title_selector' => 'h4.ntpc-title',
                    'deadline_selector' => 'span.ntpc-deadline',
                    'default_category_id' => $catBank->id,
                    'default_department_id' => $deptNTPC->id,
                    'default_state_id' => $statePanIndia->id,
                    'default_qualification_id' => $qGrad->id
                ],
                'cron_expression' => '0 18 * * *',
                'is_active' => true
            ]
        );

        $srcStatePsc = ScrapingSource::firstOrCreate(
            ['source_url' => 'https://gpsc.goa.gov.in/active-listings'],
            [
                'name' => 'Goa Public Service Commission Portal',
                'source_type' => 'html',
                'selectors_config' => [
                    'item_selector' => 'table.gpsc-table tr',
                    'title_selector' => 'td.gpsc-title',
                    'deadline_selector' => 'td.gpsc-date',
                    'default_category_id' => $catCivil->id,
                    'default_department_id' => $deptGPSC->id,
                    'default_state_id' => $statePanIndia->id,
                    'default_qualification_id' => $qGrad->id
                ],
                'cron_expression' => '0 0 * * *',
                'is_active' => true
            ]
        );

        $srcDefence = ScrapingSource::firstOrCreate(
            ['source_url' => 'https://joinindianarmy.nic.in/recruitment-board'],
            [
                'name' => 'Indian Army Join Military Feed',
                'source_type' => 'html',
                'selectors_config' => [
                    'item_selector' => 'div.army-job',
                    'title_selector' => 'a.army-title',
                    'deadline_selector' => 'span.army-date',
                    'default_category_id' => $catDef->id,
                    'default_department_id' => $deptArmy->id,
                    'default_state_id' => $statePanIndia->id,
                    'default_qualification_id' => $qTwelve->id
                ],
                'cron_expression' => '0 8 * * *',
                'is_active' => true
            ]
        );

        // 7. Seed Job posts representing realistic, high-quality entries (unique on slug)
        JobPost::firstOrCreate(
            ['slug' => 'upsc-ias-civil-services-examination-2026'],
            [
                'category_id' => $catCivil->id,
                'department_id' => $deptUPSC->id,
                'state_id' => $statePanIndia->id,
                'qualification_id' => $qGrad->id,
                'title' => 'UPSC IAS Civil Services Examination 2026',
                'description' => 'The Union Public Service Commission (UPSC) has released the recruitment notification for Civil Services Examination (CSE) 2026. Top administrative posts like IAS, IFS, IPS, and Group A/B services are recruited through this process.',
                'exam_pattern' => 'The examination comprises three consecutive stages: Preliminary Examination (Objective type), Main Written Examination (Descriptive type), and Personal Interview.',
                'selection_process' => 'Stage 1: CSE Prelims MCQ Test. Stage 2: Descriptive Papers (9 Papers). Stage 3: Board Personality Interview.',
                'age_limit' => '21 - 32 Years',
                'salary_min' => 56100.00,
                'salary_max' => 250000.00,
                'vacancy_count' => 1056,
                'application_fee' => 100.00,
                'official_website_link' => 'https://upsc.gov.in',
                'apply_link' => 'https://upsconline.nic.in',
                'last_date_to_apply' => Carbon::now()->addDays(90)->toDateString(),
                'exam_date' => Carbon::now()->addDays(120)->toDateString(),
                'status' => 'published',
                'published_at' => Carbon::now(),
                'is_featured' => true
            ]
        );

        JobPost::firstOrCreate(
            ['slug' => 'ssc-cgl-tier-1-combined-graduate-level'],
            [
                'category_id' => $catCivil->id,
                'department_id' => $deptSSC->id,
                'state_id' => $statePanIndia->id,
                'qualification_id' => $qGrad->id,
                'title' => 'SSC CGL Tier 1 Combined Graduate Level',
                'description' => 'Staff Selection Commission (SSC) recruits for top central administrative posts (Inspector, Auditor, Assistant Section Officer) across ministries through the CGL exam.',
                'exam_pattern' => 'Computer Based Objective Exam. General Intelligence & Reasoning (50 Marks), General Awareness (50 Marks), Quantitative Aptitude (50 Marks), and English Comprehension (50 Marks).',
                'selection_process' => 'Stage 1: CBT Tier-1 Objective Test. Stage 2: CBT Tier-2 Descriptive & Technical Math Test.',
                'age_limit' => '18 - 30 Years',
                'salary_min' => 35400.00,
                'salary_max' => 112400.00,
                'vacancy_count' => 8440,
                'application_fee' => 100.00,
                'official_website_link' => 'https://ssc.gov.in',
                'apply_link' => 'https://ssc.gov.in/apply',
                'last_date_to_apply' => Carbon::now()->addDays(45)->toDateString(),
                'status' => 'published',
                'published_at' => Carbon::now(),
                'is_featured' => true
            ]
        );

        JobPost::firstOrCreate(
            ['slug' => 'rbi-grade-b-officer-vacancies-2026'],
            [
                'category_id' => $catBank->id,
                'department_id' => $deptRBI->id,
                'state_id' => $statePanIndia->id,
                'qualification_id' => $qGrad->id,
                'title' => 'RBI Grade B Officer Vacancies 2026',
                'description' => 'Reserve Bank of India (RBI) invites online applications for direct recruitment of Grade B Officers in General, DEPR, and DSIM cadres.',
                'exam_pattern' => 'Phase 1: Online Objective Test (200 Marks). General Awareness, English, Quantitative Aptitude, and Reasoning. Phase 2: Economic & Social Issues (Descriptive), English Writing, and Finance & Management.',
                'selection_process' => 'Stage 1: Phase 1 Online Examination. Stage 2: Phase 2 Descriptive Examination. Stage 3: Interview (75 Marks).',
                'age_limit' => '21 - 30 Years',
                'salary_min' => 55200.00,
                'salary_max' => 99750.00,
                'vacancy_count' => 250,
                'application_fee' => 850.00,
                'official_website_link' => 'https://rbi.org.in',
                'apply_link' => 'https://rbi.org.in/apply',
                'last_date_to_apply' => Carbon::now()->addDays(30)->toDateString(),
                'status' => 'published',
                'published_at' => Carbon::now(),
                'is_featured' => true
            ]
        );

        JobPost::firstOrCreate(
            ['slug' => 'sbi-probationary-officer-po-recruitment-2026'],
            [
                'category_id' => $catBank->id,
                'department_id' => $deptSBI->id,
                'state_id' => $statePanIndia->id,
                'qualification_id' => $qGrad->id,
                'title' => 'SBI Probationary Officer (PO) Recruitment 2026',
                'description' => 'State Bank of India (SBI) recruits dynamic graduates for Probationary Officer vacancies. Successful candidates are trained across retail, corporate, and rural branch portfolios.',
                'exam_pattern' => 'Preliminary Exam: English (30 Marks), Quantitative Aptitude (35 Marks), Reasoning (35 Marks). Mains Exam: Objective (200 Marks) & Descriptive Test (50 Marks).',
                'selection_process' => 'Stage 1: Prelims Test. Stage 2: Main Test. Stage 3: Psychometric Test, Group Exercise, and Personal Interview.',
                'age_limit' => '21 - 30 Years',
                'salary_min' => 41960.00,
                'salary_max' => 65000.00,
                'vacancy_count' => 2000,
                'application_fee' => 750.00,
                'official_website_link' => 'https://sbi.co.in/careers',
                'apply_link' => 'https://sbi.co.in/careers/po-apply',
                'last_date_to_apply' => Carbon::now()->addDays(60)->toDateString(),
                'status' => 'published',
                'published_at' => Carbon::now(),
                'is_featured' => false
            ]
        );

        JobPost::firstOrCreate(
            ['slug' => 'railway-rrb-alp-assistant-loco-pilot-exam'],
            [
                'category_id' => $catRail->id,
                'department_id' => $deptRRB->id,
                'state_id' => $statePanIndia->id,
                'qualification_id' => $qTen->id,
                'title' => 'Railway RRB ALP (Assistant Loco Pilot) Exam',
                'description' => 'Railway Recruitment Board (RRB) has announced massive vacancies for Assistant Loco Pilot (ALP) across Indian Railways zones.',
                'exam_pattern' => 'CBT 1: Mathematics, Mental Ability, General Science, General Awareness. CBT 2: Core Trade technical paper.',
                'selection_process' => 'Stage 1: CBT-1. Stage 2: CBT-2. Stage 3: Computer Based Aptitude Test (CBAT). Stage 4: Document Verification.',
                'age_limit' => '18 - 30 Years',
                'salary_min' => 19900.00,
                'salary_max' => 35000.00,
                'vacancy_count' => 5696,
                'application_fee' => 500.00,
                'official_website_link' => 'https://indianrailways.gov.in',
                'apply_link' => 'https://rrbapply.gov.in',
                'last_date_to_apply' => Carbon::now()->addDays(15)->toDateString(),
                'status' => 'published',
                'published_at' => Carbon::now(),
                'is_featured' => false
            ]
        );
    }
}
