@extends('admin.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-support-tickets-page', 'pageClass' => 'admin-support-tickets-page'])
@section('page-title', 'Support Tickets')
@section('meta_tags')
    @include('partials.meta-tags', ['pageTitle' => 'Support Tickets - Admin Panel', 'robots' => 'noindex, nofollow'])
@endsection
@section('content')
@php($statusColors = ['open' => 'bg-warning text-dark', 'pending' => 'bg-info text-dark', 'resolved' => 'bg-success', 'closed' => 'bg-secondary'])
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="d-flex justify-content-between align-items-center p-2 flex-wrap gap-2">
            <form method="GET" action="{{ route('admin.support.tickets.index') }}" class="d-flex align-items-center gap-2 flex-wrap">
                <div class="input-group" style="min-width:240px;">
                    <button type="submit" class="input-group-text btn btn-primary steve-btn gap-1" title="Search"><i class="fas fa-search"></i></button>
                    <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Search subject, name or email...">
                </div>
                @if ($search !== '')
                    <a href="{{ request()->fullUrlWithQuery(['search' => null, 'page' => null]) }}" class="btn btn-outline-danger d-flex align-items-center justify-content-center h-40px w-40px" title="Clear search"><i class="fas fa-times"></i></a>
                @endif
                <select name="status" class="form-select w-auto" onchange="this.form.submit()">
                    <option value="">All statuses</option>
                    @foreach (['open', 'pending', 'resolved', 'closed'] as $s)
                        <option value="{{ $s }}" @selected($status === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <select name="per_page" class="form-select w-auto" onchange="this.form.submit()">
                    @foreach ([10, 20, 50, 100] as $n)
                        <option value="{{ $n }}" @selected((int) request('per_page', 20) === $n)>{{ $n }} / page</option>
                    @endforeach
                </select>
            </form>
            <div class="text-muted small">
                Showing {{ $tickets->firstItem() ?? 0 }}-{{ $tickets->lastItem() ?? 0 }} of {{ $tickets->total() }}
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">#</th>
                        <th>Subject</th>
                        <th>Customer</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Replies</th>
                        <th>Created</th>
                        <th class="pe-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tickets as $ticket)
                        <tr>
                            <td class="ps-3">{{ $tickets->firstItem() + $loop->index }}</td>
                            <td>
                                <a href="{{ route('admin.support.tickets.show', $ticket) }}" class="fw-semibold text-decoration-none">{{ $ticket->subject }}</a>
                                <div class="small text-muted text-truncate" style="max-width:320px;">{{ \Illuminate\Support\Str::limit($ticket->message, 70) }}</div>
                            </td>
                            <td>
                                <div>{{ $ticket->name }}</div>
                                <div class="small text-muted">{{ $ticket->email }}</div>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ ucfirst($ticket->priority) }}</span></td>
                            <td><span class="badge {{ $statusColors[$ticket->status] ?? 'bg-secondary' }}">{{ ucfirst($ticket->status) }}</span></td>
                            <td>{{ $ticket->replies_count ?? 0 }}</td>
                            <td class="small text-muted">{{ $ticket->created_at?->format('d M Y, H:i') }}</td>
                            <td class="pe-3 table-action-col">
                                <div class="action-buttons">
                                    <a href="{{ route('admin.support.tickets.show', $ticket) }}" class="action-btn btn-view" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="View"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></a>
                                    <form action="{{ route('admin.support.tickets.destroy', $ticket) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this ticket?')">
                                        @csrf @method('DELETE')
                                        <button class="action-btn btn-cancel" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No tickets found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-3">{{ $tickets->links() }}</div>
    </div>
</div>
@endsection
