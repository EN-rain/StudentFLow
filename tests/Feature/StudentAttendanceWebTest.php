<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentAttendanceWebTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_student_sees_own_attendance_records(): void
    {
        $studentUser = $this->createStudentUser();
        $class = $this->createClass();
        $this->createAttendance($studentUser->student, $class, '2026-05-01', 'Present', 'On time');
        $this->createAttendance($studentUser->student, $class, '2026-05-02', 'Late', 'Traffic');
        $this->createAttendance($studentUser->student, $class, '2026-05-03', 'Absent', null);

        $response = $this->actingAs($studentUser)->get('/student/attendance');
        $response->assertOk();
        $response->assertSee('My Attendance', false);
        $response->assertSee($class->class_name, false);
        $response->assertSee('Present', false);
        $response->assertSee('Late', false);
        $response->assertSee('Absent', false);
        $response->assertSee('On time', false);
        $response->assertSee('Traffic', false);
    }

    public function test_student_does_not_see_other_students_attendance(): void
    {
        $studentUser = $this->createStudentUser();
        $otherUser = $this->createStudentUser();
        $class = $this->createClass();
        $this->createAttendance($otherUser->student, $class, '2026-05-01', 'Present', 'Should not leak');

        $response = $this->actingAs($studentUser)->get('/student/attendance');
        $response->assertOk();
        $response->assertDontSee('Should not leak', false);
    }

    public function test_attendance_index_handles_zero_records(): void
    {
        $studentUser = $this->createStudentUser();

        $response = $this->actingAs($studentUser)->get('/student/attendance');
        $response->assertOk();
        $response->assertSee('No attendance records yet', false);
    }

    public function test_overall_summary_counts_present_late(): void
    {
        $studentUser = $this->createStudentUser();
        $class = $this->createClass();
        $this->createAttendance($studentUser->student, $class, '2026-05-01', 'Present');
        $this->createAttendance($studentUser->student, $class, '2026-05-02', 'Late');
        $this->createAttendance($studentUser->student, $class, '2026-05-03', 'Absent');
        $this->createAttendance($studentUser->student, $class, '2026-05-04', 'Excused');

        $response = $this->actingAs($studentUser)->get('/student/attendance');
        $response->assertOk();
        // 4 total
        $response->assertSee('Total Records', false);
        // 2 present (Present + Late)
        $response->assertSee('Present / Late', false);
        // 50% rate
        $response->assertSee('50%', false);
    }

    private function createStudentUser(): User
    {
        $user = User::factory()->create([
            'role' => 'student',
            'username' => 'att-test-'.uniqid(),
            'name' => 'Attendance Test',
            'status' => 'active',
        ]);
        $student = Student::create([
            'student_number' => 'SID-ATT-'.uniqid(),
            'first_name' => 'Att',
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
            'class_name' => 'Att Class '.uniqid(),
            'subject' => 'History',
            'description' => 'For attendance test.',
            'teacher_id' => $teacher->id,
            'status' => 'active',
        ]);
    }

    private function createAttendance(Student $student, SchoolClass $class, string $date, string $status, ?string $remarks = null): Attendance
    {
        return Attendance::create([
            'class_id' => $class->id,
            'student_id' => $student->id,
            'attendance_date' => $date,
            'status' => $status,
            'remarks' => $remarks,
            'recorded_by' => User::where('role', 'admin')->firstOrFail()->id,
        ]);
    }
}
