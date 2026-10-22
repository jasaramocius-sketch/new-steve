@extends('admin.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-orders-show-page', 'pageClass' => 'admin-orders-show-page'])
@section('page-title', 'Order Number' . ' #' . $order->order_number)
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Order Detail - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection

@section('content')

@php
    $shipping = is_array($order->shipping_details) ? $order->shipping_details : json_decode($order->shipping_details, true) ?? [];
    $shippingFee = $order->shipping_fee ?? 0;
    $taxAmount   = $order->tax ?? 0;
    $couponDiscount = $order->coupon_discount ?? 0;
    $totalAmount = $order->total_amount ?? 0;
    $subTotal    = $totalAmount - $shippingFee - $taxAmount + $couponDiscount;

    $paymentDetails = is_array($order->payment_details) ? $order->payment_details : json_decode($order->payment_details, true);
    $paymentDetails = is_array($paymentDetails) ? $paymentDetails : [];
    $transactionId = $paymentDetails['transaction_id'] ?? '';

    $orderBadge = match($order->status) {
        'pending'    => 'bg-light text-warning border border-warning-subtle',
        'processing' => 'bg-light text-info border border-info-subtle',
        'shipped'    => 'bg-light text-primary border border-primary-subtle',
        'delivered'  => 'bg-light text-success border border-success-subtle',
        'cancelled'  => 'bg-light text-danger border border-danger-subtle',
        default      => 'bg-light text-secondary border border-secondary-subtle',
    };

    $paymentBadge = match($order->payment_status) {
        'paid'     => 'bg-light text-success border border-success-subtle',
        'refunded' => 'bg-light text-secondary border border-secondary-subtle',
        default    => 'bg-light text-warning border border-warning-subtle',
    };
@endphp

<div class="d-flex flex-wrap align-items-center justify-content-between pb-3 border-bottom gap-3">
    <div>
        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
            <!-- <a href="{{ route('admin.orders.index') }}" class="text-decoration-none text-muted"><i class="fas fa-arrow-left me-1"></i> Back</a> -->
            <h4 class="mb-0 fw-bold">Order #{{ $order->order_number }}</h4>
            <span class="badge {{ $orderBadge }}">{{ ucfirst($order->status) }}</span>
            <span class="badge {{ $paymentBadge }}">{{ ucfirst($order->payment_status ?? 'unpaid') }}</span>
        </div>
        <small class="text-muted">Placed on {{ $order->created_at->format('M d, Y \a\t h:i A') }}</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.orders.invoice', $order->id) }}" target="_blank" class="btn btn-outline-secondary steve-btn">
            <i class="fas fa-print me-1"></i> Print Invoice
        </a>
        <form action="{{ route('admin.orders.email-customer', $order->id) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-outline-primary steve-btn">
                <i class="fas fa-envelope me-1"></i> Email Customer
            </button>
        </form>
    </div>
</div>

<form action="{{ route('admin.orders.update-status', $order->id) }}" method="POST" id="updateOrderForm">
    @csrf
    <div class="row g-3 mt-0">

        {{-- Left column --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">Order Items ({{ $order->items->count() }})</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Product</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th class="text-end">Total</th>
                                <th class="pe-3 text-end">Coupon Discount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($order->items as $item)
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="{{ asset(storedImageUrl($item->product->image ?? null, 'assets/images/thumbnails')) }}"
                                             alt="{{ $item->product->name ?? 'Product' }}"
                                             class="rounded"
                                             style="width:64px;height:64px;object-fit:cover;background:#f8f9fa;"
                                             onerror="this.src='{{ asset('assets/images/placeholder.png') }}'">
                                        <div>
                                            <h6 class="mb-0 fw-medium">{{ $item->product->name ?? 'Product #' . $item->product_id }}</h6>
                                            @if(!empty($order->coupon_code))
                                                <span class="text-muted small">Coupon: {{ $order->coupon_code }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="text-nowrap">{{ currency_format($item->price) }}</td>
                                <td>{{ $item->qty }}</td>
                                <td class="text-end">{{ currency_format($item->price * $item->qty) }}</td>
                                <td class="pe-3 text-end {{ ($item->coupon_discount ?? 0) > 0 ? 'text-danger fw-medium' : 'text-muted' }}">{{ ($item->coupon_discount ?? 0) > 0 ? '-' . currency_format($item->coupon_discount) : currency_format(0) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No items found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-light border-top py-3">
                    <div class="row justify-content-end">
                        <div class="col-5">
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="text-muted">Subtotal</span>
                                <span class="fw-medium">{{ currency_format($subTotal) }}</span>
                            </div>
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="text-muted">Shipping Fee</span>
                                <span class="fw-medium">{{ currency_format($shippingFee) }}</span>
                            </div>
                            @if($taxAmount > 0)
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="text-muted">Tax</span>
                                <span class="fw-medium">{{ currency_format($taxAmount) }}</span>
                            </div>
                            @endif
                            @if($couponDiscount > 0)
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="text-muted">Coupon ({{ $order->coupon_code ?? '—' }})</span>
                                <span class="fw-medium text-danger">-{{ currency_format($couponDiscount) }}</span>
                            </div>
                            @endif
                            <div class="d-flex justify-content-between fw-bold pt-2 border-top mt-2">
                                <span>Total</span>
                                <span>{{ currency_format($totalAmount) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Status History</h5>
                </div>
                <div class="card-body">
                    @include('partials.order-status-timeline', ['order' => $order])
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Update Order Status</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted text-uppercase">Order Status</label>
                            <select name="status" class="form-select">
                                <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                                <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted text-uppercase">Payment Status</label>
                            <select name="payment_status" class="form-select">
                                <option value="unpaid" {{ ($order->payment_status ?? 'unpaid') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                <option value="paid" {{ ($order->payment_status ?? '') === 'paid' ? 'selected' : '' }}>Paid</option>
                                <option value="refunded" {{ ($order->payment_status ?? '') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                            </select>
                        </div>
                        <div class="col-12" id="transactionIdGroup">
                            <label class="form-label fw-semibold small text-muted text-uppercase">Transaction ID</label>
                            <div id="transactionIdPaid">
                                <div class="input-group">
                                    <input type="text" name="transaction_id" class="form-control" value="" placeholder="e.g. pay_xxxxxxxxxxxx">
                                    <button type="button" class="btn btn-outline-secondary steve-btn" id="generateTxnBtn" title="Generate a random Transaction ID">
                                        <i class="fas fa-dice me-1"></i> Generate
                                    </button>
                                </div>
                                <small class="text-muted d-block">Must be unique. Can only be set once, and only when payment is Paid.</small>
                            </div>
                            <div id="transactionIdLocked" style="display:none;">
                                <input type="text" class="form-control" value="{{ $transactionId }}" readonly>
                                <small class="text-muted d-block">Transaction ID is locked and can be set only once.</small>
                            </div>
                            <div id="transactionIdNotPaid" style="display:none;">
                                <input type="text" class="form-control" value="" disabled placeholder="Transaction ID">
                                <small class="text-muted d-block">Transaction ID can only be set when Payment Status is Paid.</small>
                            </div>
                        </div>
                    <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted text-uppercase">Tracking Number</label>
                            <input type="text" name="tracking_number" class="form-control" value="{{ $order->tracking_number }}" placeholder="e.g. SPX123456789">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted text-uppercase">Tracking Carrier</label>
                            <input type="text" name="tracking_carrier" class="form-control" value="{{ $order->tracking_carrier }}" placeholder="e.g. DHL / FedEx / USPS">
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary steve-btn gap-1"><i class="fas fa-sync"></i> Update</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right column --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>Customer Details</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        @php
                            $initials = strtoupper(substr($order->user->name ?? 'G', 0, 1));
                        @endphp
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold"
                             style="width:40px;height:40px;font-size:14px;">
                            {{ $initials }}
                        </div>
                        <div>
                            <p class="mb-0 fw-medium">{{ $order->user->name ?? 'Guest' }}</p>
                            <small class="text-muted">Customer since {{ $order->user?->created_at?->format('Y') ?? 'N/A' }}</small>
                        </div>
                    </div>
                    <div class="small">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="far fa-envelope text-muted"></i>
                            <span>{{ $order->user->email ?? '—' }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-phone text-muted"></i>
                            <span>{{ $order->user->phone ?? '—' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-truck me-2"></i>Shipping Address</h5>
                </div>
                <div class="card-body">
                    <div class="small">
                        <div class="d-flex align-items-start gap-2 mb-2">
                            <i class="fas fa-user text-muted mt-1"></i>
                            <span>{{ $shipping['name'] ?? $order->user->name ?? 'N/A' }}</span>
                        </div>
                        @if(!empty($shipping['address']))
                        <div class="d-flex align-items-start gap-2 mb-2">
                            <i class="fas fa-map-marker-alt text-muted mt-1"></i>
                            <span>{{ $shipping['address'] }}</span>
                        </div>
                        @endif
                        @if(!empty($shipping['city']) || !empty($shipping['state']) || !empty($shipping['zip_code']))
                        <div class="d-flex align-items-start gap-2 mb-2">
                            <i class="fas fa-building text-muted mt-1"></i>
                            <span>{{ $shipping['city'] ?? '' }}{{ ($shipping['city'] ?? '') && ($shipping['state'] ?? '') ? ', ' : '' }}{{ $shipping['state'] ?? '' }}, {{ $shipping['zip_code'] ?? '' }}</span>
                        </div>
                        @endif
                        @if(!empty($shipping['country']))
                        <div class="d-flex align-items-start gap-2">
                            <i class="fas fa-globe text-muted mt-1"></i>
                            <span>{{ $shipping['country'] }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Payment Info</h5>
                </div>
                <div class="card-body">
                    <div class="small">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Method</span>
                            <span class="fw-medium">{{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'N/A')) }}</span>
                        </div>
                        @if($transactionId !== '')
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Transaction ID</span>
                                <span class="fw-medium text-truncate ms-2" style="max-width:160px;" title="{{ $transactionId }}">
                                    {{ $transactionId }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</form>

@push('scripts')
<script>
(function() {
    var txnIdExists = @json($transactionId !== '');
    var paySelect = document.querySelector('select[name="payment_status"]');
    var group = document.getElementById('transactionIdGroup');
    if (!paySelect || !group) return;

    var paidDiv = document.getElementById('transactionIdPaid');
    var lockedDiv = document.getElementById('transactionIdLocked');
    var notPaidDiv = document.getElementById('transactionIdNotPaid');

    function applyState() {
        if (txnIdExists) {
            paidDiv.style.display = 'none';
            notPaidDiv.style.display = 'none';
            lockedDiv.style.display = 'block';
            return;
        }
        var val = paySelect.value;
        if (val === 'paid') {
            lockedDiv.style.display = 'none';
            notPaidDiv.style.display = 'none';
            paidDiv.style.display = 'block';
        } else {
            paidDiv.style.display = 'none';
            lockedDiv.style.display = 'none';
            notPaidDiv.style.display = 'block';
        }
    }

    function randomTxnId() {
        var chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        var rnd = '';
        for (var i = 0; i < 24; i++) {
            rnd += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return 'pay_' + rnd;
    }

    var txnInput = document.querySelector('#transactionIdPaid input[name="transaction_id"]');
    var generateBtn = document.getElementById('generateTxnBtn');
    if (generateBtn && txnInput) {
        generateBtn.addEventListener('click', function() {
            txnInput.value = randomTxnId();
        });
    }

    paySelect.addEventListener('change', applyState);
    applyState();
})();
</script>
@endpush

@endsection