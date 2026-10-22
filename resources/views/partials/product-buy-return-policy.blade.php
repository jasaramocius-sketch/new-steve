@php
    // Per-product Buy / Return Policy.
    // Custom policy_text (set from the admin product form) wins; otherwise a
    // unique, product-specific policy is auto-generated from product attributes.

    $customPolicy = trim((string) ($product['policy_text'] ?? ''));
@endphp

@if($customPolicy !== '')
    {!! $customPolicy !!}
@else
    @php
        $pName  = trim((string) ($product['name'] ?? ''));
        $pBrand = $product->brand->name ?? null;
        $pCat   = $product->category->name ?? null;
        $pYear  = $product['year'] ?? null;
        $pMake  = $product['make'] ?? null;
        $pModel = $product['model'] ?? null;
        $pPrice = is_numeric($product['price'] ?? null) ? number_format(buyerPrice($product['price']), 2) : null;

        $identifiers = array_filter([$pYear, $pMake, $pModel]);
        $fitment = implode(' / ', $identifiers);

        $badge = strtolower(trim((string) ($product['badge'] ?? '')));
        $isDiscount = (bool) preg_match('/off|sale|deal|discount/i', $badge);
        $isNew      = (bool) preg_match('/new/i', $badge);

        $subject = !empty($pName) ? $pName : 'This item';
    @endphp
    <div class="product-policy-body">
        <p class="mb-3"><strong>{{ $subject }}</strong>
            @if($pBrand) ({{ $pBrand }})@endif
            @if($pCat) — {{ $pCat }}@endif
            @if($pPrice) — <span class="text-muted">USD {{ $pPrice }} @endif</span>
        </p>

        <h6 class="mb-2">Buy / Return Policy</h6>
        <p class="mb-2">We want you to be completely satisfied with your purchase. If you need to return {{ $subject }}, you may do so within <strong>30 calendar days</strong> from the date you received it.</p>

        @if($isDiscount)
            <p class="mb-2"><strong>Discounted / Sale item:</strong> Despite being offered at a promotional price, this item is covered under the standard return window above, provided it is returned in its original, unused condition.</p>
        @elseif($isNew)
            <p class="mb-2"><strong>New item:</strong> This product is brand new and covered under the standard return window above. Please keep it in its original, unused packaging to be eligible.</p>
        @endif

        @if($fitment !== '')
            <p class="mb-2"><strong>Fitment / Compatibility:</strong> Please confirm {{ $subject }} matches your vehicle — {{ $fitment }} — before installing. Correct fitment can be checked against the product details before you return the item.</p>
        @endif

        <h6 class="mt-3 mb-2">Eligibility for Return</h6>
        <p class="mb-2">To be eligible for a return, {{ $subject }} must be:</p>
        <ul class="mb-2">
            <li>Unused and in the same condition you received it</li>
            <li>In the original packaging with all tags and accessories</li>
            <li>Returned within the 30-day return window</li>
        </ul>

        <h6 class="mt-3 mb-2">Refunds</h6>
        <p class="mb-2">Once we receive your returned item, we will inspect it and notify you of the status of your refund. If your return is approved, we will initiate a refund to your original method of payment.</p>

        <h6 class="mt-3 mb-2">Return Shipping</h6>
        <p class="mb-2">You are responsible for paying your own shipping costs for returning {{ $subject }}. Shipping costs are non-refundable, and if you receive a refund the cost of return shipping will be deducted from it.</p>

        <p class="mb-0">For full details, please see our <a href="{{ route('return.policy') }}">Return Policy</a>. If you have any questions about returning {{ $subject }}, <a href="{{ route('contact') }}">contact us</a>.</p>
    </div>
@endif