<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReturnRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturnController extends Controller
{
    public function index(Request $request)
    {
        $query = ReturnRequest::with(['order', 'user', 'product']);

        if ($request->filled('status') && in_array($request->status, ['pending', 'approved', 'rejected', 'refunded'], true)) {
            $query->where('status', $request->status);
        }

        $returns = $query->latest()->paginate(20)->withQueryString();

        return view('admin.returns.index', compact('returns'));
    }

    public function show($id)
    {
        $return = ReturnRequest::with(['order', 'user', 'product'])->findOrFail($id);

        return view('admin.returns.show', compact('return'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected,refunded',
            'refund_amount' => 'nullable|numeric|min:0',
            'admin_note' => 'nullable|string|max:5000',
        ], [], [
            'refund_amount' => 'refund amount',
        ]);

        $return = ReturnRequest::with('product')->findOrFail($id);
        $oldStatus = $return->status;

        $update = [
            'status' => $request->status,
            'admin_note' => $request->input('admin_note') ?: null,
        ];

        // Rejected return → no refund happens, so refund amount must be cleared.
        // Approved/Refunded → refund amount may be set (defaulted to order total in UI).
        if ($request->status === 'rejected') {
            $update['refund_amount'] = null;
        } elseif ($request->filled('refund_amount')) {
            $update['refund_amount'] = $request->refund_amount;
        }

        DB::transaction(function () use ($return, $update, $oldStatus, $request) {
            $return->update($update);

            // Restore stock when the product is returned to us (refunded).
            if ($request->status === 'refunded' && $oldStatus !== 'refunded' && $return->product_id) {
                $return->product?->increment('vendor_stock', $return->qty);
            }
        });

        $message = 'Return request updated.';
        if ($request->status === 'refunded') {
            $message = 'Return approved and refunded. Stock restored.';
        }

        return redirect()->route('admin.returns.show', $return->id)->with('success', $message);
    }
}
