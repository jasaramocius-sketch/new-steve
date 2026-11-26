@extends('backend.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-settings-search-categories-page', 'pageClass' => 'admin-settings-search-categories-page'])
@section('page-title', 'Search Categories')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Search Categories - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')
<div class="container-fluid admin-settings-search-categories">
    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-layer-group me-2"></i>Search Category Dropdown</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        Choose which categories appear in the category dropdown of the storefront search bar, and set their order.
                        "All Categories" is always shown first. Renaming a category later won't change the saved dropdown label until it is re-picked here.
                    </p>

                    <form action="{{ route('admin.settings.search-categories.update') }}" method="POST" id="searchCategoriesForm">
                        @csrf
                        <input type="hidden" name="search_category_menu" id="searchCategoriesInput" value="{{ json_encode($selected) }}">

                        <div class="row g-4">
                            {{-- Available categories --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-muted text-uppercase">Available Categories</label>
                                <div class="input-group mb-2">
                                    <button type="button" id="searchCategoriesFilterBtn" class="input-group-text btn steve-btn gap-1 btn-primary sc-filter-toggle" title="Search categories" data-bs-toggle="tooltip" data-bs-placement="top">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <input type="text" id="searchCategoriesFilter" class="form-control" placeholder="Search 21,300 categories..." autocomplete="off">
                                </div>
                                <div class="border rounded p-3 available-categories-list" style="max-height:700px;overflow-y:auto;background:#f8f9fa;">
                                    <div id="scCategoryResults" class="mb-0" data-lookup-url="{{ route('admin.settings.search-categories.lookup') }}">
                                        <div class="text-muted p-3 text-center">Loading categories…</div>
                                    </div>
                                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 small text-muted">
                                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 w-100">
                                            <div class="d-flex align-items-center gap-2">
                                                <label for="scGoToPage" class="mb-0">Go to page</label>
                                                <input type="number" id="scGoToPage" class="form-control" min="1" value="1" style="width:60px; padding:0 .5rem; height:28px; font-size:14px;">
                                                <button type="button" class="btn btn-sm btn-outline-primary" id="scGoToPageBtn">Go</button>
                                            </div>
                                            <span id="scPageInfo">—</span>
                                            <div class="">
                                                <button type="button" class="btn btn-sm btn-outline-secondary" id="scPrevPage" disabled>Prev</button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" id="scNextPage" disabled>Next</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-primary steve-btn gap-1 mt-2" id="scAddSelected">
                                    <i class="fas fa-plus me-1"></i> Add Selected to Dropdown
                                </button>
                            </div>

                            {{-- Selected list --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-muted text-uppercase">Selected (Order)</label>
                                <div id="scSelectedContainer" class="border rounded p-3 gap-1 d-flex flex-direction-col" style="min-height:160px;background:#fff;">
                                    @if(count($selected))
                                    @foreach(collect($selected)->unique('label')->values() as $item)
                                            <div class="sc-selected-item" data-label="{{ $item['label'] ?? '' }}" data-url="{{ $item['url'] ?? '' }}">
                                                <span class="sc-handle" title="Drag to reorder"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="5" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="9" cy="12" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="19" r="1"/></svg></span>
                                                <span class="sc-label">{{ $item['label'] ?? '' }}</span>
                                                <div class="sc-selected-actions">
                                                    <button type="button" class="sc-action-btn btn-move-up" title="Move up"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg></button>
                                                    <button type="button" class="sc-action-btn btn-move-down" title="Move down"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></button>
                                                    <button type="button" class="sc-action-btn btn-delete" title="Remove"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                                <small class="text-muted d-block mt-2" id="scEmptyHint">No categories selected yet. Use the list on the left to add some.</small>

                                <hr>
                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary steve-btn gap-1">
                                        <i class="fas fa-save me-1"></i> Save Categories
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .sc-checkbox { display: flex; align-items: center; padding: .25rem .5rem; border-radius: .25rem; cursor: pointer; }
    .sc-checkbox:hover { background: #eef1f4; }
    .sc-category-option { display: flex; flex-wrap: wrap; align-items: flex-start; gap: 0 .5rem; padding: .125rem .25rem; border-radius: .25rem; }
    .sc-category-option:hover { background: #eef1f4; }
    .sc-category-option .sc-checkbox { flex: 1; min-width: 0; align-items: flex-start; }
    .sc-category-option .sc-checkbox input { margin-top: .2rem; }
    .sc-category-context { width: 100%; margin-left: 1.75rem; font-size: 12px; line-height: 1.2; }
    .sc-selected-item { display: flex; align-items: center; gap: 8px; padding: .4rem .6rem; border: 1px solid #e9ecef; border-radius: .375rem; background: #f8f9fa; }
    .sc-handle { cursor: grab; display: inline-flex; align-items: center; color: #999; }
    .sc-handle svg { width: 16px; height: 16px; }
    .sc-label { flex: 1; min-width: 0; font-size: 0.875rem; }
    .sc-action-btn {
        --size: 36px;
        width: var(--size);
        height: var(--size);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        padding: 0;
        cursor: pointer;
        transition: .2s ease;
        border: 1px solid rgba(31,3,0,.15);
        background: rgba(31,3,0,.05);
        color: #1f0300;
        flex-shrink: 0;
    }
    .sc-action-btn svg { width: 16px; height: 16px; }
    .sc-action-btn.btn-move-up:hover,
    .sc-action-btn.btn-move-down:hover {
        background: #1f0300;
        border-color: #1f0300;
        color: #fff;
    }
    .sc-action-btn.btn-delete {
        color: #e62e04;
        border-color: rgba(230,46,4,.2);
        background: rgba(230,46,4,.08);
    }
    .sc-action-btn.btn-delete:hover {
        background: #e62e04;
        border-color: #e62e04;
        color: #fff;
    }
    .sc-selected-actions { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
</style>

<script>
(function() {
    var form = document.getElementById('searchCategoriesForm');
    var hiddenInput = document.getElementById('searchCategoriesInput');
    var selectedContainer = document.getElementById('scSelectedContainer');
    var emptyHint = document.getElementById('scEmptyHint');
    var filterInput = document.getElementById('searchCategoriesFilter');
    var filterBtn = document.getElementById('searchCategoriesFilterBtn');
    var results = document.getElementById('scCategoryResults');
    var prevBtn = document.getElementById('scPrevPage');
    var nextBtn = document.getElementById('scNextPage');
    var pageInfo = document.getElementById('scPageInfo');
    var goToPageInput = document.getElementById('scGoToPage');
    var goToPageBtn = document.getElementById('scGoToPageBtn');
    var lookupUrl = results ? results.getAttribute('data-lookup-url') : '';
    var currentPage = 1;
    var lastPage = 1;
    var searchTimer = null;
    var requestSeq = 0;

    function selectedItems() {
        return Array.prototype.map.call(selectedContainer.querySelectorAll('.sc-selected-item'), function(row) {
            return {
                label: row.getAttribute('data-label') || '',
                url: row.getAttribute('data-url') || ''
            };
        });
    }

    function syncHidden() {
        hiddenInput.value = JSON.stringify(selectedItems());
        var count = selectedContainer.querySelectorAll('.sc-selected-item').length;
        if (emptyHint) {
            emptyHint.style.display = count ? 'none' : 'block';
        }
    }

    function addItem(label, url) {
        var exists = Array.prototype.some.call(selectedContainer.querySelectorAll('.sc-selected-item'), function(row) {
            return row.getAttribute('data-label') === label;
        });
        if (exists) return;

        var row = document.createElement('div');
        row.className = 'sc-selected-item';
        row.setAttribute('data-label', label);
        row.setAttribute('data-url', url);
        row.innerHTML =
            '<span class="sc-handle" title="Drag to reorder"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="5" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="9" cy="12" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="19" r="1"/></svg></span>' +
            '<span class="sc-label"></span>' +
            '<div class="sc-selected-actions">' +
            '<button type="button" class="sc-action-btn btn-move-up" title="Move up"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg></button>' +
            '<button type="button" class="sc-action-btn btn-move-down" title="Move down"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></button>' +
            '<button type="button" class="sc-action-btn btn-delete" title="Remove"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>' +
            '</div>';
        row.querySelector('.sc-label').textContent = label;
        selectedContainer.appendChild(row);
        syncHidden();
    }

    document.getElementById('scAddSelected').addEventListener('click', function() {
        document.querySelectorAll('.sc-category-cb:checked').forEach(function(cb) {
            addItem(cb.getAttribute('data-label'), cb.getAttribute('data-url'));
            cb.checked = false;
        });
    });

    selectedContainer.addEventListener('click', function(e) {
        var target = e.target.closest('button');
        if (!target) return;
        var row = target.closest('.sc-selected-item');
        if (!row) return;

        if (target.classList.contains('btn-delete')) {
            row.remove();
        } else if (target.classList.contains('btn-move-up')) {
            var prev = row.previousElementSibling;
            if (prev) selectedContainer.insertBefore(row, prev);
        } else if (target.classList.contains('btn-move-down')) {
            var next = row.nextElementSibling;
            if (next) selectedContainer.insertBefore(next, row);
        }
        syncHidden();
    });

    function selectedLabels() {
        return Array.prototype.map.call(
            selectedContainer.querySelectorAll('.sc-selected-item'),
            function (row) { return row.getAttribute('data-label') || ''; }
        );
    }

    function renderCategories(payload) {
        if (!results) return;
        results.innerHTML = '';
        var labels = selectedLabels();

        if (!payload.data || !payload.data.length) {
            var empty = document.createElement('div');
            empty.className = 'text-muted p-3 text-center';
            empty.textContent = (filterInput && filterInput.value.trim())
                ? 'No categories matched your search.'
                : 'No categories found.';
            results.appendChild(empty);
        }

        payload.data.forEach(function (item) {
            var row = document.createElement('div');
            row.className = 'sc-category-option';

            var label = document.createElement('label');
            label.className = 'form-check-label sc-checkbox';

            var cb = document.createElement('input');
            cb.type = 'checkbox';
            cb.className = 'form-check-input me-2 sc-category-cb mt-0';
            cb.setAttribute('data-label', item.label);
            cb.setAttribute('data-url', item.url);
            cb.checked = labels.indexOf(item.label) !== -1;

            var text = document.createElement('span');
            text.textContent = item.label;
            label.appendChild(cb);
            label.appendChild(text);
            row.appendChild(label);

            if (item.context) {
                var context = document.createElement('small');
                context.className = 'text-muted sc-category-context';
                context.textContent = item.context;
                row.appendChild(context);
            }

            results.appendChild(row);
        });

        currentPage = payload.current_page || 1;
        lastPage = payload.last_page || 1;
        if (pageInfo) {
            pageInfo.textContent = 'Page ' + currentPage + ' of ' + lastPage + ' (' + (payload.total || 0) + ' Total)';
        }
        if (prevBtn) prevBtn.disabled = currentPage <= 1;
        if (nextBtn) nextBtn.disabled = currentPage >= lastPage;
        if (goToPageInput) {
            goToPageInput.max = String(lastPage);
            goToPageInput.value = String(currentPage);
        }
    }

    function loadCategories(page) {
        if (!results || !lookupUrl) return;
        var seq = ++requestSeq;
        results.innerHTML = '<div class="text-muted p-3 text-center">Loading categories…</div>';

        var url = lookupUrl + '?page=' + encodeURIComponent(page)
            + '&q=' + encodeURIComponent(filterInput ? filterInput.value.trim() : '');

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (response) { return response.json(); })
            .then(function (payload) {
                if (seq !== requestSeq) return;
                renderCategories(payload);
            })
            .catch(function () {
                if (seq !== requestSeq) return;
                results.innerHTML = '<div class="text-danger p-3 text-center">Could not load categories. <button type="button" class="btn btn-sm btn-outline-primary" id="scRetryLoad">Retry</button></div>';
                var retry = document.getElementById('scRetryLoad');
                if (retry) retry.addEventListener('click', function () { loadCategories(currentPage); });
            });
    }

    function setFilterBtnState() {
        if (!filterBtn) return;
        var hasValue = !!(filterInput && filterInput.value.trim() !== '');
        filterBtn.classList.toggle('btn-primary', !hasValue);
        filterBtn.classList.toggle('btn-outline-danger', hasValue);
        filterBtn.innerHTML = hasValue ? '<i class="fas fa-times"></i>' : '<i class="fas fa-search"></i>';
        var title = hasValue ? 'Clear search' : 'Search categories';
        filterBtn.setAttribute('title', title);
        if (window.bootstrap && window.bootstrap.Tooltip) {
            var tip = window.bootstrap.Tooltip.getInstance(filterBtn);
            if (tip && typeof tip.setContent === 'function') {
                tip.setContent({'.tooltip-inner': title});
            }
        }
    }

    if (filterBtn) {
        filterBtn.addEventListener('click', function () {
            if (filterInput && filterInput.value.trim() !== '') {
                filterInput.value = '';
                setFilterBtnState();
                loadCategories(1);
            } else if (filterInput) {
                filterInput.focus();
            }
        });
    }

    if (filterInput) {
        filterInput.addEventListener('input', function () {
            setFilterBtnState();
            window.clearTimeout(searchTimer);
            searchTimer = window.setTimeout(function () { loadCategories(1); }, 250);
        });
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', function () {
            if (currentPage > 1) loadCategories(currentPage - 1);
        });
    }
    if (nextBtn) {
        nextBtn.addEventListener('click', function () {
            if (currentPage < lastPage) loadCategories(currentPage + 1);
        });
    }

    function goToPage() {
        if (!goToPageInput) return;
        var page = parseInt(goToPageInput.value, 10);
        if (isNaN(page)) page = currentPage;
        page = Math.max(1, Math.min(lastPage || 1, page));
        goToPageInput.value = String(page);
        if (page !== currentPage) loadCategories(page);
    }

    if (goToPageBtn) {
        goToPageBtn.addEventListener('click', goToPage);
    }
    if (goToPageInput) {
        goToPageInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                goToPage();
            }
        });
    }

    loadCategories(1);

    syncHidden();
})();
</script>

@endsection