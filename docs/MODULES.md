# Domain Modules Reference

`vision-mission` organizes its business logic into 6 domain modules under the `app/Domains` namespace. This file outlines each module's purpose, controllers, models, views, routing hooks, dependencies, related tables, and potential risks.

---

## 1. Admin Module
* **Purpose**: Coordinates website configurations, master tables, advertising banners, database backups, email SMTP testing, and horizon queue telemetry.
* **Controllers**:
  * [AdManagementController](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Domains/Admin/Controllers/AdManagementController.php): Store, update, and toggle active banners.
  * [AdminDashboardController](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Domains/Admin/Controllers/AdminDashboardController.php): Admin page metrics, audit log fetching.
  * [AiContentManagementController](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Domains/Admin/Controllers/AiContentManagementController.php): Review, edit, and approve AI-generated summaries.
  * [SettingsManagementController](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Domains/Admin/Controllers/SettingsManagementController.php): Dynamic settings panel (Logo, theme, social links, SMTP, CMS Page builder).
  * [MasterDataController](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Domains/Admin/Controllers/MasterDataController.php): CRUD for master states, categories, and qualifications.
  * [QueueManagementController](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Domains/Admin/Controllers/QueueManagementController.php): DLQ recovery, retries, flushes.
* **Models**: `Setting`, `SettingGroup`, `ThemeSetting`, `EmailSetting`, `ApiSetting`, `SeoSetting`, `SocialLink`, `AuditLog`, `CmsPage`, `Menu`, `MenuItem`, `Advertisement`, `Banner`.
* **Views**: Admin dashboard panels.
* **Routes**: Protected routes inside [routes/admin.php](file:///C:/xampp_8.2.12/htdocs/vision-mission/routes/admin.php) under `/api/admin` prefix.
* **Dependencies**: `spatie/laravel-permission`
* **Related Tables**: `settings`, `setting_groups`, `cms_pages`, `menus`, `menu_items`, `audit_logs`, `social_links`, `advertisements`.
* **Potential Risks**: Backup script timeout on large files; unauthorized settings modifications if permissions checks are bypassed.

---

## 2. Extraction Module
* **Purpose**: Orchestrates document parsing pipeline. Processes incoming government PDF notifications (both machine-readable and scanned images via OCR engines) and parses fields using AI text extraction.
* **Controllers**:
  * [ExtractionController](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Http/Controllers/Api/V1/Extraction/ExtractionController.php): REST API for manual notification document uploads, status tracking, and AI structuring approvals.
* **Models**: `ExtractedNotification`.
* **Views**: None (API/JSON only).
* **Routes**: Extraction routes in `routes/api.php` under `/api/v1/extraction`.
* **Dependencies**: `smalot/pdfparser`, `phpoffice/phpspreadsheet`, `phpoffice/phpword`, Python sidecars for Tesseract/PaddleOCR, Gemini/OpenAI API integrations.
* **Related Tables**: `extracted_notifications`.
* **Potential Risks**: High memory consumption during heavy PDF text extractions; remote OCR API execution latency and pricing.

---

## 3. Jobs Module
* **Purpose**: Core domain for recruitment notices, search matching, salary analytics, eligibility calculations, sitemap feeds, and programmatic SEO landings.
* **Controllers**:
  * [JobController](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Domains/Jobs/Controllers/JobController.php): Frontend listings, detail cards.
  * [SearchController](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Domains/Jobs/Controllers/SearchController.php): Typo tolerance, organization filters, search pages.
  * [ProgrammaticSeoController](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Domains/Jobs/Controllers/ProgrammaticSeoController.php): State-wise, category-wise landing pages.
  * [SitemapController](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Domains/Jobs/Controllers/SitemapController.php): Crawl sitemaps for pages, jobs, images, FAQs, and Google News.
  * [EligibilityController](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Domains/Jobs/Controllers/EligibilityController.php): Age limit and qualification checkers.
  * [SalaryInfoController](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Domains/Jobs/Controllers/SalaryInfoController.php): Base salary ranges dashboard.
  * [ApplicationController](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Domains/Jobs/Controllers/ApplicationController.php): Bookmarking and applying to postings.
* **Models**: `JobPost`, `JobPostAiContent`, `Category`, `Department`, `State`, `District`, `Qualification`, `Tag`, `CategoryVacancy`, `Bookmark`, `JobApplication`, `AnalyticsPageView`, `AnalyticsJobEvent`, `AnalyticsRevenueEvent`, `AnalyticsSearchQuery`.
* **Views**: Public job portal web views (blade templates), XML sitemaps.
* **Routes**: Public views defined in `routes/web.php` and public AJAX routes in `routes/api.php`.
* **Dependencies**: MySQL Full-Text Search index, IndexNow service.
* **Related Tables**: `job_posts`, `job_post_ai_contents`, `categories`, `departments`, `states`, `districts`, `qualifications`, `category_vacancies`, `bookmarks`, `job_applications`, `tags`, `job_post_tags`, `analytics_page_views`, `analytics_job_events`, `analytics_search_queries`.
* **Potential Risks**: SQL query degradation during complex multi-joins on millions of posts; IndexNow API rate limits.

---

## 4. Notifications Module
* **Purpose**: Coordinates email dispatching triggers, digest delivery, welcome series automations, and tracking token instrumentation.
* **Controllers**:
  * [EmailTrackingController](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Http/Controllers/EmailTrackingController.php): Receives email open tracking pixels and link clicks.
* **Models**: `EmailLog`, `EmailSetting`, `JobAlert`.
* **Views**: Email templates (Blade layout mailables).
* **Routes**: Open tracking pixels and link redirections in `routes/web.php`.
* **Dependencies**: SMTP or external email service provider (Mailgun/SES).
* **Related Tables**: `email_logs`, `job_alerts`.
* **Potential Risks**: Queue blocks due to massive SMTP failures; email tracking database table swelling on heavy click rates.

---

## 5. Scrapers Module
* **Purpose**: Pulls announcements from government domains, validates the feeds, classifies the notification types, and runs fuzzy duplicate mitigation filters.
* **Controllers**:
  * [ScraperController](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Domains/Scrapers/Controllers/ScraperController.php): Scraping source creation, toggle, manual trigger runner, quarantine rescue.
* **Models**: `ScrapingSource`, `ScrapingLog`, `DuplicateAuditLog`, `AiAuditLog`.
* **Views**: None.
* **Routes**: Dedicated routes defined in [routes/scraper.php](file:///C:/xampp_8.2.12/htdocs/vision-mission/routes/scraper.php).
* **Dependencies**: Laravel HTTP Client, `similar_text()` helper.
* **Related Tables**: `scraping_sources`, `scraping_logs`, `duplicate_audit_logs`, `ai_audit_logs`.
* **Potential Risks**: Scraping selector breakages due to external website layout adjustments; SSRF attacks via malicious target URLs (mitigated by `UrlSecurity`).

---

## 6. Users Module
* **Purpose**: Candidate authentication, token refreshing, dashboard telemetry, and preference management.
* **Controllers**:
  * [AuthController](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Domains/Users/Controllers/AuthController.php): Sign up, stateless login/logout.
  * [DashboardController](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Domains/Users/Controllers/DashboardController.php): Candidates Dashboard, profile preference configurations.
* **Models**: `User`, `PersonalRefreshToken`.
* **Views**: Account login and profile dashboard pages.
* **Routes**: `/api/register`, `/api/login`, and `/api/profile/*` groups.
* **Dependencies**: `JwtService` library.
* **Related Tables**: `users`, `personal_refresh_tokens`.
* **Potential Risks**: Refresh token leakage; JWT key verification speed bottleneck under concurrent requests.
