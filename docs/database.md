# Database

This section describes the database layout, migrations, and how to import/export data.

Default connection

- .env.example defaults to sqlite for quick local setup. For production use MySQL/MariaDB.

Schema & Migrations

- All schema changes are managed with Laravel migrations located in database/migrations.
- To run migrations:

  php artisan migrate --force

Seeders

- Default seeders are in database/seeders. Run:

  php artisan db:seed

- A HomePageSectionSeeder seeds the homepage sections used by the admin home-page manager.

Export / Import

- The repository includes a SQL snapshot: stautoparts.sql
- There is also sample-products-import.csv used by the admin product import feature.

Example import (MySQL):

  mysql -u root -p stautoparts < stautoparts.sql

Product import

- Admin panel provides a Products > Import feature. There is a sample CSV at `sample-products-import.csv`.
- Use the admin import form at /admin/products/import to upload.

Backups

- Regular backups of database and /public/assets and /storage should be part of operations.
- There is a backup/ directory and copy_backup.py script included for reference — review before use.

Database tips

- When changing columns used by production code, prefer adding new columns and backfilling rather than destructive changes.
- Use transactions in migrations where appropriate.
