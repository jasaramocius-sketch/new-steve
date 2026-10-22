@php
    $currentUrl = url()->current();
    $basePath = rtrim(parse_url(url('/'), PHP_URL_PATH) ?? '', '/');
    $currentPath = parse_url($currentUrl, PHP_URL_PATH) ?? '/';
    $relativePath = $basePath !== '' && str_starts_with($currentPath, $basePath) ? substr($currentPath, strlen($basePath)) : $currentPath;
    $menu = $menu ?? [];
    $megaCategories = $megaCategories ?? collect();
    $activeCategoryUrls = $activeCategoryUrls ?? [];
    $routeAliases = [
        '/product' => ['/shop'],
        '/category' => ['/shop'],
    ];
    $aliasedMenuUrl = '';
    foreach ($routeAliases as $prefix => $targets) {
        if (str_starts_with($relativePath, $prefix . '/') || $relativePath === $prefix) {
            foreach ($targets as $target) {
                $aliasedMenuUrl = url($target);
                break;
            }
            break;
        }
    }
@endphp
@foreach($menu as $item)
    @php
        $label = $item['label'] ?? '';
        $url = $item['url'] ?? '#';
        $children = $item['children'] ?? [];
        $hasChildren = !empty($children);
        $menuUrl = $url === '#' ? '' : url($url);
        $isActive = $menuUrl && ($currentUrl === $menuUrl || $currentUrl === $menuUrl . '/');
        if (!$isActive && $menuUrl && $url !== '/' && $url !== '') {
            $isActive = str_starts_with($currentUrl, $menuUrl . '/') || str_starts_with($currentUrl, $menuUrl . '?');
        }
        if (!$isActive && $menuUrl && $aliasedMenuUrl && $menuUrl === $aliasedMenuUrl) {
            $isActive = true;
        }
        if (!$isActive && $menuUrl && !empty($activeCategoryUrls) && in_array($menuUrl, $activeCategoryUrls)) {
            $isActive = true;
        }
        if (!$isActive && $hasChildren) {
            foreach ($children as $child) {
                $childUrl = $child['url'] ?? '';
                if ($childUrl === '' || $childUrl === '#') continue;
                $childFullUrl = url($childUrl);
                if ($childUrl !== '/' && ($currentUrl === $childFullUrl || str_starts_with($currentUrl, $childFullUrl . '/') || str_starts_with($currentUrl, $childFullUrl . '?'))) {
                    $isActive = true;
                    break;
                }
                if (!$isActive && !empty($activeCategoryUrls) && in_array($childFullUrl, $activeCategoryUrls)) {
                    $isActive = true;
                    break;
                }
                if (!$isActive && $aliasedMenuUrl && $childFullUrl === $aliasedMenuUrl) {
                    $isActive = true;
                    break;
                }
                if (!empty($child['children'])) {
                    foreach ($child['children'] as $subChild) {
                        $subUrl = $subChild['url'] ?? '';
                        if ($subUrl === '' || $subUrl === '#') continue;
                        $subFullUrl = url($subUrl);
                        if ($subUrl !== '/' && ($currentUrl === $subFullUrl || str_starts_with($currentUrl, $subFullUrl . '/') || str_starts_with($currentUrl, $subFullUrl . '?'))) {
                            $isActive = true;
                            break 2;
                        }
                        if (!$isActive && !empty($activeCategoryUrls) && in_array($subFullUrl, $activeCategoryUrls)) {
                            $isActive = true;
                            break 2;
                        }
                    }
                }
            }
        }
        $liClass = $hasChildren ? 'has-megamenu' : '';
        if ($isActive) $liClass .= ' active';
        $isMegaFormat = $hasChildren && isset($children[0]['children']);
    @endphp

    <li class="{{ $liClass }}">
        <div class="menu-item-with-icon">
        <a href="{{ $url === '#' ? 'javascript:void(0)' : url($url) }}" class="nav-link{{ $isActive ? ' active' : '' }}">{{ $label }}</a>
        @if($hasChildren)
            <span class="has-submenu-icon">
                <i class="fas fa-chevron-down"></i>
            </span></div>
            <div class="megamenu cat-megamenu">
                <div class="row w-100">
                    @if($isMegaFormat)
                        @foreach($children as $col)
                            @php
                                $colFullUrl = url($col['url'] ?? '#');
                                $colIsActive = !empty($activeCategoryUrls) && in_array($colFullUrl, $activeCategoryUrls);
                            @endphp
                            <div class="col-lg-4">
                                <div class="single-menu mt-30">
                                    <h5><a href="{{ $colFullUrl }}" class="{{ $colIsActive ? 'active' : '' }}">{{ $col['label'] ?? '' }}</a></h5>
                                    @if(!empty($col['children']))
                                        <ul>
                                            @foreach($col['children'] as $child)
                                                @php
                                                    $childFullUrl = url($child['url'] ?? '#');
                                                    $childIsActive = !empty($activeCategoryUrls) && in_array($childFullUrl, $activeCategoryUrls);
                                                @endphp
                                                <li><a href="{{ $childFullUrl }}" class="{{ $childIsActive ? 'active' : '' }}">{{ $child['label'] ?? '' }}</a></li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="col-lg-4">
                            <div class="single-menu mt-30">
                                <ul>
                                    @foreach($children as $child)
                                        @php
                                            $childFullUrl = url($child['url'] ?? '#');
                                            $childIsActive = !empty($activeCategoryUrls) && in_array($childFullUrl, $activeCategoryUrls);
                                        @endphp
                                        <li><a href="{{ $childFullUrl }}" class="{{ $childIsActive ? 'active' : '' }}">{{ $child['label'] ?? '' }}</a></li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @else
            </div>
        @endif
    </li>
@endforeach
