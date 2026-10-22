@extends('user.layouts.dashboard')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'user-reviews-page', 'pageClass' => 'user-reviews-page'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'My Reviews - StAutoparts',
        'metaTitle' => 'My Reviews | StAutoparts',
        'metaDescription' => 'View and manage your product reviews at StAutoparts.',
    ])
@endsection
@section('dashboard-content')

<div class="user-reviews-page">
<div class="dashboard-topbar">
    <h4 class="h4-style mb-0">My Reviews</h4>
    <a href="{{ route('shop') }}" class="btn btn-primary steve-btn">Continue Shopping</a>
</div>
<div class="view-reviews-filter d-flex align-items-center justify-content-between mb-3 gap-2 flex-wrap">
    <form method="GET" action="{{ route('user.reviews') }}" class="d-flex gap-2 align-items-center" id="reviewFilterForm">
        @include('admin.partials.search-form', [
            'route' => route('user.reviews'),
            'placeholder' => 'Search...',
            'size' => 'sm',
            'showClear' => !empty($search),
            'clearRoute' => route('user.reviews', array_filter(['status' => $statusFilter ?? '']))
        ])
    </form>
    <form method="GET" action="{{ route('user.reviews') }}" id="statusFilterForm">
        <input type="hidden" name="search" value="{{ $search ?? '' }}">
        <select name="status" class="form-select" onchange="this.form.submit()">
            <option value="">All</option>
            <option value="pending" {{ ($statusFilter ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="reviewed" {{ ($statusFilter ?? '') === 'reviewed' ? 'selected' : '' }}>Reviewed</option>
        </select>
    </form>
</div>
<div class="table-responsive">
    <table class="table table--custom table--responsive-lg table-hover">
        <thead>
            <tr>
                <th>Product</th>
                <th>Review</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        @forelse($items as $item)
            <tr id="review-row-{{ md5($item['product_slug']) }}">
                <td data-label="Product">
                    <div class="d-flex align-items-center gap-2">
                        @if($item['product_image'])
                            {!! imgTag(storedPath($item['product_image'], 'assets/images/thumbnails'), '', '', 'style="width:40px;height:40px;object-fit:cover;border-radius:6px;"') !!}
                        @else
                            <div style="width:40px;height:40px;background:#f0f0f0;border-radius:6px;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-image text-muted"></i>
                            </div>
                        @endif
                        <div class="review-page-table-product-name">
                            <a href="{{ route('product', $item['product_slug']) }}" class="fw-semibold text-decoration-none">{{ $item['product_name'] }}</a>
                        </div>
                    </div>
                </td>
                <td data-label="Review">
                    @if($item['status'] === 'pending')
                        <span class="badge badge--warning mb-1">Pending</span>
                    @else
                        <div class="d-flex  justify-content-center gap-1">
                            @for($i = 0; $i < 5; $i++)
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="13" viewBox="0 0 17 16" fill="none">
                                    <path d="M8.5 0.5L10.4084 6.37336L16.584 6.37336L11.5878 10.0033L13.4962 15.8766L8.5 12.2467L3.50383 15.8766L5.41219 10.0033L0.416019 6.37336L6.59163 6.37336L8.5 0.5Z" fill="{{ $i < $item['rating'] ? '#EEAE0B' : '#E2E8F0' }}" />
                                </svg>
                            @endfor
                        </div>
                    @endif
                </td>
                <td data-label="Action" class="table-action-col">
                    @if($item['status'] === 'pending')
                        <div class="action-buttons" style="justify-content:flex-end;">
                            <button type="button" class="action-btn btn-invoice review-action-btn" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Add Review"
                                data-slug="{{ $item['product_slug'] }}"
                                data-name="{{ $item['product_name'] }}"
                                data-image="{{ storedImageUrl($item['product_image'], 'assets/images/thumbnails') }}"
                                data-status="{{ $item['status'] }}"
                                data-review-id=""
                                data-rating="{{ $item['rating'] }}"
                                data-text=""
                                data-images="[]"
                                data-date="">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                            </button>
                        </div>
                    @else
                        <div class="action-buttons" style="justify-content:flex-end;">
                            <button type="button" class="action-btn btn-view review-view-btn" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="View Review"
                                data-slug="{{ $item['product_slug'] }}"
                                data-name="{{ $item['product_name'] }}"
                                data-image="{{ storedImageUrl($item['product_image'], 'assets/images/thumbnails') }}"
                                data-rating="{{ $item['rating'] }}"
                                data-text="{{ addslashes($item['text'] ?? '') }}"
                                data-images="{!! htmlentities(json_encode($item['images'] ?? [])) !!}"
                                data-date="{{ $item['date'] ?? '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                            @if(!empty($item['review_id']))
                                <button type="button" class="action-btn btn-invoice review-edit-btn" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Edit Review"
                                    data-slug="{{ $item['product_slug'] }}"
                                    data-name="{{ $item['product_name'] }}"
                                    data-image="{{ storedImageUrl($item['product_image'], 'assets/images/thumbnails') }}"
                                    data-review-id="{{ $item['review_id'] ?? '' }}"
                                    data-rating="{{ $item['rating'] }}"
                                    data-text="{{ addslashes($item['text'] ?? '') }}"
                                    data-images="{!! htmlentities(json_encode($item['images'] ?? [])) !!}">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                </button>
                            @else
                                <button type="button" class="action-btn btn-invoice review-action-btn" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Add Review"
                                    data-slug="{{ $item['product_slug'] }}"
                                    data-name="{{ $item['product_name'] }}"
                                    data-image="{{ storedImageUrl($item['product_image'], 'assets/images/thumbnails') }}"
                                    data-status="{{ $item['status'] }}"
                                    data-review-id=""
                                    data-rating="0"
                                    data-text=""
                                    data-images="[]">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                    </svg>
                                </button>
                            @endif
                            <button type="button" class="action-btn btn-cancel review-delete-btn" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Delete Review"
                                data-slug="{{ $item['product_slug'] }}"
                                data-review-id="{{ $item['review_id'] ?? '' }}"
                                data-name="{{ $item['product_name'] }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                </svg>
                            </button>
                        </div>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="100%" class="text-center py-5">
                    <i class="fas fa-star fa-3x text-muted mb-3 d-block"></i>
                    <p class="text-muted mb-0">No purchased products or reviews found.</p>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

@if(method_exists($items, 'links'))
    <div class="pagination-wrapper mt-4">
        {{ $items->links('vendor.pagination.gs-pagination') }}
    </div>
@endif
</div>

{{-- Review Modal --}}
<div class="modal fade review-page-vr-modal" id="reviewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-zoom" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-600 h5" id="reviewModalTitle">Write a Review</h5>
        <button type="button" class="btn-close steve-btn" data-bs-dismiss="modal" aria-label="Close">
          <!-- <span aria-hidden="true">&times;</span> -->
        </button>
      </div>

      {{-- VIEW PANEL --}}
      <div id="reviewViewPanel" style="display:none;">
        <div class="modal-body gry-bg px-3 pt-3">
          <div id="reviewModalAlert" class="alert alert-danger d-none mb-3" style="border-radius:0;"></div>
          <div class="d-flex align-items-center gap-3 mb-3 pb-3" style="border-bottom:1px solid #ebedf2;">
            <img id="reviewProductImage" src="" alt="Product" style="width:50px;height:50px;object-fit:cover;">
            <a id="reviewProductLink" href="" class="fw-600 text-dark text-decoration-none fs-14"></a>
          </div>
          <div class="mb-4 py-3">
            <div id="reviewViewStars" style="font-size:24px;letter-spacing:2px;"></div>
            <div id="reviewViewRatingText" class="mt-1 fw-700 fs-14 text-dark"></div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-600 fs-14 text-dark mb-1">Comment</label>
            <p id="reviewViewText" class="mb-0 fs-14" style="color:#555;white-space:pre-wrap;line-height:1.7;"></p>
          </div>
          <div class="mb-3">
            <label class="form-label fw-600 fs-14 text-dark mb-2">Review Images</label>
            <div id="reviewViewImages" class="d-flex flex-wrap gap-2"></div>
          </div>
          <div class="pt-2" style="border-top:1px solid #ebedf2;">
            <small class="text-secondary" id="reviewViewDate"></small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary fw-600 steve-btn" data-bs-dismiss="modal">Close</button>
        </div>
      </div>

      {{-- FORM PANEL --}}
      <form id="reviewFormPanel">
        <div class="modal-body gry-bg px-3 pt-3">
          <div id="reviewModalAlert2" class="alert alert-danger d-none mb-3" style="border-radius:0;"></div>
          <div class="d-flex align-items-center gap-3 mb-3 pb-3" style="border-bottom:1px solid #ebedf2;">
            <img id="reviewProductImage2" src="" alt="Product" style="width:50px;height:50px;object-fit:cover;">
            <a id="reviewProductLink2" href="" class="fw-600 text-dark text-decoration-none fs-14"></a>
          </div>
          <div class="form-group mb-3">
            <label class="form-label fw-600 fs-14 text-dark">Rating *</label>
            <div class="star-picker d-flex align-items-center gap-1 mt-1" id="reviewModalStarPicker">
              @for($i = 1; $i <= 5; $i++)
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="21" viewBox="0 0 17 16" fill="none" data-rating="{{ $i }}" class="review-star-pick" style="cursor:pointer;">
                  <path d="M8.5 0.5L10.4084 6.37336L16.584 6.37336L11.5878 10.0033L13.4962 15.8766L8.5 12.2467L3.50383 15.8766L5.41219 10.0033L0.416019 6.37336L6.59163 6.37336L8.5 0.5Z" fill="#E2E8F0" />
                </svg>
              @endfor
              <input type="hidden" name="rating" id="reviewModalRating" value="0">
            </div>
          </div>
          <div class="form-group mb-3">
            <label class="form-label fw-600 fs-14 text-dark">Comment *</label>
            <textarea class="form-control rounded-0" id="reviewModalText" rows="3" maxlength="1000" placeholder="Share your experience with this product..."></textarea>
          </div>
          <div class="form-group mb-3">
            <label class="form-label fw-600 fs-14 text-dark">Review Images</label>
            <div class="review-image-upload-wrapper d-flex align-items-center mt-1">
              <input type="file" id="reviewModalImages" multiple accept="image/jpg,image/jpeg,image/png,image/webp" class="d-none">
              <button type="button" class="btn btn-outline-primary rounded-0 fw-600 btn-sm" id="reviewBrowseBtn">
                <i class="fas fa-cloud-upload-alt"></i> Browse
              </button>
              <span class="text-secondary ms-2 fs-12">Max 5 images, 2MB each</span>
            </div>
            <div id="reviewImagePreview" class="d-flex flex-wrap gap-2 mt-2"></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary fw-600 w-100px steve-btn" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary fw-600 w-100px steve-btn" id="reviewModalSubmitBtn">Submit Review</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Image Gallery Lightbox Modal --}}
<div class="modal fade review-page-gallery-modal" id="reviewImageModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-zoom" role="document" style="max-width:800px;">
    <div class="modal-content" style="background:#0000;border:none;">
      <div class="modal-header" style="border-bottom:none;padding:10px 20px;">
        <!-- <span class="text-white fw-600 fs-14" id="reviewLightboxTitle">Review Image</span> -->
        <span class="text-white fs-13" style="background-color:var(--primary); padding:10px;font-weight: 500;" id="reviewLightboxCounter">1 / 3</span>
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close" style="filter:invert(1);">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body p-0 position-relative text-center" style="min-height:300px;">
        <button type="button" class="review-lightbox-prev" id="reviewLightboxPrev">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
        </button>
        <img id="reviewLightboxImg" src="" alt="Review image" style="max-height:70vh;max-width:100%;object-fit:contain;display:inline-block;">
        <button type="button" class="review-lightbox-next" id="reviewLightboxNext">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
        </button>
      </div>
    </div>
  </div>
</div>
</div>

<style>
.review-image-upload-wrapper { display: flex; align-items: center; }
.review-img-thumb { position: relative; width: 60px; height: 60px; overflow: hidden; border: 1px solid #ebedf2; }
.review-img-thumb img { width: 100%; height: 100%; object-fit: cover; }
.review-img-thumb .remove-img { position: absolute; top: 2px; right: 2px; background: rgba(0,0,0,0.6); color: #fff; border: none; border-radius: 50%; width: 18px; height: 18px; font-size: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; line-height: 1; }
.review-lightbox-prev, .review-lightbox-next {
  position: absolute; top: 50%; transform: translateY(-50%);
  background: rgba(0,0,0,0.4); border: none; width: 44px; height: 44px;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; transition: background 0.2s; border-radius: 50%; z-index: 2;
}
.review-lightbox-prev { left: 12px; }
.review-lightbox-next { right: 12px; }
.review-lightbox-prev:hover, .review-lightbox-next:hover { background: rgba(0,0,0,0.7); }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var reviewImages = [];
    var currentSlug = '';
    var currentReviewId = '';
    var reviewModalEl = document.getElementById('reviewModal');
    var reviewModal = new bootstrap.Modal(reviewModalEl);

    var viewPanel = document.getElementById('reviewViewPanel');
    var formPanel = document.getElementById('reviewFormPanel');
    var submitBtn = document.getElementById('reviewModalSubmitBtn');

    // Write review (pending items)
    document.querySelectorAll('.review-action-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            currentSlug = btn.dataset.slug;
            currentReviewId = '';

            document.getElementById('reviewProductImage2').src = btn.dataset.image;
            document.getElementById('reviewProductLink2').href = '{{ url("/product") }}/' + btn.dataset.slug;
            document.getElementById('reviewProductLink2').textContent = btn.dataset.name;
            document.getElementById('reviewModalTitle').textContent = 'Write a Review';
            document.getElementById('reviewModalAlert2').classList.add('d-none');

            viewPanel.style.display = 'none';
            formPanel.style.display = '';
            submitBtn.textContent = 'Submit Review';

            // Reset form
            document.getElementById('reviewModalRating').value = '0';
            document.getElementById('reviewModalText').value = '';
            document.getElementById('reviewImagePreview').innerHTML = '';
            reviewImages = [];
            document.getElementById('reviewModalImages').value = '';

            document.getElementById('reviewModalStarPicker').querySelectorAll('svg.review-star-pick').forEach(function(s) {
                s.querySelector('path').setAttribute('fill', '#E2E8F0');
            });

            reviewModal.show();
        });
    });

    // View review
    document.querySelectorAll('.review-view-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('reviewProductImage').src = btn.dataset.image;
            document.getElementById('reviewProductLink').href = '{{ url("/product") }}/' + btn.dataset.slug;
            document.getElementById('reviewProductLink').textContent = btn.dataset.name;
            document.getElementById('reviewModalTitle').textContent = 'View Review';
            document.getElementById('reviewModalAlert').classList.add('d-none');

            formPanel.style.display = 'none';

            var rating = parseInt(btn.dataset.rating) || 0;
            var text = btn.dataset.text || '';
            var date = btn.dataset.date || '';

            var svgTpl = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="13" viewBox="0 0 17 16" fill="none"><path d="M8.5 0.5L10.4084 6.37336L16.584 6.37336L11.5878 10.0033L13.4962 15.8766L8.5 12.2467L3.50383 15.8766L5.41219 10.0033L0.416019 6.37336L6.59163 6.37336L8.5 0.5Z" fill="FILL_COLOR"></path></svg>';
            var starsHtml = '';
            for (var i = 0; i < 5; i++) {
                starsHtml += svgTpl.replace('FILL_COLOR', i < rating ? '#EEAE0B' : '#E2E8F0');
            }
            document.getElementById('reviewViewStars').innerHTML = starsHtml;
            var ratingLabels = ['', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];
            document.getElementById('reviewViewRatingText').textContent = rating + '/5 - ' + (ratingLabels[rating] || '');
            document.getElementById('reviewViewText').textContent = text;
            document.getElementById('reviewViewDate').textContent = date ? 'Reviewed on ' + date : '';

            var imgContainer = document.getElementById('reviewViewImages');
            imgContainer.innerHTML = '';
            try {
                var existingImages = JSON.parse(btn.dataset.images || '[]');
                existingImages.forEach(function(imgPath) {
                    var a = document.createElement('a');
                    a.href = 'javascript:void(0);';
                    a.setAttribute('data-review-img', '{{ asset("/") }}' + imgPath);
                    a.className = 'review-lightbox-trigger';
                    var img = document.createElement('img');
                    img.src = '{{ asset("/") }}' + imgPath;
                    img.alt = 'Review image';
                    img.style.cssText = 'width:100px;height:100px;object-fit:cover;border:1px solid #ebedf2;cursor:pointer;';
                    a.appendChild(img);
                    imgContainer.appendChild(a);
                });
                if (existingImages.length === 0) {
                    imgContainer.innerHTML = '<span class="fs-13 text-secondary">No images uploaded.</span>';
                }
            } catch(e) {
                imgContainer.innerHTML = '<span class="fs-13 text-secondary">No images uploaded.</span>';
            }

            viewPanel.style.display = '';
            reviewModal.show();
        });
    });

    // Edit review (reviewed items)
    document.querySelectorAll('.review-edit-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            currentSlug = btn.dataset.slug;
            currentReviewId = btn.dataset.reviewId || '';

            document.getElementById('reviewProductImage2').src = btn.dataset.image;
            document.getElementById('reviewProductLink2').href = '{{ url("/product") }}/' + btn.dataset.slug;
            document.getElementById('reviewProductLink2').textContent = btn.dataset.name;
            document.getElementById('reviewModalTitle').textContent = 'Edit Review';
            document.getElementById('reviewModalAlert2').classList.add('d-none');

            viewPanel.style.display = 'none';
            formPanel.style.display = '';
            submitBtn.textContent = 'Update Review';

            var rating = parseInt(btn.dataset.rating) || 0;
            document.getElementById('reviewModalRating').value = rating;
            document.getElementById('reviewModalText').value = btn.dataset.text;
            document.getElementById('reviewImagePreview').innerHTML = '';
            reviewImages = [];
            document.getElementById('reviewModalImages').value = '';

            document.getElementById('reviewModalStarPicker').querySelectorAll('svg.review-star-pick').forEach(function(s) {
                var val = parseInt(s.dataset.rating);
                s.querySelector('path').setAttribute('fill', val <= rating ? '#EEAE0B' : '#E2E8F0');
            });

            try {
                var existingImages = JSON.parse(btn.dataset.images || '[]');
                var container = document.getElementById('reviewImagePreview');
                existingImages.forEach(function(imgPath) {
                    var div = document.createElement('div');
                    div.className = 'review-img-thumb';
                    div.setAttribute('data-existing-path', imgPath);
                    var img = document.createElement('img');
                    img.src = '{{ asset("/") }}' + imgPath;
                    var removeBtn = document.createElement('button');
                    removeBtn.className = 'remove-img';
                    removeBtn.innerHTML = '&times;';
                    removeBtn.addEventListener('click', function() {
                        div.remove();
                    });
                    div.appendChild(img);
                    div.appendChild(removeBtn);
                    container.appendChild(div);
                });
            } catch(e) {}

            reviewModal.show();
        });
    });

    // Delete review
    document.querySelectorAll('.review-delete-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (!confirm('Are you sure you want to delete this review?')) return;

            var slug = btn.dataset.slug;
            var reviewId = btn.dataset.reviewId;

            fetch('{{ url("/product") }}/' + slug + '/review/' + reviewId, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    toastr.success('Review deleted successfully!');
                    location.reload();
                } else {
                    toastr.error(data.message || 'Failed to delete review.');
                }
            })
            .catch(function() {
                toastr.error('Something went wrong.');
            });
        });
    });

    // Star picker
    var starPicker = document.getElementById('reviewModalStarPicker');
    var stars = starPicker.querySelectorAll('svg.review-star-pick');
    stars.forEach(function(star) {
        star.addEventListener('mouseenter', function() {
            var val = parseInt(star.dataset.rating);
            stars.forEach(function(s) {
                var v = parseInt(s.dataset.rating);
                s.querySelector('path').setAttribute('fill', v <= val ? '#EEAE0B' : '#E2E8F0');
            });
        });
        star.addEventListener('click', function() {
            document.getElementById('reviewModalRating').value = star.dataset.rating;
        });
    });
    starPicker.addEventListener('mouseleave', function() {
        var val = parseInt(document.getElementById('reviewModalRating').value) || 0;
        stars.forEach(function(s) {
            var v = parseInt(s.dataset.rating);
            s.querySelector('path').setAttribute('fill', v <= val ? '#EEAE0B' : '#E2E8F0');
        });
    });

    // Browse button
    document.getElementById('reviewBrowseBtn').addEventListener('click', function() {
        document.getElementById('reviewModalImages').click();
    });

    // File input change -> preview
    document.getElementById('reviewModalImages').addEventListener('change', function(e) {
        var files = Array.from(e.target.files);
        files.forEach(function(file) {
            if (reviewImages.length >= 5) return;
            if (file.size > 2 * 1024 * 1024) {
                alert(file.name + ' is larger than 2MB.');
                return;
            }
            if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                alert(file.name + ' is not a supported image format.');
                return;
            }
            reviewImages.push(file);
        });
        renderImagePreviews();
        e.target.value = '';
    });

    function renderImagePreviews() {
        var container = document.getElementById('reviewImagePreview');
        var existingThumbs = container.querySelectorAll('[data-existing-path]');
        container.innerHTML = '';
        existingThumbs.forEach(function(el) { container.appendChild(el); });
        reviewImages.forEach(function(file, index) {
            var div = document.createElement('div');
            div.className = 'review-img-thumb';
            var img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            var removeBtn = document.createElement('button');
            removeBtn.className = 'remove-img';
            removeBtn.innerHTML = '&times;';
            removeBtn.addEventListener('click', function() {
                reviewImages.splice(index, 1);
                renderImagePreviews();
            });
            div.appendChild(img);
            div.appendChild(removeBtn);
            container.appendChild(div);
        });
    }

    // Submit
    submitBtn.addEventListener('click', function() {
        var rating = parseInt(document.getElementById('reviewModalRating').value);
        var text = document.getElementById('reviewModalText').value.trim();
        var alertEl = document.getElementById('reviewModalAlert2');

        if (!rating || rating < 1 || rating > 5) {
            alertEl.textContent = 'Please select a rating.';
            alertEl.classList.remove('d-none');
            return;
        }
        if (!text) {
            alertEl.textContent = 'Please write your review.';
            alertEl.classList.remove('d-none');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';
        alertEl.classList.add('d-none');

        var formData = new FormData();
        formData.append('rating', rating);
        formData.append('text', text);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
        formData.append('_method', currentReviewId ? 'PUT' : 'POST');
        reviewImages.forEach(function(file) {
            formData.append('images[]', file);
        });

        var url = currentReviewId
            ? '{{ url("/product") }}/' + currentSlug + '/review/' + currentReviewId
            : '{{ url("/product") }}/' + currentSlug + '/review';

        fetch(url, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        })
        .then(function(r) { return r.json().then(function(body) { return { status: r.status, body: body }; }); })
        .then(function(res) {
            if (res.status === 401) {
                alertEl.innerHTML = 'Please <a href="{{ route("login") }}">login</a> to write a review.';
                alertEl.classList.remove('d-none');
                submitBtn.disabled = false;
                submitBtn.textContent = currentReviewId ? 'Update Review' : 'Submit Review';
            } else if (res.body.success) {
                toastr.success('Review submitted successfully!');
                reviewModal.hide();
                location.reload();
            } else {
                alertEl.textContent = res.body.message || 'Something went wrong.';
                alertEl.classList.remove('d-none');
                submitBtn.disabled = false;
                submitBtn.textContent = currentReviewId ? 'Update Review' : 'Submit Review';
            }
        })
        .catch(function() {
            alertEl.textContent = 'Something went wrong. Please try again.';
            alertEl.classList.remove('d-none');
            submitBtn.disabled = false;
            submitBtn.textContent = currentReviewId ? 'Update Review' : 'Submit Review';
        });
    });

    // Image lightbox gallery
    var galleryImages = [];
    var galleryIndex = 0;
    var lightboxImg = document.getElementById('reviewLightboxImg');
    var lightboxCounter = document.getElementById('reviewLightboxCounter');
    var lightboxTitle = document.getElementById('reviewLightboxTitle');

    function showGalleryImage(idx) {
        if (galleryImages.length === 0) return;
        galleryIndex = idx;
        lightboxImg.src = galleryImages[galleryIndex];
        lightboxCounter.textContent = (galleryIndex + 1) + ' / ' + galleryImages.length;
        document.getElementById('reviewLightboxPrev').style.display = galleryImages.length > 1 ? '' : 'none';
        document.getElementById('reviewLightboxNext').style.display = galleryImages.length > 1 ? '' : 'none';
    }

    document.addEventListener('click', function(e) {
        var trigger = e.target.closest('.review-lightbox-trigger');
        if (trigger) {
            galleryImages = [];
            document.querySelectorAll('.review-lightbox-trigger').forEach(function(t) {
                galleryImages.push(t.getAttribute('data-review-img'));
            });
            var clickedSrc = trigger.getAttribute('data-review-img');
            galleryIndex = galleryImages.indexOf(clickedSrc);
            if (galleryIndex === -1) galleryIndex = 0;
            showGalleryImage(galleryIndex);
            var lightboxModal = new bootstrap.Modal(document.getElementById('reviewImageModal'));
            lightboxModal.show();
        }
    });

    document.getElementById('reviewLightboxPrev').addEventListener('click', function() {
        if (galleryImages.length === 0) return;
        galleryIndex = (galleryIndex - 1 + galleryImages.length) % galleryImages.length;
        showGalleryImage(galleryIndex);
    });

    document.getElementById('reviewLightboxNext').addEventListener('click', function() {
        if (galleryImages.length === 0) return;
        galleryIndex = (galleryIndex + 1) % galleryImages.length;
        showGalleryImage(galleryIndex);
    });

    // Keyboard navigation
    document.getElementById('reviewImageModal').addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft') {
            document.getElementById('reviewLightboxPrev').click();
        } else if (e.key === 'ArrowRight') {
            document.getElementById('reviewLightboxNext').click();
        }
    });
});
</script>

@endsection
