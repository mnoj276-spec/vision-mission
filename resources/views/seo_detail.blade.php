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
                <div class="details-box-label">Salary Details</div>
                <div class="details-box-val">
                    @if($job->stipend)
                        {{ $job->stipend }}
                    @elseif($job->salary_min > 0 && $job->salary_max > 0)
                        ₹ {{ number_format($job->salary_min, 0) }} - {{ number_format($job->salary_max, 0) }}
                    @elseif($job->salary_min > 0)
                        ₹ {{ number_format($job->salary_min, 0) }} onwards
                    @elseif($job->pay_scale)
                        {{ $job->pay_scale }}
                    @else
                        Govt Scale
                    @endif
                </div>
                @php
                    $extra = [];
                    if ($job->pay_level) $extra[] = "📈 " . $job->pay_level;
                    if ($job->salary_grade) $extra[] = "🎖️ " . $job->salary_grade;
                    if ($job->pay_matrix) $extra[] = "📊 " . $job->pay_matrix;
                @endphp
                @if(count($extra) > 0)
                    <div style="font-size: 0.7rem; color: var(--text-secondary); margin-top: 0.4rem; display: flex; gap: 0.3rem; justify-content: center; flex-wrap: wrap; opacity: 0.85;">
                        {!! implode(' | ', $extra) !!}
                    </div>
                @endif
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
                            <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--border-color); border-radius: 8px; padding: 1rem;">
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

        <!-- Official Links -->
        <section class="details-section">
            <h4>Verification Links & PDF Files</h4>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 0.75rem;">
                @if($job->official_website_link)
                    <a href="{{ $job->official_website_link }}" target="_blank" rel="nofollow noopener" class="btn-view" style="display: flex; align-items: center; gap: 0.4rem;">
                        🌐 Official Website &raquo;
                    </a>
                @endif
                @if($job->apply_link)
                    <a href="{{ $job->apply_link }}" target="_blank" rel="nofollow noopener" class="btn-view" style="display: flex; align-items: center; gap: 0.4rem; background: var(--accent-color); color: #fff; border-color: var(--accent-color);">
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
