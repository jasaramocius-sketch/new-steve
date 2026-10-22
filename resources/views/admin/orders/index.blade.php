@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-orders-index-page', 'pageClass' => 'admin-orders-index-page'])
@section('page-title', 'Orders')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Orders - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection

@section('content')

 <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">All Orders</h4>
    <a href="{{ route('admin.orders.export-csv', request()->query()) }}" class="btn btn-primary steve-btn">
        <i class="fas fa-file-csv me-1"></i> Export CSV
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="d-flex justify-content-between align-items-center p-2 flex-wrap flex-md-nowrap">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted small">Show</span>
                    <select class="form-select w-auto" onchange="window.location.href=this.value">
                        @foreach([10, 20, 50, 100] as $n)
                            <option value="{{ request()->fullUrlWithQuery(['per_page' => $n, 'page' => null]) }}" {{ (int)request('per_page', 10) === $n ? 'selected' : '' }}>{{ $n }}</option>
                        @endforeach
                    </select>
                    <span class="text-muted small">per page</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted small">Status</span>
                    <select class="form-select w-auto" onchange="window.location.href=this.value">
                        <option value="{{ request()->fullUrlWithQuery(['status' => null, 'page' => null]) }}" {{ !request('status') ? 'selected' : '' }}>All Statuses</option>
                        @foreach(['pending', 'processing', 'shipped', 'delivered', 'cancelled'] as $st)
                            <option value="{{ request()->fullUrlWithQuery(['status' => $st, 'page' => null]) }}" {{ request('status') === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted small">Payment</span>
                    <select class="form-select w-auto" onchange="window.location.href=this.value">
                        <option value="{{ request()->fullUrlWithQuery(['payment_status' => null, 'page' => null]) }}" {{ !request('payment_status') ? 'selected' : '' }}>All Payments</option>
                        @foreach(['unpaid', 'paid', 'refunded'] as $ps)
                            <option value="{{ request()->fullUrlWithQuery(['payment_status' => $ps, 'page' => null]) }}" {{ request('payment_status') === $ps ? 'selected' : '' }}>{{ ucfirst($ps) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="text-muted small">
                Showing {{ $orders->firstItem() }}-{{ $orders->lastItem() }} of {{ $orders->total() }}
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3"><a href="{{ sortUrl('id', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Order No. {!! sortIndicator('id', $sortBy, $sortDir) !!}</a></th>
                        <th>Customer</th>
                        <th><a href="{{ sortUrl('total_amount', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Total {!! sortIndicator('total_amount', $sortBy, $sortDir) !!}</a></th>
                        <th><a href="{{ sortUrl('status', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Status {!! sortIndicator('status', $sortBy, $sortDir) !!}</a></th>
                        <th><a href="{{ sortUrl('payment_status', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Payment {!! sortIndicator('payment_status', $sortBy, $sortDir) !!}</a></th>
                        <th><a href="{{ sortUrl('created_at', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Date {!! sortIndicator('created_at', $sortBy, $sortDir) !!}</a></th>
                        <th class="">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td class="ps-3">{{ $order->order_number ?? '#' . $order->id }}</td>
                        <td>{{ $order->user->name ?? 'Guest' }}</td>
                        <td>{{ currency_format($order->total_amount ?? $order->total ?? 0) }}</td>
                        <td>
                            @php
                                $badgeClass = match($order->status) {
                                    'pending' => 'bg-light text-warning border border-warning-subtle',
                                    'processing' => 'bg-light text-info border border-info-subtle',
                                    'shipped' => 'bg-light text-primary border border-primary-subtle',
                                    'delivered' => 'bg-light text-success border border-success-subtle',
                                    'cancelled' => 'bg-light text-danger border border-danger-subtle',
                                    default => 'bg-light text-secondary border border-secondary-subtle',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ ucfirst($order->status) }}</span>
                        </td>
                        <td>
                            @php
                                $paymentBadge = match($order->payment_status) {
                                    'paid' => 'bg-light text-success border border-success-subtle',
                                    'refunded' => 'bg-light text-secondary border border-secondary-subtle',
                                    default => 'bg-light text-warning border border-warning-subtle',
                                };
                            @endphp
                            <span class="badge {{ $paymentBadge }}">{{ ucfirst($order->payment_status ?? 'unpaid') }}</span>
                        </td>
                        <td>{{ $order->created_at->format('M d, Y') }}</td>
                        <td class="pe-3 table-action-col">
                            <div class="action-buttons">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="action-btn btn-view" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="View Order"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No orders yet</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
            <div class="d-flex justify-content-center py-3">{{ $orders->links('vendor.pagination.gs-pagination') }}</div>
        @endif
    </div>
</div>

@endsection
