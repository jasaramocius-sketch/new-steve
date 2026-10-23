@extends('layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'compare-page', 'pageClass' => 'compare-page'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Compare Products - ' . config('app.name', 'StAutoparts'),
        'metaTitle' => 'Compare Products | ' . config('app.name', 'StAutoparts'),
        'metaDescription' => 'Compare auto parts and accessories at ' . config('app.name', 'StAutoparts') . '.',
    ])
@endsection

@section('content')


    <!-- Banner Hero Section -->
<section class="gs-breadcrumb-section" style="background-image: url('{{ asset('assets/images/1724480495Imagexxxxxpng.png') }}'); background-size: cover; background-position: center;">
  <div class="container">
    <div class="content-wrapper">
      <h2 class="breadcrumb-title">Compare Products</h2>
      <ul class="bread-menu">
        <li><a href="{{ route('home') }}">Home</a></li>
        <li style="color: var(--primary)">Compare Products</li>
      </ul>
    </div>
  </div>
</section>
<div class="container pt-5 pb-5 compare-page-products">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div></div>

        @if(!$compareItems->isEmpty())
        <button type="button" class="btn btn-danger steve-btn" id="compare-clear-all"
                data-url="{{ route('compare.clear') }}">
            <i class="fas fa-trash"></i> Clear All
        </button>
        @endif
    </div>
    @if($compareItems->isEmpty())
        <div class="alert alert-info text-center" id="compare-empty-msg">
            No items to compare.
        </div>
    @else
    <div class="alert alert-info text-center" id="compare-empty-msg" style="display:none;">
        No items to compare.
    </div>
    <div class="table-responsive bg-white compare-table-wrapper" id="compare-table-wrapper">
        <table class="table compare-table align-middle text-center mb-0">
            <tr>
                <th class="border-top" width="10%">Product Name</th>
                @foreach($compareItems as $item)
                <td class="compare-col border-top" data-compare-id="{{ $item->id }}">
                    <div>
                        <a href="{{ route('product',$item->product->slug) }}">
                    {!! imgTag(storedPath($item->product->image, 'assets/images/thumbnails'), '', 'img-fluid mb-3 compare-page-img', 'style="width:260px; height:260px; object-fit:cover;"') !!}
                        </a>    
                    </div>

                    

                    <a href="{{ route('product',$item->product->slug) }}"
                       class="text-decoration-none">
                        {{ $item->product->name }}
                    </a>
                </td>
                @endforeach
            </tr>
            <tr>
                <th>Price</th>
                @foreach($compareItems as $item)
                    <td class="fw-bold text-danger compare-col" data-compare-id="{{ $item->id }}">
                        {{ currency_format(buyerPrice($item->product->price, $item->product)) }}
                    </td>
                @endforeach
            </tr>
            <tr>
                <th>Old Price</th>
                @foreach($compareItems as $item)
                    <td class="compare-col border-top-1" data-compare-id="{{ $item->id }}">
                        @if($item->product->old_price)
                            <del>{{ currency_format(buyerPrice($item->product->old_price, $item->product)) }}</del>
                        @else
                            -
                        @endif
                    </td>
                @endforeach
            </tr>
            <tr>
                <th>Rating</th>
                @foreach($compareItems as $item)
                    @php
                        $visibleReviews = collect($item->product->reviews_data ?? [])->filter(fn ($r) => ! ($r['deleted'] ?? false) && ($r['approved'] ?? true) !== false);
                        if ($visibleReviews->isEmpty()) {
                            $displayRating = 0;
                            $displayReviews = 0;
                        } else {
                            $displayRating = round($visibleReviews->avg('rating'));
                            $displayReviews = $visibleReviews->count();
                        }
                    @endphp
                    <td class="compare-col" data-compare-id="{{ $item->id }}">
                        @for($i = 0; $i < 5; $i++)
                        <svg xmlns="http://www.w3.org/2000/svg" width="17" height="16" viewBox="0 0 17 16" fill="none">
                            <path d="M8.5 0.5L10.4084 6.37336L16.584 6.37336L11.5878 10.0033L13.4962 15.8766L8.5 12.2467L3.50383 15.8766L5.41219 10.0033L0.416019 6.37336L6.59163 6.37336L8.5 0.5Z" fill="{{ $i < $displayRating ? '#EEAE0B' : '#E2E8F0' }}" />
                        </svg>
                        @endfor
                        {{ $displayRating > 0 ? number_format($displayRating, 1) : '' }}
                        ({{ $displayReviews }})
                    </td>
                @endforeach
            </tr>
            <tr>
                <th>Description</th>
                @foreach($compareItems as $item)
                    <td style="min-width:300px" class="compare-col" data-compare-id="{{ $item->id }}">
                        {{ Str::limit(strip_tags(html_entity_decode((string) $item->product->description)), 150) }}
                    </td>
                @endforeach
            </tr>
            <tr>
                <th>Action</th>
                @foreach($compareItems as $item)
                    <td class="compare-col" data-compare-id="{{ $item->id }}">
                        @if((int) ($item->product->stock ?? 0) > 0)
                        <form action="{{ route('cart.add') }}" method="POST" class="add-cart-form">
                            @csrf
                            <input type="hidden" name="product_id"
                                   value="{{ $item->product->id }}">
                            <input type="hidden" name="product_name"
                                   value="{{ $item->product->name }}">
                            <input type="hidden" name="product_price"
                                   value="{{ buyerPrice($item->product->price, $item->product) }}">
                            <input type="hidden" name="product_image"
                                   value="{{ storedImageUrl($item->product->image, 'assets/images/thumbnails') }}">
                            <button type="submit" class="btn btn-danger mb-2 steve-btn">
                                Add to Cart
                            </button>
                        </form>
                        @else
                            <button type="button" class="btn btn-danger mb-2 steve-btn" disabled>Out of Stock</button>
                        @endif
                        <button type="button"
                                class="btn btn-outline-secondary compare-remove-btn steve-btn"
                                data-url="{{ route('compare.remove', $item->id) }}"
                                data-id="{{ $item->id }}">
                            Remove
                        </button>
                    </td>
                @endforeach
            </tr>
        </table>
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
$(function() {
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    // Update header compare badge
    function updateCompareBadge(count) {
        $('.compare-count').html(count);
        $('#compare-count').html(count);
        if (count > 0) {
            $('.compare-badge').css('display', 'inline-block');
        } else {
            $('.compare-badge').css('display', 'none');
        }
    }

    // Check if table is empty and show empty message
    function checkEmpty() {
        var remaining = $('.compare-col[data-compare-id]').length;
        if (remaining === 0) {
            $('#compare-table-wrapper').fadeOut(300, function() {
                $(this).remove();
            });
            $('#compare-clear-all').fadeOut(200);
            $('#compare-empty-msg').fadeIn(300);
        }
    }

    // Remove single item via AJAX
    $(document).on('click', '.compare-remove-btn', function() {
        var $btn = $(this);
        var url = $btn.data('url');
        var itemId = $btn.data('id');

        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            url: url,
            type: 'DELETE',
            data: { _token: csrfToken },
            dataType: 'json',
            success: function(data) {
                // Remove columns for this item
                $('.compare-col[data-compare-id="' + itemId + '"]').fadeOut(300, function() {
                    $(this).remove();
                    checkEmpty();
                });
                updateCompareBadge(data.count);
                toastr.success(data.message || 'Product removed from compare list.');
            },
            error: function() {
                $btn.prop('disabled', false).html('Remove');
                toastr.error('Failed to remove product. Please try again.');
            }
        });
    });

    // Clear all via AJAX
    $(document).on('click', '#compare-clear-all', function() {
        var $btn = $(this);
        var url = $btn.data('url');

        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Clearing...');

        $.ajax({
            url: url,
            type: 'DELETE',
            data: { _token: csrfToken },
            dataType: 'json',
            success: function(data) {
                $('#compare-table-wrapper').fadeOut(300, function() {
                    $(this).remove();
                });
                $btn.fadeOut(200);
                $('#compare-empty-msg').fadeIn(300);
                updateCompareBadge(0);
                toastr.success(data.message || 'Compare list cleared successfully.');
            },
            error: function() {
                $btn.prop('disabled', false).html('<i class="fas fa-trash"></i> Clear All');
                toastr.error('Failed to clear compare list. Please try again.');
            }
        });
    });
});
</script>
@endsection