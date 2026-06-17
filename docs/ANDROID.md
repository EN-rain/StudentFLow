# Android Client Scaffold

This directory contains a native Java Android scaffold for StudentFlow.

- Package: `com.studentflow.app`
- Min SDK: 23
- UI: XML layouts with Material Components
- Networking: Retrofit + Gson
- Auth storage: `SharedPreferences` through `TokenStore`
- Default API URL: `http://10.0.2.2:8000/api/` in `Constants.java`

Open the `android/` directory in Android Studio. Start the Laravel server on port 8000 before logging in from the Android emulator.

Seeded teacher credentials from the backend README work against `/api/auth/login`, for example `john.reyes` / `Teacher123!`.

Student login is role-aware and uses backend social endpoints:

- Google: `POST /api/auth/google`
- GitHub: `POST /api/auth/github`

For local QA only, the backend accepts `test-google:{student_email}` and `test-github:{student_email}`. Real Google/GitHub sign-in requires provider credentials in Laravel `.env`.

Students can use the Android app to view classes, announcements, assignments, grades, attendance, and exams. Teachers can create quick exams, publish per-student magic links, and audit submissions from the Android `Exams` screen.

## Build

1. Install Android Studio with Android SDK 35.
2. Open the `android/` folder, not the Laravel project root.
3. Let Android Studio sync Gradle.
4. Start Laravel:

   ```cmd
   C:\php\php.exe artisan serve --host=127.0.0.1 --port=8000
   ```

5. Run the app on an emulator.

To change the backend URL, edit `android/app/src/main/java/com/studentflow/app/Constants.java`.

This workspace has been verified with a temporary Gradle 8.7 distribution and Android Studio's bundled JBR:

```powershell
$env:JAVA_HOME='C:\Program Files\Android\Android Studio1\jbr'
$env:PATH="$env:JAVA_HOME\bin;$env:PATH"
gradle :app:assembleDebug
```

The debug APK is generated at:

```text
android/app/build/outputs/apk/debug/app-debug.apk
```

Use Android Studio's Gradle sync/build buttons if `gradle` is not on PATH.
