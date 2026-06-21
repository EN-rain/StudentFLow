<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\SchoolClass;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssignmentSubmissionController extends Controller
{
    public function index(Request $request, Assignment $assignment): JsonResponse
    {
        $this->authorizeAssignment($request, $assignment);
        $assignment->load('schoolClass.students', 'submissions.student');

        return response()->json(['data' => $assignment]);
    }

    public function store(Request $request, Assignment $assignment): JsonResponse
    {
        $this->authorizeAssignment($request, $assignment);
        $payload = $request->validate([
            'submissions' => 'required|array|min:1',
            'submissions.*.student_id' => 'required|integer|distinct|exists:students,id',
            'submissions.*.status' => 'required|in:Pending,Submitted,Late,Missing,Excused',
            'submissions.*.score' => 'nullable|numeric|min:0|max:'.$assignment->maximum_score,
            'submissions.*.submitted_at' => 'nullable|date',
            'submissions.*.attachment_link' => 'nullable|url|max:255',
            'submissions.*.remarks' => 'nullable|string|max:1000',
        ]);

        $enrolledStudentIds = DB::table('class_students')
            ->where('class_id', $assignment->class_id)
            ->where('status', 'enrolled')
            ->pluck('student_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        foreach ($payload['submissions'] as $submission) {
            if (! in_array((int) $submission['student_id'], $enrolledStudentIds, true)) {
                abort(422, 'Every submitted student must be actively enrolled in the assignment class.');
            }
        }

        $rows = [];
        $now = now();
        foreach ($payload['submissions'] as $s) {
            $rows[] = [
                'assignment_id' => $assignment->id,
                'student_id' => $s['student_id'],
                'status' => $s['status'],
                'score' => $s['score'] ?? null,
                'submitted_at' => $s['submitted_at'] ?? null,
                'attachment_link' => $s['attachment_link'] ?? null,
                'remarks' => $s['remarks'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('assignment_submissions')->upsert(
            $rows,
            ['assignment_id', 'student_id'],
            ['status', 'score', 'submitted_at', 'attachment_link', 'remarks', 'updated_at']
        );

        ActivityLogger::log($request, 'assignment_submissions.saved', $assignment, ['count' => count($rows)]);

        return response()->json(['data' => AssignmentSubmission::where('assignment_id', $assignment->id)->with('student')->get()]);
    }

    private function authorizeAssignment(Request $request, Assignment $assignment): void
    {
        $user = $request->user();
        if ($user->isAdmin()) {
            return;
        }
        $teacher = $user->teacher;
        $class = SchoolClass::find($assignment->class_id);
        if (! $teacher || ! $class || $class->teacher_id !== $teacher->id) {
            abort(403);
        }
    }
}
