{{-- Registers the page-id/page-class sections. Auto-resolves from DB if not passed. --}}
@php
    $routeName = request()->route()?->getName() ?? '';
    $baseSlug = $routeName ? explode('.', $routeName)[0] : '';

    // Auto-resolve $page from DB if not passed by controller
    if (!isset($page) || !$page) {
        if ($baseSlug) {
            $page = \App\Models\Page::where('slug', $baseSlug)->where('status', true)->first();
        }
        if (empty($page)) {
            $fallback = request()->segment(1);
            if ($fallback) {
                $page = \App\Models\Page::where('slug', $fallback)->where('status', true)->first();
            }
        }
    }

    // For content detail pages (blog, product), use the model's own ID
    $contentId = null;
    $contentType = null;
    $detailClass = '';
    if ($baseSlug === 'blog' && isset($blog) && $blog) {
        $contentId = $blog->id;
        $contentType = 'blog';
        $detailClass = 'detail-post';
    } elseif ($baseSlug === 'product' && isset($product) && $product) {
        $contentId = $product->id ?? $product['id'] ?? null;
        $contentType = 'product';
        $detailClass = 'product-detail';
    } elseif ($baseSlug === 'category' && isset($currentCategory) && $currentCategory) {
        $contentId = $currentCategory->id;
        $contentType = 'category';
        $detailClass = 'detail-category';
    } elseif ($baseSlug === 'blog' && isset($category) && $category) {
        $contentId = $category->id;
        $contentType = 'category';
        $detailClass = 'detail-category';
    }

    $pageModel = $page ?? null;
    $idPrefix = $contentType ? $contentType . '-' : 'page-';
    $idNumber = $contentType ? $contentId : ($pageModel->id ?? '');
    $pageIdValue = trim(($idNumber ? $idPrefix . $idNumber : '') . ' ' . trim($pageId ?? ''));
    $pageClassValue = trim(($pageModel ? ' page-' . Str::slug($pageModel->title) : '') . ' ' . trim($pageClass ?? '') . ' ' . $detailClass);
@endphp
@section('page-id', $pageIdValue !== '' ? $pageIdValue : 'default-page-id')
@section('page-class', $pageClassValue !== '' ? $pageClassValue : 'default-body-class')
