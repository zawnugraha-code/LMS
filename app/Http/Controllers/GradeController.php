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
        if (! Auth::check()) {
            $defaultUser = User::where('role', UserRole::Student)->where('email', 'arya.pratama@student.unus.ac.id')->first()
                ?? User::where('role', UserRole::Student)->first();
            if ($defaultUser) {
                Auth::login($defaultUser);
                if ($defaultUser->campus_id) {
                    session(['active_campus_id' => $defaultUser->campus_id]);
                }
            }
        }

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
        if (! Auth::check()) {
            $defaultUser = User::where('role', UserRole::Student)->where('email', 'arya.pratama@student.unus.ac.id')->first()
                ?? User::where('role', UserRole::Student)->first();
            if ($defaultUser) {
                Auth::login($defaultUser);
                if ($defaultUser->campus_id) {
                    session(['active_campus_id' => $defaultUser->campus_id]);
                }
            }
        }

        $user = Auth::user();

        // Strict authorization check: enrollment belongs to student
        if ($enrollment->user_id !== $user->id && $user->role === UserRole::Student) {
            abort(403, 'Anda tidak memiliki hak akses ke transkrip nilai mahasiswa ini.');
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
        if (! Auth::check()) {
            $defaultUser = User::where('role', UserRole::Student)->where('email', 'arya.pratama@student.unus.ac.id')->first()
                ?? User::where('role', UserRole::Student)->first();
            if ($defaultUser) {
                Auth::login($defaultUser);
            }
        }

        $user = Auth::user();

        // Strict authorization check
        if ($enrollment->user_id !== $user->id && $user->role === UserRole::Student) {
            abort(403, 'Akses ditolak.');
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
        if (! Auth::check()) {
            $defaultUser = User::where('role', UserRole::Student)->where('email', 'arya.pratama@student.unus.ac.id')->first()
                ?? User::where('role', UserRole::Student)->first();
            if ($defaultUser) {
                Auth::login($defaultUser);
            }
        }

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
