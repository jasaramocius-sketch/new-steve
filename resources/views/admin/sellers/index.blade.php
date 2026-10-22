@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-sellers-index-page', 'pageClass' => 'admin-sellers-index-page'])
@section('page-title', 'All Sellers')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'All Sellers - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')

 <div class="d-flex justify-content-between align-items-center mb-3">
    <div class=""></div>
    <a href="{{ route('admin.sellers.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Seller</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="d-flex justify-content-between align-items-center p-2 flex-wrap flex-md-nowrap">
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
                Showing {{ $sellers->firstItem() }}-{{ $sellers->lastItem() }} of {{ $sellers->total() }}
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">No.</th>
                        <th>Image</th>
                        <th><a href="{{ sortUrl('name', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Name {!! sortIndicator('name', $sortBy, $sortDir) !!}</a></th>
                        <th>Location</th>
                        <th><a href="{{ sortUrl('followers', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Followers {!! sortIndicator('followers', $sortBy, $sortDir) !!}</a></th>
                        <th><a href="{{ sortUrl('status', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Status {!! sortIndicator('status', $sortBy, $sortDir) !!}</a></th>
                        <th class="pe-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sellers as $seller)
                    <tr>
                        <td class="ps-3">{{ $sellers->firstItem() + $loop->index }}</td>
                        <td>
                            @if($seller->image)
                                <img src="{{ storedImageUrl($seller->image, 'assets/images') }}" width="50" height="50" class="admin-image-thumb">
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>{{ $seller->name }}</td>
                        <td>{{ $seller->location ?? '—' }}</td>
                        <td>
                            <a href="{{ route('admin.sellers.followers', $seller->id) }}" class="badge bg-light text-dark text-decoration-none" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="View followers">
                                <strong>{{ $seller->followers_count }}</strong> Followers
                            </a>
                        </td>
                        <td>
                            <form action="{{ route('admin.sellers.toggle-status', $seller->id) }}" method="POST" class="d-inline featured-status-btn">
                                @csrf
                                <button type="submit" class="btn btn-sm border-0 p-0 steve-btn">
                                    <span class="badge admin-clickable {{ $seller->status ? 'bg-success' : 'bg-danger' }}">
                                        {{ $seller->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </button>
                            </form>
                        </td>
                        <td class="pe-3 table-action-col">
                            <div class="action-buttons">
                            <a href="{{ route('admin.sellers.followers', $seller->id) }}" class="action-btn btn-view" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Followers">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </a>
                            <a href="{{ route('admin.sellers.edit', $seller->id) }}" class="action-btn btn-edit" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Edit">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </a>
                            <form action="{{ route('admin.sellers.destroy', $seller->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete {{ $seller->name }}?')">
                                @csrf @method('DELETE')
                                <button class="action-btn btn-cancel" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
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
        @if($sellers->hasPages())
            <div class="d-flex justify-content-center py-3">{{ $sellers->links('vendor.pagination.gs-pagination') }}</div>
        @endif
    </div>
</div>

@endsection
