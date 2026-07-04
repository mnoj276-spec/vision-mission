# Roadmap & Project TODO List

This document lists immediate refactoring goals and future optimization tasks for the `vision-mission` portal.

---

## 1. Immediate Next Steps & Refactorings

- [ ] **Deduplication Hardening**: Add additional checks for acronyms and spelling variations in the fuzzy match gate.
- [ ] **OCR Engine Fallbacks**: Benchmark and optimize CPU utilization on local Tesseract/PaddleOCR runs under heavy concurrent uploads.
- [ ] **Settings Cache Warming**: Implement long-lived caching for General Settings config arrays to avoid database reads on public page hits.
- [ ] **Internal Link Parser Upgrades**: Optimize internal linking word sweeps to ignore stop words and common nouns.

---

## 2. Long-Term Enhancements

- [ ] **IndexNow Direct Webhooks**: Trigger IndexNow notifications immediately on manual admin approvals instead of relying on the default queue delay.
- [ ] **PWA Offline Synchronization**: Allow candidates to draft resume applications offline and queue them for auto-submission once a network connection is restored.
- [ ] **Analytics Data Aggregations**: Implement scheduled daily aggregation routines to compile page views and click records, preventing table sizes from growing too large.
