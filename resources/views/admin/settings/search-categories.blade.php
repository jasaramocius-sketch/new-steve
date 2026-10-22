@extends('admin.layouts.app')
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
                                <input type="text" id="searchCategoriesFilter" class="form-control mb-2" placeholder="Filter categories...">
                                <div class="border rounded p-3 available-categories-list" style="max-height:none;overflow-y:auto;background:#f8f9fa;height:100vh;">
                                    <ul class="list-unstyled mb-0">
                                        @forelse($categories as $cat)
                                            @include('admin.settings.search-categories-tree', ['category' => $cat, 'selectedLabels' => $selectedLabels])
                                        @empty
                                            <li class="text-muted">No active categories found. Create categories first.</li>
                                        @endforelse
                                    </ul>
                                    @if($orphanCategories->count())
                                    <div class="mt-3 fw-semibold small text-muted text-uppercase">Other Categories</div>
                                    <ul class="list-unstyled mb-0">
                                        @foreach($orphanCategories as $orphan)
                                        <li>
                                            <label class="form-check-label sc-checkbox">
                                                <input type="checkbox" class="form-check-input me-2 sc-category-cb"
                                                       data-label="{{ $orphan->name }}"
                                                       data-url="{{ route('category', $orphan->slug) }}"
                                                       @checked($selectedLabels->contains($orphan->name))>
                                                {{ $orphan->name }}
                                            </label>
                                        </li>
                                        @endforeach
                                    </ul>
                                    @endif
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

    if (filterInput) {
        filterInput.addEventListener('input', function() {
            var q = filterInput.value.toLowerCase();
            document.querySelectorAll('.sc-category-cb').forEach(function(cb) {
                var row = cb.closest('li');
                var parentLi = row ? row.parentElement?.closest('li') : null;
                var match = cb.getAttribute('data-label').toLowerCase().indexOf(q) !== -1;
                if (row && parentLi) {
                    row.style.display = match ? '' : 'none';
                    var anyChildVisible = Array.prototype.some.call(parentLi.querySelectorAll(':scope > ul li'), function(li) {
                        return li.style.display !== 'none';
                    });
                    if (parentLi.querySelector(':scope > ul')) {
                        parentLi.style.display = (anyChildVisible || (match && parentLi === row)) ? '' : 'none';
                    }
                } else if (row) {
                    row.style.display = match ? '' : 'none';
                }
            });
        });
    }

    syncHidden();
})();
</script>

@endsection