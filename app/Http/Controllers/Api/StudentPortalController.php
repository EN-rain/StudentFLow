<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\ExamAttempt;
use App\Models\StudentGrade;
use App\Support\ApiPagination;
use App\Support\StudentUsername;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
        $student = $this->student($request)->load('user', 'classes.teacher.user');

        return response()->json(['data' => $this->profilePayload($student)]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $student = $this->student($request);
        $user = $request->user();

        $payload = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:191', Rule::unique('students', 'email')->ignore($student->id), Rule::unique('users', 'email')->ignore($user->id)],
            'username' => ['required', 'string', 'max:64', 'regex:/^[A-Za-z0-9._-]+$/', Rule::unique('users', 'username')->ignore($user->id)],
            'profile_image' => ['nullable', 'url', 'max:2048'],
        ]);

        $student->update([
            'first_name' => $payload['first_name'],
            'last_name' => $payload['last_name'],
            'email' => strtolower($payload['email']),
            'profile_image' => $payload['profile_image'] ?? null,
        ]);

        $user->forceFill([
            'username' => $payload['username'] ?: StudentUsername::fromStudent($student),
            'name' => $student->fresh()->full_name,
            'email' => strtolower($payload['email']),
            'avatar_url' => $payload['profile_image'] ?? $user->avatar_url,
        ])->save();

        return response()->json(['data' => $this->profilePayload($student->fresh()->load('user', 'classes.teacher.user'))]);
    }

    public function classes(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->student($request)->classes()->with('teacher.user')->get()]);
    }

    public function announcements(Request $request): JsonResponse
    {
        $classIds = $this->student($request)->classes()->pluck('classes.id');

        return response()->json(ApiPagination::paginate(
            Announcement::with('teacher.user', 'schoolClass')->whereIn('class_id', $classIds)->orderByDesc('publish_date'),
            $request
        ));
    }

    public function assignments(Request $request): JsonResponse
    {
        $student = $this->student($request);
        $classIds = $student->classes()->pluck('classes.id');

        $assignments = Assignment::with([
            'schoolClass',
            'submissions' => fn ($query) => $query->where('student_id', $student->id),
        ])->whereIn('class_id', $classIds)->orderByDesc('deadline');

        return response()->json(ApiPagination::paginate($assignments, $request));
    }

    public function grades(Request $request): JsonResponse
    {
        $student = $this->student($request);
        $grades = StudentGrade::with('gradeItem.category.schoolClass')
            ->where('student_id', $student->id)
            ->paginate(ApiPagination::perPage($request));

        $grades->through(fn ($grade) => [
                'class_name' => $grade->gradeItem?->category?->schoolClass?->class_name,
                'category' => $grade->gradeItem?->category?->category_name,
                'title' => $grade->gradeItem?->title,
                'score' => $grade->score,
                'maximum_score' => $grade->gradeItem?->maximum_score,
            ]);

        return response()->json(ApiPagination::response($grades));
    }

    public function attendance(Request $request): JsonResponse
    {
        return response()->json(ApiPagination::paginate(
            Attendance::with('schoolClass')->where('student_id', $this->student($request)->id)->orderByDesc('attendance_date'),
            $request
        ));
    }

    public function exams(Request $request): JsonResponse
    {
        $attempts = ExamAttempt::with('exam.schoolClass')
            ->where('student_id', $this->student($request)->id)
            ->orderByDesc('created_at')
            ->paginate(ApiPagination::perPage($request));

        return response()->json(ApiPagination::response($attempts));
    }

    private function student(Request $request)
    {
        $student = $request->user()->student;
        if (! $student) {
            abort(403, 'No student profile linked to this account.');
        }

        return $student;
    }

    private function profilePayload($student): array
    {
        return [
            'id' => $student->id,
            'student_number' => $student->student_number,
            'first_name' => $student->first_name,
            'last_name' => $student->last_name,
            'full_name' => $student->full_name,
            'email' => $student->email,
            'profile_image' => $student->profile_image,
            'username' => $student->user?->username,
            'google_linked' => filled($student->user?->google_id),
            'github_linked' => filled($student->user?->github_id),
            'github_username' => $student->user?->github_username,
            'classes' => $student->classes,
        ];
    }
}
