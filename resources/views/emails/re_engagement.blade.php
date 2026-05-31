@extends('emails.layout')

@section('content')
<h2 style="font-family: 'Outfit', sans-serif; font-size: 22px; color: #1e3a8a; margin-top: 0;">We Miss You, {{ $name }}!</h2>
<p>Hello,</p>
<p>It's been a couple of weeks since you last visited Sarkari Vision Mission. We wanted to reach out and make sure you're not missing out on high-value recruitment drives closing applications soon!</p>
<p>Here are some of the latest government job vacancies recently published on our platform that might be a great match for you:</p>

<div style="margin: 24px 0;">
    @foreach($jobs as $job)
        <div class="job-card">
            <h3 class="job-title">{{ $job->title }}</h3>
            <div class="job-meta">
                <span class="job-badge">{{ $job->department->name ?? 'Board' }}</span>
                <span class="job-badge tag-result">{{ $job->state->name ?? 'India' }}</span>
            </div>
            <a href="{{ route('email.track.click', ['token' => $tracking_token, 'url' => url('/job/' . $job->slug)]) }}" style="font-size: 14px; font-weight: bold; color: #2563eb; text-decoration: none;">View vacancy details &rarr;</a>
        </div>
    @endforeach
</div>

<p>Log back in to view details, practice mock papers, download your syllabus, or check state notifications.</p>
<div style="text-align: center;">
    <a href="{{ route('email.track.click', ['token' => $tracking_token, 'url' => url('/dashboard')]) }}" class="btn">Return to Your Dashboard</a>
</div>
@endsection
