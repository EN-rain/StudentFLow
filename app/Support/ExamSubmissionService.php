<?php

namespace App\Support;

use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\StudentGrade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamSubmissionService
{
    public static function submit(ExamAttempt $attempt, array $answers, Request $request): ExamAttempt
    {
        $attempt->load('exam.questions');

        if ($attempt->status === 'submitted') {
            abort(422, 'Exam already submitted.');
        }
        if ($attempt->exam->status !== 'published') {
            abort(422, 'Exam is not open.');
        }
        if (! $attempt->started_at) {
            abort(409, 'Exam must be started before it can be submitted.');
        }
        if ($attempt->exam->available_from && now()->lessThan($attempt->exam->available_from)) {
            abort(422, 'Exam is not available yet.');
        }
        if ($attempt->exam->duration_minutes && $attempt->started_at && now()->greaterThan($attempt->started_at->copy()->addMinutes($attempt->exam->duration_minutes))) {
            $attempt->update(['status' => 'expired']);
            abort(422, 'Exam duration has expired.');
        }
        if ($attempt->exam->due_at && now()->greaterThan($attempt->exam->due_at)) {
            $attempt->update(['status' => 'expired']);
            abort(422, 'Exam link has expired.');
        }

        $answers = collect($answers);
        if ($answers->pluck('question_id')->duplicates()->isNotEmpty()) {
            abort(422, 'Each exam question may only be answered once.');
        }

        $allowedQuestionIds = $attempt->exam->questions->pluck('id')->map(fn ($id) => (int) $id);
        if ($answers->pluck('question_id')->map(fn ($id) => (int) $id)->diff($allowedQuestionIds)->isNotEmpty()) {
            abort(422, 'One or more answers do not belong to this exam.');
        }

        return DB::transaction(function () use ($attempt, $answers, $request) {
            $total = 0.0;
            foreach ($answers as $answer) {
                $question = $attempt->exam->questions->firstWhere('id', (int) $answer['question_id']);
                if (! $question) {
                    continue;
                }
                $given = trim((string) ($answer['answer'] ?? ''));
                $correct = $question->correct_answer !== null && strcasecmp(trim($question->correct_answer), $given) === 0;
                $points = $correct ? (float) $question->points : 0.0;
                $total += $points;
                ExamAnswer::updateOrCreate([
                    'exam_attempt_id' => $attempt->id,
                    'exam_question_id' => $question->id,
                ], [
                    'answer_text' => $given,
                    'is_correct' => $question->correct_answer === null ? null : $correct,
                    'score' => $points,
                ]);
            }

            $score = min($total, (float) $attempt->exam->maximum_score);
            $attempt->update([
                'started_at' => $attempt->started_at ?? now(),
                'submitted_at' => now(),
                'score' => $score,
                'status' => 'submitted',
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 1000),
            ]);

            if ($attempt->exam->grade_item_id) {
                StudentGrade::updateOrCreate([
                    'grade_item_id' => $attempt->exam->grade_item_id,
                    'student_id' => $attempt->student_id,
                ], [
                    'score' => $score,
                    'remarks' => 'Synced from exam: '.$attempt->exam->title,
                ]);
            }

            return $attempt->fresh('answers');
        });
    }
}
