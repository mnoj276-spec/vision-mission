# REST API Reference (v1.0)

`vision-mission` exposes a mobile-ready, stateless REST API version 1.0. All endpoints reside under the `/api/v1` prefix.

---

## 1. Authentication Protocol & JWT Guard

The API uses stateless **JWT Bearer Token** authentication. 
* Tokens are transmitted in the HTTP headers:
  ```http
  Authorization: Bearer <your_jwt_token>
  ```
* **Guard Lifecycle**: Managed by the custom `jwt` request guard defined in [AppServiceProvider.php](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Providers/AppServiceProvider.php). It parses the token, extracts the subject claim (`sub` field matching user ID), verifies user active state (`is_active`), and returns the authenticated user object.

---

## 2. Rate Limiting & Throttling Rules

* **Auth Endpoints Group**: Capped at **5 requests per minute per IP** (`throttle:api.auth`). Applies to logins, registrations, password resets, and token refreshes.
* **Core & Data Endpoints Group**: Capped at **60 requests per minute** per user or IP (`throttle:api`). Applies to searches, profile updates, and detail fetches.

---

## 3. Version 1.0 Endpoint Matrix

### A. Authentication & Lifecycle
| HTTP Method | Route | Description | Middleware |
| :--- | :--- | :--- | :--- |
| `POST` | `/register` | Candidate registration | `throttle:api.auth` |
| `POST` | `/login` | Returns JWT and refresh tokens | `throttle:api.auth` |
| `POST` | `/refresh` | Generates a new JWT using refresh token | `throttle:api.auth` |
| `POST` | `/logout` | Blacklists/revokes active tokens | `throttle:api.auth` |

### B. Public Inquiries & Search
| HTTP Method | Route | Description | Middleware |
| :--- | :--- | :--- | :--- |
| `GET` | `/jobs` | Paginated job postings, filters | `throttle:api` |
| `GET` | `/jobs/{slug}` | Detail card fields & timeline updates | `throttle:api` |
| `GET` | `/jobs/{id}/timeline` | Chronological list of linked sub-notices | `throttle:api` |
| `GET` | `/search/autocomplete`| Matches prefix and suggests keywords | `throttle:api` |
| `GET` | `/search/typo` | Evaluates term and returns auto-correction | `throttle:api` |

### C. Candidate Actions
| HTTP Method | Route | Description | Middleware |
| :--- | :--- | :--- | :--- |
| `GET` | `/dashboard` | Returns application counts & bookmarks | `auth:api`, `active`, `throttle:api` |
| `POST` | `/profile/update` | Updates resume and general details | `auth:api`, `active`, `throttle:api` |
| `POST` | `/jobs/{id}/bookmark` | Toggles bookmarks | `auth:api`, `active`, `throttle:api` |
| `POST` | `/jobs/{id}/apply` | Submits application for review | `auth:api`, `active`, `throttle:api` |

### D. Administrative Document Ingestion
| HTTP Method | Route | Description | Middleware |
| :--- | :--- | :--- | :--- |
| `POST` | `/extraction/upload` | Upload PDF/image notification to extract fields | `auth:api`, `admin`, `throttle:api` |
| `GET` | `/extraction/status/{id}` | Poll progress of OCR/AI formatting task | `auth:api`, `admin`, `throttle:api` |
| `POST` | `/extraction/approve/{id}`| Merge extracted data into `job_posts` | `auth:api`, `admin`, `throttle:api` |

---

## 4. Standard Response Envelope

All API routes return a unified JSON response envelope:
```json
{
  "success": true,
  "message": "Resource retrieved successfully.",
  "data": {
    "items": []
  },
  "errors": null,
  "meta": {
    "timestamp": 1783159200,
    "api_version": "v1"
  }
}
```
Validation exceptions (422) return structured maps inside the `errors` object (see [ERROR_HANDLING.md](file:///C:/xampp_8.2.12/htdocs/vision-mission/docs/ERROR_HANDLING.md) for details).
