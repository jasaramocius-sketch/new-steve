@extends('layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'not-found-page', 'pageClass' => 'not-found-page'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => '404 - Page Not Found - ' . config('app.name', 'StAutoparts'),
        'metaTitle' => '404 - Page Not Found | ' . config('app.name', 'StAutoparts'),
        'metaDescription' => 'The page you are looking for could not be found.',
    ])
@endsection
@section('content')

<section class="gs-breadcrumb-section" style="background-image: url('{{ asset('assets/images/1724480495Imagexxxxxpng.png') }}'); background-size: cover; background-position: center;">
    <div class="container">
        <div class="content-wrapper">
            <h2 class="breadcrumb-title">404</h2>
            <ul class="bread-menu">
                <li><a href="{{ route('home') }}">Home</a></li>
                <li style="color: var(--primary)">404</li>
            </ul>
        </div>
    </div>
</section>

<section class="py-5" style="background-color: #F9F8F8;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 text-center">
                <div class="py-5">
                    <h1 class="display-1 fw-bold" style="color: var(--primary);">404</h1>
                    <h3 class="mb-3" style="color: #1f0300;">No results found.</h3>
                    <p class="text-muted mb-4">The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.</p>
                    <a href="{{ route('home') }}" class="btn text-white steve-btn" style="background-color: var(--primary);">
                        <i class="fas fa-home me-2"></i>Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
