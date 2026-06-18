<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClassJoinRequest;
use App\Models\SchoolClass;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClassJoinRequestController extends Controller
{
    public function studentIndex(Request $request): JsonResponse
    {
        $student = $request->user()->student;
        abort_unless($student, 403);

        return response()->json(['data' => [
            'verified' => $request->user()->isClassroomVerified(),
            'google_linked' => filled($request->user()->google_id),
            'github_linked' => filled($request->user()->github_id),
            'requests' => ClassJoinRequest::with('schoolClass.teacher.user')
                ->where('student_id', $student->id)
                ->latest()
                ->get(),
        ]]);
    }

    public function store(Request $request): JsonResponse
    {
        abort_unless($request->user()->isClassroomVerified(), 422, 'Link both Google and GitHub before joining a classroom.');
        $payload = $request->validate(['join_code' => 'required|string|max:16']);
        $student = $request->user()->student;
        abort_unless($student, 403);

        $class = SchoolClass::whereRaw('upper(join_code) = ?', [strtoupper($payload['join_code'])])
            ->where('status', 'active')
            ->firstOrFail();

        if ($class->students()->where('students.id', $student->id)->exists()) {
            return response()->json(['message' => 'You are already enrolled in this class.'], 422);
        }

        $joinRequest = ClassJoinRequest::updateOrCreate(
            ['class_id' => $class->id, 'student_id' => $student->id],
            ['status' => 'pending', 'reviewed_by' => null, 'reviewed_at' => null]
        );

        return response()->json(['data' => $joinRequest->load('schoolClass')], 201);
    }

    public function classIndex(Request $request, SchoolClass $class): JsonResponse
    {
        $this->authorizeClass($request, $class);

        return response()->json(['data' => $class->joinRequests()
            ->with('student.user')
            ->latest()
            ->get()]);
    }

    public function review(Request $request, ClassJoinRequest $joinRequest): JsonResponse
    {
        $joinRequest->loadMissing('schoolClass');
        $this->authorizeClass($request, $joinRequest->schoolClass);
        $payload = $request->validate(['decision' => 'required|in:approved,rejected']);

        $joinRequest->update([
            'status' => $payload['decision'],
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        if ($payload['decision'] === 'approved') {
            $joinRequest->schoolClass->students()->syncWithoutDetaching([
                $joinRequest->student_id => [
                    'date_enrolled' => now()->toDateString(),
                    'status' => 'enrolled',
                ],
            ]);
        }

        ActivityLogger::log($request, 'class_join_request.'.$payload['decision'], $joinRequest);

        return response()->json(['data' => $joinRequest->fresh(['student', 'schoolClass'])]);
    }

    private function authorizeClass(Request $request, SchoolClass $class): void
    {
        if ($request->user()->isAdmin()) {
            return;
        }

        $teacher = $request->user()->teacher;
        abort_unless($teacher && $class->teacher_id === $teacher->id, 403);
    }
}
