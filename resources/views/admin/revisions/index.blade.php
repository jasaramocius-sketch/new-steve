@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-revisions-index-page', 'pageClass' => 'admin-revisions-index-page'])
@section('page-title', 'Revisions')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Revisions - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')

<!-- <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap flex-md-nowrap">
    <h4 class="fw-bold mb-0">Revisions History</h4>
</div> -->

<form id="bulk-trash-form" method="POST" action="{{ route('admin.revisions.bulk-delete') }}">
    @csrf
    <div id="bulk-delete-inputs" class="d-none"></div>
</form>

<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div class="d-flex align-items-center gap-2">
        @if(!empty($trashed))
            <!-- <a href="{{ route('admin.revisions.index') }}" class="btn btn-outline-primary"><i class="fas fa-arrow-left me-1"></i> Back</a> -->
            <button type="button" class="btn btn-primary steve-btn" form="bulk-trash-form"
                    data-bulk-action="{{ route('admin.revisions.bulk-restore') }}"
                    onclick="bulkTrashRun(this)" disabled id="bulk-restore-btn">
                <i class="fas fa-undo me-1"></i> Restore Selected (<span class="bulk-count">0</span>)
            </button>
            <button type="button" class="btn btn-danger steve-btn" form="bulk-trash-form"
                    data-bulk-action="{{ route('admin.revisions.bulk-force-delete') }}"
                    onclick="if(confirm('Permanently delete the selected revisions from trash? This cannot be undone.')) bulkTrashRun(this)" disabled id="bulk-force-btn">
                <i class="fas fa-trash me-1"></i> Delete Selected (<span class="bulk-count">0</span>)
            </button>
            <form action="{{ route('admin.revisions.empty-trash') }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Empty the trash permanently? All trashed revisions will be deleted. This cannot be undone.')">
                @csrf
                <button type="submit" class="btn btn-outline-danger steve-btn"><i class="fas fa-broom me-1"></i> Empty Trash</button>
            </form>
        @else
            <a href="{{ route('admin.revisions.index', ['trashed' => 1]) }}" class="btn btn-outline-secondary steve-btn"><i class="fas fa-trash-alt me-1"></i> Trash</a>
        @endif
    </div>
</div>

<div class="card border-0 shadow-sm revisions-page-table">
    <div class="card-body p-0">
        <div class="d-flex justify-content-between align-items-center p-2 flex-wrap flex-md-nowrap flex-wrap flex-md-nowrap">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                @if(empty($trashed))
                <button type="submit" form="bulk-trash-form" class="btn btn-danger d-none" id="bulk-delete-btn" onclick="return confirm('Move the selected revisions to trash? They will auto-delete after 15 days.')">
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
            @include('admin.partials.date-range-filter', ['filterRoute' => 'admin.revisions.index'])
            @if($filterModelType || $filterModelId)
                <a href="{{ route('admin.revisions.index') }}" class="btn btn-sm btn-outline-danger" title="Clear page filter">
                    <i class="fas fa-times me-1"></i> Filtered
                    @if($filterModelType)
                        {{ class_basename($filterModelType) }}
                    @endif
                    @if($filterModelId)
                        #{{ $filterModelId }}
                    @endif
                </a>
            @endif
            <div class="text-muted small">
                Showing {{ $revisions->firstItem() }}-{{ $revisions->lastItem() }} of {{ $revisions->total() }}
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
                        <th>User</th>
                        <th><a href="{{ sortUrl('model_type', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Model {!! sortIndicator('model_type', $sortBy, $sortDir) !!}</a></th>
                        <th>Record ID</th>
                        <th><a href="{{ sortUrl('action', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Action {!! sortIndicator('action', $sortBy, $sortDir) !!}</a></th>
                        <th>URL</th>
                        <th>Actions</th>
                        <th class="pe-3"><a href="{{ sortUrl('created_at', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Date {!! sortIndicator('created_at', $sortBy, $sortDir) !!}</a></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($revisions as $rev)
                    <tr>
                        <td class="ps-3">
                            <input type="checkbox" class="form-check-input m-0 bulk-select-row" value="{{ $rev->id }}" aria-label="Select revision {{ $rev->id }}">
                        </td>
                        <td>{{ $revisions->firstItem() + $loop->index }}</td>
                        <td>
                            @if($rev->user)
                                {{ $rev->user->name }}
                                <div class="text-muted small">{{ $rev->user->email }}</div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $short = class_basename($rev->model_type);
                            @endphp
                            {{ $short }}
                            @php $relTitle = $rev->relatedTitle(); @endphp
                            @if($relTitle)
                                <div class="small text-muted">{{ $relTitle }}</div>
                            @endif
                        </td>
                        <td>#{{ $rev->model_id }}</td>
                        <td>
                            @if($rev->action === 'created')
                                <span class="badge bg-light text-success border border-success-subtle">Created</span>
                            @elseif($rev->action === 'updated')
                                <span class="badge bg-light text-primary border border-primary-subtle">Updated</span>
                            @elseif($rev->action === 'deleted')
                                <span class="badge bg-light text-danger border border-danger-subtle">Deleted</span>
                            @else
                                <span class="badge bg-light text-secondary border border-secondary-subtle">{{ $rev->action }}</span>
                            @endif
                        </td>
                        <td class="admin-max-width-250">
                            @if($rev->url)
                                <span class="small text-muted" title="{{ $rev->url }}">{{ \Illuminate\Support\Str::limit($rev->url, 40) }}</span>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="table-action-col">
                            <div class="action-buttons revision-action-buttons">
                            <a href="{{ route('admin.revisions.detail', $rev->id) }}" class="btn btn-outline-info" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="View Details">
                                <i class="fas fa-code-branch"></i>
                            </a>
                            @if(!empty($trashed))
                                <form action="{{ route('admin.revisions.restore', $rev->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-primary" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Restore revision" title="Restore revision">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.revisions.force-delete', $rev->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Permanently delete this revision from trash? This cannot be undone.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete permanently" title="Permanently delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('admin.revisions.destroy', $rev->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this revision? It will move to trash and auto-delete after 15 days.')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete revision">
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
                        <td colspan="9" class="text-center py-4 text-muted">
                            @if(!empty($trashed)) No revisions in trash. @else No revisions found. @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($revisions->hasPages())
            <div class="d-flex justify-content-center py-3">{{ $revisions->links('vendor.pagination.gs-pagination') }}</div>
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
