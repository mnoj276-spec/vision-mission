@extends('emails.layout')

@section('content')
<h2 style="font-family: 'Outfit', sans-serif; font-size: 22px; color: #1e3a8a; margin-top: 0;">Hello, {{ $name }}!</h2>

@if($part == 1)
    <p>Welcome to <strong>Sarkari Vision Mission</strong>! We are absolutely thrilled to have you onboard.</p>
    <p>Sarkari Vision Mission is India's premier, lightning-fast platform designed to keep you updated with real-time government job announcements, recruitment notices, exam schedules, hall tickets, and results.</p>
    <p>Here's what you can expect from us:</p>
    <ul>
        <li><strong>Instant Job Alerts</strong> tailored directly to your qualifications and preferred state.</li>
        <li><strong>Real-time Exam Tracker</strong> for downloading admit cards and viewing results immediately.</li>
        <li><strong>Curated Weekly Digests</strong> summarizing top career openings and upcoming deadlines.</li>
    </ul>
    <p>To get started and personalize your preferences, head over to your profile page.</p>
    <div style="text-align: center;">
        <a href="{{ $track_link ?? url('/dashboard') }}" class="btn">Explore Your Dashboard</a>
    </div>
@elseif($part == 2)
    <p>It's Day 2 of your career journey with us! We want to make sure you never miss a critical job alert.</p>
    <p>Did you know you can set up custom filters based on your preferred state, education level, and departments? That way, you only receive notifications that match your profile exactly.</p>
    <p>Setting it up takes less than 2 minutes:</p>
    <ol>
        <li>Login to your account.</li>
        <li>Navigate to the <strong>Job Alerts Preferences</strong> tab.</li>
        <li>Select your categories and preferred regions, then save.</li>
    </ol>
    <div style="text-align: center;">
        <a href="{{ $track_link ?? url('/dashboard') }}" class="btn">Configure Job Alerts</a>
    </div>
@else
    <p>Your weekly preparation partner is here! We hope you've been finding our updates useful.</p>
    <p>A quick reminder that dozens of high-value recruitments are closing applications this week. Don't let deadlines slip away!</p>
    <p>Click the button below to view the active notifications grid, search live vacancies, and find exam patterns.</p>
    <div style="text-align: center;">
        <a href="{{ $track_link ?? url('/') }}" class="btn">Browse Live Recruitments</a>
    </div>
@endif

<p style="margin-top: 24px;">Best regards,<br><strong>The Sarkari Vision Mission Team</strong></p>
@endsection
