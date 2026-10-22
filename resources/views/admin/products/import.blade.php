@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-products-import-page', 'pageClass' => 'admin-products-import-page'])
@section('page-title', 'Import Products')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Import Products - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Import Products from CSV</h5>
        <a href="{{ route('admin.products.download-sample-csv') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-download"></i> Download Sample CSV
        </a>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.products.import') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label class="form-label">CSV File *</label>
                <input type="file" name="csv_file" class="form-control @error('csv_file') is-invalid @enderror" accept=".csv,.txt" required>
                @error('csv_file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <div class="form-text">Max 5MB. First row must contain column headers.</div>
            </div>

            <div class="alert alert-info">
                <strong>Supported Columns:</strong>
                <code>id</code> (leave empty for new product, provide ID to update existing),
                <code>name</code> (required),
                <code>sku</code> (leave empty to auto-generate),
                <code>price</code> (required),
                <code>old_price</code>,
                <code>category</code> (matched by name — <strong>auto-created if missing</strong>),
                <code>stock</code>,
                <code>description</code>,
                <code>badge</code>,
                <code>product_type</code> (<code>none</code> / <code>new_arrival</code> / <code>trending</code> / <code>best_selling</code> / <code>popular</code>),
                <code>status</code> (<code>1/0</code> or <code>yes/no</code> or <code>active/inactive</code>),
                <code>featured</code> (<code>1/0</code> or <code>yes/no</code>),
                <code>brand</code> (matched by name — <strong>auto-created if missing</strong>),
                <code>seller</code> (matched by name — <strong>auto-created if missing</strong>),
                <code>year</code>,
                <code>make</code>,
                <code>model</code>,
                <code>image</code> (URL — will be downloaded and converted to WebP),
                <code>gallery_images</code> (pipe-separated URLs — e.g. <code>url1.jpg|url2.jpg|url3.jpg</code>),
                <code>policy_text</code> or <code>buy_return_policy</code> or <code>return_policy</code> (HTML accepted for the product policy tab),
                <code>features</code> or <code>feature_list</code> (newline or pipe-separated list),
                <code>specifications</code> (newline-separated <code>Label::Value</code> pairs),
                <code>reviews</code> or <code>reviews_data</code> (JSON array or <code>name::rating::text|name::rating::text</code>),
                <code>tab_label_1</code>, <code>tab_label_2</code>, <code>tab_label_3</code> (custom tab titles)
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary steve-btn gap-1">
                    <i class="fas fa-upload"></i> Import Products
                </button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-primary">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection
