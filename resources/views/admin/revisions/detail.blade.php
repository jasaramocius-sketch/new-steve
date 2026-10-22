@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-revisions-detail-page', 'pageClass' => 'admin-revisions-detail-page'])
@section('page-title', 'Revision #' . $rev->id)
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Revision Detail - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')

<!--
    .rev-header { background: #f8f9fa; border-bottom: 1px solid #dee2e6; }
    .diff-table { font-family: 'Consolas', 'Monaco', 'Courier New', monospace; font-size: 13px; width: 100%; border-collapse: collapse; }
    .diff-table th { text-align: left; padding: 10px 12px; border-bottom: 2px solid #dee2e6; font-weight: 600; }
    .diff-table td { padding: 8px 12px; border-bottom: 1px solid #f0f0f0; vertical-align: top; }
    .diff-table tr:last-child td { border-bottom: none; }
    .field-name { font-weight: 600; color: #24292e; white-space: nowrap; width: 150px; }
    .val-old { background: #ffeef0; color: #cb2431; }
    .val-new { background: #e6ffed; color: #22863a; }
    .val-cell { font-family: 'Consolas', 'Monaco', 'Courier New', monospace; font-size: 13px; white-space: pre-wrap; word-break: break-all; max-width: 400px; max-height: 200px; overflow-y: auto; }
    .val-same { color: #6a737d; }
    .meta-icon { width: 20px; text-align: center; display: inline-block; }
    .empty-diff { color: #999; padding: 20px; text-align: center; }
-->
<div class="revision-page">
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">
        Revision Detail
    </h4>
    <!-- <a href="{{ route('admin.revisions.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-list"></i> Back to List</a> -->
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <div class="row g-3">
            <div class="col-md-3">
                <strong>Model:</strong>
                <span class="ms-1">{{ class_basename($rev->model_type) }}</span>
            </div>
            <div class="col-md-2">
                <strong>Record #:</strong>
                <span class="ms-1">{{ $rev->model_id }}</span>
            </div>
            <div class="col-md-2">
                <strong>Action:</strong>
                @if($rev->action === 'created')
                    <span class="badge bg-light text-success border border-success-subtle ms-1">Created</span>
                @elseif($rev->action === 'updated')
                    <span class="badge bg-light text-primary border border-primary-subtle ms-1">Updated</span>
                @elseif($rev->action === 'deleted')
                    <span class="badge bg-light text-danger border border-danger-subtle ms-1">Deleted</span>
                @else
                    <span class="badge bg-light text-secondary border border-secondary-subtle ms-1">{{ $rev->action }}</span>
                @endif
            </div>
            <div class="col-md-3">
                <strong>User:</strong>
                @if($rev->user)
                    <span class="ms-1">{{ $rev->user->name }}</span>
                    <span class="text-muted small">({{ $rev->user->email }})</span>
                @else
                    <span class="text-muted ms-1">—</span>
                @endif
            </div>
            <div class="col-md-2">
                <strong>Date:</strong>
                <span class="ms-1">{{ $rev->created_at->format('d M Y H:i:s') }}</span>
            </div>
            @if($rev->url)
            <div class="col-md-12 mt-2">
                <strong>URL:</strong>
                <span class="ms-1 small text-break text-muted">{{ $rev->url }}</span>
            </div>
            @endif
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header rev-header py-2">
        <i class="fas fa-exchange-alt me-1"></i> Field Changes
    </div>
    <div class="card-body p-0">
        @if($rev->action === 'created' && $rev->new_values)
            <div class="table-responsive">
            <table class="diff-table">
                <thead><tr><th class="admin-table-field">Field</th><th>Value (New)</th></tr></thead>
                <tbody>
                    @foreach($rev->new_values as $field => $val)
                        @php $valStr = is_scalar($val) ? (string)$val : (is_null($val) ? 'null' : json_encode($val, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); @endphp
                        <tr>
                            <td class="field-name">{{ $field }}</td>
                            <td class="val-cell val-new">{{ $valStr }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @elseif($rev->action === 'deleted' && $rev->old_values)
            <div class="table-responsive">
            <table class="diff-table">
                <thead><tr><th class="admin-table-field">Field</th><th>Value (Old)</th></tr></thead>
                <tbody>
                    @foreach($rev->old_values as $field => $val)
                        @php $valStr = is_scalar($val) ? (string)$val : (is_null($val) ? 'null' : json_encode($val, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); @endphp
                        <tr>
                            <td class="field-name">{{ $field }}</td>
                            <td class="val-cell val-old">{{ $valStr }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        @elseif($rev->action === 'updated' && $rev->old_values && $rev->new_values)
            <div class="table-responsive">
            <table class="diff-table">
                <thead><tr><th class="admin-table-field">Field</th><th>Old Value</th><th>New Value</th></tr></thead>
                <tbody>
                    @php $hasChanges = false; @endphp
                    @foreach($rev->new_values as $field => $newVal)
                        @if(array_key_exists($field, $rev->old_values ?? []))
                            @php
                                $oldVal = $rev->old_values[$field] ?? '';
                                $oldStr = is_scalar($oldVal) ? (string)$oldVal : (is_null($oldVal) ? 'null' : json_encode($oldVal, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                                $newStr = is_scalar($newVal) ? (string)$newVal : (is_null($newVal) ? 'null' : json_encode($newVal, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                                $changed = $oldStr !== $newStr;
                                if ($changed) $hasChanges = true;
                            @endphp
                            @if($changed)
                                <tr>
                                    <td class="field-name">{{ $field }}</td>
                                    <td class="val-cell val-old">{{ $oldStr }}</td>
                                    <td class="val-cell val-new">{{ $newStr }}</td>
                                </tr>
                            @else
                                <tr>
                                    <td class="field-name">{{ $field }}</td>
                                    <td class="val-cell val-same" colspan="2">{{ $oldStr }}</td>
                                </tr>
                            @endif
                        @endif
                    @endforeach
                    @if(!$hasChanges)
                        <tr><td colspan="3" class="empty-diff">No field-level changes recorded.</td></tr>
                    @endif
                </tbody>
            </table>
            </div>
        @else
            <div class="empty-diff">No change data available for this revision.</div>
        @endif
    </div>
</div>
</div>
@endsection
