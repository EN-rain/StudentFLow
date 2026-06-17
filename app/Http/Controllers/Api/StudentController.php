<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentRequest;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Student::query();

        // Teacher sees only students enrolled in their classes
        if ($request->user()->isTeacher()) {
            $teacher = $request->user()->teacher;
            if (! $teacher) {
                return response()->json(['data' => []]);
            }
            $classIds = SchoolClass::where('teacher_id', $teacher->id)->pluck('id');
            $studentIds = \Illuminate\Support\Facades\DB::table('class_students')
                ->whereIn('class_id', $classIds)
                ->pluck('student_id');
            $query->whereIn('id', $studentIds);
        }

        // Search
        if ($q = $request->query('q')) {
            $query->where(function ($w) use ($q) {
                $like = "%{$q}%";
                $w->where('first_name', 'like', $like)
                    ->orWhere('middle_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhere('student_number', 'like', $like)
                    ->orWhere('email', 'like', $like);
            });
        }

        // Filter by class
        if ($classId = $request->query('class_id')) {
            $studentIds = \Illuminate\Support\Facades\DB::table('class_students')
                ->where('class_id', $classId)
                ->pluck('student_id');
            $query->whereIn('id', $studentIds);
        }

        return response()->json([
            'data' => $query->orderBy('last_name')->orderBy('first_name')->limit(200)->get(),
        ]);
    }

    public function show(Request $request, Student $student): JsonResponse
    {
        $this->authorizeAccess($request, $student);
        $student->load('classes.teacher.user');
        return response()->json(['data' => $student]);
    }

    public function store(StoreStudentRequest $request): JsonResponse
    {
        $student = Student::create($request->validated());
        return response()->json(['data' => $student], 201);
    }

    public function update(StoreStudentRequest $request, Student $student): JsonResponse
    {
        $this->authorizeAccess($request, $student);
        $student->update($request->validated());
        return response()->json(['data' => $student]);
    }

    public function destroy(Request $request, Student $student): JsonResponse
    {
        $this->authorizeAccess($request, $student);
        $student->delete();
        return response()->json(['message' => 'Student deleted.']);
    }

    private function authorizeAccess(Request $request, Student $student): void
    {
        $user = $request->user();
        if ($user->isAdmin()) return;

        $teacher = $user->teacher;
        if (! $teacher) abort(403);

        $classIds = SchoolClass::where('teacher_id', $teacher->id)->pluck('id');
        $enrolled = \Illuminate\Support\Facades\DB::table('class_students')
            ->whereIn('class_id', $classIds)
            ->where('student_id', $student->id)
            ->exists();
        if (! $enrolled) abort(403, 'You can only access students in your classes.');
    }
}
