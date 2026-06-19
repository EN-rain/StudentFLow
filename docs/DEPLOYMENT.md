# StudentFlow Deployment

StudentFlow can run on a PHP host, a container platform, or Render through the included Docker files.

## Server requirements

- PHP 8.2 or newer
- Composer
- PostgreSQL, MySQL, MariaDB, or SQLite
- Web server document root set to `public/`
- Writable `storage/` and `bootstrap/cache/`
- Node.js and npm when frontend assets are built on the server
- SMTP or another mail driver for password reset and announcement email delivery

## Production environment

```env
APP_NAME=StudentFlow
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
APP_KEY=

DB_CONNECTION=pgsql
DB_HOST=
DB_PORT=5432
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME=StudentFlow
```

OAuth values are required only when student social sign-in is enabled:

```env
GOOGLE_CLIENT_ID=
GITHUB_CLIENT_ID=
GITHUB_CLIENT_SECRET=
```

Seed configuration:

```env
STUDENTFLOW_SEED_STARTER_DATA=false
STUDENTFLOW_SEED_ADMIN_PASSWORD=
STUDENTFLOW_SEED_TEACHER_PASSWORD=
STUDENTFLOW_SEED_STUDENT_PASSWORD=
```

Keep starter data disabled in normal production deployments.

## Build and deploy

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Ensure these directories are writable by the application process:

```text
storage/
bootstrap/cache/
```

## Web server

The document root must point to:

```text
public/
```

Do not expose the repository root directly.

## Pre-deployment checks

```bash
php artisan test
vendor/bin/pint --test
php artisan route:list
npm run build
```

## Post-deployment checks

Verify:

```text
GET /
POST /api/auth/login
GET /api/auth/me
```

Also test:

- administrator login
- teacher login
- student social login when enabled
- class and student access rules
- attendance and grade updates
- PDF report generation
- email delivery
- Android API access over HTTPS

## Database notes

Use one database configuration per environment. Run migrations before serving new application code that depends on schema changes.

Back up the production database before destructive migrations or `migrate:fresh`. Never run `migrate:fresh --seed` in production.

## Security

- Set `APP_DEBUG=false`.
- Use HTTPS.
- Keep `.env`, OAuth secrets, mail credentials, and database passwords outside Git.
- Rotate credentials exposed in logs or repository history.
- Restrict database network access.
- Use production OAuth callback URLs.
- Review Laravel logs without publishing them through the web server.
