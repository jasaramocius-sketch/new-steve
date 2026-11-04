<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    /**
     * Toggle product in wishlist (add if not present, remove if present)
     */
    public function add(Request $request)
    {
        $productId = $request->product_id;

        if (session('user_profile.id')) {
            $userId = session('user_profile.id');
            $wishlistItem = Wishlist::where('user_id', $userId)
                ->where('product_id', $productId)
                ->first();

            if ($wishlistItem) {
                $wishlistItem->delete();
                $action = 'removed';
                $message = 'Product removed from wishlist!';
            } else {
                Wishlist::create([
                    'user_id' => $userId,
                    'product_id' => $productId,
                ]);
                $action = 'added';
                $message = 'Product added to wishlist!';
            }

            $count = Wishlist::where('user_id', $userId)->count();
        } else {
            $wishlist = session()->get('guest_wishlist', []);

            if (in_array($productId, $wishlist)) {
                $wishlist = array_diff($wishlist, [$productId]);
                $action = 'removed';
                $message = 'Product removed from wishlist!';
            } else {
                $wishlist[] = $productId;
                $action = 'added';
                $message = 'Product added to wishlist!';
            }

            session()->put('guest_wishlist', $wishlist);
            $count = count($wishlist);
        }

        return response()->json([
            'success' => true,
            'action' => $action,
            'message' => $message,
            'count' => $count,
        ]);
    }

    /**
     * Wishlist Page
     */
    public function index()
    {
        // Logged In User Wishlist
        if (session('user_profile.id')) {

            $wishlist = Wishlist::with('product')
                ->where('user_id', session('user_profile.id'))
                ->latest()
                ->get();

            return view('user.wishlist', compact('wishlist'));
        }

        // Guest Wishlist
        $guestWishlist = session()->get('guest_wishlist', []);

        $wishlist = collect();

        if (! empty($guestWishlist)) {

            $products = Product::whereIn(
                'id',
                $guestWishlist
            )->get();

            foreach ($products as $product) {

                $wishlist->push((object) [
                    'id' => $product->id,
                    'product_id' => $product->id,
                    'product' => $product,
                ]);
            }
        }

        return view('user.wishlist', compact('wishlist'));
    }

    /**
     * Remove Product
     */
    public function remove(Request $request, $id)
    {
        // Logged In User
        if (session('user_profile.id') && session('user_profile.id')) {

            Wishlist::where('id', $id)
                ->where(
                    'user_id',
                    session('user_profile.id')
                )
                ->delete();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product removed from wishlist.',
                    'count' => Wishlist::where('user_id', session('user_profile.id'))->count(),
                ]);
            }

            return back()->with(
                'success',
                'Product removed from wishlist'
            );
        }

        // Guest User
        $wishlist = session()->get(
            'guest_wishlist',
            []
        );

        $wishlist = array_diff(
            $wishlist,
            [$id]
        );

        session()->put(
            'guest_wishlist',
            $wishlist
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Product removed from wishlist.',
                'count' => count($wishlist),
            ]);
        }

        return back()->with(
            'success',
            'Product removed from wishlist'
        );
    }

    /**
     * Move Wishlist Product To Cart
     */
    public function moveToCart(Request $request)
    {
        $wishlist = Wishlist::with('product')
            ->findOrFail($request->wishlist_id);

        $cart = session()->get('cart', []);

        $productId = $wishlist->product_id;

        if (isset($cart[$productId])) {
            $cart[$productId]['qty']++;
        } else {
            $cart[$productId] = [
                'id' => $wishlist->product->id,
                'name' => $wishlist->product->name,
                'price' => buyerPrice($wishlist->product->price, $wishlist->product),
                'image' => storedImageUrl($wishlist->product->image, 'assets/images/thumbnails'),
                'qty' => 1,
            ];
        }

        session()->put('cart', $cart);

        $wishlist->delete();

        return back()->with(
            'success',
            'Product moved to cart'
        );
    }

    /**
     * Clear All Wishlist
     */
    public function clearAll()
    {
        if (session('user_profile.id')) {
            Wishlist::where('user_id', session('user_profile.id'))->delete();
        } else {
            session()->forget('guest_wishlist');
        }

        return back()->with('success', 'Wishlist cleared successfully');
    }

    /**
     * Wishlist Count
     */
    public function count()
    {
        if (session('user_profile.id')) {

            return Wishlist::where(
                'user_id',
                session('user_profile.id')
            )->count();
        }

        return count(
            session()->get(
                'guest_wishlist',
                []
            )
        );
    }
}
