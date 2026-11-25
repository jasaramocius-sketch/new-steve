@php
    $mobileCategoryTree = $mobileCategoryTree ?? [];
    $mobileCurrentUrl = $mobileCurrentUrl ?? url()->current();
    $activeCategoryUrls = $activeCategoryUrls ?? [];
    $catRoute = function($slug) { return url('/category/' . $slug); };
    $subcatRoute = function($parent, $child) { return url('/category/' . $parent . '/' . $child); };
    $subchildRoute = function($parent, $child, $subchild) { return url('/category/' . $parent . '/' . $child . '/' . $subchild); };
@endphp
@if (count($mobileCategoryTree))
@foreach($mobileCategoryTree as $topCat)
    @php
        $topCatUrl = $catRoute($topCat->slug);
        $topActive = $mobileCurrentUrl === $topCatUrl || $mobileCurrentUrl === $topCatUrl . '/' || (!empty($activeCategoryUrls) && in_array($topCatUrl, $activeCategoryUrls));
        $hasActiveChild = false;
        if (count($topCat->children) > 0 && !$topActive) {
            foreach ($topCat->children as $subCat) {
                $subCatUrl = $subcatRoute($topCat->slug, $subCat->slug);
                if ($mobileCurrentUrl === $subCatUrl || $mobileCurrentUrl === $subCatUrl . '/' || (!empty($activeCategoryUrls) && in_array($subCatUrl, $activeCategoryUrls))) {
                    $hasActiveChild = true; break;
                }
                foreach ($subCat->children as $childCat) {
                    $childCatUrl = $subchildRoute($topCat->slug, $subCat->slug, $childCat->slug);
                    if ($mobileCurrentUrl === $childCatUrl || $mobileCurrentUrl === $childCatUrl . '/' || (!empty($activeCategoryUrls) && in_array($childCatUrl, $activeCategoryUrls))) {
                        $hasActiveChild = true; break 2;
                    }
                }
            }
        }
    @endphp
    <li class="main-list gap-1 d-flex flex-column mb-1 {{ $topActive || $hasActiveChild ? 'active' : '' }}">
        <div class="d-flex justify-content-between align-items-center gap-3">
            @if($topCat->descendant_count > 0)
                <a href="{{ \App\Support\CategoryRef::url($topCat->id, $topCat->slug) }}" class="text-decoration-none flex-grow-1 {{ $topActive || $hasActiveChild ? 'fw-bold' : '' }}" style="color: {{ $topActive || $hasActiveChild ? 'var(--primary)' : '#1f0300' }}; font-weight: {{ $topActive || $hasActiveChild ? '600' : '400' }}; font-size: 15px;">
                    {{ $topCat->name }}
                    <span class="text-muted" style="font-size: 13px; font-weight: 400;">({{ $topCat->descendant_count }})</span>
                </a>
            @else
                <span class="flex-grow-1" style="color: #aaa; font-size: 15px;">
                    {{ $topCat->name }}
                    <span class="text-muted" style="font-size: 13px; font-weight: 400;">(0)</span>
                </span>
            @endif
            @if(count($topCat->children) > 0)
                <button class="btn p-0 border-0 cat-toggle-btn collapsed steve-btn" data-bs-toggle="collapse" data-bs-target="#mobcat_{{ $topCat->id }}">
                    <i class="fa-solid fa-plus" style="font-size: 11px;"></i>
                    <i class="fa-solid fa-minus" style="font-size: 11px;"></i>
                </button>
            @endif
        </div>

        @if(count($topCat->children) > 0)
            @php $showTop = $topActive || $hasActiveChild; @endphp
            <ul id="mobcat_{{ $topCat->id }}" class="collapse ms-3 mt-0 list-unstyled {{ $showTop ? 'show' : '' }}">
                @foreach($topCat->children as $subCat)
                    @php
                        $subCatUrl = $subcatRoute($topCat->slug, $subCat->slug);
                        $subActive = $mobileCurrentUrl === $subCatUrl || $mobileCurrentUrl === $subCatUrl . '/' || (!empty($activeCategoryUrls) && in_array($subCatUrl, $activeCategoryUrls));
                        $hasActiveSubChild = false;
                        if (count($subCat->children) > 0 && !$subActive) {
                            foreach ($subCat->children as $childCat) {
                                $childCatUrl = $subchildRoute($topCat->slug, $subCat->slug, $childCat->slug);
                                if ($mobileCurrentUrl === $childCatUrl || $mobileCurrentUrl === $childCatUrl . '/' || (!empty($activeCategoryUrls) && in_array($childCatUrl, $activeCategoryUrls))) {
                                    $hasActiveSubChild = true; break;
                                }
                            }
                        }
                    @endphp
                    <li class="mb-2 {{ $subActive || $hasActiveSubChild ? 'active' : '' }}">
                        <div class="d-flex justify-content-between align-items-center gap-3">
                            @if($subCat->descendant_count > 0)
                                <a href="{{ \App\Support\CategoryRef::url($subCat->id, $subCat->slug) }}" class="text-decoration-none flex-grow-1 {{ $subActive || $hasActiveSubChild ? 'fw-bold' : '' }}" style="color: {{ $subActive || $hasActiveSubChild ? 'var(--primary)' : '#1f0300' }}; font-weight: {{ $subActive || $hasActiveSubChild ? '600' : '400' }}; font-size: 15px;">
                                    {{ $subCat->name }}
                                    <span class="text-muted" style="font-size: 12px; font-weight: 400;">({{ $subCat->descendant_count }})</span>
                                </a>
                            @else
                                <span class="flex-grow-1" style="color: #aaa; font-size: 15px;">
                                    {{ $subCat->name }}
                                    <span class="text-muted" style="font-size: 12px; font-weight: 400;">(0)</span>
                                </span>
                            @endif
                            @if(count($subCat->children) > 0)
                                <button class="btn p-0 border-0 cat-toggle-btn collapsed steve-btn" data-bs-toggle="collapse" data-bs-target="#mobcat_{{ $subCat->id }}">
                                    <i class="fa-solid fa-plus" style="font-size: 9px;"></i>
                                    <i class="fa-solid fa-minus" style="font-size: 9px;"></i>
                                </button>
                            @endif
                        </div>

                        @if(count($subCat->children) > 0)
                            @php $showSub = $subActive || $hasActiveSubChild; @endphp
                            <ul id="mobcat_{{ $subCat->id }}" class="collapse ms-3 mt-0 list-unstyled {{ $showSub ? 'show' : '' }}">
                                @foreach($subCat->children as $childCat)
                                    @php
                                        $childCatUrl = $subchildRoute($topCat->slug, $subCat->slug, $childCat->slug);
                                        $childActive = $mobileCurrentUrl === $childCatUrl || $mobileCurrentUrl === $childCatUrl . '/' || (!empty($activeCategoryUrls) && in_array($childCatUrl, $activeCategoryUrls));
                                    @endphp
                                    <li class="my-1 {{ $childActive ? 'active' : '' }}">
                                        @if($childCat->descendant_count > 0)
                                            <a href="{{ \App\Support\CategoryRef::url($childCat->id, $childCat->slug) }}" class="text-decoration-none {{ $childActive ? 'fw-bold' : '' }}" style="color: {{ $childActive ? 'var(--primary)' : '#1f0300' }}; font-weight: {{ $childActive ? '600' : '400' }}; font-size: 14px;">
                                                {{ $childCat->name }}
                                                <span class="text-muted" style="font-size: 12px; font-weight: 400;">({{ $childCat->descendant_count }})</span>
                                            </a>
                                        @else
                                            <span style="color: #aaa; font-size: 14px;">
                                                {{ $childCat->name }}
                                                <span class="text-muted" style="font-size: 12px; font-weight: 400;">(0)</span>
                                            </span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif

        
    </li>
@endforeach
@else
    <li><a href="{{ route('categories.index') }}">Browse all categories</a></li>
@endif
