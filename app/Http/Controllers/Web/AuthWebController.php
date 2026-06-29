<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Support\StudentUsername;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $payload = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email|unique:students,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        [$firstName, $lastName] = $this->splitName($payload['name']);

        $student = null;
        $user = null;
        DB::transaction(function () use (&$student, &$user, $payload, $firstName, $lastName) {
            $student = Student::create([
                'student_number' => 'pending-'.Str::uuid(),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => strtolower($payload['email']),
                'status' => 'active',
            ]);
            $student->forceFill([
                'student_number' => sprintf('%s-%04d', now()->format('Y'), $student->id),
            ])->save();

            $user = User::create([
                'username' => StudentUsername::fromStudent($student),
                'name' => $student->full_name,
                'email' => $student->email,
                'password' => Hash::make($payload['password']),
                'role' => 'student',
                'status' => 'active',
                'student_id' => $student->id,
            ]);
        }, 3);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/student')->with('status', 'Account created.');
    }

    public function login(Request $request)
    {
        $startedAt = microtime(true);
        $marks = [];
        $outcome = 'failed';
        $user = null;

        $payload = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
        $marks['validated_ms'] = $this->elapsedMs($startedAt);

        $lookupStartedAt = microtime(true);
        $user = User::where('username', $payload['username'])
            ->orWhere('email', $payload['username'])
            ->first();
        $marks['lookup_ms'] = $this->elapsedMs($lookupStartedAt);

        $hashStartedAt = microtime(true);
        if (! $user || ! Hash::check($payload['password'], $user->password)) {
            $marks['hash_ms'] = $this->elapsedMs($hashStartedAt);
            $this->logAuthTiming($request, $startedAt, $marks, $outcome, $user);
            throw ValidationException::withMessages([
                'username' => 'Invalid credentials.',
            ]);
        }
        $marks['hash_ms'] = $this->elapsedMs($hashStartedAt);

        if ($user->status !== 'active') {
            $this->logAuthTiming($request, $startedAt, $marks, $outcome, $user);
            throw ValidationException::withMessages([
                'username' => 'This account has been disabled.',
            ]);
        }

        $sessionStartedAt = microtime(true);
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        $marks['session_ms'] = $this->elapsedMs($sessionStartedAt);
        $outcome = 'success';
        $this->logAuthTiming($request, $startedAt, $marks, $outcome, $user);

        return redirect()->intended($user->isStudent() ? '/student' : '/dashboard');
    }

    private function elapsedMs(float $startedAt): int
    {
        return (int) round((microtime(true) - $startedAt) * 1000);
    }

    private function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name), 2);

        return [$parts[0] ?: 'Student', $parts[1] ?? 'User'];
    }

    private function logAuthTiming(Request $request, float $startedAt, array $marks, string $outcome, ?User $user): void
    {
        if (! filter_var(env('PERF_LOG_AUTH', false), FILTER_VALIDATE_BOOLEAN)) {
            return;
        }

        $totalMs = $this->elapsedMs($startedAt);
        $level = $totalMs >= 1000 ? 'warning' : 'info';

        Log::log($level, 'web login timing', [
            'outcome' => $outcome,
            'total_ms' => $totalMs,
            'steps' => $marks,
            'role' => $user?->role,
            'user_id' => $user?->id,
            'ip' => $request->ip(),
        ]);
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
        if ($redirect = $this->frontendRedirectUrl('/reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ])) {
            return redirect()->away($redirect);
        }

        return view('auth.reset-password', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function showTeacherSetup(Request $request, string $token)
    {
        if ($redirect = $this->frontendRedirectUrl('/teacher/setup/'.$token, [
            'email' => $request->query('email'),
        ])) {
            return redirect()->away($redirect);
        }

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
            if (! $user->isTeacher() || ! $user->hasPendingTeacherSetup()) {
                throw ValidationException::withMessages([
                    'email' => 'This teacher setup link is not valid for an established account.',
                ]);
            }

            $user->forceFill([
                'username' => $payload['username'],
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
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

    private function frontendRedirectUrl(string $path, array $query = []): ?string
    {
        $frontend = trim((string) env('FRONTEND_URL', ''));
        if ($frontend === '') {
            return null;
        }

        $url = rtrim($frontend, '/').'/'.ltrim($path, '/');
        $query = array_filter($query, fn ($value) => filled($value));

        return $query ? $url.'?'.http_build_query($query) : $url;
    }
}
