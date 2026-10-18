<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AcademicClass;
use App\Models\Course;
use App\Models\Meeting;
use App\Models\Term;
use App\Models\User;
use App\Services\Admin\ClassService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class AdminClassController extends Controller
{
    public function __construct(
        protected ClassService $classService
    ) {}

    /**
     * Tampilkan daftar kelas perkuliahan (Screenshot 4).
     */
    public function index(Request $request): View
    {
        $status = $request->query('status', 'semua');
        $activeTerm = Term::where('status', 'aktif')->orWhere('is_active', true)->first();
        
        // Jika filter term_id tidak diberikan dan bukan memilih 'semua' semester, arahkan ke semester aktif sebagai default
        $termId = $request->has('term_id')
            ? ($request->query('term_id') === '' || $request->query('term_id') === 'semua' ? null : $request->integer('term_id'))
            : $activeTerm?->id;

        $courseId = $request->integer('course_id') ?: null;
        $lecturerId = $request->integer('lecturer_id') ?: null;
        $search = $request->query('q');

        $classes = $this->classService->getClassesList(
            status: $status,
            termId: $termId,
            courseId: $courseId,
            lecturerId: $lecturerId,
            search: $search
        );

        $summary = $this->classService->getClassSummary($termId);
        $terms = Term::orderBy('start_date', 'desc')->get();
        $courses = Course::orderBy('code')->get();
        $lecturers = User::where('role', UserRole::Lecturer)->orderBy('name')->get();

        return view('pages.admin.kelas.index', [
            'classes' => $classes,
            'summary' => $summary,
            'terms' => $terms,
            'courses' => $courses,
            'lecturers' => $lecturers,
            'activeTerm' => $activeTerm,
            'selectedStatus' => $status,
            'selectedTermId' => $termId,
            'selectedCourseId' => $courseId,
            'selectedLecturerId' => $lecturerId,
            'searchQuery' => $search,
        ]);
    }

    /**
     * Tampilkan form pembuatan kelas baru.
     */
    public function create(): View
    {
        $activeTerm = Term::where('status', 'aktif')->orWhere('is_active', true)->first();
        $terms = Term::orderBy('start_date', 'desc')->get();
        $courses = Course::orderBy('code')->get();
        $lecturers = User::where('role', UserRole::Lecturer)->orderBy('name')->get();

        return view('pages.admin.kelas.create', [
            'courses' => $courses,
            'terms' => $terms,
            'lecturers' => $lecturers,
            'activeTerm' => $activeTerm,
        ]);
    }

    /**
     * Simpan kelas perkuliahan baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'term_id' => 'required|exists:terms,id',
            'lecturer_id' => 'nullable|exists:users,id',
            'section' => 'required|string|max:20',
            'room' => 'nullable|string|max:50',
            'schedule_day' => 'nullable|string|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'capacity' => 'required|integer|min:5|max:150',
            'meetings_count' => 'nullable|integer|min:1|max:32',
        ], [
            'course_id.required' => 'Mata kuliah wajib dipilih.',
            'term_id.required' => 'Semester akademik wajib dipilih.',
            'section.required' => 'Nama/Seksi kelas wajib diisi (misal: IF-201-A).',
            'capacity.min' => 'Kapasitas kelas minimal 5 mahasiswa.',
            'capacity.max' => 'Kapasitas kelas maksimal 150 mahasiswa.',
        ]);

        try {
            $class = $this->classService->createClass($validated);
            return redirect()->route('admin.kelas.show', $class)
                ->with('success', "Kelas [{$class->course->code}] {$class->section} berhasil dibuka dengan 16 pertemuan otomatis.");
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Tampilkan detail kelas lengkap dengan 4 tab navigasi.
     */
    public function show(AcademicClass $class): View
    {
        $class->load([
            'course.curriculum.program',
            'term',
            'lecturer',
            'activeEnrollments.user',
            'meetings.presensi',
            'assessmentComponents',
            'materials',
            'assignments.submissions',
        ]);

        $lecturers = User::where('role', UserRole::Lecturer)->orderBy('name')->get();

        return view('pages.admin.kelas.show', [
            'class' => $class,
            'lecturers' => $lecturers,
        ]);
    }

    /**
     * Tampilkan form edit konfigurasi kelas.
     */
    public function edit(AcademicClass $class): View
    {
        $terms = Term::orderBy('start_date', 'desc')->get();
        $courses = Course::orderBy('code')->get();
        $lecturers = User::where('role', UserRole::Lecturer)->orderBy('name')->get();

        return view('pages.admin.kelas.edit', [
            'class' => $class,
            'courses' => $courses,
            'terms' => $terms,
            'lecturers' => $lecturers,
        ]);
    }

    /**
     * Perbarui data kelas perkuliahan.
     */
    public function update(Request $request, AcademicClass $class): RedirectResponse
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'term_id' => 'required|exists:terms,id',
            'lecturer_id' => 'nullable|exists:users,id',
            'section' => 'required|string|max:20',
            'room' => 'nullable|string|max:50',
            'schedule_day' => 'nullable|string|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'capacity' => 'required|integer|min:5|max:150',
        ]);

        try {
            $this->classService->updateClass($class, $validated);
            return redirect()->route('admin.kelas.show', $class)
                ->with('success', "Informasi kelas [{$class->section}] berhasil diperbarui.");
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Selesaikan kelas perkuliahan (kunci status ke selesai).
     */
    public function finish(AcademicClass $class): RedirectResponse
    {
        try {
            $this->classService->finishClass($class);
            return redirect()->back()->with('success', "Kelas {$class->course->code} {$class->section} telah berhasil diselesaikan.");
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Hapus kelas perkuliahan yang belum memiliki peserta.
     */
    public function destroy(AcademicClass $class): RedirectResponse
    {
        try {
            $this->classService->deleteClass($class);
            return redirect()->route('admin.kelas.index')
                ->with('success', "Kelas perkuliahan berhasil dihapus dari sistem.");
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Endpoint API untuk pengecekan bentrok jadwal secara langsung (Live Schedule Checker).
     */
    public function checkConflict(Request $request): JsonResponse
    {
        $ignoreClassId = $request->integer('class_id') ?: null;
        $termId = $request->integer('term_id');
        $scheduleDay = $request->input('schedule_day', '');
        $startTime = $request->input('start_time', '');
        $endTime = $request->input('end_time', '');
        $room = $request->input('room');
        $lecturerId = $request->integer('lecturer_id') ?: null;

        if (! $termId || ! $scheduleDay || ! $startTime || ! $endTime) {
            return response()->json([
                'has_conflict' => false,
                'message' => 'Lengkapi hari dan jam untuk memeriksa bentrok.',
            ]);
        }

        $conflict = $this->classService->checkScheduleConflict(
            ignoreClassId: $ignoreClassId,
            termId: $termId,
            scheduleDay: $scheduleDay,
            startTime: $startTime,
            endTime: $endTime,
            room: $room,
            lecturerId: $lecturerId
        );

        return response()->json($conflict);
    }

    /**
     * Perbarui jadwal tanggal / topik pertemuan perkuliahan tertentu.
     */
    public function updateMeeting(Request $request, AcademicClass $class, Meeting $meeting): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'date' => 'required|date',
            'description' => 'nullable|string|max:1000',
            'mode' => 'required|string|in:offline,online',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
        ]);

        try {
            $this->classService->updateMeeting($meeting, $validated);
            return redirect()->route('admin.kelas.show', ['class' => $class, 'tab' => 'pertemuan'])
                ->with('success', "Jadwal Pertemuan pekan ke-{$meeting->week} berhasil disesuaikan.");
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
