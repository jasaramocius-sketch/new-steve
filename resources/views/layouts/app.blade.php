<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ rtrim(request()->getBasePath(), '/') }}">
    @hasSection('meta_tags')
    @yield('meta_tags')
    @else
    <title>{{ config('app.name', 'Steve Auto Parts') }}</title>
    <meta name="robots" content="noindex, nofollow">
    @endif
    
    <!-- Preconnect for external origins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    
    <!-- Essential css files -->
    <link rel="stylesheet" href="{{ asset('assets/front/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/front/css/style.css') }}?v={{ filemtime(public_path('assets/front/css/style.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/front/css/custom.css') }}?v={{ filemtime(public_path('assets/front/css/custom.css')) }}">
    <link rel="stylesheet" href="{{ asset('assets/front/css/all.min.css') }}">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/line-awesome/1.3.0/line-awesome/css/line-awesome.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <!-- Non-critical CSS - loaded async -->
    <link rel="preload" href="{{ asset('assets/front/css/slick.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="{{ asset('assets/front/css/swiper-bundle.min.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="{{ asset('assets/front/css/nice-select.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    @stack('jquery-ui-css')
    <link rel="preload" href="{{ asset('assets/front/css/animate.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="{{ asset('assets/front/css/toastr.min.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="{{ asset('assets/front/css/dynamic-font.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="icon" href="{{ storedImageUrl(\App\Models\Setting::get('header_favicon') ?? '1730880696Fabpng.png', 'assets/images') }}">
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Jost:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Saira:ital,wght@0,100..900;1,100..900&display=swap" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Jost:wght@100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Saira:ital,wght@0,100..900;1,100..900&display=swap"></noscript>

    <style>
    .border-bottom-6px {
      border-bottom: 6px solid !important;
      border-bottom-color: #ededf2 !important;
    }
    .done .border-bottom-6px {
      border-bottom-color: var(--green)  !important;
    }
    .active .border-bottom-6px {
      border-bottom-color: #d02a03 !important;
    }

    {{-- Utility classes (.fs-*, .fw-*, .opacity-*, .gutters-5, .sm-gutters-10,
         .img-fit, .size-60px, .btn-circle, .btn-soft-primary,
         .aiz-plus-minus) live in custom.css — removed the duplicates here. --}}
    @media (max-width: 991px) {
      .aiz-plus-minus { width: fit-content; }
    }
    /* .icon-circle .cart-count {
    background: var(--primary);
    width: 25px;
    height: 25px;
    display: block;
    position: absolute;
    top: -12px;
    right: -10px;
    border-radius: 50%;
    color: white;
    display: -webkit-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-pack: center;
    -ms-flex-pack: center;
    justify-content: center;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
} */

/* Prevent slider FOUC - hide sliders until initialized */
.home-cate-slider:not(.swiper-initialized),
.product-cards-slider:not(.swiper-initialized),
.details_slider_thumb:not(.swiper-initialized),
.details_slider_nav:not(.swiper-initialized) {
    visibility: hidden;
}
    </style>

    @yield('style')
    @stack('page-builder-css')
</head>
<body id="@yield('page-id', 'default-page-id')" class="site-root @yield('page-class', 'default-body-class')">

    <!-- header area -->
    <header class="header-section position-relative z-3 header-stikcy">
        @php
            $languages = config('languages', ['en' => ['name' => 'English', 'flag' => 'us'], 'hi' => ['name' => 'Hindi', 'flag' => 'in']]);
            $currentLocale = session('locale', config('app.locale', 'en'));
            $currencies = config('currencies', ['USD' => ['symbol' => '$', 'name' => 'USD'], 'INR' => ['symbol' => '₹', 'name' => 'INR']]);
            $currentCurrency = session('currency', 'USD');
            $megaCategories = \App\Models\Category::has('children')->with(['children' => function ($q) { $q->orderBy('name'); }])->orderBy('name')->take(5)->get();
        @endphp
        <div class="info-bar d-none d-md-block">
            <div class="container custom-containerr">
                <div class="info-row d-flex">
                    <div class="info-left">
                        <ul class="wows align-items-center">
                            <li class="text-white">Contact &amp; Support: 
                                <a href="tel:{{ \App\Models\Setting::get('header_phone', '+1 (234) 567-8901') }}" class="a-tag-hover-color">
                                    {{ \App\Models\Setting::get('header_phone', '+1 (234) 567-8901') }}
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="info-right">
                        <ul class="d-flex wows align-items-center">
                            <!-- <li class="d-none d-lg-block">
                                <a href="{{ route('login') }}" class="info-bar-btn">Vendor Login</a>
                            </li>
                            <li class="d-none d-lg-block">
                                <a href="{{ route('login') }}" class="info-bar-btn">Rider Login</a>
                            </li> 
                            <li class="d-none d-md-inline-block">
                                <svg xmlns="http://www.w3.org/2000/svg" width="2" height="21" viewBox="0 0 2 21" fill="none">
                                    <path d="M1 0.5V20.5" stroke="white" stroke-opacity="0.8" />
                                </svg>
                            </li>-->
                            <li class="d-flex gap-2 align-items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="21" viewBox="0 0 20 21" fill="none">
                                    <path d="M9.99935 2.16669C12.4993 3.83335 13.2683 7.41005 13.3327 10.5C13.2683 13.59 12.4994 17.1667 9.99935 18.8334M9.99935 2.16669C7.49935 3.83335 6.73039 7.41005 6.66602 10.5C6.73039 13.59 7.49935 17.1667 9.99935 18.8334M9.99935 2.16669C5.39698 2.16669 1.66602 5.89765 1.66602 10.5M9.99935 2.16669C14.6017 2.16669 18.3327 5.89765 18.3327 10.5M9.99935 18.8334C14.6017 18.8334 18.3327 15.1024 18.3327 10.5M9.99935 18.8334C5.39698 18.8334 1.66602 15.1024 1.66602 10.5M18.3327 10.5C16.666 13 13.0893 13.769 9.99935 13.8334C6.90938 13.769 3.33268 13 1.66602 10.5M18.3327 10.5C16.666 8.00002 13.0893 7.23106 9.99935 7.16669C6.90938 7.23106 3.33268 8.00002 1.66602 10.5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                                <div class="dropdown">
                                    <button class="dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">{{ $languages[$currentLocale]['name'] ?? 'English' }}</button>
                                    <ul class="dropdown-menu">
                                        @foreach($languages as $code => $info)
                                            <li>
                                                <a class="dropdown-item dropdown__item {{ $currentLocale === $code ? 'active' : '' }}" href="{{ route('language.change', $code) }}">{{ $info['name'] }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </li>
                            <li>
                                <svg xmlns="http://www.w3.org/2000/svg" width="2" height="21" viewBox="0 0 2 21" fill="none">
                                    <path d="M1 0.5V20.5" stroke="white" stroke-opacity="0.8"></path>
                                </svg>
                            </li>
                            <li class="d-flex gap-2 align-items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="21" viewBox="0 0 20 21" fill="none">
                                    <path d="M7.08268 12.7222C7.08268 13.7961 7.95324 14.6667 9.02713 14.6667H10.8327C11.9833 14.6667 12.916 13.7339 12.916 12.5834C12.916 11.4328 11.9833 10.5 10.8327 10.5H9.16602C8.01542 10.5 7.08268 9.56728 7.08268 8.41669C7.08268 7.26609 8.01542 6.33335 9.16602 6.33335H10.9716C12.0455 6.33335 12.916 7.20391 12.916 8.2778M9.99935 5.08335V6.33335M9.99935 14.6667V15.9167M18.3327 10.5C18.3327 15.1024 14.6017 18.8334 9.99935 18.8334C5.39698 18.8334 1.66602 15.1024 1.66602 10.5C1.66602 5.89765 5.39698 2.16669 9.99935 2.16669C14.6017 2.16669 18.3327 5.89765 18.3327 10.5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                                <div class="dropdown">
                                    <button class="dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">{{ $currentCurrency }}</button>
                                    <ul class="dropdown-menu">
                                        @foreach($currencies as $code => $info)
                                            <li>
                                                <a class="dropdown-item dropdown__item {{ $currentCurrency === $code ? 'active' : '' }}" href="{{ route('currency.change', $code) }}">{{ $info['name'] }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </li>
                            
                            <li class="d-none d-md-inline-block" style="list-style:none;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="2" height="21" viewBox="0 0 2 21" fill="none">
                                    <path d="M1 0.5V20.5" stroke="white" stroke-opacity="0.8"></path>
                                </svg>
                            </li>
                            @auth
                            <li class="d-none d-md-flex gap-2 align-items-center dropdown">
                                <!-- <a href="{{ route('user.dashboard') }}" class="a-tag-hover-color dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-regular fa-circle-user"></i> My Account
                                </a> -->
                                <button class="dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-regular fa-circle-user"></i> {{ Str::limit(Auth::user()->name, 10, '...') }}
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('user.dashboard') }}">Dashboard</a></li>
                                    <li><a class="dropdown-item" href="{{ route('user.profile') }}">My Profile</a></li>
                                    <li><a class="dropdown-item" href="{{ route('logout') }}">Logout</a></li>
                                </ul>
                            </li>
                            @else
                            <li class="d-none d-md-flex align-items-center" style="list-style:none;">
                                <a href="{{ route('login') }}" class="a-tag-hover-color">Login</a>
                            </li>
                            @endauth
                            <li class="d-none d-md-inline-block">
                                <svg xmlns="http://www.w3.org/2000/svg" width="2" height="21" viewBox="0 0 2 21" fill="none">
                                    <path d="M1 0.5V20.5" stroke="white" stroke-opacity="0.8"></path>
                                </svg>
                            </li>
                            <li class="bell-icon-col d-none d-md-flex align-items-center" style="list-style:none;left: -10px;position: relative;" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Notifications">
                                <a href="{{ route('user.notifications') }}" class="position-relative" aria-label="Notifications" style="color:#fff;text-decoration:none;line-height:1;">
                                    @if(!empty($unreadNotificationCount) && $unreadNotificationCount > 0)
                                        <span class="cart-count" id="notification-count">{{ $unreadNotificationCount }}</span>
                                    @endif
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="21" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                                    </svg>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="header-top">
            <div class="container custom-containerr">
                <div class="create-navbar d-flex">
                    <div class="nav-left">
                        <button type="button" class="header-toggle mobile-menu-toggle d-flex d-xl-none" aria-label="Open menu">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <path d="M3 12H21M3 6H21M3 18H15" stroke="#1F0300" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </button>
                        <a class="header-logo-wrapper" href="{{ route('home') }}">
                            {!! imgTag(storedPath(\App\Models\Setting::get('header_logo') ?? 'BwSkuSZ7ZYGWPc4Zk3CfeFzcn49dHpx3143n4WKS.png', 'assets/images'), 'logo', 'logo', 'loading="eager" fetchpriority="high" width="60" height="63" onerror="this.onerror=null;this.src=\''.asset('assets/images/placeholder.png').'\'' . '"') !!}
                        </a>
                    </div>
                    <div class="nav-center">
                        <ul class="d-flex align-items-center nav-menus">
                            @php $desktopMenu = json_decode(\App\Models\Setting::get('nav_menu', '[]'), true); @endphp
                            @include('partials.nav-menu', ['menu' => $desktopMenu, 'megaCategories' => $megaCategories])
                        </ul>
                    </div>
                    <div class="nav-right d-flex align-items-center gap-3">
                        <div class="icon-circle">
                            <button id="searchIcon" class="p-0" data-bs-toggle="tooltip" data-bs-placement="bottom" aria-label="Search" data-bs-original-title="Search">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <path d="M21 21L17.5001 17.5M20 11.5C20 16.1944 16.1944 20 11.5 20C6.80558 20 3 16.1944 3 11.5C3 6.80558 6.80558 3 11.5 3C16.1944 3 20 6.80558 20 11.5Z" stroke="#1F0300" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="icon-circle position-relative compare-count-wrapper">
                            <a href="{{ route('compare.index') }}" data-bs-toggle="tooltip" data-bs-placement="bottom" aria-label="Compare" data-bs-original-title="Compare">
                                <span class="cart-count" id="compare-count">{{ $compareCount ?? 0 }}</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <path d="M18.1777 8C23.2737 8 23.2737 16 18.1777 16C13.0827 16 11.0447 8 5.43875 8C0.85375 8 0.85375 16 5.43875 16C11.0447 16 13.0828 8 18.1788 8H18.1777Z" stroke="#1F0300" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </a>
                        </div>
                        <div class="icon-circle position-relative wishlist-count-wrapper">
                            <a href="{{ route('user.wishlist') }}" data-bs-toggle="tooltip" data-bs-placement="bottom" aria-label="Wishlist" data-bs-original-title="Wishlist">
                                <span class="cart-count" id="wishlist-count">{{ $wishlistCount ?? 0 }}</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <path d="M16.1111 3C19.6333 3 22 6.3525 22 9.48C22 15.8138 12.1778 21 12 21C11.8222 21 2 15.8138 2 9.48C2 6.3525 4.36667 3 7.88889 3C9.91111 3 11.2333 4.02375 12 4.92375C12.7667 4.02375 14.0889 3 16.1111 3Z" stroke="#1F0300" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </a>
                        </div>
                        <div class="icon-circle position-relative cart-count-wrapper">
                            <a href="{{ route('cart') }}" data-bs-toggle="tooltip" data-bs-placement="bottom" aria-label="Cart" data-bs-original-title="Cart">
                                <span class="cart-count" id="cart-count">{{ $cartCount ?? 0 }}</span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <path d="M2 2H3.30616C3.55218 2 3.67519 2 3.77418 2.04524C3.86142 2.08511 3.93535 2.14922 3.98715 2.22995C4.04593 2.32154 4.06333 2.44332 4.09812 2.68686L4.57143 6M4.57143 6L5.62332 13.7314C5.75681 14.7125 5.82355 15.2031 6.0581 15.5723C6.26478 15.8977 6.56108 16.1564 6.91135 16.3174C7.30886 16.5 7.80394 16.5 8.79411 16.5H17.352C18.2945 16.5 18.7658 16.5 19.151 16.3304C19.4905 16.1809 19.7818 15.9398 19.9923 15.6342C20.2309 15.2876 20.3191 14.8247 20.4955 13.8988L21.8191 6.94969C21.8812 6.62381 21.9122 6.46087 21.8672 6.3335C21.8278 6.22177 21.7499 6.12768 21.6475 6.06802C21.5308 6 21.365 6 21.0332 6H4.57143ZM10 21C10 21.5523 9.55228 22 9 22C8.44772 22 8 21.5523 8 21C8 20.4477 8.44772 20 9 20C9.55228 20 10 20.4477 10 21ZM18 21C18 21.5523 17.5523 22 17 22C16.4477 22 16 21.5523 16 21C16 20.4477 16.4477 20 17 20C17.5523 20 18 20.4477 18 21Z" stroke="#1F0300" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="overlay"></div>
    <div class="mobile-menu">
        <div class="mobile-menu-top">
            <a href="{{ route('home') }}">
                {!! imgTag(storedPath(\App\Models\Setting::get('mobile_logo') ?? '1730281141Whitepng.png', 'assets/images'), 'logo', 'logo', 'width="100%" height="100%"') !!}
            </a>
            <svg class="close" xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none">
                <path d="M18 6L6 18M6 6L18 18" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </div>
        <div class="mobile-menu-account px-3 py-3 border-bottom mobile-view-auth-btns mb-2">
            @if(Auth::check())
                <div class="d-flex align-items-center gap-2">
                    @if(Auth::user()->avatar)
                        {!! imgTag(storedPath(Auth::user()->avatar), Auth::user()->name, '', 'style="width:36px;height:36px;border-radius:50%;object-fit:cover;"') !!}
                    @else
                        <div style="width:36px;height:36px;border-radius:50%;background:#1e1e2d;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:600;font-size:14px;flex-shrink:0;">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                    @endif
                    <span class="fw-700 fs-14">{{ Auth::user()->name }}</span>
                </div>
                <div class="mt-2 d-flex flex-wrap gap-2">
                    <a href="{{ route('user.dashboard') }}" class="mobile-auth-icon-btn steve-btn" title="Dashboard"><i class="fas fa-gauge"></i> Dashboard</a>
                    @if(!in_array(Auth::user()->role, ['admin', 'master_admin', 'staff']))
                        <a href="{{ route('user.orders') }}" class="mobile-auth-icon-btn steve-btn" title="Orders"><i class="fas fa-box"></i> Orders</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="mobile-auth-icon-btn steve-btn" title="Logout"><i class="fas fa-right-from-bracket"></i> Logout</button>
                    </form>
                </div>
            @else
                <div class="d-flex gap-2">
                    <a href="{{ route('login') }}" class="mobile-auth-icon-btn steve-btn" title="Login"><i class="fas fa-right-to-bracket"></i> Sign In</a>
                    <a href="{{ route('register') }}" class="mobile-auth-icon-btn steve-btn" title="Register"><i class="fas fa-user-plus"></i> Sign Up</a>
                </div>
            @endif
        </div>
        <nav>
            <div class="nav justify-content-between pt-24 mobile-nav-tabs" id="nav-tab" role="tablist">
                <button class="flex-grow-1 state-left-btn active active-tab-btn" id="main-menu-tab" data-bs-toggle="tab"
                    data-bs-target="#main-menu" type="button" role="tab" aria-controls="main-menu"
                    aria-selected="true">MAIN MENU</button>
                <button class="flex-grow-1 state-right-btn active-tab-btn" id="categories-tab" data-bs-toggle="tab"
                    data-bs-target="#categories" type="button" role="tab" aria-controls="categories"
                    aria-selected="false">CATEGORIES</button>
            </div>
        </nav>
        <div class="tab-content" id="nav-tabContent1">
            <div class="tab-pane fade show active tb-tb" id="main-menu" role="tabpanel"
                aria-labelledby="main-menu-tab">
                <div class="mobile-menu-widget">
                    <ul class="mobile-nav-menu">
                        @php
                            $mobileMenu = json_decode(\App\Models\Setting::get('nav_menu', '[]'), true);
                            $mobileCurrentUrl = url()->current();
                            $mobileBasePath = rtrim(parse_url(url('/'), PHP_URL_PATH) ?? '', '/');
                            $mobileCurrentPath = parse_url($mobileCurrentUrl, PHP_URL_PATH) ?? '/';
                            $mobileRelativePath = $mobileBasePath !== '' && str_starts_with($mobileCurrentPath, $mobileBasePath) ? substr($mobileCurrentPath, strlen($mobileBasePath)) : $mobileCurrentPath;
                            $activeCategoryUrls = $activeCategoryUrls ?? [];
                            $mobileRouteAliases = ['/product' => ['/shop'], '/category' => ['/shop']];
                            $mobileAliasedMenuUrl = '';
                            foreach ($mobileRouteAliases as $prefix => $targets) {
                                if (str_starts_with($mobileRelativePath, $prefix . '/') || $mobileRelativePath === $prefix) {
                                    foreach ($targets as $target) { $mobileAliasedMenuUrl = url($target); break; }
                                    break;
                                }
                            }
                        @endphp
                        @foreach($mobileMenu as $item)
                            @php
                                $children = $item['children'] ?? [];
                                $hasChildren = !empty($children);
                                $rawItemUrl = $item['url'] ?? '';
                                $itemUrl = ($rawItemUrl !== '' && $rawItemUrl !== '#') ? $rawItemUrl : '/';
                                $itemFullUrl = url($itemUrl);
                                $isActive = $itemFullUrl === $mobileCurrentUrl || $itemFullUrl === rtrim($mobileCurrentUrl, '/');
                                if (!$isActive && $itemUrl !== '/') {
                                    $isActive = str_starts_with($mobileCurrentUrl, $itemFullUrl . '/') || str_starts_with($mobileCurrentUrl, $itemFullUrl . '?');
                                }
                                if (!$isActive && $mobileAliasedMenuUrl && $itemFullUrl === $mobileAliasedMenuUrl) {
                                    $isActive = true;
                                }
                                if (!$isActive && !empty($activeCategoryUrls) && in_array($itemFullUrl, $activeCategoryUrls)) {
                                    $isActive = true;
                                }
                            @endphp
                            <li class="{{ $hasChildren ? 'has-children' : '' }} {{ $isActive ? 'active' : '' }}">
                                @if($hasChildren)
                                    <div class="mobile-menu-item-row d-flex align-items-center">
                                        <a href="{{ url($item['url'] ?? '#') }}">{{ $item['label'] ?? '' }}</a>
                                        <span class="mobile-submenu-toggle ms-auto"><i class="fas fa-chevron-down"></i></span>
                                    </div>
                                    <ul class="mobile-submenu">
                                        @foreach($children as $child)
                                            @php
                                                $subChildren = $child['children'] ?? [];
                                                $hasSubChildren = !empty($subChildren);
                                                $rawChildUrl = $child['url'] ?? '';
                                                $childUrl = ($rawChildUrl !== '' && $rawChildUrl !== '#') ? $rawChildUrl : '/';
                                                $childFullUrl = url($childUrl);
                                                $childActive = $childFullUrl === $mobileCurrentUrl || $childFullUrl === rtrim($mobileCurrentUrl, '/');
                                                if (!$childActive && $childUrl !== '/') {
                                                    $childActive = str_starts_with($mobileCurrentUrl, $childFullUrl . '/') || str_starts_with($mobileCurrentUrl, $childFullUrl . '?');
                                                }
                                                if (!$childActive && !empty($activeCategoryUrls) && in_array($childFullUrl, $activeCategoryUrls)) {
                                                    $childActive = true;
                                                }
                                            @endphp
                                            <li class="{{ $hasSubChildren ? 'has-children' : '' }} {{ $childActive ? 'active' : '' }}">
                                                @if($hasSubChildren)
                                                    <div class="mobile-menu-item-row d-flex align-items-center">
                                                        <a href="{{ url($child['url'] ?? '#') }}">{{ $child['label'] ?? '' }}</a>
                                                        <span class="mobile-submenu-toggle ms-auto"><i class="fas fa-chevron-down"></i></span>
                                                    </div>
                                                    <ul class="mobile-submenu">
                                                        @foreach($subChildren as $sub)
                                                            @php
                                                            $rawSubUrl = $sub['url'] ?? '';
                                                            $subUrl = ($rawSubUrl !== '' && $rawSubUrl !== '#') ? $rawSubUrl : '/';
                                                            $subFullUrl = url($subUrl);
                                                            $subActive = $subFullUrl === $mobileCurrentUrl || $subFullUrl === rtrim($mobileCurrentUrl, '/');
                                                            if (!$subActive && $subUrl !== '/') {
                                                                $subActive = str_starts_with($mobileCurrentUrl, $subFullUrl . '/') || str_starts_with($mobileCurrentUrl, $subFullUrl . '?');
                                                            }
                                                            if (!$subActive && !empty($activeCategoryUrls) && in_array($subFullUrl, $activeCategoryUrls)) {
                                                                $subActive = true;
                                                            }
                                                            @endphp
                                                            <li class="{{ $subActive ? 'active' : '' }}"><a href="{{ url($sub['url'] ?? '#') }}">{{ $sub['label'] ?? '' }}</a></li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <a href="{{ url($child['url'] ?? '#') }}">{{ $child['label'] ?? '' }}</a>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <a href="{{ url($item['url'] ?? '#') }}">{{ $item['label'] ?? '' }}</a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        <div class="tab-content" id="nav-tabContent3">
            <div class="tab-pane fade tb-tb" id="categories" role="tabpanel"
                aria-labelledby="categories-tab">
                <div class="mobile-menu-widget">
                    <div class="product-cat-widget">
                        <ul class="list-unstyled mb-0 gap-1 d-flex flex-column">
                            @php
                                $catRoute = function($slug) { return url('/category/' . $slug); };
                                $subcatRoute = function($parent, $child) { return url('/category/' . $parent . '/' . $child); };
                                $subchildRoute = function($parent, $child, $subchild) { return url('/category/' . $parent . '/' . $child . '/' . $subchild); };
                            @endphp
                            @foreach($mobileCategoryTree as $topCat)
                                @php
                                    $topCatUrl = $catRoute($topCat->slug);
                                    $topActive = $mobileCurrentUrl === $topCatUrl || $mobileCurrentUrl === $topCatUrl . '/' || (!empty($activeCategoryUrls) && in_array($topCatUrl, $activeCategoryUrls));
                                    $hasActiveChild = false;
                                    if (count($topCat->children) > 0 && !$topActive) {
                                        foreach ($topCat->children as $subCat) {
                                            $subCatUrl = $subcatRoute($topCat->slug, $subCat->slug);
                                            if ($mobileCurrentUrl === $subCatUrl || $mobileCurrentUrl === $subCatUrl . '/' || (!empty($activeCategoryUrls) && in_array($subCatUrl, $activeCategoryUrls))) {
                                                $hasActiveChild = true; break;
                                            }
                                            foreach ($subCat->children as $childCat) {
                                                $childCatUrl = $subchildRoute($topCat->slug, $subCat->slug, $childCat->slug);
                                                if ($mobileCurrentUrl === $childCatUrl || $mobileCurrentUrl === $childCatUrl . '/' || (!empty($activeCategoryUrls) && in_array($childCatUrl, $activeCategoryUrls))) {
                                                    $hasActiveChild = true; break 2;
                                                }
                                            }
                                        }
                                    }
                                @endphp
                                <li class="main-list gap-1 d-flex flex-column mb-1 {{ $topActive || $hasActiveChild ? 'active' : '' }}">
                                    <div class="d-flex justify-content-between align-items-center gap-3">
                                        @if($topCat->descendant_count > 0)
                                            <a href="{{ route('category', $topCat->slug) }}" class="text-decoration-none flex-grow-1 {{ $topActive || $hasActiveChild ? 'fw-bold' : '' }}" style="color: {{ $topActive || $hasActiveChild ? 'var(--primary)' : '#1f0300' }}; font-weight: {{ $topActive || $hasActiveChild ? '600' : '400' }}; font-size: 15px;">
                                                {{ $topCat->name }}
                                                <span class="text-muted" style="font-size: 13px; font-weight: 400;">({{ $topCat->descendant_count }})</span>
                                            </a>
                                        @else
                                            <span class="flex-grow-1" style="color: #aaa; font-size: 15px;">
                                                {{ $topCat->name }}
                                                <span class="text-muted" style="font-size: 13px; font-weight: 400;">(0)</span>
                                            </span>
                                        @endif
                                        @if(count($topCat->children) > 0)
                                            <button class="btn p-0 border-0 cat-toggle-btn collapsed steve-btn" data-bs-toggle="collapse" data-bs-target="#mobcat_{{ $topCat->id }}">
                                                <i class="fa-solid fa-plus" style="font-size: 11px;"></i>
                                                <i class="fa-solid fa-minus" style="font-size: 11px;"></i>
                                            </button>
                                        @endif
                                    </div>

                                    @if(count($topCat->children) > 0)
                                        @php $showTop = $topActive || $hasActiveChild; @endphp
                                        <ul id="mobcat_{{ $topCat->id }}" class="collapse ms-3 mt-0 list-unstyled {{ $showTop ? 'show' : '' }}">
                                            @foreach($topCat->children as $subCat)
                                                @php
                                                    $subCatUrl = $subcatRoute($topCat->slug, $subCat->slug);
                                                    $subActive = $mobileCurrentUrl === $subCatUrl || $mobileCurrentUrl === $subCatUrl . '/' || (!empty($activeCategoryUrls) && in_array($subCatUrl, $activeCategoryUrls));
                                                    $hasActiveSubChild = false;
                                                    if (count($subCat->children) > 0 && !$subActive) {
                                                        foreach ($subCat->children as $childCat) {
                                                            $childCatUrl = $subchildRoute($topCat->slug, $subCat->slug, $childCat->slug);
                                                            if ($mobileCurrentUrl === $childCatUrl || $mobileCurrentUrl === $childCatUrl . '/' || (!empty($activeCategoryUrls) && in_array($childCatUrl, $activeCategoryUrls))) {
                                                                $hasActiveSubChild = true; break;
                                                            }
                                                        }
                                                    }
                                                @endphp
                                                <li class="mb-2 {{ $subActive || $hasActiveSubChild ? 'active' : '' }}">
                                                    <div class="d-flex justify-content-between align-items-center gap-3">
                                                        @if($subCat->descendant_count > 0)
                                                            <a href="{{ route('subcategory', ['parent' => $topCat->slug, 'child' => $subCat->slug]) }}" class="text-decoration-none flex-grow-1 {{ $subActive || $hasActiveSubChild ? 'fw-bold' : '' }}" style="color: {{ $subActive || $hasActiveSubChild ? 'var(--primary)' : '#1f0300' }}; font-weight: {{ $subActive || $hasActiveSubChild ? '600' : '400' }}; font-size: 15px;">
                                                                {{ $subCat->name }}
                                                                <span class="text-muted" style="font-size: 12px; font-weight: 400;">({{ $subCat->descendant_count }})</span>
                                                            </a>
                                                        @else
                                                            <span class="flex-grow-1" style="color: #aaa; font-size: 15px;">
                                                                {{ $subCat->name }}
                                                                <span class="text-muted" style="font-size: 12px; font-weight: 400;">(0)</span>
                                                            </span>
                                                        @endif
                                                        @if(count($subCat->children) > 0)
                                                            <button class="btn p-0 border-0 cat-toggle-btn collapsed steve-btn" data-bs-toggle="collapse" data-bs-target="#mobcat_{{ $subCat->id }}">
                                                                <i class="fa-solid fa-plus" style="font-size: 9px;"></i>
                                                                <i class="fa-solid fa-minus" style="font-size: 9px;"></i>
                                                            </button>
                                                        @endif
                                                    </div>

                                                    @if(count($subCat->children) > 0)
                                                        @php $showSub = $subActive || $hasActiveSubChild; @endphp
                                                        <ul id="mobcat_{{ $subCat->id }}" class="collapse ms-3 mt-0 list-unstyled {{ $showSub ? 'show' : '' }}">
                                                            @foreach($subCat->children as $childCat)
                                                                @php
                                                                    $childCatUrl = $subchildRoute($topCat->slug, $subCat->slug, $childCat->slug);
                                                                    $childActive = $mobileCurrentUrl === $childCatUrl || $mobileCurrentUrl === $childCatUrl . '/' || (!empty($activeCategoryUrls) && in_array($childCatUrl, $activeCategoryUrls));
                                                                @endphp
                                                                <li class="my-1 {{ $childActive ? 'active' : '' }}">
                                                                    @if($childCat->descendant_count > 0)
                                                                        <a href="{{ route('subcategory', ['parent' => $topCat->slug, 'child' => $subCat->slug, 'subchild' => $childCat->slug]) }}" class="text-decoration-none {{ $childActive ? 'fw-bold' : '' }}" style="color: {{ $childActive ? 'var(--primary)' : '#1f0300' }}; font-weight: {{ $childActive ? '600' : '400' }}; font-size: 14px;">
                                                                            {{ $childCat->name }}
                                                                            <span class="text-muted" style="font-size: 12px; font-weight: 400;">({{ $childCat->descendant_count }})</span>
                                                                        </a>
                                                                    @else
                                                                        <span style="color: #aaa; font-size: 14px;">
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
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Highlight parent categories when a child is active
    (function() {
        var catTab = document.getElementById('categories');
        if (!catTab) return;
        catTab.querySelectorAll('.product-cat-widget li.active').forEach(function(li) {
            var collapse = li.closest('.collapse');
            while (collapse) {
                var parentLi = collapse.closest('li');
                if (parentLi) {
                    parentLi.classList.add('active');
                    collapse = parentLi.closest('.collapse');
                } else {
                    collapse = null;
                }
            }
        });
    })();
    </script>

    <!-- search bar -->
    <div class="search-bar" id="searchBar">
        <div class="container">
            <div class="row">
                <div class="col">
                    <form class="search-form" action="{{ route('shop') }}" method="GET">
                        <div class="input-group input__group">
                            <input type="text" id="searchInput" class="form-control form__control" name="search" placeholder="I am shopping for..." value="{{ request('search') }}" autocomplete="off">
                            <div class="input-group-append">
                                <span class="search-separator"></span>
                                <button class="dropdown-toggle btn btn-secondary search-category-dropdown steve-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    All Categories
                                </button>
                                @php
                                    $searchCategoryMenu = json_decode(\App\Models\Setting::get('search_category_menu', '[]'), true);
                                    if (!is_array($searchCategoryMenu)) {
                                        $searchCategoryMenu = [];
                                    }
                                    if (empty($searchCategoryMenu)) {
                                        $searchCategoryMenu = [
                                            ['label' => 'Engine Parts', 'url' => url('/category/Engine-Parts')],
                                            ['label' => 'Body & Exterior', 'url' => url('/category/Body-Exterior')],
                                            ['label' => 'Interior Parts', 'url' => url('/category/Interior-Parts')],
                                        ];
                                    }
                                @endphp
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item dropdown__item" href="{{ route('shop') }}">All Categories</a></li>
                                    @foreach($searchCategoryMenu as $item)
                                        <li><a class="dropdown-item dropdown__item" href="{{ $item['url'] ?? '/' }}">{{ $item['label'] ?? '' }}</a></li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="input-group-append">
                                <button class="btn btn-primary search-icn steve-btn" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            <div id="searchSuggestions" class="search-suggestions"></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main>
    @yield('content')
    </main>
    <!-- footer section -->
    <footer class="gs-footer-section">
        <!-- <div class="newslatter newslatter2">
            <div class="container">
                <div class="row newslatter-row">
                    <div class="col-lg-7 col-md-6 col-12 newslatter-area">
                        <div class="newslatter-content">
                            <h2>Subscribe to our newsletter</h2>
                        </div>
                    </div>
                    <div class="col-lg-5 col-md-6 col-12 newslatter-area">
                        <div class="newslatter-form">
                            <form action="javascript:void(0);">
                                @csrf
                                <input class="news-latter-input" type="email" placeholder="Your Email" name="email" required>
                                <button class="newsletter-btn" type="submit">Subscribe</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->
        <div class="container">
            <div class="row footer-row">
                @include('partials.footer-columns')
            </div>
        </div>
        <div class="gs-footer-bottom">
            <div class="container">
                <div class="row">
                    <div class="col-12 text-center py-1">
                        <p class="mb-0 text-secondary">{!! str_replace([':year', 'current_year'], current_year(), \App\Models\Setting::get('footer_copyright', 'COPYRIGHT &copy; :year. All Rights Reserved By STautoparts')) !!}</p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Essential Js Files -->
    <script src="{{ asset('assets/front/js/jquery.min.js') }}"></script>
    @stack('jquery-ui-js')
    @stack('slider-js')
    <script src="{{ asset('assets/front/js/nice-select.js') }}?v={{ filemtime(public_path('assets/front/js/nice-select.js')) }}" defer></script>
    <script src="{{ asset('assets/front/js/wow.js') }}" defer></script>
    <script src="{{ asset('assets/front/js/bootstrap.bundle.min.js') }}" defer></script>
    <script src="{{ asset('assets/front/js/toastr.min.js') }}" defer></script>
    <script src="{{ asset('assets/front/js/script.js') }}?v={{ filemtime(public_path('assets/front/js/script.js')) }}" defer></script>

    <script src="{{ asset('assets/front/js/myscript.js') }}?v={{ filemtime(public_path('assets/front/js/myscript.js')) }}" defer></script>

    <script src="{{ asset('assets/front/js/form-select.js') }}?v={{ filemtime(public_path('assets/front/js/form-select.js')) }}"></script>

    <!-- Session Flash Notifications (must remain inline for Blade PHP) -->
    <script>
    var PLACEHOLDER_IMG = '{{ asset("assets/images/placeholder.png") }}';
    window.searchSuggestionsUrl = "{{ route('search.suggestions') }}";
    </script>
<script>
    @if(session('success'))
        document.addEventListener('DOMContentLoaded', function(){ toastr.success("{{ session('success') }}"); });
    @endif
    @if(session('error'))
        document.addEventListener('DOMContentLoaded', function(){ toastr.error("{{ session('error') }}"); });
    @endif
    @if(session('warning'))
        document.addEventListener('DOMContentLoaded', function(){ toastr.warning("{{ session('warning') }}"); });
    @endif
    @if(session('info'))
        document.addEventListener('DOMContentLoaded', function(){ toastr.info("{{ session('info') }}"); });
    @endif

    // Mobile search toggle
    document.querySelectorAll('[data-target=".front-header-search"]').forEach(function(el) {
        if (!el.closest('.front-header-search')) {
            el.addEventListener('click', function() {
                document.querySelector('.front-header-search')?.classList.toggle('show');
            });
        }
    });
    // Close button
    document.querySelector('.mobile-menu .close')?.addEventListener('click', function() {
        document.querySelector('.mobile-menu')?.classList.remove('active');
        document.querySelector('.overlay')?.classList.remove('active');
    });
    // Overlay click to close
    document.querySelector('.overlay')?.addEventListener('click', function() {
        document.querySelector('.mobile-menu')?.classList.remove('active');
        this.classList.remove('active');
    });
    // Auto-expand parent submenus of active items
    document.querySelectorAll('.mobile-nav-menu li.active').forEach(function(li) {
        var submenu = li.closest('.mobile-submenu');
        while (submenu) {
            var parentLi = submenu.closest('li');
            if (parentLi) {
                parentLi.classList.add('expanded');
                parentLi.querySelector('.mobile-submenu-toggle')?.classList.add('open');
                submenu.classList.add('open');
            }
            submenu = parentLi ? parentLi.closest('.mobile-submenu') : null;
        }
    });
    // Mobile submenu accordion toggle
    document.querySelectorAll('.mobile-submenu-toggle').forEach(function(el) {
        el.addEventListener('click', function(e) {
            e.stopPropagation();
            var li = this.closest('li');
            var submenu = li.querySelector('.mobile-submenu');
            if (submenu) {
                var wasOpen = submenu.classList.contains('open');
                submenu.classList.toggle('open');
                this.classList.toggle('open');
                if (!wasOpen) {
                    li.classList.add('expanded');
                } else if (!submenu.querySelector('li.active')) {
                    li.classList.remove('expanded');
                }
            }
        });
    });
    // Escape key to close mobile menu
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelector('.mobile-menu')?.classList.remove('active');
            document.querySelector('.overlay')?.classList.remove('active');
            document.querySelector('.front-header-search')?.classList.remove('show');
        }
    });
</script>
    @yield('scripts')
    @stack('page-builder-js')

    @include('partials.whatsapp-button')
</body>
</html>