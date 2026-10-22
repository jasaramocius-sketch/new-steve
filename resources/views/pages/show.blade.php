@extends('layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'user-show-page', 'pageClass' => 'user-show-page'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => $page->meta_title ?: $page->title,
        'metaTitle' => $page->meta_title,
        'metaDescription' => $page->meta_description,
        'robots' => 'index, follow',
    ])
@endsection
@section('content')

@if($page->image)
<section class="page-title-banner page-title-banner-image" style="background-image:url('{{ storedImageUrl($page->image, 'assets/images/pages') }}'); background-size:cover; background-position:center;">
@endif
<div class="container">
    <div class="row justify-content-center">
        @if ($page->show_title)
        <div class="col-lg-12 mb-4 py-5">
            
                <h1 class="page-title">{{ $page->title }}</h1>
            
            @if ($page->short_description)
                <p class="lead page-short-description">{{ $page->short_description }}</p>
            @endif
        </div>
         @endif
            <div class="col-lg-12">
                @if ($page->content && trim(strip_tags($page->content)) !== '')
                <div class="page-content">
                    {!! $page->content !!}
                </div>
                @endif
            </div>
    </div>
</div>
@if($page->image)
</section>
@endif

<x-page-blocks :model="$page" />

<style>
    .page-content p { margin-bottom: 1rem; line-height: 1.8; }
    .page-content img { max-width: 100%; height: auto; border-radius: 8px; margin: 1rem 0; }
    .page-title-banner { border-radius: 0px; background-repeat: no-repeat; }
    .page-title-banner-image .page-title,
    .page-title-banner-image .page-short-description { color: #fff; text-shadow: 0 2px 8px rgba(0,0,0,.55); }
</style>

@endsection
