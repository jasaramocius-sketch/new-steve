<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\TwoFactor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TwoFactorController extends Controller
{
    public function challenge()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        if (! session('two_factor_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ], [
            'code.required' => 'Enter the 6-digit code or a recovery code.',
        ]);

        $userId = $request->session()->get('two_factor_user_id');

        if (! $userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);

        if (! $user) {
            $request->session()->forget(['two_factor_user_id', 'two_factor_remember']);

            return redirect()->route('login')->withErrors(['email' => 'Your session expired. Please sign in again.']);
        }

        $code = trim((string) $request->input('code'));
        $verified = TwoFactor::verifyCode($user->two_factor_secret, $code);

        if (! $verified && ! empty($user->two_factor_recovery_codes)) {
            $verified = TwoFactor::consumeRecoveryCode($user, $code);
        }

        if (! $verified) {
            return back()->withErrors(['code' => 'That code is invalid or has expired.'])->withInput();
        }

        $remember = (bool) $request->session()->get('two_factor_remember');

        Auth::login($user, $remember);
        $request->session()->forget(['two_factor_user_id', 'two_factor_remember']);
        $request->session()->regenerate();
        $request->session()->put([
            'user_logged_in' => true,
            'user_profile' => $user->only([
                'id', 'name', 'email', 'role', 'phone', 'address', 'city', 'country',
            ]),
        ]);

        (new AuthController())->mergeGuestData();

        return redirect()->route(in_array($user->role, ['master_admin', 'admin', 'staff'])
            ? 'admin.dashboard'
            : 'user.dashboard');
    }

    public function setup(Request $request)
    {
        $user = Auth::user();

        if (! $user->two_factor_secret) {
            $user->two_factor_secret = TwoFactor::generateSecret();
            $user->save();
        }

        $recoveryCodes = $request->session()->pull('recovery_codes', []);
        $otpauthUri = TwoFactor::otpauthUri($user, config('app.name'));

        return view('admin.settings.two-factor', compact('user', 'otpauthUri', 'recoveryCodes'));
    }

    public function confirm(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'code' => 'required|string|regex:/^\d{6}$/',
        ], [
            'code.required' => 'Enter the 6-digit code from your authenticator app.',
            'code.regex' => 'Enter a 6-digit numeric code.',
        ]);

        if (! TwoFactor::verifyCode($user->two_factor_secret, (string) $request->input('code'))) {
            return back()->withErrors(['code' => 'That code did not match. Try again.'])->withInput();
        }

        $recoveryCodes = TwoFactor::generateRecoveryCodes();

        $user->two_factor_confirmed_at = now();
        $user->two_factor_recovery_codes = TwoFactor::hashRecoveryCodes($recoveryCodes);
        $user->save();

        return redirect()->route('admin.settings.two-factor')->with('recovery_codes', $recoveryCodes);
    }

    public function disable(Request $request)
    {
        $user = Auth::user();

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return redirect()->route('admin.settings.two-factor')->with('success', 'Two-factor authentication has been disabled.');
    }
}
