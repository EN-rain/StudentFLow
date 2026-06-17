<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradeCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'category_name',
        'percentage_weight',
    ];

    protected $casts = [
        'percentage_weight' => 'decimal:2',
    ];

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function items()
    {
        return $this->hasMany(GradeItem::class, 'category_id');
    }
}
