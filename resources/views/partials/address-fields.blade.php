{{--
    Shared address fields partial — single consistent style for every address form.
    Country is a dropdown; State & City fields show a custom suggestion dropdown
    (below the field). Countries come from the DB server-side; States and Cities
    are fetched via AJAX (GET /address/states, /address/cities) only when a
    country/state is actually chosen, and cached in memory.

    Params:
      $prefix           (string, default 'addr') id prefix, must be unique per form
      $value            (object/array/null) existing address for prefill
      $withFullName     (bool) show Full Name field
      $withPhone        (bool) show Phone field
      $withDefault      (bool) show "Set as default" checkbox
      $required         (array) override which fields are required, e.g. ['country','city','zip_code']
      $zipName          (string, default 'zip_code') name attribute for the zip input
--}}
@php
    $p = $prefix ?? 'addr';
    $v = $value ?? null;
    $required = $required ?? ['full_name', 'phone', 'address', 'country', 'state', 'city', 'zip_code'];
    $get = function ($field) use ($v) {
        $oldVal = old($field);
        if ($oldVal !== null) return $oldVal;
        if (is_object($v) && isset($v->{$field})) return $v->{$field};
        if (is_array($v) && array_key_exists($field, $v)) return $v[$field];
        return '';
    };
    $req = function ($field) use ($required) {
        return in_array($field, $required, true);
    };
    $countries = \App\Helpers\AddressHelper::countries();
    // Country starts empty on new forms; only prefilled when editing an existing
    // address. State/City stay disabled until a country is actually chosen.
    $savedCountry = trim((string) $get('country'));
    // Frequent destination countries pinned to the top of the dropdown.
    $topCountries = array_values(array_filter(
        ['United States', 'Canada', 'United Kingdom', 'Australia', 'Germany', 'France', 'India', 'United Arab Emirates'],
        function ($name) use ($countries, $savedCountry) {
            return $name !== $savedCountry && in_array($name, $countries, true);
        }
    ));
    $restCountries = array_values(array_filter(
        $countries,
        function ($name) use ($topCountries, $savedCountry) {
            return $name !== $savedCountry && !in_array($name, $topCountries, true);
        }
    ));
@endphp

<div class="row g-3 address-fields-row">
    @if($withFullName ?? false)
        <div class="col-md-6">
            <label class="form-label fs-14" for="{{ $p }}_full_name">Full Name @if($req('full_name'))<span class="text-danger">*</span>@endif</label>
            <input type="text" name="full_name" id="{{ $p }}_full_name" class="form-control @error('full_name') is-invalid @enderror" value="{{ $get('full_name') }}" placeholder="John Doe" @if($req('full_name'))required @endif>
            @error('full_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
    @endif
    @if($withPhone ?? false)
        <div class="col-md-6">
            <label class="form-label fs-14" for="{{ $p }}_phone">Phone @if($req('phone'))<span class="text-danger">*</span>@endif</label>
            <input type="tel" name="phone" id="{{ $p }}_phone" class="form-control @error('phone') is-invalid @enderror" inputmode="numeric" value="{{ $get('phone') }}" placeholder="+1 (234) 567-890" pattern="[0-9+\-\s()]{7,20}" title="Enter a valid phone number" @if($req('phone'))required @endif>
            @error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
    @endif
    <div class="col-md-6 address-country-wrapper select-dropdown-icon">
        <label class="form-label fs-14" for="{{ $p }}_country_search">Country @if($req('country'))<span class="text-danger">*</span>@endif</label>
        <div class="address-suggest">
            <div class="form-select address-suggest-trigger @error('country') is-invalid @enderror" id="{{ $p }}_country_search" data-placeholder="Select your Country" role="combobox" aria-haspopup="listbox" aria-expanded="false" tabindex="0"></div>
            <ul class="address-suggest-list" role="listbox"></ul>
        <select name="country" id="{{ $p }}_country" class="address-country-select @error('country') is-invalid @enderror" data-prefix="{{ $p }}" @if($req('country'))required @endif>
            <option value="">Select Country</option>
            @if($savedCountry !== '')
                <option value="{{ $savedCountry }}" selected>{{ $savedCountry }}</option>
            @endif
            @foreach($topCountries as $countryName)
                <option value="{{ $countryName }}">{{ $countryName }}</option>
            @endforeach
            @foreach($restCountries as $countryName)
                <option value="{{ $countryName }}">{{ $countryName }}</option>
            @endforeach
        </select>
        </div>
        @error('country')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 address-state-wrapper select-dropdown-icon">
        <label class="form-label fs-14" for="{{ $p }}_state_search">State @if($req('state'))<span class="text-danger">*</span>@endif</label>
        <div class="address-suggest">
            <div class="form-select address-suggest-trigger @error('state') is-invalid @enderror" id="{{ $p }}_state_search" data-placeholder="Select your State" role="combobox" aria-haspopup="listbox" aria-expanded="false" tabindex="0"></div>
            <ul class="address-suggest-list" role="listbox"></ul>
        <select name="state" id="{{ $p }}_state" class="address-state-select @error('state') is-invalid @enderror" data-prefix="{{ $p }}" data-selected="{{ $get('state') }}" data-required="{{ $req('state') ? '1' : '0' }}" @if($req('state'))required @endif>
            <option value="">Select State</option>
            @if($get('state') !== '')
                <option value="{{ $get('state') }}" selected>{{ $get('state') }}</option>
            @endif
        </select>
        </div>
        @error('state')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6 address-city-wrapper select-dropdown-icon">
        <label class="form-label fs-14" for="{{ $p }}_city_search">City @if($req('city'))<span class="text-danger">*</span>@endif</label>
        <div class="address-suggest">
            <div class="form-select address-suggest-trigger @error('city') is-invalid @enderror" id="{{ $p }}_city_search" data-placeholder="Select your City" role="combobox" aria-haspopup="listbox" aria-expanded="false" tabindex="0"></div>
            <ul class="address-suggest-list" role="listbox"></ul>
        <select name="city" id="{{ $p }}_city" class="address-city-select @error('city') is-invalid @enderror" data-prefix="{{ $p }}" data-selected="{{ $get('city') }}" data-required="{{ $req('city') ? '1' : '0' }}" @if($req('city'))required @endif>
            <option value="">Select City</option>
            @if($get('city') !== '')
                <option value="{{ $get('city') }}" selected>{{ $get('city') }}</option>
            @endif
        </select>
        </div>
        @error('city')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label fs-14" for="{{ $p }}_zip">Zip Code / Postal Code @if($req('zip_code'))<span class="text-danger">*</span>@endif</label>
        <input type="text" name="{{ $zipName ?? 'zip_code' }}" id="{{ $p }}_zip" class="form-control @error('zip_code') is-invalid @enderror" inputmode="text" value="{{ $get($zipName ?? 'zip_code') }}" placeholder="10001" pattern="[A-Za-z0-9\-\s]{3,20}" title="Enter a valid zip code or postal code" @if($req('zip_code'))required @endif>
        @error($zipName ?? 'zip_code')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label class="form-label fs-14" for="{{ $p }}_address">Address @if($req('address'))<span class="text-danger">*</span>@endif</label>
        <textarea
    name="address"
    id="{{ $p }}_address"
    class="form-control @error('address') is-invalid @enderror"
    rows="3"
    placeholder="123 Street Name"
    @if($req('address')) required @endif
>{{ $get('address') }}</textarea>
        @error('address')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>
    @if($withDefault ?? false)
        <div class="col-12">
            <div class="form-check">
                <input type="checkbox" name="set_default" id="{{ $p }}_set_default" class="form-check-input" value="1" {{ $get('set_default') ? 'checked' : '' }}>
                <label class="form-check-label fs-14" for="{{ $p }}_set_default">Set as default address</label>
            </div>
        </div>
    @endif
</div>

@once
<style>
    .address-suggest { position: relative; }

    /* Real <select> kept for submission/validation but visually hidden
       (NOT display:none — display:none fields are dropped from form
       submission and native constraint validation). */
    .address-suggest select {
        position: absolute;
        left: -9999px;
        width: 1px;
        height: 1px;
        opacity: 0;
        pointer-events: none;
        border: 0;
        padding: 0;
        margin: 0;
    }

    .address-suggest-trigger {
        cursor: pointer;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .address-suggest-trigger.disabled {
        background-color: #e9ecef;
        color: var(--bs-body-color);
        cursor: not-allowed;
        opacity: 1;
    }

    /* Dropdown always opens below the field — never flips upward. */
    .address-suggest-list {
        position: absolute;
        top: calc(100% + 4px);
        bottom: auto;
        left: 0;
        right: 0;
        width: 100%;
        max-height: 220px;
        overflow-y: auto;
        z-index: 1060;
        background: #fff;
        border: 1px solid #dee2e6;
        border-radius: 6px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, .12);
        list-style: none;
        margin: 0;
        padding: 0;
        display: none;
    }
    .address-suggest-list.open { display: block; }

    .address-suggest-search {
        position: sticky;
        top: 0;
        padding: 6px 8px;
        background: #fff;
        border-bottom: 1px solid #eef0f2;
    }
    .address-suggest-search-input {
        width: 100%;
        padding: 5px 10px;
        font-size: 14px;
        border: 1px solid #ced2d9;
        border-radius: 4px;
        outline: none;
        background-color: #fff;
    }

    .address-suggest-list li {
        padding: 8px 12px;
        cursor: pointer;
        font-size: 14px;
        color: #333;
    }
    .address-suggest-list li:hover,
    .address-suggest-list li.active,
    .address-suggest-list li.selected { background: #f2f4f7; }
    .address-suggest-list li.empty { cursor: default; color: #999; }
    .address-suggest-list li.placeholder-item { color: #999; cursor: pointer; }

    /* Keep a single consistent chevron for all address selects. */
    .address-fields-row .form-select-wrapper::after,
    .address-fields-row .nice-select::after {
        content: '';
        position: absolute;
        top: 50%;
        right: 16px;
        width: 10px;
        height: 10px;
        background: url(data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e) no-repeat center / contain;
        border-right: 2px solid #000;
        border-bottom: 2px solid #000;
        transform: translateY(-70%) rotate(45deg);
        transition: transform .2s ease;
        pointer-events: none;
    }

    .address-fields-row .form-select-wrapper.focused::after,
    .address-fields-row .nice-select.open::after {
        transform: translateY(-30%) rotate(225deg);
    }

    .address-fields-row .nice-select {
        width: 100%;
        float: none;
        min-width: 0;
        height: auto;
        min-height: 35px;
        border: solid 1px #dee2e6;
        position: relative;
    }
    .address-fields-row .nice-select .current {
        display: block;
        padding: 2px 16px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: var(--bs-body-color);
    }

    .address-fields-row .nice-select.open-down .list {
        top: calc(100% + 4px) !important;
        bottom: auto !important;
    }
    .address-fields-row .nice-select .list {
        height: 200px;
        overflow-y: auto;
    }
    .address-fields-row .nice-select:focus{
        box-shadow:unset;
        color: var(--bs-body-color);
        border-color: var(--bs-body-color);
    }
</style>
<script>
(function () {

    var ADDR_STATES_URL = @json(route('location.states'));
    var ADDR_CITIES_URL = @json(route('location.cities'));

    // ── Lazy AJAX loader: states/cities fetched from the DB only when a
    //    country/state is actually chosen. Results are cached in memory so
    //    re-selecting the same country/state never triggers another request.
    var __addrCache    = { state: {}, city: {} };
    var __addrPending  = { state: {}, city: {} };

    function loadList(kind, country, state) {
        var key = (country || '').trim() + '|' + (state || '').trim();
        if (__addrCache[kind][key]) return Promise.resolve(__addrCache[kind][key]);
        if (__addrPending[kind][key]) return __addrPending[kind][key];

        var url = kind === 'state'
            ? ADDR_STATES_URL + '?country=' + encodeURIComponent(country)
            : ADDR_CITIES_URL + '?country=' + encodeURIComponent(country) + '&state=' + encodeURIComponent(state);

        __addrPending[kind][key] = fetch(url, { cache: 'no-cache' })
            .then(function (response) {
                if (!response.ok) throw new Error('Location data failed: ' + response.status);
                return response.json();
            })
            .then(function (data) {
                var respKey = kind === 'state' ? 'states' : 'cities';
                var list = (data && data[respKey]) || [];
                __addrCache[kind][key] = list;
                return list;
            })
            .catch(function (err) {
                delete __addrPending[kind][key];
                throw err;
            });

        return __addrPending[kind][key];
    }

    // ── Saved value of a state/city select ──────────────────────────────────
    // data-selected is the source of truth for a pre-existing value (set
    // server-side or by prefill code). The .value fallback covers external
    // setters, but the internal "__loading__" placeholder must never be
    // treated as a real selection.
    function savedValue(el) {
        if (!el) return '';
        var v = (el.getAttribute('data-selected') || '').trim();
        if (v && v !== '__loading__') return v;
        return '';
    }

    // Show a disabled "Loading…" option while an AJAX request is in flight.
    function setLoading(el, placeholder) {
        if (!el) return;
        el.innerHTML = '';
        var opt = document.createElement('option');
        opt.value = '__loading__';
        opt.textContent = 'Loading…';
        opt.disabled = true;
        opt.selected = true;
        el.appendChild(opt);
        el.disabled = true;
        el.required = false;
    }

    // ── Fill a <select> with options and refresh its nice-select wrapper ──────────
    function fillSelect(el, items, selectedVal, enabled, placeholder) {
        if (!el) return;
        el.innerHTML = '';

        var first = document.createElement('option');
        first.value = '';
        first.textContent = placeholder || 'Select';
        el.appendChild(first);

        if (enabled && Array.isArray(items) && items.length) {
            var list = items.slice();
            if (selectedVal === '__loading__') selectedVal = '';
            if (selectedVal && list.indexOf(selectedVal) === -1) list.unshift(selectedVal);
            list.forEach(function(val) {
                var o = document.createElement('option');
                o.value = o.textContent = val;
                if (val === selectedVal) o.selected = true;
                el.appendChild(o);
            });
        }

        var shouldRequire = enabled && (el.getAttribute('data-required') === '1');
        el.disabled = !enabled;
        el.required = shouldRequire;
        el.setCustomValidity(shouldRequire ? '' : '');

        if (selectedVal && !enabled) {
            el.value = '';
        }
    }

    // ── Wire one Country→State→City group ────────────────────────────────────────
    function wireGroup(countryEl) {
        if (!countryEl || countryEl._addrWired) return;

        var prefix  = countryEl.getAttribute('data-prefix');
        var stateEl = document.getElementById(prefix + '_state');
        var cityEl  = document.getElementById(prefix + '_city');

        function notify(el) {
            if (el && el.dispatchEvent) el.dispatchEvent(new Event('addrFieldUpdated', { bubbles: true }));
        }

        function refresh() {
            var country    = (countryEl.value || '').trim();
            var hasCountry = country !== '';

            var savedState = savedValue(stateEl);
            var savedCity  = savedValue(cityEl);
            var hasState   = savedState !== '';

            if (stateEl && stateEl.nextElementSibling && stateEl.nextElementSibling.classList) {
                stateEl.nextElementSibling.classList.remove('open');
            }
            if (cityEl && cityEl.nextElementSibling && cityEl.nextElementSibling.classList) {
                cityEl.nextElementSibling.classList.remove('open');
            }

            if (hasCountry) {
                // State: enabled only once a country is chosen.
                setLoading(stateEl, 'Select State');
                loadList('state', country).then(function(states) {
                    // Stale-guard: the country may have changed while fetching.
                    if ((countryEl.value || '').trim() !== country) return;
                    var curState = savedValue(stateEl);
                    fillSelect(stateEl, states, curState, true, 'Select State');
                    notify(stateEl);
                }).catch(function() {
                    if ((countryEl.value || '').trim() !== country) return;
                    var curState = savedValue(stateEl);
                    fillSelect(stateEl, [], curState, true, 'Select State');
                    notify(stateEl);
                });
            } else {
                fillSelect(stateEl, [], '', false, 'Select State');
                fillSelect(cityEl, [], '', false, 'Select City');
                notify(stateEl);
                notify(cityEl);
                return;
            }

            if (hasState) {
                // City: enabled only once both a country AND a state are chosen
                // (mirrors the country -> state cascade).
                setLoading(cityEl, 'Select City');
                loadList('city', country, savedState).then(function(cities) {
                    // Stale-guard: the state may have changed while fetching.
                    if (savedValue(stateEl) !== savedState) return;
                    var curCity = savedValue(cityEl);
                    fillSelect(cityEl, cities, curCity, true, 'Select City');
                    notify(cityEl);
                }).catch(function() {
                    if (savedValue(stateEl) !== savedState) return;
                    var curCity = savedValue(cityEl);
                    fillSelect(cityEl, [], curCity, true, 'Select City');
                    notify(cityEl);
                });
            } else {
                fillSelect(cityEl, [], '', hasCountry && hasState, 'Select City');
                notify(cityEl);
            }
        }

        countryEl._addrWired = true;
        countryEl._addrRefresh = refresh;

        // Listen via jQuery: the nice-select plugin fires a jQuery-only
        // .trigger('change') which native addEventListener never receives.
        var $ = window.jQuery;
        $(countryEl).off('change.addrCascade').on('change.addrCascade', function() {
            // Country changed: drop any previously selected state/city (they
            // belonged to the old country) so refresh repopulates cleanly.
            if (stateEl) { stateEl.value = ''; stateEl.setAttribute('data-selected', ''); }
            if (cityEl)  { cityEl.value = ''; cityEl.setAttribute('data-selected', ''); }
            refresh();
        });

        if (stateEl) {
            $(stateEl).off('change.addrCascade').on('change.addrCascade', function() {
                stateEl.setAttribute('data-selected', stateEl.value || '');
                refresh();
            });
        }

        // Populate immediately on wire-up
        refresh();
    }

    // ── Bootstrap modal: re-wire on open ─────────────────────────────────────────
    function bindModalEvents() {
        document.addEventListener('show.bs.modal', function(e) {
            e.target.querySelectorAll('.address-country-select').forEach(function(el) {
                el._addrWired = false; // allow re-wiring for fresh modal opens
                wireGroup(el);
                // Sync the searchable widget display on every modal open
                // (covers e.g. form.reset() before the modal is shown).
                if (el.dispatchEvent) el.dispatchEvent(new Event('addrFieldUpdated', { bubbles: true }));
            });
        });
    }

    // ── Searchable dropdown widget for the Country/State/City selects ─────────
    // The visible field keeps the existing `.form-select` look (same classes,
    // same chevron) and the real <select> stays in the form (visually hidden)
    // so submission, validation, cascade and external value setters still work.
    function wireAddressSearch(select) {
        if (!select || select._addrSearch) return;
        var wrap = select.closest('.address-suggest');
        if (!wrap) return;

        var trigger = wrap.querySelector('.address-suggest-trigger');
        var list    = wrap.querySelector('.address-suggest-list');
        if (!trigger || !list) return;

        select._addrSearch = true;

        var placeholder = trigger.getAttribute('data-placeholder') || 'Select';
        var searchPlaceholder = trigger.getAttribute('data-search-placeholder') ||
            ('Search ' + placeholder.replace(/^Select\s*/, ''));

        // Search row lives at the top of the list (persistent across renders).
        var searchInput = document.createElement('input');
        searchInput.type = 'search';
        searchInput.className = 'address-suggest-search-input';
        searchInput.placeholder = searchPlaceholder;
        searchInput.setAttribute('autocomplete', 'off');

        var searchRow = document.createElement('li');
        searchRow.className = 'address-suggest-search';
        searchRow.appendChild(searchInput);
        list.appendChild(searchRow);

        function setFocused(on) {
            var w = trigger.closest('.form-select-wrapper');
            if (w) w.classList.toggle('focused', !!on);
        }

        function getItems() {
            var out = [];
            for (var i = 0; i < list.children.length; i++) {
                var li = list.children[i];
                if (li.classList.contains('address-suggest-item')) out.push(li);
            }
            return out;
        }

        function renderItems() {
            var filter = (searchInput.value || '').trim().toLowerCase();
            var hasSelection = select.value && select.value !== '';

            // Remove previously rendered items, keep the search row.
            for (var i = list.children.length - 1; i >= 0; i--) {
                var li = list.children[i];
                if (li !== searchRow) list.removeChild(li);
            }

            // Always show placeholder item at top of dropdown
            if (!filter) {
                var phItem = document.createElement('li');
                phItem.className = 'address-suggest-item placeholder-item';
                phItem.textContent = placeholder;
                phItem.dataset.value = '';
                phItem.setAttribute('role', 'option');
                if (!hasSelection) {
                    phItem.classList.add('selected');
                    phItem.setAttribute('aria-selected', 'true');
                }
                list.appendChild(phItem);
            }

            var any = false;
            for (var j = 0; j < select.options.length; j++) {
                var opt = select.options[j];
                if (!opt.value) continue; // skip placeholder option
                var text = opt.textContent || opt.text || opt.value;
                if (filter && text.toLowerCase().indexOf(filter) === -1) continue;

                var item = document.createElement('li');
                item.className = 'address-suggest-item';
                item.textContent = text;
                item.dataset.value = opt.value;
                item.setAttribute('role', 'option');
                if (opt.value === select.value) {
                    item.classList.add('selected');
                    item.setAttribute('aria-selected', 'true');
                }
                list.appendChild(item);
                any = true;
            }

            if (!any && !filter) {
                // placeholder shown above, skip empty message
            } else if (!any) {
                var empty = document.createElement('li');
                empty.className = 'empty';
                empty.textContent = 'No options found';
                list.appendChild(empty);
            }
        }

        function syncTrigger() {
            var txt = '';
            if (select.selectedIndex > -1 && select.options[select.selectedIndex]) {
                var opt = select.options[select.selectedIndex];
                if (opt.value) txt = opt.textContent || opt.text || opt.value;
            }
            trigger.textContent = txt || placeholder;
            trigger.classList.toggle('disabled', !!select.disabled);
            trigger.setAttribute('aria-disabled', select.disabled ? 'true' : 'false');
        }

        function sync() {
            syncTrigger();
            if (list.classList.contains('open')) renderItems();
        }

        function open() {
            if (select.disabled) return;
            if (!list.classList.contains('open')) {
                renderItems();
                list.classList.add('open');
                trigger.setAttribute('aria-expanded', 'true');
                setFocused(true);
            }
            if (document.activeElement !== searchInput) {
                try { searchInput.focus(); } catch (e) {}
            }
        }

        function close() {
            list.classList.remove('open');
            trigger.setAttribute('aria-expanded', 'false');
            searchInput.value = '';
            setFocused(false);
        }

        function toggle() {
            if (list.classList.contains('open')) close(); else open();
        }

        function moveActive(dir) {
            var items = getItems();
            if (!items.length) return;
            var idx = -1;
            for (var i = 0; i < items.length; i++) {
                if (items[i].classList.contains('active')) { idx = i; break; }
            }
            idx += dir;
            if (idx < 0) idx = items.length - 1;
            if (idx >= items.length) idx = 0;
            for (var j = 0; j < items.length; j++) items[j].classList.remove('active');
            items[idx].classList.add('active');
            if (items[idx].scrollIntoView) items[idx].scrollIntoView({ block: 'nearest' });
        }

        function selectOption(item) {
            var val = item.dataset.value;
            var changed = select.value !== val;
            select.value = val;
            syncTrigger();
            close();
            // Single native dispatch reaches both the widget's own 'change'
            // listener and the jQuery-bound country->state->city cascade.
            // Only fire when the value actually changed (like a native select).
            if (changed && select.dispatchEvent) select.dispatchEvent(new Event('change', { bubbles: true }));
        }

        trigger.addEventListener('mousedown', function(e) {
            if (select.disabled) return;
            e.preventDefault();
            if (list.classList.contains('open')) { close(); trigger.focus(); }
            else { open(); }
        });

        trigger.addEventListener('keydown', function(e) {
            if (select.disabled) return;
            if (e.key === 'ArrowDown') { e.preventDefault(); open(); moveActive(1); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); open(); moveActive(-1); }
            else if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggle(); }
            else if (e.key === 'Escape') { close(); }
        });

        searchInput.addEventListener('input', function() { renderItems(); });
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowDown') { e.preventDefault(); moveActive(1); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); moveActive(-1); }
            else if (e.key === 'Enter') { e.preventDefault(); var a = list.querySelector('.address-suggest-item.active'); if (a) selectOption(a); }
            else if (e.key === 'Escape') { e.preventDefault(); close(); trigger.focus(); }
        });

        list.addEventListener('mousedown', function(e) {
            var item = e.target.closest ? e.target.closest('.address-suggest-item') : null;
            if (item) { e.preventDefault(); selectOption(item); }
        });

        // Keep focus/chevron state correct as focus moves around the widget.
        wrap.addEventListener('focusin', function() {
            if (list.classList.contains('open')) setFocused(true);
        });
        wrap.addEventListener('focusout', function() {
            setTimeout(function() {
                if (!wrap.contains(document.activeElement)) close();
            }, 0);
        });

        // External setters (prefill, edit_address, cascade refresh) keep the
        // widget in sync via the native 'change' + 'addrFieldUpdated' events.
        select.addEventListener('change', sync);
        select.addEventListener('addrFieldUpdated', sync);

        // form.reset() clears the real select without firing 'change', so
        // re-sync the display right after a reset.
        var form = select.closest('form');
        if (form) {
            form.addEventListener('reset', function() { setTimeout(sync, 0); });
        }

        syncTrigger();
    }

    // ── Entry point ──────────────────────────────────────────────────────────────
    function showAddressWarning(message) {
        if (window.toastr) {
            toastr.warning(message);
            return;
        }
        alert(message);
    }

    function validateAddressForm(form) {
        if (!form || !(form instanceof HTMLFormElement)) return true;
        if (form.dataset && form.dataset.addressAjax === '1') return true;
        if (!form.querySelector('.address-country-select, .address-state-select, .address-city-select')) return true;

        var checks = [
            { name: 'full_name', label: 'Full Name', required: form.querySelector('[name="full_name"]') && form.querySelector('[name="full_name"]').hasAttribute('required') },
            { name: 'phone', label: 'Phone', required: form.querySelector('[name="phone"]') && form.querySelector('[name="phone"]').hasAttribute('required'), pattern: /^[0-9+\-\s()]{7,20}$/ },
            { name: 'country', label: 'Country', required: form.querySelector('[name="country"]') && form.querySelector('[name="country"]').hasAttribute('required'), pattern: /.+/ },
            { name: 'state', label: 'State', required: form.querySelector('[name="state"]') && form.querySelector('[name="state"]').hasAttribute('required'), pattern: /.+/ },
            { name: 'city', label: 'City', required: form.querySelector('[name="city"]') && form.querySelector('[name="city"]').hasAttribute('required'), pattern: /.+/ },
            { name: 'zip_code', label: 'Zip Code', selector: '[name="zip_code"], [name="postal_code"]', required: (function(){ var el = form.querySelector('[name="zip_code"], [name="postal_code"]'); return !!el && el.hasAttribute('required'); })(), pattern: /^[A-Za-z0-9\-\s]{3,20}$/ },
            { name: 'address', label: 'Address', required: form.querySelector('[name="address"]') && form.querySelector('[name="address"]').hasAttribute('required'), pattern: /\S/ }
        ];

        for (var i = 0; i < checks.length; i++) {
            var item = checks[i];
            var field = item.selector ? form.querySelector(item.selector) : form.querySelector('[name="' + item.name + '"]');
            if (!field || field.disabled) continue;

            // Route focus to the visible widget trigger when the real field is
            // a hidden searchable select.
            function focusField(f) {
                var w = f.closest ? f.closest('.address-suggest') : null;
                var t = w && w.querySelector('.address-suggest-trigger');
                if (t) t.focus(); else f.focus();
            }

            var value = (field.value || '').trim();
            if (item.required && value === '') {
                focusField(field);
                showAddressWarning(item.label + ' is required.');
                return false;
            }

            if (value && item.pattern && !item.pattern.test(value)) {
                focusField(field);
                showAddressWarning('Please enter a valid ' + item.label + '.');
                return false;
            }
        }

        return true;
    }

    function init() {
        if (window.__addressFieldsInitRun) return;
        window.__addressFieldsInitRun = true;

        bindModalEvents();

        document.querySelectorAll('.address-country-select, .address-state-select, .address-city-select')
            .forEach(wireAddressSearch);

        document.querySelectorAll('.address-country-select').forEach(wireGroup);

        document.addEventListener('submit', function(e) {
            var form = e.target;
            if (!form || !form.matches || !form.matches('form')) return;
            if (validateAddressForm(form) === false) {
                e.preventDefault();
                e.stopPropagation();
            }
        }, true);

        // Close suggest lists on outside click
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.address-suggest')) {
                document.querySelectorAll('.address-suggest-list.open').forEach(function(l) {
                    l.classList.remove('open');
                    var w = l.closest('.address-suggest');
                    var t = w && w.querySelector('.address-suggest-trigger');
                    if (t) {
                        var fw = t.closest('.form-select-wrapper');
                        if (fw) fw.classList.remove('focused');
                        t.setAttribute('aria-expanded', 'false');
                    }
                });
            }
        });

        // Keep the chevron rotated while interacting with an open dropdown
        // (the global closeFormSelects handler would otherwise un-rotate it).
        // Capture phase runs before that bubble-phase handler.
        document.addEventListener('click', function(e) {
            var openList = e.target.closest ? e.target.closest('.address-suggest-list.open') : null;
            if (!openList) return;
            var t = openList.closest('.address-suggest').querySelector('.address-suggest-trigger');
            var fw = t && t.closest('.form-select-wrapper');
            if (fw) fw.classList.add('focused');
        }, true);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
</script>
@endonce
