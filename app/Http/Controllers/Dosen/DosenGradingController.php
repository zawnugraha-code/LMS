<?php

namespace App\Http\Controllers\Dosen;

use App\Helpers\NotificationHelper;
use App\Http\Controllers\Controller;
use App\Models\AcademicClass;
use App\Models\Assignment;
use App\Models\Submission;
use App\Models\Term;
use App\Models\User;
use App\Notifications\SubmissionGradedNotification;
use App\Services\Dosen\DosenDashboardService;
use App\Services\Dosen\DosenGradingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DosenGradingController extends Controller
{
    public function __construct(
        protected DosenGradingService $gradingService,
        protected DosenDashboardService $dashboardService
    ) {}

    /**
     * Display the assessment grading overview page.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $term = Term::where('is_active', true)->first() ?? Term::latest()->first();

        $filters = [
            'jenis' => $request->query('jenis', 'semua'),
            'course_id' => $request->query('course_id', 'semua'),
            'class_id' => $request->query('class_id', 'semua'),
            'search' => $request->query('search', ''),
        ];

        $assessments = $this->gradingService->getAssessmentsForGrading($user, $filters, 10);
        $summary = $this->gradingService->getGradingSummary($user, $term);
        $progress = $this->gradingService->getGradingProgress($user, $term);
        $recentAnnouncements = $this->dashboardService->getRecentAnnouncements($user, 2);

        // Get classes and courses for filters and right widget
        $classes = AcademicClass::with('course')
            ->where('lecturer_id', $user->id)
            ->where('term_id', $term->id)
            ->get();

        $courses = $classes->pluck('course')->unique('id');

        return view('pages.dosen.penilaian.index', compact(
            'user',
            'term',
            'filters',
            'assessments',
            'summary',
            'progress',
            'classes',
            'courses',
            'recentAnnouncements'
        ));
    }

    /**
     * Show grading workspace for a specific assessment.
     */
    public function show(Assignment $assignment): View
    {
        Gate::authorize('view', $assignment);

        $gradingData = $this->gradingService->getGradingDetail($assignment);

        return view('pages.dosen.penilaian.show', array_merge($gradingData, [
            'assignment' => $assignment,
        ]));
    }

    /**
     * Submit score and feedback for an existing submission.
     */
    public function grade(Request $request, Submission $submission): RedirectResponse
    {
        Gate::authorize('grade', $submission);

        $validated = $request->validate([
            'score' => 'required|numeric|min:0|max:100',
            'feedback' => 'nullable|string|max:1000',
        ]);

        $this->gradingService->gradeSubmission($submission, (float) $validated['score'], $validated['feedback'] ?? null);

        return redirect()->back()
            ->with('success', "Nilai untuk {$submission->user?->name} berhasil disimpan.");
    }

    /**
     * Assign grade for a student without an existing submission (manual grading).
     */
    public function gradeStudent(Request $request, Assignment $assignment, User $student): RedirectResponse
    {
        Gate::authorize('update', $assignment);

        $validated = $request->validate([
            'score' => 'required|numeric|min:0|max:100',
            'feedback' => 'nullable|string|max:1000',
        ]);

        $submission = Submission::updateOrCreate(
            [
                'assignment_id' => $assignment->id,
                'user_id' => $student->id,
            ],
            [
                'content' => '',
                'file_path' => null,
                'status' => 'graded',
                'status_waktu' => 'terlambat',
                'score' => (float) $validated['score'],
                'feedback' => $validated['feedback'] ?? null,
                'submitted_at' => now(),
            ]
        );

        if (NotificationHelper::shouldNotify($student, 'submission_graded')) {
            $student->notify(new SubmissionGradedNotification($submission));
        }

        return redirect()->back()
            ->with('success', "Nilai untuk {$student->name} berhasil disimpan.");
    }

    /**
     * Quick-assign score 0 to all unsubmitted students.
     */
    public function quickZeros(Assignment $assignment): RedirectResponse
    {
        Gate::authorize('update', $assignment);

        $count = $this->gradingService->quickGradeZeros($assignment);

        return redirect()->back()
            ->with('success', "Berhasil memberikan nilai 0 kepada {$count} mahasiswa yang belum mengumpulkan tugas.");
    }
}
