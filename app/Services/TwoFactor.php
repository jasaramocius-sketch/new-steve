<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TwoFactor
{
    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public static function generateSecret(): string
    {
        return self::base32Encode(random_bytes(20));
    }

    public static function code(string $secret, ?int $timestamp = null): string
    {
        $key = self::base32Decode($secret);
        $counter = intdiv($timestamp ?? time(), 30);
        $hash = hash_hmac('sha1', pack('N2', 0, $counter), $key, true);
        $offset = ord($hash[19]) & 0x0f;
        $value = unpack('N', substr($hash, $offset, 4))[1] & 0x7fffffff;

        return str_pad((string) ($value % 1000000), 6, '0', STR_PAD_LEFT);
    }

    public static function verifyCode(?string $secret, string $code, int $window = 1): bool
    {
        if (! $secret || ! preg_match('/^\d{6}$/', $code)) {
            return false;
        }

        $timestamp = time();

        for ($offset = -$window; $offset <= $window; $offset++) {
            if (hash_equals(self::code($secret, $timestamp + ($offset * 30)), $code)) {
                return true;
            }
        }

        return false;
    }

    public static function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            $hex = bin2hex(random_bytes(5));
            $codes[] = substr($hex, 0, 5).'-'.substr($hex, 5, 5);
        }

        return $codes;
    }

    public static function hashRecoveryCodes(array $codes): array
    {
        return array_map(fn (string $code) => Hash::make($code), $codes);
    }

    public static function consumeRecoveryCode(User $user, string $code): bool
    {
        $codes = $user->two_factor_recovery_codes ?? [];

        foreach ($codes as $index => $hashed) {
            if (Hash::check($code, $hashed)) {
                unset($codes[$index]);
                $user->two_factor_recovery_codes = array_values($codes);
                $user->save();

                return true;
            }
        }

        return false;
    }

    public static function otpauthUri(User $user, string $appName): string
    {
        $label = $appName.':'.$user->email;

        return 'otpauth://totp/'.rawurlencode($label)
            .'?secret='.rawurlencode((string) $user->two_factor_secret)
            .'&issuer='.rawurlencode($appName)
            .'&algorithm=SHA1&digits=6&period=30';
    }

    private static function base32Encode(string $bytes): string
    {
        $bits = '';

        foreach (unpack('C*', $bytes) as $byte) {
            $bits .= str_pad(decbin($byte), 8, '0', STR_PAD_LEFT);
        }

        $encoded = '';

        foreach (str_split($bits, 5) as $chunk) {
            $encoded .= self::ALPHABET[bindec(str_pad($chunk, 5, '0'))];
        }

        return $encoded;
    }

    private static function base32Decode(string $secret): string
    {
        $secret = strtoupper(rtrim($secret, '='));
        $bits = '';

        foreach (str_split($secret) as $char) {
            $index = strpos(self::ALPHABET, $char);

            if ($index === false) {
                continue;
            }

            $bits .= str_pad(decbin($index), 5, '0', STR_PAD_LEFT);
        }

        $decoded = '';

        foreach (str_split($bits, 8) as $chunk) {
            if (strlen($chunk) === 8) {
                $decoded .= chr(bindec($chunk));
            }
        }

        return $decoded;
    }
}
