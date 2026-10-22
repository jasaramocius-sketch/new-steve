@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-questions-page', 'pageClass' => 'admin-questions-page'])
@section('page-title', 'Questions / Inquiries')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Questions / Inquiries - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap flex-md-nowrap">
    <h4 class="fw-bold mb-0">Questions / Inquiries</h4>
    <form method="GET" class="d-flex gap-2 align-items-center">
        <select name="source" class="form-select w-auto" onchange="this.form.submit()">
            <option value="">All Sources</option>
            <option value="product" {{ request('source') === 'product' ? 'selected' : '' }}>Questions</option>
            <option value="contact" {{ request('source') === 'contact' ? 'selected' : '' }}>Inquiries</option>
        </select>
    </form>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="d-flex justify-content-between align-items-center p-2 flex-wrap flex-md-nowrap flex-wrap flex-md-nowrap">
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">Show</span>
                <select class="form-select w-auto" onchange="window.location.href=this.value">
                    @foreach([10, 20, 50, 100] as $n)
                        <option value="{{ request()->fullUrlWithQuery(['per_page' => $n, 'page' => null]) }}" {{ (int)request('per_page', 20) === $n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                </select>
                <span class="text-muted small">per page</span>
            </div>
            <div class="text-muted small">
                Showing {{ $contacts->firstItem() }}-{{ $contacts->lastItem() }} of {{ $contacts->total() }}
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">No.</th>
                        <th><a href="{{ sortUrl('name', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Name {!! sortIndicator('name', $sortBy, $sortDir) !!}</a></th>
                        <th>Email</th>
                        <th>Page</th>
                        <th><a href="{{ sortUrl('created_at', $sortBy, $sortDir) }}" class="text-decoration-none text-dark">Date {!! sortIndicator('created_at', $sortBy, $sortDir) !!}</a></th>
                        <th>Status</th>
                        <th class="pe-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($contacts as $contact)
                    <tr>
                        <td class="ps-3">{{ $contacts->firstItem() + $loop->index }}</td>
                        <td>
                            <div>{{ $contact->name }}</div>
                            <small class="text-muted">{{ $contact->phone ?? '' }}</small>
                        </td>
                        <td><small>{{ $contact->email }}</small></td>
                        <td>
                            @if($contact->product)
                                <a href="{{ route('product', $contact->product->slug) }}" target="_blank" class="text-decoration-none primary-a-tag-text-hover">{{ Str::limit($contact->product->name, 30) }}</a>
                            @else
                                <a href="{{ route('contact') }}"
                                target="_blank"
                                class="text-decoration-none primary-a-tag-text-hover">
                                    Contact Page
                                </a>
                            @endif
                        </td>
                        <td><small>{{ $contact->created_at->format('M d, Y') }}</small></td>
                        <td>
                            @if($contact->replied_at)
                                <span class="badge bg-light text-success border border-success-subtle rounded-pill">Replied</span>
                            @else
                                <span class="badge bg-light text-warning border border-warning-subtle rounded-pill">Pending</span>
                            @endif
                        </td>
                        <td class="pe-3 table-action-col">
                            <div class="action-buttons">
                                <a href="{{ route('admin.contacts.show', $contact->id) }}" class="action-btn btn-view" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="View"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></a>
                                <form action="{{ route('admin.contacts.destroy', $contact->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this contact?')">
                                    @csrf @method('DELETE')
                                    <button class="action-btn btn-cancel" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No contacts found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-3 py-3">
            {{ $contacts->links('vendor.pagination.gs-pagination') }}
        </div>
    </div>
</div>

@endsection
