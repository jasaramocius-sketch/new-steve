@extends('layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'checkout-payment', 'pageClass' => 'checkout-page payment-step-body bg-light'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Payment - ' . config('app.name', 'StAutoparts'),
        'metaTitle' => 'Payment | ' . config('app.name', 'StAutoparts'),
        'metaDescription' => 'Complete your payment at ' . config('app.name', 'StAutoparts') . '.',
    ])
@endsection

@section('content')

<style>
.payment-step-body .payment-radio {
    position: fixed;
    left: -9999px;
    opacity: 0;
}
</style>
@include('partials.checkout-steps', ['activeStep' => 4])

<section class="py-4">
  <div class="container">
    <div class="row">
      <div class="mx-auto">
        <div class="border bg-white p-4 mb-4">
          <div class="row">
          <div class="col-lg-8">
            <form action="{{ route('checkout.payment.submit') }}" class="form-default" role="form" method="POST" id="checkout-form">
              @csrf

              <div class="card rounded-0 border shadow-none">
                <div class="card-header p-4 border-bottom-0 bg-white">
                  <h3 class="fs-16 fw-700 text-dark mb-0">Any additional info?</h3>
                </div>
                <div class="form-group px-4">
                  <textarea name="additional_info" rows="5" class="form-control textarea-control texteditor rounded-0" placeholder="Type your text..."></textarea>
                </div>

                <div class="card-header p-4 border-bottom-0 bg-white">
                  <h3 class="fs-16 fw-700 text-dark mb-0">Select a payment option</h3>
                </div>

                <div class="card-body text-center px-4 pt-0">
                  <div class="row gutters-10">
                    @forelse($paymentMethods as $method => $paymentOption)
                    <div class="col-6 col-xl-4 col-md-4 w-33">
                      <label class="aiz-megabox d-block mb-3" onclick="document.getElementById('pm_{{ $method }}').checked=true;">
                        <input value="{{ $method }}" type="radio" name="payment_method" id="pm_{{ $method }}" class="payment-radio" @checked($method === 'cod' || ($loop->first && !array_key_exists('cod', $paymentMethods)))>
                        <span class="d-block aiz-megabox-elem rounded-0 p-3">
                          <i class="{{ $paymentOption['icon'] }} la-3x d-block mb-2 text-primary"></i>
                          <span class="d-block text-center">
                            <span class="d-block fw-600 fs-15">{{ $paymentOption['label'] }}</span>
                          </span>
                        </span>
                      </label>
                    </div>
                    @empty
                    <div class="col-12">
                      <div class="alert alert-warning mb-0">No payment methods are currently available.</div>
                    </div>
                    @endforelse
                  </div>

                  @if (array_key_exists('card', $paymentMethods))
                  <div class="alert alert-info text-left mt-4 mb-0" role="note">
                    Stripe payments are completed on Stripe's secure checkout page. You will be redirected there after placing the order, and your card details are never stored on our servers.
                  </div>
                  @endif
                </div>

                <div class="pt-3 px-4 fs-14 pb-4">
                  <label class="aiz-checkbox mb-0">
                    <input type="checkbox" required id="agree_checkbox">
                    <span class="aiz-square-check"></span>
                    <span>I agree to the</span>
                  </label>
                  <a href="{{ url('/terms') }}" class="fw-700">terms and conditions</a>,
                  <a href="{{ url('/return-policy') }}" class="fw-700">return policy</a> &amp;
                  <a href="{{ url('/privacy-policy') }}" class="fw-700">privacy policy</a>
                </div>
              </div>
            </form>
          </div>
      

          <div class="col-lg-4 mt-lg-0 mt-4" id="cart_summary">
            <div class="card rounded-0 border shadow-none">
              <div class="card-header pt-4 pb-1 border-bottom-0 bg-white payment-page-summary-header">
                <h3 class="fs-16 fw-700 mb-0">Summary</h3>
                <div class="text-right">
                  <span class="badge badge-inline badge-primary fs-12 rounded px-2">
                    {{ count($cart) }} Items
                  </span>
                </div>
              </div>

              <div class="card-body">
                <table class="table summary-table">
                  <thead>
                    <tr>
                      <th class="product-name border-bottom-1 pl-0 fs-12 fw-400 opacity-60">Product</th>
                      <th class="product-total text-right border-bottom-1 pr-0 fs-12 fw-400 opacity-60">Total</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($cart as $item)
                    <tr class="cart_item">
                      <td class="product-name pl-0 fs-14 text-dark fw-400 border-top-0 border-bottom">
                        {{ $item['name'] }}
                        <strong class="product-quantity">x {{ $item['qty'] }}</strong>
                      </td>
                      <td class="product-total text-right pr-0 fs-14 text-primary fw-600 border-top-0 border-bottom">
                        <span class="pl-4 pr-0">{{ currency_format($item['price'] * $item['qty']) }}</span>
                      </td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>

                <input type="hidden" id="sub_total" value="{{ $total }}">

                <table class="table" style="margin-top: 2rem!important;">
                  <tfoot>
                    <tr class="cart-subtotal">
                      <th class="pl-0 fs-14 pb-2 text-dark fw-600">Subtotal</th>
                      <td class="text-right pr-0 fs-14 pb-2 fw-600 text-primary">
                        <span class="fw-600">{{ currency_format($total) }}</span>
                      </td>
                    </tr>
                    <tr class="cart-shipping">
                      <th class="pl-0 fs-14 pb-2 text-dark fw-600">Tax ({{ $taxRate }}%)</th>
                      <td class="text-right pr-0 fs-14 pb-2 fw-600 text-primary">
                        <span class="fw-600">{{ currency_format($tax) }}</span>
                      </td>
                    </tr>
                    <tr class="cart-shipping">
                      <th class="pl-0 fs-14 pb-2 text-dark fw-600 border-top-0">Total Shipping</th>
                      <td class="text-right pr-0 fs-14 pb-2 fw-600 text-primary border-top-0">
                        <span class="fw-600">{{ currency_format($shippingCost) }}</span>
                      </td>
                    </tr>
                    @if($shippingMethod === 'home_delivery')
                    <tr class="cart-shipping">
                      <th class="pl-0 fs-14 pb-2 text-dark fw-600 border-top-0">Shipping Method</th>
                      <td class="text-right pr-0 fs-14 pb-2 text-primary border-top-0">
                        <strong>{{ session('shipping_info.carrier_name') }}</strong>
                        @if(session('shipping_info.delivery_estimate'))
                          <br><small>{{ session('shipping_info.delivery_estimate') }}</small>
                        @endif
                      </td>
                    </tr>
                    @endif
                    @if($shippingMethod === 'pickup' && session('shipping_info.pickup_point'))
                    @php($pickupPoint = session('shipping_info.pickup_point'))
                    <tr class="cart-shipping">
                      <th class="pl-0 fs-14 pb-2 text-dark fw-600">Pickup Point</th>
                      <td class="text-right pr-0 fs-14 pb-2 text-primary">
                        <strong>{{ $pickupPoint['name'] }}</strong><br>
                        <small>{{ $pickupPoint['location'] }} · {{ $pickupPoint['contact'] }}</small>
                      </td>
                    </tr>
                    @endif
                    @if(!empty($couponData))
                    <tr class="cart-coupon">
                      <th class="pl-0 fs-14 pb-2 text-success fw-600">
                        Coupon ({{ $couponData['code'] }})
                      </th>
                      <td class="text-right pr-0 fs-14 pb-2 fw-600 text-success">
                        <span class="fw-600">-{{ currency_format($couponDiscount) }}</span>
                      </td>
                    </tr>
                    @endif
                    <tr class="cart-total">
                      <th class="pl-0 fs-14 text-dark fw-600"><span class="strong-600">Total</span></th>
                      <td class="text-right pr-0 fs-14 fw-600 text-primary">
                        <strong><span>{{ currency_format($grandTotal) }}</span></strong>
                      </td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>
          <div class="row align-items-center cp-actions-row mt-5">
            <div class="col-6 cp-actions-left">
              <a href="{{ route('home') }}" class="btn-link fw-700 px-0 a-tag-hover-color">
                <i class="fas fa-arrow-left fs-16"></i>
                Return to shop
              </a>
            </div>
            <div class="col-6 text-right cp-actions-right">
              <button type="submit" form="checkout-form" class="btn btn-primary fw-700 px-4 steve-btn">Complete Order</button>
            </div>
          </div>
          </div>
        </div>
      </div>
    </div>
    
  </div>
</section>

@endsection
