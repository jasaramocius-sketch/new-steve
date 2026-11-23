@extends('user.layouts.dashboard')
@include('partials.page-attributes', ['pageId' => 'user-wallet-page', 'pageClass' => 'user-wallet-page'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'My Wallet - Steve Autoparts',
        'metaTitle' => 'My Wallet | Steve Autoparts',
        'metaDescription' => 'View your StAutoparts wallet balance.',
    ])
@endsection
@section('dashboard-content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h4 class="h4-style mb-0">My Wallet</h4>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <p class="text-muted mb-1">Available balance</p>
        <div class="display-6 fw-semibold text-primary">{{ currency_format($balance) }}</div>
        <p class="text-muted small mt-3 mb-0">Wallet credit can be applied to eligible orders when this feature is connected to checkout.</p>
    </div>
</div>
@endsection
