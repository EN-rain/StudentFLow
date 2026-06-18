<?php

namespace App\Support;

use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Str;

class StudentUsername
{
    public static function fromStudent(Student $student): string
    {
        return self::unique(
            $student->first_name,
            $student->last_name,
            $student->student_number,
            $student->id
        );
    }

    public static function fromName(string $firstName, string $lastName, string $seed, ?int $studentId = null): string
    {
        return self::unique($firstName, $lastName, $seed, $studentId);
    }

    private static function unique(string $firstName, string $lastName, string $seed, ?int $studentId): string
    {
        $base = self::base($firstName, $lastName).self::suffix($seed);
        $username = $base;
        $counter = 2;

        while (User::where('username', $username)
            ->when($studentId, fn ($query) => $query->where('student_id', '!=', $studentId))
            ->exists()) {
            $username = $base.$counter;
            $counter++;
        }

        return $username;
    }

    private static function base(string $firstName, string $lastName): string
    {
        $base = Str::lower(Str::ascii($firstName.$lastName));
        $base = preg_replace('/[^a-z0-9]/', '', $base) ?: 'student';

        return $base;
    }

    private static function suffix(string $seed): string
    {
        $digits = preg_replace('/\D/', '', $seed);

        return substr($digits, -3) ?: (string) random_int(100, 999);
    }
}
