@extends('layouts.app')

@section('content')
<section class="py-5">
  <div class="container">
    <div class="mx-auto" style="max-width: 520px;">
      <div class="border bg-white p-4 text-center">
        <h1 class="h5 fw-700 mb-3">Complete payment with Razorpay</h1>
        <p class="fs-14 text-muted mb-1">Order {{ $order->order_number }}</p>
        <p class="fs-18 fw-700 text-primary mb-4">{{ currency_format($order->total_amount) }}</p>
        <button type="button" id="razorpay-pay" class="btn btn-primary fw-700">Pay now</button>
      </div>
    </div>
  </div>
</section>

<form id="razorpay-verify" method="POST" action="{{ $verifyUrl }}">
  @csrf
  <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
  <input type="hidden" name="razorpay_order_id" value="{{ $razorpayOrderId }}">
  <input type="hidden" name="razorpay_signature" id="razorpay_signature">
</form>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
  var razorpayOptions = {
    key: @json($key),
    amount: {{ (int) $amount }},
    currency: @json($currency),
    name: @json(config('app.name', 'Store')),
    description: 'Order {{ $order->order_number }}',
    order_id: @json($razorpayOrderId),
    prefill: {
      name: @json($customer['name']),
      email: @json($customer['email']),
      contact: @json($customer['phone'])
    },
    theme: { color: '#0d6efd' },
    handler: function (response) {
      document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
      document.getElementById('razorpay_signature').value = response.razorpay_signature;
      document.getElementById('razorpay-verify').submit();
    },
    modal: {
      ondismiss: function () {
        window.location.href = @json($cancelUrl);
      }
    }
  };

  document.getElementById('razorpay-pay').addEventListener('click', function () {
    new Razorpay(razorpayOptions).open();
  });
</script>
@endsection
