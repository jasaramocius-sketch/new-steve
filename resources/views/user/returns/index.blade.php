@extends('user.layouts.dashboard')
@include('partials.page-attributes', ['pageId' => 'user-returns-page', 'pageClass' => 'user-returns-page'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'My Returns - StAutoparts',
        'metaTitle' => 'My Returns | StAutoparts',
        'metaDescription' => 'View and manage your return requests at StAutoparts.',
    ])
@endsection
@section('dashboard-content')

@php
    $badges = [
        'pending' => 'badge--warning',
        'approved' => 'badge--info',
        'rejected' => 'badge--danger',
        'refunded' => 'badge--success',
    ];
@endphp
<div class="user-return-page">
<div class="dashboard-topbar">
    <h4 class="h4-style mb-0">My Returns</h4>
    <a href="{{ route('user.orders') }}" class="btn btn-primary steve-btn">Raise Return</a>
</div>

<div class="table-responsive">
    <table class="table table--custom table--responsive-lg table-hover">
        <thead>
            <tr>
                <th>Return No.</th>
                <th>Order</th>
                <th>Product</th>
                <th>Qty</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Refund</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($returns as $return)
                @php $badge = $badges[$return->status] ?? 'badge--secondary'; @endphp
                <tr>
                    <td data-label="Return No." class="order-code">#{{ $return->id }}</td>
                    <td data-label="Order">
                        <a href="{{ route('user.orders.show', $return->order_id) }}">{{ $return->order->order_number ?? $return->order_id }}</a>
                    </td>
                    <td data-label="Product">
                        @if ($return->product)
                            <a href="{{ route('product', $return->product->slug) }}">{{ $return->product_name ?? $return->product->name }}</a>
                        @else
                            {{ $return->product_name ?? 'Deleted product' }}
                        @endif
                    </td>
                    <td data-label="Qty">{{ $return->qty }}</td>
                    <td data-label="Reason" style="max-width:220px;">{{ \Illuminate\Support\Str::limit($return->reason, 60) }}</td>
                    <td data-label="Status">
                        <span class="badge {{ $badge }}">{{ ucfirst($return->status) }}</span>
                    </td>
                    <td data-label="Refund">
                        {{ $return->refund_amount !== null ? currency_format($return->refund_amount) : '—' }}
                    </td>
                    <td data-label="Action" class="table-action-col">
                        @if ($return->status === 'pending')
                            <form action="{{ route('user.returns.destroy', $return->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Withdraw this return request?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn btn-cancel" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Withdraw Request">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    </svg>
                                </button>
                            </form>
                        @elseif ($return->admin_note)
                            <button type="button" class="action-btn btn-view"
                                    data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="{{ $return->admin_note }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="16" x2="12" y2="12"></line>
                                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                </svg>
                            </button>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="100%">
                        <div class="empty-section">
                            <div class="empty-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="1 4 1 10 7 10"></polyline>
                                    <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
                                </svg>
                            </div>
                            <h5>No returns found</h5>
                            <p>Your return requests will appear here.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if(method_exists($returns, 'links'))
    <div class="pagination-wrapper">
        {{ $returns->links() }}
    </div>
@endif
</div>
@endsection