<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StudentExamController extends Controller
{
    public function index(Request $request)
    {
        $student = $this->student($request);

        $classIds = DB::table('class_students')
            ->where('student_id', $student->id)
            ->where('status', 'enrolled')
            ->pluck('class_id');

        $now = now();

        $exams = Exam::with(['schoolClass', 'attempts' => fn ($q) => $q->where('student_id', $student->id)])
            ->whereIn('class_id', $classIds)
            ->where('status', 'published')
            ->where(function ($q) use ($now) {
                $q->whereNull('available_from')->orWhere('available_from', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('due_at')->orWhere('due_at', '>=', $now);
            })
            ->orderBy('available_from')
            ->orderBy('due_at')
            ->get();

        $rows = $exams->map(function (Exam $exam) use ($now) {
            $attempt = $exam->attempts->first();
            $isOpen = true;
            $state = 'not_started';

            if ($attempt) {
                $state = match ($attempt->status) {
                    'submitted' => 'submitted',
                    'in_progress' => 'in_progress',
                    'expired' => 'expired',
                    default => 'assigned',
                };
                if ($attempt->status === 'submitted') {
                    $isOpen = false;
                }
            } else {
                $state = 'no_attempt';
            }

            if ($exam->due_at && $exam->due_at->lt($now)) {
                $isOpen = false;
                if ($state === 'assigned' || $state === 'not_started' || $state === 'no_attempt') {
                    $state = 'expired';
                }
            }

            return [
                'exam' => $exam,
                'attempt' => $attempt,
                'state' => $state,
                'is_open' => $isOpen,
            ];
        });

        $stats = [
            'total' => $rows->count(),
            'available' => $rows->filter(fn ($r) => $r['is_open'])->count(),
            'submitted' => $rows->filter(fn ($r) => $r['state'] === 'submitted')->count(),
        ];

        return view('student.exams.index', [
            'student' => $student,
            'rows' => $rows,
            'stats' => $stats,
        ]);
    }

    public function start(Request $request, Exam $exam)
    {
        $student = $this->student($request);
        $this->authorizeEnrollment($student, $exam);

        if ($exam->status !== 'published') {
            abort(422, 'Exam is not open.');
        }
        $now = now();
        if ($exam->available_from && $now->lessThan($exam->available_from)) {
            abort(422, 'Exam is not available yet.');
        }
        if ($exam->due_at && $now->greaterThan($exam->due_at)) {
            abort(422, 'Exam link has expired.');
        }

        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->first();

        if (! $attempt) {
            $attempt = ExamAttempt::create([
                'exam_id' => $exam->id,
                'student_id' => $student->id,
                'magic_token' => Str::random(64),
                'status' => 'assigned',
            ]);
        }

        if ($attempt->status === 'submitted') {
            abort(422, 'Exam already submitted.');
        }
        if ($attempt->status === 'expired') {
            abort(422, 'Exam attempt has expired.');
        }

        if (! $attempt->started_at) {
            $attempt->update([
                'started_at' => $now,
                'status' => 'in_progress',
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
            ]);
        }

        return redirect("/exam/magic/{$attempt->magic_token}");
    }

    private function authorizeEnrollment($student, Exam $exam): void
    {
        $enrolled = DB::table('class_students')
            ->where('student_id', $student->id)
            ->where('class_id', $exam->class_id)
            ->where('status', 'enrolled')
            ->exists();
        if (! $enrolled) {
            abort(403, 'You are not enrolled in this exam\'s class.');
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
