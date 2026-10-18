<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\AcademicClass;
use App\Models\Campus;
use App\Models\User;
use App\Services\AcademicTermService;
use App\Services\CourseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CourseController extends Controller
{
    /**
     * Display the enrolled courses (Mata Kuliah) page.
     */
    public function index(
        Request $request,
        AcademicTermService $termService,
        CourseService $courseService
    ): View {
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
        $activeTerm = $termService->getActiveTerm();
        $availableTerms = $termService->getAvailableTerms();

        $summary = $courseService->getSummary($user, $activeTerm);
        $courses = $courseService->getCourses($user, $activeTerm);
        $filterOptions = $courseService->getFilterOptions($activeTerm);

        return view('pages.app.courses', [
            'user' => $user,
            'campus' => $campus,
            'currentRole' => $user->role,
            'activeTerm' => $activeTerm,
            'availableTerms' => $availableTerms,
            'summary' => $summary,
            'courses' => $courses,
            'filterOptions' => $filterOptions,
        ]);
    }

    /**
     * Display the course detail / RPS page.
     */
    public function show(AcademicClass $class): View
    {
        $user = Auth::user();
        if (! $user) {
            $user = User::where('role', UserRole::Student)->first();
            Auth::login($user);
        }

        // Authorize view via Policy
        Gate::authorize('view', $class);

        $class->load([
            'course.cpmks.subCpmks',
            'course.curriculum.program.faculty',
            'lecturer',
            'term',
            'meetings.materials',
            'assignments',
            'quizzes',
        ]);

        return view('pages.app.course-detail', [
            'user' => $user,
            'campus' => $user?->campus ?? Campus::first(),
            'currentRole' => $user->role,
            'class' => $class,
            'course' => $class->course,
            'lecturer' => $class->lecturer,
        ]);
    }

    /**
     * Enter the virtual classroom with 1-14 meetings timeline.
     */
    public function classroom(AcademicClass $class): View
    {
        $user = Auth::user();
        if (! $user) {
            $user = User::where('role', UserRole::Student)->first();
            Auth::login($user);
        }

        // Server-side lock and authorization guard
        if ($class->isLocked()) {
            abort(403, 'Mata kuliah ini belum dimulai atau masih berstatus Akan Datang.');
        }

        Gate::authorize('enterClassroom', $class);

        $class->load([
            'course.cpmks',
            'lecturer',
            'term',
            'meetings' => fn ($q) => $q->orderBy('week')->with([
                'materials.progress' => fn ($pq) => $pq->where('user_id', $user->id),
                'attendanceSession.logs' => fn ($aq) => $aq->where('user_id', $user->id),
            ]),
            'assignments' => fn ($q) => $q->with(['submissions' => fn ($sq) => $sq->where('user_id', $user->id)]),
            'quizzes' => fn ($q) => $q->with(['attempts' => fn ($aq) => $aq->where('user_id', $user->id)]),
        ]);

        return view('pages.app.classroom', [
            'user' => $user,
            'campus' => $user?->campus ?? Campus::first(),
            'currentRole' => $user->role,
            'class' => $class,
            'course' => $class->course,
            'lecturer' => $class->lecturer,
            'meetings' => $class->meetings,
        ]);
    }
}
