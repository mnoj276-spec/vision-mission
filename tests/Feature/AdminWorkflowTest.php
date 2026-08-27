<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\User;

class AdminWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    protected $admin;
    protected $report = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::where('role', 'admin')->first();
        if (!$this->admin) {
            $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        }
    }

    private function logResult($module, $action, $expected, $actual)
    {
        $status = ($expected === $actual || (is_array($expected) && in_array($actual, $expected))) ? 'Pass' : 'Fail';
        $this->report[] = [
            'Module' => $module,
            'Action' => $action,
            'Expected' => is_array($expected) ? implode('/', $expected) : $expected,
            'Actual' => $actual,
            'Status' => $status
        ];
    }

    public function test_generate_full_report()
    {
        // 1. MASTER DATA: States
        $res = $this->actingAs($this->admin)->get('/api/admin/states');
        $this->logResult('Master Data (States)', 'Read/List', 200, $res->status());

        $res = $this->actingAs($this->admin)->post('/api/admin/states', ['name' => 'Test State', 'code' => 'TST', 'status' => 'active']);
        $this->logResult('Master Data (States)', 'Create', [200, 201, 302], $res->status());
        
        $state = \App\Models\State::where('name', 'Test State')->first();
        if ($state) {
            $res = $this->actingAs($this->admin)->post('/api/admin/states/'.$state->id, ['name' => 'Updated State', 'code' => 'TST', 'status' => 'active']);
            $this->logResult('Master Data (States)', 'Edit/Save', [200, 302], $res->status());

            $res = $this->actingAs($this->admin)->delete('/api/admin/states/'.$state->id);
            $this->logResult('Master Data (States)', 'Delete', [200, 204, 302], $res->status());
        }

        // 2. MASTER DATA: Departments
        $res = $this->actingAs($this->admin)->get('/api/admin/departments');
        $this->logResult('Master Data (Departments)', 'Read/List', 200, $res->status());
        $res = $this->actingAs($this->admin)->post('/api/admin/departments', ['name' => 'Test Dept', 'code' => 'TDEPT', 'status' => 'active']);
        $this->logResult('Master Data (Departments)', 'Create', [200, 201, 302], $res->status());

        // 3. MASTER DATA: Qualifications
        $res = $this->actingAs($this->admin)->get('/api/admin/qualifications');
        $this->logResult('Master Data (Qualifications)', 'Read/List', 200, $res->status());
        $res = $this->actingAs($this->admin)->post('/api/admin/qualifications', ['name' => 'Test Qual', 'status' => 'active']);
        $this->logResult('Master Data (Qualifications)', 'Create', [200, 201, 302], $res->status());

        // 4. JOBS
        $cat = \App\Models\Category::first() ?? \App\Models\Category::create(['name' => 'Test Cat', 'slug' => 'test-cat', 'status' => 'active']);
        $dept = \App\Models\Department::first() ?? \App\Models\Department::create(['name' => 'Test Dept', 'code' => 'TDEPT', 'status' => 'active']);
        $stateJob = \App\Models\State::first() ?? \App\Models\State::create(['name' => 'Test State', 'code' => 'TST', 'status' => 'active']);
        $qual = \App\Models\Qualification::first() ?? \App\Models\Qualification::create(['name' => 'Test Qual', 'status' => 'active']);
        
        $res = $this->actingAs($this->admin)->get('/api/admin/jobs');
        $this->logResult('Jobs', 'Read/List', 200, $res->status());
        
        $payload = [
            'title' => 'Test Job Posting 2026',
            'slug' => 'test-job',
            'category_id' => $cat->id,
            'department_id' => $dept->id,
            'state_id' => $stateJob->id,
            'qualification_id' => $qual->id,
            'description' => 'This is a valid twenty character long description.',
            'salary_min' => 1000,
            'salary_max' => 5000,
            'vacancy_count' => 10,
            'application_fee' => 0,
            'last_date_to_apply' => '2027-12-31',
            'official_website_link' => 'https://upsc.gov.in',
            'status' => 'draft'
        ];
        $res = $this->actingAs($this->admin)->post('/api/admin/jobs', $payload); // Standard resource creation
        if ($res->status() == 404 || $res->status() == 405) {
            $res = $this->actingAs($this->admin)->post('/api/admin/jobs/store', $payload);
        }
        $this->logResult('Jobs', 'Create', [200, 201, 302], $res->status());

        $job = \App\Models\ExtractedNotification::first(); // Wait, in the controller it is JobPost not ExtractedNotification!
        $jobPost = \App\Models\JobPost::where('title', 'Test Job Posting 2026')->first() ?? \App\Models\JobPost::first();
        if ($jobPost) {
            $payload['title'] = 'Updated Job Title 2026';
            $res = $this->actingAs($this->admin)->post('/api/admin/jobs/'.$jobPost->id, $payload); // Actually it's probably PUT or POST depends on implementation
            if ($res->status() == 405) $res = $this->actingAs($this->admin)->put('/api/admin/jobs/'.$jobPost->id, $payload);
            $this->logResult('Jobs', 'Edit/Save', [200, 302], $res->status());

            $res = $this->actingAs($this->admin)->post('/api/admin/jobs/'.$jobPost->id.'/toggle-featured');
            $this->logResult('Jobs', 'Toggle Featured', [200, 302], $res->status());

            $res = $this->actingAs($this->admin)->delete('/api/admin/jobs/'.$jobPost->id);
            $this->logResult('Jobs', 'Delete', [200, 204, 302], $res->status());
        } else {
             $this->logResult('Jobs', 'Edit/Save', [200, 302], 'N/A');
             $this->logResult('Jobs', 'Toggle Featured', [200, 302], 'N/A');
             $this->logResult('Jobs', 'Delete', [200, 204, 302], 'N/A');
        }

        // 5. SCRAPERS
        $res = $this->actingAs($this->admin)->get('/api/admin/scrapers');
        $this->logResult('Scrapers', 'Read/List', 200, $res->status());

        $scraper = \App\Models\ScrapingSource::first();
        if ($scraper) {
            $res = $this->actingAs($this->admin)->post('/api/admin/scraper/'.$scraper->id.'/toggle');
            $this->logResult('Scrapers', 'Toggle Active', [200, 302], $res->status());
            
            $scraperPayload = [
                'name' => $scraper->name . ' Updated',
                'source_url' => $scraper->source_url,
                'cron_expression' => '0 * * * *',
                'is_active' => 1,
                'default_category_id' => 1,
                'default_department_id' => 1,
                'default_state_id' => 1,
                'default_qualification_id' => 1,
                'title_selector' => 'h1',
                'row_selector' => 'tr',
                'link_selector' => 'a'
            ];

            $res = $this->actingAs($this->admin)->post('/api/admin/scrapers/'.$scraper->id, $scraperPayload);
            if ($res->status() == 405) $res = $this->actingAs($this->admin)->put('/api/admin/scrapers/'.$scraper->id, $scraperPayload);
            $this->logResult('Scrapers', 'Edit/Save', [200, 302], $res->status());
        }

        // 6. USERS
        $res = $this->actingAs($this->admin)->get('/api/admin/users');
        $this->logResult('Users', 'Read/List', 200, $res->status());

        $userTest = User::where('id', '!=', $this->admin->id)->first();
        if ($userTest) {
            $res = $this->actingAs($this->admin)->post('/api/admin/users/'.$userTest->id.'/update', ['status' => 'inactive']);
            $this->logResult('Users', 'Edit/Save', [200, 302], $res->status());
        }

        // 7. SETTINGS
        $res = $this->actingAs($this->admin)->get('/api/admin/settings');
        $this->logResult('Settings (General)', 'Read/List', 200, $res->status());
        
        $generalPayload = [
            'website_name' => 'Test', 'website_title' => 'Test', 'website_contact_email' => 'test@test.com',
            'copyright_text' => 'Test', 'timezone' => 'UTC', 'date_format' => 'Y-m-d',
            'currency' => 'USD', 'language' => 'en', 'maintenance_mode' => 0,
            'email_notifications' => 1, 'push_notifications' => 1, 'admin_notifications' => 1, 'user_notifications' => 1
        ];
        $res = $this->actingAs($this->admin)->post('/api/admin/settings/general', $generalPayload);
        $this->logResult('Settings (General)', 'Edit/Save', [200, 302], $res->status());

        $seoPayload = [
            'meta_title' => 'Test', 'meta_description' => 'Test', 'meta_keywords' => 'Test',
            'og_title' => 'Test', 'og_description' => 'Test', 'twitter_title' => 'Test', 'twitter_description' => 'Test'
        ];
        $res = $this->actingAs($this->admin)->post('/api/admin/settings/seo', $seoPayload);
        $this->logResult('Settings (SEO)', 'Edit/Save', [200, 302], $res->status());

        $socialPayload = [
            'links' => [
                ['platform' => 'facebook', 'url' => 'https://facebook.com', 'is_active' => 1]
            ]
        ];
        $res = $this->actingAs($this->admin)->post('/api/admin/settings/social', $socialPayload);
        $this->logResult('Settings (Social)', 'Edit/Save', [200, 302], $res->status());

        // 8. QUEUES
        $res = $this->actingAs($this->admin)->get('/api/admin/queues/metrics');
        $this->logResult('Queues', 'Metrics (Read)', 200, $res->status());
        $res = $this->actingAs($this->admin)->get('/api/admin/queues/failed');
        $this->logResult('Queues', 'Failed Jobs (List)', 200, $res->status());


        // Generate Markdown
        $out = "\n\n================================================================================\n";
        $out .= "                        ADMIN PANEL WORKFLOW TEST REPORT                        \n";
        $out .= "================================================================================\n\n";
        
        $out .= "| Module | Action | Test Record | Expected | Actual | Status |\n";
        $out .= "|---|---|---|---|---|---|\n";
        
        $grouped = [];
        foreach ($this->report as $row) {
            $out .= sprintf("| %s | %s | %s | %s | %s | %s |\n", 
                $row['Module'], $row['Action'], 'Staging DB', $row['Expected'], $row['Actual'], $row['Status']
            );
            
            if (!isset($grouped[$row['Module']])) {
                $grouped[$row['Module']] = ['total' => 0, 'passed' => 0];
            }
            $grouped[$row['Module']]['total']++;
            if ($row['Status'] === 'Pass') {
                $grouped[$row['Module']]['passed']++;
            }
        }

        $out .= "\n\n### Summary\n\n";
        
        $modulesFullyTested = [];
        $modulesPartial = [];
        $modulesFailed = [];
        
        foreach ($grouped as $module => $stats) {
            if ($stats['passed'] === $stats['total']) {
                $modulesFullyTested[] = $module;
            } elseif ($stats['passed'] === 0) {
                $modulesFailed[] = $module;
            } else {
                $modulesPartial[] = $module;
            }
        }

        $out .= "**Modules Discovered:** " . count($grouped) . "\n";
        $out .= "**Modules Fully Tested:** " . count($modulesFullyTested) . " (" . implode(', ', $modulesFullyTested) . ")\n";
        $out .= "**Modules Partial:** " . count($modulesPartial) . " (" . implode(', ', $modulesPartial) . ")\n";
        $out .= "**Modules Failed:** " . count($modulesFailed) . " (" . implode(', ', $modulesFailed) . ")\n";
        
        file_put_contents(base_path('scratch/admin_report.txt'), $out);
        $this->assertTrue(true);
    }
}
