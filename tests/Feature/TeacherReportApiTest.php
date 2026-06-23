<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherReportApiTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_admin_can_download_class_attendance_pdf(): void
    {
        $admin = $this->makeAdmin();
        $class = $this->makeClass();

        $response = $this->actingAs($admin)->get("/api/reports/attendance/pdf?class_id={$class->id}");

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));

        $body = $response->getContent();
        $this->assertGreaterThan(100, strlen($body));
        $this->assertStringStartsWith('%PDF', $body);
    }

    public function test_teacher_can_download_own_class_attendance_pdf(): void
    {
        $teacherUser = $this->makeTeacher();
        $class = $this->makeClass($teacherUser->teacher);

        $response = $this->actingAs($teacherUser)->get("/api/reports/attendance/pdf?class_id={$class->id}");

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_student_gets_403_on_pdf(): void
    {
        $studentUser = $this->makeStudent();
        $class = $this->makeClass();

        $response = $this->actingAs($studentUser)->get("/api/reports/attendance/pdf?class_id={$class->id}");
        $response->assertForbidden();
    }

    public function test_admin_can_download_class_csv(): void
    {
        $admin = $this->makeAdmin();
        $class = $this->makeClass();

        $response = $this->actingAs($admin)->get("/api/reports/attendance/csv?class_id={$class->id}");

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertNotEmpty($response->streamedContent());
    }

    public function test_teacher_can_download_own_class_csv(): void
    {
        $teacherUser = $this->makeTeacher();
        $class = $this->makeClass($teacherUser->teacher);

        $response = $this->actingAs($teacherUser)->get("/api/reports/attendance/csv?class_id={$class->id}");

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
    }

    public function test_student_gets_403_on_csv(): void
    {
        $studentUser = $this->makeStudent();
        $class = $this->makeClass();

        $response = $this->actingAs($studentUser)->get("/api/reports/attendance/csv?class_id={$class->id}");
        $response->assertForbidden();
    }

    public function test_invalid_type_returns_404_for_pdf(): void
    {
        $admin = $this->makeAdmin();
        $response = $this->actingAs($admin)->get('/api/reports/garbage-type/pdf');
        $response->assertNotFound();
    }

    public function test_unauthenticated_gets_401(): void
    {
        $response = $this->getJson('/api/reports/attendance/pdf?class_id=1');
        $response->assertUnauthorized();
    }

    // ----- helpers -----

    private function makeAdmin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'username' => 'admin-'.uniqid(),
            'name' => 'Admin',
            'status' => 'active',
        ]);
    }

    private function makeTeacher(): User
    {
        $user = User::factory()->create([
            'role' => 'teacher',
            'username' => 'teacher-'.uniqid(),
            'name' => 'Teacher',
            'status' => 'active',
        ]);
        Teacher::create([
            'user_id' => $user->id,
            'employee_number' => 'T-API-'.uniqid(),
            'first_name' => 'API',
            'last_name' => 'Teacher',
        ]);

        return $user->fresh();
    }

    private function makeStudent(): User
    {
        $user = User::factory()->create([
            'role' => 'student',
            'username' => 'student-'.uniqid(),
            'name' => 'Student',
            'status' => 'active',
        ]);
        $student = Student::create([
            'student_number' => 'SID-API-'.uniqid(),
            'first_name' => 'API',
            'last_name' => 'Tester',
            'email' => $user->email,
            'status' => 'active',
        ]);
        $user->forceFill(['student_id' => $student->id])->save();

        return $user->fresh();
    }

    private function makeClass(?Teacher $teacher = null): SchoolClass
    {
        $teacher = $teacher ?? Teacher::firstOrFail();

        return SchoolClass::create([
            'class_name' => 'API Class '.uniqid(),
            'subject' => 'Chemistry',
            'description' => 'For API report test.',
            'teacher_id' => $teacher->id,
            'grade_level' => '10',
            'school_year' => '2025-2026',
            'semester' => '1',
            'status' => 'active',
        ]);
    }
}
