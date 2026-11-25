<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Single source of truth for Features activation.
 *
 * Every legacy toggle is kept here. Runtime consumers call enabled(); toggles
 * whose provider integration is not implemented are flagged and never exposed
 * as actionable storefront options.
 */
class FeatureFlags
{
    /** @var array<string, string>|null */
    private static ?array $settings = null;

    /** Payment toggles that have a checkout adapter. Credentials are checked at runtime. */
    private const SUPPORTED_PAYMENT_KEYS = [
        'stripe_payment',
        'paypal_payment',
        'mercadopago_payment',
        'sslcommerz_payment',
        'instamojo_payment',
        'razorpay',
        'paystack',
        'voguepay',
        'payhere',
        'ngenius',
        'iyzico',
        'bkash',
        'nagad',
        'proxypay',
        'aamarpay',
        'authorizenet',
        'payku',
        'cash_payment',
    ];

    /**
     * Feature groups shown on Admin > Settings > Features activation.
     */
    public static function definitions(): array
    {
        $legacyPayment = static fn (string $key, string $label, bool $default = false): array => [
            'key' => $key,
            'label' => $label,
            'default' => $default,
            'configure' => 'admin.settings.payment-methods',
            'configureLabel' => 'Configure credentials',
        ];

        return [
            [
                'title' => 'System',
                'items' => [
                    ['key' => 'FORCE_HTTPS', 'label' => 'HTTPS Activation', 'default' => false],
                    ['key' => 'maintenance_mode', 'label' => 'Maintenance Mode Activation', 'default' => false],
                    ['key' => 'disable_image_optimization', 'label' => 'Disable image encoding?', 'default' => false],
                ],
            ],
            [
                'title' => 'Business Related',
                'items' => [
                    [
                        'key' => 'vendor_system_activation',
                        'label' => 'Vendor System Activation',
                        'default' => false,
                        'description' => 'Controls seller records, followed sellers and seller assignment on products.',
                    ],
                    ['key' => 'classified_product', 'label' => 'Classified Product', 'default' => true],
                    [
                        'key' => 'wallet_system',
                        'label' => 'Wallet System Activation (Legacy)',
                        'default' => false,
                        'description' => 'The wallet page and dashboard link have been removed. This toggle is kept only for legacy settings compatibility.',
                    ],
                    ['key' => 'coupon_system', 'label' => 'Coupon System Activation', 'default' => true],
                    ['key' => 'pickup_point', 'label' => 'Pickup Point Activation', 'default' => true],
                    [
                        'key' => 'conversation_system',
                        'label' => 'Conversation Activation (Legacy)',
                        'default' => true,
                        'description' => 'Product conversation messages now live in Questions / Inquiries. This toggle is kept for legacy settings compatibility.',
                    ],
                    [
                        'key' => 'product_manage_by_admin',
                        'label' => 'Seller Product Manage By Admin',
                        'default' => false,
                        'description' => 'When on, admins can assign and manage seller products. When off, seller assignment is hidden.',
                    ],
                    [
                        'key' => 'product_approve_by_admin',
                        'label' => 'Admin Approval On Seller Product',
                        'default' => false,
                        'description' => 'Seller products stay pending until an admin approves them.',
                    ],
                    [
                        'key' => 'email_verification',
                        'label' => 'Email Verification',
                        'default' => true,
                        'always_on' => true,
                        'description' => 'Always required for new customer accounts. Configure SMTP to enable delivery.',
                        'configure' => 'admin.settings.smtp',
                        'configureLabel' => 'Configure SMTP',
                    ],
                    [
                        'key' => 'product_query_activation',
                        'label' => 'Product Query Activation',
                        'default' => false,
                        'description' => 'Shows the “Ask a question” action on product pages.',
                    ],
                ],
            ],
            [
                'title' => 'Payment Related',
                'items' => [
                    [
                        'key' => 'stripe_payment',
                        'label' => 'Stripe Payment Activation',
                        'default' => true,
                        'configure' => 'admin.settings.payment-methods',
                        'configureLabel' => 'Configure Stripe',
                    ],
                    [
                        'key' => 'paypal_payment',
                        'label' => 'PayPal Payment Activation',
                        'default' => false,
                        'configure' => 'admin.settings.payment-methods',
                        'configureLabel' => 'Configure PayPal',
                    ],
                    $legacyPayment('mercadopago_payment', 'MercadoPago Payment Activation'),
                    $legacyPayment('sslcommerz_payment', 'SSlCommerz Activation'),
                    $legacyPayment('instamojo_payment', 'Instamojo Payment Activation'),
                    $legacyPayment('razorpay', 'Razor Pay Activation'),
                    $legacyPayment('paystack', 'PayStack Activation'),
                    $legacyPayment('voguepay', 'VoguePay Activation'),
                    $legacyPayment('payhere', 'Payhere Activation'),
                    $legacyPayment('ngenius', 'Ngenius Activation'),
                    $legacyPayment('iyzico', 'Iyzico Activation'),
                    $legacyPayment('bkash', 'Bkash Activation'),
                    $legacyPayment('nagad', 'Nagad Activation'),
                    $legacyPayment('proxypay', 'Proxy Pay Activation', true),
                    $legacyPayment('aamarpay', 'Amarpay Activation'),
                    $legacyPayment('authorizenet', 'Authorize Net Activation'),
                    $legacyPayment('payku', 'Payku Activation'),
                    ['key' => 'cash_payment', 'label' => 'Cash Payment Activation', 'default' => true],
                ],
            ],
            [
                'title' => 'Social Media Login',
                'items' => [
                    [
                        'key' => 'facebook_login',
                        'label' => 'Facebook login',
                        'default' => false,
                        'integration_required' => true,
                        'configure' => 'admin.settings.social-logins',
                        'configureLabel' => 'Configure Facebook',
                    ],
                    [
                        'key' => 'google_login',
                        'label' => 'Google login',
                        'default' => false,
                        'integration_required' => true,
                        'configure' => 'admin.settings.social-logins',
                        'configureLabel' => 'Configure Google',
                    ],
                    [
                        'key' => 'twitter_login',
                        'label' => 'Twitter login',
                        'default' => false,
                        'integration_required' => true,
                        'configure' => 'admin.settings.social-logins',
                        'configureLabel' => 'Configure Twitter',
                    ],
                    [
                        'key' => 'apple_login',
                        'label' => 'Apple login',
                        'default' => false,
                        'integration_required' => true,
                        'configure' => 'admin.settings.social-logins',
                        'configureLabel' => 'Configure Apple',
                    ],
                ],
            ],
        ];
    }

    /**
     * All configurable keys from the definitions above.
     */
    public static function keys(): array
    {
        $keys = [];

        foreach (self::items() as $item) {
            if (empty($item['always_on'])) {
                $keys[] = $item['key'];
            }
        }

        return $keys;
    }

    /**
     * Flattened feature definitions.
     */
    public static function items(): array
    {
        $items = [];

        foreach (self::definitions() as $group) {
            foreach ($group['items'] as $item) {
                $item['group'] = $group['title'];
                $items[] = $item;
            }
        }

        return $items;
    }

    /**
     * Resolve a feature at runtime.
     */
    public static function enabled(string $key): bool
    {
        // Customer email verification is a hard requirement in this app; the
        // feature is shown as always-on in admin instead of being toggleable.
        if ($key === 'email_verification') {
            return true;
        }

        $default = false;

        foreach (self::items() as $item) {
            if ($item['key'] === $key) {
                $default = (bool) ($item['default'] ?? false);
                break;
            }
        }

        $value = self::settings()[$key] ?? ($default ? '1' : '0');

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * True when the toggle exists but its provider flow is not implemented.
     */
    public static function integrationRequired(string $key): bool
    {
        foreach (self::items() as $item) {
            if ($item['key'] === $key) {
                return (bool) ($item['integration_required'] ?? false);
            }
        }

        return false;
    }

    /**
     * Payment methods that have a real checkout handler.
     */
    public static function supportedPaymentKeys(): array
    {
        return self::SUPPORTED_PAYMENT_KEYS;
    }

    /**
     * Payment methods safe to offer at checkout right now.
     *
     * The gateway manager only returns a method when its feature toggle is on
     * and its saved credentials are complete.
     */
    public static function checkoutPaymentMethods(): array
    {
        return app(\App\Services\Payments\PaymentGatewayManager::class)->availableMethods();
    }

    /**
     * Load all settings once per request.
     */
    public static function settings(): array
    {
        if (self::$settings === null) {
            self::$settings = Setting::getAllAsArray();
        }

        return self::$settings;
    }

    /**
     * Clear the in-request cache after a setting is written.
     */
    public static function flush(): void
    {
        self::$settings = null;
    }
}
