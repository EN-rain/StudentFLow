# Render Deployment

StudentFlow deploys to Render as a Docker web service. Do not use Render's Node auto-detected settings.

## Service

- Type: Web Service
- Runtime: Docker
- Root directory: blank
- Instance: Free is acceptable for demos

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

GOOGLE_CLIENT_ID=919040220334-psvoce66g4mcim0csum12mujhlmqk6oe.apps.googleusercontent.com
GITHUB_CLIENT_ID=Ov23lipHaQtpSjuQyWmi
GITHUB_CLIENT_SECRET=REPLACE_WITH_GITHUB_SECRET
```

Generate `APP_KEY` locally with:

```powershell
C:\php\php.exe artisan key:generate --show
```

## OAuth URLs

GitHub callback:

```text
https://studentflow-rbog.onrender.com/api/auth/github/callback
```

Android API base URL after deployment:

```java
https://studentflow-rbog.onrender.com/api/
```
