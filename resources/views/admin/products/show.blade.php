@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-products-show-page', 'pageClass' => 'admin-products-show-page'])
@section('page-title', 'Product Details — ' . $product->name)
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Product Details - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')

{{-- Product tabs use shared backend.css styles. --}}
<!--
    #productTabs .nav-link {
        border: 1px solid #e9ecef !important;
        border-bottom: none !important;
        margin-right: -1px !important;
        border-radius: 0.375rem 0.375rem 0 0 !important;
        padding: 0.5rem 1rem !important;
        color: #6c757d !important;
    }
    #productTabs .nav-link.active {
        background: #fff !important;
        color: #0d6efd !important;
        border: 1px solid #e9ecef !important;
        border-bottom: 2px solid #0d6efd !important;
        font-weight: 500;
    }
    #productTabs .nav-link:hover:not(.active) {
        background: #f8f9fa !important;
        color: #495057 !important;
    }
    #productTabs .nav-item:first-child .nav-link {
        border-left-start-radius: 0.375rem !important;
    }
    #productTabs .tab-pane {
        padding: 1rem 0;
    }
-->

<div class="d-flex justify-content-between align-items-center mb-3">    
    <!-- <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back</a> -->
    <!-- <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-primary steve-btn"><i class="fas fa-edit me-1"></i> Edit Product</a> -->
</div>

<div class="row g-3">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="fas fa-image me-2"></i>Product Image</h5>
            </div>
            <div class="card-body">
                @if($product->image)
                    <img src="{{ storedImageUrl($product->image, 'assets/images/thumbnails') }}" alt="{{ $product->name }}" class="img-fluid rounded admin-product-image">
                @else
                    <div class="text-muted text-center py-5"><i class="fas fa-image fa-3x mb-2"></i><br>No image uploaded</div>
                @endif

                @if($product->galleryImages->count())
                    <hr>
                    <h6 class="mb-2">Gallery Images</h6>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($product->galleryImages as $img)
                            <img src="{{ $img->thumb_url }}" alt="Gallery" class="rounded admin-gallery-image">
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-3">
                <ul class="nav nav-tabs card-header-tabs admin-product-tabs" id="productTabs">
                    <li class="nav-item">
                        <a class="nav-link active" id="desc-tab" data-bs-toggle="tab" href="#desc" role="tab">
                            <i class="fas fa-file-alt me-1"></i> {{ $product->tab_label_1 ?: 'Description' }}
                        </a>
                    </li>
                    @if($product->policy_text)
                    <li class="nav-item">
                        <a class="nav-link" id="policy-tab" data-bs-toggle="tab" href="#policy" role="tab">
                            <i class="fas fa-shield-alt me-1"></i> {{ $product->tab_label_2 ?: 'Policy' }}
                        </a>
                    </li>
                    @endif
                    @if(!empty($product->features) && count($product->features))
                    <li class="nav-item">
                        <a class="nav-link" id="features-tab" data-bs-toggle="tab" href="#features" role="tab">
                            <i class="fas fa-list-check me-1"></i> Features
                        </a>
                    </li>
                    @endif
                    @if(!empty($product->specifications) && count($product->specifications))
                    <li class="nav-item">
                        <a class="nav-link" id="specifications-tab" data-bs-toggle="tab" href="#specifications" role="tab">
                            <i class="fas fa-sliders-h me-1"></i> Specifications
                        </a>
                    </li>
                    @endif
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="productTabsContent">
                    <div class="tab-pane fade show active" id="desc" role="tabpanel">
                        {!! $product->description ?: '<p class="text-muted">No description</p>' !!}
                    </div>
                    @if($product->policy_text)
                    <div class="tab-pane fade" id="policy" role="tabpanel">
                        {!! $product->policy_text !!}
                    </div>
                    @endif
                    @if(!empty($product->features) && count($product->features ?? []))
                    <div class="tab-pane fade" id="features" role="tabpanel">
                        <ul class="mb-0 key-features-list">
                            @foreach(($product->features ?? []) as $feature)
                                <li>{!! $feature !!}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    @if(!empty($product->specifications) && count($product->specifications ?? []))
                    <div class="tab-pane fade" id="specifications" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0 admin-specs-table">
                                <thead class="table-light">
                                    <tr><th>Label</th><th>Value</th></tr>
                                </thead>
                                <tbody>
                                @foreach($product->specifications as $spec)
                                    <tr><td>{{ $spec['label'] ?? '' }}</td><td>{{ $spec['value'] ?? '' }}</td></tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Product Summary</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <tr>
                        <th class="text-muted admin-table-value">Name</th>
                        <td>{{ $product->name }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">SKU</th>
                        <td>{{ $product->sku ?: '—' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Price</th>
                        <td>{{ currency_format($product->price) }}</td>
                    </tr>
                    @if($product->old_price)
                    <tr>
                        <th class="text-muted">Old Price</th>
                        <td><s class="text-danger">{{ currency_format($product->old_price) }}</s></td>
                    </tr>
                    @endif
                    <tr>
                        <th class="text-muted">Category</th>
                        <td>{{ $product->category->name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Brand</th>
                        <td>{{ $product->brand->name ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Stock</th>
                        <td>
                            @if($product->stock > 0)
                                <span class="text-success">{{ $product->stock }}</span>
                            @else
                                <span class="text-danger">Out of Stock</span>
                            @endif
                        </td>
                    </tr>
                    @if($product->badge)
                    <tr>
                        <th class="text-muted">Badge</th>
                        <td><span class="badge bg-light text-warning border border-warning-subtle">{{ $product->badge }}</span></td>
                    </tr>
                    @endif
                    <tr>
                        <th class="text-muted">Product Type</th>
                        <td>{{ ucfirst($product->product_type ?? 'none') }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Status</th>
                        <td>
                            @if($product->status)
                                <span class="badge bg-light text-success border border-success-subtle">Active</span>
                            @else
                                <span class="badge bg-light text-secondary border border-secondary-subtle">Inactive</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th class="text-muted">Featured</th>
                        <td>
                            @if($product->featured)
                                <span class="badge bg-light text-success border border-success-subtle">Yes</span>
                            @else
                                <span class="badge bg-light text-secondary border border-secondary-subtle">No</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th class="text-muted">Rating</th>
                        <td>{{ number_format($product->rating, 1) }} / 5 ({{ $product->reviews }} reviews)</td>
                    </tr>
                </table>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="fas fa-car me-2"></i>Vehicle Details</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <tr>
                        <th class="text-muted admin-table-value">Year</th>
                        <td>{{ $product->year ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Make</th>
                        <td>{{ $product->make ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Model</th>
                        <td>{{ $product->model ?? '—' }}</td>
                    </tr>
                </table>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Metadata</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <tr>
                        <th class="text-muted admin-table-value">ID</th>
                        <td>#{{ $product->id }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Slug</th>
                        <td><code>{{ $product->slug }}</code></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Added By</th>
                        <td>{{ $product->added_by ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Created</th>
                        <td>{{ $product->created_at ? $product->created_at->format('M d, Y h:i A') : '—' }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Updated</th>
                        <td>{{ $product->updated_at ? $product->updated_at->format('M d, Y h:i A') : '—' }}</td>
                    </tr>
                </table>
                </div>
            </div>
        </div>

        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-primary steve-btn w-100">
            <i class="fas fa-edit me-1"></i> Edit This Product
        </a>
    </div>
</div>

@endsection
