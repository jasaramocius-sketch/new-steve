@php
    $rvIdList = collect(session('recently_viewed', []))->map(fn ($id) => (int) $id)->filter()->take(4)->values()->all();
    $rvProducts = collect();
    if (count($rvIdList) > 0) {
        $rvProducts = \App\Models\Product::whereIn('id', $rvIdList)
            ->where('status', true)
            ->orderByRaw('FIELD(id,'.implode(',', $rvIdList).')')
            ->get();
    }
@endphp
@if($rvProducts->count() > 0)
    <section class="gs-product-cards-slider-area recently-viewed-section">
        <div class="container">
            <div class="title text-center m-0 pb-20">
                <h3 style="font-weight: 600;">Recently Viewed</h3>
                <p class="text-muted">Products you checked out recently</p>
            </div>
            <div class="related-products-grid d-grid">
                @foreach($rvProducts as $rvProduct)
                    <div class="grid-item">
                        @include("partials.product-card", [
                            "product" => $rvProduct,
                            "cardClass" => "h-100 shadow-sm overflow-hidden",
                            "titleTag" => "h5",
                            "titleClass" => "mb-2",
                            "priceTag" => "h4",
                            "wishedProductIds" => $wishedProductIds ?? [],
                        ])
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif