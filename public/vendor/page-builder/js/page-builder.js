/**
 * Laravel Page Builder - Frontend JS
 * Handles block CRUD, drag-drop reordering, image uploads, and AJAX save.
 */
(function ($) {
    'use strict';

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    const PB = {
        container: null,
        modelType: null,
        modelId: null,
        saveUrl: null,
        uploadUrl: null,
        formUrl: null,
        storageUrl: null,
        baseUrl: null,
        blockIndex: 0,
        isDirty: false,
        _pendingInput: null,
        _pendingPreview: null,
        _galleryList: null,
        _galleryBlockIndex: null,

        init: function () {
            this.container = $('#page-builder-app');
            if (!this.container.length) return;

            this.modelType = this.container.data('model-type');
            this.modelId = this.container.data('model-id');
            this.saveUrl = this.container.data('save-url');
            this.uploadUrl = this.container.data('upload-url');
            this.formUrl = this.container.data('form-url');
            this.storageUrl = this.container.data('storage-url');
            this.baseUrl = (this.container.data('base-url') || '').replace(/\/$/, '');
            this.blockIndex = this.container.find('.pb-block-item').length;

            this.bindEvents();
            this.initSortable();
        },

        bindEvents: function () {
            $(document).on('click', '#pb-add-block-btn', this.togglePicker.bind(this));
            $(document).on('click', '.pb-picker-item', this.addBlock.bind(this));
            $(document).on('click', '#pb-save-btn', this.save.bind(this));
            $(document).on('click', '.pb-delete-btn', this.deleteBlock.bind(this));
            $(document).on('click', '.pb-duplicate-btn', this.duplicateBlock.bind(this));
            $(document).on('click', '.pb-toggle-btn', this.toggleBlock.bind(this));
            $(document).on('click', '.pb-upload-btn', this.openUpload.bind(this));
            $(document).on('click', '.pb-gallery-add', this.openGalleryUpload.bind(this));
            $(document).on('click', '.pb-gallery-remove', this.removeGalleryImage.bind(this));
            $(document).on('click', '.pb-repeater-add', this.addRepeater.bind(this));
            $(document).on('click', '.pb-repeater-remove', this.removeRepeater.bind(this));
            $(document).on('click', '#pb-preview-btn', this.togglePreview.bind(this));
            $(document).on('input', '.pb-color-picker', this.syncColorToText.bind(this));
            $(document).on('input', '.pb-color-text', this.syncTextToColor.bind(this));
            $(document).on('click', '.pb-color-clear', this.clearColor.bind(this));

            $(document).on('click', function (e) {
                if (!$(e.target).closest('.pb-add-block-dropdown').length) {
                    $('#pb-block-picker').hide();
                }
            });

            $(window).on('beforeunload', function () {
                if (PB.isDirty) return 'You have unsaved blocks.';
            });
        },

        initSortable: function () {
            if (typeof Sortable !== 'undefined') {
                Sortable.create(document.getElementById('pb-blocks-list'), {
                    handle: '.pb-drag-handle',
                    animation: 200,
                    onEnd: function () {
                        PB.isDirty = true;
                    }
                });
            }
        },

        togglePicker: function (e) {
            e.preventDefault();
            $('#pb-block-picker').toggle();
        },

        addBlock: function (e) {
            e.preventDefault();
            const type = $(e.currentTarget).data('type');
            const index = this.blockIndex++;

            $.ajax({
                url: this.formUrl,
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    type: type,
                    index: index,
                    data: {},
                },
                success: function (res) {
                    if (res.success) {
                        const html = '<div class="pb-block-item" data-index="' + index + '" data-type="' + type + '">' +
                            '<div class="pb-block-header">' +
                            '<span class="pb-drag-handle"><i class="fas fa-grip-vertical"></i></span>' +
                            '<span class="pb-block-label"><i class="' + (res.fields._icon || 'fas fa-cube') + ' me-1"></i>' + type + '</span>' +
                            '<div class="pb-block-actions">' +
                            '<button type="button" class="pb-action-btn pb-toggle-btn" title="Toggle"><i class="fas fa-chevron-down"></i></button>' +
                            '<button type="button" class="pb-action-btn pb-duplicate-btn" title="Duplicate"><i class="fas fa-clone"></i></button>' +
                            '<button type="button" class="pb-action-btn pb-delete-btn" title="Delete"><i class="fas fa-trash"></i></button>' +
                            '</div></div>' +
                            '<div class="pb-block-body">' + res.html + '</div></div>';

                        $('#pb-blocks-list').append(html);
                        PB.isDirty = true;
                        PB.updateEmptyState();
                        $('#pb-block-picker').hide();
                    }
                }
            });
        },

        save: function (e) {
            e.preventDefault();
            const btn = $('#pb-save-btn');
            const form = this.serializeBlocks();

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Saving...');

            $.ajax({
                url: this.saveUrl,
                method: 'POST',
                data: form,
                success: function (res) {
                    if (res.success) {
                        PB.isDirty = false;
                        if (typeof toastr !== 'undefined') {
                            toastr.success('Blocks saved successfully!');
                        } else {
                            alert('Blocks saved successfully!');
                        }
                    }
                },
                error: function (xhr) {
                    const msg = xhr.responseJSON?.error || 'Save failed';
                    if (typeof toastr !== 'undefined') {
                        toastr.error(msg);
                    } else {
                        alert(msg);
                    }
                },
                complete: function () {
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i>Save Blocks');
                }
            });
        },

        url: function (path) {
            if (!path) return '';
            path = String(path);
            if (/^(https?:)?\/\//i.test(path) || path.indexOf('data:') === 0 || path.indexOf('blob:') === 0) return path;
            if (path.indexOf('/uploads/') === 0 || path.indexOf('uploads/') === 0) {
                return this.storageUrl + path.replace(/^\//, '');
            }
            return this.baseUrl + '/' + path.replace(/^\//, '');
        },

        serializeBlocks: function () {
            const blocks = [];
            const isIndexKey = function (key) {
                return /^\d+$/.test(key);
            };
            const setPath = function (obj, path, value) {
                let ref = obj;

                for (let i = 0; i < path.length - 1; i++) {
                    const key = path[i];
                    const nextKey = path[i + 1];

                    if (typeof ref[key] !== 'object' || ref[key] === null || Array.isArray(ref[key]) !== isIndexKey(nextKey)) {
                        ref[key] = isIndexKey(nextKey) ? [] : {};
                    }

                    ref = ref[key];
                }

                ref[path[path.length - 1]] = value;
            };

            $('#pb-blocks-list .pb-block-item').each(function () {
                const $item = $(this);
                const type = $item.data('type');
                const data = {};

                $item.find('.pb-block-body :input').each(function () {
                    const $input = $(this);
                    const name = $input.attr('name');
                    if (!name) return;

                    const marker = '[data]';
                    const at = name.indexOf(marker);
                    if (at === -1) return;

                    const path = name.slice(at + marker.length).replace(/\]$/, '').split('][');
                    if (!path.length || !path[0]) return;

                    let value;
                    if ($input.is(':checkbox')) {
                        value = $input.is(':checked') ? '1' : '0';
                    } else if ($input.is('select')) {
                        const raw = $input.val();
                        value = Array.isArray(raw) ? raw : (raw === null ? '' : raw);
                    } else {
                        value = $input.val();
                    }

                    setPath(data, path, value);
                });

                blocks.push({ type: type, data: data, id: $item.data('id') || null });
            });

            return {
                _token: $('meta[name="csrf-token"]').attr('content'),
                model_type: this.modelType,
                model_id: this.modelId,
                blocks: blocks,
            };
        },

        deleteBlock: function (e) {
            e.preventDefault();
            if (!confirm('Delete this block?')) return;
            $(e.currentTarget).closest('.pb-block-item').fadeOut(300, function () {
                $(this).remove();
                PB.isDirty = true;
                PB.updateEmptyState();
            });
        },

        duplicateBlock: function (e) {
            e.preventDefault();
            const $item = $(e.currentTarget).closest('.pb-block-item');
            const $clone = $item.clone();
            $clone.attr('data-index', PB.blockIndex);
            $clone.removeAttr('data-id');
            $clone.removeData('id');
            $clone.find(':input').each(function () {
                const name = $(this).attr('name');
                if (name) {
                    $(this).attr('name', name.replace(/\[(\d+)\]/, '[' + PB.blockIndex + ']'));
                }
            });
            $item.after($clone);
            PB.blockIndex++;
            PB.isDirty = true;
        },

        toggleBlock: function (e) {
            e.preventDefault();
            const $item = $(e.currentTarget).closest('.pb-block-item');
            const $body = $item.find('.pb-block-body');
            const $icon = $(e.currentTarget).find('i');

            $body.slideToggle(200);
            $icon.toggleClass('fa-chevron-down fa-chevron-up');
        },

        openUpload: function (e) {
            e.preventDefault();
            const $upload = $(e.currentTarget).closest('.pb-image-upload');
            PB._pendingInput = $upload.find('.pb-image-input')[0];
            PB._pendingPreview = $upload.find('.pb-image-preview')[0];
            if (typeof impOpen_pb_single === 'function') {
                impOpen_pb_single();
            }
        },

        openGalleryUpload: function (e) {
            e.preventDefault();
            const $list = $(e.currentTarget).closest('.pb-gallery-container').find('.pb-gallery-list');
            PB._galleryList = $list[0];
            PB._galleryBlockIndex = $list.closest('.pb-block-item').data('index');
            if (typeof impOpen_pb_multi === 'function') {
                impOpen_pb_multi();
            }
        },

        removeGalleryImage: function (e) {
            e.preventDefault();
            $(e.currentTarget).closest('.pb-gallery-item').remove();
            PB.isDirty = true;
        },

        addRepeater: function (e) {
            e.preventDefault();
            const templateType = $(e.currentTarget).data('template');
            const $repeater = $(e.currentTarget).closest('.pb-repeater');
            const index = $repeater.find('.pb-repeater-item').length;
            const $template = $('#pb-repeater-template-' + templateType);

            if ($template.length) {
                let html = $template.html()
                    .replace(/__INDEX__/g, index)
                    .replace(/__NUM__/g, index + 1);
                $repeater.find('.pb-repeater-add').before(html);
                PB.isDirty = true;
            }
        },

        removeRepeater: function (e) {
            e.preventDefault();
            $(e.currentTarget).closest('.pb-repeater-item').remove();
            PB.isDirty = true;
        },

        togglePreview: function (e) {
            e.preventDefault();
            const $body = $('body');
            $body.toggleClass('pb-preview-mode');
        },

        updateEmptyState: function () {
            const count = $('#pb-blocks-list .pb-block-item').length;
            $('.pb-empty-state').toggle(count === 0);
        },

        syncColorToText: function (e) {
            $(e.currentTarget).closest('.pb-color-wrap').find('.pb-color-text').val(e.target.value);
        },

        syncTextToColor: function (e) {
            const val = e.target.value;
            if (/^#[0-9A-Fa-f]{0,6}$/.test(val)) {
                $(e.currentTarget).closest('.pb-color-wrap').find('.pb-color-picker').val(val);
            }
        },

        clearColor: function (e) {
            const $wrap = $(e.currentTarget).closest('.pb-color-wrap');
            $wrap.find('.pb-color-picker').val('#000000');
            $wrap.find('.pb-color-text').val('');
        }
    };

    $(document).ready(function () {
        PB.init();

        window._pbOrigConfirmSingle = window['impConfirm_pb_single'];
        window['impConfirm_pb_single'] = function () {
            if (window._pbOrigConfirmSingle) window._pbOrigConfirmSingle();
            var relay = document.querySelector('input[name="image_from_manager_pb_single"]');
            if (relay && relay.value && PB._pendingInput) {
                PB._pendingInput.value = relay.value;
                var url = PB.url(relay.value);
                if (PB._pendingPreview) {
                    PB._pendingPreview.innerHTML = '<img src="' + url + '" alt="Selected" style="max-width:100%;">';
                }
                PB.isDirty = true;
            }
            if (relay) relay.value = '';
            PB._pendingInput = null;
            PB._pendingPreview = null;
        };

        window._pbOrigConfirmMulti = window['impConfirm_pb_multi'];
        window['impConfirm_pb_multi'] = function () {
            if (window._pbOrigConfirmMulti) window._pbOrigConfirmMulti();
            var relay = document.querySelector('input[name="image_from_manager_pb_multi"]');
            if (relay && relay.value && PB._galleryList) {
                var paths;
                try { paths = JSON.parse(relay.value); } catch (e) { paths = [relay.value]; }
                if (!Array.isArray(paths)) paths = [paths];
                var blockIndex = PB._galleryBlockIndex;
                paths.forEach(function (path) {
                    var imgIndex = $(PB._galleryList).find('.pb-gallery-item').length;
                    var item = '<div class="pb-gallery-item" data-index="' + imgIndex + '">' +
                        '<img src="' + PB.url(path) + '" alt="Gallery">' +
                        '<input type="hidden" name="blocks[' + blockIndex + '][data][images][' + imgIndex + ']" value="' + path + '">' +
                        '<button type="button" class="pb-gallery-remove" title="Remove"><i class="fas fa-times"></i></button>' +
                        '</div>';
                    $(PB._galleryList).append(item);
                });
                PB.isDirty = true;
            }
            if (relay) relay.value = '';
            PB._galleryList = null;
            PB._galleryBlockIndex = null;
        };
    });

})(jQuery);
