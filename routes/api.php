<?php

use App\Http\Controllers\Api\AdminActivityLogController;
use App\Http\Controllers\Api\AdminSchoolSettingController;
use App\Http\Controllers\Api\AdminTeacherController;
use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\AssignmentController;
use App\Http\Controllers\Api\AssignmentSubmissionController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClassController;
use App\Http\Controllers\Api\ClassJoinRequestController;
use App\Http\Controllers\Api\DashboardStatsController;
use App\Http\Controllers\Api\EnrollmentController;
use App\Http\Controllers\Api\ExamController;
use App\Http\Controllers\Api\GradeController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\StudentPortalController;
use App\Http\Controllers\Api\StudentSocialAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::post('/google', [StudentSocialAuthController::class, 'google'])->middleware('throttle:10,1');
    Route::post('/github', [StudentSocialAuthController::class, 'github'])->middleware('throttle:10,1');
    Route::post('/github/mobile/start', [StudentSocialAuthController::class, 'mobileGithubStart'])->middleware('throttle:10,1');
    Route::post('/github/mobile/complete', [StudentSocialAuthController::class, 'mobileGithubComplete'])->middleware('throttle:10,1');
    Route::get('/github/callback', [StudentSocialAuthController::class, 'githubCallback'])->middleware('throttle:20,1');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:5,1');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:5,1');

    Route::middleware(['auth:sanctum', 'active'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
    });
});

Route::post('/exam/magic/{token}/start', [ExamController::class, 'magicStart'])->middleware('throttle:20,1');
Route::get('/exam/magic/{token}', [ExamController::class, 'magicShow'])->middleware('throttle:60,1');
Route::post('/exam/magic/{token}/submit', [ExamController::class, 'magicSubmit'])->middleware('throttle:20,1');

Route::middleware(['auth:sanctum', 'active'])->group(function () {
    Route::prefix('student')->middleware('role:student')->group(function () {
        Route::get('/dashboard', [StudentPortalController::class, 'dashboard']);
        Route::get('/profile', [StudentPortalController::class, 'profile']);
        Route::patch('/profile', [StudentPortalController::class, 'updateProfile']);
        Route::get('/classes', [StudentPortalController::class, 'classes']);
        Route::get('/announcements', [StudentPortalController::class, 'announcements']);
        Route::get('/assignments', [StudentPortalController::class, 'assignments']);
        Route::get('/grades', [StudentPortalController::class, 'grades']);
        Route::get('/attendance', [StudentPortalController::class, 'attendance']);
        Route::get('/exams', [StudentPortalController::class, 'exams']);
        Route::get('/join-requests', [ClassJoinRequestController::class, 'studentIndex']);
        Route::post('/join-requests', [ClassJoinRequestController::class, 'store']);
        Route::post('/exams/{attempt}/start', [ExamController::class, 'startAttempt']);
        Route::post('/exams/{attempt}/submit', [ExamController::class, 'submitAttempt']);
    });

    Route::prefix('admin')->middleware('role:admin')->group(function () {
        Route::get('/teachers', [AdminTeacherController::class, 'index']);
        Route::post('/teachers', [AdminTeacherController::class, 'store']);
        Route::get('/teachers/{teacher}', [AdminTeacherController::class, 'show']);
        Route::put('/teachers/{teacher}', [AdminTeacherController::class, 'update']);
        Route::patch('/teachers/{teacher}/status', [AdminTeacherController::class, 'setStatus']);
        Route::post('/teachers/{teacher}/invite', [AdminTeacherController::class, 'invite']);
        Route::get('/settings', [AdminSchoolSettingController::class, 'index']);
        Route::put('/settings', [AdminSchoolSettingController::class, 'update']);
        Route::get('/activity-logs', [AdminActivityLogController::class, 'index']);
    });

    Route::middleware('role:admin,teacher')->group(function () {
        Route::get('/dashboard/stats', DashboardStatsController::class);

        Route::get('/classes', [ClassController::class, 'index']);
        Route::post('/classes', [ClassController::class, 'store']);
        Route::get('/classes/{class}', [ClassController::class, 'show']);
        Route::put('/classes/{class}', [ClassController::class, 'update']);
        Route::patch('/classes/{class}', [ClassController::class, 'update']);
        Route::delete('/classes/{class}', [ClassController::class, 'destroy']);
        Route::get('/classes/{class}/join-requests', [ClassJoinRequestController::class, 'classIndex']);
        Route::patch('/join-requests/{joinRequest}', [ClassJoinRequestController::class, 'review']);

        Route::get('/classes/{class}/enrollments', [EnrollmentController::class, 'index']);
        Route::post('/classes/{class}/enrollments', [EnrollmentController::class, 'store']);
        Route::put('/classes/{class}/enrollments/{student}', [EnrollmentController::class, 'update']);
        Route::delete('/classes/{class}/enrollments/{student}', [EnrollmentController::class, 'destroy']);

        Route::get('/students', [StudentController::class, 'index']);
        Route::post('/students', [StudentController::class, 'store']);
        Route::get('/students/{student}', [StudentController::class, 'show']);
        Route::put('/students/{student}', [StudentController::class, 'update']);
        Route::patch('/students/{student}', [StudentController::class, 'update']);
        Route::delete('/students/{student}', [StudentController::class, 'destroy']);

        Route::get('/attendance', [AttendanceController::class, 'index']);
        Route::post('/attendance', [AttendanceController::class, 'store']);
        Route::post('/attendance/mark-all-present', [AttendanceController::class, 'markAllPresent']);
        Route::get('/attendance/student/{studentId}/stats', [AttendanceController::class, 'studentStats']);
        Route::put('/attendance/{attendance}', [AttendanceController::class, 'update']);
        Route::delete('/attendance/{attendance}', [AttendanceController::class, 'destroy']);

        Route::get('/classes/{class}/grade-categories', [GradeController::class, 'indexCategories']);
        Route::post('/classes/{class}/grade-categories', [GradeController::class, 'storeCategory']);
        Route::put('/classes/{class}/grade-categories/{category}', [GradeController::class, 'updateCategory']);
        Route::delete('/classes/{class}/grade-categories/{category}', [GradeController::class, 'destroyCategory']);

        Route::get('/classes/{class}/grade-items', [GradeController::class, 'indexItems']);
        Route::post('/classes/{class}/grade-items', [GradeController::class, 'storeItem']);
        Route::put('/classes/{class}/grade-items/{item}', [GradeController::class, 'updateItem']);
        Route::delete('/classes/{class}/grade-items/{item}', [GradeController::class, 'destroyItem']);

        Route::get('/classes/{class}/students/{studentId}/student-grades', [GradeController::class, 'indexStudentGrades']);
        Route::post('/classes/{class}/students/{studentId}/student-grades', [GradeController::class, 'saveStudentGrades']);

        Route::get('/classes/{class}/students/{studentId}/final-grade', [GradeController::class, 'finalGrade']);

        Route::get('/assignments', [AssignmentController::class, 'index']);
        Route::post('/assignments', [AssignmentController::class, 'store']);
        Route::get('/assignments/{assignment}', [AssignmentController::class, 'show']);
        Route::put('/assignments/{assignment}', [AssignmentController::class, 'update']);
        Route::delete('/assignments/{assignment}', [AssignmentController::class, 'destroy']);
        Route::get('/assignments/{assignment}/submissions', [AssignmentSubmissionController::class, 'index']);
        Route::post('/assignments/{assignment}/submissions', [AssignmentSubmissionController::class, 'store']);

        Route::get('/exams', [ExamController::class, 'index']);
        Route::post('/exams', [ExamController::class, 'store']);
        Route::get('/exams/{exam}', [ExamController::class, 'show']);
        Route::post('/exams/{exam}/publish', [ExamController::class, 'publish']);
        Route::get('/exams/{exam}/audit', [ExamController::class, 'audit']);

        Route::get('/announcements', [AnnouncementController::class, 'index']);
        Route::post('/announcements', [AnnouncementController::class, 'store']);
        Route::get('/announcements/{announcement}', [AnnouncementController::class, 'show']);
        Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update']);
        Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy']);

        Route::get('/reports/{type}', [ReportController::class, 'show']);
    });
});
