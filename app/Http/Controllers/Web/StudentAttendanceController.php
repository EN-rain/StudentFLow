<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;

class StudentAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $student = $this->student($request);

        // Load attendance records grouped by class.
        // Eager-load class so the Blade view doesn't N+1.
        $records = Attendance::with('schoolClass')
            ->where('student_id', $student->id)
            ->orderBy('attendance_date', 'desc')
            ->orderBy('class_id')
            ->get();

        // Group by class_id so the view renders one panel per class.
        $byClass = $records->groupBy(fn ($r) => $r->schoolClass?->class_name ?? 'Unassigned');

        // Per-class summary stats.
        $summary = $byClass->map(function ($rows, $className) {
            $total = $rows->count();
            $present = $rows->whereIn('status', ['Present', 'Late'])->count();
            $absent = $rows->where('status', 'Absent')->count();
            $late = $rows->where('status', 'Late')->count();
            $excused = $rows->where('status', 'Excused')->count();

            return [
                'class_name' => $className,
                'rows' => $rows,
                'total' => $total,
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
                'excused' => $excused,
                'rate' => $total > 0 ? round($present / $total * 100, 1) : null,
            ];
        })->values();

        $overallTotal = $records->count();
        $overallPresent = $records->whereIn('status', ['Present', 'Late'])->count();

        return view('student.attendance.index', [
            'student' => $student,
            'summary' => $summary,
            'overallTotal' => $overallTotal,
            'overallPresent' => $overallPresent,
            'overallRate' => $overallTotal > 0 ? round($overallPresent / $overallTotal * 100, 1) : null,
        ]);
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
