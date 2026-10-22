@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-coupons-show-page', 'pageClass' => 'admin-coupons-show-page'])
@section('page-title', 'Coupon ' . $coupon->code)
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Coupon Detail - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap flex-md-nowrap">
    <div>
        <!-- <a href="{{ route('admin.coupons.index') }}" class="text-muted text-decoration-none mb-1 d-inline-block small"><i class="fas fa-arrow-left"></i> Back to Coupons</a>
        <h4 class="fw-bold mb-0">Coupon: <span class="text-primary">{{ $coupon->code }}</span></h4> -->
    </div>
    <a href="{{ route('admin.coupons.edit', $coupon->id) }}" class="btn btn-outline-primary"><i class="fas fa-edit"></i> Edit Coupon</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase">Type</div>
                <div class="fw-bold fs-5">{{ ucfirst($coupon->type) }}<span class="fs-6 text-muted"> · {{ $coupon->type === 'percentage' ? number_format($coupon->value, 0, '.', ',') . '%' : currency_format($coupon->value) }}</span></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase">Orders Used</div>
                <div class="fw-bold fs-5">{{ $orderCount }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase">Total Discount Given</div>
                <div class="fw-bold fs-5 text-danger">{{ currency_format($totalDiscount) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small text-uppercase">Uses Limit</div>
                <div class="fw-bold fs-5">{{ $coupon->used_count }}{{ $coupon->max_uses ? ' / ' . $coupon->max_uses : '' }} <span class="fs-6 text-muted">of {{ $coupon->max_uses ?: '∞' }}</span></div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="d-flex justify-content-between align-items-center p-2 flex-wrap flex-md-nowrap">
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">Show</span>
                <select class="form-select w-auto" onchange="window.location.href=this.value">
                    @foreach([10, 20, 50, 100] as $n)
                        <option value="{{ request()->fullUrlWithQuery(['per_page' => $n, 'page' => null]) }}" {{ (int)request('per_page', 20) === $n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                </select>
                <span class="text-muted small">per page</span>
            </div>
            <div class="text-muted small">
                Showing {{ $orders->firstItem() }}-{{ $orders->lastItem() }} of {{ $orders->total() }} orders
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Order No.</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Products (with coupon discount)</th>
                        <th class="text-end">Coupon Discount</th>
                        <th class="text-end">Order Total</th>
                        <th class="pe-3 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td class="ps-3">{{ $order->order_number ?? '#' . $order->id }}</td>
                        <td>{{ $order->user->name ?? 'Guest' }}</td>
                        <td>{{ $order->created_at->format('d M Y') }}</td>
                        <td>
                            @if($order->items->count())
                                <div class="d-flex flex-column gap-1">
                                    @foreach($order->items as $item)
                                        <div class="d-flex justify-content-between align-items-center small" style="min-width: 220px;">
                                            <span class="me-2" style="max-width: 200px;">{{ $item->product->name ?? 'Product #' . $item->product_id }} &times; {{ $item->qty }} </br><span class="text-muted fw-normal">@ {{ currency_format($item->price) }}</span></span>
                                            <span class="text-end text-danger fw-semibold" style="font-size: 16px;">-{{ currency_format($item->price ?? 0) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="text-end text-danger fw-semibold">-{{ currency_format($order->coupon_discount) }}</td>
                        <td class="text-end fw-semibold">{{ currency_format($order->total_amount) }}</td>
                        <td class="pe-3 text-end">
                            <div class="action-buttons" style="justify-content: flex-end;">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="action-btn btn-view" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="View Order"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No orders have used this coupon yet.</td>
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
