<?php

namespace App\Services;

use App\Models\OrderItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ProductReviewEligibility
{
    /**
     * Get the non-cancelled orders in which the user purchased the product.
     *
     * @return Collection<int, \App\Models\Order>
     */
    public function purchasedOrders(int $userId, int $productId): Collection
    {
        return OrderItem::query()
            ->where('product_id', $productId)
            ->whereHas('order', function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->where('status', '!=', 'cancelled');
            })
            ->with(['order' => function ($query) {
                $query->select('id', 'order_number', 'user_id', 'status', 'created_at');
            }])
            ->get()
            ->pluck('order')
            ->filter()
            ->unique('id')
            ->sortBy(fn ($order) => $order->created_at?->timestamp ?? 0)
            ->values();
    }

    /**
     * Map each review to the purchase/order it belongs to for display.
     *
     * New reviews store an `order_id`. Legacy reviews (created before
     * order-wise reviews) have no order_id; they are mapped to the latest
     * purchase made on or before the review date.
     *
     * @return array<string, int> review key => order id
     */
    public function allocateReviews(array $reviews, int $userId, Collection $purchasedOrders): array
    {
        $purchasedById = $purchasedOrders->keyBy('id');
        $allocated = [];

        foreach ($reviews as $index => $review) {
            if (! is_array($review)
                || (int) ($review['user_id'] ?? 0) !== $userId
                || ($review['deleted'] ?? false)) {
                continue;
            }

            $key = (string) ($review['id'] ?? 'legacy-'.$index);
            $orderId = (int) ($review['order_id'] ?? 0);

            if ($orderId > 0) {
                // A review tied to a cancelled/unknown order must not be
                // shown as reviewing a different purchase.
                if ($purchasedById->has($orderId)) {
                    $allocated[$key] = $orderId;
                }

                continue;
            }

            $reviewDate = $this->reviewDate($review);
            $order = null;

            if ($reviewDate) {
                $order = $purchasedOrders
                    ->filter(fn ($order) => $order->created_at && $order->created_at->lte($reviewDate))
                    ->sortBy(fn ($order) => $order->created_at->timestamp)
                    ->last();
            }

            $order ??= $purchasedOrders
                ->sortBy(fn ($order) => $order->created_at?->timestamp ?? 0)
                ->first();

            if ($order) {
                $allocated[$key] = (int) $order->id;
            }
        }

        return $allocated;
    }

    /**
     * Get the order IDs that are already covered by a user review.
     *
     * New reviews cover their stored order_id. Legacy reviews with a date
     * cover all purchases made on or before that date, so only purchases
     * after the old review become eligible again.
     *
     * @return array<int, int>
     */
    public function reviewedOrderIds(array $reviews, int $userId, Collection $purchasedOrders): array
    {
        $purchasedById = $purchasedOrders->keyBy('id');
        $reviewed = [];

        foreach ($reviews as $review) {
            if (! is_array($review)
                || (int) ($review['user_id'] ?? 0) !== $userId
                || ($review['deleted'] ?? false)) {
                continue;
            }

            $orderId = (int) ($review['order_id'] ?? 0);

            if ($orderId > 0) {
                if ($purchasedById->has($orderId)) {
                    $reviewed[$orderId] = true;
                }

                continue;
            }

            $reviewDate = $this->reviewDate($review);

            if ($reviewDate) {
                foreach ($purchasedOrders as $order) {
                    if ($order->created_at && $order->created_at->lte($reviewDate)) {
                        $reviewed[(int) $order->id] = true;
                    }
                }

                continue;
            }

            // Undated legacy review: cover only the earliest purchase so a
            // later repurchase can still be reviewed.
            $earliest = $purchasedOrders
                ->sortBy(fn ($order) => $order->created_at?->timestamp ?? 0)
                ->first();

            if ($earliest) {
                $reviewed[(int) $earliest->id] = true;
            }
        }

        return array_keys($reviewed);
    }

    /**
     * Find the first purchased order that has not been reviewed yet.
     */
    public function nextUnreviewedOrder(array $reviews, int $userId, Collection $purchasedOrders): mixed
    {
        $reviewedOrderIds = $this->reviewedOrderIds($reviews, $userId, $purchasedOrders);

        return $purchasedOrders->first(
            fn ($order) => ! in_array((int) $order->id, $reviewedOrderIds, true)
        );
    }

    private function reviewDate(array $review): ?Carbon
    {
        $value = $review['review_date'] ?? $review['date'] ?? null;

        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value)->endOfDay();
        } catch (\Throwable) {
            return null;
        }
    }
}
