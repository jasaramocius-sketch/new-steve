@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-staff-index-page', 'pageClass' => 'admin-staff-index-page'])
@section('page-title', 'Staff')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Staff - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')

<div class="container-fluid">

    <div class="card border-0 shadow-sm">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="fw-bold mb-0">Staff Management</h4>

            @if(Auth::check() && in_array(Auth::user()->role, ['master_admin', 'admin']))
                <a href="{{ route('admin.staff.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Staff
                </a>
            @endif
        </div>

        <div class="card-body">

            @php $staffList = $users ?? $staffs ?? collect(); @endphp

            <div class="d-flex justify-content-between align-items-center px-0 pb-2">
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
                    Showing {{ $staffList->firstItem() }}-{{ $staffList->lastItem() }} of {{ $staffList->total() }}
                </div>
            </div>

            <div class="table-responsive">

                <table class="table table-hover mb-0">

                    <thead class="table-light">
                        <tr>
                            <th><a href="{{ sortUrl('id', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">ID {!! sortIndicator('id', $sortBy, $sortDir) !!}</a></th>
                            <th>Image</th>
                            <th><a href="{{ sortUrl('name', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Name {!! sortIndicator('name', $sortBy, $sortDir) !!}</a></th>
                            <th><a href="{{ sortUrl('email', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Email Address {!! sortIndicator('email', $sortBy, $sortDir) !!}</a></th>
                            <th><a href="{{ sortUrl('role', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Role {!! sortIndicator('role', $sortBy, $sortDir) !!}</a></th>
                            <th><a href="{{ sortUrl('created_at', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Joined {!! sortIndicator('created_at', $sortBy, $sortDir) !!}</a></th>
                            <th width="180">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($staffList as $user)

                            <tr>

                                <td>{{ $staffList->firstItem() + $loop->index }}</td>

                                <td>
                                    <img
                                        src="{{ storedImageUrl($user->avatar, 'assets/images', 'assets/images/avatar-place.png') }}"
                                        width="60"
                                        class="rounded"
                                        alt="{{ $user->name }}">
                                </td>

                                <td>{{ $user->name }}</td>

                                <td>{{ $user->email }}</td>

                                <td>
                                    <span class="badge bg-light text-primary border border-primary-subtle">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>

                                <td>
                                    {{ $user->created_at->format('d M Y') }}
                                </td>

                                <td class="table-action-col pe-3">
                                    <div class="action-buttons">
                                    <a href="{{ route('admin.staff.edit', $user->id) }}" class="action-btn btn-edit" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Edit"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></a>

                                    <form action="{{ route('admin.staff.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this staff member?')">
                                        @csrf @method('DELETE')
                                        <button class="action-btn btn-cancel" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>
                                    </form>
                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No results found.</td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            @if($staffList->hasPages())
                <div class="d-flex justify-content-center">{{ $staffList->links('vendor.pagination.gs-pagination') }}</div>
            @endif

        </div>

    </div>

</div>

@endsection