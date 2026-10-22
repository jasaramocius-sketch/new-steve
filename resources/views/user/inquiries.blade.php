@extends('user.layouts.dashboard')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'user-inquiries-page', 'pageClass' => 'user-inquiries-page'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'My Inquiries - StAutoparts',
        'metaTitle' => 'My Inquiries | StAutoparts',
        'metaDescription' => 'View your product inquiries and replies at StAutoparts.',
    ])
@endsection
@section('dashboard-content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h4 class="h4-style mb-0">My Inquiries</h4>
</div>

@forelse($inquiries as $inquiry)
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
            <div>
                <h6 class="mb-1 fw-semibold">{{ $inquiry->subject }}</h6>
                <small class="text-muted">{{ $inquiry->created_at->format('M d, Y h:i A') }}</small>
            </div>
            @if($inquiry->replied_at)
                <span class="badge badge--success">Replied</span>
            @else
                <div class="d-flex align-items-center gap-2">
                    <span class="badge badge--warning">Pending</span>
                    <div class="d-flex gap-1">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleInquiryEdit({{ $inquiry->id }})">
                            <i class="fas fa-edit me-1"></i>Edit
                        </button>
                        <form action="{{ route('user.inquiries.destroy', $inquiry->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this inquiry?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        @php
            $productName = null;
            $productUrl = null;
            $messageBody = trim($inquiry->message ?? '');
            if (preg_match('/^Product:\s*(.+?)\nProduct URL:\s*(\S+)(?:\n\n|\n)(.*)$/is', $messageBody, $m)) {
                $productName = trim($m[1]);
                $productUrl = normalizeStoredUrl(trim($m[2]));
                $messageBody = trim($m[3]);
            }
        @endphp

        <div class="p-3 bg-light rounded mb-3">
            @if($productName || $productUrl)
                @if($productName)
                <div class="d-flex align-items-start mb-1 gap-2">
                    <i class="fas fa-box text-muted mt-1" style="width:18px; height:18px;"></i>
                    <span><strong class="text-muted small d-block">Product:</strong>{{ $productName }}</span>
                </div>
                @endif
                @if($productUrl)
                <div class="d-flex align-items-start mb-1 gap-2">
                    <i class="fas fa-link text-muted mt-1" style="width:18px; height:18px;"></i>
                    <span><strong class="text-muted small d-block">Product URL:</strong>
                        <a href="{{ $productUrl }}" target="_blank" rel="noopener" class="text-decoration-none" style="word-break: break-word;">{{ $productUrl }}</a>
                    </span>
                </div>
                @endif
            @elseif($inquiry->product)
                <div class="d-flex align-items-start mb-1 gap-2">
                    <i class="fas fa-box text-muted mt-1" style="width:18px; height:18px;"></i>
                    <span><strong class="text-muted small d-block">Product:</strong>
                        <a href="{{ route('product', $inquiry->product->slug) }}" target="_blank" rel="noopener" class="text-decoration-none" style="word-break: break-word;">{{ $inquiry->product->name }}</a>
                    </span>
                </div>
            @endif

            @if($messageBody)
                <div class="admin-preserve-whitespace" style="line-height:1.7;padding-left: 28px;">
                    <strong class="text-muted small d-block">Message:</strong>
                    {{ $messageBody }}</div>
            @endif
        </div>

        @if($inquiry->reply)
        <div class="inquiry-reply-card rounded-3 overflow-hidden">
            <div class="inquiry-reply-header d-flex align-items-center gap-2 px-3 py-2">
                <div class="inquiry-reply-avatar">
                    @if($inquiry->replier)
                        {{ strtoupper(substr($inquiry->replier->name, 0, 1)) }}
                    @else
                        <i class="fas fa-user"></i>
                    @endif
                </div>
                <div class="flex-grow-1">
                    <span class="inquiry-reply-label">Reply from
                        @if($inquiry->replier)
                            <strong>{{ $inquiry->replier->name }}</strong>
                        @else
                            <strong>Support</strong>
                        @endif
                    </span>
                </div>
                @if($inquiry->replied_at)
                    <small class="inquiry-reply-time">{{ $inquiry->replied_at->format('M d, Y h:i A') }}</small>
                @endif
            </div>
            <div class="inquiry-reply-body px-3 pb-3 pt-2 admin-preserve-whitespace">{{ $inquiry->reply }}</div>
        </div>
        @endif

        @if(!$inquiry->replied_at)
        <div id="inquiry-edit-{{ $inquiry->id }}" class="inquiry-edit-form d-none border-top pt-3 mt-3">
            <form action="{{ route('user.inquiries.update', $inquiry->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-2">
                    <label class="form-label small fw-semibold mb-1">Subject</label>
                    <input type="text" name="subject" class="form-control form-control-sm" value="{{ old('subject', $inquiry->subject) }}" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-semibold mb-1">Message</label>
                    <textarea name="message" rows="4" class="form-control form-control-sm" maxlength="2000" required>{{ old('message', trim($messageBody)) }}</textarea>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary steve-btn"><i class="fas fa-save me-1"></i>Save</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary steve-btn" onclick="toggleInquiryEdit({{ $inquiry->id }})">Cancel</button>
                </div>
            </form>
        </div>
        @endif
    </div>
</div>
@empty
<div class="card">
    <div class="card-body text-center py-5">
        <p class="text-muted mb-0">You have not submitted any inquiries yet.</p>
    </div>
</div>
@endforelse

@if(method_exists($inquiries, 'links'))
    <div class="pagination-wrapper mt-4">
        {{ $inquiries->links() }}
    </div>
@endif

<style>
    .inquiry-reply-card {
        background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
        border: 1px solid rgba(34,197,94,.18);
    }
    .inquiry-reply-header {
        background: rgba(34,197,94,.08);
        border-bottom: 1px solid rgba(34,197,94,.12);
    }
    .inquiry-reply-avatar {
        width: 30px; height: 30px; border-radius: 50%;
        background: linear-gradient(135deg, #22c55e, #16a34a);
        color: #fff; font-size: 13px; font-weight: 700;
        display: inline-flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .inquiry-reply-label { font-size: 13px; color: #166534; }
    .inquiry-reply-label strong { color: #15803d; }
    .inquiry-reply-time { font-size: 11px; color: #6b7280; white-space: nowrap; }
    .inquiry-reply-body { font-size: 14px; line-height: 1.8; color: #1e293b; white-space: pre-wrap; }
</style>

<script>
    function toggleInquiryEdit(id) {
        const el = document.getElementById('inquiry-edit-' + id);
        if (el) el.classList.toggle('d-none');
    }
</script>
@endsection
