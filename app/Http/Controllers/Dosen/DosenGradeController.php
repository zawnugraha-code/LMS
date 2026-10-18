<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\AcademicClass;
use App\Models\Term;
use App\Services\Dosen\DosenGradeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DosenGradeController extends Controller
{
    protected DosenGradeService $gradeService;

    public function __construct(DosenGradeService $gradeService)
    {
        $this->gradeService = $gradeService;
    }

    /**
     * Display Buku Nilai overview dashboard matching Screenshot 2.
     */
    public function index(Request $request): View
    {
        $lecturer = $request->user();
        $activeTerm = Term::where('is_active', true)->first();

        $stats = $this->gradeService->getStats($lecturer);
        $donut = $this->gradeService->getDonutDistribution($lecturer);
        $defaultComponents = $this->gradeService->getDefaultComponents($lecturer);

        $tab = $request->query('tab', 'semua');
        $search = $request->query('search');

        $courses = $this->gradeService->getCoursesWithGradeSummary($lecturer, $tab, $search);

        return view('pages.dosen.buku-nilai.index', [
            'stats' => $stats,
            'donut' => $donut,
            'defaultComponents' => $defaultComponents,
            'courses' => $courses,
            'activeTab' => $tab,
            'search' => $search,
            'activeTerm' => $activeTerm,
        ]);
    }

    /**
     * Display detailed student grade matrix for a specific academic class.
     */
    public function show(Request $request, AcademicClass $class): View
    {
        $lecturer = $request->user();

        // Security authorization check
        if ($class->lecturer_id !== $lecturer->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengelola buku nilai kelas ini.');
        }

        $search = $request->query('search');
        $status = $request->query('status', 'semua');

        $data = $this->gradeService->getClassGradeMatrix($class, $search, $status);

        return view('pages.dosen.buku-nilai.show', array_merge($data, [
            'search' => $search,
            'activeStatus' => $status,
        ]));
    }

    /**
     * Update component weights for an academic class with 100% total validation.
     */
    public function updateComponents(Request $request, AcademicClass $class): RedirectResponse
    {
        $lecturer = $request->user();

        if ($class->lecturer_id !== $lecturer->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah komponen penilaian kelas ini.');
        }

        $request->validate([
            'components' => 'required|array',
            'components.*' => 'required|numeric|min:0|max:100',
        ]);

        $weights = $request->input('components');
        $totalWeight = array_sum($weights);

        if ((int) $totalWeight !== 100) {
            return back()->withInput()->with('error', "Total bobot komponen penilaian harus tepat 100%. Saat ini: {$totalWeight}%.");
        }

        $this->gradeService->updateAssessmentComponents($class, $weights);

        return back()->with('success', 'Pengaturan bobot komponen penilaian kelas berhasil disimpan.');
    }

    /**
     * Export class grade matrix to CSV.
     */
    public function export(Request $request, AcademicClass $class): StreamedResponse
    {
        $lecturer = $request->user();

        if ($class->lecturer_id !== $lecturer->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengekspor buku nilai kelas ini.');
        }

        return $this->gradeService->streamCsvExport($class);
    }
}
