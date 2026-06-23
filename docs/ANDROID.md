# StudentFlow Android Client

Native Java Android client for the StudentFlow Laravel API.

## Configuration

```text
Package:    com.studentflow.app
Min SDK:    23
Target SDK: 35
Java:       17
UI:         XML layouts and Material Components
Networking: Retrofit, Gson, and OkHttp
Auth store: AndroidX Security
```

Open the `android/` directory in Android Studio.

## API URL

The base URL is defined in:

```text
android/app/src/main/java/com/studentflow/app/Constants.java
```

For an Android emulator connected to a local Laravel server:

```text
http://10.0.2.2:8000/api/
```

For a physical device, use the computer's local network IP address.

## Authentication

Teacher and administrator accounts use:

```text
POST /api/auth/login
```

Student social sign-in uses:

```text
POST /api/auth/google
POST /api/auth/github
```

Provider credentials must be supplied through Laravel environment values. Do not hard-code OAuth client secrets in Android source code.

Google Sign-In requires:

- a Google Web OAuth client ID for token verification
- a separate Android OAuth client registered for package `com.studentflow.app`
- the signing certificate SHA-1

Provide the Web OAuth client ID through the `GOOGLE_WEB_CLIENT_ID` Gradle property or environment variable.

GitHub authentication requires the Laravel backend to exchange the authorization code. Keep `GITHUB_CLIENT_SECRET` on the server only.

## Main modules

Students can access:

- dashboard and profile
- classes and announcements
- assignments
- grades and attendance
- exams and submissions
- classroom join requests

Teachers can access:

- classes and students
- attendance and grades
- reports
- exams and submission review
- password changes

## Build

From Android Studio, sync Gradle and run the `app` configuration.

Command-line debug build:

```bash
cd android
gradle :app:assembleDebug
```

APK output:

```text
android/app/build/outputs/apk/debug/app-debug.apk
```

Use Android Studio's bundled JDK or another Java 17 installation.

## Local backend

Start Laravel before running the Android app:

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

## Security notes

- Do not store provider client secrets in the Android project.
- Use HTTPS outside local development.
- Keep bearer tokens in encrypted storage.
- Disable HTTP cleartext traffic in production builds.
- Use separate OAuth credentials for development and production.

## Teacher mobile vs teacher web parity audit

This table compares each teacher/admin web route group under `role:admin,teacher` in `routes/web.php` with the corresponding mobile API endpoints in `routes/api.php`. "Status" indicates whether the surfaces are at parity, an endpoint needs to be added, or a gap is intentionally deferred.

| Teacher-web feature | Mobile API endpoint | Status |
|---|---|---|
| Classes CRUD — list, create, read, update, delete | `GET/POST/PUT/PATCH/DELETE /api/classes[/{class}]` | Covered |
| Class enrollments — read, create, update, delete | `GET/POST/PUT/DELETE /api/classes/{class}/enrollments[/{student}]` | Covered |
| Class join requests — list, review (approve/reject) | `GET /api/classes/{class}/join-requests`, `PATCH /api/join-requests/{joinRequest}` | Covered |
| Students CRUD — list, create, read, update, delete | `GET/POST/PUT/PATCH/DELETE /api/students[/{student}]` | Covered |
| Attendance — list, read, save, history | `GET /api/attendance`, `PUT /api/attendance/{attendance}`, `POST /api/attendance` (save equivalent), `DELETE /api/attendance/{attendance}` | Covered |
| Attendance — bulk mark all present | `POST /api/attendance/mark-all-present` | Deferred — web `/attendance/{class}` save screen handles bulk entries per row |
| Attendance — per-student stats | `GET /api/attendance/student/{studentId}/stats` | Deferred — not surfaced on web; teacher infers from `/attendance/{class}/history` view |
| Grades — list, read, save per-class | `GET /api/classes/{class}/students/{studentId}/student-grades`, `POST /api/classes/{class}/students/{studentId}/student-grades`, `GET /api/classes/{class}/students/{studentId}/final-grade` | Covered |
| Grades — categories CRUD | `GET/POST/PUT/DELETE /api/classes/{class}/grade-categories[/{category}]` | Covered |
| Grades — items CRUD | `GET/POST/PUT/DELETE /api/classes/{class}/grade-items[/{item}]` | Covered |
| Assignments — list, create, read, update, delete | `GET/POST/PUT/DELETE /api/assignments[/{assignment}]` | Covered |
| Assignments — submissions read + create | `GET/POST /api/assignments/{assignment}/submissions` | Covered |
| Exams — list, create, read, publish | `GET/POST /api/exams[/{exam}]`, `POST /api/exams/{exam}/publish` | Covered |
| Exams — submission audit log | `GET /api/exams/{exam}/audit` | Deferred — not surfaced on web; admin/teacher can use mobile app for forensic detail |
| Exams — update/delete | n/a (neither surface has PUT/DELETE) | Deferred — exams are immutable after publish by design (consistent across both surfaces) |
| Announcements — list, create, read, update, delete | `GET/POST/PUT/DELETE /api/announcements[/{announcement}]` | Covered |
| Reports — index + show per type | `GET /api/reports/{type}` | Covered |
| Reports — **PDF export** | **MISSING — needs `GET /api/reports/{type}/pdf`** | **Add endpoint (Phase 3 step 4)** |
| Reports — **CSV export** | **MISSING — needs `GET /api/reports/{type}/csv`** | **Add endpoint (Phase 3 step 4)** |
| Dashboard stats | `GET /api/dashboard/stats` | Covered |

### Summary of gaps to patch in Phase 3 step 4

The mobile app currently cannot download a PDF or CSV copy of any of the seven report types from the API even though the web UI exposes both. This blocks offline sharing and any third-party integration that wants to ingest roster/grade data. The fix is two new API routes that delegate to the existing `App\Http\Controllers\Web\ReportController::pdf` and `::csv` methods (extracted behind a shared `ReportExportService` if duplication exceeds ~30 lines).

1. `GET /api/reports/{type}/pdf` under `role:admin,teacher` — returns the same Dompdf stream the web route returns today. Content-Type: `application/pdf`.
2. `GET /api/reports/{type}/csv` under `role:admin,teacher` — returns the same CSV stream the web route returns today. Content-Type: `text/csv`.

Both routes accept the same `{type}` whitelist used by the web routes: `student-profile|attendance|grades|class-performance|missing-assignments|failing-grades|frequent-absences`. Each must be covered by a feature test asserting 200 + correct Content-Type for admin and teacher, and 403 for student.

### Deferred items — rationale

- **Attendance bulk "mark all present"** — the web per-class attendance screen (`/attendance/{class}`) already supports marking every student present in one save action by pre-checking the "present" radio for each row. A separate bulk endpoint is therefore redundant for the web UI, and the mobile app currently does not have a dedicated bulk-marker screen. If the mobile app later adds a "first day of term" workflow, this endpoint is ready.
- **Per-student attendance stats** — the API returns aggregated Present/Absent/Late counts. The web history view (`/attendance/{class}/history`) renders the same data inline; surfacing it as a separate endpoint adds noise without value.
- **Exam audit log** — a forensic detail screen for tracking individual exam events (view, start, submit, expiry). Not part of the teacher web UX; admins who need it use the mobile app or the existing admin activity-log view.
- **Exam PUT/DELETE** — by design, exams are immutable after `publish` to preserve the integrity of submitted attempts. Both surfaces respect this invariant.

### Verification of parity

After step 4 lands the two new export endpoints, every row in this table is either Covered or Deferred, and no teacher-web feature is missing a corresponding mobile API counterpart.
