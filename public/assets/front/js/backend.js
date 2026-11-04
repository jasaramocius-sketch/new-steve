(function() {
    'use strict';

    function initializeAdmin() {
        if (window.jQuery) {
            var flashMessages = document.body.dataset;
            toastr.options = { positionClass: 'toast-top-right', timeOut: 3000, progressBar: true };
            if (flashMessages.success) toastr.success(flashMessages.success);
            if (flashMessages.error) toastr.error(flashMessages.error);
            if (flashMessages.warning) toastr.warning(flashMessages.warning);
            if (flashMessages.info) toastr.info(flashMessages.info);

            document.querySelectorAll('textarea.texteditor, textarea#editor').forEach(function(editor) {
                if (window.marked && editor.value && !/<[a-z][^>]*>/i.test(editor.value)) {
                    var raw = editor.value.replace(/^[ \t]*\u2022[ \t]+/gm, '- ');
                    editor.value = marked.parse(raw);
                }
                $(editor).summernote({ placeholder: 'WYSIWYG rich text editor', tabsize: 2, height: 250 });
            });
        }

        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(element) {
            new bootstrap.Tooltip(element, { trigger: 'hover' });
        });
        document.querySelectorAll('.action-btn[title]:not([data-bs-toggle="tooltip"]), .footer-remove-btn[title]:not([data-bs-toggle="tooltip"])').forEach(function(element) {
            new bootstrap.Tooltip(element, { trigger: 'hover' });
        });

        var sidebar = document.querySelector('.admin-sidebar');
        var toggle = document.getElementById('sidebarToggle');
        var overlay = document.querySelector('.admin-sidebar-overlay');
        var closeButton = document.getElementById('sidebarCloseBtn');
        function closeSidebar() {
            if (!sidebar) return;
            sidebar.classList.remove('open');
            document.body.classList.remove('sidebar-open');
        }
        if (toggle && sidebar) toggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
            document.body.classList.toggle('sidebar-open');
        });
        if (closeButton) closeButton.addEventListener('click', closeSidebar);
        if (overlay) overlay.addEventListener('click', closeSidebar);

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                document.querySelectorAll('.tooltip.show').forEach(function(tooltip) {
                    tooltip.classList.remove('show');
                    tooltip.style.display = '';
                });
                if (sidebar && sidebar.classList.contains('open')) closeSidebar();
            }
        });
        document.addEventListener('scroll', function() {
            document.querySelectorAll('.tooltip.show').forEach(function(tooltip) {
                tooltip.classList.remove('show');
                tooltip.style.pointerEvents = 'none';
            });
        }, true);
        document.addEventListener('error', function(event) {
            var image = event.target;
            if (image.tagName !== 'IMG' || image.hasAttribute('data-fallback')) return;
            image.setAttribute('data-fallback', '1');
            var baseMeta = document.querySelector('meta[name="base-url"]');
            var baseUrl = ((baseMeta && baseMeta.getAttribute('content')) || '').replace(/\/+$/, '');
            image.src = baseUrl + '/assets/images/placeholder.png';
        }, true);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeAdmin);
    } else {
        initializeAdmin();
    }
})();
