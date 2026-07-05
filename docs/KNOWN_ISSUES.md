---
title: "Known Issues & Technical Debt"
aliases: ["Known Issues","Technical Debt"]
tags: ["troubleshooting","bugs","debt","existing"]
created: 2024-01-05
updated: 2026-07-05
---

# Known Issues & Technical Debt

This document tracks system constraints, scraper dependencies, and performance/concurrency bottlenecks.

---

## 1. Scraper Breakages (External Site Alterations)

* **Problem**: Government websites frequently alter their HTML layouts, which can cause CSS selector paths mapped in `scraping_sources` to fail.
* **Resolution**: If selectors fail, the source is automatically quarantined. Administrators must adjust the JSON selectors configuration inside the admin settings panel.

---

## 2. LLM API Timeouts & OCR Bottlenecks

* **Problem**: Document parsing pipelines that process heavy scanned PDFs and call external OCR engines (Tesseract/PaddleOCR) or LLM endpoints (Gemini/OpenAI) can time out.
* **Mitigation**: Jobs are run on the `scrapers` background queue with custom timeout boundaries and automatic retries to prevent blocking the web thread.

---

## 3. Strict N+1 Query Exceptions in Development

* **Problem**: Because lazy loading is disabled in development and testing environments, referencing un-loaded relations throws a `LazyLoadingViolationException`.
* **Mitigation**: Developers must explicitly use eager loading (`with()`) for nested relationships.

---

## 4. Crawl Limits & SSRF False Positives

* **Problem**: Strict DNS checking in `UrlSecurity` may block valid government portals if they use non-standard IP configurations, private network mappings, or subdomains that fail lookup checks.
* **Mitigation**: Approved domains should be explicitly whitelisted inside the `approvedDomains` array in [UrlSecurity.php](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Services/UrlSecurity.php).

---
## Related Notes
* **MOC Connections**: [[Business MOC]]
* **Navigation**:
  * ⬅️ Prev: [[UI_COMPONENTS]]
  * ➡️ Next: [[TODO]]
  * 🏠 Home: [[Home]] | 📊 Dashboard: [[Dashboard]]
