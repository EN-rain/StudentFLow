# Step 7: Web Dashboard + Auth Views - Verification Notes

## What was done
1. Created `app/Http/Controllers/Web/AuthWebController.php` with 6 actions: `showLogin`, `login`, `logout`, `showForgotPassword`, `forgotPassword`, `showChangePassword`, `changePassword`. Uses Laravel's session guard (Auth::login + session regeneration).
2. Created `app/Http/Controllers/Web/DashboardController.php` with `index()` that dispatches to admin or teacher dashboard based on user role:
   - Admin: total students/classes/teachers, absent today, pending assignments, recent announcements.
   - Teacher: own classes count, own students count, absent today (own classes), own pending assignments, own recent announcements + recent grade updates.
3. Created Blade views with Bootstrap 5 (CDN):
   - `resources/views/layouts/app.blade.php` - base layout with sidebar nav, Bootstrap 5 + Bootstrap Icons from CDN
   - `resources/views/auth/login.blade.php` - login form with CSRF + demo credentials displayed
   - `resources/views/auth/forgot-password.blade.php` - password-reset request
   - `resources/views/auth/change-password.blade.php` - change password (current + new + confirm)
   - `resources/views/dashboard/admin.blade.php` - admin stats grid + recent announcements
   - `resources/views/dashboard/teacher.blade.php` - teacher stats grid + own announcements + recent grades
4. Updated `routes/web.php`: public auth routes + protected `/dashboard` and `/change-password`.
5. Generated APP_KEY (`php artisan key:generate`) since scaffold didn't include one.
6. Fixed `Attendance` model - added `protected $table = 'attendance';` because Laravel's default plural inference (`attendances`) didn't match the migration's singular table name from plan.md §17.

## Verification - web smoke test
| Case | Expected | Got |
|------|----------|-----|
| GET /login | 200, CSRF token | 200 ✓, CSRF token extracted ✓ |
| POST /login as admin | 302 → /dashboard | 302 ✓; dashboard renders with "Administrator Dashboard", "Maria Santos", stat cards showing 20 students + 3 classes + 3 teachers ✓ |
| POST /login as teacher (john.reyes) | 302 → /dashboard | 302 ✓; dashboard renders "Teacher Dashboard", "John Michael Reyes", "My Classes", "My Students" ✓ |
| GET /forgot-password | 200 | 200 ✓ |
| GET /change-password (logged in) | 200, form rendered | 200 ✓, "Current password" field present ✓ |
| POST /logout | 302 | 302 ✓ |
| GET /dashboard after logout | 302 (guest) | 302 ✓ |

## Plan verify command
`curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:8000/login` → `200` ✓

## Notes
- During initial test, POST /login returned 500 because the `Attendance` model auto-inferred table name `attendances` (Laravel plural) while the migration created `attendance` (singular, per plan §17). Fixed by adding `protected $table = 'attendance';` to the model.
- Login as admin displays all four plan §4.2 dashboard stat cards with seeded values (Total Students=20, Total Classes=3, Total Teachers=3, plus absent today and pending assignments).
- Login as teacher (john.reyes) displays teacher-specific stats scoped to BSIT 2A.
- Bootstrap 5 + Bootstrap Icons loaded from jsdelivr CDN; no build step required.
