@extends('backend.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-products-edit-page', 'pageClass' => 'admin-products-edit-page'])
@section('page-title', 'Edit Product')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Edit Product - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">Edit Product</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
            @csrf @method('PUT')

            <!-- Tab Navigation -->
            <ul class="nav nav-tabs nav-fill mb-4" id="productTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active border-radius-0" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic-info" type="button" role="tab" aria-controls="basic-info" aria-selected="true">
                        <i class="fas fa-info-circle me-1"></i> Basic Info
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link border-radius-0" id="vehicle-tab" data-bs-toggle="tab" data-bs-target="#vehicle-details" type="button" role="tab" aria-controls="vehicle-details" aria-selected="false">
                        <i class="fas fa-car me-1"></i> Vehicle Details
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link border-radius-0" id="content-tab" data-bs-toggle="tab" data-bs-target="#content-details" type="button" role="tab" aria-controls="content-details" aria-selected="false">
                        <i class="fas fa-file-alt me-1"></i> Content & Descriptions
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link border-radius-0" id="seo-tab" data-bs-toggle="tab" data-bs-target="#seo-details" type="button" role="tab" aria-controls="seo-details" aria-selected="false">
                        <i class="fas fa-search me-1"></i> SEO
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link border-radius-0" id="media-tab" data-bs-toggle="tab" data-bs-target="#media-details" type="button" role="tab" aria-controls="media-details" aria-selected="false">
                        <i class="fas fa-cloud-upload-alt me-1"></i> Media
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
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $product->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror

                            <label class="form-label mt-2" for="product-part-number">Part Number</label>
                            <input type="text" id="product-part-number" class="form-control bg-light" value="{{ $product->part_number }}" readonly disabled>
                            <small class="form-text text-muted">Part Number is generated automatically and cannot be changed.</small>

                            <label class="form-label mt-2" for="product-sku">SKU</label>
                            <input type="text" id="product-sku" name="sku" class="form-control @error('sku') is-invalid @enderror" value="{{ old('sku', $product->sku) }}" maxlength="100" placeholder="Leave empty to auto-generate">
                            @error('sku') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <small class="form-text text-muted">SKU is separate from Part Number. Leave empty to generate one automatically.</small>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Price *</label>
                            <input type="number" step="0.01" name="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', $product->price) }}" required>
                            @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Old Price</label>
                            <input type="number" step="0.01" name="old_price" class="form-control" value="{{ old('old_price', $product->old_price) }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Shipping Cost</label>
                            <input type="number" step="0.01" min="0" name="shipping_cost" class="form-control" value="{{ old('shipping_cost', $product->shipping_cost ?? 0) }}">
                            <small class="text-muted">Used with Product Wise Shipping.</small>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Commission (%)</label>
                            <input type="number" step="0.01" min="0" max="100" name="commission" class="form-control no-spinner @error('commission') is-invalid @enderror" value="{{ old('commission', $product->commission) }}" placeholder="Global ({{ number_format(globalCommissionPercent(), 2) }}%)">
                            @error('commission') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <small class="text-muted">Leave empty to use the global commission.</small>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select" data-ajax-url="{{ route('admin.products.lookup', ['type' => 'category']) }}" data-search-placeholder="Search categories…">
                                <option value="">None</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ (old('category_id', $product->category_id) == $cat->id) ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Brand</label>
                            <select name="brand_id" class="form-select" data-ajax-url="{{ route('admin.products.lookup', ['type' => 'brand']) }}" data-search-placeholder="Search brands…">
                                <option value="">None</option>
                                @foreach($brands as $b)
                                    <option value="{{ $b->id }}" {{ (old('brand_id', $product->brand_id) == $b->id) ? 'selected' : '' }}>{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        @if (\App\Support\FeatureFlags::enabled('vendor_system_activation') && \App\Support\FeatureFlags::enabled('product_manage_by_admin'))
                        <div class="col-md-3">
                            <label class="form-label">Seller</label>
                            <select name="seller_id" class="form-select" data-ajax-url="{{ route('admin.products.lookup', ['type' => 'seller']) }}" data-search-placeholder="Search sellers…">
                                <option value="">None</option>
                                @foreach($sellers as $s)
                                    <option value="{{ $s->id }}" {{ (old('seller_id', $product->seller_id) == $s->id) ? 'selected' : '' }}>{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @elseif ($product->seller_id)
                        <div class="col-md-3">
                            <label class="form-label">Seller</label>
                            <input type="text" class="form-control" value="{{ $product->seller?->name ?? ('Seller #'.$product->seller_id) }}" disabled>
                        </div>
                        @endif

                        <div class="col-md-2">
                            <label class="form-label">Stock</label>
                            <input type="number" name="stock" class="form-control" value="{{ old('stock', $product->stock) }}" min="0">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Condition</label>
                            <select name="condition" class="form-select">
                                @foreach(['New', 'Used', 'Remanufactured', 'Refurbished', 'Core'] as $condition)
                                    <option value="{{ $condition }}" {{ old('condition', $product->condition) === $condition ? 'selected' : '' }}>{{ $condition }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Warranty (months)</label>
                            <input type="number" name="warranty_months" class="form-control" value="{{ old('warranty_months', $product->warranty_months) }}" min="0" max="600" placeholder="e.g. 24">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Warranty Details</label>
                            <input type="text" name="warranty_text" class="form-control" value="{{ old('warranty_text', $product->warranty_text) }}" placeholder="e.g. 2-year limited warranty">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Weight (kg)</label>
                            <input type="number" step="0.01" min="0" name="weight_kg" class="form-control" value="{{ old('weight_kg', $product->weight_kg) }}" placeholder="0.00">
                            <small class="text-muted">Used by carrier rate calculations.</small>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Length (cm)</label>
                            <input type="number" step="0.01" min="0" name="dimensions[length_cm]" class="form-control" value="{{ old('dimensions.length_cm', $product->dimensions['length_cm'] ?? null) }}" placeholder="0.00">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Width (cm)</label>
                            <input type="number" step="0.01" min="0" name="dimensions[width_cm]" class="form-control" value="{{ old('dimensions.width_cm', $product->dimensions['width_cm'] ?? null) }}" placeholder="0.00">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Height (cm)</label>
                            <input type="number" step="0.01" min="0" name="dimensions[height_cm]" class="form-control" value="{{ old('dimensions.height_cm', $product->dimensions['height_cm'] ?? null) }}" placeholder="0.00">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Badge</label>
                            <input type="text" name="badge" class="form-control" value="{{ old('badge', $product->badge) }}" placeholder="e.g. New, Sale">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Section</label>
                            <select name="product_type" class="form-select">
                                <option value="none" {{ old('product_type', $product->product_type) == 'none' ? 'selected' : '' }}>None</option>
                                <option value="new_arrival" {{ old('product_type', $product->product_type) == 'new_arrival' ? 'selected' : '' }}>New Arrivals</option>
                                <option value="trending" {{ old('product_type', $product->product_type) == 'trending' ? 'selected' : '' }}>Trending</option>
                                <option value="best_selling" {{ old('product_type', $product->product_type) == 'best_selling' ? 'selected' : '' }}>Best Selling</option>
                                <option value="popular" {{ old('product_type', $product->product_type) == 'popular' ? 'selected' : '' }}>Popular</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="1" {{ old('status', $product->status) == '1' ? 'selected' : '' }}>Active</option>
                                <option value="0" {{ old('status', $product->status) == '0' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label d-block">&nbsp;</label>
                            <div class="form-check form-switch mt-1">
                                <input type="checkbox" name="featured" value="1" class="form-check-input" id="featured" {{ old('featured', $product->featured) ? 'checked' : '' }}>
                                <label class="form-check-label" for="featured">Featured Product</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 2: Vehicle Details -->
                <div class="tab-pane fade" id="vehicle-details" role="tabpanel" aria-labelledby="vehicle-tab">
                    @include('backend.products.partials.fitment-editor', [
                        'fitmentRows' => $fitments,
                    ])
                </div>

                <!-- Tab 3: Content & Descriptions -->
                <div class="tab-pane fade" id="content-details" role="tabpanel" aria-labelledby="content-tab">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Tab 1 Label</label>
                            <input type="text" name="tab_label_1" class="form-control" value="{{ old('tab_label_1', $product->tab_label_1) }}" placeholder="Description">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tab 2 Label</label>
                            <input type="text" name="tab_label_2" class="form-control" value="{{ old('tab_label_2', $product->tab_label_2) }}" placeholder="Policy">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tab 3 Label</label>
                            <input type="text" name="tab_label_3" class="form-control" value="{{ old('tab_label_3', $product->tab_label_3) }}" placeholder="Reviews">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control textarea-control texteditor" rows="4">{{ old('overview', $product->overview) }}</textarea>
                        </div>
                        
                        <div class="col-md-12">
                            <label class="form-label">Policy Text</label>
                            <textarea name="policy_text" class="form-control textarea-control texteditor" rows="4">{{ old('policy_text', $product->policy_text ?? '') }}</textarea>
                        </div>
                        
                        <div class="col-md-12">
                            <label class="form-label">Key Features</label>
                            <textarea name="features" class="form-control textarea-control" rows="4" placeholder="Enter one feature per line...">{{ old('features', is_array($product->features) ? implode("\n", $product->features) : '') }}</textarea>
                            @include('backend.partials.key-features-preview', ['initialFeatures' => old('features', is_array($product->features) ? implode("\n", $product->features) : '')])
                        </div>
                        
                        <div class="col-md-12">
                            <label class="form-label">Specifications</label>
                            <textarea name="specifications" class="form-control textarea-control text-monospace" rows="4" placeholder="One per line, format:  Label :: Value&#10;e.g.&#10;Brand :: Duralast&#10;Material :: Ceramic">{{ old('specifications', is_array($product->specifications) ? implode("\n", array_map(fn ($s) => ($s['label'] ?? $s['title'] ?? '') . ' :: ' . ($s['value'] ?? ''), $product->specifications)) : '') }}</textarea>
                            <small class="text-muted">Format: <code>Label :: Value</code> on each line (rendered as a specifications table on the product page).</small>
                            @include('backend.partials.specifications-preview', ['initialSpecifications' => old('specifications', is_array($product->specifications) ? implode("\n", array_map(fn ($s) => ($s['label'] ?? $s['title'] ?? '') . ' :: ' . ($s['value'] ?? ''), $product->specifications)) : '')])
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">OEM Numbers</label>
                            <textarea name="oem_numbers" class="form-control textarea-control" rows="4" placeholder="One number per line or comma separated">{{ old('oem_numbers', implode("\n", $product->oem_numbers)) }}</textarea>
                            <small class="text-muted">Original equipment numbers this part replaces.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Interchange / Cross-Reference Numbers</label>
                            <textarea name="interchange_numbers" class="form-control textarea-control" rows="4" placeholder="One number per line or comma separated">{{ old('interchange_numbers', implode("\n", $product->interchange_numbers)) }}</textarea>
                            <small class="text-muted">Equivalent numbers from other brands.</small>
                        </div>
                        
                        <div class="col-md-12">
                            <label class="form-label">Reviews Data (JSON array)</label>
                            <textarea name="reviews_data" class="form-control textarea-control text-monospace" rows="4" placeholder='[{"name":"John","rating":5,"text":"Great product!"}]'>{{ old('reviews_data', is_array($product->reviews_data) ? json_encode($product->reviews_data, JSON_PRETTY_PRINT) : '') }}</textarea>
                            <small class="text-muted">Format: [{"name":"John","rating":5,"text":"Great product!"}]</small>
                        </div>
                    </div>
                </div>

                <!-- Tab 4: SEO -->
                <div class="tab-pane fade" id="seo-details" role="tabpanel" aria-labelledby="seo-tab">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" class="form-control" value="{{ old('slug', $product->slug) }}" placeholder="e.g. toyota-camry-2-5l-o2-sensor">
                            <small class="text-muted">Changing this will change the public product URL. Existing links to the old slug will stop working.</small>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Meta Title</label>
                            <input type="text" name="meta_title" class="form-control" value="{{ old('meta_title', $product->meta_title) }}" maxlength="255" placeholder="e.g. Toyota Camry 2.5L Oxygen Sensor">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Meta Description</label>
                            <textarea name="meta_description" class="form-control textarea-control" rows="3" maxlength="500">{{ old('meta_description', $product->meta_description) }}</textarea>
                            <small class="text-muted">Leave blank to use the product description automatically.</small>
                        </div>
                    </div>
                </div>

                <!-- Tab 5: Media -->
                <div class="tab-pane fade" id="media-details" role="tabpanel" aria-labelledby="media-tab">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Primary Image</label>
                            <div class="card p-3 border-dashed h-100">
                                @if($product->image)
                                    <div class="mb-3 d-flex align-items-center bg-light p-2 rounded">
                                        <img src="{{ storedImageUrl($product->image, 'assets/images/thumbnails') }}" width="60" height="60" class="admin-image-thumb-bordered" onerror="this.onerror=null;this.src='{{ asset('assets/images/placeholder.png') }}'">
                                        <div class="ms-3">
                                            <small class="text-muted d-block fw-bold">Current Primary Image</small>
                                            <small class="text-muted">Upload a new one below or choose from image manager to replace.</small>
                                        </div>
                                    </div>
                                @endif
                                
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
                                <div>
                                    <button type="button" class="btn btn-sm btn-outline-primary mt-1" onclick="impOpen_product_image()">
                                        <i class="fas fa-cloud-upload-alt me-1"></i> Browse
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Gallery Images</label>
                            <div class="card p-3 border-dashed h-100">
                                @if($product->galleryImages->count() > 0)
                                    <div class="mb-3">
                                        <small class="text-muted d-block fw-bold mb-2">Existing Gallery Images</small>
                                        <div class="row g-2" id="existing-gallery">
                                            @foreach($product->galleryImages as $img)
                                                <div class="col-auto position-relative text-center">
                                                       <img src="{{ $img->thumb_url }}" width="60" height="60" class="admin-image-thumb-bordered">
                                                    <div class="form-check mt-1 d-flex justify-content-center">
                                                        <input type="checkbox" name="delete_gallery_ids[]" value="{{ $img->id }}" class="form-check-input me-1" id="del_img_{{ $img->id }}">
                                                        <label class="form-check-label text-danger small admin-clickable" for="del_img_{{ $img->id }}">Remove</label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <!-- <hr class="my-3 text-muted">    -->
                                    </div>
                                @endif

                                <input type="file" name="gallery_images[]" class="form-control mb-2" multiple accept="image/*">
                                <input type="hidden" name="gallery_images_from_manager" id="gallery_images_from_manager">
                                <div id="impPreview_gallery_images" class="d-none my-2"></div>
                                
                                <div>
                                    <button type="button" class="btn btn-sm btn-outline-primary mt-1" onclick="impOpen_gallery_images()">
                                        <i class="fas fa-cloud-upload-alt me-1"></i> Browse
                                    </button>
                                    <small class="text-muted mt-2 d-block">Pick multiple from Image Manager, or upload files above.</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div> <!-- End Tab Content -->

            <hr class="mt-4 mb-3">
            
            <!-- Form Actions -->
            <div class="d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary steve-btn gap-1"><i class="fas fa-save"></i> Update Product</button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-primary steve-btn gap-1"><i class="fas fa-times"></i>Cancel</a>
            </div>
            
        </form>

        @if($product->exists)
            <form id="fitmentImportForm" action="{{ route('admin.products.fitments.import-csv', $product->id) }}" method="POST" enctype="multipart/form-data" class="d-none">
                @csrf
                <input type="file" id="fitmentImportFile" name="fitment_file" accept=".csv,.txt,.xml" onchange="if(this.files.length){document.getElementById('fitmentImportForm').submit();}">
            </form>
        @endif
    </div>
</div>

@include('backend.partials.image-manager-picker', ['pickerId' => 'product_image', 'targetInput' => 'image_from_manager'])
@include('backend.partials.image-manager-picker', ['pickerId' => 'gallery_images', 'targetInput' => 'gallery_images_from_manager', 'multiple' => true])

@endsection