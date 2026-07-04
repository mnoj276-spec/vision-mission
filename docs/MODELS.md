# Models & Eloquent ORM Mapping

`vision-mission` contains 38 Eloquent Models. To avoid high memory overhead and maintain code cleanliness, models are grouped logically.

---

## 1. Core Recruitment Models

### [JobPost](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Models/JobPost.php)
* **Description**: Central table holding job advertisements, results, answer keys, syllabi, and admit cards (distinguished by the `post_type` column).
* **Self-Referential Hierarchy**:
  * `parent_id` points to the primary recruitment. A sub-notice (corrigendum, result sheet, hall ticket) acts as a child post.
  * Relations: `parent()` (BelongsTo), `children()` (HasMany).
* **Relations**: `category()`, `source()`, `department()`, `state()`, `district()`, `qualification()`, `tags()` (BelongsToMany), `bookmarks()`, `applications()`, `categoryVacancies()`, `aiContent()` (HasOne).
* **Scopes**:
  * Type Filters: `scopeJobs()`, `scopeResults()`, `scopeAdmitCards()`, `scopeAnswerKeys()`, `scopeSyllabi()`, `scopeNotices()`, `scopeAdmissions()`, `scopeScholarships()`.
  * Status Filters: `scopeRootPosts()` (where `parent_id IS NULL`), `scopePublished()`, `scopeFeatured()`, `scopeSponsored()`.
  * Search Logic: `scopeSearch($term)` performs MySQL BOOLEAN MATCH or SQL `LIKE` fallback.
  * Attributes Filter: `scopeFilterBy($filters)` dynamically maps state, district, salary, and fees.
* **Observer**: [JobPostObserver](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Observers/JobPostObserver.php) (flushes query caches, schedules IndexNow submitter on publish).
* **Traits**: `SoftDeletes`, `HasFactory`.

### JobPostAiContent
* **Description**: Houses SEO-optimized details for a job post.
* **Relations**: `jobPost()` (BelongsTo).

### ExtractedNotification
* **Description**: Holds incoming PDF/image uploads parsed by parsers/OCR before human review/approval.

---

## 2. Master Meta Models
These define categorizations for jobs:
* **Category**: Main career domains (e.g. Railway, Bank). Relates to `JobPost` (HasMany).
* **Department**: Government departments or agencies (e.g. UPSC, Staff Commission).
* **State**: Regional scopes (e.g. Karnataka, Uttar Pradesh, CENTRAL).
* **District**: Sub-regional granularity (BelongsTo `State`).
* **Qualification**: Minimum required academic thresholds (e.g. 10th Pass, Graduate).
* **Tag**: Custom tags linked via `job_post_tags` join table.
* **CategoryVacancy**: Tracks reservation splits (caste, gender divisions) for a specific `JobPost`.

---

## 3. Candidates & Interactions
* **User**: Standard user account. Original `role` string maps to Spatie permissions. Custom `is_active` filter. Relates to `bookmarks()` (HasMany), `applications()` (HasMany).
* **Bookmark**: Saved job alerts for candidates.
* **JobApplication**: Submitted candidacies with resume PDF attachment paths.

---

## 4. Scrapers & Deduplication Logs
* **ScrapingSource**: Configs for scraping tasks (target URL, frequency intervals, css selectors). Relates to `logs()` (HasMany).
* **ScrapingLog**: Historical records of scraper executions (items found, errors, validation list, state).
* **DuplicateAuditLog**: Log of hits blocked by deduplication filters.
* **AiAuditLog**: Confidence validation tracker logs.
* **AuditLog**: Admin activity logger.

---

## 5. Dynamic Settings & CMS
* **Setting** & **SettingGroup**: Dynamic key-value mappings for core features.
* **ThemeSetting**, **EmailSetting**, **ApiSetting**, **SeoSetting**: Cast-mapped setups.
* **SocialLink**: Header/footer social navigation widgets.
* **CmsPage**: Dynamic pages (e.g., Privacy Policy, About Us) rendered via `/p/{slug}` route.
* **Menu** & **MenuItem**: Custom site navigations.

---

## 6. Telemetry & Marketing
* **AnalyticsPageView**: Logs browser pages, referrers, IPs, and user agents.
* **AnalyticsJobEvent**: Logs actions on job posts (clicks, shares, applies).
* **AnalyticsRevenueEvent**: Logs monetized click conversions.
* **AnalyticsSearchQuery**: Captures search terms to audit typo corrections.
* **EmailLog**: Record of all automated emails sent.
* **JobAlert**: Email alerts saved by guest users.
* **PersonalRefreshToken**: Dynamic JWT bearer storage.
* **Advertisement**: Holds Ad slots configurations.
