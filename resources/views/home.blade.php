@extends('layouts.app')

@section('title', 'GovJobs - Premium Automated Government Jobs Portal')

@section('content')

<!-- AAGGREGATOR DESIGN SYSTEM STYLES -->

<div style="max-width: 1400px; margin: 0 auto; padding: 0 5%;">

<!-- 1. Hero Welcome Segment -->
<section class="hero" style="margin-bottom: 1.5rem;">
    <h1 data-i18n-html="hero_title">{!! setting('homepage_hero_title', 'Find Your Dream <span style="color: var(--accent-color);">Government Job</span> Today') !!}</h1>
    <p data-i18n="hero_desc">{{ setting('homepage_hero_description', 'Discover real-time, highly validated recruitment alerts across UPSC, SSC, Banking, Railways, and individual states. Updated automatically, systematically verified, 100% accurate.') }}</p>
</section>

<!-- 2. Scrolling Marquee Updates Ticker -->
<div class="ticker-wrap">
    <div class="ticker-label" data-i18n="latest_updates">LATEST UPDATES</div>
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
            <span class="card-title" data-i18n="trend_latest_jobs">Latest Jobs</span>
        </a>
        <a href="#sarkari-admit-cards" class="trending-card" style="--card-accent: #10b981;">
            <div class="card-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">🎟️</div>
            <span class="card-title" data-i18n="trend_admit_cards">Admit Cards</span>
        </a>
        <a href="#sarkari-results" class="trending-card" style="--card-accent: #8b5cf6;">
            <div class="card-icon" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;">🏆</div>
            <span class="card-title" data-i18n="trend_results">Exam Results</span>
        </a>
        <a href="#sarkari-answer-keys" class="trending-card" style="--card-accent: #f59e0b;">
            <div class="card-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">🔑</div>
            <span class="card-title" data-i18n="trend_answer_keys">Answer Keys</span>
        </a>
        <a href="#sarkari-syllabus" class="trending-card" style="--card-accent: #ec4899;">
            <div class="card-icon" style="background: rgba(236, 72, 153, 0.1); color: #ec4899;">📖</div>
            <span class="card-title" data-i18n="trend_syllabus">Syllabus</span>
        </a>
        <a href="#sarkari-notices" class="trending-card" style="--card-accent: #ef4444;">
            <div class="card-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">📢</div>
            <span class="card-title" data-i18n="trend_notices">Notices</span>
        </a>
        <a href="#sarkari-admissions" class="trending-card" style="--card-accent: #06b6d4;">
            <div class="card-icon" style="background: rgba(6, 182, 212, 0.1); color: #06b6d4;">🎓</div>
            <span class="card-title" data-i18n="trend_admissions">Admissions</span>
        </a>
        <a href="#sarkari-scholarships" class="trending-card" style="--card-accent: #f97316;">
            <div class="card-icon" style="background: rgba(249, 115, 22, 0.1); color: #f97316;">💰</div>
            <span class="card-title" data-i18n="trend_scholarships">Scholarships</span>
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
                <span data-i18n="trend_latest_jobs">Latest Jobs</span>
            </div>
            <ul class="sarkari-list">
                @forelse($recentJobs as $job)
                    <li class="sarkari-item">
                        <a href="#" class="sarkari-item-link btn-view" data-slug="{{ $job->slug }}" title="{{ $job->title }}">
                            &raquo; <span class="notranslate" translate="no" data-translate-title="{{ $job->title }}">{{ $job->title }}</span>
                        </a>
                        <span class="new-badge" data-i18n="new_badge">NEW</span>
                    </li>
                @empty
                    <li style="padding: 1rem; text-align: center; color: var(--text-secondary); font-size: 0.85rem;" data-i18n="no_active_recruitments">No active recruitments listed.</li>
                @endforelse
            </ul>
        </div>

        <!-- Panel 2: Admit Cards -->
        <div class="sarkari-panel" id="sarkari-admit-cards" style="border-top: 4px solid #10b981;">
            <div class="sarkari-panel-header" style="color: #10b981;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
                <span data-i18n="trend_admit_cards">Admit Cards</span>
            </div>
            <ul class="sarkari-list">
                @forelse($admitCards as $card)
                    <li class="sarkari-item">
                        <a href="#" class="sarkari-item-link btn-view" data-slug="{{ $card->slug }}" title="{{ $card->title }}">
                            &raquo; <span class="notranslate" translate="no" data-translate-title="{{ $card->title }}">{{ $card->title }}</span>
                        </a>
                        <span class="new-badge" data-i18n="new_badge">NEW</span>
                    </li>
                @empty
                    <li style="padding: 1rem; text-align: center; color: var(--text-secondary); font-size: 0.85rem;" data-i18n="no_admit_cards">No active admit cards released.</li>
                @endforelse
            </ul>
        </div>

        <!-- Panel 3: Exam Results -->
        <div class="sarkari-panel" id="sarkari-results" style="border-top: 4px solid #8b5cf6;">
            <div class="sarkari-panel-header" style="color: #8b5cf6;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138z"></path></svg>
                <span data-i18n="trend_results">Exam Results</span>
            </div>
            <ul class="sarkari-list">
                @forelse($results as $res)
                    <li class="sarkari-item">
                        <a href="#" class="sarkari-item-link btn-view" data-slug="{{ $res->slug }}" title="{{ $res->title }}">
                            &raquo; <span class="notranslate" translate="no" data-translate-title="{{ $res->title }}">{{ $res->title }}</span>
                        </a>
                        <span class="new-badge" data-i18n="new_badge">NEW</span>
                    </li>
                @empty
                    <li style="padding: 1rem; text-align: center; color: var(--text-secondary); font-size: 0.85rem;" data-i18n="no_results">No active results declared yet.</li>
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
                <span data-i18n="trend_answer_keys">Answer Keys</span>
            </div>
            <ul class="sarkari-list">
                @forelse($answerKeys as $key)
                    <li class="sarkari-item">
                        <a href="#" class="sarkari-item-link btn-view" data-slug="{{ $key->slug }}" title="{{ $key->title }}">
                            &raquo; <span class="notranslate" translate="no" data-translate-title="{{ $key->title }}">{{ $key->title }}</span>
                        </a>
                        <span class="new-badge" data-i18n="new_badge">NEW</span>
                    </li>
                @empty
                    <li style="padding: 1rem; text-align: center; color: var(--text-secondary); font-size: 0.85rem;" data-i18n="no_answer_keys">No official answer keys released.</li>
                @endforelse
            </ul>
        </div>

        <!-- Panel 5: Exam Syllabus -->
        <div class="sarkari-panel" id="sarkari-syllabus" style="border-top: 4px solid #ec4899;">
            <div class="sarkari-panel-header" style="color: #ec4899;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                <span data-i18n="syllabus_exams">Syllabus & Exams</span>
            </div>
            <ul class="sarkari-list">
                @forelse($syllabi as $syllabus)
                    <li class="sarkari-item">
                        <a href="#" class="sarkari-item-link btn-view" data-slug="{{ $syllabus->slug }}" title="{{ $syllabus->title }}">
                            &raquo; <span class="notranslate" translate="no" data-translate-title="{{ $syllabus->title }}">{{ $syllabus->title }}</span>
                        </a>
                        <span class="new-badge" data-i18n="new_badge">NEW</span>
                    </li>
                @empty
                    <li style="padding: 1rem; text-align: center; color: var(--text-secondary); font-size: 0.85rem;" data-i18n="no_syllabus">No new syllabus structures out.</li>
                @endforelse
            </ul>
        </div>

        <!-- Panel 6: Important Notices -->
        <div class="sarkari-panel" id="sarkari-notices" style="border-top: 4px solid #ef4444;">
            <div class="sarkari-panel-header" style="color: #ef4444;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                <span data-i18n="important_notices">Important Notices</span>
            </div>
            <ul class="sarkari-list">
                @forelse($notices as $notice)
                    <li class="sarkari-item">
                        <a href="#" class="sarkari-item-link btn-view" data-slug="{{ $notice->slug }}" title="{{ $notice->title }}">
                            &raquo; <span class="notranslate" translate="no" data-translate-title="{{ $notice->title }}">{{ $notice->title }}</span>
                        </a>
                        <span class="new-badge" data-i18n="new_badge">NEW</span>
                    </li>
                @empty
                    <li style="padding: 1rem; text-align: center; color: var(--text-secondary); font-size: 0.85rem;" data-i18n="no_notices">No important circular notices active.</li>
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
                <span data-i18n="admissions_hub">Admissions Hub</span>
            </div>
            <ul class="sarkari-list">
                @forelse($admissions as $adm)
                    <li class="sarkari-item">
                        <a href="#" class="sarkari-item-link btn-view" data-slug="{{ $adm->slug }}" title="{{ $adm->title }}">
                            &raquo; <span class="notranslate" translate="no" data-translate-title="{{ $adm->title }}">{{ $adm->title }}</span>
                        </a>
                        <span class="new-badge" data-i18n="new_badge">NEW</span>
                    </li>
                @empty
                    <li style="padding: 1rem; text-align: center; color: var(--text-secondary); font-size: 0.85rem;" data-i18n="no_admissions">No active entrance exam admission notices.</li>
                @endforelse
            </ul>
        </div>

        <!-- Panel 8: Scholarships -->
        <div class="sarkari-panel" id="sarkari-scholarships" style="border-top: 4px solid #f97316;">
            <div class="sarkari-panel-header" style="color: #f97316;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span data-i18n="scholarships_grants">Scholarships & Grants</span>
            </div>
            <ul class="sarkari-list">
                @forelse($scholarships as $scho)
                    <li class="sarkari-item">
                        <a href="#" class="sarkari-item-link btn-view" data-slug="{{ $scho->slug }}" title="{{ $scho->title }}">
                            &raquo; <span class="notranslate" translate="no" data-translate-title="{{ $scho->title }}">{{ $scho->title }}</span>
                        </a>
                        <span class="new-badge" data-i18n="new_badge">NEW</span>
                    </li>
                @empty
                    <li style="padding: 1rem; text-align: center; color: var(--text-secondary); font-size: 0.85rem;" data-i18n="no_scholarships">No active scholarship schemes posted.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <!-- Redesigned Single Row Search Bar Layout -->
    <div class="search-toolbar-wrapper" id="interactive-finder">
        <!-- Typo Correction Banner -->
        <div id="homeTypoBanner" style="display: none; margin-bottom: 1rem;"></div>

        <div class="search-toolbar-main">
            <!-- 1. Search Input Column -->
            <div class="search-input-col">
                <div class="search-input-container">
                    <svg class="search-icon" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <input type="text" id="searchKeywords" placeholder="Search Government Jobs, UPSC, SSC, Railway..." data-i18n="search_placeholder" autocomplete="off" value="{{ request('search') ?? request('q') }}">
                    <button type="button" id="clearSearchBtn" class="clear-search-btn" style="display: none;">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <!-- Autocomplete Dropdown -->
                <div class="autocomplete-dropdown" id="autocompleteDropdown"></div>
            </div>

            <!-- 2. State searchable dropdown -->
            <div class="search-dropdown-col">
                <div class="searchable-dropdown" id="stateDropdownWrapper">
                    <div class="dropdown-selected" tabindex="0" role="combobox" aria-haspopup="listbox" aria-expanded="false" aria-label="Select Region">
                        <span class="selected-text">All Regions</span>
                        <svg class="caret-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </div>
                    <div class="dropdown-panel">
                        <div class="dropdown-search">
                            <input type="text" placeholder="Search region..." class="dropdown-search-input" aria-label="Search region">
                        </div>
                        <div class="dropdown-list" role="listbox"></div>
                    </div>
                </div>
            </div>

            <!-- 3. Qualification searchable dropdown -->
            <div class="search-dropdown-col">
                <div class="searchable-dropdown" id="qualificationDropdownWrapper">
                    <div class="dropdown-selected" tabindex="0" role="combobox" aria-haspopup="listbox" aria-expanded="false" aria-label="Select Qualification">
                        <span class="selected-text">All Qualifications</span>
                        <svg class="caret-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </div>
                    <div class="dropdown-panel">
                        <div class="dropdown-search">
                            <input type="text" placeholder="Search qualification..." class="dropdown-search-input" aria-label="Search qualification">
                        </div>
                        <div class="dropdown-list" role="listbox"></div>
                    </div>
                </div>
            </div>

            <!-- 4. Search Submit Button -->
            <div class="search-btn-col">
                <button type="button" class="btn-search-primary" id="searchSubmitBtn">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                    <span>Search</span>
                </button>
            </div>
        </div>

        <!-- Hidden/Sync dropdown elements -->
        <select id="stateSelect" style="display: none;">
            <option value="" data-i18n="select_state">Select Region/State</option>
            @foreach($states as $state)
                <option value="{{ $state->id }}">{{ $state->name }}</option>
            @endforeach
        </select>
        <select id="qualificationSelect" style="display: none;">
            <option value="" data-i18n="select_qual">Select Qualification</option>
            @foreach($qualifications as $qual)
                <option value="{{ $qual->id }}" {{ request('qualification') === $qual->slug ? 'selected' : '' }}>{{ $qual->name }}</option>
            @endforeach
        </select>

        <!-- Quick Suggestions Row -->
        <div class="quick-suggestions-row">
            <span class="suggestion-label"><span class="fire-icon">🔥</span> Quick Search:</span>
            <div class="suggestions-chips-container">
                <span class="suggestion-chip-item" data-query="UPSC">🔥 UPSC</span>
                <span class="suggestion-chip-item" data-query="SSC">🔥 SSC</span>
                <span class="suggestion-chip-item" data-query="Railway">🔥 Railway</span>
                <span class="suggestion-chip-item" data-query="Banking">🔥 Banking</span>
                <span class="suggestion-chip-item" data-query="Teaching">🔥 Teaching</span>
                <span class="suggestion-chip-item" data-query="Bihar Police">🔥 Bihar Police</span>
            </div>
        </div>

        <!-- Sub-toolbar containing Advanced Filters toggle and active tags summary -->
        <div class="search-sub-toolbar">
            <div class="sub-toolbar-left-group">
                <button type="button" class="btn-advanced-trigger" id="toggleAdvancedFiltersBtn">
                    <span>⚙ Advanced Filters</span>
                </button>
                <button type="button" class="btn-reset-trigger" id="resetFiltersTriggerBtn">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    <span data-i18n="btn_reset">Reset All</span>
                </button>
            </div>
            <div class="active-filter-chips-list" id="activeFilterChipsContainer"></div>
        </div>
    </div>

    <!-- Advanced Filters Drawer overlay & container -->
    <div class="advanced-drawer-overlay" id="advancedDrawerOverlay"></div>
    <div class="advanced-drawer" id="advancedDrawer">
        <div class="drawer-header">
            <h3>⚙ Advanced Filters</h3>
            <button type="button" class="close-drawer-btn" id="closeDrawerBtn">&times;</button>
        </div>
        <div class="drawer-body">
            <!-- Category Filter -->
            <div class="filter-group">
                <label class="filter-label">🏢 Category / Sector</label>
                <div class="searchable-dropdown" id="categoryDropdownWrapper">
                    <div class="dropdown-selected" tabindex="0" role="combobox" aria-haspopup="listbox" aria-expanded="false" aria-label="Select Category">
                        <span class="selected-text" data-i18n="all_streams">All Streams</span>
                        <svg class="caret-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </div>
                    <div class="dropdown-panel">
                        <div class="dropdown-search">
                            <input type="text" placeholder="Search category..." class="dropdown-search-input" aria-label="Search category">
                        </div>
                        <div class="dropdown-list" role="listbox"></div>
                    </div>
                </div>
                <!-- Hidden select input to preserve all existing logic/APIs -->
                <select class="filter-select" id="categorySelect" style="display: none;">
                    <option value="" data-i18n="all_streams">All Streams</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category') === $category->slug ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Free Application Toggle Switch -->
            <div class="filter-group">
                <div class="switch-wrapper" style="width: 100%; min-height: 48px; display: flex; justify-content: space-between; align-items: center; background: var(--bg-primary); border: 1px solid var(--border-color); padding: 0.75rem 1rem; border-radius: 10px;">
                    <span style="font-size: 0.88rem; font-weight: 600; color: var(--text-secondary);" data-i18n="filter_free_app">Free Applications Only (₹0)</span>
                    <label class="custom-switch" style="position: relative; display: inline-block; width: 44px; height: 24px;">
                        <input type="checkbox" id="noFeeCheck" style="opacity: 0; width: 0; height: 0;">
                        <span class="slider-switch" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: var(--border-color); transition: .3s; border-radius: 24px;"></span>
                    </label>
                </div>
            </div>
        </div>
        <div class="drawer-footer">
            <button type="button" class="btn-reset-drawer" id="resetFiltersDrawerBtn">Reset All</button>
            <button type="button" class="btn-apply-drawer" id="applyFiltersDrawerBtn">Apply Filters</button>
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
                    <span data-i18n="premium_featured">Premium Featured Announcements</span>
                </h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.2rem;">
                    @forelse($featuredJobs as $fJob)
                        <div class="glass-panel job-card" style="display: flex; flex-direction: column; justify-content: space-between; height: 100%; border-left: 4px solid var(--accent-color); margin-bottom: 0;">
                            <div class="job-info">
                                <span class="badge" data-i18n="badge_featured" style="margin-bottom: 0.5rem; display: inline-block;">FEATURED</span>
                                <h3 style="font-size: 1.1rem; margin-bottom: 0.4rem;" title="{{ $fJob->title }}">
                                    <span class="notranslate" translate="no" data-translate-title="{{ $fJob->title }}">{{ $fJob->title }}</span>
                                </h3>
                                <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.8rem;">
                                    <span data-translate-lookup="{{ $fJob->department->name ?? 'Government' }}">{{ $fJob->department->name ?? 'Government' }}</span> &bull; <span data-translate-lookup="{{ $fJob->state->name ?? 'Pan India' }}">{{ $fJob->state->name ?? 'Pan India' }}</span>
                                </p>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; gap: 0.5rem;">
                                <span class="badge badge-deadline" data-translate-key="apply_by" data-translate-prefix="📅 " data-translate-suffix=": {{ $fJob->last_date_to_apply ? $fJob->last_date_to_apply->format('d M') : 'N/A' }}">📅 Apply by: {{ $fJob->last_date_to_apply ? $fJob->last_date_to_apply->format('d M') : 'N/A' }}</span>
                                <a href="#" class="btn-view" data-i18n="btn_view_details" data-slug="{{ $fJob->slug }}" aria-label="View details for {{ $fJob->title }}" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; height: auto;">Details</a>
                            </div>
                        </div>
                    @empty
                        <div class="glass-panel" data-i18n="no_featured" style="grid-column: 1/-1; padding: 2rem; text-align: center; color: var(--text-secondary);">
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
                        <span data-i18n="latest_active">Latest Active Recruitments</span>
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
                                <h3 style="display:flex; align-items:center; gap:0.5rem;" title="{{ $rJob->title }}">
                                    <span class="notranslate" translate="no" data-translate-title="{{ $rJob->title }}">{{ $rJob->title }}</span>
                                    @if($rJob->is_sponsored)
                                        <span class="badge badge-sponsored" data-i18n="badge_sponsored">SPONSORED</span>
                                    @elseif($rJob->is_featured)
                                        <span class="badge" data-i18n="badge_featured" style="background:var(--accent-color); color:#fff; font-size:0.75rem;">FEATURED</span>
                                    @endif
                                </h3>
                                <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.5rem;">
                                    <span data-translate-lookup="{{ $rJob->department->name ?? 'Government' }}">{{ $rJob->department->name ?? 'Government' }}</span> &bull; <span data-translate-lookup="{{ $rJob->state->name ?? 'Pan India' }}">{{ $rJob->state->name ?? 'Pan India' }}</span>
                                </p>
                                <div class="job-tags">
                                    <span class="badge badge-dept" data-translate-lookup="{{ $rJob->qualification->name ?? 'Degree Required' }}" data-translate-prefix="🎓 ">🎓 {{ $rJob->qualification->name ?? 'Degree Required' }}</span>
                                    <span class="badge" data-translate-key="vacancies_count" data-translate-prefix="👥 " data-translate-suffix=": {{ number_format($rJob->vacancy_count) }}" style="background: rgba(16, 185, 129, 0.08); color: #10b981;">👥 Vacancies: {{ number_format($rJob->vacancy_count) }}</span>
                                    <span class="badge badge-deadline" data-translate-key="apply_by" data-translate-prefix="📅 " data-translate-suffix=": {{ $rJob->last_date_to_apply ? $rJob->last_date_to_apply->format('d M Y') : 'N/A' }}">📅 Apply by: {{ $rJob->last_date_to_apply ? $rJob->last_date_to_apply->format('d M Y') : 'N/A' }}</span>
                                </div>
                            </div>
                            <div class="job-card-actions">
                                <a href="{{ $applyTarget }}" class="btn-view" data-i18n="btn_view_details" data-slug="{{ $rJob->slug }}" aria-label="View details for {{ $rJob->title }}">View Details</a>
                                @auth
                                    <button class="toggle-bookmark-btn" data-i18n="btn_save_job" data-id="{{ $rJob->id }}" aria-label="Save {{ $rJob->title }} to bookmarks">
                                        Save Job
                                    </button>
                                @endauth
                            </div>
                        </div>
                    @empty
                        <div class="glass-panel" data-i18n="no_active_recruitments" style="padding: 3rem; text-align: center; color: var(--text-secondary);">
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
                    <button class="tab-btn active" data-tab="admitCards" data-i18n="trend_admit_cards">Admit Cards</button>
                    <button class="tab-btn" data-tab="examResults" data-i18n="trend_results">Results</button>
                    <button class="tab-btn" data-tab="syllabi" data-i18n="trend_syllabus">Syllabus</button>
                </div>
                <div class="tab-content active" id="admitCards">
                    <ul class="tab-list">
                        <li class="tab-item"><a href="#">&rarr; <span data-i18n="tab_admit_1">UPSC Civil Services (IAS) 2026 Admit Card</span></a></li>
                        <li class="tab-item"><a href="#">&rarr; <span data-i18n="tab_admit_2">SSC CGL Tier 1 Entry Card</span></a></li>
                        <li class="tab-item"><a href="#">&rarr; <span data-i18n="tab_admit_3">RBI Officer Grade B Exam Schedule</span></a></li>
                        <li class="tab-item"><a href="#">&rarr; <span data-i18n="tab_admit_4">SBI Probationary Officer Exam Hall Ticket</span></a></li>
                    </ul>
                </div>
                <div class="tab-content" id="examResults">
                    <ul class="tab-list">
                        <li class="tab-item"><a href="#" style="font-weight: 500; color: #10b981;">&check; <span data-i18n="tab_result_1">UPSC IFS Final Selection List 2025</span></a></li>
                        <li class="tab-item"><a href="#">&check; <span data-i18n="tab_result_2">Railway NTPC CBT 2 Merit List</span></a></li>
                        <li class="tab-item"><a href="#">&check; <span data-i18n="tab_result_3">IBPS Specialist Officer Mains Result</span></a></li>
                    </ul>
                </div>
                <div class="tab-content" id="syllabi">
                    <ul class="tab-list">
                        <li class="tab-item"><a href="#">&bull; <span data-i18n="tab_syllabus_1">UPSC IAS Complete Pattern (Prelims & Mains)</span></a></li>
                        <li class="tab-item"><a href="#">&bull; <span data-i18n="tab_syllabus_2">SSC CGL Tier 1 & Tier 2 Math Syllabus</span></a></li>
                        <li class="tab-item"><a href="#">&bull; <span data-i18n="tab_syllabus_3">RBI Grade B Phase 1 Syllabus Pattern</span></a></li>
                    </ul>
                </div>
            </div>

            <!-- Automation Status panel -->
            <div class="glass-panel sidebar-panel" style="border-left: 4px solid #10b981;">
                <h3 style="font-size: 1.1rem; margin-bottom: 0.8rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span style="display:inline-block; width:8px; height:8px; background:#10b981; border-radius:50%; animation: pulse 1s infinite;"></span>
                    <span data-i18n="automation_monitor">Automation Monitor</span>
                </h3>
                <p data-i18n="automation_desc" style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.8rem;">
                    Our intelligent scraping pipeline parses government portals every 5 minutes, validates parameters deterministically, and isolates errors in quarantine.
                </p>
                <div style="font-size: 0.8rem; background: var(--bg-primary); padding: 0.6rem; border-radius: 6px; border: 1px solid var(--border-color);">
                    <strong data-i18n="lbl_status">Status</strong>: <span data-i18n="status_active">Active</span> &bull; <strong data-i18n="lbl_system_mode">System Mode</strong>: <span data-i18n="system_failsafe">Failsafe</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TAB 2: PORTAL INFORMATION HUB (NEW TAB) -->
<div class="portal-main-tab" id="info-hub-section" style="display: none; padding: 0 5%; max-width: 1400px; margin: 0 auto;">
    <h2 style="font-size: 1.8rem; margin-bottom: 1.5rem; font-family: 'Outfit';" data-i18n="info_hub_title">Portal Information & Help Center</h2>
    
    <div class="sub-tab-headers">
        <button class="sub-tab-btn active" data-sub="info-blog" data-i18n="info_blog_tab">Blog & News</button>
        <button class="sub-tab-btn" data-sub="info-timeline" data-i18n="info_timeline_tab">About Portal Timeline</button>
        <button class="sub-tab-btn" data-sub="info-faq" data-i18n="info_faq_tab">Frequently Asked Questions</button>
        <button class="sub-tab-btn" data-sub="info-contact" data-i18n="info_contact_tab">Contact Helpdesk</button>
    </div>

    <!-- A. Blog Sub-tab -->
    <div class="sub-tab-content active-sub" id="info-blog">
        <div class="blog-feed-grid">
            <div class="blog-card glass-panel">
                <div class="blog-image-wrapper">UPSC 2026</div>
                <div class="blog-body">
                    <span class="blog-tag" data-i18n="blog_tag_rec">Recruitment News</span>
                    <h3 style="font-size: 1.1rem; margin-bottom: 0.5rem; font-family: 'Outfit';" data-i18n="blog_t1">UPSC Civil Services 2026 Notification Out!</h3>
                    <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1rem; flex-grow: 1;" data-i18n="blog_d1">
                        The Union Public Service Commission has officially announced the vacancies count and cutoff criteria for the IAS/IFS preliminary examinations.
                    </p>
                    <div style="font-size: 0.8rem; color: var(--text-secondary); display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 0.75rem;">
                        <span data-i18n="blog_rel_today">Released: Today</span>
                        <a href="#" class="btn-view-sm" style="text-decoration: none; color: var(--accent-color); font-weight: 600;" data-i18n-html="blog_read_more">Read More &rarr;</a>
                    </div>
                </div>
            </div>
            <div class="blog-card glass-panel">
                <div class="blog-image-wrapper" style="background: linear-gradient(135deg, #10b981, #059669);">SSC CGL</div>
                <div class="blog-body">
                    <span class="blog-tag" style="background:rgba(16,185,129,0.08); color:#10b981;" data-i18n="blog_tag_admit">Admit Card Updates</span>
                    <h3 style="font-size: 1.1rem; margin-bottom: 0.5rem; font-family: 'Outfit';" data-i18n="blog_t2">SSC CGL Tier 1 Hall Ticket Release Dates</h3>
                    <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1rem; flex-grow: 1;" data-i18n="blog_d2">
                        Candidates who submitted application forms can download active entry cards starting this Friday by entering their unique birth records.
                    </p>
                    <div style="font-size: 0.8rem; color: var(--text-secondary); display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 0.75rem;">
                        <span data-i18n="blog_rel_yesterday">Released: Yesterday</span>
                        <a href="#" class="btn-view-sm" style="text-decoration: none; color: #10b981; font-weight: 600;" data-i18n-html="blog_read_more">Read More &rarr;</a>
                    </div>
                </div>
            </div>
            <div class="blog-card glass-panel">
                <div class="blog-image-wrapper" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">RAILWAYS</div>
                <div class="blog-body">
                    <span class="blog-tag" style="background:rgba(139,92,246,0.08); color:#8b5cf6;" data-i18n="blog_tag_syllabus">Syllabus Releases</span>
                    <h3 style="font-size: 1.1rem; margin-bottom: 0.5rem; font-family: 'Outfit';" data-i18n="blog_t3">Railway Recruitment Board Syllabus Overhaul</h3>
                    <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1rem; flex-grow: 1;" data-i18n="blog_d3">
                        The selection committee revised general aptitude and science parameters for technical examinations. Read complete subject breakdowns here.
                    </p>
                    <div style="font-size: 0.8rem; color: var(--text-secondary); display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 0.75rem;">
                        <span data-i18n="blog_rel_2days">Released: 2 days ago</span>
                        <a href="#" class="btn-view-sm" style="text-decoration: none; color: #8b5cf6; font-weight: 600;" data-i18n-html="blog_read_more">Read More &rarr;</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- B. About Us Timeline Sub-tab -->
    <div class="sub-tab-content" id="info-timeline" style="display: none;">
        <div class="glass-panel" style="padding: 1.75rem;">
            <h3 style="font-family:'Outfit'; margin-bottom: 0.5rem; color: var(--accent-color);" data-i18n="timeline_title">Portal Design & Low-Temperature Scraping Pipeline</h3>
            <p style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 1.5rem;" data-i18n="timeline_desc">
                GovJobs is engineered with clean PHP Laravel MVC + Service-Repository architecture, keeping API requests blazing-fast and highly secure.
            </p>
            <div class="timeline-flow">
                <div class="timeline-step">
                    <div class="timeline-title" data-i18n="timeline_s1_t">Stage 1: Multi-Feed Target Web Scraper</div>
                    <div class="timeline-desc" data-i18n="timeline_s1_d">Intelligent crawler engines fetch recruitment notifications directly from official portals asynchronously via Background Queues.</div>
                </div>
                <div class="timeline-step">
                    <div class="timeline-title" data-i18n="timeline_s2_t">Stage 2: Deterministic Pre-Parser Validation</div>
                    <div class="timeline-desc" data-i18n="timeline_s2_d">Strict regex filters extract qualification codes, vacancies, cutoff ages, application fees, and deadlines. Matches with incomplete fields are quarantined.</div>
                </div>
                <div class="timeline-step">
                    <div class="timeline-title" data-i18n="timeline_s3_t">Stage 3: Quarantine Override & Live Publish</div>
                    <div class="timeline-desc" data-i18n="timeline_s3_d">Administrators review isolated postings, make corrections with a single click, and synchronize them live into public job directories instantly!</div>
                </div>
            </div>
        </div>
    </div>

    <!-- C. Frequently Asked Questions (Accordion FAQ) Sub-tab -->
    <div class="sub-tab-content" id="info-faq" style="display: none;">
        <div class="glass-panel" style="padding: 1.75rem;">
            <h3 style="font-family:'Outfit'; color: var(--accent-color); margin-bottom: 0.5rem;" data-i18n="info_faq_tab">Frequently Asked Questions</h3>
            <p style="font-size: 0.9rem; color: var(--text-secondary);">Expand options below to understand GovJobs verification engines and registration policies.</p>
            
            <div class="accordion-wrapper">
                <div class="accordion-item">
                    <div class="accordion-header" data-i18n="faq_q1">Are all listed government job alerts verified?</div>
                    <div class="accordion-content" data-i18n="faq_a1">
                        Yes! Every announcement in our portal is scraped directly from authentic government domain resources (.gov.in / .nic.in) and cross-validated before listing.
                    </div>
                </div>
                <div class="accordion-item">
                    <div class="accordion-header" data-i18n="faq_q2">How does the mock OTP verification system work?</div>
                    <div class="accordion-content" data-i18n="faq_a2">
                        To recover your candidate account, click the 'Reset PW' tab in the authentication modal, input your email, and receive a simulated SMS code '123456' immediately to restore session rights.
                    </div>
                </div>
                <div class="accordion-item">
                    <div class="accordion-header" data-i18n="faq_q3">How can candidates update their alert preferences?</div>
                    <div class="accordion-content" data-i18n="faq_a3">
                        Candidates can sign in, open the 'Dashboard' section, go to the 'Profile Settings' tab, and toggle Email or SMS notifications checkbox configurations in real-time.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- D. Contact Helpdesk Sub-tab -->
    <div class="sub-tab-content" id="info-contact" style="display: none;">
        <div class="glass-panel" style="padding: 1.75rem; max-width: 600px; margin: 0 auto;">
            <h3 style="font-family:'Outfit'; color: var(--accent-color); margin-bottom: 0.5rem; text-align: center;" data-i18n="contact_title">Contact Portal Support Helpdesk</h3>
            <p style="font-size: 0.85rem; color: var(--text-secondary); text-align: center; margin-bottom: 1.5rem;" data-i18n="contact_desc">
                Have questions or spot a typo on a scraped recruitment feed? Send us a ticket.
            </p>
            <form id="ajaxContactForm">
                @csrf
                <div class="form-group">
                    <label for="contactName" data-i18n="contact_name_lbl">Your Name</label>
                    <input type="text" name="name" id="contactName" class="form-control" placeholder="Candidate Name" data-i18n="contact_name_placeholder" required>
                </div>
                <div class="form-group">
                    <label for="contactEmail" data-i18n="lbl_email_addr">Email Address</label>
                    <input type="email" name="email" id="contactEmail" class="form-control" placeholder="candidate@example.com" data-i18n="contact_email_placeholder" required>
                </div>
                <div class="form-group">
                    <label for="contactMessage" data-i18n="contact_msg_lbl">Support Message / Feedback</label>
                    <textarea name="message" id="contactMessage" class="form-control" rows="4" placeholder="Briefly describe your request..." data-i18n="contact_msg_placeholder" required></textarea>
                </div>
                <button type="submit" class="form-btn" id="contactSubmitBtn" data-i18n="contact_submit_btn">Submit Support Ticket</button>
            </form>
        </div>
    </div>
</div>

<!-- ======================= AUTH TAB PANELS (LOADED DYNAMICALLY) ======================= -->

<!-- TAB 3: CANDIDATE INTERACTIVE DASHBOARD -->
<div class="portal-main-tab" id="dashboard-section" style="display: none; padding: 0 5%; max-width: 1400px; margin: 0 auto;">
    <h2 style="font-size: 1.8rem; margin-bottom: 1.5rem; font-family: 'Outfit';" data-i18n="dash_title">Candidate Interactive Dashboard</h2>
    
    <div class="sub-tab-headers" style="margin-bottom: 1.5rem;">
        <button class="sub-tab-btn active dash-sub-trigger" data-target="dash-overview-block" data-i18n="dash_overview_tab">Workspace Overview</button>
        <button class="sub-tab-btn dash-sub-trigger" data-target="dash-settings-block" data-i18n="dash_settings_tab">Profile & Match Alerts Preferences</button>
        <button class="sub-tab-btn dash-sub-trigger" data-target="dash-membership-block" id="dashMembershipTabTrigger" data-i18n="dash_membership_tab">Premium Membership Plans</button>
    </div>

    <!-- Dash Block 1: Overview (Bookmarks and apps table) -->
    <div id="dash-overview-block" class="dash-block-panel">
        <div class="grid grid-cols-1 lg:grid-cols-[2fr_1fr]" style="gap: 2rem; align-items: start;">
            <div>
                <!-- Bookmarked items box -->
                <div class="glass-panel" style="padding: 1.5rem; margin-bottom: 2rem;">
                    <h3 style="font-size: 1.25rem; margin-bottom: 1rem; color: var(--accent-color); font-family: 'Outfit';" data-i18n="dash_saved_postings">Saved Recruitment Postings</h3>
                    <div class="responsive-table-container">
                        <table class="portal-table" id="dashboardBookmarksTable">
                            <thead>
                                <tr>
                                    <th data-i18n="lbl_job_title">Job Title</th>
                                    <th data-i18n="lbl_region">Region</th>
                                    <th data-i18n="lbl_deadline">Apply Deadline</th>
                                    <th style="text-align: center;" data-i18n="lbl_actions">Actions</th>
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
                    <h3 style="font-size: 1.25rem; margin-bottom: 1rem; color: #10b981; font-family: 'Outfit';" data-i18n="dash_submitted_apps">Submitted Applications & Recruiter Status</h3>
                    <div class="responsive-table-container">
                        <table class="portal-table" id="dashboardApplicationsTable">
                            <thead>
                                <tr>
                                    <th data-i18n="lbl_job_title">Recruitment Title</th>
                                    <th data-i18n="lbl_organization">Organization</th>
                                    <th data-i18n="lbl_date_submitted">Date Submitted</th>
                                    <th data-i18n="lbl_process_state">Process State</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Loaded via AJAX -->
                            </tbody>
                        </table>
                </div>

                <!-- Recently Viewed Recruitments box -->
                <div class="glass-panel" style="padding: 1.5rem; margin-top: 2rem;">
                    <h3 style="font-size: 1.25rem; margin-bottom: 1rem; color: #8b5cf6; font-family: 'Outfit';" data-i18n="dash_recently_viewed">Recently Viewed Recruitments</h3>
                    <div class="responsive-table-container">
                        <table class="portal-table" id="dashboardRecentlyViewedTable">
                            <thead>
                                <tr>
                                    <th data-i18n="lbl_job_title">Recruitment Title</th>
                                    <th data-i18n="lbl_region">Region</th>
                                    <th data-i18n="lbl_deadline">Apply Deadline</th>
                                    <th style="text-align: center;" data-i18n="lbl_actions">Actions</th>
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
                <h3 style="font-size: 1.25rem; margin-bottom: 1.5rem; font-family: 'Outfit';" data-i18n="dash_profile_stats">Profile Statistics</h3>
                
                <div class="stats-grid" style="grid-template-columns: 1fr; gap: 1rem;">
                    <div class="glass-panel stat-card" style="background: var(--bg-primary); border: 1px solid var(--border-color);">
                        <div class="stat-num" id="statsTotalBookmarks">0</div>
                        <div class="stat-label" data-i18n="dash_saved_count">Saved Recruitments</div>
                    </div>
                    <div class="glass-panel stat-card" style="background: var(--bg-primary); border: 1px solid var(--border-color);">
                        <div class="stat-num" id="statsTotalApplications" style="color: #10b981;">0</div>
                        <div class="stat-label" data-i18n="dash_submitted_count">Submitted Applications</div>
                    </div>
                </div>

                <div style="border-top: 1px solid var(--border-color); padding-top: 1.25rem; margin-top: 1.25rem; font-size: 0.9rem; color: var(--text-secondary);">
                    <p><strong>Candidate:</strong> <span id="dashCandidateName" translate="no" class="notranslate" style="color: var(--text-primary);">John Doe</span></p>
                    <p><strong>Email:</strong> <span id="dashCandidateEmail" translate="no" class="notranslate">candidate@example.com</span></p>
                    <p><strong>Phone:</strong> <span id="dashCandidatePhone" translate="no" class="notranslate">Not Verified</span></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Dash Block 2: Profile Settings Form -->
    <div id="dash-settings-block" class="dash-block-panel" style="display: none;">
        <div class="glass-panel" style="padding: 2rem; max-width: 700px; margin: 0 auto;">
            <h3 style="font-family:'Outfit'; color: var(--accent-color); margin-bottom: 1.5rem; text-align: center;" data-i18n="dash_update_profile">Update Profile Settings & Preferences</h3>
            
            <form id="ajaxProfileUpdateForm" translate="no" class="notranslate" style="margin-bottom: 2rem; border-bottom: 1px solid var(--border-color); padding-bottom: 2rem;">
                @csrf
                <div class="form-group">
                    <label for="profileName" data-i18n="lbl_full_name">Full Name</label>
                    <input type="text" name="name" id="profileName" class="form-control" required>
                    <div class="invalid-feedback" id="profileNameError"></div>
                </div>
                <div class="form-group">
                    <label for="profileEmail" data-i18n="lbl_email_addr">Email Address</label>
                    <input type="email" name="email" id="profileEmail" class="form-control" required>
                    <div class="invalid-feedback" id="profileEmailError"></div>
                </div>
                <div class="form-group">
                    <label for="profilePhone" data-i18n="lbl_phone_num">Phone Number</label>
                    <input type="text" name="phone" id="profilePhone" class="form-control" required>
                    <div class="invalid-feedback" id="profilePhoneError"></div>
                </div>
                
                <div style="background: rgba(37,99,235,0.03); padding: 1rem; border-radius: 8px; border: 1px dashed var(--border-color); margin: 1.5rem 0;">
                    <p style="font-size:0.8rem; color:var(--text-secondary); margin-bottom:1rem;" data-i18n="dash_pass_blank">Leave password fields blank if you do not want to alter credentials.</p>
                    <div class="form-group">
                        <label for="profilePassword" data-i18n="lbl_new_pass">New Password (Min 6 chars)</label>
                        <input type="password" name="password" id="profilePassword" class="form-control" placeholder="••••••••">
                        <div class="invalid-feedback" id="profilePasswordError"></div>
                    </div>
                    <div class="form-group">
                        <label for="profilePasswordConfirm" data-i18n="lbl_confirm_pass">Confirm New Password</label>
                        <input type="password" name="password_confirmation" id="profilePasswordConfirm" class="form-control" placeholder="••••••••">
                    </div>
                </div>

                <button type="submit" class="form-btn" id="profileUpdateSubmitBtn" data-i18n="btn_sync_profile">Synchronize Profile Settings</button>
            </form>

            <form id="ajaxPreferencesForm">
                @csrf
                <h4 style="font-family:'Outfit'; font-size:1.1rem; color:var(--text-primary); margin-bottom:1rem;" data-i18n="dash_alert_channels">Real-time Recruitment Alert Channels</h4>
                
                <div class="alert-preference-row">
                    <div>
                        <strong data-i18n="dash_email_alerts">Email Match Notifications</strong><br>
                        <span style="font-size:0.8rem; color:var(--text-secondary);" data-i18n="dash_email_alerts_desc">Receive validation notifications daily on active categories.</span>
                    </div>
                    <input type="checkbox" name="email_alerts" id="prefEmailAlerts" value="1" checked style="width: 20px; height: 20px; cursor: pointer;">
                </div>
                
                <div class="alert-preference-row" style="border-bottom:none; margin-bottom: 1.5rem;">
                    <div>
                        <strong data-i18n="dash_sms_alerts">SMS Verification Alerts</strong><br>
                        <span style="font-size:0.8rem; color:var(--text-secondary);" data-i18n="dash_sms_alerts_desc">Send live SMS reminders 24 hours prior to apply deadlines.</span>
                    </div>
                    <input type="checkbox" name="sms_alerts" id="prefSmsAlerts" value="1" checked style="width: 20px; height: 20px; cursor: pointer;">
                </div>

                <button type="submit" class="form-btn" id="preferencesSubmitBtn" style="background:#10b981;" data-i18n="btn_save_preferences">Save Notification Preferences</button>
            </form>
        </div>
    </div>

    <!-- Dash Block 3: Membership Plans & Upgrades -->
    <div id="dash-membership-block" class="dash-block-panel" style="display: none;">
        <div class="glass-panel" style="padding: 2rem; max-width: 800px; margin: 0 auto;">
            <h3 style="font-family:'Outfit'; color: var(--accent-color); margin-bottom: 0.5rem; text-align: center;" data-i18n="dash_membership_tab">Premium Membership Plans</h3>
            <p style="font-size:0.9rem; color:var(--text-secondary); text-align:center; margin-bottom:2rem;" data-i18n-html="membership_desc">
                Unlock advanced automation alerts, early results access, and a completely <strong>ad-free experience</strong>.
            </p>

            <div style="display: flex; gap: 1rem; margin-bottom: 2rem; align-items: stretch; justify-content: center; flex-wrap: wrap;">
                <!-- Plan 1: Free -->
                <div class="glass-panel" style="flex: 1; min-width: 220px; padding: 1.5rem; display: flex; flex-direction: column; justify-content: space-between; border-top: 4px solid var(--text-secondary);">
                    <div>
                        <h4 style="font-family:'Outfit'; font-size:1.15rem; margin-bottom:0.5rem;" data-i18n="plan_free">Basic Free Plan</h4>
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
                        <h4 style="font-family:'Outfit'; font-size:1.15rem; margin-bottom:0.5rem;" data-i18n="plan_premium">Premium Candidate</h4>
                        <div style="font-size:1.5rem; font-weight:800; margin-bottom:1rem; color:var(--accent-color);">₹299 <span style="font-size:0.8rem; font-weight:normal;">/ month</span></div>
                        <ul style="list-style:none; padding:0; margin:0; display:grid; gap:0.5rem; font-size:0.82rem; color:var(--text-secondary);">
                            <li><strong>✓ Completely Ad-Free Experience</strong></li>
                            <li>✓ Instant WhatsApp/SMS alerts</li>
                            <li>✓ Early Access to Exam Results</li>
                            <li>✓ Automated study guide matching</li>
                        </ul>
                    </div>
                    <div style="margin-top:1.5rem;">
                        <button class="form-btn select-membership-plan-btn" data-plan="premium" id="btnPremiumPlanIndicator" style="width:100%; margin:0; padding:0.6rem;" data-i18n="btn_upgrade_premium">Upgrade Premium</button>
                    </div>
                </div>

                <!-- Plan 3: Pro -->
                <div class="glass-panel" style="flex: 1; min-width: 220px; padding: 1.5rem; display: flex; flex-direction: column; justify-content: space-between; border-top: 4px solid #10b981; background: rgba(16,185,129,0.02);">
                    <div>
                        <h4 style="font-family:'Outfit'; font-size:1.15rem; margin-bottom:0.5rem;" data-i18n="plan_pro">Pro Professional</h4>
                        <div style="font-size:1.5rem; font-weight:800; margin-bottom:1rem; color:#10b981;">₹599 <span style="font-size:0.8rem; font-weight:normal;">/ month</span></div>
                        <ul style="list-style:none; padding:0; margin:0; display:grid; gap:0.5rem; font-size:0.82rem; color:var(--text-secondary);">
                            <li><strong>✓ Completely Ad-Free Experience</strong></li>
                            <li>✓ Priority SMS and Call reminders</li>
                            <li>✓ Access to premium Test Series</li>
                            <li>✓ Downloadable PDF Syllabus guides</li>
                        </ul>
                    </div>
                    <div style="margin-top:1.5rem;">
                        <button class="form-btn select-membership-plan-btn" data-plan="pro" id="btnProPlanIndicator" style="width:100%; margin:0; padding:0.6rem; background:#10b981;" data-i18n="btn_upgrade_pro">Upgrade Pro</button>
                    </div>
                </div>
            </div>

            <!-- UPI/Credit Card Simulated Payment Panel (hidden by default) -->
            <div id="simulatedPaymentPanel" style="display: none; border-top: 1px solid var(--border-color); padding-top: 1.5rem; margin-top: 1.5rem;">
                <h4 style="font-family:'Outfit'; font-size:1.1rem; color:var(--accent-color); margin-bottom:1rem; text-align:center;">Secure Mock Checkout Interface</h4>
                <form id="ajaxSimulatedCheckoutForm" translate="no" class="notranslate" style="max-width: 450px; margin: 0 auto;">
                    @csrf
                    <input type="hidden" id="checkoutTargetPlan" name="plan">
                    
                    <div class="form-group">
                        <label for="mockPaymentMethod">Payment Gateway Mode</label>
                        <select id="mockPaymentMethod" class="form-control">
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
        $(document).on('click', '.sub-tab-btn[data-sub]', function(e) {
            e.preventDefault();
            $(this).siblings().removeClass('active');
            $(this).addClass('active');
            const targetSub = $(this).data('sub');
            $(`#${targetSub}`).siblings('.sub-tab-content').hide();
            $(`#${targetSub}`).fadeIn();
        });

        // Sub-tabs transitions inside Candidate Dashboard settings
        $(document).on('click', '.dash-sub-trigger', function(e) {
            e.preventDefault();
            $('.dash-sub-trigger').removeClass('active');
            $(this).addClass('active');
            const targetBlock = $(this).data('target');
            $('.dash-block-panel').hide();
            $(`#${targetBlock}`).fadeIn();
        });

        // Sub-tabs transitions inside Administration Control panels
        $(document).on('click', '.admin-sub-trigger', function(e) {
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
        } else if (currentHash === '#info-hub-section') {
            $('.nav-tab-trigger[data-target="info-hub"]').trigger('click');
        } else if (currentHash === '#jobs-search-section') {
            $('.nav-tab-trigger[data-target="jobs"]').trigger('click');
        }

        // ================== SEARCH AND PAGINATION SYSTEM ==================
        let currentPage = 1;

        // Fetch jobs from server
        function fetchJobs(page = 1) {
            currentPage = page;
            const queryData = {
                search: $('#searchKeywords').val(),
                state_id: $('#stateSelect').val(),
                qualification_id: $('#qualificationSelect').val(),
                category_id: $('#categorySelect').val(),
                has_no_fee: $('#noFeeCheck').is(':checked'),
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

                        $('#jobsCountFeedback').text(window.t('found_jobs', 'Found {count} recruitments').replace('{count}', data.total));

                        if (jobs.length === 0) {
                            $('#jobsListContainer').html(`
                                <div class="glass-panel" style="padding: 3rem; text-align: center; color: var(--text-secondary);">
                                    ${window.t('no_match_criteria', 'No recruitment postings match your exact search criteria. Try modifying your filters.')}
                                </div>
                            `).fadeIn();
                            return;
                        }

                        // Rebuild HTML cards dynamically
                        let html = '';
                        jobs.forEach(function(job) {
                            const isFeaturedBadge = job.is_featured ? `<span class="badge" style="background:var(--accent-color); color:#fff; font-size:0.75rem;">${window.t('badge_featured', 'FEATURED')}</span>` : '';
                            const isSponsoredBadge = job.is_sponsored ? `<span class="badge badge-sponsored">${window.t('badge_sponsored', 'SPONSORED')}</span>` : '';
                            const sponsoredClass = job.is_sponsored ? 'is-sponsored' : '';
                            const featuredClass = job.is_featured ? 'featured-premium' : '';
                            const applyTarget = job.affiliate_link ? `/go/${job.slug}` : `#`;

                            html += `
                                <div class="glass-panel job-card ${sponsoredClass} ${featuredClass}">
                                    <div class="job-info">
                                        <h3 style="display:flex; align-items:center; gap:0.5rem;" title="${job.title}">
                                            <span class="notranslate" translate="no" data-translate-title="${job.title}">${window.translateJobTitle(job.title)}</span> 
                                            ${isSponsoredBadge}
                                            ${isFeaturedBadge}
                                        </h3>
                                        <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.5rem;">
                                            ${window.t(job.department, job.department)} &bull; ${window.t(job.state, job.state)}
                                        </p>
                                        <div class="job-tags">
                                            <span class="badge badge-dept">🎓 ${window.t(job.qualification, job.qualification)}</span>
                                            <span class="badge" style="background: rgba(16, 185, 129, 0.08); color: #10b981;">👥 ${window.t('vacancies_count', 'Vacancies')}: ${Number(job.vacancy_count).toLocaleString('en-IN')}</span>
                                            <span class="badge badge-deadline">📅 ${window.t('apply_by', 'Apply by')}: ${job.last_date}</span>
                                        </div>
                                    </div>
                                    <div class="job-card-actions">
                                        <a href="${applyTarget}" class="btn-view" data-slug="${job.slug}" aria-label="View details for ${job.title}">${window.t('btn_view_details', 'View Details')}</a>
                                        @auth
                                            <button class="toggle-bookmark-btn" data-id="${job.id}" aria-label="Save ${job.title}">
                                                ${window.t('btn_save_job', 'Save Job')}
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
                            ${window.t('system_error', 'System error occurred! Could not synchronize listings. Please try again.')}
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
                html += `<a href="#" class="page-link" data-page="${current - 1}">&laquo; ${window.t('btn_prev', 'Prev')}</a>`;
            }
            for (let i = 1; i <= last; i++) {
                const activeClass = i === current ? 'active' : '';
                html += `<a href="#" class="page-link ${activeClass}" data-page="${i}">${i}</a>`;
            }
            if (current < last) {
                html += `<a href="#" class="page-link" data-page="${current + 1}">${window.t('btn_next', 'Next')} &raquo;</a>`;
            }
            $('#paginationContainer').html(html);
        }

        // Initialize custom searchable dropdowns
        function initSearchableDropdown(wrapperId, hiddenSelectId, placeholderText) {
            const $wrapper = $(wrapperId);
            const $hiddenSelect = $(hiddenSelectId);
            const $selectedText = $wrapper.find('.selected-text');
            const $selectedDiv = $wrapper.find('.dropdown-selected');
            const $searchInput = $wrapper.find('.dropdown-search-input');
            const $panel = $wrapper.find('.dropdown-panel');
            const $list = $wrapper.find('.dropdown-list');
            const defaultI18nKey = $selectedText.attr('data-i18n') || '';

            function populateOptions() {
                $list.empty();
                $hiddenSelect.find('option').each(function() {
                    const val = $(this).val();
                    let text = $(this).text().trim();
                    if (!val) {
                        text = placeholderText;
                    }
                    const isSelected = $(this).is(':selected');
                    const lookup = $(this).attr('data-translate-lookup');
                    const prefix = $(this).attr('data-translate-prefix');
                    const suffix = $(this).attr('data-translate-suffix');
                    const i18nKey = $(this).attr('data-i18n');
                    
                    const optionAttr = {
                        class: 'dropdown-option' + (isSelected ? ' selected' : ''),
                        'data-value': val,
                        text: text,
                        role: 'option',
                        tabindex: '-1'
                    };
                    
                    if (lookup) { optionAttr['data-translate-lookup'] = lookup; }
                    if (prefix) { optionAttr['data-translate-prefix'] = prefix; }
                    if (suffix) { optionAttr['data-translate-suffix'] = suffix; }
                    if (i18nKey) { optionAttr['data-i18n'] = i18nKey; }

                    const $option = $('<div>', optionAttr);
                    $list.append($option);
                    if (isSelected) {
                        $selectedText.text(text);
                        if (val) {
                            $selectedText.removeAttr('data-i18n');
                        } else {
                            if (defaultI18nKey) {
                                $selectedText.attr('data-i18n', defaultI18nKey);
                            }
                        }
                        if (lookup) {
                            $selectedText.attr('data-translate-lookup', lookup);
                        } else {
                            $selectedText.removeAttr('data-translate-lookup');
                        }
                        if (prefix) {
                            $selectedText.attr('data-translate-prefix', prefix);
                        } else {
                            $selectedText.removeAttr('data-translate-prefix');
                        }
                        if (suffix) {
                            $selectedText.attr('data-translate-suffix', suffix);
                        } else {
                            $selectedText.removeAttr('data-translate-suffix');
                        }
                    }
                });
            }

            populateOptions();

            // Toggle panel
            $selectedDiv.on('click', function(e) {
                e.stopPropagation();
                const isOpen = $wrapper.hasClass('open');
                $('.searchable-dropdown').not($wrapper).removeClass('open');
                $wrapper.toggleClass('open');
                if (!isOpen) {
                    $searchInput.val('').trigger('input');
                    $searchInput.focus();
                }
            });

            // Handle option click
            $list.on('click', '.dropdown-option', function(e) {
                e.stopPropagation();
                const val = $(this).attr('data-value') || '';
                const text = $(this).text();
                $hiddenSelect.val(val).trigger('change');
                $selectedText.text(text);
                $wrapper.removeClass('open');
                $selectedDiv.focus();
            });

            // Handle search input filtering
            $searchInput.on('input', function() {
                const query = $(this).val().toLowerCase();
                $list.find('.dropdown-option').each(function() {
                    const text = $(this).text().toLowerCase();
                    if (text.includes(query)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            // Keyboard accessibility
            $selectedDiv.on('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    $(this).trigger('click');
                }
            });

            $searchInput.on('keydown', function(e) {
                const $visibleOptions = $list.find('.dropdown-option:visible');
                let activeIdx = $visibleOptions.index($list.find('.dropdown-option.highlighted'));
                
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    activeIdx = (activeIdx + 1) % $visibleOptions.length;
                    $visibleOptions.removeClass('highlighted');
                    $visibleOptions.eq(activeIdx).addClass('highlighted');
                    $visibleOptions.eq(activeIdx)[0].scrollIntoView({ block: 'nearest' });
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    activeIdx = (activeIdx - 1 + $visibleOptions.length) % $visibleOptions.length;
                    $visibleOptions.removeClass('highlighted');
                    $visibleOptions.eq(activeIdx).addClass('highlighted');
                    $visibleOptions.eq(activeIdx)[0].scrollIntoView({ block: 'nearest' });
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    const $highlighted = $list.find('.dropdown-option.highlighted');
                    if ($highlighted.length > 0) {
                        $highlighted.trigger('click');
                    } else if ($visibleOptions.length > 0) {
                        $visibleOptions.first().trigger('click');
                    }
                } else if (e.key === 'Escape') {
                    $wrapper.removeClass('open');
                    $selectedDiv.focus();
                }
            });

            // Sync from hidden select changes
            $hiddenSelect.on('change', function() {
                const val = $(this).val();
                let selectedTextVal = placeholderText;
                let activeLookup = null;
                let activePrefix = null;
                let activeSuffix = null;
                
                $list.find('.dropdown-option').removeClass('selected').each(function() {
                    if (($(this).attr('data-value') || '') == val) {
                        $(this).addClass('selected');
                        selectedTextVal = $(this).text();
                        activeLookup = $(this).attr('data-translate-lookup');
                        activePrefix = $(this).attr('data-translate-prefix');
                        activeSuffix = $(this).attr('data-translate-suffix');
                    }
                });
                
                $selectedText.text(selectedTextVal);
                if (val) {
                    $selectedText.removeAttr('data-i18n');
                } else {
                    if (defaultI18nKey) {
                        $selectedText.attr('data-i18n', defaultI18nKey);
                    }
                }
                
                if (activeLookup) {
                    $selectedText.attr('data-translate-lookup', activeLookup);
                } else {
                    $selectedText.removeAttr('data-translate-lookup');
                }
                if (activePrefix) {
                    $selectedText.attr('data-translate-prefix', activePrefix);
                } else {
                    $selectedText.removeAttr('data-translate-prefix');
                }
                if (activeSuffix) {
                    $selectedText.attr('data-translate-suffix', activeSuffix);
                } else {
                    $selectedText.removeAttr('data-translate-suffix');
                }
            });
        }

        // Initialize custom dropdowns
        initSearchableDropdown('#stateDropdownWrapper', '#stateSelect', 'All Regions');
        initSearchableDropdown('#qualificationDropdownWrapper', '#qualificationSelect', 'All Qualifications');
        initSearchableDropdown('#categoryDropdownWrapper', '#categorySelect', 'All Streams');

        // Render initial chips on load
        updateFilterChips();

        // Show clear search button on load if keywords exist
        if ($('#searchKeywords').val()) {
            $('#clearSearchBtn').show();
        }

        // Auto-fetch jobs if any query/filter is pre-selected on load
        if ($('#categorySelect').val() || $('#stateSelect').val() || $('#qualificationSelect').val() || $('#noFeeCheck').is(':checked') || $('#searchKeywords').val()) {
            fetchJobs(1);
        }

        // Close dropdowns clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.searchable-dropdown').length) {
                $('.searchable-dropdown').removeClass('open');
            }
        });

        // Advanced filter drawer toggle
        $('#toggleAdvancedFiltersBtn').on('click', function() {
            $('#advancedDrawer, #advancedDrawerOverlay').addClass('open');
            $(this).addClass('active');
            $('#advancedDrawer').find('select, input').first().focus();
        });

        $('#closeDrawerBtn, #advancedDrawerOverlay, #applyFiltersDrawerBtn').on('click', function() {
            $('#advancedDrawer, #advancedDrawerOverlay').removeClass('open');
            $('#toggleAdvancedFiltersBtn').removeClass('active');
        });

        // Selected Active Filter tag chips manager
        function updateFilterChips() {
            const container = $('#activeFilterChipsContainer');
            container.empty();
            let hasFilters = false;

            const stateVal = $('#stateSelect').val();
            if (stateVal) {
                const text = $(`#stateSelect option[value="${stateVal}"]`).text().trim();
                container.append(`<div class="active-filter-chip">📍 ${text} <span class="remove-filter-btn" data-type="state" role="button" aria-label="Remove ${text} filter">&times;</span></div>`);
                hasFilters = true;
            }

            const qualVal = $('#qualificationSelect').val();
            if (qualVal) {
                const text = $(`#qualificationSelect option[value="${qualVal}"]`).text().trim();
                container.append(`<div class="active-filter-chip">🎓 ${text} <span class="remove-filter-btn" data-type="qualification" role="button" aria-label="Remove ${text} filter">&times;</span></div>`);
                hasFilters = true;
            }

            const catVal = $('#categorySelect').val();
            if (catVal) {
                const text = $(`#categorySelect option[value="${catVal}"]`).text().trim();
                container.append(`<div class="active-filter-chip">🏢 ${text} <span class="remove-filter-btn" data-type="category" role="button" aria-label="Remove ${text} filter">&times;</span></div>`);
                hasFilters = true;
            }

            const noFeeVal = $('#noFeeCheck').is(':checked');
            if (noFeeVal) {
                container.append(`<div class="active-filter-chip">💸 Free Application <span class="remove-filter-btn" data-type="nofee" role="button" aria-label="Remove free applications filter">&times;</span></div>`);
                hasFilters = true;
            }

            if (hasFilters) {
                container.append(`<a href="#" id="clearAllFiltersBtn" style="font-size: 0.85rem; color: #ef4444; font-weight: 600; text-decoration: none; margin-left: 0.5rem;">Clear All</a>`);
            }
        }

        // Dismiss active filter chips handler
        $('#activeFilterChipsContainer').on('click', '.remove-filter-btn', function() {
            const type = $(this).data('type');
            if (type === 'state') {
                $('#stateSelect').val('').trigger('change');
            } else if (type === 'qualification') {
                $('#qualificationSelect').val('').trigger('change');
            } else if (type === 'category') {
                $('#categorySelect').val('').trigger('change');
            } else if (type === 'nofee') {
                $('#noFeeCheck').prop('checked', false).trigger('change');
            }
        });

        $(document).on('click', '#clearAllFiltersBtn', function(e) {
            e.preventDefault();
            $('#resetFiltersTriggerBtn').trigger('click');
        });

        // Trigger filters on changes
        $('#stateSelect, #qualificationSelect, #categorySelect, #noFeeCheck').on('change', function() {
            updateFilterChips();
            fetchJobs(1);
        });

        // Try searching suggestions click
        $(document).on('click', '.suggestion-chip-item', function() {
            const query = $(this).data('query');
            $('#searchKeywords').val(query);
            $('#clearSearchBtn').show();
            fetchJobs(1);
        });

        // Submit search on button click
        $('#searchSubmitBtn').on('click', function() {
            clearTimeout(searchTimeout);
            fetchJobs(1);
        });

        // Submit search on Enter keypress
        $('#searchKeywords').on('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(searchTimeout);
                fetchJobs(1);
                $('#autocompleteDropdown').hide();
            }
        });

        // Search Input Keyup Debouncing
        let searchTimeout = null;
        let autocompleteTimeout = null;

        $('#searchKeywords').on('input keyup', function() {
            const query = $(this).val();

            if (query.length > 0) {
                $('#clearSearchBtn').show();
            } else {
                $('#clearSearchBtn').hide();
            }

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
                                        <span>${window.t('did_you_mean', 'Did you mean:')} <a id="homeSuggestedQueryLink" href="#" data-query="${res.data.suggestion}">${res.data.suggestion}</a> ?</span>
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

        // Clear search text
        $(document).on('click', '#clearSearchBtn', function() {
            $('#searchKeywords').val('');
            $(this).hide();
            $('#autocompleteDropdown').hide().empty();
            $('#homeTypoBanner').hide().empty();
            fetchJobs(1);
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
            $('#clearSearchBtn').show();
            fetchJobs(1);
        });

        // Reset all filters trigger
        $('#resetFiltersTriggerBtn').on('click', function() {
            $('#searchKeywords').val('');
            $('#stateSelect').val('').trigger('change');
            $('#qualificationSelect').val('').trigger('change');
            $('#categorySelect').val('').trigger('change');
            $('#noFeeCheck').prop('checked', false).trigger('change');
            $('#clearSearchBtn').hide();
            $('#autocompleteDropdown').hide().empty();
            $('#homeTypoBanner').hide().empty();
            $('#advancedDrawer, #advancedDrawerOverlay').removeClass('open');
            $('#toggleAdvancedFiltersBtn').removeClass('active');
            fetchJobs(1);
        });

        $('#resetFiltersDrawerBtn').on('click', function() {
            $('#resetFiltersTriggerBtn').trigger('click');
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
                        btn.text(window.t('btn_remove_save', 'Remove Save')).css({'color': '#ef4444', 'border-color': 'rgba(239,68,68,0.15)', 'background': 'rgba(239,68,68,0.06)'});
                    } else {
                        btn.text(window.t('btn_save_job', 'Save Job')).css({'color': 'var(--accent-color)', 'border-color': 'rgba(37,99,235,0.15)', 'background': 'rgba(37,99,235,0.06)'});
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
            const slug = $(this).data('slug');
            if (!slug) return;
            e.preventDefault();

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
                        
                        // Sanitize and clean description to avoid duplication and format newlines
                        let cleanDescription = job.description || '';
                        cleanDescription = cleanDescription.replace(/(📅|🗓️)?\s*Important Dates[\s\S]*?(?=(₹|💵)?\s*Application Fee|(📅|⏰)?\s*Age Limit|Selection Process|Vacancy|Overview|$)/gi, '');
                        cleanDescription = cleanDescription.replace(/(₹|💵)?\s*Application Fee[\s\S]*?(?=(📅|⏰)?\s*Age Limit|Selection Process|Vacancy|Overview|$)/gi, '');
                        cleanDescription = cleanDescription.replace(/(📅|⏰)?\s*Age Limit Details[\s\S]*?(?=Selection Process|Vacancy|Overview|$)/gi, '');
                        cleanDescription = cleanDescription.trim().replace(/\n/g, '<br>');
                        cleanDescription = cleanDescription.replace(/(<br>\s*){2,}/g, '<br><br>');
                        cleanDescription = cleanDescription.replace(/^(<br>\s*)+|(<br>\s*)+$/g, '');
                        
                        if (!cleanDescription || cleanDescription.replace(/<br>/g, '').trim().length < 5) {
                            cleanDescription = `Recruitment notification details for <strong>${job.title}</strong> in the ${job.department} department. Please read the official notification PDF and ensure your eligibility before submitting your application.`;
                        }

                        let selectionProcessHtml = (job.selection_process || 'Written Exam.').trim().replace(/\n/g, '<br>').replace(/(<br>\s*){2,}/g, '<br><br>');
                        let examPatternHtml = (job.exam_pattern || 'Objective MCQs.').trim().replace(/\n/g, '<br>').replace(/(<br>\s*){2,}/g, '<br><br>');

                        const type = job.post_type;
                        let html = '';
                        if (type === 'job') {
                            // Determine status badge values
                            let statusText = 'Active';
                            let statusClass = 'status-open';
                            
                            const now = new Date();
                            const lastDate = job.last_date ? new Date(job.last_date) : null;
                            const startDate = job.start_date ? new Date(job.start_date) : null;
                            
                            if (lastDate && lastDate < now) {
                                statusText = 'Apply Closed';
                                statusClass = 'status-closed';
                            } else if (startDate && startDate > now) {
                                statusText = 'Upcoming';
                                statusClass = 'status-upcoming';
                            } else {
                                statusText = 'Apply Open';
                                statusClass = 'status-open';
                            }

                            // Build Application Fee HTML
                            let feeHtml = '';
                            if (job.application_fee > 0) {
                                feeHtml = `
                                    <li>
                                        <span class="info-label">General / OBC / EWS:</span>
                                        <span class="info-val">₹ ${parseFloat(job.application_fee).toFixed(2)}</span>
                                    </li>
                                    <li>
                                        <span class="info-label">SC / ST / PH:</span>
                                        <span class="info-val">₹ 0.00 (Exempted)</span>
                                    </li>
                                    <li>
                                        <span class="info-label">Females (All Category):</span>
                                        <span class="info-val">₹ 0.00 (Exempted)</span>
                                    </li>
                                `;
                            } else {
                                feeHtml = `
                                    <li>
                                        <span class="info-label">All Category Candidates:</span>
                                        <span class="info-val text-success">Free (No Fee)</span>
                                    </li>
                                `;
                            }

                            // Build Timeline Nodes HTML
                            let timelineHtml = '';
                            if (job.timeline && job.timeline.length > 1) {
                                timelineHtml = `
                                    <div class="details-full-section" style="margin-top:1.5rem; border-top:1px solid var(--border-color); padding-top:1.25rem;">
                                        <h4 style="color:var(--accent-color); font-weight:700; font-family:'Outfit'; margin-bottom:0.75rem;"><i class="fa-solid fa-code-fork"></i> Recruitment Lifecycle & Update Timeline</h4>
                                        <div class="timeline-container">
                                            ${job.timeline.map(item => {
                                                const isCurrent = (item.id === job.id);
                                                let itemType = item.post_type;
                                                if (itemType === 'job') itemType = 'Original Announcement';
                                                else if (itemType === 'admit_card') itemType = 'Admit Card Available';
                                                else if (itemType === 'result') itemType = 'Final Exam Results';
                                                else if (itemType === 'answer_key') itemType = 'Answer Key Objections';
                                                else if (itemType === 'syllabus') itemType = 'Exam Syllabus Published';
                                                else if (itemType === 'cutoff') itemType = 'Declared Cutoffs';
                                                else if (itemType === 'notice') itemType = 'Official Notice';
                                                else itemType = itemType.charAt(0).toUpperCase() + itemType.slice(1);

                                                let iconHtml = '<i class="fa-solid fa-circle-info"></i>';
                                                if (item.post_type === 'job') iconHtml = '<i class="fa-solid fa-bullhorn"></i>';
                                                else if (item.post_type === 'admit_card') iconHtml = '<i class="fa-solid fa-id-card"></i>';
                                                else if (item.post_type === 'result') iconHtml = '<i class="fa-solid fa-trophy"></i>';
                                                else if (item.post_type === 'answer_key') iconHtml = '<i class="fa-solid fa-key"></i>';
                                                else if (item.post_type === 'syllabus') iconHtml = '<i class="fa-solid fa-book-open"></i>';

                                                return `
                                                    <div class="timeline-node ${isCurrent ? 'current-node' : ''}">
                                                        <div class="node-icon">${iconHtml}</div>
                                                        <div class="node-content">
                                                            <span class="node-date">${item.published_at}</span>
                                                            <h5 class="node-title">
                                                                ${isCurrent ? `<strong>${itemType}: ${item.title} (This Popup)</strong>` : `<a href="#" class="btn-view" data-slug="${item.slug}">${itemType}: ${item.title}</a>`}
                                                            </h5>
                                                        </div>
                                                    </div>
                                                `;
                                            }).join('')}
                                        </div>
                                    </div>
                                `;
                            }

                            // Build Guidelines Photo Rule conditional list
                            let sscPhotoGuideline = '';
                            if (job.title.toLowerCase().includes('ssc') || job.description.toLowerCase().includes('webcam') || job.description.toLowerCase().includes('live photo')) {
                                sscPhotoGuideline = `
                                    <li style="display: flex; gap: 0.75rem; font-size: 0.95rem; color: var(--text-secondary); line-height: 1.6;">
                                        <span style="color:var(--accent-color); font-weight:bold;">2.</span>
                                        <span><strong>Webcam Live Photograph:</strong> SSC and other major commissions require taking a live photo of yourself via webcam or through the official mobile app. Stand in front of a light/white background and look straight. Do not wear caps, spectacles, or masks.</span>
                                    </li>
                                `;
                            }

                            // PDF Link logic
                            let pdfRow = '';
                            if (job.notification_pdf_path) {
                                const pdfUrl = job.notification_pdf_path.startsWith('http')
                                    ? job.notification_pdf_path
                                    : `/storage/${job.notification_pdf_path}`;
                                pdfRow = `
                                    <tr>
                                        <td><strong>Download Official Notification PDF</strong></td>
                                        <td style="text-align: right;">
                                            <a href="${pdfUrl}" target="_blank" class="btn-link-action" style="background:#dc2626;">
                                                <i class="fa-solid fa-file-pdf"></i> Download PDF
                                            </a>
                                        </td>
                                    </tr>
                                `;
                            }

                            let boardName = job.department || 'Government Ministry';
                            let boardShort = 'GOVT';
                            let boardColor = 'var(--accent-color)';
                            let bnLower = boardName.toLowerCase();
                            if (bnLower.includes('staff selection') || bnLower.includes('ssc')) {
                                boardShort = 'SSC';
                                boardColor = 'linear-gradient(135deg, #1e40af 0%, #3b82f6 100%)';
                            } else if (bnLower.includes('union public') || bnLower.includes('upsc')) {
                                boardShort = 'UPSC';
                                boardColor = 'linear-gradient(135deg, #78350f 0%, #d97706 100%)';
                            } else if (bnLower.includes('railway') || bnLower.includes('rrb')) {
                                boardShort = 'RRB';
                                boardColor = 'linear-gradient(135deg, #991b1b 0%, #dc2626 100%)';
                            } else if (bnLower.includes('public service commission') || bnLower.includes('psc')) {
                                boardShort = 'PSC';
                                boardColor = 'linear-gradient(135deg, #065f46 0%, #10b981 100%)';
                            } else if (bnLower.includes('police')) {
                                boardShort = 'POLICE';
                                boardColor = 'linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 100%)';
                            } else {
                                let words = boardName.split(' ').filter(w => w);
                                if (words.length >= 2) {
                                    boardShort = (words[0].charAt(0) + words[1].charAt(0)).toUpperCase();
                                } else {
                                    boardShort = boardName.substring(0, 2).toUpperCase();
                                }
                                boardColor = 'linear-gradient(135deg, rgba(37, 99, 235, 0.2) 0%, rgba(139, 92, 246, 0.2) 100%)';
                            }

                            html = `
                                <article class="detail-card" style="margin: 0; box-shadow: none; border: none; padding: 0.5rem; background: transparent; -webkit-backdrop-filter: none; backdrop-filter: none;">
                                    <header class="detail-header-block">
                                        <div class="detail-header-main">
                                            <h1 class="notranslate" translate="no" data-translate-title="${job.title}" style="font-size:1.8rem; margin-bottom: 0;">${window.translateJobTitle(job.title)}</h1>
                                        </div>
                                        <div class="detail-badges">
                                            <span class="status-badge ${statusClass}">${statusText}</span>
                                            <span class="badge">${type.toUpperCase()}</span>
                                            <span class="badge badge-dept">${job.department || 'Government Ministry'}</span>
                                            <span class="badge" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;">📍 ${job.state || 'Pan India'}</span>
                                            <span class="badge badge-dept">${job.qualification || 'Degree Required'}</span>
                                        </div>
                                    </header>

                                    <!-- Recruiting Board Brand Identity Strip -->
                                    <div class="board-branding-strip">
                                        <div class="board-logo" style="background: ${boardColor}; color: #fff;">
                                            ${boardShort}
                                        </div>
                                        <div class="board-meta">
                                            <span class="board-dept-name">${boardName}</span>
                                            <span class="board-state">${job.state || 'Central Government'} Notification</span>
                                        </div>
                                    </div>

                                    <!-- Short Information Context Card -->
                                    <div class="short-info-card-block">
                                        <h5 class="short-info-title"><i class="fa-solid fa-circle-info"></i> Short Information</h5>
                                        <p class="short-info-text">
                                            ${cleanDescription}
                                        </p>
                                    </div>

                                    <!-- Split Dates & Fees Card -->
                                    <div class="split-info-card" style="margin-top: 1rem;">
                                        <div class="split-info-column">
                                            <h5 class="column-title" style="margin-top:0;"><i class="fa-regular fa-calendar-days"></i> Important Dates</h5>
                                            <ul class="info-list">
                                                <li>
                                                    <span class="info-label">Application Begin:</span>
                                                    <span class="info-val">${job.start_date}</span>
                                                </li>
                                                <li>
                                                    <span class="info-label">Last Date to Apply:</span>
                                                    <span class="info-val deadline-text">${job.last_date}</span>
                                                </li>
                                                <li>
                                                    <span class="info-label">Online Fee Last Date:</span>
                                                    <span class="info-val">${job.last_date}</span>
                                                </li>
                                                <li>
                                                    <span class="info-label">Exam Date:</span>
                                                    <span class="info-val exam-text">${job.exam_date}</span>
                                                </li>
                                                ${job.result_date ? `
                                                <li>
                                                    <span class="info-label">Expected Result Date:</span>
                                                    <span class="info-val result-text">${job.result_date}</span>
                                                </li>
                                                ` : ''}
                                            </ul>
                                        </div>
                                        <div class="split-info-column">
                                            <h5 class="column-title" style="margin-top:0;"><i class="fa-solid fa-indian-rupee-sign"></i> Application Fee</h5>
                                            <ul class="info-list">
                                                ${feeHtml}
                                                <li class="fee-note">
                                                    <span class="info-label">Payment Mode:</span>
                                                    <span class="info-val">Pay the examination fee through Debit Card, Credit Card, Net Banking, or UPI mode only.</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                    <!-- Age Limit Card -->
                                    <div class="age-limit-card" style="margin-top: 1rem;">
                                        <h5 class="column-title" style="border-bottom:none; margin-bottom:0; padding-bottom:0; margin-top:0;"><i class="fa-regular fa-clock"></i> Age Limit Details</h5>
                                        <div class="age-grid">
                                            <div class="age-box">
                                                <span class="age-label">Minimum Age</span>
                                                <span class="age-val">${job.age_min ? job.age_min + ' Years' : '18 Years'}</span>
                                            </div>
                                            <div class="age-box">
                                                <span class="age-label">Maximum Age</span>
                                                <span class="age-val">${job.age_max ? job.age_max + ' Years' : (job.age_limit ? job.age_limit : '32 Years')}</span>
                                            </div>
                                        </div>
                                        <div class="age-cutoff-info">
                                            <strong>Age Limit Reference:</strong> Calculated based on the board's recruitment guidelines. Age relaxation is applicable extra as per government reservation rules.
                                        </div>
                                    </div>

                                    <!-- Recruitment timeline -->
                                    ${timelineHtml}

                                    <!-- Vacancy Details Card (SPA) -->
                                    <div class="details-full-section" style="margin-top:1.5rem; border-top: 1px solid var(--border-color); padding-top:1.25rem;">
                                        <h4 style="color: var(--accent-color); font-weight:700; font-family:'Outfit'; margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem;">
                                            Vacancy Details
                                        </h4>
                                        ${(job.vacancy_details && job.vacancy_details.length > 0) ? `
                                        <div style="overflow-x: auto; background: rgba(255,255,255,0.01); border: 1px solid var(--border-color); border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
                                            <table style="min-width: 800px; width: 100%; border-collapse: collapse; font-size: 0.9rem; text-align: left;">
                                                <thead>
                                                    <tr style="border-bottom: 1px solid var(--border-color);">
                                                        <th style="padding: 0.5rem; font-weight: 700; color: var(--text-primary); width: 25%; min-width: 200px;">Post Name</th>
                                                        <th style="padding: 0.5rem; font-weight: 700; color: var(--text-primary); text-align: center; width: 120px; min-width: 120px;">Total Post</th>
                                                        <th style="padding: 0.5rem; font-weight: 700; color: var(--text-primary);">Post Recruitment Eligibility Details</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    ${job.vacancy_details.map(vd => `
                                                        <tr style="border-bottom: 1px solid var(--border-color);">
                                                            <td style="padding: 0.5rem; color: var(--text-primary); font-weight: 600;">${vd.post_name}</td>
                                                            <td style="padding: 0.5rem; text-align: center;">
                                                                <span class="badge" style="background: rgba(37, 99, 235, 0.1); color: var(--accent-color); font-weight: 700; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem;">
                                                                    ${vd.total_post}
                                                                </span>
                                                            </td>
                                                            <td style="padding: 0.5rem; color: var(--text-secondary); white-space: pre-line;">${vd.eligibility}</td>
                                                        </tr>
                                                    `).join('')}
                                                </tbody>
                                            </table>
                                        </div>
                                        ` : `
                                        <div style="background: rgba(255,255,255,0.01); border: 1px dashed var(--border-color); border-radius: 8px; padding: 1.5rem; text-align: center; color: var(--text-secondary);">
                                            <p style="margin: 0; font-size: 0.85rem;">No vacancy details specified for this post.</p>
                                        </div>
                                        `}
                                    </div>

                                    <!-- Category Wise Vacancy Details Card (SPA) -->
                                    <div class="details-full-section" style="margin-top:1.5rem; border-top: 1px solid var(--border-color); padding-top:1.25rem;">
                                        <h4 style="color: var(--accent-color); font-weight:700; font-family:'Outfit'; margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem;">
                                            Category Wise Vacancy Details
                                        </h4>
                                        ${(job.category_wise_vacancies && job.category_wise_vacancies.length > 0) ? `
                                        <div style="overflow-x: auto; background: rgba(255,255,255,0.01); border: 1px solid var(--border-color); border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
                                            <table style="min-width: 750px; border-collapse: collapse; font-size: 0.9rem; text-align: left;">
                                                <thead>
                                                    <tr style="border-bottom: 1px solid var(--border-color);">
                                                        <th style="padding: 0.5rem; font-weight: 700; color: var(--text-primary);">Post Name</th>
                                                        <th style="padding: 0.5rem; font-weight: 700; color: var(--text-primary); text-align: right;">UR</th>
                                                        <th style="padding: 0.5rem; font-weight: 700; color: var(--text-primary); text-align: right;">EWS</th>
                                                        <th style="padding: 0.5rem; font-weight: 700; color: var(--text-primary); text-align: right;">EBC</th>
                                                        <th style="padding: 0.5rem; font-weight: 700; color: var(--text-primary); text-align: right;">BC</th>
                                                        <th style="padding: 0.5rem; font-weight: 700; color: var(--text-primary); text-align: right;">BC (F)</th>
                                                        <th style="padding: 0.5rem; font-weight: 700; color: var(--text-primary); text-align: right;">SC</th>
                                                        <th style="padding: 0.5rem; font-weight: 700; color: var(--text-primary); text-align: right;">ST</th>
                                                        <th style="padding: 0.5rem; font-weight: 700; color: var(--text-primary); text-align: right; background: rgba(37, 99, 235, 0.05);">Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    ${job.category_wise_vacancies.map(cwv => `
                                                        <tr style="border-bottom: 1px solid var(--border-color);">
                                                            <td style="padding: 0.5rem; color: var(--text-primary); font-weight: 600;">${cwv.post_name}</td>
                                                            <td style="padding: 0.5rem; text-align: right; color: var(--text-secondary);">${cwv.ur}</td>
                                                            <td style="padding: 0.5rem; text-align: right; color: var(--text-secondary);">${cwv.ews}</td>
                                                            <td style="padding: 0.5rem; text-align: right; color: var(--text-secondary);">${cwv.ebc}</td>
                                                            <td style="padding: 0.5rem; text-align: right; color: var(--text-secondary);">${cwv.bc}</td>
                                                            <td style="padding: 0.5rem; text-align: right; color: var(--text-secondary);">${cwv.bc_female}</td>
                                                            <td style="padding: 0.5rem; text-align: right; color: var(--text-secondary);">${cwv.sc}</td>
                                                            <td style="padding: 0.5rem; text-align: right; color: var(--text-secondary);">${cwv.st}</td>
                                                            <td style="padding: 0.5rem; text-align: right; font-weight: 700; color: var(--accent-color); background: rgba(37, 99, 235, 0.05);">${cwv.total}</td>
                                                        </tr>
                                                    `).join('')}
                                                </tbody>
                                            </table>
                                        </div>
                                        ` : `
                                        <div style="background: rgba(255,255,255,0.01); border: 1px dashed var(--border-color); border-radius: 8px; padding: 1.5rem; text-align: center; color: var(--text-secondary);">
                                            <p style="margin: 0; font-size: 0.85rem;">No category wise vacancy details specified for this post.</p>
                                        </div>
                                        `}
                                    </div>

                                    <!-- Vacancy Breakdown -->
                                    ${(job.category_vacancies && job.category_vacancies.length > 0) ? `
                                    <div class="details-full-section" style="margin-top:1.5rem; border-top: 1px solid var(--border-color); padding-top:1.25rem;">
                                        <h4 style="color: var(--accent-color); font-weight:700; font-family:'Outfit'; margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem;">
                                            Vacancy Distribution Breakdown
                                        </h4>
                                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem;">
                                            ${['post', 'caste_category', 'department', 'trade', 'discipline'].map(type => {
                                                const items = job.category_vacancies.filter(cv => cv.type === type);
                                                if (items.length === 0) return '';
                                                
                                                let groupTitle = '';
                                                if (type === 'post') groupTitle = 'Trade-wise / Post-wise Posts';
                                                else if (type === 'caste_category') groupTitle = 'Category-wise Posts';
                                                else if (type === 'department') groupTitle = 'Department-wise Posts';
                                                else if (type === 'trade') groupTitle = 'Trade-wise Posts';
                                                else if (type === 'discipline') groupTitle = 'Discipline-wise Posts';
                                                
                                                return `
                                                    <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--border-color); border-radius: 8px; padding: 1rem;">
                                                        <h5 style="font-size:0.85rem; text-transform:uppercase; color:var(--text-secondary); font-weight:700; margin-bottom:0.75rem; border-bottom: 1px solid var(--border-color); padding-bottom:0.4rem; font-family:'Outfit';">
                                                            ${groupTitle}
                                                        </h5>
                                                        <ul style="list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:0.5rem;">
                                                            ${items.map(cv => `
                                                                <li style="display:flex; justify-content:space-between; align-items:center; font-size:0.9rem; color:var(--text-primary);">
                                                                    <span>${cv.category_name}</span>
                                                                    <span class="badge" style="background:var(--border-color); color:var(--text-primary); font-weight:bold; padding: 2px 8px; font-size:0.75rem;">
                                                                        ${cv.vacancy_count}
                                                                    </span>
                                                                </li>
                                                            `).join('')}
                                                        </ul>
                                                    </div>
                                                `;
                                            }).join('')}
                                        </div>
                                    </div>
                                    ` : ''}

                                    <div class="details-full-section" style="margin-top:1.5rem;">
                                        <h4 style="color: var(--accent-color); font-weight:700; font-family:'Outfit';">${window.t('modal_overview', 'Recruitment Overview & Eligibility')}</h4>
                                        <p style="color: var(--text-secondary); line-height:1.75; font-size:0.95rem; margin-top:0.5rem;">${cleanDescription}</p>
                                    </div>

                                    <div class="details-full-section" style="margin-top:1.5rem;">
                                        <h4 style="color: var(--accent-color); font-weight:700; font-family:'Outfit';">${window.t('modal_selection', 'Selection Process Steps')}</h4>
                                        <p style="color: var(--text-secondary); line-height:1.75; font-size:0.95rem; margin-top:0.5rem;">${selectionProcessHtml}</p>
                                    </div>

                                    <div class="details-full-section" style="margin-top:1.5rem;">
                                        <h4 style="color: var(--accent-color); font-weight:700; font-family:'Outfit';">${window.t('modal_syllabus', 'Exam Scheme & Syllabus Patterns')}</h4>
                                        <div class="details-syllabus-container" style="max-height: none; overflow: visible; margin-top:0.5rem; color:var(--text-secondary); line-height:1.75;">
                                            ${examPatternHtml}
                                        </div>
                                    </div>

                                    <div style="display: flex; gap: 1rem; margin-top: 2rem; border-top: 1px solid var(--border-color); padding-top: 1.5rem; flex-wrap: wrap;">
                                        ${job.official_website_link ? `
                                        <a href="${job.official_website_link}" target="_blank" class="btn-view" style="flex:1; text-align:center; display:flex; align-items:center; justify-content:center; gap:0.4rem; font-weight:600; text-decoration:none;">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle;"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                            ${window.t('modal_advertisement', 'Official Advertisement')}
                                        </a>
                                        ` : ''}
                                    </div>
                                </article>
                            `;
                        } else if (type === 'admit_card') {
                            html = `
                                <div class="theme-accent-admit_card">
                                    <div class="category-visual-header">
                                        <h2>🎟️ <span class="notranslate" translate="no" data-translate-title="${job.title}">${window.translateJobTitle(job.title)}</span> ${window.t('trend_admit_cards', 'Admit Card')}</h2>
                                        <p>${window.t(job.department, job.department)} &bull; ${window.t(job.state, job.state)} &bull; ${window.t('modal_official_call_letter', 'Official Call Letter')}</p>
                                    </div>

                                    <div class="details-summary-grid">
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">${window.t('modal_admit_card_status', 'Admit Card Status')}</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: #10b981; margin-top:0.25rem;">${window.t('modal_released_active', '⚡ RELEASED & ACTIVE')}</div>
                                        </div>
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">${window.t('modal_exam_lbl', 'Expected Exam Date')}</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: var(--text-primary); margin-top:0.25rem;">${job.exam_date}</div>
                                        </div>
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">${window.t('modal_vacancies_lbl', 'Total Vacancies')}</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: var(--text-primary); margin-top:0.25rem;">${job.vacancy_count} ${window.t('vacancies_count', 'Posts')}</div>
                                        </div>
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">${window.t('modal_download_deadline', 'Download Deadline')}</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: #ef4444; margin-top:0.25rem;">${job.last_date}</div>
                                        </div>
                                    </div>

                                    <div class="details-full-section" style="margin-top:1.5rem;">
                                        <h4 style="color: #10b981; font-weight:700; font-family:'Outfit';">${window.t('modal_download_instructions', 'Download Call Letter Instructions')}</h4>
                                        <p style="color: var(--text-secondary); line-height:1.75; font-size:0.95rem; margin-top:0.5rem;">
                                            ${window.t('modal_board_released_admit', 'The selection board has released the admit cards for')} <strong><span class="notranslate" translate="no" data-translate-title="${job.title}">${window.translateJobTitle(job.title)}</span></strong>. ${window.t('modal_download_prior', 'Please download your entry card prior to the download deadline.')}
                                        </p>
                                        <div style="background: rgba(16, 185, 129, 0.05); padding: 1.25rem; border-radius: 8px; border: 1px dashed rgba(16, 185, 129, 0.2); margin: 1.25rem 0;">
                                            <h5 style="color: #10b981; margin-bottom: 0.5rem; font-weight: 700; font-size:0.95rem;">${window.t('modal_credentials_checklist', 'Required Credentials Checklist:')}</h5>
                                            <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.4rem; padding: 0; margin: 0; font-size: 0.9rem; color: var(--text-secondary);">
                                                <li>${window.t('modal_chk_1', '🔑 1. Registered Application Number / Registration ID')}</li>
                                                <li>${window.t('modal_chk_2', '🎂 2. Candidate Date of Birth (DD-MM-YYYY format)')}</li>
                                                <li>${window.t('modal_chk_3', '🧩 3. Security Verification Code Captcha')}</li>
                                            </ul>
                                        </div>
                                        <p style="color: var(--text-secondary); font-size: 0.9rem; line-height:1.5;">
                                            ⚠️ <strong>${window.t('modal_note', 'Note:')}</strong> ${window.t('modal_admit_card_note_text', 'Carry a printed color copy of this Admit Card along with an active government photo ID proof (Aadhaar Card, Passport, driving license, PAN card) and two passport-sized color photos to the test venue.')}
                                        </p>
                                    </div>

                                    ${(job.apply_link || job.official_website_link) ? `
                                    <div class="download-callout-panel">
                                        <h4 style="color: var(--text-primary); margin-bottom: 0.5rem; font-weight: 700;">${window.t('modal_direct_access', 'Direct Candidate Server Access')}</h4>
                                        <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1.25rem;">${window.t('modal_select_server', 'Select Server 1 or 2 to download call letters instantly.')}</p>
                                        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                                            ${(job.apply_link || job.official_website_link) ? `
                                            <a href="${job.apply_link || job.official_website_link}" target="_blank" class="download-button-premium">
                                                ${window.t('modal_download_s1', '🚀 Download Call Letter (Server 1)')}
                                            </a>
                                            ` : ''}
                                            ${job.official_website_link ? `
                                            <a href="${job.official_website_link}" target="_blank" class="download-button-premium" style="background: var(--bg-secondary); border: 1px solid var(--border-color); color: var(--text-primary); box-shadow: none;">
                                                ${window.t('modal_alt_login_s2', '🌐 Alternative Login (Server 2)')}
                                            </a>
                                            ` : ''}
                                        </div>
                                    </div>
                                    ` : ''}
                                </div>
                            `;
                        } else if (type === 'result') {
                            html = `
                                <div class="theme-accent-result">
                                    <div class="category-visual-header">
                                        <h2>🏆 <span class="notranslate" translate="no" data-translate-title="${job.title}">${window.translateJobTitle(job.title)}</span> ${window.t('trend_results', 'Exam Result')}</h2>
                                        <p>${window.t(job.department, job.department)} &bull; ${window.t(job.state, job.state)} &bull; ${window.t('modal_merit_cutoff', 'Merit & Cutoff Scores')}</p>
                                    </div>

                                    <div class="details-summary-grid">
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">${window.t('modal_result_status', 'Result Status')}</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: #8b5cf6; margin-top:0.25rem;">${window.t('modal_merit_released', '🎉 MERIT LIST RELEASED')}</div>
                                        </div>
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">${window.t('modal_cutoff_verification', 'Cutoff Verification')}</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: var(--text-primary); margin-top:0.25rem;">${window.t('modal_completed', 'COMPLETED')}</div>
                                        </div>
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">${window.t('modal_total_selected', 'Total Selected Candidates')}</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: var(--text-primary); margin-top:0.25rem;">${job.vacancy_count} ${window.t('modal_allotments', 'Allotments')}</div>
                                        </div>
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">${window.t('modal_allotment_date', 'Allotment Date')}</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: var(--text-primary); margin-top:0.25rem;">${job.last_date}</div>
                                        </div>
                                    </div>

                                    <div class="details-full-section" style="margin-top:1.5rem;">
                                        <h4 style="color: #8b5cf6; font-weight:700; font-family:'Outfit';">${window.t('modal_cutoff_marks', 'Category-Wise Cutoff Marks')}</h4>
                                        <div class="details-table-wrapper">
                                            <table class="details-cutoff-table">
                                                <thead>
                                                    <tr>
                                                        <th>${window.t('modal_category_segment', 'Category Segment')}</th>
                                                        <th>${window.t('modal_cutoff_percent', 'Cutoff Marks (%)')}</th>
                                                        <th>${window.t('modal_status_index', 'Status Index')}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td><strong>${window.t('modal_cat_gen', 'General (UR)')}</strong></td>
                                                        <td>78.50%</td>
                                                        <td>${window.t('modal_status_active_cleared', 'Active / Cleared')}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>OBC</strong></td>
                                                        <td>72.40%</td>
                                                        <td>${window.t('modal_status_active_cleared', 'Active / Cleared')}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>SC / ST</strong></td>
                                                        <td>65.00%</td>
                                                        <td>${window.t('modal_status_active_cleared', 'Active / Cleared')}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>EWS</strong></td>
                                                        <td>70.15%</td>
                                                        <td>${window.t('modal_status_active_cleared', 'Active / Cleared')}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="details-full-section" style="margin-top:1.5rem;">
                                        <h4 style="color: #8b5cf6; font-weight:700; font-family:'Outfit';">${window.t('modal_next_steps', 'Next Steps & Counselling Process')}</h4>
                                        <p style="color: var(--text-secondary); line-height: 1.75; font-size: 0.95rem; margin-top:0.5rem;">
                                            ${window.t('modal_merit_note_text', 'All qualifying candidates whose roll numbers are highlighted in the merit list must prepare documents for the biometric validation and certificate screening. Individual counseling invitations will be sent via registered candidate emails soon.')}
                                        </p>
                                    </div>

                                    ${(job.apply_link || job.official_website_link) ? `
                                    <div class="download-callout-panel">
                                        <h4 style="color: var(--text-primary); margin-bottom: 0.5rem; font-weight: 700;">${window.t('modal_direct_merit_dl', 'Direct Merit PDF Downloads')}</h4>
                                        <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1.25rem;">${window.t('modal_dl_cutoff_text', 'Download final selection indexes or cutoff list directly from secure servers.')}</p>
                                        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                                            ${(job.apply_link || job.official_website_link) ? `
                                            <a href="${job.apply_link || job.official_website_link}" target="_blank" class="download-button-premium">
                                                ${window.t('modal_download_merit_pdf', '📄 Download Merit List (PDF)')}
                                            </a>
                                            ` : ''}
                                            ${job.official_website_link ? `
                                            <a href="${job.official_website_link}" target="_blank" class="download-button-premium" style="background: var(--bg-secondary); border: 1px solid var(--border-color); color: var(--text-primary); box-shadow: none;">
                                                ${window.t('modal_download_cutoff', '📊 Download Official Cutoff')}
                                            </a>
                                            ` : ''}
                                        </div>
                                    </div>
                                    ` : ''}
                                </div>
                            `;
                        } else if (type === 'syllabus') {
                            html = `
                                <div class="theme-accent-syllabus">
                                    <div class="category-visual-header">
                                        <h2>📖 <span class="notranslate" translate="no" data-translate-title="${job.title}">${window.translateJobTitle(job.title)}</span> ${window.t('trend_syllabus', 'Exam Syllabus')}</h2>
                                        <p>${window.t(job.department, job.department)} &bull; ${window.t(job.state, job.state)} &bull; ${window.t('modal_syllabus_topics', 'Topics & Marking Pattern')}</p>
                                    </div>

                                    <div class="details-summary-grid">
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">${window.t('modal_syllabus_status', 'Syllabus Status')}</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: #db2777; margin-top:0.25rem;">${window.t('modal_official_overhaul', '⭐ OFFICIAL OVERHAUL')}</div>
                                        </div>
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">${window.t('modal_total_marks', 'Total Exam Marks')}</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: var(--text-primary); margin-top:0.25rem;">200 - 300 Marks</div>
                                        </div>
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">${window.t('modal_negative_marking', 'Negative Marking')}</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: #ef4444; margin-top:0.25rem;">0.25 Points / Wrong</div>
                                        </div>
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">${window.t('modal_duration_allowance', 'Duration Allowance')}</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: var(--text-primary); margin-top:0.25rem;">120 - 180 Minutes</div>
                                        </div>
                                    </div>

                                    <div class="details-full-section" style="margin-top:1.5rem;">
                                        <h4 style="color: #db2777; font-weight:700; font-family:'Outfit';">${window.t('modal_exam_scheme', 'Exam Scheme & Section Breakdown')}</h4>
                                        <p style="color: var(--text-secondary); line-height: 1.75; font-size: 0.95rem; margin-top:0.5rem;">${job.exam_pattern}</p>
                                    </div>

                                    <div class="details-full-section" style="margin-top:1.5rem;">
                                        <h4 style="color: #db2777; font-weight:700; font-family:'Outfit';">${window.t('modal_important_subjects', 'Important Subjects & Key Syllabus Focus')}</h4>
                                        <div class="details-syllabus-container" style="max-height: none; overflow: visible; margin-top:0.5rem; color:var(--text-secondary); line-height:1.75;">
                                            ${job.description}
                                        </div>
                                    </div>

                                    ${job.official_website_link ? `
                                    <div class="download-callout-panel">
                                        <h4 style="color: var(--text-primary); margin-bottom: 0.5rem; font-weight: 700;">${window.t('modal_download_resources', 'Download Official Study Resources')}</h4>
                                        <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1.25rem;">${window.t('modal_grab_syllabus_text', 'Grab verified syllabus copy and previous year mock papers instantly.')}</p>
                                        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                                            <a href="${job.official_website_link}" target="_blank" class="download-button-premium">
                                                ${window.t('modal_download_syllabus_pdf', '📚 Download Detailed Syllabus (PDF)')}
                                            </a>
                                            <a href="${job.official_website_link}" target="_blank" class="download-button-premium" style="background: var(--bg-secondary); border: 1px solid var(--border-color); color: var(--text-primary); box-shadow: none;">
                                                ${window.t('modal_mock_papers', '✏️ Mock Question Papers')}
                                            </a>
                                        </div>
                                    </div>
                                    ` : ''}
                                </div>
                            `;
                        } else if (type === 'answer_key') {
                            html = `
                                <div class="theme-accent-answer_key">
                                    <div class="category-visual-header">
                                        <h2>🔑 <span class="notranslate" translate="no" data-translate-title="${job.title}">${window.translateJobTitle(job.title)}</span> ${window.t('trend_answer_keys', 'Answer Key')}</h2>
                                        <p>${window.t(job.department, job.department)} &bull; ${window.t(job.state, job.state)} &bull; ${window.t('modal_answer_key_objection_window', 'Official Key & Objection Window')}</p>
                                    </div>

                                    <div class="details-summary-grid">
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">${window.t('modal_answer_key_state', 'Answer Key State')}</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: #d97706; margin-top:0.25rem;">${window.t('modal_answer_key_active_objection_open', '📝 ACTIVE / OBJECTION OPEN')}</div>
                                        </div>
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">${window.t('modal_release_date', 'Release Date')}</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: var(--text-primary); margin-top:0.25rem;">${job.exam_date}</div>
                                        </div>
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">${window.t('modal_objection_fee', 'Objection Filing Fee')}</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: var(--text-primary); margin-top:0.25rem;">₹ 100 / Question</div>
                                        </div>
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">${window.t('modal_closing_date', 'Closing Date')}</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: #ef4444; margin-top:0.25rem;">${job.last_date}</div>
                                        </div>
                                    </div>

                                    <div class="details-full-section" style="margin-top:1.5rem;">
                                        <h4 style="color: #d97706; font-weight:700; font-family:'Outfit';">${window.t('modal_objection_milestones', 'Important Objection Filing Milestones')}</h4>
                                        <div class="objections-timeline">
                                            <div class="timeline-milestone">
                                                <div class="timeline-milestone-title">${window.t('modal_milestone_1', '1. Release of Provisional Key')}</div>
                                                <div class="timeline-milestone-desc">${window.t('modal_milestone_1_desc', 'Candidates can access their individual exam response sheets along with official answer options.')}</div>
                                            </div>
                                            <div class="timeline-milestone">
                                                <div class="timeline-milestone-title">${window.t('modal_milestone_2', '2. Objection Submission Gate (OPEN)')}</div>
                                                <div class="timeline-milestone-desc">${window.t('modal_milestone_2_desc', 'If any answer candidate selected differs from the key, they can upload substantial text book proof.')}</div>
                                            </div>
                                            <div class="timeline-milestone">
                                                <div class="timeline-milestone-title">${window.t('modal_milestone_3', '3. Announcement of Final Key')}</div>
                                                <div class="timeline-milestone-desc">${window.t('modal_milestone_3_desc', 'The advisory committee will evaluate objections and launch the overridden final answer key copy.')}</div>
                                            </div>
                                        </div>
                                    </div>

                                    ${(job.apply_link || job.official_website_link) ? `
                                    <div class="download-callout-panel">
                                        <h4 style="color: var(--text-primary); margin-bottom: 0.5rem; font-weight: 700;">${window.t('modal_download_keys_objection', 'Download Keys & File Objections')}</h4>
                                        <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1.25rem;">${window.t('modal_check_scores_text', 'Check your scores against the keys or raise concerns directly.')}</p>
                                        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                                            ${(job.apply_link || job.official_website_link) ? `
                                            <a href="${job.apply_link || job.official_website_link}" target="_blank" class="download-button-premium">
                                                ${window.t('modal_download_prov_key', '🔑 Download Provisional Key (PDF)')}
                                            </a>
                                            ` : ''}
                                            ${job.official_website_link ? `
                                            <a href="${job.official_website_link}" target="_blank" class="download-button-premium" style="background: var(--bg-secondary); border: 1px solid var(--border-color); color: var(--text-primary); box-shadow: none;">
                                                ${window.t('modal_raise_objections', '🛡️ Raise Key Objections Now')}
                                            </a>
                                            ` : ''}
                                        </div>
                                    </div>
                                    ` : ''}
                                </div>
                            `;
                        } else if (type === 'admission') {
                            html = `
                                <div class="theme-accent-admission">
                                    <div class="category-visual-header">
                                        <h2>🎓 <span class="notranslate" translate="no" data-translate-title="${job.title}">${window.translateJobTitle(job.title)}</span></h2>
                                        <p>${window.t(job.department, job.department)} &bull; ${window.t('modal_entrance_counselling_board', 'Entrance & Counselling Board')}</p>
                                    </div>

                                    <div class="details-summary-grid">
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">${window.t('modal_program_stream', 'Program Stream')}</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: #0891b2; margin-top:0.25rem;">${window.t('modal_academic_technical', 'Academic & Technical')}</div>
                                        </div>
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">${window.t('modal_entrance_exam_fee', 'Entrance Exam Fee')}</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: var(--text-primary); margin-top:0.25rem;">₹ ${job.application_fee}</div>
                                        </div>
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">${window.t('modal_seat_intake_cap', 'Seat Intake Cap')}</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: var(--text-primary); margin-top:0.25rem;">${job.vacancy_count} ${window.t('modal_open_seats', 'Open Seats')}</div>
                                        </div>
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">${window.t('modal_counselling_deadline', 'Counseling Deadline')}</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: #ef4444; margin-top:0.25rem;">${job.last_date}</div>
                                        </div>
                                    </div>

                                    <div class="details-full-section" style="margin-top:1.5rem;">
                                        <h4 style="color: #0891b2; font-weight:700; font-family:'Outfit';">${window.t('modal_course_scope', 'Course Scope & Eligibility Guidelines')}</h4>
                                        <p style="color: var(--text-secondary); line-height: 1.75; font-size: 0.95rem; margin-top:0.5rem;">${job.description}</p>
                                    </div>

                                    <div class="details-full-section" style="margin-top:1.5rem;">
                                        <h4 style="color: #0891b2; font-weight:700; font-family:'Outfit';">${window.t('modal_semester_fee_allocation', 'Semester Fee & Academic Allocation')}</h4>
                                        <div class="fees-info-grid">
                                            <div style="background: rgba(255,255,255,0.01); border: 1px solid var(--border-color); padding: 1rem; border-radius: 8px;">
                                                <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">${window.t('modal_regular_stream_fee', 'Regular Stream Fee')}</div>
                                                <div style="font-size:1.15rem; font-weight:700; color:var(--text-primary); margin-top:0.25rem;">₹ 25,000 / Year</div>
                                            </div>
                                            <div style="background: rgba(255,255,255,0.01); border: 1px solid var(--border-color); padding: 1rem; border-radius: 8px;">
                                                <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">${window.t('modal_selection_entry_criteria', 'Selection / Entry Criteria')}</div>
                                                <div style="font-size:1.15rem; font-weight:700; color:#0891b2; margin-top:0.25rem;">${window.t('modal_entrance_score_merit', 'Entrance Score Merit')}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div style="display: flex; gap: 1rem; margin-top: 2rem; border-top: 1px solid var(--border-color); padding-top: 1.5rem; flex-wrap: wrap;">
                                        ${job.official_website_link ? `
                                        <a href="${job.official_website_link}" target="_blank" class="btn-view" style="flex:1; text-align:center; display:flex; align-items:center; justify-content:center; gap:0.4rem; font-weight:600; text-decoration:none;">
                                            ${window.t('modal_official_admissions_portal', '🌐 Official Admissions Portal')}
                                        </a>
                                        ` : ''}
                                    </div>
                                </div>
                            `;
                        } else if (type === 'scholarship') {
                            html = `
                                <div class="theme-accent-scholarship">
                                    <div class="category-visual-header">
                                        <h2>💰 <span class="notranslate" translate="no" data-translate-title="${job.title}">${window.translateJobTitle(job.title)}</span> ${window.t('trend_scholarships', 'Scheme')}</h2>
                                        <p>${window.t(job.department, job.department)} &bull; ${window.t(job.state, job.state)} &bull; ${window.t('modal_scholarship_grant_sub', 'Merit & Means Financial Grant')}</p>
                                    </div>

                                    <div class="details-summary-grid">
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">${window.t('modal_financial_grant_scope', 'Financial Grant Scope')}</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: #ea580c; margin-top:0.25rem;">₹ 50,000 / Academic Year</div>
                                        </div>
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">${window.t('modal_income_eligibility_cap', 'Income Eligibility Cap')}</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: var(--text-primary); margin-top:0.25rem;">&lt; ₹ 2.5 Lakhs / Year</div>
                                        </div>
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">${window.t('modal_allotment_seats_limit', 'Allotment Seats Limit')}</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: var(--text-primary); margin-top:0.25rem;">${job.vacancy_count} ${window.t('modal_beneficiaries', 'Beneficiaries')}</div>
                                        </div>
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">${window.t('modal_submission_deadline', 'Submission Deadline')}</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: #ef4444; margin-top:0.25rem;">${job.last_date}</div>
                                        </div>
                                    </div>

                                    <div class="details-full-section" style="margin-top:1.5rem;">
                                        <h4 style="color: #ea580c; font-weight:700; font-family:'Outfit';">${window.t('modal_scholarship_objective', 'Scholarship Objective & Grant Criteria')}</h4>
                                        <p style="color: var(--text-secondary); line-height: 1.75; font-size: 0.95rem; margin-top:0.5rem;">${job.description}</p>
                                    </div>

                                    <div class="details-full-section" style="margin-top:1.5rem;">
                                        <h4 style="color: #ea580c; font-weight:700; font-family:'Outfit';">${window.t('modal_mandatory_documents', 'Mandatory Required Documents Checklist')}</h4>
                                        <div class="documents-checklist">
                                            <div class="checklist-item">
                                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" style="margin-right:4px;"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                <span>${window.t('modal_doc_chk_1', '1. Valid Income Certificate verified by local Revenue Inspector (Tahsildar)')}</span>
                                            </div>
                                            <div class="checklist-item">
                                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" style="margin-right:4px;"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                <span>${window.t('modal_doc_chk_2', '2. Candidate Caste & Domicile certificate files')}</span>
                                            </div>
                                            <div class="checklist-item">
                                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" style="margin-right:4px;"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                <span>${window.t('modal_doc_chk_3', '3. Previous Academic year Marks memo Card / Qualifying certificates')}</span>
                                            </div>
                                            <div class="checklist-item">
                                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" style="margin-right:4px;"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                <span>${window.t('modal_doc_chk_4', '4. Candidate Bank Passbook linking Aadhaar profile for direct DBTs')}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div style="display: flex; gap: 1rem; margin-top: 2rem; border-top: 1px solid var(--border-color); padding-top: 1.5rem; flex-wrap: wrap;">
                                        ${job.official_website_link ? `
                                        <a href="${job.official_website_link}" target="_blank" class="btn-view" style="flex:1; text-align:center; display:flex; align-items:center; justify-content:center; gap:0.4rem; font-weight:600; text-decoration:none;">
                                            ${window.t('modal_official_scheme_guidelines', 'Official Scheme Guidelines')}
                                        </a>
                                        ` : ''}
                                    </div>
                                </div>
                            `;
                        } else {
                            html = `
                                <div class="theme-accent-notice">
                                    <div class="category-visual-header">
                                        <h2>📢 <span class="notranslate" translate="no" data-translate-title="${job.title}">${window.translateJobTitle(job.title)}</span></h2>
                                        <p>${window.t(job.department, job.department)} &bull; ${window.t(job.state, job.state)} &bull; ${window.t('modal_official_important_alert', 'Official Important Alert')}</p>
                                    </div>

                                    <div class="notice-critical-alert">
                                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="margin-top: 2px; flex-shrink: 0;"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                        <div>
                                            <strong>${window.t('modal_critical_calendar_notice', 'Critical Calendar Notice:')}</strong> ${window.t('modal_critical_calendar_notice_text', 'The examination date has been scheduled/updated. Please review the official notice specifications below and align your schedules.')}
                                        </div>
                                    </div>

                                    <div class="details-summary-grid">
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">${window.t('modal_announced_exam_date', 'Announced Exam Date')}</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: #dc2626; margin-top:0.25rem;">${job.exam_date}</div>
                                        </div>
                                        <div class="details-summary-item">
                                            <div style="font-size:0.75rem; text-transform:uppercase; color:var(--text-secondary);">${window.t('modal_notice_published_date', 'Notice Published Date')}</div>
                                            <div style="font-size:1.15rem; font-weight:700; color: var(--text-primary); margin-top:0.25rem;">${job.last_date}</div>
                                        </div>
                                    </div>

                                    <div class="details-full-section" style="margin-top:1.5rem;">
                                        <h4 style="color: #dc2626; font-weight:700; font-family:'Outfit';">${window.t('modal_important_circular_specs', 'Important Circular Specifications')}</h4>
                                        <p style="color: var(--text-secondary); line-height: 1.75; font-size: 0.95rem; margin-top:0.5rem;">${job.description}</p>
                                    </div>

                                    ${job.official_website_link ? `
                                    <div class="download-callout-panel">
                                        <h4 style="color: var(--text-primary); margin-bottom: 0.5rem; font-weight: 700;">${window.t('modal_download_official_circular', 'Download Official Circular')}</h4>
                                        <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1.25rem;">${window.t('modal_download_notice_pdf_desc', 'Download the full, official notice PDF released by the department.')}</p>
                                        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                                            <a href="${job.official_website_link}" target="_blank" class="download-button-premium">
                                                ${window.t('modal_download_notice_pdf', '📄 Download Official Notice (PDF)')}
                                            </a>
                                        </div>
                                    </div>
                                    ` : ''}
                                </div>
                            `;
                        }

                        $('#modalRealContent').html(html);

                        // Inject related parent recruitment or child notices/corrigenda dynamically
                        let hierarchyHtml = '';
                        if (job.parent) {
                            hierarchyHtml = `
                                <div style="background: rgba(59, 130, 246, 0.08); padding: 0.85rem 1.25rem; border-radius: 8px; border: 1px solid rgba(59, 130, 246, 0.2); margin-top: 1.25rem; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.5rem; font-size: 0.9rem;">
                                    <span style="color: #60a5fa;">ℹ️</span>
                                    <span>${window.t('modal_related_to_main', 'This is related to the main recruitment:')}</span>
                                    <a href="#" class="btn-view" data-slug="${job.parent.slug}" style="color: #60a5fa; font-weight: 600; text-decoration: none; border-bottom: 1px dashed #60a5fa;">${window.translateJobTitle(job.parent.title)}</a>
                                </div>
                            `;
                        } else if (job.children && job.children.length > 0) {
                            hierarchyHtml = `
                                <div style="background: rgba(139, 92, 246, 0.08); padding: 0.85rem 1.25rem; border-radius: 8px; border: 1px dashed rgba(139, 92, 246, 0.3); margin-top: 1.25rem; margin-bottom: 1.25rem; font-size: 0.9rem;">
                                    <strong style="color: #a78bfa; display: block; margin-bottom: 0.5rem;">🔔 ${window.t('modal_related_updates', 'Related Updates & Notices:')}</strong>
                                    <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.4rem;">
                                        ${job.children.map(child => `
                                            <li style="display: flex; align-items: center; gap: 0.5rem;">
                                                <span style="font-size: 0.7rem; text-transform: uppercase; padding: 0.15rem 0.4rem; border-radius: 4px; background: rgba(139, 92, 246, 0.2); color: #c084fc; font-weight: 700;">${window.t(child.postType, child.postType.replace('_', ' '))}</span>
                                                <a href="#" class="btn-view" data-slug="${child.slug}" style="color: var(--text-primary); text-decoration: none; border-bottom: 1px solid rgba(255,255,255,0.15); font-weight: 500;">${window.translateJobTitle(child.title)}</a>
                                            </li>
                                        `).join('')}
                                    </ul>
                                </div>
                            `;
                        }

                        if (hierarchyHtml) {
                            $('#modalRealContent').find('.category-visual-header').after(hierarchyHtml);
                        }
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
            const rTable = $('#dashboardRecentlyViewedTable tbody');

            bTable.html(`<tr><td colspan="4" style="text-align:center;">${window.t('dash_loading_bookmarks', 'Loading Saved bookmarks...')}</td></tr>`);
            aTable.html(`<tr><td colspan="4" style="text-align:center;">${window.t('dash_loading_applications', 'Loading Submitted applications...')}</td></tr>`);
            rTable.html(`<tr><td colspan="4" style="text-align:center;">${window.t('dash_loading_recently_viewed', 'Loading recently viewed...')}</td></tr>`);

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
                            bTable.html(`<tr><td colspan="4" style="text-align:center; color: var(--text-secondary);">${window.t('dash_no_bookmarks', 'No recruitment alerts bookmarked.')}</td></tr>`);
                        } else {
                            let bHtml = '';
                            data.bookmarks.forEach(book => {
                                bHtml += `
                                    <tr>
                                        <td style="font-weight:600;"><span class="notranslate" translate="no" data-translate-title="${book.title}">${window.translateJobTitle(book.title)}</span></td>
                                        <td>${window.t(book.state, book.state)}</td>
                                        <td style="color:#ef4444; font-weight:500;">${book.last_date}</td>
                                        <td style="text-align:center;">
                                            <button class="btn-sm-danger delete-bookmark-btn" data-id="${book.job_id}" style="margin-right:0.5rem;">${window.t('btn_delete', 'Delete')}</button>
                                            <a href="#" class="btn-view btn-view-sm" data-slug="${book.slug}" style="padding: 0.35rem 0.75rem; font-size:0.75rem;">${window.t('btn_view', 'View')}</a>
                                        </td>
                                    </tr>
                                `;
                            });
                            bTable.html(bHtml);
                        }

                        // Render Submitted Applications
                        if (data.applications.length === 0) {
                            aTable.html(`<tr><td colspan="4" style="text-align:center; color: var(--text-secondary);">${window.t('dash_no_applications', 'No job applications submitted.')}</td></tr>`);
                        } else {
                            let aHtml = '';
                            data.applications.forEach(app => {
                                let statusClass = 'status-applied';
                                if (app.status === 'reviewing') statusClass = 'status-reviewing';
                                if (app.status === 'shortlisted') statusClass = 'status-shortlisted';
                                if (app.status === 'rejected') statusClass = 'status-rejected';

                                aHtml += `
                                    <tr>
                                        <td style="font-weight:600;"><span class="notranslate" translate="no" data-translate-title="${app.title}">${window.translateJobTitle(app.title)}</span></td>
                                        <td>${window.t(app.department, app.department)}</td>
                                        <td>${app.applied_at}</td>
                                        <td>
                                            <span class="status-badge ${statusClass}">${window.t('status_' + app.status.toLowerCase(), app.status)}</span>
                                        </td>
                                    </tr>
                                `;
                            });
                            aTable.html(aHtml);
                        }

                        // Render Recently Viewed
                        if (!data.recently_viewed || data.recently_viewed.length === 0) {
                            rTable.html(`<tr><td colspan="4" style="text-align:center; color: var(--text-secondary);">${window.t('dash_no_recently_viewed', 'No recently viewed recruitments.')}</td></tr>`);
                        } else {
                            let rHtml = '';
                            data.recently_viewed.forEach(recent => {
                                rHtml += `
                                    <tr>
                                        <td style="font-weight:600;"><span class="notranslate" translate="no" data-translate-title="${recent.title}">${window.translateJobTitle(recent.title)}</span></td>
                                        <td>${window.t(recent.state, recent.state)}</td>
                                        <td style="color:#ef4444; font-weight:500;">${recent.last_date}</td>
                                        <td style="text-align:center;">
                                            <a href="#" class="btn-view btn-view-sm" data-slug="${recent.slug}" style="padding: 0.35rem 0.75rem; font-size:0.75rem;">${window.t('btn_view', 'View')}</a>
                                        </td>
                                    </tr>
                                `;
                            });
                            rHtml += `
                                `;
                            rTable.html(rHtml);
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
