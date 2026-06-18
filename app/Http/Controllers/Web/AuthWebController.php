<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthWebController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $payload = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $payload['username'])
            ->orWhere('email', $payload['username'])
            ->first();

        if (! $user || ! Hash::check($payload['password'], $user->password)) {
            throw ValidationException::withMessages([
                'username' => 'Invalid credentials.',
            ]);
        }

        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'username' => 'This account has been disabled.',
            ]);
        }

        if ($user->isStudent()) {
            throw ValidationException::withMessages([
                'username' => 'Students must sign in through the StudentFlow mobile app.',
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function forgotPassword(Request $request)
    {
        $payload = $request->validate([
            'email' => 'required|email',
        ]);

        Password::sendResetLink($payload);

        return back()->with('status', 'If an account exists for that email, a reset link has been issued.');
    }

    public function showResetPassword(Request $request, string $token)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function showTeacherSetup(Request $request, string $token)
    {
        return view('auth.teacher-setup', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function completeTeacherSetup(Request $request)
    {
        $payload = $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'username' => ['required', 'string', 'max:64', Rule::unique('users', 'username')],
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset([
            'token' => $payload['token'],
            'email' => $payload['email'],
            'password' => $payload['password'],
            'password_confirmation' => $request->string('password_confirmation')->toString(),
        ], function (User $user, string $password) use ($payload) {
            if (! $user->isTeacher()) {
                throw ValidationException::withMessages([
                    'email' => 'Only teacher invite links can be used on this form.',
                ]);
            }

            $user->forceFill([
                'username' => $payload['username'],
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
                'status' => 'active',
            ])->save();
            $user->tokens()->delete();
        });

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => __($status)]);
        }

        return redirect('/login')->with('status', 'Teacher account setup complete. You can now sign in.');
    }

    public function resetPassword(Request $request)
    {
        $payload = $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset($payload, function ($user, string $password) {
            $user->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();
            $user->tokens()->delete();
        });

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => __($status)]);
        }

        return redirect('/login')->with('status', 'Password reset. You can now sign in.');
    }

    public function showChangePassword()
    {
        return view('auth.change-password');
    }

    public function changePassword(Request $request)
    {
        $payload = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();
        if (! Hash::check($payload['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Current password is incorrect.',
            ]);
        }

        $user->password = Hash::make($payload['new_password']);
        $user->setRememberToken(Str::random(60));
        $user->save();
        $user->tokens()->delete();
        $request->session()->regenerate();

        return back()->with('status', 'Password changed.');
    }
}
