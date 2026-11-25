<?php

namespace App\Support;

use Illuminate\Support\Facades\Crypt;

class CategoryRef
{
    public static function encode(int|string $id, string $slug): string
    {
        $payload = json_encode(['i' => (int) $id, 's' => (string) $slug], JSON_UNESCAPED_SLASHES);
        $encrypted = Crypt::encryptString($payload);

        return rtrim(strtr(base64_encode($encrypted), '+/', '-_'), '=');
    }

    public static function decode(mixed $token, string $expectedSlug): ?int
    {
        if (! is_string($token) || $token === '') {
            return null;
        }

        try {
            $padded = strtr($token, '-_', '+/');
            $padded .= str_repeat('=', (4 - strlen($padded) % 4) % 4);
            $encrypted = base64_decode($padded, true);
            if ($encrypted === false) {
                return null;
            }

            $payload = json_decode(Crypt::decryptString($encrypted), true);
        } catch (\Throwable $e) {
            return null;
        }

        if (! is_array($payload)) {
            return null;
        }

        $id = $payload['i'] ?? null;
        $slug = $payload['s'] ?? null;
        if (! is_numeric($id) || (int) $id <= 0 || ! is_string($slug)) {
            return null;
        }

        if ($slug !== (string) $expectedSlug) {
            return null;
        }

        return (int) $id;
    }

    public static function url(int|string $id, string $slug, array $params = [], bool $absolute = true): string
    {
        $params = array_merge(['slug' => $slug, 'c' => self::encode($id, $slug)], $params);

        return route('category', $params, $absolute);
    }
}
