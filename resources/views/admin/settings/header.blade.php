@extends('admin.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-settings-header-page', 'pageClass' => 'admin-settings-header-page'])
@section('page-title', 'General Settings')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'General Settings - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')
<div class="container-fluid admin-settings-header">
    <ul class="nav nav-tabs" id="settingsConfigurationTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ session('settings_tab') === 'footer' ? '' : 'active' }}" id="header-configuration-tab" data-bs-toggle="tab" data-bs-target="#header-configuration" type="button" role="tab" aria-controls="header-configuration" aria-selected="{{ session('settings_tab') === 'footer' ? 'false' : 'true' }}">Header Configuration</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ session('settings_tab') === 'footer' ? 'active' : '' }}" id="footer-configuration-tab" data-bs-toggle="tab" data-bs-target="#footer-configuration" type="button" role="tab" aria-controls="footer-configuration" aria-selected="{{ session('settings_tab') === 'footer' ? 'true' : 'false' }}">Footer Configuration</button>
        </li>
    </ul>
    <div class="tab-content pt-3" id="settingsConfigurationTabContent">
        <div class="tab-pane fade {{ session('settings_tab') === 'footer' ? '' : 'show active' }}" id="header-configuration" role="tabpanel" aria-labelledby="header-configuration-tab">
            <div class="row">
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Header Configuration</h5>
                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#headerConfigurationCollapse" aria-expanded="true" aria-controls="headerConfigurationCollapse" aria-label="Collapse or expand Header Configuration">
                                <i class="fas fa-chevron-up" aria-hidden="true"></i>
                            </button>
                        </div>
                <div class="collapse show" id="headerConfigurationCollapse">
                    <div class="card-body">
                    <form action="{{ route('admin.settings.header.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row header-settings-row">
                        <div class="header-settings-col col-lg-6 border-right-lg border-none-sm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label"><strong>Header Logo</strong></label>
                                        <div class="mb-2">
                                        <img src="{{ storedImageUrl($settings['header_logo'] ?? 'BwSkuSZ7ZYGWPc4Zk3CfeFzcn49dHpx3143n4WKS.png', 'assets/images') }}"
                                                alt="Header Logo" class="admin-logo-preview">
                                        </div>
                                        <input type="hidden" name="image_from_manager_header_logo" id="image_from_manager_header_logo">
                                        <div id="impPreview_header_logo" class="d-none mt-2"></div>
                                        <button type="button" class="btn btn-outline-primary" onclick="impOpen_header_logo()">
                                            <i class="fas fa-images me-1"></i> Browse Image Manager
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label"><strong>Favicon</strong></label>
                                        <div class="mb-2">
                                            <img src="{{ storedImageUrl($settings['header_favicon'] ?? '1730880696Fabpng.png', 'assets/images') }}"
                                                alt="Favicon" class="admin-favicon-preview">
                                        </div>
                                        <input type="hidden" name="image_from_manager_header_favicon" id="image_from_manager_header_favicon">
                                        <div id="impPreview_header_favicon" class="d-none mt-2"></div>
                                        <button type="button" class="btn btn-outline-primary" onclick="impOpen_header_favicon()">
                                            <i class="fas fa-images me-1"></i> Browse Image Manager
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label"><strong>Mobile Menu Logo</strong></label>
                                        <div class="mb-2">
                                            <img src="{{ storedImageUrl($settings['mobile_logo'] ?? '1730281141Whitepng.png', 'assets/images') }}"
                                                alt="Mobile Logo" class="admin-logo-preview admin-logo-preview-small">
                                        </div>
                                        <input type="hidden" name="image_from_manager_mobile_logo" id="image_from_manager_mobile_logo">
                                        <div id="impPreview_mobile_logo" class="d-none mt-2"></div>
                                        <button type="button" class="btn btn-outline-primary" onclick="impOpen_mobile_logo()">
                                            <i class="fas fa-images me-1"></i> Browse Image Manager
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label"><strong>Footer Logo</strong></label>
                                        <div class="mb-2">
                                            <img src="{{ storedImageUrl($settings['footer_logo'] ?? '1730281141Whitepng.png', 'assets/images') }}"
                                                alt="Footer Logo" class="admin-logo-preview admin-logo-preview-small">
                                        </div>
                                        <input type="hidden" name="image_from_manager_footer_logo" id="image_from_manager_footer_logo">
                                        <div id="impPreview_footer_logo" class="d-none mt-2"></div>
                                        <button type="button" class="btn btn-outline-primary" onclick="impOpen_footer_logo()">
                                            <i class="fas fa-images me-1"></i> Browse Image Manager
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label"><strong>Admin Header Background Image</strong></label>
                                        <small class="text-muted d-block mb-2">Background image of the admin top bar (the container that holds the page title).</small>
                                        @if (!empty($settings['admin_header_bg']))
                                            <div class="mb-2 admin-header-bg-current">
                                                <img src="{{ storedImageUrl($settings['admin_header_bg'], 'assets/images') }}" alt="Admin Header Background"
                                                    class="admin-bg-preview">
                                            </div>
                                        @endif
                                        <input type="hidden" name="image_from_manager_admin_header_bg" id="image_from_manager_admin_header_bg">
                                        <input type="hidden" name="remove_admin_header_bg" id="remove_admin_header_bg" value="0">
                                        <div id="impPreview_admin_header_bg" class="d-none mt-2"></div>
                                        <div class="d-flex gap-2 mt-1">
                                            <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('remove_admin_header_bg').value='0'; impOpen_admin_header_bg()">
                                                <i class="fas fa-images me-1"></i> Browse Image Manager
                                            </button>
                                            <button type="button" id="clear_btn_admin_header_bg" data-preview="impPreview_admin_header_bg" data-current=".admin-header-bg-current" class="btn btn-sm btn-outline-danger {{ !empty($settings['admin_header_bg']) ? '' : 'd-none' }}" onclick="clearPickerImage('image_from_manager_admin_header_bg','impPreview_admin_header_bg','.admin-header-bg-current','remove_admin_header_bg')">
                                                <i class="fas fa-times me-1"></i> Clear Image
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="header-settings-col col-lg-6">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label"><strong>Phone Number</strong></label>
                                        <input type="text" name="header_phone" class="form-control"
                                            value="{{ $settings['header_phone'] ?? '+1 (234) 567-8901' }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label"><strong>Support Text</strong></label>
                                        <input type="text" name="header_support_text" class="form-control"
                                            value="{{ $settings['header_support_text'] ?? 'Contact & Support: 00 000 000 000' }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label"><strong>Email Address</strong></label>
                                        <input type="email" name="header_email" class="form-control"
                                            value="{{ $settings['header_email'] ?? 'admin@geniusocean.com' }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label"><strong>Address</strong></label>
                                        <input type="text" name="header_address" class="form-control"
                                            value="{{ $settings['header_address'] ?? '3584 Hickory Heights Drive , USA' }}">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label"><strong>Footer Copyright Text</strong></label>
                                <input type="text" name="footer_copyright" class="form-control"
                                    value="{{ $settings['footer_copyright'] ?? 'COPYRIGHT &copy; :year. All Rights Reserved By STautoparts' }}">
                            </div>
                        </div>
                        </div>

                            <hr>
                            <div class="form-group mb-3">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <label class="form-label fw-bold mb-0">Serve WebP Images on Frontend</label>
                                        <small class="text-muted d-block">When enabled, browsers that support WebP will automatically receive optimized WebP images instead of JPG/PNG. Uses &lt;picture&gt; tag with fallback for older browsers.</small>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="webp_frontend" value="1" id="webpFrontendToggle"
                                            {{ ($settings['webp_frontend'] ?? '1') === '1' ? 'checked' : '' }}>
                                    </div>
                                </div>
                            </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary steve-btn gap-1">
                                <i class="fas fa-save"></i> Save
                            </button>
                        </div>
                    </form>
                    </div>
                </div>
            </div>
        </div>
            </div>

    {{-- ============================================================ --}}
    {{-- NAVIGATION MENU — Filament Menu Manager Ditto Copy           --}}
    {{-- ============================================================ --}}
    <style>
        :root {
            --fmm-bg: #ffffff;
            --fmm-surface: #f4f6f9;
            --fmm-border: #e9ecef;
            --fmm-text: #1f0300;
            --fmm-muted: #6c757d;
            --fmm-accent: #d02a03;
            --fmm-accent-hover: #af2405;
            --fmm-success: #85b567;
            --fmm-danger: #dc3545;
            --fmm-warning: #f3af3d;
            --fmm-handle-bg: #f4f6f9;
            --fmm-badge-bg: #d02a0315;
            --fmm-badge-text: #d02a03;
            --fmm-secondary: #2d4776;
            --fmm-radius: 0;
            --fmm-radius-sm: 4px;
            --fmm-shadow: 0 .125rem 6rem rgba(0,0,0,.075);
        }
        .fmm-wrapper { display: flex; gap: 1.5rem; align-items: flex-start; }
        .fmm-builder { flex: 1; min-width: 0; }
        .fmm-panel { width: 300px; flex-shrink: 0; position: sticky; top: 80px; }

        .fmm-card {
            background: var(--fmm-bg);
            border: none;
            border-radius: var(--fmm-radius);
            box-shadow: var(--fmm-shadow);
        }
        .fmm-card-header {
            padding: 0.875rem 1rem;
            border-bottom: 1px solid var(--fmm-border);
            font-weight: 700;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: var(--fmm-text);
            background: var(--fmm-bg);
        }
        .fmm-auto-save-badge {
            font-size: 0.7rem;
            font-weight: 500;
            color: var(--fmm-muted);
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .fmm-auto-save-badge::before {
            content: '';
            display: inline-block;
            width: 7px; height: 7px;
            background: var(--fmm-success);
            border-radius: 50%;
            vertical-align: middle;
        }
        .fmm-card-body { padding: 0.75rem; }

        /* Empty state */
        .fmm-empty {
            text-align: center;
            padding: 2.5rem 1rem;
            color: var(--fmm-muted);
            font-size: 0.875rem;
        }
        .fmm-empty p { margin: 0.25rem 0; font-size: 0.8125rem; }

        /* Menu item row */
        .fmm-item-row { margin-bottom: 0.375rem; }
        .fmm-item-card {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.625rem;
            border: 1px solid var(--fmm-border);
            border-radius: var(--fmm-radius-sm);
            background: var(--fmm-bg);
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .fmm-item-card:hover { border-color: #d02a0330; }
        .fmm-item-card.disabled-item { opacity: 0.5; }
        .fmm-item-card.fmm-editing {
            border-color: var(--fmm-accent);
            box-shadow: 0 0 0 2px rgba(208,42,3,0.1);
        }

        /* Drag handle */
        .fmm-drag-handle {
            cursor: grab;
            color: var(--fmm-muted);
            display: flex;
            align-items: center;
            flex-shrink: 0;
            padding: 0.25rem;
            border-radius: var(--fmm-radius-sm);
            transition: color 0.2s, background 0.2s;
        }
        .fmm-drag-handle:hover { color: var(--fmm-accent); background: var(--fmm-handle-bg); }
        .fmm-drag-handle:active { cursor: grabbing; }

        /* Item body */
        .fmm-item-body { flex: 1; min-width: 0; }
        .fmm-item-title-row { display: flex; align-items: center; gap: 0.5rem; }
        .fmm-item-title { font-size: 0.8125rem; font-weight: 600; color: var(--fmm-text); }
        .fmm-item-url {
            display: block;
            font-size: 0.7rem;
            color: var(--fmm-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-top: 0.125rem;
        }
        .fmm-item-badges { display: flex; gap: 0.375rem; align-items: center; flex-wrap: wrap; }
        .fmm-item-badge {
            display: inline-flex;
            align-items: center;
            font-size: 0.625rem;
            font-weight: 600;
            padding: 0.15rem 0.5rem;
            border-radius: 50px;
            background: var(--fmm-badge-bg);
            color: var(--fmm-badge-text);
            text-transform: uppercase;
            letter-spacing: 0.03em;
            line-height: 1;
        }
        .fmm-item-badge.hidden-badge {
            background: #dc354515;
            color: var(--fmm-danger);
        }

        /* Unavailable item warning */
        .fmm-item-card.fmm-unavailable-item {
            border-color: #f3af3d80;
            background: #f3af3d08;
        }
        .fmm-item-warning {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            margin-top: 0.35rem;
            padding: 0.3rem 0.5rem;
            font-size: 0.7rem;
            font-weight: 600;
            color: #8a4b00;
            background: #f3af3d1a;
            border: 1px solid #f3af3d55;
            border-left: 3px solid var(--fmm-warning);
            border-radius: var(--fmm-radius-sm);
            line-height: 1.3;
        }
        .fmm-item-warning i {
            font-size: 0.75rem;
            flex-shrink: 0;
        }

        /* Action buttons — matches admin table action-btn style */
        .fmm-item-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }
        .fmm-action-btn {
            --size: 36px;
            width: var(--size);
            height: var(--size);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            padding: 0;
            font-size: 0;
            cursor: pointer;
            transition: .2s ease;
            border: 1px solid rgba(31,3,0,.15);
            background: rgba(31,3,0,.05);
            color: #1f0300;
        }
        .fmm-action-btn svg {
            width: 16px;
            height: 16px;
        }
        .fmm-action-btn.btn-move-up:hover,
        .fmm-action-btn.btn-move-down:hover,
        .fmm-action-btn.btn-nest-right:hover,
        .fmm-action-btn.btn-nest-left:hover {
            background: #1f0300;
            border-color: #1f0300;
            color: #fff;
        }
        .fmm-action-btn.btn-edit {
            color: #e67a38;
            border-color: rgba(230,122,56,.2);
            background: rgba(230,122,56,.08);
        }
        .fmm-action-btn.btn-edit:hover {
            background: #e67a38;
            border-color: #e67a38;
            color: #fff;
        }
        .fmm-action-btn.btn-toggle-visibility {
            color: #198754;
            border-color: rgba(25,135,84,.2);
            background: rgba(25,135,84,.08);
        }
        .fmm-action-btn.btn-toggle-visibility:hover {
            background: #198754;
            border-color: #198754;
            color: #fff;
        }
        .fmm-action-btn.danger,
        .fmm-action-btn.btn-delete {
            color: #e62e04;
            border-color: rgba(230,46,4,.2);
            background: rgba(230,46,4,.08);
        }
        .fmm-action-btn.danger:hover,
        .fmm-action-btn.btn-delete:hover {
            background: #e62e04;
            border-color: #e62e04;
            color: #fff;
        }
        .fmm-action-btn:disabled { opacity: 0.3; cursor: not-allowed; }
        .fmm-action-btn:disabled:hover { background: rgba(31,3,0,.05); color: #1f0300; border-color: rgba(31,3,0,.15); }

        /* Inline edit form */
        .fmm-edit-form {
            margin-top: 0.375rem;
            padding: 0.75rem;
            border: 1px solid var(--fmm-accent);
            border-radius: var(--fmm-radius-sm);
            background: var(--fmm-bg);
            box-shadow: 0 0 0 2px rgba(208,42,3,0.08);
        }
        .fmm-edit-form .fmm-label {
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--fmm-muted);
            text-transform: uppercase;
            letter-spacing: 0.03em;
            margin-bottom: 0.25rem;
        }
        .fmm-edit-form input[type="text"],
        .fmm-edit-form select {
            width: 100%;
            padding: 0.4rem 0.625rem;
            border-radius: var(--fmm-radius-sm);
            border: 1px solid var(--fmm-border);
            background: var(--fmm-bg);
            color: var(--fmm-text);
            font-size: 0.8125rem;
            outline: none;
            margin-bottom: 0.5rem;
        }
        .fmm-edit-form input:focus,
        .fmm-edit-form select:focus {
            border-color: var(--fmm-accent);
            box-shadow: 0 0 0 2px rgba(208,42,3,0.1);
        }
        .fmm-edit-row { display: flex; gap: 0.5rem; }
        .fmm-edit-row > div { flex: 1; }
        .fmm-edit-actions {
            display: flex;
            justify-content: flex-end;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        .fmm-edit-actions button {
            padding: 0.3rem 0.875rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: var(--fmm-radius-sm);
            cursor: pointer;
            border: none;
        }
        .fmm-edit-actions .fmm-btn-cancel {
            background: var(--fmm-handle-bg);
            border: 1px solid var(--fmm-border);
            color: var(--fmm-text);
        }
        .fmm-edit-actions .fmm-btn-save {
            background: var(--fmm-accent);
            color: #fff;
        }
        .fmm-edit-actions .fmm-btn-save:hover {
            background: var(--fmm-accent-hover);
        }

        /* Nested list */
        #fmm-root-list {
            min-height: 2rem;
            padding: 0.25rem;
        }
        #fmm-root-list > .fmm-item-row {
            margin-bottom: 0.5rem;
        }
        .fmm-nested-list {
            margin-left: 1.5rem;
            margin-top: 0;
            padding: 0.25rem 0;
            /* padding-bottom: 0.75rem; */
            border-left: 2px dotted var(--fmm-border);
            min-height: 2.5rem;
            transition: min-height 0.2s, background 0.2s, border-color 0.2s;
        }
        .fmm-nested-list .fmm-nested-list {
            border-left-color: #e9ecef;
        }
        .fmm-nested-list.fmm-drop-target,
        #fmm-root-list.fmm-drop-target {
            min-height: 3.5rem;
            background: #d02a0308;
            border-left: 2px dashed var(--fmm-accent);
            border-radius: var(--fmm-radius-sm);
        }
        .fmm-nested-list:empty {
            min-height: 2.5rem;
            border: 1px dashed var(--fmm-border);
            border-left: 2px solid var(--fmm-border);
            border-radius: var(--fmm-radius-sm);
            background: var(--fmm-surface);
        }
        .fmm-nested-list:empty.fmm-drop-target {
            border-color: var(--fmm-accent);
            background: #d02a0308;
            min-height: 3.5rem;
        }

        /* Sortable */
        .fmm-sortable-ghost {
            opacity: 0.4;
            border: 1px dashed var(--fmm-accent) !important;
            background: #d02a0308 !important;
        }
        .fmm-sortable-chosen {
            box-shadow: 0 4px 16px rgba(0,0,0,0.12) !important;
        }
        body.fmm-dragging * { cursor: grabbing !important; }

        /* Panel card */
        .fmm-panel-card {
            background: var(--fmm-bg);
            border: none;
            border-radius: var(--fmm-radius);
            box-shadow: var(--fmm-shadow);
        }
        .fmm-panel-tabs {
            display: flex;
            border-bottom: 1px solid var(--fmm-border);
        }
        .fmm-panel-tab {
            flex: 1;
            padding: 0.625rem;
            font-size: 0.8rem;
            font-weight: 600;
            text-align: center;
            border: none;
            background: none;
            cursor: pointer;
            color: var(--fmm-muted);
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
        }
        .fmm-panel-tab.active {
            color: var(--fmm-accent);
            border-bottom-color: var(--fmm-accent);
        }
        .fmm-panel-tab:hover { color: var(--fmm-text); }
        .fmm-panel-body { padding: 0.875rem; }
        .fmm-panel-body .fmm-label {
            font-size: 0.7rem;
            font-weight: 600;
            color: var(--fmm-muted);
            text-transform: uppercase;
            letter-spacing: 0.03em;
            margin-bottom: 0.25rem;
            margin-top: 0.5rem;
        }
        .fmm-panel-body .fmm-label:first-child { margin-top: 0; }
        .fmm-panel-body input,
        .fmm-panel-body select {
            width: 100%;
            padding: 0.4rem 0.625rem;
            border-radius: var(--fmm-radius-sm);
            border: 1px solid var(--fmm-border);
            background: var(--fmm-bg);
            color: var(--fmm-text);
            font-size: 0.8125rem;
            outline: none;
        }
        .fmm-panel-body input:focus,
        .fmm-panel-body select:focus {
            border-color: var(--fmm-accent);
            box-shadow: 0 0 0 2px rgba(208,42,3,0.1);
        }
        .fmm-panel-body .fmm-add-btn {
            width: 100%;
            padding: 0.5rem;
            background: var(--fmm-accent);
            color: #fff;
            border: none;
            border-radius: var(--fmm-radius-sm);
            font-size: 0.8125rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 0.75rem;
            transition: background 0.2s;
        }
        .fmm-panel-body .fmm-add-btn:hover { background: var(--fmm-accent-hover); }

        /* Model list */
        .fmm-panel-section-header {
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--fmm-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 0.5rem 0 0.25rem;
            border-bottom: 1px solid var(--fmm-border);
            margin-bottom: 0.375rem;
        }
        .fmm-model-list { max-height: 250px; overflow-y: auto; }
        .fmm-model-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.375rem 0.5rem;
            border-radius: var(--fmm-radius-sm);
            font-size: 0.8rem;
            cursor: pointer;
            transition: background 0.2s;
        }
        .fmm-model-item:hover { background: var(--fmm-handle-bg); }
        .fmm-model-item.disabled { opacity: 0.5; cursor: not-allowed; }

        /* Tab checklist */
        .fmm-tab-bar {
            display: flex;
            border-bottom: 1px solid var(--fmm-border);
            margin-bottom: 0.5rem;
        }
        .fmm-tab-bar button {
            padding: 0.375rem 0.625rem;
            font-size: 0.7rem;
            font-weight: 600;
            border: none;
            background: none;
            cursor: pointer;
            color: var(--fmm-muted);
            border-bottom: 2px solid transparent;
        }
        .fmm-tab-bar button.active {
            color: var(--fmm-accent);
            border-bottom-color: var(--fmm-accent);
        }
        .fmm-checklist {
            list-style: none;
            margin: 0;
            padding: 0;
            max-height: 200px;
            overflow-y: auto;
        }
        .fmm-checklist li label {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.25rem 0;
            font-size: 0.8rem;
            cursor: pointer;
            color: var(--fmm-text);
        }
        .fmm-checklist li label:hover { color: var(--fmm-accent); }
        .fmm-checklist li label input[type="checkbox"] { width: auto; }
        .fmm-search-input {
            width: 100%;
            padding: 0.375rem 0.5rem;
            border-radius: var(--fmm-radius-sm);
            border: 1px solid var(--fmm-border);
            font-size: 0.8rem;
            outline: none;
            margin-bottom: 0.375rem;
        }

        /* Delete modal */
        .fmm-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.45);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(2px);
        }
        .fmm-modal-content {
            background: var(--fmm-bg);
            border-radius: 8px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.25);
            width: 100%;
            max-width: 26rem;
            overflow: hidden;
        }
        .fmm-modal-icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            background: #dc354515;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--fmm-danger);
        }
        .fmm-btn {
            padding: 0.5rem 1rem;
            border-radius: var(--fmm-radius-sm);
            font-size: 0.8125rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: all 0.2s;
        }
        .fmm-btn:hover { opacity: 0.85; }
        .fmm-btn-secondary {
            background: var(--fmm-handle-bg);
            border: 1px solid var(--fmm-border);
            color: var(--fmm-text);
        }
        .fmm-btn-danger { background: var(--fmm-danger); color: #fff; }

        /* Responsive */
        @media (max-width: 991px) {
            .fmm-wrapper { flex-direction: column-reverse; }
            .fmm-panel { width: 100%; position: static; }
        }
        @media (max-width: 767.98px) {
            .admin-settings-header,
            .admin-settings-header .tab-content,
            .admin-settings-header .tab-pane,
            .admin-settings-header .tab-pane > .row,
            .admin-settings-header .tab-pane [class*="col-"],
            .fmm-wrapper,
            .fmm-builder,
            .fmm-panel,
            .fmm-card,
            .fmm-panel-card {
                min-width: 0;
                max-width: 100%;
            }

            .fmm-item-card { flex-wrap: wrap; }
            .fmm-item-actions {
                flex: 1 1 100%;
                justify-content: flex-end;
                flex-wrap: wrap;
                gap: 4px;
            }
            .fmm-action-btn {
                --size: 32px;
            }
            .fmm-nested-list { margin-left: 0.75rem; }
            .fmm-panel-tab {
                min-width: 0;
                padding: 0.5rem 0.25rem;
                font-size: 0.72rem;
            }
            .fmm-panel-body { padding: 0.75rem; }
            .fmm-edit-row { flex-wrap: wrap; }
            .fmm-edit-row > div { flex: 1 1 100%; min-width: 0; }
        }
        @media (max-width: 575.98px) {
            .admin-settings-header > .nav-tabs .nav-link {
                padding: 0.5rem 0.65rem;
                font-size: 0.875rem;
            }
            .fmm-card-header {
                align-items: flex-start;
                flex-wrap: wrap;
                gap: 0.375rem;
            }
            .fmm-item-actions { justify-content: flex-start; }
            .fmm-modal-content { width: calc(100% - 2rem); }
        }
    </style>

    <div class="row mt-4">
        <div class="col-md-12">
            <form action="{{ route('admin.settings.header.update') }}" method="POST" id="navMenuForm">
                @csrf
                <input type="hidden" name="nav_menu" id="navMenuInput" value="{{ $settings['nav_menu'] ?? '[]' }}">

                <div class="fmm-wrapper">
                    {{-- Builder (Right) --}}
                    <div class="fmm-builder">
                        <div class="fmm-card">
                            <div class="fmm-card-header">
                                <span>Menu Items</span>
                                <span class="fmm-auto-save-badge">Auto-save enabled</span>
                            </div>
                            <div class="fmm-card-body">
                                <div id="fmm-root-list">
                                    <div class="fmm-empty" id="fmm-empty-hint" style="{{ ($settings['nav_menu'] ?? '[]') !== '[]' ? 'display:none' : '' }}">
                                        <i class="fas fa-layer-group" style="font-size:2rem;opacity:0.3;display:block;margin-bottom:0.5rem;"></i>
                                        <p>Add items from the panel on the right.</p>
                                    </div>
                                </div>
                                <div style="padding:0.75rem 0 0;display:flex;justify-content:flex-end;">
                                    <button type="submit" class="btn btn-primary steve-btn gap-1">
                                        <i class="fas fa-save me-1"></i> Save Menu
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Panel (Left) --}}
                    <div class="fmm-panel">
                        <div class="fmm-panel-card">
                            <div class="fmm-panel-tabs">
                                <button type="button" class="fmm-panel-tab active" data-fmm-tab="pages">Pages</button>
                                <button type="button" class="fmm-panel-tab" data-fmm-tab="posts">Posts</button>
                                <button type="button" class="fmm-panel-tab" data-fmm-tab="categories">Categories</button>
                                <button type="button" class="fmm-panel-tab" data-fmm-tab="custom">Custom</button>
                            </div>
                            <div class="fmm-panel-body">

                                {{-- Pages --}}
                                <div class="fmm-tab-content" data-fmm-tabcontent="pages">
                                    <div class="fmm-tab-bar">
                                        <button type="button" class="active" data-fmm-inner="recent">Recent</button>
                                        <button type="button" data-fmm-inner="all">All</button>
                                        <button type="button" data-fmm-inner="search">Search</button>
                                    </div>
                                    <div class="fmm-tab-inner" data-fmm-inner="recent">
                                        <ul class="fmm-checklist">
                                            @foreach($pages->take(10) as $page)
                                                <li><label><input type="checkbox" data-fmm-type="page" data-fmm-label="{{ $page->title }}" data-fmm-url="/page/{{ $page->slug }}">{{ $page->title }}</label></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    <div class="fmm-tab-inner" data-fmm-inner="all" style="display:none">
                                        <ul class="fmm-checklist">
                                            @foreach($pages as $page)
                                                <li><label><input type="checkbox" data-fmm-type="page" data-fmm-label="{{ $page->title }}" data-fmm-url="/page/{{ $page->slug }}">{{ $page->title }}</label></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    <div class="fmm-tab-inner" data-fmm-inner="search" style="display:none">
                                        <input type="text" class="fmm-search-input" placeholder="Search pages...">
                                        <ul class="fmm-checklist fmm-searchable">
                                            @foreach($pages as $page)
                                                <li><label><input type="checkbox" data-fmm-type="page" data-fmm-label="{{ $page->title }}" data-fmm-url="/page/{{ $page->slug }}">{{ $page->title }}</label></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    <button type="button" class="fmm-add-btn fmm-add-checked">+ Add to Menu</button>
                                </div>

                                {{-- Posts --}}
                                <div class="fmm-tab-content" data-fmm-tabcontent="posts" style="display:none">
                                    <div class="fmm-tab-bar">
                                        <button type="button" class="active" data-fmm-inner="recent">Recent</button>
                                        <button type="button" data-fmm-inner="all">All</button>
                                        <button type="button" data-fmm-inner="search">Search</button>
                                    </div>
                                    <div class="fmm-tab-inner" data-fmm-inner="recent">
                                        <ul class="fmm-checklist">
                                            @foreach($posts->take(10) as $post)
                                                <li><label><input type="checkbox" data-fmm-type="post" data-fmm-label="{{ $post->title }}" data-fmm-url="/blog/{{ $post->slug }}">{{ $post->title }}</label></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    <div class="fmm-tab-inner" data-fmm-inner="all" style="display:none">
                                        <ul class="fmm-checklist">
                                            @foreach($posts as $post)
                                                <li><label><input type="checkbox" data-fmm-type="post" data-fmm-label="{{ $post->title }}" data-fmm-url="/blog/{{ $post->slug }}">{{ $post->title }}</label></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    <div class="fmm-tab-inner" data-fmm-inner="search" style="display:none">
                                        <input type="text" class="fmm-search-input" placeholder="Search posts...">
                                        <ul class="fmm-checklist fmm-searchable">
                                            @foreach($posts as $post)
                                                <li><label><input type="checkbox" data-fmm-type="post" data-fmm-label="{{ $post->title }}" data-fmm-url="/blog/{{ $post->slug }}">{{ $post->title }}</label></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    <button type="button" class="fmm-add-btn fmm-add-checked">+ Add to Menu</button>
                                </div>

                                {{-- Categories --}}
                                <div class="fmm-tab-content" data-fmm-tabcontent="categories" style="display:none">
                                    <div class="fmm-tab-bar">
                                        <button type="button" class="active" data-fmm-inner="all">All</button>
                                        <button type="button" data-fmm-inner="search">Search</button>
                                    </div>
                                    <div class="fmm-tab-inner" data-fmm-inner="all">
                                        <ul class="fmm-checklist">
                                            @foreach($categories as $cat)
                                                <li><label><input type="checkbox" data-fmm-type="category" data-fmm-label="{{ $cat->name }}" data-fmm-url="/category/{{ $cat->slug }}">{{ $cat->name }}</label></li>
                                                @if($cat->children->count())
                                                    @foreach($cat->children as $child)
                                                        <li><label style="padding-left:1rem"><input type="checkbox" data-fmm-type="category" data-fmm-label="{{ $child->name }}" data-fmm-url="/category/{{ $child->slug }}">{{ $child->name }}</label></li>
                                                    @endforeach
                                                @endif
                                            @endforeach
                                        </ul>
                                    </div>
                                    <div class="fmm-tab-inner" data-fmm-inner="search" style="display:none">
                                        <input type="text" class="fmm-search-input" placeholder="Search categories...">
                                        <ul class="fmm-checklist fmm-searchable">
                                            @foreach($categories as $cat)
                                                <li><label><input type="checkbox" data-fmm-type="category" data-fmm-label="{{ $cat->name }}" data-fmm-url="/category/{{ $cat->slug }}">{{ $cat->name }}</label></li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    <button type="button" class="fmm-add-btn fmm-add-checked">+ Add to Menu</button>
                                </div>

                                {{-- Custom Link --}}
                                <div class="fmm-tab-content" data-fmm-tabcontent="custom" style="display:none">
                                    <div class="fmm-label">Title</div>
                                    <input type="text" id="fmm-custom-title" placeholder="e.g. Home">
                                    <div class="fmm-label">URL</div>
                                    <input type="text" id="fmm-custom-url" placeholder="https://... or /">
                                    <div class="fmm-label">Open in</div>
                                    <select id="fmm-custom-target">
                                        <option value="_self">Same Tab</option>
                                        <option value="_blank">New Tab</option>
                                    </select>
                                    <button type="button" class="fmm-add-btn" id="fmm-add-custom">+ Add to Menu</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

        </div>
        <div class="tab-pane fade {{ session('settings_tab') === 'footer' ? 'show active' : '' }}" id="footer-configuration" role="tabpanel" aria-labelledby="footer-configuration-tab">
            <div class="row">
                <div class="col-md-12">
                    @include('admin.settings.partials.footer-columns')
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div class="fmm-modal-overlay" id="fmm-delete-modal" style="display:none">
        <div class="fmm-modal-content" role="dialog" aria-modal="true">
            <div style="padding:1.5rem;display:flex;gap:1rem;align-items:flex-start;">
                <div class="fmm-modal-icon">
                    <i class="fas fa-exclamation-triangle" style="font-size:1.25rem;"></i>
                </div>
                <div style="flex:1;">
                    <h3 style="font-size:1rem;font-weight:600;color:var(--fmm-text);margin:0;">Delete Menu Item</h3>
                    <p style="font-size:0.875rem;color:var(--fmm-muted);margin-top:0.5rem;">Are you sure? This will also remove any children items.</p>
                </div>
            </div>
            <div style="padding:1rem 1.5rem;background:var(--fmm-handle-bg);border-top:1px solid var(--fmm-border);display:flex;justify-content:flex-end;gap:0.75rem;">
                <button type="button" class="fmm-btn fmm-btn-secondary" id="fmm-delete-cancel">Cancel</button>
                <button type="button" class="fmm-btn fmm-btn-danger" id="fmm-delete-confirm">Delete</button>
            </div>
        </div>
    </div>
@include('admin.partials.image-manager-picker', ['pickerId' => 'header_logo', 'targetInput' => 'image_from_manager_header_logo'])
@include('admin.partials.image-manager-picker', ['pickerId' => 'header_favicon', 'targetInput' => 'image_from_manager_header_favicon'])
@include('admin.partials.image-manager-picker', ['pickerId' => 'mobile_logo', 'targetInput' => 'image_from_manager_mobile_logo'])
@include('admin.partials.image-manager-picker', ['pickerId' => 'footer_logo', 'targetInput' => 'image_from_manager_footer_logo'])
@include('admin.partials.image-manager-picker', ['pickerId' => 'admin_header_bg', 'targetInput' => 'image_from_manager_admin_header_bg'])

<script>
function clearPickerImage(hiddenId, previewId, currentImgSel, removeFlagId) {
    var hidden = document.getElementById(hiddenId);
    if (hidden) hidden.value = '';
    var preview = document.getElementById(previewId);
    if (preview) {
        preview.innerHTML = '';
        preview.classList.add('d-none');
    }
    if (currentImgSel) {
        document.querySelectorAll(currentImgSel).forEach(function(el) {
            el.classList.add('d-none');
        });
    }
    if (removeFlagId) {
        var flag = document.getElementById(removeFlagId);
        if (flag) flag.value = '1';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var obsConfig = { childList: true, subtree: true, attributes: true, attributeFilter: ['class'] };
    document.querySelectorAll('[data-preview]').forEach(function(btn) {
        var previewId  = btn.getAttribute('data-preview');
        var currentSel = btn.getAttribute('data-current');
        var preview    = document.getElementById(previewId);
        if (!preview) return;

        var update = function() {
            var hasPickedImage = preview.querySelector('img') !== null;
            var hasExistingImage = false;
            if (currentSel) {
                document.querySelectorAll(currentSel).forEach(function(el) {
                    if (!el.classList.contains('d-none')) hasExistingImage = true;
                });
            }
            btn.classList.toggle('d-none', !hasPickedImage && !hasExistingImage);
        };

        new MutationObserver(update).observe(preview, obsConfig);
        if (currentSel) {
            document.querySelectorAll(currentSel).forEach(function(el) {
                new MutationObserver(update).observe(el, obsConfig);
            });
        }
    });
});
</script>

@php
    $availableMenuUrls = [];
    foreach ($pages as $p) {
        $availableMenuUrls[strtolower('page:/page/' . $p->slug)] = true;
    }
    foreach ($posts as $p) {
        $availableMenuUrls[strtolower('post:/blog/' . $p->slug)] = true;
    }
    foreach ($categories as $c) {
        $availableMenuUrls[strtolower('category:/category/' . $c->slug)] = true;
        foreach ($c->children as $ch) {
            $availableMenuUrls[strtolower('category:/category/' . $ch->slug)] = true;
        }
    }
@endphp

@push('scripts')
<script>
(function($) {
    var $root = $('#fmm-root-list');
    var $form = $('#navMenuForm');
    var $input = $('#navMenuInput');
    var $emptyHint = $('#fmm-empty-hint');
    var menuData = {!! $settings['nav_menu'] ?? '[]' !!};
    var fmmAvailable = @json($availableMenuUrls);
    var itemCounter = 0;

    function nextId() { return 'fmm-' + (++itemCounter); }
    function esc(s) {
        if (!s) return '';
        return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
    }
    function toggleEmpty() {
        $emptyHint.toggle($root.find('.fmm-item-row').length === 0);
    }
    function typeBadge(type) {
        var map = { page: 'Page', post: 'Post', category: 'Category', custom: 'Custom' };
        return map[type] || type;
    }
    function urlLinkedType(url) {
        if (/^\/category\/.+/i.test(url)) return 'category';
        if (/^\/page\/.+/i.test(url)) return 'page';
        if (/^\/blog\/.+/i.test(url)) return 'post';
        return null;
    }
    function refreshSortable() {
        $('#fmm-root-list, .fmm-nested-list').sortable('destroy');

        var sortableOpts = {
            connectWith: '#fmm-root-list, .fmm-nested-list',
            items: '> .fmm-item-row',
            handle: '.fmm-drag-handle',
            placeholder: 'fmm-sortable-ghost',
            tolerance: 'pointer',
            opacity: 0.9,
            cursor: 'grabbing',
            forcePlaceholderSize: true,
            over: function(e, ui) {
                var $target = $(this);
                $target.addClass('fmm-drop-target');
                $('#fmm-root-list, .fmm-nested-list').not($target).removeClass('fmm-drop-target');
            },
            out: function(e, ui) {
                $(this).removeClass('fmm-drop-target');
            },
            deactivate: function() {
                $('#fmm-root-list, .fmm-nested-list').removeClass('fmm-drop-target');
            },
            stop: function() {
                $('#fmm-root-list, .fmm-nested-list').removeClass('fmm-drop-target');
                rebuildJson();
            }
        };

        $('#fmm-root-list').sortable(sortableOpts);
        $('.fmm-nested-list').each(function() {
            if (!$(this).data('ui-sortable')) {
                $(this).sortable(sortableOpts);
            }
        });
    }

    function renderRow(item) {
        var id = nextId();
        var type = item.type || 'custom';
        var label = item.label || '';
        var url = item.url || '';
        var target = item.target || '_self';
        var visible = item.visible !== false;
        var children = item.children || [];

        var disabledCls = visible ? '' : ' disabled-item';
        var hiddenBadge = visible ? '' : ' <span class="fmm-item-badge hidden-badge">Hidden</span>';

        var urlType = urlLinkedType(url);
        var unavailable = urlType !== null && !fmmAvailable[(urlType + ':' + url).toLowerCase()];
        var unavailCls = unavailable ? ' fmm-unavailable-item' : '';
        var warningHtml = unavailable
            ? '<span class="fmm-item-warning"><i class="fas fa-exclamation-triangle"></i> Not available — the linked ' + urlType + ' ( ' + esc(url) + ' ) no longer exists or is unpublished.</span>'
            : '';

        var html = '<div class="fmm-item-row" data-id="' + id + '" data-type="' + type + '">';
        html += '<div class="fmm-item-card' + disabledCls + unavailCls + '">';
        html += '<span class="fmm-drag-handle" title="Drag to reorder"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="5" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="9" cy="12" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="19" r="1"/></svg></span>';
        html += '<div class="fmm-item-body">';
        html += '<div class="fmm-item-title-row"><span class="fmm-item-title">' + esc(label) + '</span>';
        html += '<span class="fmm-item-badges"><span class="fmm-item-badge">' + typeBadge(type) + '</span>' + hiddenBadge + '</span>';
        html += '</div>';
        html += '<span class="fmm-item-url fmm-item-url-display" title="' + esc(url) + '">' + esc(url) + '</span>';
        html += warningHtml;
        html += '</div>';
        html += '<div class="fmm-item-actions">';
        html += '<button type="button" class="fmm-action-btn btn-move-up" title="Move up"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg></button>';
        html += '<button type="button" class="fmm-action-btn btn-move-down" title="Move down"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></button>';
        html += '<button type="button" class="fmm-action-btn btn-nest-right" title="Make sub-item of above"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="11 17 6 12 11 7"/><polyline points="18 17 13 12 18 7"/></svg></button>';
        html += '<button type="button" class="fmm-action-btn btn-nest-left" title="Move out to parent level"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="13 17 18 12 13 7"/><polyline points="6 17 11 12 6 7"/></svg></button>';
        html += '<button type="button" class="fmm-action-btn btn-edit" title="Edit"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button>';
        html += '<button type="button" class="fmm-action-btn btn-toggle-visibility" title="' + (visible ? 'Hide' : 'Show') + '"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' + (visible ? '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>' : '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>') + '</svg></button>';
        html += '<button type="button" class="fmm-action-btn btn-delete" title="Delete"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg></button>';
        html += '</div>';
        html += '</div>';

        html += '<div class="fmm-edit-form" style="display:none">';
        html += '<div class="fmm-label">Title</div>';
        html += '<input type="text" class="fmm-edit-title" value="' + esc(label) + '">';
        html += '<div class="fmm-label">URL</div>';
        if (type === 'custom') {
            html += '<input type="text" class="fmm-edit-url" value="' + esc(url) + '">';
        } else {
            html += '<div style="padding:0.4rem 0.625rem;background:var(--fmm-handle-bg);border:1px solid var(--fmm-border);border-radius:var(--fmm-radius-sm);font-size:0.8125rem;color:var(--fmm-muted);word-break:break-all;line-height:1.4;">' + esc(url) + '</div>';
        }
        html += '<div class="fmm-edit-row">';
        html += '<div><div class="fmm-label">Target</div><select class="fmm-edit-target"><option value="_self"' + (target === '_self' ? ' selected' : '') + '>Same Tab</option><option value="_blank"' + (target === '_blank' ? ' selected' : '') + '>New Tab</option></select></div>';
        html += '<div><div class="fmm-label">Visible</div><select class="fmm-edit-visible"><option value="1"' + (visible ? ' selected' : '') + '>Yes</option><option value="0"' + (!visible ? ' selected' : '') + '>No</option></select></div>';
        html += '</div>';
        html += '<div class="fmm-edit-actions"><button type="button" class="fmm-btn-cancel fmm-btn" style="background:var(--fmm-bg);border:1.5px solid var(--fmm-border);color:var(--fmm-text);">Cancel</button> <button type="button" class="fmm-btn-save fmm-btn" style="background:var(--fmm-accent);color:#fff;">Save</button></div>';
        html += '</div>';
        html += '</div>';

        var $el = $(html);
        if (children && children.length) {
            var $nested = $('<div class="fmm-nested-list"></div>');
            children.forEach(function(child) {
                $nested.append(renderRow(child));
            });
            $el.append($nested);
        }
        return $el;
    }

    function buildFromJson(data) {
        $root.find('.fmm-item-row').remove();
        if (!data || !data.length) { toggleEmpty(); refreshSortable(); return; }
        data.forEach(function(item) { $root.append(renderRow(item)); });
        toggleEmpty();
        refreshSortable();
    }

    function rebuildJson() {
        function readList($list) {
            var items = [];
            $list.children('.fmm-item-row').each(function() {
                var $row = $(this);
                var $editForm = $row.find('> .fmm-edit-form');
                var $card = $row.find('> .fmm-item-card');
                var titleText = $card.find('.fmm-item-title').text().trim();
                var urlText = $card.find('.fmm-item-url-display').attr('title') || $card.find('.fmm-item-url-display').text().trim();
                var type = $row.data('type');
                var visible = true;
                if ($editForm.length && $editForm.find('.fmm-edit-visible').length) {
                    visible = $editForm.find('.fmm-edit-visible').val() === '1';
                }
                if ($editForm.is(':visible')) {
                    titleText = $editForm.find('.fmm-edit-title').val() || titleText;
                    urlText = $editForm.find('.fmm-edit-url').val() || urlText;
                    visible = $editForm.find('.fmm-edit-visible').val() === '1';
                }
                var children = readList($row.find('> .fmm-nested-list'));
                var item = {
                    label: titleText,
                    url: urlText,
                    type: type,
                    target: ($editForm.length ? $editForm.find('.fmm-edit-target').val() : '_self') || '_self',
                    visible: visible
                };
                if (children.length) item.children = children;
                items.push(item);
            });
            return items;
        }
        var json = readList($root);
        $input.val(JSON.stringify(json));
    }

    $(document).on('click', '.fmm-panel-tab', function() {
        var tab = $(this).data('fmm-tab');
        $('.fmm-panel-tab').removeClass('active');
        $(this).addClass('active');
        $('.fmm-tab-content').hide();
        $('.fmm-tab-content[data-fmm-tabcontent="' + tab + '"]').show();
    });

    $(document).on('click', '.fmm-tab-bar button', function() {
        var panel = $(this).closest('.fmm-tab-content');
        var inner = $(this).data('fmm-inner');
        panel.find('.fmm-tab-bar button').removeClass('active');
        $(this).addClass('active');
        panel.find('.fmm-tab-inner').hide();
        panel.find('.fmm-tab-inner[data-fmm-inner="' + inner + '"]').show();
    });

    $(document).on('input', '.fmm-search-input', function() {
        var q = $(this).val().toLowerCase();
        $(this).next('.fmm-checklist, .fmm-searchable').find('li').each(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(q) > -1);
        });
    });

    $(document).on('click', '.fmm-add-checked', function() {
        var panel = $(this).closest('.fmm-tab-content');
        panel.find('.fmm-checklist input[type="checkbox"]:checked').each(function() {
            var item = {
                label: $(this).data('fmm-label'),
                url: $(this).data('fmm-url'),
                type: $(this).data('fmm-type'),
                target: '_self',
                visible: true,
                children: []
            };
            $root.append(renderRow(item));
            $(this).prop('checked', false);
        });
        toggleEmpty();
        refreshSortable();
        rebuildJson();
    });

    $(document).on('click', '#fmm-add-custom', function() {
        var title = $('#fmm-custom-title').val().trim();
        var url = $('#fmm-custom-url').val().trim();
        var target = $('#fmm-custom-target').val();
        if (!title) { $('#fmm-custom-title').focus(); return; }
        if (!url) { $('#fmm-custom-url').focus(); return; }
        var item = { label: title, url: url, type: 'custom', target: target, visible: true, children: [] };
        $root.append(renderRow(item));
        $('#fmm-custom-title').val('');
        $('#fmm-custom-url').val('');
        toggleEmpty();
        refreshSortable();
        rebuildJson();
    });

    $(document).on('click', '.btn-edit', function(e) {
        e.stopPropagation();
        var $card = $(this).closest('.fmm-item-card');
        var $row = $card.closest('.fmm-item-row');
        var $form = $row.find('> .fmm-edit-form');
        $form.slideToggle(150);
        $card.toggleClass('fmm-editing');
    });

    $(document).on('click', '.fmm-btn-cancel', function(e) {
        e.stopPropagation();
        var $form = $(this).closest('.fmm-edit-form');
        var $row = $form.closest('.fmm-item-row');
        $form.slideUp(150);
        $row.find('> .fmm-item-card').removeClass('fmm-editing');
    });

    $(document).on('click', '.fmm-btn-save', function(e) {
        e.stopPropagation();
        var $form = $(this).closest('.fmm-edit-form');
        var $row = $form.closest('.fmm-item-row');
        var $card = $row.find('> .fmm-item-card');
        var newTitle = $form.find('.fmm-edit-title').val().trim();
        var newUrl = $form.find('.fmm-edit-url').val().trim();
        var newVisible = $form.find('.fmm-edit-visible').val() === '1';
        if (newTitle) $card.find('.fmm-item-title').text(newTitle);
        if (newUrl) {
            $card.find('.fmm-item-url-display').text(newUrl).attr('title', newUrl);
        }
        $card.toggleClass('disabled-item', !newVisible);
        var $badges = $card.find('.fmm-item-badges');
        $badges.find('.fmm-item-badge.hidden-badge').remove();
        if (!newVisible) {
            $badges.append('<span class="fmm-item-badge hidden-badge">Hidden</span>');
        }
        $form.slideUp(150);
        $card.removeClass('fmm-editing');
        rebuildJson();
    });

    $(document).on('click', '.btn-toggle-visibility', function(e) {
        e.stopPropagation();
        var $row = $(this).closest('.fmm-item-row');
        var $card = $row.find('> .fmm-item-card');
        var $editForm = $row.find('> .fmm-edit-form');
        var currentVis = $editForm.find('.fmm-edit-visible').val();
        var newVis = currentVis === '1' ? '0' : '1';
        $editForm.find('.fmm-edit-visible').val(newVis);
        $card.toggleClass('disabled-item', newVis === '0');
        var $badges = $card.find('.fmm-item-badges');
        $badges.find('.fmm-item-badge.hidden-badge').remove();
        if (newVis === '0') {
            $badges.append('<span class="fmm-item-badge hidden-badge">Hidden</span>');
        }
        rebuildJson();
    });

    $(document).on('click', '.btn-move-up', function(e) {
        e.stopPropagation();
        var $row = $(this).closest('.fmm-item-row');
        var $prev = $row.prev('.fmm-item-row');
        if ($prev.length) $row.insertBefore($prev);
        rebuildJson();
    });
    $(document).on('click', '.btn-move-down', function(e) {
        e.stopPropagation();
        var $row = $(this).closest('.fmm-item-row');
        var $next = $row.next('.fmm-item-row');
        if ($next.length) $row.insertAfter($next);
        rebuildJson();
    });

    $(document).on('click', '.btn-nest-right', function(e) {
        e.stopPropagation();
        var $row = $(this).closest('.fmm-item-row');
        var $prev = $row.prev('.fmm-item-row');
        if (!$prev.length) return;
        var $nested = $prev.find('> .fmm-nested-list');
        if (!$nested.length) {
            $nested = $('<div class="fmm-nested-list"></div>');
            $prev.append($nested);
        }
        $nested.append($row);
        refreshSortable();
        rebuildJson();
    });

    $(document).on('click', '.btn-nest-left', function(e) {
        e.stopPropagation();
        var $row = $(this).closest('.fmm-item-row');
        var $parentNested = $row.closest('.fmm-nested-list');
        if (!$parentNested.length) return;
        var $parentRow = $parentNested.closest('.fmm-item-row');
        if (!$parentRow.length) return;
        $row.insertAfter($parentRow);
        refreshSortable();
        rebuildJson();
    });

    var $deleteModal = $('#fmm-delete-modal');
    var $deleteTarget = null;

    $(document).on('click', '.btn-delete', function(e) {
        e.stopPropagation();
        $deleteTarget = $(this).closest('.fmm-item-row');
        $deleteModal.show();
    });
    $(document).on('click', '#fmm-delete-cancel', function() {
        $deleteTarget = null;
        $deleteModal.hide();
    });
    $(document).on('click', '#fmm-delete-confirm', function() {
        if ($deleteTarget) {
            $deleteTarget.fadeOut(200, function() { $(this).remove(); toggleEmpty(); rebuildJson(); });
        }
        $deleteTarget = null;
        $deleteModal.hide();
    });

    $form.on('submit', function() {
        rebuildJson();
    });

    buildFromJson(menuData);

})(jQuery);
</script>
@endpush
@endsection