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

        if (! $attempt->started_at && $attempt->status !== 'submitted') {
            $attempt->update(['started_at' => now(), 'status' => 'in_progress']);
            $attempt->refresh();
        }

        return view('exams.magic', compact('attempt'));
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
