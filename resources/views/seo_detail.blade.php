@inject('seoService', 'App\Domains\Jobs\Services\SeoService')
@extends('layouts.app')

@section('title', $pageTitle)

@section('content')
<style>
    /* Premium Breadcrumbs styling */
    .breadcrumb-trail {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.85rem;
        color: var(--text-secondary);
        margin: 1.5rem 0;
        font-family: 'Outfit', sans-serif;
    }
    .breadcrumb-trail a {
        color: var(--accent-color);
        text-decoration: none;
        transition: opacity 0.2s;
    }
    .breadcrumb-trail a:hover {
        opacity: 0.8;
        text-decoration: underline;
    }
    .breadcrumb-separator {
        opacity: 0.5;
    }

    /* Detail Card Container */
    .detail-card {
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 2.5rem;
        margin-bottom: 2.5rem;
        box-shadow: var(--card-shadow);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
    }
    
    .detail-header-block {
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 1.5rem;
        margin-bottom: 2rem;
    }

    .detail-header-block h1 {
        font-family: 'Outfit', sans-serif;
        font-size: 2.2rem;
        font-weight: 800;
        line-height: 1.25;
        color: var(--text-primary);
        margin-bottom: 0.75rem;
    }

    .detail-badges {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.5rem;
        margin-top: 0.5rem;
    }

    /* Split Info Card */
    .split-info-card {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    @media (max-width: 768px) {
        .split-info-card {
            grid-template-columns: 1fr;
        }
    }
    .split-info-column {
        background: rgba(255, 255, 255, 0.005);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.5rem;
        backdrop-filter: blur(8px);
    }
    .column-title {
        font-family: 'Outfit', sans-serif;
        font-size: 1.15rem;
        font-weight: 800;
        color: var(--accent-color);
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        border-bottom: 2px solid var(--border-color);
        padding-bottom: 0.5rem;
    }
    .info-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
    }
    .info-list li {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px dashed rgba(255, 255, 255, 0.05);
        padding-bottom: 0.6rem;
        font-size: 0.95rem;
    }
    .info-list li:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    .info-label {
        color: var(--text-secondary);
        font-weight: 500;
    }
    .info-val {
        color: var(--text-primary);
        font-weight: 700;
        text-align: right;
    }
    .deadline-text {
        color: #ef4444 !important;
    }
    .exam-text {
        color: var(--accent-color) !important;
    }
    .result-text {
        color: #10b981 !important;
    }
    .fee-note {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 0.25rem;
    }
    .fee-note .info-val {
        text-align: left;
        font-size: 0.85rem;
        color: var(--text-secondary);
        font-weight: normal;
    }

    /* Age Limit Card */
    .age-limit-card {
        background: rgba(255, 255, 255, 0.005);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        backdrop-filter: blur(8px);
    }
    .age-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-top: 1rem;
        margin-bottom: 1rem;
    }
    .age-box {
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 1rem;
        text-align: center;
    }
    .age-label {
        display: block;
        font-size: 0.75rem;
        text-transform: uppercase;
        color: var(--text-secondary);
        font-weight: 700;
        margin-bottom: 0.25rem;
    }
    .age-val {
        font-size: 1.25rem;
        font-weight: 800;
        color: var(--text-primary);
    }
    .age-cutoff-info {
        font-size: 0.85rem;
        color: var(--text-secondary);
        line-height: 1.6;
        border-top: 1px solid var(--border-color);
        padding-top: 0.75rem;
        margin-top: 0.5rem;
    }

    /* Useful Links Table styling */
    .links-table-container {
        border: 1px solid var(--border-color);
        border-radius: 12px;
        overflow: hidden;
        margin-top: 1rem;
        margin-bottom: 2rem;
    }
    .links-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }
    .links-table th {
        background: rgba(37, 99, 235, 0.05);
        color: var(--text-primary);
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        padding: 1rem;
        font-size: 0.95rem;
        border-bottom: 1px solid var(--border-color);
    }
    .links-table td {
        padding: 1rem;
        border-bottom: 1px solid var(--border-color);
        font-size: 0.95rem;
        color: var(--text-primary);
    }
    .links-table tr:last-child td {
        border-bottom: none;
    }
    .links-table tr:hover {
        background: rgba(255, 255, 255, 0.01);
    }
    .btn-link-action {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: var(--accent-color);
        color: #fff !important;
        text-decoration: none !important;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.2s;
    }
    .btn-link-action:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }
    .btn-pulse {
        animation: apply-pulse 2s infinite;
        background: #10b981;
    }
    @keyframes apply-pulse {
        0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
        100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }

    /* Timeline Section styling */
    .timeline-container {
        position: relative;
        padding-left: 2rem;
        margin-top: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .timeline-container::before {
        content: '';
        position: absolute;
        left: 8px;
        top: 8px;
        bottom: 8px;
        width: 2px;
        background: var(--border-color);
    }
    .timeline-node {
        position: relative;
        margin-bottom: 1.5rem;
    }
    .timeline-node:last-child {
        margin-bottom: 0;
    }
    .node-icon {
        position: absolute;
        left: -2rem;
        top: 2px;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: var(--bg-secondary);
        border: 2px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-secondary);
        font-size: 0.55rem;
        z-index: 2;
        transition: all 0.3s;
    }
    .node-content {
        background: rgba(255, 255, 255, 0.005);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 0.75rem 1rem;
        transition: border-color 0.2s;
    }
    .node-date {
        font-size: 0.75rem;
        color: var(--text-secondary);
        font-weight: 600;
        display: block;
        margin-bottom: 0.25rem;
    }
    .node-title {
        font-size: 0.9rem;
        margin: 0;
        color: var(--text-primary);
        font-family: 'Inter', sans-serif;
    }
    .node-title a {
        color: var(--accent-color);
        text-decoration: none;
    }
    .node-title a:hover {
        text-decoration: underline;
    }
    .current-node .node-icon {
        border-color: var(--accent-color);
        color: var(--accent-color);
        background: rgba(37, 99, 235, 0.1);
        transform: scale(1.2);
    }
    .current-node .node-content {
        border-color: rgba(37, 99, 235, 0.4);
        background: rgba(37, 99, 235, 0.02);
    }

    /* Active status badge styles */
    .status-badge {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 99px;
    }
    .status-open {
        background: rgba(16, 185, 129, 0.1);
        color: #10b981;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }
    .status-upcoming {
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
        border: 1px solid rgba(59, 130, 246, 0.2);
    }
    .status-closed {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }
    .status-declared {
        background: rgba(139, 92, 246, 0.1);
        color: #8b5cf6;
        border: 1px solid rgba(139, 92, 246, 0.2);
    }

    /* Content sections */
    .details-section {
        margin-bottom: 2rem;
        border-top: 1px solid var(--border-color);
        padding-top: 1.5rem;
    }

    .details-section h4 {
        font-family: 'Outfit', sans-serif;
        font-size: 1.2rem;
        font-weight: 800;
        color: var(--accent-color);
        margin-bottom: 0.75rem;
    }

    .details-section p, .details-section ul {
        font-size: 0.95rem;
        color: var(--text-secondary);
        line-height: 1.7;
    }

    /* Application form section */
    .apply-panel {
        border-top: 1px solid var(--border-color);
        padding-top: 2rem;
        margin-top: 2.5rem;
    }

    .apply-card {
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.08) 0%, rgba(16, 185, 129, 0.05) 100%);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 2rem;
    }

    /* AI Enriched Styles */
    .ai-summary-card {
        background: linear-gradient(135deg, rgba(139, 92, 246, 0.04) 0%, rgba(37, 99, 235, 0.03) 100%);
        border: 1px dashed rgba(139, 92, 246, 0.4);
        border-radius: 12px;
        padding: 1.5rem 1.75rem;
        margin-bottom: 2rem;
        position: relative;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }
    .ai-badge {
        position: absolute;
        top: -12px;
        right: 20px;
        background: #8b5cf6;
        color: #fff;
        font-size: 0.65rem;
        font-weight: 900;
        padding: 3px 10px;
        border-radius: 99px;
        text-transform: uppercase;
        letter-spacing: 0.07em;
        box-shadow: 0 4px 12px rgba(139, 92, 246, 0.4);
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }
    .faq-accordion {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        margin-top: 1rem;
    }
    .faq-item {
        background: rgba(255, 255, 255, 0.01);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .faq-header {
        padding: 1rem 1.25rem;
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 0.95rem;
        color: var(--text-primary);
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        user-select: none;
        transition: background 0.2s;
    }
    .faq-header:hover {
        background: rgba(255, 255, 255, 0.03);
    }
    .faq-header::after {
        content: '+';
        font-size: 1.1rem;
        color: var(--accent-color);
        font-weight: bold;
        transition: transform 0.3s;
    }
    .faq-item.active {
        border-color: rgba(139, 92, 246, 0.4);
        background: rgba(139, 92, 246, 0.02);
    }
    .faq-item.active .faq-header::after {
        content: '-';
        transform: rotate(180deg);
    }
    .faq-body {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-out, padding 0.3s ease, border-top 0.2s;
        padding: 0 1.25rem;
        color: var(--text-secondary);
        font-size: 0.9rem;
        line-height: 1.6;
        border-top: 1px solid transparent;
    }
    .faq-item.active .faq-body {
        padding: 1rem 1.25rem;
        border-top: 1px solid var(--border-color);
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
@endphp

<div style="max-width: 1000px; margin: 0 auto; padding: 0 5%;">
    <!-- Breadcrumbs -->
    <nav aria-label="Breadcrumb" class="breadcrumb-trail" itemscope itemtype="https://schema.org/BreadcrumbList">
        <span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
            <a itemprop="item" href="/"><span itemprop="name">Home</span></a>
            <meta itemprop="position" content="1">
        </span>
        @foreach($breadcrumbs as $label => $url)
            <span class="breadcrumb-separator">&raquo;</span>
            <span itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                @if($url)
                    <a itemprop="item" href="{{ $url }}"><span itemprop="name">{{ $label }}</span></a>
                @else
                    <span itemprop="name">{{ $label }}</span>
                @endif
                <meta itemprop="position" content="{{ $loop->iteration + 1 }}">
            </span>
        @endforeach
    </nav>

    <!-- Main Detail Card -->
    <article class="detail-card">
        <!-- Header -->
        <header class="detail-header-block">
            <h1>{{ $pageHeader }}</h1>
            <div class="detail-badges">
                <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
                <span class="badge">{{ strtoupper($job->post_type) }}</span>
                <span class="badge badge-dept">{{ $job->department->name ?? 'Government Ministry' }}</span>
                <span class="badge" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;">📍 {{ $job->state->name ?? 'Pan India' }}</span>
                @if($job->district)
                    <span class="badge" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">🏢 District: {{ $job->district->name }}</span>
                @endif
                <span class="badge badge-dept">{{ $job->qualification->name ?? 'Degree Required' }}</span>
                @if($job->advertisement_number)
                    <span class="badge" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">📋 Advt No: {{ $job->advertisement_number }}</span>
                @endif
            </div>
        </header>

        <!-- Dynamic Split Info Panel (Dates & Fees) -->
        <div class="split-info-card">
            <!-- Left: Important Dates -->
            <div class="split-info-column">
                <h5 class="column-title"><i class="fa-regular fa-calendar-days"></i> Important Dates</h5>
                <ul class="info-list">
                    <li>
                        <span class="info-label">Application Begin:</span>
                        <span class="info-val">
                            {{ $job->start_date ? $job->start_date->format('d/m/Y') : ($job->published_at ? $job->published_at->format('d/m/Y') : 'Refer Notification') }}
                        </span>
                    </li>
                    <li>
                        <span class="info-label">Last Date to Apply:</span>
                        <span class="info-val deadline-text">
                            {{ $job->last_date_to_apply ? $job->last_date_to_apply->format('d/m/Y') : 'Announced Soon' }}
                        </span>
                    </li>
                    <li>
                        <span class="info-label">Online Fee Last Date:</span>
                        <span class="info-val">
                            {{ $job->last_date_to_apply ? $job->last_date_to_apply->format('d/m/Y') : 'Announced Soon' }}
                        </span>
                    </li>
                    <li>
                        <span class="info-label">Exam Date:</span>
                        <span class="info-val exam-text">
                            {{ $job->exam_date ? $job->exam_date->format('d/m/Y') : 'Announced Soon' }}
                        </span>
                    </li>
                    @if($job->result_date)
                        <li>
                            <span class="info-label">Expected Result Date:</span>
                            <span class="info-val result-text">{{ $job->result_date->format('d/m/Y') }}</span>
                        </li>
                    @endif
                </ul>
            </div>

            <!-- Right: Application Fees -->
            <div class="split-info-column">
                <h5 class="column-title"><i class="fa-solid fa-indian-rupee-sign"></i> Application Fee</h5>
                <ul class="info-list">
                    @if($job->application_fee > 0)
                        <li>
                            <span class="info-label">General / OBC / EWS:</span>
                            <span class="info-val">₹ {{ number_format($job->application_fee, 2) }}</span>
                        </li>
                        <li>
                            <span class="info-label">SC / ST / PH:</span>
                            <span class="info-val">₹ 0.00 (Exempted)</span>
                        </li>
                        <li>
                            <span class="info-label">Females (All Category):</span>
                            <span class="info-val">₹ 0.00 (Exempted)</span>
                        </li>
                    @else
                        <li>
                            <span class="info-label">All Category Candidates:</span>
                            <span class="info-val text-success">Free (No Fee)</span>
                        </li>
                    @endif
                    <li class="fee-note">
                        <span class="info-label">Payment Mode:</span>
                        <span class="info-val">Pay the examination fee through Debit Card, Credit Card, Net Banking, or UPI mode only.</span>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Age Limit Card -->
        <div class="age-limit-card">
            <h5 class="column-title" style="border-bottom:none; margin-bottom:0; padding-bottom:0;"><i class="fa-regular fa-clock"></i> Age Limit Details</h5>
            <div class="age-grid">
                <div class="age-box">
                    <span class="age-label">Minimum Age</span>
                    <span class="age-val">{{ $job->age_min ? $job->age_min . ' Years' : '18 Years' }}</span>
                </div>
                <div class="age-box">
                    <span class="age-label">Maximum Age</span>
                    <span class="age-val">{{ $job->age_max ? $job->age_max . ' Years' : ($job->age_limit ? $job->age_limit : '32 Years') }}</span>
                </div>
            </div>
            <div class="age-cutoff-info">
                <strong>Age Limit Reference:</strong> Calculated based on the board's recruitment guidelines. Age relaxation is applicable extra as per government reservation rules.
            </div>
        </div>

        <!-- Recruitment Update Timeline & Lifecycle -->
        @if($timeline && $timeline->count() > 1)
            <section class="details-section" style="border-top:none; padding-top:0;">
                <h4 style="font-family:'Outfit'; color:var(--accent-color); margin-bottom:0.75rem;"><i class="fa-solid fa-code-fork"></i> Recruitment Lifecycle & Update Timeline</h4>
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
                                        <strong>{{ $itemType }}: {{ $item->title }} (This Page)</strong>
                                    @else
                                        <a href="{{ route('seo.job_detail', ['slug' => $item->slug]) }}">{{ $itemType }}: {{ $item->title }}</a>
                                    @endif
                                </h5>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <!-- Enriched AI Summary Card -->
        @if($aiContent && !empty($aiContent->summary))
            <div class="ai-summary-card">
                <div class="ai-badge">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                    AI Summary
                </div>
                <div style="font-size: 0.95rem; color: var(--text-primary); line-height: 1.7;">
                    {!! $aiContent->summary !!}
                </div>
            </div>
        @endif

        <!-- Vacancy Distribution Breakdown -->
        @if($job->categoryVacancies && $job->categoryVacancies->count() > 0)
            <section class="details-section">
                <h4 style="font-family:'Outfit'; color:var(--accent-color); margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem;">
                    Vacancy Distribution Breakdown
                </h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem;">
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
                            <div style="background: rgba(255,255,255,0.01); border: 1px solid var(--border-color); border-radius: 8px; padding: 1rem;">
                                <h5 style="font-size:0.85rem; text-transform:uppercase; color:var(--text-secondary); font-weight:700; margin-bottom:0.75rem; border-bottom: 1px solid var(--border-color); padding-bottom:0.4rem; font-family:'Outfit';">
                                    {{ $groupTitle }}
                                </h5>
                                <ul style="list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:0.5rem;">
                                    @foreach($items as $cv)
                                        <li style="display:flex; justify-content:space-between; align-items:center; font-size:0.9rem; color:var(--text-primary);">
                                            <span>{{ $cv->category_name }}</span>
                                            <span class="badge" style="background:var(--border-color); color:var(--text-primary); font-weight:bold; padding: 2px 8px; font-size:0.75rem;">
                                                {{ $cv->vacancy_count }}
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    @endforeach
                </div>
            </section>
        @endif

        <!-- Description -->
        <section class="details-section">
            <h4>Recruitment Overview & Requirements</h4>
            @if($aiContent && !empty($aiContent->eligibility))
                <div style="margin-bottom: 1.5rem;">
                    <h5 style="font-family: 'Outfit'; font-size: 1.05rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem;">Detailed Eligibility Criteria</h5>
                    <div style="font-size: 0.95rem; color: var(--text-secondary); line-height: 1.7;">
                        {!! \Illuminate\Support\Str::markdown($aiContent->eligibility) !!}
                    </div>
                </div>
            @endif
            
            <h5 style="font-family: 'Outfit'; font-size: 1.05rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem; margin-top: 1rem;">Original Announcement Overview</h5>
            <div class="original-overview-content">{!! $job->description !!}</div>
        </section>

        <!-- Exam Pattern & Syllabus -->
        @if($job->exam_pattern)
            <section class="details-section" id="exam-pattern-section">
                <h4>Official Syllabus & Exam Pattern</h4>
                <div>{!! $job->exam_pattern !!}</div>
            </section>
        @endif

        <!-- Selection Process -->
        @if($aiContent && !empty($aiContent->selection_process))
            <section class="details-section">
                <h4>Selection Process Details</h4>
                <div style="font-size: 0.95rem; color: var(--text-secondary); line-height: 1.7;">
                    {!! $aiContent->selection_process !!}
                </div>
            </section>
        @elseif($job->selection_process)
            <section class="details-section">
                <h4>Selection Process</h4>
                <div>{!! $job->selection_process !!}</div>
            </section>
        @endif

        <!-- Enriched AI FAQs -->
        @if($aiContent && !empty($aiContent->faqs) && count($aiContent->faqs) > 0)
            <section class="details-section">
                <h4>Frequently Asked Questions (FAQs)</h4>
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
            <h4>How to Fill the Form - Step-by-Step Instructions</h4>
            <ul style="list-style-type: none; padding-left: 0; display: flex; flex-direction: column; gap: 0.75rem;">
                <li style="display: flex; gap: 0.75rem; font-size: 0.95rem; color: var(--text-secondary); line-height: 1.6;">
                    <span style="color:var(--accent-color); font-weight:bold;">1.</span>
                    <span><strong>OTR Registration:</strong> Most recruitment boards (like UPSC, SSC, and State PSCs) mandate completing a One-Time Registration (OTR) profile on their official website before submitting any exam application forms.</span>
                </li>
                @if(str_contains(strtolower($job->title), 'ssc') || str_contains(strtolower($job->description), 'webcam') || str_contains(strtolower($job->description), 'live photo'))
                    <li style="display: flex; gap: 0.75rem; font-size: 0.95rem; color: var(--text-secondary); line-height: 1.6;">
                        <span style="color:var(--accent-color); font-weight:bold;">2.</span>
                        <span><strong>Webcam Live Photograph:</strong> SSC and other major commissions require taking a live photo of yourself via webcam or through the official mobile app. Stand in front of a light/white background and look straight. Do not wear caps, spectacles, or masks.</span>
                    </li>
                @endif
                <li style="display: flex; gap: 0.75rem; font-size: 0.95rem; color: var(--text-secondary); line-height: 1.6;">
                    <span style="color:var(--accent-color); font-weight:bold;">3.</span>
                    <span><strong>Check Credentials & Preview:</strong> Before clicking on final submit, review the form preview thoroughly. Confirm that spelling, dates, caste category, and certificates are accurate.</span>
                </li>
                <li style="display: flex; gap: 0.75rem; font-size: 0.95rem; color: var(--text-secondary); line-height: 1.6;">
                    <span style="color:var(--accent-color); font-weight:bold;">4.</span>
                    <span><strong>Fee Payment:</strong> Candidates requiring a fee must complete the payment (using UPI, Credit/Debit cards, or Net banking). Unpaid applications are treated as incomplete and rejected automatically.</span>
                </li>
                <li style="display: flex; gap: 0.75rem; font-size: 0.95rem; color: var(--text-secondary); line-height: 1.6;">
                    <span style="color:var(--accent-color); font-weight:bold;">5.</span>
                    <span><strong>Print Confirmation:</strong> Save and print a hard copy of the final submitted application form for future reference and exam attendance confirmation.</span>
                </li>
            </ul>
        </section>

        <!-- Official Useful Important Links -->
        <section class="details-section">
            <h4>Some Useful Important Links</h4>
            <div class="links-table-container">
                <table class="links-table">
                    <thead>
                        <tr>
                            <th>Resource / Action Name</th>
                            <th style="text-align: right;">Action Link</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($job->apply_link)
                            <tr>
                                <td><strong>Apply Online (Registration & Login)</strong></td>
                                <td style="text-align: right;">
                                    <a href="{{ $job->apply_link }}" target="_blank" rel="nofollow noopener" class="btn-link-action btn-pulse">
                                        <i class="fa-solid fa-pen-to-square"></i> Apply Online
                                    </a>
                                </td>
                            </tr>
                        @endif
                        @if($job->notification_pdf_path)
                            <tr>
                                <td><strong>Download Official Notification PDF</strong></td>
                                <td style="text-align: right;">
                                    @php
                                        $pdfUrl = str_starts_with($job->notification_pdf_path, 'http') 
                                            ? $job->notification_pdf_path 
                                            : Storage::url($job->notification_pdf_path);
                                    @endphp
                                    <a href="{{ $pdfUrl }}" target="_blank" rel="noopener" class="btn-link-action" style="background:#dc2626;">
                                        <i class="fa-solid fa-file-pdf"></i> Download PDF
                                    </a>
                                </td>
                            </tr>
                        @endif
                        @if($job->exam_pattern)
                            <tr>
                                <td><strong>Download Syllabus & Exam Pattern</strong></td>
                                <td style="text-align: right;">
                                    <a href="#exam-pattern-section" class="btn-link-action" style="background:#8b5cf6;">
                                        <i class="fa-solid fa-book-open"></i> View Syllabus
                                    </a>
                                </td>
                            </tr>
                        @endif
                        @if($job->official_website_link)
                            <tr>
                                <td><strong>Official Recruitment Website</strong></td>
                                <td style="text-align: right;">
                                    <a href="{{ $job->official_website_link }}" target="_blank" rel="nofollow noopener" class="btn-link-action" style="background:#4b5563;">
                                        <i class="fa-solid fa-globe"></i> Visit Website
                                    </a>
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <td><strong>Join Telegram Alerts Channel</strong></td>
                            <td style="text-align: right;">
                                <a href="https://t.me/sarkariresult" target="_blank" rel="noopener" class="btn-link-action" style="background:#229ED9;">
                                    <i class="fa-brands fa-telegram"></i> Telegram Channel
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Join WhatsApp Updates Channel</strong></td>
                            <td style="text-align: right;">
                                <a href="https://whatsapp.com/channel/sarkariresult" target="_blank" rel="noopener" class="btn-link-action" style="background:#25D366;">
                                    <i class="fa-brands fa-whatsapp"></i> WhatsApp Group
                                </a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Lead Capture Form / Submission -->
        <section class="apply-panel">
            <div class="apply-card">
                @auth
                    <h3 style="font-family: 'Outfit'; font-size: 1.3rem; margin-bottom: 0.5rem; color: var(--accent-color);">Submit Job Application</h3>
                    <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1.25rem;">Upload your resume to complete your application for this government posting instantly.</p>
                    
                    <form id="standaloneRecruitmentApplyForm" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="standaloneAppResume">Select CV / Resume (PDF, DOC, DOCX up to 2MB)</label>
                            <input type="file" name="resume" id="standaloneAppResume" class="form-control" required style="padding: 0.65rem 1rem;">
                            <div class="invalid-feedback" id="standaloneResumeError" style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem;"></div>
                        </div>
                        <button type="submit" class="form-btn" id="standaloneApplySubmitBtn" style="margin-top: 0.75rem;">
                            Submit Application Now
                        </button>
                    </form>
                @else
                    <div style="text-align: center; padding: 1.5rem 0;">
                        <h3 style="font-family: 'Outfit'; font-size: 1.25rem; margin-bottom: 0.5rem; color: var(--text-primary);">Apply for this Announcement</h3>
                        <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1.25rem;">Please register or login to your candidate dashboard to apply directly using your resume profile.</p>
                        <button class="form-btn trigger-auth-redirect-btn" style="width: auto; padding: 0.75rem 2rem; display: inline-block;">
                            Login / Register to Apply
                        </button>
                    </div>
                @endauth
            </div>
        </section>
    </article>

    {{-- ─── Automated Internal Linking System ────────────────────────────── --}}
    @if(!empty($internalLinks))
        @include('components.internal-linking.related-links', [
            'links' => $internalLinks,
            'currentJob' => $job,
        ])
    @endif
</div>

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
