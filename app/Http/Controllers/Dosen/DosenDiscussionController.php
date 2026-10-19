<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\AcademicClass;
use App\Models\DiscussionCategory;
use App\Models\DiscussionThread;
use App\Services\Dosen\DosenDiscussionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DosenDiscussionController extends Controller
{
    protected DosenDiscussionService $discussionService;

    public function __construct(DosenDiscussionService $discussionService)
    {
        $this->discussionService = $discussionService;
    }

    /**
     * Display the Split-Pane Discussion Forum overview matching Screenshot 3.
     */
    public function index(Request $request): View
    {
        $lecturer = $request->user();

        // 1. Get classes taught by lecturer for the left pane
        $classes = $this->discussionService->getClassesList($lecturer, $request->query('search_class'));

        if ($classes->isEmpty()) {
            abort(404, 'Tidak ada kelas yang diampu.');
        }

        // 2. Identify active selected class
        $classId = $request->query('class_id');
        if ($classId) {
            $selectedClass = AcademicClass::with(['course'])->where('lecturer_id', $lecturer->id)->find($classId);
        } else {
            // Default to IF2101 Kelas A if exists, else first class
            $selectedClass = AcademicClass::with(['course'])->where('lecturer_id', $lecturer->id)
                ->whereHas('course', fn ($q) => $q->where('code', 'IF2101'))
                ->where('section', 'Kelas A')
                ->first() ?? AcademicClass::with(['course'])->where('lecturer_id', $lecturer->id)->first();
        }

        if (! $selectedClass) {
            $selectedClass = AcademicClass::with(['course'])->where('lecturer_id', $lecturer->id)->firstOrFail();
        }

        // 3. Threads for the middle pane
        $filters = [
            'kategori' => $request->query('kategori', 'semua'),
            'search' => $request->query('search'),
            'sort' => $request->query('sort', 'terbaru'),
        ];

        $threads = $this->discussionService->getThreads($selectedClass, $filters, 10, $lecturer);

        // 4. Widgets for the right pane
        $stats = $this->discussionService->getDiscussionStats($selectedClass);
        $recentThreads = $this->discussionService->getRecentThreads($selectedClass, 5);
        $announcements = $this->discussionService->getRecentAnnouncements($selectedClass, 3);
        $categories = DiscussionCategory::all();

        return view('pages.dosen.diskusi.index', [
            'classes' => $classes,
            'selectedClass' => $selectedClass,
            'threads' => $threads,
            'stats' => $stats,
            'recentThreads' => $recentThreads,
            'announcements' => $announcements,
            'categories' => $categories,
            'activeKategori' => $filters['kategori'],
            'search' => $filters['search'],
            'sort' => $filters['sort'],
            'searchClass' => $request->query('search_class'),
        ]);
    }

    /**
     * Display a single discussion thread with conversation flow.
     */
    public function show(Request $request, DiscussionThread $thread): View
    {
        $lecturer = $request->user();

        // Security check
        if ($thread->academicClass?->lecturer_id !== $lecturer->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat diskusi kelas ini.');
        }

        // Mark as read
        $this->discussionService->markAsRead($lecturer, $thread);

        $thread->load([
            'user',
            'category',
            'academicClass.course',
            'replies' => fn ($q) => $q->whereNull('parent_id')->with(['user', 'children.user'])->orderBy('created_at', 'asc'),
        ]);

        $isFavorited = $thread->isFavoritedBy($lecturer);

        return view('pages.dosen.diskusi.show', [
            'thread' => $thread,
            'class' => $thread->academicClass,
            'isFavorited' => $isFavorited,
        ]);
    }

    /**
     * Create a new discussion thread.
     */
    public function store(Request $request, AcademicClass $class): RedirectResponse
    {
        $lecturer = $request->user();

        if ($class->lecturer_id !== $lecturer->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk membuat diskusi di kelas ini.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'kategori' => 'required|string|in:tanya_jawab,pengumuman,diskusi_umum',
            'is_pinned' => 'nullable|boolean',
            'is_locked' => 'nullable|boolean',
            'is_private' => 'nullable|boolean',
        ]);

        $this->discussionService->createThread($lecturer, $class, $validated);

        return redirect()->route('dosen.diskusi.index', ['class_id' => $class->id])
            ->with('success', 'Topik diskusi baru berhasil dipublikasikan.');
    }

    /**
     * Post a reply to a discussion thread.
     */
    public function reply(Request $request, DiscussionThread $thread): RedirectResponse
    {
        $lecturer = $request->user();

        if ($thread->academicClass?->lecturer_id !== $lecturer->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk membalas diskusi ini.');
        }

        $request->validate([
            'content' => 'required|string',
            'parent_id' => 'nullable|exists:discussion_replies,id',
        ]);

        try {
            $this->discussionService->replyThread($lecturer, $thread, $request->input('content'), $request->input('parent_id'));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Balasan Anda berhasil dikirim.');
    }

    /**
     * Toggle lock/unlock status of a thread.
     */
    public function toggleLock(Request $request, DiscussionThread $thread): RedirectResponse
    {
        $lecturer = $request->user();

        if ($thread->academicClass?->lecturer_id !== $lecturer->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengunci diskusi ini.');
        }

        $isLocked = $this->discussionService->toggleLock($thread);

        return back()->with('success', $isLocked ? 'Diskusi berhasil dikunci dari balasan baru.' : 'Kunci diskusi berhasil dibuka kembali.');
    }

    /**
     * Toggle pin/unpin status of a thread.
     */
    public function togglePin(Request $request, DiscussionThread $thread): RedirectResponse
    {
        $lecturer = $request->user();

        if ($thread->academicClass?->lecturer_id !== $lecturer->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk menyematkan diskusi ini.');
        }

        $isPinned = $this->discussionService->togglePin($thread);

        return back()->with('success', $isPinned ? 'Diskusi berhasil disematkan di posisi teratas.' : 'Penyematan diskusi berhasil dilepas.');
    }

    /**
     * Toggle star favorite status of a thread.
     */
    public function toggleFavorite(Request $request, DiscussionThread $thread): JsonResponse|RedirectResponse
    {
        $lecturer = $request->user();

        if ($thread->academicClass?->lecturer_id !== $lecturer->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk memfavoritkan diskusi kelas ini.');
        }

        $isFav = $this->discussionService->toggleFavorite($lecturer, $thread);

        if ($request->wantsJson()) {
            return response()->json(['favorited' => $isFav]);
        }

        return back()->with('success', $isFav ? 'Diskusi ditambahkan ke favorit.' : 'Diskusi dihapus dari favorit.');
    }
}
