# StudentFlow

StudentFlow is a student management system built from `plan.md` with:

- Laravel 11 web dashboard and REST API
- Sanctum token authentication for API/Android clients
- Bootstrap 5 Blade dashboard
- SQLite by default, MySQL/MariaDB-ready through `.env`
- Native Java Android client scaffold under `android/`

## Install

```cmd
C:\php\php.exe C:\composer\composer.phar install --no-interaction --no-security-blocking
copy .env.example .env
C:\php\php.exe artisan key:generate
type nul > database\database.sqlite
C:\php\php.exe artisan migrate --seed
```

Run locally:

```cmd
C:\php\php.exe artisan serve --host=127.0.0.1 --port=8000
```

Open `http://127.0.0.1:8000`.

## Seeded Accounts

| Role | Username | Password |
| --- | --- | --- |
| Admin | `admin` | `Admin123!` |
| Teacher | `john.reyes` | `Teacher123!` |
| Teacher | `angela.cruz` | `Teacher123!` |
| Teacher | `roberto.delapena` | `Teacher123!` |

## Implemented Scope

- Authentication: login, logout, change password, disabled account checks, Laravel password reset links.
- Admin: teacher CRUD, enable/disable/reactivate accounts, activity logs with CSV export, school settings with history.
- Teacher workflows: classes, student records, class enrollment, attendance, grades/categories/items, assignments/submissions, announcements with enrolled-student email notifications.
- Student workflows: Google/GitHub-backed student account linking, student mobile APIs, student Android dashboard, exam taking through Android or same-domain magic links.
- Exams: teacher-created quizzes/exams, per-student magic links, submissions, audit stats, and automatic score sync to grade items.
- Reports: student profile, attendance, grades, class performance, missing assignments, failing grades, frequent absences; web/PDF/CSV plus JSON report API.
- Android: Java client scaffold with login, dashboard, classes, students, attendance, grades, assignments, announcements, reports, profile, and change password.

## API

Core API routes are under `/api/*` and require `Authorization: Bearer <token>` except login, forgot password, and reset password.

See [docs/API.md](docs/API.md) for endpoint groups and payload examples.

## Android

Open the `android/` directory in Android Studio. The default emulator API URL is:

```java
http://10.0.2.2:8000/api/
```

See [docs/ANDROID.md](docs/ANDROID.md).

## Documentation

- [API Reference](docs/API.md)
- [User Manual](docs/USER_MANUAL.md)
- [Deployment Guide](docs/DEPLOYMENT.md)
- [Render Deployment](docs/RENDER.md)
- [Android Build Guide](docs/ANDROID.md)

## Verification

Run the full local QA suite:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\qa-all.ps1
```

Focused checks:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\qa-api.ps1
powershell -ExecutionPolicy Bypass -File .\scripts\qa-web.ps1
```

The full script runs a fresh migration/seed, Laravel tests, API smoke checks, web smoke checks, and the Android debug build when the local Gradle/JDK paths are available.

```cmd
C:\php\php.exe artisan migrate:fresh --seed
C:\php\php.exe artisan route:list
C:\php\php.exe artisan test
```

Android Gradle verification requires Android Studio/SDK or Gradle on PATH.
