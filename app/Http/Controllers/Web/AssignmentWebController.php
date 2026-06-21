<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssignmentRequest;
use App\Models\Assignment;
use App\Models\SchoolClass;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssignmentWebController extends Controller
{
    public function index(Request $request)
    {
        $query = Assignment::with('class');
        if ($request->user()->isTeacher()) {
            $teacher = $request->user()->teacher;
            if ($teacher) {
                $classIds = SchoolClass::where('teacher_id', $teacher->id)->pluck('id');
                $query->whereIn('class_id', $classIds);
            }
        }
        $assignments = $query->orderBy('deadline')->get();

        $classes = SchoolClass::query();
        if ($request->user()->isTeacher()) {
            $teacher = $request->user()->teacher;
            if ($teacher) {
                $classes->where('teacher_id', $teacher->id);
            }
        }
        $classes = $classes->orderBy('class_name')->get();

        return view('assignments.index', compact('assignments', 'classes'));
    }

    public function create(Request $request)
    {
        $classes = $this->scopedClasses($request);

        return view('assignments.create', compact('classes'));
    }

    public function store(StoreAssignmentRequest $request)
    {
        $this->authorizeClassId($request, $request->class_id);
        $assignment = Assignment::create($request->validated());
        ActivityLogger::log($request, 'assignment.created', $assignment);

        return redirect('/assignments')->with('status', 'Assignment created.');
    }

    public function show(Request $request, Assignment $assignment)
    {
        $this->authorizeAccess($request, $assignment);
        $assignment->load('class.students', 'submissions.student');

        return view('assignments.show', compact('assignment'));
    }

    public function edit(Request $request, Assignment $assignment)
    {
        $this->authorizeAccess($request, $assignment);
        $classes = $this->scopedClasses($request);

        return view('assignments.edit', compact('assignment', 'classes'));
    }

    public function update(StoreAssignmentRequest $request, Assignment $assignment)
    {
        $this->authorizeAccess($request, $assignment);
        $this->authorizeClassId($request, $request->integer('class_id'));
        $assignment->update($request->validated());
        ActivityLogger::log($request, 'assignment.updated', $assignment);

        return redirect('/assignments')->with('status', 'Assignment updated.');
    }

    public function destroy(Request $request, Assignment $assignment)
    {
        $this->authorizeAccess($request, $assignment);
        $assignment->delete();
        ActivityLogger::log($request, 'assignment.deleted', $assignment);

        return redirect('/assignments')->with('status', 'Assignment deleted.');
    }

    public function saveSubmissions(Request $request, Assignment $assignment)
    {
        $this->authorizeAccess($request, $assignment);
        $payload = $request->validate([
            'submissions' => 'required|array',
            'submissions.*.status' => 'required|in:Pending,Submitted,Late,Missing,Excused',
            'submissions.*.score' => 'nullable|numeric|min:0|max:'.$assignment->maximum_score,
            'submissions.*.submitted_at' => 'nullable|date',
            'submissions.*.attachment_link' => 'nullable|url|max:255',
            'submissions.*.remarks' => 'nullable|string|max:1000',
        ]);

        $enrolledStudentIds = DB::table('class_students')
            ->where('class_id', $assignment->class_id)
            ->where('status', 'enrolled')
            ->pluck('student_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $rows = [];
        $now = now();
        foreach ($payload['submissions'] as $studentId => $submission) {
            if (! in_array((int) $studentId, $enrolledStudentIds, true)) {
                abort(422, 'Every submitted student must be actively enrolled in the assignment class.');
            }

            $rows[] = [
                'assignment_id' => $assignment->id,
                'student_id' => (int) $studentId,
                'status' => $submission['status'],
                'score' => $submission['score'] ?? null,
                'submitted_at' => $submission['submitted_at'] ?? null,
                'attachment_link' => $submission['attachment_link'] ?? null,
                'remarks' => $submission['remarks'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($rows) {
            DB::table('assignment_submissions')->upsert(
                $rows,
                ['assignment_id', 'student_id'],
                ['status', 'score', 'submitted_at', 'attachment_link', 'remarks', 'updated_at']
            );
        }

        ActivityLogger::log($request, 'assignment_submissions.saved', $assignment, ['count' => count($rows)]);

        return back()->with('status', 'Assignment submissions saved.');
    }

    private function scopedClasses(Request $request)
    {
        $q = SchoolClass::query();
        if ($request->user()->isTeacher()) {
            $teacher = $request->user()->teacher;
            if ($teacher) {
                $q->where('teacher_id', $teacher->id);
            }
        }

        return $q->orderBy('class_name')->get();
    }

    private function authorizeAccess(Request $request, Assignment $assignment): void
    {
        $user = $request->user();
        if ($user->isAdmin()) {
            return;
        }
        $teacher = $user->teacher;
        if (! $teacher) {
            abort(403);
        }
        $class = SchoolClass::find($assignment->class_id);
        if (! $class || $class->teacher_id !== $teacher->id) {
            abort(403);
        }
    }

    private function authorizeClassId(Request $request, int $classId): void
    {
        $user = $request->user();
        if ($user->isAdmin()) {
            return;
        }
        $teacher = $user->teacher;
        if (! $teacher) {
            abort(403);
        }
        $class = SchoolClass::find($classId);
        if (! $class || $class->teacher_id !== $teacher->id) {
            abort(403);
        }
    }
}
