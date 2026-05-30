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
        gap: 0.5rem;
        margin-top: 0.5rem;
    }

    /* Elegant grid for parameters */
    .details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.25rem;
        margin-bottom: 2.5rem;
    }

    .details-box {
        background: rgba(255, 255, 255, 0.01);
        border: 1px solid var(--border-color);
        border-radius: 10px;
        padding: 1.25rem;
        text-align: center;
        backdrop-filter: blur(8px);
    }

    .details-box-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        color: var(--text-secondary);
        font-weight: 700;
        letter-spacing: 0.05em;
        margin-bottom: 0.4rem;
    }

    .details-box-val {
        font-family: 'Outfit', sans-serif;
        font-size: 1.2rem;
        font-weight: 800;
        color: var(--text-primary);
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
</style>

<div style="max-width: 1000px; margin: 0 auto; padding: 0 5%;">
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

    <!-- Main Detail Card -->
    <article class="detail-card">
        <!-- Header -->
        <header class="detail-header-block">
            <h1>{{ $pageHeader }}</h1>
            <div class="detail-badges">
                <span class="badge">{{ strtoupper($job->post_type) }}</span>
                <span class="badge badge-dept">{{ $job->department->name ?? 'Government Ministry' }}</span>
                <span class="badge" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;">📍 {{ $job->state->name ?? 'Pan India' }}</span>
                @if($job->district)
                    <span class="badge" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">🏢 District: {{ $job->district->name }}</span>
                @endif
                <span class="badge badge-dept">{{ $job->qualification->name ?? 'Degree Required' }}</span>
            </div>
        </header>

        <!-- Dynamic parameters grid -->
        <div class="details-grid">
            <div class="details-box">
                <div class="details-box-label">Salary Range</div>
                <div class="details-box-val">
                    @if($job->salary_min > 0)
                        ₹ {{ number_format($job->salary_min, 0) }} - {{ number_format($job->salary_max, 0) }}
                    @else
                        Govt Scale
                    @endif
                </div>
            </div>
            <div class="details-box">
                <div class="details-box-label">Age Requirements</div>
                <div class="details-box-val">{{ $job->age_limit ?? '18-35 Years' }}</div>
            </div>
            <div class="details-box">
                <div class="details-box-label">Total Vacancies</div>
                <div class="details-box-val">{{ $job->vacancy_count > 0 ? number_format($job->vacancy_count) : 'Announced' }}</div>
            </div>
            <div class="details-box">
                <div class="details-box-label">Application Fee</div>
                <div class="details-box-val">
                    @if($job->application_fee > 0)
                        ₹ {{ number_format($job->application_fee, 2) }}
                    @else
                        Free (No Fee)
                    @endif
                </div>
            </div>
            <div class="details-box">
                <div class="details-box-label">Apply Deadline</div>
                <div class="details-box-val" style="color: #ef4444;">
                    {{ $job->last_date_to_apply ? $job->last_date_to_apply->format('d M Y') : 'Announced' }}
                </div>
            </div>
            <div class="details-box">
                <div class="details-box-label">Expected Exam Date</div>
                <div class="details-box-val" style="color: var(--accent-color);">
                    {{ $job->exam_date ? $job->exam_date->format('d M Y') : 'Announced Soon' }}
                </div>
            </div>
        </div>

        <!-- Description -->
        <section class="details-section">
            <h4>Recruitment Overview & Eligibility</h4>
            <div>{!! $job->description !!}</div>
        </section>

        <!-- Exam Pattern & Syllabus -->
        @if($job->exam_pattern)
            <section class="details-section">
                <h4>Official Syllabus & Exam Pattern</h4>
                <div>{!! $job->exam_pattern !!}</div>
            </section>
        @endif

        <!-- Selection Process -->
        @if($job->selection_process)
            <section class="details-section">
                <h4>Selection Process</h4>
                <div>{!! $job->selection_process !!}</div>
            </section>
        @endif

        <!-- Official Links -->
        <section class="details-section">
            <h4>Verification Links & PDF Files</h4>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 0.75rem;">
                @if($job->official_website_link)
                    <a href="{{ $job->official_website_link }}" target="_blank" class="btn-view" style="display: flex; align-items: center; gap: 0.4rem;">
                        🌐 Official Website &raquo;
                    </a>
                @endif
                @if($job->apply_link)
                    <a href="{{ $job->apply_link }}" target="_blank" class="btn-view" style="display: flex; align-items: center; gap: 0.4rem; background: var(--accent-color); color: #fff; border-color: var(--accent-color);">
                        📝 Apply Online &raquo;
                    </a>
                @endif
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
</div>

<!-- Render Schema Markup (JSON-LD) -->
<script type="application/ld+json">
{!! json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
</script>

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // standalone recruitment submission handler
        $('#standaloneRecruitmentApplyForm').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#standaloneApplySubmitBtn');
            const fileInput = $('#standaloneAppResume')[0];
            const errorDiv = $('#standaloneResumeError');
            
            errorDiv.hide().text('');
            
            if (fileInput.files.length === 0) {
                showToast('Please select a resume file.', 'error');
                return;
            }

            btn.prop('disabled', true).text('Uploading...');
            
            // Build FormData to send file correctly
            const formData = new FormData(this);

            $.ajax({
                url: '/api/jobs/{{ $job->id }}/apply',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    showToast(res.message, 'success');
                    
                    // Transition form to checkmark success card
                    $('#standaloneRecruitmentApplyForm').html(`
                        <div style="text-align: center; padding: 1.5rem 0; color: #10b981;">
                            <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="margin-bottom: 0.5rem;"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <div style="font-weight: 700; font-size: 1.05rem;">Application Submitted Successfully!</div>
                            <p style="font-size: 0.85rem; color: var(--text-secondary); margin-top: 0.25rem;">You can track the recruitment stage inside your candidate dashboard.</p>
                        </div>
                    `);
                },
                error: function(err) {
                    btn.prop('disabled', false).text('Submit Application Now');
                    if (err.status === 422) {
                        const res = err.responseJSON;
                        showToast(res.message || 'File upload validation failed.', 'error');
                        if (res.errors && res.errors.resume) {
                            errorDiv.text(res.errors.resume[0]).show();
                        }
                    } else {
                        showToast('Submission failed. Connection error.', 'error');
                    }
                }
            });
        });
    });
</script>
@endsection
