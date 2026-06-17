<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamQuestion extends Model
{
    protected $fillable = [
        'exam_id',
        'prompt',
        'type',
        'choices',
        'correct_answer',
        'points',
        'sort_order',
    ];

    protected $casts = [
        'choices' => 'array',
        'points' => 'decimal:2',
    ];

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
}
