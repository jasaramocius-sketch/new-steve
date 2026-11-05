@extends('admin.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-shipping-zones-page', 'pageClass' => 'admin-shipping-zones-page'])
@section('page-title', 'Shipping Zones')
@section('meta_tags')
    @include('partials.meta-tags', ['pageTitle' => 'Shipping Zones'])
@endsection
@section('content')
<div class="container-fluid px-15px px-lg-25px">
    <div class="ap-page-header mb-3">
        <div class="ap-page-header-row d-flex justify-content-between align-items-center">
            <div class=""></div>
            <a href="{{ route('admin.settings.shipping-zones.create') }}" class="btn btn-primary d-flex align-items-center gap-1"><i class="fas fa-plus mr-1"></i>Add Zone</a>
        </div>
    </div>
    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>Zone</th><th>Destination</th><th>Area cost</th><th>Carrier rates</th><th>Priority</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
                <tbody>
                    @forelse ($zones as $zone)
                        <tr>
                            <td>{{ $zone->name }}</td>
                            <td>{{ $zone->country === '*' ? 'All countries' : $zone->country }}{{ $zone->state ? ', '.$zone->state : '' }}{{ $zone->city ? ', '.$zone->city : '' }}</td>
                            <td>{{ currency_format($zone->cost ?? 0) }}</td>
                            <td>
                                @forelse ($zone->rates as $rate)
                                    <div>{{ $rate->carrier?->name ?? 'Unknown carrier' }}: {{ currency_format($rate->fee) }}</div>
                                @empty
                                    <span class="text-muted">No rates</span>
                                @endforelse
                            </td>
                            <td>{{ $zone->priority }}</td>
                            <td>
                                <span class="badge {{ $zone->is_active ? 'bg-light text-success border border-success-subtle' : 'bg-light text-danger border border-danger-subtle' }}">
                                    {{ $zone->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="pe-3 table-action-col">
                                <div class="action-buttons">
                                    <a href="{{ route('admin.settings.shipping-zones.edit', $zone) }}" class="action-btn btn-edit" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit {{ $zone->name }}" aria-label="Edit {{ $zone->name }}">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </a>
                                    <form action="{{ route('admin.settings.shipping-zones.destroy', $zone) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this shipping zone and its rates?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-btn btn-cancel" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete {{ $zone->name }}" aria-label="Delete {{ $zone->name }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="m19 6-1 14H6L5 6"/><path d="M10 11v5M14 11v5"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No shipping zones configured. Add a zone and rates before enabling home delivery.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-3">{{ $zones->links() }}</div>
        </div>
    </div>
</div>
@endsection
