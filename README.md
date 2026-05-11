# Restaurant Booking

A Laravel restaurant booking system with a mobile-friendly customer booking form, staff-only booking diary, configurable availability rules, business settings, branding, logo upload, and staff user management.

Built by [Code by Scott](https://codebyscott.co.uk).

## Current Features

- Mobile-friendly public booking form
- Live table availability checks
- Booking confirmation page with reference number
- Customer booking confirmation emails
- Staff alert emails for new online bookings
- Staff-only login area
- Admin booking diary
- Business settings panel
- Restaurant logo upload and brand colour controls
- Booking terms and cancellation policy settings
- Staff user create, edit, activate/deactivate and delete
- Service management for lunch, dinner and other bookable sessions
- Dining area and table management
- Opening hours and closure management
- Staff manual booking creation
- Booking status controls from the diary
- Booking rules for lead time, advance booking window, max party size and slot capacity
- Joined-table allocation for larger parties
- Seeded demo restaurant, tables, services and bookings
- Code by Scott footer branding

## Tech Stack

- Laravel 13
- PHP 8.4 locally through Laravel Herd
- SQLite for local development
- Blade templates
- Vite and Tailwind-ready frontend tooling
- PHPUnit feature tests

## Local Setup

This project was created for local development with [Laravel Herd](https://herd.laravel.com).

Clone the repository into your Herd directory:

```bash
cd ~/Herd
git clone https://github.com/codebyscott-co-uk/restaurant-booking.git
cd restaurant-booking
```

Install PHP dependencies:

```bash
composer install
```

Create the environment file:

```bash
cp .env.example .env
php artisan key:generate
```

Create the local SQLite database:

```bash
touch database/database.sqlite
```

Run migrations and seed demo data:

```bash
php artisan migrate:fresh --seed
```

Install and build frontend assets:

```bash
npm install
npm run build
```

Link public storage for uploaded logos:

```bash
php artisan storage:link
```

Open the app:

```text
http://restaurant-booking.test
```

## Staff Login

Local seeded staff account:

```text
Email: hello@codebyscott.co.uk
Password: Letmein.123@
```

Staff login URL:

```text
http://restaurant-booking.test/staff/login
```

## Useful Commands

Run tests:

```bash
php artisan test
```

Rebuild the local database:

```bash
php artisan migrate:fresh --seed
```

Run frontend development server:

```bash
npm run dev
```

Build frontend assets:

```bash
npm run build
```

## Admin Routes

```text
/admin/diary
/admin/bookings/create
/admin/services
/admin/availability
/admin/areas
/admin/settings
/admin/staff
```

All admin routes require staff login.

## Roadmap

Planned next steps:

- Add SMS notifications
- Add API endpoints for future mobile app use
- Prepare deployment configuration

## Notes

The `.env` file, local SQLite database, dependencies, build output and uploaded storage symlink are intentionally ignored by Git.
