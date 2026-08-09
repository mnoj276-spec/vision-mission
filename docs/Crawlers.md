---
title: "Crawlers"
aliases: ["Government Scrapers", "OCR Parsing"]
tags: ["crawlers", "scraping", "ocr", "deduplication"]
created: 2026-07-05
updated: 2026-07-05
---

# Crawlers & Document Extraction Pipeline 🕷️

The core ingestion engine crawls government portals, downloads notifications, runs OCR, and extracts structured data.

## Scraper Execution Pipeline
Below is the full data processing workflow for crawler ingestion:

```mermaid
flowchart TD
    Start[Run Scraper] --> Fetch[Download HTML/Attachments]
    Fetch --> FileCheck{Is PDF/Doc?}
    FileCheck -->|Yes| Parse[smalot/pdfparser / PhpSpreadsheet]
    FileCheck -->|No| CleanText[Sanitize HTML content]
    Parse --> OCR{Need OCR?}
    OCR -->|Yes| OcrProcess[OcrManager: EasyOcr / PaddleOcr / Gemini]
    OCR -->|No| Extracted[Raw text gathered]
    CleanText --> Extracted
    OcrProcess --> Extracted
    Extracted --> AI[Gemini / OpenAI API Structuring]
    AI --> Gate1{Gate 1: SHA256 duplicate?}
    Gate1 -->|Yes| Drop[Drop & Log Duplicate]
    Gate1 -->|No| Gate2{Gate 2: Fuzzy similarity >= 85%?}
    Gate2 -->|Yes| Drop
    Gate2 -->|No| Gate3{Gate 3: Title Variant match?}
    Gate3 -->|Yes| Drop
    Gate3 -->|No| Save[Save Unique JobPost]
    Save --> Lifecycle[RecruitmentLifecycleManager]
```

## Domain Scraper Modules
For technical layout, see [[MODULES#2 Scrapers Module]] and [[SERVICES#Scrapers Domain]].
- **ScraperController**: Gated admin endpoints to run, toggle, or inspect crawls.
- **ScrapingService**: Orchestration of crawling tasks.
- **FingerprintService**: Computes deduplication hash validations.

## Mitigations & Technical Debt
See [[KNOWN_ISSUES]] for details on handling:
- Crawler Timeout bottlenecks.
- Government website HTML structure modifications.
- LLM API timeout limits.

---
* 🏠 Home: [[Home]] | 📊 Dashboard: [[Dashboard]] | 🗺️ Crawler MOC: [[Crawler MOC]]
