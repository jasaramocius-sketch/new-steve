@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-edit-brand-page', 'pageClass' => 'admin-edit-brand-page'])
@section('page-title', 'Edit Brand')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Edit Brand - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">Edit Brand</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.brands.update', $brand->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $brand->name) }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Website</label>
                    <input type="url" name="website" class="form-control @error('website') is-invalid @enderror" value="{{ old('website', $brand->website) }}" placeholder="https://example.com">
                    @error('website') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label class="form-label">Image</label>
                    @if($brand->image)
                        <div class="mt-2">
                            <img src="{{ storedImageUrl($brand->image, 'assets/images/brands') }}" width="80" class="admin-image-thumb" onerror="this.onerror=null;this.src='{{ asset('assets/images/placeholder.png') }}'">
                            <small class="text-muted ms-2">Current Image</small>
                        </div>
                    @endif
                    <input type="hidden" name="image_from_manager" id="image_from_manager_brand_image">
                    <div id="impPreview_brand_image" class="d-none mt-2"></div>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-10" onclick="impOpen_brand_image()">
                        <i class="fas fa-images me-1"></i> Browse Image Manager
                    </button>
                </div>

                <div class="col-md-6">
                    <label class="form-label">&nbsp;</label>
                    <div class="form-check mt-2">
                        <input type="checkbox" name="status" value="1" class="form-check-input" id="status" {{ $brand->status ? 'checked' : '' }}>
                        <label class="form-check-label" for="status">Active</label>
                    </div>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control texteditor @error('description') is-invalid @enderror" rows="4">{{ old('description', $brand->description) }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mt-4 d-flex gap-1">
                <button class="btn btn-primary steve-btn gap-1"><i class="fas fa-save"></i> Update</button>
                <a href="{{ route('admin.brands.index') }}" class="btn btn-primary steve-btn align-items-center gap-1">Cancel</a>
            </div>
        </form>
    </div>
</div>

@include('admin.partials.image-manager-picker', ['pickerId' => 'brand_image', 'targetInput' => 'image_from_manager'])

@endsection
