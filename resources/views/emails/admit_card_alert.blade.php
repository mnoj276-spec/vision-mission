@extends('emails.layout')

@section('content')
<h2 style="font-family: 'Outfit', sans-serif; font-size: 22px; color: #f59e0b; margin-top: 0;">🎫 Admit Cards Released!</h2>
<p>Hello{{ $name ? ' ' . $name : '' }},</p>
<p>Admit cards/hall tickets have been officially released for the following upcoming examinations. Download your entry ticket now:</p>

<div style="margin: 24px 0;">
    @foreach($jobs as $job)
        <div class="job-card" style="border-left: 4px solid #f59e0b;">
            <h3 class="job-title">{{ $job->title }}</h3>
            <div class="job-meta">
                <span class="job-badge tag-admit">Admit Card Active</span>
                <span class="job-badge">{{ $job->department->name ?? 'Board' }}</span>
                @if($job->exam_date)
                    <span class="job-badge" style="background-color:rgba(239,68,68,0.08); color:#ef4444;">📅 Exam: {{ $job->exam_date->format('d M, Y') }}</span>
                @endif
            </div>
            <p style="font-size: 14px; color: #475569; margin: 0 0 16px 0;">
                The selection board has released candidate hall tickets. Download your copy and review the venue guidelines, dress code, and mandatory ID proofs required at the test center.
            </p>
            <a href="{{ route('email.track.click', ['token' => $tracking_token, 'url' => url('/job/' . $job->slug)]) }}" style="font-size: 14px; font-weight: bold; color: #f59e0b; text-decoration: none;">Download Admit Card &rarr;</a>
        </div>
    @endforeach
</div>

<div style="text-align: center;">
    <a href="{{ route('email.track.click', ['token' => $tracking_token, 'url' => url('/')]) }}" class="btn" style="background-color: #f59e0b;">Check Admit Card Portal</a>
</div>
@endsection
