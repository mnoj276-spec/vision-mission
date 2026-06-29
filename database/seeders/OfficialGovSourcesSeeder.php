<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Department;
use App\Models\State;
use App\Models\ScrapingSource;
use Illuminate\Database\Seeder;

class OfficialGovSourcesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create States
        $statePanIndia = State::firstOrCreate(['name' => 'Pan India Central'], ['code' => 'CENTRAL']);
        $stateUP = State::firstOrCreate(['name' => 'Uttar Pradesh'], ['code' => 'UP']);
        $stateMaha = State::firstOrCreate(['name' => 'Maharashtra'], ['code' => 'MH']);
        $stateDelhi = State::firstOrCreate(['name' => 'Delhi NCT'], ['code' => 'DL']);
        $stateKar = State::firstOrCreate(['name' => 'Karnataka'], ['code' => 'KA']);
        $stateGoa = State::firstOrCreate(['name' => 'Goa'], ['code' => 'GA']);

        // 2. Create Sector Categories
        $catCivil = Category::firstOrCreate(['slug' => 'upsc-ssc-jobs'], ['name' => 'UPSC & SSC Jobs']);
        $catBank = Category::firstOrCreate(['slug' => 'banking-finance'], ['name' => 'Banking & Finance']);
        $catRail = Category::firstOrCreate(['slug' => 'railway-jobs'], ['name' => 'Railways (RRB)']);
        $catDef = Category::firstOrCreate(['slug' => 'defense-jobs'], ['name' => 'Defense & Police']);
        $catJudicial = Category::firstOrCreate(['slug' => 'judicial-services'], ['name' => 'Judicial Services']);
        $catMunicipal = Category::firstOrCreate(['slug' => 'municipal-local-boards'], ['name' => 'Municipal & Local Boards']);
        $catPsu = Category::firstOrCreate(['slug' => 'psu-corporate-jobs'], ['name' => 'PSU & Corporate Jobs']);
        $catAcademic = Category::firstOrCreate(['slug' => 'academic-research'], ['name' => 'Academic & Research']);
        $catNatural = Category::firstOrCreate(['slug' => 'natural-resources'], ['name' => 'Natural Resources']);
        $catHealth = Category::firstOrCreate(['slug' => 'health-services'], ['name' => 'Health Services']);

        // 3. Create Departments
        $deptUPSC = Department::firstOrCreate(['name' => 'Union Public Service Commission'], ['code' => 'UPSC']);
        $deptSSC = Department::firstOrCreate(['name' => 'Staff Selection Commission'], ['code' => 'SSC']);
        $deptSBI = Department::firstOrCreate(['name' => 'State Bank of India'], ['code' => 'SBI']);
        $deptRBI = Department::firstOrCreate(['name' => 'Reserve Bank of India'], ['code' => 'RBI']);
        $deptIBPS = Department::firstOrCreate(['name' => 'Institute of Banking Personnel Selection'], ['code' => 'IBPS']);
        $deptNABARD = Department::firstOrCreate(['name' => 'NABARD Board'], ['code' => 'NABARD']);
        $deptRRB = Department::firstOrCreate(['name' => 'Railway Recruitment Board'], ['code' => 'RRB']);
        $deptDHC = Department::firstOrCreate(['name' => 'Delhi High Court Board'], ['code' => 'DHC']);
        $deptAHC = Department::firstOrCreate(['name' => 'Allahabad High Court Board'], ['code' => 'AHC']);
        $deptDDC = Department::firstOrCreate(['name' => 'Delhi District Courts Board'], ['code' => 'DDC']);
        $deptMCD = Department::firstOrCreate(['name' => 'Municipal Corporation of Delhi'], ['code' => 'MCD']);
        $deptBMC = Department::firstOrCreate(['name' => 'Brihanmumbai Municipal Corporation'], ['code' => 'BMC']);
        $deptPuneSC = Department::firstOrCreate(['name' => 'Pune Smart City Corporation'], ['code' => 'PUNE-SC']);
        $deptBlrSC = Department::firstOrCreate(['name' => 'Bengaluru Smart City Board'], ['code' => 'BLR-SC']);
        $deptONGC = Department::firstOrCreate(['name' => 'Oil and Natural Gas Corporation'], ['code' => 'ONGC']);
        $deptNTPC = Department::firstOrCreate(['name' => 'National Thermal Power Corporation'], ['code' => 'NTPC']);
        $deptBHEL = Department::firstOrCreate(['name' => 'Bharat Heavy Electricals Limited'], ['code' => 'BHEL']);
        $deptGAIL = Department::firstOrCreate(['name' => 'GAIL India Limited'], ['code' => 'GAIL']);
        $deptIOCL = Department::firstOrCreate(['name' => 'Indian Oil Corporation Limited'], ['code' => 'IOCL']);
        $deptDU = Department::firstOrCreate(['name' => 'Delhi University Board'], ['code' => 'DU']);
        $deptJNU = Department::firstOrCreate(['name' => 'Jawaharlal Nehru University Board'], ['code' => 'JNU']);
        $deptAIIMS = Department::firstOrCreate(['name' => 'AIIMS Delhi Board'], ['code' => 'AIIMS']);
        $deptPGIMER = Department::firstOrCreate(['name' => 'PGIMER Chandigarh Board'], ['code' => 'PGIMER']);
        $deptDRDO = Department::firstOrCreate(['name' => 'Defence Research and Development Organisation'], ['code' => 'DRDO']);
        $deptArmy = Department::firstOrCreate(['name' => 'Indian Army Board'], ['code' => 'ARMY']);
        $deptUPPBPB = Department::firstOrCreate(['name' => 'Uttar Pradesh Police Recruitment Board'], ['code' => 'UPPBPB']);
        $deptKSP = Department::firstOrCreate(['name' => 'Karnataka State Police Board'], ['code' => 'KSP']);
        $deptICAR = Department::firstOrCreate(['name' => 'Indian Council of Agricultural Research'], ['code' => 'ICAR']);
        $deptIFS = Department::firstOrCreate(['name' => 'Indian Forest Service Board'], ['code' => 'IFS']);
        $deptCoal = Department::firstOrCreate(['name' => 'Coal India Limited Board'], ['code' => 'CIL']);
        $deptNMDC = Department::firstOrCreate(['name' => 'NMDC Limited Board'], ['code' => 'NMDC']);
        $deptNHM = Department::firstOrCreate(['name' => 'National Health Mission'], ['code' => 'NHM']);
        $deptKVS = Department::firstOrCreate(['name' => 'Kendriya Vidyalaya Sangathan'], ['code' => 'KVS']);
        $deptISRO = Department::firstOrCreate(['name' => 'Indian Space Research Organisation'], ['code' => 'ISRO']);
        $deptCSIR = Department::firstOrCreate(['name' => 'Council of Scientific and Industrial Research'], ['code' => 'CSIR']);
        $deptDSC = Department::firstOrCreate(['name' => 'District Selection Committee'], ['code' => 'DSC']);
        $deptGPSC = Department::firstOrCreate(['name' => 'Goa Public Service Commission'], ['code' => 'GPSC']);
        $deptUPPSC = Department::firstOrCreate(['name' => 'Uttar Pradesh Public Service Commission'], ['code' => 'UPPSC']);

        // 4. Source Definitions (Official Gov Ingestion Sources)
        $sources = [
            // --- CENTRAL GOVERNMENT ---
            [
                'name' => 'UPSC Official Recruitment Feed',
                'source_url' => 'https://upsc.gov.in/recruitment/active-jobs-feed',
                'selectors_config' => [
                    'driver' => 'upsc',
                    'official_url' => 'https://upsc.gov.in',
                    'notification_page' => 'https://upsc.gov.in/recruitment/active-jobs',
                    'update_frequency' => 'daily',
                    'authentication_type' => 'none',
                    'rss_availability' => 'yes',
                    'formats' => ['HTML', 'RSS', 'XML'],
                    'has_captcha' => 'no',
                    'is_dynamic' => 'no',
                    'priority' => 'high',
                    'crawler_type' => 'http',
                    'item_selector' => 'table.views-table tr',
                    'title_selector' => 'td.title',
                    'deadline_selector' => 'td.last-date',
                    'default_category_id' => $catCivil->id,
                    'default_department_id' => $deptUPSC->id,
                    'default_state_id' => $statePanIndia->id,
                ],
                'cron_expression' => '*/5 * * * *',
            ],
            [
                'name' => 'SSC Staff Selection Board',
                'source_url' => 'https://ssc.gov.in/portal/active-recruitments',
                'selectors_config' => [
                    'driver' => 'ssc',
                    'official_url' => 'https://ssc.gov.in',
                    'notification_page' => 'https://ssc.gov.in/portal/active-recruitments',
                    'update_frequency' => 'daily',
                    'authentication_type' => 'none',
                    'rss_availability' => 'no',
                    'formats' => ['HTML'],
                    'has_captcha' => 'no',
                    'is_dynamic' => 'yes',
                    'priority' => 'high',
                    'crawler_type' => 'playwright',
                    'item_selector' => 'table.ssc-table tbody tr',
                    'title_selector' => 'td.job-title',
                    'deadline_selector' => 'td.deadline',
                    'default_category_id' => $catCivil->id,
                    'default_department_id' => $deptSSC->id,
                    'default_state_id' => $statePanIndia->id,
                ],
                'cron_expression' => '0 0 * * *',
            ],

            // --- STATE GOVERNMENT ---
            [
                'name' => 'Goa Public Service Commission Portal',
                'source_url' => 'https://gpsc.goa.gov.in/active-listings',
                'selectors_config' => [
                    'driver' => 'state_psc',
                    'official_url' => 'https://gpsc.goa.gov.in',
                    'notification_page' => 'https://gpsc.goa.gov.in/active-listings',
                    'update_frequency' => 'daily',
                    'authentication_type' => 'none',
                    'rss_availability' => 'no',
                    'formats' => ['HTML'],
                    'has_captcha' => 'no',
                    'is_dynamic' => 'no',
                    'priority' => 'medium',
                    'crawler_type' => 'headless',
                    'item_selector' => 'table.gpsc-table tr',
                    'title_selector' => 'td.gpsc-title',
                    'deadline_selector' => 'td.gpsc-date',
                    'default_category_id' => $catCivil->id,
                    'default_department_id' => $deptGPSC->id,
                    'default_state_id' => $stateGoa->id,
                ],
                'cron_expression' => '0 0 * * *',
            ],
            [
                'name' => 'Uttar Pradesh Public Service Commission',
                'source_url' => 'https://uppsc.up.nic.in/active-recruitments',
                'selectors_config' => [
                    'driver' => 'state_psc',
                    'official_url' => 'https://uppsc.up.nic.in',
                    'notification_page' => 'https://uppsc.up.nic.in/active-recruitments',
                    'update_frequency' => 'daily',
                    'authentication_type' => 'none',
                    'rss_availability' => 'no',
                    'formats' => ['HTML', 'PDF'],
                    'has_captcha' => 'no',
                    'is_dynamic' => 'no',
                    'priority' => 'high',
                    'crawler_type' => 'http',
                    'item_selector' => 'table.uppsc-table tr',
                    'title_selector' => 'td.title',
                    'deadline_selector' => 'td.date',
                    'default_category_id' => $catCivil->id,
                    'default_department_id' => $deptUPPSC->id,
                    'default_state_id' => $stateUP->id,
                ],
                'cron_expression' => '0 1 * * *',
            ],

            // --- HIGH COURTS & DISTRICT COURTS ---
            [
                'name' => 'Delhi High Court Opportunities',
                'source_url' => 'https://delhihighcourt.nic.in/recruitment',
                'selectors_config' => [
                    'driver' => 'high_court',
                    'official_url' => 'https://delhihighcourt.nic.in',
                    'notification_page' => 'https://delhihighcourt.nic.in/recruitment',
                    'update_frequency' => 'weekly',
                    'authentication_type' => 'none',
                    'rss_availability' => 'no',
                    'formats' => ['HTML', 'PDF'],
                    'has_captcha' => 'no',
                    'is_dynamic' => 'no',
                    'priority' => 'high',
                    'crawler_type' => 'http',
                    'item_selector' => 'div.hc-rec-row',
                    'title_selector' => 'a.title-link',
                    'deadline_selector' => 'span.end-date',
                    'default_category_id' => $catJudicial->id,
                    'default_department_id' => $deptDHC->id,
                    'default_state_id' => $stateDelhi->id,
                ],
                'cron_expression' => '0 2 * * *',
            ],
            [
                'name' => 'Delhi District Courts Job Board',
                'source_url' => 'https://delhidistrictcourts.nic.in/recruitment',
                'selectors_config' => [
                    'driver' => 'high_court',
                    'official_url' => 'https://delhidistrictcourts.nic.in',
                    'notification_page' => 'https://delhidistrictcourts.nic.in/recruitment',
                    'update_frequency' => 'weekly',
                    'authentication_type' => 'none',
                    'rss_availability' => 'no',
                    'formats' => ['HTML', 'PDF'],
                    'has_captcha' => 'no',
                    'is_dynamic' => 'no',
                    'priority' => 'medium',
                    'crawler_type' => 'http',
                    'item_selector' => 'div.court-item',
                    'title_selector' => 'h4.title',
                    'deadline_selector' => 'span.date',
                    'default_category_id' => $catJudicial->id,
                    'default_department_id' => $deptDDC->id,
                    'default_state_id' => $stateDelhi->id,
                ],
                'cron_expression' => '0 3 * * *',
            ],

            // --- MUNICIPAL CORPORATIONS & SMART CITIES ---
            [
                'name' => 'Municipal Corporation of Delhi Careers',
                'source_url' => 'https://mcdonline.nic.in/careers',
                'selectors_config' => [
                    'driver' => 'municipal',
                    'official_url' => 'https://mcdonline.nic.in',
                    'notification_page' => 'https://mcdonline.nic.in/careers',
                    'update_frequency' => 'weekly',
                    'authentication_type' => 'cookie',
                    'rss_availability' => 'no',
                    'formats' => ['HTML'],
                    'has_captcha' => 'no',
                    'is_dynamic' => 'yes',
                    'priority' => 'medium',
                    'crawler_type' => 'headless',
                    'item_selector' => 'table.mcd-jobs tr',
                    'title_selector' => 'td.job-title',
                    'deadline_selector' => 'td.expiry',
                    'default_category_id' => $catMunicipal->id,
                    'default_department_id' => $deptMCD->id,
                    'default_state_id' => $stateDelhi->id,
                ],
                'cron_expression' => '0 4 * * *',
            ],
            [
                'name' => 'Pune Smart City Careers',
                'source_url' => 'https://punesmartcity.in/careers',
                'selectors_config' => [
                    'driver' => 'municipal',
                    'official_url' => 'https://punesmartcity.in',
                    'notification_page' => 'https://punesmartcity.in/careers',
                    'update_frequency' => 'monthly',
                    'authentication_type' => 'none',
                    'rss_availability' => 'no',
                    'formats' => ['HTML', 'PDF'],
                    'has_captcha' => 'no',
                    'is_dynamic' => 'no',
                    'priority' => 'low',
                    'crawler_type' => 'http',
                    'item_selector' => 'div.sc-job-item',
                    'title_selector' => 'h3.title',
                    'deadline_selector' => 'span.date',
                    'default_category_id' => $catMunicipal->id,
                    'default_department_id' => $deptPuneSC->id,
                    'default_state_id' => $stateMaha->id,
                ],
                'cron_expression' => '0 5 * * *',
            ],

            // --- PSUs ---
            [
                'name' => 'NTPC Career Ingestion Feed',
                'source_url' => 'https://ntpccareers.net/active-jobs',
                'selectors_config' => [
                    'driver' => 'psu',
                    'official_url' => 'https://ntpc.co.in',
                    'notification_page' => 'https://ntpccareers.net',
                    'update_frequency' => 'twice_weekly',
                    'authentication_type' => 'none',
                    'rss_availability' => 'no',
                    'formats' => ['HTML'],
                    'has_captcha' => 'no',
                    'is_dynamic' => 'no',
                    'priority' => 'high',
                    'crawler_type' => 'http',
                    'item_selector' => 'div.ntpc-job',
                    'title_selector' => 'h4.ntpc-title',
                    'deadline_selector' => 'span.ntpc-deadline',
                    'default_category_id' => $catPsu->id,
                    'default_department_id' => $deptNTPC->id,
                    'default_state_id' => $statePanIndia->id,
                ],
                'cron_expression' => '0 18 * * *',
            ],
            [
                'name' => 'ONGC Recruitment Ingest',
                'source_url' => 'https://ongcindia.com/careers',
                'selectors_config' => [
                    'driver' => 'psu',
                    'official_url' => 'https://ongcindia.com',
                    'notification_page' => 'https://ongcindia.com/careers',
                    'update_frequency' => 'weekly',
                    'authentication_type' => 'none',
                    'rss_availability' => 'no',
                    'formats' => ['HTML', 'PDF'],
                    'has_captcha' => 'no',
                    'is_dynamic' => 'yes',
                    'priority' => 'high',
                    'crawler_type' => 'playwright',
                    'item_selector' => 'div.ongc-job-row',
                    'title_selector' => 'h3.title',
                    'deadline_selector' => 'span.deadline',
                    'default_category_id' => $catPsu->id,
                    'default_department_id' => $deptONGC->id,
                    'default_state_id' => $statePanIndia->id,
                ],
                'cron_expression' => '0 6 * * *',
            ],

            // --- UNIVERSITIES & MEDICAL COLLEGES ---
            [
                'name' => 'Delhi University Careers',
                'source_url' => 'https://du.ac.in/recruitment',
                'selectors_config' => [
                    'driver' => 'academic',
                    'official_url' => 'https://du.ac.in',
                    'notification_page' => 'https://du.ac.in/recruitment',
                    'update_frequency' => 'weekly',
                    'authentication_type' => 'none',
                    'rss_availability' => 'no',
                    'formats' => ['HTML'],
                    'has_captcha' => 'no',
                    'is_dynamic' => 'no',
                    'priority' => 'medium',
                    'crawler_type' => 'http',
                    'item_selector' => 'div.du-job-item',
                    'title_selector' => 'a.job-title',
                    'deadline_selector' => 'span.expiry',
                    'default_category_id' => $catAcademic->id,
                    'default_department_id' => $deptDU->id,
                    'default_state_id' => $stateDelhi->id,
                ],
                'cron_expression' => '0 7 * * *',
            ],
            [
                'name' => 'AIIMS Delhi Recruitment Board',
                'source_url' => 'https://aiims.edu/recruitment',
                'selectors_config' => [
                    'driver' => 'academic',
                    'official_url' => 'https://aiims.edu',
                    'notification_page' => 'https://aiims.edu/recruitment',
                    'update_frequency' => 'weekly',
                    'authentication_type' => 'none',
                    'rss_availability' => 'no',
                    'formats' => ['HTML', 'PDF'],
                    'has_captcha' => 'no',
                    'is_dynamic' => 'yes',
                    'priority' => 'high',
                    'crawler_type' => 'playwright',
                    'item_selector' => 'table.aiims-table tr',
                    'title_selector' => 'td.title',
                    'deadline_selector' => 'td.date',
                    'default_category_id' => $catAcademic->id,
                    'default_department_id' => $deptAIIMS->id,
                    'default_state_id' => $stateDelhi->id,
                ],
                'cron_expression' => '0 8 * * *',
            ],

            // --- POLICE & DEFENCE ---
            [
                'name' => 'UP Police Recruitment Board',
                'source_url' => 'https://uppbpb.gov.in/active-recruitments',
                'selectors_config' => [
                    'driver' => 'police',
                    'official_url' => 'https://uppbpb.gov.in',
                    'notification_page' => 'https://uppbpb.gov.in/active-recruitments',
                    'update_frequency' => 'twice_weekly',
                    'authentication_type' => 'none',
                    'rss_availability' => 'no',
                    'formats' => ['HTML', 'PDF'],
                    'has_captcha' => 'no',
                    'is_dynamic' => 'no',
                    'priority' => 'high',
                    'crawler_type' => 'http',
                    'item_selector' => 'table.police-table tr',
                    'title_selector' => 'td.title',
                    'deadline_selector' => 'td.date',
                    'default_category_id' => $catDef->id,
                    'default_department_id' => $deptUPPBPB->id,
                    'default_state_id' => $stateUP->id,
                ],
                'cron_expression' => '0 9 * * *',
            ],
            [
                'name' => 'Indian Army Join Military Feed',
                'source_url' => 'https://joinindianarmy.nic.in/recruitment-board',
                'selectors_config' => [
                    'driver' => 'defence',
                    'official_url' => 'https://joinindianarmy.nic.in',
                    'notification_page' => 'https://joinindianarmy.nic.in/recruitment-board',
                    'update_frequency' => 'daily',
                    'authentication_type' => 'captcha',
                    'rss_availability' => 'no',
                    'formats' => ['HTML'],
                    'has_captcha' => 'yes',
                    'is_dynamic' => 'yes',
                    'priority' => 'high',
                    'crawler_type' => 'puppeteer',
                    'item_selector' => 'div.army-job',
                    'title_selector' => 'a.army-title',
                    'deadline_selector' => 'span.army-date',
                    'default_category_id' => $catDef->id,
                    'default_department_id' => $deptArmy->id,
                    'default_state_id' => $statePanIndia->id,
                ],
                'cron_expression' => '0 8 * * *',
            ],

            // --- RAILWAYS & BANKING ---
            [
                'name' => 'RRB Recruitment Board Feed',
                'source_url' => 'https://rrbapply.gov.in/recruitment-feed',
                'selectors_config' => [
                    'driver' => 'railway',
                    'official_url' => 'https://rrbapply.gov.in',
                    'notification_page' => 'https://rrbapply.gov.in/recruitment-feed',
                    'update_frequency' => 'daily',
                    'authentication_type' => 'none',
                    'rss_availability' => 'no',
                    'formats' => ['HTML'],
                    'has_captcha' => 'no',
                    'is_dynamic' => 'no',
                    'priority' => 'high',
                    'crawler_type' => 'http',
                    'item_selector' => 'div.rrb-item',
                    'title_selector' => 'h3.rrb-title',
                    'deadline_selector' => 'span.rrb-date',
                    'default_category_id' => $catRail->id,
                    'default_department_id' => $deptRRB->id,
                    'default_state_id' => $statePanIndia->id,
                ],
                'cron_expression' => '0 12 * * *',
            ],
            [
                'name' => 'SBI Careers Recruitment Portal',
                'source_url' => 'https://sbi.co.in/careers/active-listings',
                'selectors_config' => [
                    'driver' => 'banking',
                    'official_url' => 'https://sbi.co.in',
                    'notification_page' => 'https://sbi.co.in/careers/active-listings',
                    'update_frequency' => 'daily',
                    'authentication_type' => 'none',
                    'rss_availability' => 'no',
                    'formats' => ['HTML'],
                    'has_captcha' => 'no',
                    'is_dynamic' => 'no',
                    'priority' => 'high',
                    'crawler_type' => 'http',
                    'item_selector' => 'div.sbi-job',
                    'title_selector' => 'a.sbi-title',
                    'deadline_selector' => 'span.sbi-deadline',
                    'default_category_id' => $catBank->id,
                    'default_department_id' => $deptSBI->id,
                    'default_state_id' => $statePanIndia->id,
                ],
                'cron_expression' => '0 6 * * *',
            ],

            // --- AGRICULTURE, FOREST & MINING ---
            [
                'name' => 'ICAR Agriculture Careers',
                'source_url' => 'https://icar.org.in/recruitment',
                'selectors_config' => [
                    'driver' => 'natural_resources',
                    'official_url' => 'https://icar.org.in',
                    'notification_page' => 'https://icar.org.in/recruitment',
                    'update_frequency' => 'weekly',
                    'authentication_type' => 'none',
                    'rss_availability' => 'no',
                    'formats' => ['HTML', 'PDF'],
                    'has_captcha' => 'no',
                    'is_dynamic' => 'no',
                    'priority' => 'medium',
                    'crawler_type' => 'http',
                    'item_selector' => 'div.icar-job-row',
                    'title_selector' => 'a.title',
                    'deadline_selector' => 'span.date',
                    'default_category_id' => $catNatural->id,
                    'default_department_id' => $deptICAR->id,
                    'default_state_id' => $statePanIndia->id,
                ],
                'cron_expression' => '0 10 * * *',
            ],
            [
                'name' => 'Coal India Recruitment Ingest',
                'source_url' => 'https://coalindia.in/careers',
                'selectors_config' => [
                    'driver' => 'natural_resources',
                    'official_url' => 'https://coalindia.in',
                    'notification_page' => 'https://coalindia.in/careers',
                    'update_frequency' => 'weekly',
                    'authentication_type' => 'none',
                    'rss_availability' => 'no',
                    'formats' => ['HTML'],
                    'has_captcha' => 'no',
                    'is_dynamic' => 'no',
                    'priority' => 'medium',
                    'crawler_type' => 'http',
                    'item_selector' => 'table.coal-jobs tr',
                    'title_selector' => 'td.title',
                    'deadline_selector' => 'td.deadline',
                    'default_category_id' => $catNatural->id,
                    'default_department_id' => $deptCoal->id,
                    'default_state_id' => $statePanIndia->id,
                ],
                'cron_expression' => '0 11 * * *',
            ],

            // --- HEALTH, EDUCATION & RESEARCH ---
            [
                'name' => 'National Health Mission Board',
                'source_url' => 'https://nhm.gov.in/recruitment',
                'selectors_config' => [
                    'driver' => 'academic',
                    'official_url' => 'https://nhm.gov.in',
                    'notification_page' => 'https://nhm.gov.in/recruitment',
                    'update_frequency' => 'twice_weekly',
                    'authentication_type' => 'none',
                    'rss_availability' => 'no',
                    'formats' => ['HTML', 'PDF'],
                    'has_captcha' => 'no',
                    'is_dynamic' => 'no',
                    'priority' => 'high',
                    'crawler_type' => 'http',
                    'item_selector' => 'div.nhm-job-item',
                    'title_selector' => 'a.title',
                    'deadline_selector' => 'span.date',
                    'default_category_id' => $catHealth->id,
                    'default_department_id' => $deptNHM->id,
                    'default_state_id' => $statePanIndia->id,
                ],
                'cron_expression' => '0 13 * * *',
            ],
            [
                'name' => 'ISRO Space Careers',
                'source_url' => 'https://isro.gov.in/careers',
                'selectors_config' => [
                    'driver' => 'academic',
                    'official_url' => 'https://isro.gov.in',
                    'notification_page' => 'https://isro.gov.in/careers',
                    'update_frequency' => 'weekly',
                    'authentication_type' => 'none',
                    'rss_availability' => 'no',
                    'formats' => ['HTML', 'PDF'],
                    'has_captcha' => 'no',
                    'is_dynamic' => 'yes',
                    'priority' => 'high',
                    'crawler_type' => 'playwright',
                    'item_selector' => 'table.isro-table tr',
                    'title_selector' => 'td.title',
                    'deadline_selector' => 'td.date',
                    'default_category_id' => $catAcademic->id,
                    'default_department_id' => $deptISRO->id,
                    'default_state_id' => $statePanIndia->id,
                ],
                'cron_expression' => '0 14 * * *',
            ],

            // --- DISTRICT RECRUITMENT BOARDS ---
            [
                'name' => 'District Selection Committee UP',
                'source_url' => 'https://districtselection.up.nic.in/recruitment',
                'selectors_config' => [
                    'driver' => 'state_psc',
                    'official_url' => 'https://districtselection.up.nic.in',
                    'notification_page' => 'https://districtselection.up.nic.in/recruitment',
                    'update_frequency' => 'monthly',
                    'authentication_type' => 'none',
                    'rss_availability' => 'no',
                    'formats' => ['HTML'],
                    'has_captcha' => 'no',
                    'is_dynamic' => 'no',
                    'priority' => 'low',
                    'crawler_type' => 'http',
                    'item_selector' => 'table.dsc-table tr',
                    'title_selector' => 'td.title',
                    'deadline_selector' => 'td.date',
                    'default_category_id' => $catCivil->id,
                    'default_department_id' => $deptDSC->id,
                    'default_state_id' => $stateUP->id,
                ],
                'cron_expression' => '0 15 * * *',
            ],
        ];

        // 5. Seed Sources
        foreach ($sources as $src) {
            ScrapingSource::updateOrCreate(
                ['source_url' => $src['source_url']],
                [
                    'name' => $src['name'],
                    'source_type' => 'html',
                    'selectors_config' => $src['selectors_config'],
                    'cron_expression' => $src['cron_expression'],
                    'is_active' => true,
                ]
            );
        }
    }
}
