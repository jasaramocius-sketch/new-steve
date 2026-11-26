@extends('frontend.user.layouts.dashboard')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'user-profile-edit-page', 'pageClass' => 'user-profile-edit-page'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Edit Profile - Steve Autoparts',
        'metaTitle' => 'Edit Profile | Steve Autoparts',
        'metaDescription' => 'Update your profile information at Steve Autoparts.',
    ])
@endsection
@section('dashboard-content')

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h4 class="h4-style mb-1">Edit Profile</h4>
        <p class="text-muted mb-0">Update your name, phone, address and profile photo. Email address is not editable.</p>
    </div>
</div>

<form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data" id="profileForm">
    @csrf

    <div class="row gy-4">
        <div class="col-lg-4">
            <div class="card mb-4 text-center">
                <div class="card-body">
                    <div id="profileAvatarPreview"
                         class="mx-auto mb-3 rounded-circle overflow-hidden d-flex flex-column align-items-center justify-content-center"
                         style="width:120px;height:120px;background:#f5f6f8;">
                        @if($profile->avatar)
                            <img src="{{ storedImageUrl($profile->avatar) }}"
                                 alt="{{ $profile->name }}"
                                 style="width:100%;height:77%;object-fit:cover;"
                                 onerror="this.onerror=null;this.src='{{ asset('assets/images/avatar-place.png') }}'">
                        @else
                            <span style="font-size:2.5rem;color:#adb5bd;line-height:1;">{{ strtoupper(substr($profile->name, 0, 1)) }}</span>
                        @endif
                        <label for="avatar" class="btn btn-outline-primary d-flex align-items-center justify-content-center border-radius-0 mb-0" style="width:100%;height:100%;">
                            <i class="fas fa-camera me-1"></i>
                        </label>
                        <input type="file"
                            name="avatar"
                            id="avatar"
                            class="d-none @error('avatar') is-invalid @enderror"
                            accept="image/jpeg,image/png,image/webp,image/gif">
                        @error('avatar')
                            <div class="text-danger fs-14 mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <h5 class="mb-1">{{ $profile->name }}</h5>
                    <p class="text-muted mb-0">{{ $profile->email }}</p>

                    
                    <div class="form-text mt-1">JPG, PNG, WebP or GIF · Max 2 MB</div>
                </div>
            </div>
        </div>

        <div class="col-lg-8 profile-edit-form-wrapper">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Edit Profile</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col">
                            <label class="form-label fs-14" for="pe_email">Email Address</label>
                            <input type="email" name="email" id="pe_email" class="form-control bg-light" value="{{ $profile->email }}" readonly>
                        </div>
                    </div>

                    @php
                        $profileAddressValue = (object) [
                            'full_name' => $profile->name,
                            'name' => $profile->name,
                            'email' => $profile->email,
                            'phone' => $profile->phone,
                            'address' => $profile->address,
                            'city' => $profile->city,
                            'state' => $profile->state,
                            'country' => $profile->country,
                            'postal_code' => $profile->postal_code,
                        ];
                    @endphp
                    @include('partials.address-fields', [
                        'prefix' => 'profile_form',
                        'value' => $profileAddressValue,
                        'zipName' => 'postal_code',
                        'withFullName' => true,
                        'withPhone' => true,
                        'withDefault' => false,
                        'required' => ['full_name'],
                    ])

                    <div class="row g-3 mt-1">
                        <div class="col-12">
                            <h6 class="mb-0" style="font-weight:600;">Change Password (optional)</h6>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-14" for="pe_current_password">Current Password</label>
                            <input type="password" name="current_password" id="pe_current_password" class="form-control" autocomplete="current-password">
                            @error('current_password')
                                <div class="text-danger fs-14 mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-14" for="pe_new_password">New Password</label>
                            <input type="password" name="new_password" id="pe_new_password" class="form-control" autocomplete="new-password">
                            @error('new_password')
                                <div class="text-danger fs-14 mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-14" for="pe_new_password_confirmation">Confirm New Password</label>
                            <input type="password" name="new_password_confirmation" id="pe_new_password_confirmation" class="form-control" autocomplete="new-password">
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary fs-14 fw-600 steve-btn">Save Changes</button>
                        <a href="{{ route('user.profile') }}" class="btn btn-primary fs-14 fw-600 steve-btn">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
(function() {
    var input = document.getElementById('avatar');
    var preview = document.getElementById('profileAvatarPreview');
    if (!input || !preview) return;

    input.addEventListener('change', function() {
        var file = this.files && this.files[0];
        if (!file || !/^image\//.test(file.type)) return;

        var url = URL.createObjectURL(file);
        var img = preview.querySelector('img');

        if (img) {
            img.src = url;
        } else {
            preview.innerHTML = '<img src="' + url + '" alt="Avatar" style="width:100%;height:100%;object-fit:cover;">';
        }

        img = preview.querySelector('img');
        if (img) {
            img.addEventListener('load', function() { URL.revokeObjectURL(url); }, { once: true });
        }
    });
})();
</script>

@endsection
