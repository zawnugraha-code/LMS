<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Term;
use App\Services\Dosen\DosenDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DosenDashboardController extends Controller
{
    public function __construct(
        protected DosenDashboardService $dashboardService
    ) {}

    /**
     * Display the Lecturer Dashboard page.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $term = Term::where('is_active', true)->first() ?? Term::latest()->first();

        $stats = $this->dashboardService->getStats($user, $term);
        $todaySchedule = $this->dashboardService->getTodaySchedule($user, $term);
        $pendingSubmissions = $this->dashboardService->getRecentPendingSubmissions($user, 5);
        $upcomingDeadlines = $this->dashboardService->getUpcomingDeadlines($user, 3);
        $recentAnnouncements = $this->dashboardService->getRecentAnnouncements($user, 3);
        $academicAnnouncements = $this->dashboardService->getAcademicAnnouncements(2);

        return view('pages.dosen.dashboard', compact(
            'user',
            'term',
            'stats',
            'todaySchedule',
            'pendingSubmissions',
            'upcomingDeadlines',
            'recentAnnouncements',
            'academicAnnouncements'
        ));
    }
}
