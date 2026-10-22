@extends('user.layouts.dashboard')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'user-orders-page', 'pageClass' => 'user-orders-page'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'My Orders - StAutoparts',
        'metaTitle' => 'My Orders | StAutoparts',
        'metaDescription' => 'View and manage your orders at StAutoparts.',
    ])
@endsection
@section('dashboard-content')

@php
    $currentStatus = request('status', '');
    $statuses = ['', 'pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    $statusLabels = ['' => 'All', 'pending' => 'Pending', 'processing' => 'Processing', 'shipped' => 'Shipped', 'delivered' => 'Delivered', 'cancelled' => 'Cancelled'];
    $deliveryBadges = [
        'pending' => 'badge--warning',
        'processing' => 'badge--info',
        'shipped' => 'badge--primary',
        'delivered' => 'badge--success',
        'cancelled' => 'badge--danger',
    ];
@endphp
<div class="user-order-page">
<div class="dashboard-topbar">
    <h4 class="h4-style mb-0">Purchase History</h4>
    <a href="{{ route('shop') }}" class="btn btn-primary steve-btn">Continue Shopping</a>
</div>

<div class="dashboard-filter">
    @foreach($statuses as $s)
        <a href="{{ $s ? route('user.orders', ['status' => $s, 'page' => null]) : route('user.orders', ['page' => null]) }}"
           class="dashboard-filter__link{{ $currentStatus === $s ? ' active' : '' }}">
            {{ $statusLabels[$s] }}
        </a>
    @endforeach
</div>

<div class="table-responsive">
    <table class="table table--custom table--responsive-lg table-hover">
        <thead>
            <tr>
                <th>Order Code</th>
                <th>Date</th>
                <th>Amount</th>
                <th>Delivery Status</th>
                <th>Payment</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
                @php
                    $status = strtolower($order->status ?? 'pending');
                    $badge = $deliveryBadges[$status] ?? 'badge--secondary';
                    $isPaid = $order->payment_method && $order->payment_method !== 'cod';
                @endphp
                <tr>
                    <td data-label="Order Code" class="order-code">
                        <a href="{{ route('user.orders.show', $order->id) }}">
                            {{ $order->order_number ?? '#' . $order->id }}
                        </a>
                    </td>
                    <td data-label="Date">
                        <div>
                        <span>{{ optional($order->created_at)->format('d-m-Y') }}</span>
                        <span class="d-block" style="font-size:0.8125rem;color:#8a7b79;">{{ optional($order->created_at)->format('h:i A') }}</span></div>
                    </td>
                    <td data-label="Amount">
                        <span style="font-weight:600;">{{ currency_format($order->total_amount ?? 0) }}</span>
                    </td>
                    <td data-label="Delivery Status">
                        <span class="badge {{ $badge }}">{{ $statusLabels[$status] ?? ucfirst($status) }}</span>
                    </td>
                    <td data-label="Payment">
                        <span class="badge {{ $isPaid ? 'badge--success' : 'badge--danger' }}">{{ $isPaid ? 'Paid' : 'Unpaid' }}</span>
                    </td>
                    <td data-label="Action" class="table-action-col">
                        <div class="action-buttons" style="justify-content:flex-end;">
                            <a href="{{ route('user.orders.show', $order->id) }}" class="action-btn btn-view" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="View Details">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </a>
                            <a href="{{ route('user.orders.invoice', $order->id) }}" target="_blank" class="action-btn btn-invoice" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Download Invoice">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="7 10 12 15 17 10"></polyline>
                                    <line x1="12" y1="15" x2="12" y2="3"></line>
                                </svg>
                            </a>
                            @if($status === 'delivered')
                                <a href="{{ route('user.returns.create', $order->id) }}" class="action-btn btn-view" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Request Return" style="color:#198754;">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="1 4 1 10 7 10"></polyline>
                                        <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
                                    </svg>
                                </a>
                            @endif
                            <form action="{{ route('user.orders.destroy', $order->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Are you sure you want to cancel this order?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn btn-cancel" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Cancel Order">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="100%">
                        <div class="empty-section">
                            <div class="empty-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="8" x2="12" y2="12"></line>
                                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                </svg>
                            </div>
                            <h5>No orders found</h5>
                            <p>Your purchased orders will appear here.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if(method_exists($orders, 'links'))
    <div class="pagination-wrapper">
        {{ $orders->links() }}
    </div>
@endif
</div>
@endsection