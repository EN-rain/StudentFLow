<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\SchoolClass;
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
            $query->whereDate('attendance_date', $date);
        }
        if ($from = $request->query('from')) {
            $query->whereDate('attendance_date', '>=', $from);
        }
        if ($to = $request->query('to')) {
            $query->whereDate('attendance_date', '<=', $to);
        }

        if ($request->user()->isTeacher()) {
            $teacher = $request->user()->teacher;
            if (! $teacher) {
                return response()->json(['data' => []]);
            }
            $classIds = SchoolClass::where('teacher_id', $teacher->id)->pluck('id');
            $query->whereIn('class_id', $classIds);
        }

        return response()->json(['data' => $query->orderBy('attendance_date', 'desc')->orderBy('student_id')->limit(500)->get()]);
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
            'records.*.student_id' => 'required|integer|exists:students,id',
            'records.*.status' => 'required|in:Present,Absent,Late,Excused',
            'records.*.remarks' => 'nullable|string|max:255',
        ]);

        $this->authorizeClassAccess($request, $payload['class_id']);

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
            ->whereDate('attendance_date', $payload['attendance_date'])
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
        $total = Attendance::where('student_id', $studentId)->count();
        $present = Attendance::where('student_id', $studentId)
            ->whereIn('status', ['Present', 'Late'])->count();
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
