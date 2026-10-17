<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\AcademicClass;
use App\Models\Term;
use App\Services\Dosen\DosenClassService;
use App\Services\Dosen\DosenDashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DosenClassController extends Controller
{
    public function __construct(
        protected DosenClassService $classService,
        protected DosenDashboardService $dashboardService
    ) {}

    /**
     * Display the Lecturer Classes page ("Kelas Saya").
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $term = Term::where('is_active', true)->first() ?? Term::latest()->first();
        $statusFilter = $request->query('status', 'semua');

        $courses = $this->classService->getCoursesWithClasses($user, $term, $statusFilter);
        $summary = $this->classService->getClassSummary($user, $term);
        $todaySchedule = $this->dashboardService->getTodaySchedule($user, $term);
        $recentAnnouncements = $this->dashboardService->getRecentAnnouncements($user, 3);

        return view('pages.dosen.kelas.index', compact(
            'user',
            'term',
            'courses',
            'summary',
            'todaySchedule',
            'recentAnnouncements',
            'statusFilter'
        ));
    }

    /**
     * Display the Lecturer Class Detail page.
     */
    public function show(AcademicClass $class): View
    {
        Gate::authorize('view', $class);

        $classDetail = $this->classService->getClassDetail($class);

        return view('pages.dosen.kelas.show', [
            'class' => $classDetail,
        ]);
    }
}
