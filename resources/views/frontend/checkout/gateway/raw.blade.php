@extends('layouts.app')

@section('content')
<section class="py-4">
  <div class="container">
    <div class="border bg-white p-4">
      <h1 class="h5 fw-700 mb-3">{{ $title }}</h1>
      {!! $html !!}
    </div>
  </div>
</section>
@endsection
