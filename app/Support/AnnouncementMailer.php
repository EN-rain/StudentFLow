<?php

namespace App\Support;

use App\Mail\ClassAnnouncementMail;
use App\Models\Announcement;
use Illuminate\Support\Facades\Mail;

class AnnouncementMailer
{
    public static function sendToEnrolledStudents(Announcement $announcement): int
    {
        if (! $announcement->class_id) {
            return 0;
        }

        $announcement->loadMissing('schoolClass.students', 'teacher.user');

        $sent = 0;
        foreach ($announcement->schoolClass->students as $student) {
            if ($student->pivot?->status !== 'enrolled' || ! $student->email) {
                continue;
            }

            Mail::to($student->email)->send(new ClassAnnouncementMail($announcement, $student));
            $sent++;
        }

        return $sent;
    }
}
