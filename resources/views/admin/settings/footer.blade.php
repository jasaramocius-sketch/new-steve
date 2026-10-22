@extends('admin.layouts.app')
@include('partials.page-attributes', ['pageId' => 'admin-settings-footer-page', 'pageClass' => 'admin-settings-footer-page'])
@section('page-title', 'Footer Settings')
@section('meta_tags')
    @include('partials.meta-tags', [
        'pageTitle' => 'Footer Settings - Admin Panel',
        'robots' => 'noindex, nofollow',
    ])
@endsection
@section('content')
<div class="container-fluid admin-settings-footer">
    <div class="row">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Footer Columns</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.footer.update') }}" method="POST" id="footerColsForm">
                        @csrf
                        <input type="hidden" name="footer_columns" id="footerColumnsInput" value="{{ $settings['footer_columns'] ?? '[]' }}">

                        <div id="footerColsContainer"></div>

                        <button type="button" class="btn btn-primary steve-btn gap-1 mt-2" id="addFooterColumn">
                            <i class="fas fa-plus me-1"></i> Add Column
                        </button>

                        <hr>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary steve-btn gap-1">
                                <i class="fas fa-save me-1"></i> Save Columns
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .footer-col-card {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 0;
        box-shadow: 0 .125rem 6rem rgba(0,0,0,.075);
        margin-bottom: 0.5rem;
    }
    .footer-col-card:hover { border-color: #d02a0330; }
    .footer-col-header {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    .footer-col-header .form-select,
    .footer-col-header .form-control {
        font-size: 0.8125rem;
        border-radius: 4px;
        border-color: #e9ecef;
    }
    .footer-col-header .form-select:focus,
    .footer-col-header .form-control:focus {
        border-color: #d02a03;
        box-shadow: 0 0 0 2px rgba(208,42,3,0.1);
    }
    .footer-col-header .form-select { width: auto; max-width: 160px; }
    .footer-col-header .form-control { width: auto; min-width: 150px; }
    .footer-col-actions { display: flex; align-items: center; gap: 8px; margin-left: auto; }
    .footer-type-note {
        font-size: 0.75rem;
        color: #6c757d;
        padding: 0.375rem 0.5rem;
        background: #f4f6f9;
        border-radius: 4px;
        margin-top: 0.5rem;
    }
    .footer-links-block { margin-top: 0.5rem; }
    .footer-link-row {
        display: flex;
        align-items: center;
        gap: 0.375rem;
        margin-bottom: 0.375rem;
    }
    .footer-link-row .form-control { font-size: 0.8125rem; border-radius: 4px; border-color: #e9ecef; }
    .footer-link-row .form-control:focus { border-color: #d02a03; box-shadow: 0 0 0 2px rgba(208,42,3,0.1); }
    .footer-link-row .form-control.footer-link-label { width: 40%; }
    .footer-link-row .form-control.footer-link-url { width: calc(60% - 44px); }
    .footer-add-link {
        font-size: 0.75rem;
        padding: 0.25rem 0.625rem;
    }
    .footer-remove-btn {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        padding: 0;
        cursor: pointer;
        transition: .2s ease;
        border: 1px solid rgba(230,46,4,.2);
        background: rgba(230,46,4,.08);
        color: #e62e04;
        flex-shrink: 0;
    }
    .footer-remove-btn svg { width: 14px; height: 14px; }
    .footer-remove-btn:hover { background: #e62e04; border-color: #e62e04; color: #fff; }
    .footer-drag-handle {
        cursor: grab;
        color: #6c757d;
        display: flex;
        align-items: center;
        padding: 0.25rem;
        border-radius: 4px;
        transition: all 0.2s;
    }
    .footer-drag-handle:hover { color: #1f0300; background: #f4f6f9; }
    .footer-drag-handle:active { cursor: grabbing; }
    .footer-drag-handle svg { width: 16px; height: 16px; }
    .footer-col-sortable-placeholder {
        opacity: 0.4;
        border: 1px dashed #d02a03 !important;
        background: #d02a0308 !important;
    }
</style>

@push('scripts')
<script>
    var footerData = {!! $settings['footer_columns'] ?? '[]' !!};
    var footerTypes = ['links', 'newsletter', 'contact'];
    var footerSpans = [2, 3, 4, 6, 12];
    var socialPlatforms = [
        {id: 'facebook', label: 'Facebook', icon: 'fab fa-facebook-f'},
        {id: 'instagram', label: 'Instagram', icon: 'fab fa-instagram'},
        {id: 'twitter', label: 'Twitter / X', icon: 'fab fa-twitter'},
        {id: 'linkedin', label: 'LinkedIn', icon: 'fab fa-linkedin-in'},
        {id: 'youtube', label: 'YouTube', icon: 'fab fa-youtube'},
        {id: 'whatsapp', label: 'WhatsApp', icon: 'fab fa-whatsapp'},
        {id: 'pinterest', label: 'Pinterest', icon: 'fab fa-pinterest-p'},
        {id: 'tiktok', label: 'TikTok', icon: 'fab fa-tiktok'},
        {id: 'telegram', label: 'Telegram', icon: 'fab fa-telegram-plane'}
    ];

    var trashSvg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>';
    var closeSvg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
    var gripSvg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="5" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="9" cy="12" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="19" r="1"/></svg>';

    function initFooterTooltips() {
        document.querySelectorAll('.footer-remove-btn[title]:not([data-bs-tooltip-init])').forEach(function(el) {
            el.setAttribute('data-bs-tooltip-init', '1');
            new bootstrap.Tooltip(el, { trigger: 'hover' });
        });
    }

    function footerLinkRowHtml(link) {
        link = link || {};
        return '<div class="footer-link-row">' +
            '<input type="text" class="form-control footer-link-label" placeholder="Label" value="' + (link.label || '').replace(/"/g, '&quot;') + '">' +
            '<input type="text" class="form-control footer-link-url" placeholder="URL (e.g. /shop)" value="' + (link.url || '').replace(/"/g, '&quot;') + '">' +
            '<button type="button" class="footer-remove-btn footer-remove-link" title="Remove">' + closeSvg + '</button>' +
            '</div>';
    }

    function footerLinksBlock(col) {
        var links = (col && col.links) || [];
        var html = '<div class="footer-links-block">';
        for (var i = 0; i < links.length; i++) {
            html += footerLinkRowHtml(links[i]);
        }
        html += '<button type="button" class="btn btn-outline-primary btn-sm footer-add-link mt-1"><i class="fas fa-plus me-1"></i> Add Link</button>';
        html += '</div>';
        return html;
    }

    function footerSocialRowHtml(link) {
        link = link || {};
        var platformOpts = '';
        for (var i = 0; i < socialPlatforms.length; i++) {
            var p = socialPlatforms[i];
            platformOpts += '<option value="' + p.id + '"' + (link.platform === p.id ? ' selected' : '') + '>' + p.label + '</option>';
        }
        return '<div class="footer-link-row">' +
            '<select class="form-select form-select-sm footer-social-platform admin-footer-social-platform">' + platformOpts + '</select>' +
            '<input type="text" class="form-control footer-link-url footer-social-url" placeholder="URL" value="' + (link.url || '').replace(/"/g, '&quot;') + '">' +
            '<button type="button" class="footer-remove-btn footer-remove-social" title="Remove">' + closeSvg + '</button>' +
            '</div>';
    }

    function footerSocialBlock(col) {
        var links = (col && col.links) || [];
        var html = '<div class="footer-links-block">';
        for (var i = 0; i < links.length; i++) {
            html += footerSocialRowHtml(links[i]);
        }
        html += '<button type="button" class="btn btn-outline-primary btn-sm footer-add-social mt-1"><i class="fas fa-plus me-1"></i> Add Social Link</button>';
        html += '</div>';
        return html;
    }

    function renderFooterColumn(col, index) {
        col = col || {};
        var type = (footerTypes.indexOf(col.type) !== -1) ? col.type : 'links';
        var span = (footerSpans.indexOf(parseInt(col.span, 10)) !== -1) ? parseInt(col.span, 10) : 2;
        var heading = col.heading || '';

        var typeOptions = '';
        var typeLabels = {links: 'Links', newsletter: 'Newsletter', contact: 'Contact / Logo'};
        for (var i = 0; i < footerTypes.length; i++) {
            typeOptions += '<option value="' + footerTypes[i] + '"' + (type === footerTypes[i] ? ' selected' : '') + '>' + typeLabels[footerTypes[i]] + '</option>';
        }

        var spanOptions = '';
        var spanLabels = {2: 'lg-2 (narrow)', 3: 'lg-3', 4: 'lg-4 (wide)', 6: 'lg-6 (half)', 12: 'lg-12 (full)'};
        for (var i = 0; i < footerSpans.length; i++) {
            spanOptions += '<option value="' + footerSpans[i] + '"' + (span === footerSpans[i] ? ' selected' : '') + '>' + spanLabels[footerSpans[i]] + '</option>';
        }

        var html =
            '<div class="footer-col-card" data-index="' + index + '">' +
                '<div class="card-body py-2 px-3">' +
                    '<div class="footer-col-header">' +
                        '<span class="footer-drag-handle" title="Drag to reorder">' + gripSvg + '</span>' +
                        '<select class="form-select form-select-sm footer-col-type">' + typeOptions + '</select>' +
                        '<input type="text" class="form-control form-control-sm footer-col-heading" placeholder="Heading" value="' + heading.replace(/"/g, '&quot;') + '">' +
                        '<select class="form-select form-select-sm footer-col-span">' + spanOptions + '</select>' +
                        '<div class="footer-col-actions">' +
                            '<button type="button" class="footer-remove-btn footer-remove-col" title="Remove Column">' + trashSvg + '</button>' +
                        '</div>' +
                    '</div>' +
                    '<div class="footer-type-note' + (type === 'links' ? ' d-none' : '') + '" data-note="' + type + '">' +
                        (type === 'newsletter'
                            ? 'Add your social media links below. They will show under the newsletter form.'
                            : type === 'contact'
                                ? 'This column shows the footer logo plus the phone, email and address from Header Settings.'
                                : '') +
                    '</div>' +
                    (type === 'links' ? footerLinksBlock(col) : '') +
                    (type === 'newsletter' ? footerSocialBlock(col) : '') +
                '</div>' +
            '</div>';

        return html;
    }

    function rebuildFooterJson() {
        var cols = [];
        $('#footerColsContainer').children('.footer-col-card').each(function() {
            var $card = $(this);
            var type = $card.find('.footer-col-type').val();
            var span = parseInt($card.find('.footer-col-span').val(), 10) || 2;
            var heading = $card.find('.footer-col-heading').val().trim();
            var links = [];
            if (type === 'newsletter') {
                $card.find('.footer-links-block .footer-link-row').each(function() {
                    var $row = $(this);
                    var platform = $row.find('.footer-social-platform').val();
                    var url = $row.find('.footer-social-url').val().trim();
                    if (url !== '') {
                        links.push({platform: platform, url: url});
                    }
                });
            } else {
                $card.find('.footer-links-block .footer-link-row').each(function() {
                    var $row = $(this);
                    var label = $row.find('.footer-link-label').val().trim();
                    var url = $row.find('.footer-link-url').val().trim();
                    if (label !== '' || url !== '') {
                        links.push({label: label, url: url});
                    }
                });
            }
            cols.push({type: type, heading: heading, span: span, links: links});
        });
        $('#footerColumnsInput').val(JSON.stringify(cols));
    }

    $(document).ready(function() {
        if (footerData && footerData.length > 0) {
            for (var i = 0; i < footerData.length; i++) {
                $('#footerColsContainer').append(renderFooterColumn(footerData[i], i));
            }
        } else {
            $('#footerColsContainer').append(renderFooterColumn({
                type: 'links', heading: 'Quick Links', span: 3, links: []
            }, 0));
        }

        initFooterTooltips();

        if ($.fn && $.fn.sortable) {
            $('#footerColsContainer').sortable({
                handle: '.footer-drag-handle',
                placeholder: 'footer-col-sortable-placeholder',
                tolerance: 'pointer',
                update: function() {
                    $('#footerColsContainer').children('.footer-col-card').each(function(i) {
                        $(this).attr('data-index', i);
                    });
                }
            });
        }

        $('#addFooterColumn').on('click', function() {
            var index = $('#footerColsContainer').children('.footer-col-card').length;
            $('#footerColsContainer').append(renderFooterColumn({
                type: 'links', heading: '', span: 3, links: []
            }, index));
            initFooterTooltips();
        });

        $(document).on('click', '.footer-remove-col', function() {
            $(this).closest('.footer-col-card').fadeOut(150, function() { $(this).remove(); });
        });

        $(document).on('click', '.footer-add-link', function() {
            $(this).before(footerLinkRowHtml({}));
        });

        $(document).on('click', '.footer-remove-link', function() {
            $(this).closest('.footer-link-row').fadeOut(100, function() { $(this).remove(); });
        });

        $(document).on('click', '.footer-add-social', function() {
            $(this).before(footerSocialRowHtml({}));
        });

        $(document).on('click', '.footer-remove-social', function() {
            $(this).closest('.footer-link-row').fadeOut(100, function() { $(this).remove(); });
        });

        $(document).on('change', '.footer-col-type', function() {
            var $card = $(this).closest('.footer-col-card');
            var $body = $card.find('.card-body');
            var type = $(this).val();
            var $note = $card.find('.footer-type-note');

            $card.find('.footer-links-block').remove();
            $card.find('.newslatter-area').remove();

            if (type === 'links') {
                $note.addClass('d-none');
                $body.append(footerLinksBlock({links: []}));
                initFooterTooltips();
            } else if (type === 'newsletter') {
                $note.removeClass('d-none');
                $note.text('Add your social media links below. They will show under the newsletter form.');
                $body.append(footerSocialBlock({links: []}));
                initFooterTooltips();
            } else {
                $note.removeClass('d-none');
                $note.text('This column shows the footer logo plus the phone, email and address from Header Settings.');
            }
        });

        $('#footerColsForm').on('submit', function(e) {
            rebuildFooterJson();
        });
    });
</script>
@endpush
@endsection