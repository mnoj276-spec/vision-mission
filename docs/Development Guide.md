---
title: "Development Guide"
aliases: ["Coding Conventions", "Developer Manual"]
tags: ["development", "conventions", "code"]
created: 2026-07-05
updated: 2026-07-05
---

# Development Conventions & Standards 💻

All developers and AI agents must follow these guidelines to keep the codebase clean, maintainable, and backwards compatible.

## Core Architectural Rules
1. **Domain-Driven Hybrid Structure**:
   Organize logic into domains under `app/Domains/`. See [[MODULES]] for the existing 6 domain folders.
2. **Repository Pattern (Dependency Inversion)**:
   Controllers must interact with Service classes or Repositories, never directly with Eloquent models.
3. **No-Touch Security Protocols**:
   Never modify auth guards (JWT), URL routing parameters, or HTML sanitizers without security approval. See [[SECURITY]] and [[AI_CONTEXT]].

## Writing Code Checklist
* **Controllers**: Use dependency injection inside the constructors. Return unified structures for APIs (see [[API]]).
* **Models**: Document all relationships and query scopes inside [[MODELS]]. Ensure soft-deletion support is included.
* **Services**: Put complex workflows and third-party API integration code inside Domain Services (see [[SERVICES]]).
* **Routes**: Place routes in correct file: `web.php`, `api.php`, `admin.php`, or `scraper.php`. See [[ROUTES]].

## AI Agent Integration Protocols
For details on how AI agents must analyze and execute changes safely without breaking tests, refer to [[AI_RULES]] and [[README_AI]]. Always run `php artisan test` before committing.

---
* 🏠 Home: [[Home]] | 📊 Dashboard: [[Dashboard]]
