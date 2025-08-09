# Copilot Instructions for DOUBLE (Laravel Blood Bank)

## Project Overview
- **Framework:** Laravel (PHP)
- **Purpose:** Blood bank management system with features for reservations, donations, inventory, and user management.
- **Key Directories:**
  - `app/Models/`: Eloquent ORM models for all business entities (e.g., `BloodBag`, `Donor`, `ReservationRequest`).
  - `app/Http/Controllers/`: Handles HTTP requests and business logic.
  - `routes/`: Route definitions (`web.php`, `api.php`, etc.).
  - `database/migrations/`, `database/seeders/`: Schema and seed data.
  - `resources/views/`: Blade templates for UI.
  - `tests/`: PHPUnit tests (Feature/Unit).

## Developer Workflows
- **Run the app:** Use `php artisan serve` (default: http://localhost:8000).
- **Database:**
  - Default: MySQL (see `.env`).
  - SQLite file for local/dev: `database/database.sqlite`.
  - Migrate: `php artisan migrate` (see `run_migrations.php` for custom logic).
- **Testing:**
  - Run all tests: `php artisan test` or `vendor\bin\phpunit`.
  - Test files: `tests/`, plus custom scripts like `test_sprint4.php`.
- **Build assets:** Use Vite (`npm run dev`), config in `vite.config.js`.

## Project-Specific Patterns
- **Session, Cache, Queue:** All use `database` drivers by default (see `.env`).
- **Mail:** Uses log driver for local/dev (see `.env`).
- **Custom scripts:**
  - `run_migrations.php`, `diagnostic_sprint4.php`, `test_reservation_acompte.php`, etc. are entry points for custom workflows/tests.
- **Models:** All business logic is in `app/Models/`. Relationships and scopes are defined per model.
- **Controllers:** Grouped by domain in `app/Http/Controllers/`.
- **Validation:** Uses Form Requests in `app/Http/Requests/`.

## Integration & Conventions
- **External dependencies:** Managed via Composer (`composer.json`) and NPM (`package.json`).
- **Environment:** All config in `.env` (see example for DB, mail, session, cache, etc.).
- **No hardcoded credentials:** Use `.env` for secrets.
- **Testing conventions:**
  - Custom test scripts supplement PHPUnit tests for sprint-based validation.
  - Test result documentation in `TESTS_SPRINT2_RESULTATS.md`, etc.

## Examples
- To add a new entity: create a model in `app/Models/`, migration in `database/migrations/`, controller in `app/Http/Controllers/`, and update routes.
- To run a custom test: `php test_sprint4.php` (from project root).

---
**For AI agents:**
- Prefer using Laravel's built-in features (Eloquent, Form Requests, Blade, etc.).
- Follow existing directory structure and naming conventions.
- Reference `.env` for all environment-specific config.
- When in doubt, check for custom scripts in the project root for non-standard workflows.
