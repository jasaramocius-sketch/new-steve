@php
    $categoryNodes = $categoryNodes ?? [];
    $activeCategoryIds = array_map('intval', $activeCategoryIds ?? []);
    $level = max(1, (int) ($level ?? 1));
    $childrenUrl = route('ajax.shop-category-children');

    $liClass = $level === 1
        ? 'main-list mb-1 gap-1 d-flex flex-column'
        : ($level === 2 ? 'mb-2 gap-1 d-flex flex-column' : 'my-1 gap-1 d-flex flex-column');
    $fontSize = $level === 1 ? 15 : 12;
    $countSize = $level === 1 ? 13 : 12;
@endphp

@foreach($categoryNodes as $node)
    @php
        $nodeId = (int) $node->id;
        $isActive = in_array($nodeId, $activeCategoryIds, true);
        $hasChildren = ! empty($node->children);
        $expand = $isActive && $hasChildren;
        $href = \App\Support\CategoryRef::url($node->id, $node->slug);
        $color = $isActive ? 'var(--primary)' : '#1f0300';
        $weight = $isActive ? '600' : '400';
    @endphp
    <li class="{{ $liClass }}">
        <div class="d-flex justify-content-between align-items-center gap-3">
            @if(($node->descendant_count ?? 0) > 0 || $isActive)
                <a href="{{ $href }}" class="text-decoration-none flex-grow-1 primary-a-tag-text-hover a-tag-text-hover"
                   style="color: {{ $color }}; font-weight: {{ $weight }}; font-size: {{ $fontSize }}px;">
                    {{ $node->name }}
                    <span class="text-muted" style="font-size: {{ $countSize }}px; font-weight: 400;">({{ $node->descendant_count }})</span>
                </a>
            @else
                <span class="flex-grow-1 cat-no-products" style="font-size: {{ $fontSize }}px;">
                    {{ $node->name }}
                    <span class="text-muted" style="font-size: {{ $countSize }}px; font-weight: 400;">(0)</span>
                </span>
            @endif

            @if($hasChildren)
                <button type="button"
                        class="btn p-0 border-0 cat-toggle-btn {{ $expand ? '' : 'collapsed' }} steve-btn"
                        data-bs-toggle="collapse"
                        data-bs-target="#sidebarcat_{{ $nodeId }}"
                        aria-label="Toggle {{ $node->name }} categories">
                    <i class="fa-solid fa-plus" style="font-size: 9px;"></i>
                    <i class="fa-solid fa-minus" style="font-size: 9px;"></i>
                </button>
            @endif
        </div>

        @if($hasChildren)
            <ul id="sidebarcat_{{ $nodeId }}"
                class="collapse ms-3 mt-0 list-unstyled {{ $expand ? 'show' : '' }}"
                data-sidebar-cat-children="{{ $nodeId }}"
                data-sidebar-cat-level="{{ $level + 1 }}"
                data-sidebar-cat-url="{{ $childrenUrl }}"
                data-loaded="0">
                @if($expand)
                    <li class="text-muted small py-1">Loading…</li>
                @endif
            </ul>
        @endif
    </li>
@endforeach
