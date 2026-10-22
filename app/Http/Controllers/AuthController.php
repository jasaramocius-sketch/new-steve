<?php

namespace App\Http\Controllers;

use App\Helpers\NotificationHelper;
use App\Mail\WelcomeMail;
use App\Models\Compare;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

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

    private function mergeGuestData()
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

        return view('auth.login');
    }

    public function registerForm()
    {
        if ($route = $this->dashboardRoute()) {
            return redirect()->to($route);
        }

        return view('auth.register');
    }

    /**
     * Authenticate user against the database and store real data in session.
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Invalid credentials']);
        }

        $request->session()->regenerate();

        $user = Auth::user();
        $request->session()->put([
            'user_logged_in' => true,
            'user_profile' => $user->only(['id', 'name', 'email', 'role', 'phone', 'address', 'city', 'country']),
        ]);

        $this->mergeGuestData();

        if ($user->role === 'master_admin' || $user->role === 'admin' || $user->role === 'staff') {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('user.dashboard');
    }

    /**
     * Register a new customer account and log them in.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'customer',
        ]);

        Auth::login($user);

        $request->session()->regenerate();
        $request->session()->put([
            'user_logged_in' => true,
            'user_profile' => $user->only(['id', 'name', 'email', 'role', 'phone', 'address', 'city', 'country']),
        ]);

        $this->mergeGuestData();

        NotificationHelper::welcomeUser($user);

        Mail::to($user->email)->send(new WelcomeMail($user));

        return redirect()->route('user.dashboard')->with('success', 'Account created successfully!');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function showLogin()
    {
        if ($route = $this->dashboardRoute()) {
            return redirect()->to($route);
        }

        return view('auth.login');
    }

    public function showForgot()
    {
        if ($route = $this->dashboardRoute()) {
            return redirect()->to($route);
        }

        return view('auth.forgot');
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

        return view('auth.reset', [
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
