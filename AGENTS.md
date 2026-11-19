# Repository Guidelines

## Project Structure
This is a Laravel 12 application (PHP 8.2+). Application logic lives in `app/` (models, middleware, exports, providers); HTTP routes are in `routes/`; Blade views and frontend assets are in `resources/`; public files are in `public/`. Database migrations, factories, and seeders belong in `database/`. Tests are organized under `tests/Feature` and `tests/Unit`. Vite configuration and frontend dependencies are defined in `vite.config.js` and `package.json`.

## Build, Test, and Development
- `composer install` installs PHP dependencies; `npm install` installs frontend dependencies.
- `composer run dev` starts the Laravel server, queue listener, and Vite development server together.
- `npm run dev` runs only the Vite asset server; `npm run build` compiles production assets.
- `php artisan migrate` applies database migrations after configuring `.env`.
- `composer test` runs the PHPUnit suite via `php artisan test`.

## Coding Style
Follow Laravel conventions and existing neighboring code. Use PSR-4 namespaces and `StudlyCase` class names; use descriptive `camelCase` methods and variables. Keep controllers focused and put reusable domain behavior in appropriate classes. Format PHP with `vendor/bin/pint`; keep Blade, CSS, and JavaScript consistent with the surrounding files.

## Testing
Use PHPUnit, with feature tests in `tests/Feature` for HTTP and workflow behavior and unit tests in `tests/Unit` for isolated logic. Name test files with the `*Test.php` suffix and test methods descriptively (for example, `test_user_can...`). Run the full suite with `composer test`, or a focused test with `php artisan test tests/Feature/SomeTest.php`. Add coverage for changed behavior and relevant edge cases.

## Commits and Pull Requests
No project-specific commit convention is documented; use short imperative subjects that describe the change (for example, `Fix barang report date grouping`). Keep each commit focused. Pull requests should explain the user-visible change, list notable implementation choices, reference related issues, and include screenshots for UI changes. Mention tests run and any required migration or configuration updates.

## Configuration and Data Safety
Keep credentials and environment-specific values in `.env`; never commit secrets. Review migrations and seeders for unintended data changes before submitting.
