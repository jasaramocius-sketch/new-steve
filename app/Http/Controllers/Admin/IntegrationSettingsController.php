<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Throwable;

class IntegrationSettingsController extends Controller
{
    public function socialLogins()
    {
        $settings = Setting::getAllAsArray();

        return view('admin.settings.social-logins', [
            'settings' => $settings,
            'googleClientSecretConfigured' => ! empty($settings['google_client_secret']) || ! empty(config('services.google.client_secret')),
            'facebookClientSecretConfigured' => ! empty($settings['facebook_client_secret']) || ! empty(config('services.facebook.client_secret')),
            'twitterClientSecretConfigured' => ! empty($settings['twitter_client_secret']) || ! empty(config('services.twitter.client_secret')),
            'appleClientSecretConfigured' => ! empty($settings['apple_client_secret']) || ! empty(config('services.apple.client_secret')),
        ]);
    }

    public function updateSocialLogins(Request $request)
    {
        $validated = $request->validate([
            'google_login_enabled' => 'nullable|boolean',
            'google_client_id' => 'nullable|string|max:255',
            'google_client_secret' => 'nullable|string|max:4096',
            'google_redirect' => 'nullable|string|max:500',
            'facebook_login_enabled' => 'nullable|boolean',
            'facebook_client_id' => 'nullable|string|max:255',
            'facebook_client_secret' => 'nullable|string|max:4096',
            'facebook_redirect' => 'nullable|string|max:500',
            'twitter_login_enabled' => 'nullable|boolean',
            'twitter_client_id' => 'nullable|string|max:255',
            'twitter_client_secret' => 'nullable|string|max:4096',
            'twitter_redirect' => 'nullable|string|max:500',
            'apple_login_enabled' => 'nullable|boolean',
            'apple_client_id' => 'nullable|string|max:255',
            'apple_team_id' => 'nullable|string|max:255',
            'apple_key_id' => 'nullable|string|max:255',
            'apple_client_secret' => 'nullable|string|max:8192',
            'apple_redirect' => 'nullable|string|max:500',
        ]);

        Setting::set('google_login', $request->boolean('google_login_enabled') ? '1' : '0');
        Setting::set('facebook_login', $request->boolean('facebook_login_enabled') ? '1' : '0');
        Setting::set('twitter_login', $request->boolean('twitter_login_enabled') ? '1' : '0');
        Setting::set('apple_login', $request->boolean('apple_login_enabled') ? '1' : '0');

        Setting::set('google_client_id', (string) ($validated['google_client_id'] ?? ''));
        Setting::set('google_redirect', (string) ($validated['google_redirect'] ?? ''));
        Setting::set('facebook_client_id', (string) ($validated['facebook_client_id'] ?? ''));
        Setting::set('facebook_redirect', (string) ($validated['facebook_redirect'] ?? ''));
        Setting::set('twitter_client_id', (string) ($validated['twitter_client_id'] ?? ''));
        Setting::set('twitter_redirect', (string) ($validated['twitter_redirect'] ?? ''));
        Setting::set('apple_client_id', (string) ($validated['apple_client_id'] ?? ''));
        Setting::set('apple_team_id', (string) ($validated['apple_team_id'] ?? ''));
        Setting::set('apple_key_id', (string) ($validated['apple_key_id'] ?? ''));
        Setting::set('apple_redirect', (string) ($validated['apple_redirect'] ?? ''));

        $this->storeSecret($request, 'google_client_secret', $validated['google_client_secret'] ?? null);
        $this->storeSecret($request, 'facebook_client_secret', $validated['facebook_client_secret'] ?? null);
        $this->storeSecret($request, 'twitter_client_secret', $validated['twitter_client_secret'] ?? null);
        $this->storeSecret($request, 'apple_client_secret', $validated['apple_client_secret'] ?? null);

        return redirect()->route('admin.settings.social-logins')->with('success', 'Social login settings updated.');
    }

    public function facebookChat()
    {
        return view('admin.settings.facebook-chat', [
            'settings' => Setting::getAllAsArray(),
        ]);
    }

    public function updateFacebookChat(Request $request)
    {
        $validated = $request->validate([
            'facebook_chat_enabled' => 'nullable|boolean',
            'facebook_page_id' => 'nullable|string|max:255',
            'facebook_chat_theme_color' => 'nullable|string|max:20',
            'facebook_chat_logged_in_greeting' => 'nullable|string|max:255',
            'facebook_chat_logged_out_greeting' => 'nullable|string|max:255',
        ]);

        Setting::set('facebook_chat_enabled', $request->boolean('facebook_chat_enabled') ? '1' : '0');
        Setting::set('facebook_page_id', (string) ($validated['facebook_page_id'] ?? ''));
        Setting::set('facebook_chat_theme_color', (string) ($validated['facebook_chat_theme_color'] ?? ''));
        Setting::set('facebook_chat_logged_in_greeting', (string) ($validated['facebook_chat_logged_in_greeting'] ?? ''));
        Setting::set('facebook_chat_logged_out_greeting', (string) ($validated['facebook_chat_logged_out_greeting'] ?? ''));

        return redirect()->route('admin.settings.facebook-chat')->with('success', 'Facebook Chat settings updated.');
    }

    public function facebookComment()
    {
        return view('admin.settings.facebook-comment', [
            'settings' => Setting::getAllAsArray(),
        ]);
    }

    public function updateFacebookComment(Request $request)
    {
        $validated = $request->validate([
            'facebook_comment_enabled' => 'nullable|boolean',
            'facebook_app_id' => 'nullable|string|max:255',
            'facebook_comment_num_posts' => 'nullable|integer|min:1|max:50',
        ]);

        Setting::set('facebook_comment_enabled', $request->boolean('facebook_comment_enabled') ? '1' : '0');
        Setting::set('facebook_app_id', (string) ($validated['facebook_app_id'] ?? ''));
        Setting::set('facebook_comment_num_posts', (string) ($validated['facebook_comment_num_posts'] ?? '5'));

        return redirect()->route('admin.settings.facebook-comment')->with('success', 'Facebook Comment settings updated.');
    }

    public function googleMap()
    {
        $settings = Setting::getAllAsArray();

        return view('admin.settings.google-map', [
            'settings' => $settings,
            'apiKeyConfigured' => ! empty($settings['google_map_api_key']),
        ]);
    }

    public function updateGoogleMap(Request $request)
    {
        $validated = $request->validate([
            'google_map_api_key' => 'nullable|string|max:4096',
            'google_map_enabled' => 'nullable|boolean',
        ]);

        Setting::set('google_map_enabled', $request->boolean('google_map_enabled') ? '1' : '0');
        $this->storeSecret($request, 'google_map_api_key', $validated['google_map_api_key'] ?? null);

        return redirect()->route('admin.settings.google-map')->with('success', 'Google Map settings updated.');
    }

    public function googleFirebase()
    {
        $settings = Setting::getAllAsArray();

        return view('admin.settings.google-firebase', [
            'settings' => $settings,
            'serverKeyConfigured' => ! empty($settings['firebase_server_key']),
        ]);
    }

    public function updateGoogleFirebase(Request $request)
    {
        $validated = $request->validate([
            'firebase_enabled' => 'nullable|boolean',
            'firebase_api_key' => 'nullable|string|max:255',
            'firebase_auth_domain' => 'nullable|string|max:255',
            'firebase_project_id' => 'nullable|string|max:255',
            'firebase_sender_id' => 'nullable|string|max:255',
            'firebase_app_id' => 'nullable|string|max:255',
            'firebase_server_key' => 'nullable|string|max:4096',
        ]);

        Setting::set('firebase_enabled', $request->boolean('firebase_enabled') ? '1' : '0');
        foreach (['firebase_api_key', 'firebase_auth_domain', 'firebase_project_id', 'firebase_sender_id', 'firebase_app_id'] as $key) {
            Setting::set($key, (string) ($validated[$key] ?? ''));
        }
        $this->storeSecret($request, 'firebase_server_key', $validated['firebase_server_key'] ?? null);

        return redirect()->route('admin.settings.google-firebase')->with('success', 'Google Firebase settings updated.');
    }

    private function storeSecret(Request $request, string $key, ?string $value): void
    {
        if ($request->filled($key) && $value !== null) {
            Setting::set($key, Crypt::encryptString($value));
        }
    }

    public static function decryptSecret(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (Throwable) {
            return $value;
        }
    }
}
