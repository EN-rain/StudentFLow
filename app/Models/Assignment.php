<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'title',
        'description',
        'date_assigned',
        'deadline',
        'maximum_score',
        'status',
        'attachment_link',
    ];

    protected $casts = [
        'date_assigned' => 'date',
        'deadline' => 'date',
        'maximum_score' => 'decimal:2',
    ];

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /** Alias relationship so `with('class')` and `$a->class` both work. */
    public function class()
    {
        return $this->schoolClass();
    }

    /** Convenience accessor for views. */
    public function getClassAttribute()
    {
        return $this->schoolClass;
    }

    public function submissions()
    {
        return $this->hasMany(AssignmentSubmission::class);
    }
}
