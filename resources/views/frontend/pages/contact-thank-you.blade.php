@extends('layouts.app')
@section('title', 'Thank You')
@section('content')
<section class="contact-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <div class="card border-0 shadow-sm">
                    <div class="card-body py-5 px-4">
                        <div class="mb-4">
                            <i class="fas fa-check-circle text-success" style="font-size: 80px;"></i>
                        </div>
                        <h2 class="mb-3">Thank You!</h2>
                        <p class="mb-4 fs-5">Your message has been sent successfully.</p>
                        <p class="mb-4 text-muted">We appreciate you contacting us. We will review your inquiry and get back to you as soon as possible.</p>
                        <div class="d-flex flex-wrap justify-content-center gap-2">
                            <a href="{{ url('/') }}" class="btn btn-primary"><i class="fas fa-home me-2"></i>Back to Home</a>
                            <a href="{{ route('contact') }}" class="btn btn-outline-primary"><i class="fas fa-envelope me-2"></i>Send Another Message</a>
                            <a href="{{ route('shop') }}" class="btn btn-outline-secondary"><i class="fas fa-shopping-bag me-2"></i>Continue Shopping</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
