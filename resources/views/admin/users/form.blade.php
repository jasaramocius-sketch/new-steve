@extends('admin.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-users-form-page', 'pageClass' => 'admin-users-form-page'])
@section('page-title', isset($user) ? 'Edit User' : 'Add User')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'User Form - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')

<div class="card border-0 shadow-sm">
    <!-- <div class="card-header bg-white">
        <h5 class="mb-0">{{ isset($user) ? 'Edit User: ' . $user->name : 'Add User' }}</h5>
    </div> -->
    <div class="card-body">
        <form action="{{ isset($user) ? route('admin.users.update', $user->id) : route('admin.users.store') }}" method="POST">
            @csrf
            @if(isset($user)) @method('PUT') @endif

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name ?? '') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email Address <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email ?? '') }}" required {{ isset($user) ? 'readonly' : '' }}>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Role <span class="text-danger">*</span></label>
                    @if(isset($user) && $user->role === 'master_admin')
                        <input type="hidden" name="role" value="master_admin">
                        <input type="text" class="form-control" value="Admin" disabled>
                        <div class="form-text text-warning"><i class="fas fa-lock"></i> Master admin role cannot be changed.</div>
                    @else
                    <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                        <option value="">None</option>
                        <option value="master_admin" {{ old('role', $user->role ?? '') === 'master_admin' ? 'selected' : '' }}>Admin</option>
                        <option value="staff" {{ old('role', $user->role ?? '') === 'staff' ? 'selected' : '' }}>Staff</option>
                        <option value="customer" {{ old('role', $user->role ?? '') === 'customer' ? 'selected' : '' }}>Customer</option>
                    </select>
                    @endif
                    @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    @if(isset($user) && $user->role === 'master_admin')
                        <input type="hidden" name="status" value="active">
                        <input type="text" class="form-control" value="Active" disabled>
                        <div class="form-text text-warning"><i class="fas fa-lock"></i> Master admin status is always active.</div>
                    @else
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="active" {{ old('status', $user->status ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $user->status ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @endif
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="tel" name="phone" class="form-control" inputmode="numeric" value="{{ old('phone', $user->phone ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password @if(!isset($user))<span class="text-danger">*</span>@endif</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="{{ isset($user) ? 'Leave blank to keep current' : 'Min 6 characters' }}" {{ !isset($user) ? 'required' : '' }}>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    @include('partials.address-fields', [
                        'prefix' => 'user_form',
                        'value' => $user ?? null,
                        'withPhone' => false,
                        'withFullName' => false,
                        'withDefault' => false,
                        'zipName' => 'postal_code',
                        'required' => [],
                    ])
                </div>
            </div>

            <div class="mt-4 d-flex gap-1">
                <button class="btn btn-primary steve-btn gap-1"><i class="fas fa-save"></i> {{  isset($user) ? 'Update' : 'Save' }}</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-primary gap-1">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection
