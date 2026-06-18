# Android Client Scaffold

This directory contains a native Java Android scaffold for StudentFlow.

- Package: `com.studentflow.app`
- Min SDK: 23
- UI: XML layouts with Material Components
- Networking: Retrofit + Gson
- Auth storage: `SharedPreferences` through `TokenStore`
- Default API URL: `https://studentflow-rbog.onrender.com/api/` in `Constants.java`

Open the `android/` directory in Android Studio. The default build points to the Render backend. For local emulator testing, temporarily set `Constants.API_BASE_URL` to `http://10.0.2.2:8000/api/`.

Teacher password login works against `/api/auth/login`, but bootstrap passwords now come from backend env variables or are randomized during seeding. Set `STUDENTFLOW_SEED_TEACHER_PASSWORD` before seeding if you need a known teacher password for QA.

Student login is role-aware and uses backend social endpoints:

- Google: `POST /api/auth/google`
- GitHub: `POST /api/auth/github`

For local QA only, the backend accepts `test-google:{student_email}` and `test-github:{student_email}`. Real Google/GitHub sign-in requires provider credentials in Laravel `.env`.

Configured Google Web OAuth client ID used by Android `requestIdToken`:

```text
919040220334-t58kckj5vih61ph3070r60obuiap0bkl.apps.googleusercontent.com
```

If Google sign-in returns status code `10`, replace `Constants.GOOGLE_WEB_CLIENT_ID` and Laravel `GOOGLE_CLIENT_ID` with a Google OAuth **Web application** client ID. Keep a separate Google OAuth **Android** client in Google Console for package `com.studentflow.app` and the signing SHA-1, but do not put the Android client ID in `Constants.java`.

Configured GitHub OAuth client ID:

```text
Ov23lipHaQtpSjuQyWmi
```

For Android GitHub login, set the GitHub OAuth app callback URL to the Laravel backend callback. Laravel exchanges the code and then redirects back into the app:

```text
https://studentflow-rbog.onrender.com/api/auth/github/callback
```

Keep `GITHUB_CLIENT_SECRET` only in Laravel `.env`; do not put it in Android code.

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
