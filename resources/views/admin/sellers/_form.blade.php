@php
    $isEdit = ! empty($seller);
    $statusValue = (string) old('status', $isEdit ? ($seller->status ? '1' : '0') : '1');
@endphp

<style>
    .seller-form .card {
        border-radius: 12px;
    }
    .seller-form .card-header {
        border-bottom: 1px solid #eef0f4;
        border-radius: 12px 12px 0 0;
    }
    .seller-form .form-label {
        color: #1f2937;
        font-size: 0.875rem;
        font-weight: 600;
    }
    .seller-form .form-control,
    .seller-form .form-select {
        border-color: #dfe3ea;
        border-radius: 8px;
        padding: 0.65rem 0.85rem;
    }
    .seller-form .form-control:focus,
    .seller-form .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(52, 84, 209, 0.12);
    }
    .seller-form .form-text {
        font-size: 0.78rem;
    }
    .seller-form-note {
        background: #f5f7ff;
        border: 1px solid #e4e8ff;
        border-radius: 8px;
        color: #5b6478;
        font-size: 0.8rem;
        padding: 10px 12px;
    }
    .seller-image-preview {
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
    .seller-image-preview img {
        display: block;
        height: 100% !important;
        object-fit: contain;
        width: 100% !important;
    }
    .seller-image-preview small {
        display: none;
    }
    .seller-image-placeholder {
        color: #9aa3b2;
        font-size: 0.8rem;
        padding: 24px;
        text-align: center;
    }
    .seller-image-placeholder i {
        color: #c4cad4;
        display: block;
        font-size: 2rem;
        margin-bottom: 8px;
    }
    .seller-form-sidebar {
        position: sticky;
        top: 84px;
    }
    @media (max-width: 991.98px) {
        .seller-form-sidebar {
            position: static;
        }
    }
</style>

<div class="seller-form">
    <form action="{{ $action }}" method="POST" enctype="multipart/form-data">
        @csrf
        @if($method !== 'POST')
            @method($method)
        @endif

        <div class="row g-4 align-items-start">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">Seller Details</h5>
                        <small class="text-muted">
                            {{ $isEdit ? 'Update the seller information below.' : 'Fill in the details to create a new seller.' }}
                        </small>
                    </div>

                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="seller-name">Name <span class="text-danger">*</span></label>
                                <input type="text"
                                       name="name"
                                       id="seller-name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $isEdit ? $seller->name : '') }}"
                                       placeholder="e.g. Auto Parts Central"
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="seller-form-note mt-2">
                                    <i class="fas fa-info-circle me-1"></i>
                                    The slug will be generated automatically from the seller name.
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="seller-location">Location</label>
                                <input type="text"
                                       name="location"
                                       id="seller-location"
                                       class="form-control @error('location') is-invalid @enderror"
                                       value="{{ old('location', $isEdit ? $seller->location : '') }}"
                                       placeholder="City, Country">
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="seller-shipping-cost">Shipping Cost</label>
                                <input type="number"
                                       step="0.01"
                                       min="0"
                                       name="shipping_cost"
                                       id="seller-shipping-cost"
                                       class="form-control @error('shipping_cost') is-invalid @enderror"
                                       value="{{ old('shipping_cost', $isEdit ? $seller->shipping_cost : 0) }}">
                                <div class="form-text">Used with Seller Wise Flat Shipping.</div>
                                @error('shipping_cost')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label" for="seller-status">Status</label>
                                <select name="status" id="seller-status" class="form-select @error('status') is-invalid @enderror">
                                    <option value="1" {{ $statusValue === '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ $statusValue === '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label" for="seller-description">Description</label>
                                <textarea name="description"
                                          id="seller-description"
                                          rows="4"
                                          class="form-control textarea-control texteditor @error('description') is-invalid @enderror">{{ old('description', $isEdit ? $seller->description : '') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="seller-form-sidebar">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white py-3">
                            <h5 class="mb-0">Seller Image</h5>
                            <small class="text-muted">Choose an image for this seller.</small>
                        </div>

                        <div class="card-body p-4">
                            <div id="impPreview_seller_image" class="seller-image-preview">
                                @if($isEdit && $seller->image)
                                    <img src="{{ storedImageUrl($seller->image, 'assets/images') }}"
                                         alt="{{ $seller->name }}"
                                         onerror="this.onerror=null;this.src='{{ asset('assets/images/placeholder.png') }}'">
                                @else
                                    <div class="seller-image-placeholder">
                                        <i class="fas fa-image"></i>
                                        <span>No image selected</span>
                                    </div>
                                @endif
                            </div>

                            <input type="hidden" name="image_from_manager" id="image_from_manager_seller_image">

                            <button type="button" class="btn btn-sm btn-outline-primary w-100 mt-3" onclick="impOpen_seller_image()">
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
                            <a href="{{ route('admin.sellers.index') }}" class="btn btn-outline-secondary flex-fill">
                                Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@include('admin.partials.image-manager-picker', ['pickerId' => 'seller_image', 'targetInput' => 'image_from_manager'])
