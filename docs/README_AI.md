# AI Assistant Documentation Manual

Welcome! This documentation folder is designed to give you high-fidelity architectural context with minimal token consumption. 

Follow the guide below to understand and navigate this codebase efficiently.

---

## 1. Reading Protocol (Order of Operations)

When onboarding to a task in this repository:
1. **Always read [AI_CONTEXT.md](file:///C:/xampp_8.2.12/htdocs/vision-mission/docs/AI_CONTEXT.md) first**. It contains naming rules, active business processes, coding standards, and constraints.
2. **Next, consult [AI_RULES.md](file:///C:/xampp_8.2.12/htdocs/vision-mission/docs/AI_RULES.md)** to prevent breaking changes, database mismatches, or security vulnerabilities.
3. **For architectural context**, refer to [ARCHITECTURE.md](file:///C:/xampp_8.2.12/htdocs/vision-mission/docs/ARCHITECTURE.md) and [MODULES.md](file:///C:/xampp_8.2.12/htdocs/vision-mission/docs/MODULES.md) to locate which Domain namespace owns the logic.
4. **For specifics on individual layers**, consult:
   * Models: [MODELS.md](file:///C:/xampp_8.2.12/htdocs/vision-mission/docs/MODELS.md)
   * Routing & Web endpoints: [ROUTES.md](file:///C:/xampp_8.2.12/htdocs/vision-mission/docs/ROUTES.md) & [API.md](file:///C:/xampp_8.2.12/htdocs/vision-mission/docs/API.md)
   * Database relationships: [DATABASE.md](file:///C:/xampp_8.2.12/htdocs/vision-mission/docs/DATABASE.md)
   * Scheduled tasks: [CRON_JOBS.md](file:///C:/xampp_8.2.12/htdocs/vision-mission/docs/CRON_JOBS.md)

---

## 2. Recommended AI Workflow

1. **Research Phase (No modifications)**:
   * Match the task description with the appropriate domain modules in [MODULES.md](file:///C:/xampp_8.2.12/htdocs/vision-mission/docs/MODULES.md).
   * Review related interfaces in the repository or service layer.
   * Do not read entire folders or run full codebase scans. Check only the relevant domain namespaces.
2. **Implementation Plan**:
   * Map dependencies and write down file edits.
   * Review against rules in [AI_RULES.md](file:///C:/xampp_8.2.12/htdocs/vision-mission/docs/AI_RULES.md).
3. **Execution**:
   * Edit contiguous line ranges where possible.
   * If writing queries, use bounded scope filters.
4. **Verification**:
   * Verify changes using: `php artisan test`

---

## 3. How to Minimize Token Consumption

* **Do not read raw source code to answer conceptual questions**: Rely on [ARCHITECTURE.md](file:///C:/xampp_8.2.12/htdocs/vision-mission/docs/ARCHITECTURE.md), [SERVICES.md](file:///C:/xampp_8.2.12/htdocs/vision-mission/docs/SERVICES.md), and [MODELS.md](file:///C:/xampp_8.2.12/htdocs/vision-mission/docs/MODELS.md). They list model attributes, class responsibilities, and routing layers.
* **Scan targeted directories**: Avoid recursive directory listings. Use specific absolute paths mapping to the target Domain folder.
* **Write concise summaries**: Do not copy/paste large files or methods into the chat context. Summarize changes in short diffs.
