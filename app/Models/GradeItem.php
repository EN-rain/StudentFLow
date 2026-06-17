<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'category_id',
        'title',
        'maximum_score',
        'date_given',
    ];

    protected $casts = [
        'maximum_score' => 'decimal:2',
        'date_given' => 'date',
    ];

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function category()
    {
        return $this->belongsTo(GradeCategory::class, 'category_id');
    }

    public function studentGrades()
    {
        return $this->hasMany(StudentGrade::class);
    }
}
