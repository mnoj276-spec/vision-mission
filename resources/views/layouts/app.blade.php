@php
    $seo = [
        'meta_title' => 'GovJobs - Premium Automated Government Jobs Portal',
        'meta_description' => 'Discover real-time, highly validated recruitment alerts verified by AI across UPSC, SSC, Banking, and Railways. Fast, mobile responsive, and fully automated.',
        'meta_keywords' => 'government jobs, upsc, ssc, banking, railways, rrb, admit cards, results'
    ];
    $settingsPath = storage_path('app/seo_settings.json');
    if (file_exists($settingsPath)) {
        $settings = json_decode(file_get_contents($settingsPath), true);
        if (is_array($settings)) {
            $seo = array_merge($seo, $settings);
        }
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $seo['meta_title'])</title>
    
    <!-- Meta SEO Binds -->
    <meta name="description" content="{{ $seo['meta_description'] }}">
    <meta name="keywords" content="{{ $seo['meta_keywords'] }}">
    <meta name="robots" content="index, follow">
    
    <!-- Custom Design Stylesheet -->
    <link rel="stylesheet" href="{{ asset('assets/css/portal.css') }}">
</head>
<body>

    <!-- 1. Mega Menu Header & Glassmorphic Navigation -->
    <header class="glass-panel">
        <nav class="navbar">
            <a href="/" class="logo">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--accent-color);"><path d="M12 2L2 7l10 5 10-5-10-5z"></path><path d="M2 17l10 5 10-5"></path><path d="M2 12l10 5 10-5"></path></svg>
                Gov<span>Jobs</span>
            </a>
            
            <ul class="nav-links">
                <li><a href="/" class="nav-tab-trigger" data-target="jobs">Home</a></li>
                <li><a href="/ssc-jobs" style="font-weight: 700;">SSC Board</a></li>
                <li><a href="/railway-jobs" style="font-weight: 700;">Railways</a></li>
                <li><a href="/upsc-jobs" style="font-weight: 700;">UPSC</a></li>
                <li><a href="/state-jobs" style="font-weight: 700;">State Jobs</a></li>
                <li><a href="#" class="nav-tab-trigger" data-target="info-hub">Info Hub</a></li>
            </ul>

            <div class="header-actions" style="display: flex; gap: 0.75rem; align-items: center;">
                <button class="theme-toggle-btn" id="themeToggle">
                    <svg id="themeIcon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></svg>
                    <span id="themeText">Night Mode</span>
                </button>

                @auth
                    <div class="user-menu-dropdown">
                        <button class="theme-toggle-btn">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--accent-color);"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            <span style="max-width: 100px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ auth()->user()->name }}</span>
                        </button>
                        <div class="dropdown-menu">
                            <a href="#dashboard-section" class="dropdown-item nav-tab-trigger" data-target="dashboard">Dashboard</a>
                            @if(auth()->user()->role === 'admin')
                                <a href="{{ route('admin.dashboard') }}" class="dropdown-item">Admin Panel</a>
                            @endif
                            <div class="dropdown-divider"></div>
                            <button class="dropdown-item" id="logoutBtn" style="border:none; background:none; width:100%; cursor:pointer;">Logout</button>
                        </div>
                    </div>
                @else
                    <button class="theme-toggle-btn" id="openAuthModalBtn">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>
                        Login / Register
                    </button>
                @endauth

                <!-- Hamburger menu button for smaller screens -->
                <button class="hamburger-btn" id="hamburgerMenuBtn" aria-label="Toggle Navigation Menu">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                </button>
            </div>
        </nav>
    </header>

    <!-- Mobile Glassmorphic Navigation Drawer -->
    <div class="mobile-drawer-overlay" id="mobileDrawerOverlay"></div>
    <div class="mobile-drawer glass-panel" id="mobileDrawer">
        <div class="mobile-drawer-header">
            <a href="/" class="logo">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--accent-color);"><path d="M12 2L2 7l10 5 10-5-10-5z"></path><path d="M2 17l10 5 10-5"></path><path d="M2 12l10 5 10-5"></path></svg>
                Gov<span>Jobs</span>
            </a>
            <button class="drawer-close-btn" id="closeMobileDrawerBtn">&times;</button>
        </div>
        <ul class="mobile-drawer-links">
            <li><a href="/" class="nav-tab-trigger mobile-drawer-link" data-target="jobs">Home</a></li>
            <li><a href="#latest-jobs" class="nav-tab-trigger mobile-drawer-link" data-target="jobs">Jobs List</a></li>
            <li><a href="#" class="nav-tab-trigger mobile-drawer-link" data-target="info-hub">Information Hub</a></li>
            <li><a href="#admit-cards" class="mobile-drawer-link">Exam Utilities</a></li>
        </ul>
    </div>

    <!-- 2. Master Dynamic Content -->
    <main>
        @yield('content')
    </main>

    <!-- 3. Dynamic Footer -->
    <footer style="background-color: var(--bg-secondary); border-top: 1px solid var(--border-color); padding: 3rem 5% 2rem 5%; font-size: 0.9rem; color: var(--text-secondary); margin-top: 4rem;">
        <div style="max-width: 1400px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
            <div>
                <h3 style="color: var(--text-primary); margin-bottom: 1rem; font-family: 'Outfit';">GovJobs</h3>
                <p>An advanced, fully automated Government Recruitment Job Portal featuring low-temperature validation engines and zero full page refreshes.</p>
            </div>
            <div>
                <h4 style="color: var(--text-primary); margin-bottom: 1rem; font-family: 'Outfit';">Portal Hubs</h4>
                <ul style="list-style: none; display: grid; gap: 0.5rem;">
                    <li><a href="#" class="nav-tab-trigger" data-target="jobs" style="color: var(--text-secondary); text-decoration: none;">Recruitments Board</a></li>
                    <li><a href="#" class="nav-tab-trigger" data-target="info-hub" style="color: var(--text-secondary); text-decoration: none;">Information Hub</a></li>
                    <li><a href="#" class="nav-tab-trigger" data-target="info-hub" style="color: var(--text-secondary); text-decoration: none;">FAQ Accordions</a></li>
                </ul>
            </div>
            <div>
                <h4 style="color: var(--text-primary); margin-bottom: 1rem; font-family: 'Outfit';">Recruitment Partners</h4>
                <ul style="list-style: none; display: grid; gap: 0.5rem;">
                    <li><a href="#" style="color: var(--text-secondary); text-decoration: none;">Union Public Service Commission (UPSC)</a></li>
                    <li><a href="#" style="color: var(--text-secondary); text-decoration: none;">Staff Selection Commission (SSC)</a></li>
                    <li><a href="#" style="color: var(--text-secondary); text-decoration: none;">Reserve Bank of India (RBI)</a></li>
                </ul>
            </div>
        </div>
        <div style="max-width: 1400px; margin: 0 auto; border-top: 1px solid var(--border-color); padding-top: 1.5rem; display: flex; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
            <p>&copy; 2026 GovJobs Portal Automation Inc. All rights reserved.</p>
            <p>Developed with robust MVC + Service-Repository architecture.</p>
        </div>
    </footer>

    <!-- ================= DYNAMIC MODALS OVERLAYS ================= -->

    <!-- A. Authenticatable Login/Register Modal -->
    <div class="modal-overlay" id="authModal">
        <div class="modal-box glass-panel">
            <button class="modal-close-btn" id="closeAuthModal">&times;</button>
            <div class="auth-tabs">
                <button class="auth-tab-btn active" data-tab="loginFormContainer">Sign In</button>
                <button class="auth-tab-btn" data-tab="registerFormContainer">Register</button>
                <button class="auth-tab-btn" data-tab="forgotFormContainer">Reset PW</button>
            </div>

            <!-- Login Sub-Form -->
            <div id="loginFormContainer">
                <form id="ajaxLoginForm">
                    @csrf
                    <div class="form-group">
                        <label for="loginEmail">Email Address</label>
                        <input type="email" name="email" id="loginEmail" class="form-control" placeholder="candidate@example.com" required>
                        <div class="invalid-feedback" id="loginEmailError"></div>
                    </div>
                    <div class="form-group">
                        <label for="loginPassword">Password</label>
                        <input type="password" name="password" id="loginPassword" class="form-control" placeholder="••••••••" required>
                        <div class="invalid-feedback" id="loginPasswordError"></div>
                    </div>
                    <button type="submit" class="form-btn" id="loginSubmitBtn">Sign In</button>
                    <p class="form-text">Forgot password? <a href="#" class="auth-toggle-link" data-target="forgotFormContainer">Recover account</a></p>
                </form>
            </div>

            <!-- Register Sub-Form -->
            <div id="registerFormContainer" style="display: none;">
                <form id="ajaxRegisterForm">
                    @csrf
                    <div class="form-group">
                        <label for="regName">Full Name</label>
                        <input type="text" name="name" id="regName" class="form-control" placeholder="John Doe" required>
                        <div class="invalid-feedback" id="regNameError"></div>
                    </div>
                    <div class="form-group">
                        <label for="regEmail">Email Address</label>
                        <input type="email" name="email" id="regEmail" class="form-control" placeholder="johndoe@example.com" required>
                        <div class="invalid-feedback" id="regEmailError"></div>
                    </div>
                    <div class="form-group">
                        <label for="regPhone">Phone Number</label>
                        <input type="text" name="phone" id="regPhone" class="form-control" placeholder="9876543210" required>
                        <div class="invalid-feedback" id="regPhoneError"></div>
                    </div>
                    <div class="form-group">
                        <label for="regPassword">Password (Min 6 chars)</label>
                        <input type="password" name="password" id="regPassword" class="form-control" placeholder="••••••••" required>
                        <div class="invalid-feedback" id="regPasswordError"></div>
                    </div>
                    <div class="form-group">
                        <label for="regPasswordConfirm">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="regPasswordConfirm" class="form-control" placeholder="••••••••" required>
                    </div>
                    <button type="submit" class="form-btn" id="registerSubmitBtn">Register Now</button>
                    <p class="form-text">Already registered? <a href="#" class="auth-toggle-link" data-target="loginFormContainer">Sign In instead</a></p>
                </form>
            </div>

            <!-- Forgot Password OTP Reset Flow -->
            <div id="forgotFormContainer" style="display: none;">
                <!-- Step 1: Send OTP code -->
                <form id="ajaxForgotForm">
                    @csrf
                    <div class="form-group">
                        <label for="forgotEmail">Registered Email Address</label>
                        <input type="email" name="email" id="forgotEmail" class="form-control" placeholder="candidate@example.com" required>
                        <div class="invalid-feedback" id="forgotEmailError"></div>
                    </div>
                    <button type="submit" class="form-btn" id="forgotSubmitBtn">Send Verification Code</button>
                </form>

                <!-- Step 2: Validate OTP and Set password -->
                <form id="ajaxResetForm" style="display: none; border-top: 1px solid var(--border-color); padding-top: 1.5rem; margin-top: 1.5rem;">
                    @csrf
                    <input type="hidden" name="email" id="resetEmailHidden">
                    <div class="form-group">
                        <label for="resetOtp">Enter OTP Code (Sent: 123456)</label>
                        <input type="text" name="otp_code" id="resetOtp" class="form-control" placeholder="123456" required>
                        <div class="invalid-feedback" id="resetOtpError"></div>
                    </div>
                    <div class="form-group">
                        <label for="resetPassword">New Password (Min 6 chars)</label>
                        <input type="password" name="password" id="resetPassword" class="form-control" placeholder="••••••••" required>
                        <div class="invalid-feedback" id="resetPasswordError"></div>
                    </div>
                    <div class="form-group">
                        <label for="resetPasswordConfirm">Confirm New Password</label>
                        <input type="password" name="password_confirmation" id="resetPasswordConfirm" class="form-control" placeholder="••••••••" required>
                    </div>
                    <button type="submit" class="form-btn" id="resetSubmitBtn">Synchronize Password</button>
                </form>
            </div>
        </div>
    </div>

    <!-- B. Asynchronous Job Details Modal -->
    <div class="modal-overlay" id="jobDetailsModal">
        <div class="modal-box glass-panel" style="max-width: 800px;">
            <button class="modal-close-btn" id="closeJobDetailsModal">&times;</button>
            
            <!-- Skeleton Loader placeholder inside modal -->
            <div id="modalSkeletonLoader" class="skeleton-modal">
                <div class="skeleton-modal-line" style="height: 40px; width: 60%;"></div>
                <div class="skeleton-modal-line" style="height: 20px; width: 40%; margin-bottom: 2rem;"></div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
                    <div class="skeleton-modal-line" style="height: 80px;"></div>
                    <div class="skeleton-modal-line" style="height: 80px;"></div>
                </div>
                <div class="skeleton-modal-line" style="height: 150px; width: 100%;"></div>
            </div>

            <!-- Authentic details block loaded dynamically -->
            <div id="modalRealContent" style="display: none;">
                <div class="details-header">
                    <h2 id="detailTitle" style="font-size: 1.8rem; margin-bottom: 0.25rem;"></h2>
                    <p style="color: var(--text-secondary); display: flex; gap: 0.75rem; flex-wrap: wrap; font-size: 0.95rem; margin-top: 0.5rem;">
                        <span class="badge" id="detailCategory"></span>
                        <span class="badge badge-dept" id="detailDepartment"></span>
                        <span class="badge" id="detailState" style="background: rgba(139, 92, 246, 0.1); color: #8b5cf6;"></span>
                    </p>
                </div>

                <div class="details-grid">
                    <div class="glass-panel" style="padding: 1rem; border-radius: 8px;">
                        <div class="details-box-label">Salary Range (Monthly)</div>
                        <div class="details-box-val">₹ <span id="detailSalary"></span></div>
                    </div>
                    <div class="glass-panel" style="padding: 1rem; border-radius: 8px;">
                        <div class="details-box-label">Age Requirements</div>
                        <div class="details-box-val" id="detailAge"></div>
                    </div>
                    <div class="glass-panel" style="padding: 1rem; border-radius: 8px;">
                        <div class="details-box-label">Total Vacancies</div>
                        <div class="details-box-val" id="detailVacancies"></div>
                    </div>
                    <div class="glass-panel" style="padding: 1rem; border-radius: 8px;">
                        <div class="details-box-label">Application Fees</div>
                        <div class="details-box-val">₹ <span id="detailFee"></span></div>
                    </div>
                    <div class="glass-panel" style="padding: 1rem; border-radius: 8px;">
                        <div class="details-box-label">Application Deadline</div>
                        <div class="details-box-val" id="detailDeadline" style="color: #ef4444;"></div>
                    </div>
                    <div class="glass-panel" style="padding: 1rem; border-radius: 8px;">
                        <div class="details-box-label">Expected Exam Date</div>
                        <div class="details-box-val" id="detailExamDate"></div>
                    </div>
                </div>

                <div class="details-full-section">
                    <h4>Recruitment Overview & Eligibility</h4>
                    <p id="detailDescription" style="color: var(--text-secondary); margin-bottom: 1.25rem; font-size: 0.95rem;"></p>
                </div>

                <div class="details-full-section">
                    <h4>Official Syllabus & Exam Pattern</h4>
                    <div class="details-syllabus-container" id="detailSyllabus"></div>
                </div>

                <div class="details-full-section">
                    <h4>Selection Process</h4>
                    <p id="detailSelection" style="color: var(--text-secondary); font-size: 0.95rem;"></p>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 2rem; border-top: 1px solid var(--border-color); padding-top: 1.5rem; flex-wrap: wrap;">
                    <a id="detailOfficialLink" href="" target="_blank" class="btn-view" style="flex:1; text-align:center;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px; vertical-align: middle;"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                        Official Advertisement
                    </a>
                    
                    @auth
                        <button id="modalApplyBtn" class="form-btn" style="flex:1.5; margin-top:0; padding: 0.8rem;">
                            Apply Recruitment Now
                        </button>
                    @else
                        <button class="form-btn trigger-auth-redirect-btn" style="flex:1.5; margin-top:0; padding: 0.8rem; background: var(--text-secondary);">
                            Login to Apply Now
                        </button>
                    @endauth
                </div>
            </div>

            <!-- Application Form section loaded inside details modal -->
            <div id="modalApplicationFormBlock" style="display: none; border-top: 1px solid var(--border-color); padding-top: 1.5rem; margin-top: 1.5rem;">
                <h3 style="font-family: 'Outfit'; font-size: 1.3rem; margin-bottom: 0.5rem; color: var(--accent-color);">Recruitment Submission Form</h3>
                <form id="recruitmentApplicationForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="job_id" id="applicationFormJobId">
                    <div class="form-group">
                        <label for="appResume">Upload Default CV / Resume (PDF, DOC, DOCX up to 2MB)</label>
                        <input type="file" name="resume" id="appResume" class="form-control" required>
                        <div class="invalid-feedback" id="appResumeError"></div>
                    </div>
                    <div style="display:flex; gap:0.75rem; margin-top:1.5rem;">
                        <button type="button" class="btn-view" id="cancelApplicationBtn" style="flex:1; text-align:center;">Cancel</button>
                        <button type="submit" class="form-btn" id="submitApplicationBtn" style="flex:2; margin-top:0;">Submit Application</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- C. Sidebar/Utilities Details Modal (Admit Cards / Results / Syllabus Detail) -->
    <div class="modal-overlay" id="sidebarDetailsModal">
        <div class="modal-box glass-panel" style="max-width: 600px;">
            <button class="modal-close-btn" id="closeSidebarDetailsModal">&times;</button>
            <h3 style="font-family: 'Outfit'; font-size: 1.4rem; margin-bottom: 1.25rem; color: var(--accent-color);" id="sidebarDetailTitle">Exam Utility Info</h3>
            <div id="sidebarDetailBody" style="color: var(--text-secondary); font-size: 0.95rem; line-height: 1.75;">
                <!-- Loaded dynamically -->
            </div>
        </div>
    </div>

    <!-- D. Sliding toast feedback alerts -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- 4. jQuery CDN and Theme JS Controller -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        // Global Sliding Toast Dispenser
        function showToast(message, type = 'success') {
            const container = $('#toastContainer');
            const toastId = 'toast_' + Math.floor(Math.random() * 1000);
            
            let icon = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>';
            if (type === 'error') {
                icon = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>';
            } else if (type === 'warning') {
                icon = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>';
            }

            const toastHtml = `
                <div class="toast toast-${type}" id="${toastId}">
                    ${icon}
                    <span>${message}</span>
                </div>
            `;
            container.append(toastHtml);
            
            setTimeout(() => {
                $(`#${toastId}`).addClass('active');
            }, 50);

            setTimeout(() => {
                const toast = $(`#${toastId}`);
                toast.removeClass('active');
                setTimeout(() => {
                    toast.remove();
                }, 400);
            }, 3500);
        }

        $(document).ready(function() {
            // Theme Toggle Controller Binds
            const body = $('body');
            const themeBtn = $('#themeToggle');
            const themeText = $('#themeText');
            
            if (localStorage.getItem('theme') === 'dark') {
                body.addClass('dark-theme');
                themeText.text('Day Mode');
            }

            themeBtn.on('click', function() {
                body.toggleClass('dark-theme');
                if (body.hasClass('dark-theme')) {
                    localStorage.setItem('theme', 'dark');
                    themeText.text('Day Mode');
                } else {
                    localStorage.setItem('theme', 'light');
                    themeText.text('Night Mode');
                }
            });

            // ================== MOBILE DRAWER TOGGLE SYSTEM ==================
            const mDrawer = $('#mobileDrawer');
            const mOverlay = $('#mobileDrawerOverlay');
            
            function openDrawer() {
                mDrawer.addClass('active');
                mOverlay.addClass('active');
                body.css('overflow', 'hidden');
            }
            
            function closeDrawer() {
                mDrawer.removeClass('active');
                mOverlay.removeClass('active');
                body.css('overflow', '');
            }

            $('#hamburgerMenuBtn').on('click', openDrawer);
            $('#closeMobileDrawerBtn, #mobileDrawerOverlay, .mobile-drawer-link').on('click', closeDrawer);


            // ================== LOGIN MODAL TRIGGERS ==================
            const authModal = $('#authModal');
            
            $(document).on('click', '#openAuthModalBtn, .trigger-auth-redirect-btn', function(e) {
                e.preventDefault();
                $('#ajaxLoginForm')[0].reset();
                $('#ajaxRegisterForm')[0].reset();
                $('#ajaxForgotForm')[0].reset();
                $('#ajaxResetForm')[0].reset();
                $('#ajaxResetForm').hide();
                $('#ajaxForgotForm').show();
                $('.invalid-feedback').hide();
                authModal.addClass('active');
            });

            $('#closeAuthModal, #authModal').on('click', function(e) {
                if (e.target === this || e.target.id === 'closeAuthModal') {
                    authModal.removeClass('active');
                }
            });

            // Tab Switching inside Auth modal
            $('.auth-tab-btn').on('click', function() {
                $('.auth-tab-btn').removeClass('active');
                $(this).addClass('active');
                
                const target = $(this).data('tab');
                $('#loginFormContainer, #registerFormContainer, #forgotFormContainer').hide();
                $(`#${target}`).show();
            });

            $('.auth-toggle-link').on('click', function(e) {
                e.preventDefault();
                const target = $(this).data('target');
                $(`.auth-tab-btn[data-tab="${target}"]`).trigger('click');
            });

            // ================== AJAX LOGIN SUBMISSION ==================
            $('#ajaxLoginForm').on('submit', function(e) {
                e.preventDefault();
                const btn = $('#loginSubmitBtn');
                btn.prop('disabled', true).text('Signing In...');
                $('.invalid-feedback').hide();

                $.ajax({
                    url: '/api/login',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        showToast(res.message, 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    },
                    error: function(err) {
                        btn.prop('disabled', false).text('Sign In');
                        if (err.status === 422 || err.status === 401) {
                            const res = err.responseJSON;
                            showToast(res.message || 'Login failed', 'error');
                            if (res.errors) {
                                Object.keys(res.errors).forEach(key => {
                                    $(`#login${key.charAt(0).toUpperCase() + key.slice(1)}Error`).text(res.errors[key][0]).show();
                                });
                            }
                        } else {
                            showToast('Server connection failed.', 'error');
                        }
                    }
                });
            });

            // ================== AJAX REGISTRATION SUBMISSION ==================
            $('#ajaxRegisterForm').on('submit', function(e) {
                e.preventDefault();
                const btn = $('#registerSubmitBtn');
                btn.prop('disabled', true).text('Registering...');
                $('.invalid-feedback').hide();

                $.ajax({
                    url: '/api/register',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        showToast(res.message, 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    },
                    error: function(err) {
                        btn.prop('disabled', false).text('Register Now');
                        if (err.status === 422) {
                            const res = err.responseJSON;
                            showToast('Please correct validation flaws', 'error');
                            if (res.errors) {
                                Object.keys(res.errors).forEach(key => {
                                    $(`#reg${key.charAt(0).toUpperCase() + key.slice(1)}Error`).text(res.errors[key][0]).show();
                                });
                            }
                        } else {
                            showToast('Server connection failed.', 'error');
                        }
                    }
                });
            });

            // ================== AJAX FORGOT PASSWORD OTP TRIGGER ==================
            $('#ajaxForgotForm').on('submit', function(e) {
                e.preventDefault();
                const btn = $('#forgotSubmitBtn');
                btn.prop('disabled', true).text('Requesting code...');
                $('.invalid-feedback').hide();

                $.ajax({
                    url: '/api/forgot-password',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        btn.prop('disabled', false).text('Send Verification Code');
                        showToast(res.message, 'success');
                        
                        // Proceed to Reset Step Form
                        $('#resetEmailHidden').val($('#forgotEmail').val());
                        $('#ajaxForgotForm').hide();
                        $('#ajaxResetForm').fadeIn();
                        
                        // Seed mock OTP in placeholder to make manual testing super friendly!
                        $('#resetOtp').val(res.otp_code || '123456');
                    },
                    error: function(err) {
                        btn.prop('disabled', false).text('Send Verification Code');
                        if (err.status === 422 || err.status === 404) {
                            const res = err.responseJSON;
                            showToast(res.message || 'OTP dispatch failed.', 'error');
                            if (res.errors && res.errors.email) {
                                $('#forgotEmailError').text(res.errors.email[0]).show();
                            }
                        } else {
                            showToast('Server connection failed.', 'error');
                        }
                    }
                });
            });

            // ================== AJAX RESET PASSWORD CONFIRM SUBMIT ==================
            $('#ajaxResetForm').on('submit', function(e) {
                e.preventDefault();
                const btn = $('#resetSubmitBtn');
                btn.prop('disabled', true).text('Overriding password...');
                $('.invalid-feedback').hide();

                $.ajax({
                    url: '/api/reset-password',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        showToast(res.message, 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    },
                    error: function(err) {
                        btn.prop('disabled', false).text('Synchronize Password');
                        const res = err.responseJSON;
                        showToast(res.message || 'Password override failed.', 'error');
                        if (res.errors) {
                            Object.keys(res.errors).forEach(key => {
                                $(`#reset${key.charAt(0).toUpperCase() + key.slice(1)}Error`).text(res.errors[key][0]).show();
                            });
                        }
                    }
                });
            });

            // ================== AJAX LOGOUT SUBMISSION ==================
            $('#logoutBtn').on('click', function(e) {
                e.preventDefault();
                $.ajax({
                    url: '/api/logout',
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        showToast(res.message, 'success');
                        setTimeout(() => {
                            window.location.href = '/';
                        }, 800);
                    },
                    error: function() {
                        window.location.reload();
                    }
                });
            });

            // ================== DYNAMIC SIDEBAR DETAILS MODAL ==================
            const sModal = $('#sidebarDetailsModal');
            
            // Map sidebar clicks to show immersive instructions popup
            $(document).on('click', '.tab-item a', function(e) {
                e.preventDefault();
                const text = $(this).text();
                const cleanText = text.replace('→ ', '').replace('✓ ', '').replace('• ', '');
                
                $('#sidebarDetailTitle').text(cleanText);
                
                // Synthesize comprehensive mock description depending on clicks
                let detailsText = '';
                if (cleanText.includes('Admit Card') || cleanText.includes('Entry Card') || cleanText.includes('Hall Ticket')) {
                    detailsText = `
                        <strong>Recruitment Body:</strong> Government Selection Board<br>
                        <strong>Release Status:</strong> LIVE & Ready to Download<br>
                        <strong>Instructions:</strong> candidates can access their call letters by logging into their application reference dashboard. Please carry a printed copy of the Admit Card along with an active government-issued Photo ID (Aadhaar, Passport, PAN Card) to the allocated testing venue.<br><br>
                        <strong>Exam Date:</strong> Scheduled for next month.<br>
                        <strong>Reporting Time:</strong> 08:30 AM (Strict closing gate hours apply).
                    `;
                } else if (cleanText.includes('Result') || cleanText.includes('Selection List') || cleanText.includes('Merit List')) {
                    detailsText = `
                        <strong>Examination Segment:</strong> Final Merit & Cutoff Index Lists<br>
                        <strong>Review Status:</strong> Official Verification Complete<br>
                        <strong>Cutoff Parameters:</strong> General 78.5%, OBC 72.4%, SC/ST 65.0%<br><br>
                        Congratulations to all qualifying candidates! The selection board will dispatch individual call letters for physical verification and biometric checks via registered email profiles shortly.
                    `;
                } else {
                    detailsText = `
                        <strong>Subject Stream:</strong> Combined Competitive Exam Syllabus Patterns<br>
                        <strong>Topic Outlines:</strong><br>
                        &bull; <strong>Paper I (Aptitude & Math):</strong> Quantitative Reasoning, Algebra, Numerical Analysis, Data Interpretation.<br>
                        &bull; <strong>Paper II (General Studies):</strong> Current Affairs, Constitutional Law, Public Policies, Indian History & Geography.<br><br>
                        <strong>Marking Scheme:</strong> Objective type MCQ format (negative marking 0.25 index points for every wrong answer choice).
                    `;
                }
                
                $('#sidebarDetailBody').html(detailsText);
                sModal.addClass('active');
            });

            $('#closeSidebarDetailsModal, #sidebarDetailsModal').on('click', function(e) {
                if (e.target === this || e.target.id === 'closeSidebarDetailsModal') {
                    sModal.removeClass('active');
                }
            });

            // ================== HIGH PERFORMANCE TELEMETRY TRACKER ==================
            // 1. Auto Page View Track
            if (!window.location.pathname.startsWith('/admin') && !window.location.pathname.startsWith('/api')) {
                $.ajax({
                    url: '/api/analytics/page-view',
                    method: 'POST',
                    data: {
                        path: window.location.pathname,
                        referer: document.referrer,
                        _token: '{{ csrf_token() }}'
                    }
                });
            }

            // 2. Track Ad Slot Impressions
            $('.ad-banner-placeholder').each(function() {
                const slotName = $(this).attr('id') || 'home_sponsored_banner';
                $.ajax({
                    url: '/api/analytics/ad-event',
                    method: 'POST',
                    data: {
                        event_type: 'ad_impression',
                        slot_name: slotName,
                        _token: '{{ csrf_token() }}'
                    }
                });
            });

            // 3. Track Ad Slot Clicks
            $(document).on('click', '.ad-banner-placeholder', function() {
                const slotName = $(this).attr('id') || 'home_sponsored_banner';
                $.ajax({
                    url: '/api/analytics/ad-event',
                    method: 'POST',
                    data: {
                        event_type: 'ad_click',
                        slot_name: slotName,
                        _token: '{{ csrf_token() }}'
                    }
                });
            });

            // 4. Track External Apply link CTR Click
            $(document).on('click', '#detailOfficialLink', function() {
                const jobId = $('#applicationFormJobId').val();
                if (jobId) {
                    $.ajax({
                        url: '/api/analytics/job-event',
                        method: 'POST',
                        data: {
                            job_post_id: jobId,
                            event_type: 'apply_click',
                            _token: '{{ csrf_token() }}'
                        }
                    });
                }
            });
        });
    </script>
    
    @yield('scripts')
</body>
</html>
