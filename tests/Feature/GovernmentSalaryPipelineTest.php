<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Department;
use App\Models\JobPost;
use App\Models\State;
use App\Models\Qualification;
use App\Helpers\SalaryParser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GovernmentSalaryPipelineTest extends TestCase
{
    use RefreshDatabase;

    protected Category $category;
    protected Department $department;
    protected State $state;
    protected Qualification $qualification;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic DB records required for creating JobPosts
        $this->state = State::create(['name' => 'Pan India', 'code' => 'CENTRAL']);
        $this->category = Category::create(['name' => 'Banking & Finance', 'slug' => 'banking-finance']);
        $this->department = Department::create(['name' => 'Department of Science and Technology', 'code' => 'DST']);
        $this->qualification = Qualification::create(['name' => 'Bachelor of Technology', 'slug' => 'graduate']);
    }

    /**
     * Test direct parsing of all 10 government salary examples.
     */
    public function test_salary_parser_extracts_all_government_formats(): void
    {
        // 1. ₹19,900 – ₹63,200 (unicode en-dash)
        $p1 = SalaryParser::parse('₹19,900 – ₹63,200');
        $this->assertEquals(19900.0, $p1['salary_min']);
        $this->assertEquals(63200.0, $p1['salary_max']);
        $this->assertEquals('₹19,900 – ₹63,200', $p1['pay_scale']);

        // 2. Pay Level-4
        $p2 = SalaryParser::parse('Pay Level-4');
        $this->assertEquals('Level 4', $p2['pay_level']);

        // 3. Level 10
        $p3 = SalaryParser::parse('Level 10');
        $this->assertEquals('Level 10', $p3['pay_level']);

        // 4. Rs.56100-177500
        $p4 = SalaryParser::parse('Rs.56100-177500');
        $this->assertEquals(56100.0, $p4['salary_min']);
        $this->assertEquals(177500.0, $p4['salary_max']);

        // 5. Pay Matrix Level 7
        $p5 = SalaryParser::parse('Pay Matrix Level 7');
        $this->assertEquals('Level 7', $p5['pay_level']);
        $this->assertEquals('Pay Matrix Level 7', $p5['pay_matrix']);

        // 6. Rs.25500 + Allowances
        $p6 = SalaryParser::parse('Rs.25500 + Allowances');
        $this->assertEquals(25500.0, $p6['salary_min']);
        $this->assertEquals('Rs.25500 + Allowances', $p6['pay_scale']);

        // 7. As per 7th CPC
        $p7 = SalaryParser::parse('As per 7th CPC');
        $this->assertEquals('7th CPC', $p7['pay_matrix']);

        // 8. Fixed Pay
        $p8 = SalaryParser::parse('Fixed Pay');
        $this->assertEquals('Fixed Pay', $p8['pay_scale']);

        // 9. Honorarium
        $p9 = SalaryParser::parse('Honorarium');
        $this->assertEquals('Honorarium', $p9['stipend']);

        // 10. Consolidated Salary
        $p10 = SalaryParser::parse('Consolidated Salary');
        $this->assertEquals('Consolidated Salary', $p10['pay_scale']);
    }

    /**
     * Test that saving a JobPost with the "salary" attribute triggers the observer
     * and populates the database fields correctly.
     */
    public function test_job_post_observer_automatically_parses_salary_on_save(): void
    {
        $job = JobPost::create([
            'category_id' => $this->category->id,
            'department_id' => $this->department->id,
            'state_id' => $this->state->id,
            'qualification_id' => $this->qualification->id,
            'title' => 'Test Recruitment Officer 2026',
            'slug' => 'test-recruitment-officer-2026',
            'description' => 'Recruitment for testing.',
            'status' => 'published',
            'post_type' => 'job',
            'salary' => 'Pay Matrix Level 7 (Rs.44900-142400) + GP 4600 as per 7th CPC',
            'last_date_to_apply' => now()->addMonth(),
        ]);

        // Refetch from database
        $freshJob = JobPost::find($job->id);

        // Verify database columns were populated
        $this->assertEquals(44900.0, (float) $freshJob->salary_min);
        $this->assertEquals(142400.0, (float) $freshJob->salary_max);
        $this->assertEquals('Level 7', $freshJob->pay_level);
        $this->assertEquals('Pay Matrix Level 7', $freshJob->pay_matrix);
        $this->assertEquals('Grade Pay 4600', $freshJob->salary_grade);
        $this->assertEquals('Pay Matrix Level 7 (Rs.44900-142400) + GP 4600 as per 7th CPC', $freshJob->pay_scale);
        $this->assertNull($freshJob->stipend);
    }
}
