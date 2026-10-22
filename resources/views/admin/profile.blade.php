@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-profile-page', 'pageClass' => 'admin-profile-page'])
@section('page-title', 'My Profile')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'My Profile - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')

@if($errors->any())
    <div class="alert alert-danger">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif
<!-- Account Details Panel -->
<div class="acc-info-wrapper rounded p-3 mb-3 admin-profile-info">
    <h4 class="mb-3 admin-profile-heading">Account Details</h4>
    <div class="list-wrapper">
    <div class="row w-100">
        <div class="col-md-3">
            <ul>
            <li>
                @if(Auth::user()->avatar)
                    <img src="{{ storedImageUrl(Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" width="100%" class="border">
                @else
                    <div class="text-dark w-100 h-100 d-flex align-items-center justify-content-center admin-avatar-80 admin-avatar-initial">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                @endif
            </li>
        </ul>
        </div>
        <div class="col-md-3">        
        <ul class="list-unstyled d-flex flex-column gap-2">            
            <li><strong class="text-secondary admin-profile-label">Name:</strong> <span class="user-name text-transform-capitalize admin-profile-value">{{ $user['name'] }}</span></li>
            <li><strong class="text-secondary admin-profile-label">Email Address:</strong> <span class="user-email text-transform-capitalize admin-profile-value">{{ $user['email'] }}</span></li>
            <li><strong class="text-secondary admin-profile-label">Phone:</strong> <span class="user-phone text-transform-capitalize admin-profile-value">{{ $user['phone'] }}</span></li>
        </ul>
        </div>
        <div class="col-md-6">
        <ul class="list-unstyled d-flex flex-column gap-2">
            <li><strong class="text-secondary admin-profile-label">Address:</strong> <span class="user-address text-transform-capitalize admin-profile-value">{!! $user['address'] !!}</span></li>
            <li><strong class="text-secondary admin-profile-label">City:</strong> <span class="user-city text-transform-capitalize admin-profile-value">{{ $user['city'] }}</span></li>
            <li><strong class="text-secondary admin-profile-label">State:</strong> <span class="user-state text-transform-capitalize admin-profile-value">{{ $user['state'] }}</span></li>
            <li><strong class="text-secondary admin-profile-label">Country:</strong> <span class="user-country text-transform-capitalize admin-profile-value">{{ $user['country'] }}</span></li>
            <li><strong class="text-secondary admin-profile-label">Zip:</strong> <span class="user-postal text-transform-capitalize admin-profile-value">{{ $user['postal_code'] }}</span></li>
        </ul>
        </div>
    </div>
    </div>
</div>
<div class="d-flex flex-column flex-lg-row gap-3">
    <div class="flex-fill d-flex">
        <div class="card card-box shadow-sm w-100">
            <div class="card-header bg-white">
                <h5 class="mb-0">Profile Settings</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.profile.update') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Profile Photo</label>
                        <input type="hidden" name="image_from_manager" id="image_from_manager_avatar">
                        <div class="d-flex align-items-center gap-3">
                            <div id="impPreview_avatar" class="rounded-circle overflow-hidden admin-avatar-80">
                                @if(Auth::user()->avatar)
                                    <img src="{{ storedImageUrl(Auth::user()->avatar) }}" alt="Avatar">
                                @else
                                    <span class="admin-avatar-initial">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                                @endif
                            </div>
                            <div>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="impOpen_avatar()">
                                    <i class="fas fa-images me-1"></i>Browse Image Manager
                                </button>
                                <div><small class="text-muted">Select a profile photo from Image Manager</small></div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3 d-flex flex-column flex-md-row gap-3">
                        <div class="w-100">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control text-transform-capitalize" value="{{ $user->name }}" required>
                        </div>
                        <div class="w-100">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control text-transform-capitalize" value="{{ $user->email }}" readonly>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" class="form-control text-transform-capitalize" inputmode="numeric" value="{{ $user->phone }}">
                    </div>

                    @include('partials.address-fields', [
                        'prefix' => 'pro',
                        'value' => $user,
                        'withFullName' => false,
                        'withPhone' => false,
                        'withDefault' => false,
                        'required' => [],
                        'zipName' => 'postal_code',
                    ])

                    <button type="submit" class="btn btn-primary text-transform-capitalize steve-btn mt-3">Update Profile</button>
                </form>
            </div>
        </div>
    </div>

    <div class="flex-fill d-flex">
        <div class="card card-box shadow-sm w-100">
            <div class="card-header bg-white">
                <h5 class="mb-0">Change Password</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.profile.password') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="new_password_confirmation" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-warning steve-btn">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@include('admin.partials.image-manager-picker', ['pickerId' => 'avatar', 'targetInput' => 'image_from_manager'])