@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-products-index-page', 'pageClass' => 'admin-products-index-page'])
@section('page-title', 'All Products')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'All Products - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')

@php
    $trashedCount = \App\Models\Product::onlyTrashed()->count();
    $hasActiveFilters = request()->filled('search') || request()->filled('category_id') || request()->filled('brand_id') || request()->filled('seller_id')
        || (request()->filled('product_type') && request('product_type') !== 'all')
        || (request()->filled('stock_filter') && request('stock_filter') !== 'all')
        || request()->filled('status') || request()->filled('featured');
@endphp

<div class="d-flex justify-content-between align-items-center mb-3 gap-2 flex-wrap">
    <div class=""></div>
    <div class="d-flex gap-2 flex-wrap admin-product-page-important-btn">
        <a href="{{ route('admin.products.import-form') }}" class="btn btn-outline-primary product-import-export-btn steve-btn"><i class="fas fa-upload"></i> Import</a>
        <a href="{{ route('admin.products.export-csv') }}" class="btn btn-outline-secondary product-import-export-btn"><i class="fas fa-download"></i> Export CSV</a>
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Product</a>
    </div>
</div>

<ul class="nav nav-tabs gap-2 d-flex flex-wrap admin-product-index-nav-tabs">
    @php
        $activeParams = request()->only(['search', 'category_id', 'brand_id', 'seller_id', 'product_type', 'stock_filter', 'status', 'featured']);
        $trashParams = array_merge(['trashed' => 1], $activeParams);
    @endphp
    <li class="nav-item">
        <a class="nav-link {{ !request()->has('trashed') ? 'active' : '' }}" href="{{ route('admin.products.index', $activeParams) }}">Active</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->has('trashed') ? 'active' : '' }}" href="{{ route('admin.products.index', $trashParams) }}">Trash ({{ $trashedCount }})</a>
    </li>
    <li class="nav-item search-form ms-lg-auto">
        @include('admin.partials.search-form', [
            'route' => route('admin.products.index'),
            'placeholder' => 'Search products...'
        ])
    </li>
</ul>

{{-- Filter bar --}}
<div class="card border-0 shadow-sm mb-3 admin-product-index-filter-bar">
    <div class="card-body py-3">
        <form action="{{ route('admin.products.index') }}" method="GET" class="row g-2 align-items-end">
            @if(request()->has('trashed'))
                <input type="hidden" name="trashed" value="1">
            @endif
            @if(request()->filled('search'))
                <input type="hidden" name="search" value="{{ request('search') }}">
            @endif
            <div class="col-6 col-md-3 col-lg-2">
                <label class="form-label small text-muted mb-1">Category</label>
                <select name="category_id" class="form-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <label class="form-label small text-muted mb-1">Brand</label>
                <select name="brand_id" class="form-select">
                    <option value="">All Brands</option>
                    @foreach($brands as $br)
                        <option value="{{ $br->id }}" {{ request('brand_id') == $br->id ? 'selected' : '' }}>{{ $br->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <label class="form-label small text-muted mb-1">Seller</label>
                <select name="seller_id" class="form-select">
                    <option value="">All Sellers</option>
                    @foreach($sellers as $se)
                        <option value="{{ $se->id }}" {{ request('seller_id') == $se->id ? 'selected' : '' }}>{{ $se->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <label class="form-label small text-muted mb-1">Section</label>
                <select name="product_type" class="form-select">
                    <option value="all" {{ request('product_type') === 'all' ? 'selected' : '' }}>All Sections</option>
                    @foreach(['none' => 'None', 'new_arrival' => 'New Arrivals', 'trending' => 'Trending', 'best_selling' => 'Best Selling', 'popular' => 'Popular'] as $val => $label)
                        <option value="{{ $val }}" {{ request('product_type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <label class="form-label small text-muted mb-1">Stock</label>
                <select name="stock_filter" class="form-select">
                    <option value="all" {{ request('stock_filter') === 'all' ? 'selected' : '' }}>Any Stock</option>
                    <option value="in_stock" {{ request('stock_filter') === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                    <option value="out_of_stock" {{ request('stock_filter') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                </select>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <label class="form-label small text-muted mb-1">Status</label>
                <select name="status" class="form-select">
                    <option value="">Any Status</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <label class="form-label small text-muted mb-1">Featured</label>
                <select name="featured" class="form-select">
                    <option value="">Any</option>
                    <option value="1" {{ request('featured') === '1' ? 'selected' : '' }}>Featured</option>
                    <option value="0" {{ request('featured') === '0' ? 'selected' : '' }}>Not Featured</option>
                </select>
            </div>
            <div class="col-12 col-md-auto d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary btn-sm steve-btn"><i class="fas fa-filter me-1"></i> Filter</button>
                @if($hasActiveFilters)
                    <a href="{{ route('admin.products.index', request()->has('trashed') ? ['trashed' => 1] : []) }}" class="btn btn-outline-danger btn-sm steve-btn"><i class="fas fa-times me-1"></i> Clear Filters</a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Bulk action form --}}
<form id="bulk-form" method="POST" action="">
    @csrf
    <div id="bulk-inputs" class="d-none"></div>
</form>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="d-flex justify-content-between align-items-center p-2 flex-wrap flex-md-nowrap gap-2">
            <div class="d-flex align-items-center gap-2">
                @if(!request()->has('trashed'))
                    <button type="button" class="btn btn-danger steve-btn btn-sm d-none" id="bulk-delete-btn" data-bulk-action="{{ route('admin.products.bulk-delete') }}" onclick="if(confirm('Move the selected products to trash?')) bulkRun(this)"><i class="fas fa-trash me-1"></i> Delete (<span class="bulk-count">0</span>)</button>
                    <button type="button" class="btn btn-success steve-btn btn-sm d-none" id="bulk-activate-btn" data-bulk-action="{{ route('admin.products.bulk-status') }}" data-field="status" data-value="1" onclick="bulkRunValue(this)"><i class="fas fa-check me-1"></i> Activate (<span class="bulk-count">0</span>)</button>
                    <button type="button" class="btn btn-warning steve-btn btn-sm d-none" id="bulk-deactivate-btn" data-bulk-action="{{ route('admin.products.bulk-status') }}" data-field="status" data-value="0" onclick="bulkRunValue(this)"><i class="fas fa-ban me-1"></i> Deactivate (<span class="bulk-count">0</span>)</button>
                    <button type="button" class="btn btn-outline-primary steve-btn btn-sm d-none" id="bulk-featured-btn" data-bulk-action="{{ route('admin.products.bulk-featured') }}" data-field="featured" data-value="1" onclick="bulkRunValue(this)"><i class="fas fa-star me-1"></i> Featured (<span class="bulk-count">0</span>)</button>
                    <button type="button" class="btn btn-outline-secondary steve-btn btn-sm d-none" id="bulk-unfeatured-btn" data-bulk-action="{{ route('admin.products.bulk-featured') }}" data-field="featured" data-value="0" onclick="bulkRunValue(this)"><i class="far fa-star me-1"></i> Unfeature (<span class="bulk-count">0</span>)</button>
                    <select id="bulk-category-select" class="form-select form-select-sm w-auto d-none">
                        <option value="">Move to category…</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    <button type="button" class="btn btn-primary steve-btn btn-sm d-none" id="bulk-move-btn" data-bulk-action="{{ route('admin.products.bulk-category') }}" onclick="bulkRunCategory(this)"><i class="fas fa-folder-open me-1"></i> Move (<span class="bulk-count">0</span>)</button>
                @else
                    <button type="button" class="btn btn-success steve-btn btn-sm d-none" id="bulk-restore-btn" data-bulk-action="{{ route('admin.products.bulk-restore') }}" onclick="bulkRun(this)"><i class="fas fa-undo me-1"></i> Restore (<span class="bulk-count">0</span>)</button>
                    <button type="button" class="btn btn-danger steve-btn btn-sm d-none" id="bulk-force-btn" data-bulk-action="{{ route('admin.products.bulk-force-delete') }}" onclick="if(confirm('Permanently delete the selected products from trash? This cannot be undone.')) bulkRun(this)"><i class="fas fa-trash-alt me-1"></i> Delete Permanently (<span class="bulk-count">0</span>)</button>
                @endif
                <span class="text-muted small">Show</span>
                <select class="form-select w-auto" onchange="window.location.href=this.value">
                    @php $currentPerPage = request('per_page', '10'); @endphp
                    @foreach([10, 20, 50, 100] as $n)
                        <option value="{{ request()->fullUrlWithQuery(['per_page' => $n, 'page' => null]) }}" {{ $currentPerPage == $n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                    <option value="{{ request()->fullUrlWithQuery(['per_page' => 'all', 'page' => null]) }}" {{ $currentPerPage === 'all' ? 'selected' : '' }}>All</option>
                </select>
                <span class="text-muted small">per page</span>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">
                            <input type="checkbox" class="form-check-input m-0 bulk-select-all" aria-label="Select all on this page">
                        </th>
                        <th>No.</th>
                        <th>Image</th>
                        <th><a href="{{ sortUrl('name', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">
                            Name {!! sortIndicator('name', $sortBy, $sortDir) !!}
                        </a></th>
                        <th><a href="{{ sortUrl('sku', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">
                            SKU {!! sortIndicator('sku', $sortBy, $sortDir) !!}
                        </a></th>
                        <th>Category</th>
                        <th>Seller</th>
                        <th><a href="{{ sortUrl('price', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">
                            Price {!! sortIndicator('price', $sortBy, $sortDir) !!}
                        </a></th>
                        <th>Old Price</th>
                        <th><a href="{{ sortUrl('stock', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">
                            Stock {!! sortIndicator('stock', $sortBy, $sortDir) !!}
                        </a></th>
                        <th>Badge</th>
                        <th>Section</th>
                        <th><a href="{{ sortUrl('rating', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">
                            Rating {!! sortIndicator('rating', $sortBy, $sortDir) !!}
                        </a></th>
                        <th><a href="{{ sortUrl('featured', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">
                            Featured {!! sortIndicator('featured', $sortBy, $sortDir) !!}
                        </a></th>
                        <th><a href="{{ sortUrl('status', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">
                            Status {!! sortIndicator('status', $sortBy, $sortDir) !!}
                        </a></th>
                        <th class="pe-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td class="ps-3">
                            <input type="checkbox" class="form-check-input m-0 bulk-select-row" value="{{ $product->id }}" aria-label="Select product {{ $product->name }}">
                        </td>
                        <td>{{ $products->firstItem() + $loop->index }}</td>
                        <td>
                            <img src="{{ storedImageUrl($product->image, 'assets/images/thumbnails') }}" width="50" height="50" class="admin-image-thumb">
                        </td>
                        <td class="admin-product-name"><a href="{{ route('product', $product->slug) }}" target="_blank">{{ Str::words($product->name, 3, '...') }}</a></td>
                        <td><code>{{ $product->sku ?: '—' }}</code></td>
                        <td>{{ $product->category->name ??  'N/A' }}</td>
                        <td>{{ $product->seller->name ?? 'N/A' }}</td>
                        <td>{{ currency_format($product->price) }}</td>
                        <td>@if($product->old_price) {{ currency_format($product->old_price) }} @else - @endif</td>
                        <td>{{ $product->stock ?? 0 }}</td>
                        <td>@if($product->badge) <span class="badge bg-light text-warning border border-warning-subtle">{{ $product->badge }}</span> @else - @endif</td>
                        <td><span class="badge bg-light text-info border border-info-subtle">{{ str_replace('_', ' ', ucfirst($product->product_type ?? 'none')) }}</span></td>
                        <td>
                            @if($product->rating)
                                <span class="text-warning"><i class="fas fa-star me-1"></i>{{ number_format($product->rating, 1) }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if(!request()->has('trashed'))
                                <form action="{{ route('admin.products.toggle-featured', $product->id) }}" method="POST" class="d-inline featured-status-btn">
                                    @csrf
                                    <button type="submit" class="btn btn-sm border-0 p-0 steve-btn">
                                        <span class="badge admin-clickable {{ $product->featured ? 'bg-warning text-dark' : 'bg-secondary' }}">
                                            @if($product->featured)
                                                <i class="fa-solid fa-star"><span class="visually-hidden">Featured</span></i> Yes
                                            @else
                                                <i class="fa-regular fa-star"><span class="visually-hidden">Not featured</span></i> No
                                            @endif
                                        </span>
                                    </button>
                                </form>
                            @else
                                <span class="badge bg-secondary">-</span>
                            @endif
                        </td>
                        <td>
                            @if(request()->has('trashed'))
                                <span class="badge bg-secondary">Deleted</span>
                            @else
                                <form action="{{ route('admin.products.toggle-status', $product->id) }}" method="POST" class="d-inline featured-status-btn">
                                    @csrf
                                    <button type="submit" class="btn btn-sm border-0 p-0 steve-btn">
                                        <span class="badge admin-clickable {{ $product->status ? 'bg-success' : 'bg-danger' }}">
                                            {{  $product->status ? 'Active' : 'Inactive' }}
                                        </span>
                                    </button>
                                </form>
                            @endif
                        </td>
                        <td class="pe-3 table-action-col">
                            <div class="action-buttons">
                            @if(request()->has('trashed'))
                                <form action="{{ route('admin.products.restore', $product->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="action-btn btn-restore" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Restore"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg></button>
                                </form>
                                <form action="{{ route('admin.products.force-delete', $product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Permanently delete {{ $product->name }}?')">
                                    @csrf @method('DELETE')
                                    <button class="action-btn btn-cancel" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete Permanently"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>
                                </form>
                            @else
                                <a href="{{ route('admin.products.details', $product->id) }}" class="action-btn btn-view" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="View Details"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></a>
                                <form action="{{ route('admin.products.duplicate', $product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Duplicate {{ $product->name }}? A copy (inactive) will be created.')">
                                    @csrf
                                    <button class="action-btn btn-view-live" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Duplicate"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg></button>
                                </form>
                                <a href="{{ route('admin.products.edit', $product->id) }}" class="action-btn btn-edit" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Edit"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></a>
                                <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this product?')">
                                    @csrf @method('DELETE')
                                    <button class="action-btn btn-cancel" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>
                                </form>
                            @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="16" class="text-center py-4 text-muted">{{ request()->has('trashed') ? 'Trash is empty.' : 'No products found.' }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
            <div class="d-flex justify-content-center py-3">
                {{ $products->links('vendor.pagination.gs-pagination') }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    function bulkRun(btn) {
        var form = document.getElementById('bulk-form');
        form.action = btn.getAttribute('data-bulk-action');
        form.submit();
    }

    function bulkRunValue(btn) {
        var form = document.getElementById('bulk-form');
        var existing = form.querySelector('input[data-extra-field]');
        if (existing) {
            existing.remove();
        }
        var inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = btn.getAttribute('data-field');
        inp.value = btn.getAttribute('data-value');
        inp.setAttribute('data-extra-field', '1');
        form.appendChild(inp);
        form.action = btn.getAttribute('data-bulk-action');
        form.submit();
    }

    function bulkRunCategory(btn) {
        var sel = document.getElementById('bulk-category-select');
        if (!sel || !sel.value) {
            toastr.error('Select a destination category first.');
            return;
        }
        var form = document.getElementById('bulk-form');
        var existing = form.querySelector('input[data-extra-field]');
        if (existing) {
            existing.remove();
        }
        var inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = 'category_id';
        inp.value = sel.value;
        inp.setAttribute('data-extra-field', '1');
        form.appendChild(inp);
        form.action = btn.getAttribute('data-bulk-action');
        form.submit();
    }

    (function () {
        var rowChecks = document.querySelectorAll('.bulk-select-row');
        var selectAll = document.querySelector('.bulk-select-all');
        var inputsContainer = document.getElementById('bulk-inputs');
        var btnIds = ['bulk-delete-btn', 'bulk-activate-btn', 'bulk-deactivate-btn', 'bulk-featured-btn', 'bulk-unfeatured-btn', 'bulk-move-btn', 'bulk-restore-btn', 'bulk-force-btn'];
        var moveBtn = document.getElementById('bulk-move-btn');
        var categorySelect = document.getElementById('bulk-category-select');

        function refresh() {
            var selected = Array.prototype.filter.call(rowChecks, function (c) { return c.checked; });
            document.querySelectorAll('.bulk-count').forEach(function (el) { el.textContent = selected.length; });
            inputsContainer.innerHTML = '';
            selected.forEach(function (c) {
                var inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'ids[]';
                inp.value = c.value;
                inputsContainer.appendChild(inp);
            });
            btnIds.forEach(function (id) {
                var el = document.getElementById(id);
                if (el) {
                    el.classList.toggle('d-none', selected.length === 0);
                }
            });
            if (moveBtn && categorySelect) {
                var hasCat = selected.length > 0 && !!categorySelect.value;
                moveBtn.classList.toggle('d-none', !hasCat);
                categorySelect.classList.toggle('d-none', selected.length === 0);
            }
            if (selectAll) {
                selectAll.checked = rowChecks.length > 0 && selected.length === rowChecks.length;
            }
        }

        rowChecks.forEach(function (c) { c.addEventListener('change', refresh); });
        if (selectAll) {
            selectAll.addEventListener('change', function () {
                rowChecks.forEach(function (c) { c.checked = selectAll.checked; });
                refresh();
            });
        }
        if (categorySelect) {
            categorySelect.addEventListener('change', function () {
                if (moveBtn) {
                    moveBtn.classList.toggle('d-none', !categorySelect.value);
                }
            });
        }
        refresh();
    })();
</script>
@endpush
@endsection