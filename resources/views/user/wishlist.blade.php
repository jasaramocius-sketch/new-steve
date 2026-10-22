@extends('user.layouts.dashboard')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'user-wishlist-page', 'pageClass' => 'user-wishlist-page'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'My Wishlist - StAutoparts',
        'metaTitle' => 'My Wishlist | StAutoparts',
        'metaDescription' => 'View your wishlist at StAutoparts.',
    ])
@endsection
@section('dashboard-content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h4 class="h4-style mb-0">My Wishlist</h4>
    @if(count($wishlist) > 0)
    <form action="{{ route('wishlist.clear') }}" method="POST">
        @csrf
        @method('DELETE')
        <button class="btn btn-danger steve-btn">
            <i class="fas fa-trash"></i> Clear All
        </button>
    </form>
    @endif
</div>

<div class="d-grid wishlist-page-products">    
    @forelse($wishlist as $item)
    <div class="wishlist-page-products-items">
        @php
            $product = $item->product ?? $item;
            $wishlistId = isset($item->id) ? $item->id : null;
        @endphp

        @include('partials.product-card', [
            'product' => $product,
            'wishlistItemId' => $wishlistId,
            'showRemoveWishlistIcon' => true,
            'colClass' => 'col-lg-4 col-md-6 mb-4',
        ])
    </div>
    @empty
    </div>
        <div class="col-12">
            <div class="alert alert-info">
                No products in wishlist.
            </div>
        </div>
    @endforelse
</div>
@if(method_exists($wishlist, 'links') && $wishlist->hasPages())
    <div class="pagination-wrapper mt-4 col-12">
        {{ $wishlist->links() }}
    </div>
@endif

@endsection