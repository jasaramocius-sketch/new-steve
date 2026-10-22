@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-products-create-page', 'pageClass' => 'admin-products-create-page'])
@section('page-title', 'Add Product')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Add Product - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')

<div class="card border-0 shadow-sm">    
    <div class="card-body">
        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Tab Navigation -->
            <ul class="nav nav-tabs nav-fill mb-4" id="productTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic-info" type="button" role="tab" aria-controls="basic-info" aria-selected="true">
                        <i class="fas fa-info-circle me-1"></i> Basic Info
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="vehicle-tab" data-bs-toggle="tab" data-bs-target="#vehicle-details" type="button" role="tab" aria-controls="vehicle-details" aria-selected="false">
                        <i class="fas fa-car me-1"></i> Vehicle Details
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="content-tab" data-bs-toggle="tab" data-bs-target="#content-details" type="button" role="tab" aria-controls="content-details" aria-selected="false">
                        <i class="fas fa-file-alt me-1"></i> Content & Descriptions
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="media-tab" data-bs-toggle="tab" data-bs-target="#media-details" type="button" role="tab" aria-controls="media-details" aria-selected="false">
                        <i class="fas fa-images me-1"></i> Media
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="productTabsContent">
                
                <!-- Tab 1: Basic Info -->
                <div class="tab-pane fade show active" id="basic-info" role="tabpanel" aria-labelledby="basic-tab">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name *</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Price *</label>
                            <input type="number" step="0.01" name="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price') }}" required>
                            @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">SKU / Part Number</label>
                            <div class="input-group">
                                <input type="text" name="sku" id="sku" class="form-control @error('sku') is-invalid @enderror" value="{{ old('sku') }}" placeholder="e.g. am-6113">
                                <button type="button" class="btn btn-outline-secondary" id="sku-generate-btn" title="Generate SKU"><i class="fas fa-dice"></i> Auto</button>
                                @error('sku') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Old Price</label>
                            <input type="number" step="0.01" name="old_price" class="form-control" value="{{ old('old_price') }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select">
                                <option value="">None</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Brand</label>
                            <select name="brand_id" class="form-select">
                                <option value="">None</option>
                                @foreach($brands as $b)
                                    <option value="{{ $b->id }}" {{ old('brand_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Seller</label>
                            <select name="seller_id" class="form-select">
                                <option value="">None</option>
                                @foreach($sellers as $s)
                                    <option value="{{ $s->id }}" {{ old('seller_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Stock</label>
                            <input type="number" name="stock" class="form-control" value="{{ old('stock', 0) }}" min="0">
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Badge</label>
                            <input type="text" name="badge" class="form-control" value="{{ old('badge') }}" placeholder="e.g. New, Sale">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Section</label>
                            <select name="product_type" class="form-select">
                                <option value="none" {{ old('product_type') == 'none' ? 'selected' : '' }}>None</option>
                                <option value="new_arrival" {{ old('product_type') == 'new_arrival' ? 'selected' : '' }}>New Arrivals</option>
                                <option value="trending" {{ old('product_type') == 'trending' ? 'selected' : '' }}>Trending</option>
                                <option value="best_selling" {{ old('product_type') == 'best_selling' ? 'selected' : '' }}>Best Selling</option>
                                <option value="popular" {{ old('product_type') == 'popular' ? 'selected' : '' }}>Popular</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('status', '1') == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label d-block">&nbsp;</label>
                            <div class="form-check form-switch mt-1">
                                <input type="checkbox" name="featured" value="1" class="form-check-input" id="featured" {{ old('featured') ? 'checked' : '' }}>
                                <label class="form-check-label" for="featured">Featured Product</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Vehicle Details -->
                <div class="tab-pane fade" id="vehicle-details" role="tabpanel" aria-labelledby="vehicle-tab">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Year</label>
                            <input type="number" name="year" class="form-control" value="{{ old('year') }}" min="1900" max="2026" placeholder="e.g. 2020">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Make</label>
                            <input type="text" name="make" class="form-control" value="{{ old('make') }}" placeholder="e.g. Toyota">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Model</label>
                            <input type="text" name="model" class="form-control" value="{{ old('model') }}" placeholder="e.g. Camry">
                        </div>
                    </div>
                </div>

                <!-- Tab 3: Content & Descriptions -->
                <div class="tab-pane fade" id="content-details" role="tabpanel" aria-labelledby="content-tab">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Tab 1 Label</label>
                            <input type="text" name="tab_label_1" class="form-control" value="{{ old('tab_label_1') }}" placeholder="Description">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tab 2 Label</label>
                            <input type="text" name="tab_label_2" class="form-control" value="{{ old('tab_label_2') }}" placeholder="Policy">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tab 3 Label</label>
                            <input type="text" name="tab_label_3" class="form-control" value="{{ old('tab_label_3') }}" placeholder="Reviews">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control texteditor" rows="4">{{ old('description') }}</textarea>
                        </div>
                        
                        <div class="col-md-12">
                            <label class="form-label">Policy Text</label>
                            <textarea name="policy_text" class="form-control texteditor" rows="4">{{ old('policy_text') }}</textarea>
                        </div>
                        
                        <div class="col-md-12">
                            <label class="form-label">Key Features</label>
                            <textarea name="features" class="form-control" rows="4" placeholder="Enter one feature per line...">{{ old('features') }}</textarea>
                            @include('admin.partials.key-features-preview', ['initialFeatures' => old('features', '')])
                        </div>
                        
                        <div class="col-md-12">
                            <label class="form-label">Specifications</label>
                            <textarea name="specifications" class="form-control text-monospace" rows="4" placeholder="One per line, format:  Label :: Value&#10;e.g.&#10;Brand :: Duralast&#10;Material :: Ceramic">{{ old('specifications') }}</textarea>
                            <small class="text-muted">Format: <code>Label :: Value</code> on each line (rendered as a specifications table on the product page).</small>
                            @include('admin.partials.specifications-preview', ['initialSpecifications' => old('specifications', '')])
                        </div>
                        
                        <div class="col-md-12">
                            <label class="form-label">Reviews Data (JSON array)</label>
                            <textarea name="reviews_data" class="form-control text-monospace" rows="4" placeholder='[{"name":"John","rating":5,"text":"Great product!"}]'>{{ old('reviews_data') }}</textarea>
                            <small class="text-muted">Format: [{"name":"John","rating":5,"text":"Great product!"}]</small>
                        </div>
                    </div>
                </div>

                <!-- Tab 4: Media -->
                <div class="tab-pane fade" id="media-details" role="tabpanel" aria-labelledby="media-tab">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Primary Image</label>
                            <div class="card p-3 border-dashed">
                                <input type="file" name="image" class="form-control mb-2 @error('image') is-invalid @enderror" accept="image/*">
                                @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    @php $selectedImageFromManager = old('image_from_manager'); @endphp
                                    <input type="hidden" name="image_from_manager" id="image_from_manager_product_image" value="{{ $selectedImageFromManager }}">
                                    <div id="impPreview_product_image" class="{{ $selectedImageFromManager ? '' : 'd-none' }} my-2">
                                        @if($selectedImageFromManager)
                                            <img src="{{ asset('storage/' . $selectedImageFromManager) }}" width="80" class="admin-image-thumb">
                                            <small class="text-muted d-block">Selected from image manager</small>
                                        @endif
                                    </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Gallery Images</label>
                            <div class="card p-3 border-dashed">
                                <input type="file" name="gallery_images[]" class="form-control mb-2" multiple accept="image/*">
                                <input type="hidden" name="gallery_images_from_manager" id="gallery_images_from_manager">
                                <div id="impPreview_gallery_images" class="d-none my-2"></div>
                                <button type="button" class="btn btn-sm btn-outline-primary mt-1" onclick="impOpen_gallery_images()">
                                    <i class="fas fa-folder-open me-1"></i> Browse Image Manager
                                </button>
                                <small class="text-muted mt-2 d-block">Pick multiple images from Image Manager, or upload files above.</small>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div> <!-- End Tab Content -->

            <hr class="mt-5 mb-4">
            
            <!-- Form Actions -->
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary steve-btn px-4"><i class="fas fa-save me-1"></i> Save Product</button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-primary steve-btn px-4">Cancel</a>
            </div>
            
        </form>
    </div>
</div>

@include('admin.partials.image-manager-picker', ['pickerId' => 'product_image', 'targetInput' => 'image_from_manager'])
@include('admin.partials.image-manager-picker', ['pickerId' => 'gallery_images', 'targetInput' => 'gallery_images_from_manager', 'multiple' => true])

<script>
document.getElementById('sku-generate-btn').addEventListener('click', function () {
    const name = (document.querySelector('input[name="name"]')?.value || '').trim();
    const slug = name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
    const prefix = ('SKU-' + slug.substring(0, 6)).toUpperCase();
    const rand = Math.random().toString(36).substring(2, 8).toUpperCase();
    document.getElementById('sku').value = prefix + '-' + rand;
});
</script>

@endsection