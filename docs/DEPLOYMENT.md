# StudentFlow Deployment Guide

## Local Production Check

```cmd
C:\php\php.exe artisan migrate:fresh --seed
C:\php\php.exe artisan test
C:\php\php.exe artisan route:list
```

## Server Requirements

- PHP 8.2+
- Composer
- MySQL or MariaDB for production
- Web server pointing to `public/`
- Writable `storage/` and `bootstrap/cache/`
- SMTP credentials for real password reset email delivery

## Environment

Set production values in `.env`:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=studentflow
DB_USERNAME=studentflow
DB_PASSWORD=change-me
MAIL_MAILER=smtp
```

Then run:

```cmd
C:\php\php.exe artisan key:generate
C:\php\php.exe artisan migrate --force
C:\php\php.exe artisan config:cache
C:\php\php.exe artisan route:cache
C:\php\php.exe artisan view:cache
```

Actual live deployment requires hosting credentials and DNS access.
