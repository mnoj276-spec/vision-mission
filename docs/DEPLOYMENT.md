---
title: "Deployment & Setup Operations Reference"
aliases: ["Deployment Setup","Deployment Operations"]
tags: ["deployment","operations","setup","existing"]
created: 2024-01-05
updated: 2026-07-05
---

# Deployment & Setup Operations Reference

This document maps out the system setup, database provisioning, front-end compilation, and queue runner daemon activation.

---

## 1. Automated Setup Script

`vision-mission` configures a setup command inside `composer.json` to handle onboarding or deployment steps:
```bash
composer run setup
```

### Steps Executed
1. **Packages Ingestion**: Runs `composer install` to download PHP dependencies.
2. **Environment Configuration**: Copies `.env.example` to `.env` if no environment configuration exists.
3. **Application Encryption**: Runs `php artisan key:generate` to bind security keys.
4. **Database Migration**: Runs `php artisan migrate --force` to execute table creations on production targets.
5. **Frontend Installation**: Installs NPM packages and compiles static assets via Vite.

---

## 2. Production Deployment Sequence

When deploying updates to staging or production, execute:

```bash
# 1. Pull changes
git pull origin main

# 2. Run composer dependencies update
composer install --no-dev --optimize-autoloader

# 3. Cache Laravel configurations & routes
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Migrate database updates safely
php artisan migrate --force

# 5. Build Vite asset bundles
npm install
npm run build

# 6. Restart Queue monitors
php artisan horizon:terminate
```

---

## 3. Persistent Daemons & Horizon Configurations

To run the application, ensure the following daemons are managed by system services (e.g. Supervisor):

### A. Queue Daemon (Horizon)
Starts the Horizon redis processor:
```bash
php artisan horizon
```
*In production, Supervisor should monitor this script to restart it if it terminates.*

### B. Periodic Cron Scheduler
Standard crontab scheduling:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

---
## Related Notes
* **MOC Connections**: [[Deployment MOC]], [[DevOps MOC]]
* **Navigation**:
  * ⬅️ Prev: [[DEPENDENCIES]]
  * ➡️ Next: [[UI_COMPONENTS]]
  * 🏠 Home: [[Home]] | 📊 Dashboard: [[Dashboard]]
