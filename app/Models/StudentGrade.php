<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentGrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'grade_item_id',
        'student_id',
        'score',
        'remarks',
    ];

    protected $casts = [
        'score' => 'decimal:2',
    ];

    public function gradeItem()
    {
        return $this->belongsTo(GradeItem::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
