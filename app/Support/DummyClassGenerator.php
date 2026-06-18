<?php

namespace App\Support;

use App\Models\SchoolClass;
use App\Models\Teacher;
use Illuminate\Validation\ValidationException;

class DummyClassGenerator
{
    public static function create(): SchoolClass
    {
        $teacher = Teacher::query()->inRandomOrder()->first();

        if (! $teacher) {
            throw ValidationException::withMessages([
                'teacher' => ['Create at least one teacher before adding a dummy class.'],
            ]);
        }

        $number = SchoolClass::query()->count() + 1;
        $subjects = [
            'Application Development',
            'Database Systems',
            'Web Technologies',
            'Computer Networks',
            'Information Security',
        ];
        $days = ['Monday and Wednesday', 'Tuesday and Thursday', 'Friday'];

        return SchoolClass::create([
            'teacher_id' => $teacher->id,
            'class_name' => 'Sample Class '.$number,
            'section' => chr(65 + (($number - 1) % 26)),
            'subject' => $subjects[array_rand($subjects)],
            'grade_level' => 'College',
            'school_year' => now()->year.'-'.(now()->year + 1),
            'semester' => 'First Semester',
            'schedule' => $days[array_rand($days)].', 9:00 AM-10:30 AM',
            'room' => 'Room '.random_int(101, 399),
            'status' => 'active',
        ]);
    }
}
