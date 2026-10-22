@php
  $checkoutSteps = [
    1 => ['1. My Cart', 'las la-shopping-cart', route('cart')],
    2 => ['2. Shipping info', 'las la-map', route('checkout')],
    3 => ['3. Delivery info', 'las la-truck', route('checkout.delivery-info')],
    4 => ['4. Payment', 'las la-credit-card', route('checkout.payment')],
    5 => ['5. Confirmation', 'las la-check-circle', route('checkout.confirmed')],
  ];
@endphp
<section class="pt-5 mb-4" id="cart-summary">
  <div class="container">
    <div class="row">
      <div class="mx-auto">
        <div class="row gutters-5 sm-gutters-10">
          @foreach($checkoutSteps as $step => $info)
            @php
              $label = $info[0];
              $icon = $info[1];
              $url = $info[2];
            @endphp

            @if($step < $activeStep)
              <div class="col done">
                <a href="{{ $url }}" class="d-block text-reset">
                  <div class="text-center border border-bottom-6px p-2 text-success">
                    <i class="la-3x mb-2 {{ $icon }}"></i>
                    <h3 class="fs-14 fw-600 d-none d-lg-block">{{ $label }}</h3>
                  </div>
                </a>
              </div>
            @elseif($step == $activeStep)
              <div class="col active">
                <div class="text-center border border-bottom-6px p-2 text-primary">
                  <i class="la-3x mb-2 {{ $icon }} cart-animate"></i>
                  <h3 class="fs-14 fw-600 d-none d-lg-block">{{ $label }}</h3>
                </div>
              </div>
            @else
              <div class="col">
                <div class="text-center border border-bottom-6px p-2">
                  <i class="la-3x mb-2 opacity-50 {{ $icon }}"></i>
                  <h3 class="fs-14 fw-600 d-none d-lg-block opacity-50">{{ $label }}</h3>
                </div>
              </div>
            @endif
          @endforeach
        </div>
        </div>
    </div>
  </div>
</section>
