<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

class StudentClassController extends Controller
{
    public function index(Request $request)
    {
        $student = $this->student($request);
        $classes = $student->classes()
            ->wherePivot('status', 'enrolled')
            ->with('teacher.user')
            ->orderBy('class_name')
            ->get();

        return view('student.classes.index', compact('classes', 'student'));
    }

    public function show(Request $request, SchoolClass $class)
    {
        $student = $this->student($request);
        $enrolled = $student->classes()
            ->wherePivot('status', 'enrolled')
            ->whereKey($class->id)
            ->exists();

        if (! $enrolled) {
            abort(403, 'You are not enrolled in this class.');
        }

        $class->load([
            'teacher.user',
            'announcements' => fn ($q) => $q->orderByDesc('publish_date')->limit(5),
            'assignments' => fn ($q) => $q->orderByDesc('deadline')->limit(5),
        ]);

        return view('student.classes.show', compact('class', 'student'));
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
