@extends('admin.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-shipping-states-page', 'pageClass' => 'admin-shipping-states-page'])
@section('page-title', 'Shipping States')
@section('meta_tags')
    @include('partials.meta-tags', ['pageTitle' => 'Shipping States'])
@endsection
@section('content')
<div class="container-fluid px-15px px-lg-25px">
    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if (session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
    @if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif

    <div class="row">
        <div class="col-lg-8 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <form action="{{ route('admin.settings.shipping-states.index') }}" method="GET">
                        <div class="row gutters-5 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label fs-12 text-muted" for="state-search">State name</label>
                                <input id="state-search" type="text" class="form-control" name="search" value="{{ $search }}" placeholder="Type state name">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fs-12 text-muted" for="state-country">Country</label>
                                <select id="state-country" name="country_id" class="form-select">
                                    <option value="">All countries</option>
                                    @foreach ($countries as $country)
                                        <option value="{{ $country->id }}" @selected($countryId === $country->id)>{{ $country->name }}{{ $country->status ? '' : ' (inactive)' }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fs-12 text-muted" for="state-status">Status</label>
                                <select id="state-status" name="status" class="form-select">
                                    <option value="">All</option>
                                    <option value="active" @selected($status === true)>Active</option>
                                    <option value="inactive" @selected($status === false)>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-auto d-flex gap-2">
                                <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                                @if ($search !== '' || $countryId > 0 || $status !== null)
                                    <a href="{{ route('admin.settings.shipping-states.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th width="8%">#</th>
                                <th>Name</th>
                                <th>Country</th>
                                <th>Status</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($states as $state)
                                <tr>
                                    <td>{{ $states->firstItem() + $loop->index }}</td>
                                    <td>
                                        {{ $state->name }}
                                        @if ($state->cities_count > 0)
                                            <small class="text-muted d-block">{{ $state->cities_count }} {{ $state->cities_count === 1 ? 'city' : 'cities' }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $state->country?->name ?? '—' }}</td>
                                    <td>
                                        <form action="{{ route('admin.settings.shipping-states.status', $state) }}" method="POST" class="state-status-form">
                                            @csrf
                                            <input type="hidden" name="status" value="{{ $state->status ? 1 : 0 }}">
                                            <label class="feature-switch mb-0">
                                                <input type="checkbox"
                                                       role="switch"
                                                       aria-label="Toggle status for {{ $state->name }}"
                                                       @checked($state->status)>
                                                <span class="feature-switch-track" aria-hidden="true"></span>
                                            </label>
                                        </form>
                                    </td>
                                    <td class="pe-3 table-action-col">
                                        <div class="action-buttons">
                                            <a href="{{ route('admin.settings.shipping-states.edit', $state) }}" class="action-btn btn-edit" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit {{ $state->name }}" aria-label="Edit {{ $state->name }}">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            </a>
                                            <form action="{{ route('admin.settings.shipping-states.destroy', $state) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this state?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="action-btn btn-cancel" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $state->cities_count > 0 ? 'Delete cities under this state first' : 'Delete '.$state->name }}" aria-label="Delete {{ $state->name }}" @disabled($state->cities_count > 0)>
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="m19 6-1 14H6L5 6"/><path d="M10 11v5M14 11v5"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">No states found for the selected filters.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-3">{{ $states->links() }}</div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0 h6">Add New State</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.shipping-states.store') }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="new-state-name" class="form-label">Name</label>
                            <input id="new-state-name" type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="State name" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label for="new-state-country" class="form-label">Country</label>
                            <select id="new-state-country" name="country_id" class="form-select @error('country_id') is-invalid @enderror" required>
                                <option value="">Select country</option>
                                @foreach ($countries as $country)
                                    <option value="{{ $country->id }}" @selected((int) old('country_id') === $country->id)>{{ $country->name }}{{ $country->status ? '' : ' (inactive)' }}</option>
                                @endforeach
                            </select>
                            @error('country_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary">Save</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .admin-shipping-states-page .feature-switch {
        position: relative;
        display: inline-flex;
        width: 48px;
        height: 26px;
        cursor: pointer;
    }

    .admin-shipping-states-page .feature-switch input {
        position: absolute;
        width: 1px;
        height: 1px;
        opacity: 0;
    }

    .admin-shipping-states-page .feature-switch-track {
        position: absolute;
        inset: 0;
        background: #b7c0bd;
        border-radius: 26px;
        transition: background-color .18s ease;
    }

    .admin-shipping-states-page .feature-switch-track::before {
        position: absolute;
        top: 3px;
        left: 3px;
        width: 20px;
        height: 20px;
        content: "";
        background: #fff;
        border-radius: 50%;
        transition: transform .18s ease;
    }

    .admin-shipping-states-page .feature-switch input:checked + .feature-switch-track {
        background: #267c70;
    }

    .admin-shipping-states-page .feature-switch input:checked + .feature-switch-track::before {
        transform: translateX(22px);
    }

    .admin-shipping-states-page .feature-switch input:focus-visible + .feature-switch-track {
        outline: 3px solid rgba(38, 124, 112, .3);
        outline-offset: 2px;
    }

    @media (prefers-reduced-motion: reduce) {
        .admin-shipping-states-page .feature-switch-track,
        .admin-shipping-states-page .feature-switch-track::before {
            transition: none;
        }
    }
</style>

<script>
document.querySelectorAll('.state-status-form').forEach((form) => {
    const checkbox = form.querySelector('input[type="checkbox"]');
    const status = form.querySelector('input[name="status"]');

    checkbox.addEventListener('change', () => {
        status.value = checkbox.checked ? '1' : '0';
        form.submit();
    });
});
</script>
@endsection
