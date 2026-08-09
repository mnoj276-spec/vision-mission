---
title: "Performance Optimization Reference"
aliases: ["Performance Optimization","Performance"]
tags: ["performance","caching","database","existing"]
created: 2024-01-05
updated: 2026-07-05
---

# Performance Optimization Reference

This document details database indexing, query caching, N+1 query prevention, and crawler optimization layers.

---

## 1. N+1 Query Prevention & Eager Loading

To prevent database round-trip performance bottlenecks, model lazy loading is disabled in non-production environments:
* **Implementation**: Inside [AppServiceProvider.php](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Providers/AppServiceProvider.php):
  ```php
  Model::preventLazyLoading(!$this->app->isProduction());
  ```
* **Developer Workflow**: If an N+1 query is triggered during development or feature test runs, an exception is immediately thrown. Developers must eager load relationships using `with()` (e.g. `JobPost::with(['state', 'district', 'category'])->get()`).

---

## 2. Dynamic Database Indexing

The DB schema is optimized for fast read-heavy search and filtering operations:
* **Composite Filter Index**: An index exists on `['status', 'published_at', 'is_featured']` inside the `job_posts` table to optimize primary landing list fetches.
* **Timeline Index**: The self-referential `parent_id` column is indexed to compile recruitment timelines.
* **Full-Text Index**: MySQL `MATCH AGAINST` is executed on `(title, description)` to perform typo-tolerant autocomplete matches.
* **Deduplication Constraint**: `fingerprint` column has a unique key constraint to intercept race-condition duplicate inserts.

---

## 3. High-Performance Query Caching

* **Automated Cache Busting**: [JobPostObserver](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Observers/JobPostObserver.php) monitors the lifecycle of listings. When posts are created, updated, or deleted, it automatically busts specific cache tags (`homepage_data`, sitemap caches) globally.
* **Contextual Linking Cache**: The [InternalLinkingService](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Domains/Jobs/Services/InternalLinkingService.php) caches link packages (related jobs, admit cards, categories) for specific details and landings.
* **Internal Links Pre-Warming**: The console task `seo:warm-internal-links` calculates cross-links and warms the cache before heavy search engine sweeps.

---

## 4. Adaptive Crawl Scheduling

To reduce network overhead and API call spikes:
* If a scraper finds new postings, the interval is set to **30 minutes** (active feed mode).
* If a scraper run results in no new postings, the crawler scales up the execution interval by **1.5x** up to a maximum cap of **24 hours** (idle feed mode).

---
## Related Notes
* **MOC Connections**: [[DevOps MOC]], [[Analytics MOC]]
* **Navigation**:
  * ⬅️ Prev: [[ERROR_HANDLING]]
  * ➡️ Next: [[TESTING]]
  * 🏠 Home: [[Home]] | 📊 Dashboard: [[Dashboard]]
