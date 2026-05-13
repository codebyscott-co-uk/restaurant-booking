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
- Premium bookings diary with status/search/service filters, timeline and list views, detail drawer, quick status actions and table assignment
- Professional+ customer CRM with VIP flags, allergy/dietary notes, preferences, booking history and repeat-guest indicators
- Sectioned business settings workspace
- Venue logo upload and brand colour controls
- Booking terms and cancellation policy settings
- WYSIWYG-style email template editing for branded customer and staff emails
- Brandable public widget settings and example embed snippet
- Configurable online change and cancellation notice period
- Staff user create, edit, activate/deactivate and delete
- Staff profile editing with avatar upload and personal contact details
- Tenant-scoped customer records and subscription ownership preparation for Stripe billing
- Stripe billing with Laravel Cashier, venue-owned subscriptions, Checkout, Billing Portal and plan-based feature gates
- Feature-gated analytics and reports with tenant-scoped booking, cover, service, cancellation and customer repeat-visit insights
- Premium-only advanced reports with CSV exports for bookings, covers, services, customer activity and operational performance
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

## Stripe Billing

Resora OS uses Laravel Cashier with `Venue` as the billable model. Stripe customers and subscriptions attach to the venue tenant, not to individual staff users.

Required local `.env` values:

```env
STRIPE_KEY=your_stripe_publishable_key
STRIPE_SECRET=your_stripe_secret_key
STRIPE_STARTER_PRICE_ID=price_starter_monthly
STRIPE_PROFESSIONAL_PRICE_ID=price_professional_monthly
STRIPE_PREMIUM_PRICE_ID=price_premium_monthly
STRIPE_TRIAL_DAYS=14
STRIPE_WEBHOOK_SECRET=
CASHIER_CURRENCY=gbp
CASHIER_CURRENCY_LOCALE=en_GB
```

Do not commit your real `.env` file or real Stripe keys. `.env.example` only contains safe placeholders.

In Stripe test mode, create three recurring monthly prices:

- Starter
- Professional
- Premium

Copy each Stripe Price ID into the matching `STRIPE_*_PRICE_ID` value. New subscriptions started through Checkout receive a 14-day trial when the venue has not previously completed a Cashier subscription.

Billing routes:

```text
GET /admin/billing
POST /admin/billing/checkout/{plan}
POST /admin/billing/swap/{plan}
POST /admin/billing/resume
GET|POST /admin/billing/portal
POST /stripe/webhook
```

The billing page stays accessible even if platform access expires, so venue admins can restore payment details or choose a plan.

For local Checkout testing, use Stripe test card:

```text
4242 4242 4242 4242
```

Webhook setup:

```bash
stripe listen --forward-to http://restaurant-booking.test/stripe/webhook
```

Then copy the displayed webhook signing secret into `STRIPE_WEBHOOK_SECRET`. Local development can run with this value blank, but production must set it so Cashier can verify Stripe webhook signatures.

Supported webhook events include checkout completion, subscription create/update/delete and invoice payment success/failure. Cashier keeps the local `subscriptions` and `subscription_items` tables in sync.

## Plan Features

Plan gates are defined in `config/resora_billing.php`.

Starter includes online booking, the booking diary, table and area management, email confirmations, basic settings and up to 3 staff users.

Professional includes everything in Starter plus customer CRM, advanced booking rules, analytics/reporting, enhanced branding and up to 10 staff users.

Premium includes everything in Professional plus priority support, advanced reporting, future waitlist modules, advanced multi-service controls and unlimited staff users where practical.

Locked features show a friendly upgrade screen rather than a generic error, and locked navigation items remain visible with a lock badge for upsell context.

## Analytics & Reports

Analytics and reports live at:

```text
GET /admin/reports
GET /admin/reports/export/{report}
```

The section uses the existing plan gates:

- Starter venues cannot access analytics and are sent to the upgrade screen.
- Professional venues can access standard analytics and standard report tables.
- Premium venues can access all standard analytics plus advanced panels and CSV exports.

Every analytics query is scoped to the authenticated staff user's current venue. Reports never load bookings, services, customers or table allocations from another tenant.

Standard analytics include total bookings, confirmed bookings, cancellations, no-shows, covers booked, average party size, bookings by day, service performance, status mix, busiest days/times and upcoming booking count.

Standard report tables include bookings, covers, service/session performance, cancellation/no-show summary and repeat customer summary where customer history exists.

Premium advanced analytics include booking trends against the previous period, forecast covers where enough history exists, cancellation/no-show rates, average covers per service, top service performance, table utilisation, repeat-visit rate and previous-period comparison.

Premium CSV exports are available for:

- bookings
- covers
- service performance
- customer activity
- operational performance

Date filters support today, last 7 days, last 30 days, this month, last month and custom ranges. The analytics UI uses lightweight Blade/CSS chart components so no extra browser chart dependency is required.

## Customer CRM

The Customers / CRM section lives at:

```text
GET /admin/customers
GET /admin/customers/create
POST /admin/customers
GET /admin/customers/{customer}
GET /admin/customers/{customer}/edit
PUT /admin/customers/{customer}
```

Customer CRM is controlled by the `customer_crm` feature gate:

- Starter venues see the polished upgrade screen and cannot access CRM routes.
- Professional venues can manage customer profiles, notes and visit history.
- Premium venues get the full Professional CRM experience alongside premium reporting modules.

Customer records are venue-scoped. Staff users only search, view, edit and attach customers from their authenticated venue. Cross-tenant customer IDs return a not found response.

CRM profiles include contact details, VIP status, marketing opt-in, allergies, dietary requirements, preferences, favourite area/table and private internal notes. These internal CRM notes are for staff only and are not shown on public booking pages.

Bookings link to customers automatically where safe. Public, API and staff-created bookings reuse an existing customer from the same venue when the email or phone matches; otherwise a new tenant-scoped customer is created. Staff booking drawers show VIP, repeat-guest and CRM note indicators only when the venue has CRM access.

## Tables & Areas

Tables & Areas is core Resora OS functionality and is available to Starter, Professional and Premium venues.

The section lives at:

```text
GET /admin/areas
GET /admin/areas/create
POST /admin/areas
GET /admin/areas/{area}/edit
PUT /admin/areas/{area}
PATCH /admin/areas/{area}/toggle
DELETE /admin/areas/{area}
GET /admin/tables/create
POST /admin/tables
GET /admin/tables/{table}/edit
PUT /admin/tables/{table}
PATCH /admin/tables/{table}/toggle
DELETE /admin/tables/{table}
```

Dining areas represent operational sections such as Main Restaurant, Bar, Garden, Private Dining and Terrace. Areas can be active or inactive, sorted, described and deleted only when no tables depend on them.

Tables belong to one venue and one dining area. Each table has a name, minimum covers, maximum capacity, active status, joinable flag and internal notes. Active capacity is calculated from active tables only.

The overview includes visual table cards grouped by area, a compact list view, area capacity guidance, inactive-table warnings, future booking counts and safe quick actions. Tables with future bookings cannot be hard deleted; deactivate them instead so existing bookings remain intact.

Booking create/edit and availability only use active tables from active areas in the authenticated staff user’s current venue. Cross-tenant table and area IDs are rejected.

Joined-table allocation is already supported by the availability engine using joinable tables. Saved table-combination management is presented as a Professional-ready future workflow, while the drag-and-drop Visual Floorplan panel is a Premium-only future module.

## Admin Routes

```text
/admin
/admin/diary
/admin/bookings/create
/admin/bookings/{booking_reference}/edit
/admin/customers
/admin/reports
/admin/services
/admin/availability
/admin/areas
/admin/settings
/admin/staff
```

All admin routes require staff login.

## Bookings Diary

The staff bookings diary is available to every active or trial venue plan because bookings are core Resora OS functionality.

Diary features include:

- today, tomorrow and previous/next day navigation
- day and week modes
- timeline and compact list views
- service/session filter
- status filter
- search by guest name, email, phone or booking reference
- summary cards for bookings, covers, confirmed, seated, completed, cancelled and no-shows
- service-grouped timeline for configured venue services such as lunch, dinner or custom sessions
- slide-over booking detail panels with guest, contact, table, request, note and timeline information
- quick status actions and internal note updates

Booking statuses currently used by the system are:

```text
pending
confirmed
seated
completed
cancelled
no_show
```

Staff-created bookings support phone, walk-in, staff and online/source values. Table assignment is scoped to the current venue and can use one or multiple tables where the venue supports joined-table workflows. The app rejects cross-tenant table IDs and prevents table conflicts, under-capacity assignments, closures, closed service windows and slot capacity breaches.

Advanced booking rules remain a Professional+ billing feature. Core diary, booking creation, booking editing and booking status management remain available to Starter, Professional and Premium venues.

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
