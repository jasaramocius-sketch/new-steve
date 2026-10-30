@extends('layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'cart-page', 'pageClass' => 'cart-page'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Cart - ' . config('app.name', 'StAutoparts'),
        'metaTitle' => 'Cart | ' . config('app.name', 'StAutoparts'),
        'metaDescription' => 'View your shopping cart at ' . config('app.name', 'StAutoparts') . '.',
    ])
@endsection
@section('content')

@include('partials.checkout-steps', ['activeStep' => 1])
<section class="mb-4">
  <div class="container">
    <div class="row cols-xs-space cols-sm-space cols-md-space">
      <div class="mx-auto">
        @if(count($cart) > 0)
        <div class="border bg-white p-3 p-lg-4">
          <div class="mb-4">
            <div class="row gutters-5 d-none d-lg-flex border-bottom mb-3 pb-3">
              <div class="col-md-5 fw-600">Product</div>
              <div class="col fw-600">Price</div>
              <div class="col fw-600">Qty</div>
              <div class="col fw-600">Total</div>
              <div class="col-auto fw-600">Remove</div>
            </div>
            <ul class="list-group list-group-flush">
              @foreach($cart as $key => $item)
              <li class="list-group-item px-0 px-lg-3 cart-item" data-id="{{ $key }}" data-stock="{{ $item['stock'] ?? 0 }}">
                <div class="row gutters-5 align-items-center cart-summary-item-group">
                  <div class="col-lg-5 d-flex align-items-center cart-summary-item mb-2 mb-lg-0">
                    <span class="mr-2 ml-0 flex-shrink-0">
                      {!! imgTag(
                        storedPath($item['image'], 'assets/images/thumbnails'),
                        $item['name'],
                        'img-fit size-60px',
                        ''
                      ) !!}
                    </span>
                    <span class="fs-14 opacity-60">{{ $item['name'] }}</span>
                  </div>
                  <div class="col-4 col-lg cart-summary-item">
                    <span class="opacity-60 fs-12 d-block d-lg-none">Price</span>
                    <span class="fw-600 fs-16">{{ currency_format($item['price']) }}</span>
                  </div>
                  <div class="col-2 col-lg cart-summary-item" style="display: flex;">
                    <div class="row g-0 align-items-center aiz-plus-minus mr-2 ml-0">
                      <button class="btn col-auto btn-icon btn-sm btn-circle btn-light change-qty steve-btn" type="button" data-action="decrease" data-id="{{ $key }}">
                        <i class="las la-minus"></i>
                      </button>
                      <input type="text" class="col border-0 text-center flex-grow-1 fs-16 qty-input" value="{{ $item['qty'] }}">
                      <button class="btn col-auto btn-icon btn-sm btn-circle btn-light change-qty steve-btn" type="button" data-action="increase" data-id="{{ $key }}">
                        <i class="las la-plus"></i>
                      </button>
                    </div>
                  </div>
                  <div class="col-3 col-lg cart-summary-item">
                    <span class="opacity-60 fs-12 d-block d-lg-none">Total</span>
                    <span class="fw-600 fs-16" id="prc{{ $key }}">{{ currency_format($item['price'] * $item['qty']) }}</span>
                  </div>
                  <div class="col-auto cart-summary-item">
                    <form action="{{ route('cart.remove') }}" method="POST" class="d-inline remove-form">
                      @csrf
                      <input type="hidden" name="product_id" value="{{ $item['id'] }}">
                      <button type="submit" class="btn btn-icon btn-sm btn-soft-primary btn-circle steve-btn">
                        <i class="las la-trash"></i>
                      </button>
                    </form>
                  </div>
                </div>
              </li>
              @endforeach
            </ul>
          </div>

          <div class="px-3 py-2 mb-4 border-top d-flex justify-content-between">
            <span class="opacity-60 fs-15">Subtotal</span>
            <span class="fw-600 fs-17 total-cart-price-sub total-cart-price-val">{{ currency_format($total) }}</span>
          </div>

          @if(!empty($couponData))
          <div class="px-3 mb-3 d-flex justify-content-between align-items-center bg-success-subtle border border-success-subtle rounded p-2">
            <div class="fs-14">
              <i class="las la-tag text-success me-1"></i>
              <span class="fw-600 text-success">{{ $couponData['code'] }}</span>
              <span class="opacity-70"> — saved {{ currency_format($couponDiscount) }}</span>
            </div>
            <form action="{{ route('cart.coupon.remove') }}" method="POST">
              @csrf
              <button type="submit" class="btn btn-sm btn-outline-danger steve-btn"><i class="las la-times"></i> Remove</button>
            </form>
          </div>
          <div class="px-3 py-2 mb-3 border-top d-flex justify-content-between">
            <span class="text-success fw-600 fs-14">Coupon Discount</span>
            <span class="text-success fw-600 fs-14">-{{ currency_format($couponDiscount) }}</span>
          </div>
          <div class="px-3 py-2 mb-4 border-top d-flex justify-content-between">
            <span class="opacity-60 fs-15 fw-700">Total</span>
            <span class="fw-700 fs-17">{{ currency_format($total - $couponDiscount) }}</span>
          </div>
          @endif

          @if(empty($couponData))
          <div class="px-3 mb-4">
            <form action="{{ route('cart.coupon.apply') }}" method="POST" class="d-flex gap-2">
              @csrf
              <input type="text" name="coupon_code" class="form-control form-control-sm rounded-0" placeholder="Enter coupon code" required>
              <button type="submit" class="btn btn-sm btn-outline-primary steve-btn px-3">Apply</button>
            </form>
          </div>
          @endif

          <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
              <a href="{{ route('shop') }}" class="fw-600 a-tag-hover-color">
                <i class="fas fa-arrow-left"></i>
                Return to shop
              </a>
              <a href="{{ route('checkout') }}" class="btn btn-primary steve-btn">
                Continue to checkout
              </a>
          </div>
        </div>
        @else
        <div class="border bg-white p-4">
          <div class="text-center p-3">
            <i class="las la-frown la-3x opacity-60 mb-3"></i>
            <h3 class="h4 fw-700">Your Cart is empty</h3>
            <a href="{{ route('shop') }}" class="btn btn-primary steve-btn">Continue Shopping</a>
          </div>
        </div>
        @endif
      </div>
    </div>
  </div>
</section>

@endsection
@section('style')
<style>
@media (max-width: 991px) {
  .cart-summary-item-group .cart-summary-item {
    margin-bottom: 0.5rem;
  }
  .cart-summary-item-group .cart-summary-item:last-child {
    margin-bottom: 0;
  }
  .cart-summary-item-group .cart-summary-item .qty-input {
    width: 36px;
    font-size: 14px !important;
  }
  .cart-summary-item-group .cart-summary-item .btn-circle {
    width: 36px;
    height: 36px;
  }
  .cart-summary-item-group .cart-summary-item .btn-circle i {
    font-size: 12px;
  }
  .cart-summary-item-group .cart-summary-item .fs-16 {
    font-size: 14px !important;
  }
}
@media (max-width: 575px) {
  .cart-summary-item-group .cart-summary-item {
    padding: 0 4px;
  }
  .cart-summary-item-group .cart-summary-item .opacity-60.fs-12 {
    font-size: 10px !important;
  }
}
</style>
@endsection
