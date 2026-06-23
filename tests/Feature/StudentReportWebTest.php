<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentReportWebTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_student_can_view_own_profile_report(): void
    {
        $studentUser = $this->createStudentUser();
        $class = $this->createClass();
        $studentUser->student->classes()->attach($class->id, [
            'date_enrolled' => now(),
            'status' => 'enrolled',
        ]);

        $response = $this->actingAs($studentUser)->get('/student/reports/profile');
        $response->assertOk();
        $response->assertSee('My Student Profile Report', false);
        $response->assertSee($studentUser->student->full_name, false);
        $response->assertSee($studentUser->student->student_number, false);
    }

    public function test_student_can_download_own_profile_pdf(): void
    {
        $studentUser = $this->createStudentUser();
        $class = $this->createClass();
        $studentUser->student->classes()->attach($class->id, [
            'date_enrolled' => now(),
            'status' => 'enrolled',
        ]);

        $response = $this->actingAs($studentUser)->get('/student/reports/profile.pdf');

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));

        $body = $response->getContent();
        $this->assertGreaterThan(100, strlen($body), 'PDF body should be non-trivially large');
        $this->assertStringStartsWith('%PDF', $body, 'PDF body should start with %PDF magic bytes');
    }

    public function test_user_without_student_profile_gets_403_on_reports(): void
    {
        $user = User::factory()->create([
            'role' => 'student',
            'username' => 'orphan-'.uniqid(),
            'name' => 'Orphan User',
            'status' => 'active',
            'student_id' => null,
        ]);

        $response = $this->actingAs($user)->get('/student/reports/profile');
        $response->assertForbidden();

        $pdfResponse = $this->actingAs($user)->get('/student/reports/profile.pdf');
        $pdfResponse->assertForbidden();
    }

    public function test_admin_and_teacher_cannot_access_student_reports(): void
    {
        // Admin
        $admin = User::factory()->create([
            'role' => 'admin',
            'username' => 'admin-'.uniqid(),
            'name' => 'Admin',
            'status' => 'active',
        ]);
        $this->actingAs($admin)->get('/student/reports/profile')->assertForbidden();
        $this->actingAs($admin)->get('/student/reports/profile.pdf')->assertForbidden();

        // Teacher
        $teacherUser = User::factory()->create([
            'role' => 'teacher',
            'username' => 'teacher-'.uniqid(),
            'name' => 'Teacher',
            'status' => 'active',
        ]);
        $this->actingAs($teacherUser)->get('/student/reports/profile')->assertForbidden();
        $this->actingAs($teacherUser)->get('/student/reports/profile.pdf')->assertForbidden();
    }

    private function createStudentUser(): User
    {
        $user = User::factory()->create([
            'role' => 'student',
            'username' => 'report-'.uniqid(),
            'name' => 'Report Test',
            'status' => 'active',
        ]);
        $student = Student::create([
            'student_number' => 'SID-RPT-'.uniqid(),
            'first_name' => 'Report',
            'last_name' => 'Tester',
            'email' => $user->email,
            'status' => 'active',
        ]);
        $user->forceFill(['student_id' => $student->id])->save();

        return $user->fresh();
    }

    private function createClass(): SchoolClass
    {
        $teacher = Teacher::firstOrFail();

        return SchoolClass::create([
            'class_name' => 'Report Class '.uniqid(),
            'subject' => 'Biology',
            'description' => 'For report test.',
            'teacher_id' => $teacher->id,
            'status' => 'active',
        ]);
    }
}
