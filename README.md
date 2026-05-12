# Resora OS

A Laravel multi-tenant hospitality operations platform with mobile-friendly booking, a staff-only diary, configurable availability rules, tenant branding, guest self-management, widgets, and staff user management.

Built by [Code by Scott](https://codebyscott.co.uk).

## Current Features

- Mobile-friendly public booking form with polished SaaS styling
- Multi-tenant venue model with staff scoped to their own restaurant
- Self-serve Resora OS signup that creates a tenant workspace, owner account, starter services, tables and opening hours
- Tenant-specific public booking, widget and API URLs by restaurant slug
- Live table availability checks
- Booking confirmation page with reference number
- Public booking lookup, modify and cancellation flow
- Secure customer booking management links with private tokens
- Embeddable public booking widget with iframe script and API-backed booking flow
- Branded customer emails for confirmation, modification, cancellation and reminders
- Branded staff alert emails for new online bookings
- JSON API endpoints for venue details, services, availability and booking creation
- Staff-only login area
- Premium staff dashboard with colourful KPI widgets, animated infographics, service mix, booking source, cover flow and setup health panels
- Vuexy-inspired semi-dark staff admin shell with fixed sidebar, floating sticky top bar, search placeholder, quick actions, notifications placeholder, profile menu and persistent light/dark mode
- Premium indigo/slate SaaS component system for public booking, auth, customer management and widget experiences
- Responsive collapsible admin sidebar with grouped dropdown navigation sections, inline icons, active states and polished hover states
- Refined typography, cards, forms, buttons, badges, dropdowns and diary views for a commercial SaaS feel
- Modern staff diary with day/week views, service filters and mobile-friendly booking cards
- Sectioned business settings workspace
- Venue logo upload and brand colour controls
- Booking terms and cancellation policy settings
- WYSIWYG-style email template editing for branded customer and staff emails
- Brandable public widget settings and example embed snippet
- Configurable online change and cancellation notice period
- Staff user create, edit, activate/deactivate and delete
- Staff profile editing with avatar upload and personal contact details
- Tenant-scoped customer records and subscription ownership preparation for Stripe billing
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

## Resora OS Signup

Create a new restaurant workspace:

```text
http://restaurant-booking.test/signup
```

The signup flow creates a venue, owner account, default lunch and dinner services, starter tables, weekday opening hours and widget settings.

## Multi-Tenant Architecture

Resora OS uses the `venues` table as the tenant boundary. Staff users belong to exactly one venue through `users.venue_id`, and operational records such as bookings, customers, services, tables, dining areas, opening hours, closures and subscription records are tenant-owned.

Authenticated admin routes are protected by both `auth` and `tenant.staff` middleware. The middleware blocks staff accounts that are not attached to a venue before any admin controller runs. Controllers then resolve the active tenant with `currentVenue()` and enforce ownership with tenant-scoped queries or `ensureVenue()`.

Tenant-owned models use the `BelongsToVenue` concern, which provides a reusable `forVenue()` query scope and `belongsToVenue()` ownership check. This keeps recurring tenant checks consistent and gives future billing, reporting and notification code a shared pattern.

Public tenant routes resolve the venue from the restaurant slug:

```text
/r/{restaurant-slug}
/r/{restaurant-slug}/book
/r/{restaurant-slug}/manage-booking
/r/{restaurant-slug}/widget/bookings
/api/v1/{restaurant-slug}/...
```

Public booking creation only accepts services from the resolved venue. Customer booking management links are token-protected and, on tenant routes, also verify that the booking belongs to the slugged venue.

## Onboarding Flow

Self-serve signup creates all starter data inside one transaction:

- venue tenant
- owner staff user assigned to that venue
- starter dining area
- starter tables
- lunch and dinner services
- opening hours
- trialing tenant subscription placeholder for future Stripe billing

Billing is prepared at venue level through `tenant_subscriptions`, so Stripe customers and subscriptions can be owned by the restaurant tenant rather than by an individual staff user.

## Authorization Approach

Admin controllers avoid global record lists and load data through the current venue wherever possible. Route-bound admin resources are checked with `ensureVenue()` before they are displayed, updated or deleted. Validation rules for tenant-owned foreign keys, such as services, dining areas and opening hours, are scoped to the active venue to avoid cross-tenant IDs being accepted.

The test suite includes multi-tenant coverage for settings visibility, CRUD isolation, booking status isolation, availability isolation, public slug isolation, onboarding defaults and subscription ownership preparation.

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
/r/{restaurant-slug}
/r/{restaurant-slug}/manage-booking
```

Customers can use their booking reference and email address to open a private management link. Online changes and cancellations are blocked once the booking is inside the configured notice period.

## Public Booking Widget

Widget iframe route:

```text
/widget/bookings
/r/{restaurant-slug}/widget/bookings
```

Example embed snippet:

```html
<div data-restaurant-booking-widget></div>
<script src="http://restaurant-booking.test/r/the-demo-table/widget/embed.js" async></script>
```

The widget runs inside an iframe and uses the public API endpoints for services, availability and booking creation.

## Public API Routes

These endpoints are intended as the starting point for a future mobile app or headless booking frontend.

```text
GET /api/v1/venue
GET /api/v1/services
GET /api/v1/availability?service_id=1&date=2026-05-18&party_size=2
POST /api/v1/bookings
GET /api/v1/{restaurant-slug}/venue
GET /api/v1/{restaurant-slug}/services
GET /api/v1/{restaurant-slug}/availability?service_id=1&date=2026-05-18&party_size=2
POST /api/v1/{restaurant-slug}/bookings
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
