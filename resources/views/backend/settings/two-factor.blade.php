@extends('backend.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-two-factor-page', 'pageClass' => 'admin-two-factor-page'])
@section('page-title', 'Two-Factor Authentication')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Two-Factor Authentication',
    ])
@endsection
@section('content')

<div class="container-fluid px-15px px-lg-25px">
    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Two-Factor Authentication (TOTP)</h5>
        </div>
        <div class="card-body">
            <p class="text-muted">Use an authenticator app such as Google Authenticator, Microsoft Authenticator or Authy. Codes refresh every 30 seconds and work offline.</p>

            @if ($user->hasTwoFactorEnabled())
                <div class="alert alert-success">
                    <strong>Enabled</strong> since {{ $user->two_factor_confirmed_at ? $user->two_factor_confirmed_at->format('Y-m-d H:i') : '—' }}.
                </div>
            @else
                <div class="alert alert-warning">
                    <strong>Not enabled yet.</strong> Confirm a code below to protect this account.
                </div>
            @endif

            <h6 class="mt-4">1. Add this account to your authenticator app</h6>
            <p class="small text-muted mb-2">Manual setup key:</p>
            <code class="d-block p-2 bg-light border rounded text-break">{{ $user->two_factor_secret }}</code>
            <p class="small text-muted mt-3 mb-2">OTPauth URI (optional, for apps that support importing):</p>
            <code class="d-block p-2 bg-light border rounded text-break">{{ $otpauthUri }}</code>

            <h6 class="mt-4">2. Confirm with a 6-digit code</h6>
            <form action="{{ route('admin.settings.two-factor.confirm') }}" method="POST" class="row g-2 align-items-end">
                @csrf
                <div class="col-auto">
                    <label for="two-factor-confirm-code" class="form-label mb-1">Authenticator code</label>
                    <input type="text"
                           id="two-factor-confirm-code"
                           name="code"
                           class="form-control @error('code') is-invalid @enderror"
                           inputmode="numeric"
                           autocomplete="one-time-code"
                           maxlength="6"
                           placeholder="000000"
                           required>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">{{ $user->hasTwoFactorEnabled() ? 'Regenerate recovery codes' : 'Enable 2FA' }}</button>
                </div>
            </form>

            @if (count($recoveryCodes))
                <div class="alert alert-info mt-4">
                    <h6 class="mb-1">Save your recovery codes</h6>
                    <p class="small mb-2">Each code works once. Store them somewhere safe — they are shown only now.</p>
                    <ul class="mb-0">
                        @foreach ($recoveryCodes as $recoveryCode)
                            <li><code>{{ $recoveryCode }}</code></li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($user->hasTwoFactorEnabled())
                <hr class="mt-4">
                <form action="{{ route('admin.settings.two-factor.disable') }}" method="POST" onsubmit="return confirm('Disable two-factor authentication for this account?')">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger">Disable 2FA</button>
                </form>
            @endif
        </div>
    </div>
</div>

@endsection
