@extends('emails.layout')

@section('content')
<h2 style="font-family: 'Outfit', sans-serif; font-size: 22px; color: #10b981; margin-top: 0;">📢 Exam Results Declared!</h2>
<p>Hello{{ $name ? ' ' . $name : '' }},</p>
<p>Official exam results have been declared for the following government recruitments. Check your scores and selection status instantly:</p>

<div style="margin: 24px 0;">
    @foreach($jobs as $job)
        <div class="job-card" style="border-left: 4px solid #10b981;">
            <h3 class="job-title">{{ $job->title }}</h3>
            <div class="job-meta">
                <span class="job-badge tag-result">Result Out</span>
                <span class="job-badge">{{ $job->department->name ?? 'Board' }}</span>
                <span class="job-badge" style="background-color:rgba(100,116,139,0.08); color:#64748b;">{{ $job->state->name ?? 'India' }}</span>
            </div>
            <p style="font-size: 14px; color: #475569; margin: 0 0 16px 0;">
                The official result list and marks have been published. Candidates who participated in the exam can now access their results.
            </p>
            <a href="{{ route('email.track.click', ['token' => $tracking_token, 'url' => url('/job/' . $job->slug)]) }}" style="font-size: 14px; font-weight: bold; color: #10b981; text-decoration: none;">Download Selection List / Check Marks &rarr;</a>
        </div>
    @endforeach
</div>

<div style="text-align: center;">
    <a href="{{ route('email.track.click', ['token' => $tracking_token, 'url' => url('/')]) }}" class="btn" style="background-color: #10b981;">Browse Latest Results</a>
</div>
@endsection
