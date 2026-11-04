@extends('admin.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-newsletter-subscribers-index-page', 'pageClass' => 'admin-newsletter-subscribers-index-page'])
@section('page-title', 'Newsletter Subscribers')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Newsletter Subscribers - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection

@section('content')
 <div class="d-flex justify-content-between align-items-center mb-3">
    <div class=""></div>
    <a href="{{ route('admin.subscribers.export-csv') }}" class="btn btn-primary steve-btn">
        <i class="fas fa-file-csv me-1"></i> Export CSV
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="d-flex justify-content-between align-items-center p-2 flex-wrap flex-md-nowrap">
            <form method="GET" action="{{ route('admin.subscribers.index') }}" class="d-flex align-items-center gap-2">
                <input type="text" name="email" value="{{ request('email') }}" class="form-control" placeholder="Search by email..." style="min-width:220px;">
                <button class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
            </form>
            <div class="text-muted small">
                Showing {{ $subscribers->firstItem() }}-{{ $subscribers->lastItem() }} of {{ $subscribers->total() }}
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">#</th>
                        <th>Email</th>
                        <th>Subscribed On</th>
                        <th class="">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscribers as $subscriber)
                    <tr>
                        <td class="ps-3">{{ $subscriber->id }}</td>
                        <td>{{ $subscriber->email }}</td>
                        <td>{{ $subscriber->created_at?->format('M d, Y h:i A') ?? '—' }}</td>
                        <td>
                            <form action="{{ route('admin.subscribers.destroy', $subscriber->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this subscriber?')">
                                @csrf @method('DELETE')
                                <button class="action-btn btn-cancel" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="100%" class="text-center py-4 text-muted">No subscribers found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($subscribers->hasPages())
            <div class="p-2 border-top">
                {{ $subscribers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection