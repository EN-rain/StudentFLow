<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentGrade;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        if ($user->isAdmin()) {
            return $this->adminDashboard($user);
        }

        return $this->teacherDashboard($user);
    }

    private function adminDashboard($user)
    {
        $totalStudents = Student::where('status', 'active')->count();
        $totalClasses = SchoolClass::where('status', 'active')->count();
        $totalTeachers = Teacher::count();
        $today = Carbon::today()->toDateString();
        $absentToday = Attendance::whereDate('attendance_date', $today)
            ->whereIn('status', ['Absent', 'Late'])
            ->distinct('student_id')
            ->count('student_id');
        $pendingAssignments = Assignment::where('status', '!=', 'Completed')
            ->where('deadline', '>=', $today)
            ->count();
        $recentAnnouncements = Announcement::orderBy('publish_date', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.admin', compact(
            'totalStudents', 'totalClasses', 'totalTeachers',
            'absentToday', 'pendingAssignments', 'recentAnnouncements'
        ));
    }

    private function teacherDashboard($user)
    {
        $teacher = $user->teacher;
        if (! $teacher) {
            abort(403, 'No teacher profile linked to this account.');
        }

        $classIds = SchoolClass::where('teacher_id', $teacher->id)->pluck('id');
        $totalClasses = $classIds->count();

        $today = Carbon::today()->toDateString();
        $absentToday = Attendance::whereIn('class_id', $classIds)
            ->whereDate('attendance_date', $today)
            ->whereIn('status', ['Absent', 'Late'])
            ->distinct('student_id')
            ->count('student_id');

        $totalStudents = DB::table('class_students')
            ->whereIn('class_id', $classIds)
            ->distinct('student_id')
            ->count('student_id');

        $pendingAssignments = Assignment::whereIn('class_id', $classIds)
            ->where('status', '!=', 'Completed')
            ->where('deadline', '>=', $today)
            ->count();

        $recentAnnouncements = Announcement::where('teacher_id', $teacher->id)
            ->orderBy('publish_date', 'desc')
            ->limit(5)
            ->get();

        $recentGrades = StudentGrade::whereHas('gradeItem', function ($q) use ($classIds) {
            $q->whereIn('class_id', $classIds);
        })->orderBy('updated_at', 'desc')->limit(5)->with('student', 'gradeItem')->get();

        return view('dashboard.teacher', compact(
            'teacher', 'totalClasses', 'absentToday',
            'totalStudents', 'pendingAssignments',
            'recentAnnouncements', 'recentGrades'
        ));
    }
}
