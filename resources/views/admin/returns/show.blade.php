@extends('admin.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-returns-show-page', 'pageClass' => 'admin-returns-show-page'])
@section('page-title', 'Return #' . $return->id)
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Return Detail - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection

@section('content')

@php
    $badgeClass = match($return->status) {
        'pending' => 'bg-light text-warning border border-warning-subtle',
        'approved' => 'bg-light text-info border border-info-subtle',
        'rejected' => 'bg-light text-danger border border-danger-subtle',
        'refunded' => 'bg-light text-success border border-success-subtle',
        default => 'bg-light text-secondary border border-secondary-subtle',
    };
@endphp

<div class="d-flex flex-wrap align-items-center justify-content-between pb-3 border-bottom gap-3">
    <div>
        <h4 class="mb-0 fw-bold">Return Request #{{ $return->id }}
            <span class="badge {{ $badgeClass }}">{{ ucfirst($return->status) }}</span>
        </h4>
        <small class="text-muted">Requested on {{ $return->created_at->format('M d, Y \a\t h:i A') }}</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.orders.show', $return->order_id) }}" class="btn btn-outline-secondary steve-btn">
            <i class="fas fa-box me-1"></i> View Order
        </a>
    </div>
</div>

<div class="row g-3 mt-0">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Return Details</h5>
            </div>
            <div class="card-body">
                <div class="row small mb-3">
                    <div class="col-md-6">
                        <p class="mb-1"><span class="text-muted">Customer:</span> <strong>{{ $return->user->name ?? 'Guest' }}</strong></p>
                        <p class="mb-1"><span class="text-muted">Email:</span> {{ $return->user->email ?? '—' }}</p>
                        <p class="mb-0"><span class="text-muted">Order:</span>
                            @if ($return->order) #{{ $return->order->order_number }} @else #{{ $return->order_id }} @endif
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p class="mb-1"><span class="text-muted">Product:</span>
                            @if ($return->product)
                                <a href="{{ route('admin.products.edit', $return->product->id) }}">{{ $return->product_name ?? $return->product->name }}</a>
                            @else
                                {{ $return->product_name ?? 'Deleted product' }}
                            @endif
                        </p>
                        <p class="mb-1"><span class="text-muted">Quantity:</span> <strong>{{ $return->qty }}</strong></p>
                        <p class="mb-0"><span class="text-muted">Refund amount:</span>
                            {{ $return->refund_amount !== null ? currency_format($return->refund_amount) : '—' }}
                        </p>
                    </div>
                </div>

                <div class="p-3 rounded" style="background:#f8fafc;border:1px solid #edf1f5;">
                    <label class="form-label fw-semibold small text-muted text-uppercase">Reason</label>
                    <p class="mb-0">{{ $return->reason ?? '—' }}</p>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="fas fa-check-circle me-2"></i>Process Request</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.returns.update', $return->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted text-uppercase">Decision</label>
                            <select name="status" class="form-select">
                                <option value="approved" {{ $return->status === 'approved' ? 'selected' : '' }}>Approve</option>
                                <option value="rejected" {{ $return->status === 'rejected' ? 'selected' : '' }}>Reject</option>
                                <option value="refunded" {{ $return->status === 'refunded' ? 'selected' : '' }}>Approve & Refund (restore stock)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted text-uppercase">Refund Amount</label>
                            <input type="number" name="refund_amount" step="0.01" min="0" class="form-control"
                                   value="{{ $return->refund_amount ?? ($return->order->total_amount ?? '') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold small text-muted text-uppercase">Admin Note (shown to customer)</label>
                            <textarea name="admin_note" rows="3" class="form-control" placeholder="Visible to the customer...">{{ $return->admin_note }}</textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary steve-btn"><i class="fas fa-save me-1"></i> Save Decision</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Admin Notes</h5>
            </div>
            <div class="card-body">
                @if ($return->admin_note)
                    <p class="mb-0 small">{{ $return->admin_note }}</p>
                @else
                    <p class="mb-0 text-muted small">No notes yet.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection