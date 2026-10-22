@php
    $p = is_array($product) ? (object) $product : $product;
    $pImage = !empty($p->image) ? $p->image : '';
    $cardClass = $cardClass ?? '';
    $titleTag = $titleTag ?? 'h3';
    $titleClass = $titleClass ?? '';
    $priceTag = $priceTag ?? 'h6';
    $viewIcon = $viewIcon ?? 'svg';
    $isWished = !empty($showRemoveWishlistIcon) && $wishlistItemId ? false : in_array($p->id, $wishedProductIds ?? []);
@endphp
<div class="single-product {{ $cardClass }}">
    <div class="img-wrapper single-product-col">
        <div class="product-badges-category">
            @if(!empty($p->badge))
            <span class="product-badge">{{ $p->badge }}</span>
            @endif
        </div>
        @if(!empty($showRemoveWishlistIcon) && $wishlistItemId)
            <form action="{{ route('wishlist.remove', $wishlistItemId) }}" method="POST" class="wishlist-remove-form">
                @csrf
                @method('DELETE')
                <button type="button" class="add-to-wishlist-btn remove-wishlist-btn border-0 bg-transparent steve-btn" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="bottom" aria-label="Remove" data-bs-original-title="Remove">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M18 6L6 18" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M6 6L18 18" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </form>
        @else
            <form action="{{ route('wishlist.add') }}" method="POST" class="wishlist-form">
                @csrf
                <input type="hidden" name="product_id" value="{{ $p->id }}">
                <button type="button" class="add-to-wishlist-btn wishlist-btn border-0 bg-transparent steve-btn" data-product-id="{{ $p->id }}" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="bottom" aria-label="Add to wishlist" data-bs-original-title="Wishlist">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="wishlist-svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M11.9932 5.13581C9.9938 2.7984 6.65975 2.16964 4.15469 4.31001C1.64964 6.45038 1.29697 10.029 3.2642 12.5604C4.89982 14.6651 9.84977 19.1041 11.4721 20.5408C11.6536 20.7016 11.7444 20.7819 11.8502 20.8135C11.9426 20.8411 12.0437 20.8411 12.1361 20.8135C12.2419 20.7819 12.3327 20.7016 12.5142 20.5408C14.1365 19.1041 19.0865 14.6651 20.7221 12.5604C22.6893 10.029 22.3797 6.42787 19.8316 4.31001C17.2835 2.19216 13.9925 2.7984 11.9932 5.13581Z" fill="{{ $isWished ? 'var(--primary)' : 'none' }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="stroke: var(--primary) !important;" />
                    </svg>
                </button>
            </form>
        @endif
        <a href="{{ route('product', $p->slug) }}">
            {!! imgTag(storedPath($pImage, 'assets/images/thumbnails'), $p->name, 'product-img', '', 300) !!}
        </a>
        <div class="add-to-cart">
            <a class="compare_product" href="javascript:;" data-href="{{ route('compare.add', ['product_id' => $p->id]) }}" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="bottom" title="Compare">
                <div class="compare">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M18.1777 8C23.2737 8 23.2737 16 18.1777 16C13.0827 16 11.0447 8 5.43875 8C0.85375 8 0.85375 16 5.43875 16C11.0447 16 13.0828 8 18.1788 8H18.1777Z" stroke="#030712" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
            </a>
            <form action="{{ route('cart.add') }}" method="POST" class="add-cart-form d-inline">
                @csrf
                <input type="hidden" name="product_id" value="{{ $p->id }}">
                <input type="hidden" name="product_name" value="{{ $p->name }}">
                <input type="hidden" name="product_price" value="{{ buyerPrice($p->price) }}">
                <input type="hidden" name="product_image" value="{{ storedImageUrl($pImage, 'assets/images/thumbnails') }}">
                <button type="submit" class="add-cart border-0 steve-btn" @disabled((int) ($p->stock ?? 0) <= 0)>
                    {{ (int) ($p->stock ?? 0) > 0 ? 'Add to Cart' : 'Out of Stock' }}
                </button>
            </form>
            <a href="{{ route('product', $p->slug) }}" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="bottom" title="Quick View">
                @if($viewIcon === 'fa')
                <div class="details">
                    <span class="icon"><i class="fas fa-eye text-dark"></i></span>
                </div>
                @else
                <div class="details">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M2.42012 12.7132C2.28394 12.4975 2.21584 12.3897 2.17772 12.2234C2.14909 12.0985 2.14909 11.9015 2.17772 11.7766C2.21584 11.6103 2.28394 11.5025 2.42012 11.2868C3.54553 9.50484 6.8954 5 12.0004 5C17.1054 5 20.4553 9.50484 21.5807 11.2868C21.7169 11.5025 21.785 11.6103 21.8231 11.7766C21.8517 11.9015 21.8517 12.0985 21.8231 12.2234C21.785 12.3897 21.7169 12.4975 21.5807 12.7132C20.4553 14.4952 17.1054 19 12.0004 19C6.8954 19 3.54553 14.4952 2.42012 12.7132Z" stroke="#030712" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M12.0004 15C13.6573 15 15.0004 13.6569 15.0004 12C15.0004 10.3431 13.6573 9 12.0004 9C10.3435 9 9.0004 10.3431 9.0004 12C9.0004 13.6569 10.3435 15 12.0004 15Z" stroke="#030712" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                @endif
            </a>
        </div>
    </div>
    <div class="content-wrapper single-product-col">
        <a href="{{ route('product', $p->slug) }}" class="text-decoration-none">
            <{{ $titleTag }} class="product-title {{ $titleClass }}">{{ $p->name }}</{{ $titleTag }}>
        </a>
        <div class="price-wrapper">
            <{{ $priceTag }}>{{ currency_format(buyerPrice($p->price)) }}</{{ $priceTag }}>
            @if(!empty($p->old_price))
            <{{ $priceTag }}><del>{{ currency_format(buyerPrice($p->old_price)) }}</del></{{ $priceTag }}>
            @endif
        </div>
        @php
            $visibleCardReviews = collect($p->reviews_data ?? [])->filter(fn ($r) => ! ($r['deleted'] ?? false) && ($r['approved'] ?? true) !== false);
            if ($visibleCardReviews->isEmpty()) {
                $displayRating = 0;
                $displayReviews = 0;
            } else {
                $displayRating = round($visibleCardReviews->avg('rating'));
                $displayReviews = $visibleCardReviews->count();
            }
        @endphp
        @if($displayReviews > 0)
        <div class="ratings-wrapper">
            @for($i = 0; $i < 5; $i++)
            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="16" viewBox="0 0 17 16" fill="none">
                <path d="M8.5 0.5L10.4084 6.37336L16.584 6.37336L11.5878 10.0033L13.4962 15.8766L8.5 12.2467L3.50383 15.8766L5.41219 10.0033L0.416019 6.37336L6.59163 6.37336L8.5 0.5Z" fill="{{ $i < $displayRating ? '#EEAE0B' : '#E2E8F0' }}" />
            </svg>
            @endfor
            <span class="rating-title">{{ $displayRating > 0 ? number_format($displayRating, 1) . ' ' : '' }}({{ $displayReviews }})</span>
        </div>
        @endif
        <div class="add-to-cart">
            <a class="compare_product" href="javascript:;" data-href="{{ route('compare.add', ['product_id' => $p->id]) }}" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="bottom" title="Compare">
                <div class="compare">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M18.1777 8C23.2737 8 23.2737 16 18.1777 16C13.0827 16 11.0447 8 5.43875 8C0.85375 8 0.85375 16 5.43875 16C11.0447 16 13.0828 8 18.1788 8H18.1777Z" stroke="#030712" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
            </a>
            <form action="{{ route('cart.add') }}" method="POST" class="add-cart-form d-inline">
                @csrf
                <input type="hidden" name="product_id" value="{{ $p->id }}">
                <input type="hidden" name="product_name" value="{{ $p->name }}">
                <input type="hidden" name="product_price" value="{{ buyerPrice($p->price) }}">
                <input type="hidden" name="product_image" value="{{ storedImageUrl($pImage, 'assets/images/thumbnails') }}">
                <button type="submit" class="add-cart border-0 steve-btn" @disabled((int) ($p->stock ?? 0) <= 0)>
                    {{ (int) ($p->stock ?? 0) > 0 ? 'Add to Cart' : 'Out of Stock' }}
                </button>
            </form>
            <a href="{{ route('product', $p->slug) }}" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="bottom" title="Quick View">
                @if($viewIcon === 'fa')
                <div class="details">
                    <span class="icon"><i class="fas fa-eye text-dark"></i></span>
                </div>
                @else
                <div class="details">
                    <svg class="icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M2.42012 12.7132C2.28394 12.4975 2.21584 12.3897 2.17772 12.2234C2.14909 12.0985 2.14909 11.9015 2.17772 11.7766C2.21584 11.6103 2.28394 11.5025 2.42012 11.2868C3.54553 9.50484 6.8954 5 12.0004 5C17.1054 5 20.4553 9.50484 21.5807 11.2868C21.7169 11.5025 21.785 11.6103 21.8231 11.7766C21.8517 11.9015 21.8517 12.0985 21.8231 12.2234C21.785 12.3897 21.7169 12.4975 21.5807 12.7132C20.4553 14.4952 17.1054 19 12.0004 19C6.8954 19 3.54553 14.4952 2.42012 12.7132Z" stroke="#030712" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M12.0004 15C13.6573 15 15.0004 13.6569 15.0004 12C15.0004 10.3431 13.6573 9 12.0004 9C10.3435 9 9.0004 10.3431 9.0004 12C9.0004 13.6569 10.3435 15 12.0004 15Z" stroke="#030712" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                @endif
            </a>
        </div>
    </div>
</div>
