# Changelog

All notable changes to this project will be documented in this file.

This project follows a simple changelog style while it is in early development.

## Unreleased

### Added

- Mobile-friendly public booking form.
- Booking confirmation page with customer reference.
- Customer booking confirmation emails.
- Staff alert emails for new online bookings.
- JSON API endpoints for venue details, services, availability lookup and booking creation.
- Staff authentication and protected admin routes.
- Admin booking diary with daily booking overview.
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
- Feature test coverage for bookings, authentication, settings, staff, services, availability, dining areas, tables, booking rules and API endpoints.

### Changed

- Replaced the default Laravel README with project documentation.
- Polished the demo restaurant seed data.
- Updated booking references to use the `CBR` prefix.

### Security

- Restricted admin diary, settings, staff, services and table management to authenticated staff users.
- Prevented staff users from deleting their own account.
- Prevented inactive staff accounts from logging in.
