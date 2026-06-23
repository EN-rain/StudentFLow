<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $student = $this->student($request);

        // Load assignments for the student's enrolled classes, with the student's own submission.
        $assignmentIds = DB::table('class_students')
            ->where('student_id', $student->id)
            ->where('status', 'enrolled')
            ->pluck('class_id');

        $assignments = Assignment::with(['class', 'submissions' => function ($q) use ($student) {
            $q->where('student_id', $student->id);
        }])
            ->whereIn('class_id', $assignmentIds)
            ->orderByDesc('deadline')
            ->get();

        $rows = $assignments->map(function (Assignment $a) {
            $submission = $a->submissions->first();
            $now = now();
            $deadline = $a->deadline;
            $isPastDeadline = $deadline ? $deadline->endOfDay()->lt($now) : false;

            return [
                'assignment' => $a,
                'submission' => $submission,
                'is_past_deadline' => $isPastDeadline,
            ];
        });

        $stats = [
            'total' => $rows->count(),
            'pending' => $rows->filter(fn ($r) => ! $r['submission'] || in_array($r['submission']->status, ['Pending', 'Missing'], true))->count(),
            'submitted' => $rows->filter(fn ($r) => $r['submission'] && in_array($r['submission']->status, ['Submitted', 'Late'], true))->count(),
        ];

        return view('student.assignments.index', [
            'student' => $student,
            'rows' => $rows,
            'stats' => $stats,
        ]);
    }

    public function show(Request $request, Assignment $assignment)
    {
        $student = $this->student($request);
        $this->authorizeEnrollment($student, $assignment);

        $assignment->load([
            'class',
            'submissions' => fn ($q) => $q->where('student_id', $student->id),
        ]);
        $submission = $assignment->submissions->first();
        $isPastDeadline = $assignment->deadline ? $assignment->deadline->endOfDay()->lt(now()) : false;

        return view('student.assignments.show', [
            'student' => $student,
            'assignment' => $assignment,
            'submission' => $submission,
            'isPastDeadline' => $isPastDeadline,
        ]);
    }

    public function submit(Request $request, Assignment $assignment)
    {
        $student = $this->student($request);
        $this->authorizeEnrollment($student, $assignment);

        $payload = $request->validate([
            'attachment_link' => 'nullable|url|max:255',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $now = now();
        $isLate = $assignment->deadline ? $assignment->deadline->endOfDay()->lt($now) : false;
        $status = $isLate ? 'Late' : 'Submitted';

        DB::table('assignment_submissions')->upsert(
            [[
                'assignment_id' => $assignment->id,
                'student_id' => $student->id,
                'status' => $status,
                'score' => null,
                'submitted_at' => $now,
                'attachment_link' => $payload['attachment_link'] ?? null,
                'remarks' => $payload['remarks'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ]],
            ['assignment_id', 'student_id'],
            ['status', 'submitted_at', 'attachment_link', 'remarks', 'updated_at']
        );

        ActivityLogger::log($request, 'assignment_submission.submitted', $assignment, [
            'student_id' => $student->id,
            'status' => $status,
        ]);

        return redirect()->route('student.assignments.show', $assignment->id)
            ->with('status', $isLate ? 'Submitted (late).' : 'Assignment submitted successfully.');
    }

    private function authorizeEnrollment($student, Assignment $assignment): void
    {
        $enrolled = DB::table('class_students')
            ->where('student_id', $student->id)
            ->where('class_id', $assignment->class_id)
            ->where('status', 'enrolled')
            ->exists();
        if (! $enrolled) {
            abort(403, 'You are not enrolled in this assignment\'s class.');
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
