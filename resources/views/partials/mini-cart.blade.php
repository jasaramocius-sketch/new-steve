@php $cart = session('cart', []); @endphp
@if(count($cart) > 0)
    <div style="max-height: 300px; overflow-y: auto;">
        <ul class="list-group list-group-flush">
            @foreach($cart as $item)
                <li class="list-group-item d-flex align-items-center gap-3 px-3 py-3 cart-icon-dropdown-items">
                    {!! imgTag(storedPath($item['image'], 'assets/images/thumbnails'), $item['name'], 'cart-icon-dropdown-item-img', 'style="width:50px;height:50px;object-fit:cover;"') !!}
                    <div class="flex-grow-1 min-w-0 cart-icon-dropdown-item-details">
                        <a href="{{ route('cart') }}" class="text-dark fw-600 fs-14 text-truncate d-block">{{ $item['name'] }}</a>
                        <div class="fs-13 text-secondary">
                            {{ $item['qty'] }} x {{ currency_format($item['price']) }}
                        </div>
                    </div>
                    <form action="{{ route('cart.remove') }}" method="POST" class="m-0 gs-mini-cart-remove-form">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $item['id'] }}">
                        <button type="submit" class="btn btn-sm p-0 border-0 fs-16 text-secondary steve-btn">
                            <i class="las la-times"></i>
                        </button>
                    </form>
                </li>
            @endforeach
        </ul>
    </div>
    <div class="px-3 py-3 border-top">
        <div class="d-flex justify-content-between mb-3 fs-14 fw-600">
            <span>Subtotal</span>
            <span class="text-primary cart-total-price">{{ currency_format($cartTotal ?? 0) }}</span>
        </div>
        <div class="d-flex gap-2 border-top cart-actions pt-3 mini-cart-actions">
            <a href="{{ route('cart') }}" class="btn btn-warning btn-sm btn-block rounded-4 text-white w-100">
                View Cart
            </a>
            <a href="{{ route('checkout') }}" class="btn btn-primary btn-sm btn-block rounded-4 text-white w-100">
                Checkout
            </a>
        </div>
    </div>
@else
    <div class="text-center p-3">
        <i class="las la-frown la-3x opacity-60 mb-3" style="font-size:3rem;color:#b5b5bf;"></i>
        <h3 class="h6 fw-700">Your Cart is empty</h3>
    </div>
@endif