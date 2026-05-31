@extends('layouts.app')

@section('title', 'GovJobs - Premium Automated Government Jobs Portal')

@section('content')

<!-- AAGGREGATOR DESIGN SYSTEM STYLES -->
<style>
    /* Scrolling Marquee Update Ticker */
    .ticker-wrap {
        width: 100%;
        box-sizing: border-box;
        overflow: hidden;
        height: 48px;
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.15) 0%, rgba(16, 185, 129, 0.1) 100%);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        display: flex;
        align-items: center;
        margin-bottom: 2rem;
        padding: 0 1rem;
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.05);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }
    .ticker-label {
        background: linear-gradient(90deg, #ef4444 0%, #dc2626 100%);
        color: #fff;
        font-weight: 700;
        font-size: 0.75rem;
        padding: 0.3rem 0.75rem;
        border-radius: 4px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-right: 1.5rem;
        white-space: nowrap;
        animation: ticker-pulse 1.5s infinite;
    }
    @keyframes ticker-pulse {
        0% { opacity: 0.85; transform: scale(1); }
        50% { opacity: 1; transform: scale(1.03); }
        100% { opacity: 0.85; transform: scale(1); }
    }
    .ticker {
        display: flex;
        white-space: nowrap;
        width: 100%;
        overflow: hidden;
        position: relative;
    }
    .ticker-item-list {
        display: inline-flex;
        animation: marquee 35s linear infinite;
    }
    .ticker-item {
        color: var(--text-primary);
        text-decoration: none;
        font-size: 0.85rem;
        font-weight: 600;
        margin-right: 3rem;
        transition: color 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        white-space: nowrap;
    }
    .ticker-item:hover {
        color: var(--accent-color);
        text-decoration: underline;
    }
    @keyframes marquee {
        0% { transform: translate3d(0, 0, 0); }
        100% { transform: translate3d(-50%, 0, 0); }
    }

    /* Trending Hot Action Cards */
    .trending-grid {
        display: grid;
        grid-template-columns: repeat(8, 1fr);
        gap: 0.75rem;
        margin-bottom: 3rem;
    }
    
    @media (max-width: 1200px) {
        .trending-grid {
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
        }
    }
    
    @media (max-width: 640px) {
        .trending-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
        }
    }
    
    .trending-card {
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 1.1rem 0.5rem;
        text-align: center;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        height: 105px;
        position: relative;
        overflow: hidden;
        box-shadow: var(--card-shadow);
    }
    
    .trending-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background: var(--card-accent, var(--accent-color));
        opacity: 0.85;
    }
    
    .trending-card:hover {
        transform: translateY(-5px);
        border-color: var(--card-accent, var(--accent-color));
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 0 15px -3px var(--card-accent, var(--accent-color));
        background: var(--bg-secondary);
    }
    
    .dark-theme .trending-card:hover {
        box-shadow: 0 12px 30px -8px rgba(0, 0, 0, 0.5), 0 0 20px -5px var(--card-accent, var(--accent-color));
    }
    
    .trending-card .card-icon {
        font-size: 1.6rem;
        margin-bottom: 0.15rem;
        filter: drop-shadow(0 2px 6px rgba(0,0,0,0.15));
        transition: transform 0.3s ease;
    }
    
    .trending-card:hover .card-icon {
        transform: scale(1.12) rotate(3deg);
    }
    
    .trending-card .card-title {
        font-family: 'Outfit', sans-serif;
        font-size: 0.82rem;
        font-weight: 700;
        color: var(--text-primary);
        text-transform: uppercase;
        letter-spacing: 0.02em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
        padding: 0 0.25rem;
    }

    /* Sarkari Board Panels */
    .sarkari-panels-container {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.5rem;
        margin-bottom: 2.5rem;
    }
    @media (max-width: 1024px) {
        .sarkari-panels-container {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    @media (max-width: 768px) {
        .sarkari-panels-container {
            grid-template-columns: 1fr;
        }
    }
    .sarkari-panel {
        min-width: 0;
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.5rem 1.25rem;
        display: flex;
        flex-direction: column;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease;
    }
    .sarkari-panel:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3);
    }
    .sarkari-panel-header {
        font-size: 1.2rem;
        font-weight: 800;
        margin-bottom: 1.25rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid var(--border-color);
        display: flex;
        align-items: center;
        gap: 0.6rem;
        font-family: 'Outfit', sans-serif;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .sarkari-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 0.6rem;
        max-height: 480px;
        overflow-y: auto;
    }
    /* Style scrollbar for list containers */
    .sarkari-list::-webkit-scrollbar {
        width: 5px;
    }
    .sarkari-list::-webkit-scrollbar-track {
        background: rgba(255,255,255,0.02);
    }
    .sarkari-list::-webkit-scrollbar-thumb {
        background: rgba(255,255,255,0.1);
        border-radius: 3px;
    }
    .sarkari-item {
        min-width: 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0.75rem;
        border-radius: 6px;
        background: rgba(255, 255, 255, 0.01);
        border: 1px solid rgba(255,255,255,0.02);
        transition: all 0.2s ease;
    }
    .sarkari-item:hover {
        background: rgba(255, 255, 255, 0.04);
        border-color: rgba(255,255,255,0.06);
    }
    .sarkari-item-link {
        color: var(--text-primary);
        text-decoration: none;
        font-size: 0.82rem;
        font-weight: 500;
        line-height: 1.4;
        flex-grow: 1;
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        cursor: pointer;
    }
    .sarkari-item-link:hover {
        color: var(--accent-color);
    }
    .new-badge {
        font-size: 0.6rem;
        font-weight: 800;
        color: #fff;
        background: linear-gradient(90deg, #ef4444 0%, #ea580c 100%);
        padding: 0.15rem 0.4rem;
        border-radius: 3px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        white-space: nowrap;
        animation: badge-blink 1.2s infinite;
    }
    @keyframes badge-blink {
        0% { opacity: 0.7; }
        50% { opacity: 1; }
        100% { opacity: 0.7; }
    }

    /* Monetization Google AdSense Responsive Frame */
    .ad-banner-placeholder {
        width: 100%;
        background: linear-gradient(135deg, rgba(255,255,255,0.01) 0%, rgba(255,255,255,0.02) 100%);
        border: 1px dashed var(--border-color);
        border-radius: 10px;
        padding: 1.5rem;
        text-align: center;
        margin-bottom: 2.5rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.6rem;
        position: relative;
        overflow: hidden;
    }
    .ad-banner-placeholder::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(37,99,235,0.03) 0%, transparent 60%);
        pointer-events: none;
    }
    .ad-badge {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255,255,255,0.05);
        color: var(--text-secondary);
        font-size: 0.6rem;
        font-weight: 700;
        text-transform: uppercase;
        padding: 0.2rem 0.6rem;
        border-radius: 4px;
        letter-spacing: 0.08em;
    }

    /* Monetization Premium Styles */
    .job-card.is-sponsored {
        background: linear-gradient(135deg, rgba(217, 119, 6, 0.04) 0%, rgba(217, 119, 6, 0.01) 100%) !important;
        border: 1.5px solid rgba(217, 119, 6, 0.35) !important;
        box-shadow: 0 8px 30px rgba(217, 119, 6, 0.06);
        position: relative;
        animation: sponsor-glow-pulse 2.5s infinite alternate;
    }
    .job-card.is-sponsored::after {
        content: 'SPONSORED MATCH';
        position: absolute;
        top: 0;
        right: 0;
        background: linear-gradient(90deg, #d97706 0%, #f59e0b 100%);
        color: white;
        font-size: 0.62rem;
        font-weight: 800;
        padding: 0.2rem 0.5rem;
        border-radius: 0 0 0 8px;
        letter-spacing: 0.05em;
        z-index: 10;
    }
    @keyframes sponsor-glow-pulse {
        0% { border-color: rgba(217, 119, 6, 0.25); box-shadow: 0 8px 30px rgba(217, 119, 6, 0.05); }
        100% { border-color: rgba(217, 119, 6, 0.5); box-shadow: 0 8px 35px rgba(217, 119, 6, 0.12); }
    }
    .badge.badge-sponsored {
        background: linear-gradient(90deg, #d97706 0%, #f59e0b 100%) !important;
        color: white !important;
        font-weight: 800;
        letter-spacing: 0.03em;
    }
    /* Autocomplete Suggestions Menu */
    .autocomplete-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        margin-top: 0.5rem;
        max-height: 350px;
        overflow-y: auto;
        z-index: 1000;
        box-shadow: 0 15px 35px -5px rgba(0,0,0,0.25);
        display: none;
        backdrop-filter: blur(14px);
    }
    .autocomplete-section {
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 0.5rem;
    }
    .autocomplete-section:last-child {
        border-bottom: none;
    }
    .autocomplete-header {
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        color: var(--accent-color);
        letter-spacing: 0.08em;
        padding: 0.75rem 1rem 0.4rem 1rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        background: rgba(255,255,255,0.01);
    }
    .autocomplete-item {
        padding: 0.6rem 1.25rem;
        font-size: 0.88rem;
        color: var(--text-secondary);
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.15s ease;
    }
    .autocomplete-item:hover {
        background: rgba(37, 99, 235, 0.08);
        color: var(--text-primary);
        padding-left: 1.5rem;
    }
    .autocomplete-item .badge-type {
        font-size: 0.68rem;
        font-weight: 700;
        background: rgba(255,255,255,0.04);
        padding: 0.15rem 0.4rem;
        border-radius: 4px;
        color: var(--text-secondary);
    }
    .typo-banner {
        background: rgba(245, 158, 11, 0.08);
        border: 1.5px solid rgba(245, 158, 11, 0.2);
        color: #f59e0b;
        padding: 0.85rem 1.25rem;
        border-radius: 10px;
        margin-bottom: 1.5rem;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
        gap: 0.6rem;
        animation: slideDown 0.3s ease;
        font-weight: 500;
    }
    .typo-banner a {
        color: var(--text-primary);
        text-decoration: underline;
        font-weight: 700;
        cursor: pointer;
    }
    .typo-banner a:hover {
        color: var(--accent-color);
    }
</style>

<div style="max-width: 1400px; margin: 0 auto; padding: 0 5%;">

<!-- 1. Hero Welcome Segment -->
<section class="hero" style="margin-bottom: 1.5rem;">
    <h1>Find Your Dream <span style="color: var(--accent-color);">Government Job</span> Today</h1>
    <p>Discover real-time, highly validated recruitment alerts across UPSC, SSC, Banking, Railways, and individual states. Updated automatically, verified by AI, 100% accurate.</p>
</section>

<!-- 2. Scrolling Marquee Updates Ticker -->
<div class="ticker-wrap">
    <div class="ticker-label">LATEST UPDATES</div>
    <div class="ticker">
        <div class="ticker-item-list">
            @forelse($tickerNotices as $tNotice)
                <a href="#" class="ticker-item btn-view" data-slug="{{ $tNotice->slug }}">
                    <span class="new-badge" style="margin-right: 0.25rem;">NEW</span>
                    {{ $tNotice->title }}
                </a>
            @empty
                <a href="#" class="ticker-item">Welcome to GovJobs - Real-time Highly Validated Recruitment Aggregator Platform</a>
            @endforelse
            <!-- Duplicate array items to create seamless scrolling loop -->
            @foreach($tickerNotices as $tNotice)
                <a href="#" class="ticker-item btn-view" data-slug="{{ $tNotice->slug }}">
                    <span class="new-badge" style="margin-right: 0.25rem;">NEW</span>
                    {{ $tNotice->title }}
                </a>
            @endforeach
        </div>
    </div>
</div>

</div><!-- end centering wrapper -->

<!-- ======================= PORTAL FRONTEND TAB SEGMENTS ======================= -->

<!-- TAB 1: PRIMARY JOBS DIRECTORY & FILTERS (Active by default) -->
<div class="portal-main-tab active" id="jobs-search-section" style="padding: 0 5%; max-width: 1400px; margin: 0 auto;">

    <!-- 3. Trending Hot Quick Navigation Cards -->
    <div class="trending-grid">
        <a href="#sarkari-jobs" class="trending-card" style="--card-accent: #3b82f6;">
            <div class="card-icon" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">💼</div>
            <span class="card-title">Latest Jobs</span>
        </a>
        <a href="#sarkari-admit-cards" class="trending-card" style="--card-accent: #10b981;">
            <div class="card-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">🎟️</div>
            <span class="card-title">Admit Cards</span>
        </a>
        <a href="#sarkari-results" class="trending-card" style="--card-accent: #8b5cf6;">
            <div class="card-icon" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;">🏆</div>
            <span class="card-title">Exam Results</span>
        </a>
        <a href="#sarkari-answer-keys" class="trending-card" style="--card-accent: #f59e0b;">
            <div class="card-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">🔑</div>
            <span class="card-title">Answer Keys</span>
        </a>
        <a href="#sarkari-syllabus" class="trending-card" style="--card-accent: #ec4899;">
            <div class="card-icon" style="background: rgba(236, 72, 153, 0.1); color: #ec4899;">📖</div>
            <span class="card-title">Syllabus</span>
        </a>
        <a href="#sarkari-notices" class="trending-card" style="--card-accent: #ef4444;">
            <div class="card-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">📢</div>
            <span class="card-title">Notices</span>
        </a>
        <a href="#sarkari-admissions" class="trending-card" style="--card-accent: #06b6d4;">
            <div class="card-icon" style="background: rgba(6, 182, 212, 0.1); color: #06b6d4;">🎓</div>
            <span class="card-title">Admissions</span>
        </a>
        <a href="#sarkari-scholarships" class="trending-card" style="--card-accent: #f97316;">
            <div class="card-icon" style="background: rgba(249, 115, 22, 0.1); color: #f97316;">💰</div>
            <span class="card-title">Scholarships</span>
        </a>
    </div>

    <!-- 4. Monetization Responsive Ad Placeholder Row 1 -->
    @if(!auth()->check() || !in_array(auth()->user()->membership_plan, ['premium', 'pro']))
    <div class="ad-banner-placeholder" id="home_leaderboard_ad">
        <span class="ad-badge">Advertisement</span>
        <div style="font-size: 0.9rem; font-weight: 600; color: var(--text-secondary); display: flex; align-items: center; gap: 0.5rem;">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Sponsored High-Target Ad slot - Monetization Enabled
        </div>
        <div style="font-size: 0.75rem; color: var(--text-secondary); opacity: 0.8;">Supports GovJobs Free Auto-Extraction & Failsafe Processing Infrastructure</div>
    </div>
    @endif

    <!-- 5. Sarkari Grid Row 1 (Jobs, Admit Cards, Results) -->
    <div class="sarkari-panels-container">
        <!-- Panel 1: Latest Jobs -->
        <div class="sarkari-panel" id="sarkari-jobs" style="border-top: 4px solid #3b82f6;">
            <div class="sarkari-panel-header" style="color: #3b82f6;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                Latest Jobs
            </div>
            <ul class="sarkari-list">
                @forelse($recentJobs as $job)
                    <li class="sarkari-item">
                        <a href="#" class="sarkari-item-link btn-view" data-slug="{{ $job->slug }}">
                            &raquo; {{ $job->title }}
                        </a>
                        <span class="new-badge">NEW</span>
                    </li>
                @empty
                    <li style="padding: 1rem; text-align: center; color: var(--text-secondary); font-size: 0.85rem;">No active recruitments listed.</li>
                @endforelse
            </ul>
        </div>

        <!-- Panel 2: Admit Cards -->
        <div class="sarkari-panel" id="sarkari-admit-cards" style="border-top: 4px solid #10b981;">
            <div class="sarkari-panel-header" style="color: #10b981;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
                Admit Cards
            </div>
            <ul class="sarkari-list">
                @forelse($admitCards as $card)
                    <li class="sarkari-item">
                        <a href="#" class="sarkari-item-link btn-view" data-slug="{{ $card->slug }}">
                            &raquo; {{ $card->title }}
                        </a>
                        <span class="new-badge">NEW</span>
                    </li>
                @empty
                    <li style="padding: 1rem; text-align: center; color: var(--text-secondary); font-size: 0.85rem;">No active admit cards released.</li>
                @endforelse
            </ul>
        </div>

        <!-- Panel 3: Exam Results -->
        <div class="sarkari-panel" id="sarkari-results" style="border-top: 4px solid #8b5cf6;">
            <div class="sarkari-panel-header" style="color: #8b5cf6;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138z"></path></svg>
                Exam Results
            </div>
            <ul class="sarkari-list">
                @forelse($results as $res)
                    <li class="sarkari-item">
                        <a href="#" class="sarkari-item-link btn-view" data-slug="{{ $res->slug }}">
                            &raquo; {{ $res->title }}
                        </a>
                        <span class="new-badge">NEW</span>
                    </li>
                @empty
                    <li style="padding: 1rem; text-align: center; color: var(--text-secondary); font-size: 0.85rem;">No active results declared yet.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <!-- 6. Sarkari Grid Row 2 (Answer Keys, Syllabus, Notices) -->
    <div class="sarkari-panels-container">
        <!-- Panel 4: Answer Keys -->
        <div class="sarkari-panel" id="sarkari-answer-keys" style="border-top: 4px solid #f59e0b;">
            <div class="sarkari-panel-header" style="color: #f59e0b;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M15 7a2 2 0 012 2m-2 4a2 2 0 012 2m-2 4a2 2 0 012 2M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                Answer Keys
            </div>
            <ul class="sarkari-list">
                @forelse($answerKeys as $key)
                    <li class="sarkari-item">
                        <a href="#" class="sarkari-item-link btn-view" data-slug="{{ $key->slug }}">
                            &raquo; {{ $key->title }}
                        </a>
                        <span class="new-badge">NEW</span>
                    </li>
                @empty
                    <li style="padding: 1rem; text-align: center; color: var(--text-secondary); font-size: 0.85rem;">No official answer keys released.</li>
                @endforelse
            </ul>
        </div>

        <!-- Panel 5: Exam Syllabus -->
        <div class="sarkari-panel" id="sarkari-syllabus" style="border-top: 4px solid #ec4899;">
            <div class="sarkari-panel-header" style="color: #ec4899;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                Syllabus & Exams
            </div>
            <ul class="sarkari-list">
                @forelse($syllabi as $syllabus)
                    <li class="sarkari-item">
                        <a href="#" class="sarkari-item-link btn-view" data-slug="{{ $syllabus->slug }}">
                            &raquo; {{ $syllabus->title }}
                        </a>
                        <span class="new-badge">NEW</span>
                    </li>
                @empty
                    <li style="padding: 1rem; text-align: center; color: var(--text-secondary); font-size: 0.85rem;">No new syllabus structures out.</li>
                @endforelse
            </ul>
        </div>

        <!-- Panel 6: Important Notices -->
        <div class="sarkari-panel" id="sarkari-notices" style="border-top: 4px solid #ef4444;">
            <div class="sarkari-panel-header" style="color: #ef4444;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                Important Notices
            </div>
            <ul class="sarkari-list">
                @forelse($notices as $notice)
                    <li class="sarkari-item">
                        <a href="#" class="sarkari-item-link btn-view" data-slug="{{ $notice->slug }}">
                            &raquo; {{ $notice->title }}
                        </a>
                        <span class="new-badge">NEW</span>
                    </li>
                @empty
                    <li style="padding: 1rem; text-align: center; color: var(--text-secondary); font-size: 0.85rem;">No important circular notices active.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <!-- 7. Sarkari Grid Row 3 (Admissions, Scholarships) -->
    <div class="sarkari-panels-container" style="grid-template-columns: repeat(2, 1fr);">
        <!-- Panel 7: Admissions -->
        <div class="sarkari-panel" id="sarkari-admissions" style="border-top: 4px solid #06b6d4;">
            <div class="sarkari-panel-header" style="color: #06b6d4;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 14l9-5-9-5-9 5 9 5z"></path><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path></svg>
                Admissions Hub
            </div>
            <ul class="sarkari-list">
                @forelse($admissions as $adm)
                    <li class="sarkari-item">
                        <a href="#" class="sarkari-item-link btn-view" data-slug="{{ $adm->slug }}">
                            &raquo; {{ $adm->title }}
                        </a>
                        <span class="new-badge">NEW</span>
                    </li>
                @empty
                    <li style="padding: 1rem; text-align: center; color: var(--text-secondary); font-size: 0.85rem;">No active entrance exam admission notices.</li>
                @endforelse
            </ul>
        </div>

        <!-- Panel 8: Scholarships -->
        <div class="sarkari-panel" id="sarkari-scholarships" style="border-top: 4px solid #f97316;">
            <div class="sarkari-panel-header" style="color: #f97316;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Scholarships & Grants
            </div>
            <ul class="sarkari-list">
                @forelse($scholarships as $scho)
                    <li class="sarkari-item">
                        <a href="#" class="sarkari-item-link btn-view" data-slug="{{ $scho->slug }}">
                            &raquo; {{ $scho->title }}
                        </a>
                        <span class="new-badge">NEW</span>
                    </li>
                @empty
                    <li style="padding: 1rem; text-align: center; color: var(--text-secondary); font-size: 0.85rem;">No active scholarship schemes posted.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <!-- State & Qualification Explorer Grids -->
    <div class="explorer-deck-title">
        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="color: var(--accent-color);"><path d="M12 2a8 8 0 00-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 00-8-8z"></path><circle cx="12" cy="10" r="3"></circle></svg>
        Explore by State / Region
    </div>
    <div class="explorer-chips-container" id="stateExplorerChips">
        <div class="explorer-chip active" data-id="">
            🌐 All Regions
        </div>
        @foreach($states as $state)
            <div class="explorer-chip" data-id="{{ $state->id }}">
                📍 {{ $state->name }}
            </div>
        @endforeach
    </div>

    <div class="explorer-deck-title" style="margin-top: 1rem;">
        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="color: #10b981;"><path d="M12 14l9-5-9-5-9 5 9 5z"></path><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path></svg>
        Explore by Qualification
    </div>
    <div class="explorer-chips-container" id="qualExplorerChips">
        <div class="explorer-chip active" data-id="">
            🎓 All Qualifications
        </div>
        @foreach($qualifications as $qual)
            <div class="explorer-chip" data-id="{{ $qual->id }}">
                📚 {{ $qual->name }}
            </div>
        @endforeach
    </div>

    <!-- 8. Interactive Search Filters Panel (original Search Compass) -->
    <div class="main-grid" style="margin-bottom: 0px; padding-bottom: 0px;" id="interactive-finder">
        <!-- Typo Correction Banner -->
        <div id="homeTypoBanner" style="display: none; grid-column: 1 / -1; margin-bottom: 1rem;"></div>

        <div class="glass-panel search-compass" style="border-left: 4px solid var(--accent-color); overflow: visible;">
            <div style="position: relative;">
                <input type="text" id="searchKeywords" placeholder="Search keywords (e.g. UPSC, RBI officer)..." autocomplete="off">
                <div class="autocomplete-dropdown" id="autocompleteDropdown" style="position: absolute; top: 100%; left: 0; right: 0; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 12px; margin-top: 0.5rem; max-height: 350px; overflow-y: auto; z-index: 1000; box-shadow: 0 15px 35px -5px rgba(0,0,0,0.25); display: none; text-align: left; backdrop-filter: blur(14px);"></div>
            </div>
            <div>
                <select id="stateSelect">
                    <option value="">Select Region/State</option>
                    @foreach($states as $state)
                        <option value="{{ $state->id }}">{{ $state->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select id="qualificationSelect">
                    <option value="">Select Qualification</option>
                    @foreach($qualifications as $qual)
                        <option value="{{ $qual->id }}">{{ $qual->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select id="categorySelect">
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Main Workspace Split Grid -->
    <div class="main-grid">
        <!-- LEFT SIDE: Dynamic Jobs List Feed (70%) -->
        <div class="jobs-feed-column">
            
            <!-- Featured announcements segment -->
            <div id="featuredSegment" style="margin-bottom: 2.5rem;">
                <h2 style="font-size: 1.4rem; margin-bottom: 1.2rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span style="display:inline-block; width:8px; height:20px; background:var(--accent-color); border-radius:4px;"></span>
                    Premium Featured Announcements
                </h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.2rem;">
                    @forelse($featuredJobs as $fJob)
                        <div class="glass-panel job-card" style="display:block; border-left: 4px solid var(--accent-color); margin-bottom: 0;">
                            <div class="job-info">
                                <span class="badge" style="margin-bottom: 0.5rem; display: inline-block;">FEATURED</span>
                                <h3 style="font-size: 1.1rem; margin-bottom: 0.4rem;">{{ $fJob->title }}</h3>
                                <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.8rem;">
                                    {{ $fJob->department->name ?? 'Government' }} &bull; {{ $fJob->state->name ?? 'Pan India' }}
                                </p>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span class="badge badge-deadline">Apply by {{ $fJob->last_date_to_apply ? $fJob->last_date_to_apply->format('d M') : 'N/A' }}</span>
                                <a href="#" class="btn-view" data-slug="{{ $fJob->slug }}">Details</a>
                            </div>
                        </div>
                    @empty
                        <div class="glass-panel" style="grid-column: 1/-1; padding: 2rem; text-align: center; color: var(--text-secondary);">
                            No featured announcements active at this moment.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Latest active postings section -->
            <div id="latest-jobs">
                <h2 style="font-size: 1.4rem; margin-bottom: 1.2rem; display: flex; align-items: center; justify-content: space-between;">
                    <span style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="display:inline-block; width:8px; height:20px; background:#10b981; border-radius:4px;"></span>
                        Latest Active Recruitments
                    </span>
                    <span id="jobsCountFeedback" style="font-size: 0.85rem; color: var(--text-secondary); font-weight: normal;"></span>
                </h2>

                <!-- Skeleton Placeholders -->
                <div id="skeletonLoader" style="display: none;">
                    <div class="skeleton-job"></div>
                    <div class="skeleton-job"></div>
                    <div class="skeleton-job"></div>
                </div>

                <!-- Dynamic Jobs container -->
                <div id="jobsListContainer">
                    @forelse($recentJobs as $rJob)
                        @php
                            $applyTarget = $rJob->affiliate_link ? route('monetization.affiliate_redirect', ['slug' => $rJob->slug]) : '#';
                        @endphp
                        <div class="glass-panel job-card {{ $rJob->is_sponsored ? 'is-sponsored' : '' }} {{ $rJob->is_featured ? 'featured-premium' : '' }}">
                            <div class="job-info">
                                <h3 style="display:flex; align-items:center; gap:0.5rem;">
                                    {{ $rJob->title }}
                                    @if($rJob->is_sponsored)
                                        <span class="badge badge-sponsored">SPONSORED</span>
                                    @elseif($rJob->is_featured)
                                        <span class="badge" style="background:var(--accent-color); color:#fff; font-size:0.75rem;">FEATURED</span>
                                    @endif
                                </h3>
                                <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.5rem;">
                                    {{ $rJob->department->name ?? 'Government' }} &bull; {{ $rJob->state->name ?? 'Pan India' }}
                                </p>
                                <div class="job-tags">
                                    <span class="badge badge-dept">{{ $rJob->qualification->name ?? 'Degree Required' }}</span>
                                    <span class="badge" style="background: rgba(16, 185, 129, 0.08); color: #10b981;">Vacancies: {{ $rJob->vacancy_count }}</span>
                                    <span class="badge badge-deadline">Apply by {{ $rJob->last_date_to_apply ? $rJob->last_date_to_apply->format('d M Y') : 'N/A' }}</span>
                                </div>
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 0.5rem; align-items: flex-end;">
                                <a href="{{ $applyTarget }}" class="btn-view" data-slug="{{ $rJob->slug }}">View Details</a>
                                @auth
                                    <button class="btn-sm-danger toggle-bookmark-btn" data-id="{{ $rJob->id }}" style="background: rgba(37,99,235,0.06); color: var(--accent-color); border-color: rgba(37,99,235,0.15);">
                                        Save Job
                                    </button>
                                @endauth
                            </div>
                        </div>
                    @empty
                        <div class="glass-panel" style="padding: 3rem; text-align: center; color: var(--text-secondary);">
                            No recruitment posts active. Check back later!
                        </div>
                    @endforelse
                </div>

                <!-- Dynamic AJAX Pagination container -->
                <div class="pagination-container" id="paginationContainer"></div>
            </div>
        </div>

        <!-- RIGHT SIDE: Utilities Sidebar Panel -->
        <div class="utilities-column">
            <!-- Admit Cards / Syllabus Widget -->
            <div class="glass-panel sidebar-panel" id="admit-cards">
                <div class="tab-headers">
                    <button class="tab-btn active" data-tab="admitCards">Admit Cards</button>
                    <button class="tab-btn" data-tab="examResults">Results</button>
                    <button class="tab-btn" data-tab="syllabi">Syllabus</button>
                </div>
                <div class="tab-content active" id="admitCards">
                    <ul class="tab-list">
                        <li class="tab-item"><a href="#">&rarr; UPSC Civil Services (IAS) 2026 Admit Card</a></li>
                        <li class="tab-item"><a href="#">&rarr; SSC CGL Tier 1 Entry Card</a></li>
                        <li class="tab-item"><a href="#">&rarr; RBI Officer Grade B Exam Schedule</a></li>
                        <li class="tab-item"><a href="#">&rarr; SBI Probationary Officer Exam Hall Ticket</a></li>
                    </ul>
                </div>
                <div class="tab-content" id="examResults">
                    <ul class="tab-list">
                        <li class="tab-item"><a href="#" style="font-weight: 500; color: #10b981;">&check; UPSC IFS Final Selection List 2025</a></li>
                        <li class="tab-item"><a href="#">&check; Railway NTPC CBT 2 Merit List</a></li>
                        <li class="tab-item"><a href="#">&check; IBPS Specialist Officer Mains Result</a></li>
                    </ul>
                </div>
                <div class="tab-content" id="syllabi">
                    <ul class="tab-list">
                        <li class="tab-item"><a href="#">&bull; UPSC IAS Complete Pattern (Prelims & Mains)</a></li>
                        <li class="tab-item"><a href="#">&bull; SSC CGL Tier 1 & Tier 2 Math Syllabus</a></li>
                        <li class="tab-item"><a href="#">&bull; RBI Grade B Phase 1 Syllabus Pattern</a></li>
                    </ul>
                </div>
            </div>

            <!-- Automation Status panel -->
            <div class="glass-panel sidebar-panel" style="border-left: 4px solid #10b981;">
                <h3 style="font-size: 1.1rem; margin-bottom: 0.8rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span style="display:inline-block; width:8px; height:8px; background:#10b981; border-radius:50%; animation: pulse 1s infinite;"></span>
                    Automation Monitor
                </h3>
                <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.8rem;">
                    Our intelligent scraping pipeline parses government portals every 5 minutes, validates parameters deterministically, and isolates errors in quarantine.
                </p>
                <div style="font-size: 0.8rem; background: var(--bg-primary); padding: 0.6rem; border-radius: 6px; border: 1px solid var(--border-color);">
                    <strong>Status:</strong> Active &bull; <strong>System Mode:</strong> Failsafe
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TAB 2: PORTAL INFORMATION HUB (NEW TAB) -->
<div class="portal-main-tab" id="info-hub-section" style="display: none; padding: 0 5%; max-width: 1400px; margin: 0 auto;">
    <h2 style="font-size: 1.8rem; margin-bottom: 1.5rem; font-family: 'Outfit';">Portal Information & Help Center</h2>
    
    <div class="sub-tab-headers">
        <button class="sub-tab-btn active" data-sub="info-blog">Blog & News</button>
        <button class="sub-tab-btn" data-sub="info-timeline">About Portal Timeline</button>
        <button class="sub-tab-btn" data-sub="info-faq">Frequently Asked Questions</button>
        <button class="sub-tab-btn" data-sub="info-contact">Contact Helpdesk</button>
    </div>

    <!-- A. Blog Sub-tab -->
    <div class="sub-tab-content active-sub" id="info-blog">
        <div class="blog-feed-grid">
            <div class="blog-card glass-panel">
                <div class="blog-image-wrapper">UPSC 2026</div>
                <div class="blog-body">
                    <span class="blog-tag">Recruitment News</span>
                    <h3 style="font-size: 1.1rem; margin-bottom: 0.5rem; font-family: 'Outfit';">UPSC Civil Services 2026 Notification Out!</h3>
                    <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1rem; flex-grow: 1;">
                        The Union Public Service Commission has officially announced the vacancies count and cutoff criteria for the IAS/IFS preliminary examinations.
                    </p>
                    <div style="font-size: 0.8rem; color: var(--text-secondary); display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 0.75rem;">
                        <span>Released: Today</span>
                        <a href="#" class="btn-view-sm" style="text-decoration: none; color: var(--accent-color); font-weight: 600;">Read More &rarr;</a>
                    </div>
                </div>
            </div>
            <div class="blog-card glass-panel">
                <div class="blog-image-wrapper" style="background: linear-gradient(135deg, #10b981, #059669);">SSC CGL</div>
                <div class="blog-body">
                    <span class="blog-tag" style="background:rgba(16,185,129,0.08); color:#10b981;">Admit Card Updates</span>
                    <h3 style="font-size: 1.1rem; margin-bottom: 0.5rem; font-family: 'Outfit';">SSC CGL Tier 1 Hall Ticket Release Dates</h3>
                    <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1rem; flex-grow: 1;">
                        Candidates who submitted application forms can download active entry cards starting this Friday by entering their unique birth records.
                    </p>
                    <div style="font-size: 0.8rem; color: var(--text-secondary); display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 0.75rem;">
                        <span>Released: Yesterday</span>
                        <a href="#" class="btn-view-sm" style="text-decoration: none; color: #10b981; font-weight: 600;">Read More &rarr;</a>
                    </div>
                </div>
            </div>
            <div class="blog-card glass-panel">
                <div class="blog-image-wrapper" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">RAILWAYS</div>
                <div class="blog-body">
                    <span class="blog-tag" style="background:rgba(139,92,246,0.08); color:#8b5cf6;">Syllabus Releases</span>
                    <h3 style="font-size: 1.1rem; margin-bottom: 0.5rem; font-family: 'Outfit';">Railway Recruitment Board Syllabus Overhaul</h3>
                    <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1rem; flex-grow: 1;">
                        The selection committee revised general aptitude and science parameters for technical examinations. Read complete subject breakdowns here.
                    </p>
                    <div style="font-size: 0.8rem; color: var(--text-secondary); display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 0.75rem;">
                        <span>Released: 2 days ago</span>
                        <a href="#" class="btn-view-sm" style="text-decoration: none; color: #8b5cf6; font-weight: 600;">Read More &rarr;</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- B. About Us Timeline Sub-tab -->
    <div class="sub-tab-content" id="info-timeline" style="display: none;">
        <div class="glass-panel" style="padding: 1.75rem;">
            <h3 style="font-family:'Outfit'; margin-bottom: 0.5rem; color: var(--accent-color);">Portal Design & Low-Temperature Scraping Pipeline</h3>
            <p style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 1.5rem;">
                GovJobs is engineered with clean PHP Laravel MVC + Service-Repository architecture, keeping API requests blazing-fast and highly secure.
            </p>
            <div class="timeline-flow">
                <div class="timeline-step">
                    <div class="timeline-title">Stage 1: Multi-Feed Target Web Scraper</div>
                    <div class="timeline-desc">Intelligent crawler engines fetch recruitment notifications directly from official portals asynchronously via Background Queues.</div>
                </div>
                <div class="timeline-step">
                    <div class="timeline-title">Stage 2: Deterministic Pre-Parser Validation</div>
                    <div class="timeline-desc">Strict regex filters extract qualification codes, vacancies, cutoff ages, application fees, and deadlines. Matches with incomplete fields are quarantined.</div>
                </div>
                <div class="timeline-step">
                    <div class="timeline-title">Stage 3: Quarantine Override & Live Publish</div>
                    <div class="timeline-desc">Administrators review isolated postings, make corrections with a single click, and synchronize them live into public job directories instantly!</div>
                </div>
            </div>
        </div>
    </div>

    <!-- C. Frequently Asked Questions (Accordion FAQ) Sub-tab -->
    <div class="sub-tab-content" id="info-faq" style="display: none;">
        <div class="glass-panel" style="padding: 1.75rem;">
            <h3 style="font-family:'Outfit'; color: var(--accent-color); margin-bottom: 0.5rem;">Frequently Asked Questions</h3>
            <p style="font-size: 0.9rem; color: var(--text-secondary);">Expand options below to understand GovJobs verification engines and registration policies.</p>
            
            <div class="accordion-wrapper">
                <div class="accordion-item">
                    <div class="accordion-header">Are all listed government job alerts verified?</div>
                    <div class="accordion-content">
                        Yes! Every announcement in our portal is scraped directly from authentic government domain resources (.gov.in / .nic.in) and cross-validated before listing.
                    </div>
                </div>
                <div class="accordion-item">
                    <div class="accordion-header">How does the mock OTP verification system work?</div>
                    <div class="accordion-content">
                        To recover your candidate account, click the 'Reset PW' tab in the authentication modal, input your email, and receive a simulated SMS code '123456' immediately to restore session rights.
                    </div>
                </div>
                <div class="accordion-item">
                    <div class="accordion-header">How can candidates update their alert preferences?</div>
                    <div class="accordion-content">
                        Candidates can sign in, open the 'Dashboard' section, go to the 'Profile Settings' tab, and toggle Email or SMS notifications checkbox configurations in real-time.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- D. Contact Helpdesk Sub-tab -->
    <div class="sub-tab-content" id="info-contact" style="display: none;">
        <div class="glass-panel" style="padding: 1.75rem; max-width: 600px; margin: 0 auto;">
            <h3 style="font-family:'Outfit'; color: var(--accent-color); margin-bottom: 0.5rem; text-align: center;">Contact Portal Support Helpdesk</h3>
            <p style="font-size: 0.85rem; color: var(--text-secondary); text-align: center; margin-bottom: 1.5rem;">
                Have questions or spot a typo on a scraped recruitment feed? Send us a ticket.
            </p>
            <form id="ajaxContactForm">
                @csrf
                <div class="form-group">
                    <label for="contactName">Your Name</label>
                    <input type="text" name="name" id="contactName" class="form-control" placeholder="Candidate Name" required>
                </div>
                <div class="form-group">
                    <label for="contactEmail">Email Address</label>
                    <input type="email" name="email" id="contactEmail" class="form-control" placeholder="candidate@example.com" required>
                </div>
                <div class="form-group">
                    <label for="contactMessage">Support Message / Feedback</label>
                    <textarea name="message" id="contactMessage" class="form-control" rows="4" placeholder="Briefly describe your request..." required></textarea>
                </div>
                <button type="submit" class="form-btn" id="contactSubmitBtn">Submit Support Ticket</button>
            </form>
        </div>
    </div>
</div>

<!-- ======================= AUTH TAB PANELS (LOADED DYNAMICALLY) ======================= -->

<!-- TAB 3: CANDIDATE INTERACTIVE DASHBOARD -->
<div class="portal-main-tab" id="dashboard-section" style="display: none; padding: 0 5%; max-width: 1400px; margin: 0 auto;">
    <h2 style="font-size: 1.8rem; margin-bottom: 1.5rem; font-family: 'Outfit';">Candidate Interactive Dashboard</h2>
    
    <div class="sub-tab-headers" style="margin-bottom: 1.5rem;">
        <button class="sub-tab-btn active dash-sub-trigger" data-target="dash-overview-block">Workspace Overview</button>
        <button class="sub-tab-btn dash-sub-trigger" data-target="dash-settings-block">Profile & Match Alerts Preferences</button>
        <button class="sub-tab-btn dash-sub-trigger" data-target="dash-membership-block" id="dashMembershipTabTrigger">Premium Membership Plans</button>
    </div>

    <!-- Dash Block 1: Overview (Bookmarks and apps table) -->
    <div id="dash-overview-block" class="dash-block-panel">
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; align-items: start;">
            <div>
                <!-- Bookmarked items box -->
                <div class="glass-panel" style="padding: 1.5rem; margin-bottom: 2rem;">
                    <h3 style="font-size: 1.25rem; margin-bottom: 1rem; color: var(--accent-color); font-family: 'Outfit';">Saved Recruitment Postings</h3>
                    <div class="responsive-table-container">
                        <table class="portal-table" id="dashboardBookmarksTable">
                            <thead>
                                <tr>
                                    <th>Job Title</th>
                                    <th>Region</th>
                                    <th>Apply Deadline</th>
                                    <th style="text-align: center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Job Applications box -->
                <div class="glass-panel" style="padding: 1.5rem;">
                    <h3 style="font-size: 1.25rem; margin-bottom: 1rem; color: #10b981; font-family: 'Outfit';">Submitted Applications & Recruiter Status</h3>
                    <div class="responsive-table-container">
                        <table class="portal-table" id="dashboardApplicationsTable">
                            <thead>
                                <tr>
                                    <th>Recruitment Title</th>
                                    <th>Organization</th>
                                    <th>Date Submitted</th>
                                    <th>Process State</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Candidate statistics card -->
            <div class="glass-panel" style="padding: 1.5rem;">
                <h3 style="font-size: 1.25rem; margin-bottom: 1.5rem; font-family: 'Outfit';">Profile Statistics</h3>
                
                <div class="stats-grid" style="grid-template-columns: 1fr; gap: 1rem;">
                    <div class="glass-panel stat-card" style="background: var(--bg-primary); border: 1px solid var(--border-color);">
                        <div class="stat-num" id="statsTotalBookmarks">0</div>
                        <div class="stat-label">Saved Recruitments</div>
                    </div>
                    <div class="glass-panel stat-card" style="background: var(--bg-primary); border: 1px solid var(--border-color);">
                        <div class="stat-num" id="statsTotalApplications" style="color: #10b981;">0</div>
                        <div class="stat-label">Submitted Applications</div>
                    </div>
                </div>

                <div style="border-top: 1px solid var(--border-color); padding-top: 1.25rem; margin-top: 1.25rem; font-size: 0.9rem; color: var(--text-secondary);">
                    <p><strong>Candidate:</strong> <span id="dashCandidateName" style="color: var(--text-primary);">John Doe</span></p>
                    <p><strong>Email:</strong> <span id="dashCandidateEmail">candidate@example.com</span></p>
                    <p><strong>Phone:</strong> <span id="dashCandidatePhone">Not Verified</span></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Dash Block 2: Profile Settings Form -->
    <div id="dash-settings-block" class="dash-block-panel" style="display: none;">
        <div class="glass-panel" style="padding: 2rem; max-width: 700px; margin: 0 auto;">
            <h3 style="font-family:'Outfit'; color: var(--accent-color); margin-bottom: 1.5rem; text-align: center;">Update Profile Settings & Preferences</h3>
            
            <form id="ajaxProfileUpdateForm" style="margin-bottom: 2rem; border-bottom: 1px solid var(--border-color); padding-bottom: 2rem;">
                @csrf
                <div class="form-group">
                    <label for="profileName">Full Name</label>
                    <input type="text" name="name" id="profileName" class="form-control" required>
                    <div class="invalid-feedback" id="profileNameError"></div>
                </div>
                <div class="form-group">
                    <label for="profileEmail">Email Address</label>
                    <input type="email" name="email" id="profileEmail" class="form-control" required>
                    <div class="invalid-feedback" id="profileEmailError"></div>
                </div>
                <div class="form-group">
                    <label for="profilePhone">Phone Number</label>
                    <input type="text" name="phone" id="profilePhone" class="form-control" required>
                    <div class="invalid-feedback" id="profilePhoneError"></div>
                </div>
                
                <div style="background: rgba(37,99,235,0.03); padding: 1rem; border-radius: 8px; border: 1px dashed var(--border-color); margin: 1.5rem 0;">
                    <p style="font-size:0.8rem; color:var(--text-secondary); margin-bottom:1rem;">Leave password fields blank if you do not want to alter credentials.</p>
                    <div class="form-group">
                        <label for="profilePassword">New Password (Min 6 chars)</label>
                        <input type="password" name="password" id="profilePassword" class="form-control" placeholder="••••••••">
                        <div class="invalid-feedback" id="profilePasswordError"></div>
                    </div>
                    <div class="form-group">
                        <label for="profilePasswordConfirm">Confirm New Password</label>
                        <input type="password" name="password_confirmation" id="profilePasswordConfirm" class="form-control" placeholder="••••••••">
                    </div>
                </div>

                <button type="submit" class="form-btn" id="profileUpdateSubmitBtn">Synchronize Profile Settings</button>
            </form>

            <form id="ajaxPreferencesForm">
                @csrf
                <h4 style="font-family:'Outfit'; font-size:1.1rem; color:var(--text-primary); margin-bottom:1rem;">Real-time Recruitment Alert Channels</h4>
                
                <div class="alert-preference-row">
                    <div>
                        <strong>Email Match Notifications</strong><br>
                        <span style="font-size:0.8rem; color:var(--text-secondary);">Receive validation notifications daily on active categories.</span>
                    </div>
                    <input type="checkbox" name="email_alerts" id="prefEmailAlerts" value="1" checked style="width: 20px; height: 20px; cursor: pointer;">
                </div>
                
                <div class="alert-preference-row" style="border-bottom:none; margin-bottom: 1.5rem;">
                    <div>
                        <strong>SMS Verification Alerts</strong><br>
                        <span style="font-size:0.8rem; color:var(--text-secondary);">Send live SMS reminders 24 hours prior to apply deadlines.</span>
                    </div>
                    <input type="checkbox" name="sms_alerts" id="prefSmsAlerts" value="1" checked style="width: 20px; height: 20px; cursor: pointer;">
                </div>

                <button type="submit" class="form-btn" id="preferencesSubmitBtn" style="background:#10b981;">Save Notification Preferences</button>
            </form>
        </div>
    </div>

    <!-- Dash Block 3: Membership Plans & Upgrades -->
    <div id="dash-membership-block" class="dash-block-panel" style="display: none;">
        <div class="glass-panel" style="padding: 2rem; max-width: 800px; margin: 0 auto;">
            <h3 style="font-family:'Outfit'; color: var(--accent-color); margin-bottom: 0.5rem; text-align: center;">Premium Membership Plans</h3>
            <p style="font-size:0.9rem; color:var(--text-secondary); text-align:center; margin-bottom:2rem;">
                Unlock advanced automation alerts, early results access, and a completely <strong>ad-free experience</strong>.
            </p>

            <div style="display: flex; gap: 1rem; margin-bottom: 2rem; align-items: stretch; justify-content: center; flex-wrap: wrap;">
                <!-- Plan 1: Free -->
                <div class="glass-panel" style="flex: 1; min-width: 220px; padding: 1.5rem; display: flex; flex-direction: column; justify-content: space-between; border-top: 4px solid var(--text-secondary);">
                    <div>
                        <h4 style="font-family:'Outfit'; font-size:1.15rem; margin-bottom:0.5rem;">Basic Free Plan</h4>
                        <div style="font-size:1.5rem; font-weight:800; margin-bottom:1rem; color:var(--text-primary);">₹0</div>
                        <ul style="list-style:none; padding:0; margin:0; display:grid; gap:0.5rem; font-size:0.82rem; color:var(--text-secondary);">
                            <li>✓ Standard job notifications</li>
                            <li>✓ Web extraction portal access</li>
                            <li>✗ Sponsored advertisements</li>
                            <li>✗ Advanced SMS alerts</li>
                        </ul>
                    </div>
                    <div style="margin-top:1.5rem;">
                        <button class="btn-view" style="width:100%; text-align:center; cursor:default;" id="btnFreePlanIndicator" disabled>Active Plan</button>
                    </div>
                </div>

                <!-- Plan 2: Premium -->
                <div class="glass-panel" style="flex: 1; min-width: 220px; padding: 1.5rem; display: flex; flex-direction: column; justify-content: space-between; border-top: 4px solid var(--accent-color); background: rgba(37,99,235,0.02);">
                    <div>
                        <h4 style="font-family:'Outfit'; font-size:1.15rem; margin-bottom:0.5rem;">Premium Candidate</h4>
                        <div style="font-size:1.5rem; font-weight:800; margin-bottom:1rem; color:var(--accent-color);">₹299 <span style="font-size:0.8rem; font-weight:normal;">/ month</span></div>
                        <ul style="list-style:none; padding:0; margin:0; display:grid; gap:0.5rem; font-size:0.82rem; color:var(--text-secondary);">
                            <li><strong>✓ Completely Ad-Free Experience</strong></li>
                            <li>✓ Instant WhatsApp/SMS alerts</li>
                            <li>✓ Early Access to Exam Results</li>
                            <li>✓ Automated study guide matching</li>
                        </ul>
                    </div>
                    <div style="margin-top:1.5rem;">
                        <button class="form-btn select-membership-plan-btn" data-plan="premium" id="btnPremiumPlanIndicator" style="width:100%; margin:0; padding:0.6rem;">Upgrade Premium</button>
                    </div>
                </div>

                <!-- Plan 3: Pro -->
                <div class="glass-panel" style="flex: 1; min-width: 220px; padding: 1.5rem; display: flex; flex-direction: column; justify-content: space-between; border-top: 4px solid #10b981; background: rgba(16,185,129,0.02);">
                    <div>
                        <h4 style="font-family:'Outfit'; font-size:1.15rem; margin-bottom:0.5rem;">Pro Professional</h4>
                        <div style="font-size:1.5rem; font-weight:800; margin-bottom:1rem; color:#10b981;">₹599 <span style="font-size:0.8rem; font-weight:normal;">/ month</span></div>
                        <ul style="list-style:none; padding:0; margin:0; display:grid; gap:0.5rem; font-size:0.82rem; color:var(--text-secondary);">
                            <li><strong>✓ Completely Ad-Free Experience</strong></li>
                            <li>✓ Priority SMS and Call reminders</li>
                            <li>✓ Access to premium Test Series</li>
                            <li>✓ Downloadable PDF Syllabus guides</li>
                        </ul>
                    </div>
                    <div style="margin-top:1.5rem;">
                        <button class="form-btn select-membership-plan-btn" data-plan="pro" id="btnProPlanIndicator" style="width:100%; margin:0; padding:0.6rem; background:#10b981;">Upgrade Pro</button>
                    </div>
                </div>
            </div>

            <!-- UPI/Credit Card Simulated Payment Panel (hidden by default) -->
            <div id="simulatedPaymentPanel" style="display: none; border-top: 1px solid var(--border-color); padding-top: 1.5rem; margin-top: 1.5rem;">
                <h4 style="font-family:'Outfit'; font-size:1.1rem; color:var(--accent-color); margin-bottom:1rem; text-align:center;">Secure Mock Checkout Interface</h4>
                <form id="ajaxSimulatedCheckoutForm" style="max-width: 450px; margin: 0 auto;">
                    @csrf
                    <input type="hidden" id="checkoutTargetPlan" name="plan">
                    
                    <div class="form-group">
                        <label for="mockPaymentMethod">Payment Gateway Mode</label>
                        <select id="mockPaymentMethod" class="form-control" style="background: var(--bg-primary);">
                            <option value="upi">Instant UPI (Paytm/PhonePe/GPay)</option>
                            <option value="card">Visa / Mastercard Credit Card</option>
                        </select>
                    </div>

                    <!-- UPI ID field -->
                    <div class="form-group" id="upiFieldBlock">
                        <label for="mockUpiId">Enter UPI Address</label>
                        <input type="text" id="mockUpiId" class="form-control" placeholder="username@upi" value="candidate@oksbi">
                    </div>

                    <!-- Card details block -->
                    <div id="cardFieldBlock" style="display: none;">
                        <div class="form-group">
                            <label for="mockCardNumber">Card Number</label>
                            <input type="text" id="mockCardNumber" class="form-control" placeholder="4111 2222 3333 4444" value="4111222233334444">
                        </div>
                        <div style="display: flex; gap: 0.75rem;">
                            <div class="form-group" style="flex:1;">
                                <label for="mockCardExpiry">Expiry Date</label>
                                <input type="text" id="mockCardExpiry" class="form-control" placeholder="MM/YY" value="12/28">
                            </div>
                            <div class="form-group" style="flex:1;">
                                <label for="mockCardCvv">CVV Code</label>
                                <input type="password" id="mockCardCvv" class="form-control" placeholder="•••" value="123">
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="form-btn" id="paymentSubmitBtn">Authorize Secure Transaction</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- TAB 4: ADMIN SCRAPER SCHEDULE & OVERRIDES PANEL -->
<div class="portal-main-tab" id="admin-section" style="display: none; padding: 0 5%; max-width: 1400px; margin: 0 auto;">
    <h2 style="font-size: 1.8rem; margin-bottom: 1.5rem; font-family: 'Outfit';">Enterprise Automation Control Center</h2>

    <div class="sub-tab-headers" style="margin-bottom: 1.5rem;">
        <button class="sub-tab-btn active admin-sub-trigger" data-target="admin-crawlers-block">Web Crawler Monitors</button>
        <button class="sub-tab-btn admin-sub-trigger" data-target="admin-publisher-block">Manual Recruitment Publisher</button>
        <button class="sub-tab-btn admin-sub-trigger" data-target="admin-users-block">User Registry Elevations Board</button>
        <button class="sub-tab-btn admin-sub-trigger" data-target="admin-seo-block">SEO Caching Console</button>
        <button class="sub-tab-btn admin-sub-trigger" data-target="admin-revenue-block" id="adminRevenueTabTrigger">Monetization & Revenue Dashboard</button>
    </div>

    <!-- Admin Block 1: Crawler Monitors -->
    <div id="admin-crawlers-block" class="admin-block-panel">
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; align-items: start;">
            
            <!-- Scrapers list and live logs -->
            <div>
                <!-- Web Scraper Targets Table -->
                <div class="glass-panel" style="padding: 1.5rem; margin-bottom: 2rem;">
                    <h3 style="font-size: 1.25rem; margin-bottom: 1rem; color: var(--accent-color); font-family: 'Outfit';">Scraper Crawl Target Configurations</h3>
                    <div class="responsive-table-container">
                        <table class="portal-table" id="adminScrapersTable">
                            <thead>
                                <tr>
                                    <th>Source Feed Name</th>
                                    <th>Cron Schedule</th>
                                    <th style="text-align: center;">Crawl Trigger</th>
                                    <th style="text-align: center;">Active State</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Web Scraper Execution Audit logs -->
                <div class="glass-panel" style="padding: 1.5rem; margin-bottom: 2rem;">
                    <h3 style="font-size: 1.25rem; margin-bottom: 1rem; color: var(--text-primary); font-family: 'Outfit';">Scraper Dispatch Execution Audits</h3>
                    <div class="responsive-table-container">
                        <table class="portal-table" id="adminScraperLogsTable">
                            <thead>
                                <tr>
                                    <th>Feed Announcement</th>
                                    <th>State</th>
                                    <th>Items Gathered</th>
                                    <th>Diagnostics / Error</th>
                                    <th>Crawl Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- AUTO-QUARANTINE MANUAL OVERRIDE RESCUE PANEL -->
                <div class="glass-panel" style="padding: 1.5rem;">
                    <h3 style="font-size: 1.25rem; margin-bottom: 1rem; color: #f59e0b; font-family: 'Outfit';">Quarantined Scraped Listings (Manual Review Override Panel)</h3>
                    <div id="adminQuarantinedContainer">
                        <!-- Loaded dynamically via AJAX -->
                    </div>
                </div>
            </div>

            <!-- Scraper Statistics panel -->
            <div class="glass-panel" style="padding: 1.5rem;">
                <h3 style="font-size: 1.25rem; margin-bottom: 1.5rem; font-family: 'Outfit';">Automation System Health</h3>
                
                <div class="stats-grid" style="grid-template-columns: 1fr; gap: 1rem;">
                    <div class="glass-panel stat-card" style="background: var(--bg-primary); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.5rem;">
                        <div class="stat-label" style="text-align: left; font-size: 0.9rem;">Crawl Targets</div>
                        <div class="stat-num" id="metricsTotalSources" style="font-size: 1.6rem; margin: 0;">0</div>
                    </div>
                    <div class="glass-panel stat-card" style="background: var(--bg-primary); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.5rem;">
                        <div class="stat-label" style="text-align: left; font-size: 0.9rem;">Successful Runs</div>
                        <div class="stat-num" id="metricsSuccessRuns" style="color: #10b981; font-size: 1.6rem; margin: 0;">0</div>
                    </div>
                    <div class="glass-panel stat-card" style="background: var(--bg-primary); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.5rem;">
                        <div class="stat-label" style="text-align: left; font-size: 0.9rem;">Quarantined Records</div>
                        <div class="stat-num" id="metricsQuarantineRuns" style="color: #f59e0b; font-size: 1.6rem; margin: 0;">0</div>
                    </div>
                    <div class="glass-panel stat-card" style="background: var(--bg-primary); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.5rem;">
                        <div class="stat-label" style="text-align: left; font-size: 0.9rem;">Critical Failures</div>
                        <div class="stat-num" id="metricsFailedRuns" style="color: #ef4444; font-size: 1.6rem; margin: 0;">0</div>
                    </div>
                </div>

                <div style="border-top: 1px solid var(--border-color); padding-top: 1.25rem; margin-top: 1.25rem; font-size: 0.85rem; color: var(--text-secondary); text-align: center;">
                    <p>Enterprise Automation Failsafe: Enabled</p>
                    <p style="margin-top: 0.25rem;">Active Queue Worker: SQS / Sync Connection</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Block 2: Manual Job Publisher Form -->
    <div id="admin-publisher-block" class="admin-block-panel" style="display: none;">
        <div class="glass-panel" style="padding: 2rem; max-width: 800px; margin: 0 auto;">
            <h3 style="font-family:'Outfit'; color: var(--accent-color); margin-bottom: 0.5rem; text-align: center;">Publish Manual Job Announcement</h3>
            <p style="font-size:0.85rem; color:var(--text-secondary); text-align:center; margin-bottom:1.5rem;">Broadcast a verified recruitment opportunity directly into GovJobs directories instantly.</p>
            
            <form id="ajaxManualJobForm">
                @csrf
                <div class="form-group">
                    <label for="mjTitle">Recruitment Post Title</label>
                    <input type="text" name="title" id="mjTitle" class="form-control" placeholder="e.g. UPSC Assistant Commandant Recruitment 2026" required>
                    <div class="invalid-feedback" id="mjTitleError"></div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.25rem;">
                    <div class="form-group">
                        <label for="mjCategory">Job Category</label>
                        <select name="category_id" id="mjCategory" class="form-control" required>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="mjDepartment">Partner Organization / Department</label>
                        <select name="department_id" id="mjDepartment" class="form-control" required>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.25rem;">
                    <div class="form-group">
                        <label for="mjState">Region / State</label>
                        <select name="state_id" id="mjState" class="form-control" required>
                            @foreach($states as $st)
                                <option value="{{ $st->id }}">{{ $st->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="mjQualification">Minimum Qualification</label>
                        <select name="qualification_id" id="mjQualification" class="form-control" required>
                            @foreach($qualifications as $ql)
                                <option value="{{ $ql->id }}">{{ $ql->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="mjDescription">Recruitment Overview & Eligibility Details</label>
                    <textarea name="description" id="mjDescription" class="form-control" rows="5" placeholder="Provide clear specifications, age bar exemptions, screening tests outline..." required></textarea>
                    <div class="invalid-feedback" id="mjDescriptionError"></div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:1rem;">
                    <div class="form-group">
                        <label for="mjSalaryMin">Min Salary (Monthly ₹)</label>
                        <input type="number" name="salary_min" id="mjSalaryMin" class="form-control" value="35000" required>
                    </div>
                    <div class="form-group">
                        <label for="mjSalaryMax">Max Salary (Monthly ₹)</label>
                        <input type="number" name="salary_max" id="mjSalaryMax" class="form-control" value="112000" required>
                    </div>
                    <div class="form-group">
                        <label for="mjVacancies">Vacancies count</label>
                        <input type="number" name="vacancy_count" id="mjVacancies" class="form-control" value="10" required>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.25rem;">
                    <div class="form-group">
                        <label for="mjFee">Application Fees (₹)</label>
                        <input type="number" name="application_fee" id="mjFee" class="form-control" value="100" required>
                    </div>
                    <div class="form-group">
                        <label for="mjDeadline">Apply Deadline (Valid Date)</label>
                        <input type="date" name="last_date_to_apply" id="mjDeadline" class="form-control" required>
                        <div class="invalid-feedback" id="mjDeadlineError"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="mjOfficialLink">Official Recruitment Web Link</label>
                    <input type="url" name="official_website_link" id="mjOfficialLink" class="form-control" placeholder="https://upsc.gov.in" required>
                    <div class="invalid-feedback" id="mjOfficialLinkError"></div>
                </div>

                <button type="submit" class="form-btn" id="mjSubmitBtn">Publish Announcement Live</button>
            </form>
        </div>
    </div>

    <!-- Admin Block 3: User Registry Board Table -->
    <div id="admin-users-block" class="admin-block-panel" style="display: none;">
        <div class="glass-panel" style="padding: 1.5rem;">
            <h3 style="font-size: 1.25rem; margin-bottom: 1rem; color: var(--accent-color); font-family: 'Outfit';">User Access Registry & Security Clearances</h3>
            <p style="font-size:0.85rem; color:var(--text-secondary); margin-bottom:1.5rem;">Toggle candidate/administrator session roles or suspend/activate user profiles synchronously.</p>
            
            <div class="responsive-table-container">
                <table class="portal-table" id="adminUsersRegistryTable">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Candidate Name</th>
                            <th>Email Profile</th>
                            <th>Phone Contact</th>
                            <th>Access Role</th>
                            <th style="text-align: center;">Account Status</th>
                            <th style="text-align: center;">Elevations / Toggles</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Populated dynamically via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Admin Block 4: SEO Caching Console -->
    <div id="admin-seo-block" class="admin-block-panel" style="display: none;">
        <div class="glass-panel" style="padding: 2rem; max-width: 600px; margin: 0 auto;">
            <h3 style="font-family:'Outfit'; color: var(--accent-color); margin-bottom: 0.5rem; text-align: center;">SEO Meta Caching Console</h3>
            <p style="font-size:0.85rem; color:var(--text-secondary); text-align:center; margin-bottom:1.5rem;">Configure dynamic keywords and metadata dynamically cached in local JSON store configurations.</p>
            
            <form id="ajaxSeoSettingsForm">
                @csrf
                <div class="form-group">
                    <label for="seoTitle">Homepage Meta Title</label>
                    <input type="text" name="meta_title" id="seoTitle" class="form-control" value="GovJobs - Premium Automated Government Jobs Portal" required>
                    <div class="invalid-feedback" id="seoTitleError"></div>
                </div>
                <div class="form-group">
                    <label for="seoDescription">Meta Description</label>
                    <textarea name="meta_description" id="seoDescription" class="form-control" rows="4" required>Discover real-time, highly validated recruitment alerts verified by AI across UPSC, SSC, Banking, and Railways. Fast, mobile responsive, and fully automated.</textarea>
                    <div class="invalid-feedback" id="seoDescriptionError"></div>
                </div>
                <div class="form-group">
                    <label for="seoKeywords">Meta Keywords (Comma separated)</label>
                    <input type="text" name="meta_keywords" id="seoKeywords" class="form-control" value="government jobs, upsc, ssc, banking, railways, rrb, admit cards, results" required>
                    <div class="invalid-feedback" id="seoKeywordsError"></div>
                </div>
                <button type="submit" class="form-btn" id="seoSubmitBtn" style="background:#8b5cf6;">Synchronize Meta Tags Cache</button>
            </form>
        </div>
    </div>

    <!-- Admin Block 5: Monetization & Revenue Dashboard -->
    <div id="admin-revenue-block" class="admin-block-panel" style="display: none;">
        <!-- KPI summary statistics cards -->
        <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
            <div class="glass-panel stat-card" style="background: rgba(37, 99, 235, 0.02); border: 1px solid var(--border-color); text-align:center;">
                <div class="stat-num" id="revStatsAdsTotal" style="font-size:1.8rem; font-family:'Outfit'; color: var(--accent-color);">₹0.00</div>
                <div class="stat-label" style="font-size:0.75rem;">Google AdSense CPM/CPC</div>
            </div>
            <div class="glass-panel stat-card" style="background: rgba(245, 158, 11, 0.02); border: 1px solid var(--border-color); text-align:center;">
                <div class="stat-num" id="revStatsAffiliate" style="font-size:1.8rem; font-family:'Outfit'; color: #f59e0b;">₹0.00</div>
                <div class="stat-label" style="font-size:0.75rem;">Cloaked Affiliate Clicks</div>
            </div>
            <div class="glass-panel stat-card" style="background: rgba(139, 92, 246, 0.02); border: 1px solid var(--border-color); text-align:center;">
                <div class="stat-num" id="revStatsSponsorship" style="font-size:1.8rem; font-family:'Outfit'; color: #8b5cf6;">₹0.00</div>
                <div class="stat-label" style="font-size:0.75rem;">Direct Sponsorship Fees</div>
            </div>
            <div class="glass-panel stat-card" style="background: rgba(16, 185, 129, 0.02); border: 1px solid var(--border-color); text-align:center;">
                <div class="stat-num" id="revStatsSubscriptions" style="font-size:1.8rem; font-family:'Outfit'; color: #10b981;">₹0.00</div>
                <div class="stat-label" style="font-size:0.75rem;">Premium Plan Signups</div>
            </div>
            <div class="glass-panel stat-card" style="background: var(--bg-primary); border: 1.5px solid var(--accent-color); text-align:center;">
                <div class="stat-num" id="revStatsGrandTotal" style="font-size:1.8rem; font-family:'Outfit'; color: var(--text-primary); font-weight:800;">₹0.00</div>
                <div class="stat-label" style="font-size:0.75rem; color:var(--accent-color); font-weight:700;">CONSOLIDATED EARNINGS</div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1.2fr; gap: 2rem; align-items: start;">
            <!-- Column 1: Streams Daily Graph -->
            <div class="glass-panel" style="padding: 1.5rem;">
                <h3 style="font-size:1.25rem; margin-bottom:1.5rem; color:var(--accent-color); font-family:'Outfit';">Estimated Earnings Breakdown</h3>
                
                <div id="revenueStreamsGraphBlock" style="height: 250px; display: flex; align-items: flex-end; justify-content: space-between; gap: 0.5rem; border-left: 2px solid var(--border-color); border-bottom: 2px solid var(--border-color); padding: 1rem 0.5rem 0.5rem 1rem; margin-bottom: 1.5rem;">
                    <!-- Drawn dynamically via JS -->
                </div>
                <div style="display:flex; justify-content:center; gap:1.5rem; font-size:0.8rem; color:var(--text-secondary);">
                    <div style="display:flex; align-items:center; gap:0.4rem;">
                        <span style="display:inline-block; width:12px; height:12px; background:var(--accent-color); border-radius:3px;"></span> AdSense CPC/CPM
                    </div>
                    <div style="display:flex; align-items:center; gap:0.4rem;">
                        <span style="display:inline-block; width:12px; height:12px; background:#f59e0b; border-radius:3px;"></span> Affiliate Clicks
                    </div>
                    <div style="display:flex; align-items:center; gap:0.4rem;">
                        <span style="display:inline-block; width:12px; height:12px; background:#10b981; border-radius:3px;"></span> Subscriptions
                    </div>
                </div>
            </div>

            <!-- Column 2: Leaderboard -->
            <div class="glass-panel" style="padding: 1.5rem;">
                <h3 style="font-size:1.25rem; margin-bottom:1rem; color:var(--text-primary); font-family:'Outfit';">Top Affiliate Performers</h3>
                <div class="responsive-table-container">
                    <table class="portal-table" id="adminRevenueLeaderboardTable">
                        <thead>
                            <tr>
                                <th>Recruitment Guide</th>
                                <th>Clicks</th>
                                <th style="text-align: right;">Earned</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    const isLoggedIn = @json(auth()->check());

    $(document).ready(function() {
        
        // 1. Interactive Sidebar Tab Switches (Local DOM shifts for admit card panel)
        $('.tab-btn').on('click', function() {
            const targetTab = $(this).data('tab');
            $(this).siblings().removeClass('active');
            $(this).addClass('active');
            $(`#${targetTab}`).siblings('.tab-content').removeClass('active');
            $(`#${targetTab}`).addClass('active');
        });

        // FAQ accordion transitions
        $(document).on('click', '.accordion-header', function() {
            $(this).parent('.accordion-item').toggleClass('active').siblings().removeClass('active');
        });

        // Contact Support ticket simulated dispatches
        $('#ajaxContactForm').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#contactSubmitBtn');
            btn.prop('disabled', true).text('Sending message...');
            setTimeout(() => {
                btn.prop('disabled', false).text('Submit Support Ticket');
                showToast('Support ticket dispatched successfully! Our helpline agents will review your message shortly.', 'success');
                $('#ajaxContactForm')[0].reset();
            }, 800);
        });

        // ================= NAVBAR PORTAL TAB TRIGGERS =================
        $('.nav-tab-trigger').on('click', function(e) {
            e.preventDefault();
            const target = $(this).data('target'); // 'dashboard', 'admin', 'jobs', 'info-hub'
            
            // Toggle view panels
            $('.portal-main-tab').hide();
            
            if (target === 'dashboard') {
                $('#dashboard-section').fadeIn();
                loadDashboardData();
            } else if (target === 'admin') {
                $('#admin-section').fadeIn();
                loadAdminData();
            } else if (target === 'info-hub') {
                $('#info-hub-section').fadeIn();
            } else {
                $('#jobs-search-section').fadeIn();
            }
        });

        // Sub-tabs transitions inside Information Hub
        $('.sub-tab-btn[data-sub]').on('click', function(e) {
            e.preventDefault();
            $(this).siblings().removeClass('active');
            $(this).addClass('active');
            const targetSub = $(this).data('sub');
            $(`#${targetSub}`).siblings('.sub-tab-content').hide();
            $(`#${targetSub}`).fadeIn();
        });

        // Sub-tabs transitions inside Candidate Dashboard settings
        $('.dash-sub-trigger').on('click', function(e) {
            e.preventDefault();
            $('.dash-sub-trigger').removeClass('active');
            $(this).addClass('active');
            const targetBlock = $(this).data('target');
            $('.dash-block-panel').hide();
            $(`#${targetBlock}`).fadeIn();
        });

        // Sub-tabs transitions inside Administration Control panels
        $('.admin-sub-trigger').on('click', function(e) {
            e.preventDefault();
            $('.admin-sub-trigger').removeClass('active');
            $(this).addClass('active');
            const targetBlock = $(this).data('target');
            $('.admin-block-panel').hide();
            $(`#${targetBlock}`).fadeIn();
            
            if (targetBlock === 'admin-users-block') {
                loadUsersRegistry();
            }
        });

        // If URL hash points to section, load automatically
        const currentHash = window.location.hash;
        if (currentHash === '#dashboard-section') {
            $('.nav-tab-trigger[data-target="dashboard"]').trigger('click');
        } else if (currentHash === '#admin-section') {
            $('.nav-tab-trigger[data-target="admin"]').trigger('click');
        }

        // ================== SEARCH AND PAGINATION SYSTEM ==================
        let currentPage = 1;

        function fetchJobs(page = 1) {
            currentPage = page;
            const queryData = {
                search: $('#searchKeywords').val(),
                state_id: $('#stateSelect').val(),
                qualification_id: $('#qualificationSelect').val(),
                category_id: $('#categorySelect').val(),
                page: page
            };

            $('#jobsListContainer').hide();
            $('#paginationContainer').empty();
            $('#skeletonLoader').show();

            $.ajax({
                url: '/',
                type: 'GET',
                data: queryData,
                dataType: 'json',
                success: function(response) {
                    $('#skeletonLoader').hide();
                    
                    if (response.status === 'success') {
                        const data = response.data;
                        const jobs = data.jobs;

                        $('#jobsCountFeedback').text(`Found ${data.total} recruitments`);

                        if (jobs.length === 0) {
                            $('#jobsListContainer').html(`
                                <div class="glass-panel" style="padding: 3rem; text-align: center; color: var(--text-secondary);">
                                    No recruitment postings match your exact search criteria. Try modifying your filters.
                                </div>
                            `).fadeIn();
                            return;
                        }

                        // Rebuild HTML cards dynamically
                        let html = '';
                        jobs.forEach(function(job) {
                            const isFeaturedBadge = job.is_featured ? '<span class="badge" style="background:var(--accent-color); color:#fff; font-size:0.75rem;">FEATURED</span>' : '';
                            const isSponsoredBadge = job.is_sponsored ? '<span class="badge badge-sponsored">SPONSORED</span>' : '';
                            const sponsoredClass = job.is_sponsored ? 'is-sponsored' : '';
                            const featuredClass = job.is_featured ? 'featured-premium' : '';
                            const applyTarget = job.affiliate_link ? `/go/${job.slug}` : `#`;

                            html += `
                                <div class="glass-panel job-card ${sponsoredClass} ${featuredClass}">
                                    <div class="job-info">
                                        <h3 style="display:flex; align-items:center; gap:0.5rem;">
                                            ${job.title} 
                                            ${isSponsoredBadge}
                                            ${isFeaturedBadge}
                                        </h3>
                                        <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.5rem;">
                                            ${job.department} &bull; ${job.state}
                                        </p>
                                        <div class="job-tags">
                                            <span class="badge badge-dept">${job.qualification}</span>
                                            <span class="badge" style="background: rgba(16, 185, 129, 0.08); color: #10b981;">Vacancies: ${job.vacancy_count}</span>
                                            <span class="badge badge-deadline">Apply by ${job.last_date}</span>
                                        </div>
                                    </div>
                                    <div style="display: flex; flex-direction: column; gap: 0.5rem; align-items: flex-end;">
                                        <a href="${applyTarget}" class="btn-view" data-slug="${job.slug}">View Details</a>
                                        @auth
                                            <button class="btn-sm-danger toggle-bookmark-btn" data-id="${job.id}" style="background: rgba(37,99,235,0.06); color: var(--accent-color); border-color: rgba(37,99,235,0.15);">
                                                Save Job
                                            </button>
                                        @endauth
                                    </div>
                                </div>
                            `;
                        });

                        $('#jobsListContainer').html(html).fadeIn();
                        buildPagination(data.current_page, data.last_page);
                    }
                },
                error: function() {
                    $('#skeletonLoader').hide();
                    $('#jobsListContainer').html(`
                        <div class="glass-panel" style="padding: 3rem; text-align: center; color: #ef4444; border-color: rgba(239,68,68,0.2);">
                            <strong>System error occurred!</strong> Could not synchronize listings. Please try again.
                        </div>
                    `).fadeIn();
                }
            });
        }

        // Render Pagination buttons
        function buildPagination(current, last) {
            if (last <= 1) return;

            let html = '';
            if (current > 1) {
                html += `<a href="#" class="page-link" data-page="${current - 1}">&laquo; Prev</a>`;
            }
            for (let i = 1; i <= last; i++) {
                const activeClass = i === current ? 'active' : '';
                html += `<a href="#" class="page-link ${activeClass}" data-page="${i}">${i}</a>`;
            }
            if (current < last) {
                html += `<a href="#" class="page-link" data-page="${current + 1}">Next &raquo;</a>`;
            }
            $('#paginationContainer').html(html);
        }

        // Trigger filters
        $('#stateSelect, #qualificationSelect, #categorySelect').on('change', function() {
            fetchJobs(1);
        });

        // State Explorer Chips click handler
        $('#stateExplorerChips').on('click', '.explorer-chip', function() {
            $(this).addClass('active').siblings().removeClass('active');
            const stateId = $(this).data('id');
            $('#stateSelect').val(stateId);
            fetchJobs(1);
        });

        // Qualification Explorer Chips click handler
        $('#qualExplorerChips').on('click', '.explorer-chip', function() {
            $(this).addClass('active').siblings().removeClass('active');
            const qualId = $(this).data('id');
            $('#qualificationSelect').val(qualId);
            fetchJobs(1);
        });

        // Sync dropdown changes back to explorer chips
        $('#stateSelect').on('change', function() {
            const val = $(this).val();
            $(`#stateExplorerChips .explorer-chip[data-id="${val || ''}"]`).addClass('active').siblings().removeClass('active');
        });

        // Sync dropdown changes back to explorer chips
        $('#qualificationSelect').on('change', function() {
            const val = $(this).val();
            $(`#qualExplorerChips .explorer-chip[data-id="${val || ''}"]`).addClass('active').siblings().removeClass('active');
        });

        // Search Input Keyup Debouncing
        let searchTimeout = null;
        let autocompleteTimeout = null;

        $('#searchKeywords').on('keyup', function() {
            const query = $(this).val();

            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                fetchJobs(1);

                // Fetch typo suggestion in the background
                if (query.trim().length > 0) {
                    $.ajax({
                        url: '/api/search/typo',
                        type: 'GET',
                        data: { q: query },
                        success: function(res) {
                            if (res.status === 'success' && res.data.suggestion) {
                                $('#homeTypoBanner').html(`
                                    <div class="typo-banner">
                                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                        <span>Did you mean: <a id="homeSuggestedQueryLink" href="#" data-query="${res.data.suggestion}">${res.data.suggestion}</a> ?</span>
                                    </div>
                                `).fadeIn();
                            } else {
                                $('#homeTypoBanner').hide().empty();
                            }
                        }
                    });
                } else {
                    $('#homeTypoBanner').hide().empty();
                }
            }, 300);

            // Autocomplete suggest matching
            clearTimeout(autocompleteTimeout);
            if (query.trim().length < 2) {
                $('#autocompleteDropdown').hide().empty();
                return;
            }

            autocompleteTimeout = setTimeout(function() {
                $.ajax({
                    url: '/api/search/autocomplete',
                    type: 'GET',
                    data: { q: query },
                    success: function(res) {
                        if (res.status === 'success') {
                            const data = res.data;
                            let html = '';
                            let totalSuggestions = 0;

                            if (data.jobs && data.jobs.length > 0) {
                                html += `<div class="autocomplete-section">
                                    <div class="autocomplete-header">💼 Jobs Found</div>`;
                                data.jobs.forEach(item => {
                                    html += `<div class="autocomplete-item select-suggest-job" data-slug="${item.slug}">
                                        <span>${item.title}</span>
                                        <span class="badge-type">${item.post_type}</span>
                                    </div>`;
                                });
                                html += `</div>`;
                                totalSuggestions += data.jobs.length;
                            }

                            if (data.categories && data.categories.length > 0) {
                                html += `<div class="autocomplete-section">
                                    <div class="autocomplete-header">📁 Streams</div>`;
                                data.categories.forEach(item => {
                                    html += `<div class="autocomplete-item select-suggest-slug" data-type="category" data-slug="${item.slug}">
                                        <span>${item.name} board listings</span>
                                        <span class="badge-type">stream</span>
                                    </div>`;
                                });
                                html += `</div>`;
                                totalSuggestions += data.categories.length;
                            }

                            if (data.states && data.states.length > 0) {
                                html += `<div class="autocomplete-section">
                                    <div class="autocomplete-header">📍 Regions</div>`;
                                data.states.forEach(item => {
                                    html += `<div class="autocomplete-item select-suggest-slug" data-type="state" data-slug="${item.slug}">
                                        <span>Jobs located in ${item.name}</span>
                                        <span class="badge-type">region</span>
                                    </div>`;
                                });
                                html += `</div>`;
                                totalSuggestions += data.states.length;
                            }

                            if (totalSuggestions > 0) {
                                $('#autocompleteDropdown').html(html).fadeIn();
                            } else {
                                $('#autocompleteDropdown').hide().empty();
                            }
                        }
                    }
                });
            }, 150);
        });

        // Autocomplete click actions on homepage
        $(document).on('click', '.select-suggest-job', function() {
            const slug = $(this).data('slug');
            window.location.href = `/job/${slug}`;
        });

        $(document).on('click', '.select-suggest-slug', function() {
            const type = $(this).data('type');
            const slug = $(this).data('slug');
            window.location.href = `/search/${type}/${slug}`;
        });

        // Hide autocomplete when clicking outside input
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#interactive-finder').length) {
                $('#autocompleteDropdown').hide();
            }
        });

        // Suggested typo link clicked handler
        $(document).on('click', '#homeSuggestedQueryLink', function(e) {
            e.preventDefault();
            const query = $(this).data('query');
            $('#searchKeywords').val(query);
            $('#homeTypoBanner').hide().empty();
            fetchJobs(1);
        });

        $(document).on('click', '.page-link', function(e) {
            e.preventDefault();
            const targetPage = $(this).data('page');
            fetchJobs(targetPage);
        });

        // ================== BOOKMARK SWITCH CLICKS ==================
        $(document).on('click', '.toggle-bookmark-btn', function(e) {
            e.preventDefault();
            const btn = $(this);
            const jobId = btn.data('id');
            btn.prop('disabled', true);

            $.ajax({
                url: `/api/jobs/${jobId}/bookmark`,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    btn.prop('disabled', false);
                    showToast(res.message, 'success');
                    if (res.action === 'added') {
                        btn.text('Remove Save').css({'color': '#ef4444', 'border-color': 'rgba(239,68,68,0.15)', 'background': 'rgba(239,68,68,0.06)'});
                    } else {
                        btn.text('Save Job').css({'color': 'var(--accent-color)', 'border-color': 'rgba(37,99,235,0.15)', 'background': 'rgba(37,99,235,0.06)'});
                    }
                },
                error: function(err) {
                    btn.prop('disabled', false);
                    if (err.status === 401) {
                        showToast('Please log in to save recruitments!', 'warning');
                        $('#authModal').addClass('active');
                    } else {
                        showToast('Failed to save recruitment.', 'error');
                    }
                }
            });
        });

        // ================== DETAILED ASYNC POPUP MODAL ==================
        const detailsModal = $('#jobDetailsModal');

        $(document).on('click', '.btn-view', function(e) {
            e.preventDefault();
            const slug = $(this).data('slug');
            if (!slug) return;

            detailsModal.addClass('active');
            $('#modalSkeletonLoader').show();
            $('#modalRealContent').hide();
            $('#modalApplicationFormBlock').hide();

            $.ajax({
                url: `/api/jobs/${slug}`,
                method: 'GET',
                success: function(res) {
                    if (res.status === 'success') {
                        const job = res.data;
                        let html = '';
                        const type = job.post_type || 'job';
                        
                        if (type === 'job') {
                            html = `
                                <div class="theme-accent-job">
                                    <div class="category-visual-header">
                                        <h2>💼 ${job.title}</h2>
                                        <p>${job.department} &bull; ${job.state} &bull; ${job.category}</p>
                                    </div>
                                    
                                    <div class="details-summary-grid">
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">Monthly Salary Index</div>
                                            <div style="font-size:1.15rem; font-weight:700; color:var(--text-primary); margin-top:0.25rem;">₹ ${job.salary_min} - ₹ ${job.salary_max}</div>
                                        </div>
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">Total Vacancies</div>
                                            <div style="font-size:1.15rem; font-weight:700; color:var(--accent-color); margin-top:0.25rem;">${job.vacancy_count} Active Posts</div>
                                        </div>
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">Application Fees</div>
                                            <div style="font-size:1.15rem; font-weight:700; color:var(--text-primary); margin-top:0.25rem;">₹ ${job.application_fee}</div>
                                        </div>
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">Age Requirements</div>
                                            <div style="font-size:1.15rem; font-weight:700; color:var(--text-primary); margin-top:0.25rem;">${job.age_limit}</div>
                                        </div>
                                    </div>

                                    <div class="details-full-section" style="margin-top:1.5rem;">
                                        <h4 style="color: var(--accent-color); font-weight:700; font-family:'Outfit';">Recruitment Overview & Eligibility</h4>
                                        <p style="color: var(--text-secondary); line-height:1.75; font-size:0.95rem; margin-top:0.5rem;">${job.description}</p>
                                    </div>

                                    <div class="details-full-section" style="margin-top:1.5rem;">
                                        <h4 style="color: var(--accent-color); font-weight:700; font-family:'Outfit';">Selection Process Steps</h4>
                                        <p style="color: var(--text-secondary); line-height:1.75; font-size:0.95rem; margin-top:0.5rem;">${job.selection_process}</p>
                                    </div>

                                    <div class="details-full-section" style="margin-top:1.5rem;">
                                        <h4 style="color: var(--accent-color); font-weight:700; font-family:'Outfit';">Exam Scheme & Syllabus Patterns</h4>
                                        <div class="details-syllabus-container" style="max-height: none; overflow: visible; margin-top:0.5rem; color:var(--text-secondary); line-height:1.75;">
                                            ${job.exam_patter                                    <div style="display: flex; gap: 1rem; margin-top: 2rem; border-top: 1px solid var(--border-color); padding-top: 1.5rem; flex-wrap: wrap;">
                                        <a href="${finalOfficialLink}" target="_blank" class="btn-view" style="flex:1; text-align:center; display:flex; align-items:center; justify-content:center; gap:0.4rem; font-weight:600; text-decoration:none;">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle;"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                            Official Advertisement
                                        </a>
                                        %isLoggedIn%
                                    </div>                                      \%isLoggedIn%
                                    </div>
                                </div>
                            `;
                            html = html.replace('\%isLoggedIn%', isLoggedIn ? `
                                <button id="modalApplyBtn" class="form-btn" style="flex:1.5; margin-top:0; padding: 0.8rem; background: var(--accent-color); font-weight:700;" data-id="${job.id}">
                                    Apply Recruitment Now
                                </button>
                            ` : `
                                <button class="form-btn trigger-auth-redirect-btn" style="flex:1.5; margin-top:0; padding: 0.8rem; background: var(--text-secondary); color:#ffffff; font-weight:700;">
                                    Login to Apply Now
                                </button>
                            `);
                        } else if (type === 'admit_card') {
                            html = `
                                <div class="theme-accent-admit_card">
                                    <div class="category-visual-header">
                                        <h2>🎟️ ${job.title} Admit Card</h2>
                                        <p>${job.department} &bull; ${job.state} &bull; Official Call Letter</p>
                                    </div>

                                    <div class="details-summary-grid">
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">Admit Card Status</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: #10b981; margin-top:0.25rem;">⚡ RELEASED & ACTIVE</div>
                                        </div>
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">Expected Exam Date</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: var(--text-primary); margin-top:0.25rem;">${job.exam_date}</div>
                                        </div>
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">Total Vacancies</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: var(--text-primary); margin-top:0.25rem;">${job.vacancy_count} Posts</div>
                                        </div>
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">Download Deadline</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: #ef4444; margin-top:0.25rem;">${job.last_date}</div>
                                        </div>
                                    </div>

                                    <div class="details-full-section" style="margin-top:1.5rem;">
                                        <h4 style="color: #10b981; font-weight:700; font-family:'Outfit';">Download Call Letter Instructions</h4>
                                        <p style="color: var(--text-secondary); line-height:1.75; font-size:0.95rem; margin-top:0.5rem;">
                                            The selection board has released the admit cards for <strong>${job.title}</strong>. Please download your entry card prior to the download deadline.
                                        </p>
                                        <div style="background: rgba(16, 185, 129, 0.05); padding: 1.25rem; border-radius: 8px; border: 1px dashed rgba(16, 185, 129, 0.2); margin: 1.25rem 0;">
                                            <h5 style="color: #10b981; margin-bottom: 0.5rem; font-weight: 700; font-size:0.95rem;">Required Credentials Checklist:</h5>
                                            <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.4rem; padding: 0; margin: 0; font-size: 0.9rem; color: var(--text-secondary);">
                                                <li>🔑 1. Registered Application Number / Registration ID</li>
                                                <li>🎂 2. Candidate Date of Birth (DD-MM-YYYY format)</li>
                                                <li>🧩 3. Security Verification Code Captcha</li>
                                            </ul>
                                        </div>
                                        <p style="color: var(--text-secondary); font-size: 0.9rem; line-height:1.5;">
                                            ⚠️ <strong>Note:</strong> Carry a printed color copy of this Admit Card along with an active government photo ID proof (Aadhaar Card, Passport, driving license, PAN card) and two passport-sized color photos to the test venue.
                                        </p>
                                    </div>

                                    <div class="download-callout-panel">
                                        <h4 style="color: var(--text-primary); margin-bottom: 0.5rem; font-weight: 700;">Direct Candidate Server Access</h4>
                                        <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1.25rem;">Select Server 1 or 2 to download call letters instantly.</p>
                                        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                                            <a href="${job.apply_link || job.official_website_link}" target="_blank" class="download-button-premium">
                                                🚀 Download Call Letter (Server 1)
                                            </a>
                                            <a href="${job.official_website_link}" target="_blank" class="download-button-premium" style="background: var(--bg-secondary); border: 1px solid var(--border-color); color: var(--text-primary); box-shadow: none;">
                                                🌐 Alternative Login (Server 2)
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            `;
                        } else if (type === 'result') {
                            html = `
                                <div class="theme-accent-result">
                                    <div class="category-visual-header">
                                        <h2>🏆 ${job.title} Exam Result</h2>
                                        <p>${job.department} &bull; ${job.state} &bull; Merit & Cutoff Scores</p>
                                    </div>

                                    <div class="details-summary-grid">
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">Result Status</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: #8b5cf6; margin-top:0.25rem;">🎉 MERIT LIST RELEASED</div>
                                        </div>
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">Cutoff Verification</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: var(--text-primary); margin-top:0.25rem;">COMPLETED</div>
                                        </div>
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">Total Selected Candidates</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: var(--text-primary); margin-top:0.25rem;">${job.vacancy_count} Allotments</div>
                                        </div>
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">Allotment Date</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: var(--text-primary); margin-top:0.25rem;">${job.last_date}</div>
                                        </div>
                                    </div>

                                    <div class="details-full-section" style="margin-top:1.5rem;">
                                        <h4 style="color: #8b5cf6; font-weight:700; font-family:'Outfit';">Category-Wise Cutoff Marks</h4>
                                        <table class="details-cutoff-table">
                                            <thead>
                                                <tr>
                                                    <th>Category Segment</th>
                                                    <th>Cutoff Marks (%)</th>
                                                    <th>Status Index</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><strong>General (UR)</strong></td>
                                                    <td>78.50%</td>
                                                    <td>Active / Cleared</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>OBC</strong></td>
                                                    <td>72.40%</td>
                                                    <td>Active / Cleared</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>SC / ST</strong></td>
                                                    <td>65.00%</td>
                                                    <td>Active / Cleared</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>EWS</strong></td>
                                                    <td>70.15%</td>
                                                    <td>Active / Cleared</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="details-full-section" style="margin-top:1.5rem;">
                                        <h4 style="color: #8b5cf6; font-weight:700; font-family:'Outfit';">Next Steps & Counselling Process</h4>
                                        <p style="color: var(--text-secondary); line-height: 1.75; font-size: 0.95rem; margin-top:0.5rem;">
                                            All qualifying candidates whose roll numbers are highlighted in the merit list must prepare documents for the biometric validation and certificate screening. Individual counseling invitations will be sent via registered candidate emails soon.
                                        </p>
                                    </div>

                                    <div class="download-callout-panel">
                                        <h4 style="color: var(--text-primary); margin-bottom: 0.5rem; font-weight: 700;">Direct Merit PDF Downloads</h4>
                                        <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1.25rem;">Download final selection indexes or cutoff list directly from secure servers.</p>
                                        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                                            <a href="${job.apply_link || job.official_website_link}" target="_blank" class="download-button-premium">
                                                📄 Download Merit List (PDF)
                                            </a>
                                            <a href="${job.official_website_link}" target="_blank" class="download-button-premium" style="background: var(--bg-secondary); border: 1px solid var(--border-color); color: var(--text-primary); box-shadow: none;">
                                                📊 Download Official Cutoff
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            `;
                        } else if (type === 'syllabus') {
                            html = `
                                <div class="theme-accent-syllabus">
                                    <div class="category-visual-header">
                                        <h2>📖 ${job.title} Exam Syllabus</h2>
                                        <p>${job.department} &bull; ${job.state} &bull; Topics & Marking Pattern</p>
                                    </div>

                                    <div class="details-summary-grid">
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">Syllabus Status</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: #db2777; margin-top:0.25rem;">⭐ OFFICIAL OVERHAUL</div>
                                        </div>
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">Total Exam Marks</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: var(--text-primary); margin-top:0.25rem;">200 - 300 Marks</div>
                                        </div>
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">Negative Marking</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: #ef4444; margin-top:0.25rem;">0.25 Points / Wrong</div>
                                        </div>
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">Duration Allowance</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: var(--text-primary); margin-top:0.25rem;">120 - 180 Minutes</div>
                                        </div>
                                    </div>

                                    <div class="details-full-section" style="margin-top:1.5rem;">
                                        <h4 style="color: #db2777; font-weight:700; font-family:'Outfit';">Exam Scheme & Section Breakdown</h4>
                                        <p style="color: var(--text-secondary); line-height: 1.75; font-size: 0.95rem; margin-top:0.5rem;">${job.exam_pattern}</p>
                                    </div>

                                    <div class="details-full-section" style="margin-top:1.5rem;">
                                        <h4 style="color: #db2777; font-weight:700; font-family:'Outfit';">Important Subjects & Key Syllabus Focus</h4>
                                        <div class="details-syllabus-container" style="max-height: none; overflow: visible; margin-top:0.5rem; color:var(--text-secondary); line-height:1.75;">
                                            ${job.description}
                                        </div>
                                    </div>

                                    <div class="download-callout-panel">
                                        <h4 style="color: var(--text-primary); margin-bottom: 0.5rem; font-weight: 700;">Download Official Study Resources</h4>
                                        <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1.25rem;">Grab verified syllabus copy and previous year mock papers instantly.</p>
                                        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                                            <a href="${job.official_website_link}" target="_blank" class="download-button-premium">
                                                📚 Download Detailed Syllabus (PDF)
                                            </a>
                                            <a href="${job.official_website_link}" target="_blank" class="download-button-premium" style="background: var(--bg-secondary); border: 1px solid var(--border-color); color: var(--text-primary); box-shadow: none;">
                                                ✏️ Mock Question Papers
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            `;
                        } else if (type === 'answer_key') {
                            html = `
                                <div class="theme-accent-answer_key">
                                    <div class="category-visual-header">
                                        <h2>🔑 ${job.title} Answer Key</h2>
                                        <p>${job.department} &bull; ${job.state} &bull; Official Key & Objection Window</p>
                                    </div>

                                    <div class="details-summary-grid">
                                        <div class="details-summary-grid">
                                            <div class="details-summary-item">
                                                <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">Answer Key State</div>
                                                <div style="font-size:1.15rem; font-weight:700; color: #d97706; margin-top:0.25rem;">📝 ACTIVE / OBJECTION OPEN</div>
                                            </div>
                                            <div class="details-summary-item">
                                                <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">Release Date</div>
                                                <div style="font-size:1.15rem; font-weight:700; color: var(--text-primary); margin-top:0.25rem;">${job.exam_date}</div>
                                            </div>
                                            <div class="details-summary-item">
                                                <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">Objection Filing Fee</div>
                                                <div style="font-size:1.15rem; font-weight:700; color: var(--text-primary); margin-top:0.25rem;">₹ 100 / Question</div>
                                            </div>
                                            <div class="details-summary-item">
                                                <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">Closing Date</div>
                                                <div style="font-size:1.15rem; font-weight:700; color: #ef4444; margin-top:0.25rem;">${job.last_date}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="details-full-section" style="margin-top:1.5rem;">
                                        <h4 style="color: #d97706; font-weight:700; font-family:'Outfit';">Important Objection Filing Milestones</h4>
                                        <div class="objections-timeline">
                                            <div class="timeline-milestone">
                                                <div class="timeline-milestone-title">1. Release of Provisional Key</div>
                                                <div class="timeline-milestone-desc">Candidates can access their individual exam response sheets along with official answer options.</div>
                                            </div>
                                            <div class="timeline-milestone">
                                                <div class="timeline-milestone-title">2. Objection Submission Gate (OPEN)</div>
                                                <div class="timeline-milestone-desc">If any answer candidate selected differs from the key, they can upload substantial text book proof.</div>
                                            </div>
                                            <div class="timeline-milestone">
                                                <div class="timeline-milestone-title">3. Announcement of Final Key</div>
                                                <div class="timeline-milestone-desc">The advisory committee will evaluate objections and launch the overridden final answer key copy.</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="download-callout-panel">
                                        <h4 style="color: var(--text-primary); margin-bottom: 0.5rem; font-weight: 700;">Download Keys & File Objections</h4>
                                        <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1.25rem;">Check your scores against the keys or raise concerns directly.</p>
                                        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                                            <a href="${job.apply_link || job.official_website_link}" target="_blank" class="download-button-premium">
                                                🔑 Download Provisional Key (PDF)
                                            </a>
                                            <a href="${job.official_website_link}" target="_blank" class="download-button-premium" style="background: var(--bg-secondary); border: 1px solid var(--border-color); color: var(--text-primary); box-shadow: none;">
                                                🛡️ Raise Key Objections Now
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            `;
                        } else if (type === 'admission') {
                            html = `
                                <div class="theme-accent-admission">
                                    <div class="category-visual-header">
                                        <h2>🎓 ${job.title}</h2>
                                        <p>${job.department} &bull; Entrance & Counselling Board</p>
                                    </div>

                                    <div class="details-summary-grid">
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">Program Stream</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: #0891b2; margin-top:0.25rem;">Academic & Technical</div>
                                        </div>
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">Entrance Exam Fee</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: var(--text-primary); margin-top:0.25rem;">₹ ${job.application_fee}</div>
                                        </div>
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">Seat Intake Cap</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: var(--text-primary); margin-top:0.25rem;">${job.vacancy_count} Open Seats</div>
                                        </div>
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">Counseling Deadline</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: #ef4444; margin-top:0.25rem;">${job.last_date}</div>
                                        </div>
                                    </div>

                                    <div class="details-full-section" style="margin-top:1.5rem;">
                                        <h4 style="color: #0891b2; font-weight:700; font-family:'Outfit';">Course Scope & Eligibility Guidelines</h4>
                                        <p style="color: var(--text-secondary); line-height: 1.75; font-size: 0.95rem; margin-top:0.5rem;">${job.description}</p>
                                    </div>

                                    <div class="details-full-section" style="margin-top:1.5rem;">
                                        <h4 style="color: #0891b2; font-weight:700; font-family:'Outfit';">Semester Fee & Academic Allocation</h4>
                                        <div class="fees-info-grid">
                                            <div style="background: rgba(255,255,255,0.01); border: 1px solid var(--border-color); padding: 1rem; border-radius: 8px;">
                                                <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">Regular Stream Fee</div>
                                                <div style="font-size:1.15rem; font-weight:700; color:var(--text-primary); margin-top:0.25rem;">₹ 25,000 / Year</div>
                                            </div>
                                            <div style="background: rgba(255,255,255,0.01); border: 1px solid var(--border-color); padding: 1rem; border-radius: 8px;">
                                                <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">Selection / Entry Criteria</div>
                                                <div style="font-size:1.15rem; font-weight:700; color:#0891b2; margin-top:0.25rem;">Entrance Score Merit</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div style="display: flex; gap: 1rem; margin-top: 2rem; border-top: 1px solid var(--border-color); padding-top: 1.5rem; flex-wrap: wrap;">
                                        <a href="${job.official_website_link}" target="_blank" class="btn-view" style="flex:1; text-align:center; display:flex; align-items:center; justify-content:center; gap:0.4rem; font-weight:600; text-decoration:none;">
                                            🌐 Official Admissions Portal
                                        </a>
                                        \%isLoggedIn%
                                    </div>
                                </div>
                            `;
                            html = html.replace('\%isLoggedIn%', isLoggedIn ? `
                                <button id="modalApplyBtn" class="form-btn" style="flex:1.5; margin-top:0; padding: 0.8rem; background: #0891b2; font-weight:700;" data-id="${job.id}">
                                    Submit Admissions Form
                                </button>
                            ` : `
                                <button class="form-btn trigger-auth-redirect-btn" style="flex:1.5; margin-top:0; padding: 0.8rem; background: var(--text-secondary); color:#ffffff; font-weight:700;">
                                    Login to Apply Now
                                </button>
                            `);
                        } else if (type === 'scholarship') {
                            html = `
                                <div class="theme-accent-scholarship">
                                    <div class="category-visual-header">
                                        <h2>💰 ${job.title} Scheme</h2>
                                        <p>${job.department} &bull; ${job.state} &bull; Merit & Means Financial Grant</p>
                                    </div>

                                    <div class="details-summary-grid">
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">Financial Grant Scope</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: #ea580c; margin-top:0.25rem;">₹ 50,000 / Academic Year</div>
                                        </div>
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">Income Eligibility Cap</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: var(--text-primary); margin-top:0.25rem;">&lt; ₹ 2.5 Lakhs / Year</div>
                                        </div>
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">Allotment Seats Limit</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: var(--text-primary); margin-top:0.25rem;">${job.vacancy_count} Beneficiaries</div>
                                        </div>
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">Submission Deadline</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: #ef4444; margin-top:0.25rem;">${job.last_date}</div>
                                        </div>
                                    </div>

                                    <div class="details-full-section" style="margin-top:1.5rem;">
                                        <h4 style="color: #ea580c; font-weight:700; font-family:'Outfit';">Scholarship Objective & Grant Criteria</h4>
                                        <p style="color: var(--text-secondary); line-height: 1.75; font-size: 0.95rem; margin-top:0.5rem;">${job.description}</p>
                                    </div>

                                    <div class="details-full-section" style="margin-top:1.5rem;">
                                        <h4 style="color: #ea580c; font-weight:700; font-family:'Outfit';">Mandatory Required Documents Checklist</h4>
                                        <div class="documents-checklist">
                                            <div class="checklist-item">
                                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" style="margin-right:4px;"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                <span>1. Valid Income Certificate verified by local Revenue Inspector (Tahsildar)</span>
                                            </div>
                                            <div class="checklist-item">
                                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" style="margin-right:4px;"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                <span>2. Candidate Caste & Domicile certificate files</span>
                                            </div>
                                            <div class="checklist-item">
                                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" style="margin-right:4px;"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                <span>3. Previous Academic year Marks memo Card / Qualifying certificates</span>
                                            </div>
                                            <div class="checklist-item">
                                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" style="margin-right:4px;"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                <span>4. Candidate Bank Passbook linking Aadhaar profile for direct DBTs</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div style="display: flex; gap: 1rem; margin-top: 2rem; border-top: 1px solid var(--border-color); padding-top: 1.5rem; flex-wrap: wrap;">
                                        <a href="${job.official_website_link}" target="_blank" class="btn-view" style="flex:1; text-align:center; display:flex; align-items:center; justify-content:center; gap:0.4rem; font-weight:600; text-decoration:none;">
                                            🌐 Official Scheme Guidelines
                                        </a>
                                        \%isLoggedIn%
                                    </div>
                                </div>
                            `;
                            html = html.replace('\%isLoggedIn%', isLoggedIn ? `
                                <button id="modalApplyBtn" class="form-btn" style="flex:1.5; margin-top:0; padding: 0.8rem; background: #ea580c; font-weight:700;" data-id="${job.id}">
                                    Apply Scholarship Now
                                </button>
                            ` : `
                                <button class="form-btn trigger-auth-redirect-btn" style="flex:1.5; margin-top:0; padding: 0.8rem; background: var(--text-secondary); color:#ffffff; font-weight:700;">
                                    Login to Apply Now
                                </button>
                            `);
                        } else {
                            html = `
                                <div class="theme-accent-notice">
                                    <div class="category-visual-header">
                                        <h2>📢 ${job.title}</h2>
                                        <p>${job.department} &bull; ${job.state} &bull; Official Important Alert</p>
                                    </div>

                                    <div class="notice-critical-alert">
                                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="margin-top: 2px; flex-shrink: 0;"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                        <div>
                                            <strong>Critical Calendar Notice:</strong> The examination date has been scheduled/updated. Please review the official notice specifications below and align your schedules.
                                        </div>
                                    </div>

                                    <div class="details-summary-grid">
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">Announced Exam Date</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: #dc2626; margin-top:0.25rem;">${job.exam_date}</div>
                                        </div>
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">Notice Published Date</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: var(--text-primary); margin-top:0.25rem;">${job.last_date}</div>
                                        </div>
                                    </div>

                                    <div class="details-full-section" style="margin-top:1.5rem;">
                                        <h4 style="color: #dc2626; font-weight:700; font-family:'Outfit';">Important Circular Specifications</h4>
                                        <p style="color: var(--text-secondary); line-height: 1.75; font-size: 0.95rem; margin-top:0.5rem;">${job.description}</p>
                                    </div>

                                    <div class="download-callout-panel">
                                        <h4 style="color: var(--text-primary); margin-bottom: 0.5rem; font-weight: 700;">Download Official Circular</h4>
                                        <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1.25rem;">Download the full, official notice PDF released by the department.</p>
                                        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                                            <a href="${job.official_website_link}" target="_blank" class="download-button-premium">
                                                📄 Download Official Notice (PDF)
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            `;
                        }

                        $('#modalRealContent').html(html);
                        $('#applicationFormJobId').val(job.id);
                        
                        $('#modalSkeletonLoader').hide();
                        $('#modalRealContent').fadeIn();
                    }
                },
                error: function() {
                    showToast('Failed to retrieve recruitment specifications.', 'error');
                    detailsModal.removeClass('active');
                }
            });
        });

        $('#closeJobDetailsModal, #jobDetailsModal').on('click', function(e) {
            if (e.target === this || e.target.id === 'closeJobDetailsModal') {
                detailsModal.removeClass('active');
            }
        });

        // Show application form section inside details modal (delegated for dynamic elements)
        $(document).on('click', '#modalApplyBtn', function() {
            $('#modalRealContent').hide();
            $('#modalApplicationFormBlock').fadeIn();
        });

        $('#cancelApplicationBtn').on('click', function() {
            $('#modalApplicationFormBlock').hide();
            $('#modalRealContent').fadeIn();
        });

        // ================== AJAX RECRUITMENT APPLICATION FORM SUBMIT ==================
        $('#recruitmentApplicationForm').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const jobId = $('#applicationFormJobId').val();
            const btn = $('#submitApplicationBtn');
            
            btn.prop('disabled', true).text('Submitting Form...');
            $('.invalid-feedback').hide();

            const formData = new FormData(this);

            $.ajax({
                url: `/api/jobs/${jobId}/apply`,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    btn.prop('disabled', false).text('Submit Application');
                    showToast(res.message, 'success');
                    detailsModal.removeClass('active');
                    form[0].reset();
                },
                error: function(err) {
                    btn.prop('disabled', false).text('Submit Application');
                    if (err.status === 422) {
                        const res = err.responseJSON;
                        showToast(res.message || 'Validation error', 'error');
                        if (res.errors && res.errors.resume) {
                            $('#appResumeError').text(res.errors.resume[0]).show();
                        }
                    } else {
                        showToast('Submission error occurred.', 'error');
                    }
                }
            });
        });

        // ================== CANDIDATE DASHBOARD LOADER ==================
        function loadDashboardData() {
            const bTable = $('#dashboardBookmarksTable tbody');
            const aTable = $('#dashboardApplicationsTable tbody');

            bTable.html('<tr><td colspan="4" style="text-align:center;">Loading Saved bookmarks...</td></tr>');
            aTable.html('<tr><td colspan="4" style="text-align:center;">Loading Submitted applications...</td></tr>');

            $.ajax({
                url: '/api/dashboard',
                method: 'GET',
                success: function(res) {
                    if (res.status === 'success') {
                        const data = res.data;
                        
                        // Set stats
                        $('#statsTotalBookmarks').text(data.bookmarks.length);
                        $('#statsTotalApplications').text(data.applications.length);
                        
                        // Set user profile info
                        $('#dashCandidateName').text(data.user.name);
                        $('#dashCandidateEmail').text(data.user.email);
                        $('#dashCandidatePhone').text(data.user.phone);

                        // Seed form inputs in Profile Settings
                        $('#profileName').val(data.user.name);
                        $('#profileEmail').val(data.user.email);
                        $('#profilePhone').val(data.user.phone);

                        // Render Saved Bookmarks
                        if (data.bookmarks.length === 0) {
                            bTable.html('<tr><td colspan="4" style="text-align:center; color: var(--text-secondary);">No recruitment alerts bookmarked.</td></tr>');
                        } else {
                            let bHtml = '';
                            data.bookmarks.forEach(book => {
                                bHtml += `
                                    <tr>
                                        <td style="font-weight:600;">${book.title}</td>
                                        <td>${book.state}</td>
                                        <td style="color:#ef4444; font-weight:500;">${book.last_date}</td>
                                        <td style="text-align:center;">
                                            <button class="btn-sm-danger delete-bookmark-btn" data-id="${book.job_id}" style="margin-right:0.5rem;">Delete</button>
                                            <a href="#" class="btn-view btn-view-sm" data-slug="${book.slug}" style="padding: 0.35rem 0.75rem; font-size:0.75rem;">View</a>
                                        </td>
                                    </tr>
                                `;
                            });
                            bTable.html(bHtml);
                        }

                        // Render Submitted Applications
                        if (data.applications.length === 0) {
                            aTable.html('<tr><td colspan="4" style="text-align:center; color: var(--text-secondary);">No job applications submitted.</td></tr>');
                        } else {
                            let aHtml = '';
                            data.applications.forEach(app => {
                                let statusClass = 'status-applied';
                                if (app.status === 'reviewing') statusClass = 'status-reviewing';
                                if (app.status === 'shortlisted') statusClass = 'status-shortlisted';
                                if (app.status === 'rejected') statusClass = 'status-rejected';

                                aHtml += `
                                    <tr>
                                        <td style="font-weight:600;">${app.title}</td>
                                        <td>${app.department}</td>
                                        <td>${app.applied_at}</td>
                                        <td>
                                            <span class="status-badge ${statusClass}">${app.status}</span>
                                        </td>
                                    </tr>
                                `;
                            });
                            aTable.html(aHtml);
                        }
                    }
                },
                error: function() {
                    showToast('Failed to fetch candidate dashboard metrics.', 'error');
                }
            });
        }

        // Inline Delete Bookmark from Dashboard table
        $(document).on('click', '.delete-bookmark-btn', function(e) {
            e.preventDefault();
            const jobId = $(this).data('id');
            $.ajax({
                url: `/api/jobs/${jobId}/bookmark`,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    showToast('Bookmark deleted.', 'success');
                    loadDashboardData();
                }
            });
        });

        // Candidate updates profile details
        $('#ajaxProfileUpdateForm').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#profileUpdateSubmitBtn');
            btn.prop('disabled', true).text('Updating Profile...');
            $('.invalid-feedback').hide();

            $.ajax({
                url: '/api/profile/update',
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    btn.prop('disabled', false).text('Synchronize Profile Settings');
                    showToast(res.message, 'success');
                    $('#profilePassword').val('');
                    $('#profilePasswordConfirm').val('');
                    loadDashboardData();
                },
                error: function(err) {
                    btn.prop('disabled', false).text('Synchronize Profile Settings');
                    if (err.status === 422) {
                        const res = err.responseJSON;
                        showToast('Correction validation failed.', 'error');
                        if (res.errors) {
                            Object.keys(res.errors).forEach(key => {
                                $(`#profile${key.charAt(0).toUpperCase() + key.slice(1)}Error`).text(res.errors[key][0]).show();
                            });
                        }
                    } else {
                        showToast('Server update failed.', 'error');
                    }
                }
            });
        });

        // Candidate alerts preference settings
        $('#ajaxPreferencesForm').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#preferencesSubmitBtn');
            btn.prop('disabled', true).text('Saving...');

            $.ajax({
                url: '/api/profile/preferences',
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    btn.prop('disabled', false).text('Save Notification Preferences');
                    showToast(res.message, 'success');
                },
                error: function() {
                    btn.prop('disabled', false).text('Save Notification Preferences');
                    showToast('Failed to sync settings.', 'error');
                }
            });
        });

        // ================== ENTERPRISE ADMIN DASHBOARD LOADER ==================
        function loadAdminData() {
            const scTable = $('#adminScrapersTable tbody');
            const lTable = $('#adminScraperLogsTable tbody');
            const qContainer = $('#adminQuarantinedContainer');

            scTable.html('<tr><td colspan="4" style="text-align:center;">Loading scraper configurations...</td></tr>');
            lTable.html('<tr><td colspan="5" style="text-align:center;">Loading dispatch execution logs...</td></tr>');
            qContainer.html('<p style="text-align:center; color: var(--text-secondary);">Scanning isolated quarantined items...</p>');

            $.ajax({
                url: '/api/admin/data',
                method: 'GET',
                success: function(res) {
                    if (res.status === 'success') {
                        const data = res.data;

                        // Render Metrics
                        $('#metricsTotalSources').text(data.metrics.total_sources);
                        $('#metricsSuccessRuns').text(data.metrics.success_runs);
                        $('#metricsQuarantineRuns').text(data.metrics.quarantine_runs);
                        $('#metricsFailedRuns').text(data.metrics.failed_runs);

                        // Render Scrapers Config Table
                        let scHtml = '';
                        data.sources.forEach(src => {
                            const checkedAttr = src.is_active ? 'checked' : '';
                            scHtml += `
                                <tr>
                                    <td style="font-weight:600;">${src.name}</td>
                                    <td><code>${src.cron}</code></td>
                                    <td style="text-align:center;">
                                        <button class="btn-sm-danger trigger-crawling-btn" data-id="${src.id}" style="background: rgba(16, 185, 129, 0.08); color:#10b981; border-color:rgba(16, 185, 129, 0.15);">
                                            Crawl Now
                                        </button>
                                    </td>
                                    <td style="text-align:center;">
                                        <div class="toggle-switch-container" style="justify-content:center;">
                                            <label class="toggle-switch">
                                                <input type="checkbox" class="toggle-scraper-switch" data-id="${src.id}" ${checkedAttr}>
                                                <span class="toggle-slider"></span>
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        });
                        scTable.html(scHtml);

                        // Render Logs Table
                        let lHtml = '';
                        data.logs.forEach(log => {
                            let badgeClass = 'badge-success';
                            if (log.status === 'failed') badgeClass = 'badge-failed';
                            if (log.status === 'quarantined') badgeClass = 'badge-quarantined';
                            if (log.status === 'duplicate') badgeClass = 'badge-duplicate';

                            lHtml += `
                                <tr>
                                    <td><strong>${log.source_name}</strong></td>
                                    <td><span class="admin-badge-status ${badgeClass}">${log.status}</span></td>
                                    <td style="text-align:center; font-weight:600;">${log.items_found}</td>
                                    <td style="font-size:0.8rem; color:var(--text-secondary); max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                        ${log.error_message}
                                    </td>
                                    <td>${log.time}</td>
                                </tr>
                            `;
                        });
                        lTable.html(lHtml);

                        // Render Quarantined Override items review
                        if (data.quarantines.length === 0) {
                            qContainer.html(`
                                <div class="glass-panel" style="padding: 2rem; text-align: center; color: #10b981; border-color: rgba(16,185,129,0.2); background: rgba(16,185,129,0.02);">
                                    &check; Clean Slate: All scraped listings validated 100% and parsed into directories!
                                </div>
                            `);
                        } else {
                            let qHtml = '';
                            data.quarantines.forEach(item => {
                                let errorsList = '';
                                if (item.errors) {
                                    Object.keys(item.errors).forEach(errKey => {
                                        errorsList += `&bull; ${item.errors[errKey]}<br>`;
                                    });
                                }

                                qHtml += `
                                    <div class="quarantine-card glass-panel" id="quarantine_card_${item.id}">
                                        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--border-color); padding-bottom:0.75rem; margin-bottom:0.75rem;">
                                            <div>
                                                <h4 style="font-family:'Outfit'; font-size:1.1rem; color:var(--text-primary);">${item.source_name}</h4>
                                                <small style="color:var(--text-secondary);">${item.time}</small>
                                            </div>
                                            <span class="admin-badge-status badge-quarantined">Isolated</span>
                                        </div>

                                        <div class="quarantine-error-log">
                                            <strong>Validation Omissions Detected:</strong><br>
                                            ${errorsList || 'Missing vital parameters required for candidate application triggers.'}
                                        </div>

                                        <form class="quarantine-rescue-override-form" data-id="${item.id}">
                                            @csrf
                                            <div class="quarantine-grid-fields">
                                                <div class="form-group" style="margin-bottom:0;">
                                                    <label style="font-size:0.8rem; margin-bottom:0.25rem;">Correct Recruitment Title</label>
                                                    <input type="text" name="title" class="form-control" style="padding:0.5rem 0.75rem; font-size:0.85rem;" value="${item.raw_payload.title || ''}" required>
                                                </div>
                                                <div class="form-group" style="margin-bottom:0;">
                                                    <label style="font-size:0.8rem; margin-bottom:0.25rem;">Application Fee (₹)</label>
                                                    <input type="number" name="application_fee" class="form-control" style="padding:0.5rem 0.75rem; font-size:0.85rem;" value="0" required>
                                                </div>
                                            </div>

                                            <div class="quarantine-grid-fields">
                                                <div class="form-group" style="margin-bottom:0;">
                                                    <label style="font-size:0.8rem; margin-bottom:0.25rem;">Apply Deadline (Valid Date)</label>
                                                    <input type="date" name="last_date_to_apply" class="form-control" style="padding:0.5rem 0.75rem; font-size:0.85rem;" required>
                                                </div>
                                                <div class="form-group" style="margin-bottom:0;">
                                                    <label style="font-size:0.8rem; margin-bottom:0.25rem;">Vacancies Count</label>
                                                    <input type="number" name="vacancy_count" class="form-control" style="padding:0.5rem 0.75rem; font-size:0.85rem;" value="25" required>
                                                </div>
                                            </div>

                                            <div class="quarantine-grid-fields">
                                                <div class="form-group" style="margin-bottom:0;">
                                                    <label style="font-size:0.8rem; margin-bottom:0.25rem;">Official Recruitment Website</label>
                                                    <input type="url" name="official_website_link" class="form-control" style="padding:0.5rem 0.75rem; font-size:0.85rem;" value="${item.raw_payload.link || 'https://upsc.gov.in'}" required>
                                                </div>
                                                <div class="form-group" style="margin-bottom:0;">
                                                    <label style="font-size:0.8rem; margin-bottom:0.25rem;">Apply Hyperlink (Online App)</label>
                                                    <input type="url" name="apply_link" class="form-control" style="padding:0.5rem 0.75rem; font-size:0.85rem;" value="${item.raw_payload.link || 'https://upsconline.nic.in'}">
                                                </div>
                                            </div>

                                            <div style="text-align:right; margin-top:1.25rem;">
                                                <button type="submit" class="form-btn rescue-submit-btn" style="margin-top:0; width:auto; padding: 0.5rem 1.5rem; font-size:0.85rem; background:#f59e0b;">
                                                    Rescue & Publish Announcement
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                `;
                            });
                            qContainer.html(qHtml);
                        }
                    }
                },
                error: function() {
                    showToast('Failed to retrieve scraper statistics.', 'error');
                }
            });
        }

        // Admin Action: Toggle Scraper active scheduling
        $(document).on('change', '.toggle-scraper-switch', function() {
            const id = $(this).data('id');
            $.ajax({
                url: `/api/admin/scraper/${id}/toggle`,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    showToast(res.message, 'success');
                    loadAdminData();
                },
                error: function() {
                    showToast('Failed to switch scraper states.', 'error');
                }
            });
        });

        // Admin Action: Manually run and crawl web source feed
        $(document).on('click', '.trigger-crawling-btn', function(e) {
            e.preventDefault();
            const btn = $(this);
            const id = btn.data('id');
            btn.prop('disabled', true).text('Crawling...');

            $.ajax({
                url: `/api/admin/scraper/${id}/run`,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    btn.prop('disabled', false).text('Crawl Now');
                    showToast(res.message, 'success');
                    setTimeout(() => {
                        loadAdminData();
                    }, 1500);
                },
                error: function() {
                    btn.prop('disabled', false).text('Crawl Now');
                    showToast('Failed to dispatch background crawler.', 'error');
                }
            });
        });

        // Admin Action: Submit Quarantined Rescue Form
        $(document).on('submit', '.quarantine-rescue-override-form', function(e) {
            e.preventDefault();
            const form = $(this);
            const logId = form.data('id');
            const btn = form.find('.rescue-submit-btn');

            btn.prop('disabled', true).text('Rescuing...');

            $.ajax({
                url: `/api/admin/quarantine/${logId}/rescue`,
                method: 'POST',
                data: form.serialize(),
                success: function(res) {
                    showToast(res.message, 'success');
                    $(`#quarantine_card_${logId}`).slideUp(400, function() {
                        $(this).remove();
                        loadAdminData();
                    });
                },
                error: function(err) {
                    btn.prop('disabled', false).text('Rescue & Publish Announcement');
                    if (err.status === 422) {
                        const res = err.responseJSON;
                        let errText = '';
                        Object.keys(res.errors).forEach(key => {
                            errText += `${res.errors[key][0]}\n`;
                        });
                        showToast(errText || 'Correction validation failed.', 'error');
                    } else {
                        showToast('Failed to override quarantine.', 'error');
                    }
                }
            });
        });

        // Admin Action: Load Registered Users
        function loadUsersRegistry() {
            const uTable = $('#adminUsersRegistryTable tbody');
            uTable.html('<tr><td colspan="7" style="text-align:center;">Scanning registry files...</td></tr>');

            $.ajax({
                url: '/api/admin/users',
                method: 'GET',
                success: function(res) {
                    if (res.status === 'success') {
                        let html = '';
                        res.data.users.forEach(u => {
                            const isActiveBadge = u.is_active ? '<span class="status-badge status-shortlisted">Active</span>' : '<span class="status-badge status-rejected">Suspended</span>';
                            const roleBadge = u.role === 'admin' ? '<span class="role-badge role-admin">Admin</span>' : '<span class="role-badge role-candidate">Candidate</span>';
                            const toggleRoleBtnText = u.role === 'admin' ? 'Demote Candidate' : 'Promote Admin';
                            const toggleActiveBtnText = u.is_active ? 'Suspend' : 'Activate';
                            const activeBtnClass = u.is_active ? 'btn-sm-danger' : 'btn-sm-success';
                            
                            html += `
                                <tr>
                                    <td><strong>#${u.id}</strong></td>
                                    <td style="font-weight:600;">${u.name}</td>
                                    <td>${u.email}</td>
                                    <td>${u.phone}</td>
                                    <td>${roleBadge}</td>
                                    <td style="text-align:center;">${isActiveBadge}</td>
                                    <td style="text-align:center;">
                                        <button class="btn-view btn-view-sm toggle-user-role-btn" data-id="${u.id}" data-role="${u.role === 'admin' ? 'candidate' : 'admin'}" style="padding:0.35rem 0.6rem; font-size:0.75rem; margin-right:0.25rem;">
                                            ${toggleRoleBtnText}
                                        </button>
                                        <button class="${activeBtnClass} toggle-user-status-btn" data-id="${u.id}" data-active="${u.is_active ? 0 : 1}" style="padding:0.35rem 0.6rem; font-size:0.75rem;">
                                            ${toggleActiveBtnText}
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                        uTable.html(html);
                    }
                },
                error: function() {
                    uTable.html('<tr><td colspan="7" style="text-align:center; color:#ef4444;">Failed to sync user records.</td></tr>');
                }
            });
        }

        // Toggle user role promotion
        $(document).on('click', '.toggle-user-role-btn', function() {
            const id = $(this).data('id');
            const targetRole = $(this).data('role');
            $.ajax({
                url: `/api/admin/users/${id}/update`,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}', role: targetRole },
                success: function(res) {
                    showToast(res.message, 'success');
                    loadUsersRegistry();
                },
                error: function(err) {
                    showToast(err.responseJSON.message || 'Operation failed.', 'error');
                }
            });
        });

        // Toggle user suspension status
        $(document).on('click', '.toggle-user-status-btn', function() {
            const id = $(this).data('id');
            const targetActive = $(this).data('active');
            $.ajax({
                url: `/api/admin/users/${id}/update`,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}', is_active: targetActive },
                success: function(res) {
                    showToast(res.message, 'success');
                    loadUsersRegistry();
                },
                error: function(err) {
                    showToast(err.responseJSON.message || 'Operation failed.', 'error');
                }
            });
        });

        // Admin publishes manual job announcement
        $('#ajaxManualJobForm').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#mjSubmitBtn');
            btn.prop('disabled', true).text('Publishing live...');
            $('.invalid-feedback').hide();

            $.ajax({
                url: '/api/admin/jobs/store',
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    btn.prop('disabled', false).text('Publish Announcement Live');
                    showToast(res.message, 'success');
                    $('#ajaxManualJobForm')[0].reset();
                    fetchJobs(1);
                },
                error: function(err) {
                    btn.prop('disabled', false).text('Publish Announcement Live');
                    if (err.status === 422) {
                        const res = err.responseJSON;
                        showToast('Correction validation failed.', 'error');
                        if (res.errors) {
                            Object.keys(res.errors).forEach(key => {
                                $(`#mj${key.charAt(0).toUpperCase() + key.slice(1)}Error`).text(res.errors[key][0]).show();
                            });
                        }
                    } else {
                        showToast('Server manual publish failed.', 'error');
                    }
                }
            });
        });

        // Admin updates dynamic SEO tags in cache
        $('#ajaxSeoSettingsForm').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#seoSubmitBtn');
            btn.prop('disabled', true).text('Caching meta...');
            $('.invalid-feedback').hide();

            $.ajax({
                url: '/api/admin/seo/update',
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    btn.prop('disabled', false).text('Synchronize Meta Tags Cache');
                    showToast(res.message, 'success');
                },
                error: function(err) {
                    btn.prop('disabled', false).text('Synchronize Meta Tags Cache');
                    if (err.status === 422) {
                        showToast('Metadata validation failed.', 'error');
                    } else {
                        showToast('Server meta update failed.', 'error');
                    }
                }
            });
        });

        // ================== MONETIZATION INTERACTIVE CONTROLS ==================
        
        // 1. Payment gateway fields toggling
        $(document).on('change', '#mockPaymentMethod', function() {
            if ($(this).val() === 'upi') {
                $('#upiFieldBlock').show();
                $('#cardFieldBlock').hide();
            } else {
                $('#upiFieldBlock').hide();
                $('#cardFieldBlock').show();
            }
        });

        // 2. Select plan checkout trigger
        $(document).on('click', '.select-membership-plan-btn', function() {
            const plan = $(this).data('plan');
            $('#checkoutTargetPlan').val(plan);
            $('#simulatedPaymentPanel').slideDown();
            $('html, body').animate({
                scrollTop: $("#simulatedPaymentPanel").offset().top - 150
            }, 500);
        });

        // 3. Simulated Upgrade transaction submit
        $('#ajaxSimulatedCheckoutForm').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#paymentSubmitBtn');
            btn.prop('disabled', true).text('Authorizing Secure UPI/Card Link...');
            
            $.ajax({
                url: '/api/membership/upgrade',
                method: 'POST',
                data: {
                    plan: $('#checkoutTargetPlan').val(),
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    showToast(res.message, 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                },
                error: function() {
                    btn.prop('disabled', false).text('Authorize Secure Transaction');
                    showToast('Transaction processing failed. Please retry.', 'error');
                }
            });
        });

        // 4. Render active user plan visual badges
        const currentPlan = isLoggedIn ? @json(auth()->user()?->membership_plan) : 'free';
        if (currentPlan === 'premium') {
            $('#btnPremiumPlanIndicator').prop('disabled', true).text('Active Plan').css('background', 'var(--text-secondary)');
            $('#btnFreePlanIndicator').hide();
        } else if (currentPlan === 'pro') {
            $('#btnProPlanIndicator').prop('disabled', true).text('Active Plan').css('background', 'var(--text-secondary)');
            $('#btnFreePlanIndicator').hide();
        }

        // 5. Admin Revenue Analytics fetching & CSS chart drawing
        $(document).on('click', '#adminRevenueTabTrigger', function() {
            $.ajax({
                url: '/api/admin/revenue-analytics',
                method: 'GET',
                success: function(res) {
                    if (res.success) {
                        const data = res.data;
                        
                        // Update KPI metrics
                        $('#revStatsAdsTotal').text('₹' + Number(data.kpis.ads_cpc + data.kpis.ads_cpm).toFixed(2));
                        $('#revStatsAffiliate').text('₹' + Number(data.kpis.affiliate).toFixed(2));
                        $('#revStatsSponsorship').text('₹' + Number(data.kpis.sponsorship).toFixed(2));
                        $('#revStatsSubscriptions').text('₹' + Number(data.kpis.subscriptions).toFixed(2));
                        $('#revStatsGrandTotal').text('₹' + Number(data.kpis.total_revenue).toFixed(2));

                        // Find maximum bar value for relative chart heights
                        let maxVal = 100;
                        data.charts.streams.forEach(function(day) {
                            const total = day.ads + day.affiliate + day.subscriptions;
                            if (total > maxVal) maxVal = total;
                        });

                        // Draw SVG/CSS composite bars
                        let chartHtml = '';
                        data.charts.streams.forEach(function(day) {
                            const total = day.ads + day.affiliate + day.subscriptions;
                            const adsHeight = total > 0 ? (day.ads / maxVal) * 180 : 0;
                            const affHeight = total > 0 ? (day.affiliate / maxVal) * 180 : 0;
                            const subHeight = total > 0 ? (day.subscriptions / maxVal) * 180 : 0;
                            
                            chartHtml += `
                                <div style="flex:1; display:flex; flex-direction:column; align-items:center; height:100%; justify-content:flex-end;">
                                    <div style="width:24px; display:flex; flex-direction:column-reverse; border-radius:4px; overflow:hidden; background:rgba(255,255,255,0.03); height:180px;">
                                        <div style="height:${adsHeight}px; background:var(--accent-color); width:100%; transition:height 0.5s ease;" title="AdSense: ₹${day.ads}"></div>
                                        <div style="height:${affHeight}px; background:#f59e0b; width:100%; transition:height 0.5s ease;" title="Affiliate: ₹${day.affiliate}"></div>
                                        <div style="height:${subHeight}px; background:#10b981; width:100%; transition:height 0.5s ease;" title="Subscriptions: ₹${day.subscriptions}"></div>
                                    </div>
                                    <span style="font-size:0.68rem; margin-top:0.5rem; color:var(--text-secondary); text-align:center;">${day.date}</span>
                                </div>
                            `;
                        });
                        $('#revenueStreamsGraphBlock').html(chartHtml);

                        // Draw Leaderboard
                        let leaderboardHtml = '';
                        if (data.leaderboard.length === 0) {
                            leaderboardHtml = `
                                <tr>
                                    <td colspan="3" style="text-align:center; color:var(--text-secondary); font-size:0.8rem; padding: 2rem 0;">No affiliate clicks recorded.</td>
                                </tr>
                            `;
                        } else {
                            data.leaderboard.forEach(function(item) {
                                leaderboardHtml += `
                                    <tr>
                                        <td style="font-size:0.8rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:200px;" title="${item.title}">${item.title}</td>
                                        <td style="font-size:0.85rem; font-weight:700;">${item.clicks}</td>
                                        <td style="font-size:0.85rem; font-weight:700; color:#10b981; text-align:right;">₹${item.earnings.toFixed(2)}</td>
                                    </tr>
                                `;
                            });
                        }
                        $('#adminRevenueLeaderboardTable tbody').html(leaderboardHtml);
                    }
                }
            });
        });

    });
</script>
@endsection
