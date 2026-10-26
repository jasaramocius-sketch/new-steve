(function () {
    'use strict';

    function closeFormSelects() {
        document.querySelectorAll('.form-select-wrapper.focused').forEach(function (w) {
            w.classList.remove('focused');
        });
    }

    function attachSelect(el) {
        var wrapper = el.parentElement;
        if (!wrapper || wrapper.classList.contains('form-select-wrapper') === false) {
            wrapper = document.createElement('span');
            wrapper.className = 'form-select-wrapper';
            el.parentNode.insertBefore(wrapper, el);
            wrapper.appendChild(el);
        }
        if (el.getAttribute('data-fsw-bound') === '1') {
            return;
        }
        el.setAttribute('data-fsw-bound', '1');
        el.addEventListener('mousedown', function () {
            wrapper.classList.toggle('focused');
        });
        el.addEventListener('blur', function () {
            wrapper.classList.remove('focused');
        });
        el.addEventListener('change', function () {
            wrapper.classList.remove('focused');
        });
        el.addEventListener('keydown', function (e) {
            if (e.key === ' ' || e.key === 'Enter') {
                wrapper.classList.toggle('focused');
            }
        });
    }

    function init() {
        var els = document.querySelectorAll('.form-select');
        for (var i = 0; i < els.length; i++) {
            attachSelect(els[i]);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    if (window.MutationObserver) {
        var observer = new MutationObserver(function () {
            init();
        });
        if (document.body) {
            observer.observe(document.body, { childList: true, subtree: true });
        }
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeFormSelects();
        }
    }, true);
    document.addEventListener('keyup', function (e) {
        if (e.key === 'Escape') {
            closeFormSelects();
        }
    }, true);
    document.addEventListener('scroll', function () {
        closeFormSelects();
    }, true);
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.form-select-wrapper')) {
            closeFormSelects();
        }
    });
})();