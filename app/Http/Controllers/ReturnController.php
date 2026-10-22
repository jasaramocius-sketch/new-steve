<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ReturnRequest;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    public function index()
    {
        $returns = ReturnRequest::with(['order', 'product'])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return view('user.returns.index', compact('returns'));
    }

    public function create($orderId)
    {
        $order = Order::with('items.product')
            ->where('user_id', auth()->id())
            ->findOrFail($orderId);

        if (! in_array($order->status, ['delivered'], true)) {
            return back()->with('error', 'Return requests can only be raised for delivered orders.');
        }

        $existing = ReturnRequest::where('order_id', $order->id)
            ->where('user_id', auth()->id())
            ->whereIn('status', ['pending', 'approved'])
            ->get();

        $items = $order->items->map(function ($item) use ($existing) {
            $requested = $existing
                ->where('product_id', $item->product_id)
                ->sum('qty');

            return [
                'order_item' => $item,
                'available_qty' => max((int) $item->qty - (int) $requested, 0),
            ];
        });

        return view('user.returns.create', compact('order', 'items'));
    }

    public function store(Request $request, $orderId)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.qty' => 'required|integer|min:1',
            'reason' => 'required|string|max:1000',
        ]);

        $order = Order::with('items.product')
            ->where('user_id', auth()->id())
            ->findOrFail($orderId);

        if (! in_array($order->status, ['delivered'], true)) {
            return back()->with('error', 'Return requests can only be raised for delivered orders.');
        }

        // Guard against overshooting quantity for any item.
        foreach ($request->items as $submitted) {
            $item = $order->items->firstWhere('product_id', (int) $submitted['product_id']);
            if (! $item) {
                return back()->withErrors(['items' => 'Invalid product selected for return.']);
            }
            $alreadyRequested = ReturnRequest::where('order_id', $order->id)
                ->where('user_id', auth()->id())
                ->where('product_id', (int) $submitted['product_id'])
                ->whereIn('status', ['pending', 'approved'])
                ->sum('qty');
            if ((int) $submitted['qty'] > ((int) $item->qty - (int) $alreadyRequested)) {
                return back()->withErrors(['items' => 'Return quantity cannot exceed the ordered quantity.']);
            }
        }

        foreach ($request->items as $submitted) {
            $item = $order->items->firstWhere('product_id', (int) $submitted['product_id']);

            ReturnRequest::create([
                'order_id' => $order->id,
                'user_id' => auth()->id(),
                'product_id' => (int) $submitted['product_id'],
                'product_name' => $item->product->name ?? null,
                'qty' => (int) $submitted['qty'],
                'reason' => trim($request->reason),
                'status' => 'pending',
            ]);
        }

        return redirect()->route('user.returns.index')
            ->with('success', 'Return request submitted. You will be notified once it is reviewed.');
    }

    public function destroy($id)
    {
        $return = ReturnRequest::where('user_id', auth()->id())->where('id', $id)->firstOrFail();

        if (! in_array($return->status, ['pending'], true)) {
            return back()->with('error', 'Only pending return requests can be withdrawn.');
        }

        $return->delete();

        return back()->with('success', 'Return request withdrawn.');
    }
}
