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
use App\Services\ShippingChargeService;
use App\Services\ShippingRateService;
use App\Services\StripeCheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

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
        $homeDeliveryEnabled = Setting::get('home_delivery_enabled', '1') === '1';
        $shippingService = app(ShippingChargeService::class);
        $shippingType = $shippingService->type();
        $shippingRates = collect();
        $standardShipping = null;
        $homeDeliveryAvailable = false;

        if ($homeDeliveryEnabled) {
            if ($shippingService->isCarrierBased()) {
                $shippingRates = app(ShippingRateService::class)->availableRatesFor($billing);
                $homeDeliveryAvailable = $shippingRates->isNotEmpty();
            } else {
                $standardShipping = $shippingService->quote($cart, $billing);
                $homeDeliveryAvailable = $standardShipping !== null;
            }
        }

        return view('checkout.delivery-info', compact(
            'cart',
            'total',
            'billing',
            'pickupEnabled',
            'pickupPoints',
            'homeDeliveryEnabled',
            'shippingRates',
            'shippingType',
            'standardShipping',
            'homeDeliveryAvailable'
        ));
    }

    public function deliveryInfoSubmit(Request $request)
    {
        $billing = session('billing_info');
        if (! $billing) {
            return redirect()->route('checkout');
        }

        $cart = $this->refreshCartPrices();
        if (count($cart) === 0) {
            return redirect()->route('cart')->with('error', 'Your cart is empty.');
        }

        $homeDeliveryEnabled = Setting::get('home_delivery_enabled', '1') === '1';
        $shippingService = app(ShippingChargeService::class);
        $shippingType = $shippingService->type();
        $shippingRates = collect();
        $standardShipping = null;
        $homeDeliveryAvailable = false;

        if ($homeDeliveryEnabled) {
            if ($shippingService->isCarrierBased()) {
                $shippingRates = app(ShippingRateService::class)->availableRatesFor($billing);
                $homeDeliveryAvailable = $shippingRates->isNotEmpty();
            } else {
                $standardShipping = $shippingService->quote($cart, $billing);
                $homeDeliveryAvailable = $standardShipping !== null;
            }
        }

        $allowedShippingMethods = [];
        if ($homeDeliveryAvailable) {
            $allowedShippingMethods[] = 'home_delivery';
        }
        if (Setting::get('pickup_point', '1') === '1' && PickupPoint::where('is_open', true)->exists()) {
            $allowedShippingMethods[] = 'pickup';
        }
        if ($allowedShippingMethods === []) {
            return back()->withInput()->withErrors([
                'shipping_method' => 'No delivery option is available for this address. Contact the store for assistance.',
            ]);
        }

        $requiresCarrierRate = $request->input('shipping_method') === 'home_delivery'
            && $shippingService->isCarrierBased();

        $request->validate([
            'shipping_method' => 'required|in:'.implode(',', $allowedShippingMethods),
            'shipping_rate_id' => $requiresCarrierRate ? 'required|integer' : 'nullable',
            'pickup_point_id' => $request->input('shipping_method') === 'pickup'
                ? 'required|integer|exists:pickup_points,id'
                : 'nullable',
        ]);

        $shippingInfo = [
            'method' => $request->shipping_method,
        ];
        if ($request->shipping_method === 'home_delivery') {
            if ($shippingService->isCarrierBased()) {
                $rate = app(ShippingRateService::class)->resolveAvailableRate($billing, (int) $request->input('shipping_rate_id'));
                if (! $rate) {
                    return back()->withInput()->withErrors(['shipping_rate_id' => 'That carrier rate is no longer available for this address. Please choose another option.']);
                }
                $shippingInfo += [
                    'cost' => (float) $rate->fee,
                    'rate_id' => $rate->id,
                    'zone_id' => $rate->shipping_zone_id,
                    'zone_name' => $rate->zone->name,
                    'carrier_id' => $rate->shipping_carrier_id,
                    'carrier_name' => $rate->carrier->name,
                    'delivery_estimate' => $rate->carrier->delivery_estimate,
                ];
            } else {
                if (! $standardShipping) {
                    return back()->withInput()->withErrors(['shipping_method' => 'No shipping charge is available for this address.']);
                }
                $shippingInfo += [
                    'cost' => $standardShipping['cost'],
                    'rate_id' => null,
                    'zone_id' => null,
                    'zone_name' => $standardShipping['zone_name'],
                    'carrier_id' => null,
                    'carrier_name' => $standardShipping['carrier_name'],
                    'delivery_estimate' => $standardShipping['delivery_estimate'],
                ];
            }
        } elseif ($request->shipping_method === 'pickup') {
            $pickupPoint = PickupPoint::whereKey($request->input('pickup_point_id'))->where('is_open', true)->first();
            if (! $pickupPoint) {
                return back()->withInput()->withErrors(['pickup_point_id' => 'Choose an open pickup point.']);
            }
            $shippingInfo['cost'] = 0;
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
        } elseif (($shipping['method'] ?? null) === 'home_delivery') {
            $shipping = $this->refreshHomeDeliveryRate($shipping, $billing, $cart);
            if (! $shipping) {
                session()->forget('shipping_info');

                return redirect()->route('checkout.delivery-info')->with('error', 'The selected shipping rate is no longer available. Choose a delivery option.');
            }
        } else {
            session()->forget('shipping_info');

            return redirect()->route('checkout.delivery-info')->with('error', 'Choose a valid shipping option to continue.');
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

        $paymentMethod = (string) $request->payment_method;
        $isStripe = $paymentMethod === 'card';

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
        } elseif (($shipping['method'] ?? null) === 'home_delivery') {
            $shipping = $this->refreshHomeDeliveryRate($shipping, $billing, $cart);
            if (! $shipping) {
                session()->forget('shipping_info');

                return redirect()->route('checkout.delivery-info')->with('error', 'The selected shipping rate is no longer available. Choose a delivery option.');
            }
        } else {
            session()->forget('shipping_info');

            return redirect()->route('checkout.delivery-info')->with('error', 'Choose a valid shipping option to continue.');
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
        } elseif (($shipping['method'] ?? null) === 'home_delivery') {
            $shippingDetails['shipping'] = [
                'carrier' => $shipping['carrier_name'],
                'delivery_estimate' => $shipping['delivery_estimate'],
                'zone' => $shipping['zone_name'],
            ];
        }

        // Card payments are only marked paid after Stripe confirms the charge.
        $paymentDetails = null;
        if ($paymentMethod === 'paypal') {
            $paymentDetails = json_encode(['transaction_id' => generateTransactionId()]);
        }

        $dbOrder = $this->createCheckoutOrder(
            $request,
            $cart,
            $shippingDetails,
            $shipping,
            $grandTotal,
            $shippingCost,
            $tax,
            $paymentDetails,
            $couponData,
            $couponDiscount,
            $total,
            $products
        );

        if ($isStripe && $grandTotal > 0) {
            return $this->startStripeCheckout($dbOrder, $cart, $billing);
        }

        if ($isStripe) {
            // A fully discounted order needs no charge, so consider it paid.
            $dbOrder->update(['payment_status' => 'paid']);
        }

        $this->notifyOrderPlaced($dbOrder);

        session()->put('last_order', $this->orderSummary($dbOrder));
        $this->clearCheckoutSession();

        return redirect()->route('checkout.confirmed');
    }

    /**
     * Persist the order, its items, coupon usage and stock movement in one transaction.
     */
    private function createCheckoutOrder(
        Request $request,
        array $cart,
        array $shippingDetails,
        array $shipping,
        float $grandTotal,
        float $shippingCost,
        float $tax,
        ?string $paymentDetails,
        array $couponData,
        float $couponDiscount,
        float $total,
        $products
    ): Order {
        return DB::transaction(function () use ($request, $cart, $shippingDetails, $shipping, $grandTotal, $shippingCost, $tax, $paymentDetails, $couponData, $couponDiscount, $total, $products) {

            $orderNumber = 'ORD'.strtoupper(uniqid());

            $dbOrder = Order::create([
                'user_id' => auth()->id(),
                'vehicle_id' => session('checkout_vehicle_id'),
                'order_number' => $orderNumber,
                'total_amount' => $grandTotal,
                'status' => 'pending',
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_method === 'paypal' ? 'paid' : 'unpaid',
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
    }

    /**
     * Start a hosted Stripe Checkout session using the admin-saved credentials.
     */
    private function startStripeCheckout(Order $order, array $cart, array $billing)
    {
        // Release stock held by an earlier abandoned Stripe attempt.
        $previousOrderId = session('pending_stripe_order_id');
        if ($previousOrderId && (int) $previousOrderId !== (int) $order->id) {
            $previous = Order::find($previousOrderId);
            if ($previous && $previous->payment_status !== 'paid') {
                $this->cancelPendingOrder($previous);
            }
        }

        $itemCount = array_sum(array_map(fn ($item) => (int) $item['qty'], $cart));
        $lineItems = [[
            'name' => 'Order '.$order->order_number.' ('.$itemCount.' item'.($itemCount === 1 ? '' : 's').')',
            'amount' => (float) $order->total_amount,
            'quantity' => 1,
        ]];

        try {
            $session = app(StripeCheckoutService::class)->createCheckoutSession($lineItems, $order->order_number, [
                'order_id' => $order->id,
                'email' => $billing['email'] ?? null,
                'success_url' => route('checkout.stripe.success').'?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('checkout.stripe.cancel'),
            ]);
        } catch (Throwable $exception) {
            $this->cancelPendingOrder($order);

            return back()->with('error', 'Stripe could not start the payment: '.$exception->getMessage());
        }

        if (empty($session->url) || empty($session->id)) {
            $this->cancelPendingOrder($order);

            return back()->with('error', 'Stripe did not return a valid checkout session. Please try again.');
        }

        $details = json_decode($order->payment_details ?? '[]', true) ?: [];
        $details['stripe_session_id'] = $session->id;
        $order->update(['payment_details' => json_encode($details)]);
        session(['pending_stripe_order_id' => $order->id]);

        return redirect()->away($session->url);
    }

    /**
     * Cancel an unpaid order and return its reserved stock to the catalogue.
     */
    private function cancelPendingOrder(Order $order): void
    {
        if ($order->payment_status === 'paid') {
            return;
        }

        if ($order->stock_deducted) {
            $order->restoreStock();
        }

        $order->update([
            'status' => 'cancelled',
            'payment_status' => 'failed',
            'stock_deducted' => false,
        ]);
        $order->recordStatusChange('cancelled', 'Stripe checkout was not completed');
    }

    public function stripeSuccess(Request $request)
    {
        $sessionId = (string) $request->query('session_id', '');
        if ($sessionId === '') {
            return redirect()->route('checkout.payment')->with('error', 'Missing Stripe session reference.');
        }

        $session = app(StripeCheckoutService::class)->retrieveCheckoutSession($sessionId);

        if (! $session || ($session->payment_status ?? null) !== 'paid') {
            return redirect()->route('checkout.payment')->with('error', 'Stripe payment was not completed. Please try again.');
        }

        $orderId = $session->metadata->order_id ?? null;
        $order = $orderId ? Order::find($orderId) : null;

        if (! $order || (string) ($session->client_reference_id ?? '') !== (string) $order->order_number) {
            return redirect()->route('checkout.payment')->with('error', 'We could not verify this Stripe payment against an order. Please contact support.');
        }

        $this->markOrderPaid($order, $session);

        session()->put('last_order', $this->orderSummary($order));
        $this->clearCheckoutSession();

        return redirect()->route('checkout.confirmed');
    }

    /**
     * Stripe calls this endpoint server-to-server. The session is re-fetched from
     * Stripe with our secret key, so the payload cannot be forged.
     */
    public function stripeWebhook(Request $request)
    {
        $event = json_decode($request->getContent(), true) ?: [];
        $type = (string) ($event['type'] ?? '');
        $sessionId = (string) ($event['data']['object']['id'] ?? '');

        $handledEvents = ['checkout.session.completed', 'checkout.session.async_payment_succeeded', 'checkout.session.expired'];

        if (! in_array($type, $handledEvents, true) || $sessionId === '') {
            return response()->json(['received' => true]);
        }

        $session = app(StripeCheckoutService::class)->retrieveCheckoutSession($sessionId);
        if (! $session) {
            return response()->json(['received' => true]);
        }

        $order = isset($session->metadata->order_id) ? Order::find($session->metadata->order_id) : null;
        if (! $order || (string) ($session->client_reference_id ?? '') !== (string) $order->order_number) {
            return response()->json(['received' => true]);
        }

        if (($session->payment_status ?? null) === 'paid') {
            $this->markOrderPaid($order, $session);
        } elseif ($type === 'checkout.session.expired') {
            $this->cancelPendingOrder($order);
        }

        return response()->json(['received' => true]);
    }

    private function markOrderPaid(Order $order, object $session): void
    {
        if ($order->payment_status === 'paid') {
            return;
        }

        $details = json_decode($order->payment_details ?? '[]', true) ?: [];
        $details['stripe_session_id'] = $session->id ?? null;
        $details['transaction_id'] = $session->payment_intent ?? ($session->id ?? null);

        $update = [
            'payment_status' => 'paid',
            'payment_details' => json_encode($details),
        ];

        // A paid order should not stay in the "Pending" fulfilment state.
        if ($order->status === 'pending') {
            $update['status'] = 'processing';
        }

        $order->update($update);
        $order->recordStatusChange(
            $update['status'] ?? $order->status,
            'Payment received through Stripe'.(! empty($details['transaction_id']) ? ' ('.$details['transaction_id'].')' : '')
        );
        $this->notifyOrderPlaced($order);
    }

    public function stripeCancel(Request $request)
    {
        // If Stripe actually captured the payment despite the cancel redirect, honour it.
        $sessionId = (string) $request->query('session_id', '');
        if ($sessionId !== '') {
            $session = app(StripeCheckoutService::class)->retrieveCheckoutSession($sessionId);
            if ($session && ($session->payment_status ?? null) === 'paid') {
                return redirect()->route('checkout.stripe.success', ['session_id' => $sessionId]);
            }
        }

        $pendingOrderId = session('pending_stripe_order_id');
        if ($pendingOrderId) {
            $pending = Order::find($pendingOrderId);
            if ($pending) {
                $this->cancelPendingOrder($pending);
            }
        }

        session()->forget('pending_stripe_order_id');

        return redirect()->route('checkout.payment')->with('error', 'Stripe payment was cancelled. Your cart is still saved, so you can try again.');
    }

    private function notifyOrderPlaced(Order $order): void
    {
        $order->loadMissing('items.product');
        NotificationHelper::orderPlaced($order);

        $billing = json_decode($order->shipping_details ?? '[]', true) ?: [];
        $toEmail = ($billing['email'] ?? null) ?: ($order->user?->email ?? null);
        if ($toEmail) {
            Mail::to($toEmail)->queue(new OrderConfirmationMail($order));
        }
    }

    /**
     * Build the array consumed by the order confirmation page.
     */
    private function orderSummary(Order $order): array
    {
        $order->loadMissing('items.product');
        $billing = json_decode($order->shipping_details ?? '[]', true) ?: [];
        $subtotal = $order->items->sum(fn ($item) => (float) $item->price * (int) $item->qty);

        return [
            'id' => '#'.$order->order_number,
            'db_id' => $order->id,
            'date' => $order->created_at->format('d M, Y'),
            'total' => (float) $order->total_amount,
            'shipping_fee' => (float) $order->shipping_fee,
            'delivery_type' => $order->delivery_type,
            'shipping_carrier' => $billing['shipping']['carrier'] ?? null,
            'subtotal' => $subtotal,
            'status' => ucfirst($order->status),
            'payment_method' => $order->payment_method,
            'coupon_code' => $order->coupon_code,
            'coupon_discount' => (float) $order->coupon_discount,
            'customer_name' => $billing['name'] ?? $order->user?->name ?? '',
            'customer_email' => $billing['email'] ?? $order->user?->email ?? '',
            'customer_phone' => $billing['phone'] ?? '',
            'address' => ($billing['address'] ?? '').', '.($billing['city'] ?? '').', '.($billing['country'] ?? ''),
            'items' => $order->items->map(fn ($item) => [
                'name' => $item->product->name ?? 'Product #'.$item->product_id,
                'qty' => (int) $item->qty,
                'price' => (float) $item->price,
            ])->values()->all(),
        ];
    }

    private function clearCheckoutSession(): void
    {
        session()->forget([
            'cart',
            'billing_info',
            'shipping_info',
            'checkout_vehicle_id',
            'coupon',
            'pending_stripe_order_id',
        ]);
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
                $order = $this->orderSummary($dbOrder);
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

    private function refreshHomeDeliveryRate(array $shipping, array $billing, array $cart): ?array
    {
        if (Setting::get('home_delivery_enabled', '1') !== '1') {
            return null;
        }

        $shippingService = app(ShippingChargeService::class);

        if ($shippingService->isCarrierBased()) {
            if (empty($shipping['rate_id'])) {
                return null;
            }

            $rate = app(ShippingRateService::class)->resolveAvailableRate($billing, (int) $shipping['rate_id']);
            if (! $rate) {
                return null;
            }

            $shipping['cost'] = (float) $rate->fee;
            $shipping['zone_id'] = $rate->shipping_zone_id;
            $shipping['zone_name'] = $rate->zone->name;
            $shipping['carrier_id'] = $rate->shipping_carrier_id;
            $shipping['carrier_name'] = $rate->carrier->name;
            $shipping['delivery_estimate'] = $rate->carrier->delivery_estimate;
        } else {
            $quote = $shippingService->quote($cart, $billing);
            if (! $quote) {
                return null;
            }

            $shipping['cost'] = $quote['cost'];
            $shipping['zone_id'] = null;
            $shipping['zone_name'] = $quote['zone_name'];
            $shipping['carrier_id'] = null;
            $shipping['carrier_name'] = $quote['carrier_name'];
            $shipping['delivery_estimate'] = $quote['delivery_estimate'];
            $shipping['rate_id'] = null;
        }

        session(['shipping_info' => $shipping]);

        return $shipping;
    }

    private function minimumOrderAmountRedirect()
    {
        $minimumAmount = (float) Setting::get('minimum_order_amount', 0);

        return redirect()->route('cart')->with('error', 'Cart subtotal must be at least '.currency_format($minimumAmount).' to continue.');
    }

    private function enabledCheckoutPaymentMethods(): array
    {
        $methods = [];

        if (Setting::get('stripe_payment', '1') === '1' && app(StripeCheckoutService::class)->isConfigured()) {
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
