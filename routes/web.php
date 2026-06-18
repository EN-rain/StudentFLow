<?php

use App\Http\Controllers\Web\AdminActivityLogController;
use App\Http\Controllers\Web\AdminSchoolSettingController;
use App\Http\Controllers\Web\AdminTeacherController;
use App\Http\Controllers\Web\AnnouncementWebController;
use App\Http\Controllers\Web\AssignmentWebController;
use App\Http\Controllers\Web\AttendanceWebController;
use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\ClassWebController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\GradeWebController;
use App\Http\Controllers\Web\MagicExamWebController;
use App\Http\Controllers\Web\ReportController;
use App\Http\Controllers\Web\StudentWebController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/dashboard'));
Route::get('/exam/magic/{token}', [MagicExamWebController::class, 'show']);
Route::post('/exam/magic/{token}/start', [MagicExamWebController::class, 'start']);
Route::post('/exam/magic/{token}', [MagicExamWebController::class, 'submit']);

// Public auth routes
Route::get('/login', [AuthWebController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthWebController::class, 'login']);
Route::post('/logout', [AuthWebController::class, 'logout'])->name('logout');
Route::get('/teacher/setup/{token}', [AuthWebController::class, 'showTeacherSetup'])->name('teacher.setup');
Route::post('/teacher/setup', [AuthWebController::class, 'completeTeacherSetup']);
Route::get('/forgot-password', [AuthWebController::class, 'showForgotPassword']);
Route::post('/forgot-password', [AuthWebController::class, 'forgotPassword']);
Route::get('/reset-password/{token}', [AuthWebController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthWebController::class, 'resetPassword']);

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/change-password', [AuthWebController::class, 'showChangePassword']);
    Route::post('/change-password', [AuthWebController::class, 'changePassword']);

    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/teachers', [AdminTeacherController::class, 'index']);
        Route::get('/teachers/create', [AdminTeacherController::class, 'create']);
        Route::post('/teachers', [AdminTeacherController::class, 'store']);
        Route::get('/teachers/{teacher}/edit', [AdminTeacherController::class, 'edit']);
        Route::put('/teachers/{teacher}', [AdminTeacherController::class, 'update']);
        Route::patch('/teachers/{teacher}/status', [AdminTeacherController::class, 'setStatus']);
        Route::post('/teachers/{teacher}/invite', [AdminTeacherController::class, 'invite']);
        Route::get('/activity-logs', [AdminActivityLogController::class, 'index']);
        Route::get('/activity-logs/csv', [AdminActivityLogController::class, 'csv']);
        Route::get('/settings', [AdminSchoolSettingController::class, 'index']);
        Route::put('/settings', [AdminSchoolSettingController::class, 'update']);
    });

    Route::middleware('role:admin,teacher')->group(function () {
        Route::get('/classes', [ClassWebController::class, 'index']);
        Route::get('/classes/create', [ClassWebController::class, 'create']);
        Route::post('/classes', [ClassWebController::class, 'store']);
        Route::get('/classes/{class}', [ClassWebController::class, 'show']);
        Route::post('/classes/{class}/enrollments', [ClassWebController::class, 'storeEnrollment']);
        Route::put('/classes/{class}/enrollments/{student}', [ClassWebController::class, 'updateEnrollment']);
        Route::patch('/classes/{class}/join-requests/{joinRequest}', [ClassWebController::class, 'reviewJoinRequest']);
        Route::delete('/classes/{class}/enrollments/{student}', [ClassWebController::class, 'destroyEnrollment']);
        Route::get('/classes/{class}/edit', [ClassWebController::class, 'edit']);
        Route::put('/classes/{class}', [ClassWebController::class, 'update']);
        Route::delete('/classes/{class}', [ClassWebController::class, 'destroy']);

        Route::get('/students', [StudentWebController::class, 'index']);
        Route::get('/students/create', [StudentWebController::class, 'create']);
        Route::post('/students', [StudentWebController::class, 'store']);
        Route::get('/students/{student}', [StudentWebController::class, 'show']);
        Route::get('/students/{student}/edit', [StudentWebController::class, 'edit']);
        Route::put('/students/{student}', [StudentWebController::class, 'update']);
        Route::delete('/students/{student}', [StudentWebController::class, 'destroy']);

        Route::get('/attendance', [AttendanceWebController::class, 'index']);
        Route::get('/attendance/{class}', [AttendanceWebController::class, 'show']);
        Route::post('/attendance/{class}', [AttendanceWebController::class, 'save']);
        Route::get('/attendance/{class}/history', [AttendanceWebController::class, 'history']);

        Route::get('/grades', [GradeWebController::class, 'index']);
        Route::get('/grades/{class}', [GradeWebController::class, 'show']);
        Route::post('/grades/{class}', [GradeWebController::class, 'save']);
        Route::post('/grades/{class}/categories', [GradeWebController::class, 'storeCategory']);
        Route::put('/grades/{class}/categories/{category}', [GradeWebController::class, 'updateCategory']);
        Route::delete('/grades/{class}/categories/{category}', [GradeWebController::class, 'destroyCategory']);
        Route::post('/grades/{class}/items', [GradeWebController::class, 'storeItem']);
        Route::put('/grades/{class}/items/{item}', [GradeWebController::class, 'updateItem']);
        Route::delete('/grades/{class}/items/{item}', [GradeWebController::class, 'destroyItem']);

        Route::get('/assignments', [AssignmentWebController::class, 'index']);
        Route::get('/assignments/create', [AssignmentWebController::class, 'create']);
        Route::post('/assignments', [AssignmentWebController::class, 'store']);
        Route::get('/assignments/{assignment}', [AssignmentWebController::class, 'show']);
        Route::post('/assignments/{assignment}/submissions', [AssignmentWebController::class, 'saveSubmissions']);
        Route::get('/assignments/{assignment}/edit', [AssignmentWebController::class, 'edit']);
        Route::put('/assignments/{assignment}', [AssignmentWebController::class, 'update']);
        Route::delete('/assignments/{assignment}', [AssignmentWebController::class, 'destroy']);

        Route::get('/announcements', [AnnouncementWebController::class, 'index']);
        Route::get('/announcements/create', [AnnouncementWebController::class, 'create']);
        Route::post('/announcements', [AnnouncementWebController::class, 'store']);
        Route::get('/announcements/{announcement}', [AnnouncementWebController::class, 'show']);
        Route::get('/announcements/{announcement}/edit', [AnnouncementWebController::class, 'edit']);
        Route::put('/announcements/{announcement}', [AnnouncementWebController::class, 'update']);
        Route::delete('/announcements/{announcement}', [AnnouncementWebController::class, 'destroy']);

        Route::get('/reports', [ReportController::class, 'index']);
        Route::get('/reports/{type}', [ReportController::class, 'show'])->where('type', 'student-profile|attendance|grades|class-performance|missing-assignments|failing-grades|frequent-absences');
        Route::get('/reports/{type}/pdf', [ReportController::class, 'pdf'])->where('type', 'student-profile|attendance|grades|class-performance|missing-assignments|failing-grades|frequent-absences');
        Route::get('/reports/{type}/csv', [ReportController::class, 'csv'])->where('type', 'student-profile|attendance|grades|class-performance|missing-assignments|failing-grades|frequent-absences');
    });
});
