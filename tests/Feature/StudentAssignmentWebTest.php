<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentAssignmentWebTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_student_sees_assignments_for_enrolled_classes(): void
    {
        $studentUser = $this->createStudentUser();
        $class = $this->createClass();
        $studentUser->student->classes()->attach($class->id, [
            'date_enrolled' => now(),
            'status' => 'enrolled',
        ]);

        $assignment = Assignment::create([
            'class_id' => $class->id,
            'title' => 'Essay on Photosynthesis',
            'description' => 'Write a 500-word essay.',
            'date_assigned' => now()->subWeek(),
            'deadline' => now()->addWeek(),
            'maximum_score' => 100,
            'status' => 'Active',
        ]);

        $response = $this->actingAs($studentUser)->get('/student/assignments');
        $response->assertOk();
        $response->assertSee('My Assignments', false);
        $response->assertSee('Essay on Photosynthesis', false);
        $response->assertSee('Not submitted', false);
    }

    public function test_student_does_not_see_other_students_submissions(): void
    {
        $studentUser = $this->createStudentUser();
        $otherUser = $this->createStudentUser();
        $class = $this->createClass();
        $studentUser->student->classes()->attach($class->id, [
            'date_enrolled' => now(),
            'status' => 'enrolled',
        ]);
        $otherUser->student->classes()->attach($class->id, [
            'date_enrolled' => now(),
            'status' => 'enrolled',
        ]);

        $assignment = Assignment::create([
            'class_id' => $class->id,
            'title' => 'Group Project',
            'description' => 'Team work.',
            'date_assigned' => now(),
            'deadline' => now()->addDays(7),
            'maximum_score' => 100,
            'status' => 'Active',
        ]);

        // Other student has a submission
        AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $otherUser->student->id,
            'status' => 'Submitted',
            'submitted_at' => now(),
            'remarks' => 'Confidential draft text - should not leak',
        ]);

        $response = $this->actingAs($studentUser)->get('/student/assignments');
        $response->assertOk();
        $response->assertDontSee('Confidential draft text', false);

        $showResponse = $this->actingAs($studentUser)->get("/student/assignments/{$assignment->id}");
        $showResponse->assertOk();
        $showResponse->assertDontSee('Confidential draft text', false);
    }

    public function test_student_sees_assignment_detail_on_show(): void
    {
        $studentUser = $this->createStudentUser();
        $class = $this->createClass();
        $studentUser->student->classes()->attach($class->id, [
            'date_enrolled' => now(),
            'status' => 'enrolled',
        ]);

        $assignment = Assignment::create([
            'class_id' => $class->id,
            'title' => 'Math Quiz',
            'description' => 'Solve problems 1 to 10.',
            'date_assigned' => now()->subDay(),
            'deadline' => now()->addDays(3),
            'maximum_score' => 50,
            'status' => 'Active',
        ]);

        $response = $this->actingAs($studentUser)->get("/student/assignments/{$assignment->id}");
        $response->assertOk();
        $response->assertSee('Math Quiz', false);
        $response->assertSee('Solve problems 1 to 10.', false);
        $response->assertSee("You haven't submitted", false);
    }

    public function test_student_can_submit_assignment_and_record_is_created(): void
    {
        $studentUser = $this->createStudentUser();
        $class = $this->createClass();
        $studentUser->student->classes()->attach($class->id, [
            'date_enrolled' => now(),
            'status' => 'enrolled',
        ]);

        $assignment = Assignment::create([
            'class_id' => $class->id,
            'title' => 'Lab Report',
            'description' => 'Write a lab report.',
            'date_assigned' => now()->subDay(),
            'deadline' => now()->addDays(7),
            'maximum_score' => 100,
            'status' => 'Active',
        ]);

        $response = $this->actingAs($studentUser)->post(
            "/student/assignments/{$assignment->id}/submit",
            [
                'attachment_link' => 'https://example.com/my-report.pdf',
                'remarks' => 'My first submission',
            ]
        );

        $response->assertRedirect(route('student.assignments.show', $assignment->id));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('assignment_submissions', [
            'assignment_id' => $assignment->id,
            'student_id' => $studentUser->student->id,
            'status' => 'Submitted',
            'attachment_link' => 'https://example.com/my-report.pdf',
            'remarks' => 'My first submission',
        ]);
    }

    public function test_student_gets_403_for_assignment_not_in_enrolled_class(): void
    {
        $studentUser = $this->createStudentUser();
        $otherClass = $this->createClass();
        // No attachment — not enrolled

        $assignment = Assignment::create([
            'class_id' => $otherClass->id,
            'title' => 'Mystery Assignment',
            'description' => 'Should not be visible.',
            'date_assigned' => now(),
            'deadline' => now()->addDays(7),
            'maximum_score' => 100,
            'status' => 'Active',
        ]);

        $response = $this->actingAs($studentUser)->get("/student/assignments/{$assignment->id}");
        $response->assertForbidden();

        $submitResponse = $this->actingAs($studentUser)->post(
            "/student/assignments/{$assignment->id}/submit",
            ['remarks' => 'Sneaky']
        );
        $submitResponse->assertForbidden();
    }

    private function createStudentUser(): User
    {
        $user = User::factory()->create([
            'role' => 'student',
            'username' => 'assign-test-'.uniqid(),
            'name' => 'Assign Test',
            'status' => 'active',
        ]);
        $student = Student::create([
            'student_number' => 'SID-ASSIGN-'.uniqid(),
            'first_name' => 'Assign',
            'last_name' => 'Test',
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
            'class_name' => 'Assign Class '.uniqid(),
            'subject' => 'Biology',
            'description' => 'For assignment test.',
            'teacher_id' => $teacher->id,
            'status' => 'active',
        ]);
    }
}
