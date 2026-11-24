<?php

namespace App\Http\Controllers;

use App\Helpers\NotificationHelper;
use App\Mail\WelcomeMail;
use App\Models\Compare;
use App\Models\Subscriber;
use App\Models\User;
use App\Models\Wishlist;
use App\Rules\UniquePhone;
use App\Services\Recaptcha;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use RuntimeException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class AuthController extends Controller
{
    private function dashboardRoute()
    {
        if (! Auth::check()) {
            return null;
        }

        return in_array(Auth::user()->role, ['master_admin', 'admin', 'staff'])
            ? route('admin.dashboard')
            : route('user.dashboard');
    }

    public function mergeGuestData()
    {
        $userId = Auth::id();

        $guestWishlist = session('guest_wishlist', []);
        foreach ($guestWishlist as $productId) {
            Wishlist::firstOrCreate([
                'user_id' => $userId,
                'product_id' => $productId,
            ]);
        }
        session()->forget('guest_wishlist');

        $guestCompare = session('guest_compare', []);
        $existingCount = Compare::where('user_id', $userId)->count();
        $slotsAvailable = 3 - $existingCount;

        if ($slotsAvailable > 0) {
            $merged = 0;
            foreach ($guestCompare as $productId) {
                if ($merged >= $slotsAvailable) {
                    break;
                }

                $compare = Compare::firstOrCreate([
                    'user_id' => $userId,
                    'product_id' => $productId,
                ]);

                if ($compare->wasRecentlyCreated) {
                    $merged++;
                }
            }
        }

        session()->forget('guest_compare');
    }

    public function loginForm()
    {
        if ($route = $this->dashboardRoute()) {
            return redirect()->to($route);
        }

        return view('frontend.auth.login');
    }

    public function registerForm()
    {
        if ($route = $this->dashboardRoute()) {
            return redirect()->to($route);
        }

        return view('frontend.auth.register');
    }

    /**
     * Authenticate user against the database and store real data in session.
     */
    public function login(Request $request)
    {
        if (Recaptcha::enabled('login') && ! Recaptcha::verify($request, 'login')) {
            return back()->withErrors(['g-recaptcha-response' => 'Please complete the reCAPTCHA verification.'])->withInput();
        }

        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Invalid credentials']);
        }

        $user = Auth::user();
        $redirectTo = $this->safeRedirectPath($request->input('redirect'));

        if ($user->requiresEmailVerification()) {
            Auth::logout();
            $request->session()->regenerate();
            $request->session()->put([
                'pending_verification_email' => $user->email,
                'pending_verification_redirect' => $redirectTo,
            ]);

            $verificationEmailSent = true;
            try {
                $user->sendEmailVerificationNotification();
            } catch (TransportExceptionInterface|RuntimeException $exception) {
                $verificationEmailSent = false;
                Log::error('Verification email resend failed during login.', [
                    'user_id' => $user->id,
                    'exception_type' => $exception::class,
                    'message' => $exception->getMessage(),
                ]);
            }

            $redirect = redirect()->route('verification.notice');
            if (! $verificationEmailSent) {
                $redirect->with('warning', 'We could not send your confirmation email. Please use the resend form below.');
            }

            return $redirect->withErrors([
                'email' => 'Please confirm your email address before logging in.',
            ]);
        }

        if ($user->hasTwoFactorEnabled()) {
            $remember = $request->boolean('remember');
            Auth::logout();
            $request->session()->regenerate();
            $request->session()->put([
                'two_factor_user_id' => $user->id,
                'two_factor_remember' => $remember,
                'two_factor_redirect' => $redirectTo,
            ]);

            return redirect()->route('two-factor.challenge');
        }

        $request->session()->regenerate();

        $request->session()->put([
            'user_logged_in' => true,
            'user_profile' => $user->only(['id', 'name', 'email', 'role', 'phone', 'address', 'city', 'country']),
        ]);

        $this->mergeGuestData();

        if ($user->role === 'master_admin' || $user->role === 'admin' || $user->role === 'staff') {
            return redirect()->route('admin.dashboard');
        }

        if ($redirectTo) {
            return redirect()->to($redirectTo);
        }

        return redirect()->route('user.dashboard');
    }

    /**
     * Allow only safe in-app redirect paths to prevent open redirects.
     */
    private function safeRedirectPath($redirect): ?string
    {
        if (! is_string($redirect) || $redirect === '') {
            return null;
        }

        $redirect = trim($redirect);

        if (! str_starts_with($redirect, '/') || str_starts_with($redirect, '//')) {
            return null;
        }

        return $redirect;
    }

    /**
     * Register a new customer account and require email confirmation.
     */
    public function register(Request $request)
    {
        if (Auth::check()) {
            return redirect()->to($this->dashboardRoute());
        }

        $request->merge([
            'email' => strtolower(trim((string) $request->input('email'))),
            'phone' => filled($request->input('phone')) ? trim((string) $request->input('phone')) : null,
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => ['required', 'string', 'max:50', 'regex:/^[0-9+\-\s()]*$/', new UniquePhone],
            'password' => 'required|min:6|confirmed',
            'terms' => 'accepted',
        ], [
            'terms.accepted' => 'Please agree to the Privacy Policy and Terms & Conditions to create an account.',
            'email.unique' => 'This email is already registered. Please sign in instead or contact support if you cannot access your account.',
            'phone.required' => 'Please enter your phone number.',
        ]);

        if (Recaptcha::enabled('register') && ! Recaptcha::verify($request, 'register')) {
            return back()->withErrors(['g-recaptcha-response' => 'Please complete the reCAPTCHA verification.'])->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'customer',
            'email_verification_required_at' => now(),
        ]);

        if ($request->boolean('subscribe')) {
            Subscriber::firstOrCreateForEmail($user->email);
        }

        $redirectTo = $this->safeRedirectPath($request->input('redirect'));
        $request->session()->put([
            'pending_verification_email' => $user->email,
            'pending_verification_redirect' => $redirectTo,
        ]);

        try {
            $user->sendEmailVerificationNotification();
        } catch (TransportExceptionInterface|RuntimeException $exception) {
            Log::error('Verification email delivery failed after account creation.', [
                'user_id' => $user->id,
                'exception_type' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            return redirect()->route('verification.notice')->with(
                'warning',
                'Your account was created, but we could not send the confirmation email. Please use "Resend confirmation email" below or contact support.'
            );
        }

        return redirect()->route('verification.notice')->with(
            'status',
            'Registration successful. Please check your email and confirm your account to activate it.'
        );
    }

    public function showVerificationNotice(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->hasVerifiedEmail()) {
                return redirect()->to($this->dashboardRoute() ?? route('home'));
            }

            return view('frontend.auth.verify-email', [
                'email' => $user->email,
            ]);
        }

        $email = $request->session()->get('pending_verification_email');

        return view('frontend.auth.verify-email', [
            'email' => $email,
        ]);
    }

    public function resendVerification(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = strtolower(trim((string) $request->input('email')));
        $user = User::where('email', $email)->first();

        if ($user && ! $user->hasVerifiedEmail()) {
            try {
                $user->sendEmailVerificationNotification();
            } catch (TransportExceptionInterface|RuntimeException $exception) {
                Log::error('Verification email resend failed.', [
                    'user_id' => $user->id,
                    'exception_type' => $exception::class,
                    'message' => $exception->getMessage(),
                ]);

                return back()->withErrors(['email' => 'Could not send confirmation email right now. Please try again later.'])->withInput();
            }
        }

        $request->session()->put('pending_verification_email', $email);

        return back()->with('status', 'If an account exists for that email and is not yet confirmed, a new confirmation link has been sent.');
    }

    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'Invalid verification link.');
        }

        $alreadyVerified = $user->hasVerifiedEmail();

        if (! $alreadyVerified && $user->markEmailAsVerified()) {
            event(new Verified($user));
        }
        if ($user->email_verification_required_at !== null) {
            $user->forceFill(['email_verification_required_at' => null])->save();
        }

        $redirectTo = $request->session()->pull('pending_verification_redirect');
        $request->session()->forget('pending_verification_email');

        $warning = null;

        if (! $alreadyVerified) {
            NotificationHelper::welcomeUser($user);

            try {
                Mail::to($user->email)->send(new WelcomeMail($user));
            } catch (TransportExceptionInterface|RuntimeException $exception) {
                Log::error('Welcome email delivery failed after email verification.', [
                    'user_id' => $user->id,
                    'exception_type' => $exception::class,
                    'message' => $exception->getMessage(),
                ]);
                $warning = 'Your email is confirmed, but we could not send the welcome email.';
            }
        }

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put([
            'user_logged_in' => true,
            'user_profile' => $user->only(['id', 'name', 'email', 'role', 'phone', 'address', 'city', 'country']),
        ]);

        $this->mergeGuestData();

        $destination = $redirectTo && ! in_array($user->role, ['master_admin', 'admin', 'staff'], true)
            ? $redirectTo
            : ($this->dashboardRoute() ?? route('home'));

        $redirect = redirect()->to($destination)->with(
            'success',
            $alreadyVerified ? 'Your email is already confirmed.' : 'Email confirmed successfully. Your account is now active.'
        );

        if ($warning) {
            $redirect->with('warning', $warning);
        }

        return $redirect;
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function showLogin()
    {
        if ($route = $this->dashboardRoute()) {
            return redirect()->to($route);
        }

        return view('frontend.auth.login');
    }

    public function showForgot()
    {
        if ($route = $this->dashboardRoute()) {
            return redirect()->to($route);
        }

        return view('frontend.auth.forgot');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:255',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $token = Password::broker()->createToken($user);
            $user->sendPasswordResetNotification($token);

            if (app()->environment('local')) {
                session()->flash('reset_link', route('password.reset', [
                    'token' => $token,
                    'email' => $user->email,
                ]));
            }
        }

        return back()->with('status', 'If an account exists for that email, a password reset link has been sent.');
    }

    public function showReset(Request $request, string $token)
    {
        if ($route = $this->dashboardRoute()) {
            return redirect()->to($route);
        }

        return view('frontend.auth.reset', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|string|email|max:255',
            'password' => 'required|min:6|confirmed',
        ]);

        $response = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
                Auth::guard('web')->login($user);
            }
        );

        if ($response === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', 'Your password has been reset. You can now sign in.');
        }

        $message = match ($response) {
            Password::INVALID_USER => 'We could not find an account with that email address.',
            Password::INVALID_TOKEN => 'This password reset link is invalid or has expired. Please request a new one.',
            default => 'Unable to reset your password. Please try again.',
        };

        return back()->withErrors(['email' => $message])->withInput(['email' => $request->email]);
    }
}
