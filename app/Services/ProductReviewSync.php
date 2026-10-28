<?php

namespace App\Services;

use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProductReviewSync
{
    public function sync(Product $product, array $reviews): void
    {
        foreach ($reviews as $index => $review) {
            if (! is_array($review)) {
                continue;
            }

            $reviewKey = mb_substr((string) ($review['id'] ?? 'legacy-'.$product->id.'-'.$index), 0, 191);
            $dateValue = $review['review_date'] ?? $review['date'] ?? $review['created_at'] ?? null;

            try {
                $reviewDate = $dateValue ? Carbon::parse($dateValue)->toDateString() : null;
                $createdAt = $dateValue ? Carbon::parse($dateValue) : now();
            } catch (\Throwable) {
                $reviewDate = null;
                $createdAt = now();
            }

            $now = now();
            DB::table('reviews')->updateOrInsert(
                ['product_id' => $product->id, 'review_key' => $reviewKey],
                [
                    'user_id' => (int) ($review['user_id'] ?? 0),
                    'rating' => (int) ($review['rating'] ?? 0),
                    'comment' => (string) ($review['text'] ?? $review['review'] ?? ''),
                    'review_date' => $reviewDate,
                    'status' => ($review['deleted'] ?? false) || (($review['approved'] ?? true) === false) ? 0 : 1,
                    'created_at' => $createdAt,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
