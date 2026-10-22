@extends('admin.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-logs-trash-page', 'pageClass' => 'admin-logs-trash-page'])
@section('page-title', 'Log Trash')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Log Trash - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2 p-2">
        <div class="d-flex align-items-center gap-2">
            <!-- <a href="{{ route('admin.logs.index') }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-arrow-left me-1"></i> Back to Logs</a> -->
            <h5 class="mb-0 fw-bold p-2"><i class="fas fa-trash-alt me-2"></i>Log Trash</h5>
        </div>
        @if(count($trashedFiles))
            <form action="{{ route('admin.logs.empty-trash') }}" method="POST" class="d-inline"
                  onsubmit="return confirm('Empty the log trash permanently? All trashed log files will be deleted. This cannot be undone.')">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-broom me-1"></i> Empty Trash</button>
            </form>
        @endif
    </div>
    <div class="card-body p-0">
        @if(count($trashedFiles))
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>File</th>
                            <th>Size</th>
                            <th>File Modified</th>
                            <th class="pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($trashedFiles as $index => $file)
                        <tr>
                            <td class="ps-3 text-muted">{{ $index + 1 }}</td>
                            <td><code>{{ $file }}</code></td>
                            <td class="small text-muted">@php $b = filesize(storage_path('logs/site-changes-trash/' . $file)); if ($b >= 1048576) { echo number_format($b/1048576, 1) . ' MB'; } elseif ($b >= 1024) { echo number_format($b/1024, 1) . ' KB'; } else { echo $b . ' B'; } @endphp</td>
                            <td class="small text-muted">{{ \Illuminate\Support\Carbon::createFromTimestamp(filemtime(storage_path('logs/site-changes-trash/' . $file)))->format('d M Y H:i') }}</td>
                            <td class="pe-3 table-action-col">
                                <div class="d-flex gap-1 action-buttons">
                                    <form action="{{ route('admin.logs.restore', $file) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-primary steve-btn" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Restore log file">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.logs.force-delete', $file) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Permanently delete this trashed log file? This cannot be undone.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger steve-btn" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete permanently">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-info mb-0">No log files in trash. Cleared log files are kept here for 15 days before auto-delete.</div>
        @endif
    </div>
</div>

@endsection