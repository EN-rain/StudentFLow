<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentRequest;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentWebController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with('user', 'classes');

        if ($request->user()->isTeacher()) {
            $teacher = $request->user()->teacher;
            if ($teacher) {
                $classIds = SchoolClass::where('teacher_id', $teacher->id)->pluck('id');
                $studentIds = DB::table('class_students')->whereIn('class_id', $classIds)->pluck('student_id');
                $query->whereIn('id', $studentIds);
            }
        }

        if ($q = $request->query('q')) {
            $like = "%{$q}%";
            $query->where(function ($w) use ($like) {
                $w->where('first_name', 'like', $like)
                    ->orWhere('middle_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhere('student_number', 'like', $like)
                    ->orWhere('email', 'like', $like);
            });
        }
        if ($classId = $request->query('class_id')) {
            $studentIds = DB::table('class_students')->where('class_id', $classId)->pluck('student_id');
            $query->whereIn('id', $studentIds);
        }

        $students = $query->orderBy('last_name')->orderBy('first_name')->limit(200)->get();
        $classes = SchoolClass::orderBy('class_name')->get();

        return view('students.index', compact('students', 'classes'));
    }

    public function create()
    {
        return view('students.create');
    }

    public function store(StoreStudentRequest $request)
    {
        Student::create($request->validated());

        return redirect('/students')->with('status', 'Student created.');
    }

    public function show(Request $request, Student $student)
    {
        $this->authorizeAccess($request, $student);
        $student->load('user', 'classes.teacher.user');

        $attendanceTotal = $student->attendance()->count();
        $attendancePresent = $student->attendance()->whereIn('status', ['Present', 'Late'])->count();
        $attendancePct = $attendanceTotal > 0 ? round($attendancePresent / $attendanceTotal * 100, 1) : null;

        return view('students.show', compact('student', 'attendancePct', 'attendanceTotal', 'attendancePresent'));
    }

    public function edit(Request $request, Student $student)
    {
        $this->authorizeAccess($request, $student);

        return view('students.edit', compact('student'));
    }

    public function update(StoreStudentRequest $request, Student $student)
    {
        $this->authorizeAccess($request, $student);
        $student->update($request->validated());

        return redirect('/students')->with('status', 'Student updated.');
    }

    public function destroy(Request $request, Student $student)
    {
        $this->authorizeAccess($request, $student);
        $student->delete();

        return redirect('/students')->with('status', 'Student deleted.');
    }

    private function authorizeAccess(Request $request, Student $student): void
    {
        $user = $request->user();
        if ($user->isAdmin()) {
            return;
        }
        $teacher = $user->teacher;
        if (! $teacher) {
            abort(403);
        }
        $classIds = SchoolClass::where('teacher_id', $teacher->id)->pluck('id');
        $enrolled = DB::table('class_students')->whereIn('class_id', $classIds)->where('student_id', $student->id)->exists();
        if (! $enrolled) {
            abort(403, 'You can only access students in your classes.');
        }
    }
}
