<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentClassWebTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_student_sees_enrolled_class_on_index(): void
    {
        $studentUser = $this->createStudentUser();
        $class = $this->createClassWithStudent($studentUser->student, 'enrolled');

        $response = $this->actingAs($studentUser)->get('/student/classes');
        $response->assertOk();
        $response->assertSee('My Classes', false);
        $response->assertSee($class->class_name, false);
    }

    public function test_student_gets_403_on_non_enrolled_class_show(): void
    {
        $studentUser = $this->createStudentUser();
        $otherClass = $this->createClassWithStudent(null, 'enrolled', 'Unrelated Class');

        $response = $this->actingAs($studentUser)->get("/student/classes/{$otherClass->id}");
        $response->assertForbidden();
    }

    public function test_student_can_view_enrolled_class_show(): void
    {
        $studentUser = $this->createStudentUser();
        $class = $this->createClassWithStudent($studentUser->student, 'enrolled');

        $response = $this->actingAs($studentUser)->get("/student/classes/{$class->id}");
        $response->assertOk();
        $response->assertSee($class->class_name, false);
    }

    public function test_dropped_enrollment_returns_403(): void
    {
        $studentUser = $this->createStudentUser();
        $droppedClass = $this->createClassWithStudent($studentUser->student, 'dropped');

        $response = $this->actingAs($studentUser)->get("/student/classes/{$droppedClass->id}");
        $response->assertForbidden();
    }

    private function createStudentUser(): User
    {
        $user = User::factory()->create([
            'role' => 'student',
            'username' => 'class-test-'.uniqid(),
            'name' => 'Class Test',
            'status' => 'active',
        ]);
        $student = Student::create([
            'student_number' => 'SID-CLASS-'.uniqid(),
            'first_name' => 'Class',
            'last_name' => 'Test',
            'email' => $user->email,
            'status' => 'active',
        ]);
        $user->forceFill(['student_id' => $student->id])->save();

        return $user->fresh();
    }

    private function createClassWithStudent(?Student $student, string $pivotStatus, ?string $className = null): SchoolClass
    {
        $teacher = Teacher::firstOrFail();
        $class = SchoolClass::create([
            'class_name' => $className ?? 'Test Class '.uniqid(),
            'subject' => 'Mathematics',
            'description' => 'A test class for student-class-web test.',
            'schedule' => 'Mon/Wed 9-10am',
            'teacher_id' => $teacher->id,
            'status' => 'active',
        ]);
        if ($student) {
            $student->classes()->attach($class->id, [
                'date_enrolled' => now(),
                'status' => $pivotStatus,
            ]);
        }

        return $class->fresh();
    }
}
