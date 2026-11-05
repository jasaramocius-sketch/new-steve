@extends('admin.layouts.app')

@include('partials.page-attributes', ['pageId' => 'admin-page-builder-index-page', 'pageClass' => 'admin-page-builder-index-page'])

@section('page-title', 'Page Builder')

@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Page Builder - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection

@section('content')
<div class="d

@php
    $grouped = [];
    foreach ($items as $item) {
        $grouped[$item['type']][] = $item['model'];
    }
    $typeLabels = ['page' => 'Pages', 'blog' => 'Blog Posts'];
@endphp

@if (! count($grouped))
    <div class="alert alert-info">No editable content found.</div>
@endif

@foreach ($grouped as $type => $models)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom p-2">
            <strong>{{ $typeLabels[$type] ?? ucfirst($type) }}</strong>
            <span class="text-muted small ms-2">({{ count($models) }})</span>
        </div>

        <div class="table-responsive mb-4">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">#</th>
                        <th>Title</th>
                        <th>Slug</th>
                        <th>Blocks</th>
                        <th>Updated</th>
                        <th class="pe-3 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($models as $model)
                        @php $blocksCount = method_exists($model, 'getBlocks') ? count($model->getBlocks()) : 0; @endphp
                        <tr>
                            <td class="ps-3">{{ $model->id }}</td>
                            <td>{{ $model->title ?? $model->name ?? ('#'.$model->id) }}</td>
                            <td><code>{{ $model->slug ?? '-' }}</code></td>
                            <td>
                                <span class="badge {{ $blocksCount ? 'bg-success' : 'bg-secondary' }}">{{ $blocksCount }}</span>
                            </td>
                            <td class="text-muted small">{{ optional($model->updated_at)->format('d M Y, H:i') }}</td>
                            <td class="pe-3 table-action-col">
                                @php
                                    $liveUrl = null;
                                    if (! empty($model->slug)) {
                                        $liveUrl = $type === 'page'
                                            ? route('page.show', $model->slug)
                                            : ($type === 'blog' ? route('blog.show', $model->slug) : null);
                                    }
                                @endphp
                                <div class="action-buttons justify-content-end">
                                    <a href="{{ route('admin.revisions.index', ['model_type' => get_class($model), 'model_id' => $model->id]) }}"
                                       class="action-btn btn-view-history"
                                       data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Revision History">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v5h5"/><path d="M3.05 13A9 9 0 1 0 6 5.3L3 8"/><path d="M12 7v5l4 2"/></svg>
                                    </a>
                                    <a href="{{ route('page-builder.edit', ['type' => $type, 'id' => $model->id]) }}"
                                       class="action-btn btn-edit"
                                       data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Edit Blocks">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </a>
                                    @if($liveUrl)
                                        <a href="{{ $liveUrl }}" target="_blank"
                                           class="action-btn btn-view-live"
                                           data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="View Live Page">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endforeach
@endsection
