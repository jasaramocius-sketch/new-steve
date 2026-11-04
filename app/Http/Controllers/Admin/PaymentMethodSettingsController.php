<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;
use Throwable;

class PaymentMethodSettingsController extends Controller
{
    public function index()
    {
        $definitions = self::methodDefinitions();
        $settings = [];

        foreach ($definitions as $provider => $definition) {
            $stored = $this->decodeCredentials(Setting::get('payment_method_'.$provider));
            $fields = [];
            foreach ($definition['fields'] as $field) {
                $value = $stored[$field['name']] ?? ($field['default'] ?? '');
                $field['value'] = $field['secret'] ? '' : $value;
                $field['configured'] = $field['secret'] && $value !== '';
                $field['masked'] = $field['configured'] ? $this->maskSecret((string) $value) : '';
                $fields[] = $field;
            }

            $settings[] = [
                'provider' => $provider,
                'title' => $definition['title'],
                'fields' => $fields,
                'sandbox' => $definition['sandbox'] ?? null,
                'sandboxEnabled' => isset($definition['sandbox']) && (bool) ($stored[$definition['sandbox']] ?? $definition['sandboxDefault'] ?? false),
            ];
        }

        return view('admin.settings.payment-methods', compact('settings'));
    }

    public function update(Request $request, string $provider)
    {
        $definitions = self::methodDefinitions();
        abort_unless(array_key_exists($provider, $definitions), 404);
        $definition = $definitions[$provider];
        $existing = $this->decodeCredentials(Setting::get('payment_method_'.$provider));

        $rules = [];
        foreach ($definition['fields'] as $field) {
            $required = $field['required'] ?? false;
            if ($field['secret'] && ! empty($existing[$field['name']])) {
                $required = false;
            }
            $rules[$field['name']] = [
                $required ? 'required' : 'nullable',
                'string',
                'max:4096',
            ];
            if (($field['type'] ?? 'text') === 'email') {
                $rules[$field['name']][] = 'email';
            }
        }
        if (isset($definition['sandbox'])) {
            $rules[$definition['sandbox']] = 'nullable|boolean';
        }

        $validated = $request->validate($rules);
        $credentials = [];
        foreach ($definition['fields'] as $field) {
            $name = $field['name'];
            $value = $validated[$name] ?? '';
            if ($field['secret'] && $value === '') {
                $value = $existing[$name] ?? '';
            }
            $credentials[$name] = $value;
        }
        if (isset($definition['sandbox'])) {
            $credentials[$definition['sandbox']] = $request->boolean($definition['sandbox']);
        }

        Setting::set('payment_method_'.$provider, Crypt::encryptString(json_encode($credentials, JSON_THROW_ON_ERROR)));

        return redirect()->route('admin.settings.payment-methods')->with('success', $definition['title'].' settings saved.');
    }

    /**
     * Show just enough of a stored secret for the admin to recognise it.
     * The full value is never sent back to the browser.
     */
    private function maskSecret(string $value): string
    {
        $length = strlen($value);

        if ($length === 0) {
            return '';
        }

        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        return str_repeat('*', 8).substr($value, -4);
    }

    private function decodeCredentials(?string $value): array
    {
        if (! $value) {
            return [];
        }

        try {
            $decoded = json_decode(Crypt::decryptString($value), true);
        } catch (Throwable) {
            $decoded = json_decode($value, true);
        }

        return is_array($decoded) ? $decoded : [];
    }

    private static function methodDefinitions(): array
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
                ],
            ],
            'mercadopago' => [
                'title' => 'Mercadopago Credential',
                'fields' => [
                    ['name' => 'MERCADOPAGO_KEY', 'label' => 'Mercadopago Key', 'secret' => false, 'required' => true],
                    ['name' => 'MERCADOPAGO_ACCESS', 'label' => 'Mercadopago Access', 'secret' => true, 'required' => true],
                    ['name' => 'MERCADOPAGO_CURRENCY', 'label' => 'Mercadopago Currency', 'secret' => false, 'required' => false, 'help' => 'Examples: es-AR, es-CL, es-CO, es-MX, es-VE, es-UY, es-PE, pt-BR. Defaults to en-US.'],
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
                    ['name' => 'NAGAD_MODE', 'label' => 'NAGAD MODE', 'secret' => false, 'required' => true],
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
        ];
    }
}
