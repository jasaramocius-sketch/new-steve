@extends('user.layouts.dashboard')
@include('partials.page-attributes', ['pageId' => 'user-support-page', 'pageClass' => 'user-support-page'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'My Support - Steve Autoparts',
        'metaTitle' => 'My Support | Steve Autoparts',
        'metaDescription' => 'Create support tickets and view your product conversations at StAutoparts.',
    ])
@endsection
@section('dashboard-content')
@php($statusColors = ['open' => 'bg-warning text-dark', 'pending' => 'bg-info text-dark', 'resolved' => 'bg-success', 'closed' => 'bg-secondary'])

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if ($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h4 class="h4-style mb-0">My Support</h4>
    <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#newTicketModal">
        <i class="fas fa-plus"></i> New Ticket
    </button>
</div>

{{-- Tickets --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0 h6 fw-semibold"><i class="fas fa-ticket-alt me-2 text-primary"></i>My Tickets</h5>
        <span class="badge bg-light text-dark border">{{ $tickets->total() }}</span>
    </div>
    <div class="card-body p-0">
        @forelse ($tickets as $ticket)
            <div class="d-flex justify-content-between align-items-start gap-3 p-3 border-bottom flex-wrap">
                <div class="flex-grow-1">
                    <a href="{{ route('user.support.ticket', $ticket) }}" class="fw-semibold text-decoration-none">
                        {{ $ticket->subject }}
                    </a>
                    <div class="small text-muted">
                        #{{ $ticket->id }} &middot; {{ $ticket->created_at->format('M d, Y h:i A') }}
                        &middot; {{ $ticket->replies_count }} {{ \Illuminate\Support\Str::plural('reply', $ticket->replies_count) }}
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge {{ $statusColors[$ticket->status] ?? 'bg-secondary' }}">{{ ucfirst($ticket->status) }}</span>
                    <div class="action-buttons">
                        <a href="{{ route('user.support.ticket', $ticket) }}" class="action-btn btn-view" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="View Ticket">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-4">You have not created any support tickets yet.</div>
        @endforelse
    </div>
    @if ($tickets->hasPages())
        <div class="p-3">{{ $tickets->links() }}</div>
    @endif
</div>

{{-- Product conversations --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0 h6 fw-semibold"><i class="fas fa-comments me-2 text-primary"></i>Product Conversations</h5>
        <span class="badge bg-light text-dark border">{{ $conversations->total() }}</span>
    </div>
    <div class="card-body p-0">
        @forelse ($conversations as $conversation)
            <div class="d-flex justify-content-between align-items-start gap-3 p-3 border-bottom flex-wrap">
                <div class="d-flex align-items-start gap-3 flex-grow-1">
                    @if ($conversation->product?->main_image)
                        <img src="{{ storedImageUrl($conversation->product->main_image, 'assets/images') }}" alt="" class="rounded" style="width:56px;height:56px;object-fit:cover;">
                    @endif
                    <div>
                        <a href="{{ route('user.support.conversation', $conversation) }}" class="fw-semibold text-decoration-none">
                            {{ $conversation->product?->name ?? 'Deleted product' }}
                        </a>
                        <div class="small text-muted">
                            {{ $conversation->subject ?: 'General inquiry' }} &middot;
                            {{ $conversation->messages_count }} {{ \Illuminate\Support\Str::plural('message', $conversation->messages_count) }} &middot;
                            {{ ($conversation->last_message_at ?? $conversation->created_at)->format('M d, Y h:i A') }}
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge {{ $conversation->status === 'open' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($conversation->status) }}</span>
                    <div class="action-buttons">
                        <a href="{{ route('user.support.conversation', $conversation) }}" class="action-btn btn-view" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="View Conversation">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-4">
                No product conversations yet. Open any product and use <strong>Ask a question</strong> to start one.
            </div>
        @endforelse
    </div>
    @if ($conversations->hasPages())
        <div class="p-3">{{ $conversations->links() }}</div>
    @endif
</div>

{{-- New ticket modal --}}
<div class="modal fade" id="newTicketModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('user.support.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">New Support Ticket</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="ticket-subject" class="form-label">Subject *</label>
                        <input type="text" id="ticket-subject" name="subject" class="form-control" maxlength="255" value="{{ old('subject') }}" required>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 mb-3">
                            <label for="ticket-category" class="form-label">Category</label>
                            <select id="ticket-category" name="category" class="form-select">
                                <option value="General">General</option>
                                <option value="Order">Order</option>
                                <option value="Product">Product</option>
                                <option value="Payment">Payment</option>
                                <option value="Shipping">Shipping</option>
                                <option value="Return">Return / Refund</option>
                                <option value="Technical">Technical</option>
                            </select>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <label for="ticket-priority" class="form-label">Priority</label>
                            <select id="ticket-priority" name="priority" class="form-select">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="ticket-message" class="form-label">Message *</label>
                        <textarea id="ticket-message" name="message" rows="5" class="form-control" maxlength="5000" required placeholder="Describe your issue in detail...">{{ old('message') }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1"><i class="fas fa-paper-plane"></i> Create Ticket</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
