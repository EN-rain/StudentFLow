# StudentFlow

StudentFlow is a Laravel web application and native Java Android client for managing teachers, students, programming classes, attendance, grades, assignments, announcements, quizzes, exams, and classroom membership requests.

Both clients use the same Laravel API and database. Changes made on web or Android appear on the other client after refresh.

## Local installation

```cmd
C:\php\php.exe C:\composer\composer.phar install
copy .env.example .env
C:\php\php.exe artisan key:generate
type nul > database\database.sqlite
C:\php\php.exe artisan migrate --seed
C:\php\php.exe artisan serve --host=127.0.0.1 --port=8000
```

Open `http://127.0.0.1:8000`.

## Starter data

Production should normally use:

```env
STUDENTFLOW_SEED_STARTER_DATA=false
```

With starter data disabled, seeding creates only the initial administrator and school settings. Teachers, students, classes, subjects, assessments, and enrollments are created from the application.

For local development and QA, use:

```env
STUDENTFLOW_SEED_STARTER_DATA=true
STUDENTFLOW_SEED_ADMIN_PASSWORD=AdminPass123!
STUDENTFLOW_SEED_TEACHER_PASSWORD=TeacherPass123!
STUDENTFLOW_SEED_STUDENT_PASSWORD=StudentPass123!
```

These values are environment-controlled, not embedded as fixed application passwords. Change them before seeding another environment. Every user can later change their password from the web or Android password-change screen.

## Starter accounts

### Administrator

| Username | Password |
|---|---|
| `admin` | `AdminPass123!` |

### Teachers

All five teachers use `TeacherPass123!` unless `STUDENTFLOW_SEED_TEACHER_PASSWORD` is changed before seeding.

| Username | Assigned subject |
|---|---|
| `john.reyes` | Object-Oriented Programming with Java |
| `angela.cruz` | Introduction to Programming with Python |
| `roberto.delapena` | Web Application Development |
| `paolo.mercado` | Mobile Application Development |
| `sophia.tan` | Software Engineering and Testing |

### Students

All ten students use `StudentPass123!` unless `STUDENTFLOW_SEED_STUDENT_PASSWORD` is changed before seeding.

| Username | Verification |
|---|---|
| `2026-0001` | Verified: Google and GitHub linked |
| `2026-0002` | Not verified |
| `2026-0003` | Not verified |
| `2026-0004` | Not verified |
| `2026-0005` | Not verified |
| `2026-0006` | Not verified |
| `2026-0007` | Not verified |
| `2026-0008` | Not verified |
| `2026-0009` | Not verified |
| `2026-0010` | Not verified |

Never reuse these local starter passwords in production.

## Academic context

The starter dataset represents School Year `2025-2026`, Second Semester.

It contains exactly:

- 1 administrator
- 5 teachers
- 10 students
- 5 classes
- 1 programming-related subject per teacher
- Completed quizzes with submitted answers
- Completed examinations with submitted answers
- Upcoming quizzes
- Upcoming examinations

Each class has two enrolled students. Completed attempts include answer and score records. Upcoming assessments are published and assigned but not yet submitted.

## Student verification and classroom joining

A student is marked **Verified** only when both a verified Google account and a verified GitHub account are linked.

Verified students can enter a classroom join code from Android. The assigned teacher or an administrator can approve or reject the request from web or Android. Approval creates the enrollment record in the shared backend database.

## API and Android

Core routes are under `/api` and authenticated routes require a Sanctum bearer token.

Open the `android/` directory in Android Studio. Google Sign-In requires an Android OAuth client for package `com.studentflow.app`, the signing SHA-1, and the matching Web OAuth client ID.

## Verification

```cmd
C:\php\php.exe artisan migrate:fresh --seed
C:\php\php.exe artisan test
C:\php\php.exe vendor\bin\pint --test
```

Documentation:

- [API Reference](docs/API.md)
- [User Manual](docs/USER_MANUAL.md)
- [Deployment Guide](docs/DEPLOYMENT.md)
- [Render Deployment](docs/RENDER.md)
- [Android Build Guide](docs/ANDROID.md)
