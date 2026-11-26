@extends('layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'auth-register-page', 'pageClass' => 'auth-register-page'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Register - Steve Autoparts',
        'metaTitle' => 'Register | Steve Autoparts',
        'metaDescription' => 'Create your Steve Autoparts account.',
    ])
@endsection
@section('content')

<!-- Banner Hero Section -->
@include('partials.breadcrumb', [
    'title' => 'Sign Up',
    'items' => [
        ['label' => 'Home', 'url' => route('home')],
        ['label' => 'Sign Up'],
    ],
])

<!-- Register Form Section -->
<section class="gs-reg-section">
  <div class="container">
    <div class="row">
      <div class="col-lg-6 mx-auto reg-area">
        <div class="reg-content">
          <h4 class="text-center">Create your account</h4>
          
          @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
          @endif

          <form action="{{ route('register.post') }}" method="POST">
            @csrf
            <div class="form-group">
              <label for="name">Name  <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" id="name" placeholder="Enter your full name" value="{{ old('name') }}" required>

              <label for="email">Email Address <span class="text-danger">*</span></label>
              <input type="email" name="email" class="form-control" id="email" placeholder="Enter your email" value="{{ old('email') }}" required>

              <label for="phone">Phone Number <span class="text-muted small">(Optional)</span></label>
              <input type="tel" name="phone" class="form-control" id="phone" placeholder="Enter your phone number" value="{{ old('phone') }}" autocomplete="tel">
            
              <label for="create-password">Password</label>
              <div class="pass-wrapper" style="position:relative">
                  <input type="password" name="password" class="form-control" id="create-password" placeholder="Enter your password" required>
                  <span style="position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;z-index:5" aria-label="Show password" title="Show password" onclick="togglePassword('create-password',this)">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  </span>
              </div>
              <label for="confirm-password">Confirm Password</label>
              <div class="pass-wrapper" style="position:relative">
                  <input type="password" name="password_confirmation" class="form-control" id="confirm-password" placeholder="Confirm your password" required>
                  <span style="position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;z-index:5" aria-label="Show password" title="Show password" onclick="togglePassword('confirm-password',this)">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  </span>
              </div>
            </div>

            <div class="form-check mt-4">
              <input class="form-check-input @error('terms') is-invalid @enderror" type="checkbox" name="terms" value="1" id="register_terms" {{ old('terms') ? 'checked' : '' }} required>
              <label class="form-check-label" for="register_terms">
                I agree to the <a href="{{ route('privacy.policy') }}" class="a-tag-text-hover" target="_blank">Privacy Policy</a> and <a href="{{ route('terms.conditions') }}" class="a-tag-text-hover" target="_blank">Terms &amp; Conditions</a>
              </label>
              @error('terms')
                <div class="invalid-feedback d-block">{{ $message }}</div>
              @enderror
            </div>

            <div class="form-check mt-2">
              <input class="form-check-input" type="checkbox" name="subscribe" value="1" id="register_subscribe" {{ old('subscribe') ? 'checked' : '' }}>
              <label class="form-check-label" for="register_subscribe">
                Subscribe to our newsletter for offers and updates
              </label>
            </div>

            @error('g-recaptcha-response')
              <div class="text-danger mt-2">{{ $message }}</div>
            @enderror
            @include('partials.recaptcha-field', ['form' => 'register'])

            <br>
            <button type="submit" class="template-btn btn-forms steve-btn button-hover">Register Now</button>
            @include('partials.social-login-buttons')
            <!-- <p class="text-center login-or">Or</p> -->
            <hr></hr>
            <p class="login-redirect">Already have an account? <span><a href="{{ route('login') }}" class="a-tag-text-hover">Sign In</a></span></p>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection

@section('scripts')
    @include('partials.password-toggle-script')
    @if(\App\Services\Recaptcha::enabled('register'))
        @include('partials.recaptcha-scripts', ['form' => 'register'])
    @endif
@endsection
