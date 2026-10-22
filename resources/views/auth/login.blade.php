@extends('layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'auth-login-page', 'pageClass' => 'auth-login-page'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Login - StAutoparts',
        'metaTitle' => 'Login | StAutoparts',
        'metaDescription' => 'Sign in to your StAutoparts account.',
    ])
@endsection
@section('content')

<!-- Banner Hero Section -->
<!-- <section class="gs-breadcrumb-section" style="background-image: url('{{ asset('assets/images/1724480495Imagexxxxxpng.png') }}'); background-size: cover; background-position: center;">
  <div class="container">
    <div class="content-wrapper">
      <h2 class="breadcrumb-title">Customer Login</h2>
      <ul class="bread-menu">
        <li><a href="{{ route('home') }}">Home</a></li>
        <li style="color: #fff;">Customer Login</li>
      </ul>
    </div>
  </div>
</section> -->

<!-- Login Form Section -->
<section class="gs-reg-section">
  <div class="container">
    <div class="row">
      <div class="col-lg-6 mx-auto reg-area">
        <div class="reg-content">
          <h4 class="text-center">Welcome Back</h4>
          
          @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
          @endif

          <form action="{{ route('login.submit') }}" method="POST">
            @csrf
            
            @if(request()->query('redirect'))
              <input type="hidden" name="redirect" value="{{ request()->query('redirect') }}">
            @endif

            <div class="form-group">
              <label for="email">Email Address</label>
              <input type="email" name="email" class="form-control" value="" id="email" placeholder="Enter your email" required>
              
              <label for="create-password">Password</label>
              <div class="pass-wrapper" style="position:relative">
                <input type="password" name="password" class="form-control" value="" id="create-password" placeholder="Enter your password" required>
                <span style="position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;z-index:5" onclick="togglePassword('create-password',this)">
                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </span>
              </div>
            </div>
            
            <div class="row mb-1 mt-2">
              <div class="col d-flex">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" name="remember" value="1" id="remember_me">
                  <label class="form-check-label" for="remember_me">Remember Me</label>
                </div>
              </div>
              <div class="col d-flex justify-content-end login-forgot">
                <a href="{{ route('password.request') }}" class="a-tag-text-hover">Forgot Password?</a>
              </div>
            </div>
            
            <button type="submit" class="template-btn btn-forms steve-btn button-hover mt-3">Sign In</button>
            <!-- <p class="text-center login-or">Or</p> -->
            <hr></hr>
            <p class="login-redirect">Don't have an account? <span><a href="{{ route('register') }}" class="a-tag-text-hover">Register Now</a></span></p>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
function togglePassword(id, btn) {
  var inp = document.getElementById(id);
  if (inp.type === 'password') { inp.type = 'text'; btn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>'; }
  else { inp.type = 'password'; btn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>'; }
}
</script>
@endsection
