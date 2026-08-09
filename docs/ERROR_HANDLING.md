---
title: "Exception & Error Handling Protocols"
aliases: ["Error Handling","Exception Handling"]
tags: ["backend","exceptions","error-handling","existing"]
created: 2024-01-05
updated: 2026-07-05
---

# Exception & Error Handling Protocols

`vision-mission` implements a unified API exception mapping system configured inside `bootstrap/app.php` to prevent raw server failures from leaking to clients.

---

## 1. Unified API Exception Renderer

Whenever an incoming request targets routes starting with `/api` or specifies `Accept: application/json` in its headers, the global exceptions engine intercepts errors and maps them to a consistent JSON response layout:

```json
{
  "success": false,
  "message": "<User friendly error summary>",
  "data": null,
  "errors": "<Specific error map or null>",
  "meta": {
    "timestamp": 1783159200,
    "api_version": "v1"
  }
}
```

---

## 2. Exception Mappings & HTTP Status Codes

### A. Validation Failures (HTTP 422)
* **Exception**: `Illuminate\Validation\ValidationException`
* **Response**:
  * `message`: `"Validation failed."`
  * `errors`: Nested key-value dictionary listing fields and validation rules violated (e.g. `{"email": ["The email format is invalid."]}`).

### B. Resources Not Found (HTTP 404)
* **Exception**: `ModelNotFoundException` or `NotFoundHttpException`
* **Response**:
  * `message`: `"Resource not found."`
  * `errors`: `null`

### C. Authentication Failures (HTTP 401)
* **Exception**: `AuthenticationException`
* **Response**:
  * `message`: `"Unauthorized or invalid token."`
  * `errors`: `null`

### D. Rate Limit Violations (HTTP 429)
* **Exception**: `ThrottleRequestsException`
* **Response**:
  * `message`: `"Too many requests. Please slow down."`
  * `errors`: `null`

### E. Server Failures (HTTP 500)
* **Exception**: Any unhandled backend `Throwable`.
* **Security Behavior**:
  * In **Production** (`APP_DEBUG=false`): Returns a generic `message` `"Internal Server Error."` to prevent stack trace leaks.
  * In **Local/Development** (`APP_DEBUG=true`): Returns the specific exception message, file name, line location, and trace stack arrays under the `errors` payload.

---
## Related Notes
* **MOC Connections**: [[Backend MOC]]
* **Navigation**:
  * ⬅️ Prev: [[LOGGING]]
  * ➡️ Next: [[PERFORMANCE]]
  * 🏠 Home: [[Home]] | 📊 Dashboard: [[Dashboard]]
