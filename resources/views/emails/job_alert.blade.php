@extends('emails.layout')

@section('content')
<h2 style="font-family: 'Outfit', sans-serif; font-size: 22px; color: #1e3a8a; margin-top: 0;">New Job Openings Matched!</h2>
<p>Hello{{ $name ? ' ' . $name : '' }},</p>
<p>We found new government job postings matching your alert configurations and preferred categories. Check them out below:</p>

<div style="margin: 24px 0;">
    @foreach($jobs as $job)
        <div class="job-card">
            <h3 class="job-title">{{ $job->title }}</h3>
            <div class="job-meta">
                <span class="job-badge">{{ $job->department->name ?? 'Gov Dept' }}</span>
                <span class="job-badge tag-result">{{ $job->state->name ?? 'India' }}</span>
                @if($job->vacancy_count)
                    <span class="job-badge" style="background-color:rgba(100,116,139,0.08); color:#64748b;">{{ $job->vacancy_count }} Vacancies</span>
                @endif
            </div>
            <p style="font-size: 14px; color: #475569; margin: 0 0 16px 0;">
                {{ Str::limit(strip_tags($job->description), 140) }}
            </p>
            @if($job->last_date_to_apply)
                <p style="font-size: 13px; font-weight: 600; color: #ef4444; margin: 0 0 16px 0;">
                    ⏳ Last Date to Apply: {{ $job->last_date_to_apply->format('d M, Y') }}
                </p>
            @endif
            <a href="{{ route('email.track.click', ['token' => $tracking_token, 'url' => url('/job/' . $job->slug)]) }}" style="font-size: 14px; font-weight: bold; color: #2563eb; text-decoration: none;">View Vacancy Details &rarr;</a>
        </div>
    @endforeach
</div>

<p>For the latest government job recruitments, mock tests, and exam updates, visit our portal.</p>
<div style="text-align: center;">
    <a href="{{ route('email.track.click', ['token' => $tracking_token, 'url' => url('/')]) }}" class="btn">Explore All Live Jobs</a>
</div>
@endsection
