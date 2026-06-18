<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClassRequest;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Support\ActivityLogger;
use App\Support\DummyClassGenerator;
use Illuminate\Http\Request;

class ClassWebController extends Controller
{
    public function index(Request $request)
    {
        $query = SchoolClass::with('teacher.user', 'students');
        if ($request->user()->isTeacher()) {
            $teacher = $request->user()->teacher;
            if ($teacher) {
                $query->where('teacher_id', $teacher->id);
            }
        }
        $classes = $query->orderBy('class_name')->get();

        return view('classes.index', compact('classes'));
    }

    public function create(Request $request)
    {
        $teachers = Teacher::with('user')->orderBy('last_name')->get();

        return view('classes.create', compact('teachers'));
    }

    public function store(StoreClassRequest $request)
    {
        $user = $request->user();
        if ($user->isTeacher()) {
            $teacher = $user->teacher;
            if (! $teacher || (int) $request->teacher_id !== (int) $teacher->id) {
                abort(403, 'You may only create classes for yourself.');
            }
        }
        $class = SchoolClass::create($request->validated());
        ActivityLogger::log($request, 'class.created', $class);

        return redirect('/classes')->with('status', 'Class created.');
    }

    public function show(Request $request, SchoolClass $class)
    {
        $this->authorizeAccess($request, $class);
        $class->load('teacher.user', 'students', 'assignments', 'announcements');
        $availableStudents = Student::whereNotIn('id', $class->students->pluck('id'))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('classes.show', compact('class', 'availableStudents'));
    }

    public function edit(Request $request, SchoolClass $class)
    {
        $this->authorizeAccess($request, $class);
        $teachers = Teacher::with('user')->orderBy('last_name')->get();

        return view('classes.edit', compact('class', 'teachers'));
    }

    public function update(StoreClassRequest $request, SchoolClass $class)
    {
        $this->authorizeAccess($request, $class);
        $class->update($request->validated());
        ActivityLogger::log($request, 'class.updated', $class);

        return redirect('/classes')->with('status', 'Class updated.');
    }

    public function dummy(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $class = DummyClassGenerator::create();
        ActivityLogger::log($request, 'class.dummy_created', $class);

        return redirect('/classes/'.$class->id.'/edit')->with('status', 'Dummy class created. Edit any values before using it.');
    }

    public function destroy(Request $request, SchoolClass $class)
    {
        $this->authorizeAccess($request, $class);
        $class->delete();
        ActivityLogger::log($request, 'class.deleted', $class);

        return redirect('/classes')->with('status', 'Class deleted.');
    }

    public function storeEnrollment(Request $request, SchoolClass $class)
    {
        $this->authorizeAccess($request, $class);
        $payload = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'date_enrolled' => 'nullable|date',
            'status' => 'nullable|in:enrolled,dropped,completed',
        ]);

        if ($class->students()->where('students.id', $payload['student_id'])->exists()) {
            return back()->withErrors([
                'student_id' => 'Student is already enrolled in this class.',
            ])->withInput();
        }

        $class->students()->syncWithoutDetaching([
            $payload['student_id'] => [
                'date_enrolled' => $payload['date_enrolled'] ?? now()->toDateString(),
                'status' => $payload['status'] ?? 'enrolled',
            ],
        ]);
        ActivityLogger::log($request, 'enrollment.saved', $class, ['student_id' => $payload['student_id']]);

        return back()->with('status', 'Enrollment saved.');
    }

    public function updateEnrollment(Request $request, SchoolClass $class, Student $student)
    {
        $this->authorizeAccess($request, $class);
        $payload = $request->validate([
            'date_enrolled' => 'required|date',
            'status' => 'required|in:enrolled,dropped,completed',
        ]);
        $class->students()->updateExistingPivot($student->id, $payload);
        ActivityLogger::log($request, 'enrollment.updated', $class, ['student_id' => $student->id, 'status' => $payload['status']]);

        return back()->with('status', 'Enrollment updated.');
    }

    public function destroyEnrollment(Request $request, SchoolClass $class, Student $student)
    {
        $this->authorizeAccess($request, $class);
        $class->students()->detach($student->id);
        ActivityLogger::log($request, 'enrollment.removed', $class, ['student_id' => $student->id]);

        return back()->with('status', 'Student removed from class.');
    }

    private function authorizeAccess(Request $request, SchoolClass $class): void
    {
        $user = $request->user();
        if ($user->isTeacher()) {
            $teacher = $user->teacher;
            if (! $teacher || $class->teacher_id !== $teacher->id) {
                abort(403, 'You can only access your own classes.');
            }
        }
    }
}
