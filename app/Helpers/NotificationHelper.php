<?php

namespace App\Helpers;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

class NotificationHelper
{
    public static function orderPlaced(Order $order): ?Notification
    {
        if (! $order->user_id) {
            return null;
        }

        return Notification::create([
            'user_id' => $order->user_id,
            'title' => 'Order Placed Successfully',
            'message' => 'Your order <a href="'.route('user.orders.show', $order->id)."\" class=\"text-decoration-underline\">#{$order->order_number}</a> has been placed successfully. Total: $".number_format($order->total_amount, 2),
            'is_read' => false,
        ]);
    }

    public static function orderStatusChanged(Order $order, ?string $oldStatus): ?Notification
    {
        if (! $order->user_id) {
            return null;
        }

        $statusLabels = [
            'pending' => 'Pending',
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
        ];

        $newLabel = $statusLabels[$order->status] ?? ucfirst($order->status);

        return Notification::create([
            'user_id' => $order->user_id,
            'title' => 'Order Status Updated',
            'message' => "Your order #{$order->order_number} status has been updated to {$newLabel}.",
            'is_read' => false,
        ]);
    }

    public static function welcomeUser(User $user): ?Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'title' => 'Welcome to STAutoParts!',
            'message' => 'Hi '.e($user->name).', welcome to STAutoParts! Browse our wide range of auto parts and enjoy shopping.',
            'is_read' => false,
        ]);
    }

    public static function newProductInCategory(User $user, Product $product, Category $category): ?Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'title' => 'New Product Available',
            'message' => "A new product \"{$product->name}\" has been added in {$category->name}. Check it out!",
            'is_read' => false,
        ]);
    }

    public static function promotion(User $user, string $title, string $message): ?Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'is_read' => false,
        ]);
    }

    public static function bulkPromotion(array $userIds, string $title, string $message): int
    {
        $data = array_map(fn ($userId) => [
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'is_read' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ], $userIds);

        return Notification::insert($data) ? count($data) : 0;
    }

    public static function inquiryReplied(Contact $contact): ?Notification
    {
        if (! $contact->user_id) {
            return null;
        }

        $subject = $contact->subject ?: 'Your Inquiry';
        $link = route('user.inquiries');

        return Notification::create([
            'user_id' => $contact->user_id,
            'title' => 'Reply to Your Inquiry',
            'message' => "Your inquiry \"{$subject}\" has been replied to by our team. <a href=\"{$link}\" class=\"text-decoration-underline\">View reply</a>",
            'is_read' => false,
        ]);
    }
}
