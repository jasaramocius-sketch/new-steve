@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-coupons-index-page', 'pageClass' => 'admin-coupons-index-page'])
@section('page-title', 'Coupons')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Coupons - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap flex-md-nowrap">
    <h4 class="fw-bold mb-0">All Coupons</h4>
    <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Coupon</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="d-flex justify-content-between align-items-center p-2 flex-wrap flex-md-nowrap flex-wrap flex-md-nowrap">
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">Show</span>
                <select class="form-select w-auto" onchange="window.location.href=this.value">
                    @foreach([10, 20, 50, 100] as $n)
                        <option value="{{ request()->fullUrlWithQuery(['per_page' => $n, 'page' => null]) }}" {{ (int)request('per_page', 10) === $n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                </select>
                <span class="text-muted small">per page</span>
            </div>
            <div class="text-muted small">
                Showing {{ $coupons->firstItem() }}-{{ $coupons->lastItem() }} of {{ $coupons->total() }}
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">No.</th>
                        <th><a href="{{ sortUrl('code', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Code {!! sortIndicator('code', $sortBy, $sortDir) !!}</a></th>
                        <th><a href="{{ sortUrl('type', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Type {!! sortIndicator('type', $sortBy, $sortDir) !!}</a></th>
                        <th><a href="{{ sortUrl('value', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Value {!! sortIndicator('value', $sortBy, $sortDir) !!}</a></th>
                        <th>Min Order</th>
                        <th>Uses</th>
                        <th><a href="{{ sortUrl('expires_at', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Expires {!! sortIndicator('expires_at', $sortBy, $sortDir) !!}</a></th>
                        <th><a href="{{ sortUrl('status', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Status {!! sortIndicator('status', $sortBy, $sortDir) !!}</a></th>
                        <th class="pe-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($coupons as $coupon)
                    <tr>
                        <td class="ps-3">{{ $coupons->firstItem() + $loop->index }}</td>
                        <td><strong>{{ $coupon->code }}</strong></td>
                        <td>{{ ucfirst($coupon->type) }}</td>
                        <td>
                            @if($coupon->type === 'percentage')
                                {{ $coupon->value }}%
                            @else
                                {{ currency_format($coupon->value) }}
                            @endif
                        </td>
                        <td>{{ $coupon->min_order_amount ? currency_format($coupon->min_order_amount) : '—' }}</td>
                        <td>{{ $coupon->used_count }}{{ $coupon->max_uses ? ' / ' . $coupon->max_uses : '' }}</td>
                        <td>{{ $coupon->expires_at ? $coupon->expires_at->format('d M Y') : '—' }}</td>
                        <td>
                            <span class="badge {{ $coupon->status ? 'bg-light text-success border border-success-subtle' : 'bg-light text-danger border border-danger-subtle' }}">
                                {{  $coupon->status ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="pe-3 table-action-col">
                            <div class="action-buttons">
                            <a href="{{ route('admin.coupons.show', $coupon->id) }}" class="action-btn btn-view" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="View Coupon Usage"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">No results found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($coupons->hasPages())
            <div class="d-flex justify-content-center py-3">{{ $coupons->links('vendor.pagination.gs-pagination') }}</div>
        @endif
    </div>
</div>

@endsection
