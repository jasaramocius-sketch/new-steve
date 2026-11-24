<?php

namespace App\Services\Payments;

use App\Helpers\NotificationHelper;
use App\Http\Controllers\CartController;
use App\Mail\OrderConfirmationMail;
use App\Models\Order;
use App\Models\Setting;
use App\Support\FeatureFlags;
use App\Support\PaymentMethods;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Central checkout gateway layer.
 *
 * A method is only offered when its Features toggle is ON and all required
 * credentials are saved in Admin > Settings > Payment Methods. Orders stay
 * unpaid until the provider is re-queried (or a signed callback is verified).
 */
class PaymentGatewayManager
{
    /** @var array<string, array{feature:string, provider:string, label:string, icon:string, offsite?:bool}> */
    private const METHODS = [
        'card' => ['feature' => 'stripe_payment', 'provider' => 'stripe', 'label' => 'Stripe', 'icon' => 'las la-credit-card'],
        'paypal' => ['feature' => 'paypal_payment', 'provider' => 'paypal', 'label' => 'PayPal', 'icon' => 'lab la-paypal', 'offsite' => true],
        'mercadopago' => ['feature' => 'mercadopago_payment', 'provider' => 'mercadopago', 'label' => 'MercadoPago', 'icon' => 'las la-hand-holding-usd', 'offsite' => true],
        'sslcommerz' => ['feature' => 'sslcommerz_payment', 'provider' => 'sslcommerz', 'label' => 'SSLCommerz', 'icon' => 'las la-shield-alt', 'offsite' => true],
        'instamojo' => ['feature' => 'instamojo_payment', 'provider' => 'instamojo', 'label' => 'Instamojo', 'icon' => 'las la-mobile-alt', 'offsite' => true],
        'razorpay' => ['feature' => 'razorpay', 'provider' => 'razorpay', 'label' => 'Razorpay', 'icon' => 'las la-credit-card', 'offsite' => true],
        'paystack' => ['feature' => 'paystack', 'provider' => 'paystack', 'label' => 'Paystack', 'icon' => 'las la-wallet', 'offsite' => true],
        'voguepay' => ['feature' => 'voguepay', 'provider' => 'voguepay', 'label' => 'VoguePay', 'icon' => 'las la-gateway', 'offsite' => true],
        'payhere' => ['feature' => 'payhere', 'provider' => 'payhere', 'label' => 'PayHere', 'icon' => 'las la-money-check', 'offsite' => true],
        'ngenius' => ['feature' => 'ngenius', 'provider' => 'ngenius', 'label' => 'N-Genius', 'icon' => 'las la-credit-card', 'offsite' => true],
        'iyzico' => ['feature' => 'iyzico', 'provider' => 'iyzico', 'label' => 'iyzico', 'icon' => 'las la-credit-card', 'offsite' => true],
        'bkash' => ['feature' => 'bkash', 'provider' => 'bkash', 'label' => 'bKash', 'icon' => 'las la-mobile-alt', 'offsite' => true],
        'nagad' => ['feature' => 'nagad', 'provider' => 'nagad', 'label' => 'Nagad', 'icon' => 'las la-mobile-alt', 'offsite' => true],
        'proxypay' => ['feature' => 'proxypay', 'provider' => 'proxypay', 'label' => 'ProxyPay', 'icon' => 'las la-exchange-alt', 'offsite' => true],
        'aamarpay' => ['feature' => 'aamarpay', 'provider' => 'aamarpay', 'label' => 'Aamarpay', 'icon' => 'las la-mobile-alt', 'offsite' => true],
        'authorizenet' => ['feature' => 'authorizenet', 'provider' => 'authorizenet', 'label' => 'Authorize.Net', 'icon' => 'las la-credit-card', 'offsite' => true],
        'payku' => ['feature' => 'payku', 'provider' => 'payku', 'label' => 'Payku', 'icon' => 'las la-credit-card', 'offsite' => true],
        'cod' => ['feature' => 'cash_payment', 'provider' => 'cash', 'label' => 'Cash on Delivery', 'icon' => 'las la-money-bill-wave'],
    ];

    private const ZERO_DECIMAL_CURRENCIES = [
        'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG',
        'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF',
    ];

    /**
     * Payment options currently safe to display at checkout.
     */
    public function availableMethods(): array
    {
        $methods = [];

        foreach (self::METHODS as $key => $definition) {
            if (! FeatureFlags::enabled($definition['feature'])) {
                continue;
            }

            if ($definition['provider'] !== 'cash' && ! PaymentMethods::isConfigured($definition['provider'])) {
                continue;
            }

            $methods[$key] = [
                'label' => $definition['label'],
                'icon' => $definition['icon'],
                'offsite' => (bool) ($definition['offsite'] ?? false),
            ];
        }

        return $methods;
    }

    public function label(string $method): string
    {
        return self::METHODS[$method]['label'] ?? ucfirst($method);
    }

    /**
     * Start a gateway flow. Returns a redirect URL, provider-hosted HTML, or
     * a pending-confirmation page instruction set.
     */
    public function start(Order $order, string $method, array $billing = []): array
    {
        $definition = self::METHODS[$method] ?? null;
        if (! $definition || in_array($method, ['card', 'cod'], true)) {
            throw new RuntimeException('Unsupported checkout payment method.');
        }

        session([
            'pending_gateway_order_id' => $order->id,
            'pending_gateway_provider' => $method,
        ]);

        return match ($method) {
            'paypal' => $this->startPaypal($order),
            'mercadopago' => $this->startMercadoPago($order),
            'sslcommerz' => $this->startSslCommerz($order),
            'instamojo' => $this->startInstamojo($order),
            'razorpay' => $this->startRazorpay($order),
            'paystack' => $this->startPaystack($order),
            'voguepay' => $this->startVoguePay($order),
            'payhere' => $this->startPayHere($order),
            'ngenius' => $this->startNgenius($order),
            'iyzico' => $this->startIyzico($order),
            'bkash' => $this->startBkash($order),
            'nagad' => $this->startNagad($order),
            'proxypay' => $this->startProxypay($order),
            'aamarpay' => $this->startAamarpay($order),
            'authorizenet' => $this->startAuthorizeNet($order),
            'payku' => $this->startPayku($order),
            default => throw new RuntimeException('No checkout adapter is registered for '.$method.'.'),
        };
    }

    /**
     * Verify a provider return/webhook and update the order when settled.
     */
    public function confirm(string $method, Request $request): array
    {
        $order = $this->resolveOrder($method, $request);

        if (! $order) {
            return [
                'order' => null,
                'paid' => false,
                'message' => 'We could not match this payment response to an order. Please contact support with your order reference.',
            ];
        }

        if ($order->payment_status === 'paid') {
            if (! $this->isWebhookRequest()) {
                $this->finishCheckout($order);
            }

            return [
                'order' => $order,
                'paid' => true,
                'message' => 'Payment already confirmed.',
            ];
        }

        $result = $this->verifyProvider($method, $order, $request);

        $marked = ! empty($result['paid']) && $this->markPaid(
            $order,
            $method,
            $result['transaction_id'] ?? null,
            $result['extra'] ?? []
        );

        if ($marked && ! $this->isWebhookRequest()) {
            $this->finishCheckout($order);
        }

        return [
            'order' => $order,
            'paid' => $marked,
            'message' => $result['message'] ?? 'Payment is still being confirmed by the provider.',
        ];
    }

    public function handleWebhook(string $method, Request $request)
    {
        $result = $this->confirm($method, $request);

        return response()->json([
            'received' => true,
            'paid' => $result['paid'],
        ], $result['paid'] ? 200 : 422);
    }

    public function cancel(string $method, Request $request): void
    {
        $order = $this->resolveOrder($method, $request);
        if ($order) {
            // The customer may have paid just before hitting cancel. Re-query the
            // provider so we never cancel/stock-restore an already-settled order.
            $result = $this->verifyProvider($method, $order, $request);
            if (! empty($result['paid'])) {
                $this->markPaid(
                    $order,
                    $method,
                    $result['transaction_id'] ?? null,
                    $result['extra'] ?? []
                );
            } else {
                $this->cancelPending($order, $this->label($method).' checkout was cancelled');
            }
        }

        session()->forget(['pending_gateway_order_id', 'pending_gateway_provider']);
    }

    /**
     * Razorpay posts its signature to a dedicated route. This method verifies
     * the signature before delegating to the normal confirmation path.
     */
    public function confirmRazorpay(Request $request): array
    {
        $orderId = session('pending_gateway_order_id');
        $provider = session('pending_gateway_provider');
        $order = ($orderId && $provider === 'razorpay') ? Order::find($orderId) : null;
        if (! $order) {
            return ['order' => null, 'paid' => false, 'message' => 'The Razorpay checkout session has expired. Please try again.'];
        }

        $paymentId = (string) $request->input('razorpay_payment_id', '');
        $signature = (string) $request->input('razorpay_signature', '');
        $razorpayOrderId = (string) $request->input('razorpay_order_id', '');
        $storedOrderId = (string) ($this->details($order)['razorpay_order_id'] ?? '');

        $secret = PaymentMethods::value('razorpay', 'RAZOR_SECRET');
        $expected = $storedOrderId !== '' && $paymentId !== ''
            ? hash_hmac('sha256', $storedOrderId.'|'.$paymentId, $secret)
            : '';

        if ($razorpayOrderId === '' || $razorpayOrderId !== $storedOrderId || $signature === '' || ! hash_equals($expected, $signature)) {
            return ['order' => $order, 'paid' => false, 'message' => 'Razorpay signature verification failed. Payment was not confirmed.'];
        }

        $result = $this->verifyRazorpayPayment($order, $paymentId, $razorpayOrderId);
        $marked = ! empty($result['paid']) && $this->markPaid($order, 'razorpay', $paymentId, $result['extra'] ?? []);
        if ($marked) {
            $this->finishCheckout($order);
        }

        return [
            'order' => $order,
            'paid' => $marked,
            'message' => $result['message'] ?? 'Razorpay payment is still being confirmed.',
        ];
    }

    /* ------------------------------------------------------------------ *
     * Provider start flows
     * ------------------------------------------------------------------ */

    private function startPaypal(Order $order): array
    {
        $base = PaymentMethods::isSandbox('paypal')
            ? 'https://api-m.sandbox.paypal.com'
            : 'https://api-m.paypal.com';

        $tokenResponse = Http::asForm()
            ->withBasicAuth(PaymentMethods::value('paypal', 'PAYPAL_CLIENT_ID'), PaymentMethods::value('paypal', 'PAYPAL_CLIENT_SECRET'))
            ->timeout(20)
            ->post($base.'/v1/oauth2/token', ['grant_type' => 'client_credentials']);

        if ($tokenResponse->failed() || ! $tokenResponse->json('access_token')) {
            throw new RuntimeException('PayPal authentication failed. Check the saved client ID and secret.');
        }

        $response = Http::withToken($tokenResponse->json('access_token'))
            ->withHeaders(['PayPal-Request-Id' => $order->order_number])
            ->timeout(20)
            ->post($base.'/v2/checkout/orders', [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => (string) $order->id,
                    'description' => 'Order '.$order->order_number,
                    'amount' => [
                        'currency_code' => $this->currency(),
                        'value' => $this->amount($order),
                    ],
                ]],
                'payment_source' => [
                    'paypal' => [
                        'experience_context' => [
                            'brand_name' => config('app.name', 'Store'),
                            'return_url' => route('checkout.gateway.return', 'paypal'),
                            'cancel_url' => route('checkout.gateway.cancel', 'paypal'),
                        ],
                    ],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException($response->json('message') ?: 'PayPal could not start the payment.');
        }

        $approve = collect($response->json('links', []))->firstWhere('rel', 'approve')['href'] ?? null;
        if (! $approve) {
            throw new RuntimeException('PayPal did not return an approval URL.');
        }

        $this->storeDetails($order, [
            'paypal_order_id' => $response->json('id'),
            'status' => 'initiated',
        ]);

        return ['redirect' => $approve];
    }

    private function startMercadoPago(Order $order): array
    {
        $configuredCurrency = trim(PaymentMethods::value('mercadopago', 'MERCADOPAGO_CURRENCY'));
        $currency = preg_match('/^[A-Za-z]{3}$/', $configuredCurrency) === 1
            ? strtoupper($configuredCurrency)
            : $this->currency();

        $payload = [
            'items' => [[
                'title' => 'Order '.$order->order_number,
                'quantity' => 1,
                'currency_id' => $currency,
                'unit_price' => (float) $order->total_amount,
            ]],
            'external_reference' => (string) $order->id,
            'back_urls' => [
                'success' => route('checkout.gateway.return', 'mercadopago'),
                'pending' => route('checkout.gateway.return', 'mercadopago'),
                'failure' => route('checkout.gateway.cancel', 'mercadopago'),
            ],
            'auto_return' => 'approved',
            'notification_url' => route('checkout.gateway.webhook', 'mercadopago'),
        ];

        $response = Http::withToken(PaymentMethods::value('mercadopago', 'MERCADOPAGO_ACCESS'))
            ->timeout(20)
            ->post('https://api.mercadopago.com/checkout/preferences', $payload);

        if ($response->failed()) {
            throw new RuntimeException($response->json('message') ?: 'MercadoPago could not start the payment.');
        }

        $url = PaymentMethods::isSandbox('mercadopago')
            ? ($response->json('sandbox_init_point') ?: $response->json('init_point'))
            : $response->json('init_point');
        if (! $url) {
            throw new RuntimeException('MercadoPago did not return a checkout URL.');
        }

        $this->storeDetails($order, ['preference_id' => $response->json('id'), 'status' => 'initiated']);

        return ['redirect' => $url];
    }

    private function startSslCommerz(Order $order): array
    {
        $base = PaymentMethods::isSandbox('sslcommerz')
            ? 'https://sandboxssl.sslcommerz.com/gwprocess/v4'
            : 'https://securepay.sslcommerz.com/gwprocess/v4';
        $customer = $this->customer($order);

        $response = Http::asForm()->timeout(20)->post($base.'/api.php', [
            'store_id' => PaymentMethods::value('sslcommerz', 'SSLCZ_STORE_ID'),
            'store_passwd' => PaymentMethods::value('sslcommerz', 'SSLCZ_STORE_PASSWD'),
            'total_amount' => $this->amount($order),
            'currency' => $this->currency(),
            'tran_id' => $order->order_number,
            'success_url' => route('checkout.gateway.return', 'sslcommerz'),
            'fail_url' => route('checkout.gateway.cancel', 'sslcommerz'),
            'cancel_url' => route('checkout.gateway.cancel', 'sslcommerz'),
            'ipn_url' => route('checkout.gateway.webhook', 'sslcommerz'),
            'cus_name' => $customer['name'],
            'cus_email' => $customer['email'],
            'cus_phone' => $customer['phone'],
            'cus_add1' => $customer['address'],
            'cus_city' => $customer['city'],
            'cus_country' => $customer['country'],
            'shipping_method' => 'NO',
            'product_name' => 'Order '.$order->order_number,
            'product_category' => 'general',
            'product_profile' => 'general',
        ]);

        if ($response->failed() || ! $response->json('GatewayPageURL')) {
            throw new RuntimeException($response->json('error') ?: 'SSLCommerz could not start the payment.');
        }

        $this->storeDetails($order, ['status' => 'initiated']);

        return ['redirect' => $response->json('GatewayPageURL')];
    }

    private function startInstamojo(Order $order): array
    {
        $base = PaymentMethods::isSandbox('instamojo')
            ? 'https://test.instamojo.com/v2'
            : 'https://api.instamojo.com/v2';
        $customer = $this->customer($order);

        $response = Http::withHeaders([
            'Authorization' => 'Token '.PaymentMethods::value('instamojo', 'IM_AUTH_TOKEN'),
            'Accept' => 'application/json',
        ])->timeout(20)->post($base.'/payment_requests/', [
            'purpose' => 'Order '.$order->order_number,
            'amount' => $this->amount($order),
            'buyer_name' => $customer['name'],
            'email' => $customer['email'],
            'phone' => $customer['phone'],
            'redirect_url' => route('checkout.gateway.return', 'instamojo'),
            'webhook' => route('checkout.gateway.webhook', 'instamojo'),
            'allow_repeated_payments' => false,
            'send_email' => false,
            'send_sms' => false,
        ]);

        if ($response->failed() || ! $response->json('longurl')) {
            throw new RuntimeException($response->json('message') ?: 'Instamojo could not start the payment.');
        }

        $this->storeDetails($order, [
            'instamojo_request_id' => $response->json('id'),
            'status' => 'initiated',
        ]);

        return ['redirect' => $response->json('longurl')];
    }

    private function startRazorpay(Order $order): array
    {
        $key = PaymentMethods::value('razorpay', 'RAZOR_KEY');
        $secret = PaymentMethods::value('razorpay', 'RAZOR_SECRET');

        $response = Http::withBasicAuth($key, $secret)
            ->timeout(20)
            ->post('https://api.razorpay.com/v1/orders', [
                'amount' => $this->minorUnits($order, $this->currency()),
                'currency' => $this->currency(),
                'receipt' => $order->order_number,
                'notes' => ['order_id' => (string) $order->id],
            ]);

        if ($response->failed() || ! $response->json('id')) {
            throw new RuntimeException($response->json('error.description') ?: 'Razorpay could not create an order.');
        }

        $this->storeDetails($order, [
            'razorpay_order_id' => $response->json('id'),
            'status' => 'initiated',
        ]);

        return ['html' => view('frontend.checkout.gateway.razorpay', [
            'order' => $order,
            'key' => $key,
            'razorpayOrderId' => $response->json('id'),
            'amount' => $this->minorUnits($order, $this->currency()),
            'currency' => $this->currency(),
            'customer' => $this->customer($order),
            'verifyUrl' => route('checkout.razorpay.verify'),
            'cancelUrl' => route('checkout.gateway.cancel', 'razorpay'),
        ])->render()];
    }

    private function startPaystack(Order $order): array
    {
        $currency = PaymentMethods::value('paystack', 'PAYSTACK_CURRENCY_CODE', $this->currency());
        $response = Http::withToken(PaymentMethods::value('paystack', 'PAYSTACK_SECRET_KEY'))
            ->timeout(20)
            ->post('https://api.paystack.co/transaction/initialize', [
                'email' => $this->customer($order)['email'],
                'amount' => $this->minorUnits($order, $currency),
                'currency' => $currency,
                'reference' => $order->order_number,
                'callback_url' => route('checkout.gateway.return', 'paystack'),
                'metadata' => [
                    'order_id' => $order->id,
                    'custom_fields' => [[
                        'display_name' => 'Order Number',
                        'variable_name' => 'order_number',
                        'value' => $order->order_number,
                    ]],
                ],
            ]);

        if ($response->failed() || ! $response->json('data.authorization_url')) {
            throw new RuntimeException($response->json('message') ?: 'Paystack could not start the payment.');
        }

        $this->storeDetails($order, ['paystack_reference' => $order->order_number, 'status' => 'initiated']);

        return ['redirect' => $response->json('data.authorization_url')];
    }

    private function startVoguePay(Order $order): array
    {
        $customer = $this->customer($order);
        $query = [
            'p' => 'process',
            'v_merchant_id' => PaymentMethods::value('voguepay', 'VOGUE_MERCHANT_ID'),
            'v_transaction' => $order->order_number,
            'v_total' => $this->amount($order),
            'v_email' => $customer['email'],
            'v_success_url' => route('checkout.gateway.return', 'voguepay'),
            'v_fail_url' => route('checkout.gateway.cancel', 'voguepay'),
            'v_notify_url' => route('checkout.gateway.webhook', 'voguepay'),
            'v_demo' => PaymentMethods::isSandbox('voguepay') ? '1' : '0',
        ];

        $this->storeDetails($order, ['status' => 'initiated']);

        return ['redirect' => 'https://voguepay.com/?'.http_build_query($query)];
    }

    private function startPayHere(Order $order): array
    {
        $merchantId = PaymentMethods::value('payhere', 'PAYHERE_MERCHANT_ID');
        $secret = PaymentMethods::value('payhere', 'PAYHERE_SECRET');
        $currency = PaymentMethods::value('payhere', 'PAYHERE_CURRENCY', $this->currency());
        $amount = $this->amount($order);
        $hash = strtoupper(md5($merchantId.$order->order_number.$amount.$currency.strtoupper(md5($secret))));
        $customer = $this->customer($order);

        $fields = [
            'merchant_id' => $merchantId,
            'return_url' => route('checkout.gateway.return', 'payhere'),
            'cancel_url' => route('checkout.gateway.cancel', 'payhere'),
            'notify_url' => route('checkout.gateway.webhook', 'payhere'),
            'order_id' => $order->order_number,
            'items' => 'Order '.$order->order_number,
            'amount' => $amount,
            'currency' => $currency,
            'hash' => $hash,
            'first_name' => $customer['name'],
            'last_name' => '',
            'email' => $customer['email'],
            'phone' => $customer['phone'],
            'address' => $customer['address'],
            'city' => $customer['city'],
            'country' => $customer['country'],
        ];

        $this->storeDetails($order, ['status' => 'initiated']);

        return ['html' => view('frontend.checkout.gateway.auto-submit', [
            'title' => 'Redirecting to PayHere...',
            'action' => PaymentMethods::isSandbox('payhere')
                ? 'https://sandbox.payhere.lk/pay/checkout'
                : 'https://www.payhere.lk/pay/checkout',
            'fields' => $fields,
        ])->render()];
    }

    private function startNgenius(Order $order): array
    {
        $outlet = PaymentMethods::value('ngenius', 'NGENIUS_OUTLET_ID');
        $apiKey = PaymentMethods::value('ngenius', 'NGENIUS_API_KEY');
        $base = PaymentMethods::isSandbox('ngenius')
            ? 'https://api-gateway.sandbox.ngenius-payments.com'
            : 'https://api-gateway.ngenius-payments.com';

        $tokenResponse = Http::asForm()
            ->withHeaders(['Authorization' => 'Basic '.base64_encode($outlet.':'.$apiKey)])
            ->timeout(20)
            ->post($base.'/identity/auth/accessToken', ['grant_type' => 'client_credentials']);

        if ($tokenResponse->failed() || ! $tokenResponse->json('access_token')) {
            throw new RuntimeException('N-Genius authentication failed. Check the saved outlet and API key.');
        }

        $currency = PaymentMethods::value('ngenius', 'NGENIUS_CURRENCY', 'AED');
        $response = Http::withToken($tokenResponse->json('access_token'))
            ->withHeaders([
                'Accept' => 'application/vnd.ni-payment.v2+json',
                'Content-Type' => 'application/vnd.ni-payment.v2+json',
            ])
            ->timeout(20)
            ->post($base.'/transactions/outlets/'.rawurlencode($outlet).'/orders', [
                'action' => 'SALE',
                'amount' => [
                    'currencyCode' => $currency,
                    'value' => $this->minorUnits($order, $currency),
                ],
                'merchantOrderReference' => $order->order_number,
                'merchantAttributes' => [
                    'redirectUrl' => route('checkout.gateway.return', 'ngenius'),
                    'cancelUrl' => route('checkout.gateway.cancel', 'ngenius'),
                    'notificationUrl' => route('checkout.gateway.webhook', 'ngenius'),
                    'skipConfirmationPage' => false,
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException($response->json('message') ?: 'N-Genius could not start the payment.');
        }

        $paymentUrl = $response->json('_links.payment.href');
        if (! $paymentUrl) {
            throw new RuntimeException('N-Genius did not return a payment URL.');
        }

        $this->storeDetails($order, [
            'ngenius_order_id' => $response->json('id'),
            'ngenius_order_reference' => $response->json('merchantOrderReference'),
            'status' => 'initiated',
        ]);

        return ['redirect' => $paymentUrl];
    }

    private function startIyzico(Order $order): array
    {
        $base = PaymentMethods::isSandbox('iyzico')
            ? 'https://sandbox-api.iyzipay.com'
            : 'https://api.iyzipay.com';
        $currency = PaymentMethods::value('iyzico', 'IYZICO_CURRENCY_CODE', 'TRY');
        $customer = $this->customer($order);
        $conversationId = $order->order_number;
        $payload = [
            'locale' => 'en',
            'conversationId' => $conversationId,
            'price' => $this->amount($order),
            'paidPrice' => $this->amount($order),
            'installment' => 1,
            'currency' => $currency,
            'basketId' => $order->order_number,
            'paymentGroup' => 'PRODUCT',
            'callbackUrl' => route('checkout.gateway.return', 'iyzico'),
            'items' => [[
                'id' => (string) $order->id,
                'name' => 'Order '.$order->order_number,
                'category1' => 'General',
                'itemType' => 'PHYSICAL',
                'price' => $this->amount($order),
            ]],
            'buyer' => [
                'id' => (string) ($order->user_id ?: $order->id),
                'name' => $customer['name'],
                'surname' => '',
                'email' => $customer['email'],
                'identityNumber' => '11111111111',
                'phone' => $customer['phone'],
                'registrationAddress' => $customer['address'],
                'city' => $customer['city'],
                'country' => $customer['country'],
            ],
            'billingAddress' => [
                'contactName' => $customer['name'],
                'city' => $customer['city'],
                'country' => $customer['country'],
                'address' => $customer['address'],
            ],
            'shippingAddress' => [
                'contactName' => $customer['name'],
                'city' => $customer['city'],
                'country' => $customer['country'],
                'address' => $customer['address'],
            ],
        ];

        $body = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $response = Http::withHeaders($this->iyzicoHeaders($body, '/payment/3dsecure/initialize'))
            ->withBody($body, 'application/json')
            ->timeout(20)
            ->post($base.'/payment/3dsecure/initialize');

        if ($response->failed()) {
            throw new RuntimeException($response->json('errorMessage') ?: 'iyzico could not start the payment.');
        }

        $this->storeDetails($order, [
            'iyzico_conversation_id' => $conversationId,
            'status' => 'initiated',
        ]);

        $html = $response->json('threeDSHtmlContent');
        if (! $html && $response->json('redirectUrl')) {
            return ['redirect' => $response->json('redirectUrl')];
        }
        if (! $html) {
            throw new RuntimeException('iyzico did not return a 3D Secure checkout form.');
        }

        return ['html' => view('frontend.checkout.gateway.raw', [
            'title' => 'Redirecting to iyzico...',
            'html' => $html,
        ])->render()];
    }

    private function startBkash(Order $order): array
    {
        $base = $this->bkashBaseUrl();
        $appKey = PaymentMethods::value('bkash', 'BKASH_CHECKOUT_APP_KEY');

        $tokenResponse = Http::withHeaders($this->bkashHeaders())
            ->timeout(20)
            ->post($base.'/token/grant', [
                'app_key' => $appKey,
                'app_secret' => PaymentMethods::value('bkash', 'BKASH_CHECKOUT_APP_SECRET'),
            ]);

        if ($tokenResponse->failed() || ! $tokenResponse->json('id_token')) {
            throw new RuntimeException('bKash authentication failed. Check the saved app credentials.');
        }

        $response = Http::withHeaders($this->bkashHeaders($tokenResponse->json('id_token')))
            ->timeout(20)
            ->post($base.'/create', [
                'amount' => $this->amount($order),
                'currency' => 'BDT',
                'intent' => 'sale',
                'mode' => '0011',
                'payerReference' => $order->order_number,
                'merchantInvoiceNumber' => $order->order_number,
                'callbackURL' => route('checkout.gateway.return', 'bkash'),
            ]);

        if ($response->failed() || ! $response->json('bkashURL')) {
            throw new RuntimeException($response->json('errorMessage') ?: 'bKash could not create the payment.');
        }

        $this->storeDetails($order, [
            'bkash_payment_id' => $response->json('paymentID'),
            'status' => 'initiated',
        ]);

        return ['redirect' => $response->json('bkashURL')];
    }

    private function startNagad(Order $order): array
    {
        $merchantId = PaymentMethods::value('nagad', 'NAGAD_MERCHANT_ID');
        $merchantNumber = PaymentMethods::value('nagad', 'NAGAD_MERCHANT_NUMBER');
        $invoice = $order->order_number;
        $base = $this->nagadBaseUrl();
        $datetime = now()->setTimezone('Asia/Dhaka')->format('YmdHis');
        $challenge = Str::random(16);

        $sensitive = (string) json_encode([
            'merchantId' => $merchantId,
            'datetime' => $datetime,
            'orderId' => $invoice,
            'challenge' => $challenge,
        ], JSON_UNESCAPED_SLASHES);

        $initialize = Http::withHeaders($this->nagadHeaders())
            ->timeout(20)
            ->post($base.'check-out/initialize/'.rawurlencode($merchantId).'/'.rawurlencode($invoice), [
                'accountNumber' => $merchantNumber,
                'dateTime' => $datetime,
                'sensitiveData' => $this->nagadEncrypt($sensitive),
                'signature' => $this->nagadSign($sensitive),
            ]);

        if ($initialize->failed() || strtolower((string) $initialize->json('status')) !== 'success') {
            throw new RuntimeException($initialize->json('message') ?: 'Nagad could not initialize this payment.');
        }

        $initialized = $this->nagadResponseData($initialize->json() ?: []);
        $paymentReference = (string) ($initialized['paymentReferenceId'] ?? $initialize->json('paymentReferenceId') ?? '');
        $nextChallenge = (string) ($initialized['challenge'] ?? '');
        if ($paymentReference === '' || $nextChallenge === '') {
            throw new RuntimeException('Nagad returned an incomplete checkout response.');
        }
        if (
            (string) ($initialized['merchantId'] ?? $merchantId) !== $merchantId
            || (string) ($initialized['orderId'] ?? $invoice) !== $invoice
        ) {
            throw new RuntimeException('Nagad checkout response does not match this order.');
        }

        $sensitive = (string) json_encode([
            'merchantId' => $merchantId,
            'orderId' => $invoice,
            'currencyCode' => '050',
            'amount' => $this->amount($order),
            'challenge' => $nextChallenge,
        ], JSON_UNESCAPED_SLASHES);

        $complete = Http::withHeaders($this->nagadHeaders())
            ->timeout(20)
            ->post($base.'check-out/complete/'.rawurlencode($paymentReference), [
                'merchantCallbackURL' => route('checkout.gateway.return', 'nagad'),
                'sensitiveData' => $this->nagadEncrypt($sensitive),
                'signature' => $this->nagadSign($sensitive),
            ]);

        if ($complete->failed() || strtolower((string) $complete->json('status')) !== 'success') {
            throw new RuntimeException($complete->json('message') ?: 'Nagad could not create the checkout URL.');
        }

        $completed = $this->nagadResponseData($complete->json() ?: []);
        $url = (string) ($complete->json('callBackUrl') ?? $completed['callBackUrl'] ?? '');
        if ($url === '') {
            throw new RuntimeException('Nagad did not return a checkout URL.');
        }

        $this->storeDetails($order, [
            'nagad_payment_reference_id' => $paymentReference,
            'nagad_invoice' => $invoice,
            'status' => 'initiated',
        ]);

        return ['redirect' => $url];
    }

    private function proxypaySignature(array $params): string
    {
        unset($params['signature'], $params['_token']);
        ksort($params);

        $parts = [];
        foreach ($params as $key => $value) {
            if (is_scalar($value)) {
                $parts[] = $key.'='.(string) $value;
            }
        }

        return hash_hmac('sha256', implode('&', $parts), PaymentMethods::value('proxypay', 'PROXYPAY_SECRET_KEY'));
    }

    private function startProxypay(Order $order): array
    {
        $merchant = PaymentMethods::value('proxypay', 'PROXYPAY_MERCHANT_ID');
        $baseUrl = rtrim(PaymentMethods::value('proxypay', 'PROXYPAY_BASE_URL'), '/');
        $params = [
            'merchant' => $merchant,
            'order_id' => $order->order_number,
            'amount' => $this->amount($order),
            'currency' => $this->currency(),
            'return_url' => route('checkout.gateway.return', 'proxypay'),
            'cancel_url' => route('checkout.gateway.cancel', 'proxypay'),
        ];
        $params['signature'] = $this->proxypaySignature($params);

        $this->storeDetails($order, ['status' => 'initiated']);

        return ['redirect' => $baseUrl.'?'.http_build_query($params)];
    }

    private function startAamarpay(Order $order): array
    {
        $storeId = PaymentMethods::value('aamarpay', 'AAMARPAY_STORE_ID');
        $signatureKey = PaymentMethods::value('aamarpay', 'AAMARPAY_SIGNATURE_KEY');
        $amount = $this->amount($order);
        $signature = md5($storeId.$order->order_number.$amount.'BDT'.$signatureKey);
        $customer = $this->customer($order);

        $fields = [
            'store_id' => $storeId,
            'amount' => $amount,
            'payment_type' => 'BDT',
            'tran_id' => $order->order_number,
            'success_url' => route('checkout.gateway.return', 'aamarpay'),
            'fail_url' => route('checkout.gateway.cancel', 'aamarpay'),
            'cancel_url' => route('checkout.gateway.cancel', 'aamarpay'),
            'cus_name' => $customer['name'],
            'cus_email' => $customer['email'],
            'cus_phone' => $customer['phone'],
            'cus_add1' => $customer['address'],
            'cus_city' => $customer['city'],
            'cus_country' => $customer['country'],
            'signature' => $signature,
            'desc' => 'Order '.$order->order_number,
        ];

        $this->storeDetails($order, ['status' => 'initiated']);

        return ['html' => view('frontend.checkout.gateway.auto-submit', [
            'title' => 'Redirecting to Aamarpay...',
            'action' => PaymentMethods::isSandbox('aamarpay')
                ? 'https://sandbox.aamarpay.com/request.php'
                : 'https://pay.aamarpay.com/request.php',
            'fields' => $fields,
        ])->render()];
    }

    private function startAuthorizeNet(Order $order): array
    {
        $login = PaymentMethods::value('authorizenet', 'MERCHANT_LOGIN_ID');
        $transactionKey = PaymentMethods::value('authorizenet', 'MERCHANT_TRANSACTION_KEY');
        $endpoint = PaymentMethods::isSandbox('authorizenet')
            ? 'https://apitest.authorize.net/xml/v1/request.api'
            : 'https://api.authorize.net/xml/v1/request.api';

        $settings = [
            'hostedPaymentReturnOptions' => [
                'showReceipt' => true,
                'url' => route('checkout.gateway.return', 'authorizenet'),
                'cancelUrl' => route('checkout.gateway.cancel', 'authorizenet'),
                'urlText' => 'Continue',
            ],
            'hostedPaymentButtonOptions' => ['text' => 'Pay'],
            'hostedPaymentPaymentOptions' => ['cardCodeRequired' => false],
            'hostedPaymentBillingAddressOptions' => ['show' => true, 'required' => true],
        ];

        $settingXml = '';
        foreach ($settings as $name => $setting) {
            $json = (string) json_encode($setting, JSON_UNESCAPED_SLASHES);
            $settingXml .= '<setting>'
                .'<settingName>'.$this->xml($name).'</settingName>'
                .'<settingValue>'.$this->xml($json).'</settingValue>'
                .'</setting>';
        }

        $xml = '<?xml version="1.0" encoding="utf-8"?>'
           .'<getHostedPaymentPageRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">'
           .'<merchantAuthentication><name>'.$this->xml($login).'</name><transactionKey>'.$this->xml($transactionKey).'</transactionKey></merchantAuthentication>'
           .'<transactionRequest><transactionType>authCaptureTransaction</transactionType><amount>'.$this->amount($order).'</amount>'
           .'<order><invoiceNumber>'.$this->xml($order->order_number).'</invoiceNumber><description>Order '.$this->xml($order->order_number).'</description></order>'
            .'</transactionRequest><hostedPaymentSettings>'.$settingXml.'</hostedPaymentSettings>'
            .'</getHostedPaymentPageRequest>';

        $response = Http::withHeaders(['Content-Type' => 'text/xml'])
            ->timeout(20)
            ->withBody($xml, 'text/xml')
            ->post($endpoint);

        if ($response->failed()) {
            throw new RuntimeException('Authorize.Net could not start the payment.');
        }

        $token = $this->xmlValue($response->body(), 'token');
        if (! $token) {
            throw new RuntimeException($this->xmlValue($response->body(), 'text') ?: 'Authorize.Net did not return a hosted payment token.');
        }

        $this->storeDetails($order, ['status' => 'initiated']);

        $hostedForm = PaymentMethods::isSandbox('authorizenet')
            ? 'https://test.authorize.net/payment/payment/form'
            : 'https://accept.authorize.net/payment/payment/form';

        return ['redirect' => $hostedForm.'?token='.rawurlencode($token)];
    }

    private function startPayku(Order $order): array
    {
        $base = rtrim(PaymentMethods::value('payku', 'PAYKU_BASE_URL', 'https://api.payku.cl'), '/');
        $customer = $this->customer($order);

        $response = Http::withToken(PaymentMethods::value('payku', 'PAYKU_PRIVATE_TOKEN'))
            ->timeout(20)
            ->post($base.'/api/order', [
                'subject' => 'Order '.$order->order_number,
                'amount' => $this->minorUnits($order, $this->currency()),
                'currency' => $this->currency(),
                'email' => $customer['email'],
                'phone' => $customer['phone'],
                'external_id' => $order->order_number,
                'url_ok' => route('checkout.gateway.return', 'payku'),
                'url_nok' => route('checkout.gateway.cancel', 'payku'),
            ]);

        if ($response->failed()) {
            throw new RuntimeException($response->json('message') ?: 'Payku could not start the payment.');
        }

        $url = $response->json('url') ?: $response->json('payment_url');
        if (! $url) {
            throw new RuntimeException('Payku did not return a checkout URL.');
        }

        $this->storeDetails($order, [
            'payku_order_id' => $response->json('id'),
            'status' => 'initiated',
        ]);

        return ['redirect' => $url];
    }

    /* ------------------------------------------------------------------ *
     * Provider verification
     * ------------------------------------------------------------------ */

    private function verifyProvider(string $method, Order $order, Request $request): array
    {
        return match ($method) {
            'paypal' => $this->verifyPaypal($order, $request),
            'mercadopago' => $this->verifyMercadoPago($order, $request),
            'sslcommerz' => $this->verifySslCommerz($order, $request),
            'instamojo' => $this->verifyInstamojo($order, $request),
            'razorpay' => ['paid' => false, 'message' => 'Use the Razorpay checkout verification endpoint.'],
            'paystack' => $this->verifyPaystack($order, $request),
            'voguepay' => $this->verifyVoguePay($order, $request),
            'payhere' => $this->verifyPayHere($order, $request),
            'ngenius' => $this->verifyNgenius($order),
            'iyzico' => $this->verifyIyzico($order, $request),
            'bkash' => $this->verifyBkash($order, $request),
            'nagad' => $this->verifyNagad($order, $request),
            'proxypay' => $this->verifyProxypay($order, $request),
            'aamarpay' => $this->verifyAamarpay($order, $request),
            'authorizenet' => $this->verifyAuthorizeNet($order, $request),
            'payku' => $this->verifyPayku($order),
            default => ['paid' => false, 'message' => 'This payment method has no verification adapter.'],
        };
    }

    private function verifyPaypal(Order $order, Request $request): array
    {
        $paypalOrderId = (string) ($request->input('resource.supplementary_data.related_ids.order_id')
            ?: $request->input('resource.order_id')
            ?: $request->input('token')
            ?: $this->details($order)['paypal_order_id'] ?? '');
        if ($paypalOrderId === '') {
            return ['paid' => false, 'message' => 'Missing PayPal order reference.'];
        }

        $base = PaymentMethods::isSandbox('paypal') ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
        $tokenResponse = Http::asForm()
            ->withBasicAuth(PaymentMethods::value('paypal', 'PAYPAL_CLIENT_ID'), PaymentMethods::value('paypal', 'PAYPAL_CLIENT_SECRET'))
            ->timeout(20)
            ->post($base.'/v1/oauth2/token', ['grant_type' => 'client_credentials']);
        if ($tokenResponse->failed()) {
            return ['paid' => false, 'message' => 'PayPal verification could not authenticate.'];
        }

        $http = Http::withToken($tokenResponse->json('access_token'))->timeout(20);
        $payment = $http->get($base.'/v2/checkout/orders/'.rawurlencode($paypalOrderId));
        if ($payment->failed()) {
            return ['paid' => false, 'message' => 'PayPal could not confirm this payment yet.'];
        }

        $reference = (string) ($payment->json('purchase_units.0.reference_id') ?? '');
        if ($reference !== '' && $reference !== (string) $order->id) {
            return ['paid' => false, 'message' => 'This PayPal payment belongs to a different order.'];
        }

        $status = (string) $payment->json('status');
        if ($status === 'APPROVED') {
            $capture = $http->withHeaders(['PayPal-Request-Id' => $order->order_number.'-capture'])
                ->post($base.'/v2/checkout/orders/'.rawurlencode($paypalOrderId).'/capture', new \stdClass());
            if ($capture->failed()) {
                return ['paid' => false, 'message' => 'PayPal approved the payment but capture has not completed yet.'];
            }
            $payment = $capture;
            $status = (string) $capture->json('status');
        }

        $captureId = (string) ($payment->json('purchase_units.0.payments.captures.0.id') ?? '');
        if (! in_array($status, ['COMPLETED', 'CAPTURED'], true) || $captureId === '') {
            return ['paid' => false, 'message' => 'PayPal payment is not settled yet.'];
        }

        $paidAmount = (string) ($payment->json('purchase_units.0.amount.value') ?? '');
        $paidCurrency = strtoupper((string) ($payment->json('purchase_units.0.amount.currency_code') ?? ''));
        if (($paidAmount !== '' && (float) $paidAmount !== (float) $this->amount($order))
            || ($paidCurrency !== '' && $paidCurrency !== strtoupper($this->currency()))) {
            return ['paid' => false, 'message' => 'PayPal payment amount or currency does not match this order.'];
        }

        return ['paid' => true, 'transaction_id' => $captureId, 'extra' => ['paypal_order_id' => $paypalOrderId]];
    }

    private function verifyMercadoPago(Order $order, Request $request): array
    {
        $paymentId = (string) ($request->input('collection_id') ?: $request->input('payment_id') ?: $request->input('data.id') ?: $request->input('id') ?? '');
        if ($paymentId === '') {
            return ['paid' => false, 'message' => 'Missing MercadoPago payment reference.'];
        }

        $response = Http::withToken(PaymentMethods::value('mercadopago', 'MERCADOPAGO_ACCESS'))
            ->timeout(20)
            ->get('https://api.mercadopago.com/v1/payments/'.rawurlencode($paymentId));

        if ($response->failed()) {
            return ['paid' => false, 'message' => 'MercadoPago could not confirm this payment yet.'];
        }

        $reference = (string) ($response->json('external_reference') ?? '');
        if ($reference !== '' && ! in_array($reference, [(string) $order->id, (string) $order->order_number], true)) {
            return ['paid' => false, 'message' => 'This MercadoPago payment belongs to a different order.'];
        }

        $status = (string) $response->json('status');
        if (! in_array($status, ['approved', 'accredited'], true)) {
            return ['paid' => false, 'message' => 'MercadoPago payment is '.$status.'.'];
        }

        return ['paid' => true, 'transaction_id' => $paymentId, 'extra' => ['mercadopago_status' => $status]];
    }

    private function verifySslCommerz(Order $order, Request $request): array
    {
        $valId = (string) $request->input('val_id', '');
        $tranId = (string) $request->input('tran_id', '');
        if ($valId === '' || ($tranId !== '' && $tranId !== $order->order_number)) {
            return ['paid' => false, 'message' => 'Invalid SSLCommerz validation reference.'];
        }

        $base = PaymentMethods::isSandbox('sslcommerz')
            ? 'https://sandboxssl.sslcommerz.com/validator/api/validationserverAPI.php'
            : 'https://securepay.sslcommerz.com/validator/api/validationserverAPI.php';

        $response = Http::timeout(20)->get($base, [
            'val_id' => $valId,
            'store_id' => PaymentMethods::value('sslcommerz', 'SSLCZ_STORE_ID'),
            'store_passwd' => PaymentMethods::value('sslcommerz', 'SSLCZ_STORE_PASSWD'),
            'format' => 'json',
        ]);

        $status = (string) $response->json('status');
        $responseTranId = (string) ($response->json('tran_id') ?? '');
        $paidAmount = (string) ($response->json('amount') ?? '');
        $paidCurrency = strtoupper((string) ($response->json('currency') ?? ''));
        if (
            $response->failed()
            || ! in_array($status, ['VALID', 'VALIDATED'], true)
            || ($responseTranId !== '' && $responseTranId !== $order->order_number)
            || ($paidAmount !== '' && (float) $paidAmount !== (float) $this->amount($order))
            || ($paidCurrency !== '' && $paidCurrency !== strtoupper($this->currency()))
        ) {
            return ['paid' => false, 'message' => 'SSLCommerz could not validate this payment.'];
        }

        return ['paid' => true, 'transaction_id' => (string) ($response->json('bank_tran_id') ?: $valId), 'extra' => ['sslcommerz_val_id' => $valId]];
    }

    private function verifyInstamojo(Order $order, Request $request): array
    {
        $paymentId = (string) ($request->input('payment_id') ?: '');
        $requestId = (string) ($this->details($order)['instamojo_request_id'] ?? '');
        if ($paymentId === '' || $requestId === '') {
            return ['paid' => false, 'message' => 'Missing Instamojo payment reference.'];
        }

        $base = PaymentMethods::isSandbox('instamojo') ? 'https://test.instamojo.com/v2' : 'https://api.instamojo.com/v2';
        $response = Http::withHeaders([
            'Authorization' => 'Token '.PaymentMethods::value('instamojo', 'IM_AUTH_TOKEN'),
            'Accept' => 'application/json',
        ])->timeout(20)->get($base.'/payments/'.rawurlencode($paymentId).'/');

        if ($response->failed()) {
            return ['paid' => false, 'message' => 'Instamojo could not confirm this payment yet.'];
        }

        $returnedRequest = (string) ($response->json('payment_request') ?? '');
        $returnedRequestId = $returnedRequest !== ''
            ? basename(parse_url($returnedRequest, PHP_URL_PATH) ?: $returnedRequest)
            : (string) ($response->json('payment_request_id') ?? '');
        if ($returnedRequestId !== $requestId) {
            return ['paid' => false, 'message' => 'This Instamojo payment belongs to a different request.'];
        }

        $amount = (string) ($response->json('amount') ?? '');
        if ($amount !== '' && (float) $amount !== (float) $this->amount($order)) {
            return ['paid' => false, 'message' => 'Instamojo payment amount does not match this order.'];
        }

        $status = (string) $response->json('status');
        if (! in_array($status, ['Credit', 'success', 'SUCCESS'], true)) {
            return ['paid' => false, 'message' => 'Instamojo payment is '.$status.'.'];
        }

        return ['paid' => true, 'transaction_id' => $paymentId, 'extra' => ['instamojo_request_id' => $requestId]];
    }

    private function verifyRazorpayPayment(Order $order, string $paymentId, string $razorpayOrderId): array
    {
        $response = Http::withBasicAuth(
            PaymentMethods::value('razorpay', 'RAZOR_KEY'),
            PaymentMethods::value('razorpay', 'RAZOR_SECRET')
        )->timeout(20)->get('https://api.razorpay.com/v1/payments/'.rawurlencode($paymentId));

        if ($response->failed()) {
            return ['paid' => false, 'message' => 'Razorpay could not confirm the payment yet.'];
        }

        if ((string) $response->json('order_id') !== $razorpayOrderId || (string) $response->json('status') !== 'captured') {
            return ['paid' => false, 'message' => 'Razorpay payment is not captured yet.'];
        }

        return ['paid' => true, 'transaction_id' => $paymentId, 'extra' => ['razorpay_order_id' => $razorpayOrderId]];
    }

    private function verifyPaystack(Order $order, Request $request): array
    {
        $secret = PaymentMethods::value('paystack', 'PAYSTACK_SECRET_KEY');
        $signature = (string) $request->header('X-Paystack-Signature', '');
        $isWebhook = $this->isWebhookRequest();

        if ($isWebhook && $signature === '') {
            return ['paid' => false, 'message' => 'Paystack webhook signature is missing.'];
        }
        if ($signature !== '' && ! hash_equals(hash_hmac('sha512', $request->getContent(), $secret), $signature)) {
            return ['paid' => false, 'message' => 'Paystack webhook signature is invalid.'];
        }

        $reference = (string) ($request->input('data.reference') ?: $request->input('reference') ?: $request->input('trxref') ?: $order->order_number);
        if ($reference !== $order->order_number) {
            return ['paid' => false, 'message' => 'This Paystack payment belongs to a different order.'];
        }

        $response = Http::withToken($secret)->timeout(20)->get('https://api.paystack.co/transaction/verify/'.rawurlencode($reference));

        if ($response->failed() || $response->json('data.status') !== 'success') {
            return ['paid' => false, 'message' => 'Paystack payment is not successful yet.'];
        }

        $currency = PaymentMethods::value('paystack', 'PAYSTACK_CURRENCY_CODE', $this->currency());
        if (
            (int) $response->json('data.amount') !== $this->minorUnits($order, $currency)
            || strtoupper((string) $response->json('data.currency')) !== strtoupper($currency)
        ) {
            return ['paid' => false, 'message' => 'Paystack payment amount or currency does not match this order.'];
        }

        return ['paid' => true, 'transaction_id' => (string) ($response->json('data.id') ?: $reference), 'extra' => ['paystack_reference' => $reference]];
    }

    private function verifyVoguePay(Order $order, Request $request): array
    {
        $transaction = (string) ($request->input('v_transaction') ?: $request->input('transaction_id') ?: $request->input('id') ?? '');
        if ($transaction === '' || $transaction !== $order->order_number) {
            return ['paid' => false, 'message' => 'VoguePay transaction reference does not match this order.'];
        }

        $response = Http::timeout(20)->get('https://voguepay.com/', [
            'p' => 'search',
            'v_transaction' => $transaction,
            'v_merchant_id' => PaymentMethods::value('voguepay', 'VOGUE_MERCHANT_ID'),
        ]);
        $body = strtolower($response->body());
        $settled = str_contains($body, '"status":"paid"')
            || str_contains($body, '"status":"approved"')
            || str_contains($body, 'successfully paid');

        if (! $settled) {
            return ['paid' => false, 'message' => 'VoguePay has not confirmed this payment yet.'];
        }

        $data = json_decode($response->body(), true) ?: [];
        $returnedTransaction = (string) ($data['v_transaction'] ?? '');
        $amount = (string) ($data['total'] ?? $data['v_total'] ?? '');
        $merchant = (string) ($data['merchant_id'] ?? '');
        if (
            ($returnedTransaction !== '' && $returnedTransaction !== $order->order_number)
            || ($amount !== '' && (float) $amount !== (float) $this->amount($order))
            || ($merchant !== '' && $merchant !== PaymentMethods::value('voguepay', 'VOGUE_MERCHANT_ID'))
        ) {
            return ['paid' => false, 'message' => 'VoguePay payment does not match this order.'];
        }

        return ['paid' => true, 'transaction_id' => (string) ($data['transaction_id'] ?? $transaction)];
    }

    private function verifyPayHere(Order $order, Request $request): array
    {
        $merchantId = PaymentMethods::value('payhere', 'PAYHERE_MERCHANT_ID');
        $secret = PaymentMethods::value('payhere', 'PAYHERE_SECRET');
        $payhereOrderId = (string) $request->input('order_id', '');
        $amount = (string) $request->input('payhere_amount', '');
        $currency = (string) $request->input('payhere_currency', '');
        $statusCode = (string) $request->input('status_code', '');
        $signature = strtoupper((string) $request->input('md5sig', ''));
        $expected = strtoupper(md5($merchantId.$payhereOrderId.$amount.$currency.$statusCode.strtoupper(md5($secret))));

        if ($payhereOrderId !== $order->order_number || $statusCode !== '2' || $signature === '' || ! hash_equals($expected, $signature)) {
            return ['paid' => false, 'message' => 'PayHere response could not be verified.'];
        }

        return ['paid' => true, 'transaction_id' => (string) ($request->input('payment_id') ?: $request->input('payhere_payment_id') ?: $payhereOrderId)];
    }

    private function verifyNgenius(Order $order): array
    {
        $outlet = PaymentMethods::value('ngenius', 'NGENIUS_OUTLET_ID');
        $apiKey = PaymentMethods::value('ngenius', 'NGENIUS_API_KEY');
        $base = PaymentMethods::isSandbox('ngenius')
            ? 'https://api-gateway.sandbox.ngenius-payments.com'
            : 'https://api-gateway.ngenius-payments.com';
        $stored = (string) ($this->details($order)['ngenius_order_id'] ?? '');

        if ($stored === '') {
            return ['paid' => false, 'message' => 'Missing N-Genius order reference.'];
        }

        $tokenResponse = Http::asForm()
            ->withHeaders(['Authorization' => 'Basic '.base64_encode($outlet.':'.$apiKey)])
            ->timeout(20)
            ->post($base.'/identity/auth/accessToken', ['grant_type' => 'client_credentials']);
        if ($tokenResponse->failed()) {
            return ['paid' => false, 'message' => 'N-Genius verification could not authenticate.'];
        }

        $orderUrl = str_starts_with($stored, 'http')
            ? $stored
            : $base.'/transactions/outlets/'.rawurlencode($outlet).'/orders/'.rawurlencode($stored);

        $response = Http::withToken($tokenResponse->json('access_token'))
            ->withHeaders(['Accept' => 'application/vnd.ni-payment.v2+json'])
            ->timeout(20)
            ->get($orderUrl);

        if ($response->failed()) {
            return ['paid' => false, 'message' => 'N-Genius could not confirm this payment yet.'];
        }

        $state = (string) $response->json('state');
        if (! in_array($state, ['PURCHASED', 'CAPTURED'], true)) {
            return ['paid' => false, 'message' => 'N-Genius payment is '.$state.'.'];
        }

        $reference = (string) ($response->json('merchantOrderReference') ?? $this->details($order)['ngenius_order_reference'] ?? '');
        $amount = (int) ($response->json('amount.value') ?? 0);
        $currency = strtoupper((string) ($response->json('amount.currencyCode') ?? ''));
        $expectedCurrency = strtoupper(PaymentMethods::value('ngenius', 'NGENIUS_CURRENCY', 'AED'));
        if (
            ($reference !== '' && $reference !== $order->order_number)
            || ($amount > 0 && $amount !== $this->minorUnits($order, $expectedCurrency))
            || ($currency !== '' && $currency !== $expectedCurrency)
        ) {
            return ['paid' => false, 'message' => 'N-Genius payment does not match this order.'];
        }

        return ['paid' => true, 'transaction_id' => (string) ($response->json('id') ?: $order->order_number), 'extra' => ['ngenius_state' => $state]];
    }

    private function verifyIyzico(Order $order, Request $request): array
    {
        $token = (string) $request->input('token', '');
        if ($token === '') {
            return ['paid' => false, 'message' => 'Missing iyzico 3D Secure token.'];
        }

        $base = PaymentMethods::isSandbox('iyzico') ? 'https://sandbox-api.iyzipay.com' : 'https://api.iyzipay.com';
        $conversationId = (string) ($this->details($order)['iyzico_conversation_id'] ?? $order->order_number);
        $payload = [
            'locale' => 'en',
            'conversationId' => $conversationId,
            'token' => $token,
        ];
        $body = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $response = Http::withHeaders($this->iyzicoHeaders($body, '/payment/3dsecure/auth'))
            ->withBody($body, 'application/json')
            ->timeout(20)
            ->post($base.'/payment/3dsecure/auth');

        if ($response->failed() || $response->json('status') !== 'success') {
            return ['paid' => false, 'message' => $response->json('errorMessage') ?: 'iyzico could not finalize the 3D Secure payment.'];
        }

        return ['paid' => true, 'transaction_id' => (string) ($response->json('paymentId') ?: $token)];
    }

    private function verifyBkash(Order $order, Request $request): array
    {
        $paymentId = (string) ($request->input('paymentID') ?: $this->details($order)['bkash_payment_id'] ?? '');
        if ($paymentId === '') {
            return ['paid' => false, 'message' => 'Missing bKash payment ID.'];
        }

        $base = $this->bkashBaseUrl();
        $appKey = PaymentMethods::value('bkash', 'BKASH_CHECKOUT_APP_KEY');
        $tokenResponse = Http::withHeaders($this->bkashHeaders())
            ->timeout(20)
            ->post($base.'/token/grant', [
                'app_key' => $appKey,
                'app_secret' => PaymentMethods::value('bkash', 'BKASH_CHECKOUT_APP_SECRET'),
            ]);
        if ($tokenResponse->failed() || ! $tokenResponse->json('id_token')) {
            return ['paid' => false, 'message' => 'bKash verification could not authenticate.'];
        }

        $response = Http::withHeaders($this->bkashHeaders($tokenResponse->json('id_token')))
            ->timeout(20)
            ->post($base.'/execute', ['paymentID' => $paymentId]);

        if ($response->failed() || strtolower((string) $response->json('transactionStatus')) !== 'completed') {
            return ['paid' => false, 'message' => 'bKash has not completed this payment yet.'];
        }

        $invoice = (string) ($response->json('merchantInvoiceNumber') ?? '');
        $amount = (string) ($response->json('amount') ?? '');
        if (
            ($invoice !== '' && $invoice !== $order->order_number)
            || ($amount !== '' && (float) $amount !== (float) $this->amount($order))
        ) {
            return ['paid' => false, 'message' => 'bKash payment does not match this order.'];
        }

        return ['paid' => true, 'transaction_id' => (string) ($response->json('trxID') ?: $paymentId)];
    }

    private function verifyNagad(Order $order, Request $request): array
    {
        $paymentReference = (string) ($request->input('payment_ref_id')
            ?: $request->input('paymentReferenceId')
            ?: $request->input('paymentRefId')
            ?: $request->input('ref')
            ?: $this->details($order)['nagad_payment_reference_id']
            ?? '');
        if ($paymentReference === '') {
            return ['paid' => false, 'message' => 'Missing Nagad payment reference.'];
        }

        $response = Http::withHeaders($this->nagadHeaders())
            ->timeout(20)
            ->get($this->nagadBaseUrl().'verify/payment/'.rawurlencode($paymentReference));

        if ($response->failed()) {
            return ['paid' => false, 'message' => 'Nagad could not confirm this payment yet.'];
        }

        $data = $this->nagadResponseData($response->json() ?: []);
        if ($data === []) {
            return ['paid' => false, 'message' => 'Nagad response could not be verified.'];
        }

        $status = strtolower((string) ($data['status'] ?? ''));
        if ($status !== 'success') {
            return ['paid' => false, 'message' => 'Nagad payment is '.$status.'.'];
        }

        $invoice = (string) ($data['orderId'] ?? '');
        $amount = (string) ($data['amount'] ?? '');
        $merchantId = (string) ($data['merchantId'] ?? '');
        $expectedInvoice = (string) ($this->details($order)['nagad_invoice'] ?? $order->order_number);
        if (
            ($invoice !== '' && $invoice !== $expectedInvoice)
            || ($amount !== '' && (float) $amount !== (float) $this->amount($order))
            || ($merchantId !== '' && $merchantId !== PaymentMethods::value('nagad', 'NAGAD_MERCHANT_ID'))
        ) {
            return ['paid' => false, 'message' => 'Nagad payment does not match this order.'];
        }

        return [
            'paid' => true,
            'transaction_id' => (string) ($data['paymentRefId'] ?? $paymentReference),
            'extra' => ['nagad_status' => $status],
        ];
    }

    private function verifyProxypay(Order $order, Request $request): array
    {
        $provided = (string) $request->input('signature', '');
        $params = $request->except(['signature', '_token']);
        $expected = $this->proxypaySignature($params);

        $status = strtolower((string) ($request->input('status') ?? $request->input('payment_status') ?? ''));
        $successful = in_array($status, ['success', 'paid', 'approved', 'completed'], true);
        $amount = (string) ($request->input('amount') ?? '');
        $currency = strtoupper((string) ($request->input('currency') ?? ''));

        if (
            ! $successful
            || $provided === ''
            || ! hash_equals($expected, $provided)
            || ($amount !== '' && (float) $amount !== (float) $this->amount($order))
            || ($currency !== '' && $currency !== strtoupper($this->currency()))
        ) {
            return ['paid' => false, 'message' => 'ProxyPay response could not be verified.'];
        }

        return ['paid' => true, 'transaction_id' => (string) ($request->input('transaction_id') ?: $order->order_number)];
    }

    private function verifyAamarpay(Order $order, Request $request): array
    {
        $tranId = (string) $request->input('tran_id', '');
        $amount = (string) $request->input('amount', '');
        $status = (string) $request->input('payment_status', '');
        $storeId = PaymentMethods::value('aamarpay', 'AAMARPAY_STORE_ID');
        $signatureKey = PaymentMethods::value('aamarpay', 'AAMARPAY_SIGNATURE_KEY');
        $expected = md5($storeId.$tranId.$amount.$status.$signatureKey);
        $provided = strtolower((string) $request->input('md5sig', ''));

        if ($tranId !== $order->order_number || strtolower($status) !== 'successful' || $provided === '' || ! hash_equals($expected, $provided)) {
            return ['paid' => false, 'message' => 'Aamarpay response could not be verified.'];
        }

        return ['paid' => true, 'transaction_id' => (string) ($request->input('mer_txnid') ?: $tranId)];
    }

    private function verifyAuthorizeNet(Order $order, Request $request): array
    {
        $transactionId = (string) ($request->input('transId') ?: $request->input('x_trans_id') ?? '');
        if ($transactionId === '') {
            return ['paid' => false, 'message' => 'Missing Authorize.Net transaction ID.'];
        }

        $invoice = (string) ($request->input('x_invoice_num') ?: $request->input('invoiceNumber') ?? '');
        if ($invoice !== '' && $invoice !== $order->order_number) {
            return ['paid' => false, 'message' => 'Authorize.Net payment belongs to a different order.'];
        }

        $endpoint = PaymentMethods::isSandbox('authorizenet')
            ? 'https://apitest.authorize.net/xml/v1/request.api'
            : 'https://api.authorize.net/xml/v1/request.api';
        $xml = '<?xml version="1.0" encoding="utf-8"?>'
           .'<getTransactionDetailsRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">'
           .'<merchantAuthentication><name>'.$this->xml(PaymentMethods::value('authorizenet', 'MERCHANT_LOGIN_ID')).'</name>'
           .'<transactionKey>'.$this->xml(PaymentMethods::value('authorizenet', 'MERCHANT_TRANSACTION_KEY')).'</transactionKey></merchantAuthentication>'
           .'<transId>'.$this->xml($transactionId).'</transId></getTransactionDetailsRequest>';

        $response = Http::withHeaders(['Content-Type' => 'text/xml'])
            ->timeout(20)
            ->withBody($xml, 'text/xml')
            ->post($endpoint);

        $body = $response->body();
        $resultCode = $this->xmlValue($body, 'resultCode');
        $transactionStatus = $this->xmlValue($body, 'status');
        if ($response->failed() || $resultCode !== 'Ok' || ! in_array($transactionStatus, ['settledSuccessfully', 'capturedPendingSettlement'], true)) {
            return ['paid' => false, 'message' => 'Authorize.Net has not settled this transaction yet.'];
        }

        $responseInvoice = $this->xmlValue($body, 'invoiceNumber');
        $responseAmount = $this->xmlValue($body, 'amount');
        if (
            ($responseInvoice !== '' && $responseInvoice !== $order->order_number)
            || ($responseAmount !== '' && (float) $responseAmount !== (float) $this->amount($order))
        ) {
            return ['paid' => false, 'message' => 'Authorize.Net payment does not match this order.'];
        }

        return ['paid' => true, 'transaction_id' => $transactionId, 'extra' => ['authorizenet_status' => $transactionStatus]];
    }

    private function verifyPayku(Order $order): array
    {
        $paykuId = (string) ($this->details($order)['payku_order_id'] ?? '');
        if ($paykuId === '') {
            return ['paid' => false, 'message' => 'Missing Payku order reference.'];
        }

        $base = rtrim(PaymentMethods::value('payku', 'PAYKU_BASE_URL', 'https://api.payku.cl'), '/');
        $response = Http::withToken(PaymentMethods::value('payku', 'PAYKU_PRIVATE_TOKEN'))
            ->timeout(20)
            ->get($base.'/api/order/'.rawurlencode($paykuId));

        if ($response->failed()) {
            return ['paid' => false, 'message' => 'Payku could not confirm this payment yet.'];
        }

        $status = strtolower((string) ($response->json('status') ?? ''));
        if (! in_array($status, ['paid', 'approved', 'success'], true)) {
            return ['paid' => false, 'message' => 'Payku payment is '.$status.'.'];
        }

        return ['paid' => true, 'transaction_id' => (string) ($response->json('id') ?: $paykuId)];
    }

    /* ------------------------------------------------------------------ *
     * Order helpers
     * ------------------------------------------------------------------ */

    private function resolveOrder(string $method, Request $request): ?Order
    {
        $references = collect([
            $request->input('order_id'),
            $request->input('order_number'),
            $request->input('external_reference'),
            $request->input('payment_request_id'),
            $request->input('tran_id'),
            $request->input('v_transaction'),
            $request->input('paymentID'),
            $request->input('merchantOrderReference'),
            $request->input('merchant_order_reference'),
            $request->input('order.reference'),
            $request->input('order.merchantOrderReference'),
            $request->input('x_invoice_num'),
            $request->input('receipt'),
            $request->input('reference'),
            $request->input('resource.supplementary_data.related_ids.order_id'),
            $request->input('resource.order_id'),
            $request->input('resource.id'),
            $request->input('data.id'),
            $request->input('data.reference'),
            $request->input('payment.id'),
            $request->input('id'),
        ])->filter(fn ($value) => filled($value));

        // An explicit order reference in a provider callback is stronger than a
        // lingering checkout session from another browser tab.
        foreach ($references as $reference) {
            $order = Order::where('order_number', (string) $reference)->first();
            if ($order && $order->payment_method === $method) {
                return $order;
            }
        }

        $orderId = session('pending_gateway_order_id');
        $provider = session('pending_gateway_provider');
        if ($orderId && $provider === $method) {
            $order = Order::find($orderId);
            if ($order && $order->payment_method === $method) {
                return $order;
            }
        }

        // Webhooks have no checkout session, so match the provider reference
        // saved on the order when the hosted payment was started.
        $escaped = addcslashes((string) ($references->first() ?? ''), '%_\\');
        if ($escaped !== '') {
            $order = Order::where('payment_method', $method)
                ->where('payment_details', 'like', '%'.$escaped.'%')
                ->latest()
                ->first();

            if ($order) {
                return $order;
            }
        }

        // MercadoPago webhooks only carry an opaque payment id. Query the
        // payment so its external_reference can identify the order.
        if ($method === 'mercadopago') {
            $paymentId = (string) ($request->input('data.id') ?: $request->input('collection_id') ?: $request->input('id') ?? '');
            if ($paymentId !== '') {
                $response = Http::withToken(PaymentMethods::value('mercadopago', 'MERCADOPAGO_ACCESS'))
                    ->timeout(20)
                    ->get('https://api.mercadopago.com/v1/payments/'.rawurlencode($paymentId));
                $external = (string) ($response->json('external_reference') ?? '');
                if ($external !== '') {
                    $order = Order::where('order_number', $external)->first() ?: Order::find($external);
                    if ($order && $order->payment_method === $method) {
                        return $order;
                    }
                }
            }
        }

        return null;
    }

    private function markPaid(Order $order, string $provider, ?string $transactionId, array $extra = []): bool
    {
        $fresh = DB::transaction(function () use ($order, $provider, $transactionId, $extra) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->first();
            if (! $locked || $locked->payment_status === 'paid' || $locked->status === 'cancelled') {
                return null;
            }

            $details = $this->details($locked);
            $details['provider'] = $provider;
            $details['transaction_id'] = $transactionId;
            $details['confirmed_at'] = now()->toDateTimeString();
            $details = array_merge($details, $extra);

            $update = [
                'payment_status' => 'paid',
                'payment_details' => json_encode($details),
            ];
            if ($locked->status === 'pending') {
                $update['status'] = 'processing';
            }

            $locked->update($update);
            $locked->recordStatusChange(
                $update['status'] ?? $locked->status,
                'Payment received through '.$this->label($provider).($transactionId ? ' ('.$transactionId.')' : '')
            );

            return $locked;
        });

        if (! $fresh) {
            return false;
        }

        $order->forceFill($fresh->getAttributes());
        $this->notify($fresh);

        return true;
    }

    private function cancelPending(Order $order, string $reason): void
    {
        if ($order->payment_status === 'paid' || $order->status === 'cancelled') {
            return;
        }

        if ($order->stock_deducted) {
            $order->restoreStock();
        }

        $order->update([
            'status' => 'cancelled',
            'payment_status' => 'failed',
            'stock_deducted' => false,
        ]);
        $order->recordStatusChange('cancelled', $reason);
    }

    private function finishCheckout(Order $order): void
    {
        session()->put('last_order', app(CartController::class)->orderSummary($order));
        session()->forget([
            'cart',
            'billing_info',
            'shipping_info',
            'checkout_vehicle_id',
            'coupon',
            'pending_stripe_order_id',
            'pending_gateway_order_id',
            'pending_gateway_provider',
        ]);
    }

    private function notify(Order $order): void
    {
        $order->loadMissing('items.product');
        NotificationHelper::orderPlaced($order);

        $billing = json_decode($order->shipping_details ?? '[]', true) ?: [];
        $toEmail = ($billing['email'] ?? null) ?: ($order->user?->email ?? null);
        if ($toEmail) {
            try {
                Mail::to($toEmail)->send(new OrderConfirmationMail($order));
            } catch (Throwable $exception) {
                logger()->warning('Order confirmation email failed: '.$exception->getMessage());
            }
        }
    }

    private function isWebhookRequest(): bool
    {
        return app()->bound('request')
            && str_contains((string) request()->route()?->getName(), 'webhook');
    }

    private function details(Order $order): array
    {
        $details = json_decode($order->payment_details ?? '[]', true);

        return is_array($details) ? $details : [];
    }

    private function storeDetails(Order $order, array $values): void
    {
        $order->update([
            'payment_details' => json_encode(array_merge($this->details($order), $values)),
        ]);
    }

    private function customer(Order $order): array
    {
        $details = json_decode($order->shipping_details ?? '[]', true) ?: [];
        $defaults = [
            'name' => 'Customer',
            'email' => 'customer@example.com',
            'phone' => '0000000000',
            'address' => 'N/A',
            'city' => 'N/A',
            'country' => 'US',
        ];

        return [
            'name' => trim((string) ($details['name'] ?? $defaults['name'])) ?: $defaults['name'],
            'email' => (string) ($details['email'] ?? $order->user?->email ?? $defaults['email']) ?: $defaults['email'],
            'phone' => (string) ($details['phone'] ?? $defaults['phone']) ?: $defaults['phone'],
            'address' => (string) ($details['address'] ?? $defaults['address']) ?: $defaults['address'],
            'city' => (string) ($details['city'] ?? $defaults['city']) ?: $defaults['city'],
            'country' => (string) ($details['country'] ?? $defaults['country']) ?: $defaults['country'],
        ];
    }

    private function currency(): string
    {
        return strtoupper((string) Setting::get('system_default_currency', 'USD'));
    }

    private function amount(Order $order): string
    {
        return number_format((float) $order->total_amount, 2, '.', '');
    }

    private function minorUnits(Order $order, string $currency): int
    {
        return in_array(strtoupper($currency), self::ZERO_DECIMAL_CURRENCIES, true)
            ? (int) round((float) $order->total_amount)
            : (int) round((float) $order->total_amount * 100);
    }

    private function bkashBaseUrl(): string
    {
        return PaymentMethods::isSandbox('bkash')
            ? 'https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized/checkout'
            : 'https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized/checkout';
    }

    private function bkashHeaders(?string $token = null): array
    {
        $headers = [
            'X-App-Key' => PaymentMethods::value('bkash', 'BKASH_CHECKOUT_APP_KEY'),
            'Username' => PaymentMethods::value('bkash', 'BKASH_CHECKOUT_USER_NAME'),
            'Password' => PaymentMethods::value('bkash', 'BKASH_CHECKOUT_PASSWORD'),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        if ($token) {
            $headers['Authorization'] = 'Bearer '.$token;
        }

        return $headers;
    }

    private function nagadBaseUrl(): string
    {
        $mode = strtolower(trim(PaymentMethods::value('nagad', 'NAGAD_MODE', 'sandbox')));

        return in_array($mode, ['live', 'production', 'prod'], true)
            ? 'https://api.mynagad.com/api/dfs/'
            : 'http://sandbox.mynagad.com:10080/remote-payment-gateway-1.0/api/dfs/';
    }

    private function nagadHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-KM-Api-Version' => 'v-0.2.0',
            'X-KM-IP-V4' => request()->ip() ?: '127.0.0.1',
            'X-KM-Client-Type' => 'PC_WEB',
        ];
    }

    /** @return array<int, string> */
    private function nagadPrivateKeys(): array
    {
        $value = trim(PaymentMethods::value('nagad', 'NAGAD_MERCHANT_PRIVATE_KEY'));
        if ($value === '') {
            return [];
        }

        if (str_contains($value, 'BEGIN')) {
            $keys = [$value];
            if (str_contains($value, 'BEGIN PRIVATE KEY')) {
                $body = trim(preg_replace('/-----[^-]+-----/', '', $value) ?: '');
                $keys[] = "-----BEGIN RSA PRIVATE KEY-----\n".$body."\n-----END RSA PRIVATE KEY-----\n";
            }

            return array_values(array_unique($keys));
        }

        $body = chunk_split(preg_replace('/\s+/', '', $value) ?: '', 64, "\n");

        return [
            "-----BEGIN PRIVATE KEY-----\n".$body."-----END PRIVATE KEY-----\n",
            "-----BEGIN RSA PRIVATE KEY-----\n".$body."-----END RSA PRIVATE KEY-----\n",
        ];
    }

    private function nagadPublicKey(): string
    {
        $value = trim(PaymentMethods::value('nagad', 'NAGAD_PG_PUBLIC_KEY'));
        if ($value === '') {
            return '';
        }

        if (str_contains($value, 'BEGIN')) {
            return $value;
        }

        return "-----BEGIN PUBLIC KEY-----\n".chunk_split(preg_replace('/\s+/', '', $value) ?: '', 64, "\n")."-----END PUBLIC KEY-----\n";
    }

    private function nagadEncrypt(string $data): string
    {
        $key = $this->nagadPublicKey();
        if ($key === '' || ! openssl_public_encrypt($data, $encrypted, $key, OPENSSL_PKCS1_PADDING)) {
            throw new RuntimeException('Nagad gateway public key is invalid.');
        }

        return base64_encode($encrypted);
    }

    private function nagadDecrypt(string $data): string
    {
        $encrypted = base64_decode($data, true) ?: $data;
        foreach ($this->nagadPrivateKeys() as $key) {
            if (openssl_private_decrypt($encrypted, $plain, $key, OPENSSL_PKCS1_PADDING)) {
                return (string) $plain;
            }
        }

        throw new RuntimeException('Nagad merchant private key is invalid.');
    }

    private function nagadSign(string $data): string
    {
        foreach ($this->nagadPrivateKeys() as $key) {
            if (openssl_sign($data, $signature, $key, OPENSSL_ALGO_SHA256)) {
                return base64_encode($signature);
            }
        }

        throw new RuntimeException('Nagad merchant private key is invalid.');
    }

    private function nagadVerify(string $data, string $signature): bool
    {
        $key = $this->nagadPublicKey();
        if ($key === '') {
            return false;
        }

        return openssl_verify($data, base64_decode($signature, true) ?: $signature, $key, OPENSSL_ALGO_SHA256) === 1;
    }

    private function nagadResponseData(array $response): array
    {
        $sensitive = (string) ($response['sensitiveData'] ?? '');
        $signature = (string) ($response['signature'] ?? '');
        if ($sensitive === '') {
            return [];
        }

        $plain = $this->nagadDecrypt($sensitive);
        if ($signature !== '' && ! $this->nagadVerify($plain, $signature)) {
            return [];
        }

        $decoded = json_decode($plain, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function iyzicoHeaders(string $body, string $uriPath): array
    {
        $apiKey = PaymentMethods::value('iyzico', 'IYZICO_API_KEY');
        $secret = PaymentMethods::value('iyzico', 'IYZICO_SECRET_KEY');
        $random = Str::random(12);
        $signature = base64_encode(hash_hmac('sha256', $random.$uriPath.$body, $secret, true));
        $authorization = base64_encode('apiKey:'.$apiKey.'&randomKey:'.$random.'&signature:'.$signature);

        return [
            'Authorization' => 'IYZWSv2 '.$authorization,
            'x-iyzi-rnd' => $random,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function xmlValue(string $xml, string $tag): string
    {
        if (preg_match('/<'.preg_quote($tag, '/').'>(.*?)<\/'.preg_quote($tag, '/').'>/s', $xml, $matches)) {
            return trim(html_entity_decode($matches[1], ENT_XML1 | ENT_QUOTES, 'UTF-8'));
        }

        return '';
    }
}
