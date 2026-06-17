<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAnnouncementRequest;
use App\Models\Announcement;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Support\AnnouncementMailer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Announcement::with(['teacher.user', 'schoolClass']);

        if ($request->user()->isTeacher()) {
            $teacher = $request->user()->teacher;
            if (! $teacher) return response()->json(['data' => []]);
            $query->where('teacher_id', $teacher->id);
        }

        return response()->json(['data' => $query->orderBy('publish_date', 'desc')->get()]);
    }

    public function show(Request $request, Announcement $announcement): JsonResponse
    {
        $this->authorizeAccess($request, $announcement);
        return response()->json(['data' => $announcement->load(['teacher.user', 'schoolClass'])]);
    }

    public function store(StoreAnnouncementRequest $request): JsonResponse
    {
        $user = $request->user();
        $teacher = null;

        if ($user->isTeacher()) {
            $teacher = $user->teacher;
            if (! $teacher) return response()->json(['message' => 'No teacher profile linked to this account.'], 403);
            if ($request->class_id) {
                $class = SchoolClass::find($request->class_id);
                if (! $class || $class->teacher_id !== $teacher->id) {
                    return response()->json(['message' => 'You may only post to your own classes.'], 403);
                }
            }
        } else {
            $teacher = Teacher::find($request->teacher_id) ?? null;
        }

        $announcement = Announcement::create(array_merge($request->validated(), [
            'teacher_id' => $teacher?->id ?? $request->teacher_id,
        ]));
        $sent = AnnouncementMailer::sendToEnrolledStudents($announcement);

        return response()->json(['data' => $announcement, 'emails_sent' => $sent], 201);
    }

    public function update(StoreAnnouncementRequest $request, Announcement $announcement): JsonResponse
    {
        $this->authorizeAccess($request, $announcement);
        $announcement->update($request->validated());
        return response()->json(['data' => $announcement]);
    }

    public function destroy(Request $request, Announcement $announcement): JsonResponse
    {
        $this->authorizeAccess($request, $announcement);
        $announcement->delete();
        return response()->json(['message' => 'Announcement deleted.']);
    }

    private function authorizeAccess(Request $request, Announcement $announcement): void
    {
        $user = $request->user();
        if ($user->isAdmin()) return;
        $teacher = $user->teacher;
        if (! $teacher || $announcement->teacher_id !== $teacher->id) abort(403);
    }
}
