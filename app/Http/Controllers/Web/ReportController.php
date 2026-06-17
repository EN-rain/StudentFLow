<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\SchoolClass;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Reports landing page - class picker.
     */
    public function index(Request $request)
    {
        $query = SchoolClass::query();
        if ($request->user()->isTeacher()) {
            $teacher = $request->user()->teacher;
            if ($teacher) $query->where('teacher_id', $teacher->id);
        }
        $classes = $query->orderBy('class_name')->get();
        return view('reports.index', compact('classes'));
    }

    /**
     * Show a report (HTML / printable view).
     * Route: GET /reports/{type}?class_id=N
     */
    public function show(Request $request, string $type)
    {
        $this->authorizeType($type);
        $class = $type === 'student-profile' ? null : $this->resolveClass($request);
        if ($class) $this->authorizeAccess($request, $class);

        $data = $this->buildReportData($request, $type, $class);

        $view = view()->exists("reports.{$type}") ? "reports.{$type}" : 'reports.table';
        return view($view, array_merge($data, ['class' => $class, 'type' => $type]));
    }

    /**
     * PDF export.
     * Route: GET /reports/{type}/pdf?class_id=N
     */
    public function pdf(Request $request, string $type): Response
    {
        $this->authorizeType($type);
        $class = $type === 'student-profile' ? null : $this->resolveClass($request);
        if ($class) $this->authorizeAccess($request, $class);

        $data = $this->buildReportData($request, $type, $class);
        $viewName = view()->exists("reports.{$type}") ? "reports.{$type}" : 'reports.table';
        $view = view($viewName, array_merge($data, ['class' => $class, 'type' => $type]))->render();

        $pdf = Pdf::loadHTML($view)->setPaper('a4', 'portrait');
        $suffix = $class ? "class{$class->id}" : 'student';
        return $pdf->download("{$type}_{$suffix}.pdf");
    }

    /**
     * CSV export.
     * Route: GET /reports/{type}/csv?class_id=N
     */
    public function csv(Request $request, string $type): StreamedResponse
    {
        $this->authorizeType($type);
        $class = $type === 'student-profile' ? null : $this->resolveClass($request);
        if ($class) $this->authorizeAccess($request, $class);

        $data = $this->buildReportData($request, $type, $class);

        $suffix = $class ? "class{$class->id}" : 'student';
        $filename = "{$type}_{$suffix}.csv";
        $rows = $this->buildCsvRows($type, $data);

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function buildReportData(Request $request, string $type, ?SchoolClass $class): array
    {
        if ($type === 'student-profile') {
            $student = Student::with('classes.teacher.user', 'attendance', 'grades.gradeItem.category', 'assignmentSubmissions.assignment')->find($request->query('student_id'));
            if (! $student) abort(400, 'student_id query parameter is required.');
            $this->authorizeStudent($request, $student);
            return [
                'student' => $student,
                'rows' => [[
                    'Student Number' => $student->student_number,
                    'Name' => $student->full_name,
                    'Email' => $student->email,
                    'Status' => ucfirst($student->status),
                    'Classes' => $student->classes->pluck('class_name')->join(', '),
                ]],
                'title' => 'Student Profile Report',
            ];
        }

        $class->load(['students', 'gradeCategories.items.studentGrades']);

        if ($type === 'attendance') {
            return $this->attendanceData($class);
        }
        if ($type === 'grades') {
            return $this->gradesData($class);
        }
        if ($type === 'missing-assignments') {
            return ['rows' => $this->missingAssignmentRows($class), 'title' => 'Missing Assignments Report'];
        }
        if ($type === 'failing-grades') {
            $grades = $this->gradesData($class);
            return ['rows' => array_values(array_filter($grades['rows'], fn ($r) => $r['final_grade'] < 75)), 'classAverage' => $grades['classAverage'], 'title' => 'Students With Failing Grades'];
        }
        if ($type === 'frequent-absences') {
            $attendance = $this->attendanceData($class);
            return ['rows' => array_values(array_filter($attendance['rows'], fn ($r) => $r['absent'] >= 2 || ($r['percentage'] !== null && $r['percentage'] < 80))), 'title' => 'Students With Frequent Absences'];
        }

        $attendance = $this->attendanceData($class)['rows'];
        $grades = $this->gradesData($class);
        $byStudent = [];
        foreach ($attendance as $r) {
            $byStudent[$r['student_number']] = $r + ['final_grade' => null, 'rank' => null, 'status' => null];
        }
        foreach ($grades['rows'] as $r) {
            $sn = $r['student_number'];
            if (isset($byStudent[$sn])) {
                $byStudent[$sn] = array_merge($byStudent[$sn], $r);
            } else {
                $byStudent[$sn] = $r + ['percentage' => null, 'total' => 0, 'present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0];
            }
        }
        // Sort by rank
        uasort($byStudent, fn ($a, $b) => ($a['rank'] ?? PHP_INT_MAX) <=> ($b['rank'] ?? PHP_INT_MAX));
        $rows = array_values($byStudent);
        return ['rows' => $rows, 'classAverage' => $grades['classAverage']];
    }

    private function missingAssignmentRows(SchoolClass $class): array
    {
        $class->loadMissing('students', 'assignments.submissions');
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

    private function attendanceData(SchoolClass $class): array
    {
        $students = $class->students()->orderBy('last_name')->get();
        $rows = [];
        $classSize = max(count($students), 1);
        foreach ($students as $s) {
            $total = $s->attendance()->where('class_id', $class->id)->count();
            $present = $s->attendance()->where('class_id', $class->id)->whereIn('status', ['Present', 'Late'])->count();
            $absent = $s->attendance()->where('class_id', $class->id)->where('status', 'Absent')->count();
            $late = $s->attendance()->where('class_id', $class->id)->where('status', 'Late')->count();
            $excused = $s->attendance()->where('class_id', $class->id)->where('status', 'Excused')->count();
            $pct = $total > 0 ? round($present / $total * 100, 1) : null;
            $rows[] = [
                'student_number' => $s->student_number,
                'name' => $s->full_name,
                'total' => $total,
                'present' => $present,
                'absent' => $absent,
                'late' => $late,
                'excused' => $excused,
                'percentage' => $pct,
            ];
        }
        return ['rows' => $rows, 'classSize' => $classSize];
    }

    private function gradesData(SchoolClass $class): array
    {
        $students = $class->students()->orderBy('last_name')->get();
        $rows = [];

        $studentFinals = [];
        foreach ($students as $student) {
            $total = 0.0;
            foreach ($class->gradeCategories as $cat) {
                $ratios = [];
                foreach ($cat->items as $item) {
                    if ((float) $item->maximum_score <= 0) continue;
                    $sg = $item->studentGrades->firstWhere('student_id', $student->id);
                    if (! $sg) continue;
                    $ratios[] = ((float) $sg->score) / ((float) $item->maximum_score);
                }
                $catAvg = count($ratios) > 0 ? array_sum($ratios) / count($ratios) : 0.0;
                $total += $catAvg * ((float) $cat->percentage_weight);
            }
            $studentFinals[$student->id] = round($total, 2);
        }

        // Sort by final grade desc for ranking
        arsort($studentFinals);

        $rank = 0;
        $lastScore = null;
        $lastRank = 0;
        foreach ($studentFinals as $sid => $g) {
            if ($lastScore !== $g) {
                $rank = $lastRank + 1;
                $lastRank = $rank;
                $lastScore = $g;
            } else {
                $rank = $lastRank; // tie
            }
            $student = $students->firstWhere('id', $sid);
            $rows[] = [
                'rank' => $rank,
                'student_number' => $student->student_number,
                'name' => $student->full_name,
                'final_grade' => $g,
                'status' => $g >= 75 ? 'Pass' : 'Fail',
            ];
        }

        $valid = array_filter($studentFinals);
        $classAverage = count($valid) > 0 ? round(array_sum($valid) / count($valid), 2) : null;

        return ['rows' => $rows, 'classAverage' => $classAverage];
    }

    private function buildCsvRows(string $type, array $data): array
    {
        $rows = $data['rows'] ?? [];
        if ($type === 'attendance') {
            $out = [['Student Number', 'Name', 'Total Records', 'Present/Late', 'Absent', 'Late', 'Excused', 'Attendance %']];
            foreach ($rows as $r) {
                $out[] = [$r['student_number'], $r['name'], $r['total'], $r['present'], $r['absent'], $r['late'], $r['excused'], $r['percentage']];
            }
            return $out;
        }
        if ($type === 'grades') {
            $out = [['Rank', 'Student Number', 'Name', 'Final Grade', 'Status']];
            foreach ($rows as $r) {
                $out[] = [$r['rank'], $r['student_number'], $r['name'], $r['final_grade'], $r['status']];
            }
            return $out;
        }
        if ($type === 'student-profile') {
            $row = $rows[0] ?? [];
            return [array_keys($row), array_values($row)];
        }
        if ($type === 'missing-assignments') {
            $out = [['Assignment', 'Student Number', 'Name', 'Deadline', 'Status']];
            foreach ($rows as $r) $out[] = [$r['assignment'], $r['student_number'], $r['name'], $r['deadline'], $r['status']];
            return $out;
        }
        if ($type === 'failing-grades') {
            $out = [['Rank', 'Student Number', 'Name', 'Final Grade', 'Status']];
            foreach ($rows as $r) $out[] = [$r['rank'], $r['student_number'], $r['name'], $r['final_grade'], $r['status']];
            return $out;
        }
        if ($type === 'frequent-absences') {
            $out = [['Student Number', 'Name', 'Total Records', 'Present/Late', 'Absent', 'Late', 'Excused', 'Attendance %']];
            foreach ($rows as $r) $out[] = [$r['student_number'], $r['name'], $r['total'], $r['present'], $r['absent'], $r['late'], $r['excused'], $r['percentage']];
            return $out;
        }
        // class-performance: rows are already joined (attendance + grade per student)
        $out = [['Student Number', 'Name', 'Attendance %', 'Final Grade', 'Status']];
        foreach ($rows as $r) {
            $out[] = [
                $r['student_number'],
                $r['name'],
                $r['percentage'] !== null ? $r['percentage'] . '%' : 'N/A',
                $r['final_grade'] ?? 'N/A',
                $r['status'] ?? 'N/A',
            ];
        }
        return $out;
    }

    private function resolveClass(Request $request): SchoolClass
    {
        $classId = $request->query('class_id');
        if (! $classId) abort(400, 'class_id query parameter is required.');
        $class = SchoolClass::find($classId);
        if (! $class) abort(404, 'Class not found.');
        return $class;
    }

    private function authorizeType(string $type): void
    {
        if (! in_array($type, ['student-profile', 'attendance', 'grades', 'class-performance', 'missing-assignments', 'failing-grades', 'frequent-absences'])) {
            abort(404, 'Unknown report type.');
        }
    }

    private function authorizeAccess(Request $request, SchoolClass $class): void
    {
        $user = $request->user();
        if ($user->isAdmin()) return;
        $teacher = $user->teacher;
        if (! $teacher || $class->teacher_id !== $teacher->id) abort(403);
    }

    private function authorizeStudent(Request $request, Student $student): void
    {
        $user = $request->user();
        if ($user->isAdmin()) return;
        $teacher = $user->teacher;
        if (! $teacher || ! $student->classes()->where('teacher_id', $teacher->id)->exists()) abort(403);
    }
}
