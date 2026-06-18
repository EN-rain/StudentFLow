<?php

namespace App\Support;

use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StudentSocialUserResolver
{
    public static function resolve(string $provider, array $profile): User
    {
        $email = strtolower((string) ($profile['email'] ?? ''));
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::withMessages([
                'email' => ['The social account did not provide a verified email address.'],
            ]);
        }

        $student = Student::whereRaw('lower(email) = ?', [$email])->first();
        if (! $student) {
            [$firstName, $lastName] = self::splitName((string) ($profile['name'] ?? strtok($email, '@')));
            $student = Student::create([
                'student_number' => self::nextStudentNumber(),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'profile_image' => $profile['avatar_url'] ?? $profile['picture'] ?? null,
                'status' => 'active',
            ]);
        }

        $user = User::where('student_id', $student->id)->orWhere('email', $email)->first();
        if (! $user) {
            $user = User::create([
                'username' => StudentUsername::fromStudent($student),
                'name' => $student->full_name,
                'email' => $email,
                'password' => Hash::make(Str::random(48)),
                'role' => 'student',
                'status' => 'active',
                'student_id' => $student->id,
            ]);
        }

        if ($user->role !== 'student') {
            throw ValidationException::withMessages([
                'email' => ['This email is already used by a non-student account.'],
            ]);
        }

        $updates = [
            'student_id' => $student->id,
            'name' => $student->full_name,
            'email' => $email,
            'status' => 'active',
            'social_verified_at' => now(),
        ];

        if ($provider === 'google') {
            $updates['google_id'] = (string) ($profile['id'] ?? $profile['sub'] ?? '');
            $updates['avatar_url'] = $profile['avatar_url'] ?? $user->avatar_url;
        }

        if ($provider === 'github') {
            $updates['github_id'] = (string) ($profile['id'] ?? '');
            $updates['github_username'] = $profile['username'] ?? null;
            $updates['avatar_url'] = $profile['avatar_url'] ?? $user->avatar_url;
        }

        $user->forceFill($updates)->save();

        return $user->fresh('student');
    }

    private static function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name), 2);

        return [$parts[0] ?: 'Student', $parts[1] ?? 'User'];
    }

    private static function nextStudentNumber(): string
    {
        $year = now()->format('Y');
        $latest = Student::where('student_number', 'like', $year.'-%')
            ->orderByDesc('student_number')
            ->value('student_number');
        $next = $latest ? ((int) substr($latest, -4)) + 1 : 1;

        return sprintf('%s-%04d', $year, $next);
    }
}
