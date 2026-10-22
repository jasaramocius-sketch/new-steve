@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-staff-edit-page', 'pageClass' => 'admin-staff-edit-page'])
@section('page-title', 'Edit Staff')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Edit Staff - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">Edit Staff: {{ $user->name }}</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.staff.update', $user->id) }}" method="POST">
            @csrf @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email Address <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required readonly>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Password (leave blank to keep current)</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="tel" name="phone" class="form-control" inputmode="numeric" value="{{ old('phone', $user->phone) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Role <span class="text-danger">*</span></label>
                    <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                        <option value="staff" {{ ($user->role == 'staff') ? 'selected' : '' }}>Staff</option>
                        @if(Auth::user()->role === 'master_admin')
                            <option value="admin" {{ ($user->role == 'admin') ? 'selected' : '' }}>Admin</option>
                            <option value="master_admin" {{ ($user->role == 'master_admin') ? 'selected' : '' }}>Master Admin</option>
                        @endif
                    </select>
                    @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mt-4">
                <button class="btn btn-primary steve-btn"><i class="fas fa-save"></i> Update</button>
                <a href="{{ route('admin.staff.index') }}" class="btn btn-primary">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection
