<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolSettingHistory extends Model
{
    protected $fillable = [
        'school_setting_id',
        'changed_by',
        'old_value',
        'new_value',
    ];

    public function setting()
    {
        return $this->belongsTo(SchoolSetting::class, 'school_setting_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
