@extends('layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'auth-forgot-page', 'pageClass' => 'auth-forgot-page'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Forgot Password - StAutoparts',
        'metaTitle' => 'Forgot Password | StAutoparts',
        'metaDescription' => 'Request a password reset link for your StAutoparts account.',
    ])
@endsection
@section('content')

<!-- Forgot Password Form Section -->
<section class="gs-reg-section">
  <div class="container">
    <div class="row">
      <div class="col-lg-6 mx-auto reg-area">
        <div class="reg-content">
          <h4 class="text-center">Forgot Password</h4>
          <p class="text-center text-muted">Enter your email address and we'll send you a link to reset your password.</p>

          @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
            @if(session('reset_link'))
              <div class="alert alert-info">
                <strong>Local reset link:</strong><br>
                <a href="{{ session('reset_link') }}">{{ session('reset_link') }}</a>
              </div>
            @endif
          @endif

          @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
          @endif

          <form action="{{ route('password.email') }}" method="POST">
            @csrf
            <div class="form-group">
              <label for="email">Email Address</label>
              <input type="email" name="email" class="form-control" id="email" placeholder="Enter your email" value="{{ old('email') }}" required>
            </div>
            <button type="submit" class="template-btn btn-forms steve-btn button-hover mt-3">Send Password Reset Link</button>
            <hr></hr>
            <p class="login-redirect">Remembered your password? <span><a href="{{ route('login') }}" class="a-tag-text-hover">Back to Sign In</a></span></p>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection