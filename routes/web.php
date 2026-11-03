<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\BlogController as AdminBlogController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CurrencySettingsController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\HomePageController;
use App\Http\Controllers\Admin\ImageController;
use App\Http\Controllers\Admin\LanguageController as AdminLanguageController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PaymentMethodSettingsController;
use App\Http\Controllers\Admin\PickupPointController;
use App\Http\Controllers\Admin\ReturnController as AdminReturnController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\SellerController;
use App\Http\Controllers\Admin\SmtpSettingsController;
use App\Http\Controllers\Admin\SubscriberController;
use App\Http\Controllers\Admin\ApiIntegrationController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CompareController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\WishlistController;
use App\Models\Language;
use App\Models\Currency;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| PUBLIC
|--------------------------------------------------------------------------
*/

Route::get('/currency', [CurrencyController::class, 'rates']);
Route::get('/address/states', [LocationController::class, 'states'])->name('location.states');
Route::get('/address/cities', [LocationController::class, 'cities'])->name('location.cities');
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/sitemap.xml', [HomeController::class, 'sitemapIndex'])->name('sitemap');
$sitemapTypes = [
    'page' => 'pages',
    'product' => 'products',
    'blogs' => 'blogs',
    'brands' => 'brands',
    'categories' => 'categories',
    'blog-categories' => 'blog-categories',
    'blog-tags' => 'blog-tags',
];
foreach ($sitemapTypes as $sitemapSlug => $sitemapType) {
    Route::get('/'.$sitemapSlug.'-sitemap.xml', function () use ($sitemapType) {
        return app(HomeController::class)->sitemapSection($sitemapType);
    });
    Route::get('/'.$sitemapSlug.'-sitemap{page}.xml', function (int $page) use ($sitemapType) {
        return app(HomeController::class)->sitemapSection($sitemapType, $page);
    })->whereNumber('page');
}
Route::get('/robots.txt', [HomeController::class, 'robots'])->name('robots');

Route::get('/search', [ShopController::class, 'index'])->name('shop');
Route::post('/search/clear-vehicle', [ShopController::class, 'clearVehicleFilter'])->middleware('auth')->name('shop.clear-vehicle');
Route::post('/search/clear-filters', [ShopController::class, 'clearAllFilters'])->name('shop.clear-filters');
Route::get('/api/vehicles/years', [ShopController::class, 'vehicleYears'])->name('vehicles.years');
Route::get('/api/vehicles/makes', [ShopController::class, 'vehicleMakes'])->name('vehicles.makes');
Route::get('/api/vehicles/models', [ShopController::class, 'vehicleModels'])->name('vehicles.models');
Route::get('/api/search/suggestions', [ShopController::class, 'suggestions'])->name('search.suggestions');

Route::get('/product/{slug}', [ProductController::class, 'show'])->name('product');
Route::post('/contact-seller', [ProductController::class, 'contactSeller'])->name('contact.seller');

Route::match(['get', 'post'], '/product/{slug}/review', [ReviewController::class, 'store'])->middleware('auth')->name('product.review');
Route::put('/product/{slug}/review/{reviewId}', [ReviewController::class, 'update'])->middleware('auth')->name('product.review.update');
Route::delete('/product/{slug}/review/{reviewId}', [ReviewController::class, 'destroy'])->middleware('auth')->name('product.review.delete');

Route::get('/category/{slug}', [ShopController::class, 'category'])->name('category');

Route::get('/brand/{slug}', [ShopController::class, 'brand'])->name('brand');

Route::get('/blog', [BlogController::class, 'index'])->name('blog');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/blog/category/{slug}', [BlogController::class, 'category'])->name('blog.category');
Route::get('/blog/tag/{slug}', [BlogController::class, 'tag'])->name('blog.tag');

Route::get('/about-us', [HomeController::class, 'about'])->name('about');
Route::get('/contact-us', [HomeController::class, 'contact'])->name('contact');
Route::get('/page/{slug}', [HomeController::class, 'page'])->name('page.show');

/*
|--------------------------------------------------------------------------
| CART
|--------------------------------------------------------------------------
*/

Route::get('/cart', [CartController::class, 'index'])->name('cart');

Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');

Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/remove-selected', [CartController::class, 'removeSelected'])->name('cart.removeSelected');

Route::post('/cart/update/{id}', [CartController::class, 'updateQuantity'])->name('cart.update');
Route::get('/cart/mini', [CartController::class, 'miniCart'])->name('cart.mini');
Route::post('/cart/coupon/apply', [CartController::class, 'applyCoupon'])->name('cart.coupon.apply');
Route::post('/cart/coupon/remove', [CartController::class, 'removeCoupon'])->name('cart.coupon.remove');

Route::get('/checkout', [CartController::class, 'checkout'])->middleware(['nocache'])->name('checkout');

Route::post('/checkout', [CartController::class, 'checkoutSubmit'])->middleware(['nocache'])->name('checkout.submit');

Route::post('/checkout/guest', [CartController::class, 'checkoutSubmitGuest'])->middleware(['nocache'])->name('checkout.guest.submit');

Route::get('/checkout/delivery-info', [CartController::class, 'deliveryInfo'])->middleware(['nocache'])->name('checkout.delivery-info');

Route::post('/checkout/delivery-info', [CartController::class, 'deliveryInfoSubmit'])->middleware(['nocache'])->name('checkout.delivery-info.store');

Route::get('/checkout/payment', [CartController::class, 'payment'])->middleware(['nocache'])->name('checkout.payment');

Route::post('/checkout/payment', [CartController::class, 'paymentSubmit'])->middleware(['nocache'])->name('checkout.payment.submit');

Route::get('/checkout/order-confirmed', [CartController::class, 'orderConfirmed'])->middleware(['nocache'])->name('checkout.confirmed');

/* Address management for checkout */
Route::post('/checkout/address/store', [AddressController::class, 'store'])->middleware(['auth', 'nocache'])->name('checkout.address.store');
Route::get('/checkout/address/{address}/edit', [AddressController::class, 'edit'])->middleware(['auth', 'nocache'])->name('checkout.address.edit');
Route::put('/checkout/address/{address}', [AddressController::class, 'update'])->middleware(['auth', 'nocache'])->name('checkout.address.update');
Route::delete('/checkout/address/{address}', [AddressController::class, 'destroy'])->middleware(['auth', 'nocache'])->name('checkout.address.destroy');
Route::post('/checkout/address/{address}/default', [AddressController::class, 'setDefault'])->middleware(['auth', 'nocache'])->name('checkout.address.default');

/*
|--------------------------------------------------------------------------
| WISHLIST
|--------------------------------------------------------------------------
*/

Route::post('/wishlist/add', [WishlistController::class, 'add'])->name('wishlist.add');
Route::delete('/wishlist/clear', [WishlistController::class, 'clearAll'])->name('wishlist.clear');
Route::delete('/wishlist/{id}', [WishlistController::class, 'remove'])->name('wishlist.remove');

Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');

/*
|--------------------------------------------------------------------------
| COMPARE
|--------------------------------------------------------------------------
*/

Route::get('/compare/add', [CompareController::class, 'add'])->name('compare.add');

Route::get('/compare', [CompareController::class, 'index'])->name('compare.index');
Route::delete('/compare/clear', [CompareController::class, 'clear'])
    ->name('compare.clear');
Route::delete('/compare/{id}', [CompareController::class, 'remove'])->name('compare.remove');

/*
|--------------------------------------------------------------------------
| USER AUTH
|--------------------------------------------------------------------------
*/

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.submit');
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/registration', [AuthController::class, 'registerForm'])->name('register');
Route::post('/registration', [AuthController::class, 'register'])->name('register.post');

Route::get('/forgot-password', [AuthController::class, 'showForgot'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->middleware('throttle:5,1')->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showReset'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1')->name('password.update');

/*
|--------------------------------------------------------------------------
| USER DASHBOARD
|--------------------------------------------------------------------------
*/

// User routes are consolidated below under a single authenticated user group.

/*
|--------------------------------------------------------------------------
| ADMIN AUTH
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->group(function () {

    Route::get('/login', function () {
        return redirect()->route('login');
    })->name('admin.login');
    Route::match(['get', 'post'], '/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

});

Route::get('/use/login', function () {
    return redirect('/login');
});
/*
|--------------------------------------------------------------------------
| ADMIN ROOT
|--------------------------------------------------------------------------
*/

Route::get('/admin', function () {
    return Auth::check()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| ADMIN PANEL — ALL ADMIN ROLES (master_admin / admin / staff)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->middleware(['auth', 'nocache', 'role:master_admin,admin,staff'])
    ->group(function () {

        Route::get('/dashboard', [AdminController::class, 'index'])
            ->name('admin.dashboard');

        Route::get('/dashboard/today-revenue', [AdminController::class, 'todayRevenue'])
            ->name('admin.dashboard.today-revenue');

        Route::get('/profile', [AdminController::class, 'profile'])
            ->name('admin.profile');
        Route::post('/profile', [AdminController::class, 'updateProfile'])
            ->name('admin.profile.update');
        Route::post('/profile/password', [AdminController::class, 'updatePassword'])
            ->name('admin.profile.password');

        Route::get('/customers', [UserController::class, 'index'])
            ->name('admin.customers.index');

        Route::get('/staff', [UserController::class, 'staff'])
            ->name('admin.staff.index');

        Route::get('/products', [ProductController::class, 'index'])
            ->name('admin.products.index');
        Route::get('/products/create', [ProductController::class, 'create'])
            ->name('admin.products.create');
        Route::post('/products', [ProductController::class, 'store'])
            ->name('admin.products.store');

        Route::get('/product-bulk-upload', [ProductController::class, 'importForm'])
            ->name('admin.products.import-form');
        Route::post('/product-bulk-upload', [ProductController::class, 'import'])
            ->name('admin.products.import');
        Route::get('/product-bulk-upload/index', [ProductController::class, 'downloadSampleCsv'])
            ->name('admin.products.download-sample-csv');
        Route::get('/product-bulk-export/csv', [ProductController::class, 'exportCsv'])
            ->name('admin.products.export-csv');

        Route::get('/products/stock', [ProductController::class, 'stockIndex'])
            ->name('admin.products.stock');
        Route::post('/products/bulk/stock', [ProductController::class, 'bulkStockUpdate'])
            ->name('admin.products.bulk-stock');

        Route::get('/products/{id}', [ProductController::class, 'details'])
            ->name('admin.products.details');
        Route::get('/products/{id}/edit', [ProductController::class, 'edit'])
            ->name('admin.products.edit');
        Route::put('/products/{id}', [ProductController::class, 'update'])
            ->name('admin.products.update');
        Route::delete('/products/{id}', [ProductController::class, 'destroy'])
            ->name('admin.products.destroy');
        Route::post('/products/{id}/restore', [ProductController::class, 'restore'])
            ->name('admin.products.restore');
        Route::delete('/products/{id}/force-delete', [ProductController::class, 'forceDelete'])
            ->name('admin.products.force-delete');
        Route::post('/products/{id}/toggle-status', [ProductController::class, 'toggleStatus'])
            ->name('admin.products.toggle-status');
        Route::post('/products/{id}/toggle-featured', [ProductController::class, 'toggleFeatured'])
            ->name('admin.products.toggle-featured');
        Route::post('/products/{id}/commission', [ProductController::class, 'updateCommission'])
            ->name('admin.products.commission');
        Route::post('/products/{id}/duplicate', [ProductController::class, 'duplicate'])
            ->name('admin.products.duplicate');

        Route::post('/products/bulk/delete', [ProductController::class, 'bulkDelete'])->name('admin.products.bulk-delete');
        Route::post('/products/bulk/restore', [ProductController::class, 'bulkRestore'])->name('admin.products.bulk-restore');
        Route::post('/products/bulk/force-delete', [ProductController::class, 'bulkForceDelete'])->name('admin.products.bulk-force-delete');
        Route::post('/products/bulk/status', [ProductController::class, 'bulkStatus'])->name('admin.products.bulk-status');
        Route::post('/products/bulk/featured', [ProductController::class, 'bulkFeatured'])->name('admin.products.bulk-featured');
        Route::post('/products/bulk/category', [ProductController::class, 'bulkCategory'])->name('admin.products.bulk-category');

        Route::get('/orders', [AdminOrderController::class, 'index'])
            ->name('admin.orders.index');
        Route::get('/orders/{id}', [AdminOrderController::class, 'show'])
            ->name('admin.orders.show');
        Route::post('/orders/{id}/status', [AdminOrderController::class, 'updateStatus'])
            ->name('admin.orders.update-status');

        Route::get('/returns', [AdminReturnController::class, 'index'])->name('admin.returns.index');
        Route::get('/returns/{id}', [AdminReturnController::class, 'show'])->name('admin.returns.show');
        Route::put('/returns/{id}/update', [AdminReturnController::class, 'update'])->name('admin.returns.update');

        Route::get('/reviews', [AdminReviewController::class, 'index'])->name('admin.reviews.index');
        Route::post('/reviews/{productId}/{reviewId}/toggle', [AdminReviewController::class, 'toggleApproved'])->name('admin.reviews.toggle');
        Route::delete('/reviews/{productId}/{reviewId}', [AdminReviewController::class, 'destroy'])->name('admin.reviews.destroy');

        Route::get('/subscribers', [SubscriberController::class, 'index'])->name('admin.subscribers.index');
        Route::delete('/subscribers/{id}', [SubscriberController::class, 'destroy'])->name('admin.subscribers.destroy');
        Route::get('/subscribers/export/csv', [SubscriberController::class, 'exportCsv'])->name('admin.subscribers.export-csv');
        Route::get('/orders/export/csv', [AdminOrderController::class, 'exportCsv'])->name('admin.orders.export-csv');
        Route::post('/orders/{id}/email-customer', [AdminOrderController::class, 'emailCustomer'])->name('admin.orders.email-customer');
        Route::get('/orders/{id}/invoice', [AdminOrderController::class, 'invoice'])->name('admin.orders.invoice');
        Route::post('/orders/{id}/ship-api', [AdminOrderController::class, 'shipViaApi'])->name('admin.orders.ship-api');

        // API Integrations
        Route::get('/api-integrations', [ApiIntegrationController::class, 'index'])->name('admin.api-integrations.index');
        Route::get('/api-integrations/create', [ApiIntegrationController::class, 'create'])->name('admin.api-integrations.create');
        Route::post('/api-integrations', [ApiIntegrationController::class, 'store'])->name('admin.api-integrations.store');
        Route::get('/api-integrations/{apiIntegration}/edit', [ApiIntegrationController::class, 'edit'])->name('admin.api-integrations.edit');
        Route::put('/api-integrations/{apiIntegration}', [ApiIntegrationController::class, 'update'])->name('admin.api-integrations.update');
        Route::delete('/api-integrations/{apiIntegration}', [ApiIntegrationController::class, 'destroy'])->name('admin.api-integrations.destroy');
        Route::post('/api-integrations/{apiIntegration}/toggle', [ApiIntegrationController::class, 'toggle'])->name('admin.api-integrations.toggle');
        Route::post('/api-integrations/{apiIntegration}/set-default', [ApiIntegrationController::class, 'setDefault'])->name('admin.api-integrations.set-default');
        Route::post('/api-integrations/{apiIntegration}/test', [ApiIntegrationController::class, 'test'])->name('admin.api-integrations.test');
        Route::get('/api-integrations/get-provider-fields', [ApiIntegrationController::class, 'getProviderFields'])->name('admin.api-integrations.get-provider-fields');
        Route::get('/brands', [BrandController::class, 'index'])
            ->name('admin.brands.index');
        Route::get('/brands/create', [BrandController::class, 'create'])
            ->name('admin.brands.create');
        Route::post('/brands', [BrandController::class, 'store'])
            ->name('admin.brands.store');
        Route::get('/brands/{id}/edit', [BrandController::class, 'edit'])
            ->name('admin.brands.edit');
        Route::put('/brands/{id}', [BrandController::class, 'update'])
            ->name('admin.brands.update');
        Route::delete('/brands/{id}', [BrandController::class, 'destroy'])
            ->name('admin.brands.destroy');
        Route::post('/brands/{id}/toggle-status', [BrandController::class, 'toggleStatus'])
            ->name('admin.brands.toggle-status');

        Route::get('/sellers', [SellerController::class, 'index'])
            ->name('admin.sellers.index');
        Route::get('/sellers/create', [SellerController::class, 'create'])
            ->name('admin.sellers.create');
        Route::post('/sellers', [SellerController::class, 'store'])
            ->name('admin.sellers.store');
        Route::get('/sellers/{id}/edit', [SellerController::class, 'edit'])
            ->name('admin.sellers.edit');
        Route::put('/sellers/{id}', [SellerController::class, 'update'])
            ->name('admin.sellers.update');
        Route::delete('/sellers/{id}', [SellerController::class, 'destroy'])
            ->name('admin.sellers.destroy');
        Route::post('/sellers/{id}/toggle-status', [SellerController::class, 'toggleStatus'])
            ->name('admin.sellers.toggle-status');
        Route::get('/sellers/{id}/followers', [SellerController::class, 'followers'])
            ->name('admin.sellers.followers');

        Route::get('/blogs', [AdminBlogController::class, 'index'])
            ->name('admin.blogs.index');
        Route::get('/blogs/create', [AdminBlogController::class, 'create'])
            ->name('admin.blogs.create');
        Route::post('/blogs', [AdminBlogController::class, 'store'])
            ->name('admin.blogs.store');
        Route::get('/blogs/{id}/edit', [AdminBlogController::class, 'edit'])
            ->name('admin.blogs.edit');
        Route::put('/blogs/{id}', [AdminBlogController::class, 'update'])
            ->name('admin.blogs.update');
        Route::delete('/blogs/{id}', [AdminBlogController::class, 'destroy'])
            ->name('admin.blogs.destroy');
        Route::post('/blogs/{id}/restore', [AdminBlogController::class, 'restore'])
            ->name('admin.blogs.restore');
        Route::delete('/blogs/{id}/force-delete', [AdminBlogController::class, 'forceDelete'])
            ->name('admin.blogs.force-delete');
        Route::post('/blogs/{id}/toggle-status', [AdminBlogController::class, 'toggleStatus'])
            ->name('admin.blogs.toggle-status');

        Route::get('/blog-categories', [BlogCategoryController::class, 'index'])->name('admin.blog-categories.index');
        Route::get('/blog-categories/create', [BlogCategoryController::class, 'create'])->name('admin.blog-categories.create');
        Route::post('/blog-categories', [BlogCategoryController::class, 'store'])->name('admin.blog-categories.store');
        Route::get('/blog-categories/{id}/edit', [BlogCategoryController::class, 'edit'])->name('admin.blog-categories.edit');
        Route::put('/blog-categories/{id}', [BlogCategoryController::class, 'update'])->name('admin.blog-categories.update');
        Route::delete('/blog-categories/{id}', [BlogCategoryController::class, 'destroy'])->name('admin.blog-categories.destroy');
        Route::post('/blog-categories/{id}/restore', [BlogCategoryController::class, 'restore'])->name('admin.blog-categories.restore');
        Route::delete('/blog-categories/{id}/force-delete', [BlogCategoryController::class, 'forceDelete'])->name('admin.blog-categories.force-delete');

        Route::get('/pages', [PageController::class, 'index'])
            ->name('admin.pages.index');
        Route::get('/pages/create', [PageController::class, 'create'])
            ->name('admin.pages.create');
        Route::post('/pages', [PageController::class, 'store'])
            ->name('admin.pages.store');
        Route::get('/pages/{id}/edit', [PageController::class, 'edit'])
            ->name('admin.pages.edit');
        Route::put('/pages/{id}', [PageController::class, 'update'])
            ->name('admin.pages.update');
        Route::delete('/pages/{id}', [PageController::class, 'destroy'])
            ->name('admin.pages.destroy');
        Route::post('/pages/{id}/restore', [PageController::class, 'restore'])
            ->name('admin.pages.restore');
        Route::post('/pages/{id}/toggle-status', [PageController::class, 'toggleStatus'])
            ->name('admin.pages.toggle-status');
        Route::delete('/pages/{id}/force-delete', [PageController::class, 'forceDelete'])
            ->name('admin.pages.force-delete');

        Route::get('/faqs', [FaqController::class, 'index'])
            ->name('admin.faqs.index');
        Route::get('/faqs/create', [FaqController::class, 'create'])
            ->name('admin.faqs.create');
        Route::post('/faqs', [FaqController::class, 'store'])
            ->name('admin.faqs.store');
        Route::get('/faqs/{id}/edit', [FaqController::class, 'edit'])
            ->name('admin.faqs.edit');
        Route::put('/faqs/{id}', [FaqController::class, 'update'])
            ->name('admin.faqs.update');
        Route::delete('/faqs/{id}', [FaqController::class, 'destroy'])
            ->name('admin.faqs.destroy');
        Route::post('/faqs/{id}/restore', [FaqController::class, 'restore'])
            ->name('admin.faqs.restore');
        Route::delete('/faqs/{id}/force-delete', [FaqController::class, 'forceDelete'])
            ->name('admin.faqs.force-delete');
        Route::post('/faqs/{id}/toggle-status', [FaqController::class, 'toggleStatus'])
            ->name('admin.faqs.toggle-status');

        Route::get('/contacts', [App\Http\Controllers\Admin\ContactController::class, 'index'])
            ->name('admin.contacts.index');
        Route::get('/contacts/{id}', [App\Http\Controllers\Admin\ContactController::class, 'show'])
            ->name('admin.contacts.show');
        Route::post('/contacts/{id}/reply', [App\Http\Controllers\Admin\ContactController::class, 'reply'])
            ->name('admin.contacts.reply');
        Route::delete('/contacts/{id}', [App\Http\Controllers\Admin\ContactController::class, 'destroy'])
            ->name('admin.contacts.destroy');

        Route::get('/coupons', [CouponController::class, 'index'])
            ->name('admin.coupons.index');
        Route::get('/coupons/create', [CouponController::class, 'create'])
            ->name('admin.coupons.create');
        Route::post('/coupons', [CouponController::class, 'store'])
            ->name('admin.coupons.store');
        Route::get('/coupons/{id}', [CouponController::class, 'show'])
            ->name('admin.coupons.show');
        Route::get('/coupons/{id}/edit', [CouponController::class, 'edit'])
            ->name('admin.coupons.edit');
        Route::put('/coupons/{id}', [CouponController::class, 'update'])
            ->name('admin.coupons.update');
        Route::delete('/coupons/{id}', [CouponController::class, 'destroy'])
            ->name('admin.coupons.destroy');

        Route::get('/home-page', [HomePageController::class, 'index'])
            ->name('admin.home-page.index');
        Route::get('/home-page/product-search', [HomePageController::class, 'searchProducts'])
            ->name('admin.home-page.product-search');
        Route::get('/home-page/{id}/edit', [HomePageController::class, 'edit'])
            ->name('admin.home-page.edit');
        Route::put('/home-page/{id}', [HomePageController::class, 'update'])
            ->name('admin.home-page.update');
        Route::post('/home-page/reorder', [HomePageController::class, 'reorder'])
            ->name('admin.home-page.reorder');
        Route::post('/home-page/{id}/toggle-status', [HomePageController::class, 'toggleStatus'])
            ->name('admin.home-page.toggle-status');

        Route::get('/settings', [AdminController::class, 'settingsIndex'])
            ->name('admin.settings.index');
        Route::get('/settings/languages', [AdminLanguageController::class, 'index'])
            ->name('admin.settings.languages.index');
        Route::post('/settings/languages/default', [AdminLanguageController::class, 'updateDefault'])
            ->name('admin.settings.languages.default');
        Route::get('/settings/languages/create', [AdminLanguageController::class, 'create'])
            ->name('admin.settings.languages.create');
        Route::post('/settings/languages', [AdminLanguageController::class, 'store'])
            ->name('admin.settings.languages.store');
        Route::post('/settings/languages/{language}/status', [AdminLanguageController::class, 'toggleStatus'])
            ->name('admin.settings.languages.status');
        Route::post('/settings/languages/{language}/rtl', [AdminLanguageController::class, 'toggleRtl'])
            ->name('admin.settings.languages.rtl');
        Route::get('/settings/languages/{language}/translations', [AdminLanguageController::class, 'translations'])
            ->name('admin.settings.languages.translations');
        Route::put('/settings/languages/{language}/translations', [AdminLanguageController::class, 'updateTranslations'])
            ->name('admin.settings.languages.translations.update');
        Route::post('/settings/languages/{language}/app-translations/import', [AdminLanguageController::class, 'importAppTranslations'])
            ->name('admin.settings.languages.import');
        Route::get('/settings/languages/{language}/app-translations/export', [AdminLanguageController::class, 'exportAppTranslations'])
            ->name('admin.settings.languages.export');
        Route::get('/settings/languages/{language}/edit', [AdminLanguageController::class, 'edit'])
            ->name('admin.settings.languages.edit');
        Route::put('/settings/languages/{language}', [AdminLanguageController::class, 'update'])
            ->name('admin.settings.languages.update');
        Route::delete('/settings/languages/{language}', [AdminLanguageController::class, 'destroy'])
            ->name('admin.settings.languages.destroy');
        Route::get('/settings/currency', [CurrencySettingsController::class, 'index'])
            ->name('admin.settings.currency.index');
        Route::post('/settings/currency/settings', [CurrencySettingsController::class, 'updateSettings'])
            ->name('admin.settings.currency.settings');
        Route::post('/settings/currency/sync-live-rates', [CurrencySettingsController::class, 'syncRates'])
            ->name('admin.settings.currency.sync');
        Route::get('/settings/currency/create', [CurrencySettingsController::class, 'create'])
            ->name('admin.settings.currency.create');
        Route::post('/settings/currency', [CurrencySettingsController::class, 'store'])
            ->name('admin.settings.currency.store');
        Route::post('/settings/currency/{currency}/status', [CurrencySettingsController::class, 'toggleStatus'])
            ->name('admin.settings.currency.status');
        Route::get('/settings/currency/{currency}/edit', [CurrencySettingsController::class, 'edit'])
            ->name('admin.settings.currency.edit');
        Route::put('/settings/currency/{currency}', [CurrencySettingsController::class, 'update'])
            ->name('admin.settings.currency.update');
        Route::get('/settings/pickup-points', [PickupPointController::class, 'index'])
            ->name('admin.settings.pickup-points.index');
        Route::get('/settings/pickup-points/create', [PickupPointController::class, 'create'])
            ->name('admin.settings.pickup-points.create');
        Route::post('/settings/pickup-points', [PickupPointController::class, 'store'])
            ->name('admin.settings.pickup-points.store');
        Route::post('/settings/pickup-points/{pickupPoint}/status', [PickupPointController::class, 'toggleStatus'])
            ->name('admin.settings.pickup-points.status');
        Route::get('/settings/pickup-points/{pickupPoint}/edit', [PickupPointController::class, 'edit'])
            ->name('admin.settings.pickup-points.edit');
        Route::put('/settings/pickup-points/{pickupPoint}', [PickupPointController::class, 'update'])
            ->name('admin.settings.pickup-points.update');
        Route::delete('/settings/pickup-points/{pickupPoint}', [PickupPointController::class, 'destroy'])
            ->name('admin.settings.pickup-points.destroy');
        Route::get('/settings/smtp', [SmtpSettingsController::class, 'index'])
            ->name('admin.settings.smtp');
        Route::post('/settings/smtp', [SmtpSettingsController::class, 'update'])
            ->name('admin.settings.smtp.update');
        Route::post('/settings/smtp/test', [SmtpSettingsController::class, 'test'])
            ->name('admin.settings.smtp.test');
        Route::get('/settings/payment-methods', [PaymentMethodSettingsController::class, 'index'])
            ->name('admin.settings.payment-methods');
        Route::post('/settings/payment-methods/{provider}', [PaymentMethodSettingsController::class, 'update'])
            ->name('admin.settings.payment-methods.update');
        Route::get('/settings/order', [AdminController::class, 'orderSettings'])
            ->name('admin.settings.order');
        Route::post('/settings/order', [AdminController::class, 'updateOrderSettings'])
            ->name('admin.settings.order.update');
        Route::get('/settings/features', [AdminController::class, 'featureSettings'])
            ->name('admin.settings.features');
        Route::post('/settings/features', [AdminController::class, 'updateFeatureSetting'])
            ->name('admin.settings.features.update');
        Route::get('/settings/custom', [AdminController::class, 'customSettings'])
            ->name('admin.settings.custom');
        Route::post('/settings/custom', [AdminController::class, 'updateCustomSettings'])
            ->name('admin.settings.custom.update');

        Route::get('/settings/header', [AdminController::class, 'headerSettings'])
            ->name('admin.settings.header');
        Route::post('/settings/header', [AdminController::class, 'updateHeaderSettings'])
            ->name('admin.settings.header.update');
        Route::get('/settings/seo', [AdminController::class, 'seoSettings'])
            ->name('admin.settings.seo');
        Route::post('/settings/seo', [AdminController::class, 'updateSeoSettings'])
            ->name('admin.settings.seo.update');
        Route::get('/settings/commission', [AdminController::class, 'commissionSettings'])
            ->name('admin.settings.commission');
        Route::post('/settings/commission', [AdminController::class, 'updateCommissionSettings'])
            ->name('admin.settings.commission.update');
        Route::post('/settings/footer', [AdminController::class, 'updateFooterSettings'])
            ->name('admin.settings.footer.update');
        Route::get('/settings/search-categories', [AdminController::class, 'searchCategorySettings'])
            ->name('admin.settings.search-categories');
        Route::post('/settings/search-categories', [AdminController::class, 'updateSearchCategorySettings'])
            ->name('admin.settings.search-categories.update');
        Route::get('/logs', [AdminController::class, 'logs'])
            ->name('admin.logs.index');
        Route::get('/revisions', [AdminController::class, 'revisions'])
            ->name('admin.revisions.index');
        Route::get('/revisions/{id}', [AdminController::class, 'revisionDetail'])
            ->name('admin.revisions.detail');
        Route::delete('/revisions/{id}', [AdminController::class, 'revisionDestroy'])
            ->name('admin.revisions.destroy');
        Route::post('/revisions/bulk-delete', [AdminController::class, 'revisionBulkDelete'])
            ->name('admin.revisions.bulk-delete');
        Route::post('/revisions/{id}/restore', [AdminController::class, 'revisionRestore'])
            ->name('admin.revisions.restore');
        Route::delete('/revisions/{id}/force-delete', [AdminController::class, 'revisionForceDelete'])
            ->name('admin.revisions.force-delete');
        Route::post('/revisions/bulk-restore', [AdminController::class, 'revisionBulkRestore'])
            ->name('admin.revisions.bulk-restore');
        Route::post('/revisions/bulk-force-delete', [AdminController::class, 'revisionBulkForceDelete'])
            ->name('admin.revisions.bulk-force-delete');
        Route::post('/revisions/empty-trash', [AdminController::class, 'revisionEmptyTrash'])
            ->name('admin.revisions.empty-trash');
        Route::get('/file-revisions', [AdminController::class, 'fileRevisions'])
            ->name('admin.file-revisions.index');
        Route::get('/file-revisions/download/{id}', [AdminController::class, 'fileRevisionDownload'])
            ->name('admin.file-revisions.download');
        Route::get('/file-revisions/diff/{id}', [AdminController::class, 'fileRevisionDiff'])
            ->name('admin.file-revisions.diff');
        Route::post('/file-revisions/truncate-diffs', [AdminController::class, 'fileRevisionTruncateDiffs'])
            ->name('admin.file-revisions.truncate-diffs');
        Route::post('/file-revisions/truncate-per-file', [AdminController::class, 'fileRevisionTruncatePerFile'])
            ->name('admin.file-revisions.truncate-per-file');
        Route::delete('/file-revisions/{id}', [AdminController::class, 'fileRevisionDestroy'])
            ->name('admin.file-revisions.destroy');
        Route::post('/file-revisions/bulk-delete', [AdminController::class, 'fileRevisionBulkDelete'])
            ->name('admin.file-revisions.bulk-delete');
        Route::post('/file-revisions/{id}/restore', [AdminController::class, 'fileRevisionRestore'])
            ->name('admin.file-revisions.restore');
        Route::delete('/file-revisions/{id}/force-delete', [AdminController::class, 'fileRevisionForceDelete'])
            ->name('admin.file-revisions.force-delete');
        Route::post('/file-revisions/bulk-restore', [AdminController::class, 'fileRevisionBulkRestore'])
            ->name('admin.file-revisions.bulk-restore');
        Route::post('/file-revisions/bulk-force-delete', [AdminController::class, 'fileRevisionBulkForceDelete'])
            ->name('admin.file-revisions.bulk-force-delete');
        Route::post('/file-revisions/empty-trash', [AdminController::class, 'fileRevisionEmptyTrash'])
            ->name('admin.file-revisions.empty-trash');

        // Image Manager
        Route::get('/images', [ImageController::class, 'index'])
            ->name('admin.images.index');
        Route::get('/images/picker', [ImageController::class, 'picker'])
            ->name('admin.images.picker');
        Route::post('/images/picker/upload', [ImageController::class, 'pickerStore'])
            ->name('admin.images.picker-store');
        Route::post('/images/picker/store-url', [ImageController::class, 'pickerStoreFromUrl'])
            ->name('admin.images.picker-store-url');
        Route::post('/images', [ImageController::class, 'store'])
            ->name('admin.images.store');
        Route::get('/images/{id}/edit', [ImageController::class, 'edit'])
            ->name('admin.images.edit');
        Route::put('/images/{id}', [ImageController::class, 'update'])
            ->name('admin.images.update');
        Route::post('/images/{id}/convert', [ImageController::class, 'convert'])
            ->name('admin.images.convert');
        Route::post('/images/bulk-convert', [ImageController::class, 'bulkConvert'])
            ->name('admin.images.bulk-convert');
        Route::post('/images/bulk-delete', [ImageController::class, 'bulkDelete'])
            ->name('admin.images.bulk-delete');
        Route::post('/images/bulk-mark-unused', [ImageController::class, 'bulkMarkUnused'])
            ->name('admin.images.bulk-mark-unused');
    });

/*
|--------------------------------------------------------------------------
| ADMIN PANEL — MASTER ADMIN + ADMIN (no staff)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->middleware(['auth', 'nocache', 'role:master_admin,admin'])
    ->group(function () {

        Route::delete('/logs/{file}', [AdminController::class, 'logClear'])
            ->name('admin.logs.clear');
        Route::get('/logs-trash', [AdminController::class, 'logsTrash'])
            ->name('admin.logs.trash');
        Route::post('/logs-trash/restore/{file}', [AdminController::class, 'logRestore'])
            ->name('admin.logs.restore');
        Route::delete('/logs-trash/force-delete/{file}', [AdminController::class, 'logForceDelete'])
            ->name('admin.logs.force-delete');
        Route::post('/logs-trash/empty', [AdminController::class, 'logEmptyTrash'])
            ->name('admin.logs.empty-trash');

        Route::get('/customers/create', [UserController::class, 'createCustomer'])
            ->name('admin.customers.create');
        Route::post('/customers', [UserController::class, 'storeCustomer'])
            ->name('admin.customers.store');
        Route::get('/customers/{id}', [UserController::class, 'show'])
            ->name('admin.customers.show');
        Route::get('/customers/{id}/edit', [UserController::class, 'edit'])
            ->name('admin.customers.edit');
        Route::put('/customers/{id}', [UserController::class, 'update'])
            ->name('admin.customers.update');
        Route::delete('/customers/{id}', [UserController::class, 'destroy'])
            ->name('admin.customers.destroy');
        Route::post('/customers/{id}/toggle-status', [UserController::class, 'toggleStatus'])
            ->name('admin.customers.toggle-status');
    });

/*
|--------------------------------------------------------------------------
| ADMIN PANEL — MASTER ADMIN + ADMIN (staff CRUD)
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->middleware(['auth', 'nocache', 'role:master_admin,admin'])
    ->group(function () {

        Route::get('/staff/create', [UserController::class, 'createStaff'])
            ->name('admin.staff.create');
        Route::post('/staff', [UserController::class, 'storeStaff'])
            ->name('admin.staff.store');
        Route::get('/staff/{id}/edit', [UserController::class, 'editStaff'])
            ->name('admin.staff.edit');
        Route::put('/staff/{id}', [UserController::class, 'updateStaff'])
            ->name('admin.staff.update');
        Route::delete('/staff/{id}', [UserController::class, 'destroyStaff'])
            ->name('admin.staff.destroy');
    });

/*
|--------------------------------------------------------------------------
| ADMIN PANEL — MASTER ADMIN ONLY
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->middleware(['auth', 'nocache', 'role:master_admin'])
    ->group(function () {

        Route::resource('users', UserManagementController::class)->names([
            'index' => 'admin.users.index',
            'create' => 'admin.users.create',
            'store' => 'admin.users.store',
            'edit' => 'admin.users.edit',
            'update' => 'admin.users.update',
            'destroy' => 'admin.users.destroy',
            'show' => 'admin.users.show',
        ]);
        Route::post('/users/{id}/toggle-status', [UserManagementController::class, 'toggleStatus'])
            ->name('admin.users.toggle-status');
    });
/*
|--------------------------------------------------------------------------
| STAFF PANEL
|--------------------------------------------------------------------------
*/

Route::prefix('staff')
    ->middleware(['auth', 'nocache', 'role:staff'])
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'staffDashboard'])
            ->name('staff.dashboard');

    });

/*
|--------------------------------------------------------------------------
| CATEGORIES
|--------------------------------------------------------------------------
*/
Route::prefix('admin')
    ->middleware(['auth', 'nocache', 'role:master_admin,admin,staff'])
    ->group(function () {

        Route::get('/categories', [CategoryController::class, 'index'])
            ->name('admin.categories.index');

        Route::get('/categories/create', [CategoryController::class, 'create'])
            ->name('admin.categories.create');

        Route::post('/categories', [CategoryController::class, 'store'])
            ->name('admin.categories.store');

        Route::get('/categories/{id}/edit', [CategoryController::class, 'edit'])
            ->name('admin.categories.edit');

        Route::put('/categories/{id}', [CategoryController::class, 'update'])
            ->name('admin.categories.update');

        Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])
            ->name('admin.categories.destroy');
        Route::post('/categories/{id}/restore', [CategoryController::class, 'restore'])
            ->name('admin.categories.restore');
        Route::delete('/categories/{id}/force-delete', [CategoryController::class, 'forceDelete'])
            ->name('admin.categories.force-delete');
        Route::post('/categories/{id}/toggle-status', [CategoryController::class, 'toggleStatus'])
            ->name('admin.categories.toggle-status');

    });

Route::post('/newsletter', [NewsletterController::class, 'store'])
    ->name('newsletter.store');
Route::get('/categories', [CategoryController::class, 'showCategories'])
    ->name('categories.index');
Route::get('/brands', [App\Http\Controllers\BrandController::class, 'brands'])
    ->name('brands');
// ─── Public / Storefront Routes ──────────────────────────────────────
Route::get('/category', function () {
    return redirect('/categories');
});
Route::get('/customer_products', [ShopController::class, 'customerProducts'])->name('customer.products');
Route::get('/category/{parent}/{child}/{subchild?}', [ShopController::class, 'categoryByPath'])->name('subcategory');
Route::get('/faq', [HomeController::class, 'faq'])->name('faq');
Route::get('/privacy', [HomeController::class, 'privacy'])->name('privacy');
Route::get('/privacy-policy', [HomeController::class, 'privacy'])->name('privacy.policy');
Route::get('/terms', [HomeController::class, 'terms'])->name('terms');
Route::get('/terms-conditions', [HomeController::class, 'terms'])->name('terms.conditions');
Route::get('/return-policy', [HomeController::class, 'returnPolicy'])->name('return.policy');
Route::get('/support-policy', [HomeController::class, 'supportPolicy'])->name('support.policy');

Route::match(['get', 'post'], '/order/tracking', [OrderController::class, 'tracking'])->middleware(['nocache'])->name('order.tracking');

Route::middleware(['auth', 'nocache'])->prefix('user')->name('user.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'userDashboard'])->name('dashboard');
    Route::get('/orders', [DashboardController::class, 'orders'])->name('orders');
    Route::get('/reviews', [DashboardController::class, 'reviews'])->name('reviews');
    Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/invoice/{id}', [OrderController::class, 'invoice'])->name('orders.invoice');
    Route::delete('/orders/{id}', [OrderController::class, 'destroy'])->name('orders.destroy');
    Route::get('/returns', [ReturnController::class, 'index'])->name('returns.index');
    Route::get('/returns/create/{orderId}', [ReturnController::class, 'create'])->name('returns.create');
    Route::post('/returns/{orderId}', [ReturnController::class, 'store'])->name('returns.store');
    Route::delete('/returns/{id}', [ReturnController::class, 'destroy'])->name('returns.destroy');
    Route::get('/wishlist', [DashboardController::class, 'wishlist'])->name('wishlist');
    Route::get('/vehicles', [DashboardController::class, 'vehicles'])->name('vehicles');
    Route::post('/vehicles', [DashboardController::class, 'storeVehicle'])->name('vehicles.store');
    Route::put('/vehicles/{id}', [DashboardController::class, 'updateVehicle'])->name('vehicles.update');
    Route::delete('/vehicles/{id}', [DashboardController::class, 'destroyVehicle'])->name('vehicles.destroy');
    Route::post('/vehicles/select/{id}', [DashboardController::class, 'selectVehicle'])->name('vehicles.select');
    Route::get('/followed-seller', [DashboardController::class, 'followedSellers'])->name('followed-sellers');
    Route::post('/followed-seller', [DashboardController::class, 'storeFollowedSeller'])->name('followed-sellers.store');
    Route::delete('/followed-seller/{id}', [DashboardController::class, 'destroyFollowedSeller'])->name('followed-sellers.destroy');
    Route::get('/api/seller/{id}', [DashboardController::class, 'getSellerDetails'])->name('api.seller.details');
    Route::get('/api/seller/{id}/products', [DashboardController::class, 'getSellerProducts'])->name('api.seller.products');
    Route::get('/addresses', [DashboardController::class, 'addresses'])->name('addresses');
    Route::post('/addresses', [DashboardController::class, 'storeAddress'])->name('addresses.store');
    Route::put('/addresses/{id}', [DashboardController::class, 'updateAddress'])->name('addresses.update');
    Route::delete('/addresses/{id}', [DashboardController::class, 'destroyAddress'])->name('addresses.destroy');
    Route::get('/notifications', [DashboardController::class, 'notifications'])->name('notifications');
    Route::post('/notifications/{id}/read', [DashboardController::class, 'markNotificationRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [DashboardController::class, 'markAllNotificationsRead'])->name('notifications.read-all');
    Route::get('/inquiries', [DashboardController::class, 'inquiries'])->name('inquiries');
    Route::put('/inquiries/{id}', [DashboardController::class, 'updateInquiry'])->name('inquiries.update');
    Route::delete('/inquiries/{id}', [DashboardController::class, 'destroyInquiry'])->name('inquiries.destroy');
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::get('/profile/edit', [UserController::class, 'editProfile'])->name('profile.edit');
    Route::post('/profile/update', [UserController::class, 'updateProfile'])->name('profile.update');
    Route::get('/change-password', function () {
        return view('user.change-password');
    })->name('change.password');
    Route::post('/change-password', [UserController::class, 'updatePassword'])->name('change.password.update');
});

Route::get('/admin/clear-cache', function () {

    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');

    return back()->with('success', 'Cache cleared successfully!');
})->middleware(['auth', 'nocache', 'role:master_admin,admin,staff'])->name('admin.clear.cache');

Route::middleware(['auth', 'nocache'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

});

Route::get('/language/{locale}', function ($locale) {
    if (Language::where('code', $locale)->where('is_active', true)->exists()) {
        session(['locale' => $locale]);
    }

    return back();
})->name('language.change');

Route::get('/currency/{amount}/{from}/{to}', [CurrencyController::class, 'convertCurrency']);
Route::get('/convert/{amount}/{from}/{to}', [CurrencyController::class, 'convert'])
    ->name('currency.convert');

Route::get('/currency/{currency}', function ($currency) {
    if (Currency::where('code', strtoupper($currency))->where('is_active', true)->exists()) {
        session(['currency' => strtoupper($currency)]);
    }

    return back();
})->name('currency.change');

Route::get('/contact-us', [ContactController::class, 'index'])
    ->name('contact');

Route::post('/contact-us', [ContactController::class, 'store'])
    ->name('contact.store');

Route::get('/contact/thank-you', [ContactController::class, 'thankYou'])
    ->name('contact.thank-you');
