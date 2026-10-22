@extends('admin.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-settings-commission-page', 'pageClass' => 'admin-settings-commission-page'])
@section('page-title', 'Global Commission')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Global Commission - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8 col-xl-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Global Buyer Commission</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">This percentage is added to every product's base price for buyers. Product base prices remain unchanged.</p>
                    <form action="{{ route('admin.settings.commission.update') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="commission_percent" class="form-label">Commission percentage</label>
                            <div class="input-group">
                                <input
                                    type="number"
                                    id="commission_percent"
                                    name="commission_percent"
                                    class="form-control @error('commission_percent') is-invalid @enderror"
                                    value="{{ old('commission_percent', $commissionPercent) }}"
                                    min="0"
                                    max="100"
                                    step="0.01"
                                    required
                                >
                                <span class="input-group-text">%</span>
                                @error('commission_percent')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Commission
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection