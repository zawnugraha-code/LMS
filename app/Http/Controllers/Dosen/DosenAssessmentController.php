<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\AcademicClass;
use App\Models\Assignment;
use App\Models\Term;
use App\Services\Dosen\DosenAssessmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DosenAssessmentController extends Controller
{
    public function __construct(
        protected DosenAssessmentService $assessmentService
    ) {}

    /**
     * Display the assessment list page (Tugas & Ujian).
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $term = Term::where('is_active', true)->first() ?? Term::latest()->first();

        $filters = [
            'jenis' => $request->query('jenis', 'semua'),
            'course_id' => $request->query('course_id', 'semua'),
            'class_id' => $request->query('class_id', 'semua'),
            'status' => $request->query('status', 'semua'),
            'search' => $request->query('search', ''),
        ];

        $assessments = $this->assessmentService->getAssessments($user, $filters, 10);
        $summary = $this->assessmentService->getSummary($user, $term);
        $pubStats = $this->assessmentService->getPublicationStats($user, $term);
        $upcoming = $this->assessmentService->getUpcomingAssessments($user, 4);

        // Get lecturer classes for filter dropdown
        $myClasses = AcademicClass::with('course')
            ->where('lecturer_id', $user->id)
            ->where('term_id', $term->id)
            ->get();

        $myCourses = $myClasses->pluck('course')->unique('id');

        return view('pages.dosen.tugas.index', compact(
            'user',
            'term',
            'filters',
            'assessments',
            'summary',
            'pubStats',
            'upcoming',
            'myClasses',
            'myCourses'
        ));
    }

    /**
     * Show form to create a new assessment.
     */
    public function create(Request $request): View
    {
        Gate::authorize('create', Assignment::class);

        $user = Auth::user();
        $term = Term::where('is_active', true)->first() ?? Term::latest()->first();

        $classes = AcademicClass::with('course')
            ->where('lecturer_id', $user->id)
            ->where('term_id', $term->id)
            ->where('status', 'aktif')
            ->get();

        $defaultJenis = $request->query('jenis', 'tugas');

        return view('pages.dosen.tugas.create', compact('classes', 'defaultJenis', 'term'));
    }

    /**
     * Store a new assessment in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Assignment::class);

        $user = Auth::user();

        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'title' => 'required|string|max:255',
            'jenis' => 'required|in:tugas,kuis,uts,uas',
            'mode' => 'required|in:individu,kelompok',
            'metode' => 'required|in:unggah_berkas,input_manual',
            'description' => 'nullable|string',
            'lampiran' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,zip,jpg,jpeg,png|max:10240',
            'max_score' => 'required|numeric|min:1|max:100',
            'mulai_at' => 'nullable|date',
            'due_at' => 'required|date|after_or_equal:mulai_at',
            'status' => 'required|in:draft,dipublikasikan,terjadwal',
            'publish_at' => 'nullable|date',
        ]);

        $class = AcademicClass::findOrFail($validated['class_id']);
        Gate::authorize('manage', $class);

        $this->assessmentService->storeAssessment($user, $validated);

        return redirect()->route('dosen.tugas.index')
            ->with('success', 'Assessment berhasil dibuat.');
    }

    /**
     * Show form to edit an assessment.
     */
    public function edit(Assignment $assignment): View
    {
        Gate::authorize('update', $assignment);

        $user = Auth::user();
        $term = Term::where('is_active', true)->first() ?? Term::latest()->first();

        $classes = AcademicClass::with('course')
            ->where('lecturer_id', $user->id)
            ->where('term_id', $term->id)
            ->where('status', 'aktif')
            ->get();

        return view('pages.dosen.tugas.edit', compact('assignment', 'classes', 'term'));
    }

    /**
     * Update the specified assessment in storage.
     */
    public function update(Request $request, Assignment $assignment): RedirectResponse
    {
        Gate::authorize('update', $assignment);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'jenis' => 'required|in:tugas,kuis,uts,uas',
            'mode' => 'required|in:individu,kelompok',
            'metode' => 'required|in:unggah_berkas,input_manual',
            'description' => 'nullable|string',
            'lampiran' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,zip,jpg,jpeg,png|max:10240',
            'max_score' => 'required|numeric|min:1|max:100',
            'mulai_at' => 'nullable|date',
            'due_at' => 'required|date|after_or_equal:mulai_at',
            'status' => 'required|in:draft,dipublikasikan,terjadwal,arsip',
            'publish_at' => 'nullable|date',
        ]);

        $this->assessmentService->updateAssessment($assignment, $validated);

        return redirect()->route('dosen.tugas.index')
            ->with('success', 'Assessment berhasil diperbarui.');
    }

    /**
     * Publish an assessment immediately.
     */
    public function publish(Assignment $assignment): RedirectResponse
    {
        Gate::authorize('update', $assignment);

        $this->assessmentService->publishNow($assignment);

        return redirect()->back()
            ->with('success', "Assessment '{$assignment->title}' berhasil dipublikasikan.");
    }

    /**
     * Archive an assessment.
     */
    public function archive(Assignment $assignment): RedirectResponse
    {
        Gate::authorize('update', $assignment);

        $this->assessmentService->archive($assignment);

        return redirect()->back()
            ->with('success', "Assessment '{$assignment->title}' berhasil diarsipkan.");
    }

    /**
     * Delete an assessment.
     */
    public function destroy(Assignment $assignment): RedirectResponse
    {
        Gate::authorize('delete', $assignment);

        $deleted = $this->assessmentService->delete($assignment);

        if (! $deleted) {
            return redirect()->back()
                ->with('error', 'Tidak dapat menghapus assessment yang sudah memiliki riwayat pengumpulan tugas dari mahasiswa.');
        }

        return redirect()->route('dosen.tugas.index')
            ->with('success', 'Assessment berhasil dihapus.');
    }
}
