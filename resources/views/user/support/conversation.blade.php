@extends('user.layouts.dashboard')
@include('partials.page-attributes', ['pageId' => 'user-support-conversation-page', 'pageClass' => 'user-support-conversation-page'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Conversation #'.$conversation->id.' - Steve Autoparts',
        'metaTitle' => 'Product Conversation | Steve Autoparts',
        'metaDescription' => 'View your product conversation and replies at StAutoparts.',
    ])
@endsection
@section('dashboard-content')
@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if ($errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <!-- <a href="{{ route('user.support.index') }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1">
        <i class="fas fa-arrow-left"></i> Back to Support
    </a> -->
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex align-items-center gap-3 flex-wrap">
        @if ($conversation->product?->main_image)
            <img src="{{ storedImageUrl($conversation->product->main_image, 'assets/images') }}" alt="" class="rounded" style="width:56px;height:56px;object-fit:cover;">
        @endif
        <div>
            <h5 class="mb-0 h6 fw-semibold">
                @if ($conversation->product)
                    <a href="{{ route('product', $conversation->product->slug) }}" class="text-decoration-none">{{ $conversation->product->name }}</a>
                @else
                    Deleted product
                @endif

                <span class="badge {{ $conversation->status === 'open' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($conversation->status) }}</span>
            </h5>
            <div class="small text-muted">
                {{ $conversation->subject ?: 'General inquiry' }}
                @if ($conversation->seller)
                    &middot; Seller: {{ $conversation->seller->name }}
                @endif
                &middot; {{ $conversation->created_at->format('M d, Y h:i A') }}
            </div>
        </div>
    </div>
    <div class="card-body">
        @forelse ($conversation->messages as $message)
            @php($isCustomer = $message->sender_type === 'customer')
            <div class="p-3 mb-3 rounded {{ $isCustomer ? 'bg-light' : 'bg-primary-subtle border' }}">
                <div class="d-flex justify-content-between mb-2">
                    <strong>
                        @if ($isCustomer)
                            You
                        @elseif ($message->sender_type === 'seller')
                            Seller
                        @else
                            Support Team
                        @endif
                    </strong>
                    <small class="text-muted">{{ $message->created_at->format('M d, Y h:i A') }}</small>
                </div>
                <div style="white-space:pre-line;">{{ $message->message }}</div>
            </div>
        @empty
            <div class="text-center text-muted py-3">No messages yet.</div>
        @endforelse

        @if ($conversation->status === 'closed')
            <div class="alert alert-secondary mb-0">This conversation is closed. Start a new one from the product page if you need more help.</div>
        @else
            <form action="{{ route('user.support.conversation.reply', $conversation) }}" method="POST" class="mt-3">
                @csrf
                <div class="mb-2">
                    <label for="conversation-reply" class="form-label">Reply</label>
                    <textarea id="conversation-reply" name="message" rows="4" class="form-control textarea-control" maxlength="5000" placeholder="Type your message..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary steve-btn d-inline-flex align-items-center gap-1">
                    <i class="fas fa-paper-plane"></i> Send Message
                </button>
            </form>
        @endif
    </div>
</div>
@endsection
