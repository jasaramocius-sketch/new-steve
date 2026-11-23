@extends('layouts.app')

@section('content')
<section class="py-5">
  <div class="container">
    <div class="mx-auto" style="max-width: 620px;">
      <div class="border bg-white p-4">
        <div class="alert alert-info mb-4">
          <i class="las la-info-circle fs-20 mr-2"></i>
          Your order has been created. Complete the payment with {{ $gateway }} using order reference
          <strong>{{ $order->order_number }}</strong>, then contact support once the payment is made.
        </div>
        <p class="fs-14 text-muted mb-4">
          The order will remain unpaid until the payment is verified from the {{ $gateway }} merchant dashboard.
          No payment status is changed automatically.
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
