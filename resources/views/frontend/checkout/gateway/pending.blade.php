@extends('layouts.app')

@section('content')
<section class="py-5">
  <div class="container">
    <div class="mx-auto" style="max-width: 620px;">
      <div class="border bg-white p-4">
        <div class="alert alert-info mb-4">
          <i class="las la-info-circle fs-20 mr-2"></i>
          Your order has been created. Complete the payment with {{ $gateway }} using order reference
          <strong>{{ $order->order_number }}</strong>.
        </div>
        <p class="fs-14 text-muted mb-4">
          The payment is checked directly with {{ $gateway }} and the order is confirmed automatically once the
          provider settles it. If you are not redirected back, contact support with the order reference above.
        </p>
        <div class="d-flex justify-content-between">
          <a href="{{ route('home') }}" class="btn btn-link px-0">Return to shop</a>
          <a href="{{ route('checkout.confirmed') }}" class="btn btn-primary fw-700">View order confirmation</a>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection
