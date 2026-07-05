---
title: "Decision Log"
aliases: ["Architecture Decision Records", "ADR"]
tags: ["adr", "decisions", "architecture"]
created: 2026-07-05
updated: 2026-07-05
---

# Architecture Decision Records (ADR) 📝

This page logs major design and technology choices.

## ADR 1: Domain-Driven Hybrid Layout
- **Status**: Accepted
- **Context**: Standard Laravel layouts lead to bloat when handling complex business modules (like scraper drivers, OCR, and SEO pipelines).
- **Decision**: Put all business logic modules inside domain folders under `app/Domains/` (Admin, Extraction, Jobs, Notifications, Scrapers, Users).
- **Consequences**: Controllers, Services, and Models are grouped by feature rather than layer type. Keeps code clean and highly structured. See [[MODULES]].

## ADR 2: 3-Gate Ingestion Deduplication
- **Status**: Accepted
- **Context**: Government websites repeat notices across regions. We must avoid duplicating entries to protect data quality.
- **Decision**: Enforce a strict 3-stage validation pipeline:
  1. Hash Fingerprint lookup on binary attachments.
  2. Fuzzy String Match on body text (threshold >= 85%).
  3. Title Variant lookup in Database records.
- **Consequences**: Safely drops redundant items. Pre-emptively alerts administrators when unsure. See [[AI_CONTEXT]].

## ADR 3: Multi-Engine OCR Strategy
- **Status**: Accepted
- **Context**: Government notifications are often low-quality scans of paper notices. One OCR engine fails on certain formats.
- **Decision**: Create an `OcrManager` supporting easyOCR, PaddleOCR, Tesseract, Gemini, and OpenAI. Fallback programmatically on engine timeouts.
- **Consequences**: Higher recovery rates, increased resilience. See [[SERVICES#2 Common/Global Services]].

---
* 🏠 Home: [[Home]] | 📊 Dashboard: [[Dashboard]]
