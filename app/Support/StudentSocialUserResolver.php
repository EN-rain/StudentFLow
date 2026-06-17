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
            throw ValidationException::withMessages([
                'email' => ['No StudentFlow student record matches this email address. Ask a teacher or admin to register it first.'],
            ]);
        }

        $user = User::where('student_id', $student->id)->orWhere('email', $email)->first();
        if (! $user) {
            $user = User::create([
                'username' => $student->student_number,
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
}
