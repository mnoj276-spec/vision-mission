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
        background: linear-gradient(135deg, rgba(139, 92, 246, 0.12) 0%, rgba(37, 99, 235, 0.08) 100%);
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
        background: radial-gradient(circle, rgba(139, 92, 246, 0.1) 0%, transparent 70%);
        pointer-events: none;
    }
    .seo-hero h1 {
        font-family: 'Outfit', sans-serif;
        font-size: 2.2rem;
        font-weight: 800;
        margin-bottom: 0.75rem;
        line-height: 1.25;
        background: linear-gradient(135deg, var(--text-primary) 30%, #8b5cf6 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .seo-hero p {
        font-size: 1rem;
        color: var(--text-secondary);
        max-width: 800px;
        line-height: 1.6;
    }

    /* Tab switcher for salary dimensions */
    .salary-tabs {
        display: flex;
        gap: 1rem;
        margin-bottom: 2rem;
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 1rem;
        flex-wrap: wrap;
    }
    .salary-tab-btn {
        background: var(--glass-bg);
        border: 1px solid var(--glass-border);
        padding: 0.75rem 1.5rem;
        border-radius: 50px;
        cursor: pointer;
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--text-primary);
        font-family: 'Outfit', sans-serif;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        backdrop-filter: blur(8px);
    }
    .salary-tab-btn.active {
        background: #8b5cf6;
        color: #ffffff;
        border-color: #8b5cf6;
        box-shadow: 0 4px 15px rgba(139, 92, 246, 0.25);
    }
    .salary-tab-btn:hover:not(.active) {
        border-color: #8b5cf6;
        transform: translateY(-2px);
    }

    /* Glassmorphic Data Table Panel */
    .salary-panel {
        background: var(--glass-bg);
        border: 1px solid var(--glass-border);
        border-radius: 16px;
        padding: 2rem;
        box-shadow: var(--card-shadow);
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
        margin-bottom: 2.5rem;
    }
    .salary-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }
    .salary-table th {
        font-family: 'Outfit', sans-serif;
        font-weight: 700;
        font-size: 0.9rem;
        text-transform: uppercase;
        color: var(--text-secondary);
        padding: 1rem 0.75rem;
        border-bottom: 1.5px solid var(--border-color);
    }
    .salary-table td {
        padding: 1.25rem 0.75rem;
        border-bottom: 1px solid var(--border-color);
        font-size: 0.95rem;
        vertical-align: middle;
    }
    .salary-table tr:last-child td {
        border-bottom: none;
    }
    .salary-table tr:hover td {
        background: rgba(255, 255, 255, 0.02);
    }

    /* Salary gauge scale bar */
    .salary-scale-container {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        width: 100%;
        min-width: 150px;
    }
    .salary-scale-bar {
        width: 100%;
        height: 8px;
        border-radius: 4px;
        background: var(--border-color);
        overflow: hidden;
        position: relative;
    }
    .salary-scale-fill {
        height: 100%;
        border-radius: 4px;
        background: linear-gradient(90deg, #8b5cf6 0%, var(--accent-color) 100%);
    }
    .salary-scale-labels {
        display: flex;
        justify-content: space-between;
        font-size: 0.75rem;
        color: var(--text-secondary);
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .salary-table th:nth-child(3),
        .salary-table td:nth-child(3) {
            display: none;
        }
        .salary-panel {
            padding: 1rem;
            overflow-x: auto;
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
        <h1>Sarkari Job Salary Matrix 2026</h1>
        <p>{{ $metaDescription }}</p>
    </section>

    <!-- Tab Selection Bar -->
    <div class="salary-tabs">
        <button class="salary-tab-btn active" data-target="category-panel">💼 Salaries by Stream / Sector</button>
        <button class="salary-tab-btn" data-target="department-panel">🏛️ Salaries by Department</button>
        <button class="salary-tab-btn" data-target="state-panel">📍 Salaries by State / Region</button>
    </div>

    <!-- SECTION 1: Category salaries -->
    <div class="salary-panel salary-section-block" id="category-panel">
        <h2 style="font-family:'Outfit'; font-size:1.3rem; margin-bottom:1.5rem; color:#8b5cf6; display:flex; align-items:center; gap:0.5rem;">
            <span style="width:8px; height:18px; background:#8b5cf6; border-radius:3px; display:inline-block;"></span>
            Pay Ranges Across Recruitment Sectors
        </h2>
        <table class="salary-table">
            <thead>
                <tr>
                    <th>Recruitment Sector</th>
                    <th>Average Monthly Salary</th>
                    <th>Salary Range (Min - Max)</th>
                    <th>Active Posts</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $cat)
                    @php
                        // Normalize avg salary relative to max 120,000 for gauge scaling
                        $maxRef = 120000;
                        $fillPercent = min(100, max(15, ($cat->avg_salary / $maxRef) * 100));
                    @endphp
                    <tr>
                        <td><strong>{{ $cat->name }}</strong></td>
                        <td>
                            <div class="salary-scale-container">
                                <div style="font-weight:700; color:var(--text-primary);">₹ {{ number_format($cat->avg_salary, 0) }}</div>
                                <div class="salary-scale-bar">
                                    <div class="salary-scale-fill" style="width: {{ $fillPercent }}%;"></div>
                                </div>
                            </div>
                        </td>
                        <td><span style="font-weight:500;">₹ {{ number_format($cat->min_salary, 0) }}</span> - <span style="font-weight:500;">₹ {{ number_format($cat->max_salary, 0) }}</span></td>
                        <td><span class="badge badge-dept">{{ $cat->count }} Active</span></td>
                        <td style="text-align:right;">
                            <a href="/search/category/{{ $cat->slug }}" class="btn-view" style="padding:0.4rem 0.8rem; font-size:0.8rem; border-color:rgba(139, 92, 246, 0.3);">
                                Explore &raquo;
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center; padding:3rem; color:var(--text-secondary);">No salary aggregation metrics are currently available.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- SECTION 2: Department salaries -->
    <div class="salary-panel salary-section-block" id="department-panel" style="display:none;">
        <h2 style="font-family:'Outfit'; font-size:1.3rem; margin-bottom:1.5rem; color:#8b5cf6; display:flex; align-items:center; gap:0.5rem;">
            <span style="width:8px; height:18px; background:#8b5cf6; border-radius:3px; display:inline-block;"></span>
            Highest Paying Government Departments (Top 15)
        </h2>
        <table class="salary-table">
            <thead>
                <tr>
                    <th>Department / Organization</th>
                    <th>Average Monthly Salary</th>
                    <th>Salary Range (Min - Max)</th>
                    <th>Active Posts</th>
                </tr>
            </thead>
            <tbody>
                @forelse($departments as $dept)
                    @php
                        $maxRef = 120000;
                        $fillPercent = min(100, max(15, ($dept->avg_salary / $maxRef) * 100));
                    @endphp
                    <tr>
                        <td><strong>{{ $dept->name }}</strong></td>
                        <td>
                            <div class="salary-scale-container">
                                <div style="font-weight:700; color:var(--text-primary);">₹ {{ number_format($dept->avg_salary, 0) }}</div>
                                <div class="salary-scale-bar">
                                    <div class="salary-scale-fill" style="width: {{ $fillPercent }}%; background: linear-gradient(90deg, #10b981 0%, #3b82f6 100%);"></div>
                                </div>
                            </div>
                        </td>
                        <td><span style="font-weight:500;">₹ {{ number_format($dept->min_salary, 0) }}</span> - <span style="font-weight:500;">₹ {{ number_format($dept->max_salary, 0) }}</span></td>
                        <td><span class="badge badge-dept" style="background:rgba(16,185,129,0.08); color:#10b981;">{{ $dept->count }} Posts</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align:center; padding:3rem; color:var(--text-secondary);">No salary aggregation metrics are currently available.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- SECTION 3: State salaries -->
    <div class="salary-panel salary-section-block" id="state-panel" style="display:none;">
        <h2 style="font-family:'Outfit'; font-size:1.3rem; margin-bottom:1.5rem; color:#8b5cf6; display:flex; align-items:center; gap:0.5rem;">
            <span style="width:8px; height:18px; background:#8b5cf6; border-radius:3px; display:inline-block;"></span>
            Pay Ranges Grouped by State / Region
        </h2>
        <table class="salary-table">
            <thead>
                <tr>
                    <th>State / Territory</th>
                    <th>Average Monthly Salary</th>
                    <th>Salary Range (Min - Max)</th>
                    <th>Active Posts</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($states as $st)
                    @php
                        $maxRef = 120000;
                        $fillPercent = min(100, max(15, ($st->avg_salary / $maxRef) * 100));
                    @endphp
                    <tr>
                        <td><strong>{{ $st->name }}</strong></td>
                        <td>
                            <div class="salary-scale-container">
                                <div style="font-weight:700; color:var(--text-primary);">₹ {{ number_format($st->avg_salary, 0) }}</div>
                                <div class="salary-scale-bar">
                                    <div class="salary-scale-fill" style="width: {{ $fillPercent }}%; background: linear-gradient(90deg, #3b82f6 0%, #ef4444 100%);"></div>
                                </div>
                            </div>
                        </td>
                        <td><span style="font-weight:500;">₹ {{ number_format($st->min_salary, 0) }}</span> - <span style="font-weight:500;">₹ {{ number_format($st->max_salary, 0) }}</span></td>
                        <td><span class="badge badge-dept" style="background:rgba(59,130,246,0.08); color:#3b82f6;">{{ $st->count }} Posts</span></td>
                        <td style="text-align:right;">
                            <a href="/search/state/{{ $st->slug }}" class="btn-view" style="padding:0.4rem 0.8rem; font-size:0.8rem; border-color:rgba(59, 130, 246, 0.3);">
                                Explore &raquo;
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center; padding:3rem; color:var(--text-secondary);">No salary aggregation metrics are currently available.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Tab switcher action bind
        $('.salary-tab-btn').on('click', function(e) {
            e.preventDefault();
            $(this).addClass('active').siblings().removeClass('active');
            
            const targetBlock = $(this).data('target');
            $('.salary-section-block').hide();
            $(`#${targetBlock}`).fadeIn();
        });
    });
</script>
@endsection
