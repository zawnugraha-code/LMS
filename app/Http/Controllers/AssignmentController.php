<?php

namespace App\Http\Controllers;

use App\Models\AcademicClass;
use App\Models\Assignment;
use App\Models\Quiz;
use App\Services\AssignmentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    public function __construct(
        protected AssignmentService $assignmentService
    ) {}

    /**
     * Display the main assignments and exams dashboard.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        $enrolledClasses = $this->assignmentService->getEnrolledClasses($user);
        $classIds = $enrolledClasses->pluck('id')->all();

        $summary = $this->assignmentService->getSummary($user);
        $calendarHighlights = $this->assignmentService->getCalendarHighlights();
        $upcomingAssessments = $this->assignmentService->getUpcomingAssessments($user);

        $assignments = Assignment::with([
            'academicClass.course',
            'submissions' => fn ($q) => $q->where('user_id', $user->id),
        ])
            ->whereIn('class_id', $classIds)
            ->where(function ($q) {
                $q->whereNull('status')
                    ->orWhere('status', 'dipublikasikan');
            })
            ->orderBy('due_at', 'asc')
            ->get();

        $quizzes = Quiz::with([
            'academicClass.course',
            'attempts' => fn ($q) => $q->where('user_id', $user->id),
        ])
            ->whereIn('class_id', $classIds)
            ->get();

        $cardTask1 = $assignments->firstWhere('title', 'Tugas 1 - Latihan Class dan Object') ?? $assignments->first();
        $cardTask2 = $assignments->firstWhere('title', 'Tugas 2 - Normalisasi Database') ?? $assignments->skip(1)->first();
        $cardQuiz1 = $quizzes->firstWhere('title', 'Ujian Tengah Semester (UTS)') ?? $quizzes->first();
        $cardTask4 = $assignments->firstWhere('title', 'Tugas 1 - Konfigurasi IP Address') ?? $assignments->skip(2)->first();
        $cardTask5 = $assignments->firstWhere('title', 'Tugas 3 - Analisis Performa CPU') ?? $assignments->skip(3)->first();
        $cardQuiz2 = $quizzes->firstWhere('title', 'Ujian Akhir Semester (UAS)') ?? $quizzes->skip(1)->first();
        $cardTaskLate = $assignments->firstWhere('title', 'Tugas Praktikum 0 - Environment Setup & Git Workflow') ?? $assignments->last();

        return view('pages.app.assignments', [
            'user' => $user,
            'currentRole' => $user->role,
            'enrolledClasses' => $enrolledClasses,
            'summary' => $summary,
            'calendarHighlights' => $calendarHighlights,
            'upcomingAssessments' => $upcomingAssessments,
            'assignments' => $assignments,
            'quizzes' => $quizzes,
            'cardTask1' => $cardTask1,
            'cardTask2' => $cardTask2,
            'cardQuiz1' => $cardQuiz1,
            'cardTask4' => $cardTask4,
            'cardTask5' => $cardTask5,
            'cardQuiz2' => $cardQuiz2,
            'cardTaskLate' => $cardTaskLate,
        ]);
    }

    /**
     * Dedicated sub-page: Student assignment submission workspace.
     */
    public function submitForm(Assignment $assignment): View
    {
        Gate::authorize('view', $assignment);

        $user = Auth::user();

        // Security check: verify enrollment
        $isEnrolled = AcademicClass::where('id', $assignment->class_id)
            ->whereHas('enrollments', fn ($q) => $q->where('user_id', $user->id))
            ->exists();

        if (! $isEnrolled) {
            abort(403, 'Anda tidak terdaftar dalam mata kuliah untuk tugas ini.');
        }

        $assignment->loadMissing([
            'academicClass.course',
            'academicClass.lecturer',
            'submissions' => fn ($q) => $q->where('user_id', $user->id),
        ]);

        $submission = $assignment->submissions->first();

        return view('pages.app.assignment-submit', [
            'user' => $user,
            'currentRole' => $user->role,
            'assignment' => $assignment,
            'submission' => $submission,
        ]);
    }

    /**
     * Dedicated sub-page: Online exam / quiz attempt room.
     */
    public function attemptQuiz(Quiz $quiz): View
    {
        $user = Auth::user();

        // Security check: verify enrollment
        $isEnrolled = AcademicClass::where('id', $quiz->class_id)
            ->whereHas('enrollments', fn ($q) => $q->where('user_id', $user->id))
            ->exists();

        if (! $isEnrolled) {
            abort(403, 'Anda tidak terdaftar dalam mata kuliah untuk ujian ini.');
        }

        $quiz->loadMissing([
            'academicClass.course',
            'academicClass.lecturer',
            'questions',
            'attempts' => fn ($q) => $q->where('user_id', $user->id),
        ]);

        return view('pages.app.quiz-attempt', [
            'user' => $user,
            'currentRole' => $user->role,
            'quiz' => $quiz,
            'attempt' => $quiz->attempts->sortByDesc('created_at')->first(),
        ]);
    }

    /**
     * Submit an exam/quiz attempt.
     */
    public function submitQuiz(Request $request, Quiz $quiz)
    {
        $user = Auth::user();

        // Security check
        $isEnrolled = AcademicClass::where('id', $quiz->class_id)
            ->whereHas('enrollments', fn ($q) => $q->where('user_id', $user->id))
            ->exists();

        if (! $isEnrolled) {
            abort(403, 'Anda tidak terdaftar dalam mata kuliah untuk ujian ini.');
        }

        $answers = $request->input('answers', []);
        $attempt = $this->assignmentService->submitQuizAttempt($quiz, $user, $answers);

        return redirect()->route('app.assignments')->with(
            'success',
            "Ujian {$quiz->title} berhasil diselesaikan! Skor Anda: {$attempt->score}/100 (+50 XP)."
        );
    }

    /**
     * Export assignments & exams as RFC-5545 iCalendar (.ics).
     */
    public function exportCalendar(): Response
    {
        $user = Auth::user();
        $icsContent = $this->assignmentService->generateCalendarIcs($user);

        return response($icsContent, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="jadwal-tugas-ujian-arunika.ics"',
        ]);
    }

    /**
     * Download sample assignment attachment file.
     */
    public function downloadAttachment(Assignment $assignment): Response
    {
        Gate::authorize('view', $assignment);

        $content = "%PDF-1.4\n% Panduan Resmi Tugas {$assignment->title}\n% Arunika Learning OS\n1 0 obj\n<< /Title ({$assignment->title}) >>\nendobj\ntrailer\n<< /Root 1 0 R >>\n%%EOF";
        $fileName = 'Panduan_'.substr(preg_replace('/[^A-Za-z0-9]/', '_', $assignment->title), 0, 30).'.pdf';

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }
}
