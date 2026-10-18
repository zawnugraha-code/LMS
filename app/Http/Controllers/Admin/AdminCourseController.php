<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Curriculum;
use App\Services\Admin\CourseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class AdminCourseController extends Controller
{
    public function __construct(
        protected CourseService $courseService
    ) {}

    /**
     * Tampilkan katalog master mata kuliah.
     */
    public function index(Request $request): View
    {
        $programId = $request->integer('program_id') ?: null;
        $search = $request->query('q');

        $courses = $this->courseService->getCoursesList($programId, $search);
        $summary = $this->courseService->getCourseSummary();
        $curriculums = Curriculum::with('program')->get();

        return view('pages.admin.mata-kuliah.index', [
            'courses' => $courses,
            'summary' => $summary,
            'curriculums' => $curriculums,
            'selectedProgramId' => $programId,
            'searchQuery' => $search,
        ]);
    }

    /**
     * Simpan mata kuliah baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:courses,code',
            'name' => 'required|string|max:150',
            'credits' => 'required|integer|min:1|max:6',
            'semester' => 'required|integer|min:1|max:8',
            'curriculum_id' => 'nullable|exists:curricula,id',
            'description' => 'nullable|string|max:1000',
            'syllabus' => 'nullable|string|max:2000',
        ], [
            'code.unique' => 'Kode mata kuliah ini sudah terdaftar.',
            'credits.min' => 'Bobot SKS minimal 1.',
            'credits.max' => 'Bobot SKS maksimal 6.',
        ]);

        try {
            $course = $this->courseService->createCourse($validated);
            return redirect()->route('admin.mata-kuliah.index')->with('success', "Mata kuliah [{$course->code}] {$course->name} berhasil ditambahkan.");
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Perbarui data mata kuliah.
     */
    public function update(Request $request, Course $course): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:courses,code,' . $course->id,
            'name' => 'required|string|max:150',
            'credits' => 'required|integer|min:1|max:6',
            'semester' => 'required|integer|min:1|max:8',
            'curriculum_id' => 'nullable|exists:curricula,id',
            'description' => 'nullable|string|max:1000',
            'syllabus' => 'nullable|string|max:2000',
        ]);

        try {
            $this->courseService->updateCourse($course, $validated);
            return redirect()->route('admin.mata-kuliah.index')->with('success', "Data mata kuliah [{$course->code}] berhasil diperbarui.");
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Hapus mata kuliah yang belum memiliki kelas.
     */
    public function destroy(Course $course): RedirectResponse
    {
        try {
            $this->courseService->deleteCourse($course);
            return redirect()->route('admin.mata-kuliah.index')->with('success', "Mata kuliah berhasil dihapus dari kurikulum.");
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
