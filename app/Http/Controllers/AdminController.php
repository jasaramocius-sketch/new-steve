<?php

namespace App\Http\Controllers;

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
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function index()
    {
        $topProducts = DB::table('order_items')
            ->select('product_id', DB::raw('SUM(qty) as total_qty'))
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        if ($topProducts->isNotEmpty()) {
            $products = Product::whereIn('id', $topProducts->pluck('product_id'))->get()->keyBy('id');
            $topProducts = $topProducts->map(fn ($item) => tap($item, fn ($i) => $i->product = $products->get($i->product_id)));
        }

        $ordersByStatus = Order::selectRaw('status, COUNT(*) as count')->groupBy('status')->get();

        $dbRevenue = Order::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total_amount) as total")->groupBy('month')->orderBy('month')->take(12)->pluck('total', 'month');

        $monthlyRevenueData = collect();
        for ($i = 11; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $monthlyRevenueData->put($key, (float) ($dbRevenue->get($key) ?? 0));
        }

        $weeklyRevenue = Order::selectRaw('YEARWEEK(created_at, 1) as week, SUM(total_amount) as total')
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

        $dailyRevenue = Order::selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date');

        $dailyRevenueData = collect();
        for ($i = 29; $i >= 0; $i--) {
            $key = now()->subDays($i)->format('Y-m-d');
            $dailyRevenueData->put($key, (float) ($dailyRevenue->get($key) ?? 0));
        }

        $hourlyRevenue = Order::selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00') as hour, SUM(total_amount) as total")
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

        $fiveMinRevenue = Order::selectRaw('
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

        return view('admin.dashboard', [
            'totalOrders' => Order::count(),
            'totalRevenue' => Order::sum('total_amount'),
            'totalProducts' => Product::count(),
            'totalCustomers' => User::where('role', 'customer')->count(),
            'pendingOrders' => Order::where('status', 'pending')->count(),
            'recentOrders' => Order::with('user')->latest()->take(5)->get(),
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

        return view('admin.profile', compact('user'));
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
            'postal_code' => 'nullable|string|max:255|regex:/^[A-Za-z0-9\-\s]{3,20}$/',
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
            $hourlyRevenue = Order::selectRaw("DATE_FORMAT(created_at, '%Y-%m-%d %H:00:00') as hour, SUM(total_amount) as total")
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

        $todayRevenue = Order::selectRaw('
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
        return view('admin.settings.custom', compact('settings'));
    }

    public function orderSettings()
    {
        $settings = Setting::getAllAsArray();

        return view('admin.settings.order', compact('settings'));
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
                'icon' => 'las la-sliders-h',
                'items' => [
                    ['name' => 'General Settings', 'description' => 'Site name, logo, timezone', 'icon' => 'las la-cog', 'route' => 'admin.settings.header'],
                    ['name' => 'Features activation', 'description' => 'Turn store features on or off', 'icon' => 'las la-toggle-on', 'route' => 'admin.settings.features'],
                    ['name' => 'Order Configuration', 'description' => 'Minimum order amount', 'icon' => 'las la-shopping-bag', 'route' => 'admin.settings.order'],
                    ['name' => 'File System & Cache', 'description' => 'Storage driver and caching', 'icon' => 'las la-hdd'],
                ],
            ],
            [
                'title' => 'Localization',
                'icon' => 'las la-globe',
                'items' => [
                    ['name' => 'Languages', 'description' => 'Site languages and translations', 'icon' => 'las la-language', 'route' => 'admin.settings.languages.index'],
                    ['name' => 'Currency', 'description' => 'Currencies and exchange rates', 'icon' => 'las la-coins', 'route' => 'admin.settings.currency.index'],
                    ['name' => 'Vat & TAX', 'description' => 'Tax rules applied to products', 'icon' => 'las la-percent', 'route' => 'admin.settings.custom'],
                ],
            ],
            [
                'title' => 'Payment',
                'icon' => 'las la-credit-card',
                'items' => [
                    ['name' => 'Payment Methods', 'description' => 'Gateway keys and credentials', 'icon' => 'las la-credit-card', 'route' => 'admin.settings.payment-methods'],
                ],
            ],
            [
                'title' => 'Shipping',
                'icon' => 'las la-truck',
                'items' => [
                    ['name' => 'Shipping Configuration', 'description' => 'Shipping methods and costs', 'icon' => 'las la-truck-loading', 'route' => 'admin.api-integrations.index'],
                    ['name' => 'Pickup point', 'description' => 'Collection locations', 'icon' => 'las la-store', 'route' => 'admin.settings.pickup-points.index'],
                    ['name' => 'Shipping Countries', 'description' => 'Countries you ship to', 'icon' => 'las la-flag'],
                    ['name' => 'Shipping States', 'description' => 'States within countries', 'icon' => 'las la-map'],
                    ['name' => 'Shipping Cities', 'description' => 'Cities within states', 'icon' => 'las la-city'],
                    ['name' => 'Shipping Zones', 'description' => 'Zone-based shipping', 'icon' => 'las la-map-marked'],
                    ['name' => 'Shipping Carrier', 'description' => 'Carriers and transit times', 'icon' => 'las la-shipping-fast', 'route' => 'admin.api-integrations.index'],
                ],
            ],
            [
                'title' => 'Email & Social',
                'icon' => 'las la-envelope',
                'items' => [
                    ['name' => 'SMTP Settings', 'description' => 'Outgoing mail server', 'icon' => 'las la-envelope', 'route' => 'admin.settings.smtp'],
                    ['name' => 'Social media Logins', 'description' => 'Google and Facebook sign-in', 'icon' => 'las la-users'],
                    ['name' => 'Facebook Chat', 'description' => 'Live chat widget', 'icon' => 'lab la-facebook'],
                    ['name' => 'Facebook Comment', 'description' => 'Comments on product pages', 'icon' => 'las la-comments'],
                ],
            ],
            [
                'title' => 'Google',
                'icon' => 'lab la-google',
                'items' => [
                    ['name' => 'Analytics Tools', 'description' => 'Analytics and pixel tracking', 'icon' => 'las la-chart-line'],
                    ['name' => 'Google reCAPTCHA', 'description' => 'Spam protection on forms', 'icon' => 'las la-shield-alt'],
                    ['name' => 'Google Map', 'description' => 'Map API key', 'icon' => 'las la-map-marker'],
                    ['name' => 'Google Firebase', 'description' => 'Push notification setup', 'icon' => 'las la-fire'],
                ],
            ],
        ];

        return view('admin.settings.index', compact('settingsGroups'));
    }

    public function featureSettings()
    {
        $settingsGroups = $this->featureActivationGroups();
        $storedSettings = Setting::getAllAsArray();

        foreach ($settingsGroups as &$group) {
            foreach ($group['items'] as &$item) {
                $storedValue = $storedSettings[$item['key']] ?? ($item['default'] ? '1' : '0');
                $item['enabled'] = filter_var($storedValue, FILTER_VALIDATE_BOOLEAN);
            }
            unset($item);
        }
        unset($group);

        return view('admin.settings.features', compact('settingsGroups'));
    }

    public function updateFeatureSetting(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string',
            'enabled' => 'required|boolean',
        ]);

        $allowedKeys = [];
        foreach ($this->featureActivationGroups() as $group) {
            $allowedKeys = array_merge($allowedKeys, array_column($group['items'], 'key'));
        }

        if (! in_array($validated['key'], $allowedKeys, true)) {
            return response()->json(['message' => 'Unknown feature setting.'], 422);
        }

        $enabled = $request->boolean('enabled');
        Setting::set($validated['key'], $enabled ? '1' : '0');

        if ($validated['key'] === 'disable_image_optimization') {
            Setting::set('webp_frontend', $enabled ? '0' : '1');
        }

        return response()->json(['success' => true, 'enabled' => $enabled]);
    }

    private function featureActivationGroups(): array
    {
        return [
            [
                'title' => 'System',
                'items' => [
                    ['key' => 'FORCE_HTTPS', 'label' => 'HTTPS Activation', 'default' => false],
                    ['key' => 'maintenance_mode', 'label' => 'Maintenance Mode Activation', 'default' => false],
                    ['key' => 'disable_image_optimization', 'label' => 'Disable image encoding?', 'default' => false],
                ],
            ],
            [
                'title' => 'Business Related',
                'items' => [
                    ['key' => 'vendor_system_activation', 'label' => 'Vendor System Activation', 'default' => false],
                    ['key' => 'classified_product', 'label' => 'Classified Product', 'default' => true],
                    ['key' => 'wallet_system', 'label' => 'Wallet System Activation', 'default' => false],
                    ['key' => 'coupon_system', 'label' => 'Coupon System Activation', 'default' => true],
                    ['key' => 'pickup_point', 'label' => 'Pickup Point Activation', 'default' => true],
                    ['key' => 'conversation_system', 'label' => 'Conversation Activation', 'default' => true],
                    ['key' => 'product_manage_by_admin', 'label' => 'Seller Product Manage By Admin', 'default' => false, 'description' => 'When enabled, COD for seller products is managed by the admin.'],
                    ['key' => 'product_approve_by_admin', 'label' => 'Admin Approval On Seller Product', 'default' => false, 'description' => 'Seller products require admin approval.'],
                    ['key' => 'email_verification', 'label' => 'Email Verification', 'default' => false, 'configure' => 'admin.settings.smtp', 'configureLabel' => 'Configure SMTP'],
                    ['key' => 'product_query_activation', 'label' => 'Product Query Activation', 'default' => false],
                ],
            ],
            [
                'title' => 'Payment Related',
                'items' => [
                    ['key' => 'paypal_payment', 'label' => 'PayPal Payment Activation', 'default' => false, 'configure' => 'admin.api-integrations.index'],
                    ['key' => 'stripe_payment', 'label' => 'Stripe Payment Activation', 'default' => true, 'configure' => 'admin.api-integrations.index'],
                    ['key' => 'mercadopago_payment', 'label' => 'MercadoPago Payment Activation', 'default' => false, 'configure' => 'admin.api-integrations.index'],
                    ['key' => 'sslcommerz_payment', 'label' => 'SSlCommerz Activation', 'default' => false, 'configure' => 'admin.api-integrations.index'],
                    ['key' => 'instamojo_payment', 'label' => 'Instamojo Payment Activation', 'default' => false, 'configure' => 'admin.api-integrations.index'],
                    ['key' => 'razorpay', 'label' => 'Razor Pay Activation', 'default' => false, 'configure' => 'admin.api-integrations.index'],
                    ['key' => 'paystack', 'label' => 'PayStack Activation', 'default' => false, 'configure' => 'admin.api-integrations.index'],
                    ['key' => 'voguepay', 'label' => 'VoguePay Activation', 'default' => false, 'configure' => 'admin.api-integrations.index'],
                    ['key' => 'payhere', 'label' => 'Payhere Activation', 'default' => false, 'configure' => 'admin.api-integrations.index'],
                    ['key' => 'ngenius', 'label' => 'Ngenius Activation', 'default' => false, 'configure' => 'admin.api-integrations.index'],
                    ['key' => 'iyzico', 'label' => 'Iyzico Activation', 'default' => false, 'configure' => 'admin.api-integrations.index'],
                    ['key' => 'bkash', 'label' => 'Bkash Activation', 'default' => false, 'configure' => 'admin.api-integrations.index'],
                    ['key' => 'nagad', 'label' => 'Nagad Activation', 'default' => false, 'configure' => 'admin.api-integrations.index'],
                    ['key' => 'proxypay', 'label' => 'Proxy Pay Activation', 'default' => true, 'configure' => 'admin.api-integrations.index'],
                    ['key' => 'aamarpay', 'label' => 'Amarpay Activation', 'default' => false, 'configure' => 'admin.api-integrations.index'],
                    ['key' => 'authorizenet', 'label' => 'Authorize Net Activation', 'default' => false, 'configure' => 'admin.api-integrations.index'],
                    ['key' => 'payku', 'label' => 'Payku Activation', 'default' => false, 'configure' => 'admin.api-integrations.index'],
                    ['key' => 'cash_payment', 'label' => 'Cash Payment Activation', 'default' => true],
                ],
            ],
            [
                'title' => 'Social Media Login',
                'items' => [
                    ['key' => 'facebook_login', 'label' => 'Facebook login', 'default' => false],
                    ['key' => 'google_login', 'label' => 'Google login', 'default' => false],
                    ['key' => 'twitter_login', 'label' => 'Twitter login', 'default' => false],
                    ['key' => 'apple_login', 'label' => 'Apple login', 'default' => false],
                ],
            ],
        ];
    }

    public function updateCustomSettings(Request $request)
    {
        $request->validate([
            'tax_rate' => 'required|numeric|min:0|max:100',
        ]);
        foreach ($request->except('_token') as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        return redirect()->route('admin.settings.custom')->with('success', 'Custom settings updated.');
    }

    public function headerSettings()
    {
        $settings = Setting::getAllAsArray();
        $categories = Category::with('children')->orderBy('name')->get();
        $pages = Page::where('status', true)
            ->select('id', 'title', 'slug')
            ->orderBy('title')->get();
        $posts = Blog::where('status', 'published')
            ->select('id', 'title', 'slug')
            ->orderBy('title')->get();

        return view('admin.settings.header', compact('settings', 'categories', 'pages', 'posts'));
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
        $perPage = 50;
        $currentPage = Paginator::resolveCurrentPage();
        $total = count($entries);
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

        return view('admin.logs.index', compact('files', 'selectedFile', 'entries', 'search', 'typeParam', 'typeFilter', 'types'));
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

        return view('admin.logs.trash', compact('trashedFiles'));
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

        return view('admin.revisions.index', compact('revisions', 'sortBy', 'sortDir', 'filterModelType', 'filterModelId', 'trashed'));
    }

    public function revisionDetail($id)
    {
        $rev = Revision::with('user')->findOrFail($id);

        return view('admin.revisions.detail', compact('rev'));
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

        return view('admin.file-revisions.index', compact('fileRevisions', 'sortBy', 'sortDir', 'trashed'));
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

        return view('admin.file-revisions.diff', compact('rev'));
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

        return redirect()->route('admin.settings.header')->with('success', 'Header settings updated successfully.');
    }

    public function footerSettings()
    {
        $settings = Setting::getAllAsArray();

        return view('admin.settings.footer', compact('settings'));
    }

    public function commissionSettings()
    {
        $commissionPercent = Setting::get('global_commission_percent', 0);

        return view('admin.settings.commission', compact('commissionPercent'));
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

    public function updateFooterSettings(Request $request)
    {
        $data = json_decode((string) $request->input('footer_columns', '[]'), true);

        if (! is_array($data)) {
            return back()->with('error', 'Invalid footer columns data.');
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

        return redirect()->route('admin.settings.footer')->with('success', 'Footer settings updated successfully.');
    }

    public function searchCategorySettings()
    {
        $settings = Setting::getAllAsArray();
        $categories = Category::searchCategoryTree();
        $orphanCategories = Category::with('childrenRecursive')
            ->whereNotNull('parent_id')
            ->whereNotIn('parent_id', function ($query) {
                $query->select('id')->from('categories')->whereNull('deleted_at');
            })
            ->orderBy('name')
            ->get()
            ->unique('id')
            ->values();

        $selected = json_decode((string) ($settings['search_category_menu'] ?? '[]'), true);
        if (! is_array($selected)) {
            $selected = [];
        }
        $appBase = rtrim((string) config('app.url'), '/');
        $normalizedSelected = [];
        $seen = [];
        foreach ($selected as $item) {
            if (! is_array($item)) {
                continue;
            }
            $label = trim((string) ($item['label'] ?? ''));
            $url = trim((string) ($item['url'] ?? ''));
            $key = mb_strtolower($label);
            if ($label === '' || $url === '' || isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            if (preg_match('#^https?://[^/]+(/.*)$#', $url, $m)) {
                $url = $appBase . $m[1];
            }
            $normalizedSelected[] = ['label' => $label, 'url' => $url];
        }
        $selected = $normalizedSelected;
        $selectedLabels = collect($normalizedSelected)->pluck('label');

        return view('admin.settings.search-categories', compact('settings', 'categories', 'selected', 'selectedLabels', 'orphanCategories'));
    }

    public function updateSearchCategorySettings(Request $request)
    {
        $items = json_decode((string) $request->input('search_category_menu', '[]'), true);
        if (! is_array($items)) {
            return back()->with('error', 'Invalid search categories data.');
        }

        $appBase = rtrim((string) config('app.url'), '/');
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
            if (! filter_var($url, FILTER_VALIDATE_URL)) {
                continue;
            }
            if (str_starts_with($url, $appBase)) {
                $normalizedUrl = $url;
            } elseif (preg_match('#^https?://[^/]+(/.*)$#', $url, $m)) {
                $normalizedUrl = $appBase . $m[1];
            } else {
                $normalizedUrl = $appBase . '/' . ltrim($url, '/');
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
