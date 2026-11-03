{{-- Body-start analytics fallbacks. --}}
@php
    $seoAnalytics = \App\Models\Setting::getAllAsArray();
    $gtmId = trim($seoAnalytics['seo_gtm_container_id'] ?? '');
    $fbPixelId = trim($seoAnalytics['seo_facebook_pixel_id'] ?? '');
@endphp

@if($gtmId)
<noscript>
    <iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtmId }}"
        height="0" width="0" style="display:none;visibility:hidden"></iframe>
</noscript>
@endif

@if($fbPixelId)
<noscript>
    <img height="1" width="1" style="display:none"
        src="https://www.facebook.com/tr?id={{ $fbPixelId }}&ev=PageView&noscript=1"
        alt="">
</noscript>
@endif
