<?php

namespace App\Domains\Jobs\Controllers;

use App\Http\Controllers\Controller;
use App\Models\JobPost;
use Illuminate\Http\Request;

class SalaryInfoController extends Controller
{
    public function index()
    {
        // 1. Compile category-wise salary stats
        $categoryStats = JobPost::published()->jobs()
            ->join('categories', 'job_posts.category_id', '=', 'categories.id')
            ->selectRaw('
                categories.name as name, 
                categories.slug as slug, 
                MIN(job_posts.salary_min) as min_salary, 
                MAX(job_posts.salary_max) as max_salary, 
                AVG((job_posts.salary_min + job_posts.salary_max) / 2) as avg_salary, 
                COUNT(*) as count
            ')
            ->groupBy('categories.id', 'categories.name', 'categories.slug')
            ->orderBy('avg_salary', 'desc')
            ->get();

        // 2. Compile department-wise salary stats (top 15 highest paying)
        $departmentStats = JobPost::published()->jobs()
            ->join('departments', 'job_posts.department_id', '=', 'departments.id')
            ->selectRaw('
                departments.name as name, 
                MIN(job_posts.salary_min) as min_salary, 
                MAX(job_posts.salary_max) as max_salary, 
                AVG((job_posts.salary_min + job_posts.salary_max) / 2) as avg_salary, 
                COUNT(*) as count
            ')
            ->groupBy('departments.id', 'departments.name')
            ->orderBy('avg_salary', 'desc')
            ->limit(15)
            ->get();

        // 3. Compile state-wise salary stats
        $stateStats = JobPost::published()->jobs()
            ->join('states', 'job_posts.state_id', '=', 'states.id')
            ->selectRaw('
                states.name as name, 
                states.slug as slug, 
                MIN(job_posts.salary_min) as min_salary, 
                MAX(job_posts.salary_max) as max_salary, 
                AVG((job_posts.salary_min + job_posts.salary_max) / 2) as avg_salary, 
                COUNT(*) as count
            ')
            ->groupBy('states.id', 'states.name', 'states.slug')
            ->orderBy('avg_salary', 'desc')
            ->get();

        return view('salary', [
            'pageTitle' => 'Sarkari Job Salary Information Hub 2026 - Pay Scales & Ranges',
            'metaDescription' => 'Explore the complete salary matrix of active government recruitments. Compare average, minimum, and maximum pay scales across streams, boards, and states.',
            'breadcrumbs' => ['Salary Information' => null],
            'categories' => $categoryStats,
            'departments' => $departmentStats,
            'states' => $stateStats
        ]);
    }
}
