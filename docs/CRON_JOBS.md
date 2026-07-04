# Cron Jobs & Task Scheduler Reference

`vision-mission` relies on Laravel's Task Scheduler to coordinate periodic events, scraping iterations, email drips, and system maintenance.

---

## 1. Cron Execution Configuration

To run the scheduler in production, a single cron job must run on the server:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 2. Active Scheduled Commands Registry

Registered inside [routes/console.php](file:///C:/xampp_8.2.12/htdocs/vision-mission/routes/console.php):

| Schedule Command | Frequency | Concurrency Constraints | Purpose |
| :--- | :--- | :--- | :--- |
| `scraper:run` | Every 5 minutes | `withoutOverlapping(10)`<br>`onOneServer()` | Executes crawling cycles for active sources whose `next_run_at` is in the past. |
| `scraper:detect-results` | Every 10 minutes | `withoutOverlapping(10)`<br>`onOneServer()` | Scans active recruitment timelines to match and flag declared exam results. |
| `email:welcome-series-scheduler` | Daily | `withoutOverlapping(10)`<br>`onOneServer()` | Enrolls new users into the 3-part onboarding welcome email drip. |
| `email:send-alerts` | Hourly | `withoutOverlapping(10)`<br>`onOneServer()` | Compiles newly published jobs and triggers email alerts matching candidates' preferences. |
| `email:send-weekly-digest` | Weekly (Monday 09:00) | `withoutOverlapping(10)`<br>`onOneServer()` | Sends a digest summary of all vacancies published during the week. |
| `email:send-reengagement` | Daily | `withoutOverlapping(10)`<br>`onOneServer()` | Emails inactive candidate users to prompt profile updates or search checkins. |
| `monetization:expire-features` | Daily | `withoutOverlapping(10)`<br>`onOneServer()` | Checks premium subscriber statuses and reverts expired accounts to free tiers. |

---

## 3. Utility Console Commands

These are run manually or during setup operations (not registered on periodic schedule intervals):

* **`seo:warm-internal-links`** (class: `WarmInternalLinksCache`): Rebuilds cache maps linking state, category, and qualification slugs inside job description fields.
* **`search:build-vocabulary`** (class: `BuildSearchVocabularyCommand`): Compiles unique search phrase lists to facilitate front-end autocomplete.
* **`ocr:benchmark`** (class: `BenchmarkOcrCommand`): Analyzes speed and accuracy rates across Tesseract, PaddleOCR, and Gemini/OpenAI engines.
