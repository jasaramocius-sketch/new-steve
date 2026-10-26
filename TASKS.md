# Completed Tasks

## 1. Dynamic Copyright Year
**Files changed:**
- `app/Helpers/date.php` — created with `current_year()` helper
- `app/Providers/AppServiceProvider.php` — added `require_once app_path('Helpers/date.php')`
- Footer view files — replaced `date('Y')` and `2026` with `:year` placeholder

## 2. NewsletterController
**Files changed:**
- `app/Http/Controllers/NewsletterController.php` — created
- `routes/web.php` — added missing `use` import

## 3. Unirate Currency API
**Files changed:**
- `app/Providers/AppServiceProvider.php` — updated API URL to `https://api.unirateapi.com/api/widget/v1/rates?base=USD`

## 4. Custom Pagination Design
**Files changed:**
- `resources/views/vendor/pagination/gs-pagination.blade.php` — created custom pagination view
- `app/Providers/AppServiceProvider.php` — set `Paginator::defaultView('vendor.pagination.gs-pagination')`

## 5. Product Page Dummy Content
**Files changed:**
- Product page view — added lorem ipsum to Description tab, return policy to Policy tab, 5 dummy reviews to Reviews tab

## 6. `is_active` → `status` Rename
**Files changed:**
- `database/migrations/2026_06_30_000001_rename_is_active_to_status_in_all_tables.php` — created
- Models (6): Brand, Category, Product, Coupon, Blog, Page — updated `$fillable` references
- Controllers (8) — updated all `is_active` to `status`
- Blade views (22+) — updated all references
- Seeders (2) — updated

## 7. Brands Status Toggle
**Files changed:**
- `app/Http/Controllers/Admin/BrandController.php` — added `toggleStatus()` method
- `routes/web.php` — added `admin.brands.toggle-status` route
- `resources/views/admin/brands/index.blade.php` — made status badge clickable with form POST

## 8. Home Page "Top Brands" Section
**Files changed:**
- `app/Http/Controllers/HomeController.php` — added `use App\Models\Brand` + `$brands = Brand::where('status', true)->get()`
- `resources/views/home.blade.php` — added Top Brands section before Partner section using `.gs-partner-section` CSS, `col-lg-4` grid

## 9. `is_deleted` Column Fix
**Root cause:** `is_deleted` missing from `$fillable` in 16 models — `updateQuietly(['is_deleted' => 1])` in `TracksIsDeleted` trait silently ignored.

**Files changed:**
- `database/migrations/2026_06_30_075344_add_is_deleted_to_brands_table.php` — created (adds column to brands)
- `database/migrations/2026_06_30_075837_add_is_deleted_to_remaining_tables.php` — created (adds column to 12 tables)
- Models with `'is_deleted'` added to `$fillable`:
  - `app/Models/Brand.php`
  - `app/Models/Order.php`
  - `app/Models/OrderItem.php`
  - `app/Models/Blog.php`
  - `app/Models/BlogCategory.php`
  - `app/Models/Wishlist.php`
  - `app/Models/Notification.php`
  - `app/Models/Admin.php`
  - `app/Models/Compare.php`
  - `app/Models/Address.php`
  - `app/Models/Vehicle.php`
  - `app/Models/FollowedSeller.php`
  - `app/Models/Image.php`
  - `app/Models/Staff.php`
  - `app/Models/Coupon.php`
  - `app/Models/Page.php`
- Data sync: `Brand(2)`, `Wishlist(34)`, `Compare(87)` — `is_deleted=1` set for existing soft-deleted records

## 10. Master Admin Protection
**Files changed:**
- `resources/views/admin/users/index.blade.php`:
  - Delete button hidden when `$user->role === 'master_admin'`
  - Master admins excluded from query (`where('role', '!=', 'master_admin')`)
  - Added `session('error')` alert display
- `resources/views/admin/users/form.blade.php`:
  - Role field replaced with disabled input + hidden field + lock icon for master_admin
  - Fixed Blade syntax (`'Edit User'` string quotes)
- `app/Http/Controllers/UserManagementController.php`:
  - `destroy()`: returns error if target user is master_admin
  - `update()`: rejects role change if target user is master_admin

## 11. FAQ Page (Dynamic DB + Admin CRUD)
**Files changed:**

### Database
- `database/migrations/2026_06_30_081058_create_faqs_table.php` — created (id, question, answer, order, status, softDeletes)
- `app/Models/Faq.php` — created (SoftDeletes, TracksIsDeleted, fillable, casts)

### Admin CRUD
- `app/Http/Controllers/Admin/FaqController.php` — created (index, create, store, edit, update, destroy, restore, forceDelete, toggleStatus)
- `routes/web.php` — added 9 routes (`admin.faqs.*`)
- `resources/views/admin/faqs/index.blade.php` — created (active/trash tabs, toggleable status badge)
- `resources/views/admin/faqs/create.blade.php` — created (question, answer, order, status form)
- `resources/views/admin/faqs/edit.blade.php` — created (edit form)
- `resources/views/admin/partials/sidebar.blade.php` — added FAQ link under Content section

### Frontend
- `resources/views/pages/faq.blade.php` — replaced "Coming Soon" with dynamic accordion:
  - Bootstrap collapse with `data-bs-parent="#faqsAccordion"` for auto-close
  - Chevron icon rotation via `.collapsed`/`:not(.collapsed)` CSS transition (0.35s ease)
  - `collapsed` class preloaded to prevent initial flicker
- `app/Http/Controllers/HomeController.php` — added `use App\Models\Faq` + `faq()` now queries `Faq::where('status', true)->orderBy('order')->get()`

## 12. Pagination — Limit Visible Pages with `onEachSide(2)`
**Files changed:**
- `app/Http/Controllers/ShopController.php` — added `->onEachSide(2)` to 4 `paginate(9)` calls
- `app/Http/Controllers/BlogController.php` — added `->onEachSide(2)` to 2 `paginate(6)` calls
- `app/Http/Controllers/Admin/ImageController.php` — added `->onEachSide(2)` to 1 `paginate(24)` call

## 13. Mini Cart Popup on Cart Icon Click (Bootstrap Dropdown)
**Files changed:**
- `resources/views/layouts/app.blade.php` — cart icon changed to Bootstrap dropdown (`data-toggle="dropdown"`) structure with `#cart_items` wrapper; added mobile cart icon
- `resources/views/partials/mini-cart.blade.php` — created/updated mini cart partial with `list-group` items layout and empty state
- `app/Http/Controllers/CartController.php` — added `miniCart()` method, updated `remove()` to support AJAX
- `routes/web.php` — added `cart.mini` route
- `public/assets/front/js/script.js` — added AJAX submit handler for `.gs-mini-cart-remove-form`; add-to-cart AJAX now refreshes mini cart HTML via `#cart_items .dropdown-menu`
- `public/assets/front/css/style.css` — replaced custom `.gs-mini-cart` styles with `#cart_items .dropdown-menu` width/cleanup styles

## 14. Cart Quantity Update Fix
**Root cause:** `.cart-item` `<li>` was missing `data-id` attribute, so JS read `null` as the item ID, causing the controller to return `{'success': false}`.

**Files changed:**
- `resources/views/cart.blade.php`:
  - Added `data-id="{{ $key }}"` to `.cart-item` `<li>` (JS reads ID from container)
  - Removed duplicate inline jQuery handler (external `script.js` already handles it)
  - Added `id="prc{{ $key }}"` to each item's subtotal span for JS UI updates
   - Added `.total-cart-price-sub` and `.total-cart-price-val` classes to the cart total span

## 15. Mobile Cart View
**Files changed:**
- `resources/views/cart.blade.php`:
  - Restructured layout with proper `row` grid (was missing, so `col-*` children didn't work)
  - Mobile: Product full width, then Price/Qty/Total/Remove in `col-3 col-3 col-3 col-auto` row
  - Added mobile labels "Price" and "Total" via `d-block d-lg-none`
  - Desktop header reordered: Product → Price → Qty → Total → Remove
  - Checkout buttons stack vertically on mobile (`flex-column`) with full-width button
   - Added mobile CSS: smaller qty input/circle buttons, reduced font sizes, spacing

## 16. Unified Checkout Steps Design
**Files changed:**
- `resources/views/partials/checkout-steps.blade.php` — created shared partial with icons + step circles + progress bars
- `resources/views/layouts/app.blade.php` — added `@yield('style')` to head + shared `.step-circle`/`.step-label`/`.step-progress` CSS
- `resources/views/cart.blade.php` — replaced `aiz-steps arrow-divider` with shared partial (`activeStep: 1`), removed old CSS
- `resources/views/checkout.blade.php` — replaced inline steps with shared partial (`activeStep: 2`), removed duplicate CSS
- `resources/views/delivery-info.blade.php` — replaced inline steps with shared partial (`activeStep: 3`), removed duplicate CSS

## 17. Full 5-Step Checkout Flow — Design Matches steveautoparts.com

**Step indicator completely redesigned** to match the live site:
- Changed from circle+progress-bar to `border-bottom-6px` approach
- Uses `row gutters-5 sm-gutters-10` grid (exact copy of live site HTML structure)
- Active step: `.text-primary` + primary-colored bottom border
- Done step: `.text-success` + success-colored bottom border
- Inactive steps: dimmed with `opacity-50` on both icon and label
- Labels hidden on mobile with `d-none d-lg-block` (matching live site)
- Icons use `la-3x mb-2` (matching live site)

**All 5 pages created/updated:**
1. **Cart** (step 1) — `resources/views/cart.blade.php`
2. **Shipping info / Checkout** (step 2) — `resources/views/checkout.blade.php`
3. **Delivery info** (step 3) — `resources/views/delivery-info.blade.php`
4. **Payment** (step 4) — `resources/views/payment.blade.php`
5. **Order Confirmed** (step 5) — `resources/views/order-confirmed.blade.php`

**Files changed (checkout flow):**
- `resources/views/partials/checkout-steps.blade.php` — complete redesign using border-bottom-6px layout
- `resources/views/layouts/app.blade.php`:
  - Removed old `.step-circle`, `.step-label`, `.step-progress` CSS
  - Added `.border-bottom-6px` with state classes (`.done`, `.active`)
  - Added global utility classes: `.fs-14`–`.fs-20`, `.fw-100`–`.fw-900`, `.opacity-10`–`.opacity-90`, `.gutters-5`, `.sm-gutters-10`, `.img-fit`, `.size-60px`, `.btn-circle`, `.btn-soft-primary`, `.aiz-plus-minus`
- `resources/views/cart.blade.php` — standardized structure (steps inside same section), removed duplicate local CSS (moved to layout)
- `resources/views/checkout.blade.php` — simplified to `border bg-white p-4` containers
- `resources/views/delivery-info.blade.php` — simplified to `border bg-white p-4` containers
- `resources/views/payment.blade.php` — created with payment method selection (COD/Card/PayPal)
- `resources/views/order-confirmed.blade.php` — created with order summary + thank you message
- `app/Http/Controllers/CartController.php`:
  - `deliveryInfoSubmit()` now redirects to `checkout.payment` instead of dashboard
  - Added `payment()` method — validates session, shows payment page
  - Added `paymentSubmit()` method — creates order, clears cart, redirects to confirmation
  - Added `orderConfirmed()` method — shows order success page from session
- `routes/web.php`:
  - Added `GET /checkout/payment` → `payment()` → named `checkout.payment`
  - Added `POST /checkout/payment` → `paymentSubmit()` → named `checkout.payment.submit`
  - Changed `/checkout/order-confirmed/{id}` → `/checkout/order-confirmed` → `orderConfirmed()`

**Design consistency across all 5 pages:**
- All pages use `border bg-white p-4` containers (matching live site's `border bg-white p-4`)
- All buttons use `btn btn-primary fw-600` with arrow icons
- All pages include the shared `checkout-steps` partial with correct `activeStep`
- Payment and delivery option cards use `border p-3` with checked highlight (`#e62e04` border + `#fff5f3` bg)
- Order summary sidebar consistent across steps 2-4
- Live site reference: https://steveautoparts.com/cart

## 18. Checkout Page — Address Management (aiz-megabox Cards + Modal CRUD)

**Files changed:**
- `database/migrations/xxxx_xx_xx_xxxxxx_create_addresses_table.php` — Address model migration
- `database/migrations/xxxx_xx_xx_xxxxxx_add_set_default_to_addresses.php` — `set_default` column
- `app/Models/Address.php` — created (fillable: user_id, full_name, phone, address, city, state, country, zip_code, set_default, is_deleted)
- `app/Http/Controllers/AddressController.php` — created (store, edit, update, destroy, setDefault — AJAX + ownership checks)
- `app/Http/Controllers/CartController.php` — `checkout()` passes `auth()->user()->addresses`
- `resources/views/checkout.blade.php` — rewritten: aiz-megabox address cards, "Add New Address" box, "Change" button, correct navigation
- `resources/views/partials/address-modal.blade.php` — Bootstrap 5 modal for add/edit (no full_name field, auto-set from auth user)
- `resources/views/partials/checkout-steps.blade.php` — optionally updated
- `routes/web.php` — added 5 checkout address routes (`/checkout/address/*`)

## 19. Address Modal — Full Name Field Removed

**Details:** Address form auto-sets `full_name` from `auth()->user()->name` in AddressController. Modal has no full_name input.
**Files changed:**
- `resources/views/partials/address-modal.blade.php` — no full_name field
- `app/Http/Controllers/AddressController.php` — auto-sets full_name from auth user

## 20. Delivery Info — Store Pickup Option + Fixes

**Files changed:**
- `resources/views/delivery-info.blade.php` — added "Store Pickup" aiz-megabox radio alongside "Home Delivery"; fixed `g` → `gry-bg` typo
- `app/Http/Controllers/CartController.php` — `deliveryInfoSubmit()` validation updated to allow `pickup`, costs array includes `pickup => 0`

## 21. Payment Page — Card Details Form (Stripe)

**Files changed:**
- `resources/views/payment.blade.php`:
  - Added card details section (Card Number, Expiry, CVV) hidden by default, shown when Stripe selected
  - Added `class="payment-radio"` to both radio inputs
  - Added inline CSS (`.payment-step-body .payment-radio { position: absolute; opacity: 0; pointer-events: none; }`)
  - Added JS change event listeners — card fields get `required` attribute only when Stripe is selected
  - Added `@section('page-class', 'checkout-page payment-step-body bg-light')` for page-specific styling

## 22. Purchase History Page — Matched to Live Site

**Files changed:**
- `resources/views/user/orders.blade.php` — Full rewrite:
  - Card layout (`card shadow-none rounded-0 border`) with "Purchase History" heading
  - Table columns: Code (linked to details), Date, Amount, Delivery Status (bg-soft-* badge), Payment Status (Paid/Unpaid badge), Options
  - Circular action buttons (btn-icon btn-circle btn-sm) with inline SVGs: Cancel (trash), View Details (hamburger), Download Invoice (arrow-down)
  - Pagination via `aiz-pagination` div
  - Empty state with SVG icon
- `public/assets/front/css/custom.css` — Added:
  - `bg-soft-*` classes (primary, success, info, warning, danger, secondary)
  - `btn-soft-*` classes with hover states
  - `btn-icon` and `btn-circle` classes
  - `aiz-pagination` styling
  - `gap-1` through `gap-4` utilities
- `app/Http/Controllers/DashboardController.php` — `orders()` changed to `paginate(10)` instead of `get()`

## 23. Order Cancel (destroy) Feature

**Files changed:**
- `app/Http/Controllers/OrderController.php` — added `destroy($id)` method: finds order by user_id, sets status to 'cancelled', redirects back with success message
- Fixed PDF namespace typo: `Barrier\DomPDF` → `Barryvdh\DomPDF`

## 24. Invoice View + Route Fix

**Files changed:**
- `resources/views/user/orders/invoice.blade.php` — created: PDF-ready invoice template with header, billing address, line items table, totals, footer
- `routes/web.php` — fixed user invoice route: changed path from `user/orders/invoice/{id}` to `/orders/invoice/{id}` (within user group), renamed admin route to `admin.user.orders.invoice` to avoid conflict

## 25. Product Gallery and Admin Product Edit Fixes
**Date:** 2026-08-11

**Files changed:**
- `app/Http/Controllers/ProductController.php` — added `seller_id` support on create/update, moved gallery manager attachment logic into `attachGalleryImagesFromManager()`, and ensured `gallery_images_from_manager` is processed for both store and update
- `app/Models/Product.php` — added `seller_id` to `$fillable` and `seller()` relation
- `app/Models/Image.php` — improved `normalizePath()` to strip leading slashes and `public/`/`storage/` prefixes
- `app/helpers.php` — improved `normalizeImagePath()` to normalize hidden manager paths consistently
- `resources/views/admin/partials/image-manager-picker.blade.php` — made the modal centered/scrollable, preserved existing selected image paths in multi-select mode, and restored focus after modal close
- `resources/views/admin/products/edit.blade.php` — added seller dropdown support in the edit product form
- `resources/views/product/show.blade.php` — changed gallery display to use `storedImageUrl($gi->path)` so stored gallery paths render correctly

## 219. Bulk Product Import System

**Files changed/created:**
- `app/Http/Controllers/ProductController.php` — added `importForm()`, `import()`, `downloadSampleCsv()` methods
- `resources/views/admin/products/import.blade.php` — **new**: upload form with column reference + download sample link
- `routes/web.php` — added 3 routes: import form (GET), import (POST), sample CSV download (GET)
- `resources/views/admin/partials/sidebar.blade.php` — added "Import Products" sub-link under Products
- `resources/views/admin/layouts/app.blade.php` — added `.nav-item-sub` CSS style
- `resources/views/admin/products/index.blade.php` — added "Import" button next to "Add Product"

**CSV Import features:**
- Required columns: `name`, `price`; optional: `old_price`, `category`, `stock`, `description`, `badge`, `product_type`, `status`, `featured`, `image`, `brand`, `year`, `make`, `model`
- Category matched by name (case-insensitive), unknown categories warned (not fatal)
- Brand matched by name (case-insensitive), unknown brands warned (not fatal)
- Image URLs downloaded via `saveImageFromUrlWithWebp()` helper
- Slug collision avoidance via `time() + counter`
- Status/featured accept `1/0`, `yes/no`, `active/inactive`
- Results show count + first 10 errors
- Download Sample CSV button provides ready-made template

## 26. NoCache Middleware Fix

**Files changed:**
- `app/Http/Middleware/NoCache.php` — `$response->header()` replaced with `$response->headers->set()` (StreamedResponse doesn't have `header()` method)

## 27. Shop Sidebar — Year/Make/Model/Brand Filters (Replaced Recent Products)

**Files changed:**
- `resources/views/shop.blade.php` — removed Recent Products widget; added 4 filter widgets:
  - **Brand** — links filtered by `brand_id` FK, "All Brands" reset
  - **Year** — distinct years from products table, "All Years" reset
  - **Make** — distinct makes from products table, "All Makes" reset
  - **Model** — distinct models from products table, "All Models" reset
  - Each widget: active filter bolded, all filters preserve other URL params
- `app/Http/Controllers/ShopController.php`:
  - `getSharedData()` now returns `brands` (with products), `years`, `makes`, `models` (from products table, distinct, where status=1)
  - `filterProducts()` accepts and queries by `brand_id`, `year`, `make`, `model` params
  - Removed `recentProducts` from shared data
  - Added `use App\Models\Brand`

## 28. Products Table — Added brand_id, year, make, model Columns

**Migration:**
- `database/migrations/2026_07_06_080423_add_brand_and_vehicle_columns_to_products_table.php` — adds:
  - `brand_id` (bigint unsigned, FK → brands.id, nullOnDelete)
  - `year` (year type)
  - `make` (varchar 100)
  - `model` (varchar 100)

**Model updates:**
- `app/Models/Product.php` — `$fillable` includes `brand_id`, `year`, `make`, `model`; added `brand()` belongsTo relationship
- `app/Models/Brand.php` — added `products()` hasMany relationship

**Admin product forms:**
- `resources/views/admin/products/create.blade.php` — added Brand dropdown, Year/Make/Model inputs
- `resources/views/admin/products/edit.blade.php` — same fields with old values

**Controller validation:**
- `app/Http/Controllers/ProductController.php` — `store()` and `update()` validate `brand_id` (exists:brands), `year` (integer|min:1900|max:2026), `make`/`model` (string|max:100)

## 29. Shop Sidebar — Select Dropdowns with Cascading

**Files changed:**
- `resources/views/shop.blade.php` — Brand/Year/Make/Model filter widgets changed from link lists to `<select>` dropdowns; cascading JS filters Make based on selected Year, and Model based on selected Year+Make; hidden inputs preserve other URL params
- `app/Http/Controllers/ShopController.php` — `getSharedData()` now also returns `vehicleData` (distinct year/make/model combos for cascading)

## 30. Product Export CSV

**Files changed:**
- `app/Http/Controllers/ProductController.php` — added `exportCsv()` method: streams all products as CSV with category/brand names, image URLs
- `routes/web.php` — added `GET /products/export/csv` → `admin.products.export-csv`
- `resources/views/admin/products/index.blade.php` — added "Export CSV" button next to Import/Add Product

## 31. All Products Populated with brand_id, year, make, model

**Data:**
- All 115 products assigned `brand_id = 1` (Aci window regulator)
- Year/make/model filled cycling through 10 vehicle combos (Toyota Camry 2020, Honda Civic 2019, Ford F-150 2021, etc.)

## 32. CSV Export/Import — ID Support + Update Logic

**Files changed:**
- `app/Http/Controllers/ProductController.php`:
  - `exportCsv()` — added `id` as first column in export
  - `import()` — added `id` to expected columns; if `id` is provided and product exists → **updates** that product (preserves existing slug); otherwise → **creates** new product (generates slug)
  - `downloadSampleCsv()` — added `id` column (empty in sample rows)
- `resources/views/admin/products/import.blade.php` — added `id` to supported columns list with description

## 33. Product Gallery — Only Show When Image Available

**Files changed:**
- `resources/views/product.blade.php`:
  - Wrapped thumbnail nav slider in `@if($product['image'])` — slider hidden when no image
  - Changed `@for($i=0; $i<4; $i++)` loop to a single thumbnail — only 1 thumbnail shown when only main image exists

## 34. Fixed Missing `tab_label_*` / `policy_text` Columns (Migration Not Run)

**Root cause:** Migration `2026_07_06_000000_add_tab_labels_to_products_table.php` existed but was never migrated to the database.

**Fix:** Ran `php artisan migrate --path=database/migrations/2026_07_06_000000_add_tab_labels_to_products_table.php`

## 35. Single Product Page — Redesigned to Match Zenis Template

**Reference:** https://zenis-laravel.preoit.com/shop/details

**Files changed:**
- `resources/views/product.blade.php` — partial rewrite:
  - Gallery: vertical thumbnail nav on left + main image on right (matching Zenis `details_slider_nav` / `details_slider_thumb` structure)
  - Product info: category tag, title, stock/rating, price with del, description, quantity input, Buy Now + Add to Cart buttons (using original `steve-btn` style)
  - Action links: Wishlist, Compare, Ask a question (inline list)
  - SKU + Category meta
  - Share icons (Facebook, Twitter, LinkedIn, WhatsApp)
  - Sidebar: shipping/warranty info + seller store card
  - Tabs (Description, Policy, Reviews) and Related Products section kept in original design per user request
  - All CSS embedded in `@section('style')` — gallery, product info, sidebar, spacing utilities, responsive breakpoints
- `resources/views/layouts/app.blade.php` — added Jost Google Font

## 36. User Dashboard Pages — Redesigned to Match SpareCom Theme

### Dashboard Page
**Files changed:**
- `resources/views/user/dashboard.blade.php` — complete redesign:
  - Stat cards using `.gs-single-statatics` with icons (Total Spent, Orders, Products in Cart, Pending Orders)
  - Package/shipping info using `.acc-info-wrapper`
  - Wishlist thumbnails section using `.dashboard-wishlist-thumbnails`
  - Fixed `$address` undefined error with `isset()` guard

### Order Detail Page
**Files changed:**
- `resources/views/user/orders/show.blade.php` — complete redesign:
  - Title bar with Order#, status badge, order date, Print Order (opens in new tab via stream), Back button
  - Status timeline: Order Placed → On Review → On Delivery → Delivered
  - Pickup + Billing address with icon rows
  - Payment Information (Payment Status, Tax, Paid Amount, Payment Method)
  - Shipping Method section
  - Purchase Items table

### Orders List Page
**Files changed:**
- `resources/views/user/orders.blade.php` — redesigned to match SpareCom:
  - `.dashboard-topbar` with "Purchase History" + "Continue Shopping" pill button
  - `.dashboard-filter` with status filter pills (All, Pending, Processing, Shipped, Delivered, Cancelled)
  - `.table--custom.table--responsive-lg` with `data-label` responsive attributes
  - Soft-color status badges (`.badge--success|danger|warning|info|primary|secondary`)
  - Circular SVG action buttons (cancel=red, view=dark, invoice=blue) with hover states
  - `.action-buttons` flex row
  - Empty state section with centered icon + CTA
  - `.pagination-wrapper` for pagination

### Addresses Page
**Files changed:**
- `resources/views/user/addresses.blade.php` — redesigned with RedParts-style address cards:
  - `.ud-page-title-box` title row
  - `.address-card` with SVG location/phone icons
  - `.action-icon-btn.btn-edit` (orange stroke) / `.btn-delete` (red stroke)
  - Styled modals with `.template-btn` buttons

### Order Tracking Page
**Files changed:**
- `resources/views/user/orders/tracking.blade.php` — new page:
  - `.gs-order-track-section` with form + result display
  - Status timeline and ordered products table
- `app/Http/Controllers/OrderController.php` — added `tracking()` method
- `routes/web.php` — added `GET|POST /user/order/tracking` → `user.order.tracking`

### Sidebar
**Files changed:**
- `resources/views/user/layouts/sidebar.blade.php` — added "Order Tracking" link with `fas fa-search` icon

### CSS Additions (`public/assets/front/css/style.css`)
- Base `button` selector: consistent `font-family`, `font-size: 16px`, `padding: 12px 24px`, `line-height: 1.2`, `border-radius: 8px`, `cursor: pointer`, `transition` — excluding `.add-cart`/`.add-to-cart` buttons via explicit reset
- `.page-content`, `.page-content__header`, `.page-content__title` — content layout
- `.table--custom`, `.table--responsive-lg` — clean table with section-bg headers, responsive with data-labels at <1200px
- `.badge`, `.badge--base|success|danger|warning|info|primary|secondary` — soft-colored pill badges
- `.action-buttons`, `.action-btn.btn-cancel|btn-view|btn-invoice` — circular icon buttons with hover states
- `.pagination-wrapper` — pagination layout
- `.empty-section` — centered empty state
- `.btn--base`, `.btn--sm` — pill-shaped orange buttons
- `.dashboard-topbar` — flex wrap-reverse between title/button
- `.dashboard-filter`, `.dashboard-filter__link` — pill-shaped filter buttons with active orange state
- `.dashboard-body`, `.order-item`, `.order-item__thumb`, `.order-item__content`, `.order-item__title` — sparecom-style content structure
- `.table tr td:not(:has(.btn)) > a` — underlined links in table cells

## 37. Dashboard Cart Count Fix (500 Error)

**Root cause:** `dashboard.blade.php` referenced `\App\Models\Cart` (non-existent model) via `Cart::count()`.

**Files changed:**
- `resources/views/user/dashboard.blade.php` — replaced with `collect(session('cart', []))->sum('qty') ?: 0`

## 38. Wishlist Header Count Fix (Disappeared on Refresh)

**Root cause:** View Composer in `AppServiceProvider.php` didn't set `$wishlistCount`/`$compareCount` for guests; `Auth::check()` branch had no fallback when session vars were missing.

**Files changed:**
- `app/Providers/AppServiceProvider.php`:
  - Guest branch: added `$wishlistCount = count($wishedProductIds)`
  - Auth branch: added fallback to restore `user_logged_in`/`user_profile` session vars from DB if missing

## 39. Contact Form Route Fix

**Root cause:** `action="route('contact.store')"` rendered as literal text (no Blade `{{ }}`).

**Files changed:**
- `resources/views/pages/contact.blade.php` — `action="route('contact.store')"` → `action="{{ route('contact.store') }}"`

## 40. Revision System — Admin Model Change Tracking

**Files changed/created:**
- `database/migrations/2026_07_09_092000_create_revisions_table.php` — creates `revisions` table (user_id, model_type, model_id, action, old_values JSON, new_values JSON, ip_address)
- `app/Models/Revision.php` — casts old/new_values as array, belongsTo user
- `app/Traits/Revisable.php` — hooks into `created`/`updated`/`deleted` Eloquent events; skips when no auth user; ignores `created_at`/`updated_at` timestamps
- Applied Revisable trait to 14 models: Category, Brand, Product, User, Staff, Order, Coupon, Page, Faq, Blog, BlogCategory, HomePageSection, Contact, Setting
- `app/Http/Controllers/AdminController.php` — added `revisions()` method (paginated, sortable)
- `routes/web.php` — added `GET /admin/revisions` → `admin.revisions.index`
- `resources/views/admin/revisions/index.blade.php` — paginated table with field-level diff, sortable columns
- `resources/views/admin/partials/sidebar.blade.php` — added "Revisions" link under Logs

## 41. Static Page Display Route

**Files changed:**
- `routes/web.php` — added `GET /page/{slug}` → `HomeController@page()` → `page.show`
- `app/Http/Controllers/HomeController.php` — added `page()` method: looks up `Page` by slug where `status=true`, renders `resources/views/pages/show.blade.php`
- `resources/views/pages/show.blade.php` — simple page layout with title + content

## 42. File Audit System — Real-Time File Change Tracking

**Files changed/created:**
- `database/migrations/2026_07_09_103000_create_file_revisions_table.php` — creates `file_revisions` table (file_path, event, content_hash, backup_path, user_id)
- `app/Models/FileRevision.php` — cast `created_at` to datetime, belongsTo user
- `app/Console/Commands/FileAuditCommand.php` — `file:audit` command:
  - `--init` builds mirror snapshot (1615 files) in `storage/file-backups/mirror/`
  - Default mode scans once, detects created/updated/deleted, archives old versions to `storage/file-backups/archive/YYYY-MM-DD_HH-MM-SS/`
  - `--watch` continuous polling mode (default 60s interval)
  - Auto-purges archives older than 90 days
  - Watched dirs: `app/`, `config/`, `database/`, `resources/`, `routes/`, `public/assets/`
  - Excludes: `vendor/`, `node_modules/`, `storage/`, `.git/`, `*.log`, etc.
- `app/Http/Controllers/AdminController.php` — added `fileRevisions()` + `fileRevisionDownload()` methods
- `routes/web.php` — added `GET /admin/file-revisions` + `GET /admin/file-revisions/download/{id}`
- `resources/views/admin/file-revisions/index.blade.php` — sortable table with event badges, user, backup download button, pagination
- `resources/views/admin/partials/sidebar.blade.php` — added "File Revisions" link

### Real-Time Watcher
- `/var/www/html/stautoparts/file-watcher.mjs` — Node.js script using chokidar (installed devDependency) that detects add/change/unlink events and immediately triggers `php artisan file:audit` (1s debounce)
- `/var/www/html/stautoparts/file-watcher.sh` — bash management script (`start|stop|status|restart`)
- Cron job removed; file changes now tracked in real-time via background Node.js process
- `user` column in admin UI shows "System" for CLI-triggered revisions (no HTTP auth context)

## 43. Footer Policy Routes — Terms, Privacy, Return, Support

**Root cause:** Footer links used `route('terms.conditions')`, `route('privacy.policy')`, `route('return.policy')`, `route('support.policy')` — none of these routes existed.

**Files changed:**
- `routes/web.php` — added 4 routes:
  - `GET /terms-conditions` → `HomeController@terms` → `terms.conditions`
  - `GET /privacy-policy` → `HomeController@privacy` → `privacy.policy`
  - `GET /return-policy` → `HomeController@returnPolicy` → `return.policy`
  - `GET /support-policy` → `HomeController@supportPolicy` → `support.policy`
- `app/Http/Controllers/HomeController.php` — added `returnPolicy()` and `supportPolicy()` methods
- `resources/views/pages/return-policy.blade.php` — new static view with Return Policy content
- `resources/views/pages/support-policy.blade.php` — new static view with Support Policy content

## 44. DB-Driven Policy Pages (Admin Editable)

**Change:** Policy pages converted from static Blade views to DB-driven (Page model). HomeController methods now check `Page::where('slug', $slug)->where('status', true)` first, fall back to static view only if DB record missing.

**Files changed:**
- `app/Http/Controllers/HomeController.php` — updated `terms()`, `privacy()`, `returnPolicy()`, `supportPolicy()` to query Page model by slug
- `pages` table — 4 records inserted:
  - `terms-conditions` (id=3), `privacy-policy` (id=4), `return-policy` (id=5), `support-policy` (id=6)
  - All with full HTML content, meta titles, status=true
- Admin can now edit these at `/admin/pages` → content updates reflect immediately on frontend

## 45. File Revisions — Diff View (Kaunsa Line Change Hua)

**Problem:** File Revisions showed *which* file changed but not *what* changed within it.

**Solution:** Added `diff` column + PHP unified diff computation + dedicated detail view.

**Files changed/created:**
- `database/migrations/2026_07_09_064220_add_diff_to_file_revisions_table.php` — adds `diff` LONGTEXT column (nullable) after `backup_path`
- `app/Console/Commands/FileAuditCommand.php`:
  - `handleUpdated()` now computes diff between old mirror content and new file
  - Added `computeDiff()` method — line-by-line PHP diff producing unified format (`--- a/...`, `+++ b/...`, `@@ ... @@`, `-old`, `+new`)
  - Added `formatChunk()` helper
- `app/Models/FileRevision.php` — `'diff'` added to `$fillable`
- `routes/web.php` — added `GET /admin/file-revisions/diff/{id}` → `admin.file-revisions.diff`
- `app/Http/Controllers/AdminController.php` — added `fileRevisionDiff()` method
- `resources/views/admin/file-revisions/diff.blade.php` — dedicated diff page:
  - Meta header: file path, event badge, date, user
  - Diff rendered in `<pre>` with line-level color coding:
    - Red background (`diff-line-del`) for removed `-` lines
    - Green background (`diff-line-add`) for added `+` lines
    - Blue (`diff-line-hunk`) for hunk headers `@@`
    - Gray (`diff-line-info`) for `---`/`+++` headers
  - Created/Deleted events show contextual message instead of empty diff
  - Download Backup button for archived versions
- `resources/views/admin/file-revisions/index.blade.php`:
  - Replaced "Backup" column with "Actions" column containing:
    - Code-branch icon → Diff detail page
    - Download icon → Download backup (if available)
  - Empty state colspan updated (6→7)

## 46. Revisions History — Detail View + URL Tracking

**Problem:** Model revisions didn't show *where* (which admin page URL) the change was made.

**Solution:** Added `url` column + dedicated revision detail page.

**Files changed/created:**
- `database/migrations/2026_07_09_064749_add_url_to_revisions_table.php` — adds `url` VARCHAR column (nullable) after `ip_address`
- `app/Traits/Revisable.php` — `logRevision()` now captures `request()->fullUrl()` and stores in `url` field
- `app/Models/Revision.php` — `'url'` added to `$fillable`
- `routes/web.php` — added `GET /admin/revisions/{id}` → `admin.revisions.detail`
- `app/Http/Controllers/AdminController.php` — added `revisionDetail()` method
- `resources/views/admin/revisions/detail.blade.php` — new detail page:
  - Meta header: Model name, Record ID, Action badge, User, Date, URL (full, clickable)
  - Field changes table:
    - **Created**: all fields listed with green background values
    - **Deleted**: all fields listed with red background values
    - **Updated**: 3-column table (Field | Old Value red | New Value green); unchanged fields merged into single row
  - JSON values pretty-printed, long values scrollable
- `resources/views/admin/revisions/index.blade.php`:
  - Replaced "Changes" column with "URL" column + "Diff" action button
  - URL truncated to 40 chars with tooltip + external link icon
  - Empty state colspan updated (7→8)

## 47. Admin Sortable Tables — sortUrl / sortIndicator Helpers

**Problem:** Admin index tables had no standardized sortable column headers — each controller manually built sort links.

**Solution:** Created reusable helper functions in a global `helpers.php` file (autoloaded via PSR-4 in `composer.json`).

**Files changed:**
- `app/helpers.php` — **new file**:
  - `sortUrl($column, $currentSortBy, $currentSortDir)` — generates URL with `sort_by`/`sort_dir` query params, toggles asc/desc
  - `sortIndicator($column, $currentSortBy, $currentSortDir)` — renders `↑`/`↓` arrow when column is active
- `composer.json` — added PSR-4 autoload entry for `app/helpers.php`

## 48. Auth Pages — Password Visibility Toggle + Remember Me Fix

**Files changed (all 7 auth forms):**
- `resources/views/auth/login.blade.php` — added password toggle SVG eye icon, fixed "Remember Me" checkbox (added `name="remember" value="1"`), added `togglePassword()` JS function
- `resources/views/auth/register.blade.php` — same password toggle
- `resources/views/auth/rider-login.blade.php` — same password toggle
- `resources/views/auth/rider-register.blade.php` — same password toggle
- `resources/views/auth/vendor-login.blade.php` — same password toggle
- `resources/views/auth/vendor-register.blade.php` — same password toggle
- `resources/views/admin/login.blade.php` — same password toggle

## 49. About Page — Complete Redesign

**Files changed:**
- `resources/views/pages/about.blade.php` — **complete rewrite** (18 → 400 lines):
  - Hero section with overlay + "About Our Company" heading
  - Story grid with mission/vision/values cards
  - Stats counter section (Years, Products, Customers, Partners)
  - Team section with member cards
  - Features/why-choose-us grid
  - Custom CSS variables and responsive styles
  - All content embedded directly in the view

## 50. Pagination Redesign — Results Info + Smart Window

**Files changed:**
- `resources/views/vendor/pagination/gs-pagination.blade.php` — **complete rewrite**:
  - Added "Showing X to Y of Z results" info line above pagination
  - Smart window pagination: max 5 visible page links, centered around current page
  - Wrapped in `.item-pagination-container` div
  - Preserved existing `.gs-pagination` CSS classes

## 51. Compare Page — Cart Integration + Keep-3 Logic Fix

**Root cause:** Compare page "Add to Cart" button submitted product_id only; CartController had no name/price/image without additional DB query. Compare keep-3 query was broken (`whereNotIn` subquery with `limit` inside subquery — MySQL 5.7+ restriction).

**Files changed:**
- `resources/views/compare.blade.php` — added hidden inputs (`product_name`, `product_price`, `product_image`) to Add to Cart form; added `steve-btn` class; fixed compare badge JS to update both `.compare-count` and `#compare-count`
- `app/Http/Controllers/CompareController.php` — fixed `add()` keep-3 logic: replaced subquery with two-step `pluck`+`whereNotIn` approach

## 52. `imgTag()` Helper — Null Alt + Placeholder Fallback

**Files changed:**
- `app/Helpers/image.php`:
  - `$alt` parameter changed from `string` to `?string` (nullable)
  - Added `$alt = $alt ?? ''` default fallback
  - Added missing-file guard: if `$src` is empty or file doesn't exist, use placeholder image

## 53. Blog Layout Polish

**Files changed:**
- `resources/views/blog/show.blade.php` — added `.single-blog-content-navigation` class to prev/next post row
- `resources/views/blog/index.blade.php` — added `steve-btn` class to search button

## 54. `steve-btn` CSS Class Standardization

**Scope:** Consistent button styling across the storefront.

**Files changed:**
- `resources/views/shop.blade.php` — added `steve-btn` to filter/submit buttons
- `resources/views/blog/index.blade.php` — search button
- `resources/views/compare.blade.php` — Add to Cart and Remove buttons
- `resources/views/auth/login.blade.php` — Sign In button
- `resources/views/home.blade.php` — various CTA buttons
- `public/assets/front/css/style.css` — `.steve-btn` base styles and hover states

## 55. Search Autocomplete/Suggestions — AJAX Dropdown

**Files changed:**
- `routes/web.php` — added `GET /api/search/suggestions` route
- `app/Http/Controllers/ShopController.php` — added `suggestions()` method returning top 8 products by name match with thumbnail, price, category
- `resources/views/layouts/app.blade.php` — added `#searchSuggestions` div below search input; `#searchInput` inside `.input__group`
- `public/assets/front/css/style.css` — added `.search-suggestions` styles (absolute positioning, white bg, shadow, z-index)
- `public/assets/front/js/script.js` — added debounced AJAX handler (300ms) that fetches suggestions on keyup and renders product cards with thumbnail + name + price + category

## 56. Coupon Parse Error Fix

**Root cause:** `{{ Fixed ($) }}` in blade template was interpreted as Blade echo, causing parse error.

**Files changed:**
- `resources/views/admin/coupons/create.blade.php:23-24` — changed `{{ Fixed ($) }}` to `Fixed ($)` (plain text)

## 57. Admin Dashboard Consistency — Outline Buttons, Icons, Cards

**Scope:** Standardized admin pages (categories, staff, home-page, settings/header) to match consistent design pattern.

**Files changed:**
- `resources/views/admin/categories/index.blade.php` — outline buttons, icons, card/table layout
- `resources/views/admin/staff/index.blade.php` — same consistency
- `resources/views/admin/home-page/index.blade.php` — same consistency
- `resources/views/admin/settings/header.blade.php` — same consistency
- `public/assets/front/css/style.css` — added `.card .btn-outline-*:hover` overrides

## 58. User Dashboard Circular Action Buttons

**Scope:** Converted all user dashboard pages to use circular icon buttons matching `orders.blade.php` design.

**Files changed:**
- `resources/views/user/vehicles.blade.php` — edit/delete buttons converted to circular SVG icons
- `resources/views/user/addresses.blade.php` — edit/delete/set-default buttons converted to circular icons
- `resources/views/user/notifications.blade.php` — action buttons converted
- `resources/views/user/profile.blade.php` — edit action converted to dropdown with circular icons inside
- `public/assets/front/css/style.css` — added:
  - `.btn-edit` (orange #e67a38)
  - `.btn-check` (green #198754)
  - `.btn-set-default` (blue #3490f3)
  - "Default" badge: star icon only (no text)

## 59. `SetPageAttributes` Middleware — Auto-Generated Page IDs

**Problem:** No consistent `id` or `class` attribute on `<body>` for CSS targeting.

**Solution:** Created middleware that auto-generates `pageId` and `pageClass` from route names.

**Files changed/created:**
- `app/Http/Middleware/SetPageAttributes.php` — new middleware:
  - Route name `shop` → `id="shop_page"`, `class="shop-page"`
  - Route name `admin.login` → `id="admin-login_page"`, `class="admin-page admin-login"`
  - Pattern: `{prefix}-{rest}` for IDs/classes
- `bootstrap/app.php` — registered in `web` middleware group via `$middleware->web(append: [...])`
- `resources/views/layouts/app.blade.php` — `<body id="{{ $pageId ?? 'page' }}" class="... {{ $pageClass ?? 'default-page' }}">`
- `resources/views/admin/layouts/app.blade.php` — same pattern with `admin` fallback

**Examples:**
| Route | ID | Class |
|-------|-----|-------|
| home | `home_page` | `home-page` |
| shop | `shop_page` | `shop-page` |
| category.show | `category_page` | `category-page` |
| admin.login | `admin-login_page` | `admin-page admin-login` |
| admin.categories.index | `admin-categories-index_page` | `admin-page admin-categories-index` |

## 60. CSS `.table-action-col` Fix

**Root cause:** `.table-action-col { display: flex }` broke admin tables (should only apply to action buttons container).

**Files changed:**
- `public/assets/front/css/style.css` — changed selector from `.table-action-col { display: flex }` to `.table-action-col .action-buttons { display: flex }`

## 61. Search Dropdown Position Fix

**Root cause:** `#searchSuggestions` was outside `.input__group` container (which has `position: relative`), causing incorrect absolute positioning.

**Files changed:**
- `resources/views/layouts/app.blade.php` — moved `#searchSuggestions` div inside `.input__group` wrapper

## 62. Project Reports — CSV + DOC

**Files created:**
- `project_task_report.csv` — 75 tasks, 15 dates, 7 columns (ID, Task, Date, Status, Priority, Category, Notes)
- `project_task_report.doc` — HTML with MS Word XML namespaces (.doc extension but HTML content)

## 63. File Watcher — Systemd Auto-Start Service

**Problem:** File watcher (`file-watcher.mjs`) was a manual process — stopped on reboot/crash, needed manual restart every time.

**Solution:** Created systemd service with `Restart=always` for auto-start on boot + auto-restart on crash.

**Files created:**
- `file-watcher.service` — systemd unit file (ExecStart, Restart=always, RestartSec=5, User=jasaram)
- `setup-watcher.sh` — one-time setup script (needs `sudo`): copies service file to `/etc/systemd/system/`, daemon-reload, enable + start

**Setup (one-time):**
```bash
sudo ./setup-watcher.sh
```

**Status check:**
```bash
sudo systemctl status file-watcher
```

## 64. Search Bar Close on Esc Key

**Problem:** `#searchBar` (overlay search) was not closing on Escape key press.

**Root cause:** Esc key handler was accidentally placed INSIDE the overlay click handler (nested), so it only registered AFTER clicking the overlay — never on page load.

**Files changed:**
- `public/assets/front/js/script.js` — moved `$(document).on('keydown', ...)` handler OUTSIDE the overlay click handler so it registers on page load; also closes overlay along with search bar
- `resources/views/layouts/app.blade.php` — removed duplicate wrong handler targeting `.front-header-search`

## 65. CKEditor 5 Classic — All Textareas

**Problem:** All textareas were plain text inputs, no rich text editing.

**Solution:** Added CKEditor 5 Classic to all 28 textareas via `.texteditor` class.

**Files changed:**
- `resources/views/admin/layouts/app.blade.php` — removed dead TinyMCE init; changed CKEditor init from `#editor` to `.texteditor` (querySelectorAll)
- `resources/views/layouts/app.blade.php` — added CKEditor init for `.texteditor`
- **Admin (12 files):** brands/create, brands/edit, pages/create, pages/edit, blogs/create, blogs/edit, home-page/edit, images/edit, faqs/create, faqs/edit, products/create, products/edit — added `texteditor` class to all textareas
- **Frontend (6 files):** pages/contact, product, user/addresses, user/profile-edit, payment, user/profile — added `texteditor` class to all textareas

**Note:** `features` and `reviews_data` textareas in products kept WITHOUT texteditor (structured formats: newline-separated and JSON)

## 66. Product Description/Features HTML Rendering Fix

**Problem:** CKEditor saves HTML (`<p>` tags) but product page used `{{ }}` which escapes HTML — showed literal `<p>` tags.

**Files changed:**
- `resources/views/product.blade.php`:
  - Short description: `<p>{{ }}</p>` → `<div>{!! !!}</div>`
  - Tab description: `<p>{{ }}</p>` → `<div>{!! !!}</div>`
  - Features: `{{ $feature }}` → `{!! $feature !!}`
  - Policy text: `{!! nl2br(e()) !!}` → `{!! !!}` (CKEditor outputs HTML)
- `app/Http/Controllers/Admin/BlogController.php` — removed `deleteImageFiles()` from `forceDelete()` and `update()`
- `app/Http/Controllers/ProductController.php` — removed `deleteImageFiles()` from `forceDelete()` and `update()`
- `app/Http/Controllers/CategoryController.php` — removed `deleteImageFiles()` from `forceDelete()` and `update()`
- `app/Http/Controllers/Admin/BrandController.php` — removed `deleteImageFiles()` from `update()`
- `app/Http/Controllers/Admin/HomePageController.php` — removed `deleteImageFiles()` from `update()`

## 67. Image Files Never Deleted — Image Manager Only

**Problem:** Blog/product/category/brand images were permanently deleted on force-delete and replaced on update — user wanted images to NEVER be auto-deleted.

**Solution:** Removed all `deleteImageFiles()` calls from controllers. Only Image Manager (`admin/images`) handles file deletion.

**Files changed:**
- `app/Http/Controllers/Admin/BlogController.php` — removed from `forceDelete()`, `update()` (3 occurrences)
- `app/Http/Controllers/ProductController.php` — removed from `forceDelete()`, `update()` (2 occurrences)
- `app/Http/Controllers/CategoryController.php` — removed from `forceDelete()`, `update()` (3 occurrences)
- `app/Http/Controllers/Admin/BrandController.php` — removed from `update()` (1 occurrence)
- `app/Http/Controllers/Admin/HomePageController.php` — removed from `update()` (1 occurrence)
- Base `Controller::deleteImage()` kept — used only by UserController for user avatars

## 68. Image Manager Picker — Admin Forms

**Problem:** Admin image uploads use plain file inputs — no browsing/managing existing images.

**Solution:** Reusable Blade partial (`admin/partials/image-manager-picker.blade.php`) with Bootstrap modal, AJAX grid, search, pagination, and upload-from-picker.

**Architecture:**
- Partial: `admin/partials/image-manager-picker.blade.php` — modal with grid, search, pagination, upload-from-picker
- Routes: `admin/images/picker` (GET JSON), `admin/images/picker/upload` (POST)
- Controllers: ImageController `picker()` returns paginated JSON, `pickerStore()` handles upload
- Forms: hidden `image_from_manager` input + "Pick from Image Manager" button opens modal
- Selection: clicking image sets `image_from_manager` value + shows preview thumbnail

**Files changed/created:**
- `resources/views/admin/partials/image-manager-picker.blade.php` — **NEW** reusable partial
- `app/Http/Controllers/Admin/ImageController.php` — `picker()` + `pickerStore()` methods added
- `routes/web.php` — 2 routes added under admin group
- `resources/views/admin/products/create.blade.php` — picker button + include added
- `resources/views/admin/products/edit.blade.php` — picker button + include added
- `resources/views/admin/blogs/create.blade.php` — picker button + include added
- `resources/views/admin/blogs/edit.blade.php` — picker button + include added
- `resources/views/admin/categories/create.blade.php` — picker button + include added
- `resources/views/admin/categories/edit.blade.php` — picker button + include added
- `resources/views/admin/brands/create.blade.php` — picker button + include added
- `resources/views/admin/brands/edit.blade.php` — picker button + include added

**Usage:** Any admin form needing an image adds:
```html
<input type="hidden" name="image_from_manager" id="image_from_manager">
<button type="button" onclick="openPicker()">Pick from Image Manager</button>
```
Then `@include('admin.partials.image-manager-picker', ['pickerId' => 'unique_id'])` at bottom.

## 69. Controllers Handle `image_from_manager` Input

**Problem:** Forms had picker UI but controllers only handled file upload — `image_from_manager` was ignored.

**Solution:** All 4 controllers (8 methods) check `image_from_manager` first, then fall back to file upload.

**Logic (same in all):**
```php
if ($request->filled('image_from_manager')) {
    // copy from Image Manager storage → target directory
} elseif ($request->hasFile('image')) {
    // existing file upload logic
}
```

**Files changed:**
- `app/Http/Controllers/ProductController.php` — `store()` + `update()`
- `app/Http/Controllers/Admin/BlogController.php` — `store()` + `update()`
- `app/Http/Controllers/CategoryController.php` — `store()` + `update()`
- `app/Http/Controllers/Admin/BrandController.php` — `store()` + `update()`

**Image destinations:**
| Controller | Target directory |
|---|---|
| ProductController | `assets/images/thumbnails` |
| BlogController | `assets/images/blogs` |
| CategoryController | `assets/images/categories` |
| BrandController | `assets/images/brands` |

## 70. Image Manager Picker — Gallery Images (Multi-Select)

**Problem:** Gallery Images section only had file upload, no Image Manager integration.

**Solution:** Extended `image-manager-picker.blade.php` partial to support `$multiple` parameter for multi-select.

**Files changed:**
- `resources/views/admin/partials/image-manager-picker.blade.php` — added `$multiple` flag, multi-select state (`chosen[]` array), toggle select/deselect, chosen list with remove buttons, JSON output for multiple paths
- `resources/views/admin/products/create.blade.php` — gallery picker button + `@include` with `multiple => true`
- `resources/views/admin/products/edit.blade.php` — same
- `app/Http/Controllers/ProductController.php` — `store()` + `update()` handle `gallery_images_from_manager` (JSON array of paths), copies each to `products/gallery`

## 71. Gallery "Remove" — Record Only, File Kept

**Problem:** Gallery image "Remove" checkbox was deleting files from disk via `@unlink()` — violates "images never auto-deleted" rule (Task #67).

**Solution:** Removed `@unlink()` line. Only `$img->delete()` remains — removes the Image record, file stays safe in storage.

**Files changed:**
- `app/Http/Controllers/ProductController.php` — `update()` removed `@unlink()` from `delete_gallery_ids` loop

## 72. Notification Bell Icon — Header

**Problem:** No notification indicator in header. User had to visit dashboard to see notifications.

**Solution:** Added bell icon in top info bar (between currency and My Account), only for logged-in users.

**Features:**
- SVG bell icon with red badge showing unread count (9+ max)
- Direct link to `/user/notifications` (no dropdown)
- Badge auto-updates via `$unreadNotificationCount` view composer variable

**Files changed:**
- `resources/views/layouts/app.blade.php` — bell icon `<li>` added in info bar between currency separator and My Account, `@auth` wrapped, red badge with count

## 73. Notification View Composer — Global Unread Count

**Problem:** Bell icon needs unread count on every page, but it wasn't shared globally.

**Solution:** Added `$unreadNotificationCount` to existing `View::composer('*', ...)` in AppServiceProvider.

**Files changed:**
- `app/Providers/AppServiceProvider.php` — added `use Notification`, query `unread()->count()` when logged in, shared via `$view->with()`

## 74. NotificationHelper — All Event Types

**Problem:** No centralized way to create notifications for different events.

**Solution:** Created `NotificationHelper` static class with methods for all event types.

**Events supported:**
| Method | Trigger | Title |
|---|---|---|
| `orderPlaced($order)` | Order placed | "Order Placed Successfully" |
| `orderStatusChanged($order, $oldStatus)` | Admin status change | "Order Status Updated" |
| `welcomeUser($user)` | Registration | "Welcome to STAutoParts!" |
| `newProductInCategory($user, $product, $category)` | New product in wishlisted category | "New Product Available" |
| `promotion($user, $title, $message)` | Generic promotion | Custom |
| `bulkPromotion($userIds, $title, $message)` | Broadcast to many users | Custom |

**Files created:**
- `app/Helpers/NotificationHelper.php`

## 75. Swiper Free-Mode First Item Always Fully Visible

**Problem:** On home page, when user drags Swiper slides (free-mode), the first slide could be left partially off-screen (cut at the left edge).

**Solution:** Added `freeModeSticky: true` and `swipeToSlide: true` to free-mode Swipers to force first slide fully visible.

**Files changed:**
- `resources/views/home.blade.php:635` — `.home-cate-slider` now has `swipeToSlide: true` + `freeModeSticky: true`
- `public/assets/front/js/script.js:540` — `.featured-products.product-cards-slider` now has `freeModeSticky: true`
- `public/assets/front/js/script.js:564` — `.best-selling.product-cards-slider` now has `freeModeSticky: true`

**Files changed:**
- `app/Providers/AppServiceProvider.php` — registered helper via `require_once`

## 220. Notification Triggers — Controller Integration

**Problem:** Helper existed but no controllers called it.

**Files changed:**
- `app/Http/Controllers/CartController.php` — `NotificationHelper::orderPlaced($dbOrder)` after order creation
- `app/Http/Controllers/Admin/OrderController.php` — `NotificationHelper::orderStatusChanged($order, $oldStatus)` in `updateStatus()`
- `app/Http/Controllers/AuthController.php` — `NotificationHelper::welcomeUser($user)` after registration
- `app/Http/Controllers/ProductController.php` — `NotificationHelper::newProductInCategory(...)` in `store()`, notifies users who wishlisted products in same category

## 76. Admin Layout CSRF Meta Tag Fix

**Problem:** Admin layout (`admin/layouts/app.blade.php`) was missing `<meta name="csrf-token">` — caused JavaScript errors in Image Manager picker (and any AJAX) because `document.querySelector('meta[name="csrf-token"]')` returned null.

**Solution:** Added `<meta name="csrf-token" content="{{ csrf_token() }}">` to admin layout `<head>`.

**Files changed:**
- `resources/views/admin/layouts/app.blade.php` — added CSRF meta tag

## 77. Notifications Auto-Read on Page Visit

**Problem:** User had to manually click "Mark Read" or "Mark All" — unnecessary friction.

**Solution:** `notifications()` method now auto-marks all unread notifications as `is_read = true` when the page loads. Bell icon badge resets to 0 on next page load.

**Files changed:**
- `app/Http/Controllers/DashboardController.php` — `notifications()` now calls `Notification::where(...)->unread()->update(['is_read' => true])` before fetching

## 78. Admin Panel `.btn:hover` Border Fix

**Problem:** `.btn:hover { border-color: var(--hov-primary); }` was showing unwanted button border/background on hover over status toggle badges in admin tables.

**Files changed:**
- `public/assets/front/css/style.css` — added `border-color: transparent !important;` to `.admin-page td .btn:hover` rule

## 79. My Reviews — Purchased Products + Inline Review Form

**Problem:** "My Reviews" page only showed products the user already reviewed. No way to write reviews for purchased-but-unreviewed products.

**Files changed:**
- `app/Http/Controllers/DashboardController.php` — `reviews()` method rewritten:
  - Added `use App\Models\OrderItem`
  - Fetches purchased products via OrderItem (order status: delivered/processing/shipped)
  - Builds unified `$items` array with `status` field (pending/reviewed)
  - Pending items sorted first
  - Passes `$items` instead of `$reviews` to view
- `resources/views/user/reviews.blade.php` — rewritten:
  - Single table with 3 columns: Product | Review | Status/Date
  - Pending rows: inline star picker + textarea + submit button within table cell (max-width:280px)
  - Reviewed rows: star rating + truncated review text + date
  - AJAX submit to existing `POST /product/{slug}/review` route
  - Success: row updates inline (form → review display) + toastr notification
  - Added `OrderItem` import to controller

## 80. Product Page — Review Form Always Visible + Server-Side Validation

**Problem:** Review form was conditionally hidden via Blade `@auth`/`@if($hasPurchased)` — users couldn't even see the form if not logged in or hadn't purchased.

**Solution:** Form always visible; server validates eligibility on submit.

**Files changed:**
- `resources/views/product.blade.php`:
  - Removed `@auth`/`@if($hasPurchased)` conditionals — review form always rendered
  - Added `btn btn-primary` class to Submit Review button for proper styling
  - Rewrote fetch handler to check HTTP status:
    - 401 → "Please login to write a review" with login link
    - 403 → "You must purchase this product to write a review"
    - 200 success → review added + form reset
    - else → error message displayed
  - Added `else` block for `data.success === false` responses

## 81. Contact Seller Modal — Toast + Auto-Close

**Problem:** "Ask a question" form showed success as an inline alert div instead of toast, and didn't close the modal.

**Files changed:**
- `resources/views/product.blade.php`:
  - Success: `toastr.success()` instead of alertBox update
  - Modal auto-closes via `bootstrap.Modal.getInstance(...).hide()`
  - Form still resets after submission

## 82. View Cache Clear — `Undefined variable $reviews`

**Problem:** After changing `$reviews` to `$items` in DashboardController, cached compiled view still referenced old `$reviews` variable causing 500 error.

**Fix:** `php artisan view:clear` — compiled views cleared.

## 83. Laravel Page Builder Package — Full Plugin Architecture

**Description:** Built a complete, reusable page builder package (`stevestore/laravel-page-builder`) as an isolated plugin in `/var/www/html/laravel-page-builder/`, connected to host via composer path repository.

**Package structure:**
- `composer.json` — version `1.0.0`, minimum-stability stable
- `src/PageBuilderServiceProvider.php` — core provider: loads views/routes/migrations/lang/assets/commands, conditional on enabled, registers components explicitly, aliases BlockRegistry
- `src/PageBuilder.php` — facade → resolves `'page-builder'` (BlockRegistry)
- `src/Blocks/Block.php` — abstract base class with `$name`, `$label`, `$icon`, `$group`, `$singleton`, abstract `fields()` method (regular public properties, NOT abstract properties — PHP limitation)
- `src/Blocks/BlockRegistry.php` — register/get/all/grouped/has/names/unregister
- `src/Traits/HasBlocks.php` — model trait: getBlocks, setBlocks, addBlock, removeBlock, moveBlock, duplicateBlock, updateBlock
- `src/View/Components/Editor.php` — `<x-page-builder>` component, imports `SteveStore\PageBuilder\PageBuilder` (NOT `Facades` subdirectory)
- `src/View/Components/Blocks.php` — `<x-page-blocks>` frontend renderer
- `src/Helpers/StyleHelper.php` — inline CSS builder for Elementor-style per-element styling
- `config/page-builder.php` — models (page→Page, blog→Blog), blocks, enabled toggle, middleware, prefix
- `database/migrations/2026_07_16_000000_add_content_blocks_to_tables.php` — adds `content_blocks` JSON (nullable, no `after()` clause)
- `lang/en/page-builder.php` — translation strings
- `README.md` — full documentation
- `LICENSE` — MIT

**6 Built-in blocks:**
1. HeroBanner (`hero`) — singleton, section/title/subtitle/bg image/overlay/btn
2. TextBlock (`text`) — rich text with alignment/width
3. ImageGallery (`gallery`) — multi-image, columns, gutter
4. FeaturesGrid (`features`) — repeater with icon/title/description
5. TestimonialCarousel (`testimonials`) — repeater with name/role/avatar/quote/rating
6. CTABanner (`cta`) — singleton, title/subtitle/btn

**Host integration (minimal — only 2 files modified):**
- `resources/views/layouts/app.blade.php` — `@stack('page-builder-css')` before `</head>`, `@stack('page-builder-js')` before `</body>`
- `resources/views/admin/layouts/app.blade.php` — same stacks + `<meta name="csrf-token">`

**Models using HasBlocks trait:**
- `app/Models/Page.php` — `use HasBlocks`
- `app/Models/Blog.php` — `use HasBlocks`

**Backend:**
- `bootstrap/providers.php` — includes `SteveStore\PageBuilder\PageBuilderServiceProvider::class`
- `.env` — `PAGE_BUILDER_ENABLED=true`
- `composer.json` — repositories path repo pointing to `../laravel-page-builder`

## 84. Page Builder — Install/Uninstall Commands (Schema-Based)

**Files created:**
- `src/Console/Commands/InstallCommand.php` — adds provider to bootstrap/providers.php, Schema-based column creation (adds `content_blocks` JSON to pages + blogs tables), publishes assets, sets .env `PAGE_BUILDER_ENABLED=true`. Does NOT call `migrate` (avoids host migration conflicts)
- `src/Console/Commands/UninstallCommand.php` — Schema-based column removal, removes provider, disables .env

## 85. Page Builder — Block Editor UI (Drag-Drop, AJAX Save, Image Upload)

**Files created/updated:**
- `resources/views/editor/index.blade.php` — component view with inline CSS (no `@push` — CSS loaded directly in component HTML since `@push` inside Blade component doesn't propagate to parent layout's `@stack`)
- `resources/views/admin/page-builder/editor.blade.php` — standalone editor extending admin layout, uses `@push('page-builder-js')` for JS loading (works because it's a `@section`-based child view, not a component)
- `resources/views/admin/page-builder/index.blade.php` — listing with New Page/Blog buttons
- `resources/views/admin/page-builder/create.blade.php` — create form with auto-slug, blog toggle
- `resources/js/page-builder.js` — `$.ajaxSetup` with CSRF header at top, `(function($){})(jQuery)` wrapper, SortableJS drag-reorder, AJAX save/upload, gallery, repeater, block CRUD
- `resources/css/page-builder.css` — full editor styling (toolbar, block list, field groups, image upload, gallery, repeater, block picker, empty state, preview mode)

**Key JS behaviors:** Add block via AJAX form fetch, serialize all blocks by parsing `name` attributes with regex `\[data\]\[(.+?)\]`, drag-drop with Sortable.js, image/gallery upload via FormData, repeater templates with `__INDEX__`/`__NUM__` placeholders, dirty tracking with beforeunload warning.

## 86. Page Builder — CSRF Token + jQuery Load Order Fix

**Problem:** `page-builder.js` loaded BEFORE jQuery in admin layout (jQuery at line 67, `@yield('content')` at line 58). Also missing CSRF meta tag caused 419 errors on AJAX POSTs.

**Files changed:**
- `resources/views/admin/layouts/app.blade.php` — added `<meta name="csrf-token" content="{{ csrf_token() }}">` in `<head>`
- `resources/views/admin/page-builder/editor.blade.php` — JS loaded via `@push('page-builder-js')` inside `@section` (propagates to layout's `@stack` AFTER jQuery loads)
- `resources/js/page-builder.js` — added `$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } })` at top

## 87. Page Builder — Facade Import Fix + BlockRegistry Alias

**Problem:** `Facades\PageBuilder` import resolved to `SteveStore\PageBuilder\Facades\PageBuilder` but class is at `SteveStore\PageBuilder\PageBuilder` (no `Facades` subdirectory). Also `BlockRegistry` needed alias registration.

**Files changed:**
- `src/View/Components/Editor.php` — import changed from `Facades\PageBuilder` to `SteveStore\PageBuilder\PageBuilder`
- `src/PageBuilderServiceProvider.php` — registered `BlockRegistry` as `'page-builder'` alias in app container

## 88. Page Builder — `PageBuilder::blocks()->get()` → `PageBuilder::get()` Fix

**Problem:** `Editor.php` called `PageBuilder::blocks()->get()` and `PageBuilder::blocks()->grouped()` but facade resolves directly to `BlockRegistry` (no `blocks()` method).

**Files changed:**
- `src/View/Components/Editor.php` — changed `PageBuilder::blocks()->get()` to `PageBuilder::get()` and `PageBuilder::blocks()->grouped()` to `PageBuilder::grouped()`

## 89. Page Builder — Frontend Block Rendering on Pages & Blogs

**Files changed:**
- `resources/views/pages/show.blade.php` — added `<x-page-blocks :model="$page" />` after content div
- `resources/views/blog/show.blade.php` — added `<x-page-blocks :model="$blog" />` after blog content div

## 90. Page Builder — Blade Component Registration Fix

**Problem:** `Blade::componentNamespace()` was registering components under `SteveStore\PageBuilder\View\Components` namespace, but the actual class is `Editor` not `PageBuilder` — component name mismatch.

**Files changed:**
- `src/PageBuilderServiceProvider.php` — replaced namespace registration with explicit: `Blade::component('page-builder', Editor::class)`

## 91. Page Builder — Slug Uniqueness on Create

**Problem:** Creating a new page/blog with duplicate slug overwrites existing record.

**Files changed:**
- `src/Http/Controllers/PageBuilderController.php` — `store()` method auto-appends `-1`, `-2` etc. if slug already exists in database

## 92. Page Builder — Admin Sidebar Link

**Files changed:**
- `resources/views/admin/partials/sidebar.blade.php` — added "Page Builder" link under Content section → `route('page-builder.index')`

## 93. Page Builder — Language File

**Files created:**
- `lang/en/page-builder.php` — translation strings for page builder UI

## 94. Elementor-Style Per-Element Styling System

**Description:** Added comprehensive Elementor-style styling controls to all 6 blocks. Each block's elements (title, subtitle, button, section, etc.) get independent style panels with Typography, Colors, Spacing, Border, and Size controls.

**Files created:**
- `src/Helpers/StyleHelper.php` — static PHP class with `build($data, $prefix)` method that generates inline CSS strings from saved data. Individual methods: `typography()`, `colors()`, `spacing()`, `border()`, `size()`. Supports 22 property types per element prefix.
- `resources/views/editor/partials/style-fields.blade.php` — reusable collapsible style panel partial. Parameters: `$prefix`, `$label`, `$data`, `$show` (array of groups to display). Uses `<details>` for collapsible sections. Each group has compact form controls (11px font, 30px color pickers, 4-column spacing grid).

**Files updated (editor forms — added style panel includes):**
- `resources/views/editor/blocks/hero.blade.php` — 4 style sections: Section, Title, Subtitle, Button. Removed old `btn_color`/`text_color` fields (now in style panels)
- `resources/views/editor/blocks/text.blade.php` — 2 style sections: Section, Content
- `resources/views/editor/blocks/gallery.blade.php` — 2 style sections: Section, Image
- `resources/views/editor/blocks/features.blade.php` — 6 style sections: Section, Title, Subtitle, Icon, Feature Title, Feature Desc
- `resources/views/editor/blocks/testimonials.blade.php` — 5 style sections: Section, Title, Card, Quote, Name
- `resources/views/editor/blocks/cta.blade.php` — 4 style sections: Section, Title, Subtitle, Button. Removed old `background_color`/`text_color`/`btn_color` fields

**Files updated (frontend views — apply inline styles):**
- `resources/views/frontend/blocks/hero.blade.php` — `StyleHelper::spacing($d, 'section')`, `StyleHelper::build($d, 'title')`, `StyleHelper::build($d, 'subtitle')`, `StyleHelper::build($d, 'btn')`
- `resources/views/frontend/blocks/text.blade.php` — `StyleHelper::spacing($d, 'section')`, `StyleHelper::build($d, 'content')`
- `resources/views/frontend/blocks/gallery.blade.php` — `StyleHelper::spacing($d, 'section')`, `StyleHelper::build($d, 'image')`
- `resources/views/frontend/blocks/features.blade.php` — `StyleHelper::spacing($d, 'section')`, `StyleHelper::build($d, 'sec_title')`, `StyleHelper::build($d, 'sec_subtitle')`, `StyleHelper::build($d, 'icon')`, `StyleHelper::build($d, 'feat_title')`, `StyleHelper::build($d, 'feat_desc')`
- `resources/views/frontend/blocks/testimonials.blade.php` — `StyleHelper::spacing($d, 'section')`, `StyleHelper::build($d, 'title')`, `StyleHelper::build($d, 'card')`, `StyleHelper::build($d, 'quote')`, `StyleHelper::build($d, 'name')`
- `resources/views/frontend/blocks/cta.blade.php` — `StyleHelper::spacing($d, 'section')`, `StyleHelper::build($d, 'title')`, `StyleHelper::build($d, 'subtitle')`, `StyleHelper::build($d, 'btn')`

**Files updated (PHP block classes — validation rules):**
- `src/Blocks/BuiltIn/HeroBanner.php` — added style validation for section/title/subtitle/btn prefixes × 22 keys
- `src/Blocks/BuiltIn/TextBlock.php` — added style validation for section/content prefixes
- `src/Blocks/BuiltIn/ImageGallery.php` — added style validation for section/image prefixes (includes width/height)
- `src/Blocks/BuiltIn/FeaturesGrid.php` — added style validation for section/sec_title/sec_subtitle/icon/feat_title/feat_desc prefixes
- `src/Blocks/BuiltIn/TestimonialCarousel.php` — added style validation for section/title/card/quote/name prefixes
- `src/Blocks/BuiltIn/CTABanner.php` — added style validation for section/title/subtitle/btn prefixes

**Files updated (CSS):**
- `resources/css/page-builder.css` — added ~100 lines of style panel CSS: `.pb-style-section`, `.pb-style-panel`, `.pb-style-header` (with chevron rotation), `.pb-style-body`, `.pb-style-group`, `.pb-style-group-title`, `.pb-style-label`, compact form controls, `.pb-color-wrap`

**Style properties per element:**
| Category | Properties |
|---|---|
| Typography | Font Family (12 fonts), Font Size, Font Weight (7 levels), Line Height, Letter Spacing, Text Transform, Text Decoration |
| Colors | Text Color, Background Color |
| Spacing | Padding (T/R/B/L), Margin (T/R/B/L) |
| Border | Radius, Width, Style (solid/dashed/dotted/double), Color |
| Size | Width, Height |

**Design decisions:**
- All fields optional — blank = no inline style = default CSS applies (backward compatible)
- Collapsible `<details>` panels keep editor clean
- Existing color fields migrated into style sections (no duplicates)
- Naming convention: `{element}_{property}` — no nested arrays, JS serialization works as-is
- StyleHelper as PHP class — reusable across all blocks, easy to extend

**Total: 21 files (2 new, 19 updated)**

## 95. Single Product Gallery Slider — Broken Loading Fix

**Problem:** Product image gallery slider appeared broken on page load — flash of unstyled/uninitialized slides visible before Slick.js initialized.

**Root causes:**
1. `$(window).on('load')` waited for ALL page resources before initializing Slick — long delay with broken layout visible
2. No FOUC prevention for gallery slider elements (unlike other sliders which had `visibility: hidden` until `.slick-initialized`)
3. No min-height on main slider container — layout shifted when images loaded

**Files changed:**
- `resources/views/layouts/app.blade.php` — added `.details_slider_thumb:not(.slick-initialized)` and `.details_slider_nav:not(.slick-initialized)` to the `visibility: hidden` FOUC prevention CSS rule
- `resources/views/product.blade.php`:
  - Changed `$(window).on('load')` → `$(document).ready()` for instant slider initialization
  - Added `waitForAnimate: false` to main slider options (was only on nav slider)
  - Added `min-height: 450px` to `.details_slider_thumb` to prevent layout shift

## 96. Menu & Footer Hover Underline Effect (20px `::after`)

**Files changed:**
- `public/assets/front/css/style.css`:
  - Main nav `li>a::after` — changed from `width: 0%` / `height: 1px` to `width: 0px` / `height: 2px`, added `opacity: 0; visibility: hidden;`
  - Main nav `li:hover>a::after` — changed from `width: 100%` to `width: 20px`, added `opacity: 1; visibility: visible;`
  - Main nav `li.active>a::after` — changed from `width: 100%` to `width: 20px`, added `opacity: 1; visibility: visible;`
  - Footer `.gs-footer-section .footer-row ul>li` — added `position: relative`
  - Footer `.gs-footer-section .footer-row ul>li::after` — new base style (width: 0, height: 2px, opacity: 0, visibility: hidden, transition)
  - Footer `.gs-footer-section .footer-row ul>li:hover::after` — new hover style (width: 20px, opacity: 1, visibility: visible)

## 97. Menu Active Page Highlight

**Problem:** Active menu item never highlighted — `active` class was on `<a>` but CSS targets `li.active>a`. Also subpages (`/product/*`, `/category/*`) didn't highlight "Products".

**Files changed:**
- `resources/views/partials/nav-menu.blade.php`:
  - Added `active` class to `<li>` (not just `<a>`)
  - Improved URL matching: exact match, prefix match (`str_starts_with`), and special `/shop` → `/product`+`/category` subpage matching

## 98. Admin Dashboard — Chart.js Graphs (Orders by Status + Monthly Revenue)

**Problem:** Dashboard showed order status and monthly revenue as plain HTML progress bars — no visual charts.

**Solution:** Replaced progress bars with Chart.js v4.4.7 interactive charts.

**Files changed:**
- `resources/views/admin/layouts/app.blade.php` — added Chart.js CDN (`chart.js@4.4.7/dist/chart.umd.min.js`) after toastr script
- `app/Http/Controllers/AdminController.php` — extracted `$ordersByStatus` and `$monthlyRevenue` into variables, added `$ordersByStatusJson` (pluck count by status) and `$monthlyRevenueJson` (pluck total by month)
- `app/Http/Controllers/DashboardController.php` — same JSON data added to both `index()` and `staffDashboard()` methods (3 total render points)
- `resources/views/admin/dashboard.blade.php`:
  - Replaced progress bar sections with `<canvas id="ordersByStatusChart">` (doughnut) and `<canvas id="monthlyRevenueChart">` (bar)
  - Added `@push('scripts')` block with Chart.js initialization
  - Doughnut: status colors (pending=yellow, processing=cyan, shipped=blue, delivered=green, cancelled=red), center text showing total, bottom legend with circle point style
  - Bar: green gradient bars, months on X-axis (short format), currency-formatted Y-axis (K/L suffixes), rounded bar corners
  - Currency symbol sourced from session-based config (`currencies` config) — works with all currencies
  - Empty state handled gracefully (text message instead of empty chart)

**Chart details:**
| Chart | Type | Features |
|-------|------|----------|
| Orders by Status | Doughnut (60% cutout) | Center total count, color-coded slices, percentage tooltips, bottom legend |
| Monthly Revenue | Bar (gradient fill) | Last 12 months, currency formatting, rounded corners, hover tooltips with full amount |

## 99. Category Slider Nav Buttons — Not Working

**Problem:** Home page category section had custom `cate-prev`/`cate-next` buttons in HTML but no JavaScript click handlers — buttons did nothing. Also `arrows: true` showed Slick's own built-in arrows inside the slider, conflicting with the custom buttons.

**Files changed:**
- `resources/views/home.blade.php` — `@section('scripts')` block:
  - Changed `arrows: true` → `arrows: false` (disabled Slick's built-in arrows)
  - Added `.cate-prev` click handler → `$('.home-cate-slider').slick('slickPrev')`
  - Added `.cate-next` click handler → `$('.home-cate-slider').slick('slickNext')`

## 100. Page Builder — foreach() on String Error (Gallery, Features, Testimonials)

**Problem:** `foreach() argument must be of type array|object, string given` — opening Page Builder editor for pages with gallery/features/testimonials blocks. Root cause: JS serializer double-encodes repeater/gallery array fields as JSON strings inside already-JSON `content_blocks`.

**Files changed (6):**
- `laravel-page-builder/resources/views/editor/blocks/gallery.blade.php` — `$images` json_decode if string
- `laravel-page-builder/resources/views/editor/blocks/features.blade.php` — `$features` json_decode if string
- `laravel-page-builder/resources/views/editor/blocks/testimonials.blade.php` — `$testimonials` json_decode if string
- `laravel-page-builder/resources/views/frontend/blocks/gallery.blade.php` — same fix for frontend
- `laravel-page-builder/resources/views/frontend/blocks/features.blade.php` — same fix for frontend
- `laravel-page-builder/resources/views/frontend/blocks/testimonials.blade.php` — same fix for frontend

## 101. Page Builder — Replace Native File Upload with Image Manager

**Problem:** All "Choose Image" / "+Add Images" / "Avatar" buttons in Page Builder editor used native `<input type="file">` + AJAX upload to a separate upload endpoint. User wanted the same Image Manager picker used elsewhere in admin panel.

**Solution:** Integrated the host site's `admin.partials.image-manager-picker` partial into the Page Builder editor. Modified JS to open Image Manager modal instead of native file picker, with callback overrides to route selected images back to the correct block fields.

**Files changed (6):**
- `laravel-page-builder/resources/views/editor/index.blade.php` — added `@include` for two Image Manager picker modals (`pb_single` for single image, `pb_multi` for gallery), added `data-storage-url` attribute
- `laravel-page-builder/resources/js/page-builder.js` — replaced `openUpload()` and `openGalleryUpload()` methods (removed native file input + AJAX upload), added callback overrides for `impConfirm_pb_single` and `impConfirm_pb_multi` that route selected image paths to the correct hidden inputs + previews, added `storageUrl`, `_pendingInput`, `_pendingPreview`, `_galleryList`, `_galleryBlockIndex` properties
- `laravel-page-builder/resources/views/editor/blocks/hero.blade.php` — added `fa-images` icon to "Choose Image" button
- `laravel-page-builder/resources/views/editor/blocks/gallery.blade.php` — changed `fa-plus` to `fa-images` icon on "Add Images" button
- `laravel-page-builder/resources/views/editor/blocks/testimonials.blade.php` — added `fa-images` icon to both "Avatar" buttons (existing + repeater template)

## 102. Slick Slider Gap Fix — Only Between Active Items

**Problem:** `gap: 24px` on `.slick-track` applied spacing to ALL slides including cloned ones, causing visual inconsistency. Also first/last visible items had unwanted outer padding on both sides.

**Solution:** Removed `gap` from `.slick-track`, moved spacing to individual slide padding, and used negative margin on `.slick-list` to eliminate outer edge padding.

**Files changed:**
- `public/assets/front/css/style.css`:
  - **Product Cards Slider** (Featured + Best Selling): removed `gap: 24px` from `.product-cards-slider .slick-track`, added `padding: 0 12px; box-sizing: border-box;` to `.product-cards-slider .slick-track .slick-slide`, added `margin: 0 -12px` to `.product-cards-slider .slick-list`
  - **Category Slider**: added `padding: 0 6px; box-sizing: border-box;` to `.home-cate-slider .slick-slide`, added `margin: 0 -6px` to `.home-cate-slider .slick-list`, added `display: flex` to `.home-cate-slider .slick-track`
   - Removed empty `.product-cards-slider .slick-track{}` rule from `@media (max-width: 767.98px)` block

## 103. Shop Page — List/Grid View Add-to-Cart (Two Separate Blocks)

**Problem:** `.add-to-cart` was inside `.img-wrapper` (which has `overflow: hidden`), so in list view the add-to-cart button couldn't display properly below the content.

**Solution:** Two separate `.add-to-cart` blocks — one for grid, one for list. CSS shows/hides the correct one based on view mode.

**Files changed:**
- `resources/views/shop.blade.php`:
  - Added second `.add-to-cart` block inside `.content-wrapper` (after ratings, before closing div)
  - Original `.add-to-cart` inside `.img-wrapper` stays for grid view
- `public/assets/front/css/style.css`:
  - Base rule (line 1945): changed `.single-product .add-to-cart, .single-product-list-view .add-to-cart` → `.single-product .add-to-cart` only (grid view)
  - Added `.single-product .content-wrapper .add-to-cart { display: none; }` — hide list block in grid view
  - Hover rule: changed to `.single-product:hover .add-to-cart` only
  - List view: `.single-product-list-view .img-wrapper .add-to-cart { display: none; }` — hide grid block in list view
  - List view: `.single-product-list-view .content-wrapper .add-to-cart { display: flex; margin-top: 32px; }` — show list block
  - Removed conflicting `margin-top: -100px` hack at line 15371
  - Updated media query to target `.content-wrapper .add-to-cart`

## 104. Mobile Menu — Nav-Tab (Main Menu + Categories)

**Problem:** Mobile menu had a flat list of links — no categories tab. Demo site had a tabbed interface with "MAIN MENU" and "CATEGORIES" tabs.

**Solution:** Added Bootstrap 5 tab navigation to mobile menu with two tabs. Auth buttons placed outside tabs so they're always visible.

**Files changed:**
- `resources/views/layouts/app.blade.php`:
  - Mobile menu restructured: `mobile-menu-top` → auth buttons → `<nav>` with tab buttons → two `<div class="tab-content">` panes
  - Tab 1 (Main Menu): existing mobile-nav-menu with nested submenus
  - Tab 2 (Categories): 3-level category accordion matching shop sidebar style (product counts, +/- toggle, zero-count greyed out)
  - Auth buttons (`.mobile-menu-account`) placed between logo and tabs — visible in both tabs
- `app/Providers/AppServiceProvider.php`:
  - Added `$mobileCategoryTree` to View::composer: `Category::topLevel()->where('status', true)->with('childrenRecursive')` with product counts
  - Added `$setDescendantCount` recursive closure to compute + set `descendant_count` on ALL categories (top, sub, child)
  - Variable named `$mobileCategoryTree` (not `$categoryTree`) to avoid conflict with ShopController's `$categoryTree`
- `public/assets/front/css/custom.css`:
  - Added `.mobile-nav-tabs` styling (background, border, active tab bottom border)
  - Added `.mobile-menu .tab-content .product-cat-widget` styles matching shop sidebar
  - Added `.cat-toggle-btn` +/- icon toggle CSS (moved from shop.blade.php inline style to global)
- `public/assets/front/js/script.js`:
  - Added global accordion close-siblings behavior: `$(document).on('show.bs.collapse', '.main-list > .collapse', ...)` — works in both shop sidebar and mobile categories

**Bug fixed during implementation:** `descendant_count` was only set on top-level categories, not on subcategories. Changed `computeDescendantCount` (return only) to `setDescendantCount` (sets `$category->descendant_count = $total` recursively on every level).

## 105. Blog Page Redesign — Demo Site Match (Index + Show)

**Problem:** Blog pages used generic Bootstrap card layout (`blog-card` with shadow/rounded) and plain sidebar. Demo site had horizontal card layout (`gs-main-single-blog`) with proper sidebar widgets (`single-blog-widget`).

**Solution:** Rewrote both blog templates to match demo site structure using existing CSS classes from `style.css`.

**Files changed:**
- `resources/views/blog/index.blade.php` — **complete rewrite**:
  - Layout: `gs-blog-wrapper > container > row flex-column-reverse flex-lg-row`
  - Sidebar (col-lg-4, first in DOM for mobile stacking): `gs-blog-sidebar-wrapper` with 3 widgets:
    - Search: `single-blog-widget` + `search-form` + `input-box` + SVG search icon
    - Categories: `single-blog-widget` + `cat-wrapper` with blog count per category
    - Recent Posts: `single-blog-widget` + `gs-sm-recent-post-wrapper` with thumbnail + title + date
  - Main content (col-lg-8): `gs-main-blog-wrapper` with `gs-main-single-blog` horizontal cards:
    - Left: `left-side-content > img.blog-img` (312px fixed width, object-fit cover)
    - Right: `right-side-content` (title, excerpt, date SVG, `template-btn outlinee-btn` read more)
  - Pagination: `gs-pagination` via `vendor.pagination.gs-pagination` view
  - Empty state: centered "No blog posts found" message

- `resources/views/blog/show.blade.php` — **complete rewrite**:
  - Breadcrumb: "Blog Details" with Home > Blog > Title trail
  - Layout: `gs-blog-wrapper > container > row`
  - Main content (col-lg-8): `gs-blog-details-wrapper > gs-blog-card`:
    - Featured image: `img.fea-img.img-fluid`
    - Meta info: `meta-info-wrapper` with `single-meta` items (author, date, category with SVG icons, border-right separators)
    - Title: `h4.fea-title.mb-24`
    - Content: `div.blog-content-body` (raw HTML from CKEditor)
    - `<x-page-blocks>` preserved for Page Builder blocks
  - Removed: Previous/Next post navigation (not in demo)

- `app/Http/Controllers/BlogController.php` — `show()` method:
  - Added `$recentBlogs` and `$categories` queries (same as index method)
  - Removed `$previous` and `$next` queries
  - Now passes `$recentBlogs` and `$categories` to view for sidebar

- `public/assets/front/css/custom.css` — added:
  - `.blog-content-body` styles for CKEditor raw HTML (paragraphs, images, headings, lists, blockquotes, links)
  - `.blog-sidebar-toggle` — hidden on desktop, visible on mobile (flex, full-width)
  - `.gs-blog-sidebar-wrapper` mobile off-canvas: fixed left -300px, 280px width, z-index 1060, transition, `.active` slides to left:0
  - `.blog-sidebar-overlay` — hidden by default, `.active` shows fixed overlay

- `resources/views/blog/index.blade.php` — mobile sidebar toggle:
  - Added `.blog-sidebar-toggle` button (hamburger SVG + "Sidebar" label)
  - Added `.blog-sidebar-overlay` div
  - Added `@section('scripts')` with JS: openSidebar/closeSidebar, overlay click, Escape key

- `resources/views/blog/show.blade.php` — same mobile sidebar toggle (button + overlay + JS)

**What stays unchanged:**
- Breadcrumb section HTML (already matched demo)
- All CSS in `style.css` (demo classes already present)
- Blog model, routes, admin controllers
- Footer, header, mobile menu
- Category filter route (`blog.category`) — uses same `blog.index` view

## 106. Mobile Shop Page — Auto Grid View on 1fr (≤992px)

**Problem:** Shop page layout toggle (grid/list) persisted from desktop to mobile, causing list view to look broken on small screens.

**Solution:** Added JavaScript listener that forces grid view when viewport width ≤992px.

**Files changed:**
- `resources/views/shop.blade.php` — added `checkMobileLayout()` function:
  - Detects `window.innerWidth <= 992` → calls `applyLayout('grid')`
  - Runs on page load and on `resize` event
  - Desktop preference preserved via localStorage

## 107. Admin Product Details Page (Show)

**Problem:** No dedicated product details page in admin — all product info was only in create/edit forms.

**Solution:** Created a dedicated read-only product details page showing all product information in an organized 2-column layout.

**Files changed:**
- `routes/web.php` — added `GET /products/{id}` → `admin.products.details`
- `app/Http/Controllers/ProductController.php` — added `details($id)` method:
  - Loads product with `category`, `brand`, `galleryImages` relationships
  - Returns `admin.products.show` view
- `resources/views/admin/products/show.blade.php` — **new file**:
  - Layout: 2-column grid (col-md-8 / col-md-4)
  - Left column: Product image + gallery, Description, Policy, Features
  - Right column: Product Summary table, Vehicle Details, Metadata, Edit button
  - Uses consistent admin card styling with `shadow-sm`, `border-0`
- `resources/views/admin/products/index.blade.php` — added eye icon button:
  - `action-btn btn-view` class (dark #1f0300)
  - Links to `admin.products.details` route
  - Placed before Edit button in action column

## 108. CSV Import — Auto-Create Missing Categories & Brands

**Problem:** CSV import would fail with error messages when category or brand names didn't exist in the database.

**Solution:** Auto-create missing categories and brands during import.

**Files changed:**
- `app/Http/Controllers/ProductController.php` — `import()` method:
  - Added `$categoriesCreated` and `$brandsCreated` counters
  - When category not found → create new Category with name, slug, status=true
  - When brand not found → create new Brand with name, slug, status=true
  - Newly created items cached in `$categories`/`$brands` arrays for dedup within same CSV
  - Success message includes count: "Imported 25 product(s) successfully. (3 new category(ies) created, 2 new brand(s) created)"
- `resources/views/admin/products/import.blade.php` — updated info box:
  - Added "**auto-created if missing**" note for category and brand columns

## 109. CSV Import — Gallery Images Support

**Problem:** CSV import only supported single primary image via `image` column — no way to import gallery images.

**Solution:** Added `gallery_images` column support with pipe-separated URLs.

**Files changed:**
- `app/Http/Controllers/ProductController.php` — `import()` method:
  - Added `gallery_images` to expected columns
  - Added `$galleryImported` counter
  - When `gallery_images` present → split by `|` → download each URL → save to `products/gallery/` → create Image record via `Image::storeFromUpload()`
  - Invalid URLs silently skipped
  - Success message includes count: "(5 gallery image(s) imported)"
- `downloadSampleCsv()` and `exportCsv()` methods:
  - Added `gallery_images` column (pipe-separated URLs)
  - Export includes full image URLs for gallery images
- `resources/views/admin/products/import.blade.php` — updated info box:
  - Added `gallery_images` column description with example format

## 110. CSV Import — Update Existing Products by ID

**Problem:** CSV import always created new products — providing an ID would error or create duplicate.

**Solution:** If `id` column provided and product exists → update that product (preserves existing slug). Otherwise → create new product.

**Files changed:**
- `app/Http/Controllers/ProductController.php` — `import()` method:
  - Added `id` to expected columns
  - If `id` is numeric and product exists → call `$existingProduct->update($insertData)` instead of `Product::create()`
  - Preserves existing slug on update
- `resources/views/admin/products/import.blade.php` — added `id` to supported columns list
- `downloadSampleCsv()` — added `id` column (empty in sample rows)

## 111. ReviewController — Full CRUD + Image Upload

**Problem:** Reviews were stored as JSON in `products.reviews_data` column with no server-side CRUD methods — only basic append logic existed.

**Solution:** Built complete ReviewController with store/update/destroy methods, image upload, and duplicate prevention.

**Files changed:**
- `app/Http/Controllers/ReviewController.php`:
  - `store()` — validates rating/review/image (max 5, 2MB each), checks for duplicate (one review per user per product), saves images to `public/assets/images/reviews/`, appends review to `products.reviews_data` JSON
  - `update()` — validates input, supports appending new images to existing reviews, updates rating/review/images in JSON array
  - `destroy()` — removes review from JSON array by review_id
- `routes/web.php` — added review routes:
  - `POST /product/{slug}/review` → `ReviewController@store`
  - `PUT /product/{slug}/review/{reviewId}` → `ReviewController@update`
  - `DELETE /product/{slug}/review/{reviewId}` → `ReviewController@destroy`

## 112. Dashboard Reviews — Pagination + Server-Side Search/Status Filters

**Problem:** Reviews page loaded all reviews with no pagination or filtering.

**Solution:** Added `LengthAwarePaginator` with server-side search and status filter, sharing `$activeCategoryUrls` for mega menu.

**Files changed:**
- `app/Http/Controllers/DashboardController.php` — `reviews()` method:
  - Uses `LengthAwarePaginator` for server-side pagination
  - Server-side search filters reviews by product name or review text
  - Status filter (all/pending/reviewed)
  - Passes `$activeCategoryUrls` to view for mega menu active state

## 113. Reviews Modal — Contact Seller Style + SVG Star Picker + Image Upload + Lightbox

**Problem:** "My Reviews" page had no modal for viewing/editing reviews, no image upload, and no way to manage review images.

**Solution:** Complete rewrite with Contact Seller-style modal, SVG star picker (matching product-rating design), image upload with preview, and lightbox gallery.

**Files changed:**
- `resources/views/user/reviews.blade.php` — complete rewrite:
  - 4-column table: Product | Review | Status | Action
  - Filter inputs in `<th>` for search and status
  - Status column with badges (pending/reviewed)
  - Bootstrap modal (Contact Seller style): `.modal-header`/`.modal-body`/`.modal-footer`, `modal-dialog-centered modal-dialog-zoom`, `gry-bg px-3 pt-3` body, `rounded-0 fw-600` buttons
  - SVG star picker using 17×16 viewBox, `#EEAE0B` filled / `#E2E8F0` empty, JS fill-based hover/click (replacing FontAwesome `far fa-star`)
  - Image upload with preview (max 5 files, 2MB each), `FileReader` for instant preview
  - Lightbox gallery: dark bg, prev/next buttons, counter (e.g. "2/5"), keyboard nav (Escape/Left/Right), looping

## 114. Product Page — Review Form SVG Star Picker + Image Upload + AJAX Submission

**Problem:** Product page review form used FontAwesome stars (inconsistent with product-rating SVG design) and had no image upload capability.

**Solution:** Replaced with SVG star picker matching product-rating design, added image upload with preview, AJAX submission via FormData.

**Files changed:**
- `resources/views/product.blade.php`:
  - Review form SVG star picker (matching product-rating design: `#EEAE0B`/`#E2E8F0`, 17×16 viewBox)
  - Image upload with preview (max 5 files, 2MB each)
  - Rewrote fetch handler to use `FormData` for image upload
  - HTTP status handling: 401 → login prompt, 403 → purchase required, 200 → success + form reset
  - Review images display with lightbox gallery (matching reviews.blade.php lightbox)

## 115. Nav Menu — Active State with Full URL Comparison + Base Path Stripping

**Problem:** Active menu state was broken — `active` class was on `<a>` but CSS targets `li.active>a`, and subpages like `/product/*` didn't highlight "Products".

**Solution:** Fixed active state using full URL comparison, base path stripping, route aliases, and `$activeCategoryUrls` matching for mega menu categories/subcategories.

**Files changed:**
- `resources/views/partials/nav-menu.blade.php`:
  - Added `active` class to `<li>` (not just `<a>`)
  - URL matching: exact match via `url()->current()` vs `url($menuUrl)`
  - Base path stripping: `rtrim(parse_url(url('/'), PHP_URL_PATH))` to handle subdirectory installs
  - Route aliases: `/product` → `/shop` for Products menu item
  - `$activeCategoryUrls` matching for mega menu child/subchild links

## 116. Product Category Active State — Mega Menu Highlighting

**Problem:** When viewing a product page, the mega menu didn't highlight the parent category or child/subchild links matching the product's category.

**Solution:** Product page loads `category.parent` and passes `$activeCategoryUrls` via `view()->share()`, enabling nav menu to highlight matching links.

**Files changed:**
- `app/Http/Controllers/ProductController.php` — `show()` method:
  - Loads `category.parent` relationship
  - Shares `$activeCategoryUrls` via `view()->share()` (array of category URLs from current product's category chain)
- `resources/views/partials/nav-menu.blade.php` — mega menu parent/child/subchild links check if their URL is in `$activeCategoryUrls` to apply `active` class

## 117. Home Page — Active State Fix (Root URL Exclusion)

**Problem:** Home menu item stayed highlighted on all pages because root URL `/` was matching every URL via prefix comparison.

**Solution:** Excluded root URL `/` from prefix matching to prevent Home staying active on non-home pages.

**Files changed:**
- `resources/views/partials/nav-menu.blade.php` — added guard: if menu URL is `/` (root), skip prefix matching (only exact match counts)

## 118. Shop Page — Clear Filters Button

**Problem:** No way to clear vehicle filters (year/make/model) once applied.

**Solution:** Added "Clear Filters" button that appears when any vehicle filter is active, resetting all filters at once.

**Files changed:**
- `resources/views/shop.blade.php` — added "Clear Filters" button + alert bar:
  - Button visible only when year/make/model filters are active in URL
  - Resets all vehicle filter params while preserving other URL params (search, category, etc.)
  - Styled with `btn btn-sm btn-outline-danger`

## 119. Blog Page — Clear Filters Button + Category Variable Fix

**Problem:** No way to clear blog search/category filters once applied. Also `$category` variable wasn't passed in category filter route.

**Solution:** Added "Clear Filters" button for search/category, fixed `$category` variable in BlogController.

**Files changed:**
- `resources/views/blog/index.blade.php` — added "Clear Filters" button:
  - Visible when search or category filter is active
  - Resets both search and category params while preserving pagination
- `app/Http/Controllers/BlogController.php` — `category()` method:
  - Added `$category` to `compact()` — was missing, causing undefined variable error in view

## 120. Admin Products — Per-Page "All" Option

**Problem:** Admin products list only had numeric per-page options (10/25/50/100) — no way to view all products at once.

**Solution:** Added "All" option that sets per_page parameter to display all products.

**Files changed:**
- `resources/views/admin/products/index.blade.php` — added "All" option to per-page selector
- `app/Http/Controllers/ProductController.php` — `index()` method:
  - Handles `per_page=all` parameter: when set, uses `get()` instead of `paginate()`
  - Otherwise uses numeric per-page value with `paginate()`

## 121. Pagination — First/Last Buttons

**Problem:** Custom pagination (`gs-pagination.blade.php`) had no First/Last page navigation buttons.

**Solution:** Added First and Last page buttons alongside existing Previous/Next buttons.

**Files changed:**
- `resources/views/vendor/pagination/gs-pagination.blade.php`:
  - Added "First" button (<< icon) when not on first page
  - Added "Last" button (>> icon) when not on last page
  - Used `$paginator->firstItem()` and `$paginator->lastItem()` for correct page URLs
  - Styled consistently with existing pagination buttons

## 122. Write a Review — SVG Star Picker (Replacing FontAwesome)

**Problem:** "Write a Review" section used FontAwesome `far fa-star` icons which didn't match the product-rating SVG star design.

**Solution:** Replaced with SVG stars matching product-rating design (`#EEAE0B` filled / `#E2E8F0` empty), JS updated for fill-based toggling.

**Files changed:**
- `resources/views/product.blade.php`:
  - Replaced FontAwesome `far fa-star` with inline SVG stars (17×16 viewBox, same as product-rating)
  - JS star picker updated: fill-based hover/click (toggles `fill` attribute between `#EEAE0B` and `#E2E8F0`)
  - Removed FontAwesome star dependency for review form

## 123. Mobile Dashboard Sidebar — Toggle Button

**Problem:** Dashboard sidebar was hidden on mobile with no way to access it.

**Solution:** Added floating circular toggle button at bottom-right that opens sidebar via `.active` class.

**Files changed:**
- `resources/views/user/layouts/dashboard.blade.php`:
  - Added floating button: `position: fixed; bottom: 24px; right: 24px; z-index: 1050;`
  - `d-lg-none` (hidden on desktop), circular design with hamburger/sidebar SVG icon
  - JS toggle: adds/removes `.active` class on sidebar + button
- `resources/views/user/layouts/sidebar.blade.php`:
  - Original toggle button hidden (replaced by floating button)
- `public/assets/front/css/style.css`:
  - `.user-dashboard-sidebar.active { height: 100%; }` — sidebar expands to full height when toggled

## 124. Slider Migration — Slick to Swiper.js (Categories, Products, Gallery)

**Problem:** Categories, Featured Products, Best-Selling Products, and Product Gallery sliders used Slick.js which had FOUC issues and limited customization.

**Solution:** Migrated all product/category sliders to Swiper.js with smooth cursor-based scrolling (freeMode, grabCursor, swipeToSlide). Hero banner stays on Slick.

**Files changed:**
- `resources/views/layouts/app.blade.php`:
  - Added Swiper CDN (CSS + JS) alongside existing Slick CDN
  - Added FOUC prevention CSS for `.swiper-initialized` containers
  - Slick still loaded for hero banner slider
- `resources/views/home.blade.php` — Categories/Featured/Best-Selling sections:
  - Replaced `slick()` initialization with `new Swiper()` with options: `freeMode: true`, `grabCursor: true`, `swipeToSlide: true`, `touchThreshold: 5`, `slidesPerView: auto`, `spaceBetween: 24`
  - Navigation buttons via Swiper `navigation` module
- `public/assets/front/js/script.js`:
  - Added Swiper initialization for Categories, Featured, Best-Selling sliders
  - Removed old Slick initialization code for these sliders
  - Hero banner Slick initialization preserved
- `public/assets/front/css/custom.css`:
  - Added equivalent rules for `.swiper-slide` matching old `.slick-slide` layout (padding, margins, flex, sizing)
  - Added `.swiper-container` cursor CSS
- `resources/views/product.blade.php` — Product Gallery:
  - Converted thumbnail nav and main gallery from Slick to Swiper
  - `allowTouchMove: false` on nav slider (prevents accidental swiping)
  - `centeredSlides: true`, `centeredSlidesBounds: true`
  - Manual centering via `setTranslate` with 300ms CSS transition on slide change
  - Active thumbnail auto-centers when navigating via main gallery arrows

## 125. Product Gallery — No Touch + Centered Thumbnails + Manual Centering

**Problem:** Product gallery nav slider allowed accidental swiping, and thumbnails didn't center properly on slide change.

**Solution:** Disabled touch on nav slider, added centeredSlides with manual centering via setTranslate for smooth thumbnail navigation.

**Files changed:**
- `resources/views/product.blade.php`:
  - Nav slider: `allowTouchMove: false` (prevents touch swipe on thumbnails)
  - Nav slider: `centeredSlides: true`, `centeredSlidesBounds: true` (ensures active thumbnail is always centered)
  - Added JS event handler: on `activeIndexChange`, calculates center offset and applies `setTranslate` with 300ms CSS transition for smooth centering
  - Removed `freeMode` and `grabCursor` from nav slider (cleaner interaction)

## 126. FOUC Prevention — Updated for Swiper Containers

**Problem:** FOUC prevention CSS targeted `.slick-initialized` but Swiper containers used different class names.

**Solution:** Updated FOUC prevention CSS to target `.swiper-initialized` for Swiper containers while keeping `.slick-initialized` for hero banner.

**Files changed:**
- `resources/views/layouts/app.blade.php`:
  - Added `.swiper-initialized` to visibility/opacity FOUC prevention rules
  - Kept `.slick-initialized` for hero banner (still uses Slick)
  - Both `.details_slider_thumb:not(.slick-initialized)` and `.details_slider_nav:not(.slick-initialized)` preserved for product gallery nav (Slick fallback)

## 127. Home Sliders — Consistent Slide Width (Image Adapts, Not Vice Versa)

**Problem:** "Best Selling" and "Featured Products" slider items had inconsistent widths because Bootstrap grid classes (`col-md-6 col-lg-4 col-xl-3`) on `swiper-slide` elements interfered with Swiper's `slidesPerView` width calculation. Images with different aspect ratios caused slides to expand/shrink unpredictably.

**Root causes:**
1. `col-md-6` sets `max-width: 50%`, `col-lg-4` sets `max-width: 33.33%`, `col-xl-3` sets `max-width: 25%` — these `max-width` constraints conflicted with Swiper's dynamic width calculation
2. No `flex-shrink: 0` on slides — slides could shrink below their calculated width
3. Category slider had `width: auto !important` on `swiper-slide` — overrode Swiper's width calculation entirely

**Solution:** Slider items now have fixed width controlled by Swiper's `slidesPerView`; images adapt via `object-fit: cover`.

**Files changed:**
- `public/assets/front/css/custom.css`:
  - **Category slider** (`home-cate-slider`): removed `width: auto !important` from `swiper-slide`, added `flex-shrink: 0`, added `object-fit: cover` + `width: 100%; height: 100%` to `.category-image img`
  - **Product cards slider** (`product-cards-slider`): added `flex-shrink: 0` + `flex: 0 0 auto !important` + `max-width: none !important` + `width: auto !important` to override Bootstrap `col-*` classes, added `object-fit: cover` + `height: 260px` to `.product-img`
  - **Best selling section** (`.gs-explore-product-section`): same Bootstrap grid overrides on `swiper-slide` elements
- `resources/views/home.blade.php`:
  - Removed `col-md-6 col-lg-4 col-xl-3` classes from `swiper-slide` in Featured Products and Best Selling sections (no longer needed)
- `public/assets/front/js/script.js`:
  - Added `swipeToSlide: true` and `touchThreshold: 5` to both Featured Products and Best Selling Swiper instances for smoother cursor-based scrolling

## 128. Swiper.js — CDN to Local Files

**Problem:** Swiper loaded from CDN (`cdn.jsdelivr.net`) — external dependency, slower load, no offline support.

**Solution:** Downloaded Swiper v11 bundle locally and replaced CDN links with local asset paths.

**Files created:**
- `public/assets/front/css/swiper-bundle.min.css` — Swiper v11 CSS (18KB)
- `public/assets/front/js/swiper-bundle.min.js` — Swiper v11 JS bundle (154KB)

**Files changed:**
- `resources/views/layouts/app.blade.php`:
  - CSS: `https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css` → `{{ asset('assets/front/css/swiper-bundle.min.css') }}`
  - JS: `https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js` → `{{ asset('assets/front/js/swiper-bundle.min.js') }}`

## 129. OpenCode Skills — TASKS.md to Skills Sync

**Problem:** No reusable skill documentation existed for project patterns — each new session had to re-learn patterns from scratch.

**Solution:** Created 8 OpenCode skills from TASKS.md, each capturing a reusable pattern with code examples, file references, and common pitfalls. Added auto-sync script to detect new tasks.

**Skills created:**

| # | Skill | Trigger Keywords |
|---|-------|-----------------|
| 1 | `laravel-review-system` | review CRUD, image upload, modal, lightbox, SVG star picker, My Reviews |
| 2 | `swiper-slider-migration` | Slick to Swiper, slider width, FOUC, product card slider, category slider |
| 3 | `laravel-pagination` | custom pagination, first/last buttons, LengthAwarePaginator, per-page All |
| 4 | `nav-menu-active-state` | active menu, mega menu, base path stripping, route aliases |
| 5 | `laravel-dashboard` | dashboard redesign, mobile sidebar toggle, stat cards, circular buttons |
| 6 | `laravel-admin-utils` | CSV import/export, per-page All, auto-create categories, image manager picker |
| 7 | `shop-blog-filters` | clear filters, cascading vehicle filters, mobile auto grid |
| 8 | `laravel-page-builder` | block editor, SortableJS, AJAX save, Elementor styling, frontend rendering |

**Files created:**
- `.opencode/skills/laravel-review-system/SKILL.md`
- `.opencode/skills/swiper-slider-migration/SKILL.md`
- `.opencode/skills/laravel-pagination/SKILL.md`
- `.opencode/skills/nav-menu-active-state/SKILL.md`
- `.opencode/skills/laravel-dashboard/SKILL.md`
- `.opencode/skills/laravel-admin-utils/SKILL.md`
- `.opencode/skills/shop-blog-filters/SKILL.md`
- `.opencode/skills/laravel-page-builder/SKILL.md`
- `.opencode/skills/sync-skills.sh` — detects new TASKS.md entries since last sync
- `.opencode/skills/.last_sync` — tracks last synced line count

**Sync usage:**
```bash
# Check for new tasks since last sync
bash .opencode/skills/sync-skills.sh

# After updating skills, mark as synced
echo 1825 > .opencode/skills/.last_sync
```

## 130. Featured Products Swiper Breakpoints Fix

**Problem:** Featured Products slider had no `0` breakpoint fallback — below576px viewport, `slidesPerView` fell back to the default of 4, showing too many slides on small screens. The default `slidesPerView: 4` was also risky.

**Solution:** Updated breakpoints with proper `0` fallback and safe default.

**File changed:**
- `public/assets/front/js/script.js:563-580`:
  - Default `slidesPerView: 4` → `1`
  - Added `0: { slidesPerView: 1 }` breakpoint
  - Result: `1200→4, 834→3, 460→2, 0→1` (all viewports covered)

## 131. Home Page Hero Section Animation Fix

**Problem:** Hero content showed first (visible), then `fadeInUp` animation played causing content to re-appear from bottom to up. Root cause: hero was a static section but Slick slider was still being initialized on `.hero-slider-wrapper`. Slick added `.slick-current.slick-active` classes, which triggered CSS `fadeInUp` animation. The FOUC prevention (`visibility: hidden` until `.slick-initialized`) hid content until Slick init, then the animation kicked in.

**Solution:** Commented out Slick init (hero is no longer a slider), fixed animation CSS selectors to target hero content directly.

**Files changed:**

- `public/assets/front/js/script.js:178-193`:
  - Commented out Slick initialization on `.hero-slider-wrapper` (was initializing a single-slide "slider")
  
- `public/assets/front/css/style.css:10968-10995`:
  - Commented out old Slick-specific `.slick-dots` responsive rules
  - Changed animation selectors from `.hero-slider-wrapper .slick-current.slick-active .subtitle` → `.hero-slider-wrapper .subtitle` (removed `.slick-current.slick-active` dependency)
  - Same for `.title`, `.des`, `.hero-shop-now-btn` — all delays preserved (0.1s, 0.2s, 0.3s)

- `resources/views/layouts/app.blade.php:168`:
  - Removed `.hero-slider-wrapper:not(.slick-initialized)` from FOUC prevention CSS (hero is static, no longer needs to wait for Slick init)

## 132. Monthly Revenue Chart — Fill Missing Months with Zero

**Problem:** Monthly Revenue bar chart on admin dashboard only showed months that had orders. If only one month had orders, only that bar appeared. Months with zero revenue were completely missing.

**Root cause:** `Order::selectRaw("...SUM(total_amount)...")->groupBy('month')` only returns rows for months that have orders. No orders = no row = no bar on chart.

**Solution:** Query DB for existing months, then generate all last 12 months in PHP and fill missing months with 0 revenue.

**Files changed:**

- `app/Http/Controllers/AdminController.php:34-55`:
  - Changed `$monthlyRevenue = ...->get()` to `->pluck('total', 'month')`
  - Added `for ($i = 11; $i >= 0; $i--)` loop generating last 12 months via `now()->subMonths($i)->format('Y-m')`
  - Missing months get `0` revenue, existing months get actual `total_amount` sum
  - Passed `$monthlyRevenueData` as both `monthlyRevenue` and `monthlyRevenueJson`

- `app/Http/Controllers/DashboardController.php:25-43` (admin dashboard method):
  - Same fix as AdminController

- `app/Http/Controllers/DashboardController.php:157-178` (second admin method):
  - Same fix as AdminController

## 133. Global Button System — 10 Style Groups + Modifiers + Bridge Classes

**Problem:** 19+ unique button designs across the project with no consistency. New buttons had to duplicate styles manually.

**Solution:** Created a standalone 1299-line global button system (`buttons.css`) with 10 style groups, 14 color variants, 5 sizes, 3 shapes, and bridge classes that preserve all existing old CSS.

**Files changed/created:**
- `resources/css/buttons.css` — **new** (1299 lines): 10 style groups + bridge classes
- `resources/css/app.css` — added `@import 'buttons.css';`
- `public/css/app.css` — appended buttons.css content (now 2689 lines)
- `storage/file-backups/css-backup-20260728/` — backup of original CSS files

**10 Style Groups:**
1. `.btn-solid` — CTA solid (matches old `.template-btn`)
2. `.btn-outline-style` — outlined CTA (matches `.template-btn.outline-btn`)
3. `.btn-icon-style` — 36×36 icon square (matches `.product-action-btn`/`.view-btn`/`.back-btn`)
4. `.btn-submit-style` — full-width submit (matches `.btn-forms`/`.btn-auth`/`.add-to-cart-btn`)
5. `.btn-header-action-style` — header action
6. `.btn-circle-style` — circular icon
7. `.btn-qty-style` — quantity +/- buttons
8. `.btn-hero-outline-style` — hero outline
9. `.btn-slider-nav-style` — slider nav arrows
10. `.btn-search-style` — search button

**Color modifiers:** `btn-primary-style`, `btn-secondary-style`, `btn-danger-style`, `btn-success-style`, `btn-warning-style`, `btn-info-style`, `btn-dark-style`, `btn-light-style`, `btn-white-style`, `btn-link-style`, `btn-ghost-style`, `btn-red-style`, `btn-green-style`, `btn-blue-style`

**Size modifiers:** `btn-xs`, `btn-sm`, `btn-md`, `btn-lg`, `btn-xl`

**Shape modifiers:** `btn-pill` (9999px), `btn-square` (0px)

**Bridges preserved:** `template-btn`, `steve-btn`, `product-action-btn`, `view-btn`, `back-btn`, `btn-forms`, `btn-auth`, `add-to-cart-btn`, `add-cart`, `add-to-wishlist-btn`, `wishlist-btn`, `buy-now`, `action-btn`, `template-btn.outline-btn`, `template-btn.lg-btn`, `template-btn.md-btn`, `template-btn.sm-btn`, `template-btn.dark-outline`, `template-btn.dark-btn`, `template-btn.black-btn`, `template-btn.outlinee-btn`, `nav-link.steve-btn`

**File Revisions:**
| File | Event | Timestamp |
|---|---|---|
| `resources/css/buttons.css` | Updated | 2026-07-28 13:41:19 |
| `public/assets/front/css/style.css` | Updated | 2026-07-28 13:52:23 |

**Note:** `public/css/app.css` and `resources/css/app.css` have no File Revision record — watcher was not running when these were modified.

## 134. Category Sidebar Style Fix — Shop Page

**Problem:** Category sidebar had inconsistent colors (`#000` on top level vs other levels) and mismatched font-size inline styles (15px/14px).

**Files changed:**
- `resources/views/shop.blade.php` — unified colors to `#1f0300`, removed inconsistent font-size inline styles, added `a-tag-text-hover` class to sub/child levels

**File Revisions:**
| File | Event | Timestamp |
|---|---|---|
| `resources/views/shop.blade.php` | Updated | 2026-07-28 12:18:45 |

## 135. Tooltip Stuck-on-Right-Click Fix

**Problem:** Right-click → Esc → tooltip stays stuck because Bootstrap's tooltip handler doesn't listen for `contextmenu` event.

**Files changed:**
- `public/assets/front/js/script.js:868-886` — added `contextmenu` handler to hide tooltip on right-click before browser menu opens; added global `Escape` key handler to remove `.show` from all stuck `.tooltip` elements

**File Revisions:**
| File | Event | Timestamp |
|---|---|---|
| `public/assets/front/js/script.js` | Updated | 2026-07-28 13:25:42 |

## 136. Grid/List Button Style Sync — Categories Page

**Problem:** Categories page `.view-btn` had no inline styles, causing visual mismatch with shop page's grid/list buttons.

**Files changed:**
- `resources/views/categories.blade.php` — added inline CSS for `.category-toolbar .view-btn` matching shop page's `.product-nav-wrapper .btn-wrapper .view-btn` (42×42px, border primary, white bg, rounded 8px, active = primary bg)

**File Revisions:**
| File | Event | Timestamp |
|---|---|---|
| `resources/views/categories.blade.php` | Updated | 2026-07-28 13:30:20 |

## 137. Typo Fix — "Secrch" → "Search"

**Files changed:**
- `resources/views/layouts/app.blade.php:299` — `"Secrch"` → `"Search"`

**File Revisions:**
| File | Event | Timestamp |
|---|---|---|
| `resources/views/layouts/app.blade.php` | Updated | 2026-07-28 13:26:01 |

## 138. Product Page Buttons — Match Shop Now Button Style

**Problem:** Product page Buy Now / Add to Cart buttons used `product-action-btn` class (36×36 icon button) + inline `border-radius:4px`, while Shop Now (`template-btn`) has `padding: 16px 40px, border-radius: 8px`. Tabs (Description/Buy & Return Policy/Reviews) had `border: 1px solid #030712` and smaller padding.

**Changes:**
- Buy Now: `btn steve-btn text-white px-4 product-action-btn buy-now` + inline styles → `template-btn steve-btn w-100 buy-now`
- Add to Cart: same → `template-btn steve-btn w-100`
- Submit Review: `btn btn-primary steve-btn` → `template-btn steve-btn`
- Tabs: added `template-btn` class; `.tab-product-des-wrapper .nav-link` CSS updated to match `template-btn` (padding 16px 40px, bg var(--primary), border none, color #fff, hover=var(--hov-primary))
- Tab responsive styles: removed fixed height constraints

**Files changed:**
- `resources/views/product.blade.php` — 5 button/tab modifications
- `public/assets/front/css/style.css:13131-13177` — tab CSS rewritten to match template-btn
- `public/css/app.css:2581` — bridge `border-radius` 4→8px
- `resources/css/buttons.css:1191` — source `border-radius` 4→8px

**File Revisions:**
| File | Event | Timestamp |
|---|---|---|
| `public/assets/front/css/style.css` | Updated | 2026-07-29 05:12:19 |
| `resources/views/product.blade.php` | Updated | 2026-07-28 13:51:20 |
| `resources/css/buttons.css` | Updated | 2026-07-28 13:41:19 |

**Note:** `public/css/app.css` has no File Revision record — watcher was not running when manually appended. To capture, restart file watcher and re-trigger edit.

---

## 139. UI/UX QA Report — Full Fix Pass (source: `UIUX_QA_REPORT.html`)

All Section 2–5 defects from `UIUX_QA_REPORT.html` were applied (04 Aug 2026) without introducing design/function regressions. Verified via `php artisan view:cache` + rendered-page checks (home, shop, product, cart, about-us, login, admin). Remaining items are Low-priority cleanups only: #3 dead badge CSS, #14 wishlist pill styling, #17 select chevron touch, #24 dead `.gs-cart-section` CSS.

### Accessibility
- **#16 Focus indicators** — `public/assets/front/css/style.css` — `:focus-visible` now restores a visible outline for `.form-select`/`.form-control` (`border-color: var(--primary)`, `outline: 2px solid var(--primary)`, `outline-offset: 1px`, `box-shadow: 0 0 0 3px rgba(230,46,4,.15)`) — the old `border-color:unset; box-shadow:unset` rule had removed all focus indicators.
- **#5 Anchor scroll offset** — `style.css` — added `html { scroll-padding-top: 120px }` so sticky header no longer covers in-page anchors.

### Tap targets / touch sizing
- **#6 Auth buttons** — `public/assets/front/css/custom.css` — `.mobile-auth-icon-btn` padding `6px 14px` → `10px 16px`.
- **#7 Category toggles** — `custom.css` — `.cat-toggle-btn` now `min-width:36px; min-height:36px; padding:8px`, inline-flex centered (was `p-0` ~9-11px tap).
- **#9 Search controls** — `style.css` — input, category dropdown and search button raised 38px → 44px at ≤767.98px and ≤399.97px.
- **#11 Newsletter input** — `style.css` — `.news-latter-input` height `auto` → `48px`.
- **#12 Footer social links** — `style.css` — held at 40×40px ≤991.97px (was 32×32px).
- **#13 Product Compare/Details** — `style.css` — `.single-product .compare/... .details` padding `4px !important` → `10px 14px !important`.
- **#15 / #20 `.view-btn`** — `style.css` — grid/list toggle buttons raised 30px/34px → 42px at ≤991.97px.
- **#21 Review delete button** — `product.blade.php:845` — padding `2px 8px; font-size:12px` → `6px 12px; font-size:13px`.
- **#22 Buy Now / Add to Cart** — `product.blade.php` — `.details_btn_area` stacks to `1fr` at ≤400px.
- **#23 Cart qty controls** — `cart.blade.php`, `layouts/app.blade.php`, `custom.css` — `.aiz-plus-minus` buttons 28/30px → 36px, input 32px → 44px (both inline styles and CSS).

### Layout / overflow
- **#8 Mobile menu height** — `style.css` — `.mobile-menu { height:100vh }` → `100dvh` (dynamic-toolbar safe).
- **#19 Shop sort/brand selects** — `style.css:4543` — `@media (max-width:399px){ .filter-sort-brand-wrapper select{width:125px} }` (was 180px each in 2-col grid → overflowed 360px phones).
- **#25 Compare sticky first column** — `style.css` — re-enabled `.compare-table` sticky first column (`position:sticky; left:0`, solid `#f8f9fa` bg, `min-width:180px`, z-index layering th 3 / first th 4) so product names stay visible while the 1200px table scrolls.
- **#26 About page** — `pages/about.blade.php` — added `@media (max-width:768px)` block (hero padding 70px 0, h1 38px, p 16px, values-grid 2-col gap 20px, value-card padding) for the 577–991px tablet gap.
- **#18 Home hero** — already rebuilt as a static section with centered content ≤767px (`style.css:11580-11610`).

### Wrap fixes (flex-wrap / table-responsive)
- **#28 / #29 User dashboard headers** — `user/{vehicles,wishlist,followed-sellers,notifications,dashboard}.blade.php` — added `flex-wrap` (and `gap-2` where needed) to page headers and the vehicles card body → no overflow at 320px.
- **#31 Admin index toolbars** — `admin/blog-categories/index.blade.php` — toolbar now `flex-wrap flex-md-nowrap mb-3` (matches the other 12 index pages).
- **#32 Admin page headers** — `admin/products/index.blade.php` + `admin/home-page/index.blade.php` — added `flex-wrap`.
- **#35 Admin tables** — `admin/products/show.blade.php` (Product Summary, Vehicle Details, Metadata) + `admin/images/edit.blade.php` — wrapped in `.table-responsive`.
- **#36 Revisions diff tables** — `admin/revisions/detail.blade.php` — all 3 `diff-table` variants wrapped in `.table-responsive`.

### Admin panel stacking & buttons
- **#33 Sidebar overlay z-index** — `admin/layouts/app.blade.php` — 3-layer stack: sidebar `1001` / overlay `1000` / navbar `999` (was all 999 → navbar painted above dim layer).
- **#34 Admin action buttons** — `admin/layouts/app.blade.php` — `.admin-content .card-header .btn` raised to `font-size:14px; padding:6px 12px` and the generic rule to `12px/5px 10px` (was `12px/4px 10px` → `11px/3px 8px` on mobile).
- **#37 Orders & Brands index** — restored the missing opening `<div>` (renders literal `flex-wrap flex-md-nowrap"` text and closed the layout container prematurely) — fixed pre-session, verified.

### JS cleanup
- **#10 Search bar duplicate handler** — `public/assets/front/js/script.js` — removed the dead `$('#searchIcon')` → `toggleClass('active')` block (`.search-bar.active` had no CSS). Single `.show` open path + tooltip/autofocus behavior intact.

**Remaining (deferred, Low):** #3 dead 16×16 badge rule removal · #14 wishlist pill styling vs `.steve-btn` · #17 select chevron on touch · #24 dead `.gs-cart-section .cart-table` CSS.

---

## 140. File Revisions — QA Fix Pass (04 Aug 2026)

The file watcher (`file:audit` + `file-watcher.mjs`) recorded revisions for all 20 files changed in the UI/UX QA fix pass (#139). `TASKS.md` / `UIUX_QA_REPORT.html` sit outside the watched directories (`app/`, `config/`, `database/`, `resources/`, `routes/`, `public/assets/`) so they are not tracked.

**File revisions recorded (04 Aug 2026):**
| # | File | Revisions |
|---|---|---|
| 1 | `public/assets/front/css/style.css` | 8 (4551–4557, 4590 note: 4551-4557 on 04 Aug) |
| 2 | `public/assets/front/css/custom.css` | 3 (4558–4559, 4563) |
| 3 | `public/assets/front/js/script.js` | 1 (4560) |
| 4 | `resources/views/product.blade.php` | 2 (4561–4562) |
| 5 | `resources/views/layouts/app.blade.php` | 1 (4564) |
| 6 | `resources/views/cart.blade.php` | 1 (4565) |
| 7 | `resources/views/pages/about.blade.php` | 1 (4566) |
| 8 | `resources/views/user/vehicles.blade.php` | 2 (4567–4568) |
| 9 | `resources/views/user/wishlist.blade.php` | 1 (4569) |
| 10 | `resources/views/user/followed-sellers.blade.php` | 1 (4570) |
| 11 | `resources/views/user/notifications.blade.php` | 1 (4571) |
| 12 | `resources/views/user/dashboard.blade.php` | 2 (4572–4573) |
| 13 | `resources/views/admin/blog-categories/index.blade.php` | 1 (4574) |
| 14 | `resources/views/admin/products/index.blade.php` | 1 (4575) |
| 15 | `resources/views/admin/home-page/index.blade.php` | 1 (4576) |
| 16 | `resources/views/admin/layouts/app.blade.php` | 4 (4577–4580) |
| 17 | `resources/views/admin/products/show.blade.php` | 5 (4581–4586) |
| 18 | `resources/views/admin/images/edit.blade.php` | 1 (4587) |
| 19 | `resources/views/admin/revisions/detail.blade.php` | 1 (4588) |
| 20 | `resources/views/shop.blade.php` | 2 (4589–4590) |

All events are `updated`; each has a backup archive and unified diff available from the admin **File Revisions** page (`/admin/file-revisions`).

---

## 141. Home Page — Dynamic Section Config, Countdown & Banners (04 Aug 2026)

Revisions **4591–4652** (04 Aug 06:16–09:34). Home sections made fully configurable from the admin; hero guarded by status; about/contact slugs renamed.

**Problem:** Home hero rendered even when disabled; "Special Offer" title was hardcoded; offers banner slots and countdown could not be managed; latest-posts count fixed; `/about` / `/contact` slugs did not match the footer links.

**Solution:**
- `HomeController.php` — latest posts limit read from `latest_post` section extra_data `posts_count` (clamped 1–12); `top_brands` limit/`brand_ids` selection preserved from extra_data.
- `home.blade.php` — section title from `$sections->get('offers')?->title ?? 'Special Offer'`; fixed `button-text` → `button_text` typo; hero wrapped in `@if($heroSection)` (only renders when status enabled).
- `Admin/HomePageController.php` — `update()` merges status checkbox, `posts_count`, countdown, and Offer Banners repeater (3 slots, image manager) into section extra_data; `image_from_manager` now copies from both `storage/app/public/...` and `public_path(...)`; new `toggleStatus()` method.
- `admin/home-page/edit.blade.php` — info alert context for offers / deal_of_day / latest_post sections; Offer Banners repeater with image picker.
- `admin/home-page/index.blade.php` — status toggle button per section.
- `routes/web.php` — `admin.home-page.toggle-status`; `/about` → `/about-us`, `/contact` → `/contact-us` (Home + Contact controllers).
- New hero/banner images added under `public/assets/images/home/`.

## 142. Image Manager — Upload From URL (04 Aug 2026)

Revisions **4599–4612** (04 Aug 06:29–06:35).

**Problem:** Image manager picker only supported file upload; no way to import a remote image by URL.

**Solution:** `ImageController::pickerStoreFromUrl()` + `admin.images.picker-store-url` route; `admin/images/image-manager-picker.blade.php` and `admin/images/index.blade.php` got a URL-input mode alongside the upload mode.

## 143. Static Pages — Terms Redesign & Route-Attribute Lookup Experiments (04 Aug 2026)

Revisions **4628–4649** (04 Aug 07:41–09:10).

**Problem:** Terms page was a placeholder; an attempt to auto-lookup page attributes by route name caused issues.

**Solution:**
- `pages/terms.blade.php` — full redesign (custom layout/CSS), replacing the "Coming Soon" placeholder.
- `routes/web.php` — terms/return/privacy routes wired to the redesigned view.
- Experiment (later reverted): `Page.php` `pageID` fillable/casts + `SetPageAttributes.php` route-name page lookup (rev 4631–4635) removed at rev 4637–4639 — the middleware keeps only the pure route-name-derived `pageId`/`pageClass` sharing (no DB lookup).

## 144. Checkout Flow — Layout Restructure (04 Aug 2026)

Revisions **4663–4716** (04 Aug 10:06–12:18).

**Problem:** Checkout steps header and step pages had no consistent section/container wrapper; layout and spacing inconsistent with the rest of the storefront.

**Solution:**
- `partials/checkout-steps.blade.php` — wrapped in `<section id="cart-summary"><div class="container">`, consistent step icons/labels with done/active/upcoming states.
- `checkout.blade.php` — restructured (address cards, postal code shown on the selected address).
- `cart.blade.php` — matching container restructure.
- `delivery-info.blade.php` — restructured; inhouse products list added to the delivery summary.
- `payment.blade.php` — restructured.
- `order-confirmed.blade.php` — restructured with a confirmation check icon header.
- `pages/show.blade.php` — whitespace-only touch (rev 4678).

## 145. CSS Variable Rename + Shop Vehicle Filter Chips (04 Aug 2026)

Revisions **4724–4756** (04 Aug 12:36–13:34).

**Problem:** Layout reused a green color only known as `--forest-green` while the site's green is `--green`; shop page had no visual indicator that a vehicle-filter chip was active.

**Solution:**
- `layouts/app.blade.php` + `style.css` — CSS variable renamed `--forest-green` → `--green` (border-bottom / header borders); `--green: #85b567` added to `style.css` root.
- `shop.blade.php` — active vehicle filter chip handling via `$hasVehicleFilter` (vehicle filter active when a category chip is selected), so chip states render correctly in both the sidebar and mobile views.

## 146. Payment Page — Section Wrapper + Terms Agreement Checkbox (04 Aug 2026)

Revisions **4740–4746** (04 Aug 13:06–13:13).

**Problem:** Payment page had no section wrapper and no required agreement to the store policies.

**Solution:** `payment.blade.php` — page wrapped in section/container layout; added required `agree_checkbox` with links to `/terms`, `/return-policy`, `/privacy-policy`.

## 147. User Vehicles — Section Wrapper + Edit Modal Restructure (04 Aug 2026)

Revisions **4757–4759, 4793–4796** (04 Aug 13:15–14:31).

**Problem:** Vehicles list card and edit modal were not wrapped in the dashboard section element, breaking the sidebar layout rhythm.

**Solution:** `user/vehicles.blade.php` — vehicle list wrapped in `<section>`, edit modal restructured (per-vehicle `#editVehicleModal{{ $vehicle->id }}` with cleaner form layout).

## 148. User Profile — Zip Code (postal_code) Support (04 Aug–05 Aug 2026)

Revisions **4760–4767, 4780–4783, 4797–4807** (04 Aug 13:33–05 Aug 05:10).

**Problem:** Users could not save their ZIP/postal code; the profile address summary ignored it and address layout crammed city/country on one column.

**Solution:**
- `user/profile.blade.php` — address display now `implode(array_filter([$profile->address, $profile->city, $profile->country, $profile->postal_code]))` (rev 4760).
- `user/profile-edit.blade.php` — new "Zip Code" field (`name="postal_code"`), city/country switched to `col-md-4` columns, Cancel button uses `steve-btn` styling.
- `UserController::updateProfile()` — `postal_code` added to validated/saved data.
- `style.css` (rev 4784–4790) — form `:focus-visible` outline/box-shadow set back to `unset` (reversal of the #139 focus ring) and `thead`/`tfoot` first-row `border-top` added.

## 149. Product Card — Compare / Quick View Tooltips (05 Aug 2026)

Revisions **4808–4809** (05 Aug 05:15).

**Problem:** The compare and quick-view (eye) icon buttons in the product card had no tooltips, so their purpose was unclear.

**Solution:** `partials/product-card.blade.php` — compare link gets `data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="top" title="Compare"` and the eye link gets `title="Quick View"` (auto-initialized by the existing Bootstrap tooltip init in `public/assets/front/js/script.js`).

## 150. Address Form — Country → State/City Cascade Fix (13 Aug 2026)

**Problem:** The country → state → city dropdowns did not cascade properly: state/city stayed disabled until a country was chosen, changing country left stale selections, and the checkout "edit address" modal never refreshed city because jQuery `.trigger('change')` only fires jQuery-bound handlers — not the native `addEventListener` handler bound in the partial.

**Files changed:**
- `resources/views/partials/address-fields.blade.php` — `refresh()` now enables State only when a country is chosen, and City only when both country AND state are chosen (mirrors the country → state cascade); country `change` handler resets `stateEl.value`/`cityEl.value` + `data-selected` before refreshing.
- `resources/views/checkout/index.blade.php` — `edit_address` replaced jQuery `.trigger('change')` with native `dispatchEvent(new Event('change', { bubbles: true }))`; sets state/city after country change and fires a native state change so the city select enables; appends missing state/city options.

**Verified:** headless-Chrome walkthrough of all add/edit flows (add modal, empty country, India+Maharashtra city enable, country switch reset, checkout edit prefill, dashboard server-rendered edit modal).

## 151. Cart Page — Placeholder Image Bug (13 Aug 2026)

**Problem:** Cart and mini-cart showed the placeholder image for products that had valid absolute image URLs stored.

**Files changed:**
- `app/helpers.php` — `storedPath()` now passes absolute URLs (`http(s)://`) through instead of returning the fallback, consistent with `storedImageUrl()`.
- `app/Helpers/image.php` — `imgTag()` skips the local `file_exists()` check for `http(s)://` srcs (previously the URL was swapped back to the placeholder).

**Verified:** cart renders the real storage URL (HTTP 200).

## 152. Full-Site Image Audit (13 Aug 2026)

**Details:** DB-vs-filesystem scan plus headless crawl of front and admin pages. All image spots clean (home, shop, brands, blog, categories, product + gallery, compare, admin); 668 gallery images and all DB references resolve to existing files. 10 subcategories on the `/categories` page show the placeholder because their `image` field is empty in the DB — admin data entry fix only (no code change).

## 153. Admin Pages — Edit/Create Redesign (13 Aug 2026)

**Files changed:**
- `resources/views/admin/pages/edit.blade.php` — rebuilt as sectioned cards: **Page Details** (title, Active + Show Page Title switches, short description), **Title Banner Image**, **SEO** (meta title/description with live character counters initialized server-side via `mb_strlen`), **Content** (Summernote), plus a **sticky action bar** (Update/Cancel/View Page in new tab/Last updated).
- `resources/views/admin/pages/create.blade.php` — same redesign for the create form.
- `resources/views/admin/pages/_banner_image.blade.php` — removed redundant inner `<strong>` label (now titled by the card header).

**Verified:** blades compile, page renders live, end-to-end PUT save round-trip (probe value saved + reverted).

## 154. Shop — "Vehicle" Nav Dropdown (My Vehicles) + Category Vehicle Filter (13 Aug 2026)

**Problem:** Users could not switch the applied vehicle filter directly from the product navigation; category/subcategory pages showed the "Vehicle:" chip (shared via middleware) but never actually filtered products; clearing the vehicle filter dumped the user to plain `/shop`, losing category/search/sort context.

**Files changed:**
- `resources/views/shop/index.blade.php` — added a **"Vehicle"** dropdown inside `product-nav-wrapper` (after Brand) listing the logged-in user's My Vehicles, wrapped in `@auth` + `$userVehicles->isNotEmpty()` so it is hidden via code when the user has no saved vehicles (or is a guest). Options: "All Vehicles" + each saved vehicle; defaults to the currently applied vehicle. Auto-submits POST on change — `user.vehicles.select/{id}` to set the session vehicle, `shop.clear-vehicle` to clear.
- `app/Http/Controllers/ShopController.php`:
  - New `applyVehicleFilter(Request $request, $query, bool $autoFirst)` helper (extracted from shop `index()` vehicle logic: session/first-vehicle selection, request merge, match-count with zero-match fallback). `index()` calls it with `$autoFirst = true` (existing behavior preserved); `category()` and `subcategory()` call it with `$autoFirst = false` — category pages now really filter by the selected vehicle but never auto-apply the first one.
  - `clearVehicleFilter()` now redirects to the previous URL (stripping stale `year`/`make`/`model`/`page`) instead of `route('shop')`.
- `app/Http/Controllers/DashboardController.php` — `selectVehicle()` strips stale `year`/`make`/`model`/`page` params from the previous URL before redirecting, so the nav dropdown always wins over stale sidebar filters.

**Verified (live, 3 test users):** dropdown present for users with vehicles and hidden for no-vehicle users and guests; select/clear round-trip on both shop and category pages with context preserved (`?sort=...` kept); sidebar year/make/model params stripped on select; existing sidebar "Vehicle" widget untouched.

## 155. Admin Pages — Editable Slug Field (13 Aug 2026)

**Problem:** The page slug was auto-generated from the title and could not be edited from the admin (update never saved a slug at all), so URLs were locked to the original title.

**Files changed:**
- `resources/views/admin/pages/edit.blade.php` — new **Slug** input in the "Page details" section with a live-preview link below it (`route('page.show', $page->slug)`, using the `a-tag-text-hover` style); the header "View page" link now updates live too (`#viewPageLink`). JS auto-slugifies the title while the slug field is untouched, and switches to manual mode as soon as the admin types.
- `resources/views/admin/pages/create.blade.php` — same Slug input (auto-generated from the title when left empty).
- `app/Http/Controllers/Admin/PageController.php` — `store()` and `update()` now accept `slug`, slugify it (`Str::slug`), fall back to the title when empty, and reject duplicates via `back()->withErrors(['slug' => ...])` (ignoring the current page on update).

**Verified (live):** edit round-trip changes the slug and frontend URL; duplicate slug rejected (value unchanged); create with a custom slug (`My Custom Slug` → `my-custom-slug`) and with an empty slug (`Test Auto Slug Page` → `test-auto-slug-page`).

## 156. Inactive Pages Still Opening on Frontend (13 Aug 2026)

**Problem:** Setting a page inactive from All Pages did not block its public URL. `HomeController::faq()` always rendered `pages.faq`, and `terms()`/`privacy()`/`about()`/etc. fell back to static `pages.*` blade views when the CMS record was inactive, so FAQ and Terms & Conditions kept opening despite `status` being false.

**Files changed:**
- `app/Http/Controllers/HomeController.php` — added a private `pageOrAbort($slug)` helper: loads the Page record, `abort(404)` when it exists but `status` is false, otherwise returns it (null → caller keeps the static-view fallback). Applied to `about()`, `contact()`, `faq()`, `privacy()`, `terms()`, `returnPolicy()`, `supportPolicy()`. The generic `page()` route already 404ed via `firstOrFail` + `status=true`.

**Verified (live):** `/faq`, `/terms`, `/terms-conditions` → 404 (both currently inactive); `/privacy`, `/about-us`, `/return-policy`, `/support-policy`, `/contact-us` → 200.

## 157. Console Errors — Unirate Widget + CounterUp (13 Aug 2026)

**Problem:** Two console errors on every page load: (1) `unirate-widgets.js` fetched `:5072/api/widget/v1/currencies` and failed with `ERR_CONNECTION_REFUSED` — the widget's `<div id="unirate-converter">` was already commented out (`home/index.blade.php`) but its CSS + JS still loaded on every page via the shared layout and auto-initialized; (2) `script.js` logged "CounterUp library not loaded" because the jQuery counterUp lib was never included.

**Files changed:**
- `resources/views/layouts/app.blade.php` — removed the unirate CSS `<link>` and the async `unirate-widgets.js` script tag (dead feature; the converter element is commented out).
- `public/assets/front/js/script.js` — removed the "COUNTER UP" block (guard + `console.warn`); counters on the About page are already animated by the page's own IntersectionObserver script, so the jQuery plugin block was redundant.

**Verified:** rendered homepage HTML contains no unirate script/CSS (only the harmless commented-out div); `script.js` no longer emits the warning.

## 158. Address Country/State/City — Broken "Custom Dropdown" Design (13 Aug 2026)

**Problem:** The Country/State/City dropdowns in address forms were styled/configured for the theme's `nice-select` custom dropdown (`.address-fields-row .nice-select .list` rules) but the plugin was **never initialized** — `script.js` called `$(".nice-select").niceSelect()` on a class that doesn't exist yet — so the fields rendered as plain native `<select>`s (broken/inconsistent look). An invalid stray `<div class="">` also sat inside the country `<select>`.

**Files changed:**
- `resources/views/partials/address-fields.blade.php`:
  - Removed the invalid `<div class="">` wrapper from inside the country `<select>`.
  - Added `.address-fields-row .nice-select` CSS (width 100%, no float, ellipsis on `.current`, arrow `right: 16px`) so the dropdown fills the field column.
  - `wireGroup()` now converts Country/State/City to `niceSelect()` on wire-up; it first `unwrap()`s the layout's `.form-select-wrapper` chevron span (added by `app.blade.php`/admin layout JS) so the dropdown shows a single arrow.
  - `refresh()` now calls `niceSelect('update')` on the Country select so its label stays in sync when JS prefills it (e.g., checkout). The existing `fillSelect`/`bindNiceSelectClicks` paths were already wired for nice-select.

**Verified (headless Chrome, real data):** all three selects render as `nice-select` divs (City correctly `disabled` until a state is picked); no leftover `.form-select-wrapper` around them; cascade works — India → 36 states, then Gujarat enables the city list; default country United States → US states populated. Admin pages unchanged (no nice-select.js there → native selects kept).

## 159. Admin Profile Address Fields — Nice-Select Conversion + State/Zip Support (13 Aug 2026)

**Problem:** Every admin page that reuses `partials/address-fields` (admin profile, admin customers create/edit, admin users create/edit) rendered Country/State/City as plain native `<select>`s because the **admin** layout never loaded `nice-select.js`/CSS (only the front-end layout did after #158) — so the custom-dropdown look from the front-end was missing in admin. Additionally the admin profile summary only showed City + Country (State/Zip hidden), and `updateProfile()` silently dropped `state` and `postal_code`.

**Files changed:**
- `resources/views/admin/layouts/app.blade.php` — loaded `nice-select.css` (head) + `nice-select.js` (footer) globally, so any admin form using the partial gets the same custom dropdown as the front-end.
- `resources/views/admin/profile.blade.php` — the "Profile Settings" form now includes `partials/address-fields` (`prefix => 'pro'`, `zipName => 'postal_code'`) giving Country/State/City + Zip fields; the summary `<ul>` now lists **State**, **Country**, and **Zip** (`user-state`, `user-country`, `user-postal` spans).
- `app/Http/Controllers/AdminController.php` — `updateProfile()` validation now accepts `state` and `postal_code` and `update()`s them (they were never validated/saved before).

**Verified (headless Chrome):** admin profile, admin customers create/edit, and admin users create/edit all render Country/State/City as `nice-select` divs (City correctly disabled until a state is picked). Live PUT save round-trip persisted `state` + `postal_code` earlier in the session (probe values saved + reverted). Note: the database was subsequently restored to a 10:27 backup by an external process, wiping the probe values and reverting the admin user's password to that backup's value.

## 160. Address Cascade — Disabled State + Saved Country on Top (13 Aug 2026)

**Problem:** The Country→State→City cascade logic existed but two gaps remained: (1) the nice-select plugin's click handler opened **any** `.nice-select`, including `.disabled` ones — only CSS `pointer-events: none` blocked real clicks, so the JS path still opened disabled State/City dropdowns; (2) the partial **defaulted Country to "United States"** on new forms, so State was enabled immediately (Country was never "empty"); (3) when editing an address, the saved Country stayed at its alphabetical position instead of the top of the dropdown.

**Files changed:**
- `public/assets/front/js/nice-select.js` — open/close handler selector changed to `.nice-select:not(.disabled)` so a disabled custom dropdown can never open (JS guard closes the gap CSS pointer-events already handled for real mouse clicks).
- `resources/views/partials/address-fields.blade.php`:
  - Country now **starts empty** on new forms (removed the `?: 'United States'` default) — State/City stay disabled until a country is actually chosen.
  - The saved Country is now rendered **first** (right after "Select Country"), marked selected, and skipped from the alphabetical list (no duplication) — matches the `fillSelect` prepend behavior used for State/City.

**Verified (headless Chrome cascade test + live pages):** with empty Country → State/City native+wrapper disabled and the disabled wrapper no longer opens on click; Country=India → State enabled (37 states), City still disabled; State=Gujarat → City enabled; changing Country while India+Gujarat+City are set clears State/City and repopulates for the new country (City re-disabled). Live pages: Add-address modal starts with empty Country; edit-address modal pre-selects the saved Country at the top with State/City `data-selected` preserved.

## 161. Zip/Phone Validation — `postal_code` Forms Skipped (14 Aug 2026)

**Problem:** The address form JS validation only checked `[name="zip_code"]`, but the admin customer/user forms (`admin/customers/*`, `admin/users/form`, `admin/profile`) and the user profile-edit form name the zip input `postal_code` (via `zipName => 'postal_code'`). `validateAddressForm()` resolved no field → the postal code was **completely skipped** on those forms (no JS warning, invalid values passed through). Backend `postal_code` rules also had no pattern regex (`nullable|string|max:20`), so invalid postal codes saved end-to-end on those forms.

**Files changed:**
- `resources/views/partials/address-fields.blade.php`:
  - Zip check now uses `selector: '[name="zip_code"], [name="postal_code"]'` and the loop resolves the field via `item.selector` — validates both field names.
  - `@error($zipName ?? 'zip_code')` so server errors show under the right input name.
- `app/Http/Controllers/UserController.php` — `storeCustomer()` + `update()` `postal_code` rules now include `regex:/^[A-Za-z0-9\-\s]{3,20}$/`.
- `app/Http/Controllers/AdminController.php` — `updateProfile()` `postal_code` rule gets the same regex.

**Verified (headless Chrome against live pages):**
- Profile-edit page (`postal_code`): 8/8 PASS — `AB` / `A@3` block submit with *"Please enter a valid Zip Code."* toast; `10001` passes.
- Addresses page (`zip_code`): 6/6 PASS — no regression.
- Backend validator: `AB` → fails, `10001` → passes.

## 162. Country Dropdown — Top Countries Pinned at Top (14 Aug 2026)

**Problem:** The country dropdown listed all ~250 countries alphabetically, so frequent destinations (US, Canada, UK…) required scrolling/typing every time.

**Files changed:**
- `resources/views/partials/address-fields.blade.php` — added `$topCountries` (United States, Canada, United Kingdom, Australia, Germany, France, India, United Arab Emirates) rendered right after the placeholder (saved country still renders first), skipped from the alphabetical `$restCountries` list (no duplicates). The searchable dropdown iterates `select.options`, so the pinned order carries through automatically.

**Verified (headless Chrome, live page):** dropdown first 8 items are the pinned top countries in order, `Afghanistan` starts the alphabetical remainder; edit forms still show the saved country first with no duplication.

## 163. Countries/States/Cities — Static JSON → DB Tables + Lazy AJAX (14 Aug 2026)

**Problem:** A 2.1MB `countries-states-data.json` shipped with every page load (front JS + shared address partial), and cities were flat country-wise (huge option lists, no state scoping). All three lists now come from MySQL tables and are fetched lazily over AJAX only when a country/state is actually chosen.

**Database (schema + seed):**
- `database/migrations/2026_08_14_000001_create_countries_states_cities_tables.php` — `countries`, `states`, `cities` tables with FKs + indexes + unique `[country_id,name]` / `[state_id,name]`.
- `database/seeders/CountriesStatesCitiesSeeder.php` (registered first in `DatabaseSeeder.php`) — streams CSVs with chunked inserts. Seeded **250 countries / 5,249 states / 152,114 cities** (dr5hn dataset; names normalized/trimmed/deduped so 250/250 country and 100% state names match the old JSON — saved addresses stay valid).
- `database/data/countries.csv`, `states.csv`, `cities.csv` — generated seed sources (0-based index = id; `name`, `countryIndex,name`, `stateIndex,name`). Original `countries-states-data.json` moved to `database/data/` for provenance; `public/assets/front/js/countries-states.js` + the JSON are gone from public/.

**Backend:**
- `app/Models/Country.php`, `State.php`, `City.php` — relationships + `orderBy('name')`.
- `app/Http/Controllers/LocationController.php` + routes `GET /address/states` (`location.states`), `GET /address/cities` (`location.cities`) — per-country/per-state responses cached (`address_states_*`, `address_cities_*`).
- `app/Helpers/AddressHelper.php::countries()` — now reads DB with `Cache::rememberForever('address_countries')`, falls back to hardcoded array on DB failure.

**Front-end (`resources/views/partials/address-fields.blade.php`):**
- Removed `countries-states.js` include; URLs injected via `@json(route(...))` (subdirectory-safe).
- New lazy `loadList()` (fetch + in-memory `__addrCache`/`__addrPending`), `setLoading()` disabled "Loading…" option, async `refresh()` with stale-guards (resolution-time re-read of saved values), `savedValue()` helper so the internal `__loading__` sentinel never leaks into selections.
- **Bugs fixed during headless verification:** (1) cache/pending maps keyed `state`/`city` but lookup used plural `states`/`cities` → TypeError, no fetch ever fired; (2) response key for cities (`data.cities`) vs `kind+'s'` producing `citys` → cities list always empty; (3) leftover `__loading__` value was preserved by `fillSelect` as a "saved" selection alongside the loaded options.
- `resources/views/checkout/index.blade.php` `edit_address()` — sets `data-selected` on state/city before the async fetch resolves so saved values survive the race-free cascade.

**Verified (headless Chrome against live pages, 29/29 PASS):**
- No `countries-states-data.json`/`.js` request on any page load.
- Add modal + checkout + profile-edit + admin profile/create/edit: top 8 countries pinned, country→state→city cascade via AJAX (India→Gujarat→Ahmedabad, Canada→Ontario→Toronto, US→California→Los Angeles), saved values preserved in edit modal & profile-edit (country first, state/city restored), country switch resets state/city, in-memory cache serves repeated selects without extra requests.

## 164. Button Height `40px` → `min-height: 40px` + Comment Out Conflicting Rules (15 Aug 2026)

**Problem:** All buttons had fixed `height: 40px` in `style.css`, preventing padding-based responsive sizing. On the single product page, different buttons had inconsistent heights due to competing CSS rules with different specificity.

**Root cause:** `style.css` loads first, `custom.css` loads second (overrides). The `.steve-btn` in `custom.css` uses `padding: 8px 24px` (no fixed height), but `.steve-btn` in `style.css` set `height: 40px`, blocking the padding approach. Product page had 3 different button heights: qty buttons (45px), Buy Now/Add to Cart (~35px via steve-btn), and tabs (~46px via `.nav-link` specificity).

**Files changed:**

### style.css — 12 button rules commented (`height: 40px`)
| Line | Selector |
|------|----------|
| 179 | `.steve-btn` |
| 1534 | `.icon-btn` |
| 1563 | `.icon-btn.icon-btn-lg` (mobile) |
| 3808 | `.cart-quantity-btn` |
| 6097 | `.btn-primary` |
| 6165 | `.wh-40` |
| 8589 | `.back-btn` |
| 9186 | `.user-table .view-btn` |
| 9574 | `.dt-paging-button` (mobile) |
| 13300 | `.action-btn` (qty) |
| 13903 | `.step-btn` (mobile) |
| 14731 | `.left-arrow-btn` |

### custom.css — `min-height: 40px` added
- `.steve-btn` (line 1238) — added `min-height: 40px` so all steve-btn elements have minimum 40px height

### buttons.css — 2 rules commented
- `.user-table .view-btn` (line 837)
- `.back-btn` (line 873)

### Blade inline styles — 2 rules commented
- `resources/views/categories/index.blade.php` — `.category-toolbar .view-btn`
- `resources/views/shop/index.blade.php` — `.product-nav-wrapper .btn-wrapper .view-btn`

### Product page button height uniformity
- `resources/views/product/show.blade.php`:
  - `.details_qty_input input` — `height: 45px` → `min-height: 40px`
  - `.details_qty_input button` — `height: 45px` → `min-height: 40px`, `line-height: 45px` → `line-height: 40px`

**Non-button rules left untouched** (21 rules): images, inputs, avatars, social links, containers, chevron icons, etc.

**Verified:** CSS braces balanced (0), all button types now use `min-height: 40px` minimum with padding-based sizing.

## 165. Amazon-Style Product Image Hover Zoom (17 Aug 2026)

**Problem:** Product page gallery had no zoom on hover — users couldn't inspect product details closely.

**Solution:** Added Amazon-style image zoom that shows a magnified portion of the image in a side panel on hover.

**Files changed:**
- `resources/views/product/show.blade.php`:
  - Added `.zoom-container` wrapper with `.zoom-lens` (hover area) and `.zoom-result` (magnified panel)
  - CSS: `.zoom-container` relative, `.zoom-lens` crosshair cursor, `.zoom-result` absolute right panel (400×400px, border, shadow, z-index 100, hidden by default)
  - JS: mousemove handler calculates lens position, sets `background-image`/`background-size`/`background-position` on `.zoom-result` for 2.5x magnification
  - Swiper config: `allowTouchMove: false` removed from main gallery (was preventing zoom interaction)

## 166. Address Form Placeholders + Dropdown List Placeholder Item (17 Aug 2026)

**Problem:** Country/State/City dropdowns had generic "Select Country/State/City" placeholders; dropdown list had no way to clear selection.

**Solution:** Updated placeholders to "Select your Country/State/City" and added a gray placeholder item at top of dropdown list.

**Files changed:**
- `resources/views/partials/address-fields.blade.php`:
  - `data-placeholder` attributes updated from "Select Country/State/City" to "Select your Country/State/City"
  - `renderItems()` modified to always show "Select your Country/State/City" as first gray item in dropdown (`.placeholder-item` class with `color: #999`)
  - Clicking placeholder item clears the selection

## 167. Cities Populated for All Empty States (17 Aug 2026)

**Problem:** 1,003 states had 0 cities — users couldn't complete address forms for those regions.

**Solution:** Created migration to add fallback cities for all empty states. Known regions (UK, Ireland) got detailed real cities; all others got the state name as a city.

**Files created:**
- `database/migrations/2026_08_17_133318_seed_cities_for_empty_states.php` — migration with `$knownCities` array for UK (England=30, Scotland=20, Wales=20, Northern Ireland=20, London=35) and Ireland (Dublin, Cork, Galway, etc.); fallback logic adds state name as city for all other empty states

**Data:**
- 250 countries, 5,249 states, cities now fully populated
- 0 states with 0 cities remaining
- Cache cleared (`artisan cache:clear`, `config:clear`, `route:clear`)

## 168. Countries/States Count Columns (17 Aug 2026)

**Problem:** No way to quickly see how many states a country has or how many cities a state has without running queries.

**Solution:** Added `states_count` column to `countries` table and `cities_count` column to `states` table, populated with current counts.

**Files changed:**
- `database/migrations/2026_08_17_134202_add_counts_to_countries_and_states_tables.php` — adds `states_count` (unsigned int, default 0) to countries, `cities_count` (unsigned int, default 0) to states

**Data populated:**
- 229 countries with states_count (21 city-states have 0)
- 5,249 states with cities_count (0 states with 0 cities)

**Top 5 countries by states:** United Kingdom (221), Slovenia (212), Uganda (139), Italy (126), France (124)
**Top 5 states by cities:** Germany > Bavaria (1,757), Australia > New South Wales (1,258), US > California (1,066), US > Texas (1,022), US > New York (991)

## 169. Admin Profile — Image Manager Picker for Avatar (18 Aug 2026)

**Problem:** Admin profile page displayed the user's avatar but had no upload/edit field — the avatar was set once on registration and could never be changed.

**Solution:** Added Image Manager picker (matching theme pattern) to the admin profile settings form, replacing a native file input that was initially added but inconsistent with the theme's approach.

**Files changed:**
- `resources/views/admin/profile.blade.php`:
  - Added hidden `image_from_manager` input, circular avatar preview (`#impPreview_avatar`), "Pick from Image Manager" button with `impOpen_avatar()` onclick
  - Removed native file input and `enctype="multipart/form-data"`
  - Added `@include('admin.partials.image-manager-picker', ['pickerId' => 'avatar', 'targetInput' => 'image_from_manager'])` at bottom
- `app/Http/Controllers/AdminController.php` — `updateProfile()`:
  - Removed `avatar` file validation rule
  - Added `image_from_manager` handling: saves as `storage/...`, deletes old avatar if changed via `deleteImageFiles()`

**Pattern used:** Same as other admin forms (brands, categories, blogs, products) — hidden `image_from_manager` input + `admin.partials.image-manager-picker` partial.

## 170. Tooltip Consistency Fix — 7 Buttons Across 4 Files (18 Aug 2026)

**Problem:** Action buttons had inconsistent tooltip patterns. Edit buttons on modal triggers used `title="Edit"` only (native browser tooltip), while delete buttons used `data-bs-toggle="tooltip"` (Bootstrap tooltip). Some buttons had both `title` and `data-bs-toggle="tooltip"` (redundant). Bootstrap5 doesn't allow `data-bs-toggle="tooltip"` and `data-bs-toggle="modal"` on the same element.

**Files changed:**

### Modal-trigger Edit buttons — wrapped in `<span>` with tooltip (3 files, 3 buttons):
- `resources/views/user/addresses.blade.php:152` — edit button wrapped in `<span data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">`
- `resources/views/user/profile.blade.php:101` — same wrapper fix
- `resources/views/user/vehicles.blade.php:22` — same wrapper fix

### Redundant dual attributes — `title` → `data-bs-original-title` (1 file, 4 buttons):
- `resources/views/admin/categories/index.blade.php` — 4 buttons (Restore, Delete Permanently, Edit, Delete) changed from `title="..."` to `data-bs-original-title="..."`

**Standard pattern (47 buttons across 20 files):**
```html
<button class="action-btn btn-edit" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Edit">
    <svg>...</svg>
</button>
```

**Modal trigger pattern (3 buttons):**
```html
<span data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
    <button class="action-btn btn-edit" data-bs-toggle="modal" data-bs-target="#modalId">
        <svg>...</svg>
    </button>
</span>
```

## 171. Image Manager — WebP Conversion (PNG/GIF Support + `<picture>` Tags)

**Problem:** No WebP support — images served as-is. No `<picture>` tags on frontend. No bulk conversion from PNG/GIF.

**Solution:** Full WebP pipeline: conversion for JPEG/PNG/GIF, DB tracking, frontend `<picture>` rendering, admin toggle.

**Files changed:**
- `app/Models/Image.php` — added `hasWebpVersion()` (checks if `.webp` row exists by matching base filename), `scopeConvertible()` (includes PNG/GIF mime types)
- `app/Http/Controllers/Admin/ImageController.php`:
  - `convert()` and `bulkConvert()` — support PNG/GIF via `convertToWebp()`; check existing webp DB row to prevent duplicates
  - `index()` — convertible filter uses raw subquery `SUBSTRING_INDEX(filename, '.', 1) NOT IN (...)` instead of `whereDoesntHave('webpVersion')` relationship (avoids self-referencing hasOne issues)
- `app/Helpers/image.php` — `imgTag()` wraps output in `<picture><source type="image/webp">` when `.webp` sibling exists; `convertToWebp()` supports PNG/GIF; checks `Setting::get('webp_frontend', '1')` toggle
- `app/helpers.php` — `webpExists()` checks disk with correct `uploads/` → `storage/` prefix; `webpSrc()`/`webpOriginal()` helpers
- `app/Http/Controllers/Admin/AdminController.php` — `updateHeaderSettings()` saves `webp_frontend` setting
- `resources/views/admin/images/index.blade.php` — PNG/GIF/SVG/WebP badges, green "WebP ✓" badge, "Convert All to WebP" button (on `?filter=convertible`), "Not Yet Converted" stat label
- `resources/views/admin/images/edit.blade.php` — expandable convert button for PNG/GIF; "Already Converted to WebP" message with "View WebP Version" link
- `resources/views/admin/settings/header.blade.php` — WebP frontend toggle switch
- **9+ frontend views** — all `<img src="{{ storedImageUrl(...) }}">` → `{!! imgTag(storedPath(...), ...) !!}`:
  - `resources/views/product/show.blade.php`
  - `resources/views/brands/index.blade.php`
  - `resources/views/home/index.blade.php`
  - `resources/views/blog/show.blade.php`
  - `resources/views/layouts/app.blade.php`
  - `resources/views/partials/footer-columns.blade.php`
  - `resources/views/user/profile.blade.php`
  - `resources/views/user/profile-edit.blade.php`
  - `resources/views/user/layouts/sidebar.blade.php`
  - `resources/views/user/reviews.blade.php`
  - `resources/views/user/followed-sellers.blade.php`

**Key architecture notes:**
- `storedPath()` returns relative paths like `uploads/2026/08/file.jpg`; `storedImageUrl()` returns full asset URLs
- Image Manager creates `.webp` file on disk AND a new DB row with `mime_type: 'image/webp'`; original is NOT deleted
- `public/storage` symlink must exist for `webpExists()` to find `.webp` files under `uploads/` paths
- Remaining `storedImageUrl()` plain `<img>` tags in `admin/` views intentionally left unchanged
- `Setting::get('webp_frontend', '1')` defaults to `'1'` (enabled) when not in DB

## 172. Admin Login — Password Hash (Bcrypt) Fix

**Problem:** Admin login threw `BadMethodCallException: This password does not use the Bcrypt algorithm` because stored password wasn't in Bcrypt format.

**Fix:** Reset admin password via `Hash::make()` through artisan tinker.

## 173. Image Manager — WordPress-Style Bulk Select + Grid/List Toggle

**Problem:** Image manager had old-style checkboxes on every card, no way to toggle between grid/list views, and bulk actions were hidden until checkboxes were manually ticked.

**Solution:** Complete rewrite matching WordPress Media Library UX — toggle-based bulk select, CSS Grid layout, proper list view with columns.

**Single file:** `resources/views/admin/images/index.blade.php`

**Changes:**

### Card-Header Redesign
- **Normal mode:** image count + "Bulk Select" button + Grid/List toggle buttons + "Convert All to WebP" (when filter=convertible)
- **Bulk mode:** selected count (blue) + Convert to WebP / Mark Unused / Delete / Cancel buttons
- Header mode toggled via CSS: `body.img-bulk-mode` hides normal, shows bulk actions

### Per-Card Checkboxes Removed
- Old `<label><input type="checkbox" class="image-checkbox">` removed from every card
- **Normal mode:** card click navigates to edit page (via `<a>` tag)
- **Bulk mode:** card click toggles selection with blue border + blue checkmark overlay (SVG circle)
- `pointer-events: none` on edit link when bulk mode is active

### JavaScript — WordPress UX Flow
- `enterBulkMode()` — adds `img-bulk-mode` class to body
- `exitBulkMode()` — removes class, clears `selectedIds` Set, syncs hidden inputs, resets count
- `handleCardClick(card, id)` — normal mode navigates to edit; bulk mode toggles select
- `bulkAction(url)` — validates selection, submits form
- `convertAllUnconverted(url)` — selects all visible cards, then submits
- Escape key exits bulk mode
- Selected IDs synced to hidden `<input name="ids[]">` dynamically via `syncHiddenInputs()`

### Grid View — CSS Grid
- `#images-container.grid-view` — `display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 16px`
- No Bootstrap `row`/`col-*` dependency — responsive columns adapt to container width
- Default view, saved to `localStorage`

### List View — Table-Like Layout
- **Header row** — Name | Size | Type | Usage | WebP | Edit columns
- **Each card** — 56px thumbnail | filename | size+dimensions | type badge | usage badge | WebP checkmark/dash | edit pencil link
- Cards separated by thin `border-top` lines, no card shadows
- Grid-only elements (detailed badges, separate size/dimension text) hidden via `.grid-view-only`
- List-only elements (type badge, usage badge, webp status, edit link) hidden via `.list-view-only`
- `body.list-view-active` toggles visibility of both

### View Persistence
- `localStorage.getItem('image-manager-view')` — 'grid' or 'list'
- `setView(mode)` — toggles classes, saves preference, syncs header visibility

**No backend/controller changes required.**

---

## 174. Home Page Sliders — Scroll-Triggered Autoplay (19 Aug 2026)

**Problem:** All 4 home page Swiper sliders (categories, featured products, best-selling, latest posts) had no autoplay — users had to manually swipe to see content.

**Solution:** Added IntersectionObserver-based autoplay that starts when the slider enters the viewport and pauses when it leaves. First slide advances immediately with 600ms animation, then normal autoplay interval continues.

**Files changed:**
- `public/assets/front/js/script.js:531-610`:
  - Added IntersectionObserver on `.product-cards-slider` containers (featured-products + best-selling)
  - On entry: `slideNext(600)` then `autoplay.start()` (delay 3000ms, pauseOnMouseEnter true)
  - On exit: `autoplay.stop()` + `slideTo(0)` (reset to first slide)
- `resources/views/home/index.blade.php:600-663`:
  - Added IntersectionObserver on `.home-cate-slider` (categories) and `.swiper-latest-posts` (latest posts)
  - Same pattern: immediate `slideNext(600)` on entry, stop + reset on exit
  - Categories: delay 2500ms; Latest posts: delay 4000ms

**Key detail:** `autoplay.waitForTransition: true` ensures first `slideNext(600)` completes before the 3s/2.5s/4s timer starts.

---

## 175. Image Manager — Grid/List Toggle (Shop Page Style) (19 Aug 2026)

**Problem:** Image Manager grid/list toggle had different styling than the shop page toggle — used custom CSS classes instead of the shared `view-btn` + `steve-btn` classes.

**Solution:** Updated toggle buttons to match shop page design with consistent `steve-btn view-btn` classes and `fa-th-large`/`fa-bars` icons.

**Files changed:**
- `resources/views/admin/images/index.blade.php:237-263`:
  - Grid button: `<button type="button" class="steve-btn view-btn active">` with `fa-th-large` icon
  - List button: `<button type="button" class="steve-btn view-btn">` with `fa-bars` icon
  - Removed old `<a class="view-toggle">` with `<i class="fas fa-grip-horizontal">`/`<i class="fas fa-list">`
- `public/assets/front/css/custom.css`:
  - Removed old `.view-toggle` CSS (was separate from shop page `.view-btn`)

---

## 176. BladeCompiler Permission Fix — `touch()` Utime Error (19 Aug 2026)

**Problem:** `touch(): Utime failed: Operation not permitted` error in `BladeCompiler.php:215` — compiled blade views owned by `jasaram` (CLI user) while web server runs as `www-data`.

**Immediate fix:** `php artisan view:clear` cleared compiled views, allowing `www-data` to regenerate them.

**Root cause:** Files created by CLI (`php artisan`) are owned by the CLI user (`jasaram`), not `www-data`. Web server cannot modify/touch these files.

**Permanent fix (requires manual sudo):**
```bash
sudo usermod -aG www-data jasaram
sudo find /var/www/html/stautoparts/storage -type d -exec chmod 2775 {} \;
sudo chown -R jasaram:www-data /var/www/html/stautoparts/storage/framework/views/
```

**Files affected:** None (view cache cleared, no code change)

---

## 177. PageSpeed Optimization — Asset Minification + .htaccess Caching/Gzip (20 Aug 2026)

**Problem:** Slow page loads — unminified CSS/JS shipped to browsers (style.css 377KB readable, jquery-ui.js 521KB, slick.js 90KB, script.js 29KB), no browser caching headers, no gzip compression.

**Solution:** Minified all large front assets, added full browser-caching + compression + security headers to `public/.htaccess`.

**Minification results (.bak backups created then removed same day):**
| File | Before | After | Saved |
|---|---|---|---|
| `public/assets/front/css/style.css` | 377 KB (18,003 lines) | 308 KB (1 line) | 69 KB |
| `public/assets/front/js/jquery-ui.js` | 521 KB (18,563 lines) | 257 KB | 264 KB |
| `public/assets/front/js/slick.js` | 90 KB (3,038 lines) | 43 KB | 47 KB |
| `public/assets/front/js/script.js` | 29 KB | 14 KB | 15 KB |

**`public/.htaccess` additions (+40 lines):**
- `mod_expires.c`: images/fonts `access plus 1 year`, css/js `access plus 1 month`, html `0 seconds`
- `mod_headers.c`: `Cache-Control: public, max-age=31536000, immutable` for static assets; `no-cache, must-revalidate` for html; security headers `X-Content-Type-Options: nosniff`, `X-Frame-Options: SAMEORIGIN`, `Referrer-Policy: strict-origin-when-cross-origin`
- `mod_deflate.c`: DEFLATE for html/css/js/json/svg

---

## 178. Front Layout — Async CSS, Deferred JS, Preconnects (20 Aug 2026)

**Problem:** Render-blocking CSS/JS in `layouts/app.blade.php` delayed first paint: 8 non-critical stylesheets + 10 synchronous scripts (incl. Summernote loaded on every front page though only used in admin).

**Files changed:**
- `resources/views/layouts/app.blade.php`:
  - `<link rel="preconnect">` to fonts.googleapis.com, fonts.gstatic.com, cdnjs.cloudflare.com
  - Non-critical CSS (line-awesome CDN, slick, swiper, nice-select, jquery-ui, animate, toastr, dynamic-font) switched to `rel="preload" as="style" onload="this.rel='stylesheet'"` async pattern
  - Removed render-blocking Summernote CSS + JS from front layout (admin-only dependency)
  - All JS except jQuery now `defer` (slick, swiper, jquery-ui, nice-select, wow, bootstrap, toastr, script.js, myscript.js)
  - Session toastr flash calls wrapped in `DOMContentLoaded` (toastr loads deferred)
  - Added Poppins + Saira Google Font families
- `resources/views/admin/images/edit.blade.php` — removed header row (Back link duplicated breadcrumb); preview img `rounded` class removed

---

## 179. Responsive Images — srcset/sizes Pipeline in `imgTag()` (20 Aug 2026)

**Problem:** Full-size originals served to every viewport; no `srcset`; images not lazy-loaded.

**Files changed:**
- `app/Helpers/image.php`:
  - New `ensureResponsiveVariant(string $src, int $targetWidth)` — GD-based on-demand resize: generates `{name}_{width}.webp` variant next to original (quality 80, alpha preserved for PNG), cached on disk, returns null when source ≤ target width
  - `imgTag()` new signature: `imgTag($src, $alt, $class, $extra, int $displayWidth = 0)` — when `$displayWidth > 0`, emits `srcset` (250w + 500w variants) + `sizes="{displayWidth}px"`; `<picture>` wraps responsive webp source when WebP frontend toggle enabled, else inline srcset
  - All `imgTag()` output now includes `loading="lazy" decoding="async"`
- `resources/views/partials/product-card.blade.php` — product image `imgTag(..., 300)`
- `resources/views/home/index.blade.php` — category slider images `imgTag(..., 250)`; offer banners + services icons + partner logos → `<picture>` webp sources + lazy loading; deal-of-day image gets `object-fit-cover`

---

## 180. Home Hero — LCP Fix (`fetchpriority="high"` + WebP Picture) (20 Aug 2026)

**Problem:** Hero was a CSS `background-image` — not discoverable by the browser preload scanner, no WebP variant, hurt LCP.

**Files changed:**
- `resources/views/home/index.blade.php` — hero background replaced with real `<img>` (`position:absolute; inset:0; object-fit:cover`) with `fetchpriority="high"`; wrapped in `<picture>` with `.webp` source when a sibling webp exists (checked via `preg_replace('/\.[^.]+$/', '.webp', ...)`) ; hero content raised to `z-index:1`

---

## 181. Shop Price Filter — Real Max Price + Session Currency + Clean URLs (20 Aug 2026)

**Problem:** Price range slider hardcoded 0–1000 (products cost more; filter silently capped); labels/chips always showed `$` regardless of session currency; submitting filters produced ugly URLs with empty params / trailing `?`; Clear buttons rendered for empty-string params (`request()->has()` vs `filled()`).

**Files changed:**
- `app/Http/Controllers/ShopController.php` — `getSharedData()` returns `maxProductPrice` (`Product::where('status',true)->max('price') ?? 1000`) and `currencySymbol` (from session currency config)
- `resources/views/shop/index.blade.php`:
  - Slider max = `$maxProductPrice * $rate`; hidden inputs `#price-slider-max` / `#price-currency-symbol` feed JS
  - Range label + filter chips use session currency symbol with `toLocaleString()`
  - `hasPriceFilter` chip condition compares against dynamic max instead of 1000
  - Vehicle/price Clear buttons use `request()->filled()`
  - New `buildCleanFilterUrl(form)` — builds query string skipping empties/disabled fields; vehicle form intercepts submit → clean redirect; price form strips default full-range (0–max) params on submit

---

## 182. Admin Logout Route Name Collision Fix (20 Aug 2026)

**Problem:** Admin logout route was named `logout` (same as the user-facing auth route name space) — `route('logout')` in admin navbar resolved against the wrong route group.

**Files changed:**
- `routes/web.php` — admin logout route renamed `logout` → `admin.logout`
- `resources/views/admin/partials/navbar.blade.php` — dropdown form action → `route('admin.logout')`

---

## 183. Admin Dashboard — Revenue Chart Scroll Into View (20 Aug 2026)

**Files changed:**
- `resources/views/admin/dashboard.blade.php` — chart cards got ids (`orders-status-chart-card`, `revenue-chart-card`); clicking Monthly/Weekly/Today view buttons smooth-scrolls the revenue chart card to center

---

## 184. Image Manager — Pagination 80/Page + Bulk & List View Polish (20 Aug 2026)

**Files changed:**
- `app/Http/Controllers/Admin/ImageController.php` — index pagination `28` → `80` per page
- `resources/views/admin/images/index.blade.php` (~402 lines):
  - Bulk mode: all cards dim to 0.55 opacity, selected cards full opacity + blue border/check overlay (top-right corner badge)
  - List view redesign: 140×140px thumbnails, column-stacked card info, blue left-border on selected rows, overlay hidden (checkbox-style `bulk-check` shown instead)
  - List header checkbox column appears only in bulk mode; view-toggle hidden while bulk mode active
  - Grid view kept as CSS grid `repeat(auto-fill, minmax(180px, 1fr))`

---

## 185. PageSpeed Test Reports — Measured Scores (20–21 Aug 2026)

Lighthouse reports saved for verification of tasks #177–180:
- `public/PageSpeedTest/desktop-20260820T153039.html` — Desktop: Performance **86**, Accessibility 79, Best Practices 92, SEO 63
- `mobile-20260821T112243.html` (repo root) — Mobile: Performance **69**, Accessibility 84, Best Practices **100**, SEO 63

**Housekeeping (uncommitted):** root-level mockup/report files moved into `public/` (`admin-dashboard-suggestion.html`, `admin-dashboard-suggestion-2.html`, `PageSpeedTest/`); `buttons.html` + stale `localhost-*.html` report deleted; `test-watcher2.html` modified + `test-watcher3.html` created (file-watcher verification fixtures).

---

## 186. Mobile Performance — TTFB 1.5s → 0.15s (21 Aug 2026)

Root cause: `View::composer('*')` in `AppServiceProvider` ran `childrenRecursive` on every request — **4,030 of 4,099 total queries** per page load (category tree explosion).

**Files changed:**
- `app/Providers/AppServiceProvider.php` — composer category tree wrapped in `cache()->remember('front_mobile_category_tree_v1', 21600, ...)`; `Category::saved`/`Category::deleted` hooks flush the key
- `config/cache.php` — `'serializable_classes'` whitelist added (`Illuminate\Support\Collection`, `Illuminate\Database\Eloquent\Collection`, `App\Models\Category`) — without it cached Eloquent models unserialize as `__PHP_Incomplete_Class`
- Ran `php artisan config:cache` (opcache already enabled web-side; `view:cache` skipped — www-data ownership issue from #176; `route:cache` impossible — 7 closure routes)

**Result:** home 1.5s → **0.145s**, shop 0.95s → **0.114s**, categories 0.28s → **0.065s**

---

## 187. Mobile Performance — Render-Blocking & Payload (21 Aug 2026)

**Files changed:**
- `resources/views/layouts/app.blade.php`:
  - 3 render-blocking Google Fonts `<link>`s merged into 1 async `preload/onload` link (+`<noscript>` fallback)
  - Header logo: `loading="eager" fetchpriority="high" width="60" height="63"` (was lazy — LCP image); mobile-menu + footer logos got ratio-correct `width`/`height`
  - jquery-ui CSS preload + JS moved to `@stack('jquery-ui-css')` / `@stack('jquery-ui-js')` (loaded only where used)
  - `all.css` → `all.min.css` (152KB → 93KB, csso)
- `resources/views/shop/index.blade.php` — pushes jquery-ui CSS+JS via the new stacks
- `app/Helpers/image.php` — `imgTag()` now lets `$extra` override default `loading=`/`decoding=` attrs
- `public/assets/front/js/script.js` — removed dead `#slider-range` init block (element doesn't exist anywhere; real slider is `#price-slider`, shop-only)

---

## 188. Mobile Accessibility — Buttons, Links, Contrast, Headings (21 Aug 2026)

Fixes Lighthouse "button-name ×26", "link-name ×3", "link-in-text-block", "color-contrast ×15", "heading-order ×5":

**Files changed:**
- `resources/views/partials/product-card.blade.php` — wishlist button `aria-label="Add to wishlist"`; default `$titleTag` h6 → h3
- `resources/views/layouts/app.blade.php` — header-toggle `aria-label="Open menu"`; searchIcon `aria-label="Search"`
- `resources/views/partials/footer-columns.blade.php` — social links aria-labels (Facebook/Twitter/LinkedIn)
- `public/assets/front/css/custom.css` — `.copyright-hyperlink-text` underlined (in-text-link cue); `.gs-footer-bottom .text-secondary` → `#a2a2ab`; `.gs-footer-bottom .copyright-hyperlink-text` → `#ff8560`
- `public/assets/front/css/style.css` — `--primary` `#e62e04` → `#d02a03` (white-on-primary 4.41 → 5.23, primary-on-white/#f6f6f6 also pass); price `del` color `#b7aead` → `#6e6966`
- `resources/views/home/index.blade.php` — countdown numbers h6 → `<p>` (not headings); post titles h5 → h3

SEO stays 63 by decision (hardcoded `noindex,nofollow` in layouts kept). Verified: all pages 200, mobile menu renders from cache, shop slider intact, home warm TTFB ~0.15s.

**Follow-up (Agent Accessibility audit):** notification bell link (`a.position-relative`) had no accessible name → `aria-label="Notifications"`; also added aria-labels to Compare/Wishlist/Cart header icon links (tooltip-only names don't count in static HTML).

---

## 189. Front-end Assets — Beautified Minified Sources (21 Aug 2026)

Beautified the two single-line minified files we'd been patching surgically (js-beautify, 2-space indent). `custom.css`, `myscript.js` were already readable; `all.css` is Font Awesome vendor build — left untouched.

**Files changed:**
- `public/assets/front/css/style.css` — 308KB minified → 351KB formatted
- `public/assets/front/js/script.js` — 14KB minified → 17KB formatted

**Verified:** CSS re-parses cleanly (csso round-trip), JS passes `node --check`, home/shop/category all 200. No payload concern — `mod_deflate` serves style.css at ~44KB gzipped; layout `?v=filemtime` cache-busters pick up the new versions automatically. Pre-beautify backups: `/tmp/opencode/style.css`, `/tmp/opencode/script.js`.

---

## 190. Lighthouse Report Files — 403 Fix (21 Aug 2026)

`public/desktop-20260820T153039.html` + `public/mobile-20260821T112243.html` were mode `600` (owner-only) after the #185 housekeeping move, so Apache's `www-data` couldn't read them → "Forbidden". Set both to `644`; URLs `http://localhost/stautoparts/{mobile-20260821T112243,desktop-20260820T153039}.html` now load.

---

## 191. Swiper Observer — Investigated, No Bug Found (21 Aug 2026)

Report: "swiper observer not working". Verified with headless Chrome (puppeteer-core + system Chrome, desktop 1280×800 & mobile 390×844 viewports):

- All active home sliders (`home-cate-slider`, `featured-products`, `best-selling`) autoplay-start on scroll-into-view (`realIndex` advances) and stop when scrolled away — IntersectionObserver pattern from 4946065b intact after script.js beautify
- `.latest-posts-slider` absent by design: `$showAsSlider = $latestPostsCount >= 4`; currently 2 posts → grid renders
- Zero console errors/warnings/failed requests on home (live capture + both Lighthouse reports)

Likely stale browser cache — hard refresh advised. Test scripts kept at `/tmp/opencode/swiper-test.js`, `/tmp/opencode/console-capture.js`.

---

## 192. Shop Filters — Apply Buttons, Real Min Price, Floored Display (21 Aug 2026)

**Files changed:**
- `app/Http/Controllers/ShopController.php` — added `minProductPrice` (`Product::min('price')`) alongside max
- `resources/views/shop/index.blade.php`:
  - Price slider min = lowest product price floored, max floored too (11.99/499.99 → **$11 - $499**, was $0 - $499.99); new `#price-slider-min` hidden input; JS default-min bug fixed (absent param → sliderMinVal)
  - Price Apply disabled until slider moves off full range (`slide`/`change` toggle); Vehicle Apply disabled until year/make/model selected — both server-side initial state + JS toggling
- `public/assets/front/js/script.js` — **fixed console TypeError on all non-home pages**: Swiper 11 keeps unmatched selector string in `el`, so `observeSwiperAutoplayGlobal` crashed on `.observe(el)`; guard now `e.el instanceof Element`

**Verified (puppeteer):** both buttons disabled initially → enable on value → disable on reset; 0 page errors across home/shop/category/brands.

---

## 193. User Dashboard — Design Suggestion Mockups (21 Aug 2026)

**Files added:**
- `public/user-dashboard-suggestion.html` — Suggestion 1 "Clean Corporate": welcome header + avatar, 4 stat cards (Total Spent / Orders / Pending / Wishlist), Spending Overview line chart (monthly/weekly toggle) + Orders-by-Status doughnut, Recent Orders table, My Garage vehicles, Wishlist preview, Profile Completeness ring (80%), Quick Actions grid
- `public/user-dashboard-suggestion-2.html` — Suggestion 2 "Modern Auto": dark navy gradient hero with shipment alert, horizontal stat strip, Active Shipment vertical timeline tracker (placed → paid → shipped → out for delivery → delivered), Recent Orders cards with product thumbs, Saved-for-Later wishlist grid, Garage sidebar card, Notifications feed, Quick Actions

**Notes:**
- Both standalone static HTML (Bootstrap 5.3 CDN + Font Awesome 6.5 + Inter + Chart.js), same conventions as `admin-dashboard-suggestion*.html`
- Primary color uses current brand **#d02a03** (admin mockups still show old #e62e04)
- Based on real user dashboard data: stats from `DashboardController::userDashboard()` (total_spent/pending/completed/wishlist), sidebar sections (orders, reviews, tracking, wishlist, followed sellers, vehicles, addresses, notifications)

**Verified (puppeteer):** both load 200; no JS errors (only favicon.ico 404); no horizontal overflow at 1440px & 390px; ud1 charts render (860x280 line, 190x190 doughnut); screenshots at `/tmp/opencode/ud1-{desktop,mobile}.png`, `/tmp/opencode/ud2-{desktop,mobile}.png`

---

## 194. Home Categories Slider — Square Images + 5th Slide Cut Fix (24 Aug 2026)

**Files changed:**
- `public/assets/front/css/style.css`:
  - `.gs-cate-section .category-image`: fixed 300×300 → responsive `width:100%; max-width:300px; aspect-ratio:1/1`
  - Added `.gs-cate-section .category-image img { width/height:100%; object-fit:cover }` (img had NO sizing before — root cause of distorted/unset images)
  - **Removed `.gs-cate-section .swiper-wrapper { gap:10px }`** — duplicated Swiper JS `spaceBetween:20`, making 5-slide total width exceed container → 5th category cut off

**Verified (puppeteer):** 1440px & 1280px → slidesPerView=5, translate=0, **5/5 slides fully visible**; boxes ratio 1.00 with object-fit cover; mobile 300×300 square. Categories page styles untouched.

**Incident:** accidentally overwrote `all.min.css` with csso(style.css) output — restored exact HEAD version (99KB FA+Bootstrap bundle). Note: site loads `style.css` directly (`layouts/app.blade.php:23`) + custom.css + all.min.css bundle.

## 195. Wishlist tooltip stuck after click (fixed)

**Issue:** `.single-product` wishlist icon ka tooltip click ke baad cursor hatane/scroll par bhi chipka rehta tha. Root cause: Bootstrap tooltip default trigger `hover focus` — click par button ko FOCUS mil jata tha (wishlist-btn me `data-bs-trigger` missing tha, jabki compare/quick-view/remove me tha).

**Fix:**
- `public/assets/front/js/script.js` — dono `new bootstrap.Tooltip(...)` init me `{trigger:"hover"}` + scroll par stuck tooltips hide karne wala safety net (admin layout pattern jaisa)
- `resources/views/partials/product-card.blade.php:33` — explicit `data-bs-trigger="hover"` add

**Verified (puppeteer):** hover→tooltip shows (1); click+cursor away→0; scroll→0. Header icons bhi covered (global init fix). Admin layout me ye pattern pehle se tha.

---

## 196. Deal of Day right image cut (user fixed manually)

User ne `object-fit-cover` class khud hata di (`home/index.blade.php:273`). Note: image ab bhi left:52% + 960px width se <1920 viewports par right-cut + vertically squashed hai, par user ne bola "sab sahi he" — no further action. "!! Special Offer !!" heading = deal_of_day section (NOT gs-offer-section). BG image: user said no change. HomePageSection json-cast bug researched & explained to user, fix skipped per user ("no changes").

## 197. Security audit + critical fixes

**Audit findings:** No backdoors/SQLi/eval patterns found. Uploads validated, CSRF intact, .env not in git.

**Fixed (CRITICAL):**
- Deleted public/info.php + public/phpinfo.php (phpinfo exposure)
- .env: APP_ENV=production, APP_DEBUG=false (+ config:clear)
- IDOR: OrderController::show() + invoice() me user_id ownership check (admins/staff bypass)
- Removed leftover dd() debug from OrderController::show() (ab proper 404)

**Fixed (MEDIUM):**
- Login routes par throttle:5,1 (customer + admin dono)
- /admin/clear-cache par role:master_admin,admin middleware
- Admin navbar Clear Cache button staff ke liye hidden

**Verified:** php -l sab clean; home 200; info.php 404; artisan about = production/debug OFF.
**Pending (user approval pending tha):** User $fillable hardening, SSRF URL validation (image_url), SVG upload policy, public/ dev-junk cleanup, root-level stoparts.sql/scripts hygiene.

## 198. Security hardening (optional items done)

- SSRF guard in saveImageFromUrlWithWebp (app/Helpers/image.php): sirf public http(s) URLs, localhost/.local/.internal + private/reserved IP ranges blocked, 15s timeout, 10MB size cap. Verified: localhost/private-IP/file:// sab null return karte hain
- SVG removed from Image Manager upload validation (ImageController:53,158) — existing uploads me 0 svg files the, zero impact. (SVG script-embedding XSS risk tha)
- User $fillable trimmed: access_token, refresh_token, device_token, verification_code, new_email_verificiation_code removed (codebase me inka koi use nahi). role/status/balance rakhe kyunki admin flows intentionally set karte hain
- Dev junk moved public/ -> _dev-archive/ (dashboard suggestions, QA reports, buttons.html, desktop/mobile HTML dumps, PageSpeedTest) — ab web se accessible nahi
- stoparts.sql -> backup/ moved (root clean)
- Verified: php -l clean, home 200, user model fetch OK

## 199. Clear Cache staff ke liye wapas enabled

User feedback: staff bhi page/blog/product create karta hai, har baar admin se cache clear karwana practical nahi. 
- Route middleware: role:master_admin,admin -> role:master_admin,admin,staff (customers ab bhi blocked)
- Navbar @if hiding removed — button sab admin-panel roles ko dikhta hai
- Cache clear mechanism verified working (database cache store, view/route caches, www-data writable). Staff ke liye pehle 403 aata tha isliye "kaam nahi karta" lag raha tha.

## 200. Summary tables me pehli row ke upar hairline

User request: order-confirmed jaise pages ki summary tables me first row ke upar bhi line chahiye.
- `checkout/order-confirmed.blade.php`: dono tables ko `summary-table` class + first-row tds se border-top-0 hataya (baaki rows waise hi)
- `checkout/payment.blade.php`: thead ths se border-top-0 hataya + table ko summary-table class
- style.css: `table.summary-table tbody tr:first-child > td, table.summary-table thead tr:first-child > th { border-top: 1px solid #e6e8ec }`
- Verified: firstRowTop=1px rgb(230,232,236) (#e6e8ec), dusri rows 0 (unchanged)

## 201. Subtotal row top border + all.min.css restore (FA icons fix)

**Subtotal border:** Order Details section ke neeche wali summary table (Subtotal/Shipping/Tax/Coupon/Total) pehle cover nahi hui thi. `order-confirmed.blade.php`: us table ko `summary-table` class + Subtotal row (th+td) se border-top-0 hataya. Ab Subtotal ke upar 1px #e6e8ec line (Shipping/Tax/Coupon rows unchanged).

**all.min.css CRITICAL FIX:** File me SAARE Font Awesome content values corrupt the (`content:"0"`, `"1"`... — `\f` escape sequences kabhi galat tarike se process ho gaye the; git HEAD me bhi corrupted tha, commit 318c1b68 "21-aug End of day" se). Site-wide FA icons broken the.
- Restore: cdnjs se official **Font Awesome Free 6.4.0 all.min.css** download karke replace kiya (102025 bytes, 1856 content values, 1424 \f glyphs)
- Verified live: `.fas fa-chevron-down` ka ::before content = proper glyph + fontFamily "Font Awesome 6 Free"

NOTE: agar future me is file ko kabhi shell/echo se write karna ho to backslash escapes ka dhyan rakhna (printf '%s' ya base64 route use karo).

## 202. Font Awesome Icons Blank — Webfont Path Fix (24 Aug 2026)

**Problem:** #201 ke all.min.css restore ke baad bhi FA icons (`.fa-chevron-down` → `content:"\f078"`, etc.) site-wide blank the.

**Root cause:** CSS content values sahi the, lekin cdnjs wali official FA 6.4.0 file fonts `../webfonts/` relative path se maangti hai (`public/assets/front/webfonts/` — folder exist hi nahi karta). Project ke saare font files `public/assets/front/fonts/` me hain (HEAD wali purani CSS wahin point karti thi). Font files 404 → glyphs render nahi hote.

**Fix:** Python byte-safe replace — `../webfonts/` → `../fonts/` (20 URL refs). Shell sed isliye nahi use kiya kyunki file me `\f` escapes hain (#201 note).

**Verified:** fa-solid-900 / fa-brands-400 / fa-regular-400 `.woff2` sab HTTP 200 full size; served CSS me 8 unique `url()` ab sab `../fonts/` par; chevron rule intact — `fa-chevron-down:before{content:"\f078"}`.

**Files changed:** `public/assets/front/css/all.min.css` (URL paths only)

## 203. Brands — Products Top-Up to 13 Each (24 Aug 2026)

**Problem:** 54 brands had fewer than 13 products (28 brands with just 1 product).

**Data added:** **592 new auto-parts products** (total 216 → 808). Every brand now has exactly 13 products; each product matched to its brand's specialty (Mobil 1 → oils, CSF/Mishimoto/Behr → cooling, Magnaflow/Bosal/Walker → exhaust, Garrett/BorgWarner → turbo, KYB/Sachs/Monroe → shocks, Philips/Osram/Hella → lighting, SKF/Timken/GKN → bearings/axles, Optima → batteries, etc.).

**Category assignment:** products assigned by type to available leaf categories — top: Air and Fuel Delivery (74), Cooling (60), Engine Parts (52), Electrical (39), Exhaust (37), Shocks & Struts (35). Name→id matched case-insensitively from DB.

**Product fields:** realistic USD pricing (+ old_price ~60%), stock 15–90, badge on ~25%, featured ~15%, year/make/model cycling the standard 10 vehicle combos, unique slugs (0 duplicates), status=1.

**Images:** reused existing product images of the SAME category per product (category→image pool) so listings look correct — replaceable anytime via Image Manager.

**Verified:** shop / shop?brand=... / brands all HTTP 200; `Brands below 13: 0`.

## 204. Admin Products List — `$p` Typo 500 Fix (24 Aug 2026)

**Problem:** `/admin/products` → 500 `Undefined variable $p` — product name link used `$p->slug` instead of `$product->slug`.

**Fix:** `resources/views/admin/products/index.blade.php:92` — `$p->slug` → `$product->slug` (only occurrence in file).

**Verified:** blade compiles clean; page loads (302 = auth redirect for guest curl).

## 205. New Products — Unique Images (Repeat Fix) (24 Aug 2026)

**Problem:** Task #203 wale 592 naye products same-category pool se image reuse kar rahe the → listings me images repeat ho rahi thi.

**Fix:** Storage inventory scan — `uploads/2026/*` me 1,685 base originals the, jinme se **1,267 kisi product par use nahi ho rahi**. Keyword buckets (`brake`, `radiator`, `steering-wheel`, `engine-oil-filter`, etc.) banakar har naye product ko **ek unique unused image** assign ki (`updateQuietly`).

**Verified:** 592/592 products with image, **592 distinct images, 0 duplicate assignments**, sab files disk par exist; `/shop` 200.

**Note:** Zyada tar unused images uuid-style filenames (`173086523535Ifn9IA.jpg`) aur DB me title/alt null hai — isliye exact content-based matching offline possible nahi thi; keyword-match jahan mila wahan, warna generic pool se unique assignment. Per-product relevance ke liye Image Manager me manual tagging/replace karna hoga.

## 206. Products — Minimum 5 Gallery Images (24 Aug 2026)

**Problem:** 650 products (incl. #203 wale 592) me 0 gallery images the, 52 me 1–4 — "Gallery Images" section khaali/kuchha dikhta tha.

**Fix:** Script (`/tmp/opencode/fill_galleries.php`):
- Orphan cleanup: 472 disk files jinke DB me Image rows nahi the → rows create kin (2 same-named duplicates skip; `images_filename_unique` constraint). Library: **1,732 Image rows**.
- Har product jisme <5 gallery: pehle globally-unused pool (862 ids) se distinct images attach kiye `image_product` pivot via; pool khatam hone par full library cycling with per-product offset (7 stagger) taaki padosi products ko same sets na milein.
- Guards: apna hi main image gallery me nahi, ek gallery ke andar 5 sab distinct.

**Verified:** 702 products topped-up, 3,427 attachments added → **0 products with <5 gallery, 0 empty galleries, 0 duplicate-image galleries**; random product ke saari 5 files disk par OK; product page 200 + gallery markup render.

## 207. Grid/List View Tooltips — Bootstrap Tooltip with Touch Disable

**Issue:** Bootstrap tooltip on grid/list buttons stayed visible on click; native `title` had different design.

**Fix:** Restored `data-bs-toggle="tooltip" data-bs-trigger="hover"` (Bootstrap styling, hover-only trigger) + `bootstrap.Tooltip.getInstance(this)?.hide()` in click handlers. On touch devices (`ontouchstart`), tooltips are completely disabled via `tip.disable()`.

**Files:** `resources/views/shop.blade.php`, `resources/views/categories.blade.php`

## 208. Grid/List Toggle — Extracted to Partial

Created `resources/views/partials/grid-list-toggle.blade.php` (buttons only, no wrapper). Used in shop sidebar, shop header, and categories page.

## 209. Force Grid View on Small Screens (≤580px)

Both shop and categories pages force grid layout when `window.innerWidth <= 580`, regardless of localStorage preference (on load and resize).

## 210. Form-Select-Wrapper — Single Outside Click to Close

**Issue:** Native `<select>` dropdown closes on outside click/blur, but `focused` class stayed until a second outside click (blur doesn't always fire).

**Fix:** Added document `click` handler — if click target is outside `.form-select-wrapper`, remove `.focused` from all wrappers immediately.

**Files:** `resources/views/layouts/app.blade.php`

## 211. Nice-Select — Reverted Unnecessary Handlers

Scroll and document-level Esc handlers added to `nice-select.js` were removed (click-only behavior preferred).

## 212. Tooltips — Hidden on Touch/No-Hover Devices Only (CSS Approach)

**Decision:** Tooltips stay on desktop/laptop (hover-capable devices); hidden on mobile/tablet where there is no hover. Implemented purely via CSS to cover both Bootstrap `.tooltip` and jQuery-UI `.ui-tooltip`.

**Files changed:**
- `public/assets/front/css/style.css:5730-5736` — added `@media (hover: none) { .tooltip, .ui-tooltip { display: none !important } }`
- `resources/views/layouts/app.blade.php:354-369` — restored `title` attributes on mobile header auth buttons (Login/Register)
- Shop + categories grid/list buttons keep `data-bs-trigger="hover"` and `tip.disable()` on `(hover: none)` devices (see #207)

## 213. Wishlist Pagination

**Files changed:**
- `app/Http/Controllers/DashboardController.php:210` — `wishlist()` now uses `paginate(9)->withQueryString()` instead of `get()`
- `resources/views/user/wishlist.blade.php` — added `.pagination-wrapper` block with `$wishlist->links()` behind `@if(method_exists($wishlist,'links') && $wishlist->hasPages())`

## 214. Footer Settings — Admin Manager (Like Header Settings)

Storefront footer columns are now configurable from Admin. Columns stored as JSON in the `footer_columns` setting; empty/invalid setting falls back to the original static footer design (verified byte-for-byte).

**Files changed/created:**
- `routes/web.php:373-376` — `admin.settings.footer` (GET) + `admin.settings.footer.update` (POST)
- `app/Http/Controllers/AdminController.php:340` `footerSettings()` + `:346` `updateFooterSettings()` — validates/sanitizes column types (`links`, `newsletter`, `contact`), spans (`2,3,4,6,12`), stores JSON via `Setting::set('footer_columns', ...)`
- `resources/views/admin/settings/footer.blade.php` — **NEW** column editor: add/remove/reorder columns (Sortable), per-column type/heading/span/links
- `resources/views/admin/partials/sidebar.blade.php` — "Footer Settings" link under Settings
- `resources/views/partials/footer-columns.blade.php` — **NEW** footer renderer with default fallback
- `resources/views/layouts/app.blade.php` — footer row now `@include('partials.footer-columns')`
- `public/assets/front/css/style.css` — `.footer-link-col` 50% width on mobile rule

## 215. Categories List-View — Subcategory Names Fix

**Root cause:** JS toggled class `categories-list-view` on `#categoryContainer` but CSS rules targeted `#categoryContainer.list-view`, so subcategory names never showed in list view.

**Files changed:**
- `public/assets/front/css/style.css:16244-16274` — replaced `#categoryContainer.list-view` → `#categoryContainer.categories-list-view` (and `:not(.list-view)` → `:not(.categories-list-view)`)

## 216. Categories Page — Product Preview in List View (3 Products with Image + Price)

Category cards in list view now show up to 3 product thumbnails with name + price (strikethrough old price) on the right side.

**Files changed:**
- `app/Http/Controllers/CategoryController.php:104-108` — `preview_products` = latest 3 active products across all descendant category IDs per category
- `resources/views/categories.blade.php` — card restructured into 3 divs: `.category-image`, `.category-content`, `.category-products`; preview block uses `imgTag()` + `currency_format()`, links to `route('product', $slug)`
- `public/assets/front/css/style.css` — `.category-products` flex column (270px, left border) shown only under `#categoryContainer.categories-list-view`, hidden in grid; product item/image/name/price styles

Verified: 14 categories × 3 products = 42 preview items render on `/categories` (HTTP 200); card = 3 top-level divs.

## 217. Shop Page — Active Filter Chips with Clear Icons

When a sort or brand filter is active, a chip row ("Active filters:") appears below the toolbar showing each active filter with an ✕ clear icon.

**Files changed:**
- `resources/views/shop.blade.php` — added `@php` label maps + chips markup after `.product-nav-wrapper`; chip ✕ uses `request()->fullUrlWithQuery(['sort' => null])` / `['brand' => null]` so clearing one filter preserves all other params; `.filter-chip` styles added to the page `<style>` block

**Behavior (verified):**
- `?sort=price_asc&brand=bosch&year=2020` → chips "Sort by: Price: Low to High ✕" + "Brand: Bosch ✕"
- Sort ✕ → `/shop?brand=bosch&year=2020`; Brand ✕ → `/shop?sort=price_asc&year=2020`
- No chips on plain `/shop`; `sort=default` never shows a chip

## 218. Categories Page — Force Grid View Below 767px

**Update to #209:** Categories page now forces grid view when `window.innerWidth <= 767` (was 580) — on load and resize — even if list view is saved in localStorage. View toggle buttons are hidden below 767px too.

**Files changed:**
- `resources/views/categories.blade.php:196` — `forceGridOnMobile()` breakpoint 580 → 767
- `resources/views/categories.blade.php` — `.category-toolbar .view-btn` hidden under `@media (max-width: 767px)`

## 221. Header Settings — Pages/Posts Nav Link Management (25 Aug 2026)

Header Settings admin page now loads published Pages and Blog Posts as nav link targets. The `headerSettings()` controller method passes `$pages` (active `Page` records) and `$posts` (published `Blog` records) to the view, enabling admins to link header navigation items to any internal page or blog post.

**Files changed:**
- `app/Http/Controllers/AdminController.php:220-228` — `headerSettings()` loads `Page::where('status', true)` and `Blog::where('status', 'published')`, passes both as `$pages` and `$posts` to view
- `resources/views/admin/settings/header.blade.php` — nav link editor now has dropdowns for selecting pages/posts as link targets; JS `wp-setting-url` getter falls back to `.wp-setting-url-locked` text when input is empty

## 222. Shop & Category Pages — Dynamic Scoped Price Range & Fractional Rounding Fix (25 Aug 2026)

Price range slider bounds (`sliderMin` and `sliderMax`) are now calculated dynamically per category, subcategory, childcategory, brand, vehicle, or search scope. Furthermore, decimal price handling was fixed so upper rounding doesn't filter out products.

**Files changed:**
- `app/Http/Controllers/ShopController.php`:
  - Updated `getSharedData($productsQuery = null)` to calculate `minProductPrice` and `maxProductPrice` dynamically based on the passed base scoped query (excluding price filter parameters).
  - Updated `index()`, `customerProducts()`, `category()`, and `subcategory()` action methods to pass the cloned base query to `getSharedData()`.
  - Added optional `bool $applyPriceFilter = true` parameter to `filterProducts()` helper.
  - Adjusted `max_price` query filter with an `effectiveMax` calculation (`(float)$maxPrice + 0.999999`) to ensure integer slider limits (e.g. `4`) correctly include float prices up to that ceiling (e.g. `4.42`).
- `resources/views/shop/index.blade.php`:
  - Updated `$sliderMax` calculation to `(int) ceil(($maxProductPrice ?? 1000) * $rate)` so upper bound includes fractional prices without hiding matching items.

## 223. Footer Settings Page Redesign — Match Header Menu Manager Theme (25 Aug 2026)

Footer Settings page redesigned to match Header Menu Manager theme — same card styles, action buttons (circular SVG), color scheme, drag handles, and form styling. Applied to footer columns (links/newsletter/contact types).

## 224. Footer Settings — Social Icons for Newsletter Column Type (25 Aug 2026)

Added Social Icons to Newsletter column type — admin can now add social platform dropdown (Facebook, Instagram, Twitter, LinkedIn, YouTube, WhatsApp, Pinterest, TikTok, Telegram) + URL rows under newsletter column. Frontend renders social icons under newsletter form.

**Files changed:**
- `resources/views/admin/settings/footer.blade.php` — added social platform/url repeater fields for newsletter column type
- `resources/views/partials/footer-columns.blade.php` — renders social icons under newsletter form using platform-specific SVG icons
- `app/Http/Controllers/AdminController.php` — updated validation and save logic for social icons

## 225. Footer Settings — Controller Save Logic Fix (25 Aug 2026)

Fixed Controller save logic — added 'social' type to allowedTypes and proper platform+url saving for newsletter type links.

**Files changed:**
- `app/Http/Controllers/AdminController.php` — `updateFooterSettings()` added 'social' to allowedTypes array; newsletter column now saves social array with platform+url pairs

## 226. Footer Settings — Admin JS Fixes (25 Aug 2026)

Fixed Footer Settings admin JavaScript:

**Files changed:**
- `resources/views/admin/settings/footer.blade.php`:
  - `rebuildFooterJson()` now correctly reads `.footer-social-platform` + `.footer-social-url` for newsletter type
  - Type change handler properly rebuilds card content (removes old blocks, adds correct new blocks)
  - `initFooterTooltips()` helper added for dynamically created `.footer-remove-btn` elements

## 227. Admin Layout — Tooltip Fallback Selector Fix (25 Aug 2026)

Added `.footer-remove-btn[title]` to fallback selector in admin layout for tooltip initialization on dynamically created elements.

**Files changed:**
- `resources/views/admin/layouts/app.blade.php` — updated tooltip initialization selector

## 228. Backend CSS — Tooltip Custom Theme (25 Aug 2026)

Added tooltip custom theme to backend.css with `--bs-tooltip-*` CSS variables for consistent dark theme (#1f0300) across admin.

**Files changed:**
- `public/assets/front/css/backend.css` — added `--bs-tooltip-bg`, `--bs-tooltip-color`, `--bs-tooltip-opacity`, `--bs-tooltip-arrow-color` variables for dark theme tooltips

## 229. Fixed Per_Page Dropdown Bug Across All 17 Admin Pages (26 Aug 2026)

Fixed per_page dropdown bug across all 17 admin pages — when changing items per page, the 'page' query parameter was not reset, causing empty pages when on the last page with a different per_page value. Added 'page => null' to fullUrlWithQuery() in all per_page dropdowns.

**Files changed:**
- Admin index views: orders, products, categories, brands, users, customers, staff, sellers, coupons, faqs, pages, blogs, blog-categories, contacts, home-page, revisions, file-revisions

## 230. File Revisions Truncation System (26 Aug 2026)

File Revisions truncation system — added truncateDiffs() to FileRevision model (compresses old diffs to 200 chars), --truncate-diffs flag to FileAuditCommand, daily scheduled truncate at 3 AM via routes/console.php, POST route + controller method for admin manual truncate, collapsible Maintenance panel in admin file-revisions UI. No rows or backups deleted — only diff text compressed to save ~270 MB DB space.

## 231. File Revisions per-file limit truncate (26 Aug 2026)

Added truncatePerFileLimit() to FileRevision model using ROW_NUMBER() window function, --per-file-limit CLI option, admin UI dual-panel Maintenance (per-file limit + age-based), per-file-limit route + controller. Fixed routes/console.php (Artisan::command → Schedule::command). Ran immediately: DB 342 MiB → 94 MiB (72% reduction). Rows preserved (12,185), backups preserved, only diff text compressed.

## 232. Meta-tags partial (26 Aug 2026)

Created partials/meta-tags.blade.php with title, meta_title, meta_description, OG tags, robots support. Updated layouts/app.blade.php and admin/layouts/app.blade.php head sections to use @yield('meta_tags'). Updated all 35 frontend views and 55 admin views to use the new partial. Added page-attributes to 4 missing files (dashboard layout, admin dashboard, admin users index/form). All 88+ views now have proper body IDs, classes, and meta tags.

## 233. CSS page-ID audit (26 Aug 2026)

Verified all CSS selectors using page IDs/classes. Found 1 body-level rule (body.user-reviews-page.modal-open), 0 body ID selectors, 3 dead CSS selectors (.cart-page-qty, .product-page-bg, .product-page-product-nav-slider), 1 naming inconsistency (.user-order-page singular vs user-orders-page plural body class). All component-level CSS selectors already work correctly with the body ID/class system.


## 234. Page-ID consistency across all pages (26 Aug 2026)

Created DB page entries for Categories (id=29), Brands (id=30), Cart (id=31), Compare (id=32) in pages table. Updated CategoryController, BrandController, CartController, CompareController to fetch Page model and pass $page to views. All frontend pages now get page-{id} prefix in body tag (e.g. page-29 categories-page, page-30 brands-page, etc.). Verified 13 CMS pages + 4 new non-CMS pages all show proper page IDs.

## 235. Auto page-ID resolution (26 Aug 2026)

Updated page-attributes.blade.php to auto-resolve $page from DB by route slug (route name first segment or URL segment 1). New pages/posts automatically get page-{id} in body tag when added to pages table with matching slug. Existing controller $page passed for meta tags. Verified all 15+ pages get page-{id} including blog show pages (blog.show route -> blog page id=23).

## 236. Content-specific page IDs for blog posts and products (26 Aug 2026)

Updated page-attributes.blade.php to use model-specific IDs for detail pages: blog posts get blog-{id} (e.g. blog-1), products get product-{id} (e.g. product-63). Blog index keeps page-23 (CMS page). Auto-resolution works via route name detection (blog.show -> blog model, product.show -> product model). All pages verified: blog index page-23, blog posts blog-1/blog-2, product product-63.

## 237. Global site-root class + detail page classes (27 Aug 2026)

Added site-root class to body tag in both frontend and admin layouts. Added detail-post class to blog post detail pages and product-detail class to product detail pages via page-attributes partial.

## 238. Canonical redirects for /page/{slug} (27 Aug 2026)

Added blog, categories, brands, cart, compare to page-builder.php live_url_map config. /page/blog now 301 redirects to /blog, etc. Fixed config cache issue.

## 239. Category detail IDs and classes (27 Aug 2026)

Added category-{id} ID and detail-category class for both product categories (route: category/{slug}, variable: $currentCategory) and blog categories (route: blog/category/{slug}, variable: $category) in page-attributes partial. Verified: product category gets category-57 + detail-category, blog category gets category-2 + detail-category.

## 240. Product Page — Seller Sidebar SVG Icons Redesign (31 Aug 2026)

Redesigned the product page seller sidebar (.shop_details_sidebar_store) metrics list in resources/views/product/show.blade.php. Replaced plain text bullets with inline SVG icons (Positive Seller Ratings star-badge, Ship on Time box, Chat Response Rate chat-bubble). Updated the list-item CSS: padding-left, flex-wrap, gap, capitalized text, white-space wrap, and svg positioning (absolute left, stroke: var(--primary)). Also added .shop_details_sidebar_store ul li spinner-bullet ::after removal.

## 241. Product Gallery — Swiper Navigation Arrows (31 Aug 2026)

Added prev/next navigation buttons to the product detail gallery thumbnail slider (.details_slider_nav) in resources/views/product/show.blade.php. Styled .details_slider_nav button (40x40, white bg, shadow, primary color arrow, primary hover with white arrow). Wired navigation via Swiper config: nextEl '#productGalleryNav .swiper-button-next', prevEl '#productGalleryNav .swiper-button-prev'.

## 242. Admin Home Page Edit — Action Buttons Alignment (31 Aug 2026)

Updated resources/views/admin/home-page/edit.blade.php Save/Cancel button row: changed .form-group wrapper to d-flex action-buttons, and set buttons to display:flex with 10px gap (icon + text inline centered) for consistent alignment.

## 243. Admin Layout — Removed noindex (31 Aug 2026) + CSS Tweak

Removed the <meta name="robots" content="noindex, nofollow"> tag from resources/views/admin/layouts/app.blade.php. In public/assets/front/css/style.css adjusted .form-group .form-control padding (12px 20px -> 8px 20px) and added padding-top to .shop_details_sidebar at the 1399px breakpoint.

## 244. Product Page Bug Fixes — Fake Data Removal (31 Aug 2026)

Fixed several product detail page (resources/views/product/show.blade.php) issues:
- **Seller sidebar hardcoded/fake data removed**: replaced hardcoded "Genius Store" + fake metrics (4.5 (320), Ship on Time 100%, Chat Response Rate 90%) with real seller data (name, location, followers_count, products_count). Seller box now hides entirely if no seller. ProductController::show now eager-loads the seller relation with product count (->with(['seller' => fn($q) => $q->withCount('products')])).
- **Fake SKU removed**: hardcoded "SKU: WB44721Fdq{{id}}" line removed (no real SKU field existed); Category li wrapped in isset guard.
- **Share links fixed**: icons pointed to generic facebook.com/twitter.com/etc. homepages; now use real share URLs (Facebook sharer, Twitter intent, LinkedIn share-offsite, WhatsApp send) with the current product URL + name.
- **Lorem ipsum fallback removed**: empty description previously rendered fake Latin text; now shows clean "No description available" message.
- **Admin noindex restored**: re-added <meta name="robots" content="noindex, nofollow"> to admin layout to prevent admin pages being indexed.

## 245. Admin Products — Seller Column + Import/Export Support (31 Aug 2026)

- **Admin products list**: added a "Seller" column (header + row cell `$product->seller->name ?? 'N/A'`) after Category in resources/views/admin/products/index.blade.php. Controller `index()` now eager-loads the `seller` relation for both active and trashed queries. Empty-state colspan updated 12 -> 13.
- **Import**: added `seller` to the recognized CSV columns in ProductController::import. Seller is matched by name and auto-created if missing (with unique slug), tracked via `$sellersCreated` counter and reported in the success message. `seller_id` added to insert/update data.
- **Export**: added `seller` column to exportCsv (header + per-row `$p->seller->name`), eager-loading the seller relation.
- **Sample CSV**: added `seller` column header + sample values (e.g. "AutoZone Seller").
- **Import view docs**: added `seller` (auto-created if missing) to the supported-columns help text.

## 246. Uncommitted changes — Contact replies, product import, admin UI (31 Aug 2026)

### 1. Contact Inquiry — Admin Reply System + User "My Inquiries" Page
- `database/migrations/2026_08_31_000000_add_reply_to_contacts_table.php` — **new** migration: adds `reply` (text), `replied_by` (nullable bigint), `replied_at` (nullable timestamp) to `contacts`.
- `app/Models/Contact.php` — added `reply`, `replied_by`, `replied_at` to `$fillable`; `replied_at` cast to `datetime`; new `replier()` belongsTo(User, 'replied_by') relation.
- `app/Http/Controllers/Admin/ContactController.php` — new `reply()` method (validates `reply`, saves reply + `replied_by` + `replied_at`, redirects back with success); `show()` now eager-loads `replier`.
- `routes/web.php` — `POST /admin/contacts/{id}/reply` → `admin.contacts.reply`; `GET /user/inquiries` → `user.inquiries`.
- `resources/views/admin/contacts/index.blade.php` — added "Status" column with Replied (green) / Pending (amber) badges.
- `resources/views/admin/contacts/show.blade.php` — new "Reply to Inquiry" card: shows current reply + replier name/date, textarea (Send/Update Reply), Email button.
- `app/Http/Controllers/DashboardController.php` — new `inquiries()` method (user's own contacts, eager-loads product + replier, paginate 20).
- `resources/views/user/inquiries.blade.php` — **new** "My Inquiries" user page (subject, date, status badge, linked product, message, green reply box with replier/date, pagination).
- `resources/views/user/layouts/sidebar.blade.php` — "My Inquiries" nav link with active state via `user.inquiries`.

### 2. Product CSV Import — policy/features/reviews support
- `app/Http/Controllers/ProductController.php` — new static `normalizeImportedProductData()`: normalizes `policy_text`/`buy_return_policy`/`return_policy`, `features`/`feature_list` (newline or `||` split), `reviews`/`reviews_data` (JSON array or `name::rating::text` pipe format; clamps rating 1-5; defaults `deleted`/`rating`/`name`).
- `import()` — added the new columns to `$expected`; `seller` matched by name and auto-created if missing; stores `policy_text`, `features`, `reviews_data` on insert/update.
- `exportCsv()` / `downloadSampleCsv()` — headers updated; sample rows include policy + reviews JSON data.
- `resources/views/admin/products/import.blade.php` — supported-columns help doc for policy/features/reviews columns; button row cleanup.

### 3. Admin button alignment fixes
- `resources/views/admin/coupons/edit.blade.php`, `admin/customers/edit.blade.php`, `admin/sellers/edit.blade.php` — action row `.mt-4` → `d-flex gap-*`; Cancel buttons now `steve-btn`.
- `resources/views/admin/home-page/edit.blade.php` — Cancel button gets `steve-btn`; removed hard line-breaks from section detail labels (Section Name / Order / Status / Joined / Last Updated).

### 4. Admin orders + products list tweaks
- `resources/views/admin/orders/index.blade.php` — Action header `d-flex justify-content-end` class removed.
- `resources/views/admin/products/index.blade.php` — product name truncated via `Str::words($product->name, 3, '...')` with `admin-product-name` cell class.
- `public/assets/front/css/style.css` — added `.gs-dashboard-user-sidebar-wrapper li a i` sizing (15px) and `td.admin-product-name a` line-clamp; `.table-action-col .action-buttons` justify changed `flex-end` → `flex-start`.

### 5. Admin profile — avatar initial fallback
- `resources/views/admin/profile.blade.php` — empty-avatar placeholder wrapped in `admin-avatar-80` / `admin-avatar-initial` div for better sizing.


## 247. Order/Product Status Badges — Soft Pill Unification (Admin + User) (31 Aug 2026)

Converted hard flat status badges to soft pill badges across admin and user views for a consistent look, matching the Orders list style.

- **User order detail** (`resources/views/user/orders/show.blade.php`): header + payment badges → `badge badge--*` soft pills; view cache passed.
- **Admin orders** (`admin/orders/index.blade.php`, `admin/orders/show.blade.php`): status badges → soft `bg-light text-* border border-*-subtle` pills; `php artisan view:cache` passed.
- **Admin product detail** (`admin/products/show.blade.php`): Status/Badge/Featured → soft pills.
- **Revision + file-revisions** (`admin/revisions/detail.blade.php`, `admin/file-revisions/diff.blade.php`, `admin/revisions/index.blade.php`, `admin/file-revisions/index.blade.php`): fallback badges → soft pills.
- **Rejected**: clickable/cycle status badge for orders (user wanted simple). A controller `cycleStatus()` and route `admin.orders.cycle-status` were added then fully reverted/cleaned.

## 248. User Inquiries — Edit/Delete + Reply Card Redesign (31 Aug 2026)

- `app/Http/Controllers/DashboardController.php` — new `updateInquiry()` / `destroyInquiry()` (edit on pending inquiries only, reply null; message-body-only edit preserving Product/Product URL prefix).
- `routes/web.php` (line 591, group prefix `user.`): `user.inquiries.update` / `user.inquiries.destroy`. (The `RouteNotFoundException` bug was fixed — view originally used `inquiries.*` links but the group is named `user.`.)
- `resources/views/user/inquiries.blade.php` — Edit + Delete buttons (pending only), inline toggle form + `toggleInquiryEdit()` JS, quill-style soft reply card redesign (gradient card + avatar circle).

## 249. Product Description — Markdown Rendering (31 Aug 2026)

- `resources/views/product/show.blade.php` + `resources/views/layouts/app.blade.php` + new local `public/assets/front/js/marked.min.js` (v9.1.6, 36KB) — description wrapped in `.product-description-body`; DOMContentLoaded script runs `marked.setOptions({breaks:true,gfm:true})`, converts `•`→`- `, renders `el.innerHTML = marked.parse(raw)`.
- Duplicate Key Features `<ul>` hidden when description already contains "Key Features"/bullets (`$hasFeaturesInDesc`); scoped CSS added; Node-verified `**bold**`→`<strong>` and `•`→`<ul><li>`.

## 250. MySQL No-Password Recovery — App DB Connection Fixed (1 Sep 2026)

Fixed the app failing to connect to MySQL (originally `SQLSTATE[HY000] [1698] Access denied for user 'root'@'localhost' (using password: NO)` on the `cache` query). Final working state: **MySQL root uses `mysql_native_password` with an empty password and the app connects over TCP with no password.**

- **Root cause of the original error**: MySQL root was set to `auth_socket` (passwordless, OS-socket-only login). The app connects over `127.0.0.1:3306` as the Apache `www-data` user, which `auth_socket` rejects → the `1698` "Access denied (using password: NO)".
- **Recovery blockers encountered**: a stray `--skip-grant-tables --skip-networking` mysqld (PID stuck) held the InnoDB `ibdata1` lock; AppArmor's mysqld profile (`signal=kill ... peer="snap.code.code"`) blocked all process signals/kills from the snap-confined shell, and the AppArmor profile itself couldn't be switched to complain mode from the confined context (`aa-complain`/`apparmor_parser -r -C` unavailable/denied). The `/var/run/mysqld` socket dir kept disappearing → `2002 Connection refused` during recovery.
- **Fix**: once a fresh mysqld came up on 3306, set `root`→`mysql_native_password` with empty hash (verified via TCP login with no password). Cleared `DB_PASSWORD=Jasa@123` from `.env` down to `DB_PASSWORD=` (empty). Ran `php artisan config:clear`.
- **Verification**: the previously-failing `cache` query via `php artisan tinker` returns `1`; `curl -s -o /dev/null -w "%{http_code}" http://localhost/stautoparts/login` → **HTTP 200**; no SQLSTATE/exception markers in the login page output.
- **Note**: a dedicated non-root `stautoparts` MySQL user (empty password) from a prepared `init.sql` was never persisted because the safe-mode `--skip-grant-tables`/`--init-file` attempts couldn't apply cleanly amid the restart chaos. Root-with-no-password is the current, working config.

## 251. Product Description Cleanup — HTML→Markdown Conversion (1 Sep 2026)

Converted the only 2 products that stored HTML in `description` to clean Markdown. All 808 products now have pure Markdown (no HTML tags).

**Products fixed:**
- ID 115 — "Cast Iron Exhaust Manifold" — had inline `<strong>`, `<i>`, `<a>` tags → converted to `**`, `*`, `[text](url)`
- ID 790 — "ACDelco Professional Serpentine Belt" — had `<p>`, `<br>`, `<span style="...">` block tags → converted to plain text with line breaks, styles removed

**Files changed:**
- `clean_desc.php` (temp script) — dry-run HTML→Markdown conversion using DOMDocument + regex rules (`<p>/<br>`→newlines, `<strong>/<b>`→`**`, `<i>/<em>`→`*`, `<a>`→`[text](url)`, `<span style>` stripped, `•`→`- `)
- `apply_desc.php` (temp script) — applied conversions to DB, verified via `node` + `marked@9.1.6` render before/after
- Both temp scripts deleted after verification

**Verification:**
- `grep -r '<p><br>' storage/app/public/` — 0 matches in description body
- `grep -r '<span' storage/app/public/` — 0 matches
- All 808 products: `description` column contains zero HTML tags

## 252. Admin Sidebar — Revisions Link Active State Fix (1 Sep 2026)

Fixed "Revisions" sidebar link not highlighting on the Revision Detail page.

**Root cause:** `resources/views/admin/partials/sidebar.blade.php:74` used `request()->routeIs('admin.revisions.index')` — only matched the index route, not `admin.revisions.detail`.

**Fix:** Changed to `request()->routeIs('admin.revisions.*')` — now highlights on both index and detail pages.

**Files changed:**
- `resources/views/admin/partials/sidebar.blade.php:74` — wildcard route match

**Audit note:** Full sidebar audit found 1 other bug — `admin/customers` link at line 85 uses `request()->is('admin/customers*')` which fails under `/stautoparts` subdirectory. Not fixed per user decision ("rahane do").

## 253. Admin Edit User Pages — Email Address Read-Only (1 Sep 2026)

Made Email Address field readonly in all 3 admin Edit User pages to prevent accidental/direct email changes.

**Pages affected:**
1. `admin/users/form.blade.php` (shared create+edit) — edit mode only: `{{ isset($user) ? 'readonly' : '' }}`
2. `admin/customers/edit.blade.php` — always readonly (dedicated edit page)
3. `admin/staff/edit.blade.php` — always readonly (dedicated edit page)

**Behavior:** Field still submits current email value (readonly, not disabled) so validation passes. Controllers unchanged — email remains same unless future OTP/confirmation flow is added.

**Files changed:**
- `resources/views/admin/users/form.blade.php:29`
- `resources/views/admin/customers/edit.blade.php:29`
- `resources/views/admin/staff/edit.blade.php:29`
- `php artisan view:cache` — compiled

## 254. View Cache — Permission Fix for Compiled Views (1 Sep 2026)

Fixed `touch(): Utime failed: Operation not permitted` 500 error on category pages.

**Root cause:** `php artisan view:cache` was run as `jasaram` user, creating compiled Blade files owned by `jasaram:www-data`. Apache (running as `www-data`) couldn't `touch()` mtime during runtime recompile → `Operation not permitted`.

**Fix:**
```bash
php artisan view:clear
chown -R www-data:www-data storage/framework/views
sudo -u www-data php artisan view:cache
```

**Result:** All compiled views now owned by `www-data:www-data`. Runtime recompile works; category page (`/stautoparts/category/belts-and-cooling`) and login page both return **HTTP 200**.

**Note for future:** Always run `view:cache` as the web server user (`sudo -u www-data php artisan view:cache`) to avoid ownership mismatch.

## 255. Admin Login — Password Toggle Icon Outside Input Fix (2 Sep 2026)

Admin login page (`admin/login.blade.php`) showed the eye toggle icon below/outside the password input.

**Root cause:** `.admin-password-toggle` was `position: absolute; top: 38px;` measured from the whole `.admin-password-field` wrapper (label + input stacked). The fixed `top` didn't account for the label height, so the icon landed below the input.

**Fix** (`resources/views/admin/login.blade.php` + `public/assets/front/css/backend.css`):
- Wrapped the input + icon in a new `.admin-password-input-wrap { position: relative; }` div.
- Icon is now positioned relative to just the input: `top: 50%; transform: translateY(-50%); right: 12px;`
- Input gets `padding-right: 40px` so text doesn't run under the icon.

**Result:** Toggle always centers inside the input regardless of label size.

## 256. Unified Login — Removed Separate /admin/login Page (2 Sep 2026)

The site had two login pages: `/login` (customer) and `/admin/login` (admin-only). Admin login should use the single `/login` page.

**Changes** (`routes/web.php`, `app/Http/Controllers/AdminAuthController.php`, `app/Http/Middleware/AdminMiddleware.php`):
- `GET /admin/login` now just redirects to `route('login')` (the route name is kept so `route('admin.login')` references don't break).
- Removed the separate `admin.login.submit` POST route — `AuthController@login` already handles admin roles (routes master_admin/admin/staff to `admin.dashboard`).
- `/admin` guest redirect now goes to `login` instead of `admin.login`.
- `AdminAuthController` logout + unauthorized redirects now point to `route('login')`.
- `AdminMiddleware` redirects updated to `route('login')` for consistency.

## 257. Page Builder Admin — Per-Page Selector + Sortable thead Links (2 Sep 2026)

The Page Builder listing page (`admin/page-builder`) lacked the per-page selector and clickable sortable column headers that all other admin list pages have.

**Changes:**
- `PageBuilderController@index` (package at `/var/www/html/laravel-page-builder/`): added sort whitelist (`sort_by`: id, title, updated_at; `sort_dir`), `per_page` handling (10/20/50/100), query-string preservation on the `LengthAwarePaginator`.
- `index.blade.php`: added the standard "Show N per page" dropdown + "Showing X-Y of Z" line; made `#`, `Title`, `Last Updated` headers clickable via `sortUrl()` / `sortIndicator()` helpers.

**Note:** All other admin list tables (brands, categories, customers, orders, products, staff, users, blogs, pages, coupons, sellers, faqs, contacts, revisions, file-revisions, home-page, blog-categories) already had both features. Image Manager and Logs use non-table UIs, so they were skipped at the time.

## 258. Admin Logs — JSON Parse into Readable, Filterable Table (2 Sep 2026)

The Logs page (`/admin/logs`) displayed raw JSON lines in a dark `<pre>` block.

**Changes** (`app/Http/Controllers/AdminController.php`, `resources/views/admin/logs/index.blade.php`):
- Each JSON line is parsed into a structured `{timestamp, type, message, context}` entry (malformed lines fall back to a plain-message entry).
- Replaced the raw block with a table: `#`, `Timestamp` (formatted `Y-m-d H:i:s`), `Type` (color-coded badge), `Message`, `User`, `Context`.
- Context shown via a collapsible "View" button (pretty-printed JSON) instead of inline.
- Added search box (message/type/timestamp), type-filter dropdown, single combined toolbar (file + type + search share one form so params preserve), and pagination (50/page).
- `SiteChangeLogger` log entries: source of data is `SiteChangeLogger.php` (`{timestamp, type, message, context}` JSON, one per line).

## 259. LogSiteChange Middleware — Was Never Registered in Laravel 13 (2 Sep 2026)

Context column on the Logs page was always blank because `LogSiteChange` (global middleware in legacy `app/Http/Kernel.php`) was **not registered** — Laravel 13 configures middleware in `bootstrap/app.php`, not the legacy Kernel.

**Changes:**
- `bootstrap/app.php`: added `\App\Http\Middleware\LogSiteChange::class` via `$middleware->append([...])` (global stack).
- `app/Http/Middleware/LogSiteChange.php`: skips the logs viewer + `/up` health check to avoid recursive self-logging.
- `app/Helpers/SiteChangeLogger.php`: wraps `file_put_contents` in try/catch so a permission failure can never 500 a request; `@chmod(..., 0664)` after write.

**Also fixed:** web requests hit `Permission denied` writing to the day's log (file owned by `jasaram`, not writable by `www-data`). Fixed with `chmod g+w storage/logs/site-changes/*.log`; the logger's `0664` chmod keeps future files group-writable.

**Result:** Web requests now log rich context: `{route, uri, ip, user_id, status, input_keys, ...}`.

## 260. Logs User Identity — name/email/role in Context + User Column (2 Sep 2026)

Before, log entries only had `user_id`, so you couldn't tell which staff/admin an entry belonged to.

**Changes:**
- `app/Http/Middleware/LogSiteChange.php`: context now includes `user_name`, `user_email`, `user_role` (resolved via `Auth::user()` — not `$request->user()`, because `$request->user()` is resolved before the controller performs login in the same request).
- `resources/views/admin/logs/index.blade.php`: added a `User` column showing name + email (or "Guest" for unauthenticated requests).
- Role badge was initially added as "Admin"/"Staff" (matching navbar mapping), then removed at the user's request — User column now shows just name + email; role is still visible in the expandable context.

## 261. Smart Logging — Admin-Focused Audit Trail (Option A) (2 Sep 2026)

Logging was noisy: every GET request, every public page view, every console boot ("Application booted in console", ~93×/day via `AppServiceProvider`) was logged, drowning out the useful audit data.

**Changes:**
- `app/Http/Middleware/LogSiteChange.php` — now only logs **write actions** (POST/PUT/PATCH/DELETE) that are **admin-relevant**:
  - Admin panel actions (`/admin/*`)
  - Admin logout (`admin.logout`)
  - Successful admin login (`login.submit` when the logged-in user has an admin role)
  - Skips: all GET, 4xx/5xx responses, and customer noise (cart/wishlist/register/checkout/review).
  - New types: `auth` (login/logout), `change` (admin actions).
- `app/Providers/AppServiceProvider.php`: removed the console boot log and its now-unused `use`/`require_once` for `SiteChangeLogger`.
- `app/Http/Controllers/AdminController.php`: type dropdown now built from all entries (before filters); default `type=change` so the page opens showing the audit trail.
- `resources/views/admin/logs/index.blade.php`: added `auth` badge color; "All types" dropdown uses `all` value.

**Verified (tinker):** GET shop → 0 logs; POST cart.add (customer) → 0 logs; PUT admin product (as admin) → 1 log with user identity; login.submit (as admin) → 1 auth log.

## 262. Shop Price Range Slider — Restore jQuery UI (Fix Broken Native Dual-Range) (3 Sep 2026)

The native dual-range `<input type="range">` replacement (written the prior session) was broken: overlapping absolute-positioned inputs + `pointer-events:none`/z-index juggling caused an unreliable design, and the Apply Filter `disabled` toggle didn't fire reliably because the native range events didn't call `togglePriceApply()` in all cases.

**Decision:** User said "bs filhal ye fix kre" — restore the proven working jQuery UI slider (from HEAD commit) rather than the lightweight-module optimization.

**Changes:**
- `resources/views/shop/index.blade.php`:
  - Restored `@push('jquery-ui-css')` + `@push('jquery-ui-js')` loading local `jquery-ui.css` + `jquery-ui.js`.
  - Removed the native `.price-dual-range` CSS block (custom gradient track + webkit/moz thumbs).
  - Restored the empty `<div id="price-slider" class="mb-3" style="margin-top:15px;">` markup and the original PHP price bounds block.
  - Restored the original jQuery UI slider JS: `$("#price-slider").slider({range:true,...})`, `isDefaultPriceRange()`, `togglePriceApply()`, clean-URL submit + clear handler.
- `resources/views/layouts/app.blade.php`: re-added `@stack('jquery-ui-js')` after `jquery.min.js` (had been removed when the native slider was introduced). `@stack('jquery-ui-css')` at line 29 was already present.

**Result:** Shop page is now byte-for-byte identical to HEAD (`git diff HEAD -- shop/index.blade.php` empty). Design + Apply Filter enable/disable working again. Slide loads full local `jquery-ui.js` (Lighthouse ~66K unused-JS on shop returns; lightweight-module optimization deferred).

**Verified:** `view:cache`+`view:clear`; shop HTTP 200; `jquery-ui.js`+`jquery-ui.css` load (correct `defer` order before inline doc-ready script); `#price-slider`, all hidden inputs, and `#apply-price-filter` (disabled default) present.

## 263. File Revisions — Add Per-Row Delete Option (3 Sep 2026)

Admin "File Revisions" list had only View Diff + Download backup per row; no way to delete an individual revision.

**Changes:**
- `routes/web.php`: added `DELETE /admin/file-revisions/{id}` → `admin.file-revisions.destroy` → `AdminController@fileRevisionDestroy` (after the truncate-per-file route).
- `app/Http/Controllers/AdminController.php`: added `fileRevisionDestroy($id)` — deletes the row AND removes its backup file (`storage/file-backups/archive/{backup_path}`) if present, then redirects back with success flash.
- `resources/views/admin/file-revisions/index.blade.php`: added a red trash `btn-outline-danger steve-btn` DELETE form button in the Actions cell (consistent with existing action button style), with a `confirm()` dialog that warns when a backup file will also be removed.

**Verified:** `view:cache` compiles clean; `route:list` shows DELETE route → correct controller. Tested the destroy logic in tinker on a throwaway revision: row deleted and backup file removed. Route uses standard auth/role middleware group (master_admin/admin).

## 264. File Revisions — Add Bulk Delete (3 Sep 2026)

Extends #263 with multi-select bulk delete on the same admin page.

**Changes:**
- `routes/web.php`: added `POST /admin/file-revisions/bulk-delete` → `admin.file-revisions.bulk-delete` → `AdminController@fileRevisionBulkDelete`.
- `app/Http/Controllers/AdminController.php`: `fileRevisionBulkDelete(Request)` — reads `ids[]`, deletes each selected revision row AND its backup file, redirects back with a count flash.
- `resources/views/admin/file-revisions/index.blade.php`:
  - Standalone `#bulk-delete-form` (POST, csrf, hidden `ids[]` container) placed before the table (avoids nested-form issues with the per-row delete forms).
  - Added a checkbox column: select-all checkbox in the header + per-row `bulk-select-row` checkboxes.
  - Toolbar "Delete Selected (N)" button (hidden until rows are checked) submits the bulk form.
  - `@push('scripts')` JS handles select-all toggle, live count, and populating hidden `ids[]` inputs.

**Verified:** `view:cache` compiles; route registered correctly; delete logic tested in tinker (row removed). Same auth/role middleware group (master_admin/admin).

## 265. Revisions — Add Delete + Bulk Delete (3 Sep 2026)

Revisions page (DB `Revision` rows) got the same per-row delete + bulk delete as File Revisions.

**Changes:**
- `routes/web.php`: `DELETE /admin/revisions/{id}` → `admin.revisions.destroy` and `POST /admin/revisions/bulk-delete` → `admin.revisions.bulk-delete`.
- `app/Http/Controllers/AdminController.php`: `revisionDestroy($id)` (delete single, redirect with flash) and `revisionBulkDelete(Request)` (deletes `whereIn('id', ids)`, redirects with count).
- `resources/views/admin/revisions/index.blade.php`: bulk-delete form + checkbox column (select-all + per-row), toolbar "Delete Selected (N)" button + JS, and a `btn-outline-danger` per-row trash DELETE form (consistent with existing `btn btn-sm` action style). Empty-state colspan updated 8→9.

**Verified:** `view:cache` compiles; both routes registered (no conflict with GET `revisions/{id}` detail); delete + bulk logic tested in tinker (ids 2577/2578 removed, count correct).

## 266. Logs — Add Clear Log File Action (3 Sep 2026)

Logs are parsed from JSON-lines files (`storage/logs/site-changes/*.log`) with no stable per-entry ID, so per-line/bulk-line delete is not reliable. Per user decision, added a full-file clear action instead.

**Changes:**
- `routes/web.php`: `DELETE /admin/logs/{file}` → `admin.logs.clear` → `AdminController@logClear`, placed in the `role:master_admin,admin` group (admin-only, not staff).
- `app/Http/Controllers/AdminController.php`: `logClear(string $file)` — `basename()` + `.log` regex + `is_file()` guards (prevents traversal), then truncates the file to empty and redirects back with flash.
- `resources/views/admin/logs/index.blade.php`: "Clear Log File" `btn-outline-danger` DELETE button near the title, shown only when a log file is selected AND the user role is `master_admin`/`admin`, with a `confirm()` warning. Also made the "entries" count hide when no file is selected.

**Verified:** routes registered; tinker test confirmed the file is truncated to 0 bytes and the `.log`/`basename` traversal guard rejects non-log / `../` inputs. Admin-only via `role:master_admin,admin` middleware.

## 267. Logs — Responsive Header + Smart Search/Clear Toggle Button (3 Sep 2026)

Logs page header wasn't responsive on mobile and the filter form overflowed; also had separate Search and Clear buttons.

**Changes (`resources/views/admin/logs/index.blade.php`):**
- **Responsive header**: `card-header` changed to `d-flex flex-column flex-xl-row flex-wrap justify-content-between align-items-start align-items-xl-center` — stacks title + filter form vertically on mobile, identical original row layout on `xl+`. Title block got `flex-wrap align-items-center`.
- **Filter form fields**: each field div now `style="flex:1 1 auto; min-width:170px"` so they wrap into tidy rows on mobile while staying inline on desktop. Search input changed from fixed `width:200px` to fluid `min-width`.
- **Smart Search/Clear toggle (user-chosen)**: replaced the separate Search + Clear buttons inside the search field with one `<button id="logs-search-toggle">` (input-group, inside the search input). Server-side `$hasLogFilter` picks mode:
  - No filter → `btn-primary` + `fa-search` icon, submits the form.
  - Filter active → `btn-outline-secondary` + `fa-times` icon; clicking it clears the search input + resets type to `all`, swaps back to primary/search, then submits (via existing `stAutoPartsLogsSubmit`).
  - `@push('scripts')` JS binds the click handler, updates class/icon/aria/tooltip.

**Verified:** `view:cache` + `php -l` clean; `/admin/logs` returns 302 (auth redirect, no 500); toggle markup/classes confirmed in compiled output.

Bonus fix: earlier ParseError (inline `@if(...)...entries@endif`) resolved by splitting into a multi-line block.

## 268. Trash System + 15-Day Auto-Purge for Revisions, File Revisions & Logs (3 Sep 2026)

Per user decision, deletes no longer immediately remove data. Deletes now send items to a **trash** that auto-clears after **15 days**, with restore / permanently-delete / empty-trash options (and "permanently delete + empty" specifically requested for logs trash).

**Scope:** Revisions (DB `Revision`), File Revisions (DB `FileRevision` + backup files), Logs (file-based `.log` files).

### Revisions & File Revisions (database soft-delete)
- **Migration** `2026_09_03_063808_add_deleted_at_to_revisions_and_file_revisions_table.php`: added `deleted_at` (`softDeletes()`) to `revisions` + `file_revisions`.
- **Models**: added `use SoftDeletes;` to `Revision` + `FileRevision` (backup-file rows keep their backup until force-delete/purge).
- **Index**: `revisions()` / `fileRevisions()` accept `?trashed=1` → `onlyTrashed()`; `$trashed` passed to views.
- **Controller** (`AdminController`): `revisionDestroy`/`revisionBulkDelete` and `fileRevisionDestroy`/`fileRevisionBulkDelete` now soft-delete (backup files preserved on soft delete). Added `*Restore`, `*ForceDelete` (file-rev force also unlinks backup), `*BulkRestore`, `*BulkForceDelete`, `*EmptyTrash` for both.
- **Routes** (`admin.revisions.*`, `admin.file-revisions.*`): destroy, bulk-delete, restore, force-delete, bulk-restore, bulk-force-delete, empty-trash.
- **Views** (`admin/revisions/index.blade.php`, `admin/file-revisions/index.blade.php`): "Trash" toggle button, trash-mode action bar (Restore Selected, Delete Selected, Empty Trash with confirm), per-row restore/force-delete in trash view, updated confirms on normal delete ("move to trash… auto-delete after 15 days"). Bulk JS uses shared `bulkTrashRun()` (sets form action) + `.bulk-count` spans.

### Logs (file-based trash)
- **Controller**: `logClear` now **moves** the `.log` file to `storage/logs/site-changes-trash/` (unique name via `_N` suffix if colliding) instead of truncating. Added `logsTrash()`, `logRestore`, `logForceDelete`, `logEmptyTrash`, plus `logTrashDir()`/`sanitizeLogFile()` helpers (basename + `.log` guard).
- **Routes** (`admin.logs.*`): clear, trash, restore, force-delete, empty-trash — in `role:master_admin,admin` group.
- **View** `admin/logs/trash.blade.php` (new): lists trashed `.log` files (name/size/mtime) with per-file Restore + Permanently Delete and an Empty Trash button; "Back to Logs" link. Logs index header shows Clear ("move to trash") + a "Log Trash" link.

### Scheduled auto-purge
- **Command** `app/Console/Commands/PurgeTrash.php` (`trash:purge --days=15`): force-deletes `Revision` + `FileRevision` trash older than N days (FileRevision force also unlinks its `backup_path` in `file-backups/archive/`), and deletes trashed `.log` files older than N days by mtime.
- **Schedule** (`routes/console.php`): `trash:purge --days=15` → `dailyAt('03:30')->withoutOverlapping()` (after existing `file:audit` at 03:00).

**Verified:** migration applied (columns added, data intact: revisions=2576, file_revisions=12489); soft-delete/restore tested in tinker (trashed row hidden, restore brings it back); log clear→trash→restore file-move flow tested via controller calls; all routes registered (`route:list`); all views render (`view:cache` + dummy paginator render); `php -l` clean on all changed files; `trash:purge` runs (0 items purged with no trash present).

## 269. Admin — Tooltips on Icon-Only Buttons (3 Sep 2026)

Per user request: every admin button that shows **only an icon** (no text label) should have a tooltip. Audited all `resources/views/admin/**/*.blade.php` + `admin/partials/*` for icon-only `<button>`/`<a>` lacking `data-bs-toggle="tooltip"` or `title`.

**(No actual detection script kept — this was a one-off audit.)** Additions:
- `admin/partials/search-form.blade.php:9` — search icon (`fa-search`) → `title="Search"` + `data-bs-toggle="tooltip"` (shared search form reused across many admin pages).
- `admin/partials/search-form.blade.php:19` — clear-filter icon (`fa-times`) → `title="Clear search"` + `data-bs-toggle="tooltip"`.
- `admin/file-revisions/index.blade.php:17` — Refresh (`fa-sync`) → `title="Refresh"` + `data-bs-toggle="tooltip"`.

**Already covered (verified ho so not changed):** Revisions & File Revisions per-row Diff/View/Download/Restore/Delete/Force-delete; Logs trash restore/purge buttons; date-range-filter clear button. Buttons with visible text labels (e.g. "Add X", "Save", "Empty Trash") intentionally left without tooltips since the label is already visible.

Tooltips are auto-initialized in `admin/layouts/app.blade.php` via `[data-bs-toggle="tooltip"]` + `.action-btn[title]` (Bootstrap 5, trigger hover).

**Verified:** `view:cache` compiles clean.

## 270. Admin — Logs Nav Highlight on Trash Page (3 Sep 2026)

On `/admin/logs-trash` (and other logs sub-routes) the **Logs** sidebar item wasn't highlighted while the page was open.

**Root cause:** `admin/partials/sidebar.blade.php` Logs nav-item active check was scoped to exactly one route:
`{{ request()->routeIs('admin.logs.index') ? 'active' : '' }}`
— the trash page uses `admin.logs.trash`, so it never matched.

**Fix:** changed the condition to the wildcard `request()->routeIs('admin.logs.*')`, matching all logs routes (index, trash, restore, force-delete, empty-trash). (Consistent with the existing `admin.revisions.*` / `admin.file-revisions.*` wildcard pattern used by neighbouring Revisions / File Revisions items.)

**Verified:** `admin.logs.trash` registered in `route:list`; `view:cache` compiles.

## 271. Product Buy / Return Policy — Per-Product Unique Auto-Generated Content (3 Sep 2026)

Products were showing "No policy information available." in the "Buy / Return Policy" tab because `products.policy_text` (nullable) was empty/null for all **808 products**.

**User decision:** policy is product-specific → each product should show **unique** policy content. Chose the **view default fallback** approach (no DB bulk update), generating unique content per product from its own attributes via a template.

**Changes:**
- **New partial `resources/views/partials/product-buy-return-policy.blade.php`**:
  - If the product has a custom `policy_text` (set via admin product form) → render it as-is (custom wins).
  - Otherwise auto-generate a styled, product-specific policy block using the product's own attributes:
    - **Subject line** — product name + brand + category + price (`USD n,format`).
    - **Buy / Return Policy** — 30-day return window (matches site `/return-policy` rules).
    - **Badge notes** — `Discounted / Sale item` (badge matching `off|sale|deal|discount`) and `New item` (badge `new`) get their own condition paragraph.
    - **Fitment/Compatibility** — only when `year`/`make`/`model` present (e.g. `2020 / Toyota / Camry`).
    - **Eligibility list** (unused, original packaging, within 30 days), **Refunds**, **Return Shipping** (customer pays, non-refundable, deducted), and a **Return Policy + Contact** CTA.
- **`resources/views/product/show.blade.php`**: policy tab-pane now `@include('partials.product-buy-return-policy', ['product' => $product])` (replacing `{!! $product['policy_text'] ?? 'No policy...' !!}`).

**Verified:** `view:cache` compiles; tinker render test → product without custom policy shows full unique auto-generated block (name/brand/category/price + sale badge + fitment seeded); product with custom `policy_text` shows its own content (custom wins); no DB tables touched (`policy_text` count remains 0). Tab button already hard-codes "Buy / Return Policy" (`show.blade.php:938`).

## 272. Product Detail — "Similar Products To Compare" Table (Moglix-style) (3 Sep 2026)

User wanted the product detail page to show a **side-by-side comparison** of the current product with alternatives from other brands — like Moglix's "Similar Products To Compare" section.

**Changes:**

- **`app/Http/Controllers/ProductController.php` `show()`**: builds `$similar` collection:
  - **Primary match**: same `category_id` + same `year/make/model` fitment (same part, different brands) → orders different brands first.
  - **Fallback**: if < 3 alternatives, fills remaining slots from same `category_id` (different brands prioritized).
  - Limit: **3 alternatives** (total 4 columns including current product, Moglix-style).
  - Passes `$similar` to the view.

- **New partial `resources/views/partials/product-similar-compare.blade.php`**:
  - Renders a **Moglix-style comparison table** only when alternatives exist (`$all->count() > 1`).
  - **Columns**: Current product (fixed, badge "Current Product") + up to 3 alternative products.
  - **Comparison rows**:
    - Product (thumb + name + link, current product gets "Current Product" badge)
    - Brand (`brand.name`)
    - Price (`currency_format`, shows old price if exists)
    - Rating (stars + count from `reviews_data`)
    - Fitment / Vehicle (`year / make / model`, or "Universal")
    - Availability (`stock` → In Stock / Out of Stock badges)
    - Description (truncated 90 chars)
    - Action: **Add to Cart** (same `cart.add` form) + **View Details** link.
  - Responsive `.table-responsive` + Bootstrap table styling; each column has `similar-col` class.

- **`resources/views/product/show.blade.php`**: inserts `@include('partials.product-similar-compare', ['current' => $product, 'similar' => $similar])` **above** "Related Products" section (guarded by `if(isset($similar) && count($similar) > 0)`).

**Matching logic (same part, different brands):**
- Primary: same `category_id` + same `year/make/model` (166 fitment groups with >1 product in DB; e.g. 2018/Hyundai/Elantra cat=65 has Mobil 1, Motorcraft, Castrol, MANN-FILTER).
- Orders: different `brand_id` first → current product's own brand last.
- Fallback to same `category_id` if fewer than 3 alternatives in fitment group.
- Limit: 3 alternatives (4 columns total = current + 3, matching Moglix & existing `/compare` max-3).

**Verified:** `view:cache` compiles; tinker render test → product with fitment alternatives shows 4-column table (current + 3 different-brand alternatives: Mobil 1 / Motorcraft / Castrol / MANN-FILTER) with all rows (Product/Brand/Price/Rating/Fitment/Availability/Description/Action with Add to Cart); product without alternatives returns empty (section hidden); `php -l` clean.

## 273. Admin Pages Edit — Responsive Header Bar + Button Fixes (3 Sep 2026)

**Problem:** Admin page editor header bar didn't wrap on mobile; buttons had inconsistent widths; Cancel button lacked `steve-btn` class; view link was awkwardly placed.

**Files changed:**
- `resources/views/admin/pages/edit.blade.php`:
  - Added `admin-page-builder admin-page-builder-edit` classes to the form wrapper for mobile CSS targeting
  - Header bar: title input changed from `w-60` to `w-auto flex-grow-1`; status+action div changed from `w-40` to `w-auto d-flex align-items-center gap-2 justify-content-end`
  - Status pill gets inline `height: 40px; font-size: 1rem; line-height: 1.2` for consistent sizing
  - Cancel button gets `steve-btn gap-1` class
  - View link moved inside `w-100` div for proper wrapping
  - Page URL link and "Last updated" text reorganized in the header bar

**Verified:** blade compiles; page renders; header bar wraps to multiple rows on mobile.

## 274. CSS Cleanup + Responsive Table Borders + Mobile Page Builder Fix (3 Sep 2026)

**Files changed:**

### style.css — Formatting cleanup + new rules
- Standardized whitespace/indentation across ~40 inline CSS blocks (newsletter-btn, gallery-nav, search icon, font-vw, sidebar, deal-of-day, swiper-disabled, summary-table, admin-product-name, logs-page-table, similar-compare)
- Added `.compare-table>tbody>tr:first-child>td` / `>th` first-row top border (1px solid #dee2e6)
- Added `@media (max-width:767px)` for page builder edit: `.admin-page-builder-edit, .page-header-bar-first` → `flex-direction: column`; `.page-header-bar-first div` → `width: 100%`; `.page-builder-nav` → `position: relative`

### backend.css — Admin table first-row border
- Added `.admin-content .table > tbody > tr:first-child > th/td` top border rule (prevents Bootstrap's default first-row border removal from hiding the top line)

### blog/show.blade.php — Back button icon
- Added `<i class="fas fa-arrow-left"></i>` before "Back to Blog" text

### product/show.blade.php — Stock badge styling + quantity width
- Added `.stock` base class (font-size: 14px, font-weight: 600)
- Added `.stock.in-stock` (green #2e7d32) and `.stock.out-stock` (red #c62828) color variants
- Applied conditional `in-stock`/`out-stock` class to stock `<p>` element
- Quantity input width: 130px → 140px; quantity button width: 35px → 40px

### partials/product-similar-compare.blade.php — Title spacing
- Title div changed from `mb-4` to `pb-20` for consistent bottom padding

## 275. Admin Pages Create + Edit — Complete Responsive Header Bar & Nav (4 Sep 2026)

**Problem:** The admin Page Builder (create + edit) layout wasn't properly responsive. On mobile the fixed 180px `.page-builder-nav` side nav stayed vertical in a narrow column, the horizontal `page-builder` flex row didn't collapse, and the header-bar title/action wrapping was incomplete.

**Solution:** Full, verified responsive layout with two breakpoints.

### Blade (`resources/views/admin/pages/create.blade.php` + `edit.blade.php`)
- Both forms carry `admin-page-builder admin-page-builder-edit` classes on the `.page-builder` wrapper (edit already had it; create now matches)
- Header bar is `page-header-bar flex-wrap` with `page-header-bar-first` title container + `w-auto d-flex justify-content-end` action group (create now matches edit)

### CSS (`public/assets/front/css/style.css`) — replaced incomplete `@media 767px` block with two-coverage breakpoint design

**`@media (max-width: 991.98px)`** (tablet/mobile):
- `.admin-page-builder-edit` → `flex-direction: column; gap: 12px` (nav stacks above content)
- `.page-builder-nav` → full-width horizontal pill bar: `width:100%; position:relative; top:0; display:flex; flex-direction:row; flex-wrap:wrap;` with light `#f8f9fa` bg, border, radius
- `.page-builder-nav .text-muted` ("Pages / New") → `flex-basis:100%` on its own row
- `.page-builder-nav .nav` → `flex-direction:row; flex-wrap:wrap`
- `.nav-link-section` → smaller 0.8rem padding for horizontal chips
- `.page-header-bar-first` → `flex-wrap`; its `> div` children `flex:1 1 auto`; the action group (`justify-content-end`) → `flex-basis:100%` + `justify-content:flex-start` on its own wrapped row

**`@media (max-width: 575.98px)`** (phones):
- `.page-header-bar-first > div` → `flex-basis:100%` (title full-width)
- Action group `gap:8px`, and its `.btn` + `#statusPill` → `flex:1 1 auto` (buttons stretch full width)

**Verified:** CSS braces balanced (3160/3160); `php -l` clean on both blades; `view:cache` compiled OK.

## 276. Admin Home "All Categories" — Select/Edit Which Categories Show (4 Sep 2026)

**Problem:** The home page "All Categories" section always showed every top-level active category (16), ordered by name. There was no way to choose which categories appeared on the home page or control their order.

**Solution:** Admin-driven category selection, mirroring the existing Brands/Posts pattern. The `categories_heading` section now stores a `category_ids[]` array in its `extra_data`; home rendering filters to exactly those categories (in selected order). Empty = show all (previous behavior).

### Admin/HomePageController.php — `update()`
- New branch: `if ($request->has('category_ids'))` → saves `$existing['category_ids'] = array_values(array_filter(array_map('intval', ...)))` (int-filtered, in selection order)
- Added `|| $request->has('category_ids')` to the `$extraChanged` guard

### admin/home-page/edit.blade.php
- Added a `categories_heading` info alert (Title = heading, choose categories below)
- Added a `@if($section->section_name === 'categories_heading')` block:
  - Loads `$allCategories = Category::whereNull('parent_id')->where('status', true)->orderBy('name')->get()`
  - Multi-select `category_ids[]` (size=10) with `$selectedCategoryIds` from `extra_data['category_ids']`, preselecting saved ids
  - Help text: "Hold Ctrl/Cmd ... Leave empty to show all"

### HomeController.php
- `index()` now builds `$sections` before `getCategories()`, passing `$sections->get('categories_heading')`
- `getCategories($categorySection = null)`:
  - Reads `extra_data['category_ids']` (int-filtered)
  - Non-empty → `whereIn('id', $selectedIds)->orderByRaw('FIELD(id, ...)')` (honors selection order)
  - Empty → original behavior (all top-level active, ordered by name)

**Verified:** `php -l` clean on both controllers; `view:cache` compiles; tinker unit-check returns 16 categories (empty), 3 in exact selected order when set, 16 after restore; end-to-end HTTP: home 200 + shows only selected categories, admin edit 200 + renders "Select Categories for Home Page" select. DB `categories_heading` extra_data left empty (shows all). New route/data: none.

## 277. Admin Order Detail — New Design + Payment Status Field (4 Sep 2026)

**Problem:** Admin order detail page (`admin/orders/show.blade.php`) was a basic table layout. The team provided a new design file (`Order-detail-page.html`, Tailwind mockup) with a richer layout (header badges, item thumbnails, totals strip, status-update selects, and side cards for customer/shipping/payment). Also, orders had no separate payment-status concept — only order `status` — so the design's "Payment Status" select had no real field to edit.

**Solution:** Replicated the new design in the existing **Bootstrap 5.3** admin theme (no Tailwind added — would clash with the admin's Bootstrap utilities), and added a real `payment_status` column (unpaid/paid/refunded) end-to-end. Currency via the store's `currency_format()` helper (not the mockup's ₹).

### Database
- New migration `database/migrations/2026_09_04_000001_add_payment_status_to_orders_table.php`: `string('payment_status')->default('unpaid')->after('status')` (drop on rollback). Existing orders default `unpaid`.

### app/Models/Order.php
- Added `'payment_status'` to `$fillable`.

### CartController.php — paymentSubmit()
- New orders set `payment_status` by method: `card`/`paypal` → `paid`, `cod` → `unpaid`.

### app/Http/Controllers/Admin/OrderController.php
- `index()`: `payment_status` added to `sortBy` whitelist + optional `payment_status` filter (unpaid/paid/refunded); passes `paymentStatusFilter` to view.
- `updateStatus()`: accepts optional `status` + `payment_status` (each validated). Persists whichever is present, on the same existing `admin.orders.update-status` POST route.

### admin/orders/index.blade.php
- New **Payment** filter dropdown (All Payments / Unpaid / Paid / Refunded) beside the Status filter.
- New sortable **Payment** column with color-coded badge (paid=green, refunded=gray, unpaid=amber); empty-state colspan updated 6→7.

### admin/orders/show.blade.php (rewritten)
- **Header**: Back link, `Order #number` + order-status badge + payment-status badge + "Placed on" date; right = **Print Invoice** button (`admin.orders.invoice`, target `_blank`).
- **Left (col-lg-8)**: Order Items card (product thumbnail via `storedImageUrl`, name, price, qty, line total; empty state) + totals strip (Subtotal, Shipping Fee, optional Tax, Total) + **Update Order Status** card with Order Status + Payment Status selects (one form → `update-status`).
- **Right (col-lg-4)**: Customer Details card (avatar initials, name, since-year, email, phone), Shipping Address card (from decoded `shipping_details` JSON), Payment Info card (Method humanized; transaction row only when `payment_details` present).
- Currencies through `currency_format()`; missing order thumbnails fall back to global placeholder img-onerror.

**Verified:** `php -l` clean (Order model, CartController, Admin OrderController, both blades); `php artisan migrate` adds column (existing orders → `unpaid`); `view:cache` compiles; HTTP 200 on orders list (Payment filter + sortable header + Paid/Unpaid badges render; `?payment_status=paid` filters correctly) and order detail (order #, both selects, Print Invoice, Shipping Address, Payment Info all render); controller `updateStatus` persists `payment_status` (set → paid, restored → unpaid). No new routes added (reused existing show/update-status/invoice). Imported `Order-detail-page.html` is the design reference only (left in repo root, no longer auto-read by any view).

## 278. Product Detail — "Similar Products To Compare" Responsive (Sticky Row Labels) (4 Sep 2026)

**Problem:** On the product detail page, the "Similar Products To Compare" comparison table (`partials/product-similar-compare.blade.php`) is a wide table where each product is a column and attributes (Brand, Price, Rating, Fitment, Availability, Description, Action) are rows. On mobile it relied only on Bootstrap's `.table-responsive` horizontal scroll, so the row-label column ("Product", "Brand", "Price", …) scrolled away out of view — making rows unreadable.

**Solution:** Made the row-label column sticky so it stays pinned on the left while the wide table scrolls horizontally inside `.table-responsive`. Added a `min-width` so the table keeps its columns readable instead of squeezing on narrow screens. (Kept as a table — not converted to a slider, since comparison tables pair attributes per-row.)

### public/assets/front/css/style.css
Added a block after the `.gs-similar-compare-area` styles:
- `.gs-similar-compare-area .table-responsive` → `-webkit-overflow-scrolling: touch` for smooth mobile scroll
- `.compare-similar-table` → `min-width: 720px`
- First-column cell (`> tr > th:first-child` / `> tr > td:first-child`) → `position: sticky; left: 0; z-index: 2; background: #fff; border-right; subtle shadow` so row labels stay visible while scrolling
- `th:first-child` → `min/max-width: 160px; white-space: nowrap; vertical-align: middle`
- `thead th:first-child` → `z-index: 3; background: #f8f9fa` (header sits above row cells)

**Verified:** CSS braces balanced (3166/3166); `view:cache` compiles; product page (e.g. `/product/front-shock-absorber-pair`) HTTP 200 with `gs-similar-compare-area` + `compare-similar-table` present. No blade/controller changes needed.

## 279. Admin Order Detail — Shipping Address Icons (4 Sep 2026)

**Problem:** In the admin order detail page, the "Shipping Address" card content was plain text (no icons), while the adjacent "Customer Details" card had icons for email/phone. The Shipping Address card looked inconsistent.

**Solution:** Added Font Awesome icons next to each shipping-address line (matching the Customer Details card style):
- `fa-user` — recipient name
- `fa-map-marker-alt` — address line
- `fa-building` — city / state / zip
- `fa-globe` — country
Each line is `d-flex align-items-start gap-2` with the icon muted; empty address fields are skipped entirely.

**Files changed:**
- `resources/views/admin/orders/show.blade.php` — replaced the `<p>`-based address block with icon row layout

**Verified:** `php -l` clean; `view:cache` compiles; view renders all four icons (`fa-user`, `fa-map-marker-alt`, `fa-building`, `fa-globe`).

## 280. Admin Order Detail — Transaction ID Only When Paid + Unique (4 Sep 2026)

**Problem (2 logic flaws reported by user):**
1. Transaction ID could be saved even when Payment Status was `unpaid` — a txn ID only makes sense once a payment is actually Paid.
2. The same Transaction ID could be reused across multiple orders — no uniqueness enforcement (found 2 orders sharing `pay_L9xK20sKq` in seed data).

**Solution (App/Controllers/Admin/OrderController@updateStatus):**
- **Paid-only:** A Transaction ID is only persisted when the order's payment_status is `paid` (either being set to paid in this request, or already paid). Attempts while unpaid/refunded are ignored.
- **Unique:** Before saving, checks any OTHER order whose `payment_details->transaction_id` matches; if a duplicate → `back()->withErrors(['transaction_id' => ...])`.
- **One-time (kept):** If this order already has a txn ID, further changes are rejected with an error (can't change/clear).
- Empty txn ID with no existing value = no-op (never saved blank).

**Blade (admin/orders/show.blade.php):**
- Replaced the single Transaction ID input with a 3-state group toggled by JS on the Payment Status select:
  - **paid selected + no txn yet** → editable input
  - **txn already exists** → readonly "locked" input + "can only be set once" note
  - **unpaid/refunded selected** → disabled input + "only when Paid" note
- Added `@push('scripts')` toggle JS reading the Payment Status select (initial state set via `txnIdExists`).

**Verified:** `php -l` clean + `view:cache` compiles. Controller test across orders:
1. txn while unpaid → blocked ✓
2. unpaid→paid + txn `TXN_111` → saved, pay=paid ✓
3. duplicate `TXN_111` on another order → rejected ✓
4. change after set → stays `TXN_111` (one-time) ✓
Detail page HTTP 200 with `transactionIdGroup`, toggle JS, and not-paid message present. Test-state orders reset to `unpaid`/null afterward.

## 281. Customer-Facing Coupon Apply in Cart & Checkout (4 Sep 2026)

**Problem:** Admin coupon CRUD existed, but customers had no way to apply a coupon — no cart/checkout input, no validation logic wired up, and orders had no coupon columns (invoice blade referenced a non-existent `coupon_discount`).

**DB / Models:**
- New migration `2026_09_04_000002_add_coupon_fields_to_orders_table` → adds `coupon_code` (nullable string) and `coupon_discount` (decimal, default 0) after `tax`. Ran with `php artisan migrate --force`.
- `Order` $fillable: added `coupon_code`, `coupon_discount`.
- `Coupon` model: added `calculateDiscount(float $subtotal)` (fixed = min(value, subtotal); % = min(subtotal*value/100, subtotal); 0 if invalid/min-order not met).

**Routes (`routes/web.php`):**
- `POST /cart/coupon/apply` → `cart.coupon.apply`
- `POST /cart/coupon/remove` → `cart.coupon.remove`

**CartController:**
- `applyCoupon(Request)`: validates code, expiry, status, capacity, min-order via `Coupon::isValid()` + `calculateDiscount()`, stores `session('coupon')` as `['code','discount','type','value']`, flashes success/error (uses `currency_format()`).
- `removeCoupon()`: clears `session('coupon')`.
- `index()`, `checkout()`, `payment()`: now read `session('coupon')` and pass `couponData`/`couponDiscount` to views; totals subtract the discount.
- `paymentSubmit()`: reads coupon, `grandTotal = max(total + shipping - discount, 0)`, stores `coupon_code`/`coupon_discount` on the Order, increments coupon `used_count` after success, and includes `coupon_code`/`coupon_discount` in the `last_order` confirmation session.

**Blade:**
- `cart/index.blade.php`: coupon input+Apply button (no coupon), or applied-code card with Remove button + Coupon Discount/total rows (coupon set).
- `checkout/payment.blade.php`: shows "Coupon (CODE)" − discount row before Total when applied.
- `checkout/order-confirmed.blade.php`: Coupon Discount row now uses real value and shows the applied code; subtotal calc accounts for coupon.
- `user/orders/invoice.blade.php`: subtotal now re-adds coupon discount so totals line up (referenced `coupon_discount` that previously didn't exist).
- `admin/orders/show.blade.php`: subtotal calc updated + shows "Coupon (CODE) −discount" row when > 0.

**Verified:** `php -l` clean on all edited files; `view:cache` compiles. `calculateDiscount` cases (fixed cap, %, min-order) pass. Controller apply/remove + min-order reject + invalid reject + remove all pass. Full order placement with coupon → order saved with `total_amount=500` (600−100), `coupon_code`, `coupon_discount=100`, `used_count` 0→1. HTTP apply route with CSRF → 302 + coupon stored. Contact/payment/confirm/invoice/admin pages render coupon data.

## 282. Admin Coupon Usage Visibility — Per-Product Tracking + Detail Page (4 Sep 2026)

**Problem:** Admin could only see an aggregate `used_count` per coupon — no way to know WHICH orders/products used a coupon.

**DB / Models:**
- New migration `2026_09_04_000003_add_coupon_discount_to_order_items_table.php` → adds `coupon_discount` (decimal default 0) after `price` on `order_items`. Ran with `php artisan migrate --force`.
- `OrderItem` $fillable: added `coupon_discount`.

**Per-product split (`CartController::paymentSubmit`):**
- When a coupon discount exists, the order's discount is split across each order item proportionally by line-subtotal weight (`round(discount * lineSubtotal / total, 2)`), with the last item absorbing the rounding remainder so per-item discounts sum exactly to the order discount.
- Each `OrderItem::create` now stores its `coupon_discount` share.

**Admin coupon detail page (`admin.coupons.show`):**
- New route `GET /admin/coupons/{id}` → `CouponController@show`.
- `show()`: fetches the coupon plus all orders that used it (`coupon_code = code`), eager-loads `user` + `items.product`, paginates (10/20/50/100), computes aggregate `totalDiscount` and `orderCount`.
- New blade `admin/coupons/show.blade.php`: stat cards (Type/Value, Orders Used, Total Discount Given, Uses Limit) + per-order table showing Order #, Customer, Date, **Products column listing each item with its per-product `-$discount`**, Coupon Discount, Order Total, and a View button → admin order detail.

**Wiring:**
- `admin/coupons/index.blade.php`: coupon detail opened via an eye (`btn-view`) icon link added in the Action column (edit/destroy untouched). Removed the earlier external-link on the Uses cell.
- `admin/orders/show.blade.php`: order items table gained a "Coupon Discount" column showing each product's share (with coupon code under the product name).

**Verified:** `php -l` clean on all edited files; `view:cache` compiles. End-to-end order with 10% coupon on 700 subtotal → order total 630, item splits 60.00 + 10.00 summing to 70 = order discount. Admin coupon detail (HTTP 200) lists the order, real product name, and `-$25.00` per item; admin order detail has the Coupon Discount column and `-$25.00` per item; coupon index renders the view eye button. Test orders/coupons cleaned up.

## 283. Coupon Hardening — Gaps Found & Fixed in Coupon Feature (4 Sep 2026)

**Review of the coupon feature surfaced 3 real gaps (user selected full "Option B" hardening):**

**Gap 1 — User order detail page showed wrong subtotal + no coupon row:**
`user/orders/show.blade.php` computed `$subTotal = $totalAmount - $shippingFee - $taxAmount`, so a coupon-discounted order showed a subtotal that was too LOW and had no "Coupon Discount" line (unlike the invoice/admin pages already fixed).
- Fix: subtotal now adds back `$order->coupon_discount`; added a "Coupon Discount (code) −$x" summary row shown only when discount > 0.

**Gap 2 — Coupon survived after order placement:**
`paymentSubmit()` forgot `cart`/`billing_info`/`shipping_info` on success but never `session('coupon')`, so a stale coupon silently attached to the user's NEXT cart.
- Fix: added `session()->forget('coupon')` alongside the other checkout session clears.

**Gap 3 — Stale / exploitable discount (the "hardening"):**
The discount was computed once at apply-time and never revalidated. Cart changes (`add`, `updateQuantity`, `remove`, `removeSelected`) did not recompute, and `paymentSubmit()` trusted the stored session value — so a user could apply a coupon on a big cart, then shrink quantities (even below `min_order_amount`) and still get the full discount.

**Fix (Option B, full hardening) — new private helper `CartController::recomputeCoupon(array $cart): ?array`:**
- Re-fetches the coupon by code from the DB, re-runs `isValid()` (status, capacity, expiry/start), re-checks `min_order_amount` against the CURRENT cart subtotal, and re-calculates a fresh discount via `calculateDiscount()`.
- Updates `session('coupon')` and returns it; if invalid/now-below-min/discount<=0 it clears the coupon and returns null.

**Wired everywhere:**
- `add()`, `remove()`, `removeSelected()`, `updateQuantity()` → call `recomputeCoupon($cart)` after mutating the cart, so the UI discount stays consistent and invalid coupons drop off.
- `index()`, `checkout()`, `payment()` → totals now come from `recomputeCoupon(...)` so displayed money matches the coupon at all times.
- `paymentSubmit()` → USES `recomputeCoupon($cart)` AS THE FINAL AUTHORITY instead of trusting the stored session discount, guaranteeing every order stores the correct discount against the actual paid-for cart.

**Verified:** `php -l` clean; `view:cache` compiles. Tests: apply on 900 → discount 100; qty→1 (subtotal 300) recomputes to 100; order placed 300−100=200 with `coupon_discount=100`; coupon+cart sessions cleared after order; user order detail (HTTP 200) shows Subtotal 600, Coupon Discount (HRD) −$100.00, Total 500. Min-order coupon correctly rejected below threshold. Test data cleaned up.

## 284. Coupon List — Action Column Reduced to Eye Icon Only (4 Sep 2026)

**Request:** Admin coupon index page (`admin/coupons`) ke Action column me sirf eye (View) icon rakhna tha, Edit aur Delete buttons hata do.

**Change (`admin/coupons/index.blade.php`):**
- Removed the Edit button and the Delete (destroy) form from the row's Action cell.
- Kept only the eye icon link (`btn-view`) which opens the coupon usage detail page (`admin.coupons.show`).

**Note:** Deleting the row-level Edit/Delete buttons means listing no longer offers inline edit/delete. Edit access is still available from the coupon detail page's "Edit Coupon" button (which remains). Inline delete from the list is no longer present.

**Verified:** `php -l` clean; `view:cache` compiles; admin coupon list renders HTTP 200 with btn-view (eye) present and btn-edit / destroy form absent.

## 285. Coupon Detail Page — Order Action Column Converted to Eye Icon (4 Sep 2026)

**Request:** Coupon detail page (`admin/coupons/{id}`) ke orders table ke "Action" column me bhi sirf eye icon rakhna tha, sirf isliye wo "View" text button ko replace kiya.

**Change (`admin/coupons/show.blade.php`):**
- The per-order Action cell previously used `<a class="btn btn-sm btn-outline-primary">View</a>`.
- Replaced with an `action-btn btn-view` eye-icon link (SVG eye), matching the eye-icon convention used in the coupon index (#284) and admin orders list. Tooltip "View Order", opens `admin.orders.show`.

**Note:** This is a different column than the coupon list's Action column (that one opens the coupon detail). Here the eye icon on each order row opens that order's detail page.

**Verified:** `php -l` clean; `view:cache` compiles; `admin/coupons/{id}` renders HTTP 200 with `btn-view` (eye) present and the old "View" text button removed.

## 286. Coupon Detail Page — Show Unit Price in Products Column + Clean Fake Test Order (4 Sep 2026)

**Request:** On the coupon detail page (`admin/coupons/{id}`), the "Products (with coupon discount)" column showed only product name + quantity (and a bogus "Product #1000" fallback for a deleted/test product). User wanted the product price visible too.

**Change (`admin/coupons/show.blade.php`):**
- Each product line now appends the unit price in muted text: `Product Name × 2 @ $300.00`, keeping the `-$coupon_discount` on the right.

**Data cleanup:**
- Found a leftover test order `ORD6A9A9D64D4D1E` (coupon `ORDER10`, total 500, single item product_id=1000 with `price=300`, qty=2) created during earlier coupon testing (task #281). Its product_id=1000 does NOT exist in the products table (max id is 808), which is exactly why "Product #1000 × 2" rendered. Deleted this fake test order + its items so real coupons no longer display a bogus "Product #1000" fallback.

**Verified:** `php -l` clean; `view:cache` compiles; real coupon `EREWREWR` detail page (HTTP 200) shows the real product name, the `@ $price` unit price, and `Product #1000` no longer present.

## 287. Category Admin — Parent Category Dropdown Shows All Categories as a Hierarchy (4 Sep 2026)

**Request:** When adding/editing a category, the "Parent Category" dropdown should let the admin select ANY category (not just top-level) and display them as an indented tree by level (level-1 → level-2 → level-3 → level-4, etc.).

**Changes:**

- `CategoryController::create()` — `$parents` now loads ALL non-deleted categories as a hierarchical tree: `Category::topLevel()->where('is_deleted', false)->with('children.children.children')->orderBy('name')->get()`.
  - Fix: old code used `whereNull('is_deleted')`, but every category row stores `is_deleted = false` (not NULL), so that filter matched **zero** rows and the dropdown was empty. Switched to `where('is_deleted', false)`.
- `CategoryController::edit()` — same tree load, but `whereNotIn('id', $excluded)` on top-level plus a recursive prune in the blade so the current category AND all its descendants (its whole subtree) are removed from the parent list, preventing cycles.
- New partial `resources/views/admin/categories/_parent_options.blade.php` — recursively renders each node's `<option>` with indentation (`&nbsp;` × depth) and a `└` tree marker for non-root levels, then recurses into `$cat->children`.
- `create.blade.php` / `edit.blade.php` — replaced the flat `@foreach` with `@include` of the partial; edit passes `$excluded` (self + descendants) so they are pruned, `$category->parent_id` for `selected`.
- `store()` / `update()` — `parent_id = $request->filled('parent_id') ? $request->parent_id : null;` (unchanged, already normalized to NULL for "Main Category").

**Verified:**
- `php -l` clean on controller + 3 blades; `view:cache` compiles.
- Create page `parent_id` select contains exactly 71 `<option value="N">` (all categories), with `└` tree markers showing hierarchy.
- Edit top-level #64 (`Belts and Cooling`): descendants leaked = 0. Edit sub-category #2 (`Air Intake Systems`): 69 options, self excluded, descendantLeak = none (cycle-safe).
- Save test: sub-category saved with `parent_id = '65'` (top-level parent) persisted; moving back to "Main Category" sets `parent_id = NULL`. Cleanup done, no test rows left.

## 288. Image Restoration — Recover Missing Product Images After Accidental Deletion (4 Sep 2026)

**Incident:** User deleted "unused" images from `storage/app/public/uploads/2026/08/`, but those images were actually in use by 224 active products (main images). Home page showed 33 broken images; full scan revealed 224/807 products missing their main image file.

**Root cause:** The `2026/08` uploads folder contained the current product images. A duplicate backup existed in `2026/07/` (same filenames, different month folder). Deletion removed the active copies.

**Recovery actions:**

1. **213 products fully restored** — copied missing files from `uploads/2026/07/` → `uploads/2026/08/` (exact filename match). 100% success, zero failures.

2. **5 path-correction fixes (DB updates):**
   - P#309 (SKF Pinion Seal): DB had double prefix `storage/uploads/2026/09/...` → fixed to `uploads/2026/09/...` (file existed at correct path).
   - P#67: Same double-prefix fix.
   - P#314, 315, 316 (K&N products): DB pointed to `uploads/2026/07/...` but files actually in `uploads/users/...` — updated DB paths.

3. **7 irrecoverable products (original deleted, only `_250.webp` thumbnails remained):**
   - P#56, 67, 74, 83, 89, 90, 92 — copied `_250.webp` thumbnail as the main image file (fallback). These now show a lower-res but valid image instead of broken icon.

4. **1 additional home page image** (`1730880696Fabpng.png` for P#246 MagnaFlow Y-Pipe) restored from 2026/07 backup.

**Verification:**
- Product scan: **0/807 missing** (was 224).
- Home page: **0/187 images missing** (was 33).
- Product pages (slug URLs): HTTP 200, images render.

**Note:** 7 products now use thumbnail-as-main-image fallback. Original hi-res files are permanently lost; recommend re-upload when possible.

## 289. (2026-09-07): Duplicate image cleanup + modal search improvements**

1. **Avatar fix:** `uploads/users/` was an orphan folder (arrived via backup restore, Aug 12). Restored Jasa (id 9), Purvi (id 2), test (id 7) avatars from `uploads/users/` → correct `uploads/2026/07/` paths; removed orphan folder (moved to /tmp backup).

2. **Image Manager modal search:** now searches DB (`original_name`, `alt_text`, `title`, `filename`) AND scans `uploads/` on disk, so every image (with/without an `images` table row) is findable. Added `filename` to admin images index search too. Auto-generated `_250.webp`/`_500.webp` responsive variants are hidden from results; folder-duplicate copies collapse to one entry. (ImageController.php)

3. **Duplicate cleanup** (`artisan images:cleanup-duplicates`):
   - Detected 1,856 duplicate filename groups (files present in both `2026/07` and `2026/08`).
   - Rewrote 5,632 DB references (565 product images, home banners, page content_blocks, revisions) to a single canonical (kept) copy — mostly `2026/08`.
   - Moved 1,856 duplicate copies (+ variant siblings) to `storage/app/cleanup_trash/dup_cleanup_20260907_060047/` — 75.55 MB freed, reversible via MANIFEST.json.
   - DB backup: `database/stautoparts_dupcleanup_backup_20260907_060042.sql`.
   - Verify: 0 references point to moved files; 0 duplicate groups remain.

4. **Pre-existing broken references (not caused by cleanup):** 319 referenced images still missing on disk (product images pointing to `2026/08` files that never existed in `2026/07` backup, 2 home banners, 1 test.webp). These were broken before this task. If needed, re-upload or restore from earlier DB/file backups.

## 290. (2026-09-07, follow-up): Thumbnail fallback for permanently-lost images**

- 303 referenced-but-missing image rows (media library `images` table, categories, pages, home banners) remapped to their existing `_250.webp`/`_500.webp` respavailableure variant (`artisan images:fallback-missing`, 5,382 references rewritten). DB backup: `database/stautoparts_dupcleanup_backup_20260907_061055.sql`.
- Restored 1 home banner (`home_page_sections#5` → `uploads/2026/08/17821312686a392a448fe5c.png`) from `2026/07` copy.
- 3 avatar `_250.webp` variants referenced in images table fixed (these were soft-delete rows anyway).
- Verified: only ~8 non-variant missing refs remain — all are test/junk media rows already soft-deleted (`is_deleted=1`, no product/gallery pivot), plus `category#70 (Body)` image `1786523970_6a7c3142132bc.png` which has no file or variant anywhere (needs re-upload).

## 291. (2026-09-07): Content-level image consolidation + shared-file delete protection**

- New command `artisan images:consolidate-content` (app/Console/Commands/ConsolidateContentDuplicates.php, extends CleanupDuplicateImages): MD5-hash scan of `uploads/`, groups byte-identical files regardless of filename.
- Dry-run verified 326 groups / 12,161 files / 623.54 MB. Real run rewrote 10,696 `images.path` + product/brand/category/blog/page/user/seller/settings refs to one canonical copy per group, moved 12,161 exact duplicates to `storage/app/cleanup_trash/content_dedup_20260907_091641/` (reversible via MANIFEST.json). DB backup: `database/stautoparts_dupcleanup_backup_20260907_091543.sql`. `uploads/` shrank ~648 MB → 25 MB.
- Canonical = most-referenced copy; ties prefer non-variant original, then newest month. Byte-identical, so no quality loss.
- Regression found + fixed: home banner slider `home_page_sections#5.extra_data` is double-encoded JSON (`\/`), so the path rewrite missed it and the banner file moved to trash. Restored `uploads/2026/08/17821312686a392a448fe5c.png` from trash. Verified: 0 missing refs beyond the 15 pre-existing ones.
- `ImageController::bulkDelete` now keeps (skips unlink) any file still shared by another active image row — required because many rows now point to the same canonical file.
- Residual: 33 leftover 2-file groups (mostly `_250/_500.webp` pairs regenerated on-demand + restored banner) — normal, not referenced-missing.
- Known bug (pre-existing, unrelated): any non-existent URL renders errors/404.blade.php which uses undefined `$page` (`partials.page-attributes`) → 500. commit 53f71c55.

## 292. (2026-09-07): Image Manager counts real unique files**

- `ImageController::index` now counts/displays one entry per unique file path instead of raw `images` rows. After content consolidation 10,635 rows pointed to only ~296 files; the stats grid now shows Total ~296 / Attached ~294 / Unused ~2 (previously 10,635 / 10,634 / 1). Grid pages unique paths (representative = newest row for that path) so the same image no longer repeats; filter=attached/unused uses EXISTS/NOT EXISTS subqueries; total_size sums max size per unique path.

## 293. (2026-09-07): unreferenced files + test.webp cleanup**

- `test.webp` image row soft-deleted (was the last active image row pointing to a missing file; it was unused junk).
- New command `artisan images:cleanup-unreferenced` (CleanupUnreferencedImages extends CleanupDuplicateImages): uses full reference set (simple columns + JSON blobs + double-encoded `home_page_sections.extra_data`) to move files with zero DB references to `storage/app/cleanup_trash/unreferenced_20260907_094156/` — 167 files, 2.37 MB, MANIFEST.json included. DB backup: `database/stautoparts_dupcleanup_backup_20260907_094155.sql`.
- Verified: 0 moved files referenced anywhere in DB; uploads/ now 454 files / ~24 MB.
- Image Manager now: Total 295 / Attached 294 / Unused 1 (logo — kept, used via settings).
- Trash on disk (reversible): content_dedup_20260907_091641 (644 MB) + dup_cleanup_20260907_060047 (80 MB) + unreferenced_20260907_094156 (2.4 MB) + 4 DB backups (~276 MB).

## 294. (2026-09-07): inquiry reply → user notification**

- `ContactController@reply` (admin) sirf record update karta tha; koi notification nahi banta tha.
- Added `NotificationHelper::inquiryReplied(Contact)` → in-app Notification ("Reply to Your Inquiry" + link to `route('user.inquiries')`) for `$contact->user_id`.
- Reply save pe hamesha notify (first-time aur update dono). Guest contacts (user_id null) ke liye in-app notification possible nahi — koi mail nahi bheji (MAIL config incomplete).

- Root cause for "old inquiry reply notification nahi ja rahi": zyada tar old contacts ke paas `user_id` hi nahi tha (contact form `user_id` capture nahi karta tha).
- Fixed: `ContactController::submit`/`store` ab `user_id` = logged-in user set karte hain.
- DashboardController::notifications ab real `unreadCount` badge dikhata hai (pehle 0 hardcoded).
- Backfill: contacts me `user_id` email match se set (14 contacts, 4 guest reh gaye bina matching user: #3 #4 #12).
- Already-replied inquiries ke liye in-app "Reply to Your Inquiry" notifications bana di (u9 → #80, u2 → #81), unread. Backup: `database/contact_notify_backfill_20260907_152109.sql`.

## 295. (2026-09-07): product description ↔ editor formatting + Policy Text editability**

- All 808 product descriptions were plain markdown; frontend rendered via marked (breaks+gfm). Admin Summernote showed one raw paragraph.
- New `app/Console/Commands/ConvertProductsDescriptionToHtml.php` + `Commands/ConvertDescriptionsToHtml.js` (reuses frontend marked.min.js) — migrated all 808 to HTML (verified: 0 markdown remain, 808 with <ul>). Backup: `database/desc_html_migration_20260907_153642.sql`.
- New `App\Support\DescriptionMarkdown::toHtml()` PHP converter — verified 808/808 byte-identical vs node marked output. Hooks into `ProductController::normalizeImportedProductData` so future CSV imports store HTML directly.
- Editor safety net: `backend.js` pre-renders markdown textareas via marked before Summernote init; admin layout now loads `marked.min.js`; removed 3 duplicate Summernote init blocks (layout had multiple copies; backend.js is single init point).
- Side effect: frontend `.short_description` area (product page) now renders formatted HTML instead of raw markdown (improvement).
- Admin Product Edit: "Policy Text" field was empty because policy_text is null for 807 products (auto-generated template). Now pre-fills with the same auto-generated Buy/Return Policy content (via the partial) so admin can edit; saving stores it as custom policy_text.

- Policy Text DB backfill: all 808 products' Buy/Return Policy now stored in `policy_text` (auto-generated template content persisted per product). One product (781) had hidden `<p><br></p>` — force-nulled & re-generated (2207 chars). Backup: `database/policy_backfill_20260907_155545.sql`. Frontend unchanged visually (same partial output); admin edit form shows stored content.

- Key Features dedupe: descriptions of all 808 products contained a baked-in "Key Features:" + <ul> section AND there was a separate (empty) `features` field. New `DescriptionMarkdown::splitKeyFeatures()` extracts the trailing Key Features <ul> into the `features` column (inner HTML preserved); description cleaned to intro-only. Import path (`normalizeImportedProductData`) now auto-splits too. Frontend renders Key Features from the `features` field (same items). Backup: `database/features_extract_20260907_160104.sql`. Verified: 0 descriptions with Key Features, 808 features populated, live page OK.

## 296. (2026-09-16): Blog multi-category pivot + scheduling heal + admin password forensic**

- **Multi-category pivot feature complete:** Migrations (create + schema-filler: `blog_additional_category` pivot w/ FKs + unique pair), `Blog` model private-array relation `additionalCategories()` (belongsToMany `blog_additional_category` + withTimestamps), controller `BlogController`: store+update me `additional_categories` validation rules (nullable array + `exists:blog_categories,id` per item) + `syncAdditionalCategories` pivot sync (collect-filter-unique-values-all), edit/create blades me `selectedAdditionalIds` pass + `blog-category-field` partial me additional checkbox group. Admin path green (`php -l` / `view:cache` clean). Seeders/commands/cron me koi password-touch nahi (below).
- **Scheduling heal (07:25 window):** Blog pivot `additional_categories` heal script chala — sirf `blogs` rows, per-minute ctx. 
- **Admin-password forensic (root cause):** `revisions` table me `password`-involved rows = **0** (sab ~2809 revisions, sab models) → password kabhi bhi Eloquent/`save()`/`update()` se change NAHI hua (Revisable har dirty col ko actor+IP+url ke saath log karta, password included). 2026-09-16 07:25:01 (`users#5 master_admin updated_at`, exact blog-pivot minute) pe revisions = **0** → wo change raw/manual path se hua (Tinker/DB-tool/seed-manual/raw SQL), code-seeders/crons/jobs (blog:auto-publish, file:audit, trash:purge, db:seed - UserSeeder firstOrCreate create-only) sab eliminated. `admins` table me password column NAHI; Admin model fillable me password NAHI. **Pravesh path se aise badla — audit trail nayi fix: `Revisable` me password_changed entry + `password_changed_at` column + property `Hash` redact — taaki mystery dobara na ho. (Fix pending approval.)**

## 297. (2026-09-17): Search Categories — Available/Selected Dedupe + Orphan Fix (Priority 1)

**Problem:** "Available Categories" list me matched categories bar-bar/duplicate dikhti thi; "Selected (Order)" me bhi duplicates add ho rahe the (save pe undo nahi hota tha). Browser ke network panel me kabhi 41 kabhi 71 checkbox dikhte the — inconsistent.

**Root cause:**
- GET render pe `Category::with('children')->get()` me parent categories apni children ke saath **nested render + top-level list** dono jagah aa rahi thi → duplicate display.
- UPDATE me `filter_var`/dedupe nahi tha → same label/url alag-alag base host (`localhost` vs `192.168.130.54`) ke saath JSON me store ho ja rahe the; blade JS dedupe sirf exact `label+url` string se karta tha → miss.
- Child categories jo top-level parent ke andar already render hote the, wo "orphan" list me bhi repeat ho rahe the.

**Files changed:**
- `app/Http/Controllers/AdminController.php`:
  - `searchCategorySettings()` (GET): sirf **top-level** `whereNull('parent_id')` categories query + `->unique('id')`; orphan (dangling/dangling-parent) categories alag build karke `orphanCategories` compact; selected labels normalize/dedupe kar `selectedLabels` pass (case-insensitive).
  - `updateSearchCategorySettings()` (UPDATE): `FILTER_VALIDATE_URL` guard + lowercase-label dedupe (`$seenKeys`) + URL base normalize (`config('app.url')` base + path) → save kabhi duplicate na ho. Exact-base URL prefix wale existing selected items bhi normalize ho jaate hain.
- `resources/views/admin/settings/search-categories.blade.php`:
  - Nested children rendering (`<ul class="ps-4">` etc.) top-level ke andar — children list me `sc-category-cb` checkbox ab server-side render bhi hain.
  - "Available Categories" column me **orphan categories section** (top-level parent ke bahar vaale).
  - `@checked` ×3 (parent / child / orphan) — selected state server-render pe pre-check.
  - `max-height` scroll box hata kar `max-height:none` → sab 71 checkboxes bina scroll ke visible.

**Verification:** Deterministic server render — `tinker` pe 808 products / 71 unique categories; rendered HTML me **71 `sc-category-cb` checkbox** (tinker render + `view:cache` + `php -l` clean). Browser me 41 dikhna = **transport/page cache** (purana HTML snappy box) — hard-refresh / `?nocache` query / naya browser tab se 71 aata hai.


## 298. Admin Search Categories — Recursive Category Tree Fix (17 Sep 2026)

**Problem:** Admin Search Categories ke "Available Categories" list me sirf partial categories dikh rahi thi, jabki database me total 71 categories hain.

**Fix:** Search Categories ke liye full recursive category tree load aur render kiya, taaki nested child categories kisi bhi depth par available list me show hon.

**Files changed:**
- `app/Models/Category.php` — recursive search-category tree helper
- `app/Http/Controllers/AdminController.php` — full recursive tree pass kiya
- `resources/views/admin/settings/search-categories.blade.php` — recursive tree renderer use kiya
- `resources/views/admin/settings/search-categories-tree.blade.php` — nested categories ke liye recursive partial

**Verified:** Database category count 71; controller PHP syntax valid; nested categories ab available list me include hoti hain.
## 299. (2026-09-17): Product URL Rebase — Stored vs Display Host Normalization

**Problem:** "My Inquiries" / admin contact show me product URL kabhi `http://localhost/stautoparts/...` kabhi `http://192.168.130.54/stautoparts/...` dikhti thi — mixed host, kyunki stored Contact message body me `Product URL:` line par **baked literal URL** he (jab contact bake hua tha us waqt ke APP_URL host ke saath). Display blade bhi wo raw stored string dikhata tha → non-deterministic aur Copilot/Copilot transport diff.

**Root cause:** Message store time pe `route('product', $slug)` se full URL bana kar message me bake kiya gaya tha. Purane inquiries `localhost` (purana APP_URL), naye `192.168.130.54` (current APP_URL). Blade ne regex-se `$productUrl` parse karke **raw** render kar diya.

**Fix (display-side, data untouched):**
- `app/helpers.php` — added `normalizeStoredUrl(?string $url): ?string`: parse_url se path nikaal ke `config('app.url')` base pe **rebase** karta he (stored host/dir ignore; APP_URL base + stored path ke saath rebuild; port-NULL; localhost/127.0.0.1 likha ho to bhi wahi base milta he). External absolute URLs (app dir pe nahi) wapas unchanged.
- `resources/views/user/inquiries.blade.php` — `$productUrl = normalizeStoredUrl(trim($m[2]))`
- `resources/views/admin/contacts/show.blade.php` — `$productUrl = normalizeStoredUrl(trim($m[2]))`

**Determinism proof:** Server-render me har product URL ab hosted pe `app.url` (= `192.168.130.54/stautoparts`) → 0 `localhost`, 0 `127.0.0.1` rendered; stored data me har host ko display-time normalize kiye jaate hain (naya inquiry `192.168.130.54` se banane par direct, purana `localhost` wala bhi display rebase ho jata). **Backfill (DB data rewrite) = NAYI — sirf display-side, deterministic.**

**Same-login-host requirement:** Jo bhi host se login karo (`192.168.130.54`) — sab product URLs wahi host host ke saath dikhte hain (server-side render, view:cache clean).

**Files changed:**
- `app/helpers.php` — `normalizeStoredUrl()` added
- `resources/views/user/inquiries.blade.php`
- `resources/views/admin/contacts/show.blade.php`

**Audit result:** user inquiries me 8 product-msg rows; 2 rows stored `localhost` baked (contact#8, #14) — display-rebase se dono ab `192.168.130.54` pe render hote hain (deterministic, koi data rewrite nahi).

## 300. (2026-09-18): Image Manager — Empty Dimensions "× px" Root Cause + Display Fallback

**Problem:** Admin Image Manager me bahut si images me "Dimensions:" row khali " x  px" dikhti thi (dono width/height ke beech koi numeric value nahi).

**Root cause (deterministic, DB audit via tinker):**
- `images` table me 10,653 total images me se **6,241 (~58.6%) me width/height dono NULL** (width-NULL same count — dono connected).
- Kiyo? width/height sirf **upload/store path** me `getimagesize()` se set hote hain (ImageController store/update). Bulk/seed/import ke images (`Image::create()` with no width/height) me NULL rehte hain.
- Blade `admin/images/edit.blade.php:26` render: `{{ $image->width }} x {{ $image->height }} px` → width/height NULL pe empty → " x  px".

**Fix (completed — data backfill + display fallback):**
- **Data backfill:** naya command `app/Console/Commands/BackfillImageDimensions.php` (`php artisan images:backfill-dimensions`, `--dry-run` supported) — NULL width/height wali har image ka `file_path` resolve karke `getimagesize()` se width/height update karta hai. **Result: 6,245 NULL → 4** (sirf genuinely missing/unreadable files). Sample: #7800 ab 800x450.
- **Root-cause hardening:** `ProductController.php` product-image import ab `getimagesize()` se dims compute karta hai (pehle `width/height = null` hardcoded the).
- **Display fallback (sab jagah):**
  - `resources/views/admin/images/edit.blade.php` — Dimensions row pe ternary fallback: `{{ $image->width && $image->height ? $image->width.' x '.$image->height.' px' : '—' }}` → null-dim images pe "—" dikhta he, kabhi khali " x  px" nahi. (Gotcha: pehle `px@else—@endif` me `@else` ke baad space nahi thi → Blade directive compile nahi ho rahi thi, literal `px@else—` render hota tha. Ternary fix.)
  - `resources/views/admin/images/_cards.blade.php` (grid `:26` + list `:44`) — `{{ $image->width }}x{{ $image->height }}` → `{{ $image->width && $image->height ? $image->width.'x'.$image->height : '—' }}` taaki Image Manager cards me kabhi khali "x" na dikhe.

**Verification (deterministic):** `App\Models\Image::whereNull('width')->whereNull('height')->count()` → **4** (2 test files + 2 missing thumbnails + 4 broken webp ke liye 1 file missing — files hi maujood nahi). Baaki 10,649 images me real "W x H" render hota he. `php -l` clean; `view:cache` compiles.

## 301. (2026-09-18): TinyMCE "Extended toolbar" — Active/Inactive (highlight) State on Toggle Button

**Problem:** Admin text editor ke "Extended toolbar" button se toolbar2 (extended row) show/hide hoti thi, lekin button pe koi **active/inactive indication** nahi tha — user ko pata nahi chalta tha ki extended toolbar khuli he ya band. Request: button pe `active` class add/remove ho (visual highlight).

**Root cause (deterministic — headless Chrome + puppeteer verified, TinyMCE 7.9.1):**
- `resources/views/admin/layouts/app.blade.php` me `addToggleButton('extendedtoolbar')` + `api.setActive()` se active state dikhane ki koshish ki, per:
  1. **`classes: 'extendedtoolbar-btn'` spec option DOM me apply NAHI hota** (button class sirf `tox-tbtn` rehti).
  2. TinyMCE toolbar buttons **`init` event ke BAAD lazy render** hote hain → `init` handler me `querySelector('button[data-mce-name="extendedtoolbar"]')` se `null` milta hai.
  3. Toggle button ka `api` **`getEl()` expose nahi karta** (`typeof getEl === 'undefined'`).
  4. Programmatic/synthetic click pe setActive ka `tox-tbtn--enabled` add nahi hota (TinyMCE internal DOM ownership).

**Fix (manual, deterministic):**
- `addToggleButton` → **`addButton`** switch kiya (koi TinyMCE state API reliance nahi).
- Closure variable `extOpen` (per editor) mein toolbar2 open/close state rakha.
- `onAction:` `container.querySelector('.tox-toolbar:nth-child(2)')` se toolbar2 toggle + `extOpen` flip + `button[data-mce-name="extendedtoolbar"]` pe class/aria set:
  `btn.classList.toggle('active', extOpen)` + `aria-pressed='true'/'false'`.
- `MutationObserver` (`childList, subtree` on `editor.getContainer()`) lazy-render ko handle karta he: har mutation pe `syncExtendedBtn()` — `extendedtoolbar-btn` marker class re-add, `active`/`aria-pressed` per `extOpen`, aur **toolbar2 default-hidden sync** (`trows[1].hidden = !extOpen`) — isse pehle default hide bhi lazy-render ke karan fail ho rahi thi.
- CSS (`admin/layouts/app.blade.php` style block): `.tox .tox-toolbar .tox-tbtn.extendedtoolbar-btn.active { background-color:#0d6efd; color:#fff; }` (+ hover `#0a58ca`).

**Verification (deterministic — puppeteer real trusted clicks on headless Chrome):**
- initial: `classes="tox-tbtn extendedtoolbar-btn"`, `aria="false"`, `toolbar2Hidden=true`
- 1st click: `active` class added, `aria="true"`, `toolbar2Hidden=false`
- 2nd click: `active` removed, `aria="false"`, `toolbar2Hidden=true`
- `view:cache` compiles, `view:clear` done.

## 302. (2026-09-18): Blog Scheduled Auto-Publish Fix — Middleware Ko Dead Kernel File Se bootstrap/app.php Me Register Karna

**Problem:** Blog "Schedule Post" wale blogs admin `status = scheduled` par door rehte the, kabhi auto-publish nahi hote the (Aaj wala blog bhi 10:00 AM scheduled tha, 09:28 par still unpublished).

**Root cause (deterministic):** `PublishDueScheduledBlogs` middleware sirf `app/Http/Kernel.php` me register hua tha — Laravel 11+ (project me **Laravel 13**) me `App\Http\Kernel.php` **dead file** hoti hai (framework use hi nahi karta). Middleware configuration ab `bootstrap/app.php` ki `withMiddleware()` / `$middleware->web(append: [...])` me hoti hai. Isliye middleware kabhi run hi nahi hua → scheduled blogs kabhi publish nahi hue.

**Fix:**
- `bootstrap/app.php` — `use App\Http\Middleware\PublishDueScheduledBlogs;` (line 6) + `web` group append me add kiya (lines 26–30).
- `app/Http/Kernel.php` — ab dead hai, par registration (line 48) waisi; hai inert sirf. (Faayda nahi, harm bhi nahi.)

**Verification (live server):**
- Homepage request + authenticated `/admin/blogs` load par blog #30 `scheduled → published` flip hua, admin badge `bg-success` render.
- Blog #31 "When Should You Replace Your Spark Plugs?" 10:00 UTC scheduled tha — middleware ab due time pe/baad auto-publish kar deta hai.

## 303. (2026-09-18): Product Policy Text — Extra Space `.</p>` Before Dot Cleanup (797 Products) + Backup

**Problem:** Bahut se products ke `policy_text` me dot se pehle extra space: `spark plugs .</p>` (dot se pehle `\s+`). Do products me alag-alag number of spaces (` .</p>` vs `  .</p>`) — inconsistent.

**Fix:**
- Naya command `app/Console/Commands/FixPolicyTextDotSpacing.php` (`php artisan product:fix-policy-dot-spacing`) — regex `[[:space:]]+\.</p>` → `.</p>` (policy dot ko normalize). Baaki policy content untouched.
- **Backup:** command ne pehle snapshot table `products_policy_backup_20260918` banaya (808 rows) — rollback chahiye to `INSERT INTO products (policy_text) SELECT ... FROM products_policy_backup_20260918` (doable, clean).

**Result (deterministic):** 808 products me se **797 fixed, 11 clean (skip), 0 leftover**. Sample rows verified clean; product page HTTP 200; `php -l` clean.

## 304. (2026-09-18): Image Manager Picker — Pagination Icons HTML Entities → SVG Chevrons

**Fix:** `resources/views/admin/partials/image-manager-picker.blade.php` ke `renderPagination()` me `&laquo;/&lsaquo;/&rsaquo;/&raquo;` (font-dependent, inconsistent) ko **SVG chevron icons** (double `«»` / single `‹›`) se replace kiya — `li.disabled` + `span.pagination-arrow-left-right-icon` disabled state ke saath, markup `resources/views/vendor/pagination/gs-pagination.blade.php` jaisa hi. `view:cache` clean.

## 305. (2026-09-18): `.form-select` Dropdown Wrapper — Shared MutationObserver Helper (Admin + Frontend Unified)

**Problem (3 cheezein):**
1. **Admin dynamic selects** (`admin/settings/footer.blade.php`: `footer-social-platform`, `footer-col-type`, `footer-col-span`) DOMContentLoaded wrap ke BAAD render hote the → koi `.form-select-wrapper` nahi → chevron arrow gayab.
2. **Duplication:** frontend me `layouts/app.blade.php` inline script, admin me `backend.js` + ek **dead commented copy** (`{{-- ... --}}` block, admin layout lines 213–357) — 2 alag implementations, drift prone.
3. **Cursor decision:** Task #296 (`f0b415d0`) ne `cursor: pointer` add kiya tha — investigation + user discussion ke baad **pointer field par rakha** (expected UX ready), par native dropdown ke `option` par CSS hover/cursor browsers ignore karte hain (Safari/Chrome/FF — OS-native widget) — isliye `option:hover { cursor: pointer }` effectively cosmetic/no-op cross-browser.

**Fix:**
- **Naya shared helper** `public/assets/front/js/form-select.js` (IIFE): `attachSelect()` har `.form-select` ko `span.form-select-wrapper` me wrap karta hai with **double-wrap guard** (`parentElement.classList.contains('form-select-wrapper')` + `data-fsw-bound="1"`); **`MutationObserver` on `document.body` (`childList,subtree`)** — dynamically injected selects (admin footer, page-builder blocks) auto-wrap → chevron hamesha dikhta. Behaviors preserved: `mousedown` → `focused` toggle, `blur`/`change` → close, `Enter`/`Space` → toggle, `Escape` (keydown+keyup, capture) / `scroll` (capture) / outside-click → close. `.address-suggest-trigger` div-comboboxes bhi wrapper se chevron paate hain.
- **Frontend** `layouts/app.blade.php`: inline wrapper script (735–782) **remove**, shared include add (line 657) with `?v=filemtime` cache-bust.
- **Admin** `layouts/app.blade.php`: dead `{{-- ... --}}` block **remove** (213–357); `form-select.js` include add (line 113). `backend.js` se `closeFormSelects` + wrap `forEach` **remove** (Escape/scroll tooltip + sidebar logic intact).
- **CSS** `public/assets/front/css/style.css`: `.form-select` par `cursor: pointer` (field) **retained**; naya rule `.form-select-wrapper:has(.form-select:disabled)::after { display:none }` → **disabled select pe chevron hidden**. `resources/css/app.css` (source copy) sync: `cursor: pointer` + `option:hover` restored.
- **Page-builder:** `public/vendor/page-builder/css/page-builder.css` sirf size/font rules rakhta hai (koi arrow conflict nahi) — isliye admin page-builder blocks ka `form-select-sm` bhi helper se wrap hoga.

**Verification (deterministic):** `node -c` dono JS clean; `view:cache` compiles; homepage HTML me `form-select.js?v=…` present + **0** stale `closeFormSelects`; authenticated `/admin/settings/footer` → **200**, helper loaded, dynamic select `footer-social-platform` class `form-select form-select-sm` render (MutationObserver wrap → chevron aayega). Admin user #5 password ke liye temp-pass login test kiya, baad me **exact original hash** `DB::table('users')->where('id',5)` se restore kiya (User model ka `hashed` cast raw-hash assignment par re-hash kar deta hai — isliye model `update()` se nahi, DB layer se set kiya).

## 306. (2026-09-18): Site Audit — 3 Confirmed Bugs (FIXES PENDING — kal karna, abhi sirf document kiya)

**Audit ki (read-only):** server status, key pages (home/shop/cart/product/blog/faq/contact/admin), DB settings, rendered HTML, `storage/logs/laravel.log`, 200/500 checks. Results:

### 🐞 Bug 1 — 404 pages render as 500 (site-wide, 248 "Undefined variable $page" errors logged aaj)
Unknown URL (e.g. `/contact`, `/nonexistent`) → 500 milestone page, 404 page kabhi dikhti nahi.
- **Root cause:** `app/Http/Middleware/SetPageAttributes.php` — `page` sirf `if ($route)` branch me `View::share('page', null)` karta (line 52); `else` branch (route==null, 404 case, lines 53–56) me `page` **share nahi** hota. `resources/views/partials/page-attributes.blade.php:11` line 7 ke `if ($baseSlug)` block skip hone par `$page` assign nahi hota → line 11 `if (!$page)` → undefined variable → crash. `errors/404.blade.php` + `errors/403.blade.php` dono ye partial include karte hain.
- **PENDING FIX:** (1) else-branch me bhi `View::share('page', null);`; (2) partial top par `$page ??= null;` defensive init (middleware pipeline ke bahar bhi safe).

### 🐞 Bug 2 — `?spatest=1` debug backdoor = 500 on every page
- `SetPageAttributes.php:24-26` me leftover uncommitted debug code: `if ($request->query('spatest') === '1') { abort(500, 'SPA-DEBUG-'.time()); }` — kisi bhi page par `?spatest=1` → 500. Security/cleanliness issue.
- **PENDING FIX:** block delete karna (git diff se confirm hua — kabhi committed nahi, kabhi removed bhi nahi).

### 🐞 Bug 3 — Triple `/stautoparts` prefix category links in header "All Categories" mega-menu (16 links × every `layouts/app` page)
`search_category_menu` setting me URLs store: `http://192.168.130.54/stautoparts/stautoparts/stautoparts/category/...` (homepage/shop/cart rendered HTML me confirm — 16 bhi 16). Click → 500/mangled link.
- **Root cause:** `AdminController::searchCategorySettings()` (GET, ~line 723) normalization `preg_match('#^https?://[^/]+(/.*)$#')` sirf host replace karta, path me base-dir (`/stautoparts`) ko wahi/print chhod deta; phir `$appBase` (= host + `/stautoparts`) prepend karta → har admin save pe prefix multiply. `updateSearchCategorySettings()` me jo already `$appBase` se start ho wo unchanged rehta (damage preserve). `layouts/app.blade.php:571` stored URL raw render karta hai.
- Effective same bug pattern `normalizeStoredUrl()` (`app/helpers.php:187`) me bhi — path se sirf EK base-dir occurrence strip hoti (task #299) → repeated prefixes me fail.
- **PENDING FIX:** (1) `normalizeStoredUrl()` me ALL repeated base-dir prefixes strip; (2) DB data repair (tinker: `search_category_menu` decode → har URL fix helper se → re-save); (3) GET + UPDATE normalization ko robust helper pe switch; (4) render-side self-heal (`layouts/app.blade.php:571` item url normalize) taaki stale data kabhi broken na dikhe.

### ✅ Non-issues verified (koi action nahi)
`/contact-us`, `/faq`, `/blog`, product, shop, cart → 200; `/page/*` → 301 canonical (`/page/blog`→`/blog`, intended); `/checkout`, `/admin/*` → 302 auth-redirect (intended); Google Fonts 404s external-only; all local assets → 200.

### Bash/DB verification commands used (kal reuse karna)
```
curl -s -o /dev/null -w "%{http_code}\n" "http://192.168.130.54/stautoparts/contact"        # 500 (Bug 1)
curl -s -o /dev/null -w "%{http_code}\n" "http://192.168.130.54/stautoparts/?spatest=1"     # 500 (Bug 2)
php artisan tinker --execute="dump(array_slice(json_decode(DB::table('settings')->where('key','search_category_menu')->value('value'),true),0,2));"  # Bug 3 triple URLs
```

**Status: FIXES PENDING (18-Sep ko sirf document kiya; fixes kal).**

## 307. (2026-09-22): Admin Products — Index Filters + Bulk Actions + Duplicate + SKU/Specs Frontend (last-session work completed, power-off ke baad document)

**Context:** Pichhli session me backend (controller + routes + migration) already ready tha, lekin system power-off hone se view UI adhura aur TASKS.md entry lost ho gayi. Isto ye entry + completion dono is task me.

### Completed features (backend mostly khatam tha, pehle se)
- Migration `2026_09_22_000000_add_sku_and_specifications_to_products_table.php` → `products.sku`, `products.specifications` (run ho chuka). `Product::sku` indexed.
- `Product.php`: `sku` + `specifications` fillable/casts + `getSpecificationsAttribute()` accessor (JSON decode → array).
- `ProductController::store()/update()`: SKU trim + `parseSpecifications()` (lines `Label :: Value` → `[['label'=>, 'value'=>]]` array).
- `ProductController::index()`: search name/sku/make/model; filters `category_id`, `brand_id`, `seller_id`, `product_type`, `stock_filter`, `status`, `featured`; sort-by whitelist me `sku` + `rating` add; `$categories/$brands/$sellers` view me pass.
- Bulk methods: `duplicate()`, `bulkDelete()`, `bulkRestore()`, `bulkForceDelete()`, `bulkStatus()`, `bulkFeatured()`, `bulkCategory()` + routes (`/products/bulk/*`, `/products/{id}/duplicate`).
- Admin create/edit forms: SKU input + Specifications textarea + `admin.partials.specifications-preview` (live JS preview; `Label :: Value` format).
- Password reset flow (ish session me nahi, pehle ki WIP): `AuthController` forgot/reset + `/forgot-password`, `/reset-password/*` routes + `auth/forgot.blade.php`, `auth/reset.blade.php` + login link. **NOTE:** is task scope me nahi tha, sirf working-tree me present; alag se verify/commit karna pending hota to YAGNI — sirf document kiya.

### 🛠 Fixes completed is session (view UI gaps)
1. **`admin/products/index.blade.php` (rewrite jo controller ke ready filters/bulk use kare):**
   - Filter bar card: Category, Brand, Seller, Section (`product_type`), Stock (`in/out`), Status, Featured dropdowns + Filter/Clear Filters (GET, `trashed`/`search` preserved). Active/Trash tabs ab active filters carry karte hain.
   - Table: checkbox column (`bulk-select-all` + `bulk-select-row`), sortable SKU + Rating columns (controller pehle se support karta tha, view ab use karti hain).
   - Bulk toolbar (revisions `bulkTrashRun` pattern, `bulk-form` + hidden `ids[]` + count spans):
     - Active tab: Delete→trash, Activate, Deactivate, Featured, Unfeature, **Move to Category** (dropdown required) — buttons `d-none` jab tak koi row selected nahi.
     - Trash tab: Restore, Delete Permanently.
   - Per-row `Duplicate` action button (POST `admin.products.duplicate`, copy copy confirm) in Active tab action column.
   - Stray commented-out PHP block hataya; empty row `colspan="16"` sync.
2. **`admin/products/show.blade.php`:** Product Summary me SKU row; Specifications tab (Label|Value table) jab non-empty.
3. **Frontend `product/show.blade.php`:** `details_tags_sku` me SKU / Part # item; Description tab me "Specifications:" table (`product-specifications-table`) jab `specifications` non-empty.
4. **`ProductController.php:258`:** `$request->validate([` indentation fix (cosmetic).
5. **`cart/index.blade.php`:** qty input ab editable (`readonly` removed) — script.js `.change-qty` +/- buttons + manual typing dono handled (pehle se existing fetch POST `/cart/update/{id}`).

### Files changed (this session)
- `resources/views/admin/products/index.blade.php` (rewrite/filter+bulk+duplicate+SKU/rating)
- `resources/views/admin/products/show.blade.php` (SKU row + Specifications tab)
- `resources/views/product/show.blade.php` (SKU + specs table)
- `app/Http/Controllers/ProductController.php` (store() indentation only — rest pehle se WIP)

### Verification
- `php -l` controller clean; `php artisan view:cache` compiles; admin `/admin/products` (filters + bulk UI), `/admin/products/{id}` (SKU + specs tab), product frontend page (SKU + specs) — 200s.

## 308. (2026-09-22): Admin Product Index UI Fixes — Clear Filters Active State + Bulk Category Select Arrow

**Context:** Admin products index ke do UI issues — (a) filter bar me "Clear Filters" button active filters hone par kabhi show nahi hota tha, (b) bulk "Move to Category" dropdown (`d-none`) par `form-select-wrapper::after` arrow visible rehta tha.

### Fix 1 — Clear Filters ab active filter ke saath dikhta hai
- **Root cause:** `admin/products/index.blade.php` top `@php` block `$hasActiveFilters` define karta tha (controller se override), lekin usme `search` parameter miss tha — `?search=...` hone par bhi button hidden rehta tha.
- **Fix:** `index.blade.php:15` — `$hasActiveFilters` me `request()->filled('search')` add kiya (category/brand/seller/product_type/stock_filter/status/featured ke saath). No-op values (`product_type=all`, `stock_filter=all`) filter logic ke anusaar count nahi hote.
- **Note:** Controller me pehle `compact('hasActiveFilters')` add kiya tha, phir remove kiya — blade ka `@php` variable anyway controller-val override karta hai, isliye duplicate source-of-truth rakhne ka fayda nahi.

### Fix 2 — Hidden bulk-category-select par arrow ab nahi dikhta
- **Root cause:** `public/assets/front/js/form-select.js` har `.form-select` ko auto `.form-select-wrapper` span me wrap karta hai; `d-none` inner `<select>` par lagti hai, par `::after` arrow wrapper span (`style.css .form-select-wrapper::after`) par render hota hai — wo kabhi hidden nahi hota tha. Purana rule `select#bulk-category-select.d-none::after` galat target tha (pseudo-element select par nahi, wrapper span par hai) → ineffective.
- **Fix:** `public/assets/front/css/style.css:4739` — `.form-select-wrapper:has(.form-select.d-none) { display: none; }` — inner select hidden hone par poora wrapper (arrow samet) hide ho jata hai. `index.blade.php` ki `refresh()` JS `selected.length === 0` par `d-none` toggle karti hai, to arrow bulk-category dropdown ke saath sahi show/hide hota hai.

### Files changed
- `resources/views/admin/products/index.blade.php` (`$hasActiveFilters` me search + Clear Filters gate)
- `public/assets/front/css/style.css` (`.form-select-wrapper:has(.form-select.d-none)`)

### Verification
- Clear Filters button: no-filter → hidden; `category_id=1`, `search=brake`, `trashed=1&category_id=1` → shown; `stock_filter=all`/`product_type=all` → hidden.
- Arrow fix: served CSS me rule confirm; wrapper display:none jab select d-none.

## 309. (2026-09-22): Auto SKU Generation + Backfill
- **Context:** Products ko SKU auto-generate karna (koi bhi product SKU ke bina submit ho to lena) + existing products backfill karna.
- **Details:**
  1. **`Product::generateSku()`** model static helper — `SKU-{name-slug[0:6]}-{random5}` format, DB-unique loop. `app/Models/Product.php`.
  2. **`ProductController::store()`** — `sku` blank rahe to auto-generate; **`update()`** — blank aane par existing preserve, warna generate. `app/Http/Controllers/ProductController.php`.
  3. **Admin create + edit blades:** SKU input ke side **"Auto"** button (client-side same format preview, tab click). JS `sku-generate-btn`. `create.blade.php`, `edit.blade.php`.
  4. **Backfill command** `products:backfill-skus` (+ `--dry-run`) — har SKU-less product ko auto SKU deta hai. `app/Console/Commands/BackfillProductSkus.php`.
- **Files changed:**
- `app/Models/Product.php`, `app/Http/Controllers/ProductController.php`,
- `resources/views/admin/products/create.blade.php`, `resources/views/admin/products/edit.blade.php`,
- `app/Console/Commands/BackfillProductSkus.php` (new)
- **Verification:** dry-run = 808 products; real run — 808/808 SKU-assigned, total products 808, empty sku left 0. `php -l` clean (model, controller, command), `view:cache` OK. Note: password recovery temp-set/restored during admin page verification, original hash restored.

## 310. (2026-09-23): Product Import/Export — SKU + Missing Columns (Policy, Features, Specifications, Reviews, Tab Labels)

**Context:** Product form me SKU add hone ke baad bhi import/export me SKU support nahi tha; aur audit me pata chala ki "Buy/Return Policy" (`policy_text`), `features`, `reviews_data` sirf import me the (export me nahi → export→import round-trip me data lose hota tha), aur `specifications` + `tab_label_1/2/3` dono me kabhi add hi nahi hue the.

### SKU in import/export
- **`import()`**: `$expected` columns me `sku` add (CSV header recognized). New product → CSV sku empty to `Product::generateSku($data['name'])` auto-generate; existing product (update) → CSV empty par existing sku preserve, na ho to generate with `excludeId`.
- **`exportCsv()`**: `sku` column name ke baad.
- **`downloadSampleCsv()`**: `sku` header + sample (`bp-1001`); kuch rows blank — auto-generate ka demo.
- **`resolveImportedSku(array $data, string $name, ?Product $existing = null): string`** — naya static helper (`ProductController.php`): CSV sku present → trimmed; nahi → existing sku ?? generateSku(name, excludeId). Form store/update ke SKU behavior se consistent.

### Missing columns — ab pura round-trip
| Column | Pehle | Ab |
|---|---|---|
| `policy_text` (Buy/Return Policy) | sirf import | export + sample bhi |
| `features` | sirf import | export + sample bhi |
| `reviews_data` | sirf import | export + sample bhi |
| `specifications` | dono me missing | import + export + sample |
| `tab_label_1/2/3` | dono me missing | import + export + sample |

- **Export format:** `features` → newline-joined (`\n`), import split hone ka format; `specifications` → `Label::Value` lines (import ke `parseSpecifications()` se round-trip); `reviews_data` → JSON string; `policy_text`/tab labels → raw string.
- **Import:** `$expected` me `specifications`, `tab_label_1/2/3` add; `$insertData` me `specifications` = `$this->parseSpecifications($data['specifications'] ?? null)`, tab labels passthrough.
- **Sample CSV** headers/rows 26 columns me sync (headers count == row count, checked).
- **Import blade docs** (`admin/products/import.blade.php`): `sku` (leave empty to auto-generate), `specifications` (`Label::Value` newline-separated), `tab_label_1/2/3` documented.

### Files changed
- `app/Http/Controllers/ProductController.php` — `$expected` (sku + specifications + tab labels), `resolveImportedSku()`, import insertData, `exportCsv()` (headers + values), `downloadSampleCsv()` (headers + sample rows)
- `resources/views/admin/products/import.blade.php` — supported columns list update
- `tests/Feature/ProductImportSkuTest.php` — **new**: provided-sku priority, trim, existing-sku preserve (DB-free tests, kyunki is env me sqlite driver nahi hai)
- `TASKS.md` — ye entry

### Notes
- `slug`, `rating` (user aggregate), `added_by`/`is_deleted` intentionally import/export me nahi (internal/system).
- `sku` column ka koi DB unique constraint nahi (sirf index) — duplicate CSV sku possible, manual form ke bahav ke saath consistent.

### Verification
- `php -l` controller clean; `vendor/bin/pint` clean (controller + test).
- `php artisan test --filter "ProductImportSkuTest|ProductImportPolicyReviewsTest|AdminProductRoutesTest"` → 6/6 passed, 14 assertions.
- Full suite ke 4 failures pre-existing environment issue hain (missing sqlite driver → DB-dependent tests: `ExampleTest`, `HomeTopBrandsTest`), mere changes se unrelated.

## 311. (2026-09-23): Ecommerce Audit — Phase 1–4 Implementation Report

**Source:** approved multi-phase audit plan (Phase 1 stock/returns/tracking/Mailables galat-tha earlier; Phase 2 gap list 2.8–2.15; Phase 3 fixes 3.18–3.21; Phase 4 = ye reporting entry). Sab kuch `/var/www/html/stautoparts` pe implement kiya, uncommitted.

### Phase 1 (previously completed)
- Stock deduct/restore (`stock_deducted`), returns (user+admin), 6 Mailables + mail blades with **log mailer** (no SMTP creds), order tracking fields, welcome/contact/review emails.

### Phase 2 — shipped
- **2.8 Review moderation** — `Admin\ReviewController` + `admin.reviews.*` routes/sidebar/view; `approved`/`deleted` filters honored across product card / similar / compare / product show.
- **2.9 Newsletter** — subscribers migration + `Subscriber` model, AJAX footer form → `newsletter.store` (dedupe by lowercase trimmed email, JSON `{success,message}`), admin index/search/destroy/export CSV (`newsletter-subscribers-YYYY-MM-DD.csv`).
- **2.10 Guest checkout** — `orders.user_id` nullable (FK dropped), `CartController::checkout()` guest branch → `checkout.guest`, session `billing_info` → existing delivery/payment/confirmed flow; checkout routes de-auth'd (`nocache` only); `POST /checkout/guest`; order-confirmed guest link; `tracking()` public (`GET|POST /order/tracking`), guest verified by billing email in `shipping_details`.
- **2.11 Cancel guard** — pending/processing only + stock restore (earlier).
- **2.12 Admin order gaps** — `Admin\OrderController` invoice (PDF), email-customer (queued `OrderConfirmationMail`), CSV export (filter-aware, 7-column order data); duplicate invoice routes removed.
- **2.13 Stock management** — `admin/products/stock` + `/products/bulk/stock` (mode add|set, DB transaction), stock view with threshold/filter/search/bulk UI, sidebar link.
- **2.14 Per-customer order history** — `admin.customers.show` with stats + paginated order history + per-status badges.
- **2.15 Recently viewed** — session `recently_viewed` (max 10, dedupe, most-recent-first) + `partials/recently-viewed` on product page.

### Phase 3 — fixes
- **3.18 Bugs** — `change.password` GET was `abort(404)` → now renders `user/change-password`; dashboard `$userVehicles` was never passed → now included; **fits-vehicle badge**: `$userVehicle`/`$vehicleFit` were computed only in dead `ShopController::product` while `ProductController::show` renders the page → moved logic into `ProductController::show` (uses `session('selected_vehicle_id')` fallback to default vehicle; normalized lowercase trim compare).
- **3.19 SEO** — `partials/meta-tags`: canonical + `og:site_name` + Twitter cards; `robots => 'index, follow'` on 15 public pages (home/shop/product/categories/brands/blog/blog.show/pages + 7 policy pages); dynamic XML `sitemap.xml` (home/shop/categories/brands/blog/policy/category-chains/active products/accessible blogs/blog categories/tags); `robots.txt` disallows admin/user/checkout/cart/compare + sitemap URL. Alt tags already covered by `imgTag()` helper.
- **3.20 WhatsApp + order timeline** — floating WhatsApp button `partials/whatsapp-button` (driven by footer_columns whatsapp/WA link, `wa.me` with prefilled text) in `layouts/app`; order `status_history` JSON column + migration + `Order::recordStatusChange()`/`getStatusHistory()`; recorded on placement (`CartController`) and admin status change (`Admin\OrderController::updateStatus`); reusable `partials/order-status-timeline` rendered in tracking, user order show, admin order show.
- **3.21 Cleanup** — `customer.pr    oducts` → `customer.products`; dead `Route::resource('staffs', StaffController)` (controller only had `index`, views used `admin.staff.*`) removed + root `StaffController` deleted; dev junk (`cart.blade.copy.php`, `test-watcher2.html`, `test-watcher3.html`) removed; no `docs/index.html` reference exists; no unused top-level blade views (errors/404, errors/403 auto-picked by Laravel).

### Files changed (Phase 2.12–3.21)
- `routes/web.php` — tracking public, guest submit, admin subscribers/orders/products-stock/customers routes, `sitemap.xml`, `customer.products`, staffs resource removal, `change.password` NOT here (controller-level).
- Controllers: `CartController`, `OrderController`, `ProductController` (stock, recently-viewed, fits-vehicle), `UserController`, `DashboardController`, `NewsletterController`, `HomeController` (sitemap), `Admin\OrderController`, `Admin\SubscriberController`.
- Models: `Subscriber`, `Order` (status_history + helpers).
- Migrations: `2026_09_23_000002_create_subscribers_table`, `2026_09_23_000003_make_orders_user_id_nullable`, `2026_09_23_000004_add_status_history_to_orders_table` (all ran).
- Views: guest checkout, tracking (dynamic layout), change-password, subscribers/stock/customers-show admin pages, newsletter footer AJAX, recently-viewed + whatsapp-button + order-status-timeline partials, order confirmed guest link, meta-tags/canonical + `robots` on public pages, admin/users/order show timeline, sidebar links.
- `public/robots.txt` rewritten; `app/Http/Controllers/StaffController.php` deleted; 3 dev files deleted.

### Scope decisions honored
- Payment gateway **skipped** (simulated stays); shipping/tax stay free+0; emails → log mailer; seller dashboard (#16) & full i18n (#17) deferred.

### Verification
- `php artisan migrate --force` OK (3 new migrations).
- `php -l` clean on all touched PHP; `vendor/bin/pint` clean (ProductController, HomeController, CartController, Admin\OrderController, Order model, routes/web.php).
- `php artisan view:cache` OK; `route:list` clean (316 routes; sitemap + customer.products present; no staffs resource).
- Known pre-existing failures unrelated: sqlite driver missing → DB-dependent tests fail (`ExampleTest`, `HomeTopBrandsTest`).

## 312. (2026-10-15): Schema Drift Fix — SoftDeletes on Products & Categories

**Root cause:** Catalogue DB re-imported; migrations marked "Ran" but `deleted_at` columns missing → `Product::onlyTrashed()` / `Category::onlyTrashed()` threw `BadMethodCallException` / column errors. Fix = add guarded migrations + trait (not strip filters).

**Files changed/created:**
- `database/migrations/2026_10_15_000000_add_soft_deletes_to_products_table.php` — created
- `database/migrations/2026_10_15_000001_add_soft_deletes_to_categories_table.php` — created
- `app/Models/Product.php` — added `SoftDeletes` trait
- `app/Models/Category.php` — added `SoftDeletes` trait
- `app/Http/Controllers/CategoryController.php` — `index` uses `onlyTrashed()` when `?trashed=1`; real `restore()`/`forceDelete()`

**Verified:** `/admin/categories` and `/admin/categories?trashed=1` return 200.

## 313. (2026-10-15): Admin Products `stock` → `vendor_stock` Drift Fix

**Root cause:** `products` table has `vendor_stock` (not `stock`); raw SQL / views referenced the app-facing `stock`.

**Files changed:**
- `app/Http/Controllers/ProductController.php` — index filter/sort, `stockIndex`, `bulkStockUpdate` use `vendor_stock`; sort-key map (`sku→part_number`, `price→cost`, `stock→vendor_stock`); index search switched to `name`/`part_number`
- `app/Models/Order.php` — `deductStock`/`restoreStock` use `vendor_stock`
- `app/Http/Controllers/CartController.php` — `vendor_stock` increment/decrement
- `app/Http/Controllers/Admin/ReturnController.php` — `vendor_stock` increment/decrement
- `resources/views/admin/products/stock.blade.php` — shows `vendor_stock` counts
- `app/Models/Product.php` — `'stock'` added to `$fillable`; mutator writes `vendor_stock`

**Verified:** `GET /admin/products/stock` returns 200.

## 314. (2026-10-15): Reviews — `reviews_data` Column + Review Image Select

**Root cause:** `reviews_data` column missing from `products` after re-import; admin review list selected non-existent `image`.

**Files changed/created:**
- `database/migrations/2026_10_15_000002_add_reviews_data_to_products_table.php` — created (`longText` nullable, `hasColumn`-guarded)
- `app/Models/Product.php` — `'reviews_data'` in `$fillable`; `setReviewsDataAttribute` (`json_encode`)
- `app/Http/Controllers/Admin/ReviewController.php` — select `image` → `main_image`

**Verified:** `GET /admin/reviews` returns 200.

## 315. (2026-10-15): Brands — Missing `status` Column

**Root cause:** `brands` had no `status` column, but `Admin\BrandController` (sort by `status`, `store`/`update`, `toggleStatus`) and `Brand::where('status', true)` in `home-page/edit.blade.php:331`, `ProductController:299/387` depended on it. Model only had a `getStatusAttribute` accessor.

**Files changed/created:**
- `database/migrations/2026_10_15_000003_add_status_to_brands_table.php` — created (`boolean` default true, `hasColumn`-guarded)
- `app/Models/Brand.php` — `'status'` in `$fillable`; `status` cast to `boolean`

**Verified:** `/admin/home-page/14/edit`, `/admin/brands`, `/brands` all return 200.

## 316. (2026-10-15): Header Settings — Unavailable Menu Item Warning

**Change:** `resources/views/admin/settings/header.blade.php` — menu items whose target URL has no matching type are flagged with a warning; detection is URL-based + case-insensitive (`urlLinkedType()` against lowercased `$availableMenuUrls`), not based on stored `type`. Note: many menu slugs (`Suspension-Steering`, `Body-Exterior`, `Brakes-Brake-Parts`, …) genuinely don't exist in the catalogue.

## 317. (2026-10-15): Admin Search Categories — 300s Timeout Fix

**Symptom:** `GET /admin/settings/search-categories` → `Maximum execution time of 300 seconds exceeded` (fatal at `CategoryTree.php:64` and `Connection.php:426`).

**Root cause:** `View::composer('*')` in `AppServiceProvider` runs on **every** rendered view, including each of the ~21,300 recursive `admin.settings.search-categories-tree` partials → composer fired **21,307×**; each invocation rebuilt `CategoryTree::tree()` (2.46 MB database-cache read + `json_decode`, ~95 ms) plus up to 4 DB queries → >2000s. (Environment: Xdebug `mode=develop` amplifies; `products` = 761k rows, `categories` = 21,300.)

**Files changed:**
- `app/Providers/AppServiceProvider.php` — view composer now computes the shared data once per request (`static $sharedData`), then reuses it for every view/partial
- `app/Services/CategoryTree.php` — `tree()` memoized per request (defense in depth; also used by `ShopController`)
- `app/Http/Controllers/AdminController.php` — replaced `Category::query()->pluck('id')` (21,300 ids into PHP, ~3.7s) orphan check with a `whereNotIn` subquery

**Verified (CLI render with authenticated user):** controller data 10.6s → 6.7s; view completes instead of timing out (composer invocations still 21,307 but now cheap). `php -l` clean on all touched files.

**Follow-up (optional):** page still renders ~8.2 MB / ~21,300 checkboxes (~20s CLI, faster without Xdebug) — next optimization is limiting the tree to top-level + second-level categories instead of the full 21,300.


