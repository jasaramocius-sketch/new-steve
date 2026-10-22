@extends('user.layouts.dashboard')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'user-dashboard-page', 'pageClass' => 'user-dashboard-page'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Dashboard - StAutoparts',
        'metaTitle' => 'Dashboard | StAutoparts',
        'metaDescription' => 'Manage your account and view your orders at StAutoparts.',
    ])
@endsection
@section('dashboard-content')

@php
    $cartCount = collect(session('cart', []))->sum('qty') ?: 0;
    $ordersThisMonth = isset($orders) ? $orders->filter(fn($o) => $o->created_at && $o->created_at->isCurrentMonth())->count() : 0;
    $ordersThisMonthNew = isset($orders) ? $orders->filter(fn($o) => $o->created_at && $o->created_at->isCurrentMonth() && $o->status === 'pending')->count() : 0;
    $pendingCount = isset($orders) ? $orders->whereIn('status', ['pending', 'processing'])->count() : 0;
@endphp

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.42.0/dist/tabler-icons.min.css" media="all" onload="this.media='all'">
<div style="font-family: 'Inter', sans-serif; width: 100%; background: #fff; padding: 4px;">
<div class="dashboard-header mb-3">
    <h4 class="h4-style mb-0">Dashboard</h4>
</div>
<div class="dashboard-stats">

    <!-- Total Spent -->
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon stat-icon-danger">
                <i class="ti ti-currency-dollar"></i>
            </div>

            <div class="stat-badge stat-success">
                <i class="ti ti-arrow-up-right"></i>
                {{ $ordersThisMonthNew ? $ordersThisMonthNew . ' new' : '0 new' }}
            </div>
        </div>

        <h3 class="stat-value">
            {{ currency_format($total_spent ?? 0) }}
        </h3>

        <p class="stat-text">
            Total Spent &middot; vs last month
        </p>
    </div>

    <!-- Orders -->
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon stat-icon-secondary">
                <i class="ti ti-package"></i>
            </div>

            <div class="stat-badge stat-success">
                <i class="ti ti-arrow-up-right"></i>
                {{ $ordersThisMonth }} this month
            </div>
        </div>

        <h3 class="stat-value">
            {{ $total_orders ?? 0 }}
        </h3>

        <p class="stat-text">
            Orders &middot; this month
        </p>
    </div>

    <!-- Cart -->
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon stat-icon-light">
                <i class="ti ti-shopping-cart"></i>
            </div>

            <div class="stat-badge stat-muted">
                {{ $cartCount > 0 ? $cartCount . ' items' : 'empty' }}
            </div>
        </div>

        <h3 class="stat-value">
            {{ $cartCount }}
        </h3>

        <p class="stat-text">
            Products in Cart
        </p>
    </div>

    <!-- Pending -->
    <div class="stat-card">
        <div class="stat-header">
            <div class="stat-icon stat-icon-warning">
                <i class="ti ti-clock"></i>
            </div>

            <div class="stat-badge stat-warning">
                <i class="ti ti-alert-triangle"></i>
                {{ $pendingCount > 0 ? 'needs review' : 'all clear' }}
            </div>
        </div>

        <h3 class="stat-value">
            {{ $pendingCount }}
        </h3>

        <p class="stat-text">
            Pending Orders
        </p>
    </div>

</div>
</div>
<!-- 
@if(isset($orders) && $orders->count())
<div class="recent-orders-table">
    <h4>Recent Orders</h4>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders->take(5) as $order)
                <tr>
                    <td>#{{ $order->order_number ?? $order->id }}</td>
                    <td>{{ \Carbon\Carbon::parse($order->created_at)->format('d M, Y') }}</td>
                    <td><span class="badge bg-{{ $order->status == 'delivered' ? 'success' : ($order->status == 'pending' ? 'warning' : 'secondary') }}">{{ ucfirst($order->status) }}</span></td>
                    <td>{{ currency_format($order->total_amount ?? $order->grand_total ?? 0) }}</td>
                    <td class="table-action-col"><div class="action-buttons"><a href="{{ route('user.orders.show', $order->id) }}" class="btn btn-sm btn-outline-dark">View</a></div></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif -->

<div class="dashboard-row dashboard-additional-block-3">
    <div class="dashboard-column" style="width: 100%;">
        <div class="acc-info-wrapper">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h4 class="mb-0">Your Vehicles</h4>
                <a href="{{ route('user.vehicles') }}" class="text-decoration-none small a-tag-hover-color">View All <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
            @if(isset($userVehicles) && $userVehicles->count() > 0)
            <div class="row g-3">
                @foreach($userVehicles->take(3) as $vehicle)
                    <div class="col-md-4">
                        <div class="border rounded p-3 d-flex align-items-center justify-content-between" style="background: #f8f9fa;">
                            <div>
                                <div class="fw-600" style="font-size: 14px;">{{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }}</div>
                                @if($vehicle->engine)
                                    <div class="text-muted" style="font-size: 12px;">{{ $vehicle->engine }}</div>
                                @endif
                            </div>
                            <a href="{{ route('shop', ['year' => $vehicle->year, 'make' => $vehicle->make, 'model' => $vehicle->model]) }}" class="btn steve-btn btn-primary text-white" style="padding: 8px !important;">
                                <i class="las la-search"></i> Shop
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
            @else
            <div class="text-muted small mb-3">No vehicles added yet.</div>
            <a href="{{ route('user.vehicles') }}" class="btn btn-sm btn-dark rounded-pill px-4">Add Your First Vehicle</a>
            @endif
        </div>
    </div>
</div>

<div class="dashboard-row dashboard-wishlist-block-2">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h4 class="mb-0">My Wishlist</h4>
        <a href="{{ route('user.wishlist') }}" class="text-decoration-none small a-tag-hover-color">View All <i class="fas fa-arrow-right ms-1"></i></a>
    </div>
    <div class="d-grid wishlist-page-products">
        @forelse($wishlist->take(3) as $item)
        <div class="wishlist-page-products-items">
            @php
                $product = $item->product ?? $item;
                $wishlistId = isset($item->id) ? $item->id : null;
            @endphp
            @include('partials.product-card', [
                'product' => $product,
                'wishlistItemId' => $wishlistId,
                'showRemoveWishlistIcon' => true,
                'colClass' => 'col-lg-4 col-md-6 mb-4',
            ])
            </div>
        @empty        
            <div class="col-12">
                <div class="text-muted small">No products in wishlist.</div>
            </div>
        @endforelse
    </div>
</div>

<style>
    .recent-orders-table h4 {
        margin-bottom: 16px;
    }
    .dashboard-stats{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:16px;
}

.stat-card{
    background:#fff;
    border:1px solid #ecebe8;
    border-radius:12px;
    padding:18px 20px;
}

.stat-header{
    display:flex;
    align-items:center;
    justify-content:space-between;
}

.stat-icon{
    width:36px;
    height:36px;
    border-radius:8px;
    display:flex;
    align-items:center;
    justify-content:center;
}

.stat-icon i{
    font-size:18px;
}

.stat-icon-danger{background:#fdeceb;}
.stat-icon-danger i{color:#e63328;}

.stat-icon-secondary{background:#eef1f6;}
.stat-icon-secondary i{color:#5b6577;}

.stat-icon-light{background:#f2f1ee;}
.stat-icon-light i{color:#a8a59c;}

.stat-icon-warning{background:#fff4e8;}
.stat-icon-warning i{color:#d9791f;}

.stat-badge{
    display:flex;
    align-items:center;
    gap:3px;
    font-size:11.5px;
    font-weight:600;
}

.stat-success{color:#1a9e5c;}
.stat-warning{color:#d9791f;}
.stat-muted{color:#a8a59c;}

.stat-value{
    margin-top:12px;
    font-size:24px;
    font-weight:700;
    color:#1e2433;
}

.stat-text{
    margin-top:4px;
    font-size:12.5px;
    color:#8a8680;
    font-weight:500;
}

/* ---------- Responsive ---------- */

/* Laptop */
@media (max-width: 1199px){
    .dashboard-stats{
        grid-template-columns:repeat(2,1fr);
    }
}

/* Tablet */
@media (max-width: 767px){
    .dashboard-stats{
        grid-template-columns:repeat(2,1fr);
        gap:12px;
    }

    .stat-card{
        padding:16px;
    }

    .stat-value{
        font-size:22px;
    }
}

/* Mobile */
@media (max-width: 575px){
    .dashboard-stats{
        grid-template-columns:1fr;
    }

    .stat-card{
        padding:15px;
    }

    .stat-value{
        font-size:20px;
    }

    .stat-text{
        font-size:12px;
    }

    .stat-badge{
        font-size:11px;
    }

    .stat-icon{
        width:32px;
        height:32px;
    }

    .stat-icon i{
        font-size:16px;
    }
}
</style>

@endsection
