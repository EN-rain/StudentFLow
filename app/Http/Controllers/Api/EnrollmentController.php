<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function index(Request $request, SchoolClass $class): JsonResponse
    {
        $this->authorizeClass($request, $class);
        return response()->json(['data' => $class->students()->orderBy('last_name')->get()]);
    }

    public function store(Request $request, SchoolClass $class): JsonResponse
    {
        $this->authorizeClass($request, $class);
        $payload = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'date_enrolled' => 'nullable|date',
            'status' => 'nullable|in:enrolled,dropped,completed',
        ]);

        if ($class->students()->where('students.id', $payload['student_id'])->exists()) {
            return response()->json([
                'message' => 'Student is already enrolled in this class.',
                'errors' => [
                    'student_id' => ['Student is already enrolled in this class.'],
                ],
            ], 422);
        }

        $class->students()->syncWithoutDetaching([
            $payload['student_id'] => [
                'date_enrolled' => $payload['date_enrolled'] ?? now()->toDateString(),
                'status' => $payload['status'] ?? 'enrolled',
            ],
        ]);

        ActivityLogger::log($request, 'enrollment.saved', $class, ['student_id' => $payload['student_id']]);
        return response()->json(['data' => Student::find($payload['student_id'])->load('classes')], 201);
    }

    public function update(Request $request, SchoolClass $class, Student $student): JsonResponse
    {
        $this->authorizeClass($request, $class);
        $payload = $request->validate([
            'date_enrolled' => 'required|date',
            'status' => 'required|in:enrolled,dropped,completed',
        ]);
        $class->students()->updateExistingPivot($student->id, $payload);
        ActivityLogger::log($request, 'enrollment.updated', $class, ['student_id' => $student->id, 'status' => $payload['status']]);
        return response()->json(['data' => $student->load('classes')]);
    }

    public function destroy(Request $request, SchoolClass $class, Student $student): JsonResponse
    {
        $this->authorizeClass($request, $class);
        $class->students()->detach($student->id);
        ActivityLogger::log($request, 'enrollment.removed', $class, ['student_id' => $student->id]);
        return response()->json(['message' => 'Student removed from class.']);
    }

    private function authorizeClass(Request $request, SchoolClass $class): void
    {
        $user = $request->user();
        if ($user->isAdmin()) return;
        $teacher = $user->teacher;
        if (! $teacher || $class->teacher_id !== $teacher->id) abort(403);
    }
}
