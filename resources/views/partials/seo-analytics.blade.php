{{-- Head analytics: Google Tag Manager, GA4 and Facebook Pixel. --}}
@php
    $seoAnalytics = \App\Models\Setting::getAllAsArray();
    $gtmId = trim($seoAnalytics['seo_gtm_container_id'] ?? '');
    $ga4Id = trim($seoAnalytics['seo_ga4_measurement_id'] ?? '');
    $fbPixelId = trim($seoAnalytics['seo_facebook_pixel_id'] ?? '');
@endphp

@if($gtmId)
<script>
(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});
var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';
j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','{{ $gtmId }}');
</script>
@endif

@if($ga4Id)
<script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga4Id }}"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', '{{ $ga4Id }}');
</script>
@endif

@if($fbPixelId)
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window,document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '{{ $fbPixelId }}');
fbq('track', 'PageView');
</script>
@endif
