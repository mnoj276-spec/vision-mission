---
title: "Services & Business Logic Registry"
aliases: ["Services","Business Logic"]
tags: ["backend","services","logic","existing"]
created: 2024-01-05
updated: 2026-07-05
---

# Services & Business Logic Registry

This file documents the service classes in `vision-mission`, describing their responsibilities, dependencies, and callers.

---

## 1. Domain Services (`app/Domains/*/Services/`)

### Jobs Domain
* **JobService**: Standard business layer managing job CRUD. Injected in `JobController`.
* **SearchService**: Executes fulltext matching, filters, autocomplete keywords, and typo correction fallback algorithms. Injected in `SearchController`.
* **SeoService**: Assembles meta tags, canonical routes, H1 content, breadcrumbs, and Google schemas. Injected in `SitemapController` and `ProgrammaticSeoController`.
* **SchemaService**: Generates structured JSON-LD format markup. Used by `SeoService`.
* **InternalLinkingService**: Programmatically searches job description texts for key vocabularies (e.g. states, categories) and injects links to warm crawl pathways. Warmed by `WarmInternalLinksCache` console command.
* **RecruitmentLifecycleManager**: Transition state engine that links incoming sub-notices to their parent job post and alters parent status. Called by `ScrapingService` and `ExtractionController`.
* **IndexNowService**: Formats URL directories and submits them to IndexNow endpoints for instant index crawls.

### Scrapers Domain
* **ScrapingService**: Orchestrator executing scraping attempts. Sanitizes text strings, runs deduplication gates, handles low-confidence quarantines, and executes adaptive run-interval updates. Called by `RunScraperCommand`.
* **FingerprintService**: Deduplication logic checking exact fingerprints (SHA-256), fuzzy title matching ($\ge 85\%$), and year/acronym variants.
* **HybridScrapingEngine**: Fetching engine matching HTTP clients (standard, JS-rendered, etc.) to scraping configs.
* **NotificationClassifier**: Classifies raw notice text to determine types (job, result, admit card, answer key, syllabus, notice, admission, scholarship).

### Extraction Domain
* **ExtractionPipeline**: Step-by-step workflow managing document parses, formatting validations, and structuring logs.
* **AiStructuringService**: Coordinates API endpoints for Gemini/OpenAI to transform raw OCR texts into structured JSON.
* **OCRService** & **OcrManager**: Coordinates OCR engines (`EasyOcrEngine`, `PaddleOcrEngine`, `GeminiOcrEngine`, `OpenAiOcrEngine`, `TesseractEngine`) for image-based PDFs.
* **DocumentParserService**: Parses CSV, HTML, PDF (via `pdfparser`), XML, and Word/Excel documents.

### Notifications Domain
* **NotificationService**: Trigger dispatcher managing email digests, welcome drip triggers, and logging tracking tokens. Called by schedulers.

### Admin Domain
* **AdminService**: System log auditing and dashboard widgets aggregation.
* **SettingsService**: Handles database backup generation/restores, menu hierarchies, SMTP test commands, and settings caching.

### Users Domain
* **AuthService**: Manages registration, logins, and recovery.

---

## 2. Common/Global Services (`app/Services/`)

* **AnalyticsService**: Registers pageviews, candidate interaction tags, ad clicks, and aggregates affiliate statistics. Called by monetization and telemetry routes.
* **AntivirusScanner**: SSRF/Malware check validating that uploaded candidate resumes do not contain execution vectors. Injected in resume-accepting endpoints.
* **HtmlSanitizer**: Strips unsafe script tags, event handlers, and styles from scraped HTML strings before they are persisted in `job_posts` (Stored XSS mitigation).
* **JwtService**: Handles stateless token generation, signatures, validation, and expirations. Used by the `jwt` custom request guard.
* **UrlSecurity**: SSRF protection utility checking target URLs to ensure they are on allowed lists and do not resolve to local host loops (127.0.0.1, localhost, private IP networks). Used by scrapers.

---
## Related Notes
* **MOC Connections**: [[Backend MOC]]
* **Navigation**:
  * ⬅️ Prev: [[CONTROLLERS]]
  * ➡️ Next: [[MODELS]]
  * 🏠 Home: [[Home]] | 📊 Dashboard: [[Dashboard]]
