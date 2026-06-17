<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolSetting extends Model
{
    protected $fillable = [
        'setting_key',
        'setting_value',
        'label',
    ];

    public function histories()
    {
        return $this->hasMany(SchoolSettingHistory::class);
    }
}
