@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-blogs-index-page', 'pageClass' => 'admin-blogs-index-page'])
@section('page-title', 'All Blogs')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'All Blogs - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')

@php $trashedCount = \App\Models\Blog::onlyTrashed()->count(); @endphp

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap flex-md-nowrap">
    <div class=""></div>
    <a href="{{ route('admin.blogs.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Blog</a>
</div>

<ul class="nav nav-tabs flex-wrap flex-md-nowrap">
    <li class="nav-item">
        <a class="nav-link {{ !request()->has('trashed') ? 'active' : '' }}" href="{{ route('admin.blogs.index') }}">Active</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->has('trashed') ? 'active' : '' }}" href="{{ route('admin.blogs.index', ['trashed' => 1]) }}">Trash ({{ $trashedCount }})</a>
    </li>
    <li class="nav-item search-form ms-lg-auto">
        @include('admin.partials.search-form', [
            'route' => route('admin.blogs.index'),
            'placeholder' => 'Search blogs...'
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
                Showing {{ $blogs->firstItem() }}-{{ $blogs->lastItem() }} of {{ $blogs->total() }}
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">No.</th>
                        <th>Image</th>
                        <th><a href="{{ sortUrl('title', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Title {!! sortIndicator('title', $sortBy, $sortDir) !!}</a></th>
                        <th><a href="{{ sortUrl('status', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Status {!! sortIndicator('status', $sortBy, $sortDir) !!}</a></th>
                        <th><a href="{{ sortUrl('created_at', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Date {!! sortIndicator('created_at', $sortBy, $sortDir) !!}</a></th>
                        <th class="pe-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($blogs as $blog)
                    <tr>
                        <td class="ps-3">{{ $blogs->firstItem() + $loop->index }}</td>
                        <td>
                            @if($blog->image)
                                <img src="{{ storedImageUrl($blog->image, 'assets/images/blogs') }}" width="50" height="50" class="admin-image-thumb" onerror="this.onerror=null;this.src='{{ asset('assets/images/placeholder.png') }}'">
                            @else
                                <img src="{{ asset('assets/images/blogs/placeholder.jpg') }}" width="50" height="50" class="admin-image-thumb">
                            @endif
                        </td>
                        <td>{{ $blog->title ?? 'Untitled' }}</td>
                        <td>
                            @if(request()->has('trashed'))
                                <span class="badge admin-clickable {{ $blog->status === 'published' ? 'bg-success' : 'bg-light text-warning border border-warning-subtle' }}">Deleted</span>
                            @else
                                <form action="{{ route('admin.blogs.toggle-status', $blog->id) }}" method="POST" class="d-inline featured-status-btn">
                                    @csrf
                                    <button type="submit" class="btn btn-sm border-0 p-0 steve-btn">
                                        <span class="badge admin-clickable {{ $blog->status === 'published' ? 'bg-success' : ($blog->status === 'scheduled' ? 'bg-warning text-dark border border-warning-subtle' : 'bg-danger border border-danger-subtle') }}">
                                            {{ ucfirst($blog->status ?? 'draft') }}
                                        </span>
                                    </button>
                                </form>
                            @endif
                        </td>
                        <td>{{ $blog->post_date ? $blog->post_date->format('d M Y') : '—' }}</td>
                        <td class="pe-3 table-action-col">
                            <div class="action-buttons">
                            @if(request()->has('trashed'))
                                <form action="{{ route('admin.blogs.restore', $blog->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="action-btn btn-restore" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Restore"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg></button>
                                </form>
                                <form action="{{ route('admin.blogs.force-delete', $blog->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Permanently delete {{ $blog->title }}?')">
                                    @csrf @method('DELETE')
                                    <button class="action-btn btn-cancel" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete Permanently"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>
                                </form>
                            @else
                                <a href="{{ route('admin.blogs.edit', $blog->id) }}" class="action-btn btn-edit" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Edit"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></a>
                                <form action="{{ route('admin.blogs.destroy', $blog->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this blog?')">
                                    @csrf @method('DELETE')
                                    <button class="action-btn btn-cancel" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>
                                </form>
                            @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">{{ request()->has('trashed') ? 'Trash is empty.' : 'No results found.' }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($blogs->hasPages())
            <div class="d-flex justify-content-center py-3">{{ $blogs->links('vendor.pagination.gs-pagination') }}</div>
        @endif
    </div>
</div>

@endsection
