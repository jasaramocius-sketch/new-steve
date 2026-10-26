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

<div class="d-flex justify-content-between align-items-center mb-3 gap-2 flex-wrap">
    <div></div>
    <div class="d-flex gap-2">
        <span class="badge bg-danger align-self-center">Out of Stock: {{ $outCount }}</span>
        <span class="badge bg-warning text-dark align-self-center">Low (1-{{ max($threshold, 1) }}): {{ $lowTotal }}</span>
        <a href="{{ route('admin.products.index', ['stock_filter' => 'out_of_stock']) }}" class="btn btn-outline-secondary steve-btn gap-1"><i class="fas fa-box"></i> All Products</a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="d-flex justify-content-between align-items-center p-2 flex-wrap flex-md-nowrap gap-2">
            <form method="GET" action="{{ route('admin.products.stock') }}" class="d-flex align-items-center gap-2 flex-nowrap">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm h-40px" placeholder="Search name or SKU..." style="min-width:200px;">
                <select name="stock_filter" class="form-select form-select-sm w-auto h-40px">
                    <option value="low" {{ request('stock_filter', 'low') === 'low' ? 'selected' : '' }}>Low stock</option>
                    <option value="out_of_stock" {{ request('stock_filter') === 'out_of_stock' ? 'selected' : '' }}>Out of stock</option>
                </select>
                <select name="threshold" class="form-select form-select-sm w-auto h-40px" onchange="this.form.submit()">
                    @foreach([3, 5, 10, 20] as $t)
                        <option value="{{ $t }}" {{ $threshold === $t ? 'selected' : '' }}>Show ≤ {{ $t }}</option>
                    @endforeach
                </select>
                <button class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
            </form>
            <div class="text-muted small">
                Showing {{ $products->firstItem() }}-{{ $products->lastItem() }} of {{ $products->total() }}
            </div>
        </div>

        <form action="{{ route('admin.products.bulk-stock') }}" method="POST" id="stockForm">
            @csrf
            <div class="mb-2 px-2">
                <div class="d-flex align-items-center gap-2">
                    <select name="mode" class="form-select form-select-sm w-auto h-40px" id="stockMode">
                        <option value="add">Add to current stock</option>
                        <option value="set">Set exact stock</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('Apply stock changes to checked products?');">
                        <i class="fas fa-clipboard-check"></i> Apply to Checked
                    </button>
                    <label class="small text-muted mb-0"><input type="checkbox" id="toggleAll"> Select all on page</label>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3"><input type="checkbox" id="selectAll"></th>
                            <th>Product</th>
                            <th>SKU</th>
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
                            <td><span class="badge bg-light text-dark border">{{ $product->sku ?: '—' }}</span></td>
                            <td>{{ $product->category?->name ?? '—' }}</td>
                            <td>
                                <span class="fw-bold {{ $product->stock <= 0 ? 'text-danger' : 'text-warning' }}">{{ $product->stock }}</span>
                            </td>
                            <td style="min-width:110px;">
                                <input type="number" name="editable_qty[]" class="form-control form-control-sm editable-qty" min="0" value="{{ max($product->stock, 0) }}">
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