@extends('admin.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-subscribers-index-page', 'pageClass' => 'admin-subscribers-index-page'])
@section('page-title', 'Subscribers')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Subscribers - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection

@section('content')
 <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Newsletter Subscribers</h4>
    <a href="{{ route('admin.subscribers.export-csv') }}" class="btn btn-primary steve-btn">
        <i class="fas fa-file-csv me-1"></i> Export CSV
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="d-flex justify-content-between align-items-center p-2 flex-wrap flex-md-nowrap">
            <form method="GET" action="{{ route('admin.subscribers.index') }}" class="d-flex align-items-center gap-2">
                <input type="text" name="email" value="{{ request('email') }}" class="form-control form-control-sm" placeholder="Search by email..." style="min-width:220px;">
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
                            <form action="{{ route('admin.subscribers.destroy', $subscriber->id) }}" method="POST" onsubmit="return confirm('Remove this subscriber?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
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