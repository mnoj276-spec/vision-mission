@inject('seoService', 'App\Domains\Jobs\Services\SeoService')
@extends('layouts.app')

@section('title', $pageTitle)

@section('content')

<style>
    .vacancy-table-wrapper {
        -ms-overflow-style: none;  /* IE and Edge */
        scrollbar-width: none;  /* Firefox */
    }
    .vacancy-table-wrapper::-webkit-scrollbar {
        display: none; /* Chrome, Safari and Opera */
    }
</style>

@php
    $statusText = 'Active';
    $statusClass = 'status-open';
    
    if ($job->post_type === 'job') {
        if ($job->last_date_to_apply && $job->last_date_to_apply->isPast()) {
            $statusText = 'Apply Closed';
            $statusClass = 'status-closed';
        } elseif ($job->start_date && $job->start_date->isFuture()) {
            $statusText = 'Upcoming';
            $statusClass = 'status-upcoming';
        } else {
            $statusText = 'Apply Open';
            $statusClass = 'status-open';
        }
    } elseif ($job->post_type === 'result') {
        $statusText = 'Result Out';
        $statusClass = 'status-declared';
    } elseif ($job->post_type === 'admit_card') {
        $statusText = 'Admit Card Out';
        $statusClass = 'status-declared';
    } elseif ($job->post_type === 'answer_key') {
        $statusText = 'Answer Key Out';
        $statusClass = 'status-declared';
    } elseif ($job->post_type === 'syllabus') {
        $statusText = 'Syllabus Out';
        $statusClass = 'status-open';
    } else {
        $statusText = 'Active';
        $statusClass = 'status-open';
    }

    // Dynamic Board Identity Branding resolver
    $boardName = $job->department->name ?? 'Government Ministry';
    $boardShort = 'GOVT';
    $boardColor = 'var(--accent-color)';
    
    if (str_contains(strtolower($boardName), 'staff selection') || str_contains(strtolower($boardName), 'ssc')) {
        $boardShort = 'SSC';
        $boardColor = 'linear-gradient(135deg, #1e40af 0%, #3b82f6 100%)';
    } elseif (str_contains(strtolower($boardName), 'union public') || str_contains(strtolower($boardName), 'upsc')) {
        $boardShort = 'UPSC';
        $boardColor = 'linear-gradient(135deg, #78350f 0%, #d97706 100%)';
    } elseif (str_contains(strtolower($boardName), 'railway') || str_contains(strtolower($boardName), 'rrb')) {
        $boardShort = 'RRB';
        $boardColor = 'linear-gradient(135deg, #991b1b 0%, #dc2626 100%)';
    } elseif (str_contains(strtolower($boardName), 'public service commission') || str_contains(strtolower($boardName), 'psc')) {
        $boardShort = 'PSC';
        $boardColor = 'linear-gradient(135deg, #065f46 0%, #10b981 100%)';
    } elseif (str_contains(strtolower($boardName), 'police')) {
        $boardShort = 'POLICE';
        $boardColor = 'linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 100%)';
    } else {
        $words = array_values(array_filter(explode(' ', $boardName)));
        if (count($words) >= 2) {
            $boardShort = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        } else {
            $boardShort = strtoupper(substr($boardName, 0, 2));
        }
        $boardColor = 'linear-gradient(135deg, rgba(37, 99, 235, 0.2) 0%, rgba(139, 92, 246, 0.2) 100%)';
    }

    // Dynamic Police/Defence physical standards detector
    $isPhysicalJob = false;
    $searchTerms = ['police', 'si', 'constable', 'gd', 'agniveer', 'army', 'navy', 'airforce', 'paramilitary', 'cpdf', 'bsf', 'cisf', 'crpf', 'itbp', 'ssb', 'rifleman', 'sub inspector', 'physical test', 'physical efficiency'];
    $titleLower = strtolower($job->title);
    $descLower = strtolower($job->description);
    foreach ($searchTerms as $term) {
        if (str_contains($titleLower, $term) || str_contains($descLower, $term)) {
            $isPhysicalJob = true;
            break;
        }
    }

    // Dynamic timeline child-link compiler for Useful links table
    $admitCardLink = null;
    $resultLink = null;
    $answerKeyLink = null;
    $syllabusLink = null;
    $cutoffLink = null;
    $noticeLink = null;
    
    if ($timeline && $timeline->count() > 0) {
        foreach ($timeline as $tItem) {
            if ($tItem->id === $job->id) continue;
            if ($tItem->post_type === 'admit_card') {
                $admitCardLink = route('seo.job_detail', ['slug' => $tItem->slug]);
            } elseif ($tItem->post_type === 'result') {
                $resultLink = route('seo.job_detail', ['slug' => $tItem->slug]);
            } elseif ($tItem->post_type === 'answer_key') {
                $answerKeyLink = route('seo.job_detail', ['slug' => $tItem->slug]);
            } elseif ($tItem->post_type === 'syllabus') {
                $syllabusLink = route('seo.job_detail', ['slug' => $tItem->slug]);
            } elseif ($tItem->post_type === 'cutoff') {
                $cutoffLink = route('seo.job_detail', ['slug' => $tItem->slug]);
            } elseif ($tItem->post_type === 'notice') {
                $noticeLink = route('seo.job_detail', ['slug' => $tItem->slug]);
            }
        }
    }
@endphp

<main style="max-width: 1000px; margin: 0 auto; padding: 0 5%;">
    <!-- Breadcrumbs -->
    <nav aria-label="Breadcrumb" class="breadcrumb-trail" itemscope itemtype="https://schema.org/BreadcrumbList">
        <span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
            <a itemprop="item" href="/"><span itemprop="name" data-translate-lookup="Home">Home</span></a>
            <meta itemprop="position" content="1">
        </span>
        @foreach($breadcrumbs as $label => $url)
            <span class="breadcrumb-separator">&raquo;</span>
            <span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                @if($url)
                    <a itemprop="item" href="{{ $url }}"><span itemprop="name" data-translate-lookup="{{ $label }}">{{ $label }}</span></a>
                @else
                    <span itemprop="name" data-translate-lookup="{{ $label }}">{{ $label }}</span>
                @endif
                <meta itemprop="position" content="{{ $loop->iteration + 1 }}">
            </span>
        @endforeach
    </nav>

    <!-- Main Detail Card -->
    <article class="detail-card">
        <!-- Header -->
        <header class="detail-header-block">
            <div class="detail-header-main">
                <h1 data-translate-title="{{ $pageHeader }}">{{ $pageHeader }}</h1>
                <button type="button" onclick="window.print()" class="print-btn-header">
                    <i class="fa-solid fa-print"></i> Print Details
                </button>
            </div>
            <div class="detail-badges">
                <span class="status-badge {{ $statusClass }}" data-translate-lookup="{{ $statusText }}">{{ $statusText }}</span>
                <span class="badge" data-translate-lookup="{{ strtoupper($job->post_type) }}">{{ strtoupper($job->post_type) }}</span>
                <span class="badge badge-dept" data-translate-lookup="{{ $job->department->name ?? 'Government Ministry' }}">{{ $job->department->name ?? 'Government Ministry' }}</span>
                <span class="badge" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;" data-translate-prefix="📍 " data-translate-lookup="{{ $job->state->name ?? 'Pan India' }}">📍 {{ $job->state->name ?? 'Pan India' }}</span>
                @if($job->district)
                    <span class="badge" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;" data-translate-prefix="🏢 District: " data-translate-lookup="{{ $job->district->name }}">🏢 District: {{ $job->district->name }}</span>
                @endif
                <span class="badge badge-dept" data-translate-lookup="{{ $job->qualification->name ?? 'Degree Required' }}">{{ $job->qualification->name ?? 'Degree Required' }}</span>
                @if($job->advertisement_number)
                    <span class="badge" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">📋 Advt No: {{ $job->advertisement_number }}</span>
                @endif
            </div>
        </header>

        <!-- Social Share Strip -->
        <x-job-details.social-share :url="request()->url()" :title="$pageTitle" />

        <!-- Recruiting Board Brand Identity Strip -->
        <div class="board-branding-strip">
            <div class="board-logo" style="background: {{ $boardColor }}; color: #fff;">
                {{ $boardShort }}
            </div>
            <div class="board-meta">
                <span class="board-dept-name">{{ $boardName }}</span>
                <span class="board-state">{{ $job->state->name ?? 'Central Government' }} Notification</span>
            </div>
        </div>

        <!-- Short Information Context Card -->
        <div class="short-info-card-block">
            <h5 class="short-info-title"><i class="fa-solid fa-circle-info"></i> Short Information</h5>
            <p class="short-info-text">
                @if($aiContent && !empty($aiContent->summary))
                    {!! strip_tags($aiContent->summary) !!}
                @else
                    {!! Str::limit(strip_tags($job->description), 280) !!}
                @endif
                Candidates who are interested in this recruitment can read the notification details, exam dates, salary ranges, and eligibility criteria online before applying.
            </p>
        </div>

        <!-- Dynamic Split Info Panel (Dates & Fees) -->
        <div class="split-info-card">
            <!-- Left: Important Dates -->
            <div class="split-info-column">
                <h5 class="column-title"><i class="fa-regular fa-calendar-days"></i> <span data-i18n="important_dates">Important Dates</span></h5>
                <ul class="info-list">
                    <li>
                        <span class="info-label" data-i18n="app_begin">Application Begin:</span>
                        <span class="info-val">
                            @if($job->start_date)
                                {{ $job->start_date->format('d/m/Y') }}
                            @elseif($job->published_at)
                                {{ $job->published_at->format('d/m/Y') }}
                            @else
                                <span data-translate-lookup="Refer Notification">Refer Notification</span>
                            @endif
                        </span>
                    </li>
                    <li>
                        <span class="info-label" data-i18n="last_date_apply">Last Date to Apply:</span>
                        <span class="info-val deadline-text">
                            @if($job->last_date_to_apply)
                                {{ $job->last_date_to_apply->format('d/m/Y') }}
                            @else
                                <span data-translate-lookup="Announced Soon">Announced Soon</span>
                            @endif
                        </span>
                    </li>
                    <li>
                        <span class="info-label" data-i18n="fee_last_date">Online Fee Last Date:</span>
                        <span class="info-val">
                            @if($job->last_date_to_apply)
                                {{ $job->last_date_to_apply->format('d/m/Y') }}
                            @else
                                <span data-translate-lookup="Announced Soon">Announced Soon</span>
                            @endif
                        </span>
                    </li>
                    <li>
                        <span class="info-label" data-i18n="exam_date_lbl">Exam Date:</span>
                        <span class="info-val exam-text">
                            @if($job->exam_date)
                                {{ $job->exam_date->format('d/m/Y') }}
                            @else
                                <span data-translate-lookup="Announced Soon">Announced Soon</span>
                            @endif
                        </span>
                    </li>
                    @if($job->result_date)
                        <li>
                            <span class="info-label" data-i18n="result_date_lbl">Expected Result Date:</span>
                            <span class="info-val result-text">{{ $job->result_date->format('d/m/Y') }}</span>
                        </li>
                    @endif
                </ul>
            </div>

            <!-- Right: Application Fees -->
            <div class="split-info-column">
                <h5 class="column-title"><i class="fa-solid fa-indian-rupee-sign"></i> <span data-i18n="app_fee_lbl">Application Fee</span></h5>
                <ul class="info-list">
                    @if($job->application_fee > 0)
                        <li>
                            <span class="info-label" data-i18n="fee_gen">General / OBC / EWS:</span>
                            <span class="info-val">₹ {{ number_format($job->application_fee, 2) }}</span>
                        </li>
                        <li>
                            <span class="info-label" data-i18n="fee_sc">SC / ST / PH:</span>
                            <span class="info-val">₹ 0.00 <span data-translate-prefix="(" data-translate-suffix=")" data-translate-lookup="Exempted">(Exempted)</span></span>
                        </li>
                        <li>
                            <span class="info-label" data-i18n="fee_female">Females (All Category):</span>
                            <span class="info-val">₹ 0.00 <span data-translate-prefix="(" data-translate-suffix=")" data-translate-lookup="Exempted">(Exempted)</span></span>
                        </li>
                    @else
                        <li>
                            <span class="info-label" data-i18n="fee_all">All Category Candidates:</span>
                            <span class="info-val text-success" data-translate-lookup="Free (No Fee)">Free (No Fee)</span>
                        </li>
                    @endif
                    <li class="fee-note">
                        <span class="info-label" data-i18n="pay_mode_lbl">Payment Mode:</span>
                        <span class="info-val" data-i18n="pay_mode_desc">Pay the examination fee through Debit Card, Credit Card, Net Banking, or UPI mode only.</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Age Limit Card -->
        <div class="age-limit-card">
            <h5 class="column-title" style="border-bottom:none; margin-bottom:0; padding-bottom:0;"><i class="fa-regular fa-clock"></i> <span data-i18n="age_limit_title">Age Limit Details</span></h5>
            <div class="age-grid">
                <div class="age-box">
                    <span class="age-label" data-i18n="age_min_lbl">Minimum Age</span>
                    <span class="age-val">{{ $job->age_min ?? '18' }} <span data-translate-lookup="Years">Years</span></span>
                </div>
                <div class="age-box">
                    <span class="age-label" data-i18n="age_max_lbl">Maximum Age</span>
                    <span class="age-val">{{ $job->age_max ?? ($job->age_limit ?? '32') }} <span data-translate-lookup="Years">Years</span></span>
                </div>
            </div>
            <div class="age-cutoff-info">
                <strong data-i18n="age_ref_lbl">Age Limit Reference:</strong> <span data-i18n="age_relaxation_desc">Calculated based on the board's recruitment guidelines. Age relaxation is applicable extra as per government reservation rules.</span>
            </div>
        </div>

        <!-- Recruitment Update Timeline & Lifecycle -->
        @if($timeline && $timeline->count() > 1)
            <section class="details-section" style="border-top:none; padding-top:0;">
                <h4 style="font-family:'Outfit'; color:var(--accent-color); margin-bottom:0.75rem;"><i class="fa-solid fa-code-fork"></i> <span data-i18n="lifecycle_title">Recruitment Lifecycle & Update Timeline</span></h4>
                <div class="timeline-container">
                    @foreach($timeline as $item)
                        @php
                            $isCurrent = ($item->id === $job->id);
                            $itemType = match($item->post_type) {
                                'job' => 'Original Announcement',
                                'admit_card' => 'Admit Card Available',
                                'result' => 'Final Exam Results',
                                'answer_key' => 'Answer Key Objections',
                                'syllabus' => 'Exam Syllabus Published',
                                'cutoff' => 'Declared Cutoffs',
                                'notice' => 'Official Notice / Corrigendum',
                                default => ucfirst($item->post_type)
                            };
                        @endphp
                        <div class="timeline-node {{ $isCurrent ? 'current-node' : '' }}">
                            <div class="node-icon">
                                @if($item->post_type === 'job') <i class="fa-solid fa-bullhorn"></i>
                                @elseif($item->post_type === 'admit_card') <i class="fa-solid fa-id-card"></i>
                                @elseif($item->post_type === 'result') <i class="fa-solid fa-trophy"></i>
                                @elseif($item->post_type === 'answer_key') <i class="fa-solid fa-key"></i>
                                @elseif($item->post_type === 'syllabus') <i class="fa-solid fa-book-open"></i>
                                @else <i class="fa-solid fa-circle-info"></i>
                                @endif
                            </div>
                            <div class="node-content">
                                <span class="node-date">{{ $item->published_at ? $item->published_at->format('d M Y') : $item->created_at->format('d M Y') }}</span>
                                <h5 class="node-title">
                                    @if($isCurrent)
                                        <strong><span data-translate-lookup="{{ $itemType }}">{{ $itemType }}</span>: {{ $item->title }} <span data-translate-lookup="(This Page)">(This Page)</span></strong>
                                    @else
                                        <a href="{{ route('seo.job_detail', ['slug' => $item->slug]) }}"><span data-translate-lookup="{{ $itemType }}">{{ $itemType }}</span>: {{ $item->title }}</a>
                                    @endif
                                </h5>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <!-- Vacancy Details Card -->
        <section class="details-section">
            <h4 style="font-family:'Outfit'; color:var(--accent-color); margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem;">
                <i class="fa-solid fa-list-check"></i> Vacancy Details
            </h4>
            @if($job->vacancyDetails && $job->vacancyDetails->count() > 0)
                <div class="vacancy-table-wrapper" style="overflow-x: auto; background: rgba(255,255,255,0.01); border: 1px solid var(--border-color); border-radius: 12px; padding: 1rem; margin-bottom: 1.5rem;">
                    <table class="vacancy-detail-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th style="padding: 0.75rem 1rem; font-weight: 700; color: var(--text-primary); width: 25%;">Post Name</th>
                                <th style="padding: 0.75rem 1rem; font-weight: 700; color: var(--text-primary); text-align: center; width: 15%;">Total Post</th>
                                <th style="padding: 0.75rem 1rem; font-weight: 700; color: var(--text-primary); width: 60%;">Post Recruitment Eligibility Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($job->vacancyDetails as $vd)
                                <tr>
                                    <td style="padding: 0.75rem 1rem; color: var(--text-primary); font-weight: 600;">{{ $vd->post_name }}</td>
                                    <td style="padding: 0.75rem 1rem; text-align: center;">
                                        <span class="badge badge-vacancy-number" style="background: rgba(37, 99, 235, 0.1); color: var(--accent-color); font-weight: 700; padding: 0.25rem 0.75rem; border-radius: 6px; display: inline-block;">
                                            {{ $vd->total_post }}
                                        </span>
                                    </td>
                                    <td style="padding: 0.75rem 1rem; color: var(--text-secondary); white-space: pre-line; line-height: 1.6;">{{ $vd->eligibility }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="background: rgba(255,255,255,0.01); border: 1px dashed var(--border-color); border-radius: 12px; padding: 2rem; text-align: center; color: var(--text-secondary);">
                    <i class="fa-solid fa-folder-open" style="font-size: 2rem; margin-bottom: 0.5rem; opacity: 0.5;"></i>
                    <p style="margin: 0; font-size: 0.9rem;">No vacancy details specified for this post.</p>
                </div>
            @endif
        </section>

        <!-- Category Wise Vacancy Details Card -->
        <section class="details-section">
            <h4 style="font-family:'Outfit'; color:var(--accent-color); margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem;">
                <i class="fa-solid fa-chart-pie"></i> Category Wise Vacancy Details
            </h4>
            @if($job->categoryWiseVacancies && $job->categoryWiseVacancies->count() > 0)
                <div class="vacancy-table-wrapper" style="overflow-x: auto; background: rgba(255,255,255,0.01); border: 1px solid var(--border-color); border-radius: 12px; padding: 1rem; margin-bottom: 1.5rem;">
                    <table class="vacancy-detail-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th style="padding: 0.75rem 1rem; font-weight: 700; color: var(--text-primary);">Post Name</th>
                                <th style="padding: 0.75rem 1rem; font-weight: 700; color: var(--text-primary); text-align: right;">UR</th>
                                <th style="padding: 0.75rem 1rem; font-weight: 700; color: var(--text-primary); text-align: right;">EWS</th>
                                <th style="padding: 0.75rem 1rem; font-weight: 700; color: var(--text-primary); text-align: right;">EBC</th>
                                <th style="padding: 0.75rem 1rem; font-weight: 700; color: var(--text-primary); text-align: right;">BC</th>
                                <th style="padding: 0.75rem 1rem; font-weight: 700; color: var(--text-primary); text-align: right;">BC (F)</th>
                                <th style="padding: 0.75rem 1rem; font-weight: 700; color: var(--text-primary); text-align: right;">SC</th>
                                <th style="padding: 0.75rem 1rem; font-weight: 700; color: var(--text-primary); text-align: right;">ST</th>
                                <th style="padding: 0.75rem 1rem; font-weight: 700; color: var(--text-primary); text-align: right; background: rgba(37, 99, 235, 0.05);">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($job->categoryWiseVacancies as $cwv)
                                <tr>
                                    <td style="padding: 0.75rem 1rem; color: var(--text-primary); font-weight: 600;">{{ $cwv->post_name }}</td>
                                    <td style="padding: 0.75rem 1rem; text-align: right; color: var(--text-secondary);">{{ $cwv->ur }}</td>
                                    <td style="padding: 0.75rem 1rem; text-align: right; color: var(--text-secondary);">{{ $cwv->ews }}</td>
                                    <td style="padding: 0.75rem 1rem; text-align: right; color: var(--text-secondary);">{{ $cwv->ebc }}</td>
                                    <td style="padding: 0.75rem 1rem; text-align: right; color: var(--text-secondary);">{{ $cwv->bc }}</td>
                                    <td style="padding: 0.75rem 1rem; text-align: right; color: var(--text-secondary);">{{ $cwv->bc_female }}</td>
                                    <td style="padding: 0.75rem 1rem; text-align: right; color: var(--text-secondary);">{{ $cwv->sc }}</td>
                                    <td style="padding: 0.75rem 1rem; text-align: right; color: var(--text-secondary);">{{ $cwv->st }}</td>
                                    <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 700; color: var(--accent-color); background: rgba(37, 99, 235, 0.05);">{{ $cwv->total }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="background: rgba(255,255,255,0.01); border: 1px dashed var(--border-color); border-radius: 12px; padding: 2rem; text-align: center; color: var(--text-secondary);">
                    <i class="fa-solid fa-folder-open" style="font-size: 2rem; margin-bottom: 0.5rem; opacity: 0.5;"></i>
                    <p style="margin: 0; font-size: 0.9rem;">No category wise vacancy details specified for this post.</p>
                </div>
            @endif
        </section>

        <!-- Enriched AI Summary Card -->
        @if($aiContent && !empty($aiContent->summary))
            <div class="ai-summary-card">
                <div class="ai-badge">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                    <span data-i18n="ai_summary_lbl">AI Summary</span>
                </div>
                <div style="font-size: 0.95rem; color: var(--text-primary); line-height: 1.7;">
                    {!! $aiContent->summary !!}
                </div>
            </div>
        @endif

        <!-- Vacancy Distribution Breakdown (Tabular Matrix redesign) -->
        @if($job->categoryVacancies && $job->categoryVacancies->count() > 0)
            <section class="details-section">
                <h4 style="font-family:'Outfit'; color:var(--accent-color); margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem;" data-i18n="vacancy_breakdown_title">
                    Vacancy Distribution Breakdown
                </h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
                    @foreach(['post', 'caste_category', 'department', 'trade', 'discipline'] as $type)
                        @php
                            $items = $job->categoryVacancies->where('type', $type);
                        @endphp
                        @if($items->count() > 0)
                            @php
                                $groupTitle = '';
                                if ($type === 'post') $groupTitle = 'Trade-wise / Post-wise Posts';
                                elseif ($type === 'caste_category') $groupTitle = 'Category-wise Posts';
                                elseif ($type === 'department') $groupTitle = 'Department-wise Posts';
                                elseif ($type === 'trade') $groupTitle = 'Trade-wise Posts';
                                elseif ($type === 'discipline') $groupTitle = 'Discipline-wise Posts';
                            @endphp
                            <div style="background: rgba(255,255,255,0.01); border: 1px solid var(--border-color); border-radius: 12px; padding: 1.25rem;">
                                <h5 style="font-size:0.85rem; text-transform:uppercase; color:var(--text-secondary); font-weight:700; margin-bottom:0.75rem; border-bottom: 1px solid var(--border-color); padding-bottom:0.4rem; font-family:'Outfit';">
                                    <span data-translate-lookup="{{ $groupTitle }}">{{ $groupTitle }}</span>
                                </h5>
                                <div class="vacancy-type-table-wrapper">
                                    <table class="vacancy-detail-table">
                                        <thead>
                                            <tr>
                                                <th>Post / Category</th>
                                                <th style="text-align: right;">Vacancies</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($items as $cv)
                                                <tr>
                                                    <td><span data-translate-lookup="{{ $cv->category_name }}">{{ $cv->category_name }}</span></td>
                                                    <td style="text-align: right;">
                                                        <span class="badge badge-vacancy-number">
                                                            {{ $cv->vacancy_count }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </section>
        @endif

        <!-- Physical Eligibility Matrix (Dynamic display for police/military notifications) -->
        @if($isPhysicalJob)
            <section class="details-section">
                <h4 style="font-family:'Outfit'; color:var(--accent-color); margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem;">
                    <i class="fa-solid fa-person-running"></i> Physical Standards & Eligibility
                </h4>
                <p style="font-size:0.85rem; color:var(--text-secondary); margin-bottom:1rem;">
                    Indicative physical criteria matching standard police and defense recruitment commissions:
                </p>
                <div class="physical-table-container">
                    <table class="physical-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Physical Test Element</th>
                                <th>Male (General/OBC/BC)</th>
                                <th>Male (SC/ST)</th>
                                <th>Female (All Category)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Height</strong></td>
                                <td>170 Cms</td>
                                <td>162.5 Cms</td>
                                <td>157 Cms</td>
                            </tr>
                            <tr>
                                <td><strong>Chest</strong></td>
                                <td>80 - 85 Cms</td>
                                <td>76 - 81 Cms</td>
                                <td>N/A</td>
                            </tr>
                            <tr>
                                <td><strong>Running</strong></td>
                                <td>1.6 Km in 6 Minutes</td>
                                <td>1.6 Km in 6 Minutes</td>
                                <td>800 Meters in 4 Minutes</td>
                            </tr>
                            <tr>
                                <td><strong>Long Jump</strong></td>
                                <td>12 Feet</td>
                                <td>12 Feet</td>
                                <td>9 Feet</td>
                            </tr>
                            <tr>
                                <td><strong>High Jump</strong></td>
                                <td>3 Feet 9 Inches</td>
                                <td>3 Feet 9 Inches</td>
                                <td>3 Feet</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div style="font-size: 0.8rem; color: var(--text-secondary); line-height: 1.5; border-top: 1px solid var(--border-color); padding-top: 0.5rem;">
                    * <em>Physical standards vary by board guidelines. Always check the official PDF notification below for final validated requirements.</em>
                </div>
            </section>
        @endif

        <!-- Description -->
        <section class="details-section">
            <h4 data-i18n="overview_title">Recruitment Overview & Requirements</h4>
            @if($aiContent && !empty($aiContent->eligibility))
                <div style="margin-bottom: 1.5rem;">
                    <h5 style="font-family: 'Outfit'; font-size: 1.05rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem;" data-i18n="eligibility_title">Detailed Eligibility Criteria</h5>
                    <div style="font-size: 0.95rem; color: var(--text-secondary); line-height: 1.7;">
                        {!! \Illuminate\Support\Str::markdown($aiContent->eligibility) !!}
                    </div>
                </div>
            @endif
            
            <h5 style="font-family: 'Outfit'; font-size: 1.05rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem; margin-top: 1rem;" data-i18n="original_overview_title">Original Announcement Overview</h5>
            <div class="original-overview-content">{!! $job->description !!}</div>
        </section>

        <!-- Exam Pattern & Syllabus -->
        @if($job->exam_pattern)
            <section class="details-section" id="exam-pattern-section">
                <h4 data-i18n="syllabus_title">Official Syllabus & Exam Pattern</h4>
                <div>{!! $job->exam_pattern !!}</div>
            </section>
        @endif

        <!-- Selection Process -->
        @if($aiContent && !empty($aiContent->selection_process))
            <section class="details-section">
                <h4 data-i18n="selection_details_title">Selection Process Details</h4>
                <div style="font-size: 0.95rem; color: var(--text-secondary); line-height: 1.7;">
                    {!! $aiContent->selection_process !!}
                </div>
            </section>
        @elseif($job->selection_process)
            <section class="details-section">
                <h4 data-i18n="selection_process_lbl">Selection Process</h4>
                <div>{!! $job->selection_process !!}</div>
            </section>
        @endif

        <!-- Enriched AI FAQs -->
        @if($aiContent && !empty($aiContent->faqs) && count($aiContent->faqs) > 0)
            <section class="details-section">
                <h4 data-i18n="faqs_title">Frequently Asked Questions (FAQs)</h4>
                <div class="faq-accordion">
                    @foreach($aiContent->faqs as $faq)
                        @if(!empty($faq['question']) && !empty($faq['answer']))
                            <div class="faq-item">
                                <div class="faq-header">{{ $faq['question'] }}</div>
                                <div class="faq-body">{!! nl2br(e($faq['answer'])) !!}</div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </section>
        @endif

        <!-- Dynamic board-specific guidelines -->
        <section class="details-section" style="border-top: 1px solid var(--border-color); padding-top: 1.5rem;">
            <h4 data-i18n="instructions_title">How to Fill the Form - Step-by-Step Instructions</h4>
            <ul style="list-style-type: none; padding-left: 0; display: flex; flex-direction: column; gap: 0.75rem;">
                <li style="display: flex; gap: 0.75rem; font-size: 0.95rem; color: var(--text-secondary); line-height: 1.6;">
                    <span style="color:var(--accent-color); font-weight:bold;">1.</span>
                    <span data-i18n="instr_1"><strong>OTR Registration:</strong> Most recruitment boards (like UPSC, SSC, and State PSCs) mandate completing a One-Time Registration (OTR) profile on their official website before submitting any exam application forms.</span>
                </li>
                @if(str_contains(strtolower($job->title), 'ssc') || str_contains(strtolower($job->description), 'webcam') || str_contains(strtolower($job->description), 'live photo'))
                    <li style="display: flex; gap: 0.75rem; font-size: 0.95rem; color: var(--text-secondary); line-height: 1.6;">
                        <span style="color:var(--accent-color); font-weight:bold;">2.</span>
                        <span data-i18n="instr_2"><strong>Webcam Live Photograph:</strong> SSC and other major commissions require taking a live photo of yourself via webcam or through the official mobile app. Stand in front of a light/white background and look straight. Do not wear caps, spectacles, or masks.</span>
                    </li>
                @endif
                <li style="display: flex; gap: 0.75rem; font-size: 0.95rem; color: var(--text-secondary); line-height: 1.6;">
                    <span style="color:var(--accent-color); font-weight:bold;">3.</span>
                    <span data-i18n="instr_3"><strong>Check Credentials & Preview:</strong> Before clicking on final submit, review the form preview thoroughly. Confirm that spelling, dates, caste category, and certificates are accurate.</span>
                </li>
                <li style="display: flex; gap: 0.75rem; font-size: 0.95rem; color: var(--text-secondary); line-height: 1.6;">
                    <span style="color:var(--accent-color); font-weight:bold;">4.</span>
                    <span data-i18n="instr_4"><strong>Fee Payment:</strong> Candidates requiring a fee must complete the payment (using UPI, Credit/Debit cards, or Net banking). Unpaid applications are treated as incomplete and rejected automatically.</span>
                </li>
                <li style="display: flex; gap: 0.75rem; font-size: 0.95rem; color: var(--text-secondary); line-height: 1.6;">
                    <span style="color:var(--accent-color); font-weight:bold;">5.</span>
                    <span data-i18n="instr_5"><strong>Print Confirmation:</strong> Save and print a hard copy of the final submitted application form for future reference and exam attendance confirmation.</span>
                </li>
            </ul>
        </section>

        <!-- Official Useful Important Links (Enriched with related timeline files) -->
        <section class="details-section">
            <h4 data-i18n="useful_links_title">Some Useful Important Links</h4>
            <div class="links-table-container">
                <table class="links-table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th data-i18n="resource_name_th">Resource / Action Name</th>
                            <th style="text-align: right;" data-i18n="action_link_th">Action Link</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Sibling timeline events dynamically resolving -->
                        @if($admitCardLink)
                            <tr>
                                <td><strong style="color: var(--accent-color);">Download Admit Card / Hall Ticket</strong></td>
                                <td style="text-align: right;">
                                    <a href="{{ $admitCardLink }}" class="btn-link-action" style="background:#3b82f6;">
                                        <i class="fa-solid fa-id-card"></i> Click Here
                                    </a>
                                </td>
                            </tr>
                        @endif
                        @if($answerKeyLink)
                            <tr>
                                <td><strong style="color: var(--accent-color);">Download Answer Key</strong></td>
                                <td style="text-align: right;">
                                    <a href="{{ $answerKeyLink }}" class="btn-link-action" style="background:#8b5cf6;">
                                        <i class="fa-solid fa-key"></i> Click Here
                                    </a>
                                </td>
                            </tr>
                        @endif
                        @if($resultLink)
                            <tr>
                                <td><strong style="color: #10b981;">Download Written Exam Result</strong></td>
                                <td style="text-align: right;">
                                    <a href="{{ $resultLink }}" class="btn-link-action btn-pulse">
                                        <i class="fa-solid fa-trophy"></i> Click Here
                                    </a>
                                </td>
                            </tr>
                        @endif
                        @if($cutoffLink)
                            <tr>
                                <td><strong style="color: #f59e0b;">Check Declared Cutoff Marks</strong></td>
                                <td style="text-align: right;">
                                    <a href="{{ $cutoffLink }}" class="btn-link-action" style="background:#f59e0b;">
                                        <i class="fa-solid fa-chart-bar"></i> Click Here
                                    </a>
                                </td>
                            </tr>
                        @endif
                        @if($syllabusLink)
                            <tr>
                                <td><strong style="color: var(--accent-color);">Download Detailed PDF Syllabus</strong></td>
                                <td style="text-align: right;">
                                    <a href="{{ $syllabusLink }}" class="btn-link-action" style="background:#6366f1;">
                                        <i class="fa-solid fa-book-open"></i> Click Here
                                    </a>
                                </td>
                            </tr>
                        @endif
                        @if($noticeLink)
                            <tr>
                                <td><strong style="color: var(--text-secondary);">Download Official Corrigendum / Notice</strong></td>
                                <td style="text-align: right;">
                                    <a href="{{ $noticeLink }}" class="btn-link-action" style="background:#4b5563;">
                                        <i class="fa-solid fa-circle-exclamation"></i> Click Here
                                    </a>
                                </td>
                            </tr>
                        @endif

                        <!-- Standard core links -->
                        @if($job->apply_link && in_array($job->post_type, ['job', 'admission']))
                            <tr>
                                <td><strong data-i18n="apply_online_row">Apply Online (Registration & Login)</strong></td>
                                <td style="text-align: right;">
                                    <a href="{{ $job->apply_link }}" target="_blank" rel="nofollow noopener" class="btn-link-action btn-pulse">
                                        <i class="fa-solid fa-pen-to-square"></i> <span data-i18n="apply_online_btn">Apply Online</span>
                                    </a>
                                </td>
                            </tr>
                        @endif
                        @if($job->notification_pdf_path)
                            <tr>
                                <td><strong data-i18n="download_pdf_row">Download Official Notification PDF</strong></td>
                                <td style="text-align: right;">
                                    @php
                                        $pdfUrl = str_starts_with($job->notification_pdf_path, 'http') 
                                            ? $job->notification_pdf_path 
                                            : Storage::url($job->notification_pdf_path);
                                    @endphp
                                    <a href="{{ $pdfUrl }}" target="_blank" rel="noopener" class="btn-link-action" style="background:#dc2626;">
                                        <i class="fa-solid fa-file-pdf"></i> <span data-i18n="download_pdf_btn">Download PDF</span>
                                    </a>
                                </td>
                            </tr>
                        @endif
                        @if($job->exam_pattern)
                            <tr>
                                <td><strong data-i18n="download_syllabus_row">Download Syllabus & Exam Pattern</strong></td>
                                <td style="text-align: right;">
                                    <a href="#exam-pattern-section" class="btn-link-action" style="background:#8b5cf6;">
                                        <i class="fa-solid fa-book-open"></i> <span data-i18n="view_syllabus_btn">View Syllabus</span>
                                    </a>
                                </td>
                            </tr>
                        @endif
                        @if($job->official_website_link)
                            <tr>
                                <td>
                                    <strong>
                                        @if($job->post_type === 'scholarship')
                                            Official Scholarship Website
                                        @elseif($job->post_type === 'admission')
                                            Official Admission Website
                                        @elseif(in_array($job->post_type, ['admit_card', 'result', 'answer_key', 'syllabus', 'notice']))
                                            Official Board Website
                                        @else
                                            <span data-i18n="official_website_row">Official Recruitment Website</span>
                                        @endif
                                    </strong>
                                </td>
                                <td style="text-align: right;">
                                    <a href="{{ $job->official_website_link }}" target="_blank" rel="nofollow noopener" class="btn-link-action" style="background:#4b5563;">
                                        <i class="fa-solid fa-globe"></i> <span data-i18n="visit_website_btn">Visit Website</span>
                                    </a>
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <td><strong data-i18n="join_telegram_row">Join Telegram Alerts Channel</strong></td>
                            <td style="text-align: right;">
                                <a href="https://t.me/sarkariresult" target="_blank" rel="noopener" class="btn-link-action" style="background:#229ED9;">
                                    <i class="fa-brands fa-telegram"></i> <span data-i18n="telegram_channel_btn">Telegram Channel</span>
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td><strong data-i18n="join_whatsapp_row">Join WhatsApp Updates Channel</strong></td>
                            <td style="text-align: right;">
                                <a href="https://whatsapp.com/channel/sarkariresult" target="_blank" rel="noopener" class="btn-link-action" style="background:#25D366;">
                                    <i class="fa-brands fa-whatsapp"></i> <span data-i18n="whatsapp_group_btn">WhatsApp Group</span>
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Social Share Strip (Footer placement) -->
        <x-job-details.social-share :url="request()->url()" :title="$pageTitle" />

        <!-- Disclaimer Liability Alert Card -->
        <div class="disclaimer-alert-card">
            <span class="disclaimer-title"><i class="fa-solid fa-triangle-exclamation"></i> <span data-i18n="seo_disclaimer_title">Disclaimer & Important Notice</span></span>
            <p class="disclaimer-text" data-i18n="seo_disclaimer_text">
                Although every effort has been made to ensure the accuracy and completeness of the information on this website, GovJobs is not responsible for any inadvertent errors in notification details or outcomes. Candidates are strongly advised to download the official notification PDF and verify all specifications, timelines, and requirements directly on the official board website before submitting an application.
            </p>
        </div>

        <!-- Lead Capture Form / Submission -->
        <!-- <section class="apply-panel">
            <div class="apply-card">
                @auth
                    <h3 style="font-family: 'Outfit'; font-size: 1.3rem; margin-bottom: 0.5rem; color: var(--accent-color);" data-i18n="submit_app_title">Submit Job Application</h3>
                    <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1.25rem;" data-i18n="submit_app_desc">Upload your resume to complete your application for this government posting instantly.</p>
                    
                    <form id="standaloneRecruitmentApplyForm" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="standaloneAppResume" data-i18n="select_cv_lbl">Select CV / Resume (PDF, DOC, DOCX up to 2MB)</label>
                            <input type="file" name="resume" id="standaloneAppResume" class="form-control" required style="padding: 0.65rem 1rem;">
                            <div class="invalid-feedback" id="standaloneResumeError" style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem;"></div>
                        </div>
                        <button type="submit" class="form-btn" id="standaloneApplySubmitBtn" style="margin-top: 0.75rem;" data-i18n="submit_app_btn">
                            Submit Application Now
                        </button>
                    </form>
                @else
                    <div style="text-align: center; padding: 1.5rem 0;">
                        <h3 style="font-family: 'Outfit'; font-size: 1.25rem; margin-bottom: 0.5rem; color: var(--text-primary);" data-i18n="apply_announcement_title">Apply for this Announcement</h3>
                        <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1.25rem;" data-i18n="apply_announcement_desc">Please register or login to your candidate dashboard to apply directly using your resume profile.</p>
                        <button class="form-btn trigger-auth-redirect-btn" style="width: auto; padding: 0.75rem 2rem; display: inline-block;" data-i18n="login_register_apply_btn">
                            Login / Register to Apply
                        </button>
                    </div>
                @endauth
            </div>
        </section> -->
    </article>

    {{-- ─── Automated Internal Linking System ────────────────────────────── --}}
    @if(!empty($internalLinks))
        @include('components.internal-linking.related-links', [
            'links' => $internalLinks,
            'currentJob' => $job,
        ])
    @endif
</main>

<!-- Mobile Sticky Bar Component -->
<x-job-details.sticky-apply-bar :job="$job" />

@endsection

@section('schema')
<!-- BreadcrumbList Schema -->
<script type="application/ld+json">
{!! json_encode($seoService->getSchemaService()->getBreadcrumbListSchema($breadcrumbs), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
</script>

<!-- JobPosting Schema -->
<script type="application/ld+json">
{!! json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
</script>

@if($aiContent && !empty($aiContent->faqs) && count($aiContent->faqs) > 0)
<!-- FAQPage Schema -->
<script type="application/ld+json">
{!! json_encode($seoService->getSchemaService()->getFAQPageSchema($aiContent->faqs), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
</script>
@endif

<!-- Speakable Schema (Voice Search Optimization) -->
<script type="application/ld+json">
{!! json_encode($seoService->getSchemaService()->getSpeakableSchema($pageTitle, $metaDescription, request()->url()), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
</script>

<!-- GovernmentService Schema -->
<script type="application/ld+json">
{!! json_encode($seoService->getSchemaService()->getGovernmentServiceSchema($job), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
</script>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // standalone recruitment submission handler
        const applyForm = document.getElementById('standaloneRecruitmentApplyForm');
        if (applyForm) {
            applyForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const btn = document.getElementById('standaloneApplySubmitBtn');
                const fileInput = document.getElementById('standaloneAppResume');
                const errorDiv = document.getElementById('standaloneResumeError');
                
                errorDiv.style.display = 'none';
                errorDiv.textContent = '';
                
                if (fileInput.files.length === 0) {
                    if (typeof showToast === 'function') showToast('Please select a resume file.', 'error');
                    return;
                }

                btn.disabled = true;
                btn.textContent = 'Uploading...';
                
                const formData = new FormData(this);
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                fetch(`/api/jobs/{{ $job->id }}/apply`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => response.json().then(data => ({ status: response.status, body: data })))
                .then(res => {
                    if (res.status >= 200 && res.status < 300) {
                        if (typeof showToast === 'function') showToast(res.body.message || 'Success', 'success');
                        
                        applyForm.innerHTML = `
                            <div style="text-align: center; padding: 1.5rem 0; color: #10b981;">
                                <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="margin-bottom: 0.5rem;"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <div style="font-weight: 700; font-size: 1.05rem;">Application Submitted Successfully!</div>
                                <p style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.25rem;">You can track the recruitment stage inside your candidate dashboard.</p>
                            </div>
                        `;
                    } else if (res.status === 422) {
                        btn.disabled = false;
                        btn.textContent = 'Submit Application Now';
                        if (typeof showToast === 'function') showToast(res.body.message || 'File upload validation failed.', 'error');
                        if (res.body.errors && res.body.errors.resume) {
                            errorDiv.textContent = res.body.errors.resume[0];
                            errorDiv.style.display = 'block';
                        }
                    } else {
                        throw new Error('Server error');
                    }
                })
                .catch(err => {
                    btn.disabled = false;
                    btn.textContent = 'Submit Application Now';
                    if (typeof showToast === 'function') showToast('Submission failed. Connection error.', 'error');
                });
            });
        }

        // FAQ Accordion click transition handler
        document.querySelectorAll('.faq-header').forEach(header => {
            header.addEventListener('click', function() {
                const item = this.closest('.faq-item');
                const body = item.querySelector('.faq-body');
                
                if (item.classList.contains('active')) {
                    item.classList.remove('active');
                    body.style.maxHeight = '0';
                } else {
                    document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('active'));
                    document.querySelectorAll('.faq-body').forEach(b => b.style.maxHeight = '0');
                    
                    item.classList.add('active');
                    body.style.maxHeight = body.scrollHeight + 'px';
                }
            });
        });
    });
</script>
@endsection
