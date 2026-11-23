<?php

namespace App\Http\Controllers;

use App\Services\Payments\PaymentGatewayManager;
use Illuminate\Http\Request;

class PaymentGatewayController extends Controller
{
    public function return(Request $request, string $provider)
    {
        $result = app(PaymentGatewayManager::class)->confirm($provider, $request);

        if ($result['paid']) {
            return redirect()->route('checkout.confirmed')
                ->with('success', 'Payment received. Thank you!');
        }

        return redirect()->route('checkout.payment')
            ->with('error', $result['message'] ?? 'Payment could not be confirmed. Please try again.');
    }

    public function cancel(Request $request, string $provider)
    {
        app(PaymentGatewayManager::class)->cancel($provider, $request);

        return redirect()->route('checkout.payment')
            ->with('error', 'Payment was cancelled. Your cart is still saved, so you can choose another method.');
    }

    public function webhook(Request $request, string $provider)
    {
        return app(PaymentGatewayManager::class)->handleWebhook($provider, $request);
    }

    public function verifyRazorpay(Request $request)
    {
        $result = app(PaymentGatewayManager::class)->confirmRazorpay($request);

        if ($result['paid']) {
            return redirect()->route('checkout.confirmed')
                ->with('success', 'Payment received. Thank you!');
        }

        return redirect()->route('checkout.payment')
            ->with('error', $result['message'] ?? 'Razorpay payment could not be confirmed.');
    }
}
