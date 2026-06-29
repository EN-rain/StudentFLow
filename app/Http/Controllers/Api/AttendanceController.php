<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Support\ApiPagination;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    /**
     * List attendance with filters: class_id, date, from, to.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Attendance::with('student');

        if ($classId = $request->query('class_id')) {
            $query->where('class_id', $classId);
        }
        if ($date = $request->query('date')) {
            $query->where('attendance_date', $date);
        }
        if ($from = $request->query('from')) {
            $query->where('attendance_date', '>=', $from);
        }
        if ($to = $request->query('to')) {
            $query->where('attendance_date', '<=', $to);
        }

        if ($request->user()->isTeacher()) {
            $teacher = $request->user()->teacher;
            if (! $teacher) {
                return response()->json(['data' => []]);
            }
            $classIds = SchoolClass::where('teacher_id', $teacher->id)->pluck('id');
            $query->whereIn('class_id', $classIds);
        }

        return response()->json(ApiPagination::paginate(
            $query->orderBy('attendance_date', 'desc')->orderBy('student_id'),
            $request
        ));
    }

    /**
     * Bulk save attendance for a class + date.
     * Body: {class_id, attendance_date, records: [{student_id, status, remarks?}]}
     */
    public function store(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'class_id' => 'required|integer|exists:classes,id',
            'attendance_date' => 'required|date',
            'records' => 'required|array|min:1',
            'records.*.student_id' => 'required|integer|distinct|exists:students,id',
            'records.*.status' => 'required|in:Present,Absent,Late,Excused',
            'records.*.remarks' => 'nullable|string|max:255',
        ]);

        $this->authorizeClassAccess($request, $payload['class_id']);

        $enrolledStudentIds = DB::table('class_students')
            ->where('class_id', $payload['class_id'])
            ->where('status', 'enrolled')
            ->pluck('student_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        foreach ($payload['records'] as $record) {
            if (! in_array((int) $record['student_id'], $enrolledStudentIds, true)) {
                abort(422, 'Every attendance student must be actively enrolled in the class.');
            }
        }

        $userId = $request->user()->id;
        $rows = [];
        $now = now();

        foreach ($payload['records'] as $r) {
            $rows[] = [
                'class_id' => $payload['class_id'],
                'student_id' => $r['student_id'],
                'attendance_date' => $payload['attendance_date'],
                'status' => $r['status'],
                'remarks' => $r['remarks'] ?? null,
                'recorded_by' => $userId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Upsert by (class_id, student_id, attendance_date)
        DB::table('attendance')->upsert(
            $rows,
            ['class_id', 'student_id', 'attendance_date'],
            ['status', 'remarks', 'recorded_by', 'updated_at']
        );

        $records = Attendance::where('class_id', $payload['class_id'])
            ->where('attendance_date', $payload['attendance_date'])
            ->get();

        return response()->json(['data' => $records], 201);
    }

    /**
     * Mark all enrolled students Present for a class + date (overrides existing).
     */
    public function markAllPresent(Request $request): JsonResponse
    {
        $payload = $request->validate([
            'class_id' => 'required|integer|exists:classes,id',
            'attendance_date' => 'required|date',
            'remarks' => 'nullable|string|max:255',
        ]);

        $this->authorizeClassAccess($request, $payload['class_id']);

        $studentIds = DB::table('class_students')
            ->where('class_id', $payload['class_id'])
            ->where('status', 'enrolled')
            ->pluck('student_id');

        $userId = $request->user()->id;
        $now = now();
        $rows = [];
        foreach ($studentIds as $sid) {
            $rows[] = [
                'class_id' => $payload['class_id'],
                'student_id' => $sid,
                'attendance_date' => $payload['attendance_date'],
                'status' => 'Present',
                'remarks' => $payload['remarks'] ?? null,
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

        return response()->json(['message' => 'Marked all present.', 'count' => count($rows)]);
    }

    public function update(Request $request, Attendance $attendance): JsonResponse
    {
        $this->authorizeClassAccess($request, $attendance->class_id);
        $payload = $request->validate([
            'status' => 'required|in:Present,Absent,Late,Excused',
            'remarks' => 'nullable|string|max:255',
        ]);
        $attendance->update($payload);

        return response()->json(['data' => $attendance]);
    }

    public function destroy(Request $request, Attendance $attendance): JsonResponse
    {
        $this->authorizeClassAccess($request, $attendance->class_id);
        $attendance->delete();

        return response()->json(['message' => 'Attendance record deleted.']);
    }

    /**
     * Per-student attendance stats: total records + percentage of Present/Late.
     */
    public function studentStats(Request $request, int $studentId): JsonResponse
    {
        $query = Attendance::where('student_id', $studentId);
        if ($classId = $request->query('class_id')) {
            $this->authorizeClassAccess($request, (int) $classId);
            $query->where('class_id', $classId);
        }
        if ($request->user()->isTeacher()) {
            $teacherId = $request->user()->teacher?->id;
            if (! $teacherId) {
                abort(403);
            }
            $query->whereIn('class_id', SchoolClass::where('teacher_id', $teacherId)->select('id'));
        }

        $total = (clone $query)->count();
        $present = (clone $query)->whereIn('status', ['Present', 'Late'])->count();
        $pct = $total > 0 ? round($present / $total * 100, 1) : null;

        return response()->json(['data' => ['student_id' => $studentId, 'total' => $total, 'present_or_late' => $present, 'percentage' => $pct]]);
    }

    private function authorizeClassAccess(Request $request, int $classId): void
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
            abort(403, 'You can only manage attendance for your own classes.');
        }
    }
}
