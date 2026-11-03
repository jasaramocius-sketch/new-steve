<?php

namespace App\Http\Controllers;

use App\Helpers\NotificationHelper;
use App\Mail\OrderConfirmationMail;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Page;
use App\Models\PickupPoint;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class CartController extends Controller
{
    public function index()
    {
        $page = Page::where('slug', 'cart')->first();
        $cart = $this->refreshCartPrices();
        $total = array_sum(array_map(fn ($item) => $item['price'] * $item['qty'], $cart));
        $cartItems = $cart;
        $couponData = $this->recomputeCoupon($cart) ?? [];
        $couponDiscount = $couponData['discount'] ?? 0;
        $couponEnabled = Setting::get('coupon_system', '1') === '1';
        $minimumOrderAmountEnabled = Setting::get('minimum_order_amount_check', '0') === '1';
        $minimumOrderAmount = (float) Setting::get('minimum_order_amount', 0);
        $minimumOrderRemaining = $minimumOrderAmountEnabled ? max($minimumOrderAmount - $total, 0) : 0;

        $taxRate = (float) (Setting::where('key', 'tax_rate')->value('value') ?? 0);
        $tax = round($total * ($taxRate / 100), 2);
        $grandTotal = $total - $couponDiscount + $tax;
        return view('cart.index', compact('cart', 'total', 'cartItems', 'page', 'couponData', 'couponDiscount', 'couponEnabled', 'minimumOrderAmountEnabled', 'minimumOrderAmount', 'minimumOrderRemaining', 'tax', 'grandTotal', 'taxRate'));
    }

    public function add(Request $request)
    {
        $cart = $this->refreshCartPrices();

        $product = Product::where('status', true)->find($request->input('product_id'));
        $reject = function (string $message) use ($request) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return back()->with('error', $message);
        };

        if (! $product) {
            return $reject('This product is not available.');
        }

        $stock = (int) $product->stock;
        if ($stock <= 0) {
            return $reject('This product is out of stock.');
        }

        $qty = max((int) $request->input('qty', 1), 1);
        $id = $product->id;
        $existingQty = (int) ($cart[$id]['qty'] ?? 0);

        if ($existingQty + $qty > $stock) {
            return $reject('Only '.$stock.' unit(s) of this product are available.');
        }

        if (isset($cart[$id])) {
            $cart[$id]['qty'] += $qty;
        } else {
            $cart[$id] = [
                'id' => $id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => buyerPrice($product->price, $product),
                'image' => storedImageUrl($product->image, 'assets/images/thumbnails'),
                'qty' => $qty,
                'stock' => $stock,
            ];
        }

        session()->put('cart', $cart);
        $this->recomputeCoupon($cart);

        // Buy Now
        if ($request->has('buy_now')) {
            return redirect()->route('cart')
                ->with('success', 'Product added to cart!');
        }

        // AJAX request
        if ($request->ajax()) {
            $total = array_sum(array_map(fn ($item) => $item['price'] * $item['qty'], $cart));

            return response()->json([
                'success' => true,
                'message' => 'Product added to cart!',
                'cart_count' => count($cart),
                'cart_total' => currency_format($total),
            ]);
        }

        // Normal request
        return back()->with('success', 'Product added to cart!');
    }

    public function remove(Request $request)
    {
        $cart = $this->refreshCartPrices();
        unset($cart[$request->product_id]);
        session(['cart' => $cart]);
        $this->recomputeCoupon($cart);

        if ($request->ajax()) {
            $total = array_sum(array_map(fn ($item) => $item['price'] * $item['qty'], $cart));

            return response()->json([
                'success' => true,
                'message' => 'Product removed from cart!',
                'cart_count' => count($cart),
                'cart_total' => currency_format($total),
            ]);
        }

        return back()->with('success', 'Product removed from cart!');
    }

    public function removeSelected(Request $request)
    {
        $ids = json_decode($request->product_ids, true) ?? [];
        $cart = $this->refreshCartPrices();
        foreach ($ids as $id) {
            unset($cart[$id]);
        }
        session(['cart' => $cart]);
        $this->recomputeCoupon($cart);
        $count = count($ids);

        return back()->with('success', "$count item(s) removed from cart!");
    }

    public function updateQuantity(Request $request, $id)
    {
        $cart = $this->refreshCartPrices();

        if (! isset($cart[$id])) {

            return response()->json([
                'success' => false,
            ]);

        }

        $qty = max((int) $request->quantity, 1);
        $stock = (int) ($cart[$id]['stock'] ?? 0);

        $product = \App\Models\Product::find($id);
        if ($product) {
            $stock = (int) ($product->vendor_stock ?? $product->stock ?? 0);
            if ($stock > 0 && $qty > $stock) {
                $qty = $stock;
            }
        }

        $cart[$id]['qty'] = $qty;

        session()->put('cart', $cart);
        $this->recomputeCoupon($cart);

        $itemSubtotal = $cart[$id]['price'] * $qty;

        $cartTotal = array_sum(array_map(function ($item) {

            return $item['price'] * $item['qty'];

        }, $cart));

        return response()->json([

            'success' => true,

            'qty' => $qty,

            'stock' => $stock,

            'itemSubtotal' => currency_format($itemSubtotal),

            'cartTotal' => currency_format($cartTotal),

        ]);
    }

    public function applyCoupon(Request $request)
    {
        if (Setting::get('coupon_system', '1') !== '1') {
            return back()->with('error', 'Coupons are currently disabled.');
        }

        $request->validate(['coupon_code' => 'required|string|max:50']);
        $code = strtoupper(trim($request->coupon_code));
        $coupon = Coupon::where('code', $code)->first();

        if (! $coupon) {
            return back()->with('error', 'Invalid coupon code.');
        }
        if (! $coupon->isValid()) {
            return back()->with('error', 'This coupon is no longer valid.');
        }

        $cart = $this->refreshCartPrices();
        $subtotal = array_sum(array_map(fn ($item) => $item['price'] * $item['qty'], $cart));

        if ($coupon->min_order_amount > 0 && $subtotal < $coupon->min_order_amount) {
            return back()->with('error', 'Minimum order amount for this coupon is '.currency_format($coupon->min_order_amount).'.');
        }

        $discount = $coupon->calculateDiscount($subtotal);
        session([
            'coupon' => [
                'code' => $coupon->code,
                'discount' => $discount,
                'type' => $coupon->type,
                'value' => $coupon->value,
            ],
        ]);

        return back()->with('success', 'Coupon applied! You saved '.currency_format($discount).'.');
    }

    public function removeCoupon()
    {
        session()->forget('coupon');

        return back()->with('success', 'Coupon removed.');
    }

    /**
     * Recompute the applied coupon against the CURRENT cart.
     * Re-fetches the coupon from DB, re-validates (validity, capacity, min-order)
     * and re-calculates the discount on the current subtotal.
     * Used on every cart change and as the final authority in paymentSubmit.
     */
    private function recomputeCoupon(array $cart): ?array
    {
        if (Setting::get('coupon_system', '1') !== '1') {
            session()->forget('coupon');

            return null;
        }

        if (session()->missing('coupon')) {
            return null;
        }

        $code = session('coupon')['code'] ?? null;
        if (! $code) {
            session()->forget('coupon');

            return null;
        }

        $subtotal = array_sum(array_map(fn ($item) => $item['price'] * $item['qty'], $cart));
        $coupon = Coupon::where('code', $code)->first();

        if (! $coupon || ! $coupon->isValid()) {
            session()->forget('coupon');

            return null;
        }

        $discount = $coupon->calculateDiscount($subtotal);

        if ($discount <= 0) {
            session()->forget('coupon');

            return null;
        }

        $data = [
            'code' => $coupon->code,
            'discount' => $discount,
            'type' => $coupon->type,
            'value' => $coupon->value,
        ];
        session()->put('coupon', $data);

        return $data;
    }

    public function addToCart(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $cart = $this->refreshCartPrices();

        if (isset($cart[$id])) {

            $cart[$id]['qty']++;

        } else {

            $cart[$id] = [

                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'price' => buyerPrice($product->price, $product),
                'image' => $product->image,
                'qty' => 1,

            ];
        }

        session()->put('cart', $cart);

        if ($request->ajax()) {
            $total = array_sum(array_map(fn ($item) => $item['price'] * $item['qty'], $cart));

            return response()->json([
                'success' => true,
                'message' => 'Product added to cart!',
                'cart_count' => count($cart),
                'cart_total' => currency_format($total),
            ]);
        }

        return redirect()->back()->with('success', 'Product added to cart!');
    }

    public function checkout()
    {
        $cart = $this->refreshCartPrices();
        if (count($cart) === 0) {
            return redirect()->route('cart')->with('error', 'Your cart is empty.');
        }
        if (! $this->minimumOrderAmountIsMet($cart)) {
            return $this->minimumOrderAmountRedirect();
        }

        // Guests enter their delivery details directly on the checkout page.
        if (! auth()->check()) {
            return view('checkout.guest', compact('cart'));
        }

        $total = array_sum(array_map(fn ($item) => $item['price'] * $item['qty'], $cart));
        $couponData = $this->recomputeCoupon($cart) ?? [];
        $couponDiscount = $couponData['discount'] ?? 0;
        $addresses = auth()->user()->addresses()->latest()->get();
        $vehicles = Vehicle::where('user_id', auth()->id())->get();
        $selectedVehicleId = session('selected_vehicle_id');

        return view('checkout.index', compact('cart', 'total', 'addresses', 'vehicles', 'selectedVehicleId', 'couponData', 'couponDiscount'));
    }

    public function checkoutSubmitGuest(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:150',
            'state' => 'nullable|string|max:150',
            'country' => 'required|string|max:150',
            'zip_code' => 'nullable|string|max:20',
        ]);

        $cart = $this->refreshCartPrices();
        if (count($cart) === 0) {
            return redirect()->route('cart')->with('error', 'Your cart is empty.');
        }

        session([
            'billing_info' => [
                'name' => $request->full_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'country' => $request->country,
                'zip_code' => $request->zip_code,
            ],
        ]);
        session(['checkout_vehicle_id' => null]);

        return redirect()->route('checkout.delivery-info');
    }

    public function checkoutSubmit(Request $request)
    {
        $request->validate([
            'address_id' => 'required|exists:addresses,id',
        ]);

        $cart = $this->refreshCartPrices();
        if (count($cart) === 0) {
            return redirect()->route('cart')->with('error', 'Your cart is empty.');
        }

        $address = Address::findOrFail($request->address_id);
        if ($address->user_id !== auth()->id()) {
            abort(403);
        }

        $vehicleId = null;
        if ($request->filled('vehicle_id')) {
            $vehicle = Vehicle::where('user_id', auth()->id())->where('id', $request->vehicle_id)->first();
            $vehicleId = $vehicle ? $vehicle->id : null;
        }
        session(['checkout_vehicle_id' => $vehicleId]);

        session([
            'billing_info' => [
                'name' => $address->full_name,
                'email' => auth()->user()->email,
                'phone' => $address->phone,
                'address' => $address->address,
                'city' => $address->city,
                'state' => $address->state,
                'country' => $address->country,
                'zip_code' => $address->zip_code,
            ],
        ]);

        return redirect()->route('checkout.delivery-info');
    }

    public function deliveryInfo()
    {
        $cart = $this->refreshCartPrices();
        if (count($cart) === 0) {
            return redirect()->route('cart')->with('error', 'Your cart is empty.');
        }
        if (! $this->minimumOrderAmountIsMet($cart)) {
            return $this->minimumOrderAmountRedirect();
        }
        $billing = session('billing_info');
        if (! $billing) {
            return redirect()->route('checkout');
        }
        $total = array_sum(array_map(fn ($item) => $item['price'] * $item['qty'], $cart));
        $pickupPoints = PickupPoint::where('is_open', true)->orderBy('name')->get();
        $pickupEnabled = Setting::get('pickup_point', '1') === '1' && $pickupPoints->isNotEmpty();

        return view('checkout.delivery-info', compact('cart', 'total', 'billing', 'pickupEnabled', 'pickupPoints'));
    }

    public function deliveryInfoSubmit(Request $request)
    {
        $allowedShippingMethods = ['free'];
        if (Setting::get('pickup_point', '1') === '1' && PickupPoint::where('is_open', true)->exists()) {
            $allowedShippingMethods[] = 'pickup';
        }

        $request->validate([
            'shipping_method' => 'required|in:'.implode(',', $allowedShippingMethods),
            'pickup_point_id' => $request->input('shipping_method') === 'pickup'
                ? 'required|integer|exists:pickup_points,id'
                : 'nullable',
        ]);

        $cart = $this->refreshCartPrices();
        if (count($cart) === 0) {
            return redirect()->route('cart')->with('error', 'Your cart is empty.');
        }

        $shippingCosts = ['free' => 0, 'pickup' => 0];
        $shippingCost = $shippingCosts[$request->shipping_method];
        $shippingInfo = [
            'method' => $request->shipping_method,
            'cost' => $shippingCost,
        ];
        if ($request->shipping_method === 'pickup') {
            $pickupPoint = PickupPoint::whereKey($request->input('pickup_point_id'))->where('is_open', true)->first();
            if (! $pickupPoint) {
                return back()->withInput()->withErrors(['pickup_point_id' => 'Choose an open pickup point.']);
            }
            $shippingInfo['pickup_point_id'] = $pickupPoint->id;
        }

        session(['shipping_info' => $shippingInfo]);

        return redirect()->route('checkout.payment');
    }

    public function payment()
    {
        $cart = $this->refreshCartPrices();
        if (count($cart) === 0) {
            return redirect()->route('cart')->with('error', 'Your cart is empty.');
        }
        if (! $this->minimumOrderAmountIsMet($cart)) {
            return $this->minimumOrderAmountRedirect();
        }
        $billing = session('billing_info');
        if (! $billing) {
            return redirect()->route('checkout');
        }
        $shipping = session('shipping_info');
        if (! $shipping) {
            return redirect()->route('checkout.delivery-info');
        }
        if (($shipping['method'] ?? null) === 'pickup') {
            $pickupDetails = $this->activePickupPointDetails($shipping);
            if (! $pickupDetails) {
                session()->forget('shipping_info');

                return redirect()->route('checkout.delivery-info')->with('error', 'Pickup is currently unavailable. Choose home delivery.');
            }
            $shipping['pickup_point'] = $pickupDetails;
            session(['shipping_info' => $shipping]);
        }
        $total = array_sum(array_map(fn ($item) => $item['price'] * $item['qty'], $cart));
        $shippingCost = $shipping['cost'];
        $couponData = $this->recomputeCoupon($cart) ?? [];
        $couponDiscount = $couponData['discount'] ?? 0;
        $taxRate = (float) (Setting::where('key', 'tax_rate')->value('value') ?? 0);
        $tax = round($total * ($taxRate / 100), 2);
        $grandTotal = max($total + $shippingCost - $couponDiscount + $tax, 0);
        $shippingMethod = $shipping['method'];
        $paymentMethods = $this->enabledCheckoutPaymentMethods();

        return view('checkout.payment', compact('cart', 'total', 'shippingCost', 'grandTotal', 'shippingMethod', 'couponData', 'couponDiscount', 'taxRate', 'tax', 'paymentMethods'));
    }

    public function paymentSubmit(Request $request)
    {
        $paymentMethods = $this->enabledCheckoutPaymentMethods();
        if (empty($paymentMethods)) {
            return back()->with('error', 'No payment methods are currently available. Contact the store administrator.');
        }

        $request->validate([
            'payment_method' => 'required|in:'.implode(',', array_keys($paymentMethods)),
        ]);

        $paymentDetails = [];
        if ($request->payment_method === 'card' && $request->filled('card_number')) {
            $digits = preg_replace('/\D/', '', $request->card_number);
            $paymentDetails['card_last_four'] = substr($digits, -4);
            $paymentDetails['card_number'] = substr($request->card_number, 0, 6).'****'.substr($digits, -4);
        }

        if (in_array($request->payment_method, ['card', 'paypal'], true)) {
            $paymentDetails['transaction_id'] = generateTransactionId();
        }

        $paymentDetails = empty($paymentDetails) ? null : json_encode($paymentDetails);

        $cart = $this->refreshCartPrices();
        if (count($cart) === 0) {
            return redirect()->route('cart')->with('error', 'Your cart is empty.');
        }
        if (! $this->minimumOrderAmountIsMet($cart)) {
            return $this->minimumOrderAmountRedirect();
        }

        $billing = session('billing_info');
        $shipping = session('shipping_info');

        if (! $billing || ! $shipping) {
            return redirect()->route('checkout');
        }
        if (($shipping['method'] ?? null) === 'pickup') {
            $pickupDetails = $this->activePickupPointDetails($shipping);
            if (! $pickupDetails) {
                session()->forget('shipping_info');

                return redirect()->route('checkout.delivery-info')->with('error', 'Pickup is currently unavailable. Choose home delivery.');
            }
            $shipping['pickup_point'] = $pickupDetails;
            session(['shipping_info' => $shipping]);
        }

        $total = array_sum(array_map(fn ($item) => $item['price'] * $item['qty'], $cart));
        $shippingCost = $shipping['cost'] ?? 0;
        $couponData = $this->recomputeCoupon($cart) ?? [];
        $couponDiscount = $couponData['discount'] ?? 0;
        $taxRate = (float) (Setting::where('key', 'tax_rate')->value('value') ?? 0);
        $tax = round($total * ($taxRate / 100), 2);
        $grandTotal = max($total + $shippingCost - $couponDiscount + $tax, 0);

        // Stock availability check against the database (source of truth)
        $productIds = array_map('intval', array_keys($cart));
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
        foreach ($cart as $id => $item) {
            $product = $products[(int) $id] ?? null;
            if (! $product) {
                return back()->with('error', "Sorry, \"{$item['name']}\" is no longer available.");
            }
            if ((int) $product->stock < (int) $item['qty']) {
                session()->put('stock_error_'.$product->id, $product->stock);

                return back()->with('error', "Sorry, only {$product->stock} unit(s) of \"{$product->name}\" are available. Please adjust the quantity.");
            }
        }

        $shippingDetails = $billing;
        if (($shipping['method'] ?? null) === 'pickup' && ! empty($shipping['pickup_point'])) {
            $shippingDetails['pickup_point'] = $shipping['pickup_point'];
        }

        // Save to database
        $dbOrder = DB::transaction(function () use ($request, $cart, $shippingDetails, $shipping, $grandTotal, $shippingCost, $tax, $paymentDetails, $couponData, $couponDiscount, $total, $products) {

            $orderNumber = 'ORD'.strtoupper(uniqid());

            $dbOrder = Order::create([
                'user_id' => auth()->id(),
                'vehicle_id' => session('checkout_vehicle_id'),
                'order_number' => $orderNumber,
                'total_amount' => $grandTotal,
                'status' => 'pending',
                'payment_method' => $request->payment_method,
                'payment_status' => in_array($request->payment_method, ['card', 'paypal'], true) ? 'paid' : 'unpaid',
                'payment_details' => $paymentDetails,
                'delivery_type' => $shipping['method'] ?? 'free',
                'shipping_fee' => $shippingCost,
                'tax' => $tax,
                'coupon_code' => $couponData['code'] ?? null,
                'coupon_discount' => $couponDiscount,
                'additional_info' => $request->input('additional_info'),
                'shipping_details' => json_encode($shippingDetails),
            ]);

            $dbOrder->recordStatusChange('pending', 'Order placed');

            // Proportional coupon split: each item's share = discount * (line subtotal / order subtotal)
            $distributedDiscount = 0.0;
            $lineCount = count($cart);
            foreach ($cart as $i => $item) {
                $lineSubtotal = $item['price'] * $item['qty'];
                $itemDiscount = 0.0;
                if ($couponDiscount > 0 && $total > 0) {
                    $itemDiscount = round($couponDiscount * ($lineSubtotal / $total), 2);
                }
                // Last item absorbs rounding remainder so line discounts sum exactly to the order discount
                if ($i === $lineCount - 1 && $couponDiscount > 0) {
                    $itemDiscount = round($couponDiscount - $distributedDiscount, 2);
                }
                $distributedDiscount += $itemDiscount;

                OrderItem::create([
                    'order_id' => $dbOrder->id,
                    'product_id' => $item['id'],
                    'qty' => $item['qty'],
                    'price' => $item['price'],
                    'coupon_discount' => $itemDiscount,
                ]);
            }

            // Increment coupon usage count
            if (! empty($couponData['code']) && $couponDiscount > 0) {
                $coupon = Coupon::where('code', $couponData['code'])->first();
                if ($coupon) {
                    $coupon->increment('used_count');
                }
            }

            // Deduct the ordered quantity from stock (locked rows prevent overselling)
            foreach ($cart as $id => $item) {
                $product = $products[(int) $id] ?? null;
                if ($product) {
                    $product->whereKey($product->id)->lockForUpdate()->decrement('vendor_stock', (int) $item['qty']);
                }
            }
            $dbOrder->update(['stock_deducted' => true]);

            return $dbOrder;
        });

        NotificationHelper::orderPlaced($dbOrder);

        $toEmail = $billing['email'] ?? null ?: ($dbOrder->user?->email ?? null);
        if ($toEmail) {
            Mail::to($toEmail)->queue(new OrderConfirmationMail($dbOrder->load('items.product')));
        }

        // Build session data for the confirmation page
        $order = [
            'id' => '#'.$dbOrder->order_number,
            'db_id' => $dbOrder->id,
            'date' => $dbOrder->created_at->format('d M, Y'),
            'total' => $grandTotal,
            'subtotal' => $total,
            'status' => 'Pending',
            'payment_method' => $request->payment_method,
            'coupon_code' => $couponData['code'] ?? null,
            'coupon_discount' => $couponDiscount,
            'customer_name' => $billing['name'] ?? auth()->user()->name,
            'customer_email' => $billing['email'] ?? auth()->user()->email,
            'customer_phone' => $billing['phone'] ?? '',
            'address' => ($billing['address'] ?? '').', '.($billing['city'] ?? '').', '.($billing['country'] ?? ''),
            'items' => array_values(array_map(fn ($item) => [
                'name' => $item['name'],
                'qty' => $item['qty'],
                'price' => $item['price'],
            ], $cart)),
        ];

        session()->put('last_order', $order);

        // Clear checkout session data
        session()->forget('cart');
        session()->forget('billing_info');
        session()->forget('shipping_info');
        session()->forget('checkout_vehicle_id');
        session()->forget('coupon');

        return redirect()->route('checkout.confirmed');
    }

    public function orderConfirmed()
    {
        $order = session('last_order');

        // Fallback: if session expired, try loading the user's latest order
        if (! $order && auth()->check()) {
            $dbOrder = Order::where('user_id', auth()->id())
                ->latest()
                ->with('items.product')
                ->first();

            if ($dbOrder) {
                $billing = json_decode($dbOrder->shipping_details, true) ?? [];
                $order = [
                    'id' => '#'.$dbOrder->order_number,
                    'db_id' => $dbOrder->id,
                    'date' => $dbOrder->created_at->format('d M, Y'),
                    'total' => $dbOrder->total_amount,
                    'status' => ucfirst($dbOrder->status),
                    'payment_method' => $dbOrder->payment_method,
                    'customer_name' => $billing['name'] ?? auth()->user()->name,
                    'customer_email' => $billing['email'] ?? auth()->user()->email,
                    'customer_phone' => $billing['phone'] ?? '',
                    'address' => ($billing['address'] ?? '').', '.($billing['city'] ?? '').', '.($billing['country'] ?? ''),
                    'items' => $dbOrder->items->map(fn ($i) => [
                        'name' => $i->product->name ?? 'Product #'.$i->product_id,
                        'qty' => $i->qty,
                        'price' => $i->price,
                    ])->toArray(),
                ];
            }
        }

        if (! $order) {
            return redirect()->route('home');
        }

        return view('checkout.order-confirmed', compact('order'));
    }

    private function minimumOrderAmountIsMet(array $cart): bool
    {
        if (Setting::get('minimum_order_amount_check', '0') !== '1') {
            return true;
        }

        $minimumAmount = (float) Setting::get('minimum_order_amount', 0);
        $subtotal = array_sum(array_map(fn ($item) => $item['price'] * $item['qty'], $cart));

        return $subtotal >= $minimumAmount;
    }

    private function activePickupPointDetails(array $shipping): ?array
    {
        if (Setting::get('pickup_point', '1') !== '1') {
            return null;
        }

        $pickupPoint = PickupPoint::whereKey($shipping['pickup_point_id'] ?? 0)
            ->where('is_open', true)
            ->first();

        return $pickupPoint?->only(['id', 'name', 'manager', 'location', 'contact']);
    }

    private function minimumOrderAmountRedirect()
    {
        $minimumAmount = (float) Setting::get('minimum_order_amount', 0);

        return redirect()->route('cart')->with('error', 'Cart subtotal must be at least '.currency_format($minimumAmount).' to continue.');
    }

    private function enabledCheckoutPaymentMethods(): array
    {
        $methods = [];

        if (Setting::get('stripe_payment', '1') === '1') {
            $methods['card'] = ['label' => 'Stripe', 'icon' => 'las la-credit-card'];
        }
        if (Setting::get('cash_payment', '1') === '1') {
            $methods['cod'] = ['label' => 'Cash on Delivery', 'icon' => 'las la-money-bill-wave'];
        }
        if (Setting::get('paypal_payment', '0') === '1') {
            $methods['paypal'] = ['label' => 'PayPal', 'icon' => 'lab la-paypal'];
        }

        return $methods;
    }

    private function refreshCartPrices(): array
    {
        $cart = session('cart', []);
        if ($cart === []) {
            return [];
        }

        $products = Product::whereIn('id', array_keys($cart))->get(['id', 'name', 'slug', 'cost', 'main_image', 'vendor_stock'])->keyBy('id');
        foreach ($cart as $id => $item) {
            $product = $products->get((int) $id);
            if (! $product) {
                continue;
            }

            $cart[$id]['name'] = $product->name;
            $cart[$id]['slug'] = $product->slug;
            $cart[$id]['price'] = buyerPrice($product->price, $product);
            $cart[$id]['image'] = storedImageUrl($product->image, 'assets/images/thumbnails');
            $cart[$id]['stock'] = (int) $product->stock;
        }

        session()->put('cart', $cart);

        return $cart;
    }

    public function miniCart()
    {
        $cart = $this->refreshCartPrices();
        $cartCount = count($cart);
        $cartTotal = array_sum(array_map(fn ($item) => $item['price'] * $item['qty'], $cart));

        return view('partials.mini-cart', compact('cart', 'cartCount', 'cartTotal'));
    }
}
