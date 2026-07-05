---
title: "AI Coding Rules & Constraints"
aliases: ["AI Coding Rules","Rules"]
tags: ["guidelines","ai","rules","existing"]
created: 2024-01-05
updated: 2026-07-05
---

# AI Coding Rules & Constraints

Future AI assistants working on this codebase **must** adhere strictly to the following rules. Failure to do so will result in broken integrations, regressions in SEO ranking, or query execution exceptions.

---

## 1. Directory Scans & File Reading

* **No recursive repository scans**: Never perform recursive directory scans (`dir -Recurse`, `find .`, etc.) or read large quantities of source files unless explicitly requested by the user.
* **Targeted investigation**: Read only files related to the requested task. Use the domain mapping in [MODULES.md](file:///C:/xampp_8.2.12/htdocs/vision-mission/docs/MODULES.md) to locate relevant classes.

---

## 2. Routing & URL Protocols

* **No unnecessary route renaming**: Do not alter route names or URI paths. The existing JS/Blade front-end and dynamic crawlers rely on exact named routes (e.g. `admin.dashboard.api`, `seo.job_detail`).
* **Preserve URL structure**: The programmatic SEO structures (such as `/job/{slug}`, `/jobs/state/{state_slug}`, `/go/{slug}`) are indexed by Google and IndexNow. Do not alter slugs, parameters, or base structures.

---

## 3. Database Schema & Migration Operations

* **Schema alterations**: Never modify migrations or alter table structures without explicit approval.
* **Backward compatibility**: Any new schema updates must be backward-compatible (non-destructive inserts, nullable fields for new columns).
* **Constraints**: Do not drop or modify indexes (`uq_job_posts_fingerprint` or indexes in `add_monetization_fields_to_tables`) without review.

---

## 4. Authentication, Authorization & Security

* **Do not modify guards**: The JWT request guard is custom-coded in [AppServiceProvider.php](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Providers/AppServiceProvider.php). Do not replace it with default guards or modify session scopes unless instructed.
* **Preserve security layers**: Do not bypass `UrlSecurity::isSafeUrl()` checks (SSRF protection) or `HtmlSanitizer` calls (XSS protection) for scraped items.
* **Keep middleware validation**: Do not remove or bypass middleware checks (`admin`, `permission`, `active`, `throttle`).

---

## 5. SEO & Performance Constraints

* **No removal of SEO features**: Never remove internal linking tracking, sitemap generation routes, Schema.org structural metadata outputs, or the IndexNow submission trigger.
* **N+1 Prevention**: Keep lazy loading disabled (`Model::preventLazyLoading(!isProduction())`) in non-production environments to flag performance issues immediately. Eager load relations.

---

## 6. Coding Practices

* **Always explain modifications**: When editing a file, explain the change, its design rationale, and its security implications.
* **Follow Repository Pattern**: Always write Eloquent database queries inside the Repository classes, not inside controllers or command scripts. Use bindings in AppServiceProvider.
* **Document changes in the log**: When completing a task, append a log entry to [CHANGELOG_AI.md](file:///C:/xampp_8.2.12/htdocs/vision-mission/docs/CHANGELOG_AI.md).

---
## Related Notes
* **MOC Connections**: [[DevOps MOC]]
* **Navigation**:
  * ⬅️ Prev: [[AI_CONTEXT]]
  * ➡️ Next: [[ARCHITECTURE]]
  * 🏠 Home: [[Home]] | 📊 Dashboard: [[Dashboard]]
