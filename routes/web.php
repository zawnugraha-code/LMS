<?php

use App\Http\Controllers\AcademicActionController;
use App\Http\Controllers\AiCopilotController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DemoAuthController;
use App\Http\Controllers\DiscussionController;
use App\Http\Controllers\Dosen\DosenClassController;
use App\Http\Controllers\Dosen\DosenDashboardController;
use App\Http\Controllers\Dosen\DosenPlaceholderController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SettingController;
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
Route::get('/login', function () {
    return redirect()->route('marketing.home');
})->name('login');
Route::get('/demo/login/{role}', [DemoAuthController::class, 'switchRole'])->name('demo.login');
Route::post('/logout', [DemoAuthController::class, 'logout'])->name('logout');

// 4. Arunika LMS Application Dashboard & Pages
Route::get('/app', [DashboardController::class, 'index'])->name('app.dashboard');
Route::get('/app/courses', [CourseController::class, 'index'])->name('app.courses');
Route::get('/app/courses/{class}', [CourseController::class, 'show'])->name('app.courses.show');
Route::get('/app/courses/{class}/classroom', [CourseController::class, 'classroom'])->name('app.courses.classroom');
Route::get('/app/schedule', [ScheduleController::class, 'index'])->name('app.schedule');
Route::get('/app/schedule/export.ics', [ScheduleController::class, 'exportIcs'])->name('app.schedule.export.ics');
Route::get('/api/schedule/events', [ScheduleController::class, 'events'])->name('api.schedule.events');
Route::get('/app/assignments', [AssignmentController::class, 'index'])->name('app.assignments');
Route::get('/app/assignments/calendar.ics', [AssignmentController::class, 'exportCalendar'])->name('assignments.calendar.ics');
Route::get('/app/assignments/{assignment}/submit', [AssignmentController::class, 'submitForm'])->name('assignments.submit_form');
Route::get('/app/assignments/{assignment}/download', [AssignmentController::class, 'downloadAttachment'])->name('assignments.download');
Route::get('/app/quizzes/{quiz}/attempt', [AssignmentController::class, 'attemptQuiz'])->name('quizzes.attempt');
Route::post('/app/quizzes/{quiz}/attempt', [AssignmentController::class, 'submitQuiz'])->name('quizzes.submit_attempt');
Route::get('/app/attendance', [AttendanceController::class, 'index'])->name('app.attendance');
Route::post('/app/attendance/check-in/qr', [AttendanceController::class, 'checkInQr'])->name('attendance.check_in.qr');
Route::post('/app/attendance/check-in/code', [AttendanceController::class, 'checkInCode'])->name('attendance.check_in.code');
Route::post('/app/attendance/permit', [AttendanceController::class, 'submitPermit'])->name('attendance.permit');
Route::get('/app/grades', [GradeController::class, 'index'])->name('app.grades');
Route::get('/app/grades/khs/download', [GradeController::class, 'downloadFullKhs'])->name('app.grades.khs.download');
Route::get('/app/grades/{enrollment}', [GradeController::class, 'show'])->name('app.grades.show');
Route::get('/app/grades/{enrollment}/pdf', [GradeController::class, 'downloadKhs'])->name('app.grades.pdf');
Route::get('/app/materials', [MaterialController::class, 'index'])->name('app.materials');
Route::get('/app/materials/{material}/view', [MaterialController::class, 'view'])->name('app.materials.view');
Route::post('/app/materials/{material}/progress', [MaterialController::class, 'updateProgress'])->name('app.materials.progress');
Route::get('/app/discussions', [DiscussionController::class, 'index'])->name('app.discussions');
Route::post('/app/discussions', [DiscussionController::class, 'store'])->name('app.discussions.store');
Route::get('/app/discussions/{thread}', [DiscussionController::class, 'show'])->name('app.discussions.show');
Route::post('/app/discussions/{thread}/replies', [DiscussionController::class, 'storeReply'])->name('app.discussions.reply');
Route::post('/app/discussions/{thread}/upvote', [DiscussionController::class, 'upvoteThread'])->name('app.discussions.upvote');
Route::post('/app/discussions/replies/{reply}/upvote', [DiscussionController::class, 'upvoteReply'])->name('app.discussions.reply.upvote');
Route::post('/app/discussions/replies/{reply}/solution', [DiscussionController::class, 'markSolution'])->name('app.discussions.reply.solution');
Route::get('/app/settings', [SettingController::class, 'index'])->name('app.settings');
Route::post('/app/settings/profile', [SettingController::class, 'updateProfile'])->name('app.settings.profile');
Route::post('/app/settings/password', [SettingController::class, 'updatePassword'])->name('app.settings.password');
Route::post('/app/settings/preferences', [SettingController::class, 'updatePreferences'])->name('app.settings.preferences');
Route::post('/app/settings/support-ticket', [SettingController::class, 'submitSupportTicket'])->name('app.settings.support_ticket');
Route::get('/app/settings/{section}', [SettingController::class, 'showSection'])->name('app.settings.section');
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

// 8. AI Copilot Endpoints & Learning Workspace
Route::get('/app/copilot', [AiCopilotController::class, 'index'])->name('app.copilot');
Route::get('/api/copilot/suggestions', [AiCopilotController::class, 'suggestions'])->name('copilot.suggestions');
Route::post('/api/copilot/ask', [AiCopilotController::class, 'ask'])->name('copilot.ask');

// 9. Dosen (Lecturer) Portal Routes
Route::prefix('dosen')->middleware(['auth', 'role.dosen'])->group(function () {
    Route::get('/', [DosenDashboardController::class, 'index'])->name('dosen.dashboard');
    Route::get('/kelas', [DosenClassController::class, 'index'])->name('dosen.kelas.index');
    Route::get('/kelas/{class}', [DosenClassController::class, 'show'])->name('dosen.kelas.show');

    Route::get('/materi', function () {
        return view('pages.dosen.placeholder', [
            'menuTitle' => 'Materi Pembelajaran',
            'menuDescription' => 'Kelola materi per pertemuan perkuliahan, slide, modul, dan video pembelajaran.',
            'badgeBatch' => 'Fase 6',
        ]);
    })->name('dosen.materi.index');

    Route::get('/tugas', function () {
        return view('pages.dosen.placeholder', [
            'menuTitle' => 'Tugas & Ujian',
            'menuDescription' => 'Kelola assessment, tugas individu/kelompok, kuis, UTS, dan UAS.',
            'badgeBatch' => 'Fase 4',
        ]);
    })->name('dosen.tugas.index');

    Route::get('/tugas/buat', function () {
        return view('pages.dosen.placeholder', [
            'menuTitle' => 'Buat Tugas / Ujian',
            'menuDescription' => 'Formulir pembuatan tugas, kuis, atau ujian baru.',
            'badgeBatch' => 'Fase 4',
        ]);
    })->name('dosen.tugas.create');

    Route::get('/penilaian', function () {
        return view('pages.dosen.placeholder', [
            'menuTitle' => 'Penilaian',
            'menuDescription' => 'Kelola dan nilai berkas pengumpulan tugas, kuis, dan ujian mahasiswa.',
            'badgeBatch' => 'Fase 5',
        ]);
    })->name('dosen.penilaian.index');

    // 4 Menu Batch 2 Placeholders
    Route::get('/presensi', [DosenPlaceholderController::class, 'presensi'])->name('dosen.presensi.index');
    Route::get('/buku-nilai', [DosenPlaceholderController::class, 'bukuNilai'])->name('dosen.buku-nilai.index');
    Route::get('/diskusi', [DosenPlaceholderController::class, 'diskusi'])->name('dosen.diskusi.index');
    Route::get('/pengaturan', [DosenPlaceholderController::class, 'pengaturan'])->name('dosen.pengaturan.edit');
});
