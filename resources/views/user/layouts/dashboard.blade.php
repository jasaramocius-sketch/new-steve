@extends('layouts.app')
@include('partials.page-attributes', ['pageId' => 'user-dashboard-layout', 'pageClass' => 'user-dashboard-layout'])

@section('content')
<section class="gs-dashboard-section py-5 py-md-4 py-sm-5">
    <div class="container">
        <div class="row">

            @include('user.layouts.sidebar')

            <div class="col-lg-9">
                <div class="gs-dashboard-user-content-wrapper">
                    @yield('dashboard-content')
                </div>
            </div>

        </div>
    </div>
</section>

<button class="dashboard-sidebar-toggle-float d-lg-none" type="button" aria-label="Toggle sidebar">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="3" y1="6" x2="21" y2="6"></line>
        <line x1="3" y1="12" x2="21" y2="12"></line>
        <line x1="3" y1="18" x2="21" y2="18"></line>
    </svg>
</button>

<style>
.dashboard-sidebar-toggle-float {
    display: none;
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 1040;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: var(--primary, #ee5316);
    color: #fff;
    border: none;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
}
.dashboard-sidebar-toggle-float:active {
    transform: scale(0.92);
}
@media (max-width: 991.98px) {
    .dashboard-sidebar-toggle-float {
        display: flex;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var floatBtn = document.querySelector('.dashboard-sidebar-toggle-float');
    var sidebar = document.querySelector('.user-dashboard-sidebar');
    var overlay = document.querySelector('.dashboard-sidebar-overlay');

    if (floatBtn && sidebar && overlay) {
        floatBtn.addEventListener('click', function() {
            sidebar.classList.add('active');
            overlay.classList.add('active');
            document.body.classList.add('overflow-hidden');
        });
    }
});
</script>
@endsection
