@extends('layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'brands-page', 'pageClass' => 'brands-page'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'All Brands - ' . config('app.name', 'StAutoparts'),
        'metaTitle' => 'All Brands | ' . config('app.name', 'StAutoparts'),
        'metaDescription' => 'Browse all auto parts brands at ' . config('app.name', 'StAutoparts') . '.',
        'robots' => 'index, follow',
    ])
@endsection

@section('content')

<!-- Banner Hero Section -->
<section class="gs-breadcrumb-section"
    style="background-image: url('{{ asset('/assets/images/1724480495Imagexxxxxpng.png') }}');
    background-size: cover; background-position: center;">

  <div class="container">
    <div class="content-wrapper">
      <h2 class="breadcrumb-title">All Brands</h2>
      <ul class="bread-menu">
        <li><a href="{{ route('home') }}">Home</a></li>
        <li><a href="{{ route('brands') }}">Brands</a></li>
      </ul>
    </div>
  </div>
</section>

<div class="container mt-5 mb-5">

    <!-- Toolbar -->
    <div class="category-toolbar mb-4">
        <div class="row align-items-center">
            <div class="col-md-4">
                @include('admin.partials.search-form', [
                    'route' => route('brands'),
                    'placeholder' => 'Search brands...'
                ])
            </div>
            <div class="col-md-8">
                <div class="toolbar-right">
                    <span class="sort-label">Sort by:</span>
                    <form method="GET">
                        <input type="hidden" name="search" value="{{ $search }}">
                        <input type="hidden" name="page" value="">
                        <select class="form-select sort-select"
                                name="sort"
                                onchange="this.form.submit()">
                            <option value="latest" {{ $sort == 'latest' ? 'selected' : '' }}>Latest</option>
                            <option value="oldest" {{ $sort == 'oldest' ? 'selected' : '' }}>Oldest</option>
                            <option value="name" {{ $sort == 'name' ? 'selected' : '' }}>Name A-Z</option>
                        </select>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Brands Grid -->
    <div class="row">
    @forelse($brands as $brand)
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="category-card">
            <div class="category-image">
                <a href="{{ route('shop', ['brand' => $brand->slug]) }}">
                    @if($brand->image)
                        {!! imgTag(storedPath($brand->image, 'assets/images/brands'), $brand->name, '', 'style="width:100%;height:200px;object-fit:cover;"') !!}
                    @else
                        <div style="width:100%;height:200px;background:#f0f0f0;display:flex;align-items:center;justify-content:center;">
                            <i class="fas fa-tag fa-3x text-muted"></i>
                        </div>
                    @endif
                </a>
            </div>
            <div class="category-content">
                <a href="{{ route('shop', ['brand' => $brand->slug]) }}" class="text-dark">
                    <h5>{{ $brand->name }}</h5>
                </a>
                <div class="category-stats">
                    <span class="badge bg-primary">
                        {{ $brand->products_count }} Products
                    </span>
                </div>
                @if($brand->description)
                <p class="mt-2" style="font-size:13px;color:#666;">{!! Str::limit(strip_tags($brand->description), 100) !!}</p>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="col-12 text-center py-5">
        <i class="fas fa-tag fa-3x text-muted mb-3"></i>
        <p class="text-muted">No brands found.</p>
    </div>
    @endforelse
    </div>

    @if($brands->hasPages())
    <div class="d-flex justify-content-center mt-5 shop-page-pagination">
        {{ $brands->links('pagination::gs-pagination') }}
    </div>
    @endif

</div>

@endsection
