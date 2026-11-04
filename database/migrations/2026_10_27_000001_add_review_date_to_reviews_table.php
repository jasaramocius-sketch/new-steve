<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('reviews')) {
            Schema::create('reviews', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('product_id');
                $table->unsignedInteger('user_id');
                $table->integer('rating')->default(0);
                $table->mediumText('comment');
                $table->string('photos')->nullable();
                $table->integer('status')->default(1);
                $table->integer('viewed')->default(0);
                $table->string('review_key', 191)->nullable();
                $table->date('review_date')->nullable();
                $table->timestamps();

                $table->index(['product_id', 'review_key']);
            });
        } else {
            if (! Schema::hasColumn('reviews', 'review_key')) {
                Schema::table('reviews', function (Blueprint $table) {
                    $table->string('review_key', 191)->nullable()->after('id');
                    $table->index(['product_id', 'review_key']);
                });
            }

            if (! Schema::hasColumn('reviews', 'review_date')) {
                Schema::table('reviews', function (Blueprint $table) {
                    $table->date('review_date')->nullable()->after('comment');
                });
            }
        }

        DB::table('products')
            ->whereNotNull('reviews_data')
            ->where('reviews_data', '<>', '')
            ->orderBy('id')
            ->chunkById(100, function ($products) {
                foreach ($products as $product) {
                    $reviews = json_decode((string) $product->reviews_data, true);
                    if (! is_array($reviews)) {
                        continue;
                    }

                    foreach ($reviews as $index => $review) {
                        if (! is_array($review)) {
                            continue;
                        }

                        $reviewKey = (string) ($review['id'] ?? 'legacy-'.$product->id.'-'.$index);
                        $reviewKey = mb_substr($reviewKey, 0, 191);
                        $dateValue = $review['review_date'] ?? $review['date'] ?? $review['created_at'] ?? null;
                        try {
                            $reviewDate = $dateValue ? Carbon::parse($dateValue)->toDateString() : null;
                        } catch (Throwable) {
                            $reviewDate = null;
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
                                'updated_at' => $now,
                                'created_at' => $now,
                            ]
                        );
                    }
                }
            });
    }

    public function down(): void
    {
        // Keep the legacy reviews table and its data intact on rollback.
    }
};
