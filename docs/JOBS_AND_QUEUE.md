---
title: "Background Jobs & Horizon Queue Reference"
aliases: ["Background Jobs","Horizon Queue"]
tags: ["automation","queue","jobs","horizon","existing"]
created: 2024-01-05
updated: 2026-07-05
---

# Background Jobs & Horizon Queue Reference

`vision-mission` offloads intensive tasks (scraping, document parsing, AI summarization, email dispatching, and search engine pings) to background queues managed by **Laravel Horizon**.

---

## 1. Queue Infrastructure

* **Driver**: Redis (Production), SQLite/Database or Sync (Testing & Local).
* **Horizon Dashboard**: Accessible at `/horizon` in web browser, restricted to users with the `Super Admin` role via gate filters.
* **Queues & Priority**:
  1. `default`: Standard execution (General alerts, IndexNow submits).
  2. `emails`: Inbound/Outbound mailer actions.
  3. `scrapers`: Ingestion cycles, document parser tasks, OCR conversions.

---

## 2. Global Background Jobs Directory

### [GenerateJobContentJob](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Jobs/GenerateJobContentJob.php)
* **Purpose**: Triggers AI generation for newly inserted job posts. Calls LLM APIs to generate title SEO keywords, detailed salary summaries, and FAQ structured markup blocks.
* **Queue**: `default` (processed asynchronously to keep scraper execution loops fast).
* **Retry Behavior**: Retried up to 3 times on timeout before dropping into failed lists.

### [SendEmailJob](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Jobs/SendEmailJob.php)
* **Purpose**: Formats and dispatches transactional or automated digest emails. Instruments each mailable link with tracking tokens to count opens/clicks.
* **Queue**: `emails`.

### [SubmitToIndexNow](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Jobs/SubmitToIndexNow.php)
* **Purpose**: Submits the URL directory of a newly published post to IndexNow.
* **Queue**: `default`.
* **Failsafe**: Does not impact main thread executions if external IndexNow requests time out or fail.

---

## 3. Dead-Letter Queue (DLQ) Recovery Operations

When background jobs fail (e.g. LLM API timeouts or SMTP service downtime), they are routed to the failed jobs collection.
* **Management Controller**: [QueueManagementController](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Domains/Admin/Controllers/QueueManagementController.php).
* **Admin Actions**:
  * **Fetch Metrics**: Returns lists of jobs currently processing, pending, or failed.
  * **Retry Job**: Retries a specific job by its UUID.
  * **Retry All**: Dispatches a recovery request to retry all pending DLQ items.
  * **Delete / Flush**: Deletes individual failed jobs or flushes the DLQ list.

---
## Related Notes
* **MOC Connections**: [[Automation MOC]], [[DevOps MOC]]
* **Navigation**:
  * ⬅️ Prev: [[CRON_JOBS]]
  * ➡️ Next: [[SEO]]
  * 🏠 Home: [[Home]] | 📊 Dashboard: [[Dashboard]]
