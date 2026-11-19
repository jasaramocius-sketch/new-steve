@extends('admin.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-support-conversation-show-page', 'pageClass' => 'admin-support-conversation-show-page'])
@section('page-title', 'Conversation #'.$conversation->id)
@section('meta_tags')
    @include('partials.meta-tags', ['pageTitle' => 'Conversation #'.$conversation->id.' - Admin Panel', 'robots' => 'noindex, nofollow'])
@endsection
@section('content')
<div class="container-fluid px-15px px-lg-25px">
    <!-- @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if ($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif -->

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <!-- <a href="{{ route('admin.support.conversations.index') }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1"><i class="fas fa-arrow-left"></i> Back</a> -->
        <div class="d-flex gap-2 align-items-center">
            <form action="{{ route('admin.support.conversations.status', $conversation) }}" method="POST" class="d-flex gap-1">
                @csrf
                <select name="status" class="form-select w-auto">
                    @foreach (['open', 'closed'] as $s)
                        <option value="{{ $s }}" @selected($conversation->status === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary">Update</button>
            </form>
            <form action="{{ route('admin.support.conversations.destroy', $conversation) }}" method="POST" onsubmit="return confirm('Delete this conversation?')">
                @csrf @method('DELETE')
                <button class="btn steve-btn btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
            </form>
            <span class="badge {{ $conversation->status === 'open' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($conversation->status) }}</span>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-3">
            <div class="card h-100">
                <div class="card-header"><h2 class="h6 mb-0">Conversation Details</h2></div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <tbody>
                            <tr><th class="w-50">Conversation</th><td>#{{ $conversation->id }}</td></tr>
                            <tr><th>Product</th><td>{{ $conversation->product?->name ?? 'Deleted product' }}</td></tr>
                            @if ($conversation->product?->main_image)
                                <tr><th>Image</th><td><img src="{{ storedImageUrl($conversation->product->main_image, 'assets/images') }}" alt="" class="rounded" style="width:64px;height:64px;object-fit:cover;"></td></tr>
                            @endif
                            <tr><th>Subject</th><td>{{ $conversation->subject ?: 'General inquiry' }}</td></tr>
                            <tr><th>Customer</th><td>{{ $conversation->user?->name ?? 'Guest' }}</td></tr>
                            <tr><th>Email</th><td class="text-break">{{ $conversation->user?->email ?? '—' }}</td></tr>
                            <tr><th>Seller</th><td>{{ $conversation->seller?->name ?? '—' }}</td></tr>
                            <tr><th>Created</th><td>{{ $conversation->created_at?->format('d M Y, H:i') }}</td></tr>
                            <tr><th>Last activity</th><td>{{ ($conversation->last_message_at ?? $conversation->created_at)?->format('d M Y, H:i') }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-8 mb-3">
            <div class="card">
                <div class="card-header"><h2 class="h6 mb-0">Messages</h2></div>
                <div class="card-body">
                    @forelse ($conversation->messages as $message)
                        @php($isAdmin = $message->sender_type === 'admin')
                        <div class="p-3 mb-3 rounded {{ $isAdmin ? 'bg-primary-subtle border' : 'bg-light' }}">
                            <div class="d-flex justify-content-between mb-2">
                                <strong>
                                    {{ $isAdmin ? 'Support Team' : ($message->sender?->name ?? ucfirst($message->sender_type)) }}
                                    @unless ($isAdmin)
                                        <span class="badge bg-secondary ms-1">{{ ucfirst($message->sender_type) }}</span>
                                    @endunless
                                </strong>
                                <small class="text-muted">{{ $message->created_at?->format('d M Y, H:i') }}</small>
                            </div>
                            <div style="white-space:pre-line;">{{ $message->message }}</div>
                        </div>
                    @empty
                        <div class="text-muted text-center py-3">No messages yet.</div>
                    @endforelse

                    <form action="{{ route('admin.support.conversations.reply', $conversation) }}" method="POST">
                        @csrf
                        <div class="mb-2">
                            <label for="conversation-reply" class="form-label">Reply</label>
                            <textarea id="conversation-reply" name="message" rows="4" class="form-control textarea-control" placeholder="Type your reply..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary steve-btn d-inline-flex align-items-center gap-1"><i class="fas fa-paper-plane"></i> Send Reply</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
