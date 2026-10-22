<?php

namespace App\Http\Controllers;

use App\Models\Compare;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Http\Request;

class CompareController extends Controller
{
    public function index()
    {
        $page = Page::where('slug', 'compare')->first();
        $compareItems = collect();

        if (session('user_logged_in') && session('user_profile.id')) {
            $compareItems = Compare::with('product')
                ->where('user_id', session('user_profile.id'))
                ->latest()
                ->get();
        } else {
            $guestCompare = session()->get('guest_compare', []);
            $products = Product::whereIn('id', $guestCompare)->get();

            foreach ($products as $product) {
                $compareItems->push((object) [
                    'id' => $product->id,
                    'product_id' => $product->id,
                    'product' => $product,
                ]);
            }
        }

        return view('compare.index', compact('compareItems', 'page'));
    }

    public function add(Request $request)
    {
        $productId = $request->input('product_id');

        if (! $productId) {
            return response()->json([
                'error' => 'Invalid product selected for compare.',
                'count' => 0,
            ], 400);
        }

        // Logged-in user
        if (session('user_logged_in') && session('user_profile.id')) {

            $userId = session('user_profile.id');

            // Duplicate check
            if (Compare::where('user_id', $userId)
                ->where('product_id', $productId)
                ->exists()) {

                return response()->json([
                    'error' => 'Product already in compare list.',
                    'count' => Compare::where('user_id', $userId)->count(),
                ]);
            }

            // Maximum 3 products
            $count = Compare::where('user_id', $userId)->count();

            if ($count >= 3) {
                return response()->json([
                    'error' => 'Maximum 3 products can be compared.',
                    'count' => $count,
                ]);
            }

            Compare::create([
                'user_id' => $userId,
                'product_id' => $productId,
            ]);

            $idsToKeep = Compare::where('user_id', $userId)
                ->orderBy('id', 'desc')
                ->limit(3)
                ->pluck('id')
                ->toArray();

            if (! empty($idsToKeep)) {
                Compare::where('user_id', $userId)
                    ->whereNotIn('id', $idsToKeep)
                    ->delete();
            }

            $count = Compare::where('user_id', $userId)->count();
        }

        // Guest user
        else {

            $guestCompare = session()->get('guest_compare', []);

            // Duplicate check
            if (in_array($productId, $guestCompare)) {

                return response()->json([
                    'error' => 'Product already in compare list.',
                    'count' => count($guestCompare),
                ]);
            }

            // Maximum 3 products
            if (count($guestCompare) >= 3) {

                return response()->json([
                    'error' => 'Maximum 3 products can be compared.',
                    'count' => count($guestCompare),
                ]);
            }

            $guestCompare[] = $productId;
            $guestCompare = array_slice($guestCompare, 0, 3);

            session()->put('guest_compare', $guestCompare);

            $count = count($guestCompare);
        }

        session()->flash('success', 'Product added to compare list!');

        return response()->json([
            'success' => 'Product added to compare list!',
            'count' => $count,
        ]);
    }

    public function remove(Request $request, $id)
    {
        if (session('user_logged_in') && session('user_profile.id')) {
            Compare::where('id', $id)
                ->where('user_id', session('user_profile.id'))
                ->delete();
            $count = Compare::where('user_id', session('user_profile.id'))->count();
        } else {
            $guestCompare = session()->get('guest_compare', []);
            $guestCompare = array_values(array_diff($guestCompare, [(int) $id]));
            session()->put('guest_compare', $guestCompare);
            $count = count($guestCompare);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'message' => 'Product removed from compare list.',
                'count' => $count,
            ]);
        }

        return back()->with('success', 'Product removed from compare list.');
    }

    public function clear(Request $request)
    {
        if (session('user_logged_in') && session('user_profile.id')) {

            Compare::where('user_id', session('user_profile.id'))
                ->delete();

        } else {

            session()->forget('guest_compare');
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'message' => 'Compare list cleared successfully.',
                'count' => 0,
            ]);
        }

        return back()->with('success', 'Compare list cleared successfully.');
    }
}
