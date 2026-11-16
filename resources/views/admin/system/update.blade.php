@extends('admin.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-system-update-page', 'pageClass' => 'admin-system-update-page'])
@section('page-title', 'Update')
@section('meta_tags')
    @include('partials.meta-tags', ['pageTitle' => 'Update - Admin Panel', 'robots' => 'noindex, nofollow'])
@endsection
@section('content')
<div class="container-fluid px-15px px-lg-25px">
    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h1 class="h5 mb-0">Update &amp; System Information</h1>
        <a href="{{ route('admin.system.update') }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1"><i class="fas fa-sync"></i> Refresh</a>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header"><h2 class="h6 mb-0">Version Information</h2></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr><th class="w-50">Application</th><td>{{ $current['app_name'] }}</td></tr>
                            <tr><th>Application Version</th><td><span class="badge bg-primary">{{ $current['app_version'] }}</span></td></tr>
                            <tr><th>Laravel Version</th><td>{{ $current['laravel_version'] }}</td></tr>
                            <tr><th>PHP Version</th><td>{{ $current['php_version'] }}</td></tr>
                            <tr><th>Environment</th><td><span class="badge bg-secondary">{{ $current['environment'] }}</span></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header"><h2 class="h6 mb-0">Source Control</h2></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <tr><th class="w-50">Git Available</th><td><span class="badge {{ $git['available'] ? 'bg-success' : 'bg-secondary' }}">{{ $git['available'] ? 'Yes' : 'No' }}</span></td></tr>
                            <tr><th>Branch</th><td>{{ $git['branch'] ?? '—' }}</td></tr>
                            <tr><th>Commit</th><td><code>{{ $git['short_commit'] ?? $git['commit'] ?? '—' }}</code></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header"><h2 class="h6 mb-0">Database Migrations</h2></div>
                <div class="card-body">
                    <p class="mb-2">Ran migrations: <strong>{{ $migrationCount ?? '—' }}</strong></p>
                    @if ($pendingMigrations === [])
                        <div class="alert alert-success mb-0"><i class="fas fa-check-circle me-1"></i> All migration files have been applied.</div>
                    @else
                        <div class="alert alert-warning mb-2"><i class="fas fa-exclamation-triangle me-1"></i> {{ count($pendingMigrations) }} pending migration(s) detected. Run <code>php artisan migrate</code> from the server.</div>
                        <ul class="small mb-0 text-break">
                            @foreach ($pendingMigrations as $migration)
                                <li>{{ $migration }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header"><h2 class="h6 mb-0">Maintenance</h2></div>
                <div class="card-body">
                    <ul class="list-unstyled small mb-3 d-flex flex-column gap-1">
                        <li class="d-flex justify-content-between"><span>Config Cached</span><span class="badge {{ $maintenance['config_cached'] ? 'bg-success' : 'bg-secondary' }}">{{ $maintenance['config_cached'] ? 'Yes' : 'No' }}</span></li>
                        <li class="d-flex justify-content-between"><span>Routes Cached</span><span class="badge {{ $maintenance['routes_cached'] ? 'bg-success' : 'bg-secondary' }}">{{ $maintenance['routes_cached'] ? 'Yes' : 'No' }}</span></li>
                        <li class="d-flex justify-content-between"><span>Compiled View Files</span><span class="badge bg-secondary">{{ $maintenance['view_cache_files'] }}</span></li>
                    </ul>
                    <div class="d-flex flex-wrap gap-2">
                        <form action="{{ route('admin.system.update.clear-all') }}" method="POST">@csrf<button class="btn btn-primary d-inline-flex align-items-center gap-1"><i class="fas fa-broom"></i> Clear All Caches</button></form>
                        <form action="{{ route('admin.system.update.clear-config') }}" method="POST">@csrf<button class="btn  btn-outline-secondary">Config</button></form>
                        <form action="{{ route('admin.system.update.clear-route') }}" method="POST">@csrf<button class="btn btn-outline-secondary">Routes</button></form>
                        <form action="{{ route('admin.system.update.clear-view') }}" method="POST">@csrf<button class="btn btn-outline-secondary">Views</button></form>
                        <form action="{{ route('admin.system.update.clear-cache') }}" method="POST">@csrf<button class="btn btn-outline-secondary">App Cache</button></form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-info mb-0">
        <strong>Note:</strong> This page does not download or apply application updates automatically. To update, deploy the new code/package on the server, run <code>composer install</code> (if dependencies changed) and <code>php artisan migrate</code>, then clear caches.
    </div>
</div>
@endsection
