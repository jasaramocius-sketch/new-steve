@extends('layouts.app')
@push('slider-js')
    <script src="{{ asset('assets/front/js/swiper-bundle.min.js') }}" defer></script>
@endpush
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'home-page', 'pageClass' => 'home-page'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => $page->meta_title ?? config('app.name', 'StAutoparts'),
        'metaTitle' => $page->meta_title ?? (config('app.name', 'StAutoparts') . ' | Spare Parts & Accessories'),
        'metaDescription' => $page->meta_description ?? ('Shop genuine auto spare parts and accessories at ' . config('app.name', 'StAutoparts') . '. Best prices, fast shipping and quality assurance.'),
        'robots' => 'index, follow',
    ])
@endsection
@section('content')

<!-- hero section start -->
@if($heroSection)
<section class="hero-slider-wrapper">
    <div class="gs-hero-section" style="position:relative; min-height:520px; display:flex; align-items:center; overflow:hidden;">
        @php
            $heroImg = $heroSection->image ? storedImageUrl($heroSection->image, 'assets/images/home') : 'assets/images/placeholder.png';
            $heroAssetPng = str_starts_with($heroImg, 'uploads/') ? 'storage/' . $heroImg : $heroImg;
            $heroCheckPath = str_starts_with($heroImg, 'uploads/') ? 'storage/' . $heroImg : $heroImg;
            $heroWebpAsset = null;
            if (file_exists(public_path(preg_replace('/\.[^.]+$/', '.webp', $heroCheckPath)))) {
                $heroWebpAsset = str_starts_with($heroImg, 'uploads/') ? 'storage/' . preg_replace('/\.[^.]+$/', '.webp', $heroImg) : preg_replace('/\.[^.]+$/', '.webp', $heroImg);
            }
        @endphp
        @if($heroWebpAsset)
        <picture style="position:absolute;inset:0;width:100%;height:100%;z-index:0;">
            <source srcset="{{ asset($heroWebpAsset) }}" type="image/webp">
            <img src="{{ asset($heroAssetPng) }}" alt="" fetchpriority="high" onerror="this.onerror=null;this.src='{{ asset('assets/images/placeholder.png') }}'" style="width:100%;height:100%;object-fit:cover;">
        </picture>
        @else
        <img src="{{ asset($heroAssetPng) }}" alt="" fetchpriority="high" onerror="this.onerror=null;this.src='{{ asset('assets/images/placeholder.png') }}'" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;z-index:0;">
        @endif
        <div class="container">
            <div class="row align-items-center">
                <div class="col-12 col-md-6 col-lg-6">
                    <div class="hero-content" style="position:relative; z-index:1;">
                        @if($heroSection->subtitle)
                            <h6 class="subtitle wow-replaced" style="color:var(--hov-primary)">{{ $heroSection->subtitle }}</h6>
                        @endif
                        @if($heroSection->title)
                            <h1 class="title wow-replaced" data-wow-delay=".1s" style="color:#090909; font-weight: 800;">{{ $heroSection->title }}</h1>
                        @endif
                        @if($heroSection->description)
                            <p class="des wow-replaced" data-wow-delay=".2s" style="color:#000000">
                                {!! $heroSection->description !!}
                            </p>
                        @endif
                        @if($heroSection->button_text && $heroSection->button_url)
                            <a class="template-btn hero-shop-now-btn steve-btn wow-replaced" data-wow-delay=".3s" href="{{ $heroSection->button_url }}">
                                {{ $heroSection->button_text }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endif
<!-- hero section end -->

@if($sections->has('categories_heading'))
<!-- categories section start -->
<div class="gs-cate-section" {!! $sections->get('categories_heading')?->bgStyle() !!}>
    <div class="container wow-replaced section-with-padding">

        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center pb-20">
            <h2>{{ $sections->get('categories_heading')?->title ??  'All Categories' }}</h2>
            <div class="slider-nav">
                <button class="cate-prev steve-btn">
                    <i class="fas fa-chevron-left"></i>
                </button>

                <button class="cate-next steve-btn">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>

        <!-- Slider -->
        <div class="swiper home-cate-slider">
            <div class="swiper-wrapper">
            @foreach($categories as $category)
            <div class="swiper-slide">
                <a href="{{ route('category', $category['slug']) }}">
                    <div class="gs-single-cat">
                        <div class="gs-single-cat">
                            <div class="category-image">
                                @php $categoryImage = $category['image'] ?? 'assets/images/placeholder.png'; @endphp
                                {!! imgTag($categoryImage, $category['name'], '', '', 250) !!}
                            </div>
                            <h3 class="title">{{ $category['name'] }}</h3>
                            <p class="des"> 
                                ({{ $category['count'] ?? 0 }}) Products
                            </p>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
            </div>
        </div>

    </div>
</div>
<!-- categories section end -->
@endif

@if($sections->has('offers'))
<!-- product offer section start -->
<section class="gs-offer-section" {!! $sections->get('offers')?->bgStyle() !!}>
    <div class="container section-with-padding">
        <!-- title box -->
        <div class="pb-20 gs-offer-section-row-first">
            <div class="flex" >
                <div class="gs-title-box flex-column">
                    <h2 class="title wow-replaced">{{ $sections->get('offers')?->title ?? 'Special Offer' }}</h2>
                     <p class="des mb-0 wow-replaced" data-wow-delay=".1s">{!! $sections->get('offers')?->description ??  'Discover outstanding deals on high-quality auto parts. Upgraded selection and special savings this month only.' !!}</p>
                </div>
                <div class="shop-page-nav flex-column" data-wow-delay=".2s">
                    <a href="{{ route('shop') }}" class="a-tag-hover-color">
                        {{ $sections->get('offers')?->button_text ??  'Shop Now' }}
                        <i class="fas fa-arrow-right ms-1"></i> 
                    </a>                       
                </div>
            </div>
            
        </div>

        <!-- main content -->
        <div class="row g-4">
            @forelse($banners as $banner)
                @php
                    $bannerCount = $banners->count();
                    $colClass = $bannerCount == 1 ? 'col-lg-12' : ($bannerCount == 2 ? 'col-lg-6' : 'col-lg-4');
                @endphp
                <div class="{{ $colClass }} wow-replaced" data-wow-delay=".2s">
                    <a href="{{ $banner->button_url ?? route('shop') }}" class="">
                        @if($banner->image)
                            @php
                                $bannerImg = storedPath($banner->image, 'assets/images/home');
                                if (!str_contains($banner->image, '/') && !file_exists(public_path($bannerImg))) {
                                    $bannerImg = 'assets/images/categories/' . $banner->image;
                                }
                            @endphp
                            {!! imgTag($bannerImg, 'offer banner', 'w-100 h-100 object-fit-cover') !!}
                        @else
                            <div class="bg-light p-5 text-center" style="min-height: 300px; display: flex; align-items: center; justify-content: center; border-radius: 4px;">
                                <div>
                                    <h5>{{ $banner->title }}</h5>
                                    <p>{{ $banner->subtitle }}</p>
                                    <button class="btn btn-primary steve-btn">{{ $banner->button_text ??  'Learn More' }}</button>
                                </div>
                            </div>
                        @endif
                    </a>
                </div>
            @empty
                <div class="col-lg-4 wow-replaced" data-wow-delay=".2s">
                    <a href="{{ route('shop') }}" class="">
                        {!! imgTag('assets/images/arrival/1730872869Banner12-minpng.png', 'offer product', 'w-100 h-100 object-fit-cover') !!}
                    </a>
                </div>
                <div class="col-md-6 col-lg-4 wow-replaced" data-wow-delay=".2s">
                    <a href="{{ route('shop') }}" class="">
                        {!! imgTag('assets/images/arrival/1730872879Banner13-minpng.png', 'offer product', 'w-100 h-100 object-fit-cover') !!}
                    </a>
                </div>
                <div class="col-md-6 col-lg-4 wow-replaced" data-wow-delay=".2s">
                    <a href="{{ route('shop') }}" class="">
                        {!! imgTag('assets/images/arrival/1730872888Banner14-minpng.png', 'offer product', 'w-100 h-100 object-fit-cover') !!}
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</section>
<!-- product offer section end -->
@endif
<!-- <div id="unirate-converter"></div> -->
@if($sections->has('explore_products'))
<!-- explore product section start -->
<section class="gs-explore-product-section bg-light-white" {!! $sections->get('explore_products')?->bgStyle() !!}>
    <div class="container section-with-padding">
        <!-- title box & nav-tab -->
        <div class="row pb-20 justify-content-center">
            <div class="col-12">
                <div class="gs-title-box text-center">
                    <h2 class="title wow-replaced">{{ $sections->get('explore_products')?->title ??  'Explore Our Products' }}</h2>
                </div>
                <!-- product nav -->
                <ul class="nav explore-tab-navbar wow-replaced" data-wow-delay=".1s" id="myTab" role="tablist">
                    @foreach($exploreTabs as $i => $tab)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $i === 0 ? 'active' : '' }} steve-btn" id="ex-product-{{ $i + 1 }}" data-bs-toggle="tab" data-bs-target="#ex-product-{{ $i + 1 }}-pane" type="button" role="tab" aria-controls="ex-product-{{ $i + 1 }}-pane" aria-selected="{{ $i === 0 ? 'true' : 'false' }}">{{ $tab['label'] }}</button>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <!-- tab content -->
        <div class="tab-content" id="myTabContent">
            @foreach($exploreTabs as $i => $tab)
            <div class="tab-pane fade {{ $i === 0 ? 'show active' : '' }}" id="ex-product-{{ $i + 1 }}-pane" role="tabpanel" aria-labelledby="ex-product-{{ $i + 1 }}" tabindex="0">
                <div class="products-wrapper" id="products-wrapper">
                    @foreach($tab['products'] as $product)
                    <div class="col-md-6 col-lg-4 col-xl-3 product-item-col">
                        @include("partials.product-card", ["product" => $product, "wishedProductIds" => $wishedProductIds ?? []])
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@if($sections->has('deal_of_day'))
<!-- Deal of the Day -->
<section class="gs-deal-of-day gs-deal-of-day-home2" {!! $sections->get('deal_of_day')?->dealBgStyle() !!}>
    <div class="container section-with-padding">
        <div class="row">
            <div class="col-lg-6 col-md-12 d-flex align-items-center">
                <div class="deal-of-day-wrapper">
                    <div class="deal-of-day-content">
                        @php $deal = $sections->get('deal_of_day'); @endphp
                        <h2 class="title wow-replaced">{{ $deal?->title ?? '!! Special Offer !!' }}</h2>
                        <h3 class="sub-title wow-replaced" data-wow-delay=".1s">{{ $deal?->subtitle ?? 'CLICK SHOP NOW FOR ALL DEAL OF THE PRODUCT' }}</h3>
                        <p class="deal-description wow-replaced" data-wow-delay=".2s">{{ $deal?->description ?? 'Donec condimentum Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam at risus nec urna facilisis tincidunt.' }}</p>
                        <div class="countdown-wrapper flex-wrap " id="countdown">
                            <div class="countdown-item-wrapper d-flex">
                                <div class="countdown-item wow-replaced" data-wow-delay=".3s">
                                    <p class="countdown-number" id="days"><span class="countdown-title">Day</span></p>
                                    <span class="countdown-title">Day</span>
                                </div>
                                <div class="countdown-item wow-replaced" data-wow-delay=".4s">
                                    <p class="countdown-number" id="hours"><span class="countdown-title">Hour</span></p>
                                    <span class="countdown-title">Hour</span>
                                </div>
                                <div class="countdown-item wow-replaced" data-wow-delay=".5s">
                                    <p class="countdown-number" id="minutes"><span class="countdown-title">Min</span></p>
                                    <span class="countdown-title">Min</span>
                                </div>
                                <div class="countdown-item wow-replaced" data-wow-delay=".6s">
                                    <p class="countdown-number" id="seconds"><span class="countdown-title">Sec</span></p>
                                    <span class="countdown-title">Sec</span>
                                </div>
                            </div>
                            @php
                                $dealUrl = $deal?->button_url ?: route('shop');
                                if (!str_starts_with($dealUrl, 'http')) { $dealUrl = url($dealUrl); }
                            @endphp
                            <a href="{{ $dealUrl }}" class="template-btn steve-btn w-100 wow-replaced" data-wow-delay=".7s">{{ $deal?->button_text ??  'Shop Now' }}</a>
                        </div>
                    </div>
                </div>
            </div>
            @if($deal?->image)
            @php $dealRelPath = storedPath($deal->image, 'assets/images/home'); @endphp
            <div class="col-lg-6 d-lg-none col-md-12 res-deal-img">
                {!! imgTag($dealRelPath, 'deal of the day', 'img-fluid') !!}
            </div>
            <div class="deal-of-day-img h-100">
                {!! imgTag($dealRelPath, 'deal of the day', 'wow-replaced h-100') !!}
            </div>
            @endif
        </div>
    </div>
    <input type="hidden" id="countdown-date" value="{{ $deal?->extra_data['countdown'] ?? '2026-12-31T23:59:59' }}">
</section>
<!-- Deal of the Day Completed -->
@endif

@if($sections->has('featured_products_heading'))
<!-- Featured Products Section Started -->
<section class="gs-explore-product-section bg-white" {!! $sections->get('featured_products_heading')?->bgStyle() !!}>
    <div class="container section-with-padding">
        <div class="d-flex justify-content-between align-items-center pb-20 featured-title-row-first gap-2">
            <div class="gs-title-box">
                <h2 class="title">{{ $sections->get('featured_products_heading')?->title ??  'Featured Products' }}</h2>
                <div class="gs-title-box">
                    <p class="des mb-0 wow-replaced" data-wow-delay=".1s">{!! $sections->get('featured_products_heading')?->description ??  'Discover our handpicked selection of quality products at great prices. Shop top-rated items designed to deliver reliable performance and value.' !!}</p>
                </div>
            </div>

            <div class="featured-nav slider-nav">
                <button class="featured-prev steve-btn">
                    <i class="fas fa-chevron-left"></i>
                </button>

                <button class="featured-next steve-btn">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>

        <div class="swiper featured-products product-cards-slider">
            <div class="swiper-wrapper">
            @foreach($featuredProducts as $product)
            <div class="swiper-slide">@include('partials.product-card', ['product' => $product])</div>
            @endforeach
            </div>
        </div>

    </div>
</section>
<!-- Featured Product Section Completed -->
@endif

@if(count($services) > 0)
<!-- Service Section -->
<section class="gs-service-section px-4 bg-light-white">
    <div class="container section-with-padding">
        <div class="row service-row">
            @foreach($services as $service)
            <div class="col-lg-3 col-md-6 col-sm-12 services-area wow-removed">
                <div class="single-service d-flex flex-lg-column flex-xl-row text-lg-center text-xl-start">
                    <div class="icon-wrapper">
                        {!! imgTag(storedPath($service['image'] ?? null, 'assets/images/services'), $service['title'] ?? 'service') !!}
                    </div>
                    <div class="service-content">
                        <h3 class="service-title">{{ $service['title'] ?? '' }}</h3>
                        <p class="service-desc">{{ $service['description'] ?? '' }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
<!-- Service Section Completed -->
@endif

@if($sections->has('best_selling'))
<!-- Best Selling Section -->
<section class="gs-explore-product-section" {!! $sections->get('best_selling')?->bgStyle() !!}>
    <div class="container section-with-padding">
        <div class="row pb-20 best-selling-title-row">
            <div class="gs-title-box">
                    <h2 class="title wow-replaced">{{ $sections->get('best_selling')?->title ??  'Best Selling' }}</h2>
                </div>
                <div class="gs-title-box">
                    <p class="des mb-0 wow-replaced" data-wow-delay=".1s">{!! $sections->get('best_selling')?->description ??  'Discover our top-performing products that customers love most. Quality parts, verified performance, and exceptional ratings.' !!}</p>
                </div>
                <div class="best-selling-nav slider-nav">
                    <button class="best-selling-prev steve-btn ">
                        <i class="fas fa-chevron-left"></i>
                    </button>

                    <button class="best-selling-next steve-btn">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
        </div>
        <div class="swiper best-selling product-cards-slider">
            <div class="swiper-wrapper">
            @foreach($bestSelling as $product)
            <div class="swiper-slide">@include('partials.product-card', ['product' => $product])</div>
            @endforeach
            </div>
        </div>
    </div>
</section>
<!-- Best Selling Section Completed -->
@endif

@if($sections->has('latest_post'))
<!-- Latest Post Section -->
<section class="gs-latest-post-section bg-light-white py-120 mt-0 mb-0" {!! $sections->get('latest_post')?->bgStyle() !!}>
    <div class="container section-with-padding">
        @php
            $latestPostsCount = $latestPosts->count();
            $showAsSlider = $latestPostsCount >= 4;
        @endphp

        <div class="justify-content-center">
            <div class="col-12 d-flex column flex-lg-row flex-xl-row latest-post-title-row-first pb-20">
                <div class="gs-title-box">
                    <h2 class="title wow-replaced">{{ $sections->get('latest_post')?->title ??  'Latest Post' }}</h2>
                    <p class="des mb-0 wow-replaced" data-wow-delay=".1s">{!! $sections->get('latest_post')?->description ??  'Stay updated with our latest maintenance guides, tips, and insights from professional automotive mechanics.' !!}</p>
                </div>

                
                <div class="align-items-start flex-lg-column flex-xl-column latest-post-nav">
                    <div class="flex-column" data-wow-delay=".2s">
                        <a href="{{ route('blog') }}" class="a-tag-hover-color">
                            {{ $sections->get('latest_post')?->button_text ??  'View More' }}
                            <i class="fas fa-arrow-right ms-1"></i> 
                        </a>
                    </div>
                    @if($showAsSlider)
                    <div class="slider-nav d-flex gap-2">
                        <button class="latest-posts-prev steve-btn"><i class="fas fa-chevron-left"></i></button>
                        <button class="latest-posts-next steve-btn"><i class="fas fa-chevron-right"></i></button>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        @if($showAsSlider)
        <div class="latest-post-area m-0 latest-posts-slider swiper">
            <div class="swiper-wrapper">
                @foreach($latestPosts as $post)
                <div class="posts-area wow-replaced swiper-slide" data-wow-delay=".2s">
                    <a href="{{ route('blog.show', $post->slug) }}" class="single-post h2-single-post">
                        <div class="post-img">
                            {!! imgTag(storedPath($post->image ?? null, 'assets/images/blogs', 'assets/images/blogs/placeholder.jpg'), $post->title) !!}
                        </div>
                        <div class="blog-overlay"></div>
                        <div class="post-content home-2">
                            <h3 class="post-title">{{ $post->title }}</h3>
                            <p class="date">{{ $post->created_at->format('d M, Y') }}</p>
                            <p class="post-desc">{{ Str::limit(strip_tags(html_entity_decode($post->details ?? '')), 150) }}</p>
                            <span class="read-more">Read More</span>
                        </div>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @else
        <div class="gy-5 latest-post-area m-0 latest-post-section-items posts-col-{{ $latestPostsCount }}">
            @forelse($latestPosts as $post)
            <div class="posts-area wow-replaced" data-wow-delay=".2s">
                <a href="{{ route('blog.show', $post->slug) }}" class="single-post h2-single-post">
                    <div class="post-img">
                        {!! imgTag(storedPath($post->image ?? null, 'assets/images/blogs', 'assets/images/blogs/placeholder.jpg'), $post->title) !!}
                    </div>
                    <div class="blog-overlay"></div>
                    <div class="post-content home-2">
                        <h3 class="post-title">{{ $post->title }}</h3>
                        <p class="date">{{ $post->created_at->format('d M, Y') }}</p>
                        <p class="post-desc">{{ Str::limit(strip_tags(html_entity_decode($post->details ?? '')), 150) }}</p>
                        <span class="read-more">Read More</span>
                    </div>
                </a>
            </div>
            @empty
            <div class="col-12 text-center">
                <p>No posts available yet.</p>
            </div>
            @endforelse
        </div>
        @endif
    </div>
</section>
<!-- Latest Post Section Completed -->
@endif

@if($sections->has('top_brands_heading'))
<!-- Top Brands Section -->
<section class="gs-brands-section" {!! $sections->get('top_brands_heading')?->bgStyle() !!}>
    <div class="container section-with-padding">
        <div class="brands-section-row-first pb-20">
            <div class="flex">
                <div class="gs-title-box flex-column">
                    <h2 class="title wow-replaced">{{ $sections->get('top_brands_heading')?->title ??  'Top Brands' }}</h2>
                    <p class="des mb-0 wow-replaced" data-wow-delay=".1s">{!! $sections->get('top_brands_heading')?->description ??  'Explore our curated selection of premium auto part brands known for quality and reliability.' !!}</p>
                </div>
                <div class="brands-page-nav flex-column">
                    <a href="{{ route('brands') }}" class="a-tag-hover-color">
                        View All
                        <i class="fas fa-arrow-right ms-1"></i> 
                    </a>                       
                </div>
            </div>
        </div>
        <div class="gs-brands row justify-content-center">
            @foreach($brands as $brand)
            <div class="col-lg-4 col-md-6 col-sm-6 wow-replaced p-0" data-wow-delay=".1s">
                <a href="{{ $brand->website ?: route('shop', ['brand' => $brand->slug]) }}">
                    <div class="single-brands">
                        {!! imgTag(storedPath($brand->image, 'assets/images/brands'), $brand->name) !!}
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>
<!-- Top Brands Section Completed -->
@endif

@if($sections->has('buy_with_confidence'))
@php
    $buyConfidenceSection = $sections->get('buy_with_confidence');
    $buyConfidenceExtra = $buyConfidenceSection?->extra_data ?? [];
    $buyConfidenceItems = $buyConfidenceSection?->extra_data['items'] ?? [
        ['icon' => 'las la-box', 'title' => 'Fitment Guarantee', 'description' => 'Enter your VIN or vehicle specs and we show only parts that fit. Wrong part? We cover return shipping.'],
        ['icon' => 'las la-shipping-fast', 'title' => 'Fast Shipping', 'description' => '5 distribution centers coast-to-coast. Most orders arrive in 1–3 business days. Free over $99.'],
        ['icon' => 'las la-undo-alt', 'title' => '30-Day Returns', 'description' => 'Parts don’t fit or change your mind? Return within 30 days. No restocking fee on most items.'],
        ['icon' => 'las la-wrench', 'title' => 'Mechanic Tested', 'description' => 'Every brand we carry is vetted by ASE-certified technicians. No junk, no knock-offs, no surprises.'],
    ];
@endphp
<section class="gs-buy-confidence-section">
    <div class="container section-with-padding">
        <div class="row pb-20 justify-content-center">
            <div class="col-lg-8 text-center">
                <div class="gs-title-box text-center">
                    <h2 class="title wow-replaced">{{ $buyConfidenceSection?->title ?? 'Buy With Confidence' }}</h2>
                    <p class="des mb-0 wow-replaced" data-wow-delay=".1s">{!! $buyConfidenceSection?->description ?? '40 years of getting it right. Here’s how we make sure you get the right part, every time.' !!}</p>
                </div>
            </div>
        </div>

        <div class="row g-4 justify-content-center">
            @foreach($buyConfidenceItems as $index => $item)
            @php $icon = $item['icon'] ?? 'las la-box'; $title = $item['title'] ?? ''; $description = $item['description'] ?? ''; @endphp
            <div class="col-md-6 col-xl-3 wow-replaced" data-wow-delay=".{{ ($index + 1) / 10 }}s">
                <div class="buy-confidence-card" style="height:100%; background:#fff; border:1px solid #ece8e2; padding:28px 22px; text-align:center; box-shadow:0 8px 24px rgba(0,0,0,.04);">
                    <i class="{{ $icon }} text-primary d-block mb-3" style="font-size:36px;" aria-hidden="true"></i>
                    <h4 style="font-size: 1.15rem; font-weight:700; margin-bottom:10px;">{{ $title }}</h4>
                    <p style="margin:0; color:#5d5d5d; line-height:1.7; font-size:0.95rem;">{{ $description }}</p>
                </div>
            </div>
            @endforeach
        </div>

        <div class="row mt-5 justify-content-center py-4" style="background: #f8f8f8; border-radius: 12px;">
            <div class="col d-flex align-items-center justify-content-between flex-column flex-md-row gap-2">
                <p class="col d-flex gap-2 flex-column flex-md-row" style="margin:0; font-size:1rem; color:#333; line-height:1.7;">
                    <i class="las la-search text-primary mr-2" style="font-size:26px;" aria-hidden="true"></i><strong>{{ $buyConfidenceExtra['hotline_heading'] ?? 'Not sure what fits?' }}</strong> {{ $buyConfidenceExtra['hotline_description'] ?? 'Call our parts hotline with your VIN and a real mechanic will match you. We read the diagrams so you don’t have to.' }}
                </p>
                <div class="">
                    <a href="{{ $buyConfidenceSection?->button_url ?? 'tel:1800555' }}" class="template-btn steve-btn w-100 wow-replaced" style="font-weight:700; font-size:1.15rem; color: #fff; text-decoration:none;">{{ $buyConfidenceSection?->button_text ?? '1-800-555-AUTO' }}</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

@if($sections->has('partners_heading'))
<!-- Partner Section -->
<section class="gs-partner-section" {!! $sections->get('partners_heading')?->bgStyle() !!}>
    <div class="container section-with-padding">
        <div class="row pb-20 justify-content-center">
            <div class="col">
                <div class="gs-title-box">
                    <h2 class="title wow-replaced">{{ $sections->get('partners_heading')?->title ??  'Our Partners' }}</h2>
                    <p class="des mb-0 wow-replaced" data-wow-delay=".1s">{!! $sections->get('partners_heading')?->description ??  'We collaborate with world-class manufacturers to provide the highest-grade auto parts and accessories.' !!}</p>
                </div>
            </div>
        </div>
        <div class="gs-partnerss row justify-content-center">
            @php
                $partners = $sections->get('partners_heading')?->extra_data['partners'] ?? [];
            @endphp
            @forelse($partners as $partner)
            @if(!empty($partner['image']))
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 wow-replaced p-0" data-wow-delay=".1s">
                @if(!empty($partner['url']))
                <a href="{{ $partner['url'] }}" target="_blank" rel="noopener">
                @endif
                <div class="single-partner">
                    {!! imgTag($partner['image'], $partner['name'] ?? 'partner') !!}
                </div>
                @if(!empty($partner['url']))
                </a>
                @endif
            </div>
            @endif
            @empty
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 wow-replaced p-0" data-wow-delay=".1s">
                <div class="single-partner">
                    {!! imgTag('assets/images/partner/1571289583p1.jpg', 'partner') !!}
                </div>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 wow-replaced p-0" data-wow-delay=".1s">
                <div class="single-partner">
                    {!! imgTag('assets/images/partner/1571289601p2.jpg', 'partner') !!}
                </div>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 wow-replaced p-0" data-wow-delay=".1s">
                <div class="single-partner">
                    {!! imgTag('assets/images/partner/1571289608p3.jpg', 'partner') !!}
                </div>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 wow-replaced p-0" data-wow-delay=".1s">
                <div class="single-partner">
                    {!! imgTag('assets/images/partner/1571289614p4.jpg', 'partner') !!}
                </div>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 wow-replaced p-0" data-wow-delay=".1s">
                <div class="single-partner">
                    {!! imgTag('assets/images/partner/1571289621p5.jpg', 'partner') !!}
                </div>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 wow-replaced p-0" data-wow-delay=".1s">
                <div class="single-partner">
                    {!! imgTag('assets/images/partner/1571289627p6.jpg', 'partner') !!}
                </div>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 wow-replaced p-0" data-wow-delay=".1s">
                <div class="single-partner">
                    {!! imgTag('assets/images/partner/1571289634p7.jpg', 'partner') !!}
                </div>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 wow-replaced p-0" data-wow-delay=".1s">
                <div class="single-partner">
                    {!! imgTag('assets/images/partner/1571289642p8.jpg', 'partner') !!}
                </div>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 wow-replaced p-0" data-wow-delay=".1s">
                <div class="single-partner">
                    {!! imgTag('assets/images/partner/1571289650p9.jpg', 'partner') !!}
                </div>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 wow-replaced p-0" data-wow-delay=".1s">
                <div class="single-partner">
                    {!! imgTag('assets/images/partner/1571289657p10.jpg', 'partner') !!}
                </div>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 wow-replaced p-0" data-wow-delay=".1s">
                <div class="single-partner">
                    {!! imgTag('assets/images/partner/1571289669p12.jpg', 'partner') !!}
                </div>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 wow-replaced p-0" data-wow-delay=".1s">
                <div class="single-partner">
                    {!! imgTag('assets/images/partner/1571289675p13.jpg', 'partner') !!}
                </div>
            </div>
            @endforelse
        </div>
    </div>
</section>
<!-- Partner Section Completed -->
@endif

@endsection

@section('scripts')
<script>
    function observeSwiperAutoplay(swiper) {
        var firstRun = true;
        var observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    if (firstRun) {
                        firstRun = false;
                        swiper.slideNext(600);
                        swiper.autoplay.start();
                    } else {
                        swiper.autoplay.start();
                    }
                } else {
                    swiper.autoplay.stop();
                }
            });
        }, { threshold: 0.1, rootMargin: '50px 0px' });
        observer.observe(swiper.el);
    }

    $(document).ready(function() {
        if ($('.home-cate-slider').length) {
            var cateSwiper = new Swiper('.home-cate-slider', {
                slidesPerView: 6,
                spaceBetween: 20,
                grabCursor: true,
                swipeToSlide: true,
                freeModeSticky: true,
                speed: 800,
                autoplay: {
                    delay: 3000,
                    disableOnInteraction: false,
                    enabled: false,
                },
                navigation: {
                    prevEl: '.cate-prev',
                    nextEl: '.cate-next',
                },
                breakpoints: {
                    1200: { slidesPerView: 5 },
                    992: { slidesPerView: 4 },
                    670: { slidesPerView: 3 },
                    460: { slidesPerView: 2 },
                    0: { slidesPerView: 1 },
                },
            });
            observeSwiperAutoplay(cateSwiper);
        }
        if ($('.latest-posts-slider').length) {
            var postsSwiper = new Swiper('.latest-posts-slider', {
                slidesPerView: 3,
                spaceBetween: 24,
                grabCursor: true,
                speed: 800,
                autoplay: {
                    delay: 3000,
                    disableOnInteraction: false,
                    enabled: false,
                },
                navigation: {
                    prevEl: '.latest-posts-prev',
                    nextEl: '.latest-posts-next',
                },
                breakpoints: {
                    1200: { slidesPerView: 3 },
                    992: { slidesPerView: 2 },
                    576: { slidesPerView: 2 },
                    0: { slidesPerView: 1 },
                },
            });
            observeSwiperAutoplay(postsSwiper);
        }
    });
</script>
@endsection