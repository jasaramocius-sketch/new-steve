@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-file-revisions-page', 'pageClass' => 'admin-file-revisions-page'])
@section('page-title', 'File Revisions')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'File Revisions - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap flex-md-nowrap">
    <!-- <h4 class="fw-bold mb-0">File Revisions</h4> -->
    <div class="d-flex align-items-center gap-1">
        <span class="text-muted small me-2">Next scan: via cron</span>
        <a href="{{ route('admin.file-revisions.index') }}" class="btn btn-outline-secondary steve-btn" title="Refresh" data-bs-toggle="tooltip" data-bs-placement="top"><i class="fas fa-sync"></i></a>
    </div>
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div class="d-flex align-items-center gap-2">
        @if(!empty($trashed))
            <!-- <a href="{{ route('admin.file-revisions.index') }}" class="btn btn-outline-primary"><i class="fas fa-arrow-left me-1"></i> Back</a> -->
            <button type="button" class="btn btn-primary" form="bulk-trash-form"
                    data-bulk-action="{{ route('admin.file-revisions.bulk-restore') }}"
                    onclick="bulkTrashRun(this)" disabled id="bulk-restore-btn">
                <i class="fas fa-undo me-1"></i> Restore Selected (<span class="bulk-count">0</span>)
            </button>
            <button type="button" class="btn btn-danger" form="bulk-trash-form"
                    data-bulk-action="{{ route('admin.file-revisions.bulk-force-delete') }}"
                    onclick="if(confirm('Permanently delete the selected file revisions (and their backup files) from trash? This cannot be undone.')) bulkTrashRun(this)" disabled id="bulk-force-btn">
                <i class="fas fa-trash me-1"></i> Delete Selected (<span class="bulk-count">0</span>)
            </button>
            <form action="{{ route('admin.file-revisions.empty-trash') }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Empty the trash permanently? All trashed file revisions and their backup files will be deleted. This cannot be undone.')">
                @csrf
                <button type="submit" class="btn btn-outline-danger"><i class="fas fa-broom me-1"></i> Empty Trash</button>
            </form>
        @else
            <a href="{{ route('admin.file-revisions.index', ['trashed' => 1]) }}" class="btn btn-outline-secondary"><i class="fas fa-trash-alt me-1"></i> Trash</a>
        @endif
    </div>
</div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#maintenancePanel" style="cursor:pointer;">
        <span class="small fw-semibold"><i class="fas fa-broom me-1"></i> Maintenance</span>
        <i class="fas fa-chevron-down small text-muted"></i>
    </div>
    <div id="maintenancePanel" class="collapse">
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card border bg-light h-100">
                        <div class="card-body">
                            <h6 class="card-title fw-semibold small"><i class="fas fa-compress-alt me-1"></i> Per-File Limit</h6>
                            <p class="text-muted small mb-2">Keep only the last N revisions per file. Older diffs are truncated to 200 chars. Rows and backup files are preserved.</p>
                            <form action="{{ route('admin.file-revisions.truncate-per-file') }}" method="POST" class="d-flex gap-2 align-items-end"
                                  onsubmit="return confirm('This will truncate diff text beyond the per-file limit. Revisions and backups are preserved. Continue?')">
                                @csrf
                                <div>
                                    <label class="form-label small">Keep last (per file)</label>
                                    <input type="number" name="keep_per_file" value="50" min="5" max="500" class="form-control form-control-sm" style="width:120px;">
                                </div>
                                <button type="submit" class="btn btn-warning btn-sm"><i class="fas fa-filter me-1"></i> Apply Limit</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border bg-light h-100">
                        <div class="card-body">
                            <h6 class="card-title fw-semibold small"><i class="fas fa-clock me-1"></i> Age-Based Truncate</h6>
                            <p class="text-muted small mb-2">Replace large diff text on rows older than N days with a 200-char summary. Revisions and backup files are preserved.</p>
                            <form action="{{ route('admin.file-revisions.truncate-diffs') }}" method="POST" class="d-flex gap-2 align-items-end"
                                  onsubmit="return confirm('This will truncate diff text on rows older than N days. Revisions and backups are preserved. Continue?')">
                                @csrf
                                <div>
                                    <label class="form-label small">Older than (days)</label>
                                    <input type="number" name="older_than_days" value="90" min="1" max="3650" class="form-control form-control-sm" style="width:120px;">
                                </div>
                                <button type="submit" class="btn btn-warning btn-sm"><i class="fas fa-compress-alt me-1"></i> Truncate Diffs</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="bulk-trash-form" method="POST" action="{{ route('admin.file-revisions.bulk-delete') }}">
    @csrf
    <div id="bulk-delete-inputs" class="d-none"></div>
</form>



<div class="card border-0 shadow-sm file-revisions-table">
    <div class="card-body p-0">
        <div class="d-flex justify-content-between align-items-center p-2 flex-wrap flex-md-nowrap flex-wrap flex-md-nowrap">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                @if(empty($trashed))
                <button type="submit" form="bulk-trash-form" class="btn btn-danger d-none" id="bulk-delete-btn" onclick="return confirm('Move the selected file revisions to trash? They will auto-delete after 15 days.')">
                    <i class="fas fa-trash me-1"></i> Delete Selected (<span class="bulk-count" id="bulk-delete-count">0</span>)
                </button>
                @endif
                <span class="text-muted small">Show</span>
                <select class="form-select w-auto" onchange="window.location.href=this.value">
                    @foreach([10, 20, 50, 100] as $n)
                        <option value="{{ request()->fullUrlWithQuery(['per_page' => $n, 'page' => null]) }}" {{ (int)request('per_page', 20) === $n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                </select>
                <span class="text-muted small">per page</span>
            </div>
            @include('admin.partials.date-range-filter', ['filterRoute' => 'admin.file-revisions.index'])
            <div class="text-muted small">
                Showing {{ $fileRevisions->firstItem() }}-{{ $fileRevisions->lastItem() }} of {{ $fileRevisions->total() }}
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">
                            <input type="checkbox" class="form-check-input m-0 bulk-select-all" aria-label="Select all on this page">
                        </th>
                        <th>No.</th>
                        <th><a href="{{ sortUrl('file_path', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">File {!! sortIndicator('file_path', $sortBy, $sortDir) !!}</a></th>
                        <th><a href="{{ sortUrl('event', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Event {!! sortIndicator('event', $sortBy, $sortDir) !!}</a></th>
                        <th>User</th>
                        <th>Actions</th>
                        <th class="pe-3"><a href="{{ sortUrl('created_at', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Date {!! sortIndicator('created_at', $sortBy, $sortDir) !!}</a></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fileRevisions as $rev)
                    <tr>
                        <td class="ps-3">
                            <input type="checkbox" class="form-check-input m-0 bulk-select-row" value="{{ $rev->id }}" aria-label="Select revision {{ $rev->id }}">
                        </td>
                        <td>{{ $fileRevisions->firstItem() + $loop->index }}</td>
                        <td class="file-path-cell">
                            <code>{{ $rev->file_path }}</code>
                        </td>
                        <td>
                            @if($rev->event === 'created')
                                <span class="badge bg-light text-success border border-success-subtle">Created</span>
                            @elseif($rev->event === 'updated')
                                <span class="badge bg-light text-primary border border-primary-subtle">Updated</span>
                            @elseif($rev->event === 'deleted')
                                <span class="badge bg-light text-danger border border-danger-subtle">Deleted</span>
                            @else
                                <span class="badge bg-light text-secondary border border-secondary-subtle">{{ $rev->event }}</span>
                            @endif
                        </td>
                        <td>
                            @if($rev->user)
                                {{ $rev->user->name }}
                            @else
                                <span class="text-muted small">System</span>
                            @endif
                        </td>
                        <td class="table-action-col">
                            <div class="d-flex gap-1 action-buttons revision-action-buttons">
                                <a href="{{ route('admin.file-revisions.diff', $rev->id) }}" class="btn btn-outline-info steve-btn" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="View Diff">
                                    <i class="fas fa-code-branch"></i>
                                </a>
                                @if($rev->backup_path)
                                    <a href="{{ route('admin.file-revisions.download', $rev->id) }}" class="btn btn-outline-secondary steve-btn" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Download backup">
                                        <i class="fas fa-download"></i>
                                    </a>
                                @endif
                                @if(!empty($trashed))
                                    <form action="{{ route('admin.file-revisions.restore', $rev->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-primary steve-btn" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Restore revision">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.file-revisions.force-delete', $rev->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Permanently delete this file revision{{ $rev->backup_path ? ' and its backup file' : '' }} from trash? This cannot be undone.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger steve-btn" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete permanently">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @else
                                <form action="{{ route('admin.file-revisions.destroy', $rev->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this file revision? It will move to trash (backup preserved) and auto-delete after 15 days.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger steve-btn" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete revision">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                        <td class="pe-3 text-nowrap small text-muted">
                            {{ $rev->created_at->format('d M Y H:i') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            @if(!empty($trashed))
                                No file revisions in trash.
                            @else
                                No file revisions recorded yet. Run <code>php artisan file:audit --watch</code> or set up a cron job.
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($fileRevisions->hasPages())
            <div class="d-flex justify-content-center py-3">{{ $fileRevisions->links('vendor.pagination.gs-pagination') }}</div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    function bulkTrashRun(btn) {
        var form = document.getElementById('bulk-trash-form');
        form.action = btn.getAttribute('data-bulk-action');
        form.submit();
    }
    (function () {
        var rowChecks = document.querySelectorAll('.bulk-select-row');
        var selectAll = document.querySelector('.bulk-select-all');
        var inputsContainer = document.getElementById('bulk-delete-inputs');
        var countEls = document.querySelectorAll('.bulk-count');

        function refresh() {
            var selected = Array.prototype.filter.call(rowChecks, function (c) { return c.checked; });
            countEls.forEach(function (el) { el.textContent = selected.length; });
            inputsContainer.innerHTML = '';
            selected.forEach(function (c) {
                var inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'ids[]';
                inp.value = c.value;
                inputsContainer.appendChild(inp);
            });
            ['bulk-delete-btn', 'bulk-restore-btn', 'bulk-force-btn'].forEach(function (id) {
                var el = document.getElementById(id);
                if (el && !el.disabled) el.classList.toggle('d-none', selected.length === 0);
            });
            if (selectAll) {
                selectAll.checked = rowChecks.length > 0 && selected.length === rowChecks.length;
            }
        }

        rowChecks.forEach(function (c) { c.addEventListener('change', refresh); });
        if (selectAll) {
            selectAll.addEventListener('change', function () {
                rowChecks.forEach(function (c) { c.checked = selectAll.checked; });
                refresh();
            });
        }
        refresh();
    })();
</script>
@endpush

@endsection
