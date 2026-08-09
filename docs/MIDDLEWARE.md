---
title: "Middleware Stack Reference"
aliases: ["Middleware","Middleware Stack"]
tags: ["backend","middleware","security","existing"]
created: 2024-01-05
updated: 2026-07-05
---

# Middleware Stack Reference

This document maps custom and framework middlewares, execution orders, and target route groups.

---

## 1. Middleware Aliases & Mappings

Configured in [bootstrap/app.php](file:///C:/xampp_8.2.12/htdocs/vision-mission/bootstrap/app.php):

| Alias | Middleware Class | Target Responsibility |
| :--- | :--- | :--- |
| `admin` | [EnsureAdmin](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Http/Middleware/EnsureAdmin.php) | Validates that user original role is `admin` or triggers Spatie gate `admin-access`. |
| `active` | [EnsureActiveUser](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Http/Middleware/EnsureActiveUser.php) | Asserts that user `is_active` attribute is true. |
| `role` | [RoleMiddleware](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Http/Middleware/RoleMiddleware.php) | Restricts access based on Spatie roles. |
| `permission` | [CheckPermission](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Http/Middleware/CheckPermission.php) | Restricts access based on Spatie permissions. |
| `internal_linking` | [InternalLinkingHeaders](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Http/Middleware/InternalLinkingHeaders.php) | Adds crawler-specific HTTP caching headers and X-Robots tag values on crawl paths. |
| `feature` | [EnsureFeatureEnabled](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Http/Middleware/EnsureFeatureEnabled.php) | Checks dynamic database settings to toggle modules (e.g. `marketing`, `analytics`). |

---

## 2. Route Groups & Execution Order

### A. Web Page Group (`routes/web.php` & `routes/admin.php`)
1. [DynamicMaintenanceMode](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Http/Middleware/DynamicMaintenanceMode.php): Checks database flags. If maintenance mode is active, intercepts and serves 503 pages.
2. Standard Sessions: Encrypts cookies, starts sessions, validates CSRF.
3. Auth Filters: `auth` (Checks standard session login).
4. Role/Permission Checks: `admin`, `permission:xyz`, `feature:xyz`.

### B. Stateful API Group (Web prefix `/api`)
1. [DynamicMaintenanceMode](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Http/Middleware/DynamicMaintenanceMode.php)
2. Standard Session & CSRF guards.
3. Candidate Verification: `auth`, `active` (Ensures authenticated session + active user).

### C. Stateless REST API Version 1.0 (`routes/api.php` prefix `/api/v1`)
1. [DynamicMaintenanceMode](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Http/Middleware/DynamicMaintenanceMode.php)
2. Throttling:
   * Auth paths: `throttle:api.auth` (Capped at 5 attempts/minute).
   * Data paths: `throttle:api` (Capped at 60 requests/minute).
3. JWT Authentication: `auth:api` (Triggers custom `jwt` request guard defined in AppServiceProvider).
4. Candidate Validation: `active`.

### D. Crawl-Friendly SEO Detail Detail Paths
1. [InternalLinkingHeaders](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Http/Middleware/InternalLinkingHeaders.php): Runs on `/job/{slug}`, `/result/{slug}`, etc., appending index/noindex markers and cache headers.

---
## Related Notes
* **MOC Connections**: [[Backend MOC]], [[Security MOC]], [[API MOC]], [[SEO MOC]]
* **Navigation**:
  * ⬅️ Prev: [[MODELS]]
  * ➡️ Next: [[ROUTES]]
  * 🏠 Home: [[Home]] | 📊 Dashboard: [[Dashboard]]
