<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAnnouncementRequest;
use App\Models\Announcement;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Support\AnnouncementMailer;
use Illuminate\Http\Request;

class AnnouncementWebController extends Controller
{
    public function index(Request $request)
    {
        $query = Announcement::with(['teacher.user', 'schoolClass']);
        if ($request->user()->isTeacher()) {
            $teacher = $request->user()->teacher;
            if ($teacher) {
                $query->where('teacher_id', $teacher->id);
            }
        }
        $announcements = $query->orderBy('publish_date', 'desc')->get();

        return view('announcements.index', compact('announcements'));
    }

    public function create(Request $request)
    {
        $classes = $this->scopedClasses($request);

        return view('announcements.create', compact('classes'));
    }

    public function store(StoreAnnouncementRequest $request)
    {
        $user = $request->user();
        $teacher = null;
        if ($user->isTeacher()) {
            $teacher = $user->teacher;
            if (! $teacher) {
                abort(403);
            }
            if ($request->class_id) {
                $class = SchoolClass::find($request->class_id);
                if (! $class || $class->teacher_id !== $teacher->id) {
                    abort(403);
                }
            }
        } else {
            $teacher = Teacher::find($request->teacher_id) ?? null;
        }

        $announcement = Announcement::create(array_merge($request->validated(), [
            'teacher_id' => $teacher?->id ?? $request->teacher_id,
        ]));
        $queued = AnnouncementMailer::queueForEnrolledStudents($announcement);

        return redirect('/announcements')->with('status', "Announcement posted. Email notifications queued for {$queued} enrolled student(s).");
    }

    public function show(Request $request, Announcement $announcement)
    {
        $this->authorizeAccess($request, $announcement);
        $announcement->load(['teacher.user', 'schoolClass']);

        return view('announcements.show', compact('announcement'));
    }

    public function edit(Request $request, Announcement $announcement)
    {
        $this->authorizeAccess($request, $announcement);
        $classes = $this->scopedClasses($request);

        return view('announcements.edit', compact('announcement', 'classes'));
    }

    public function update(StoreAnnouncementRequest $request, Announcement $announcement)
    {
        $this->authorizeAccess($request, $announcement);
        $announcement->update($request->validated());

        return redirect('/announcements')->with('status', 'Announcement updated.');
    }

    public function destroy(Request $request, Announcement $announcement)
    {
        $this->authorizeAccess($request, $announcement);
        $announcement->delete();

        return redirect('/announcements')->with('status', 'Announcement deleted.');
    }

    private function scopedClasses(Request $request)
    {
        $q = SchoolClass::query();
        if ($request->user()->isTeacher()) {
            $teacher = $request->user()->teacher;
            if ($teacher) {
                $q->where('teacher_id', $teacher->id);
            }
        }

        return $q->orderBy('class_name')->get();
    }

    private function authorizeAccess(Request $request, Announcement $announcement): void
    {
        $user = $request->user();
        if ($user->isAdmin()) {
            return;
        }
        $teacher = $user->teacher;
        if (! $teacher || $announcement->teacher_id !== $teacher->id) {
            abort(403);
        }
    }
}
