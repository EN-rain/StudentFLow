<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentAnnouncementWebTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_student_sees_only_announcements_for_enrolled_classes_or_global(): void
    {
        $studentUser = $this->createStudentUser();
        $class = $this->createClass();
        $studentUser->student->classes()->attach($class->id, [
            'date_enrolled' => now(),
            'status' => 'enrolled',
        ]);

        $otherClass = $this->createClass();

        // Targeted at the student's class — should appear
        $targeted = Announcement::create([
            'teacher_id' => Teacher::firstOrFail()->id,
            'class_id' => $class->id,
            'title' => 'Class Update',
            'message' => 'Tomorrow we have a special guest.',
            'priority' => 'Important',
            'publish_date' => now()->subDay(),
            'expiration_date' => now()->addDays(7),
        ]);

        // Global announcement — should appear
        $global = Announcement::create([
            'teacher_id' => Teacher::firstOrFail()->id,
            'class_id' => null,
            'title' => 'School-wide Notice',
            'message' => 'No classes on Friday.',
            'priority' => 'Normal',
            'publish_date' => now()->subDay(),
            'expiration_date' => null,
        ]);

        // Targeted at a class the student is NOT enrolled in — should NOT appear
        Announcement::create([
            'teacher_id' => Teacher::firstOrFail()->id,
            'class_id' => $otherClass->id,
            'title' => 'Confidential Class Notice',
            'message' => 'Should not leak to other students.',
            'priority' => 'Urgent',
            'publish_date' => now()->subDay(),
            'expiration_date' => now()->addDays(7),
        ]);

        $response = $this->actingAs($studentUser)->get('/student/announcements');
        $response->assertOk();
        $response->assertSee('My Announcements', false);
        $response->assertSee('Class Update', false);
        $response->assertSee('School-wide Notice', false);
        $response->assertDontSee('Confidential Class Notice', false);
        $response->assertDontSee('Should not leak to other students', false);
    }

    public function test_student_does_not_see_expired_or_future_announcements(): void
    {
        $studentUser = $this->createStudentUser();
        $class = $this->createClass();
        $studentUser->student->classes()->attach($class->id, [
            'date_enrolled' => now(),
            'status' => 'enrolled',
        ]);

        // Expired yesterday — should NOT appear
        Announcement::create([
            'teacher_id' => Teacher::firstOrFail()->id,
            'class_id' => $class->id,
            'title' => 'Already Expired Notice',
            'message' => 'This is in the past.',
            'priority' => 'Normal',
            'publish_date' => now()->subDays(10),
            'expiration_date' => now()->subDay(),
        ]);

        // Publish date in the future — should NOT appear
        Announcement::create([
            'teacher_id' => Teacher::firstOrFail()->id,
            'class_id' => $class->id,
            'title' => 'Future Scheduled Notice',
            'message' => 'Coming soon.',
            'priority' => 'Normal',
            'publish_date' => now()->addDays(7),
            'expiration_date' => now()->addDays(30),
        ]);

        $response = $this->actingAs($studentUser)->get('/student/announcements');
        $response->assertOk();
        $response->assertDontSee('Already Expired Notice', false);
        $response->assertDontSee('Future Scheduled Notice', false);
    }

    public function test_student_can_view_targeted_announcement_detail(): void
    {
        $studentUser = $this->createStudentUser();
        $class = $this->createClass();
        $studentUser->student->classes()->attach($class->id, [
            'date_enrolled' => now(),
            'status' => 'enrolled',
        ]);

        $announcement = Announcement::create([
            'teacher_id' => Teacher::firstOrFail()->id,
            'class_id' => $class->id,
            'title' => 'Detail Page Test',
            'message' => 'Full message content for the show page.',
            'priority' => 'Important',
            'publish_date' => now()->subDay(),
            'expiration_date' => now()->addDays(7),
        ]);

        $response = $this->actingAs($studentUser)->get("/student/announcements/{$announcement->id}");
        $response->assertOk();
        $response->assertSee('Detail Page Test', false);
        $response->assertSee('Full message content', false);
        $response->assertSee('Important', false);
    }

    public function test_non_targeted_student_gets_403_on_show(): void
    {
        $studentUser = $this->createStudentUser();
        $otherClass = $this->createClass();
        // Student is NOT enrolled in $otherClass.

        $announcement = Announcement::create([
            'teacher_id' => Teacher::firstOrFail()->id,
            'class_id' => $otherClass->id,
            'title' => 'Other Class Announcement',
            'message' => 'Should not be visible.',
            'priority' => 'Urgent',
            'publish_date' => now()->subDay(),
            'expiration_date' => now()->addDays(7),
        ]);

        $response = $this->actingAs($studentUser)->get("/student/announcements/{$announcement->id}");
        $response->assertForbidden();
    }

    public function test_student_can_view_global_announcement_detail(): void
    {
        $studentUser = $this->createStudentUser();
        $class = $this->createClass();
        $studentUser->student->classes()->attach($class->id, [
            'date_enrolled' => now(),
            'status' => 'enrolled',
        ]);

        $global = Announcement::create([
            'teacher_id' => Teacher::firstOrFail()->id,
            'class_id' => null,
            'title' => 'School-wide Detail',
            'message' => 'Everyone can see this.',
            'priority' => 'Normal',
            'publish_date' => now()->subDay(),
            'expiration_date' => null,
        ]);

        $response = $this->actingAs($studentUser)->get("/student/announcements/{$global->id}");
        $response->assertOk();
        $response->assertSee('School-wide Detail', false);
        $response->assertSee('Everyone can see this', false);
        $response->assertSee('All Classes', false);
    }

    private function createStudentUser(): User
    {
        $user = User::factory()->create([
            'role' => 'student',
            'username' => 'announce-test-'.uniqid(),
            'name' => 'Announce Test',
            'status' => 'active',
        ]);
        $student = Student::create([
            'student_number' => 'SID-ANN-'.uniqid(),
            'first_name' => 'Announce',
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
            'class_name' => 'Announce Class '.uniqid(),
            'subject' => 'History',
            'description' => 'For announcement test.',
            'teacher_id' => $teacher->id,
            'status' => 'active',
        ]);
    }
}
