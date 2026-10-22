@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-add-category-page', 'pageClass' => 'admin-add-category-page'])
@section('page-title', 'Create Cetegory')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Create Category - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')

<div class="container-fluid category-create-page">

<div class="card shadow">


    <div class="card-body">

        <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">

            @csrf

            <div class="mb-3">
                <label>Name</label>

                <input type="text"
                       name="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name') }}"
                       required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-rows">
                <div class="mb-3 form-rows-col">
                    <label>Image</label>
                    <input type="hidden" name="image_from_manager" id="image_from_manager_category_image">
                    <div id="impPreview_category_image" class="d-none mt-2"></div>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="impOpen_category_image()">
                        <i class="fas fa-images me-1"></i> Browse Image Manager
                    </button>
                </div>

            <div class="mb-3 form-rows-col">
                <label>Or Image Download URL</label>
                <input type="url"
                       name="image_url"
                       class="form-control @error('image_url') is-invalid @enderror"
                       value="{{ old('image_url') }}"
                       placeholder="https://example.com/image.png">
                @error('image_url')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">Provide a direct URL to an image. The server will download and store it locally.</small>
            </div>
                </div>
            <div class="form-rows">
            <div class="mb-3 form-rows-col">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('status', '1') == '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="mb-3 form-rows-col">

                <label>Parent Category</label>

                <select class="form-control" name="parent_id">

                    <option value="">
                        Main Category
                    </option>

                    @include('admin.categories._parent_options', ['categories' => $parents, 'depth' => 0, 'selected' => null])

                </select>

            </div>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary steve-btn">
                    Save
                </button>

                <a href="{{ route('admin.categories.index') }}"
                class="btn btn-primary steve-btn">
                    Back
                </a>
            </div>

        </form>

    </div>

</div>

</div>

@include('admin.partials.image-manager-picker', ['pickerId' => 'category_image', 'targetInput' => 'image_from_manager'])

@endsection
