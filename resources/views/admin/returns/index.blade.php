@extends('admin.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-returns-index-page', 'pageClass' => 'admin-returns-index-page'])
@section('page-title', 'Returns')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Returns - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="d-flex justify-content-between align-items-center p-2 flex-wrap flex-md-nowrap">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted small">Status</span>
                    <select class="form-select w-auto" onchange="window.location.href=this.value">
                        <option value="{{ request()->fullUrlWithQuery(['status' => null, 'page' => null]) }}" {{ !request('status') ? 'selected' : '' }}>All Statuses</option>
                        @foreach(['pending', 'approved', 'rejected', 'refunded'] as $st)
                            <option value="{{ request()->fullUrlWithQuery(['status' => $st, 'page' => null]) }}" {{ request('status') === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="text-muted small">
                Showing {{ $returns->firstItem() }}-{{ $returns->lastItem() }} of {{ $returns->total() }}
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">#</th>
                        <th>Customer</th>
                        <th>Order</th>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Status</th>
                        <th class="">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returns as $return)
                    <tr>
                        <td class="ps-3">#{{ $return->id }}</td>
                        <td>{{ $return->user->name ?? 'Guest' }}</td>
                        <td>
                            @if ($return->order)
                                {{ $return->order->order_number ?? $return->order_id }}
                            @else
                                #{{ $return->order_id }}
                            @endif
                        </td>
                        <td>{{ $return->product_name ?? ($return->product->name ?? 'Deleted product') }}</td>
                        <td>{{ $return->qty }}</td>
                        <td>
                            @php
                                $badgeClass = match($return->status) {
                                    'pending' => 'bg-light text-warning border border-warning-subtle',
                                    'approved' => 'bg-light text-info border border-info-subtle',
                                    'rejected' => 'bg-light text-danger border border-danger-subtle',
                                    'refunded' => 'bg-light text-success border border-success-subtle',
                                    default => 'bg-light text-secondary border border-secondary-subtle',
                                };
                            @endphp
                            <span class="badge rounded-pill {{ $badgeClass }}">{{ ucfirst($return->status) }}</span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ route('admin.returns.show', $return->id) }}" class="action-btn btn-view" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="View returns"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="100%" class="text-center py-4 text-muted">No return requests found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($returns->hasPages())
            <div class="p-2 border-top">
                {{ $returns->links() }}
            </div>
        @endif
    </div>
</div>
@endsection