{{--
|--------------------------------------------------------------------------
| Internal Linking: Detail Page Component
|--------------------------------------------------------------------------
|
| Renders all internal link sections on job/result/admit-card detail pages.
| Receives $links (from InternalLinkingService::getLinksForDetailPage())
| and $currentJob (the JobPost being viewed).
|
--}}

@php
    $linkingService = app(\App\Domains\Jobs\Services\InternalLinkingService::class);
    $routeMap = config('internal_linking.post_type_routes', []);
    $labelMap = config('internal_linking.post_type_labels', []);
@endphp

<style>
    /* ─── Internal Linking System Styles ────────────────────────────────── */
    .il-section {
        margin-top: 2.5rem;
        padding-top: 2rem;
        border-top: 1px solid var(--border-color);
    }
    .il-section-header {
        font-family: 'Outfit', sans-serif;
        font-size: 1.2rem;
        font-weight: 800;
        color: var(--text-primary);
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .il-section-header .il-icon {
        width: 20px;
        height: 20px;
        color: var(--accent-color);
        flex-shrink: 0;
    }
    .il-section-header .il-count {
        font-size: 0.7rem;
        background: rgba(37, 99, 235, 0.1);
        color: var(--accent-color);
        padding: 2px 8px;
        border-radius: 99px;
        font-weight: 700;
        margin-left: 0.5rem;
    }

    /* Related Jobs Grid */
    .il-jobs-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1rem;
    }
    .il-job-card {
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 1.15rem;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
        overflow: hidden;
        text-decoration: none;
        display: block;
    }
    .il-job-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 3px;
        height: 100%;
        background: var(--accent-color);
        opacity: 0;
        transition: opacity 0.3s;
    }
    .il-job-card:hover {
        border-color: var(--accent-color);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px -8px rgba(37, 99, 235, 0.15);
    }
    .il-job-card:hover::before {
        opacity: 1;
    }
    .il-job-card-title {
        font-family: 'Outfit', sans-serif;
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.4rem;
        line-height: 1.35;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .il-job-card-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        margin-bottom: 0.5rem;
    }
    .il-job-card-meta .il-tag {
        font-size: 0.68rem;
        font-weight: 600;
        padding: 2px 7px;
        border-radius: 4px;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .il-tag-dept {
        background: rgba(37, 99, 235, 0.08);
        color: #3b82f6;
    }
    .il-tag-state {
        background: rgba(139, 92, 246, 0.08);
        color: #8b5cf6;
    }
    .il-tag-deadline {
        background: rgba(239, 68, 68, 0.08);
        color: #ef4444;
    }
    .il-job-card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 0.5rem;
        padding-top: 0.5rem;
        border-top: 1px solid var(--border-color);
    }
    .il-job-card-footer .il-salary {
        font-size: 0.78rem;
        font-weight: 700;
        color: #10b981;
    }
    .il-job-card-footer .il-arrow {
        font-size: 0.75rem;
        color: var(--accent-color);
        font-weight: 700;
        transition: transform 0.2s;
    }
    .il-job-card:hover .il-arrow {
        transform: translateX(3px);
    }

    /* Cross-Type Navigation (Lifecycle) */
    .il-lifecycle-nav {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
    }
    .il-lifecycle-pill {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 0.85rem 1.15rem;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        flex: 1;
        min-width: 200px;
    }
    .il-lifecycle-pill:hover {
        border-color: var(--accent-color);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px -6px rgba(37, 99, 235, 0.12);
    }
    .il-lifecycle-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }
    .il-lifecycle-icon-result { background: rgba(16, 185, 129, 0.1); }
    .il-lifecycle-icon-admit_card { background: rgba(245, 158, 11, 0.1); }
    .il-lifecycle-icon-answer_key { background: rgba(139, 92, 246, 0.1); }
    .il-lifecycle-icon-syllabus { background: rgba(59, 130, 246, 0.1); }
    .il-lifecycle-icon-job { background: rgba(37, 99, 235, 0.1); }
    .il-lifecycle-text {
        flex: 1;
        min-width: 0;
    }
    .il-lifecycle-label {
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--text-secondary);
        font-weight: 700;
    }
    .il-lifecycle-title {
        font-family: 'Outfit', sans-serif;
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--text-primary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Category Pills */
    .il-category-scroll {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .il-category-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 99px;
        padding: 0.5rem 1rem;
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--text-primary);
        text-decoration: none;
        transition: all 0.25s;
        white-space: nowrap;
    }
    .il-category-pill:hover {
        background: var(--accent-color);
        color: #fff;
        border-color: var(--accent-color);
        transform: translateY(-1px);
    }
    .il-category-count {
        font-size: 0.68rem;
        background: rgba(255, 255, 255, 0.15);
        padding: 1px 6px;
        border-radius: 99px;
        font-weight: 700;
    }

    /* State Grid */
    .il-state-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 0.65rem;
    }
    .il-state-link {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 0.65rem 0.85rem;
        text-decoration: none;
        transition: all 0.25s;
        font-size: 0.82rem;
        color: var(--text-primary);
        font-weight: 600;
    }
    .il-state-link:hover {
        border-color: var(--accent-color);
        background: rgba(37, 99, 235, 0.04);
    }
    .il-state-count {
        font-size: 0.7rem;
        background: rgba(37, 99, 235, 0.1);
        color: var(--accent-color);
        padding: 2px 6px;
        border-radius: 4px;
        font-weight: 700;
    }

    /* Exam Updates Row (Results + Admit Cards) */
    .il-exam-row {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 0.85rem;
    }
    .il-exam-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 0.85rem 1rem;
        text-decoration: none;
        transition: all 0.25s;
    }
    .il-exam-item:hover {
        border-color: var(--accent-color);
        transform: translateY(-1px);
    }
    .il-exam-icon {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }
    .il-exam-icon-result { background: rgba(16, 185, 129, 0.1); }
    .il-exam-icon-admit { background: rgba(245, 158, 11, 0.1); }
    .il-exam-text {
        flex: 1;
        min-width: 0;
    }
    .il-exam-title {
        font-family: 'Outfit', sans-serif;
        font-size: 0.82rem;
        font-weight: 700;
        color: var(--text-primary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .il-exam-dept {
        font-size: 0.72rem;
        color: var(--text-secondary);
    }

    @media (max-width: 640px) {
        .il-jobs-grid { grid-template-columns: 1fr; }
        .il-lifecycle-nav { flex-direction: column; }
        .il-lifecycle-pill { min-width: unset; }
        .il-state-grid { grid-template-columns: repeat(2, 1fr); }
    }
</style>

{{-- ─── Cross-Type Lifecycle Navigation ─────────────────────────────────── --}}
@if(!empty($links['cross_type']))
<section class="il-section" id="il-cross-type-nav">
    <h3 class="il-section-header">
        <svg class="il-icon" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
        Related Updates for This Exam
    </h3>
    <div class="il-lifecycle-nav">
        @foreach($links['cross_type'] as $cross)
            @php
                $iconMap = [
                    'result' => '📊', 'admit_card' => '🎫', 'answer_key' => '🔑',
                    'syllabus' => '📚', 'job' => '💼',
                ];
            @endphp
            <a href="{{ $cross['url'] }}" class="il-lifecycle-pill il-tracked-link"
               data-il-source="{{ $currentJob->id }}" data-il-section="cross_type"
               data-il-anchor="{{ $cross['anchor'] }}">
                <div class="il-lifecycle-icon il-lifecycle-icon-{{ $cross['type'] }}">
                    {{ $iconMap[$cross['type']] ?? '📋' }}
                </div>
                <div class="il-lifecycle-text">
                    <div class="il-lifecycle-label">{{ $cross['label'] }}</div>
                    <div class="il-lifecycle-title">{{ $cross['anchor'] }}</div>
                </div>
            </a>
        @endforeach
    </div>
</section>
@endif

{{-- ─── Related Jobs ──────────────────────────────────────────────────── --}}
@if($links['related_jobs']->isNotEmpty())
<section class="il-section" id="il-related-jobs">
    <h3 class="il-section-header">
        <svg class="il-icon" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
        Similar Government Jobs
        <span class="il-count">{{ $links['related_jobs']->count() }}</span>
    </h3>
    <div class="il-jobs-grid">
        @foreach($links['related_jobs'] as $relJob)
            <a href="{{ $linkingService->getDetailUrl($relJob) }}" class="il-job-card il-tracked-link"
               data-il-source="{{ $currentJob->id }}" data-il-target="{{ $relJob->id }}"
               data-il-section="related_jobs" data-il-anchor="{{ $linkingService->generateAnchor($relJob) }}">
                <div class="il-job-card-title">{{ $linkingService->generateAnchor($relJob) }}</div>
                <div class="il-job-card-meta">
                    <span class="il-tag il-tag-dept">{{ $relJob->department->name ?? 'Govt' }}</span>
                    <span class="il-tag il-tag-state">📍 {{ $relJob->state->name ?? 'India' }}</span>
                    @if($relJob->last_date_to_apply)
                        <span class="il-tag il-tag-deadline">⏰ {{ $relJob->last_date_to_apply->format('d M') }}</span>
                    @endif
                </div>
                <div class="il-job-card-footer">
                    <span class="il-salary">
                        @if($relJob->salary_min > 0)
                            ₹{{ number_format($relJob->salary_min, 0) }} - {{ number_format($relJob->salary_max, 0) }}
                        @else
                            Govt Scale
                        @endif
                    </span>
                    <span class="il-arrow">View Details →</span>
                </div>
            </a>
        @endforeach
    </div>
</section>
@endif

{{-- ─── Related Results & Admit Cards (Exam Updates) ──────────────────── --}}
@if($links['related_results']->isNotEmpty() || $links['related_admit_cards']->isNotEmpty())
<section class="il-section" id="il-exam-updates">
    <h3 class="il-section-header">
        <svg class="il-icon" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        Exam Updates & Notifications
    </h3>
    <div class="il-exam-row">
        @foreach($links['related_results'] as $result)
            <a href="{{ $linkingService->getDetailUrl($result) }}" class="il-exam-item il-tracked-link"
               data-il-source="{{ $currentJob->id }}" data-il-target="{{ $result->id }}"
               data-il-section="related_results" data-il-anchor="{{ $result->title }}">
                <div class="il-exam-icon il-exam-icon-result">📊</div>
                <div class="il-exam-text">
                    <div class="il-exam-title">{{ Str::limit($result->title, 45) }}</div>
                    <div class="il-exam-dept">{{ $result->department->name ?? 'Result' }} • Result</div>
                </div>
            </a>
        @endforeach
        @foreach($links['related_admit_cards'] as $admit)
            <a href="{{ $linkingService->getDetailUrl($admit) }}" class="il-exam-item il-tracked-link"
               data-il-source="{{ $currentJob->id }}" data-il-target="{{ $admit->id }}"
               data-il-section="related_admit_cards" data-il-anchor="{{ $admit->title }}">
                <div class="il-exam-icon il-exam-icon-admit">🎫</div>
                <div class="il-exam-text">
                    <div class="il-exam-title">{{ Str::limit($admit->title, 45) }}</div>
                    <div class="il-exam-dept">{{ $admit->department->name ?? 'Admit Card' }} • Admit Card</div>
                </div>
            </a>
        @endforeach
    </div>
</section>
@endif

{{-- ─── Related Categories ────────────────────────────────────────────── --}}
@if(!empty($links['categories']))
<section class="il-section" id="il-categories">
    <h3 class="il-section-header">
        <svg class="il-icon" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
        Explore Job Categories
    </h3>
    <div class="il-category-scroll">
        @foreach($links['categories'] as $cat)
            <a href="{{ $cat['url'] }}" class="il-category-pill il-tracked-link"
               data-il-source="{{ $currentJob->id }}" data-il-section="categories"
               data-il-anchor="{{ $cat['name'] }} Jobs">
                💼 {{ $cat['name'] }}
                @if($cat['count'] > 0)
                    <span class="il-category-count">{{ $cat['count'] }}</span>
                @endif
            </a>
        @endforeach
    </div>
</section>
@endif

{{-- ─── State Recommendations ─────────────────────────────────────────── --}}
@if($links['state_recommendations']->isNotEmpty())
<section class="il-section" id="il-states">
    <h3 class="il-section-header">
        <svg class="il-icon" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
        Jobs by State
    </h3>
    <div class="il-state-grid">
        @foreach($links['state_recommendations'] as $state)
            <a href="{{ $state['url'] }}" class="il-state-link il-tracked-link"
               data-il-source="{{ $currentJob->id }}" data-il-section="state_reco"
               data-il-anchor="{{ $state['name'] }} Jobs">
                📍 {{ $state['name'] }}
                <span class="il-state-count">{{ $state['count'] }}</span>
            </a>
        @endforeach
    </div>
</section>
@endif

{{-- ─── Click Tracking Script ─────────────────────────────────────────── --}}
<script>
    $(document).on('click', '.il-tracked-link', function() {
        var $el = $(this);
        $.ajax({
            url: '{{ route("internal_link.track") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                source_id: $el.data('il-source'),
                target_id: $el.data('il-target') || null,
                target_url: $el.attr('href'),
                section: $el.data('il-section'),
                anchor: $el.data('il-anchor')
            }
        });
        // Don't prevent default — let the navigation proceed
    });
</script>
