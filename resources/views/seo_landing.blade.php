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

    /* SEO Landing Header banner */
    .seo-hero {
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.12) 0%, rgba(16, 185, 129, 0.08) 100%);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 2.5rem 2rem;
        margin-bottom: 2.5rem;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        position: relative;
        overflow: hidden;
    }
    .seo-hero::before {
        content: '';
        position: absolute;
        top: -20%;
        right: -10%;
        width: 350px;
        height: 350px;
        background: radial-gradient(circle, rgba(37, 99, 235, 0.1) 0%, transparent 70%);
        pointer-events: none;
    }
    .seo-hero h1 {
        font-family: 'Outfit', sans-serif;
        font-size: 2.2rem;
        font-weight: 800;
        margin-bottom: 0.75rem;
        line-height: 1.25;
        background: linear-gradient(135deg, var(--text-primary) 30%, var(--accent-color) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .seo-hero p {
        font-size: 1rem;
        color: var(--text-secondary);
        max-width: 800px;
        line-height: 1.6;
    }

    /* Elegant Job list table */
    .seo-table-panel {
        background: rgba(255, 255, 255, 0.01);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 1.5rem;
        box-shadow: var(--card-shadow);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        margin-bottom: 3rem;
    }
    .seo-table-title {
        font-family: 'Outfit', sans-serif;
        font-size: 1.3rem;
        font-weight: 700;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .job-row-item {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1fr;
        gap: 1rem;
        padding: 1rem 0.75rem;
        border-bottom: 1px solid var(--border-color);
        align-items: center;
        transition: background 0.2s;
    }
    .job-row-item:last-child {
        border-bottom: none;
    }
    .job-row-item:hover {
        background: rgba(255, 255, 255, 0.02);
    }
    .job-title-col h3 {
        font-size: 0.95rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
        color: var(--text-primary);
    }
    .job-title-col p {
        font-size: 0.8rem;
        color: var(--text-secondary);
    }
    .job-meta-col {
        font-size: 0.85rem;
        color: var(--text-primary);
        font-weight: 500;
    }
    .job-deadline-col {
        font-size: 0.82rem;
        color: #ef4444;
        font-weight: 600;
    }

    /* Real-Time Telemetry Dashboard styling */
    .funnel-dashboard-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 3rem;
    }
    @media (max-width: 768px) {
        .funnel-dashboard-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .job-row-item {
            grid-template-columns: 1fr;
            gap: 0.5rem;
            padding: 1rem 0;
        }
    }
    .funnel-stat-card {
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.25rem;
        text-align: center;
        box-shadow: var(--card-shadow);
        position: relative;
        overflow: hidden;
    }
    .funnel-stat-card::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background: var(--stat-color, var(--accent-color));
        opacity: 0.8;
    }
    .funnel-stat-val {
        font-family: 'Outfit', sans-serif;
        font-size: 1.8rem;
        font-weight: 800;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
    }
    .funnel-stat-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-secondary);
        font-weight: 700;
    }

    /* Premium Growth Widget Layouts */
    .growth-sidebar-card {
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: var(--card-shadow);
        position: relative;
    }
    .growth-card-header {
        font-family: 'Outfit', sans-serif;
        font-size: 1.15rem;
        font-weight: 800;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .growth-input-group {
        display: flex;
        gap: 0.5rem;
        margin-top: 1rem;
    }
    .growth-input {
        flex: 1;
        background: var(--bg-primary);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 0.6rem 0.75rem;
        color: var(--text-primary);
        font-size: 0.85rem;
        outline: none;
        transition: border-color 0.2s;
    }
    .growth-input:focus {
        border-color: var(--accent-color);
    }
    .growth-btn {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        color: white;
        border: none;
        border-radius: 8px;
        padding: 0.6rem 1.25rem;
        font-weight: 700;
        font-size: 0.85rem;
        cursor: pointer;
        transition: transform 0.2s, opacity 0.2s;
    }
    .growth-btn:hover {
        transform: translateY(-1px);
        opacity: 0.95;
    }

    /* Animated Telegram CTA Banner */
    .telegram-cta-banner {
        background: linear-gradient(135deg, #0088cc 0%, #006699 100%);
        color: white;
        border-radius: 14px;
        padding: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 10px 25px -5px rgba(0, 136, 204, 0.4);
        margin-bottom: 2.5rem;
        position: relative;
        overflow: hidden;
    }
    .telegram-cta-banner::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -30%;
        width: 250px;
        height: 250px;
        background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 60%);
        pointer-events: none;
    }
    .telegram-content h3 {
        font-family: 'Outfit', sans-serif;
        font-size: 1.25rem;
        font-weight: 800;
        margin-bottom: 0.25rem;
    }
    .telegram-content p {
        font-size: 0.85rem;
        opacity: 0.9;
    }
    .telegram-btn {
        background: white;
        color: #0088cc;
        border: none;
        border-radius: 8px;
        padding: 0.6rem 1.5rem;
        font-weight: 800;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        text-decoration: none;
        display: inline-block;
    }
    .telegram-btn:hover {
        transform: translateY(-2px) scale(1.03);
        box-shadow: 0 6px 15px rgba(0,0,0,0.15);
        background: rgba(255, 255, 255, 0.95);
    }
</style>

<div style="max-width: 1400px; margin: 0 auto; padding: 0 5%;">
    <!-- Breadcrumbs -->
    <div class="breadcrumb-trail">
        <a href="/" data-translate-lookup="Home">Home</a>
        <span class="breadcrumb-separator">&raquo;</span>
        <span data-translate-lookup="{{ $breadcrumb }}">{{ $breadcrumb }}</span>
    </div>

    <!-- SEO Banner -->
    <section class="seo-hero">
        <h1 data-translate-title="{{ $pageHeader }}">{{ $pageHeader }}</h1>
        <p>{{ $metaDescription }}</p>
    </section>

    <!-- Real-Time Telemetry Dashboard -->
    <h2 style="font-size: 1.3rem; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; font-family: 'Outfit';">
        <span style="display:inline-block; width:8px; height:18px; background:var(--accent-color); border-radius:3px;"></span>
        <span data-i18n="conversion_telemetry_title">Real-Time Conversion Telemetry</span>
    </h2>
    <div class="funnel-dashboard-grid">
        <div class="funnel-stat-card" style="--stat-color: #3b82f6;">
            <div class="funnel-stat-val" id="telemetryViews">{{ number_format($funnel['views']) }}</div>
            <div class="funnel-stat-label" data-i18n="daily_page_visitors">Daily Page Visitors</div>
        </div>
        <div class="funnel-stat-card" style="--stat-color: #10b981;">
            <div class="funnel-stat-val" id="telemetryAlerts">{{ number_format($funnel['subscribers']) }}</div>
            <div class="funnel-stat-label" data-i18n="lead_subscribers">Lead Subscribers</div>
        </div>
        <div class="funnel-stat-card" style="--stat-color: #8b5cf6;">
            <div class="funnel-stat-val">{{ number_format($funnel['applies']) }}</div>
            <div class="funnel-stat-label" data-i18n="job_applicants">Job Applicants</div>
        </div>
        <div class="funnel-stat-card" style="--stat-color: #f59e0b;">
            <div class="funnel-stat-val">{{ $funnel['conversion_rate'] }}%</div>
            <div class="funnel-stat-label" data-i18n="conversion_efficiency">Conversion Efficiency</div>
        </div>
    </div>

    <!-- Split Column Layout -->
    <div style="display: grid; grid-template-columns: 2.2fr 1fr; gap: 2rem; align-items: start; margin-bottom: 3rem;">
        <!-- Left Column: Dynamic crawlable list of jobs -->
        <div>
            <div class="seo-table-panel">
                <div class="seo-table-title" style="color: var(--accent-color);">
                    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    <span data-i18n="live_opportunities_title">Live Active Opportunities</span> ({{ $jobs->count() }})
                </div>

                @forelse($jobs as $job)
                    @php
                        $detailRoute = match($job->post_type) {
                            'result' => route('seo.result_detail', ['slug' => $job->slug]),
                            'admit_card' => route('seo.admit_card_detail', ['slug' => $job->slug]),
                            'answer_key' => route('seo.answer_key_detail', ['slug' => $job->slug]),
                            'syllabus' => route('seo.syllabus_detail', ['slug' => $job->slug]),
                            default => route('seo.job_detail', ['slug' => $job->slug]),
                        };
                    @endphp
                    <div class="job-row-item">
                        <div class="job-title-col">
                            <h3 data-translate-title="{{ $job->title }}">{{ $job->title }}</h3>
                            <p>
                                <span data-translate-lookup="{{ $job->department->name ?? 'Government Ministry' }}">{{ $job->department->name ?? 'Government Ministry' }}</span> &bull; 
                                <span data-translate-lookup="{{ $job->state->name ?? 'Pan India' }}">{{ $job->state->name ?? 'Pan India' }}</span>
                            </p>
                        </div>
                        <div class="job-meta-col">
                            @if($job->salary_min > 0)
                                ₹ {{ number_format($job->salary_min, 0) }} - {{ number_format($job->salary_max, 0) }}
                            @else
                                <span data-translate-lookup="Govt Scales">Govt Scales</span>
                            @endif
                        </div>
                        <div class="job-deadline-col">
                            @if($job->last_date_to_apply)
                                <span data-translate-lookup="Till">Till</span> {{ $job->last_date_to_apply->format('d M Y') }}
                            @else
                                <span data-translate-lookup="Announced Soon">Announced Soon</span>
                            @endif
                        </div>
                        <div style="text-align: right;">
                            <a href="{{ $detailRoute }}" class="growth-btn seo-apply-trigger" data-slug="{{ $job->slug }}" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; text-decoration: none; display: inline-block;">
                                <span data-i18n="btn_view">Details</span> &raquo;
                            </a>
                        </div>
                    </div>
                @empty
                    <div style="padding: 3rem; text-align: center; color: var(--text-secondary); font-size: 0.9rem;">
                        <span data-i18n="no_active_recruitments_alerts">No state recruitments are currently active. Use the alerts form to register for instant releases!</span>
                    </div>
                @endforelse
            </div>

            <!-- Telegram Channel Call-To-Action (Tasks 7 & 9) -->
            <div class="telegram-cta-banner">
                <div class="telegram-content">
                    <h3 data-i18n="telegram_cta_title">📢 Joint Telegram Alert Network</h3>
                    <p data-i18n="telegram_cta_desc">Get real-time push feeds of government postings. Join 150K+ candidates now!</p>
                </div>
                <div>
                    <a href="https://t.me/gov_job_alerts_mock" class="telegram-btn" id="telegramAlertJoinBtn" target="_blank">
                        <span data-i18n="telegram_cta_btn">Join Channel &raquo;</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Right Column: Conversion Capture Widgets -->
        <div>
            <!-- Email Alert subscription capture (Task 5 & 9) -->
            <div class="growth-sidebar-card" style="border-top: 4px solid #10b981;">
                <div class="growth-card-header" style="color: #10b981;" data-i18n="email_alerts_title">
                    📧 Email Job Alerts
                </div>
                <p style="font-size: 0.82rem; color: var(--text-secondary); line-height: 1.5;" data-i18n="email_alerts_desc_sidebar">
                    Receive instant notifications directly to your email whenever new vacancies are published in this segment.
                </p>
                <form id="growthSubscribeForm">
                    @csrf
                    <input type="hidden" name="category_name" value="{{ $categoryName }}">
                    <div class="growth-input-group">
                        <input type="email" name="email" id="subscriberEmail" class="growth-input" placeholder="candidate@example.com" data-i18n="email_placeholder_lbl" required>
                        <button type="submit" class="growth-btn" id="subscriberSubmitBtn" style="background: #10b981;">
                            <span data-i18n="btn_activate">Activate</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Interactive Web Browser Push popup (Task 6 & 9) -->
            <div class="growth-sidebar-card" id="pushNotificationsCard" style="border-top: 4px solid #3b82f6;">
                <div class="growth-card-header" style="color: #3b82f6;" data-i18n="push_alerts_title">
                    🔔 Desktop Push Alerts
                </div>
                <p style="font-size: 0.82rem; color: var(--text-secondary); line-height: 1.5;" id="pushCardBody" data-i18n="push_alerts_desc_sidebar">
                    Authorize real-time push notifications inside your browser window to bypass email delay filters.
                </p>
                <button class="growth-btn" id="browserPushTriggerBtn" style="background: #3b82f6; width: 100%; margin-top: 1rem;">
                    <span data-i18n="btn_enable_push">Enable Instant Alerts</span>
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('schema')
<!-- BreadcrumbList Schema -->
<script type="application/ld+json">
{!! json_encode($seoService->getSchemaService()->getBreadcrumbListSchema([$breadcrumb => request()->url()]), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
</script>

<!-- ItemList Schema -->
@php
  $itemListSchema = [
      '@context' => 'https://schema.org',
      '@type' => 'ItemList',
      'numberOfItems' => $jobs->count(),
      'itemListElement' => $jobs->map(fn($job, $index) => [
          '@type' => 'ListItem',
          'position' => $index + 1,
          'url' => route('seo.job_detail', ['slug' => $job->slug]),
          'name' => $job->title
      ])->toArray()
  ];
@endphp
<script type="application/ld+json">
{!! json_encode($itemListSchema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
</script>

<!-- JobPostings Schema -->
@foreach($jobs as $job)
<script type="application/ld+json">
{!! json_encode($seoService->getSchemaService()->getJobPostingSchema($job), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
</script>
@endforeach
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        const pagePath = window.location.pathname;

        // AJAX Job alerts subscription handler (Task 5)
        $('#growthSubscribeForm').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#subscriberSubmitBtn');
            const emailInput = $('#subscriberEmail');
            
            btn.prop('disabled', true).text('Saving...');

            $.ajax({
                url: '{{ route("growth.subscribe") }}',
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    showToast(res.message, 'success');
                    
                    // Increment the telemetry alert count card in real-time! (Task 8 & 9)
                    const telemetryVal = $('#telemetryAlerts');
                    const currentCount = parseInt(telemetryVal.text().replace(/,/g, ''));
                    telemetryVal.text((currentCount + 1).toLocaleString());

                    // Transition subscription form to a gorgeous success checkmark card!
                    $('#growthSubscribeForm').html(`
                        <div style="text-align: center; padding: 1rem 0; color: #10b981;">
                            <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="margin-bottom: 0.5rem;"><path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138z"></path></svg>
                            <div style="font-weight: 700; font-size: 0.9rem;">Job Alerts Active!</div>
                        </div>
                    `);
                },
                error: function() {
                    btn.prop('disabled', false).text('Activate');
                    showToast('Subscription alert dispatch failed. Please retry.', 'error');
                }
            });
        });

        // Browser push alert subscription flow (Task 6)
        $('#browserPushTriggerBtn').on('click', function(e) {
            e.preventDefault();
            const btn = $(this);
            btn.prop('disabled', true).text('Requesting Browser Handshake...');

            // Micro-animation timer simulating desktop authorization prompt
            setTimeout(() => {
                const answer = confirm('GovJobs wishes to deliver instant alerts regarding newly scraped government listings on your desktop. Allow push alerts?');
                
                if (answer) {
                    showToast('Browser Push notifications successfully synchronized!', 'success');
                    
                    // Update stats telemetry card
                    const telemetryVal = $('#telemetryAlerts');
                    const currentCount = parseInt(telemetryVal.text().replace(/,/g, ''));
                    telemetryVal.text((currentCount + 1).toLocaleString());

                    // Transition to authorized state
                    $('#pushNotificationsCard').html(`
                        <div style="text-align: center; padding: 1rem 0; color: #3b82f6;">
                            <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="margin-bottom: 0.5rem;"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v8M8 12h8"></path></svg>
                            <div style="font-weight: 700; font-size: 0.9rem;">Desktop Handshake Linked</div>
                        </div>
                    `);
                } else {
                    btn.prop('disabled', false).text('Enable Instant Alerts');
                    showToast('Notification permission denied.', 'warning');
                }
            }, 800);
        });

        // Track Telegram join conversions (Task 7)
        $('#telegramAlertJoinBtn').on('click', function() {
            $.ajax({
                url: '{{ route("growth.track") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    event_type: 'subscribe',
                    page_path: pagePath + '/telegram'
                }
            });
        });

        // Track Apply clicks for funnel metrics (Task 8 & 9)
        $(document).on('click', '.seo-apply-trigger', function() {
            const slug = $(this).data('slug');
            $.ajax({
                url: '{{ route("growth.track") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    event_type: 'apply_click',
                    page_path: pagePath
                }
            });
        });
    });
</script>
@endsection
