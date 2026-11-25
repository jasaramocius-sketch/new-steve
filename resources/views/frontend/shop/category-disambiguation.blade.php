@extends('layouts.app')
@include('partials.page-attributes', ['pageId' => 'category-disambiguation-page', 'pageClass' => 'category-disambiguation-page'])

@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Select Category - Steve Autoparts',
        'metaTitle' => 'Select Category | Steve Autoparts',
        'metaDescription' => 'Multiple categories share this name. Select the correct category.',
        'robots' => 'noindex,follow'
    ])
@endsection

@section('content')
@php
    $breadcrumbItems = [
        ['label' => 'Home', 'url' => route('home')],
        ['label' => 'Shop', 'url' => route('shop')],
        ['label' => 'Select Category'],
    ];
@endphp

@include('partials.breadcrumb', [
    'title' => 'Select Category',
    'items' => $breadcrumbItems,
])

<section class="py-120" style="background-color: #F9F8F8;">
    <div class="container">
        <div class="bg-white border rounded shadow-sm p-4 p-md-5">
            <h2 class="mb-2" style="font-size: 28px;">Multiple categories found</h2>
            <p class="text-muted mb-4">
                "{{ str_replace('-', ' ', $slug) }}" There is more than one category for the name. Please select the correct category..
            </p>

            <div class="list-group">
                @foreach($candidates as $candidate)
                    @php
                        $chain = collect($candidate['chain'] ?? []);
                        $label = $chain->pluck('name')->implode(' / ');
                    @endphp
                    <a href="{{ $candidate['url'] }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center gap-3 py-3">
                        <div>
                            <div style="font-weight: 600; color: #1f0300;">{{ $candidate['category']->name ?? 'Category' }}</div>
                            <div class="text-muted small">{{ $label }}</div>
                        </div>
                        <span class="text-muted" aria-hidden="true"><i class="fa-solid fa-chevron-right"></i></span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endsection
