@php $adminHeaderBg = \App\Models\Setting::get('admin_header_bg'); @endphp
<div class="admin-navbar admin-dashboard-header @if($adminHeaderBg) admin-navbar-bg @endif" @if($adminHeaderBg) style="background-image:url('{{ storedImageUrl($adminHeaderBg, 'assets/images') }}'); background-size:cover; background-position:center;" @endif>
    <div class="d-flex align-items-center gap-2 admin-navbar-first-col">
        <button class="btn btn-outline-secondary d-md-none steve-btn wh-40 d-flex" id="sidebarToggle" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Toggle sidebar">
            <i class="fas fa-bars"></i>
        </button>
        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary steve-btn wh-40 d-flex" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Go Back">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div class="page-title">@yield('page-title', 'Dashboard')</div>
    </div>
    <div class="nav-actions admin-navbar-second-col">
        <a href="{{ route('home') }}" target="_blank" class="btn btn-outline-primary d-none d-md-inline-block d-flex wh-40" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="View Site">
            <i class="fas fa-external-link-alt"></i>
        </a>
        <a href="{{ route('admin.clear.cache') }}" class="btn btn-outline-danger wh-40 d-flex" onclick="return confirm('Clear all cache?')" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="Clear Cache">
            <i class="fas fa-broom"></i>
        </a>
        <div class="dropdown">
            <a href="#" class="user-dropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="user-avatar">
                    @if(Auth::user()->avatar)
                        <img src="{{ storedImageUrl(Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}">
                    @else
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    @endif
                </div>
                <div>
                    <div class="login-user-labels text-transform-capitalize admin-navbar-user-name">{{ Auth::user()->name }}</div>
                    <div class="admin-navbar-user-role">
                        @php
                            $roleLabel = match(Auth::user()->role) {
                                'master_admin' => 'Admin',
                                'admin' => 'Admin',
                                'staff' => 'Staff',
                                default => 'Customer',
                            };
                            $roleBadge = match(Auth::user()->role) {
                                'master_admin' => 'danger',
                                'admin' => 'danger',
                                'staff' => 'info',
                                default => 'success',
                            };
                        @endphp
                        <span class="badge bg-light border border-{{ $roleBadge }}-subtle text-{{ $roleBadge }} admin-navbar-role-badge">{{ $roleLabel }}</span>
                    </div>
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li><a class="dropdown-item" href="{{ route('admin.profile') }}"><i class="fas fa-user-cog fa-fw me-2"></i>My Profile</a></li>
                <!-- <li><hr class="dropdown-divider"></li> -->
                <li>
                    <form action="{{ route('admin.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item steve-btn"><i class="fas fa-sign-out-alt fa-fw me-2"></i>Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</div>
