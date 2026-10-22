@extends('user.layouts.dashboard')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'user-vehicles-page', 'pageClass' => 'user-vehicles-page'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'My Vehicles - StAutoparts',
        'metaTitle' => 'My Vehicles | StAutoparts',
        'metaDescription' => 'Manage your vehicles at StAutoparts.',
    ])
@endsection
@section('dashboard-content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h4 class="h4-style mb-0">My Vehicles</h4>
    <button class="btn btn-primary btn-sm steve-btn" data-bs-toggle="modal" data-bs-target="#addVehicleModal">+ Add Vehicle</button>
</div>

<section>
@forelse($vehicles as $vehicle)
<div class="card mb-3">
    <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="mb-1">{{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }}</h5>
            @if($vehicle->engine)
            <p class="mb-0 text-muted">Engine: {{ $vehicle->engine }}</p>
            @endif
        </div>
        <div class="d-flex gap-2">
            <div class="action-buttons">
                <span data-bs-toggle="tooltip" data-bs-placement="top" title="Edit">
                    <button class="action-btn btn-edit" data-bs-toggle="modal" data-bs-target="#editVehicleModal{{ $vehicle->id }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                    </button>
                </span>
                <form action="{{ route('user.vehicles.destroy', $vehicle->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Remove this vehicle?')">
                    @csrf @method('DELETE')
                    <button class="action-btn btn-cancel" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Vehicle Modal -->
<div class="modal fade" id="editVehicleModal{{ $vehicle->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('user.vehicles.update', $vehicle->id) }}" method="POST" class="w-100">
            @csrf @method('PUT')
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Edit Vehicle</h5><button class="btn-close steve-btn" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Year</label>
                        <input type="number" name="year" class="form-control" min="1900" max="2026" value="{{ $vehicle->year }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Make</label>
                        <input type="text" name="make" class="form-control" value="{{ $vehicle->make }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Model</label>
                        <input type="text" name="model" class="form-control" value="{{ $vehicle->model }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Engine</label>
                        <input type="text" name="engine" class="form-control" value="{{ $vehicle->engine }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary steve-btn">Save Changes</button>
                    <button class="btn btn-primary steve-btn" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>
@empty
<div class="card">
    <div class="card-body text-center py-5">
        <p class="text-muted mb-3">No vehicles added yet.</p>
    </div>
</div>
@endforelse
</section>
<!-- Add Vehicle Modal -->
<div class="modal fade" id="addVehicleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('user.vehicles.store') }}" method="POST" class="w-100">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Add Vehicle</h5><button class="btn-close steve-btn" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Year</label>
                        <input type="number" name="year" class="form-control" min="1900" max="2026" placeholder="e.g. 2022" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Make</label>
                        <input type="text" name="make" class="form-control" placeholder="e.g. Audi" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Model</label>
                        <input type="text" name="model" class="form-control" placeholder="e.g. A5 Quattro" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Engine</label>
                        <input type="text" name="engine" class="form-control" placeholder="e.g. 2.0 Turbo">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary steve-btn">Save</button>
                    <button class="btn btn-primary steve-btn" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection