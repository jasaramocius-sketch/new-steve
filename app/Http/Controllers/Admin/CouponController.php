<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Order;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $sortBy = in_array($request->sort_by, ['id', 'code', 'type', 'value', 'status', 'expires_at', 'created_at']) ? $request->sort_by : 'created_at';
        $sortDir = $request->sort_dir === 'asc' ? 'asc' : 'desc';
        $perPage = in_array((int) $request->per_page, [10, 20, 50, 100]) ? (int) $request->per_page : 10;

        $coupons = Coupon::orderBy($sortBy, $sortDir)->paginate($perPage);
        $coupons->appends($request->query())->onEachSide(1);

        return view('admin.coupons.index', compact('coupons', 'sortBy', 'sortDir'));
    }

    public function create()
    {
        return view('admin.coupons.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code',
            'type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:0',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        $data = $request->only(['code', 'type', 'value', 'min_order_amount', 'max_uses', 'starts_at', 'expires_at']);
        $data['code'] = strtoupper($request->code);
        $data['status'] = $request->boolean('status');

        Coupon::create($data);

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon created successfully.');
    }

    public function edit($id)
    {
        $coupon = Coupon::findOrFail($id);

        return view('admin.coupons.edit', compact('coupon'));
    }

    public function update(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);

        $request->validate([
            'code' => 'required|string|max:50|unique:coupons,code,'.$id,
            'type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:0',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:0',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        $data = $request->only(['code', 'type', 'value', 'min_order_amount', 'max_uses', 'starts_at', 'expires_at']);
        $data['code'] = strtoupper($request->code);
        $data['status'] = $request->boolean('status');

        $coupon->update($data);

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon updated successfully.');
    }

    public function destroy($id)
    {
        Coupon::findOrFail($id)->delete();

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon deleted successfully.');
    }

    public function show(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);

        $perPage = in_array((int) $request->per_page, [10, 20, 50, 100]) ? (int) $request->per_page : 20;
        $orders = Order::where('coupon_code', $coupon->code)
            ->with(['user', 'items.product'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
        $orders->appends($request->query())->onEachSide(1);

        $totalDiscount = Order::where('coupon_code', $coupon->code)->sum('coupon_discount');
        $orderCount = $orders->total();

        return view('admin.coupons.show', compact('coupon', 'orders', 'totalDiscount', 'orderCount'));
    }
}
