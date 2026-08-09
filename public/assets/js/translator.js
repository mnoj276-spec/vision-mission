/**
 * GovJobs Multilingual Translation System Controller
 * Client-Side Structured Internationalization (i18n) Framework
 */
(function() {
    // 1. Core State & Variable Initialization
    const LANG_STORAGE_KEY = 'preferred_language';
    const POPUP_CHOICE_KEY = 'preferred_language_choice';

    // Detect bots and crawlers to protect SEO, sitemaps, and Lighthouse scores
    const userAgent = navigator.userAgent.toLowerCase();
    const isBotOrLighthouse = /bot|googlebot|bingbot|yandex|baidu|crawler|spider|robot|crawling|lighthouse|chrome-lighthouse/i.test(userAgent);

    // 2. Full Static Translations Dictionary
    window.translations = {
        en: {
            "logo_html": "Gov<span>Jobs</span>",
            "nav_home": "Home",
            "nav_ssc": "SSC Board",
            "nav_railway": "Railways",
            "nav_upsc": "UPSC",
            "nav_state": "State Jobs",
            "nav_info": "Info Hub",
            "nav_jobs_list": "Jobs List",
            "nav_utilities": "Exam Utilities",
            "theme_night": "Night Mode",
            "theme_day": "Day Mode",
            "btn_login_register": "Login / Register",
            "dropdown_dashboard": "Dashboard",
            "dropdown_admin": "Admin Panel",
            "dropdown_logout": "Logout",
            "latest_updates": "Latest Updates",
            "new_badge": "NEW",
            "hero_title": "Find Your Dream <span style=\"color: var(--accent-color);\">Government Job</span> Today",
            "hero_desc": "Discover real-time, highly validated recruitment alerts across UPSC, SSC, Banking, Railways, and individual states. Updated automatically, systematically verified, 100% accurate.",
            "trend_latest_jobs": "Latest Jobs",
            "trend_admit_cards": "Admit Cards",
            "trend_results": "Exam Results",
            "trend_answer_keys": "Answer Keys",
            "trend_syllabus": "Syllabus",
            "trend_notices": "Notices",
            "trend_admissions": "Admissions",
            "trend_scholarships": "Scholarships",
            "search_placeholder": "Search government postings (e.g. UPSC, RBI Grade B, Banking)...",
            "filter_title": "Filter Listings",
            "filter_state": "📍 State / Region",
            "all_regions": "All Regions",
            "filter_stream": "💼 Stream / Sector",
            "all_streams": "All Streams",
            "filter_degree": "🎓 Candidate Degree",
            "all_degrees": "All Degrees",
            "filter_board": "🏢 Recruitment Board",
            "all_boards": "All Boards",
            "filter_salary": "💸 Minimum Salary",
            "any_salary_scale": "Any Salary Scale",
            "filter_free_app": "Free Applications Only (₹0 fee)",
            "btn_reset": "Reset Parameters",
            "sort_featured": "Sort: Featured First • Fresh",
            "btn_view_details": "View Details",
            "badge_featured": "FEATURED",
            "lbl_deadline": "Deadline",
            "lbl_vacancies": "Vacancies",
            "lbl_salary": "Salary",
            "govt_scale": "Govt Scale",
            "modal_overview": "Recruitment Overview & Eligibility",
            "modal_syllabus": "Official Syllabus & Exam Pattern",
            "modal_selection": "Selection Process",
            "modal_advertisement": "Official Advertisement",
            "modal_apply_now": "Apply Recruitment Now",
            "modal_login_apply": "Login to Apply Now",
            "modal_vacancies_lbl": "Total Vacancies",
            "modal_salary_lbl": "Salary Range (Monthly)",
            "modal_age_lbl": "Age Requirements",
            "modal_fees_lbl": "Application Fees",
            "modal_deadline_lbl": "Application Deadline",
            "modal_exam_lbl": "Expected Exam Date",
            "footer_desc": "An advanced, fully automated Government Recruitment Job Portal featuring low-temperature validation engines and zero full page refreshes.",
            "footer_hubs": "Portal Hubs",
            "footer_rec_board": "Recruitments Board",
            "footer_info_hub": "Information Hub",
            "footer_faq": "FAQ Accordions",
            "footer_partners": "Recruitment Partners",
            "reset_btn": "Back to English",

            // Info Hub English Keys
            "info_hub_title": "Portal Information & Help Center",
            "info_blog_tab": "Blog & News",
            "info_timeline_tab": "About Portal Timeline",
            "info_faq_tab": "Frequently Asked Questions",
            "info_contact_tab": "Contact Helpdesk",
            "blog_tag_rec": "Recruitment News",
            "blog_t1": "UPSC Civil Services 2026 Notification Out!",
            "blog_d1": "The Union Public Service Commission has officially announced the vacancies count and cutoff criteria for the IAS/IFS preliminary examinations.",
            "blog_rel_today": "Released: Today",
            "blog_read_more": "Read More &rarr;",
            "blog_tag_admit": "Admit Card Updates",
            "blog_t2": "SSC CGL Tier 1 Hall Ticket Release Dates",
            "blog_d2": "Candidates who submitted application forms can download active entry cards starting this Friday by entering their unique birth records.",
            "blog_rel_yesterday": "Released: Yesterday",
            "blog_tag_syllabus": "Syllabus Releases",
            "blog_t3": "Railway Recruitment Board Syllabus Overhaul",
            "blog_d3": "The selection committee revised general aptitude and science parameters for technical examinations. Read complete subject breakdowns here.",
            "blog_rel_2days": "Released: 2 days ago",
            "timeline_title": "Portal Design & Low-Temperature Scraping Pipeline",
            "timeline_desc": "GovJobs is engineered with clean PHP Laravel MVC + Service-Repository architecture, keeping API requests blazing-fast and highly secure.",
            "timeline_s1_t": "Stage 1: Multi-Feed Target Web Scraper",
            "timeline_s1_d": "Intelligent crawler engines fetch recruitment notifications directly from official portals asynchronously via Background Queues.",
            "timeline_s2_t": "Stage 2: Deterministic Pre-Parser Validation",
            "timeline_s2_d": "Strict regex filters extract qualification codes, vacancies, cutoff ages, application fees, and deadlines. Matches with incomplete fields are quarantined.",
            "timeline_s3_t": "Stage 3: Quarantine Override & Live Publish",
            "timeline_s3_d": "Administrators review isolated postings, make corrections with a single click, and synchronize them live into public job directories instantly!",
            "faq_q1": "Are all listed government job alerts verified?",
            "faq_a1": "Yes! Every announcement in our portal is scraped directly from authentic government domain resources (.gov.in / .nic.in) and cross-validated before listing.",
            "faq_q2": "How does the mock OTP verification system work?",
            "faq_a2": "To recover your candidate account, click the 'Reset PW' tab in the authentication modal, input your email, and receive a simulated SMS code '123456' immediately to restore session rights.",
            "faq_q3": "How can candidates update their alert preferences?",
            "faq_a3": "Candidates can sign in, open the 'Dashboard' section, go to the 'Profile Settings' tab, and toggle Email or SMS notifications checkbox configurations in real-time.",
            "contact_title": "Contact Portal Support Helpdesk",
            "contact_desc": "Have questions or spot a typo on a scraped recruitment feed? Send us a ticket.",
            "contact_name_lbl": "Your Name",
            "contact_msg_lbl": "Support Message / Feedback",
            "contact_submit_btn": "Submit Support Ticket",
            "contact_name_placeholder": "Candidate Name",
            "contact_email_placeholder": "candidate@example.com",
            "contact_msg_placeholder": "Briefly describe your request...",

            // Dashboard English Keys
            "dash_title": "Candidate Interactive Dashboard",
            "dash_overview_tab": "Workspace Overview",
            "dash_settings_tab": "Profile & Match Alerts Preferences",
            "dash_membership_tab": "Premium Membership Plans",
            "dash_saved_postings": "Saved Recruitment Postings",
            "lbl_job_title": "Recruitment Title",
            "lbl_region": "Region",
            "lbl_deadline": "Apply Deadline",
            "lbl_actions": "Actions",
            "dash_submitted_apps": "Submitted Applications & Recruiter Status",
            "lbl_organization": "Organization",
            "lbl_date_submitted": "Date Submitted",
            "lbl_process_state": "Process State",
            "dash_recently_viewed": "Recently Viewed Recruitments",
            "dash_profile_stats": "Profile Statistics",
            "dash_saved_count": "Saved Recruitments",
            "dash_submitted_count": "Submitted Applications",
            "dash_update_profile": "Update Profile Settings & Preferences",
            "lbl_full_name": "Full Name",
            "lbl_email_addr": "Email Address",
            "lbl_phone_num": "Phone Number",
            "dash_pass_blank": "Leave password fields blank if you do not want to alter credentials.",
            "lbl_new_pass": "New Password (Min 6 chars)",
            "lbl_confirm_pass": "Confirm New Password",
            "btn_sync_profile": "Synchronize Profile Settings",
            "dash_alert_channels": "Real-time Recruitment Alert Channels",
            "dash_email_alerts": "Email Match Notifications",
            "dash_email_alerts_desc": "Receive validation notifications daily on active categories.",
            "dash_sms_alerts": "SMS Verification Alerts",
            "dash_sms_alerts_desc": "Send live SMS reminders 24 hours prior to apply deadlines.",
            "btn_save_preferences": "Save Notification Preferences",
            "membership_desc": "Unlock advanced automation alerts, early results access, and a completely ad-free experience.",
            "plan_free": "Basic Free Plan",
            "plan_premium": "Premium Candidate",
            "plan_pro": "Pro Professional",
            "btn_upgrade_premium": "Upgrade Premium",
            "btn_upgrade_pro": "Upgrade Pro",

            // Generic English Keys
            "found_jobs": "Found {count} recruitments",
            "apply_by": "Apply by",
            "vacancies_count": "Vacancies",
            "btn_delete": "Delete",
            "btn_view": "View",
            "active_posts": "Active Posts",
            "btn_save_job": "Save Job",
            "btn_remove_save": "Remove Save",
            "salary_range_monthly": "Salary Range (Monthly)",
            "active_posts_lbl": "Active Posts",
            "explore_state": "Explore by State / Region",
            "explore_qual": "Explore by Qualification",
            "all_quals": "All Qualifications",
            "select_state": "Select Region/State",
            "select_qual": "Select Qualification",
            "select_cat": "Select Category",
            "syllabus_exams": "Syllabus & Exams",
            "important_notices": "Important Notices",
            "admissions_hub": "Admissions Hub",
            "scholarships_grants": "Scholarships & Grants",
            "did_you_mean": "Did you mean:",

            // Empty state messages
            "no_active_recruitments": "No active recruitments listed.",
            "no_admit_cards": "No active admit cards released.",
            "no_results": "No active results declared yet.",
            "no_answer_keys": "No official answer keys released.",
            "no_syllabus": "No new syllabus structures out.",
            "no_notices": "No important circular notices active.",
            "no_admissions": "No active entrance exam admission notices.",
            "no_scholarships": "No active scholarship schemes posted.",

            // Featured & Latest sections
            "premium_featured": "Premium Featured Announcements",
            "latest_active": "Latest Active Recruitments",
            "no_featured": "No featured announcements active at this moment.",
            "no_match_criteria": "No recruitment postings match your exact search criteria. Try modifying your filters.",
            "system_error": "System error occurred! Could not synchronize listings. Please try again.",

            // Sidebar labels
            "automation_monitor": "Automation Monitor",
            "automation_desc": "Our intelligent scraping pipeline parses government portals every 5 minutes, validates parameters deterministically, and isolates errors in quarantine.",
            "status_active": "Active",
            "system_failsafe": "Failsafe",
            "search_keywords_placeholder": "Search keywords (e.g. UPSC, RBI officer)...",
            "badge_sponsored": "SPONSORED",
            "lbl_status": "Status",
            "lbl_system_mode": "System Mode",
            "tab_admit_1": "UPSC Civil Services (IAS) 2026 Admit Card",
            "tab_admit_2": "SSC CGL Tier 1 Entry Card",
            "tab_admit_3": "RBI Officer Grade B Exam Schedule",
            "tab_admit_4": "SBI Probationary Officer Exam Hall Ticket",
            "tab_result_1": "UPSC IFS Final Selection List 2025",
            "tab_result_2": "Railway NTPC CBT 2 Merit List",
            "tab_result_3": "IBPS Specialist Officer Mains Result",
            "tab_syllabus_1": "UPSC IAS Complete Pattern (Prelims & Mains)",
            "tab_syllabus_2": "SSC CGL Tier 1 & Tier 2 Math Syllabus",
            "tab_syllabus_3": "RBI Grade B Phase 1 Syllabus Pattern",
            "dash_loading_bookmarks": "Loading Saved bookmarks...",
            "dash_loading_applications": "Loading Submitted applications...",
            "dash_loading_recently_viewed": "Loading recently viewed...",
            "dash_no_bookmarks": "No recruitment alerts bookmarked.",
            "dash_no_applications": "No job applications submitted.",
            "dash_no_recently_viewed": "No recently viewed recruitments.",
            "btn_updating_profile": "Updating Profile...",
            "status_rejected": "Rejected",
            "btn_prev": "Prev",
            "btn_next": "Next",
            "search_failed_timeout": "Search failed! Connection to database indexing timed out. Please retry.",

            // New Modal & Sidebar & Auth English Keys
            "modal_salary_lbl_index": "Monthly Salary Index",
            "modal_official_call_letter": "Official Call Letter",
            "modal_admit_card_status": "Admit Card Status",
            "modal_released_active": "⚡ RELEASED & ACTIVE",
            "modal_download_deadline": "Download Deadline",
            "modal_download_instructions": "Download Call Letter Instructions",
            "modal_board_released_admit": "The selection board has released the admit cards for",
            "modal_download_prior": "Please download your entry card prior to the download deadline.",
            "modal_credentials_checklist": "Required Credentials Checklist:",
            "modal_chk_1": "🔑 1. Registered Application Number / Registration ID",
            "modal_chk_2": "🎂 2. Candidate Date of Birth (DD-MM-YYYY format)",
            "modal_chk_3": "🧩 3. Security Verification Code Captcha",
            "modal_note": "Note:",
            "modal_admit_card_note_text": "Carry a printed color copy of this Admit Card along with an active government photo ID proof (Aadhaar Card, Passport, driving license, PAN card) and two passport-sized color photos to the test venue.",
            "modal_direct_access": "Direct Candidate Server Access",
            "modal_select_server": "Select Server 1 or 2 to download call letters instantly.",
            "modal_download_s1": "🚀 Download Call Letter (Server 1)",
            "modal_alt_login_s2": "🌐 Alternative Login (Server 2)",
            "modal_merit_cutoff": "Merit & Cutoff Scores",
            "modal_result_status": "Result Status",
            "modal_merit_released": "🎉 MERIT LIST RELEASED",
            "modal_cutoff_verification": "Cutoff Verification",
            "modal_completed": "COMPLETED",
            "modal_total_selected": "Total Selected Candidates",
            "modal_allotments": "Allotments",
            "modal_allotment_date": "Allotment Date",
            "modal_cutoff_marks": "Category-Wise Cutoff Marks",
            "modal_category_segment": "Category Segment",
            "modal_cutoff_percent": "Cutoff Marks (%)",
            "modal_status_index": "Status Index",
            "modal_cat_gen": "General (UR)",
            "modal_status_active_cleared": "Active / Cleared",
            "modal_next_steps": "Next Steps & Counselling Process",
            "modal_merit_note_text": "All qualifying candidates whose roll numbers are highlighted in the merit list must prepare documents for the biometric validation and certificate screening. Individual counseling invitations will be sent via registered candidate emails soon.",
            "modal_direct_merit_dl": "Direct Merit PDF Downloads",
            "modal_dl_cutoff_text": "Download final selection indexes or cutoff list directly from secure servers.",
            "modal_download_merit_pdf": "📄 Download Merit List (PDF)",
            "modal_download_cutoff": "📊 Download Official Cutoff",
            "modal_syllabus_topics": "Topics & Marking Pattern",
            "modal_syllabus_status": "Syllabus Status",
            "modal_official_overhaul": "⭐ OFFICIAL OVERHAUL",
            "modal_total_marks": "Total Exam Marks",
            "modal_negative_marking": "Negative Marking",
            "modal_duration_allowance": "Duration Allowance",
            "modal_exam_scheme": "Exam Scheme & Section Breakdown",
            "modal_important_subjects": "Important Subjects & Key Syllabus Focus",
            "modal_download_resources": "Download Official Study Resources",
            "modal_grab_syllabus_text": "Grab verified syllabus copy and previous year mock papers instantly.",
            "modal_download_syllabus_pdf": "📚 Download Detailed Syllabus (PDF)",
            "modal_mock_papers": "Mock Question Papers",
            "modal_answer_key_objection_window": "Official Key & Objection Window",
            "modal_answer_key_state": "Answer Key State",
            "modal_answer_key_active_objection_open": "📝 ACTIVE / OBJECTION OPEN",
            "modal_release_date": "Release Date",
            "modal_objection_fee": "Objection Filing Fee",
            "modal_closing_date": "Closing Date",
            "modal_objection_milestones": "Important Objection Filing Milestones",
            "modal_milestone_1": "1. Release of Provisional Key",
            "modal_milestone_1_desc": "Candidates can access their individual exam response sheets along with official answer options.",
            "modal_milestone_2": "2. Objection Submission Gate (OPEN)",
            "modal_milestone_2_desc": "If any answer candidate selected differs from the key, they can upload substantial text book proof.",
            "modal_milestone_3": "3. Announcement of Final Key",
            "modal_milestone_3_desc": "The advisory committee will evaluate objections and launch the overridden final answer key copy.",
            "modal_download_keys_objection": "Download Keys & File Objections",
            "modal_check_scores_text": "Check your scores against the keys or raise concerns directly.",
            "modal_download_prov_key": "🔑 Download Provisional Key (PDF)",
            "modal_raise_objections": "Raise Key Objections Now",
            "modal_entrance_counselling_board": "Entrance & Counselling Board",
            "modal_program_stream": "Program Stream",
            "modal_academic_technical": "Academic & Technical",
            "modal_entrance_exam_fee": "Entrance Exam Fee",
            "modal_seat_intake_cap": "Seat Intake Cap",
            "modal_open_seats": "Open Seats",
            "modal_counselling_deadline": "Counseling Deadline",
            "modal_course_scope": "Course Scope & Eligibility Guidelines",
            "modal_semester_fee_allocation": "Semester Fee & Academic Allocation",
            "modal_regular_stream_fee": "Regular Stream Fee",
            "modal_selection_entry_criteria": "Selection / Entry Criteria",
            "modal_entrance_score_merit": "Entrance Score Merit",
            "modal_official_admissions_portal": "🌐 Official Admissions Portal",
            "modal_submit_admissions_form": "Submit Admissions Form",
            "modal_scholarship_grant_sub": "Merit & Means Financial Grant",
            "modal_financial_grant_scope": "Financial Grant Scope",
            "modal_income_eligibility_cap": "Income Eligibility Cap",
            "modal_allotment_seats_limit": "Allotment Seats Limit",
            "modal_beneficiaries": "Beneficiaries",
            "modal_submission_deadline": "Submission Deadline",
            "modal_scholarship_objective": "Scholarship Objective & Grant Criteria",
            "modal_mandatory_documents": "Mandatory Required Documents Checklist",
            "modal_doc_chk_1": "1. Valid Income Certificate verified by local Revenue Inspector (Tahsildar)",
            "modal_doc_chk_2": "2. Candidate Caste & Domicile certificate files",
            "modal_doc_chk_3": "3. Previous Academic year Marks memo Card / Qualifying certificates",
            "modal_doc_chk_4": "4. Candidate Bank Passbook linking Aadhaar profile for direct DBTs",
            "modal_official_scheme_guidelines": "Official Scheme Guidelines",
            "modal_apply_scholarship_now": "Apply Scholarship Now",
            "modal_official_important_alert": "Official Important Alert",
            "modal_critical_calendar_notice": "Critical Calendar Notice:",
            "modal_critical_calendar_notice_text": "The examination date has been scheduled/updated. Please review the official notice specifications below and align your schedules.",
            "modal_announced_exam_date": "Announced Exam Date",
            "modal_notice_published_date": "Notice Published Date",
            "modal_important_circular_specs": "Important Circular Specifications",
            "modal_download_official_circular": "Download Official Circular",
            "modal_download_notice_pdf_desc": "Download the full, official notice PDF released by the department.",
            "modal_download_notice_pdf": "📄 Download Official Notice (PDF)",
            "auth_tab_signin": "Sign In",
            "auth_tab_register": "Register",
            "auth_tab_reset": "Reset PW",
            "lbl_password": "Password",
            "auth_forgot_pw": "Forgot password?",
            "auth_recover_acct": "Recover account",
            "auth_already_reg": "Already registered?",
            "auth_signin_instead": "Sign In instead",
            "auth_btn_register": "Register Now",
            "lbl_reg_email_addr": "Registered Email Address",
            "auth_btn_send_otp": "Send Verification Code",
            "lbl_enter_otp": "Enter OTP Code (Sent: 123456)",
            "auth_btn_sync_pass": "Synchronize Password",
            "modal_sidebar_rec_body": "Recruitment Body",
            "modal_sidebar_gov_board": "Government Selection Board",
            "modal_sidebar_rel_status": "Release Status",
            "modal_sidebar_status_live": "LIVE & Ready to Download",
            "modal_sidebar_instructions_lbl": "Instructions",
            "modal_sidebar_admit_instr_val": "candidates can access their call letters by logging into their application reference dashboard. Please carry a printed copy of the Admit Card along with an active government-issued Photo ID (Aadhaar, Passport, PAN Card) to the allocated testing venue.",
            "modal_sidebar_scheduled_next_month": "Scheduled for next month.",
            "modal_sidebar_reporting_time": "Reporting Time",
            "modal_sidebar_reporting_val": "08:30 AM (Strict closing gate hours apply).",
            "modal_sidebar_exam_segment": "Examination Segment",
            "modal_sidebar_merit_cutoff_val": "Final Merit & Cutoff Index Lists",
            "modal_sidebar_review_status": "Review Status",
            "modal_sidebar_verification_complete": "Official Verification Complete",
            "modal_sidebar_cutoff_params": "Cutoff Parameters",
            "modal_sidebar_result_congrats": "Congratulations to all qualifying candidates! The selection board will dispatch individual call letters for physical verification and biometric checks via registered email profiles shortly.",
            "modal_sidebar_subject_stream": "Subject Stream",
            "modal_sidebar_combined_syllabus": "Combined Competitive Exam Syllabus Patterns",
            "modal_sidebar_topic_outlines": "Topic Outlines",
            "modal_sidebar_paper_1": "Paper I (Aptitude & Math)",
            "modal_sidebar_paper_1_val": "Quantitative Reasoning, Algebra, Numerical Analysis, Data Interpretation.",
            "modal_sidebar_paper_2": "Paper II (General Studies)",
            "modal_sidebar_paper_2_val": "Current Affairs, Constitutional Law, Public Policies, Indian History & Geography.",
            "modal_sidebar_marking_scheme": "Marking Scheme",
            "modal_sidebar_marking_val": "Objective type MCQ format (negative marking 0.25 index points for every wrong answer choice).",
            "important_dates": "Important Dates",
            "app_begin": "Application Begin:",
            "last_date_apply": "Last Date to Apply:",
            "fee_last_date": "Online Fee Last Date:",
            "exam_date_lbl": "Exam Date:",
            "result_date_lbl": "Expected Result Date:",
            "app_fee_lbl": "Application Fee",
            "fee_gen": "General / OBC / EWS:",
            "fee_sc": "SC / ST / PH:",
            "fee_female": "Females (All Category):",
            "fee_all": "All Category Candidates:",
            "pay_mode_lbl": "Payment Mode:",
            "pay_mode_desc": "Pay the examination fee through Debit Card, Credit Card, Net Banking, or UPI mode only.",
            "age_limit_title": "Age Limit Details",
            "age_min_lbl": "Minimum Age",
            "age_max_lbl": "Maximum Age",
            "age_ref_lbl": "Age Limit Reference:",
            "age_relaxation_desc": "Calculated based on the board's recruitment guidelines. Age relaxation is applicable extra as per government reservation rules.",
            "lifecycle_title": "Recruitment Lifecycle & Update Timeline",
            "ai_summary_lbl": "AI Summary",
            "vacancy_breakdown_title": "Vacancy Distribution Breakdown",
            "overview_title": "Recruitment Overview & Requirements",
            "eligibility_title": "Detailed Eligibility Criteria",
            "original_overview_title": "Original Announcement Overview",
            "syllabus_title": "Official Syllabus & Exam Pattern",
            "selection_details_title": "Selection Process Details",
            "selection_process_lbl": "Selection Process",
            "faqs_title": "Frequently Asked Questions (FAQs)",
            "instructions_title": "How to Fill the Form - Step-by-Step Instructions",
            "instr_1": "OTR Registration: Most recruitment boards (like UPSC, SSC, and State PSCs) mandate completing a One-Time Registration (OTR) profile on their official website before submitting any exam application forms.",
            "instr_2": "Webcam Live Photograph: SSC and other major commissions require taking a live photo of yourself via webcam or through the official mobile app. Stand in front of a light/white background and look straight. Do not wear caps, spectacles, or masks.",
            "instr_3": "Check Credentials & Preview: Before clicking on final submit, review the form preview thoroughly. Confirm that spelling, dates, caste category, and certificates are accurate.",
            "instr_4": "Fee Payment: Candidates requiring a fee must complete the payment (using UPI, Credit/Debit cards, or Net banking). Unpaid applications are treated as incomplete and rejected automatically.",
            "instr_5": "Print Confirmation: Save and print a hard copy of the final submitted application form for future reference and exam attendance confirmation.",
            "useful_links_title": "Some Useful Important Links",
            "seo_disclaimer_title": "Disclaimer & Important Notice",
            "seo_disclaimer_text": "Although every effort has been made to ensure the accuracy and completeness of the information on this website, GovJobs is not responsible for any inadvertent errors in notification details or outcomes. Candidates are strongly advised to download the official notification PDF and verify all specifications, timelines, and requirements directly on the official board website before submitting an application.",
            "resource_name_th": "Resource / Action Name",
            "action_link_th": "Action Link",
            "apply_online_row": "Apply Online (Registration & Login)",
            "apply_online_btn": "Apply Online",
            "download_pdf_row": "Download Official Notification PDF",
            "download_pdf_btn": "Download PDF",
            "download_syllabus_row": "Download Syllabus & Exam Pattern",
            "view_syllabus_btn": "View Syllabus",
            "official_website_row": "Official Recruitment Website",
            "visit_website_btn": "Visit Website",
            "join_telegram_row": "Join Telegram Alerts Channel",
            "telegram_channel_btn": "Telegram Channel",
            "join_whatsapp_row": "Join WhatsApp Updates Channel",
            "whatsapp_group_btn": "WhatsApp Group",
            "submit_app_title": "Submit Job Application",
            "submit_app_desc": "Upload your resume to complete your application for this government posting instantly.",
            "select_cv_lbl": "Select CV / Resume (PDF, DOC, DOCX up to 2MB)",
            "submit_app_btn": "Submit Application Now",
            "apply_announcement_title": "Apply for this Announcement",
            "apply_announcement_desc": "Please register or login to your candidate dashboard to apply directly using your resume profile.",
            "login_register_apply_btn": "Login / Register to Apply",
            "related_updates_title": "Related Updates for This Exam",
            "similar_jobs_title": "Similar Government Jobs",
            "exam_updates_title": "Exam Updates & Notifications",
            "explore_categories_title": "Explore Job Categories",
            "jobs_by_state_title": "Jobs by State",
            "view_details_btn": "View Details →",
            "explore_hubs_title": "Explore More Government Job Hubs",
            "explore_hubs_subtitle": "Navigate our search networks for alternative recruitments, exam schedules, and circular announcements.",
            "state_wise_jobs_title": "State-Wise Jobs",
            "view_all_states_btn": "View All States »",
            "district_jobs_title": "District Jobs",
            "trending_sectors_title": "Trending Sectors",
            "utilities_hub_title": "Utilities Hub",
            "trending_categories_title": "Trending Categories",
            "jobs_suffix": "Jobs",
            "apply_now_btn": "Apply Now »",
            "no_active_recruitments_alerts": "No recruitments are currently active. Use the alerts form to register for instant releases!",
            "telegram_cta_title": "📢 Joint Telegram Alert Network",
            "telegram_cta_desc": "Get real-time push feeds of government postings. Join 150K+ candidates now!",
            "telegram_cta_btn": "Join Channel »",
            "email_alerts_title": "📧 Email Job Alerts",
            "email_alerts_desc_sidebar": "Receive instant notifications directly to your email whenever new vacancies are published in this segment.",
            "email_placeholder_lbl": "candidate@example.com",
            "btn_activate": "Activate",
            "push_alerts_title": "🔔 Desktop Push Alerts",
            "push_alerts_desc_sidebar": "Authorize real-time push notifications inside your browser window to bypass email delay filters.",
            "btn_enable_push": "Enable Instant Alerts",
            "conversion_telemetry_title": "Real-Time Conversion Telemetry",
            "daily_page_visitors": "Daily Page Visitors",
            "lead_subscribers": "Lead Subscribers",
            "job_applicants": "Job Applicants",
            "conversion_efficiency": "Conversion Efficiency",
            "live_opportunities_title": "Live Active Opportunities",
            "eligibility_hero_title": "Sarkari Job Eligibility Checker 2026",
            "eligibility_qualification_label": "🎓 Highest Qualification",
            "eligibility_select_qualification": "-- Select Qualification --",
            "eligibility_state_label": "📍 Preferred State / Region",
            "eligibility_all_india": "-- All India (Central) --",
            "eligibility_category_label": "💼 Recruitment Category",
            "eligibility_all_sectors": "-- All Sectors --",
            "eligibility_age_label": "🎂 Candidate Age (Years)",
            "eligibility_reset_btn": "Reset Filters",
            "eligibility_check_btn": "Check Eligibility »",
            "eligibility_matched_title": "Matched Recruitments",
            "eligibility_no_check_title": "No Check Performed Yet",
            "eligibility_no_check_desc": "Input your parameters above and click \"Check Eligibility\" to instantly discover matching active recruitments.",
            "salary_hero_title": "Sarkari Job Salary Matrix 2026",
            "salary_tab_stream": "💼 Salaries by Stream / Sector",
            "salary_tab_dept": "🏛️ Salaries by Department",
            "salary_tab_state": "📍 Salaries by State / Region",
            "salary_sector_heading": "Pay Ranges Across Recruitment Sectors",
            "salary_dept_heading": "Highest Paying Government Departments (Top 15)",
            "salary_state_heading": "Pay Ranges Grouped by State / Region",
            "salary_th_sector": "Recruitment Sector",
            "salary_th_department": "Department / Organization",
            "salary_th_state": "State / Territory",
            "salary_th_avg": "Average Monthly Salary",
            "salary_th_range": "Salary Range (Min - Max)",
            "salary_th_active": "Active Posts",
            "salary_th_actions": "Actions",
            "salary_explore_btn": "Explore »",
            "salary_no_data": "No salary aggregation metrics are currently available."
        },
        hi: {
            "logo_html": "शासन<span>नौकरियाँ</span>",
            "nav_home": "होम",
            "nav_ssc": "एसएससी बोर्ड",
            "nav_railway": "रेलवे",
            "nav_upsc": "यूपीएससी",
            "nav_state": "राज्य की नौकरियां",
            "nav_info": "सूचना केंद्र",
            "nav_jobs_list": "नौकरियों की सूची",
            "nav_utilities": "परीक्षा उपयोगिता",
            "theme_night": "रात मोड",
            "theme_day": "दिन मोड",
            "btn_login_register": "लॉगिन / पंजीकरण",
            "dropdown_dashboard": "डैशबोर्ड",
            "dropdown_admin": "एडमिन पैनल",
            "dropdown_logout": "लॉगआऊट",
            "latest_updates": "नवीनतम अपडेट",
            "new_badge": "नया",
            "hero_title": "आज ही अपनी मनपसंद <span style=\"color: var(--accent-color);\">सरकारी नौकरी</span> खोजें",
            "hero_desc": "यूपीएससी, एसएससी, बैंकिंग, रेलवे और विभिन्न राज्यों में भर्ती संबंधी वास्तविक समय की, उच्च गुणवत्ता वाली सूचनाएं प्राप्त करें। स्वचालित रूप से अपडेट की गई, व्यवस्थित रूप से सत्यापित, 100% सटीक।",
            "trend_latest_jobs": "नवीनतम नौकरियां",
            "trend_admit_cards": "प्रवेश पत्र",
            "trend_results": "परीक्षा परिणाम",
            "trend_answer_keys": "उत्तर कुंजी",
            "trend_syllabus": "पाठ्यक्रम",
            "trend_notices": "नोटिस",
            "trend_admissions": "दाखिले",
            "trend_scholarships": "छात्रवृत्ति",
            "search_placeholder": "सरकारी नौकरियों की खोज करें (जैसे: UPSC, RBI ग्रेड बी, बैंकिंग)...",
            "filter_title": "फिल्टर सूची",
            "filter_state": "📍 राज्य / क्षेत्र",
            "all_regions": "सभी क्षेत्र",
            "filter_stream": "💼 क्षेत्र / विभाग",
            "all_streams": "सभी विभाग",
            "filter_degree": "🎓 उम्मीदवार की डिग्री",
            "all_degrees": "सभी डिग्रियां",
            "filter_board": "🏢 भर्ती बोर्ड",
            "all_boards": "सभी बोर्ड",
            "filter_salary": "💸 न्यूनतम वेतन",
            "any_salary_scale": "कोई भी वेतनमान",
            "filter_free_app": "केवल निःशुल्क आवेदन (₹0 शुल्क)",
            "btn_reset": "फिल्टर रीसेट करें",
            "sort_featured": "क्रम: विशेष पहले • नवीन",
            "btn_view_details": "विवरण देखें",
            "badge_featured": "विशेष",
            "lbl_deadline": "अंतिम तिथि",
            "lbl_vacancies": "रिक्तियां",
            "lbl_salary": "वेतन",
            "govt_scale": "सरकारी वेतनमान",
            "modal_overview": "भर्ती अवलोकन और पात्रता",
            "modal_syllabus": "आधिकारिक पाठ्यक्रम और परीक्षा पैटर्न",
            "modal_selection": "चयन प्रक्रिया",
            "modal_advertisement": "आधिकारिक विज्ञापन",
            "modal_apply_now": "अभी आवेदन करें",
            "modal_login_apply": "आवेदन करने के लिए लॉगिन करें",
            "modal_vacancies_lbl": "कुल रिक्तियां",
            "modal_salary_lbl": "वेतन सीमा (मासिक)",
            "modal_age_lbl": "आयु सीमा",
            "modal_fees_lbl": "आवेदन शुल्क",
            "modal_deadline_lbl": "आवेदन की अंतिम तिथि",
            "modal_exam_lbl": "संभावित परीक्षा तिथि",
            "footer_desc": "एक उन्नत, पूरी तरह से स्वचालित सरकारी भर्ती नौकरी पोर्टल जिसमें कम तापमान सत्यापन इंजन और शून्य पूर्ण पृष्ठ रीफ्रेश शामिल हैं।",
            "footer_hubs": "पोर्टल हब",
            "footer_rec_board": "भर्ती बोर्ड",
            "footer_info_hub": "सूचना केंद्र",
            "footer_faq": "एफएक्यू एकॉर्डियन",
            "footer_partners": "भर्ती भागीदार",
            "reset_btn": "अंग्रेज़ी पर वापस जाएँ",

            // Info Hub Hindi Keys
            "info_hub_title": "पोर्टल सूचना और सहायता केंद्र",
            "info_blog_tab": "ब्लॉग और समाचार",
            "info_timeline_tab": "पोर्टल टाइमलाइन के बारे में",
            "info_faq_tab": "अक्सर पूछे जाने वाले प्रश्न",
            "info_contact_tab": "हेल्पडेस्क से संपर्क करें",
            "blog_tag_rec": "भर्ती समाचार",
            "blog_t1": "यूपीएससी सिविल सेवा 2026 अधिसूचना जारी!",
            "blog_d1": "संघ लोक सेवा आयोग ने आधिकारिक तौर पर आईएएस/आईएफएस प्रारंभिक परीक्षाओं के लिए रिक्तियों की संख्या और कटऑफ मानदंडों की घोषणा की है।",
            "blog_rel_today": "जारी: आज",
            "blog_read_more": "और पढ़ें &rarr;",
            "blog_tag_admit": "प्रवेश पत्र अपडेट",
            "blog_t2": "एसएससी सीजीएल टियर 1 हॉल टिकट जारी होने की तारीखें",
            "blog_d2": "जिन उम्मीदवारों ने आवेदन पत्र जमा किए हैं, वे इस शुक्रवार से अपने अद्वितीय जन्म रिकॉर्ड दर्ज करके सक्रिय प्रवेश पत्र डाउनलोड कर सकते हैं।",
            "blog_rel_yesterday": "जारी: कल",
            "blog_tag_syllabus": "पाठ्यक्रम जारी",
            "blog_t3": "रेलवे भर्ती बोर्ड पाठ्यक्रम में बदलाव",
            "blog_d3": "चयन समिति ने तकनीकी परीक्षाओं के लिए सामान्य योग्यता और विज्ञान मापदंडों में संशोधन किया। पूर्ण विषय विवरण यहाँ पढ़ें।",
            "blog_rel_2days": "जारी: 2 दिन पहले",
            "timeline_title": "पोर्टल डिज़ाइन और कम तापमान वाली स्क्रैपिंग पाइपलाइन",
            "timeline_desc": "GovJobs को स्वच्छ PHP Laravel MVC + सेवा-संग्रह (Service-Repository) आर्किटेक्चर के साथ डिज़ाइन किया गया है, जो API अनुरोधों को बहुत तेज़ और सुरक्षित रखता है।",
            "timeline_s1_t": "चरण 1: मल्टी-फीड लक्षित वेब स्क्रैपर",
            "timeline_s1_d": "इंटेलिजेंट क्रॉलर इंजन पृष्ठभूमि कतारों (Background Queues) के माध्यम से अतुल्यकालिक रूप से आधिकारिक पोर्टलों से सीधे भर्ती सूचनाएं लाते हैं।",
            "timeline_s2_t": "चरण 2: नियतात्मक पूर्व-पार्सर सत्यापन",
            "timeline_s2_d": "सख्त रेगेक्स फ़िल्टर योग्यता कोड, रिक्तियां, कटऑफ आयु, आवेदन शुल्क और समय सीमा निकालते हैं। अपूर्ण फ़ील्ड वाले मैचों को अलग (quarantined) किया जाता है।",
            "timeline_s3_t": "चरण 3: संगरोध ओवरराइड और लाइव प्रकाशन",
            "timeline_s3_d": "प्रशासक अलग की गई पोस्टिंग की समीक्षा करते हैं, एक क्लिक से सुधार करते हैं, और उन्हें तुरंत सार्वजनिक नौकरी निर्देशिकाओं में लाइव सिंक करते हैं!",
            "faq_q1": "क्या सभी सूचीबद्ध सरकारी नौकरी अलर्ट सत्यापित हैं?",
            "faq_a1": "हां! हमारे पोर्टल में प्रत्येक घोषणा सीधे प्रामाणिक सरकारी डोमेन संसाधनों (.gov.in / .nic.in) से ली जाती है और सूचीबद्ध करने से पहले क्रॉस-सत्यापित की जाती है।",
            "faq_q2": "मॉक ओटीपी सत्यापन प्रणाली कैसे काम करती है?",
            "faq_a2": "अपने उम्मीदवार खाते को पुनर्प्राप्त करने के लिए, प्रमाणीकरण मोडल में 'Reset PW' टैब पर क्लिक करें, अपना ईमेल दर्ज करें, और सत्र अधिकारों को बहाल करने के लिए तुरंत एक नकली एसएमएस कोड '123456' प्राप्त करें।",
            "faq_q3": "उम्मीदवार अपने अलर्ट प्राथमिकताओं को कैसे अपडेट कर सकते हैं?",
            "faq_a3": "उम्मीदवार साइन इन कर सकते हैं, 'डैशबोर्ड' अनुभाग खोल सकते हैं, 'प्रोफ़ाइल सेटिंग्स' टैब पर जा सकते हैं, और वास्तविक समय में ईमेल या एसएमएस अधिसूचना चेकबॉक्स कॉन्फ़िगरेशन टॉगल कर सकते हैं।",
            "contact_title": "पोर्टल सहायता हेल्पडेस्क से संपर्क करें",
            "contact_desc": "कोई प्रश्न हैं या स्क्रैप की गई भर्ती फ़ीड पर कोई त्रुटि दिखाई देती है? हमें एक टिकट भेजें।",
            "contact_name_lbl": "आपका नाम",
            "contact_msg_lbl": "सहायता संदेश / प्रतिक्रिया",
            "contact_submit_btn": "सहायता टिकट जमा करें",
            "contact_name_placeholder": "उम्मीदवार का नाम",
            "contact_email_placeholder": "candidate@example.com",
            "contact_msg_placeholder": "अपनी प्रतिक्रिया संक्षेप में बताएं...",

            // Dashboard Hindi Keys
            "dash_title": "उम्मीदवार इंटरैक्टिव डैशबोर्ड",
            "dash_overview_tab": "कार्यस्थान अवलोकन",
            "dash_settings_tab": "प्रोफ़ाइल और मैच अलर्ट प्राथमिकताएं",
            "dash_membership_tab": "प्रीमियम सदस्यता योजनाएं",
            "dash_saved_postings": "सहेजे गए भर्ती विज्ञापन",
            "lbl_job_title": "भर्ती का नाम",
            "lbl_region": "क्षेत्र",
            "lbl_deadline": "आवेदन की अंतिम तिथि",
            "lbl_actions": "कार्रवाई",
            "dash_submitted_apps": "जमा किए गए आवेदन और भर्तीकर्ता स्थिति",
            "lbl_organization": "संगठन",
            "lbl_date_submitted": "जमा करने की तिथि",
            "lbl_process_state": "प्रक्रिया की स्थिति",
            "dash_recently_viewed": "हाल ही में देखी गई भर्तियां",
            "dash_profile_stats": "प्रोफ़ाइल सांख्यिकी",
            "dash_saved_count": "सहेजी गई भर्तियां",
            "dash_submitted_count": "जमा किए गए आवेदन",
            "dash_update_profile": "प्रोफ़ाइल सेटिंग्स और प्राथमिकताएं अपडेट करें",
            "lbl_full_name": "पूरा नाम",
            "lbl_email_addr": "ईमेल पता",
            "lbl_phone_num": "फ़ोन नंबर",
            "dash_pass_blank": "यदि आप क्रेडेंशियल बदलना नहीं चाहते हैं तो पासवर्ड फ़ील्ड खाली छोड़ दें।",
            "lbl_new_pass": "नया पासवर्ड (न्यूनतम 6 वर्ण)",
            "lbl_confirm_pass": "नए पासवर्ड की पुष्टि करें",
            "btn_sync_profile": "प्रोफ़ाइल सेटिंग्स सिंक करें",
            "dash_alert_channels": "वास्तविक समय भर्ती अलर्ट चैनल",
            "dash_email_alerts": "ईमेल मैच सूचनाएं",
            "dash_email_alerts_desc": "सक्रिय श्रेणियों पर दैनिक सत्यापन सूचनाएं प्राप्त करें।",
            "dash_sms_alerts": "एसएमएस सत्यापन अलर्ट",
            "dash_sms_alerts_desc": "आवेदन की समय सीमा से 24 घंटे पहले लाइव एसएमएस अनुस्मारक भेजें।",
            "btn_save_preferences": "अधिसूचना प्राथमिकताएं सहेजें",
            "membership_desc": "उन्नत स्वचालन अलर्ट, परिणामों तक शीघ्र पहुंच और पूरी तरह से विज्ञापन-मुक्त अनुभव का लाभ उठाएं।",
            "plan_free": "बुनियादी मुफ्त योजना",
            "plan_premium": "प्रीमियम उम्मीदवार",
            "plan_pro": "प्रो प्रोफेशनल",
            "btn_upgrade_premium": "प्रीमियम में अपग्रेड करें",
            "btn_upgrade_pro": "प्रो में अपग्रेड करें",

            // Generic Hindi Keys
            "found_jobs": "{count} भर्तियाँ मिलीं",
            "apply_by": "आवेदन की अंतिम तिथि",
            "vacancies_count": "रिक्तियां",
            "btn_delete": "हटाएं",
            "btn_view": "देखें",
            "active_posts": "सक्रिय पद",
            "btn_save_job": "नौकरी सहेजें",
            "btn_remove_save": "सहेजा हुआ हटाएं",
            "salary_range_monthly": "वेतन सीमा (मासिक)",
            "active_posts_lbl": "सक्रिय पद",
            "explore_state": "राज्य / क्षेत्र के अनुसार खोजें",
            "explore_qual": "योग्यता के अनुसार खोजें",
            "all_quals": "सभी योग्यताएं",
            "select_state": "राज्य/क्षेत्र चुनें",
            "select_qual": "योग्यता चुनें",
            "select_cat": "श्रेणी चुनें",
            "syllabus_exams": "पाठ्यक्रम और परीक्षा",
            "important_notices": "महत्वपूर्ण सूचनाएं",
            "admissions_hub": "प्रवेश केंद्र",
            "scholarships_grants": "छात्रवृत्ति और अनुदान",
            "did_you_mean": "क्या आपका मतलब था:",

            // Empty state messages
            "no_active_recruitments": "कोई सक्रिय भर्ती सूचीबद्ध नहीं है।",
            "no_admit_cards": "कोई सक्रिय प्रवेश पत्र जारी नहीं हुआ।",
            "no_results": "अभी तक कोई सक्रिय परिणाम घोषित नहीं हुआ।",
            "no_answer_keys": "कोई आधिकारिक उत्तर कुंजी जारी नहीं हुई।",
            "no_syllabus": "कोई नया पाठ्यक्रम जारी नहीं हुआ।",
            "no_notices": "कोई महत्वपूर्ण सर्कुलर सूचना सक्रिय नहीं।",
            "no_admissions": "कोई सक्रिय प्रवेश परीक्षा दाखिला सूचना नहीं।",
            "no_scholarships": "कोई सक्रिय छात्रवृत्ति योजना पोस्ट नहीं।",

            // Featured & Latest sections
            "premium_featured": "प्रीमियम विशेष घोषणाएं",
            "latest_active": "नवीनतम सक्रिय भर्तियां",
            "no_featured": "इस समय कोई विशेष घोषणा सक्रिय नहीं है।",
            "no_match_criteria": "आपके खोज मापदंडों से मेल खाने वाली कोई भर्ती नहीं मिली। अपने फ़िल्टर बदलकर देखें।",
            "system_error": "सिस्टम त्रुटि हुई! सूचियां सिंक नहीं हो सकीं। कृपया पुनः प्रयास करें।",

            // Sidebar labels
            "automation_monitor": "स्वचालन मॉनिटर",
            "automation_desc": "हमारी बुद्धिमान स्क्रैपिंग पाइपलाइन हर 5 मिनट में सरकारी पोर्टलों को पार्स करती है, मापदंडों को नियतात्मक रूप से मान्य करती है, और त्रुटियों को संगरोध में अलग करती है।",
            "status_active": "सक्रिय",
            "system_failsafe": "फेलसेफ",
            "search_keywords_placeholder": "सरकारी भर्ती खोजें (जैसे: UPSC, RBI अधिकारी)...",
            "badge_sponsored": "प्रायोजित",
            "lbl_status": "स्थिति",
            "lbl_system_mode": "सिस्टम मोड",
            "tab_admit_1": "यूपीएससी सिविल सेवा (IAS) 2026 प्रवेश पत्र",
            "tab_admit_2": "एसएससी सीजीएल टियर 1 प्रवेश पत्र",
            "tab_admit_3": "आरबीआई अधिकारी ग्रेड बी परीक्षा अनुसूची",
            "tab_admit_4": "एसबीआई प्रोबेशनरी अधिकारी परीक्षा हॉल टिकट",
            "tab_result_1": "यूपीएससी आईएफएस अंतिम चयन सूची 2025",
            "tab_result_2": "रेलवे एनटीपीसी सीबीटी 2 मेरिट सूची",
            "tab_result_3": "आईबीपीएस विशेषज्ञ अधिकारी मुख्य परीक्षा परिणाम",
            "tab_syllabus_1": "यूपीएससी आईएएस पूर्ण पैटर्न (प्रारंभिक और मुख्य)",
            "tab_syllabus_2": "एसएससी सीजीएल टियर 1 और टियर 2 गणित पाठ्यक्रम",
            "tab_syllabus_3": "आरबीआई ग्रेड बी चरण 1 पाठ्यक्रम पैटर्न",
            "dash_loading_bookmarks": "सहेजे गए बुकमार्क लोड हो रहे हैं...",
            "dash_loading_applications": "जमा किए गए आवेदन लोड हो रहे हैं...",
            "dash_loading_recently_viewed": "हाल ही में देखे गए विज्ञापन लोड हो रहे हैं...",
            "dash_no_bookmarks": "कोई भर्ती अलर्ट बुकमार्क नहीं किया गया है।",
            "dash_no_applications": "कोई नौकरी आवेदन जमा नहीं किया गया है।",
            "dash_no_recently_viewed": "हाल ही में कोई भर्ती विज्ञापन नहीं देखा गया है।",
            "btn_updating_profile": "प्रोफ़ाइल अपडेट की जा रही है...",
            "status_applied": "आवेदन किया",
            "status_reviewing": "समीक्षा की जा रही है",
            "status_shortlisted": "शॉर्टलिस्ट किया गया",
            "status_rejected": "अस्वीकृत",
            "btn_prev": "पिछला",
            "btn_next": "अगला",
            "search_failed_timeout": "खोज विफल रही! डेटाबेस सूचकांक कनेक्शन समय समाप्त हो गया। कृपया पुनः प्रयास करें।",

            // New Modal & Sidebar & Auth Hindi Keys
            "modal_salary_lbl_index": "मासिक वेतन सूचकांक",
            "modal_official_call_letter": "आधिकारिक कॉल लेटर",
            "modal_admit_card_status": "प्रवेश पत्र की स्थिति",
            "modal_released_active": "⚡ जारी और सक्रिय",
            "modal_download_deadline": "डाउनलोड की अंतिम तिथि",
            "modal_download_instructions": "प्रवेश पत्र डाउनलोड करने के निर्देश",
            "modal_board_released_admit": "चयन बोर्ड ने प्रवेश पत्र जारी कर दिए हैं:",
            "modal_download_prior": "कृपया अंतिम तिथि से पहले अपना प्रवेश पत्र डाउनलोड करें।",
            "modal_credentials_checklist": "आवश्यक क्रेडेंशियल चेकलिस्ट:",
            "modal_chk_1": "🔑 1. पंजीकृत आवेदन संख्या / पंजीकरण आईडी",
            "modal_chk_2": "🎂 2. उम्मीदवार की जन्म तिथि (DD-MM-YYYY प्रारूप)",
            "modal_chk_3": "🧩 3. सुरक्षा सत्यापन कोड कैप्चा",
            "modal_note": "नोट:",
            "modal_admit_card_note_text": "परीक्षा केंद्र पर इस प्रवेश पत्र की एक प्रिंटेड रंगीन प्रति के साथ एक सक्रिय सरकारी फोटो पहचान पत्र (आधार कार्ड, पासपोर्ट, ड्राइविंग लाइसेंस, पैन कार्ड) और दो पासपोर्ट आकार के रंगीन फोटो साथ लाएं।",
            "modal_direct_access": "सीधा उम्मीदवार सर्वर एक्सेस",
            "modal_select_server": "कॉल लेटर तुरंत डाउनलोड करने के लिए सर्वर 1 या 2 चुनें।",
            "modal_download_s1": "🚀 कॉल लेटर डाउनलोड करें (सर्वर 1)",
            "modal_alt_login_s2": "🌐 वैकल्पिक लॉगिन (सर्वर 2)",
            "modal_merit_cutoff": "मेरिट और कटऑफ स्कोर",
            "modal_result_status": "परिणाम की स्थिति",
            "modal_merit_released": "🎉 मेरिट सूची जारी",
            "modal_cutoff_verification": "कटऑफ सत्यापन",
            "modal_completed": "पूर्ण",
            "modal_total_selected": "कुल चयनित उम्मीदवार",
            "modal_allotments": "आवंटन",
            "modal_allotment_date": "आवंटन तिथि",
            "modal_cutoff_marks": "श्रेणी-वार कटऑफ अंक",
            "modal_category_segment": "श्रेणी खंड",
            "modal_cutoff_percent": "कटऑफ अंक (%)",
            "modal_status_index": "स्थिति सूचकांक",
            "modal_cat_gen": "सामान्य (UR)",
            "modal_status_active_cleared": "सक्रिय / स्वीकृत",
            "modal_next_steps": "आगे के चरण और काउंसलिंग प्रक्रिया",
            "modal_merit_note_text": "मेरिट सूची में जिन उम्मीदवारों के रोल नंबर दिए गए हैं, उन्हें बायोमेट्रिक सत्यापन और प्रमाणपत्र जांच के लिए दस्तावेज तैयार रखने होंगे। व्यक्तिगत काउंसलिंग आमंत्रण जल्द ही पंजीकृत ईमेल पर भेजे जाएंगे।",
            "modal_direct_merit_dl": "सीधे मेरिट पीडीएफ डाउनलोड",
            "modal_dl_cutoff_text": "सुरक्षित सर्वर से सीधे अंतिम चयन सूची या कटऑफ सूची डाउनलोड करें।",
            "modal_download_merit_pdf": "📄 मेरिट सूची डाउनलोड करें (PDF)",
            "modal_download_cutoff": "📊 आधिकारिक कटऑफ डाउनलोड करें",
            "modal_syllabus_topics": "विषय और अंकन पैटर्न",
            "modal_syllabus_status": "पाठ्यक्रम की स्थिति",
            "modal_official_overhaul": "⭐ आधिकारिक बदलाव",
            "modal_total_marks": "कुल परीक्षा अंक",
            "modal_negative_marking": "नकारात्मक अंकन",
            "modal_duration_allowance": "निर्धारित समय",
            "modal_exam_scheme": "परीक्षा योजना और अनुभाग विवरण",
            "modal_important_subjects": "महत्वपूर्ण विषय और मुख्य पाठ्यक्रम",
            "modal_download_resources": "आधिकारिक अध्ययन संसाधन डाउनलोड करें",
            "modal_grab_syllabus_text": "सत्यापित पाठ्यक्रम प्रति और पिछले वर्ष के मॉक पेपर तुरंत प्राप्त करें।",
            "modal_download_syllabus_pdf": "📚 विस्तृत पाठ्यक्रम डाउनलोड करें (PDF)",
            "modal_mock_papers": "✏️ मॉक प्रश्न पत्र",
            "modal_answer_key_objection_window": "आधिकारिक उत्तर कुंजी और आपत्ति विंडो",
            "modal_answer_key_state": "उत्तर कुंजी की स्थिति",
            "modal_answer_key_active_objection_open": "📝 सक्रिय / आपत्ति विंडो खुली",
            "modal_release_date": "जारी होने की तिथि",
            "modal_objection_fee": "आपत्ति दर्ज करने का शुल्क",
            "modal_closing_date": "बंद होने की तिथि",
            "modal_objection_milestones": "आपत्ति दर्ज करने के महत्वपूर्ण चरण",
            "modal_milestone_1": "1. अनंतिम उत्तर कुंजी जारी करना",
            "modal_milestone_1_desc": "उम्मीदवार आधिकारिक उत्तर विकल्पों के साथ अपनी व्यक्तिगत परीक्षा उत्तर पुस्तिका देख सकते हैं।",
            "modal_milestone_2": "2. आपत्ति सबमिशन गेट (खुला है)",
            "modal_milestone_2_desc": "यदि उम्मीदवार द्वारा चुना गया उत्तर कुंजी से भिन्न है, तो वे पाठ्यपुस्तक का प्रामाणिक प्रमाण अपलोड कर सकते हैं।",
            "modal_milestone_3": "3. अंतिम उत्तर कुंजी की घोषणा",
            "modal_milestone_3_desc": "सलाहकार समिति आपत्तियों का मूल्यांकन करेगी और संशोधित अंतिम उत्तर कुंजी जारी करेगी।",
            "modal_download_keys_objection": "उत्तर कुंजी डाउनलोड करें और आपत्ति दर्ज करें",
            "modal_check_scores_text": "Check your scores against the keys or raise concerns directly.",
            "modal_download_prov_key": "🔑 अनंतिम उत्तर कुंजी डाउनलोड करें (PDF)",
            "modal_raise_objections": "🛡️ आपत्ति दर्ज करें",
            "modal_entrance_counselling_board": "प्रवेश और काउंसलिंग बोर्ड",
            "modal_program_stream": "कार्यक्रम स्ट्रीम",
            "modal_academic_technical": "शैक्षणिक और तकनीकी",
            "modal_entrance_exam_fee": "प्रवेश परीक्षा शुल्क",
            "modal_seat_intake_cap": "कुल सीट क्षमता",
            "modal_open_seats": "सीटें खुली हैं",
            "modal_counselling_deadline": "काउंसलिंग की अंतिम तिथि",
            "modal_course_scope": "पाठ्यक्रम दायरा और पात्रता दिशानिर्देश",
            "modal_semester_fee_allocation": "सेमेस्टर शुल्क और शैक्षणिक आवंटन",
            "modal_regular_stream_fee": "नियमित स्ट्रीम शुल्क",
            "modal_selection_entry_criteria": "चयन / प्रवेश मानदंड",
            "modal_entrance_score_merit": "प्रवेश परीक्षा मेरिट",
            "modal_official_admissions_portal": "🌐 आधिकारिक प्रवेश पोर्टल",
            "modal_submit_admissions_form": "प्रवेश फॉर्म जमा करें",
            "modal_scholarship_grant_sub": "योग्यता और साधन वित्तीय सहायता",
            "modal_financial_grant_scope": "वित्तीय सहायता राशि",
            "modal_income_eligibility_cap": "पारिवारिक आय सीमा",
            "modal_allotment_seats_limit": "कुल लाभार्थी सीमा",
            "modal_beneficiaries": "लाभार्थी",
            "modal_submission_deadline": "जमा करने की अंतिम तिथि",
            "modal_scholarship_objective": "छात्रवृत्ति का उद्देश्य और सहायता मानदंड",
            "modal_mandatory_documents": "अनिवार्य आवश्यक दस्तावेजों की चेकलिस्ट",
            "modal_doc_chk_1": "1. स्थानीय राजस्व निरीक्षक (तहसीलदार) द्वारा सत्यापित वैध आय प्रमाण पत्र",
            "modal_doc_chk_2": "2. उम्मीदवार का जाति और निवास प्रमाण पत्र",
            "modal_doc_chk_3": "3. पिछले शैक्षणिक वर्ष की अंकतालिका / योग्यता प्रमाण पत्र",
            "modal_doc_chk_4": "4. प्रत्यक्ष लाभ हस्तांतरण (DBT) के लिए आधार से लिंक बैंक पासबुक",
            "modal_official_scheme_guidelines": "🌐 आधिकारिक योजना दिशानिर्देश",
            "modal_apply_scholarship_now": "अभी छात्रवृत्ति के लिए आवेदन करें",
            "modal_official_important_alert": "आधिकारिक महत्वपूर्ण चेतावनी",
            "modal_critical_calendar_notice": "महत्वपूर्ण परीक्षा कार्यक्रम नोटिस:",
            "modal_critical_calendar_notice_text": "परीक्षा तिथि निर्धारित/अपडेट कर दी गई है। कृपया नीचे आधिकारिक नोटिस विनिर्देशों की समीक्षा करें और तदनुसार योजना बनाएं।",
            "modal_announced_exam_date": "घोषित परीक्षा तिथि",
            "modal_notice_published_date": "नोटिस प्रकाशन तिथि",
            "modal_important_circular_specs": "महत्वपूर्ण परिपत्र विनिर्देश",
            "modal_download_official_circular": "आधिकारिक परिपत्र डाउनलोड करें",
            "modal_download_notice_pdf_desc": "विभाग द्वारा जारी किया गया आधिकारिक नोटिस पीडीएफ डाउनलोड करें।",
            "modal_download_notice_pdf": "📄 आधिकारिक नोटिस डाउनलोड करें (PDF)",
            "auth_tab_signin": "साइन इन",
            "auth_tab_register": "पंजीकरण",
            "auth_tab_reset": "पासवर्ड रीसेट",
            "lbl_password": "पासवर्ड",
            "auth_forgot_pw": "पासवर्ड भूल गए?",
            "auth_recover_acct": "खाता पुनर्प्राप्त करें",
            "auth_already_reg": "पहले से पंजीकृत हैं?",
            "auth_signin_instead": "इसके बजाय साइन इन करें",
            "auth_btn_register": "अभी पंजीकरण करें",
            "lbl_reg_email_addr": "पंजीकृत ईमेल पता",
            "auth_btn_send_otp": "सत्यापन कोड भेजें",
            "lbl_enter_otp": "ओटीपी कोड दर्ज करें (भेजा गया: 123456)",
            "auth_btn_sync_pass": "पासवर्ड सिंक करें",
            "modal_sidebar_rec_body": "भर्ती संस्था",
            "modal_sidebar_gov_board": "सरकारी चयन बोर्ड",
            "modal_sidebar_rel_status": "जारी होने की स्थिति",
            "modal_sidebar_status_live": "लाइव और डाउनलोड के लिए तैयार",
            "modal_sidebar_instructions_lbl": "निर्देश",
            "modal_sidebar_admit_instr_val": "उम्मीदवार अपने संदर्भ डैशबोर्ड में लॉग इन करके प्रवेश पत्र देख सकते हैं। कृपया आवंटित परीक्षा केंद्र पर प्रवेश पत्र की प्रिंटेड प्रति के साथ एक सक्रिय सरकारी फोटो पहचान पत्र (आधार, पासपोर्ट, पैन कार्ड) अवश्य लाएं।",
            "modal_sidebar_scheduled_next_month": "अगले महीने के लिए निर्धारित।",
            "modal_sidebar_reporting_time": "रिपोर्टिंग समय",
            "modal_sidebar_reporting_val": "सुबह 08:30 बजे (सख्त गेट बंद होने का समय लागू)।",
            "modal_sidebar_exam_segment": "परीक्षा खंड",
            "modal_sidebar_merit_cutoff_val": "अंतिम मेरिट और कटऑफ सूची",
            "modal_sidebar_review_status": "समीक्षा की स्थिति",
            "modal_sidebar_verification_complete": "आधिकारिक सत्यापन पूर्ण",
            "modal_sidebar_cutoff_params": "कटऑफ मानदंड",
            "modal_sidebar_result_congrats": "सभी योग्य उम्मीदवारों को बधाई! चयन बोर्ड शारीरिक सत्यापन और बायोमेट्रिक जांच के लिए जल्द ही पंजीकृत ईमेल पर बुलावा पत्र भेजेगा।",
            "modal_sidebar_subject_stream": "विषय स्ट्रीम",
            "modal_sidebar_combined_syllabus": "संयुक्त प्रतियोगी परीक्षा पाठ्यक्रम पैटर्न",
            "modal_sidebar_topic_outlines": "विषय रूपरेखा",
            "modal_sidebar_paper_1": "पेपर I (योग्यता और गणित)",
            "modal_sidebar_paper_1_val": "मात्रात्मक योग्यता, बीजगणित, संख्यात्मक विश्लेषण, डेटा व्याख्या।",
            "modal_sidebar_paper_2": "पेपर II (सामान्य अध्ययन)",
            "modal_sidebar_paper_2_val": "करंत अफेयर्स, संवैधानिक कानून, सार्वजनिक नीतियां, भारतीय इतिहास और भूगोल।",
            "modal_sidebar_marking_scheme": "अंकन योजना",
            "modal_sidebar_marking_val": "वस्तुनिष्ठ बहुविकल्पीय प्रश्न (MCQ) प्रारूप (प्रत्येक गलत उत्तर के लिए 0.25 नकारात्मक अंक)।",
            "important_dates": "महत्वपूर्ण तिथियाँ",
            "app_begin": "आवेदन शुरू:",
            "last_date_apply": "आवेदन की अंतिम तिथि:",
            "fee_last_date": "शुल्क भुगतान की अंतिम तिथि:",
            "exam_date_lbl": "परीक्षा तिथि:",
            "result_date_lbl": "संभावित परिणाम तिथि:",
            "app_fee_lbl": "आवेदन शुल्क",
            "fee_gen": "सामान्य / ओबीसी / ईडब्ल्यूएस:",
            "fee_sc": "एससी / एसटी / दिव्यांग:",
            "fee_female": "महिलाएं (सभी श्रेणी):",
            "fee_all": "सभी श्रेणी के उम्मीदवार:",
            "pay_mode_lbl": "भुगतान का प्रकार:",
            "pay_mode_desc": "आवेदन शुल्क का भुगतान केवल डेबिट कार्ड, क्रेडिट कार्ड, नेट बैंकिंग या यूपीआई के माध्यम से ही करें।",
            "age_limit_title": "आयु सीमा विवरण",
            "age_min_lbl": "न्यूनतम आयु",
            "age_max_lbl": "अधिकतम आयु",
            "age_ref_lbl": "आयु सीमा संदर्भ:",
            "age_relaxation_desc": "गणना भर्ती बोर्ड के नियमों के अनुसार की जाती है। सरकारी नियमानुसार आरक्षित श्रेणियों को अधिकतम आयु सीमा में छूट अतिरिक्त देय होगी।",
            "lifecycle_title": "भर्ती जीवन चक्र और अपडेट टाइमलाइन",
            "ai_summary_lbl": "एआई सारांश (AI Summary)",
            "vacancy_breakdown_title": "रिक्तियों का श्रेणी-वार विवरण",
            "overview_title": "भर्ती अवलोकन और आवश्यकताएं",
            "eligibility_title": "विस्तृत पात्रता मानदंड",
            "original_overview_title": "मूल विज्ञापन अवलोकन",
            "syllabus_title": "आधिकारिक पाठ्यक्रम और परीक्षा पैटर्न",
            "selection_details_title": "चयन प्रक्रिया का विवरण",
            "selection_process_lbl": "चयन प्रक्रिया",
            "faqs_title": "अक्सर पूछे जाने वाले प्रश्न (FAQs)",
            "instructions_title": "आवेदन फॉर्म कैसे भरें - चरण-दर-चरण निर्देश",
            "instr_1": "ओटीआर पंजीकरण: अधिकांश भर्ती बोर्ड (जैसे यूपीएससी, एसएससी और राज्य पीएससी) किसी भी परीक्षा आवेदन पत्र को जमा करने से पहले उनकी आधिकारिक वेबसाइट पर एक बार पंजीकरण (OTR) प्रोफ़ाइल पूरा करना अनिवार्य करते हैं।",
            "instr_2": "वेबकैम लाइव फोटोग्राफ: एसएससी और अन्य प्रमुख आयोगों को वेबकैम के माध्यम से या आधिकारिक मोबाइल ऐप के माध्यम से अपनी एक लाइव फोटो लेने की आवश्यकता होती है। एक साफ/सफेद पृष्ठभूमि के सामने खड़े हों और सीधे देखें। टोपी, चश्मा या मास्क न पहनें।",
            "instr_3": "विवरण जांचें और पूर्वावलोकन करें: फाइनल सबमिट पर क्लिक करने से पहले, आवेदन पत्र के पूर्वावलोकन की अच्छी तरह से समीक्षा करें। पुष्टि करें कि नाम की स्पेलिंग, जन्म तिथि, श्रेणी और प्रमाण पत्र बिल्कुल सही हैं।",
            "instr_4": "शुल्क भुगतान: जिन उम्मीदवारों को शुल्क की आवश्यकता है, वे भुगतान (यूपीआई, क्रेडिट/डेबिट कार्ड, या नेट बैंकिंग का उपयोग करके) पूरा करें। बिना भुगतान वाले आवेदनों को अपूर्ण माना जाता है और स्वचालित रूप से खारिज कर दिया जाता है।",
            "instr_5": "पुष्टि का प्रिंट लें: भविष्य के संदर्भ और परीक्षा उपस्थिति की पुष्टि के लिए अंतिम रूप से सबमिट किए गए आवेदन पत्र की एक प्रति सहेजें और प्रिंट कर लें।",
            "useful_links_title": "कुछ उपयोगी और महत्वपूर्ण लिंक्स",
            "seo_disclaimer_title": "अस्वीकरण और महत्वपूर्ण सूचना",
            "seo_disclaimer_text": "यद्यपि इस वेबसाइट पर जानकारी की सटीकता और पूर्णता सुनिश्चित करने के लिए हर संभव प्रयास किया गया है, GovJobs अधिसूचना विवरण या परिणामों में किसी भी अनजाने त्रुटि के लिए ज़िम्मेदार नहीं है। उम्मीदवारों को दृढ़ता से सलाह दी जाती है कि वे आधिकारिक अधिसूचना पीडीएफ डाउनलोड करें और आवेदन जमा करने से पहले आधिकारिक बोर्ड वेबसाइट पर सभी विनिर्देशों, समय-सीमाओं और आवश्यकताओं को सत्यापित करें।",
            "resource_name_th": "संसाधन / कार्य का नाम",
            "action_link_th": "लिंक",
            "apply_online_row": "ऑनलाइन आवेदन करें (पंजीकरण और लॉगिन)",
            "apply_online_btn": "ऑनलाइन आवेदन करें",
            "download_pdf_row": "आधिकारिक अधिसूचना (PDF) डाउनलोड करें",
            "download_pdf_btn": "नोटिस डाउनलोड करें",
            "download_syllabus_row": "पाठ्यक्रम और परीक्षा पैटर्न डाउनलोड करें",
            "view_syllabus_btn": "पाठ्यक्रम देखें",
            "official_website_row": "आधिकारिक भर्ती वेबसाइट",
            "visit_website_btn": "वेबसाइट पर जाएं",
            "join_telegram_row": "टेलीग्राम अलर्ट चैनल से जुड़ें",
            "telegram_channel_btn": "टेलीग्राम चैनल",
            "join_whatsapp_row": "WhatsApp अपडेट चैनल से जुड़ें",
            "whatsapp_group_btn": "WhatsApp ग्रुप",
            "submit_app_title": "आवेदन पत्र जमा करें",
            "submit_app_desc": "इस सरकारी नौकरी के लिए अपना आवेदन तुरंत पूरा करने के लिए अपना बायोडाटा/रिज्यूम अपलोड करें।",
            "select_cv_lbl": "बायोडाटा / रिज्यूम चुनें (PDF, DOC, DOCX अधिकतम 2MB)",
            "submit_app_btn": "अभी आवेदन जमा करें",
            "apply_announcement_title": "इस भर्ती के लिए आवेदन करें",
            "apply_announcement_desc": "अपने रिज्यूम प्रोफाइल का उपयोग करके सीधे आवेदन करने के लिए कृपया पंजीकरण करें या अपने उम्मीदवार डैशबोर्ड पर लॉगिन करें।",
            "login_register_apply_btn": "आवेदन के लिए लॉगिन / पंजीकरण करें",
            "related_updates_title": "इस परीक्षा से संबंधित अपडेट",
            "similar_jobs_title": "समान सरकारी नौकरियां",
            "exam_updates_title": "परीक्षा अपडेट और सूचनाएं",
            "explore_categories_title": "नौकरी श्रेणियों का पता लगाएं",
            "jobs_by_state_title": "राज्य के अनुसार नौकरियां",
            "view_details_btn": "विवरण देखें →",
            "explore_hubs_title": "अधिक सरकारी नौकरी हब खोजें",
            "explore_hubs_subtitle": "वैकल्पिक भर्ती, परीक्षा कार्यक्रम और परिपत्र घोषणाओं के लिए हमारे खोज नेटवर्क पर नेविगेट करें।",
            "state_wise_jobs_title": "राज्य-वार नौकरियां",
            "view_all_states_btn": "सभी राज्य देखें »",
            "district_jobs_title": "जिला नौकरियां",
            "trending_sectors_title": "ट्रेंडिंग सेक्टर्स",
            "utilities_hub_title": "उपयोगिता हब",
            "trending_categories_title": "ट्रेंडिंग श्रेणियां",
            "jobs_suffix": "नौकरियां",
            "apply_now_btn": "अभी आवेदन करें »",
            "no_active_recruitments_alerts": "कोई भर्ती वर्तमान में सक्रिय नहीं है। तत्काल सूचनाओं के लिए पंजीकरण करने हेतु अलर्ट फॉर्म का उपयोग करें!",
            "telegram_cta_title": "📢 संयुक्त टेलीग्राम अलर्ट नेटवर्क",
            "telegram_cta_desc": "सरकारी नौकरियों के वास्तविक समय की पुश फीड प्राप्त करें। अभी 150K+ उम्मीदवारों में शामिल हों!",
            "telegram_cta_btn": "चैनल से जुड़ें »",
            "email_alerts_title": "📧 ईमेल नौकरी अलर्ट",
            "email_alerts_desc_sidebar": "इस श्रेणी में नई रिक्तियां प्रकाशित होने पर सीधे अपने ईमेल पर तुरंत सूचनाएं प्राप्त करें।",
            "email_placeholder_lbl": "अपना-ईमेल@उदाहरण.कॉम",
            "btn_activate": "सक्रिय करें",
            "push_alerts_title": "🔔 डेस्कटॉप पुश अलर्ट",
            "push_alerts_desc_sidebar": "ईमेल देरी फिल्टर से बचने के लिए अपने ब्राउज़र विंडो के भीतर रीयल-टाइम पुश नोटिफिकेशन अधिकृत करें।",
            "btn_enable_push": "त्वरित अलर्ट सक्षम करें",
            "conversion_telemetry_title": "वास्तविक समय रूपांतरण टेलीमेट्री",
            "daily_page_visitors": "दैनिक पृष्ठ आगंतुक",
            "lead_subscribers": "लीड सब्सक्राइबर",
            "job_applicants": "नौकरी आवेदक",
            "conversion_efficiency": "रूपांतरण दक्षता",
            "live_opportunities_title": "लाइव सक्रिय अवसर",
            "eligibility_hero_title": "सरकारी नौकरी पात्रता जाँचकर्ता 2026",
            "eligibility_qualification_label": "🎓 उच्चतम योग्यता",
            "eligibility_select_qualification": "-- योग्यता चुनें --",
            "eligibility_state_label": "📍 पसंदीदा राज्य / क्षेत्र",
            "eligibility_all_india": "-- अखिल भारत (केंद्रीय) --",
            "eligibility_category_label": "💼 भर्ती श्रेणी",
            "eligibility_all_sectors": "-- सभी क्षेत्र --",
            "eligibility_age_label": "🎂 उम्मीदवार की आयु (वर्ष)",
            "eligibility_reset_btn": "फ़िल्टर रीसेट करें",
            "eligibility_check_btn": "पात्रता जाँचें »",
            "eligibility_matched_title": "मिलान की गई भर्तियां",
            "eligibility_no_check_title": "अभी तक कोई जांच नहीं की गई",
            "eligibility_no_check_desc": "ऊपर अपने पैरामीटर दर्ज करें और सक्रिय भर्तियों की खोज के लिए \"पात्रता जाँचें\" पर क्लिक करें।",
            "salary_hero_title": "सरकारी नौकरी वेतन मैट्रिक्स 2026",
            "salary_tab_stream": "💼 स्ट्रीम / सेक्टर अनुसार वेतन",
            "salary_tab_dept": "🏛️ विभाग अनुसार वेतन",
            "salary_tab_state": "📍 राज्य / क्षेत्र अनुसार वेतन",
            "salary_sector_heading": "भर्ती क्षेत्रों में वेतन श्रेणियां",
            "salary_dept_heading": "सबसे अधिक वेतन देने वाले सरकारी विभाग (शीर्ष 15)",
            "salary_state_heading": "राज्य / क्षेत्र अनुसार वेतन श्रेणियां",
            "salary_th_sector": "भर्ती क्षेत्र",
            "salary_th_department": "विभाग / संगठन",
            "salary_th_state": "राज्य / केंद्र शासित प्रदेश",
            "salary_th_avg": "औसत मासिक वेतन",
            "salary_th_range": "वेतन श्रेणी (न्यूनतम - अधिकतम)",
            "salary_th_active": "सक्रिय पद",
            "salary_th_actions": "कार्रवाई",
            "salary_explore_btn": "खोजें »",
            "salary_no_data": "वर्तमान में कोई वेतन एकत्रीकरण मेट्रिक्स उपलब्ध नहीं है।"
        }
    };

    // 3. Dynamic Database Value Mapper Lookups
    const dynamicLookups = {
        // Menu Titles
        "Home": "होम",
        "All Jobs": "सभी नौकरियाँ",
        "UPSC & SSC": "यूपीएससी और एसएससी",
        "Banking": "बैंकिंग",
        "Railways": "रेलवे",
        "About Us": "हमारे बारे में",
        "Contact Us": "संपर्क करें",
        "Careers": "करियर",
        "Privacy Policy": "गोपनीयता नीति",
        "Terms of Service": "सेवा की शर्तें",
        "Disclaimer": "अस्वीकरण",
        "Quick Links": "त्वरित लिंक्स",
        "Useful Links": "उपयोगी लिंक्स",
        "Legal & Info": "कानूनी और जानकारी",

        // Categories & Streams
        "Gov Job": "सरकारी नौकरी",
        "railway": "रेलवे",
        "railway jobs": "रेलवे भर्तियां",
        "banking": "बैंकिंग",
        "banking jobs": "बैंकिंग भर्तियां",
        "ssc": "एसएससी",
        "ssc jobs": "एसएससी भर्तियां",
        "upsc": "यूपीएससी",
        "upsc jobs": "यूपीएससी भर्तियां",
        "defence": "रक्षा",
        "defence jobs": "रक्षा भर्तियां",
        "psu": "पीएसयू",
        "psu jobs": "पीएसयू भर्तियां",
        "admission": "दाखिला",
        "scholarship": "छात्रवृत्ति",
        "state": "राज्य",
        "state jobs": "राज्य की नौकरियां",
        
        // Qualifications
        "Graduate": "स्नातक",
        "Post Graduate": "स्नातकोत्तर",
        "10th Pass": "10वीं पास",
        "12th Pass": "12वीं पास",
        "Diploma": "डिप्लोमा",
        "IT Graduate": "आईटी स्नातक",
        "B.Tech / B.E": "बी.टेक / बी.ई",
        "MBBS / Medical": "एमबीबीएस / मेडिकल",
        "PhD / Doctorate": "पीएचडी / डॉक्टरेट",
        "Any Degree": "कोई भी डिग्री",
        "Matriculation": "मैट्रिक",
        "Intermediate": "इंटरमीडिएट",
        
        // States / Regions
        "Andhra Pradesh": "आंध्र प्रदेश",
        "Arunachal Pradesh": "अरुणाचल प्रदेश",
        "Assam": "असम",
        "Bihar": "बिहार",
        "Chhattisgarh": "छत्तीसगढ़",
        "Delhi": "दिल्ली",
        "Goa": "गोवा",
        "Gujarat": "गुजरात",
        "Haryana": "हरियाणा",
        "Himachal Pradesh": "हिमाचल प्रदेश",
        "Jharkhand": "झारखंड",
        "Karnataka": "कर्नाटक",
        "Kerala": "केरल",
        "Madhya Pradesh": "मध्य प्रदेश",
        "Maharashtra": "महाराष्ट्र",
        "Manipur": "मणिपुर",
        "Meghalaya": "मेघालय",
        "Mizoram": "मिजोरम",
        "Nagaland": "नागालैंड",
        "Odisha": "ओडिशा",
        "Punjab": "पंजाब",
        "Rajasthan": "राजस्थान",
        "Sikkim": "सिक्किम",
        "Tamil Nadu": "तमिलनाडु",
        "Telangana": "तेलंगाना",
        "Tripura": "त्रिपुरा",
        "Uttar Pradesh": "उत्तर प्रदेश",
        "Uttarakhand": "उत्तराखंड",
        "West Bengal": "पश्चिम बंगाल",
        "Central": "केंद्र",
        "Pan India": "अखिल भारतीय",
        
        // Departments / Agencies
        "Staff Selection Commission": "कर्मचारी चयन आयोग",
        "Union Public Service Commission": "संघ लोक सेवा आयोग",
        "Railway Recruitment Board": "रेलवे भर्ती बोर्ड",
        "Reserve Bank of India": "भारतीय रिजर्व बैंक",
        "State Bank of India": "भारतीय स्टेट बैंक",
        "UPSC Board": "यूपीएससी बोर्ड",
        "SSC Board": "एसएससी बोर्ड",
        "Railways Board": "रेलवे बोर्ड",
        "APPLY OPEN": "आवेदन खुला है",
        "JOB": "नौकरी",
        "RESULT": "परिणाम",
        "ADMIT_CARD": "प्रवेश पत्र",
        "ANSWER_KEY": "उत्तर कुंजी",
        "SYLLABUS": "पाठ्यक्रम",
        "CUTOFF": "कटऑफ",
        "NOTICE": "नोटिस",
        "Original Announcement": "मूल विज्ञापन",
        "Admit Card Available": "प्रवेश पत्र उपलब्ध",
        "Final Exam Results": "अंतिम परीक्षा परिणाम",
        "Answer Key Objections": "उत्तर कुंजी आपत्ति",
        "Exam Syllabus Published": "परीक्षा पाठ्यक्रम प्रकाशित",
        "Declared Cutoffs": "घोषित कटऑफ",
        "Official Notice / Corrigendum": "आधिकारिक नोटिस / शुद्धिपत्र",
        "Trade-wise / Post-wise Posts": "ट्रेड-वार / पोस्ट-वार पद",
        "Category-wise Posts": "श्रेणी-वार पद",
        "Department-wise Posts": "विभाग-वार पद",
        "Trade-wise Posts": "ट्रेड-वार पद",
        "Discipline-wise Posts": "अनुशासन-वार पद",
        "General": "सामान्य (UR)",
        "OBC": "ओबीसी (OBC)",
        "SC": "एससी (SC)",
        "ST": "एसटी (ST)",
        "EWS": "ईडब्ल्यूएस (EWS)",
        "Announced Soon": "जल्द घोषित होगा",
        "(Exempted)": "(छूट प्राप्त)",
        "Free (No Fee)": "निःशुल्क",
        "Refer Notification": "विज्ञापन देखें",
        "Years": "वर्ष",
        "(This Page)": "(यह पृष्ठ)",
        "Result": "परिणाम",
        "Admit Card": "प्रवेश पत्र",
        "Govt Scale": "सरकारी वेतनमान",
        "Govt Scales": "सरकारी वेतनमान",
        "Till": "तक",
        "Government Ministry": "सरकारी मंत्रालय",
        "Government Board": "सरकारी बोर्ड",
        "Details": "विवरण",
        "Active": "सक्रिय",
        "Posts": "पद",
        "Explore": "खोजें",
        "Salary Matrix": "वेतन मैट्रिक्स",
        "Eligibility Checker": "पात्रता जाँचकर्ता",
        "Eligibility": "पात्रता",
        "Salary": "वेतन",
        "Railway Jobs": "रेलवे भर्तियां",
        "Banking Jobs": "बैंकिंग भर्तियां",
        "SSC Jobs": "एसएससी भर्तियां",
        "UPSC Exams": "यूपीएससी परीक्षाएं",
        "Defence Jobs": "रक्षा भर्तियां",
        "PSU Jobs": "पीएसयू भर्तियां",
        "Latest Jobs": "नवीनतम नौकरियां",
        "Exam Results": "परीक्षा परिणाम",
        "Admit Cards": "प्रवेश पत्र",
        "Answer Keys": "उत्तर कुंजी",
        "Syllabus": "पाठ्यक्रम",
        "Syllabus PDFs": "पाठ्यक्रम पीडीएफ",
        "Notices": "सूचनाएं",
        "Admissions": "प्रवेश",
        "Scholarships": "छात्रवृत्ति",
        "Cutoffs": "कटऑफ",
        "Exam Calendar": "परीक्षा कैलेंडर",
        "Previous Year Papers": "पिछले वर्ष के पेपर",
        "Jobs": "भर्तियां",
        "Jobs in": "में भर्तियां",
        "active": "सक्रिय"
    };

    // 4. Global Translation Helpers
    window.t = function(key, defaultVal) {
        const lang = localStorage.getItem(LANG_STORAGE_KEY) || 'en';
        
        // First try to look up in the active language dictionary
        const dict = window.translations[lang];
        if (dict && dict[key] !== undefined) {
            return dict[key];
        }

        if (lang === 'en') return defaultVal || key;

        const hiDict = window.translations['hi'];
        if (hiDict && hiDict[key] !== undefined) {
            return hiDict[key];
        }
        
        if (dynamicLookups[key]) {
            return dynamicLookups[key];
        }
        
        // Case insensitive lookups
        const lowerKey = typeof key === 'string' ? key.toLowerCase().trim() : '';
        for (let k in dynamicLookups) {
            if (k.toLowerCase() === lowerKey) {
                return dynamicLookups[k];
            }
        }

        return defaultVal || key;
    };

    window.translateJobTitle = function(title) {
        const lang = localStorage.getItem(LANG_STORAGE_KEY) || 'en';
        if (lang === 'en' || !title) return title;
        
        let translated = title;
        const titleDict = {
            'Recruitments': 'भर्तियां',
            'Recruitment': 'भर्ती',
            'Vacancies': 'रिक्तियां',
            'Vacancy': 'रिक्ति',
            'Notification': 'अधिसूचना',
            'Admit Cards': 'प्रवेश पत्र',
            'Admit Card': 'प्रवेश पत्र',
            'Entry Card': 'प्रवेश पत्र',
            'Hall Ticket': 'हॉल टिकट',
            'Results': 'परिणाम',
            'Result': 'परिणाम',
            'Answer Keys': 'उत्तर कुंजी',
            'Answer Key': 'उत्तर कुंजी',
            'Syllabus': 'पाठ्यक्रम',
            'Apply Online': 'ऑनलाइन आवेदन करें',
            'Online Form': 'ऑनलाइन फॉर्म',
            'Out': 'जारी',
            'Exam Date': 'परीक्षा तिथि',
            'Exam Date/Schedule': 'परीक्षा तिथि/कार्यक्रम',
            'Exam Schedule': 'परीक्षा कार्यक्रम',
            'Exam': 'परीक्षा',
            'Examination': 'परीक्षा',
            'Board': 'बोर्ड',
            'Board Jobs': 'बोर्ड भर्तियां',
            'Government Jobs': 'सरकारी नौकरियां',
            'Jobs': 'नौकरियां'
        };
        
        Object.keys(titleDict).forEach(word => {
            const regex = new RegExp('\\b' + word + '\\b', 'gi');
            translated = translated.replace(regex, titleDict[word]);
        });
        return translated;
    };

    // Helper to read and write cookies
    function setCookie(name, value, days) {
        let expires = "";
        if (days) {
            let date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "")  + expires + "; path=/; SameSite=Lax";
        // Also set on parent domain
        document.cookie = name + "=" + (value || "")  + expires + "; path=/; domain=" + window.location.hostname + "; SameSite=Lax";
    }

    function deleteCookie(name) {
        document.cookie = name + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
        document.cookie = name + "=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/; domain=" + window.location.hostname + ";";
    }

    // 5. DOM Translation Engine
    function translatePage(langCode) {
        const dict = window.translations[langCode];
        if (!dict) return;

        // Parse standard elements
        document.querySelectorAll('[data-i18n]').forEach(el => {
            const key = el.getAttribute('data-i18n');
            if (dict[key]) {
                // If it is an input with placeholder
                if (el.tagName === 'INPUT' && el.hasAttribute('placeholder')) {
                    el.setAttribute('placeholder', dict[key]);
                } else {
                    el.innerText = dict[key];
                }
            }
        });

        // Parse HTML elements
        document.querySelectorAll('[data-i18n-html]').forEach(el => {
            const key = el.getAttribute('data-i18n-html');
            if (dict[key]) {
                el.innerHTML = dict[key];
            }
        });

        // Parse dynamic titles
        document.querySelectorAll('[data-translate-title]').forEach(el => {
            const raw = el.getAttribute('data-translate-title');
            el.innerText = langCode === 'hi' ? window.translateJobTitle(raw) : raw;
        });

        // Parse dynamic values using t() lookup
        document.querySelectorAll('[data-translate-lookup]').forEach(el => {
            const raw = el.getAttribute('data-translate-lookup');
            const suffix = el.getAttribute('data-translate-suffix') || '';
            const prefix = el.getAttribute('data-translate-prefix') || '';
            const translated = langCode === 'hi' ? window.t(raw, raw) : raw;
            el.innerText = prefix + translated + suffix;
        });

        // Parse keys with prefix / suffix / count
        document.querySelectorAll('[data-translate-key]').forEach(el => {
            const key = el.getAttribute('data-translate-key');
            const suffix = el.getAttribute('data-translate-suffix') || '';
            const prefix = el.getAttribute('data-translate-prefix') || '';
            const count = el.getAttribute('data-translate-count');
            let translated = window.t(key, key);
            if (count !== null && count !== undefined) {
                translated = translated.replace('{count}', count);
            }
            el.innerText = prefix + translated + suffix;
        });

        // Set html tag lang attribute
        document.documentElement.setAttribute('lang', langCode);

        // Toggle stylesheet font triggers
        if (langCode === 'hi') {
            document.documentElement.classList.add('lang-hi');
        } else {
            document.documentElement.classList.remove('lang-hi');
        }
    }

    // Synchronize Language buttons UI
    function syncSwitcherUI(langCode) {
        const switchers = document.querySelectorAll('.lang-switcher');
        switchers.forEach(switcher => {
            const btns = switcher.querySelectorAll('.lang-btn');
            btns.forEach(btn => {
                if (btn.getAttribute('data-lang') === langCode) {
                    btn.classList.add('active');
                    btn.setAttribute('aria-current', 'true');
                } else {
                    btn.classList.remove('active');
                    btn.setAttribute('aria-current', 'false');
                }
            });
        });

        // Toggle reset float pill
        const resetPill = document.getElementById('langResetPill');
        if (resetPill) {
            resetPill.style.display = 'flex'; // Always visible
            const textEl = document.getElementById('langResetPillText');
            if (textEl) {
                if (langCode === 'hi') {
                    textEl.innerText = 'अंग्रेज़ी में बदलें';
                    resetPill.setAttribute('aria-label', 'अंग्रेज़ी में बदलें');
                } else {
                    textEl.innerText = 'Switch to Hindi';
                    resetPill.setAttribute('aria-label', 'Switch to Hindi');
                }
            }
        }
    }

    // Show dynamic overlay loader
    function showLoader() {
        const loader = document.getElementById('translationLoader');
        if (loader) {
            loader.classList.add('active');
            setTimeout(() => {
                loader.classList.remove('active');
            }, 550);
        }
    }

    // 6. Global Switch Callback
    window.setPortalLanguage = function(langCode, userTriggered = false) {
        if (isBotOrLighthouse) return;

        localStorage.setItem(LANG_STORAGE_KEY, langCode);
        setCookie('preferred_language', langCode, 365);

        // Apply translations
        translatePage(langCode);
        syncSwitcherUI(langCode);

        if (userTriggered) {
            showLoader();
        }
    };

    // 7. Auto Detection consent prompt card
    function checkAutoDetection() {
        if (isBotOrLighthouse) return;

        const preferredLang = localStorage.getItem(LANG_STORAGE_KEY);
        const hasChoice = localStorage.getItem(POPUP_CHOICE_KEY);

        const browserLanguages = navigator.languages || [navigator.language || navigator.userLanguage];
        const isBrowserHindi = browserLanguages.some(lang => lang && lang.toLowerCase().startsWith('hi'));

        if (!preferredLang && !hasChoice && isBrowserHindi) {
            const popup = document.getElementById('langSuggestionPopup');
            if (popup) {
                setTimeout(() => {
                    popup.style.display = 'flex';
                }, 3000);
            }
        }
    }

    // 8. Event Initializations
    document.addEventListener('DOMContentLoaded', function() {
        if (isBotOrLighthouse) {
            // Remove hidden body FOUC blockers for web scrapers immediately
            const styleOverrides = document.querySelectorAll('style');
            styleOverrides.forEach(style => {
                if (style.innerHTML.includes('body { visibility: hidden !important; }')) {
                    style.remove();
                }
            });
            if (document.body) document.body.style.visibility = 'visible';
            return;
        }

        const activeLang = localStorage.getItem(LANG_STORAGE_KEY) || 'en';
        
        // Translate page immediately before display
        translatePage(activeLang);
        syncSwitcherUI(activeLang);

        // Remove the FOUC style overrides to show translated page
        const styleOverrides = document.querySelectorAll('style');
        styleOverrides.forEach(style => {
            if (style.innerHTML.includes('body { visibility: hidden !important; }')) {
                style.remove();
            }
        });
        if (document.body) document.body.style.visibility = 'visible';

        // Bind switcher clicks
        document.body.addEventListener('click', function(e) {
            const btn = e.target.closest('.lang-btn');
            if (btn) {
                e.preventDefault();
                const lang = btn.getAttribute('data-lang');
                window.setPortalLanguage(lang, true);
            }

            const resetBtn = e.target.closest('.lang-reset-pill');
            if (resetBtn) {
                e.preventDefault();
                const currentLang = localStorage.getItem(LANG_STORAGE_KEY) || 'en';
                const nextLang = currentLang === 'hi' ? 'en' : 'hi';
                window.setPortalLanguage(nextLang, true);
            }
        });

        // Bind suggestion popup buttons
        const btnSwitchToHindi = document.getElementById('btnSwitchToHindi');
        const btnContinueEnglish = document.getElementById('btnContinueEnglish');
        const popup = document.getElementById('langSuggestionPopup');

        if (btnSwitchToHindi) {
            btnSwitchToHindi.addEventListener('click', function() {
                localStorage.setItem(POPUP_CHOICE_KEY, 'true');
                if (popup) popup.style.display = 'none';
                window.setPortalLanguage('hi', true);
            });
        }

        if (btnContinueEnglish) {
            btnContinueEnglish.addEventListener('click', function() {
                localStorage.setItem(POPUP_CHOICE_KEY, 'true');
                if (popup) popup.style.display = 'none';
                window.setPortalLanguage('en', false);
            });
        }

        checkAutoDetection();

        // Hook into dynamic jQuery AJAX template loads
        if (window.jQuery) {
            // Translate jQuery templates immediately on AJAX complete
            jQuery(document).ajaxComplete(function(event, xhr, settings) {
                const currentLang = localStorage.getItem(LANG_STORAGE_KEY) || 'en';
                // Walk newly created DOM fields that might have data-i18n
                translatePage(currentLang);
            });
        }
    });
})();
