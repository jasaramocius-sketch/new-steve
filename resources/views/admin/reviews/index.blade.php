@extends('admin.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-reviews-index-page', 'pageClass' => 'admin-reviews-index-page'])
@section('page-title', 'Reviews')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Reviews - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection

@section('content')
 <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Product Reviews</h4>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="d-flex justify-content-between align-items-center p-2 flex-wrap flex-md-nowrap">
            <div class="d-flex align-items-center gap-3 flex-wrap">
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
                    <input type="text" name="search" value="{{ $search }}" class="form-control form-control-sm" placeholder="Search product, reviewer, review..." style="min-width:220px;">
                    <button class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button>
                </form>
            </div>
            <div class="text-muted small">
                Showing {{ $reviews->firstItem() }}-{{ $reviews->lastItem() }} of {{ $reviews->total() }}
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Product</th>
                        <th>Reviewer</th>
                        <th>Rating</th>
                        <th>Review</th>
                        <th>Date</th>
                        <th>Visibility</th>
                        <th class="">Action</th>
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
                        <td>
                            <div class="d-flex gap-2">
                                <form action="{{ route('admin.reviews.toggle', [$review['product_id'], $review['review_id']]) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $review['approved'] ? 'btn-outline-warning' : 'btn-outline-success' }}">
                                        <i class="fas {{ $review['approved'] ? 'fa-eye-slash' : 'fa-eye' }}"></i> {{ $review['approved'] ? 'Hide' : 'Show' }}
                                    </button>
                                </form>
                                <form action="{{ route('admin.reviews.destroy', [$review['product_id'], $review['review_id']]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this review?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
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