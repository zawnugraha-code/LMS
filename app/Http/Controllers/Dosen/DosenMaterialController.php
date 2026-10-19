<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\AcademicClass;
use App\Models\Announcement;
use App\Models\Material;
use App\Models\Term;
use App\Services\Dosen\DosenMaterialService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DosenMaterialController extends Controller
{
    public function __construct(
        protected DosenMaterialService $materialService
    ) {}

    /**
     * Display the materials management page for lecturer.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $term = Term::where('is_active', true)->first() ?? Term::latest()->first();

        $classes = $this->materialService->getClassesForLecturer($user, $term);
        $activeClass = $this->materialService->resolveActiveClass($user, $classes, $request->query('class_id'));

        $filters = [
            'type' => $request->query('type', 'semua'),
            'status' => $request->query('status', 'semua'),
            'search' => $request->query('search', ''),
        ];

        $meetings = $activeClass ? $this->materialService->getMaterialsByMeeting($activeClass, $filters) : collect();
        $summary = $activeClass ? $this->materialService->getMaterialSummary($activeClass) : [
            'total' => 0,
            'published' => 0,
            'draft' => 0,
            'meetings_filled' => 0,
            'total_meetings' => 16,
            'percent_published' => 0,
        ];

        $announcements = $activeClass ? $this->materialService->getAnnouncementsForClass($activeClass, $user) : collect();

        return view('pages.dosen.materi.index', compact(
            'user',
            'term',
            'classes',
            'activeClass',
            'meetings',
            'summary',
            'announcements',
            'filters'
        ));
    }

    /**
     * Store new learning material for a class meeting.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'week' => 'required|integer|min:1|max:16',
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:pdf,ppt,slide,video,tautan,link,zip,dokumen',
            'content' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,zip,jpg,jpeg,png|max:10240',
            'external_url' => 'nullable|url|max:255',
            'status' => 'required|in:published,draft',
            'is_downloadable' => 'nullable|boolean',
        ]);

        $class = AcademicClass::findOrFail($validated['class_id']);
        Gate::authorize('manage', $class);

        $material = $this->materialService->storeMaterial(Auth::user(), $validated, $request->file('file'));

        return redirect()->route('dosen.materi.index', ['class_id' => $class->id])
            ->with('success', "Materi \"{$material->title}\" berhasil ditambahkan.");
    }

    /**
     * Update an existing learning material.
     */
    public function update(Request $request, Material $material): RedirectResponse
    {
        Gate::authorize('manage', $material);

        $validated = $request->validate([
            'week' => 'required|integer|min:1|max:16',
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:pdf,ppt,slide,video,tautan,link,zip,dokumen',
            'content' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,zip,jpg,jpeg,png|max:10240',
            'external_url' => 'nullable|url|max:255',
            'status' => 'required|in:published,draft',
            'is_downloadable' => 'nullable|boolean',
        ]);

        $this->materialService->updateMaterial($material, $validated, $request->file('file'));

        $classId = $material->class_id ?? $material->meeting?->class_id;

        return redirect()->route('dosen.materi.index', ['class_id' => $classId])
            ->with('success', "Materi \"{$material->title}\" berhasil diperbarui.");
    }

    /**
     * Toggle status between published and draft.
     */
    public function togglePublish(Material $material): RedirectResponse
    {
        Gate::authorize('manage', $material);

        $this->materialService->togglePublish($material);

        $statusLabel = $material->status === 'published' ? 'dipublikasikan' : 'disimpan sebagai draft';
        $classId = $material->class_id ?? $material->meeting?->class_id;

        return redirect()->route('dosen.materi.index', ['class_id' => $classId])
            ->with('success', "Status materi \"{$material->title}\" kini {$statusLabel}.");
    }

    /**
     * Delete a material.
     */
    public function destroy(Material $material): RedirectResponse
    {
        Gate::authorize('manage', $material);

        $title = $material->title;
        $classId = $material->class_id ?? $material->meeting?->class_id;

        $this->materialService->deleteMaterial($material);

        return redirect()->route('dosen.materi.index', ['class_id' => $classId])
            ->with('success', "Materi \"{$title}\" berhasil dihapus.");
    }

    /**
     * Store an announcement for a class or all classes.
     */
    public function storeAnnouncement(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'class_id' => 'nullable',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $this->materialService->storeAnnouncement(Auth::user(), $validated);

        return redirect()->back()
            ->with('success', "Pengumuman \"{$validated['title']}\" berhasil dipublikasikan.");
    }

    /**
     * Delete an announcement.
     */
    public function destroyAnnouncement(Announcement $announcement): RedirectResponse
    {
        $user = Auth::user();

        // Check ownership
        if ($announcement->author_id !== $user->id && $announcement->academicClass?->lecturer_id !== $user->id) {
            abort(403, 'Anda tidak berhak menghapus pengumuman ini.');
        }

        $title = $announcement->title;
        $this->materialService->deleteAnnouncement($announcement, $user);

        return redirect()->back()
            ->with('success', "Pengumuman \"{$title}\" berhasil dihapus.");
    }
}
