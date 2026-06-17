# Step 3: SQLite + Sanctum Setup - Verification Notes

## What was done
1. Created empty `database\database.sqlite` (0 bytes).
2. `.env` already had `DB_CONNECTION=sqlite` from the Laravel scaffold default - no edit needed.
3. Ran `php artisan migrate` → 3 default Laravel migrations ran (users, cache, jobs).
4. Ran `php artisan install:api --no-interaction` → installed Sanctum package, published `routes/api.php`.
5. Sanity problem: Sanctum's `personal_access_tokens` migration wasn't auto-registered. Published it manually with `php artisan vendor:publish --tag=sanctum-migrations --force` and ran `php artisan migrate` again.

## Tables in DB after step 3
- cache, cache_locks (Laravel cache)
- failed_jobs, job_batches, jobs (Laravel queue)
- migrations (migration tracking)
- password_reset_tokens, sessions (Laravel users table extras)
- users (Laravel default)
- personal_access_tokens (Sanctum)

## Verification
- `php artisan migrate:status` → 4 migrations marked Ran ✓

## Notes
- install:api in Laravel 11 only publishes the routes file and installs the Sanctum composer package - it does NOT auto-publish Sanctum's migration. Documented as a known Laravel 11 quirk. Migration publish step must be done explicitly.
