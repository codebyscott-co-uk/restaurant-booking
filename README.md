# Restaurant Booking

A Laravel restaurant booking system with a mobile-friendly customer booking form, staff-only booking diary, configurable availability rules, business settings, branding, logo upload, and staff user management.

Built by [Code by Scott](https://codebyscott.co.uk).

## Current Features

- Mobile-friendly public booking form
- Live table availability checks
- Booking confirmation page with reference number
- Public booking lookup, modify and cancellation flow
- Secure customer booking management links with private tokens
- Embeddable public booking widget with iframe script and API-backed booking flow
- Branded customer emails for confirmation, modification, cancellation and reminders
- Branded staff alert emails for new online bookings
- JSON API endpoints for venue details, services, availability and booking creation
- Staff-only login area
- Polished staff dashboard with daily metrics, quick actions and setup health
- Responsive admin navigation with active states and mobile scrolling
- Staff-only sidebar navigation with grouped admin workspace sections
- Premium theme layer with animated cards, refined buttons, focus states and modal confirmations
- Modern staff diary with day/week views, service filters and mobile-friendly booking cards
- Sectioned business settings workspace
- Restaurant logo upload and brand colour controls
- Booking terms and cancellation policy settings
- WYSIWYG-style email template editing for branded customer and staff emails
- Brandable public widget settings and example embed snippet
- Configurable online change and cancellation notice period
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
/admin
/admin/diary
/admin/bookings/create
/admin/services
/admin/availability
/admin/areas
/admin/settings
/admin/staff
```

All admin routes require staff login.

## Public Customer Routes

```text
/manage-booking
/manage-booking/{booking_reference}/{customer_manage_token}
/manage-booking/{booking_reference}/{customer_manage_token}/edit
```

Customers can use their booking reference and email address to open a private management link. Online changes and cancellations are blocked once the booking is inside the configured notice period.

## Public Booking Widget

Widget iframe route:

```text
/widget/bookings
```

Example embed snippet:

```html
<div data-restaurant-booking-widget></div>
<script src="http://restaurant-booking.test/widget/embed.js" async></script>
```

The widget runs inside an iframe and uses the public API endpoints for services, availability and booking creation.

## Public API Routes

These endpoints are intended as the starting point for a future mobile app or headless booking frontend.

```text
GET /api/v1/venue
GET /api/v1/services
GET /api/v1/availability?service_id=1&date=2026-05-18&party_size=2
POST /api/v1/bookings
```

API booking responses include a `manage_url` that can be used by a mobile app or headless frontend for customer self-service.

Example booking payload:

```json
{
  "service_id": 1,
  "party_size": 2,
  "date": "2026-05-18",
  "time": "12:00",
  "first_name": "Grace",
  "last_name": "Taylor",
  "email": "grace@example.com",
  "phone": "07111 222333",
  "special_requests": "Window seat if possible."
}
```

## Roadmap

Planned next steps:

- Add SMS notifications
- Prepare deployment configuration

## Notes

The `.env` file, local SQLite database, dependencies, build output and uploaded storage symlink are intentionally ignored by Git.
