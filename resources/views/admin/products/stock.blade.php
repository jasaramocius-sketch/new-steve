@extends('admin.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-products-stock-page', 'pageClass' => 'admin-products-stock-page'])
@section('page-title', 'Stock Management')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Stock Management - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')

@php
    $outCount = \App\Models\Product::where('vendor_stock', '<=', 0)->count();
    $lowTotal = \App\Models\Product::whereBetween('vendor_stock', [1, max($threshold, 1)])->count();
@endphp

<div class="products-stock-page-top-header d-flex justify-content-between align-items-center mb-3 gap-2 flex-wrap">
    <div></div>
    <div class="top-header-column d-flex gap-2">
        <span class="badge bg-danger align-self-center">Out of Stock: {{ $outCount }}</span>
        <span class="badge bg-warning text-dark align-self-center">Low (1-{{ max($threshold, 1) }}): {{ $lowTotal }}</span>
        <a href="{{ route('admin.products.index', ['stock_filter' => 'out_of_stock']) }}" class="btn btn-outline-secondary steve-btn gap-1"><i class="fas fa-box"></i> All Products</a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0 products-stock-page-container">
        <div class="products-stock-page-header d-flex justify-content-between align-items-center p-2 flex-wrap flex-md-nowrap gap-2">
            <form method="GET" action="{{ route('admin.products.stock') }}" class="products-stock-page-filters d-flex align-items-center gap-2 flex-nowrap flex-wrap">
                <select name="per_page" class="form-select w-auto h-40px" aria-label="Products per page" onchange="this.form.submit()">
                    @foreach([10, 20, 50, 100] as $count)
                        <option value="{{ $count }}" @selected((int) request('per_page', 20) === $count)>{{ $count }} page</option>
                    @endforeach
                </select>
                <select name="stock_filter" class="form-select w-auto h-40px">
                    <option value="low" {{ request('stock_filter', 'low') === 'low' ? 'selected' : '' }}>Low stock</option>
                    <option value="out_of_stock" {{ request('stock_filter') === 'out_of_stock' ? 'selected' : '' }}>Out of stock</option>
                </select>
                <select name="threshold" class="form-select w-auto h-40px" onchange="this.form.submit()">
                    @foreach([3, 5, 10, 20] as $t)
                        <option value="{{ $t }}" {{ $threshold === $t ? 'selected' : '' }}>Show ≤ {{ $t }}</option>
                    @endforeach
                </select>
                <div class="w-auto">
                    @include('admin.partials.search-form', [
                        'embedded' => true,
                        'placeholder' => 'Search name or Part Number...',
                        'inputClass' => 'form-control h-40px',
                    ])
                </div>
                <select name="mode" class="form-select w-auto h-40px" id="stockMode">
                    <option value="add">Add to current stock</option>
                    <option value="set">Set exact stock</option>
                </select>
                <div class="w-auto">
                    <button type="submit" class="btn steve-btn btn-primary d-flex align-items-center gap-1 w-100" onclick="return confirm('Apply stock changes to checked products?');">
                        <i class="fas fa-clipboard-check"></i> Apply to Checked
                    </button>
                </div>
            </form>
            <div class="products-stock-page-actions">
                <div class="d-flex align-items-center gap-2">
                    <div class="d-flex align-items-center gap-2">
                    
                    </div>
                    
                <label class="small text-muted mb-0 w-auto"><input type="checkbox" id="toggleAll"> Select all on page</label>
                </div>
            </div>
            <!-- <div class="text-muted small">
                Showing {{ $products->firstItem() }}-{{ $products->lastItem() }} of {{ $products->total() }}
            </div> -->
        </div>

        <form action="{{ route('admin.products.bulk-stock') }}" method="POST" id="stockForm">
            @csrf

            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3"><input type="checkbox" id="selectAll"></th>
                            <th>Product</th>
                            <th>Part Number</th>
                            <th>Category</th>
                            <th>Stock</th>
                            <th>New Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                        <tr class="{{ $product->stock <= 0 ? 'table-danger' : 'table-warning' }}">
                            <td class="ps-3">
                                <input type="checkbox" name="product_id[]" value="{{ $product->id }}" class="row-check">
                                <input type="hidden" name="stock_qty[]" value="0">
                            </td>
                            <td>
                                <a href="{{ route('admin.products.details', $product->id) }}" class="text-decoration-none fw-semibold">{{ $product->name }}</a>
                                <div class="small text-muted">#{{ $product->id }}</div>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $product->part_number ?: '—' }}</span></td>
                            <td>{{ $product->category?->name ?? '—' }}</td>
                            <td>
                                <div class="d-flex align-items-center justify-content-center gap-2">
                                    <span class="fw-bold {{ $product->stock <= 0 ? 'text-danger' : 'text-warning' }}">{{ $product->stock }}</span>
                                </div>
                            </td>
                            <td style="width:7%;">
                                <input type="number" name="editable_qty[]" class="form-control editable-qty" min="0" value="{{ max($product->stock, 0) }}">
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="100%" class="text-center py-4 text-muted">No products match this stock filter.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>

        @if ($products->hasPages())
            <div class="p-2 border-top">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>

@endsection

@push('page-builder-css')
<style>
    #admin-products-stock-page .products-stock-page-container {
        container-type: inline-size;
    }
    #admin-products-stock-page .products-stock-page-filters .form-select-wrapper select{
        min-width: 200px !important;
    }

    @container (max-width: 1100px) {
        #admin-products-stock-page .products-stock-page-header {
            flex-wrap: wrap !important;
            align-items: stretch !important;
        }

        #admin-products-stock-page .products-stock-page-filters,
        #admin-products-stock-page .products-stock-page-actions {
            flex: 1 1 100%;
            min-width: 0;
        }

        #admin-products-stock-page .products-stock-page-filters {
            flex-wrap: wrap !important;
        }

        #admin-products-stock-page .products-stock-page-actions > div,
        #admin-products-stock-page .products-stock-page-actions > div > div {
            flex-wrap: wrap;
        }
        #admin-products-stock-page .products-stock-page-filters .form-select-wrapper{
            width: auto;
        }
    }

    @container (max-width: 767.98px) {
        #admin-products-stock-page .products-stock-page-filters > .form-select {
            flex: 1 1 calc(50% - .5rem);
            min-width: 100px;
        }

        #admin-products-stock-page .products-stock-page-filters > .input-group {
            flex: 1 1 100%;
            min-width: 0 !important;
        }

        #admin-products-stock-page .products-stock-page-filters > .input-group .form-control {
            min-width: 0;
        }

        /* #admin-products-stock-page .products-stock-page-actions {
            padding-right: .5rem !important;
            padding-left: .5rem !important;
        } */

        #admin-products-stock-page .products-stock-page-actions .form-select {
            max-width: 100%;
        }

        #admin-products-stock-page .products-stock-page-actions label {
            flex: 1 1 100%;
        }
        #admin-products-stock-page .products-stock-page-filters{
            display: grid !important;
            grid-template-columns: 1fr 1fr;
        }
        #admin-products-stock-page .products-stock-page-filters .form-select-wrapper select{
            width: 100% !important;
        }
    }
    .products-stock-page-top-header .top-header-column {
            display: grid !important;
            grid-template-columns: 1fr 1fr 1fr;
            justify-items: start;
    }
    @media (max-width: 767.98px) {
        .products-stock-page-top-header .top-header-column {
            grid-template-columns: 1fr 1fr;
        }
        .products-stock-page-top-header {
            justify-content: start !important;
        }
    }
    @media (max-width: 552px) {
        #admin-products-stock-page .products-stock-page-filters{
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var selectAll = document.getElementById('selectAll');
    var toggleAll = document.getElementById('toggleAll');
    var form = document.getElementById('stockForm');
    var rows = form ? form.querySelectorAll('.row-check') : document.querySelectorAll('.row-check');

    function syncSelectAll() {
        if (!rows || rows.length === 0) {
            if (selectAll) selectAll.checked = false;
            if (toggleAll) toggleAll.checked = false;
            return;
        }
        var allChecked = Array.from(rows).every(function (r) { return r.checked; });
        if (selectAll) selectAll.checked = allChecked;
        if (toggleAll) toggleAll.checked = allChecked;
    }
    function setAll(checked) {
        Array.from(rows).forEach(function (r) { r.checked = !!checked; });
        syncSelectAll();
    }
    if (selectAll) {
        selectAll.addEventListener('change', function () { setAll(selectAll.checked); });
    }
    if (toggleAll) {
        toggleAll.addEventListener('change', function () { setAll(toggleAll.checked); });
    }
    Array.from(rows).forEach(function (r) {
        r.addEventListener('change', syncSelectAll);
    });
    syncSelectAll();

    // Sync editable qty -> hidden stock_qty on submit
    document.getElementById('stockForm').addEventListener('submit', function (e) {
        var form = this;
        var checked = Array.from(rows).filter(function (r) { return r.checked; });
        if (checked.length === 0) {
            e.preventDefault();
            alert('Select at least one product.');
            return;
        }
        var indices = checked.map(function (r) {
            return Array.prototype.indexOf.call(rows, r);
        });
        var editables = form.querySelectorAll('.editable-qty');
        var hidden = form.querySelectorAll('input[name="stock_qty[]"]');
        hidden.forEach(function (h, i) {
            if (indices.indexOf(i) !== -1) {
                h.value = editables[i] ? editables[i].value : 0;
            } else {
                h.value = 0;
                h.disabled = true;
            }
        });
    });
});
</script>
@endpush