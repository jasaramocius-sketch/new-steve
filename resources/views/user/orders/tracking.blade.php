@php $trackingIsGuest = !auth()->check(); @endphp
@extends($trackingIsGuest ? 'layouts.app' : 'user.layouts.dashboard')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'user-order-tracking-page', 'pageClass' => 'user-order-tracking-page'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Order Tracking - StAutoparts',
        'metaTitle' => 'Order Tracking | StAutoparts',
        'metaDescription' => 'Track your orders and view their status at StAutoparts.',
    ])
@endsection
@section($trackingIsGuest ? 'content' : 'dashboard-content')

<style>
/* =========================
   Order Tracking UI/UX
   ========================= */
.gs-order-track-section {
    --track-primary: var(--primary, #ef2b05);
    --track-text: #111827;
    --track-muted: #6b7280;
    --track-border: #e5e7eb;
    --track-success: #16a34a;
    --track-danger: #dc2626;
    --track-bg: #f8fafc;
}

.gs-order-track-section .track-page-header {
    margin-bottom: 28px;
}

.gs-order-track-section .track-page-title {
    margin: 0;
    color: var(--track-text);
    font-size: clamp(25px, 3vw, 34px);
    font-weight: 700;
}

.gs-order-track-section .track-page-subtitle {
    margin: 8px 0 0;
    color: var(--track-muted);
    font-size: 15px;
    line-height: 1.6;
}

.gs-order-track-section .track-search-card {
    padding: 24px;
    margin-bottom: 28px;
    background: #fff;
    border: 1px solid var(--track-border);
    border-radius: 12px;
    box-shadow: 0 4px 18px rgba(15, 23, 42, .04);
}

.gs-order-track-section .track-search-heading {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
}

.gs-order-track-section .track-search-icon {
    width: 44px;
    height: 44px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    border-radius: 10px;
    background: #fff1ed;
    color: var(--track-primary);
}

.gs-order-track-section .track-search-heading h4 {
    margin: 0;
    color: var(--track-text);
    font-size: 18px;
    font-weight: 700;
}

.gs-order-track-section .track-search-heading p {
    margin: 3px 0 0;
    color: var(--track-muted);
    font-size: 13px;
}

.gs-order-track-section .track-form-row {
    display: flex;
    align-items: flex-end;
    gap: 14px;
}

.gs-order-track-section .track-form-field {
    flex: 1 1 auto;
}

.gs-order-track-section .track-form-field label {
    display: block;
    margin-bottom: 8px;
    color: #374151;
    font-size: 13px;
    font-weight: 600;
}

.gs-order-track-section .track-form-field .form-control {
    min-height: 40px;
    border-color: #dbe2ea;
    border-radius: 8px;
    box-shadow: none;
}

.gs-order-track-section .track-form-field .form-control:focus {
    border-color: var(--track-primary);
    box-shadow: 0 0 0 3px rgba(239, 43, 5, .10);
}

.gs-order-track-section .track-submit {
    min-height: 40px;
    white-space: nowrap;
    border-radius: 8px;
}

.gs-order-track-section .track-error {
    display: block;
    margin-top: 6px;
    color: var(--track-danger);
    font-size: 12px;
}

.gs-order-track-section .track-result-card {
    padding: 24px;
    background: #fff;
    border: 1px solid var(--track-border);
    border-radius: 12px;
    box-shadow: 0 4px 18px rgba(15, 23, 42, .04);
}

.gs-order-track-section .track-result-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 18px;
    margin-bottom: 28px;
}

.gs-order-track-section .track-order-number {
    margin: 0;
    color: var(--track-text);
    font-size: clamp(21px, 2.5vw, 28px);
    font-weight: 700;
    line-height: 1.3;
    word-break: break-word;
}

.gs-order-track-section .track-order-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-left: 8px;
    padding: 5px 10px;
    border-radius: 999px;
    background: #ecfdf3;
    color: var(--track-success);
    font-size: 12px;
    font-weight: 700;
    vertical-align: middle;
}

.gs-order-track-section .track-order-status.cancelled {
    background: #fef2f2;
    color: var(--track-danger);
}

.gs-order-track-section .track-result-date {
    margin: 7px 0 0;
    color: var(--track-muted);
    font-size: 13px;
}

.gs-order-track-section .track-status-box {
    padding: 24px 18px 20px;
    margin-bottom: 24px;
    background: #f8fafc;
    border: 1px solid #edf1f5;
    border-radius: 10px;
}

.gs-order-track-section .track-status-heading {
    margin: 0 0 22px;
    color: var(--track-text);
    font-size: 15px;
    font-weight: 700;
}

.gs-order-track-section .track-status-track {
    position: relative;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
}

.gs-order-track-section .track-status-track::before {
    content: "";
    position: absolute;
    top: 20px;
    left: 12.5%;
    right: 12.5%;
    height: 2px;
    background: #e2e8f0;
    z-index: 0;
}

.gs-order-track-section .track-status-progress {
    position: absolute;
    top: 20px;
    left: 12.5%;
    height: 2px;
    background: var(--track-success);
    z-index: 1;
    transition: width .35s ease;
}

.gs-order-track-section .track-status-step {
    position: relative;
    z-index: 2;
    text-align: center;
}

.gs-order-track-section .track-status-dot {
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 9px;
    border: 2px solid #e2e8f0;
    border-radius: 50%;
    background: #fff;
    color: #94a3b8;
    font-size: 14px;
    font-weight: 700;
}

.gs-order-track-section .track-status-step.complete .track-status-dot {
    background: var(--track-success);
    border-color: var(--track-success);
    color: #fff;
}

.gs-order-track-section .track-status-step.current .track-status-dot {
    background: var(--track-primary);
    border-color: var(--track-primary);
    color: #fff;
    box-shadow: 0 0 0 5px rgba(239, 43, 5, .12);
}

.gs-order-track-section .track-status-label {
    color: var(--track-muted);
    font-size: 13px;
    font-weight: 600;
}

.gs-order-track-section .track-status-step.complete .track-status-label,
.gs-order-track-section .track-status-step.current .track-status-label {
    color: var(--track-text);
}

.gs-order-track-section .track-current-label {
    display: inline-block;
    margin-top: 5px;
    padding: 3px 8px;
    border-radius: 999px;
    background: #fff1ed;
    color: var(--track-primary);
    font-size: 10px;
    font-weight: 700;
}

.gs-order-track-section .track-summary-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
    margin-bottom: 28px;
}

.gs-order-track-section .track-summary-item {
    padding: 17px;
    border: 1px solid var(--track-border);
    border-radius: 10px;
    background: #fff;
}

.gs-order-track-section .track-summary-label {
    display: block;
    margin-bottom: 6px;
    color: var(--track-muted);
    font-size: 12px;
    font-weight: 600;
}

.gs-order-track-section .track-summary-value {
    margin: 0;
    color: var(--track-text);
    font-size: 15px;
    font-weight: 700;
}

.gs-order-track-section .track-products {
    overflow: hidden;
    border: 1px solid var(--track-border);
    border-radius: 10px;
}

.gs-order-track-section .track-products-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 18px 20px;
    border-bottom: 1px solid var(--track-border);
}

.gs-order-track-section .track-products-header h5 {
    margin: 0;
    color: var(--track-text);
    font-size: 17px;
    font-weight: 700;
}

.gs-order-track-section .track-products-count {
    padding: 5px 9px;
    border-radius: 999px;
    background: #f1f5f9;
    color: #475569;
    font-size: 11px;
    font-weight: 700;
}

.gs-order-track-section .track-products-table {
    min-width: 650px;
    margin: 0;
}

.gs-order-track-section .track-products-table thead th {
    padding: 12px 16px;
    border: 0;
    background: var(--primary);
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .03em;
}

.gs-order-track-section .track-products-table tbody td {
    padding: 16px;
    vertical-align: middle;
    border-bottom: 1px solid #eef2f7;
    color: #374151;
    font-size: 14px;
}

.gs-order-track-section .track-products-table tbody tr:last-child td {
    border-bottom: 0;
}

.gs-order-track-section .track-product-name {
    color: var(--track-text);
    font-weight: 600;
}

.gs-order-track-section .track-empty-card {
    padding: 40px 24px;
    text-align: center;
    background: #fff;
    border: 1px solid var(--track-border);
    border-radius: 12px;
    box-shadow: 0 4px 18px rgba(15, 23, 42, .04);
}

.gs-order-track-section .track-empty-icon {
    width: 54px;
    height: 54px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 12px;
    border-radius: 50%;
    background: #fef2f2;
    color: var(--track-danger);
}

.gs-order-track-section .track-empty-card h5 {
    margin: 0;
    color: var(--track-text);
    font-size: 17px;
    font-weight: 700;
}

.gs-order-track-section .track-empty-card p {
    margin: 7px auto 0;
    max-width: 470px;
    color: var(--track-muted);
    font-size: 14px;
    line-height: 1.6;
}

@media (max-width: 767.98px) {
    .gs-order-track-section .track-search-card,
    .gs-order-track-section .track-result-card {
        padding: 18px;
    }

    .gs-order-track-section .track-form-row {
        display: block;
    }

    .gs-order-track-section .track-submit {
        width: 100%;
        margin-top: 12px;
    }

    .gs-order-track-section .track-result-header {
        flex-direction: column;
        margin-bottom: 20px;
    }

    .gs-order-track-section .track-status-box {
        overflow-x: auto;
        padding: 20px 10px;
    }

    .gs-order-track-section .track-status-track {
        min-width: 590px;
    }

    .gs-order-track-section .track-summary-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="gs-order-track-section">

    <div class="track-page-header">
        <h4 class="h4-style mb-0">Order Tracking</h4>
        <p class="track-page-subtitle">
            Enter your order number to view the latest delivery status and order details.
        </p>
    </div>

    <div class="track-search-card">
        <div class="track-search-heading">
            <span class="track-search-icon">
                <i class="fas fa-search"></i>
            </span>
            <div>
                <h4>Track Your Order</h4>
                <p>Enter your order number or ID below.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('order.tracking') }}">
            @csrf

            <div class="track-form-row">
                <div class="track-form-field">
                    <label for="order_number">Order Number / ID</label>
                    <input
                        type="text"
                        name="order_number"
                        id="order_number"
                        class="form-control"
                        value="{{ old('order_number', request('order_number')) }}"
                        placeholder="e.g. ORD6********A462"
                        autocomplete="off"
                    >
                </div>

                <div class="track-form-field">
                    <label for="track_email">Billing Email @guest<span class="text-danger">*</span>@endguest</label>
                    <input
                        type="email"
                        name="email"
                        id="track_email"
                        class="form-control"
                        value="{{ old('email', auth()->user()->email ?? '') }}"
                        placeholder="you@example.com"
                        autocomplete="off"
                        @guest required @endguest
                    >
                </div>

                <button type="submit" class="template-btn btn-forms steve-btn track-submit">
                    <i class="fas fa-search me-1"></i>
                    Track Order
                </button>
            </div>
            @error('order_number')
                <small class="track-error">{{ $message }}</small>
            @enderror
            @error('email')
                <small class="track-error">{{ $message }}</small>
            @enderror
        </form>
    </div>

    @if(request()->isMethod('post') && isset($order))
        @if($order)

            @php
                $statusSteps = ['Order Placed', 'On Review', 'On Delivery', 'Delivered'];

                $statusMap = [
                    'pending' => 0,
                    'processing' => 1,
                    'shipped' => 2,
                    'delivered' => 3,
                    'cancelled' => -1,
                ];

                $currentStep = $statusMap[$order->status] ?? 0;
                $statusProgress = $currentStep > 0
                    ? min(100, ($currentStep / (count($statusSteps) - 1)) * 100)
                    : 0;
            @endphp

            <div class="track-result-card">

                <div class="track-result-header">
                    <div>
                        <h3 class="track-order-number">
                            Order #{{ $order->order_number ?? $order->id }}

                            <span class="track-order-status {{ $order->status === 'cancelled' ? 'cancelled' : '' }}">
                                <i class="fas {{ $order->status === 'cancelled' ? 'fa-times-circle' : 'fa-check-circle' }}"></i>
                                {{ ucfirst($order->status) }}
                            </span>
                        </h3>

                        <p class="track-result-date">
                            Placed on {{ optional($order->created_at)->format('d M Y') }}
                        </p>
                    </div>
                </div>

                <div class="track-status-box">
                    <h4 class="track-status-heading">Order Progress</h4>

                    @if($order->status === 'cancelled')
                        <div style="text-align: center; padding: 20px;">
                            <div style="font-size: 40px; color: #dc2626; margin-bottom: 10px;">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <h5 style="color: #dc2626; font-weight: 700; margin-bottom: 5px;">Order Cancelled</h5>
                            <p style="color: #6b7280; margin: 0;">This order has been cancelled and will not be delivered.</p>
                        </div>
                    @else
                        <div class="track-status-track">
                            <span
                                class="track-status-progress"
                                style="width:{{ $statusProgress * 0.75 }}%;"
                            ></span>

                            @foreach($statusSteps as $i => $step)
                                @php
                                    $isComplete = $i < $currentStep || ($order->status === 'delivered' && $i === 3);
                                    $isCurrent = $i === $currentStep;
                                @endphp

                                <div class="track-status-step {{ $isComplete ? 'complete' : '' }} {{ $isCurrent ? 'current' : '' }}">
                                    <div class="track-status-dot">
                                        @if($isComplete)
                                            <i class="fas fa-check"></i>
                                        @else
                                            {{ $i + 1 }}
                                        @endif
                                    </div>

                                    <div class="track-status-label">{{ $step }}</div>

                                    @if($isCurrent)
                                        <span class="track-current-label">Current</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    @if(!empty($order->tracking_number))
                        <div class="mt-4 p-3" style="background:#fff;border:1px dashed #dbe2ea;border-radius:8px;">
                            <p class="mb-1" style="color:#6b7280;font-size:13px;font-weight:600;">TRACKING NUMBER</p>
                            <p class="mb-0" style="color:#111827;font-size:16px;font-weight:700;">
                                {{ $order->tracking_number }}
                                @if(!empty($order->tracking_carrier))
                                    <span class="track-summary-label" style="display:inline-block;margin-left:8px;">({{ $order->tracking_carrier }})</span>
                                @endif
                            </p>
                        </div>
                    @endif
                </div>

                @if(count($order->getStatusHistory()) > 1)
                <div class="track-status-box mt-4">
                    <h4 class="track-status-heading">Status History</h4>
                    @include('partials.order-status-timeline', ['order' => $order])
                </div>
                @endif

                <div class="track-summary-grid">
                    <div class="track-summary-item">
                        <span class="track-summary-label">Order Date</span>
                        <p class="track-summary-value">
                            {{ optional($order->created_at)->format('d M Y') }}
                        </p>
                    </div>

                    <div class="track-summary-item">
                        <span class="track-summary-label">Total Amount</span>
                        <p class="track-summary-value">
                            {{ currency_format($order->total_amount ?? 0) }}
                        </p>
                    </div>

                    <div class="track-summary-item">
                        <span class="track-summary-label">Delivery Type</span>
                        <p class="track-summary-value">
                            {{ ucfirst($order->delivery_type ?? 'Free Shipping') }}
                        </p>
                    </div>
                </div>

                <div class="track-products">
                    <div class="track-products-header">
                        <h5>Ordered Products</h5>
                        <span class="track-products-count">
                            {{ $order->items->count() }}
                            {{ $order->items->count() === 1 ? 'Item' : 'Items' }}
                        </span>
                    </div>

                    <div class="table-responsive">
                        <table class="table track-products-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Variation</th>
                                    <th>Qty</th>
                                    <th>Price</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($order->items as $item)
                                    <tr>
                                        <td>
                                            <span class="track-product-name"><a href="{{ route('product', $item->product->slug) }}">
                                                {{ $item->product->name ?? 'Product Unavailable' }}</a>
                                            </span>
                                        </td>
                                        <td>{{ $item->variation ?? '—' }}</td>
                                        <td>{{ $item->quantity ?? $item->qty ?? 1 }}</td>
                                        <td>
                                            <strong>{{ currency_format($item->price ?? 0) }}</strong>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        @else

            <div class="track-empty-card">
                <span class="track-empty-icon">
                    <i class="fas fa-search"></i>
                </span>

                <h5>Order Not Found</h5>

                <p>
                    No order was found with the provided order number.
                    Please check the number and try again.
                </p>
            </div>

        @endif
    @endif

</div>

@endsection