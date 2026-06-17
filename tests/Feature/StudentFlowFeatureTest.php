<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\GradeCategory;
use App\Models\SchoolClass;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StudentFlowFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_root_redirects_to_dashboard(): void
    {
        $this->get('/')->assertRedirect('/dashboard');
    }

    public function test_login_disabled_account_and_password_reset_flow(): void
    {
        Notification::fake();
        $admin = User::where('username', 'admin')->first();

        $this->post('/login', ['username' => 'admin', 'password' => 'Admin123!'])
            ->assertRedirect('/dashboard');

        $admin->update(['status' => 'disabled']);
        $this->post('/login', ['username' => 'admin', 'password' => 'Admin123!'])
            ->assertSessionHasErrors('username');
        $admin->update(['status' => 'active']);

        $this->post('/forgot-password', ['email' => $admin->email])
            ->assertSessionHas('status');
        Notification::assertSentTo($admin, ResetPassword::class);
    }

    public function test_admin_can_manage_teacher_settings_and_logs(): void
    {
        $admin = User::where('username', 'admin')->first();

        $this->actingAs($admin)->post('/admin/teachers', [
            'username' => 'new.teacher',
            'name' => 'New Teacher',
            'email' => 'new.teacher@studentflow.local',
            'password' => 'Teacher123!',
            'password_confirmation' => 'Teacher123!',
            'status' => 'active',
            'employee_number' => 'TCH-2026-099',
            'first_name' => 'New',
            'middle_name' => null,
            'last_name' => 'Teacher',
            'department' => 'Science',
            'contact_number' => '09000000000',
        ])->assertRedirect('/admin/teachers');

        $teacher = Teacher::where('employee_number', 'TCH-2026-099')->first();
        $this->assertNotNull($teacher);

        $this->actingAs($admin)->patch("/admin/teachers/{$teacher->id}/status", ['status' => 'disabled'])
            ->assertRedirect();
        $this->assertSame('disabled', $teacher->fresh()->user->status);

        $this->actingAs($admin)->put('/admin/settings', [
            'settings' => ['school_name' => 'Updated School'],
        ])->assertRedirect();

        $this->assertSame('Updated School', SchoolSetting::where('setting_key', 'school_name')->first()->setting_value);
        $this->assertTrue(ActivityLog::where('action', 'teacher.created')->exists());
        $this->assertTrue(ActivityLog::where('action', 'setting.updated')->exists());
    }

    public function test_teacher_scoping_and_enrollment_management(): void
    {
        $john = User::where('username', 'john.reyes')->first();
        $angela = User::where('username', 'angela.cruz')->first();
        $johnClass = SchoolClass::where('class_name', 'BSIT 2A')->first();
        $angelaClass = SchoolClass::where('class_name', 'BSIT 1B')->first();
        $student = Student::where('student_number', '2026-0020')->first();

        Sanctum::actingAs($angela);
        $this->getJson("/api/classes/{$johnClass->id}")->assertForbidden();

        Sanctum::actingAs($john);
        $this->postJson("/api/classes/{$johnClass->id}/enrollments", [
            'student_id' => $student->id,
            'date_enrolled' => '2026-06-17',
        ])->assertCreated();
        $this->assertTrue($johnClass->students()->where('students.id', $student->id)->exists());

        $this->deleteJson("/api/classes/{$johnClass->id}/enrollments/{$student->id}")->assertOk();
        $this->assertFalse($johnClass->students()->where('students.id', $student->id)->exists());

        $this->postJson("/api/classes/{$angelaClass->id}/enrollments", [
            'student_id' => $student->id,
        ])->assertForbidden();
    }

    public function test_grade_assignment_and_report_endpoints(): void
    {
        $admin = User::where('username', 'admin')->first();
        $class = SchoolClass::where('class_name', 'BSIT 2A')->first();
        $assignment = Assignment::where('title', 'Java Student Record Program')->first();
        $student = Student::where('student_number', '2026-0007')->first();

        Sanctum::actingAs($admin);
        $categoryResponse = $this->postJson("/api/classes/{$class->id}/grade-categories", [
            'category_name' => 'Recitation',
            'percentage_weight' => 5,
        ])->assertCreated();
        $categoryId = $categoryResponse->json('data.id');
        $this->assertTrue(GradeCategory::where('id', $categoryId)->exists());

        $this->postJson("/api/assignments/{$assignment->id}/submissions", [
            'submissions' => [[
                'student_id' => $student->id,
                'status' => 'Submitted',
                'score' => 42,
                'submitted_at' => '2026-06-23 10:00:00',
            ]],
        ])->assertOk();
        $this->assertSame('Submitted', AssignmentSubmission::where('assignment_id', $assignment->id)->where('student_id', $student->id)->first()->status);

        $this->getJson("/api/reports/missing-assignments?class_id={$class->id}")
            ->assertOk()
            ->assertJsonPath('data.type', 'missing-assignments');

        $this->getJson("/api/reports/student-profile?student_id={$student->id}")
            ->assertOk()
            ->assertJsonPath('data.student_number', '2026-0007');
    }
}
