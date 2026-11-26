@extends('backend.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-products-bulk-progress-page', 'pageClass' => 'admin-products-bulk-progress-page'])
@section('page-title', 'Bulk Operation Progress')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Bulk Operation Progress - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')
@php
    $percent = $operation['total'] > 0
        ? min(100, (int) round(($operation['processed'] / $operation['total']) * 100))
        : ($operation['status'] === 'completed' ? 100 : 0);
@endphp
<div class="container-fluid px-15px px-lg-25px">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h1 class="h5 mb-1">{{ $operation['type'] === 'export' ? 'Product Export' : 'Product Import' }} Progress</h1>
            <div class="text-muted small text-break">{{ $operation['file_name'] }}</div>
        </div>
        <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left"></i> Back to Products</a>
    </div>

    <div id="bulk-status" class="card border-0 shadow-sm" data-polling-url="{{ $pollingUrl }}">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="h6 mb-0">{{ $operation['type'] === 'export' ? 'Exporting products' : 'Importing products' }}</h2>
                <span id="bulk-status-badge" class="badge bg-{{ $operation['status'] === 'completed' ? 'success' : ($operation['status'] === 'failed' ? 'danger' : 'primary') }}">
                    {{ ucfirst($operation['status']) }}
                </span>
            </div>

            <div class="progress mb-3" style="height: 22px;">
                <div id="bulk-progress-bar" class="progress-bar progress-bar-striped {{ $operation['status'] === 'processing' ? 'progress-bar-animated' : '' }}"
                     role="progressbar" style="width: {{ $percent }}%" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100">
                    {{ $percent }}%
                </div>
            </div>

            <p id="bulk-message" class="mb-3">{{ $operation['message'] }}</p>

            <div class="row g-3 mb-3">
                <div class="col-md-4"><strong>Processed:</strong> <span id="bulk-processed">{{ $operation['processed'] }}</span> / <span id="bulk-total">{{ $operation['total'] }}</span></div>
                <div class="col-md-4"><strong>Errors:</strong> <span id="bulk-error-count">{{ $operation['error_count'] ?? 0 }}</span></div>
                <div class="col-md-4"><strong>Started:</strong> {{ $operation['created_at'] }}</div>
            </div>

            <div id="bulk-result" class="alert alert-light small mb-3">
                @if (! empty($operation['result']))
                    @foreach ($operation['result'] as $key => $value)
                        <span class="me-3">{{ str_replace('_', ' ', ucfirst($key)) }}: <strong>{{ $value }}</strong></span>
                    @endforeach
                @else
                    <span class="text-muted">Waiting for the queue worker to start this job.</span>
                @endif
            </div>

            @if (! empty($operation['errors']))
                <div class="alert alert-warning">
                    <strong>Recent issues</strong>
                    <ul id="bulk-errors" class="mb-0 small">
                        @foreach ($operation['errors'] as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @else
                <div id="bulk-errors" class="d-none"></div>
            @endif

            <div class="d-flex gap-2 flex-wrap">
                <a id="bulk-download"
                   href="{{ $operation['download_url'] ?? '#' }}"
                   class="btn btn-primary steve-btn {{ $operation['status'] === 'completed' && $operation['download_url'] ? '' : 'd-none' }}">
                    <i class="fas fa-download"></i> Download CSV
                </a>
                <a href="{{ route('admin.products.import-form') }}" class="btn btn-outline-secondary"><i class="fas fa-upload"></i> New Import</a>
            </div>

            <div class="form-text mt-3">
                Keep this page open. Progress is refreshed automatically. Bulk work runs in the background, so you can leave once the job is queued and return to this page later.
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const root = document.getElementById('bulk-status');
    if (! root) {
        return;
    }

    const url = root.dataset.pollingUrl;
    const finished = ['completed', 'failed'];
    const labels = {
        imported: 'Imported',
        skipped: 'Skipped',
        categories_created: 'Categories created',
        brands_created: 'Brands created',
        sellers_created: 'Sellers created',
        gallery_images_imported: 'Gallery images',
        exported: 'Exported'
    };

    const badge = document.getElementById('bulk-status-badge');
    const bar = document.getElementById('bulk-progress-bar');
    const message = document.getElementById('bulk-message');
    const processed = document.getElementById('bulk-processed');
    const total = document.getElementById('bulk-total');
    const errorCount = document.getElementById('bulk-error-count');
    const result = document.getElementById('bulk-result');
    const errors = document.getElementById('bulk-errors');
    const download = document.getElementById('bulk-download');

    function render(state) {
        const percent = state.total > 0
            ? Math.min(100, Math.round((state.processed / state.total) * 100))
            : (state.status === 'completed' ? 100 : 0);

        badge.textContent = state.status.charAt(0).toUpperCase() + state.status.slice(1);
        badge.className = 'badge bg-' + (state.status === 'completed' ? 'success' : (state.status === 'failed' ? 'danger' : 'primary'));
        bar.style.width = percent + '%';
        bar.setAttribute('aria-valuenow', String(percent));
        bar.textContent = percent + '%';
        bar.classList.toggle('progress-bar-animated', state.status === 'processing');
        message.textContent = state.message || '';
        processed.textContent = state.processed;
        total.textContent = state.total;
        errorCount.textContent = state.error_count || 0;

        result.textContent = '';
        const entries = Object.entries(state.result || {});
        if (entries.length === 0) {
            const span = document.createElement('span');
            span.className = 'text-muted';
            span.textContent = 'Waiting for the queue worker to start this job.';
            result.appendChild(span);
        } else {
            entries.forEach(([key, value]) => {
                const span = document.createElement('span');
                span.className = 'me-3';
                span.textContent = (labels[key] || key.replace(/_/g, ' ')) + ': ' + value;
                result.appendChild(span);
            });
        }

        errors.textContent = '';
        errors.className = (state.errors || []).length ? 'mb-0 small' : 'd-none';
        (state.errors || []).forEach(function (error) {
            const li = document.createElement('li');
            li.textContent = error;
            errors.appendChild(li);
        });

        if (state.status === 'completed' && state.download_url) {
            download.href = state.download_url;
            download.classList.remove('d-none');
        } else {
            download.classList.add('d-none');
        }
    }

    async function poll() {
        try {
            const response = await fetch(url, {headers: {'X-Requested-With': 'XMLHttpRequest'}});
            if (response.ok) {
                const state = await response.json();
                render(state);
                if (finished.indexOf(state.status) === -1) {
                    setTimeout(poll, 2000);
                }
                return;
            }
        } catch (error) {
            // Temporary network issue — try again below.
        }
        setTimeout(poll, 4000);
    }

    poll();
})();
</script>
@endpush
