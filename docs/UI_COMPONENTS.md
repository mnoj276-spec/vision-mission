# UI Components & Frontend Interface Reference

`vision-mission` implements a Progressive Web App (PWA) layout powered by Blade templates, Vite compiler setups, and modular front-end interfaces.

---

## 1. Frontend Layout Structure

The app runs on a **Single Page Application (SPA) shell** concept:
* The main landing page is rendered via the `home` route using a public Blade layout template.
* Subsequent search queries, eligibility checks, and dashboard telemetry are updated using AJAX queries back to stateful API controllers.
* Assets are compiled and optimized using Vite.

---

## 2. Key Interactive Elements & Views

### A. Advanced Search & Autocomplete Widgets
* **Location**: Public search forms.
* **Telemetry**: Sends inputs to autocomplete API routes `/api/search/autocomplete` for fast keyword queries.
* **Typo Correction**: Displays did-you-mean prompts if search inputs fail to match the parsed vocabulary database.

### B. Eligibility matching Wizard
* **Location**: `/eligibility-checker`
* **Flow**: Multi-step wizard collecting candidate age, category (caste), and qualifications. Triggers AJAX validations back to `EligibilityController@check`.

### C. Candidate dashboard Dashboard
* **Location**: Web paths `/api/dashboard` (when logged in).
* **Widgets**: bookmark cards list, application tracker grid, preferences manager (state/category alert triggers).

---

## 3. PWA Integration & Offline Cache Architecture

* **Service Worker**: Caches main CSS stylesheet bundles, layout images, and core JS scripts.
* **Offline Fallback Route**: `/offline` maps to `JobController@offline` rendering a PWA fallback view.
* **Offline Action**: If users lose internet connectivity while browsing, the Service Worker intercepts routing calls and serves this static view, allowing them to search local caches.

---

## 4. Telemetry Telemetry Hooks

Frontend components are instrumented to collect user interaction data without violating privacy:
* **Page View Tracks**: Dispatches posts to `/api/analytics/page-view` on route changes.
* **Ad Click Tracks**: Intercepts sponsor links to log revenue hits to `/api/analytics/ad-event` before redirect runs.
* **Affiliate Redirects**: Converts outbound job apply links to local `/go/{slug}` redirect targets, updating logs on the fly.
