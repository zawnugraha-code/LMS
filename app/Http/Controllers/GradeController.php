<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Campus;
use App\Models\Enrollment;
use App\Models\User;
use App\Services\AcademicTermService;
use App\Services\GradeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GradeController extends Controller
{
    public function __construct(
        protected GradeService $gradeService,
        protected AcademicTermService $termService
    ) {}

    /**
     * Display the student grades dashboard page.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $campus = $user?->campus ?? Campus::first();

        $activeTerm = $this->termService->getActiveTerm();
        $terms = $this->termService->getAllTerms();

        $summary = $this->gradeService->getSummary($user);
        $grades = $this->gradeService->getEnrollmentGrades($user, $request->input('semester', 'all'));
        $distribution = $this->gradeService->getGradeDistribution($user);
        $performance = $this->gradeService->getAcademicPerformanceTrends($user);
        $announcements = $this->gradeService->getAnnouncements();

        return view('pages.app.grades', [
            'user' => $user,
            'campus' => $campus,
            'currentRole' => $user->role,
            'activeTerm' => $activeTerm,
            'terms' => $terms,
            'summary' => $summary,
            'grades' => $grades,
            'distribution' => $distribution,
            'performance' => $performance,
            'announcements' => $announcements,
        ]);
    }

    /**
     * Dedicated sub-page: Course transcript & OBE evaluation detail.
     */
    public function show(Enrollment $enrollment): View
    {
        $user = Auth::user();

        // Strict authorization check: enrollment belongs to student or lecturer of the class
        if ($user->role === UserRole::Student) {
            if ($enrollment->user_id !== $user->id || $enrollment->isDikeluarkan()) {
                abort(403, 'Anda tidak memiliki hak akses ke transkrip nilai mahasiswa ini.');
            }
        } elseif ($user->role === UserRole::Lecturer) {
            if ($enrollment->academicClass?->lecturer_id !== $user->id) {
                abort(403, 'Anda tidak memiliki hak akses ke transkrip nilai mahasiswa kelas ini.');
            }
        }

        $detail = $this->gradeService->getEnrollmentDetail($enrollment);
        $detail['user'] = $user;
        $detail['currentRole'] = $user->role;

        return view('pages.app.grade-detail', $detail);
    }

    /**
     * Download official KHS PDF document for a specific course enrollment.
     */
    public function downloadKhs(Enrollment $enrollment): Response
    {
        $user = Auth::user();

        // Strict authorization check
        if ($user->role === UserRole::Student) {
            if ($enrollment->user_id !== $user->id || $enrollment->isDikeluarkan()) {
                abort(403, 'Akses ditolak.');
            }
        } elseif ($user->role === UserRole::Lecturer) {
            if ($enrollment->academicClass?->lecturer_id !== $user->id) {
                abort(403, 'Akses ditolak.');
            }
        }

        $pdfBinary = $this->gradeService->generateKhsPdf($enrollment);
        $courseCode = $enrollment->academicClass?->course?->code ?? 'KHS';
        $fileName = 'KHS_'.$courseCode.'_'.($user->identifier ?? '23060101024').'.pdf';

        return response($pdfBinary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }

    /**
     * Download comprehensive semester KHS PDF document.
     */
    public function downloadFullKhs(Request $request): Response
    {
        $user = Auth::user();
        $firstEnrollment = Enrollment::where('user_id', $user->id)->first() ?? Enrollment::first();

        if (! $firstEnrollment) {
            abort(404, 'Data KHS tidak ditemukan.');
        }

        $pdfBinary = $this->gradeService->generateKhsPdf($firstEnrollment);
        $fileName = 'KHS_Semester3_'.($user->identifier ?? '23060101024').'.pdf';

        return response($pdfBinary, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
        ]);
    }
}
