<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\GradeItem;
use App\Models\SchoolClass;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamWebController extends Controller
{
    public function index(Request $request)
    {
        $query = Exam::with('schoolClass', 'attempts');
        if ($request->user()->isTeacher()) {
            $teacher = $request->user()->teacher;
            $query->where('teacher_id', $teacher?->id ?? 0);
        }

        return view('exams.index', [
            'exams' => $query->orderByDesc('created_at')->get(),
        ]);
    }

    public function create(Request $request)
    {
        $classes = $this->classesFor($request);

        return view('exams.create', compact('classes'));
    }

    public function store(Request $request)
    {
        $payload = $request->validate([
            'class_id' => 'required|integer|exists:classes,id',
            'grade_item_id' => 'nullable|integer|exists:grade_items,id',
            'title' => 'required|string|max:191',
            'instructions' => 'nullable|string|max:5000',
            'available_from' => 'nullable|date',
            'due_at' => 'nullable|date|after_or_equal:available_from',
            'duration_minutes' => 'nullable|integer|min:1|max:600',
            'maximum_score' => 'required|numeric|min:1',
            'status' => 'required|in:draft,published',
            'questions' => 'required|array|min:1|max:100',
            'questions.*.prompt' => 'required|string|max:2000',
            'questions.*.type' => 'required|in:multiple_choice,text',
            'questions.*.choices_text' => 'nullable|string|max:5000',
            'questions.*.correct_answer' => 'nullable|string|max:2000',
            'questions.*.points' => 'required|numeric|min:0.01',
        ]);

        $class = SchoolClass::findOrFail($payload['class_id']);
        $this->authorizeClass($request, $class);

        if (! empty($payload['grade_item_id'])) {
            $item = GradeItem::findOrFail($payload['grade_item_id']);
            abort_unless($item->class_id === $class->id, 422, 'Grade item must belong to the selected class.');
        }

        $exam = DB::transaction(function () use ($payload, $class, $request) {
            $questions = $payload['questions'];
            unset($payload['questions']);

            $exam = Exam::create($payload + ['teacher_id' => $class->teacher_id]);

            foreach ($questions as $index => $question) {
                $choices = null;
                if ($question['type'] === 'multiple_choice') {
                    $choices = collect(preg_split('/\r\n|\r|\n/', (string) ($question['choices_text'] ?? '')))
                        ->map(fn ($choice) => trim($choice))
                        ->filter()
                        ->values()
                        ->all();
                    abort_if(count($choices) < 2, 422, 'Multiple-choice questions require at least two choices.');
                }

                ExamQuestion::create([
                    'exam_id' => $exam->id,
                    'prompt' => $question['prompt'],
                    'type' => $question['type'],
                    'choices' => $choices,
                    'correct_answer' => $question['correct_answer'] ?? null,
                    'points' => $question['points'],
                    'sort_order' => $index + 1,
                ]);
            }

            if ($exam->status === 'published') {
                $exam->assignEnrolledStudents();
            }

            ActivityLogger::log($request, 'exam.created', $exam);

            return $exam;
        });

        return redirect('/exams/'.$exam->id)->with('status', 'Exam created.');
    }

    public function show(Request $request, Exam $exam)
    {
        $this->authorizeExam($request, $exam);
        $exam->load('schoolClass', 'questions', 'attempts.student');

        return view('exams.show', compact('exam'));
    }

    public function publish(Request $request, Exam $exam)
    {
        $this->authorizeExam($request, $exam);
        $exam->update(['status' => 'published']);
        $exam->assignEnrolledStudents();
        ActivityLogger::log($request, 'exam.published', $exam);

        return back()->with('status', 'Exam published and assigned to enrolled students.');
    }

    private function classesFor(Request $request)
    {
        $query = SchoolClass::with('gradeItems')->orderBy('class_name');
        if ($request->user()->isTeacher()) {
            $query->where('teacher_id', $request->user()->teacher?->id ?? 0);
        }

        return $query->get();
    }

    private function authorizeExam(Request $request, Exam $exam): void
    {
        $exam->loadMissing('schoolClass');
        $this->authorizeClass($request, $exam->schoolClass);
    }

    private function authorizeClass(Request $request, SchoolClass $class): void
    {
        if ($request->user()->isAdmin()) {
            return;
        }

        abort_unless($request->user()->teacher && $class->teacher_id === $request->user()->teacher->id, 403);
    }
}
