<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\AcademicClass;
use App\Models\Assignment;
use App\Models\Badge;
use App\Models\Campus;
use App\Models\Course;
use App\Models\Cpl;
use App\Models\Faculty;
use App\Models\OutcomeScore;
use App\Models\Program;
use App\Models\Quiz;
use App\Models\Submission;
use App\Models\User;
use App\Models\XpLedger;
use App\Services\ScheduleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected ScheduleService $scheduleService
    ) {}

    /**
     * Display the role-adaptive Arunika LMS dashboard.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $campus = $user?->campus ?? Campus::first();

        $data = [
            'user' => $user,
            'campus' => $campus,
            'currentRole' => $user->role,
            'allRoles' => UserRole::cases(),
        ];

        // Specific data per role
        switch ($user->role) {
            case UserRole::Lecturer:
                $data['classesTaught'] = AcademicClass::with(['course', 'term', 'enrollments'])
                    ->where('lecturer_id', $user->id)
                    ->get();
                $data['pendingSubmissions'] = Submission::with(['assignment.academicClass.course', 'user'])
                    ->where('status', 'submitted')
                    ->whereHas('assignment.academicClass', fn ($q) => $q->where('lecturer_id', $user->id))
                    ->latest()
                    ->take(5)
                    ->get();
                $data['cpls'] = Cpl::with('program')->take(5)->get();
                break;

            case UserRole::CampusAdmin:
            case UserRole::SuperAdmin:
                $data['stats'] = [
                    'faculties' => Faculty::count(),
                    'programs' => Program::count(),
                    'courses' => Course::count(),
                    'classes' => AcademicClass::count(),
                    'lecturers' => User::where('role', UserRole::Lecturer)->count(),
                    'students' => User::where('role', UserRole::Student)->count(),
                ];
                $data['programs'] = Program::with(['faculty', 'cpls'])->get();
                $data['recentClasses'] = AcademicClass::with(['course', 'lecturer'])->latest()->take(6)->get();
                break;

            case UserRole::Auditor:
                $data['programs'] = Program::with(['cpls', 'faculty'])->get();
                $data['cpls'] = Cpl::with('program')->get();
                $data['courses'] = Course::with(['cpmks.subCpmks', 'classes'])->take(6)->get();
                break;

            case UserRole::IndustryMentor:
                $data['internStudents'] = User::where('role', UserRole::Student)->take(4)->get();
                break;

            case UserRole::Staff:
                $data['advisees'] = User::where('role', UserRole::Student)->with('enrollments.academicClass.course')->get();
                break;

            case UserRole::Student:
            default:
                $data['enrolledClasses'] = AcademicClass::with(['course', 'lecturer', 'meetings.materials', 'assignments'])
                    ->whereHas('enrollments', fn ($q) => $q->where('user_id', $user->id)->accessible())
                    ->get();
                $data['assignments'] = Assignment::with(['academicClass.course'])
                    ->whereHas('academicClass.enrollments', fn ($q) => $q->where('user_id', $user->id)->accessible())
                    ->latest('due_at')
                    ->take(5)
                    ->get();
                $data['quizzes'] = Quiz::with(['academicClass.course', 'attempts'])
                    ->whereHas('academicClass.enrollments', fn ($q) => $q->where('user_id', $user->id)->accessible())
                    ->take(5)
                    ->get();
                $data['cplScores'] = OutcomeScore::with('cpl')
                    ->where('user_id', $user->id)
                    ->get();
                $data['badges'] = $user->badges;
                $data['allBadges'] = Badge::all();
                $data['recentXp'] = XpLedger::where('user_id', $user->id)->latest()->take(5)->get();
                $data['todayClasses'] = $this->scheduleService->getTodayClasses($user);
                $data['todayFormatted'] = now()->locale('id')->isoFormat('dddd, D MMM Y');
                $hour = (int) now()->format('H');
                $data['timeGreeting'] = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 18 ? 'Selamat sore' : 'Selamat malam'));
                break;
        }

        return view('pages.app.dashboard', $data);
    }
}
