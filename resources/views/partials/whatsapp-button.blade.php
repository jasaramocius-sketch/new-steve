{{-- Floating WhatsApp chat button. Pulls number from the footer social link config. --}}
@php
    $waNumber = null;
    $footerCols = json_decode(\App\Models\Setting::get('footer_columns', '[]'), true);
    if (is_array($footerCols)) {
        foreach ($footerCols as $col) {
            foreach (($col['links'] ?? []) as $link) {
                $url = trim((string) ($link['url'] ?? ''));
                $platform = strtolower((string) ($link['platform'] ?? ''));
                if ($platform === 'whatsapp' || str_contains($url, 'wa.me') || str_contains($url, 'api.whatsapp.com') || str_contains($url, 'whatsapp.com')) {
                    if (preg_match('/(?:wa\.me\/|send\?phone=)([0-9+()\- ]+)/i', $url, $m)) {
                        $waNumber = preg_replace('/[^0-9]/', '', $m[1]);
                    } else {
                        $waNumber = preg_replace('/[^0-9]/', '', $url);
                    }
                    break 2;
                }
            }
        }
    }
@endphp
@if($waNumber)
@once
<style>
    .whatsapp-float {
        position: fixed;
        right: 20px;
        bottom: 20px;
        z-index: 1050;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: #25D366;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, .25);
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .whatsapp-float:hover { transform: scale(1.08); box-shadow: 0 6px 18px rgba(0, 0, 0, .3); color: #fff; }
</style>
@endonce
<a class="whatsapp-float" href="https://wa.me/{{ $waNumber }}?text={{ urlencode('Hello, I have a question about an auto part on your website.') }}" target="_blank" rel="noopener" aria-label="Chat with us on WhatsApp">
    <i class="fab fa-whatsapp"></i>
</a>
@endif