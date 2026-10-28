/*  Searchable Select
    ------------------------------------------------------------------
    Kisi bhi native <select> me options zyada hon (default >= 10) to uske
    liye search box wala dropdown bana deta hai.

    - Native <select> DOM me hi rehta hai (sirf visually hidden), is liye
      form submit, HTML5 validation, aur pehle se likhe gaye saare
      change-handlers (jQuery included) waise hi kaam karte hain.
    - Options baad me JS/fetch se aate hon (vehicle make/model) to
      MutationObserver khud refresh kar deta hai.
    - Threshold change karna ho to select par data-searchable-min="N",
      aur kisi select ko skip karna ho to data-searchable="off".

    Dependencies: koi nahi (vanilla JS). form-select.js ke baad load karein.
*/
(function () {
    'use strict';

    var DEFAULT_MIN_OPTIONS = 10;
    var MAX_RENDER = 200; // ek waqt me max rendered rows (21k categories ke liye zaroori)
    var uid = 0;
    var instances = [];

    function optionCount(select) {
        return select.options ? select.options.length : 0;
    }

    function minOptions(select) {
        var v = parseInt(select.getAttribute('data-searchable-min'), 10);
        return isNaN(v) ? DEFAULT_MIN_OPTIONS : v;
    }

    function isEligible(select) {
        if (!select || select.tagName !== 'SELECT') return false;
        if (select.multiple) return false;
        if (parseInt(select.getAttribute('size'), 10) > 1) return false;
        if (select.getAttribute('data-ss-bound') === '1') return false;
        if (select.getAttribute('data-searchable') === 'off') return false;
        // Address fields ka apna searchable widget hai.
        if (select.closest('.address-suggest')) return false;
        if (select.closest('.nice-select')) return false;
        if (optionCount(select) < minOptions(select)) return false;
        return true;
    }

    function normalize(text) {
        return (text || '').replace(/\s+/g, ' ').trim().toLowerCase();
    }

    function findInstance(select) {
        for (var i = 0; i < instances.length; i++) {
            if (instances[i].select === select) return instances[i];
        }
        return null;
    }

    function syncValue(inst) {
        var select = inst.select;
        var opt = select.options[select.selectedIndex] || null;
        var label = opt ? (opt.text || '').replace(/\s+/g, ' ').trim() : '';

        if (!label) {
            label = select.getAttribute('data-placeholder') || 'Select';
        }
        if (inst.valueSpan.textContent !== label) {
            inst.valueSpan.textContent = label;
        }
        var isPlaceholder = !opt || opt.value === '';
        inst.control.classList.toggle('ss-placeholder', isPlaceholder);
        inst.control.title = label;
    }

    function syncDisabled(inst) {
        var disabled = inst.select.disabled;
        inst.control.classList.toggle('disabled', disabled);
        inst.control.setAttribute('aria-disabled', disabled ? 'true' : 'false');
        inst.control.tabIndex = disabled ? -1 : 0;
        if (disabled && inst.open) closePanel(inst);
    }

    function refresh(inst) {
        if (!inst) return;
        syncValue(inst);
        syncDisabled(inst);
        if (inst.open) renderList(inst, inst.search.value);
    }

    function positionPanel(inst) {
        var rect = inst.control.getBoundingClientRect();
        var panel = inst.panel;
        var width = rect.width || 220;
        var gap = 4;

        panel.style.width = width + 'px';
        panel.style.left = Math.max(8, Math.min(rect.left, window.innerWidth - width - 8)) + 'px';
        panel.style.top = (rect.bottom + gap) + 'px';
        panel.classList.remove('ss-panel--up');

        var ph = panel.offsetHeight;
        var fitsBelow = rect.bottom + gap + ph <= window.innerHeight - 8;
        var fitsAbove = rect.top - gap - ph >= 8;
        if (!fitsBelow && fitsAbove) {
            panel.classList.add('ss-panel--up');
            panel.style.top = (rect.top - gap - ph) + 'px';
        }
    }

    function openPanel(inst) {
        if (inst.select.disabled) return;
        // baaki khule hue dropdowns band karo
        for (var i = 0; i < instances.length; i++) {
            if (instances[i] !== inst && instances[i].open) closePanel(instances[i]);
        }
        inst.search.value = '';
        inst.active = -1;
        inst.panel.hidden = false;
        inst.open = true;
        inst.control.setAttribute('aria-expanded', 'true');
        inst.wrapper.classList.add('focused');
        renderList(inst, '');
        // currently selected option ko highlight karo (ArrowDown se next par jayega)
        var selectedOpt = inst.select.options[inst.select.selectedIndex] || null;
        if (selectedOpt) {
            var selIdx = inst.items.indexOf(selectedOpt);
            if (selIdx >= 0 && selIdx < inst.rendered) setActive(inst, selIdx);
        }
        positionPanel(inst);
        try { inst.search.focus({ preventScroll: true }); } catch (e) { inst.search.focus(); }
    }

    function closePanel(inst) {
        if (!inst.open) return;
        inst.open = false;
        inst.panel.hidden = true;
        inst.control.setAttribute('aria-expanded', 'false');
        inst.control.removeAttribute('aria-activedescendant');
        inst.wrapper.classList.remove('focused');
        inst.active = -1;
    }

    function renderList(inst, query) {
        var select = inst.select;
        var q = normalize(query);
        var list = inst.list;
        var frag = document.createDocumentFragment();

        list.innerHTML = '';
        inst.items = [];
        inst.rendered = 0;
        inst.active = -1;

        var shownGroups = {};
        var total = 0;

        for (var i = 0; i < select.options.length; i++) {
            var opt = select.options[i];
            if (!opt || opt.hidden) continue;

            var label = (opt.text || '').replace(/\s+/g, ' ').trim();
            if (!label && !opt.value) label = 'Select';
            total++;

            if (q && label.toLowerCase().indexOf(q) === -1) continue;

            var idx = inst.items.length;
            inst.items.push(opt);

            if (inst.items.length > MAX_RENDER) continue;

            // optgroup heading (sirf jab search empty ho)
            if (!q && opt.parentElement && opt.parentElement.tagName === 'OPTGROUP') {
                var groupLabel = opt.parentElement.label || '';
                if (groupLabel && !shownGroups[groupLabel]) {
                    shownGroups[groupLabel] = true;
                    var groupLi = document.createElement('li');
                    groupLi.className = 'ss-group';
                    groupLi.textContent = groupLabel;
                    frag.appendChild(groupLi);
                }
            }

            var li = document.createElement('li');
            li.setAttribute('role', 'option');
            li.setAttribute('data-idx', String(idx));
            li.id = inst.panel.id + '-opt-' + idx;
            li.setAttribute('aria-selected', opt.selected ? 'true' : 'false');
            li.textContent = label;
            if (opt.disabled) li.classList.add('ss-option-disabled');
            frag.appendChild(li);
            inst.rendered++;
        }

        if (!total) {
            var empty = document.createElement('li');
            empty.className = 'ss-empty';
            empty.textContent = 'No options available';
            frag.appendChild(empty);
        } else if (!inst.items.length) {
            var noMatch = document.createElement('li');
            noMatch.className = 'ss-empty';
            noMatch.textContent = 'No results found';
            frag.appendChild(noMatch);
        }

        list.appendChild(frag);

        if (inst.items.length > MAX_RENDER) {
            inst.footer.hidden = false;
            inst.footer.textContent = 'Showing first ' + MAX_RENDER + ' of ' + inst.items.length +
                ' matches — type to narrow down';
        } else {
            inst.footer.hidden = true;
            inst.footer.textContent = '';
        }
    }

    function setActive(inst, idx) {
        var lis = inst.list.querySelectorAll('li[role="option"]');
        for (var i = 0; i < lis.length; i++) lis[i].classList.remove('ss-active');
        if (idx < 0 || idx >= inst.rendered) {
            inst.active = -1;
            inst.control.removeAttribute('aria-activedescendant');
            return;
        }
        inst.active = idx;
        var li = lis[idx];
        if (li) {
            li.classList.add('ss-active');
            inst.control.setAttribute('aria-activedescendant', li.id);
            if (li.scrollIntoView) li.scrollIntoView({ block: 'nearest' });
        }
    }

    function moveActive(inst, dir) {
        if (!inst.rendered) return;
        var next = inst.active + dir;
        if (next < 0) next = inst.rendered - 1;
        if (next >= inst.rendered) next = 0;
        setActive(inst, next);
    }

    function choose(inst, opt) {
        if (!opt || opt.disabled) return;
        var before = inst.select.selectedIndex;
        opt.selected = true;
        var after = inst.select.selectedIndex;
        syncValue(inst);
        closePanel(inst);
        if (inst.control.focus) inst.control.focus({ preventScroll: true });
        if (before !== after) {
            inst.select.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    function bindSelect(inst) {
        var select = inst.select;

        if (window.MutationObserver) {
            var scheduled = false;
            var mo = new MutationObserver(function () {
                if (scheduled) return;
                scheduled = true;
                window.requestAnimationFrame(function () {
                    scheduled = false;
                    refresh(inst);
                });
            });
            mo.observe(select, {
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ['disabled', 'required', 'class']
            });
            inst.observer = mo;
        }

        select.addEventListener('change', function () {
            syncValue(inst);
        });
    }

    function attach(select) {
        if (!isEligible(select)) return;

        var wrapper = select.closest('.form-select-wrapper');
        if (!wrapper) {
            wrapper = document.createElement('span');
            wrapper.className = 'form-select-wrapper';
            select.parentNode.insertBefore(wrapper, select);
            wrapper.appendChild(select);
        }

        if (select.getAttribute('data-ss-bound') === '1') return;
        select.setAttribute('data-ss-bound', '1');

        // Native select abhi visible hai — width note kar lo (w-auto cases ke liye).
        var measured = select.getBoundingClientRect().width;

        var control = document.createElement('div');
        control.className = 'form-select ss-control';
        control.setAttribute('role', 'combobox');
        control.setAttribute('aria-haspopup', 'listbox');
        control.setAttribute('aria-expanded', 'false');
        control.tabIndex = 0;
        if (measured > 0) control.style.minWidth = Math.round(measured) + 'px';

        var valueSpan = document.createElement('span');
        valueSpan.className = 'ss-value';
        control.appendChild(valueSpan);

        var panel = document.createElement('div');
        panel.className = 'ss-panel';
        panel.id = 'ss-panel-' + (++uid);
        panel.hidden = true;

        var searchWrap = document.createElement('div');
        searchWrap.className = 'ss-search-wrap';
        var search = document.createElement('input');
        search.type = 'search';
        search.className = 'ss-search';
        search.setAttribute('autocomplete', 'off');
        search.setAttribute('aria-label', 'Search options');
        search.setAttribute('placeholder', select.getAttribute('data-search-placeholder') || 'Search…');
        searchWrap.appendChild(search);

        var list = document.createElement('ul');
        list.className = 'ss-list';
        list.setAttribute('role', 'listbox');

        var footer = document.createElement('div');
        footer.className = 'ss-more';
        footer.hidden = true;

        panel.appendChild(searchWrap);
        panel.appendChild(list);
        panel.appendChild(footer);
        document.body.appendChild(panel);

        // Control native select ke turant baad, wrapper ke andar.
        select.parentNode.insertBefore(control, select.nextSibling);
        select.classList.add('ss-native');
        select.setAttribute('tabindex', '-1');
        select.setAttribute('aria-hidden', 'true');

        var inst = {
            select: select,
            wrapper: wrapper,
            control: control,
            valueSpan: valueSpan,
            panel: panel,
            search: search,
            list: list,
            footer: footer,
            items: [],
            rendered: 0,
            active: -1,
            open: false
        };
        instances.push(inst);

        control.setAttribute('aria-controls', panel.id);

        syncValue(inst);
        syncDisabled(inst);
        bindSelect(inst);

        // ── Events ───────────────────────────────────────────────────────
        control.addEventListener('mousedown', function (e) {
            e.preventDefault();
        });
        control.addEventListener('click', function () {
            if (inst.open) closePanel(inst);
            else openPanel(inst);
        });
        control.addEventListener('keydown', function (e) {
            if (inst.select.disabled) return;
            if (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                e.preventDefault();
                if (!inst.open) openPanel(inst);
                if (e.key === 'ArrowDown') moveActive(inst, 1);
                if (e.key === 'ArrowUp') moveActive(inst, -1);
            } else if (e.key === 'Escape' && inst.open) {
                e.preventDefault();
                closePanel(inst);
            }
        });

        search.addEventListener('input', function () {
            renderList(inst, search.value);
        });
        search.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                moveActive(inst, 1);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                moveActive(inst, -1);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (inst.active >= 0 && inst.items[inst.active]) {
                    choose(inst, inst.items[inst.active]);
                } else if (inst.rendered === 1 && inst.items[0]) {
                    choose(inst, inst.items[0]);
                }
            } else if (e.key === 'Escape') {
                e.preventDefault();
                closePanel(inst);
                if (control.focus) control.focus({ preventScroll: true });
            } else if (e.key === 'Tab') {
                closePanel(inst);
            }
        });

        // mousedown par default rok kar focus search input par hi rakhte hain
        list.addEventListener('mousedown', function (e) {
            e.preventDefault();
        });
        list.addEventListener('click', function (e) {
            e.stopPropagation();
            var li = e.target.closest ? e.target.closest('li[data-idx]') : null;
            if (!li) return;
            var idx = parseInt(li.getAttribute('data-idx'), 10);
            if (!isNaN(idx) && inst.items[idx]) choose(inst, inst.items[idx]);
        });

        // Panel body me hai, is liye page ke doosre click-handlers (jaise
        // form-select.js ka .focused cleanup) is par na chalein.
        panel.addEventListener('click', function (e) {
            e.stopPropagation();
        });

        // Required select invalid ho to panel khol kar focus control par le aao
        // (native select off-screen hai, browser ka validation bubble dikhta nahi).
        select.addEventListener('invalid', function () {
            openPanel(inst);
            if (control.focus) control.focus({ preventScroll: true });
        });
    }

    function enhanceAll() {
        var els = document.querySelectorAll(
            'select.form-select:not([data-ss-bound="1"]), select[data-searchable]:not([data-ss-bound="1"])'
        );
        for (var i = 0; i < els.length; i++) attach(els[i]);
    }

    function init() {
        enhanceAll();

        if (window.MutationObserver && document.body) {
            var scheduled = false;
            var observer = new MutationObserver(function () {
                if (scheduled) return;
                scheduled = true;
                window.setTimeout(function () {
                    scheduled = false;
                    enhanceAll();
                }, 60);
            });
            observer.observe(document.body, { childList: true, subtree: true });
        }
    }

    document.addEventListener('click', function (e) {
        for (var i = 0; i < instances.length; i++) {
            var inst = instances[i];
            if (!inst.open) continue;
            if (inst.panel.contains(e.target) || inst.control.contains(e.target)) continue;
            closePanel(inst);
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        for (var i = 0; i < instances.length; i++) {
            if (instances[i].open) closePanel(instances[i]);
        }
    });

    document.addEventListener('scroll', function (e) {
        for (var i = 0; i < instances.length; i++) {
            var inst = instances[i];
            if (!inst.open) continue;
            if (inst.panel.contains(e.target)) continue; // list ke andar scroll
            closePanel(inst);
        }
    }, true);

    window.addEventListener('resize', function () {
        for (var i = 0; i < instances.length; i++) {
            if (instances[i].open) positionPanel(instances[i]);
        }
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
