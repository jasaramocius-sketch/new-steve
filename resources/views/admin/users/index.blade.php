@extends('admin.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-users-index-page', 'pageClass' => 'admin-users-index-page'])
@section('page-title', 'User Management')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'User Management - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')
<div class="user-management-page">
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap flex-md-nowrap">
    <div class=""></div>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add User</a>
</div>

<div class="row g-3 mb-4">
    @php
        $totalCount = $users->count();
        $adminCount = $users->where('role','master_admin')->count();
        $staffCount = $users->where('role','staff')->count();
        $customerCount = $users->where('role','customer')->count();
    @endphp
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3 text-center">
            <h5 class="fw-bold mb-1">{{ $totalCount }}</h5>
            <small class="text-muted">Total Users</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3 text-center">
            <h5 class="fw-bold mb-1">{{ $adminCount }}</h5>
            <small class="text-muted">Admin</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3 text-center">
            <h5 class="fw-bold mb-1">{{ $staffCount }}</h5>
            <small class="text-muted">Staff</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm p-3 text-center">
            <h5 class="fw-bold mb-1">{{ $customerCount }}</h5>
            <small class="text-muted">Customer</small>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="d-flex justify-content-between align-items-center p-2 flex-wrap flex-md-nowrap flex-wrap flex-md-nowrap">
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">Show</span>
                <select class="form-select w-auto" onchange="window.location.href=this.value">
                    @foreach([10, 20, 50, 100] as $n)
                        <option value="{{ request()->fullUrlWithQuery(['per_page' => $n, 'page' => null]) }}" {{ (int)request('per_page', 10) === $n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                </select>
                <span class="text-muted small">per page</span>
            </div>
            <div class="text-muted small">
                Showing {{ $users->firstItem() }}-{{ $users->lastItem() }} of {{ $users->total() }}
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">No.</th>
                        <th><a href="{{ sortUrl('name', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Name {!! sortIndicator('name', $sortBy, $sortDir) !!}</a></th>
                        <th><a href="{{ sortUrl('email', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Email Address {!! sortIndicator('email', $sortBy, $sortDir) !!}</a></th>
                        <th>Phone</th>
                        <th><a href="{{ sortUrl('role', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Role {!! sortIndicator('role', $sortBy, $sortDir) !!}</a></th>
                        <th><a href="{{ sortUrl('status', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Status {!! sortIndicator('status', $sortBy, $sortDir) !!}</a></th>
                        <th><a href="{{ sortUrl('city', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">City {!! sortIndicator('city', $sortBy, $sortDir) !!}</a></th>
                        <th><a href="{{ sortUrl('created_at', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Joined {!! sortIndicator('created_at', $sortBy, $sortDir) !!}</a></th>
                        <th class="pe-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td class="ps-3">{{ $users->firstItem() + $loop->index }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="admin-user-avatar">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <span class="user-name text-transform-capitalize">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->phone ?? '—' }}</td>
                        <td>
                            @if($user->role === 'master_admin')
                                <span class="badge bg-light text-danger border border-danger-subtle">Admin</span>
                            @elseif($user->role === 'staff')
                                <span class="badge bg-light text-info border border-info-subtle">Staff</span>
                            @else
                                <span class="badge bg-light text-success border border-success-subtle">Customer</span>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('admin.users.toggle-status', $user->id) }}" method="POST" class="d-inline featured-status-btn">
                                @csrf
                                <button type="submit" class="btn btn-sm border-0 p-0 steve-btn">
                                    <span class="badge admin-clickable {{ $user->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                        {{ $user->status === 'active' ? 'Active' : 'Inactive' }}
                                    </span>
                                </button>
                            </form>
                        </td>
                        <td>{{ $user->city ?? '—' }}</td>
                        <td>{{ $user->created_at ? $user->created_at->format('d M Y') : '—' }}</td>
                        <td class="pe-3 table-action-col">
                            <div class="action-buttons">
                            <a href="{{ route('admin.users.edit', $user->id) }}" class="action-btn btn-edit" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Edit"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></a>
                            @if($user->role !== 'master_admin')
                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete {{ $user->name }}?')">
                                @csrf @method('DELETE')
                                <button class="action-btn btn-cancel" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>
                            </form>
                            @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">No results found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="d-flex justify-content-center py-3">{{ $users->links('vendor.pagination.gs-pagination') }}</div>
        @endif
    </div>
</div>
</div>
@endsection