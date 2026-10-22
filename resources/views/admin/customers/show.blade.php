@extends('admin.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-customers-show-page', 'pageClass' => 'admin-customers-show-page'])
@section('page-title', 'Customer: ' . $customer->name)
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Customer Details - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')

@php
    $badgeFor = function ($status) {
        return match($status) {
            'delivered' => 'bg-light text-success border border-success-subtle',
            'cancelled' => 'bg-light text-danger border border-danger-subtle',
            'shipped'   => 'bg-light text-primary border border-primary-subtle',
            default     => 'bg-light text-warning border border-warning-subtle',
        };
    };
@endphp

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h4 class="fw-bold mb-0">{{ $customer->name }}</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary steve-btn"><i class="fas fa-arrow-left me-1"></i> Back</a>
        <a href="{{ route('admin.customers.edit', $customer->id) }}" class="btn btn-primary"><i class="fas fa-user-edit me-1"></i> Edit</a>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h5 class="h4 mb-1 fw-bold">{{ $stats['total_orders'] }}</h5>
                <div class="text-muted small">Total Orders</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h5 class="h4 mb-1 fw-bold">{{ currency_format($stats['total_spent']) }}</h5>
                <div class="text-muted small">Total Spent</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h5 class="h4 mb-1 fw-bold text-warning">{{ $stats['pending_orders'] }}</h5>
                <div class="text-muted small">Pending Orders</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <h5 class="h4 mb-1 fw-bold">{{ $customer->addresses->count() }}</h5>
                <div class="text-muted small">Saved Addresses</div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <h6 class="fw-bold mb-3">Customer Info</h6>
        <div class="row g-3">
            <div class="col-md-4"><span class="text-muted small d-block">Email</span><strong>{{ $customer->email }}</strong></div>
            <div class="col-md-4"><span class="text-muted small d-block">Phone</span><strong>{{ $customer->phone ?? '—' }}</strong></div>
            <div class="col-md-4"><span class="text-muted small d-block">Status</span><span class="badge {{ $customer->status === 'active' ? 'bg-success' : 'bg-danger' }}">{{ ucfirst($customer->status ?? 'inactive') }}</span></div>
            <div class="col-md-4"><span class="text-muted small d-block">Member Since</span><strong>{{ optional($customer->created_at)->format('d M Y') }}</strong></div>
            <div class="col-md-8"><span class="text-muted small d-block">City</span><strong>{{ $customer->city ?? '—' }}</strong></div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
            <h6 class="fw-bold mb-0">Order History</h6>
            <div class="text-muted small">Showing {{ $orders->firstItem() }}-{{ $orders->lastItem() }} of {{ $orders->total() }}</div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Order</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Total</th>
                        <th class="pe-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td class="ps-3">#{{ $order->order_number }}</td>
                        <td>{{ $order->created_at->format('d M Y') }}</td>
                        <td><span class="badge {{ $badgeFor($order->status) }}">{{ ucfirst($order->status) }}</span></td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ ucfirst($order->payment_status ?? 'unpaid') }}</span>
                            <small class="d-block text-muted">{{ ucfirst($order->payment_method) }}</small>
                        </td>
                        <td>{{ currency_format($order->total_amount) }}</td>
                        <td class="pe-3">
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary steve-btn">View</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="100%" class="text-center py-4 text-muted">This customer has no orders yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($orders->hasPages())
            <div class="d-flex justify-content-center py-3">{{ $orders->links('vendor.pagination.gs-pagination') }}</div>
        @endif
    </div>
</div>

@endsection