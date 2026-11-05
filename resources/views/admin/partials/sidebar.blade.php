<div class="admin-sidebar">
    <div class="brand">
        <a class="d-block py-15px mr-3 ml-0 gap-1" href="{{ route('home') }}">
            <img src="{{ storedImageUrl(\App\Models\Setting::get('header_logo') ?? 'BwSkuSZ7ZYGWPc4Zk3CfeFzcn49dHpx3143n4WKS.png', 'assets/images') }}" alt="SteveAutoPartsInc." class="mw-100 h-80px h-md-50px" height="40">
             STEVE Autoparts</a>
        <button class="sidebar-close-btn d-md-none" id="sidebarCloseBtn" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Close sidebar">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="nav-wrap">
        <div class="admin-sidebar-scroll-box scroll-box">
        <div class="nav-section">Main</div>
        <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        @php
            // Products parent active
            $productsActive = request()->routeIs(
                'admin.products.*',
                'admin.categories.*',
                'admin.brands.*',
                'admin.settings.commission*',
                'admin.reviews.*'
            );

            // Submenu default expanded if parent or any child is active
            $productsExpanded = $productsActive;

            // All Products
            $allProductsActive = request()->routeIs('admin.products.*') && !request()->routeIs(
                'admin.products.import-form',
                'admin.products.import',
                'admin.products.download-sample-csv',
                'admin.products.export-csv',
                'admin.products.stock',
                'admin.products.bulk-stock'
            );

            // Bulk Import
            $productImportActive = request()->routeIs(
                'admin.products.import-form',
                'admin.products.import',
                'admin.products.download-sample-csv'
            );

            // Stock Management
            $productStockActive = request()->routeIs(
                'admin.products.stock',
                'admin.products.bulk-stock'
            );
        @endphp

        <ul class="admin-sidebar-drop list-unstyled">
            <li>
                <a href="#productsSubmenu"
                class="nav-item sidebar-link {{ $productsActive ? 'active' : '' }} {{ $productsExpanded ? 'expanded' : 'collapsed' }}"
                data-bs-toggle="collapse"
                role="button"
                aria-expanded="{{ $productsExpanded ? 'true' : 'false' }}"
                aria-controls="productsSubmenu">
                    <i class="fas fa-box"></i>
                    Products
                    <i class="fas fa-chevron-down submenu-arrow"></i>
                </a>

                <ul class="collapse list-unstyled submenu {{ $productsExpanded ? 'show' : '' }}"
                    id="productsSubmenu">

                    <li><a href="{{ route('admin.products.index') }}" class="nav-item-sub {{ $allProductsActive ? 'active' : '' }}"><i class="fas fa-boxes-stacked" aria-hidden="true"></i> All Products</a></li>
                    <li><a href="{{ route('admin.products.import-form') }}" class="nav-item-sub {{ $productImportActive ? 'active' : '' }}"><i class="fas fa-file-import" aria-hidden="true"></i> Bulk Import</a></li>
                    <li><a href="{{ route('admin.products.export-csv') }}" class="nav-item-sub {{ request()->routeIs('admin.products.export-csv') ? 'active' : '' }}"><i class="fas fa-file-export" aria-hidden="true"></i> Bulk Export</a></li>
                    <li><a href="{{ route('admin.categories.index') }}" class="nav-item-sub {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}"><i class="fas fa-folder-tree" aria-hidden="true"></i> Categories</a></li>
                    <li><a href="{{ route('admin.brands.index') }}" class="nav-item-sub {{ request()->routeIs('admin.brands.*') ? 'active' : '' }}"><i class="fas fa-tags" aria-hidden="true"></i> Brands</a></li>
                    <li><a href="{{ route('admin.products.stock') }}" class="nav-item-sub {{ $productStockActive ? 'active' : '' }}"><i class="fas fa-boxes-packing" aria-hidden="true"></i> Stock Management</a></li>
                    <li><a href="{{ route('admin.settings.commission') }}" class="nav-item-sub {{ request()->routeIs('admin.settings.commission*') ? 'active' : '' }}"><i class="fas fa-percent" aria-hidden="true"></i> Commission</a></li>
                    <li><a href="{{ route('admin.reviews.index') }}" class="nav-item-sub {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}"><i class="fas fa-star" aria-hidden="true"></i> Product Reviews</a></li>

                </ul>
            </li>
        </ul>
        <a href="{{ route('admin.orders.index') }}" class="nav-item {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
            <i class="fas fa-shopping-cart"></i> Orders
        </a>
        <a href="{{ route('admin.returns.index') }}" class="nav-item {{ request()->routeIs('admin.returns.*') ? 'active' : '' }}">
            <i class="fas fa-undo"></i> Returns
        </a>
        <!-- <a href="{{ route('admin.products.import-form') }}" class="nav-item nav-item-sub {{ request()->routeIs('admin.products.import-form') ? 'active' : '' }}">
            <i class="fas fa-upload"></i> Import Products 
        </a>-->
        <a href="{{ route('admin.sellers.index') }}" class="nav-item {{ request()->routeIs('admin.sellers.*') ? 'active' : '' }}">
            <i class="fas fa-store"></i> Sellers
        </a>

        <div class="nav-section">Content</div>
        <a href="{{ route('page-builder.index') }}" class="nav-item {{ request()->routeIs('page-builder.*') ? 'active' : '' }}">
            <i class="fas fa-puzzle-piece"></i> Page Builder
        </a>
        <a href="{{ route('admin.images.index') }}" class="nav-item {{ request()->routeIs('admin.images.*') ? 'active' : '' }}">
            <i class="fas fa-images"></i> Image Manager
        </a>
        <a href="{{ route('admin.blogs.index') }}" class="nav-item {{ request()->routeIs('admin.blogs.*') ? 'active' : '' }}">
            <i class="fas fa-blog"></i> Blog
        </a>
        <a href="{{ route('admin.blog-categories.index') }}" class="nav-item {{ request()->routeIs('admin.blog-categories.*') ? 'active' : '' }}">
            <i class="fas fa-folder"></i> Blog Categories
        </a>
        <a href="{{ route('admin.pages.index') }}" class="nav-item {{ request()->routeIs('admin.pages.*') ? 'active' : '' }}">
            <i class="fas fa-file"></i> Pages
        </a>
        <a href="{{ route('admin.faqs.index') }}" class="nav-item {{ request()->routeIs('admin.faqs.*') ? 'active' : '' }}">
            <i class="fas fa-question-circle"></i> FAQs
        </a>
        <a href="{{ route('admin.contacts.index') }}" class="nav-item {{ request()->routeIs('admin.contacts.*') ? 'active' : '' }}">
            <i class="fas fa-envelope-open-text"></i> Questions / Inquiries
        </a>
        <a href="{{ route('admin.coupons.index') }}" class="nav-item {{ request()->routeIs('admin.coupons.*') ? 'active' : '' }}">
            <i class="fas fa-percent"></i> Coupons
        </a>
        <!-- <a href="{{ route('admin.reviews.index') }}" class="nav-item {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
            <i class="fas fa-star"></i> Reviews
        </a> -->

        <div class="nav-section">Settings</div>
        @php
            $setupConfigExpanded = request()->routeIs('admin.settings.*', 'admin.api-integrations.*', 'admin.home-page.*')
                && ! request()->routeIs('admin.settings.commission*');
        @endphp
        <ul class="admin-sidebar-drop submenu-sub list-unstyled">
            <li>
                <a href="#setupConfigSubmenu"
                class="nav-item sidebar-link {{ $setupConfigExpanded ? 'active' : 'collapsed' }}"
                data-bs-toggle="collapse"
                role="button"
                aria-expanded="{{ $setupConfigExpanded ? 'true' : 'false' }}"
                aria-controls="setupConfigSubmenu">
                    <i class="fas fa-cogs"></i>
                    Setup &amp; Configurations
                    <i class="fas fa-chevron-down submenu-arrow"></i>
                </a>

                <ul class="collapse list-unstyled submenu {{ $setupConfigExpanded ? 'show' : '' }}" id="setupConfigSubmenu">
                    <li><a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings.index') ? 'active' : '' }}"><i class="fas fa-sliders" aria-hidden="true"></i> All Settings</a></li>
                    <li><a href="{{ route('admin.settings.header') }}" class="{{ request()->routeIs('admin.settings.header*') ? 'active' : '' }}"><i class="fas fa-gear" aria-hidden="true"></i> General Settings</a></li>
                    <li><a href="{{ route('admin.settings.seo') }}" class="{{ request()->routeIs('admin.settings.seo*') ? 'active' : '' }}"><i class="fas fa-magnifying-glass" aria-hidden="true"></i> SEO Settings</a></li>
                    <li><a href="{{ route('admin.settings.features') }}" class="{{ request()->routeIs('admin.settings.features*') ? 'active' : '' }}"><i class="fas fa-toggle-on" aria-hidden="true"></i> Features activation</a></li>
                    <li><a href="{{ route('admin.settings.languages.index') }}" class="{{ request()->routeIs('admin.settings.languages.*') ? 'active' : '' }}"><i class="fas fa-language" aria-hidden="true"></i> Languages</a></li>
                    <li><a href="{{ route('admin.settings.currency.index') }}" class="{{ request()->routeIs('admin.settings.currency.*') ? 'active' : '' }}"><i class="fas fa-coins" aria-hidden="true"></i> Currency</a></li>
                    <li><a href="{{ route('admin.settings.custom') }}" class="{{ request()->routeIs('admin.settings.custom*') ? 'active' : '' }}"><i class="fas fa-percent" aria-hidden="true"></i> Vat &amp; TAX</a></li>
                    <li><a href="{{ route('admin.settings.pickup-points.index') }}" class="{{ request()->routeIs('admin.settings.pickup-points.*') ? 'active' : '' }}"><i class="fas fa-store" aria-hidden="true"></i> Pickup point</a></li>
                    <li><a href="{{ route('admin.settings.smtp') }}" class="{{ request()->routeIs('admin.settings.smtp*') ? 'active' : '' }}"><i class="fas fa-envelope" aria-hidden="true"></i> SMTP Settings</a></li>
                    <li><a href="{{ route('admin.settings.payment-methods') }}" class="{{ request()->routeIs('admin.settings.payment-methods*') ? 'active' : '' }}"><i class="fas fa-credit-card" aria-hidden="true"></i> Payment Methods</a></li>
                    <li><a href="{{ route('admin.api-integrations.index') }}" class="{{ request()->routeIs('admin.api-integrations*') ? 'active' : '' }}">
                        <i class="fas fa-plug" aria-hidden="true"></i> API Integrations
                    </a></li>
                    @php($shippingMenuExpanded = request()->routeIs('admin.settings.shipping*', 'admin.settings.shipping-countries.*', 'admin.settings.shipping-states.*', 'admin.settings.shipping-cities.*', 'admin.settings.shipping-zones.*', 'admin.settings.shipping-carriers.*'))
                    <li class="shipping-settings-submenu">
                        <a href="#shippingSettingsSubmenu"
                           class="nav-item sidebar-link {{ $shippingMenuExpanded ? 'active' : 'collapsed' }} {{ $shippingMenuExpanded ? 'expanded' : '' }}"
                           data-bs-toggle="collapse"
                           role="button"
                           aria-expanded="{{ $shippingMenuExpanded ? 'true' : 'false' }}"
                           aria-controls="shippingSettingsSubmenu">
                            <i class="fas fa-truck-fast"></i>
                            <span>Shipping</span>
                            <i class="fas fa-chevron-down submenu-arrow"></i>
                        </a>
                         <ul class="collapse list-unstyled submenu {{ $shippingMenuExpanded ? 'show' : '' }}" id="shippingSettingsSubmenu">
                             <li><a href="{{ route('admin.settings.shipping') }}" class="nav-item-sub {{ request()->routeIs('admin.settings.shipping', 'admin.settings.shipping.update') ? 'active' : '' }}"><i class="fas fa-sliders"></i> Shipping Configuration</a></li>
                             <li><a href="{{ route('admin.settings.shipping-countries.index') }}" class="nav-item-sub {{ request()->routeIs('admin.settings.shipping-countries.*') ? 'active' : '' }}"><i class="fas fa-globe"></i> Shipping Countries</a></li>
                             <li><a href="{{ route('admin.settings.shipping-states.index') }}" class="nav-item-sub {{ request()->routeIs('admin.settings.shipping-states.*') ? 'active' : '' }}"><i class="fas fa-map-marked-alt"></i> Shipping States</a></li>
                             <li><a href="{{ route('admin.settings.shipping-cities.index') }}" class="nav-item-sub {{ request()->routeIs('admin.settings.shipping-cities.*') ? 'active' : '' }}"><i class="fas fa-building"></i> Shipping Cities</a></li>
                             <li><a href="{{ route('admin.settings.shipping-zones.index') }}" class="nav-item-sub {{ request()->routeIs('admin.settings.shipping-zones.*') ? 'active' : '' }}"><i class="fas fa-map-marker-alt"></i> Shipping Zones</a></li>
                             <li><a href="{{ route('admin.settings.shipping-carriers.index') }}" class="nav-item-sub {{ request()->routeIs('admin.settings.shipping-carriers.*') ? 'active' : '' }}"><i class="fas fa-ship"></i> Shipping Carriers</a></li>
                         </ul>
                     </li>
                    <li><a href="{{ route('admin.settings.order') }}" class="{{ request()->routeIs('admin.settings.order*') ? 'active' : '' }}"><i class="fas fa-clipboard-list" aria-hidden="true"></i> Order Configuration</a></li>
                    <!-- <li><span class="text-muted" aria-disabled="true">File System &amp; Cache Configuration</span></li>
                    <li>
                        <span class="text-muted" aria-disabled="true">Social media Logins</span>
                        <ul class="list-unstyled">
                            <li><span class="text-muted" aria-disabled="true">Facebook</span>
                                <ul class="list-unstyled">
                                    <li><span class="text-muted" aria-disabled="true">Facebook Chat</span></li>
                                    <li><span class="text-muted" aria-disabled="true">Facebook Comment</span></li>
                                </ul>
                            </li>
                            <li><span class="text-muted" aria-disabled="true">Google</span>
                                <ul class="list-unstyled">
                                    <li><span class="text-muted" aria-disabled="true">Analytics Tools</span></li>
                                    <li><span class="text-muted" aria-disabled="true">Google reCAPTCHA</span></li>
                                    <li><span class="text-muted" aria-disabled="true">Google Map</span></li>
                                    <li><span class="text-muted" aria-disabled="true">Google Firebase</span></li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <span class="text-muted" aria-disabled="true">Shipping</span>
                        <ul class="list-unstyled">
                            <li><a href="{{ route('admin.api-integrations.index') }}">Shipping Configuration</a></li>
                            <li><span class="text-muted" aria-disabled="true">Shipping Countries</span></li>
                            <li><span class="text-muted" aria-disabled="true">Shipping States</span></li>
                            <li><span class="text-muted" aria-disabled="true">Shipping Cities</span></li>
                            <li><span class="text-muted" aria-disabled="true">Shipping Zones</span></li>
                            <li><a href="{{ route('admin.api-integrations.index') }}">Shipping Carrier</a></li>
                        </ul>
                    </li> -->
                </ul>
            </li>
        </ul>
        <a href="{{ route('admin.home-page.index') }}" class="nav-item {{ request()->routeIs('admin.home-page.*') ? 'active' : '' }}">
            <i class="fas fa-home"></i> Home Page
        </a>
        <a href="{{ route('admin.settings.search-categories') }}" class="nav-item {{ request()->routeIs('admin.settings.search-categories*') ? 'active' : '' }}">
            <i class="fas fa-list-ul"></i> Search Categories
        </a>
        <a href="{{ route('admin.logs.index') }}" class="nav-item {{ request()->routeIs('admin.logs.*') ? 'active' : '' }}">
            <i class="fas fa-file-alt"></i> Logs
        </a>
        <a href="{{ route('admin.subscribers.index') }}" class="nav-item {{ request()->routeIs('admin.subscribers.*') ? 'active' : '' }}">
            <i class="fas fa-envelope"></i> Subscribers
        </a>
        <a href="{{ route('admin.revisions.index') }}" class="nav-item {{ request()->routeIs('admin.revisions.*') ? 'active' : '' }}">
            <i class="fas fa-history"></i> Revisions
        </a>
        <a href="{{ route('admin.file-revisions.index') }}" class="nav-item {{ request()->routeIs('admin.file-revisions.*') ? 'active' : '' }}">
            <i class="fas fa-file-code"></i> File Revisions
        </a>
        <a href="{{ route('admin.profile') }}" class="nav-item {{ request()->routeIs('admin.profile') ? 'active' : '' }}">
            <i class="fas fa-user-cog"></i> My Profile
        </a>

        <div class="nav-section">People</div>
        <a href="{{ url('/admin/customers') }}" class="nav-item {{ request()->is('admin/customers*') ? 'active' : '' }}">
            <i class="fas fa-users"></i> Customers
        </a>

        <!-- @if(Auth::check() && in_array(Auth::user()->role, ['master_admin', 'admin']))
        <a href="{{ url('/admin/staff') }}" class="nav-item {{ request()->is('admin/staff*') && !request()->is('admin/staffs*') ? 'active' : '' }}">
            <i class="fas fa-user-tie"></i> Staff Management
        </a>
        @endif -->

        @if(Auth::check() && Auth::user()->role === 'master_admin')
        <a href="{{ route('admin.users.index') }}" class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <i class="fas fa-user-cog"></i> All Users
        </a>
        @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (window.innerWidth < 768) {
        console.log('Mobile viewport — icon-collapse feature disabled');
        return;
    }

    if (document.getElementById('sidebarIconToggle')) {
        console.log('Sidebar toggle already initialized — skipping');
        return;
    }

    var sidebar = document.querySelector('.admin-sidebar');
    if (!sidebar) { console.error('Sidebar not found'); return; }

    // 1. Wrap nav-item label text in a span + set tooltip title from label
    document.querySelectorAll('.admin-sidebar .nav-item, .admin-sidebar .sidebar-link').forEach(function(item) {
        var icon = item.querySelector('i');
        if (!icon) return;
        var label = document.createElement('span');
        label.className = 'nav-label';
        var node = icon.nextSibling;
        while (node) {
            var next = node.nextSibling;
            // Skip the chevron-arrow icon (it's a sibling too)
            if (node.classList && node.classList.contains('submenu-arrow')) {
                node = next;
                continue;
            }
            label.appendChild(node);
            node = next;
        }
        item.appendChild(label);
        // Keep submenu arrow at the far right after the label
        var arrow = item.querySelector('.submenu-arrow');
        if (arrow) { item.appendChild(arrow); }
        // Set tooltip title from visible text (icon + label)
        var title = Array.from(item.querySelectorAll('.nav-label')).map(function(l){ return l.textContent.trim(); }).join(' ');
        if (title) item.setAttribute('data-bs-original-title', title);
    });

    // 2. Wrap brand text in a span (logo stays untouched)
    var brandLink = document.querySelector('.admin-sidebar .brand a');
    if (brandLink) {
        var img = brandLink.querySelector('img');
        var label = document.createElement('span');
        label.className = 'nav-label';
        var node = img ? img.nextSibling : brandLink.firstChild;
        while (node) {
            var next = node.nextSibling;
            label.appendChild(node);
            node = next;
        }
        brandLink.appendChild(label);
    }

    // 3. Wrap nav-section text (e.g. "Main", "Content") in its own span
    document.querySelectorAll('.admin-sidebar .nav-section').forEach(function(section) {
        var text = section.textContent;
        section.textContent = '';
        var span = document.createElement('span');
        span.className = 'nav-section-text';
        span.textContent = text;
        section.appendChild(span);
    });

    // 4. Inject CSS (only once)
    if (!document.getElementById('sidebarToggleStyles')) {
        var style = document.createElement('style');
        style.id = 'sidebarToggleStyles';
        style.textContent = `
            .admin-sidebar {
                position: fixed;
                transition: width .3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            .admin-sidebar.icon-collapsed { width: 72px !important; overflow: visible; }

            .admin-sidebar .nav-item {
                white-space: nowrap;
                transition: padding-left .3s ease;
            }
            .admin-sidebar.icon-collapsed .nav-item {
                justify-content: flex-start;
            }
            .admin-sidebar .nav-item i {
                width: 20px;
                text-align: center;
                flex-shrink: 0;
            }

            .admin-sidebar .nav-label {
                opacity: 1;
                max-width: 200px;
                overflow: hidden;
                display: inline-block;
                transition: opacity .2s ease, max-width .25s ease;
            }
            .admin-sidebar.icon-collapsed .nav-label {
                opacity: 0;
                max-width: 0;
            }
            .admin-sidebar.icon-collapsed .admin-sidebar-drop .submenu{
                opacity: 0;
                max-width: 0;
                max-height: 0;
            }

            .admin-sidebar .nav-section {
                position: relative;
                height: auto;
                overflow: hidden;
                transition: height .25s ease;
            }
            .admin-sidebar.icon-collapsed .nav-section {
                height: 37px;
            }
            .admin-sidebar.icon-collapsed .nav-section::before {
                content: '';
                position: absolute;
                left: 20px; right: 20px; top: 50%;
                height: 1px;
                transform: translateY(-50%);
                background: var(--primary);
                opacity: 1;
                transition: opacity .2s ease;
            }
            .admin-sidebar .nav-section::before {
                content: '';
                position: absolute;
                left: 20px; right: 20px; top: 50%;
                height: 1px;
                transform: translateY(-50%);
                background: var(--primary);
                opacity: 0;
                transition: opacity .2s ease;
            }
            .admin-sidebar .nav-section-text {
                opacity: 1;
                transition: opacity .15s ease;
            }
            .admin-sidebar.icon-collapsed .nav-section-text {
                opacity: 0;
            }

            .admin-sidebar .brand {
                position: relative;
                justify-content: flex-start;
            }

            .main-content {
                transition: margin-left .3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            body.icon-collapsed-body .main-content { margin-left: 72px !important; }

            /* Floating toggle button, half outside the sidebar edge */
            #sidebarIconToggle {
                position: absolute;
                top: 17px;
                right: -14px;
                display: inline-flex !important;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                border: 1px solid rgba(0,0,0,.08);
                background: #FFF;
                color: #1e1e2d;
                width: 28px;
                height: 28px;
                border-radius: 6px;
                box-shadow: 0 2px 6px rgba(0,0,0,.15);
                transition: all .15s;
                flex-shrink: 0;
                padding: 0;
                z-index: 1003;
            }
            #sidebarIconToggle:hover { background: var(--primary); color:#fff; box-shadow: 0 2px 10px rgba(0,0,0,.22); }

            .sidebar-toggle-icon {
                transition: transform 0.25s ease;
            }
            .admin-sidebar.icon-collapsed .sidebar-toggle-icon {
                transform: rotate(180deg);
            }
        `;
        document.head.appendChild(style);
    }

    // 5. Toggle button — single button, chevron icon inside directly (no nested button)
    var brandDiv = document.querySelector('.admin-sidebar .brand');
    if (!brandDiv) { console.error('Brand div not found'); return; }

    var toggleBtn = document.createElement('button');
    toggleBtn.type = 'button';
    toggleBtn.id = 'sidebarIconToggle';
    toggleBtn.setAttribute('aria-label', 'Toggle sidebar');
    toggleBtn.title = 'Toggle sidebar';
    toggleBtn.innerHTML = `
        <svg class="sidebar-toggle-icon" width="20" height="20" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <path d="m15 18-6-6 6-6"/>
        </svg>
    `;
    brandDiv.appendChild(toggleBtn);

    toggleBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var collapsed = sidebar.classList.toggle('icon-collapsed');
        document.body.classList.toggle('icon-collapsed-body', collapsed);
        localStorage.setItem('sidebarIconCollapsed', collapsed);
    });

    // 6. Restore saved state
    if (localStorage.getItem('sidebarIconCollapsed') === 'true') {
        sidebar.classList.add('icon-collapsed');
        document.body.classList.add('icon-collapsed-body');
    }
});

document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(function (link) {
    const target = document.querySelector(link.getAttribute('href'));

    if (!target) return;

    const syncParentState = function () {
        const isExpanded = target.classList.contains('show');
        link.classList.toggle('active', isExpanded);
        link.classList.toggle('expanded', isExpanded);
        link.classList.toggle('collapsed', !isExpanded);
        link.setAttribute('aria-expanded', String(isExpanded));
    };

    syncParentState();
    target.addEventListener('shown.bs.collapse', syncParentState);
    target.addEventListener('hidden.bs.collapse', syncParentState);
});

document.addEventListener('show.bs.collapse', function (event) {
    const opening = event.target;
    const sidebar = opening instanceof Element ? opening.closest('.admin-sidebar') : null;

    if (!sidebar || !window.bootstrap?.Collapse) return;

    sidebar.querySelectorAll('.collapse.show').forEach(function (expanded) {
        // Keep the open parent visible when expanding one of its nested menus.
        if (expanded === opening || expanded.contains(opening)) return;

        window.bootstrap.Collapse.getOrCreateInstance(expanded, { toggle: false }).hide();
    });
});
</script>