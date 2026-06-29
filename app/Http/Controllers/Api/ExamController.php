<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\GradeItem;
use App\Models\SchoolClass;
use App\Support\ActivityLogger;
use App\Support\ApiPagination;
use App\Support\ExamSubmissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Exam::query()
            ->select(['id', 'class_id', 'teacher_id', 'grade_item_id', 'title', 'status', 'available_from', 'due_at', 'duration_minutes', 'maximum_score', 'created_at'])
            ->with('schoolClass:id,class_name,subject')
            ->withCount(['questions', 'attempts']);
        if ($request->user()->isTeacher()) {
            $teacher = $request->user()->teacher;
            if (! $teacher) {
                return response()->json(['data' => []]);
            }
            $query->where('teacher_id', $teacher->id);
        }

        return response()->json(ApiPagination::paginate($query->orderByDesc('created_at'), $request));
    }

    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'class_id' => 'required|integer|exists:classes,id',
            'grade_item_id' => 'nullable|integer|exists:grade_items,id',
            'title' => 'required|string|max:191',
            'instructions' => 'nullable|string',
            'available_from' => 'nullable|date',
            'due_at' => 'nullable|date',
            'duration_minutes' => 'nullable|integer|min:1|max:600',
            'maximum_score' => 'required|numeric|min:1',
            'status' => 'nullable|in:draft,published,closed',
            'questions' => 'required|array|min:1',
            'questions.*.prompt' => 'required|string',
            'questions.*.type' => 'nullable|in:multiple_choice,text',
            'questions.*.choices' => 'nullable|array',
            'questions.*.correct_answer' => 'nullable|string',
            'questions.*.points' => 'required|numeric|min:0',
        ]);

        $class = SchoolClass::findOrFail($payload['class_id']);
        $this->authorizeClass($request, $class);

        if (! empty($payload['grade_item_id'])) {
            $item = GradeItem::findOrFail($payload['grade_item_id']);
            if ($item->class_id !== $class->id) {
                abort(422, 'Grade item must belong to the selected class.');
            }
        }

        $exam = DB::transaction(function () use ($payload, $class, $request) {
            $exam = Exam::create(collect($payload)->except('questions')->all() + [
                'teacher_id' => $class->teacher_id,
                'status' => $payload['status'] ?? 'draft',
            ]);

            $now = now();
            $questionRows = collect($payload['questions'])->map(fn (array $question, int $index) => [
                'exam_id' => $exam->id,
                'prompt' => $question['prompt'],
                'type' => $question['type'] ?? 'multiple_choice',
                'choices' => isset($question['choices']) ? json_encode($question['choices']) : null,
                'correct_answer' => $question['correct_answer'] ?? null,
                'points' => $question['points'],
                'sort_order' => $index + 1,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all();
            DB::table('exam_questions')->insert($questionRows);

            if ($exam->status === 'published') {
                $exam->assignEnrolledStudents();
            }

            ActivityLogger::log($request, 'exam.created', $exam);

            return $exam;
        });

        return response()->json(['data' => $exam->load('questions', 'attempts.student')], 201);
    }

    public function show(Request $request, Exam $exam): JsonResponse
    {
        $this->authorizeExam($request, $exam);

        return response()->json(['data' => $exam->load('schoolClass', 'questions', 'attempts.student.user')]);
    }

    public function publish(Request $request, Exam $exam): JsonResponse
    {
        $this->authorizeExam($request, $exam);
        $exam->update(['status' => 'published']);
        $exam->assignEnrolledStudents();
        ActivityLogger::log($request, 'exam.published', $exam);

        return response()->json(['data' => $exam->load('attempts.student')]);
    }

    public function audit(Request $request, Exam $exam): JsonResponse
    {
        $this->authorizeExam($request, $exam);
        $exam->load('attempts.student.user', 'questions', 'schoolClass');

        return response()->json(['data' => [
            'exam' => $exam,
            'stats' => [
                'assigned' => $exam->attempts->count(),
                'submitted' => $exam->attempts->where('status', 'submitted')->count(),
                'average_score' => round((float) $exam->attempts->whereNotNull('score')->avg('score'), 2),
            ],
            'students' => $exam->attempts->map(fn ($attempt) => [
                'student_id' => $attempt->student_id,
                'student_number' => $attempt->student->student_number,
                'name' => $attempt->student->full_name,
                'email' => $attempt->student->email,
                'github_username' => $attempt->student->user?->github_username,
                'google_email' => $attempt->student->user?->google_id ? $attempt->student->user?->email : null,
                'status' => $attempt->status,
                'started_at' => $attempt->started_at,
                'submitted_at' => $attempt->submitted_at,
                'score' => $attempt->score,
                'magic_url' => url('/exam/magic/'.$attempt->magic_token),
            ])->values(),
        ]]);
    }

    public function startAttempt(Request $request, ExamAttempt $attempt): JsonResponse
    {
        $student = $request->user()->student;
        if (! $student || $attempt->student_id !== $student->id) {
            abort(403);
        }
        $this->authorizeStudentAttempt($student->id, $attempt);

        $this->start($attempt);
        $attempt->refresh()->load('exam.questions', 'exam.schoolClass', 'student');

        return response()->json(['data' => $this->attemptPayload($attempt)]);
    }

    public function submitAttempt(Request $request, ExamAttempt $attempt): JsonResponse
    {
        $student = $request->user()->student;
        if (! $student || $attempt->student_id !== $student->id) {
            abort(403);
        }
        $this->authorizeStudentAttempt($student->id, $attempt);

        return $this->submit($request, $attempt);
    }

    public function submit(Request $request, ExamAttempt $attempt): JsonResponse
    {
        $attempt->load('exam.questions', 'student');
        if ($attempt->status === 'submitted') {
            abort(422, 'Exam already submitted.');
        }
        if ($attempt->exam->status !== 'published') {
            abort(422, 'Exam is not open.');
        }
        if ($attempt->exam->due_at && now()->greaterThan($attempt->exam->due_at)) {
            $attempt->update(['status' => 'expired']);
            abort(422, 'Exam link has expired.');
        }

        $payload = $request->validate([
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|integer|exists:exam_questions,id',
            'answers.*.answer' => 'nullable|string',
        ]);

        $attempt = ExamSubmissionService::submit($attempt, $payload['answers'], $request);

        return response()->json(['data' => $attempt, 'score' => (float) $attempt->score]);
    }

    public function magicStart(string $token): JsonResponse
    {
        $attempt = ExamAttempt::with('exam')->where('magic_token', $token)->firstOrFail();

        return $this->start($attempt);
    }

    public function magicShow(string $token): JsonResponse
    {
        $attempt = ExamAttempt::with('exam.questions', 'exam.schoolClass', 'student')
            ->where('magic_token', $token)
            ->firstOrFail();

        if (! $attempt->started_at && $attempt->status !== 'submitted') {
            abort(409, 'Exam must be started before questions can be viewed.');
        }
        if ($attempt->exam->available_from && now()->lessThan($attempt->exam->available_from)) {
            abort(422, 'Exam is not available yet.');
        }

        return response()->json(['data' => $this->attemptPayload($attempt)]);
    }

    public function magicSubmit(Request $request, string $token): JsonResponse
    {
        $attempt = ExamAttempt::where('magic_token', $token)->firstOrFail();

        return $this->submit($request, $attempt);
    }

    private function start(ExamAttempt $attempt): JsonResponse
    {
        $attempt->loadMissing('exam');
        if ($attempt->status === 'submitted') {
            abort(422, 'Exam already submitted.');
        }
        if ($attempt->exam->status !== 'published') {
            abort(422, 'Exam is not open.');
        }
        if ($attempt->exam->available_from && now()->lessThan($attempt->exam->available_from)) {
            abort(422, 'Exam is not available yet.');
        }
        if ($attempt->exam->due_at && now()->greaterThan($attempt->exam->due_at)) {
            $attempt->update(['status' => 'expired']);
            abort(422, 'Exam link has expired.');
        }

        if (! $attempt->started_at) {
            $attempt->update(['started_at' => now(), 'status' => 'in_progress']);
        }

        return response()->json(['data' => $attempt->fresh()]);
    }

    private function attemptPayload(ExamAttempt $attempt): array
    {
        $expiresAt = null;
        if ($attempt->started_at && $attempt->exam->duration_minutes) {
            $expiresAt = $attempt->started_at->copy()->addMinutes($attempt->exam->duration_minutes);
        }
        if ($attempt->exam->due_at && (! $expiresAt || $attempt->exam->due_at->lessThan($expiresAt))) {
            $expiresAt = $attempt->exam->due_at;
        }

        return [
            'id' => $attempt->id,
            'status' => $attempt->status,
            'started_at' => $attempt->started_at,
            'submitted_at' => $attempt->submitted_at,
            'score' => $attempt->score,
            'expires_at' => $expiresAt,
            'remaining_seconds' => $expiresAt ? max(0, now()->diffInSeconds($expiresAt, false)) : null,
            'student' => [
                'student_number' => $attempt->student->student_number,
                'name' => $attempt->student->full_name,
            ],
            'exam' => [
                'id' => $attempt->exam->id,
                'title' => $attempt->exam->title,
                'instructions' => $attempt->exam->instructions,
                'available_from' => $attempt->exam->available_from,
                'due_at' => $attempt->exam->due_at,
                'duration_minutes' => $attempt->exam->duration_minutes,
                'maximum_score' => $attempt->exam->maximum_score,
                'class' => [
                    'id' => $attempt->exam->schoolClass->id,
                    'class_name' => $attempt->exam->schoolClass->class_name,
                    'subject' => $attempt->exam->schoolClass->subject,
                ],
                'questions' => $attempt->exam->questions->map(fn ($question) => [
                    'id' => $question->id,
                    'prompt' => $question->prompt,
                    'type' => $question->type,
                    'choices' => $question->choices,
                    'points' => $question->points,
                    'sort_order' => $question->sort_order,
                ])->values(),
            ],
        ];
    }

    private function authorizeExam(Request $request, Exam $exam): void
    {
        $exam->loadMissing('schoolClass');
        $this->authorizeClass($request, $exam->schoolClass);
    }

    private function authorizeClass(Request $request, SchoolClass $class): void
    {
        $user = $request->user();
        if ($user->isAdmin()) {
            return;
        }
        $teacher = $user->teacher;
        if (! $teacher || $class->teacher_id !== $teacher->id) {
            abort(403);
        }
    }

    private function authorizeStudentAttempt(int $studentId, ExamAttempt $attempt): void
    {
        $attempt->loadMissing('exam');
        $enrolled = DB::table('class_students')
            ->where('student_id', $studentId)
            ->where('class_id', $attempt->exam->class_id)
            ->where('status', 'enrolled')
            ->exists();

        if (! $enrolled) {
            abort(403, 'You are not enrolled in this exam\'s class.');
        }
    }
}
