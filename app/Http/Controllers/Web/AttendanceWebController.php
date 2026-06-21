<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceWebController extends Controller
{
    /**
     * List classes so teacher can pick one to mark attendance for.
     */
    public function index(Request $request)
    {
        $query = SchoolClass::query();
        if ($request->user()->isTeacher()) {
            $teacher = $request->user()->teacher;
            if ($teacher) {
                $query->where('teacher_id', $teacher->id);
            }
        }
        $classes = $query->orderBy('class_name')->get();

        return view('attendance.index', compact('classes'));
    }

    /**
     * Show the per-class per-date attendance form.
     */
    public function show(Request $request, SchoolClass $class)
    {
        $this->authorizeClassAccess($request, $class);
        $date = $request->query('date', date('Y-m-d'));

        $students = $class->students()
            ->wherePivot('status', 'enrolled')
            ->orderBy('last_name')
            ->get();
        $existing = Attendance::where('class_id', $class->id)
            ->where('attendance_date', $date)
            ->get()
            ->keyBy('student_id');

        return view('attendance.show', compact('class', 'date', 'students', 'existing'));
    }

    /**
     * Save attendance for a class + date (POST).
     */
    public function save(Request $request, SchoolClass $class)
    {
        $this->authorizeClassAccess($request, $class);

        $payload = $request->validate([
            'attendance_date' => 'required|date',
            'records' => 'required|array',
            'records.*.status' => 'required|in:Present,Absent,Late,Excused',
            'records.*.remarks' => 'nullable|string|max:255',
        ]);

        $enrolledStudentIds = $class->students()
            ->wherePivot('status', 'enrolled')
            ->pluck('students.id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $userId = $request->user()->id;
        $now = now();
        $rows = [];
        foreach ($payload['records'] as $studentId => $r) {
            if (! in_array((int) $studentId, $enrolledStudentIds, true)) {
                abort(422, 'Every attendance student must be actively enrolled in the class.');
            }

            $rows[] = [
                'class_id' => $class->id,
                'student_id' => (int) $studentId,
                'attendance_date' => $payload['attendance_date'],
                'status' => $r['status'],
                'remarks' => $r['remarks'] ?? null,
                'recorded_by' => $userId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($rows) {
            DB::table('attendance')->upsert(
                $rows,
                ['class_id', 'student_id', 'attendance_date'],
                ['status', 'remarks', 'recorded_by', 'updated_at']
            );
        }

        return redirect("/attendance/{$class->id}?date={$payload['attendance_date']}")
            ->with('status', 'Attendance saved.');
    }

    /**
     * History view with date filter.
     */
    public function history(Request $request, SchoolClass $class)
    {
        $this->authorizeClassAccess($request, $class);
        $from = $request->query('from', date('Y-m-d', strtotime('-30 days')));
        $to = $request->query('to', date('Y-m-d'));

        $records = Attendance::with('student')
            ->where('class_id', $class->id)
            ->where('attendance_date', '>=', $from)
            ->where('attendance_date', '<=', $to)
            ->orderBy('attendance_date', 'desc')
            ->orderBy('student_id')
            ->get();

        // Per-student summary
        $studentsById = $class->students()->get()->keyBy('id');
        $recordsByStudent = $records->groupBy('student_id');
        $summary = [];
        foreach ($studentsById as $sid => $student) {
            $studentRecords = $recordsByStudent->get($sid, collect());
            $total = $studentRecords->count();
            $present = $studentRecords->whereIn('status', ['Present', 'Late'])->count();
            $summary[] = [
                'student' => $student,
                'total' => $total,
                'present' => $present,
                'percentage' => $total > 0 ? round($present / $total * 100, 1) : null,
            ];
        }

        return view('attendance.history', compact('class', 'records', 'summary', 'from', 'to'));
    }

    private function authorizeClassAccess(Request $request, SchoolClass $class): void
    {
        $user = $request->user();
        if ($user->isAdmin()) {
            return;
        }
        $teacher = $user->teacher;
        if (! $teacher || $class->teacher_id !== $teacher->id) {
            abort(403);
        }
    }
}
