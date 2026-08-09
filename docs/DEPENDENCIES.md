---
title: "System Dependencies & Packages Reference"
aliases: ["Dependencies","System Dependencies"]
tags: ["setup","packages","composer","npm","existing"]
created: 2024-01-05
updated: 2026-07-05
---

# System Dependencies & Packages Reference

This file documents the PHP and JavaScript libraries utilized in `vision-mission`, mapping their roles and versions.

---

## 1. Backend PHP Dependencies (`composer.json`)

### Core Requirements
* **PHP**: `^8.2` (Required for modern features, typed properties, and read-only attributes).
* **laravel/framework**: `^12.0` (Core framework layer).
* **laravel/horizon**: `^5.46` (Redis monitor dashboard for managing queues).
* **spatie/laravel-permission**: `^6.11` (Implements granular role and permission checks on administrative panels).
* **smalot/pdfparser**: `2.12` (Processes text extraction from uploaded governmental PDFs).
* **phpoffice/phpspreadsheet**: `4.1` (Parses CSV and Excel files during batch imports).
* **phpoffice/phpword**: `1.3` (Parses DOCX notification templates).

### Development Requirements
* **phpunit/phpunit**: `^11.5.50` (Primary test runner executing the 26 feature tests).
* **laravel/pail**: `^1.2.2` (Real-time log tailing tool for console debugging).
* **laravel/pint**: `^1.24` (PHP code style fixer).
* **laravel/sail**: `^1.41` (Docker development workspace setup).
* **mockery/mockery**: `^1.6` (Mocking framework used inside feature tests).

---

## 2. Frontend JS/React Dependencies (`package.json`)

* **React**: `^18.2.0` (Powers custom components such as eligibility wizard, graphs, and settings editors).
* **Vite**: `^7.0.7` (Build tool compiling JS assets).
* **axios**: `^1.11.0` (HTTP client executing calls from SPA frontend back to the API).
* **tailwindcss**: `^4.0.0` (Sleek CSS styling).
* **lucide-react**: `^0.370.0` (Modern iconography library).
* **concurrently**: `^9.0.1` (Utility script running server, queue, pail, and Vite simultaneously in dev).

---
## Related Notes
* **MOC Connections**: [[Deployment MOC]]
* **Navigation**:
  * ⬅️ Prev: [[CONFIGURATION]]
  * ➡️ Next: [[DEPLOYMENT]]
  * 🏠 Home: [[Home]] | 📊 Dashboard: [[Dashboard]]
