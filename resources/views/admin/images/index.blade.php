@extends('admin.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-images-index-page', 'pageClass' => 'admin-images-index-page'])
@section('page-title', 'Image Manager')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Image Manager - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')
<style>
    .image-card { cursor: pointer; transition: border-color 0.15s, box-shadow 0.15s, opacity 0.2s ease; position: relative; }
    .image-card:hover { box-shadow: 0 0.25rem 0.5rem rgba(0,0,0,0.08); }

    /* === BULK MODE: all cards dim === */
    body.img-bulk-mode .image-card { opacity: 0.55; }
    body.img-bulk-mode .image-card:hover { opacity: 0.8; }
    body.img-bulk-mode .image-card.selected { opacity: 1; border: 2px solid #0d6efd !important; box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.15); }
    body.img-bulk-mode .image-card .image-edit-link { pointer-events: none; }
    body.img-bulk-mode .image-card { cursor: pointer; }

    /* === GRID VIEW SELECT OVERLAY (original blue check-circle design) === */
    .select-overlay {
        display: none; position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(13,110,253,0.08); z-index: 7; align-items: center; justify-content: center;
    }
    .image-card.selected .select-overlay { display: flex; }
    .select-overlay .check-circle {
        width: 36px; height: 36px; background: #0d6efd;
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 2px 6px rgba(0,0,0,0.25); top: 0; position: absolute; right: 0;
    }
    .select-overlay .check-circle svg { width: 20px; height: 20px; }

    /* === LIST VIEW === */
    #images-container.list-view { display: flex; flex-direction: column; gap: 0; }
    #images-container.list-view .grid-item { width: 100%; }
    #images-container.list-view .image-card { display: flex; align-items: flex-start; border-radius: 0; border-left: none; border-right: none; border-top: none;flex-direction: row; }
    #images-container.list-view .image-card.selected { border-left: 2px solid #0d6efd !important; }
    #images-container.list-view .grid-item + .grid-item { border-top: 1px solid #dee2e6; }
    #images-container.list-view .image-card .thumb-wrap {
        width: 140px !important; height: 140px !important; overflow: hidden; background: #f8f9fa; display: flex; align-items: center; justify-content: center;
    }
    #images-container.list-view .image-card .thumb-wrap img { width: 100%; height: 100%; object-fit: cover; }
    #images-container.list-view .image-card .card-info { flex: 1; padding: 8px 16px; min-width: 0; display: flex; align-items: flex-start; gap: 10px;     flex-direction: column;}
    #images-container.list-view .image-card .card-info .info-name { min-width: 0; }
    #images-container.list-view .image-card .card-info .info-name .text-truncate { font-size: 13px; font-weight: 500; color: #212529; }
    #images-container.list-view .image-card .card-info .info-meta { font-size: 12px; color: #6c757d; white-space: nowrap; }
    #images-container.list-view .image-card .card-info .info-type { flex: 0 0 60px; }
    #images-container.list-view .image-card .card-info .info-usage { flex: 1; }
    #images-container.list-view .image-card .card-info .info-webp { flex: 0 0 70px; text-align: center; }
    #images-container.list-view .image-card .card-info .info-edit { flex: 0 0 40px; text-align: center; }
    #images-container.list-view .image-card .card-info .info-edit a { color: #0d6efd; font-size: 13px; }
    #images-container.list-view .image-card .card-badges { display: none; }
    #images-container.list-view .image-card .select-overlay { display: none !important; }
    .grid-view .image-card .list-bulk-check { display: none !important; }
    .grid-view .image-edit-link { flex-direction: column;}
    #images-container.list-view .image-card .list-bulk-check { align-items: center; justify-content: center; padding: 4px;}
    .list-view .start-0 {right: 0;left: unset !important;}
    .list-view .image-card .list-bulk-check { display: flex; }
    body.img-bulk-mode.list-view-active #images-container.list-view .image-card .card-info { padding-left: 8px; }
    #images-container.list-view .image-card .grid-view-only { display: none !important; }

    /* === LIST HEADER === */
    .list-header {
        display: none; align-items: center; padding: 8px 16px; background: #f8f9fa;
        border: 1px solid #dee2e6; border-radius: 6px; margin-bottom: 4px; font-size: 11px;
        font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #6c757d;
    }
    body.list-view-active .list-header { display: flex; }
    .list-header .lh-check { width: 38px; display: none; align-items: center; justify-content: center; }
    body.img-bulk-mode.list-view-active .list-header .lh-check { display: flex; }
    .list-header .lh-name { flex: 2; padding-left: 72px; }
    body.img-bulk-mode.list-view-active .list-header .lh-name { padding-left: 0; }
    .list-header .lh-meta { flex: 1; }
    .list-header .lh-type { flex: 0 0 60px; }
    .list-header .lh-usage { flex: 1; }
    .list-header .lh-webp { flex: 0 0 70px; text-align: center; }
    .list-header .lh-edit { flex: 0 0 40px; text-align: center; }
    .list-view-only { display: none; }
    body.list-view-active .list-view-only { display: flex !important; }
    body.list-view-active .grid-view-only { display: none !important; }

    /* === GRID VIEW === */
    #images-container.grid-view { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 16px; }
    #images-container.grid-view .grid-item { width: 100%; }

    /* === HEADER BULK ACTIONS === */
    .bulk-header-normal, .bulk-header-actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .bulk-header-actions { display: none !important; }
    body.img-bulk-mode .bulk-header-normal { display: none !important; }
    body.img-bulk-mode .bulk-header-actions { display: flex !important; }
    body.img-bulk-mode .view-toggle-wrap { display: none !important; }
    .selected-count { font-weight: 600; color: #0d6efd; }

    /* === BULK CHECK (shared) === */
    .bulk-check {
        width: 18px; height: 18px; border: 2px solid #adb5bd; border-radius: 3px;
        background: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center;
        transition: all 0.15s ease; flex-shrink: 0; position: relative;
    }
    .bulk-check:hover { border-color: #0d6efd; }
    body.img-bulk-mode .image-card.selected .bulk-check {
        background: #0d6efd; border-color: #0d6efd;
    }
    body.img-bulk-mode .image-card.selected .bulk-check::after {
        content: ''; position: absolute; width: 5px; height: 9px;
        border: solid white; border-width: 0 2px 2px 0; transform: rotate(45deg); margin-top: -2px;
    }
    .bulk-check.checked {
        background: #0d6efd; border-color: #0d6efd;
    }
    .bulk-check.checked::after {
        content: ''; position: absolute; width: 5px; height: 9px;
        border: solid white; border-width: 0 2px 2px 0; transform: rotate(45deg); margin-top: -2px;
    }
</style>

<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap flex-md-nowrap">
        <div></div>
        <button type="button" class="btn btn-primary steve-btn gap-1" data-bs-toggle="modal" data-bs-target="#uploadModal">
            <i class="fas fa-upload"></i> Upload Images
        </button>
    </div>

    @php $queryParams = array_filter(request()->only(['search', 'sort', 'order'])); @endphp
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <a href="{{ route('admin.images.index', $queryParams) }}" class="text-decoration-none image-manager-stats-card">
                <div class="card border-0 shadow-sm p-3 text-center">
                    <h5 class="fw-bold mb-1">{{ $stats['total'] }}</h5>
                    <small class="text-muted">Total Images</small>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('admin.images.index', ['filter' => 'attached'] + $queryParams) }}" class="text-decoration-none image-manager-stats-card">
                <div class="card border-0 shadow-sm p-3 text-center">
                    <h5 class="fw-bold mb-1">{{ $stats['attached'] }}</h5>
                    <small class="text-muted">Attached</small>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('admin.images.index', ['filter' => 'unused'] + $queryParams) }}" class="text-decoration-none image-manager-stats-card">
                <div class="card border-0 shadow-sm p-3 text-center admin-danger-card">
                    <h5 class="fw-bold mb-1 text-danger">{{ $stats['unused'] }}</h5>
                    <small class="text-danger">Unused</small>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('admin.images.index', ['filter' => 'convertible'] + $queryParams) }}" class="text-decoration-none image-manager-stats-card">
                <div class="card border-0 shadow-sm p-3 text-center">
                    <h5 class="fw-bold mb-1">{{ $stats['convertible'] }}</h5>
                    <small class="text-muted">Not Yet Converted</small>
                </div>
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-3 flex-wrap flex-md-nowrap">
        <div class="card-body d-flex d-sm-flex gap-1 col-sm-12 col-lg-12 flex-wrap align-items-end">
            <div class="col-lg-4 col-md-12 col-sm-12">
                    <label class="form-label small">Search</label>
                    @include('admin.partials.search-form', [
                        'route' => route('admin.images.index'),
                        'placeholder' => 'Search images...'
                    ])
                </div>
            <form method="GET" class="row align-items-end col-md-12 g-1 col-lg-8 col-sm-12">
                <div class="col-lg-3 col-md-3 col-sm-6">
                    <label class="form-label small">Filter</label>
                    <select name="filter" class="form-select" onchange="this.form.submit()">
                        <option value="">All</option>
                        <option value="attached" {{ request('filter')=='attached'?'selected':'' }}>Attached</option>
                        <option value="unused" {{ request('filter')=='unused'?'selected':'' }}>Unused</option>
                        <option value="convertible" {{ request('filter')=='convertible'?'selected':'' }}>Convertible</option>
                    </select>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6">
                    <label class="form-label small">Sort</label>
                    <select name="sort" class="form-select">
                        <option value="created_at" {{ request('sort')=='created_at'?'selected':'' }}>Date</option>
                        <option value="original_name" {{ request('sort')=='original_name'?'selected':'' }}>Name</option>
                        <option value="size" {{ request('sort')=='size'?'selected':'' }}>Size</option>
                    </select>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6">
                    <label class="form-label small">Order</label>
                    <select name="order" class="form-select">
                        <option value="desc" {{ request('order')=='desc'?'selected':'' }}>DESC</option>
                        <option value="asc" {{ request('order')=='asc'?'selected':'' }}>ASC</option>
                    </select>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6">
                    <button type="submit" class="btn btn-primary w-100 steve-btn gap-1"><i class="fas fa-search"></i> Filter</button>
                    @if(request()->hasAny(['search', 'filter', 'sort', 'order']))
                        <a href="{{ route('admin.images.index') }}" class="btn btn-outline-secondary w-100 mt-1 steve-btn gap-1"><i class="fas fa-times"></i> Clear Filters</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    @if($images->count())
    <form id="bulk-form" method="POST" action="">
        @csrf
        <div id="bulk-inputs"></div>
        <input type="hidden" name="_method" id="bulk-method" value="POST">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center admin-min-height-48">
                <div class="bulk-header-normal">
                    <button type="button" class="btn btn-outline-primary steve-btn" id="bulk-select-btn">
                        Bulk Select
                    </button>
                    <span>{{ $images->total() }} images found</span>
                    @if(request('filter') === 'convertible')
                        <button type="button" class="btn btn-success steve-btn gap-1" onclick="convertAllUnconverted('{{ route('admin.images.bulk-convert') }}')">
                            <i class="fas fa-bolt"></i> Convert All to WebP
                        </button>
                    @endif
                </div>
                <div class="bulk-header-actions">
                    <div class="lh-check"><div class="bulk-check" id="select-all-check"></div></div>
                    <span><span class="selected-count" id="selected-count">0</span> selected</span>
                    <button type="button" class="btn btn-success steve-btn gap-1" onclick="bulkAction('{{ route('admin.images.bulk-convert') }}')">
                        <i class="fas fa-exchange-alt"></i> Convert to WebP
                    </button>
                    <button type="button" class="btn btn-warning steve-btn gap-1" onclick="bulkAction('{{ route('admin.images.bulk-mark-unused') }}')">
                        <i class="fas fa-tag"></i> Mark Unused
                    </button>
                    <button type="button" class="btn btn-danger steve-btn gap-1" onclick="if(confirm('Delete selected images permanently? This will also delete the physical file.')) bulkAction('{{ route('admin.images.bulk-delete') }}')">
                        <i class="fas fa-trash"></i> Delete Permanently
                    </button>
                    <button type="button" class="btn btn-outline-secondary steve-btn gap-1" id="bulk-cancel-btn">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
                <div class="d-flex gap-1 view-toggle-wrap">
                    <button type="button" class="view-btn steve-btn active" id="view-grid-btn" data-view="grid" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Grid View">
                        <i class="fas fa-th-large"></i>
                    </button>
                    <button type="button" class="view-btn steve-btn" id="view-list-btn" data-view="list" data-bs-toggle="tooltip" data-bs-placement="bottom" title="List View">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-3">
                <!-- <div id="images-header" class="list-header">
                    <div class="lh-check"><div class="bulk-check" id="select-all-check"></div></div>
                    <div class="lh-name">Name</div>
                    <div class="lh-meta">Size</div>
                    <div class="lh-type">Type</div>
                    <div class="lh-usage">Usage</div>
                    <div class="lh-webp">WebP</div>
                    <div class="lh-edit"><i class="fas fa-pen"></i></div>
                </div> -->
                <div id="images-container" class="grid-view">
                    @include('admin.images._cards', ['images' => $images])
                </div>
            </div>
        </div>
    </form>

    <div class="mt-3 d-flex justify-content-center align-items-center flex-wrap">
        {{ $images->links() }}
    </div>
    @else
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5 text-muted">
            <i class="fas fa-image fa-3x mb-3 flex-wrap flex-md-nowrap"></i>
            <p>No images found.</p>
        </div>
    </div>
    @endif
</div>
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.images.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-upload me-2"></i>Upload Images</h5>
                    <button type="button" class="btn-close steve-btn" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 flex-wrap flex-md-nowrap">
                        <label class="form-label">Select Images <span class="text-danger">*</span></label>
                        <input type="file" name="images[]" class="form-control" multiple accept="image/*" required>
                        <div class="form-text">Allowed: jpeg, png, jpg, gif, svg, webp. Max 40MB per file.</div>
                    </div>
                    <div id="upload-preview" class="row g-2"></div>
                    <hr>
                    <div class="mb-2">
                        <label class="form-label">Or download from a URL</label>
                        <div class="d-flex gap-2">
                            <input type="url" id="image-url-input" class="form-control" placeholder="https://example.com/image.jpg" autocomplete="off">
                            <button type="button" id="image-url-btn" class="btn btn-outline-secondary text-nowrap">
                                <i class="fas fa-download me-1"></i> Download URL
                            </button>
                        </div>
                        <div id="image-url-error" class="text-danger small mt-1"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary steve-btn gap-1" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary steve-btn gap-1"><i class="fas fa-upload"></i> Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    let bulkMode = false;
    const selectedIds = new Set();
    const container = document.getElementById('images-container');
    const header = document.getElementById('images-header');
    const gridBtn = document.getElementById('view-grid-btn');
    const listBtn = document.getElementById('view-list-btn');
    const bulkForm = document.getElementById('bulk-form');
    const bulkInputs = document.getElementById('bulk-inputs');
    const countEl = document.getElementById('selected-count');

    function isListView() {
        return container && container.classList.contains('list-view');
    }

    function setView(mode) {
        localStorage.setItem('image-manager-view', mode);
        if (mode === 'list') {
            container.classList.remove('grid-view');
            container.classList.add('list-view');
            document.body.classList.add('list-view-active');
            listBtn.classList.add('active');
            gridBtn.classList.remove('active');
            if (header) header.style.display = 'flex';
        } else {
            container.classList.remove('list-view');
            container.classList.add('grid-view');
            document.body.classList.remove('list-view-active');
            gridBtn.classList.add('active');
            listBtn.classList.remove('active');
            if (header) header.style.display = 'none';
        }
    }

    function enterBulkMode() {
        bulkMode = true;
        document.body.classList.add('img-bulk-mode');
    }

    function exitBulkMode() {
        bulkMode = false;
        document.body.classList.remove('img-bulk-mode');
        selectedIds.clear();
        document.querySelectorAll('.image-card.selected').forEach(function(c) { c.classList.remove('selected'); });
        if (selectAllCheck) selectAllCheck.classList.remove('checked');
        syncInputs();
        updateCount();
    }

    function toggleSelect(id) {
        var card = container.querySelector('.image-card[data-id="' + id + '"]');
        if (!card) return;
        if (selectedIds.has(id)) {
            selectedIds.delete(id);
            card.classList.remove('selected');
        } else {
            selectedIds.add(id);
            card.classList.add('selected');
        }
        syncInputs();
        updateCount();
        syncSelectAllCheck();
    }

    function syncSelectAllCheck() {
        if (!selectAllCheck) return;
        var total = document.querySelectorAll('.image-card').length;
        if (selectedIds.size === total && total > 0) {
            selectAllCheck.classList.add('checked');
        } else {
            selectAllCheck.classList.remove('checked');
        }
    }

    function selectAll() {
        var allSelected = true;
        document.querySelectorAll('.image-card').forEach(function(card) {
            var id = parseInt(card.dataset.id);
            if (!selectedIds.has(id)) allSelected = false;
        });
        document.querySelectorAll('.image-card').forEach(function(card) {
            var id = parseInt(card.dataset.id);
            if (allSelected) {
                selectedIds.delete(id);
                card.classList.remove('selected');
            } else {
                selectedIds.add(id);
                card.classList.add('selected');
            }
        });
        syncInputs();
        updateCount();
        syncSelectAllCheck();
    }

    function updateCount() {
        if (countEl) countEl.textContent = selectedIds.size;
    }

    function syncInputs() {
        bulkInputs.innerHTML = '';
        selectedIds.forEach(function(id) {
            var inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = 'ids[]';
            inp.value = id;
            bulkInputs.appendChild(inp);
        });
    }

    function bulkAction(url) {
        if (selectedIds.size === 0) { alert('Please select at least one image.'); return; }
        bulkForm.action = url;
        bulkForm.submit();
    }

    document.getElementById('bulk-select-btn').addEventListener('click', enterBulkMode);
    document.getElementById('bulk-cancel-btn').addEventListener('click', exitBulkMode);

    gridBtn.addEventListener('click', function() { setView('grid'); });
    listBtn.addEventListener('click', function() { setView('list'); });

    var selectAllCheck = document.getElementById('select-all-check');
    if (selectAllCheck) {
        selectAllCheck.addEventListener('click', selectAll);
    }

    container.addEventListener('click', function(e) {
        var listCheck = e.target.closest('.list-bulk-check');
        if (listCheck) {
            e.preventDefault();
            e.stopPropagation();
            var card = listCheck.closest('.image-card');
            if (!card) return;
            var id = parseInt(card.dataset.id);
            if (!bulkMode) enterBulkMode();
            toggleSelect(id);
            return;
        }

        var card = e.target.closest('.image-card');
        if (!card) return;
        var id = parseInt(card.dataset.id);

        if (!bulkMode) {
            var link = card.querySelector('.image-edit-link');
            if (link) window.location.href = link.href;
            return;
        }

        toggleSelect(id);
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && bulkMode) exitBulkMode();
    });

    document.querySelector('input[name="images[]"]')?.addEventListener('change', function() {
        var preview = document.getElementById('upload-preview');
        preview.innerHTML = '';
        Array.from(this.files).forEach(function(file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var col = document.createElement('div');
                col.className = 'col-4 col-md-3';
                col.innerHTML = '<div class="card border p-1"><img src="' + e.target.result + '" class="img-fluid rounded" style="height:80px;width:100%;object-fit:cover;"></div>';
                preview.appendChild(col);
            };
            reader.readAsDataURL(file);
        });
    });

    window.bulkAction = bulkAction;
    window.convertAllUnconverted = function(url) {
        if (!confirm('Convert all unconverted images on this page to WebP?')) return;
        document.querySelectorAll('.image-card').forEach(function(card) {
            var id = parseInt(card.dataset.id);
            selectedIds.add(id);
            card.classList.add('selected');
        });
        syncInputs();
        updateCount();
        bulkForm.action = url;
        bulkForm.submit();
    };

    var savedView = localStorage.getItem('image-manager-view') || 'grid';
    setView(savedView);
})();
</script>
@endpush
