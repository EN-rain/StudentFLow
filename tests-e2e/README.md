# StudentFlow End-to-End Tests

This directory contains the Playwright end-to-end (E2E) test configuration for the StudentFlow QA audit.

## Running the tests

1. Start the Laravel development server bound to the expected host/port:

   ```bash
   php artisan serve --host=127.0.0.1 --port=8000 &
   ```

2. Run the Playwright tests:

   ```bash
   npx playwright test
   ```

## Important notes

- **Chromium-only**: This configuration runs against a single Chromium project. Firefox and WebKit are intentionally not enabled.
- **No retries**: `retries` is set to `0`. Each failing test is recorded exactly once, so investigate failures as they happen.
- **Database seeding**: The database must be re-seeded before each run:

  ```bash
  php artisan migrate:fresh --seed --env=testing
  ```

- **Seeded credentials**: The known seeded passwords are exported into `process.env` for use in specs:
  - admin: `AdminPass123!`
  - teacher: `TeacherPass123!`
  - student: `StudentPass123!`

## Configuration

See `playwright.config.js` for the full test settings, including `baseURL`, viewport, trace, and screenshot behavior.
