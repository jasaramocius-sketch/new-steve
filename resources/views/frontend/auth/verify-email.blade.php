@extends('layouts.app')
@include('partials.page-attributes', ['pageId' => 'auth-verify-email-page', 'pageClass' => 'auth-verify-email-page'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Confirm Your Email - ' . config('app.name', 'Steve Autoparts'),
        'metaTitle' => 'Confirm Your Email | ' . config('app.name', 'Steve Autoparts'),
        'metaDescription' => 'Confirm your email address to activate your account.',
    ])
@endsection
@section('content')

<section class="gs-reg-section">
  <div class="container">
    <div class="row">
      <div class="col-lg-6 mx-auto reg-area">
        <div class="reg-content">
          <h4 class="text-center">Confirm Your Email</h4>
          <p class="text-center text-muted">New accounts must confirm their email address before signing in. If you have not received a link, enter your email below to request another.</p>

          @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
          @endif

          <form action="{{ route('verification.send') }}" method="POST">
            @csrf
            <div class="form-group">
              <label for="verification-email">Email Address <span class="text-danger">*</span></label>
              <input type="email" name="email" class="form-control" id="verification-email" placeholder="Enter your email" value="{{ old('email', $email ?? '') }}" required>
            </div>
            <button type="submit" class="template-btn btn-forms steve-btn button-hover mt-3">Resend Confirmation Email</button>
            <hr>
            <p class="login-redirect">Already confirmed? <span><a href="{{ route('login') }}" class="a-tag-text-hover">Sign In</a></span></p>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection
