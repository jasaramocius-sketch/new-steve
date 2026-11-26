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

        <div class="table-responsive bg-white compare-table-wrapper">
            <table class="table compare-table align-middle text-center mb-0">
                <tr>
                    <th class="border-top" width="10%"></th>
                    @foreach($all as $p)
                    <td class="compare-col similar-col border-top" data-product-id="{{ $p->id }}">
                        <div>
                            <a href="{{ route('product', $p->slug) }}">
                                {!! imgTag(storedPath($p->image, 'assets/images/thumbnails'), '', 'img-fluid mb-3 compare-page-img', 'style="width:260px; height:260px; object-fit:cover;"') !!}
                            </a>
                        </div>
                        @if($p->id === $current->id)
                            <span class="badge bg-primary-subtle text-white border border-primary-subtle mt-1">Current Product</span>
                        @endif
                    </td>
                    @endforeach
                </tr>

                <tr>
                    <th>Product Name</th>
                    @foreach($all as $p)
                        <td class="fw-bold text-danger compare-col similar-col">
                            <a href="{{ route('product', $p->slug) }}" class="text-decoration-none">
                                {{ $p->name }}
                            </a>
                        </td>
                    @endforeach
                </tr>

                <tr>
                    <th>Brand</th>
                    @foreach($all as $p)
                        <td class="fw-bold text-danger compare-col similar-col">
                            {{ $p->brand->name ?? 'N/A' }}
                        </td>
                    @endforeach
                </tr>

                <tr>
                    <th>Category</th>
                    @foreach($all as $p)
                        <td class="fw-bold text-danger compare-col similar-col">
                            {{ $p->category->name ?? 'N/A' }}
                        </td>
                    @endforeach
                </tr>

                <tr>
                    <th>Price</th>
                    @foreach($all as $p)
                        <td class="fw-bold text-danger compare-col similar-col">
                            {{ currency_format(buyerPrice($p->price, $p)) }}
                            @if($p->old_price)
                                <div><del class="text-muted small">{{ currency_format(buyerPrice($p->old_price, $p)) }}</del></div>
                            @endif
                        </td>
                    @endforeach
                </tr>

                <tr>
                    <th>Rating</th>
                    @foreach($all as $p)
                        @php
                            $visReviews = collect($p->reviews_data ?? [])->filter(fn ($r) => ! ($r['deleted'] ?? false) && ($r['approved'] ?? true) !== false);
                            if ($visReviews->isEmpty()) {
                                $dispRating = 0;
                                $dispReviews = 0;
                            } else {
                                $dispRating = round($visReviews->avg('rating'));
                                $dispReviews = $visReviews->count();
                            }
                        @endphp
                        <td class="compare-col similar-col">
                            @for($i = 0; $i < 5; $i++)
                            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="16" viewBox="0 0 17 16" fill="none">
                                <path d="M8.5 0.5L10.4084 6.37336L16.584 6.37336L11.5878 10.0033L13.4962 15.8766L8.5 12.2467L3.50383 15.8766L5.41219 10.0033L0.416019 6.37336L6.59163 6.37336L8.5 0.5Z" fill="{{ $i < $dispRating ? '#EEAE0B' : '#E2E8F0' }}" />
                            </svg>
                            @endfor
                            {{ $dispRating > 0 ? number_format($dispRating, 1) : '' }}
                            ({{ $dispReviews }})
                        </td>
                    @endforeach
                </tr>

                <tr>
                    <th class="compare-row-label">Part Number</th>
                    @foreach($all as $p)
                        <td class="compare-col similar-col">
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
                        <td class="compare-col similar-col">
                            @if((int) ($p->stock ?? 0) > 0)
                                <span class="badge bg-success-subtle text-success border border-success-subtle">In Stock</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Out of Stock</span>
                            @endif
                        </td>
                    @endforeach
                </tr>

                <tr>
                    <th>Description</th>
                    @foreach($all as $p)
                        <td style="min-width:300px" class="compare-col similar-col">
                            {{ \Illuminate\Support\Str::limit(strip_tags(html_entity_decode((string) $p->overview)), 150) }}
                        </td>
                    @endforeach
                </tr>

                <tr>
                    <th>Action</th>
                    @foreach($all as $p)
                        <td class="compare-col similar-col compare-action-btn">
                            <div class="gap-1">
                                @if((int) ($p->stock ?? 0) > 0)
                                <form action="{{ route('cart.add') }}" method="POST" class="add-cart-form">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $p->id }}">
                                    <input type="hidden" name="product_name" value="{{ $p->name }}">
                                    <input type="hidden" name="product_price" value="{{ buyerPrice($p->price, $p) }}">
                                    <input type="hidden" name="product_image" value="{{ storedImageUrl($p->image, 'assets/images/thumbnails') }}">
                                    <button type="submit" class="btn btn-danger steve-btn">Add to Cart</button>
                                </form>
                                @else
                                    <button type="button" class="btn btn-danger steve-btn" disabled>Out of Stock</button>
                                @endif
                                <a href="{{ route('product', $p->slug) }}" class="btn btn-outline-secondary steve-btn">View Details</a>
                            </div>
                        </td>
                    @endforeach
                </tr>
            </table>
        </div>
    </div>
</section>
@endif
