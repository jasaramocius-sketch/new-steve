<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;
use Throwable;

/**
 * Single source of truth for Payment Methods credentials.
 *
 * Admin settings encrypt the saved credentials; checkout adapters read the
 * same values through this class so a gateway is only offered when its
 * credentials actually exist.
 */
class PaymentMethods
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public static function definitions(): array
    {
        return [
            'paypal' => [
                'title' => 'Paypal Credential', 'sandbox' => 'paypal_sandbox',
                'fields' => [
                    ['name' => 'PAYPAL_CLIENT_ID', 'label' => 'Paypal Client Id', 'secret' => false, 'required' => true],
                    ['name' => 'PAYPAL_CLIENT_SECRET', 'label' => 'Paypal Client Secret', 'secret' => true, 'required' => true],
                ],
            ],
            'stripe' => [
                'title' => 'Stripe Credential',
                'fields' => [
                    ['name' => 'STRIPE_KEY', 'label' => 'Stripe Key', 'secret' => false, 'required' => true],
                    ['name' => 'STRIPE_SECRET', 'label' => 'Stripe Secret', 'secret' => true, 'required' => true],
                    ['name' => 'STRIPE_WEBHOOK_SECRET', 'label' => 'Stripe Webhook Signing Secret', 'secret' => true, 'required' => false, 'help' => 'Optional, but strongly recommended. Copy the signing secret from the Stripe webhook endpoint so callbacks can be verified.'],
                ],
            ],
            'mercadopago' => [
                'title' => 'Mercadopago Credential',
                'fields' => [
                    ['name' => 'MERCADOPAGO_KEY', 'label' => 'Mercadopago Key', 'secret' => false, 'required' => true],
                    ['name' => 'MERCADOPAGO_ACCESS', 'label' => 'Mercadopago Access', 'secret' => true, 'required' => true],
                    ['name' => 'MERCADOPAGO_CURRENCY', 'label' => 'Mercadopago Currency', 'secret' => false, 'required' => false, 'help' => 'Three-letter currency code for this merchant account, e.g. USD, ARS, CLP, COP, MXN. Defaults to the store currency.'],
                ],
            ],
            'bkash' => [
                'title' => 'Bkash Credential', 'sandbox' => 'bkash_sandbox', 'sandboxDefault' => true,
                'fields' => [
                    ['name' => 'BKASH_CHECKOUT_APP_KEY', 'label' => 'BKASH CHECKOUT APP KEY', 'secret' => true, 'required' => true],
                    ['name' => 'BKASH_CHECKOUT_APP_SECRET', 'label' => 'BKASH CHECKOUT APP SECRET', 'secret' => true, 'required' => true],
                    ['name' => 'BKASH_CHECKOUT_USER_NAME', 'label' => 'BKASH CHECKOUT USER NAME', 'secret' => false, 'required' => true],
                    ['name' => 'BKASH_CHECKOUT_PASSWORD', 'label' => 'BKASH CHECKOUT PASSWORD', 'secret' => true, 'required' => true],
                ],
            ],
            'nagad' => [
                'title' => 'Nagad Credential',
                'fields' => [
                    ['name' => 'NAGAD_MODE', 'label' => 'NAGAD MODE', 'secret' => false, 'required' => true, 'default' => 'sandbox', 'help' => 'Use sandbox or live. Paste the PEM keys exactly as downloaded from the Nagad merchant panel.'],
                    ['name' => 'NAGAD_MERCHANT_ID', 'label' => 'NAGAD MERCHANT ID', 'secret' => false, 'required' => true],
                    ['name' => 'NAGAD_MERCHANT_NUMBER', 'label' => 'NAGAD MERCHANT NUMBER', 'secret' => false, 'required' => true],
                    ['name' => 'NAGAD_PG_PUBLIC_KEY', 'label' => 'NAGAD PG PUBLIC KEY', 'secret' => false, 'required' => true],
                    ['name' => 'NAGAD_MERCHANT_PRIVATE_KEY', 'label' => 'NAGAD MERCHANT PRIVATE KEY', 'secret' => true, 'required' => true],
                ],
            ],
            'sslcommerz' => [
                'title' => 'Sslcommerz Credential', 'sandbox' => 'sslcommerz_sandbox', 'sandboxDefault' => true,
                'fields' => [
                    ['name' => 'SSLCZ_STORE_ID', 'label' => 'Sslcz Store Id', 'secret' => false, 'required' => true],
                    ['name' => 'SSLCZ_STORE_PASSWD', 'label' => 'Sslcz Store Password', 'secret' => true, 'required' => true],
                ],
            ],
            'aamarpay' => [
                'title' => 'Aamarpay Credential', 'sandbox' => 'aamarpay_sandbox',
                'fields' => [
                    ['name' => 'AAMARPAY_STORE_ID', 'label' => 'Aamarpay Store Id', 'secret' => false, 'required' => true],
                    ['name' => 'AAMARPAY_SIGNATURE_KEY', 'label' => 'Aamarpay Signature Key', 'secret' => true, 'required' => true],
                ],
            ],
            'iyzico' => [
                'title' => 'Iyzico Credential', 'sandbox' => 'iyzico_sandbox', 'sandboxDefault' => true,
                'fields' => [
                    ['name' => 'IYZICO_API_KEY', 'label' => 'IYZICO API KEY', 'secret' => false, 'required' => true],
                    ['name' => 'IYZICO_SECRET_KEY', 'label' => 'IYZICO SECRET KEY', 'secret' => true, 'required' => true],
                    ['name' => 'IYZICO_CURRENCY_CODE', 'label' => 'IYZICO CURRENCY CODE', 'secret' => false, 'required' => true, 'default' => 'TRY'],
                ],
            ],
            'instamojo' => [
                'title' => 'Instamojo Credential', 'sandbox' => 'instamojo_sandbox', 'sandboxDefault' => true,
                'fields' => [
                    ['name' => 'IM_API_KEY', 'label' => 'API KEY', 'secret' => false, 'required' => true],
                    ['name' => 'IM_AUTH_TOKEN', 'label' => 'AUTH TOKEN', 'secret' => true, 'required' => true],
                ],
            ],
            'paystack' => [
                'title' => 'PayStack Credential',
                'fields' => [
                    ['name' => 'PAYSTACK_PUBLIC_KEY', 'label' => 'PUBLIC KEY', 'secret' => false, 'required' => true],
                    ['name' => 'PAYSTACK_SECRET_KEY', 'label' => 'SECRET KEY', 'secret' => true, 'required' => true],
                    ['name' => 'MERCHANT_EMAIL', 'label' => 'MERCHANT EMAIL', 'secret' => false, 'required' => true, 'type' => 'email'],
                    ['name' => 'PAYSTACK_CURRENCY_CODE', 'label' => 'PAYSTACK CURRENCY CODE', 'secret' => false, 'required' => true],
                ],
            ],
            'payhere' => [
                'title' => 'Payhere Credential', 'sandbox' => 'payhere_sandbox',
                'fields' => [
                    ['name' => 'PAYHERE_MERCHANT_ID', 'label' => 'PAYHERE MERCHANT ID', 'secret' => false, 'required' => true],
                    ['name' => 'PAYHERE_SECRET', 'label' => 'PAYHERE SECRET', 'secret' => true, 'required' => true],
                    ['name' => 'PAYHERE_CURRENCY', 'label' => 'PAYHERE CURRENCY', 'secret' => false, 'required' => true],
                ],
            ],
            'ngenius' => [
                'title' => 'Ngenius Credential',
                'sandbox' => 'ngenius_sandbox',
                'fields' => [
                    ['name' => 'NGENIUS_OUTLET_ID', 'label' => 'NGENIUS OUTLET ID', 'secret' => false, 'required' => true],
                    ['name' => 'NGENIUS_API_KEY', 'label' => 'NGENIUS API KEY', 'secret' => true, 'required' => true],
                    ['name' => 'NGENIUS_CURRENCY', 'label' => 'NGENIUS CURRENCY', 'secret' => false, 'required' => true, 'default' => 'AED', 'help' => 'Use AED, USD or EUR. Defaults to AED.'],
                ],
            ],
            'voguepay' => [
                'title' => 'VoguePay Credential', 'sandbox' => 'voguepay_sandbox',
                'fields' => [
                    ['name' => 'VOGUE_MERCHANT_ID', 'label' => 'MERCHANT ID', 'secret' => false, 'required' => true, 'default' => 'DEMO'],
                ],
            ],
            'razorpay' => [
                'title' => 'RazorPay Credential',
                'fields' => [
                    ['name' => 'RAZOR_KEY', 'label' => 'RAZOR KEY', 'secret' => false, 'required' => true],
                    ['name' => 'RAZOR_SECRET', 'label' => 'RAZOR SECRET', 'secret' => true, 'required' => true],
                ],
            ],
            'authorizenet' => [
                'title' => 'Authorize Net Credential', 'sandbox' => 'authorizenet_sandbox', 'sandboxDefault' => true,
                'fields' => [
                    ['name' => 'MERCHANT_LOGIN_ID', 'label' => 'MERCHANT LOGIN ID', 'secret' => false, 'required' => true],
                    ['name' => 'MERCHANT_TRANSACTION_KEY', 'label' => 'MERCHANT TRANSACTION KEY', 'secret' => true, 'required' => true],
                ],
            ],
            'payku' => [
                'title' => 'Payku Credential',
                'fields' => [
                    ['name' => 'PAYKU_BASE_URL', 'label' => 'PAYKU BASE URL', 'secret' => false, 'required' => true],
                    ['name' => 'PAYKU_PUBLIC_TOKEN', 'label' => 'PAYKU PUBLIC TOKEN', 'secret' => false, 'required' => true],
                    ['name' => 'PAYKU_PRIVATE_TOKEN', 'label' => 'PAYKU PRIVATE TOKEN', 'secret' => true, 'required' => true],
                ],
            ],
            'proxypay' => [
                'title' => 'Proxy Pay Credential',
                'fields' => [
                    ['name' => 'PROXYPAY_BASE_URL', 'label' => 'PROXYPAY CHECKOUT URL', 'secret' => false, 'required' => true, 'help' => 'Full hosted checkout URL, e.g. https://pay.example.com/checkout'],
                    ['name' => 'PROXYPAY_MERCHANT_ID', 'label' => 'PROXYPAY MERCHANT ID', 'secret' => false, 'required' => true],
                    ['name' => 'PROXYPAY_SECRET_KEY', 'label' => 'PROXYPAY SECRET KEY', 'secret' => true, 'required' => true, 'help' => 'Signature: HMAC-SHA256 over the sorted key=value request fields joined with & (signature excluded). Configure the same scheme in ProxyPay.'],
                ],
            ],
        ];
    }

    public static function credentials(string $provider): array
    {
        $stored = Setting::get('payment_method_'.$provider);

        if (! $stored) {
            return [];
        }

        try {
            $decoded = json_decode(Crypt::decryptString($stored), true);
        } catch (Throwable) {
            $decoded = json_decode($stored, true);
        }

        return is_array($decoded) ? $decoded : [];
    }

    public static function isConfigured(string $provider): bool
    {
        $definition = self::definitions()[$provider] ?? null;
        if (! $definition) {
            return false;
        }

        $credentials = self::credentials($provider);
        foreach ($definition['fields'] as $field) {
            if (! empty($field['required']) && empty($credentials[$field['name']])) {
                return false;
            }
        }

        return true;
    }

    public static function isSandbox(string $provider): bool
    {
        $definition = self::definitions()[$provider] ?? null;
        if (! isset($definition['sandbox'])) {
            return false;
        }

        $credentials = self::credentials($provider);

        return (bool) ($credentials[$definition['sandbox']] ?? ($definition['sandboxDefault'] ?? false));
    }

    public static function value(string $provider, string $field, string $default = ''): string
    {
        return (string) (self::credentials($provider)[$field] ?? $default);
    }
}
