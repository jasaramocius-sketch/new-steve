@extends('layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'categories-page', 'pageClass' => 'categories-page'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'All Categories - ' . config('app.name', 'StAutoparts'),
        'metaTitle' => 'All Categories | ' . config('app.name', 'StAutoparts'),
        'metaDescription' => 'Browse all auto parts categories at ' . config('app.name', 'StAutoparts') . '.',
        'robots' => 'index, follow',
    ])
@endsection

@section('content')
<style>
  .category-toolbar .view-btn {
    width: 40px;
    /* height: 40px; — using padding instead */
    border: 1px solid var(--primary);
    background: #fff;
    color: var(--primary);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    padding: 9px 18px;
  }
  .category-toolbar .view-btn.active {
    background: var(--primary);
    color: #fff;
  }
</style>

<!-- Banner Hero Section -->
<section class="gs-breadcrumb-section"
    style="background-image: url('{{ asset('/assets/images/1724480495Imagexxxxxpng.png') }}');
    background-size: cover; background-position: center;">

  <div class="container">
    <div class="content-wrapper">
      <h2 class="breadcrumb-title">All Categories</h2>
      <ul class="bread-menu">
        <li><a href="{{ route('home') }}">Home</a></li>
        <li style="color: var(--primary)">Categories</li>
      </ul>
    </div>
  </div>
</section>
<section class="product-category py-120 shop-page-product-items" style="background-color: #F9F8F8;">
<div class="container">

    <!-- Toolbar -->
    <div class="category-toolbar mb-4 gap-3 d-grid">
        <div class="d-flex justify-content-between flex-md-row gap-2 flex-sm-row category-toolbar-top">
            <div class="w-auto">
                @include('admin.partials.search-form', [
                    'route' => route('categories.index'),
                    'placeholder' => 'Search category...'
                ])
            </div>
            <div class="w-auto">
                <div class="toolbar-right category-toolbar-right d-flex align-items-center justify-content-md-end gap- flex-wrap">
                    <div class="d-flex align-items-center gap-2">
                        <h5 class="mb-0 small fw-medium">Sort by</h5>
                        <form method="GET">
                            <input type="hidden" name="search" value="{{ request('search') }}">
                            <input type="hidden" name="page" value="">
                            <select class="form-select" name="sort" onchange="this.form.submit()">
                                <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Latest</option>
                                <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest</option>
                                <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Name A-Z</option>
                            </select>
                        </form>
                    </div>
                    <div class="d-flex align-items-center gap-2" style="padding-left:10px;">
                        @include('partials.grid-list-toggle')
                </div>
            </div>
        </div>
    </div>

    <!-- Categories Grid -->
    <div id="categoryContainer" class="category-container">

    @foreach($categories as $category)

    <div class="category-grid-item">
        <div class="category-card">

            <div class="category-image">
                <a href="{{ route('category', $category->slug) }}">
                    @php $categoryImage = $category->getDisplayImagePath(); @endphp
                    {!! imgTag($categoryImage, $category->name) !!}
                </a>
            </div>

            <div class="category-content">
                <a href="{{ route('category', $category->slug) }}" class="text-dark">
                    <h5>{{ $category->name }}</h5>
                </a>

                <div class="category-stats">
                    <span class="badge bg-primary">
                        {{ $category->total_products_count }} Products
                    </span>

                    @if($category->children->count() > 0)
                    <span class="badge bg-secondary subcategory-count-badge">
                        {{ $category->children->count() }} Sub Categories
                    </span>
                    @endif
                </div>

                @if($category->children->count() > 0)
                <div class="subcategory-list mt-2">
                        <ul class="list-unstyled mb-0" style="font-size:13px;">
                            @foreach($category->children->take(3) as $child)
                                <li class="mb-1 d-flex align-items-center">
                                    <a href="{{ route('subcategory', ['parent' => $category->slug, 'child' => $child->slug]) }}" class="text-decoration-none d-flex align-items-center gap-2 subcategory-link" style="color:#1f0300;">
                                        {!! imgTag($child->getDisplayImagePath(), $child->name, 'subcategory-thumb-img') !!}
                                        <span>{{ $child->name }}</span>
                                    </a>
                                    <span class="text-muted ms-1">({{ $child->total_products_count }})</span>
                                </li>
                            @endforeach
                        </ul>
                        @if($category->children->count() > 3)
                        <a href="{{ route('category', $category->slug) }}" class="subcategory-view-more d-inline-flex align-items-center gap-1  fw-medium mt-1 a-tag-hover-color">
                            View More <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                        @endif
                </div>
                @endif
            </div>

            @if(isset($category->preview_products) && $category->preview_products->count() > 0)
            <div class="category-products">
                <!-- <h6 class="category-products-title">Products</h6> -->
                <div class="category-products-list">
                    @foreach($category->preview_products as $previewProduct)
                    <a href="{{ route('product', $previewProduct->slug) }}" class="category-product-item">
                        <span class="category-product-img">
                            {!! imgTag(storedPath($previewProduct->image, 'assets/images/thumbnails'), $previewProduct->name) !!}
                        </span>
                        <span class="category-product-info">
                            <span class="category-product-name">{{ $previewProduct->name }}</span>
                            <span class="category-product-price">
                                {{ currency_format(buyerPrice($previewProduct->price)) }}
                                @if(!empty($previewProduct->old_price) && $previewProduct->old_price > $previewProduct->price)
                                <del class="category-product-old-price">{{ currency_format(buyerPrice($previewProduct->old_price)) }}</del>
                                @endif
                            </span>
                        </span>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>

    @endforeach

</div>

</div>
</section>
<script>
(function() {
    function applyCategoriesLayout(layout) {
        var container = document.getElementById('categoryContainer');
        if (!container) return;

        if (layout === 'list') {
            document.querySelectorAll('[data-layout="list"]').forEach(function(b) { b.classList.add('active'); });
            document.querySelectorAll('[data-layout="grid"]').forEach(function(b) { b.classList.remove('active'); });
            container.classList.add('categories-list-view');
        } else {
            document.querySelectorAll('[data-layout="grid"]').forEach(function(b) { b.classList.add('active'); });
            document.querySelectorAll('[data-layout="list"]').forEach(function(b) { b.classList.remove('active'); });
            container.classList.remove('categories-list-view');
        }
    }

    var saved = localStorage.getItem('categories_layout');
    applyCategoriesLayout(saved || 'grid');

    function forceGridOnMobile() {
      if (window.innerWidth <= 767) {
        applyCategoriesLayout('grid');
      }
    }
    forceGridOnMobile();
    window.addEventListener('resize', forceGridOnMobile);

    document.querySelectorAll('[data-layout="grid"]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            bootstrap.Tooltip.getInstance(this)?.hide();
            this.blur();
            applyCategoriesLayout('grid');
            localStorage.setItem('categories_layout', 'grid');
        });
    });
    document.querySelectorAll('[data-layout="list"]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            bootstrap.Tooltip.getInstance(this)?.hide();
            this.blur();
            applyCategoriesLayout('list');
            localStorage.setItem('categories_layout', 'list');
        });
    });

    // Disable grid/list tooltips on devices without hover support
    document.addEventListener('DOMContentLoaded', function() {
        if (window.matchMedia && window.matchMedia('(hover: none)').matches) {
            document.querySelectorAll('[data-layout="grid"], [data-layout="list"]').forEach(function(el) {
                var tip = bootstrap.Tooltip.getInstance(el);
                if (tip) tip.disable();
            });
        }
    });
})();
</script>

<style>
#categoryContainer.categories-list-view .category-card .category-image {
    flex: 0 0 24%;
    border-right:1px solid #eee;
}

#categoryContainer {
  box-sizing: border-box;
  width: 100%;
  max-width: 100%;
  margin-right: auto;
  margin-left: auto;
  overflow-x: hidden;  /* Safety fallback to stop horizontal scroll */
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 24px;
}
@media (max-width: 1199px) {
  #categoryContainer {
    grid-template-columns: repeat(3, 1fr);
  }
}
@media (max-width: 767px) {
  #categoryContainer {
    grid-template-columns: repeat(2, 1fr);
  }
}
@media (max-width: 576px) {
  #categoryContainer {
    grid-template-columns: 1fr;
  }
}
#categoryContainer.categories-list-view {
  grid-template-columns: 1fr;
}
.category-products .category-product-item:hover .category-product-name{
    color: var(--primary);
}
.subcategory-list .subcategory-thumb-img {
    width: 38px;
    height: 38px;
    flex: 0 0 38px;
    object-fit: cover;
    border-radius: 6px;
    border: 1px solid #eee;
    background: #fff;
}
.subcategory-list .subcategory-link:hover span {
    color: var(--primary);
}
.subcategory-list .subcategory-view-more {
    color: var(--primary);
    font-size: 13px;
}

/* #categoryContainer.row .col-lg-3{
    padding: 0;
} */
@media (max-width: 767px) {
    .category-toolbar .view-btn {
        display: none !important;
    }}
@media (max-width: 576px) {
    #categoryContainer.categories-list-view .category-card {
        display: block !important;
    }
    #categoryContainer.categories-list-view .category-card .category-image {
        flex: none !important;
    }
    #categoryContainer.categories-list-view .category-card .category-content {
        flex: none !important;
    }
}
</style>

@endsection