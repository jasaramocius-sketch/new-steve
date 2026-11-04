<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

if (! function_exists('globalCommissionPercent')) {
    function globalCommissionPercent(): float
    {
        static $commissionPercent = null;

        if ($commissionPercent === null) {
            $commissionPercent = (float) Cache::rememberForever(
                'pricing.global_commission_percent',
                fn () => Setting::get('global_commission_percent', 0)
            );
        }

        return $commissionPercent;
    }
}

if (! function_exists('calculateBuyerPrice')) {
    function calculateBuyerPrice($basePrice, $commissionPercent): float
    {
        $percent = max(0, min(100, (float) $commissionPercent));

        return round((float) $basePrice * (1 + ($percent / 100)), 2);
    }
}

if (! function_exists('buyerPrice')) {
    /**
     * Buyer-facing price for a base price.
     *
     * Pass the product to honour a product-specific commission; without it the
     * global commission setup in admin settings is used.
     */
    function buyerPrice($basePrice, $product = null): float
    {
        $percent = globalCommissionPercent();

        if ($product instanceof \App\Models\Product) {
            $percent = $product->effectiveCommissionPercent();
        } elseif (is_object($product) && isset($product->commission) && $product->commission !== null && $product->commission !== '') {
            $percent = max(0, min(100, (float) $product->commission));
        } elseif (is_array($product) && array_key_exists('commission', $product) && $product['commission'] !== null && $product['commission'] !== '') {
            $percent = max(0, min(100, (float) $product['commission']));
        }

        return calculateBuyerPrice($basePrice, $percent);
    }
}

if (! function_exists('sortUrl')) {
    function sortUrl($column, $currentSortBy, $currentSortDir): string
    {
        $newDir = $currentSortBy === $column && $currentSortDir === 'asc' ? 'desc' : 'asc';

        return request()->fullUrlWithQuery(['sort_by' => $column, 'sort_dir' => $newDir, 'page' => null]);
    }
}

if (! function_exists('sortIndicator')) {
    function sortIndicator($column, $currentSortBy, $currentSortDir): string
    {
        if ($currentSortBy !== $column) {
            return '';
        }

        return $currentSortDir === 'asc'
            ? '<small class="text-muted ms-1"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="display:inline"><polyline points="18 15 12 9 6 15"></polyline></svg></small>'
            : '<small class="text-muted ms-1"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="display:inline"><polyline points="6 9 12 15 18 9"></polyline></svg></small>';
    }
}

if (! function_exists('resolveImageSource')) {
    function resolveImageSource(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $candidates = [public_path($path), storage_path('app/public/'.$path)];
        foreach ($candidates as $candidate) {
            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}

if (! function_exists('normalizeImagePath')) {
    function normalizeImagePath($value, ?string $legacyPrefix = null): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        $value = trim((string) $value);

        if (preg_match('#^https?://#i', $value)) {
            return $value;
        }

        $value = preg_replace('#^/+#', '', $value);

        if (str_starts_with($value, 'storage/')) {
            $value = substr($value, strlen('storage/'));
        }

        if (str_starts_with($value, 'public/')) {
            $value = substr($value, strlen('public/'));
        }

        if (str_contains($value, '/')) {
            return $value;
        }

        if ($legacyPrefix) {
            return $legacyPrefix.'/'.$value;
        }

        return $value;
    }
}

if (! function_exists('storedPath')) {
    function storedPath($value, ?string $legacyPrefix = null, string $fallback = 'assets/images/placeholder.png'): string
    {
        $path = normalizeImagePath($value, $legacyPrefix);
        if (! $path) {
            return $fallback;
        }

        // Absolute URLs (e.g. full storage URLs stored in the cart session)
        // are already complete — pass them through instead of falling back.
        if (preg_match('#^https?://#i', $path)) {
            return storedImageUrl($path, null, $fallback);
        }

        if (str_starts_with($path, 's3://')) {
            return sourceS3ImageUrl($path) ?? asset($fallback);
        }

        return $path;
    }
}

if (! function_exists('isSourceS3ImageReference')) {
    function isSourceS3ImageReference(?string $value): bool
    {
        if (! $value) {
            return false;
        }

        $parts = parse_url(trim($value));
        $bucket = strtolower((string) config('filesystems.disks.source_images.bucket'));
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));

        if ($bucket === '' || empty($parts['path'])) {
            return false;
        }

        if ($scheme === 's3') {
            return strtolower((string) ($parts['host'] ?? '')) === $bucket;
        }

        if ($scheme !== 'http' && $scheme !== 'https') {
            return false;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));

        return $host === $bucket.'.s3.amazonaws.com'
            || (str_starts_with($host, $bucket.'.s3.') && str_ends_with($host, '.amazonaws.com'));
    }
}

if (! function_exists('sourceS3ImageUrl')) {
    function sourceS3ImageUrl(string $value): ?string
    {
        $parts = parse_url($value);
        if (! isSourceS3ImageReference($value)) {
            return null;
        }

        $objectKey = rawurldecode(ltrim($parts['path'], '/'));

        try {
            return Storage::disk('source_images')->temporaryUrl($objectKey, now()->addMinutes(15));
        } catch (\Throwable) {
            return null;
        }
    }
}

if (! function_exists('webpOriginal')) {
    function webpOriginal(string $path): ?string
    {
        if (! preg_match('/\.webp$/i', $path)) {
            return null;
        }

        $info = pathinfo($path);
        foreach (['jpg', 'jpeg', 'png', 'gif'] as $ext) {
            $candidate = $info['dirname'].'/'.$info['filename'].'.'.$ext;
            $checkPath = str_starts_with($candidate, 'uploads/') ? 'storage/'.$candidate : $candidate;
            if (file_exists(public_path($checkPath))) {
                return $checkPath;
            }
        }

        return null;
    }
}

if (! function_exists('imageUrl')) {
    function imageUrl(string $path): string
    {
        if (preg_match('/\.webp$/i', $path)) {
            $original = webpOriginal($path);
            if ($original !== null) {
                return asset($original);
            }
        }

        if (str_starts_with($path, 'uploads/')) {
            return asset('storage/'.$path);
        }

        return asset($path);
    }
}

if (! function_exists('storedImageUrl')) {
    function storedImageUrl($value, ?string $legacyPrefix = null, string $fallback = 'assets/images/placeholder.png'): string
    {
        $path = normalizeImagePath($value, $legacyPrefix);
        if (! $path) {
            return asset($fallback);
        }

        if (preg_match('#^https?://#i', $path)) {
            return sourceS3ImageUrl($path) ?? $path;
        }

        if (str_starts_with($path, 's3://')) {
            return sourceS3ImageUrl($path) ?? asset($fallback);
        }

        if (str_starts_with($path, 'uploads/')) {
            return asset('storage/'.$path);
        }

        return imageUrl($path);
    }
}

if (! function_exists('webpExists')) {
    function webpExists(string $path): bool
    {
        $info = pathinfo($path);
        $webpPath = $info['dirname'].'/'.$info['filename'].'.webp';
        $checkPath = str_starts_with($webpPath, 'uploads/') ? 'storage/'.$webpPath : $webpPath;

        return file_exists(public_path($checkPath));
    }
}

if (! function_exists('webpSrc')) {
    function webpSrc(string $path): string
    {
        $info = pathinfo($path);
        $webpPath = $info['dirname'].'/'.$info['filename'].'.webp';
        if (str_starts_with($webpPath, 'uploads/')) {
            return asset('storage/'.$webpPath);
        }

        return asset($webpPath);
    }
}

if (! function_exists('generateTransactionId')) {
    function generateTransactionId(): string
    {
        return 'pay_'.Str::random(24);
    }
}

if (! function_exists('normalizeStoredUrl')) {
    function normalizeStoredUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }
        $path = parse_url($url, PHP_URL_PATH);
        if (! is_string($path) || $path === '') {
            return $url;
        }
        $appBasePath = parse_url((string) config('app.url'), PHP_URL_PATH);
        if ($appBasePath && $appBasePath !== '/' && str_starts_with($path, $appBasePath)) {
            $path = substr($path, strlen($appBasePath));
        }
        return rtrim(url('/'), '/') . $path;
    }
}

if (! function_exists('prefixAppBasePathInHtml')) {
    /**
     * Rewrite root-relative href/src/action values in stored HTML so they point
     * at the app's subdirectory (e.g. "/" -> "/new-steve/") instead of the
     * domain root. External URLs and paths already containing the base path are
     * left untouched.
     */
    function prefixAppBasePathInHtml(?string $html): string
    {
        if (! $html) {
            return '';
        }

        $basePath = rtrim((string) parse_url((string) config('app.url'), PHP_URL_PATH), '/');
        if ($basePath === '' || $basePath === '/') {
            return $html;
        }

        return preg_replace_callback(
            '/\b(href|src|action)=(["\'])\/(?!\/)([^"\']*)\2/i',
            function ($matches) use ($basePath) {
                $path = '/'.ltrim($matches[3], '/');

                if ($path === $basePath || str_starts_with($path, $basePath.'/')) {
                    return $matches[0];
                }

                return $matches[1].'='.$matches[2].$basePath.$path.$matches[2];
            },
            $html
        );
    }
}

if (! function_exists('defaultRobotsTxt')) {
    /**
     * Build the default robots.txt content.
     *
     * The sitemap URL is stored as a placeholder so the value stays portable
     * between local development and the live domain.
     */
    function defaultRobotsTxt(): string
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /user',
            'Disallow: /checkout',
            'Disallow: /cart',
            'Disallow: /compare',
            '',
            'Sitemap: {{sitemap}}',
        ];

        return implode("\n", $lines);
    }
}

if (! function_exists('normalizeAppUrlPath')) {
    /**
     * Convert an absolute or relative URL to an app-relative path.
     *
     * Menu links are stored without a host so they keep working when the site
     * moves between domains or between a subdirectory and the domain root.
     */
    function normalizeAppUrlPath(string $url): string
    {
        $url = trim($url);

        if ($url === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $url)) {
            $url = (string) parse_url($url, PHP_URL_PATH);
        }

        $url = '/'.ltrim($url, '/');

        $basePaths = array_filter([
            trim((string) parse_url((string) config('app.url'), PHP_URL_PATH), '/'),
            trim((string) request()->getBaseUrl(), '/'),
        ]);

        foreach (array_unique($basePaths) as $basePath) {
            $prefix = '/'.$basePath;

            while ($url === $prefix || str_starts_with($url, $prefix.'/')) {
                $url = '/'.ltrim(substr($url, strlen($prefix)), '/');
            }
        }

        return $url === '' ? '/' : $url;
    }
}

if (! function_exists('robotsLocalHosts')) {
    /**
     * Hosts whose sitemap URLs should be rewritten to the current domain.
     *
     * @return array<int, string>
     */
    function robotsLocalHosts(): array
    {
        $hosts = [
            'localhost',
            '127.0.0.1',
            '::1',
            request()->getHost(),
            parse_url((string) config('app.url'), PHP_URL_HOST),
        ];

        return array_values(array_unique(array_filter(array_map(
            static fn ($host) => strtolower((string) $host),
            $hosts
        ))));
    }
}

if (! function_exists('normalizeRobotsTxtForStorage')) {
    /**
     * Replace host-specific sitemap URLs with a portable placeholder.
     *
     * This keeps the saved robots.txt content working when the site moves to
     * another domain. External sitemap URLs are left untouched.
     */
    function normalizeRobotsTxtForStorage(string $content): string
    {
        $localHosts = robotsLocalHosts();

        return preg_replace_callback('/^Sitemap:\s*(\S+)\s*$/mi', function ($matches) use ($localHosts) {
            $host = strtolower((string) parse_url($matches[1], PHP_URL_HOST));

            if ($host === '' || in_array($host, $localHosts, true)) {
                return 'Sitemap: {{sitemap}}';
            }

            return $matches[0];
        }, $content);
    }
}

if (! function_exists('renderRobotsTxt')) {
    /**
     * Render robots.txt for the current request.
     *
     * Existing sitemap lines pointing at localhost or the current host are
     * rebased automatically, so no domain needs to be hardcoded.
     */
    function renderRobotsTxt(?string $content = null): string
    {
        $sitemapUrl = url('/sitemap.xml');
        $content = trim((string) $content);

        if ($content === '') {
            $content = defaultRobotsTxt();
        }

        $content = str_replace('{{sitemap}}', $sitemapUrl, $content);

        $sitemapEnabled = Setting::get('seo_sitemap_enabled', '1') !== '0';

        if (! $sitemapEnabled) {
            $content = trim((string) preg_replace('/^\s*Sitemap:\s*\S+\s*$/mi', '', $content));
            $content = preg_replace("/\n{3,}/", "\n\n", $content);

            return $content;
        }

        $localHosts = robotsLocalHosts();
        $hasSitemap = false;

        $content = preg_replace_callback('/^Sitemap:\s*(\S+)\s*$/mi', function ($matches) use ($sitemapUrl, $localHosts, &$hasSitemap) {
            $hasSitemap = true;
            $host = strtolower((string) parse_url($matches[1], PHP_URL_HOST));

            if ($host === '' || in_array($host, $localHosts, true)) {
                return 'Sitemap: '.$sitemapUrl;
            }

            return $matches[0];
        }, $content);

        if (! $hasSitemap) {
            $content = rtrim($content)."\n\nSitemap: ".$sitemapUrl;
        }

        return $content;
    }
}
