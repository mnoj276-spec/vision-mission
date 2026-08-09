---
title: "Testing Framework & Regression Checklist"
aliases: ["Testing Framework","Testing Checklist"]
tags: ["testing","phpunit","qa","existing"]
created: 2024-01-05
updated: 2026-07-05
---

# Testing Framework & Regression Checklist

`vision-mission` contains 26 feature test suites checking scraping workflows, deduplication blocks, document parsers, database performance, and security hardening.

---

## 1. Testing Commands

To execute the full testing suite:
```bash
php artisan test
```
To run a specific test suite file:
```bash
php artisan test --filter=SecurityHardeningTest
```

---

## 2. Test Suites Directory

### A. Scraper & Deduplication Gates
* **`ScraperTest`** & **`ScraperRedesignTest`**: Tests the adaptive crawler frequency updates, validation parameters, and quarantine logs.
* **`ScraperDriverManagerTest`** & **`HybridScraperArchitectureTest`**: Asserts driver matches (UPSC, Banking, State PSC, etc.) and JS rendering fallbacks.

### B. Document Parsers & OCR Engines
* **`EnterpriseDocumentParserTest`**: Asserts text parsing yields from CSV, HTML, Word/Excel, and machine-readable PDFs.
* **`HybridOcrTest`**: Tests engine transitions (Tesseract, PaddleOCR, Gemini/OpenAI OCR) on image notifications.
* **`NotificationExtractionTest`** & **`NotificationClassifierTest`**: Verifies LLM-powered structuring pipelines and taxonomy classifications.

### C. Lifecycle State Machine
* **`RecruitmentLifecycleTrackingTest`**: Asserts that parent posts transition correctly (e.g. `published` $\to$ `admit_card_released`) when child notices are processed.
* **`ResultDetectionEngineTest`**: Asserts scheduled result matches.

### D. Security & Hardening
* **`SecurityHardeningTest`**: Asserts SSRF blocks on private/reserved IP blocks, ClamAV antivirus scans, and Stored XSS filter cleanups.

### E. API & Telemetry
* **`RestApiArchitectureTest`**: Verifies stateless JWT guard handshakes and REST route responses.
* **`AnalyticsTelemetryTest`** & **`MonetizationTelemetryTest`**: Asserts click events, view counters, and affiliate redirection logs.

---

## 3. Regression Checklist

Before submitting code edits, verify:
- [ ] Model lazy loading is disabled and does not throw `LazyLoadingViolationException` in tests.
- [ ] No changes are made to the database schema that break backward compatibility.
- [ ] All custom middleware handlers (`admin`, `permission`, `active`, `feature`) execute correctly.
- [ ] No HTML tags or event attributes bypass the `HtmlSanitizer` utility.
- [ ] SSRF block validations reject private IP ranges when executing cURL crawls.
- [ ] Email tracking tokens compile and capture pixel views.

---
## Related Notes
* **MOC Connections**: [[Testing MOC]]
* **Navigation**:
  * ⬅️ Prev: [[PERFORMANCE]]
  * ➡️ Next: [[CONFIGURATION]]
  * 🏠 Home: [[Home]] | 📊 Dashboard: [[Dashboard]]
