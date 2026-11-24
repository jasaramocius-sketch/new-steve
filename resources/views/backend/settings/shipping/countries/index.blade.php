@extends('backend.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-shipping-countries-page', 'pageClass' => 'admin-shipping-countries-page'])
@section('page-title', 'Shipping Countries')
@section('meta_tags')
    @include('partials.meta-tags', ['pageTitle' => 'Shipping Countries'])
@endsection
@section('content')
<div class="container-fluid px-15px px-lg-25px">
    <!-- @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if (session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
    @if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif -->

    <div class="card">
        <div class="card-header">
            <form action="{{ route('admin.settings.shipping-countries.index') }}" method="GET">
                <div class="d-flex flex-wrap flex-row flex-wrap align-items-end gap-1">
                    <div class="gap-1" style="display: grid !important; grid-template-columns: 1fr auto;">
                    @include('backend.partials.search-form', [
                        'embedded' => true,
                        'placeholder' => 'Type country name',
                        'inputId' => 'country-search',
                        'showClear' => $search !== '' || $status !== null,
                        'clearRoute' => route('admin.settings.shipping-countries.index'),
                    ])
                    </div>
                    <div class="w-auto">
                        <select id="country-status" name="status" class="form-select form-control w-auto">
                            <option value="">All statuses</option>
                            <option value="active" @selected($status === true)>Active</option>
                            <option value="inactive" @selected($status === false)>Inactive</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary d-flex align-items-center gap-1"><i class="fas fa-filter"></i>Filter</button>
                </div>
            </form>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($countries as $country)
                        <tr>
                            <td class="admin-row-number">{{ $countries->firstItem() + $loop->index }}</td>
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
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox"
                                               role="switch"
                                               aria-label="Toggle status for {{ $country->name }}"
                                               @checked($country->status)>
                                    </div>
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
