@php
    $isEdit = ! empty($category);
    $statusValue = (string) old('status', $isEdit ? ($category->status ? '1' : '0') : '1');
    $selectedParent = old('parent_id', $isEdit ? $category->parent_id : null);
@endphp

<style>
    .category-form .card {
        border-radius: 12px;
    }
    .category-form .card-header {
        border-bottom: 1px solid #eef0f4;
        border-radius: 12px 12px 0 0;
    }
    .category-form .form-label {
        color: #1f2937;
        font-size: 0.875rem;
        font-weight: 600;
    }
    .category-form .form-control,
    .category-form .form-select {
        border-color: #dfe3ea;
        border-radius: 8px;
        padding: 0.65rem 0.85rem;
    }
    .category-form .form-control:focus,
    .category-form .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(52, 84, 209, 0.12);
    }
    .category-form .form-text {
        font-size: 0.78rem;
    }
    .category-form-note {
        background: #f5f7ff;
        border: 1px solid #e4e8ff;
        border-radius: 8px;
        color: #5b6478;
        font-size: 0.8rem;
        padding: 10px 12px;
    }
    .category-image-preview {
        align-items: center;
        aspect-ratio: 1 / 1;
        background: #f8f9fb;
        border: 1.5px dashed #d6dbe3;
        border-radius: 12px;
        display: flex;
        justify-content: center;
        overflow: hidden;
        width: 100%;
    }
    .category-image-preview img {
        display: block;
        height: 100% !important;
        object-fit: cover;
        width: 100% !important;
    }
    .category-image-preview small {
        display: none;
    }
    .category-image-placeholder {
        color: #9aa3b2;
        font-size: 0.8rem;
        padding: 24px;
        text-align: center;
    }
    .category-image-placeholder i {
        color: #c4cad4;
        display: block;
        font-size: 2rem;
        margin-bottom: 8px;
    }
    .category-form-sidebar {
        position: sticky;
        top: 84px;
    }
    @media (max-width: 991.98px) {
        .category-form-sidebar {
            position: static;
        }
    }
</style>

<div class="category-form">
    <form action="{{ $action }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if($method !== 'POST')
            @method($method)
        @endif

        <div class="row g-4 align-items-start">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">Category Details</h5>
                        <small class="text-muted">
                            {{ $isEdit ? 'Update the category information below.' : 'Fill in the details to create a new category.' }}
                        </small>
                    </div>

                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="category-name">Name <span class="text-danger">*</span></label>
                                <input type="text"
                                       name="name"
                                       id="category-name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $isEdit ? $category->name : '') }}"
                                       placeholder="e.g. Engine Parts"
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="category-form-note mt-2">
                                    <i class="fas fa-info-circle me-1"></i>
                                    The slug will be generated automatically from the category name.
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="category-parent">Parent Category</label>
                                <select name="parent_id" id="category-parent" class="form-select @error('parent_id') is-invalid @enderror">
                                    <option value="">— Main Category —</option>
                                    @include('backend.categories._parent_options', [
                                        'frontend.categories' => $parents,
                                        'depth' => 0,
                                        'selected' => $selectedParent,
                                        'excluded' => $excluded ?? [],
                                    ])
                                </select>
                                @error('parent_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Leave empty to create a top-level category.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="category-status">Status</label>
                                <select name="status" id="category-status" class="form-select @error('status') is-invalid @enderror">
                                    <option value="1" {{ $statusValue === '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ $statusValue === '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="category-image-url">Or Image Download URL</label>
                                <input type="url"
                                       name="image_url"
                                       id="category-image-url"
                                       class="form-control @error('image_url') is-invalid @enderror"
                                       value="{{ old('image_url') }}"
                                       placeholder="https://example.com/image.png">
                                @error('image_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Provide a direct image URL and the server will download and store it locally.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="category-form-sidebar">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">Category Image</h5>
                            <small class="text-muted">Choose an image for this category.</small>
                        </div>

                        <div class="card-body p-4">
                            <div id="impPreview_category_image" class="category-image-preview">
                                @if($isEdit && $category->image)
                                    <img src="{{ storedImageUrl($category->image, 'assets/images/categories') }}"
                                         alt="{{ $category->name }}"
                                         onerror="this.onerror=null;this.src='{{ asset('assets/images/placeholder.png') }}'">
                                @else
                                    <div class="category-image-placeholder">
                                        <i class="fas fa-image"></i>
                                        <span>No image selected</span>
                                    </div>
                                @endif
                            </div>

                            <input type="hidden" name="image_from_manager" id="image_from_manager_category_image">

                            <button type="button" class="btn btn-sm btn-outline-primary w-100 mt-3" onclick="impOpen_category_image()">
                                <i class="fas fa-cloud-upload-alt me-1"></i> Browse
                            </button>
                            <div class="form-text text-center mt-2">Recommended: 800 × 800px · JPG, PNG or WebP</div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mt-3">
                        <div class="card-body p-3 d-flex gap-2">
                            <button class="btn btn-primary steve-btn flex-fill">
                                <i class="fas fa-save me-1"></i> {{ $submitLabel }}
                            </button>
                            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary flex-fill">
                                Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@include('backend.partials.image-manager-picker', ['pickerId' => 'category_image', 'targetInput' => 'image_from_manager'])
