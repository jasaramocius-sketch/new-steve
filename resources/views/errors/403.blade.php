@extends('layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'access-denied-page', 'pageClass' => 'access-denied-page'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => '403 - Access Denied - ' . config('app.name', 'StAutoparts'),
        'metaTitle' => '403 - Access Denied | ' . config('app.name', 'StAutoparts'),
        'metaDescription' => 'You do not have permission to access this resource.',
    ])
@endsection
@section('content')

<!-- <section class="gs-breadcrumb-section" style="background-image: url('{{ asset('assets/images/1724480495Imagexxxxxpng.png') }}'); background-size: cover; background-position: center;">
    <div class="container">
        <div class="content-wrapper">
            <h2 class="breadcrumb-title">403</h2>
            <ul class="bread-menu">
                <li><a href="{{ route('home') }}">Home</a></li>
                <li style="color: var(--primary)">Access Denied</li>
            </ul>
        </div>
    </div>
</section> -->

<section class="py-5" style="background-color: #F9F8F8;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center">
                <div class="py-5">
                    <div class="mb-4">
                        <i class="fas fa-lock" style="font-size: 4rem; color: var(--primary); opacity: 0.3;"></i>
                    </div>
                    <h1 class="display-1 fw-bold" style="color: var(--primary);">403</h1>
                    <h3 class="mb-3" style="color: #1f0300;">Access Denied</h3>
                    <p class="text-muted mb-4">
                        @if(!empty($exception) && $exception->getMessage())
                            {{ $exception->getMessage() }}
                        @else
                            You do not have permission to view this resource. 
                            <br>If you believe this is an error, please contact support.
                        @endif
                    </p>
                    
                    <div class="alert alert-info mb-4" style="background-color: #e7f3ff; border-bottom: 4px solid var(--primary);">
                        <small class="text-muted">
                            <strong>Why am I seeing this?</strong><br>
                            You can only view, track, and manage your own orders. If you need to access another order, please ask the order owner or contact our support team.
                        </small>
                    </div>
                    
                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        @auth
                            <a href="{{ route('user.orders') }}" class="btn text-white steve-btn" style="background-color: var(--primary);">
                                <i class="fas fa-shopping-bag me-2"></i>My Orders
                            </a>
                        @endauth
                        <a href="{{ route('home') }}" class="btn text-white steve-btn" style="background-color: var(--primary);">
                            <i class="fas fa-home me-2"></i>Home
                        </a>
                        <a href="{{ route('contact') }}" class="btn text-white steve-btn" style="background-color: #6c757d;">
                            <i class="fas fa-envelope me-2"></i>Contact Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
