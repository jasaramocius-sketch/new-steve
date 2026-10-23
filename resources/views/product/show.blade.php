@extends('layouts.app')
@push('slider-js')
    <script src="{{ asset('assets/front/js/swiper-bundle.min.js') }}" defer></script>
@endpush
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'product-page', 'pageClass' => 'product-page'])
@php $prodName = $product['name'] ?? 'Product'; $prodDesc = trim(strip_tags(html_entity_decode($product['description'] ?? ''))); $prodDesc = mb_strlen($prodDesc) > 160 ? mb_substr($prodDesc, 0, 157) . '...' : $prodDesc; @endphp
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => $prodName . ' - ' . config('app.name', 'StAutoparts'),
        'metaTitle' => $prodName . ' | ' . config('app.name', 'StAutoparts'),
        'metaDescription' => $prodDesc ?: ('Buy ' . $prodName . ' at ' . config('app.name', 'StAutoparts')),
        'robots' => 'index, follow',
    ])
@endsection

@section('style')
<style>
/*=====================================
    SHOP DETAILS STYLES
=====================================*/
.shop_details {
    margin-top: 60px;
    margin-bottom: 50px;
}

.details_slider_nav {
    display: block;
    margin-top: 12px;
    opacity: 0;
    transition: opacity 0.3s ease;
}
.details_slider_nav.swiper-initialized {
    opacity: 1;
}
.details_slider_nav .swiper-slide {
    /* margin: 0 6px; */
    width: auto;
    flex-shrink: 0;
}
.details_slider_nav .swiper-slide img {
    /* width: 100%; */
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
}
/* .details_slider_nav_item img {
    width: 100%;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
} */
.details_slider_nav_item picture {
    display: block;
    width: 100%;
    height: 100%;
}
.details_slider_nav_item {
    background: #F5F5F5;
    overflow: hidden;
    cursor: pointer;
    border: 2px solid #F5F5F5;
    transition: all linear .3s;
    border-radius: 10px;
    height: 84px;
}

.details_slider_nav_item:hover,
.details_slider_nav .swiper-slide-active .details_slider_nav_item,
.swiper-slide-thumb-active {
    border-color: var(--primary, #e62e04);
}

.details_slider_thumb {
    display: block;
    position: relative;
    /* min-height: 450px; */
}
.details_slider_thumb .swiper-slide img {
    width: 100%;
    height: 450px;
    object-fit: cover;
}
.details_slider_thumb .swiper-slide {
    flex-shrink: 0;
}
.details_slider_thumb_item {
    /* background: #F5F5F5; */
    overflow: hidden;
    margin: 0;
    border-radius: 16px;
}
.details_slider_thumb_item picture img{
  height: 100%;
}

/* ── Amazon-style hover zoom ── */
.zoom-wrapper {
    position: relative;
}
.zoom-container {
    position: relative;
    overflow: hidden;
    border-radius: 16px;
    cursor: crosshair;
}
.zoom-container .zoom-lens {
    display: none;
    position: absolute;
    width: 150px;
    height: 150px;
    border: 2px solid rgba(0,0,0,0.15);
    border-radius: 50%;
    background-repeat: no-repeat;
    pointer-events: none;
    z-index: 10;
    box-shadow: 0 0 0 10px rgba(0,0,0,0.04);
}
.zoom-result {
    display: none;
    position: absolute;
    top: 0;
    left: calc(100% + 15px);
    width: 100%;
    height: 100%;
    border: 1px solid #ddd;
    border-radius: 12px;
    background-repeat: no-repeat;
    background-color: #fff;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    z-index: 20;
    overflow: hidden;
}
.zoom-wrapper:hover .zoom-lens {
    display: block;
}
.zoom-wrapper:hover .zoom-result {
    display: block;
}
/* Swiper zoom module styles */
.swiper-zoom-container {
    width: 100%;
    height: 100%;
}
.swiper-zoom-container picture,
.swiper-zoom-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.swiper-slide-zoomed {
    cursor: grab;
}
@media (max-width: 991.98px) {
    .zoom-container .zoom-result {
        display: none !important;
    }
    .zoom-container:hover .zoom-result {
        display: none !important;
    }
}
.shop_details_text {
    padding: 0px 0px;
}

.shop_details_text .category {
    color:var(--secondary);
    font-size: 14px;
    font-weight: 500;
    text-transform: capitalize;
    margin-bottom: 5px;
}

.shop_details_text .details_title {
    font-size: 38px;
    font-weight: 600;
    text-transform: capitalize;
    margin-bottom: 15px;
    margin-top: 7px;
    color: #333;
    font-family: 'Jost', sans-serif;
}

.shop_details_text .stock {
    background: #05a84512;
    color: #05A845;
    padding: 2px 10px;
    margin-right: 15px;
    text-transform: capitalize;
    font-family: 'Jost', sans-serif;
    border-radius: 5px;
    font-size: 14px;
}

.shop_details_text .out_stock {
    color: #DB4437;
    background: #db443712;
}

.shop_details_text .rating svg {
    vertical-align: middle;
    margin-right: 2px;
}

.shop_details_text .rating span {
    font-size: 14px;
    margin-left: 5px;
    text-transform: capitalize;
    color: #7d7b7b;
}

.shop_details_text .price {
    font-size: 30px;
    color: #333;
    font-weight: 600;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
    margin-top: 15px;
    margin-bottom: 15px;
}

.shop_details_text .price del {
    color: #7d7b7b;
    font-weight: 400;
    font-size: 18px;
}

.shop_details_text .short_description {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin-bottom: 35px;
    color: #7d7b7b;
    line-height: 26px;
}
.shop_details_text .details_list_btn .wishlist-btn:hover{
  color:var(--primary) !important;
}
.details_single_variant {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    margin-bottom: 20px;
}

.details_single_variant .variant_title {
    color: #333;
    min-width: 54px;
    font-family: 'Jost', sans-serif;
    font-weight: 500;
    font-size: 15px;
}

.details_variant_color {
    gap: 5px;
    display: flex;
    flex-wrap: wrap;
}

.details_variant_color li {
    width: 25px;
    height: 25px;
    position: relative;
    cursor: pointer;
    border-radius: 50%;
}

.details_variant_color li.active::after {
    position: absolute;
    content: "\f00c";
    font-family: "Font Awesome 5 Free";
    font-weight: 900;
    color: #fff;
    top: 50%;
    left: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    transform: translate(-50%, -50%);
}

.details_variant_size {
    gap: 5px;
    display: flex;
    flex-wrap: wrap;
}

.details_variant_size li {
    height: 25px;
    line-height: 25px;
    width: 45px;
    background: #F5F5F5;
    text-align: center;
    text-transform: uppercase;
    font-size: 14px;
    font-weight: 400;
    color: #333;
    position: relative;
    cursor: pointer;
    transition: all linear .3s;
    border-radius: 5px;
}

.details_variant_size li:hover,
.details_variant_size li.active {
    background: #ffa500;
    color: #fff;
}

.details_qty_input {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    background: #F5F5F5;
    overflow: hidden;
    margin-right: 15px;
    width: 140px;
    border-radius: 8px;
    margin-bottom: 15px;
}

.details_qty_input input {
    width: 60px;
    min-height: 40px;
    padding: 0;
    text-align: center;
    background: none;
    border: none;
    border-right: 1px solid #fff;
    border-left: 1px solid #fff;
    font-size: 14px;
    font-weight: 500;
    color: #333;
    border-radius: 0;
}

.details_qty_input button {
    width: 40px;
    min-height: 40px;
    font-weight: 400;
    color: #333;
    background: #F5F5F5;
    transition: all linear .3s;
    padding: 0;
    font-size: 15px;
    line-height: 40px;
    text-align: center;
    border: none;
    cursor: pointer;
}

.details_qty_input button:hover {
    background: #05A845;
    color: #fff;
}

.details_qty_input button.minus:hover {
    background: #DB4437;
}

.details_qty_input button:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.details_qty_input button:disabled:hover {
    background: #F5F5F5;
    color: #333;
}

.details_total_price {
    font-size: 14px;
    font-weight: 500;
    color: #333;
    margin-bottom: 15px;
}

.details_total_price .details_total_price_value {
    font-size: 18px;
    font-weight: 700;
    color: #05A845;
}

.details_btn_area {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    flex-wrap: wrap;
    align-items: center;
    gap: 5px;
}
@media (max-width: 400px) {
  .details_btn_area {
    grid-template-columns: 1fr;
  }
}
/* .details_btn_area div{
  width: 49%;
} */
.details_btn_area .buy_now {
    background: #05A845;
}

.common_btn {
    background: #0A69D8;
    padding: 12px 25px;
    color: #fff;
    text-transform: capitalize;
    font-weight: 500;
    font-size: 15px;
    text-align: center;
    position: relative;
    overflow: hidden;
    z-index: 1;
    transition: all .3s ease;
    display: inline-block;
    border: none;
    cursor: pointer;
}

.common_btn i {
    margin-left: 5px;
    transform: rotate(-45deg);
    transition: all 0.3s ease;
}

.common_btn:hover i {
    transform: rotate(0deg);
}

.common_btn::after {
    position: absolute;
    content: "";
    width: 0;
    height: 100%;
    top: 0;
    right: 0;
    z-index: -1;
    background: #333;
    transition: all 0.3s ease;
}

.common_btn:hover::after {
    left: 0;
    width: 100%;
}

.details_list_btn {
    display: inline-flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px 20px;
    border-bottom: 1px solid #dddf;
    padding-bottom: 10px;
    margin-top: 20px;
    margin-bottom: 20px;
    list-style: none;
    padding-left: 0;
}

.details_list_btn li a {
    font-size: 16px;
    color: #7d7b7b;
    text-decoration: none;
    transition: all linear .3s;
}

.details_list_btn li a i {
    margin-right: 3px;
}

.details_list_btn li a:hover {
    color: #ffa500;
}

.details_tags_sku {
    display: flex;
    flex-direction: column;
    gap: 5px;
    list-style: none;
    padding-left: 0;
}

.details_tags_sku li {
    color: #7d7b7b;
    font-size: 14px;
    text-transform: capitalize;
}

.details_tags_sku li span {
    color: #333;
    font-size: 15px;
    margin-right: 5px;
    font-family: 'Jost', sans-serif;
    font-weight: 500;
}

.shop_details_shate {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
    margin-top: 15px;
    list-style: none;
    padding-left: 0;
}

.shop_details_shate li {
    color: #333;
    font-weight: 500;
    font-family: 'Jost', sans-serif;
    font-size: 15px;
}

.shop_details_shate li a {
    text-align: center;
    color: #333;
    font-size: 16px;
    margin-left: 10px;
    text-decoration: none;
    transition: all linear .3s;
}

.shop_details_shate li a:hover {
    color: #ffa500;
}

/* Sidebar */
.shop_details_sidebar_info {
    border: 1px solid #ddd;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 25px;
    width: 100%;
    min-width: 100%;
    max-width: 100%;
}

.shop_details_sidebar_info ul {
    display: flex;
    flex-direction: column;
    gap: 15px;
    list-style: none;
    padding-left: 0;
    margin-bottom: 0;
}

.shop_details_sidebar_info ul li {
    font-size: 13px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 10px;
    text-transform: capitalize;
    position: relative;
    padding-left: 35px;
    color: #7d7b7b;
}

.shop_details_sidebar_info ul li span,
.shop_details_sidebar_info ul li svg {
    display: inline-block;
    width: 27px;
    height: 22px;
    margin-top: -1px;
    position: absolute;
    left: 0;
    stroke:var(--primary);
}

.shop_details_sidebar_info h5 {
    text-transform: capitalize;
    font-size: 14px;
    font-weight: 600;
    margin-top: 25px;
    margin-bottom: 10px;
    color: #333;
}

.shop_details_sidebar_store {
    background: #edf5ff;
    padding: 20px;
    position: relative;
    border-radius: 10px;
    width: 100%;
    min-width: 100%;
    max-width: 100%;
}

.shop_details_sidebar_store .sold_by {
    display: block;
    color: #333;
    font-size: 14px;
    margin-bottom: 5px;
}

.shop_details_sidebar_store .store_name {
    text-transform: capitalize;
    font-size: 18px;
    font-weight: 600;
    color: #333;
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin-bottom: 15px;
}

.shop_details_sidebar_store ul {
    display: flex;
    flex-direction: column;
    border-top: 1px solid #ddd;
    gap: 10px;
    padding: 15px 0px;
    list-style: none;
    padding-left: 0;
}

.shop_details_sidebar_store ul li p {
    color: #333;
    font-size: 14px;
    font-weight: 400;
    position: relative;
    margin-bottom: 0;
}
.shop_details_sidebar_store ul li {
    font-size: 13px;
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    gap: 10px;
    text-transform: capitalize;
    position: relative;
    padding-left: 35px;
    color: #7d7b7b;
}
.shop_details_sidebar_store ul li svg {
    display: inline-block;
    /* width: 27px;
    height: 22px; */
    margin-top: -1px;
    position: absolute;
    left: 0;
    stroke: var(--primary);
}

.shop_details_sidebar_store ul li span {
    font-size: 14px;
    font-weight: 400;
    color: #7d7b7b;
}

.shop_details_sidebar_store .go_store {
    width: 100%;
    text-align: center;
    color: #ffa500;
    font-weight: 600;
    font-size: 15px;
    margin-top: 15px;
    text-decoration: underline;
    text-underline-offset: 4px;
    display: block;
}

.shop_details_sidebar_store .go_store:hover {
    color: #333;
}

/* mt/mb spacing */
.mt_60 { margin-top: 60px; }
.mt_90 { margin-top: 90px; }
.mb_70 { margin-bottom: 70px; }
.details_slider_nav .swiper-wrapper {
    display: flex;
}
.details_slider_nav button {
    background-color: white;
    width: 30px;
    height: 30px;
    padding: 0;
    box-shadow: 0px 0px 10px #000;
    min-height: 30px;
}
.details_slider_nav button i {
  width: 30px !important;
  height: 30px !important;
}
/* .details_slider_nav button:after{
    font-size: 20px;
    color: var(--primary);
} */
.details_slider_nav button:hover{
  background-color: var(--primary);
}
.details_slider_nav button:hover:after{
  color: white !important;
}
/* Responsive adjustments */
@media (max-width: 1199px) {
    .shop_details_text { padding: 0; }
    /* .details_slider_thumb_item { height: 450px; } */
    /* .details_slider_nav_item { width: 78px; height: 90px; } */
    .shop_details_text .details_title { font-size: 28px; }
}
@media (max-width: 991px) {
    .shop_details_text { padding: 0; margin-top: 30px; }
    /* .details_slider_nav_item { width: 70px; height: 80px; margin: 0px 8px 3px 8px; } */
    .shop_details_text .details_title { font-size: 24px; }
    .shop_details_text .price { font-size: 24px; }
    .shop_details_des_area { padding: 30px; }
    /* .shop_details_sidebar_info { margin-top: 25px; } */
    .shop_details_sidebar_store ul { flex-direction: row; }
    .shop_details_sidebar_store ul li { width: 33.33%; }
}
@media (max-width: 767px) {
    .details_slider_thumb_item { height: 350px; }
    /* .details_slider_nav_item { width: 60px; height: 70px; } */
    .details_btn_area { margin-top: 15px; }
    .shop_details_des_area { padding: 20px; }
    .shop_details_des_area .nav-pills button { font-size: 14px; padding: 6px 15px; }
}

@media (max-width: 575px) {
    /* .details_slider_nav_item { width: 50px; height: 60px; margin: 0px 5px 3px 5px; font-size: 12px; } */
}

@media (min-width: 1600px) {
    .details_slider_nav_item { 
      width: 91px; 
      /* height: 108px;  */
    }
}
.tab-product-des-wrapper .nav .nav-item button.active:before {
    content: " ";
    background: var(--primary);
    background: linear-gradient(139deg,rgba(208, 42, 3, 0) 50%, var(--primary) 50%);
    position: absolute;
    width: 10px;
    height: 10px;
    bottom: -5px;
    transform: rotate(45deg);
    z-index: 0;
}
.tab-product-des-wrapper .nav .nav-item button.active:hover:before{
  background: var(--hov-primary);
  background: linear-gradient(139deg,rgba(208, 42, 3, 0) 50%, var(--hov-primary) 50%);
}
.tab-product-des-wrapper .nav .nav-item button{
  position: relative;
}
.product-description-body > *:first-child { margin-top: 0; }
.product-description-body > *:last-child { margin-bottom: 0; }
.product-description-body h1,
.product-description-body h2,
.product-description-body h3,
.product-description-body h4,
.product-description-body h5 {
    font-weight: 600;
    color: #2f211f;
    margin: 1.4em 0 .6em;
    line-height: 1.3;
}
.product-description-body h1 { font-size: 1.6rem; }
.product-description-body h2 { font-size: 1.4rem; }
.product-description-body h3 { font-size: 1.25rem; }
.product-description-body h4 { font-size: 1.1rem; }
.product-description-body p { margin: 0 0 1em; }
.product-description-body ul,
.product-description-body ol {
    margin: 0 0 1em;
    padding-left: 22px;
}
.product-description-body ul { list-style: disc; }
.product-description-body ol { list-style: decimal; }
.product-description-body li { margin: 0 0 .4em; }
.product-description-body strong, .product-description-body b { font-weight: 700; }
.product-description-body em, .product-description-body i { font-style: italic; }
.product-description-body a { color: var(--primary); text-decoration: underline; }
.product-description-body a:hover { text-decoration: none; }
.product-description-body hr {
    border: 0;
    border-top: 1px solid rgba(0,0,0,.1);
    margin: 1.4em 0;
}
.product-description-body blockquote {
    border-left: 3px solid var(--primary);
    padding-left: 14px;
    margin: 0 0 1em;
    color: #6d5c5a;
}
.product-description-body {
    line-height: 1.8;
    color: #4c3533;
}
.short_description {
    line-height: 1.8;
    color: #4c3533;
}
.product-policy-tab-body {
    line-height: 1.8;
    color: #4c3533;
}

/* Stock badge */
.stock {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 0;
    margin-right: 15px;
}
.stock.in-stock  { color: #2e7d32; }
.stock.out-stock { color: #c62828; }
</style>
@endsection

@section('content')

<!-- SHOP DETAILS START -->
<section class="shop_details">
  <div class="container">
    <div class="row">
      <div class="col-xxl-10">
        <div class="row">
          <!-- Gallery Column -->
          <div class="col-lg-6">
            <div class="shop_details_slider_area">
              <div class="zoom-wrapper">
                <div class="zoom-container">
                  <div class="swiper details_slider_thumb" id="productGallery">
                    <div class="swiper-wrapper">
                    @php
                      $galleryImages = collect($product['images_json'] ?? [])
                        ->map(fn ($image) => (object) ['path' => is_array($image) ? ($image['file'] ?? null) : $image])
                        ->filter(fn ($image) => isSourceS3ImageReference($image->path));
                      $hasProductImages = filled($product['image']) || $galleryImages->isNotEmpty();
                    @endphp
                    @if($product['image'])
                      <div class="swiper-slide details_slider_thumb_item swiper-zoom-container">
                        {!! imgTag(storedPath($product['image'], 'assets/images/thumbnails'), $product['name'], 'img-fluid w-100') !!}
                      </div>
                    @endif
                    @foreach($galleryImages as $gi)
                      <div class="swiper-slide details_slider_thumb_item swiper-zoom-container">
                        {!! imgTag(storedPath($gi->path), $product['name'], 'img-fluid w-100') !!}
                      </div>
                    @endforeach
                    @if(!$hasProductImages)
                      <div class="swiper-slide details_slider_thumb_item swiper-zoom-container">
                        {!! imgTag('assets/images/placeholder.png', $product['name'], 'img-fluid w-100') !!}
                      </div>
                    @endif
                    </div>
                  </div>
                  <div class="zoom-lens" id="zoomLens"></div>
                </div>
                <div class="zoom-result" id="zoomResult"></div>
              </div>
              <div class="swiper details_slider_nav position-relative" id="productGalleryNav">
                <div class="swiper-wrapper">
                @if($product['image'])
                  <div class="swiper-slide details_slider_nav_item">
                    {!! imgTag(storedPath($product['image'], 'assets/images/thumbnails'), $product['name'], 'img-fluid w-100') !!}
                  </div>
                @endif
                @foreach($galleryImages as $gi)
                  <div class="swiper-slide details_slider_nav_item">
                    {!! imgTag(storedPath($gi->path), $product['name'], 'img-fluid w-100') !!}
                  </div>
                @endforeach
                @if(!$hasProductImages)
                  <div class="swiper-slide details_slider_nav_item">
                    {!! imgTag('assets/images/placeholder.png', $product['name'], 'img-fluid w-100') !!}
                  </div>
                @endif
                </div>
                <div class="product-detail-blade-img-gallery-nav w-100 slider-nav d-flex position-absolute top-0 justify-content-between align-items-center mt-4 z-1 px-1">
                  <button class="swiper-btn-prev steve-btn position-absolute top-0">
                      <i class="fas fa-chevron-left"></i>
                  </button>

                  <button class="swiper-btn-next steve-btn position-absolute top-0 right-0">
                      <i class="fas fa-chevron-right"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Product Info Column -->
          <div class="col-lg-6">
            <div class="shop_details_text">
              @if(isset($product['category']) && $product['category'])
                <p class="category">{{ $product['category']['name'] ?? $product['category']->name }}</p>
              @endif
              <h2 class="details_title">{{ $product['name'] }}</h2>

              <div class="d-flex flex-wrap align-items-center">
                <p class="stock {{ $product['stock'] > 0 ? 'in-stock' : 'out-stock' }}">{{ $product['stock'] > 0 ? 'In Stock' : 'Out of Stock' }}</p>
                @php
                  $visibleReviews = collect($product['reviews_data'] ?? [])->filter(fn ($r) => ! ($r['deleted'] ?? false) && ($r['approved'] ?? true) !== false);
                  $avgRating = $visibleReviews->avg('rating');
                  $displayRating = round($avgRating);
                @endphp
                @if($visibleReviews->count() > 0)
                <p class="rating" id="product-rating">
                  @for($i = 0; $i < 5; $i++)
                  <svg xmlns="http://www.w3.org/2000/svg" width="17" height="16" viewBox="0 0 17 16" fill="none">
                      <path d="M8.5 0.5L10.4084 6.37336L16.584 6.37336L11.5878 10.0033L13.4962 15.8766L8.5 12.2467L3.50383 15.8766L5.41219 10.0033L0.416019 6.37336L6.59163 6.37336L8.5 0.5Z" fill="{{ $i < $displayRating ? '#EEAE0B' : '#E2E8F0' }}" />
                  </svg>
                  @endfor
                  <span id="product-review-count">({{ $visibleReviews->count() }})</span>
                </p>
                @endif
              </div>

              @if($product->year && $product->make && $product->model)
                <div class="d-flex flex-wrap align-items-center mb-2" style="gap: 8px;">
                  <span class="badge" style="background: #e3f2fd; color: var(--secondary); font-size: 13px; font-weight: 500; padding: 6px 12px; border-radius: 6px; margin-top:10px;">
                    <i class="las la-car mr-1"></i> Fits: {{ $product->year }} {{ $product->make }} {{ $product->model }}
                  </span>
                </div>
              @endif

              @if(isset($userVehicle) && $userVehicle && $product->year && $product->make && $product->model)
                <div class="mb-2" style="font-size: 13px;">
                  @if($vehicleFit)
                    <span style="color: #2e7d32; font-weight: 600;">
                      <i class="las la-check-circle"></i> Fits your {{ $userVehicle->year }} {{ $userVehicle->make }} {{ $userVehicle->model }}
                    </span>
                  @else
                    <span style="color: #c62828; font-weight: 600;">
                      <i class="las la-times-circle"></i> Does not fit your {{ $userVehicle->year }} {{ $userVehicle->make }} {{ $userVehicle->model }}
                    </span>
                  @endif
                </div>
              @endif

              <h3 class="price">
                {{ currency_format(buyerPrice($product['price'], $product)) }}
                @if($product['old_price'])
                  <del>{{ currency_format(buyerPrice($product['old_price'], $product)) }}</del>
                @endif
              </h3>

              <!-- <div class="short_description">{!! $product['description'] ?? 'Auctor urna nunc id cursus. Scelerisque purus semper eget duis at pharetra vel turpis nunc eget.' !!}</div> -->

              <form action="{{ route('cart.add') }}" method="POST">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product['id'] }}">
                <input type="hidden" name="product_name" value="{{ $product['name'] }}">
                <input type="hidden" name="product_price" value="{{ buyerPrice($product['price'], $product) }}">
                <input type="hidden" name="product_image" value="{{ storedImageUrl($product['image'], 'assets/images/thumbnails') }}">

                <div class="d-flex flex-column align-items-start">
                  <div class="details_qty_input">
                    <button type="button" class="minus" id="qty-minus" onclick="updateQty(-1)" disabled><i class="fas fa-minus"></i></button>
                    <input type="text" id="qty" name="qty" value="1" min="1" max="{{ max(1, (int) $product['stock']) }}" @disabled((int) $product['stock'] <= 0)
                           data-unit-price="{{ buyerPrice($product['price'], $product) * config('currencies.' . session('currency', 'USD') . '.rate', config('currencies.USD.rate')) }}"
                           data-currency-symbol="{{ config('currencies.' . session('currency', 'USD') . '.symbol', '$') }}">
                    <button type="button" class="plus" id="qty-plus" onclick="updateQty(1)"><i class="fas fa-plus"></i></button>
                  </div>
                  <div class="details_total_price">
                    <span class="details_total_price_label">Total Price:</span>
                    <span class="details_total_price_value" id="total-price"></span>
                  </div>
                </div>
                <div class="details_btn_area">
                  @if((int) $product['stock'] > 0)
                  <div class="buy-now-btn">
                    <button type="submit" name="buy_now" value="1" class="template-btn steve-btn w-100 buy-now">Buy Now</button>
                    </div>
                    <div class="add-cart-btn">
                    <button type="submit" class="template-btn steve-btn w-100">Add to Cart</button>
                    </div>
                  @else
                    <div class="add-cart-btn">
                      <button type="button" class="template-btn steve-btn w-100" disabled>Out of Stock</button>
                    </div>
                  @endif
                  </div>
              </form>

              <ul class="details_list_btn">
                <li>
                  <form action="{{ route('wishlist.add') }}" method="POST" class="wishlist-form d-inline">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <button type="button" class="wishlist-btn" style="background:none;border:none;padding:0;font-size:16px;color:#7d7b7b;cursor:pointer;">
                      <i class="{{ $inWishlist ? 'fas' : 'far' }} fa-heart"></i> <span>{{ $inWishlist ? 'Remove Wishlist' : 'Add Wishlist' }}</span>
                    </button>
                  </form>
                </li>
                <li>
                  <a href="javascript:;" data-href="{{ route('compare.add', ['product_id' => $product->id]) }}" class="compare_product"><i class="fas fa-exchange-alt"></i> Compare</a>
                </li>
<li>
                   <a href="javascript:void(0)" id="contact-seller-btn" style="cursor:pointer;"><i class="fas fa-question-circle"></i> Ask a question</a>
                 </li>
              </ul>

              <ul class="details_tags_sku">
                @if(isset($product['category']) && $product['category'])
                <li><span>Category:</span> {{ $product['category']['name'] ?? $product['category']->name }}</li>
                @endif
                @if(!empty($product['sku']))
                <li><span>SKU / Part #:</span> {{ $product['sku'] }}</li>
                @endif
                @if(!empty($product['brand']) && $product['brand'])
                <li><span>Brand:</span> <span style="text-transform: uppercase;">{{ $product['brand']['name'] ?? $product['brand']->name }}</span></li>
                @endif
              </ul>

              <ul class="shop_details_shate">
                <li>Share:</li>
                @php $shareUrl = url()->current(); $shareText = ($product['name'] ?? 'Check this product'); @endphp
                <li><a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}" target="_blank" rel="noopener" aria-label="Share on Facebook"><i class="fab fa-facebook-f"></i></a></li>
                <li><a href="https://twitter.com/intent/tweet?url={{ urlencode($shareUrl) }}&text={{ urlencode($shareText) }}" target="_blank" rel="noopener" aria-label="Share on Twitter"><i class="fab fa-twitter"></i></a></li>
                <li><a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($shareUrl) }}" target="_blank" rel="noopener" aria-label="Share on LinkedIn"><i class="fab fa-linkedin-in"></i></a></li>
                <li><a href="https://api.whatsapp.com/send?text={{ urlencode($shareText . ' ' . $shareUrl) }}" target="_blank" rel="noopener" aria-label="Share on WhatsApp"><i class="fab fa-whatsapp"></i></a></li>
              </ul>
            </div>
          </div>
        </div>

        <!-- Description & Reviews Tabs -->
        <div class="tab-product-des-wrapper pt-4">
          <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link template-btn steve-btn active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description-tab-pane" type="button" role="tab" aria-controls="description-tab-pane" aria-selected="true">Description</button>
            </li>
            <!-- <li class="nav-item" role="presentation">
              <button class="nav-link template-btn steve-btn" id="policy-tab" data-bs-toggle="tab" data-bs-target="#policy-tab-pane" type="button" role="tab" aria-controls="policy-tab-pane" aria-selected="false">Buy / Return Policy</button>
            </li> -->
            <li class="nav-item" role="presentation">
              <button class="nav-link template-btn steve-btn" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews-tab-pane" type="button" role="tab" aria-controls="reviews-tab-pane" aria-selected="false">Reviews ({{ collect($product['reviews_data'] ?? [])->filter(fn ($r) => ! ($r['deleted'] ?? false) && ($r['approved'] ?? true) !== false)->count() }})</button>
            </li>
          </ul>
          <div class="tab-content border px-4 " id="myTabContent">
            <div class="tab-pane fade show active py-4" id="description-tab-pane" role="tabpanel" aria-labelledby="description-tab" tabindex="0">
              @php
                  $descText  = $product['description'] ?? '';
                  $descText  = $product['overview'] ?? '';
                  $descLower = strtolower(strip_tags($descText));
                  $hasFeaturesInDesc = str_contains($descLower, 'key features') || preg_match('/^\s*[•\-*]\s/m', $descText);
              @endphp
              @if(!empty($descText))
              <div class="product-description-body">{!! $descText !!}</div>
              @else
              <p class="text-muted mb-0">No description available for this product yet.</p>
              @endif
              @if(!empty($product['features']) && !$hasFeaturesInDesc)
                <h5 class="mt-4 mb-3 fw-bold">Key Features:</h5>
                <ul class="key-features-list">
                  @foreach($product['features'] ?? [] as $feature)
                    <li>{!! $feature !!}</li>
                  @endforeach
                </ul>
              @endif
              <!-- @if(!empty($product['specifications']))
                <h5 class="mt-4 mb-3 fw-bold">Specifications:</h5>
                <div class="table-responsive">
                  <table class="table table-bordered product-specifications-table">
                    <tbody>
                      @foreach($product['specifications'] as $spec)
                        <tr>
                          <th class="text-muted" style="width: 40%;">{{ $spec['label'] ?? '' }}</th>
                          <td>{{ $spec['value'] ?? '' }}</td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              @endif -->
            </div>
            <div class="tab-pane fade py-4" id="policy-tab-pane" role="tabpanel" aria-labelledby="policy-tab" tabindex="0">
                <div class="product-policy-tab-body">
                  <!-- @include('partials.product-buy-return-policy', ['product' => $product]) -->
                </div>
            </div>
            <div class="tab-pane fade py-4" id="reviews-tab-pane" role="tabpanel" aria-labelledby="reviews-tab" tabindex="0">
              <div class="review-tab-content-wrapper bg-white">

                <!-- Review Form (always visible, server validates eligibility) -->
                <div class="write-review-form mb-4 pb-4 border-bottom" id="writeReviewForm">
                  <h5 class="mb-3" style="font-weight: 600;">Write a Review</h5>
                  <div id="reviewFormAlert" class="d-none mb-3"></div>
                  <div class="mb-3">
                    <label class="form-label fw-semibold">Your Rating *</label>
                    <div class="star-picker" id="starPicker">
                      @for($i = 1; $i <= 5; $i++)
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="21" viewBox="0 0 17 16" fill="none" data-rating="{{ $i }}" style="cursor:pointer;transition:fill .2s;">
                          <path d="M8.5 0.5L10.4084 6.37336L16.584 6.37336L11.5878 10.0033L13.4962 15.8766L8.5 12.2467L3.50383 15.8766L5.41219 10.0033L0.416019 6.37336L6.59163 6.37336L8.5 0.5Z" fill="#E2E8F0" />
                        </svg>
                      @endfor
                      <input type="hidden" name="rating" id="reviewRating" value="0">
                    </div>
                  </div>
                  <div class="mb-3">
                    <label class="form-label fw-semibold">Your Review *</label>
                    <textarea class="form-control" id="reviewText" rows="3" maxlength="1000" placeholder="Share your experience with this product..."></textarea>
                  </div>
                  <div class="mb-3">
                    <label class="form-label fw-semibold">Review Images</label>
                    <input type="file" id="reviewImagesInput" multiple accept="image/jpg,image/jpeg,image/png,image/webp" class="d-none">
                    <button type="button" class="btn btn-outline-secondary btn-sm steve-btn" id="reviewImagesBrowseBtn"><i class="fas fa-cloud-upload-alt"></i> Browse</button>
                    <span class="text-muted ms-2" style="font-size:12px;">Max 5 images, 2MB each</span>
                    <div id="reviewImagesPreview" class="d-flex flex-wrap gap-2 mt-2"></div>
                  </div>
                  <button type="button" class="template-btn steve-btn" id="submitReviewBtn">Submit Review</button>
                </div>

                <!-- Reviews List -->
                <div id="reviewsList">
                @php $reviews = collect($product['reviews_data'] ?? [])->filter(fn ($r) => ! ($r['deleted'] ?? false) && ($r['approved'] ?? true) !== false); @endphp
                @forelse($reviews as $review)
                  <div class="d-flex gap-3 mb-4 pb-3 border-bottom review-item" data-review-id="{{ $review['id'] ?? '' }}">
                    <div class="flex-shrink-0">
                      <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 50px; height: 50px; background: var(--primary); font-weight: 600; font-size: 18px;">{{ substr($review['name'] ?? '?', 0, 1) }}</div>
                    </div>
                    <div class="flex-grow-1">
                      <div class="d-flex align-items-center gap-2 mb-1">
                        <h6 class="mb-0 fw-semibold">{{ $review['name'] ?? 'Anonymous' }}</h6>
                        <small class="text-muted">{{ $review['date'] ?? '' }}</small>
                        @auth
                          @if(($review['user_id'] ?? null) == Auth::id())
                            <button type="button" class="btn btn-sm btn-outline-danger ms-auto delete-review-btn" data-review-id="{{ $review['id'] ?? '' }}" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete review" style="padding:6px 12px;font-size:13px;">
                              <i class="fas fa-trash"></i>
                            </button>
                          @endif
                        @endauth
                      </div>
                      <div class="mb-1">
                        @for($i = 0; $i < 5; $i++)
                          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="13" viewBox="0 0 17 16" fill="none">
                              <path d="M8.5 0.5L10.4084 6.37336L16.584 6.37336L11.5878 10.0033L13.4962 15.8766L8.5 12.2467L3.50383 15.8766L5.41219 10.0033L0.416019 6.37336L6.59163 6.37336L8.5 0.5Z" fill="{{ $i < ($review['rating'] ?? 0) ? '#EEAE0B' : '#E2E8F0' }}" />
                          </svg>
                        @endfor
                      </div>
                      <p class="mb-0" style="color: #4c3533;">{{ $review['text'] ?? '' }}</p>
                      @if(!empty($review['images']) && count($review['images']) > 0)
                        <div class="d-flex flex-wrap gap-2 mt-2">
                          @foreach($review['images'] as $img)
                            <a href="{{ asset($img) }}" target="_blank">
                              <img src="{{ asset($img) }}" alt="Review image" style="width:70px;height:70px;object-fit:cover;border-radius:6px;border:1px solid #ddd;">
                            </a>
                          @endforeach
                        </div>
                      @endif
                    </div>
                  </div>
                @empty
                  <p class="text-muted mb-0" id="noReviewsMsg">No reviews yet. Be the first to review this product!</p>
                @endforelse
                </div>

              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="col-xxl-2">
        <div class="shop_details_sidebar">
          <div class="shop_details_sidebar_info">
            <ul>
              <li>
                <span>
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247" />
                  </svg>
                </span>
                Shipping worldwide
              </li>
              <li>
                <span>
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                  </svg>
                </span>
                Always Authentic
              </li>
              <li>
                <span>
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                  </svg>
                </span>
                Cash on Delivery Available
              </li>
            </ul>
            <h5>Return & Warranty</h5>
            <ul>
              <li>
                <span>
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                  </svg>
                </span>
                14 days easy return
              </li>
              <li>
                <span>
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M12 9v3.75m0-10.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.25-8.25-3.286Zm0 13.036h.008v.008H12v-.008Z" />
                  </svg>
                </span>
                Warranty not available
              </li>
            </ul>
          </div>

          <!-- <div class="shop_details_sidebar_store">
            @php $seller = $product->seller ?? null; @endphp
            @if($seller)
            <p class="sold_by">Sold by</p>
            <h4 class="store_name">{{ $seller->name }}</h4>
              <ul>
                @if($seller->location)
                <li>
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M12 21s-6-5.35-6-10a6 6 0 1 1 12 0c0 4.65-6 10-6 10Z" stroke="var(--primary)" stroke-width="1.5" stroke-linejoin="round"/>
                      <circle cx="12" cy="11" r="2.25" stroke="var(--primary)" stroke-width="1.5"/>
                    </svg>
                  <p>Location</p>
                  <span>{{ $seller->location }}</span>
                </li>
                @endif
                <li>
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke="var(--primary)" stroke-width="1.5" stroke-linecap="round"/>
                      <circle cx="9" cy="7" r="4" stroke="var(--primary)" stroke-width="1.5"/>
                      <path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" stroke="var(--primary)" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                  <p>Followers</p>
                  <span>({{ $seller->followers_count }})</span>
                </li>
                @if(!empty($seller->products_count))
                <li>
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                      <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" stroke="var(--primary)" stroke-width="1.5" stroke-linejoin="round"/>
                      <path d="M3.27 6.96 12 12.01l8.73-5.05M12 22.08V12" stroke="var(--primary)" stroke-width="1.5" stroke-linejoin="round"/>
                    </svg>
                  <p>Products</p>
                  <span>({{ $seller->products_count }})</span>
                </li>
                @endif
              </ul>
            @endif
          </div> -->
        </div>
      </div>
    </div>
  </div>
</section>
<!-- SHOP DETAILS END -->

<!-- Similar Products To Compare -->
@if(isset($similar) && count($similar) > 0)
<div class="container">
    @include('partials.product-similar-compare', ['current' => $product, 'similar' => $similar])
</div>
@endif

<!-- Related Products Area -->
@if(isset($related) && $related->count() > 0)
<section class="gs-product-cards-slider-area">
  <div class="container">
    <div class="title text-center m-0 pb-20">
      <h3 style="font-weight: 600;">Related Products</h3>
      <p class="text-muted">Explore other premium products in this category</p>
    </div>
    <div class="related-products-grid d-grid">
      @foreach($related as $rel)
        <div class="grid-item">
          @include("partials.product-card", ["product" => $rel, "cardClass" => "h-100 shadow-sm border overflow-hidden", "titleTag" => "h5", "titleClass" => "mb-2", "priceTag" => "h4", "wishedProductIds" => $wishedProductIds ?? []])
        </div>
      @endforeach
    </div>
  </div>
</section>
@endif

<!-- Recently Viewed Products -->
@include('partials.recently-viewed')

<!-- Contact Seller Modal -->
<div class="modal fade" id="contactSellerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div id="contactSellerAlert" class="d-none" style="margin:0; border-radius:0;"></div>
      <form action="{{ route('contact.seller') }}" method="POST" id="contactSellerForm">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product['id'] }}">
        <div class="modal-header">
          <h5 class="modal-title">Contact Seller</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Name *</label>
            <input type="text" name="name" class="form-control" required maxlength="255" value="{{ Auth::check() ? Auth::user()->name : '' }}">
          </div>
          <div class="mb-3">
            <label class="form-label">Email *</label>
            <input type="email" name="email" class="form-control" required maxlength="255" value="{{ Auth::check() ? Auth::user()->email : '' }}">
          </div>
          <div class="mb-3">
            <label class="form-label">Phone</label>
            <input type="tel" name="phone" class="form-control" inputmode="numeric" maxlength="20" value="{{ Auth::check() ? Auth::user()->phone ?? '' : '' }}">
          </div>
          <div class="mb-3">
            <label class="form-label">Message *</label>
            <textarea name="message" class="form-control" required rows="4" maxlength="2000"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary" id="contactSellerSubmitBtn">
            <span id="contactSellerBtnText">Send Message</span>
            <span id="contactSellerBtnSpinner" class="d-none">
              <span class="spinner-border spinner-border-sm" role="status"></span> Sending...
            </span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@endsection

@section('scripts')
<script src="{{ asset('assets/front/js/marked.min.js') }}" defer></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  if (typeof marked !== 'undefined') {
    marked.setOptions({ breaks: true, gfm: true });
    document.querySelectorAll('.product-description-body').forEach(function(el) {
      var html = el.innerHTML;
      // Already rich HTML (e.g. saved by the WYSIWYG editor) -> render as-is, skip markdown
      if (/<[a-z][^>]*>/i.test(html)) return;
      var raw = el.textContent;
      raw = raw.replace(/^[ \t]*\u2022[ \t]+/gm, '- ');
      el.innerHTML = marked.parse(raw);
    });
  }
});
document.getElementById('contact-seller-btn')?.addEventListener('click', function(e) {
  e.preventDefault();
  var modal = new bootstrap.Modal(document.getElementById('contactSellerModal'));
  modal.show();
});
document.getElementById('contact-seller-btn-sidebar')?.addEventListener('click', function(e) {
  e.preventDefault();
  var modal = new bootstrap.Modal(document.getElementById('contactSellerModal'));
  modal.show();
});

document.getElementById('contactSellerForm')?.addEventListener('submit', function(e) {
  e.preventDefault();
  var form = this;
  var alertBox = document.getElementById('contactSellerAlert');
  var submitBtn = document.getElementById('contactSellerSubmitBtn');
  var btnText = document.getElementById('contactSellerBtnText');
  var btnSpinner = document.getElementById('contactSellerBtnSpinner');

  alertBox.className = 'd-none';
  submitBtn.disabled = true;
  btnText.classList.add('d-none');
  btnSpinner.classList.remove('d-none');

  var formData = new FormData(form);

  fetch(form.action, {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json'
    },
    body: formData
  }).then(function(response) {
    return response.json().then(function(data) {
      return { status: response.status, data: data };
    });
  }).then(function(result) {
    submitBtn.disabled = false;
    btnText.classList.remove('d-none');
    btnSpinner.classList.add('d-none');

    if (result.status === 200 || result.status === 201) {
      toastr.success(result.data.message || 'Message sent successfully!');
      form.reset();
      form.querySelectorAll('input, textarea').forEach(function(el) { el.value = ''; });
      bootstrap.Modal.getInstance(document.getElementById('contactSellerModal')).hide();
    } else if (result.status === 422) {
      var errors = result.data.errors || {};
      var msgs = [];
      Object.values(errors).forEach(function(arr) { arr.forEach(function(m) { msgs.push(m); }); });
      alertBox.className = 'alert alert-danger m-0 rounded-0';
      alertBox.textContent = msgs.join(' ');
    } else {
      alertBox.className = 'alert alert-danger m-0 rounded-0';
      alertBox.textContent = result.data.message || 'Something went wrong. Please try again.';
    }
  }).catch(function() {
    submitBtn.disabled = false;
    btnText.classList.remove('d-none');
    btnSpinner.classList.add('d-none');
    alertBox.className = 'alert alert-danger m-0 rounded-0';
    alertBox.textContent = 'Network error. Please try again.';
  });
});

const qtyInput = document.getElementById('qty');
const qtyMinus = document.getElementById('qty-minus');
const qtyPlus = document.getElementById('qty-plus');
const totalPriceEl = document.getElementById('total-price');
const maxQty = {{ $product['stock'] }};
const unitPrice = parseFloat(qtyInput.dataset.unitPrice) || 0;
const currencySymbol = qtyInput.dataset.currencySymbol || '$';

function formatPrice(amount) {
  return currencySymbol + Number(amount).toFixed(2);
}

function updateTotalPrice(val) {
  if (totalPriceEl) {
    totalPriceEl.textContent = formatPrice(unitPrice * val);
  }
}

function updateQty(change) {
  let val = parseInt(qtyInput.value) + change;
  if (val < 1) val = 1;
  if (val > maxQty) val = maxQty;
  qtyInput.value = val;
  qtyMinus.disabled = val <= 1;
  qtyPlus.disabled = val >= maxQty;
  updateTotalPrice(val);
}

qtyInput.addEventListener('input', function () {
  let val = parseInt(this.value) || 1;
  if (val < 1) val = 1;
  if (val > maxQty) val = maxQty;
  this.value = val;
  qtyMinus.disabled = val <= 1;
  qtyPlus.disabled = val >= maxQty;
  updateTotalPrice(val);
});

updateQty(0);


// Star picker
const starPicker = document.getElementById('starPicker');
const reviewRating = document.getElementById('reviewRating');
if (starPicker) {
  const stars = starPicker.querySelectorAll('svg');
  stars.forEach(star => {
    star.addEventListener('mouseenter', () => {
      const val = parseInt(star.dataset.rating);
      stars.forEach(s => {
        s.querySelector('path').setAttribute('fill', parseInt(s.dataset.rating) <= val ? '#EEAE0B' : '#E2E8F0');
      });
    });
    star.addEventListener('click', () => {
      reviewRating.value = star.dataset.rating;
    });
  });
  starPicker.addEventListener('mouseleave', () => {
    const val = parseInt(reviewRating.value) || 0;
    stars.forEach(s => {
      s.querySelector('path').setAttribute('fill', parseInt(s.dataset.rating) <= val ? '#EEAE0B' : '#E2E8F0');
    });
  });
}

// Review images
let reviewPageImages = [];
const reviewImagesInput = document.getElementById('reviewImagesInput');
const reviewImagesBrowseBtn = document.getElementById('reviewImagesBrowseBtn');
const reviewImagesPreview = document.getElementById('reviewImagesPreview');

if (reviewImagesBrowseBtn) {
  reviewImagesBrowseBtn.addEventListener('click', () => reviewImagesInput.click());
}
if (reviewImagesInput) {
  reviewImagesInput.addEventListener('change', (e) => {
    Array.from(e.target.files).forEach(file => {
      if (reviewPageImages.length >= 5) return;
      if (file.size > 2 * 1024 * 1024) { alert(file.name + ' is larger than 2MB.'); return; }
      if (!['image/jpeg','image/png','image/webp'].includes(file.type)) { alert(file.name + ' is not supported.'); return; }
      reviewPageImages.push(file);
    });
    renderReviewPageImages();
    e.target.value = '';
  });
}
function renderReviewPageImages() {
  if (!reviewImagesPreview) return;
  reviewImagesPreview.innerHTML = '';
  reviewPageImages.forEach((file, idx) => {
    const div = document.createElement('div');
    div.style.cssText = 'position:relative;width:60px;height:60px;border-radius:6px;overflow:hidden;border:1px solid #ddd;';
    const img = document.createElement('img');
    img.src = URL.createObjectURL(file);
    img.style.cssText = 'width:100%;height:100%;object-fit:cover;';
    const rm = document.createElement('button');
    rm.innerHTML = '&times;';
    rm.style.cssText = 'position:absolute;top:2px;right:2px;background:rgba(0,0,0,0.6);color:#fff;border:none;border-radius:50%;width:18px;height:18px;font-size:10px;cursor:pointer;display:flex;align-items:center;justify-content:center;';
    rm.addEventListener('click', () => { reviewPageImages.splice(idx, 1); renderReviewPageImages(); });
    div.appendChild(img);
    div.appendChild(rm);
    reviewImagesPreview.appendChild(div);
  });
}

// Submit review
const submitReviewBtn = document.getElementById('submitReviewBtn');
if (submitReviewBtn) {
  submitReviewBtn.addEventListener('click', () => {
    const rating = parseInt(reviewRating.value);
    const text = document.getElementById('reviewText').value.trim();
    const alertEl = document.getElementById('reviewFormAlert');

    if (!rating || rating < 1 || rating > 5) {
      alertEl.className = 'alert alert-danger';
      alertEl.textContent = 'Please select a rating.';
      alertEl.classList.remove('d-none');
      return;
    }
    if (!text) {
      alertEl.className = 'alert alert-danger';
      alertEl.textContent = 'Please write your review.';
      alertEl.classList.remove('d-none');
      return;
    }

    submitReviewBtn.disabled = true;
    submitReviewBtn.textContent = 'Submitting...';

    const formData = new FormData();
    formData.append('rating', rating);
    formData.append('text', text);
    reviewPageImages.forEach(file => formData.append('images[]', file));

    fetch('{{ route("product.review", $product->slug) }}', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'X-Requested-With': 'XMLHttpRequest'
      },
      body: formData
    })
    .then(r => r.json().then(body => ({ status: r.status, body })))
    .then(({ status, body }) => {
      if (status === 401) {
        alertEl.className = 'alert alert-danger';
        alertEl.innerHTML = 'Please <a href="{{ route("login") }}">login</a> to write a review.';
        alertEl.classList.remove('d-none');
        submitReviewBtn.disabled = false;
        submitReviewBtn.textContent = 'Submit Review';
      } else if (status === 403) {
        alertEl.className = 'alert alert-danger';
        alertEl.textContent = body.message || 'You must purchase this product to write a review.';
        alertEl.classList.remove('d-none');
        submitReviewBtn.disabled = false;
        submitReviewBtn.textContent = 'Submit Review';
      } else if (body.success) {
        const review = body.review;
        const svgTpl = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="13" viewBox="0 0 17 16" fill="none"><path d="M8.5 0.5L10.4084 6.37336L16.584 6.37336L11.5878 10.0033L13.4962 15.8766L8.5 12.2467L3.50383 15.8766L5.41219 10.0033L0.416019 6.37336L6.59163 6.37336L8.5 0.5Z" fill="FILL_COLOR"></path></svg>';
        const starsHtml = Array.from({length: 5}, (_, i) =>
          svgTpl.replace('FILL_COLOR', i < review.rating ? '#EEAE0B' : '#E2E8F0')
        ).join('');

        let imagesHtml = '';
        if (review.images && review.images.length > 0) {
          imagesHtml = '<div class="d-flex flex-wrap gap-2 mt-2">' +
            review.images.map(img => '<a href="' + '{{ asset("/") }}' + img + '" target="_blank"><img src="' + '{{ asset("/") }}' + img + '" alt="Review image" style="width:70px;height:70px;object-fit:cover;border-radius:6px;border:1px solid #ddd;"></a>').join('') +
            '</div>';
        }

        const html = `
          <div class="d-flex gap-3 mb-4 pb-3 border-bottom review-item" data-review-id="${review.id}">
            <div class="flex-shrink-0">
              <div class="rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 50px; height: 50px; background: var(--primary); font-weight: 600; font-size: 18px;">${review.name.charAt(0)}</div>
            </div>
            <div class="flex-grow-1">
              <div class="d-flex align-items-center gap-2 mb-1">
                <h6 class="mb-0 fw-semibold">${review.name}</h6>
                <small class="text-muted">${review.date}</small>
                <button type="button" class="btn btn-sm btn-outline-danger ms-auto delete-review-btn" data-review-id="${review.id}" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete review" style="padding:2px 8px;font-size:12px;">
                  <i class="fas fa-trash"></i>
                </button>
              </div>
              <div class="mb-1">${starsHtml}</div>
              <p class="mb-0" style="color: #4c3533;">${review.text}</p>
              ${imagesHtml}
            </div>
          </div>`;

        const noMsg = document.getElementById('noReviewsMsg');
        if (noMsg) noMsg.remove();

        document.getElementById('reviewsList').insertAdjacentHTML('afterbegin', html);
        updateProductRating(body.rating, body.count);

        // Reset form
        document.getElementById('reviewText').value = '';
        reviewRating.value = 0;
        reviewPageImages = [];
        renderReviewPageImages();
        starPicker.querySelectorAll('svg').forEach(s => {
          s.querySelector('path').setAttribute('fill', '#E2E8F0');
        });
        alertEl.classList.add('d-none');
        submitReviewBtn.disabled = false;
        submitReviewBtn.textContent = 'Submit Review';

        // Update tab count
        document.getElementById('reviews-tab').textContent = `Reviews (${body.count})`;
      } else {
        alertEl.className = 'alert alert-danger';
        alertEl.textContent = body.message || 'You are not eligible to review this product.';
        alertEl.classList.remove('d-none');
        submitReviewBtn.disabled = false;
        submitReviewBtn.textContent = 'Submit Review';
      }
    })
    .catch(() => {
      alertEl.className = 'alert alert-danger';
      alertEl.textContent = 'Something went wrong. Please try again.';
      alertEl.classList.remove('d-none');
      submitReviewBtn.disabled = false;
      submitReviewBtn.textContent = 'Submit Review';
    });
  });
}

// Delete review
document.addEventListener('click', (e) => {
  const btn = e.target.closest('.delete-review-btn');
  if (!btn) return;
  if (!confirm('Are you sure you want to delete this review?')) return;

  const reviewId = btn.dataset.reviewId;
  btn.disabled = true;

  fetch('{{ route("product.review.delete", ["slug" => $product->slug, "reviewId" => "REVIEW_ID"]) }}'.replace('REVIEW_ID', reviewId), {
    method: 'DELETE',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
    .then(r => {
      return r.json().then(data => ({ status: r.status, data: data }));
    })
    .then(result => {
      if (result.status === 401) {
        alertEl.className = 'alert alert-danger';
        alertEl.innerHTML = 'Please <a href="' + window.loginUrl + '">login</a> to write a review.';
        alertEl.classList.remove('d-none');
        submitReviewBtn.disabled = false;
        submitReviewBtn.textContent = 'Submit Review';
      } else if (result.status === 403) {
        alertEl.className = 'alert alert-danger';
        alertEl.textContent = result.data.message || 'You must purchase this product to write a review.';
        alertEl.classList.remove('d-none');
        submitReviewBtn.disabled = false;
        submitReviewBtn.textContent = 'Submit Review';
      } else if (result.data.success) {
      const item = document.querySelector(`.review-item[data-review-id="${reviewId}"]`);
      if (item) {
        item.style.transition = 'opacity 0.3s';
        item.style.opacity = '0';
        setTimeout(() => item.remove(), 300);
      }
      updateProductRating(data.rating, data.count);
      document.getElementById('reviews-tab').textContent = `Reviews (${data.count})`;
    }
  })
  .catch(() => {
    btn.disabled = false;
  });
});

function updateProductRating(rating, count) {
  let container = document.getElementById('product-rating');
  if (!container) {
    if (!count) return;
    const stockP = document.querySelector('p.stock');
    if (!stockP) return;
    container = document.createElement('p');
    container.className = 'rating';
    container.id = 'product-rating';
    stockP.parentNode.appendChild(container);
  }
  const svgTemplate = '<svg xmlns="http://www.w3.org/2000/svg" width="17" height="16" viewBox="0 0 17 16" fill="none"><path d="M8.5 0.5L10.4084 6.37336L16.584 6.37336L11.5878 10.0033L13.4962 15.8766L8.5 12.2467L3.50383 15.8766L5.41219 10.0033L0.416019 6.37336L6.59163 6.37336L8.5 0.5Z" fill="FILL_COLOR"></path></svg>';
  const starsHtml = Array.from({length: 5}, (_, i) =>
    svgTemplate.replace('FILL_COLOR', i < rating ? '#EEAE0B' : '#E2E8F0')
  ).join('');
  container.innerHTML = starsHtml + ` <span>(${count})</span>`;
}

$(document).ready(function() {
  var galleryNav = new Swiper('#productGalleryNav', {
    slidesPerView: 4,
    spaceBetween: 10,
    allowTouchMove: false,
    centeredSlides: true,
    centeredSlidesBounds: true,
    breakpoints: {
      992: { slidesPerView: 3 },
      768: { slidesPerView: 2 },
    },
  });

  var thumbSwiper = new Swiper('#productGallery', {
    slidesPerView: 1,
    spaceBetween: 0,
    effect: 'fade',
    fadeEffect: { crossFade: true },
    grabCursor: true,
    allowTouchMove: true,
    zoom: {
      maxRatio: 3,
      minRatio: 1,
    },
    navigation: {
      nextEl: '#productGalleryNav .swiper-btn-next',
      prevEl: '#productGalleryNav .swiper-btn-prev',
    },
    thumbs: {
      swiper: galleryNav,
    },
  });

  function centerNavSlide(index) {
    var slide = galleryNav.slides[index];
    if (!slide) return;
    var slideLeft = slide.offsetLeft;
    var slideWidth = slide.offsetWidth;
    var containerWidth = galleryNav.el.offsetWidth;
    var offset = slideLeft - (containerWidth - slideWidth) / 2;
    offset = Math.max(0, Math.min(offset, galleryNav.wrapperEl.scrollWidth - containerWidth));
    galleryNav.wrapperEl.style.transition = 'transform 300ms ease';
    galleryNav.setTranslate(-offset);
  }

  galleryNav.on('click', function(swiper) {
    thumbSwiper.slideTo(swiper.clickedIndex);
  });

  thumbSwiper.on('slideChangeTransitionEnd', function() {
    centerNavSlide(thumbSwiper.activeIndex);
    resetZoom();
  });

  /* ── Amazon-style hover zoom (desktop only) ── */
  var isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
  if (!isMobile && window.innerWidth > 991) {
    var wrapper = document.querySelector('.zoom-wrapper');
    var container = document.querySelector('.zoom-container');
    var lens = document.getElementById('zoomLens');
    var result = document.getElementById('zoomResult');
    if (!wrapper || !container || !lens || !result) return;

    var zoomFactor = 3;

    function updateZoom(e) {
      var activeSlide = thumbSwiper.slides[thumbSwiper.activeIndex];
      if (!activeSlide) return;
      var img = activeSlide.querySelector('img');
      if (!img) return;

      var rect = container.getBoundingClientRect();
      var x = e.clientX - rect.left;
      var y = e.clientY - rect.top;

      if (x < 0 || x > rect.width || y < 0 || y > rect.height) {
        resetZoom();
        return;
      }

      var lensX = x - lens.offsetWidth / 2;
      var lensY = y - lens.offsetHeight / 2;
      lens.style.left = lensX + 'px';
      lens.style.top = lensY + 'px';

      var bgW = rect.width * zoomFactor;
      var bgH = rect.height * zoomFactor;
      var bgX = -(x * zoomFactor - result.offsetWidth / 2);
      var bgY = -(y * zoomFactor - result.offsetHeight / 2);

      lens.style.backgroundImage = 'url(' + img.src + ')';
      lens.style.backgroundSize = bgW + 'px ' + bgH + 'px';
      lens.style.backgroundPosition = bgX + 'px ' + bgY + 'px';
      lens.style.display = 'block';

      result.style.backgroundImage = 'url(' + img.src + ')';
      result.style.backgroundSize = bgW + 'px ' + bgH + 'px';
      result.style.backgroundPosition = bgX + 'px ' + bgY + 'px';
      result.style.display = 'block';
    }

    function resetZoom() {
      lens.style.display = 'none';
      result.style.display = 'none';
    }

    wrapper.addEventListener('mousemove', updateZoom);
    wrapper.addEventListener('mouseleave', resetZoom);
  }
});
</script>
@endsection
