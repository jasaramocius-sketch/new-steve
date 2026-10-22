# Getting Started

This guide helps you get the project running locally.

Requirements

- PHP 8.3+
- Composer
- Node.js 18+ and npm
- MySQL / MariaDB or SQLite (project defaults to sqlite in .env.example)
- Git

Quick setup (recommended)

1. Clone the repository and switch to the project directory:

   git clone <repo-url> steve_autoparts
   cd steve_autoparts

2. Install PHP dependencies:

   composer install

3. Copy environment file and generate app key:

   cp .env.example .env
   php artisan key:generate

4. Configure database in .env (MySQL example):

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=stautoparts
   DB_USERNAME=root
   DB_PASSWORD=secret

5. Create the database (if using MySQL) and run migrations & seeders:

   php artisan migrate --force
   php artisan db:seed

The repository also contains stautoparts.sql for an importable snapshot.

6. Install JS dependencies and build assets:

   npm install
   npm run dev   # for development (hot reload)
   npm run build # for production

7. Start the application for local dev:

   php artisan serve --port=8000

Admin access

- Create an admin user via tinker or a seeder.
- See HOME_PAGE_ADMIN_GUIDE.md for admin homepage management.

Notes

- The repo includes useful scripts in composer.json: `composer run setup` can automate several of the above steps but review the script before running on production.
- .env.example shows recommended values. Keep secrets out of source control.
