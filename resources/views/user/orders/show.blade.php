@extends('user.layouts.dashboard')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'user-order-details-page', 'pageClass' => 'user-order-details-page'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Order Details - StAutoparts',
        'metaTitle' => 'Order Details | StAutoparts',
        'metaDescription' => 'View your order details at StAutoparts.',
    ])
@endsection
@section('dashboard-content')

<style>
/* =========================
   Order Details UI/UX
   ========================= */
.user-order-details-page {

    --order-primary: var(--primary, #ef2b05);
    --order-text: #111827;
    --order-muted: #6b7280;
    --order-border: #e5e7eb;
    --order-bg: #f8fafc;
    --order-success: #16a34a;
    --order-danger: #dc2626;
}

.user-order-details-page .order-page-header {
    margin-bottom: 28px;
}

.user-order-details-page .order-back-btn {
    width: 42px;
    height: 42px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    background: #f1f5f9;
    color: var(--order-text);
    text-decoration: none;
    transition: .2s ease;
}

.user-order-details-page .order-back-btn:hover {
    background: var(--order-primary);
    color: #fff;
}

.user-order-details-page .order-page-title {
    margin: 0;
    color: var(--order-text);
    font-size: clamp(24px, 3vw, 34px);
    font-weight: 700;
}

.user-order-details-page .order-main-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
    padding: 24px;
    margin-bottom: 28px;
    background: #fff;
    border: 1px solid var(--order-border);
    border-radius: 12px;
    box-shadow: 0 4px 18px rgba(15, 23, 42, .04);
}

.user-order-details-page .order-number {
    margin: 0;
    color: var(--order-text);
    font-size: clamp(22px, 3vw, 32px);
    font-weight: 700;
    line-height: 1.25;
    word-break: break-word;
}

.user-order-details-page .order-date {
    margin: 8px 0 0;
    color: var(--order-muted);
    font-size: 14px;
}

.user-order-details-page .order-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-left: 8px;
    padding: 5px 10px;
    border-radius: 999px;
    background: #ecfdf3;
    color: var(--order-success);
    font-size: 13px;
    font-weight: 700;
    vertical-align: middle;
    text-transform: capitalize;
}

.user-order-details-page .order-status-badge.is-cancelled {
    background: #fef2f2;
    color: var(--order-danger);
}

.user-order-details-page .order-print-btn {
    white-space: nowrap;
}

.user-order-details-page .order-status-card {
    padding: 28px 24px 22px;
    margin-bottom: 32px;
    background: #fff;
    border: 1px solid var(--order-border);
    border-radius: 12px;
    box-shadow: 0 4px 18px rgba(15, 23, 42, .04);
}

.user-order-details-page .order-status-title {
    margin: 0 0 24px;
    color: var(--order-text);
    font-size: 16px;
    font-weight: 700;
}

.user-order-details-page .order-status-track {
    position: relative;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
}

.user-order-details-page .order-status-track::before {
    content: "";
    position: absolute;
    top: 20px;
    left: 12.5%;
    right: 12.5%;
    height: 2px;
    background: #e5e7eb;
    z-index: 0;
}

.user-order-details-page .order-status-progress {
    position: absolute;
    top: 20px;
    left: 12.5%;
    height: 2px;
    background: var(--order-success);
    z-index: 1;
    transition: width .35s ease;
}

.user-order-details-page .order-status-step {
    position: relative;
    z-index: 2;
    text-align: center;
}

.user-order-details-page .order-status-dot {
    width: 42px;
    height: 42px;
    margin: 0 auto 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: #f1f5f9;
    color: #000;
    border: 2px solid #e2e8f0;
    font-size: 15px;
    font-weight: 700;
}

.user-order-details-page .order-status-step.is-complete .order-status-dot {
    background: var(--primary);
    border-color: var(--primary);
    color: #fff;
}

.user-order-details-page .order-status-step.is-current .order-status-dot {
    box-shadow: 0 0 0 5px rgba(239, 43, 5, .12);
    background: var(--order-primary);
    border-color: var(--order-primary);
    color: #fff;
}

.user-order-details-page .order-status-label {
    color: var(--order-muted);
    font-size: 14px;
    font-weight: 600;
}

.user-order-details-page .order-status-step.is-complete .order-status-label,
.user-order-details-page .order-status-step.is-current .order-status-label {
    color: var(--order-text);
}

.user-order-details-page .order-status-current {
    display: inline-block;
    margin-top: 5px;
    padding: 3px 8px;
    border-radius: 999px;
    background: #fff1ed;
    color: var(--order-primary);
    font-size: 11px;
    font-weight: 700;
}

.user-order-details-page .order-info-card {
    height: 100%;
    padding: 22px;
    background: #fff;
    border: 1px solid var(--order-border);
    border-radius: 12px;
    box-shadow: 0 4px 18px rgba(15, 23, 42, .04);
}

.user-order-details-page .order-info-title {
    display: flex;
    align-items: center;
    gap: 9px;
    margin: 0 0 17px;
    color: var(--order-text);
    font-size: 17px;
    font-weight: 700;
}

.user-order-details-page .order-info-title i {
    color: var(--order-primary);
    font-size: 15px;
}

.user-order-details-page .order-address-line,
.user-order-details-page .order-contact-line {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 10px;
    color: #374151;
    font-size: 14px;
    line-height: 1.6;
}

.user-order-details-page .order-address-line:last-child,
.user-order-details-page .order-contact-line:last-child {
    margin-bottom: 0;
}

.user-order-details-page .order-address-line svg,
.user-order-details-page .order-contact-line svg {
    flex: 0 0 auto;
    margin-top: 2px;
    color: #64748b;
}

.user-order-details-page .order-info-value {
    margin: 0;
}

.user-order-details-page .order-info-label {
    display: block;
    margin-bottom: 3px;
    color: var(--order-muted);
    font-size: 12px;
    font-weight: 600;
}

.user-order-details-page .order-payment-list {
    display: grid;
    gap: 11px;
}

.user-order-details-page .order-payment-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    color: #374151;
    font-size: 14px;
}

.user-order-details-page .order-payment-row strong {
    color: var(--order-text);
}

.user-order-details-page .order-paid-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 9px;
    border-radius: 999px;
    background: #ecfdf3;
    color: var(--order-success);
    font-size: 12px;
    font-weight: 700;
}

.user-order-details-page .order-unpaid-badge {
    background: #fffbeb;
    color: #b45309;
}

.user-order-details-page .order-cancelled-badge {
    background: #fef2f2;
    color: var(--order-danger);
}

.user-order-details-page .order-shipping-method {
    display: flex;
    align-items: center;
    gap: 14px;
}

.user-order-details-page .order-shipping-icon {
    width: 46px;
    height: 46px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    border-radius: 10px;
    background: #fff1ed;
    color: var(--order-primary);
    font-size: 18px;
}

.user-order-details-page .order-shipping-name {
    margin: 0 0 3px;
    color: var(--order-text);
    font-size: 15px;
    font-weight: 700;
}

.user-order-details-page .order-shipping-subtitle {
    margin: 0;
    color: var(--order-muted);
    font-size: 13px;
}

.user-order-details-page .order-items-section {
    margin-top: 32px;
    padding: 24px;
    background: #fff;
    border: 1px solid var(--order-border);
    border-radius: 12px;
    box-shadow: 0 4px 18px rgba(15, 23, 42, .04);
}

.user-order-details-page .order-items-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    margin-bottom: 18px;
}

.user-order-details-page .order-items-heading h4 {
    margin: 0;
    color: var(--order-text);
    font-size: 21px;
    font-weight: 700;
}

.user-order-details-page .order-item-count {
    padding: 5px 10px;
    border-radius: 999px;
    background: #f1f5f9;
    color: #475569;
    font-size: 12px;
    font-weight: 700;
}

.user-order-details-page .order-items-table {
    min-width: 760px;
    margin-bottom: 0;
}

.user-order-details-page .order-items-table thead th {
    padding: 13px 14px;
    border: 0;
    background: #fff1ed;
    color: #374151;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .03em;
    white-space: nowrap;
}

.user-order-details-page .order-items-table thead th:first-child {
    border-radius: 8px 0 0 8px;
}

.user-order-details-page .order-items-table thead th:last-child {
    border-radius: 0 8px 8px 0;
}

.user-order-details-page .order-items-table tbody td {
    padding: 17px 14px;
    vertical-align: middle;
    border-bottom: 1px solid #eef2f7;
    color: #374151;
    font-size: 14px;
}

.user-order-details-page .order-items-table tbody tr:last-child td {
    border-bottom: 0;
}

.user-order-details-page .order-product-cell {
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 250px;
}

.user-order-details-page .order-product-number {
    width: 34px;
    height: 34px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    border-radius: 8px;
    background: #f8fafc;
    color: var(--order-primary);
    font-size: 12px;
    font-weight: 700;
}

.user-order-details-page .order-product-name {
    color: var(--order-text);
    font-weight: 600;
    text-decoration: none;
    line-height: 1.4;
}

.user-order-details-page .order-product-name:hover {
    color: var(--order-primary);
}

.user-order-details-page .order-review-btn {
    white-space: nowrap;
    font-size: 12px;
    font-weight: 600;
}

.user-order-details-page .order-summary {
    width: min(100%, 390px);
    margin: 24px 0 0 auto;
    padding: 18px;
    border: 1px solid var(--order-border);
    border-radius: 10px;
    background: #f8fafc;
}

.user-order-details-page .order-summary-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    padding: 7px 0;
    color: #4b5563;
    font-size: 14px;
}

.user-order-details-page .order-summary-row.total {
    margin-top: 8px;
    padding-top: 14px;
    border-top: 1px solid #dbe2ea;
    color: var(--order-text);
    font-size: 18px;
    font-weight: 800;
}

.user-order-details-page .order-free {
    color: var(--order-success);
    font-weight: 700;
}

.user-order-details-page .order-review-modal .modal-content {
    overflow: hidden;
    border: 0;
    border-radius: 14px;
    box-shadow: 0 20px 50px rgba(15, 23, 42, .18);
}

@media (max-width: 991.98px) {
    .user-order-details-page .order-main-header {
        padding: 20px;
    }

    .user-order-details-page .order-status-card {
        padding: 24px 16px 20px;
    }

    .user-order-details-page .order-status-track {
        gap: 5px;
    }
}

@media (max-width: 767.98px) {
    .user-order-details-page .order-page-header {
        margin-bottom: 20px;
    }

    .user-order-details-page .order-main-header {
        flex-direction: column;
        padding: 18px;
        margin-bottom: 20px;
    }

    .user-order-details-page .order-print-btn {
        width: 100%;
        justify-content: center;
    }

    .user-order-details-page .order-number {
        font-size: 22px;
    }

    .user-order-details-page .order-status-card {
        padding: 20px 10px;
        overflow-x: auto;
    }

    .user-order-details-page .order-status-track {
        min-width: 590px;
    }

    .user-order-details-page .order-info-card {
        padding: 18px;
    }

    .user-order-details-page .order-items-section {
        padding: 16px;
    }

    .user-order-details-page .order-items-heading h4 {
        font-size: 19px;
    }

    .user-order-details-page .order-summary {
        width: 100%;
    }
}
</style>


@php
    $shipping = is_array($order->shipping_details)
        ? $order->shipping_details
        : json_decode($order->shipping_details, true);

    $shippingFee = $order->shipping_fee ?? 0;
    $taxAmount = $order->tax ?? 0;
    $couponDiscount = $order->coupon_discount ?? 0;
    $totalAmount = $order->total_amount ?? 0;
    $subTotal = $totalAmount - $shippingFee - $taxAmount + $couponDiscount;

    $statusSteps = ['Order Placed', 'On Review', 'On Delivery', 'Delivered'];
    $currentStatus = $order->status ?? 'pending';
    $statusMap = [
        'pending' => 0,
        'processing' => 1,
        'shipped' => 2,
        'delivered' => 3,
        'cancelled' => -1,
    ];
    $currentStep = $statusMap[$currentStatus] ?? 0;
    $deliveryBadges = [
        'pending' => 'badge--warning',
        'processing' => 'badge--info',
        'shipped' => 'badge--primary',
        'delivered' => 'badge--success',
        'cancelled' => 'badge--danger',
    ];
    $statusBadgeClass = $deliveryBadges[$currentStatus] ?? 'badge--secondary';
    $statusBadgeIcon = $currentStatus === 'cancelled' ? 'fa-times-circle' : ($currentStatus === 'delivered' ? 'fa-check-circle' : 'fa-clock');
@endphp
<div class="order-page-header d-flex align-items-center gap-3">
    <a href="{{ route('user.orders') }}" class="order-back-btn" aria-label="Back to orders">
        <i class="fas fa-arrow-left"></i>
    </a>
    <h2 class="order-page-title">Order Details</h2>
</div>

<div class="order-main-header">
    <div>
        <h3 class="order-number">
            Order #{{ $order->order_number ?? $order->id }}
            <span class="badge {{ $statusBadgeClass }}">
                <i class="fas {{ $statusBadgeIcon }} me-1"></i>
                {{ ucfirst($currentStatus) }}
            </span>
        </h3>
        <p class="order-date">
            Placed on {{ optional($order->created_at)->format('d M Y') }}
        </p>
    </div>

    <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
        <a href="{{ route('user.orders.invoice', $order->id) }}"
           target="_blank"
           class="template-btn outline-btn lg-btn steve-btn steve-btn-sm order-print-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                 stroke-linejoin="round" style="margin-right:6px;">
                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                <rect x="6" y="14" width="12" height="8"></rect>
            </svg>
            Print Order
        </a>
        @if($currentStatus === 'delivered')
            <a href="{{ route('user.returns.create', $order->id) }}"
               class="template-btn lg-btn steve-btn steve-btn-sm order-print-btn"
               style="background:var(--primary);color:#fff;">
                <i class="fas fa-undo me-1"></i> Request Return
            </a>
        @endif
    </div>
</div>

<div class="order-status-card">
    <h4 class="order-status-title">Order Tracking</h4>

    @if($currentStatus === 'cancelled')
        <div style="text-align: center; padding: 20px;">
            <div style="font-size: 40px; color: var(--order-danger); margin-bottom: 10px;">
                <i class="fas fa-times-circle"></i>
            </div>
            <h5 style="color: var(--order-danger); font-weight: 700; margin-bottom: 5px;">Order Cancelled</h5>
            <p style="color: var(--order-muted); margin: 0;">This order has been cancelled and will not be delivered.</p>
        </div>
    @else
        <div class="order-status-track">
            @php
                $statusProgress = $currentStep > 0
                    ? min(100, ($currentStep / (count($statusSteps) - 1)) * 100)
                    : 0;
            @endphp

            <span class="order-status-progress" style="width:{{ $statusProgress * 0.75 }}%;"></span>

            @foreach($statusSteps as $index => $step)
                @php
                    $isComplete = $index < $currentStep || ($currentStatus === 'delivered' && $index === 3);
                    $isCurrent = $index === $currentStep;
                @endphp

                <div class="order-status-step {{ $isComplete ? 'is-complete' : '' }} {{ $isCurrent ? 'is-current' : '' }}">
                    <div class="order-status-dot">
                        @if($isComplete)
                            <i class="fas fa-check"></i>
                        @else
                            {{ $index + 1 }}
                        @endif
                    </div>
                    <div class="order-status-label">{{ $step }}</div>
                    @if($isCurrent)
                        <span class="order-status-current">Current</span>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>

@if(count($order->getStatusHistory()) > 1)
<div class="order-status-card mt-4">
    <h4 class="order-status-title">Status History</h4>
    @include('partials.order-status-timeline', ['order' => $order])
</div>
@endif

<div class="row g-4 mt-0">
    <div class="col-lg-6">
        <div class="order-info-card">
            <h5 class="order-info-title">
                <i class="fas fa-map-marker-alt"></i>
                Pickup Address
            </h5>

            @if(!empty($shipping))
                <div class="order-address-line">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                    <p class="order-info-value">
                        {{ ($shipping['address'] ?? '') === "old('address')" ? 'Street Address' : ($shipping['address'] ?? '') }},
                        {{ $shipping['city'] ?? '' }}{{ !empty($shipping['state']) ? ', ' . $shipping['state'] : '' }}
                        {{ !empty($shipping['zip_code']) ? ' - ' . $shipping['zip_code'] : '' }}
                    </p>
                </div>
            @else
                <p class="order-info-value text-muted">Not provided</p>
            @endif
        </div>
    </div>

    <div class="col-lg-6">
        <div class="order-info-card">
            <h5 class="order-info-title">
                <i class="fas fa-file-invoice"></i>
                Billing Address
            </h5>

            @if(!empty($shipping))
                @if(!empty($shipping['name']))
                    <div class="order-contact-line">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <p class="order-info-value">{{ $shipping['name'] }}</p>
                    </div>
                @endif

                <div class="order-contact-line">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                    <p class="order-info-value">
                        {{ ($shipping['address'] ?? '') === "old('address')" ? 'Street Address' : ($shipping['address'] ?? '') }},
                        {{ $shipping['city'] ?? '' }}{{ !empty($shipping['state']) ? ', ' . $shipping['state'] : '' }}
                        {{ !empty($shipping['zip_code']) ? ' - ' . $shipping['zip_code'] : '' }}
                    </p>
                </div>

                @if(!empty($shipping['phone']))
                    <div class="order-contact-line">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                        <p class="order-info-value">{{ $shipping['phone'] }}</p>
                    </div>
                @endif

                @if(!empty($shipping['email']))
                    <div class="order-contact-line">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                        <p class="order-info-value">{{ $shipping['email'] }}</p>
                    </div>
                @endif
            @else
                <p class="order-info-value text-muted">Not provided</p>
            @endif
        </div>
    </div>

    <div class="col-lg-6">
        <div class="order-info-card">
            <h5 class="order-info-title">
                <i class="fas fa-credit-card"></i>
                Payment Information
            </h5>

            <div class="order-payment-list">
                <div class="order-payment-row">
                    <span>Payment Status</span>
                    @if($currentStatus == 'delivered')
                        <span class="badge badge--success"><i class="fas fa-check me-1"></i> Paid</span>
                    @elseif($currentStatus == 'cancelled')
                        <span class="badge badge--danger"><i class="fas fa-times me-1"></i> Cancelled</span>
                    @else
                        <span class="badge badge--warning"><i class="fas fa-clock me-1"></i> Unpaid</span>
                    @endif
                </div>

                <div class="order-payment-row">
                    <span>Tax</span>
                    <strong>{{ currency_format($taxAmount) }}</strong>
                </div>

                <div class="order-payment-row">
                    <span>Paid Amount</span>
                    <strong>{{ currency_format($totalAmount) }}</strong>
                </div>

                <div class="order-payment-row">
                    <span>Payment Method</span>
                    <strong>{{ $order->payment_method ? ucfirst(str_replace('_', ' ', $order->payment_method)) : 'Cash On Delivery' }}</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="order-info-card">
            <h5 class="order-info-title">
                <i class="fas fa-truck"></i>
                Shipping Method
            </h5>

            <div class="order-shipping-method">
                <span class="order-shipping-icon">
                    <i class="fas fa-truck"></i>
                </span>
                <div>
                    <p class="order-shipping-name">{{ ucfirst($order->delivery_type ?? 'Free Shipping') }}</p>
                    <p class="order-shipping-subtitle">
                        {{ $currentStatus === 'delivered' ? 'Order delivered successfully' : 'Delivery information' }}
                    </p>
                    @if(!empty($order->tracking_number))
                        <div class="mt-3 p-3" style="background:#f8fafc;border:1px dashed #dbe2ea;border-radius:8px;">
                            <p class="order-shipping-subtitle mb-1">
                                <strong>Tracking Number:</strong>
                                <span class="fw-bold" style="color:var(--order-text);">{{ $order->tracking_number }}</span>
                            </p>
                            @if(!empty($order->tracking_carrier))
                                <p class="order-shipping-subtitle mb-0">
                                    <strong>Carrier:</strong> {{ $order->tracking_carrier }}
                                </p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="order-items-section">
    <div class="order-items-heading">
        <h4>Purchase Items</h4>
        <span class="order-item-count">
            {{ $order->items->count() }} {{ $order->items->count() === 1 ? 'Item' : 'Items' }}
        </span>
    </div>

    <div class="table-responsive">
        <table class="table order-items-table">
            <thead>
                <tr>
                    <th width="40%">Product</th>
                    <th>Variation</th>
                    <th>Quantity</th>
                    <th>Delivery</th>
                    <th>Price</th>
                    <th>Review</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $key => $item)
                    <tr>
                        <td>
                            <div class="order-product-cell">
                                <span class="order-product-number">{{ sprintf('%02d', $key + 1) }}</span>

                                @if($item->product)
                                    <a href="{{ route('product', $item->product->slug ?? '') }}"
                                       target="_blank"
                                       class="order-product-name">
                                        {{ $item->product->name }}
                                    </a>
                                @else
                                    <span class="text-muted">Product Unavailable</span>
                                @endif
                            </div>
                        </td>

                        <td>{{ $item->variation ?? '—' }}</td>
                        <td>{{ $item->quantity ?? $item->qty ?? 1 }}</td>
                        <td>{{ ucfirst($order->delivery_type ?? 'Home Delivery') }}</td>
                        <td><strong>{{ currency_format($item->price ?? 0) }}</strong></td>

                        <td>
                            @if(!empty($reviewedSlugs[$item->product_id]))
                                <button type="button"
                                        class="btn btn-sm btn-outline-success rounded-pill px-3 order-review-btn"
                                        disabled>
                                    <i class="fas fa-check-circle me-1"></i> Reviewed
                                </button>
                            @elseif($item->product)
                                <a href="javascript:void(0);"
                                   class="btn btn-sm btn-outline-dark rounded-pill px-3 write-review-btn order-review-btn"
                                   data-product-slug="{{ $item->product->slug }}"
                                   data-product-name="{{ $item->product->name }}"
                                   data-bs-toggle="modal"
                                   data-bs-target="#orderReviewModal"
                                   onclick="openOrderReview(this)">
                                    <i class="far fa-star me-1"></i> Write Review
                                </a>
                            @else
                                <span class="text-muted">Unavailable</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="order-summary">
        <div class="order-summary-row">
            <span>Subtotal</span>
            <strong>{{ currency_format($subTotal) }}</strong>
        </div>
        <div class="order-summary-row">
            <span>Tax</span>
            <strong>{{ currency_format($taxAmount) }}</strong>
        </div>
        <div class="order-summary-row">
            <span>Shipping</span>
            <strong class="{{ (float)$shippingFee <= 0 ? 'order-free' : '' }}">
                {{ (float)$shippingFee <= 0 ? 'FREE' : currency_format($shippingFee) }}
            </strong>
        </div>
        @if($couponDiscount > 0)
        <div class="order-summary-row">
            <span>Coupon Discount @if(!empty($order->coupon_code))<small class="text-muted">({{ $order->coupon_code }})</small>@endif</span>
            <strong class="text-success">-{{ currency_format($couponDiscount) }}</strong>
        </div>
        @endif
        <div class="order-summary-row total">
            <span>Total</span>
            <strong>{{ currency_format($totalAmount) }}</strong>
        </div>
    </div>
</div>

<!-- Write Review Modal -->
<div class="modal fade" id="orderReviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-zoom">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-600">Write a Review</h5>
                <button type="button" class="btn-close steve-btn" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body gry-bg px-3 pt-3">
                <div id="orderReviewAlert" class="d-none mb-3"></div>
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-14" id="orderReviewProductLabel">Product</label>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-14">Your Rating <span class="text-danger">*</span></label>
                    <div class="star-picker" id="orderStarPicker">
                        @for($i = 1; $i <= 5; $i++)
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="21" viewBox="0 0 17 16" fill="none" data-rating="{{ $i }}" style="cursor:pointer;transition:fill .2s;">
                            <path d="M8.5 0.5L10.4084 6.37336L16.584 6.37336L11.5878 10.0033L13.4962 15.8766L8.5 12.2467L3.50383 15.8766L5.41219 10.0033L0.416019 6.37336L6.59163 6.37336L8.5 0.5Z" fill="#E2E8F0" />
                        </svg>
                        @endfor
                        <input type="hidden" id="orderReviewRating" value="0">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-14">Your Review <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="orderReviewText" rows="4" maxlength="1000" placeholder="Share your experience with this product..."></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold fs-14">Review Images</label>
                    <input type="file" id="orderReviewImagesInput" multiple accept="image/jpg,image/jpeg,image/png,image/webp" class="d-none">
                    <button type="button" class="btn btn-outline-secondary btn-sm steve-btn" id="orderReviewImagesBrowseBtn"><i class="fas fa-cloud-upload-alt"></i> Browse</button>
                    <span class="text-muted ms-2" style="font-size:12px;">Max 5 images, 2MB each</span>
                    <div id="orderReviewImagesPreview" class="d-flex flex-wrap gap-2 mt-2"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary fw-600 steve-btn" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary fw-600 steve-btn" id="orderSubmitReviewBtn">Submit Review</button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var currentSlug = null;
    var orderReviewImages = [];
    var starPicker = document.getElementById('orderStarPicker');
    var ratingInput = document.getElementById('orderReviewRating');
    var stars = starPicker ? starPicker.querySelectorAll('svg') : [];
    var imagesInput = document.getElementById('orderReviewImagesInput');
    var browseBtn = document.getElementById('orderReviewImagesBrowseBtn');
    var previewEl = document.getElementById('orderReviewImagesPreview');
    var alertEl = document.getElementById('orderReviewAlert');
    var submitBtn = document.getElementById('orderSubmitReviewBtn');

    function resetStars() {
        var val = parseInt(ratingInput.value) || 0;
        stars.forEach(function (s) {
            s.querySelector('path').setAttribute('fill', parseInt(s.dataset.rating) <= val ? '#EEAE0B' : '#E2E8F0');
        });
    }

    if (starPicker) {
        stars.forEach(function (star) {
            star.addEventListener('mouseenter', function () {
                var val = parseInt(star.dataset.rating);
                stars.forEach(function (s) {
                    s.querySelector('path').setAttribute('fill', parseInt(s.dataset.rating) <= val ? '#EEAE0B' : '#E2E8F0');
                });
            });
            star.addEventListener('click', function () {
                ratingInput.value = star.dataset.rating;
                resetStars();
            });
        });
        starPicker.addEventListener('mouseleave', resetStars);
    }

    if (browseBtn) browseBtn.addEventListener('click', function () { imagesInput.click(); });
    if (imagesInput) {
        imagesInput.addEventListener('change', function (e) {
            Array.from(e.target.files).forEach(function (file) {
                if (orderReviewImages.length >= 5) return;
                if (file.size > 2 * 1024 * 1024) { alert(file.name + ' is larger than 2MB.'); return; }
                if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) { alert(file.name + ' is not supported.'); return; }
                orderReviewImages.push(file);
            });
            renderImages();
            e.target.value = '';
        });
    }

    function renderImages() {
        if (!previewEl) return;
        previewEl.innerHTML = '';
        orderReviewImages.forEach(function (file, idx) {
            var div = document.createElement('div');
            div.style.cssText = 'position:relative;width:60px;height:60px;border-radius:6px;overflow:hidden;border:1px solid #ddd;';
            var img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.style.cssText = 'width:100%;height:100%;object-fit:cover;';
            var rm = document.createElement('button');
            rm.type = 'button';
            rm.innerHTML = '&times;';
            rm.style.cssText = 'position:absolute;top:2px;right:2px;background:rgba(0,0,0,0.6);color:#fff;border:none;border-radius:50%;width:18px;height:18px;font-size:10px;cursor:pointer;display:flex;align-items:center;justify-content:center;';
            rm.addEventListener('click', function () { orderReviewImages.splice(idx, 1); renderImages(); });
            div.appendChild(img);
            div.appendChild(rm);
            previewEl.appendChild(div);
        });
    }

    window.openOrderReview = function (el) {
        currentSlug = el.getAttribute('data-product-slug');
        var label = document.getElementById('orderReviewProductLabel');
        if (label) label.textContent = 'Product: ' + el.getAttribute('data-product-name');
        ratingInput.value = 0;
        document.getElementById('orderReviewText').value = '';
        orderReviewImages = [];
        renderImages();
        alertEl.className = 'd-none mb-3';
        submitBtn.disabled = false;
        submitBtn.textContent = 'Submit Review';
        resetStars();
    };

    if (submitBtn) {
        submitBtn.addEventListener('click', function () {
            var rating = parseInt(ratingInput.value);
            var text = document.getElementById('orderReviewText').value.trim();

            function showAlert(msg) {
                alertEl.className = 'alert alert-danger mb-3';
                alertEl.textContent = msg;
            }

            if (!currentSlug) { showAlert('Product not found.'); return; }
            if (!rating || rating < 1 || rating > 5) { showAlert('Please select a rating.'); return; }
            if (!text) { showAlert('Please write your review.'); return; }

            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';

            var formData = new FormData();
            formData.append('rating', rating);
            formData.append('text', text);
            orderReviewImages.forEach(function (file) { formData.append('images[]', file); });

            fetch('{{ route("product.review", "__SLUG__") }}'.replace('__SLUG__', currentSlug), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(function (r) { return r.json().then(function (body) { return { status: r.status, body: body }; }); })
            .then(function (res) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit Review';
                if (res.status === 401) {
                    showAlert('Please <a href="{{ route('login') }}">login</a> to write a review.');
                } else if (res.status === 403) {
                    showAlert(res.body.message || 'You cannot review this product.');
                } else if (res.body.success) {
                    var success = document.getElementById('orderReviewAlert');
                    success.className = 'alert alert-success mb-3';
                    success.innerHTML = 'Review submitted successfully! <a href="{{ route('product', '__SLUG__') }}'.replace('__SLUG__', currentSlug) + '">View your review</a>.';
                    setTimeout(function () {
                        var modalEl = document.getElementById('orderReviewModal');
                        var modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                        modal.hide();
                        var link = document.querySelector('.write-review-btn[data-product-slug="' + currentSlug + '"]');
                        if (link) { link.textContent = 'Reviewed'; link.style.pointerEvents = 'none'; link.style.opacity = '0.6'; }
                    }, 1500);
                } else {
                    showAlert(res.body.message || 'Something went wrong. Please try again.');
                }
            })
            .catch(function () {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit Review';
                showAlert('Something went wrong. Please try again.');
            });
        });
    }
})();
</script>

@endsection