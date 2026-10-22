@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-edit-category-page', 'pageClass' => 'admin-edit-category-page'])
@section('page-title', 'Edit Category')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Edit Category - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')

<div class="category-edit-page">
<div class="card shadow">
    <div class="card-header">
        <h3>Edit Category</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.categories.update',$category->id) }}"
              method="POST" enctype="multipart/form-data">

            @csrf
            @method('PUT')
            <div class="category-edit-page-section">
                <div class="category-edit-page-section-col">
                    <div class="mb-3">
                        <label>Name</label>
                        <input type="text"
                            class="form-control @error('name') is-invalid @enderror"
                            name="name"
                            value="{{ old('name', $category->name) }}"
                            required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                    </div>

                    <div class="mb-3">
                        <label>Update Image</label>
                        <input type="hidden" name="image_from_manager" id="image_from_manager_category_image">
                        <div id="impPreview_category_image" class="d-none mt-2"></div>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="impOpen_category_image()">
                            <i class="fas fa-images me-1"></i> Browse Image Manager
                        </button>
                    </div>
                    <div class="mb-3">
                        <label>Or Image Download URL</label>
                        <input type="url"
                            name="image_url"
                            class="form-control @error('image_url') is-invalid @enderror"
                            value="{{ old('image_url') }}"
                            placeholder="https://example.com/image.png">
                        @error('image_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Provide a direct URL to a new image. The server will download and store it locally.</small>
                    </div>
                    <div class="mb-3">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="1" {{ old('status', $category->status) == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status', $category->status) == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Parent Category</label>
                        <select class="form-control" name="parent_id">
                            <option value="">
                                Main Category
                            </option>
                            @include('admin.categories._parent_options', ['categories' => $parents, 'depth' => 0, 'selected' => $category->parent_id, 'excluded' => $excluded])
                        </select>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary steve-btn">
                            Update
                        </button>
                        <a href="{{ route('admin.categories.index') }}"
                        class="btn btn-primary steve-btn">
                            Back
                        </a>
                    </div>
                </div>
                <div class="category-edit-page-section-col">
                @if($category->image)
                    <div class="mb-3 gap-1 d-flex flex-direction-col">
                        <label>Current Image</label>
                        <div>
                            <img src="{{ storedImageUrl($category->image, 'assets/images/categories') }}" width="50%" onerror="this.onerror=null;this.src='{{ asset('assets/images/placeholder.png') }}'">
                        </div>
                    </div>
                @endif
                </div>
            </div>
        </form>
    </div>
</div>
</div>

@include('admin.partials.image-manager-picker', ['pickerId' => 'category_image', 'targetInput' => 'image_from_manager'])

@endsection
