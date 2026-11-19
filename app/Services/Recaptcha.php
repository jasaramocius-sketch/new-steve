<?php

namespace App\Services;

use App\Http\Controllers\Admin\RecaptchaSettingsController;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class Recaptcha
{
    public static function enabled(string $form): bool
    {
        $key = 'recaptcha_' . $form;

        return Setting::get($key) == '1' && self::siteKey() && self::secretKey();
    }

    public static function version(): string
    {
        return Setting::get('recaptcha_version', 'v2') === 'v3' ? 'v3' : 'v2';
    }

    public static function isV3(): bool
    {
        return self::version() === 'v3';
    }

    public static function siteKey(): ?string
    {
        $key = Setting::get(self::isV3() ? 'recaptcha_v3_site_key' : 'recaptcha_site_key');

        return $key ? (string) $key : null;
    }

    public static function secretKey(): ?string
    {
        $key = self::isV3() ? 'recaptcha_v3_secret_key' : 'recaptcha_secret_key';
        $secret = Setting::get($key);

        if (! $secret) {
            return null;
        }

        return RecaptchaSettingsController::decryptSecret($secret);
    }

    public static function verify(Request $request, ?string $action = null): bool
    {
        $response = $request->input('g-recaptcha-response');
        $secret = self::secretKey();

        if (! $response || ! $secret) {
            return false;
        }

        try {
            $res = Http::asForm()->timeout(10)->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $secret,
                'response' => $response,
                'remoteip' => $request->ip(),
            ]);

            if (! $res->successful() || $res->json('success') !== true) {
                return false;
            }

            if (! self::isV3()) {
                return true;
            }

            $threshold = (float) Setting::get('recaptcha_v3_score', '0.5');
            $score = (float) $res->json('score', 0);

            if ($score < $threshold) {
                return false;
            }

            $responseAction = $res->json('action');

            return $action === null || $responseAction === $action;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
