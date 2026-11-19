@extends('layouts.app')
@include('partials.page-attributes', ['pageId' => 'checkout-guest-page', 'pageClass' => 'checkout-guest-page'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Checkout - ' . config('app.name', 'StAutoparts'),
        'metaTitle' => 'Checkout | ' . config('app.name', 'StAutoparts'),
        'metaDescription' => 'Complete your order at ' . config('app.name', 'StAutoparts') . '.',
    ])
@endsection
@section('content')

@include('partials.checkout-steps', ['activeStep' => 2])

<section class="mb-4">
  <div class="container">
    <div class="row cols-xs-space cols-sm-space cols-md-space">
      <div class="mx-auto">
        <form class="form-default" action="{{ route('checkout.guest.submit') }}" method="POST">
          @csrf
          <div class="border bg-white p-4 mb-4">

            <h5 class="fw-700 mb-4" style="font-size: 15px;">Enter Your Shipping Details</h5>
            <p class="text-muted mb-3" style="font-size: 13px;">
              You don't need an account to place an order. Your details are only used to deliver and confirm your order.
            </p>

            <div class="row g-3 mb-3">
              <div class="col-md-12">
                <label class="form-label fs-14" for="gc_email">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" id="gc_email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="you@example.com" required>
                @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
              </div>
            </div>

            @include('partials.address-fields', [
                'prefix' => 'gc',
                'value' => old(),
                'withFullName' => true,
                'withPhone' => true,
                'required' => ['full_name', 'phone', 'address', 'country', 'city', 'zip_code'],
            ])

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 mt-4">
              <a href="{{ route('cart') }}" class="fw-600 a-tag-hover-color">
                <i class="fas fa-arrow-left"></i>
                Back to cart
              </a>
              <button type="submit" class="btn btn-primary fs-14 fw-700 px-4 steve-btn">Continue to Delivery Info</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>

@endsection