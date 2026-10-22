@extends('user.layouts.dashboard')
@include('partials.page-attributes', ['pageId' => 'user-change-password-page', 'pageClass' => 'user-change-password-page'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Change Password - StAutoparts',
        'metaTitle' => 'Change Password | StAutoparts',
        'metaDescription' => 'Securely change your account password at StAutoparts.',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('dashboard-content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1">Change Password</h4>
        <p class="text-muted mb-0" style="font-size:14px;">Update the password you use to sign in to your account.</p>
    </div>
    <a href="{{ route('user.profile') }}" class="btn btn-outline-secondary steve-btn"><i class="fas fa-arrow-left me-1"></i> Back to Profile</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('user.change.password.update') }}" method="POST" style="max-width: 520px;">
            @csrf

            <div class="mb-3">
                <label class="form-label fs-14" for="cp_current_password">Current Password <span class="text-danger">*</span></label>
                <input type="password" name="current_password" id="cp_current_password" class="form-control @error('current_password') is-invalid @enderror" autocomplete="current-password" required>
                @error('current_password')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fs-14" for="cp_new_password">New Password <span class="text-danger">*</span></label>
                <input type="password" name="new_password" id="cp_new_password" class="form-control @error('new_password') is-invalid @enderror" autocomplete="new-password" minlength="8" required>
                @error('new_password')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fs-14" for="cp_new_password_confirmation">Confirm New Password <span class="text-danger">*</span></label>
                <input type="password" name="new_password_confirmation" id="cp_new_password_confirmation" class="form-control @error('new_password_confirmation') is-invalid @enderror" autocomplete="new-password" minlength="8" required>
                @error('new_password_confirmation')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary steve-btn"><i class="fas fa-key me-1"></i> Update Password</button>
        </form>
    </div>
</div>

@endsection