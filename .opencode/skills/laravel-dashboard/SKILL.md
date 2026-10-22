---
name: laravel-dashboard
description: Use when building or fixing Laravel user dashboard pages — including dashboard redesign, mobile sidebar toggle, stat cards, order history, order details, addresses, notifications, profile pages, or dashboard sidebar active state.
---

# Laravel Dashboard

## Dashboard Layout

File: `resources/views/user/layouts/dashboard.blade.php`

### Mobile Sidebar Toggle (Floating Button)
```html
<!-- Floating toggle button - visible on mobile only -->
<button class="dashboard-sidebar-toggle d-lg-none" onclick="toggleSidebar()">
  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
    <path d="M3 12h18M3 6h18M3 18h18"/>
  </svg>
</button>
<div class="dashboard-sidebar-overlay" onclick="closeSidebar()"></div>
```

```javascript
function toggleSidebar() {
  $('.user-dashboard-sidebar').toggleClass('active');
  $('.dashboard-sidebar-overlay').toggleClass('active');
}
function closeSidebar() {
  $('.user-dashboard-sidebar').removeClass('active');
  $('.dashboard-sidebar-overlay').removeClass('active');
}
```

### CSS (Mobile Off-Canvas)
```css
@media (max-width: 991.98px) {
  .user-dashboard-sidebar {
    position: fixed;
    top: 0;
    left: -300px;
    width: 280px;
    height: 100vh;
    z-index: 1060;
    background: #fff;
    overflow-y: auto;
    transition: left 0.3s ease;
    box-shadow: 0 0 20px rgba(0,0,0,0.15);
  }
  .user-dashboard-sidebar.active {
    left: 0;
    height: 100%;
  }
}
.dashboard-sidebar-overlay.active {
  display: block;
  position: fixed;
  inset: 0;
  z-index: 1050;
  background: rgba(0,0,0,0.5);
}
```

## Stat Cards Pattern
```html
<div class="row">
  <div class="col-lg-3 col-md-6">
    <div class="gs-single-statatics">
      <div class="icon"><!-- SVG icon --></div>
      <div class="content">
        <h3>{{ number_format($totalSpent, 2) }}</h3>
        <p>Total Spent</p>
      </div>
    </div>
  </div>
  <!-- Repeat for Orders, Cart Items, Pending Orders -->
</div>
```

## Circular Action Buttons (All Dashboard Pages)
```css
.btn-edit { color: #e67a38; }      /* Orange */
.btn-delete { color: #dc3545; }    /* Red */
.btn-check { color: #198754; }     /* Green */
.btn-set-default { color: #3490f3; } /* Blue */
```

```html
<a href="{{ route('user.edit', $id) }}" class="action-btn btn-edit btn-circle btn-icon" title="Edit">
  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
    <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
    <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
  </svg>
</a>
```

## Dashboard Sidebar Active State
```blade
<li class="{{ request()->routeIs('user.dashboard') ? 'active' : '' }}">
  <a href="{{ route('user.dashboard') }}">
    <i class="fas fa-tachometer-alt"></i> Dashboard
  </a>
</li>
```

## Controller Pattern
```php
class DashboardController extends Controller {
  public function index() {
    $user = auth()->user();
    $totalSpent = $user->orders()->sum('total');
    $orderCount = $user->orders()->count();
    $cartCount = collect(session('cart', []))->sum('qty') ?: 0;
    $pendingOrders = $user->orders()->where('status', 'pending')->count();
    return view('user.dashboard', compact('totalSpent', 'orderCount', 'cartCount', 'pendingOrders'));
  }
}
```

## Common Pitfalls

- Cart count: use `collect(session('cart', []))->sum('qty')` — NOT `Cart::count()` (model doesn't exist)
- Address: guard with `isset($address)` — may be null for new users
- Mobile sidebar: `position: fixed` + `left: -300px` default, `.active` slides to `left: 0`
- Overlay must be separate div with `z-index` lower than sidebar
