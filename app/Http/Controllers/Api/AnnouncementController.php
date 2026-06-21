<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAnnouncementRequest;
use App\Models\Announcement;
use App\Models\SchoolClass;
use App\Models\Teacher;
use App\Support\AnnouncementMailer;
use App\Support\ApiPagination;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Announcement::with(['teacher.user', 'schoolClass']);

        if ($request->user()->isTeacher()) {
            $teacher = $request->user()->teacher;
            if (! $teacher) {
                return response()->json(['data' => []]);
            }
            $query->where('teacher_id', $teacher->id);
        }

        return response()->json(ApiPagination::paginate($query->orderBy('publish_date', 'desc'), $request));
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
            if (! $teacher) {
                return response()->json(['message' => 'No teacher profile linked to this account.'], 403);
            }
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
        $queued = AnnouncementMailer::queueForEnrolledStudents($announcement);

        return response()->json(['data' => $announcement, 'emails_queued' => $queued], 201);
    }

    public function update(StoreAnnouncementRequest $request, Announcement $announcement): JsonResponse
    {
        $this->authorizeAccess($request, $announcement);

        if ($request->user()->isTeacher() && $request->filled('class_id')) {
            $class = SchoolClass::find($request->integer('class_id'));
            if (! $class || $class->teacher_id !== $request->user()->teacher?->id) {
                abort(403, 'You may only post to your own classes.');
            }
        }

        $data = $request->validated();
        if ($request->user()->isTeacher()) {
            $data['teacher_id'] = $request->user()->teacher->id;
        }
        $announcement->update($data);

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
        if ($user->isAdmin()) {
            return;
        }
        $teacher = $user->teacher;
        if (! $teacher || $announcement->teacher_id !== $teacher->id) {
            abort(403);
        }
    }
}
