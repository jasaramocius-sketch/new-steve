# Architecture Overview

This project is a Laravel 13 application with server-rendered Blade views and modern frontend tooling (Vite + Tailwind).

High-level components

- HTTP layer: routes/web.php defines routes for public site and admin panel.
- Controllers: app/Http/Controllers hold request logic.
- Models & Eloquent: app/Models, database relationships mapped via Eloquent ORM.
- Views: resources/views — Blade templating for pages, admin UI.
- Assets: resources/js, resources/css, built by Vite into public/build.
- Jobs & Queues: Laravel queue system used for background tasks (emails, heavy processing).

Key folders

- app/ — application PHP code
- bootstrap/ — framework bootstrap files
- config/ — app configuration
- database/ — migrations, seeders, factories
- public/ — web root and public assets
- resources/ — views and raw frontend assets
- routes/ — web.php for routes
- storage/ — logs, cache, and file uploads
- vendor/ — composer dependencies

Admin panel

- Routes are grouped under /admin with role middleware for access control (master_admin, admin, staff).
- Admin controllers live under App\Http\Controllers\Admin
- A home page content manager (HomePageController) drives the front page via the home_page_sections table — see HOME_PAGE_ADMIN_GUIDE.md for details.

Third-party packages of note

- barryvdh/laravel-dompdf — PDF generation
- stevestore/laravel-page-builder — page builder integration (local path repository configured)

Routing patterns

- Public routes include home, shop, product, blog, contact, cart and checkout flows.
- Admin routes include resource-like routes for products, brands, blogs, pages, coupons, images, and settings.

Data flow example

- Product creation (admin) -> stored in products table -> visible in shop listing and product pages.
- Home page admin sections -> stored in home_page_sections -> rendered by HomeController on index()

Extensibility

- PSR-4 autoloading: App namespace mapped to app/
- Use Laravel events & jobs to offload heavy tasks
- Use service providers for registering app-level services
