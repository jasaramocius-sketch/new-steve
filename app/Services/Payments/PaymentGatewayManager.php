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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;

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
            $this->finishCheckout($order);

            return [
                'order' => $order,
                'paid' => true,
                'message' => 'Payment already confirmed.',
            ];
        }

        $result = $this->verifyProvider($method, $order, $request);

        if (! empty($result['paid'])) {
            $this->markPaid(
                $order,
                $method,
                $result['transaction_id'] ?? null,
                $result['extra'] ?? []
            );
            $this->finishCheckout($order);
        }

        return [
            'order' => $order,
            'paid' => (bool) ($result['paid'] ?? false),
            'message' => $result['message'] ?? 'Payment is still being confirmed by the provider.',
        ];
    }

    public function handleWebhook(string $method, Request $request)
    {
        $result = $this->confirm($method, $request);

        return response()->json([
            'received' => true,
            'paid' => $result['paid'],
        ]);
    }

    public function cancel(string $method, Request $request): void
    {
        $order = $this->resolveOrder($method, $request);
        if ($order) {
            $this->cancelPending($order, $this->label($method).' checkout was cancelled');
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
        if (! empty($result['paid'])) {
            $this->markPaid($order, 'razorpay', $paymentId, $result['extra'] ?? []);
            $this->finishCheckout($order);
        }

        return [
            'order' => $order,
            'paid' => (bool) ($result['paid'] ?? false),
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
                'application_context' => [
                    'brand_name' => config('app.name', 'Store'),
                    'return_url' => route('checkout.gateway.return', 'paypal'),
                    'cancel_url' => route('checkout.gateway.cancel', 'paypal'),
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
        $response = Http::withToken(PaymentMethods::value('mercadopago', 'MERCADOPAGO_ACCESS'))
            ->timeout(20)
            ->post('https://api.mercadopago.com/checkout/preferences', [
                'items' => [[
                    'title' => 'Order '.$order->order_number,
                    'quantity' => 1,
                    'currency_id' => $this->currency(),
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
            ]);

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

        return ['html' => view('checkout.gateway.razorpay', [
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

        return ['html' => view('checkout.gateway.auto-submit', [
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
            'ngenius_order_id' => $response->json('_links.self.href') ?: $response->json('id'),
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
        $response = Http::withHeaders($this->iyzicoHeaders($body, $conversationId))
            ->withBody($body, 'application/json')
            ->timeout(20)
            ->post($base.'/payment/3dsecure/initialize');

        if ($response->failed()) {
            throw new RuntimeException($response->json('errorMessage') ?: 'iyzico could not start the payment.');
        }

        $html = $response->json('threeDSHtmlContent');
        if (! $html && $response->json('redirectUrl')) {
            return ['redirect' => $response->json('redirectUrl')];
        }
        if (! $html) {
            throw new RuntimeException('iyzico did not return a 3D Secure checkout form.');
        }

        $this->storeDetails($order, [
            'iyzico_conversation_id' => $conversationId,
            'status' => 'initiated',
        ]);

        return ['html' => view('checkout.gateway.raw', [
            'title' => 'Redirecting to iyzico...',
            'html' => $html,
        ])->render()];
    }

    private function startBkash(Order $order): array
    {
        $base = PaymentMethods::isSandbox('bkash')
            ? 'https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized'
            : 'https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized';
        $appKey = PaymentMethods::value('bkash', 'BKASH_CHECKOUT_APP_KEY');

        $tokenResponse = Http::withHeaders([
            'X-App-Key' => $appKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->timeout(20)->post($base.'/grant', [
            'app_key' => $appKey,
            'app_secret' => PaymentMethods::value('bkash', 'BKASH_CHECKOUT_APP_SECRET'),
        ]);

        if ($tokenResponse->failed() || ! $tokenResponse->json('id_token')) {
            throw new RuntimeException('bKash authentication failed. Check the saved app credentials.');
        }

        $response = Http::withToken($tokenResponse->json('id_token'))
            ->withHeaders(['X-App-Key' => $appKey, 'Accept' => 'application/json'])
            ->withBasicAuth(
                PaymentMethods::value('bkash', 'BKASH_CHECKOUT_USER_NAME'),
                PaymentMethods::value('bkash', 'BKASH_CHECKOUT_PASSWORD')
            )
            ->timeout(20)
            ->post($base.'/payment/create', [
                'amount' => $this->amount($order),
                'currency' => 'BDT',
                'intent' => 'sale',
                'merchantInvoiceNumber' => $order->order_number,
                'callbackURL' => route('checkout.gateway.return', 'bkash'),
            ]);

        if ($response->failed() || ! $response->json('paymentURL')) {
            throw new RuntimeException($response->json('errorMessage') ?: 'bKash could not create the payment.');
        }

        $this->storeDetails($order, [
            'bkash_payment_id' => $response->json('paymentID'),
            'status' => 'initiated',
        ]);

        return ['redirect' => $response->json('paymentURL')];
    }

    private function startNagad(Order $order): array
    {
        // Nagad's hosted checkout requires per-request RSA encryption and a
        // signed initialize call with merchant-specific gateway certificates.
        // Credentials are validated above; the order is kept unpaid until an
        // administrator confirms settlement from the Nagad merchant dashboard.
        $this->storeDetails($order, [
            'status' => 'awaiting_manual_confirmation',
            'gateway_reference' => $order->order_number,
        ]);

        return ['pending' => true, 'order' => $order];
    }

    private function startProxypay(Order $order): array
    {
        $merchant = PaymentMethods::value('proxypay', 'PROXYPAY_MERCHANT_ID');
        $secret = PaymentMethods::value('proxypay', 'PROXYPAY_SECRET_KEY');
        $baseUrl = rtrim(PaymentMethods::value('proxypay', 'PROXYPAY_BASE_URL'), '/');
        $params = [
            'merchant' => $merchant,
            'order_id' => $order->order_number,
            'amount' => $this->amount($order),
            'currency' => $this->currency(),
            'return_url' => route('checkout.gateway.return', 'proxypay'),
            'cancel_url' => route('checkout.gateway.cancel', 'proxypay'),
        ];
        $params['signature'] = hash_hmac('sha256', implode('|', $params), $secret);

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

        return ['html' => view('checkout.gateway.auto-submit', [
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
            'hostedPaymentReturnOptions' => ['showReceipt' => true, 'url' => route('checkout.gateway.return', 'authorizenet'), 'urlText' => 'Continue'],
            'hostedPaymentButtonOptions' => ['text' => 'Pay'],
            'hostedPaymentPaymentOptions' => ['cardCodeRequired' => false],
            'hostedPaymentBillingAddressOptions' => ['show' => true, 'required' => true],
        ];

        $settingXml = '';
        foreach ($settings as $name => $setting) {
            $values = '';
            foreach ($setting as $settingName => $settingValue) {
                $values .= '<setting><name>'.$this->xml($settingName).'</name><value>'.$this->xml((string) $settingValue).'</value></setting>';
            }
            $settingXml .= '<setting><name>'.$this->xml($name).'</name><value>'.$values.'</value></setting>';
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

        return ['redirect' => 'https://accept.authorize.net/payment/payment/form?token='.rawurlencode($token)];
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
            'nagad' => ['paid' => false, 'message' => 'Nagad payments need to be confirmed from the merchant dashboard before this order is marked paid.'],
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
            $capture = $http->post($base.'/v2/checkout/orders/'.rawurlencode($paypalOrderId).'/capture', new \stdClass());
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
        if ($reference !== '' && $reference !== (string) $order->id) {
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
        if ($response->failed() || ! in_array($status, ['VALID', 'VALIDATED'], true) || ($responseTranId !== '' && $responseTranId !== $order->order_number)) {
            return ['paid' => false, 'message' => 'SSLCommerz could not validate this payment.'];
        }

        return ['paid' => true, 'transaction_id' => (string) ($response->json('bank_tran_id') ?: $valId), 'extra' => ['sslcommerz_val_id' => $valId]];
    }

    private function verifyInstamojo(Order $order, Request $request): array
    {
        $paymentId = (string) ($request->input('payment_id') ?: '');
        $requestId = (string) ($request->input('payment_request_id') ?: $this->details($order)['instamojo_request_id'] ?? '');
        if ($paymentId === '') {
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
        if ($requestId !== '' && $returnedRequest !== '' && $returnedRequest !== $requestId) {
            return ['paid' => false, 'message' => 'This Instamojo payment belongs to a different request.'];
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
        if ($signature !== '' && ! hash_equals(hash_hmac('sha512', $request->getContent(), $secret), $signature)) {
            return ['paid' => false, 'message' => 'Paystack webhook signature is invalid.'];
        }

        $reference = (string) ($request->input('reference') ?: $request->input('trxref') ?: $request->input('data.reference') ?: $order->order_number);
        $response = Http::withToken($secret)->timeout(20)->get('https://api.paystack.co/transaction/verify/'.rawurlencode($reference));

        if ($response->failed() || $response->json('data.status') !== 'success') {
            return ['paid' => false, 'message' => 'Paystack payment is not successful yet.'];
        }

        $currency = PaymentMethods::value('paystack', 'PAYSTACK_CURRENCY_CODE', $this->currency());
        if ((int) $response->json('data.amount') !== $this->minorUnits($order, $currency)) {
            return ['paid' => false, 'message' => 'Paystack payment amount does not match this order.'];
        }

        return ['paid' => true, 'transaction_id' => (string) ($response->json('data.id') ?: $reference), 'extra' => ['paystack_reference' => $reference]];
    }

    private function verifyVoguePay(Order $order, Request $request): array
    {
        $transaction = (string) ($request->input('v_transaction') ?: $request->input('transaction_id') ?: $request->input('id') ?? '');
        if ($transaction === '') {
            return ['paid' => false, 'message' => 'Missing VoguePay transaction reference.'];
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

        return ['paid' => true, 'transaction_id' => $transaction];
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
        $base = PaymentMethods::isSandbox('ngenius') ? 'https://api-gateway.sandbox.ngenius-payments.com' : 'https://api-gateway.ngenius-payments.com';
        $orderUrl = (string) ($this->details($order)['ngenius_order_id'] ?? '');

        if ($orderUrl === '') {
            return ['paid' => false, 'message' => 'Missing N-Genius order reference.'];
        }

        $tokenResponse = Http::asForm()
            ->withHeaders(['Authorization' => 'Basic '.base64_encode($outlet.':'.$apiKey)])
            ->timeout(20)
            ->post($base.'/identity/auth/accessToken', ['grant_type' => 'client_credentials']);
        if ($tokenResponse->failed()) {
            return ['paid' => false, 'message' => 'N-Genius verification could not authenticate.'];
        }

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
        $response = Http::withHeaders($this->iyzicoHeaders($body, $conversationId))
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

        $base = PaymentMethods::isSandbox('bkash')
            ? 'https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized'
            : 'https://tokenized.pay.bka.sh/v1.2.0-beta/tokenized';
        $appKey = PaymentMethods::value('bkash', 'BKASH_CHECKOUT_APP_KEY');
        $tokenResponse = Http::withHeaders(['X-App-Key' => $appKey, 'Accept' => 'application/json'])
            ->timeout(20)
            ->post($base.'/grant', [
                'app_key' => $appKey,
                'app_secret' => PaymentMethods::value('bkash', 'BKASH_CHECKOUT_APP_SECRET'),
            ]);
        if ($tokenResponse->failed()) {
            return ['paid' => false, 'message' => 'bKash verification could not authenticate.'];
        }

        $response = Http::withToken($tokenResponse->json('id_token'))
            ->withHeaders(['X-App-Key' => $appKey, 'Accept' => 'application/json'])
            ->withBasicAuth(
                PaymentMethods::value('bkash', 'BKASH_CHECKOUT_USER_NAME'),
                PaymentMethods::value('bkash', 'BKASH_CHECKOUT_PASSWORD')
            )
            ->timeout(20)
            ->post($base.'/payment/execute', ['paymentID' => $paymentId]);

        if ($response->failed() || strtolower((string) $response->json('transactionStatus')) !== 'completed') {
            return ['paid' => false, 'message' => 'bKash has not completed this payment yet.'];
        }

        return ['paid' => true, 'transaction_id' => (string) ($response->json('trxID') ?: $paymentId)];
    }

    private function verifyProxypay(Order $order, Request $request): array
    {
        $provided = (string) $request->input('signature', '');
        $params = $request->except(['signature', '_token']);
        ksort($params);
        $expected = hash_hmac('sha256', implode('|', array_map('strval', $params)), PaymentMethods::value('proxypay', 'PROXYPAY_SECRET_KEY'));

        $status = strtolower((string) ($request->input('status') ?? $request->input('payment_status') ?? ''));
        $successful = in_array($status, ['success', 'paid', 'approved', 'completed'], true);
        if (! $successful || $provided === '' || ! hash_equals($expected, $provided)) {
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

        $resultCode = $this->xmlValue($response->body(), 'resultCode');
        $transactionStatus = $this->xmlValue($response->body(), 'status');
        if ($response->failed() || $resultCode !== 'Ok' || ! in_array($transactionStatus, ['settledSuccessfully', 'capturedPendingSettlement'], true)) {
            return ['paid' => false, 'message' => 'Authorize.Net has not settled this transaction yet.'];
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
        $orderId = session('pending_gateway_order_id');
        $provider = session('pending_gateway_provider');

        if ($orderId && $provider === $method) {
            $order = Order::find($orderId);
            if ($order && $order->payment_method === $method) {
                return $order;
            }
        }

        $reference = collect([
            $request->input('order_id'),
            $request->input('order_number'),
            $request->input('external_reference'),
            $request->input('payment_request_id'),
            $request->input('tran_id'),
            $request->input('v_transaction'),
            $request->input('paymentID'),
            $request->input('merchantOrderReference'),
            $request->input('merchant_order_reference'),
            $request->input('receipt'),
            $request->input('reference'),
            $request->input('resource.supplementary_data.related_ids.order_id'),
            $request->input('resource.order_id'),
            $request->input('resource.id'),
            $request->input('data.id'),
            $request->input('data.reference'),
            $request->input('payment.id'),
            $request->input('id'),
        ])->first(fn ($value) => filled($value));

        if (! $reference) {
            return null;
        }

        $reference = (string) $reference;
        $order = Order::where('order_number', $reference)->first();

        if (! $order) {
            // Webhooks have no checkout session, so match the provider reference
            // saved on the order when the hosted payment was started.
            $order = Order::where('payment_method', $method)
                ->where('payment_details', 'like', '%'.$reference.'%')
                ->latest()
                ->first();
        }

        return $order && $order->payment_method === $method ? $order : null;
    }

    private function markPaid(Order $order, string $provider, ?string $transactionId, array $extra = []): void
    {
        if ($order->payment_status === 'paid') {
            return;
        }

        $details = $this->details($order);
        $details['provider'] = $provider;
        $details['transaction_id'] = $transactionId;
        $details['confirmed_at'] = now()->toDateTimeString();
        $details = array_merge($details, $extra);

        $update = [
            'payment_status' => 'paid',
            'payment_details' => json_encode($details),
        ];
        if ($order->status === 'pending') {
            $update['status'] = 'processing';
        }

        $order->update($update);
        $order->recordStatusChange(
            $update['status'] ?? $order->status,
            'Payment received through '.$this->label($provider).($transactionId ? ' ('.$transactionId.')' : '')
        );

        $this->notify($order);
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
            Mail::to($toEmail)->queue(new OrderConfirmationMail($order));
        }
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

    private function iyzicoHeaders(string $body, string $conversationId): array
    {
        $apiKey = PaymentMethods::value('iyzico', 'IYZICO_API_KEY');
        $secret = PaymentMethods::value('iyzico', 'IYZICO_SECRET_KEY');
        $random = Str::random(12);
        $hash = base64_encode(hash_hmac('sha256', $apiKey.$random.$conversationId.$body, $secret, true));

        return [
            'Authorization' => 'IYZWS '.$apiKey.':'.$hash,
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
