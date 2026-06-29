<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentPlaceholderRouteTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_student_can_open_student_dashboard_route(): void
    {
        $studentUser = User::factory()->create([
            'role' => 'student',
            'username' => 'placeholder-test',
            'name' => 'Placeholder Test',
            'status' => 'active',
        ]);
        $student = Student::create([
            'student_number' => 'SID-PLACEHOLDER',
            'first_name' => 'Placeholder',
            'last_name' => 'Test',
            'email' => $studentUser->email,
            'status' => 'active',
        ]);
        $studentUser->forceFill(['student_id' => $student->id])->save();

        $response = $this->actingAs($studentUser)->get('/student');
        $response->assertOk();
        $response->assertSee('Student Dashboard', false);
        $response->assertSee('Enrolled Classes', false);
    }

    public function test_admin_cannot_view_student_route(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();

        $response = $this->actingAs($admin)->get('/student');
        $response->assertForbidden();
    }

    public function test_teacher_cannot_view_student_route(): void
    {
        $teacher = User::where('role', 'teacher')->firstOrFail();

        $response = $this->actingAs($teacher)->get('/student');
        $response->assertForbidden();
    }
}
