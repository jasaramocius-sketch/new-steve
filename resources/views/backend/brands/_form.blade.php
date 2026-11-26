@php
    $isEdit = ! empty($brand);
    $statusValue = (string) old('status', $isEdit ? ($brand->status ? '1' : '0') : '1');
@endphp

<style>
    .brand-form .card {
        border-radius: 12px;
    }
    .brand-form .card-header {
        border-bottom: 1px solid #eef0f4;
        border-radius: 12px 12px 0 0;
    }
    .brand-form .form-label {
        color: #1f2937;
        font-size: 0.875rem;
        font-weight: 600;
    }
    .brand-form .form-control,
    .brand-form .form-select {
        border-color: #dfe3ea;
        border-radius: 8px;
        padding: 0.65rem 0.85rem;
    }
    .brand-form .form-control:focus,
    .brand-form .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(52, 84, 209, 0.12);
    }
    .brand-form .form-text {
        font-size: 0.78rem;
    }
    .brand-form-note {
        background: #f5f7ff;
        border: 1px solid #e4e8ff;
        border-radius: 8px;
        color: #5b6478;
        font-size: 0.8rem;
        padding: 10px 12px;
    }
    .brand-image-preview {
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
    .brand-image-preview img {
        display: block;
        height: 100% !important;
        object-fit: contain;
        width: 100% !important;
    }
    .brand-image-preview small {
        display: none;
    }
    .brand-image-placeholder {
        color: #9aa3b2;
        font-size: 0.8rem;
        padding: 24px;
        text-align: center;
    }
    .brand-image-placeholder i {
        color: #c4cad4;
        display: block;
        font-size: 2rem;
        margin-bottom: 8px;
    }
    .brand-form-sidebar {
        position: sticky;
        top: 84px;
    }
    @media (max-width: 991.98px) {
        .brand-form-sidebar {
            position: static;
        }
    }
</style>

<div class="brand-form">
    <form action="{{ $action }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if($method !== 'POST')
            @method($method)
        @endif

        <div class="row g-4 align-items-start">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">Brand Details</h5>
                        <small class="text-muted">
                            {{ $isEdit ? 'Update the brand information below.' : 'Fill in the details to create a new brand.' }}
                        </small>
                    </div>

                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="brand-name">Name <span class="text-danger">*</span></label>
                                <input type="text"
                                       name="name"
                                       id="brand-name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $isEdit ? $brand->name : '') }}"
                                       placeholder="e.g. Bosch"
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="brand-form-note mt-2">
                                    <i class="fas fa-info-circle me-1"></i>
                                    The slug will be generated automatically from the brand name.
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="brand-website">Website</label>
                                <input type="url"
                                       name="website"
                                       id="brand-website"
                                       class="form-control @error('website') is-invalid @enderror"
                                       value="{{ old('website', $isEdit ? $brand->website : '') }}"
                                       placeholder="https://example.com">
                                @error('website')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="brand-status">Status</label>
                                <select name="status" id="brand-status" class="form-select @error('status') is-invalid @enderror">
                                    <option value="1" {{ $statusValue === '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ $statusValue === '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="brand-description">Description</label>
                                <textarea name="description"
                                          id="brand-description"
                                          rows="4"
                                          class="form-control textarea-control texteditor @error('description') is-invalid @enderror">{{ old('description', $isEdit ? $brand->description : '') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="brand-form-sidebar">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">Brand Image</h5>
                            <small class="text-muted">Choose a logo for this brand.</small>
                        </div>

                        <div class="card-body p-4">
                            <div id="impPreview_brand_image" class="brand-image-preview">
                                @if($isEdit && $brand->image)
                                    <img src="{{ storedImageUrl($brand->image, 'assets/images/brands') }}"
                                         alt="{{ $brand->name }}"
                                         onerror="this.onerror=null;this.src='{{ asset('assets/images/placeholder.png') }}'">
                                @else
                                    <div class="brand-image-placeholder">
                                        <i class="fas fa-image"></i>
                                        <span>No image selected</span>
                                    </div>
                                @endif
                            </div>

                            <input type="hidden" name="image_from_manager" id="image_from_manager_brand_image">

                            <button type="button" class="btn btn-sm btn-outline-primary w-100 mt-3" onclick="impOpen_brand_image()">
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
                            <a href="{{ route('admin.brands.index') }}" class="btn btn-outline-secondary flex-fill">
                                Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@include('backend.partials.image-manager-picker', ['pickerId' => 'brand_image', 'targetInput' => 'image_from_manager'])
