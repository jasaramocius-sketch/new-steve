---
name: nav-menu-active-state
description: Use when fixing or implementing active menu/navigation highlighting in Laravel — including header nav active state, mega menu category/subcategory highlighting, base path stripping for subdirectory installs, route aliases, or product page category-based active state.
---

# Nav Menu Active State

## Key Pattern: Full URL Comparison

```php
// In nav-menu.blade.php
@php
  $currentUrl = url()->current();
  $menuUrl = url($menu['url']);
  $isActive = $currentUrl === $menuUrl;

  // Base path stripping for subdirectory installs
  $basePath = rtrim(parse_url(url('/'), PHP_URL_PATH));
  $currentPath = parse_url($currentUrl, PHP_URL_PATH);
  $menuPath = parse_url($menuUrl, PHP_URL_PATH);

  // Strip base path for comparison
  $currentPath = str_replace($basePath, '', $currentPath);
  $menuPath = str_replace($basePath, '', $menuPath);

  $isActive = $currentPath === $menuPath;
@endphp
<li class="{{ $isActive ? 'active' : '' }}">
  <a href="{{ $menu['url'] }}">{{ $menu['title'] }}</a>
</li>
```

## Route Aliases

Map subpages to parent menu items:
```php
@php
  $routeAliases = [
    '/product' => '/shop',
    '/category' => '/shop',
  ];
  $menuPath = str_replace(array_keys($routeAliases), array_values($routeAliases), $menuPath);
@endphp
```

## Root URL Exclusion

Home menu item should NOT stay active on all pages:
```php
// Skip prefix matching for root URL
if ($menuPath === '/') {
  $isActive = $currentPath === '/';
} else {
  $isActive = str_starts_with($currentPath, $menuPath);
}
```

## Mega Menu Category Active State

For product pages, highlight the matching category in mega menu:
```php
// In ProductController::show()
$product->load('category.parent');
$activeCategoryUrls = [];
$category = $product->category;
while ($category) {
    $activeCategoryUrls[] = url($category->url ?? '/category/' . $category->slug);
    $category = $category->parent;
}
view()->share('activeCategoryUrls', $activeCategoryUrls);
```

```blade
<!-- In nav-menu.blade.php for mega menu child links -->
<li class="{{ in_array(url($child['url']), $activeCategoryUrls ?? []) ? 'active' : '' }}">
  <a href="{{ $child['url'] }}">{{ $child['title'] }}</a>
</li>
```

## CSS Target

Active state targets `<li>`, not `<a>`:
```css
li.active > a {
  color: var(--primary);
}
li.active > a::after {
  width: 20px;
  opacity: 1;
  visibility: visible;
}
```

## Common Pitfalls

- Always use `url()->current()` not `Request::path()` — handles subdirectory installs
- Base path stripping is essential for subdirectory installs (e.g., `/stautoparts/`)
- Root URL `/` must be excluded from prefix matching — otherwise Home stays active everywhere
- Route aliases prevent `/product/*` pages from not highlighting "Products" menu
- `$activeCategoryUrls` must be shared via `view()->share()` for mega menu access
