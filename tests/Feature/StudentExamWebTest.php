<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class StudentExamWebTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_student_sees_published_exams_for_enrolled_classes(): void
    {
        $studentUser = $this->createStudentUser();
        $class = $this->createClass();
        $studentUser->student->classes()->attach($class->id, [
            'date_enrolled' => now(),
            'status' => 'enrolled',
        ]);

        $exam = Exam::create([
            'class_id' => $class->id,
            'teacher_id' => Teacher::firstOrFail()->id,
            'title' => 'Midterm Exam',
            'instructions' => 'Answer all questions.',
            'available_from' => now()->subDay(),
            'due_at' => now()->addDays(3),
            'duration_minutes' => 60,
            'maximum_score' => 100,
            'status' => 'published',
        ]);

        $response = $this->actingAs($studentUser)->get('/student/exams');
        $response->assertOk();
        $response->assertSee('My Exams', false);
        $response->assertSee('Midterm Exam', false);
        $response->assertSee($class->class_name, false);
    }

    public function test_student_does_not_see_exams_for_non_enrolled_or_unpublished_exams(): void
    {
        $studentUser = $this->createStudentUser();
        $class = $this->createClass();
        $studentUser->student->classes()->attach($class->id, [
            'date_enrolled' => now(),
            'status' => 'enrolled',
        ]);

        // Unpublished exam (draft) — should NOT show
        Exam::create([
            'class_id' => $class->id,
            'teacher_id' => Teacher::firstOrFail()->id,
            'title' => 'Draft Exam Should Not Appear',
            'available_from' => now()->subDay(),
            'due_at' => now()->addDays(3),
            'duration_minutes' => 60,
            'maximum_score' => 100,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($studentUser)->get('/student/exams');
        $response->assertOk();
        $response->assertDontSee('Draft Exam Should Not Appear', false);

        // Exam in a class the student is NOT enrolled in — should NOT show
        $otherClass = $this->createClass();
        Exam::create([
            'class_id' => $otherClass->id,
            'teacher_id' => Teacher::firstOrFail()->id,
            'title' => 'Other Class Exam Hidden',
            'available_from' => now()->subDay(),
            'due_at' => now()->addDays(3),
            'duration_minutes' => 30,
            'maximum_score' => 50,
            'status' => 'published',
        ]);
        $response2 = $this->actingAs($studentUser)->get('/student/exams');
        $response2->assertOk();
        $response2->assertDontSee('Other Class Exam Hidden', false);
    }

    public function test_start_creates_exam_attempt_row_and_redirects_to_magic_url(): void
    {
        $studentUser = $this->createStudentUser();
        $class = $this->createClass();
        $studentUser->student->classes()->attach($class->id, [
            'date_enrolled' => now(),
            'status' => 'enrolled',
        ]);

        $exam = Exam::create([
            'class_id' => $class->id,
            'teacher_id' => Teacher::firstOrFail()->id,
            'title' => 'Quiz 1',
            'available_from' => now()->subDay(),
            'due_at' => now()->addDays(7),
            'duration_minutes' => 30,
            'maximum_score' => 50,
            'status' => 'published',
        ]);

        // Pre-condition: no attempt exists yet.
        $this->assertDatabaseMissing('exam_attempts', [
            'exam_id' => $exam->id,
            'student_id' => $studentUser->student->id,
        ]);

        $response = $this->actingAs($studentUser)->get("/student/exams/{$exam->id}/start");
        $response->assertRedirect();
        $location = $response->headers->get('Location');
        $this->assertStringContainsString('/exam/magic/', $location);

        // Post-condition: attempt row created with magic_token and status=in_progress.
        $attempt = ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $studentUser->student->id)
            ->firstOrFail();
        $this->assertNotEmpty($attempt->magic_token);
        $this->assertSame('in_progress', $attempt->status);
        $this->assertNotNull($attempt->started_at);

        // The magic URL token must match the attempt's magic_token.
        $this->assertStringEndsWith($attempt->magic_token, $location);
    }

    public function test_start_resumes_existing_attempt_without_creating_duplicate(): void
    {
        $studentUser = $this->createStudentUser();
        $class = $this->createClass();
        $studentUser->student->classes()->attach($class->id, [
            'date_enrolled' => now(),
            'status' => 'enrolled',
        ]);

        $exam = Exam::create([
            'class_id' => $class->id,
            'teacher_id' => Teacher::firstOrFail()->id,
            'title' => 'Resumable Quiz',
            'available_from' => now()->subDay(),
            'due_at' => now()->addDays(7),
            'duration_minutes' => 30,
            'maximum_score' => 50,
            'status' => 'published',
        ]);

        $existing = ExamAttempt::create([
            'exam_id' => $exam->id,
            'student_id' => $studentUser->student->id,
            'magic_token' => Str::random(64),
            'status' => 'assigned',
        ]);

        $response = $this->actingAs($studentUser)->get("/student/exams/{$exam->id}/start");
        $response->assertRedirect();
        $this->assertDatabaseHas('exam_attempts', [
            'exam_id' => $exam->id,
            'student_id' => $studentUser->student->id,
            'status' => 'in_progress',
        ]);
        // Confirm only one attempt exists for this student+exam (no duplicate).
        $this->assertSame(1, ExamAttempt::where('exam_id', $exam->id)->where('student_id', $studentUser->student->id)->count());
        $existing->refresh();
        $this->assertSame('in_progress', $existing->status);
        $this->assertNotNull($existing->started_at);
    }

    public function test_student_gets_403_starting_exam_for_non_enrolled_class(): void
    {
        $studentUser = $this->createStudentUser();
        $otherClass = $this->createClass();
        // No enrollment.

        $exam = Exam::create([
            'class_id' => $otherClass->id,
            'teacher_id' => Teacher::firstOrFail()->id,
            'title' => 'Forbidden Exam',
            'available_from' => now()->subDay(),
            'due_at' => now()->addDays(7),
            'duration_minutes' => 60,
            'maximum_score' => 100,
            'status' => 'published',
        ]);

        $response = $this->actingAs($studentUser)->get("/student/exams/{$exam->id}/start");
        $response->assertForbidden();
        $this->assertDatabaseMissing('exam_attempts', [
            'exam_id' => $exam->id,
            'student_id' => $studentUser->student->id,
        ]);
    }

    private function createStudentUser(): User
    {
        $user = User::factory()->create([
            'role' => 'student',
            'username' => 'exam-test-'.uniqid(),
            'name' => 'Exam Test',
            'status' => 'active',
        ]);
        $student = Student::create([
            'student_number' => 'SID-EXAM-'.uniqid(),
            'first_name' => 'Exam',
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
            'class_name' => 'Exam Class '.uniqid(),
            'subject' => 'Chemistry',
            'description' => 'For exam test.',
            'teacher_id' => $teacher->id,
            'status' => 'active',
        ]);
    }
}
