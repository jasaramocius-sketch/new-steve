@extends('admin.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-api-integrations-page', 'pageClass' => 'admin-api-integrations-page'])
@section('page-title', 'API Integrations')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'API Integrations - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between pb-3 border-bottom gap-3 mb-4">
    <div>
        <h4 class="mb-0 fw-bold">API Integrations</h4>
        <p class="text-muted mb-0 small">Configure Courier, Payment, SMS, WhatsApp & other APIs from one place</p>
    </div>
    <a href="{{ route('admin.api-integrations.create') }}" class="btn btn-primary steve-btn"><i class="fas fa-plus me-1"></i> Add New Integration</a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('info'))
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        {{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-3">Name</th>
                        <th>Type</th>
                        <th>Provider</th>
                        <th>Auth Type</th>
                        <th>Default</th>
                        <th>Status</th>
                        <th>Last Test</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($integrations as $integration)
                        <tr>
                            <td class="ps-3">
                                <div class="fw-semibold">{{ $integration->name }}</div>
                                @if($integration->description)
                                    <small class="text-muted">{{ Str::limit($integration->description, 60) }}</small>
                                @endif
                            </td>
                            <td><span class="badge bg-secondary">{{ strtoupper($integration->type) }}</span></td>
                            <td>{{ ucfirst($integration->provider) }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $integration->auth_type }}</span></td>
                            <td>
                                @if($integration->is_default)
                                    <span class="badge bg-primary">Default</span>
                                @else
                                    <form action="{{ route('admin.api-integrations.set-default', $integration->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-primary">Set Default</button>
                                    </form>
                                @endif
                            </td>
                            <td>
                                <form action="{{ route('admin.api-integrations.toggle', $integration->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm {{ $integration->is_active ? 'btn-success' : 'btn-outline-secondary' }}">
                                        {{ $integration->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>
                            <td>
                                @if($integration->last_tested_at)
                                    <small class="d-block">{{ $integration->last_tested_at->format('d-m-Y H:i') }}</small>
                                    <small class="text-muted">{{ Str::limit($integration->last_test_result, 40) }}</small>
                                @else
                                    <small class="text-muted">Not tested</small>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <div class="btn-group">
                                    <form action="{{ route('admin.api-integrations.test', $integration->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-info"><i class="fas fa-plug"></i> Test</button>
                                    </form>
                                    <a href="{{ route('admin.api-integrations.edit', $integration->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i> Edit</a>
                                    <form action="{{ route('admin.api-integrations.destroy', $integration->id) }}" method="POST" onsubmit="return confirm('Delete this integration?');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No API integrations added yet. <a href="{{ route('admin.api-integrations.create') }}">Add first integration</a></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
