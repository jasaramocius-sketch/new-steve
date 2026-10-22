@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-pages-index-page', 'pageClass' => 'admin-pages-index-page'])
@section('page-title', 'Pages')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Pages - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')

@php $trashedCount = \App\Models\Page::onlyTrashed()->count(); @endphp

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap flex-md-nowrap">
    <h4 class="fw-bold mb-0">All Pages</h4>
    <a href="{{ route('admin.pages.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Page</a>
</div>

<ul class="nav nav-tabs flex-wrap flex-md-nowrap">
    <li class="nav-item">
        <a class="nav-link {{ !request()->has('trashed') ? 'active' : '' }}" href="{{ route('admin.pages.index') }}">Active</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->has('trashed') ? 'active' : '' }}" href="{{ route('admin.pages.index', ['trashed' => 1]) }}">Trash ({{ $trashedCount }})</a>
    </li>
    <li class="nav-item search-form ms-lg-auto">
        @include('admin.partials.search-form', [
            'route' => route('admin.pages.index'),
            'placeholder' => 'Search pages...'
        ])
    </li>
</ul>

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
                Showing {{ $pages->firstItem() }}-{{ $pages->lastItem() }} of {{ $pages->total() }}
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">No.</th>
                        <th>Image</th>
                        <th><a href="{{ sortUrl('title', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Title {!! sortIndicator('title', $sortBy, $sortDir) !!}</a></th>
                        <th><a href="{{ sortUrl('slug', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Slug {!! sortIndicator('slug', $sortBy, $sortDir) !!}</a></th>
                        <th><a href="{{ sortUrl('status', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Status {!! sortIndicator('status', $sortBy, $sortDir) !!}</a></th>
                        <th><a href="{{ sortUrl('updated_at', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Updated {!! sortIndicator('updated_at', $sortBy, $sortDir) !!}</a></th>
                        <th class="pe-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pages as $page)
                    <tr>
                        <td class="ps-3">{{ $pages->firstItem() + $loop->index }}</td>
                        <td>
                            @if($page->image)
                                <img src="{{ storedImageUrl($page->image, 'assets/images/pages') }}" width="50" height="50" class="admin-image-thumb">
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>{{ $page->title }}</td>
                        <td><code>/{{ $page->slug }}</code></td>
                        <td>
                            @if(request()->has('trashed'))
                                <span class="badge btn-sm btn-outline-danger steve-btn">Deleted</span>
                            @else
                                <form action="{{ route('admin.pages.toggle-status', $page->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="badge admin-clickable {{ $page->status ? 'bg-success' : 'bg-danger' }}" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Click to toggle status">
                                        {{ $page->status ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            @endif
                        </td>
                        <td>{{ $page->updated_at ? $page->updated_at->format('d M Y') : '—' }}</td>
                        <td class="pe-3 table-action-col">
                            <div class="action-buttons">
                            @if(request()->has('trashed'))
                                <form action="{{ route('admin.pages.restore', $page->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="action-btn btn-restore" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Restore"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg></button>
                                </form>
                                <form action="{{ route('admin.pages.force-delete', $page->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Permanently delete {{ $page->title }}?')">
                                    @csrf @method('DELETE')
                                    <button class="action-btn btn-cancel" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete Permanently"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>
                                </form>
                            @else
                                <a href="{{ route('admin.pages.edit', $page->id) }}" class="action-btn btn-edit" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Edit"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></a>
                                <form action="{{ route('admin.pages.destroy', $page->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete {{ $page->title }}?')">
                                    @csrf @method('DELETE')
                                    <button class="action-btn btn-cancel" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>
                                </form>
                            @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">{{ request()->has('trashed') ? 'Trash is empty.' : 'No results found.' }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($pages->hasPages())
            <div class="d-flex justify-content-center py-3">{{ $pages->links('vendor.pagination.gs-pagination') }}</div>
        @endif
    </div>
</div>

@endsection
