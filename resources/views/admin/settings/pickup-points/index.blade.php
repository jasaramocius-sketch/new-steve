@extends('admin.layouts.app')

@section('content')
<div class="container-fluid px-15px px-lg-25px">
    <div class="ap-page-header mb-3">
        <nav class="ap-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('admin.settings.index') }}">Settings</a>
            <span class="sep" aria-hidden="true">/</span>
            <span class="current" aria-current="page">Pickup Points</span>
        </nav>
        <div class="ap-page-header-row">
            <h1 class="ap-page-title">Pickup Points</h1>
            <div class="ap-page-actions"><a href="{{ route('admin.settings.pickup-points.create') }}" class="btn btn-sm btn-info">Add New Pick-up Point</a></div>
        </div>
    </div>

    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if (session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif

    <div class="card">
        <div class="card-header">
            <form action="{{ route('admin.settings.pickup-points.index') }}" method="GET">
                <div class="row gutters-5 align-items-end">
                    <div class="col-lg-3 col-md-4 col-8 mb-2">
                        <label class="fs-12 text-muted mb-1" for="pickup-search">Name</label>
                        <input id="pickup-search" type="text" class="form-control form-control-sm" name="search" value="{{ $search }}" placeholder="Name, manager or location">
                    </div>
                    <div class="col-auto mb-2"><button type="submit" class="btn btn-sm btn-primary">Filter</button></div>
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
                            <td class="text-right text-nowrap">
                                <a class="btn btn-soft-primary btn-icon btn-circle btn-sm" href="{{ route('admin.settings.pickup-points.edit', $pickupPoint) }}" title="Edit {{ $pickupPoint->name }}" aria-label="Edit {{ $pickupPoint->name }}"><i class="las la-edit"></i></a>
                                <form action="{{ route('admin.settings.pickup-points.destroy', $pickupPoint) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this pickup point?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-soft-danger btn-icon btn-circle btn-sm" title="Delete {{ $pickupPoint->name }}" aria-label="Delete {{ $pickupPoint->name }}"><i class="las la-trash"></i></button>
                                </form>
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
