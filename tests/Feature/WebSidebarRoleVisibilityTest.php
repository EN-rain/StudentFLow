<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebSidebarRoleVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_admin_sidebar_shows_admin_and_teacher_links_not_student(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();

        $response = $this->actingAs($admin)->get('/dashboard');
        $response->assertOk();

        // Admin-only sidebar section header
        $response->assertSee('Administration', false);
        // Admin-only sidebar link labels
        $response->assertSee('Activity Logs', false);
        $response->assertSee('Teachers', false);

        // Shared teacher links (also visible to admin)
        $response->assertSee('Classes', false);

        // Student-only sidebar section header — must NOT appear for admin
        $response->assertDontSee('My Portal', false);
    }

    public function test_teacher_sidebar_shows_teacher_links_not_admin_nor_student(): void
    {
        $teacher = User::where('role', 'teacher')->firstOrFail();

        $response = $this->actingAs($teacher)->get('/dashboard');
        $response->assertOk();

        // Teacher sidebar links (shared with admin)
        $response->assertSee('Classes', false);
        $response->assertSee('Attendance', false);
        $response->assertSee('Grades', false);
        $response->assertSee('Assignments', false);
        $response->assertSee('Exams', false);

        // Admin-only sidebar section header must NOT appear for teacher
        $response->assertDontSee('Administration', false);
        $response->assertDontSee('Activity Logs', false);

        // Student-only sidebar section header must NOT appear for teacher
        $response->assertDontSee('My Portal', false);
    }

    public function test_student_sidebar_section_header_renders_when_student_routes_missing(): void
    {
        // At this point (Phase 1) no student.* routes are wired yet, so
        // Route::has() guards hide all individual nav links.
        // We assert the section header "My Portal" is present to confirm
        // the student section gate works; individual links will be tested
        // in Phase 2 when routes exist.
        $studentUser = User::factory()->create([
            'role' => 'student',
            'username' => 'sidebar-test-student',
            'name' => 'Sidebar Test Student',
            'status' => 'active',
        ]);
        $student = Student::create([
            'student_number' => 'SID-SIDEBAR',
            'first_name' => 'Sidebar',
            'last_name' => 'Test',
            'email' => $studentUser->email,
            'status' => 'active',
        ]);
        $studentUser->forceFill(['student_id' => $student->id])->save();

        $response = $this->actingAs($studentUser)->get('/dashboard');
        $response->assertOk();

        // Student section header is visible (no Route::has guard on it)
        $response->assertSee('My Portal', false);

        // Change Password is shared — must appear for all roles
        $response->assertSee('Change Password', false);

        // Admin-only sidebar labels must NOT appear for student
        $response->assertDontSee('Activity Logs', false);
        $response->assertDontSee('Administration', false);
        $response->assertDontSee('Settings', false);

        // Teacher-only sidebar link label "Teachers" must NOT appear for student
        $response->assertDontSee('Teachers', false);
    }
}
