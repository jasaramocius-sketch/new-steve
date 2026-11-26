<?php

namespace App\Support;

use App\Models\EmailTemplate;

class EmailTemplates
{
    public static function defaults(): array
    {
        $app = config('app.name', 'Steve Autoparts');

        return [
            'order_confirmation' => [
                'name' => 'Order Confirmation',
                'subject' => 'Order Confirmation - #{{order_number}}',
                'description' => 'Sent when a customer places an order.',
                'placeholders' => ['customer_name', 'order_number', 'order_date', 'order_total', 'payment_method', 'shipping_address', 'order_items_html', 'order_url', 'app_name'],
                'body_html' => self::wrap('
                    <h1 style="font-size:20px;margin:0 0 8px;">Thank You for Your Order!</h1>
                    <p style="font-size:14px;line-height:1.6;margin:0 0 20px;">
                      Hi {{customer_name}},<br>
                      Your order <strong>#{{order_number}}</strong> has been placed successfully on <strong>{{order_date}}</strong>.
                    </p>
                    {{order_items_html}}
                    <p style="font-size:14px;line-height:1.6;margin:0 0 20px;">
                      <strong>Payment method:</strong> {{payment_method}}<br>
                      <strong>Shipping address:</strong><br>{{shipping_address}}
                    </p>
                    <p style="font-size:14px;line-height:1.6;margin:0 0 24px;">
                      You can track your order from your account.
                    </p>
                    <a href="{{order_url}}" style="display:inline-block;background:#1d3557;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;font-size:14px;">View Order</a>
                '),
            ],
            'payment_received' => [
                'name' => 'Payment Received',
                'subject' => 'Payment Received - #{{order_number}}',
                'description' => 'Sent when order payment is marked as paid/received.',
                'placeholders' => ['customer_name', 'order_number', 'order_date', 'order_total', 'payment_method', 'order_url', 'app_name'],
                'body_html' => self::wrap('
                    <h1 style="font-size:20px;margin:0 0 8px;">Payment Received</h1>
                    <p style="font-size:14px;line-height:1.6;margin:0 0 20px;">
                      Hi {{customer_name}},<br>
                      We have received payment for your order <strong>#{{order_number}}</strong>.
                    </p>
                    <p style="font-size:14px;line-height:1.6;margin:0 0 20px;">
                      <strong>Amount:</strong> {{order_total}}<br>
                      <strong>Payment method:</strong> {{payment_method}}<br>
                      <strong>Order date:</strong> {{order_date}}
                    </p>
                    <a href="{{order_url}}" style="display:inline-block;background:#1d3557;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;font-size:14px;">View Order</a>
                '),
            ],
            'order_shipped' => [
                'name' => 'Order Shipped',
                'subject' => 'Your Order #{{order_number}} Has Been Shipped',
                'description' => 'Sent when an order is shipped. Include tracking details.',
                'placeholders' => ['customer_name', 'order_number', 'tracking_number', 'tracking_carrier', 'tracking_url', 'order_url', 'app_name'],
                'body_html' => self::wrap('
                    <h1 style="font-size:20px;margin:0 0 8px;">Your Order Has Been Shipped</h1>
                    <p style="font-size:14px;line-height:1.6;margin:0 0 20px;">
                      Hi {{customer_name}},<br>
                      Your order <strong>#{{order_number}}</strong> has been shipped.
                    </p>
                    <p style="font-size:14px;line-height:1.6;margin:0 0 20px;">
                      <strong>Carrier:</strong> {{tracking_carrier}}<br>
                      <strong>Tracking number:</strong> {{tracking_number}}
                    </p>
                    {{tracking_url_block}}
                    <a href="{{order_url}}" style="display:inline-block;background:#1d3557;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;font-size:14px;">View Order</a>
                '),
            ],
            'order_delivered' => [
                'name' => 'Order Delivered',
                'subject' => 'Your Order #{{order_number}} Has Been Delivered',
                'description' => 'Sent when an order is marked delivered.',
                'placeholders' => ['customer_name', 'order_number', 'order_url', 'app_name'],
                'body_html' => self::wrap('
                    <h1 style="font-size:20px;margin:0 0 8px;">Your Order Has Been Delivered</h1>
                    <p style="font-size:14px;line-height:1.6;margin:0 0 20px;">
                      Hi {{customer_name}},<br>
                      Your order <strong>#{{order_number}}</strong> has been delivered. We hope you enjoy your purchase!
                    </p>
                    <a href="{{order_url}}" style="display:inline-block;background:#1d3557;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;font-size:14px;">View Order</a>
                '),
            ],
            'refund_processed' => [
                'name' => 'Refund Processed',
                'subject' => 'Refund Processed - #{{order_number}}',
                'description' => 'Sent when a refund is processed for an order/return.',
                'placeholders' => ['customer_name', 'order_number', 'refund_amount', 'refund_method', 'refund_reference', 'order_url', 'app_name'],
                'body_html' => self::wrap('
                    <h1 style="font-size:20px;margin:0 0 8px;">Refund Processed</h1>
                    <p style="font-size:14px;line-height:1.6;margin:0 0 20px;">
                      Hi {{customer_name}},<br>
                      A refund has been processed for order <strong>#{{order_number}}</strong>.
                    </p>
                    <p style="font-size:14px;line-height:1.6;margin:0 0 20px;">
                      <strong>Refund amount:</strong> {{refund_amount}}<br>
                      <strong>Method:</strong> {{refund_method}}<br>
                      <strong>Reference:</strong> {{refund_reference}}
                    </p>
                    <a href="{{order_url}}" style="display:inline-block;background:#1d3557;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;font-size:14px;">View Order</a>
                '),
            ],
            'return_status' => [
                'name' => 'Return Status Update',
                'subject' => 'Return Request Update - #{{order_number}}',
                'description' => 'Sent when a return request status changes.',
                'placeholders' => ['customer_name', 'order_number', 'return_status', 'return_reason', 'admin_note', 'order_url', 'app_name'],
                'body_html' => self::wrap('
                    <h1 style="font-size:20px;margin:0 0 8px;">Return Request Update</h1>
                    <p style="font-size:14px;line-height:1.6;margin:0 0 20px;">
                      Hi {{customer_name}},<br>
                      The status of your return request for order <strong>#{{order_number}}</strong> has been updated.
                    </p>
                    <p style="font-size:14px;line-height:1.6;margin:0 0 20px;">
                      <strong>Status:</strong> {{return_status}}<br>
                      <strong>Reason:</strong> {{return_reason}}
                    </p>
                    {{admin_note_block}}
                    <a href="{{order_url}}" style="display:inline-block;background:#1d3557;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;font-size:14px;">View Order</a>
                '),
            ],
            'password_reset' => [
                'name' => 'Password Reset',
                'subject' => 'Reset Your Password - {{app_name}}',
                'description' => 'Sent when a user requests a password reset.',
                'placeholders' => ['customer_name', 'reset_url', 'app_name'],
                'body_html' => self::wrap('
                    <h1 style="font-size:20px;margin:0 0 8px;">Reset Your Password</h1>
                    <p style="font-size:14px;line-height:1.6;margin:0 0 20px;">
                      Hi {{customer_name}},<br>
                      We received a request to reset your password. Click the button below to set a new password.
                    </p>
                    <a href="{{reset_url}}" style="display:inline-block;background:#1d3557;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;font-size:14px;">Reset Password</a>
                    <p style="font-size:13px;line-height:1.6;margin:24px 0 0;color:#666;">
                      If you did not request a password reset, you can ignore this email.
                    </p>
                '),
            ],
        ];
    }

    public static function wrap(string $content): string
    {
        $app = config('app.name', 'Steve Autoparts');
        return '<!DOCTYPE html><html lang="en"><body style="margin:0;padding:0;background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;color:#333;">'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f8;padding:24px 0;"><tr><td align="center">'
            . '<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:8px;overflow:hidden;">'
            . '<tr><td style="background:#1d3557;padding:24px 32px;"><span style="font-size:20px;font-weight:bold;color:#fff;">{{app_name}}</span></td></tr>'
            . '<tr><td style="padding:32px;">' . trim($content) . '</td></tr>'
            . '<tr><td style="padding:16px 32px;background:#f8f9fa;font-size:12px;color:#999;">&copy; {{year}} {{app_name}}. All rights reserved.</td></tr>'
            . '</table></td></tr></table></body></html>';
    }

    public static function ensureDefaults(): void
    {
        foreach (self::defaults() as $key => $def) {
            EmailTemplate::query()->firstOrCreate(
                ['key' => $key],
                [
                    'name' => $def['name'],
                    'subject' => $def['subject'],
                    'body_html' => $def['body_html'],
                    'description' => $def['description'] ?? null,
                    'placeholders' => $def['placeholders'] ?? [],
                    'is_enabled' => true,
                ]
            );
        }
    }

    public static function resolve(string $key): ?EmailTemplate
    {
        self::ensureDefaults();
        return EmailTemplate::query()->where('key', $key)->first();
    }

    public static function render(string $key, array $data = []): ?array
    {
        $tpl = self::resolve($key);
        if (!$tpl || !$tpl->is_enabled) {
            return null;
        }

        $data = array_merge([
            'app_name' => config('app.name', 'Steve Autoparts'),
            'year' => date('Y'),
        ], $data);

        $replace = [];
        foreach ($data as $k => $v) {
            $replace['{{' . $k . '}}'] = is_scalar($v) || $v === null ? (string) $v : '';
        }

        $subject = strtr((string) $tpl->subject, $replace);
        $body = strtr((string) $tpl->body_html, $replace);

        // Remove any unreplaced placeholders to avoid leaking {{...}} to users.
        $subject = preg_replace('/\{\{[a-zA-Z0-9_.]+\}\}/', '', $subject) ?? $subject;
        $body = preg_replace('/\{\{[a-zA-Z0-9_.]+\}\}/', '', $body) ?? $body;

        return [
            'subject' => trim($subject),
            'body_html' => $body,
            'template' => $tpl,
        ];
    }

    public static function default(string $key): ?array
    {
        return self::defaults()[$key] ?? null;
    }
}
