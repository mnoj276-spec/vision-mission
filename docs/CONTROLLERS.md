# Controllers & HTTP Endpoints Reference

Controllers in `vision-mission` are partitioned between Laravel's standard REST API folders and DDD domain controller subdirectories.

---

## 1. Candidate Front-End Controllers (`app/Domains/Jobs/Controllers` & `app/Domains/Users/Controllers`)

### JobController
* **Purpose**: Fetches landing page cards, specific detail views, CMS pages, and lead captures.
* **Public Methods**: `index()`, `show()`, `offline()`, `subscribeAlerts()`, `trackEvent()`, `showCmsPage()`.
* **Dependencies**: `JobServiceInterface`.
* **Related Route**: `/`, `/jobs`, `/p/{slug}`, `/api/growth/subscribe`.

### SearchController
* **Purpose**: Coordinates full-text job searches and autocompletes.
* **Public Methods**: `search()`, `stateSearch()`, `categorySearch()`, `qualificationSearch()`, `organizationSearch()`, `apiAutocomplete()`, `apiTypoCorrection()`.
* **Dependencies**: `SearchServiceInterface`.
* **Related Route**: `/search`, `/api/search/autocomplete`, `/api/search/typo`.

### ProgrammaticSeoController
* **Purpose**: Generates semantic landings, tracks internal crawler-friendly clicks, and sets optimization response headers.
* **Public Methods**: `results()`, `admitCards()`, `answerKeys()`, `syllabus()`, `stateJobs()`, `districtJobs()`, `showJob()`, `trackLinkClick()`.
* **Dependencies**: `JobServiceInterface`, `SeoService`.
* **Related Route**: `/jobs/state/{state_slug}/{district_slug}`, `/job/{slug}`, `/api/internal-link/click`.

### SitemapController
* **Purpose**: Delivers search-engine optimized XML sitemaps.
* **Public Methods**: `index()`, `pages()`, `jobs()`, `images()`, `videos()`, `faqs()`, `news()`, `indexNowKey()`.
* **Dependencies**: `SeoService`.
* **Related Route**: `/sitemap.xml`, `/sitemaps/sitemap-jobs.xml`, `/{key}.txt`.

### AuthController
* **Purpose**: Candidate registration, logins, and recovery.
* **Public Methods**: `register()`, `login()`, `logout()`, `forgotPassword()`, `resetPassword()`.
* **Dependencies**: `AuthServiceInterface`.
* **Related Route**: `/api/register`, `/api/login`, `/api/logout`.

### DashboardController & ApplicationController
* **Purpose**: Processes bookmarks, job applications, preferences, and dashboards.
* **Public Methods**: `getDashboardData()`, `updateProfile()`, `updatePreferences()`, `toggleBookmark()`, `applyJob()`.
* **Dependencies**: `JobServiceInterface`.
* **Related Route**: `/api/dashboard`, `/api/jobs/{id}/bookmark`, `/api/jobs/{id}/apply`.

---

## 2. API Version 1.0 Mobile Endpoints (`app/Http/Controllers/Api/V1/*`)
Strictly throttled REST APIs returning structured JSON payloads:
* **Auth/AuthController**: Standard JWT login/register/refreshes.
* **Jobs/JobController**: REST endpoints for mobile jobs list, timeline matching, bookmarks, and applications.
* **Search/SearchController**: REST endpoints for search autocomplete.
* **Users/ProfileController**: REST endpoints for profile and preferences update.
* **Extraction/ExtractionController**: Allows administrators to upload raw PDFs/images, verify extraction statuses, and approve them into `job_posts`.

---

## 3. Administrative Controllers (`app/Domains/Admin/Controllers` & `app/Domains/Scrapers/Controllers`)

All routes require auth, EnsureAdmin middleware, and Spatie permission checks:
* **AdminDashboardController**: Fetches dashboard metrics, audit logs, and handles SEO updates.
* **AdManagementController**: CRUD for active revenue banner slots.
* **AiContentManagementController**: Reviews and edits summaries, and manually triggers AI generation.
* **SettingsManagementController**: Complex controller handling theme details, General details, logo uploads, social links, menu re-order hierarchies, CMS page saves, media directory uploads, and dynamic DB backup operations.
* **MasterDataController**: CRUD endpoints for states, categories, departments, and qualifications.
* **QueueManagementController**: Integrates with Horizon to flush queues, list failed jobs, and run retries.
* **ResumeDownloadController**: Standard PDF downloader for candidates' resumes.
* **ScraperController**: CRUD scraper sources, toggles source crawl states, and triggers manual runner instances.

---

## 4. Telemetry & Monetization Controllers (`app/Http/Controllers/*`)
* **AnalyticsApiController**: Captures pageviews, interaction triggers, and ad events for analytical auditing.
* **EmailTrackingController**: Tracks email pixel opens and redirect clicks.
* **MonetizationController**: Handles `/go/{slug}` affiliate link generation redirects, membership payments, and revenue analytics.
