@extends('layouts.app')

@section('title', 'Enterprise Control Center - GovJobs Admin')

@section('content')
<div class="admin-container" style="display: grid; grid-template-columns: 260px 1fr; min-height: 100vh; gap: 2rem; padding: 0 5%; max-width: 1600px; margin: 2rem auto 0 auto;">
    
    <!-- 1. Enterprise Sidebar Navigation -->
    <aside class="glass-panel admin-sidebar" style="padding: 1.5rem; height: fit-content; position: sticky; top: 100px; border-radius: 16px;">
        <div style="text-align: center; margin-bottom: 2rem; border-bottom: 1px solid var(--border-color); padding-bottom: 1.5rem;">
            <h2 style="font-family: 'Outfit'; font-size: 1.25rem; color: var(--accent-color); margin-bottom: 0.25rem;">Admin Workspace</h2>
            <span style="font-size: 0.75rem; color: var(--text-secondary); background: rgba(37,99,235,0.08); padding: 0.25rem 0.5rem; border-radius: 99px; text-transform: uppercase;">Clearance: Level 3</span>
        </div>
        
        <div class="admin-nav-links" style="display: flex; flex-direction: column; gap: 0.5rem;">
            <button class="admin-nav-btn active" data-block="overview"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="7" height="9"></rect><rect x="14" y="3" width="7" height="5"></rect><rect x="14" y="12" width="7" height="9"></rect><rect x="3" y="16" width="7" height="5"></rect></svg> Dashboard Overview</button>
            <button class="admin-nav-btn" data-block="jobs"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg> Recruitment Postings</button>
            <button class="admin-nav-btn" data-block="crawlers"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg> Crawler Target Configs</button>
            <button class="admin-nav-btn" data-block="master"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2L2 7l10 5 10-5-10-5z"></path><path d="M2 17l10 5 10-5"></path><path d="M2 12l10 5 10-5"></path></svg> Master Data Manager</button>
            <button class="admin-nav-btn" data-block="users"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-3-3.87"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg> User Access Panel</button>
            <button class="admin-nav-btn" data-block="queues"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="2" y="2" width="20" height="8" rx="2" ry="2"></rect><rect x="2" y="14" width="20" height="8" rx="2" ry="2"></rect><line x1="6" y1="6" x2="6.01" y2="6"></line><line x1="6" y1="18" x2="6.01" y2="18"></line></svg> Queue Engine & DLQ</button>
            <button class="admin-nav-btn" data-block="seo"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg> SEO & Content Cache</button>
            <button class="admin-nav-btn" data-block="audit"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg> Audit Activity Logs</button>
            <button class="admin-nav-btn" data-block="ai-content"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg> AI Content Manager</button>
        </div>
        
        <div style="margin-top: 3rem; text-align: center;">
            <a href="/" class="form-btn" style="text-decoration: none; padding: 0.6rem; display: block; background: var(--text-secondary); text-align: center; border-radius: 8px;">&larr; Exit Console</a>
        </div>
    </aside>

    <!-- 2. Main Administration Canvas -->
    <main class="admin-content-canvas" style="display: flex; flex-direction: column; gap: 2rem;">
        
        <!-- ================= PANEL 1: DASHBOARD OVERVIEW ================= -->
        <section class="admin-panel-block active" id="admin-overview">
            <h2 style="font-family: 'Outfit'; font-size: 1.75rem; margin-bottom: 1.5rem;">SaaS Control Panel Overview</h2>
            
            <!-- Statistics Metric Cards -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem;">
                <div class="glass-panel stat-card-premium" style="border-left: 5px solid var(--accent-color);">
                    <div class="label">Total Published Posts</div>
                    <div class="number" id="overview-jobs-posted">0</div>
                    <div class="subtext">Active direct announcements</div>
                </div>
                <div class="glass-panel stat-card-premium" style="border-left: 5px solid #10b981;">
                    <div class="label">Crawl Target Feeds</div>
                    <div class="number" id="overview-sources">0</div>
                    <div class="subtext" id="overview-active-sources">0 active crawlers</div>
                </div>
                <div class="glass-panel stat-card-premium" style="border-left: 5px solid #f59e0b;">
                    <div class="label">Logs Quarantined</div>
                    <div class="number" id="overview-quarantines" style="color: #f59e0b;">0</div>
                    <div class="subtext">Pending manual corrections</div>
                </div>
                <div class="glass-panel stat-card-premium" style="border-left: 5px solid #ef4444;">
                    <div class="label">Automation Success Rate</div>
                    <div class="number" id="overview-success-runs" style="color: #10b981;">100%</div>
                    <div class="subtext" id="overview-failed-runs">0 critical errors</div>
                </div>
            </div>

            <!-- Visualization Row -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; align-items: start;">
                <!-- Crawler status and health -->
                <div class="glass-panel" style="padding: 1.5rem; border-radius: 16px;">
                    <h3 style="font-family: 'Outfit'; font-size: 1.2rem; color: var(--accent-color); margin-bottom: 1rem;">System Health & Crawl Metrics</h3>
                    <div class="responsive-table-container">
                        <table class="portal-table">
                            <thead>
                                <tr>
                                    <th>Target Crawl Feed</th>
                                    <th>Last Execution Log</th>
                                    <th>Harvests</th>
                                    <th>Health</th>
                                </tr>
                            </thead>
                            <tbody id="overview-crawlers-table">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- SVG Graph circular gauge -->
                <div class="glass-panel" style="padding: 1.5rem; border-radius: 16px; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                    <h3 style="font-family: 'Outfit'; font-size: 1.1rem; margin-bottom: 1.5rem;">Crawl Success Ratio</h3>
                    <div style="position: relative; width: 140px; height: 140px;">
                        <svg width="140" height="140" viewBox="0 0 36 36" style="transform: rotate(-90deg);">
                            <circle cx="18" cy="18" r="16" fill="none" stroke="var(--border-color)" stroke-width="3"></circle>
                            <circle id="success-svg-gauge" cx="18" cy="18" r="16" fill="none" stroke="#10b981" stroke-width="3" stroke-dasharray="100 100" stroke-linecap="round"></circle>
                        </svg>
                        <div id="success-ratio-label" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 1.4rem; font-weight: bold; font-family: 'Outfit'; color: #10b981;">100%</div>
                    </div>
                    <p style="font-size: 0.8rem; color: var(--text-secondary); margin-top: 1.5rem; line-height: 1.4;">Ratio of successful feed harvesting runs to critical diagnostic failures.</p>
                </div>
            </div>

            <!-- Pending Quarantine rescue card -->
            <div class="glass-panel" style="padding: 1.5rem; border-radius: 16px; margin-top: 2rem;">
                <h3 style="font-family: 'Outfit'; font-size: 1.25rem; color: #f59e0b; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;"><span style="display:inline-block; width:10px; height:20px; background:#f59e0b; border-radius:3px;"></span> Quarantined Scraped Listings (Awaiting Approval)</h3>
                <div id="admin-quarantine-override-canvas">
                    <!-- Populated dynamically via AJAX -->
                </div>
            </div>
        </section>

        <!-- ================= PANEL 2: RECRUITMENT POSTINGS ================= -->
        <section class="admin-panel-block" id="admin-jobs" style="display: none;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="font-family: 'Outfit'; font-size: 1.75rem; margin: 0;">Recruitment Postings Manager</h2>
                <button class="form-btn" id="btn-create-job-drawer" style="margin: 0; padding: 0.6rem 1.2rem; display: flex; align-items: center; gap: 0.5rem;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg> Publish Recruitment</button>
            </div>

            <div class="glass-panel" style="padding: 1.5rem; border-radius: 16px; margin-bottom: 1.5rem; display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
                <input type="text" id="jobs-search-input" placeholder="Live search announcements title..." style="flex: 1; padding: 0.6rem 1rem; border-radius: 8px; background: var(--bg-primary); border: 1px solid var(--border-color); color: var(--text-primary);" autocomplete="off">
                <select id="jobs-per-page" style="padding: 0.6rem; border-radius: 8px; background: var(--bg-primary); border: 1px solid var(--border-color); color: var(--text-primary);">
                    <option value="10">10 Per Page</option>
                    <option value="25">25 Per Page</option>
                    <option value="50">50 Per Page</option>
                </select>
            </div>

            <div class="glass-panel" style="padding: 1.5rem; border-radius: 16px;">
                <div class="responsive-table-container">
                    <table class="portal-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Recruitment Announcement Title</th>
                                <th>Category</th>
                                <th>Region</th>
                                <th>Salary Max</th>
                                <th>Deadline</th>
                                <th style="text-align: center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="jobs-management-table-body">
                            <!-- Populated dynamically via AJAX -->
                        </tbody>
                    </table>
                </div>
                <div class="pagination-container" id="jobs-management-pagination" style="margin-top: 1.5rem;"></div>
            </div>
        </section>

        <!-- ================= PANEL 3: CRAWLER TARGET CONFIGS ================= -->
        <section class="admin-panel-block" id="admin-crawlers" style="display: none;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="font-family: 'Outfit'; font-size: 1.75rem; margin: 0;">Web Crawler Monitor Profiles</h2>
                <button class="form-btn" id="btn-create-crawler-drawer" style="margin: 0; padding: 0.6rem 1.2rem; display: flex; align-items: center; gap: 0.5rem;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg> Add Scraper Target</button>
            </div>

            <div class="glass-panel" style="padding: 1.5rem; border-radius: 16px;">
                <div class="responsive-table-container">
                    <table class="portal-table">
                        <thead>
                            <tr>
                                <th>Crawl Target Name</th>
                                <th>Source URL</th>
                                <th>Cron Schedule</th>
                                <th style="text-align: center;">Active State</th>
                                <th style="text-align: center;">Crawl Override</th>
                                <th style="text-align: center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="crawlers-management-table-body">
                            <!-- Populated dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- ================= PANEL 4: MASTER DATA MANAGER ================= -->
        <section class="admin-panel-block" id="admin-master" style="display: none;">
            <h2 style="font-family: 'Outfit'; font-size: 1.75rem; margin-bottom: 1.5rem;">Master Data Management Center</h2>

            <!-- Segment Master Tabs -->
            <div class="sub-tab-headers" style="margin-bottom: 1.5rem; display: flex; gap: 0.5rem;">
                <button class="sub-tab-btn active master-sub-trigger" data-target="master-categories">Job Categories</button>
                <button class="sub-tab-btn master-sub-trigger" data-target="master-departments">Departments</button>
                <button class="sub-tab-btn master-sub-trigger" data-target="master-qualifications">Qualifications</button>
                <button class="sub-tab-btn master-sub-trigger" data-target="master-states">States/Regions</button>
            </div>

            <!-- Categories sub-tab -->
            <div class="master-sub-panel active" id="master-categories">
                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; align-items: start;">
                    <div class="glass-panel" style="padding: 1.5rem; border-radius: 16px;">
                        <h3 id="category-form-title" style="font-family:'Outfit'; font-size:1.15rem; margin-bottom:1rem; color:var(--accent-color);">Add Category</h3>
                        <form id="ajax-category-form">
                            <input type="hidden" id="category-edit-id">
                            <div class="form-group">
                                <label for="category-name-input">Category Name</label>
                                <input type="text" id="category-name-input" class="form-control" placeholder="e.g. Banking & Finance" required>
                            </div>
                            <button type="submit" class="form-btn" id="category-submit-btn">Save Category</button>
                            <button type="button" class="btn-view" id="category-cancel-btn" style="display:none; width:100%; margin-top:0.5rem;">Cancel Edit</button>
                        </form>
                    </div>

                    <div class="glass-panel" style="padding: 1.5rem; border-radius: 16px;">
                        <div class="responsive-table-container">
                            <table class="portal-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Category Name</th>
                                        <th>Slug Reference</th>
                                        <th style="text-align: center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="categories-table-body"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Departments sub-tab -->
            <div class="master-sub-panel" id="master-departments" style="display: none;">
                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; align-items: start;">
                    <div class="glass-panel" style="padding: 1.5rem; border-radius: 16px;">
                        <h3 id="dept-form-title" style="font-family:'Outfit'; font-size:1.15rem; margin-bottom:1rem; color:var(--accent-color);">Add Department</h3>
                        <form id="ajax-department-form">
                            <input type="hidden" id="dept-edit-id">
                            <div class="form-group">
                                <label for="dept-name-input">Department Name</label>
                                <input type="text" id="dept-name-input" class="form-control" placeholder="e.g. Staff Selection Board" required>
                            </div>
                            <div class="form-group">
                                <label for="dept-code-input">Unique Code</label>
                                <input type="text" id="dept-code-input" class="form-control" placeholder="e.g. SSC" required>
                            </div>
                            <button type="submit" class="form-btn" id="dept-submit-btn">Save Department</button>
                            <button type="button" class="btn-view" id="dept-cancel-btn" style="display:none; width:100%; margin-top:0.5rem;">Cancel Edit</button>
                        </form>
                    </div>

                    <div class="glass-panel" style="padding: 1.5rem; border-radius: 16px;">
                        <div class="responsive-table-container">
                            <table class="portal-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Department Name</th>
                                        <th>Code</th>
                                        <th style="text-align: center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="departments-table-body"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Qualifications sub-tab -->
            <div class="master-sub-panel" id="master-qualifications" style="display: none;">
                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; align-items: start;">
                    <div class="glass-panel" style="padding: 1.5rem; border-radius: 16px;">
                        <h3 id="qual-form-title" style="font-family:'Outfit'; font-size:1.15rem; margin-bottom:1rem; color:var(--accent-color);">Add Qualification</h3>
                        <form id="ajax-qualification-form">
                            <input type="hidden" id="qual-edit-id">
                            <div class="form-group">
                                <label for="qual-name-input">Qualification Name</label>
                                <input type="text" id="qual-name-input" class="form-control" placeholder="e.g. Graduate Degree" required>
                            </div>
                            <button type="submit" class="form-btn" id="qual-submit-btn">Save Qualification</button>
                            <button type="button" class="btn-view" id="qual-cancel-btn" style="display:none; width:100%; margin-top:0.5rem;">Cancel Edit</button>
                        </form>
                    </div>

                    <div class="glass-panel" style="padding: 1.5rem; border-radius: 16px;">
                        <div class="responsive-table-container">
                            <table class="portal-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Qualification</th>
                                        <th>Slug Reference</th>
                                        <th style="text-align: center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="qualifications-table-body"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- States sub-tab -->
            <div class="master-sub-panel" id="master-states" style="display: none;">
                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; align-items: start;">
                    <div class="glass-panel" style="padding: 1.5rem; border-radius: 16px;">
                        <h3 id="state-form-title" style="font-family:'Outfit'; font-size:1.15rem; margin-bottom:1rem; color:var(--accent-color);">Add State/Region</h3>
                        <form id="ajax-state-form">
                            <input type="hidden" id="state-edit-id">
                            <div class="form-group">
                                <label for="state-name-input">State Name</label>
                                <input type="text" id="state-name-input" class="form-control" placeholder="e.g. Maharashtra" required>
                            </div>
                            <div class="form-group">
                                <label for="state-code-input">State ISO Code</label>
                                <input type="text" id="state-code-input" class="form-control" placeholder="e.g. MH" required>
                            </div>
                            <button type="submit" class="form-btn" id="state-submit-btn">Save State</button>
                            <button type="button" class="btn-view" id="state-cancel-btn" style="display:none; width:100%; margin-top:0.5rem;">Cancel Edit</button>
                        </form>
                    </div>

                    <div class="glass-panel" style="padding: 1.5rem; border-radius: 16px;">
                        <div class="responsive-table-container">
                            <table class="portal-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>State Name</th>
                                        <th>Code</th>
                                        <th style="text-align: center;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="states-table-body"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ================= PANEL 5: USER ACCESS PANEL ================= -->
        <section class="admin-panel-block" id="admin-users" style="display: none;">
            <h2 style="font-family: 'Outfit'; font-size: 1.75rem; margin-bottom: 1.5rem;">User Access Registry</h2>
            <div class="glass-panel" style="padding: 1.5rem; border-radius: 16px;">
                <div class="responsive-table-container">
                    <table class="portal-table" id="admin-users-table">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th style="text-align: center;">Account State</th>
                                <th style="text-align: center;">Elevations / Actions</th>
                            </tr>
                        </thead>
                        <tbody id="users-table-body">
                            <!-- Populated dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- ================= PANEL 6: SEO & CONTENT CACHE ================= -->
        <section class="admin-panel-block" id="admin-seo" style="display: none;">
            <h2 style="font-family: 'Outfit'; font-size: 1.75rem; margin-bottom: 1.5rem;">SEO Configurations & Meta Console</h2>
            
            <div class="glass-panel" style="padding: 2.5rem; max-width: 700px; margin: 0 auto; border-radius: 16px;">
                <h3 style="font-family: 'Outfit'; color: var(--accent-color); margin-bottom: 1.5rem; text-align: center;">Synchronize Cached SEO Keywords</h3>
                <form id="ajax-seo-console-form">
                    @csrf
                    <div class="form-group">
                        <label for="seo-title">Meta Title Template</label>
                        <input type="text" name="meta_title" id="seo-title" class="form-control" value="{{ $seo['meta_title'] }}" required>
                    </div>
                    <div class="form-group">
                        <label for="seo-desc">Meta Description Template</label>
                        <textarea name="meta_description" id="seo-desc" class="form-control" rows="4" required>{{ $seo['meta_description'] }}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="seo-keywords">Meta Keywords Tag String</label>
                        <input type="text" name="meta_keywords" id="seo-keywords" class="form-control" value="{{ $seo['meta_keywords'] }}" required>
                        <p style="font-size:0.75rem; color:var(--text-secondary); margin-top:0.25rem;">Separate terms by commas (e.g. government, upsc, recruitment)</p>
                    </div>
                    <button type="submit" class="form-btn" id="seo-submit-btn">Synchronize Metadata Cache</button>
                </form>
            </div>
        </section>

        <!-- ================= PANEL 7: AUDIT ACTIVITY LOGS ================= -->
        <section class="admin-panel-block" id="admin-audit" style="display: none;">
            <h2 style="font-family: 'Outfit'; font-size: 1.75rem; margin-bottom: 1.5rem;">System & Administrative Audit Logs</h2>
            <div class="glass-panel" style="padding: 1.5rem; border-radius: 16px;">
                <div class="responsive-table-container">
                    <table class="portal-table">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Actor User</th>
                                <th>IP Address</th>
                                <th>Action Event</th>
                                <th>Details / Payload Trace</th>
                            </tr>
                        </thead>
                        <tbody id="audit-logs-table-body">
                            <!-- Populated dynamically via AJAX -->
                        </tbody>
                    </table>
                </div>
                <div class="pagination-container" id="audit-logs-pagination" style="margin-top: 1.5rem;"></div>
            </div>
        </section>

        <!-- ================= PANEL 8: QUEUE ENGINE & DLQ MANAGEMENT ================= -->
        <section class="admin-panel-block" id="admin-queues" style="display: none;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="font-family: 'Outfit'; font-size: 1.75rem; margin: 0;">Distributed Queue Control Center</h2>
                <div style="display: flex; gap: 0.75rem;">
                    <a href="/horizon" target="_blank" class="form-btn" style="margin: 0; padding: 0.6rem 1.2rem; background: var(--accent-color); border: none; text-decoration: none; display: flex; align-items: center; gap: 0.5rem;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg> Open Horizon Console</a>
                    <button class="form-btn" id="btn-queues-retry-all" style="margin: 0; padding: 0.6rem 1.2rem; background: #10b981; border: none; display: flex; align-items: center; gap: 0.5rem;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 10 17 10"></polyline><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg> Retry All Failures</button>
                    <button class="form-btn" id="btn-queues-clear-all" style="margin: 0; padding: 0.6rem 1.2rem; background: #ef4444; border: none; display: flex; align-items: center; gap: 0.5rem;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg> Flush DLQ Store</button>
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
            <div class="glass-panel" style="padding: 1.5rem; border-radius: 16px;">
                <h3 style="font-family: 'Outfit'; font-size: 1.2rem; color: var(--accent-color); margin-bottom: 1rem;">Dead-Letter Queue (DLQ) Browser</h3>
                <div class="responsive-table-container">
                    <table class="portal-table">
                        <thead>
                            <tr>
                                <th>UUID</th>
                                <th>Job Class</th>
                                <th>Origin Queue</th>
                                <th>Diagnostic Error</th>
                                <th>Failed Time</th>
                                <th style="text-align: center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="queues-failed-table-body">
                            <!-- Populated dynamically via AJAX -->
                        </tbody>
                    </table>
                </div>
                <div class="pagination-container" id="queues-failed-pagination" style="margin-top: 1.5rem;"></div>
            </div>
        </section>

        <!-- ================= PANEL 9: AI CONTENT MANAGER ================= -->
        <section class="admin-panel-block" id="admin-ai-content" style="display: none;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="font-family: 'Outfit'; font-size: 1.75rem; margin: 0;">AI Content Generation Console</h2>
                <div style="display: flex; gap: 0.75rem; align-items: center;">
                    <span style="font-size: 0.85rem; color: var(--text-secondary);">Active AI Engine:</span>
                    <span id="ai-telemetry-engine" class="badge" style="background: rgba(37, 99, 235, 0.15); color: var(--accent-color); font-weight: 700; padding: 0.4rem 0.8rem; border-radius: 6px; text-transform: uppercase;">GEMINI</span>
                </div>
            </div>

            <!-- AI Telemetry Stats -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                <div class="glass-panel stat-card-premium" style="border-left: 5px solid var(--accent-color);">
                    <div class="label">Total AI Enriched Posts</div>
                    <div class="number" id="ai-stat-total">0</div>
                    <div class="subtext">Completed or pending generation</div>
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

            <!-- Filter Console -->
            <div class="glass-panel" style="padding: 1.5rem; border-radius: 16px; margin-bottom: 1.5rem; display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
                <input type="text" id="ai-search-input" placeholder="Search job title for AI drafts..." style="flex: 1; padding: 0.6rem 1rem; border-radius: 8px; background: var(--bg-primary); border: 1px solid var(--border-color); color: var(--text-primary);" autocomplete="off">
                <select id="ai-status-filter" style="padding: 0.6rem; border-radius: 8px; background: var(--bg-primary); border: 1px solid var(--border-color); color: var(--text-primary);">
                    <option value="all">All States</option>
                    <option value="pending" selected>Pending Review</option>
                    <option value="approved">Approved & Live</option>
                    <option value="rejected">Rejected Drafts</option>
                </select>
                <button class="form-btn" id="btn-ai-filter-trigger" style="margin: 0; padding: 0.6rem 1.2rem;">Apply Filter</button>
            </div>

            <!-- Main Data Table -->
            <div class="glass-panel" style="padding: 1.5rem; border-radius: 16px;">
                <div class="responsive-table-container">
                    <table class="portal-table">
                        <thead>
                            <tr>
                                <th>Post ID</th>
                                <th>Recruitment Title</th>
                                <th>AI Engine</th>
                                <th>Draft Status</th>
                                <th>Creation Date</th>
                                <th style="text-align: center;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="ai-management-table-body">
                            <!-- Populated dynamically via AJAX -->
                        </tbody>
                    </table>
                </div>
                <div class="pagination-container" id="ai-management-pagination" style="margin-top: 1.5rem;"></div>
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
<div class="admin-drawer glass-panel" id="job-post-drawer" style="position: fixed; right: -480px; top: 0; width: 450px; height: 100vh; background: var(--bg-secondary); border-left: 1px solid var(--border-color); z-index: 1001; transition: right 0.4s cubic-bezier(0.16, 1, 0.3, 1); padding: 2rem; overflow-y: auto; box-shadow: -10px 0 30px rgba(0,0,0,0.15);">
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
        <h3 style="font-family: 'Outfit'; font-size: 1.4rem; color: var(--accent-color); margin: 0;">AI Draft Editorial Console</h3>
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
                <button type="button" class="form-btn" id="btn-ai-regenerate" style="margin: 0; width: 100%; background: #8b5cf6; border: none;">⚡ Regenerate Draft</button>
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
            <button type="button" class="form-btn" id="btn-ai-drawer-reject" style="background: #ef4444; border: none; margin: 0;">Decline Draft</button>
            <button type="button" class="form-btn" id="btn-ai-drawer-approve" style="background: #10b981; border: none; margin: 0;">Approve & Live</button>
        </div>
        <button type="submit" class="form-btn" style="width: 100%; margin-top: 0.75rem; background: var(--text-secondary); border: none;">Save Draft Changes Only</button>
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
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Toggle Sidebar Dashboard Tabs/Panels
        $('.admin-nav-btn').on('click', function() {
            $('.admin-nav-btn').removeClass('active');
            $(this).addClass('active');

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
            }
        });

        // Toggle Master Data nested tabs
        $('.master-sub-trigger').on('click', function() {
            $('.master-sub-trigger').removeClass('active');
            $(this).addClass('active');

            const targetTab = $(this).data('target');
            $('.master-sub-panel').hide();
            $(`#${targetTab}`).fadeIn(300);
        });

        // Initialize dynamic sidebar overview statistics on first launch
        loadOverviewData();

        // Close Slide-out drawers and backdrops
        function closeAllDrawers() {
            $('.admin-drawer').css('right', '-480px');
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

                        // Populate Overview active crawlers table
                        let trs = '';
                        res.data.sources.forEach(src => {
                            const isAct = src.is_active ? '<span class="badge" style="background:rgba(16,185,129,0.08); color:#10b981;">Active</span>' : '<span class="badge" style="background:rgba(239,68,68,0.08); color:#ef4444;">Suspended</span>';
                            // Find last audit status
                            const log = res.data.logs.find(l => l.source_name === src.name);
                            const healthStatus = log ? log.status : 'pending';
                            let healthBadge = '<span class="badge" style="background:rgba(156,163,175,0.08); color:#9ca3af;">Pending</span>';
                            if (healthStatus === 'success') {
                                healthBadge = '<span class="badge" style="background:rgba(16,185,129,0.08); color:#10b981;">Healthy</span>';
                            } else if (healthStatus === 'failed') {
                                healthBadge = '<span class="badge" style="background:rgba(239,68,68,0.08); color:#ef4444;">Error</span>';
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
                        $('#overview-crawlers-table').html(trs || '<tr><td colspan="4" style="text-align:center; color:var(--text-secondary);">No crawlers active.</td></tr>');

                        // Populate quarantine listings overriding
                        let qHtml = '';
                        res.data.quarantines.forEach(q => {
                            let errs = '';
                            if (q.errors) {
                                Object.keys(q.errors).forEach(k => {
                                    errs += `&bull; ${k}: ${q.errors[k].join(', ')}<br>`;
                                });
                            }
                            qHtml += `
                                <div class="glass-panel" style="padding: 1.25rem; margin-bottom: 1rem; border-left: 4px solid #f59e0b; display: flex; justify-content: space-between; align-items: center; gap: 1rem; background: var(--bg-primary);">
                                    <div style="flex:1;">
                                        <h4 style="font-size: 1.05rem; margin-bottom: 0.25rem;">${q.raw_payload.title || 'Quarantined Announcement'}</h4>
                                        <p style="font-size:0.8rem; color:var(--text-secondary); margin-bottom:0.5rem;">Source Feed: <strong>${q.source_name}</strong> &bull; Crawled: ${q.time}</p>
                                        <div style="font-size:0.75rem; color:#ef4444; font-family:monospace;">${errs || 'Validation limits check failed.'}</div>
                                    </div>
                                    <button class="form-btn btn-rescue-trigger" data-id="${q.id}" data-title="${q.raw_payload.title || ''}" data-url="${q.raw_payload.official_link || ''}" data-errors="${errs}" style="margin:0; padding:0.5rem 1rem; background:#f59e0b;">Rescue</button>
                                </div>
                            `;
                        });
                        $('#admin-quarantine-override-canvas').html(qHtml || '<div style="text-align:center; color:var(--text-secondary); padding: 1rem 0;">Excellent! 0 quarantined listings require manual rescue.</div>');
                    }
                }
            });
        }

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
        let currentJobsPage = 1;
        function loadJobsData(page) {
            currentJobsPage = page;
            const search = $('#jobs-search-input').val();
            const perPage = $('#jobs-per-page').val();

            $.ajax({
                url: '/api/admin/jobs',
                method: 'GET',
                data: { page: page, search: search, per_page: perPage },
                success: function(res) {
                    if (res.status === 'success') {
                        let trs = '';
                        res.data.jobs.forEach(job => {
                            trs += `
                                <tr>
                                    <td>${job.id}</td>
                                    <td><strong>${job.title}</strong><br><span style="font-size:0.75rem; color:var(--text-secondary);">${job.department ? job.department.name : 'Unknown Department'}</span></td>
                                    <td><span class="badge" style="margin-bottom:0;">${job.category ? job.category.name : 'Unassigned'}</span></td>
                                    <td><span class="badge badge-dept" style="margin-bottom:0;">${job.state ? job.state.name : 'Pan India'}</span></td>
                                    <td style="font-weight:bold;">₹ ${Math.round(job.salary_max)}</td>
                                    <td><span class="badge badge-deadline" style="margin-bottom:0;">${job.last_date_to_apply ? job.last_date_to_apply.substring(0, 10) : 'N/A'}</span></td>
                                    <td>
                                        <div style="display:flex; gap:0.5rem; justify-content:center; align-items:center;">
                                            <button class="btn-sm btn-trigger-ai-gen" data-id="${job.id}" style="background: #8b5cf6; border: none; border-radius: 4px; padding: 0.25rem 0.5rem; color: #fff; display: flex; align-items: center; gap: 0.25rem; font-size: 0.75rem; cursor: pointer;">
                                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg> Run AI
                                            </button>
                                            <button class="btn-sm-view btn-edit-job" data-id="${job.id}" data-title="${job.title}" data-category="${job.category_id}" data-dept="${job.department_id}" data-state="${job.state_id}" data-qual="${job.qualification_id}" data-desc="${job.description}" data-min="${job.salary_min}" data-max="${job.salary_max}" data-vac="${job.vacancy_count}" data-fee="${job.application_fee}" data-deadline="${job.last_date_to_apply ? job.last_date_to_apply.substring(0, 10) : ''}" data-url="${job.official_website_link}">Edit</button>
                                            <button class="btn-sm-danger btn-delete-job" data-id="${job.id}">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        });
                        $('#jobs-management-table-body').html(trs || '<tr><td colspan="7" style="text-align:center; color:var(--text-secondary);">No announcements found matching credentials.</td></tr>');

                        // Setup pagination
                        buildPagination('#jobs-management-pagination', res.data.current_page, res.data.last_page, loadJobsData);
                    }
                }
            });
        }

        // Live Search Input debounce
        let searchTimeout;
        $('#jobs-search-input').on('keyup', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                loadJobsData(1);
            }, 300);
        });

        $('#jobs-per-page').on('change', function() {
            loadJobsData(1);
        });

        // Trigger Edit Job
        $(document).on('click', '.btn-edit-job', function() {
            const id = $(this).data('id');
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
        function loadCrawlersData() {
            $.ajax({
                url: '/api/admin/scrapers',
                method: 'GET',
                success: function(res) {
                    if (res.status === 'success') {
                        let trs = '';
                        res.data.forEach(src => {
                            const isActChecked = src.is_active ? 'checked' : '';
                            const selectors = src.selectors_config || {};
                            
                            trs += `
                                <tr>
                                    <td><strong>${src.name}</strong></td>
                                    <td><span style="font-size:0.75rem; color:var(--text-secondary); word-break:break-all;">${src.source_url}</span></td>
                                    <td style="font-family:monospace; font-size:0.8rem;">${src.cron_expression}</td>
                                    <td style="text-align:center;">
                                        <label class="switch" style="position:relative; display:inline-block; width:40px; height:20px; vertical-align:middle;">
                                            <input type="checkbox" class="toggle-scraper-active-switch" data-id="${src.id}" ${isActChecked} style="opacity:0; width:0; height:0;">
                                            <span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background-color:#ccc; border-radius:34px; transition:0.3s; display:inline-block; width:100%; height:100%;"></span>
                                        </label>
                                    </td>
                                    <td style="text-align:center;">
                                        <button class="form-btn btn-run-scraper" data-id="${src.id}" style="margin:0; padding:0.4rem 0.8rem; font-size:0.75rem; background:#10b981;">Crawl Now</button>
                                    </td>
                                    <td>
                                        <div style="display:flex; gap:0.5rem; justify-content:center;">
                                            <button class="btn-sm-view btn-edit-crawler" data-id="${src.id}" data-name="${src.name}" data-url="${src.source_url}" data-cron="${src.cron_expression}" data-active="${src.is_active}" data-row="${selectors.row_selector || ''}" data-title="${selectors.title_selector || ''}" data-link="${selectors.link_selector || ''}" data-cat="${selectors.default_category_id || 1}" data-dept="${selectors.default_department_id || 1}" data-state="${selectors.default_state_id || 1}" data-qual="${selectors.default_qualification_id || 1}">Edit</button>
                                            <button class="btn-sm-danger btn-delete-crawler" data-id="${src.id}">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        });
                        $('#crawlers-management-table-body').html(trs || '<tr><td colspan="6" style="text-align:center; color:var(--text-secondary);">No crawling configurations mapped.</td></tr>');
                        
                        // Style slider switch checked status in DOM
                        applySwitchStyles();
                    }
                }
            });
        }

        // Apply dynamic styled visual for our switches
        function applySwitchStyles() {
            $('.toggle-scraper-active-switch').each(function() {
                const checked = $(this).prop('checked');
                const slider = $(this).siblings('span');
                if (checked) {
                    slider.css('background-color', '#10b981');
                } else {
                    slider.css('background-color', '#ccc');
                }
            });
        }

        // Toggle Switch Active Crawl Targets
        $(document).on('change', '.toggle-scraper-active-switch', function() {
            const id = $(this).data('id');
            const slider = $(this).siblings('span');
            
            $.ajax({
                url: `/api/admin/scraper/${id}/toggle`,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    showToast(res.message, 'success');
                    if (res.is_active) {
                        slider.css('background-color', '#10b981');
                    } else {
                        slider.css('background-color', '#ccc');
                    }
                },
                error: function() {
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
        function loadCategoriesList() {
            $.ajax({
                url: '/api/admin/categories',
                method: 'GET',
                success: function(res) {
                    let trs = '';
                    res.data.forEach(c => {
                        trs += `
                            <tr>
                                <td>${c.id}</td>
                                <td><strong>${c.name}</strong></td>
                                <td><span style="font-size:0.8rem; color:var(--text-secondary);">${c.slug}</span></td>
                                <td>
                                    <div style="display:flex; gap:0.5rem; justify-content:center;">
                                        <button class="btn-sm-view btn-edit-category" data-id="${c.id}" data-name="${c.name}">Edit</button>
                                        <button class="btn-sm-danger btn-delete-category" data-id="${c.id}">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
                    $('#categories-table-body').html(trs || '<tr><td colspan="4" style="text-align:center;">No categories indexed.</td></tr>');
                }
            });
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
        function loadDepartmentsList() {
            $.ajax({
                url: '/api/admin/departments',
                method: 'GET',
                success: function(res) {
                    let trs = '';
                    res.data.forEach(d => {
                        trs += `
                            <tr>
                                <td>${d.id}</td>
                                <td><strong>${d.name}</strong></td>
                                <td style="font-weight:bold;">${d.code}</td>
                                <td>
                                    <div style="display:flex; gap:0.5rem; justify-content:center;">
                                        <button class="btn-sm-view btn-edit-dept" data-id="${d.id}" data-name="${d.name}" data-code="${d.code}">Edit</button>
                                        <button class="btn-sm-danger btn-delete-dept" data-id="${d.id}">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
                    $('#departments-table-body').html(trs || '<tr><td colspan="4" style="text-align:center;">No departments indexed.</td></tr>');
                }
            });
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
        function loadQualificationsList() {
            $.ajax({
                url: '/api/admin/qualifications',
                method: 'GET',
                success: function(res) {
                    let trs = '';
                    res.data.forEach(q => {
                        trs += `
                            <tr>
                                <td>${q.id}</td>
                                <td><strong>${q.name}</strong></td>
                                <td><span style="font-size:0.8rem; color:var(--text-secondary);">${q.slug}</span></td>
                                <td>
                                    <div style="display:flex; gap:0.5rem; justify-content:center;">
                                        <button class="btn-sm-view btn-edit-qual" data-id="${q.id}" data-name="${q.name}">Edit</button>
                                        <button class="btn-sm-danger btn-delete-qual" data-id="${q.id}">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
                    $('#qualifications-table-body').html(trs || '<tr><td colspan="4" style="text-align:center;">No qualifications indexed.</td></tr>');
                }
            });
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
        function loadStatesList() {
            $.ajax({
                url: '/api/admin/states',
                method: 'GET',
                success: function(res) {
                    let trs = '';
                    res.data.forEach(s => {
                        trs += `
                            <tr>
                                <td>${s.id}</td>
                                <td><strong>${s.name}</strong></td>
                                <td style="font-weight:bold;">${s.code}</td>
                                <td>
                                    <div style="display:flex; gap:0.5rem; justify-content:center;">
                                        <button class="btn-sm-view btn-edit-state" data-id="${s.id}" data-name="${s.name}" data-code="${s.code}">Edit</button>
                                        <button class="btn-sm-danger btn-delete-state" data-id="${s.id}">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        `;
                    });
                    $('#states-table-body').html(trs || '<tr><td colspan="4" style="text-align:center;">No states indexed.</td></tr>');
                }
            });
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
        function loadUsersData() {
            $.ajax({
                url: '/api/admin/users',
                method: 'GET',
                success: function(res) {
                    if (res.status === 'success') {
                        let trs = '';
                        res.data.users.forEach(u => {
                            const badgeRole = u.role === 'admin' ? '<span class="badge" style="background:rgba(139,92,246,0.08); color:#8b5cf6;">Administrator</span>' : '<span class="badge badge-dept">Candidate</span>';
                            const badgeState = u.is_active ? '<span class="badge" style="background:rgba(16,185,129,0.08); color:#10b981;">Active Session</span>' : '<span class="badge" style="background:rgba(239,68,68,0.08); color:#ef4444;">Suspended</span>';
                            
                            trs += `
                                <tr>
                                    <td>${u.id}</td>
                                    <td><strong>${u.name}</strong></td>
                                    <td><span style="font-size:0.8rem; color:var(--text-secondary);">${u.email}</span></td>
                                    <td>${badgeRole}</td>
                                    <td style="text-align:center;">${badgeState}</td>
                                    <td>
                                        <div style="display:flex; gap:0.5rem; justify-content:center;">
                                            <button class="btn-sm-view btn-toggle-role" data-id="${u.id}" data-role="${u.role}">Toggle Role</button>
                                            <button class="${u.is_active ? 'btn-sm-danger' : 'btn-sm-success'} btn-toggle-status" data-id="${u.id}" data-active="${u.is_active}">${u.is_active ? 'Suspend' : 'Activate'}</button>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        });
                        $('#users-table-body').html(trs || '<tr><td colspan="6" style="text-align:center;">No users registered.</td></tr>');
                    }
                }
            });
        }

        // Toggle user access role
        $(document).on('click', '.btn-toggle-role', function() {
            const id = $(this).data('id');
            const currentRole = $(this).data('role');
            const targetRole = currentRole === 'admin' ? 'candidate' : 'admin';

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
        let currentQueuePage = 1;
        function loadQueueDashboard(page) {
            currentQueuePage = page || 1;
            
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
            $.ajax({
                url: '/api/admin/queues/failed',
                method: 'GET',
                data: { page: currentQueuePage },
                success: function(res) {
                    if (res.status === 'success') {
                        let trs = '';
                        res.data.items.forEach(job => {
                            trs += `
                                <tr id="dlq-row-${job.uuid}">
                                    <td style="font-family:monospace; font-size:0.8rem; font-weight:bold; color:var(--text-secondary);">${job.uuid}</td>
                                    <td><strong style="color:var(--accent-color);">${job.job_name}</strong></td>
                                    <td><span class="badge" style="background:rgba(37,99,235,0.08); color:var(--accent-color);">${job.queue}</span></td>
                                    <td><span style="font-size:0.8rem; color:#ef4444; font-family:monospace;">${job.exception}</span></td>
                                    <td><span style="font-size:0.8rem; color:var(--text-secondary);">${job.failed_at}</span></td>
                                    <td style="text-align:center; display:flex; gap:0.5rem; justify-content:center;">
                                        <button class="btn-sm btn-queue-retry" data-uuid="${job.uuid}" style="background:#10b981; color:#fff; border:none; padding:0.4rem 0.8rem; border-radius:4px; font-size:0.75rem; cursor:pointer;">Retry</button>
                                        <button class="btn-sm btn-queue-delete" data-uuid="${job.uuid}" style="background:#ef4444; color:#fff; border:none; padding:0.4rem 0.8rem; border-radius:4px; font-size:0.75rem; cursor:pointer;">Forget</button>
                                    </td>
                                </tr>
                            `;
                        });
                        $('#queues-failed-table-body').html(trs || '<tr><td colspan="6" style="text-align:center; color:var(--text-secondary); padding: 1.5rem 0;">Excellent! Dead-Letter Queue is empty. 0 failures.</td></tr>');
                        
                        buildPagination('#queues-failed-pagination', res.data.current_page, res.data.last_page, loadQueueDashboard);
                    }
                }
            });
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
        let currentAuditPage = 1;
        function loadAuditLogs(page) {
            currentAuditPage = page;
            $.ajax({
                url: '/api/admin/activity-logs',
                method: 'GET',
                data: { page: page },
                success: function(res) {
                    if (res.status === 'success') {
                        let trs = '';
                        res.data.logs.forEach(log => {
                            trs += `
                                <tr>
                                    <td><span style="font-size:0.8rem; color:var(--text-secondary);">${log.created_at ? log.created_at.substring(0, 19).replace('T', ' ') : 'N/A'}</span></td>
                                    <td><strong>${log.user ? log.user.name : 'System / Guest'}</strong></td>
                                    <td style="font-family:monospace; font-size:0.8rem;">${log.ip_address}</td>
                                    <td><span class="badge" style="margin:0; background:rgba(37,99,235,0.08); color:var(--accent-color);">${log.action}</span></td>
                                    <td><span style="font-size:0.85rem; color:var(--text-secondary);">${log.details || 'N/A'}</span></td>
                                </tr>
                            `;
                        });
                        $('#audit-logs-table-body').html(trs || '<tr><td colspan="5" style="text-align:center;">No activity audit trails logged.</td></tr>');

                        buildPagination('#audit-logs-pagination', res.data.current_page, res.data.last_page, loadAuditLogs);
                    }
                }
            });
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
        }

        // ─── AI CONTENT MANAGER ACTIONS & LOADER ─────────────────────────────

        let aiContentsCache = [];

        function escapeHtml(text) {
            if (!text) return '';
            return String(text)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Main AJAX draft loader
        window.loadAiContentData = function(page = 1) {
            const status = $('#ai-status-filter').val();
            const search = $('#ai-search-input').val();
            const tableBody = $('#ai-management-table-body');
            const paginationContainer = $('#ai-management-pagination');

            tableBody.html('<tr><td colspan="6" style="text-align: center; padding: 3rem;"><div class="loading-spinner" style="margin: 0 auto 1rem auto;"></div>Retrieving AI draft copies...</td></tr>');

            $.ajax({
                url: '/api/admin/ai-contents',
                method: 'GET',
                data: {
                    status: status,
                    search: search,
                    per_page: 10,
                    page: page
                },
                success: function(res) {
                    if (res.status === 'success') {
                        const data = res.data;
                        aiContentsCache = data.items;

                        // Telemetry Stats Update
                        const tel = data.telemetry;
                        $('#ai-telemetry-engine').text(tel.active_provider.toUpperCase());
                        $('#ai-stat-total').text(tel.total_generated);
                        $('#ai-stat-pending').text(tel.pending_count);
                        $('#ai-stat-approved').text(tel.approved_count);
                        $('#ai-stat-rejected').text(tel.rejected_count);

                        if (data.items.length === 0) {
                            tableBody.html('<tr><td colspan="6" style="text-align: center; padding: 3rem; color: var(--text-secondary);">No AI content drafts matched your filters.</td></tr>');
                            paginationContainer.empty();
                            return;
                        }

                        let html = '';
                        data.items.forEach(function(item) {
                            let statusClass = 'status-badge-draft';
                            if (item.status === 'approved') statusClass = 'status-badge-success';
                            if (item.status === 'rejected') statusClass = 'status-badge-failed';

                            const errorIndicator = item.error_message 
                                ? `<span style="color: #ef4444; font-size: 0.75rem; margin-left: 0.5rem; cursor: help;" title="${escapeHtml(item.error_message)}">⚠ Error</span>` 
                                : '';

                            html += `
                                <tr>
                                    <td>#${item.job_post_id}</td>
                                    <td style="font-weight: 700; color: var(--text-primary); max-width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        ${escapeHtml(item.job_post ? item.job_post.title : 'Deleted Post')}
                                    </td>
                                    <td>
                                        <span class="badge" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6; text-transform: uppercase; font-size: 0.75rem;">
                                            ${escapeHtml(item.provider)}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge ${statusClass}">
                                            ${escapeHtml(item.status)}
                                        </span>
                                        ${errorIndicator}
                                    </td>
                                    <td style="font-size: 0.8rem; color: var(--text-secondary);">
                                        ${new Date(item.created_at).toLocaleDateString('en-IN', {day: '2-digit', month: 'short', year: 'numeric'})}
                                    </td>
                                    <td style="text-align: center;">
                                        <button class="btn-sm btn-review-ai" data-id="${item.id}" style="background: var(--accent-color); border: none; font-size: 0.75rem; padding: 0.4rem 0.8rem; border-radius: 6px; color: #fff;">
                                            Review & Edit
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });

                        tableBody.html(html);

                        // Render Pagination
                        renderCustomPagination(
                            paginationContainer,
                            data.pagination.current_page,
                            data.pagination.last_page,
                            loadAiContentData
                        );
                    }
                },
                error: function() {
                    tableBody.html('<tr><td colspan="6" style="text-align: center; padding: 3rem; color: #ef4444;">Failed to load AI Content Registry. Please check connections.</td></tr>');
                }
            });
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
            const item = aiContentsCache.find(x => x.id === id);
            
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
                    loadAiContentData($('#ai-management-pagination .pagination-btn.active').data('page') || 1);
                },
                error: function(err) {
                    showToast('Failed to approve draft copy.', 'error');
                },
                complete: function() {
                    btn.prop('disabled', false).text('Approve & Live');
                }
            });
        });

        // Decline/Reject action from drawer
        $('#btn-ai-drawer-reject').on('click', function(e) {
            e.preventDefault();
            const id = $('#ai-review-id').val();
            const btn = $(this);
            
            btn.prop('disabled', true).text('Declining...');

            $.ajax({
                url: `/api/admin/ai-contents/${id}/reject`,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    showToast(res.message, 'success');
                    $('#ai-review-drawer').css('right', '-650px');
                    $('#admin-drawer-backdrop').fadeOut(200);
                    loadAiContentData($('#ai-management-pagination .pagination-btn.active').data('page') || 1);
                },
                error: function() {
                    showToast('Failed to decline draft copy.', 'error');
                },
                complete: function() {
                    btn.prop('disabled', false).text('Decline Draft');
                }
            });
        });

        // Regenerate Draft action
        $('#btn-ai-regenerate').on('click', function(e) {
            e.preventDefault();
            const postId = $('#ai-review-post-id').val();
            const provider = $('#ai-review-provider-select').val();
            const btn = $(this);
            
            btn.prop('disabled', true).text('Queueing...');

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
                    showToast('Failed to trigger AI generation task.', 'error');
                },
                complete: function() {
                    btn.prop('disabled', false).text('⚡ Regenerate Draft');
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
                    loadAiContentData($('#ai-management-pagination .pagination-btn.active').data('page') || 1);
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

            btn.prop('disabled', true).text('Queueing...');
            $.ajax({
                url: `/api/admin/ai-contents/generate/${postId}`,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    showToast(res.message, 'success');
                },
                error: function() {
                    showToast('Failed to queue AI generation task.', 'error');
                },
                complete: function() {
                    btn.prop('disabled', false).html('<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg> Run AI');
                }
            });
        });

        // ─── END AI CONTENT MANAGER ──────────────────────────────────────────

    });
</script>
@endsection
