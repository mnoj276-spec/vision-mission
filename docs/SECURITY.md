# Security Hardening & Protection Protocols

`vision-mission` implements layers of defence against malware, XSS, SSRF, session hijackings, and rate abuses.

---

## 1. Authentication & Authorization Security

* **JWT Stateless Token Subsystem**: REST APIs rely on JWTs signed using custom SHA tokens. The custom request guard in `AppServiceProvider` parses tokens, enforces expiration boundaries, and invalidates claims if the target account's `is_active` status flag is set to false.
* **Role-Based Access Control (RBAC)**: Managed using `spatie/laravel-permission`. 
  * Permissions such as `view_dashboard`, `manage_seo`, `manage_queues`, and `edit_jobs` restrict administrative routes.
  * Legacies: Original database role string value `admin` defaults to super-admin bypass permissions to guarantee backward compatibility.

---

## 2. Inbound Data Sanitization & SSRF Defences

### A. SSRF Mitigation & DNS Verification
* **Component**: [UrlSecurity](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Services/UrlSecurity.php).
* **Execution**: Before any crawler/scraper accesses a source URL, the domain name is resolved to its underlying IP addresses.
* **Checks**:
  * Blocks localhost, private subnet loops (RFC 1918 IPv4 ranges and IPv6 link-locals).
  * Enforces whitelists limiting scrapers to official governmental domains ending in `.gov.in` or `.nic.in` (plus approved exceptions like OpenAI/Google language endpoints).

### B. Stored XSS Protection
* **Component**: [HtmlSanitizer](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Services/HtmlSanitizer.php).
* **Execution**: Crawler fields containing rich-text descriptions are sanitized prior to database persistence.
* **Checks**: Strips inline JavaScript execution markers, event triggers (e.g. `onload=`, `onerror=`), `<script>` blocks, and iframe embedding injections.

### C. File Upload Hardening (Malware Check)
* **Component**: [AntivirusScanner](file:///C:/xampp_8.2.12/htdocs/vision-mission/app/Services/AntivirusScanner.php).
* **Execution**: Candidate resume uploads undergo scanning before storage saves are committed.
* **Checks**: Integrates with local ClamAV daemons or executes content signatures sweeps to check for executable vectors hidden inside PDFs/DOCXs.

---

## 3. SQL Injection & CSRF Protections

* **Query Sanitization**: Database inputs pass through Eloquent's parameterized binding layers. Raw search clauses (`scopeSearch`) trim search terms and clean syntax characters before running boolean matches.
* **CSRF Validation**: Stateful front-end API groups (defined in `routes/web.php` under the `/api` prefix) enforce standard session-token validations on POST/PUT requests.
