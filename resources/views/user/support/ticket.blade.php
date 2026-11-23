@extends('user.layouts.dashboard')
@include('partials.page-attributes', ['pageId' => 'user-support-ticket-page', 'pageClass' => 'user-support-ticket-page'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Ticket #'.$ticket->id.' - Steve Autoparts',
        'metaTitle' => 'Support Ticket | Steve Autoparts',
        'metaDescription' => 'View your support ticket and replies at StAutoparts.',
    ])
@endsection
@section('dashboard-content')
@php($statusColors = ['open' => 'bg-warning text-dark', 'pending' => 'bg-info text-dark', 'resolved' => 'bg-success', 'closed' => 'bg-secondary'])

<!-- @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if ($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif -->

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0 h6 fw-semibold">{{ $ticket->subject }}
            <span class="badge {{ $statusColors[$ticket->status] ?? 'bg-secondary' }}">{{ ucfirst($ticket->status) }}</span>
        </h5>
        <div class="small text-muted">
            #{{ $ticket->id }} &middot; {{ ucfirst($ticket->category ?: 'General') }} &middot;
            {{ ucfirst($ticket->priority) }} priority &middot; {{ $ticket->created_at->format('M d, Y h:i A') }}
        </div>
    </div>
    <div class="card-body">
        <div class="p-3 mb-3 rounded bg-light">
            <div class="d-flex justify-content-between mb-2">
                <strong>{{ $ticket->name }} <span class="badge bg-secondary ms-1">You</span></strong>
                <small class="text-muted">{{ $ticket->created_at->format('M d, Y h:i A') }}</small>
            </div>
            <div style="white-space:pre-line;">{{ $ticket->message }}</div>
        </div>

        @foreach ($ticket->replies as $reply)
            <div class="p-3 mb-3 rounded {{ $reply->sender_type === 'admin' ? 'bg-primary-subtle border' : 'bg-light' }}">
                <div class="d-flex justify-content-between mb-2">
                    <strong>{{ $reply->sender_type === 'admin' ? 'Support Team' : 'You' }}</strong>
                    <small class="text-muted">{{ $reply->created_at->format('M d, Y h:i A') }}</small>
                </div>
                <div style="white-space:pre-line;">{{ $reply->message }}</div>
            </div>
        @endforeach

        @if ($ticket->status === 'closed')
            <div class="alert alert-secondary mb-0">This ticket is closed. Create a new ticket if you still need help.</div>
        @else
            <form action="{{ route('user.support.ticket.reply', $ticket) }}" method="POST" class="mt-3">
                @csrf
                <div class="mb-2">
                    <label for="ticket-reply" class="form-label">Reply</label>
                    <textarea id="ticket-reply" name="message" rows="4" class="form-control textarea-control" maxlength="5000" placeholder="Type your reply..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary steve-btn d-inline-flex align-items-center gap-1">
                    <i class="fas fa-paper-plane"></i> Send Reply
                </button>
            </form>
        @endif
    </div>
</div>
@endsection
