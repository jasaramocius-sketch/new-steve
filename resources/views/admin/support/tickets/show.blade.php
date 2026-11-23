@extends('admin.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-support-ticket-show-page', 'pageClass' => 'admin-support-ticket-show-page'])
@section('page-title', 'Ticket #'.$ticket->id)
@section('meta_tags')
    @include('partials.meta-tags', ['pageTitle' => 'Ticket #'.$ticket->id.' - Admin Panel', 'robots' => 'noindex, nofollow'])
@endsection
@section('content')
@php($statusColors = ['open' => 'bg-warning text-dark', 'pending' => 'bg-info text-dark', 'resolved' => 'bg-success', 'closed' => 'bg-secondary'])
<div class="container-fluid px-15px px-lg-25px">
    @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <!-- <a href="{{ route('admin.support.tickets.index') }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1"><i class="fas fa-arrow-left"></i> Back</a> -->
        <div class="d-flex gap-2 align-items-center">
            <form action="{{ route('admin.support.tickets.status', $ticket) }}" method="POST" class="d-flex gap-1">
                @csrf
                <select name="status" class="form-select w-auto">
                    @foreach (['open', 'pending', 'resolved', 'closed'] as $s)
                        <option value="{{ $s }}" @selected($ticket->status === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <button class="btn btn-sm btn-primary">Update</button>
            </form>
            <form action="{{ route('admin.support.tickets.destroy', $ticket) }}" method="POST" onsubmit="return confirm('Delete this ticket?')">
                @csrf @method('DELETE')
                <button class="btn steve-btn btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
            </form>
            <span class="badge {{ $statusColors[$ticket->status] ?? 'bg-secondary' }}">{{ ucfirst($ticket->status) }}</span>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-3">
            <div class="card h-100">
                <div class="card-header"><h2 class="h6 mb-0">Ticket Details</h2></div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <tbody>
                            <tr><th class="w-50">Ticket</th><td>#{{ $ticket->id }}</td></tr>
                            <tr><th>Customer</th><td>{{ $ticket->name }}</td></tr>
                            <tr><th>Email</th><td class="text-break">{{ $ticket->email }}</td></tr>
                            @if ($ticket->user)
                                <tr><th>Account</th><td>{{ $ticket->user->name }} ({{ $ticket->user->email }})</td></tr>
                            @endif
                            <tr><th>Category</th><td>{{ $ticket->category ?: '—' }}</td></tr>
                            <tr><th>Priority</th><td>{{ ucfirst($ticket->priority) }}</td></tr>
                            <tr><th>Created</th><td>{{ $ticket->created_at?->format('d M Y, H:i') }}</td></tr>
                            <tr><th>Last Reply</th><td>{{ $ticket->last_reply_at?->format('d M Y, H:i') ?? '—' }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-8 mb-3">
            <div class="card">
                <div class="card-header"><h2 class="h6 mb-0">{{ $ticket->subject }}</h2></div>
                <div class="card-body">
                    <div class="p-3 mb-3 rounded bg-light">
                        <div class="d-flex justify-content-between mb-2">
                            <strong>{{ $ticket->name }}</strong>
                            <small class="text-muted">{{ $ticket->created_at?->format('d M Y, H:i') }}</small>
                        </div>
                        <div style="white-space:pre-line;">{{ $ticket->message }}</div>
                    </div>

                    @foreach ($ticket->replies as $reply)
                        <div class="p-3 mb-3 rounded {{ $reply->sender_type === 'admin' ? 'bg-primary-subtle border' : 'bg-light' }}">
                            <div class="d-flex justify-content-between mb-2">
                                <strong>{{ $reply->sender_type === 'admin' ? 'Support Team' : $ticket->name }}</strong>
                                <small class="text-muted">{{ $reply->created_at?->format('d M Y, H:i') }}</small>
                            </div>
                            <div style="white-space:pre-line;">{{ $reply->message }}</div>
                        </div>
                    @endforeach

                    <form action="{{ route('admin.support.tickets.reply', $ticket) }}" method="POST">
                        @csrf
                        <div class="mb-2">
                            <label for="reply-message" class="form-label">Reply</label>
                            <textarea id="reply-message" name="message" rows="4" class="form-control textarea-control" placeholder="Type your reply..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1"><i class="fas fa-paper-plane"></i> Send Reply</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
