<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ProductReviewSync;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::whereNotNull('reviews_data')
            ->orderBy('updated_at', 'desc')
            ->get(['id', 'name', 'slug', 'main_image', 'reviews_data']);

        $items = [];
        foreach ($products as $product) {
            $reviews = is_array($product->reviews_data) ? $product->reviews_data : [];
            foreach ($reviews as $review) {
                if ($review['deleted'] ?? false) {
                    continue;
                }
                $items[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_slug' => $product->slug,
                    'product_image' => $product->image,
                    'review_id' => $review['id'] ?? '',
                    'user_name' => $review['user_name'] ?? $review['name'] ?? 'Anonymous',
                    'user_id' => $review['user_id'] ?? null,
                    'rating' => $review['rating'] ?? 0,
                    'review' => $review['review'] ?? $review['text'] ?? '',
                    'images' => $review['images'] ?? [],
                    'date' => $review['created_at'] ?? $review['date'] ?? $product->updated_at->format('Y-m-d H:i:s'),
                    'approved' => ($review['approved'] ?? true) !== false,
                ];
            }
        }

        $items = collect($items);

        $status = $request->input('status', '');
        if ($status === 'approved') {
            $items = $items->where('approved', true)->values();
        } elseif ($status === 'disapproved') {
            $items = $items->where('approved', false)->values();
        }

        $search = trim($request->input('search', ''));
        if ($search !== '') {
            $items = $items->filter(function ($item) use ($search) {
                return stripos($item['product_name'], $search) !== false
                    || stripos($item['user_name'], $search) !== false
                    || stripos($item['review'], $search) !== false;
            })->values();
        }

        $perPage = 15;
        $currentPage = max((int) $request->input('page', 1), 1);
        $paginated = new LengthAwarePaginator(
            $items->slice(($currentPage - 1) * $perPage, $perPage),
            $items->count(),
            $perPage,
            $currentPage,
            ['path' => route('admin.reviews.index', array_filter(['status' => $status, 'search' => $search]))]
        );

        return view('admin.reviews.index', [
            'reviews' => $paginated,
            'status' => $status,
            'search' => $search,
        ]);
    }

    public function toggleApproved(Request $request, $productId, $reviewId)
    {
        $product = Product::findOrFail($productId);
        $reviews = is_array($product->reviews_data) ? $product->reviews_data : [];

        foreach ($reviews as &$review) {
            if (($review['id'] ?? '') === $reviewId) {
                $review['approved'] = ($review['approved'] ?? true) === false;
                break;
            }
        }

        $product->update(['reviews_data' => array_values($reviews)]);
        app(ProductReviewSync::class)->sync($product, array_values($reviews));

        return back()->with('success', 'Review visibility updated.');
    }

    public function destroy($productId, $reviewId)
    {
        $product = Product::findOrFail($productId);
        $reviews = is_array($product->reviews_data) ? $product->reviews_data : [];

        foreach ($reviews as &$review) {
            if (($review['id'] ?? '') === $reviewId) {
                $review['deleted'] = true;
                break;
            }
        }

        $product->update(['reviews_data' => array_values($reviews)]);
        app(ProductReviewSync::class)->sync($product, array_values($reviews));

        return back()->with('success', 'Review deleted.');
    }
}