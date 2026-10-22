# Development Guide

This section covers common developer workflows: running locally, code style, and tools.

Local development

- Start PHP server, queue listener and vite (concurrent):

  composer run dev

  This uses the `dev` script in composer.json and runs:
  - php artisan serve
  - php artisan queue:listen
  - artisan pail (logs)
  - npm run dev (vite)

- If you prefer manual start:

  php artisan serve
  npm run dev
  php artisan queue:listen --tries=1 --timeout=0

Code structure overview

- app/ — Laravel application code (Models, Controllers, Jobs, etc.)
- resources/views — Blade templates
- resources/js & resources/css — frontend assets
- public/ — public webroot and built assets
- routes/web.php — HTTP routes (both public and admin)
- database/migrations — migrations
- database/seeders — seeders

Common tasks

- Create a migration:

  php artisan make:migration create_example_table

- Create a controller:

  php artisan make:controller ExampleController

- Create a model + migration + factory:

  php artisan make:model Example -mfs

Testing

- Run the test suite:

  composer test

- PHPUnit config: phpunit.xml

Linting & code style

- PHP Pint is included. Run:

  vendor/bin/pint

- Follow PSR-12 and Laravel conventions.

Frontend

- Vite + Tailwind is configured in package.json. Run:

  npm run dev    # development with HMR
  npm run build  # build for production

Notes on repository-specific tooling

- file-watcher.* scripts and file-watcher.service are included to help with automatic backups or syncs. Review those scripts before enabling in production.
- copy_backup.py and backup/ directory contain backup utilities — used by operations.
