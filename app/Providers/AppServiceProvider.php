<?php

namespace App\Providers;

use App\Mail\Transport\MailgunApiTransport;
use App\Mail\Transport\MicrosoftGraphTransport;
use App\Models\Category;
use App\Models\Compare;
use App\Models\Currency;
use App\Models\Notification;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Wishlist;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

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

        Mail::extend('mailgun_api', fn (array $config) => new MailgunApiTransport(
            (string) config('services.mailgun_settings.domain'),
            (string) config('services.mailgun_settings.secret'),
        ));
        Mail::extend('microsoft_graph', fn (array $config) => new MicrosoftGraphTransport(
            (string) config('services.microsoft_mail.tenant_id'),
            (string) config('services.microsoft_mail.client_id'),
            (string) config('services.microsoft_mail.client_secret'),
        ));
        $this->applyMailSettings();
        $this->applyFileSystemCacheSettings();

        try {
            $databaseCurrencies = Currency::where('is_active', true)->get()->mapWithKeys(fn ($currency) => [
                $currency->code => [
                    'symbol' => $currency->symbol,
                    'name' => $currency->name,
                    'rate' => (float) $currency->rate,
                ],
            ])->all();
            if ($databaseCurrencies !== []) {
                config(['currencies' => $databaseCurrencies]);
            }
        } catch (\Throwable $exception) {
            logger()->warning('Database currency catalog unavailable: '.$exception->getMessage());
        }

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

            $currencies = config('currencies', []);
            foreach ($currencies as $code => &$currency) {
                if (isset($dynamicCurrencies[$code]['rate'])) {
                    $currency['rate'] = $dynamicCurrencies[$code]['rate'];
                }
            }
            unset($currency);
            config(['currencies' => $currencies]);
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

            $sharedData = [
                'wishlistCount' => $wishlistCount,
                'wishedProductIds' => $wishedProductIds,
                'compareCount' => $compareCount,
                'cartCount' => $cartCount,
                'cartTotal' => $cartTotal,
                'unreadNotificationCount' => $unreadNotificationCount,
            ];

            $view->with($sharedData);
        });

        View::composer('admin.partials.sidebar', function ($view) {
            try {
                $view->with([
                    'supportOpenTickets' => \App\Models\SupportTicket::whereIn('status', ['open', 'pending'])->count(),
                    'supportOpenConversations' => \App\Support\FeatureFlags::enabled('conversation_system')
                        ? \App\Models\ProductConversation::where('status', 'open')->count()
                        : 0,
                ]);
            } catch (\Throwable) {
                $view->with([
                    'supportOpenTickets' => 0,
                    'supportOpenConversations' => 0,
                ]);
            }
        });

        if (app()->runningInConsole()) {
            // (console boot logging intentionally removed — it was pure noise)
        }

        Category::saved(fn () => \App\Services\CategoryTree::flush());
        Category::deleted(fn () => \App\Services\CategoryTree::flush());
    }

    private function applyMailSettings(): void
    {
        try {
            $settings = Setting::getAllAsArray();
            $driver = $settings['mail_driver'] ?? null;
            if (! in_array($driver, ['smtp', 'sendmail', 'mailgun', 'microsoft', 'log'], true)) {
                $hasMicrosoftCredentials = filled(config('services.microsoft_mail.tenant_id'))
                    && filled(config('services.microsoft_mail.client_id'))
                    && filled(config('services.microsoft_mail.client_secret'));

                if (
                    $driver === null
                    && ! app()->environment('testing')
                    && config('mail.default') === 'log'
                    && $hasMicrosoftCredentials
                ) {
                    config(['mail.default' => 'microsoft']);
                }

                return;
            }

            $smtpPassword = $this->decryptMailSecret($settings['mail_password'] ?? null);
            $mailgunSecret = $this->decryptMailSecret($settings['mailgun_secret'] ?? null);
            $microsoftSecret = $this->decryptMailSecret($settings['mail_client_secret'] ?? null);
            $encryption = $settings['mail_encryption'] ?? 'tls';

            config([
                'mail.default' => $driver,
                'mail.from.address' => $settings['mail_from_address'] ?? config('mail.from.address'),
                'mail.from.name' => $settings['mail_from_name'] ?? config('mail.from.name'),
                'mail.mailers.smtp.host' => $settings['mail_host'] ?? config('mail.mailers.smtp.host'),
                'mail.mailers.smtp.port' => (int) ($settings['mail_port'] ?? config('mail.mailers.smtp.port', 587)),
                'mail.mailers.smtp.username' => $settings['mail_username'] ?? config('mail.mailers.smtp.username'),
                'mail.mailers.smtp.password' => $smtpPassword ?? config('mail.mailers.smtp.password'),
                'mail.mailers.smtp.scheme' => match ($encryption) {
                    'ssl' => 'smtps',
                    'none' => null,
                    default => 'smtp',
                },
                'services.mailgun_settings.domain' => $settings['mailgun_domain'] ?? '',
                'services.mailgun_settings.secret' => $mailgunSecret ?? '',
                'services.microsoft_mail.tenant_id' => $settings['mail_tenant_id'] ?? config('services.microsoft_mail.tenant_id', ''),
                'services.microsoft_mail.client_id' => $settings['mail_client_id'] ?? config('services.microsoft_mail.client_id', ''),
                'services.microsoft_mail.client_secret' => $microsoftSecret ?? config('services.microsoft_mail.client_secret', ''),
            ]);
        } catch (Throwable $exception) {
            logger()->warning('Stored mail configuration could not be applied: '.$exception->getMessage());
        }
    }

    private function applyFileSystemCacheSettings(): void
    {
        try {
            $settings = Setting::getAllAsArray();

            $filesystemDriver = $settings['filesystem_driver'] ?? null;
            if (in_array($filesystemDriver, ['local', 'public', 's3'], true)) {
                config(['filesystems.default' => $filesystemDriver]);
            }

            $awsSecret = $this->decryptMailSecret($settings['aws_secret_access_key'] ?? null);
            if (($settings['aws_access_key_id'] ?? '') !== '' || $awsSecret || ($settings['aws_bucket'] ?? '') !== '') {
                config([
                    'filesystems.disks.s3.key' => $settings['aws_access_key_id'] ?? config('filesystems.disks.s3.key'),
                    'filesystems.disks.s3.secret' => $awsSecret ?? config('filesystems.disks.s3.secret'),
                    'filesystems.disks.s3.region' => $settings['aws_default_region'] ?? config('filesystems.disks.s3.region'),
                    'filesystems.disks.s3.bucket' => $settings['aws_bucket'] ?? config('filesystems.disks.s3.bucket'),
                    'filesystems.disks.s3.url' => $settings['aws_url'] ?? config('filesystems.disks.s3.url'),
                ]);
            }

            $cacheDriver = $settings['cache_driver'] ?? null;
            if (in_array($cacheDriver, ['file', 'database', 'redis', 'array'], true)) {
                config(['cache.default' => $cacheDriver]);
            }

            $sessionDriver = $settings['session_driver'] ?? null;
            if (in_array($sessionDriver, ['file', 'cookie', 'database', 'redis', 'array'], true)) {
                config(['session.driver' => $sessionDriver]);
            }

            $redisHost = $settings['redis_host'] ?? null;
            $redisPort = $settings['redis_port'] ?? null;
            $redisPassword = $this->decryptMailSecret($settings['redis_password'] ?? null);
            if ($redisHost || $redisPort || $redisPassword) {
                config([
                    'database.redis.default.host' => $redisHost ?: config('database.redis.default.host'),
                    'database.redis.default.port' => $redisPort ? (int) $redisPort : config('database.redis.default.port'),
                    'database.redis.default.password' => $redisPassword ?: config('database.redis.default.password'),
                    'database.redis.cache.host' => $redisHost ?: config('database.redis.cache.host'),
                    'database.redis.cache.port' => $redisPort ? (int) $redisPort : config('database.redis.cache.port'),
                    'database.redis.cache.password' => $redisPassword ?: config('database.redis.cache.password'),
                ]);
            }
        } catch (Throwable $exception) {
            logger()->warning('Stored file system/cache configuration could not be applied: '.$exception->getMessage());
        }
    }

    private function decryptMailSecret(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (Throwable) {
            return $value;
        }
    }
}
