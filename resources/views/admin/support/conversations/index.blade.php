@extends('admin.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-support-conversations-page', 'pageClass' => 'admin-support-conversations-page'])
@section('page-title', 'Product Conversations')
@section('meta_tags')
    @include('partials.meta-tags', ['pageTitle' => 'Product Conversations - Admin Panel', 'robots' => 'noindex, nofollow'])
@endsection
@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="d-flex justify-content-between align-items-center border-bottom flex-wrap gap-2 p-2">
            <div class="d-flex align-items-center flex-wrap gap-2">
                <div class="show-per-page">
                    <select name="per_page" class="form-select w-100" onchange="this.form.submit()">
                    @foreach ([10, 20, 50, 100] as $n)
                        <option value="{{ $n }}" @selected((int) request('per_page', 20) === $n)>{{ $n }} per page</option>
                    @endforeach
                </select>
                </div>
                <form method="GET" action="{{ route('admin.support.conversations.index') }}" class="d-flex flex-wrap gap-2 flex-lg-row">
                <select name="status" class="form-select w-auto" onchange="this.form.submit()">
                    <option value="">All statuses</option>
                    @foreach (['open', 'closed'] as $s)
                        <option value="{{ $s }}" @selected($status === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                </form>
                <div class="w-auto">
                    @include('admin.partials.search-form', [
                        'embedded' => true,
                        'placeholder' => 'Search product or customer...',
                    ])
                </div>
            </div>
            <div class="text-muted small">
                Showing {{ $conversations->firstItem() ?? 0 }}-{{ $conversations->lastItem() ?? 0 }} of {{ $conversations->total() }}
            </div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">#</th>
                        <th>Product</th>
                        <th>Customer</th>
                        <th>Seller</th>
                        <th>Messages</th>
                        <th>Status</th>
                        <th>Last activity</th>
                        <th class="pe-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($conversations as $conversation)
                        <tr>
                            <td class="ps-3 admin-row-number">{{ $conversations->firstItem() + $loop->index }}</td>
                            <td>
                                <a href="{{ route('admin.support.conversations.show', $conversation) }}" class="fw-semibold text-decoration-none">
                                    {{ $conversation->product?->name ?? 'Deleted product' }}
                                </a>
                                <div class="small text-muted">{{ $conversation->subject ?: 'General inquiry' }}</div>
                            </td>
                            <td>
                                <div>{{ $conversation->user?->name ?? 'Guest' }}</div>
                                <div class="small text-muted">{{ $conversation->user?->email ?? '—' }}</div>
                            </td>
                            <td>{{ $conversation->seller?->name ?? '—' }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $conversation->messages_count ?? 0 }}</span></td>
                            <td><span class="badge {{ $conversation->status === 'open' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($conversation->status) }}</span></td>
                            <td class="small text-muted">{{ ($conversation->last_message_at ?? $conversation->created_at)?->format('d M Y, H:i') }}</td>
                            <td class="pe-3 table-action-col">
                                <div class="action-buttons">
                                    <a href="{{ route('admin.support.conversations.show', $conversation) }}" class="action-btn btn-view" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="View"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></a>
                                    <form action="{{ route('admin.support.conversations.destroy', $conversation) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this conversation?')">
                                        @csrf @method('DELETE')
                                        <button class="action-btn btn-cancel" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No conversations found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $conversations->links() }}</div>
    </div>
</div>
@endsection
