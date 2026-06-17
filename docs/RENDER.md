# Render Deployment

StudentFlow deploys to Render as a Docker web service. Do not use Render's Node auto-detected settings.

## Service

- Type: Web Service
- Runtime: Docker
- Root directory: blank
- Instance: Free is acceptable for demos
- Do not use the auto-detected Node runtime. The repository has `package.json` only for Vite assets; the backend is Laravel/PHP and must deploy with Docker.

You can either create the service manually with **Language = Docker** or deploy from the root `render.yaml` Blueprint.

## Environment Variables

Set these on Render:

```env
APP_NAME=StudentFlow
APP_ENV=production
APP_DEBUG=false
APP_URL=https://studentflow-rbog.onrender.com
APP_KEY=base64:REPLACE_WITH_GENERATED_KEY

DB_CONNECTION=pgsql
DB_HOST=aws-1-ap-northeast-2.pooler.supabase.com
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres.awlbobfhobzcjyyhiuxb
DB_PASSWORD=REPLACE_WITH_SUPABASE_PASSWORD

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=log

GOOGLE_CLIENT_ID=REPLACE_WITH_GOOGLE_WEB_CLIENT_ID
GITHUB_CLIENT_ID=Ov23lipHaQtpSjuQyWmi
GITHUB_CLIENT_SECRET=REPLACE_WITH_GITHUB_SECRET
```

`GOOGLE_CLIENT_ID` must be a Google OAuth **Web application** client ID. The Android client ID is only for Google Console package/SHA-1 ownership and causes Android status code `10` if used in the app/server token flow.

Generate `APP_KEY` locally with:

```powershell
C:\php\php.exe artisan key:generate --show
```

## OAuth URLs

Android GitHub callback URL:

```text
studentflow://oauth/github
```

Optional backend/browser GitHub callback:

```text
https://studentflow-rbog.onrender.com/api/auth/github/callback
```

Google must use a Web OAuth client ID in `GOOGLE_CLIENT_ID`. Also create a separate Android OAuth client in Google Console for package `com.studentflow.app` and the app signing SHA-1.

Android API base URL after deployment:

```java
https://studentflow-rbog.onrender.com/api/
```
