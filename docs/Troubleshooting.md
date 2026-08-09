---
title: "Troubleshooting"
aliases: ["Troubleshooting Guide", "FAQ"]
tags: ["troubleshooting", "faq", "fixes"]
created: 2026-07-05
updated: 2026-07-05
---

# Troubleshooting & Issue Resolution 🛠️

Guidance on resolving issues when developing or running the platform.

## Active Technical Debt & Workarounds
See [[KNOWN_ISSUES]] for details:
1. **Scraper Failures (Gov Site Changes)**:
   - *Symptom*: Scraper fails to scrape content or crashes.
   - *Fix*: Inspect gov portal structure, update selectors in scraper drivers, or bypass via manual quarantine upload.
2. **SSRF Whitelist Violations**:
   - *Symptom*: Scraping engine rejects a valid government domain.
   - *Fix*: Register domain in `app/Services/UrlSecurity.php` whitelist.
3. **N+1 Query Exceptions in Development**:
   - *Symptom*: Page throws `LazyLoadingViolationException` in local dev mode.
   - *Fix*: Model lazy loading is disabled in development. Update code to use eager loading (`with([...])`) on query relationships. See [[PERFORMANCE]].

## Tracking Log Exceptions
Use Laravel Pail inside your terminal to view real-time error details (see [[LOGGING]]):
```bash
php artisan pail
```
For failed queues, navigate to the Filament admin site queue section or standard Laravel logs at `storage/logs/laravel.log`.

---
* 🏠 Home: [[Home]] | 📊 Dashboard: [[Dashboard]]
