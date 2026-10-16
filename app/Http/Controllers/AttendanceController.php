<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\AcademicClass;
use App\Models\Campus;
use App\Models\User;
use App\Services\AcademicTermService;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService,
        protected AcademicTermService $termService
    ) {}

    /**
     * Display the student attendance dashboard page.
     */
    public function index(Request $request): View
    {
        if (! Auth::check()) {
            $defaultUser = User::where('role', UserRole::Student)->first() ?? User::first();
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

        $enrolledClasses = AcademicClass::with(['course', 'lecturer'])
            ->whereHas('enrollments', fn ($q) => $q->where('user_id', $user->id))
            ->get();

        $selectedClassId = $request->query('class_id');
        $selectedClass = $selectedClassId
            ? $enrolledClasses->firstWhere('id', (int) $selectedClassId)
            : $enrolledClasses->firstWhere('course.code', 'IF2101') ?? $enrolledClasses->first();

        $summary = $this->attendanceService->getSummary($user, $selectedClass);
        $meetingLogs = $this->attendanceService->getMeetingLogs($user, $selectedClass);
        $nextSchedule = $this->attendanceService->getNextSchedule($user, $selectedClass);
        $donut = $this->attendanceService->getDonutBreakdown($user, $selectedClass);
        $courseBreakdown = $this->attendanceService->getCourseAttendanceBreakdown($user);

        return view('pages.app.attendance', [
            'user' => $user,
            'campus' => $campus,
            'currentRole' => $user->role,
            'activeTerm' => $activeTerm,
            'terms' => $terms,
            'enrolledClasses' => $enrolledClasses,
            'selectedClass' => $selectedClass,
            'summary' => $summary,
            'meetingLogs' => $meetingLogs,
            'nextSchedule' => $nextSchedule,
            'donut' => $donut,
            'courseBreakdown' => $courseBreakdown,
        ]);
    }

    /**
     * Check in attendance via QR token scan.
     */
    public function checkInQr(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'token' => 'required|string',
            'lat' => 'nullable|string',
            'lng' => 'nullable|string',
        ]);

        $user = Auth::user();
        $result = $this->attendanceService->checkInByQr(
            $user,
            $request->input('token'),
            $request->input('lat'),
            $request->input('lng')
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }

        if (! $result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        return redirect()->back()->with('success', $result['message']);
    }

    /**
     * Check in attendance via 6-digit passcode.
     */
    public function checkInCode(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = Auth::user();
        $result = $this->attendanceService->checkInByCode($user, $request->input('code'));

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($result, $result['success'] ? 200 : 422);
        }

        if (! $result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        return redirect()->back()->with('success', $result['message']);
    }

    /**
     * Submit an absence permit (Izin/Sakit) with document upload.
     */
    public function submitPermit(Request $request): RedirectResponse
    {
        $request->validate([
            'meeting_id' => 'required|integer',
            'type' => 'required|string|in:izin,sakit',
            'reason' => 'required|string|max:1000',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $user = Auth::user();
        $result = $this->attendanceService->submitPermit(
            $user,
            (int) $request->input('meeting_id'),
            $request->input('type'),
            $request->input('reason'),
            $request->file('document')
        );

        if (! $result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        return redirect()->back()->with('success', $result['message']);
    }
}
