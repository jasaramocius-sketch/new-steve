<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Contact;
use App\Models\FollowedSeller;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $ordersByStatus = Order::selectRaw('status, COUNT(*) as count')->groupBy('status')->get();

        $dbRevenue = Order::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(total_amount) as total")->groupBy('month')->orderBy('month')->take(12)->pluck('total', 'month');

        $monthlyRevenueData = collect();
        for ($i = 11; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $monthlyRevenueData->put($key, (float) ($dbRevenue->get($key) ?? 0));
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
            'topProducts' => collect(),
            'user' => $user,
        ]);
    }

    public function __construct()
    {
        // Intentionally left blank; data should be loaded from the database.
    }

    // -------------------------------------------------------------
    // User Dashboard
    // -------------------------------------------------------------
    public function userDashboard()
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to access your dashboard.');
        }

        if (in_array(Auth::user()->role, ['master_admin', 'admin', 'staff'])) {
            return redirect()->route('admin.dashboard');
        }

        // Always fetch fresh data from the database
        $userId = Auth::id();
        $dbUser = $userId ? User::find($userId) : null;

        if ($dbUser) {
            // Sync session with latest DB values
            $profile = [
                'id' => $dbUser->id,
                'name' => $dbUser->name,
                'email' => $dbUser->email,
                'phone' => $dbUser->phone ?? '',
                'address' => $dbUser->address ?? '',
                'city' => $dbUser->city ?? '',
                'country' => $dbUser->country ?? '',
                'role' => $dbUser->role,
            ];
            session(['user_profile' => $profile]);
        } else {
            $profile = session('user_profile', [
                'name' => '', 'email' => '', 'phone' => '',
                'address' => '', 'city' => '', 'country' => '', 'role' => 'customer',
            ]);
        }

        // Fetch orders from database
        $orders = Order::where('user_id', $userId)->with('items.product')->latest()->get();
        $total_orders = $orders->count();
        $pending_orders = $orders->where('status', 'pending')->count();
        $completed_orders = $orders->where('status', 'delivered')->count();
        $total_spent = $orders->where('status', '!=', 'cancelled')->sum('total_amount');
        $wishlist = Wishlist::with('product')
            ->where('user_id', auth()->id())->get();
        $userVehicles = Vehicle::where('user_id', $userId)->latest()->get();

        return view('user.dashboard', compact(
            'orders', 'total_orders', 'pending_orders',
            'completed_orders', 'total_spent', 'profile', 'wishlist', 'userVehicles'
        ));
    }

    public function userProfileUpdate(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:255|regex:/^[0-9+\-\s()]*$/',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
        ]);

        $userId = session('user_profile.id');
        if ($userId) {
            $user = User::find($userId);
            if ($user) {
                $user->update($request->only('name', 'email', 'phone', 'address', 'city', 'country'));
                // Refresh session
                session(['user_profile' => array_merge(session('user_profile', []), $request->only('name', 'email', 'phone', 'address', 'city', 'country'))]);
            }
        }

        return redirect()->back()->with('success', 'Profile updated successfully!');
    }

    // -------------------------------------------------------------
    // Vendor related methods removed – not needed in simplified role model
    // -------------------------------------------------------------
    public function staffDashboard()
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
            'topProducts' => $topProducts,
        ]);
    }

    public function orders()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $userId = Auth::id();

        $query = Order::where('user_id', $userId)->with('items');

        $status = request('status');
        if ($status && in_array($status, ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])) {
            $query->where('status', $status);
        }

        $orders = $query->latest()->paginate(10);

        return view('user.orders.index', compact('orders'));
    }

    public function wishlist()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $userId = Auth::id();

        $wishlist = Wishlist::where('user_id', $userId)->latest()->paginate(9)->withQueryString();

        return view('user.wishlist', compact('wishlist'));
    }

    public function followedSellers()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $userId = Auth::id();

        $followedSellers = [];

        if ($userId) {
            $followedSellers = FollowedSeller::with(['seller.products' => fn ($q) => $q->where('status', true)->orderBy('id')])
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        $availableSellers = Seller::where('status', true)->orderBy('name')->get();

        return view('user.followed-sellers', compact('followedSellers', 'availableSellers'));
    }

    public function storeFollowedSeller(Request $request)
    {
        $request->validate([
            'seller_id' => 'required|exists:sellers,id',
        ]);

        $seller = Seller::findOrFail($request->seller_id);

        if ($seller->status !== true) {
            return response()->json(['success' => false, 'message' => 'This seller is not available.']);
        }

        $existing = FollowedSeller::withTrashed()
            ->where('user_id', Auth::id())
            ->where('seller_id', $seller->id)
            ->first();

        if ($existing) {
            if (! $existing->trashed()) {
                return response()->json(['success' => false, 'message' => 'You are already following this seller.']);
            }
            $existing->restore();

            return response()->json(['success' => true, 'seller' => $existing]);
        }

        $followedSeller = FollowedSeller::create([
            'user_id' => Auth::id(),
            'seller_id' => $seller->id,
            'seller_name' => $seller->name,
            'location' => $seller->location,
            'description' => $seller->description,
            'products' => 0,
            'rating' => 0,
            'followers' => 0,
        ]);

        return response()->json(['success' => true, 'seller' => $followedSeller]);
    }

    public function destroyFollowedSeller($id)
    {
        $followedSeller = FollowedSeller::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        $followedSeller->delete();

        return response()->json(['success' => true]);
    }

    public function getSellerDetails($id)
    {
        $followedSeller = FollowedSeller::with('seller')
            ->where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        $sellerModel = $followedSeller->seller;
        $productTotal = $sellerModel
            ? $sellerModel->products()->where('status', true)->count()
            : $followedSeller->products;
        $products = $sellerModel
            ? $sellerModel->products()->where('status', true)->orderBy('id')->limit(4)->get(['id', 'name', 'slug', 'cost', 'commission', 'image'])
            : collect();

        $seller = [
            'seller_name' => $followedSeller->seller_name,
            'location' => $followedSeller->location,
            'description' => $followedSeller->description,
            'products' => $productTotal,
            'rating' => $sellerModel ? (round($sellerModel->products()->where('status', true)->avg('rating') ?? 0, 1)) : $followedSeller->rating,
            'followers' => $sellerModel ? $sellerModel->followers_count : $followedSeller->followers,
            'seller_image' => ($sellerModel && $sellerModel->image)
                ? storedImageUrl($sellerModel->image, 'assets/images')
                : null,
            'product_list' => $products->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'price' => currency_format(buyerPrice($p->price, $p)),
                'image' => storedImageUrl($p->image, 'assets/images/thumbnails'),
            ])->values(),
        ];

        return response()->json(['success' => true, 'seller' => $seller]);
    }

    public function getSellerProducts($id)
    {
        $followedSeller = FollowedSeller::with('seller')
            ->where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        $sellerModel = $followedSeller->seller;
        $offset = (int) request('offset', 0);
        $limit = 4;

        $query = $sellerModel
            ? $sellerModel->products()->where('status', true)
            : null;

        $total = $query ? $query->count() : 0;
        $products = $query
            ? $query->orderBy('id')->skip($offset)->take($limit)->get(['id', 'name', 'slug', 'cost', 'commission', 'image'])
            : collect();

        return response()->json([
            'success' => true,
            'product_list' => $products->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'price' => currency_format(buyerPrice($p->price, $p)),
                'image' => storedImageUrl($p->image, 'assets/images/thumbnails'),
            ])->values(),
            'has_more' => ($offset + $products->count()) < $total,
        ]);
    }

    public function vehicles()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $userId = Auth::id();

        $vehicles = Vehicle::where('user_id', $userId)->get();

        return view('user.vehicles', compact('vehicles'));
    }

    public function selectVehicle($id)
    {
        $vehicle = Vehicle::where('user_id', Auth::id())->findOrFail($id);
        session(['selected_vehicle_id' => $vehicle->id]);
        session()->forget('shop_vehicle_cleared');

        $url = url()->previous();
        $path = $url;
        $query = [];
        if (str_contains($url, '?')) {
            [$path, $qs] = explode('?', $url, 2);
            parse_str($qs, $query);
        }
        unset($query['year'], $query['make'], $query['model'], $query['page']);

        return redirect($path.(! empty($query) ? '?'.http_build_query($query) : ''))
            ->with('success', 'Vehicle selected: '.$vehicle->year.' '.$vehicle->make.' '.$vehicle->model);
    }

    public function addresses()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $userId = Auth::id();

        $addresses = Address::where('user_id', $userId)->orderBy('set_default', 'desc')->latest()->get();

        return view('user.addresses', compact('addresses'));
    }

    public function notifications()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $userId = Auth::id();
        $unreadCount = Notification::where('user_id', $userId)->unread()->count();
        $notifications = Notification::where('user_id', $userId)->latest()->paginate(10);
        Notification::where('user_id', $userId)->unread()->update(['is_read' => true]);

        return view('user.notifications', compact('notifications', 'unreadCount'));
    }

    public function markNotificationRead($id)
    {
        $notification = Notification::where('user_id', Auth::id())->findOrFail($id);
        $notification->update(['is_read' => true]);

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllNotificationsRead()
    {
        Notification::where('user_id', Auth::id())->unread()->update(['is_read' => true]);

        return back()->with('success', 'All notifications marked as read.');
    }

    public function inquiries()
    {
        $inquiries = Contact::with(['product', 'replier'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(20);

        return view('user.inquiries', compact('inquiries'));
    }

    public function updateInquiry(Request $request, $id)
    {
        $inquiry = Contact::where('user_id', Auth::id())
            ->whereNull('replied_at')
            ->findOrFail($id);

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        $existing = $inquiry->message;
        $body = null;
        $prefix = '';
        if (preg_match('/^\s*(Product:\s*.+?Product URL:\s*\S+)(?:\n\n|\n)(.*)$/is', $existing, $m)) {
            $prefix = trim($m[1]);
            $body = trim($m[2]);
        }

        $newBody = trim($validated['message']);
        $inquiry->message = $prefix !== '' ? "{$prefix}\n\n{$newBody}" : $newBody;
        $inquiry->subject = $validated['subject'];
        $inquiry->save();

        return back()->with('success', 'Inquiry updated successfully.');
    }

    public function destroyInquiry($id)
    {
        $inquiry = Contact::where('user_id', Auth::id())
            ->whereNull('replied_at')
            ->findOrFail($id);

        $inquiry->delete();

        return back()->with('success', 'Inquiry deleted successfully.');
    }

    // -------------------------------------------------------------
    // Vehicle CRUD
    // -------------------------------------------------------------
    public function storeVehicle(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer|digits:4|min:1900|max:2026',
            'make' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'engine' => 'nullable|string|max:100',
        ]);

        Vehicle::create(array_merge($validated, ['user_id' => Auth::id()]));

        return redirect()->route('user.vehicles')->with('success', 'Vehicle added successfully.');
    }

    public function updateVehicle(Request $request, $id)
    {
        $vehicle = Vehicle::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'year' => 'required|integer|digits:4|min:1900|max:2026',
            'make' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'engine' => 'nullable|string|max:100',
        ]);

        $vehicle->update($validated);

        return redirect()->route('user.vehicles')->with('success', 'Vehicle updated successfully.');
    }

    public function destroyVehicle($id)
    {
        $vehicle = Vehicle::where('user_id', Auth::id())->findOrFail($id);
        $vehicle->delete();

        return redirect()->route('user.vehicles')->with('success', 'Vehicle removed successfully.');
    }

    // -------------------------------------------------------------
    // Address CRUD
    // -------------------------------------------------------------
    public function storeAddress(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|regex:/^[0-9+\-\s()]+$/',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'required|string|max:100',
            'zip_code' => 'required|string|max:20|regex:/^[0-9a-zA-Z\-\s]+$/',
            'set_default' => 'nullable|boolean',
        ]);

        $address = Address::create(array_merge($validated, ['user_id' => Auth::id()]));

        if ($request->boolean('set_default')) {
            Auth::user()->addresses()->where('id', '!=', $address->id)->update(['set_default' => false]);
            $address->update(['set_default' => true]);
        }

        return redirect()->route('user.addresses')->with('success', 'Address added successfully.');
    }

    public function updateAddress(Request $request, $id)
    {
        $address = Address::where('user_id', Auth::id())->findOrFail($id);

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|regex:/^[0-9+\-\s()]+$/',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'required|string|max:100',
            'zip_code' => 'required|string|max:20|regex:/^[0-9a-zA-Z\-\s]+$/',
            'set_default' => 'nullable|boolean',
        ]);

        $address->update($validated);

        if ($request->boolean('set_default')) {
            Auth::user()->addresses()->where('id', '!=', $address->id)->update(['set_default' => false]);
            $address->update(['set_default' => true]);
        }

        return redirect()->route('user.addresses')->with('success', 'Address updated successfully.');
    }

    public function destroyAddress($id)
    {
        $address = Address::where('user_id', Auth::id())->findOrFail($id);
        $address->delete();

        return redirect()->route('user.addresses')->with('success', 'Address removed successfully.');
    }

    public function profile()
    {
        if (! Auth::check()) {
            return redirect()->route('login')
                ->with('error', 'Please login to access your profile.');
        }

        $user = User::find(Auth::id());

        if ($user) {
            $profile = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? '',
                'address' => $user->address ?? '',
                'city' => $user->city ?? '',
                'country' => $user->country ?? '',
                'role' => $user->role,
            ];
        } else {
            $profile = session('user_profile', [
                'name' => '', 'email' => '', 'phone' => '',
                'address' => '', 'city' => '', 'country' => '', 'role' => 'customer',
            ]);
        }

        return view('user.profile', compact('user', 'profile'));
    }

    public function updateProfile(Request $request)
    {
        $user = User::find(Auth::id());

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:255|regex:/^[0-9+\-\s()]+$/',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'country' => $request->country,
        ]);

        session([
            'user_profile' => $user->toArray(),
        ]);

        return back()->with(
            'success',
            'Profile updated successfully'
        );
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $user = User::find(session('user_profile.id'));

        if (! $user) {
            return back()->with('error', 'User not found.');
        }

        if (! Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'Current password is incorrect.');
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return back()->with('success', 'Password changed successfully.');
    }

    public function dashboard()
    {
        $wishlist = Wishlist::with('product')->where('user_id', auth()->id())->get();

        return view('user.dashboard', compact('wishlist'));
    }

    public function reviews(Request $request)
    {
        $userId = auth()->id();

        // Fetch user's existing reviews
        $products = Product::whereNotNull('reviews_data')->get();

        $reviewedSlugs = [];
        $items = [];
        foreach ($products as $product) {
            $reviews = is_array($product->reviews_data) ? $product->reviews_data : [];
            foreach ($reviews as $review) {
                if (($review['user_id'] ?? null) == $userId && ! ($review['deleted'] ?? false)) {
                    $reviewedSlugs[] = $product->slug;
                    $items[] = [
                        'product_name' => $product->name,
                        'product_slug' => $product->slug,
                        'product_image' => $product->image,
                        'status' => 'reviewed',
                        'review_id' => $review['id'] ?? '',
                        'rating' => $review['rating'] ?? 0,
                        'text' => $review['text'] ?? '',
                        'images' => $review['images'] ?? [],
                        'date' => $review['date'] ?? '',
                    ];
                }
            }
        }

        // Fetch purchased products not yet reviewed
        $purchasedProducts = OrderItem::whereHas('order', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
            ->with('product')
            ->get()
            ->pluck('product')
            ->filter()
            ->unique('id');

        foreach ($purchasedProducts as $product) {
            if (! in_array($product->slug, $reviewedSlugs)) {
                $items[] = [
                    'product_name' => $product->name,
                    'product_slug' => $product->slug,
                    'product_image' => $product->image,
                    'status' => 'pending',
                    'rating' => 0,
                    'text' => '',
                    'date' => '',
                ];
            }
        }

        // Pending first, then reviewed
        $items = collect($items)->sortBy(function ($item) {
            return $item['status'] === 'pending' ? 0 : 1;
        })->values();

        // Apply filters
        $search = $request->input('search', '');
        $status = $request->input('status', '');

        if ($search !== '') {
            $items = $items->filter(function ($item) use ($search) {
                return stripos($item['product_name'], $search) !== false;
            })->values();
        }
        if ($status !== '' && in_array($status, ['pending', 'reviewed'])) {
            $items = $items->filter(function ($item) use ($status) {
                return $item['status'] === $status;
            })->values();
        }

        $perPage = 10;
        $currentPage = $request->input('page', 1);
        $paginatedItems = new LengthAwarePaginator(
            $items->slice(($currentPage - 1) * $perPage, $perPage),
            $items->count(),
            $perPage,
            $currentPage,
            ['path' => route('user.reviews', array_filter(['search' => $search, 'status' => $status]))]
        );

        return view('user.reviews', ['items' => $paginatedItems, 'search' => $search, 'statusFilter' => $status]);
    }
}
