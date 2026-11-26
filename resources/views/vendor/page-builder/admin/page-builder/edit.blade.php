@extends('backend.layouts.app')

@include('partials.page-attributes', ['pageId' => 'admin-page-builder-edit-page', 'pageClass' => 'admin-page-builder-edit-page'])

@section('page-title', 'Edit Blocks')

@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Edit Blocks - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection

@section('content')
@php
    $designMode = ($type === 'page' && method_exists($model, 'designMode')) ? $model->designMode() : null;
    $modeLabels = ['classic' => 'Classic', 'builder' => 'Builder', 'both' => 'Classic + Builder'];
@endphp
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="fas fa-layer-group me-1 text-primary"></i>
            {{ $model->title ?? $model->name ?? ('Content Blocks') }}
            @if($designMode)
                <span class="badge bg-secondary ms-2 align-middle">{{ $modeLabels[$designMode] ?? $designMode }}</span>
            @endif
        </h4>
        <p class="text-muted mb-0 small">
            <a href="{{ route('page-builder.index') }}" class="text-decoration-none">&larr; Page Builder</a>
            <span class="mx-1">/</span>
            <span class="text-capitalize">{{ $type }}</span>
            <span class="mx-1">/</span>
            <code>{{ $model->slug ?? '#'.$model->id }}</code>
            @if($type === 'page')
                <span class="mx-1">/</span>
                <a href="{{ route('admin.pages.edit', $model->id) }}" class="text-decoration-none">Classic editor</a>
            @endif
        </p>
        @if($designMode === 'classic')
            <div class="alert alert-warning small mt-2 mb-0">
                This page is currently in <strong>classic</strong> mode, so saved blocks will not show on the live page.
                Saving blocks here will switch it to <strong>Page Builder</strong> mode automatically.
            </div>
        @endif
    </div>

    <div class="d-flex gap-2 flex-wrap">
        @if($type === 'page')
            <a href="{{ route('admin.pages.edit', $model->id) }}" class="btn btn-outline-primary">
                <i class="fas fa-edit me-1"></i> Classic Editor
            </a>
        @endif
        @if (! empty($liveUrl))
            <a href="{{ $liveUrl }}" target="_blank" class="btn btn-outline-secondary">
                <i class="fas fa-external-link-alt me-1"></i> View Live
            </a>
        @endif
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        @include('page-builder::editor.index', [
            'model' => $model,
            'type' => $type,
            'blocks' => $blocks,
        ])
    </div>
</div>
@endsection

@push('page-builder-css')
    <link rel="stylesheet" href="{{ asset('vendor/page-builder/css/page-builder.css') }}?v=1.0.1">
@endpush

@push('page-builder-js')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script src="{{ asset('vendor/page-builder/js/page-builder.js') }}?v=1.0.0"></script>
@endpush
