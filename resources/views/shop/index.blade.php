@extends('layouts.app')
@push('jquery-ui-css')
    <link rel="preload" href="{{ asset('assets/front/css/jquery-ui.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
@endpush
@push('jquery-ui-js')
    <script src="{{ asset('assets/front/js/jquery-ui.js') }}" defer></script>
@endpush
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'shop-page', 'pageClass' => 'shop-page'])
@php $shopTitle = isset($pageTitle) ? $pageTitle : (!empty($currentBrand) ? $currentBrand->name : (isset($currentChildcategory) ? $currentChildcategory->name : (isset($currentSubcategory) ? $currentSubcategory->name : (isset($currentCategory) ? $currentCategory->name : 'Shop')))); $shopMeta = (isset($page) && $page->meta_title) ? $page->meta_title : null; $shopDesc = (isset($page) && $page->meta_description) ? $page->meta_description : null; @endphp
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => $shopMeta ?: ($shopTitle . ' - StAutoparts'),
        'metaTitle' => $shopMeta ?: ($shopTitle . ' | StAutoparts'),
        'metaDescription' => $shopDesc ?: ($shopTitle . ' — browse auto spare parts and accessories at StAutoparts.'),
        'robots' => 'index, follow',
    ])
@endsection

@section('content')
<style>
  .gs-product-sidebar-wrapper .price-range .ui-slider-handle {
    background-color: #ffffff !important;
    border: 2px solid var(--primary) !important;
    outline: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  }
  .gs-product-sidebar-wrapper .price-range .ui-slider-handle:hover,
  .gs-product-sidebar-wrapper .price-range .ui-slider-handle.ui-state-active {
    background-color: var(--primary) !important;
  }
  .product-nav-wrapper .btn-wrapper .view-btn {
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
  .filter-select option:hover {
    background-color: var(--primary) !important;
    color: #fff;
  }
  .product-nav-wrapper .btn-wrapper .view-btn.active {
    background: var(--primary);
    color: #fff;
  }
  .recent-post-item:hover .recent-post-content a {
    color: var(--primary) !important;
  }
  /* Accordion +/- toggle */
  .cat-toggle-btn .fa-minus { display: none; }
  /* .cat-toggle-btn .fa-plus { display: inline; } */
  .cat-toggle-btn:not(.collapsed) .fa-minus { display: inline; }
  /* .cat-toggle-btn:not(.collapsed) .fa-plus lives in custom.css */
  /* Active filter chips */
  .active-filter-chips-label {
    font-size: 13px;
    font-weight: 600;
    color: #6b7280;
  }
  
  .active-filter-chips form.ms-auto .btn:hover {
    background-color: transparent !important;
    border-color: transparent !important;
    color: var(--primary) !important;
  }
  .filter-chip {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 4px 8px 4px 12px;
    background: #fff7ed;
    border: 1px solid var(--primary);
    border-radius: 20px;
    font-size: 13px;
  }
  .filter-chip-label {
    color: #6b7280;
    font-weight: 500;
  }
  .filter-chip-value {
    color: var(--primary);
    font-weight: 600;
  }
  .filter-chip-clear {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    color: #fff;
    background: var(--primary);
    font-size: 13px;
    text-decoration: none;
  }
  .filter-chip-clear:hover {
    background: #c53030;
    color: #fff;
  }
</style>

<!-- Banner Hero Section -->
<section class="gs-breadcrumb-section" style="background-image: url('{{ asset('assets/images/1724480495Imagexxxxxpng.png') }}'); background-size: cover; background-position: center;">
  <div class="container">
    <div class="content-wrapper">
      <h2 class="breadcrumb-title">
        @if(isset($pageTitle))
          {{ $pageTitle }}
        @elseif(!empty($currentBrand))
          {{ $currentBrand->name }}
        @elseif(isset($currentChildcategory))
          {{ $currentChildcategory->name }}
        @elseif(isset($currentSubcategory))
          {{ $currentSubcategory->name }}
        @elseif(isset($currentCategory))
          {{ $currentCategory->name }}
        @else
          Shop
        @endif
      </h2>
      <ul class="bread-menu">
        <li><a href="{{ route('home') }}">Home</a></li>

        @if(!empty($currentBrand))
            <li><a href="{{ route('brands') }}">Brands</a></li>
            <li style="color: var(--primary);">{{ $currentBrand->name }}</li>
        @else
            @if(isset($currentCategory) || isset($currentSubcategory) || isset($currentChildcategory))
                <li><a href="{{ route('shop') }}">Shop</a></li>
            @else
                <li style="color: var(--primary);">Shop</li>
            @endif

            @if(isset($currentCategory))
                @if(isset($currentSubcategory) || isset($currentChildcategory))
                    <li>
                        <a href="{{ route('category', ['slug' => $currentCategory->slug]) }}">
                            {{ $currentCategory->name }}
                        </a>
                    </li>
                @else
                    <li style="color: var(--primary);">{{ $currentCategory->name }}</li>
                @endif
            @endif

            @if(isset($currentSubcategory))
                @if(isset($currentChildcategory))
                    <li>
                        <a href="{{ route('category', ['slug' => $currentSubcategory->slug]) }}">
                            {{ $currentSubcategory->name }}
                        </a>
                    </li>
                @else
                    <li style="color: var(--primary);">{{ $currentSubcategory->name }}</li>
                @endif
            @endif

            @if(isset($currentChildcategory))
                <li style="color: var(--primary);">{{ $currentChildcategory->name }}</li>
            @endif
        @endif
      </ul>
    </div>
  </div>
</section>

<!-- Product Category Listing Section -->
<section class="product-category py-120 shop-page-product-items" style="background-color: #F9F8F8;">
  <div class="container">
    <div class="row g-4">

      <!-- Sidebar -->
      <div class="col-lg-3">
        <div class="shop-sidebar-wrapper">
        <!-- <div class="btn-wrapper d-flex gap-2 sidebar-grid-list-view-wrapper d-lg-none">
            @include('partials.grid-list-toggle')
        </div> -->
        </div>
        <div class="shop-sidebar-overlay"></div>
        <div class="gs-product-sidebar-wrapper">
          <div class="shop-sidebar-close-div">
            <button class="shop-sidebar-close bg-transparent p-2 steve-btn justify-content-end" type="button" aria-label="Close filters">
              <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
              </svg>
              <!-- <span class="ms-1" style="font-size:14px;">Close</span> -->
            </button>
          </div>

          <!-- Categories Widget -->
          @php
            $isCategoryPage = isset($currentCategory) || isset($currentSubcategory) || isset($currentChildcategory);
          @endphp
          <div class="single-product-widget shadow-sm rounded border">
            <h5 class="widget-title">
              <button type="button" class="widget-collapse-toggle collapsed" data-bs-toggle="collapse" data-bs-target="#sidebarCategoriesCollapse" aria-expanded="false" aria-controls="sidebarCategoriesCollapse">
                <span>Categories</span>
                <i class="fa-solid fa-chevron-down widget-collapse-icon" aria-hidden="true"></i>
              </button>
            </h5>
            <div class="collapse show" id="sidebarCategoriesCollapse">
            <div class="product-cat-widget">
              <ul class="list-unstyled mb-0 gap-1 d-flex flex-column">
                @if(isset($currentCategory))
                <li class="mb-3 pb-2 border-bottom">
                  <a href="{{ route('shop') }}" class="text-decoration-none fw-600" style="color: #1f0300; font-size:15px;">
                    All Categories
                  </a>
                </li>
                @endif
                @foreach($categoryTree as $topCat)
                  @php
                    $isTopActive = isset($currentCategory) && $currentCategory->id === $topCat->id;
                  @endphp
                  @if(!isset($currentCategory) || $isTopActive)
                  <li class="main-list mb-1 gap-1 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center gap-3">
                      @if($topCat->descendant_count > 0 || $isTopActive)
                        <a href="{{ route('category', ['slug' => $topCat->slug]) }}" class="text-decoration-none flex-grow-1 primary-a-tag-text-hover" style="color: {{ $isTopActive ? 'var(--primary)' : '#1f0300' }}; font-weight: {{ $isTopActive ? '600' : '400' }};">
                          {{ $topCat->name }}
                          <span class="text-muted" style="font-size: 13px; font-weight: 400;">({{ $topCat->descendant_count }})</span>
                        </a>
                      @else
                        <span class="flex-grow-1 cat-no-products">
                          {{ $topCat->name }}
                          <span class="text-muted" style="font-size: 13px; font-weight: 400;">(0)</span>
                        </span>
                      @endif
                      @if(count($topCat->children) > 0)
                        <button class="btn p-0 border-0 cat-toggle-btn {{ $isTopActive ? '' : 'collapsed' }} steve-btn" data-bs-toggle="collapse" data-bs-target="#cat_{{ $topCat->id }}">
                          <i class="fa-solid fa-plus" style="font-size: 9px;"></i>
                          <i class="fa-solid fa-minus" style="font-size: 9px;"></i>
                        </button>
                      @endif
                    </div>

                    @if(count($topCat->children) > 0)
                      <ul id="cat_{{ $topCat->id }}" class="collapse ms-3 mt-0 list-unstyled {{ $isTopActive ? 'show' : '' }}">
                        @foreach($topCat->children as $subCat)
                          @php
                            $isSubActive = isset($currentSubcategory) && $currentSubcategory->id === $subCat->id;
                            $showSubChildren = $isSubActive;
                          @endphp
                          <li class="mb-2 gap-1 d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center gap-3">
                              @if($subCat->descendant_count > 0 || $isSubActive)
                                <a href="{{ route('category', ['slug' => $subCat->slug]) }}" class="text-decoration-none flex-grow-1 primary-a-tag-text-hover" style="color: {{ $isSubActive ? 'var(--primary)' : '#1f0300' }}; font-weight: {{ $isSubActive ? '600' : '400' }};">
                                  {{ $subCat->name }}
                                  <span class="text-muted" style="font-size: 12px; font-weight: 400;">({{ $subCat->descendant_count }})</span>
                                </a>
                              @else
                                <span class="flex-grow-1 cat-no-products">
                                  {{ $subCat->name }}
                                  <span class="text-muted" style="font-size: 12px; font-weight: 400;">(0)</span>
                                </span>
                              @endif
                              @if(count($subCat->children) > 0)
                                <button class="btn p-0 border-0 cat-toggle-btn {{ $showSubChildren ? '' : 'collapsed' }} steve-btn" data-bs-toggle="collapse" data-bs-target="#cat_{{ $subCat->id }}">
                                  <i class="fa-solid fa-plus" style="font-size: 9px;"></i>
                                  <i class="fa-solid fa-minus" style="font-size: 9px;"></i>
                                </button>
                              @endif
                            </div>

                            @if(count($subCat->children) > 0)
                              <ul id="cat_{{ $subCat->id }}" class="collapse ms-3 mt-0 list-unstyled {{ $showSubChildren ? 'show' : '' }}">
                                @foreach($subCat->children as $childCat)
                                  @php
                                    $isChildActive = isset($currentChildcategory) && $currentChildcategory->id === $childCat->id;
                                  @endphp
                                  <li class="my-1 gap-1 d-flex flex-column">
                                    @if($childCat->descendant_count > 0 || $isChildActive)
                                      <a href="{{ route('category', ['slug' => $childCat->slug]) }}" class="text-decoration-none a-tag-text-hover" style="color: {{ $isChildActive ? 'var(--primary)' : '#1f0300' }}; font-weight: {{ $isChildActive ? '600' : '400' }};">
                                        {{ $childCat->name }}
                                        <span class="text-muted" style="font-size: 12px; font-weight: 400;">({{ $childCat->descendant_count }})</span>
                                      </a>
                                    @else
                                      <span class="cat-no-products">
                                        {{ $childCat->name }}
                                        <span class="text-muted" style="font-size: 12px; font-weight: 400;">(0)</span>
                                      </span>
                                    @endif
                                  </li>
                                @endforeach
                              </ul>
                            @endif
                          </li>
                        @endforeach
                      </ul>
                    @endif
                  </li>
                  @endif
                @endforeach
              </ul>
            </div>
            </div>
          </div>

          <!-- Price Filter Widget -->
          <div class="single-product-widget shadow-sm rounded border">
            <h5 class="widget-title">Price Range
              <!-- <button type="button" class="widget-collapse-toggle" data-bs-toggle="collapse" data-bs-target="#sidebarPriceCollapse" aria-expanded="false" aria-controls="sidebarPriceCollapse">
                <span>Price Range</span>
                <i class="fa-solid fa-chevron-down widget-collapse-icon" aria-hidden="true"></i>
              </button> -->
            </h5>
            <form id="price-filter-form" action="{{ url()->current() }}" method="GET">
              @foreach(request()->except(['min_price', 'max_price', 'page']) as $name => $value)
                @if(is_array($value))
                  @foreach($value as $item)
                    <input type="hidden" name="{{ $name }}[]" value="{{ $item }}">
                  @endforeach
                @else
                  <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                @endif
              @endforeach
              <div class="price-range">
                <div id="price-slider" class="mb-3" style="margin-top: 15px;"></div>
                @php
                    $rate = config('currencies.' . session('currency', 'USD') . '.rate', 1);
                    $sliderMin = (int) floor(($minProductPrice ?? 0) * $rate);
                    $sliderMax = (int) ceil(($maxProductPrice ?? 1000) * $rate);
                    $hasAppliedPrice = request()->filled('min_price') || request()->filled('max_price');
                @endphp
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <span class="text-muted" style="font-size: 14px;">Range:</span>
                  <span id="price-range-label" style="font-weight: 600; color: #1f0300;">{{ $currencySymbol }}{{ number_format($sliderMin, 2) }} - {{ $currencySymbol }}{{ number_format($sliderMax, 2) }}</span>
                </div>
                <input type="hidden" id="price-min" name="min_price" value="{{ request('min_price', $sliderMin) }}">
                <input type="hidden" id="price-max" name="max_price" value="{{ request('max_price', $sliderMax) }}">
                <input type="hidden" id="price-currency-symbol" value="{{ $currencySymbol }}">
                <input type="hidden" id="price-slider-min" value="{{ $sliderMin }}">
                <input type="hidden" id="price-slider-max" value="{{ $sliderMax }}">
                <div class="price-range-actions apply-clear-action-btn d-flex gap-2">
                  <button type="submit" class="btn btn-sm w-100 text-white steve-btn" id="apply-price-filter" {{ $hasAppliedPrice ? '' : 'disabled' }}>Apply Filter</button>
                  @if(request()->filled('min_price') || request()->filled('max_price'))
                    <button type="button" class="btn btn-sm w-100 text-white steve-btn" id="clear-price-filter">Clear</button>
                  @endif
                </div>
              </div>
            </form>
            <div class="collapse" id="sidebarPriceCollapse">
            
            </div>
          </div>

          <!-- Vehicle Filter Widgets (Year, Make, Model) -->
          <div class="single-product-widget shadow-sm rounded border mb-4">
            <h5 class="widget-title mb-4">
              <button type="button" class="widget-collapse-toggle" data-bs-toggle="collapse" data-bs-target="#sidebarVehicleCollapse" aria-expanded="true" aria-controls="sidebarVehicleCollapse">
                <span>Vehicle</span>
                <i class="fa-solid fa-chevron-down widget-collapse-icon" aria-hidden="true"></i>
              </button>
            </h5>
            <div class="collapse show" id="sidebarVehicleCollapse">
            <form id="vehicle-filter-form" action="{{ url()->current() }}" class="d-flex flex-column gap-2" method="GET" class="vehicle-filter-form">
            @foreach(request()->except(['brand', 'year', 'make', 'model', 'page']) as $name => $value)
              @if(is_array($value))
                @foreach($value as $item)
                  <input type="hidden" name="{{ $name }}[]" value="{{ $item }}">
                @endforeach
              @else
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
              @endif
            @endforeach            
              @if($years->isNotEmpty())
                <span class="shop-page-widget-title">Year</span>
                <select name="year" class="form-select filter-select" id="filter-year">
                  <option value="">All Years</option>
                  @foreach($years as $year)
                    <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                  @endforeach
                </select>
              @endif
                <span class="shop-page-widget-title">Make</span>
                <select name="make" class="form-select filter-select" id="filter-make">
                  <option value="">All Makes</option>
                  @foreach($makes as $make)
                    <option value="{{ $make }}" {{ request('make') == $make ? 'selected' : '' }}>{{ $make }}</option>
                  @endforeach
                </select>
                <span class="shop-page-widget-title">Model</span>
                <select name="model" class="form-select filter-select" id="filter-model">
                  <option value="">All Models</option>
                  @foreach($models as $model)
                    <option value="{{ $model }}" {{ request('model') == $model ? 'selected' : '' }}>{{ $model }}</option>
                  @endforeach
                </select>
                <div class="vehicle-filter-form-actions apply-clear-action-btn d-flex flex-row gap-2">
                <button type="submit" class="btn btn-sm w-100 text-white steve-btn" id="apply-vehicle-filter" {{ (request()->filled('year') || request()->filled('make') || request()->filled('model')) ? '' : 'disabled' }}>Apply Filter</button>
                @if(request()->filled('year') || request()->filled('make') || request()->filled('model'))
                  <a href="{{ url()->current() }}" class="btn btn-sm w-100 text-white steve-btn">Clear Filter</a>
                @endif
                </div>
              

            <!-- @if($makes->isNotEmpty())
              <div class="single-product-widget shadow-sm rounded border mb-4">
                <span class="shop-page-widget-title">Make</span>
                <select name="make" class="form-select filter-select" id="filter-make">
                  <option value="">All Makes</option>
                  @foreach($makes as $make)
                    <option value="{{ $make }}" {{ request('make') == $make ? 'selected' : '' }}>{{ $make }}</option>
                  @endforeach
                </select>
              </div>
            @endif

            @if($models->isNotEmpty())
              <div class="single-product-widget shadow-sm rounded border mb-2">
                <span class="shop-page-widget-title">Model</span>
                <select name="model" class="form-select filter-select" id="filter-model">
                  <option value="">All Models</option>
                  @foreach($models as $model)
                    <option value="{{ $model }}" {{ request('model') == $model ? 'selected' : '' }}>{{ $model }}</option>
                  @endforeach
                </select>
              </div>
            @endif
            <div class="vehicle-filter-form-actions apply-clear-action-btn d-flex flex-row gap-2 mt-3">
            <button type="submit" class="btn btn-primary btn-sm w-100 mt-2 steve-btn">Apply Filters</button>
            @if(request()->hasAny(['year', 'make', 'model']))
              <a href="{{ url()->current() }}" class="btn btn-outline-secondary btn-sm w-100 mt-2 steve-btn">Clear Filters</a>
            @endif
            </div> -->
          </form>
          </div>

          </div>
          <script>
          (function() {
            var yearEl = document.getElementById('filter-year');
            var makeEl = document.getElementById('filter-make');
            var modelEl = document.getElementById('filter-model');
            if (!yearEl && !makeEl && !modelEl) return;

            var makesUrl = @json(route('vehicles.makes'));
            var modelsUrl = @json(route('vehicles.models'));

            function label(el, word) {
              el.innerHTML = '<option value="">All ' + word + '</option>';
            }

            function populateSelect(el, values, selected) {
              if (!el) return;
              var word = el.name.charAt(0).toUpperCase() + el.name.slice(1) + 's';
              label(el, word);
              values.forEach(function(v) {
                var opt = document.createElement('option');
                opt.value = v;
                opt.textContent = v;
                if (v == selected) opt.selected = true;
                el.appendChild(opt);
              });
            }

            function loadMakes(year, selected, cb) {
              if (!makeEl) { if (cb) cb(); return; }
              fetch(makesUrl + (year ? '?year=' + encodeURIComponent(year) : ''))
                .then(function(r) { return r.json(); })
                .then(function(makes) {
                  populateSelect(makeEl, makes, selected);
                  if (cb) cb();
                })
                .catch(function() { if (cb) cb(); });
            }

            function loadModels(year, make, selected) {
              if (!modelEl) return;
              var url = modelsUrl + '?';
              if (year) url += 'year=' + encodeURIComponent(year) + '&';
              if (make) url += 'make=' + encodeURIComponent(make);
              fetch(url)
                .then(function(r) { return r.json(); })
                .then(function(models) { populateSelect(modelEl, models, selected); })
                .catch(function() {});
            }

            var vehicleApplyBtn = document.getElementById('apply-vehicle-filter');
            function toggleVehicleApply() {
              if (!vehicleApplyBtn) return;
              var hasValue = (yearEl && yearEl.value) || (makeEl && makeEl.value) || (modelEl && modelEl.value);
              vehicleApplyBtn.disabled = !hasValue;
              vehicleApplyBtn.classList.toggle('disabled', !hasValue);
            }

            if (yearEl) {
              yearEl.addEventListener('change', function() {
                if (makeEl) makeEl.value = '';
                if (modelEl) modelEl.value = '';
                loadMakes(yearEl.value, '', function() { loadModels(yearEl.value, '', ''); });
                toggleVehicleApply();
              });
            }
            if (makeEl) {
              makeEl.addEventListener('change', function() {
                if (modelEl) modelEl.value = '';
                loadModels(yearEl ? yearEl.value : '', makeEl.value, '');
                toggleVehicleApply();
              });
            }
            if (modelEl) {
              modelEl.addEventListener('change', toggleVehicleApply);
            }
            toggleVehicleApply();
          })();
          </script>

        </div>        
      </div>

      <!-- Floating Filter Toggle Button (Mobile) -->
      <button class="shop-sidebar-toggle-float d-lg-none" type="button" aria-label="Toggle filters">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="4" y1="21" x2="4" y2="14"></line>
          <line x1="4" y1="10" x2="4" y2="3"></line>
          <line x1="12" y1="21" x2="12" y2="12"></line>
          <line x1="12" y1="8" x2="12" y2="3"></line>
          <line x1="20" y1="21" x2="20" y2="16"></line>
          <line x1="20" y1="12" x2="20" y2="3"></line>
          <line x1="1" y1="14" x2="7" y2="14"></line>
          <line x1="9" y1="8" x2="15" y2="8"></line>
          <line x1="17" y1="16" x2="23" y2="16"></line>
        </svg>
      </button>
      <script>
      document.addEventListener('DOMContentLoaded', function() {
        var sidebar = document.querySelector('.gs-product-sidebar-wrapper');
        var overlay = document.querySelector('.shop-sidebar-overlay');
        var toggle = document.querySelector('.shop-sidebar-toggle-float');
        var close = document.querySelector('.shop-sidebar-close');

        function openSidebar() {
          sidebar.classList.add('active');
          overlay.classList.add('active');
          document.body.classList.add('overflow-hidden');
        }
        function closeSidebar() {
          sidebar.classList.remove('active');
          overlay.classList.remove('active');
          document.body.classList.remove('overflow-hidden');
        }

        if (toggle) toggle.addEventListener('click', openSidebar);
        if (close) close.addEventListener('click', closeSidebar);
        if (overlay) overlay.addEventListener('click', closeSidebar);
        document.addEventListener('keydown', function(e) {
          if (e.key === 'Escape' && sidebar && sidebar.classList.contains('active')) closeSidebar();
        });
      });
      </script>

      <!-- Products Grid Area -->
      <div class="col-lg-9 products-grid-area">

        <!-- Sort & Nav Header -->
        <div class="product-nav-wrapper shadow-sm rounded border mb-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
          
            <div class="d-flex align-items-center gap-3 flex-wrap filter-sort-brand-wrapper w-84 w-77">
              <div class="d-flex align-items-center gap-2 filter-sort-wrapper">
                <h5 class="mb-0" style="font-size: 14px; font-weight: 500;">Sort by</h5>
                <select class="form-select" style="/*width:180px;*/" id="sort-select">
                  <option value="default" {{ request('sort') == 'default' ? 'selected' : '' }}>Newest</option>
                  <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest</option>
                  <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                  <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                  <option value="rating" {{ request('sort') == 'rating' ? 'selected' : '' }}>Top Rated</option>
                </select>
              </div>
              <div class="d-flex align-items-center gap-2 filter-brand-wrapper">
                <h5 class="mb-0" style="font-size: 14px; font-weight: 500;">Brand</h5>
                <form id="brand-filter-form" action="{{ url()->current() }}" method="GET" class="d-flex align-items-center gap-2">
                  @foreach(request()->except(['brand', 'page']) as $name => $value)
                    @if(is_array($value))
                      @foreach($value as $item)
                        <input type="hidden" name="{{ $name }}[]" value="{{ $item }}">
                      @endforeach
                    @else
                      <input type="hidden" name="{{ $name }}" value="{{ $value }}">
                    @endif
                  @endforeach
                  <select name="brand" class="form-select" style="width:180px;">
                    <option value="" {{ request()->filled('brand') ? '' : 'selected' }}>All Brands</option>
                    @foreach($brands as $brand)
                      @php $brandFilterValue = filled($brand->slug) ? $brand->slug : (string) $brand->id; @endphp
                      <option value="{{ $brandFilterValue }}" {{ (string) (!empty($currentBrand) ? $currentBrand->slug : request('brand')) === (string) $brandFilterValue ? 'selected' : '' }}>{{ $brand->name }}</option>
                    @endforeach
                  </select>
                </form>
              </div>
              @auth
                @if(isset($userVehicles) && $userVehicles->isNotEmpty())
                  <div class="d-flex align-items-center gap-2 filter-vehicle-wrapper">
                    <h5 class="mb-0" style="font-size: 14px; font-weight: 500;">Vehicle</h5>
                    <form id="vehicle-nav-filter-form" method="POST">
                      @csrf
                      <select id="vehicle-nav-select" class="form-select">
                        <option value="">All Vehicles</option>
                        @foreach($userVehicles as $vehicle)
                          <option value="{{ $vehicle->id }}" {{ (isset($selectedVehicle) && $selectedVehicle && $selectedVehicle->id == $vehicle->id) ? 'selected' : '' }}>
                            {{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }}
                          </option>
                        @endforeach
                      </select>
                    </form>
                  </div>
                @endif
              @endauth
            </div>
            <div class="btn-wrapper d-flex gap-2 grid-list-view-wrapper d-lg-flex justify-content-sm-end w-10">
              @include('partials.grid-list-toggle')
            </div>
        </div>

        @php
          $sortLabels = ['default' => 'Newest', 'oldest' => 'Oldest', 'price_asc' => 'Price: Low to High', 'price_desc' => 'Price: High to Low', 'rating' => 'Top Rated'];
          $sortVal = request('sort');
          $hasSortFilter = !empty($sortVal) && $sortVal !== 'default';
          $activeSortLabel = $hasSortFilter ? ($sortLabels[$sortVal] ?? $sortVal) : null;
          $brandVal = !empty($currentBrand) ? $currentBrand->slug : request('brand');
          $hasBrandFilter = !empty($brandVal);
          $activeBrand = !empty($currentBrand)
              ? $currentBrand->name
              : ($hasBrandFilter
                  ? ($brands->firstWhere('slug', $brandVal)?->name ?? $brands->firstWhere('id', $brandVal)?->name ?? $brandVal)
                  : null);
          $brandClearUrl = !empty($currentBrand)
              ? route('shop', request()->except(['brand', 'page']))
              : request()->fullUrlWithQuery(['brand' => null, 'page' => null]);
          $hasSearchFilter = request()->filled('search');
          $hasPriceParams = request()->filled('min_price') || request()->filled('max_price');
          $minPriceVal = (float) request('min_price', 0);
          $maxPriceVal = (float) request('max_price', 0);
          $hasPriceFilter = $hasPriceParams && ($minPriceVal > 0 || $maxPriceVal < ($maxProductPrice * $rate));
          $hasVehicleChips = !isset($selectedVehicle) || !$selectedVehicle;
          $hasVehicleFilterChip = isset($selectedVehicle) && $selectedVehicle;
          $hasYearFilter = $hasVehicleChips && request()->filled('year');
          $hasMakeFilter = $hasVehicleChips && request()->filled('make');
          $hasModelFilter = $hasVehicleChips && request()->filled('model');
        @endphp

        @if($hasSortFilter || $hasBrandFilter || $hasSearchFilter || $hasPriceFilter || $hasYearFilter || $hasMakeFilter || $hasModelFilter || $hasVehicleFilterChip)
          <div class="active-filter-chips d-flex align-items-center flex-wrap gap-2 mb-3">
            <span class="active-filter-chips-label">Active filters:</span>
            @if($hasSearchFilter)
              <span class="filter-chip">
                <span class="filter-chip-label">Search:</span>
                <span class="filter-chip-value">{{ request('search') }}</span>
                <a href="{{ request()->fullUrlWithQuery(['search' => null, 'page' => null]) }}" class="filter-chip-clear" data-bs-toggle="tooltip" data-bs-placement="top" title="Remove search filter">
                  <i class="las la-times"></i>
                </a>
              </span>
            @endif
            @if($hasSortFilter)
              <span class="filter-chip">
                <span class="filter-chip-label">Sort by:</span>
                <span class="filter-chip-value">{{ $activeSortLabel }}</span>
                <a href="{{ request()->fullUrlWithQuery(['sort' => null, 'page' => null]) }}" class="filter-chip-clear" data-bs-toggle="tooltip" data-bs-placement="top" title="Remove sort filter">
                  <i class="las la-times"></i>
                </a>
              </span>
            @endif
            @if($hasBrandFilter)
              <span class="filter-chip">
                <span class="filter-chip-label">Brand:</span>
                <span class="filter-chip-value">{{ $activeBrand }}</span>
                <a href="{{ $brandClearUrl }}" class="filter-chip-clear" data-bs-toggle="tooltip" data-bs-placement="top" title="Remove brand filter">
                  <i class="las la-times"></i>
                </a>
              </span>
            @endif
            @if($hasPriceFilter)
              <span class="filter-chip">
                <span class="filter-chip-label">Price:</span>
                <span class="filter-chip-value">{{ $currencySymbol }}{{ number_format($minPriceVal, 0) }} - {{ $currencySymbol }}{{ number_format($maxPriceVal, 0) }}</span>
                <a href="{{ request()->fullUrlWithQuery(['min_price' => null, 'max_price' => null, 'page' => null]) }}" class="filter-chip-clear" data-bs-toggle="tooltip" data-bs-placement="top" title="Remove price filter">
                  <i class="las la-times"></i>
                </a>
              </span>
            @endif
            @if($hasYearFilter)
              <span class="filter-chip">
                <span class="filter-chip-label">Year:</span>
                <span class="filter-chip-value">{{ request('year') }}</span>
                <a href="{{ request()->fullUrlWithQuery(['year' => null, 'page' => null]) }}" class="filter-chip-clear" data-bs-toggle="tooltip" data-bs-placement="top" title="Remove year filter">
                  <i class="las la-times"></i>
                </a>
              </span>
            @endif
            @if($hasMakeFilter)
              <span class="filter-chip">
                <span class="filter-chip-label">Make:</span>
                <span class="filter-chip-value">{{ request('make') }}</span>
                <a href="{{ request()->fullUrlWithQuery(['make' => null, 'page' => null]) }}" class="filter-chip-clear" data-bs-toggle="tooltip" data-bs-placement="top" title="Remove make filter">
                  <i class="las la-times"></i>
                </a>
              </span>
            @endif
            @if($hasModelFilter)
              <span class="filter-chip">
                <span class="filter-chip-label">Model:</span>
                <span class="filter-chip-value">{{ request('model') }}</span>
                <a href="{{ request()->fullUrlWithQuery(['model' => null, 'page' => null]) }}" class="filter-chip-clear" data-bs-toggle="tooltip" data-bs-placement="top" title="Remove model filter">
                  <i class="las la-times"></i>
                </a>
              </span>
            @endif
            @if($hasVehicleFilterChip)
              @php
                $vehicleMatchCount = $vehicleMatchCount ?? null;
              @endphp
              <span class="filter-chip">
                <span class="filter-chip-label">Vehicle:</span>
                <span class="filter-chip-value">{{ $selectedVehicle->year }} {{ $selectedVehicle->make }} {{ $selectedVehicle->model }}</span>
                @if($vehicleMatchCount !== null)
                  <span class="filter-chip-value" style="color:#6b7280; font-weight:600;">{{ $vehicleMatchCount }} part{{ $vehicleMatchCount === 1 ? '' : 's' }}</span>
                @endif
                <form method="POST" action="{{ route('shop.clear-vehicle') }}" class="d-inline">
                  @csrf
                  <button type="submit" class="filter-chip-clear" style="border:0; padding:0; cursor:pointer;" data-bs-toggle="tooltip" data-bs-placement="top" title="Remove vehicle filter">
                    <i class="las la-times"></i>
                  </button>
                </form>
              </span>
            @endif

            <form method="POST" action="{{ route('shop.clear-filters') }}" class="ms-auto">
              @csrf
              <input type="hidden" name="return_path" value="{{ '/'.request()->path() }}">
              <button type="submit" class="btn p-0 text-decoration-underline">Clear all</button>
            </form>
          </div>
        @endif

        @if(count($products) > 0)
          <div class="products-wrapper" id="products-wrapper">
            @foreach($products as $product)
              <div class="col-md-6 col-lg-4 col-xl-4 product-item-col">
                @include("partials.product-card", ["product" => $product, "wishedProductIds" => $wishedProductIds ?? []])
              </div>
            @endforeach
          </div>

          <div class="d-flex justify-content-center mt-5 shop-page-pagination">
            {{ $products->links() }}
          </div>
        @else
          <div class="text-center py-5 bg-white rounded shadow-sm border">
            <i class="fas fa-box-open text-muted mb-3" style="font-size:4rem"></i>
            <h4 class="text-muted">No products found.</h4>
            <a href="{{ route('shop') }}" class="btn mt-3 text-white steve-btn" style="background-color:var(--primary);">View All</a>
          </div>
        @endif

      </div>

    </div>
  </div>
</section>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Build clean URLs for filter forms (no trailing "?" when empty)
    function buildCleanFilterUrl(form) {
        var params = new URLSearchParams();
        form.find('input, select').each(function() {
            if ($(this).prop('disabled')) return;
            var name = $(this).attr('name');
            if (!name) return;
            if ($(this).is('select') && !this.value) return;
            if ($(this).attr('type') === 'hidden' && !this.value) return;
            if (name.endsWith('[]')) {
                var baseName = name.slice(0, -2);
                $(this).closest(form).find('[name="' + name + '"]').each(function() {
                    if (this.value) params.append(baseName, this.value);
                });
            } else {
                params.set(name, this.value);
            }
        });
        var qs = params.toString();
        var base = form.attr('action');
        return qs ? base + '?' + qs : base;
    }
    $('#vehicle-filter-form').on('submit', function(e) {
        e.preventDefault();
        window.location.href = buildCleanFilterUrl($(this));
    });

    // Sort Handler
    $('#sort-select').on('change', function() {
        var url = new URL(window.location.href);
        url.searchParams.set('sort', this.value);
        url.searchParams.delete('page');
        window.location.href = url.toString();
    });

    // Brand Filter Auto-Submit
    $('#brand-filter-form select[name="brand"]').on('change', function() {
        var url = new URL(window.location.href);
        url.searchParams.set('brand', this.value);
        url.searchParams.delete('page');
        window.location.href = url.toString();
    });

    // Vehicle Nav Filter (My Vehicles)
    $('#vehicle-nav-select').on('change', function() {
        var id = this.value;
        var form = $('#vehicle-nav-filter-form');
        var action = '{{ route('shop.clear-vehicle') }}';
        if (id) {
            action = '{{ route('user.vehicles.select', 'VEHICLE_ID') }}'.replace('VEHICLE_ID', id);
        }
        form.attr('action', action);
        form.submit();
    });

    // jQuery UI Price Slider
    var priceSymbol = $("#price-currency-symbol").val() || '$';
    var sliderMinVal = parseInt($("#price-slider-min").val()) || 0;
    var sliderMaxVal = parseInt($("#price-slider-max").val()) || 1000;
    var minPriceRaw = "{{ request('min_price', '') }}";
    var minPrice = minPriceRaw === '' ? sliderMinVal : (parseInt(minPriceRaw) || sliderMinVal);
    var maxPrice = parseInt("{{ request('max_price', 0) }}") || sliderMaxVal;

    function priceSliderOptions(values) {
        return {
            range: true, min: sliderMinVal, max: sliderMaxVal,
            values: values || [minPrice, maxPrice],
            slide: function(event, ui) {
                $("#price-min").val(ui.values[0]);
                $("#price-max").val(ui.values[1]);
                $("#price-range-label").text(priceSymbol + ui.values[0].toLocaleString() + " - " + priceSymbol + ui.values[1].toLocaleString());
            },
            change: function(event, ui) {
                togglePriceApply();
            }
        };
    }

    $("#price-slider").slider(priceSliderOptions());
    $("#price-range-label").text(priceSymbol + $("#price-slider").slider("values", 0).toLocaleString() + " - " + priceSymbol + $("#price-slider").slider("values", 1).toLocaleString());

    // Widget default collapsed hai — slider tab init hota hai jab container hidden
    // hota hai, isliye width/handle calculations galat ho sakti hain. Panel expand
    // hone par slider ko current values ke saath dobara init karte hain.
    $(document).on('shown.bs.collapse', '#sidebarPriceCollapse', function () {
        var $slider = $("#price-slider");
        if (!$slider.hasClass('ui-slider')) return;
        var current = $slider.slider('values');
        $slider.slider('destroy').slider(priceSliderOptions(current));
    });

    function isDefaultPriceRange() {
        var v = $("#price-slider").slider("values");
        return v[0] === sliderMinVal && v[1] === sliderMaxVal;
    }
    function togglePriceApply() {
        var btn = $("#apply-price-filter");
        if (!btn.length) return;
        var disabled = isDefaultPriceRange();
        btn.prop('disabled', disabled).toggleClass('disabled', disabled);
    }
    togglePriceApply();

    // Strip default price params on submit for clean URL
    $("#price-filter-form").on('submit', function(e) {
        e.preventDefault();
        var slider = $('#price-slider');
        var min = slider.slider('values', 0);
        var max = slider.slider('values', 1);
        if (min === sliderMinVal && max === sliderMaxVal) {
            $("#price-min, #price-max").prop('disabled', true);
        }
        window.location.href = buildCleanFilterUrl($(this));
    });

    $("#clear-price-filter").click(function() {
        $("#price-min, #price-max").val("").prop('disabled', true);
        $("#price-filter-form").submit();
    });

    // Grid/List Toggle
    function applyLayout(layout) {
        if (layout === 'list') {
            $('[data-layout="list"]').addClass('active');
            $('[data-layout="grid"]').removeClass('active');
            $('#products-wrapper').addClass('list-view-active');
            $('#products-wrapper > .product-item-col').removeClass('col-md-6 col-lg-4 col-xl-4').addClass('col-12');
            $('#products-wrapper .single-product').removeClass('single-product').addClass('single-product-list-view');
            localStorage.setItem('shop_layout', 'list');
        } else {
            $('[data-layout="grid"]').addClass('active');
            $('[data-layout="list"]').removeClass('active');
            $('#products-wrapper').removeClass('list-view-active');
            $('#products-wrapper > .product-item-col').removeClass('col-12').addClass('col-md-6 col-lg-4 col-xl-4');
            $('#products-wrapper .single-product-list-view').removeClass('single-product-list-view').addClass('single-product');
            localStorage.setItem('shop_layout', 'grid');
        }
    }

    applyLayout(localStorage.getItem('shop_layout') || 'grid');
    $(document).on('click', '[data-layout="grid"]', function(e) {
        e.preventDefault();
        bootstrap.Tooltip.getInstance(this)?.hide();
        this.blur();
        applyLayout('grid');
    });
    $(document).on('click', '[data-layout="list"]', function(e) {
        e.preventDefault();
        bootstrap.Tooltip.getInstance(this)?.hide();
        this.blur();
        applyLayout('list');
    });

    // Disable grid/list tooltips on devices without hover support
    if (window.matchMedia && window.matchMedia('(hover: none)').matches) {
        document.querySelectorAll('[data-layout="grid"], [data-layout="list"]').forEach(function(el) {
            var tip = bootstrap.Tooltip.getInstance(el);
            if (tip) tip.disable();
        });
    }

    // Default to grid on phones (≤767px) — only on initial load
    function checkMobileLayout() {
      if (window.innerWidth <= 580) {
        applyLayout('grid');
      }
    }
    checkMobileLayout();
    $(window).on('resize', function() {
      if (window.innerWidth <= 580) {
        applyLayout('grid');
      }
    });

    // Accordion: close other main-list categories when one opens
    document.querySelectorAll('.main-list > .collapse').forEach(function(el) {
        el.addEventListener('show.bs.collapse', function() {
            document.querySelectorAll('.main-list > .collapse.show').forEach(function(other) {
                if (other !== el) {
                    bootstrap.Collapse.getInstance(other)?.hide();
                }
            });
        });
    });
});
</script>
@endsection
