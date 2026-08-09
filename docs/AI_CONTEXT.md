---
title: "AI Context & Developer Guidelines"
aliases: ["AI Context","Developer Guidelines"]
tags: ["guidelines","ai","development","existing"]
created: 2024-01-05
updated: 2026-07-05
---

# AI Context: Developer/Agent Guidelines

This is the primary context file for AI assistants. Read this before reading or modifying any other files in the codebase.

---

## 1. System Architecture Summary

The project is structured using a hybrid **Domain-Driven Design (DDD)** layout:
* Core features are compartmentalized under `app/Domains/<DomainName>`.
* Common configurations, middlewares, global models, and providers remain in standard Laravel locations (`app/Http`, `app/Models`, `bootstrap/app.php`).

### Domain Modules
1. **Admin**: Backups, Settings, Queue/DLQ telemetry, Master Data.
2. **Extraction**: Parsers, OCR engines (Tesseract, PaddleOCR, Gemini, etc.), AI validation.
3. **Jobs**: Job Post listings, Timeline, Search service, SEO internal links, Sitemap.
4. **Notifications**: Email tracking, digest emails, alerts.
5. **Scrapers**: Crawling engine, fuzzy deduplication, title variance checks, scraper drivers.
6. **Users**: Candidate profiles, JWT auth, dashboard telemetry.

---

## 2. Important Business Rules

### deduplication Pipeline (3 Gates)
Before inserting any scraped item:
1. **Stage 1 (Exact Fingerprint Gate)**: Computes a SHA-256 fingerprint of the `title`, `department_id`, `source_url`, and `publish_date`. Collisions reject the insert or log them as duplicates.
2. **Stage 2 (Fuzzy Similarity Gate)**: Compares the title string using `similar_text()` against other recent postings under the same department. Similarity $\ge 85\%$ is flagged as duplicate.
3. **Stage 3 (Title Variant Gate)**: Compares year-stripped and acronym-expanded variants of the title. Matches are flagged as duplicates.

### Parent-Child Job Post State Machine
A job posting can have child notices (results, admit cards, answer keys, syllabi, exam dates, or corrigenda).
* Sub-notices are stored as separate `JobPost` records with `parent_id` pointing to the main recruitment post.
* When a child is processed, the `RecruitmentLifecycleManager` triggers status transitions on the parent post (e.g., transitioning from `published` to `admit_card_released`, `result_declared`, `archived`, etc.).

### AI Structuring & Quarantine
* Extraction uses LLMs to structure PDF text/images into JSON.
* A confidence check calculates a score based on field validation.
* Any extracted notice with an AI confidence score **under 85%** must be quarantined in `ScrapingLog` for manual human review and approval (rather than being auto-published).

---

## 3. Naming & Coding Standards

* **Dependency Inversion**: Service layers and controllers MUST depend on Repository Interfaces, not concrete classes. Interfaces are bound to concrete Eloquent repositories inside [AppServiceProvider](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Providers/AppServiceProvider.php).
* **Naming Conventions**:
  * Models: Singular PascalCase (`JobPost`).
  * Repositories: `<Name>Repository` implementing `<Name>RepositoryInterface`.
  * Services: `<Name>Service` implementing `<Name>ServiceInterface`.
  * Middlewares: PascalCase describing check (`EnsureActiveUser`).
* **Cache Busting**: All cache flushes are managed via [JobPostObserver](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Observers/JobPostObserver.php). Never flush caches manually inside services or controllers.

---

## 4. CRITICAL: Things AI Must Never Change

1. **Authentication guards**: Do not touch the custom stateless `jwt` request guard defined in `AppServiceProvider::boot`.
2. **Database Schemas**: Do not alter existing columns or constraints without explicit authorization (especially fingerprints and constraints on `uq_job_posts_fingerprint`).
3. **SEO Settings and URL Formats**: The URL formats `/job/{slug}`, `/sitemaps/sitemap-jobs.xml`, etc., are critical for index ranking. Do not change route paths or names.
4. **HTML Sanitization**: All rich-text inputs from scrapers must pass through `HtmlSanitizer` to prevent Stored XSS. Do not bypass this safety layer.

---

## 5. Files Requiring Extra Care

* [AppServiceProvider.php](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Providers/AppServiceProvider.php): Holds core interface bindings, guards, rate-limiting rules, and global query performance checks (disabling lazy loading in non-prod).
* [bootstrap/app.php](file:///C:/xampp_8.2.12/htdocs/vision-mission/bootstrap/app.php): Handles standard global exceptions, validation rendering, and middleware routing aliases.
* [JobPost.php](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Models/JobPost.php): Core model containing relations, scopes, and filter mechanisms.
* [ScrapingService.php](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Domains/Scrapers/Services/ScrapingService.php): Handles the processing flow of all crawled postings, deduplication, and LLM structured extraction.

---

## 6. Safe Modification Strategy

1. **Locate matching repository**: If modifying queries or database fetches, write them in the repository classes inside `app/Domains/*/Repositories/Eloquent` and register them in the interfaces.
2. **Verify tests**: Always run tests before and after modifications:
   ```powershell
   php artisan test
   ```
3. **Keep changes localized**: Do not perform broad refactorings. Limit modifications exactly to the requested module or service.

---
## Related Notes
* **MOC Connections**: [[DevOps MOC]], [[Business MOC]]
* **Navigation**:
  * ➡️ Next: [[AI_RULES]]
  * 🏠 Home: [[Home]] | 📊 Dashboard: [[Dashboard]]
