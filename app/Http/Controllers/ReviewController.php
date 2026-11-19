<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ProductReviewEligibility;
use App\Services\ProductReviewSync;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReviewController extends Controller
{
    public function store(Request $request, $slug, ProductReviewEligibility $eligibility)
    {
        if ($request->isMethod('get')) {
            return redirect()->route('product', $slug);
        }

        $product = Product::where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'text' => 'required|string|max:1000',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
            'order_id' => 'nullable|integer',
        ]);

        $userId = (int) auth()->id();
        $purchasedOrders = $eligibility->purchasedOrders($userId, $product->id);

        if ($purchasedOrders->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'You must purchase this product to write a review.'], 403);
        }

        $reviews = $product->reviews_data ?? [];
        $reviewedOrderIds = $eligibility->reviewedOrderIds($reviews, $userId, $purchasedOrders);

        $requestedOrderId = (int) ($validated['order_id'] ?? 0);

        if ($requestedOrderId > 0) {
            $order = $purchasedOrders->first(
                fn ($purchase) => (int) $purchase->id === $requestedOrderId
            );

            if (! $order) {
                return response()->json(['success' => false, 'message' => 'This order is not eligible for a review.'], 403);
            }

            if (in_array($requestedOrderId, $reviewedOrderIds, true)) {
                return response()->json(['success' => false, 'message' => 'You have already reviewed this product for this order.'], 403);
            }
        } else {
            $order = $purchasedOrders->first(
                fn ($purchase) => ! in_array((int) $purchase->id, $reviewedOrderIds, true)
            );

            if (! $order) {
                return response()->json(['success' => false, 'message' => 'You have already reviewed this product for all your purchases. You can edit your existing review.'], 403);
            }
        }

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePaths[] = saveImageWithWebp($image);
            }
        }

        $review = [
            'id' => 'r_'.time().'_'.Str::random(6),
            'user_id' => auth()->id(),
            'name' => auth()->user()->name,
            'rating' => (int) $validated['rating'],
            'text' => $validated['text'],
            'images' => $imagePaths,
            'date' => now()->format('M d, Y'),
            'review_date' => now()->toDateString(),
            'order_id' => (int) $order->id,
            'order_number' => $order->order_number,
            'purchase_date' => $order->created_at?->toDateString(),
            'deleted' => false,
        ];

        $reviews[] = $review;

        $this->recomputeRating($product, $reviews);

        return response()->json([
            'success' => true,
            'review' => $review,
            'rating' => $product->rating,
            'count' => $product->reviews,
        ]);
    }

    public function destroy(Request $request, $slug, $reviewId)
    {
        $product = Product::where('slug', $slug)->firstOrFail();

        $reviews = $product->reviews_data ?? [];
        $found = false;

        foreach ($reviews as &$r) {
            if (($r['id'] ?? '') === $reviewId) {
                if (($r['user_id'] ?? null) != auth()->id()) {
                    return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
                }
                $r['deleted'] = true;
                $found = true;
                break;
            }
        }
        unset($r);

        if (! $found) {
            return response()->json(['success' => false, 'message' => 'Review not found'], 404);
        }

        $this->recomputeRating($product, $reviews);

        return response()->json([
            'success' => true,
            'rating' => $product->rating,
            'count' => $product->reviews,
        ]);
    }

    public function update(Request $request, $slug, $reviewId)
    {
        $product = Product::where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'text' => 'required|string|max:1000',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $reviews = $product->reviews_data ?? [];
        $found = false;

        foreach ($reviews as &$r) {
            if (($r['id'] ?? '') === $reviewId) {
                if (($r['deleted'] ?? false)) {
                    continue;
                }
                if (($r['user_id'] ?? null) != auth()->id()) {
                    return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
                }
                $r['rating'] = (int) $validated['rating'];
                $r['text'] = $validated['text'];
                $found = true;

                if ($request->hasFile('images')) {
                    $imagePaths = $r['images'] ?? [];
                    foreach ($request->file('images') as $image) {
                        $imagePaths[] = saveImageWithWebp($image);
                    }
                    $r['images'] = $imagePaths;
                }

                break;
            }
        }
        unset($r);

        if (! $found) {
            return response()->json(['success' => false, 'message' => 'Review not found'], 404);
        }

        $this->recomputeRating($product, $reviews);

        $updatedReview = collect($reviews)->firstWhere('id', $reviewId);

        return response()->json([
            'success' => true,
            'review' => $updatedReview,
            'rating' => $product->rating,
            'count' => $product->reviews,
        ]);
    }

    public static function hasPurchased($userId, $productId)
    {
        if (! $userId) {
            return false;
        }

        return app(ProductReviewEligibility::class)
            ->purchasedOrders((int) $userId, (int) $productId)
            ->isNotEmpty();
    }

    private function recomputeRating(Product $product, array $reviews)
    {
        $visible = collect($reviews)->where('deleted', false);
        $avg = $visible->avg('rating');

        DB::transaction(function () use ($product, $reviews, $visible, $avg) {
            $product->reviews_data = $reviews;
            $product->rating = $visible->isEmpty() ? 0 : round($avg);
            $product->reviews = $visible->count();
            $product->save();

            app(ProductReviewSync::class)->sync($product, $reviews);
        });
    }
}
