# SSVIAS — Stolen Vehicle Identification & Alert System
## Full Implementation Plan

### Project Overview
A full-stack web application (mobile-first) built with HTML/CSS/JS (frontend) and PHP/MySQL/XAMPP (backend). The system covers vehicle registration, stolen vehicle reporting, real-time verification, image-based plate OCR simulation, crowd sighting reports, and an admin dashboard.

---

## Project Structure

```
ssvias/                          ← Root folder (inside htdocs/)
├── index.php                    ← Landing / Home page
├── login.php                    ← Login page
├── register.php                 ← User registration
├── dashboard.php                ← User dashboard
├── vehicles.php                 ← My vehicles list
├── add-vehicle.php              ← Register a vehicle
├── report-stolen.php            ← Report vehicle as stolen
├── verify.php                   ← Public vehicle verification
├── sightings.php                ← Report a sighting
├── notifications.php            ← User notifications
├── profile.php                  ← User profile settings
│
├── admin/
│   ├── index.php                ← Admin dashboard
│   ├── vehicles.php             ← Manage all vehicles
│   ├── reports.php              ← Manage stolen reports
│   ├── users.php                ← Manage users
│   ├── sightings.php            ← Manage sightings
│   └── generate-report.php     ← Export/print reports
│
├── api/
│   ├── auth.php                 ← Login/register/logout
│   ├── vehicles.php             ← CRUD vehicles
│   ├── reports.php              ← Stolen reports CRUD
│   ├── verify.php               ← Plate/VIN lookup
│   ├── sightings.php            ← Sighting submissions
│   ├── notifications.php        ← Fetch notifications
│   ├── ocr.php                  ← Image upload + simulated OCR
│   └── admin.php               ← Admin actions
│
├── includes/
│   ├── db.php                   ← PDO database connection
│   ├── auth_check.php           ← Session guard
│   ├── header.php               ← Shared HTML head + nav
│   └── footer.php               ← Shared footer
│
├── assets/
│   ├── css/
│   │   └── style.css            ← Global styles (dark theme)
│   ├── js/
│   │   ├── app.js               ← Global JS utilities
│   │   ├── verify.js            ← Verification page logic
│   │   ├── ocr.js               ← OCR upload/preview
│   │   └── dashboard.js         ← Dashboard charts
│   └── img/
│       └── logo.png             ← App logo
│
├── uploads/
│   ├── vehicles/                ← Vehicle images
│   └── sightings/               ← Sighting images
│
└── db/
    └── ssvias.sql               ← Full database schema + seed
```

---

## Database Schema (MySQL)

### Tables
1. **users** — id, name, email, phone, password_hash, role (admin/officer/owner/public), created_at
2. **vehicles** — id, owner_id, plate_number, vin, make, model, color, year, status (active/stolen/recovered), image_path, created_at
3. **stolen_reports** — id, vehicle_id, reporter_id, last_seen_location, description, reported_at, status (pending/verified/closed)
4. **sightings** — id, vehicle_id, reporter_id, location, description, image_path, sighted_at, verified
5. **notifications** — id, user_id, message, type, is_read, created_at
6. **admin_logs** — id, admin_id, action, target_id, created_at

---

## Pages & Features

### Public Pages
- **Home (index.php)** — Hero, stats counter, quick search bar, how it works
- **Verify (verify.php)** — Search by plate/VIN + image upload OCR tab

### Auth Pages
- **Login / Register** — Glassmorphism card UI, role selection on register

### User Pages (logged in)
- **Dashboard** — Stats cards, recent activity, quick actions
- **My Vehicles** — Cards grid with status badges
- **Add Vehicle** — Form with image upload preview
- **Report Stolen** — Mark vehicle stolen, last location input
- **Sightings** — Submit public sighting with map/location text + image
- **Notifications** — Notification list with read/unread
- **Profile** — Edit account info

### Admin Panel
- **Admin Dashboard** — Charts: total vehicles, stolen count, sightings, users
- **All Vehicles** — Filterable data table, edit/delete/change status
- **Stolen Reports** — Verify/close reports
- **Users Management** — View/disable/change roles
- **Sightings** — Review and verify crowd reports
- **Generate Reports** — Printable summary

---

## Build Order

1. `db/ssvias.sql` — Database schema
2. `includes/db.php` — DB connection
3. `assets/css/style.css` — Full design system (dark gradient theme)
4. `includes/header.php` + `footer.php`
5. `api/auth.php` — Login/register/logout API
6. `login.php` + `register.php`
7. `index.php` — Landing page
8. `includes/auth_check.php`
9. `api/vehicles.php` — Vehicle CRUD
10. `add-vehicle.php` + `vehicles.php`
11. `api/reports.php` + `report-stolen.php`
12. `api/verify.php` + `verify.php` + `api/ocr.php`
13. `api/sightings.php` + `sightings.php`
14. `api/notifications.php` + `notifications.php`
15. `dashboard.php`
16. `profile.php`
17. Admin panel (all pages)
18. `db/ssvias.sql` seed data
19. Final polish + mobile responsiveness
