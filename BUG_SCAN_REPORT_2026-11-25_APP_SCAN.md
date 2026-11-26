# Application Bug Scan Report

**Scan date:** 2026-11-25  
**Scope:** Current Laravel source, routing, views, middleware, migrations, queue/schedule configuration, dependency advisories, test suite, and live HTTP smoke checks.  
**Basis:** This report was produced from the current codebase only. No prior report, task log, or historical note was used as input.

## Severity key

- **P1** — Must fix before release; security or primary user path is affected.
- **P2** — High-impact operational/runtime risk.
- **P3** — User-visible defect or inconsistent behavior with a limited blast radius.
- **P4** — Latent/dead-code or housekeeping issue.

---

## Confirmed findings

### [P1] Installed dependencies carry 31 known advisories, including a critical Laravel authentication bypass

- **Evidence:**
  - `composer audit --format=plain` reported **31 advisories across 8 packages** and exited with code 1.
  - Installed versions include `laravel/framework 13.15.0`, `dompdf/dompdf 3.1.5`, `league/commonmark 2.8.2`, `symfony/http-foundation 7.4.13`, `symfony/routing 7.4.13`, `symfony/filesystem 7.4.21`.
  - Notable advisories:
    - `CVE-2026-114509` — critical, Laravel TokenGuard authentication bypass through non-string API credentials; affects `>=13.0.0,<13.27.0`.
    - `CVE-2026-114508` — medium, Laravel relationship inclusion can trigger unauthorized data destruction; affects `>=13.0.0,<13.27.0`.
    - `CVE-2026-128083` — low, Laravel task output injection; affects `<13.40.0`.
    - Multiple dompdf advisories fixed in `>=3.1.6`, including local file read via SVG data URI and resource-exhaustion issues.
    - Multiple `league/commonmark` advisories fixed in newer 2.x releases, including XSS and DoS issues.
    - Symfony advisories affecting `http-foundation`, `http-kernel`, `routing`, `mime`, and `filesystem` at the installed versions.
- **Impact:** Known vulnerable code is present in production. The critical Laravel advisory matters most if API/TokenGuard authentication is used; even where a specific code path is unused, the framework and transitive packages should be patched.
- **Suggested fix:** Run `composer update` with patched constraints (at minimum Laravel `>=13.27.0`, dompdf `>=3.1.6`, commonmark above the affected range, and Symfony packages above the reported affected ranges), then re-run `composer audit`, clear caches, and repeat the HTTP smoke checks.

### [P1] Legacy base URL `/new-steve/` throws HTTP 500 instead of rendering the home page

- **Location:** `routes/web.php:1097-1099`, `app/Http/Controllers/HomeController.php:383`.
- **Evidence:**
  - `GET http://192.168.130.54/new-steve/` (simulated with `Host: 192.168.130.54`) returned **500**.
  - `GET http://192.168.130.54/new-steve` returned 301 to `/new-steve/`, then 500 after redirect.
  - Structured log entry `aff562c6ec16` at `2026-11-25T05:53:37`:
    - `path: "/"`
    - `route: "page.show"`
    - `route_uri: "{slug}"`
    - `exception: "ArgumentCountError"`
    - `message: "Too few arguments to function App\Http\Controllers\HomeController::page(), 0 passed ... and exactly 1 expected"`
  - Other legacy paths work: `/new-steve/search?q=brake` and `/new-steve/product/{slug}` returned 200.
- **Impact:** The required legacy entry point is broken. A visitor opening the old root URL sees a server error.
- **Suggested fix:** Make the catch-all handler tolerate an empty slug and redirect to `home`, or add an explicit guard/route before the catch-all so an empty slug never reaches `HomeController::page($slug)`. Keep the root catch-all registered last.

### [P3] Any visitor can force a 500 response with `?spatest=1`

- **Location:** `app/Http/Middleware/SetPageAttributes.php:24-26`.
- **Evidence:**
  - The web middleware group contains:
    ```php
    if ($request->query('spatest') === '1') {
        abort(500, 'SPA-DEBUG-'.time());
    }
    ```
  - Live check: `GET https://new-steve.local/?spatest=1` returned **500**, while the same URL without the parameter returned **200**.
- **Impact:** A diagnostic abort is active in production with no environment or authentication guard. It affects the requester's own response rather than other users or stored data, but it can pollute monitoring and error logs.
- **Suggested fix:** Remove the abort, or restrict it to a local/test environment behind an explicit debug flag.

### [P2] Production is configured to expose the admin 500-debug viewer

- **Location:** `.env:2-5`, `routes/web.php:768-769`, `app/Http/Controllers/Admin/SystemStatusController.php:101-103`.
- **Evidence:**
  - `.env` has `APP_ENV=production`, `APP_DEBUG=false`, and `DEBUG_ERRORS_ENABLED=true`.
  - The route is admin-only (`auth`, `nocache`, `role:master_admin,admin,staff`) and the controller allows production access when `config('app.debug_errors_enabled')` is true.
  - `GET /admin/system/debug-errors` rendered 200 for an authenticated admin during the scan.
  - `storage/logs/debug500.log` contains structured records with exception messages, file/line data, stack frames, route/user IDs, and SQL text including submitted values.
- **Impact:** The viewer is not public, but every privileged admin/staff account can see internal stack-frame and query details. The setting is unnecessarily enabled on a production environment.
- **Suggested fix:** Set `DEBUG_ERRORS_ENABLED=false` in production and only enable it temporarily when diagnosing an incident. Keep the log channel sanitized and access-limited.

### [P2] Queue worker is not running and a failed job is pending

- **Evidence:**
  - Queue driver is `database` (`php artisan about` and runtime config).
  - A running process check for `queue:work` / `queue:listen` returned no process.
  - `php artisan queue:failed` shows one failed job:
    - ID `585c3f80-fab3-4b06-a1c6-6be14411ec57`
    - Queue `database@default`
    - Job `App\Mail\OrderConfirmationMail`
    - Failed at `2026-11-16 11:03:48`.
  - Scheduled commands are registered (`file:audit`, `trash:purge`, `blog:auto-publish-due-scheduled`), but queued work still requires a worker.
- **Impact:** Queued bulk import/export jobs, Stock Sync “Run now” jobs, and other dispatched work will stay pending. The failed order-confirmation mail has not been retried or removed.
- **Suggested fix:** Run a supervised `php artisan queue:work --tries=3` process, add monitoring/restart handling, and review/retry or delete the failed job.

### [P3] Native `alert()` dialogs remain in customer-facing flows despite toastr being available

- **Location / evidence:**
  - Toastr is loaded in `resources/views/layouts/app.blade.php:33,602,617-629`.
  - Native dialogs still appear in:
    - `resources/views/frontend/checkout/index.blade.php:206,216,270,275,291`
    - `resources/views/frontend/pages/checkout/index.blade.php:206,216,270,275,291`
    - `resources/views/frontend/user/reviews.blade.php:527,531`
    - `resources/views/frontend/product/show.blade.php:735-736`
    - `resources/views/partials/address-fields.blade.php:871`
    - `resources/views/partials/recaptcha-scripts.blade.php:26,45`
    - `resources/views/backend/settings/pickup-points/index.blade.php:108`
    - `resources/views/backend/settings/languages/index.blade.php:168`
    - `resources/views/backend/settings/currency/index.blade.php:168`
    - `resources/views/backend/products/stock.blade.php:262`
    - `resources/views/backend/images/index.blade.php:429`
    - `public/vendor/page-builder/js/page-builder.js:145,154`
- **Impact:** Inconsistent notification UX; storefront validation and upload errors use blocking browser dialogs while session messages use toastr.
- **Suggested fix:** Replace user-facing `alert()`/`window.alert()` calls with `toastr.success/error/warning/info`, keeping native dialogs only where a hard block is genuinely required.

### [P3] Front-controller rewriting is host-dependent on `localhost`

- **Location:** `public/.htaccess:20,31-44`.
- **Evidence:**
  - `RewriteBase /new-steve/` is set globally, while a special rewrite rule only exists for host `new-steve.local`.
  - Live checks:
    - `Host: localhost`, `GET /` → 200
    - `Host: localhost`, `GET /search?q=brake` → **404** from Apache before Laravel
    - `Host: new-steve.local`, `GET /search?q=brake` → 200
    - Legacy `Host: 192.168.130.54`, `GET /new-steve/search?q=brake` → 200
- **Impact:** The canonical host and legacy base path work, but other legitimate localhost requests do not reach the front controller. This conflicts with the expectation that localhost/IP hosts remain usable on HTTP.
- **Suggested fix:** Make the front-controller rewrite host-agnostic while preserving the legacy `/new-steve/` prefix handling, and re-test all three host forms.

### [P4] Dead social-login route reference

- **Location:** `resources/views/partials/social-login-buttons.blade.php:15`.
- **Evidence:**
  - The partial calls `route('social.login', $flag)`.
  - No `social.login` route exists in `routes/web.php`.
  - All four providers in `app/Support/FeatureFlags.php:153-185` are marked `integration_required => true`, so the buttons are not currently rendered.
- **Impact:** Latent breakage as soon as an OAuth provider is implemented and its integration flag is cleared.
- **Suggested fix:** Add the provider login route when implementing OAuth, or remove/deactivate the dead link until the handler exists.

### [P4] Stale Blade backup file inside the view tree

- **Location:** `resources/views/layouts/app.blade.php.restore-backup`.
- **Evidence:** The file exists and contains an older copy of the layout, including older toastr/asset references.
- **Impact:** It is not a registered view, but it can confuse maintenance and accidental restore operations.
- **Suggested fix:** Remove the backup file, or move such artifacts outside `resources/views/`.

---

## Test suite status

`php artisan test` did not pass.

- **Totals:** 41 tests, 25 passed, 3 failed, 13 errors, 9 risky.
- **Failures:**
  - `Tests\Unit\CategoryImageTest::test_category_without_image_uses_slug_fallback` expects a legacy filename, while `Category::getDisplayImage()` currently returns `placeholder.png` (`app/Models/Category.php:126,129-132`). This is a stale test expectation rather than a current production defect.
  - `Tests\Feature\AdminProductRoutesTest::test_import_route_is_not_shadowed_by_product_id_route` requests `/admin/products/import`, but the current import form route is `/admin/product-bulk-upload` (`routes/web.php:372-373`). The test expectation is stale; the named route still exists.
  - `Tests\Feature\ExampleTest::test_the_application_returns_a_successful_response` fails with `PDOException: could not find driver` for the SQLite test connection.
- **Errors:** 13 tests error with the same missing SQLite PDO driver, including cart-stock, home-brand, product-import, shipping-rate, and Stripe service tests.
- **Risky:** 9 tests are marked risky by PHPUnit.
- **Recommended fix:** Install the SQLite PDO extension for the test environment, update the two stale assertions, and review risky tests for missing assertions.

---

## Checks performed with no active finding

- PHP syntax: all PHP files under `app/`, `bootstrap/`, `config/`, `database/`, and `routes/` passed `php -l`.
- Blade compilation: `view:clear` + `view:cache` completed successfully.
- Route cache: `route:clear` + `route:cache` completed successfully.
- Static reference scan:
  - 569 PHP/Blade source files scanned.
  - 847 view references resolved; **0 missing views**.
  - 1,234 route references checked; no duplicate route names or duplicate URI/method pairs.
  - The only exact-name route miss is the dead `social.login` reference listed above; all other misses are intentional `routeIs('...*')` wildcard patterns.
- Migrations: all listed migrations are marked `Ran`.
- Canonical public HTTP smoke checks on `https://new-steve.local` returned 200 for `/`, `/search`, `/categories`, `/brands`, `/blog`, `/about-us`, `/contact-us`, `/cart`, `/wishlist`, login/registration pages, vehicle/suggestion/AJAX endpoints, `robots.txt`, and `sitemap.xml`.
- Authenticated smoke checks returned 200 for `/admin/dashboard`, `/admin/users`, `/admin/products`, `/admin/pages`, `/admin/system/debug-errors`, `/user/dashboard`, `/user/orders`, `/user/reviews`, `/user/vehicles`, and `/user/addresses`.
- `/user/wallet` correctly redirects to the user dashboard.
- Product page related cards are distinct, and the current product is excluded from the same-page “Recently Viewed” section.
- No `steve_autoparts-main` string was found in rendered home-page source.

## Limitations

- Live payment-provider settlement, provider webhook signatures, SMTP/mail delivery, and S3 image delivery were not exercised because credentials and external accounts were not available.
- CLI-rendered `/` and `/search` initially hit file-cache permission errors because cache subdirectories are owned by `www-data`; the same URLs returned 200 through the web server on the canonical host. This is a CLI/test artifact, not listed as an application defect.
- Several older entries in `storage/logs/debug500.log` were not reproducible during current re-checks; only currently evidenced issues are listed as findings.
- No application code was changed during this scan. The caches were cleared and rebuilt, and this report is the only repository file created/updated for the scan.
