@extends('layouts.app')

@section('content')
<section class="py-5">
  <div class="container">
    <div class="mx-auto" style="max-width: 520px;">
      <div class="border bg-white p-4 text-center">
        <h1 class="h5 fw-700 mb-3">{{ $title }}</h1>
        <p class="fs-14 text-muted mb-4">Do not close this window. You will be redirected to the payment provider.</p>
        <form id="gateway-form" method="POST" action="{{ $action }}">
          @foreach($fields as $name => $value)
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
          @endforeach
          <button type="submit" class="btn btn-primary fw-700">Continue to payment</button>
        </form>
      </div>
    </div>
  </div>
</section>

<script>
  document.getElementById('gateway-form').submit();
</script>
@endsection
