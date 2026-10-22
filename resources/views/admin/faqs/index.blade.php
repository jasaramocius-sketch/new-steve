@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-faqs-index-page', 'pageClass' => 'admin-faqs-index-page'])
@section('page-title', 'All FAQs')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'All FAQs - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')

@php $trashedCount = \App\Models\Faq::onlyTrashed()->count(); @endphp

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap flex-md-nowrap">
    <div class=""></div>
    <a href="{{ route('admin.faqs.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add FAQ</a>
</div>

<ul class="nav nav-tabs flex-wrap flex-md-nowrap">
    <li class="nav-item">
        <a class="nav-link {{ !request()->has('trashed') ? 'active' : '' }}" href="{{ route('admin.faqs.index') }}">Active</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->has('trashed') ? 'active' : '' }}" href="{{ route('admin.faqs.index', ['trashed' => 1]) }}">Trash ({{ $trashedCount }})</a>
    </li>
    <li class="nav-item search-form ms-lg-auto">
        @include('admin.partials.search-form', [
            'route' => route('admin.faqs.index'),
            'placeholder' => 'Search FAQs...'
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
                Showing {{ $faqs->firstItem() }}-{{ $faqs->lastItem() }} of {{ $faqs->total() }}
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">No.</th>
                        <th><a href="{{ sortUrl('question', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Question {!! sortIndicator('question', $sortBy, $sortDir) !!}</a></th>
                        <th><a href="{{ sortUrl('order', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Order {!! sortIndicator('order', $sortBy, $sortDir) !!}</a></th>
                        <th><a href="{{ sortUrl('status', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Status {!! sortIndicator('status', $sortBy, $sortDir) !!}</a></th>
                        <th><a href="{{ sortUrl('updated_at', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Updated {!! sortIndicator('updated_at', $sortBy, $sortDir) !!}</a></th>
                        <th class="pe-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($faqs as $faq)
                    <tr>
                        <td class="ps-3">{{ $faqs->firstItem() + $loop->index }}</td>
                        <td>{{ Str::limit($faq->question, 60) }}</td>
                        <td>{{ $faq->order }}</td>
                        <td>
                            @if(request()->has('trashed'))
                                <span class="badge bg-secondary">Deleted</span>
                            @else
                                <form action="{{ route('admin.faqs.toggle-status', $faq->id) }}" method="POST" class="d-inline featured-status-btn">
                                    @csrf
                                    <button type="submit" class="btn btn-sm border-0 p-0 steve-btn">
                                        <span class="badge admin-clickable {{ $faq->status ? 'bg-success' : 'bg-danger' }}">
                                            {{ $faq->status ? 'Active' : 'Inactive' }}
                                        </span>
                                    </button>
                                </form>
                            @endif
                        </td>
                        <td>{{ $faq->updated_at ? $faq->updated_at->format('d M Y') : '—' }}</td>
                        <td class="pe-3 table-action-col">
                            <div class="action-buttons">
                            @if(request()->has('trashed'))
                                <form action="{{ route('admin.faqs.restore', $faq->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="action-btn btn-restore" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Restore"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg></button>
                                </form>
                                <form action="{{ route('admin.faqs.force-delete', $faq->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Permanently delete this FAQ?')">
                                    @csrf @method('DELETE')
                                    <button class="action-btn btn-cancel" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete Permanently"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>
                                </form>
                            @else
                                <a href="{{ route('admin.faqs.edit', $faq->id) }}" class="action-btn btn-edit" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Edit"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></a>
                                <form action="{{ route('admin.faqs.destroy', $faq->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this FAQ?')">
                                    @csrf @method('DELETE')
                                    <button class="action-btn btn-cancel" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>
                                </form>
                            @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">{{ request()->has('trashed') ? 'Trash is empty.' : 'No FAQs found.' }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($faqs->hasPages())
            <div class="d-flex justify-content-center py-3">{{ $faqs->links('vendor.pagination.gs-pagination') }}</div>
        @endif
    </div>
</div>

@endsection
