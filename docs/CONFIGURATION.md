---
title: "System Configuration & Environment Variables"
aliases: ["System Configuration","Configuration"]
tags: ["configuration","setup","env","existing"]
created: 2024-01-05
updated: 2026-07-05
---

# System Configuration & Environment Variables

This document maps configuration directories, feature toggles, environment variables, and external service credentials.

---

## 1. Primary Configuration Files

Standard Laravel configurations exist inside the `/config` directory:
* **`config/app.php`**: Core parameters (caching, timezone, encryption keys).
* **`config/horizon.php`**: Horizon background processes, queues, balances, and retry margins.
* **`config/internal_linking.php`**: Configures SEO vocabularies, crawler exclusions, and anchor texts.
* **`config/services.php`**: Dynamic configurations for OpenAI, Google Gemini, IndexNow, and fallback scraping toggles.

---

## 2. Key Environment Variables (`.env`)

### Core Environment
* `APP_ENV`: Environment state (`production`, `local`, `testing`).
* `APP_DEBUG`: Set to `false` in production to prevent stack trace leaks.
* `APP_KEY`: 32-character encryption key.

### DB Connections
* `DB_CONNECTION`: Configured database connection (`mysql` in production, `sqlite` for tests/local development).
* `DB_DATABASE`: SQLite file path or MySQL target database name.

### Queue & Cache Drivers
* `QUEUE_CONNECTION`: Set to `redis` in production to enable Horizon.
* `CACHE_STORE`: Redis or database caching.

### External APIs & Integrations
* `GEMINI_API_KEY`: API key for Google Gemini model, utilized in document extraction and OCR formatting.
* `OPENAI_API_KEY`: Fallback LLM credentials.
* `INDEXNOW_KEY`: 32-character key matching Bing/Yandex IndexNow APIs.

---

## 3. Dynamic Feature Flags & Settings

Features are toggled dynamically through the database configuration model.
* **Toggles**: Checks occur via the `feature` middleware (e.g. `middleware('feature:marketing')`).
* **Feature Keys**:
  * `analytics`: Enables frontend event collection and dashboard graphs.
  * `marketing`: Controls email digest scheduling and re-engagement campaigns.
  * `settings.operations`: Allows CMS page and menu edits.
  * `settings.media`: Enables file uploads and folders management.
  * `settings.security.backups`: Activates backup generation and download options.

---
## Related Notes
* **MOC Connections**: [[Deployment MOC]], [[DevOps MOC]]
* **Navigation**:
  * ⬅️ Prev: [[TESTING]]
  * ➡️ Next: [[DEPENDENCIES]]
  * 🏠 Home: [[Home]] | 📊 Dashboard: [[Dashboard]]
