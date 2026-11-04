<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Services\StripeCheckoutService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StripeCheckoutServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('settings');
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Setting::set('payment_method_stripe', Crypt::encryptString(json_encode([
            'STRIPE_KEY' => 'pk_test_example',
            'STRIPE_SECRET' => 'sk_test_example',
        ])));
        Setting::set('system_default_currency', 'USD');
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('settings');

        parent::tearDown();
    }

    public function test_it_uses_the_saved_credentials_to_create_a_checkout_session(): void
    {
        Http::fake([
            'api.stripe.com/v1/checkout/sessions' => Http::response([
                'id' => 'cs_test_123',
                'url' => 'https://checkout.stripe.com/c/pay/cs_test_123',
            ]),
        ]);

        $session = app(StripeCheckoutService::class)->createCheckoutSession(
            [['name' => 'Order ORD1 (1 item)', 'amount' => 19.99, 'quantity' => 1]],
            'ORD1',
            ['order_id' => 42, 'email' => 'buyer@example.com']
        );

        $this->assertSame('cs_test_123', $session->id);

        Http::assertSent(function ($request) {
            $data = $request->data();

            return $request->url() === 'https://api.stripe.com/v1/checkout/sessions'
                && $request->hasHeader('Authorization', 'Bearer sk_test_example')
                && $data['mode'] === 'payment'
                && $data['client_reference_id'] === 'ORD1'
                && $data['metadata[order_id]'] === '42'
                && $data['customer_email'] === 'buyer@example.com'
                && $data['line_items[0][price_data][currency]'] === 'USD'
                && $data['line_items[0][price_data][unit_amount]'] === '1999'
                && $data['line_items[0][price_data][product_data][name]'] === 'Order ORD1 (1 item)';
        });
    }

    public function test_it_reports_configuration_and_retrieves_a_paid_session(): void
    {
        $service = app(StripeCheckoutService::class);

        $this->assertTrue($service->isConfigured());

        Http::fake([
            'api.stripe.com/v1/checkout/sessions/cs_test_123' => Http::response([
                'id' => 'cs_test_123',
                'payment_status' => 'paid',
                'metadata' => ['order_id' => '42'],
            ]),
        ]);

        $session = $service->retrieveCheckoutSession('cs_test_123');

        $this->assertSame('paid', $session->payment_status);
    }

    public function test_it_fails_clearly_when_stripe_is_not_configured(): void
    {
        Setting::set('payment_method_stripe', Crypt::encryptString(json_encode([])));

        $service = app(StripeCheckoutService::class);

        $this->assertFalse($service->isConfigured());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Stripe secret key is not configured.');

        $service->createCheckoutSession(
            [['name' => 'Order ORD1', 'amount' => 10, 'quantity' => 1]],
            'ORD1'
        );
    }
}
