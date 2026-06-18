<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function show(Request $request, string $type): JsonResponse
    {
        if (! in_array($type, ['student-profile', 'attendance', 'grades', 'class-performance', 'missing-assignments', 'failing-grades', 'frequent-absences'], true)) {
            abort(404);
        }

        $class = null;
        if ($request->query('class_id')) {
            $class = SchoolClass::with(['students', 'gradeCategories.items.studentGrades', 'assignments.submissions'])->findOrFail($request->query('class_id'));
            $this->authorizeClass($request, $class);
        }

        if ($type === 'student-profile') {
            $student = Student::with('classes.teacher.user', 'attendance', 'grades.gradeItem.category', 'assignmentSubmissions.assignment')->findOrFail($request->query('student_id'));
            $this->authorizeStudent($request, $student);

            return response()->json(['data' => $student]);
        }

        if (! $class) {
            abort(400, 'class_id query parameter is required.');
        }

        return response()->json(['data' => [
            'type' => $type,
            'class' => $class,
            'rows' => $this->rows($type, $class),
        ]]);
    }

    private function rows(string $type, SchoolClass $class): array
    {
        return match ($type) {
            'attendance' => $this->attendanceRows($class),
            'grades' => $this->gradeRows($class),
            'class-performance' => $this->performanceRows($class),
            'missing-assignments' => $this->missingRows($class),
            'failing-grades' => array_values(array_filter($this->gradeRows($class), fn ($r) => $r['final_grade'] < 75)),
            'frequent-absences' => array_values(array_filter($this->attendanceRows($class), fn ($r) => $r['absent'] >= 2 || ($r['percentage'] !== null && $r['percentage'] < 80))),
            default => [],
        };
    }

    private function attendanceRows(SchoolClass $class): array
    {
        return $class->students->map(function ($student) use ($class) {
            $records = $student->attendance()->where('class_id', $class->id)->get();
            $total = $records->count();
            $present = $records->whereIn('status', ['Present', 'Late'])->count();

            return [
                'student_number' => $student->student_number,
                'name' => $student->full_name,
                'total' => $total,
                'present' => $present,
                'absent' => $records->where('status', 'Absent')->count(),
                'late' => $records->where('status', 'Late')->count(),
                'excused' => $records->where('status', 'Excused')->count(),
                'percentage' => $total > 0 ? round($present / $total * 100, 1) : null,
            ];
        })->values()->all();
    }

    private function gradeRows(SchoolClass $class): array
    {
        $rows = [];
        foreach ($class->students as $student) {
            $final = 0.0;
            foreach ($class->gradeCategories as $cat) {
                $ratios = [];
                foreach ($cat->items as $item) {
                    $grade = $item->studentGrades->firstWhere('student_id', $student->id);
                    if ($grade && (float) $item->maximum_score > 0) {
                        $ratios[] = (float) $grade->score / (float) $item->maximum_score;
                    }
                }
                $final += (count($ratios) ? array_sum($ratios) / count($ratios) : 0) * (float) $cat->percentage_weight;
            }
            $rows[] = ['student_number' => $student->student_number, 'name' => $student->full_name, 'final_grade' => round($final, 2), 'status' => $final >= 75 ? 'Pass' : 'Fail'];
        }
        usort($rows, fn ($a, $b) => $b['final_grade'] <=> $a['final_grade']);
        foreach ($rows as $i => &$row) {
            $row['rank'] = $i + 1;
        }

        return $rows;
    }

    private function performanceRows(SchoolClass $class): array
    {
        $grades = collect($this->gradeRows($class))->keyBy('student_number');

        return collect($this->attendanceRows($class))->map(fn ($r) => $r + ($grades[$r['student_number']] ?? []))->values()->all();
    }

    private function missingRows(SchoolClass $class): array
    {
        $rows = [];
        foreach ($class->assignments as $assignment) {
            foreach ($class->students as $student) {
                $submission = $assignment->submissions->firstWhere('student_id', $student->id);
                if (! $submission || $submission->status === 'Missing') {
                    $rows[] = [
                        'assignment' => $assignment->title,
                        'student_number' => $student->student_number,
                        'name' => $student->full_name,
                        'deadline' => optional($assignment->deadline)->toDateString(),
                        'status' => $submission?->status ?? 'Missing',
                    ];
                }
            }
        }

        return $rows;
    }

    private function authorizeClass(Request $request, SchoolClass $class): void
    {
        if ($request->user()->isAdmin()) {
            return;
        }
        $teacher = $request->user()->teacher;
        if (! $teacher || $class->teacher_id !== $teacher->id) {
            abort(403);
        }
    }

    private function authorizeStudent(Request $request, Student $student): void
    {
        if ($request->user()->isAdmin()) {
            return;
        }
        $teacher = $request->user()->teacher;
        $allowed = $teacher && $student->classes()->where('teacher_id', $teacher->id)->exists();
        if (! $allowed) {
            abort(403);
        }
    }
}
