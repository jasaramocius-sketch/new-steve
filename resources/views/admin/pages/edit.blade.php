@extends('admin.layouts.app')
{{-- Add your custom page ID and classes right here --}}
@include('partials.page-attributes', ['pageId' => 'admin-pages-edit-page', 'pageClass' => 'admin-pages-edit-page'])
@section('page-title', 'Edit Page')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Edit Page - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')

<!--
.page-builder-nav {
    width: 180px;
    flex-shrink: 0;
    position: sticky;
    top: 16px;
}
.nav-link-section {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 10px;
    border-radius: 6px;
    font-size: 0.85rem;
    color: #6b7280 !important;
    text-decoration: none !important;
}
.nav-link-section i { width: 16px; text-align: center; }
.nav-link-section.active,
.nav-link-section:hover {
    background: rgba(52, 84, 209, 0.08);
    color: #1c1e26 !important;
}
.min-width-0 { min-width: 0; }

.tab-pane { display: none; }
.tab-pane.active-pane { display: block; }

.status-pill {
    border: none;
    font-size: 0.75rem;
    padding: 6px 14px;
    border-radius: 20px;
    background: var(--primary);
    color: #fff;
    white-space: nowrap;
    cursor: pointer;
}
.status-pill.is-active {
    background: #028d5c;
    color: #ffffff;
}

.banner-dropzone { position: relative; }
.banner-dropzone .dz-placeholder {
    display: block;
    border: 1.5px dashed #cfcfca;
    border-radius: 8px;
    padding: 28px;
    text-align: center;
    color: #9a9992;
    font-size: 0.85rem;
}

.serp-preview {
    background: #f7f7f5;
    border-radius: 8px;
    padding: 14px 16px;
    font-family: arial, sans-serif;
}
.serp-url { font-size: 13px; color: #4d5156; }
.serp-title { font-size: 18px; color: #1a0dab; line-height: 1.3; margin: 2px 0; }
.serp-desc { font-size: 13px; color: #4d5156; line-height: 1.4; }
-->

<form action="{{ route('admin.pages.update', $page->id) }}" method="POST" id="pageForm">
    @csrf
    @method('PUT')

    <div class="d-flex gap-4 align-items-start page-builder admin-page-builder admin-page-builder-edit">

        {{-- Section nav --}}
        <div class="page-builder-nav">
            <div class="text-muted small mb-2"><a href="{{ route('admin.pages.index') }}">Pages</a> / Edit</div>
            <nav class="nav flex-column gap-1">
                <a href="#s-details" class="nav-link-section active" data-target="s-details"><i class="fas fa-file-alt"></i> Details</a>
                <a href="#s-banner" class="nav-link-section" data-target="s-banner"><i class="fas fa-image"></i> Banner</a>
                <a href="#s-seo" class="nav-link-section" data-target="s-seo"><i class="fas fa-search"></i> SEO</a>
                <a href="#s-content" class="nav-link-section" data-target="s-content"><i class="fas fa-edit"></i> Content</a>
            </nav>
        </div>

        <div class="flex-grow-1 min-width-0">

            {{-- Header bar: title + status + actions --}}
            <div class="d-flex align-items-center gap-2 pb-3 mb-4 border-bottom page-header-bar flex-wrap">
                <div class="page-header-bar-first d-flex gap-2 align-items-center flex-grow-1 min-width-0">
                <div class="w-auto flex-grow-1">
                <input type="text" name="title" id="titleInput" class="page-title-input form-control form-control-lg border-0 fw-semibold flex-grow-1 @error('title') is-invalid @enderror"
                       placeholder="Untitled page" value="{{ old('title', $page->title) }}" maxlength="255" required>
                @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="w-auto d-flex align-items-center gap-2 justify-content-end">
                <button type="button" id="statusPill" class="status-pill {{ old('status', $page->status) ? 'is-active' : '' }}" data-active="{{ old('status', $page->status) ? '1' : '0' }}" style="height: 40px; font-size: 1rem; line-height: 1.2;">
                    {{ old('status', $page->status) ? 'Active' : 'Inactive' }}
                </button>
                <input type="hidden" name="status" id="statusInput" value="{{ old('status', $page->status) ? 1 : 0 }}">

                <button type="submit" class="btn btn-primary steve-btn text-nowrap"><i class="fas fa-save me-1"></i> Update</button>
                <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary text-nowrap steve-btn gap-1"><i class="fas fa-times me-1"></i> Cancel</a>
                </div>
                </div>
                <div class="w-100">
                    <a href="{{ route('page.show', $page->slug) }}" target="_blank" id="viewPageLink" class="a-tag-text-hover text-nowrap">{{ route('page.show', $page->slug) }}</a>
                </div>
                <span class="small text-muted">Last updated: {{ optional($page->updated_at)->format('M d, Y h:i A') }}</span>
            </div>

            {{-- Page Details --}}
            <section id="s-details" class="card border-0 shadow-sm mb-3 tab-pane">
                <div class="card-body">
                    <h5 class="mb-3">Page details</h5>

                    <label class="form-label">Short description</label>
                    <input type="text" name="short_description" class="form-control mb-3 @error('short_description') is-invalid @enderror"
                           placeholder="One line summary shown in listings" value="{{ old('short_description', $page->short_description) }}" maxlength="255">
                    @error('short_description') <div class="invalid-feedback">{{ $message }}</div> @enderror

                    <label class="form-label mb-1">Slug</label>
                    <input type="text" name="slug" id="slugInput" class="form-control mb-1 @error('slug') is-invalid @enderror"
                           placeholder="page-url-slug" value="{{ old('slug', $page->slug) }}" maxlength="255">
                    @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="form-text mb-3">
                        <a href="{{ route('page.show', $page->slug) }}" target="_blank" id="slugLink" class="a-tag-text-hover text-nowrap"
                           data-base="{{ str_replace($page->slug, '', route('page.show', $page->slug)) }}">{{ route('page.show', $page->slug) }}</a>
                    </div>

                    <div class="form-check form-switch">
                        <input type="checkbox" name="show_title" value="1" class="form-check-input" id="show_title" {{ old('show_title', $page->show_title) ? 'checked' : '' }}>
                        <label class="form-check-label" for="show_title">Show page title on the front end</label>
                    </div>
                </div>
            </section>

            {{-- Title Banner Image --}}
            <section id="s-banner" class="card border-0 shadow-sm mb-3 tab-pane">
                <div class="card-body">
                    <h5 class="mb-1">Title banner image</h5>
                    <div class="banner-dropzone">
                        @include('admin.pages._banner_image')
                    </div>
                </div>
            </section>

            {{-- SEO --}}
            <section id="s-seo" class="card border-0 shadow-sm mb-3 tab-pane">
                <div class="card-body">
                    <h5 class="mb-1">Search appearance</h5>
                    <p class="text-muted small mb-3">Control how this page looks in search results.</p>

                    {{-- Live SERP preview --}}
                    <div class="serp-preview mb-4">
                        <div class="text-muted small mb-1">Preview</div>
                        <div class="serp-url">yoursite.com &rsaquo; pages</div>
                        <div class="serp-title" id="serpTitle">{{ old('meta_title', $page->meta_title) ?: (old('title', $page->title) ?: 'Untitled page') }}</div>
                        <div class="serp-desc" id="serpDesc">{{ old('meta_description', $page->meta_description) ?: 'Add a meta description to control this snippet.' }}</div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Meta title</label>
                            <input type="text" name="meta_title" id="meta_title" class="form-control @error('meta_title') is-invalid @enderror" value="{{ old('meta_title', $page->meta_title) }}" maxlength="60">
                            @error('meta_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text text-end mb-0">Recommended 60 characters. <span id="meta_title_count" class="fw-semibold font-monospace">{{ mb_strlen(old('meta_title', $page->meta_title) ?? '') }}</span>/60</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Meta description</label>
                            <textarea name="meta_description" id="meta_description" class="form-control @error('meta_description') is-invalid @enderror" rows="2" maxlength="500">{{ old('meta_description', $page->meta_description) }}</textarea>
                            @error('meta_description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text text-end mb-0">Recommended 160 characters. <span id="meta_description_count" class="fw-semibold font-monospace">{{ mb_strlen(old('meta_description', $page->meta_description) ?? '') }}</span>/500</div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Content --}}
            <section id="s-content" class="card border-0 shadow-sm mb-3 tab-pane">
                <div class="card-body">
                    <h5 class="mb-3">Content</h5>
                    <textarea name="content" class="form-control texteditor @error('content') is-invalid @enderror" rows="12">{{ old('content', $page->content) }}</textarea>
                    @error('content') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </section>

        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
(function() {
    function bindCharCount(inputId, countId) {
        var input = document.getElementById(inputId);
        var count = document.getElementById(countId);
        if (!input || !count) return;
        var update = function() {
            count.textContent = input.value.length;
        };
        input.addEventListener('input', update);
        update();
    }
    bindCharCount('meta_title', 'meta_title_count');
    bindCharCount('meta_description', 'meta_description_count');

    // Live SERP preview
    var titleInput = document.getElementById('titleInput');
    var metaTitle = document.getElementById('meta_title');
    var metaDesc = document.getElementById('meta_description');
    var serpTitle = document.getElementById('serpTitle');
    var serpDesc = document.getElementById('serpDesc');

    function refreshSerpTitle() {
        serpTitle.textContent = metaTitle.value || titleInput.value || 'Untitled page';
    }
    titleInput.addEventListener('input', refreshSerpTitle);
    metaTitle.addEventListener('input', refreshSerpTitle);
    metaDesc.addEventListener('input', function() {
        serpDesc.textContent = metaDesc.value || 'Add a meta description to control this snippet.';
    });

    // Slug: auto-generate from the title until the user edits it manually
    var slugInput = document.getElementById('slugInput');
    var slugLink = document.getElementById('slugLink');
    var viewPageLink = document.getElementById('viewPageLink');
    var slugBase = slugLink ? slugLink.getAttribute('data-base') : '';
    var slugFromTitle = true;

    function slugify(str) {
        return (str || '').toLowerCase().trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/[\s_]+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    function refreshSlugLink() {
        var value = (slugInput.value || '').trim() || '{{ $page->slug }}';
        if (slugLink) {
            slugLink.href = slugBase + value;
            slugLink.textContent = slugLink.href;
        }
        if (viewPageLink) {
            viewPageLink.href = slugBase + value;
            viewPageLink.textContent = viewPageLink.href;
        }
    }

    if (slugInput) {
        slugFromTitle = slugInput.value === '';
        slugInput.addEventListener('input', function() {
            slugFromTitle = slugInput.value === '';
            refreshSlugLink();
        });
        titleInput.addEventListener('input', function() {
            if (slugFromTitle) {
                slugInput.value = slugify(titleInput.value);
                refreshSlugLink();
            }
        });
        refreshSlugLink();
    }

    // Status pill toggle
    var pill = document.getElementById('statusPill');
    var statusInput = document.getElementById('statusInput');
    pill.addEventListener('click', function() {
        var active = statusInput.value === '1';
        active = !active;
        statusInput.value = active ? '1' : '0';
        pill.textContent = active ? 'Active' : 'Inactive';
        pill.classList.toggle('is-active', active);
    });

    // Side tab switching: show only the selected section's pane
    var navLinks = document.querySelectorAll('.nav-link-section');
    var panes = document.querySelectorAll('.tab-pane');

    function showTab(targetId) {
        panes.forEach(function(pane) {
            pane.classList.toggle('active-pane', pane.id === targetId);
        });
        navLinks.forEach(function(l) {
            l.classList.toggle('active', l.dataset.target === targetId);
        });
    }

    navLinks.forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            showTab(link.dataset.target);
        });
    });

    // Start on the first tab, unless a validation error is hiding in another tab
    var errorPane = document.querySelector('.tab-pane .is-invalid');
    var startTarget = errorPane ? errorPane.closest('.tab-pane').id : navLinks[0].dataset.target;
    showTab(startTarget);
})();
</script>
@endpush