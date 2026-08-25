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
                <span class="badge badge-dept" data-translate-lookup="{{ str_replace("\n", " | ", $job->qualification->name ?? 'Degree Required') }}">{{ str_replace("\n", " | ", $job->qualification->name ?? 'Degree Required') }}</span>
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

        
        <!-- CONTENT TYPE SWITCHER -->
        @if(in_array($job->post_type, ['job']))
            <div class="split-info-card">
                <div class="split-info-column">
                    @include('partials.seo_sections._important_dates')
                </div>
                <div class="split-info-column">
                    @include('partials.seo_sections._application_fee')
                </div>
            </div>
            @include('partials.seo_sections._age_limit')
            @include('partials.seo_sections._lifecycle')
            @include('partials.seo_sections._vacancy_breakdown')
            @include('partials.seo_sections._eligibility_overview')
            @include('partials.seo_sections._syllabus_pattern')
            @include('partials.seo_sections._selection_process')
            @include('partials.seo_sections._faqs')
            @include('partials.seo_sections._how_to_apply')
            @include('partials.seo_sections._useful_links')

        @elseif(in_array($job->post_type, ['admit_card', 'result']))
            <div class="split-info-card" style="display: block;">
                @include('partials.seo_sections._important_dates')
            </div>
            @include('partials.seo_sections._how_to_apply')
            @include('partials.seo_sections._useful_links')

        @elseif($job->post_type === 'answer_key')
            <div class="split-info-card" style="display: block;">
                @include('partials.seo_sections._important_dates')
            </div>
            @include('partials.seo_sections._how_to_apply')
            @include('partials.seo_sections._useful_links')

        @elseif($job->post_type === 'syllabus')
            <div class="split-info-card" style="display: block;">
                @include('partials.seo_sections._important_dates')
            </div>
            @include('partials.seo_sections._syllabus_pattern')
            @include('partials.seo_sections._useful_links')

        @elseif($job->post_type === 'notice')
            <div class="split-info-card" style="display: block;">
                @include('partials.seo_sections._important_dates')
            </div>
            @include('partials.seo_sections._eligibility_overview')
            @include('partials.seo_sections._useful_links')

        @elseif(in_array($job->post_type, ['admission', 'scholarship']))
            <div class="split-info-card">
                <div class="split-info-column">
                    @include('partials.seo_sections._important_dates')
                </div>
                <div class="split-info-column">
                    @include('partials.seo_sections._application_fee')
                </div>
            </div>
            @include('partials.seo_sections._eligibility_overview')
            @include('partials.seo_sections._faqs')
            @include('partials.seo_sections._how_to_apply')
            @include('partials.seo_sections._useful_links')
            
        @else
            <!-- Fallback for generic or missing post types -->
            <div class="split-info-card">
                <div class="split-info-column">
                    @include('partials.seo_sections._important_dates')
                </div>
                <div class="split-info-column">
                    @include('partials.seo_sections._application_fee')
                </div>
            </div>
            @include('partials.seo_sections._age_limit')
            @include('partials.seo_sections._vacancy_breakdown')
            @include('partials.seo_sections._eligibility_overview')
            @include('partials.seo_sections._useful_links')
        @endif
        
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
