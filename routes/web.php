<?php

use App\Http\Controllers\AcademicActionController;
use App\Http\Controllers\AiCopilotController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemoAuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SearchController;
use App\Models\Campus;
use App\Models\Course;
use App\Models\Cpl;
use App\Models\Faculty;
use App\Models\Program;
use App\Models\User;
use App\Services\AcademicTermService;
use Illuminate\Http\Request;
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

// 3. Instant Role Switcher & Auth
Route::get('/demo/login/{role}', [DemoAuthController::class, 'switchRole'])->name('demo.login');
Route::post('/logout', [DemoAuthController::class, 'logout'])->name('logout');

// 4. Arunika LMS Application Dashboard & Pages
Route::get('/app', [DashboardController::class, 'index'])->name('app.dashboard');
Route::get('/app/courses', [CourseController::class, 'index'])->name('app.courses');
Route::get('/app/courses/{class}', [CourseController::class, 'show'])->name('app.courses.show');
Route::get('/app/courses/{class}/classroom', [CourseController::class, 'classroom'])->name('app.courses.classroom');
Route::get('/app/assignments', [DashboardController::class, 'assignments'])->name('app.assignments');
Route::get('/app/attendance', [DashboardController::class, 'attendance'])->name('app.attendance');
Route::get('/app/grades', [DashboardController::class, 'grades'])->name('app.grades');
Route::get('/app/materials', [DashboardController::class, 'materials'])->name('app.materials');
Route::get('/app/discussions', [DashboardController::class, 'discussions'])->name('app.discussions');
Route::get('/app/settings', [DashboardController::class, 'settings'])->name('app.settings');
Route::get('/app/notifications', [NotificationController::class, 'all'])->name('app.notifications');

// 5. Global Search & Notifications APIs
Route::get('/app/search', [SearchController::class, 'search'])->name('app.search');
Route::get('/api/notifications', [NotificationController::class, 'index'])->name('api.notifications');
Route::post('/app/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark_all_read');
Route::post('/app/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark_as_read');

// 6. Global Academic Term Switcher
Route::post('/app/term/switch', function (Request $request, AcademicTermService $termService) {
    $termId = (int) $request->input('term_id');
    $termService->setActiveTerm($termId);

    return redirect()->back()->with('success', 'Semester aktif berhasil diubah.');
})->name('app.term.switch');

// 7. Academic Actions
Route::prefix('/app')->group(function () {
    Route::post('/assignments/{assignment}/submit', [AcademicActionController::class, 'submitAssignment'])->name('assignments.submit');
    Route::post('/submissions/{submission}/grade', [AcademicActionController::class, 'gradeSubmission'])->name('submissions.grade');
    Route::post('/meetings/{meeting}/attendance', [AcademicActionController::class, 'checkInAttendance'])->name('meetings.attendance');
});

// 8. AI Copilot Endpoint
Route::post('/api/copilot/ask', [AiCopilotController::class, 'ask'])->name('copilot.ask');
