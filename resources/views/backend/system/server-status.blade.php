@extends('backend.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-server-status-page', 'pageClass' => 'admin-server-status-page'])
@section('page-title', 'Server Status')
@section('meta_tags')
    @include('partials.meta-tags', ['pageTitle' => 'Server Status - Admin Panel', 'robots' => 'noindex, nofollow'])
@endsection
@section('content')
@php($formatBytes = fn ($bytes) => $bytes === null ? '—' : (round($bytes / 1073741824, 2).' GB'))
<div class="container-fluid px-15px px-lg-25px">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h1 class="h5 mb-0">Server Status</h1>
        <a href="{{ route('admin.system.server-status') }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1"><i class="fas fa-sync"></i> Refresh</a>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header"><h2 class="h6 mb-0">Runtime</h2></div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <tbody>
                            <tr><th class="w-50">Laravel Version</th><td>{{ $runtime['laravel'] }}</td></tr>
                            <tr><th>PHP Version</th><td>{{ $runtime['php'] }}</td></tr>
                            <tr><th>Server Software</th><td>{{ $runtime['server'] }}</td></tr>
                            <tr><th>Operating System</th><td>{{ $runtime['os'] }}</td></tr>
                            <tr><th>Environment</th><td><span class="badge bg-secondary">{{ $runtime['environment'] }}</span></td></tr>
                            <tr><th>Debug Mode</th><td><span class="badge {{ $runtime['debug'] === 'Enabled' ? 'bg-warning text-dark' : 'bg-success' }}">{{ $runtime['debug'] }}</span></td></tr>
                            <tr><th>Timezone</th><td>{{ $runtime['timezone'] }}</td></tr>
                            <tr><th>APP URL</th><td class="text-break">{{ $runtime['url'] }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header"><h2 class="h6 mb-0">Database</h2></div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <tbody>
                            <tr><th class="w-50">Status</th><td><span class="badge {{ $database['connected'] ? 'bg-success' : 'bg-danger' }}">{{ $database['connected'] ? 'Connected' : 'Disconnected' }}</span></td></tr>
                            <tr><th>Driver</th><td>{{ $database['driver'] }}</td></tr>
                            <tr><th>Host</th><td>{{ $database['host'] ?: '—' }}</td></tr>
                            <tr><th>Database</th><td>{{ $database['database'] ?: '—' }}</td></tr>
                            <tr><th>Server Version</th><td>{{ $database['version'] ?: '—' }}</td></tr>
                            <tr><th>Tables</th><td>{{ $database['table_count'] ?? '—' }}</td></tr>
                            @if (!empty($database['error']))
                                <tr><th>Error</th><td class="text-danger small">{{ $database['error'] }}</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header"><h2 class="h6 mb-0">Storage</h2></div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <tbody>
                            <tr><th class="w-50">Disk Total</th><td>{{ $formatBytes($storage['disk_total']) }}</td></tr>
                            <tr><th>Disk Used</th><td>{{ $formatBytes($storage['disk_used']) }}</td></tr>
                            <tr><th>Disk Free</th><td>{{ $formatBytes($storage['disk_free']) }}</td></tr>
                            <tr><th>storage/ Writable</th><td><span class="badge {{ $storage['storage_writable'] ? 'bg-success' : 'bg-danger' }}">{{ $storage['storage_writable'] ? 'Yes' : 'No' }}</span></td></tr>
                            <tr><th>bootstrap/cache Writable</th><td><span class="badge {{ $storage['cache_writable'] ? 'bg-success' : 'bg-danger' }}">{{ $storage['cache_writable'] ? 'Yes' : 'No' }}</span></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header"><h2 class="h6 mb-0">Drivers &amp; Queues</h2></div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <tbody>
                            <tr><th class="w-50">Cache Driver</th><td>{{ $drivers['cache'] }}</td></tr>
                            <tr><th>Session Driver</th><td>{{ $drivers['session'] }}</td></tr>
                            <tr><th>Queue Driver</th><td>{{ $drivers['queue'] }}</td></tr>
                            <tr><th>Filesystem Driver</th><td>{{ $drivers['filesystem'] }}</td></tr>
                            <tr><th>Pending Jobs</th><td>{{ $queue['pending'] ?? '—' }}</td></tr>
                            <tr><th>Failed Jobs</th><td><span class="badge {{ ($queue['failed'] ?? 0) > 0 ? 'bg-danger' : 'bg-success' }}">{{ $queue['failed'] ?? '—' }}</span></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header"><h2 class="h6 mb-0">PHP Limits</h2></div>
                <div class="card-body">
                    @if (! empty($bulkLimits['warnings']))
                        <div class="alert alert-danger small">
                            <strong>Bulk import/export limits are too low.</strong>
                            <ul class="mb-2 ps-3">
                                @foreach ($bulkLimits['warnings'] as $warning)
                                    <li>{{ $warning }}</li>
                                @endforeach
                            </ul>
                            <div>Recommended: upload {{ $bulkLimits['recommended_upload'] }}, post {{ $bulkLimits['recommended_post'] }}, execution {{ $bulkLimits['recommended_execution'] }}s. For PHP-FPM, update <code>public/.user.ini</code> and reload PHP-FPM; for mod_php, <code>public/.htaccess</code> is used.</div>
                        </div>
                    @endif
                    <div class="alert alert-info small mb-0">
                        Bulk imports/exports are queued jobs. Start a worker with <code>php artisan queue:work</code> (or Supervisor) so they run, and use the progress page to watch status and download exports.
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <tbody>
                            <tr><th class="w-50">Memory Limit</th><td>{{ $limits['memory_limit'] ?: '—' }}</td></tr>
                            <tr><th>Max Execution Time</th><td>{{ $limits['max_execution_time'] ?: '—' }}</td></tr>
                            <tr><th>Upload Max Filesize</th><td>{{ $limits['upload_max_filesize'] ?: '—' }}</td></tr>
                            <tr><th>Post Max Size</th><td>{{ $limits['post_max_size'] ?: '—' }}</td></tr>
                            <tr><th>Max Input Vars</th><td>{{ $limits['max_input_vars'] ?: '—' }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header"><h2 class="h6 mb-0">PHP Extensions</h2></div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($extensions as $extension => $loaded)
                            <span class="badge {{ $loaded ? 'bg-success' : 'bg-danger' }}">{{ $extension }}: {{ $loaded ? 'On' : 'Off' }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
