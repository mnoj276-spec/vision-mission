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

    /* SEO Hero styled header */
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

    /* Premium interactive wizard panel */
    .wizard-panel {
        background: var(--glass-bg);
        border: 1px solid var(--glass-border);
        border-radius: 16px;
        padding: 2rem;
        box-shadow: var(--card-shadow);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        margin-bottom: 2.5rem;
    }
    .wizard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1.5rem;
    }
    .wizard-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    .wizard-label {
        font-family: 'Outfit', sans-serif;
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--text-primary);
    }
    .wizard-select, .wizard-input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        background: var(--bg-secondary);
        color: var(--text-primary);
        font-size: 0.95rem;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }
    .wizard-select:focus, .wizard-input:focus {
        border-color: var(--accent-color);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
    }

    /* Age range styling */
    .age-container {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .age-slider {
        flex: 1;
        height: 6px;
        border-radius: 3px;
        background: var(--border-color);
        outline: none;
        -webkit-appearance: none;
    }
    .age-slider::-webkit-slider-thumb {
        -webkit-appearance: none;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: var(--accent-color);
        cursor: pointer;
        transition: transform 0.1s;
    }
    .age-slider::-webkit-slider-thumb:hover {
        transform: scale(1.2);
    }
    .age-number-input {
        width: 70px;
        text-align: center;
    }

    /* Interactive filter action bar */
    .wizard-actions {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 1.5rem;
        border-top: 1px solid var(--border-color);
        padding-top: 1.5rem;
    }

    /* Matching jobs list styled table */
    .results-panel {
        background: rgba(255, 255, 255, 0.01);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 1.5rem;
        box-shadow: var(--card-shadow);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        margin-bottom: 2.5rem;
    }
    .results-title {
        font-family: 'Outfit', sans-serif;
        font-size: 1.3rem;
        font-weight: 700;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        color: var(--accent-color);
    }
    .job-row-item {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr 1fr;
        gap: 1rem;
        padding: 1.25rem 0.75rem;
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
        font-size: 1rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
        color: var(--text-primary);
    }
    .job-title-col p {
        font-size: 0.82rem;
        color: var(--text-secondary);
    }
    .job-meta-col {
        font-size: 0.88rem;
        color: var(--text-primary);
        font-weight: 500;
    }
    .job-deadline-col {
        font-size: 0.85rem;
        color: #ef4444;
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .job-row-item {
            grid-template-columns: 1fr;
            gap: 0.75rem;
            padding: 1.25rem 0;
        }
        .wizard-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div style="max-width: 1400px; margin: 0 auto; padding: 0 5%;">
    <!-- Breadcrumbs -->
    <div class="breadcrumb-trail">
        <a href="/">Home</a>
        @foreach($breadcrumbs as $label => $url)
            <span class="breadcrumb-separator">&raquo;</span>
            @if($url)
                <a href="{{ $url }}">{{ $label }}</a>
            @else
                <span>{{ $label }}</span>
            @endif
        @endforeach
    </div>

    <!-- Hero Header -->
    <section class="seo-hero">
        <h1>Sarkari Job Eligibility Checker 2026</h1>
        <p>{{ $metaDescription }}</p>
    </section>

    <!-- Interactive Glassmorphic Selector Form -->
    <section class="wizard-panel">
        <form id="eligibilityCheckerForm">
            <div class="wizard-grid">
                <!-- 1. Education Qualification Select -->
                <div class="wizard-group">
                    <label for="qualification_id" class="wizard-label">🎓 Highest Qualification</label>
                    <select id="qualification_id" name="qualification_id" class="wizard-select">
                        <option value="">-- Select Qualification --</option>
                        @foreach($qualifications as $qual)
                            <option value="{{ $qual->id }}">{{ $qual->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- 2. Home Region State Select -->
                <div class="wizard-group">
                    <label for="state_id" class="wizard-label">📍 Preferred State / Region</label>
                    <select id="state_id" name="state_id" class="wizard-select">
                        <option value="">-- All India (Central) --</option>
                        @foreach($states as $st)
                            <option value="{{ $st->id }}">{{ $st->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- 3. Desired Stream/Category Select -->
                <div class="wizard-group">
                    <label for="category_id" class="wizard-label">💼 Recruitment Category</label>
                    <select id="category_id" name="category_id" class="wizard-select">
                        <option value="">-- All Sectors --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- 4. Interactive Age Slider -->
                <div class="wizard-group">
                    <label for="age" class="wizard-label">🎂 Candidate Age (Years)</label>
                    <div class="age-container">
                        <input type="range" id="age_slider" class="age-slider" min="16" max="65" value="21">
                        <input type="number" id="age" name="age" class="wizard-input age-number-input" min="16" max="65" value="21">
                    </div>
                </div>
            </div>

            <!-- Action buttons -->
            <div class="wizard-actions">
                <button type="button" id="resetEligibilityBtn" class="btn-secondary">Reset Filters</button>
                <button type="submit" id="checkEligibilityBtn" class="btn-primary" style="background: linear-gradient(135deg, var(--accent-color) 0%, var(--accent-hover) 100%);">
                    Check Eligibility &raquo;
                </button>
            </div>
        </form>
    </section>

    <!-- Real-time eligibility match display section -->
    <section class="results-panel">
        <div class="results-title">
            <span style="display:flex; align-items:center; gap:0.5rem;">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138z"></path></svg>
                Matched Recruitments
            </span>
            <span id="resultsCountBadge" class="badge" style="background:var(--accent-color); color:#fff; font-size:0.85rem;">0 Matches</span>
        </div>

        <!-- Skeleton Loader placeholder -->
        <div id="resultsSkeletonLoader" style="display:none;">
            <div class="skeleton-job" style="height:80px; margin-bottom:1rem;"></div>
            <div class="skeleton-job" style="height:80px; margin-bottom:1rem;"></div>
            <div class="skeleton-job" style="height:80px; margin-bottom:1rem;"></div>
        </div>

        <!-- Real matches content list loaded dynamically -->
        <div id="matchedJobsContainer">
            <div style="padding: 4rem; text-align: center; color: var(--text-secondary); font-size: 0.95rem;">
                <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin-bottom:1rem; opacity:0.6;"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <div style="font-weight:700; font-family:'Outfit'; font-size:1.1rem; color:var(--text-primary); margin-bottom:0.25rem;">No Check Performed Yet</div>
                <p>Input your parameters above and click "Check Eligibility" to instantly discover matching active recruitments.</p>
            </div>
        </div>
    </section>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        const ageSlider = $('#age_slider');
        const ageInput = $('#age');

        // Synchronize age range slider & number input
        ageSlider.on('input', function() {
            ageInput.val($(this).val());
        });
        ageInput.on('input', function() {
            let val = parseInt($(this).val());
            if (isNaN(val)) val = 21;
            if (val < 16) val = 16;
            if (val > 65) val = 65;
            ageSlider.val(val);
        });

        // AJAX search check handler
        function runEligibilityCheck() {
            $('#matchedJobsContainer').hide();
            $('#resultsSkeletonLoader').show();

            const queryData = $('#eligibilityCheckerForm').serialize();

            $.ajax({
                url: '{{ route("eligibility.check") }}',
                method: 'GET',
                data: queryData,
                success: function(res) {
                    $('#resultsSkeletonLoader').hide();
                    
                    if (res.status === 'success') {
                        const jobs = res.data;
                        $('#resultsCountBadge').text(`${jobs.length} Matches`);

                        if (jobs.length === 0) {
                            $('#matchedJobsContainer').html(`
                                <div style="padding: 4rem; text-align: center; color: var(--text-secondary); font-size: 0.95rem;">
                                    <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin-bottom:1rem; opacity:0.6;"><circle cx="12" cy="12" r="10"></circle><path d="M15 9l-6 6m0-6l6 6"></path></svg>
                                    <div style="font-weight:700; font-family:'Outfit'; font-size:1.1rem; color:var(--text-primary); margin-bottom:0.25rem;">No Matches Found</div>
                                    <p>We couldn't find any active government job postings matching your specific education, region, or age criteria.</p>
                                </div>
                            `).fadeIn();
                            return;
                        }

                        let html = '';
                        jobs.forEach(function(job) {
                            html += `
                                <div class="job-row-item">
                                    <div class="job-title-col">
                                        <h3>${job.title}</h3>
                                        <p>${job.department} &bull; ${job.state}</p>
                                    </div>
                                    <div class="job-meta-col">
                                        <span class="badge badge-dept" style="padding:0.2rem 0.5rem; font-size:0.75rem;">${job.qualification}</span>
                                        <div style="margin-top:0.25rem; font-size:0.75rem; color:var(--text-secondary);">Vacancies: ${job.vacancy_count}</div>
                                    </div>
                                    <div class="job-meta-col">
                                        <div style="font-weight:700; color:var(--text-primary);">₹ ${job.salary_min} - ${job.salary_max}</div>
                                        <div style="font-size:0.72rem; color:var(--text-secondary);">Monthly Scale</div>
                                    </div>
                                    <div class="job-deadline-col" style="display:flex; flex-direction:column; gap:0.25rem; align-items:flex-end;">
                                        <span>Apply by ${job.last_date}</span>
                                        <a href="/job/${job.slug}" class="btn-view" style="padding:0.35rem 0.75rem; font-size:0.75rem; text-decoration:none; display:inline-block;">
                                            Details &raquo;
                                        </a>
                                    </div>
                                </div>
                            `;
                        });

                        $('#matchedJobsContainer').html(html).fadeIn();
                        showToast(`Successfully filtered ${jobs.length} eligible government jobs!`, 'success');
                    }
                },
                error: function() {
                    $('#resultsSkeletonLoader').hide();
                    $('#matchedJobsContainer').html(`
                        <div class="glass-panel" style="padding: 3rem; text-align: center; color: #ef4444; border-color: rgba(239,68,68,0.2);">
                            <strong>System error occurred!</strong> Could not perform eligibility check. Please try again.
                        </div>
                    `).fadeIn();
                    showToast('Eligibility validation query failed.', 'error');
                }
            });
        }

        // Form submit binding
        $('#eligibilityCheckerForm').on('submit', function(e) {
            e.preventDefault();
            runEligibilityCheck();
        });

        // Reset filters binding
        $('#resetEligibilityBtn').on('click', function() {
            $('#eligibilityCheckerForm')[0].reset();
            ageSlider.val(21);
            ageInput.val(21);
            $('#resultsCountBadge').text('0 Matches');
            $('#matchedJobsContainer').html(`
                <div style="padding: 4rem; text-align: center; color: var(--text-secondary); font-size: 0.95rem;">
                    <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin-bottom:1rem; opacity:0.6;"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <div style="font-weight:700; font-family:'Outfit'; font-size:1.1rem; color:var(--text-primary); margin-bottom:0.25rem;">Filters Cleared</div>
                    <p>Input your parameters above and click "Check Eligibility" to instantly discover matching active recruitments.</p>
                </div>
            `);
            showToast('Parameters reset successfully.', 'info');
        });
    });
</script>
@endsection
