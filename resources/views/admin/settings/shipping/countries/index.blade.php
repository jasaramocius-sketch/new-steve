@extends('admin.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-shipping-countries-page', 'pageClass' => 'admin-shipping-countries-page'])
@section('page-title', 'Shipping Countries')
@section('meta_tags')
    @include('partials.meta-tags', ['pageTitle' => 'Shipping Countries'])
@endsection
@section('content')
<div class="container-fluid px-15px px-lg-25px">
    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if (session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
    @if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif

    <div class="card">
        <div class="card-header">
            <form action="{{ route('admin.settings.shipping-countries.index') }}" method="GET">
                <div class="row gutters-5 align-items-end gap-2">
                    <div class="col-md-4">
                        <label class="form-label fs-12 text-muted" for="country-search">Country name</label>
                        <input id="country-search" type="text" class="form-control" name="search" value="{{ $search }}" placeholder="Type country name">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fs-12 text-muted" for="country-status">Status</label>
                        <select id="country-status" name="status" class="form-select form-control">
                            <option value="">All statuses</option>
                            <option value="active" @selected($status === true)>Active</option>
                            <option value="inactive" @selected($status === false)>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-auto d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
                        @if ($search !== '' || $status !== null)
                            <a href="{{ route('admin.settings.shipping-countries.index') }}" class="btn btn-outline-secondary w-40px d-flex align-items-center justify-content-center"><i class="fas fa-times"></i></a>
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
                        <th>Code</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($countries as $country)
                        <tr>
                            <td>{{ $countries->firstItem() + $loop->index }}</td>
                            <td>{{ $country->name }}</td>
                            <td>{{ $country->iso2 ?: '—' }}</td>
                            <td>
                                <form action="{{ route('admin.settings.shipping-countries.status', $country) }}" method="POST" class="country-status-form">
                                    @csrf
                                    <input type="hidden" name="status" value="{{ $country->status ? 1 : 0 }}">
                                    @if ($search !== '')
                                        <input type="hidden" name="search" value="{{ $search }}">
                                    @endif
                                    @if ($status !== null)
                                        <input type="hidden" name="status_filter" value="{{ $status ? 'active' : 'inactive' }}">
                                    @endif
                                    <label class="feature-switch mb-0">
                                        <input type="checkbox"
                                               role="switch"
                                               aria-label="Toggle status for {{ $country->name }}"
                                               @checked($country->status)>
                                        <span class="feature-switch-track" aria-hidden="true"></span>
                                    </label>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No countries found for the selected filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-3">{{ $countries->links() }}</div>
        </div>
    </div>
</div>

<style>
    .admin-shipping-countries-page .feature-switch {
        position: relative;
        display: inline-flex;
        width: 48px;
        height: 26px;
        cursor: pointer;
    }

    .admin-shipping-countries-page .feature-switch input {
        position: absolute;
        width: 1px;
        height: 1px;
        opacity: 0;
    }

    .admin-shipping-countries-page .feature-switch-track {
        position: absolute;
        inset: 0;
        background: #b7c0bd;
        border-radius: 26px;
        transition: background-color .18s ease;
    }

    .admin-shipping-countries-page .feature-switch-track::before {
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

    .admin-shipping-countries-page .feature-switch input:checked + .feature-switch-track {
        background: #267c70;
    }

    .admin-shipping-countries-page .feature-switch input:checked + .feature-switch-track::before {
        transform: translateX(22px);
    }

    .admin-shipping-countries-page .feature-switch input:focus-visible + .feature-switch-track {
        outline: 3px solid rgba(38, 124, 112, .3);
        outline-offset: 2px;
    }

    @media (prefers-reduced-motion: reduce) {
        .admin-shipping-countries-page .feature-switch-track,
        .admin-shipping-countries-page .feature-switch-track::before {
            transition: none;
        }
    }
</style>

<script>
document.querySelectorAll('.country-status-form').forEach((form) => {
    const checkbox = form.querySelector('input[type="checkbox"]');
    const status = form.querySelector('input[name="status"]');

    checkbox.addEventListener('change', () => {
        status.value = checkbox.checked ? '1' : '0';
        form.submit();
    });
});
</script>
@endsection
