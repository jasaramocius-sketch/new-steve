@extends('layouts.app'){{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'user-privacy-policy-page', 'pageClass' => 'user-privacy-policy-page'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => $page->meta_title ?? ('Privacy Policy - ' . config('app.name', 'StAutoparts')),
        'metaTitle' => $page->meta_title ?? ('Privacy Policy | ' . config('app.name', 'StAutoparts')),
        'metaDescription' => $page->meta_description ?? null,
        'robots' => 'index, follow',
    ])
@endsection
@section('content')
<div class="shop-hero py-4">
  <div class="container-fluid px-4">
    <h1 class="mb-1">Privacy Policy</h1>
    <nav><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li><li class="breadcrumb-item active">Privacy Policy</li></ol></nav>
  </div>
</div>
<div class="py-5"><div class="container-fluid px-4">
  <div class="bg-white rounded shadow-sm p-5 text-center">
    <i class="fas fa-tools text-danger mb-3" style="font-size:3rem"></i>
    <h3>Page Coming Soon</h3>
    <p class="text-muted">This page is coming soon. Please check back later.</p>
    <a href="{{ route('home') }}" class="steve-btn mt-3"><i class="fas fa-home me-2"></i>Back {{ 'to' }} Home</a>
  </div>
</div></div>
@endsection
