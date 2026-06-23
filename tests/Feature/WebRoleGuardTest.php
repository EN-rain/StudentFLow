<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebRoleGuardTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_cross_role_url_access_returns_403(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $teacher = User::where('role', 'teacher')->firstOrFail();

        $studentUser = User::factory()->create([
            'role' => 'student',
            'username' => 'guard-test',
            'name' => 'Guard Test',
            'status' => 'active',
        ]);
        $student = Student::create([
            'student_number' => 'SID-GUARD-TEST',
            'first_name' => 'Guard',
            'last_name' => 'Test',
            'email' => $studentUser->email,
            'status' => 'active',
        ]);
        $studentUser->forceFill(['student_id' => $student->id])->save();

        // Each entry: [role_name, user, forbidden_urls[], allowed_url]
        $cases = [
            'admin' => [
                'user' => $admin,
                'forbidden' => ['/student'],
                'allowed' => '/admin/teachers',
            ],
            'teacher' => [
                'user' => $teacher,
                'forbidden' => ['/admin/teachers', '/admin/activity-logs', '/admin/settings', '/student'],
                'allowed' => '/classes',
            ],
            'student' => [
                'user' => $studentUser,
                'forbidden' => [
                    '/admin/teachers',
                    '/admin/activity-logs',
                    '/admin/settings',
                    '/classes',
                    '/students',
                    '/attendance',
                    '/grades',
                    '/assignments',
                    '/exams',
                    '/announcements',
                    '/reports',
                ],
                'allowed' => '/student',
            ],
        ];

        $failures = [];

        foreach ($cases as $roleName => $case) {
            $user = $case['user'];
            $forbiddenUrls = $case['forbidden'];
            $allowedUrl = $case['allowed'];

            // Positive control: verify the role CAN access its own route
            $allowedResponse = $this->actingAs($user)->get($allowedUrl);
            if ($allowedResponse->status() !== 200) {
                $failures[] = "Positive control failed: {$roleName} got HTTP {$allowedResponse->status()} instead of 200 for {$allowedUrl}";
            }

            // Cross-role checks: each forbidden URL must return 403
            foreach ($forbiddenUrls as $url) {
                $response = $this->actingAs($user)->get($url);
                if ($response->status() !== 403) {
                    $failures[] = "Expected 403: {$roleName} got HTTP {$response->status()} for {$url}";
                }
            }
        }

        $this->assertEmpty($failures, implode(PHP_EOL, $failures));
    }
}
