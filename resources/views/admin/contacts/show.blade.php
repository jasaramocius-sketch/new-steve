@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-contact-detail-page', 'pageClass' => 'admin-contact-detail-page'])
@section('page-title', 'Contact Detail')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Contact Detail - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Contact #{{ $contact->id }}</h4>
    <!-- <a href="{{ route('admin.contacts.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a> -->
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-semibold">Message Details</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold text-muted small">Subject</label>
                    <p class="mb-0">{{ $contact->subject }}</p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold text-muted small">Message</label>
                    @php
                        $productName = null;
                        $productUrl = null;
                        $messageBody = trim($contact->message ?? '');
                        if (preg_match('/^Product:\s*(.+?)\nProduct URL:\s*(\S+)(?:\n\n|\n)(.*)$/is', $messageBody, $m)) {
                            $productName = trim($m[1]);
                            $productUrl = normalizeStoredUrl(trim($m[2]));
                            $messageBody = trim($m[3]);
                        }
                    @endphp
                    <div class="p-3 bg-light rounded">
                        @if($productName || $productUrl)
                            @if($productName)
                            <div class="d-flex align-items-start mb-1 gap-2">
                                <i class="fas fa-box text-muted mt-1"></i>
                                <span><strong class="form-label fw-semibold text-muted small d-block mb-0">Product</strong>{{ $productName }}</span>
                            </div>
                            @endif
                            @if($productUrl)
                            <div class="d-flex align-items-start mb-1 gap-2">
                                <i class="fas fa-link text-muted mt-1"></i>
                                <span><strong class="form-label fw-semibold text-muted small d-block mb-0">Product URL</strong>
                                    <a href="{{ $productUrl }}" target="_blank" rel="noopener" class="text-decoration-none">{{ $productUrl }}</a>
                                </span>
                            </div>
                            @endif
                        @elseif($contact->product)
                            <div class="d-flex align-items-start mb-1 gap-2">
                                <i class="fas fa-box text-muted mt-1"></i>
                                <span><strong class="form-label fw-semibold text-muted small d-block mb-0">Product</strong>
                                    <a href="{{ route('product', $contact->product->slug) }}" target="_blank" rel="noopener" class="text-decoration-none">{{ $contact->product->name }}</a>
                                </span>
                            </div>
                        @endif

                        @if($messageBody)
                            <div class="admin-preserve-whitespace" style="line-height:1.7;padding-left: 28px;">{{ $messageBody }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-semibold">Reply to Inquiry</h6>
                @if($contact->replied_at)
                    <span class="badge bg-light text-success border border-success-subtle rounded-pill">Replied</span>
                @endif
            </div>
            <div class="card-body">
                @if($contact->reply)
                <div class="mb-3">
                    <label class="form-label fw-semibold text-muted small">Your Reply
                        @if($contact->replier)
                            <span class="text-muted fw-normal">— {{ $contact->replier->name }}</span>
                        @endif
                    </label>
                    <div class="p-3 bg-light rounded admin-preserve-whitespace">{{ $contact->reply }}</div>
                    @if($contact->replied_at)
                        <small class="text-muted">{{ $contact->replied_at->format('M d, Y h:i A') }}</small>
                    @endif
                </div>
                @endif

                <form action="{{ route('admin.contacts.reply', $contact->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-muted small">{{ $contact->reply ? 'Update reply' : 'Write reply' }}</label>
                        <textarea name="reply" class="form-control" rows="5" placeholder="Type your reply to {{ $contact->name }}...">{{ old('reply', $contact->reply) }}</textarea>
                        @error('reply')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary steve-btn"><i class="fas fa-paper-plane me-1"></i> {{ $contact->reply ? 'Update Reply' : 'Send Reply' }}</button>
                        <a href="mailto:{{ $contact->email }}" class="btn btn-outline-secondary steve-btn"><i class="fas fa-envelope me-1"></i> Email {{ $contact->name }}</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-semibold">Contact Info</h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <small class="text-muted">Name</small>
                    <p class="mb-0 fw-semibold">{{ $contact->name }}</p>
                </div>
                <div class="mb-2">
                    <small class="text-muted">Email</small>
                    <p class="mb-0"><a href="mailto:{{ $contact->email }}">{{ $contact->email }}</a></p>
                </div>
                @if($contact->phone)
                <div class="mb-2">
                    <small class="text-muted">Phone</small>
                    <p class="mb-0">{{ $contact->phone }}</p>
                </div>
                @endif
                <div class="mb-2">
                    <small class="text-muted">Date</small>
                    <p class="mb-0">{{ $contact->created_at->format('M d, Y h:i A') }}</p>
                </div>
                @if($contact->user)
                <div class="mb-2">
                    <small class="text-muted">User</small>
                    <p class="mb-0"><a href="{{ route('admin.users.index') }}">{{ $contact->user->name }} ({{ $contact->user->email }})</a></p>
                </div>
                @endif
            </div>
        </div>

        @if($contact->product)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-semibold">Product</h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    @if($contact->product->image)
                        <img src="{{ storedImageUrl($contact->product->image, 'assets/images/thumbnails') }}" alt="" class="admin-contact-product-image">
                    @endif
                    <div>
                        <a href="{{ route('product', $contact->product->slug) }}" target="_blank" class="fw-semibold text-dark text-decoration-none">{{ $contact->product->name }}</a>
                        <div class="text-muted small">{{ currency_format($contact->product->price) }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="{{ route('admin.contacts.destroy', $contact->id) }}" method="POST" onsubmit="return confirm('Delete this contact permanently?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger w-100"><i class="fas fa-trash me-1"></i> Delete Contact</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
