<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StudentReportController extends Controller
{
    /**
     * Student profile report (HTML view).
     * Route: GET /student/reports/profile (added in step 2)
     */
    public function studentProfile(Request $request)
    {
        $student = $this->resolveOwnStudent($request);

        $data = $this->buildStudentProfileData($student);

        return view('reports.student-profile', $data);
    }

    /**
     * Student profile report (PDF download).
     * Route: GET /student/reports/profile.pdf (added in step 2)
     */
    public function studentProfilePdf(Request $request): Response
    {
        $student = $this->resolveOwnStudent($request);

        $data = $this->buildStudentProfileData($student);

        $html = view('reports.student-profile', $data)->render();
        $pdf = Pdf::loadHTML($html)->setPaper('a4', 'portrait');

        return $pdf->download("student_profile_{$student->student_number}.pdf");
    }

    private function resolveOwnStudent(Request $request): Student
    {
        $user = $request->user();
        $student = $user->student;
        if (! $student) {
            abort(403, 'No student profile linked to this account.');
        }

        return $student->load('classes.teacher.user');
    }

    private function buildStudentProfileData(Student $student): array
    {
        return [
            'student' => $student,
            'rows' => [[
                'Student Number' => $student->student_number,
                'Name' => $student->full_name,
                'Email' => $student->email,
                'Status' => ucfirst($student->status),
                'Classes' => $student->classes->pluck('class_name')->join(', '),
            ]],
            'title' => 'My Student Profile Report',
            'class' => null,
            'type' => 'student-profile',
        ];
    }
}
