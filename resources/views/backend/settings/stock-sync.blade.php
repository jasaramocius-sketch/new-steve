@extends('backend.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-settings-stock-sync-page', 'pageClass' => 'admin-settings-stock-sync-page'])
@section('page-title', 'Stock Sync')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Stock Sync - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col">
            @if (! $configured)
                <div class="alert alert-warning">
                    <strong>Integration required.</strong>
                    No supplier stock feed is configured yet. Stock is currently updated only by CSV import, manual admin edits and order deductions.
                    Add a feed URL and enable sync below; nothing is fetched or overwritten until then.
                </div>
            @endif

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-rotate me-2"></i>Supplier Stock Feed</h5>
                    <span class="badge {{ $configured ? 'bg-success' : 'bg-secondary' }}">
                        {{ $configured ? 'Configured' : 'Integration required' }}
                    </span>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        The sync sends one GET request and updates only products whose key appears in the feed. Products missing from the feed are never changed.
                    </p>

                    <form action="{{ route('admin.settings.stock-sync.update') }}" method="POST" autocomplete="off">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label for="stock_feed_url" class="form-label">Feed URL</label>
                                <input type="url" id="stock_feed_url" name="stock_feed_url"
                                       class="form-control @error('stock_feed_url') is-invalid @enderror"
                                       value="{{ old('stock_feed_url', $settings['stock_feed_url']) }}"
                                       placeholder="https://supplier.example.com/stock.json">
                                @error('stock_feed_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted">JSON array or CSV with a header row. A Bearer token is sent when one is saved.</small>
                            </div>

                            <div class="col-md-4">
                                <label for="stock_feed_token" class="form-label">API token (optional)</label>
                                <div class="input-group">
                                    <input id="stock_feed_token" type="password" name="stock_feed_token"
                                           class="form-control @error('stock_feed_token') is-invalid @enderror"
                                           value="{{ old('stock_feed_token', $settings['stock_feed_token']) }}"
                                           autocomplete="new-password" data-lpignore="true" data-1p-ignore
                                           placeholder="Leave blank to keep current"
                                           @if ($settings['stock_feed_token'] !== '') readonly @endif>
                                    @if ($settings['stock_feed_token'] !== '')
                                        <button class="action-btn btn-view toggle-secret-visibility" type="button"
                                                data-target="stock_feed_token" aria-label="Show token" title="Show token">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </button>
                                        <button class="action-btn btn-edit toggle-secret-edit" type="button"
                                                data-target="stock_feed_token" aria-label="Change token" title="Change token">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                        </button>
                                    @endif
                                </div>
                                @error('stock_feed_token') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="stock_feed_format" class="form-label">Feed format</label>
                                <select id="stock_feed_format" name="stock_feed_format" class="form-select">
                                    <option value="json" @selected(old('stock_feed_format', $settings['stock_feed_format']) === 'json')>JSON</option>
                                    <option value="csv" @selected(old('stock_feed_format', $settings['stock_feed_format']) === 'csv')>CSV</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="stock_feed_key_field" class="form-label">Product key in feed</label>
                                <select id="stock_feed_key_field" name="stock_feed_key_field" class="form-select">
                                    <option value="part_number" @selected(old('stock_feed_key_field', $settings['stock_feed_key_field']) === 'part_number')>Part number</option>
                                    <option value="id" @selected(old('stock_feed_key_field', $settings['stock_feed_key_field']) === 'id')>Product ID</option>
                                    <option value="slug" @selected(old('stock_feed_key_field', $settings['stock_feed_key_field']) === 'slug')>Slug</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="stock_feed_stock_field" class="form-label">Stock field in feed</label>
                                <input type="text" id="stock_feed_stock_field" name="stock_feed_stock_field"
                                       class="form-control @error('stock_feed_stock_field') is-invalid @enderror"
                                       value="{{ old('stock_feed_stock_field', $settings['stock_feed_stock_field']) }}"
                                       placeholder="stock">
                                @error('stock_feed_stock_field') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="stock_feed_items_path" class="form-label">JSON items path</label>
                                <input type="text" id="stock_feed_items_path" name="stock_feed_items_path"
                                       class="form-control @error('stock_feed_items_path') is-invalid @enderror"
                                       value="{{ old('stock_feed_items_path', $settings['stock_feed_items_path']) }}"
                                       placeholder="data.products">
                                @error('stock_feed_items_path') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted">Optional, e.g. <code>data.products</code>.</small>
                            </div>

                            <div class="col-md-3">
                                <label for="low_stock_threshold" class="form-label">Low-stock threshold</label>
                                <input type="number" id="low_stock_threshold" name="low_stock_threshold"
                                       class="form-control @error('low_stock_threshold') is-invalid @enderror"
                                       value="{{ old('low_stock_threshold', $settings['low_stock_threshold']) }}"
                                       min="0" max="100000" step="1">
                                @error('low_stock_threshold') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <small class="text-muted">Used on Stock Management.</small>
                            </div>

                            <div class="col-md-3">
                                <label for="stock_sync_frequency" class="form-label">Schedule</label>
                                <select id="stock_sync_frequency" name="stock_sync_frequency" class="form-select">
                                    <option value="manual" @selected(old('stock_sync_frequency', $settings['stock_sync_frequency']) === 'manual')>Manual only</option>
                                    <option value="hourly" @selected(old('stock_sync_frequency', $settings['stock_sync_frequency']) === 'hourly')>Hourly</option>
                                    <option value="daily" @selected(old('stock_sync_frequency', $settings['stock_sync_frequency']) === 'daily')>Daily at 04:00</option>
                                </select>
                            </div>

                            <div class="col-md-6 d-flex align-items-end">
                                <div class="form-check form-switch mb-1">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                           name="stock_sync_enabled" value="1" id="stock_sync_enabled"
                                           @checked(old('stock_sync_enabled', $settings['stock_sync_enabled']))>
                                    <label class="form-check-label" for="stock_sync_enabled">Enable supplier sync</label>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary steve-btn gap-1 mt-3">
                            <i class="fas fa-save"></i> Save Settings
                        </button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-clock-rotate-left me-2"></i>Last Sync</h5>
                </div>
                <div class="card-body d-flex justify-content-between align-items-center gap-3 flex-wrap">
                    <div>
                        <div class="small text-muted">Last run</div>
                        <div class="fw-semibold">{{ $settings['stock_sync_last_run_at'] ?: 'Never' }}</div>
                        <div class="small text-muted mt-1">
                            {{ $settings['stock_sync_last_result'] ?: 'No sync has run yet.' }}
                        </div>
                        <div class="small text-muted mt-2">
                            CLI preview: <code>php artisan stocks:sync --dry-run</code>
                        </div>
                    </div>
                    @if ($configured)
                        <form action="{{ route('admin.settings.stock-sync.run') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary steve-btn gap-1">
                                <i class="fas fa-rotate"></i> Run sync now
                            </button>
                        </form>
                    @else
                        <button type="button" class="btn btn-outline-secondary steve-btn gap-1" disabled title="Configure the feed first">
                            <i class="fas fa-rotate"></i> Run sync now
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var token = document.getElementById('stock_feed_token');
    if (! token) return;

    var viewButtons = document.querySelectorAll('.toggle-secret-visibility[data-target="stock_feed_token"]');
    var editButtons = document.querySelectorAll('.toggle-secret-edit[data-target="stock_feed_token"]');

    viewButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            var showing = token.type === 'text';
            token.type = showing ? 'password' : 'text';
            var label = showing ? 'Show token' : 'Hide token';
            button.setAttribute('aria-label', label);
            button.setAttribute('title', label);
        });
    });

    editButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            token.removeAttribute('readonly');
            token.value = '';
            token.placeholder = 'Enter a new token';
            token.focus();
        });
    });
})();
</script>
@endsection
