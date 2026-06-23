<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function show(Request $request, string $type): JsonResponse
    {
        if (! in_array($type, ['student-profile', 'attendance', 'grades', 'class-performance', 'missing-assignments', 'failing-grades', 'frequent-absences'], true)) {
            abort(404);
        }

        $class = null;
        if ($request->query('class_id')) {
            $relations = ['students'];
            if (in_array($type, ['grades', 'class-performance', 'failing-grades'], true)) {
                $relations[] = 'gradeCategories.items.studentGrades';
            }
            if ($type === 'missing-assignments') {
                $relations[] = 'assignments.submissions';
            }

            $class = SchoolClass::query()
                ->select(['id', 'teacher_id', 'class_name', 'subject'])
                ->with($relations)
                ->findOrFail($request->query('class_id'));
            $this->authorizeClass($request, $class);
        }

        if ($type === 'student-profile') {
            $student = Student::with('classes.teacher.user')->findOrFail($request->query('student_id'));
            $this->authorizeStudent($request, $student);

            return response()->json(['data' => $student]);
        }

        if (! $class) {
            abort(400, 'class_id query parameter is required.');
        }

        return response()->json(['data' => [
            'type' => $type,
            'class' => [
                'id' => $class->id,
                'class_name' => $class->class_name,
                'subject' => $class->subject,
            ],
            'rows' => $this->rows($type, $class),
        ]]);
    }

    /**
     * PDF export — delegates to the web ReportController so both surfaces produce
     * identical bytes. Same {type} whitelist; same authorize checks; same Content-Type.
     * Route: GET /api/reports/{type}/pdf
     */
    public function pdf(Request $request, string $type): Response
    {
        return app(\App\Http\Controllers\Web\ReportController::class)->pdf($request, $type);
    }

    /**
     * CSV export — delegates to the web ReportController for byte-identical output.
     * Route: GET /api/reports/{type}/csv
     */
    public function csv(Request $request, string $type): StreamedResponse
    {
        return app(\App\Http\Controllers\Web\ReportController::class)->csv($request, $type);
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
        $statsByStudent = Attendance::query()
            ->select('student_id')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status IN ('Present', 'Late') THEN 1 ELSE 0 END) as present")
            ->selectRaw("SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) as absent")
            ->selectRaw("SUM(CASE WHEN status = 'Late' THEN 1 ELSE 0 END) as late")
            ->selectRaw("SUM(CASE WHEN status = 'Excused' THEN 1 ELSE 0 END) as excused")
            ->where('class_id', $class->id)
            ->whereIn('student_id', $class->students->pluck('id'))
            ->groupBy('student_id')
            ->get()
            ->keyBy('student_id');

        return $class->students->map(function ($student) use ($statsByStudent) {
            $stats = $statsByStudent->get($student->id);
            $total = (int) ($stats->total ?? 0);
            $present = (int) ($stats->present ?? 0);

            return [
                'student_number' => $student->student_number,
                'name' => $student->full_name,
                'total' => $total,
                'present' => $present,
                'absent' => (int) ($stats->absent ?? 0),
                'late' => (int) ($stats->late ?? 0),
                'excused' => (int) ($stats->excused ?? 0),
                'percentage' => $total > 0 ? round($present / $total * 100, 1) : null,
            ];
        })->values()->all();
    }

    private function gradeRows(SchoolClass $class): array
    {
        $gradesByItem = [];
        foreach ($class->gradeCategories as $category) {
            foreach ($category->items as $item) {
                $gradesByItem[$item->id] = $item->studentGrades->keyBy('student_id');
            }
        }

        $rows = [];
        foreach ($class->students as $student) {
            $final = 0.0;
            foreach ($class->gradeCategories as $category) {
                $ratios = [];
                foreach ($category->items as $item) {
                    $grade = $gradesByItem[$item->id]->get($student->id);
                    if ($grade && (float) $item->maximum_score > 0) {
                        $ratios[] = (float) $grade->score / (float) $item->maximum_score;
                    }
                }
                $final += ($ratios ? array_sum($ratios) / count($ratios) : 0) * (float) $category->percentage_weight;
            }
            $rows[] = [
                'student_number' => $student->student_number,
                'name' => $student->full_name,
                'final_grade' => round($final, 2),
                'status' => $final >= 75 ? 'Pass' : 'Fail',
            ];
        }

        usort($rows, fn ($a, $b) => $b['final_grade'] <=> $a['final_grade']);
        foreach ($rows as $index => &$row) {
            $row['rank'] = $index + 1;
        }
        unset($row);

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
            $submissionsByStudent = $assignment->submissions->keyBy('student_id');
            foreach ($class->students as $student) {
                $submission = $submissionsByStudent->get($student->id);
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
