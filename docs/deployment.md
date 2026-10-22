# Deployment Guide

This section gives a checklist for deploying to production.

Prerequisites

- Server with PHP 8.3+, Composer, Node, and required PHP extensions (gd, mbstring, pdo, etc.)
- Web server (nginx or Apache)
- Database (MySQL/MariaDB recommended)
- Supervisor for queue workers

Build steps (example)

1. Pull code on server (or CI/CD pipeline):

   git checkout main
   git pull origin main

2. Install PHP deps:

   composer install --no-dev --optimize-autoloader

3. Build frontend assets:

   npm ci
   npm run build

4. Environment configuration:

   - Copy .env and configure environment variables.
   - Set APP_ENV=production, APP_DEBUG=false
   - Ensure APP_KEY is set.

5. Database migrations & seeders:

   php artisan migrate --force
   php artisan db:seed --force

6. Cache optimizations:

   php artisan config:cache
   php artisan route:cache
   php artisan view:cache

7. Permissions:

   - Ensure storage/ and bootstrap/cache/ are writable by web user.
   - Typical: chown -R www-data:www-data storage bootstrap/cache

8. Supervisor (queues):

   - Create a supervisor config to run `php artisan queue:work --sleep=3 --tries=3`.
   - Reload supervisor after adding config.

9. Web server config

- Nginx example: point document root to /path/to/project/public and configure fastcgi for PHP-FPM.
- See `mylaravel.conf` in repo for an example Apache/Nginx config.

Zero-downtime tips

- Run migrations in a safe way; avoid dropping columns inline.
- Use maintenance mode for breaking changes:

  php artisan down
  # run migration and deploy steps
  php artisan up

Backups

- Use database dumps and archive public/assets and storage when performing upgrades.
- The repository contains backup utilities (copy_backup.py and backup/) — review before use.

CI/CD

- Prefer building assets inside CI and deploying artifacts.
- Add health checks for the application and queue worker status.
