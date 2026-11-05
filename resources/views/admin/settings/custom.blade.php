@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-Tax-Rate-settings-page', 'pageClass' => 'admin-Tax-Rate-settings-page'])
@section('page-title', 'Tax Rate and Custom Value Settings')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Tax Rate and Custom Value Settings',
    ])
@endsection
@section('content')
<div class="container-fluid px-15px px-lg-25px">
    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="mb-1">Tax Types</h5>
                <p class="text-muted mb-0">Manage the tax types available in your store.</p>
            </div>
            <a href="{{ route('admin.settings.tax-types.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1" aria-hidden="true"></i> Add Tax Type
            </a>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tax Type</th>
                        <th>Status</th>
                        <th class="text-right">Options</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($taxTypes as $taxType)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $taxType->name }}</td>
                            <td>
                                <form action="{{ route('admin.settings.tax-types.status', $taxType) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $taxType->is_active ? 'btn-success' : 'btn-outline-secondary' }}">
                                        {{ $taxType->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.settings.tax-types.edit', $taxType) }}" class="btn btn-soft-primary btn-icon btn-circle btn-sm" title="Edit {{ $taxType->name }}" aria-label="Edit {{ $taxType->name }}">
                                    <i class="las la-edit" aria-hidden="true"></i>
                                </a>
                                <form action="{{ route('admin.settings.tax-types.destroy', $taxType) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this tax type?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-soft-danger btn-icon btn-circle btn-sm" title="Delete {{ $taxType->name }}" aria-label="Delete {{ $taxType->name }}">
                                        <i class="las la-trash" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">No tax types have been added.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Tax Rate and Custom Value Settings</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.settings.custom.update') }}">
                @csrf
                <div class="form-row d-flex flex-column gap-3 mb-2">
                    <div class="form-group">
                        <label class="form-label mt-0" for="tax-rate">Tax Rate (%)</label>
                        <input id="tax-rate" type="number" min="0" max="100" step="0.01" name="tax_rate" class="form-control" value="{{ old('tax_rate', $settings['tax_rate'] ?? 0) }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label mt-0" for="custom-value">Custom Value</label>
                        <input id="custom-value" type="text" name="custom_value" maxlength="255" class="form-control" value="{{ old('custom_value', $settings['custom_value'] ?? '') }}">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Save Tax Settings</button>
            </form>
        </div>
    </div>
</div>
@endsection