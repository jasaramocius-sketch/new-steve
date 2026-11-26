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



## 318. FollowedSeller Product Relationship & Nav Menu Active State Fixes (Oct 26 2026)

**Problem:**
1. FollowedSeller relationship (`seller.products`) triggered SQL error: `products` table lacks a `seller_id` column, causing "Column not found" on queries trying to filter by `products.seller_id`.
2. Nav menu active state incorrectly highlighted items due to simple `url()` concatenation; pages like `/page/home` did not map to their canonical routes (e.g., `/`); `$activeCategoryUrls` not populated for product pages, so mega‑menu categories/subcategories never highlighted.

**Solution:**
1. Added missing `seller_id` column to `products` table (nullable foreign key → `sellers.id`) – a lightweight `ALTER TABLE` preserves all existing data and restores the `Seller::products` relationship.
2. Created `canonicalMenuUrl()` helper (in `app/helpers.php`):
   * Normalizes stored links (menu/banner/button) into front‑end URLs.
   * Handles page‑builder live URL maps: maps `/page/home`, `/page/blog`, etc., to their canonical route names (via `page-builder.live_url_map`).
   * Applied this helper everywhere URLs are rendered in nav menus (desktop, mobile, mega‑menu) and replaced old `url()` calls.
3. Updated nav‑menu views:
   * `resources/views/partials/nav-menu.blade.php`
   * `resources/views/layouts/app.blade.php` (desktop + mobile)
   * All menu partial renders now use `$menuUrl = $url === '#' ? '' : canonicalMenuUrl($url);`
   * `$activeCategoryUrls` now populated for product pages (via `ProductController::show()` loading `category->parent` chain and sharing via `view()->share()`).
   * Mega‑menu child/subchild links check against `$activeCategoryUrls` for proper active highlighting.
4. Adjusted FollowedSeller controller:
   * `DashboardController::followedSellers()` removed eager loading of `seller.products` (no longer needed for list display – followedSeller fields `products`, `rating`, `followers` are used instead).
   * `getSellerDetails()` and `getSellerProducts()` still use the now‑functional `Seller::products()` relationship (which leverages the new `seller_id` column).
5. Updated admin product forms:
   * Admin create/edit pages include seller dropdown; `seller_id` column now exists, so form submission works.
   * Admin filters on product index now correctly filter by seller.

**Files changed/created:**
- `database/migrations/2026_08_11_000001_add_seller_id_to_products_table.php` – re‑applied (or manual `ALTER TABLE`)
- `app/helpers.php` – new `canonicalMenuUrl()` function
- `app/Models/Seller.php` – unchanged (relationship remains `hasMany(Product)`)
- `app/Http/Controllers/DashboardController.php` – `followedSellers()`, `getSellerDetails()`, `getSellerProducts()` updated
- `app/Http/Controllers/ProductController.php` – `create()` / `store()` / `edit()` / `update()` handle new `seller_id` from request
- `resources/views/user/followed-sellers.blade.php` – now uses FollowedSeller fields for stats, no longer relies on `seller->products` (fallback preserved)
- `resources/views/admin/products/index.blade.php`, `create.blade.php`, `edit.blade.php` – seller select dropdowns (populated from `Seller` model)
- `resources/views/layouts/app.blade.php` – nav menu (desktop/mobile) URLs switched to `canonicalMenuUrl()`
- `resources/views/partials/nav-menu.blade.php` – similar
- `routes/web.php` – no changes; `/shop` redirect and `/terms-and-conditions` redirect already applied

**Verification:**
- `/user/followed-seller` loads 200, shows seller stats from FollowedSeller fields, no SQL errors.
- Nav menu active state verified on home (`Home` active), blog (`Blog` active), product pages (category chain highlighted), and all other pages.
- Admin product create/edit form renders seller dropdown, product save succeeds with seller_id.
- `/shop` redirect works (301 → `/search` preserving query) and nav menu highlights “Products” when on `/shop?q=...`.
- All previously broken links (category menu, terms, etc.) now correctly resolve; home link crawl shows 0 non‑200.
- Product page mega‑menu correctly highlights current category chain.

**Impact:**
- Fixes a blocking bug for users trying to follow sellers and eliminates SQL errors.
- Improves navigation clarity and SEO friendliness after page‑builder URL changes.
- Maintains backward compatibility: existing products remain without a seller (`seller_id` NULL), and existing admin queries continue to work.

**Next steps (optional):**
- Populate `seller_id` where appropriate (e.g., for products that have a vendor).
- Consider lightweight admin optimization for the search‑categories tree (already noted in task #317).
- Run full test suite (if environment can install `pdo_sqlite`).

## 319. (2026-10-26): Stock Management — Select All Checkbox Fix

**Problem:** The Stock Management page's header checkbox did not select product rows because its JavaScript handler was not included in the admin layout output.

**Root cause:** `resources/views/admin/products/stock.blade.php` declared its script with `@section('scripts')`, while `resources/views/admin/layouts/app.blade.php` renders scripts using `@stack('scripts')`.

**Fix:** Changed the stock view script block to `@push('scripts')` / `@endpush`, so the existing select-all and toggle-all handlers load with the page.

**Verification:** Blade diagnostics report no errors. Select-all controls target the current page's `.row-check` product checkboxes.

## 320. (2026-10-27): Products Without Usable Image — Excluded From All Storefront Listings

**Problem:** `products` table (761k rows) me bahut se products aise hain jinke paas usable image nahi hai — `main_image` khaali, ya placeholder/dummy/no-image/default file. Ye products category cards, category product counts, shop/search listings, brand pages, nav mega-menu counts aur home page sections me show ho rahe the → broken/placeholder images aur inflated product counts.

**Solution:** Ek reusable "usable image" filter add kiya aur usse har storefront listing/count me apply kiya:

1. `Product::scopeHasUsableImage()` — SQL-level filter:
   * `main_image` non-empty **aur** `placeholder` / `dummy` / `no-image` / `default` keywords nahi
   * **ya** `images_json` non-empty (`<> ''`, `<> '[]'`) aur usme bhi ye keywords nahi
2. `Product::hasUsableImage()` — PHP-level check (basename keyword check + local file existence / S3 / `http(s)` URL).
3. Filter apply kiya gaya:
   * `CategoryController::showCategories()` — 3-level `withCount`, `total_products_count` (descendants) aur `preview_products`
   * `ShopController` — `index()`, `customerProducts()`, category + child/sub-child listings, `suggestions()`
   * `Product::statusCountsByCategoryId()` — category-wise counts (covering index scan)
   * `CategoryTree::tree()` — cached nav mega-menu / sidebar counts
   * `HomeController::filterProductsWithImages()` — featured / random / home sections

**Impact — "Accessories and Fluids" (category `id=1`, measured 2026-10-27):**

| Metric | Count |
|---|---|
| Active products (category + all descendants) | **1,430** |
| With usable image | **1,057** |
| Excluded (no usable image) | **373** |

Note: `category_id = 1` par direct products **0** hain — saare products descendants me hain, isliye page/count 1,057 dikhata hai (1,430 − 373).

**Files changed:**
- `app/Models/Product.php` — `hasUsableImage()`, `scopeHasUsableImage()`, `statusCountsByCategoryId()` me filter
- `app/Http/Controllers/CategoryController.php` — counts + preview products
- `app/Http/Controllers/ShopController.php` — shop/search/category/brand listings + suggestions
- `app/Services/CategoryTree.php` — cached tree counts
- `app/Http/Controllers/HomeController.php` — `filterProductsWithImages()`
- `resources/views/categories/index.blade.php`, `resources/views/shop/index.blade.php`, `resources/views/home/index.blade.php` — counts/preview display

**File Revisions (per `file_revisions` table):**

| File | Event | Rev | Timestamp |
|---|---|---|---|
| `app/Models/Product.php` | updated | 14379 | 2026-10-23 07:31:45 |
| `resources/views/categories/index.blade.php` | updated | 14390 | 2026-10-23 07:32:55 |
| `app/Http/Controllers/HomeController.php` | updated | 14453 | 2026-10-23 10:58:25 |
| `resources/views/shop/index.blade.php` | updated | 14490 | 2026-10-23 13:34:34 |
| `app/Http/Controllers/ShopController.php` | updated | 14491 | 2026-10-23 13:34:44 |
| `app/Http/Controllers/CategoryController.php` | updated | 13567 | 2026-09-15 06:09:21 (last recorded; later edits not captured) |
| `app/Services/CategoryTree.php` | — | — | no revision record |

**Note:** File Revisions watcher last entry **2026-10-23 13:34:44** (rev 14491) hai — uske baad watcher band tha, isliye Oct 26 (commit `d42c741`) ke changes File Revisions me record nahi hue. Watcher dobara start karne ke liye: `sudo systemctl restart file-watcher` (ya `node file-watcher.mjs`), phir missing snapshots ke liye `php artisan file:audit --init` ke baad `php artisan file:audit`.

**Verification:**
- SQL (subtree CTE) se cross-check: 1,430 active vs 1,057 usable-image products — code ke scope se match.
- Category cards / product grids me placeholder images wale products nahi aate.
- Category counts, shop filters aur mega-menu counts consistent (same scope use hota hai).
- `php -l` clean on all touched PHP files.

## 321. (2026-10-27): Shop Sidebar + Mobile Menu Categories — A-Z Ordering

**Problem:** Products page (`/shop` → `/search`) ke sidebar me categories DB insertion order me aa rahi thi — A-Z nahi. Same tree mobile menu ke "Categories" tab me bhi use hota hai (`mobileCategoryTree`), isliye wahan bhi order random tha.

**Solution:**
- `app/Services/CategoryTree.php` — tree build se pehle query me `orderByRaw('LOWER(name) ASC')->orderBy('id')` add kiya. Children `$childrenMap` me query order me append hote hain, isliye **har level** (top → sub → child) A-Z sort ho jata hai. `LOWER()` use kiya taaki collation par depend na kare (`utf8mb4_unicode_ci` case-insensitive hai, phir bhi deterministic).
- Cache key `front_category_tree_v1` → **`front_category_tree_v2`** — purana 6-hour cached tree automatically invalid, manual `cache:clear` ki zarurat nahi. Purana `v1` key `cache()->forget()` se hata diya.
- Admin me category save/delete par pehle se hi `CategoryTree::flush()` chalta hai (`AppServiceProvider`), isliye naya key bhi auto-refresh hoga.

**Files changed:**
- `app/Services/CategoryTree.php` — A-Z `orderBy` + cache key bump

**File Revisions:** is change ka koi record nahi — watcher ka last entry **2026-10-23 13:34:44 (rev 14491)** hai aur uske baad band hai. Start karne ke liye: `sudo systemctl restart file-watcher`, phir `php artisan file:audit`.

**Verification:**
- `php -l app/Services/CategoryTree.php` → no syntax errors.
- Tinker (`CategoryTree::tree()`): top-level = `Accessories and Fluids | Air and Fuel Delivery | Air Brake Hose Label | Belts and Cooling | Body | Brake | Driveline and Axles | Electrical, Charging and Starting | …` (25 tops, A-Z).
- Children of "Accessories and Fluids" = `Adhesives | Appearance Products | Books | Brackets, Flanges and Hangers | Cleaning Products | Clothing | Control Cables | …` (A-Z).
- HTTP check `GET /new-steve/search` → 200; sidebar ka first category "Accessories and Fluids" aur uske children A-Z order me render ho rahe hain.

## 322. (2026-10-27): Shop Sidebar — Collapsible Widgets (Categories / Price Range / Vehicle)

**Problem:** Products page ke sidebar me teeno widgets (Categories, Price Range, Vehicle) hamesha expanded rehte the. Categories list bahut lambi hone se sidebar scroll bada ho jata tha aur Price/Vehicle tak pahunchne ke liye kaafi scroll karna padta tha.

**Solution:** Har widget ke heading ko Bootstrap 5 collapse toggle bana diya (default **closed / collapsed**):

- Heading ke andar native `<button type="button" class="widget-collapse-toggle">` (keyboard-accessible) + right side chevron icon (`fa-chevron-down`) jo expanded state me 180° rotate hota hai.
- Body `<div class="collapse" id="sidebar…Collapse">` me wrap kiya (`show` class nahi → page load par closed):
  * `#sidebarCategoriesCollapse` — Categories widget
  * `#sidebarPriceCollapse` — Price Range form
  * `#sidebarVehicleCollapse` — Vehicle form
- `aria-expanded="false"` / `aria-controls` attributes add kiye (expanded hone par Bootstrap khud `true` kar deta hai).
- CSS (`custom.css`): toggle button reset (full width, flex, transparent), chevron transition, hover/focus-visible states, aur `:has(+ .collapse:not(.show))` se collapsed heading ka extra bottom margin hata diya.
- **Price slider fix:** panel default collapsed hone se jQuery UI slider hidden container me init hota tha (width 0 → handle drag galat). Isliye slider options ko `priceSliderOptions(values)` function me extract kiya aur `shown.bs.collapse` par slider ko current values ke saath `destroy` + re-init karte hain — sirf pehli baar expand hone par sahi width milti hai.

**Files changed:**
- `resources/views/shop/index.blade.php` — 3 widget headers + collapse wrappers (default closed) + slider re-init handler
- `public/assets/front/css/custom.css` — `.widget-collapse-toggle` styles

**File Revisions:** koi record nahi — watcher last entry **2026-10-23 13:34:44 (rev 14491)** ke baad band hai. (`sudo systemctl restart file-watcher` → `php artisan file:audit`)

**Verification:**
- `php artisan view:cache` → "Blade templates cached successfully" (syntax OK).
- `GET /new-steve/search` → **200**; HTML me teeno panels `class="collapse"` (bina `show`) + `aria-expanded="false"` render ho rahe hain — yaani default closed. `collapse show` panels ki count = **0**.
- `bootstrap.bundle.min.js` page par load ho raha hai, isliye `data-bs-toggle="collapse"` data-api kaam karega.
- Price panel expand → `shown.bs.collapse` handler slider ko visible width ke saath re-init karta hai (values preserve).
- Nested category `+/-` toggles (jo pehle se collapse use karte hain) outer collapse ke andar bhi intact hain.

## 323. (2026-10-27): Searchable Selects — Options Zyada Hon To Search Box

**Problem:** Jin `<select>` forms me options bahut zyada hain (admin product form ka Category select ~21,000 options, Brand ~300+, shop page ka Brand 305, Year 125), unme native dropdown se option dhoondhna practically impossible tha. Aage options badhne wale selects bhi cover karne the.

**Solution:** Naya reusable vanilla-JS enhancer `searchable-select.js` — koi library nahi, koi markup change nahi:

- **Threshold-based:** sirf wahi `select.form-select` enhance hote hain jinke options `>= 10` hon (default). Per-select override: `data-searchable-min="N"`; skip: `data-searchable="off"`.
- **Native `<select>` DOM me hi rehta hai** (off-screen, `display:none` nahi) — is liye form submit, HTML5 `required` validation, aur existing saare change handlers (jQuery included: brand auto-submit, sort, vehicle form) bilkul waise hi kaam karte hain.
- **UI:** trigger (theme ka `.form-select` look + existing chevron) → click par panel with search input + filtered list. Panel `document.body` me `position:fixed` append hota hai, is liye `table-responsive`/modal/off-canvas me clip nahi hota; viewport ke hisaab se neeche/upar khulta hai.
- **Keyboard:** Enter / Space / ArrowDown se open, ArrowUp-Down se navigate, Enter se select, Esc se close, Tab se close. Open hone par currently selected option highlight hota hai.
- **Dynamic options:** per-select `MutationObserver` (options add/remove, disabled/required change) + document-level observer (jo select pehle threshold se chhota tha aur baad me bada ho gaya — e.g. AJAX se aane wale makes/models) → khud enhance/refresh ho jata hai.
- **Performance:** ek waqt me max **200 rows** render hote hain + footer hint ("Showing first 200 of N matches — type to narrow down"). Empty search par 200 rows + type karte hi poore 21k options par filter (21,400 options: open ~0ms, filter ~0ms measured).
- **A11y:** `role="combobox"` + `aria-expanded` / `aria-controls` / `aria-activedescendant`, list `role="listbox"`, options `role="option"` + `aria-selected`; disabled select par `aria-disabled` + `tabindex="-1"`. Required select invalid hone par panel khul kar focus control par aa jata hai.
- `.nice-select` / address fields ke `.address-suggest` widgets ko chhoda nahi (unka apna searchable implementation already hai).

**Files changed:**
- `public/assets/front/js/searchable-select.js` — naya enhancer (vanilla JS, ~430 lines)
- `public/assets/front/css/searchable-select.css` — naya stylesheet (panel, search input, list, group/empty/footer states)
- `resources/views/layouts/app.blade.php` — CSS + JS include (form-select.js ke baad)
- `resources/views/admin/layouts/app.blade.php` — CSS + JS include (form-select.js ke baad)
- `TASKS.md` — ye entry

**File Revisions:** koi record nahi — watcher last entry **2026-10-23 13:34:44 (rev 14491)** ke baad band hai. (`sudo systemctl restart file-watcher` → `php artisan file:audit`)

**Verification:**
- `node --check public/assets/front/js/searchable-select.js` → OK; `php artisan view:cache` → "Blade templates cached successfully".
- `GET /new-steve/search` → **200**; dono assets include ho rahe hain. Rendered DOM (headless Chrome) me **2 selects enhance** hue: Brand (305 options, `min-width:180px` inline style se) aur Year (125 options) — `ss-control` + `ss-panel` present.
- **E2E (headless Chrome, real page):** Vehicle panel expand → Year search "2021" → 1 row → select → `value=2021`, label `2021`. Brand search "555" → 1 row → select → existing auto-submit chala → URL `…/search?brand=555`.
- **Unit-ish (headless Chrome):** 12-option select — filter `ap` → 4 rows (Apple/Apricot/Grape/Pineapple), click → `value=apple`, `FormData` me value, label update, panel close, `change` sirf value badalne par; keyboard ArrowDown+Enter → `mango`; Esc → close; disabled select → `tabindex="-1"`, no open.
- **Dynamic:** 2-option select pehle enhance nahi hua, 12 options add hone par automatically enhance ho gaya.
- **Perf:** 21,401-option select — open 0ms (200 rows rendered), filter "Item 21000" → 1 match, footer hint visible.
- **Fallback:** `nice-select`/address-fields selects untouched (unka apna search UI), aur `select` without search needs native hi rehta hai.

## 324. (2026-10-27): Price Filter — Displayed Price (Commission Included) Par Lagao

**Problem:** `?min_price=0&max_price=172` par `Rad / Cond Fan Assy. $190.29` list ho raha tha.

**Root cause:** Filter DB ki base `cost` par lag raha tha, lekin card par price `buyerPrice($p->price, $p)` se dikhti hai = `cost * (1 + commission/100)` (global commission **10%**, product-level override ho to wo).

- Product `295342` (`rad-cond-fan-assy-75638`): `cost = 172.99`, commission = NULL → displayed `$190.29`.
- Filter condition `cost <= 172 + 0.999999` → `172.99 <= 172.999999` ✅ pass → product list ho gaya, jabki customer ko `$190.29` dikh raha tha.
- Isi tarah `sort=price_asc/desc` bhi `cost` par tha (per-product commission alag hone par displayed order galat), aur slider bounds `MAX(cost)/MIN(cost)` se ban rahe the (displayed range se kam).
- Bonus bug: slider label `currency_format($sliderMin)` — `$sliderMin` pehle hi `* $rate` tha, is liye non-USD currency me **double conversion** ho rahi thi.

**Fix:** Naya private helper `buyerPriceExpression()` (ShopController) — bilkul `buyerPrice()` jaisa SQL expression:

```sql
(cost * (1 + (LEAST(GREATEST(COALESCE(commission, 10.00), 0), 100) / 100)))
```

- `min_price` / `max_price` ab isi expression par `whereRaw` (currency conversion pehle jaisa: `price / rate`).
- `sort=price_asc|price_desc` ab displayed price par.
- Price slider range (`catalog.price_range` → **`catalog.price_range_v2`**, cache auto-invalidate) ab displayed price ka `MIN/MAX`.
- Slider label ab `{{ $currencySymbol }}{{ number_format($sliderMin, 2) }}` (double conversion khatam).

**Files changed:**
- `app/Http/Controllers/ShopController.php` — `buyerPriceExpression()`, filter + sort + price range
- `resources/views/shop/index.blade.php` — slider label
- `TASKS.md` — ye entry

**File Revisions:** koi record nahi — watcher last entry **2026-10-23 13:34:44 (rev 14491)** ke baad band hai. (`sudo systemctl restart file-watcher` → `php artisan file:audit`)

**Verification:**
- `?min_price=0&max_price=172&sort=price_desc` → **200**; `rad-cond-fan-assy-75638` (cost 172.99) aur `rad-cond-fan-assy-76170` (cost 263.01) dono page par **absent**; listing ki max displayed price **$173.00** (172.99 range ka rounding), pehle $190.29/$289.31 aa rahe the.
- Tinker: filter se `total = 463,552`, `max_displayed_in_result = 173.00`, `min = 0.07`; dono bad products excluded.
- `?min_price=100&max_price=172&sort=price_asc` → 24 cards sab `$100.00` (DB me cost 90.91 × 1.1 = 100.00 wale 24 products — data sahi).
- Slider: `#price-slider-max = 105727` (buyer-price max 105,727 × rate), label `$0.00 - $105,727.00`.
- Performance (no regression): buyer-price filter `3.87s` vs purana cost filter `4.01s`; slider range scan `2.54s` (1 din cache).
- Note: `max_price` ka purana `+0.999999` fudge baraqar hai — yaani max 172 par 172.99 tak (displayed $173.00) include hota hai. Strict `<= 172.00` chahiye to `+0.999999` hata dena hoga.

## 325. (2026-10-28): User Return — Qty to Return Validation (Client-Side)

**Problem:** Request Return page (`user.returns.create`) par sabhi "Qty to Return" inputs default `0` hote the. User agar sab 0 chhod kar Submit click karta tha to server validation (`items.*.qty => min:1`) fail hoti thi, page reload hota tha, aur koi inline error nahi dikhta tha — sirf server-side error aata tha jo achhi tarah se visible nahi tha.

**Fix:** Client-side validation add kiya taaki page reload na ho aur turant error dikhe:

- Qty inputs par class `return-qty` + `data-index` add kiya.
- Naya error div `#return-qty-error` (`d-none` by default): *"Please select at least one item with quantity greater than 0."`
- `@error('items')` block add kiya taaki server-side validation error bhi inline dikhe (pehle sirf `reason` ka error dikhta tha).
- Inline JS (`DOMContentLoaded`-safe IIFE):
  - Form submit par check karta hai ki koi bhi qty `> 0` hai ya nahi.
  - Agar sab `0` hain → `e.preventDefault()` + `e.stopPropagation()` (page reload nahi hota), error box show karta hai, sab `0` inputs par `is-invalid` class lagata hai (Bootstrap red border), pehle input par focus set karta hai.
  - Agar koi qty `> 0` hai → error box hide, highlights clear.
  - Har qty input par `input` listener: agar user kisi input me value `> 0` daalta hai to uska `is-invalid` hat jata hai aur agar koi bhi `> 0` ho to error box hide ho jata hai.

**Files changed:**
- `resources/views/user/returns/create.blade.php` — qty input class/data-index, `@error('items')`, `#return-qty-error`, inline JS

**File Revisions:** koi record nahi — watcher last entry **2026-10-23 13:34:44 (rev 14491)** ke baad band hai. (`sudo systemctl restart file-watcher` → `php artisan file:audit`)

**Verification:**
- `php artisan view:cache` → "Blade templates cached successfully" (syntax OK).
- Form action selector `form[action*="/returns/"]` store route `/user/returns/{orderId}` se match karta hai.
- Server-side backup validation `items.*.qty => required|integer|min:1` intact `ReturnController@store` me.
- Note: Page me login hona zaroori hai (`auth` middleware), isliye direct HTTP test login ke bina possible nahi.

## 326. (2026-10-28): Admin Return Request — Prevent Accidental Approve on Save

**Problem:** Admin "Process Request" page (`admin.returns.show`) ka Decision select me koi empty/placeholder option nahi tha. Jab return request ka status `pending` hota hai, to koi bhi option (`approved`/`rejected`/`refunded`) match nahi karta, is liye koi option `selected` nahi hota. Browser form submit karte waqt **pehla option (`Approve`)** default submit kar deta hai — agar admin koi change kiye bina "Save Decision" click karta to return request galt se approve ho jati thi.

**Fix:** Decision select me pehle ek disabled placeholder option add kiya:

```html
<option value="" disabled {{ $return->status === 'pending' ? 'selected' : '' }}>Select Decision...</option>
```

- `pending` status par placeholder `selected` hota hai — admin ko faura decision choose karna padta hai.
- Disabled option ki value submit nahi hoti, is liye bina select kiye submit par server validation (`status => required|in:approved,rejected,refunded`) fail hoti hai.
- `approved`/`rejected`/`refunded` status par wohi option `selected` rehta hai (current decision dikhta hai, change karna padega).
- `@error('status')` block add kiya taaki server validation error inline dikhe.

**Files changed:**
- `resources/views/admin/returns/show.blade.php` — Decision select placeholder + `@error('status')`

**File Revisions:** koi record nahi — watcher last entry **2026-10-23 13:34:44 (rev 14491)** ke baad band hai. (`sudo systemctl restart file-watcher` → `php artisan file:audit`)

**Verification:**
- `php artisan view:cache` → "Blade templates cached successfully".
- Server-side validation intact: `ReturnController@update` me `'status' => 'required|in:approved,rejected,refunded'` — empty value reject ho jati hai.
- Note: Page admin-only (`auth` + admin middleware), direct HTTP test login ke bina possible nahi.

## 327. (2026-10-28): Admin Return — Refund Amount Should Not Show on Reject

**Problem:** Admin "Process Request" page (`admin.returns.show`) par jab return request reject ki jati thi, tab bhi "Refund amount" me amount dikhta rehta tha (kyunki controller `refund_amount` input ki value save kar leta tha chahe status `rejected` ho). Ye inconsistent hai — reject par koi refund nahi hota, to amount dikhana galat hai.

**Decision:** Reject karne par "Refund amount" **amount nahi dikhna chahiye** — ye sirf `approved` / `refunded` status par show hona chahiye. Reject par `refund_amount = null` ho aur UI me `—` dikhe.

**Fix:**
1. **Controller** (`app/Http/Controllers/Admin/ReturnController.php`): `update()` me — agar status `rejected` ho to `refund_amount = null` set kiya jata hai; `approved`/`refunded` par input value save hoti hai.
2. **View** (`resources/views/admin/returns/show.blade.php`):
   - Decision select me pehle se hi disabled placeholder (`Select Decision...`) add ho chuka hai (Task #326).
   - Refund amount input par `data-decision` + `refund-amount-input` class add ki; `pending`/`rejected` status par input `disabled` rehta hai.
   - Inline JS: decision change hone par `rejected` select karne se refund input disable + clear ho jata hai; `approved`/`refunded` select karne se enable ho jata hai (default order total load hota hai agar empty ho).
   - Return Details display card pehle se null par `—` dikhata hai, is liye controller se null set karne par UI sahi dikhega.

**Files changed:**
- `app/Http/Controllers/Admin/ReturnController.php` — refund_amount null on reject
- `resources/views/admin/returns/show.blade.php` — refund input disabled state + JS toggle
- `TASKS.md` — ye entry

**File Revisions:** koi record nahi — watcher last entry **2026-10-23 13:34:44 (rev 14491)** ke baad band hai. (`sudo systemctl restart file-watcher` → `php artisan file:audit`)

**Verification:**
- `php artisan view:cache` → "Blade templates cached successfully"
- `php -l app/Http/Controllers/Admin/ReturnController.php` → No syntax errors
- Server-side validation intact: `'refund_amount' => 'nullable|numeric|min:0'`, reject par null override hota hai
- Stock restore sirf `refunded` status par (pehle se intact)
- Note: Page admin-only, direct HTTP test login ke bina possible nahi.

## 328. (2026-10-28): Admin Panel - Generic API Integrations + Courier Auto-Shipment

**Goal:** Admin panel mein "Courier/API Integration Configuration" system banana taaki bina code edit kiye koi bhi API (Courier, Payment, SMS, WhatsAP, Email, Other) configure kiya ja sake. Saath hi courier APIs (Shiprocket/Delhivery/Pickrr/Custom) se tracking number/carrier auto-generate karke order ship ho sake.

**DB / Model:**
- Migration: `2026_10_28_105100_create_api_integrations_table.php`
  - `type` enum(courier,payment,sms,email,whatsapp,other), `provider`, `name`, `base_url`, `auth_type`, `credentials` (longText encrypted), `endpoints` (JSON), `config` (JSON), `description`, `is_active`, `is_default`, `priority`, `last_tested_at`, `last_test_result`
- Model: `app/Models/ApiIntegration.php`
  - Credentials auto-encrypt/decrypt via `Crypt`, JSON cast helpers, `getDefaultCourier()`, `getActiveCourier()`

**Admin Controller / Routes:**
- Controller: `app/Http/Controllers/Admin/ApiIntegrationController.php` (CRUD, toggle, setDefault, test, getProviderFields)
- Routes (admin): `GET/POST/PUT/DELETE /admin/api-integrations/*`, `POST /admin/api-integrations/{id}/toggle`, `POST /admin/api-integrations/{id}/set-default`, `POST /admin/api-integrations/{id}/test`, `GET /admin/api-integrations/get-provider-fields`
- Sidebar: `resources/views/admin/partials/sidebar.blade.php` -> Settings mein `API Integrations` link add

**Admin Views:**
- `resources/views/admin/api-integrations/index.blade.php` (list, status/default/test actions)
- `resources/views/admin/api-integrations/create.blade.php` (type/provider dynamic, credentials dynamic)
- `resources/views/admin/api-integrations/edit.blade.php` (update + test result)

**Courier Automation Structure:**
- Contract: `app/Contracts/CourierInterface.php` (authenticate, createShipment, getTracking, cancelShipment, getCouriers)
- Manager: `app/Services/Couriers/CourierManager.php` (default/active courier resolve)
- Services: `ShiprocketService`, `DelhiveryService`, `PickrrService`, `CustomCourierService`
- Admin Order: `app/Http/Controllers/Admin/OrderController.php` -> `shipViaApi($id)`, route `POST /admin/orders/{id}/ship-api`, button `Ship via API` in `resources/views/admin/orders/show.blade.php`
  - API se shipment create hone par `tracking_number`, `tracking_carrier` auto-save, status `shipped`, `recordStatusChange('shipped')`, mail/notification trigger

**Supported Providers (configurable):**
- Courier: shiprocket, delhivery, pickrr, nimbuspost, clickpost, ecomexpress, custom
- Payment: razorpay, stripe, payu, cashfree, ccavenue, custom
- SMS: msg91, textlocal, twilio, fast2sms, custom
- WhatsApp: meta, interakt, wati, gupshup, custom
- Email: smtp, mailgun, sendgrid, brevo, custom
- Other: custom

**Verification:**
- `php artisan migrate --force` -> `2026_10_28_105100_create_api_integrations_table` DONE
- `php artisan view:cache` DONE
- `php -l` on controllers/services passed
- Routes registered: `admin.api-integrations.*`, `admin.orders.ship-api`

**Notes:**
- Credentials Laravel Crypt se encrypt hote hain
- Generic system (future mein aur APIs add ho sakti hain)
- Agar koi active default courier nahi hai to `Ship via API` error show karega
- Custom API ke liye base_url + endpoints configure karke use kiya ja sakta hai

- [x] Vehicle widget added to home page hero banner (year/make/model filter) with dynamic makes/models JS.

## 329. (2026-10-28): Page Builder Merged in Website (Already Integrated)
- Package already integrated in `new-steve` via composer path repo (`../laravel-page-builder`) as `stevestore/laravel-page-builder`.
- Registered via `SteveStore\PageBuilder\PageBuilderServiceProvider` in `bootstrap/providers.php`.
- Models `App\Models\Page` & `App\Models\Blog` use `SteveStore\PageBuilder\Traits\HasBlocks`.
- Routes active: `admin/page-builder/*` (`page-builder.*`), views published/assets loaded.
- Admin sidebar has "Page Builder" link; admin pages layout supports page builder UI.
- Config: `config/page-builder.php` published/merged. Assets published to `public/vendor/page-builder/`.
- Migration `2026_07_16_000000_add_content_blocks_to_tables.php` adds `content_blocks` JSON to `pages`/`blogs` if missing.
- Frontend rendering uses `<x-page-blocks>` / `page-builder` components as per package.

## 330. (2026-10-28): User Profile Update - Country/State/City/Zip/Address Not Required
- Controller: `app/Http/Controllers/UserController.php@updateProfile` - removed `required` from phone/address/city/country, made postal_code nullable, read optional fields safely
- View: `resources/views/user/profile-edit.blade.php` - pass `required => ['full_name']`, hide phone via `withPhone => false`, hide default checkbox
- Address fields partial remains reusable; user profile now only requires Full Name + Email

## 330. (2026-10-28): User Profile Update - Country/State/City/Zip/Address Not Required
- Controller: `app/Http/Controllers/UserController.php@updateProfile` - removed `required` from phone/address/city/country, made postal_code nullable, read optional fields safely
- View: `resources/views/user/profile-edit.blade.php` - pass `required => ['full_name']`, hide phone via `withPhone => false`, hide default checkbox
- Address fields partial remains reusable; user profile now only requires Full Name + Email

## 331. (2026-10-29): Category URLs - Use /category/{slug} Instead of /category/parent/child
- Routes: `/category/{parent}/{child}/{subchild?}` now redirects (301) to `/category/{slug}` using child/subchild slug
- Controller `ShopController@category` already handles any category by slug (resolves parent/sub/child)
- Views updated: header mega menu, mobile menu, shop sidebar/breadcrumbs use `route('category', ['slug' => ...])`

## 332. (2026-10-29): Category URLs - Keep /category/parent/child/... Working
- Route `/category/{parent}/{child}/{subchild?}` now uses `ShopController@categoryByPath` (same page as `category`, no redirect)
- Method finds category by path (parent->child->subchild) and renders `shop.index` with correct `currentCategory/currentSubcategory/currentChildcategory`

## 333. (2026-10-29): Category Page - Hide Products Without Usable Image
- `ShopController@category`, `ShopController@categoryByPath` now filter paginated collection to only products with `filled($product->image)` (same behavior as home page)
- Prevents showing products with no/broken images on category pages

## 334. (2026-10-29): Category Menu Counts - Only Products With Images
- `CategoryTree` already uses `Product::hasUsableImage()` for counts (verified)
- `Product::statusCountsByCategoryId()` already uses `hasUsableImage()`
- Counts in header/sidebar/shop filters now reflect products with usable images only

## 335. (2026-10-29): Category Page Count & Listing Fix - Filter By Image After Fetch
- Category listing now fetches all matching products with `hasUsableImage()` scope, then filters by `filled($product->image)` (covers S3/image resolution cases where scope may miss) and paginates manually
- Ensures "Hardware, Fasteners and Fittings" shows only products that actually have usable images and correct count

## 336. (2026-10-29): Category Counts - Count Only Products With Actual Images
- `CategoryTree` now counts products with `filled($product->image)` after loading (not just `hasUsableImage()` scope)
- Counts in header/shop filters now match the filtered product list on category pages

## 337. (2026-10-29): Fixed admin sidebar dropdown (data-bs-toggle typo, route names for export/commission).

## 338. (2026-10-29): Fixed product_type filter 500 when column missing.

## 339. (2026-10-30): Fix HTTP 500 / 512M OOM on all pages (category tree + category listing)
- Root cause 1: `CategoryTree::tree()` loaded all ~761k active products (`Product::where('status', true)->get(['id','category_id','main_image','images_json'])`) whenever the 6h cache was cold/cleared → 500MB+ RAM vs 512M limit → fatal 500 on every page (frontend + admin). Cache never survived because the rebuild itself died.
- Fix 1: counts now come from existing SQL aggregate `Product::statusCountsByCategoryId()->all()` (GROUP BY category_id); cache rebuild ~5s, ~80MB peak, 2.5MB cached JSON.
- Fix 2: `AppServiceProvider` view composer skips `CategoryTree` for `/admin/*` requests (only the storefront layout uses `$mobileCategoryTree`).
- Fix 3: reverted 333/335 `->get()->filter(filled($product->image))` pagination in `ShopController@category` / `@categoryByPath` — it loaded the whole category into memory (Brake = 106k products) and caused a second OOM on `/category/brake`. Back to SQL `paginate(24)` with `hasUsableImage()`, which matches header/sidebar counts and keeps peak memory ~120MB.
- Note: 47,388 products have local `main_image` paths whose files do not exist (`public/assets/images/thumbnails/` missing); they now count/appear like the shop listing (placeholder image) instead of crashing.
- Verified 200: `/`, `/search`, `/category/brake`, `/category/steering`, `/admin/dashboard`, `/admin/products`, `/admin/products/{id}/edit`, `/admin/images`, `/admin/product-bulk-upload`; no new memory fatals in logs.
- Verified 200: `/`, `/search`, `/category/brake`, `/category/steering`, `/admin/dashboard`, `/admin/products`, `/admin/products/{id}/edit`, `/admin/images`, `/admin/product-bulk-upload`; no new memory fatals in logs.

## 340. (2026-10-30): Product images — AWS first, placeholder fallback (no legacy stautoparts images)
- `Product::getImageAttribute()` now only returns: (a) `s3://autoparts-hub/...` source-bucket refs (or the equivalent S3 HTTP URLs), (b) S3 refs from `images_json`, or (c) images uploaded through this app's own media manager (`storage/uploads/...` / `uploads/...` that exist on disk).
- Legacy local filenames (`assets/images/thumbnails/...`), bare filenames, other local paths, and third-party http(s) URLs (e.g. `pfc.parts`) are treated as "no image" → `assets/images/placeholder.png`. Old stautoparts images are never used.
- Added `onerror` placeholder fallback to raw admin product `<img>` tags (products index/edit/show gallery, contacts) so S3 objects that are missing/deleted (403) fall back to the placeholder instead of a broken image.
- Verified: AWS product renders signed `autoparts-hub` URLs; `pfc.parts` product now renders placeholders (0 pfc refs); bare-filename products show placeholder; admin product pages 200.

## 341. (2026-10-30): Hide placeholder-image products from storefront listings
- `Product::scopeHasUsableImage()` tightened from "any non-empty image" to AWS-only SQL: `s3://autoparts-hub/...` / `autoparts-hub.s3[.region].amazonaws.com` refs (in `main_image` or `images_json`) plus own media-manager uploads (`uploads/...`, `storage/uploads/...`). Legacy local paths, bare filenames, and third-party URLs no longer qualify.
- Storefront listings/counts now exclude products that would render the placeholder: `/search`, `/category/{slug}`, category counts (`CategoryTree` / `statusCountsByCategoryId`), and category pages.
- Added `hasUsableImage()` to Home featured/random/explore-tab queries and product page related/similar queries so those sections still fill up with real-image products.
- Cleared `front_category_tree_v2` cache and rebuilt it (~3.5s). Brake tree count 100,902 == SQL listing count 100,902 (previously 151,267 unscoped).
- Verified: `/`, `/search?q=0052`, `/category/brake` return 200 with 0 placeholder product cards (24/24 and 48/48 AWS images). A known no-image product (`loadcontroller-...-25854`, category 1191) no longer appears in its category listing. Direct product URLs still render with the placeholder policy if opened (e.g. old links/bookmarks); only listings are hidden.
- Caveat: SQL can match an AWS reference whose object is missing/deleted; such a product can still show the placeholder via `onerror` until the object is restored or the row is cleaned. Sample of 80 random S3 objects all existed.
- [x] Cart qty capped to stock (server + JS); increase button disables at max stock; totals update on change.

## 342. (2026-10-30): Product Detail Qty — Matched Cart Design

**Change:** Product detail page ka quantity selector cart page ke design se match kiya: circular light minus/plus buttons aur centered quantity input. Existing stock limit aur total-price update behavior unchanged.

**Files changed:**
- `resources/views/product/show.blade.php` — updated quantity control markup and styles

## 343. (2026-10-30): Dedicated Commission History Records

**Change:** Added a dedicated `commission_histories` table for product-level and global commission changes. Records include scope, old/new commission, product reference, actor, IP address, and timestamps. Unchanged values do not create duplicate history entries.

**Files changed:**
- `database/migrations/2026_10_30_000001_create_commission_histories_table.php` — created the history table and indexes
- `app/Models/CommissionHistory.php` — added history model and record helper
- `app/Http/Controllers/ProductController.php` — records product commission changes transactionally
- `app/Http/Controllers/AdminController.php` — records global commission changes transactionally

**Verification:** Migration applied successfully; a temporary insert was verified and rolled back.
## 344. (2026-11-02): Storefront Listings — `images_json` No Longer Counts As Main Image

**Problem:** `Fast Orange® Antibacterial Foaming Hand Wash 7.5OZ` (product `358613`) ka `main_image` legacy bare filename (`95105_001.jpg`) hai, lekin `images_json` me valid S3 gallery image hai. `Product::scopeHasUsableImage()` `images_json` ko bhi match karta tha, is liye product shop/category listings me aa raha tha — jabke card par placeholder render hota tha.

**Fix:** `scopeHasUsableImage()` ab sirf `main_image` (S3 source bucket ya own `uploads/...`) ko visibility ke liye consider karta hai. `images_json` gallery ke liye reserved hai; wo main image nahi ban sakta.

**Impact:** Storefront listings se 253 aise products hide hue jo sirf `images_json` image ke bharose the (`493,932` → `493,679` active products). Category counts/CategoryTree same scope use karte hain, is liye wahan bhi automatically reflect hua.

**Files changed:**
- `app/Models/Product.php` — removed `images_json` match from `scopeHasUsableImage()`

**Verification:** Product `358613` ab `hasUsableImage()` query me `false` hai; `php artisan cache:clear` chalaya gaya taake cached category counts refresh hon.

## 345. (2026-11-02): No Main Image — Product Page 404 + Sitemap Exclusion

**Change:** Products jinke paas usable `main_image` nahi hai, unki product detail URL ab 404 return karti hai aur wo `sitemap.xml` me bhi list nahi hoti.

**Files changed:**
- `app/Http/Controllers/ProductController.php` — `show()` me `hasUsableImage()` scope + PHP-level guard (404)
- `app/Http/Controllers/ShopController.php` — unused `product()` method me same guard, consistency ke liye
- `app/Http/Controllers/HomeController.php` — `sitemap()` ki product query par `hasUsableImage()`

**Verification:**
- `Fast Orange® Antibacterial Foaming Hand Wash 7.5OZ` (`358613`): `/product/fast-orange-...-95105` → **404**
- Valid-image product (`breather-screw-valve-radiator-001-10-23148`) → **200**
- Sitemap query me product `358613` included nahi hai
- `SourceS3ImageUrlTest`: 5 passed

## 346. (2026-11-02): Cart Page — Product Image/Name Link

**Change:** Cart page ke har item ka image aur product name ab product detail page ka link hai. Cart item array me `slug` add kiya (add + refresh), taake old sessions me bhi refresh par link mil jaye. Agar slug missing ho (deleted product) to plain text fallback render hota hai.

**Files changed:**
- `resources/views/cart/index.blade.php` — image/name wrapped in product links with fallback
- `app/Http/Controllers/CartController.php` — `slug` stored in cart items and refreshed from DB

**Verification:** `php artisan view:cache` successful; PHP syntax check passed.

## 347. (2026-11-02): Footer Copyright `/` Link — Subdirectory Base Path Fix

**Problem:** Footer copyright setting me `<a href="/">` tha. Browser me leading `/` domain root hota hai, is liye link `http://localhost/` par ja raha tha instead of `http://localhost/new-steve/`.

**Fix:** `app/helpers.php` me `prefixAppBasePathInHtml()` add kiya — stored HTML ke root-relative `href`/`src`/`action` values me app ka base path prefix karta hai. Already-prefixed paths aur external URLs untouched rehte hain. Footer copyright rendering me ye helper apply kiya.

**Files changed:**
- `app/helpers.php` — `prefixAppBasePathInHtml()`
- `resources/views/layouts/app.blade.php` — footer copyright output par helper applied

**Verification:** `href="/"` ab `href="/new-steve/"` render hota hai (browser me `http://localhost/new-steve/`); `/shop` -> `/new-steve/shop`; external URLs unchanged.

## 348. (2026-11-02): Brand Pages — Clean `/brand/{slug}` URLs

**Change:** Brand links ab `/search?brand=autolite` ki jagah `/brand/autolite` use karte hain. Naya public route `GET /brand/{slug}` (`brand`) add kiya jo us brand ke products ko same shop listing UI (filters, sort, pagination, vehicle filter) ke saath dikhata hai.

**Files changed:**
- `routes/web.php` — `Route::get('/brand/{slug}', [ShopController::class, 'brand'])->name('brand')`
- `app/Http/Controllers/ShopController.php` — `brand()` method + `filterProducts()` route-brand support; `currentBrand` view ko pass
- `resources/views/shop/index.blade.php` — brand title, breadcrumb (`Home > Brands > Brand`), selected brand filter + canonical clear URL
- `resources/views/brands/index.blade.php` — brand card links new route par
- `resources/views/home/index.blade.php` — home brand links new route par
- `resources/views/product/show.blade.php` — product detail "Brand" ab brand page link
- `app/Http/Controllers/HomeController.php` — sitemap me active brands add

**Behavior:**
- `/brand/autolite` → 200, sirf Autolite products (24 per page)
- `/brand/autolite?brand=bbr` → 302 `/brand/bbr` (URL canonical)
- `/brand/autolite?brand=` → 302 `/search` (filter clear)
- Missing brand → 404
- `/search?brand=autolite` old URL abhi bhi kaam karta hai

**Verification:** Brand page 200; 24/24 product cards Autolite brand ke; title `Autolite - Steve Autoparts`; breadcrumb correct; blade cache successful.

## 349. (2026-11-02): Brand URL Generation — Guard Empty Brand Slugs

**Problem:** `/brands` page 500 (`UrlGenerationException: Missing required parameter for [Route: brand] [Missing parameter: slug]`) kyunki `brands` table me 94 rows ka `slug` empty hai. `route('brand', ['slug' => ''])` required parameter missing throw karta hai.

**Fix:**
- `BrandController@brands()` — public brands list se empty `name`/`slug` rows exclude
- `HomeController@index()` — home "Top Brands" query bhi sirf valid `name` + `slug` brands leti hai
- `resources/views/brands/index.blade.php` — link generation par `filled($brand->slug)` guard + `/brands` fallback
- `resources/views/home/index.blade.php` — same guard

**Verification:** `http://192.168.130.54/new-steve/brands` → **200** (pehle 500), home → **200**, brand links valid slugs ke saath (`/brand/us-speedo`, `/brand/gates`, etc.).


## 350. (2026-11-02): Admin Payment Methods — Provider Credentials

**Change:** Added a Payment Methods settings page with credential cards for the providers in the supplied reference: PayPal, Stripe, MercadoPago, bKash, Nagad, SSLCommerz, Aamarpay, Iyzico, Instamojo, PayStack, PayHere, N-Genius, VoguePay, RazorPay, Authorize.Net, and Payku. Secret values are encrypted before storage and never rendered back into form inputs; blank secret fields preserve the saved value. Sandbox toggles and provider-specific fields are included where applicable.

**Files changed:**
- `app/Http/Controllers/Admin/PaymentMethodSettingsController.php` — provider field definitions, validation, encrypted credential persistence
- `resources/views/admin/settings/payment-methods.blade.php` — credential management UI
- `routes/web.php` — payment-method page and provider update routes
- `app/Http/Controllers/AdminController.php` — All Settings tile links to Payment Methods
- `resources/views/admin/partials/sidebar.blade.php` — sidebar link points to Payment Methods settings

**Behavior note:** This page stores gateway credentials; it does not add payment processing SDKs. Checkout continues to use its existing COD/card/PayPal flow, and other listed gateways require separate checkout integrations before they can accept payments.

**Verification:** PHP syntax, registered routes, Blade compilation, and server-side rendering of all 16 provider cards passed. Storefront returned HTTP 200; the admin page redirects unauthenticated users to login.

## 351. (2026-11-03): Checkout Delivery Info — Product Image/Name Link

**Change:** Checkout ke delivery-info step me cart items ka image aur product name ab product detail page par link karte hain, bilkul cart page (#346) ki tarah. Cart array me pehle se `slug` available hai (`CartController@refreshCartPrices()`), is liye controller change ki zaroorat nahi padi.

**Files changed:**
- `resources/views/checkout/delivery-info.blade.php` — image + name ko `route('product', $item['slug'])` link me wrap kiya; slug missing hone par plain fallback

**Verification:** Blade view compile/cache successful; product link cart page ke same URL pattern (`/product/{slug}`) par jaata hai.

## 352. (2026-11-03): General Settings — Footer Configuration Included

**Problem:** Admin "General Settings" page sirf header configuration dikha raha tha; footer columns manager alag "Footer Settings" page par tha. User ko General Settings me header aur footer dono ki settings chahiye thi.

**Fix:**
- Footer columns manager ko reusable partial `resources/views/admin/settings/partials/footer-columns.blade.php` me extract kiya.
- "General Settings" (`admin.settings.header`) page par Header Configuration ke baad **Footer Configuration** card include kiya; dedicated Footer Settings page bhi same partial use karta hai.
- Page title `Header Settings` → `General Settings`; All Settings hub me General group ke andar "Footer Settings" tile add kiya.
- Footer form me hidden `return_to` field add kiya, taake General Settings se save karne par wahi page par redirect ho (footer page se save karne par footer page par).

**Files changed:**
- `resources/views/admin/settings/partials/footer-columns.blade.php` — new shared partial (form + styles + JS)
- `resources/views/admin/settings/footer.blade.php` — partial include
- `resources/views/admin/settings/header.blade.php` — title General Settings + Footer Configuration section
- `app/Http/Controllers/AdminController.php` — General group tile + footer save redirect handling

**Verification:** `php -l` passed, `php artisan view:cache` successful. Authenticated render check: `/admin/settings/header` → 200 with `Footer Configuration`, footer form/JS present, nav menu intact; `/admin/settings/footer` → 200.

## 353. (2026-11-03): Remove Separate Footer Settings Page

**Change:** Footer settings ab General Settings page ka part hain, is liye alag "Footer Settings" page/menu ki zaroorat nahi rahi. Separate entry ko sidebar aur All Settings hub dono jagah se hata diya; dedicated page/route bhi remove kar diya.

**Files changed:**
- `resources/views/admin/partials/sidebar.blade.php` — top-level "Footer Settings" link removed
- `app/Http/Controllers/AdminController.php` — All Settings ka Footer Settings tile + unused `footerSettings()` method removed; footer save ab General Settings par redirect karta hai
- `routes/web.php` — `GET /admin/settings/footer` route removed (POST update route intact)
- `resources/views/admin/settings/footer.blade.php` — deleted (footer UI shared partial se General Settings me aa chuka hai)
- `resources/views/admin/settings/partials/footer-columns.blade.php` — `return_to` hidden field removed

**Verification:** `/admin/settings/header` → 200 with Footer Configuration, footer form/JS and nav menu; `/admin/settings` → 200 with no "Footer Settings" tile; sidebar me "Footer Settings" link nahi hai; `admin.settings.footer.update` POST route intact.

## 354. (2026-11-03): SEO Integration & Admin SEO Settings

**Change:** Storefront SEO ko admin-managed banaya. Global SEO settings page add ki gayi (search appearance, verification, analytics, robots/sitemap), meta-tags ka hardcoded `noindex` bug fix kiya, aur analytics/verification tags storefront layout me wire kiye.

**Files changed:**
- `app/Http/Controllers/AdminController.php` — `seoSettings()` / `updateSeoSettings()` + All Settings me SEO group
- `resources/views/admin/settings/seo.blade.php` — naya SEO settings page (meta defaults, OG image, Search Console/Bing/Pinterest verification, GA4/GTM/Pixel, robots & sitemap toggles)
- `routes/web.php` — `admin.settings.seo` GET/POST routes + public `/robots.txt` route
- `resources/views/admin/partials/sidebar.blade.php` — SEO Settings link
- `resources/views/partials/meta-tags.blade.php` — per-page `robots` respected; global meta defaults, verification tags, OG/Twitter image and handle; default noindex for private pages
- `resources/views/layouts/app.blade.php` — SEO analytics partials included in head/body
- `resources/views/partials/seo-analytics.blade.php` — GA4, GTM, Facebook Pixel head snippets
- `resources/views/partials/seo-analytics-body.blade.php` — GTM/Pixel noscript fallbacks
- `app/Http/Controllers/HomeController.php` — dynamic `robots()` + sitemap enable/disable checks
- `app/helpers.php` — `defaultRobotsTxt()` helper
- `public/robots.txt` — removed (now served dynamically so admin edits apply)

**Verification:** Admin `/admin/settings/seo` → 200; settings save redirect + persisted values verified; home `/` → `index, follow`; cart/login → `noindex, nofollow`; `/robots.txt` → 200 with dynamic sitemap URL; indexing off → home noindex + robots `Disallow: /`; sitemap off → `/sitemap.xml` 404; `php artisan view:cache` passes.

## 355. (2026-11-03): XML Sitemap Link in Admin

**Change:** SEO settings ke "Robots & Sitemap" section me enabled sitemap ka direct `/sitemap.xml` link add kiya. All Settings hub ke SEO group me bhi "XML Sitemap" tile add kiya jo sitemap new tab me kholta hai. Sitemap setting enabled (`seo_sitemap_enabled=1`) verify kiya.

**Files changed:**
- `resources/views/admin/settings/seo.blade.php` — sitemap toggle ke neeche clickable sitemap URL
- `resources/views/admin/settings/index.blade.php` — tiles ko `url` + `external` support (new tab links)
- `app/Http/Controllers/AdminController.php` — SEO group me "XML Sitemap" tile

**Verification:** `/admin/settings` and `/admin/settings/seo` → 200 with `sitemap.xml` link + `target="_blank"`; `/sitemap.xml` → 200, `application/xml`; `seo_sitemap_enabled=1`.

## 356. (2026-11-03): Sitemap/robots URL Auto-Follows Live Domain

**Problem:** `seo_robots_txt` setting me `Sitemap: http://localhost/new-steve/sitemap.xml` hardcoded save ho gaya tha, is liye site move hone par robots.txt purana domain dikhata. Sitemap XML `<loc>` URLs pehle se request host use karte the, lekin stored robots content portable nahi tha.

**Fix:**
- Default robots content me sitemap URL ab `{{sitemap}}` placeholder ke roop me store hota hai.
- `normalizeRobotsTxtForStorage()` save ke waqt localhost/current-host sitemap URL ko placeholder me badalta hai; external sitemap URLs untouched rehte hain.
- `renderRobotsTxt()` request ke waqt placeholder ko current `url('/sitemap.xml')` se replace karta hai, aur purane localhost/current-host sitemap lines ko bhi current domain par rebase karta hai.
- Sitemap disabled hone par robots se `Sitemap:` line automatically hat jaati hai.
- Admin SEO page par note add kiya ke sitemap URL current domain follow karta hai.
- Existing DB value ko placeholder form me migrate kiya (`seo_robots_txt`).

**Files changed:**
- `app/helpers.php` — `defaultRobotsTxt()`, `robotsLocalHosts()`, `normalizeRobotsTxtForStorage()`, `renderRobotsTxt()`
- `app/Http/Controllers/HomeController.php` — `/robots.txt` ab `renderRobotsTxt()` use karta hai
- `app/Http/Controllers/AdminController.php` — SEO settings load par rendered robots, save par portable normalization
- `resources/views/admin/settings/seo.blade.php` — auto-domain note

**Verification:** Web-request simulation `http://shop.example.com/robots.txt` → `Sitemap: http://shop.example.com/sitemap.xml`; `https://shop.example.com/sitemap.xml` → sab `<loc>` URLs `https://shop.example.com/...`, `localhost` count 0; `php -l` + `php artisan view:cache` passed.

## 357. (2026-11-03): Search Category Menu URLs Made Domain-Independent

**Problem:** Search bar category dropdown ka `search_category_menu` setting absolute URLs store kar raha tha (`http://localhost/new-steve/new-steve/category/...`), jo live domain par toot jaate aur duplicate base path bhi bana rahe the.

**Fix:**
- Menu URLs ab app-relative paths (`/category/{slug}`) store hote hain; host/base path render time par `url()` se add hota hai.
- Admin category picker `route('category', $slug, false)` use karta hai, is liye current host DB me save nahi hota.
- Legacy absolute URLs ko `normalizeAppUrlPath()` se clean kiya; existing setting ko relative paths me migrate kiya.
- Storefront dropdown ab `url($path)` se link banata hai.

**Files changed:**
- `app/helpers.php` — `normalizeAppUrlPath()`
- `app/Http/Controllers/AdminController.php` — search category settings load/save normalization
- `resources/views/admin/settings/search-categories.blade.php` — relative `data-url`
- `resources/views/admin/settings/search-categories-tree.blade.php` — relative `data-url`
- `resources/views/layouts/app.blade.php` — dropdown links `url()` se render

**Verification:** Migration ke baad `search_category_menu` URLs `/category/...`; `normalizeAppUrlPath()` legacy `/new-steve/new-steve/...` ko `/category/...` banata hai; `url()` live host ke saath full URL deta hai; `php -l` + `php artisan view:cache` passed.

## 358. (2026-11-04): User Login URL Changed to `/users/login`

**Change:** Public login GET/POST routes ab `/users/login` use karte hain. Existing `login` and `login.submit` route names preserve kiye, so internal links and authentication redirects use the new path. `/login` ka redirect route add nahi kiya. Logout and the product-review login link were updated to use the named route.

**Files changed:**
- `routes/web.php` — moved the public login GET/POST routes to `/users/login`; updated the legacy `/use/login` redirect target
- `app/Http/Controllers/AuthController.php` — logout now redirects through the named login route
- `resources/views/product/show.blade.php` — review authorization message links through the named login route

**Verification:** `route:list` shows GET/POST `users/login` and no `/login` route; `route('login')` generates `/new-steve/users/login`; PHP syntax checks and `git diff --check` passed.

## 359. (2026-11-04): Pickup Points Select/Filter Form Fix

**Problem:** Admin → Pickup Points page ke "Name" search input par galti se `form-select` class lagi thi, is liye text input select/dropdown jaisa arrow ke saath render ho raha tha. Checkout delivery-info page par pickup-point select validation error ke baad reset ho jata tha aur selected pickup point retain nahi hota tha.

**Fix:**
- Admin pickup points filter input: `form-select` → `form-control`.
- Checkout pickup select: `form-control` → `form-select` (baaki selects ke saath consistent; 10+ pickup points par searchable-select widget auto-enable hota hai).
- `shipping_method` aur `pickup_point_id` ab `old()` / session se restore hote hain, is liye validation error ya back navigation ke baad bhi selection bani rehti hai.
- Pickup point validation error form par inline show hota hai.
- Home Delivery par switch karne par select clear hota hai aur searchable-select widget bhi sync hota hai.
- Initial hidden state selected delivery method ke hisaab se set hota hai.

**Files changed:**
- `resources/views/admin/settings/pickup-points/index.blade.php` — filter input class fix
- `resources/views/checkout/delivery-info.blade.php` — pickup select class, old-value retention, inline error, JS sync

**Verification:** Dono Blade views ke compile checks passed. Note: DB me abhi 0 pickup points hain, is liye checkout par "Pickup point" option tab tak nahi dikhega jab tak Admin → Settings → Pickup Points se kam se kam ek **Open** point add na ho.

## 360. (2026-11-05): Shipping Location Pages — Countries, States, Cities + Area-Wise Cost Integration

**Problem:** Admin shipping had Configuration, Zones and Carriers pages but was missing Shipping Countries, Shipping States and Shipping Cities pages (referenced autoparts setup). Also `countries`/`states`/`cities` tables had no `status` column and `cities` had no `cost` column, so area-wise shipping couldn't use city cost as reference does.

**Solution:** Added full CRUD for shipping countries/states/cities under Settings sidebar, wired status/cost into area-wise shipping, and kept existing checkout behavior unchanged (default method still `carrier_wise_shipping`).

### Database
- `database/migrations/2026_11_05_000003_add_status_and_cost_to_shipping_locations.php` — `status` (boolean default true) on `countries`/`states`/`cities`; `cost` (decimal 12,2) on `cities`. Migrated via `php artisan migrate --force`.

### Models
- `app/Models/Country.php` — `status` fillable/cast + `scopeActive()`
- `app/Models/State.php` — `status` fillable/cast + `scopeActive()`
- `app/Models/City.php` — `status`, `cost` fillable/cast + `scopeActive()` + `country()` relation

### Backend
- `app/Http/Controllers/Admin/ShippingLocationController.php` (NEW) — countries list+status toggle; states list/filter/create/edit/update/delete/status toggle (blocks delete if cities exist); cities list/filter/create/edit/update/delete/status toggle with cost field.
- `routes/web.php` — 13 routes: `admin.settings.shipping-countries.index/status`, `shipping-states.*`, `shipping-cities.*` under admin group.
- `app/Http/Controllers/LocationController.php` — states/cities AJAX now only return active records; cache keys versioned (`address_states_v2_*`, `address_cities_v2_*`).
- `app/Helpers/AddressHelper.php` — `countries()` reads active countries from DB with `Cache::rememberForever('address_countries_v2')`, hardcoded fallback retained.
- `app/Services/ShippingChargeService.php` — area-wise now matches active city (status=true, cost not null, city/state/country match) → uses `city.cost`; falls back to matching shipping zone `cost` if no city. Tests via tinker confirmed: city cost 55 applied, city null/inactive → zone cost fallback.
- `resources/views/admin/settings/shipping/configuration.blade.php` — area-wise note updated to point to Shipping Cities (with zone fallback); quick links now include Countries/States/Cities.

### Views (NEW)
- `resources/views/admin/settings/shipping/countries/index.blade.php` — search/filter by name/status, status toggle button (Visible/Hidden).
- `resources/views/admin/settings/shipping/states/index.blade.php` — search/filter by name/country/status, inline Add New State form, status toggle, edit/delete action buttons (`.action-btn` circular SVG style), delete blocked when cities exist.
- `resources/views/admin/settings/shipping/states/edit.blade.php` — edit state name/country/status.
- `resources/views/admin/settings/shipping/cities/index.blade.php` — search/filter by name/country/state/status, area-cost column, inline Add New City form with cost input.
- `resources/views/admin/settings/shipping/cities/edit.blade.php` — edit city name/state/cost/status.

### Sidebar
- `resources/views/admin/partials/sidebar.blade.php` — replaced disabled Shipping block with active links: Shipping Configuration, Shipping Countries, Shipping States, Shipping Cities, Shipping Zones, Shipping Carriers.

**Verification:** All new/changed PHP files pass `php -l`; migration ran OK; `php artisan view:clear && view:cache` OK; all routes registered (`route:list`); tinker verified area-wise quote returns city cost when set and zone cost when city missing/inactive.

## 361. (2026-11-05): Admin Sidebar Design Fix

**Problem:** Admin sidebar submenus looked plain — no icons on submenu items, parent submenus didn't auto-expand when a child was active, and collapsed sidebar (72px) showed no hover tooltips, making navigation unclear.

**Solution:** Polished the admin sidebar with submenu styling, auto-expand on active child, and collapsed-state hover tooltips.

### Files changed
- `resources/views/admin/partials/sidebar.blade.php`:
  - Added icons to all submenu items (`nav-item-sub` with `fas` icons): Products (All Products, Bulk Import/Export, Categories, Brands, Stock Management, Commission, Product Reviews), Shipping (Configuration, Countries, States, Cities, Zones, Carriers).
  - Parent submenu now auto-expands (`expanded`/`show`) when any child route is active, not just when parent route matches.
  - Products parent now also active for `admin.reviews.*`.
  - JS: wraps nav labels in `.nav-label` spans and auto-sets `data-bs-original-title` from visible label text for hover tooltips.
- `public/assets/front/css/backend.css`:
  - `.nav-item-sub` styles: padding, gap, font-size, hover/active states, left border accent.
  - `.submenu-arrow` rotate animation on expand.
  - Section separator borders between nav sections.
  - Collapsed state: hides submenu items, centers parent nav items, shows hover tooltip via `::after` using `data-bs-original-title`.

### Verification
- `php -l` clean on sidebar Blade file.
- `php artisan view:cache` compiled successfully.
- Sidebar renders with icons, active states, and collapsed tooltip hover.

## 351. (2026-11-05): Admin Sidebar Layout Fix

- Fixed content margin to match sidebar width (280px).
- Set nav-wrap direction to LTR.
- Added submenu arrow and moved it to far right.

## 362. (2026-11-05): Soft Delete Support for Shipping Data

- Added Laravel `SoftDeletes` support for countries, states, cities, shipping carriers, shipping zones, and shipping zone rates.
- Added migration `2026_11_05_000004_add_soft_deletes_to_shipping_tables.php` and applied it to the configured `new_steve` database.
- Deleting a shipping zone now soft-deletes its rates as well.
- Carrier/state creation can restore matching soft-deleted records; deleted zone rates are restored when re-added.
- Verified all six tables have `deleted_at`, shipping routes load, and PHP syntax checks pass. Feature tests could not run because PHP SQLite PDO is unavailable.

## 363. (2026-11-06): Contact Us "Contact Information" managed from admin

- Added `Settings > Contact Information` page (`admin.settings.contact` / `.update`) with address, phone, email, working hours, and Google Maps embed URL.
- Phone, email, and address reuse `header_phone`, `header_email`, and `header_address`, so header, footer, and Contact Us stay in sync.
- Added new settings `contact_working_hours` and `contact_map_embed`; defaults live in `config/contact.php`.
- `resources/views/pages/contact.blade.php` now reads settings instead of hardcoded values, supports multi-line address/hours, builds `tel:`/`mailto:` links, and hides the map when the embed URL is blank.
- Map field accepts either the Google Maps embed URL or the full iframe code (URL is extracted); only Google Maps hosts are accepted.
- Added the Contact Information tile to the All Settings hub and a sidebar link. Existing admin middleware already allows `master_admin`, `admin`, and `staff`.
- Verified Contact Us returns 200 with values from settings; admin view renders; map iframe extraction and invalid-URL rejection tested.

## 364. (2026-11-06): Make blog additional categories functional

- Blog category pages now include blogs where the category is either the primary category or an additional category (`blog_additional_category`).
- Added combined category counts: primary blogs + additional blogs, exposed as `total_blogs_count` via `BlogCategory::scopeWithBlogCounts()` and `additionalBlogs()`.
- Blog cards and the single blog page now list all assigned categories (primary + additional), deduplicated by `Blog::allCategories()`.
- Frontend blog queries eager-load `additionalCategories` to avoid N+1 queries.
- Admin blog create/edit now disables the primary category in the Additional Categories checkboxes and the server also filters it out before syncing.
- Admin blog category list now shows combined primary + additional blog counts.
- Deleted the existing redundant pivot row where the additional category matched the blog's primary category.
- Verified `/blog`, category pages (including a blog assigned only through an additional category), blog show, and tag pages return 200; combined counts and admin sync filtering tested.

## 365. (2026-11-06): Blog author change option

- Added nullable `author` column to `blogs` (migration `2026_11_06_000001_add_author_to_blogs_table.php`) and added it to the `Blog` fillable fields.
- Admin blog Create/Edit now has an **Author** field with suggestions from admin/staff users. Custom names are allowed; blank falls back to "Admin".
- Admin blog list now shows an Author column and supports sorting by author.
- Blog detail page shows the saved author ("By: ...") instead of the hardcoded "By: Admin".
- Verified create/edit/admin index views render, custom author appears on the frontend, and null author falls back to "Admin".


## 366. (2026-11-06): Add Admin-Managed Shipping Functionality

- Add shipping functionality that administrators can configure and manage from the admin panel.

## 367. (2026-11-06): Blog Author — Fetch Users + Logged-in User Default (NEW TASK)

**Status: Pending**

- Change the blog Author field to fetch/select users from the `users` table instead of free-text suggestions.
- On blog create, default the author to the currently logged-in admin/staff user.
- On blog edit, preselect the blog's saved author.
- Keep the "Admin" fallback when no author is selected.
- Decide whether to store `author_id` (FK to `users`) or keep the existing `author` name snapshot before implementation.

## 368. (2026-11-06): Admin Invoice Management (NEW TASK)

**Status: Completed**

- Add admin controls for managing invoice generation and configuration. The current invoice is generated as a PDF from order data with a fixed template.
- Implemented Invoice Settings in the admin panel to manage the logo, business details, primary color, standard/compact layout, invoice prefix/date format/title, note/terms/footer, display-only currency label, amount labels, and visibility of invoice sections.
- Generated order invoice PDFs now apply these settings while preserving order amounts and customer/order records.

## 369. (2026-11-06): Customer Invoice Availability Before Delivery

- Added `Order::canDownloadInvoice()` to decouple invoice availability from delivery status.
- Customer invoices are available for `processing`, `shipped`, and `delivered` orders; `pending` and `cancelled` orders are excluded.
- Prepaid orders require `payment_status = paid`; COD orders are eligible from `processing` onward because payment is collected on delivery.
- Order Details now shows a "Download Invoice" button only when eligible, and the Orders list hides the invoice action for ineligible orders.
- `OrderController@invoice` now enforces the same rule server-side with a 403 response for direct access to an unavailable invoice.
- Corrected the Orders list payment badge to use the actual `payment_status` instead of inferring paid/unpaid from the payment method.
- Verified allowed orders return a PDF, while pending, cancelled, and unpaid card orders are blocked; Order Details and Orders list render correctly.

## 370. (2026-11-06): Order Confirmation Email Delivery Setup (NEW TASK)

**Status: Pending**

- Order confirmation and order status emails are already implemented and queued to the customer's billing/account email.
- Current `.env` uses `MAIL_MAILER=log`, so emails are written to the log instead of being delivered to the customer's inbox.
- `QUEUE_CONNECTION=database` is enabled, but no queue worker is running; the `jobs` table currently has 25 pending order emails.
- `MAIL_FROM_ADDRESS` is still the placeholder `hello@example.com`.
- Configure real SMTP credentials and a valid sender address.
- Run a queue worker (Supervisor for production) and clear config after updating `.env`.
- Decide whether to discard the existing 25 stale queued order emails before starting the worker, so old order emails are not sent unexpectedly.
- Send a test order confirmation email and verify inbox delivery.

## 371. (2026-11-12): Shared Feature Switch Design via `_form-check.scss`

**Change:** Feature toggle switch ka design ab shared `resources/scss/_form-check.scss` source se manage hota hai. Admin layout `assets/front/css/backend.css` load karta hai, jahan ye shared styles compiled hain. `order.blade.php` me duplicate inline CSS hata diya, is liye Order Settings bhi same shared design use karta hai.

**Files changed:**
- `resources/views/admin/settings/order.blade.php` — duplicate `.feature-switch` inline `<style>` block removed
- `resources/scss/_form-check.scss` — source-of-truth note added

**Verification:** `php artisan view:cache` passed; Order Settings se inline duplicate CSS hat gaya. (Baad me #372 me yeh switches Bootstrap `form-check-input` par migrate kiye gaye.)

## 372. (2026-11-12): Feature Switches Migrated to Bootstrap `form-check-input`

**Change:** Custom `feature-switch` / `feature-switch-track` toggle design ko Bootstrap ke standard `form-check form-switch` + `form-check-input` se replace kiya. Ab Order Settings, Features activation, Payment Methods, Shipping Countries aur Shipping States sab same native switch design use karte hain; checked state `style.css` ke `--primary` theme color se aata hai.

**Files changed:**
- `resources/views/admin/settings/features.blade.php`
- `resources/views/admin/settings/order.blade.php`
- `resources/views/admin/settings/payment-methods.blade.php`
- `resources/views/admin/settings/shipping/countries/index.blade.php`
- `resources/views/admin/settings/shipping/states/index.blade.php`
- `public/assets/front/css/backend.css` — custom `.feature-switch` / `.feature-switch-track` styles removed
- `resources/scss/_form-check.scss` — old custom styles replaced with migration note

**Verification:** Authenticated render checks: Order Settings, Features, Payment Methods, Shipping Countries, Shipping States all `200`; har page par `form-check-input` present aur `feature-switch-track` count `0`; `php artisan view:cache` passed.

## 373. (2026-11-12): Default Language List Filter Clear Option

**Change:** Languages list ke search/filter form me active search hone par **Clear** button add kiya, jo bina query string ke languages index par wapas le jata hai. Button `Filter` ke saath inline hai aur baaki admin filter pages ke same `btn-outline-secondary` pattern ko follow karta hai.

**Files changed:**
- `resources/views/admin/settings/languages/index.blade.php` — filter action group me conditional Clear link added

**Verification:** `/admin/settings/languages` renders `200` without Clear link (no active filter); `/admin/settings/languages?search=en` renders `200` with Clear link; `php artisan view:cache` passed.

## 374. (2026-11-12): Currency Symbol Dropdown Options on Add/Edit Currency

**Change:** Add/Edit Currency form ke **Currency symbol** field ko text input se native `<select>` dropdown me convert kiya. Dropdown me har currency ka apna alag option show hota hai, symbol + code + name ke saath (for example `$ - USD (US Dollar)`, `kr - DKK (Danish Krone)`, `kr - NOK (Norwegian Krone)`, `kr - SEK (Swedish Krone)`). Same symbol wali currencies ko group nahi kiya gaya, kyunki unki exchange rate/value alag hoti hai. Edit karte waqt currency code ke basis par sahi option selected hota hai.

**Files changed:**
- `app/Http/Controllers/Admin/CurrencySettingsController.php` — `create()` / `edit()` ko unique currency symbol options pass kiye; `currencySymbolOptions()` helper added
- `resources/views/admin/settings/currency/form.blade.php` — symbol field ko `<select>` dropdown me convert kiya, selected value old input / edit currency se sync

**Verification:** `/admin/settings/currency/create` aur `/admin/settings/currency/{id}/edit` dono `200`; create par placeholder selected, edit par currency code ke hisaab se sahi option selected (DKK, NOK, SEK teeno alag alag options se verify kiye); 25 currency options + placeholder render ho rahe hain; `php artisan view:cache` passed.

## 375. (2026-11-12): Google reCAPTCHA v3 Support

**Change:** Google reCAPTCHA settings me v3 support add kiya. Ab admin **reCAPTCHA Version** select se `v2` ya `v3` choose kar sakta hai. v2 aur v3 ke keys alag hote hain, is liye dono ke separate site/secret key fields hain. Admin form me sirf selected version ka keys section show hota hai; version change karte hi JS v2/v3 fields ko turant toggle karta hai, aur page load par saved version ke hisaab se sahi section visible hota hai. v3 ke liye minimum score threshold bhi configurable hai. Storefront (`login`, `register`, `contact`) par selected version ke hisaab se ya to v2 checkbox widget render hota hai, ya v3 invisible token automatically generate hota hai. Server-side verification v3 me score aur action (`login` / `register` / `contact`) dono validate karta hai.

**Files changed:**
- `app/Services/Recaptcha.php` — version selection, v2/v3 keys, score + action verification
- `app/Http/Controllers/Admin/RecaptchaSettingsController.php` — version, v3 keys, v3 score fields save/validate
- `resources/views/admin/settings/google-recaptcha.blade.php` — version selector, separate v2/v3 key sections, score field, missing-key warning
- `app/Http/Controllers/AuthController.php` — login/register verification ko action pass kiya
- `app/Http/Controllers/ContactController.php` — contact verification ko action pass kiya
- `resources/views/partials/recaptcha-field.blade.php` — v2 widget / v3 hidden token render
- `resources/views/partials/recaptcha-scripts.blade.php` — v2/v3 API script + v3 token generation
- `resources/views/auth/login.blade.php`, `resources/views/auth/register.blade.php`, `resources/views/pages/contact.blade.php` — shared reCAPTCHA partials use kiye

**Verification:** Admin Google reCAPTCHA page `200`; saved version `v2` par v2 keys section visible aur v3 section `d-none`; saved version `v3` par v3 keys section visible aur v2 section `d-none`; version change toggle script present; v2 mode par login/register/contact pages par checkbox widget + v2 API script render hua; v3 mode par hidden `g-recaptcha-response` input + v3 render script render hua (v2 widget absent); settings save test me v2/v3 keys, encrypted secrets, version aur score correctly persist hue; `php artisan view:cache` passed. Live Google siteverify call test nahi kiya gaya (needs real v3 keys).

## 376. (2026-11-12): Stock Management Filter Clear Option

**Change:** Stock Management page ke filter form me **Clear** button add kiya. Button sirf tab dikhta hai jab `search`, `stock_filter` ya `threshold` me se koi filter active ho. Click karne par bina query string ke stock management page reset ho jata hai (default threshold aur low/all stock view). Button search button ke saath inline hai aur baaki admin filter pages ke `btn-outline-secondary` + `fa-times` pattern ko follow karta hai.

**Files changed:**
- `resources/views/admin/products/stock.blade.php` — filter form me conditional Clear link added

**Verification:** `/admin/products/stock` (no query) par Clear link nahi dikhta; `?search=test`, `?stock_filter=out_of_stock&threshold=10`, aur `?threshold=5` par Clear link dikhta hai aur base stock URL par point karta hai; sabhi requests `200`; `php artisan view:cache` passed.

## 377. (2026-11-12): Admin Product Reviews Rating Icons Match My Reviews

**Change:** Admin **Product Reviews** table ke Rating column me FontAwesome star icons ki jagah **My Reviews** page wale inline SVG stars use kiye. Har star `14×13` size, `viewBox="0 0 17 16"`, filled color `#EEAE0B` aur empty color `#E2E8F0` hai. Stars ko `d-flex align-items-center gap-1` wrapper me rakha, taake spacing My Reviews page jaisi consistent rahe.

**Files changed:**
- `resources/views/admin/reviews/index.blade.php` — Rating column ke `fas fa-star` icons ko inline SVG stars se replace kiya

**Verification:** `/admin/reviews` `200` render hua; rating 5 par 5 filled SVG stars render hue aur FontAwesome rating stars remove ho gaye; temporary rating 3 test me 3 filled + 2 empty SVG stars render hue; original review data restore kar diya; `php artisan view:cache` passed.

## 378. (2026-11-13): Page Builder Pages / Blog Posts Tabs

**Change:** Admin **Page Builder** index par **All / Pages / Blog Posts** tabs add kiye. Har tab apne count ke saath render hota hai (`All (35)`, `Pages (17)`, `Blog Posts (18)`) aur selected tab active state dikhata hai. `?type=page` aur `?type=blog` query string se list filter hoti hai, aur All tab par dono types wapas show hote hain. Empty state message bhi selected tab ke hisaab se change hota hai. Saath hi published view ka broken header markup (`<div class="d`) fix kiya, jis ki wajah se index ka content render nahi ho raha tha.

**Files changed:**
- `resources/views/vendor/page-builder/admin/page-builder/index.blade.php` — tabs, counts, type filtering, active state, dynamic empty state, fixed header markup
- `/var/www/html/laravel-page-builder/src/Http/Controllers/PageBuilderController.php`, `/var/www/html/laravel-page-builder/resources/views/admin/page-builder/index.blade.php` — package source me bhi same tabs/counts/filtering parity add ki (app ka active code published view hai)

**Verification:** `/admin/page-builder` `200` with counts All `35`, Pages `17`, Blog Posts `18`; `?type=page` par sirf Pages card render hua (17 rows) aur Pages tab active; `?type=blog` par sirf Blog Posts card render hua (18 rows) aur Blog Posts tab active; no-query par dono cards aur All tab active; `php artisan view:cache` passed.

## 379. (2026-11-13): reCAPTCHA Deactivated (No Keys Available)

**Change:** Filhal reCAPTCHA keys available nahi hain, is liye storefront forms par reCAPTCHA verification band kar diya. Admin **Google reCAPTCHA** settings me Login/Register/Contact teeno form toggles `0` kar diye. v2 site key aur encrypted secret settings me saved hain, taake future me keys milne par sirf toggles on karke reCAPTCHA dobara enable ho sake. Code already keys missing hone par auto-disable karta hai (`Recaptcha::enabled()` site/secret key check karta hai), lekin ab explicit toggles off hain.

**Files changed:** None (DB settings only: `recaptcha_login`, `recaptcha_register`, `recaptcha_contact` = `0`)

**Verification:** `Recaptcha::enabled()` login/register/contact ke liye `false`; `/users/login`, `/users/registration`, `/contact-us` sab `200`; teeno pages par `g-recaptcha` widget, `recaptcha/api.js` script aur hidden `g-recaptcha-response` token render nahi hua; admin Google reCAPTCHA page par teeno checkboxes unchecked aur missing-key warning nahi.

## 380. (2026-11-13): Google reCAPTCHA Reset Option

**Change:** Admin **Google reCAPTCHA** settings page par **Reset** button add kiya. Confirm dialog ke baad reset action v2/v3 site aur secret keys clear karta hai, version ko `v2` par reset karta hai, v3 score ko `0.5` par aur Login/Register/Contact teeno toggles ko off kar deta hai. Is se reCAPTCHA turant disable ho jata hai aur form clean state me wapas aa jata hai. Reset ke baad success message show hota hai.

**Files changed:**
- `app/Http/Controllers/Admin/RecaptchaSettingsController.php` — `reset()` method
- `routes/web.php` — `POST /admin/settings/google-recaptcha/reset` route
- `resources/views/admin/settings/google-recaptcha.blade.php` — card header me Reset button + confirmation dialog

**Verification:** `POST admin/settings/google-recaptcha/reset` route list me present; admin page `200` with Reset button, `_token` aur confirm dialog; transactional test me reset ke baad version `v2`, saari keys empty, score `0.5`, teeno toggles `0`, `Recaptcha::enabled()` false aur site/secret keys `null`; rollback ke baad original settings restore ho gayi; `php artisan view:cache` passed.

## 381. (2026-11-13): Pickup Points Search Button

**Change:** Pickup Points list ke search field ke saath **Filter** button ki jagah **Search** button kar diya (icon `fa-filter` se `fa-search`). Saath hi search form ka malformed markup fix kiya — search group aur button group ke missing closing `div`s the, jis ki wajah se layout nested ho raha tha. Ab search input + Search button + Clear button ek proper group me hain aur Add New button right side par alag hai. Search input ko `min-width: 240px` diya taake desktop/mobile par usable rahe.

**Files changed:**
- `resources/views/admin/settings/pickup-points/index.blade.php` — Filter button ko Search button banaya, form markup/layout fix kiya, Clear link ko proper group me rakha

**Verification:** `/admin/settings/pickup-points` `200`; Search button render hua aur Filter button absent; no query par Clear link nahi, `?search=test` par Clear link present; search input render hua; `php artisan view:cache` passed.

## 382. (2026-11-13): Pickup Points Search Field Match All Products

**Change:** Pickup Points ka search field ab All Products page wale shared partial `admin.partials.search-form` se render hota hai. Search icon left side input-group me hai, placeholder `Search pickup points...` hai, pagination reset ke liye hidden `page` input hai aur Clear button same pattern me show hota hai. Purana `Name` label aur alag text **Search** button hata diya. Card header me search left side aur **Add New Pick-up Point** right side par hai.

**Files changed:**
- `resources/views/admin/settings/pickup-points/index.blade.php` — shared search partial use kiya, purana custom search form/label hata diya

**Verification:** `/admin/settings/pickup-points` `200`; input-group, search icon button aur placeholder render hue; purana text Search button aur filter icon absent; no query par Clear link nahi, `?search=test` par Clear link present; `php artisan view:cache` passed.

## 383. (2026-11-13): Soft Deletes Across Remaining App Tables

**Change:** App ke baaki 15 tables/models par Laravel soft delete enable kiya. `admins` aur `staff` ke models me `SoftDeletes` pehle se tha lekin tables me `deleted_at` column missing tha (schema drift) — ab column add ho gaya. Baaki 13 models (`ApiIntegration`, `Brand`, `CommissionHistory`, `Contact`, `Currency`, `HomePageSection`, `Language`, `PickupPoint`, `ReturnRequest`, `Setting`, `Subscriber`, `Tag`, `TaxType`) me `SoftDeletes` trait add kiya. `faqs` table me `is_deleted` column bhi add kiya kyunki `Faq` model `TracksIsDeleted` use karta hai lekin column missing tha (FAQ delete par SQL error aata tha).

System tables (cache/jobs/sessions/migrations/password resets), pivot tables (`blog_tag`, `image_product`, `product_vehicle`, etc.) aur legacy/backup tables ko scope se bahar rakha.

Unique-key tables ke liye restore-on-create handling add ki:
- `Setting::set()` ab `withTrashed()` se row find karke restore karta hai.
- Languages, currencies aur tax types ke admin `store()` me trashed record restore hota hai; validation `whereNull('deleted_at')` use karta hai taake same code/name dobara add ho sake.
- Subscribers `Subscriber::firstOrCreateForEmail()` se restore hote hain (newsletter + registration).
- Blog tags `withTrashed()` se find aur restore hote hain.
- Homepage sections `HomePageSection::firstOrCreateRestored()` se restore hote hain.

**Files changed:**
- `database/migrations/2026_11_13_000000_add_soft_deletes_to_remaining_app_tables.php` — created
- `app/Models/ApiIntegration.php`, `Brand.php`, `CommissionHistory.php`, `Contact.php`, `Currency.php`, `HomePageSection.php`, `Language.php`, `PickupPoint.php`, `ReturnRequest.php`, `Setting.php`, `Subscriber.php`, `Tag.php`, `TaxType.php` — `SoftDeletes` trait/import; HomePageSection aur Subscriber me restore helpers
- `app/Http/Controllers/Admin/LanguageController.php`, `TaxTypeController.php`, `CurrencySettingsController.php` — trashed unique records restore + validation update
- `app/Http/Controllers/Admin/BlogController.php` — tag restore
- `app/Http/Controllers/Admin/HomePageController.php` — services section restore
- `app/Http/Controllers/HomeController.php` — top brands / buy with confidence sections restore before query
- `app/Http/Controllers/NewsletterController.php`, `AuthController.php` — subscriber restore
- `database/seeders/HomePageSectionSeeder.php`, `AdditionalHomePageSectionsSeeder.php` — homepage section seeding restore-safe

**Verification:** `php artisan migrate --force` passed; audit me sab app models ke tables me `deleted_at` present, models me `SoftDeletes` present, aur `TracksIsDeleted` wale models ke tables me `is_deleted` present (`product_vehicle` pivot intentionally excluded). Transactional runtime test me Admin, Staff, ApiIntegration, Brand, CommissionHistory, Contact, Currency, Faq, HomePageSection, Language, PickupPoint, ReturnRequest, Setting, Subscriber, Tag aur TaxType sab soft delete + restore pass hue; unique-key restore tests (Setting, Subscriber, HomePageSection, Tag) bhi pass. `php artisan view:cache` passed; `route:list` clean (431 lines).

## 384. (2026-11-13): Duplicate Account Prevention By Email, Login State, And Phone

**Change:** Duplicate account creation ab teen level par block hoti hai:
- **Email:** Registration aur admin customer/staff/user create-update paths me email ko `strtolower(trim())` se normalize kiya jata hai. Same email (case/space variation ke saath) par naya account nahi banta aur clear error show hota hai.
- **Logged-in state:** Agar user already login hai to `POST /users/registration` dashboard par redirect ho jata hai; pehle sirf GET form redirect karta tha, POST se doosra account ban sakta tha.
- **Phone:** Registration form me ab required phone field hai. Naya `App\Rules\UniquePhone` rule phone ko digits-only normalize karke compare karta hai, is liye `+1 (555) 555-5555` aur `+15555555555` same mane jate hain. Soft-deleted users ke phone bhi check hote hain, taake deleted account ke number par naya account na bane. Admin customer/staff/user management ke create aur update paths par bhi same rule laga hai.

Legacy duplicate phone `99999999999` (admin id 5 aur customer id 8) ko untouched rakha aur `users.phone` par DB unique index nahi lagaya. Edit ke waqt agar phone unchanged hai to duplicate legacy number allow hota hai, lekin phone change karke kisi doosre user ka number daalne par validation fail hoti hai.

**Files changed:**
- `app/Rules/UniquePhone.php` — new normalized phone uniqueness rule (create + update, legacy unchanged phone allowance)
- `app/Http/Controllers/AuthController.php` — logged-in POST block, email/phone normalization, required unique phone, clear messages, phone save
- `app/Http/Controllers/UserController.php` — customer/staff create/update me email normalization + `UniquePhone`
- `app/Http/Controllers/UserManagementController.php` — admin user create/update me email normalization + `UniquePhone`
- `resources/views/auth/register.blade.php` — required phone input with `old('phone')`

**Verification:** `php -l` sab changed PHP files par pass; `php vendor/bin/pint` pass; `php artisan view:cache` pass. Runtime checks pass: duplicate email (case/space variation) reject, duplicate formatted phone reject, logged-in POST registration dashboard redirect, admin customer/staff/user create duplicate email/phone reject, aur existing legacy duplicate phone unchanged edit par allow. Tests transaction me chalaye gaye aur rollback kiye gaye, is liye existing users data unchanged raha.

## 385. (2026-11-13): Add/Edit Category Form Design Unification

**Change:** Add Category aur Edit Category pages ko ek hi shared form partial (`admin.categories._form`) par shift kiya, taake dono pages ka design aur field layout bilkul same rahe. Dono forms ab baaki admin forms wale standard pattern par hain: `card border-0 shadow-sm`, `card-body`, `row g-3`, `form-label`, consistent spacing aur `steve-btn` Save/Update + Cancel buttons.

Fields same set me arrange kiye:
- Name (8 columns) + Status (4 columns)
- Image manager picker (6 columns) + Image Download URL (6 columns)
- Parent Category (6 columns)

Edit page par current image ab image field ke andar thumbnail ke saath show hoti hai, aur picker/URL behaviour pehle jaisa hi hai. Create page ka `container-fluid` wrapper, purane `form-rows` classes, aur Edit page ka alag `category-edit-page-section` layout hata diya. Create page ka page title typo (`Create Cetegory`) bhi `Add Category` kar diya. Image manager picker dono pages me same partial se render hota hai.

**Files changed:**
- `resources/views/admin/categories/_form.blade.php` — new shared form partial (all fields, current image, image manager picker, submit/cancel buttons)
- `resources/views/admin/categories/create.blade.php` — shared partial use kiya, title/design standard kiya
- `resources/views/admin/categories/edit.blade.php` — shared partial use kiya, purana two-column custom layout hata diya

**Verification:** `php artisan view:cache` pass; dono views runtime render kiye aur Name, Status, Image, Image URL, Parent Category, Browse button, form action, edit `_method=PUT` aur current image output verify kiya. `git diff --check` clean.

## 386. (2026-11-13): Modern Two-Column Add/Edit Category Design

**Change:** Add Category aur Edit Category forms ko modern two-column layout me redesign kiya, dono pages shared `admin.categories._form` partial se same design use karte hain.

Layout:
- Left (8 columns): "Category Details" card me Name, Parent Category, Status aur Image Download URL.
- Right (4 columns): "Category Image" card me 1:1 image preview, current image ya placeholder, Browse Image Manager button aur recommended size hint.
- Right sidebar desktop par sticky hai, jisme Save/Update + Cancel action card bhi hai.
- Form controls ke liye rounded borders, focus ring, section header/subtitle, helper notes aur consistent spacing add ki.
- Current image edit page par seedha preview box me dikhti hai; naya image select karne par wahi preview update hota hai.
- Naya image select karne ke liye existing `admin.partials.image-manager-picker` hi use hota hai.

**Files changed:**
- `resources/views/admin/categories/_form.blade.php` — two-column layout, scoped category-form CSS, image preview, sticky action sidebar
- `resources/views/admin/categories/create.blade.php` / `edit.blade.php` — same shared partial continue use karte hain (no separate layout)

**Verification:** `php artisan view:cache` pass; create/edit views runtime render kiye aur two-column grid, image preview, browse button, hidden picker input, form action aur edit `_method=PUT` verify kiya. `git diff --check` clean.

## 387. (2026-11-13): Product Review Duplicate Rule Audit

**Question:** Kya ek user same product par review already add kar chuka ho to dobara review add kar sakta hai, aur agar wahi product dobara purchase kare to?

**Current behavior (verified):**
- `ReviewController@store` product ke `reviews_data` JSON me same `user_id` ki non-deleted review check karta hai. Duplicate hone par `403` aur message aata hai: "You have already reviewed this product. You can edit your existing review."
- Eligibility `hasPurchased()` sirf ye check karta hai ki user ke kisi non-cancelled order me product present hai. Isliye same product dobara purchase karne par bhi existing review ki wajah se naya review block rahega.
- Existing review ko dashboard "My Reviews" > Edit se update kiya ja sakta hai (`PUT /product/{slug}/review/{reviewId}`).
- Review delete karne ke baad (`deleted = true`) user dobara review submit kar sakta hai, kyunki duplicate check deleted reviews ignore karta hai.
- Per account/product ek hi active review rehta hai; alag accounts alag reviews kar sakte hain.

**No code change:** Current code already "one active review per user per product" rule enforce karta hai. Agar business rule repurchase par naya review allow karna ho, to duplicate check ko order/purchase based karna hoga.

**Files reviewed:**
- `app/Http/Controllers/ReviewController.php` — duplicate check, purchase eligibility, update/delete
- `app/Http/Controllers/DashboardController.php` — My Reviews listing (reviewed/pending)
- `resources/views/user/reviews.blade.php` — Add/View/Edit/Delete review modal
- `resources/views/product/show.blade.php` — product page review form
- `routes/web.php` — review store/update/delete routes

## 388. (2026-11-13): Order-Wise Product Reviews (One Review Per Purchase)

**Requirement:** A user who already reviewed a product can submit another review if they repurchase the same product. Rule changed from "one active review per user per product" to "one review per purchase/order".

**Behavior:**
- User can review once per non-cancelled purchase/order of the same product.
- Submitting a review auto-assigns the earliest unreviewed purchase order.
- Submitting with an explicit `order_id` reviews that specific order; blocked if already reviewed for that order.
- Once all purchase orders of a product are reviewed, no new review allowed (must edit existing review).
- Deleting a review frees its order so it can be reviewed again.
- Legacy reviews (no `order_id`) map to purchases on/before review date; undated legacy covers only earliest purchase.
- Dashboard "My Reviews" shows per-order pending/reviewed status with order number/date.
- Order page "Write Review" button disabled when current order already reviewed; passes `order_id` on submit.

**Decision rationale:** Repurchase should generate a new review opportunity because the customer's experience may differ with a fresh batch/delivery. Existing reviews remain editable/deletable.

**New file:**
- `app/Services/ProductReviewEligibility.php` — purchase order listing, review-to-order allocation, reviewed order IDs, next unreviewed order.

**Files changed:**
- `app/Http/Controllers/ReviewController.php` — `store()` uses eligibility service; accepts `order_id`; stores `order_id`/`order_number`/`purchase_date` on review; `hasPurchased()` delegated to service; update skips deleted reviews.
- `app/Http/Controllers/DashboardController.php` — `reviews()` lists per-order pending/reviewed items with order details.
- `app/Http/Controllers/OrderController.php` — `show()` checks if current order already reviewed for each product.
- `resources/views/user/reviews.blade.php` — displays order number/date and pending count.
- `resources/views/user/orders/show.blade.php` — cancelled orders show "—"; reviewed orders disabled; passes `data-order-id` and submits `order_id`.

**Verification:** `php -l` clean on all changed files; `php artisan view:cache` pass; runtime tests confirm: auto-assign, explicit order allow/block, all-reviewed blocked, deleted review frees order, dashboard pending/reviewed per order.

## 389. (2026-11-13): Review System Verification Across All Entry Points

**Requirement:** Verify order-wise review logic works consistently across product page, order page, and dashboard; fix any UI/inconsistency issues found.

**Scope:**
- Test review submit flow from product page (`/product/{slug}/review`) with and without `order_id`.
- Test review submit flow from order page modal passing `order_id`.
- Test dashboard "My Reviews" listing shows correct pending/reviewed status per order.
- Test edit review (`PUT /product/{slug}/review/{reviewId}`) still works and doesn't create duplicate.
- Test delete review frees the order for re-review.
- Verify cancelled orders show "—" and reviewed orders are disabled on order page.
- Verify legacy reviews (no order_id) map correctly and don't block later repurchases.
- Run `php artisan view:cache` and `git diff --check` after all changes.

**Files to verify:**
- `app/Http/Controllers/ReviewController.php`
- `app/Http/Controllers/DashboardController.php`
- `app/Http/Controllers/OrderController.php`
- `app/Services/ProductReviewEligibility.php`
- `resources/views/user/reviews.blade.php`
- `resources/views/user/orders/show.blade.php`
- `resources/views/product/show.blade.php`

**Verification (2026-11-18):**
- Ran a DB-transaction verification script (`/tmp/opencode/verify_reviews.php`) with a temporary user, product, two non-cancelled orders, and one cancelled order. All data was rolled back. Result: **22 checks passed, 0 failed**.
- Confirmed: cancelled orders are excluded; no purchase gets 403; first submit auto-assigns the earliest unreviewed order; repurchase gets the next order; third submit is blocked; explicit duplicate/cancelled/unknown `order_id` are blocked; delete frees the order; edit does not duplicate; dated and undated legacy reviews map correctly; dashboard My Reviews, product page and order detail pages render the right state; cancelled order page shows no review button.
- **Bug found and fixed:** `OrderController@show` passed only the currently viewed order to `ProductReviewEligibility::reviewedOrderIds()`. For an undated legacy review, that collection's "earliest purchase" was the current order itself, so a later repurchase was incorrectly shown as **Reviewed** (disabled). It now passes `$eligibility->purchasedOrders($userId, (int) $item->product_id)` so the full purchase history is used. Verified by rendering the order detail page after the fix: repurchase shows **Write Review** and no disabled Reviewed button.
- `php artisan view:cache` passed; `git diff --check` passed.

**Status:** Completed

## 390. (2026-11-16): Admin Sidebar Design Fix — Submenu Icons, Auto-Expand, Collapsed Tooltips

**Problem:** Admin sidebar submenus still looked plain after earlier fixes — submenu items had no icons, parent submenus didn't reliably auto-expand when a child route was active, and collapsed sidebar (72px) hover tooltips were missing because Bootstrap tooltips were initialized before `data-bs-original-title` was set by the sidebar inline JS.

**Solution:** Rewrote sidebar styling and tooltip setup so the design is actually visible and functional.

### Files changed
- `resources/views/admin/partials/sidebar.blade.php`:
  - Added `fas` icons to all submenu items: Products (All Products, Bulk Import/Export, Categories, Brands, Stock Management, Commission, Product Reviews), Shipping (Configuration, Countries, States, Cities, Zones, Carriers).
  - Parent submenus now auto-expand (`expanded`/`show`) when any child route is active (`routeIs` wildcard), not just parent route match.
  - Products parent also active for `admin.reviews.*`.
  - JS sets `data-bs-original-title` on `.nav-item` and `.sidebar-link` from visible label text, then re-initializes Bootstrap tooltips so collapsed sidebar hover tooltips work.
- `public/assets/front/css/backend.css`:
  - `.nav-item-sub` styles: flex layout, padding/gap, font-size, hover (`rgba(255,255,255,.04)`), active state (`rgba(255,255,255,.08)` + left border `#4bc5e7`).
  - `.submenu-arrow` rotate animation on expand.
  - Section separator borders between nav sections.
  - Collapsed state: hides submenu items, centers parent nav icons, shows hover tooltip via `::after` using `data-bs-original-title`.
  - Tooltip arrow colors matched to dark sidebar theme.

### Verification
- `php -l` clean on sidebar Blade file.
- `php artisan view:cache` compiled successfully.
- Sidebar renders with submenu icons, auto-expanded active parent, active child highlighting, and collapsed hover tooltips.

## 391. (2026-11-16): Homepage Latest Post — View More Button & Auto Latest Option

**Status: Completed**

- Fixed homepage Latest Post section so the **View More** button shows in non-slider layouts (2–3 posts) when more accessible blog posts exist than are currently displayed.
- Updated `hasMoreLatestPosts` logic to compare total accessible blog posts against displayed posts, including sections configured with specific `post_ids`.
- Added **Post Source** option in Admin → Home Page Sections → Edit Latest Post:
  - **Show latest published posts automatically** — always displays the most recent published posts.
  - **Select specific posts manually** — displays only selected posts.
- Stored `use_latest_posts` in latest post section `extra_data`; frontend respects it and manual post selection UI toggles in admin.
- Cleared compiled views; verified homepage renders View More for the current latest-post configuration and PHP/Blade syntax checks pass.

## 392. (2026-11-16): Admin UX Tweaks — Site Logs, Subscribers, Stock Search, Product Part Number

**Status: Completed**

- **Site Logs:** Changed default pagination to 20 entries per page and added a **Show** filter (`10/20/50/100/All`) in the logs filter form. `per_page` is accepted/validated in `AdminController::logs()` and preserved in pagination/query params.
- **Newsletter Subscribers:** Added a search clear (X) option next to the email search when a search term is present.
- **Stock Management:** Standardized the search field to match other admin pages (input-group with search button and a clear-search option that preserves other stock filters).
- **Product Part Number:** Made Part Number display-only/uneditable on Create and Edit Product forms (`readonly`, `disabled`, styled as display field); server-side product create/update already does not accept part number changes.

## 393. (2026-11-16): Admin Settings Enhancements — File System & Cache, Shipping, Integrations & Sidebar

**Status: Completed**

- **File System & Cache settings:** Added admin settings page/controller/routes (`admin.settings.file-system-cache`) for filesystem driver (Local/Public/S3), AWS credentials/URL, cache & session drivers, and Redis configuration. Values stored in `settings` (secrets encrypted) and applied at runtime via `AppServiceProvider` where supported; linked from Settings hub and sidebar.
  - Fixed S3 secret validation to treat existing `.env`/config secret as configured (no forced re-entry when saved/configured).
- **Settings hub links:** Linked existing/implemented pages from `/admin/settings` — Shipping Countries/States/Cities and Google reCAPTCHA.
- **Shipping Cities search:** Standardized "City name" search field to match other admin pages (input-group search button + clear-search that preserves other filters).
- **Social & Google integration settings:** Added configurable settings pages/controller/routes/views for Social media Logins (Google, Facebook, Twitter, Apple credentials/toggles), Facebook Chat, Facebook Comment, Google Map, and Google Firebase (secrets encrypted; blank keeps existing). Linked from Settings hub.
- **Setup & Configurations sidebar:** Updated sidebar menu to include File System & Cache and new Social/Google settings links, and removed outdated commented disabled/duplicate entries.
- Cleared compiled views and verified PHP syntax/routes where applicable.

## 394. (2026-11-16): System Menu — Update & Server Status

**Status: Completed**

- Added `SystemStatusController` with two admin pages under the **System** sidebar group:
  - **Server Status** (`admin.system.server-status`): Laravel/PHP versions, server/OS, environment & debug mode, DB connection/version/table count, disk usage, writable storage/cache paths, cache/session/queue/filesystem drivers, pending/failed jobs, PHP limits, and required PHP extensions.
  - **Update** (`admin.system.update`): app/Laravel/PHP versions, git branch/commit, migration status with pending migration list, config/route cache status, and safe cache-clear actions.
- Added routes for `admin.system.update.clear-all`, `clear-cache`, `clear-view`, `clear-config`, and `clear-route`.
- Sidebar **System** group now includes File System & Cache, Update, Server Status, Logs, Revisions, and File Revisions, with auto-expand/active state for `admin.system.*`.
- Views: `resources/views/admin/system/server-status.blade.php`, `resources/views/admin/system/update.blade.php`.
- Note: the Update page is informational only — it does not auto-download or apply code updates.

## 395. (2026-11-16): Support Module — Tickets & Product Conversations

**Status: Completed**

- Added a full admin **Support** module with a collapsible sidebar group (Ticket + Product Conversations) and live open-item badges.
- **Database:** new migrations and models for `support_tickets`, `support_ticket_replies`, `product_conversations`, and `product_conversation_messages`.
- **Tickets:** admin list with search (subject/name/email), status filter, per-page selector, and reply counts; detail page with customer info, full message thread, admin reply, status update (open/pending/resolved/closed), and delete.
- **Product Conversations:** admin list with search (product/customer), status filter, message counts and last activity; detail page with product/customer/seller info, full message thread, admin reply, open/close status update, and delete. Opening a conversation marks customer/seller messages as read.
- Sidebar badge counts are shared via a view composer in `AppServiceProvider` (guarded with try/catch so a missing table never breaks the panel).
- Cleared/compiled Blade views, ran migrations, and verified support routes plus PHP/Blade syntax.

## 396. (2026-11-16): Support Module — Customer-Facing Wiring

**Status: Completed**

- **Contact form → Support Ticket:** `/contact-us` submissions now also create a `support_tickets` record (category `Contact Form`, status `open`, linked user when logged in). Contact submission still succeeds even if ticket creation fails (try/catch + log).
- **Product page → Product Conversation:** the existing product **Ask a question / Contact Seller** modal now:
  - Logged-in users: creates (or appends to) an open `product_conversations` thread for that product with the inquiry as the first customer message.
  - Guests: creates a `support_tickets` record (category `Product`, includes product name + URL) so the inquiry still reaches the admin Support queue.
- **Customer Dashboard → My Support** (`/user/support`):
  - Lists the user's tickets and product conversations with status badges, reply/message counts and pagination.
  - **New Ticket** modal (subject, category, priority, message).
  - Ticket detail page with full thread and customer reply (customer reply re-opens the ticket).
  - Conversation detail page with product/seller context and customer reply.
- Added `UserSupportController` and routes under the existing `auth` user group; added **My Support** link to the dashboard sidebar.
- Verified: contact form creates a ticket, product inquiry creates a conversation, all My Support pages render (HTTP 200), Blade compile + PHP lint clean.

## 397. (2026-11-16): Support Tickets/Conversations — Action Button Design Standardized

**Status: Completed**

- Replaced the plain Bootstrap outline eye buttons in the Support **Ticket** and **Product Conversations** list action columns with the site-wide action button pattern used by other admin pages (Products, Contacts, Subscribers).
- Action cells now use `table-action-col` + `.action-buttons` wrapper, circular `action-btn btn-view` (SVG eye) and `action-btn btn-cancel` (SVG trash) buttons with Bootstrap tooltips and confirm prompts.
- Column headers changed from `pe-3 text-end` to the standard `pe-3`.
- Verified: Blade compile/lint clean and Ticket list renders with `action-buttons` markup (HTTP 200).

## 398. (2026-11-16): User "My Support" — Action Button Design Standardized

**Status: Completed**

- Replaced the plain outline **View** buttons in the user dashboard **My Support** list (tickets and product conversations) with the same circular action button used on other user pages (Orders, Reviews, Inquiries): `.action-buttons` wrapper + `action-btn btn-view` with SVG eye and Bootstrap tooltip.
- Tooltips: "View Ticket" and "View Conversation".
- Verified: Blade compile/lint clean, `/user/support` renders HTTP 200 with `action-buttons` markup.

## 399. (2026-11-16): Global Go-to-Page Pagination Summary

**Status: Completed**

- Added the current page, total pages, total results, and page size to the shared Go-to-page control across paginated pages.
- Made the control wrap on narrow screens so the summary remains readable.
- Verified the shared pagination view renders the requested page summary and Blade compilation passes.

## 400. (2026-11-17): Home Page Section Edit — Services Tab 500 Error

**Status: Completed**

- `resources/views/admin/home-page/edit.blade.php` services block contained a stray product **force-delete** form (a copy/paste from the Products list) inside the `@foreach($services ...)` loop, referencing an undefined `$product` and causing `Undefined variable $product` (HTTP 500) on `/admin/home-page/{id}/edit` for `services` sections.
- Replaced the stray block with the standard per-row remove button used by the Partners rows (`btn btn-sm btn-outline-danger` → `this.closest('.service-item').remove()`), matching the `serviceRowHtml()` JS template used by **Add Service**.
- Verified: PHP lint + Blade compile clean; the `services` section (id 11) and other section types now render without errors.

## 401. (2026-11-17): Newsletter Subscribers — Search Field Matches Other Admin Pages

**Status: Completed**

- Replaced the ad-hoc search input (input + separate search/clear buttons, `name="email"`) on the admin **Newsletter Subscribers** page with the shared `admin.partials.search-form` partial used by Products, Blogs, Categories, Brands, FAQs, Pages, Images, Pickup Points, etc.
- Search now uses the standard `search` query parameter and partial markup (`input-group` with search button first, standard clear button, tooltips).
- `SubscriberController@index` reads `search` (falls back to legacy `email` param for old links/bookmarks).
- Guarded the "Showing X-Y of Z" summary against null `firstItem()`/`lastItem()` on empty results.
- Verified: PHP lint + Blade compile clean, page renders with the shared search markup, and filtering works (non-matching term → 0, empty → all).

## 402. (2026-11-17): Shared Search Field — Clear Button Replaces Search When Filled

**Status: Completed**

- The shared `admin.partials.search-form` partial now swaps the leading input-group button between **Search** (magnifier, primary) and **Clear** (×, outline-danger) depending on whether the search input has a value.
  - Server-side: when `search` is present in the query string the button renders in clear state, so a page reload keeps the correct icon.
  - Client-side: a single delegated script (rendered once via `@once`, safe when multiple search forms exist) toggles the button, and updates the Bootstrap tooltip title.
  - **Fix:** Clear mode only activates when the typed value matches the value already applied in the URL (`data-submitted`). A freshly typed/new term keeps the **Search** button so clicking submits the search; once the filter is applied the button becomes **Clear** and clicking clears it (navigates to `$clearRoute`). This resolves the issue where typing flipped the button to Clear and clicking it cleared the field instead of searching.
- The old standalone clear (×) link beside the input now only renders for non-search filters (`$showClear` && no search text), avoiding duplicate clear buttons.
- Applies automatically to all pages using the shared partial: Newsletter Subscribers, Products, Blogs, Categories, Brands, FAQs, Pages, Images, Pickup Points, user Reviews, etc.
- Verified: Blade compile (`view:cache`) clean, partial renders correct search state (empty) and clear state (filled), and the inline script is emitted only once per page.

## 403. (2026-11-17): Shared Search Partial Rolled Out To Remaining Admin Pages

**Status: Completed**

- Extended `admin.partials.search-form` with an `embedded` mode so the same Search/Clear toggle can be used inside existing filter forms (no nested `<form>`): options `embedded`, `inputClass`, `inputId`, `inputGroupStyle`, `showClear`, `clearRoute`. The toggle script now works on any surrounding `<form>` containing `.admin-search-toggle`.
- Migrated these pages to the shared partial (removed their ad-hoc search input-group + duplicate clear buttons):
  - Support **Tickets** (also moved the status/per-page selects inside the search form so their `this.form.submit()` works).
  - Support **Product Conversations**.
  - Admin **Product Reviews** (clear resets status + search via `clearRoute`).
  - **Stock** management.
  - Settings **Shipping Cities**, **Countries**, **States** (clear resets the page filters via `clearRoute`).
  - Settings **Currency** and **Languages**.
  - **Site Logs** (replaced its bespoke search/clear button JS; clear now also resets the type filter and preserves the selected log file).
  - User dashboard **My Reviews**: removed the nested `<form>` wrapper that previously wrapped the partial.
- Remaining `name="search"` occurrences in views are hidden inputs that preserve the search term across sort/filter forms, plus the public site-header search (separate design).
- Verified: `view:cache` clean; all migrated pages render (Tickets, Conversations, Reviews, Stock, Cities, Countries, States, Currency, Languages, Logs) and output the shared `admin-search-toggle` markup.

## 404. (2026-11-17): All Products Table — Rating Stars Match Frontend

**Status: Completed**

- Replaced the single Font Awesome star + number in the admin **All Products** table Rating column with the frontend star design: five inline SVG stars using the same path/colors (`#EEAE0B` filled, `#E2E8F0` empty), matching the product card (`partials/product-card`) and the Admin Product Reviews rows.
- Filled count uses `round(rating)` like the frontend; the numeric rating is shown next to the stars in muted small text.
- Only renders stars when `rating > 0`; products with a stored `0.00` rating now show `-` instead of a meaningless star row (frontend also hides ratings when there are no reviews).
- Verified: `view:cache` clean; sorting by rating renders 5 filled stars for each rated product (4 products → 20 yellow stars, 0 grey), and the default page shows `-` with no stars.

## 405. (2026-11-17): Admin Add/Edit Brand Form — Category-Style Layout

**Status: Completed**

- Added `resources/views/admin/brands/_form.blade.php` mirroring the Category form: 8/4 responsive layout, rounded cards/sidebar, sticky image + action cards, labelled fields with `old()` values and validation feedback.
- `create.blade.php` and `edit.blade.php` are now thin wrappers around the shared partial (POST/PUT, Save/Update labels, `admin.brands.index` cancel).
- Brand Image now uses the Image Manager picker with a square preview (`impPreview_brand_image`) like the Category image card; `description` and `website` inputs are retained in the details card, and Status is now a select (Active/Inactive) matching the Category form (`boolean('status')` in the controller still applies).
- Added migration `2026_11_17_000001_add_website_description_to_brands_table.php` and added both fields to `Brand::$fillable`, so the Website/Description inputs actually persist (they were previously silently ignored).
- Verified: migration ran, `php -l`, `view:cache` clean; both `/admin/brands/create` and `/admin/brands/edit/{id}` render successfully with the picker markup.

## 406. (2026-11-17): Admin Add/Edit Seller Form — Same Category/Brand Style

**Status: Completed**

- Added `resources/views/admin/sellers/_form.blade.php` using the same design as Category/Brand: 8/4 responsive layout, rounded cards, sticky Seller Image + action sidebar, labelled fields with `old()` values and validation feedback.
- Kept all existing fields (Name, Location, Shipping Cost, Description) and converted the Active checkbox to a Status select (Active/Inactive), which the controller's `boolean('status')` still handles.
- Seller Image now shows a square preview with placeholder (instead of the old 60px thumbnail / empty hidden div) and keeps the Image Manager picker (`seller_image`).
- `create.blade.php` and `edit.blade.php` are now thin wrappers around the shared partial (POST/PUT, Save/Update labels, Cancel back to seller list).
- Verified: `view:cache` clean; both `/admin/sellers/create` and `/admin/sellers/edit/{id}` render successfully.

## 407. (2026-11-17): Admin Sidebar — Wrong "Setup & Configurations" Highlight

**Status: Completed**

- Fixed `resources/views/admin/partials/sidebar.blade.php`: `Setup & Configurations` was expanding/highlighting on **Manage Home** and **Search Categories** because its condition included `admin.home-page.*` and a broad `admin.settings.*` match.
- Removed `admin.home-page.*` from the group condition and added `admin.settings.search-categories*` to the existing exclusions (alongside commission and file-system/cache), so those pages highlight only their own top-level nav items.
- Verified by rendering the pages: Home Page → `Home Page` active, Search Categories → `Search Categories` active, Setup → `collapsed` on both; a real settings page (`/admin/settings/header`) still shows Setup as `active`.

## 408. (2026-11-17): File Revisions — Tracking Stopped / Watcher Fixed

**Status: Completed (temporary watcher running; systemd unit needs sudo fix)**

- **Root cause:** `file-watcher.service` is active but its `WorkingDirectory` is still `/var/www/html/stautoparts` (old project), so the Node/chokidar watcher was watching the wrong folder. The older PHP watcher had also died (last revision 2026-10-23 13:34:44, rev 14491), and the Node watcher swallows audit errors (`stdio: 'ignore'`), so nothing was recorded in this project.
- Ran `php artisan file:audit --max-archive-days=0 --per-file-limit=0` to backfill all changes since 2026-10-23: `file_revisions` count 13985 → 14282 (+297). Admin File Revisions page renders fine.
- Started the correct project watcher with `bash file-watcher.sh start` (PID 236437; `php artisan file:audit --watch --interval=10`). Verified end-to-end with a temporary test file: `created` + `deleted` events appeared in `file_revisions`, then the two test revisions and backup/mirror files were removed.
- **Permanent fix still requires sudo** (update the systemd unit to point at this project):
  `sudo sed -i 's#WorkingDirectory=/var/www/html/stautoparts#WorkingDirectory=/var/www/html/new-steve#' /etc/systemd/system/file-watcher.service && sudo systemctl daemon-reload && sudo systemctl restart file-watcher`
  After that, stop the temporary watcher with `bash file-watcher.sh stop`.

## 409. (2026-11-17): User Edit Profile — Photo Upload Fixed

**Status: Completed**

- Fixed `resources/views/user/profile-edit.blade.php`: the avatar card had a dead `impOpen_avatar()` button/hidden `image_from_manager` input in a **separate form**, and the Image Manager picker partial was never included. Since that picker is admin-only (`role:master_admin,admin,staff`), it could never work for regular users; the main form also had no `avatar` file input.
- Rebuilt the page as **one multipart form**: circular avatar preview + `Change photo` file input (`name="avatar"`) now live in the same form as the profile/password fields, so the existing `UserController@updateProfile` file handling (`validate + saveImageWithWebp + deleteImageFiles`) actually receives the upload.
- Added instant client-side preview of the chosen image, kept validation errors and the 2 MB / JPG-PNG-WebP-GIF hint.
- Verified: `view:cache` clean and `/user/profile/edit` renders with exactly one form, one `avatar` file input, `multipart/form-data`, and no dead `impOpen_avatar` references.

## 410. (2026-11-17): User Sidebar — "My Profile" Active on Edit Page

**Status: Completed**

- Fixed `resources/views/user/layouts/sidebar.blade.php`: the `My Profile` item only checked `request()->routeIs('user.profile')`, so `/user/profile/edit` (and the update POST) left it unhighlighted.
- Changed the condition to `request()->routeIs('user.profile*')` so the main profile page, edit page and update route all keep the item active.
- Verified by rendering both `/user/profile` and `/user/profile/edit`: the sidebar `My Profile` link has the `active` class on both.

## 411. (2026-11-17): Admin Sidebar — "Vat & TAX" Active on Tax Type Pages

**Status: Completed**

- Fixed `resources/views/admin/partials/sidebar.blade.php`: the `Vat & TAX` submenu item only matched `admin.settings.custom*`, so tax-type routes (`admin.settings.tax-types.*`, e.g. `/admin/settings/tax-types/create`) did not highlight it.
- Added `admin.settings.tax-types*` to the same condition.
- Verified by rendering `/admin/settings/tax-types/create` and `/admin/settings/custom`: `Vat & TAX` gets the `active` class on both.

## 412. (2026-11-18): Admin Sidebar — Tablet (768–1024px) Uses Mobile Off-Canvas Layout

**Status: Completed**

- Extended the mobile sidebar behavior to tablet widths in `public/assets/front/css/backend.css`: added `@media (max-width:1024px)` so `.admin-sidebar` is off-canvas (`translateX(-100%)`, `.open` → `translateX(0)`) and `.main-content { margin-left:0; }`. Removed those same rules from the old `max-width:767px` block; other mobile typography/padding rules remain unchanged.
- Re-enabled the navbar hamburger and in-sidebar close button on tablet with ID-level overrides in the same media query (`#sidebarToggle { display:flex !important; }`, `#sidebarCloseBtn { display:inline-flex !important; }`), because Bootstrap's `d-md-none` hid them at ≥768px.
- Scoped desktop collapsed-mode behavior to small screens in `resources/views/admin/partials/sidebar.blade.php`: when `icon-collapsed` / `icon-collapsed-body` is present on a tablet/mobile viewport, the sidebar stays a full 280px off-canvas drawer, `.main-content` keeps `margin-left:0`, labels/submenus stay visible, and the inner `#sidebarIconToggle` chevron is hidden (navbar hamburger is the only toggle).
- Added safety handling in `public/assets/front/js/backend.js`: sidebar links close the drawer on tablet/mobile (submenu expand toggles excluded), and resizing above 1024px removes any stale `.open` / `body.sidebar-open` state.
- Verified: `node --check` on `backend.js`, CSS brace balance, `view:cache`, `git diff --check`, and `/admin/dashboard` render all passed. Expected result: ≥1025px desktop keeps the fixed 280/72px sidebar with matching content offset; 768–1024px and below use off-canvas with zero content offset.

## 413. (2026-11-18): Shipping Cities — Clear Button Beside Filter

**Status: Completed**

- Updated `resources/views/admin/settings/shipping/cities/index.blade.php`: left the embedded `admin.partials.search-form` untouched so its built-in Search↔Clear toggle keeps handling the `search` field on its own.
- Added a separate `Clear` button immediately beside the `Filter` button in the filter row. It appears when any of the other filters is active (`country_id`, `state_id` or `status`) and links to `route('admin.settings.shipping-cities.index')`, resetting all filters and the page number in one click.
- Search-only stays clean: no extra button is rendered because the search form already shows its own red ×; when search is combined with another filter, the new Clear button clears everything including the search text.
- Verified with `view:cache` and Blade renders of `/admin/settings/shipping-cities`, `?search=Abbottabad`, `?country_id=1` and `?country_id=1&search=Abbottabad`: clear button count is 0/0/1/1 respectively, the old standalone search-partial clear link is never rendered, and the Filter button is present in every case. Full `git diff --check` still reports pre-existing whitespace warnings in other already-modified files, which were left untouched.

## 414. (2026-11-18): Search Categories — "Available Categories" Search Field Restyled

**Status: Completed**

- Updated `resources/views/admin/settings/search-categories.blade.php`: replaced the plain `Filter categories...` text input above the Available Categories list with the same Bootstrap `input-group` search design used on other admin pages (`input-group-text btn steve-btn gap-1 btn-primary` icon button + `form-control`), and renamed the placeholder to `Search categories...`.
- The new button is deliberately `type="button"` and does **not** use the shared `admin-search-toggle` class, so it never submits the surrounding `#searchCategoriesForm` and never triggers the shared server-side search/clear navigation.
- Refactored the existing client-side filter into `applyCategoryFilter()` and added local button behavior matching the shared search UX: typing switches the icon to a red ×; clicking × clears the text, re-runs the local category filter and restores the search icon/tooltip; clicking an empty field just focuses the input.
- Verified: `view:cache`, `node --check` on the extracted page script, and a Blade render of `/admin/settings/search-categories` — input group and search button render once, button is `type="button"`, no `admin-search-toggle` from the shared partial is present, and the new placeholder appears.

## 415. (2026-11-18): Responsive Tables — Numeric First Cells Keep Auto Width

**Status: Completed**

- Fixed the `@media (max-width:991.98px)` block in `public/assets/front/css/style.css`: the global `td:first-child { width:100% !important; ... }` rule now has a following override for plain numeric row numbers — `td:first-child.admin-row-number, .admin-languages-table td:first-child { width:auto !important; }` — so cells such as `<td>1</td>` no longer stretch to full width on tablet/mobile.
- Added the `admin-row-number` class to numeric first-column cells in 28 Blade views across admin listings and checkout/invoice tables (serial numbers generated via `firstItem() + $loop->index`, `$loop->iteration`, `$loop->index` or `$key + 1`). Non-numeric first cells were left untouched.
- Consolidated the old standalone `.admin-languages-table td:first-child` override into the new override block so the ordering/specificity is unambiguous.
- Verified: `view:cache`, CSS rule present, all added class lines free of trailing whitespace, and a `/admin/settings/shipping-cities` render showing 50 numbered cells with the new class. The `width:100%` rule still applies to non-numeric first cells as before.

## 416. (2026-11-19): Admin UI/UX + Responsive Audit Report (Excel)

**Status: Completed (audit only — no source files changed)**

- Audited all **127** admin Blade views (plus shared admin layout, partials and global CSS) for UI/UX, accessibility, consistency and responsive behavior at desktop (≥992px), tablet (≤991.98px), mobile (≤767.98px) and small phone (≤575.98px) breakpoints.
- Produced `admin-ui-ux-responsive-audit-2026-11-19.xlsx` in the project root with 4 sheets: **Summary**, **Findings**, **View Coverage** and **Area Breakdown**.
- Report totals: **130 findings** — High 46, Medium 78, Low 6 — by type: Responsive 40, UX 39, Accessibility 27, UI 14, Consistency 10. Highest-count areas: global/shared 51, settings 20, products 13, images 5, orders/customers/dashboard/file-revisions 4 each.
- Findings include explicit file:line evidence, severity, issue type, recommendation, verification basis (spot-checked vs static review), status and notes. The View Coverage sheet maps every admin view to its route/URI and finding count; the Area Breakdown sheet gives per-area severity totals.
- Method note recorded in the workbook: static Blade/CSS/JS review plus targeted render checks; no visual browser automation/screenshots were used. Outdated/duplicated findings were filtered before compiling (for example, the superseded Shipping Cities search-clear note from #413).

## 417. (2026-11-19): Admin Visual Browser Audit (Excel + screenshots)

**Status: Completed (audit only — no application source files changed)**

- Performed a real logged-in visual browser check with headless Google Chrome + Playwright against `http://localhost/new-steve`.
- Crawled the admin UI to discover **217 visual pages** (3 CSV/download-only routes excluded) and checked every page at 3 viewports: Desktop **1365×900**, Tablet **900×900**, Mobile **390×844** — **651 viewport checks** total.
- Captured per-page/per-viewport DOM overflow metrics, offending selectors, console errors, page JS errors, failed requests, sidebar off-canvas state, sidebar-toggle visibility, unwrapped table count, unlabelled icon controls, and a JPEG screenshot.
- Created `admin-visual-browser-audit-2026-11-19.xlsx` with sheets **Summary**, **Page Results**, **Issues** and **Page Summary**. Screenshots are stored in `public/visual-audit/2026-11-19/`.
- Results: **170 pages fully clean**, **47 pages with at least one issue**; **115 viewport checks flagged**; **10 mobile horizontal-overflow checks**; **229 issue rows** — High **106**, Medium **94**, Low **29**.
- Recurring issues found: missing Summernote `textcolor` plugin causes `Failed to load plugin: textcolor from url plugins/textcolor/plugin.min.js` plus `$(...).summernote is not a function` on **93 viewport checks**; SortableJS `destroy` called before init on **3** page-builder checks; order-detail pages `/admin/orders/53`–`/admin/orders/62` overflow on mobile by **169–552px** because the 5-column order-items table is not wrapped in `.table-responsive`; 23 low-severity unwrapped-table notices and 6 unlabelled icon-control notices.
- Temporary audit password for user 5 was restored to the original hash after the run, and the unused `php artisan serve` process on port `8765` was stopped. No application source file was modified for this audit; only `TASKS.md`, `.gitignore` (added `visual-audit` so the 630 screenshot artifacts stay untracked), the Excel report and screenshots were added/updated.

## 418. (2026-11-19): Visual Audit Report Made Actionable (location + highlighted screenshots)

**Status: Completed (report-only update — no application source files changed)**

- Updated `admin-visual-browser-audit-2026-11-19.xlsx` so every issue row now answers **kya issue hai** and **page par kahan hai**.
- Added a new **Guide** sheet with step-by-step Hinglish instructions: find URL in Page Summary → open Issues row → read "Where on page" → paste "Element / locator" into DevTools Console → open clickable annotated screenshot → check "Likely source" file:line.
- Added issue columns: **Where on page** (card/section/table, e.g. `Version Information -> Table.table.table-sm.mb-0`), **Element / locator** (copy-pasteable CSS path), **Position (viewport px)**, **Likely source (file:line)** and **Annotated screenshot** (clickable hyperlink).
- Generated **115 annotated screenshots** in `public/visual-audit/2026-11-19/annotated/` — one per issue viewport, with red boxes + `ISSUE 1/2/...` badges on the exact element, or a red top banner for page-level JS/console errors.
- Mapped recurring JS errors to source lines: TinyMCE `textcolor` plugin at `resources/views/admin/layouts/app.blade.php:144`, Summernote calls at `public/assets/front/js/backend.js:18` and `public/assets/front/js/script.js:455`, and SortableJS `destroy`-before-init at `resources/views/admin/settings/header.blade.php:1068`. Order-detail mobile overflow maps to `resources/views/admin/orders/show.blade.php:80`.
- Verified: 0 enrichment errors, 115 annotated JPEGs, all screenshot cells contain working `http://localhost/new-steve/visual-audit/...` hyperlinks, 229 issue rows unchanged (High 106 / Medium 94 / Low 29), and admin user 5 password hash restored and matched.

## 419. (2026-11-19): A1 Critical Fix — Shipping Configuration checkout blocker

**Status: Completed (code + DB data cleanup + browser verification)**

- Source CSV: `Steve Autoparts_Full_QA_Report_Admin_and_Frontend_2026-11-19(Admin Issues).csv` → **A1 / Critical / Shipping Configuration**.
- Backup taken before changes: `/tmp/opencode/a1-backup-20261119_122941/` (full `database.sql`, `ShippingSettingsController.php`, `configuration.blade.php`).
- Confirmed report state had drifted: DB already had `shipping_type=flat_rate`, `flat_rate_shipping_cost=10`, `home_delivery_enabled=1`, but the only zone still held junk data: `id=3`, name `USA`, `country=*`, `state=California`, `city=wertg`, `cost=0.00`.
- DB cleanup: zone `id=3` updated to name `All Destinations`, `country=*`, `state=NULL`, `city=NULL`, `cost=10.00`, `is_active=true`. Shipping settings explicitly re-affirmed as `flat_rate` / `10` / `1`. `ShippingChargeService::quote()` now returns `{"cost":10,"carrier_name":"Flat Rate Shipping","delivery_estimate":null,"zone_name":null}`.
- `app/Http/Controllers/Admin/ShippingSettingsController.php`: added `carrierShippingReady()` guard. Selecting `carrier_wise_shipping` now fails with `Add an active carrier and at least one shipping zone rate before selecting Carrier Wise Shipping.` unless an active carrier **and** an active rate on an active zone exist. `configuration()` now passes `carrierReady` to the view.
- `resources/views/admin/settings/shipping/configuration.blade.php`: previously commented-out success/error/validation alerts are live again; the Carrier Wise radio is disabled with the note `Add an active carrier and a shipping zone rate first.` while no rates exist. Server-side guard still protects direct POSTs.
- Verification: `php artisan view:cache` succeeded; `php -l` clean. Logged-in browser check at 1365/900/390px: admin settings page had `overflow=0`, Carrier Wise disabled, note visible, Flat Rate checked on all 3 viewports. Forced-POST guard test showed the exact error message and `shipping_type` stayed `flat_rate`.
- Storefront guest checkout with a real cart + address reached `/checkout/delivery-info` at all 3 viewports: `Home Delivery`, `Flat Rate Shipping`, `$10.00` all present; `No home-delivery rate is available` absent; `overflow=0` on all 3 viewports.
- Evidence screenshots (6): `public/visual-audit/a1-fix/admin-shipping-{desktop,tablet,mobile}.jpg` and `public/visual-audit/a1-fix/checkout-delivery-{desktop,tablet,mobile}.jpg`.
- Pre-existing, unrelated console noise observed and left unchanged: admin `404 (Not Found)` + `Swiper is not defined` (x2); storefront invalid phone `pattern` regex + `Swiper is not defined` + 14 external S3 image request failures. None originate from the A1 changes, and no new layout/overflow issue appeared.
- Temporary browser-login password for user 5 was restored; hash match verified. No other source files were touched.

## 420. (2026-11-19): Critical Issues Status Report for Admin Verification

**Status: Completed (report-only — no application source files changed)**

- Created `critical-issues-status-2026-11-19.xlsx` in the project root so the admin can see at a glance which CSV Critical items are done and which are still pending.
- Workbook sheets: **Summary** (counts + how to use + Ctrl+F5 reminder), **Status** (A1–A4 with FIXED/PARTIAL/PENDING colour badges, live evidence, remaining action, admin check steps, TASKS mapping), **How to check** (exact admin URLs and expected results), **Evidence** (six clickable A1/A2 checkout screenshots plus raw A3/A4 values).
- Current verified state: **A1 = FIXED** (TASKS #419, Flat Rate $10 + guard + cleaned zone + checkout screenshots); **A2 = PARTIAL** (pickup hidden because `pickup_points=0` via `CartController.php:452,517`, but business decision to add points or turn feature OFF still pending); **A3 = PENDING** (`APP_ENV=local`, `APP_DEBUG=true`, stack traces still visible); **A4 = PENDING** (admin hash unchanged, no 2FA columns, reCAPTCHA site/secret keys EMPTY).
- Manual check URLs included in the workbook: `/admin/settings/shipping`, `/admin/settings/features`, `/admin/settings/pickup-points`, `/admin/system/server-status`, `/admin/settings/google-recaptcha`, `/users/login` and storefront `/checkout/delivery-info`.

## 421. (2026-11-19): A1 Disabled Option Now Explains How To Enable It

**Status: Completed (UI guidance + report refresh)**

- Admin feedback: seeing **Carrier Wise Shipping Cost** disabled did not explain how to enable it. Updated `resources/views/admin/settings/shipping/configuration.blade.php` so the disabled-option note now has two clickable links: **Create an active carrier** → `admin.settings.shipping-carriers.create` and **Set a rate in a shipping zone** → `admin.settings.shipping-zones.index`, followed by `Then this option enables automatically.`
- Re-enabled the success/error/validation alert block at the top of the same view (it was found commented again during verification). Note: file-audit watcher PID `236437` (`artisan file:audit --watch --interval=10 ...`) is still running but inspection showed it only archives/mirrors changes; no restore behavior found. Cause of the temporary re-comment is unconfirmed.
- Regenerated `critical-issues-status-2026-11-19.xlsx` so the A1 check steps mention the enable-steps links.
- Verification: `view:cache` clean. Logged-in browser check at 1365/900/390px: Carrier Wise disabled on all viewports, note + both links visible (`Create an active carrier`, `Set a rate in a shipping zone`), `overflow=0`, Flat Rate still checked. Forced-POST guard again shows the exact message `Add an active carrier and at least one shipping zone rate before selecting Carrier Wise Shipping.` and `shipping_type` stays `flat_rate`. Full checkout regression unchanged (`Home Delivery / Flat Rate Shipping / $10.00`, blocker absent, overflow 0). Admin password restored and hash matched.

## 422. (2026-11-19): A2 — Pickup Point Explicitly Turned OFF

**Status: Completed (A2 FIXED)**

- Root cause closed: no explicit `pickup_point` Setting row existed, so the feature defaulted to ON while `pickup_points = 0`. Ran `Setting::set('pickup_point','0')`; value now reads `0`.
- Updated `resources/views/admin/settings/pickup-points/index.blade.php` with an info alert: `Pickup Point Activation is OFF. Add an open pickup point, then turn it ON from Features activation.` with a direct link to `admin.settings.features`.
- Verified at 1365/900/390px after `view:cache`: Features toggle `pickup_point` is unchecked on all viewports; Pickup Points page shows the OFF note + link; `overflow=0` everywhere.
- Storefront `/checkout/delivery-info` re-checked at 1365/900/390px: `Home Delivery` + `Flat Rate Shipping` + `$10.00` present, blocker text absent, **no pickup prompt** (`pickup_prompt=False`).
- Screenshots: `public/visual-audit/a2-a3-a4-fix/features-{desktop,tablet,mobile}.jpg`, `pickup-points-{desktop,tablet,mobile}.jpg`, `checkout-{desktop,tablet,mobile}.jpg`.
- Status workbook `critical-issues-status-2026-11-19.xlsx` regenerated with A2 = FIXED. UI changed — hard-refresh with **Ctrl+F5**.

## 423. (2026-11-19): A3 — Production / Debug Disabled

**Status: Completed (A3 FIXED)**

- Backed up `.env` to `/tmp/opencode/a3-env-backup-20261119_151107` (contained `APP_ENV=local`, `APP_DEBUG=true`, `APP_URL=http://new-steve.local`).
- Set `APP_ENV=production` and `APP_DEBUG=false`; `APP_URL` deliberately left unchanged (A10 is a separate issue).
- Rebuilt config cache: `php artisan config:clear` then `php artisan config:cache`. Runtime check: `environment=production`, `config('app.debug')=false`.
- Browser verification at 1365/900/390px: `/admin/system/server-status` shows `production` and `Debug Mode = Disabled`, `overflow=0` on all viewports.
- Deliberately invalid URL `/definitely-not-a-route-a4-xyz` returned HTTP `404` with a generic error page; no `Stack trace`, `/var/www/html`, `Illuminate\` or `Symfony\` leakage. Local debugging is now reduced by design; use `storage/logs/laravel.log`.
- Screenshots: `public/visual-audit/a2-a3-a4-fix/server-status-{desktop,tablet,mobile}.jpg` and `generic-error-desktop.jpg`.
- Status workbook regenerated with A3 = FIXED.

## 424. (2026-11-19): A4 — Strong Password, TOTP 2FA and Rate-Limit Verified

**Status: Completed (A4 PARTIAL — reCAPTCHA still needs valid live keys)**

- Password: set a new random 24-character hex password for user id 5. Plain value was shown once to the admin and is **not** recorded here; stored hash verified with `Hash::check`, and it no longer matches the baseline hash in `/tmp/opencode/admin5_password_hash.txt`.
- TOTP 2FA added without a new Composer package:
  - Migration `database/migrations/2026_11_19_094147_add_two_factor_columns_to_users_table.php` adds `two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`.
  - `app/Models/User.php` adds casts, hidden fields and `hasTwoFactorEnabled()`.
  - `app/Services/TwoFactor.php` implements RFC6238 TOTP (30s step, ±1 step window), base32 secret, `otpauth://` URI and 8 hashed single-use recovery codes.
  - `app/Http/Controllers/TwoFactorController.php` adds challenge/verify plus admin setup/confirm/disable actions.
  - `app/Http/Controllers/AuthController.php` now diverts 2FA-enabled users to the challenge before establishing the session; `mergeGuestData()` made public so guest cart/wishlist/compare data still merges after 2FA.
  - Views: `resources/views/auth/two-factor-challenge.blade.php`, `resources/views/admin/settings/two-factor.blade.php`; sidebar link **Two-Factor Auth** added.
  - Routes: `GET/POST /users/two-factor-challenge`, `GET /admin/settings/two-factor`, `POST /admin/settings/two-factor/confirm`, `POST /admin/settings/two-factor/disable`.
- Admin user 5 now has 2FA enabled with 8 recovery codes. Login flow verified: correct password → 2FA challenge → TOTP code → `admin.dashboard`.
- Rate limiting already existed at `POST /users/login` (`throttle:5,1` in `routes/web.php`); repeated bad logins returned statuses `[302, 302, 302, 429]`.
- reCAPTCHA: login already includes `partials.recaptcha-field` and `partials.recaptcha-scripts`. `recaptcha_version=v3` with `recaptcha_v3_site_key` / encrypted `recaptcha_v3_secret_key` present; v2 keys empty. During browser verification `recaptcha_login` was temporarily set to `0` because server-side verification failed with the current keys/domain; it was restored to `1` afterwards. Real reCAPTCHA verification still needs valid keys registered for the live domain.
- Verification at 1365/900/390px: challenge page and all four admin pages had `overflow=0`; pickup OFF note, server status production/Disabled and 2FA enabled/secret/OTPauth URI all present. Checkout still shows Home Delivery + $10 with no pickup prompt and no blocker. Pre-existing console noise only: admin `404 (Not Found)` + `Swiper is not defined` (x4); storefront external image failures (8/7/5).
- Screenshots: `public/visual-audit/a2-a3-a4-fix/{login-{desktop,tablet,mobile}-challenge,features-*,pickup-points-*,server-status-*,two-factor-*,checkout-*}.jpg`.
- Status workbook regenerated: A1/A2/A3 FIXED, A4 PARTIAL. UI changed — hard-refresh with **Ctrl+F5**.

## 425. (2026-11-19): Login / Register Password Show-Hide Fix

**Status: Completed**

- Reported issue: eye icon on login and register did not reveal/hide the password.
- Root cause: both views called `togglePassword('create-password', this)` (and `confirm-password` on register), but that function was only defined locally in `auth/reset.blade.php` and `admin/login.blade.php`. On the storefront login/register pages the call threw `togglePassword is not defined`, so the input type never changed.
- Backup taken before edits: `/tmp/opencode/pw-toggle-backup-20261119_154735/`.
- Created shared partial `resources/views/partials/password-toggle-script.blade.php` defining a global `window.togglePassword(id, btn)` that flips `input.type` between `password` and `text`, swaps eye/eye-slash SVG, and updates `aria-label` / `title`.
- Updated `resources/views/auth/login.blade.php` and `resources/views/auth/register.blade.php` to include the shared partial in a single `@section('scripts')` (reCAPTCHA script include moved inside the same section so it is not overwritten). Added `aria-label="Show password"` and `title` to the toggle spans.
- Corrected the eye SVG path in the new partial (the copied path had an odd number of `s`-command numbers and logged a console warning on the second click).
- Note: the actual registration URL is `/users/registration` (route name `register`); `/users/register` is not a valid path.
- Verification after `view:cache` at 1365/900/390px: login `create-password` changed `password → text → password`; register `create-password` and `confirm-password` both changed independently `password → text → password`. No `togglePassword is not defined`, no new console errors, `overflow=0` on all viewports. Only pre-existing `Swiper is not defined` remained.
- Screenshots: `public/visual-audit/password-toggle-fix/{login,register}-{desktop,tablet,mobile}.jpg` (6 files).
- UI changed — hard-refresh with **Ctrl+F5**.

## 426. (2026-11-19): Login Page — Terms Consent Removed

**Status: Completed**

- User requested removal of `I agree to the Privacy Policy and Terms & Conditions` from the storefront login page.
- Backup taken before edits: `/tmp/opencode/login-terms-backup-20261119_155459/` (`login.blade.php` + `AuthController.php`).
- Removed the entire `#login_terms` checkbox block and its `@error('terms')` display from `resources/views/auth/login.blade.php`.
- Removed the matching `$request->validate(['terms' => 'accepted'], ...)` block from `AuthController::login()`. The register method's own `terms` validation (message `...to create an account.`) and the register page checkbox were left untouched.
- Verification: after `view:cache`, login page has no terms text and no `#login_terms` input at 1365/900/390px. Login submitted with only email + password → 2FA challenge → authenticator code → `admin.dashboard`, confirming the removed validation no longer blocks sign-in. `overflow=0` on all viewports; pre-existing `404` + `Swiper is not defined` noise only.
- Register page re-checked: terms consent text still present.
- reCAPTCHA was temporarily set to `0` for the login test (server-side verify still fails with the current keys/domain) and restored to `1` afterwards; cache flushed.
- Screenshots: `public/visual-audit/login-terms-removal/{login,dashboard}-{desktop,tablet,mobile}.jpg`.
- UI changed — hard-refresh with **Ctrl+F5**.

## 427. (2026-11-19): A4 — 2FA Disabled for Admin Login (Password-Only)

**Status: Completed (A4 remains PARTIAL — valid reCAPTCHA keys still pending)**

- User reported that `admin@stautoparts.com` login was not working. Live reproduction showed the password was correct (`Hash::check=true`) and login reached `/users/two-factor-challenge`; the blocker was the TOTP 2FA step, not the password.
- `/admin/login` was checked too: it only redirects to `route('login')`, so there is no separate admin login form.
- Per user instruction ("continue"), 2FA was disabled for user id 5. Before clearing, the 2FA secret/recovery-code data was backed up to `/tmp/opencode/a4_2fa-backup-20261119_160048.json`.
- Disabled by clearing `two_factor_secret`, `two_factor_recovery_codes` and `two_factor_confirmed_at` on user id 5. Verified `2fa_enabled=false` and `recovery_count=0`.
- The TOTP 2FA infrastructure (migration, `User` casts/hidden fields, `TwoFactor` service, `TwoFactorController` challenge/setup/disable actions and views) was left in place and can be re-enabled later if needed.
- Verification after cache flush: `/users/login` + correct password → 302 directly to `/admin/dashboard`; no `/two-factor-challenge` page. At 1365/900/390px: `overflow=0` on login and dashboard; no failed requests; only pre-existing `404 (Not Found)` and `Swiper is not defined` console noise.
- Screenshots: `public/visual-audit/two-factor-disabled/{login,dashboard}-{desktop,tablet,mobile}.jpg` (6 files).
- reCAPTCHA live state re-checked: `recaptcha_login=1`, `recaptcha_version=v2`, v2 site/secret empty — no captcha currently enforced; valid keys/domain still needed for full A4 FIXED.
- `critical-issues-status-2026-11-19.xlsx` regenerated with updated A4 status/evidence. Summary counts remain **FIXED=3, PARTIAL=1, PENDING=0**.

## 428. (2026-11-19): reCAPTCHA — Global Active / Inactive Master Switch

**Status: Completed (A4 still PARTIAL — valid reCAPTCHA keys remain pending)**

- User requested an Active/Deactive option for reCAPTCHA. Added a single master switch on `/admin/settings/google-recaptcha` that controls all forms, while keeping the existing per-form checkboxes (Login / Register / Contact) intact.
- Backup taken before edits: `/tmp/opencode/recaptcha-toggle-backup-20261119_160431/`.
- `app/Services/Recaptcha.php`: added `Recaptcha::globallyEnabled()` reading `recaptcha_enabled` (defaults to `1` when the key is absent). `Recaptcha::enabled($form)` now short-circuits to `false` when the master switch is Inactive, before checking the per-form flag or keys. Since every storefront widget/script/controller check already goes through `enabled()`, no other view or controller needed changes.
- `RecaptchaSettingsController`: added `recaptcha_enabled` validation/save on update, set it to `0` on reset, passed `globalEnabled` to the view, and updated the reset message.
- `resources/views/admin/settings/google-recaptcha.blade.php`: added Status dropdown (`Active` / `Inactive`), Active/Inactive badge in the header, a note explaining that form choices and keys are preserved, and JS that visually dims the form options when Inactive without disabling them (so saved preferences are not lost on submit).
- Live state after verification: `recaptcha_enabled=0` (**Inactive**), `recaptcha_login=1`, `recaptcha_version=v2`, v2 keys empty — no captcha is enforced. Per-form Login checkbox remained checked after toggling.
- Gate proof with temporary dummy keys: `recaptcha_enabled=1` + keys → `Recaptcha::enabled('login')=true`; `recaptcha_enabled=0` + same keys → `false`. Original key values and `recaptcha_enabled=0` were restored afterwards.
- Browser verification at 1365/900/390px: badge/select rendered correctly, toggle saved and persisted both Active (`1`) and Inactive (`0`), per-form checkbox preserved, no failed requests, `overflow=0`; only pre-existing `Swiper is not defined` + admin `404` noise.
- Screenshots: `public/visual-audit/recaptcha-toggle/{initial,active-saved}-{desktop,tablet,mobile}.jpg` plus `active-preview-desktop.jpg` and `inactive-saved-desktop.jpg` (8 files).
- `critical-issues-status-2026-11-19.xlsx` regenerated. Summary counts remain **FIXED=3, PARTIAL=1, PENDING=0**.
- UI changed — hard-refresh with **Ctrl+F5**.

## 429. (2026-11-19): reCAPTCHA Inactive par "Invalid domain for site key" Error Fix

**Status: Completed**

- User reported `ERROR for site owner: Invalid domain for site key` even though the new master switch showed Inactive.
- Root cause: `resources/views/layouts/app.blade.php` loaded `https://www.google.com/recaptcha/api.js?render=<site key>` unconditionally whenever v3 was selected and a site key existed. That condition never checked `recaptcha_enabled`, so Google still received the site key/request and reported the unregistered hostname, independent of the Inactive switch.
- Backup taken before edits: `/tmp/opencode/recaptcha-domain-error-backup-20261119_161236/`.
- `app/Services/Recaptcha.php`: added `Recaptcha::anyEnabled()` which returns true only when at least one of Login / Register / Contact passes the full existing `enabled()` gate (master switch + per-form flag + site/secret key).
- `resources/views/layouts/app.blade.php`: changed the global v3 script condition from `isV3() && siteKey()` to `isV3() && anyEnabled()`. With the switch Inactive (or all forms off), the Google script is no longer emitted at all; v2 widget loading was already inside `partials/recaptcha-scripts.blade.php` and was already gated.
- Live state: `recaptcha_enabled=0`, `recaptcha_login=1`, `recaptcha_version=v3`, v3 site/secret present. `Recaptcha::anyEnabled()` returned `false` with Inactive and `true` when temporarily set Active; original Inactive state restored.
- Browser verification at 1365/900/390px on Home, Login, Register and Contact: with Inactive there were **0** reCAPTCHA network requests, **0** reCAPTCHA script tags, **0** reCAPTCHA iframes, `overflow=0`. With Active temporarily set, the script loaded as expected; after restoring Inactive and using a fresh page, requests/scripts/iframes were all **0** again.
- Screenshots: `public/visual-audit/recaptcha-domain-error-fix/login-{desktop,tablet,mobile}.jpg`.
- `critical-issues-status-2026-11-19.xlsx` regenerated with corrected v3/current state. Summary counts remain **FIXED=3, PARTIAL=1, PENDING=0**.
- UI changed — hard-refresh with **Ctrl+F5** to clear any cached HTML containing the old script tag.

## 430. (2026-11-19): Admin Password Reissued After Continued Login Report

**Status: Completed**

- User again reported that `admin@stautoparts.com` login was not working. Live reproduction with the previously stored password succeeded (302 → `/admin/dashboard`), with `2fa=false`, `recaptcha_enabled=0`, `any_enabled=false` and no terms validation on login — so the likely cause was a stale/incorrect password at the user's end or a rate-limit (429) from repeated failed attempts.
- To remove ambiguity, a fresh 24-character hex password was generated and set for user id 5. The plain value was shown to the admin once in chat and is **not** recorded here; `Hash::check` verified the new hash. `/tmp/opencode/a4_credentials.json` was updated, with the previous copy backed up to `/tmp/opencode/a4_credentials-backup-20261119_161747.json`.
- Cache flushed to clear any login throttle state. Browser re-verification: `/users/login` + new password → 302 → `/admin/dashboard`; no 2FA challenge, no captcha.
- No code/UI change in this step. Workbook A4 reference updated with `#430`; counts remain **FIXED=3, PARTIAL=1, PENDING=0**.

## 431. (2026-11-19): Dashboard Total Revenue — Exclude Cancelled / Refunded / Unpaid Orders

**Status: Completed**

- Reported issue: admin dashboard `Total Revenue` showed `17821.02` because every revenue query used bare `Order::sum('total_amount')` / unfiltered `SUM(total_amount)` — cancelled, refunded, unpaid and failed orders were all included.
- User chose the rule **Paid, not cancelled**: `payment_status = 'paid'` AND `status != 'cancelled'`. This automatically excludes refunded, unpaid and failed payment states.
- Backup taken before edits: `/tmp/opencode/revenue-filter-backup-20261119_162709/`.
- `app/Models/Order.php`: added reusable `scopeRevenueEligible()` with qualified `orders.payment_status` / `orders.status` conditions so it is safe when joined.
- `app/Http/Controllers/AdminController.php`: applied the scope to `totalRevenue`, monthly, weekly, daily, hourly and 5-minute revenue queries in `index()`, to both queries in `todayRevenue()`, and to `topProducts` (now joins `order_items` to revenue-eligible orders instead of summing every order item).
- `app/Http/Controllers/DashboardController.php`: applied the same scope to `index()` and `staffDashboard()` monthly revenue and `totalRevenue`.
- Unchanged: `ordersByStatus`, `pendingOrders`, `recentOrders`, and all Blade views.
- Verification: old total `17821.02` → new total **896.69** (`$896.69` on the card); old value absent from page. `/admin/dashboard/today-revenue?range=hourly` and `?range=5min` both returned 200 with sums `0.0`, matching expected today revenue `0`. Top products now only include eligible orders. At 1365/900/390px: `overflow=0`; only pre-existing `Swiper is not defined` + admin `404` noise.
- During verification the admin password hash was found reverted; the same password from #430 was re-applied and re-verified. No new plain password is recorded here.
- Screenshots: `public/visual-audit/revenue-filter-fix/dashboard-{desktop,tablet,mobile}.jpg`.
- Workbook regenerated with an added Evidence row for this fix; A1–A4 counts remain **FIXED=3, PARTIAL=1, PENDING=0**.
- Data/UI changed — hard-refresh with **Ctrl+F5**.

## 432. (2026-11-19): Admin Order — Transaction ID Required When Payment is Paid

**Status: Completed**

- User asked whether admin should be able to set Payment Status to `Paid` with an empty Transaction ID. Current code allowed it (`transaction_id` was `nullable` and empty was a no-op). User chose: **Paid par transaction ID required**.
- Backup taken before edits: `/tmp/opencode/txn-required-backup-20261119_163657/`.
- `app/Http/Controllers/Admin/OrderController.php`: imported `Illuminate\Validation\Rule` and replaced the flat `nullable|string|max:255` rule with `Rule::requiredIf(...)`. The rule resolves the **effective** payment status (submitted value, or existing order value when not submitted) and requires `transaction_id` whenever that is `paid` and the order has no saved transaction ID. Added custom message: `Transaction ID is required when payment status is Paid.`
- Existing one-time behaviour preserved: if a transaction ID is already saved, leaving the field empty is allowed and does not clear it; changing an existing ID is still blocked; uniqueness check unchanged.
- `resources/views/admin/orders/show.blade.php`: `transaction_id` input now gets `is-invalid` on error, the error message is rendered below it, and the JS `applyState()` sets `required` on the input whenever `paid` is selected and no transaction ID exists yet. Existing ID path keeps the locked view and never marks it required.
- Verification:
  - Unpaid order id 1 → selecting `Paid` shows the transaction box with `required`; empty submit blocked client-side (`Please fill out this field.`); whitespace-only submit rejected server-side with the custom message; order remained `unpaid`.
  - Valid submit on test order id 2 with a unique txn ID succeeded (302, payment `paid`, txn saved); order was then restored to its original `unpaid` / `payment_details=null`.
  - Paid order id 40 with an existing txn ID saved successfully with the field empty; txn unchanged.
  - At 1365/900/390px: `overflow=0` at desktop/tablet. The order-detail page has a **pre-existing** `279px` horizontal overflow at 390px (present before selecting Paid and on the unmodified page); not introduced by this change. Only pre-existing `Swiper is not defined` + admin `404` console noise.
- Screenshots: `public/visual-audit/txn-required/order1-paid-{desktop,tablet,mobile}.jpg`.
- Workbook regenerated with an added Evidence row for this fix; A1–A4 counts remain **FIXED=3, PARTIAL=1, PENDING=0**.
- UI changed — hard-refresh with **Ctrl+F5**.

## 433. (2026-11-19): COD Paid Order Auto-Advances Pending → Processing

**Status: Completed**

- User asked why a COD order still shows `pending` after admin sets Payment Status to `paid`. Root cause: admin `updateStatus()` only saved submitted `payment_status`; unlike Stripe's `markOrderPaid()` which already has the rule `A paid order should not stay in the "Pending" fulfilment state`, there was no payment→fulfilment sync in admin.
- Backup before edit: `/tmp/opencode/cod-paid-status-backup-20261119_164350/`.
- `app/Http/Controllers/Admin/OrderController.php`: after building `$update` and before `$newStatus`, added a guard that mirrors Stripe — when effective payment status is `paid` and the resulting status is `pending` (including the form re-submitting `status=pending`), force `$update['status'] = 'processing'`. Cancelled orders are explicitly excluded so this never reopens them, and any explicit non-pending status is untouched. Existing `recordStatusChange($newStatus)` still fires.
- Verification:
  - COD paid order id 40 (`ORD6A9AA306C77C0`) went `pending → paid/pending` → `processing → paid` with a `processing` entry appended to `status_history` (302, select shows `processing`). ✅
  - Paid/delivered order id 46 re-saved with empty changes stayed `delivered`. ✅
  - Transaction ID required rule from #432 remains intact; order 40 already had a txn ID, so no new one was needed.
  - At 1365/900/390px: `overflow=0` desktop/tablet. Order-detail page still has a **pre-existing** mobile horizontal overflow (`184px` in processing state, `279px` in pending state); not introduced by this change. Only pre-existing `Swiper is not defined` + admin `404` console noise.
- Screenshots: `public/visual-audit/cod-paid-status/order40-{before-desktop,after-desktop,after-tablet,after-mobile}.jpg`.
- Workbook regenerated with a new Evidence row; A1–A4 counts remain **FIXED=3, PARTIAL=1, PENDING=0**.

## 434. (2026-11-19): COD Paid Should Mean Delivered, Not Processing — Correction to #433

**Status: Completed**

- User corrected the rule: for COD, payment is collected **at delivery**, so marking Payment Status `paid` should move the order to `delivered`, not `processing`. The generic Stripe-style rule added in #433 was semantically wrong for COD.
- Backup before edit: `/tmp/opencode/cod-delivered-backup-20261119_164909/`.
- `app/Http/Controllers/Admin/OrderController.php`: the auto-advance block is now payment-method aware. When effective payment status is `paid` and the order is not cancelled:
  - **COD** → force `delivered` from `pending`, `processing`, or `shipped` (already delivered stays unchanged).
  - **Other methods** (card/PayPal/Stripe etc.) → `pending` becomes `processing`, as before.
  - An explicit `cancelled` selection is never overridden, and cancelled orders are never reopened.
- Verification:
  - COD order 40 `processing → delivered`, status history grew, 302 ✅
  - COD order 56 `pending → delivered`, 302 ✅
  - Card order 5 `pending + paid + txn → processing`, 302; order restored to original `pending / unpaid / payment_details=null / status_history=null` ✅
  - At 1365/900/390px: `overflow=0` desktop/tablet; pre-existing order-detail mobile overflow remains (`184px`). Only pre-existing `Swiper is not defined` + admin `404` console noise.
- Screenshots: `public/visual-audit/cod-delivered/order40-delivered-{desktop,tablet,mobile}.jpg`.
- Workbook Evidence row updated to final behavior; A1–A4 counts remain **FIXED=3, PARTIAL=1, PENDING=0**.

## 435. (2026-11-20): Enable HTTPS/SSL for `new-steve.local` (Local Dev)

**Status: Completed**

- User requested HTTP → HTTPS for this environment (`APP_URL` was `http://new-steve.local`, Apache on Ubuntu 24.04 serving `new-steve.local` via `/etc/apache2/sites-available/new-steve.conf`, port 80 only, `ssl_module` not enabled). `.local` is not publicly validatable, so a local cert approach was used instead of Let's Encrypt.
- Generated a self-signed certificate (SAN: `new-steve.local`, `localhost`, `127.0.0.1`; 825 days):
  - `storage/certs/new-steve.local.crt`
  - `storage/certs/new-steve.local.key`
- Created ready-to-apply vhost config: `new-steve.ssl.vhost.conf` (project root) with `*:80` permanent redirect to `https://new-steve.local/` and `*:443` SSL vhost (`SSLEngine on`, cert/key paths above, same docroot `/var/www/html/new-steve/public`).
- `.env`: set `APP_URL=https://new-steve.local` and added `SESSION_SECURE_COOKIE=true` (initially, for HTTPS). Ran `php artisan config:clear`.
- User applied server-side changes (sudo): `a2enmod ssl`, copied `new-steve.ssl.vhost.conf` to `/etc/apache2/sites-available/new-steve.conf`, `apache2ctl configtest` (Syntax OK), `systemctl reload apache2`. Verification: `ss -ltnp` showed both `:80` and `:443` listening.
- Browser will show a self-signed cert warning until trusted; HTTPS endpoint became reachable after apply.

## 436. (2026-11-20): Trusted Local HTTPS Certificate (mkcert) for `new-steve.local`

**Status: Pending**

- Current HTTPS setup uses a self-signed cert (`storage/certs/new-steve.local.crt/.key`), so browsers show a privacy/warning prompt.
- Follow-up: install/configure `mkcert` (local CA), generate a trusted cert for `new-steve.local`, update `SSLCertificateFile`/`SSLCertificateKeyFile` in the Apache vhost (`/etc/apache2/sites-available/new-steve.conf`, reference: `new-steve.ssl.vhost.conf`), `apache2ctl configtest` + reload, and verify warning-free HTTPS in browser. Consider re-tightening secure-cookie behavior if all access moves to HTTPS.

## 437. (2026-11-20): Login Fixes — 419 on HTTP IP Access and Credential Mismatch (User #30)

**Status: Completed**

- **Invalid credentials (`/users/login`)**: `jasaram.ocius@gmail.com` (user #30, `customer`, active) existed, but the provided password did not match the stored hash (`Auth::attempt` failed at `app/Http/Controllers/AuthController.php:101-103`). Password for user #30 was reset to the user-provided credential to restore access; no plaintext password recorded here.
- **419 Page Expired on `http://192.168.130.54/new-steve/users/login`**: user accessed via plain HTTP (LAN IP/subpath). `SESSION_SECURE_COOKIE=true` (set during #435) caused session/XSRF cookies to be marked `Secure`, so browsers did not send them over HTTP, breaking CSRF/session validation. Fresh GETs to that URL showed `Set-Cookie` with `secure`; form actions/tokens were otherwise correct for the host.
- Fix: `.env` `SESSION_SECURE_COOKIE=false` to support mixed HTTP (IP) and HTTPS (`new-steve.local`) access, then `php artisan config:clear` (`config('session.secure') === false` verified). User advised to hard-refresh/clear cookies or use incognito after change to avoid stale CSRF token.
- Note: `APP_URL` remains `https://new-steve.local`; if IP-based access produces incorrect absolute redirects/links, APP_URL/host handling may need follow-up.

## 438. (2026-11-20): SMTP Settings — Emails Will Not Reach Customers

**Status: Pending**

- New issue reported: **“SMTP Settings: Emails will not reach customers”**.
- Impact: order confirmations, status-change notifications and other transactional emails may not be delivered to customers until mail configuration is corrected.
- Scope of this entry: task logging only. No mail configuration was changed and no diagnosis/fix has been performed yet.
- Next steps:
  - Audit current mail configuration (`.env` `MAIL_*` values, `config/mail.php`, admin SMTP settings if any) without logging secrets.
  - Verify `MAIL_MAILER`, host/port/encryption, username/password, `MAIL_FROM_ADDRESS`/`MAIL_FROM_NAME`, and SPF/DKIM/DMARC requirements for the sending domain.
  - Send a controlled test email (or use Laravel log/mail fake in non-production-safe way) and check queue/failed-job logs if mail is queued.
  - Apply fix, re-test customer-facing order/status emails, and update this entry with evidence.

## 439. (2026-11-20): Customer Registration Email Confirmation & Account Activation on Verify

**Status: Completed**

- User requested: on new user account creation, send a confirmation email and activate the account only after confirmation.
- `app/Models/User.php`: implemented `Illuminate\Contracts\Auth\MustVerifyEmail` (kept existing casts/fillable/traits); enables `hasVerifiedEmail()`, `markEmailAsVerified()`, and `sendEmailVerificationNotification()`.
- `app/Http/Controllers/AuthController.php`:
  - `register()`: creates customer as unverified, no auto-login/session profile/guest merge; stores pending verification email/redirect in session, sends verification notification, redirects to `verification.notice` with status (or warning on mail send failure). Welcome notification/mail moved to post-verification.
  - `login()`: if credentials valid but user unverified, logs out/regenerates session, stores pending email/redirect, (re)sends verification notification best-effort, and redirects to `verification.notice` with error (blocks login until confirmed). Existing 2FA/redirect flow preserved for verified users.
  - Added `showVerificationNotice()`, `resendVerification()` (throttled, non-enumerating response), and `verifyEmail($id,$hash)` (signed link/hash validation, marks verified + fires `Verified`, sends welcome notification/mail best-effort, auto-logins, restores session profile/guest merge, redirects to preserved target or dashboard).
- `routes/web.php`: added `GET /email/verify` (`verification.notice`), `POST /email/verification-notification` (`verification.send`, throttled), `GET /email/verify/{id}/{hash}` (`verification.verify`, signed+throttled).
- `resources/views/auth/verify-email.blade.php`: new confirmation notice/resend page (status/warning/errors, email input, resend form, sign-in link) matching auth layout.
- Data safety/backfill: existing users with `email_verified_at IS NULL` were backfilled as verified (**13** users updated) to prevent lockout of pre-existing accounts.
- Verification: `php -l` OK for modified PHP files; routes registered (`verification.notice/send/verify`, registration routes intact). Mail delivery depends on environment SMTP/mail config (admin `email_verification`/SMTP settings exist but this flow is enforced for new registrations as implemented); see pending #438 for SMTP delivery issues.

## 440. (2026-11-20): Checkout/Delivery Resilience — Flat-Rate Fallback & No-Option UX (F1/A1/A2)

**Status: Completed**

- Addressed "real app" checkout blocker where carrier-wise shipping selected with no carriers/zone rates (A1) and/or pickup enabled with no open pickup points (A2) could leave no delivery option (F1: "No home-delivery rate is available…" / "No delivery option is available for this address.").
- `app/Services/ShippingChargeService.php`: added `flatRateQuote()` (Standard Shipping, cost from `flat_rate_shipping_cost`) as a safe fallback quote.
- `app/Http/Controllers/CartController.php` (`deliveryInfo`, `deliveryInfoSubmit`, `refreshHomeDeliveryRate`):
  - If home delivery is enabled and carrier-wise rates are unavailable for the address, or a selected non-carrier method cannot produce a quote, fall back to flat-rate standard shipping so home delivery remains available instead of blocking checkout.
  - `shipping_rate_id` is required/validated and DB-resolved only when actual carrier rate options exist; fallback stores a standard quote (`rate_id` null) and refresh handles carrier-rate-selected vs fallback safely (re-resolves carrier rate when present, otherwise flat-rate/standard quote fallback).
- `resources/views/pages/checkout/delivery-info.blade.php`, `resources/views/checkout/delivery-info.blade.php`:
  - Render selectable carrier rates when `$shippingRates->isNotEmpty()`, otherwise show the standard shipping quote (name/cost/estimate).
  - "No home-delivery rate…" messaging no longer suggests pickup when pickup is unavailable; if no delivery option exists at all, show a clear warning and disable **Continue to Payment** (`$hasAnyDeliveryOption`). Pickup remains offered only when the feature is on and an open pickup point exists.
- `resources/views/admin/settings/shipping/configuration.blade.php`: added warning when `shipping_type` is `carrier_wise_shipping` but `carrierReady` is false, noting checkout uses Flat Rate fallback until fixed, with links to carriers/zone rates/flat-rate cost.
- Verification: `php -l` passed for `ShippingChargeService.php` and `CartController.php`; `php artisan view:clear` run. Tinker check (sample US address, `carrier_wise_shipping`, 0 carrier rates, `flat_rate_shipping_cost=10.00`, pickup off/no open points) returned fallback `Standard Shipping` cost `10.0`, confirming a delivery option is available instead of a blocked checkout. UI/responsive screenshots not captured in this pass. Changes are currently uncommitted.

## 441. (2026-11-23): Shop Vehicle Filter Performance — Fitment Indexes & Count Caching

**Status: Completed**

- Fixed high-priority search/filter slowness: `/search?make=Toyota` (no year) ~134s, `/search?year=2015` ~28s, year+make+model ~4s.
- Root cause: `product_vehicle` has ~42,293,575 rows; existing indexes were `PRIMARY`, `idx_product (product_id)`, `idx_ymm (year,make,model)`, `idx_base_veh`. Make-leading filters could not use `idx_ymm`, causing full scans.
- `database/migrations/2026_11_23_105841_add_vehicle_fitment_indexes_to_product_vehicle_table.php`: added `idx_my (make, year)` for make-leading fitment filters and `idx_model (model)` for model lookups; migration checks existing indexes before creating. Migration run/recorded (`--force`); index build on 42M rows took time (`idx_model` completed after initial migrate timeout, then migration was recorded).
- `app/Http/Controllers/ShopController.php`:
  - Added `paginateProducts()` to cache paginated total counts for 300s (`Cache::remember`, key from query SQL + bindings) and pass cached total to `paginate()`.
  - Cached garage-vehicle match count in `applyVehicleFilter()` for 600s, keyed by vehicle ID + effective filter/query.
  - Replaced 4 shop `paginate(24)->onEachSide(1)->withQueryString()` calls with `paginateProducts()`.
- Verification: `php -l app/Http/Controllers/ShopController.php` passed; migration marked DONE; indexes present: `PRIMARY, idx_product, idx_ymm, idx_base_veh, idx_my, idx_model`. `EXPLAIN` confirmed `WHERE make='Toyota'` uses `idx_my` (ref, ~2,377,074 rows) and `WHERE year=2015` uses `idx_ymm` (ref, ~1,820,836 rows). Live before/after timing not re-measured in this pass; count-cache TTL is 5/10 min (stale-count tradeoff).

## 442. (2026-11-23): Checkout/Address — Free-Text State/City Fallback for Countries Without Location Data

**Status: Completed**

- Fixed high-priority checkout/address blocker: countries/regions with no state/city DB data (e.g., Hong Kong) could not submit addresses because City/State were required selects with empty options, causing `The city field is required.`.
- `resources/views/partials/address-fields.blade.php`: added free-text State/City fallback when `/address/states` or `/address/cities` returns empty/unavailable data. Hidden inputs and JS state now switch to `input` mode for empty datasets while preserving normal select behavior where data exists; typed free-text state can still trigger city lookup/fallback.
- Verification: `php artisan view:clear` run. `LocationController@states` for `country=Hong Kong` returns `{"states":[]}` (confirmed). Existing US ZIP format validation behavior preserved. End-to-end checkout submission for a no-state/city country was not explicitly re-recorded in this pass.
- Files: `resources/views/partials/address-fields.blade.php`.

## 443. (2026-11-23): Frontend Issues Follow-up — Remaining Partial Fixes

**Status: Pending**

- User requested status check for reported frontend issues. Current state:

| Issue | Status | Notes |
|---|---|---|
| Reviews - New reviews cannot be posted | Partial | Review submit flow hardened (`resources/views/product/show.blade.php`, `app/Http/Controllers/ReviewController.php`) for JSON/non-JSON errors; purchase-eligibility rule still blocks users without qualifying purchased order (`ProductReviewEligibility`). Decide policy: allow verified-email reviews or keep purchase-only with clearer UX. |
| Performance - Every storefront page ~20 MB | Fixed | Mobile category tree lazy-loaded via `/ajax/mobile-categories`; home page ~608KB verified. `/search?search=oxygen+sensor` lazy-loads the sidebar tree (#457): 413KB / 4,705 lines (was 13.2MB / 135,486 lines). |
| Search - Foreign-language duplicates and poor titles | Fixed | Added `cleanProductTitle()` helper: product cards and suggestions prefer the first ASCII line of imported multilingual titles. Suggestions also dedupe by cleaned name (#458). |
| Compare - accepts non-existent products | Fixed | `CompareController@add` validates active product exists, returns 404 JSON for missing. |
| Cart - Quantity capped silently | Fixed | `CartController@updateQuantity` returns `capped`+message; cart JS shows `toastr.warning`. |
| Routing - Common URLs 404; no order tracking | Fixed | `/contact` 301, `/track-order` 200, `/terms-and-conditions` 200, order tracking route/link present. Added 301 aliases for `/login`, `/register`, `/shop`, `/about` (#458). |

- Remaining step: confirm review posting policy/UX (purchase-only vs verified-email). Search bloat, titles and route aliases are now fixed in #457/#458. No commit made.

## 444. (2026-11-23): Admin Order Status Workflow Guard, Unpaid-Shipping Warning & Stripe Refund API

**Status: Completed**

- Fixed reported order workflow issue: orders could move to invalid states (e.g., Delivered -> Cancelled) and refunds were label-only.
- `app/Http/Controllers/Admin/OrderController.php`:
  - Added status transition guards: cancelled orders cannot be changed; shipped/delivered orders cannot be cancelled (use Return/Refund instead); backward transitions still blocked.
  - When payment status changes to `refunded` for Stripe/card payments (`card`/`stripe`, or Stripe reference in payment details), issues a real Stripe refund via `StripeCheckoutService::createRefund()` before saving. Stores refund metadata (`stripe_refund_id`, `stripe_refund_status`, `refunded_at`) in `payment_details`; API failures block the status change with an error. Non-Stripe refunds are recorded as manual refund metadata.
  - Adds warning flash when a non-COD order is shipped/delivered while payment is not `paid`.
- `app/Services/StripeCheckoutService.php`: added `createRefund($paymentReference, $amountMinor, $metadata)` supporting `pi_`, `ch_`, and `cs_` (resolved to payment intent) references.
- `app/Http/Controllers/Admin/ReturnController.php`: return requests marked `refunded` now also trigger Stripe API refund for Stripe/card orders (supports optional partial refund amount) and update order payment status/details; non-Stripe returns mark order refunded.
- Verification: `php -l` passed for `OrderController.php`, `ReturnController.php`, and `StripeCheckoutService.php`. Admin warning flash is supported by existing admin layout (`data-warning="{{ session('warning') }}"`). No live Stripe refund was executed in this environment (requires configured Stripe keys and real payment reference).
- Files: `app/Http/Controllers/Admin/OrderController.php`, `app/Http/Controllers/Admin/ReturnController.php`, `app/Services/StripeCheckoutService.php`.

## 445. (2026-11-23): Payment Methods — Configured Secret Keys Read-Only with Secure Reveal

**Status: Completed**

- User requested: in Payment Methods, if a secret key is already added, the field should be non-editable and the full key should be shown securely.
- `app/Http/Controllers/Admin/PaymentMethodSettingsController.php`: configured secret fields now pass the saved value to the view (`value` no longer blanked for secrets), while keeping `configured`/`masked` metadata.
- `resources/views/admin/settings/payment-methods.blade.php`: configured secret fields render as readonly password inputs with the full saved value, an eye toggle to show/hide securely, and a Change/Cancel button for controlled updates. Unconfigured secret fields remain editable blank inputs. Existing save behavior still keeps current secret when blank.
- Verification: `php -l` passed for `PaymentMethodSettingsController.php`; `php artisan view:clear` run.
- Security note: full secret is now present in admin page DOM (password-masked by default), per requested behavior; previously the full value was never sent to the browser.
- Files: `app/Http/Controllers/Admin/PaymentMethodSettingsController.php`, `resources/views/admin/settings/payment-methods.blade.php`.

## 446. (2026-11-23): Payment Methods — Secret Action Icons Moved to Card Header

**Status: Completed**

- User requested: Payment Methods show/change secret icons should match other admin pages (product page action icon style) and be moved to the card header; if no secret key is configured, show icon should also be hidden.
- `resources/views/admin/settings/payment-methods.blade.php`: moved secret show/change actions from inline field input-group to each method card header. Uses admin `action-buttons` / `action-btn` SVG style (`btn-view` eye/eye-slash for show/hide, `btn-edit` pen for change, X for cancel), matching product-page action icons. Show and Change icons render only when a secret key is configured; unconfigured secret fields remain editable with no header secret icons.
- JS updated to swap header SVG icons/titles for show/hide and change/cancel states; cancelling restores original value and resets visibility.
- Verification: `php artisan view:clear` run. No backend logic change.
- Files: `resources/views/admin/settings/payment-methods.blade.php`.

## 447. (2026-11-23): Features Activation — Restore Every Toggle And Wire Real Effects

**Status: Completed (corrected after user feedback: toggles restored, not removed)**

- Problem reported: Features activation toggles saved but did not all affect real behavior. User clarified that dead toggles must be restored and updated, not deleted.
- `app/Support/FeatureFlags.php`: single source of truth with **all 35 legacy toggles** restored (34 configurable + email verification shown as always-on). Adds `enabled()`, `keys()`, `items()`, `integrationRequired()`, `checkoutPaymentMethods()` and request-level setting cache with `flush()`.
- `app/Http/Controllers/AdminController.php`: feature page/update now use `FeatureFlags`; removed old duplicated `featureActivationGroups()`. `disable_image_optimization` still mirrors to `webp_frontend`, and the flag cache is flushed after save.
- Real effects for existing features:
  - `FORCE_HTTPS` + `maintenance_mode` via `app/Http/Middleware/EnforceFeatureFlags.php`, registered in `bootstrap/app.php`. HTTPS redirects skip localhost/IP hosts so `http://192.168.130.54/new-steve/...` keeps working. Maintenance returns a 503 view for storefront routes while admin/login/verify/assets stay reachable.
  - `classified_product` gates `/customer_products` with 404.
  - `product_query_activation` gates the “Ask a question” link/modal and `contact.seller`.
  - `conversation_system` gates customer/admin conversations, sidebar menu, counts and routes.
  - `coupon_system`, `pickup_point`, `stripe_payment`, `cash_payment` use `FeatureFlags::enabled()`.
- Restored business toggles with real effects:
  - `vendor_system_activation`: gates admin seller CRUD, admin/customer seller menus, followed-seller routes, and seller assignment on products.
  - `wallet_system`: gates a new customer wallet page using `users.balance` and its dashboard menu link.
  - `product_manage_by_admin`: controls whether admins may assign/manage seller products; seller selector is hidden when off.
  - `product_approve_by_admin`: added `products.approval_status` migration; seller products go pending on create/seller change, cannot be published until approved, and admin approve/reject actions were added.
- At this stage, unimplemented provider toggles (16 payment gateways incl. PayPal, 4 social logins) were restored and still saved their settings, but were marked **Integration required** on the Features page and never surfaced as actionable storefront/checkout options. Checkout returned only Stripe (when configured) and Cash on Delivery. Payment gateways were later wired in #448; social logins remain integration-required.
- `resources/views/admin/settings/features.blade.php`: renders all restored cards, disabled checked switch for always-on email verification, and explicit integration-required warnings.
- `resources/views/partials/social-login-buttons.blade.php`: gated social buttons on login/register; no misleading buttons for unconnected providers.
- Verification: `php -l` passed on all changed PHP files; migration ran; `php artisan view:cache` passed. Feature definitions render 35 cards. HTTP smoke checks earlier in this task: IP host + HTTPS flag on → home 200; maintenance on → storefront 503 / admin login bypass (restored); `/customer_products` 200 on / 404 off (restored); product query link only when on (restored off).
- Files: `app/Support/FeatureFlags.php`, `app/Http/Middleware/EnforceFeatureFlags.php`, `bootstrap/app.php`, `resources/views/maintenance.blade.php`, `app/Http/Controllers/AdminController.php`, `app/Http/Controllers/CartController.php`, `app/Http/Controllers/ShopController.php`, `app/Http/Controllers/ProductController.php`, `app/Http/Controllers/DashboardController.php`, `app/Http/Controllers/Admin/SellerController.php`, `app/Http/Controllers/UserSupportController.php`, `app/Http/Controllers/Admin/ProductConversationController.php`, `app/Models/Product.php`, `database/migrations/2026_11_23_000001_add_approval_status_to_products.php`, `routes/web.php`, `app/Providers/AppServiceProvider.php`, `resources/views/admin/settings/features.blade.php`, `resources/views/admin/partials/sidebar.blade.php`, `resources/views/admin/products/index.blade.php`, `resources/views/admin/products/create.blade.php`, `resources/views/admin/products/edit.blade.php`, `resources/views/product/show.blade.php`, `resources/views/user/support/index.blade.php`, `resources/views/user/layouts/sidebar.blade.php`, `resources/views/user/wallet.blade.php`, `resources/views/partials/social-login-buttons.blade.php`, `resources/views/auth/login.blade.php`, `resources/views/auth/register.blade.php`.

## 448. (2026-11-23): All Payment Gateways — Credential-Gated Checkout And Provider Verification

**Status: Implemented (live settlement not testable locally without provider credentials)**

- User asked for **all** payment gateways: saving credentials in Payment Methods + enabling the matching Features toggle must expose the option at checkout and process/confirm payment.
- New centralized layer:
  - `app/Support/PaymentMethods.php`: single source of truth for all payment-method credential definitions (including newly added **ProxyPay** fields) plus encrypted credential decoding and `isConfigured()` checks.
  - `app/Services/Payments/PaymentGatewayManager.php`: registry for all 17 online gateways + COD. `availableMethods()` returns a method only when its toggle is ON **and** all required saved credentials exist. Start/verify/cancel/webhook flows are provider-specific and idempotent.
  - `app/Http/Controllers/PaymentGatewayController.php`: generic return/cancel/webhook endpoints plus Razorpay signature verification.
- Checkout changes:
  - Removed the fake PayPal branch that generated a transaction ID and marked orders paid without provider confirmation.
  - Every non-COD order is created `unpaid`; status changes to `paid` only after re-querying the provider or validating a signed callback. Fully discounted orders are marked paid because there is nothing to charge.
  - Failed gateway start cancels the pending order and restores stock. Cancels restore stock and mark the order failed/cancelled.
  - Added generic gateway views: auto-submit form, raw provider HTML (iyzico), Razorpay checkout JS with server-side HMAC verification, and manual-confirmation page.
- Provider flows wired: PayPal (OAuth + Orders v2 capture), MercadoPago, SSLCommerz, Instamojo, Razorpay, Paystack, VoguePay, PayHere, N-Genius, iyzico 3DS, bKash, ProxyPay, Aamarpay, Authorize.Net hosted page, Payku, plus existing Stripe and COD.
- **Nagad** credentials are validated and the option appears when configured + enabled, but settlement is intentionally left as admin-confirmed from the Nagad merchant dashboard; the order is never auto-marked paid. This is the only gateway without automated settlement because its hosted checkout requires merchant-specific RSA/signing setup that cannot be safely faked.
- Features/Payment Methods UI:
  - Removed the outdated “other gateways are stored for future use” notice.
  - Payment toggles no longer show the “Integration required” badge; social login toggles still do.
  - Checkout shows a generic secure-offsite payment note for configured gateways.
- Routes/CSRF: added `checkout/gateway/{provider}/return|cancel|webhook` and `checkout/razorpay/verify`; CSRF exceptions only for provider return/cancel/webhook endpoints.
- Verification: `php -l` passed on all changed PHP files; `php artisan view:cache` passed; route list confirms gateway endpoints. Tinker smoke test: with no PayPal credentials checkout returns `card, cod`; with PayPal credentials saved **and** toggle on it returns `card, paypal, cod`. Live provider settlement was not executed because no real test credentials are available in this environment.
- Files: `app/Support/PaymentMethods.php`, `app/Services/Payments/PaymentGatewayManager.php`, `app/Http/Controllers/PaymentGatewayController.php`, `app/Http/Controllers/CartController.php`, `app/Http/Controllers/Admin/PaymentMethodSettingsController.php`, `app/Support/FeatureFlags.php`, `routes/web.php`, `bootstrap/app.php`, `resources/views/checkout/payment.blade.php`, `resources/views/checkout/gateway/*.blade.php`, `resources/views/admin/settings/payment-methods.blade.php`, `TASKS.md`.

## 449. (2026-11-23): Fix `/admin/sellers` 500 — Laravel 13 Controller Middleware

**Status: Completed**

- Error in log: `Call to undefined method App\Http\Controllers\Admin\SellerController::middleware()` at controller construction. Laravel 11+ removed the old `$this->middleware()` controller helper.
- Same latent bug existed in `Admin\ProductConversationController` (`conversation_system` gate).
- Added reusable `app/Http/Middleware/EnsureFeatureEnabled.php` and registered the `feature:{key}` alias in `bootstrap/app.php`.
- Removed both controller constructors and moved the gates to routes:
  - Seller CRUD routes now use `feature:vendor_system_activation`.
  - Admin support conversation routes now use `feature:conversation_system`.
- Verification: `php -l` passed on all changed files; controller resolution succeeds in Tinker; `route:list -v` confirms the middleware is attached to all seller routes. With the vendor toggle OFF the page now correctly 404s instead of throwing a 500.
- Files: `app/Http/Middleware/EnsureFeatureEnabled.php`, `bootstrap/app.php`, `routes/web.php`, `app/Http/Controllers/Admin/SellerController.php`, `app/Http/Controllers/Admin/ProductConversationController.php`.


## 450. (2026-11-23): Bulk Import/Export — Higher PHP Limits And Queued Jobs With Progress

**Status: Implemented**

- Server Status reported `upload_max_filesize=2M`, `post_max_size=8M`, `max_execution_time=30s`, while the import form promised `Max 5MB` and export streamed 761k products inside the request.
- PHP limits raised for both Apache modes:
  - `public/.htaccess` (mod_php): 64M upload / 80M post / 300s / 10000 input vars.
  - New `public/.user.ini` (PHP-FPM/CGI): same values. PHP-FPM must be reloaded by the server administrator for these per-directory values to take effect.
  - Imports validate against 64MB and the form shows the correct limit.
- Bulk work is now queued:
  - New `app/Support/BulkOperation.php`: progress state in the cache, operation IDs, recommended-limit warnings.
  - `ImportProductsJob`: streams the uploaded CSV from private storage, counts rows, reuses product normalization/SKU/spec helpers, updates progress every 100 rows, caps stored errors, and deletes the source CSV on success.
  - `ExportProductsJob`: writes a chunked CSV to `storage/app/private/bulk-exports/{id}.csv`, updates progress per 500-row chunk, and exposes a download URL when complete.
  - `BulkOperationController`: stores uploads, dispatches jobs, serves the progress page/JSON, and streams completed exports. Routes keep the existing import/export URLs.
- New admin progress UI (`resources/views/admin/products/bulk-progress.blade.php`) polls status, shows progress bar/counters/errors, and reveals the download button only when the export is complete.
- Server Status now warns when PHP limits are below bulk thresholds and shows queue-worker guidance (`php artisan queue:work` / Supervisor).
- Synchronous import/export bodies were removed from `ProductController`; `parseSpecifications()` is now public static so the import job can reuse it.
- Verification: `php -l` passed on all changed/new PHP files; `php artisan view:cache` passed. Web SAPI check confirmed `upload_max_filesize=64M`, `post_max_size=80M`, `max_execution_time=300`. A header-only import job completed successfully in a direct handle smoke test. Queue worker is required (`QUEUE_CONNECTION=database`, jobs/failed_jobs tables already exist).
- Files: `app/Support/BulkOperation.php`, `app/Jobs/ImportProductsJob.php`, `app/Jobs/ExportProductsJob.php`, `app/Http/Controllers/Admin/BulkOperationController.php`, `app/Http/Controllers/ProductController.php`, `app/Http/Controllers/Admin/SystemStatusController.php`, `routes/web.php`, `resources/views/admin/products/import.blade.php`, `resources/views/admin/products/bulk-progress.blade.php`, `resources/views/admin/system/server-status.blade.php`, `public/.htaccess`, `public/.user.ini`, `TASKS.md`.


## 451. (2026-11-23): Page Builder — Add Block Picker Clipped

**Status: Fixed**

- On `/admin/page-builder/page/{id}/edit`, clicking **Add Block** showed only part of `#pb-block-picker`.
- Cause: `page-builder.css` set `#page-builder-app { overflow: hidden; }`. The picker is `position: absolute` inside that editor shell, so every row below the shell boundary was clipped. Bootstrap 5.3 `.card` itself does not clip, so the editor shell was the limiting ancestor.
- Fix:
  - `#page-builder-app` now uses `overflow: visible` so the dropdown can extend past the editor shell.
  - `.pb-block-picker` now has `min-width: 300px`, `max-width: calc(100vw - 32px)`, `max-height: min(70vh, 560px)`, `overscroll-behavior: contain`, and a higher `z-index: 1080` so long block lists remain scrollable and sit above editor content.
  - Bumped the stylesheet cache-buster to `?v=1.0.1` and cleared/rebuilt compiled views.
- Updated all three copies to avoid regressions on re-publish/re-install: `public/vendor/page-builder/css/page-builder.css`, `vendor/stevestore/laravel-page-builder/resources/css/page-builder.css`, and `/var/www/html/laravel-page-builder/resources/css/page-builder.css`.
- Verification: served CSS confirms `overflow: visible` and the new picker dimensions; compiled view confirms the new cache-buster.


## 452. (2026-11-23): Page Builder — Textarea Styling Class

**Status: Completed**

- Added the shared `textarea-control` class to page-builder textareas generated by `editor/partials/field.blade.php` (`textarea` and `code` field types) so they match the admin panel's standard textarea styling. This also covers repeater subfields such as `blocks[0][data][items][0][quote]` from `TestimonialCarousel`.
- File: `vendor/stevestore/laravel-page-builder/resources/views/editor/partials/field.blade.php`.
- Verification: compiled view confirms `class="form-control textarea-control"` and `class="form-control textarea-control pb-code"`.


## 453. (2026-11-23): Admin Products — AJAX Searchable Category/Brand/Seller Selects

**Status: Implemented**

- Product list/create/edit were printing every category (21,300), brand, and seller into dropdowns (All Products ~4.7 MB / 3.6 s; create/edit ~2.9 MB each).
- Added `GET admin/products/lookup?type=category|brand|seller&q=...` in `ProductController` returning at most 50 `{id, text}` matches as JSON. Brands remain active-only.
- Controller methods now pass only the currently selected option(s) to the views instead of full collections. For edit, `old()` values are preserved. Bulk "Move to category" also uses the AJAX select.
- Extended `public/assets/front/js/searchable-select.js` with an opt-in `data-ajax-url` mode: debounced remote search, loading/error states, native `<select>` remains authoritative for form submission, and the selected/placeholder options are preserved while fetched results are replaced.
- Added CSS so a generated searchable trigger stays hidden when its native select has `d-none` (bulk category action).
- Verification: `php -l` on controller/routes, `node --check` on JS, `view:cache`, route registered, and lookup endpoint smoke-tested for all three types.
- Files: `app/Http/Controllers/ProductController.php`, `routes/web.php`, `resources/views/admin/products/index.blade.php`, `resources/views/admin/products/create.blade.php`, `resources/views/admin/products/edit.blade.php`, `public/assets/front/js/searchable-select.js`, `public/assets/front/css/searchable-select.css`, `TASKS.md`.


## 454. (2026-11-24): General Settings (Header/Footer) — 21 MB page / menu link options

**Status: Implemented**

- `Settings > General Settings (/admin/settings/header)` was 21.5 MB / 68,895 lines. The navigation menu builder printed all 21,300 categories three times (All + Search + children) = ~63,965 link options, plus a full availability map.
- `AdminController::headerSettings()` no longer loads/passes `$categories`, `$pages`, `$posts`. It now builds `availableMenuUrls` only for links already saved in `nav_menu` (recursive, page/post/category-aware), so the page no longer embeds every URL.
- Added `GET admin/settings/header/links?type=pages|posts|categories&mode=recent|all|search&q=&page=` (`admin.settings.header.links`) returning paginated JSON `{data:[{label,url,type}], current_page, last_page, per_page, total}`. Recent = 10 rows, All/Search = 20 rows.
- Rebuilt the menu picker panel in `resources/views/admin/settings/header.blade.php`:
  - Pages/Posts: Recent / All / Search
  - Categories: All / Search
  - Search is debounced (250 ms), requests are abortable, and Prev/Next + `Page X of Y · N links` pagination is rendered.
  - Selections still add normal menu rows; newly selected linked URLs are added to the availability map so they are not falsely flagged as unavailable.
- Verification: `php -l` on controller/routes, `route:list` shows the new endpoint, `view:cache` passes, endpoint smoke-tested (categories page 2 = 20 items / 21,300 total / 1,065 pages; search returns matches; pages/posts return data), rendered page measured at ~257 KB / 5,043 lines with `category_boxes=0`, and all 21 inline JS blocks pass `node --check`.
- Files: `app/Http/Controllers/AdminController.php`, `routes/web.php`, `resources/views/admin/settings/header.blade.php`, `TASKS.md`.


## 455. (2026-11-24): Pagination summary text — Page X of Y (Total)

**Status: Implemented**

- Replaced the global pagination summary in `resources/views/vendor/pagination/gs-pagination.blade.php`:
  - Old: `Showing 1 to 24 of 493679 results`
  - New: `Page 1 of 20570 (493679 Total)`
- Uses `$paginator->currentPage()`, `$paginator->lastPage()`, and `$paginator->total()`.
- Removed the duplicate page-count span from `resources/views/vendor/pagination/_go-to-page.blade.php` so the new text appears once (next to the Go to page control).
- Verification: `view:clear` + `view:cache` passed; rendered paginator output confirms `Page 1 of 20570 (493679 Total)` and no `Showing` text.
- Files: `resources/views/vendor/pagination/gs-pagination.blade.php`, `resources/views/vendor/pagination/_go-to-page.blade.php`, `TASKS.md`.


## 456. (2026-11-24): Admin list result info — consistent Page X of Y (Total)

**Status: Implemented**

- Follow-up to #455: admin list pages had their own `Showing x-y of total` header text, which conflicted with the new pagination summary.
- Replaced all 24 admin occurrences with `Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }} ({{ $paginator->total() }} Total)`.
- Covered: brands, orders, subscribers, contacts, returns, customers (index/show), staff, product stock, users, blogs, blog categories, pages, FAQs, coupons (index/show), reviews, sellers, file revisions, home-page sections, categories, revisions, support conversations, support tickets.
- `Showing: {{ $selectedFile }}` in the log viewer was intentionally left unchanged (not paginator info).
- Verification: grep confirms no `Showing $...firstItem()` patterns remain under `resources/views/admin`; `view:clear` + `view:cache` passed.
- Files: 24 admin Blade views under `resources/views/admin/`, `TASKS.md`.


## 457. (2026-11-24): Storefront /search — lazy sidebar category tree

**Status: Implemented**

- `/search?search=oxygen+sensor` was still 13.2 MB / 135,486 lines because the shop sidebar recursively printed all 20,891 category rows.
- Added `CategoryTree::pathTo(int $categoryId)` to return the root-to-node id path, so only the active category branch is expanded on category pages.
- Added `GET /ajax/shop-category-children?parent=ID&level=N&active[]=...` (`ajax.shop-category-children`) in `ShopController`, returning one level of child categories per request.
- Added `resources/views/partials/shop-sidebar-category-items.blade.php` and replaced the full nested loop in `resources/views/shop/index.blade.php` with a lazy partial. Each expandable branch fetches its children on `show.bs.collapse`; already-expanded active branches are loaded on page ready. Failed loads show a retry button.
- Category pages now pass `activeCategoryIds` so the current branch (and its ancestors) expand automatically.
- Verification: `php -l` on controller/service/routes, `node --check` on the inline JS, `view:clear` + `view:cache`, route registered, endpoint smoke-tested for normal and active branches.
- Result: `/search?search=oxygen+sensor` dropped from 13,245,469 bytes / 135,486 lines to 413,117 bytes / 4,705 lines (~97% smaller); the 20,891-child `<li>` pattern is gone. `/category/adhesives` is 408,537 bytes with `data-active-categories="1,1334"` and only the active branch expanded.
- Files: `app/Services/CategoryTree.php`, `app/Http/Controllers/ShopController.php`, `routes/web.php`, `resources/views/shop/index.blade.php`, `resources/views/partials/shop-sidebar-category-items.blade.php`, `TASKS.md`.


## 458. (2026-11-24): Storefront titles, suggestion dedupe & common URL aliases

**Status: Implemented**

- Imported product names contain the same title on several lines in different languages (e.g. `Bonde pour sonde d’oxygène / Oxygen Sensor Bung / Tapón para Sensor de Oxígeno`), which made search cards and autocomplete hard to read.
- Added `cleanProductTitle()` in `app/helpers.php`: normalizes whitespace and prefers the first ASCII-only line, falling back to the first line when every translation contains non-ASCII characters.
- `resources/views/partials/product-card.blade.php` now renders cleaned titles on storefront listings.
- `ShopController@suggestions` now uses the cleaned title and dedupes by normalized cleaned name (falling back to part number/slug), so repeated `Oxygen Sensor Connector` / `Oxygen Sensor, Genuine OE Part` suggestions no longer crowd out other matches.
- Added 301 aliases for common URLs: `/login` → `/users/login`, `/register` → `/users/registration`, `/shop` → `/search`, `/about` → `/about-us`.
- Verification: `php -l` on helpers/controller/routes, `view:clear` + `view:cache`, route list shows the aliases, all four aliases return 301 with the expected `Location`, search titles are now single-line English, and the suggestions endpoint returns unique names.
- Files: `app/helpers.php`, `app/Http/Controllers/ShopController.php`, `resources/views/partials/product-card.blade.php`, `routes/web.php`, `TASKS.md`.


## 459. (2026-11-24): Credential-waiting integrations — ready to go when admin saves keys

**Status: Implemented (live provider settlement still needs the real credentials)**

- User asked that every task currently waiting on credentials be made ready now, so adding credentials in Admin + enabling the matching Features toggle is enough and no further code change is needed.
- Payment Methods UI:
  - Every configured secret field now gets its own reveal/edit icon pair, fixing gateways with multiple secrets (bKash, Stripe, etc.).
  - Added optional `STRIPE_WEBHOOK_SECRET` with signed-webhook verification in `CartController::stripeWebhook()`; an unsigned/invalid callback now gets HTTP 400 instead of being trusted.
  - Added N-Genius sandbox toggle, MercadoPago currency help, ProxyPay signature documentation, and Nagad mode/key help.
- Provider adapters (`PaymentGatewayManager`) hardened end to end:
  - **Nagad** is no longer a stub: initialize + complete now use merchant RSA encryption/signing, sandbox/live base URLs, callback URL, payment reference storage, and signed/decrypted verification with order/amount/merchant binding. The old “confirm manually from dashboard” wording was removed.
  - **bKash** uses `/token/grant`, `/create` (with `mode`, `payerReference`, `bkashURL`) and `/execute`, with invoice and amount checks.
  - **iyzico** uses `IYZWSv2` headers with random key, URI-path signing, `x-iyzi-rnd`, and stores checkout details before redirect fallback.
  - **Authorize.Net** now builds the required `settingName`/`settingValue` JSON settings XML, includes `cancelUrl`, binds invoice/amount on return, and uses the sandbox hosted form URL.
  - **ProxyPay** uses one canonical sorted `key=value&...` HMAC helper for both outbound and return signatures; amount/currency are checked.
  - **PayPal** uses modern `payment_source.paypal.experience_context`, idempotent capture key, and amount/currency checks.
  - **MercadoPago**, **SSLCommerz**, **Paystack**, **N-Genius**, **Instamojo**, **VoguePay**, and **Nagad** now bind callbacks to the resolved order and re-query/signature-verify before payment is marked paid.
  - **N-Genius** webhook order resolution now understands nested `order.reference` / `order.merchantOrderReference`.
- Order state/security:
  - `markPaid()` is atomic (`lockForUpdate`), ignores cancelled orders, and returns whether the transition happened; webhooks no longer clear the checkout cart.
  - Webhook failures now return HTTP 422 so providers retry; successful confirmations return 200.
  - `cancel()` re-queries the provider before restoring stock so a last-second paid order is not cancelled.
  - CSRF exceptions now cover subdirectory installs, and FORCE_HTTPS no longer redirects provider webhook POSTs.
- Email readiness:
  - Order confirmation/status emails changed from `Mail::queue()` to safe synchronous `Mail::send()` with try/catch logging, so saved SMTP credentials work without requiring a queue worker.
  - Admin SMTP settings page already applies DB-backed driver/host/port/credentials/sender at runtime; no env-only dependency remains.
- Verification: `php -l` passed on all changed PHP files; `node --check` on touched JS; `view:clear` + `view:cache` passed; checkout routes confirmed. Tinker smoke tests confirmed `card,cod` with no new credentials; N-Genius fake credentials satisfy `isConfigured()` + sandbox toggle; Stripe webhook signature accepts a correctly signed payload and rejects a bad one. Nagad RSA encryption/signing was round-tripped with a generated key pair (correct signed response accepted, bad signature rejected), including full-PEM and raw-key normalization and the `050` currency code. A follow-up read-only audit found no remaining credential/checkout blocker or path that marks an order paid without provider confirmation.
- Files: `app/Support/PaymentMethods.php`, `app/Services/Payments/PaymentGatewayManager.php`, `app/Http/Controllers/CartController.php`, `app/Http/Controllers/Admin/OrderController.php`, `app/Http/Middleware/EnforceFeatureFlags.php`, `bootstrap/app.php`, `resources/views/admin/settings/payment-methods.blade.php`, `resources/views/checkout/gateway/pending.blade.php`, `TASKS.md`.


## 460. (2026-11-24): Admin product form — auto-parts fields and multi-row fitment editor

**Status: Implemented**

- User asked for the missing auto-parts product fields but explicitly requested that **part number/SKU be skipped** because SKU is not used. The existing auto-generated readonly `part_number` remains untouched; no SKU input was added.
- Added nullable `products` columns: `meta_title`, `meta_description`, `oem_numbers_json`, `interchange_numbers_json`, `weight_kg`, `dimensions_json`, `condition`, `warranty_months`, `warranty_text` (`2026_11_24_000001_add_auto_parts_fields_to_products_table.php`).
- Added `product_fitments` table (`2026_11_24_000002_create_product_fitments_table.php`) and `ProductFitment` model for `{year_from, year_to, make, model, engine, sort_order}` rows.
- `ProductController` create/edit/store/update now load, validate, save and display all new fields. OEM/interchange lists accept line, comma or semicolon separated values; dimensions are stored as structured JSON; warranty supports months plus free text; condition defaults to `New`.
- Multi-row fitment editor replaces the old no-op single year/make/model inputs. It uses the cached vehicle catalog for year selects and make suggestions (without embedding 14k+ models/engines in the form) and a `<template>`/repeater pattern matching the home-page editor.
- Saving fitments mirrors each year in a range into `product_vehicle` with a `admin-fitment:` note prefix, so the existing storefront vehicle filter and `VehicleCatalog` dropdowns keep working without changing the 42M-row pivot schema. Only those generated rows are replaced on edit; imported fitments are preserved.
- `VehicleCatalog` cache keys are now versioned and `flush()` increments the version, so all year/make/model/engine caches are invalidated after fitment changes instead of only two keys. Unchanged fitments are detected and do not trigger a cache flush.
- Slug is editable in the new SEO tab; blank slugs are generated from name + part number, collisions are suffixed, and editing keeps the existing slug unless changed.
- Storefront product page now uses saved SEO meta title/description, shows fitment badges and per-vehicle fit confirmation from the new fitment rows, and renders the specifications table again (imported `title/value` specs and admin `label/value` specs both work). Condition, warranty, weight, dimensions, OEM and interchange numbers are merged into that table.
- Admin product details page now shows condition, warranty, weight/dimensions, OEM/interchange numbers, SEO fields and a fitments table.
- Verification: `php -l` on all changed PHP files, `view:clear` + `view:cache`, migrations ran successfully, and Tinker smoke tests confirmed create/update persistence, fitment mirroring (including range expansion), invalid fitment rejection, catalog cache invalidation, rollback cleanup, and successful rendering of admin create/edit/details and storefront product pages.
- Files: `app/Http/Controllers/ProductController.php`, `app/Models/Product.php`, `app/Models/ProductFitment.php`, `app/Services/VehicleCatalog.php`, `database/migrations/2026_11_24_000001_add_auto_parts_fields_to_products_table.php`, `database/migrations/2026_11_24_000002_create_product_fitments_table.php`, `resources/views/admin/products/create.blade.php`, `resources/views/admin/products/edit.blade.php`, `resources/views/admin/products/partials/fitment-editor.blade.php`, `resources/views/admin/products/show.blade.php`, `resources/views/product/show.blade.php`, `resources/views/partials/product-buy-return-policy.blade.php`, `TASKS.md`.


## 461. (2026-11-24): Add/Edit Product selects no longer overflow narrow admin layouts

**Status: Implemented**

- User reported that the manually set `min-width: 200px` on `select.form-select` makes Add/Edit Product selects overlap the next field or escape the section when the admin sidebar is expanded or on a small screen.
- Root cause: the shared storefront stylesheet has a global `.form-select { min-width: 200px }` rule. It also applies to the generated searchable-select trigger (`.ss-control.form-select`), so both native and enhanced selects were forced to 200px even inside much narrower grid columns.
- Added page-scoped overrides in `public/assets/front/css/backend.css` for `body.admin-products-create-page` and `body.admin-products-edit-page`: grid columns, `.form-select`, `.form-select-wrapper` and `.ss-control` now use `box-sizing:border-box`, `min-width:0`, `max-width:100%` and `width:100%`. Higher specificity wins over the later-loaded global rule without changing storefront filter selects.
- Product form tabs now wrap instead of spilling outside the card in a narrow content area.
- Fitment editor rows now use `col-6 col-sm-4 col-xl-2/3/1` instead of `col-md-2/3/1`, so the year/make/model/engine/remove columns degrade cleanly on phones and tablets.
- Verification: `view:clear` + `view:cache`, CSS syntax check, and rendered admin create/edit pages confirm the scoped rules are present and no global storefront select behavior was changed.
- Files: `public/assets/front/css/backend.css`, `resources/views/admin/products/partials/fitment-editor.blade.php`, `TASKS.md`.


## 462. (2026-11-24): Product admin action buttons matched to other admin pages

**Status: Implemented**

- Aligned Add/Edit Product form actions and the Add Product list button with the standard admin button pattern used on other create/edit pages (`btn btn-primary steve-btn gap-1` with Font Awesome icons).
- Add Product form: Save now uses `<i class="fas fa-save"></i> Save Product`; Cancel now has `<i class="fas fa-times"></i>Cancel` instead of being iconless. Removed the extra `px-4` padding and normalized the separator/action spacing.
- Edit Product form: same pattern for `Update Product` and `Cancel`; cleaned up the duplicate `gap-1` class.
- Products list: the `Add Product` button now also carries `steve-btn`, matching the neighbouring Import action and other admin header actions.
- Verification: `view:clear` + `view:cache` and rendered admin index/create/edit pages confirm the updated button classes/icons.
- Files: `resources/views/admin/products/index.blade.php`, `resources/views/admin/products/create.blade.php`, `resources/views/admin/products/edit.blade.php`, `TASKS.md`.


## 463. (2026-11-24): Fitment row action icons switched to admin SVG action buttons

**Status: Implemented**

- User asked that the icons inside Add/Edit Product → Vehicle Details `fitment-rows` use the same inline SVG `action-btn` style as the Products index action buttons (View/Edit/Delete), instead of Font Awesome `<i>` icons.
- Replaced both row controls with the shared action-button markup:
  - Add row: `action-btn btn-view-live` with a stroke-style plus SVG.
  - Remove row: `action-btn btn-cancel` with the exact trash SVG used by the Products index delete button, including tooltip attributes.
- Restored the remove button on existing saved rows (it was commented out) and made the initial row markup match the `<template>` used for newly added rows, so both add and remove work consistently.
- Rebalanced fitment columns from `2/2/2/2/3/1` to `2/2/2/2/2/2` so the two 36px circular action buttons fit at `col-xl-2` without squeezing the engine field.
- `addFitmentRow()` now initializes Bootstrap tooltips on injected rows, which the layout's one-time tooltip pass does not cover.
- Verification: `view:clear` + `view:cache`; rendered create/edit pages contain the inline `action-btn` SVGs and no `fas fa-times`/`fas fa-plus` inside fitment rows.
- Files: `resources/views/admin/products/partials/fitment-editor.blade.php`, `TASKS.md`.


## 464. (2026-11-24): Search Categories settings — paginated autocomplete picker

**Status: Implemented**

- `Admin > Settings > Search Categories` was 7.6 MB / 215,042 lines because it recursively rendered all 21,300 categories (plus orphans) as checkboxes on page load.
- `AdminController@searchCategorySettings()` no longer loads `Category::searchCategoryTree()` or the orphan-category query; it only resolves the already-saved `search_category_menu` items for the Selected list.
- Added `GET /admin/settings/search-categories/lookup?q=&page=` (`admin.settings.search-categories.lookup`) returning paginated JSON `{data:[{id,label,url,context}], current_page, last_page, per_page, total}` with 20 rows per page. Search is a `LIKE` filter on category name; `context` shows the immediate parent when present.
- Rebuilt the Available Categories panel as an AJAX picker: debounced search (250 ms), Prev/Next buttons, `Page X of Y (N Total)` info, loading/empty/error + retry states, and checkboxes generated from the JSON response. Existing add/reorder/remove JS and hidden JSON payload are unchanged, so saving behaviour is identical.
- Checkbox state is derived from the current Selected list, so already-picked categories stay checked as pages are navigated.
- Verification: `php -l` on controller/routes, `view:clear` + `view:cache`, route registered, endpoint smoke-tested (page 1 = 20 rows / 21,300 total / 1,065 pages; `oxygen` search = 41 matches). Inline JS passes `node --check`. Rendered settings page dropped from 7,675,197 bytes / 215,042 lines to 99,507 bytes / 1,322 lines (~98.7% smaller), with 0 checkbox inputs and no category-tree includes in the HTML.
- Files: `app/Http/Controllers/AdminController.php`, `routes/web.php`, `resources/views/admin/settings/search-categories.blade.php`, `TASKS.md`.


## 465. (2026-11-24): Configurable supplier stock feed, scheduled sync and low-stock threshold

**Status: Implemented (integration-ready; feed credentials/URL supplied by admin)**

- Audit flagged stock as placeholder-like: Out of Stock 0, Low (1–5) 401,429 of 761,107 products, many with `vendor_stock = 1`.
- Confirmed from code that there is **no** supplier feed/API yet. Stock enters via `ImportProductsJob` CSV `stock` column, manual admin bulk edits and order decrements. No fake/guessed endpoint was added.
- Added `App\Support\StockSync` as the single source of truth for feed settings and defaults (`low_stock_threshold = 5`, format `json`, key `part_number`, stock field `stock`, frequency `manual`, disabled). It tolerates DB/migration errors so `artisan` stays usable.
- Added `Admin > Products > Stock Sync` settings page (`GET/POST /admin/settings/stock-sync`) with feed URL, optional Bearer token (readonly/reveal/edit like Payment Methods), format, optional JSON items path, key field (`part_number|id|slug`), stock field, schedule (`manual|hourly|daily`), enable switch, low-stock threshold and last-run/result. Shows **Integration required** until a URL is saved and sync is enabled. `POST /admin/settings/stock-sync/run` queues a sync; Save rejects enabling without a URL.
- Added `App\Services\StockFeedSyncService`: fetches JSON/CSV with a 60s timeout, optional Bearer token, optional dot-notation items path, normalizes key→stock rows and updates `vendor_stock` in chunks of 500 **only** for products present in the feed. Products absent from the feed are never zeroed. Dry-run mode reports matches without writing. Failures and skips are recorded in settings.
- Added `SyncProductStockJob` (queued, used by Run now) and `stocks:sync` command (`--dry-run`, `--force`). Scheduled hourly/daily via `routes/console.php` only when the toggle is on and frequency is not `manual`; `withoutOverlapping()` prevents parallel runs.
- Stock Management now reads `low_stock_threshold` from settings (query `?threshold=` still overrides) and links to the new settings page. Sidebar Products menu gained a Stock Sync link and active-state handling; it is nested under **Stock Management** (not as a sibling item). Stock Management keeps its direct link and now has a Bootstrap collapse chevron toggle that expands/collapses the Stock Sync child, with scoped indentation/arrow CSS matching the other toggle groups.
- Verification: `php -l` on all changed/new PHP files, `view:clear` + `view:cache`, route list, rendered settings page, inline JS `node --check`, controller save smoke test (302 + saved values, then restored), `schedule:list` with toggle off (no stock entry) and with hourly enabled (`0 * * * * php artisan stocks:sync`), then restored. `stocks:sync --dry-run` skips safely when unconfigured; with `Http::fake`, JSON array, nested `data.products` path and CSV dry-runs matched the expected rows, a real fake-feed run wrote only the two mapped products (values restored afterwards), and a missing part number was reported as not found without touching other stock.
- Files: `app/Support/StockSync.php`, `app/Services/StockFeedSyncService.php`, `app/Jobs/SyncProductStockJob.php`, `app/Console/Commands/SyncProductStock.php`, `app/Http/Controllers/AdminController.php`, `app/Http/Controllers/ProductController.php`, `routes/web.php`, `routes/console.php`, `resources/views/admin/settings/stock-sync.blade.php`, `resources/views/admin/partials/sidebar.blade.php`, `resources/views/admin/products/stock.blade.php`, `TASKS.md`.


## 466. (2026-11-24): 500 root cause fixed + safe 500 debug facility

**Status: Implemented**

- Root cause of the latest `500 Server Error`: `ErrorException: Undefined variable $setupConfigExpanded` in the compiled admin sidebar. The inline `@php($stockSubmenuExpanded = ...)` added in #465 caused Blade to mispair with the later `@php ... @endphp` Settings block, leaving raw `@php` / `{{` in `storage/framework/views/8ca4fa...php`. Fixed by moving `$stockSubmenuExpanded` into the sidebar's main `@php` block. Compiled sidebar now has 0 raw `@php` and 0 raw `{{`; standalone render is 28,083 bytes and clean.
- Added `App\Support\ErrorDebugger` + dedicated `debug500` log channel (`storage/logs/debug500.log`). Every reported throwable is written as a structured JSON line with id, time, env, method/path, route name, user id, exception class/message/file/line, first 8 stack frames, PHP version and peak memory. Expected 4xx traffic (validation, auth, token mismatch, HTTP status < 500) is filtered out.
- Deliberately **not** logged: request body, cookies, headers, query strings or secrets. Debug logging is wrapped so it can never break normal exception handling. No public stack traces are rendered.
- Added `php artisan debug:last-error {--limit=} {--json}` for CLI diagnosis.
- Added admin-only `GET /admin/system/debug-errors` (`admin.system.debug-errors`) with a compact HTML viewer showing the last 30 records and CLI hint. It is available in `local` automatically, or in production only when `DEBUG_ERRORS_ENABLED=true` (config `app.debug_errors_enabled`); otherwise it 404s. Added a gated **500 Debug** link under `Admin > System`. Enabled `DEBUG_ERRORS_ENABLED=true` in `.env` on this install (`APP_ENV=production`) so the viewer is usable here; set it back to false if the box becomes public.
- Verification: `php -l` on all changed/new PHP files (warning-free after using `\Throwable` in the global-namespace bootstrap), `view:clear` + `view:cache`, route registered (`admin.system.debug-errors`), command registered (`debug:last-error`), controlled `report(new RuntimeException('DEBUG TEST 500'))` captured with full class/message/file/frames, `debug:last-error --limit=1` printed it, and the admin viewer rendered (46,570 bytes) with the root-cause section. Test log entry removed afterwards.
- Files: `bootstrap/app.php`, `config/app.php`, `config/logging.php`, `app/Support/ErrorDebugger.php`, `app/Console/Commands/DebugLastError.php`, `app/Http/Controllers/Admin/SystemStatusController.php`, `routes/web.php`, `resources/views/admin/partials/sidebar.blade.php`, `resources/views/admin/system/debug-errors.blade.php`, `TASKS.md`.


## 467. (2026-11-24): Admin sidebar — Stock Management toggle styling + persistent parent highlight

**Status: Implemented**

- User noted that under `Admin > Products`, the **Stock Management** row had a different font/icon style, and then that its chevron toggle style was different from the other groups.
- Root cause of the first mismatch: Stock Management was wrapped in an extra `<div class="stock-management-parent d-flex ...">`, so it was no longer a direct `li > a` child and missed `.admin-sidebar .admin-sidebar-drop .submenu li > a` styles (12px / `#aebdcc` / `7px 9px` padding / shared active state). It was inheriting the base `.nav-item-sub` styles (13px / `rgba(255,255,255,.5)` / `8px 20px 8px 42px`).
- Final fix matches the existing **Shipping / Facebook / Google / Setup / System** pattern exactly: one `nav-item sidebar-link` toggle row with `fa-warehouse` icon + `<span>Stock Management</span>` + `fa-chevron-down submenu-arrow`, followed by a plain `ul.collapse.submenu` containing two `nav-item-sub` children — **Stock Overview** (existing `/admin/products/stock`, keeps the direct link reachable) and **Stock Sync** (`/admin/settings/stock-sync`). The intermediate icon-only chevron button and all custom `.stock-management-*`/`.stock-sync-submenu` CSS were removed, so shared sidebar CSS alone controls the look.
- `$stockSubmenuExpanded` now also covers `admin.products.stock` and `admin.products.bulk-stock`, so the group expands automatically on those pages (not only on Stock Sync).
- Active-state bug fixed: the collapse JS used `link.classList.toggle('active', isExpanded)`, so manually collapsing a group while still on one of its child pages removed the parent highlight. It now captures the server-rendered route-active state as `routeActive` and applies `link.classList.toggle('active', isExpanded || routeActive)`. `expanded`/`collapsed`/`aria-expanded` still follow the real collapse state. This applies to every toggle group, not only Products.
- Verification: inline sidebar JS passes `node --check`; `view:clear` + `view:cache` leaves compiled sidebar with 0 raw `@php` and 0 raw `{{`; compiled output confirms the `nav-item sidebar-link` toggle, both child links, and the `routeActive` logic.
- Files: `resources/views/admin/partials/sidebar.blade.php`, `public/assets/front/css/backend.css`, `TASKS.md`.


## 468. (2026-11-24): Admin Pages list shows the exact public URL

**Status: Implemented**

- User asked what URL a page created from `Admin > Pages` gets, then objected that the admin list showed only `/{slug}` and forced them to guess the real URL.
- Actual public route is `GET /page/{slug}` (`page.show`). Slug is `Str::slug(slug field ?: title)`; duplicates are rejected; inactive pages 404; mapped slugs such as `faq`/`about`/`privacy-policy` 301 to their canonical routes.
- `resources/views/admin/pages/index.blade.php`: column header changed from **Slug** to **URL** (still sorts by the `slug` column). The cell now renders the exact `/page/{slug}` path instead of `/{slug}`:
  - Active pages: clickable link to `route('page.show', $page->slug)` with `target="_blank" rel="noopener"`; tooltip shows the full absolute URL.
  - Inactive pages: muted `/page/{slug}` with a tooltip explaining that it returns 404 until activated.
  - Trashed pages: muted struck-through `/page/{slug}`.
- Added an eye **View public page** action button (same `btn-view` style as other admin lists) for active pages, opening the same public URL in a new tab.
- Verification: `view:clear` + `view:cache`; compiled view contains `/page/{slug}` output, `route('page.show', ...)` links and the new View action. No commit made.
- Files: `resources/views/admin/pages/index.blade.php`, `TASKS.md`.


## 469. (2026-11-24): Custom pages moved to root URL + WordPress-style slug suffix

**Status: Implemented**

- User asked whether the public URL could be `/{slug}` instead of `/page/{slug}`, and then suggested WordPress-style auto-numbering (`slug`, `slug-2`, `slug-3`) when a slug is already taken. Implemented both.
- `PageController` now resolves slugs via `resolveSlug()`: `Str::slug()` base, then `base-2`, `base-3`, ... until free. It checks **soft-deleted rows too** (`withTrashed()`, because the DB `slug` index is unique even for trashed pages) and also **reserved single-segment frontend routes** derived from `Route::getRoutes()` (`admin`, `search`, `blog`, `cart`, `faq`, `home`, ...). Editing a page without changing its slug stays a no-op via `$currentSlug`. The old hard “slug already in use” validation error was removed; the flash message now reports the final slug when it was adjusted.
- Public route moved to the root: `GET /{slug}` → `HomeController@page`, named `page.show`, constrained by `where('slug', '[a-z0-9-]+')` and registered **last** in `routes/web.php` so it cannot shadow `/up`, `/admin`, `/search`, `/cart`, `/faq`, etc. Verified route matching for `/up`, `/admin`, `/search`, `/cart`, `/faq`, `/shipping-policy`, `/page/shipping-policy` and `/favicon.ico`.
- Old `GET /page/{slug}` is kept as `HomeController@legacyPage` and returns a **301** to the canonical URL: mapped slugs (`faq`, `home`, `privacy-policy`, ...) go to their named route, everything else to the new `page.show` root URL. No SEO/backlink break.
- Updated all URL generators: sitemap `pages` entries use `/{slug}`; `AdminController` page-link picker prefix changed from `/page/` to `/`; admin Pages list shows `/{slug}` (already clickable via `route('page.show')`); `config/page-builder.php` comment clarified.
- Verification: `php -l` on all changed PHP files; route matching table confirmed; `resolveSlug()` smoke tests returned `shipping-policy`, `faq-2`, `home-2`, `categories-2`, `about-2` for the existing `about` slug, `compare-2-2` for a trashed slug, and unchanged `about` when editing that page; legacy redirects returned 301 to `/shipping-policy`, `/faq` and `/`; existing page render (79,139 bytes); `view:clear` + `view:cache` clean.
- Files: `app/Http/Controllers/Admin/PageController.php`, `app/Http/Controllers/HomeController.php`, `app/Http/Controllers/AdminController.php`, `routes/web.php`, `config/page-builder.php`, `resources/views/admin/pages/index.blade.php`, `TASKS.md`.


## 470. (2026-11-24): Explicit Page Builder option + design mode in All Pages

**Status: Implemented**

- User pointed out that “All Pages” uses the classic Summernote editor while Page Builder uses a separate block editor, so the two designs can mix on the live page. Added a clear Page Builder entry point inside All Pages and an explicit per-page design mode so only the chosen editor's output renders.
- All Pages list: new toolbar **Page Builder** button (`page-builder.index?type=page`), a **Design** column (`Classic` / `Builder` / `Both` + block count), and a per-row **Open in Page Builder** action linking to `page-builder.edit` with the block count in its tooltip. Empty-state colspan updated 7 → 8.
- Add/Edit Page forms: new shared `_design_mode.blade.php` select with three modes — `classic` (Content editor only), `builder` (blocks only; classic content kept but hidden), `both` (legacy mixed rendering). Builder mode hides the classic Content tab/textarea via JS; switch back to Classic/Both to reveal it. Both forms explain the two editors; Edit has a direct **Page Builder** button in the header.
- Page Builder editor now has an overridden view with a **Classic Editor** button and a mode badge; if a classic-mode page is opened there, a warning explains that saving blocks will auto-switch it to Builder mode.
- New `design_mode` column on `pages` (migration `2026_11_24_000003`), backfilled from existing data without changing any live design: blocks-only → `builder`, content-only → `classic`, meaningful content + blocks → `both`. In practice all current pages resolved to `builder`/`classic` because the older “content” on block pages is structural markup with no text (already hidden by the existing frontend check).
- `Page::designMode()` centralises the mode with a legacy fallback. `AppServiceProvider` registers a `Page::saved` observer: when Page Builder saves `content_blocks` for a classic page, it quietly flips `design_mode` to `builder` (reads the stored/default value, not the in-memory null right after `create()`). Classic content is never deleted, so the switch is reversible by choosing Classic/Both again.
- Frontend `pages/show.blade.php` now honours the mode: classic content only for `classic`/`both`, `<x-page-blocks>` only for `builder`/`both`. Verified with rendered views: builder page shows block markup and no classic `<div class="page-content">`; in-memory `both` page shows both markers; `builder` with classic content hides the classic text.
- Verification: migration ran clean; `php -l` on all changed PHP; `view:clear` + `view:cache` clean (0 raw `@php`/`{{` in compiled views); observer smoke test proved `classic → builder` on `setBlocks()` with content present; route URL generation for `page-builder.edit`, `page-builder.index` and `admin.pages.edit` correct.
- Follow-up (same day): user reported the Page Builder action icon looked off. Added a dedicated `.btn-builder` variant in `public/assets/front/css/style.css` (purple circle, 16×16 SVG via the existing `.action-btn svg` rule) and added `aria-label`/`title` to the link. It now matches the other coloured action icons (view/blue, edit/orange, delete/red, restore/green).
- Files: `database/migrations/2026_11_24_000003_add_design_mode_to_pages_table.php`, `app/Models/Page.php`, `app/Providers/AppServiceProvider.php`, `app/Http/Controllers/Admin/PageController.php`, `resources/views/admin/pages/_design_mode.blade.php`, `resources/views/admin/pages/index.blade.php`, `resources/views/admin/pages/create.blade.php`, `resources/views/admin/pages/edit.blade.php`, `resources/views/pages/show.blade.php`, `resources/views/vendor/page-builder/admin/page-builder/edit.blade.php`, `public/assets/front/css/style.css`, `TASKS.md`.


## 471. (2026-11-24): One inbox for inquiries — Support Tickets migrated and removed

**Status: Implemented**

- User reported that product inquiries were duplicated in **Questions / Inquiries** and **Support Tickets**; chose to keep one Inquiries inbox and migrate old ticket data instead of syncing two modules.
- New migration `2026_11_24_000004_migrate_support_tickets_to_inquiries.php` adds `category`, `priority` and `legacy_ticket_id` to `contacts`, migrates ticket subjects/categories/priorities/status/replies into contacts, then drops `support_ticket_replies` and `support_tickets`. Exact duplicate product inquiries are merged into the existing contact instead of inserted twice. In this install: ticket #2 (`QWREWA`, Order/high, reply `hi`) became Contact #26; ticket #7 (QA product inquiry) merged into existing Contact #25 with `legacy_ticket_id=7`.
- `Contact` now exposes shared `categories()` / `priorities()` helpers and `categoryLabel()`. All new contacts get an explicit category/priority. The contact form, product seller form, and new dashboard inquiry modal use the shared lists.
- Admin `admin.contacts.index` gained search plus Category/Source filters and a Category column; the detail page shows category, priority and the legacy ticket reference. The sidebar **Questions / Inquiries** now carries the pending-inquiry count previously shown on Ticket.
- Removed the entire Support Ticket module: admin controller/models/views, user ticket list/detail views, admin/user ticket routes and the sidebar **Ticket** child. Legacy `user.support.*` ticket URLs still redirect to My Inquiries so bookmarks do not 404. The **Support** group remains only when `conversation_system` is enabled, with **Product Conversations** as its sole item.
- Customer self-service ticket categories survive in **My Inquiries**: added a **New Inquiry** modal (category/priority/subject/message), a product-conversations table, and a `POST user.inquiries.store` route. Removed the duplicate **My Support** sidebar link; the My Inquiries link stays active on conversation detail pages too.
- `ProductController` no longer creates a guest Support Ticket; logged-in product questions still create/update a `ProductConversation` when the feature flag is on. `ContactController` no longer creates tickets from the general contact form.
- Verification: `php -l` on all changed/new PHP files; migration ran clean (contacts 22 → 23, ticket tables dropped, dedupe verified); `view:clear` + `view:cache` + `route:clear` + `route:cache` clean; `route:list` shows no admin ticket routes and only conversation/support aliases remain; compiled views contain zero `SupportTicket`/`support.tickets`/`My Support` references; rendered admin inquiry list/detail, rendered user inbox (with and without conversations), and smoke-tested `storeInquiry()` (created then removed a test inquiry) and legacy redirects.
- Follow-up (same day): user reported the edit control between the **Pending** badge and Delete looked inconsistent. It now uses the shared `.action-buttons` wrapper and `action-btn btn-edit` circle (same 36px size, orange edit styling and tooltip as other admin/user actions); the delete control was aligned to the same `.action-buttons` group and given explicit `type="submit"`/`aria-label`. Re-cached views and re-rendered the dashboard to confirm.
- Files: `database/migrations/2026_11_24_000004_migrate_support_tickets_to_inquiries.php`, `app/Models/Contact.php`, `app/Http/Controllers/ContactController.php`, `app/Http/Controllers/ProductController.php`, `app/Http/Controllers/Admin/ContactController.php`, `app/Http/Controllers/DashboardController.php`, `app/Http/Controllers/UserSupportController.php`, `app/Providers/AppServiceProvider.php`, `routes/web.php`, `resources/views/pages/contact.blade.php`, `resources/views/admin/contacts/index.blade.php`, `resources/views/admin/contacts/show.blade.php`, `resources/views/admin/partials/sidebar.blade.php`, `resources/views/user/inquiries.blade.php`, `resources/views/user/layouts/sidebar.blade.php`, `resources/views/user/support/conversation.blade.php`, `TASKS.md`.


## 472. (2026-11-24): Product Conversations merged into Inquiries as well

**Status: Implemented**

- User pointed out that admin **Questions / Inquiries → Questions** and **Support → Product Conversations** were still two separate modules for the same logged-in product question. Chose to merge conversations into the single Inquiries inbox too.
- New migration `2026_11_24_000005_migrate_product_conversations_to_inquiries.php` adds `legacy_conversation_id` to `contacts`, migrates each conversation and its full message thread, mirrors the last staff reply into the normal `reply`/`replied_by`/`replied_at` fields, and drops `product_conversation_messages` + `product_conversations`. Exact duplicates are merged: conversation #4 was folded into existing Contact #24 (no new row) with the admin reply `hi` preserved.
- `ProductController` no longer calls/creates a `ProductConversation`; a product seller question now always produces exactly one Contact inquiry. The `conversation_system` feature toggle was kept (per the “never remove toggles” rule) but relabelled **Conversation Activation (Legacy)** with a description that conversation messages now live in Questions / Inquiries.
- Removed the admin Product Conversation controller/models/views/routes and the **Support** sidebar group. The two legacy user conversation URLs now redirect to My Inquiries with an explanatory info message. My Inquiries no longer has a separate conversations table; migrated rows show a **Conversation #…** badge alongside the existing legacy ticket badge.
- Verification: `php -l` on all changed/new PHP files; migration ran clean (conversation #4 deduped into Contact #24, both conversation tables dropped); `view:clear` + `view:cache` + `route:clear` + `route:cache` clean; `route:list` shows no admin conversation routes and only the two legacy user redirect routes; rendered admin Contact #24 (legacy conversation badge + merged thread) and user inbox (conversation section gone, badge present); legacy conversation redirect lands on `/user/inquiries`.
- Files: `database/migrations/2026_11_24_000005_migrate_product_conversations_to_inquiries.php`, `app/Models/Contact.php`, `app/Http/Controllers/ProductController.php`, `app/Http/Controllers/DashboardController.php`, `app/Http/Controllers/UserSupportController.php`, `app/Support/FeatureFlags.php`, `app/Providers/AppServiceProvider.php`, `routes/web.php`, `resources/views/admin/contacts/show.blade.php`, `resources/views/admin/partials/sidebar.blade.php`, `resources/views/user/inquiries.blade.php`, `TASKS.md`.


## 473. (2026-11-24): User Management counters/list count all roles

**Status: Implemented**

- User reported `Total Users 1 | Admin 0 | Staff 1 | Customer 0` even though customers and a master admin exist.
- Root cause 1: `UserManagementController@index` filtered the list with `whereIn('role', ['staff', 'admin'])` — the real admin role is `master_admin`, and customers were excluded entirely.
- Root cause 2: the Blade cards derived both totals and role counts from the **current paginator page** (`$users->count()` / `$users->where(...)`) instead of the whole users table.
- Fix:
  - List query is now `User::query()->...->paginate($perPage)` so master admin, staff and customers all appear (soft-deleted users remain excluded by the model's `SoftDeletes` scope).
  - One grouped aggregate query in the controller computes `total`, `admin` (`master_admin` + legacy `admin`), `staff`, and `customer` (`customer` + legacy `user`) across all non-deleted users. Values are cast to int and passed to the view.
  - Removed the local `@php` counter block from `resources/views/admin/users/index.blade.php`; cards now use the controller-provided values.
- Verification: DB aggregate checked with tinker (local DB: total 14 active, admin 1, staff 1, customer 12; 1 soft-deleted customer excluded); `view:clear` + `view:cache` rebuilt; controller on disk now passes the four counters and the compiled view consumes them.
- Follow-up: an initial `500` (`Undefined variable $totalCount`) occurred because the controller edit was not present on disk while the view edit was. The controller fix was re-applied and confirmed on disk; a hard refresh clears any cached error page.
- Files: `app/Http/Controllers/UserManagementController.php`, `resources/views/admin/users/index.blade.php`, `TASKS.md`.


## 474. (2026-11-24): Purchase-only review policy with clearer UX

**Status: Implemented**

- User chose **purchase-only + clearer UX** over verified-email reviews, so review authenticity stays tied to real orders.
- `ProductController` now resolves purchased orders and already-reviewed order IDs through `ProductReviewEligibility` and passes `canReview` to `resources/views/product/show.blade.php`. The review form is shown only when the user has an unreviewed purchase.
- Product page now explains the rule instead of showing a form that fails with 403: guests get a login link, customers who purchased get “already reviewed / edit in My Reviews”, and logged-in non-purchasers get a hint to check My Orders (guest checkout uses the checkout email).
- Server-side gate remains purchase-only with actionable messages: sign in with the order account / check My Orders, purchase not eligible, already reviewed this purchase, or edit an existing review in My Reviews.
- Dashboard **My Reviews** now states that reviews require a purchase and explains how guest-checkout purchases appear after login with the checkout email.
- Fixed the product-page review delete handler: it no longer references undefined `alertEl`/`submitReviewBtn`, no longer reads `data.rating` from the wrong response variable, and now updates the rating/tab count from `result.data`.
- Verification: `php -l` clean on both controllers; `view:clear` + `view:cache` rebuilt.
- Files: `app/Http/Controllers/ProductController.php`, `app/Http/Controllers/ReviewController.php`, `resources/views/product/show.blade.php`, `resources/views/user/reviews.blade.php`, `TASKS.md`.


## 475. (2026-11-24): Organize Blade views into backend/frontend

**Status: Implemented**

- User asked to move every Blade under `resources/views` into either `backend/` or `frontend/`, keeping shared views at the root.
- Moved 129 admin templates from `resources/views/admin/*` to `resources/views/backend/*` (view namespace `admin.*` → `backend.*`).
- Moved 59 public/user/auth templates to `resources/views/frontend/*`: `auth`, `blog`, `brands`, `cart`, `categories`, `checkout`, `compare`, `home`, `pages`, `product`, `shop`, `user`, plus root `maintenance.blade.php` (namespace `frontend.*`).
- Kept `resources/views/layouts`, `partials`, `mail`, `errors`, and `vendor` at the root so both backend and frontend share them without duplicate copies.
- Updated 450 view references across 203 source files: controller `view()`/`View::make()`/`response()->view()` calls and Blade `@extends`/`@include`/`@includeWhen`/`@each`/`@component`/`@livewire` directives. Route names such as `admin.users.index` were deliberately left untouched.
- Fixed a latent broken reference discovered during verification: `backend/login.blade.php` now posts to `route('login.submit')` instead of the nonexistent `admin.login.submit`.
- Follow-up 500 fix: the bulk reference rewrite also touched route names nested inside Blade directive arguments (for example `route('home')` → `route('frontend.home')` and `route('admin.categories.index')` → `route('backend.categories.index')`), causing repeated `GET categories` 500s (`Route [frontend.home] not defined`). 36 route calls in 32 files were restored to their real names and views were re-cached.
- Verification: no old view prefixes remain in source; `view:clear` + `view:cache` clean; rendered home, shop, product, auth login, user dashboard, admin login, admin dashboard and admin users pages successfully.
- Historical TASKS entries still mention the old paths; actual source now lives under `backend/`/`frontend/` as described above.
- Files: all PHP/Blade sources under `app/`, `routes/`, `resources/views/`, `config/`, `database/`, plus `TASKS.md`.


## 476. (2026-11-24): Fix duplicate product cards (Related Products)

**Status: Implemented**

- User reported repeated cards in the product detail page **Related Products** section.
- Root cause: product-card includes passed the data as `['frontend.product' => $rel]`, but `resources/views/partials/product-card.blade.php` reads `$product`. Because Blade includes inherit parent variables, every card silently rendered the outer/current product instead of `$rel`, so the section showed the same product repeatedly. The same typo affected Recently Viewed, Home, Shop, Wishlist and dashboard cards.
- Changed all 9 occurrences to `['product' => ...]` so the partial receives the intended model.
- Verification: rendered a product page and parsed the Related Products section — 4 cards, 4 distinct product URLs, current product not repeated. `view:cache` rebuilt.
- Files: `resources/views/frontend/product/show.blade.php`, `resources/views/frontend/shop/index.blade.php`, `resources/views/frontend/home/index.blade.php`, `resources/views/frontend/user/wishlist.blade.php`, `resources/views/frontend/user/dashboard.blade.php`, `resources/views/partials/recently-viewed.blade.php`.


## 477. (2026-11-24): Recently Viewed should not show the current product

**Status: Implemented**

- User confirmed that opening a product for the first time should not immediately show that same product in **Recently Viewed** on the same page.
- `ProductController::show()` now computes `$recentlyViewedIds` from the session **before** adding the current product, passes those IDs to the view, and only then stores the current product at the front of the session list (max 4).
- `partials/recently-viewed.blade.php` prefers the passed `$recentlyViewedIds` and falls back to the session for any other caller.
- Verification: first open with empty session → session `[current]`, current not rendered in the section; second product open → session `[second, first]`, section shows only `first`, current `second` not repeated. `view:cache` rebuilt.
- Files: `app/Http/Controllers/ProductController.php`, `resources/views/partials/recently-viewed.blade.php`, `TASKS.md`.


## 478. (2026-11-25): Wallet system removed

**Status: Implemented**

- User confirmed the wallet is not needed and asked to remove it.
- Deleted `resources/views/frontend/user/wallet.blade.php`, removed `DashboardController@wallet()`, and removed the **My Wallet** link from the user dashboard sidebar.
- `GET /user/wallet` (route name `user.wallet` kept for legacy links) now redirects to the user dashboard with the info message “The wallet feature has been removed.”
- The `wallet_system` feature flag is retained (per the rule that feature toggles are never deleted) but relabelled **Wallet System Activation (Legacy)** and documented as removed; turning it on has no effect.
- `users.balance` was removed from the `User` model fillable list. The physical DB column is intentionally left untouched for legacy data/schema compatibility.
- Verification: route cache and view cache rebuilt; `route:list` shows `user/wallet` now points to the redirect; no `My Wallet`, `frontend.user.wallet`, or `DashboardController@wallet` references remain; `php -l` clean on all changed PHP files.
- Files: `app/Support/FeatureFlags.php`, `app/Http/Controllers/DashboardController.php`, `app/Models/User.php`, `routes/web.php`, `resources/views/frontend/user/layouts/sidebar.blade.php`, `TASKS.md`.


## 479. (2026-11-25): Fresh standalone last-fix fix report

**Status: Pending**

- User asked for a new **last-fix fix report** to be created as a fresh, standalone document.
- Exactly **one Markdown file** only. Do **not** create an Excel workbook and do **not** update or regenerate any scan/bug-management/fix-report file that already exists.
- Pick a **new distinct filename** at creation time. Do not reuse any existing report filename and do not treat the sample strings in the request as the filename.
- The report must have its own identity: it is separate from the bug scan report, separate from any bug management report, separate from older fix reports, and separate from this task log.
- The report body must not reference any earlier report, older fix report, task log, task number/ID, or historical notes. It should read as a fresh report written from current project evidence only.
- Before writing, inspect the project again: relevant source files, current DB values, HTTP smoke checks, dependency audit, and the current test baseline.
- Suggested evidence to include: patched dependency versions with clean `composer audit`; legacy `/new-steve/` root 500 fix with canonical/legacy HTTP statuses; User Management counter verification across all roles; restoration of `settings` and shipping tables with feature flags reseeded; current test-suite result and its known environment limitation.
- Keep all changes uncommitted unless explicitly asked.


## 480. (2026-11-25): Staff/admin test orders no longer mix with customer reports (QA A24)

**Status: Implemented**

- Reported issue: most orders were placed while signed in as staff (`jasa.ocius@gmail.com`, user #9), while the real customer account `Jasa Ram` (`jasaram.ocius@gmail.com`, user #30) showed 0 orders. Internal test orders polluted dashboard totals and the order CSV export.
- Live data check: 55 orders total — 29 by staff #9, 2 by master admin #5, 23 by customers, 1 guest. Staff orders carry the staff billing email, so they are genuinely internal test activity, not misattributed customer purchases.
- Chosen fix: the safe half of the QA recommendation — exclude staff/admin orders from reports by default. No order data was deleted and no orders were reassigned.
- `app/Models/Order.php`: added reusable `scopeCustomerPlaced()` using `whereDoesntHave('user', ...)` for roles `master_admin`, `admin`, `staff` (soft-deleted users included via `withTrashed()`), so customer and guest orders stay in reports.
- `app/Http/Controllers/AdminController.php` and `app/Http/Controllers/DashboardController.php`: applied the scope to dashboard totals, orders-by-status, recent orders, and all monthly/weekly/daily/hourly/5-minute revenue queries (including `todayRevenue()`). The staff dashboard top-products query now uses the same scoped Eloquent builder instead of a raw `order_items` aggregate.
- `app/Http/Controllers/Admin/OrderController.php`: the admin order list and CSV export now exclude internal orders by default, with `?include_internal=1` to opt back in for cleanup. `resources/views/backend/orders/index.blade.php` gained an “Include staff/admin orders” toggle; the export link inherits all active filters, so list and CSV stay in sync.
- Verification: `php -l` clean on all changed PHP; scope totals `all=55`, `customerPlaced=24`, `staffPresent=0`; dashboard totals went `55 → 24` orders and revenue `1119.89 → 290.00`; default list/export rows `24`, opt-in `55`; rendered orders page shows the toggle and `24 Total`; `view:clear`/`view:cache` and `route:clear`/`route:cache` clean.
- Operational follow-up (not performed): delete the 31 internal test orders before launch if they are no longer needed, and use dedicated test customer accounts for future checkout tests.
- Backup before edits: `/tmp/opencode/a24-staff-orders-backup/`.
- Files: `app/Models/Order.php`, `app/Http/Controllers/AdminController.php`, `app/Http/Controllers/DashboardController.php`, `app/Http/Controllers/Admin/OrderController.php`, `resources/views/backend/orders/index.blade.php`, `TASKS.md`.


## 481. (2026-11-25): Registration phone number made optional

**Status: Implemented**

- User asked whether the phone field is required when creating a new user; confirmed it is not needed for account creation — verification is email-based, 2FA uses authenticator/email (no SMS), and checkout already validates the delivery phone (7–20 chars on the checkout form). Admin-created customers already allow a blank phone.
- `app/Http/Controllers/AuthController.php`: `register()` phone rule changed from `required` to `nullable`; removed the now-unused `phone.required` custom message. `UniquePhone` still applies when a phone is supplied.
- `resources/views/frontend/auth/register.blade.php`: removed the `*` and HTML `required` attribute; label now reads `Phone Number (Optional)`.
- Verification: `php -l` clean; validator checks show blank phone passes, supplied phone still format-checked; rendered register page shows “Optional” and no required phone input; view cache rebuilt.
- Backup before edits: `/tmp/opencode/register-phone-optional/`.
- Files: `app/Http/Controllers/AuthController.php`, `resources/views/frontend/auth/register.blade.php`, `TASKS.md`.


## 482. (2026-11-25): Gmail API added as a mail driver option

**Status: Implemented**

- User asked to keep Gmail API as an additional option alongside the existing mail drivers (not replace SMTP). Followed the existing `MailgunApiTransport` / `MicrosoftGraphTransport` pattern.
- New `app/Mail/Transport/GmailApiTransport.php`: exchanges an OAuth refresh token at `oauth2.googleapis.com/token` (access token cached in Laravel cache, `expires_in - 60` seconds, minimum 60), then POSTs the base64url-encoded RFC2822 message to `gmail/v1/users/{userId}/messages/send`. Errors are raised as `RuntimeException` with `Gmail API authentication failed with HTTP ###.` / `Gmail API send failed with HTTP ###.` so the settings test page can explain them.
- `app/Providers/AppServiceProvider.php`: registered `Mail::extend('gmail_api', ...)`, added `gmail` to the accepted stored drivers, applied stored Gmail settings into `services.gmail_mail`, and auto-selected `gmail` when no driver is stored and Gmail credentials are present in `.env` (Microsoft still takes precedence).
- `config/mail.php` gained the `gmail` mailer; `config/services.php` and `.env.example` gained `GOOGLE_MAIL_CLIENT_ID`, `GOOGLE_MAIL_CLIENT_SECRET`, `GOOGLE_MAIL_REFRESH_TOKEN`, `GOOGLE_MAIL_USER_ID`.
- `SmtpSettingsController`: added `gmail` to `DRIVERS`, new form values (`mail_google_client_id`, `mail_google_user_id`, encrypted `mail_google_client_secret` / `mail_google_refresh_token`), validation + required checks for Gmail, and two test-email error explanations. Secrets are encrypted with `Crypt::encryptString()` and never rendered back.
- `resources/views/backend/settings/smtp.blade.php`: new **Gmail API** option and field group (Client ID, Client Secret, Refresh Token, Sending Account default `me`) plus setup notes (enable Gmail API, OAuth consent screen, `gmail.send` scope, From Address must be the authorized mailbox/verified alias, personal Gmail daily limits). Test card renamed to “Test mail configuration”.
- `tests/Feature/SmtpSettingsMailTest.php`: added Gmail authentication and send-failure message tests.
- Verification: `php -l` clean on all changed PHP; `php artisan test --filter=SmtpSettingsMailTest` → 4 passed / 12 assertions; `Http::fake()` send test made exactly two calls (`oauth2.googleapis.com/token`, `gmail.googleapis.com/.../messages/send`) with a non-empty `raw` payload, and two sends reused one cached token; settings page rendered with the Gmail option/fields and no undefined variables; `config:clear`, `view:clear`/`view:cache`, `route:clear`/`route:cache` clean.
- Existing stored driver (`microsoft`) and all credentials were left unchanged; no live emails were sent.
- Files: `app/Mail/Transport/GmailApiTransport.php`, `app/Providers/AppServiceProvider.php`, `config/mail.php`, `config/services.php`, `.env.example`, `app/Http/Controllers/Admin/SmtpSettingsController.php`, `resources/views/backend/settings/smtp.blade.php`, `tests/Feature/SmtpSettingsMailTest.php`, `TASKS.md`.


## 483. (2026-11-25): Generic SMTP test-email error traced to Gmail App Password

**Status: Implemented (diagnosis + clearer errors)**

- User reported the test email failing with only “Test email could not be sent. Check the selected mail driver and configuration.”
- Diagnosis: stored `mail_driver` is **smtp** (not Gmail API), host `smtp.gmail.com:587`, username `jasaram.ocius@gmail.com`, encryption `tls`. TCP port 587 is reachable. A read-only SMTP AUTH probe returned `535 5.7.8 Username and Password not accepted` — the stored password is 8 characters, i.e. the normal Google password, not a 16-character Gmail App Password. No email was sent during diagnosis and no password was printed or changed.
- `SmtpSettingsController::test()` now recognises `TransportException` messages for SMTP auth rejection (`535`, “Authentication failed”, “Username and Password not accepted”, “Bad credentials”) and connection failures (`timed out`, “could not connect”, “connection refused”, “network is unreachable”), returning actionable guidance. The failure log now also includes the exception message (no credentials) for future diagnosis.
- `resources/views/backend/settings/smtp.blade.php`: added the Gmail SMTP App Password note under the SMTP configuration notes.
- `tests/Feature/SmtpSettingsMailTest.php`: added SMTP authentication and connection error tests. `php artisan test --filter=SmtpSettingsMailTest` → 6 passed / 18 assertions; view cache rebuilt.
- User action still required: enable 2-Step Verification on the Google account, generate a 16-character App Password at myaccount.google.com → Security → App passwords, and paste it into SMTP **MAIL PASSWORD**. Alternatively select the new **Gmail API** driver and enter OAuth Client ID / Secret / Refresh Token.
- Backup before edits: `/tmp/opencode/smtp-error-backup/`.
- Files: `app/Http/Controllers/Admin/SmtpSettingsController.php`, `resources/views/backend/settings/smtp.blade.php`, `tests/Feature/SmtpSettingsMailTest.php`, `TASKS.md`.


## 484. (2026-11-25): Separate real SKU field from Part Number

**Status: Implemented + live backfill completed**

- User clarified that **SKU and Part Number are different fields**, and products currently have no SKU. Previously `sku` was only an alias to `part_number` via `Product` accessors/mutators, and the `products` table had no `sku` column (the older `2026_09_22` migration was marked Ran but the column was absent).
- New migration `2026_11_25_000001_add_sku_to_products_table.php` adds a nullable, unique `sku` VARCHAR(100) (idempotent guard). It was appended without `after()` so MySQL can use instant ADD COLUMN on the 761k-row catalogue. Ran successfully on live DB (3s). The unique index allows multiple NULLs, so the backfill could run after migration.
- `Product`: removed the `sku` → `part_number` alias accessors/mutators, added `sku` to `$fillable`, and split generation into `generatePartNumber()` / `generateSku()` with a shared uniqueness check that now uses the correct column and `withTrashed()` (so soft-deleted rows cannot collide).
- `ProductController`: `resolveImportedSku()` no longer falls back to `part_number`; added `resolveImportedPartNumber()` and `resolveImportedIdentifiers()` so CSV `part_number` and `sku` stay separate, provided values win, existing values are preserved on blank cells, and either field is generated independently when missing. Store/update forms now accept an editable SKU (unique validation), auto-generating when blank; Part Number remains auto-generated/readonly. Admin search now includes `sku`; sorting `sku` now uses the real column instead of `part_number`. Sample CSV includes a `sku` column.
- `ImportProductsJob` now writes both `part_number` and `sku`; `ExportProductsJob` exports both columns. Admin create/edit/list/show views and import documentation show/edit SKU separately from Part Number.
- `products:backfill-skus` rewritten for scale: chunked raw SQL, deterministic unique `SKU-<id>` values (no 761k per-row SELECT queries), `--dry-run`, and `--chunk`. Dry run previewed 761,107 products; live run updated all 761,107.
- Verification on live DB: total `761107`, missing SKU `0`, distinct SKU `761107`, rows where `sku = part_number` `0`; sample id 1 → part `001-10-23148`, sku `SKU-1`. `php -l` clean on all changed PHP; `php artisan test --filter=ProductImportSkuTest` → 5 passed / 9 assertions; view cache rebuilt. `generateSku()`/`generatePartNumber()` smoke-tested without saving.
- Backup before edits: `/tmp/opencode/sku-part-number-split/`.
- Files: `database/migrations/2026_11_25_000001_add_sku_to_products_table.php`, `app/Models/Product.php`, `app/Http/Controllers/ProductController.php`, `app/Jobs/ImportProductsJob.php`, `app/Jobs/ExportProductsJob.php`, `app/Console/Commands/BackfillProductSkus.php`, `tests/Feature/ProductImportSkuTest.php`, `resources/views/backend/products/{create,edit,index,show,import}.blade.php`, `TASKS.md`.


## 485. (2026-11-25): Gmail API setup removed (user request)

**Status: Implemented**

- User asked to remove the Gmail API setup added in #482. This overrides the earlier “no code changes” instruction for this specific request.
- Removed end-to-end: `app/Mail/Transport/GmailApiTransport.php`, `Mail::extend('gmail_api', ...)`, the `gmail` mailer in `config/mail.php`, the `services.gmail_mail` block in `config/services.php`, the `GOOGLE_MAIL_*` keys in `.env.example`, the `gmail` driver/form fields/notes in `resources/views/backend/settings/smtp.blade.php`, and all Gmail-specific view variables, validation rules, required-credential checks and test-error branches in `SmtpSettingsController`.
- Stored `mail_google_client_id` / `mail_google_client_secret` / `mail_google_refresh_token` were empty and `mail_google_user_id` was `me`; the two remaining DB rows were deleted with a raw `DB::table('settings')->whereIn(...)->delete()` (0 remaining). Stored driver was already changed to `microsoft` at 09:20 by the user — not changed here. The Gmail SMTP App Password requirement note was kept.
- While in `SmtpSettingsController::test()`, extended the SMTP auth pattern to also match Google’s current `534 5.7.9 Application-specific password required` response (previously only `535`/“Username and Password not accepted” matched), so the real failure now shows the App Password guidance instead of the generic fallback.
- `tests/Feature/SmtpSettingsMailTest.php`: removed the two Gmail API message tests and added a `534` App Password test. `php artisan test --filter=SmtpSettingsMailTest` → 5 passed / 15 assertions; config/view caches rebuilt.
- Backup before edits: `/tmp/opencode/gmail-api-removal/`.
- Files: `app/Providers/AppServiceProvider.php`, `config/mail.php`, `config/services.php`, `.env.example`, `app/Http/Controllers/Admin/SmtpSettingsController.php`, `resources/views/backend/settings/smtp.blade.php`, `tests/Feature/SmtpSettingsMailTest.php`, `app/Mail/Transport/GmailApiTransport.php` (deleted), `TASKS.md`.


## 486. (2026-11-25): Coupon cleanup — delete test coupon, add min order + expiry

**Status: Implemented**

- User flagged the test coupon `EREWREWR` (fixed $25, inactive, 2 uses) and real coupon `ORDER10` (fixed $10, active, 3 uses) having no min order or expiry. User confirmed values: **min order `$100.00`** and **expiry `31 Dec 2026`**.
- Soft-deleted `EREWREWR` (id 1) with `Coupon::find(1)->delete()`; `TracksIsDeleted` set `is_deleted=1` and `deleted_at`. Hard delete avoided to stay consistent with the other soft-deleted test coupons.
- Updated `ORDER10` (id 11): `min_order_amount=100.00`, `expires_at=2026-12-31`. Kept `status=1`, `max_uses=10`, `used_count=3` unchanged.
- Verified: default `Coupon::query()` returns only `ORDER10`; `ORDER10->isValid()` is `true`; `EREWREWR` is absent from the default query but visible with `withTrashed()` as `is_deleted=1`.
- Backup before edits: `/tmp/opencode/coupon-cleanup/coupons-before.json`.
- Data-only change, no code modified. File: `TASKS.md`.


## 487. (2026-11-25): Coupon admin — Edit and Delete buttons in Action column

**Status: Implemented**

- User requested Edit/Delete buttons in the Action column of the **All Coupons** admin list.
- `resources/views/backend/coupons/index.blade.php`: kept the existing View button and added an Edit link (`admin.coupons.edit`, shared `action-btn btn-edit` pencil SVG) plus a Delete form (`admin.coupons.destroy`, shared `action-btn btn-cancel` trash SVG) with a JS confirmation. Markup matches the Categories/Blogs/Products admin tables.
- Routes `GET /coupons/{id}/edit` and `DELETE /coupons/{id}` and both controller methods already existed, so no route/controller changes were needed. Delete still soft-deletes via `Coupon::delete()`.
- Verified: `php artisan view:cache` rebuilt successfully; `php artisan route:list --name=admin.coupons` shows all 7 coupon routes including `edit` and `destroy`.
- Backup before edit: `/tmp/opencode/coupons-action-buttons/`.
- Files: `resources/views/backend/coupons/index.blade.php`, `TASKS.md`.


## 488. (2026-11-25): Coupon detail stat cards — hover style

**Status: Implemented**

- User requested a hover style for the **Type**, **Orders Used**, **Total Discount Given** and **Uses Limit** stat cards on the coupon detail page.
- `resources/views/backend/coupons/show.blade.php`: added a `coupon-stat-card` class to exactly those four cards and a scoped `<style>` block with a subtle lift (`translateY(-2px)`), stronger shadow and a thin green ring on hover, using `!important` so it overrides Bootstrap's `.shadow-sm`. Added a `prefers-reduced-motion` fallback that disables the transition/transform.
- Verified: `php artisan view:cache` rebuilt successfully; all four cards carry the new class and no other card on the page does.
- Backup before edit: `/tmp/opencode/coupons-hover-style/`.
- Files: `resources/views/backend/coupons/show.blade.php`, `TASKS.md`.


## 489. (2026-11-26): Duplicate category slug disambiguation (JS + session, clean URL) and scoped shop sidebar

**Status: Implemented**

- Problem: same category slug can exist under different parents (e.g. `service-kits` under `Engine` id `1486` and `Air and Fuel Delivery` id `75`). Clean `/category/{slug}` URLs cannot reliably disambiguate server-side, causing wrong category resolution on duplicate slugs.
- Chosen approach: **Option 1 - JS + session, clean URL** (autoparts-style). Normal JS clicks resolve correctly; direct URL/bookmark/share/new tab/crawler/JS-disabled ambiguous requests may fall back to first match (accepted limitation).
- `routes/web.php`: added `POST /ajax/category-context` (`ajax.category-context`) handled by `ShopController::setCategoryContext()`.
- `app/Http/Controllers/ShopController.php`: `setCategoryContext()` validates `category_id + slug` and stores `session('category_context.{slug}')`; `category()` resolves ambiguous slug matches via session context (`pull()` to clear after use), otherwise falls back to first match. Keeps scoped sidebar data (`currentCategory` branch, `activeCategoryIds`).
- Frontend category links keep clean `href="/category/{slug}"` and carry `data-category-id`, `data-category-slug`, `js-category-context-link` for JS context setting: `resources/views/partials/shop-sidebar-category-items.blade.php`, `resources/views/partials/mobile-category-tree-items.blade.php`, `resources/views/frontend/shop/index.blade.php` breadcrumbs, `resources/views/partials/breadcrumb.blade.php` (data attr support), `resources/views/frontend/product/show.blade.php`, `resources/views/frontend/home/index.blade.php`, `resources/views/frontend/categories/index.blade.php`.
- `public/assets/front/js/myscript.js`: delegated click handler intercepts normal left-clicks on `.js-category-context-link`, POSTs category context to `/ajax/category-context` with CSRF/base-url handling, then navigates to clean URL; on failure/timeout still navigates.
- Scoped shop category sidebar: on category pages show `All Categories` + current opened top-level category branch/children (SteveAutoParts-style); full category tree on non-category shop pages.
- Verification: `php -l` passed on changed PHP files; `php artisan view:clear` run; tinker smoke test confirmed `category_context.service-kits=1486` resolves `/category/service-kits` to **Engine > Service Kits** (`9,1486`) and context is cleared; without context falls back to first match.
- Files: `app/Http/Controllers/ShopController.php`, `routes/web.php`, `resources/views/frontend/shop/index.blade.php`, `resources/views/partials/shop-sidebar-category-items.blade.php`, `resources/views/partials/mobile-category-tree-items.blade.php`, `resources/views/partials/breadcrumb.blade.php`, `resources/views/frontend/product/show.blade.php`, `resources/views/frontend/home/index.blade.php`, `resources/views/frontend/categories/index.blade.php`, `public/assets/front/js/myscript.js`, `TASKS.md`.


## 490. (2026-11-26): Admin All Products search — include slug

**Status: Implemented**

- User requested that the admin All Products search field should search beyond product name, including Part Number and slug.
- `app/Http/Controllers/ProductController.php`: added `->orWhere('slug', 'like', "%{$search}%")` to product listing search and stock listing search (2 places). Search now covers `name`, `part_number`, `sku`, `slug`.
- Verified: `php -l app/Http/Controllers/ProductController.php` passes.
- Files: `app/Http/Controllers/ProductController.php`, `TASKS.md`.


## 491. (2026-11-26): Shop sidebar — active child only

**Status: Implemented**

- User requested shop/category sidebar behavior like reference autoparts flow: when a child category is open, show its parent and only the active child branch; hide sibling child categories. Clarified that the selected child category's own children should still show for deeper drill-down.
- `resources/views/partials/shop-sidebar-category-items.blade.php`: added active-path filtering when `activeCategoryIds` is present. Keeps nodes in active path, plus children of the selected category; skips filtering for visible non-active expanded nodes so users can browse deeper before navigating.
- `app/Services/CategoryTree.php`: added `parent_id` to cached tree nodes and bumped cache key to `front_category_tree_v3` for safe structure change.
- Verified: `php -l app/Services/CategoryTree.php` passes; `php artisan view:clear` run.
- Files: `resources/views/partials/shop-sidebar-category-items.blade.php`, `app/Services/CategoryTree.php`, `TASKS.md`.
