@extends('backend.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-pages-index-page', 'pageClass' => 'admin-pages-index-page'])
@section('page-title', 'All Pages')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Pages - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')

@php $trashedCount = \App\Models\Page::onlyTrashed()->count(); @endphp


<ul class="nav nav-tabs flex-wrap flex-md-nowrap">
    <li class="nav-item">
        <a class="nav-link {{ !request()->has('trashed') ? 'active' : '' }}" href="{{ route('admin.pages.index') }}">Active</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->has('trashed') ? 'active' : '' }}" href="{{ route('admin.pages.index', ['trashed' => 1]) }}">Trash ({{ $trashedCount }})</a>
    </li>
</ul>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0 d-flex gap-1 flex-wrap justify-content-between align-items-center">
        <div class="d-flex justify-content-between align-items-center p-2 flex-wrap flex-wrap">
            <div class="pages-index-page-nav d-flex align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">Show</span>
                <select class="form-select show-per-page w-auto" onchange="window.location.href=this.value">
                    @foreach([10, 20, 50, 100] as $n)
                        <option value="{{ request()->fullUrlWithQuery(['per_page' => $n, 'page' => null]) }}" {{ (int)request('per_page', 10) === $n ? 'selected' : '' }}>{{ $n }} per page</option>
                    @endforeach
                </select>
                </div>
                    @include('backend.partials.search-form', [
                        'route' => route('admin.pages.index'),
                        'placeholder' => 'Search pages...'
                    ])
                    <a href="{{ route('admin.pages.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Page</a>
                    <a href="{{ route('page-builder.index', ['type' => 'page']) }}" class="btn btn-outline-primary"><i class="fas fa-layer-group me-1"></i> Page Builder</a>
                </div>
                <div class="pages-index-page-nav-action d-grid align-items-center gap-2">
                </div>
            </div>
            <div class="text-muted small px-2">
                Page {{ $pages->currentPage() }} of {{ $pages->lastPage() }} ({{ $pages->total() }} Total)
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">No.</th>
                        <th>Image</th>
                        <th><a href="{{ sortUrl('title', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Title {!! sortIndicator('title', $sortBy, $sortDir) !!}</a></th>
                        <th><a href="{{ sortUrl('slug', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">URL {!! sortIndicator('slug', $sortBy, $sortDir) !!}</a></th>
                        <th>Design</th>
                        <th><a href="{{ sortUrl('status', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Status {!! sortIndicator('status', $sortBy, $sortDir) !!}</a></th>
                        <th><a href="{{ sortUrl('updated_at', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Updated {!! sortIndicator('updated_at', $sortBy, $sortDir) !!}</a></th>
                        <th class="pe-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pages as $page)
                    <tr>
                        <td class="ps-3 admin-row-number">{{ $pages->firstItem() + $loop->index }}</td>
                        <td>
                            @if($page->image)
                                <img src="{{ storedImageUrl($page->image, 'assets/images/pages') }}" width="50" height="50" class="admin-image-thumb">
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>{{ $page->title }}</td>
                        <td>
                            @if(request()->has('trashed'))
                                <code class="text-muted text-decoration-line-through">/{{ $page->slug }}</code>
                            @elseif(! $page->status)
                                <code class="text-muted" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Inactive — this URL returns 404 until the page is activated">/{{ $page->slug }}</code>
                            @else
                                <a href="{{ route('page.show', $page->slug) }}" target="_blank" rel="noopener" class="text-decoration-none" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="{{ route('page.show', $page->slug) }}">
                                    <code>/{{ $page->slug }}</code>
                                </a>
                            @endif
                        </td>
                        <td>
                            @php
                                $mode = $page->designMode();
                                $blocksCount = count($page->getBlocks());
                                $modeLabels = ['classic' => 'Classic', 'builder' => 'Builder', 'both' => 'Both'];
                            @endphp
                            <span class="badge {{ $mode === 'builder' ? 'bg-primary' : ($mode === 'both' ? 'bg-info' : 'bg-secondary') }}">
                                {{ $modeLabels[$mode] }}
                            </span>
                            @if ($blocksCount)
                                <span class="text-muted small d-block">{{ $blocksCount }} block{{ $blocksCount === 1 ? '' : 's' }}</span>
                            @endif
                        </td>
                        <td>
                            @if(request()->has('trashed'))
                                <span class="badge btn-sm bg-danger btn-outline-danger">Deleted</span>
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
                                @if($page->status)
                                    <a href="{{ route('page.show', $page->slug) }}" target="_blank" rel="noopener" class="action-btn btn-view" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="View public page"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></a>
                                @endif
                                <a href="{{ route('page-builder.edit', ['type' => 'page', 'id' => $page->id]) }}" class="action-btn btn-builder" aria-label="Open in Page Builder" title="Open in Page Builder" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Open in Page Builder ({{ $blocksCount }} block{{ $blocksCount === 1 ? '' : 's' }})"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg></a>
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
                        <td colspan="8" class="text-center py-4 text-muted">{{ request()->has('trashed') ? 'Trash is empty.' : 'No results found.' }}</td>
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
