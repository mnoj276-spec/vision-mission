@extends('layouts.app')

@section('title', 'Enterprise Control Center - GovJobs Admin')

@php
    $sidebarMenu = [
        ['block' => 'overview',   'permission' => 'view_dashboard',    'label' => 'Dashboard Overview',       'icon' => '<rect x="3" y="3" width="7" height="9"></rect><rect x="14" y="3" width="7" height="5"></rect><rect x="14" y="12" width="7" height="9"></rect><rect x="3" y="16" width="7" height="5"></rect>'],
        ['block' => 'analytics',  'permission' => 'view_dashboard',    'label' => 'Telemetry & Analytics',    'icon' => '<line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line>'],
        ['block' => 'jobs',       'permission' => 'view_jobs',         'label' => 'Recruitment Postings',     'icon' => '<rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>'],
        ['block' => 'crawlers',   'permission' => 'create_jobs',       'label' => 'Crawler Target Configs',   'icon' => '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line>'],
        ['block' => 'master',     'permission' => 'view_master_data',  'label' => 'Master Data Manager',      'icon' => '<path d="M12 2L2 7l10 5 10-5-10-5z"></path><path d="M2 17l10 5 10-5"></path><path d="M2 12l10 5 10-5"></path>'],
        ['block' => 'users',      'permission' => 'manage_users',      'label' => 'User Access Panel',        'icon' => '<path d="M17 21v-2a4 4 0 0 0-3-3.87"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path>'],
        ['block' => 'queues',     'permission' => 'manage_queues',     'label' => 'Queue Engine & DLQ',       'icon' => '<rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect><rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect><line x1="6" y1="6" x2="6.01" y2="6"></line><line x1="6" y1="18" x2="6.01" y2="18"></line>'],
        ['block' => 'marketing',  'permission' => 'manage_queues',     'label' => 'Email Automation',         'icon' => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline>'],
        ['block' => 'settings',   'permission' => 'manage_seo',        'label' => 'Settings Management',      'icon' => '<circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path>'],
        ['block' => 'audit',      'permission' => 'view_audit_logs',   'label' => 'Audit Activity Logs',      'icon' => '<circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline>'],
        ['block' => 'ai-content', 'permission' => 'view_ai_content',   'label' => 'Content Verification Hub',       'icon' => '<polyline points="9 11 12 14 22 4"></polyline><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>'],
        ['block' => 'rbac',       'permission' => 'manage_users',      'label' => 'RBAC Clearance Matrix',    'icon' => '<rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path>'],
    ];

    // Filter sidebar menu items by active feature version
    $sidebarMenu = array_filter($sidebarMenu, function ($item) {
        return feature_enabled($item['block']);
    });

    $activeBlock = null;
    foreach ($sidebarMenu as $item) {
        if (auth()->user()->can($item['permission'])) {
            $activeBlock = $item['block'];
            break;
        }
    }
@endphp

@section('content')
<style>
    /* Fix dropdown clipping in User Access Registry */
    #admin-users .responsive-table-container {
        overflow: visible !important;
        min-height: 400px; /* Ensure space for dropdown */
    }
</style>
<div class="admin-container min-h-screen gap-8 px-[5%] max-w-[1600px] mx-auto mt-8 grid grid-cols-1 md:grid-cols-[200px_1fr] lg:grid-cols-[260px_1fr]">
    
    <!-- 1. Enterprise Sidebar Navigation -->
    <aside class="glass-panel admin-sidebar p-6 h-fit sticky top-[100px] rounded-2xl">
        <div class="text-center mb-8 border-b border-gray-200 pb-6">
            <h2 class="font-['Outfit'] text-xl text-blue-600 mb-1 font-semibold">Admin Workspace</h2>
            <span class="text-xs text-gray-500 bg-blue-50 px-2 py-1 rounded-full uppercase tracking-wide font-medium">Clearance: Level 3</span>
            
            <button type="button" id="adminSidebarToggleBtn" class="admin-sidebar-toggle-btn btn-secondary w-full mt-4 flex items-center justify-center gap-2">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                <span>Toggle Console Menu</span>
            </button>
        </div>
        
        <div id="adminSidebarLinks" class="admin-sidebar-links flex flex-col gap-2">
            <div class="admin-nav-links flex flex-col gap-2">
                @foreach ($sidebarMenu as $item)
                    @can($item['permission'])
                        <button class="admin-nav-btn {{ $activeBlock === $item['block'] ? 'active' : '' }}" data-block="{{ $item['block'] }}">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">{!! $item['icon'] !!}</svg>
                            {{ $item['label'] }}
                        </button>
                    @endcan
                @endforeach
            </div>
            
            <div class="mt-12 text-center">
                <a href="/" class="btn-secondary w-full">&larr; Exit Console</a>
            </div>
        </div>
    </aside>

    <!-- 2. Main Administration Canvas -->
    <main class="admin-content-canvas flex flex-col gap-8 min-w-0">
        
        <!-- ================= PANEL 1: DASHBOARD OVERVIEW ================= -->
        <section class="admin-panel-block active" id="admin-overview">
            <h2 class="font-['Outfit'] text-3xl mb-6">SaaS Control Panel Overview</h2>
            
            <!-- Statistics Metric Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                <div class="glass-panel stat-card-premium border-l-4 border-blue-600 p-5 rounded-xl shadow-sm hover:shadow-md transition-shadow">
                    <div class="text-sm text-gray-500 font-semibold mb-1 uppercase tracking-wide">Total Published Posts</div>
                    <div class="text-3xl font-bold text-gray-900 dark:text-white" id="overview-jobs-posted">0</div>
                    <div class="text-xs text-gray-400 mt-2">Active direct announcements</div>
                </div>
                <div class="glass-panel stat-card-premium border-l-4 border-emerald-500 p-5 rounded-xl shadow-sm hover:shadow-md transition-shadow">
                    <div class="text-sm text-gray-500 font-semibold mb-1 uppercase tracking-wide">Crawl Target Feeds</div>
                    <div class="text-3xl font-bold text-gray-900 dark:text-white" id="overview-sources">0</div>
                    <div class="text-xs text-emerald-600 font-medium mt-2" id="overview-active-sources">0 active crawlers</div>
                </div>
                <div class="glass-panel stat-card-premium border-l-4 border-amber-500 p-5 rounded-xl shadow-sm hover:shadow-md transition-shadow">
                    <div class="text-sm text-gray-500 font-semibold mb-1 uppercase tracking-wide">Logs Quarantined</div>
                    <div class="text-3xl font-bold text-amber-500" id="overview-quarantines">0</div>
                    <div class="text-xs text-amber-600 font-medium mt-2">Pending manual corrections</div>
                </div>
                <div class="glass-panel stat-card-premium border-l-4 border-red-500 p-5 rounded-xl shadow-sm hover:shadow-md transition-shadow">
                    <div class="text-sm text-gray-500 font-semibold mb-1 uppercase tracking-wide">Automation Success Rate</div>
                    <div class="text-3xl font-bold text-emerald-500" id="overview-success-runs">100%</div>
                    <div class="text-xs text-red-500 font-medium mt-2" id="overview-failed-runs">0 critical errors</div>
                </div>
            </div>

            <!-- Visualization & Telemetry Grid Row -->
            <div class="grid grid-cols-1 lg:grid-cols-[2fr_1fr] gap-8 items-start">
                <!-- Crawler status and health -->
                <div class="glass-panel p-6 rounded-2xl">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.5rem;">
                        <h3 class="font-['Outfit'] text-xl text-blue-600 font-semibold" style="margin: 0;">System Health & Crawl Metrics</h3>
                        <div>
                            <input type="text" id="overview-crawlers-search" placeholder="Search feeds..." style="max-width: 200px; font-size: 0.8rem; padding: 0.4rem 0.75rem; border-radius: 8px; border: 1px solid var(--border-color); background: var(--bg-primary); color: var(--text-primary); outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--accent-color)'" onblur="this.style.borderColor='var(--border-color)'" />
                        </div>
                    </div>
                    <div class="responsive-table-container">
                        <table class="enterprise-table density-compact">
                            <thead>
                                <tr>
                                    <th>Target Crawl Feed</th>
                                    <th>Last Execution Log</th>
                                    <th class="text-right">Harvests</th>
                                    <th class="text-center">Health</th>
                                </tr>
                            </thead>
                            <tbody id="overview-crawlers-table">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem; flex-wrap: wrap; gap: 0.5rem;">
                        <div id="overview-crawlers-count" style="font-size: 0.85rem; color: var(--text-secondary);">
                            Showing 0-0 of 0 entries
                        </div>
                        <div id="overview-crawlers-pagination" class="flex gap-1">
                            <!-- Populated dynamically -->
                        </div>
                    </div>
                </div>

                <!-- SVG Graph circular gauge & Quarantined Listings column -->
                <div style="display: flex; flex-direction: column; gap: 2rem;">
                    <!-- SVG Graph circular gauge -->
                    <div class="glass-panel" style="padding: 1.5rem; border-radius: 16px; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        <h3 style="font-family: 'Outfit'; font-size: 1.1rem; margin-bottom: 1.5rem; margin-top: 0;">Crawl Success Ratio</h3>
                        <div style="position: relative; width: 140px; height: 140px;">
                            <svg width="140" height="140" viewBox="0 0 36 36" style="transform: rotate(-90deg);">
                                <circle cx="18" cy="18" r="16" fill="none" stroke="var(--border-color)" stroke-width="3"></circle>
                                <circle id="success-svg-gauge" cx="18" cy="18" r="16" fill="none" stroke="#10b981" stroke-width="3" stroke-dasharray="100 100" stroke-linecap="round"></circle>
                            </svg>
                            <div id="success-ratio-label" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 1.4rem; font-weight: bold; font-family: 'Outfit'; color: #10b981;">100%</div>
                        </div>
                        <p style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 1.5rem; line-height: 1.4;">Ratio of successful feed harvesting runs to critical diagnostic failures.</p>
                    </div>

                    <!-- Pending Quarantine rescue card -->
                    <div class="glass-panel" style="padding: 1.5rem; border-radius: 16px;">
                        <h3 style="font-family: 'Outfit'; font-size: 1.15rem; color: #f59e0b; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; margin-top: 0;"><span style="display:inline-block; width:8px; height:16px; background:#f59e0b; border-radius:2px;"></span> Quarantined Listings</h3>
                        <div id="admin-quarantine-override-canvas">
                            <!-- Populated dynamically via AJAX -->
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ================= PANEL 1B: TELEMETRY & ANALYTICS ================= -->
        <section class="admin-panel-block" id="admin-analytics" style="display: none;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="font-family: 'Outfit'; font-size: 1.75rem; margin: 0;">Telemetry & Analytics Control Center</h2>
                <div>
                    <select id="analytics-timeframe" style="width: auto; font-size: 0.85rem; padding-top: 0.4rem !important; padding-bottom: 0.4rem !important;">
                        <option value="7">Last 7 Days</option>
                        <option value="14" selected>Last 14 Days</option>
                        <option value="30">Last 30 Days</option>
                    </select>
                </div>
            </div>

            <!-- KPI Cards -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem;">
                <div class="glass-panel stat-card-premium" style="border-left: 5px solid var(--accent-color);">
                    <div class="label">Total Job Views</div>
                    <div class="number" id="analytics-kpi-views">0</div>
                    <div class="subtext">Active portal details loads</div>
                </div>
                <div class="glass-panel stat-card-premium" style="border-left: 5px solid #10b981;">
                    <div class="label">Click-Through Rate (CTR)</div>
                    <div class="number" id="analytics-kpi-ctr" style="color: #10b981;">0%</div>
                    <div class="subtext">Views to applies/bookmarks</div>
                </div>
                <div class="glass-panel stat-card-premium" style="border-left: 5px solid #f59e0b;">
                    <div class="label">Total Search Queries</div>
                    <div class="number" id="analytics-kpi-searches" style="color: #f59e0b;">0</div>
                    <div class="subtext">Keyword & filter searches</div>
                </div>
                <div class="glass-panel stat-card-premium" style="border-left: 5px solid #ef4444;">
                    <div class="label">Estimated Ad Earnings</div>
                    <div class="number" id="analytics-kpi-revenue" style="color: #ef4444;">$0.00</div>
                    <div class="subtext">CPM impressions + CPC clicks</div>
                </div>
            </div>

            <!-- Chart Row 1: Traffic and Revenue -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
                <div class="glass-panel" style="padding: 1.5rem; border-radius: 16px; min-height: 380px;">
                    <h3 style="font-family: 'Outfit'; font-size: 1.15rem; color: var(--accent-color); margin-bottom: 1.5rem;">Daily Traffic Breakdown (Bots vs Organic vs Direct)</h3>
                    <div style="position: relative; height: 280px; width: 100%;">
                        <canvas id="trafficChart"></canvas>
                    </div>
                </div>

                <div class="glass-panel" style="padding: 1.5rem; border-radius: 16px; min-height: 380px;">
                    <h3 style="font-family: 'Outfit'; font-size: 1.15rem; color: #ef4444; margin-bottom: 1.5rem;">Estimated Ad Monetization Revenue Stream ($)</h3>
                    <div style="position: relative; height: 280px; width: 100%;">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Chart Row 2: Funnel and Top User Journeys -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
                <div class="glass-panel" style="padding: 1.5rem; border-radius: 16px; min-height: 380px;">
                    <h3 style="font-family: 'Outfit'; font-size: 1.15rem; color: #10b981; margin-bottom: 1.5rem;">Conversions & Engagement Funnel</h3>
                    <div style="position: relative; height: 280px; width: 100%;">
                        <canvas id="funnelChart"></canvas>
                    </div>
                </div>

                <div class="glass-panel" style="padding: 1.5rem; border-radius: 16px;">
                    <h3 style="font-family: 'Outfit'; font-size: 1.15rem; color: #f59e0b; margin-bottom: 1rem;">Frequent User Journey Pathways</h3>
                    <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1.5rem;">The most common sequence of page visits preceding conversion events.</p>
                    <div id="analytics-journeys-container" style="display: flex; flex-direction: column; gap: 1rem;">
                        <!-- Populated dynamically -->
                    </div>
                </div>
            </div>

            <!-- Tables Row: Top Search Queries and Job CTR Performance -->
            <div class="grid grid-cols-1 lg:grid-cols-[1fr_1.2fr] gap-8">
                <div class="glass-panel" style="padding: 1.5rem; border-radius: 16px;">
                    <h3 style="font-family: 'Outfit'; font-size: 1.15rem; margin-bottom: 1rem; color: var(--accent-color);">Top 10 Search Queries</h3>
                    <div class="responsive-table-container">
                        <table class="enterprise-table density-compact">
                            <thead>
                                <tr>
                                    <th>Keyword Query</th>
                                    <th class="text-right">Hits</th>
                                    <th class="text-right">Avg Results</th>
                                </tr>
                            </thead>
                            <tbody id="analytics-queries-table">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="glass-panel" style="padding: 1.5rem; border-radius: 16px;">
                    <h3 style="font-family: 'Outfit'; font-size: 1.15rem; margin-bottom: 1rem; color: #10b981;">Job Post CTR Performance Leaderboard</h3>
                    <div class="responsive-table-container">
                        <table class="enterprise-table density-compact">
                            <thead>
                                <tr>
                                    <th>Job Title</th>
                                    <th class="text-right">Views</th>
                                    <th class="text-right">Bookmarks</th>
                                    <th class="text-right">Applies</th>
                                    <th class="text-right">CTR</th>
                                </tr>
                            </thead>
                            <tbody id="analytics-ctr-table">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- ================= PANEL 2: RECRUITMENT POSTINGS ================= -->
        <section class="admin-panel-block" id="admin-jobs" style="display: none;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="font-family: 'Outfit'; font-size: 1.75rem; margin: 0;">Recruitment Postings Manager</h2>
                <button class="btn-primary" id="btn-create-job-drawer" style="margin: 0; padding: 0.6rem 1.2rem; display: flex; align-items: center; gap: 0.5rem;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg> Publish Recruitment</button>
            </div>

            <div id="jobs-datatable"></div>
        </section>

        <!-- ================= PANEL 3: CRAWLER TARGET CONFIGS ================= -->
        <section class="admin-panel-block" id="admin-crawlers" style="display: none;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="font-family: 'Outfit'; font-size: 1.75rem; margin: 0;">Web Crawler Monitor Profiles</h2>
                <button class="btn-primary" id="btn-create-crawler-drawer" style="margin: 0; padding: 0.6rem 1.2rem; display: flex; align-items: center; gap: 0.5rem;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg> Add Scraper Target</button>
            </div>

            <div id="crawlers-management-datatable"></div>
        </section>

        <!-- ================= PANEL 4: MASTER DATA MANAGER ================= -->
        <section class="admin-panel-block" id="admin-master" style="display: none;">
            <div id="react-master-data-root"></div>
            @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
                @viteReactRefresh
                @vite('resources/js/master-data.jsx')
            @else
                <div class="glass-panel p-6 m-4 text-center text-red-600 rounded-lg">
                    <p class="font-bold mb-2">Build Assets Missing</p>
                    <p class="text-sm">Please run <code>npm install && npm run build</code> to compile the React assets for the Master Data module.</p>
                </div>
            @endif
        </section>

        <!-- ================= PANEL 5: USER ACCESS PANEL ================= -->
        <section class="admin-panel-block" id="admin-users" style="display: none;">
            <h2 style="font-family: 'Outfit'; font-size: 1.75rem; margin-bottom: 1.5rem;">User Access Registry</h2>
            <div id="users-datatable"></div>
        </section>

        <!-- ================= PANEL 6: SETTINGS MANAGEMENT MODULE ================= -->
        <section class="admin-panel-block" id="admin-settings" style="display: none;">
            <h2 style="font-family: 'Outfit'; font-size: 1.75rem; margin-bottom: 1.5rem;">Global Settings Console</h2>
            
            <!-- Settings Sub-Navigation Tabs -->
            <div class="sub-tab-headers" style="margin-bottom: 2rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                @if(feature_enabled('settings.site'))
                    <button class="sub-tab-btn active settings-sub-trigger" data-target="settings-site">Site Configs</button>
                @endif
                @if(feature_enabled('settings.layout'))
                    <button class="sub-tab-btn settings-sub-trigger" data-target="settings-layout">Look & Layout</button>
                @endif
                @if(feature_enabled('settings.operations'))
                    <button class="sub-tab-btn settings-sub-trigger" data-target="settings-operations">Operations & CMS</button>
                @endif
                @if(feature_enabled('settings.integrations'))
                    <button class="sub-tab-btn settings-sub-trigger" data-target="settings-integrations">SMTP & APIs</button>
                @endif
                @if(feature_enabled('settings.security'))
                    <button class="sub-tab-btn settings-sub-trigger" data-target="settings-security">Security</button>
                @endif
                @if(feature_enabled('settings.media'))
                    <button class="sub-tab-btn settings-sub-trigger" data-target="settings-media">Media Manager</button>
                @endif
            </div>

            @if(feature_enabled('settings.site'))
            <!-- SUB-PANEL 1: SITE CONFIGS -->
            <div class="settings-sub-panel active" id="settings-site">
                <div class="settings-responsive-grid">
                    
                    <!-- General settings form -->
                    <div class="glass-panel" style="padding: 1.5rem; border-radius: 16px;">
                        <h3 style="font-family:'Outfit'; font-size:1.25rem; margin-bottom:1.5rem; color:var(--accent-color);">General Properties</h3>
                        <form id="settings-general-form">
                            @csrf
                            <div class="form-row-grid">
                                <div class="form-group">
                                    <label for="cfg-website-name">Website Name</label>
                                    <input type="text" id="cfg-website-name" name="website_name" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="cfg-website-title">Website Title</label>
                                    <input type="text" id="cfg-website-title" name="website_title" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="cfg-website-tagline">Website Tagline</label>
                                <input type="text" id="cfg-website-tagline" name="website_tagline" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="cfg-website-description">Website Description</label>
                                <textarea id="cfg-website-description" name="website_description" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="cfg-website-keywords">Keywords (comma separated)</label>
                                <input type="text" id="cfg-website-keywords" name="website_keywords" class="form-control">
                            </div>
                            <div class="form-row-grid">
                                <div class="form-group">
                                    <label for="cfg-website-author">Website Author</label>
                                    <input type="text" id="cfg-website-author" name="website_author" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="cfg-copyright-text">Copyright Text</label>
                                    <input type="text" id="cfg-copyright-text" name="copyright_text" class="form-control" required>
                                </div>
                            </div>
                            
                            <div class="form-row-grid-three" style="margin-top: 1rem;">
                                <div class="form-group">
                                    <label for="cfg-timezone">Timezone</label>
                                    <select id="cfg-timezone" name="timezone" class="form-control">
                                        <option value="Asia/Kolkata">Asia/Kolkata (IST)</option>
                                        <option value="UTC">UTC</option>
                                        <option value="America/New_York">EST/EDT</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="cfg-date-format">Date Format</label>
                                    <select id="cfg-date-format" name="date_format" class="form-control">
                                        <option value="d M Y">d M Y (e.g. 07 Jun 2026)</option>
                                        <option value="Y-m-d">Y-m-d (e.g. 2026-06-07)</option>
                                        <option value="m/d/Y">m/d/Y</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="cfg-currency">Currency Symbol</label>
                                    <input type="text" id="cfg-currency" name="currency" class="form-control" required>
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem;">
                                <div class="form-group">
                                    <label for="cfg-language">Default Language</label>
                                    <select id="cfg-language" name="language" class="form-control">
                                        <option value="en">English (EN)</option>
                                        <option value="hi">Hindi (हिन्दी)</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="cfg-maintenance-mode">Maintenance Mode</label>
                                    <select id="cfg-maintenance-mode" name="maintenance_mode" class="form-control">
                                        <option value="0">Disabled (Site Live)</option>
                                        <option value="1">Enabled (Block Traffic)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group" id="maintenance-msg-group" style="display:none;">
                                <label for="cfg-maintenance-message">Maintenance Message</label>
                                <textarea id="cfg-maintenance-message" name="maintenance_message" class="form-control" rows="2"></textarea>
                            </div>
                            
                            <div class="divider" style="margin: 1.5rem 0;"></div>
                            
                            <!-- Notification Configurations -->
                            <h4 style="font-family:'Outfit'; font-size:1.1rem; margin-bottom:1rem; color:var(--accent-color);">Notification Configurations</h4>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                                <div class="form-group">
                                    <label for="cfg-email-notifications">Email Alerts</label>
                                    <select id="cfg-email-notifications" name="email_notifications" class="form-control">
                                        <option value="1">Enabled</option>
                                        <option value="0">Disabled</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="cfg-push-notifications">Push Notifications</label>
                                    <select id="cfg-push-notifications" name="push_notifications" class="form-control">
                                        <option value="1">Enabled</option>
                                        <option value="0">Disabled</option>
                                    </select>
                                </div>
                            </div>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                                <div class="form-group">
                                    <label for="cfg-admin-notifications">Admin Activity Logging</label>
                                    <select id="cfg-admin-notifications" name="admin_notifications" class="form-control">
                                        <option value="1">Enabled</option>
                                        <option value="0">Disabled</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="cfg-user-notifications">User Interaction Alerts</label>
                                    <select id="cfg-user-notifications" name="user_notifications" class="form-control">
                                        <option value="1">Enabled</option>
                                        <option value="0">Disabled</option>
                                    </select>
                                </div>
                            </div>

                            <div class="divider" style="margin: 1.5rem 0;"></div>
                            
                            <!-- Contact Settings -->
                            <h4 style="font-family:'Outfit'; font-size:1.1rem; margin-bottom:1rem; color:var(--accent-color);">Contact Details</h4>
                            <div class="form-row-grid">
                                <div class="form-group">
                                    <label for="cfg-contact-email">Website Contact Email</label>
                                    <input type="email" id="cfg-contact-email" name="website_contact_email" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="cfg-contact-mobile">Contact Mobile</label>
                                    <input type="text" id="cfg-contact-mobile" name="website_contact_mobile" class="form-control">
                                </div>
                            </div>
                            <div class="form-row-grid">
                                <div class="form-group">
                                    <label for="cfg-support-email">Support Email</label>
                                    <input type="email" id="cfg-support-email" name="support_email" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="cfg-support-phone">Support Phone</label>
                                    <input type="text" id="cfg-support-phone" name="support_phone" class="form-control">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="cfg-office-address">Office Address</label>
                                <textarea id="cfg-office-address" name="office_address" class="form-control" rows="2"></textarea>
                            </div>

                            <button type="submit" class="form-btn" style="margin-top:1.5rem;">Save Site Properties</button>
                        </form>
                    </div>

                    <!-- Logo & Favicon Uploader column -->
                    <div style="display:flex; flex-direction:column; gap:1.5rem;">
                        <div class="glass-panel" style="padding: 1.5rem; border-radius: 16px;">
                            <h3 style="font-family:'Outfit'; font-size:1.15rem; margin-bottom:1rem; color:var(--accent-color);">Logos & Graphics</h3>
                            
                            <!-- Main Logo upload -->
                            <div style="margin-bottom:1.5rem; border-bottom: 1px dashed var(--border-color); padding-bottom:1rem;">
                                <div style="font-size:0.85rem; font-weight:600; margin-bottom:0.5rem;">Header Logo</div>
                                <div style="margin-bottom:0.5rem; display:flex; align-items:center; justify-content:center; height:60px; background:rgba(0,0,0,0.1); border-radius:6px; overflow:hidden;">
                                    <img id="preview-header_logo" src="" alt="Header Logo Preview" style="max-height:45px; display:none;">
                                    <span id="no-header_logo" style="font-size:0.75rem; color:var(--text-secondary);">No logo set</span>
                                </div>
                                <input type="file" class="logo-upload-input" data-key="header_logo" style="font-size:0.8rem; width:100%;">
                            </div>

                            <!-- Footer Logo upload -->
                            <div style="margin-bottom:1.5rem; border-bottom: 1px dashed var(--border-color); padding-bottom:1rem;">
                                <div style="font-size:0.85rem; font-weight:600; margin-bottom:0.5rem;">Footer Logo</div>
                                <div style="margin-bottom:0.5rem; display:flex; align-items:center; justify-content:center; height:60px; background:rgba(0,0,0,0.1); border-radius:6px; overflow:hidden;">
                                    <img id="preview-footer_logo" src="" alt="Footer Logo Preview" style="max-height:45px; display:none;">
                                    <span id="no-footer_logo" style="font-size:0.75rem; color:var(--text-secondary);">No logo set</span>
                                </div>
                                <input type="file" class="logo-upload-input" data-key="footer_logo" style="font-size:0.8rem; width:100%;">
                            </div>

                            <!-- Favicon upload -->
                            <div>
                                <div style="font-size:0.85rem; font-weight:600; margin-bottom:0.5rem;">Browser Favicon (16x16 / 32x32)</div>
                                <div style="margin-bottom:0.5rem; display:flex; align-items:center; justify-content:center; height:50px; background:rgba(0,0,0,0.1); border-radius:6px;">
                                    <img id="preview-favicon" src="" alt="Favicon Preview" style="width:32px; height:32px; display:none;">
                                    <span id="no-favicon" style="font-size:0.75rem; color:var(--text-secondary);">No favicon set</span>
                                </div>
                                <input type="file" class="logo-upload-input" data-key="favicon" style="font-size:0.8rem; width:100%;">
                            </div>
                        </div>

                        <!-- Social Media Links -->
                        <div class="glass-panel" style="padding: 1.5rem; border-radius: 16px;">
                            <h3 style="font-family:'Outfit'; font-size:1.15rem; margin-bottom:1rem; color:var(--accent-color);">Social Channels</h3>
                            <form id="settings-social-form">
                                @csrf
                                <div style="display:flex; flex-direction:column; gap:0.75rem;" id="social-links-inputs">
                                    <!-- Rendered dynamically -->
                                </div>
                                <button type="submit" class="form-btn" style="margin-top:1rem; width:100%;">Save Channels</button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
            @endif

            @if(feature_enabled('settings.layout'))
            <!-- SUB-PANEL 2: LOOK & LAYOUT -->
            <div class="settings-sub-panel" id="settings-layout" style="display: none;">
                <div class="settings-responsive-grid-theme">
                    
                    <!-- Themes and colors -->
                    <div style="display:flex; flex-direction:column; gap:1.5rem;">
                        <div class="glass-panel" style="padding: 1.5rem; border-radius: 16px;">
                            <h3 style="font-family:'Outfit'; font-size:1.25rem; margin-bottom:1.5rem; color:var(--accent-color);">Theme Style Variables</h3>
                            <form id="settings-theme-form">
                                @csrf
                                <div class="form-group">
                                    <label for="theme-primary">Primary / Accent Color</label>
                                    <div style="display:flex; gap:0.5rem;">
                                        <input type="color" id="theme-primary-picker" style="width:50px; height:38px; border:none; padding:0; background:none; cursor:pointer;">
                                        <input type="text" id="theme-primary" name="accent_color" class="form-control" placeholder="#2563eb" required>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="theme-secondary">Secondary Color</label>
                                    <div style="display:flex; gap:0.5rem;">
                                        <input type="color" id="theme-secondary-picker" style="width:50px; height:38px; border:none; padding:0; background:none; cursor:pointer;">
                                        <input type="text" id="theme-secondary" name="secondary_color" class="form-control" placeholder="#1e40af" required>
                                    </div>
                                </div>
                                <div class="form-row-grid">
                                    <div class="form-group">
                                        <label for="theme-bg">Light Bg</label>
                                        <input type="text" id="theme-bg" name="background_color" class="form-control" placeholder="#f8fafc" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="theme-text">Light Text</label>
                                        <input type="text" id="theme-text" name="text_color" class="form-control" placeholder="#0f172a" required>
                                    </div>
                                </div>
                                <div class="form-row-grid">
                                    <div class="form-group">
                                        <label for="theme-dark-primary">Dark Primary</label>
                                        <input type="text" id="theme-dark-primary" name="dark_primary_color" class="form-control" placeholder="#3b82f6" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="theme-dark-bg">Dark Bg</label>
                                        <input type="text" id="theme-dark-bg" name="dark_background_color" class="form-control" placeholder="#090d16" required>
                                    </div>
                                </div>
                                <button type="submit" class="form-btn" style="margin-top:1rem;">Apply Global Colors</button>
                            </form>
                        </div>
                        
                        <!-- Homepage sections CMS toggling -->
                        <div class="glass-panel" style="padding: 1.5rem; border-radius: 16px;">
                            <h3 style="font-family:'Outfit'; font-size:1.15rem; margin-bottom:1rem; color:var(--accent-color);">Homepage CMS Layout</h3>
                            <p style="font-size:0.8rem; color:var(--text-secondary); margin-bottom:1.2rem;">Enable or disable layout columns dynamically.</p>
                            
                            <div style="display:flex; flex-direction:column; gap:0.75rem;" id="homepage-sections-toggles">
                                <!-- Loaded dynamically or rendered with fallbacks -->
                                <div style="display:flex; justify-content:space-between; align-items:center; background:var(--bg-primary); padding:0.6rem 1rem; border-radius:8px; border:1px solid var(--border-color);">
                                    <span style="font-size:0.85rem; font-weight:600;">Hero Banner Section</span>
                                    <label class="custom-switch" style="position: relative; display: inline-block; width: 44px; height: 24px;">
                                        <input type="checkbox" class="section-toggle-check" data-section="hero" checked style="opacity: 0; width: 0; height: 0;">
                                        <span class="slider-switch" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: var(--border-color); transition: .3s; border-radius: 24px;"></span>
                                    </label>
                                </div>
                                <div style="display:flex; justify-content:space-between; align-items:center; background:var(--bg-primary); padding:0.6rem 1rem; border-radius:8px; border:1px solid var(--border-color);">
                                    <span style="font-size:0.85rem; font-weight:600;">Latest Ticker Notices</span>
                                    <label class="custom-switch" style="position: relative; display: inline-block; width: 44px; height: 24px;">
                                        <input type="checkbox" class="section-toggle-check" data-section="ticker" checked style="opacity: 0; width: 0; height: 0;">
                                        <span class="slider-switch" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: var(--border-color); transition: .3s; border-radius: 24px;"></span>
                                    </label>
                                </div>
                                <div style="display:flex; justify-content:space-between; align-items:center; background:var(--bg-primary); padding:0.6rem 1rem; border-radius:8px; border:1px solid var(--border-color);">
                                    <span style="font-size:0.85rem; font-weight:600;">Trending Hot Card Guides</span>
                                    <label class="custom-switch" style="position: relative; display: inline-block; width: 44px; height: 24px;">
                                        <input type="checkbox" class="section-toggle-check" data-section="trending" checked style="opacity: 0; width: 0; height: 0;">
                                        <span class="slider-switch" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: var(--border-color); transition: .3s; border-radius: 24px;"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Header/Footer Menu Manager -->
                    <div class="glass-panel" style="padding: 1.5rem; border-radius: 16px;">
                        <h3 style="font-family:'Outfit'; font-size:1.25rem; margin-bottom:1rem; color:var(--accent-color);">Menu & Navigation Builder</h3>
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem; background:var(--bg-primary); padding:0.5rem; border-radius:6px;">
                            <select id="menu-select-selector" style="width: auto; font-size:0.85rem; margin:0; padding:0.4rem 0.8rem;">
                                <option value="1">Header Menu</option>
                                <option value="2">Footer Column 1 (Quick Links)</option>
                                <option value="3">Footer Column 2 (Useful Links)</option>
                            </select>
                            <button class="btn-primary" id="btn-add-menu-item-modal" style="font-size:0.8rem; margin:0; padding:0.4rem 0.8rem;">+ Add Item</button>
                        </div>
                        
                        <!-- Navigation items list wrapper -->
                        <div style="border:1px solid var(--border-color); border-radius:8px; padding:0.5rem; min-height:200px; max-height:400px; overflow-y:auto; background:rgba(0,0,0,0.08);" id="menu-items-sortable-list">
                            <!-- Populated dynamically -->
                        </div>
                    </div>

                </div>
            </div>
            @endif

            @if(feature_enabled('settings.operations'))
            <!-- SUB-PANEL 3: OPERATIONS & CMS -->
            <div class="settings-sub-panel" id="settings-operations" style="display: none;">
                <div class="settings-responsive-grid-ops">
                    
                    <!-- CMS Pages list & creator -->
                    <div class="glass-panel" style="padding: 1.5rem; border-radius: 16px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
                            <h3 style="font-family:'Outfit'; font-size:1.25rem; margin:0; color:var(--accent-color);">Custom Static Pages</h3>
                            <button class="btn-primary" id="btn-create-cms-page" style="font-size:0.8rem; margin:0; padding:0.4rem 0.8rem;">+ Create Page</button>
                        </div>
                        
                        <div id="cms-pages-datatable"></div>
                    </div>

                    <!-- Advertisements Scheduler -->
                    <div class="glass-panel" style="padding: 1.5rem; border-radius: 16px;">
                        <h3 style="font-family:'Outfit'; font-size:1.25rem; margin-bottom:1rem; color:var(--accent-color);">Monetization Ad Slots</h3>
                        <p style="font-size:0.8rem; color:var(--text-secondary); margin-bottom:1.5rem;">Configure ad scripts or campaign graphics for monetization spots.</p>
                        
                        <div style="display:flex; flex-direction:column; gap:1.2rem;" id="ad-slots-container">
                            <!-- Populated dynamically -->
                        </div>
                    </div>

                </div>
            </div>
            @endif

            @if(feature_enabled('settings.integrations'))
            <!-- SUB-PANEL 4: SMTP & APIS -->
            <div class="settings-sub-panel" id="settings-integrations" style="display: none;">
                <div class="settings-responsive-grid-ops">
                    
                    <!-- SMTP configuration -->
                    <div class="glass-panel" style="padding: 1.5rem; border-radius: 16px;">
                        <h3 style="font-family:'Outfit'; font-size:1.25rem; margin-bottom:1.5rem; color:var(--accent-color);">SMTP Mail Setup</h3>
                        <form id="settings-smtp-form">
                            @csrf
                            <div class="form-row-grid">
                                <div class="form-group">
                                    <label for="smtp-host">SMTP Server Host</label>
                                    <input type="text" id="smtp-host" name="smtp_host" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="smtp-port">Port</label>
                                    <input type="number" id="smtp-port" name="smtp_port" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-row-grid">
                                <div class="form-group">
                                    <label for="smtp-username">SMTP Username</label>
                                    <input type="text" id="smtp-username" name="smtp_username" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="smtp-password">SMTP Password</label>
                                    <input type="password" id="smtp-password" name="smtp_password" class="form-control" placeholder="••••••••">
                                </div>
                            </div>
                            <div class="form-row-grid">
                                <div class="form-group">
                                    <label for="smtp-encryption">Encryption Protocol</label>
                                    <select id="smtp-encryption" name="smtp_encryption" class="form-control">
                                        <option value="">None</option>
                                        <option value="tls">TLS</option>
                                        <option value="ssl">SSL</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="smtp-sender-email">Sender Mail</label>
                                    <input type="email" id="smtp-sender-email" name="sender_email" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="smtp-sender-name">Sender Name / App Signature</label>
                                <input type="text" id="smtp-sender-name" name="sender_name" class="form-control" required>
                            </div>

                            <div class="divider" style="margin: 1.5rem 0;"></div>
                            
                            <!-- Test Connection panel -->
                            <div style="background:rgba(0,0,0,0.06); padding:1rem; border-radius:10px; border:1px dashed var(--border-color);">
                                <h4 style="font-family:'Outfit'; font-size:0.95rem; margin-bottom:0.5rem;">Verify Dispatch Capabilities</h4>
                                <div style="display:flex; gap:0.5rem; align-items:center;">
                                    <input type="email" id="smtp-test-recipient" placeholder="test@recipient.com" class="form-control" style="margin:0; flex:1;">
                                    <button type="button" class="btn-success" id="btn-trigger-smtp-test" style="margin:0; padding:0.6rem 1rem;">Test Connection</button>
                                </div>
                            </div>
                            
                            <button type="submit" class="form-btn" style="margin-top:1.5rem; width:100%;">Save SMTP Configs</button>
                        </form>
                    </div>

                    <!-- API Keys configuration and script injection -->
                    <div style="display:flex; flex-direction:column; gap:1.5rem;">
                        <div class="glass-panel" style="padding: 1.5rem; border-radius: 16px;">
                            <h3 style="font-family:'Outfit'; font-size:1.15rem; margin-bottom:1rem; color:var(--accent-color);">Third-Party API Keys</h3>
                            <form id="settings-api-form">
                                @csrf
                                <div class="form-group">
                                    <label for="api-google">Google API Credentials</label>
                                    <input type="password" id="api-google" name="google_api_keys" class="form-control" placeholder="••••••••">
                                </div>
                                <div class="form-group">
                                    <label for="api-maps">Google Maps API Key</label>
                                    <input type="password" id="api-maps" name="maps_api" class="form-control" placeholder="••••••••">
                                </div>
                                <div class="form-group">
                                    <label for="api-sms">SMS Gateway Authentication</label>
                                    <input type="password" id="api-sms" name="sms_gateway_api" class="form-control" placeholder="••••••••">
                                </div>
                                <div class="form-group">
                                    <label for="api-whatsapp">WhatsApp Gateway Key</label>
                                    <input type="password" id="api-whatsapp" name="whatsapp_api" class="form-control" placeholder="••••••••">
                                </div>
                                <button type="submit" class="form-btn" style="margin-top:1rem; width:100%;">Save Encrypted API Keys</button>
                            </form>
                        </div>
                        
                        <div class="glass-panel" style="padding: 1.5rem; border-radius: 16px;">
                            <h3 style="font-family:'Outfit'; font-size:1.15rem; margin-bottom:1rem; color:var(--accent-color);">Custom Code Injection</h3>
                            <form id="settings-scripts-form">
                                @csrf
                                <div class="form-group">
                                    <label for="script-header">Header Injections (Google Analytics / Pixel)</label>
                                    <textarea id="script-header" name="header_scripts" class="form-control" rows="3" placeholder="<script>...</script>"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="script-footer">Footer Injections (Custom JS)</label>
                                    <textarea id="script-footer" name="footer_scripts" class="form-control" rows="3" placeholder="<script>...</script>"></textarea>
                                </div>
                                <button type="submit" class="form-btn" style="width:100%;">Save Injection Scripts</button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
            @endif

            @if(feature_enabled('settings.security'))
            <!-- SUB-PANEL 5: SECURITY & BACKUPS -->
            <div class="settings-sub-panel" id="settings-security" style="display: none;">
                <div class="settings-responsive-grid-ops">
                    
                    <!-- Security properties -->
                    <div class="glass-panel" style="padding: 1.5rem; border-radius: 16px;">
                        <h3 style="font-family:'Outfit'; font-size:1.25rem; margin-bottom:1.5rem; color:var(--accent-color);">Security Policy Settings</h3>
                        <form id="settings-security-form">
                            @csrf
                            <div class="form-row-grid">
                                <div class="form-group">
                                    <label for="sec-session">Session Timeout (minutes)</label>
                                    <input type="number" id="sec-session" name="session_timeout" class="form-control" value="120" required>
                                </div>
                                <div class="form-group">
                                    <label for="sec-attempts">Login Attempt Limits</label>
                                    <input type="number" id="sec-attempts" name="login_attempt_limit" class="form-control" value="5" required>
                                </div>
                            </div>
                            
                            <div class="divider" style="margin: 1rem 0;"></div>
                            
                            <div style="display:flex; flex-direction:column; gap:1rem;">
                                <div style="display:flex; justify-content:space-between; align-items:center;">
                                    <div>
                                        <div style="font-size:0.88rem; font-weight:600;">Enable Recaptcha Protection</div>
                                        <span style="font-size:0.75rem; color:var(--text-secondary);">Blocks bot registration requests</span>
                                    </div>
                                    <label class="custom-switch" style="position: relative; display: inline-block; width: 44px; height: 24px;">
                                        <input type="checkbox" id="sec-captcha-enable" name="captcha_enable" value="1" style="opacity: 0; width: 0; height: 0;">
                                        <span class="slider-switch" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: var(--border-color); transition: .3s; border-radius: 24px;"></span>
                                    </label>
                                </div>
                                
                                <div style="display:flex; justify-content:space-between; align-items:center;">
                                    <div>
                                        <div style="font-size:0.88rem; font-weight:600;">Two-Factor Authentication</div>
                                        <span style="font-size:0.75rem; color:var(--text-secondary);">Force OTP check for admin access</span>
                                    </div>
                                    <label class="custom-switch" style="position: relative; display: inline-block; width: 44px; height: 24px;">
                                        <input type="checkbox" id="sec-2fa-enable" name="two_factor_auth" value="1" style="opacity: 0; width: 0; height: 0;">
                                        <span class="slider-switch" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: var(--border-color); transition: .3s; border-radius: 24px;"></span>
                                    </label>
                                </div>
                            </div>

                            <button type="submit" class="form-btn" style="margin-top:1.5rem; width:100%;">Save Security Policies</button>
                        </form>
                    </div>

                    @if(feature_enabled('settings.security.backups'))
                    <!-- Database Backups -->
                    <div class="glass-panel" style="padding: 1.5rem; border-radius: 16px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
                            <h3 style="font-family:'Outfit'; font-size:1.25rem; margin:0; color:var(--accent-color);">System SQL Backups</h3>
                            <button class="btn-primary" id="btn-trigger-backup" style="font-size:0.8rem; margin:0; padding:0.4rem 0.8rem;">Generate Backup</button>
                        </div>
                        <div id="backups-datatable"></div>
                    </div>
                    @endif

                </div>
            </div>
            @endif

            @if(feature_enabled('settings.media'))
            <!-- SUB-PANEL 6: MEDIA MANAGER -->
            <div class="settings-sub-panel" id="settings-media" style="display: none;">
                <div class="glass-panel" style="padding: 1.5rem; border-radius: 16px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; gap:1rem; flex-wrap:wrap; margin-bottom:1.5rem; padding-bottom:1rem; border-bottom:1px solid var(--border-color);">
                        <h3 style="font-family:'Outfit'; font-size:1.25rem; margin:0; color:var(--accent-color);">File & Graphics Explorer</h3>
                        
                        <div style="display:flex; gap:0.5rem; align-items:center;">
                            <!-- Directory breadcrumbs -->
                            <div id="media-breadcrumbs" style="font-size:0.85rem; color:var(--text-secondary); background:rgba(0,0,0,0.1); padding:0.4rem 0.8rem; border-radius:6px; font-family:monospace;">
                                uploads/media
                            </div>
                            
                            <!-- Folder maker -->
                            <button class="btn-secondary" id="btn-media-new-folder" style="font-size:0.8rem; margin:0; padding:0.4rem 0.8rem;">+ New Folder</button>
                            
                            <!-- File uploader -->
                            <input type="file" id="media-upload-input" style="display:none;">
                            <button class="btn-primary" id="btn-media-trigger-upload" style="font-size:0.8rem; margin:0; padding:0.4rem 0.8rem;">Upload File</button>
                        </div>
                    </div>

                    <!-- Files grid layout -->
                    <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap:1rem; min-height:250px; max-height:500px; overflow-y:auto; padding:0.5rem; background:rgba(0,0,0,0.08); border-radius:10px;" id="media-files-grid">
                        <!-- Loaded dynamically -->
                    </div>
                </div>
            </div>
            @endif

        </section>

        <!-- ================= PANEL 7: AUDIT ACTIVITY LOGS ================= -->
        <section class="admin-panel-block" id="admin-audit" style="display: none;">
            <h2 style="font-family: 'Outfit'; font-size: 1.75rem; margin-bottom: 1.5rem;">System & Administrative Audit Logs</h2>
            <div id="audit-logs-datatable"></div>
        </section>

        <!-- ================= PANEL 8: QUEUE ENGINE & DLQ MANAGEMENT ================= -->
        <section class="admin-panel-block" id="admin-queues" style="display: none;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="font-family: 'Outfit'; font-size: 1.75rem; margin: 0;">Distributed Queue Control Center</h2>
                <div style="display: flex; gap: 0.75rem;">
                    <button class="btn-success" id="btn-queues-retry-all" style="margin: 0; padding: 0.6rem 1.2rem; border: none; display: flex; align-items: center; gap: 0.5rem;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg> Retry All Failures</button>
                    <button class="btn-danger" id="btn-queues-clear-all" style="margin: 0; padding: 0.6rem 1.2rem; border: none; display: flex; align-items: center; gap: 0.5rem;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg> Flush DLQ Store</button>
                </div>
            </div>

            <!-- Queue Telemetry Stats -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                <div class="glass-panel stat-card-premium" style="border-left: 5px solid var(--accent-color);">
                    <div class="label">Queue Connection Driver</div>
                    <div class="number" id="queues-driver" style="font-size: 1.5rem; text-transform: uppercase;">REDIS</div>
                    <div class="subtext">Multi-worker active driver</div>
                </div>
                <div class="glass-panel stat-card-premium" style="border-left: 5px solid #10b981;">
                    <div class="label">Total Pending Tasks</div>
                    <div class="number" id="queues-pending">0</div>
                    <div class="subtext" id="queues-pending-details">scrapers: 0 | notifications: 0</div>
                </div>
                <div class="glass-panel stat-card-premium" style="border-left: 5px solid #f59e0b;">
                    <div class="label">Active Workers Processing</div>
                    <div class="number" id="queues-active">0</div>
                    <div class="subtext">Active concurrent allocations</div>
                </div>
                <div class="glass-panel stat-card-premium" style="border-left: 5px solid #ef4444;">
                    <div class="label">Dead-Letter Queue Failures</div>
                    <div class="number" id="queues-failed" style="color: #ef4444;">0</div>
                    <div class="subtext">Awaiting manual operations</div>
                </div>
            </div>

            <!-- DLQ failed jobs browser table -->
            <div id="queues-failed-datatable"></div>
        </section>

        <!-- ================= PANEL 8B: EMAIL MARKETING AUTOMATION ================= -->
        <section class="admin-panel-block" id="admin-marketing" style="display: none;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="font-family: 'Outfit'; font-size: 1.75rem; margin: 0;">Marketing & Email Automation Panel</h2>
                <div style="display: flex; gap: 0.75rem; align-items: center;">
                    <span style="font-size: 0.85rem; color: var(--text-secondary);">Active Queue Engine:</span>
                    <span class="badge" style="background: rgba(37, 99, 235, 0.15); color: var(--accent-color); font-weight: 700; padding: 0.4rem 0.8rem; border-radius: 6px; text-transform: uppercase;">DATABASE QUEUE</span>
                </div>
            </div>

            <!-- Aggregated Performance Analytics Cards -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                <div class="glass-panel stat-card-premium" style="border-left: 5px solid var(--accent-color); padding: 1.5rem; border-radius: 12px;">
                    <div class="label" style="font-size:0.85rem; text-transform:uppercase; color:var(--text-secondary); margin-bottom:0.5rem;">Total Emails Queued/Sent</div>
                    <div class="number" id="mkt-stat-sent" style="font-size:2rem; font-weight:bold; font-family:'Outfit';">0</div>
                    <div class="subtext" style="font-size:0.75rem; color:var(--text-secondary); margin-top:0.25rem;">Active automation dispatches</div>
                </div>
                <div class="glass-panel stat-card-premium" style="border-left: 5px solid #10b981; padding: 1.5rem; border-radius: 12px;">
                    <div class="label" style="font-size:0.85rem; text-transform:uppercase; color:var(--text-secondary); margin-bottom:0.5rem;">Average Open Rate</div>
                    <div class="number" id="mkt-stat-open-rate" style="font-size:2rem; font-weight:bold; font-family:'Outfit'; color: #10b981;">0%</div>
                    <div class="subtext" id="mkt-stat-opened" style="font-size:0.75rem; color:var(--text-secondary); margin-top:0.25rem;">0 total opens logged</div>
                </div>
                <div class="glass-panel stat-card-premium" style="border-left: 5px solid #f59e0b; padding: 1.5rem; border-radius: 12px;">
                    <div class="label" style="font-size:0.85rem; text-transform:uppercase; color:var(--text-secondary); margin-bottom:0.5rem;">Click-Through Rate (CTR)</div>
                    <div class="number" id="mkt-stat-ctr" style="font-size:2rem; font-weight:bold; font-family:'Outfit'; color: #f59e0b;">0%</div>
                    <div class="subtext" id="mkt-stat-clicked" style="font-size:0.75rem; color:var(--text-secondary); margin-top:0.25rem;">0 total link clicks logged</div>
                </div>
                <div class="glass-panel stat-card-premium" style="border-left: 5px solid #ef4444; padding: 1.5rem; border-radius: 12px;">
                    <div class="label" style="font-size:0.85rem; text-transform:uppercase; color:var(--text-secondary); margin-bottom:0.5rem;">Delivery Failures (DLQ)</div>
                    <div class="number" id="mkt-stat-failed" style="font-size:2rem; font-weight:bold; font-family:'Outfit'; color: #ef4444;">0</div>
                    <div class="subtext" style="font-size:0.75rem; color:var(--text-secondary); margin-top:0.25rem;">SMTP/Network errors caught</div>
                </div>
            </div>

            <!-- Middle Layout: Campaign breakdown & Test trigger suite -->
            <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 2rem; margin-bottom: 2rem; align-items: start;">
                
                <!-- Campaign Breakdown Performance list -->
                <div class="glass-panel" style="padding: 1.5rem; border-radius: 16px;">
                    <h3 style="font-family: 'Outfit'; font-size: 1.2rem; color: var(--accent-color); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;"><span style="display:inline-block; width:4px; height:18px; background:var(--accent-color); border-radius:2px;"></span> Campaign Performance Breakdown</h3>
                    <div id="mkt-campaigns-list" style="display: flex; flex-direction: column; gap: 1.25rem;">
                        <div style="text-align:center; color:var(--text-secondary); font-size:0.9rem; padding: 2rem 0;">Loading campaign datasets...</div>
                    </div>
                </div>

                <!-- Manual dispatch/testing suite -->
                <div class="glass-panel" style="padding: 1.5rem; border-radius: 16px;">
                    <h3 style="font-family: 'Outfit'; font-size: 1.2rem; color: #f59e0b; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;"><span style="display:inline-block; width:4px; height:18px; background:#f59e0b; border-radius:2px;"></span> Administrative Campaign Testing</h3>
                    <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1.5rem; line-height: 1.4;">
                        Select a campaign type and enter a recipient address to manually queue a high-fidelity test email. Links and open trackers will be fully active.
                    </p>
                    <form id="mkt-test-form">
                        @csrf
                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label for="mkt-test-email" style="display:block; margin-bottom:0.5rem; font-size:0.85rem; font-weight:600;">Recipient Email Address</label>
                            <input type="email" id="mkt-test-email" class="form-control" placeholder="e.g. candidate@example.com" required style="width:100%; box-sizing:border-box;">
                        </div>
                        <div class="form-group" style="margin-bottom: 1.5rem;">
                            <label for="mkt-test-campaign" style="display:block; margin-bottom:0.5rem; font-size:0.85rem; font-weight:600;">Campaign Blueprint Template</label>
                            <select id="mkt-test-campaign" class="form-control" style="width:100%; box-sizing:border-box;">
                                <option value="welcome_1">Welcome Series Part 1: Instant Intro</option>
                                <option value="welcome_2">Welcome Series Part 2: Preference Setup</option>
                                <option value="welcome_3">Welcome Series Part 3: Portal Walkthrough</option>
                                <option value="job_alert">Instant Matched Job Alerts</option>
                                <option value="result_alert">Official Results Declared Notification</option>
                                <option value="admit_card_alert">Hall Ticket / Admit Card Release Notification</option>
                                <option value="weekly_digest">Weekly Careers Collation Digest</option>
                                <option value="re_engagement">Winback Re-engagement (We Miss You!)</option>
                            </select>
                        </div>
                        <button type="submit" class="form-btn" id="mkt-test-submit" style="width:100%; margin:0; background: linear-gradient(135deg, #f59e0b, #d97706); border:none; font-weight:700;">Queue Test Campaign Dispatch</button>
                    </form>
                </div>
            </div>

            <!-- Dynamic logs audit trail -->
            <div id="mkt-logs-datatable"></div>
        </section>

        <!-- ================= PANEL 9: CONTENT VERIFICATION HUB ================= -->
        <section class="admin-panel-block" id="admin-ai-content" style="display: none;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="font-family: 'Outfit'; font-size: 1.75rem; margin: 0;">Content Verification Hub Console</h2>
                <div style="display: flex; gap: 0.75rem; align-items: center;">
                    <span style="font-size: 0.85rem; color: var(--text-secondary);">Active Verification Engine:</span>
                    <span id="ai-telemetry-engine" class="badge" style="background: rgba(37, 99, 235, 0.15); color: var(--accent-color); font-weight: 700; padding: 0.4rem 0.8rem; border-radius: 6px; text-transform: uppercase;">GEMINI</span>
                </div>
            </div>

            <!-- AI Telemetry Stats -->
            <!-- AI Telemetry Stats -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                <div class="glass-panel stat-card-premium" style="border-left: 5px solid var(--accent-color);">
                    <div class="label">Total Enriched Posts</div>
                    <div class="number" id="ai-stat-total">0</div>
                    <div class="subtext">Completed or pending verification</div>
                </div>
                <div class="glass-panel stat-card-premium" style="border-left: 5px solid #f59e0b;">
                    <div class="label">Pending Approvals</div>
                    <div class="number" id="ai-stat-pending" style="color: #f59e0b;">0</div>
                    <div class="subtext">Awaiting administrative validation</div>
                </div>
                <div class="glass-panel stat-card-premium" style="border-left: 5px solid #10b981;">
                    <div class="label">Approved & Public</div>
                    <div class="number" id="ai-stat-approved" style="color: #10b981;">0</div>
                    <div class="subtext">Enriching SEO details live</div>
                </div>
                <div class="glass-panel stat-card-premium" style="border-left: 5px solid #ef4444;">
                    <div class="label">Rejected Variants</div>
                    <div class="number" id="ai-stat-rejected" style="color: #ef4444;">0</div>
                    <div class="subtext">Declined drafts</div>
                </div>
            </div>

            <div id="ai-management-datatable"></div>
        </section>

        <!-- ================= PANEL 10: RBAC MATRIX ================= -->
        <section class="admin-panel-block" id="admin-rbac" style="display: none;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="font-family: 'Outfit'; font-size: 1.75rem; margin: 0;">RBAC Clearance & Privilege Matrix</h2>
                <span class="badge" style="background: rgba(16, 185, 129, 0.1); color: #10b981; font-weight: 700; padding: 0.4rem 0.8rem; border-radius: 6px;">Enterprise RBAC Active</span>
            </div>

            <!-- Access Control Matrix Premium Table -->
            <div class="glass-panel" style="padding: 2rem; border-radius: 16px;">
                <p style="color: var(--text-secondary); margin-bottom: 1.5rem; font-size: 0.95rem; line-height: 1.5;">
                    The following matrix defines the granular access levels and permissions granted to each administrative role in the system. Security settings are dynamically enforced by Spatie Permission Package.
                </p>
                <div class="responsive-table-container">
                    <table class="enterprise-table density-compact" style="width: 100%;">
                        <thead>
                            <tr>
                                <th style="text-align: left; width: 35%;">Administrative Permission</th>
                                <th style="text-align: center;">Super Admin</th>
                                <th style="text-align: center;">Admin</th>
                                <th style="text-align: center;">Editor</th>
                                <th style="text-align: center;">Reviewer</th>
                                <th style="text-align: center;">Moderator</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>View Dashboard Analytics</strong> <br><small style="color: var(--text-secondary);">Access administrative landing metrics</small></td>
                                <td style="text-align: center; color: #10b981; font-weight: bold; font-size: 1.2rem;">✔</td>
                                <td style="text-align: center; color: #10b981; font-weight: bold; font-size: 1.2rem;">✔</td>
                                <td style="text-align: center; color: #10b981; font-weight: bold; font-size: 1.2rem;">✔</td>
                                <td style="text-align: center; color: #10b981; font-weight: bold; font-size: 1.2rem;">✔</td>
                                <td style="text-align: center; color: #10b981; font-weight: bold; font-size: 1.2rem;">✔</td>
                            </tr>
                            <tr>
                                <td><strong>View Admin Audit Logs</strong> <br><small style="color: var(--text-secondary);">Access system audit trail</small></td>
                                <td style="text-align: center; color: #10b981; font-weight: bold; font-size: 1.2rem;">✔</td>
                                <td style="text-align: center; color: #10b981; font-weight: bold; font-size: 1.2rem;">✔</td>
                                <td style="text-align: center; color: #ef4444; font-weight: bold; font-size: 1.2rem;">✘</td>
                                <td style="text-align: center; color: #ef4444; font-weight: bold; font-size: 1.2rem;">✘</td>
                                <td style="text-align: center; color: #ef4444; font-weight: bold; font-size: 1.2rem;">✘</td>
                            </tr>
                            <tr>
                                <td><strong>Manage Queues & DLQ</strong> <br><small style="color: var(--text-secondary);">Flush DLQ or retry failed queue jobs</small></td>
                                <td style="text-align: center; color: #10b981; font-weight: bold; font-size: 1.2rem;">✔</td>
                                <td style="text-align: center; color: #10b981; font-weight: bold; font-size: 1.2rem;">✔</td>
                                <td style="text-align: center; color: #ef4444; font-weight: bold; font-size: 1.2rem;">✘</td>
                                <td style="text-align: center; color: #ef4444; font-weight: bold; font-size: 1.2rem;">✘</td>
                                <td style="text-align: center; color: #ef4444; font-weight: bold; font-size: 1.2rem;">✘</td>
                            </tr>
                            <tr>
                                <td><strong>Manage System Users</strong> <br><small style="color: var(--text-secondary);">Alter roles or suspend user accounts</small></td>
                                <td style="text-align: center; color: #10b981; font-weight: bold; font-size: 1.2rem;">✔</td>
                                <td style="text-align: center; color: #10b981; font-weight: bold; font-size: 1.2rem;">✔</td>
                                <td style="text-align: center; color: #ef4444; font-weight: bold; font-size: 1.2rem;">✘</td>
                                <td style="text-align: center; color: #ef4444; font-weight: bold; font-size: 1.2rem;">✘</td>
                                <td style="text-align: center; color: #ef4444; font-weight: bold; font-size: 1.2rem;">✘</td>
                            </tr>
                            <tr>
                                <td><strong>View Job Registry</strong> <br><small style="color: var(--text-secondary);">Browse active and draft recruitments</small></td>
                                <td style="text-align: center; color: #10b981; font-weight: bold; font-size: 1.2rem;">✔</td>
                                <td style="text-align: center; color: #10b981; font-weight: bold; font-size: 1.2rem;">✔</td>
                                <td style="text-align: center; color: #10b981; font-weight: bold; font-size: 1.2rem;">✔</td>
                                <td style="text-align: center; color: #10b981; font-weight: bold; font-size: 1.2rem;">✔</td>
                                <td style="text-align: center; color: #10b981; font-weight: bold; font-size: 1.2rem;">✔</td>
                            </tr>
                            <tr>
                                <td><strong>Create Recruitment Posts</strong> <br><small style="color: var(--text-secondary);">Add new job postings</small></td>
                                <td style="text-align: center; color: #10b981; font-weight: bold; font-size: 1.2rem;">✔</td>
                                <td style="text-align: center; color: #10b981; font-weight: bold; font-size: 1.2rem;">✔</td>
                                <td style="text-align: center; color: #10b981; font-weight: bold; font-size: 1.2rem;">✔</td>
                                <td style="text-align: center; color: #ef4444; font-weight: bold; font-size: 1.2rem;">✘</td>
                                <td style="text-align: center; color: #ef4444; font-weight: bold; font-size: 1.2rem;">✘</td>
                            </tr>
                            <tr>
                                <td><strong>Edit Recruitment Posts</strong> <br><small style="color: var(--text-secondary);">Modify details of job postings</small></td>
                                <td style="text-align: center; color: #10b981; font-weight: bold; font-size: 1.2rem;">✔</td>
                                <td style="text-align: center; color: #10b981; font-weight: bold; font-size: 1.2rem;">✔</td>
                                <td style="text-align: center; color: #10b981; font-weight: bold; font-size: 1.2rem;">✔</td>
                                <td style="text-align: center; color: #ef4444; font-weight: bold; font-size: 1.2rem;">✘</td>
                                <td style="text-align: center; color: #ef4444; font-weight: bold; font-size: 1.2rem;">✘</td>
                            </tr>
                            <tr>
                                <td><strong>Delete Recruitment Posts</strong> <br><small style="color: var(--text-secondary);">Delete existing job announcements</small></td>
                                <td style="text-align: center; color: #10b981; font-weight: bold; font-size: 1.2rem;">✔</td>
                                <td style="text-align: center; color: #10b981; font-weight: bold; font-size: 1.2rem;">✔</td>
                                <td style="text-align: center; color: #ef4444; font-weight: bold; font-size: 1.2rem;">✘</td>
                                <td style="text-align: center; color: #ef4444; font-weight: bold; font-size: 1.2rem;">✘</td>
                                <td style="text-align: center; color: #ef4444; font-weight: bold; font-size: 1.2rem;">✘</td>
                            </tr>
                            <tr>
                                <td><strong>View AI Content Registry</strong> <br><small style="color: var(--text-secondary);">View draft or approved AI generated content</small></td>
                                <td style="text-align: center; color: #10b981; font-weight: bold; font-size: 1.2rem;">✔</td>
                                <td style="text-align: center; color: #10b981; font-weight: bold; font-size: 1.2rem;">✔</td>
                                <td style="text-align: center; color: #10b981; font-weight: bold; font-size: 1.2rem;">✔</td>
                                <td style="text-align: center; color: #10b981; font-weight: bold; font-size: 1.2rem;">✔</td>
                                <td style="text-align: center; color: #10b981; font-weight: bold; font-size: 1.2rem;">✔</td>
                            </tr>
                            <tr>
                                <td><strong>Generate AI Content</strong> <br><small style="color: var(--text-secondary);">Trigger AI copy generation workflows</small></td>
                                <td style="text-align: center; color: #10b981; font-weight: bold; font-size: 1.2rem;">✔</td>
                                <td style="text-align: center; color: #10b981; font-weight: bold; font-size: 1.2rem;">✔</td>
                                <td style="text-align: center; color: #10b981; font-weight: bold; font-size: 1.2rem;">✔</td>
                                <td style="text-align: center; color: #ef4444; font-weight: bold; font-size: 1.2rem;">✘</td>
                                <td style="text-align: center; color: #10b981; font-weight: bold; font-size: 1.2rem;">✔</td>
                            </tr>
                            <tr>
                                <td><strong>Approve/Reject AI Content</strong> <br><small style="color: var(--text-secondary);">Publish or reject draft copies</small></td>
                                <td style="text-align: center; color: #10b981; font-weight: bold; font-size: 1.2rem;">✔</td>
                                <td style="text-align: center; color: #10b981; font-weight: bold; font-size: 1.2rem;">✔</td>
                                <td style="text-align: center; color: #10b981; font-weight: bold; font-size: 1.2rem;">✔</td>
                                <td style="text-align: center; color: #10b981; font-weight: bold; font-size: 1.2rem;">✔</td>
                                <td style="text-align: center; color: #ef4444; font-weight: bold; font-size: 1.2rem;">✘</td>
                            </tr>
                            <tr>
                                <td><strong>Manage SEO Cache</strong> <br><small style="color: var(--text-secondary);">Update global dynamic SEO metadata</small></td>
                                <td style="text-align: center; color: #10b981; font-weight: bold; font-size: 1.2rem;">✔</td>
                                <td style="text-align: center; color: #10b981; font-weight: bold; font-size: 1.2rem;">✔</td>
                                <td style="text-align: center; color: #10b981; font-weight: bold; font-size: 1.2rem;">✔</td>
                                <td style="text-align: center; color: #ef4444; font-weight: bold; font-size: 1.2rem;">✘</td>
                                <td style="text-align: center; color: #ef4444; font-weight: bold; font-size: 1.2rem;">✘</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
</div>

<!-- =================================================================== -->
<!-- ======================= INTERACTIVE DRAWERS ======================= -->
<!-- =================================================================== -->

<!-- Drawer Backdrop Overlay -->
<div class="drawer-backdrop" id="admin-drawer-backdrop" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); z-index: 1000; transition: opacity 0.3s ease;"></div>

<!-- A. Job Posting Form Slide-out Drawer -->
<div class="admin-drawer glass-panel" id="job-post-drawer" style="position: fixed; right: -730px; top: 0; width: 700px; height: 100vh; background: var(--bg-secondary); border-left: 1px solid var(--border-color); z-index: 1001; transition: right 0.4s cubic-bezier(0.16, 1, 0.3, 1); padding: 2rem; overflow-y: auto; box-shadow: -10px 0 30px rgba(0,0,0,0.15);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
        <h3 id="job-drawer-title" style="font-family: 'Outfit'; font-size: 1.4rem; color: var(--accent-color); margin: 0;">Publish Announcement</h3>
        <button class="btn-sm-danger" id="close-job-drawer" style="padding: 0.25rem 0.5rem; cursor: pointer;">&times; Close</button>
    </div>

    <form id="ajax-job-drawer-form">
        @csrf
        <input type="hidden" id="job-edit-id">
        <div class="form-group">
            <label for="job-title-input">Recruitment Post Title</label>
            <input type="text" name="title" id="job-title-input" class="form-control" placeholder="e.g. UPSC Assistant Commandant Recruitment 2026" required>
        </div>

        <div class="form-group">
            <label for="job-category-select">Job Category</label>
            <select name="category_id" id="job-category-select" class="form-control" required>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="job-department-select">Partner Department</label>
            <select name="department_id" id="job-department-select" class="form-control" required>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="job-state-select">Region / State</label>
            <select name="state_id" id="job-state-select" class="form-control" required>
                @foreach($states as $st)
                    <option value="{{ $st->id }}">{{ $st->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="job-qualification-select">Min Qualification</label>
            <select name="qualification_id" id="job-qualification-select" class="form-control" required>
                @foreach($qualifications as $qual)
                    <option value="{{ $qual->id }}">{{ $qual->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="job-desc-input">Recruitment Overview & Eligibility Details</label>
            <textarea name="description" id="job-desc-input" class="form-control" rows="5" placeholder="Syllabus, vacancy particulars, selection processes..." required></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="job-salary-min">Min Salary (Monthly)</label>
                <input type="number" name="salary_min" id="job-salary-min" class="form-control" value="35000" required>
            </div>
            <div class="form-group">
                <label for="job-salary-max">Max Salary (Monthly)</label>
                <input type="number" name="salary_max" id="job-salary-max" class="form-control" value="112000" required>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div class="form-group">
                <label for="job-vacancies">Vacancies</label>
                <input type="number" name="vacancy_count" id="job-vacancies" class="form-control" value="10" required>
            </div>
            <div class="form-group">
                <label for="job-fee">Application Fee (₹)</label>
                <input type="number" name="application_fee" id="job-fee" class="form-control" value="100" required>
            </div>
        </div>

        <div class="form-group">
            <label for="job-deadline">Apply Deadline</label>
            <input type="date" name="last_date_to_apply" id="job-deadline" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="job-link">Official Web Link</label>
            <input type="url" name="official_website_link" id="job-link" class="form-control" placeholder="https://upsc.gov.in" required>
        </div>

        <!-- Vacancy Details Section in Admin Form -->
        <div style="border-top: 1px solid var(--border-color); padding-top: 1rem; margin-top: 1rem;">
            <h4 style="font-family: 'Outfit'; color: var(--accent-color); font-size: 1.1rem; margin-bottom: 0.5rem; display: flex; justify-content: space-between; align-items: center; margin-top: 0;">
                Vacancy Details
                <button type="button" class="btn-sm" id="btn-admin-add-vacancy" style="padding: 0.2rem 0.5rem; font-size: 0.8rem; background: var(--accent-color); color: #fff; border: none; border-radius: 4px; cursor: pointer;">+ Add Row</button>
            </h4>
            <div id="admin-vacancy-details-container" style="margin-bottom: 1rem;">
                <!-- Dynamic Rows Go Here -->
            </div>
        </div>

        <!-- Category Wise Vacancy Details Section in Admin Form -->
        <div style="border-top: 1px solid var(--border-color); padding-top: 1rem; margin-top: 1rem; margin-bottom: 1.5rem;">
            <h4 style="font-family: 'Outfit'; color: var(--accent-color); font-size: 1.1rem; margin-bottom: 0.5rem; display: flex; justify-content: space-between; align-items: center; margin-top: 0;">
                Category Wise Vacancies
                <button type="button" class="btn-sm" id="btn-admin-add-cat-vacancy" style="padding: 0.2rem 0.5rem; font-size: 0.8rem; background: var(--accent-color); color: #fff; border: none; border-radius: 4px; cursor: pointer;">+ Add Row</button>
            </h4>
            <div id="admin-category-wise-container" style="margin-bottom: 1rem;">
                <!-- Dynamic Rows Go Here -->
            </div>
        </div>

        <button type="submit" class="form-btn" id="job-drawer-submit-btn" style="width:100%;">Save Announcement Live</button>
    </form>
</div>

<!-- B. Web Crawler Configuration Form Slide-out Drawer -->
<div class="admin-drawer glass-panel" id="crawler-drawer" style="position: fixed; right: -480px; top: 0; width: 450px; height: 100vh; background: var(--bg-secondary); border-left: 1px solid var(--border-color); z-index: 1001; transition: right 0.4s cubic-bezier(0.16, 1, 0.3, 1); padding: 2rem; overflow-y: auto; box-shadow: -10px 0 30px rgba(0,0,0,0.15);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
        <h3 id="crawler-drawer-title" style="font-family: 'Outfit'; font-size: 1.4rem; color: var(--accent-color); margin: 0;">Add Scraper Configuration</h3>
        <button class="btn-sm-danger" id="close-crawler-drawer" style="padding: 0.25rem 0.5rem; cursor: pointer;">&times; Close</button>
    </div>

    <form id="ajax-crawler-drawer-form">
        @csrf
        <input type="hidden" id="crawler-edit-id">
        
        <div class="form-group">
            <label for="crawler-name">Crawl Target Feed Name</label>
            <input type="text" id="crawler-name" class="form-control" placeholder="e.g. UPSC Official Recruitment Feed" required>
        </div>

        <div class="form-group">
            <label for="crawler-url">Source Target Feed URL</label>
            <input type="url" id="crawler-url" class="form-control" placeholder="https://example.com/jobs" required>
        </div>

        <div class="form-group">
            <label for="crawler-cron">Cron Expression Schedule</label>
            <input type="text" id="crawler-cron" class="form-control" placeholder="e.g. */15 * * * *" required>
            <p style="font-size:0.7rem; color:var(--text-secondary); margin-top:0.25rem;">Standard Linux Crontab syntax (e.g. hourly, daily, etc.)</p>
        </div>

        <div class="form-group">
            <label for="crawler-active">Initial State</label>
            <select id="crawler-active" class="form-control">
                <option value="1">Active Schedule Enabled</option>
                <option value="0">Suspended / Draft Configuration</option>
            </select>
        </div>

        <h4 style="font-family:'Outfit'; font-size:1.05rem; margin:1.5rem 0 0.75rem 0; border-top:1px dashed var(--border-color); padding-top:1rem; color:var(--accent-color);">Parser Dom Selectors Config</h4>
        
        <div class="form-group">
            <label for="crawler-row-sel">Feed List Card Row Selector (Dom Row Query)</label>
            <input type="text" id="crawler-row-sel" class="form-control" placeholder="e.g. table.recruitment-table tr" required>
        </div>

        <div class="form-group">
            <label for="crawler-title-sel">Job Post Title Selector</label>
            <input type="text" id="crawler-title-sel" class="form-control" placeholder="e.g. td.title-cell a" required>
        </div>

        <div class="form-group">
            <label for="crawler-link-sel">Official Web Link / PDF URL Selector</label>
            <input type="text" id="crawler-link-sel" class="form-control" placeholder="e.g. td.title-cell a" required>
        </div>

        <h4 style="font-family:'Outfit'; font-size:1.05rem; margin:1.5rem 0 0.75rem 0; border-top:1px dashed var(--border-color); padding-top:1rem; color:var(--accent-color);">Default Semantic Mappings</h4>

        <div class="form-group">
            <label for="crawler-cat-select">Default Category Mapping</label>
            <select id="crawler-cat-select" class="form-control" required>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="crawler-dept-select">Default Partner Department</label>
            <select id="crawler-dept-select" class="form-control" required>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="crawler-state-select">Default Region / State</label>
            <select id="crawler-state-select" class="form-control" required>
                @foreach($states as $st)
                    <option value="{{ $st->id }}">{{ $st->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="crawler-qual-select">Default Minimum Qualification</label>
            <select id="crawler-qual-select" class="form-control" required>
                @foreach($qualifications as $qual)
                    <option value="{{ $qual->id }}">{{ $qual->name }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="form-btn" id="crawler-drawer-submit-btn" style="width:100%;">Save Scraper Settings</button>
    </form>
</div>

<!-- D. AI Content Review & Approval Drawer -->
<div class="admin-drawer glass-panel" id="ai-review-drawer" style="position: fixed; right: -650px; top: 0; width: 620px; height: 100vh; background: var(--bg-secondary); border-left: 1px solid var(--border-color); z-index: 1001; transition: right 0.4s cubic-bezier(0.16, 1, 0.3, 1); padding: 2rem; overflow-y: auto; box-shadow: -10px 0 30px rgba(0,0,0,0.15);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
        <h3 style="font-family: 'Outfit'; font-size: 1.4rem; color: var(--accent-color); margin: 0;">Verification & Editorial Console</h3>
        <button class="btn-sm-danger" id="close-ai-drawer" style="padding: 0.25rem 0.5rem; cursor: pointer;">&times; Close</button>
    </div>

    <!-- Telemetry and Diagnostic Errors -->
    <div id="ai-drawer-error-alert" class="glass-panel" style="display: none; padding: 1rem; border-radius: 8px; border-left: 5px solid #ef4444; background: rgba(239, 68, 68, 0.05); margin-bottom: 1.5rem;">
        <div style="color: #ef4444; font-weight: 700; font-size: 0.9rem;">Diagnostic Error Registered:</div>
        <p id="ai-drawer-error-text" style="font-size: 0.8rem; color: var(--text-secondary); margin: 0.25rem 0 0 0; line-height: 1.4;"></p>
    </div>

    <form id="ajax-ai-review-form">
        @csrf
        <input type="hidden" id="ai-review-id">
        <input type="hidden" id="ai-review-post-id">

        <div class="form-group">
            <label style="font-weight: 800; color: var(--accent-color); font-size: 0.8rem; text-transform: uppercase;">Recruitment Title</label>
            <div id="ai-review-title" style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary); margin-top: 0.25rem; line-height: 1.4;"></div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
            <div class="form-group">
                <label for="ai-review-provider-select">Active AI Engine</label>
                <select id="ai-review-provider-select" class="form-control">
                    <option value="gemini">Google Gemini</option>
                    <option value="openai">OpenAI GPT</option>
                    <option value="claude">Anthropic Claude</option>
                </select>
            </div>
            <div class="form-group" style="display: flex; align-items: flex-end;">
                <button type="button" class="btn-primary" id="btn-ai-regenerate" style="margin: 0; width: 100%; background: #8b5cf6; border-color: #8b5cf6;">⚡ Regenerate Verification</button>
            </div>
        </div>

        <div class="form-group">
            <label for="ai-edit-summary">Enriched Job Summary (HTML/Markdown)</label>
            <textarea id="ai-edit-summary" class="form-control" rows="6" placeholder="2-3 paragraphs of job overview..." required></textarea>
        </div>

        <div class="form-group">
            <label for="ai-edit-eligibility">Enriched Eligibility Section (Markdown List)</label>
            <textarea id="ai-edit-eligibility" class="form-control" rows="5" placeholder="Bullet points of eligibility criteria..." required></textarea>
        </div>

        <div class="form-group">
            <label for="ai-edit-selection">Selection Process Details</label>
            <textarea id="ai-edit-selection" class="form-control" rows="4" placeholder="Rounds, stages, exam criteria..." required></textarea>
        </div>

        <div class="form-group">
            <label>Frequently Asked Questions (FAQs)</label>
            <div id="ai-edit-faqs-container" style="display: flex; flex-direction: column; gap: 0.75rem; margin-bottom: 0.5rem;">
                <!-- Dynamically loaded questions -->
            </div>
            <button type="button" class="btn-view" id="btn-ai-add-faq" style="width: 100%; padding: 0.5rem; margin-top: 0.5rem;">+ Add FAQ Question</button>
        </div>

        <h4 style="font-family:'Outfit'; font-size:1.05rem; margin:1.5rem 0 0.75rem 0; border-top:1px dashed var(--border-color); padding-top:1rem; color:var(--accent-color);">Custom Hand-Crafted SEO Overrides</h4>

        <div class="form-group">
            <label for="ai-edit-meta-title">Hand-Crafted Meta Title (High CTR)</label>
            <input type="text" id="ai-edit-meta-title" class="form-control" maxlength="100" required>
        </div>

        <div class="form-group">
            <label for="ai-edit-meta-desc">Hand-Crafted Meta Description</label>
            <textarea id="ai-edit-meta-desc" class="form-control" rows="3" maxlength="255" required></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 2rem;">
            <button type="button" class="btn-danger" id="btn-ai-drawer-reject" style="margin: 0;">Reject Announcement</button>
            <button type="button" class="btn-success" id="btn-ai-drawer-approve" style="margin: 0;">Publish Verification</button>
        </div>
        <button type="submit" class="btn-secondary" style="width: 100%; margin-top: 0.75rem;">Save Draft Changes Only</button>
    </form>
</div>

<!-- C. Immersive Quarantine Rescue & Correct Modal Overlay -->
<div class="modal-overlay" id="quarantineRescueModal">
    <div class="modal-box glass-panel" style="max-width: 650px;">
        <button class="modal-close-btn" id="closeQuarantineRescueModal">&times;</button>
        <h3 style="font-family: 'Outfit'; font-size: 1.4rem; margin-bottom: 0.5rem; color: #f59e0b;">Review Quarantined Feed Listing</h3>
        <p style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 1.5rem;">Correct validation faults, seed missing properties, and override the quarantine block to publish announcement.</p>
        
        <form id="ajaxQuarantineRescueForm">
            @csrf
            <input type="hidden" id="rescue-log-id">
            
            <div class="form-group">
                <label for="rescue-title">Recruitment Announcement Title</label>
                <input type="text" name="title" id="rescue-title" class="form-control" required>
                <div class="invalid-feedback" id="rescueTitleError"></div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.25rem;">
                <div class="form-group">
                    <label for="rescue-deadline">Last Date to Apply</label>
                    <input type="date" name="last_date_to_apply" id="rescue-deadline" class="form-control" required>
                    <div class="invalid-feedback" id="rescueDeadlineError"></div>
                </div>
                <div class="form-group">
                    <label for="rescue-vacancy">Total Vacancies count</label>
                    <input type="number" name="vacancy_count" id="rescue-vacancy" class="form-control" value="5" required>
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.25rem;">
                <div class="form-group">
                    <label for="rescue-fee">Application Fee (₹)</label>
                    <input type="number" name="application_fee" id="rescue-fee" class="form-control" value="100" required>
                </div>
                <div class="form-group">
                    <label for="rescue-link">Official Announcement Link</label>
                    <input type="url" name="official_website_link" id="rescue-link" class="form-control" required>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label>Validation Diagnostics Error Output</label>
                <div id="rescue-errors-feedback" style="background: rgba(239,68,68,0.06); border:1px solid rgba(239,68,68,0.2); padding: 0.75rem 1rem; border-radius: 8px; font-size: 0.8rem; color: #ef4444; font-family: monospace; white-space: pre-wrap;"></div>
            </div>

            <button type="submit" class="form-btn" id="rescueSubmitBtn" style="background:#f59e0b;">Approve Override & Publish Live</button>
        </form>
    </div>
</div>

<!-- Dynamic Settings: Menu Item Modal -->
<div class="modal-overlay" id="menuItemModal">
    <div class="modal-box glass-panel" style="max-width: 500px;">
        <button class="modal-close-btn" id="closeMenuItemModal">&times;</button>
        <h3 style="font-family: 'Outfit'; font-size: 1.4rem; margin-bottom: 0.5rem; color: var(--accent-color);" id="menu-item-modal-title">Add Menu Item</h3>
        <p style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 1.5rem;">Configure navigation links, URL route path, and target behavior.</p>
        
        <form id="menuItemForm">
            @csrf
            <input type="hidden" id="menu-item-id" name="id">
            <input type="hidden" id="menu-item-menu-id" name="menu_id">
            
            <div class="form-group">
                <label for="menu-item-parent">Parent Item (Optional)</label>
                <select id="menu-item-parent" name="parent_id" class="form-control">
                    <option value="">None (Root Level)</option>
                </select>
            </div>
            <div class="form-group">
                <label for="menu-item-title">Title</label>
                <input type="text" id="menu-item-title" name="title" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="menu-item-url">URL / Path</label>
                <input type="text" id="menu-item-url" name="url" class="form-control" placeholder="e.g. /jobs or /p/about-us" required>
            </div>
            <div class="form-group">
                <label for="menu-item-icon">Icon Class (Optional)</label>
                <input type="text" id="menu-item-icon" name="icon" class="form-control" placeholder="e.g. fas fa-home">
            </div>
            <div class="form-row-grid">
                <div class="form-group">
                    <label for="menu-item-target">Target</label>
                    <select id="menu-item-target" name="target" class="form-control">
                        <option value="_self">Same Tab (_self)</option>
                        <option value="_blank">New Tab (_blank)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="menu-item-status">Status</label>
                    <select id="menu-item-status" name="is_active" class="form-control">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="form-btn" style="margin-top: 1.5rem; width: 100%;">Save Item</button>
        </form>
    </div>
</div>

<!-- Dynamic Settings: CMS Page Modal -->
<div class="modal-overlay" id="cmsPageModal">
    <div class="modal-box glass-panel" style="max-width: 850px; width: 90%;">
        <button class="modal-close-btn" id="closeCmsPageModal">&times;</button>
        <h3 style="font-family: 'Outfit'; font-size: 1.4rem; margin-bottom: 0.5rem; color: var(--accent-color);" id="cms-page-modal-title">Create CMS Page</h3>
        <p style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 1.5rem;">Draft dynamic static pages utilizing Rich Text Editor and configure metadata indices.</p>
        
        <form id="cmsPageForm">
            @csrf
            <input type="hidden" id="cms-page-id" name="id">
            
            <div class="modal-form-grid" style="text-align: left;">
                <div>
                    <div class="form-group">
                        <label for="cms-page-title">Page Title</label>
                        <input type="text" id="cms-page-title" name="title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Page Content</label>
                        <!-- Quill Editor container -->
                        <div id="cms-editor" style="height: 250px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-color); border-radius: 8px; color: var(--text-primary);"></div>
                        <input type="hidden" id="cms-page-content" name="content">
                    </div>
                </div>
                <div>
                    <div class="form-group">
                        <label for="cms-page-meta-title">Meta Title</label>
                        <input type="text" id="cms-page-meta-title" name="meta_title" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="cms-page-meta-desc">Meta Description</label>
                        <textarea id="cms-page-meta-desc" name="meta_description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="cms-page-meta-keywords">Meta Keywords</label>
                        <input type="text" id="cms-page-meta-keywords" name="meta_keywords" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="cms-page-status">Status</label>
                        <select id="cms-page-status" name="is_active" class="form-control">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="form-btn" style="margin-top: 1.5rem; width: 100%;">Save CMS Page</button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<!-- Load Quill Rich Editor CDN -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
<!-- Load local offline Chart.js to prevent Service Worker network fetch errors on localhost -->
<script src="{{ asset('assets/js/chart.js') }}"></script>
<script src="{{ asset('assets/js/enterprise-datatable.js') }}"></script>
<script>
    $(document).ready(function() {
        // Toggle Admin Sidebar Navigation links on mobile/tablet
        $('#adminSidebarToggleBtn').on('click', function() {
            $('#adminSidebarLinks').toggleClass('active');
        });

        // Toggle Sidebar Dashboard Tabs/Panels
        $(document).on('click', '.admin-nav-btn', function() {
            $('.admin-nav-btn').removeClass('active');
            $(this).addClass('active');

            // Close navigation links on click of a nav button on mobile
            if ($(window).width() <= 900) {
                $('#adminSidebarLinks').removeClass('active');
            }

            const targetBlock = $(this).data('block');
            $('.admin-panel-block').hide();
            $(`#admin-${targetBlock}`).fadeIn(300);

            // Trigger active data loaders
            if (targetBlock === 'overview') {
                loadOverviewData();
            } else if (targetBlock === 'jobs') {
                loadJobsData(1);
            } else if (targetBlock === 'crawlers') {
                loadCrawlersData();
            } else if (targetBlock === 'master') {
                loadMasterData();
            } else if (targetBlock === 'users') {
                loadUsersData();
            } else if (targetBlock === 'queues') {
                loadQueueDashboard(1);
            } else if (targetBlock === 'audit') {
                loadAuditLogs(1);
            } else if (targetBlock === 'ai-content') {
                loadAiContentData(1);
            } else if (targetBlock === 'marketing') {
                loadMarketingDashboard(1);
            } else if (targetBlock === 'analytics') {
                loadAnalyticsDashboard();
            } else if (targetBlock === 'settings') {
                loadSettingsData();
            }
        });

        // Toggle Master Data nested tabs
        $(document).on('click', '.master-sub-trigger', function() {
            $('.master-sub-trigger').removeClass('active');
            $(this).addClass('active');

            const targetTab = $(this).data('target');
            $('.master-sub-panel').hide();
            $(`#${targetTab}`).fadeIn(300);
        });

        // Initialize dynamic sidebar statistics on first launch based on active block
        const initialBlock = '{{ $activeBlock }}';
        $('.admin-nav-btn').removeClass('active');
        $(`.admin-nav-btn[data-block="${initialBlock}"]`).addClass('active');
        $('.admin-panel-block').hide().removeClass('active');
        $(`#admin-${initialBlock}`).fadeIn(300).addClass('active');

        if (initialBlock === 'overview') {
            loadOverviewData();
        } else if (initialBlock === 'jobs') {
            loadJobsData(1);
        } else if (initialBlock === 'crawlers') {
            loadCrawlersData();
        } else if (initialBlock === 'master') {
            loadMasterData();
        } else if (initialBlock === 'users') {
            loadUsersData();
        } else if (initialBlock === 'queues') {
            loadQueueDashboard(1);
        } else if (initialBlock === 'audit') {
            loadAuditLogs(1);
        } else if (initialBlock === 'ai-content') {
            loadAiContentData(1);
        } else if (initialBlock === 'marketing') {
            loadMarketingDashboard(1);
        } else if (initialBlock === 'analytics') {
            loadAnalyticsDashboard();
        } else if (initialBlock === 'settings') {
            loadSettingsData();
        }

        // Close Slide-out drawers and backdrops
        function closeAllDrawers() {
            $('#job-post-drawer').css('right', '-730px');
            $('#crawler-drawer').css('right', '-480px');
            $('#ai-review-drawer').css('right', '-650px');
            $('#admin-drawer-backdrop').fadeOut(300);
        }

        $('#close-job-drawer, #close-crawler-drawer, #admin-drawer-backdrop').on('click', function() {
            closeAllDrawers();
        });

        // Open Announce Drawer
        $('#btn-create-job-drawer').on('click', function() {
            $('#job-drawer-title').text('Publish Recruitment');
            $('#job-edit-id').val('');
            $('#ajax-job-drawer-form')[0].reset();
            
            // Reset vacancy lists
            $('#admin-vacancy-details-container').empty();
            $('#admin-category-wise-container').empty();
            
            $('#admin-drawer-backdrop').fadeIn(300);
            $('#job-post-drawer').css('right', '0');
        });

        // Open Crawler Drawer
        $('#btn-create-crawler-drawer').on('click', function() {
            $('#crawler-drawer-title').text('Add Scraper Configuration');
            $('#crawler-edit-id').val('');
            $('#ajax-crawler-drawer-form')[0].reset();

            $('#admin-drawer-backdrop').fadeIn(300);
            $('#crawler-drawer').css('right', '0');
        });

        // ===================================================================
        // 1. DASHBOARD OVERVIEW LOAD DATA & RECOVERY PANELS
        // ===================================================================
        let overviewSources = [];
        let overviewLogs = [];
        let currentOverviewPage = 1;
        const itemsPerOverviewPage = 10;

        function renderOverviewCrawlersTable() {
            const query = ($('#overview-crawlers-search').val() || '').toLowerCase().trim();
            
            // Filter sources based on search query
            const filteredSources = overviewSources.filter(src => {
                return src.name.toLowerCase().includes(query) || 
                       (src.source_url && src.source_url.toLowerCase().includes(query));
            });

            const start = (currentOverviewPage - 1) * itemsPerOverviewPage;
            const end = start + itemsPerOverviewPage;
            const pageItems = filteredSources.slice(start, end);

            let trs = '';
            pageItems.forEach(src => {
                const isAct = src.is_active ? '<span class="badge" style="background:rgba(16,185,129,0.08); color:#10b981;">Active</span>' : '<span class="badge" style="background:rgba(239,68,68,0.08); color:#ef4444;">Suspended</span>';
                // Find last audit status
                const log = overviewLogs.find(l => l.source_name === src.name);
                const healthStatus = log ? log.status : 'pending';
                let healthBadge = '<span class="badge" style="background:rgba(156,163,175,0.08); color:#9ca3af;">Pending</span>';
                if (healthStatus === 'success') {
                    healthBadge = '<span class="badge" style="background:rgba(16,185,129,0.08); color:#10b981;">Healthy</span>';
                } else if (healthStatus === 'failed') {
                    const errMsg = log ? (log.error_message || 'Unknown error during scraping run').replace(/"/g, '&quot;').replace(/'/g, '&#39;') : 'Unknown Error';
                    healthBadge = `<span class="badge enterprise-tooltip" data-tooltip="${errMsg}" style="background:rgba(239,68,68,0.08); color:#ef4444; cursor:help;">Error ⚠️</span>`;
                } else if (healthStatus === 'quarantined') {
                    healthBadge = '<span class="badge" style="background:rgba(245,158,11,0.08); color:#f59e0b;">Quarantine</span>';
                }

                trs += `
                    <tr>
                        <td><strong>${src.name}</strong></td>
                        <td><span style="font-size:0.8rem; color:var(--text-secondary);">${log ? log.time : 'Never run'}</span></td>
                        <td style="font-weight:bold;">${log ? log.items_found : 0}</td>
                        <td>${healthBadge}</td>
                    </tr>
                `;
            });
            $('#overview-crawlers-table').html(trs || '<tr><td colspan="4" style="text-align:center; color:var(--text-secondary);">No matching crawlers active.</td></tr>');

            const totalCount = filteredSources.length;
            const fromEntry = totalCount > 0 ? start + 1 : 0;
            const toEntry = Math.min(end, totalCount);
            $('#overview-crawlers-count').text(`Showing ${fromEntry}-${toEntry} of ${totalCount} entries`);

            const lastPage = Math.ceil(totalCount / itemsPerOverviewPage);
            buildPagination('#overview-crawlers-pagination', currentOverviewPage, lastPage, function(page) {
                currentOverviewPage = page;
                renderOverviewCrawlersTable();
            });
        }

        // Search Input handler
        $(document).on('input', '#overview-crawlers-search', function() {
            currentOverviewPage = 1;
            renderOverviewCrawlersTable();
        });

        function loadOverviewData() {
            $.ajax({
                url: '/api/admin/data',
                method: 'GET',
                success: function(res) {
                    if (res.status === 'success') {
                        const m = res.data.metrics;
                        $('#overview-jobs-posted').text(m.total_jobs_posted);
                        $('#overview-sources').text(m.total_sources);
                        $('#overview-active-sources').text(m.active_sources + ' active crawlers');
                        $('#overview-quarantines').text(m.quarantine_runs);

                        const totalRuns = m.success_runs + m.failed_runs;
                        const ratio = totalRuns > 0 ? Math.round((m.success_runs / totalRuns) * 100) : 100;
                        $('#overview-success-runs').text(ratio + '%');
                        $('#overview-failed-runs').text(m.failed_runs + ' critical errors');

                        // Set SVG gauge progress
                        $('#success-ratio-label').text(ratio + '%');
                        const strokeDash = ratio + ' 100';
                        $('#success-svg-gauge').attr('stroke-dasharray', strokeDash);

                        // Populate Overview active crawlers table via paginated function
                        overviewSources = res.data.sources || [];
                        overviewLogs = res.data.logs || [];
                        currentOverviewPage = 1;
                        renderOverviewCrawlersTable();

                        // Populate quarantine listings overriding
                        let qHtml = '';
                        res.data.quarantines.forEach(q => {
                            let errs = '';
                            if (q.errors) {
                                Object.keys(q.errors).forEach(k => {
                                    let errMsgs = Array.isArray(q.errors[k]) ? q.errors[k].join(', ') : q.errors[k];
                                    errs += `&bull; ${k}: ${errMsgs}<br>`;
                                });
                            }
                            
                            let safeTitle = (q.raw_payload.title || 'Quarantined Announcement').replace(/"/g, '&quot;');
                            let safeUrl = (q.raw_payload.official_link || '').replace(/"/g, '&quot;');
                            let safeErrs = errs.replace(/"/g, '&quot;');

                            qHtml += `
                                <div class="glass-panel" style="padding: 1.25rem; margin-bottom: 1rem; border-left: 4px solid #f59e0b; display: flex; justify-content: space-between; align-items: center; gap: 1rem; background: var(--bg-primary);">
                                    <div style="flex:1; min-width:0;">
                                        <h4 style="font-size: 1.05rem; margin-bottom: 0.25rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${safeTitle}</h4>
                                        <p style="font-size:0.8rem; color:var(--text-secondary); margin-bottom:0.5rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">Source Feed: <strong>${q.source_name}</strong> &bull; Crawled: ${q.time}</p>
                                        <div style="font-size:0.75rem; color:#ef4444; font-family:monospace; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">${errs || 'Validation limits check failed.'}</div>
                                    </div>
                                    <button class="form-btn btn-rescue-trigger" data-id="${q.id}" data-title="${safeTitle}" data-url="${safeUrl}" data-errors="${safeErrs}" style="margin:0; padding:0.5rem 1rem; background:#f59e0b; flex-shrink:0;">Rescue</button>
                                </div>
                            `;
                        });
                        $('#admin-quarantine-override-canvas').html(qHtml || '<div style="text-align:center; color:var(--text-secondary); padding: 1rem 0;">Excellent! 0 quarantined listings require manual rescue.</div>');
                    }
                },
                error: function(err) {
                    showToast(err.responseJSON?.message || 'Access Denied: Unable to retrieve overview metrics.', 'error');
                }
            });
        }

        // ===================================================================
        // 1B. TELEMETRY & ANALYTICS DASHBOARD CHART ENGINE
        // ===================================================================
        let trafficChartInstance = null;
        let revenueChartInstance = null;
        let funnelChartInstance = null;

        function loadAnalyticsDashboard() {
            const days = $('#analytics-timeframe').val() || 14;
            $.ajax({
                url: '/api/admin/analytics/metrics',
                method: 'GET',
                data: { days: days },
                success: function(res) {
                    if (res.status === 'success') {
                        const k = res.data.kpis;
                        $('#analytics-kpi-views').text(k.job_views.toLocaleString());
                        $('#analytics-kpi-ctr').text(k.overall_ctr + '%');
                        $('#analytics-kpi-searches').text(k.search_queries.toLocaleString());
                        $('#analytics-kpi-revenue').text('$' + k.estimated_revenue.toFixed(2));

                        // 1. Traffic Chart (Line)
                        const dates = res.data.charts.traffic.map(d => d.date);
                        const organic = res.data.charts.traffic.map(d => d.organic);
                        const direct = res.data.charts.traffic.map(d => d.direct);
                        const bots = res.data.charts.traffic.map(d => d.bots);

                        if (trafficChartInstance) trafficChartInstance.destroy();
                        const ctxTraffic = document.getElementById('trafficChart').getContext('2d');
                        trafficChartInstance = new Chart(ctxTraffic, {
                            type: 'line',
                            data: {
                                labels: dates,
                                datasets: [
                                    {
                                        label: 'Organic Traffic',
                                        data: organic,
                                        borderColor: '#10b981',
                                        backgroundColor: 'rgba(16, 185, 129, 0.05)',
                                        fill: true,
                                        tension: 0.3
                                    },
                                    {
                                        label: 'Direct Traffic',
                                        data: direct,
                                        borderColor: '#2563eb',
                                        backgroundColor: 'rgba(37, 99, 235, 0.05)',
                                        fill: true,
                                        tension: 0.3
                                    },
                                    {
                                        label: 'Bot Crawls (SEO)',
                                        data: bots,
                                        borderColor: '#f59e0b',
                                        backgroundColor: 'rgba(245, 158, 11, 0.05)',
                                        fill: true,
                                        tension: 0.3
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { labels: { color: '#9ca3af' } }
                                },
                                scales: {
                                    x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#9ca3af' } },
                                    y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#9ca3af' } }
                                }
                            }
                        });

                        // 2. Revenue Chart (Bar)
                        const revDates = res.data.charts.revenue.map(d => d.date);
                        const cpc = res.data.charts.revenue.map(d => d.cpc);
                        const cpm = res.data.charts.revenue.map(d => d.cpm);

                        if (revenueChartInstance) revenueChartInstance.destroy();
                        const ctxRev = document.getElementById('revenueChart').getContext('2d');
                        revenueChartInstance = new Chart(ctxRev, {
                            type: 'bar',
                            data: {
                                labels: revDates,
                                datasets: [
                                    {
                                        label: 'CPC (Ad Clicks)',
                                        data: cpc,
                                        backgroundColor: '#ef4444'
                                    },
                                    {
                                        label: 'CPM (Impressions)',
                                        data: cpm,
                                        backgroundColor: '#a855f7'
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { labels: { color: '#9ca3af' } }
                                },
                                scales: {
                                    x: { stacked: true, grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#9ca3af' } },
                                    y: { stacked: true, grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#9ca3af' } }
                                }
                            }
                        });

                        // 3. Funnel Chart (Horizontal Bar)
                        const f = res.data.charts.funnel;
                        if (funnelChartInstance) funnelChartInstance.destroy();
                        const ctxFunnel = document.getElementById('funnelChart').getContext('2d');
                        funnelChartInstance = new Chart(ctxFunnel, {
                            type: 'bar',
                            data: {
                                labels: ['Job Views', 'Saved Bookmarks', 'Apply Clicks', 'Applications'],
                                datasets: [{
                                    label: 'Actions',
                                    data: [f.views, f.bookmarks, f.clicks, f.submissions],
                                    backgroundColor: ['#2563eb', '#8b5cf6', '#f59e0b', '#10b981'],
                                    borderRadius: 6
                                }]
                            },
                            options: {
                                indexAxis: 'y',
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false }
                                },
                                scales: {
                                    x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#9ca3af' } },
                                    y: { grid: { color: 'none' }, ticks: { color: '#9ca3af', font: { weight: 'bold' } } }
                                }
                            }
                        });

                        // 4. Populate Queries Table
                        let qHtml = '';
                        res.data.top_queries.forEach(q => {
                            qHtml += `
                                <tr>
                                    <td><strong>"${q.query}"</strong></td>
                                    <td><span class="badge" style="background:rgba(37,99,235,0.1); color:#2563eb; font-weight:700;">${q.frequency} hits</span></td>
                                    <td style="font-weight:bold;">${q.avg_results}</td>
                                </tr>
                            `;
                        });
                        $('#analytics-queries-table').html(qHtml || '<tr><td colspan="3" style="text-align:center; color:#9ca3af;">No searches logged in timeframe.</td></tr>');

                        // 5. Populate CTR Table
                        let ctrHtml = '';
                        res.data.job_performance.forEach(job => {
                            ctrHtml += `
                                <tr>
                                    <td style="max-width:180px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><strong>${job.title}</strong></td>
                                    <td>${job.views}</td>
                                    <td>${job.bookmarks}</td>
                                    <td>${job.clicks}</td>
                                    <td><span class="badge" style="background:rgba(16,185,129,0.1); color:#10b981; font-weight:bold;">${job.ctr}%</span></td>
                                </tr>
                            `;
                        });
                        $('#analytics-ctr-table').html(ctrHtml || '<tr><td colspan="5" style="text-align:center; color:#9ca3af;">No job views tracked.</td></tr>');

                        // 6. Populate User Journeys
                        let journeyHtml = '';
                        res.data.user_journeys.forEach((j, index) => {
                            journeyHtml += `
                                <div class="glass-panel" style="padding: 1rem; border-left: 4px solid #f59e0b; background: var(--bg-primary); display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                    <div style="font-size: 0.85rem; font-family: monospace; color: var(--text-primary);">
                                        <span style="color:#f59e0b; font-weight:bold; margin-right:0.5rem;">#${index + 1}</span> ${j.path}
                                    </div>
                                    <span class="badge" style="background:rgba(245,158,11,0.1); color:#f59e0b; font-weight:bold;">${j.count} runs</span>
                                </div>
                            `;
                        });
                        $('#analytics-journeys-container').html(journeyHtml || '<div style="text-align:center; color:#9ca3af; padding: 1rem 0;">Awaiting visitor pathways...</div>');
                    }
                },
                error: function(err) {
                    showToast(err.responseJSON?.message || 'Access Denied: Unable to retrieve telemetry metrics.', 'error');
                }
            });
        }

        $(document).on('change', '#analytics-timeframe', function() {
            loadAnalyticsDashboard();
        });

        // Trigger Quarantine Rescue Modal
        $(document).on('click', '.btn-rescue-trigger', function() {
            const id = $(this).data('id');
            const title = $(this).data('title');
            const url = $(this).data('url');
            const errs = $(this).data('errors');

            $('#rescue-log-id').val(id);
            $('#rescue-title').val(title);
            $('#rescue-link').val(url);
            $('#rescue-errors-feedback').html(errs || 'Title characters limit min 15 failing or deadline check out of bounds.');
            $('#rescue-deadline').val('');

            $('#quarantineRescueModal').addClass('active');
        });

        $('#closeQuarantineRescueModal, #quarantineRescueModal').on('click', function(e) {
            if (e.target === this || e.target.id === 'closeQuarantineRescueModal') {
                $('#quarantineRescueModal').removeClass('active');
            }
        });

        // Submit Quarantine Rescue Override Form
        $('#ajaxQuarantineRescueForm').on('submit', function(e) {
            e.preventDefault();
            const logId = $('#rescue-log-id').val();
            const btn = $('#rescueSubmitBtn');
            btn.prop('disabled', true).text('Publishing...');

            $.ajax({
                url: `/api/admin/quarantine/${logId}/rescue`,
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    btn.prop('disabled', false).text('Approve Override & Publish Live');
                    $('#quarantineRescueModal').removeClass('active');
                    showToast(res.message, 'success');
                    loadOverviewData();
                },
                error: function(err) {
                    btn.prop('disabled', false).text('Approve Override & Publish Live');
                    const res = err.responseJSON;
                    showToast(res.message || 'Rescue failed', 'error');
                }
            });
        });

        // ===================================================================
        // 2. RECRUITMENT POSTINGS CRUD
        // ===================================================================
        let jobsTable;
        function loadJobsData() {
            if (!jobsTable) {
                jobsTable = new EnterpriseDataTable('#jobs-datatable', {
                    url: '/api/admin/jobs',
                    searchPlaceholder: 'Search announcements...',
                    pageSize: 20,
                    dataKey: 'jobs',
                    columns: [
                        { key: 'id', title: 'ID', sortable: true, priority: 'high' },
                        { key: 'title', title: 'Recruitment Announcement Title', sortable: true, priority: 'high', render: function(row) {
                            return `
                                <div class="enterprise-primary-stack">
                                    <span class="enterprise-primary-value">${jobsTable.escape(row.title)}</span>
                                    <span class="enterprise-secondary-metadata">${row.department ? jobsTable.escape(row.department.name) : 'Unknown Department'}</span>
                                </div>
                            `;
                        }},
                        { key: 'category', title: 'Category', sortable: false, priority: 'medium', align: 'center', render: function(row) {
                            return `<span class="badge-pill-compact" style="background:rgba(37,99,235,0.08); color:var(--accent-color);">${row.category ? jobsTable.escape(row.category.name) : 'Unassigned'}</span>`;
                        }},
                        { key: 'region', title: 'Region', sortable: false, priority: 'medium', align: 'center', render: function(row) {
                            return `<span class="badge-pill-compact" style="background:rgba(156,163,175,0.1); color:var(--text-primary);">${row.state ? jobsTable.escape(row.state.name) : 'Pan India'}</span>`;
                        }},
                        { key: 'salary_max', title: 'Salary Max', sortable: true, priority: 'medium', align: 'right', render: function(row) {
                            return `₹${Math.round(row.salary_max).toLocaleString('en-IN')}`;
                        }},
                        { key: 'last_date_to_apply', title: 'Deadline', sortable: true, priority: 'high', align: 'center', render: function(row) {
                            return `<span class="badge-pill-compact" style="background:rgba(239,68,68,0.08); color:#ef4444;">${row.last_date_to_apply ? row.last_date_to_apply.substring(0, 10) : 'N/A'}</span>`;
                        }},
                        { key: 'actions', title: 'Actions', sortable: false, priority: 'high', align: 'center', render: function(row) {
                            return `
                                <div style="display:flex; gap:0.35rem; justify-content:center; align-items:center;">
                                    <button class="enterprise-action-icon-btn enterprise-action-icon-btn-ai btn-trigger-ai-gen enterprise-tooltip" data-tooltip="Verify Listing" data-id="${row.id}"><i class="fas fa-check-circle"></i></button>
                                    <button class="enterprise-action-icon-btn enterprise-action-icon-btn-view btn-edit-job enterprise-tooltip" data-tooltip="Edit" data-id="${row.id}" data-slug="${row.slug}" data-title="${jobsTable.escape(row.title)}" data-category="${row.category_id}" data-dept="${row.department_id}" data-state="${row.state_id}" data-qual="${row.qualification_id}" data-desc="${jobsTable.escape(row.description)}" data-min="${row.salary_min}" data-max="${row.salary_max}" data-vac="${row.vacancy_count}" data-fee="${row.application_fee}" data-deadline="${row.last_date_to_apply ? row.last_date_to_apply.substring(0, 10) : ''}" data-url="${jobsTable.escape(row.official_website_link)}"><i class="fas fa-edit"></i></button>
                                    <button class="enterprise-action-icon-btn enterprise-action-icon-btn-danger btn-delete-job enterprise-tooltip" data-tooltip="Delete" data-id="${row.id}"><i class="fas fa-trash-alt"></i></button>
                                </div>
                            `;
                        }}
                    ]
                });
            } else {
                jobsTable.refresh();
            }
        }

        // Trigger Edit Job
        $(document).on('click', '.btn-edit-job', function() {
            const id = $(this).data('id');
            const slug = $(this).data('slug');
            $('#job-drawer-title').text('Edit Recruitment Post');
            $('#job-edit-id').val(id);
            
            $('#job-title-input').val($(this).data('title'));
            $('#job-category-select').val($(this).data('category'));
            $('#job-department-select').val($(this).data('dept'));
            $('#job-state-select').val($(this).data('state'));
            $('#job-qualification-select').val($(this).data('qual'));
            $('#job-desc-input').val($(this).data('desc'));
            $('#job-salary-min').val($(this).data('min'));
            $('#job-salary-max').val($(this).data('max'));
            $('#job-vacancies').val($(this).data('vac'));
            $('#job-fee').val($(this).data('fee'));
            $('#job-deadline').val($(this).data('deadline'));
            $('#job-link').val($(this).data('url'));

            // Reset containers
            $('#admin-vacancy-details-container').empty();
            $('#admin-category-wise-container').empty();

            if (slug) {
                $.ajax({
                    url: `/api/jobs/${slug}`,
                    method: 'GET',
                    success: function(res) {
                        if (res.status === 'success' && res.data) {
                            if (res.data.vacancy_details) {
                                res.data.vacancy_details.forEach(function(vd, index) {
                                    addVacancyRow(vd.post_name, vd.total_post, vd.eligibility, index);
                                });
                            }
                            if (res.data.category_wise_vacancies) {
                                res.data.category_wise_vacancies.forEach(function(cwv, index) {
                                    addCategoryVacancyRow(cwv);
                                });
                            }
                        }
                    }
                });
            }

            $('#admin-drawer-backdrop').fadeIn(300);
            $('#job-post-drawer').css('right', '0');
        });

        // Delete Job posting
        $(document).on('click', '.btn-delete-job', function() {
            if (!confirm('Are you absolutely sure you want to permanently erase this announcement?')) return;
            const id = $(this).data('id');

            $.ajax({
                url: `/api/admin/jobs/${id}`,
                method: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    showToast(res.message, 'success');
                    loadJobsData(currentJobsPage);
                },
                error: function(err) {
                    showToast('Unable to delete posting.', 'error');
                }
            });
        });

        // Submit Job Posting Form (Save / Edit)
        $('#ajax-job-drawer-form').on('submit', function(e) {
            e.preventDefault();
            const id = $('#job-edit-id').val();
            const url = id ? `/api/admin/jobs/${id}` : '/api/admin/jobs';
            const btn = $('#job-drawer-submit-btn');
            btn.prop('disabled', true).text('Synchronizing...');

            $.ajax({
                url: url,
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    btn.prop('disabled', false).text('Save Announcement Live');
                    closeAllDrawers();
                    showToast(res.message, 'success');
                    loadJobsData(1);
                },
                error: function(err) {
                    btn.prop('disabled', false).text('Save Announcement Live');
                    const res = err.responseJSON;
                    showToast(res.message || 'Error occurred while saving announcement.', 'error');
                }
            });
        });

        // ===================================================================
        // 3. CRAWLER MONITOR CONFIGS CRUD
        // ===================================================================
        let crawlersTable;
        function loadCrawlersData() {
            if (!crawlersTable) {
                crawlersTable = new EnterpriseDataTable('#crawlers-management-datatable', {
                    url: '/api/admin/scrapers',
                    serverSide: false,
                    searchPlaceholder: 'Search scrapers...',
                    columns: [
                        { key: 'name', title: 'Crawl Target Name', sortable: true, priority: 'high', render: function(row) {
                            return `<strong>${crawlersTable.escape(row.name)}</strong>`;
                        }},
                        { key: 'source_url', title: 'Source URL', sortable: true, priority: 'medium', render: function(row) {
                            return `<span style="font-size:0.75rem; color:var(--text-secondary); word-break:break-all;">${crawlersTable.escape(row.source_url)}</span>`;
                        }},
                        { key: 'cron_expression', title: 'Cron Schedule', sortable: true, priority: 'medium', align: 'center', render: function(row) {
                            return `<span style="font-family:monospace; font-size:0.8rem;">${crawlersTable.escape(row.cron_expression)}</span>`;
                        }},
                        { key: 'is_active', title: 'Active State', sortable: true, priority: 'high', align: 'center', render: function(row) {
                            const isActChecked = row.is_active ? 'checked' : '';
                            return `
                                <label class="toggle-switch" style="vertical-align:middle; display:inline-block;">
                                    <input type="checkbox" class="toggle-scraper-active-switch" data-id="${row.id}" ${isActChecked}>
                                    <span class="toggle-slider slider-success"></span>
                                </label>
                            `;
                        }},
                        { key: 'crawl_now', title: 'Crawl Override', sortable: false, priority: 'high', align: 'center', render: function(row) {
                            return `<button class="enterprise-btn enterprise-btn-primary btn-run-scraper" style="padding:0.2rem 0.5rem; height:24px; font-size:0.75rem;" data-id="${row.id}"><i class="fas fa-play" style="font-size:0.65rem;"></i> Run</button>`;
                        }},
                        { key: 'actions', title: 'Actions', sortable: false, priority: 'high', align: 'center', render: function(row) {
                            const selectors = row.selectors_config || {};
                            return `
                                <div style="display:flex; gap:0.35rem; justify-content:center; align-items:center;">
                                    <button class="enterprise-action-icon-btn enterprise-action-icon-btn-view btn-edit-crawler enterprise-tooltip" data-tooltip="Edit" data-id="${row.id}" data-name="${crawlersTable.escape(row.name)}" data-url="${crawlersTable.escape(row.source_url)}" data-cron="${crawlersTable.escape(row.cron_expression)}" data-active="${row.is_active}" data-row="${crawlersTable.escape(selectors.row_selector || '')}" data-title="${crawlersTable.escape(selectors.title_selector || '')}" data-link="${crawlersTable.escape(selectors.link_selector || '')}" data-cat="${selectors.default_category_id || 1}" data-dept="${selectors.default_department_id || 1}" data-state="${selectors.default_state_id || 1}" data-qual="${selectors.default_qualification_id || 1}"><i class="fas fa-edit"></i></button>
                                    <button class="enterprise-action-icon-btn enterprise-action-icon-btn-danger btn-delete-crawler enterprise-tooltip" data-tooltip="Delete" data-id="${row.id}"><i class="fas fa-trash-alt"></i></button>
                                </div>
                            `;
                        }}
                    ]
                });
            } else {
                crawlersTable.refresh();
            }
        }

        // Toggle Switch Active Crawl Targets
        $(document).on('change', '.toggle-scraper-active-switch', function() {
            const id = $(this).data('id');
            const checkbox = $(this);
            
            $.ajax({
                url: `/api/admin/scraper/${id}/toggle`,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    showToast(res.message, 'success');
                },
                error: function() {
                    checkbox.prop('checked', !checkbox.prop('checked'));
                    showToast('Failed to toggle active configuration state.', 'error');
                }
            });
        });

        // Trigger manual scraper dispatch
        $(document).on('click', '.btn-run-scraper', function() {
            const id = $(this).data('id');
            const btn = $(this);
            btn.prop('disabled', true).text('Crawling...');

            $.ajax({
                url: `/api/admin/scraper/${id}/run`,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    btn.prop('disabled', false).text('Crawl Now');
                    showToast(res.message, 'success');
                    // Refresh overview stats
                    setTimeout(() => { loadOverviewData(); }, 3000);
                },
                error: function() {
                    btn.prop('disabled', false).text('Crawl Now');
                    showToast('Failed to dispatch background crawler task.', 'error');
                }
            });
        });

        // Trigger Edit Crawler
        $(document).on('click', '.btn-edit-crawler', function() {
            const id = $(this).data('id');
            $('#crawler-drawer-title').text('Edit Scraper Config');
            $('#crawler-edit-id').val(id);

            $('#crawler-name').val($(this).data('name'));
            $('#crawler-url').val($(this).data('url'));
            $('#crawler-cron').val($(this).data('cron'));
            $('#crawler-active').val($(this).data('active') ? '1' : '0');
            $('#crawler-row-sel').val($(this).data('row'));
            $('#crawler-title-sel').val($(this).data('title'));
            $('#crawler-link-sel').val($(this).data('link'));
            $('#crawler-cat-select').val($(this).data('cat'));
            $('#crawler-dept-select').val($(this).data('dept'));
            $('#crawler-state-select').val($(this).data('state'));
            $('#crawler-qual-select').val($(this).data('qual'));

            $('#admin-drawer-backdrop').fadeIn(300);
            $('#crawler-drawer').css('right', '0');
        });

        // Delete crawler target
        $(document).on('click', '.btn-delete-crawler', function() {
            if (!confirm('Are you absolutely sure you want to permanently erase this crawler configuration?')) return;
            const id = $(this).data('id');

            $.ajax({
                url: `/api/admin/scrapers/${id}`,
                method: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    showToast(res.message, 'success');
                    loadCrawlersData();
                },
                error: function() {
                    showToast('Unable to delete scraping target.', 'error');
                }
            });
        });

        // Submit Crawler Target Config Form
        $('#ajax-crawler-drawer-form').on('submit', function(e) {
            e.preventDefault();
            const id = $('#crawler-edit-id').val();
            const url = id ? `/api/admin/scrapers/${id}` : '/api/admin/scrapers';
            const btn = $('#crawler-drawer-submit-btn');
            btn.prop('disabled', true).text('Configuring...');

            const formData = {
                _token: '{{ csrf_token() }}',
                name: $('#crawler-name').val(),
                source_url: $('#crawler-url').val(),
                cron_expression: $('#crawler-cron').val(),
                is_active: $('#crawler-active').val(),
                row_selector: $('#crawler-row-sel').val(),
                title_selector: $('#crawler-title-sel').val(),
                link_selector: $('#crawler-link-sel').val(),
                default_category_id: $('#crawler-cat-select').val(),
                default_department_id: $('#crawler-dept-select').val(),
                default_state_id: $('#crawler-state-select').val(),
                default_qualification_id: $('#crawler-qual-select').val()
            };

            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                success: function(res) {
                    btn.prop('disabled', false).text('Save Scraper Settings');
                    closeAllDrawers();
                    showToast(res.message, 'success');
                    loadCrawlersData();
                },
                error: function(err) {
                    btn.prop('disabled', false).text('Save Scraper Settings');
                    const res = err.responseJSON;
                    showToast(res.message || 'Error occurred.', 'error');
                }
            });
        });

        // ===================================================================
        // 4. MASTER DATA MANAGER CRUD
        // ===================================================================
        function loadMasterData() {
            loadCategoriesList();
            loadDepartmentsList();
            loadQualificationsList();
            loadStatesList();
        }

        // Categories Actions
        let categoriesTable;
        function loadCategoriesList() {
            if (!categoriesTable) {
                categoriesTable = new EnterpriseDataTable('#categories-datatable', {
                    url: '/api/admin/categories',
                    serverSide: false,
                    searchPlaceholder: 'Search categories...',
                    columns: [
                        { key: 'id', title: 'ID', sortable: true, priority: 'high' },
                        { key: 'name', title: 'Category Name', sortable: true, priority: 'high', render: function(row) {
                            return `<strong>${categoriesTable.escape(row.name)}</strong>`;
                        }},
                        { key: 'slug', title: 'Slug Reference', sortable: true, priority: 'medium', render: function(row) {
                            return `<span style="font-size:0.8rem; color:var(--text-secondary);">${categoriesTable.escape(row.slug)}</span>`;
                        }},
                        { key: 'actions', title: 'Actions', sortable: false, priority: 'high', align: 'center', render: function(row) {
                            return `
                                <div style="display:flex; gap:0.35rem; justify-content:center; align-items:center;">
                                    <button class="enterprise-action-icon-btn enterprise-action-icon-btn-view btn-edit-category enterprise-tooltip" data-tooltip="Edit" data-id="${row.id}" data-name="${categoriesTable.escape(row.name)}"><i class="fas fa-edit"></i></button>
                                    <button class="enterprise-action-icon-btn enterprise-action-icon-btn-danger btn-delete-category enterprise-tooltip" data-tooltip="Delete" data-id="${row.id}"><i class="fas fa-trash-alt"></i></button>
                                </div>
                            `;
                        }}
                    ]
                });
            } else {
                categoriesTable.refresh();
            }
        }

        $('#ajax-category-form').on('submit', function(e) {
            e.preventDefault();
            const id = $('#category-edit-id').val();
            const url = id ? `/api/admin/categories/${id}` : '/api/admin/categories';
            const btn = $('#category-submit-btn');
            btn.prop('disabled', true).text('Saving...');

            $.ajax({
                url: url,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}', name: $('#category-name-input').val() },
                success: function(res) {
                    btn.prop('disabled', false).text('Save Category');
                    $('#category-edit-id').val('');
                    $('#category-name-input').val('');
                    $('#category-form-title').text('Add Category');
                    $('#category-cancel-btn').hide();
                    showToast(res.message, 'success');
                    loadCategoriesList();
                },
                error: function(err) {
                    btn.prop('disabled', false).text('Save Category');
                    const res = err.responseJSON;
                    showToast(res.message || 'Error occurred.', 'error');
                }
            });
        });

        $(document).on('click', '.btn-edit-category', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            $('#category-edit-id').val(id);
            $('#category-name-input').val(name);
            $('#category-form-title').text('Edit Category');
            $('#category-cancel-btn').show();
        });

        $('#category-cancel-btn').on('click', function() {
            $('#category-edit-id').val('');
            $('#category-name-input').val('');
            $('#category-form-title').text('Add Category');
            $(this).hide();
        });

        $(document).on('click', '.btn-delete-category', function() {
            if (!confirm('Are you sure you want to delete this Category?')) return;
            const id = $(this).data('id');
            $.ajax({
                url: `/api/admin/categories/${id}`,
                method: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    showToast(res.message, 'success');
                    loadCategoriesList();
                },
                error: function(err) {
                    showToast(err.responseJSON.message || 'Linked posts blocking delete.', 'error');
                }
            });
        });

        // Departments Actions
        let departmentsTable;
        function loadDepartmentsList() {
            if (!departmentsTable) {
                departmentsTable = new EnterpriseDataTable('#departments-datatable', {
                    url: '/api/admin/departments',
                    serverSide: false,
                    searchPlaceholder: 'Search departments...',
                    columns: [
                        { key: 'id', title: 'ID', sortable: true, priority: 'high' },
                        { key: 'name', title: 'Department Name', sortable: true, priority: 'high', render: function(row) {
                            return `<strong>${departmentsTable.escape(row.name)}</strong>`;
                        }},
                        { key: 'code', title: 'Code', sortable: true, priority: 'medium', render: function(row) {
                            return `<span style="font-weight:bold;">${departmentsTable.escape(row.code)}</span>`;
                        }},
                        { key: 'actions', title: 'Actions', sortable: false, priority: 'high', align: 'center', render: function(row) {
                            return `
                                <div style="display:flex; gap:0.35rem; justify-content:center; align-items:center;">
                                    <button class="enterprise-action-icon-btn enterprise-action-icon-btn-view btn-edit-dept enterprise-tooltip" data-tooltip="Edit" data-id="${row.id}" data-name="${departmentsTable.escape(row.name)}" data-code="${departmentsTable.escape(row.code)}"><i class="fas fa-edit"></i></button>
                                    <button class="enterprise-action-icon-btn enterprise-action-icon-btn-danger btn-delete-dept enterprise-tooltip" data-tooltip="Delete" data-id="${row.id}"><i class="fas fa-trash-alt"></i></button>
                                </div>
                            `;
                        }}
                    ]
                });
            } else {
                departmentsTable.refresh();
            }
        }

        $('#ajax-department-form').on('submit', function(e) {
            e.preventDefault();
            const id = $('#dept-edit-id').val();
            const url = id ? `/api/admin/departments/${id}` : '/api/admin/departments';
            const btn = $('#dept-submit-btn');
            btn.prop('disabled', true).text('Saving...');

            $.ajax({
                url: url,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}', name: $('#dept-name-input').val(), code: $('#dept-code-input').val() },
                success: function(res) {
                    btn.prop('disabled', false).text('Save Department');
                    $('#dept-edit-id').val('');
                    $('#dept-name-input').val('');
                    $('#dept-code-input').val('');
                    $('#dept-form-title').text('Add Department');
                    $('#dept-cancel-btn').hide();
                    showToast(res.message, 'success');
                    loadDepartmentsList();
                },
                error: function(err) {
                    btn.prop('disabled', false).text('Save Department');
                    showToast(err.responseJSON.message || 'Error occurred.', 'error');
                }
            });
        });

        $(document).on('click', '.btn-edit-dept', function() {
            const id = $(this).data('id');
            $('#dept-edit-id').val(id);
            $('#dept-name-input').val($(this).data('name'));
            $('#dept-code-input').val($(this).data('code'));
            $('#dept-form-title').text('Edit Department');
            $('#dept-cancel-btn').show();
        });

        $('#dept-cancel-btn').on('click', function() {
            $('#dept-edit-id').val('');
            $('#dept-name-input').val('');
            $('#dept-code-input').val('');
            $('#dept-form-title').text('Add Department');
            $(this).hide();
        });

        $(document).on('click', '.btn-delete-dept', function() {
            if (!confirm('Are you sure you want to delete this Department?')) return;
            const id = $(this).data('id');
            $.ajax({
                url: `/api/admin/departments/${id}`,
                method: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    showToast(res.message, 'success');
                    loadDepartmentsList();
                },
                error: function(err) {
                    showToast(err.responseJSON.message || 'Error occurred.', 'error');
                }
            });
        });

        // Qualifications Actions
        let qualificationsTable;
        function loadQualificationsList() {
            if (!qualificationsTable) {
                qualificationsTable = new EnterpriseDataTable('#qualifications-datatable', {
                    url: '/api/admin/qualifications',
                    serverSide: false,
                    searchPlaceholder: 'Search qualifications...',
                    columns: [
                        { key: 'id', title: 'ID', sortable: true, priority: 'high' },
                        { key: 'name', title: 'Qualification', sortable: true, priority: 'high', render: function(row) {
                            return `<strong>${qualificationsTable.escape(row.name)}</strong>`;
                        }},
                        { key: 'slug', title: 'Slug Reference', sortable: true, priority: 'medium', render: function(row) {
                            return `<span style="font-size:0.8rem; color:var(--text-secondary);">${qualificationsTable.escape(row.slug)}</span>`;
                        }},
                        { key: 'actions', title: 'Actions', sortable: false, priority: 'high', align: 'center', render: function(row) {
                            return `
                                <div style="display:flex; gap:0.35rem; justify-content:center; align-items:center;">
                                    <button class="enterprise-action-icon-btn enterprise-action-icon-btn-view btn-edit-qual enterprise-tooltip" data-tooltip="Edit" data-id="${row.id}" data-name="${qualificationsTable.escape(row.name)}"><i class="fas fa-edit"></i></button>
                                    <button class="enterprise-action-icon-btn enterprise-action-icon-btn-danger btn-delete-qual enterprise-tooltip" data-tooltip="Delete" data-id="${row.id}"><i class="fas fa-trash-alt"></i></button>
                                </div>
                            `;
                        }}
                    ]
                });
            } else {
                qualificationsTable.refresh();
            }
        }

        $('#ajax-qualification-form').on('submit', function(e) {
            e.preventDefault();
            const id = $('#qual-edit-id').val();
            const url = id ? `/api/admin/qualifications/${id}` : '/api/admin/qualifications';
            const btn = $('#qual-submit-btn');
            btn.prop('disabled', true).text('Saving...');

            $.ajax({
                url: url,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}', name: $('#qual-name-input').val() },
                success: function(res) {
                    btn.prop('disabled', false).text('Save Qualification');
                    $('#qual-edit-id').val('');
                    $('#qual-name-input').val('');
                    $('#qual-form-title').text('Add Qualification');
                    $('#qual-cancel-btn').hide();
                    showToast(res.message, 'success');
                    loadQualificationsList();
                },
                error: function(err) {
                    btn.prop('disabled', false).text('Save Qualification');
                    showToast(err.responseJSON.message || 'Error occurred.', 'error');
                }
            });
        });

        $(document).on('click', '.btn-edit-qual', function() {
            const id = $(this).data('id');
            $('#qual-edit-id').val(id);
            $('#qual-name-input').val($(this).data('name'));
            $('#qual-form-title').text('Edit Qualification');
            $('#qual-cancel-btn').show();
        });

        $('#qual-cancel-btn').on('click', function() {
            $('#qual-edit-id').val('');
            $('#qual-name-input').val('');
            $('#qual-form-title').text('Add Qualification');
            $(this).hide();
        });

        $(document).on('click', '.btn-delete-qual', function() {
            if (!confirm('Are you sure you want to delete this Qualification?')) return;
            const id = $(this).data('id');
            $.ajax({
                url: `/api/admin/qualifications/${id}`,
                method: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    showToast(res.message, 'success');
                    loadQualificationsList();
                },
                error: function(err) {
                    showToast(err.responseJSON.message || 'Error occurred.', 'error');
                }
            });
        });

        // States Actions
        let statesTable;
        function loadStatesList() {
            if (!statesTable) {
                statesTable = new EnterpriseDataTable('#states-datatable', {
                    url: '/api/admin/states',
                    serverSide: false,
                    searchPlaceholder: 'Search states...',
                    columns: [
                        { key: 'id', title: 'ID', sortable: true, priority: 'high' },
                        { key: 'name', title: 'State Name', sortable: true, priority: 'high', render: function(row) {
                            return `<strong>${statesTable.escape(row.name)}</strong>`;
                        }},
                        { key: 'code', title: 'Code', sortable: true, priority: 'medium', render: function(row) {
                            return `<span style="font-weight:bold;">${statesTable.escape(row.code)}</span>`;
                        }},
                        { key: 'actions', title: 'Actions', sortable: false, priority: 'high', align: 'center', render: function(row) {
                            return `
                                <div style="display:flex; gap:0.35rem; justify-content:center; align-items:center;">
                                    <button class="enterprise-action-icon-btn enterprise-action-icon-btn-view btn-edit-state enterprise-tooltip" data-tooltip="Edit" data-id="${row.id}" data-name="${statesTable.escape(row.name)}" data-code="${statesTable.escape(row.code)}"><i class="fas fa-edit"></i></button>
                                    <button class="enterprise-action-icon-btn enterprise-action-icon-btn-danger btn-delete-state enterprise-tooltip" data-tooltip="Delete" data-id="${row.id}"><i class="fas fa-trash-alt"></i></button>
                                </div>
                            `;
                        }}
                    ]
                });
            } else {
                statesTable.refresh();
            }
        }

        $('#ajax-state-form').on('submit', function(e) {
            e.preventDefault();
            const id = $('#state-edit-id').val();
            const url = id ? `/api/admin/states/${id}` : '/api/admin/states';
            const btn = $('#state-submit-btn');
            btn.prop('disabled', true).text('Saving...');

            $.ajax({
                url: url,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}', name: $('#state-name-input').val(), code: $('#state-code-input').val() },
                success: function(res) {
                    btn.prop('disabled', false).text('Save State');
                    $('#state-edit-id').val('');
                    $('#state-name-input').val('');
                    $('#state-code-input').val('');
                    $('#state-form-title').text('Add State/Region');
                    $('#state-cancel-btn').hide();
                    showToast(res.message, 'success');
                    loadStatesList();
                },
                error: function(err) {
                    btn.prop('disabled', false).text('Save State');
                    showToast(err.responseJSON.message || 'Error occurred.', 'error');
                }
            });
        });

        $(document).on('click', '.btn-edit-state', function() {
            const id = $(this).data('id');
            $('#state-edit-id').val(id);
            $('#state-name-input').val($(this).data('name'));
            $('#state-code-input').val($(this).data('code'));
            $('#state-form-title').text('Edit State');
            $('#state-cancel-btn').show();
        });

        $('#state-cancel-btn').on('click', function() {
            $('#state-edit-id').val('');
            $('#state-name-input').val('');
            $('#state-code-input').val('');
            $('#state-form-title').text('Add State');
            $(this).hide();
        });

        $(document).on('click', '.btn-delete-state', function() {
            if (!confirm('Are you sure you want to delete this State?')) return;
            const id = $(this).data('id');
            $.ajax({
                url: `/api/admin/states/${id}`,
                method: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    showToast(res.message, 'success');
                    loadStatesList();
                },
                error: function(err) {
                    showToast(err.responseJSON.message || 'Error occurred.', 'error');
                }
            });
        });

        // ===================================================================
        // 5. USER ACCESS MANAGER (ROLE & STATUS TOGGLES)
        // ===================================================================
        let usersTable;
        function loadUsersData() {
            if (!usersTable) {
                usersTable = new EnterpriseDataTable('#users-datatable', {
                    url: '/api/admin/users',
                    serverSide: false,
                    searchPlaceholder: 'Search users...',
                    dataKey: 'users',
                    columns: [
                        { key: 'id', title: 'User ID', sortable: true, priority: 'low' },
                        { key: 'name', title: 'Name', sortable: true, priority: 'high', render: function(row) {
                            return `<strong>${usersTable.escape(row.name)}</strong>`;
                        }},
                        { key: 'email', title: 'Email', sortable: true, priority: 'high', render: function(row) {
                            return `<span style="font-size:0.8rem; color:var(--text-secondary);">${usersTable.escape(row.email)}</span>`;
                        }},
                        { key: 'role', title: 'Role', sortable: true, priority: 'high', align: 'center', render: function(row) {
                            let badgeRole = '';
                            if (row.role === 'super_admin') {
                                badgeRole = '<span class="badge-pill-compact" style="background:rgba(239,68,68,0.08); color:#ef4444; font-weight:700;">Super Admin</span>';
                            } else if (row.role === 'admin') {
                                badgeRole = '<span class="badge-pill-compact" style="background:rgba(139,92,246,0.08); color:#8b5cf6; font-weight:700;">Admin</span>';
                            } else if (row.role === 'editor') {
                                badgeRole = '<span class="badge-pill-compact" style="background:rgba(59,130,246,0.08); color:#3b82f6;">Editor</span>';
                            } else if (row.role === 'reviewer') {
                                badgeRole = '<span class="badge-pill-compact" style="background:rgba(20,184,166,0.08); color:#14b8a6;">Reviewer</span>';
                            } else if (row.role === 'moderator') {
                                badgeRole = '<span class="badge-pill-compact" style="background:rgba(245,158,11,0.08); color:#f59e0b;">Moderator</span>';
                            } else {
                                badgeRole = '<span class="badge-pill-compact" style="background:rgba(156,163,175,0.1); color:var(--text-primary);">Candidate</span>';
                            }
                            return badgeRole;
                        }},
                        { key: 'is_active', title: 'Account State', sortable: true, priority: 'medium', align: 'center', render: function(row) {
                            return row.is_active 
                                ? '<span class="badge-pill-compact" style="background:rgba(16,185,129,0.08); color:#10b981;">Active</span>' 
                                : '<span class="badge-pill-compact" style="background:rgba(239,68,68,0.08); color:#ef4444;">Suspended</span>';
                        }},
                        { key: 'actions', title: 'Elevations / Actions', sortable: false, priority: 'high', align: 'center', render: function(row) {
                            const rolesList = [
                                { value: 'super_admin', label: 'Super Admin' },
                                { value: 'admin', label: 'Admin' },
                                { value: 'editor', label: 'Editor' },
                                { value: 'reviewer', label: 'Reviewer' },
                                { value: 'moderator', label: 'Moderator' },
                                { value: 'candidate', label: 'Candidate' }
                            ];

                            let roleSelect = `<select class="select-user-role enterprise-select" style="height:24px; padding: 0 1.25rem 0 0.25rem !important; font-size:0.75rem !important; margin:0;" data-id="${row.id}">`;
                            rolesList.forEach(r => {
                                const selected = row.role === r.value ? 'selected' : '';
                                roleSelect += `<option value="${r.value}" ${selected}>${r.label}</option>`;
                            });
                            roleSelect += `</select>`;

                            return `
                                <div style="display:flex; gap:0.35rem; justify-content:center; align-items:center;">
                                    ${roleSelect}
                                    <button class="enterprise-action-icon-btn ${row.is_active ? 'enterprise-action-icon-btn-danger' : 'enterprise-action-icon-btn-success'} btn-toggle-status enterprise-tooltip" data-tooltip="${row.is_active ? 'Suspend Account' : 'Activate Account'}" data-id="${row.id}" data-active="${row.is_active}">
                                        <i class="fas ${row.is_active ? 'fa-ban' : 'fa-user-check'}"></i>
                                    </button>
                                </div>
                            `;
                        }}
                    ]
                });
            } else {
                usersTable.refresh();
            }
        }

        // Change user access role via dropdown selector
        $(document).on('change', '.select-user-role', function() {
            const id = $(this).data('id');
            const targetRole = $(this).val();

            $.ajax({
                url: `/api/admin/users/${id}/update`,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}', role: targetRole },
                success: function(res) {
                    showToast(res.message, 'success');
                    loadUsersData();
                },
                error: function(err) {
                    showToast(err.responseJSON.message || 'Access change forbidden.', 'error');
                    loadUsersData();
                }
            });
        });

        // Toggle account active state
        $(document).on('click', '.btn-toggle-status', function() {
            const id = $(this).data('id');
            const currentActive = $(this).data('active');
            const targetActive = currentActive ? 0 : 1;

            $.ajax({
                url: `/api/admin/users/${id}/update`,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}', is_active: targetActive },
                success: function(res) {
                    showToast(res.message, 'success');
                    loadUsersData();
                },
                error: function(err) {
                    showToast(err.responseJSON.message || 'Access change forbidden.', 'error');
                }
            });
        });

        // ===================================================================
        // 6. SEO CACHE SYNCHRONIZER
        // ===================================================================
        $('#ajax-seo-console-form').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#seo-submit-btn');
            btn.prop('disabled', true).text('Caching...');

            $.ajax({
                url: '/api/admin/seo/update',
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    btn.prop('disabled', false).text('Synchronize Metadata Cache');
                    showToast(res.message, 'success');
                },
                error: function(err) {
                    btn.prop('disabled', false).text('Synchronize Metadata Cache');
                    showToast('Failed to write cached metadata configurations.', 'error');
                }
            });
        });

        // ===================================================================
        // 7. QUEUE DASHBOARD & DLQ OPERATIONS
        // ===================================================================
        let queuesTable;
        function loadQueueDashboard(page) {
            // 1. Fetch metrics
            $.ajax({
                url: '/api/admin/queues/metrics',
                method: 'GET',
                success: function(res) {
                    if (res.status === 'success') {
                        const m = res.data.metrics;
                        $('#queues-driver').text(res.data.driver);
                        $('#queues-pending').text(m.total_pending);
                        $('#queues-pending-details').text(`scrapers: ${m.pending_scrapers} | notifications: ${m.pending_notifications} | default: ${m.pending_default}`);
                        $('#queues-active').text(m.processing);
                        $('#queues-failed').text(m.failed_dlq);
                    }
                }
            });

            // 2. Fetch failed jobs list
            if (!queuesTable) {
                queuesTable = new EnterpriseDataTable('#queues-failed-datatable', {
                    url: '/api/admin/queues/failed',
                    searchable: false,
                    emptyMessage: 'Excellent! Dead-Letter Queue is empty. 0 failures.',
                    columns: [
                        { key: 'uuid', title: 'UUID', sortable: true, priority: 'medium', render: function(row) {
                            return `<span style="font-family:monospace; font-size:0.8rem; font-weight:bold; color:var(--text-secondary);">${row.uuid}</span>`;
                        }},
                        { key: 'job_name', title: 'Job Class', sortable: true, priority: 'high', render: function(row) {
                            return `<strong style="color:var(--accent-color);">${queuesTable.escape(row.job_name)}</strong>`;
                        }},
                        { key: 'queue', title: 'Origin Queue', sortable: true, priority: 'medium', align: 'center', render: function(row) {
                            return `<span class="badge-pill-compact" style="background:rgba(37,99,235,0.08); color:var(--accent-color);">${row.queue}</span>`;
                        }},
                        { key: 'exception', title: 'Diagnostic Error', sortable: false, priority: 'high', render: function(row) {
                            return `
                                <div class="text-ellipsis-2 enterprise-tooltip" data-tooltip="${queuesTable.escape(row.exception)}" style="font-size:0.75rem; color:#ef4444; font-family:monospace; cursor:help;">
                                    ${queuesTable.escape(row.exception)}
                                </div>
                            `;
                        }},
                        { key: 'failed_at', title: 'Failed Time', sortable: true, priority: 'medium', render: function(row) {
                            return `<span style="font-size:0.8rem; color:var(--text-secondary);">${row.failed_at}</span>`;
                        }},
                        { key: 'actions', title: 'Actions', sortable: false, priority: 'high', align: 'center', render: function(row) {
                            return `
                                <div style="display:flex; gap:0.35rem; justify-content:center; align-items:center;">
                                    <button class="enterprise-action-icon-btn enterprise-action-icon-btn-success btn-queue-retry enterprise-tooltip" data-tooltip="Retry Task" data-uuid="${row.uuid}"><i class="fas fa-redo"></i></button>
                                    <button class="enterprise-action-icon-btn enterprise-action-icon-btn-danger btn-queue-delete enterprise-tooltip" data-tooltip="Forget Failure" data-uuid="${row.uuid}"><i class="fas fa-trash-alt"></i></button>
                                </div>
                            `;
                        }}
                    ]
                });
            } else {
                queuesTable.refresh();
            }
        }

        // Retry single job
        $(document).on('click', '.btn-queue-retry', function() {
            const uuid = $(this).data('uuid');
            const btn = $(this);
            btn.prop('disabled', true).text('Retrying...');

            $.ajax({
                url: `/api/admin/queues/failed/${uuid}/retry`,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    showToast(res.message, 'success');
                    loadQueueDashboard(currentQueuePage);
                },
                error: function(xhr) {
                    btn.prop('disabled', false).text('Retry');
                    showToast(xhr.responseJSON?.message || 'Failed to retry job.', 'error');
                }
            });
        });

        // Forget single job
        $(document).on('click', '.btn-queue-delete', function() {
            if (!confirm('Are you sure you want to permanently delete this failed job from the Dead-Letter Queue?')) return;
            const uuid = $(this).data('uuid');
            
            $.ajax({
                url: `/api/admin/queues/failed/${uuid}`,
                method: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    showToast(res.message, 'success');
                    loadQueueDashboard(currentQueuePage);
                },
                error: function(xhr) {
                    showToast(xhr.responseJSON?.message || 'Failed to delete job.', 'error');
                }
            });
        });

        // Retry all failures
        $('#btn-queues-retry-all').on('click', function() {
            const btn = $(this);
            if (!confirm('Are you sure you want to retry all failed jobs currently in the Dead-Letter Queue?')) return;
            btn.prop('disabled', true).html('Dispatching...');

            $.ajax({
                url: '/api/admin/queues/failed/retry-all',
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    btn.prop('disabled', false).html('<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg> Retry All Failures');
                    showToast(res.message, 'success');
                    loadQueueDashboard(1);
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg> Retry All Failures');
                    showToast(xhr.responseJSON?.message || 'Failed to retry jobs.', 'error');
                }
            });
        });

        // Flush all failures
        $('#btn-queues-clear-all').on('click', function() {
            const btn = $(this);
            if (!confirm('WARNING: Are you absolutely sure you want to permanently clear all failed jobs from the Dead-Letter Queue? This action cannot be undone.')) return;
            btn.prop('disabled', true).html('Purging...');

            $.ajax({
                url: '/api/admin/queues/failed/flush',
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    btn.prop('disabled', false).html('<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg> Flush DLQ Store');
                    showToast(res.message, 'success');
                    loadQueueDashboard(1);
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg> Flush DLQ Store');
                    showToast(xhr.responseJSON?.message || 'Failed to flush jobs.', 'error');
                }
            });
        });

        // ===================================================================
        // 8. AUDIT LOGS DISPLAY
        // ===================================================================
        let auditLogsTable;
        function loadAuditLogs(page) {
            if (!auditLogsTable) {
                auditLogsTable = new EnterpriseDataTable('#audit-logs-datatable', {
                    url: '/api/admin/activity-logs',
                    searchable: false,
                    dataKey: 'logs',
                    columns: [
                        { key: 'created_at', title: 'Timestamp', sortable: true, priority: 'high', render: function(row) {
                            return `<span style="font-size:0.8rem; color:var(--text-secondary);">${row.created_at ? row.created_at.substring(0, 19).replace('T', ' ') : 'N/A'}</span>`;
                        }},
                        { key: 'user', title: 'Actor User', sortable: false, priority: 'high', render: function(row) {
                            return `<strong>${row.user ? auditLogsTable.escape(row.user.name) : 'System / Guest'}</strong>`;
                        }},
                        { key: 'ip_address', title: 'IP Address', sortable: false, priority: 'medium', render: function(row) {
                            return `<span style="font-family:monospace; font-size:0.8rem;">${auditLogsTable.escape(row.ip_address)}</span>`;
                        }},
                        { key: 'action', title: 'Action Event', sortable: true, priority: 'high', align: 'center', render: function(row) {
                            return `<span class="badge-pill-compact" style="background:rgba(37,99,235,0.08); color:var(--accent-color);">${auditLogsTable.escape(row.action)}</span>`;
                        }},
                        { key: 'details', title: 'Details / Payload Trace', sortable: false, priority: 'medium', render: function(row) {
                            return `
                                <div class="text-ellipsis-2 enterprise-tooltip" data-tooltip="${auditLogsTable.escape(row.details)}" style="font-size:0.85rem; color:var(--text-secondary); cursor:help;">
                                    ${auditLogsTable.escape(row.details || 'N/A')}
                                </div>
                            `;
                        }}
                    ]
                });
            } else {
                auditLogsTable.refresh();
            }
        }

        // ===================================================================
        // COMPONENT UTILS: PAGINATION BUILDER
        // ===================================================================
        function buildPagination(selector, currentPage, lastPage, clickCallback) {
            const container = $(selector);
            container.empty();

            if (lastPage <= 1) return;

            let html = '';
            
            // Prev Button
            if (currentPage > 1) {
                html += `<button class="pagination-btn" data-page="${currentPage - 1}">&laquo; Prev</button>`;
            }

            // Page numbers
            for (let i = 1; i <= lastPage; i++) {
                const activeClass = i === currentPage ? 'active' : '';
                html += `<button class="pagination-btn ${activeClass}" data-page="${i}">${i}</button>`;
            }

            // Next Button
            if (currentPage < lastPage) {
                html += `<button class="pagination-btn" data-page="${currentPage + 1}">Next &raquo;</button>`;
            }

            container.html(html);

            // Bind click events
            container.find('.pagination-btn').on('click', function(e) {
                e.preventDefault();
                const page = $(this).data('page');
                clickCallback(page);
            });
        }

        // ─── AI CONTENT MANAGER ACTIONS & LOADER ─────────────────────────────

        let aiContentsCache = [];
        let aiContentTable;
        window.loadAiContentData = function(page = 1) {
            if (!aiContentTable) {
                aiContentTable = new EnterpriseDataTable('#ai-management-datatable', {
                    url: '/api/admin/ai-contents',
                    searchPlaceholder: 'Search job title...',
                    pageSize: 20,
                    filters: [
                        { name: 'status', label: 'Status', value: 'pending', options: [
                            { value: 'pending', label: 'Pending Review' },
                            { value: 'approved', label: 'Approved & Live' },
                            { value: 'rejected', label: 'Rejected Drafts' }
                        ]}
                    ],
                    onLoad: function(res, rows) {
                        aiContentsCache = rows;
                        // Telemetry Stats Update
                        const data = res.data;
                        const tel = data.telemetry;
                        $('#ai-telemetry-engine').text(tel.active_provider.toUpperCase());
                        $('#ai-stat-total').text(tel.total_generated);
                        $('#ai-stat-pending').text(tel.pending_count);
                        $('#ai-stat-approved').text(tel.approved_count);
                        $('#ai-stat-rejected').text(tel.rejected_count);
                    },
                    columns: [
                        { key: 'job_post_id', title: 'Post ID', sortable: true, priority: 'low' },
                        { key: 'title', title: 'Recruitment Title', sortable: true, priority: 'high', render: function(row) {
                            return `
                                <div class="enterprise-primary-stack">
                                    <span class="enterprise-primary-value">${aiContentTable.escape(row.job_post ? row.job_post.title : 'Deleted Post')}</span>
                                    <span class="enterprise-secondary-metadata">Provider: ${aiContentTable.escape(row.provider)}</span>
                                </div>
                            `;
                        }},
                        { key: 'provider', title: 'AI Engine', sortable: true, priority: 'medium', align: 'center', render: function(row) {
                            return `<span class="badge-pill-compact" style="background:rgba(139,92,246,0.1); color:#8b5cf6;">${aiContentTable.escape(row.provider)}</span>`;
                        }},
                        { key: 'status', title: 'Draft Status', sortable: true, priority: 'high', align: 'center', render: function(row) {
                            let statusClass = 'background:rgba(156,163,175,0.1); color:var(--text-primary);';
                            if (row.status === 'approved') statusClass = 'background:rgba(16,185,129,0.12); color:#10b981;';
                            if (row.status === 'rejected') statusClass = 'background:rgba(239,68,68,0.12); color:#ef4444;';
                            let errMsg = row.error_message;
                            if (errMsg && errMsg.toLowerCase().includes('api key')) {
                                errMsg += ' (Configure in Settings Management > SMTP & APIs)';
                            }
                            const errorIndicator = errMsg 
                                ? `<span style="color:#ef4444; font-size:0.75rem; margin-left:0.35rem; cursor:help;" class="enterprise-tooltip" data-tooltip="${aiContentTable.escape(errMsg)}">⚠️ Error</span>` 
                                : '';
                            return `<span class="badge-pill-compact" style="${statusClass}">${aiContentTable.escape(row.status)}</span>${errorIndicator}`;
                        }},
                        { key: 'created_at', title: 'Creation Date', sortable: true, priority: 'medium', render: function(row) {
                            return new Date(row.created_at).toLocaleDateString('en-IN', {day: '2-digit', month: 'short', year: 'numeric'});
                        }},
                        { key: 'actions', title: 'Actions', sortable: false, priority: 'high', align: 'center', render: function(row) {
                            return `
                                <button class="enterprise-action-icon-btn enterprise-action-icon-btn-view btn-review-ai enterprise-tooltip" data-tooltip="Review & Edit Draft" data-id="${row.id}"><i class="fas fa-eye"></i></button>
                            `;
                        }}
                    ]
                });
            } else {
                aiContentTable.refresh();
            }
        };

        // Filter button trigger
        $('#btn-ai-filter-trigger').on('click', function(e) {
            e.preventDefault();
            loadAiContentData(1);
        });

        // Review Drawer Trigger
        $(document).on('click', '.btn-review-ai', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            const item = aiContentsCache.find(x => String(x.id) === String(id));
            
            if (!item) return;

            // Seed drawer fields
            $('#ai-review-id').val(item.id);
            $('#ai-review-post-id').val(item.job_post_id);
            $('#ai-review-title').text(item.job_post ? item.job_post.title : 'Deleted Posting');
            $('#ai-review-provider-select').val(item.provider);
            
            $('#ai-edit-summary').val(item.summary || '');
            $('#ai-edit-eligibility').val(item.eligibility || '');
            $('#ai-edit-selection').val(item.selection_process || '');
            
            $('#ai-edit-meta-title').val(item.meta_title || '');
            $('#ai-edit-meta-desc').val(item.meta_description || '');

            // Load diagnostic error if present
            if (item.error_message) {
                $('#ai-drawer-error-text').text(item.error_message);
                $('#ai-drawer-error-alert').slideDown(200);
            } else {
                $('#ai-drawer-error-alert').hide();
            }

            // Seed FAQs
            const faqContainer = $('#ai-edit-faqs-container');
            faqContainer.empty();
            const faqs = Array.isArray(item.faqs) ? item.faqs : [];
            
            faqs.forEach(function(faq, idx) {
                appendFaqInputs(faq.question, faq.answer);
            });

            if (faqs.length === 0) {
                appendFaqInputs('', ''); // Seed one blank block
            }

            // Open slide drawer
            $('#admin-drawer-backdrop').fadeIn(200);
            $('#ai-review-drawer').css('right', '0px');
        });

        function escapeHtml(text) {
            if (!text) return '';
            return String(text)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        let vacancyCounter = 0;
        function addVacancyRow(postName = '', totalPost = '', eligibility = '', sortOrder = null) {
            const container = $('#admin-vacancy-details-container');
            const idx = vacancyCounter++;
            const order = sortOrder !== null ? sortOrder : container.children().length;
            const html = `
                <div class="vacancy-row-item glass-panel" style="padding: 1rem; border-radius: 8px; border: 1px solid var(--border-color); margin-bottom: 0.75rem; position: relative;" data-order="${order}">
                    <div style="position: absolute; top: 0.5rem; right: 0.5rem; display: flex; gap: 0.25rem;">
                        <button type="button" class="btn-sm-secondary btn-move-vacancy-up" style="padding: 0.15rem 0.35rem; font-size: 0.75rem; cursor:pointer;"><i class="fas fa-chevron-up"></i></button>
                        <button type="button" class="btn-sm-secondary btn-move-vacancy-down" style="padding: 0.15rem 0.35rem; font-size: 0.75rem; cursor:pointer;"><i class="fas fa-chevron-down"></i></button>
                        <button type="button" class="btn-sm-danger btn-remove-vacancy-row" style="padding: 0.15rem 0.35rem; font-size: 0.75rem; cursor:pointer;">&times; Remove</button>
                    </div>
                    <div class="form-group" style="margin-bottom: 0.5rem; margin-top: 1.5rem;">
                        <label style="font-size: 0.75rem; font-weight: 700;">Post Name</label>
                        <input type="text" name="vacancy_details[${idx}][post_name]" class="form-control vacancy-post-name" value="${escapeHtml(postName)}" placeholder="e.g. Junior Engineer" required style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">
                    </div>
                    <div class="form-group" style="margin-bottom: 0.5rem;">
                        <label style="font-size: 0.75rem; font-weight: 700;">Total Post</label>
                        <input type="number" name="vacancy_details[${idx}][total_post]" class="form-control vacancy-total-post" value="${totalPost}" placeholder="e.g. 120" required min="0" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="font-size: 0.75rem; font-weight: 700;">Eligibility Details</label>
                        <textarea name="vacancy_details[${idx}][eligibility]" class="form-control vacancy-eligibility" rows="2" placeholder="e.g. Diploma in Civil Engineering" required style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">${escapeHtml(eligibility)}</textarea>
                    </div>
                    <input type="hidden" name="vacancy_details[${idx}][sort_order]" class="vacancy-sort-order" value="${order}">
                </div>
            `;
            container.append(html);
        }

        let categoryVacancyCounter = 0;
        function addCategoryVacancyRow(data = {}) {
            const container = $('#admin-category-wise-container');
            const idx = categoryVacancyCounter++;
            const order = data.sort_order !== undefined ? data.sort_order : container.children().length;
            const postName = data.post_name || '';
            const ur = data.ur !== undefined ? data.ur : 0;
            const ews = data.ews !== undefined ? data.ews : 0;
            const ebc = data.ebc !== undefined ? data.ebc : 0;
            const bc = data.bc !== undefined ? data.bc : 0;
            const bcFemale = data.bc_female !== undefined ? data.bc_female : 0;
            const sc = data.sc !== undefined ? data.sc : 0;
            const st = data.st !== undefined ? data.st : 0;
            const total = data.total !== undefined ? data.total : 0;

            const html = `
                <div class="category-vacancy-row-item glass-panel" style="padding: 1rem; border-radius: 8px; border: 1px solid var(--border-color); margin-bottom: 0.75rem; position: relative;" data-order="${order}">
                    <div style="position: absolute; top: 0.5rem; right: 0.5rem; display: flex; gap: 0.25rem;">
                        <button type="button" class="btn-sm-secondary btn-move-cat-up" style="padding: 0.15rem 0.35rem; font-size: 0.75rem; cursor:pointer;"><i class="fas fa-chevron-up"></i></button>
                        <button type="button" class="btn-sm-secondary btn-move-cat-down" style="padding: 0.15rem 0.35rem; font-size: 0.75rem; cursor:pointer;"><i class="fas fa-chevron-down"></i></button>
                        <button type="button" class="btn-sm-danger btn-remove-cat-row" style="padding: 0.15rem 0.35rem; font-size: 0.75rem; cursor:pointer;">&times; Remove</button>
                    </div>
                    <div class="form-group" style="margin-bottom: 0.5rem; margin-top: 1.5rem;">
                        <label style="font-size: 0.75rem; font-weight: 700;">Post Name</label>
                        <input type="text" name="category_wise_vacancies[${idx}][post_name]" class="form-control cat-post-name" value="${escapeHtml(postName)}" placeholder="e.g. Junior Engineer" required style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">
                    </div>
                    
                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.5rem; margin-bottom: 0.5rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label style="font-size: 0.7rem; font-weight: 700;">UR</label>
                            <input type="number" name="category_wise_vacancies[${idx}][ur]" class="form-control cat-val cat-ur" value="${ur}" required min="0" style="padding: 0.3rem; font-size: 0.8rem;">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label style="font-size: 0.7rem; font-weight: 700;">EWS</label>
                            <input type="number" name="category_wise_vacancies[${idx}][ews]" class="form-control cat-val cat-ews" value="${ews}" required min="0" style="padding: 0.3rem; font-size: 0.8rem;">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label style="font-size: 0.7rem; font-weight: 700;">EBC</label>
                            <input type="number" name="category_wise_vacancies[${idx}][ebc]" class="form-control cat-val cat-ebc" value="${ebc}" required min="0" style="padding: 0.3rem; font-size: 0.8rem;">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label style="font-size: 0.7rem; font-weight: 700;">BC</label>
                            <input type="number" name="category_wise_vacancies[${idx}][bc]" class="form-control cat-val cat-bc" value="${bc}" required min="0" style="padding: 0.3rem; font-size: 0.8rem;">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.5rem; margin-bottom: 0.5rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label style="font-size: 0.7rem; font-weight: 700;">BC (F)</label>
                            <input type="number" name="category_wise_vacancies[${idx}][bc_female]" class="form-control cat-val cat-bc-female" value="${bcFemale}" required min="0" style="padding: 0.3rem; font-size: 0.8rem;">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label style="font-size: 0.7rem; font-weight: 700;">SC</label>
                            <input type="number" name="category_wise_vacancies[${idx}][sc]" class="form-control cat-val cat-sc" value="${sc}" required min="0" style="padding: 0.3rem; font-size: 0.8rem;">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label style="font-size: 0.7rem; font-weight: 700;">ST</label>
                            <input type="number" name="category_wise_vacancies[${idx}][st]" class="form-control cat-val cat-st" value="${st}" required min="0" style="padding: 0.3rem; font-size: 0.8rem;">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label style="font-size: 0.7rem; font-weight: 700;">Total</label>
                            <input type="number" name="category_wise_vacancies[${idx}][total]" class="form-control cat-total" value="${total}" required min="0" style="padding: 0.3rem; font-size: 0.8rem; border-color: var(--accent-color); font-weight: bold;">
                        </div>
                    </div>

                    <input type="hidden" name="category_wise_vacancies[${idx}][sort_order]" class="cat-sort-order" value="${order}">
                </div>
            `;
            container.append(html);
        }

        function reindexVacancyOrders() {
            $('#admin-vacancy-details-container').children('.vacancy-row-item').each(function(index) {
                $(this).attr('data-order', index);
                $(this).find('.vacancy-sort-order').val(index);
            });
        }

        function reindexCatOrders() {
            $('#admin-category-wise-container').children('.category-vacancy-row-item').each(function(index) {
                $(this).attr('data-order', index);
                $(this).find('.cat-sort-order').val(index);
            });
        }

        // Bind events for dynamically adding vacancy rows
        $('#btn-admin-add-vacancy').on('click', function(e) {
            e.preventDefault();
            addVacancyRow();
        });

        $('#btn-admin-add-cat-vacancy').on('click', function(e) {
            e.preventDefault();
            addCategoryVacancyRow();
        });

        // Remove row handlers
        $(document).on('click', '.btn-remove-vacancy-row', function(e) {
            e.preventDefault();
            $(this).closest('.vacancy-row-item').slideUp(200, function() {
                $(this).remove();
                reindexVacancyOrders();
            });
        });

        $(document).on('click', '.btn-remove-cat-row', function(e) {
            e.preventDefault();
            $(this).closest('.category-vacancy-row-item').slideUp(200, function() {
                $(this).remove();
                reindexCatOrders();
            });
        });

        // Reorder Up/Down handlers
        $(document).on('click', '.btn-move-vacancy-up', function(e) {
            e.preventDefault();
            const current = $(this).closest('.vacancy-row-item');
            const prev = current.prev('.vacancy-row-item');
            if (prev.length > 0) {
                current.insertBefore(prev);
                reindexVacancyOrders();
            }
        });

        $(document).on('click', '.btn-move-vacancy-down', function(e) {
            e.preventDefault();
            const current = $(this).closest('.vacancy-row-item');
            const next = current.next('.vacancy-row-item');
            if (next.length > 0) {
                current.insertAfter(next);
                reindexVacancyOrders();
            }
        });

        $(document).on('click', '.btn-move-cat-up', function(e) {
            e.preventDefault();
            const current = $(this).closest('.category-vacancy-row-item');
            const prev = current.prev('.category-vacancy-row-item');
            if (prev.length > 0) {
                current.insertBefore(prev);
                reindexCatOrders();
            }
        });

        $(document).on('click', '.btn-move-cat-down', function(e) {
            e.preventDefault();
            const current = $(this).closest('.category-vacancy-row-item');
            const next = current.next('.category-vacancy-row-item');
            if (next.length > 0) {
                current.insertAfter(next);
                reindexCatOrders();
            }
        });

        // Auto sum categories to Total field
        $(document).on('input', '.cat-val', function() {
            const block = $(this).closest('.category-vacancy-row-item');
            let sum = 0;
            block.find('.cat-val').each(function() {
                sum += parseInt($(this).val()) || 0;
            });
            block.find('.cat-total').val(sum);
        });

        // FAQ input builder helper
        function appendFaqInputs(question = '', answer = '') {
            const faqContainer = $('#ai-edit-faqs-container');
            const index = faqContainer.children().length;
            const faqBlock = `
                <div class="faq-input-block glass-panel" style="padding: 1rem; border-radius: 8px; position: relative; margin-bottom: 0.5rem;">
                    <button type="button" class="btn-sm-danger btn-ai-remove-faq" style="position: absolute; top: 0.5rem; right: 0.5rem; padding: 0.15rem 0.4rem; font-size: 0.7rem; border-radius: 4px;">&times; Remove</button>
                    <div class="form-group" style="margin-bottom: 0.5rem;">
                        <label style="font-size: 0.75rem; font-weight: 700;">FAQ Question</label>
                        <input type="text" class="form-control faq-question" value="${escapeHtml(question)}" placeholder="e.g. When is the exam date?" required style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label style="font-size: 0.75rem; font-weight: 700;">FAQ Answer</label>
                        <textarea class="form-control faq-answer" rows="2" placeholder="e.g. The exam date is scheduled for October." required style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">${escapeHtml(answer)}</textarea>
                    </div>
                </div>
            `;
            faqContainer.append(faqBlock);
        }

        // Add FAQ button click
        $('#btn-ai-add-faq').on('click', function(e) {
            e.preventDefault();
            appendFaqInputs('', '');
        });

        // Remove FAQ button click
        $(document).on('click', '.btn-ai-remove-faq', function(e) {
            e.preventDefault();
            $(this).closest('.faq-input-block').slideUp(200, function() {
                $(this).remove();
            });
        });

        // Close AI drawer
        $('#close-ai-drawer, #admin-drawer-backdrop').on('click', function(e) {
            if (e.target === this || $(this).attr('id') === 'close-ai-drawer' || $(this).attr('id') === 'admin-drawer-backdrop') {
                $('#ai-review-drawer').css('right', '-650px');
                $('#admin-drawer-backdrop').fadeOut(200);
            }
        });

        // Approve action from drawer
        $('#btn-ai-drawer-approve').on('click', function(e) {
            e.preventDefault();
            const id = $('#ai-review-id').val();
            const btn = $(this);
            
            btn.prop('disabled', true).text('Approving...');

            $.ajax({
                url: `/api/admin/ai-contents/${id}/approve`,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    showToast(res.message, 'success');
                    $('#ai-review-drawer').css('right', '-650px');
                    $('#admin-drawer-backdrop').fadeOut(200);
                    loadAiContentData(aiContentTable ? aiContentTable.currentPage : 1);
                },
                error: function(err) {
                    showToast('Failed to publish verification.', 'error');
                },
                complete: function() {
                    btn.prop('disabled', false).text('Publish Verification');
                }
            });
        });

        // Decline/Reject action from drawer
        $('#btn-ai-drawer-reject').on('click', function(e) {
            e.preventDefault();
            const id = $('#ai-review-id').val();
            const btn = $(this);
            
            btn.prop('disabled', true).text('Rejecting...');

            $.ajax({
                url: `/api/admin/ai-contents/${id}/reject`,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    showToast(res.message, 'success');
                    $('#ai-review-drawer').css('right', '-650px');
                    $('#admin-drawer-backdrop').fadeOut(200);
                    loadAiContentData(aiContentTable ? aiContentTable.currentPage : 1);
                },
                error: function() {
                    showToast('Failed to reject announcement.', 'error');
                },
                complete: function() {
                    btn.prop('disabled', false).text('Reject Announcement');
                }
            });
        });

        // Regenerate Draft action
        $('#btn-ai-regenerate').on('click', function(e) {
            e.preventDefault();
            const postId = $('#ai-review-post-id').val();
            const provider = $('#ai-review-provider-select').val();
            const btn = $(this);
            
            btn.prop('disabled', true).text('Verifying...');

            $.ajax({
                url: `/api/admin/ai-contents/generate/${postId}`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    provider: provider
                },
                success: function(res) {
                    showToast(res.message, 'success');
                    $('#ai-review-drawer').css('right', '-650px');
                    $('#admin-drawer-backdrop').fadeOut(200);
                    loadAiContentData(1);
                },
                error: function() {
                    showToast('Failed to trigger verification task.', 'error');
                },
                complete: function() {
                    btn.prop('disabled', false).text('⚡ Regenerate Verification');
                }
            });
        });

        // Save manual changes form
        $('#ajax-ai-review-form').on('submit', function(e) {
            e.preventDefault();
            const id = $('#ai-review-id').val();
            
            // Gather FAQs array
            const faqs = [];
            $('#ai-edit-faqs-container .faq-input-block').each(function() {
                const question = $(this).find('.faq-question').val();
                const answer = $(this).find('.faq-answer').val();
                if (question.trim() && answer.trim()) {
                    faqs.push({ question: question, answer: answer });
                }
            });

            const data = {
                _token: '{{ csrf_token() }}',
                summary: $('#ai-edit-summary').val(),
                eligibility: $('#ai-edit-eligibility').val(),
                selection_process: $('#ai-edit-selection').val(),
                faqs: faqs,
                meta_title: $('#ai-edit-meta-title').val(),
                meta_description: $('#ai-edit-meta-desc').val(),
            };

            const submitBtn = $(this).find('button[type="submit"]');
            submitBtn.prop('disabled', true).text('Saving Changes...');

            $.ajax({
                url: `/api/admin/ai-contents/${id}/update`,
                method: 'POST',
                data: data,
                success: function(res) {
                    showToast(res.message, 'success');
                    $('#ai-review-drawer').css('right', '-650px');
                    $('#admin-drawer-backdrop').fadeOut(200);
                    loadAiContentData(aiContentTable ? aiContentTable.currentPage : 1);
                },
                error: function(err) {
                    if (err.status === 422) {
                        showToast('Validation failed. Please check field inputs.', 'error');
                    } else {
                        showToast('Failed to save manual changes.', 'error');
                    }
                },
                complete: function() {
                    submitBtn.prop('disabled', false).text('Save Draft Changes Only');
                }
            });
        });

        // Hook Generate/Regenerate button inside Direct Job Registry table (adding a manual action inside Step 5)
        // This makes manual triggers on normal job posts very easy from the main postings list!
        $(document).on('click', '.btn-trigger-ai-gen', function(e) {
            e.preventDefault();
            const postId = $(this).data('id');
            const btn = $(this);

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin" style="margin-right: 4px; font-size: 0.75rem;"></i> Verifying...');
            $.ajax({
                url: `/api/admin/ai-contents/generate/${postId}`,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    showToast(res.message, 'success');
                },
                error: function() {
                    showToast('Failed to queue verification task.', 'error');
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="fas fa-check-circle" style="margin-right: 4px; font-size: 0.75rem;"></i> Verify Listing');
                }
            });
        });

        // ─── END AI CONTENT MANAGER ──────────────────────────────────────────

        // ─── EMAIL AUTOMATION WORKSPACE ───
        let marketingTable;
        window.loadMarketingDashboard = function(page = 1) {
            // 1. Fetch Stats
            $.ajax({
                url: '/api/admin/marketing/stats',
                method: 'GET',
                success: function(res) {
                    if (res.status === 'success') {
                        const o = res.data.overall;
                        $('#mkt-stat-sent').text(o.sent.toLocaleString());
                        $('#mkt-stat-open-rate').text(o.open_rate + '%');
                        $('#mkt-stat-opened').text(o.opened.toLocaleString() + ' total opens logged');
                        $('#mkt-stat-ctr').text(o.ctr + '%');
                        $('#mkt-stat-clicked').text(o.clicked.toLocaleString() + ' total clicks logged');
                        $('#mkt-stat-failed').text(o.failed.toLocaleString());

                        // Render Breakdown Progress Bars
                        let html = '';
                        if (res.data.campaigns.length === 0) {
                            html = '<div style="text-align:center; color:var(--text-secondary); padding: 1rem 0;">No active dispatches tracked yet.</div>';
                        } else {
                            res.data.campaigns.forEach(c => {
                                let label = c.campaign_type;
                                if (label.startsWith('welcome_')) label = 'Welcome Series ' + label.replace('welcome_', 'Part ');
                                else label = label.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');

                                html += `
                                    <div style="margin-bottom: 1rem;">
                                        <div style="display:flex; justify-content:space-between; font-size:0.85rem; margin-bottom:0.4rem; font-weight:600;">
                                            <span>${label} <span style="font-size:0.75rem; font-weight:normal; color:var(--text-secondary);">(${c.sent.toLocaleString()} sent)</span></span>
                                            <span>Opens: ${c.open_rate}% | CTR: ${c.ctr}%</span>
                                        </div>
                                        <div style="width:100%; height:8px; background:var(--border-color); border-radius:99px; overflow:hidden; display:flex;">
                                            <div style="width:${c.open_rate}%; height:100%; background:#10b981; transition:width 0.4s ease;" title="Open Rate: ${c.open_rate}%"></div>
                                            <div style="width:${c.ctr}%; height:100%; background:#f59e0b; transition:width 0.4s ease;" title="Click CTR: ${c.ctr}%"></div>
                                        </div>
                                    </div>
                                `;
                            });
                        }
                        $('#mkt-campaigns-list').html(html);
                    }
                }
            });

            // 2. Fetch Logs
            if (!marketingTable) {
                marketingTable = new EnterpriseDataTable('#mkt-logs-datatable', {
                    url: '/api/admin/marketing/logs',
                    searchPlaceholder: 'Search recipients...',
                    pageSize: 20,
                    columns: [
                        { key: 'id', title: 'Log ID', sortable: true, priority: 'low' },
                        { key: 'recipient', title: 'Recipient Address', sortable: false, priority: 'high', render: function(row) {
                            const email = row.user ? `${row.user.name} <${row.user.email}>` : row.subscriber_email;
                            return `<strong>${marketingTable.escape(email)}</strong>`;
                        }},
                        { key: 'campaign_type', title: 'Campaign Type', sortable: true, priority: 'high', render: function(row) {
                            let typeLabel = row.campaign_type;
                            if (typeLabel.startsWith('welcome_')) typeLabel = 'Welcome Series ' + typeLabel.replace('welcome_', 'Part ');
                            else typeLabel = typeLabel.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
                            return `<span class="badge-pill-compact" style="background:rgba(100,116,139,0.08); color:var(--text-primary);">${marketingTable.escape(typeLabel)}</span>`;
                        }},
                        { key: 'status', title: 'Status', sortable: true, priority: 'high', align: 'center', render: function(row) {
                            if (row.status === 'sent') {
                                return '<span class="badge-pill-compact" style="background:rgba(16,185,129,0.12); color:#10b981;">Delivered</span>';
                            } else if (row.status === 'failed') {
                                return `<span class="badge-pill-compact enterprise-tooltip" style="background:rgba(239,68,68,0.12); color:#ef4444; cursor:help;" data-tooltip="${marketingTable.escape(row.error_message || 'SMTP Crash')}">Failed ⚠️</span>`;
                            } else {
                                return '<span class="badge-pill-compact" style="background:rgba(37,99,235,0.12); color:#2563eb;">Queued</span>';
                            }
                        }},
                        { key: 'telemetry', title: 'Telemetry Tracker', sortable: false, priority: 'medium', render: function(row) {
                            const opened = row.opened_at ? '<span style="color:#10b981; font-weight:bold; margin-right:8px;" class="enterprise-tooltip" data-tooltip="Opened at ' + row.opened_at + '">👁️ Opened</span>' : '<span style="color:var(--text-secondary); margin-right:8px;">👁️ -</span>';
                            const clicked = row.clicked_at ? '<span style="color:#f59e0b; font-weight:bold;" class="enterprise-tooltip" data-tooltip="Clicked at ' + row.clicked_at + '">🖱️ Clicked</span>' : '<span style="color:var(--text-secondary);">🖱️ -</span>';
                            return `<div style="display:flex; align-items:center;">${opened} ${clicked}</div>`;
                        }},
                        { key: 'created_at', title: 'Sent Time', sortable: true, priority: 'medium', render: function(row) {
                            return new Date(row.created_at).toLocaleString();
                        }}
                    ]
                });
            } else {
                marketingTable.refresh();
            }
        };

        // Trigger manual test form submission
        $('#mkt-test-form').on('submit', function(e) {
            e.preventDefault();
            const submitBtn = $('#mkt-test-submit');
            const originalText = submitBtn.text();

            submitBtn.prop('disabled', true).text('Dispatching onto Queue...');

            $.ajax({
                url: '/api/admin/marketing/trigger-test',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    email: $('#mkt-test-email').val(),
                    campaign_type: $('#mkt-test-campaign').val(),
                },
                success: function(res) {
                    showToast(res.message, 'success');
                    $('#mkt-test-email').val(''); // Clear target
                    
                    // Reload dashboard statistics and logs list after 1 second delay
                    setTimeout(() => {
                        loadMarketingDashboard(1);
                    }, 1000);
                },
                error: function(err) {
                    showToast('Failed to trigger test email.', 'error');
                },
                complete: function() {
                    submitBtn.prop('disabled', false).text(originalText);
                }
            });
        });
        // ==========================================
        // DYNAMIC SETTINGS MANAGEMENT MODULE JS CODE
        // ==========================================

        // Utility to safely resolve asset URLs without double leading slashes
        function resolveAssetUrl(path) {
            if (!path) return '';
            if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('//')) {
                return path;
            }
            const cleanPath = path.startsWith('/') ? path.substring(1) : path;
            return '/' + cleanPath;
        }

        // Settings nested sub-tabs toggling
        $(document).on('click', '.settings-sub-trigger', function() {
            $('.settings-sub-trigger').removeClass('active');
            $(this).addClass('active');

            const targetTab = $(this).data('target');
            $('.settings-sub-panel').hide();
            $(`#${targetTab}`).fadeIn(300);

            // Fetch specific data if needed
            if (targetTab === 'settings-layout') {
                loadMenusList();
            } else if (targetTab === 'settings-operations') {
                loadCmsPagesList();
                loadAdSlots();
            } else if (targetTab === 'settings-security') {
                loadBackupsList();
            } else if (targetTab === 'settings-media') {
                loadMediaExplorer('');
            }
        });

        // Toggle maintenance message display depending on maintenance mode select state
        $('#cfg-maintenance-mode').on('change', function() {
            if ($(this).val() === '1') {
                $('#maintenance-msg-group').slideDown();
            } else {
                $('#maintenance-msg-group').slideUp();
            }
        });

        // 1. Fetch settings data from backend
        window.loadSettingsData = function() {
            $.ajax({
                url: '/api/admin/settings',
                method: 'GET',
                success: function(res) {
                    if (res.status === 'success') {
                        const data = res.data;
                        
                        // Populate Site Settings (General)
                        if (data.general) {
                            $.each(data.general, function(key, val) {
                                const input = $(`[name="${key}"]`);
                                if (input.length) {
                                    if (input.is('select') || input.is('textarea') || input.attr('type') === 'text' || input.attr('type') === 'email') {
                                        input.val(val);
                                    } else if (input.is('input[type="checkbox"]') || input.attr('type') === 'checkbox') {
                                        input.prop('checked', val == 1);
                                    }
                                }
                            });
                            
                            // Trigger maintenance select change callback
                            $('#cfg-maintenance-mode').trigger('change');
                        }

                        // Logo Graphic previews
                        const logos = ['header_logo', 'footer_logo', 'favicon'];
                        logos.forEach(logoKey => {
                            const val = data.general ? data.general[logoKey] : null;
                            if (val) {
                                $(`#preview-${logoKey}`).attr('src', resolveAssetUrl(val)).show();
                                $(`#no-${logoKey}`).hide();
                            } else {
                                $(`#preview-${logoKey}`).hide();
                                $(`#no-${logoKey}`).show();
                            }
                        });

                        // Render Social link checkboxes/urls
                        let socialHtml = '';
                        if (data.social) {
                            data.social.forEach((link, idx) => {
                                socialHtml += `
                                    <div style="display:grid; grid-template-columns: 100px 1fr 60px; gap:0.5rem; align-items:center;">
                                        <span style="font-size:0.85rem; font-weight:600;">${link.platform}</span>
                                        <input type="text" name="links[${idx}][url]" value="${link.url || ''}" class="form-control" style="margin:0;" placeholder="https://...">
                                        <input type="hidden" name="links[${idx}][platform]" value="${link.platform}">
                                        <label class="custom-switch" style="position: relative; display: inline-block; width: 44px; height: 24px; margin: 0 auto;">
                                            <input type="checkbox" name="links[${idx}][is_active]" value="1" ${link.is_active ? 'checked' : ''} style="opacity: 0; width: 0; height: 0;" class="social-active-checkbox">
                                            <span class="slider-switch" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: var(--border-color); transition: .3s; border-radius: 24px;"></span>
                                        </label>
                                    </div>
                                `;
                            });
                        }
                        $('#social-links-inputs').html(socialHtml);

                        // Theme Settings
                        if (data.theme) {
                            $.each(data.theme, function(key, val) {
                                const input = $(`#theme-${key.replace('_color', '')}`);
                                if (input.length) input.val(val);
                                const picker = $(`#theme-${key.replace('_color', '')}-picker`);
                                if (picker.length) picker.val(val);
                            });
                        }

                        // SEO Settings
                        if (data.seo) {
                            $.each(data.seo, function(key, val) {
                                const target = $(`#admin-settings [name="${key}"]`);
                                if (target.length) target.val(val);
                            });
                        }

                        // Email Configs
                        if (data.email) {
                            $.each(data.email, function(key, val) {
                                const target = $(`#settings-smtp-form [name="${key}"]`);
                                if (target.length) target.val(val);
                            });
                        }

                        // API credentials
                        if (data.api) {
                            $.each(data.api, function(key, val) {
                                const target = $(`#settings-api-form [name="${key}"]`);
                                if (target.length) target.val(val);
                            });
                        }
                    }
                },
                error: function() {
                    showToast('Failed to retrieve settings matrix.', 'error');
                }
            });
        };

        // Sync pickers and color inputs
        $('#theme-primary-picker').on('input', function() { $('#theme-primary').val($(this).val()); });
        $('#theme-primary').on('input', function() { $('#theme-primary-picker').val($(this).val()); });
        $('#theme-secondary-picker').on('input', function() { $('#theme-secondary').val($(this).val()); });
        $('#theme-secondary').on('input', function() { $('#theme-secondary-picker').val($(this).val()); });

        // Submit General properties
        $('#settings-general-form').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');
            const originalText = submitBtn.text();
            submitBtn.prop('disabled', true).text('Saving Configs...');

            // Merge both General and Scripts forms to avoid missing fields validation errors
            const data = form.serialize() + '&' + $('#settings-scripts-form').serialize();

            $.ajax({
                url: '/api/admin/settings/general',
                method: 'POST',
                data: data,
                success: function(res) {
                    showToast(res.message, 'success');
                },
                error: function(err) {
                    showToast(err.responseJSON?.message || 'Failed to save general configurations.', 'error');
                },
                complete: function() {
                    submitBtn.prop('disabled', false).text(originalText);
                }
            });
        });

        // Submit Social platforms
        $('#settings-social-form').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');
            const originalText = submitBtn.text();
            submitBtn.prop('disabled', true).text('Synchronizing...');

            // Custom serialize to make sure checkboxes are sent correctly as 0 or 1
            const payload = {
                _token: '{{ csrf_token() }}',
                links: []
            };

            $('#social-links-inputs > div').each(function(idx, el) {
                const platform = $(el).find('input[type="hidden"]').val();
                const url = $(el).find('input[type="text"]').val();
                const is_active = $(el).find('.social-active-checkbox').is(':checked') ? 1 : 0;
                payload.links.push({ platform, url, is_active });
            });

            $.ajax({
                url: '/api/admin/settings/social',
                method: 'POST',
                data: payload,
                success: function(res) {
                    showToast(res.message, 'success');
                },
                error: function() {
                    showToast('Failed to save social platforms.', 'error');
                },
                complete: function() {
                    submitBtn.prop('disabled', false).text(originalText);
                }
            });
        });

        // Logo Upload handling
        $(document).on('change', '.logo-upload-input', function() {
            const input = $(this);
            const file = this.files[0];
            if (!file) return;

            const key = input.data('key');
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('key', key);
            formData.append('file', file);

            showToast('Uploading graphics asset...', 'info');

            $.ajax({
                url: '/api/admin/settings/logo',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.status === 'success') {
                        showToast(res.message, 'success');
                        $(`#preview-${key}`).attr('src', resolveAssetUrl(res.data.path)).show();
                        $(`#no-${key}`).hide();
                    }
                },
                error: function(err) {
                    showToast(err.responseJSON?.message || 'Failed to upload logo image.', 'error');
                }
            });
        });

        // Submit Theme configuration
        $('#settings-theme-form').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');
            const originalText = submitBtn.text();
            submitBtn.prop('disabled', true).text('Rebuilding layout...');

            $.ajax({
                url: '/api/admin/settings/theme',
                method: 'POST',
                data: form.serialize(),
                success: function(res) {
                    showToast(res.message, 'success');
                    // Reload index view to apply new dynamic colors immediately
                    setTimeout(() => {
                        location.reload();
                    }, 500);
                },
                error: function() {
                    showToast('Failed to update theme colors.', 'error');
                },
                complete: function() {
                    submitBtn.prop('disabled', false).text(originalText);
                }
            });
        });

        // Submit SMTP Setup
        $('#settings-smtp-form').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');
            const originalText = submitBtn.text();
            submitBtn.prop('disabled', true).text('Applying binding...');

            $.ajax({
                url: '/api/admin/settings/email',
                method: 'POST',
                data: form.serialize(),
                success: function(res) {
                    showToast(res.message, 'success');
                },
                error: function() {
                    showToast('Failed to apply SMTP credentials.', 'error');
                },
                complete: function() {
                    submitBtn.prop('disabled', false).text(originalText);
                }
            });
        });

        // Trigger SMTP Verification Test
        $('#btn-trigger-smtp-test').on('click', function() {
            const btn = $(this);
            const originalText = btn.text();
            const recipient = $('#smtp-test-recipient').val();

            if (!recipient) {
                showToast('Please specify a verification recipient email.', 'error');
                return;
            }

            btn.prop('disabled', true).text('Testing...');

            const payload = {
                _token: '{{ csrf_token() }}',
                smtp_host: $('#smtp-host').val(),
                smtp_port: $('#smtp-port').val(),
                smtp_username: $('#smtp-username').val(),
                smtp_password: $('#smtp-password').val(),
                smtp_encryption: $('#smtp-encryption').val(),
                sender_name: $('#smtp-sender-name').val(),
                sender_email: $('#smtp-sender-email').val(),
                test_recipient: recipient
            };

            $.ajax({
                url: '/api/admin/settings/email/test',
                method: 'POST',
                data: payload,
                success: function(res) {
                    showToast(res.message, 'success');
                },
                error: function(err) {
                    showToast(err.responseJSON?.message || 'SMTP Connection failed.', 'error');
                },
                complete: function() {
                    btn.prop('disabled', false).text(originalText);
                }
            });
        });

        // Submit API Credentials
        $('#settings-api-form').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');
            const originalText = submitBtn.text();
            submitBtn.prop('disabled', true).text('Encrypting credentials...');

            $.ajax({
                url: '/api/admin/settings/api',
                method: 'POST',
                data: form.serialize(),
                success: function(res) {
                    showToast(res.message, 'success');
                },
                error: function() {
                    showToast('Failed to save API credentials.', 'error');
                },
                complete: function() {
                    submitBtn.prop('disabled', false).text(originalText);
                }
            });
        });

        // Submit Custom Scripts (Sends entire general config values to prevent validation block)
        $('#settings-scripts-form').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');
            const originalText = submitBtn.text();
            submitBtn.prop('disabled', true).text('Saving injections...');

            const data = $('#settings-general-form').serialize() + '&' + form.serialize();

            $.ajax({
                url: '/api/admin/settings/general',
                method: 'POST',
                data: data,
                success: function(res) {
                    showToast('Injected scripts saved successfully!', 'success');
                },
                error: function() {
                    showToast('Failed to save custom scripts.', 'error');
                },
                complete: function() {
                    submitBtn.prop('disabled', false).text(originalText);
                }
            });
        });

        // 2. Menus & Navigation Builder
        let activeMenusData = [];
        window.loadMenusList = function() {
            const select = $('#menu-select-selector');
            const activeMenuId = select.val() || 1;

            $.ajax({
                url: '/api/admin/settings/menus',
                method: 'GET',
                success: function(res) {
                    if (res.status === 'success') {
                        activeMenusData = res.data;
                        
                        // Populate select dropdown options if empty
                        if (select.find('option').length <= 3) {
                            let selectHtml = '';
                            activeMenusData.forEach(menu => {
                                selectHtml += `<option value="${menu.id}">${menu.name} (${menu.location})</option>`;
                            });
                            select.html(selectHtml);
                            select.val(activeMenuId);
                        }

                        // Filter active menu items
                        const activeMenu = activeMenusData.find(m => m.id == activeMenuId);
                        renderMenuItemsList(activeMenu ? activeMenu.items : []);
                    }
                },
                error: function() {
                    showToast('Failed to retrieve menus list.', 'error');
                }
            });
        };

        $('#menu-select-selector').on('change', function() {
            loadMenusList();
        });

        function renderMenuItemsList(items) {
            let html = '';
            const rootItems = items.filter(item => !item.parent_id).sort((a, b) => a.order_index - b.order_index);

            if (rootItems.length === 0) {
                html = `<div style="text-align:center; padding:2rem; color:var(--text-secondary); font-size:0.85rem;">No navigation items inside this menu. Click + Add Item to create.</div>`;
            } else {
                rootItems.forEach(item => {
                    const children = items.filter(c => c.parent_id == item.id).sort((a, b) => a.order_index - b.order_index);
                    const statusBadge = item.is_active 
                        ? `<span class="badge" style="background:rgba(16,185,129,0.1); color:#10b981;">Active</span>`
                        : `<span class="badge" style="background:rgba(239,68,68,0.1); color:#ef4444;">Inactive</span>`;

                    html += `
                        <div class="menu-item-row" style="background:var(--bg-primary); border:1px solid var(--border-color); border-radius:8px; padding:0.75rem 1rem; margin-bottom:0.5rem; display:flex; justify-content:space-between; align-items:center;">
                            <div>
                                <strong style="font-size:0.9rem;">${item.title}</strong>
                                <span style="font-size:0.75rem; color:var(--text-secondary); margin-left:0.5rem;">${item.url}</span>
                                <span style="margin-left:0.5rem;">${statusBadge}</span>
                            </div>
                            <div style="display:flex; gap:0.4rem; align-items:center;">
                                <button type="button" class="btn-secondary btn-menu-item-up" data-id="${item.id}" style="font-size:0.7rem; padding:0.2rem 0.4rem; margin:0;">▲</button>
                                <button type="button" class="btn-secondary btn-menu-item-down" data-id="${item.id}" style="font-size:0.7rem; padding:0.2rem 0.4rem; margin:0;">▼</button>
                                <button type="button" class="btn-primary btn-menu-item-edit" data-id="${item.id}" style="font-size:0.7rem; padding:0.2rem 0.5rem; margin:0;">Edit</button>
                                <button type="button" class="btn-danger btn-menu-item-delete" data-id="${item.id}" style="font-size:0.7rem; padding:0.2rem 0.5rem; margin:0;">Del</button>
                            </div>
                        </div>
                    `;

                    children.forEach(cItem => {
                        const childStatusBadge = cItem.is_active 
                            ? `<span class="badge" style="background:rgba(16,185,129,0.1); color:#10b981; font-size:0.7rem;">Active</span>`
                            : `<span class="badge" style="background:rgba(239,68,68,0.1); color:#ef4444; font-size:0.7rem;">Inactive</span>`;

                        html += `
                            <div class="menu-item-row-child" style="background:rgba(255,255,255,0.02); border:1px dashed var(--border-color); border-radius:8px; padding:0.5rem 1rem; margin-bottom:0.5rem; margin-left:2rem; display:flex; justify-content:space-between; align-items:center;">
                                <div>
                                    <span style="color:var(--text-secondary); margin-right:0.25rem;">└</span>
                                    <strong style="font-size:0.85rem;">${cItem.title}</strong>
                                    <span style="font-size:0.7rem; color:var(--text-secondary); margin-left:0.5rem;">${cItem.url}</span>
                                    <span style="margin-left:0.5rem;">${childStatusBadge}</span>
                                </div>
                                <div style="display:flex; gap:0.4rem; align-items:center;">
                                    <button type="button" class="btn-secondary btn-menu-item-up" data-id="${cItem.id}" style="font-size:0.65rem; padding:0.15rem 0.3rem; margin:0;">▲</button>
                                    <button type="button" class="btn-secondary btn-menu-item-down" data-id="${cItem.id}" style="font-size:0.65rem; padding:0.15rem 0.3rem; margin:0;">▼</button>
                                    <button type="button" class="btn-primary btn-menu-item-edit" data-id="${cItem.id}" style="font-size:0.65rem; padding:0.15rem 0.4rem; margin:0;">Edit</button>
                                    <button type="button" class="btn-danger btn-menu-item-delete" data-id="${cItem.id}" style="font-size:0.65rem; padding:0.15rem 0.4rem; margin:0;">Del</button>
                                </div>
                            </div>
                        `;
                    });
                });
            }
            $('#menu-items-sortable-list').html(html);
        }

        // Add Menu Item triggers Modal
        $('#btn-add-menu-item-modal').on('click', function() {
            const menuId = $('#menu-select-selector').val();
            
            $('#menuItemForm')[0].reset();
            $('#menu-item-id').val('');
            $('#menu-item-menu-id').val(menuId);
            $('#menu-item-modal-title').text('Add Menu Item');

            // Populate Parents select
            const activeMenu = activeMenusData.find(m => m.id == menuId);
            let parentOptions = '<option value="">None (Root Level)</option>';
            if (activeMenu && activeMenu.items) {
                const rootItems = activeMenu.items.filter(item => !item.parent_id);
                rootItems.forEach(item => {
                    parentOptions += `<option value="${item.id}">${item.title}</option>`;
                });
            }
            $('#menu-item-parent').html(parentOptions);

            $('#menuItemModal').addClass('active');
        });

        // Edit Menu Item
        $(document).on('click', '.btn-menu-item-edit', function() {
            const itemId = $(this).data('id');
            const menuId = $('#menu-select-selector').val();
            const activeMenu = activeMenusData.find(m => m.id == menuId);
            if (!activeMenu) return;

            const item = activeMenu.items.find(i => i.id == itemId);
            if (!item) return;

            $('#menu-item-id').val(item.id);
            $('#menu-item-menu-id').val(item.menu_id);
            $('#menu-item-title').val(item.title);
            $('#menu-item-url').val(item.url);
            $('#menu-item-icon').val(item.icon);
            $('#menu-item-target').val(item.target);
            $('#menu-item-status').val(item.is_active ? '1' : '0');
            $('#menu-item-modal-title').text('Edit Menu Item');

            // Populate Parents select (exclude self)
            let parentOptions = '<option value="">None (Root Level)</option>';
            const rootItems = activeMenu.items.filter(i => !i.parent_id && i.id != item.id);
            rootItems.forEach(rItem => {
                parentOptions += `<option value="${rItem.id}">${rItem.title}</option>`;
            });
            $('#menu-item-parent').html(parentOptions);
            $('#menu-item-parent').val(item.parent_id || '');

            $('#menuItemModal').addClass('active');
        });

        // Delete Menu Item
        $(document).on('click', '.btn-menu-item-delete', function() {
            const itemId = $(this).data('id');
            if (!confirm('Are you sure you want to delete this menu item and all its submenus?')) return;

            $.ajax({
                url: `/api/admin/settings/menus/${itemId}`,
                method: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    showToast(res.message, 'success');
                    loadMenusList();
                },
                error: function() {
                    showToast('Failed to delete menu item.', 'error');
                }
            });
        });

        // Close Menu Item Modal
        $('#closeMenuItemModal, #menuItemModal').on('click', function(e) {
            if (e.target === this || e.target.id === 'closeMenuItemModal') {
                $('#menuItemModal').removeClass('active');
            }
        });

        // Save Menu Item submit handler
        $('#menuItemForm').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            $.ajax({
                url: '/api/admin/settings/menus',
                method: 'POST',
                data: form.serialize(),
                success: function(res) {
                    showToast(res.message, 'success');
                    $('#menuItemModal').removeClass('active');
                    loadMenusList();
                },
                error: function(err) {
                    showToast('Failed to save menu navigation item.', 'error');
                }
            });
        });

        // Reorder Menu Items via Up/Down buttons
        $(document).on('click', '.btn-menu-item-up, .btn-menu-item-down', function() {
            const btn = $(this);
            const itemId = btn.data('id');
            const direction = btn.hasClass('btn-menu-item-up') ? 'up' : 'down';
            
            const menuId = $('#menu-select-selector').val();
            const activeMenu = activeMenusData.find(m => m.id == menuId);
            if (!activeMenu) return;

            const items = activeMenu.items;
            const item = items.find(i => i.id == itemId);
            if (!item) return;

            // Get siblings (same parent level)
            const siblings = items.filter(i => i.parent_id == item.parent_id).sort((a, b) => (a.order_index || 0) - (b.order_index || 0));
            
            // Normalize sibling order indices to ensure they are sequential and distinct
            siblings.forEach((sib, sIdx) => {
                sib.order_index = sIdx;
            });

            const idx = siblings.findIndex(i => i.id == item.id);

            if (direction === 'up' && idx > 0) {
                // Swap order indices
                const prevItem = siblings[idx - 1];
                const temp = prevItem.order_index;
                prevItem.order_index = item.order_index;
                item.order_index = temp;
            } else if (direction === 'down' && idx < siblings.length - 1) {
                // Swap order indices
                const nextItem = siblings[idx + 1];
                const temp = nextItem.order_index;
                nextItem.order_index = item.order_index;
                item.order_index = temp;
            } else {
                return; // already at extreme boundaries
            }

            // Construct payload items sorted hierarchically
            const orderedPayloadItems = [];
            const sortedRootItems = items.filter(i => !i.parent_id).sort((a, b) => (a.order_index || 0) - (b.order_index || 0));
            
            sortedRootItems.forEach(root => {
                orderedPayloadItems.push({ id: root.id, parent_id: null });
                const sortedChildren = items.filter(i => i.parent_id == root.id).sort((a, b) => (a.order_index || 0) - (b.order_index || 0));
                sortedChildren.forEach(child => {
                    orderedPayloadItems.push({ id: child.id, parent_id: root.id });
                });
            });

            // Fallback for any orphaned items
            items.forEach(i => {
                if (!orderedPayloadItems.some(p => p.id === i.id)) {
                    orderedPayloadItems.push({ id: i.id, parent_id: i.parent_id });
                }
            });

            $.ajax({
                url: '/api/admin/settings/menus/reorder',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    menu_id: menuId,
                    items: orderedPayloadItems
                },
                success: function(res) {
                    showToast('Navigation ordering updated!', 'success');
                    loadMenusList();
                },
                error: function() {
                    showToast('Failed to swap navigation positions.', 'error');
                }
            });
        });

        // 3. Operations: CMS Pages Table & Editor
        let cmsEditor = null;
        function initCmsEditor() {
            if (!cmsEditor) {
                cmsEditor = new Quill('#cms-editor', {
                    theme: 'snow',
                    modules: {
                        toolbar: [
                            [{ 'header': [1, 2, 3, false] }],
                            ['bold', 'italic', 'underline', 'strike'],
                            ['link', 'blockquote', 'code-block'],
                            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                            ['clean']
                        ]
                    }
                });
            }
        }

        let cmsPagesTable;
        window.loadCmsPagesList = function() {
            if (!cmsPagesTable) {
                cmsPagesTable = new EnterpriseDataTable('#cms-pages-datatable', {
                    url: '/api/admin/settings/cms-pages',
                    serverSide: false,
                    searchPlaceholder: 'Search CMS pages...',
                    columns: [
                        { key: 'title', title: 'Title', sortable: true, priority: 'high', render: function(row) {
                            return `<strong>${cmsPagesTable.escape(row.title)}</strong>`;
                        }},
                        { key: 'slug', title: 'Slug Link', sortable: true, priority: 'medium', render: function(row) {
                            return `<a href="/p/${row.slug}" target="_blank" style="color:var(--accent-color); font-family:monospace; font-size:0.85rem;">/p/${row.slug}</a>`;
                        }},
                        { key: 'is_active', title: 'Status', sortable: true, priority: 'high', align: 'center', render: function(row) {
                            return row.is_active 
                                ? `<span class="badge-pill-compact" style="background:rgba(16,185,129,0.1); color:#10b981;">Published</span>`
                                : `<span class="badge-pill-compact" style="background:rgba(100,116,139,0.1); color:var(--text-secondary);">Draft</span>`;
                        }},
                        { key: 'actions', title: 'Actions', sortable: false, priority: 'high', align: 'center', render: function(row) {
                            return `
                                <div style="display:flex; gap:0.35rem; justify-content:center; align-items:center;">
                                    <button type="button" class="enterprise-action-icon-btn enterprise-action-icon-btn-view btn-cms-page-edit enterprise-tooltip" data-tooltip="Edit" data-id="${row.id}"><i class="fas fa-edit"></i></button>
                                    <button type="button" class="enterprise-action-icon-btn enterprise-action-icon-btn-danger btn-cms-page-delete enterprise-tooltip" data-tooltip="Delete" data-id="${row.id}"><i class="fas fa-trash-alt"></i></button>
                                </div>
                            `;
                        }}
                    ]
                });
            } else {
                cmsPagesTable.refresh();
            }
        };

        // Create CMS page opens modal
        $('#btn-create-cms-page').on('click', function() {
            $('#cmsPageForm')[0].reset();
            $('#cms-page-id').val('');
            $('#cms-page-modal-title').text('Create CMS Page');

            // Init Quill if not done
            initCmsEditor();
            if (cmsEditor) cmsEditor.setText('');

            $('#cmsPageModal').addClass('active');
        });

        // Edit CMS page
        $(document).on('click', '.btn-cms-page-edit', function() {
            const pageId = $(this).data('id');
            
            showToast('Loading page details...', 'info');

            $.ajax({
                url: `/api/admin/settings/cms-pages/${pageId}`,
                method: 'GET',
                success: function(res) {
                    if (res.status === 'success') {
                        const page = res.data;

                        $('#cms-page-id').val(page.id);
                        $('#cms-page-title').val(page.title);
                        $('#cms-page-meta-title').val(page.meta_title);
                        $('#cms-page-meta-desc').val(page.meta_description);
                        $('#cms-page-meta-keywords').val(page.meta_keywords);
                        $('#cms-page-status').val(page.is_active ? '1' : '0');
                        $('#cms-page-modal-title').text('Edit CMS Page');

                        initCmsEditor();
                        if (cmsEditor) {
                            cmsEditor.clipboard.dangerouslyPasteHTML(page.content || '');
                        }

                        $('#cmsPageModal').addClass('active');
                    }
                },
                error: function() {
                    showToast('Failed to retrieve page details.', 'error');
                }
            });
        });

        // Submit CMS page Form
        $('#cmsPageForm').on('submit', function(e) {
            e.preventDefault();
            
            // Extract content from Quill editor
            const htmlContent = cmsEditor ? cmsEditor.getSemanticHTML() : '';
            $('#cms-page-content').val(htmlContent);

            if (!htmlContent.trim()) {
                showToast('Page content is empty. Please enter details.', 'error');
                return;
            }

            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.text();
            submitBtn.prop('disabled', true).text('Saving template...');

            $.ajax({
                url: '/api/admin/settings/cms-pages',
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    showToast(res.message, 'success');
                    $('#cmsPageModal').removeClass('active');
                    loadCmsPagesList();
                },
                error: function(err) {
                    showToast('Failed to save static page template.', 'error');
                },
                complete: function() {
                    submitBtn.prop('disabled', false).text(originalText);
                }
            });
        });

        // Delete CMS Page
        $(document).on('click', '.btn-cms-page-delete', function() {
            const pageId = $(this).data('id');
            if (!confirm('Are you sure you want to delete this CMS static page permanently?')) return;

            $.ajax({
                url: `/api/admin/settings/cms-pages/${pageId}`,
                method: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    showToast(res.message, 'success');
                    loadCmsPagesList();
                },
                error: function() {
                    showToast('Failed to delete CMS page.', 'error');
                }
            });
        });

        // Close CMS Page Modal
        $('#closeCmsPageModal, #cmsPageModal').on('click', function(e) {
            if (e.target === this || e.target.id === 'closeCmsPageModal') {
                $('#cmsPageModal').removeClass('active');
            }
        });

        // 4. Operations: Advertisements list
        window.loadAdSlots = function() {
            $.ajax({
                url: '/api/admin/advertisements',
                method: 'GET',
                success: function(res) {
                    if (res.status === 'success') {
                        let html = '';
                        res.data.forEach(ad => {
                            html += `
                                <div style="background:var(--bg-primary); border:1px solid var(--border-color); border-radius:10px; padding:1rem;">
                                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem;">
                                        <strong style="font-size:0.95rem; text-transform:capitalize;">${ad.slot_name.replace(/_/g, ' ')}</strong>
                                        <label class="custom-switch" style="position: relative; display: inline-block; width: 44px; height: 24px;">
                                            <input type="checkbox" class="ad-active-toggle" data-id="${ad.id}" ${ad.is_active ? 'checked' : ''} style="opacity: 0; width: 0; height: 0;">
                                            <span class="slider-switch" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: var(--border-color); transition: .3s; border-radius: 24px;"></span>
                                        </label>
                                    </div>
                                    <form class="ajax-ad-slot-form" data-slot="${ad.slot_name}">
                                        @csrf
                                        <input type="hidden" name="slot_name" value="${ad.slot_name}">
                                        <input type="hidden" name="is_active" value="${ad.is_active ? 1 : 0}" class="ad-form-status-val">
                                        
                                        <div class="form-group" style="margin:0;">
                                            <label style="font-size:0.75rem; margin-bottom:0.25rem;">Ad Script (JS/HTML code)</label>
                                            <textarea name="ad_code" class="form-control" rows="2" style="font-family:monospace; font-size:0.8rem; margin-bottom:0.5rem;" placeholder="Paste Google AdSense / Header auction code here...">${ad.ad_code || ''}</textarea>
                                        </div>
                                        <button type="submit" class="btn-primary" style="font-size:0.75rem; margin:0; padding:0.3rem 0.7rem; width:100%;">Apply Ad Slot Code</button>
                                    </form>
                                </div>
                            `;
                        });
                        $('#ad-slots-container').html(html);
                    }
                },
                error: function() {
                    showToast('Failed to load advertisements slots.', 'error');
                }
            });
        };

        // Submit Ad slot code
        $(document).on('submit', '.ajax-ad-slot-form', function(e) {
            e.preventDefault();
            const form = $(this);
            const submitBtn = form.find('button[type="submit"]');
            submitBtn.prop('disabled', true).text('Applying script...');

            $.ajax({
                url: '/api/admin/advertisements',
                method: 'POST',
                data: form.serialize(),
                success: function(res) {
                    showToast(res.message, 'success');
                    loadAdSlots();
                },
                error: function() {
                    showToast('Failed to save ad slot scripts.', 'error');
                },
                complete: function() {
                    submitBtn.prop('disabled', false).text('Apply Ad Slot Code');
                }
            });
        });

        // Toggle Ad Slot Active State
        $(document).on('change', '.ad-active-toggle', function() {
            const checkbox = $(this);
            const id = checkbox.data('id');
            
            $.ajax({
                url: `/api/admin/advertisements/${id}/toggle`,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    showToast(res.message, 'success');
                    loadAdSlots();
                },
                error: function() {
                    showToast('Failed to toggle ad slot state.', 'error');
                }
            });
        });

        let backupsTable;
        window.loadBackupsList = function() {
            if (!backupsTable) {
                backupsTable = new EnterpriseDataTable('#backups-datatable', {
                    url: '/api/admin/settings/backups',
                    serverSide: false,
                    searchPlaceholder: 'Search backups...',
                    emptyMessage: 'No backups generated in storage path.',
                    columns: [
                        { key: 'filename', title: 'Filename / Date', sortable: true, priority: 'high', render: function(row) {
                            return `
                                <div class="enterprise-primary-stack">
                                    <span class="enterprise-primary-value">${backupsTable.escape(row.filename)}</span>
                                    <span class="enterprise-secondary-metadata">${row.created_at}</span>
                                </div>
                            `;
                        }},
                        { key: 'size', title: 'File Size', sortable: true, priority: 'high', render: function(row) {
                            const fileSizeMb = (row.size / (1024 * 1024)).toFixed(2);
                            return `${fileSizeMb} MB`;
                        }},
                        { key: 'actions', title: 'Actions', sortable: false, priority: 'high', align: 'center', render: function(row) {
                            return `
                                <div style="display:flex; gap:0.35rem; justify-content:center; align-items:center;">
                                    <a href="/api/admin/settings/backups/download/${row.filename}" class="enterprise-action-icon-btn enterprise-action-icon-btn-success enterprise-tooltip" data-tooltip="Download SQL Backup"><i class="fas fa-download"></i></a>
                                    <button type="button" class="enterprise-action-icon-btn enterprise-action-icon-btn-view btn-backup-restore enterprise-tooltip" data-tooltip="Restore Database" data-file="${row.filename}"><i class="fas fa-history"></i></button>
                                    <button type="button" class="enterprise-action-icon-btn enterprise-action-icon-btn-danger btn-backup-delete enterprise-tooltip" data-tooltip="Delete Backup" data-file="${row.filename}"><i class="fas fa-trash-alt"></i></button>
                                </div>
                            `;
                        }}
                    ]
                });
            } else {
                backupsTable.refresh();
            }
        };

        // Generate Backup
        $('#btn-trigger-backup').on('click', function() {
            const btn = $(this);
            btn.prop('disabled', true).text('Writing SQL dump...');
            showToast('Generating database SQL dump. Please wait...', 'info');

            $.ajax({
                url: '/api/admin/settings/backups/generate',
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    showToast(res.message, 'success');
                    loadBackupsList();
                },
                error: function(err) {
                    showToast(err.responseJSON?.message || 'Database export dump failed.', 'error');
                },
                complete: function() {
                    btn.prop('disabled', false).text('Generate Backup');
                }
            });
        });

        // Restore Backup
        $(document).on('click', '.btn-backup-restore', function() {
            const filename = $(this).data('file');
            if (!confirm(`CAUTION: Restoring database will drop all current table rows and overwrite states with backup file: "${filename}". Proceed?`)) return;

            showToast('Restoring DB backup. Connection might drop temporarily...', 'info');

            $.ajax({
                url: '/api/admin/settings/backups/restore',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    filename: filename
                },
                success: function(res) {
                    showToast(res.message, 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                },
                error: function(err) {
                    showToast(err.responseJSON?.message || 'DB Restoration state failed.', 'error');
                }
            });
        });

        // Delete Backup file
        $(document).on('click', '.btn-backup-delete', function() {
            const filename = $(this).data('file');
            if (!confirm(`Are you sure you want to delete this SQL backup file permanently from storage: "${filename}"?`)) return;

            $.ajax({
                url: `/api/admin/settings/backups/${filename}`,
                method: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    showToast(res.message, 'success');
                    loadBackupsList();
                },
                error: function() {
                    showToast('Failed to delete backup file.', 'error');
                }
            });
        });

        // 6. Media Manager Explorer
        let currentMediaPath = '';
        window.loadMediaExplorer = function(path = '') {
            currentMediaPath = path;
            
            // Render breadcrumbs
            const segments = path.split('/').filter(p => p !== '');
            let breadHtml = '<span style="cursor:pointer;" class="media-breadcrumb-segment" data-path="">uploads/media</span>';
            let accumulatedPath = '';
            
            segments.forEach(seg => {
                accumulatedPath += (accumulatedPath === '' ? seg : '/' + seg);
                breadHtml += ` &rsaquo; <span style="cursor:pointer;" class="media-breadcrumb-segment" data-path="${accumulatedPath}">${seg}</span>`;
            });
            $('#media-breadcrumbs').html(breadHtml);

            // Fetch directory contents
            $.ajax({
                url: `/api/admin/settings/media?path=${encodeURIComponent(path)}`,
                method: 'GET',
                success: function(res) {
                    if (res.status === 'success') {
                        let gridHtml = '';
                        const items = res.data;

                        if (items.length === 0) {
                            gridHtml = `
                                <div style="grid-column: 1 / -1; display:flex; flex-direction:column; justify-content:center; align-items:center; color:var(--text-secondary); padding:3rem; font-size:0.9rem;">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:1rem;"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                                    This directory is empty.
                                </div>
                            `;
                        } else {
                            items.forEach(item => {
                                if (item.type === 'directory') {
                                    gridHtml += `
                                        <div class="media-folder-card media-item-clickable" data-type="directory" data-path="${item.path}" style="background:var(--bg-primary); border:1px solid var(--border-color); border-radius:8px; padding:1rem; display:flex; flex-direction:column; align-items:center; text-align:center; cursor:pointer; position:relative;">
                                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#eab308" stroke-width="2" style="margin-bottom:0.5rem;"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                                            <div style="font-size:0.8rem; font-weight:600; width:100%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="${item.name}">${item.name}</div>
                                            <button type="button" class="btn-media-delete" data-path="${item.path}" style="position:absolute; top:2px; right:2px; font-size:0.7rem; padding:0.1rem 0.25rem; border-radius:3px; margin:0; line-height:1; background:#ef4444; border:none; color:white; display:none;">&times;</button>
                                        </div>
                                    `;
                                } else {
                                    const ext = item.name.split('.').pop().toLowerCase();
                                    const isImg = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'ico'].includes(ext);
                                    
                                    let iconHtml = '';
                                    if (isImg) {
                                        iconHtml = `<img src="${item.url}" style="width:48px; height:48px; object-fit:cover; border-radius:4px; margin-bottom:0.5rem;">`;
                                    } else {
                                        iconHtml = `<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-bottom:0.5rem; color:var(--text-secondary);"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>`;
                                    }

                                    const sizeKb = (item.size / 1024).toFixed(1);

                                    gridHtml += `
                                        <div class="media-file-card media-item-clickable" data-type="file" data-url="${item.url}" style="background:var(--bg-primary); border:1px solid var(--border-color); border-radius:8px; padding:1rem; display:flex; flex-direction:column; align-items:center; text-align:center; cursor:pointer; position:relative;">
                                            ${iconHtml}
                                            <div style="font-size:0.8rem; font-weight:600; width:100%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; margin-bottom:0.2rem;" title="${item.name}">${item.name}</div>
                                            <div style="font-size:0.65rem; color:var(--text-secondary);">${sizeKb} KB</div>
                                            <button type="button" class="btn-media-delete" data-path="${item.path}" style="position:absolute; top:2px; right:2px; font-size:0.7rem; padding:0.1rem 0.25rem; border-radius:3px; margin:0; line-height:1; background:#ef4444; border:none; color:white; display:none;">&times;</button>
                                        </div>
                                    `;
                                }
                            });
                        }
                        $('#media-files-grid').html(gridHtml);
                    }
                },
                error: function() {
                    showToast('Failed to explore directory contents.', 'error');
                }
            });
        };

        // Click breadcrumb to navigate
        $(document).on('click', '.media-breadcrumb-segment', function() {
            const targetPath = $(this).data('path');
            loadMediaExplorer(targetPath);
        });

        // Click media folder card to go deeper
        $(document).on('click', '.media-item-clickable', function(e) {
            if ($(e.target).closest('.btn-media-delete').length) return;
            
            const card = $(this);
            if (card.data('type') === 'directory') {
                loadMediaExplorer(card.data('path'));
            } else {
                const url = card.data('url');
                navigator.clipboard.writeText(url).then(() => {
                    showToast('Copied media URL to clipboard!', 'success');
                }).catch(() => {
                    const temp = $('<input>');
                    $('body').append(temp);
                    temp.val(url).select();
                    document.execCommand('copy');
                    temp.remove();
                    showToast('Copied media URL!', 'success');
                });
            }
        });

        // Show/hide delete icon on media cards hover
        $(document).on('mouseenter', '.media-folder-card, .media-file-card', function() {
            $(this).find('.btn-media-delete').show();
        }).on('mouseleave', '.media-folder-card, .media-file-card', function() {
            $(this).find('.btn-media-delete').hide();
        });

        // Delete File/Directory in Media Manager
        $(document).on('click', '.btn-media-delete', function(e) {
            e.stopPropagation();
            const path = $(this).data('path');
            if (!confirm(`Are you sure you want to delete this file/folder permanently: "${path}"?`)) return;

            $.ajax({
                url: '/api/admin/settings/media',
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}',
                    path: path
                },
                success: function(res) {
                    showToast(res.message, 'success');
                    loadMediaExplorer(currentMediaPath);
                },
                error: function() {
                    showToast('Failed to delete media asset.', 'error');
                }
            });
        });

        // Create Folder in Media Manager
        $('#btn-media-new-folder').on('click', function() {
            const name = prompt('Enter folder name:');
            if (!name) return;

            $.ajax({
                url: '/api/admin/settings/media/folder',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    path: currentMediaPath,
                    folder_name: name
                },
                success: function(res) {
                    showToast(res.message, 'success');
                    loadMediaExplorer(currentMediaPath);
                },
                error: function(err) {
                    showToast(err.responseJSON?.message || 'Failed to create directory.', 'error');
                }
            });
        });

        // Upload Media Trigger
        $('#btn-media-trigger-upload').on('click', function() {
            $('#media-upload-input').click();
        });

        $('#media-upload-input').on('change', function() {
            const file = this.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('path', currentMediaPath);
            formData.append('file', file);

            showToast('Uploading file...', 'info');

            $.ajax({
                url: '/api/admin/settings/media/upload',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    showToast(res.message, 'success');
                    loadMediaExplorer(currentMediaPath);
                },
                error: function(err) {
                    showToast(err.responseJSON?.message || 'Failed to upload media file.', 'error');
                }
            });
        });

    });
</script>
@endsection
