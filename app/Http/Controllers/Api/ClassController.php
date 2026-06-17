<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClassRequest;
use App\Models\SchoolClass;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    /**
     * List classes. Admin sees all; teacher sees only their own.
     */
    public function index(Request $request): JsonResponse
    {
        $query = SchoolClass::with('teacher.user');

        if ($request->user()->isTeacher()) {
            $teacher = $request->user()->teacher;
            if (! $teacher) {
                return response()->json(['data' => []]);
            }
            $query->where('teacher_id', $teacher->id);
        }

        return response()->json(['data' => $query->orderBy('class_name')->get()]);
    }

    public function show(Request $request, SchoolClass $class): JsonResponse
    {
        $this->authorizeAccess($request, $class);

        $class->load('teacher.user', 'students');

        return response()->json(['data' => $class]);
    }

    public function store(StoreClassRequest $request): JsonResponse
    {
        $user = $request->user();

        // Teacher can only create classes for themselves
        if ($user->isTeacher()) {
            $teacher = $user->teacher;
            if (! $teacher || (int) $request->teacher_id !== (int) $teacher->id) {
                return response()->json(['message' => 'You may only create classes for yourself.'], 403);
            }
        }

        $class = SchoolClass::create($request->validated());

        return response()->json(['data' => $class], 201);
    }

    public function update(StoreClassRequest $request, SchoolClass $class): JsonResponse
    {
        $this->authorizeAccess($request, $class);

        $class->update($request->validated());

        return response()->json(['data' => $class]);
    }

    public function destroy(Request $request, SchoolClass $class): JsonResponse
    {
        $this->authorizeAccess($request, $class);
        $class->delete();
        return response()->json(['message' => 'Class deleted.']);
    }

    private function authorizeAccess(Request $request, SchoolClass $class): void
    {
        $user = $request->user();
        if ($user->isTeacher()) {
            $teacher = $user->teacher;
            if (! $teacher || $class->teacher_id !== $teacher->id) {
                abort(403, 'You can only access your own classes.');
            }
        }
    }
}
