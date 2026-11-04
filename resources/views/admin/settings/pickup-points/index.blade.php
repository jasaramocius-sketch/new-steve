@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-pickup-points-page', 'pageClass' => 'admin-pickup-points-page'])
@section('page-title', 'Pickup Points')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Pickup Points',
    ])
@endsection
@section('content')
<div class="container-fluid px-15px px-lg-25px">
    <div class="ap-page-header mb-3">
        <!-- <nav class="ap-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('admin.settings.index') }}">Settings</a>
            <span class="sep" aria-hidden="true">/</span>
            <span class="current" aria-current="page">Pickup Points</span>
        </nav> -->
        <div class="ap-page-header-row">
            <!-- <h1 class="ap-page-title">Pickup Points</h1> -->
            
        </div>
    </div>

    <!-- @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if (session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif -->

    <div class="card">
        <div class="card-header">
            <form action="{{ route('admin.settings.pickup-points.index') }}" method="GET">
                <div class="d-flex flex-wrap gap-3 align-items-end">
                    <div class="col d-flex d-sm-flex flex-row align-items-center gap-2">
                        <label class="fs-12 text-muted" for="pickup-search">Name</label>
                        <input id="pickup-search" type="text" class="form-select" name="search" value="{{ $search }}" placeholder="Name, manager or location">
                    <div class="col"><button type="submit" class="btn btn-sm btn-primary">Filter</button></div>
                    </div>
                    <div class=""><a href="{{ route('admin.settings.pickup-points.create') }}" class="btn btn-primary d-flex align-items-center gap-1"><i class="fas fa-plus"></i>Add New Pick-up Point</a></div>
                </div>
            </form>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th width="10%">#</th><th>Name</th><th>Manager</th><th>Location</th><th>Pickup Station Contact</th><th>Status</th><th width="10%" class="text-right">Options</th></tr></thead>
                <tbody>
                    @forelse ($pickupPoints as $pickupPoint)
                        <tr>
                            <td>{{ $pickupPoints->firstItem() + $loop->index }}</td>
                            <td>{{ $pickupPoint->name }}</td>
                            <td>{{ $pickupPoint->manager }}</td>
                            <td>{{ $pickupPoint->location }}</td>
                            <td>{{ $pickupPoint->contact }}</td>
                            <td>
                                <form action="{{ route('admin.settings.pickup-points.status', $pickupPoint) }}" method="POST" class="pickup-status-form">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $pickupPoint->is_open ? 'btn-success' : 'btn-outline-secondary' }}">{{ $pickupPoint->is_open ? 'Open' : 'Closed' }}</button>
                                </form>
                            </td>
                            <td class="pe-3 table-action-col">
                                <div class="action-buttons">
                                    <a href="{{ route('admin.settings.pickup-points.edit', $pickupPoint) }}" class="action-btn btn-edit" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit {{ $pickupPoint->name }}" aria-label="Edit {{ $pickupPoint->name }}">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </a>
                                    <form action="{{ route('admin.settings.pickup-points.destroy', $pickupPoint) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this pickup point?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-btn btn-cancel" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete {{ $pickupPoint->name }}" aria-label="Delete {{ $pickupPoint->name }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="m19 6-1 14H6L5 6"/><path d="M10 11v5M14 11v5"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No pickup points found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-3">{{ $pickupPoints->links() }}</div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.pickup-status-form').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const button = form.querySelector('button');
        button.disabled = true;

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': form.querySelector('[name="_token"]').value },
                body: new FormData(form),
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Could not update pickup point.');
            window.location.reload();
        } catch (error) {
            window.alert(error.message);
            button.disabled = false;
        }
    });
});
</script>
@endsection
