@extends('emails.layout')

@section('content')
<h2 style="font-family: 'Outfit', sans-serif; font-size: 22px; color: #1e3a8a; margin-top: 0;">Weekly Government Careers Digest</h2>
<p>Hello{{ $name ? ' ' . $name : '' }},</p>
<p>Here is your weekly summary of the hottest active vacancies, recently declared exam results, and newly available admit cards on Sarkari Vision Mission:</p>

@if(count($recentJobs) > 0)
    <h3 style="font-family: 'Outfit', sans-serif; font-size: 18px; color: #1e293b; margin-top: 24px; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">🔥 Hot Vacancies This Week</h3>
    @foreach($recentJobs as $job)
        <div class="job-card">
            <h4 class="job-title" style="font-size: 16px; margin-bottom: 4px;">{{ $job->title }}</h4>
            <div class="job-meta" style="margin-bottom: 8px;">
                <span class="job-badge" style="font-size: 11px;">{{ $job->department->name ?? 'Gov Dept' }}</span>
                <span class="job-badge tag-result" style="font-size: 11px;">{{ $job->state->name ?? 'India' }}</span>
            </div>
            <a href="{{ route('email.track.click', ['token' => $tracking_token, 'url' => url('/job/' . $job->slug)]) }}" style="font-size: 13px; font-weight: bold; color: #2563eb; text-decoration: none;">View Job details &rarr;</a>
        </div>
    @endforeach
@endif

@if(count($admitCards) > 0)
    <h3 style="font-family: 'Outfit', sans-serif; font-size: 18px; color: #f59e0b; margin-top: 24px; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">🎫 Admit Cards Released</h3>
    @foreach($admitCards as $job)
        <div class="job-card" style="border-left: 4px solid #f59e0b;">
            <h4 class="job-title" style="font-size: 16px; margin-bottom: 4px;">{{ $job->title }}</h4>
            <div class="job-meta" style="margin-bottom: 8px;">
                <span class="job-badge tag-admit" style="font-size: 11px;">Admit Card Active</span>
                <span class="job-badge" style="font-size: 11px;">{{ $job->department->name ?? 'Gov Dept' }}</span>
            </div>
            <a href="{{ route('email.track.click', ['token' => $tracking_token, 'url' => url('/job/' . $job->slug)]) }}" style="font-size: 13px; font-weight: bold; color: #f59e0b; text-decoration: none;">Download entry card &rarr;</a>
        </div>
    @endforeach
@endif

@if(count($results) > 0)
    <h3 style="font-family: 'Outfit', sans-serif; font-size: 18px; color: #10b981; margin-top: 24px; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">📢 Results Declared</h3>
    @foreach($results as $job)
        <div class="job-card" style="border-left: 4px solid #10b981;">
            <h4 class="job-title" style="font-size: 16px; margin-bottom: 4px;">{{ $job->title }}</h4>
            <div class="job-meta" style="margin-bottom: 8px;">
                <span class="job-badge tag-result" style="font-size: 11px;">Results Out</span>
                <span class="job-badge" style="font-size: 11px;">{{ $job->department->name ?? 'Gov Dept' }}</span>
            </div>
            <a href="{{ route('email.track.click', ['token' => $tracking_token, 'url' => url('/job/' . $job->slug)]) }}" style="font-size: 13px; font-weight: bold; color: #10b981; text-decoration: none;">Download selection sheet &rarr;</a>
        </div>
    @endforeach
@endif

<div style="text-align: center; margin-top: 32px;">
    <a href="{{ route('email.track.click', ['token' => $tracking_token, 'url' => url('/')]) }}" class="btn">Explore Vision Mission Portal</a>
</div>
@endsection
