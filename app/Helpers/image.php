<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Http;

if (! function_exists('convertToWebp')) {
    function convertToWebp(string $sourcePath, string $destPath, int $quality = 80): bool
    {
        if (! file_exists($sourcePath)) {
            return false;
        }

        $info = @getimagesize($sourcePath);
        if ($info === false) {
            return false;
        }

        $mime = $info['mime'];
        $imageResource = match ($mime) {
            'image/jpeg', 'image/jpg', 'image/pjpeg' => @imagecreatefromjpeg($sourcePath),
            'image/png' => @imagecreatefrompng($sourcePath),
            'image/gif' => @imagecreatefromgif($sourcePath),
            'image/webp' => @imagecreatefromwebp($sourcePath),
            default => null,
        };

        if (! $imageResource) {
            return false;
        }

        if ($mime === 'image/png') {
            imagepalettetotruecolor($imageResource);
            imagealphablending($imageResource, true);
            imagesavealpha($imageResource, true);
        }

        $result = @imagewebp($imageResource, $destPath, $quality);
        imagedestroy($imageResource);

        return $result;
    }
}

if (! function_exists('saveImageWithWebp')) {
    function saveImageWithWebp($file, string $dir = 'uploads'): string
    {
        $subdir = 'uploads/'.now()->format('Y/m');
        $destination = storage_path('app/public/'.$subdir);

        // Directory auto-create karo agar nahi hai
        if (! is_dir($destination)) {
            mkdir($destination, 0775, true);
        }

        // Permission check
        if (! is_writable($destination)) {
            throw new Exception('Upload directory is not writable: '.$destination);
        }

        $filename = time().'_'.uniqid().'.'.$file->extension();

        $file->move($destination, $filename);

        $fullPath = $destination.'/'.$filename;
        $webpPath = $destination.'/'.pathinfo($filename, PATHINFO_FILENAME).'.webp';

        convertToWebp($fullPath, $webpPath);

        return 'storage/'.$subdir.'/'.$filename;
    }
}

if (! function_exists('saveImageFromUrlWithWebp')) {
    function saveImageFromUrlWithWebp(string $url, string $dir = 'uploads'): ?string
    {
        // SSRF guard: sirf public http(s) URLs allow karo
        $parts = parse_url($url);
        if (! $parts || ! in_array($parts['scheme'] ?? '', ['http', 'https'], true) || empty($parts['host'])) {
            return null;
        }
        $host = strtolower($parts['host']);
        if ($host === 'localhost' || str_ends_with($host, '.local') || str_ends_with($host, '.internal')) {
            return null;
        }
        $ip = gethostbyname($host);
        if ($ip === $host || ! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return null;
        }

        $response = Http::timeout(15)->get($url);
        if ($response->failed()) {
            return null;
        }

        if (strlen($response->body()) > 10 * 1024 * 1024) {
            return null;
        }

        $contentType = $response->header('Content-Type');
        if (! str_contains($contentType, 'image/')) {
            return null;
        }

        $extension = 'jpg';
        if (str_contains($contentType, 'jpeg') || str_contains($contentType, 'jpg')) {
            $extension = 'jpg';
        } elseif (str_contains($contentType, 'png')) {
            $extension = 'png';
        } elseif (str_contains($contentType, 'webp')) {
            $extension = 'webp';
        } elseif (str_contains($contentType, 'gif')) {
            $extension = 'gif';
        }

        $filename = time().'_'.uniqid().'.'.$extension;
        $subdir = 'uploads/'.now()->format('Y/m');
        $dirPath = storage_path('app/public/'.$subdir);
        if (! file_exists($dirPath)) {
            mkdir($dirPath, 0775, true);
        }
        file_put_contents($dirPath.'/'.$filename, $response->body());

        $webpPath = $dirPath.'/'.pathinfo($filename, PATHINFO_FILENAME).'.webp';
        $fullPath = $dirPath.'/'.$filename;

        convertToWebp($fullPath, $webpPath);

        return 'storage/'.$subdir.'/'.$filename;
    }
}

if (! function_exists('deleteImageFiles')) {
    function deleteImageFiles(?string $filename, string $dir = 'uploads'): void
    {
        if (! $filename) {
            return;
        }

        $path = normalizeImagePath($filename, $dir);

        foreach ([public_path($path), storage_path('app/public/'.$path)] as $fullPath) {
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }

            $webpPath = dirname($fullPath).'/'.pathinfo($fullPath, PATHINFO_FILENAME).'.webp';
            if (file_exists($webpPath)) {
                @unlink($webpPath);
            }
        }
    }
}

if (! function_exists('webpExists')) {
    function webpExists(string $path): bool
    {
        $info = pathinfo($path);

        return file_exists(public_path($info['dirname'].'/'.$info['filename'].'.webp'));
    }
}

if (! function_exists('webpSrc')) {
    function webpSrc(string $path): string
    {
        $info = pathinfo($path);

        return asset($info['dirname'].'/'.$info['filename'].'.webp');
    }
}

if (! function_exists('ensureResponsiveVariant')) {
    function ensureResponsiveVariant(string $src, int $targetWidth): ?string
    {
        $checkPath = str_starts_with($src, 'uploads/') ? 'storage/'.$src : $src;
        $fullPath = public_path($checkPath);
        if (! file_exists($fullPath)) {
            return null;
        }

        $info = pathinfo(str_replace('\\', '/', ltrim($src, '/')));
        $variantName = $info['filename'].'_'.$targetWidth.'.webp';
        $variantRel = trim($info['dirname'].'/'.$variantName, '/');
        $variantPath = public_path(str_starts_with($variantRel, 'uploads/') ? 'storage/'.$variantRel : $variantRel);

        if (file_exists($variantPath)) {
            return $variantRel;
        }

        $imageInfo = @getimagesize($fullPath);
        if ($imageInfo === false || $imageInfo[0] <= $targetWidth) {
            return null;
        }

        $mime = $imageInfo['mime'];
        $srcImg = match ($mime) {
            'image/jpeg', 'image/jpg', 'image/pjpeg' => @imagecreatefromjpeg($fullPath),
            'image/png' => @imagecreatefrompng($fullPath),
            'image/gif' => @imagecreatefromgif($fullPath),
            'image/webp' => @imagecreatefromwebp($fullPath),
            default => null,
        };
        if (! $srcImg) {
            return null;
        }

        if ($mime === 'image/png') {
            imagepalettetotruecolor($srcImg);
        }

        $origW = imagesx($srcImg);
        $origH = imagesy($srcImg);
        $targetH = (int) round($origH * ($targetWidth / $origW));

        $resized = imagecreatetruecolor($targetWidth, $targetH);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagecopyresampled($resized, $srcImg, 0, 0, 0, 0, $targetWidth, $targetH, $origW, $origH);
        imagedestroy($srcImg);

        $ok = @imagewebp($resized, $variantPath, 80);
        imagedestroy($resized);

        if (! $ok || ! file_exists($variantPath)) {
            return null;
        }

        return $variantRel;
    }
}

if (! function_exists('imgTag')) {
    function imgTag(string $src, ?string $alt = '', string $class = '', string $extra = '', int $displayWidth = 0): string
    {
        $alt = $alt ?? '';
        $src = trim($src);

        if (str_starts_with($src, '/storage/')) {
            $src = substr($src, 1);
        }

        if (str_starts_with($src, 'storage/storage/')) {
            $src = substr($src, strlen('storage/'));
        }

        if (empty($src)) {
            $src = 'assets/images/placeholder.png';
        }

        $checkPath = str_starts_with($src, 'uploads/') ? 'storage/'.$src : $src;
        $isRemote = (bool) preg_match('#^https?://#i', $src);
        if (! $isRemote && ! file_exists(public_path($checkPath))) {
            $src = 'assets/images/placeholder.png';
            $checkPath = $src;
        }

        $placeholder = asset('assets/images/placeholder.png');
        $onerror = "this.onerror=null;this.src='{$placeholder}'";
        $classAttr = $class ? " class=\"{$class}\"" : '';
        $extraAttr = $extra ? " {$extra}" : '';

        $imgSrc = $src;
        $webpSource = null;

        if (preg_match('/\.webp$/i', $src)) {
            $original = webpOriginal($src);
            if ($original !== null) {
                $imgSrc = $original;
                $webpSource = $src;
            }
        } elseif (webpExists($src)) {
            $webpSource = webpSrc($src);
        }

        $assetSrc = str_starts_with($imgSrc, 'uploads/') ? 'storage/'.$imgSrc : $imgSrc;
        $assetWebp = $webpSource ? (str_starts_with($webpSource, 'uploads/') ? 'storage/'.$webpSource : $webpSource) : null;

        $srcsetAttr = '';
        if ($displayWidth > 0 && ! $isRemote) {
            $v250 = ensureResponsiveVariant($src, 250);
            $v500 = ensureResponsiveVariant($src, 500);
            $srcsetParts = [];
            if ($v250) {
                $a = str_starts_with($v250, 'uploads/') ? 'storage/'.$v250 : $v250;
                $srcsetParts[] = asset($a).' 250w';
            }
            if ($v500) {
                $a = str_starts_with($v500, 'uploads/') ? 'storage/'.$v500 : $v500;
                $srcsetParts[] = asset($a).' 500w';
            }
            if ($srcsetParts) {
                $srcsetAttr = ' srcset="'.implode(', ', $srcsetParts).'" sizes="'.$displayWidth.'px"';
            }
        }

        $loadingAttr = str_contains($extra, 'loading=') ? '' : ' loading="lazy"';
        $decodingAttr = str_contains($extra, 'decoding=') ? '' : ' decoding="async"';
        $html = '<img src="'.asset($assetSrc).'" alt="'.e($alt).'"'.$classAttr.$loadingAttr.$decodingAttr.' onerror="'.$onerror.'"'.$extraAttr.'>';

        static $webpFrontend = null;
        if ($webpFrontend === null) {
            $webpFrontend = Setting::get('webp_frontend', '1') === '1';
        }
        if ($srcsetAttr && $webpFrontend) {
            $html = '<picture><source'.$srcsetAttr.' type="image/webp">'.$html.'</picture>';
        } elseif ($assetWebp && $webpFrontend) {
            $html = '<picture><source srcset="'.asset($assetWebp).'" type="image/webp">'.$html.'</picture>';
        } elseif ($srcsetAttr) {
            $html = str_replace('<img ', '<img'.$srcsetAttr.' ', $html);
        }

        return $html;
    }
}
