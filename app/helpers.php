<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

if (! function_exists('globalCommissionPercent')) {
    function globalCommissionPercent(): float
    {
        return (float) Cache::rememberForever(
            'pricing.global_commission_percent',
            fn () => Setting::get('global_commission_percent', 0)
        );
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
    function buyerPrice($basePrice): float
    {
        return calculateBuyerPrice($basePrice, globalCommissionPercent());
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

if (! function_exists('sourceS3ImageUrl')) {
    function sourceS3ImageUrl(string $value): ?string
    {
        $parts = parse_url($value);
        $bucket = (string) config('filesystems.disks.source_images.bucket');
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));

        if ($bucket === '' || empty($parts['path'])) {
            return null;
        }

        if ($scheme === 's3') {
            if (strtolower((string) ($parts['host'] ?? '')) !== strtolower($bucket)) {
                return null;
            }
        } elseif ($scheme === 'http' || $scheme === 'https') {
            $host = strtolower((string) ($parts['host'] ?? ''));
            $bucketHost = strtolower($bucket.'.s3.amazonaws.com');
            $isSourceHost = $host === $bucketHost
                || (str_starts_with($host, strtolower($bucket.'.s3.')) && str_ends_with($host, '.amazonaws.com'));

            if (! $isSourceHost) {
                return null;
            }
        } else {
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
