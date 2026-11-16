@extends('admin.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-file-system-cache-settings-page', 'pageClass' => 'admin-file-system-cache-settings-page'])
@section('page-title', 'File System & Cache')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'File System & Cache - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')
<div class="container-fluid px-15px px-lg-25px">
    <!-- @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif -->

    <div class="row">
        <div class="col-xxl-6 mb-3">
            <div class="card h-100">
                <div class="card-header"><h2 class="h6 mb-0">File System (S3) Configuration</h2></div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.file-system-cache.filesystem.update') }}" method="POST" autocomplete="off">
                        @csrf
                        <div class="form-group row align-items-center">
                            <label class="col-md-4 col-form-label" for="filesystem-driver">Default Filesystem Driver</label>
                            <div class="col-md-8">
                                <select id="filesystem-driver" name="filesystem_driver" class="form-control form-select">
                                    <option value="local" @selected($filesystemDriver === 'local')>Local</option>
                                    <option value="public" @selected($filesystemDriver === 'public')>Public</option>
                                    <option value="s3" @selected($filesystemDriver === 's3')>Amazon S3</option>
                                </select>
                                <small class="text-muted">Select S3 to store uploads on Amazon S3 (requires credentials below).</small>
                            </div>
                        </div>

                        <div id="s3-fields">
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label" for="aws-access-key-id">AWS_ACCESS_KEY_ID</label>
                                <div class="col-md-8">
                                    <input id="aws-access-key-id" type="text" name="aws_access_key_id" class="form-control" value="{{ old('aws_access_key_id', $awsAccessKeyId) }}" placeholder="AWS access key ID">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label" for="aws-secret-access-key">AWS_SECRET_ACCESS_KEY</label>
                                <div class="col-md-8">
                                    <input id="aws-secret-access-key" type="password" name="aws_secret_access_key" class="form-control" value="" autocomplete="new-password" placeholder="{{ $awsSecretConfigured ? 'Saved securely; leave blank to keep current' : 'AWS secret access key' }}">
                                    @if ($awsSecretConfigured) <small class="text-muted">A secret is saved. Leave blank to keep the current value.</small> @endif
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label" for="aws-default-region">AWS_DEFAULT_REGION</label>
                                <div class="col-md-8">
                                    <input id="aws-default-region" type="text" name="aws_default_region" class="form-control" value="{{ old('aws_default_region', $awsDefaultRegion) }}" placeholder="e.g. us-east-1">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label" for="aws-bucket">AWS_BUCKET</label>
                                <div class="col-md-8">
                                    <input id="aws-bucket" type="text" name="aws_bucket" class="form-control" value="{{ old('aws_bucket', $awsBucket) }}" placeholder="Bucket name">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-md-4 col-form-label" for="aws-url">AWS_URL</label>
                                <div class="col-md-8">
                                    <input id="aws-url" type="text" name="aws_url" class="form-control" value="{{ old('aws_url', $awsUrl) }}" placeholder="Optional custom URL / CDN">
                                </div>
                            </div>
                        </div>

                        <div class="text-right">
                            <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1"><i class="fas fa-save"></i> Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xxl-6 mb-3">
            <div class="card h-100">
                <div class="card-header"><h2 class="h6 mb-0">Cache &amp; Session Driver</h2></div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.file-system-cache.cache.update') }}" method="POST" autocomplete="off">
                        @csrf
                        <div class="form-group row align-items-center">
                            <label class="col-md-4 col-form-label" for="cache-driver">CACHE_DRIVER</label>
                            <div class="col-md-8">
                                <select id="cache-driver" name="cache_driver" class="form-control form-select">
                                    <option value="file" @selected($cacheDriver === 'file')>File</option>
                                    <option value="database" @selected($cacheDriver === 'database')>Database</option>
                                    <option value="redis" @selected($cacheDriver === 'redis')>Redis</option>
                                    <option value="array" @selected($cacheDriver === 'array')>Array (no cache)</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row align-items-center">
                            <label class="col-md-4 col-form-label" for="session-driver">SESSION_DRIVER</label>
                            <div class="col-md-8">
                                <select id="session-driver" name="session_driver" class="form-control form-select">
                                    <option value="file" @selected($sessionDriver === 'file')>File</option>
                                    <option value="cookie" @selected($sessionDriver === 'cookie')>Cookie</option>
                                    <option value="database" @selected($sessionDriver === 'database')>Database</option>
                                    <option value="redis" @selected($sessionDriver === 'redis')>Redis</option>
                                    <option value="array" @selected($sessionDriver === 'array')>Array</option>
                                </select>
                            </div>
                        </div>

                        <hr>
                        <h3 class="h6 text-muted mb-3">Redis Configuration <small>(required if Redis is selected)</small></h3>
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label" for="redis-host">REDIS_HOST</label>
                            <div class="col-md-8">
                                <input id="redis-host" type="text" name="redis_host" class="form-control" value="{{ old('redis_host', $redisHost) }}" placeholder="127.0.0.1">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label" for="redis-password">REDIS_PASSWORD</label>
                            <div class="col-md-8">
                                <input id="redis-password" type="password" name="redis_password" class="form-control" value="" autocomplete="new-password" placeholder="{{ $redisPasswordConfigured ? 'Saved securely; leave blank to keep current' : 'Redis password (optional)' }}">
                                @if ($redisPasswordConfigured) <small class="text-muted">A password is saved. Leave blank to keep the current value.</small> @endif
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-md-4 col-form-label" for="redis-port">REDIS_PORT</label>
                            <div class="col-md-8">
                                <input id="redis-port" type="number" min="1" max="65535" name="redis_port" class="form-control" value="{{ old('redis_port', $redisPort) }}" placeholder="6379">
                            </div>
                        </div>

                        <div class="text-right">
                            <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1"><i class="fas fa-save"></i> Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-info mb-0">
        <strong>Note:</strong> Driver changes are stored in application settings and applied at runtime when supported. For cache/session/filesystem driver switches to take full effect, you may need to run <code>php artisan config:clear</code> / <code>cache:clear</code> and restart any queue workers or long-running processes.
    </div>
</div>

<script>
(function () {
    const driver = document.getElementById('filesystem-driver');
    const s3Fields = document.getElementById('s3-fields');
    function toggleS3() { s3Fields.style.display = driver.value === 's3' ? '' : 'none'; }
    if (driver && s3Fields) { driver.addEventListener('change', toggleS3); toggleS3(); }
})();
</script>
@endsection
