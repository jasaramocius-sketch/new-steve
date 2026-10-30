<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Compare;
use App\Models\Notification;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once app_path('Helpers/currency.php');
        require_once app_path('Helpers/image.php');
        require_once app_path('Helpers/date.php');
        require_once app_path('Helpers/NotificationHelper.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.gs-pagination');
        Paginator::defaultSimpleView('vendor.pagination.gs-pagination');

        // Fetch dynamic currency rates from Unirate API
        try {
            $dynamicCurrencies = cache()->remember('dynamic_currency_rates', 43200, function () {
                $defaultCurrencies = config('currencies', []);

                try {
                    $response = Http::timeout(5)->withoutVerifying()->get('https://api.unirateapi.com/api/widget/v1/rates?base=USD');
                    if ($response->successful()) {
                        $data = $response->json();
                        if (isset($data['rates'])) {
                            $rates = $data['rates'];
                            foreach ($defaultCurrencies as $code => $info) {
                                if (isset($rates[$code])) {
                                    $defaultCurrencies[$code]['rate'] = $rates[$code];
                                }
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    logger()->warning('Dynamic currency rates unavailable, using defaults: '.$e->getMessage());
                }

                return $defaultCurrencies;
            });

            config(['currencies' => $dynamicCurrencies]);
        } catch (\Exception $e) {
            logger()->error('Failed to fetch/apply dynamic currencies: '.$e->getMessage());
        }

        View::composer('*', function ($view) {
            static $sharedData = null;

            if ($sharedData !== null) {
                $view->with($sharedData);

                return;
            }

            $wishlistCount = 0;
            $compareCount = 0;
            $wishedProductIds = [];
            $unreadNotificationCount = 0;

            $loggedIn = session('user_logged_in') && session('user_profile.id');

            if (! $loggedIn && Auth::check()) {
                $user = Auth::user();
                session()->put([
                    'user_logged_in' => true,
                    'user_profile' => $user->only(['id', 'name', 'email', 'role', 'phone', 'address', 'city', 'country']),
                ]);
                $loggedIn = true;
            }

            if ($loggedIn) {
                $userId = session('user_profile.id');
                $wishlistCount = Wishlist::where('user_id', $userId)->count();
                $wishedProductIds = Wishlist::where('user_id', $userId)->pluck('product_id')->toArray();
                $compareCount = Compare::where('user_id', $userId)->count();
                $unreadNotificationCount = Notification::where('user_id', $userId)->unread()->count();
            } else {
                $wishedProductIds = session('guest_wishlist', []);
                $wishlistCount = count($wishedProductIds);
                $compareCount = count(session('guest_compare', []));
            }

            $cart = session('cart', []);
            $cartCount = count($cart);
            $cartTotal = array_sum(array_map(fn ($item) => ($item['price'] ?? 0) * ($item['qty'] ?? ($item['quantity'] ?? 0)), $cart));

            // The mobile category tree is only rendered by the storefront layout.
            // Skip the (cache-backed) tree lookup for admin pages so the panel
            // stays light even when the tree cache is being rebuilt.
            $isAdminArea = str_contains('/'.ltrim(request()->path(), '/').'/', '/admin/');
            $mobileCategoryTree = $isAdminArea ? [] : \App\Services\CategoryTree::tree();

            $sharedData = [
                'wishlistCount' => $wishlistCount,
                'wishedProductIds' => $wishedProductIds,
                'compareCount' => $compareCount,
                'cartCount' => $cartCount,
                'cartTotal' => $cartTotal,
                'unreadNotificationCount' => $unreadNotificationCount,
                'mobileCategoryTree' => $mobileCategoryTree,
            ];

            $view->with($sharedData);
        });

        if (app()->runningInConsole()) {
            // (console boot logging intentionally removed — it was pure noise)
        }

        Category::saved(fn () => \App\Services\CategoryTree::flush());
        Category::deleted(fn () => \App\Services\CategoryTree::flush());
    }
}
