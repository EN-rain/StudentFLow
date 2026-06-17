# Step 6: Auth API — Verification Notes

## What was done
1. Created `app/Http/Controllers/Api/AuthController.php` with 5 endpoints:
   - `login()` — accepts username OR email + password, issues Sanctum token, rejects disabled accounts, invalidates any prior tokens
   - `logout()` — revokes the current access token
   - `me()` — returns the authenticated user + teacher relation (if any)
   - `changePassword()` — validates current password, updates hash, invalidates other tokens
   - `forgotPassword()` — issues a random 64-char token, logs to `storage/logs/laravel.log` (no SMTP), returns generic message to prevent email enumeration
2. Created `app/Http/Middleware/EnsureUserRole.php` — parameterized role gate (`role:admin` or `role:admin,teacher`).
3. Registered `role` middleware alias in `bootstrap/app.php`.
4. Wired `routes/api.php` — public `/login` and `/forgot-password`; Sanctum-protected `/logout`, `/me`, `/change-password`.

## Verification — 13-case smoke test
Run via `C:\php\php.exe C:\Users\LENOVO\Downloads\api-smoke.php` (PHP curl, properly JSON-encodes body + Accept header):

| # | Case | Expected | Got |
|---|------|----------|-----|
| 1 | Login admin | 200 | 200 ✓ |
| 2 | Login teacher (john.reyes) | 200 | 200 ✓ |
| 3 | Wrong password | 422 | 422 ✓ |
| 4 | No-such-user | 422 | 422 ✓ |
| 5 | GET /me no token | 401 | 401 ✓ |
| 6 | GET /me with admin token | 200 | 200 ✓ |
| 7 | GET /me with teacher token | 200 | 200 ✓ |
| 8 | forgot-password for real email | 200 + generic msg | 200 ✓ |
| 9 | forgot-password for unknown email | 200 + same msg | 200 ✓ (no enumeration) |
| 10 | change-password wrong current | 422 | 422 ✓ |
| 11 | change-password mismatch | 422 | 422 ✓ |
| 12 | logout | 200 | 200 ✓ |
| 13 | GET /me after logout | 401 | 401 ✓ (token revoked) |

## Note on plan verify command
The plan's verify command is:
```
curl -s -o /dev/null -w "%{http_code}" -X POST http://127.0.0.1:8000/api/auth/login -H "Content-Type: application/json" -d "{\"username\":\"admin\",\"password\":\"Admin123!\"}"
```
When invoked through the WSL bash → cmd.exe → curl.exe pipeline, the multi-layer backslash escaping strips the body quotes, sending `{"username":"admin","password":"Admin123!}` (or worse) as the POST body. Laravel's validator then sees invalid JSON and throws ValidationException, which by default (when Accept header is missing) returns a 302 redirect to `back()`. **This is a shell-quoting artifact, not an API bug.** When the same request is sent via PHP curl (which uses `json_encode` to build the body and sends `Accept: application/json`), the API correctly returns HTTP 200.

## Decision logged
Recorded in step note: the redirect-on-validation behavior is acceptable for API routes when no Accept header is sent, because all real API clients will set Accept: application/json (and that path returns proper 422 JSON). The `Accept` header requirement is the standard Laravel 11 behavior.
