<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardStatsController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $classQuery = SchoolClass::query();
        $classIdsQuery = SchoolClass::query()->select('id');

        if ($request->user()->isTeacher()) {
            $teacher = $request->user()->teacher;
            if (! $teacher) {
                return response()->json(['data' => [
                    'classes' => 0,
                    'students' => 0,
                    'attendance_records' => 0,
                    'assignments' => 0,
                    'announcements' => 0,
                ]]);
            }

            $classQuery->where('teacher_id', $teacher->id);
            $classIdsQuery->where('teacher_id', $teacher->id);
        }

        return response()->json(['data' => [
            'classes' => (clone $classQuery)->count(),
            'students' => Student::whereHas('classes', fn ($query) => $query->whereIn('classes.id', clone $classIdsQuery))->count(),
            'attendance_records' => Attendance::whereIn('class_id', clone $classIdsQuery)->count(),
            'assignments' => Assignment::whereIn('class_id', clone $classIdsQuery)->count(),
            'announcements' => Announcement::whereIn('class_id', clone $classIdsQuery)->count(),
        ]]);
    }
}
