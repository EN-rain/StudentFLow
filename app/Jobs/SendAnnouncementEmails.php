<?php

namespace App\Jobs;

use App\Mail\ClassAnnouncementMail;
use App\Models\Announcement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendAnnouncementEmails implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private readonly int $announcementId)
    {
    }

    public function handle(): void
    {
        $announcement = Announcement::with('teacher.user', 'schoolClass')->find($this->announcementId);
        if (! $announcement || ! $announcement->class_id || ! $announcement->schoolClass) {
            return;
        }

        $announcement->schoolClass->students()
            ->wherePivot('status', 'enrolled')
            ->whereNotNull('students.email')
            ->chunkById(50, function ($students) use ($announcement) {
                foreach ($students as $student) {
                    if (filled($student->email)) {
                        Mail::to($student->email)->send(new ClassAnnouncementMail($announcement, $student));
                    }
                }
            });
    }
}
