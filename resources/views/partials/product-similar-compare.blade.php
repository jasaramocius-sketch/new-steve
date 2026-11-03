@php
    $similarItems = collect($similar ?? [])->take(2);
    $all = collect([$current])->merge($similarItems)->take(3);
@endphp

@if($all->count() > 1)
<section class="gs-similar-compare-area mt-5">
    <div class="container">
    <div class="title pb-20">
        <h3 style="font-weight: 600;">Similar Products To Compare</h3>
        <p class="text-muted">Compare this part with alternatives from other brands</p>
    </div>
    <div class="table-responsive bg-white border">
        <table class="table table-bordered align-middle text-center compare-similar-table mb-0">
            <tr>
                <th class="compare-row-label" width="10%" style="vertical-align: middle;"></th>
                @foreach($all as $p)
                <td class="similar-col p-0">
                    <div class="compare-product-image-wrapper">
                        <a class="compare-product-image" href="{{ route('product', $p->slug) }}" style="display: flex; margin: 0 auto;">
                            {!! imgTag(storedPath($p->image, 'assets/images/thumbnails'), '', 'img-fluid mb-2', 'style="height:100%; width:100%; object-fit:cover;"') !!}
                        </a>
                    </div>
                    @if($p->id === $product['id'])
                        <span class="badge bg-primary-subtle text-white border border-primary-subtle mt-1">Current Product</span>
                    @endif
                </td>
                @endforeach
            </tr>
            <tr>
                <th class="compare-row-label">Product</th>
                @foreach($all as $p)
                    <td class="similar-col"  style="text-transform: uppercase;">
                        <a href="{{ route('product', $p->slug) }}" class="d-block text-decoration-none small fw-semibold p-10">
                        {{ $p->name }}
                    </a>
                    </td>
                @endforeach
            </tr>
            <tr>
                <th class="compare-row-label">Brand</th>
                @foreach($all as $p)
                    <td class="similar-col"  style="text-transform: uppercase;">
                        {{ $p->brand->name ?? '—' }}
                    </td>
                @endforeach
            </tr>

            <tr>
                <th class="compare-row-label">Category</th>
                @foreach($all as $p)
                    <td class="similar-col"  style="text-transform: uppercase;">
                        {{ $p->category->name ?? '—' }}
                    </td>
                @endforeach
            </tr>
            <tr>
                <th class="compare-row-label">Price</th>
                @foreach($all as $p)
                    <td class="similar-col fw-bold text-danger">
                        {{ currency_format(buyerPrice($p->price, $p)) }}
                        @if($p->old_price)
                            <div><del class="text-muted small">{{ currency_format(buyerPrice($p->old_price, $p)) }}</del></div>
                        @endif
                    </td>
                @endforeach
            </tr>
            <tr>
                <th class="compare-row-label">Rating</th>
                @foreach($all as $p)
                    @php
                        $visReviews = collect($p->reviews_data ?? [])->filter(fn ($r) => ! ($r['deleted'] ?? false) && ($r['approved'] ?? true) !== false);
                        if ($visReviews->isEmpty()) { $dispRating = 0; $dispReviews = 0; }
                        else { $dispRating = round($visReviews->avg('rating')); $dispReviews = $visReviews->count(); }
                    @endphp
                    <td class="similar-col">
                        @for($i = 0; $i < 5; $i++)
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="13" viewBox="0 0 17 16" fill="none" class="d-inline">
                            <path d="M8.5 0.5L10.4084 6.37336L16.584 6.37336L11.5878 10.0033L13.4962 15.8766L8.5 12.2467L3.50383 15.8766L5.41219 10.0033L0.416019 6.37336L6.59163 6.37336L8.5 0.5Z" fill="{{ $i < $dispRating ? '#EEAE0B' : '#E2E8F0' }}" />
                        </svg>
                        @endfor
                        <span class="small text-muted">{{ $dispRating > 0 ? number_format($dispRating, 1) : '' }} ({{ $dispReviews }})</span>
                    </td>
                @endforeach
            </tr>
            <tr>
                <th class="compare-row-label">Part Number</th>
                @foreach($all as $p)
                    <td class="similar-col">
                        @if($p->part_number)
                            {{ $p->part_number }}
                        @else
                            <span class="text-muted">Universal</span>
                        @endif
                    </td>
                @endforeach
            </tr>
            <tr>
                <th class="compare-row-label">Availability</th>
                @foreach($all as $p)
                    <td class="similar-col">
                        @if((int) ($p->stock ?? 0) > 0)
                            <span class="badge bg-success-subtle text-success border border-success-subtle">In Stock</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Out of Stock</span>
                        @endif
                    </td>
                @endforeach
            </tr>
            <tr>
                <th class="compare-row-label">Description</th>
                @foreach($all as $p)
                    <td class="similar-col small text-muted" style="max-width:260px;">
                        {{ \Illuminate\Support\Str::limit(strip_tags(html_entity_decode((string) $p->overview)), 90) }}
                    </td>
                @endforeach
            </tr>
            <tr>
                <th class="compare-row-label">Action</th>
                @foreach($all as $p)
                    <td class="similar-col">
                        <div class="d-flex gap-1 justify-content-center">
                            @if((int) ($p->stock ?? 0) > 0)
                                <form action="{{ route('cart.add') }}" method="POST" class="add-cart-form">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $p->id }}">
                                    <input type="hidden" name="product_name" value="{{ $p->name }}">
                                    <input type="hidden" name="product_price" value="{{ buyerPrice($p->price, $p) }}">
                                    <input type="hidden" name="product_image" value="{{ storedImageUrl($p->image, 'assets/images/thumbnails') }}">
                                    <button type="submit" class="btn btn-danger btn-sm steve-btn w-100">Add to Cart</button>
                                </form>
                            @else
                                <!-- <span class="text-muted small">Out of Stock</span> -->
                            @endif
                            <a href="{{ route('product', $p->slug) }}" class="btn btn-outline-secondary btn-sm steve-btn">View Details</a>
                        </div>
                    </td>
                @endforeach
            </tr>
        </table>
    </div>
</div>
</section>
@endif