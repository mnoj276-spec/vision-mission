@inject('seoService', 'App\Domains\Jobs\Services\SeoService')
@extends('layouts.app')

@section('title', $pageTitle)

@section('content')
<style>
    /* Advanced Search Dashboard Style Tokens */
    .search-dashboard-container {
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 2rem;
        align-items: start;
        margin-top: 1.5rem;
    }

    @media (max-width: 992px) {
        .search-dashboard-container {
            grid-template-columns: 1fr;
        }
    }

    /* Glassmorphic Search Header Panel */
    .search-bar-glass-panel {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.02) 0%, rgba(255, 255, 255, 0.01) 100%);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        position: relative;
        z-index: 100;
        box-shadow: var(--card-shadow);
    }

    .search-input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: var(--bg-primary);
        border: 2px solid var(--border-color);
        border-radius: 10px;
        padding: 0.25rem 0.5rem;
        transition: all 0.25s ease;
    }

    .search-input-wrapper:focus-within {
        border-color: var(--accent-color);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }

    .search-input-wrapper input {
        flex: 1;
        border: none;
        background: transparent;
        padding: 0.75rem 0.5rem;
        color: var(--text-primary);
        font-size: 1.05rem;
        font-weight: 500;
        outline: none;
    }

    .search-input-wrapper svg {
        color: var(--text-secondary);
        margin-left: 0.5rem;
    }

    /* Typo Correction Banner */
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
        transition: color 0.2s;
    }

    .typo-banner a:hover {
        color: var(--accent-color);
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
        max-height: 480px;
        overflow-y: auto;
        z-index: 1050;
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

    /* Glassmorphic Sidebar Filter Box */
    .filter-sidebar-panel {
        background: rgba(255, 255, 255, 0.01);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 1.5rem;
        box-shadow: var(--card-shadow);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }

    .filter-group {
        margin-bottom: 1.5rem;
    }

    .filter-group:last-child {
        margin-bottom: 0;
    }

    .filter-label {
        font-family: 'Outfit', sans-serif;
        font-size: 0.88rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-secondary);
        margin-bottom: 0.6rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .filter-select {
        width: 100%;
        background-color: var(--bg-primary);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 0.6rem 2.5rem 0.6rem 1rem;
        color: var(--text-primary);
        font-size: 0.88rem;
        outline: none;
        transition: border-color 0.2s;
    }

    .filter-input {
        width: 100%;
        background: var(--bg-primary);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 0.6rem 0.75rem;
        color: var(--text-primary);
        font-size: 0.88rem;
        outline: none;
        transition: border-color 0.2s;
    }

    .filter-select:focus, .filter-input:focus {
        border-color: var(--accent-color);
    }

    .filter-checkbox-wrapper {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        font-size: 0.88rem;
        color: var(--text-secondary);
    }

    .filter-checkbox-wrapper input {
        width: 16px;
        height: 16px;
        cursor: pointer;
    }

    .reset-filters-btn {
        width: 100%;
        background: rgba(239, 68, 68, 0.08);
        border: 1px solid rgba(239, 68, 68, 0.15);
        color: #ef4444;
        border-radius: 8px;
        padding: 0.6rem;
        font-weight: 700;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.2s;
        text-align: center;
    }

    .reset-filters-btn:hover {
        background: #ef4444;
        color: white;
    }

    /* Skeleton Feed Placeholders */
    .skeleton-search-item {
        background: linear-gradient(90deg, var(--bg-secondary) 25%, rgba(255,255,255,0.03) 50%, var(--bg-secondary) 75%);
        background-size: 200% 100%;
        animation: loading-shimmer 1.5s infinite;
        height: 110px;
        border-radius: 12px;
        border: 1px solid var(--border-color);
        margin-bottom: 1rem;
    }

    @keyframes loading-shimmer {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    /* Clean Breadcrumbs */
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
    }
    .breadcrumb-trail a:hover {
        text-decoration: underline;
    }

    @keyframes slideDown {
        from { transform: translateY(-10px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
</style>

<div style="max-width: 1400px; margin: 0 auto; padding: 0 5%;">
    <!-- Breadcrumbs -->
    <div class="breadcrumb-trail">
        <a href="/">Home</a>
        <span class="breadcrumb-separator">&raquo;</span>
        @if(count($breadcrumbs) > 1)
            @php $keys = array_keys($breadcrumbs); $lastLabel = end($keys); @endphp
            @foreach($breadcrumbs as $label => $url)
                @if($url)
                    <a href="{{ $url }}">{{ $label }}</a>
                    <span class="breadcrumb-separator">&raquo;</span>
                @else
                    <span>{{ $label }}</span>
                @endif
            @endforeach
        @else
            <span>Search</span>
        @endif
    </div>

    <!-- Stunning Search Panel Header -->
    <div class="search-bar-glass-panel">
        <div class="search-input-wrapper">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            <input type="text" id="searchKeywords" data-i18n="search_placeholder" placeholder="Search government postings (e.g. UPSC, RBI Grade B, Banking)..." value="{{ $activeFilters['search'] ?? '' }}" autocomplete="off">
            @if(!empty($activeFilters['search']))
                <button id="clearSearchBtn" style="background:none; border:none; color:var(--text-secondary); cursor:pointer; font-size:1.2rem; padding:0 0.5rem;">&times;</button>
            @endif
        </div>
        
        <!-- Dropdown Suggestions Container -->
        <div class="autocomplete-dropdown" id="autocompleteDropdown"></div>
    </div>

    <!-- Spellcheck Did You Mean Banner -->
    <div id="typoCorrectionBanner" style="{{ empty($typoSuggestion) ? 'display: none;' : '' }}">
        @if(!empty($typoSuggestion))
            <div class="typo-banner">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span><span data-i18n="did_you_mean">Did you mean:</span> <a id="suggestedQueryLink" data-query="{{ $typoSuggestion }}">{{ $typoSuggestion }}</a> ?</span>
            </div>
        @endif
    </div>

    <!-- Active Search Filter Dashboard Grid -->
    <div class="search-dashboard-container">
        
        <!-- Left Sidebar: Filter Form (Desktop-sticky helper) -->
        <div style="position: sticky; top: 100px;">
            <div class="filter-sidebar-panel">
                <h3 style="font-family:'Outfit'; font-size:1.15rem; margin-bottom:1.25rem; display:flex; align-items:center; gap:0.5rem;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    <span data-i18n="filter_title">Filter Listings</span>
                </h3>

                <!-- 1. State Filter -->
                <div class="filter-group">
                    <label class="filter-label" data-i18n="filter_state">📍 State / Region</label>
                    <select class="filter-select" id="stateSelectFilter">
                        <option value="" data-i18n="all_regions">All Regions</option>
                        @foreach($states as $state)
                            <option value="{{ $state->id }}" data-translate-lookup="{{ $state->name }}" {{ (isset($activeFilters['state_id']) && $activeFilters['state_id'] == $state->id) || (isset($activeFilters['state_slug']) && $activeFilters['state_slug'] == $state->slug) ? 'selected' : '' }}>{{ $state->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- 2. Category Filter -->
                <div class="filter-group">
                    <label class="filter-label" data-i18n="filter_stream">💼 Stream / Sector</label>
                    <select class="filter-select" id="categorySelectFilter">
                        <option value="" data-i18n="all_streams">All Streams</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" data-translate-lookup="{{ $cat->name }}" {{ (isset($activeFilters['category_id']) && $activeFilters['category_id'] == $cat->id) || (isset($activeFilters['category_slug']) && $activeFilters['category_slug'] == $cat->slug) ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- 3. Qualification Filter -->
                <div class="filter-group">
                    <label class="filter-label" data-i18n="filter_degree">🎓 Candidate Degree</label>
                    <select class="filter-select" id="qualSelectFilter">
                        <option value="" data-i18n="all_degrees">All Degrees</option>
                        @foreach($qualifications as $qual)
                            <option value="{{ $qual->id }}" data-translate-lookup="{{ $qual->name }}" {{ (isset($activeFilters['qualification_id']) && $activeFilters['qualification_id'] == $qual->id) || (isset($activeFilters['qualification_slug']) && $activeFilters['qualification_slug'] == $qual->slug) ? 'selected' : '' }}>{{ $qual->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- 4. Organization/Department Filter -->
                <div class="filter-group">
                    <label class="filter-label" data-i18n="filter_board">🏢 Recruitment Board</label>
                    <select class="filter-select" id="deptSelectFilter">
                        <option value="" data-i18n="all_boards">All Boards</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" data-translate-lookup="{{ $dept->name }}" data-translate-suffix=" ({{ $dept->code }})" {{ (isset($activeFilters['department_id']) && $activeFilters['department_id'] == $dept->id) || (isset($activeFilters['department_slug']) && $activeFilters['department_slug'] == $dept->slug) ? 'selected' : '' }}>{{ $dept->name }} ({{ $dept->code }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- 5. Salary Min Filter -->
                <div class="filter-group">
                    <label class="filter-label" data-i18n="filter_salary">💸 Minimum Salary</label>
                    <select class="filter-select" id="salarySelectFilter">
                        <option value="" data-i18n="any_salary_scale">Any Salary Scale</option>
                        <option value="20000" {{ isset($activeFilters['min_salary']) && $activeFilters['min_salary'] == 20000 ? 'selected' : '' }}>₹ 20,000+ / mo</option>
                        <option value="40000" {{ isset($activeFilters['min_salary']) && $activeFilters['min_salary'] == 40000 ? 'selected' : '' }}>₹ 40,000+ / mo</option>
                        <option value="60000" {{ isset($activeFilters['min_salary']) && $activeFilters['min_salary'] == 60000 ? 'selected' : '' }}>₹ 60,000+ / mo</option>
                        <option value="80000" {{ isset($activeFilters['min_salary']) && $activeFilters['min_salary'] == 80000 ? 'selected' : '' }}>₹ 80,000+ / mo</option>
                    </select>
                </div>

                <!-- 6. Application Fee Check -->
                <div class="filter-group">
                    <label class="filter-checkbox-wrapper">
                        <input type="checkbox" id="noFeeCheckFilter" {{ isset($activeFilters['has_no_fee']) && filter_var($activeFilters['has_no_fee'], FILTER_VALIDATE_BOOLEAN) ? 'checked' : '' }}>
                        <span data-i18n="filter_free_app">Free Applications Only (₹0 fee)</span>
                    </label>
                </div>

                <!-- Reset Btn -->
                <div class="filter-group" style="margin-top:2rem;">
                    <button class="reset-filters-btn" id="resetFiltersTrigger" data-i18n="btn_reset">Reset Parameters</button>
                </div>
            </div>
        </div>

        <!-- Right Side: Live Paginated Feed -->
        <div>
            <!-- Live Results Stats Panel -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem;">
                <h2 style="font-family:'Outfit'; font-size:1.4rem; display:flex; align-items:center; gap:0.5rem;">
                    <span style="display:inline-block; width:8px; height:20px; background:var(--accent-color); border-radius:4px;"></span>
                    <span id="jobsCountFeedback" data-translate-key="found_jobs" data-translate-count="{{ $jobs->total() }}">Found {{ $jobs->total() }} recruitments</span>
                </h2>
                <div style="font-size:0.85rem; color:var(--text-secondary); background:var(--bg-secondary); border:1px solid var(--border-color); padding:0.4rem 0.8rem; border-radius:6px;" data-i18n="sort_featured">
                    Sort: Featured First &bull; Fresh
                </div>
            </div>

            <!-- Skeletal Loader -->
            <div id="skeletonLoader" style="display: none;">
                <div class="skeleton-search-item"></div>
                <div class="skeleton-search-item"></div>
                <div class="skeleton-search-item"></div>
                <div class="skeleton-search-item"></div>
            </div>

            <!-- Job Cards Container -->
            <div id="jobsListContainer">
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
                    <div class="glass-panel job-card" style="{{ $job->is_featured ? 'border-left: 4px solid var(--accent-color);' : '' }}">
                        <div class="job-info">
                            <h3 style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;" title="{{ $job->title }}">
                                <span class="notranslate" translate="no" data-translate-title="{{ $job->title }}" style="display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; text-overflow:ellipsis; white-space:normal; word-break:break-word; line-height:1.3;">{{ $job->title }}</span>
                                @if($job->is_featured)
                                    <span class="badge" style="background:var(--accent-color); color:#fff; font-size:0.7rem; padding:0.15rem 0.4rem; flex-shrink:0;" data-i18n="badge_featured">FEATURED</span>
                                @endif
                            </h3>
                            <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.6rem;">
                                <span data-translate-lookup="{{ $job->department->name ?? 'Government Board' }}">{{ $job->department->name ?? 'Government Board' }}</span> &bull; <span data-translate-lookup="{{ $job->state->name ?? 'Pan India' }}">{{ $job->state->name ?? 'Pan India' }}</span>
                                @if($job->district) &bull; <span>{{ $job->district->name }}</span> @endif
                            </p>
                            <div class="job-tags">
                                <span class="badge badge-dept" data-translate-lookup="{{ $job->qualification->name ?? 'Eligibility Required' }}">{{ $job->qualification->name ?? 'Eligibility Required' }}</span>
                                <span class="badge" style="background: rgba(16, 185, 129, 0.08); color: #10b981; font-weight:700;" data-translate-key="vacancies_count" data-translate-prefix="" data-translate-suffix=": {{ $job->vacancy_count }}">Vacancies: {{ $job->vacancy_count }}</span>
                                <span class="badge" style="background: rgba(139, 92, 246, 0.08); color: #8b5cf6; font-weight:700;">
                                    @if($job->salary_min > 0)
                                        ₹{{ number_format($job->salary_min, 0) }} - ₹{{ number_format($job->salary_max, 0) }}
                                    @else
                                        <span data-i18n="govt_scale">Govt Scale</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 0.5rem; align-items: flex-end; justify-content: space-between;">
                            <a href="{{ $detailRoute }}" class="btn-view" style="text-decoration:none;" data-i18n="btn_view_details">View Details</a>
                            <span class="badge badge-deadline" style="margin-top:0.5rem;" data-translate-key="apply_by" data-translate-suffix=": {{ $job->last_date_to_apply ? $job->last_date_to_apply->format('d M Y') : 'N/A' }}">Deadline: {{ $job->last_date_to_apply ? $job->last_date_to_apply->format('d M Y') : 'N/A' }}</span>
                        </div>
                    </div>
                @empty
                    <div class="glass-panel" style="padding: 4rem; text-align: center; color: var(--text-secondary); border-style:dashed;">
                        <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color:var(--text-secondary); margin-bottom:1rem; opacity:0.6;"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <h3 style="font-family:'Outfit'; color:var(--text-primary); margin-bottom:0.5rem;">No matching recruitments found</h3>
                        <p style="font-size:0.9rem;">We couldn't locate any active postings matching your search filters. Try widening your criteria or resetting filters.</p>
                    </div>
                @endforelse
            </div>

            <!-- Dynamic AJAX Pagination Container -->
            <div class="pagination-container" id="paginationContainer">
                @if($jobs->lastPage() > 1)
                    @if($jobs->currentPage() > 1)
                        <a href="#" class="page-link" data-page="{{ $jobs->currentPage() - 1 }}">&laquo; Prev</a>
                    @endif
                    @for($i = 1; $i <= $jobs->lastPage(); $i++)
                        <a href="#" class="page-link {{ $i === $jobs->currentPage() ? 'active' : '' }}" data-page="{{ $i }}">{{ $i }}</a>
                    @endfor
                    @if($jobs->currentPage() < $jobs->lastPage())
                        <a href="#" class="page-link" data-page="{{ $jobs->currentPage() + 1 }}">Next &raquo;</a>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@section('schema')
<!-- BreadcrumbList Schema -->
<script type="application/ld+json">
{!! json_encode($seoService->getSchemaService()->getBreadcrumbListSchema($breadcrumbs), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
</script>

<!-- ItemList Schema -->
@php
  $itemListSchema = [
      '@context' => 'https://schema.org',
      '@type' => 'ItemList',
      'numberOfItems' => $jobs->count(),
      'itemListElement' => collect($jobs->items())->map(fn($job, $index) => [
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
        let currentPage = 1;
        let autocompleteTimeout = null;

        // Perform AJAX search fetching
        function fetchSearchResults(page = 1) {
            currentPage = page;
            const filters = {
                search: $('#searchKeywords').val(),
                state_id: $('#stateSelectFilter').val(),
                category_id: $('#categorySelectFilter').val(),
                qualification_id: $('#qualSelectFilter').val(),
                department_id: $('#deptSelectFilter').val(),
                min_salary: $('#salarySelectFilter').val(),
                has_no_fee: $('#noFeeCheckFilter').is(':checked'),
                page: page
            };

            // Toggle loading indicators
            $('#jobsListContainer').hide();
            $('#paginationContainer').empty();
            $('#skeletonLoader').show();

            $.ajax({
                url: '/search',
                type: 'GET',
                data: filters,
                dataType: 'json',
                success: function(response) {
                    $('#skeletonLoader').hide();
                    
                    if (response.status === 'success') {
                        const data = response.data;
                        const jobs = data.jobs;

                        // 1. Update total count text
                        $('#jobsCountFeedback').text(window.t('found_jobs', `Found ${data.total} recruitments`).replace('{count}', data.total))
                                               .attr('data-translate-count', data.total);

                        // 2. Render spell suggestion did-you-mean banner
                        const typoBanner = $('#typoCorrectionBanner');
                        if (data.typo_suggestion) {
                            typoBanner.html(`
                                <div class="typo-banner">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                    <span><span data-i18n="did_you_mean">${window.t('did_you_mean', 'Did you mean:')}</span> <a class="suggested-query" data-query="${data.typo_suggestion}">${data.typo_suggestion}</a> ?</span>
                                </div>
                            `).fadeIn();
                        } else {
                            typoBanner.hide();
                        }

                        // 3. Render Job cards list
                        if (jobs.length === 0) {
                            $('#jobsListContainer').html(`
                                <div class="glass-panel" style="padding: 4rem; text-align: center; color: var(--text-secondary); border-style:dashed;">
                                    <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color:var(--text-secondary); margin-bottom:1rem; opacity:0.6;"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                    <h3 style="font-family:'Outfit'; color:var(--text-primary); margin-bottom:0.5rem;" data-i18n="no_matching_jobs">No matching recruitments found</h3>
                                    <p style="font-size:0.9rem;" data-i18n="no_matching_jobs_desc">We couldn't locate any active postings matching your search filters. Try widening your criteria or resetting filters.</p>
                                </div>
                            `).fadeIn();
                            return;
                        }

                        let html = '';
                        jobs.forEach(function(job) {
                            const isFeaturedBadge = job.is_featured ? `<span class="badge" style="background:var(--accent-color); color:#fff; font-size:0.7rem; padding:0.15rem 0.4rem; flex-shrink:0;" data-i18n="badge_featured">${window.t('badge_featured', 'FEATURED')}</span>` : '';
                            const detailUrl = `/job/${job.slug}`; // Fallback router
                            
                            html += `
                                <div class="glass-panel job-card" style="${job.is_featured ? 'border-left: 4px solid var(--accent-color);' : ''}">
                                    <div class="job-info">
                                        <h3 style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;" title="${job.title}">
                                            <span class="notranslate" translate="no" data-translate-title="${job.title}" style="display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; text-overflow:ellipsis; white-space:normal; word-break:break-word; line-height:1.3;">${window.translateJobTitle(job.title)}</span> 
                                            ${isFeaturedBadge}
                                        </h3>
                                        <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.6rem;">
                                            <span data-translate-lookup="${job.department}">${window.t(job.department, job.department)}</span> &bull; <span data-translate-lookup="${job.state}">${window.t(job.state, job.state)}</span>
                                        </p>
                                        <div class="job-tags">
                                            <span class="badge badge-dept" data-translate-lookup="${job.qualification}">${window.t(job.qualification, job.qualification)}</span>
                                            <span class="badge" style="background: rgba(16, 185, 129, 0.08); color: #10b981; font-weight:700;" data-translate-key="vacancies_count" data-translate-suffix=": ${job.vacancy_count}">${window.t('vacancies_count', 'Vacancies')}: ${job.vacancy_count}</span>
                                            <span class="badge" style="background: rgba(139, 92, 246, 0.08); color: #8b5cf6; font-weight:700;">
                                                ${job.salary_min > 0 ? `₹ ${job.salary_min} - ₹ ${job.salary_max}` : `<span data-i18n="govt_scale">${window.t('govt_scale', 'Govt Scale')}</span>`}
                                            </span>
                                        </div>
                                    </div>
                                    <div style="display: flex; flex-direction: column; gap: 0.5rem; align-items: flex-end; justify-content: space-between;">
                                        <a href="${detailUrl}" class="btn-view" style="text-decoration:none;" data-i18n="btn_view_details">${window.t('btn_view_details', 'View Details')}</a>
                                        <span class="badge badge-deadline" style="margin-top:0.5rem;" data-translate-key="apply_by" data-translate-suffix=": ${job.last_date}">${window.t('apply_by', 'Apply by')}: ${job.last_date}</span>
                                    </div>
                                </div>
                            `;
                        });

                        $('#jobsListContainer').html(html).fadeIn();

                        // 4. Rebuild pagination buttons
                        buildPaginationButtons(data.current_page, data.last_page);

                        // 5. Update browser URL history state to ensure SEO friendliness
                        const cleanFilters = {};
                        Object.keys(filters).forEach(key => {
                            if (filters[key] !== '' && filters[key] !== false && filters[key] !== null) {
                                cleanFilters[key] = filters[key];
                            }
                        });
                        window.history.pushState(null, '', '/search?' + $.param(cleanFilters));
                    }
                },
                error: function() {
                    $('#skeletonLoader').hide();
                    $('#jobsListContainer').html(`
                        <div class="glass-panel" style="padding: 3rem; text-align: center; color: #ef4444; border-color: rgba(239,68,68,0.2);">
                            ${window.t('search_failed_timeout', 'Search failed! Connection to database indexing timed out. Please retry.')}
                        </div>
                    `).fadeIn();
                }
            });
        }

        // Helper to construct pagination buttons
        function buildPaginationButtons(current, last) {
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

        // ─── Filter Events Bindings ──────────────────────────────────────────

        $('#stateSelectFilter, #categorySelectFilter, #qualSelectFilter, #deptSelectFilter, #salarySelectFilter').on('change', function() {
            fetchSearchResults(1);
        });

        $('#noFeeCheckFilter').on('change', function() {
            fetchSearchResults(1);
        });

        // Search Input debouncer keyup trigger
        let searchTimeout = null;
        $('#searchKeywords').on('keyup', function() {
            const query = $(this).val();

            // Toggle clear search button
            if (query.length > 0) {
                if ($('#clearSearchBtn').length === 0) {
                    $('.search-input-wrapper').append('<button id="clearSearchBtn" style="background:none; border:none; color:var(--text-secondary); cursor:pointer; font-size:1.2rem; padding:0 0.5rem;">&times;</button>');
                }
            } else {
                $('#clearSearchBtn').remove();
            }

            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                fetchSearchResults(1);
            }, 350);

            // ─── Autocomplete suggestions loading ───
            clearTimeout(autocompleteTimeout);
            if (query.length < 2) {
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

                            // A. Matched Jobs
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

                            // B. Matched Categories
                            if (data.categories && data.categories.length > 0) {
                                html += `<div class="autocomplete-section">
                                    <div class="autocomplete-header">📁 Streams / Sectors</div>`;
                                data.categories.forEach(item => {
                                    html += `<div class="autocomplete-item select-suggest-slug" data-type="category" data-slug="${item.slug}">
                                        <span>${item.name} board listings</span>
                                        <span class="badge-type">stream</span>
                                    </div>`;
                                });
                                html += `</div>`;
                                totalSuggestions += data.categories.length;
                            }

                            // C. Matched States
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

                            // D. Matched Qualifications
                            if (data.qualifications && data.qualifications.length > 0) {
                                html += `<div class="autocomplete-section">
                                    <div class="autocomplete-header">🎓 Degrees</div>`;
                                data.qualifications.forEach(item => {
                                    html += `<div class="autocomplete-item select-suggest-slug" data-type="qualification" data-slug="${item.slug}">
                                        <span>Postings requiring ${item.name}</span>
                                        <span class="badge-type">eligibility</span>
                                    </div>`;
                                });
                                html += `</div>`;
                                totalSuggestions += data.qualifications.length;
                            }

                            // E. Matched Departments
                            if (data.departments && data.departments.length > 0) {
                                html += `<div class="autocomplete-section">
                                    <div class="autocomplete-header">🏢 Agencies & Boards</div>`;
                                data.departments.forEach(item => {
                                    html += `<div class="autocomplete-item select-suggest-slug" data-type="organization" data-slug="${item.slug}">
                                        <span>${item.name} (${item.code})</span>
                                        <span class="badge-type">board</span>
                                    </div>`;
                                });
                                html += `</div>`;
                                totalSuggestions += data.departments.length;
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

        // Clear Search text
        $(document).on('click', '#clearSearchBtn', function() {
            $('#searchKeywords').val('');
            $(this).remove();
            $('#autocompleteDropdown').hide().empty();
            fetchSearchResults(1);
        });

        // Autocomplete click on direct job suggestion
        $(document).on('click', '.select-suggest-job', function() {
            const slug = $(this).data('slug');
            window.location.href = `/job/${slug}`;
        });

        // Autocomplete click on stream, state, degree, board
        $(document).on('click', '.select-suggest-slug', function() {
            const type = $(this).data('type');
            const slug = $(this).data('slug');
            window.location.href = `/search/${type}/${slug}`;
        });

        // Hide autocomplete when clicking outside input
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.search-bar-glass-panel').length) {
                $('#autocompleteDropdown').hide();
            }
        });

        // Spell suggestion clicked did-you-mean handler
        $(document).on('click', '#suggestedQueryLink, .suggested-query', function(e) {
            e.preventDefault();
            const query = $(this).data('query');
            $('#searchKeywords').val(query);
            $('#typoCorrectionBanner').hide();
            fetchSearchResults(1);
        });

        // Reset all filters trigger
        $('#resetFiltersTrigger').on('click', function() {
            $('#searchKeywords').val('');
            $('#stateSelectFilter').val('');
            $('#categorySelectFilter').val('');
            $('#qualSelectFilter').val('');
            $('#deptSelectFilter').val('');
            $('#salarySelectFilter').val('');
            $('#noFeeCheckFilter').prop('checked', false);
            $('#clearSearchBtn').remove();
            $('#autocompleteDropdown').hide().empty();
            $('#typoCorrectionBanner').hide();
            
            fetchSearchResults(1);
        });

        // Pagination links click handler
        $(document).on('click', '.page-link', function(e) {
            e.preventDefault();
            const targetPage = $(this).data('page');
            fetchSearchResults(targetPage);
            $('html, body').animate({ scrollTop: $('#searchKeywords').offset().top - 120 }, 300);
        });
    });
</script>
@endsection
