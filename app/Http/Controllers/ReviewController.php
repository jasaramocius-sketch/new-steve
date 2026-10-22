<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReviewController extends Controller
{
    public function store(Request $request, $slug)
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
        ]);

        $hasPurchased = $this->hasPurchased(auth()->id(), $product->id);
        if (! $hasPurchased) {
            return response()->json(['success' => false, 'message' => 'You must purchase this product to write a review.'], 403);
        }

        $reviews = $product->reviews_data ?? [];

        $existingReview = collect($reviews)->first(function ($r) {
            return ($r['user_id'] ?? null) == auth()->id() && ! ($r['deleted'] ?? false);
        });
        if ($existingReview) {
            return response()->json(['success' => false, 'message' => 'You have already reviewed this product. You can edit your existing review.'], 403);
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

        return OrderItem::where('product_id', $productId)
            ->whereHas('order', function ($q) use ($userId) {
                $q->where('user_id', $userId)
                    ->where('status', '!=', 'cancelled');
            })
            ->exists();
    }

    private function recomputeRating(Product $product, array $reviews)
    {
        $visible = collect($reviews)->where('deleted', false);
        $avg = $visible->avg('rating');

        $product->reviews_data = $reviews;
        $product->rating = $visible->isEmpty() ? 0 : round($avg);
        $product->reviews = $visible->count();
        $product->save();
    }
}
