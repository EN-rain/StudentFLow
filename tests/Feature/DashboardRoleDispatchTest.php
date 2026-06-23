<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardRoleDispatchTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_admin_sees_admin_dashboard_view(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertOk();
        $response->assertViewIs('dashboard.admin');
    }

    public function test_teacher_sees_teacher_dashboard_view(): void
    {
        $teacher = User::where('role', 'teacher')->firstOrFail();

        $response = $this->actingAs($teacher)->get('/dashboard');

        $response->assertOk();
        $response->assertViewIs('dashboard.teacher');
    }

    public function test_student_sees_student_dashboard_view(): void
    {
        $studentUser = User::factory()->create([
            'role' => 'student',
            'username' => 'test-student-dashboard',
            'name' => 'Test Student',
            'status' => 'active',
        ]);
        $student = Student::create([
            'student_number' => 'TEST-9999',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'email' => $studentUser->email,
            'status' => 'active',
        ]);
        $studentUser->forceFill(['student_id' => $student->id])->save();

        $response = $this->actingAs($studentUser)->get('/dashboard');

        $response->assertOk();
        $response->assertViewIs('dashboard.student');
    }

    public function test_student_dashboard_shows_four_stat_cards(): void
    {
        // Create a teacher and class
        $teacherUser = User::factory()->create([
            'username' => 'test-teacher-statcard',
            'role' => 'teacher',
            'name' => 'Test Teacher',
            'status' => 'active',
        ]);
        $teacher = Teacher::create([
            'user_id' => $teacherUser->id,
            'employee_number' => 'T-9999',
            'first_name' => 'Test',
            'last_name' => 'Teacher',
        ]);

        // Create two classes and enroll the student in both
        $class1 = SchoolClass::create([
            'teacher_id' => $teacher->id,
            'class_name' => 'Math 101',
            'subject' => 'Mathematics',
            'grade_level' => '10',
            'school_year' => '2025-2026',
            'semester' => '1',
            'status' => 'active',
        ]);
        $class2 = SchoolClass::create([
            'teacher_id' => $teacher->id,
            'class_name' => 'Science 101',
            'subject' => 'Science',
            'grade_level' => '10',
            'school_year' => '2025-2026',
            'semester' => '1',
            'status' => 'active',
        ]);

        // Create student and link to user
        $studentUser = User::factory()->create([
            'username' => 'stat-card-student',
            'role' => 'student',
            'name' => 'Stat Card Student',
            'status' => 'active',
        ]);
        $student = Student::create([
            'student_number' => 'STAT-9999',
            'first_name' => 'Stat',
            'last_name' => 'Card',
            'email' => $studentUser->email,
            'status' => 'active',
        ]);
        $studentUser->forceFill(['student_id' => $student->id])->save();

        // Enroll student in both classes
        $class1->students()->attach($student->id, ['status' => 'enrolled', 'date_enrolled' => now()]);
        $class2->students()->attach($student->id, ['status' => 'enrolled', 'date_enrolled' => now()]);

        // Create a pending assignment
        Assignment::create([
            'class_id' => $class1->id,
            'title' => 'Homework 1',
            'description' => 'Complete exercises 1-10',
            'date_assigned' => now()->subDays(7),
            'deadline' => now()->addDays(7),
            'maximum_score' => 100,
            'status' => 'Active',
        ]);

        // Create an upcoming exam with an assigned attempt
        $exam = Exam::create([
            'class_id' => $class1->id,
            'teacher_id' => $teacher->id,
            'title' => 'Midterm Exam',
            'available_from' => now(),
            'due_at' => now()->addDays(14),
            'duration_minutes' => 60,
            'maximum_score' => 100,
            'status' => 'published',
        ]);
        ExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $student->id,
            'magic_token' => 'test-token-12345',
            'status' => 'assigned',
        ]);

        $response = $this->actingAs($studentUser)->get('/dashboard');

        $response->assertOk();
        $response->assertViewIs('dashboard.student');
        $response->assertSee('Enrolled Classes', false);
        $response->assertSee('Recent Grades', false);
        $response->assertSee('Pending Assignments', false);
        $response->assertSee('Upcoming Exams', false);
    }
}
