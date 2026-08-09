---
title: "Getting Started"
aliases: ["Onboarding", "Quick Start"]
tags: ["onboarding", "setup", "developer"]
created: 2026-07-05
updated: 2026-07-05
---

# Developer Onboarding & Getting Started 🚀

Welcome to the vision-mission developer onboarding guide. Follow this guide to stand up the local development stack.

## System Prerequisites
Ensure the system satisfies the dependencies documented in [[DEPENDENCIES]]:
- **PHP** Version 8.2+
- **Node.js** Version 18+ & **npm**
- **Composer** Version 2+
- **MySQL** Version 8.0+
- **Redis** server (optional for local, defaults to sync queue, but highly recommended)

## Quick Start Setup Steps
1. **Clone the Repository**:
   ```bash
   git clone <repository-url> vision-mission
   cd vision-mission
   ```
2. **Execute Automated Setup Script**:
   Run the automated script documented in [[DEPLOYMENT]]:
   ```bash
   composer run setup
   ```
   This will install Composer packages, copy `.env.example` to `.env`, generate the encryption key, run database migrations, and build frontend assets.
3. **Configure Environment Variables**:
   Open `.env` and configure your settings. See [[CONFIGURATION]] for details.
4. **Boot the Dev Servers**:
   ```bash
   php artisan serve
   # In another terminal:
   npm run dev
   ```

## Verification Checklist
- [ ] Accessible at `http://127.0.0.1:8000`
- [ ] Database migrated successfully with initial seeding.
- [ ] Running `php artisan test` passes all 26 feature tests (see [[TESTING]]).

---
* 🏠 Home: [[Home]] | 📊 Dashboard: [[Dashboard]]
