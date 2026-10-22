@extends('user.layouts.dashboard')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'user-followed-sellers-page', 'pageClass' => 'user-followed-sellers-page'])
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Followed Sellers - StAutoparts',
        'metaTitle' => 'Followed Sellers | StAutoparts',
        'metaDescription' => 'Manage the sellers you follow at StAutoparts.',
    ])
@endsection
@section('dashboard-content')

<section class="followed-sellers-section">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="h4-style mb-0">Followed Sellers</h4>
            <p class="text-muted mb-0">Manage the sellers you follow and view their profile summaries.</p>
        </div>
        <button class="btn btn-primary steve-btn" data-bs-toggle="modal" data-bs-target="#addSellerModal">
            <i class="fas fa-plus me-2"></i>Follow Seller
        </button>
    </div>

    <div class="row g-4 user-followed-sellers-grid">
        @forelse($followedSellers as $seller)
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body user-followed-seller-card">
                        <div class="d-flex align-items-start gap-3 mb-3 user-followed-seller-header">
                            <div class="avatar avatar-lg rounded-circle bg-light d-flex align-items-center justify-content-center" style="width:72px; height:72px; overflow:hidden;">
                                @if($seller->seller && $seller->seller->image)
                                    {!! imgTag(storedPath($seller->seller->image, 'assets/images'), $seller->seller_name, '', 'style="width:100%;height:100%;object-fit:cover;"') !!}
                                @else
                                    <span class="fs-4 text-primary">{{ strtoupper(substr($seller->seller_name, 0, 1)) }}</span>
                                @endif
                            </div>
                            <div>
                                <h5 class="mb-1">{{ $seller->seller_name }}</h5>
                                <p class="text-muted mb-0">{{ $seller->location }}</p>
                            </div>
                        </div>

                        <div class="text-secondary user-followed-seller-description" style="line-height:1.6;">{!! $seller->description !!}</div>

                        @php
                            $sellerProducts = $seller->seller ? $seller->seller->products : collect();
                            $productCount = $sellerProducts->isNotEmpty() ? $sellerProducts->count() : $seller->products;
                            $ratingCount = $sellerProducts->isNotEmpty() ? round($sellerProducts->avg('rating'), 1) : $seller->rating;
                            $followerCount = $seller->seller ? $seller->seller->followers_count : $seller->followers;
                        @endphp

                        <div class="d-flex flex-wrap gap-3 mt-4 user-followed-seller-stats">
                            <div class="badge bg-light text-dark p-2 rounded">
                                <strong>{{ $productCount }}</strong> Products
                            </div>
                            <div class="badge bg-light text-dark p-2 rounded">
                                <strong>{{ number_format($ratingCount, 1) }}</strong> Rating
                            </div>
                            <div class="badge bg-light text-dark p-2 rounded">
                                <strong>{{ number_format($followerCount) }}</strong> Followers
                            </div>
                        </div>

                        <div class="mt-4 d-flex gap-2 user-followed-seller-actions">
                            <button type="button" class="btn btn-primary view-seller-btn steve-btn" data-seller-id="{{ $seller->id }}" data-seller-name="{{ $seller->seller_name }}">
                                <i class="fas fa-eye me-1"></i> View Seller
                            </button>
                            <button type="button" class="btn btn-outline-danger unfollow-btn h-40px" data-seller-id="{{ $seller->id }}" data-seller-name="{{ $seller->seller_name }}">
                                <i class="fas fa-user-minus me-1"></i> Unfollow
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">
                    You are not following any sellers yet.
                </div>
            </div>
        @endforelse
    </div>
</section>

{{-- Add Seller Modal --}}
<div class="modal fade" id="addSellerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-600">Follow a Seller</h5>
                <button type="button" class="btn-close steve-btn" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addSellerForm">
                @csrf
                <div class="modal-body">
                    <div id="addSellerAlert" class="alert alert-danger d-none mb-3"></div>
                    @if($availableSellers->count())
                        <label class="form-label fw-600">Select Seller <span class="text-danger">*</span></label>
                        <div class="list-group sellers-select-list" style="max-height:350px; overflow-y:auto;">
                            @foreach($availableSellers as $s)
                                <label class="list-group-item list-group-item-action d-flex align-items-center gap-3 seller-option" data-seller-id="{{ $s->id }}" style="cursor:pointer;">
                                    <input type="radio" name="seller_id" value="{{ $s->id }}" class="form-check-input m-0">
                                    <span class="seller-option-avatar rounded-circle bg-light d-inline-flex align-items-center justify-content-center" style="width:40px;height:40px;overflow:hidden;flex-shrink:0;">
                                        @if($s->image)
                                            {!! imgTag(storedPath($s->image, 'assets/images'), $s->name, '', 'style="width:100%;height:100%;object-fit:cover;"') !!}
                                        @else
                                            <span class="text-primary">{{ strtoupper(substr($s->name, 0, 1)) }}</span>
                                        @endif
                                    </span>
                                    <span class="flex-grow-1">
                                        <span class="d-block fw-semibold">{{ $s->name }}</span>
                                        <span class="d-block small text-muted">{{ $s->location ?? 'Location not specified' }}</span>
                                    </span>
                                    @if($followedSellers->contains('seller_id', $s->id))
                                        <span class="badge bg-secondary">Following</span>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-warning mb-0">
                            No sellers are available to follow right now. Please check back later.
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary steve-btn" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary steve-btn" id="addSellerSubmitBtn" {{ $availableSellers->count() ? '' : 'disabled' }}>Follow Seller</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- View Seller Modal --}}
<div class="modal fade" id="viewSellerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-600" id="viewSellerTitle">Seller Details</h5>
                <button type="button" class="btn-close steve-btn" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="viewSellerBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .view-seller-btn, .unfollow-btn { min-width: 120px; }
    #viewSellerBody .seller-avatar { width: 80px; height: 80px; font-size: 2rem; }
    #viewSellerBody .stat-badge { font-size: 0.875rem; padding: 0.5rem 1rem; }
    .seller-option { border-radius: 8px; }
    .seller-option.active { border-color: var(--primary); background: rgba(230, 122, 56, 0.06); }
    .seller-option:has(input:checked) { border-color: var(--primary); background: rgba(230, 122, 56, 0.06); }
    .seller-product-thumb { width: 100%; aspect-ratio: 1/1; border-radius: 8px; overflow: hidden; background: #f8f9fa; }
    .seller-product-thumb img { object-fit: cover; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addSellerForm = document.getElementById('addSellerForm');
    const addSellerAlert = document.getElementById('addSellerAlert');
    const addSellerSubmitBtn = document.getElementById('addSellerSubmitBtn');
    const addSellerModalEl = document.getElementById('addSellerModal');
    const addSellerModal = bootstrap.Modal.getOrCreateInstance(addSellerModalEl);

    document.querySelectorAll('.seller-option').forEach(function(opt) {
        opt.addEventListener('click', function() {
            const radio = this.querySelector('input[type="radio"]');
            radio.checked = true;
        });
    });

    if (addSellerForm) {
        addSellerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const selected = document.querySelector('input[name="seller_id"]:checked');
            if (!selected) {
                addSellerAlert.textContent = 'Please select a seller.';
                addSellerAlert.classList.remove('d-none');
                return;
            }
            addSellerAlert.classList.add('d-none');
            addSellerSubmitBtn.disabled = true;
            addSellerSubmitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Following...';

            const formData = new FormData(addSellerForm);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

            fetch('{{ route("user.followed-sellers.store") }}', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    toastr.success('Seller followed successfully!');
                    addSellerModal.hide();
                    addSellerForm.reset();
                    setTimeout(() => location.reload(), 800);
                } else {
                    addSellerAlert.textContent = data.message || 'Failed to follow seller.';
                    addSellerAlert.classList.remove('d-none');
                }
            })
            .catch(() => {
                addSellerAlert.textContent = 'Something went wrong. Please try again.';
                addSellerAlert.classList.remove('d-none');
            })
            .finally(() => {
                addSellerSubmitBtn.disabled = false;
                addSellerSubmitBtn.innerHTML = 'Follow Seller';
            });
        });
    }

    document.querySelectorAll('.unfollow-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const sellerId = this.dataset.sellerId;
            const sellerName = this.dataset.sellerName;

            if (!confirm(`Are you sure you want to unfollow "${sellerName}"?`)) return;

            const btnEl = this;
            btnEl.disabled = true;
            btnEl.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Unfollowing...';

            fetch('{{ route("user.followed-sellers.destroy", ":id") }}'.replace(':id', sellerId), {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    toastr.success('Unfollowed successfully!');
                    btnEl.closest('.col-lg-6').remove();

                    const remaining = document.querySelectorAll('.col-lg-6 .card').length;
                    if (remaining === 0) {
                        document.querySelector('.row.g-4').innerHTML = `
                            <div class="col-12">
                                <div class="alert alert-info">You are not following any sellers yet.</div>
                            </div>
                        `;
                    }
                } else {
                    toastr.error(data.message || 'Failed to unfollow seller.');
                    btnEl.disabled = false;
                    btnEl.innerHTML = '<i class="fas fa-user-minus me-1"></i> Unfollow';
                }
            })
            .catch(() => {
                toastr.error('Something went wrong. Please try again.');
                btnEl.disabled = false;
                btnEl.innerHTML = '<i class="fas fa-user-minus me-1"></i> Unfollow';
            });
        });
    });

    document.querySelectorAll('.view-seller-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const sellerId = this.dataset.sellerId;
            const sellerName = this.dataset.sellerName;

            const modalEl = document.getElementById('viewSellerModal');
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            const titleEl = document.getElementById('viewSellerTitle');
            const bodyEl = document.getElementById('viewSellerBody');

            titleEl.textContent = sellerName;
            bodyEl.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
            modal.show();

            fetch('{{ route("user.api.seller.details", ":id") }}'.replace(':id', sellerId), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const s = data.seller;
                    bodyEl.innerHTML = `
                        <div class="text-center mb-4">
                            <div class="avatar avatar-xl rounded-circle bg-light d-flex align-items-center justify-content-center mx-auto seller-avatar" style="width:80px;height:80px;overflow:hidden;">
                                ${s.seller_image ? '<img src="' + s.seller_image + '" style="width:100%;height:100%;object-fit:cover;">' : '<span class="fs-1 text-primary">' + s.seller_name.charAt(0).toUpperCase() + '</span>'}
                            </div>
                            <h5 class="mt-3 mb-1">${s.seller_name}</h5>
                            <p class="text-muted mb-3">${s.location || 'Location not specified'}</p>
                            <div class="d-flex justify-content-center gap-2 flex-wrap">
                                <span class="badge bg-light text-dark stat-badge"><strong>${s.products || 0}</strong> Products</span>
                                <span class="badge bg-light text-dark stat-badge"><strong>${Number(s.rating || 0).toFixed(1)}</strong> Rating</span>
                                <span class="badge bg-light text-dark stat-badge"><strong>${Number(s.followers || 0).toLocaleString()}</strong> Followers</span>
                            </div>
                        </div>
                        ${s.description ? `
                            <div class="mt-4 p-3 bg-light rounded">
                                <h6 class="fw-600 mb-2">About</h6>
                                <div class="mb-0 text-secondary" style="line-height:1.6;">${s.description}</div>
                            </div>
                        ` : ''}
                        ${s.product_list && s.product_list.length ? `
                            <div class="mt-4">
                                <h6 class="fw-600 mb-2">Products</h6>
                                <div class="d-flex align-items-center flex-column modal-body p-0">
                                <div class="row g-2" id="sellerProductsGrid">
                                    ${s.product_list.map(function(p) {
                                        const productUrl = '{{ url("/product") }}' + '/' + p.slug;
                                        return `
                                        <div class="col-3">
                                            <a href="${productUrl}" class="text-decoration-none d-block text-center seller-product-item" title="${p.name}">
                                                <span class="seller-product-thumb d-block mx-auto mb-1">
                                                    <img src="${p.image}" alt="${p.name}" class="w-100 h-100" onerror="this.onerror=null;this.src='{{ asset('assets/images/placeholder.png') }}';">
                                                </span>
                                                <span class="d-block text-truncate fs-12 text-dark">${p.name}</span>
                                                <span class="d-block fs-12 fw-600" style="color:var(--primary);">${p.price}</span>
                                            </a>
                                        </div>`;
                                    }).join('')}
                                </div>
                                ${s.products > s.product_list.length ? `
                                    <button type="button" class="btn btn-sm btn-primary mt-3 seller-load-more steve-btn" data-seller-id="${sellerId}" data-offset="${s.product_list.length}">
                                        Load More Products
                                    </button>
                                ` : ''}
                                </div>
                            </div>
                        ` : ''}
                    `;

                    const loadMoreBtn = bodyEl.querySelector('.seller-load-more');
                    if (loadMoreBtn) {
                        loadMoreBtn.addEventListener('click', function() {
                            const btn = this;
                            const offset = parseInt(btn.dataset.offset, 10) || 0;
                            btn.disabled = true;
                            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Loading...';

                            fetch('{{ route("user.api.seller.products", ":id") }}'.replace(':id', sellerId) + '?offset=' + offset, {
                                headers: { 'X-Requested-With': 'XMLHttpRequest' }
                            })
                            .then(r => r.json())
                            .then(data => {
                                if (data.success && data.product_list.length) {
                                    const grid = bodyEl.querySelector('#sellerProductsGrid');
                                    data.product_list.forEach(function(p) {
                                        const productUrl = '{{ url("/product") }}' + '/' + p.slug;
                                        grid.insertAdjacentHTML('beforeend', `
                                            <div class="col-3">
                                                <a href="${productUrl}" class="text-decoration-none d-block text-center seller-product-item" title="${p.name}">
                                                    <span class="seller-product-thumb d-block mx-auto mb-1">
                                                        <img src="${p.image}" alt="${p.name}" class="w-100 h-100" onerror="this.onerror=null;this.src='{{ asset('assets/images/placeholder.png') }}';">
                                                    </span>
                                                    <span class="d-block text-truncate fs-12 text-dark">${p.name}</span>
                                                    <span class="d-block fs-12 fw-600" style="color:var(--primary);">${p.price}</span>
                                                </a>
                                            </div>`);
                                    });
                                    btn.dataset.offset = offset + data.product_list.length;
                                    if (data.has_more) {
                                        btn.disabled = false;
                                        btn.innerHTML = 'Load More Products';
                                    } else {
                                        btn.remove();
                                    }
                                } else {
                                    btn.remove();
                                }
                            })
                            .catch(() => {
                                btn.disabled = false;
                                btn.innerHTML = 'Load More Products';
                            });
                        });
                    }
                } else {
                    bodyEl.innerHTML = `<div class="text-center py-4 text-danger">Failed to load seller details.</div>`;
                }
            })
            .catch(() => {
                bodyEl.innerHTML = `<div class="text-center py-4 text-danger">Something went wrong.</div>`;
            });
        });
    });
});
</script>
@endsection
