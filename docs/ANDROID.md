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
