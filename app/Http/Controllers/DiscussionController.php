<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\AcademicClass;
use App\Models\Campus;
use App\Models\DiscussionReply;
use App\Models\DiscussionThread;
use App\Models\User;
use App\Services\DiscussionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DiscussionController extends Controller
{
    public function __construct(
        protected DiscussionService $discussionService
    ) {}

    /**
     * Display the main discussion forum index page.
     */
    public function index(Request $request): View
    {
        $user = $this->resolveUser();
        $campus = $user?->campus ?? Campus::first();

        $activeTab = $request->query('tab', 'all');
        $selectedCategory = $request->query('category', 'all');
        $searchQuery = $request->query('q', '');

        $threads = $this->discussionService->getThreads($selectedCategory, $searchQuery, $activeTab);
        $categories = $this->discussionService->getCategories();
        $stats = $this->discussionService->getDiscussionStats();
        $popularThreads = $this->discussionService->getPopularThreads(5);

        // Academic classes for dropdown in "Buat Diskusi Baru"
        $enrolledClasses = AcademicClass::with('course')
            ->whereHas('enrollments', fn ($q) => $q->where('user_id', $user->id))
            ->get();

        if ($enrolledClasses->isEmpty()) {
            $enrolledClasses = AcademicClass::with('course')->take(6)->get();
        }

        return view('pages.app.discussions', [
            'user' => $user,
            'campus' => $campus,
            'currentRole' => $user->role,
            'threads' => $threads,
            'categories' => $categories,
            'stats' => $stats,
            'popularThreads' => $popularThreads,
            'enrolledClasses' => $enrolledClasses,
            'activeTab' => $activeTab,
            'selectedCategory' => $selectedCategory,
            'searchQuery' => $searchQuery,
        ]);
    }

    /**
     * Display the dedicated thread detail subpage.
     */
    public function show(DiscussionThread $thread, Request $request): View
    {
        $user = $this->resolveUser();
        $campus = $user?->campus ?? Campus::first();

        // Record daily view
        $this->discussionService->recordView($thread, $user, $request->ip());

        $thread->load([
            'user',
            'category',
            'academicClass.course',
            'academicClass.lecturer',
            'replies' => fn ($q) => $q->with('user')->orderBy('is_solution', 'desc')->oldest(),
        ]);

        $categories = $this->discussionService->getCategories();
        $popularThreads = $this->discussionService->getPopularThreads(5);

        return view('pages.app.discussion-detail', [
            'user' => $user,
            'campus' => $campus,
            'currentRole' => $user->role,
            'thread' => $thread,
            'categories' => $categories,
            'popularThreads' => $popularThreads,
        ]);
    }

    /**
     * Store a newly created discussion thread.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $this->resolveUser();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:discussion_categories,id',
            'class_id' => 'nullable|exists:classes,id',
            'content' => 'required|string',
            'attachment' => 'nullable|file|max:10240',
        ]);

        $thread = $this->discussionService->createThread($user, $validated, $request->file('attachment'));

        return redirect()->route('app.discussions.show', $thread)
            ->with('success', 'Topik diskusi berhasil dipublikasikan! Anda mendapatkan +15 XP.');
    }

    /**
     * Store a reply to a discussion thread.
     */
    public function storeReply(Request $request, DiscussionThread $thread): RedirectResponse|JsonResponse
    {
        $user = $this->resolveUser();

        if ($thread->academicClass?->isFinished()) {
            abort(403, 'Kelas ini telah selesai. Diskusi telah dikunci.');
        }

        $validated = $request->validate([
            'content' => 'required|string|min:3',
            'parent_id' => 'nullable|exists:discussion_replies,id',
        ]);

        $reply = $this->discussionService->createReply($user, $thread, $validated['content'], $validated['parent_id'] ?? null);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tanggapan berhasil dikirim (+10 XP)',
                'reply' => $reply->load('user'),
            ]);
        }

        return redirect()->back()
            ->with('success', 'Tanggapan berhasil dikirim! Anda mendapatkan +10 XP.');
    }

    /**
     * Upvote a discussion thread.
     */
    public function upvoteThread(DiscussionThread $thread, Request $request): RedirectResponse|JsonResponse
    {
        $user = $this->resolveUser();
        $newCount = $this->discussionService->upvoteThread($user, $thread);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'upvotes_count' => $newCount,
                'message' => 'Apresiasi berhasil diberikan (+5 XP)',
            ]);
        }

        return redirect()->back()
            ->with('success', 'Apresiasi berhasil diberikan! Anda mendapatkan +5 XP.');
    }

    /**
     * Upvote a discussion reply.
     */
    public function upvoteReply(DiscussionReply $reply, Request $request): RedirectResponse|JsonResponse
    {
        $user = $this->resolveUser();
        $newCount = $this->discussionService->upvoteReply($user, $reply);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'upvotes_count' => $newCount,
                'message' => 'Apresiasi balasan berhasil dicatat (+5 XP)',
            ]);
        }

        return redirect()->back()
            ->with('success', 'Apresiasi balasan berhasil dicatat! Anda mendapatkan +5 XP.');
    }

    /**
     * Mark a reply as solution / best answer.
     */
    public function markSolution(DiscussionReply $reply, Request $request): RedirectResponse|JsonResponse
    {
        $user = $this->resolveUser();
        $marked = $this->discussionService->markAsSolution($user, $reply);

        if (! $marked) {
            return redirect()->back()->with('error', 'Hanya pembuat thread atau dosen yang dapat menandai jawaban terbaik.');
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Jawaban terbaik berhasil ditandai (+20 XP untuk pembuat jawaban)',
            ]);
        }

        return redirect()->back()
            ->with('success', 'Jawaban berhasil ditandai sebagai Jawaban Terbaik! (+20 XP)');
    }

    /**
     * Helper to resolve authenticated user or fallback default student.
     */
    protected function resolveUser(): User
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

        return Auth::user();
    }
}
