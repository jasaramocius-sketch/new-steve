@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-sellers-edit-page', 'pageClass' => 'admin-sellers-edit-page'])
@section('page-title', 'Edit Seller')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Edit Seller - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">Edit Seller</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.sellers.update', $seller->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $seller->name) }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Location</label>
                    <input type="text" name="location" class="form-control @error('location') is-invalid @enderror" value="{{ old('location', $seller->location) }}" placeholder="City, Country">
                    @error('location') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Image</label>
                    @if($seller->image)
                        <div class="mb-2">
                            <img src="{{ storedImageUrl($seller->image, 'assets/images') }}" width="60" height="60" class="admin-image-thumb-bordered">
                        </div>
                    @endif
                    <input type="hidden" name="image_from_manager" id="image_from_manager_seller_image">
                    <div id="impPreview_seller_image" class="d-none mt-2"></div>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="impOpen_seller_image()">
                        <i class="fas fa-images me-1"></i> Browse Image Manager
                    </button>
                </div>

                <div class="col-md-6">
                    <label class="form-label">&nbsp;</label>
                    <div class="form-check mt-2">
                        <input type="checkbox" name="status" value="1" class="form-check-input" id="status" {{ $seller->status ? 'checked' : '' }}>
                        <label class="form-check-label" for="status">Active</label>
                    </div>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control texteditor @error('description') is-invalid @enderror" rows="4">{{ old('description', $seller->description) }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mt-4 d-flex gap-1">
                <button class="btn btn-primary steve-btn gap-1"><i class="fas fa-save"></i> Update</button>
                <a href="{{ route('admin.sellers.index') }}" class="btn btn-primary gap-1">Cancel</a>
            </div>
        </form>
    </div>
</div>

@include('admin.partials.image-manager-picker', ['pickerId' => 'seller_image', 'targetInput' => 'image_from_manager'])

@endsection
