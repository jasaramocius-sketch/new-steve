{{-- Social login buttons are rendered only when the feature is enabled and a
     real OAuth handler exists. Provider toggles still saved in admin but marked
     "integration required" never surface here. --}}
@php
    $providers = [
        'google_login' => ['label' => 'Continue with Google', 'icon' => 'fab fa-google'],
        'facebook_login' => ['label' => 'Continue with Facebook', 'icon' => 'fab fa-facebook-f'],
        'twitter_login' => ['label' => 'Continue with Twitter', 'icon' => 'fab fa-twitter'],
        'apple_login' => ['label' => 'Continue with Apple', 'icon' => 'fab fa-apple'],
    ];
@endphp

@foreach ($providers as $flag => $provider)
    @if (\App\Support\FeatureFlags::enabled($flag) && ! \App\Support\FeatureFlags::integrationRequired($flag))
        <a href="{{ route('social.login', $flag) }}" class="template-btn btn-forms steve-btn button-hover d-block text-center mb-2">
            <i class="{{ $provider['icon'] }} me-2"></i>{{ $provider['label'] }}
        </a>
    @endif
@endforeach
