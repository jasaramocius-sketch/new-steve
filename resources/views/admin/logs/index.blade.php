@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-logs-index-page', 'pageClass' => 'admin-logs-index-page'])
@section('page-title', 'Logs')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Logs - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection

@section('content')

@php
    $badgeColors = [
        'change'   => 'bg-warning-subtle text-warning border border-warning-subtle',
        'request'  => 'bg-info-subtle text-info border border-info-subtle',
        'info'     => 'bg-primary-subtle text-primary border border-primary-subtle',
        'auth'     => 'bg-primary-subtle text-primary border border-primary-subtle',
        'created'  => 'bg-success-subtle text-success border border-success-subtle',
        'modified' => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
        'deleted'  => 'bg-danger-subtle text-danger border border-danger-subtle',
        'error'    => 'bg-danger-subtle text-danger border border-danger-subtle',
        'warning'  => 'bg-warning-subtle text-warning border border-warning-subtle',
    ];
@endphp

<div class="card border-0 shadow-sm logs-page-table">
    <div class="card-header bg-white border-bottom d-grid gap-2 justify-content-between align-items-start align-items-xl-center p-2">
        <div class="d-flex flex-row flex-xl-row flex-wrap align-items-center gap-2">
        <h5 class="mb-0 fw-bold p-2"><i class="fas fa-file-alt me-2"></i>Site Logs</h5>
        <div class="text-muted small p-2">
            Showing: {{ $selectedFile }}
            @if($selectedFile)
                <span class="mx-1">|</span> {{ $entries->total() }} entries
            @endif
        </div>
        @if($selectedFile && Auth::check() && in_array(Auth::user()->role, ['master_admin', 'admin']))
            <form method="POST" action="{{ route('admin.logs.clear', $selectedFile) }}" 
                  onsubmit="return confirm('Move the entire {{ basename($selectedFile) }} log file to trash? It will auto-delete after 15 days.')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-outline-danger steve-btn gap-1"><i class="fas fa-trash me-1"></i> Clear Log File</button>
            </form>
            <a href="{{ route('admin.logs.trash') }}" class="btn btn-outline-secondary mt-xl-0"><i class="fas fa-trash-alt me-1"></i> Log Trash</a>
        @endif
        </div>
        <form method="GET" action="{{ route('admin.logs.index') }}" class="d-flex flex-wrap align-items-end gap-2" onsubmit="return stAutoPartsLogsSubmit(this)">
            <div style="flex:1 1 auto; min-width:170px">
                <label class="form-label small mb-1 d-block">Log File</label>
                <select name="file" class="form-select" onchange="this.form.submit()">
                    <option value="">Select log file</option>
                    @foreach($files as $file)
                        <option value="{{ $file }}" {{ $selectedFile === $file ? 'selected' : '' }}>{{ $file }}</option>
                    @endforeach
                </select>
            </div>
            <div style="flex:1 1 auto; min-width:170px">
                <label class="form-label small mb-1 d-block">Type</label>
                <select name="type" class="form-select" onchange="this.form.submit()">
                    <option value="all" {{ ($typeParam ?? 'change') === 'all' ? 'selected' : '' }}>All types</option>
                    @foreach($types as $type)
                        <option value="{{ $type }}" {{ ($typeParam ?? 'change') === $type ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                    @endforeach
                </select>
            </div>
            <div style="flex:1 1 auto; min-width:220px">
                <label class="form-label small mb-1 d-block">Search</label>
                <div class="input-group">
                    <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control" placeholder="Search logs..." style="min-width:130px" id="logs-search-input">
                    @php $hasLogFilter = !empty($search) || (!empty($typeParam) && !in_array($typeParam, ['change', 'all'], true)); @endphp
                    <button type="submit" id="logs-search-toggle" class="btn {{ $hasLogFilter ? 'btn-outline-secondary' : 'btn-primary' }} steve-btn gap-1" data-bs-toggle="tooltip" data-bs-original-title="{{ $hasLogFilter ? 'Clear' : 'Search' }}" aria-label="{{ $hasLogFilter ? 'Clear' : 'Search' }}">
                        <i class="fas {{ $hasLogFilter ? 'fa-times' : 'fa-search' }}"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        @if($selectedFile)

            @if($entries->total())
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">#</th>
                                <th style="width:200px">Timestamp</th>
                                <th style="width:110px">Type</th>
                                <th>Message</th>
                                <th>User</th>
                                <th class="pe-3">Context</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($entries as $entry)
                            <tr>
                                <td class="ps-3 text-muted">{{ $entries->firstItem() + $loop->index }}</td>
                                <td class="text-nowrap small">
                                    @if($entry['timestamp'])
                                        {{ \Illuminate\Support\Carbon::parse($entry['timestamp'])->format('Y-m-d H:i:s') }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $badgeColors[$entry['type']] ?? 'bg-light text-secondary border border-secondary-subtle' }} border">
                                        {{ ucfirst($entry['type']) }}
                                    </span>
                                </td>
                                <td>{{ $entry['message'] }}</td>
                                <td class="text-nowrap small">
                                    @if(!empty($entry['context']['user_id']))
                                        <span class="fw-semibold">{{ $entry['context']['user_name'] ?? 'User #' . $entry['context']['user_id'] }}</span>
                                        @if(!empty($entry['context']['user_email']))
                                            <br><span class="text-muted">{{ $entry['context']['user_email'] }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted">Guest</span>
                                    @endif
                                </td>
                                <td class="pe-3 table-action-col">
                                    @if(!empty($entry['context']))
                                        <div class="action-buttons">
                                            <button type="button" class="action-btn btn-view" data-bs-toggle="collapse" data-bs-target="#log-context-{{ $loop->index }}" title="View Context" aria-expanded="false" aria-controls="log-context-{{ $loop->index }}">
                                                <svg class="eye-icon eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                                <svg class="eye-icon eye-off d-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                            </tr>
                            @if(!empty($entry['context']))
                            <tr class="collapse" id="log-context-{{ $loop->index }}">
                                <td colspan="6" class="ps-3">
                                    <pre class="admin-code-block bg-dark text-light p-3 rounded mb-0"><code>{{ json_encode($entry['context'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</code></pre>
                                </td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($entries->hasPages())
                    <div class="d-flex justify-content-center py-3">{{ $entries->links('vendor.pagination.gs-pagination') }}</div>
                @endif
            @else
                <div class="alert alert-info mb-0">No entries found for this file / search.</div>
            @endif
        @else
            <div class="alert alert-warning mb-0">No log files found yet.</div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    function stAutoPartsLogsSubmit(form) {
        var params = new URLSearchParams();
        var file = form.querySelector('[name="file"]').value;
        var type = form.querySelector('[name="type"]').value;
        var search = form.querySelector('[name="search"]').value;
        if (file) params.set('file', file);
        if (type && type !== 'change' && type !== 'all') params.set('type', type);
        if (search) params.set('search', search);
        var qs = params.toString();
        window.location.href = qs ? (form.getAttribute('action') + '?' + qs) : form.getAttribute('action');
        return false;
    }
    (function () {
        var toggleBtn = document.getElementById('logs-search-toggle');
        if (!toggleBtn) return;
        toggleBtn.addEventListener('click', function () {
            if (toggleBtn.classList.contains('btn-outline-secondary')) {
                var form = toggleBtn.closest('form');
                var search = form.querySelector('[name="search"]');
                var type = form.querySelector('[name="type"]');
                if (search) search.value = '';
                if (type) type.value = 'all';
                toggleBtn.classList.remove('btn-outline-secondary');
                toggleBtn.classList.add('btn-primary');
                toggleBtn.innerHTML = '<i class="fas fa-search"></i>';
                toggleBtn.setAttribute('aria-label', 'Search');
                toggleBtn.setAttribute('data-bs-original-title', 'Search');
                var tip = bootstrap.Tooltip.getInstance(toggleBtn);
                if (tip) tip.setContent({ '.tooltip-inner': 'Search' });
            }
        });
    })();
    document.querySelectorAll('[data-bs-target^="#log-context-"]').forEach(function(btn) {
        var target = document.querySelector(btn.getAttribute('data-bs-target'));
        if (!target) return;
        target.addEventListener('shown.bs.collapse', function() {
            var open = btn.querySelector('.eye-open'), off = btn.querySelector('.eye-off');
            if (open) open.classList.add('d-none');
            if (off) off.classList.remove('d-none');
            btn.setAttribute('aria-expanded', 'true');
            btn.setAttribute('title', 'Hide Context');
        });
        target.addEventListener('hidden.bs.collapse', function() {
            var open = btn.querySelector('.eye-open'), off = btn.querySelector('.eye-off');
            if (open) open.classList.remove('d-none');
            if (off) off.classList.add('d-none');
            btn.setAttribute('aria-expanded', 'false');
            btn.setAttribute('title', 'View Context');
        });
    });
</script>
@endpush

@endsection
