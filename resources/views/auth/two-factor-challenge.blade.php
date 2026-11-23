@extends('layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'auth-two-factor-page', 'pageClass' => 'auth-two-factor-page'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Two-Factor Verification - Steve Autoparts',
        'metaTitle' => 'Two-Factor Verification | Steve Autoparts',
        'metaDescription' => 'Verify your two-factor authentication code to sign in.',
    ])
@endsection
@section('content')

<section class="gs-reg-section">
  <div class="container">
    <div class="row">
      <div class="col-lg-6 mx-auto reg-area">
        <div class="reg-content">
          <h4 class="text-center">Two-Factor Verification</h4>
          <p class="text-center text-muted mb-4">Enter the 6-digit code from your authenticator app, or use a recovery code.</p>

          @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
          @endif

          <form action="{{ route('two-factor.verify') }}" method="POST">
            @csrf
            <div class="form-group">
              <label for="two-factor-code">Authentication code</label>
              <input type="text"
                     id="two-factor-code"
                     name="code"
                     class="form-control @error('code') is-invalid @enderror"
                     value="{{ old('code') }}"
                     inputmode="numeric"
                     autocomplete="one-time-code"
                     maxlength="32"
                     required
                     autofocus>
              @error('code')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            <button type="submit" class="template-btn btn-forms steve-btn button-hover mt-3">Verify &amp; Sign In</button>
          </form>

          <p class="text-center mt-3 mb-0"><a href="{{ route('login') }}" class="a-tag-text-hover">Back to sign in</a></p>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection
