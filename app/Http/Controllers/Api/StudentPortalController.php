<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\ExamAttempt;
use App\Models\StudentGrade;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentPortalController extends Controller
{
    public function dashboard(Request $request): JsonResponse
    {
        $student = $this->student($request);
        $classIds = $student->classes()->pluck('classes.id');

        return response()->json(['data' => [
            'student' => $student->load('user'),
            'classes_count' => $classIds->count(),
            'announcements_count' => Announcement::whereIn('class_id', $classIds)->count(),
            'assignments_count' => Assignment::whereIn('class_id', $classIds)->count(),
            'pending_exams_count' => ExamAttempt::where('student_id', $student->id)->whereIn('status', ['assigned', 'in_progress'])->count(),
        ]]);
    }

    public function profile(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->student($request)->load('user', 'classes.teacher.user')]);
    }

    public function classes(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->student($request)->classes()->with('teacher.user')->get()]);
    }

    public function announcements(Request $request): JsonResponse
    {
        $classIds = $this->student($request)->classes()->pluck('classes.id');

        return response()->json(['data' => Announcement::with('teacher.user', 'schoolClass')->whereIn('class_id', $classIds)->orderByDesc('publish_date')->get()]);
    }

    public function assignments(Request $request): JsonResponse
    {
        $student = $this->student($request);
        $classIds = $student->classes()->pluck('classes.id');

        $assignments = Assignment::with([
            'schoolClass',
            'submissions' => fn ($query) => $query->where('student_id', $student->id),
        ])->whereIn('class_id', $classIds)->orderByDesc('deadline')->get();

        return response()->json(['data' => $assignments]);
    }

    public function grades(Request $request): JsonResponse
    {
        $student = $this->student($request);
        $grades = StudentGrade::with('gradeItem.category.schoolClass')
            ->where('student_id', $student->id)
            ->get()
            ->map(fn ($grade) => [
                'class_name' => $grade->gradeItem?->category?->schoolClass?->class_name,
                'category' => $grade->gradeItem?->category?->category_name,
                'title' => $grade->gradeItem?->title,
                'score' => $grade->score,
                'maximum_score' => $grade->gradeItem?->maximum_score,
            ]);

        return response()->json(['data' => $grades]);
    }

    public function attendance(Request $request): JsonResponse
    {
        return response()->json(['data' => Attendance::with('schoolClass')->where('student_id', $this->student($request)->id)->orderByDesc('attendance_date')->get()]);
    }

    public function exams(Request $request): JsonResponse
    {
        $attempts = ExamAttempt::with('exam.schoolClass')
            ->where('student_id', $this->student($request)->id)
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['data' => $attempts]);
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
