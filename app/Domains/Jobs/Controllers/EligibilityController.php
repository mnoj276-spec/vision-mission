<?php

namespace App\Domains\Jobs\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\JobPost;
use App\Models\Qualification;
use App\Models\State;
use Illuminate\Http\Request;

class EligibilityController extends Controller
{
    public function index()
    {
        return view('eligibility', [
            'pageTitle' => 'Sarkari Job Eligibility Checker 2026 - Free Dynamic Tool',
            'metaDescription' => 'Input your educational qualification, age, and state to instantly match with eligible active government job listings.',
            'breadcrumbs' => ['Eligibility Checker' => null],
            'qualifications' => Qualification::all(),
            'states' => State::all(),
            'categories' => Category::where('is_active', true)->get()
        ]);
    }

    public function check(Request $request)
    {
        $request->validate([
            'qualification_id' => 'nullable|integer|exists:qualifications,id',
            'state_id'         => 'nullable|integer|exists:states,id',
            'category_id'      => 'nullable|integer|exists:categories,id',
            'age'              => 'nullable|integer|min:16|max:65',
        ]);

        $query = JobPost::published()->jobs();

        if ($request->filled('qualification_id')) {
            $query->where('qualification_id', $request->qualification_id);
        }

        if ($request->filled('state_id')) {
            $query->where('state_id', $request->state_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $jobs = $query->with(['category', 'department', 'state', 'qualification'])->orderBy('id', 'desc')->get();

        $formatted = $jobs->map(fn($job) => [
            'title' => $job->title,
            'slug' => $job->slug,
            'department' => $job->department->name ?? 'Government',
            'state' => $job->state->name ?? 'Pan India',
            'qualification' => $job->qualification->name ?? 'Graduate',
            'vacancy_count' => $job->vacancy_count,
            'salary_min' => number_format($job->salary_min, 0),
            'salary_max' => number_format($job->salary_max, 0),
            'last_date' => $job->last_date_to_apply?->format('d M Y') ?? 'N/A'
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $formatted
        ]);
    }
}
