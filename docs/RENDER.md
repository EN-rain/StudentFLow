# StudentFlow on Render

StudentFlow deploys to Render as a Docker web service.

## Service configuration

- Service type: Web Service
- Runtime: Docker
- Root directory: repository root
- Dockerfile: `Dockerfile`
- Blueprint: `render.yaml`

Do not use Render's Node runtime. The backend is Laravel/PHP; `package.json` is only used to build frontend assets.

## Environment values

Set these in the Render service:

```env
APP_NAME=StudentFlow
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-service.onrender.com
APP_KEY=base64:replace_with_generated_key

DB_CONNECTION=pgsql
DB_HOST=your-postgres-host
DB_PORT=5432
DB_DATABASE=your-database
DB_USERNAME=your-database-user
DB_PASSWORD=your-database-password

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=log

STUDENTFLOW_SEED_STARTER_DATA=false
STUDENTFLOW_SEED_ADMIN_PASSWORD=replace_with_secure_password
```

Optional social sign-in values:

```env
GOOGLE_CLIENT_ID=your-google-web-client-id
GITHUB_CLIENT_ID=your-github-client-id
GITHUB_CLIENT_SECRET=your-github-client-secret
```

Generate `APP_KEY` locally:

```bash
php artisan key:generate --show
```

## Database

Use a managed PostgreSQL database such as Render PostgreSQL or another external PostgreSQL provider.

Do not place real database hosts, usernames, or passwords in repository documentation.

## OAuth callbacks

GitHub callback:

```text
https://your-service.onrender.com/api/auth/github/callback
```

Google requires a Web OAuth client ID for server-side token verification. Register a separate Android OAuth client for package `com.studentflow.app` and the signing SHA-1.

Keep OAuth client secrets in Render environment values only.

## Android API URL

Set the Android API base URL to:

```text
https://your-service.onrender.com/api/
```

## Deployment checks

After deployment, verify:

```text
GET /
POST /api/auth/login
GET /api/auth/me
```

Also test database writes, sessions, password reset email handling, social login when enabled, and Android API access.

## Notes

- Free services may sleep when inactive.
- Use `APP_DEBUG=false` in production.
- Do not enable starter data unless the deployment is for a demo or QA environment.
- Rotate any credentials previously committed or shared in logs.
