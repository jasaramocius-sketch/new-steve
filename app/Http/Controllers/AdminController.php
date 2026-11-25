<?php

namespace App\Http\Controllers;

use App\Jobs\SyncProductStockJob;
use App\Models\Blog;
use App\Models\Category;
use App\Models\CommissionHistory;
use App\Models\FileRevision;
use App\Models\Image;
use App\Models\Order;
use App\Models\Page;
use App\Models\Product;
use App\Models\Revision;
use App\Models\Setting;
use App\Models\TaxType;
use App\Models\User;
use App\Support\StockSync;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Rules\ZipCodeForCountry;
use App\Support\FeatureFlags;

class AdminController extends Controller
{
    public function index()
    {
        $topProducts = Order::customerPlaced()->revenueEligible()
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->select('order_items.product_id', DB::raw('SUM(order_items.qty) as total_qty'))
            ->groupBy('order_items.product_id')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        if ($topProducts->isNotEmpty()) {
            $products = Product::whereIn('id', $topProducts->pluck('product_id'))->get()->keyBy('id');
            $topProducts = $topProducts->map(fn ($item) => tap($item, fn ($i) => $i->product = $products->get($i->product_id)));
        }

        $ordersByStatus = Order::customerPlaced()->selectRaw('status, COUNT(*) as count')->groupBy('status')->get();

        $dbRevenue = Order::customerPlaced()->revenueEligible()->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total_amount) as total")->groupBy('month')->orderBy('month')->take(12)->pluck('total', 'month');

        $monthlyRevenueData = collect();
        for ($i = 11; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $monthlyRevenueData->put($key, (float) ($dbRevenue->get($key) ?? 0));
        }

        $weeklyRevenue = Order::customerPlaced()->revenueEligible()->selectRaw('YEARWEEK(created_at, 1) as week, SUM(total_amount) as total')
            ->where('created_at', '>=', now()->subWeeks(12))
            ->groupBy('week')
            ->orderBy('week')
            ->pluck('total', 'week');

        $weeklyRevenueData = collect();
        for ($i = 11; $i >= 0; $i--) {
            $start = now()->subWeeks($i)->startOfWeek();
            $key = $start->format('Y-m-d');
            $weekKey = (int) $start->format('oW');
            $weeklyRevenueData->put($key, (float) ($weeklyRevenue->get($weekKey) ?? 0));
        }

        $dailyRevenue = Order::customerPlaced()->revenueEligible()->selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        $dailyRevenueData = collect();
        for ($i = 29; $i >= 0; $i--) {
            $key = now()->subDays($i)->format('Y-m-d');
            $dailyRevenueData->put($key, (float) ($dailyRevenue->get($key) ?? 0));
        }

        $hourlyRevenue = Order::customerPlaced()->revenueEligible()->selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00') as hour, SUM(total_amount) as total")
            ->whereDate('created_at', today())
            ->groupBy('hour')
            ->orderBy('hour')
            ->pluck('total', 'hour');

        $hourlyRevenueData = collect();
        $currentHour = (int) now()->format('H');
        for ($h = 0; $h <= $currentHour; $h++) {
            $key = now()->format('Y-m-d').' '.str_pad($h, 2, '0', STR_PAD_LEFT).':00:00';
            $hourlyRevenueData->put($key, (float) ($hourlyRevenue->get($key) ?? 0));
        }

        $fiveMinRevenue = Order::customerPlaced()->revenueEligible()->selectRaw('
            FROM_UNIXTIME(FLOOR(UNIX_TIMESTAMP(created_at) / 300) * 300) as interval_time,
            SUM(total_amount) as total
        ')
            ->whereDate('created_at', today())
            ->groupBy('interval_time')
            ->orderBy('interval_time')
            ->pluck('total', 'interval_time');

        $fiveMinRevenueData = collect();
        $current = now()->startOfDay();
        $now = now();
        while ($current <= $now) {
            $key = $current->format('Y-m-d H:i:s');
            $fiveMinRevenueData->put($key, (float) ($fiveMinRevenue->get($key) ?? 0));
            $current->addMinutes(5);
        }

        return view('backend.dashboard', [
            'totalOrders' => Order::customerPlaced()->count(),
            'totalRevenue' => Order::customerPlaced()->revenueEligible()->sum('total_amount'),
            'totalProducts' => Product::count(),
            'totalCustomers' => User::where('role', 'customer')->count(),
            'pendingOrders' => Order::customerPlaced()->where('status', 'pending')->count(),
            'recentOrders' => Order::customerPlaced()->with('user')->latest()->take(5)->get(),
            'ordersByStatus' => $ordersByStatus,
            'monthlyRevenue' => $monthlyRevenueData,
            'ordersByStatusJson' => $ordersByStatus->pluck('count', 'status'),
            'monthlyRevenueJson' => $monthlyRevenueData,
            'weeklyRevenueJson' => $weeklyRevenueData,
            'dailyRevenueJson' => $dailyRevenueData,
            'hourlyRevenueJson' => $hourlyRevenueData,
            'fiveMinRevenueJson' => $fiveMinRevenueData,
            'topProducts' => $topProducts,
        ]);
    }

    public function profile()
    {
        $user = Auth::user();

        return view('backend.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:255|regex:/^[0-9+\-\s()]*$/',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'postal_code' => ['nullable', 'string', 'max:255', new ZipCodeForCountry($request->input('country'))],
        ]);

        if ($request->filled('image_from_manager')) {
            $oldAvatar = $user->avatar;
            $user->avatar = 'storage/'.ltrim($request->input('image_from_manager'), '/');
            if ($oldAvatar && $oldAvatar !== $user->avatar) {
                deleteImageFiles($oldAvatar);
            }
        }

        $user->update($request->only(['name', 'email', 'phone', 'address', 'city', 'state', 'country', 'postal_code']));

        session()->put('user_profile', $user->only(['id', 'name', 'email', 'role', 'phone', 'address', 'city', 'state', 'country', 'postal_code']));

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        if (! Hash::check($request->current_password, Auth::user()->password)) {
            return back()->withErrors(['current_password' => 'Incorrect current password']);
        }

        Auth::user()->update([
            'password' => Hash::make($request->new_password),
        ]);

        return back()->with('success', 'Password changed successfully.');
    }

    public function todayRevenue(Request $request)
    {
        $range = $request->get('range', '5min');

        if ($range === 'hourly') {
            $hourlyRevenue = Order::customerPlaced()->revenueEligible()->selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00') as hour, SUM(total_amount) as total")
                ->whereDate('created_at', today())
                ->groupBy('hour')
                ->orderBy('hour')
                ->pluck('total', 'hour');

            $data = collect();
            $currentHour = (int) now()->format('H');
            for ($h = 0; $h <= $currentHour; $h++) {
                $key = now()->format('Y-m-d').' '.str_pad($h, 2, '0', STR_PAD_LEFT).':00:00';
                $data->put($key, (float) ($hourlyRevenue->get($key) ?? 0));
            }

            return response()->json($data);
        }

        $todayRevenue = Order::customerPlaced()->revenueEligible()->selectRaw('
            FROM_UNIXTIME(FLOOR(UNIX_TIMESTAMP(created_at) / 300) * 300) as interval_time,
            SUM(total_amount) as total
        ')
            ->whereDate('created_at', today())
            ->groupBy('interval_time')
            ->orderBy('interval_time')
            ->pluck('total', 'interval_time');

        $fiveMinIntervals = collect();
        $current = now()->startOfDay();
        $now = now();
        while ($current <= $now) {
            $key = $current->format('Y-m-d H:i:s');
            $fiveMinIntervals->put($key, (float) ($todayRevenue->get($key) ?? 0));
            $current->addMinutes(5);
        }

        return response()->json($fiveMinIntervals);
    }

    public function customSettings()
    {
        $settings = Setting::getAllAsArray();
        $taxTypes = TaxType::orderBy('name')->get();

        return view('backend.settings.custom', compact('settings', 'taxTypes'));
    }

    public function orderSettings()
    {
        $settings = Setting::getAllAsArray();

        return view('backend.settings.order', compact('settings'));
    }

    public function updateOrderSettings(Request $request)
    {
        $validated = $request->validate([
            'minimum_order_amount_check' => 'required|boolean',
            'minimum_order_amount' => 'required|numeric|min:0|max:10000000',
        ]);

        Setting::set('minimum_order_amount_check', $request->boolean('minimum_order_amount_check') ? '1' : '0');
        Setting::set('minimum_order_amount', number_format((float) $validated['minimum_order_amount'], 2, '.', ''));

        return redirect()->route('admin.settings.order')->with('success', 'Minimum order settings updated.');
    }

    public function settingsIndex()
    {
        $settingsGroups = [
            [
                'title' => 'General',
                'icon' => 'fas la-sliders-h',
                'items' => [
                    ['name' => 'General Settings', 'description' => 'Header, footer, logos and navigation', 'icon' => 'fas fa-cog', 'route' => 'admin.settings.header'],
                    ['name' => 'Contact Information', 'description' => 'Address, phone, email, working hours and map', 'icon' => 'fas fa-address-book', 'route' => 'admin.settings.contact'],
                    ['name' => 'Invoice Settings', 'description' => 'Manage invoice branding, content and PDF layout', 'icon' => 'fas fa-file-invoice', 'route' => 'admin.settings.invoice'],
                    ['name' => 'Features activation', 'description' => 'Turn store features on or off', 'icon' => 'fas fa-toggle-on', 'route' => 'admin.settings.features'],
                    ['name' => 'Order Configuration', 'description' => 'Minimum order amount', 'icon' => 'fas fa-shopping-bag', 'route' => 'admin.settings.order'],
                    ['name' => 'File System & Cache', 'description' => 'Storage driver and caching', 'icon' => 'fas fa-hdd', 'route' => 'admin.settings.file-system-cache'],
                ],
            ],
            [
                'title' => 'SEO',
                'icon' => 'fas fa-search',
                'items' => [
                    ['name' => 'SEO Settings', 'description' => 'Meta defaults, verification, analytics and robots', 'icon' => 'fas fa-search', 'route' => 'admin.settings.seo'],
                    ['name' => 'XML Sitemap', 'description' => 'Open /sitemap.xml in a new tab', 'icon' => 'fas fa-sitemap', 'url' => route('sitemap'), 'external' => true],
                ],
            ],
            [
                'title' => 'Localization',
                'icon' => 'fas fa-globe',
                'items' => [
                    ['name' => 'Languages', 'description' => 'Site languages and translations', 'icon' => 'fas fa-language', 'route' => 'admin.settings.languages.index'],
                    ['name' => 'Currency', 'description' => 'Currencies and exchange rates', 'icon' => 'fas fa-coins', 'route' => 'admin.settings.currency.index'],
                    ['name' => 'Vat & TAX', 'description' => 'Tax rules applied to products', 'icon' => 'fas fa-percent', 'route' => 'admin.settings.custom'],
                ],
            ],
            [
                'title' => 'Payment',
                'icon' => 'fas fa-credit-card',
                'items' => [
                    ['name' => 'Payment Methods', 'description' => 'Gateway keys and credentials', 'icon' => 'fas fa-credit-card', 'route' => 'admin.settings.payment-methods'],
                ],
            ],
            [
                'title' => 'Shipping',
                'icon' => 'fas fa-truck',
                'items' => [
                    ['name' => 'Shipping Configuration', 'description' => 'Enable or disable home delivery', 'icon' => 'fas fa-truck-loading', 'route' => 'admin.settings.shipping'],
                    ['name' => 'Pickup point', 'description' => 'Collection locations', 'icon' => 'fas fa-store', 'route' => 'admin.settings.pickup-points.index'],
                    ['name' => 'Shipping Countries', 'description' => 'Countries you ship to', 'icon' => 'fas fa-flag', 'route' => 'admin.settings.shipping-countries.index'],
                    ['name' => 'Shipping States', 'description' => 'States within countries', 'icon' => 'fas fa-map', 'route' => 'admin.settings.shipping-states.index'],
                    ['name' => 'Shipping Cities', 'description' => 'Cities within states', 'icon' => 'fas fa-city', 'route' => 'admin.settings.shipping-cities.index'],
                    ['name' => 'Shipping Zones', 'description' => 'Destinations and delivery rates', 'icon' => 'fas fa-map-marked', 'route' => 'admin.settings.shipping-zones.index'],
                    ['name' => 'Shipping Carrier', 'description' => 'Carriers and transit times', 'icon' => 'fas fa-shipping-fast', 'route' => 'admin.settings.shipping-carriers.index'],
                ],
            ],
            [
                'title' => 'Email & Social',
                'icon' => 'fas fa-envelope',
                'items' => [
                    ['name' => 'SMTP Settings', 'description' => 'Outgoing mail server', 'icon' => 'fas fa-envelope', 'route' => 'admin.settings.smtp'],
                    ['name' => 'Social media Logins', 'description' => 'Google and Facebook sign-in', 'icon' => 'fas fa-users', 'route' => 'admin.settings.social-logins'],
                    ['name' => 'Facebook Chat', 'description' => 'Live chat widget', 'icon' => 'fab fa-facebook', 'route' => 'admin.settings.facebook-chat'],
                    ['name' => 'Facebook Comment', 'description' => 'Comments on product pages', 'icon' => 'fas fa-comments', 'route' => 'admin.settings.facebook-comment'],
                ],
            ],
            [
                'title' => 'Google',
                'icon' => 'fab fa-google',
                'items' => [
                    ['name' => 'Analytics Tools', 'description' => 'Analytics and pixel tracking', 'icon' => 'fas fa-chart-line', 'route' => 'admin.settings.seo'],
                    ['name' => 'Google reCAPTCHA', 'description' => 'Spam protection on forms', 'icon' => 'fas fa-shield-alt', 'route' => 'admin.settings.google-recaptcha'],
                    ['name' => 'Google Map', 'description' => 'Map API key', 'icon' => 'fas fa-map-marker', 'route' => 'admin.settings.google-map'],
                    ['name' => 'Google Firebase', 'description' => 'Push notification setup', 'icon' => 'fas fa-fire', 'route' => 'admin.settings.google-firebase'],
                ],
            ],
        ];

        return view('backend.settings.index', compact('settingsGroups'));
    }

    public function featureSettings()
    {
        $settingsGroups = FeatureFlags::definitions();

        foreach ($settingsGroups as &$group) {
            foreach ($group['items'] as &$item) {
                $item['enabled'] = FeatureFlags::enabled($item['key']);
            }
            unset($item);
        }
        unset($group);

        return view('backend.settings.features', compact('settingsGroups'));
    }

    public function updateFeatureSetting(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string',
            'enabled' => 'required|boolean',
        ]);

        if (! in_array($validated['key'], FeatureFlags::keys(), true)) {
            return response()->json(['message' => 'Unknown feature setting.'], 422);
        }

        $enabled = $request->boolean('enabled');
        Setting::set($validated['key'], $enabled ? '1' : '0');

        if ($validated['key'] === 'disable_image_optimization') {
            Setting::set('webp_frontend', $enabled ? '0' : '1');
        }

        FeatureFlags::flush();

        return response()->json([
            'success' => true,
            'enabled' => $enabled,
            'message' => 'Feature setting saved successfully.',
        ]);
    }

    public function updateCustomSettings(Request $request)
    {
        $validated = $request->validate([
            'tax_rate' => 'required|numeric|min:0|max:100',
            'custom_value' => 'nullable|string|max:255',
        ]);
        Setting::set('tax_rate', (string) $validated['tax_rate']);
        Setting::set('custom_value', (string) ($validated['custom_value'] ?? ''));

        return redirect()->route('admin.settings.custom')->with('success', 'Tax rate and custom value updated.');
    }

    public function headerSettings()
    {
        $settings = Setting::getAllAsArray();
        $navMenu = json_decode((string) ($settings['nav_menu'] ?? '[]'), true);
        $availableMenuUrls = $this->availableMenuUrls(is_array($navMenu) ? $navMenu : []);

        return view('backend.settings.header', compact('settings', 'availableMenuUrls'));
    }

    /**
     * Paginated link options for the General Settings nav-menu builder.
     * Replaces rendering every page/post/category into the page HTML.
     */
    public function headerLinkOptions(Request $request)
    {
        $type = (string) $request->query('type', '');
        $mode = (string) $request->query('mode', 'all');
        $q = trim((string) $request->query('q', ''));
        $page = max(1, (int) $request->query('page', 1));

        $types = [
            'pages' => ['model' => Page::class, 'label' => 'title', 'prefix' => '/', 'value' => 'page'],
            'posts' => ['model' => Blog::class, 'label' => 'title', 'prefix' => '/blog/', 'value' => 'post'],
            'categories' => ['model' => Category::class, 'label' => 'name', 'prefix' => '/category/', 'value' => 'category'],
        ];

        if (! isset($types[$type])) {
            return response()->json($this->emptyLinkOptions());
        }

        $config = $types[$type];
        $query = $config['model']::query();

        if ($type === 'pages') {
            $query->where('status', true);
        } elseif ($type === 'posts') {
            $query->where('status', 'published');
        }

        if ($mode === 'search' && $q !== '') {
            $query->where($config['label'], 'like', '%'.$q.'%');
            $query->orderBy($config['label']);
        } elseif ($mode === 'recent') {
            $query->orderByDesc('created_at');
        } else {
            $query->orderBy($config['label']);
        }

        $perPage = $mode === 'recent' ? 10 : 20;
        $paginator = $query->paginate($perPage, ['id', $config['label'], 'slug'], 'page', $page);

        if ($paginator->lastPage() > 0 && $paginator->currentPage() > $paginator->lastPage()) {
            $paginator = $query->paginate($perPage, ['*'], 'page', $paginator->lastPage());
        }

        $items = $paginator->getCollection()->map(function ($model) use ($config) {
            return [
                'label' => (string) $model->{$config['label']},
                'url' => $config['prefix'].$model->slug,
                'type' => $config['value'],
            ];
        })->values()->all();

        return response()->json([
            'data' => $items,
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ]);
    }

    private function emptyLinkOptions(): array
    {
        return [
            'data' => [],
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => 20,
            'total' => 0,
        ];
    }

    /**
     * Validate only the links already saved in the menu instead of embedding
     * every page/post/category URL in the HTML.
     */
    private function availableMenuUrls(array $items): array
    {
        $linked = [];

        $walk = function (array $nodes) use (&$walk, &$linked) {
            foreach ($nodes as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $type = (string) ($item['type'] ?? '');
                $url = (string) ($item['url'] ?? '');

                if ($url !== '' && in_array($type, ['page', 'post', 'category'], true)) {
                    $linked[$type.':'.strtolower($url)] = true;
                }

                if (! empty($item['children']) && is_array($item['children'])) {
                    $walk($item['children']);
                }
            }
        };
        $walk($items);

        if ($linked === []) {
            return [];
        }

        $slugs = ['page' => [], 'post' => [], 'category' => []];
        foreach (array_keys($linked) as $key) {
            [$type, $url] = array_pad(explode(':', $key, 2), 2, '');
            $path = parse_url($url, PHP_URL_PATH) ?: $url;
            $slug = basename((string) $path);
            if ($slug !== '') {
                $slugs[$type][strtolower($slug)] = true;
            }
        }

        $available = [];

        if ($slugs['page'] !== []) {
            Page::whereIn('slug', array_keys($slugs['page']))
                ->where('status', true)
                ->pluck('slug')
                ->each(function ($slug) use (&$available) {
                    $available['page:/'.strtolower($slug)] = true;
                });
        }

        if ($slugs['post'] !== []) {
            Blog::whereIn('slug', array_keys($slugs['post']))
                ->where('status', 'published')
                ->pluck('slug')
                ->each(function ($slug) use (&$available) {
                    $available['post:/blog/'.strtolower($slug)] = true;
                });
        }

        if ($slugs['category'] !== []) {
            Category::whereIn('slug', array_keys($slugs['category']))
                ->pluck('slug')
                ->each(function ($slug) use (&$available) {
                    $available['category:/category/'.strtolower($slug)] = true;
                });
        }

        return $available;
    }

    public function contactSettings()
    {
        $settings = Setting::getAllAsArray();

        return view('backend.settings.contact', compact('settings'));
    }

    public function updateContactSettings(Request $request)
    {
        $map = trim((string) $request->input('contact_map_embed', ''));
        $map = html_entity_decode($map, ENT_QUOTES | ENT_HTML5);

        // Allow admins to paste either the embed URL or the complete iframe code.
        if ($map !== '' && preg_match('/<iframe[^>]*\bsrc\s*=\s*["\']([^"\']+)["\']/i', $map, $matches)) {
            $map = html_entity_decode(trim($matches[1]), ENT_QUOTES | ENT_HTML5);
        }

        $request->merge(['contact_map_embed' => $map]);

        $validated = $request->validate([
            'header_address' => 'nullable|string|max:1000',
            'header_phone' => 'nullable|string|max:50',
            'header_email' => 'nullable|email|max:255',
            'contact_working_hours' => 'nullable|string|max:1000',
            'contact_map_embed' => [
                'nullable',
                'string',
                'max:2000',
                function ($attribute, $value, $fail) {
                    if ($value === null || trim($value) === '') {
                        return;
                    }

                    $parts = parse_url(trim($value));
                    $scheme = strtolower((string) ($parts['scheme'] ?? ''));
                    $host = strtolower((string) ($parts['host'] ?? ''));
                    $path = (string) ($parts['path'] ?? '');

                    $validHost = in_array($host, ['www.google.com', 'google.com', 'maps.google.com'], true);
                    $validScheme = in_array($scheme, ['http', 'https'], true);

                    if (! $validScheme || ! $validHost || ! str_starts_with($path, '/maps')) {
                        $fail('Enter a valid Google Maps embed URL (the iframe src), or leave it blank.');
                    }
                },
            ],
        ]);

        foreach (['header_address', 'header_phone', 'header_email', 'contact_working_hours', 'contact_map_embed'] as $key) {
            Setting::set($key, (string) ($validated[$key] ?? ''));
        }

        return redirect()->route('admin.settings.contact')
            ->with('success', 'Contact information updated successfully.');
    }

    public function invoiceSettings()
    {
        $settings = Setting::getAllAsArray();
        $invoiceLogo = $settings['invoice_logo'] ?? config('invoice.logo');
        $invoiceLogoUrl = null;

        if ($invoiceLogo !== '') {
            $invoiceLogoUrl = str_starts_with($invoiceLogo, 'invoices/')
                ? asset('storage/'.$invoiceLogo)
                : asset(ltrim($invoiceLogo, '/'));
        }

        $defaults = config('invoice.settings', []);
        $invoiceSettings = [];
        foreach ($defaults as $key => $default) {
            $invoiceSettings[$key] = $settings[$key] ?? $default;
        }
        foreach (config('invoice.visible_sections', []) as $key) {
            $invoiceSettings[$key] = ($settings[$key] ?? '1') === '1';
        }

        return view('backend.settings.invoice', compact('invoiceLogoUrl', 'invoiceSettings'));
    }

    public function updateInvoiceSettings(Request $request)
    {
        $validated = $request->validate([
            'invoice_logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'remove_invoice_logo' => 'nullable|boolean',
            'invoice_business_name' => 'nullable|string|max:255',
            'invoice_business_address' => 'nullable|string|max:1000',
            'invoice_business_phone' => 'nullable|string|max:50',
            'invoice_business_email' => 'nullable|email|max:255',
            'invoice_business_tax_id' => 'nullable|string|max:100',
            'invoice_primary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'invoice_layout' => 'required|in:standard,compact',
            'invoice_prefix' => 'nullable|string|max:30',
            'invoice_date_format' => 'required|in:d M Y,M d Y,Y-m-d,d/m/Y',
            'invoice_title' => 'required|string|max:100',
            'invoice_note' => 'nullable|string|max:2000',
            'invoice_terms' => 'nullable|string|max:2000',
            'invoice_footer' => 'nullable|string|max:500',
            'invoice_currency_label' => 'nullable|string|max:10',
            'invoice_label_product' => 'required|string|max:50',
            'invoice_label_quantity' => 'required|string|max:50',
            'invoice_label_price' => 'required|string|max:50',
            'invoice_label_line_total' => 'required|string|max:50',
            'invoice_label_subtotal' => 'required|string|max:50',
            'invoice_label_shipping' => 'required|string|max:50',
            'invoice_label_tax' => 'required|string|max:50',
            'invoice_label_discount' => 'required|string|max:50',
            'invoice_label_total' => 'required|string|max:50',
            'invoice_show_business' => 'required|boolean',
            'invoice_show_billing' => 'required|boolean',
            'invoice_show_payment' => 'required|boolean',
            'invoice_show_delivery' => 'required|boolean',
            'invoice_show_items' => 'required|boolean',
            'invoice_show_subtotal' => 'required|boolean',
            'invoice_show_shipping' => 'required|boolean',
            'invoice_show_tax' => 'required|boolean',
            'invoice_show_discount' => 'required|boolean',
            'invoice_show_total' => 'required|boolean',
        ]);

        $currentLogo = Setting::get('invoice_logo');

        if ($request->hasFile('invoice_logo')) {
            $newLogo = $request->file('invoice_logo')->store('invoices', 'public');
            if (! $newLogo) {
                throw new \RuntimeException('The invoice logo could not be stored.');
            }

            Setting::set('invoice_logo', $newLogo);
            $this->deleteStoredInvoiceLogo($currentLogo);
        } elseif ($request->boolean('remove_invoice_logo')) {
            Setting::set('invoice_logo', '');
            $this->deleteStoredInvoiceLogo($currentLogo);
        }

        foreach (array_keys($validated) as $key) {
            if (in_array($key, ['invoice_logo', 'remove_invoice_logo'], true)) {
                continue;
            }

            $value = str_starts_with($key, 'invoice_show_')
                ? ($request->boolean($key) ? '1' : '0')
                : (string) ($validated[$key] ?? '');
            Setting::set($key, $value);
        }

        return redirect()->route('admin.settings.invoice')
            ->with('success', 'Invoice settings updated successfully.');
    }

    private function deleteStoredInvoiceLogo(?string $path): void
    {
        if ($path && str_starts_with($path, 'invoices/')) {
            Storage::disk('public')->delete($path);
        }
    }

    public function seoSettings()
    {
        $settings = Setting::getAllAsArray();
        $defaultRobots = renderRobotsTxt();

        if (trim((string) ($settings['seo_robots_txt'] ?? '')) !== '') {
            $settings['seo_robots_txt'] = renderRobotsTxt($settings['seo_robots_txt']);
        }

        return view('backend.settings.seo', compact('settings', 'defaultRobots'));
    }

    public function updateSeoSettings(Request $request)
    {
        $request->validate([
            'seo_site_title' => 'nullable|string|max:255',
            'seo_title_separator' => 'nullable|string|max:10',
            'seo_default_meta_title' => 'nullable|string|max:255',
            'seo_default_meta_description' => 'nullable|string|max:500',
            'seo_default_meta_keywords' => 'nullable|string|max:500',
            'seo_twitter_handle' => 'nullable|string|max:100',
            'seo_google_site_verification' => 'nullable|string|max:255',
            'seo_bing_site_verification' => 'nullable|string|max:255',
            'seo_pinterest_verification' => 'nullable|string|max:255',
            'seo_ga4_measurement_id' => 'nullable|string|max:100',
            'seo_gtm_container_id' => 'nullable|string|max:100',
            'seo_facebook_pixel_id' => 'nullable|string|max:100',
            'seo_robots_txt' => 'nullable|string|max:10000',
            'seo_default_og_image' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:4096',
        ]);

        if ($request->boolean('seo_indexing_enabled')
            && preg_match('/^\s*Disallow:\s*\/\s*$/mi', (string) $request->input('seo_robots_txt'))) {
            return back()
                ->withErrors(['seo_robots_txt' => 'robots.txt contains "Disallow: /" but search engine indexing is enabled. Remove that line or turn indexing off.'])
                ->withInput();
        }

        if ($request->has('seo_robots_txt')) {
            $robots = trim((string) $request->input('seo_robots_txt', ''));
            $request->merge([
                'seo_robots_txt' => $robots === '' ? '' : normalizeRobotsTxtForStorage($robots),
            ]);
        }

        $keys = [
            'seo_site_title',
            'seo_title_separator',
            'seo_default_meta_title',
            'seo_default_meta_description',
            'seo_default_meta_keywords',
            'seo_twitter_handle',
            'seo_google_site_verification',
            'seo_bing_site_verification',
            'seo_pinterest_verification',
            'seo_ga4_measurement_id',
            'seo_gtm_container_id',
            'seo_facebook_pixel_id',
            'seo_robots_txt',
        ];

        foreach ($keys as $key) {
            if ($request->has($key)) {
                Setting::set($key, (string) $request->input($key, ''));
            }
        }

        Setting::set('seo_indexing_enabled', $request->boolean('seo_indexing_enabled') ? '1' : '0');
        Setting::set('seo_sitemap_enabled', $request->boolean('seo_sitemap_enabled') ? '1' : '0');

        if ($request->filled('image_from_manager_seo_default_og_image')) {
            Setting::set('seo_default_og_image', 'storage/'.ltrim($request->input('image_from_manager_seo_default_og_image'), '/'));
            Image::markUsed($request->input('image_from_manager_seo_default_og_image'));
        } elseif ($request->hasFile('seo_default_og_image')) {
            $filename = saveImageWithWebp($request->file('seo_default_og_image'));
            Setting::set('seo_default_og_image', $filename);
        }

        if ($request->boolean('remove_seo_default_og_image')) {
            Setting::set('seo_default_og_image', '');
        }

        return redirect()->route('admin.settings.seo')->with('success', 'SEO settings updated successfully.');
    }

    public function logs(Request $request)
    {
        $directory = storage_path('logs/site-changes');
        $files = [];

        if (is_dir($directory)) {
            $files = array_values(array_filter(scandir($directory), function ($file) {
                return preg_match('/\.log$/', $file);
            }));
            rsort($files);
        }

        $selectedFile = $request->get('file', $files[0] ?? null);
        $contents = [];

        if ($selectedFile && is_file($directory.DIRECTORY_SEPARATOR.$selectedFile)) {
            $contents = file($directory.DIRECTORY_SEPARATOR.$selectedFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        }

        // Parse each JSON line into a structured entry; skip malformed lines.
        $entries = [];
        foreach ($contents as $line) {
            $decoded = json_decode(trim($line), true);
            if (! is_array($decoded)) {
                $entries[] = [
                    'timestamp' => null,
                    'type' => 'info',
                    'message' => $line,
                    'context' => [],
                ];

                continue;
            }
            $entries[] = [
                'timestamp' => $decoded['timestamp'] ?? null,
                'type' => $decoded['type'] ?? 'info',
                'message' => $decoded['message'] ?? '',
                'context' => $decoded['context'] ?? [],
            ];
        }

        // Newest first.
        $entries = array_reverse($entries);

        // Collect available types for the filter dropdown (all, before any filter).
        $types = array_values(array_unique(array_column($entries, 'type')));
        sort($types);

        // Search filter.
        $search = $request->get('search');
        if ($search) {
            $entries = array_filter($entries, function ($entry) use ($search) {
                return stripos($entry['message'], $search) !== false
                    || stripos((string) $entry['type'], $search) !== false
                    || stripos($entry['timestamp'] ?? '', $search) !== false;
            });
            $entries = array_values($entries);
        }

        // Filter by type (defaults to 'change' so the logs page shows the audit trail).
        $typeParam = $request->get('type');
        $typeFilter = $typeParam ?: 'change';
        if ($typeFilter && $typeFilter !== 'all') {
            $entries = array_filter($entries, fn ($entry) => $entry['type'] === $typeFilter);
            $entries = array_values($entries);
        }

        // Paginate.
        $perPage = $request->query('per_page', 20);
        $total = count($entries);
        if ($perPage === 'all') {
            $perPage = $total ?: 20;
        } else {
            $perPage = (int) $perPage;
            if (! in_array($perPage, [10, 20, 50, 100], true)) {
                $perPage = 20;
            }
        }

        $currentPage = Paginator::resolveCurrentPage();
        $pageEntries = array_slice($entries, ($currentPage - 1) * $perPage, $perPage);

        $entries = new LengthAwarePaginator(
            $pageEntries,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => Paginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]
        );

        return view('backend.logs.index', compact('files', 'selectedFile', 'entries', 'search', 'typeParam', 'typeFilter', 'types'));
    }

    public function logClear(string $file)
    {
        $file = $this->sanitizeLogFile($file);
        $directory = storage_path('logs/site-changes');
        $path = $directory.DIRECTORY_SEPARATOR.$file;

        if (! $file || ! is_file($path)) {
            return back()->with('error', 'Log file not found.');
        }

        $trashDir = $this->logTrashDir();
        if (! is_dir($trashDir)) {
            mkdir($trashDir, 0775, true);
        }

        $trashPath = $trashDir.DIRECTORY_SEPARATOR.$file;

        $i = 1;
        while (is_file($trashPath)) {
            $basename = pathinfo($file, PATHINFO_FILENAME);
            $trashPath = $trashDir.DIRECTORY_SEPARATOR.$basename.'_'.$i.'.log';
            $i++;
        }

        rename($path, $trashPath);

        return redirect()->route('admin.logs.index')
            ->with('success', 'Log file moved to trash. It will auto-delete after 15 days.');
    }

    protected function logTrashDir(): string
    {
        return storage_path('logs/site-changes-trash');
    }

    protected function sanitizeLogFile(string $file): ?string
    {
        $file = basename($file);
        if (! preg_match('/\.log$/', $file)) {
            return null;
        }

        return $file;
    }

    public function logsTrash()
    {
        $directory = $this->logTrashDir();
        $trashedFiles = [];

        if (is_dir($directory)) {
            $trashedFiles = array_values(array_filter(scandir($directory), function ($file) {
                return preg_match('/\.log$/', $file);
            }));
            rsort($trashedFiles);
        }

        return view('backend.logs.trash', compact('trashedFiles'));
    }

    public function logRestore(string $file)
    {
        $file = $this->sanitizeLogFile($file);
        $trashPath = $this->logTrashDir().DIRECTORY_SEPARATOR.$file;
        $destDir = storage_path('logs/site-changes');

        if (! $file || ! is_file($trashPath)) {
            return back()->with('error', 'Trashed log file not found.');
        }

        if (! is_dir($destDir)) {
            mkdir($destDir, 0775, true);
        }

        rename($trashPath, $destDir.DIRECTORY_SEPARATOR.$file);

        return redirect()->route('admin.logs.trash')
            ->with('success', 'Log file restored from trash.');
    }

    public function logForceDelete(string $file)
    {
        $file = $this->sanitizeLogFile($file);
        $trashPath = $this->logTrashDir().DIRECTORY_SEPARATOR.$file;

        if (! $file || ! is_file($trashPath)) {
            return back()->with('error', 'Trashed log file not found.');
        }

        @unlink($trashPath);

        return redirect()->route('admin.logs.trash')
            ->with('success', 'Trashed log file permanently deleted.');
    }

    public function logEmptyTrash()
    {
        $directory = $this->logTrashDir();
        $deleted = 0;

        if (is_dir($directory)) {
            foreach (scandir($directory) as $file) {
                if (preg_match('/\.log$/', $file)) {
                    @unlink($directory.DIRECTORY_SEPARATOR.$file);
                    $deleted++;
                }
            }
        }

        return redirect()->route('admin.logs.trash')
            ->with('success', "Log trash emptied ({$deleted} file(s) permanently deleted).");
    }

    public function revisions(Request $request)
    {
        $sortBy = in_array($request->sort_by, ['id', 'created_at', 'action', 'model_type']) ? $request->sort_by : 'created_at';
        $sortDir = $request->sort_dir === 'asc' ? 'asc' : 'desc';
        $perPage = in_array((int) $request->per_page, [10, 20, 50, 100]) ? (int) $request->per_page : 20;
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $filterModelType = $request->query('model_type');
        $filterModelId = $request->query('model_id');
        $trashed = $request->has('trashed');

        $revisions = Revision::with('user');
        if ($request->has('trashed')) {
            $revisions->onlyTrashed();
        }
        if ($dateFrom) {
            $revisions->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $revisions->whereDate('created_at', '<=', $dateTo);
        }
        if ($filterModelType) {
            $revisions->where('model_type', $filterModelType);
        }
        if ($filterModelId) {
            $revisions->where('model_id', (int) $filterModelId);
        }

        $revisions = $revisions->orderBy($sortBy, $sortDir)
            ->paginate($perPage)
            ->appends($request->query())
            ->onEachSide(1);

        return view('backend.revisions.index', compact('revisions', 'sortBy', 'sortDir', 'filterModelType', 'filterModelId', 'trashed'));
    }

    public function revisionDetail($id)
    {
        $rev = Revision::with('user')->findOrFail($id);

        return view('backend.revisions.detail', compact('rev'));
    }

    public function revisionDestroy($id)
    {
        Revision::findOrFail($id)->delete();

        return redirect()->route('admin.revisions.index')
            ->with('success', 'Revision moved to trash. It will be auto-deleted after 15 days.');
    }

    public function revisionBulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', 'No revisions selected.');
        }

        $deleted = Revision::whereIn('id', $ids)->delete();

        return redirect()->route('admin.revisions.index')
            ->with('success', "Moved {$deleted} revision(s) to trash. They will auto-delete after 15 days.");
    }

    public function revisionRestore($id)
    {
        Revision::onlyTrashed()->findOrFail($id)->restore();

        return redirect()->route('admin.revisions.index', ['trashed' => 1])
            ->with('success', 'Revision restored from trash.');
    }

    public function revisionForceDelete(Request $request, $id)
    {
        $rev = Revision::onlyTrashed()->findOrFail($id);
        $rev->forceDelete();

        return redirect()->route('admin.revisions.index', ['trashed' => 1])
            ->with('success', 'Revision permanently deleted.');
    }

    public function revisionBulkRestore(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', 'No revisions selected.');
        }

        $restored = Revision::onlyTrashed()->whereIn('id', $ids)->restore();

        return redirect()->route('admin.revisions.index', ['trashed' => 1])
            ->with('success', "Restored {$restored} revision(s) from trash.");
    }

    public function revisionBulkForceDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', 'No revisions selected.');
        }

        $deleted = Revision::onlyTrashed()->whereIn('id', $ids)->forceDelete();

        return redirect()->route('admin.revisions.index', ['trashed' => 1])
            ->with('success', "Permanently deleted {$deleted} revision(s).");
    }

    public function revisionEmptyTrash(Request $request)
    {
        $deleted = Revision::onlyTrashed()->forceDelete();

        return redirect()->route('admin.revisions.index', ['trashed' => 1])
            ->with('success', "Trash emptied ({$deleted} revision(s) permanently deleted).");
    }

    public function fileRevisions(Request $request)
    {
        $sortBy = in_array($request->sort_by, ['id', 'created_at', 'event', 'file_path']) ? $request->sort_by : 'created_at';
        $sortDir = $request->sort_dir === 'asc' ? 'asc' : 'desc';
        $perPage = in_array((int) $request->per_page, [10, 20, 50, 100]) ? (int) $request->per_page : 20;
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $trashed = $request->has('trashed');

        $fileRevisions = FileRevision::with('user');
        if ($trashed) {
            $fileRevisions->onlyTrashed();
        }
        if ($dateFrom) {
            $fileRevisions->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $fileRevisions->whereDate('created_at', '<=', $dateTo);
        }

        $fileRevisions = $fileRevisions->orderBy($sortBy, $sortDir)
            ->paginate($perPage)
            ->appends($request->query())
            ->onEachSide(1);

        return view('backend.file-revisions.index', compact('fileRevisions', 'sortBy', 'sortDir', 'trashed'));
    }

    public function fileRevisionDownload($id)
    {
        $rev = FileRevision::findOrFail($id);
        if (! $rev->backup_path) {
            return abort(404);
        }
        $path = storage_path('file-backups/archive/'.$rev->backup_path);
        if (! file_exists($path)) {
            return abort(404, 'Backup file not found.');
        }

        return response()->download($path, basename($rev->file_path).'.bak');
    }

    public function fileRevisionDiff($id)
    {
        $rev = FileRevision::with('user')->findOrFail($id);

        return view('backend.file-revisions.diff', compact('rev'));
    }

    public function updateHeaderSettings(Request $request)
    {
        $keys = [
            'header_logo', 'header_phone', 'header_support_text', 'header_email',
            'header_address', 'footer_copyright', 'header_favicon', 'mobile_logo', 'footer_logo',
            'nav_menu',
        ];

        $imageKeys = ['header_logo', 'header_favicon', 'mobile_logo', 'footer_logo', 'admin_header_bg'];

        foreach ($keys as $key) {
            $managerKey = 'image_from_manager_'.$key;
            if (in_array($key, $imageKeys) && $request->filled($managerKey)) {
                Setting::set($key, 'storage/'.ltrim($request->input($managerKey), '/'));
                Image::markUsed($request->input($managerKey));
            } elseif ($request->hasFile($key)) {
                $filename = saveImageWithWebp($request->file($key));
                Setting::set($key, $filename);
            } elseif ($request->has($key)) {
                Setting::set($key, $request->input($key));
            }
        }

        if ($request->boolean('remove_admin_header_bg')) {
            Setting::set('admin_header_bg', '');
        }

        Setting::set('webp_frontend', $request->boolean('webp_frontend') ? '1' : '0');

        return redirect()->route('admin.settings.header')
            ->with('success', 'Header settings updated successfully.')
            ->with('settings_tab', 'header');
    }

    public function commissionSettings()
    {
        $commissionPercent = Setting::get('global_commission_percent', 0);

        return view('backend.settings.commission', compact('commissionPercent'));
    }

    public function updateCommissionSettings(Request $request)
    {
        $validated = $request->validate([
            'commission_percent' => 'required|numeric|min:0|max:100',
        ]);

        $oldCommission = number_format((float) Setting::get('global_commission_percent', 0), 2, '.', '');
        $newCommission = number_format((float) $validated['commission_percent'], 2, '.', '');

        DB::transaction(function () use ($oldCommission, $newCommission) {
            Setting::set('global_commission_percent', $newCommission);

            if ($oldCommission !== $newCommission) {
                CommissionHistory::recordChange('global', null, $oldCommission, $newCommission);
            }
        });

        Cache::forget('pricing.global_commission_percent');

        return redirect()->route('admin.settings.commission')
            ->with('success', 'Global buyer commission updated successfully.');
    }

    public function stockSyncSettings()
    {
        $settings = StockSync::settings();

        return view('backend.settings.stock-sync', [
            'settings' => $settings,
            'configured' => StockSync::configured($settings),
        ]);
    }

    public function updateStockSyncSettings(Request $request)
    {
        $validated = $request->validate([
            'stock_feed_url' => 'nullable|url|max:2048',
            'stock_feed_token' => 'nullable|string|max:2048',
            'stock_feed_format' => 'required|in:json,csv',
            'stock_feed_items_path' => 'nullable|string|max:255',
            'stock_feed_key_field' => 'required|in:part_number,id,slug',
            'stock_feed_stock_field' => 'required|string|max:100',
            'stock_sync_frequency' => 'required|in:manual,hourly,daily',
            'stock_sync_enabled' => 'nullable|boolean',
            'low_stock_threshold' => 'required|integer|min:0|max:100000',
        ]);

        $url = trim((string) ($validated['stock_feed_url'] ?? ''));
        if ($request->boolean('stock_sync_enabled') && $url === '') {
            return back()
                ->withErrors(['stock_feed_url' => 'A feed URL is required when supplier sync is enabled.'])
                ->withInput();
        }

        $token = trim((string) ($validated['stock_feed_token'] ?? ''));
        if ($token === '') {
            $token = (string) Setting::get('stock_feed_token', '');
        }

        $enabled = $request->boolean('stock_sync_enabled');

        DB::transaction(function () use ($validated, $url, $token, $enabled) {
            Setting::set('stock_feed_url', $url);
            Setting::set('stock_feed_token', $token);
            Setting::set('stock_feed_format', $validated['stock_feed_format']);
            Setting::set('stock_feed_items_path', trim((string) ($validated['stock_feed_items_path'] ?? '')));
            Setting::set('stock_feed_key_field', $validated['stock_feed_key_field']);
            Setting::set('stock_feed_stock_field', trim((string) $validated['stock_feed_stock_field']));
            Setting::set('stock_sync_frequency', $validated['stock_sync_frequency']);
            Setting::set('stock_sync_enabled', $enabled ? '1' : '0');
            Setting::set('low_stock_threshold', (string) (int) $validated['low_stock_threshold']);
        });

        return redirect()->route('admin.settings.stock-sync')
            ->with('success', 'Stock sync settings updated successfully.');
    }

    public function runStockSync()
    {
        $settings = StockSync::settings();
        if (! StockSync::configured($settings)) {
            return back()->with('error', 'Stock sync is not configured. Add a feed URL and enable it first.');
        }

        SyncProductStockJob::dispatch();

        return back()->with('success', 'Stock sync has been queued. Check the last sync result shortly.');
    }

    public function updateFooterSettings(Request $request)
    {
        $data = json_decode((string) $request->input('footer_columns', '[]'), true);

        if (! is_array($data)) {
            return back()
                ->with('error', 'Invalid footer columns data.')
                ->with('settings_tab', 'footer');
        }

        $allowedTypes = ['links', 'newsletter', 'contact'];
        $allowedSpans = [2, 3, 4, 6, 12];

        $columns = [];
        foreach ($data as $col) {
            if (! is_array($col)) {
                continue;
            }

            $type = in_array($col['type'] ?? '', $allowedTypes) ? $col['type'] : 'links';
            $span = in_array((int) ($col['span'] ?? 2), $allowedSpans) ? (int) $col['span'] : 2;

            $links = [];
            foreach (($col['links'] ?? []) as $link) {
                if (! is_array($link)) {
                    continue;
                }
                if ($type === 'newsletter') {
                    $platform = trim((string) ($link['platform'] ?? ''));
                    $url = trim((string) ($link['url'] ?? ''));
                    if ($url === '') {
                        continue;
                    }
                    $links[] = ['platform' => $platform, 'url' => $url];
                } else {
                    $label = trim((string) ($link['label'] ?? ''));
                    $url = trim((string) ($link['url'] ?? ''));
                    if ($label === '' && $url === '') {
                        continue;
                    }
                    $links[] = ['label' => $label, 'url' => $url];
                }
            }

            $columns[] = [
                'type' => $type,
                'heading' => trim((string) ($col['heading'] ?? '')),
                'span' => $span,
                'links' => $links,
            ];
        }

        Setting::set('footer_columns', json_encode($columns));

        return redirect()->route('admin.settings.header')
            ->with('success', 'Footer settings updated successfully.')
            ->with('settings_tab', 'footer');
    }

    public function searchCategorySettings()
    {
        $settings = Setting::getAllAsArray();

        $selected = json_decode((string) ($settings['search_category_menu'] ?? '[]'), true);
        if (! is_array($selected)) {
            $selected = [];
        }
        $normalizedSelected = [];
        $seen = [];
        foreach ($selected as $item) {
            if (! is_array($item)) {
                continue;
            }
            $label = trim((string) ($item['label'] ?? ''));
            $url = normalizeAppUrlPath((string) ($item['url'] ?? ''));
            $key = mb_strtolower($label);
            if ($label === '' || $url === '' || isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $normalizedSelected[] = ['label' => $label, 'url' => $url];
        }
        $selected = $normalizedSelected;
        $selectedLabels = collect($normalizedSelected)->pluck('label');

        return view('backend.settings.search-categories', compact('settings', 'selected', 'selectedLabels'));
    }

    /**
     * Paginated category picker for the Search Categories settings page.
     * Replaces rendering all 21,300 categories into the page HTML.
     */
    public function searchCategoryOptions(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $page = max(1, (int) $request->query('page', 1));
        $perPage = 20;

        $query = Category::query();
        if ($q !== '') {
            $query->where('name', 'like', '%'.$q.'%');
        }
        $query->orderBy('name')->orderBy('id');

        $paginator = $query->paginate($perPage, ['id', 'name', 'slug', 'parent_id'], 'page', $page);
        if ($paginator->lastPage() > 0 && $paginator->currentPage() > $paginator->lastPage()) {
            $paginator = $query->paginate($perPage, ['id', 'name', 'slug', 'parent_id'], 'page', $paginator->lastPage());
        }

        $parentIds = $paginator->getCollection()->pluck('parent_id')->filter()->unique()->all();
        $parents = $parentIds
            ? Category::whereIn('id', $parentIds)->pluck('name', 'id')
            : collect();

        $items = $paginator->getCollection()->map(function ($category) use ($parents) {
            $parentName = $parents->get($category->parent_id);

            return [
                'id' => $category->id,
                'label' => (string) $category->name,
                'url' => \App\Support\CategoryRef::url($category->id, $category->slug, [], false),
                'context' => $parentName ? 'in '.$parentName : '',
            ];
        })->values()->all();

        return response()->json([
            'data' => $items,
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ]);
    }

    public function updateSearchCategorySettings(Request $request)
    {
        $items = json_decode((string) $request->input('search_category_menu', '[]'), true);
        if (! is_array($items)) {
            return back()->with('error', 'Invalid search categories data.');
        }

        $searchCategories = [];
        $seenKeys = [];
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }
            $label = trim((string) ($item['label'] ?? ''));
            $url = trim((string) ($item['url'] ?? ''));
            if ($label === '' || $url === '') {
                continue;
            }
            if (preg_match('#^https?://#i', $url) && ! filter_var($url, FILTER_VALIDATE_URL)) {
                continue;
            }
            $normalizedUrl = normalizeAppUrlPath($url);
            if ($normalizedUrl === '') {
                continue;
            }
            $key = mb_strtolower($label);
            if (isset($seenKeys[$key])) {
                continue;
            }
            $seenKeys[$key] = true;
            $searchCategories[] = ['label' => $label, 'url' => $normalizedUrl];
        }

        Setting::set('search_category_menu', json_encode($searchCategories));

        return redirect()->route('admin.settings.search-categories')->with('success', 'Search categories updated successfully.');
    }

    public function fileRevisionTruncateDiffs(Request $request)
    {
        $olderThanDays = (int) $request->input('older_than_days', 90);
        $trimmed = FileRevision::truncateDiffs($olderThanDays);

        return redirect()->route('admin.file-revisions.index')
            ->with('success', "Truncated diff data on {$trimmed} old revision(s).");
    }

    public function fileRevisionTruncatePerFile(Request $request)
    {
        $keepPerFile = (int) $request->input('keep_per_file', 50);
        FileRevision::truncatePerFileLimit($keepPerFile);

        return redirect()->route('admin.file-revisions.index')
            ->with('success', "Per-file limit applied: kept last {$keepPerFile} revisions per file. Older diffs truncated.");
    }

    public function fileRevisionDestroy($id)
    {
        FileRevision::findOrFail($id)->delete();

        return redirect()->route('admin.file-revisions.index')
            ->with('success', 'File revision moved to trash. It will auto-delete after 15 days.');
    }

    public function fileRevisionBulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', 'No file revisions selected.');
        }

        $deleted = FileRevision::whereIn('id', $ids)->delete();

        return redirect()->route('admin.file-revisions.index')
            ->with('success', "Moved {$deleted} file revision(s) to trash. They will auto-delete after 15 days.");
    }

    public function fileRevisionRestore($id)
    {
        FileRevision::onlyTrashed()->findOrFail($id)->restore();

        return redirect()->route('admin.file-revisions.index', ['trashed' => 1])
            ->with('success', 'File revision restored from trash.');
    }

    public function fileRevisionForceDelete($id)
    {
        $rev = FileRevision::onlyTrashed()->findOrFail($id);

        if ($rev->backup_path) {
            $path = storage_path('file-backups/archive/'.$rev->backup_path);
            if (is_file($path)) {
                @unlink($path);
            }
        }

        $rev->forceDelete();

        return redirect()->route('admin.file-revisions.index', ['trashed' => 1])
            ->with('success', 'File revision permanently deleted.');
    }

    public function fileRevisionBulkRestore(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', 'No file revisions selected.');
        }

        $restored = FileRevision::onlyTrashed()->whereIn('id', $ids)->restore();

        return redirect()->route('admin.file-revisions.index', ['trashed' => 1])
            ->with('success', "Restored {$restored} file revision(s) from trash.");
    }

    public function fileRevisionBulkForceDelete(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', 'No file revisions selected.');
        }

        $revs = FileRevision::onlyTrashed()->whereIn('id', $ids)->get();

        $deleted = 0;
        foreach ($revs as $rev) {
            if ($rev->backup_path) {
                $path = storage_path('file-backups/archive/'.$rev->backup_path);
                if (is_file($path)) {
                    @unlink($path);
                }
            }
            $rev->forceDelete();
            $deleted++;
        }

        return redirect()->route('admin.file-revisions.index', ['trashed' => 1])
            ->with('success', "Permanently deleted {$deleted} file revision(s).");
    }

    public function fileRevisionEmptyTrash(Request $request)
    {
        $revs = FileRevision::onlyTrashed()->get();

        $deleted = 0;
        foreach ($revs as $rev) {
            if ($rev->backup_path) {
                $path = storage_path('file-backups/archive/'.$rev->backup_path);
                if (is_file($path)) {
                    @unlink($path);
                }
            }
            $rev->forceDelete();
            $deleted++;
        }

        return redirect()->route('admin.file-revisions.index', ['trashed' => 1])
            ->with('success', "Trash emptied ({$deleted} file revision(s) permanently deleted).");
    }
}
