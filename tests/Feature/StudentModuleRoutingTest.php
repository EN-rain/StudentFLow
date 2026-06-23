<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Exam;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentModuleRoutingTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_all_six_student_modules_render_200_for_an_enrolled_student(): void
    {
        $studentUser = $this->createStudentUser();
        $class = $this->createClass();
        $studentUser->student->classes()->attach($class->id, [
            'date_enrolled' => now(),
            'status' => 'enrolled',
        ]);

        // Seed one representative record per module so show routes can resolve.
        $assignment = Assignment::create([
            'class_id' => $class->id,
            'title' => 'Routing Test Assignment',
            'description' => 'For routing test.',
            'date_assigned' => now()->subDay(),
            'deadline' => now()->addDays(7),
            'maximum_score' => 100,
            'status' => 'Active',
        ]);
        $exam = Exam::create([
            'class_id' => $class->id,
            'teacher_id' => Teacher::firstOrFail()->id,
            'title' => 'Routing Test Exam',
            'available_from' => now()->subDay(),
            'due_at' => now()->addDays(7),
            'duration_minutes' => 30,
            'maximum_score' => 50,
            'status' => 'published',
        ]);
        $announcement = Announcement::create([
            'class_id' => $class->id,
            'teacher_id' => Teacher::firstOrFail()->id,
            'title' => 'Routing Test Announcement',
            'message' => 'For routing test.',
            'publish_date' => now(),
            'expiration_date' => now()->addDays(7),
        ]);

        $endpoints = [
            '/student/classes',
            "/student/classes/{$class->id}",
            '/student/attendance',
            '/student/grades',
            "/student/grades/{$class->id}",
            '/student/assignments',
            "/student/assignments/{$assignment->id}",
            '/student/exams',
            '/student/announcements',
            "/student/announcements/{$announcement->id}",
        ];

        $failed = [];
        foreach ($endpoints as $url) {
            $response = $this->actingAs($studentUser)->get($url);
            if ($response->status() !== 200) {
                $failed[] = "{$url} -> {$response->status()}";
            }
        }

        $this->assertSame([], $failed, 'Some student module endpoints failed: '.implode(', ', $failed));
    }

    public function test_student_sidebar_renders_links_to_all_six_modules(): void
    {
        $studentUser = $this->createStudentUser();

        $response = $this->actingAs($studentUser)->get('/student');
        $response->assertOk();

        $content = $response->getContent();

        $expectedHrefs = [
            '/student/classes',
            '/student/attendance',
            '/student/grades',
            '/student/assignments',
            '/student/exams',
            '/student/announcements',
        ];

        $missing = [];
        foreach ($expectedHrefs as $href) {
            if (! str_contains($content, $href)) {
                $missing[] = $href;
            }
        }

        $this->assertSame([], $missing, 'Sidebar missing links: '.implode(', ', $missing));
    }

    public function test_placeholder_route_still_works_for_student(): void
    {
        $studentUser = $this->createStudentUser();
        $response = $this->actingAs($studentUser)->get('/student');
        $response->assertOk();
    }

    private function createStudentUser(): User
    {
        $user = User::factory()->create([
            'role' => 'student',
            'username' => 'routing-test-'.uniqid(),
            'name' => 'Routing Test',
            'status' => 'active',
        ]);
        $student = Student::create([
            'student_number' => 'SID-ROUTING-'.uniqid(),
            'first_name' => 'Routing',
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
            'class_name' => 'Routing Class '.uniqid(),
            'subject' => 'Math',
            'description' => 'For routing test.',
            'teacher_id' => $teacher->id,
            'status' => 'active',
        ]);
    }
}
