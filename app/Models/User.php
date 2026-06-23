<?php

namespace App\Models;

use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, MustVerifyEmailTrait, Notifiable;

    public const TEACHER_INVITE_PREFIX = 'invite-teacher-';

    protected $fillable = [
        'username',
        'name',
        'email',
        'password',
        'role',
        'status',
        'student_id',
        'google_id',
        'github_id',
        'github_username',
        'avatar_url',
        'social_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'social_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isTeacher(): bool
    {
        return $this->role === 'teacher';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    public function isClassroomVerified(): bool
    {
        return $this->isStudent()
            && $this->hasVerifiedEmail()
            && filled($this->google_id)
            && filled($this->github_id)
            && $this->social_verified_at !== null;
    }

    public function hasPendingTeacherSetup(): bool
    {
        return $this->role === 'teacher'
            && str_starts_with($this->username, self::TEACHER_INVITE_PREFIX);
    }
}
