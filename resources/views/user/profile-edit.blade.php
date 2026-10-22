@extends('user.layouts.dashboard')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'user-profile-edit-page', 'pageClass' => 'user-profile-edit-page'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Edit Profile - StAutoparts',
        'metaTitle' => 'Edit Profile | StAutoparts',
        'metaDescription' => 'Update your profile information at StAutoparts.',
    ])
@endsection
@section('dashboard-content')

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h4 class="h4-style mb-1">Edit Profile</h4>
        <p class="text-muted mb-0">Update your name, phone, address and profile photo. Email address is not editable.</p>
    </div>
</div>

<div class="row gy-4">
    <div class="col-lg-4">
        <div class="card mb-4 text-center">
            <div class="card-body">
                {!! imgTag(storedPath($profile->avatar), $profile->name, 'rounded-circle mb-3', 'onerror="this.onerror=null;this.src=\''.asset('assets/images/avatar-place.png').'\'' . '"' . ' style="width:120px;height:120px;object-fit:cover;"') !!}
                <h5 class="mb-1">{{ $profile->name }}</h5>
                <p class="text-muted mb-0">{{ $profile->email }}</p>
            </div>
        </div>
    </div>

    <div class="col-lg-8 profile-edit-form-wrapper">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Edit Profile</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fs-14" for="pe_email">Email Address</label>
                            <input type="email" name="email" id="pe_email" class="form-control bg-light" value="{{ $profile->email }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fs-14" for="pe_avatar">Profile Photo</label>
                            <input type="file" name="avatar" id="pe_avatar" class="form-control" accept="image/*">
                            @error('avatar')
                                <div class="text-danger fs-14 mt-1">{{ $message }}</div>
                            @enderror
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
                        'withDefault' => true,
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
                </form>
            </div>
        </div>
    </div>
</div>

@endsection