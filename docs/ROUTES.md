# Routing Map Reference

`vision-mission` divides its HTTP routes into five files under the `routes` directory, segregating public, REST, admin, scraper, and scheduled commands.

---

## 1. Web Routing ([routes/web.php](file:///C:/xampp_8.2.12/htdocs/vision-mission/routes/web.php))
Serves the public front-end and candidate dashboard actions.

### Public Landing & Search Paths
* `GET /` & `/jobs` $\to$ `JobController@index` (Home page listing)
* `GET /search` $\to$ `SearchController@search` (Typo-tolerant searches)
* `GET /search/{filter}/{slug}` $\to$ `SearchController@<type>Search` (State, category, etc.)
* `GET /eligibility-checker` $\to$ `EligibilityController@index`
* `GET /salary-information` $\to$ `SalaryInfoController@index`

### Programmatic SEO Landers (Predefined structural layouts)
* `GET /results`, `/admit-cards`, `/answer-keys`, `/syllabus`, etc. $\to$ `ProgrammaticSeoController`
* `GET /jobs/state/{state_slug}/{district_slug?}` $\to$ `ProgrammaticSeoController`
* `GET /job/{slug}` (and `/result/{slug}`, `/admit-card/{slug}`) $\to$ `ProgrammaticSeoController@showJob` (Protected by `internal_linking` middleware)

### Tracking & Monetization Redirects
* `GET /go/{slug}` $\to$ `MonetizationController@redirectAffiliate`
* `GET /email/track/open/{token}` $\to$ `EmailTrackingController@trackOpen`
* `GET /email/track/click/{token}` $\to$ `EmailTrackingController@trackClick`

### Stateful Front-End APIs (`/api` prefix, Session & CSRF protected)
* `POST /api/register`, `/api/login`, `/api/logout` $\to$ `AuthController`
* `GET /api/dashboard` $\to$ `DashboardController@getDashboardData` (Requires `auth`, `active`)
* `POST /api/jobs/{id}/bookmark` $\to$ `ApplicationController@toggleBookmark` (Requires `auth`, `active`)
* `POST /api/jobs/{id}/apply` $\to$ `ApplicationController@applyJob` (Requires `auth`, `active`)

---

## 2. Stateless REST API Routing ([routes/api.php](file:///C:/xampp_8.2.12/htdocs/vision-mission/routes/api.php))
Prefix: `/api/v1` (Versioned, tokenized, and throttled REST endpoints for mobile or external clients).
* **Auth paths** (Throttled to 5 reqs/min per IP): `/register`, `/login`, `/refresh`, `/logout`, `/forgot-password`, `/reset-password` $\to$ `V1/Auth/AuthController`.
* **Public search paths** (Throttled to 60 reqs/min): `/jobs`, `/jobs/{slug}`, `/search/autocomplete` $\to$ `V1` Jobs & Search controllers.
* **Extraction pipeline** (Requires `auth:api` & `admin`): `/extraction/upload`, `/extraction/status/{id}`, `/extraction/approve/{id}` $\to$ `V1/Extraction/ExtractionController`.
* **Candidate actions** (Requires `auth:api`, `active`): `/dashboard`, `/profile/update`, `/jobs/{id}/bookmark` $\to$ `V1` Users & Jobs controllers.

---

## 3. Administrative Panel Routing ([routes/admin.php](file:///C:/xampp_8.2.12/htdocs/vision-mission/routes/admin.php))
Prefix: `/api/admin` (Protected by `auth` session + `admin` middleware).
* **Dashboard analytics**: `/dashboard`, `/data`, `/analytics/metrics`.
* **Dynamic settings module**: `/settings`, `/settings/general`, `/settings/logo`, `/settings/email`, `/settings/backups/*`.
* **Queue monitor**: `/queues/metrics`, `/queues/failed`, `/queues/failed/{uuid}/retry`.
* **Master tables CRUD**: `/categories`, `/departments`, `/states`, `/qualifications`.
* **Recruitment reviews**: `/jobs/store`, `/jobs/{id}` (Update/Destroy), `/ai-contents/{id}/approve`.

---

## 4. Scraping Operations Routing ([routes/scraper.php](file:///C:/xampp_8.2.12/htdocs/vision-mission/routes/scraper.php))
Prefix: `/api/admin` (Protected by `auth`, `admin` and `permission:create_jobs`).
* **Source management**: `GET/POST/DELETE /scrapers` $\to$ `ScraperController`.
* **Actions**: `/scraper/{id}/toggle`, `/scraper/{id}/run`, `/quarantine/{id}/rescue`.

---

## 5. Console Routings ([routes/console.php](file:///C:/xampp_8.2.12/htdocs/vision-mission/routes/console.php))
Registers commands and cron definitions:
* **Cron executions**: Runs `scraper:run` every 5 mins, `scraper:detect-results` every 10 mins, daily/hourly mailers, features expirations.
