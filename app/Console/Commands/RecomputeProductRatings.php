<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class RecomputeProductRatings extends Command
{
    protected $signature = 'products:recompute-ratings';

    protected $description = 'Recalculate rating and reviews count from reviews_data for all products';

    public function handle()
    {
        $products = Product::whereNotNull('reviews_data')->get();
        $updated = 0;

        foreach ($products as $product) {
            $visible = collect($product->reviews_data ?? [])->where('deleted', false);
            $avg = $visible->avg('rating');
            $newRating = $visible->isEmpty() ? 0 : round($avg);
            $newReviews = $visible->count();

            if ($product->rating !== $newRating || $product->reviews !== $newReviews) {
                $product->update([
                    'rating' => $newRating,
                    'reviews' => $newReviews,
                ]);
                $updated++;
            }
        }

        $this->info("Done. {$updated} products updated out of {$products->count()} total.");
    }
}
