<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Term;
use App\Services\Admin\SemesterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class AdminSemesterController extends Controller
{
    public function __construct(
        protected SemesterService $semesterService
    ) {}

    /**
     * Tampilkan halaman kelola semester & kalender akademik.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('q');

        $semesters = $this->semesterService->getSemestersList($status, $search);
        $summary = $this->semesterService->getSemesterSummary();

        return view('pages.admin.semester.index', [
            'semesters' => $semesters,
            'summary' => $summary,
            'currentStatus' => $status,
            'searchQuery' => $search,
        ]);
    }

    /**
     * Simpan semester akademik baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'academic_year' => ['required', 'string', 'regex:/^\d{4}\/\d{4}$/'],
            'semester_type' => 'required|in:odd,even,short',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'nullable|boolean',
        ], [
            'academic_year.regex' => 'Format tahun ajaran harus YYYY/YYYY (misal: 2026/2027).',
            'end_date.after' => 'Tanggal selesai harus setelah tanggal mulai perkuliahan.',
        ]);

        try {
            $term = $this->semesterService->createSemester($validated);
            return redirect()->route('admin.semester.index')->with('success', "Semester [{$term->name}] berhasil dibuat.");
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Perbarui data semester akademik.
     */
    public function update(Request $request, Term $term): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'academic_year' => ['required', 'string', 'regex:/^\d{4}\/\d{4}$/'],
            'semester_type' => 'required|in:odd,even,short',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ], [
            'academic_year.regex' => 'Format tahun ajaran harus YYYY/YYYY (misal: 2026/2027).',
            'end_date.after' => 'Tanggal selesai harus setelah tanggal mulai perkuliahan.',
        ]);

        try {
            $this->semesterService->updateSemester($term, $validated);
            return redirect()->route('admin.semester.index')->with('success', "Semester [{$term->name}] berhasil diperbarui.");
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Tetapkan semester menjadi semester aktif utama.
     */
    public function activate(Term $term): RedirectResponse
    {
        try {
            $this->semesterService->setActiveSemester($term);
            return redirect()->route('admin.semester.index')->with('success', "Semester [{$term->name}] telah ditetapkan sebagai Semester Aktif.");
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Tutup semester dan kunci seluruh kelas di dalamnya.
     */
    public function close(Term $term): RedirectResponse
    {
        try {
            $this->semesterService->closeSemester($term);
            return redirect()->route('admin.semester.index')->with('success', "Semester [{$term->name}] berhasil ditutup. Seluruh kelas telah terkunci permanen.");
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Hapus semester kosong.
     */
    public function destroy(Term $term): RedirectResponse
    {
        try {
            $this->semesterService->deleteSemester($term);
            return redirect()->route('admin.semester.index')->with('success', "Semester berhasil dihapus.");
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
