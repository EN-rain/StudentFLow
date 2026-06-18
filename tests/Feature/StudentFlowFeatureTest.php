<?php

namespace Tests\Feature;

use App\Mail\ClassAnnouncementMail;
use App\Models\ActivityLog;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\ClassJoinRequest;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\GradeCategory;
use App\Models\GradeItem;
use App\Models\SchoolClass;
use App\Models\SchoolSetting;
use App\Models\Student;
use App\Models\StudentGrade;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StudentFlowFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_starter_dataset_matches_second_semester_programming_context(): void
    {
        $this->assertSame(1, User::where('role', 'admin')->count());
        $this->assertSame(5, User::where('role', 'teacher')->count());
        $this->assertSame(10, User::where('role', 'student')->count());
        $this->assertSame(5, Teacher::count());
        $this->assertSame(10, Student::count());
        $this->assertSame(5, SchoolClass::count());
        $this->assertSame(5, SchoolClass::distinct('teacher_id')->count('teacher_id'));
        $this->assertSame(0, SchoolClass::where('semester', '!=', 'Second Semester')->count());

        $this->assertSame([
            'Introduction to Programming with Python',
            'Mobile Application Development',
            'Object-Oriented Programming with Java',
            'Software Engineering and Testing',
            'Web Application Development',
        ], SchoolClass::orderBy('subject')->pluck('subject')->all());

        $this->assertSame(20, Exam::count());
        $this->assertSame(20, ExamAttempt::where('status', 'submitted')->count());
        $this->assertSame(20, ExamAttempt::where('status', 'assigned')->count());
        $this->assertSame(20, ExamAnswer::count());
    }

    public function test_root_redirects_to_dashboard(): void
    {
        $this->get('/')->assertRedirect('/dashboard');
    }

    public function test_login_disabled_account_and_password_reset_flow(): void
    {
        Notification::fake();
        $admin = User::where('username', 'admin')->first();
        $password = Str::password(24);
        $admin->forceFill(['password' => Hash::make($password)])->save();

        $this->post('/login', ['username' => 'admin', 'password' => $password])
            ->assertRedirect('/dashboard');

        $admin->update(['status' => 'disabled']);
        $this->post('/login', ['username' => 'admin', 'password' => $password])
            ->assertSessionHasErrors('username');
        $admin->update(['status' => 'active']);

        $this->post('/forgot-password', ['email' => $admin->email])
            ->assertSessionHas('status');
        Notification::assertSentTo($admin, ResetPassword::class);
    }

    public function test_admin_can_manage_teacher_settings_and_logs(): void
    {
        $admin = User::where('username', 'admin')->first();
        $response = $this->actingAs($admin)->post('/admin/teachers', [
            'name' => 'New Teacher',
            'email' => 'new.teacher@studentflow.local',
            'status' => 'active',
            'employee_number' => 'TCH-2026-099',
            'first_name' => 'New',
            'middle_name' => null,
            'last_name' => 'Teacher',
            'department' => 'Science',
            'contact_number' => '09000000000',
        ])->assertRedirect('/admin/teachers')->assertSessionHas('teacher_setup_url');

        $teacher = Teacher::where('employee_number', 'TCH-2026-099')->first();
        $this->assertNotNull($teacher);
        $this->assertTrue($teacher->user->hasPendingTeacherSetup());

        $setupUrl = $response->getSession()->get('teacher_setup_url');
        $parts = parse_url($setupUrl);
        $token = basename($parts['path'] ?? '');
        $teacherPassword = Str::password(24);

        $this->post('/teacher/setup', [
            'token' => $token,
            'email' => $teacher->user->email,
            'username' => 'new.teacher',
            'password' => $teacherPassword,
            'password_confirmation' => $teacherPassword,
        ])->assertRedirect('/login');

        $this->post('/logout');
        $this->post('/login', ['username' => 'new.teacher', 'password' => $teacherPassword])
            ->assertRedirect('/dashboard');

        $this->actingAs($admin)->patch("/admin/teachers/{$teacher->id}/status", ['status' => 'disabled'])
            ->assertRedirect();
        $this->assertSame('disabled', $teacher->fresh()->user->status);

        $this->actingAs($admin)->put('/admin/settings', [
            'settings' => ['school_name' => 'Updated School'],
        ])->assertRedirect();

        $this->assertSame('Updated School', SchoolSetting::where('setting_key', 'school_name')->first()->setting_value);
        $this->assertTrue(ActivityLog::where('action', 'teacher.created')->exists());
        $this->assertTrue(ActivityLog::where('action', 'setting.updated')->exists());
    }

    public function test_teacher_scoping_and_enrollment_management(): void
    {
        $john = User::where('username', 'john.reyes')->first();
        $angela = User::where('username', 'angela.cruz')->first();
        $johnClass = SchoolClass::where('class_name', 'BSIT 2A')->first();
        $angelaClass = SchoolClass::where('class_name', 'BSIT 1B')->first();
        $student = Student::where('student_number', '2026-0010')->first();

        Sanctum::actingAs($angela);
        $this->getJson("/api/classes/{$johnClass->id}")->assertForbidden();

        Sanctum::actingAs($john);
        $this->postJson("/api/classes/{$johnClass->id}/enrollments", [
            'student_id' => $student->id,
            'date_enrolled' => '2026-06-17',
        ])->assertCreated();
        $this->assertTrue($johnClass->students()->where('students.id', $student->id)->exists());

        $this->postJson("/api/classes/{$johnClass->id}/enrollments", [
            'student_id' => $student->id,
            'date_enrolled' => '2026-06-17',
        ])->assertUnprocessable();

        $this->deleteJson("/api/classes/{$johnClass->id}/enrollments/{$student->id}")->assertOk();
        $this->assertFalse($johnClass->students()->where('students.id', $student->id)->exists());

        $this->postJson("/api/classes/{$angelaClass->id}/enrollments", [
            'student_id' => $student->id,
        ])->assertForbidden();
    }

    public function test_grade_assignment_and_report_endpoints(): void
    {
        $admin = User::where('username', 'admin')->first();
        $class = SchoolClass::where('class_name', 'BSIT 2A')->first();
        $assignment = Assignment::where('title', 'Java Student Record Program')->first();
        $student = Student::where('student_number', '2026-0007')->first();

        Sanctum::actingAs($admin);
        $categoryResponse = $this->postJson("/api/classes/{$class->id}/grade-categories", [
            'category_name' => 'Recitation',
            'percentage_weight' => 0,
        ])->assertCreated();
        $categoryId = $categoryResponse->json('data.id');
        $this->assertTrue(GradeCategory::where('id', $categoryId)->exists());

        $this->postJson("/api/assignments/{$assignment->id}/submissions", [
            'submissions' => [[
                'student_id' => $student->id,
                'status' => 'Submitted',
                'score' => 42,
                'submitted_at' => '2026-06-23 10:00:00',
            ]],
        ])->assertOk();
        $this->assertSame('Submitted', AssignmentSubmission::where('assignment_id', $assignment->id)->where('student_id', $student->id)->first()->status);

        $this->postJson("/api/classes/{$class->id}/students/{$student->id}/student-grades", [
            'scores' => [[
                'grade_item_id' => 1,
                'score' => 25,
            ]],
        ])->assertUnprocessable();

        $this->getJson("/api/reports/missing-assignments?class_id={$class->id}")
            ->assertOk()
            ->assertJsonPath('data.type', 'missing-assignments');

        $this->getJson("/api/reports/student-profile?student_id={$student->id}")
            ->assertOk()
            ->assertJsonPath('data.student_number', '2026-0007');
    }

    public function test_class_announcement_emails_enrolled_students(): void
    {
        Mail::fake();

        $john = User::where('username', 'john.reyes')->first();
        $class = SchoolClass::where('class_name', 'BSIT 2A')->first();
        $enrolledCount = $class->students()->wherePivot('status', 'enrolled')->count();

        $this->actingAs($john)->post('/announcements', [
            'class_id' => $class->id,
            'title' => 'Bring your project draft',
            'message' => 'Please bring your Java project draft next meeting.',
            'priority' => 'Important',
            'publish_date' => '2026-06-18',
            'expiration_date' => null,
        ])->assertRedirect('/announcements');

        Mail::assertSent(ClassAnnouncementMail::class, $enrolledCount);
        Mail::assertSent(ClassAnnouncementMail::class, function (ClassAnnouncementMail $mail) use ($class) {
            return $mail->announcement->class_id === $class->id;
        });
    }

    public function test_student_social_login_student_api_exam_submission_and_teacher_audit(): void
    {
        $student = Student::where('student_number', '2026-0001')->first();
        $class = SchoolClass::where('class_name', 'BSIT 2A')->first();
        $john = User::where('username', 'john.reyes')->first();
        $gradeItem = GradeItem::where('class_id', $class->id)->first();

        $login = $this->postJson('/api/auth/google', [
            'id_token' => 'test-google:'.$student->email,
        ])->assertOk();

        $this->assertSame('student', $login->json('user.role'));
        $studentUser = User::where('student_id', $student->id)->first();
        $this->assertNotNull($studentUser);
        $this->assertNotNull($studentUser->google_id);

        Sanctum::actingAs($studentUser);
        $this->getJson('/api/student/dashboard')
            ->assertOk()
            ->assertJsonPath('data.student.student_number', '2026-0001');
        $this->getJson('/api/classes')->assertForbidden();

        Sanctum::actingAs($john);
        $examResponse = $this->postJson('/api/exams', [
            'class_id' => $class->id,
            'grade_item_id' => $gradeItem->id,
            'title' => 'OOP Quiz',
            'instructions' => 'Answer all questions.',
            'maximum_score' => 20,
            'status' => 'published',
            'questions' => [
                [
                    'prompt' => 'What keyword creates a class in Java?',
                    'type' => 'multiple_choice',
                    'choices' => ['class', 'def', 'function'],
                    'correct_answer' => 'class',
                    'points' => 20,
                ],
            ],
        ])->assertCreated();

        $examId = $examResponse->json('data.id');
        $attempt = ExamAttempt::where('exam_id', $examId)->where('student_id', $student->id)->first();
        $this->assertNotNull($attempt);

        $this->postJson("/api/exam/magic/{$attempt->magic_token}/start")->assertOk();

        $this->postJson("/api/exam/magic/{$attempt->magic_token}/submit", [
            'answers' => [[
                'question_id' => $examResponse->json('data.questions.0.id'),
                'answer' => 'class',
            ]],
        ])->assertOk()->assertJsonPath('score', 20);

        $this->assertSame('submitted', $attempt->fresh()->status);
        $this->assertEquals(20.0, (float) StudentGrade::where('grade_item_id', $gradeItem->id)->where('student_id', $student->id)->first()->score);

        Sanctum::actingAs($john);
        $this->getJson("/api/exams/{$examId}/audit")
            ->assertOk()
            ->assertJsonPath('data.stats.submitted', 1)
            ->assertJsonPath('data.students.0.google_email', $student->email);
    }

    public function test_student_can_login_with_seeded_username_and_password(): void
    {
        $login = $this->postJson('/api/auth/login', [
            'username' => 'aaronvillanueva001',
            'password' => 'StudentPass123!',
        ])->assertOk();

        $login->assertJsonPath('user.role', 'student')
            ->assertJsonPath('user.username', 'aaronvillanueva001');
        $this->assertNotEmpty($login->json('token'));
    }

    public function test_student_can_update_own_mobile_profile(): void
    {
        $studentUser = User::where('username', 'aaronvillanueva001')->firstOrFail();

        Sanctum::actingAs($studentUser);
        $this->patchJson('/api/student/profile', [
            'first_name' => 'Aaron',
            'last_name' => 'Villanueva',
            'email' => 'aaron.updated@studentflow.local',
            'username' => 'aaronupdated001',
            'profile_image' => 'https://example.com/avatar.png',
        ])->assertOk()
            ->assertJsonPath('data.email', 'aaron.updated@studentflow.local')
            ->assertJsonPath('data.username', 'aaronupdated001')
            ->assertJsonPath('data.profile_image', 'https://example.com/avatar.png');

        $this->assertDatabaseHas('students', [
            'id' => $studentUser->student_id,
            'email' => 'aaron.updated@studentflow.local',
            'profile_image' => 'https://example.com/avatar.png',
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $studentUser->id,
            'username' => 'aaronupdated001',
            'email' => 'aaron.updated@studentflow.local',
            'avatar_url' => 'https://example.com/avatar.png',
        ]);
    }

    public function test_verified_student_can_request_class_and_teacher_can_approve(): void
    {
        $verified = User::where('username', 'aaronvillanueva001')->firstOrFail();
        $unverified = User::where('username', 'biancaramos002')->firstOrFail();
        $class = SchoolClass::where('class_name', 'BSIT 1B')->firstOrFail();

        Sanctum::actingAs($unverified);
        $this->postJson('/api/student/join-requests', ['join_code' => $class->join_code])
            ->assertUnprocessable();

        Sanctum::actingAs($verified);
        $response = $this->postJson('/api/student/join-requests', ['join_code' => strtolower($class->join_code)])
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending');

        $joinRequest = ClassJoinRequest::findOrFail($response->json('data.id'));
        $teacher = User::where('username', 'angela.cruz')->firstOrFail();
        Sanctum::actingAs($teacher);

        $this->patchJson("/api/join-requests/{$joinRequest->id}", ['decision' => 'approved'])
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');

        $this->assertDatabaseHas('class_students', [
            'class_id' => $class->id,
            'student_id' => $verified->student_id,
            'status' => 'enrolled',
        ]);
    }

    public function test_starter_dataset_matches_second_semester_context(): void
    {
        $this->assertSame(1, User::where('role', 'admin')->count());
        $this->assertSame(5, User::where('role', 'teacher')->count());
        $this->assertSame(10, User::where('role', 'student')->count());
        $this->assertSame(5, Teacher::count());
        $this->assertSame(10, Student::count());
        $this->assertSame(5, SchoolClass::count());
        $this->assertSame(5, SchoolClass::distinct('teacher_id')->count('teacher_id'));
        $this->assertSame(0, SchoolClass::where('semester', '!=', 'Second Semester')->count());
        $this->assertSame(0, SchoolClass::where('school_year', '!=', '2025-2026')->count());
        $this->assertSame(10, Exam::where('status', 'closed')->count());
        $this->assertSame(10, Exam::where('status', 'published')->count());
        $this->assertGreaterThan(0, ExamAttempt::where('status', 'submitted')->count());
        $this->assertGreaterThan(0, ExamAttempt::where('status', 'assigned')->count());
        $this->assertDatabaseHas('exam_answers', ['is_correct' => true]);
    }

    public function test_student_registration_and_new_social_login_create_student_records(): void
    {
        $register = $this->postJson('/api/auth/register', [
            'name' => 'New Mobile Student',
            'email' => 'new.mobile@studentflow.local',
            'password' => 'Student123!',
            'password_confirmation' => 'Student123!',
        ])->assertCreated();

        $this->assertSame('student', $register->json('user.role'));
        $this->assertDatabaseHas('students', ['email' => 'new.mobile@studentflow.local']);
        $this->assertDatabaseHas('users', ['email' => 'new.mobile@studentflow.local', 'role' => 'student']);

        $this->postJson('/api/auth/register', [
            'name' => 'Bad Actor',
            'email' => 'bad.actor@studentflow.local',
            'password' => 'Student123!',
            'password_confirmation' => 'Student123!',
            'role' => 'teacher',
        ])->assertUnprocessable()->assertJsonValidationErrors('role');

        $social = $this->postJson('/api/auth/google', [
            'id_token' => 'test-google:new.social@studentflow.local',
        ])->assertOk();

        $this->assertSame('student', $social->json('user.role'));
        $this->assertDatabaseHas('students', ['email' => 'new.social@studentflow.local']);
        $this->assertDatabaseHas('users', ['email' => 'new.social@studentflow.local', 'role' => 'student']);
    }
}
