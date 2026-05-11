# Changelog

All notable changes to this project will be documented in this file.

This project follows a simple changelog style while it is in early development.

## Unreleased

### Added

- Mobile-friendly public booking form.
- Booking confirmation page with customer reference.
- Staff authentication and protected admin routes.
- Admin booking diary with daily booking overview.
- Business settings panel for restaurant details, address, brand colours and policies.
- Restaurant logo upload and removal.
- Staff user management with create, edit, activate/deactivate and delete actions.
- Service management for bookable service windows.
- Dining area and table management.
- Delete safeguards for services, tables and dining areas that are already in use.
- Code by Scott footer branding.
- Local SQLite seed data for demo restaurant, staff account, services, tables and bookings.
- Feature test coverage for bookings, authentication, settings, staff, services, dining areas and tables.

### Changed

- Replaced the default Laravel README with project documentation.
- Polished the demo restaurant seed data.
- Updated booking references to use the `CBR` prefix.

### Security

- Restricted admin diary, settings, staff, services and table management to authenticated staff users.
- Prevented staff users from deleting their own account.
- Prevented inactive staff accounts from logging in.

