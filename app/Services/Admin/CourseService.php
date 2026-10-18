<?php

namespace App\Services\Admin;

use App\Models\Course;
use App\Models\Curriculum;
use App\Models\Program;
use App\Models\Term;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CourseService
{
    /**
     * Dapatkan daftar mata kuliah dengan filter program studi dan pencarian kata kunci.
     */
    public function getCoursesList(?int $programId = null, ?string $search = null)
    {
        $activeTerm = Term::where('status', 'aktif')->orWhere('is_active', true)->first();
        $activeTermId = $activeTerm?->id;

        return Course::query()
            ->with(['curriculum.program'])
            ->withCount([
                'classes as total_classes_count',
                'classes as active_classes_count' => function ($q) use ($activeTermId) {
                    if ($activeTermId) {
                        $q->where('term_id', $activeTermId);
                    }
                },
            ])
            ->when($programId, function ($q) use ($programId) {
                $q->whereHas('curriculum', fn ($cq) => $cq->where('program_id', $programId));
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->orderBy('semester')
            ->orderBy('code')
            ->get();
    }

    /**
     * Dapatkan statistik ringkasan mata kuliah dan distribusi prodi.
     */
    public function getCourseSummary(): array
    {
        $all = Course::with('curriculum.program')->get();
        $activeTerm = Term::where('status', 'aktif')->orWhere('is_active', true)->first();

        $totalMk = $all->count();
        $totalSks = (int) $all->sum('credits');
        
        $activeClassesCount = \App\Models\AcademicClass::query()
            ->when($activeTerm, fn ($q) => $q->where('term_id', $activeTerm->id))
            ->count();

        // Distribusi per Program Studi
        $programs = Program::withCount('curricula')->get();
        $prodiDistribusi = [];

        foreach ($programs as $prog) {
            $mkCount = Course::whereHas('curriculum', fn ($q) => $q->where('program_id', $prog->id))->count();
            if ($mkCount > 0 || $programs->count() === 1) {
                $prodiDistribusi[] = [
                    'program_id' => $prog->id,
                    'name' => $prog->name,
                    'code' => $prog->code,
                    'count' => $mkCount,
                    'percentage' => $totalMk > 0 ? round(($mkCount / $totalMk) * 100) : 0,
                ];
            }
        }

        return [
            'totalMk' => $totalMk,
            'totalSks' => $totalSks,
            'activeClassesCount' => $activeClassesCount,
            'prodiDistribusi' => $prodiDistribusi,
            'programs' => $programs,
        ];
    }

    /**
     * Buat master mata kuliah baru.
     */
    public function createCourse(array $data): Course
    {
        return DB::transaction(function () use ($data) {
            $code = strtoupper(trim($data['code']));

            if (Course::where('code', $code)->exists()) {
                throw new InvalidArgumentException("Kode mata kuliah '{$code}' sudah terdaftar dalam kurikulum.");
            }

            $curriculumId = $data['curriculum_id'] ?? Curriculum::first()?->id;

            $course = Course::create([
                'curriculum_id' => $curriculumId,
                'code' => $code,
                'name' => trim($data['name']),
                'credits' => (int) $data['credits'],
                'semester' => (int) ($data['semester'] ?? 1),
                'description' => $data['description'] ?? null,
                'syllabus' => $data['syllabus'] ?? null,
            ]);

            ActivityLogger::log(
                aksi: 'buat_mata_kuliah',
                kategori: 'akademik',
                subjek: $course,
                deskripsi: "Menambahkan mata kuliah baru: {$course->code} - {$course->name} ({$course->credits} SKS)",
                properti: [
                    'code' => $course->code,
                    'name' => $course->name,
                    'credits' => $course->credits,
                    'semester' => $course->semester,
                ]
            );

            return $course;
        });
    }

    /**
     * Perbarui data mata kuliah.
     */
    public function updateCourse(Course $course, array $data): Course
    {
        return DB::transaction(function () use ($course, $data) {
            $code = strtoupper(trim($data['code'] ?? $course->code));

            if ($code !== $course->code && Course::where('code', $code)->where('id', '!=', $course->id)->exists()) {
                throw new InvalidArgumentException("Kode mata kuliah '{$code}' sudah digunakan oleh mata kuliah lain.");
            }

            $course->update([
                'code' => $code,
                'name' => trim($data['name'] ?? $course->name),
                'credits' => isset($data['credits']) ? (int) $data['credits'] : $course->credits,
                'semester' => isset($data['semester']) ? (int) $data['semester'] : $course->semester,
                'curriculum_id' => $data['curriculum_id'] ?? $course->curriculum_id,
                'description' => $data['description'] ?? $course->description,
                'syllabus' => $data['syllabus'] ?? $course->syllabus,
            ]);

            ActivityLogger::log(
                aksi: 'perbarui_mata_kuliah',
                kategori: 'akademik',
                subjek: $course,
                deskripsi: "Memperbarui informasi mata kuliah {$course->code} - {$course->name}",
                properti: $course->only(['code', 'name', 'credits', 'semester'])
            );

            return $course;
        });
    }

    /**
     * Hapus mata kuliah hanya jika belum memiliki kelas perkuliahan.
     */
    public function deleteCourse(Course $course): bool
    {
        if ($course->classes()->exists()) {
            throw new InvalidArgumentException(
                "Mata kuliah {$course->code} ({$course->name}) tidak dapat dihapus karena telah memiliki " .
                "{$course->classes()->count()} kelas perkuliahan yang tercatat di sistem."
            );
        }

        return DB::transaction(function () use ($course) {
            $code = $course->code;
            $name = $course->name;
            $id = $course->id;

            $course->delete();

            ActivityLogger::log(
                aksi: 'hapus_mata_kuliah',
                kategori: 'akademik',
                subjek: null,
                deskripsi: "Menghapus mata kuliah: {$code} - {$name}",
                properti: ['deleted_course_id' => $id, 'code' => $code, 'name' => $name]
            );

            return true;
        });
    }
}
