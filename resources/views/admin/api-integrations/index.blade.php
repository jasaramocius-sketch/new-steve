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
                                        <button type="submit" class="action-btn btn-set-default" data-bs-toggle="tooltip" data-bs-placement="top" title="Set as default" aria-label="Set as default">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9L12 3z"/></svg>
                                        </button>
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
                            <td class="pe-3 table-action-col">
                                <div class="action-buttons">
                                    <form action="{{ route('admin.api-integrations.test', $integration->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="action-btn btn-view-live" data-bs-toggle="tooltip" data-bs-placement="top" title="Test integration" aria-label="Test integration">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22v-5"/><path d="M9 8V2"/><path d="M15 8V2"/><path d="M18 8v4a6 6 0 0 1-12 0V8z"/></svg>
                                        </button>
                                    </form>
                                    <a href="{{ route('admin.api-integrations.edit', $integration->id) }}" class="action-btn btn-edit" data-bs-toggle="tooltip" data-bs-placement="top" title="Edit integration" aria-label="Edit integration">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </a>
                                    <form action="{{ route('admin.api-integrations.destroy', $integration->id) }}" method="POST" onsubmit="return confirm('Delete this integration?');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-btn btn-cancel" data-bs-toggle="tooltip" data-bs-placement="top" title="Delete integration" aria-label="Delete integration">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="m19 6-1 14H6L5 6"/><path d="M10 11v5M14 11v5"/></svg>
                                        </button>
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
