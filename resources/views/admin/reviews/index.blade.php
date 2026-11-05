@extends('admin.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-reviews-index-page', 'pageClass' => 'admin-reviews-index-page'])
@section('page-title', 'Product Reviews')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Reviews - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="d-flex justify-content-between align-items-center p-2 flex-wrap flex-md-nowrap">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted small">Status</span>
                    <select class="form-select w-auto" onchange="window.location.href=this.value">
                        <option value="{{ route('admin.reviews.index') }}" {{ !$status ? 'selected' : '' }}>All</option>
                        <option value="{{ route('admin.reviews.index', ['status' => 'approved']) }}" {{ $status === 'approved' ? 'selected' : '' }}>Visible</option>
                        <option value="{{ route('admin.reviews.index', ['status' => 'disapproved']) }}" {{ $status === 'disapproved' ? 'selected' : '' }}>Hidden</option>
                    </select>
                </div>
                <form method="GET" action="{{ route('admin.reviews.index') }}" class="d-flex align-items-center gap-2">
                    @if($status)
                        <input type="hidden" name="status" value="{{ $status }}">
                    @endif
                    <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search product, reviewer, review..." style="min-width:220px;">
                    <button class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
                </form>
                @if($status || $search)
                    <a href="{{ route('admin.reviews.index') }}" class="btn steve-btn btn-outline-danger"
                       data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Clear filters">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </div>
            <div class="text-muted small">
                Showing {{ $reviews->firstItem() }}-{{ $reviews->lastItem() }} of {{ $reviews->total() }}
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Product</th>
                        <th>Reviewer</th>
                        <th>Rating</th>
                        <th>Review</th>
                        <th>Date</th>
                        <th>Visibility</th>
                        <th class="pe-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $review)
                    <tr>
                        <td class="ps-3">
                            <a href="{{ route('product', $review['product_slug']) }}" target="_blank" class="text-decoration-none text-dark">
                                <strong>{{ \Illuminate\Support\Str::limit($review['product_name'], 40) }}</strong>
                            </a>
                        </td>
                        <td>{{ $review['user_name'] }}</td>
                        <td>
                            @for($i = 0; $i < 5; $i++)
                                <i class="fas fa-star {{ $i < $review['rating'] ? 'text-warning' : 'text-muted opacity-25' }}"></i>
                            @endfor
                        </td>
                        <td style="max-width:300px;">
                            <span class="d-inline-block text-truncate" style="max-width:280px;" title="{{ $review['review'] }}">
                                {{ \Illuminate\Support\Str::limit($review['review'], 80) }}
                            </span>
                            @if(!empty($review['images']))
                                <small class="text-muted ms-1"><i class="fas fa-images"></i> {{ count($review['images']) }}</small>
                            @endif
                        </td>
                        <td>{{ $review['date'] }}</td>
                        <td>
                            @if($review['approved'])
                                <span class="badge rounded-pill bg-light text-success border border-success-subtle">Visible</span>
                            @else
                                <span class="badge rounded-pill bg-light text-warning border border-warning-subtle">Hidden</span>
                            @endif
                        </td>
                        <td class="pe-3 table-action-col">
                            <div class="action-buttons">
                                <form action="{{ route('admin.reviews.toggle', [$review['product_id'], $review['review_id']]) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit"
                                            class="action-btn {{ $review['approved'] ? 'btn-cancel' : 'btn-view-live' }}"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            data-bs-original-title="{{ $review['approved'] ? 'Hide review' : 'Show review' }}">
                                        @if($review['approved'])
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                        @else
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        @endif
                                    </button>
                                </form>
                                <form action="{{ route('admin.reviews.destroy', [$review['product_id'], $review['review_id']]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this review?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn btn-cancel"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            data-bs-original-title="Delete review">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="100%" class="text-center py-4 text-muted">No reviews found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($reviews->hasPages())
            <div class="p-2 border-top">
                {{ $reviews->links() }}
            </div>
        @endif
    </div>
</div>
@endsection