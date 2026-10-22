@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-file-diff-page', 'pageClass' => 'admin-file-diff-page'])
@section('page-title', 'File Diff #' . $rev->id)
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'File Diff - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')

<!-- <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">
        File Diff
    </h4>
    <a href="{{ route('admin.file-revisions.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-list"></i> Back to List</a>
</div> -->

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <div class="row g-3">
            <div class="col-md-6">
                <strong>File:</strong>
                <code class="ms-1">{{ $rev->file_path }}</code>
            </div>
            <div class="col-md-3">
                <strong>Event:</strong>
                @if($rev->event === 'created')
                    <span class="badge bg-light text-success border border-success-subtle ms-1">Created</span>
                @elseif($rev->event === 'updated')
                    <span class="badge bg-light text-primary border border-primary-subtle ms-1">Updated</span>
                @elseif($rev->event === 'deleted')
                    <span class="badge bg-light text-danger border border-danger-subtle ms-1">Deleted</span>
                @else
                    <span class="badge bg-light text-secondary border border-secondary-subtle ms-1">{{ $rev->event }}</span>
                @endif
            </div>
            <div class="col-md-3">
                <strong>Date:</strong>
                <span class="ms-1">{{ $rev->created_at->format('d M Y H:i:s') }}</span>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header diff-header py-2 d-flex justify-content-between align-items-center">
        <span>
            <i class="fas fa-code-branch me-1"></i> Changes
            @if($rev->user)
                <span class="text-muted ms-2 small">by {{ $rev->user->name }}</span>
            @endif
        </span>
        @if($rev->backup_path)
            <a href="{{ route('admin.file-revisions.download', $rev->id) }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-download"></i> Download Backup
            </a>
        @endif
    </div>
    <div class="diff-content">
        @if($rev->diff)
            @php
                $lines = explode("\n", $rev->diff);
            @endphp
            <pre>@foreach($lines as $line)
@php
    $cls = '';
    if (str_starts_with($line, 'diff --git') || str_starts_with($line, 'index ')) continue;
    if (str_starts_with($line, '---') || str_starts_with($line, '+++')) $cls = 'diff-line-info';
    elseif (str_starts_with($line, '@@')) $cls = 'diff-line-hunk';
    elseif (str_starts_with($line, '+')) $cls = 'diff-line-add';
    elseif (str_starts_with($line, '-')) $cls = 'diff-line-del';
@endphp<span class="{{ $cls }}">{{ $line }}</span>
@endforeach</pre>
        @else
            <div class="diff-empty">
                @if($rev->event === 'created')
                    <i class="fas fa-plus-circle text-success me-2"></i>New file created — no previous version to compare.
                @elseif($rev->event === 'deleted')
                    <i class="fas fa-minus-circle text-danger me-2"></i>File deleted — <a href="{{ route('admin.file-revisions.download', $rev->id) }}">download last backup</a> to view content.
                @else
                    <i class="fas fa-minus-circle text-muted me-2"></i>No diff available.
                @endif
            </div>
        @endif
    </div>
</div>

@endsection
