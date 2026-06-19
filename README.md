# StudentFlow

Student management system with a Laravel web application, Laravel API, and native Java Android client.

## Structure

```text
app/          Laravel controllers, models, middleware, mail, and services
routes/       Web, API, and console routes
resources/    Blade views, CSS, and JavaScript
database/     Migrations, seeders, SQLite databases, and factories
tests/        PHPUnit feature tests
android/      Native Java Android application
docs/         API, Android, deployment, and user documentation
scripts/      PowerShell QA scripts
```

## Stack

- PHP 8.2 and Laravel 12
- Laravel Sanctum bearer-token authentication
- Blade, Vite, CSS, and JavaScript
- SQLite for local development
- Dompdf for PDF reports
- Native Android with Java 17 and XML layouts
- Retrofit, Gson, OkHttp, Material Components, and AndroidX Security
- PHPUnit and Laravel Pint

## Features

- Administrator, teacher, and student roles
- Teacher and student management
- Classes and enrollment
- Classroom join requests
- Attendance
- Grade categories, grade items, and student scores
- Assignments and submissions
- Announcements and email delivery
- Exams, questions, attempts, answers, and magic exam links
- Student Google and GitHub sign-in
- Reports and PDF export
- Activity logs and school settings

## Requirements

- PHP 8.2 or newer
- Composer
- Node.js and npm
- SQLite or another Laravel-supported database
- Android Studio with Android SDK 35
- Java 17

## Web and API setup

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Create the local SQLite database when it does not exist:

```bash
mkdir -p database
touch database/database.sqlite
```

Windows Command Prompt:

```cmd
type nul > database\database.sqlite
```

Run migrations and seed data:

```bash
php artisan migrate --seed
```

Install and build frontend assets:

```bash
npm install
npm run build
```

Start the application:

```bash
php artisan serve
```

Default URL:

```text
http://127.0.0.1:8000
```

## Seed data

Starter data is controlled through environment values.

For a clean environment:

```text
STUDENTFLOW_SEED_STARTER_DATA=false
```

For local development or QA:

```text
STUDENTFLOW_SEED_STARTER_DATA=true
```

The seed passwords for administrator, teacher, and student accounts are also configured through `.env`. Do not use development credentials in production.

When starter data is enabled, the seeder creates an administrator, teachers, students, classes, enrollments, assessments, grades, and attendance records.

## Authentication

Web sessions are used by the Blade application. API clients use Laravel Sanctum bearer tokens.

Main authentication routes:

```text
POST /api/auth/login
POST /api/auth/google
POST /api/auth/github
POST /api/auth/forgot-password
POST /api/auth/reset-password
GET  /api/auth/me
POST /api/auth/change-password
POST /api/auth/logout
```

Google and GitHub student sign-in require provider credentials in `.env`.

## Android setup

Open the `android/` directory in Android Studio.

Android configuration:

```text
Package:    com.studentflow.app
Min SDK:    23
Target SDK: 35
Java:       17
```

The API base URL is defined in:

```text
android/app/src/main/java/com/studentflow/app/Constants.java
```

For an Android emulator connected to a local Laravel server, use:

```text
http://10.0.2.2:8000/api/
```

For a physical device, use the computer's local network IP address.

Google Sign-In requires a Web OAuth client ID supplied through the `GOOGLE_WEB_CLIENT_ID` Gradle property or environment variable. Keep provider client secrets in the Laravel environment only.

Build the debug APK from Android Studio or Gradle:

```bash
cd android
gradle :app:assembleDebug
```

APK output:

```text
android/app/build/outputs/apk/debug/app-debug.apk
```

## API modules

```text
/api/admin
/api/classes
/api/students
/api/attendance
/api/assignments
/api/announcements
/api/exams
/api/reports
/api/student
```

The complete route list is documented in [`docs/API.md`](docs/API.md).

## Tests and checks

Run backend tests:

```bash
php artisan test
```

Check PHP formatting:

```bash
vendor/bin/pint --test
```

Rebuild the database and run seed data:

```bash
php artisan migrate:fresh --seed
```

PowerShell QA scripts:

```powershell
.\scripts\qa-all.ps1
.\scripts\qa-api.ps1
.\scripts\qa-web.ps1
```

## Deployment

Deployment files include `Dockerfile`, `render.yaml`, and scripts under `docker/`.

References:

- [`docs/API.md`](docs/API.md)
- [`docs/ANDROID.md`](docs/ANDROID.md)
- [`docs/DEPLOYMENT.md`](docs/DEPLOYMENT.md)
- [`docs/RENDER.md`](docs/RENDER.md)
- [`docs/USER_MANUAL.md`](docs/USER_MANUAL.md)

Do not commit `.env`, OAuth secrets, access tokens, or production database files.
