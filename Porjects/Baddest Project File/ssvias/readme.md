# SSVIAS — Stolen Vehicle Identification & Alert System

## Overview
SSVIAS is a web-based system for registering vehicles, reporting theft incidents, verifying stolen vehicles, submitting sightings, and managing alerts for vehicle owners and law enforcement.

## Project Structure
- `index.php` — public landing page
- `login.php`, `register.php` — authentication pages
- `dashboard.php` — user dashboard
- `vehicles.php` — registered vehicles list
- `add-vehicle.php` — vehicle registration page
- `report-stolen.php` — stolen reporting page
- `verify.php` — vehicle verification page
- `sightings.php` — sighting submission page
- `notifications.php` — notification center
- `profile.php` — account settings
- `admin/` — administration panel
- `api/` — backend routes for auth, vehicles, sightings, OCR, reports, and admin actions
- `includes/` — shared helpers, database connection, and layout templates
- `assets/` — CSS and JS assets
- `db/ssvias.sql` — database schema and seed data

## Installation
1. Copy the project into your XAMPP `htdocs` folder or configure your web server to serve the `Implementation` directory.
2. Start Apache and MySQL.
3. Import `db/ssvias.sql` into MySQL using phpMyAdmin or the MySQL command line.
4. Ensure the database is accessible with the credentials in `includes/db.php` (`root` / empty password by default).
5. Open `http://localhost/ssvias/index.php`.

## Default Accounts
- Admin: `admin@ssvias.cm` / `admin123`
- Officer: `officer@ssvias.cm` / `admin123`
- Owner: `john@example.cm` / `admin123`

## Notes
- OCR is simulated in `api/ocr.php` for demo purposes.
- `api/reports.php` handles stolen vehicle reports.
- Admin actions live under `admin/` and require officer or admin access.
