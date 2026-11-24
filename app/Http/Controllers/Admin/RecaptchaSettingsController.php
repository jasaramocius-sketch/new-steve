<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\Recaptcha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Throwable;

class RecaptchaSettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::getAllAsArray();

        return view('backend.settings.google-recaptcha', [
            'settings' => $settings,
            'globalEnabled' => Recaptcha::globallyEnabled(),
            'secretConfigured' => !empty($settings['recaptcha_secret_key']),
            'v3SecretConfigured' => !empty($settings['recaptcha_v3_secret_key']),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'recaptcha_version' => 'required|in:v2,v3',
            'recaptcha_enabled' => 'nullable|boolean',
            'recaptcha_site_key' => 'nullable|string|max:255',
            'recaptcha_secret_key' => 'nullable|string|max:255',
            'recaptcha_v3_site_key' => 'nullable|string|max:255',
            'recaptcha_v3_secret_key' => 'nullable|string|max:255',
            'recaptcha_v3_score' => 'nullable|numeric|min:0|max:1',
            'recaptcha_login' => 'nullable|boolean',
            'recaptcha_register' => 'nullable|boolean',
            'recaptcha_contact' => 'nullable|boolean',
        ]);

        Setting::set('recaptcha_version', $validated['recaptcha_version']);
        Setting::set('recaptcha_enabled', $request->boolean('recaptcha_enabled') ? '1' : '0');
        Setting::set('recaptcha_site_key', $validated['recaptcha_site_key'] ?? '');
        Setting::set('recaptcha_v3_site_key', $validated['recaptcha_v3_site_key'] ?? '');
        Setting::set('recaptcha_v3_score', (string) ($validated['recaptcha_v3_score'] ?? '0.5'));

        if ($request->filled('recaptcha_secret_key')) {
            Setting::set('recaptcha_secret_key', Crypt::encryptString($validated['recaptcha_secret_key']));
        }
        if ($request->filled('recaptcha_v3_secret_key')) {
            Setting::set('recaptcha_v3_secret_key', Crypt::encryptString($validated['recaptcha_v3_secret_key']));
        }

        Setting::set('recaptcha_login', $request->boolean('recaptcha_login') ? '1' : '0');
        Setting::set('recaptcha_register', $request->boolean('recaptcha_register') ? '1' : '0');
        Setting::set('recaptcha_contact', $request->boolean('recaptcha_contact') ? '1' : '0');

        return redirect()->route('admin.settings.google-recaptcha')->with('success', 'Google reCAPTCHA settings updated.');
    }

    public function reset()
    {
        Setting::set('recaptcha_version', 'v2');
        Setting::set('recaptcha_enabled', '0');
        Setting::set('recaptcha_site_key', '');
        Setting::set('recaptcha_secret_key', '');
        Setting::set('recaptcha_v3_site_key', '');
        Setting::set('recaptcha_v3_secret_key', '');
        Setting::set('recaptcha_v3_score', '0.5');
        Setting::set('recaptcha_login', '0');
        Setting::set('recaptcha_register', '0');
        Setting::set('recaptcha_contact', '0');

        return redirect()->route('admin.settings.google-recaptcha')
            ->with('success', 'Google reCAPTCHA settings have been reset. All keys were removed, the master switch is Inactive, and reCAPTCHA is disabled on all forms.');
    }

    public static function decryptSecret(?string $value): ?string
    {
        if (!$value) return null;
        try { return Crypt::decryptString($value); } catch (Throwable) { return $value; }
    }
}
