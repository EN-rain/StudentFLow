<?php

namespace Tests\Feature;

use App\Models\GradeCategory;
use App\Models\GradeItem;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentGrade;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentGradeWebTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_student_sees_enrolled_classes_with_final_grades_on_index(): void
    {
        $studentUser = $this->createStudentUser();
        $class = $this->createClass();
        $studentUser->student->classes()->attach($class->id, [
            'date_enrolled' => now(),
            'status' => 'enrolled',
        ]);

        // Create one category with one item: max 100, score 85 -> 85% ratio
        // weight 100 -> contributes 85 to final
        $cat = GradeCategory::create([
            'class_id' => $class->id,
            'category_name' => 'Major Exams',
            'percentage_weight' => 100,
        ]);
        $item = GradeItem::create([
            'class_id' => $class->id,
            'category_id' => $cat->id,
            'title' => 'Midterm',
            'maximum_score' => 100,
        ]);
        StudentGrade::create([
            'grade_item_id' => $item->id,
            'student_id' => $studentUser->student->id,
            'score' => 85,
            'remarks' => 'Good work',
        ]);

        $response = $this->actingAs($studentUser)->get('/student/grades');
        $response->assertOk();
        $response->assertSee('My Grades', false);
        $response->assertSee($class->class_name, false);
        $response->assertSee('85.00', false);
        // Letter grade B for 85
        $response->assertSee('B', false);
    }

    public function test_student_does_not_see_other_students_grades(): void
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

        $cat = GradeCategory::create([
            'class_id' => $class->id,
            'category_name' => 'Quizzes',
            'percentage_weight' => 100,
        ]);
        $item = GradeItem::create([
            'class_id' => $class->id,
            'category_id' => $cat->id,
            'title' => 'Quiz 1',
            'maximum_score' => 50,
        ]);
        // Other student has a score
        StudentGrade::create([
            'grade_item_id' => $item->id,
            'student_id' => $otherUser->student->id,
            'score' => 49,
            'remarks' => 'Should not leak',
        ]);
        // This student has no score

        // Visit show page
        $response = $this->actingAs($studentUser)->get("/student/grades/{$class->id}");
        $response->assertOk();
        $response->assertDontSee('Should not leak', false);
        // The other student's 49 score should not appear in either page
        $indexResponse = $this->actingAs($studentUser)->get('/student/grades');
        $indexResponse->assertDontSee('Should not leak', false);
    }

    public function test_student_sees_per_category_breakdown_on_show(): void
    {
        $studentUser = $this->createStudentUser();
        $class = $this->createClass();
        $studentUser->student->classes()->attach($class->id, [
            'date_enrolled' => now(),
            'status' => 'enrolled',
        ]);

        $cat1 = GradeCategory::create([
            'class_id' => $class->id,
            'category_name' => 'Quizzes',
            'percentage_weight' => 40,
        ]);
        $item1 = GradeItem::create([
            'class_id' => $class->id,
            'category_id' => $cat1->id,
            'title' => 'Quiz 1',
            'maximum_score' => 100,
        ]);
        StudentGrade::create([
            'grade_item_id' => $item1->id,
            'student_id' => $studentUser->student->id,
            'score' => 80,
        ]);

        $cat2 = GradeCategory::create([
            'class_id' => $class->id,
            'category_name' => 'Major Exams',
            'percentage_weight' => 60,
        ]);
        $item2 = GradeItem::create([
            'class_id' => $class->id,
            'category_id' => $cat2->id,
            'title' => 'Final Exam',
            'maximum_score' => 100,
        ]);
        StudentGrade::create([
            'grade_item_id' => $item2->id,
            'student_id' => $studentUser->student->id,
            'score' => 90,
        ]);

        // cat1 avg = 0.8 -> 0.8 * 40 = 32
        // cat2 avg = 0.9 -> 0.9 * 60 = 54
        // final = 86.00

        $response = $this->actingAs($studentUser)->get("/student/grades/{$class->id}");
        $response->assertOk();
        $response->assertSee('Quizzes', false);
        $response->assertSee('Major Exams', false);
        $response->assertSee('Quiz 1', false);
        $response->assertSee('Final Exam', false);
        $response->assertSee('86.00', false);
    }

    public function test_student_gets_403_for_non_enrolled_class_show(): void
    {
        $studentUser = $this->createStudentUser();
        $otherClass = $this->createClass();
        // No attachment — student is not enrolled

        $response = $this->actingAs($studentUser)->get("/student/grades/{$otherClass->id}");
        $response->assertForbidden();
    }

    private function createStudentUser(): User
    {
        $user = User::factory()->create([
            'role' => 'student',
            'username' => 'grade-test-'.uniqid(),
            'name' => 'Grade Test',
            'status' => 'active',
        ]);
        $student = Student::create([
            'student_number' => 'SID-GRADE-'.uniqid(),
            'first_name' => 'Grade',
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
            'class_name' => 'Grade Class '.uniqid(),
            'subject' => 'Mathematics',
            'description' => 'For grade test.',
            'teacher_id' => $teacher->id,
            'status' => 'active',
        ]);
    }
}
