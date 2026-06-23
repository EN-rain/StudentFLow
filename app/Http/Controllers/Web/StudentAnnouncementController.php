<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentAnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $student = $this->student($request);

        $classIds = DB::table('class_students')
            ->where('student_id', $student->id)
            ->where('status', 'enrolled')
            ->pluck('class_id');

        $today = now()->startOfDay();

        $announcements = Announcement::with(['schoolClass', 'teacher.user'])
            ->where(function ($q) use ($today) {
                $q->whereNull('publish_date')->orWhere('publish_date', '<=', $today);
            })
            ->where(function ($q) use ($today) {
                $q->whereNull('expiration_date')->orWhere('expiration_date', '>=', $today);
            })
            ->where(function ($q) use ($classIds) {
                $q->whereNull('class_id')->orWhereIn('class_id', $classIds);
            })
            ->orderByRaw("CASE priority WHEN 'Urgent' THEN 1 WHEN 'Important' THEN 2 ELSE 3 END")
            ->orderByDesc('publish_date')
            ->orderByDesc('id')
            ->get();

        $stats = [
            'total' => $announcements->count(),
            'urgent' => $announcements->where('priority', 'Urgent')->count(),
            'important' => $announcements->where('priority', 'Important')->count(),
        ];

        return view('student.announcements.index', [
            'student' => $student,
            'announcements' => $announcements,
            'stats' => $stats,
        ]);
    }

    public function show(Request $request, Announcement $announcement)
    {
        $student = $this->student($request);
        $this->authorizeVisibility($student, $announcement);

        $announcement->load(['schoolClass', 'teacher.user']);

        return view('student.announcements.show', [
            'student' => $student,
            'announcement' => $announcement,
        ]);
    }

    private function authorizeVisibility($student, Announcement $announcement): void
    {
        $today = now()->startOfDay();
        if ($announcement->publish_date && $announcement->publish_date->gt($today)) {
            abort(403, 'This announcement is not yet published.');
        }
        if ($announcement->expiration_date && $announcement->expiration_date->lt($today)) {
            abort(403, 'This announcement has expired.');
        }
        if ($announcement->class_id === null) {
            return; // global announcement — visible to all students
        }
        $enrolled = DB::table('class_students')
            ->where('student_id', $student->id)
            ->where('class_id', $announcement->class_id)
            ->where('status', 'enrolled')
            ->exists();
        if (! $enrolled) {
            abort(403, 'You are not enrolled in this announcement\'s class.');
        }
    }

    private function student(Request $request)
    {
        $student = $request->user()->student;
        if (! $student) {
            abort(403, 'No student profile linked to this account.');
        }

        return $student;
    }
}
