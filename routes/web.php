<?php

use App\Http\Controllers\AcademicActionController;
use App\Http\Controllers\AiCopilotController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemoAuthController;
use App\Models\Campus;
use App\Models\Course;
use App\Models\Cpl;
use App\Models\Faculty;
use App\Models\Program;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Arunika Learning Operating System
|--------------------------------------------------------------------------
*/

// 1. Product Website (Marketing Landing Page)
Route::get('/', function () {
    return view('pages.marketing.landing', [
        'campus' => Campus::first(),
        'facultiesCount' => Faculty::count(),
        'programsCount' => Program::count(),
        'coursesCount' => Course::count(),
        'cplsCount' => Cpl::count(),
        'usersCount' => User::count(),
    ]);
})->name('marketing.home');

// 2. Demo Request Submission (Landing Page Lead)
Route::post('/demo/request', [DemoAuthController::class, 'storeDemoRequest'])->name('demo.request.store');

// 3. Instant Role Switcher for Pair-Programming & Evaluation
Route::get('/demo/login/{role}', [DemoAuthController::class, 'switchRole'])->name('demo.login');

// 4. Arunika LMS Application Dashboard & Pages
Route::get('/app', [DashboardController::class, 'index'])->name('app.dashboard');
Route::get('/app/courses', [DashboardController::class, 'courses'])->name('app.courses');
Route::get('/app/assignments', [DashboardController::class, 'assignments'])->name('app.assignments');
Route::get('/app/attendance', [DashboardController::class, 'attendance'])->name('app.attendance');
Route::get('/app/grades', [DashboardController::class, 'grades'])->name('app.grades');
Route::get('/app/materials', [DashboardController::class, 'materials'])->name('app.materials');
Route::get('/app/discussions', [DashboardController::class, 'discussions'])->name('app.discussions');
Route::get('/app/settings', [DashboardController::class, 'settings'])->name('app.settings');

// 5. Academic Actions
Route::prefix('/app')->group(function () {
    Route::post('/assignments/{assignment}/submit', [AcademicActionController::class, 'submitAssignment'])->name('assignments.submit');
    Route::post('/submissions/{submission}/grade', [AcademicActionController::class, 'gradeSubmission'])->name('submissions.grade');
    Route::post('/meetings/{meeting}/attendance', [AcademicActionController::class, 'checkInAttendance'])->name('meetings.attendance');
});

// 6. AI Copilot Endpoint
Route::post('/api/copilot/ask', [AiCopilotController::class, 'ask'])->name('copilot.ask');
