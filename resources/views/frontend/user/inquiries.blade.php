@extends('frontend.user.layouts.dashboard')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'user-inquiries-page', 'pageClass' => 'user-inquiries-page'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'My Inquiries - Steve Autoparts',
        'metaTitle' => 'My Inquiries | Steve Autoparts',
        'metaDescription' => 'View your product inquiries and replies at StAutoparts.',
    ])
@endsection
@section('dashboard-content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h4 class="h4-style mb-0">My Inquiries</h4>
    <button type="button" class="btn btn-primary steve-btn d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#newInquiryModal">
        <i class="fas fa-plus"></i> New Inquiry
    </button>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if (session('info'))
    <div class="alert alert-info">{{ session('info') }}</div>
@endif

@forelse($inquiries as $inquiry)
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
            <div>
                <h6 class="mb-1 fw-semibold">{{ $inquiry->subject }}</h6>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <small class="text-muted">{{ $inquiry->created_at->format('M d, Y h:i A') }}</small>
                    <span class="badge bg-light text-dark border">{{ $inquiry->categoryLabel() }}</span>
                    <span class="badge bg-light text-dark border">Priority: {{ ucfirst($inquiry->priority ?: 'medium') }}</span>
                    @if($inquiry->legacy_ticket_id)
                        <span class="badge bg-light text-secondary border" title="Migrated from Support Tickets">Ticket #{{ $inquiry->legacy_ticket_id }}</span>
                    @endif
                    @if($inquiry->legacy_conversation_id)
                        <span class="badge bg-light text-secondary border" title="Migrated from Product Conversations">Conversation #{{ $inquiry->legacy_conversation_id }}</span>
                    @endif
                </div>
            </div>
            @if($inquiry->replied_at)
                <span class="badge badge--success">Replied</span>
            @else
                <div class="d-flex align-items-center gap-2">
                    <span class="badge badge--warning">Pending</span>
                    <div class="action-buttons">
                        <button type="button" class="action-btn btn-edit" onclick="toggleInquiryEdit({{ $inquiry->id }})" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Edit inquiry" aria-label="Edit inquiry">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </button>
                        <form action="{{ route('user.inquiries.destroy', $inquiry->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this inquiry?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="action-btn btn-cancel" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete inquiry" aria-label="Delete inquiry">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
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
                    <input type="text" name="subject" class="form-control" value="{{ old('subject', $inquiry->subject) }}" required>
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-semibold mb-1">Message</label>
                    <textarea name="message" rows="4" class="form-control textarea-control" maxlength="2000" required>{{ old('message', trim($messageBody)) }}</textarea>
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

{{-- New inquiry modal --}}
<div class="modal fade" id="newInquiryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('user.inquiries.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">New Inquiry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">{{ $errors->first() }}</div>
                    @endif
                    <div class="mb-3">
                        <label for="inquiry-subject" class="form-label">Subject *</label>
                        <input type="text" id="inquiry-subject" name="subject" class="form-control" maxlength="255" value="{{ old('subject') }}" required>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 mb-3">
                            <label for="inquiry-category" class="form-label">Category</label>
                            <select id="inquiry-category" name="category" class="form-select">
                                @foreach(\App\Models\Contact::categories() as $value => $label)
                                    <option value="{{ $value }}" {{ old('category', 'General') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <label for="inquiry-priority" class="form-label">Priority</label>
                            <select id="inquiry-priority" name="priority" class="form-select">
                                @foreach(\App\Models\Contact::priorities() as $value => $label)
                                    <option value="{{ $value }}" {{ old('priority', 'medium') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="inquiry-message" class="form-label">Message *</label>
                        <textarea id="inquiry-message" name="message" rows="5" class="form-control textarea-control" maxlength="5000" required placeholder="Describe your question in detail...">{{ old('message') }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1" data-bs-dismiss="modal"><i class="fas fa-times"></i> Cancel</button>
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1"><i class="fas fa-paper-plane"></i> Submit Inquiry</button>
                </div>
            </form>
        </div>
    </div>
</div>

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
