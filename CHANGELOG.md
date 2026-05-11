# Changelog

All notable changes to this project will be documented in this file.

This project follows a simple changelog style while it is in early development.

## Unreleased

### Added

- Mobile-friendly public booking form.
- Premium light theme for the public booking, signup, login, booking management and embeddable widget experiences.
- Cream-and-gold liquid-glass interaction polish for public and admin cards, buttons, slots and active states.
- Animated cream-and-gold abstract ribbon backgrounds for public, widget and admin surfaces, with reduced-motion support.
- Floating admin notification and profile dropdown panels that no longer stretch the top bar.
- Multi-tenant venue ownership for staff accounts and admin data isolation.
- Self-serve restaurant signup for creating a tenant venue, owner account, starter services, tables and opening hours.
- Tenant-specific public booking, widget and API routes by restaurant slug.
- Booking confirmation page with customer reference.
- Public booking lookup, modify and cancellation pages for customers.
- Token-protected customer booking management links.
- Configurable online change and cancellation notice period.
- Embeddable public booking widget with iframe embed script and API-backed booking flow.
- Branded customer emails for confirmation, modification, cancellation and reminder flows.
- Branded staff alert emails for new online bookings.
- Reusable email layout components for booking messages.
- WYSIWYG-style settings editor for customising customer and staff email copy.
- Brandable widget settings and example embed snippet in business settings.
- JSON API endpoints for venue details, services, availability lookup and booking creation with customer manage URLs.
- Staff authentication and protected admin routes.
- Polished staff dashboard with daily metrics, quick actions, upcoming bookings and setup health.
- Responsive admin navigation with active states and improved mobile behaviour.
- Staff-only sidebar navigation with grouped admin workspace sections and mobile menu toggle.
- Premium staff admin shell with sticky top bar, quick action, notifications placeholder, profile menu and persistent light/dark mode.
- Responsive collapsible admin sidebar with grouped dropdown navigation for dashboard, bookings, availability, tables, services, customers, billing, settings and staff.
- Refined admin typography, glass-style surfaces, inline navigation icons, polished buttons, badges and top bar controls.
- Shared UI polish for admin metrics, empty states, success states and compact staff pages.
- Premium theme styling with animated surfaces, refined controls, better focus states and modal confirmations for destructive actions.
- Modern staff diary with day/week views, service filters and mobile-friendly booking cards.
- Business settings panel for restaurant details, address, brand colours and policies.
- Configurable booking rules for lead time, advance booking window, maximum party size, maximum slot covers and joined-table allocation.
- Restaurant logo upload and removal.
- Staff user management with create, edit, activate/deactivate and delete actions.
- Service management for bookable service windows.
- Dining area and table management.
- Opening hours and closure management.
- Manual booking creation for staff.
- Booking status controls from the diary.
- Joined-table allocation for larger parties.
- Delete safeguards for services, tables and dining areas that are already in use.
- Code by Scott footer branding.
- Local SQLite seed data for demo restaurant, staff account, services, tables and bookings.
- Feature test coverage for bookings, customer booking management, authentication, dashboard, settings, staff, services, availability, dining areas, tables, booking rules and API endpoints.

### Changed

- Replaced the default Laravel README with project documentation.
- Polished the demo restaurant seed data.
- Updated booking references to use the `CBR` prefix.
- Kept the Code by Scott footer pinned to the bottom on short pages.
- Reworked business settings into focused tabbed panels with a persistent save action.
- Kept public booking pages on the public layout while authenticated admin routes use the new staff workspace shell.

### Security

- Scoped staff management and admin resources to the logged-in user's venue.
- Protected public booking details behind customer management tokens.
- Restricted admin diary, settings, staff, services and table management to authenticated staff users.
- Prevented staff users from deleting their own account.
- Prevented inactive staff accounts from logging in.
