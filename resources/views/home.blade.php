@extends('layouts.app')

@section('title', 'GovJobs - Premium Automated Government Jobs Portal')

@section('content')

<!-- 1. Hero Welcome Segment -->
<section class="hero">
    <h1>Find Your Dream <span style="color: var(--accent-color);">Government Job</span> Today</h1>
    <p>Discover real-time, highly validated recruitment alerts across UPSC, SSC, Banking, Railways, and individual states. Updated automatically, verified by AI, 100% accurate.</p>
</section>

<!-- ======================= PORTAL FRONTEND TAB SEGMENTS ======================= -->

<!-- TAB 1: PRIMARY JOBS DIRECTORY & FILTERS (Active by default) -->
<div class="portal-main-tab active" id="jobs-search-section">
    <!-- Search compass console -->
    <div class="main-grid" style="margin-bottom: 0px; padding-bottom: 0px;">
        <div class="glass-panel search-compass">
            <div>
                <input type="text" id="searchKeywords" placeholder="Search keywords (e.g. UPSC, RBI officer)..." autocomplete="off">
            </div>
            <div>
                <select id="stateSelect">
                    <option value="">Select Region/State</option>
                    @foreach($states as $state)
                        <option value="{{ $state->id }}">{{ $state->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select id="qualificationSelect">
                    <option value="">Select Qualification</option>
                    @foreach($qualifications as $qual)
                        <option value="{{ $qual->id }}">{{ $qual->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select id="categorySelect">
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Main Workspace Split Grid -->
    <div class="main-grid">
        <!-- LEFT SIDE: Dynamic Jobs List Feed (70%) -->
        <div class="jobs-feed-column">
            
            <!-- Featured announcements segment -->
            <div id="featuredSegment" style="margin-bottom: 2.5rem;">
                <h2 style="font-size: 1.4rem; margin-bottom: 1.2rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span style="display:inline-block; width:8px; height:20px; background:var(--accent-color); border-radius:4px;"></span>
                    Premium Featured Announcements
                </h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.2rem;">
                    @forelse($featuredJobs as $fJob)
                        <div class="glass-panel job-card" style="display:block; border-left: 4px solid var(--accent-color); margin-bottom: 0;">
                            <div class="job-info">
                                <span class="badge" style="margin-bottom: 0.5rem; display: inline-block;">FEATURED</span>
                                <h3 style="font-size: 1.1rem; margin-bottom: 0.4rem;">{{ $fJob->title }}</h3>
                                <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.8rem;">
                                    {{ $fJob->department->name ?? 'Government' }} &bull; {{ $fJob->state->name ?? 'Pan India' }}
                                </p>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span class="badge badge-deadline">Apply by {{ $fJob->last_date_to_apply ? $fJob->last_date_to_apply->format('d M') : 'N/A' }}</span>
                                <a href="#" class="btn-view" data-slug="{{ $fJob->slug }}">Details</a>
                            </div>
                        </div>
                    @empty
                        <div class="glass-panel" style="grid-column: 1/-1; padding: 2rem; text-align: center; color: var(--text-secondary);">
                            No featured announcements active at this moment.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Latest active postings section -->
            <div id="latest-jobs">
                <h2 style="font-size: 1.4rem; margin-bottom: 1.2rem; display: flex; align-items: center; justify-content: space-between;">
                    <span style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="display:inline-block; width:8px; height:20px; background:#10b981; border-radius:4px;"></span>
                        Latest Active Recruitments
                    </span>
                    <span id="jobsCountFeedback" style="font-size: 0.85rem; color: var(--text-secondary); font-weight: normal;"></span>
                </h2>

                <!-- Skeleton Placeholders -->
                <div id="skeletonLoader" style="display: none;">
                    <div class="skeleton-job"></div>
                    <div class="skeleton-job"></div>
                    <div class="skeleton-job"></div>
                </div>

                <!-- Dynamic Jobs container -->
                <div id="jobsListContainer">
                    @forelse($recentJobs as $rJob)
                        <div class="glass-panel job-card">
                            <div class="job-info">
                                <h3>{{ $rJob->title }}</h3>
                                <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.5rem;">
                                    {{ $rJob->department->name ?? 'Government' }} &bull; {{ $rJob->state->name ?? 'Pan India' }}
                                </p>
                                <div class="job-tags">
                                    <span class="badge badge-dept">{{ $rJob->qualification->name ?? 'Degree Required' }}</span>
                                    <span class="badge" style="background: rgba(16, 185, 129, 0.08); color: #10b981;">Vacancies: {{ $rJob->vacancy_count }}</span>
                                    <span class="badge badge-deadline">Apply by {{ $rJob->last_date_to_apply ? $rJob->last_date_to_apply->format('d M Y') : 'N/A' }}</span>
                                </div>
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 0.5rem; align-items: flex-end;">
                                <a href="#" class="btn-view" data-slug="{{ $rJob->slug }}">View Details</a>
                                @auth
                                    <button class="btn-sm-danger toggle-bookmark-btn" data-id="{{ $rJob->id }}" style="background: rgba(37,99,235,0.06); color: var(--accent-color); border-color: rgba(37,99,235,0.15);">
                                        Save Job
                                    </button>
                                @endauth
                            </div>
                        </div>
                    @empty
                        <div class="glass-panel" style="padding: 3rem; text-align: center; color: var(--text-secondary);">
                            No recruitment posts active. Check back later!
                        </div>
                    @endforelse
                </div>

                <!-- Dynamic AJAX Pagination container -->
                <div class="pagination-container" id="paginationContainer"></div>
            </div>
        </div>

        <!-- RIGHT SIDE: Utilities Sidebar Panel -->
        <div class="utilities-column">
            <!-- Admit Cards / Syllabus Widget -->
            <div class="glass-panel sidebar-panel" id="admit-cards">
                <div class="tab-headers">
                    <button class="tab-btn active" data-tab="admitCards">Admit Cards</button>
                    <button class="tab-btn" data-tab="examResults">Results</button>
                    <button class="tab-btn" data-tab="syllabi">Syllabus</button>
                </div>
                <div class="tab-content active" id="admitCards">
                    <ul class="tab-list">
                        <li class="tab-item"><a href="#">&rarr; UPSC Civil Services (IAS) 2026 Admit Card</a></li>
                        <li class="tab-item"><a href="#">&rarr; SSC CGL Tier 1 Entry Card</a></li>
                        <li class="tab-item"><a href="#">&rarr; RBI Officer Grade B Exam Schedule</a></li>
                        <li class="tab-item"><a href="#">&rarr; SBI Probationary Officer Exam Hall Ticket</a></li>
                    </ul>
                </div>
                <div class="tab-content" id="examResults">
                    <ul class="tab-list">
                        <li class="tab-item"><a href="#" style="font-weight: 500; color: #10b981;">&check; UPSC IFS Final Selection List 2025</a></li>
                        <li class="tab-item"><a href="#">&check; Railway NTPC CBT 2 Merit List</a></li>
                        <li class="tab-item"><a href="#">&check; IBPS Specialist Officer Mains Result</a></li>
                    </ul>
                </div>
                <div class="tab-content" id="syllabi">
                    <ul class="tab-list">
                        <li class="tab-item"><a href="#">&bull; UPSC IAS Complete Pattern (Prelims & Mains)</a></li>
                        <li class="tab-item"><a href="#">&bull; SSC CGL Tier 1 & Tier 2 Math Syllabus</a></li>
                        <li class="tab-item"><a href="#">&bull; RBI Grade B Phase 1 Syllabus Pattern</a></li>
                    </ul>
                </div>
            </div>

            <!-- Automation Status panel -->
            <div class="glass-panel sidebar-panel" style="border-left: 4px solid #10b981;">
                <h3 style="font-size: 1.1rem; margin-bottom: 0.8rem; display: flex; align-items: center; gap: 0.5rem;">
                    <span style="display:inline-block; width:8px; height:8px; background:#10b981; border-radius:50%; animation: pulse 1s infinite;"></span>
                    Automation Monitor
                </h3>
                <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.8rem;">
                    Our intelligent scraping pipeline parses government portals every 5 minutes, validates parameters deterministically, and isolates errors in quarantine.
                </p>
                <div style="font-size: 0.8rem; background: var(--bg-primary); padding: 0.6rem; border-radius: 6px; border: 1px solid var(--border-color);">
                    <strong>Status:</strong> Active &bull; <strong>System Mode:</strong> Failsafe
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TAB 2: PORTAL INFORMATION HUB (NEW TAB) -->
<div class="portal-main-tab" id="info-hub-section" style="display: none; padding: 0 5%; max-width: 1400px; margin: 0 auto;">
    <h2 style="font-size: 1.8rem; margin-bottom: 1.5rem; font-family: 'Outfit';">Portal Information & Help Center</h2>
    
    <div class="sub-tab-headers">
        <button class="sub-tab-btn active" data-sub="info-blog">Blog & News</button>
        <button class="sub-tab-btn" data-sub="info-timeline">About Portal Timeline</button>
        <button class="sub-tab-btn" data-sub="info-faq">Frequently Asked Questions</button>
        <button class="sub-tab-btn" data-sub="info-contact">Contact Helpdesk</button>
    </div>

    <!-- A. Blog Sub-tab -->
    <div class="sub-tab-content active-sub" id="info-blog">
        <div class="blog-feed-grid">
            <div class="blog-card glass-panel">
                <div class="blog-image-wrapper">UPSC 2026</div>
                <div class="blog-body">
                    <span class="blog-tag">Recruitment News</span>
                    <h3 style="font-size: 1.1rem; margin-bottom: 0.5rem; font-family: 'Outfit';">UPSC Civil Services 2026 Notification Out!</h3>
                    <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1rem; flex-grow: 1;">
                        The Union Public Service Commission has officially announced the vacancies count and cutoff criteria for the IAS/IFS preliminary examinations.
                    </p>
                    <div style="font-size: 0.8rem; color: var(--text-secondary); display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 0.75rem;">
                        <span>Released: Today</span>
                        <a href="#" class="btn-view-sm" style="text-decoration: none; color: var(--accent-color); font-weight: 600;">Read More &rarr;</a>
                    </div>
                </div>
            </div>
            <div class="blog-card glass-panel">
                <div class="blog-image-wrapper" style="background: linear-gradient(135deg, #10b981, #059669);">SSC CGL</div>
                <div class="blog-body">
                    <span class="blog-tag" style="background:rgba(16,185,129,0.08); color:#10b981;">Admit Card Updates</span>
                    <h3 style="font-size: 1.1rem; margin-bottom: 0.5rem; font-family: 'Outfit';">SSC CGL Tier 1 Hall Ticket Release Dates</h3>
                    <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1rem; flex-grow: 1;">
                        Candidates who submitted application forms can download active entry cards starting this Friday by entering their unique birth records.
                    </p>
                    <div style="font-size: 0.8rem; color: var(--text-secondary); display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 0.75rem;">
                        <span>Released: Yesterday</span>
                        <a href="#" class="btn-view-sm" style="text-decoration: none; color: #10b981; font-weight: 600;">Read More &rarr;</a>
                    </div>
                </div>
            </div>
            <div class="blog-card glass-panel">
                <div class="blog-image-wrapper" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">RAILWAYS</div>
                <div class="blog-body">
                    <span class="blog-tag" style="background:rgba(139,92,246,0.08); color:#8b5cf6;">Syllabus Releases</span>
                    <h3 style="font-size: 1.1rem; margin-bottom: 0.5rem; font-family: 'Outfit';">Railway Recruitment Board Syllabus Overhaul</h3>
                    <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1rem; flex-grow: 1;">
                        The selection committee revised general aptitude and science parameters for technical examinations. Read complete subject breakdowns here.
                    </p>
                    <div style="font-size: 0.8rem; color: var(--text-secondary); display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--border-color); padding-top: 0.75rem;">
                        <span>Released: 2 days ago</span>
                        <a href="#" class="btn-view-sm" style="text-decoration: none; color: #8b5cf6; font-weight: 600;">Read More &rarr;</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- B. About Us Timeline Sub-tab -->
    <div class="sub-tab-content" id="info-timeline" style="display: none;">
        <div class="glass-panel" style="padding: 1.75rem;">
            <h3 style="font-family:'Outfit'; margin-bottom: 0.5rem; color: var(--accent-color);">Portal Design & Low-Temperature Scraping Pipeline</h3>
            <p style="font-size: 0.9rem; color: var(--text-secondary); margin-bottom: 1.5rem;">
                GovJobs is engineered with clean PHP Laravel MVC + Service-Repository architecture, keeping API requests blazing-fast and highly secure.
            </p>
            <div class="timeline-flow">
                <div class="timeline-step">
                    <div class="timeline-title">Stage 1: Multi-Feed Target Web Scraper</div>
                    <div class="timeline-desc">Intelligent crawler engines fetch recruitment notifications directly from official portals asynchronously via Background Queues.</div>
                </div>
                <div class="timeline-step">
                    <div class="timeline-title">Stage 2: Deterministic Pre-Parser Validation</div>
                    <div class="timeline-desc">Strict regex filters extract qualification codes, vacancies, cutoff ages, application fees, and deadlines. Matches with incomplete fields are quarantined.</div>
                </div>
                <div class="timeline-step">
                    <div class="timeline-title">Stage 3: Quarantine Override & Live Publish</div>
                    <div class="timeline-desc">Administrators review isolated postings, make corrections with a single click, and synchronize them live into public job directories instantly!</div>
                </div>
            </div>
        </div>
    </div>

    <!-- C. Frequently Asked Questions (Accordion FAQ) Sub-tab -->
    <div class="sub-tab-content" id="info-faq" style="display: none;">
        <div class="glass-panel" style="padding: 1.75rem;">
            <h3 style="font-family:'Outfit'; color: var(--accent-color); margin-bottom: 0.5rem;">Frequently Asked Questions</h3>
            <p style="font-size: 0.9rem; color: var(--text-secondary);">Expand options below to understand GovJobs verification engines and registration policies.</p>
            
            <div class="accordion-wrapper">
                <div class="accordion-item">
                    <div class="accordion-header">Are all listed government job alerts verified?</div>
                    <div class="accordion-content">
                        Yes! Every announcement in our portal is scraped directly from authentic government domain resources (.gov.in / .nic.in) and cross-validated before listing.
                    </div>
                </div>
                <div class="accordion-item">
                    <div class="accordion-header">How does the mock OTP verification system work?</div>
                    <div class="accordion-content">
                        To recover your candidate account, click the 'Reset PW' tab in the authentication modal, input your email, and receive a simulated SMS code '123456' immediately to restore session rights.
                    </div>
                </div>
                <div class="accordion-item">
                    <div class="accordion-header">How can candidates update their alert preferences?</div>
                    <div class="accordion-content">
                        Candidates can sign in, open the 'Dashboard' section, go to the 'Profile Settings' tab, and toggle Email or SMS notifications checkbox configurations in real-time.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- D. Contact Helpdesk Sub-tab -->
    <div class="sub-tab-content" id="info-contact" style="display: none;">
        <div class="glass-panel" style="padding: 1.75rem; max-width: 600px; margin: 0 auto;">
            <h3 style="font-family:'Outfit'; color: var(--accent-color); margin-bottom: 0.5rem; text-align: center;">Contact Portal Support Helpdesk</h3>
            <p style="font-size: 0.85rem; color: var(--text-secondary); text-align: center; margin-bottom: 1.5rem;">
                Have questions or spot a typo on a scraped recruitment feed? Send us a ticket.
            </p>
            <form id="ajaxContactForm">
                @csrf
                <div class="form-group">
                    <label for="contactName">Your Name</label>
                    <input type="text" name="name" id="contactName" class="form-control" placeholder="Candidate Name" required>
                </div>
                <div class="form-group">
                    <label for="contactEmail">Email Address</label>
                    <input type="email" name="email" id="contactEmail" class="form-control" placeholder="candidate@example.com" required>
                </div>
                <div class="form-group">
                    <label for="contactMessage">Support Message / Feedback</label>
                    <textarea name="message" id="contactMessage" class="form-control" rows="4" placeholder="Briefly describe your request..." required></textarea>
                </div>
                <button type="submit" class="form-btn" id="contactSubmitBtn">Submit Support Ticket</button>
            </form>
        </div>
    </div>
</div>

<!-- ======================= AUTH TAB PANELS (LOADED DYNAMICALLY) ======================= -->

<!-- TAB 3: CANDIDATE INTERACTIVE DASHBOARD -->
<div class="portal-main-tab" id="dashboard-section" style="display: none; padding: 0 5%; max-width: 1400px; margin: 0 auto;">
    <h2 style="font-size: 1.8rem; margin-bottom: 1.5rem; font-family: 'Outfit';">Candidate Interactive Dashboard</h2>
    
    <div class="sub-tab-headers" style="margin-bottom: 1.5rem;">
        <button class="sub-tab-btn active dash-sub-trigger" data-target="dash-overview-block">Workspace Overview</button>
        <button class="sub-tab-btn dash-sub-trigger" data-target="dash-settings-block">Profile & Match Alerts Preferences</button>
    </div>

    <!-- Dash Block 1: Overview (Bookmarks and apps table) -->
    <div id="dash-overview-block" class="dash-block-panel">
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; align-items: start;">
            <div>
                <!-- Bookmarked items box -->
                <div class="glass-panel" style="padding: 1.5rem; margin-bottom: 2rem;">
                    <h3 style="font-size: 1.25rem; margin-bottom: 1rem; color: var(--accent-color); font-family: 'Outfit';">Saved Recruitment Postings</h3>
                    <div class="responsive-table-container">
                        <table class="portal-table" id="dashboardBookmarksTable">
                            <thead>
                                <tr>
                                    <th>Job Title</th>
                                    <th>Region</th>
                                    <th>Apply Deadline</th>
                                    <th style="text-align: center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Job Applications box -->
                <div class="glass-panel" style="padding: 1.5rem;">
                    <h3 style="font-size: 1.25rem; margin-bottom: 1rem; color: #10b981; font-family: 'Outfit';">Submitted Applications & Recruiter Status</h3>
                    <div class="responsive-table-container">
                        <table class="portal-table" id="dashboardApplicationsTable">
                            <thead>
                                <tr>
                                    <th>Recruitment Title</th>
                                    <th>Organization</th>
                                    <th>Date Submitted</th>
                                    <th>Process State</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Candidate statistics card -->
            <div class="glass-panel" style="padding: 1.5rem;">
                <h3 style="font-size: 1.25rem; margin-bottom: 1.5rem; font-family: 'Outfit';">Profile Statistics</h3>
                
                <div class="stats-grid" style="grid-template-columns: 1fr; gap: 1rem;">
                    <div class="glass-panel stat-card" style="background: var(--bg-primary); border: 1px solid var(--border-color);">
                        <div class="stat-num" id="statsTotalBookmarks">0</div>
                        <div class="stat-label">Saved Recruitments</div>
                    </div>
                    <div class="glass-panel stat-card" style="background: var(--bg-primary); border: 1px solid var(--border-color);">
                        <div class="stat-num" id="statsTotalApplications" style="color: #10b981;">0</div>
                        <div class="stat-label">Submitted Applications</div>
                    </div>
                </div>

                <div style="border-top: 1px solid var(--border-color); padding-top: 1.25rem; margin-top: 1.25rem; font-size: 0.9rem; color: var(--text-secondary);">
                    <p><strong>Candidate:</strong> <span id="dashCandidateName" style="color: var(--text-primary);">John Doe</span></p>
                    <p><strong>Email:</strong> <span id="dashCandidateEmail">candidate@example.com</span></p>
                    <p><strong>Phone:</strong> <span id="dashCandidatePhone">Not Verified</span></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Dash Block 2: Profile Settings Form -->
    <div id="dash-settings-block" class="dash-block-panel" style="display: none;">
        <div class="glass-panel" style="padding: 2rem; max-width: 700px; margin: 0 auto;">
            <h3 style="font-family:'Outfit'; color: var(--accent-color); margin-bottom: 1.5rem; text-align: center;">Update Profile Settings & Preferences</h3>
            
            <form id="ajaxProfileUpdateForm" style="margin-bottom: 2rem; border-bottom: 1px solid var(--border-color); padding-bottom: 2rem;">
                @csrf
                <div class="form-group">
                    <label for="profileName">Full Name</label>
                    <input type="text" name="name" id="profileName" class="form-control" required>
                    <div class="invalid-feedback" id="profileNameError"></div>
                </div>
                <div class="form-group">
                    <label for="profileEmail">Email Address</label>
                    <input type="email" name="email" id="profileEmail" class="form-control" required>
                    <div class="invalid-feedback" id="profileEmailError"></div>
                </div>
                <div class="form-group">
                    <label for="profilePhone">Phone Number</label>
                    <input type="text" name="phone" id="profilePhone" class="form-control" required>
                    <div class="invalid-feedback" id="profilePhoneError"></div>
                </div>
                
                <div style="background: rgba(37,99,235,0.03); padding: 1rem; border-radius: 8px; border: 1px dashed var(--border-color); margin: 1.5rem 0;">
                    <p style="font-size:0.8rem; color:var(--text-secondary); margin-bottom:1rem;">Leave password fields blank if you do not want to alter credentials.</p>
                    <div class="form-group">
                        <label for="profilePassword">New Password (Min 6 chars)</label>
                        <input type="password" name="password" id="profilePassword" class="form-control" placeholder="••••••••">
                        <div class="invalid-feedback" id="profilePasswordError"></div>
                    </div>
                    <div class="form-group">
                        <label for="profilePasswordConfirm">Confirm New Password</label>
                        <input type="password" name="password_confirmation" id="profilePasswordConfirm" class="form-control" placeholder="••••••••">
                    </div>
                </div>

                <button type="submit" class="form-btn" id="profileUpdateSubmitBtn">Synchronize Profile Settings</button>
            </form>

            <form id="ajaxPreferencesForm">
                @csrf
                <h4 style="font-family:'Outfit'; font-size:1.1rem; color:var(--text-primary); margin-bottom:1rem;">Real-time Recruitment Alert Channels</h4>
                
                <div class="alert-preference-row">
                    <div>
                        <strong>Email Match Notifications</strong><br>
                        <span style="font-size:0.8rem; color:var(--text-secondary);">Receive validation notifications daily on active categories.</span>
                    </div>
                    <input type="checkbox" name="email_alerts" id="prefEmailAlerts" value="1" checked style="width: 20px; height: 20px; cursor: pointer;">
                </div>
                
                <div class="alert-preference-row" style="border-bottom:none; margin-bottom: 1.5rem;">
                    <div>
                        <strong>SMS Verification Alerts</strong><br>
                        <span style="font-size:0.8rem; color:var(--text-secondary);">Send live SMS reminders 24 hours prior to apply deadlines.</span>
                    </div>
                    <input type="checkbox" name="sms_alerts" id="prefSmsAlerts" value="1" checked style="width: 20px; height: 20px; cursor: pointer;">
                </div>

                <button type="submit" class="form-btn" id="preferencesSubmitBtn" style="background:#10b981;">Save Notification Preferences</button>
            </form>
        </div>
    </div>
</div>

<!-- TAB 4: ADMIN SCRAPER SCHEDULE & OVERRIDES PANEL -->
<div class="portal-main-tab" id="admin-section" style="display: none; padding: 0 5%; max-width: 1400px; margin: 0 auto;">
    <h2 style="font-size: 1.8rem; margin-bottom: 1.5rem; font-family: 'Outfit';">Enterprise Automation Control Center</h2>

    <div class="sub-tab-headers" style="margin-bottom: 1.5rem;">
        <button class="sub-tab-btn active admin-sub-trigger" data-target="admin-crawlers-block">Web Crawler Monitors</button>
        <button class="sub-tab-btn admin-sub-trigger" data-target="admin-publisher-block">Manual Recruitment Publisher</button>
        <button class="sub-tab-btn admin-sub-trigger" data-target="admin-users-block">User Registry Elevations Board</button>
        <button class="sub-tab-btn admin-sub-trigger" data-target="admin-seo-block">SEO Caching Console</button>
    </div>

    <!-- Admin Block 1: Crawler Monitors -->
    <div id="admin-crawlers-block" class="admin-block-panel">
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; align-items: start;">
            
            <!-- Scrapers list and live logs -->
            <div>
                <!-- Web Scraper Targets Table -->
                <div class="glass-panel" style="padding: 1.5rem; margin-bottom: 2rem;">
                    <h3 style="font-size: 1.25rem; margin-bottom: 1rem; color: var(--accent-color); font-family: 'Outfit';">Scraper Crawl Target Configurations</h3>
                    <div class="responsive-table-container">
                        <table class="portal-table" id="adminScrapersTable">
                            <thead>
                                <tr>
                                    <th>Source Feed Name</th>
                                    <th>Cron Schedule</th>
                                    <th style="text-align: center;">Crawl Trigger</th>
                                    <th style="text-align: center;">Active State</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Web Scraper Execution Audit logs -->
                <div class="glass-panel" style="padding: 1.5rem; margin-bottom: 2rem;">
                    <h3 style="font-size: 1.25rem; margin-bottom: 1rem; color: var(--text-primary); font-family: 'Outfit';">Scraper Dispatch Execution Audits</h3>
                    <div class="responsive-table-container">
                        <table class="portal-table" id="adminScraperLogsTable">
                            <thead>
                                <tr>
                                    <th>Feed Announcement</th>
                                    <th>State</th>
                                    <th>Items Gathered</th>
                                    <th>Diagnostics / Error</th>
                                    <th>Crawl Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Loaded via AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- AUTO-QUARANTINE MANUAL OVERRIDE RESCUE PANEL -->
                <div class="glass-panel" style="padding: 1.5rem;">
                    <h3 style="font-size: 1.25rem; margin-bottom: 1rem; color: #f59e0b; font-family: 'Outfit';">Quarantined Scraped Listings (Manual Review Override Panel)</h3>
                    <div id="adminQuarantinedContainer">
                        <!-- Loaded dynamically via AJAX -->
                    </div>
                </div>
            </div>

            <!-- Scraper Statistics panel -->
            <div class="glass-panel" style="padding: 1.5rem;">
                <h3 style="font-size: 1.25rem; margin-bottom: 1.5rem; font-family: 'Outfit';">Automation System Health</h3>
                
                <div class="stats-grid" style="grid-template-columns: 1fr; gap: 1rem;">
                    <div class="glass-panel stat-card" style="background: var(--bg-primary); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.5rem;">
                        <div class="stat-label" style="text-align: left; font-size: 0.9rem;">Crawl Targets</div>
                        <div class="stat-num" id="metricsTotalSources" style="font-size: 1.6rem; margin: 0;">0</div>
                    </div>
                    <div class="glass-panel stat-card" style="background: var(--bg-primary); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.5rem;">
                        <div class="stat-label" style="text-align: left; font-size: 0.9rem;">Successful Runs</div>
                        <div class="stat-num" id="metricsSuccessRuns" style="color: #10b981; font-size: 1.6rem; margin: 0;">0</div>
                    </div>
                    <div class="glass-panel stat-card" style="background: var(--bg-primary); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.5rem;">
                        <div class="stat-label" style="text-align: left; font-size: 0.9rem;">Quarantined Records</div>
                        <div class="stat-num" id="metricsQuarantineRuns" style="color: #f59e0b; font-size: 1.6rem; margin: 0;">0</div>
                    </div>
                    <div class="glass-panel stat-card" style="background: var(--bg-primary); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.5rem;">
                        <div class="stat-label" style="text-align: left; font-size: 0.9rem;">Critical Failures</div>
                        <div class="stat-num" id="metricsFailedRuns" style="color: #ef4444; font-size: 1.6rem; margin: 0;">0</div>
                    </div>
                </div>

                <div style="border-top: 1px solid var(--border-color); padding-top: 1.25rem; margin-top: 1.25rem; font-size: 0.85rem; color: var(--text-secondary); text-align: center;">
                    <p>Enterprise Automation Failsafe: Enabled</p>
                    <p style="margin-top: 0.25rem;">Active Queue Worker: SQS / Sync Connection</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Block 2: Manual Job Publisher Form -->
    <div id="admin-publisher-block" class="admin-block-panel" style="display: none;">
        <div class="glass-panel" style="padding: 2rem; max-width: 800px; margin: 0 auto;">
            <h3 style="font-family:'Outfit'; color: var(--accent-color); margin-bottom: 0.5rem; text-align: center;">Publish Manual Job Announcement</h3>
            <p style="font-size:0.85rem; color:var(--text-secondary); text-align:center; margin-bottom:1.5rem;">Broadcast a verified recruitment opportunity directly into GovJobs directories instantly.</p>
            
            <form id="ajaxManualJobForm">
                @csrf
                <div class="form-group">
                    <label for="mjTitle">Recruitment Post Title</label>
                    <input type="text" name="title" id="mjTitle" class="form-control" placeholder="e.g. UPSC Assistant Commandant Recruitment 2026" required>
                    <div class="invalid-feedback" id="mjTitleError"></div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.25rem;">
                    <div class="form-group">
                        <label for="mjCategory">Job Category</label>
                        <select name="category_id" id="mjCategory" class="form-control" required>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="mjDepartment">Partner Organization / Department</label>
                        <select name="department_id" id="mjDepartment" class="form-control" required>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.25rem;">
                    <div class="form-group">
                        <label for="mjState">Region / State</label>
                        <select name="state_id" id="mjState" class="form-control" required>
                            @foreach($states as $st)
                                <option value="{{ $st->id }}">{{ $st->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="mjQualification">Minimum Qualification</label>
                        <select name="qualification_id" id="mjQualification" class="form-control" required>
                            @foreach($qualifications as $ql)
                                <option value="{{ $ql->id }}">{{ $ql->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="mjDescription">Recruitment Overview & Eligibility Details</label>
                    <textarea name="description" id="mjDescription" class="form-control" rows="5" placeholder="Provide clear specifications, age bar exemptions, screening tests outline..." required></textarea>
                    <div class="invalid-feedback" id="mjDescriptionError"></div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:1rem;">
                    <div class="form-group">
                        <label for="mjSalaryMin">Min Salary (Monthly ₹)</label>
                        <input type="number" name="salary_min" id="mjSalaryMin" class="form-control" value="35000" required>
                    </div>
                    <div class="form-group">
                        <label for="mjSalaryMax">Max Salary (Monthly ₹)</label>
                        <input type="number" name="salary_max" id="mjSalaryMax" class="form-control" value="112000" required>
                    </div>
                    <div class="form-group">
                        <label for="mjVacancies">Vacancies count</label>
                        <input type="number" name="vacancy_count" id="mjVacancies" class="form-control" value="10" required>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.25rem;">
                    <div class="form-group">
                        <label for="mjFee">Application Fees (₹)</label>
                        <input type="number" name="application_fee" id="mjFee" class="form-control" value="100" required>
                    </div>
                    <div class="form-group">
                        <label for="mjDeadline">Apply Deadline (Valid Date)</label>
                        <input type="date" name="last_date_to_apply" id="mjDeadline" class="form-control" required>
                        <div class="invalid-feedback" id="mjDeadlineError"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="mjOfficialLink">Official Recruitment Web Link</label>
                    <input type="url" name="official_website_link" id="mjOfficialLink" class="form-control" placeholder="https://upsc.gov.in" required>
                    <div class="invalid-feedback" id="mjOfficialLinkError"></div>
                </div>

                <button type="submit" class="form-btn" id="mjSubmitBtn">Publish Announcement Live</button>
            </form>
        </div>
    </div>

    <!-- Admin Block 3: User Registry Board Table -->
    <div id="admin-users-block" class="admin-block-panel" style="display: none;">
        <div class="glass-panel" style="padding: 1.5rem;">
            <h3 style="font-size: 1.25rem; margin-bottom: 1rem; color: var(--accent-color); font-family: 'Outfit';">User Access Registry & Security Clearances</h3>
            <p style="font-size:0.85rem; color:var(--text-secondary); margin-bottom:1.5rem;">Toggle candidate/administrator session roles or suspend/activate user profiles synchronously.</p>
            
            <div class="responsive-table-container">
                <table class="portal-table" id="adminUsersRegistryTable">
                    <thead>
                        <tr>
                            <th>User ID</th>
                            <th>Candidate Name</th>
                            <th>Email Profile</th>
                            <th>Phone Contact</th>
                            <th>Access Role</th>
                            <th style="text-align: center;">Account Status</th>
                            <th style="text-align: center;">Elevations / Toggles</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Populated dynamically via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Admin Block 4: SEO Caching Console -->
    <div id="admin-seo-block" class="admin-block-panel" style="display: none;">
        <div class="glass-panel" style="padding: 2rem; max-width: 600px; margin: 0 auto;">
            <h3 style="font-family:'Outfit'; color: var(--accent-color); margin-bottom: 0.5rem; text-align: center;">SEO Meta Caching Console</h3>
            <p style="font-size:0.85rem; color:var(--text-secondary); text-align:center; margin-bottom:1.5rem;">Configure dynamic keywords and metadata dynamically cached in local JSON store configurations.</p>
            
            <form id="ajaxSeoSettingsForm">
                @csrf
                <div class="form-group">
                    <label for="seoTitle">Homepage Meta Title</label>
                    <input type="text" name="meta_title" id="seoTitle" class="form-control" value="GovJobs - Premium Automated Government Jobs Portal" required>
                    <div class="invalid-feedback" id="seoTitleError"></div>
                </div>
                <div class="form-group">
                    <label for="seoDescription">Meta Description</label>
                    <textarea name="meta_description" id="seoDescription" class="form-control" rows="4" required>Discover real-time, highly validated recruitment alerts verified by AI across UPSC, SSC, Banking, and Railways. Fast, mobile responsive, and fully automated.</textarea>
                    <div class="invalid-feedback" id="seoDescriptionError"></div>
                </div>
                <div class="form-group">
                    <label for="seoKeywords">Meta Keywords (Comma separated)</label>
                    <input type="text" name="meta_keywords" id="seoKeywords" class="form-control" value="government jobs, upsc, ssc, banking, railways, rrb, admit cards, results" required>
                    <div class="invalid-feedback" id="seoKeywordsError"></div>
                </div>
                <button type="submit" class="form-btn" id="seoSubmitBtn" style="background:#8b5cf6;">Synchronize Meta Tags Cache</button>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        
        // 1. Interactive Sidebar Tab Switches (Local DOM shifts for admit card panel)
        $('.tab-btn').on('click', function() {
            const targetTab = $(this).data('tab');
            $(this).siblings().removeClass('active');
            $(this).addClass('active');
            $(`#${targetTab}`).siblings('.tab-content').removeClass('active');
            $(`#${targetTab}`).addClass('active');
        });

        // FAQ accordion transitions
        $(document).on('click', '.accordion-header', function() {
            $(this).parent('.accordion-item').toggleClass('active').siblings().removeClass('active');
        });

        // Contact Support ticket simulated dispatches
        $('#ajaxContactForm').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#contactSubmitBtn');
            btn.prop('disabled', true).text('Sending message...');
            setTimeout(() => {
                btn.prop('disabled', false).text('Submit Support Ticket');
                showToast('Support ticket dispatched successfully! Our helpline agents will review your message shortly.', 'success');
                $('#ajaxContactForm')[0].reset();
            }, 800);
        });

        // ================= NAVBAR PORTAL TAB TRIGGERS =================
        $('.nav-tab-trigger').on('click', function(e) {
            e.preventDefault();
            const target = $(this).data('target'); // 'dashboard', 'admin', 'jobs', 'info-hub'
            
            // Toggle view panels
            $('.portal-main-tab').hide();
            
            if (target === 'dashboard') {
                $('#dashboard-section').fadeIn();
                loadDashboardData();
            } else if (target === 'admin') {
                $('#admin-section').fadeIn();
                loadAdminData();
            } else if (target === 'info-hub') {
                $('#info-hub-section').fadeIn();
            } else {
                $('#jobs-search-section').fadeIn();
            }
        });

        // Sub-tabs transitions inside Information Hub
        $('.sub-tab-btn[data-sub]').on('click', function(e) {
            e.preventDefault();
            $(this).siblings().removeClass('active');
            $(this).addClass('active');
            const targetSub = $(this).data('sub');
            $(`#${targetSub}`).siblings('.sub-tab-content').hide();
            $(`#${targetSub}`).fadeIn();
        });

        // Sub-tabs transitions inside Candidate Dashboard settings
        $('.dash-sub-trigger').on('click', function(e) {
            e.preventDefault();
            $('.dash-sub-trigger').removeClass('active');
            $(this).addClass('active');
            const targetBlock = $(this).data('target');
            $('.dash-block-panel').hide();
            $(`#${targetBlock}`).fadeIn();
        });

        // Sub-tabs transitions inside Administration Control panels
        $('.admin-sub-trigger').on('click', function(e) {
            e.preventDefault();
            $('.admin-sub-trigger').removeClass('active');
            $(this).addClass('active');
            const targetBlock = $(this).data('target');
            $('.admin-block-panel').hide();
            $(`#${targetBlock}`).fadeIn();
            
            if (targetBlock === 'admin-users-block') {
                loadUsersRegistry();
            }
        });

        // If URL hash points to section, load automatically
        const currentHash = window.location.hash;
        if (currentHash === '#dashboard-section') {
            $('.nav-tab-trigger[data-target="dashboard"]').trigger('click');
        } else if (currentHash === '#admin-section') {
            $('.nav-tab-trigger[data-target="admin"]').trigger('click');
        }

        // ================== SEARCH AND PAGINATION SYSTEM ==================
        let currentPage = 1;

        function fetchJobs(page = 1) {
            currentPage = page;
            const queryData = {
                search: $('#searchKeywords').val(),
                state_id: $('#stateSelect').val(),
                qualification_id: $('#qualificationSelect').val(),
                category_id: $('#categorySelect').val(),
                page: page
            };

            $('#jobsListContainer').hide();
            $('#paginationContainer').empty();
            $('#skeletonLoader').show();

            $.ajax({
                url: '/',
                type: 'GET',
                data: queryData,
                dataType: 'json',
                success: function(response) {
                    $('#skeletonLoader').hide();
                    
                    if (response.status === 'success') {
                        const data = response.data;
                        const jobs = data.jobs;

                        $('#jobsCountFeedback').text(`Found ${data.total} recruitments`);

                        if (jobs.length === 0) {
                            $('#jobsListContainer').html(`
                                <div class="glass-panel" style="padding: 3rem; text-align: center; color: var(--text-secondary);">
                                    No recruitment postings match your exact search criteria. Try modifying your filters.
                                </div>
                            `).fadeIn();
                            return;
                        }

                        // Rebuild HTML cards dynamically
                        let html = '';
                        jobs.forEach(function(job) {
                            const isFeaturedBadge = job.is_featured ? '<span class="badge" style="background:var(--accent-color); color:#fff; font-size:0.75rem;">FEATURED</span>' : '';
                            html += `
                                <div class="glass-panel job-card">
                                    <div class="job-info">
                                        <h3 style="display:flex; align-items:center; gap:0.5rem;">
                                            ${job.title} 
                                            ${isFeaturedBadge}
                                        </h3>
                                        <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.5rem;">
                                            ${job.department} &bull; ${job.state}
                                        </p>
                                        <div class="job-tags">
                                            <span class="badge badge-dept">${job.qualification}</span>
                                            <span class="badge" style="background: rgba(16, 185, 129, 0.08); color: #10b981;">Vacancies: ${job.vacancy_count}</span>
                                            <span class="badge badge-deadline">Apply by ${job.last_date}</span>
                                        </div>
                                    </div>
                                    <div style="display: flex; flex-direction: column; gap: 0.5rem; align-items: flex-end;">
                                        <a href="#" class="btn-view" data-slug="${job.slug}">View Details</a>
                                        @auth
                                            <button class="btn-sm-danger toggle-bookmark-btn" data-id="${job.id}" style="background: rgba(37,99,235,0.06); color: var(--accent-color); border-color: rgba(37,99,235,0.15);">
                                                Save Job
                                            </button>
                                        @endauth
                                    </div>
                                </div>
                            `;
                        });

                        $('#jobsListContainer').html(html).fadeIn();
                        buildPagination(data.current_page, data.last_page);
                    }
                },
                error: function() {
                    $('#skeletonLoader').hide();
                    $('#jobsListContainer').html(`
                        <div class="glass-panel" style="padding: 3rem; text-align: center; color: #ef4444; border-color: rgba(239,68,68,0.2);">
                            <strong>System error occurred!</strong> Could not synchronize listings. Please try again.
                        </div>
                    `).fadeIn();
                }
            });
        }

        // Render Pagination buttons
        function buildPagination(current, last) {
            if (last <= 1) return;

            let html = '';
            if (current > 1) {
                html += `<a href="#" class="page-link" data-page="${current - 1}">&laquo; Prev</a>`;
            }
            for (let i = 1; i <= last; i++) {
                const activeClass = i === current ? 'active' : '';
                html += `<a href="#" class="page-link ${activeClass}" data-page="${i}">${i}</a>`;
            }
            if (current < last) {
                html += `<a href="#" class="page-link" data-page="${current + 1}">Next &raquo;</a>`;
            }
            $('#paginationContainer').html(html);
        }

        // Trigger filters
        $('#stateSelect, #qualificationSelect, #categorySelect').on('change', function() {
            fetchJobs(1);
        });

        // Search Input Keyup Debouncing
        let searchTimeout = null;
        $('#searchKeywords').on('keyup', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                fetchJobs(1);
            }, 300);
        });

        $(document).on('click', '.page-link', function(e) {
            e.preventDefault();
            const targetPage = $(this).data('page');
            fetchJobs(targetPage);
        });

        // ================== BOOKMARK SWITCH CLICKS ==================
        $(document).on('click', '.toggle-bookmark-btn', function(e) {
            e.preventDefault();
            const btn = $(this);
            const jobId = btn.data('id');
            btn.prop('disabled', true);

            $.ajax({
                url: `/api/jobs/${jobId}/bookmark`,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    btn.prop('disabled', false);
                    showToast(res.message, 'success');
                    if (res.action === 'added') {
                        btn.text('Remove Save').css({'color': '#ef4444', 'border-color': 'rgba(239,68,68,0.15)', 'background': 'rgba(239,68,68,0.06)'});
                    } else {
                        btn.text('Save Job').css({'color': 'var(--accent-color)', 'border-color': 'rgba(37,99,235,0.15)', 'background': 'rgba(37,99,235,0.06)'});
                    }
                },
                error: function(err) {
                    btn.prop('disabled', false);
                    if (err.status === 401) {
                        showToast('Please log in to save recruitments!', 'warning');
                        $('#authModal').addClass('active');
                    } else {
                        showToast('Failed to save recruitment.', 'error');
                    }
                }
            });
        });

        // ================== DETAILED ASYNC POPUP MODAL ==================
        const detailsModal = $('#jobDetailsModal');

        $(document).on('click', '.btn-view', function(e) {
            e.preventDefault();
            const slug = $(this).data('slug');
            if (!slug) return;

            detailsModal.addClass('active');
            $('#modalSkeletonLoader').show();
            $('#modalRealContent').hide();
            $('#modalApplicationFormBlock').hide();

            $.ajax({
                url: `/api/jobs/${slug}`,
                method: 'GET',
                success: function(res) {
                    if (res.status === 'success') {
                        const job = res.data;
                        $('#detailTitle').text(job.title);
                        $('#detailCategory').text(job.category);
                        $('#detailDepartment').text(job.department);
                        $('#detailState').text(job.state);
                        $('#detailSalary').text(`${job.salary_min} - ${job.salary_max}`);
                        $('#detailAge').text(job.age_limit);
                        $('#detailVacancies').text(job.vacancy_count);
                        $('#detailFee').text(job.application_fee);
                        $('#detailDeadline').text(job.last_date);
                        $('#detailExamDate').text(job.exam_date);
                        $('#detailDescription').text(job.description);
                        $('#detailSyllabus').html(`<strong>Written Test Pattern:</strong> ${job.exam_pattern}<br><br><strong>Major Selection Criteria:</strong> ${job.selection_process}`);
                        $('#detailSelection').text(job.selection_process);
                        $('#detailOfficialLink').attr('href', job.official_website_link);
                        
                        // Bind apply buttons
                        $('#modalApplyBtn').data('id', job.id).show();
                        $('#applicationFormJobId').val(job.id);
                        
                        $('#modalSkeletonLoader').hide();
                        $('#modalRealContent').fadeIn();
                    }
                },
                error: function() {
                    showToast('Failed to retrieve recruitment specifications.', 'error');
                    detailsModal.removeClass('active');
                }
            });
        });

        $('#closeJobDetailsModal, #jobDetailsModal').on('click', function(e) {
            if (e.target === this || e.target.id === 'closeJobDetailsModal') {
                detailsModal.removeClass('active');
            }
        });

        // Show application form section inside details modal
        $('#modalApplyBtn').on('click', function() {
            $('#modalRealContent').hide();
            $('#modalApplicationFormBlock').fadeIn();
        });

        $('#cancelApplicationBtn').on('click', function() {
            $('#modalApplicationFormBlock').hide();
            $('#modalRealContent').fadeIn();
        });

        // ================== AJAX RECRUITMENT APPLICATION FORM SUBMIT ==================
        $('#recruitmentApplicationForm').on('submit', function(e) {
            e.preventDefault();
            const form = $(this);
            const jobId = $('#applicationFormJobId').val();
            const btn = $('#submitApplicationBtn');
            
            btn.prop('disabled', true).text('Submitting Form...');
            $('.invalid-feedback').hide();

            const formData = new FormData(this);

            $.ajax({
                url: `/api/jobs/${jobId}/apply`,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    btn.prop('disabled', false).text('Submit Application');
                    showToast(res.message, 'success');
                    detailsModal.removeClass('active');
                    form[0].reset();
                },
                error: function(err) {
                    btn.prop('disabled', false).text('Submit Application');
                    if (err.status === 422) {
                        const res = err.responseJSON;
                        showToast(res.message || 'Validation error', 'error');
                        if (res.errors && res.errors.resume) {
                            $('#appResumeError').text(res.errors.resume[0]).show();
                        }
                    } else {
                        showToast('Submission error occurred.', 'error');
                    }
                }
            });
        });

        // ================== CANDIDATE DASHBOARD LOADER ==================
        function loadDashboardData() {
            const bTable = $('#dashboardBookmarksTable tbody');
            const aTable = $('#dashboardApplicationsTable tbody');

            bTable.html('<tr><td colspan="4" style="text-align:center;">Loading Saved bookmarks...</td></tr>');
            aTable.html('<tr><td colspan="4" style="text-align:center;">Loading Submitted applications...</td></tr>');

            $.ajax({
                url: '/api/dashboard',
                method: 'GET',
                success: function(res) {
                    if (res.status === 'success') {
                        const data = res.data;
                        
                        // Set stats
                        $('#statsTotalBookmarks').text(data.bookmarks.length);
                        $('#statsTotalApplications').text(data.applications.length);
                        
                        // Set user profile info
                        $('#dashCandidateName').text(data.user.name);
                        $('#dashCandidateEmail').text(data.user.email);
                        $('#dashCandidatePhone').text(data.user.phone);

                        // Seed form inputs in Profile Settings
                        $('#profileName').val(data.user.name);
                        $('#profileEmail').val(data.user.email);
                        $('#profilePhone').val(data.user.phone);

                        // Render Saved Bookmarks
                        if (data.bookmarks.length === 0) {
                            bTable.html('<tr><td colspan="4" style="text-align:center; color: var(--text-secondary);">No recruitment alerts bookmarked.</td></tr>');
                        } else {
                            let bHtml = '';
                            data.bookmarks.forEach(book => {
                                bHtml += `
                                    <tr>
                                        <td style="font-weight:600;">${book.title}</td>
                                        <td>${book.state}</td>
                                        <td style="color:#ef4444; font-weight:500;">${book.last_date}</td>
                                        <td style="text-align:center;">
                                            <button class="btn-sm-danger delete-bookmark-btn" data-id="${book.job_id}" style="margin-right:0.5rem;">Delete</button>
                                            <a href="#" class="btn-view btn-view-sm" data-slug="${book.slug}" style="padding: 0.35rem 0.75rem; font-size:0.75rem;">View</a>
                                        </td>
                                    </tr>
                                `;
                            });
                            bTable.html(bHtml);
                        }

                        // Render Submitted Applications
                        if (data.applications.length === 0) {
                            aTable.html('<tr><td colspan="4" style="text-align:center; color: var(--text-secondary);">No job applications submitted.</td></tr>');
                        } else {
                            let aHtml = '';
                            data.applications.forEach(app => {
                                let statusClass = 'status-applied';
                                if (app.status === 'reviewing') statusClass = 'status-reviewing';
                                if (app.status === 'shortlisted') statusClass = 'status-shortlisted';
                                if (app.status === 'rejected') statusClass = 'status-rejected';

                                aHtml += `
                                    <tr>
                                        <td style="font-weight:600;">${app.title}</td>
                                        <td>${app.department}</td>
                                        <td>${app.applied_at}</td>
                                        <td>
                                            <span class="status-badge ${statusClass}">${app.status}</span>
                                        </td>
                                    </tr>
                                `;
                            });
                            aTable.html(aHtml);
                        }
                    }
                },
                error: function() {
                    showToast('Failed to fetch candidate dashboard metrics.', 'error');
                }
            });
        }

        // Inline Delete Bookmark from Dashboard table
        $(document).on('click', '.delete-bookmark-btn', function(e) {
            e.preventDefault();
            const jobId = $(this).data('id');
            $.ajax({
                url: `/api/jobs/${jobId}/bookmark`,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    showToast('Bookmark deleted.', 'success');
                    loadDashboardData();
                }
            });
        });

        // Candidate updates profile details
        $('#ajaxProfileUpdateForm').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#profileUpdateSubmitBtn');
            btn.prop('disabled', true).text('Updating Profile...');
            $('.invalid-feedback').hide();

            $.ajax({
                url: '/api/profile/update',
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    btn.prop('disabled', false).text('Synchronize Profile Settings');
                    showToast(res.message, 'success');
                    $('#profilePassword').val('');
                    $('#profilePasswordConfirm').val('');
                    loadDashboardData();
                },
                error: function(err) {
                    btn.prop('disabled', false).text('Synchronize Profile Settings');
                    if (err.status === 422) {
                        const res = err.responseJSON;
                        showToast('Correction validation failed.', 'error');
                        if (res.errors) {
                            Object.keys(res.errors).forEach(key => {
                                $(`#profile${key.charAt(0).toUpperCase() + key.slice(1)}Error`).text(res.errors[key][0]).show();
                            });
                        }
                    } else {
                        showToast('Server update failed.', 'error');
                    }
                }
            });
        });

        // Candidate alerts preference settings
        $('#ajaxPreferencesForm').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#preferencesSubmitBtn');
            btn.prop('disabled', true).text('Saving...');

            $.ajax({
                url: '/api/profile/preferences',
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    btn.prop('disabled', false).text('Save Notification Preferences');
                    showToast(res.message, 'success');
                },
                error: function() {
                    btn.prop('disabled', false).text('Save Notification Preferences');
                    showToast('Failed to sync settings.', 'error');
                }
            });
        });

        // ================== ENTERPRISE ADMIN DASHBOARD LOADER ==================
        function loadAdminData() {
            const scTable = $('#adminScrapersTable tbody');
            const lTable = $('#adminScraperLogsTable tbody');
            const qContainer = $('#adminQuarantinedContainer');

            scTable.html('<tr><td colspan="4" style="text-align:center;">Loading scraper configurations...</td></tr>');
            lTable.html('<tr><td colspan="5" style="text-align:center;">Loading dispatch execution logs...</td></tr>');
            qContainer.html('<p style="text-align:center; color: var(--text-secondary);">Scanning isolated quarantined items...</p>');

            $.ajax({
                url: '/api/admin/data',
                method: 'GET',
                success: function(res) {
                    if (res.status === 'success') {
                        const data = res.data;

                        // Render Metrics
                        $('#metricsTotalSources').text(data.metrics.total_sources);
                        $('#metricsSuccessRuns').text(data.metrics.success_runs);
                        $('#metricsQuarantineRuns').text(data.metrics.quarantine_runs);
                        $('#metricsFailedRuns').text(data.metrics.failed_runs);

                        // Render Scrapers Config Table
                        let scHtml = '';
                        data.sources.forEach(src => {
                            const checkedAttr = src.is_active ? 'checked' : '';
                            scHtml += `
                                <tr>
                                    <td style="font-weight:600;">${src.name}</td>
                                    <td><code>${src.cron}</code></td>
                                    <td style="text-align:center;">
                                        <button class="btn-sm-danger trigger-crawling-btn" data-id="${src.id}" style="background: rgba(16, 185, 129, 0.08); color:#10b981; border-color:rgba(16, 185, 129, 0.15);">
                                            Crawl Now
                                        </button>
                                    </td>
                                    <td style="text-align:center;">
                                        <div class="toggle-switch-container" style="justify-content:center;">
                                            <label class="toggle-switch">
                                                <input type="checkbox" class="toggle-scraper-switch" data-id="${src.id}" ${checkedAttr}>
                                                <span class="toggle-slider"></span>
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        });
                        scTable.html(scHtml);

                        // Render Logs Table
                        let lHtml = '';
                        data.logs.forEach(log => {
                            let badgeClass = 'badge-success';
                            if (log.status === 'failed') badgeClass = 'badge-failed';
                            if (log.status === 'quarantined') badgeClass = 'badge-quarantined';
                            if (log.status === 'duplicate') badgeClass = 'badge-duplicate';

                            lHtml += `
                                <tr>
                                    <td><strong>${log.source_name}</strong></td>
                                    <td><span class="admin-badge-status ${badgeClass}">${log.status}</span></td>
                                    <td style="text-align:center; font-weight:600;">${log.items_found}</td>
                                    <td style="font-size:0.8rem; color:var(--text-secondary); max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                        ${log.error_message}
                                    </td>
                                    <td>${log.time}</td>
                                </tr>
                            `;
                        });
                        lTable.html(lHtml);

                        // Render Quarantined Override items review
                        if (data.quarantines.length === 0) {
                            qContainer.html(`
                                <div class="glass-panel" style="padding: 2rem; text-align: center; color: #10b981; border-color: rgba(16,185,129,0.2); background: rgba(16,185,129,0.02);">
                                    &check; Clean Slate: All scraped listings validated 100% and parsed into directories!
                                </div>
                            `);
                        } else {
                            let qHtml = '';
                            data.quarantines.forEach(item => {
                                let errorsList = '';
                                if (item.errors) {
                                    Object.keys(item.errors).forEach(errKey => {
                                        errorsList += `&bull; ${item.errors[errKey]}<br>`;
                                    });
                                }

                                qHtml += `
                                    <div class="quarantine-card glass-panel" id="quarantine_card_${item.id}">
                                        <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--border-color); padding-bottom:0.75rem; margin-bottom:0.75rem;">
                                            <div>
                                                <h4 style="font-family:'Outfit'; font-size:1.1rem; color:var(--text-primary);">${item.source_name}</h4>
                                                <small style="color:var(--text-secondary);">${item.time}</small>
                                            </div>
                                            <span class="admin-badge-status badge-quarantined">Isolated</span>
                                        </div>

                                        <div class="quarantine-error-log">
                                            <strong>Validation Omissions Detected:</strong><br>
                                            ${errorsList || 'Missing vital parameters required for candidate application triggers.'}
                                        </div>

                                        <form class="quarantine-rescue-override-form" data-id="${item.id}">
                                            @csrf
                                            <div class="quarantine-grid-fields">
                                                <div class="form-group" style="margin-bottom:0;">
                                                    <label style="font-size:0.8rem; margin-bottom:0.25rem;">Correct Recruitment Title</label>
                                                    <input type="text" name="title" class="form-control" style="padding:0.5rem 0.75rem; font-size:0.85rem;" value="${item.raw_payload.title || ''}" required>
                                                </div>
                                                <div class="form-group" style="margin-bottom:0;">
                                                    <label style="font-size:0.8rem; margin-bottom:0.25rem;">Application Fee (₹)</label>
                                                    <input type="number" name="application_fee" class="form-control" style="padding:0.5rem 0.75rem; font-size:0.85rem;" value="0" required>
                                                </div>
                                            </div>

                                            <div class="quarantine-grid-fields">
                                                <div class="form-group" style="margin-bottom:0;">
                                                    <label style="font-size:0.8rem; margin-bottom:0.25rem;">Apply Deadline (Valid Date)</label>
                                                    <input type="date" name="last_date_to_apply" class="form-control" style="padding:0.5rem 0.75rem; font-size:0.85rem;" required>
                                                </div>
                                                <div class="form-group" style="margin-bottom:0;">
                                                    <label style="font-size:0.8rem; margin-bottom:0.25rem;">Vacancies Count</label>
                                                    <input type="number" name="vacancy_count" class="form-control" style="padding:0.5rem 0.75rem; font-size:0.85rem;" value="25" required>
                                                </div>
                                            </div>

                                            <div class="quarantine-grid-fields">
                                                <div class="form-group" style="margin-bottom:0;">
                                                    <label style="font-size:0.8rem; margin-bottom:0.25rem;">Official Recruitment Website</label>
                                                    <input type="url" name="official_website_link" class="form-control" style="padding:0.5rem 0.75rem; font-size:0.85rem;" value="${item.raw_payload.link || 'https://upsc.gov.in'}" required>
                                                </div>
                                                <div class="form-group" style="margin-bottom:0;">
                                                    <label style="font-size:0.8rem; margin-bottom:0.25rem;">Apply Hyperlink (Online App)</label>
                                                    <input type="url" name="apply_link" class="form-control" style="padding:0.5rem 0.75rem; font-size:0.85rem;" value="${item.raw_payload.link || 'https://upsconline.nic.in'}">
                                                </div>
                                            </div>

                                            <div style="text-align:right; margin-top:1.25rem;">
                                                <button type="submit" class="form-btn rescue-submit-btn" style="margin-top:0; width:auto; padding: 0.5rem 1.5rem; font-size:0.85rem; background:#f59e0b;">
                                                    Rescue & Publish Announcement
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                `;
                            });
                            qContainer.html(qHtml);
                        }
                    }
                },
                error: function() {
                    showToast('Failed to retrieve scraper statistics.', 'error');
                }
            });
        }

        // Admin Action: Toggle Scraper active scheduling
        $(document).on('change', '.toggle-scraper-switch', function() {
            const id = $(this).data('id');
            $.ajax({
                url: `/api/admin/scraper/${id}/toggle`,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    showToast(res.message, 'success');
                    loadAdminData();
                },
                error: function() {
                    showToast('Failed to switch scraper states.', 'error');
                }
            });
        });

        // Admin Action: Manually run and crawl web source feed
        $(document).on('click', '.trigger-crawling-btn', function(e) {
            e.preventDefault();
            const btn = $(this);
            const id = btn.data('id');
            btn.prop('disabled', true).text('Crawling...');

            $.ajax({
                url: `/api/admin/scraper/${id}/run`,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    btn.prop('disabled', false).text('Crawl Now');
                    showToast(res.message, 'success');
                    setTimeout(() => {
                        loadAdminData();
                    }, 1500);
                },
                error: function() {
                    btn.prop('disabled', false).text('Crawl Now');
                    showToast('Failed to dispatch background crawler.', 'error');
                }
            });
        });

        // Admin Action: Submit Quarantined Rescue Form
        $(document).on('submit', '.quarantine-rescue-override-form', function(e) {
            e.preventDefault();
            const form = $(this);
            const logId = form.data('id');
            const btn = form.find('.rescue-submit-btn');

            btn.prop('disabled', true).text('Rescuing...');

            $.ajax({
                url: `/api/admin/quarantine/${logId}/rescue`,
                method: 'POST',
                data: form.serialize(),
                success: function(res) {
                    showToast(res.message, 'success');
                    $(`#quarantine_card_${logId}`).slideUp(400, function() {
                        $(this).remove();
                        loadAdminData();
                    });
                },
                error: function(err) {
                    btn.prop('disabled', false).text('Rescue & Publish Announcement');
                    if (err.status === 422) {
                        const res = err.responseJSON;
                        let errText = '';
                        Object.keys(res.errors).forEach(key => {
                            errText += `${res.errors[key][0]}\n`;
                        });
                        showToast(errText || 'Correction validation failed.', 'error');
                    } else {
                        showToast('Failed to override quarantine.', 'error');
                    }
                }
            });
        });

        // Admin Action: Load Registered Users
        function loadUsersRegistry() {
            const uTable = $('#adminUsersRegistryTable tbody');
            uTable.html('<tr><td colspan="7" style="text-align:center;">Scanning registry files...</td></tr>');

            $.ajax({
                url: '/api/admin/users',
                method: 'GET',
                success: function(res) {
                    if (res.status === 'success') {
                        let html = '';
                        res.data.users.forEach(u => {
                            const isActiveBadge = u.is_active ? '<span class="status-badge status-shortlisted">Active</span>' : '<span class="status-badge status-rejected">Suspended</span>';
                            const roleBadge = u.role === 'admin' ? '<span class="role-badge role-admin">Admin</span>' : '<span class="role-badge role-candidate">Candidate</span>';
                            const toggleRoleBtnText = u.role === 'admin' ? 'Demote Candidate' : 'Promote Admin';
                            const toggleActiveBtnText = u.is_active ? 'Suspend' : 'Activate';
                            const activeBtnClass = u.is_active ? 'btn-sm-danger' : 'btn-sm-success';
                            
                            html += `
                                <tr>
                                    <td><strong>#${u.id}</strong></td>
                                    <td style="font-weight:600;">${u.name}</td>
                                    <td>${u.email}</td>
                                    <td>${u.phone}</td>
                                    <td>${roleBadge}</td>
                                    <td style="text-align:center;">${isActiveBadge}</td>
                                    <td style="text-align:center;">
                                        <button class="btn-view btn-view-sm toggle-user-role-btn" data-id="${u.id}" data-role="${u.role === 'admin' ? 'candidate' : 'admin'}" style="padding:0.35rem 0.6rem; font-size:0.75rem; margin-right:0.25rem;">
                                            ${toggleRoleBtnText}
                                        </button>
                                        <button class="${activeBtnClass} toggle-user-status-btn" data-id="${u.id}" data-active="${u.is_active ? 0 : 1}" style="padding:0.35rem 0.6rem; font-size:0.75rem;">
                                            ${toggleActiveBtnText}
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                        uTable.html(html);
                    }
                },
                error: function() {
                    uTable.html('<tr><td colspan="7" style="text-align:center; color:#ef4444;">Failed to sync user records.</td></tr>');
                }
            });
        }

        // Toggle user role promotion
        $(document).on('click', '.toggle-user-role-btn', function() {
            const id = $(this).data('id');
            const targetRole = $(this).data('role');
            $.ajax({
                url: `/api/admin/users/${id}/update`,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}', role: targetRole },
                success: function(res) {
                    showToast(res.message, 'success');
                    loadUsersRegistry();
                },
                error: function(err) {
                    showToast(err.responseJSON.message || 'Operation failed.', 'error');
                }
            });
        });

        // Toggle user suspension status
        $(document).on('click', '.toggle-user-status-btn', function() {
            const id = $(this).data('id');
            const targetActive = $(this).data('active');
            $.ajax({
                url: `/api/admin/users/${id}/update`,
                method: 'POST',
                data: { _token: '{{ csrf_token() }}', is_active: targetActive },
                success: function(res) {
                    showToast(res.message, 'success');
                    loadUsersRegistry();
                },
                error: function(err) {
                    showToast(err.responseJSON.message || 'Operation failed.', 'error');
                }
            });
        });

        // Admin publishes manual job announcement
        $('#ajaxManualJobForm').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#mjSubmitBtn');
            btn.prop('disabled', true).text('Publishing live...');
            $('.invalid-feedback').hide();

            $.ajax({
                url: '/api/admin/jobs/store',
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    btn.prop('disabled', false).text('Publish Announcement Live');
                    showToast(res.message, 'success');
                    $('#ajaxManualJobForm')[0].reset();
                    fetchJobs(1);
                },
                error: function(err) {
                    btn.prop('disabled', false).text('Publish Announcement Live');
                    if (err.status === 422) {
                        const res = err.responseJSON;
                        showToast('Correction validation failed.', 'error');
                        if (res.errors) {
                            Object.keys(res.errors).forEach(key => {
                                $(`#mj${key.charAt(0).toUpperCase() + key.slice(1)}Error`).text(res.errors[key][0]).show();
                            });
                        }
                    } else {
                        showToast('Server manual publish failed.', 'error');
                    }
                }
            });
        });

        // Admin updates dynamic SEO tags in cache
        $('#ajaxSeoSettingsForm').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#seoSubmitBtn');
            btn.prop('disabled', true).text('Caching meta...');
            $('.invalid-feedback').hide();

            $.ajax({
                url: '/api/admin/seo/update',
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    btn.prop('disabled', false).text('Synchronize Meta Tags Cache');
                    showToast(res.message, 'success');
                },
                error: function(err) {
                    btn.prop('disabled', false).text('Synchronize Meta Tags Cache');
                    if (err.status === 422) {
                        showToast('Metadata validation failed.', 'error');
                    } else {
                        showToast('Server meta update failed.', 'error');
                    }
                }
            });
        });

    });
</script>
@endsection
