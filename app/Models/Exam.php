<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Exam extends Model
{
    protected $fillable = [
        'class_id',
        'teacher_id',
        'grade_item_id',
        'title',
        'instructions',
        'available_from',
        'due_at',
        'duration_minutes',
        'maximum_score',
        'status',
    ];

    protected $casts = [
        'available_from' => 'datetime',
        'due_at' => 'datetime',
        'maximum_score' => 'decimal:2',
    ];

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function gradeItem()
    {
        return $this->belongsTo(GradeItem::class);
    }

    public function questions()
    {
        return $this->hasMany(ExamQuestion::class)->orderBy('sort_order')->orderBy('id');
    }

    public function attempts()
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function assignEnrolledStudents(): void
    {
        $this->loadMissing('schoolClass.students');
        $now = now();
        $rows = $this->schoolClass->students
            ->filter(fn ($student) => $student->pivot?->status === 'enrolled')
            ->map(fn ($student) => [
                'exam_id' => $this->id,
                'student_id' => $student->id,
                'magic_token' => Str::random(64),
                'status' => 'assigned',
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->values()
            ->all();

        if ($rows) {
            DB::table('exam_attempts')->insertOrIgnore($rows);
        }
    }
}
