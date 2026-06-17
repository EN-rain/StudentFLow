<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'teacher_id',
        'class_name',
        'section',
        'subject',
        'grade_level',
        'school_year',
        'semester',
        'schedule',
        'room',
        'status',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'class_students', 'class_id', 'student_id')
            ->withPivot('date_enrolled', 'status')
            ->withTimestamps();
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class, 'class_id');
    }

    public function gradeCategories()
    {
        return $this->hasMany(GradeCategory::class, 'class_id');
    }

    public function gradeItems()
    {
        return $this->hasMany(GradeItem::class, 'class_id');
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'class_id');
    }

    public function announcements()
    {
        return $this->hasMany(Announcement::class, 'class_id');
    }
}
