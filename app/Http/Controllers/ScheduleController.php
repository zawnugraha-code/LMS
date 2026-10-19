<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Campus;
use App\Models\Term;
use App\Models\User;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function __construct(
        protected ScheduleService $scheduleService
    ) {}

    /**
     * Display the Jadwal & Kalender Akademik page.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $campus = $user?->campus ?? Campus::first();
        $activeTerm = $this->scheduleService->getActiveTerm();
        $allTerms = Term::orderBy('start_date', 'desc')->get();

        $selectedTermId = $request->has('term_id') ? (int) $request->input('term_id') : ($activeTerm?->id);
        $summary = $this->scheduleService->getSummaryMetrics($user, $selectedTermId);
        $todayClasses = $this->scheduleService->getTodayClasses($user);
        $weeklySchedule = $this->scheduleService->getWeeklySchedule($user, $selectedTermId);
        $timetableGrid = $this->scheduleService->getWeeklyTimetableGrid($user, $selectedTermId);

        // Calendar target month & year
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);
        $currentMonthCarbon = Carbon::createFromDate($year, $month, 1);
        $monthlyEvents = $this->scheduleService->getMonthlyEvents($user, $year, $month);

        $enrolledClasses = $this->scheduleService->getEnrolledClasses($user, $selectedTermId);

        return view('pages.app.schedule', [
            'user' => $user,
            'campus' => $campus,
            'activeTerm' => $activeTerm,
            'selectedTermId' => $selectedTermId,
            'allTerms' => $allTerms,
            'summary' => $summary,
            'todayClasses' => $todayClasses,
            'weeklySchedule' => $weeklySchedule,
            'timetableGrid' => $timetableGrid,
            'monthlyEvents' => $monthlyEvents,
            'enrolledClasses' => $enrolledClasses,
            'currentMonthCarbon' => $currentMonthCarbon,
            'todayFormatted' => now()->locale('id')->isoFormat('dddd, D MMMM Y'),
        ]);
    }

    /**
     * Export all student class schedules as standard RFC 5545 iCalendar (.ics).
     */
    public function exportIcs(Request $request): Response
    {
        $user = Auth::user();
        $classId = $request->has('class_id') ? (int) $request->input('class_id') : null;
        $icsData = $this->scheduleService->generateIcs($user, $classId);

        $filename = 'jadwal-kuliah-arunika-'.now()->format('Ymd').'.ics';

        return response($icsData, 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
        ]);
    }

    /**
     * JSON Endpoint for interactive monthly calendar switching.
     */
    public function events(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        $events = $this->scheduleService->getMonthlyEvents($user, $year, $month);

        return response()->json([
            'year' => $year,
            'month' => $month,
            'events' => $events,
        ]);
    }
}
