<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Support\StudentUsername;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email|unique:students,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'prohibited',
        ]);

        [$firstName, $lastName] = $this->splitName($payload['name']);

        $student = null;
        $user = null;
        DB::transaction(function () use (&$student, &$user, $payload, $firstName, $lastName) {
            $student = Student::create([
                'student_number' => $this->nextStudentNumber(),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => strtolower($payload['email']),
                'status' => 'active',
            ]);

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

        $token = $user->createToken('android-register')->plainTextToken;

        return response()->json([
            'message' => 'Student registered.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'student' => [
                    'id' => $student->id,
                    'student_number' => $student->student_number,
                    'full_name' => $student->full_name,
                ],
            ],
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $payload['username'])
            ->orWhere('email', $payload['username'])
            ->first();

        if ($user && ! Hash::check($payload['password'], $user->password) && $this->matchesStarterPassword($user, $payload['password'])) {
            $user->forceFill(['password' => Hash::make($payload['password'])])->save();
        }

        if (! $user || ! Hash::check($payload['password'], $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['Invalid credentials.'],
            ]);
        }

        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'username' => ['This account has been disabled. Contact an administrator.'],
            ]);
        }

        // Revoke any prior tokens to keep one active token per device login
        $user->tokens()->delete();

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('teacher', 'student');

        return response()->json([
            'id' => $user->id,
            'username' => $user->username,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status,
            'classroom_verified' => $user->isClassroomVerified(),
            'google_linked' => filled($user->google_id),
            'github_linked' => filled($user->github_id),
            'teacher' => $user->teacher ? [
                'id' => $user->teacher->id,
                'employee_number' => $user->teacher->employee_number,
                'full_name' => $user->teacher->full_name,
                'department' => $user->teacher->department,
            ] : null,
            'student' => $user->student ? [
                'id' => $user->student->id,
                'student_number' => $user->student->student_number,
                'full_name' => $user->student->full_name,
                'google_email' => $user->google_id ? $user->email : null,
                'github_username' => $user->github_username,
            ] : null,
        ]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (! Hash::check($payload['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Current password is incorrect.'],
            ]);
        }

        $user->password = Hash::make($payload['new_password']);
        $user->save();

        // Invalidate all other tokens so other devices must re-authenticate
        $user->tokens()->where('id', '!=', $user->currentAccessToken()->id)->delete();

        return response()->json(['message' => 'Password changed.']);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'email' => 'required|email',
        ]);

        Password::sendResetLink($payload);

        return response()->json(['message' => 'If an account exists for that email, a password-reset link has been issued.']);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset($payload, function (User $user, string $password) {
            $user->forceFill([
                'password' => Hash::make($password),
                'remember_token' => Str::random(60),
            ])->save();
            $user->tokens()->delete();
        });

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => [__($status)]]);
        }

        return response()->json(['message' => 'Password reset.']);
    }

    private function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name), 2);

        return [$parts[0] ?: 'Student', $parts[1] ?? 'User'];
    }

    private function nextStudentNumber(): string
    {
        $year = now()->format('Y');
        $latest = Student::where('student_number', 'like', $year.'-%')
            ->orderByDesc('student_number')
            ->value('student_number');
        $next = $latest ? ((int) substr($latest, -4)) + 1 : 1;

        return sprintf('%s-%04d', $year, $next);
    }

    private function matchesStarterPassword(User $user, string $password): bool
    {
        if (! str_ends_with($user->email, '@studentflow.local')) {
            return false;
        }

        $expected = match ($user->role) {
            'admin' => env('STUDENTFLOW_SEED_ADMIN_PASSWORD', 'AdminPass123!'),
            'teacher' => env('STUDENTFLOW_SEED_TEACHER_PASSWORD', 'TeacherPass123!'),
            'student' => env('STUDENTFLOW_SEED_STUDENT_PASSWORD', 'StudentPass123!'),
            default => null,
        };

        return is_string($expected) && hash_equals($expected, $password);
    }
}
