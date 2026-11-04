{{-- Meta tags partial. Include from a @section('meta_tags') block.
    Available variables: $pageTitle, $metaTitle, $metaDescription, $ogImage, $ogUrl, $ogType, $robots, $canonical
--}}
@php
    $seo = \App\Models\Setting::getAllAsArray();

    $appName   = trim((string) ($seo['seo_site_title'] ?? '')) ?: config('app.name', 'StAutoparts');
    $separator = trim((string) ($seo['seo_title_separator'] ?? '')) ?: '|';

    $defaultMetaTitle = trim((string) ($seo['seo_default_meta_title'] ?? '')) ?: null;
    $defaultMetaDesc  = trim((string) ($seo['seo_default_meta_description'] ?? '')) ?: null;
    $defaultOgImage   = trim((string) ($seo['seo_default_og_image'] ?? '')) ?: null;

    $metaTitle = $metaTitle ?? ($page->meta_title ?? null) ?? $defaultMetaTitle;
    $metaDesc  = $metaDescription ?? ($page->meta_description ?? null) ?? $defaultMetaDesc;
    $metaKeys  = trim((string) ($seo['seo_default_meta_keywords'] ?? '')) ?: null;
    $ogImage   = $ogImage ?? ($page->og_image ?? null) ?? $defaultOgImage;
    $ogImagePrefix = $ogImagePrefix ?? 'assets/images';
    $ogUrl     = $ogUrl ?? url()->current();
    $ogType    = $ogType ?? 'website';
    $canonical = $canonical ?? $ogUrl;

    $indexingEnabled = ($seo['seo_indexing_enabled'] ?? '1') !== '0';
    // Default to noindex for pages that do not explicitly opt in (auth, cart,
    // checkout, account pages). Public content pages pass "index, follow".
    $robots = $robots ?? 'noindex, nofollow';
    if (! $indexingEnabled) {
        $robots = 'noindex, nofollow';
    }

    $twitterHandle = trim($seo['seo_twitter_handle'] ?? '');
    if ($twitterHandle !== '' && ! str_starts_with($twitterHandle, '@')) {
        $twitterHandle = '@'.$twitterHandle;
    }

    $ogImageUrl = $ogImage ? storedImageUrl($ogImage, $ogImagePrefix, '') : null;
    $ogTitle = $pageTitle ?? $metaTitle ?? $appName;
@endphp

<title>{{ $pageTitle ?? ($metaTitle ? $metaTitle . ' ' . $separator . ' ' . $appName : $appName) }}</title>

<link rel="canonical" href="{{ $canonical }}">

@if($metaTitle)
<meta name="title" content="{{ $metaTitle }}">
@endif
@if($metaDesc)
<meta name="description" content="{{ $metaDesc }}">
@endif
@if($metaKeys)
<meta name="keywords" content="{{ $metaKeys }}">
@endif
<meta name="robots" content="{{ $robots }}">

@if(!empty($seo['seo_google_site_verification']))
<meta name="google-site-verification" content="{{ $seo['seo_google_site_verification'] }}">
@endif
@if(!empty($seo['seo_bing_site_verification']))
<meta name="msvalidate.01" content="{{ $seo['seo_bing_site_verification'] }}">
@endif
@if(!empty($seo['seo_pinterest_verification']))
<meta name="p:domain_verify" content="{{ $seo['seo_pinterest_verification'] }}">
@endif

<meta property="og:type" content="{{ $ogType }}">
<meta property="og:site_name" content="{{ $appName }}">
<meta property="og:title" content="{{ $ogTitle }}">
@if($metaDesc)
<meta property="og:description" content="{{ $metaDesc }}">
@endif
<meta property="og:url" content="{{ $ogUrl }}">
@if($ogImageUrl)
<meta property="og:image" content="{{ $ogImageUrl }}">
@endif

<meta name="twitter:card" content="{{ $ogImageUrl ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $ogTitle }}">
@if($metaDesc)
<meta name="twitter:description" content="{{ $metaDesc }}">
@endif
@if($ogImageUrl)
<meta name="twitter:image" content="{{ $ogImageUrl }}">
@endif
@if($twitterHandle)
<meta name="twitter:site" content="{{ $twitterHandle }}">
<meta name="twitter:creator" content="{{ $twitterHandle }}">
@endif
