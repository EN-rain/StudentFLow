<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ExamAttempt;
use App\Support\ExamSubmissionService;
use Illuminate\Http\Request;

class MagicExamWebController extends Controller
{
    public function show(string $token)
    {
        $attempt = ExamAttempt::with('exam.questions', 'exam.schoolClass', 'student')
            ->where('magic_token', $token)
            ->firstOrFail();

        return view('exams.magic', compact('attempt'));
    }

    public function start(string $token)
    {
        $attempt = ExamAttempt::with('exam')->where('magic_token', $token)->firstOrFail();

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

        return redirect("/exam/magic/{$token}");
    }

    public function submit(Request $request, string $token)
    {
        $attempt = ExamAttempt::with('exam.questions')
            ->where('magic_token', $token)
            ->firstOrFail();

        $payload = $request->validate([
            'answers' => 'required|array',
            'answers.*' => 'nullable|string',
        ]);

        $answers = collect($payload['answers'])
            ->map(fn ($answer, $questionId) => ['question_id' => (int) $questionId, 'answer' => $answer])
            ->values()
            ->all();

        ExamSubmissionService::submit($attempt, $answers, $request);

        return redirect("/exam/magic/{$token}")->with('status', 'Exam submitted. Your score was synced to StudentFlow.');
    }
}
