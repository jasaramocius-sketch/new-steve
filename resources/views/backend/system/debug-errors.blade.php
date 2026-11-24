@extends('backend.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-debug-errors-page', 'pageClass' => 'admin-debug-errors-page'])
@section('page-title', '500 Debug')
@section('meta_tags')
    @include('partials.meta-tags', ['pageTitle' => '500 Error Debugger - Admin Panel', 'robots' => 'noindex, nofollow'])
@endsection
@section('content')
<div class="container-fluid px-15px px-lg-25px">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h1 class="h5 mb-0">500 Error Debugger</h1>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.system.debug-errors') }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1"><i class="fas fa-sync"></i> Refresh</a>
            <a href="{{ route('admin.system.server-status') }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1"><i class="fas fa-server"></i> Server Status</a>
        </div>
    </div>

    <div class="alert alert-info small mb-3">
        <div><strong>Log file:</strong> <code>{{ $logPath }}</code></div>
        <div class="mt-1"><strong>CLI:</strong> <code>php artisan debug:last-error --limit=20</code></div>
        <div class="mt-1">Records are sanitized: no request body, cookies, headers or query strings are stored.</div>
    </div>

    @if (empty($entries))
        <div class="alert alert-success">
            No captured 500 errors yet. The next server error will be written to the log automatically.
        </div>
    @else
        @foreach ($entries as $entry)
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <span class="badge bg-danger">{{ $entry['exception'] ?? 'Throwable' }}</span>
                        <strong class="ms-1">{{ $entry['message'] ?? '-' }}</strong>
                    </div>
                    <span class="text-muted small">{{ $entry['time'] ?? '' }} @if (!empty($entry['id'])) · {{ $entry['id'] }}@endif</span>
                </div>
                <div class="card-body">
                    <div class="row g-2 mb-2">
                        <div class="col-md-6"><strong>Request:</strong> <code>{{ ($entry['method'] ?? '') }} {{ ($entry['path'] ?? '') }}</code></div>
                        <div class="col-md-6"><strong>Route:</strong> <code>{{ $entry['route'] ?? $entry['route_uri'] ?? '-' }}</code></div>
                    </div>

                    <div class="alert alert-danger py-2 px-3 mb-2">
                        <strong>Root cause:</strong>
                        <code class="text-break">{{ $entry['file'] ?? '-' }}:{{ $entry['line'] ?? '-' }}</code>
                    </div>

                    @if (!empty($entry['frames']) && is_array($entry['frames']))
                        <details>
                            <summary class="small fw-semibold">Stack frames (first {{ count($entry['frames']) }})</summary>
                            <ol class="small mb-0 mt-2">
                                @foreach ($entry['frames'] as $frame)
                                    <li>
                                        <code>{{ $frame['file'] ?? '-' }}:{{ $frame['line'] ?? '-' }}</code>
                                        @if (!empty($frame['call'])) <span class="text-muted">→ {{ $frame['call'] }}</span>@endif
                                    </li>
                                @endforeach
                            </ol>
                        </details>
                    @endif

                    <div class="small text-muted mt-2">
                        PHP {{ $entry['php'] ?? '-' }} · Peak memory {{ $entry['memory_peak_mb'] ?? '-' }} MB · User {{ $entry['user_id'] ?? 'guest' }}
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
