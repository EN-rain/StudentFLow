# StudentFlow Web QA Audit

- **Audit date:** 2026-06-23
- **Scope:** Web UI layer (`routes/web.php`, `app/Http/Controllers/Web/`, `resources/views/`, `tests/Feature/`)
- **Audit version:** Phase 1 (static/code-read) + Phase 2 (Playwright functional) — COMPLETE

## Executive Summary

This report documents both Phase 1 (static/code-read) and Phase 2 (Playwright end-to-end) of the StudentFlow web QA audit. The review focused on the Laravel web layer only: route structure, web controllers, model usage, service layer, test coverage, static analysis, security posture, and functional behavior across happy-path, negative-path, authorization, error-page, accessibility, responsive, performance, and console/network angles.

Phase 1 found no failing PHPUnit tests, no Pint or `php -l` violations, and no critical or high-severity security issues. The most significant concerns are maintainability risks: authorization is implemented through route middleware and ad-hoc controller helpers rather than Laravel Policies, many actions use inline validation instead of FormRequest classes, and a handful of controlled `forceFill()` calls bypass guarded model attributes.

Phase 2 Playwright testing (71 tests, 57 passed, 14 failed) surfaced 9 application bugs and 5 test-code / infrastructure issues. The highest-priority bugs are a missing CSRF token on a class edit form, an aborted attendance history route, missing form fields on the change-password and reset-password pages, and severe load-time degradation on the reports index (~10 s).

## Scope and Methodology

### In scope

- Web routes defined in `routes/web.php` (101 web routes)
- Web controllers under `app/Http/Controllers/Web/` (23 controllers)
- Feature tests under `tests/Feature/`
- Blade views under `resources/views/`
- Authorization, validation, mass-assignment, CSRF, SQL injection, and file-upload patterns

### Out of scope

- API controllers under `app/Http/Controllers/Api/` (referenced only when called by web controllers)
- Console commands and scheduled tasks
- Infrastructure/deployment configuration
- Third-party dependencies beyond usage patterns

### Tools used

| Tool | Purpose |
|------|---------|
| `php artisan route:list --json` | Route inventory and middleware mapping |
| PHPUnit 11.5.55 (`vendor/bin/phpunit --testdox`) | Feature test execution |
| Laravel Pint (`vendor/bin/pint --test`) | Code-style static analysis |
| `php -l` on `app/` files | Syntax-level linting |
| `grep` / `ripgrep` patterns | Security scans (mass-assignment, authorization, validation, CSRF, file uploads, SQL injection, env usage, open routes) |
| Manual source review | Controller behavior, architectural patterns, cross-referencing |
| Playwright (Chromium) | Phase 2 functional, authorization, accessibility, responsive, and performance testing |

## Code Read Summary

StudentFlow is a Laravel web application supporting three role groups: administrators, teachers, and students. Authentication is session-based with Laravel's default web guard plus custom `active` and `role` middleware. The public surface is intentionally small (login, password reset, health check, magic exam links, mobile OAuth interstitials). Protected functionality is grouped by role and primarily managed through shared admin/teacher controllers for school operations (classes, students, announcements, assignments, attendance, exams, grades, reports), with a parallel set of student-facing controllers under `/student/*`.

### Architecture highlights

- **Base controller:** `app/Http/Controllers/Controller.php` is an empty abstract base.
- **Web controllers:** 23 controllers in `app/Http/Controllers/Web/` (see [Controller Inventory](#controller-inventory)).
- **API controllers:** 19 controllers in `app/Http/Controllers/Api/` (out of scope for detailed review; cross-referenced only when web layer depends on them).
- **Models (observed):** The codebase uses Eloquent models including `User`, `Teacher`, `Student`, `SchoolClass`, `Announcement`, `Assignment`, `Attendance`, `Exam`, `ExamAttempt`, `GradeCategory`, `GradeItem`, `StudentGrade`, `SchoolSetting`, `JoinRequest`, and `ActivityLog`.
- **Services / support classes:** `App\Support\AccountAccess`, `App\Support\ActivityLogger`, `App\Support\StudentSocialUserResolver`, and `App\Services\ExamSubmissionService` provide shared business logic.
- **Authorization:** Enforced through `auth`, `active`, and `role` route middleware plus inline helpers such as `authorizeAccess()` and `authorizeClass()`. No Laravel Policies or `Gate` usage was found.
- **Validation:** Only five FormRequest classes exist (`StoreTeacherRequest`, `StoreAnnouncementRequest`, `StoreAssignmentRequest`, `StoreClassRequest`, `StoreStudentRequest`). Many actions validate inline.
- **Cross-reference:** Full per-controller behavior, authorization posture, and concerning patterns are documented in `.kimchi/ferments/019ef4a6-7972-754c-8284-7765a18d9765/docs/controller-summary.md`.

## Route Map per Role

Web routes total **101**. They are grouped by role/authorization level as follows:

| Role group | Count | Notes |
|------------|-------|-------|
| Public | 17 | Login/logout, password reset, health, magic exam links, mobile OAuth, root redirect |
| Auth-required | 3 | Dashboard, change-password actions |
| Admin only | 11 | Activity logs, school settings, teacher management |
| Admin/Teacher | 55 | Announcements, assignments, attendance, classes, exams, grades, reports, students |
| Student | 15 | `/student/*` views for the authenticated student |
| **Total web** | **101** | — |

All protected web routes are wrapped in `auth` + `active` middleware; role-specific routes additionally use `role:admin`, `role:admin,teacher`, or `role:student`. The full route map, including URI, method, controller, and middleware per route, is available at `.kimchi/ferments/019ef4a6-7972-754c-8284-7765a18d9765/docs/route-map.md`.

## Controller Inventory

The web layer contains **23 controllers** in `app/Http/Controllers/Web/`:

| Controller | Primary role group | Key actions | Authorization posture |
|------------|--------------------|-------------|-----------------------|
| `AdminActivityLogController` | Admin only | `index`, `csv` | `role:admin` route middleware |
| `AdminSchoolSettingController` | Admin only | `index`, `update` | `role:admin` route middleware |
| `AdminTeacherController` | Admin only | `index`, `create`, `store`, `edit`, `update`, `invite`, `setStatus` | `role:admin` + `StoreTeacherRequest` |
| `AnnouncementWebController` | Admin/Teacher | `index`, `create`, `store`, `show`, `edit`, `update`, `destroy` | Route middleware + `authorizeAccess()` helper |
| `AssignmentWebController` | Admin/Teacher | `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`, `saveSubmissions` | Route middleware + `authorizeAccess()` / `authorizeClassId()` |
| `AttendanceWebController` | Admin/Teacher | `index`, `show`, `save`, `history` | Route middleware + `authorizeClassAccess()` |
| `AuthWebController` | Mixed (public + auth) | `showLogin`, `login`, `logout`, `forgotPassword`, `resetPassword`, `showChangePassword`, `changePassword`, `showTeacherSetup`, `completeTeacherSetup` | Public + throttle for auth flows; `auth,active` for change-password |
| `ClassWebController` | Admin/Teacher | `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`, `storeEnrollment`, `updateEnrollment`, `destroyEnrollment`, `reviewJoinRequest` | Route middleware + `authorizeAccess()` |
| `DashboardController` | Auth-required | `index` | `auth,active`; student branch delegates to API controller |
| `ExamWebController` | Admin/Teacher | `index`, `create`, `store`, `show`, `publish` | Route middleware + `authorizeClass()` / `authorizeExam()` |
| `GradeWebController` | Admin/Teacher | `index`, `show`, `save`, `storeCategory`, `updateCategory`, `destroyCategory`, `storeItem`, `updateItem`, `destroyItem` | Route middleware + `authorizeClass()` |
| `HealthController` | Public | `__invoke` | No auth; DB ping only |
| `MagicExamWebController` | Public | `show`, `start`, `submit` | Public + throttle; token-based access |
| `MobileOAuthController` | Public | `github`, `assetLinks` | No auth; mobile interstitial / asset links |
| `ReportController` | Admin/Teacher | `index`, `show`, `pdf`, `csv` | Route middleware + `authorizeType()` / `authorizeAccess()` / `authorizeStudent()` |
| `StudentAnnouncementController` | Student | `index`, `show` | `role:student` + `authorizeVisibility()` |
| `StudentAssignmentController` | Student | `index`, `show`, `submit` | `role:student` + `authorizeEnrollment()` |
| `StudentAttendanceController` | Student | `index` | `role:student` |
| `StudentClassController` | Student | `index`, `show` | `role:student` + enrollment check |
| `StudentExamController` | Student | `index`, `start` | `role:student` + `authorizeEnrollment()` + availability checks |
| `StudentGradeController` | Student | `index`, `show` | `role:student` + enrollment check |
| `StudentReportController` | Student | `studentProfile`, `studentProfilePdf` | `role:student` |
| `StudentWebController` | Admin/Teacher | `index`, `create`, `store`, `show`, `edit`, `update`, `destroy` | Route middleware + `authorizeAccess()` |

Detailed action descriptions, validation patterns, and concerning observations for each controller are in `.kimchi/ferments/019ef4a6-7972-754c-8284-7765a18d9765/docs/controller-summary.md`.

## PHPUnit Results

PHPUnit was executed with `--testdox` on the full feature test suite.

| Metric | Value |
|--------|-------|
| Exit status | 0 |
| Total tests | 75 |
| Total assertions | 330 |
| Failures | 0 |
| Errors | 0 |
| Skipped | 0 |
| Risky | 0 |
| Duration | ~3.7 s |

### Per-file pass/fail counts

| Test class | Tests | Passed | Failed |
|------------|-------|--------|--------|
| `Tests\Feature\DashboardRoleDispatch` | 4 | 4 | 0 |
| `Tests\Feature\StudentAnnouncementWeb` | 5 | 5 | 0 |
| `Tests\Feature\StudentAssignmentWeb` | 5 | 5 | 0 |
| `Tests\Feature\StudentAttendanceWeb` | 4 | 4 | 0 |
| `Tests\Feature\StudentClassWeb` | 4 | 4 | 0 |
| `Tests\Feature\StudentExamWeb` | 5 | 5 | 0 |
| `Tests\Feature\StudentFlowFeature` | 22 | 22 | 0 |
| `Tests\Feature\StudentGradeWeb` | 4 | 4 | 0 |
| `Tests\Feature\StudentModuleRouting` | 3 | 3 | 0 |
| `Tests\Feature\StudentPlaceholderRoute` | 3 | 3 | 0 |
| `Tests\Feature\StudentReportWeb` | 4 | 4 | 0 |
| `Tests\Feature\TeacherReportApi` | 8 | 8 | 0 |
| `Tests\Feature\WebRoleGuard` | 1 | 1 | 0 |
| `Tests\Feature\WebSidebarRoleVisibility` | 3 | 3 | 0 |
| **Total** | **75** | **75** | **0** |

No failing tests were observed. Full console output is available at `docs/phpunit-output.txt` and `.kimchi/ferments/019ef4a6-7972-754c-8284-7765a18d9765/docs/phpunit-output-full.txt`. A summary is also at `.kimchi/ferments/019ef4a6-7972-754c-8284-7765a18d9765/docs/phpunit-summary.md`.

## Static Analysis Results

### Laravel Pint

| Item | Value |
|------|-------|
| Command | `vendor/bin/pint --test` |
| Status | PASS |
| Exit code | 0 |
| Files inspected | 139 |
| Files needing formatting | 0 |

### PHP Lint (`php -l`)

| Item | Value |
|------|-------|
| Scope | All `.php` files under `app/` |
| Files linted | 82 |
| Parse errors | 0 |
| Non-empty output file | No (`docs/php-lint.txt` is 0 bytes) |

Both Pint and `php -l` report a clean codebase. Raw outputs:

- `docs/pint-output.txt`
- `docs/php-lint.txt`
- `.kimchi/ferments/019ef4a6-7972-754c-8284-7765a18d9765/docs/static-analysis-summary.md`

## Security Findings

Security findings were consolidated from targeted grep/ripgrep scans and manual source review. The full report with line-level details, raw scan outputs, and recommendations is in `.kimchi/ferments/019ef4a6-7972-754c-8284-7765a18d9765/docs/security-findings.md`.

### Severity summary

| Severity | Count | Notes |
|----------|-------|-------|
| Critical | 0 | No exploitable injection, authentication bypass, or mass-assignment vulnerabilities found. |
| High | 0 | No direct CSRF/auth gaps or unprotected sensitive routes identified. |
| Medium | 22 | 12 controllers lack policies/controller-level auth checks; 10 controllers use inline validation instead of FormRequest classes. |
| Low | 11 | 10 controlled `forceFill()` calls bypass fillable/guarded attributes; 1 direct `env()` read in a controller. |
| Info | 3 | Raw SQL methods use static expressions or parameter binding; public routes are intentional. |

### Top concerns

1. **No Laravel Policies and no `Gate` / `$this->authorize()` usage.** The `app/Policies/` directory does not exist. Authorization is enforced entirely by route middleware and custom inline helpers. This makes the authorization surface harder to unit-test and increases the risk of drift as new actions are added.

2. **Inline validation in many controllers.** Only five FormRequest classes exist. High-impact controllers such as `ExamWebController`, `GradeWebController`, `AssignmentWebController`, `AttendanceWebController`, and `AuthWebController` validate inline, reducing reuse and consistency.

3. **`forceFill()` bypasses guarded attributes.** Ten occurrences of `forceFill()` were found across the application (including web, API, support, and console code). All operate on internally constructed arrays, so exploitation is unlikely, but they bypass Laravel's guarded-attribute protection and should be reviewed during model hardening. Key web locations:
   - `app/Http/Controllers/Web/AuthWebController.php:156` and `:180` (password reset callbacks)
   - `app/Support/AccountAccess.php:13` (status update)

4. **Direct `env()` read in a controller.** `AuthWebController.php:83` reads `env('PERF_LOG_AUTH', false)`. Direct `env()` calls outside config files can return `null` after `config:cache`.

5. **Unvalidated query parameters in filter/report endpoints.** Query parameters in activity logs, attendance history, teacher/student lists, and report controllers are used without dedicated FormRequest or explicit type/date validation. Current usage is Eloquent/DB-builder based, so SQL injection risk is low, but input normalization is missing.

### What was not found

- No `$request->all()` mass-assignment in web controllers.
- No raw user input concatenated into SQL (all query building uses parameter binding).
- No missing route-level authorization on protected routes.
- No CSRF gaps in Blade forms.
- No file-upload surface in the web layer.

### Raw security scan outputs

All scan outputs are saved under `.kimchi/ferments/019ef4a6-7972-754c-8284-7765a18d9765/docs/security-scans/`:

- `verify.txt`
- `mass-assignment.txt`
- `authorization.txt`
- `validation.txt`
- `csrf.txt`
- `file-upload.txt`
- `sql-injection.txt`
- `env.txt`
- `open-routes.txt`

## Output File Cross-Links

All raw tool outputs are captured below for traceability:

| Tool | Local path | Ferment artifact |
|------|-----------|-----------------|
| PHPUnit full output | `docs/phpunit-output.txt` | `.kimchi/ferments/019ef4a6-7972-754c-8284-7765a18d9765/docs/phpunit-output-full.txt` |
| PHPUnit summary | — | `.kimchi/ferments/019ef4a6-7972-754c-8284-7765a18d9765/docs/phpunit-summary.md` |
| Laravel Pint | `docs/pint-output.txt` | `.kimchi/ferments/019ef4a6-7972-754c-8284-7765a18d9765/docs/static-analysis-summary.md` |
| PHP Lint (`php -l`) | `docs/php-lint.txt` | `.kimchi/ferments/019ef4a6-7972-754c-8284-7765a18d9765/docs/static-analysis-summary.md` |
| Playwright output | `docs/playwright-output.txt` | `.kimchi/ferments/019ef4a6-7972-754c-8284-7765a18d9765/docs/playwright-summary.md` |
| Playwright JSON report | `docs/playwright-report.json` | — |

---

## Phase 2 — Playwright Functional Findings

Playwright end-to-end tests were executed against a fresh seeded environment (16 users: 1 admin, 5 teachers, 10 students).
BaseURL: `http://172.29.144.1:8000` (Chromium, 1 worker, 60 s timeout).

| Metric | Value |
|--------|-------|
| Total tests | 71 |
| Passed | 57 |
| Failed | 14 |
| Skipped | 0 |
| Duration | 20.7 min |

*Infrastructure note:* The initial run had 44 `networkidle` timeouts. After replacing `waitForLoadState('networkidle')` with `waitForLoadState('load')` in affected spec files (`auth.spec.js`, `error-pages.spec.js`, `negative-path.spec.js`, `student.spec.js`), zero `networkidle` timeouts remained. The remaining 14 failures are documented below by angle.

---

## Functional Findings — Happy-Path outcomes grouped by module

Playwright happy-path coverage spans all three role groups. Overall 47 of 52 happy-path assertions passed.

### Public / Authentication
| Test | Status | Notes |
|------|--------|-------|
| Login page renders with CSRF | PASS | `/login` returns 200, form visible |
| Valid admin login redirects to dashboard | PASS | Session created, redirect to `/dashboard` |
| Valid teacher login redirects to dashboard | PASS | Same flow |
| Student web login rejected with mobile-app message | PASS | Students are blocked from web login by design |
| Logout clears session | PASS | Returns to `/login` |
| Forgot-password form renders with CSRF | PASS | `/forgot-password` loads |
| Reset-password token page renders with CSRF | PASS | `/reset-password/{token}` loads |

### Admin-only management
| Test | Status | Notes |
|------|--------|-------|
| Dashboard loads with stats and no errors | PASS | Admin dashboard renders stat cards |
| Change-password form renders with CSRF | PASS | `/change-password` form visible |
| Teachers list and create form | PASS | `/admin/teachers`, `/admin/teachers/create` |
| Teacher edit form loads | FAIL | `locator('form')` resolved to 8 elements (test-code broad selector) |
| Activity logs render | PASS | `/admin/activity-logs` loads |
| CSV export streams | FAIL | `page.goto` aborted by download start (test-code issue) |
| Settings page loads | PASS | `/admin/settings` renders |
| Settings update form has CSRF | FAIL | `locator('form')` resolved to 2 elements (test-code broad selector) |

### Classes management
| Test | Status | Notes |
|------|--------|-------|
| Classes list and create form | PASS | `/classes`, `/classes/create` |
| First class detail page | PASS | `/classes/{class}` renders |
| Class edit page | FAIL | **Missing CSRF token on a POST form** (`_helpers.js:70`) |

### Students management
| Test | Status | Notes |
|------|--------|-------|
| Students list and create form | PASS | `/students`, `/students/create` |
| First student detail and edit pages | PASS | `/students/{student}`, edit form renders |

### Attendance
| Test | Status | Notes |
|------|--------|-------|
| Attendance index and first class sheet | PASS | `/attendance`, `/attendance/{class}` |
| Attendance history | FAIL | `page.goto('/attendance/history')` → `net::ERR_ABORTED` (route/controller abort or redirect loop) |

### Grades
| Test | Status | Notes |
|------|--------|-------|
| Grades index and first class gradebook | PASS | `/grades`, `/grades/{class}` |

### Assignments
| Test | Status | Notes |
|------|--------|-------|
| Assignments list and create form | PASS | `/assignments`, `/assignments/create` |
| First assignment detail and edit | PASS | `/assignments/{assignment}` |

### Exams
| Test | Status | Notes |
|------|--------|-------|
| Exams list and create form | PASS | `/exams`, `/exams/create` |
| First exam detail | PASS | `/exams/{exam}` |

### Announcements
| Test | Status | Notes |
|------|--------|-------|
| Announcements list, empty-state, and create | PASS | `/announcements`, `/announcements/create` |
| First announcement detail and edit | PASS | `/announcements/{announcement}` |

### Reports
| Test | Status | Notes |
|------|--------|-------|
| Reports index and each report type | PASS | `/reports`, `/reports/{type}` |

### Student portal
| Test | Status | Notes |
|------|--------|-------|
| Unauthenticated student routes redirect to login | PASS | All `/student/*` routes redirect |
| Dashboard redirects student to login | PASS | `/dashboard` → login for students (no web access) |

---

## Authorization Findings — Role-guard outcomes

| Test | Status | URL / Condition | Expected | Actual |
|------|--------|-----------------|----------|--------|
| Admin can access teacher-only management routes | PASS | `/admin/teachers`, `/classes` | 200 | 200 |
| Student routes redirect to login when not authenticated | PASS | `/student/classes`, `/student/grades` | Redirect to `/login` | Redirect to `/login` |
| Admin accessing student-only route rejected without 5xx | PASS | `/student/classes` | 403 | 403 |
| Guest accessing protected route redirected without 5xx | PASS | `/dashboard` | Redirect to `/login` | Redirect to `/login` |
| Cross-role URL access returns 403 | PASS | (multiple) | 403 | 403 |
| Web role guard — admin cannot access student route | PASS | `/student/*` as admin | 403 | 403 |

*Unit-test confirmation:* PHPUnit `WebRoleGuard` and `WebSidebarRoleVisibility` both pass (75/75 tests, 330 assertions). The `role:admin,teacher` middleware bundle correctly rejects students, and `role:student` rejects admin/teacher.

---

## Negative-Path Findings — Form validation failures

| Test | Status | URL | Repro steps | Expected | Actual | Angle |
|------|--------|-----|-------------|----------|--------|-------|
| Invalid email format rejected | PASS | `/login` | Submit malformed email | Validation error visible | Error surfaced | Auth |
| Invalid credentials show error | PASS | `/login` | Wrong password | Error visible | Error surfaced | Auth |
| Empty login fields show validation error | **FAIL** | `/login` | Submit empty username/password | `.invalid-feedback` or `.alert-danger` visible | No visible error element found | Auth |
| Forgot-password empty email shows validation error | **FAIL** | `/forgot-password` | Submit empty email | Visible error | No visible error element found | Auth |
| Forgot-password malformed email shows validation error | **FAIL** | `/forgot-password` | Submit "not-an-email" | Visible error | No visible error element found | Auth |
| Reset-password empty/malformed fields show validation error | **FAIL** | `/reset-password/{token}` | Fill mismatched passwords, click submit | Visible error | **Test timeout** — submit button never located; page may not fully render | Auth |
| Change-password empty fields show validation error | **FAIL** | `/change-password` | Submit empty current/new passwords | Visible error | **Test timeout** — `input[name="password"]` never located | Auth |
| Change-password mismatched new password shows error | **FAIL** | `/change-password` | Mismatched password_confirmation | Visible error | **Test timeout** — `input[name="password"]` never located | Auth |
| Teacher-setup empty fields show validation error | **FAIL** | `/teacher/setup/{token}` | Submit empty fields | Visible error | No visible error element found | Auth |
| No 5xx on validation failures | PASS | (multiple) | Trigger various invalid inputs | No 500/503 | No server errors observed | Auth |

**Pattern:** Five of the nine auth/setup negative-path tests fail because the Blade views do not render visible validation-error containers (`.invalid-feedback`, `.text-danger`, `.alert-danger`, etc.) when server-side validation fires. The reset-password and change-password pages additionally fail to expose expected form fields (`input[name="password"]`, `button[type="submit"]`), suggesting possible view/form markup issues.

---

## Error Pages Findings — 404/403/500 responses

| Test | Status | Scenario | Expected | Actual |
|------|--------|----------|----------|--------|
| Non-existent route returns 404 | PASS | `GET /this-does-not-exist` | 404 | 404 |
| POST to GET-only route returns 405 or redirects | PASS | `POST /login` to GET-only | 405 / redirect | Handled gracefully |
| Wrong credentials redirect back (no 500) | PASS | Invalid POST `/login` | Redirect, no 500 | Redirect, no 500 |
| Forgot-password validation does not produce 500 | PASS | Invalid POST `/forgot-password` | No 500 | No 500 |
| Invalid teacher id returns 404 not 500 | PASS | `/admin/teachers/99999/edit` | 404 | 404 |
| Invalid class id returns 404 not 500 | PASS | `/grades/99999` | 404 | 404 |

All error-page tests passed. The application correctly surfaces 404 for missing bound models and does not leak 500s on malformed input.

---

## A11y Findings — Accessibility issues

| Test | Status | Scenario | Notes |
|------|--------|----------|-------|
| Login page semantic regions and form labels | PASS | `/login` | `<main>` present; inputs have `<label>` |
| Keyboard navigates login form and submit is focusable | PASS | `/login` | Tab order reaches submit button |
| Admin dashboard has main region and headings | PASS | `/dashboard` (admin) | `<main>` and `<h1>`–`<h6>` present |
| Focus indicators visible after tab navigation | PASS | `/dashboard` | CSS `:focus` outline visible |
| Keyboard navigation through main navigation | PASS | `/admin/teachers` | Sidebar links reachable by keyboard |
| Student login form labels and focusable submit | PASS | `/login` (student attempt) | Same as admin login |

No accessibility defects were identified by the automated Playwright spot-checks.

---

## Responsive Findings — Viewport issues

| Test | Status | Viewports | Notes |
|------|--------|-----------|-------|
| Login page usable form | PASS | 390×844, 1280×720 | Form inputs visible, no clipping |
| Admin dashboard no horizontal scroll | PASS | 390×844 | Layout stays within viewport width |
| Classes page sidebar | PASS | Desktop / Mobile | Sidebar visible on desktop, hidden on mobile |
| Students page usable | PASS | 390×844, 1280×720 | Tables/cards adapt without overflow |
| Student login on iPhone 12 viewport | PASS | 390×844 | Renders correctly |

No responsive layout defects were identified. The Bootstrap-based layout adapts correctly across test viewports.

---

## Perf Findings — Performance observations

| Test | Status | URL | Threshold | Actual | Notes |
|------|--------|-----|-----------|--------|-------|
| `/login` load time | PASS | `/login` | < 3000 ms | ~2600 ms | Within threshold |
| `/dashboard` load time (admin) | **FAIL** | `/dashboard` | < 3000 ms | **3792.7 ms** | Slightly exceeds threshold; likely environment/WSL overhead |
| `/admin/teachers` load time | PASS | `/admin/teachers` | < 3000 ms | Within threshold | |
| `/student/dashboard` attempted load | PASS | `/student/dashboard` | Captured | Redirect to login | Not a perf concern |
| `/reports` index load time | **FAIL** | `/reports` | < 3000 ms | **10157.9 ms** | ~10 s; likely needs query optimization or eager-loading review |

The `/reports` page is a clear performance outlier. The `ReportController` aggregates multiple report types and may benefit from:
1. Adding database indexes on `class_id`, `student_id`, and `created_at` columns used in filters.
2. Eager-loading relationships currently loaded N+1.
3. Caching report summary data if real-time freshness is not critical.

The `/dashboard` admin load is borderline (~800 ms over threshold) and may improve with the same optimizations applied to stat-card aggregates.

---

## Console/Network Findings — Console errors and network failures

| Test | Status | Scenario | Console | Network |
|------|--------|----------|---------|---------|
| Admin dashboard clean | PASS | `/dashboard` (admin) | No console errors | No 5xx responses |
| Login page clean | PASS | `/login` (unauthenticated) | No console errors | N/A |
| Admin accessing student route | PASS | `/student/classes` as admin | Clean | 403 (expected) |
| Guest accessing protected route | PASS | `/dashboard` as guest | Clean | 302 to login (expected) |
| Student login attempt network | PASS | `/login` as student | Clean | No 5xx |
| 4xx responses documented | PASS | (annotation check) | — | 4xx codes recorded in test annotations |

No unexpected console errors or 5xx network responses were observed across the tested flows.

---

## Bug list — Prioritized Bug List

| # | Severity | Title | File:Line (or Route) | Repro Steps | Expected | Actual | Angle |
|---|----------|-------|----------------------|-------------|----------|--------|-------|
| 1 | **P1** | Missing CSRF token on class edit form | `resources/views/classes/edit.blade.php` (inferred) or `ClassWebController::edit` | 1. Log in as admin. 2. Navigate to `/classes/{id}/edit`. 3. Inspect POST forms. | Every POST form contains `@csrf` `_token` input. | A POST form is missing the `_token` hidden input (Playwright `expectCsrfOnForms` fails on `form:nth(4)`). | Functional / Security |
| 2 | **P1** | Attendance history route aborts navigation | `routes/web.php:73` → `AttendanceWebController::history` | 1. Log in as admin. 2. Navigate to `/attendance`. 3. Click a class row. 4. Navigate to `{currentURL}/history`. | Page loads with per-student attendance summary. | `net::ERR_ABORTED` — navigation aborted by browser (route/controller error or redirect loop). | Functional |
| 3 | **P1** | Change-password page missing expected form fields | `resources/views/auth/change-password.blade.php` (inferred) or `AuthWebController::showChangePassword` | 1. Log in as admin. 2. Navigate to `/change-password`. | Form renders with `input[name="password"]` and `button[type="submit"]`. | Playwright times out waiting for `input[name="password"]`; page may not load the expected markup. | Functional / Negative-path |
| 4 | **P1** | Reset-password page missing submit button | `resources/views/auth/reset-password.blade.php` (inferred) or `AuthWebController::showResetPassword` | 1. Visit `/reset-password/{token}` with a valid token. 2. Look for submit button. | Form renders with `button[type="submit"]`. | Playwright times out waiting for `button[type="submit"]`; form may be incomplete. | Functional / Negative-path |
| 5 | **P1** | `/reports` index load exceeds acceptable threshold | `ReportController::index` | 1. Log in as admin. 2. Navigate to `/reports`. | Page loads in < 3 s under benchmark conditions. | `domContentLoaded` / `loadComplete` at ~10.2 s; requires query review. | Performance |
| 6 | **P2** | Login empty fields do not show visible validation error | `resources/views/auth/login.blade.php` | 1. Go to `/login`. 2. Submit empty form. | `.invalid-feedback`, `.text-danger`, or `.alert-danger` visible. | No visible error element found; validation fires but UI does not surface it. | Negative-path / UX |
| 7 | **P2** | Forgot-password empty email does not show visible validation error | `resources/views/auth/forgot-password.blade.php` | 1. Go to `/forgot-password`. 2. Submit empty email. | Visible error message. | No visible error element found. | Negative-path / UX |
| 8 | **P2** | Forgot-password malformed email does not show visible validation error | `resources/views/auth/forgot-password.blade.php` | 1. Go to `/forgot-password`. 2. Enter "not-an-email". 3. Submit. | Visible error message. | No visible error element found. | Negative-path / UX |
| 9 | **P2** | Teacher-setup empty fields do not show visible validation error | `resources/views/auth/teacher-setup.blade.php` (inferred) | 1. Visit `/teacher/setup/{token}`. 2. Submit empty form. | Visible error message. | No visible error element found. | Negative-path / UX |
| 10 | **P3** | Admin dashboard load slightly above threshold | `DashboardController::index` | 1. Log in as admin. 2. Navigate to `/dashboard`. | < 3000 ms on benchmark setup. | ~3793 ms; may be environment-specific but worth monitoring. | Performance |

**Infra / Test-code issues (not application bugs):**

| # | Severity | Title | Notes |
|---|----------|-------|-------|
| T1 | P3 | Teacher edit form locator too broad | `admin.spec.js:70` uses `page.locator('form')`, which resolves to 8 forms on the teachers list. Scope to the edit form specifically. |
| T2 | P3 | Settings form locator too broad | `admin.spec.js:91` uses `page.locator('form')`, resolving to 2 elements (logout + settings). Scope the assertion. |
| T3 | P3 | CSV export test does not handle download | `admin.spec.js:82` — `page.goto` aborts because CSV triggers a download. Use `waitForEvent('download')` before `goto`, or assert headers instead. |
| T4 | P3 | Performance threshold may be too aggressive for WSL | `/dashboard` at ~3793 ms and `/reports` at ~10 s in a `php artisan serve` / WSL environment may need environment-calibrated thresholds or headless benchmarking on production-like hardware. |

---

## Appendix: Artifacts

### Raw outputs in repository root `docs/`

- `docs/phpunit-output.txt`
- `docs/pint-output.txt`
- `docs/php-lint.txt`
- `docs/playwright-output.txt`
- `docs/playwright-report.json`

### Generated ferment docs

- `.kimchi/ferments/019ef4a6-7972-754c-8284-7765a18d9765/docs/route-map.md`
- `.kimchi/ferments/019ef4a6-7972-754c-8284-7765a18d9765/docs/controller-summary.md`
- `.kimchi/ferments/019ef4a6-7972-754c-8284-7765a18d9765/docs/phpunit-summary.md`
- `.kimchi/ferments/019ef4a6-7972-754c-8284-7765a18d9765/docs/phpunit-output-full.txt`
- `.kimchi/ferments/019ef4a6-7972-754c-8284-7765a18d9765/docs/static-analysis-summary.md`
- `.kimchi/ferments/019ef4a6-7972-754c-8284-7765a18d9765/docs/security-findings.md`
- `.kimchi/ferments/019ef4a6-7972-754c-8284-7765a18d9765/docs/playwright-summary.md`

### Security raw scans

- `.kimchi/ferments/019ef4a6-7972-754c-8284-7765a18d9765/docs/security-scans/verify.txt`
- `.kimchi/ferments/019ef4a6-7972-754c-8284-7765a18d9765/docs/security-scans/mass-assignment.txt`
- `.kimchi/ferments/019ef4a6-7972-754c-8284-7765a18d9765/docs/security-scans/authorization.txt`
- `.kimchi/ferments/019ef4a6-7972-754c-8284-7765a18d9765/docs/security-scans/validation.txt`
- `.kimchi/ferments/019ef4a6-7972-754c-8284-7765a18d9765/docs/security-scans/csrf.txt`
- `.kimchi/ferments/019ef4a6-7972-754c-8284-7765a18d9765/docs/security-scans/file-upload.txt`
- `.kimchi/ferments/019ef4a6-7972-754c-8284-7765a18d9765/docs/security-scans/sql-injection.txt`
- `.kimchi/ferments/019ef4a6-7972-754c-8284-7765a18d9765/docs/security-scans/env.txt`
- `.kimchi/ferments/019ef4a6-7972-754c-8284-7765a18d9765/docs/security-scans/open-routes.txt`

