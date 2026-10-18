<?php

namespace App\Services\Admin;

use App\Models\Campus;
use App\Models\Term;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SemesterService
{
    /**
     * Ambil daftar semester dengan filter status dan pencarian.
     */
    public function getSemestersList(?string $status = null, ?string $search = null)
    {
        return Term::query()
            ->withCount('classes')
            ->when($status && in_array($status, ['aktif', 'belum_aktif', 'selesai'], true), function ($q) use ($status) {
                if ($status === 'aktif') {
                    $q->where(function ($sub) {
                        $sub->where('status', 'aktif')->orWhere('is_active', true);
                    });
                } else {
                    $q->where('status', $status);
                }
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('academic_year', 'like', "%{$search}%");
                });
            })
            ->orderByRaw("CASE WHEN status = 'aktif' OR is_active = 1 THEN 0 WHEN status = 'belum_aktif' THEN 1 ELSE 2 END")
            ->orderBy('start_date', 'desc')
            ->get();
    }

    /**
     * Ringkasan kuantitas semester untuk kartu statistik.
     */
    public function getSemesterSummary(): array
    {
        $all = Term::all();

        $aktif = $all->first(fn ($t) => $t->status === 'aktif' || $t->is_active);
        $totalCount = $all->count();
        $aktifCount = $aktif ? 1 : 0;
        $belumAktifCount = $all->where('status', 'belum_aktif')->where('is_active', false)->count();
        $selesaiCount = $all->where('status', 'selesai')->count();

        return [
            'total' => $totalCount,
            'aktif' => $aktifCount,
            'belum_aktif' => $belumAktifCount,
            'selesai' => $selesaiCount,
            'currentActive' => $aktif,
        ];
    }

    /**
     * Buat semester akademik baru.
     */
    public function createSemester(array $data): Term
    {
        return DB::transaction(function () use ($data) {
            $campusId = $data['campus_id'] ?? Campus::first()?->id;

            // Validasi duplikasi tahun ajaran dan tipe semester
            $exists = Term::where('academic_year', $data['academic_year'])
                ->where('semester_type', $data['semester_type'])
                ->exists();

            if ($exists) {
                throw new InvalidArgumentException("Semester {$data['academic_year']} untuk tipe tersebut sudah ada di sistem.");
            }

            $isActive = !empty($data['is_active']) || ($data['status'] ?? '') === 'aktif';
            $status = $isActive ? 'aktif' : ($data['status'] ?? 'belum_aktif');

            if ($isActive) {
                // Nonaktifkan semester lain
                Term::where('is_active', true)->orWhere('status', 'aktif')->update([
                    'is_active' => false,
                    'status' => 'belum_aktif',
                ]);
            }

            $term = Term::create([
                'campus_id' => $campusId,
                'name' => trim($data['name']),
                'academic_year' => trim($data['academic_year']),
                'semester_type' => $data['semester_type'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'status' => $status,
                'is_active' => $isActive,
            ]);

            ActivityLogger::log(
                aksi: 'buat_semester',
                kategori: 'akademik',
                subjek: $term,
                deskripsi: "Membuat semester baru: {$term->name} ({$term->academic_year})",
                properti: [
                    'name' => $term->name,
                    'academic_year' => $term->academic_year,
                    'status' => $term->status,
                ]
            );

            return $term;
        });
    }

    /**
     * Perbarui data semester akademik.
     */
    public function updateSemester(Term $term, array $data): Term
    {
        return DB::transaction(function () use ($term, $data) {
            // Cek duplikasi unique jika tahun ajaran / tipe semester berubah
            if (
                ($data['academic_year'] ?? $term->academic_year) !== $term->academic_year ||
                ($data['semester_type'] ?? $term->semester_type) !== $term->semester_type
            ) {
                $exists = Term::where('academic_year', $data['academic_year'])
                    ->where('semester_type', $data['semester_type'])
                    ->where('id', '!=', $term->id)
                    ->exists();

                if ($exists) {
                    throw new InvalidArgumentException("Semester {$data['academic_year']} untuk jenis tersebut sudah terdaftar.");
                }
            }

            $term->update([
                'name' => trim($data['name'] ?? $term->name),
                'academic_year' => trim($data['academic_year'] ?? $term->academic_year),
                'semester_type' => $data['semester_type'] ?? $term->semester_type,
                'start_date' => $data['start_date'] ?? $term->start_date,
                'end_date' => $data['end_date'] ?? $term->end_date,
            ]);

            ActivityLogger::log(
                aksi: 'perbarui_semester',
                kategori: 'akademik',
                subjek: $term,
                deskripsi: "Memperbarui kalender akademik semester: {$term->name}",
                properti: $term->only(['name', 'academic_year', 'semester_type', 'start_date', 'end_date'])
            );

            return $term;
        });
    }

    /**
     * Tetapkan satu semester menjadi semester aktif utama.
     */
    public function setActiveSemester(Term $term): Term
    {
        return DB::transaction(function () use ($term) {
            // Nonaktifkan semester lain
            Term::where('id', '!=', $term->id)
                ->where(function ($q) {
                    $q->where('is_active', true)->orWhere('status', 'aktif');
                })
                ->update([
                    'is_active' => false,
                    'status' => 'belum_aktif',
                ]);

            $term->update([
                'is_active' => true,
                'status' => 'aktif',
                'closed_at' => null,
            ]);

            ActivityLogger::log(
                aksi: 'aktifkan_semester',
                kategori: 'konfigurasi',
                subjek: $term,
                deskripsi: "Menetapkan {$term->name} sebagai Semester Aktif utama institusi",
                properti: ['term_id' => $term->id, 'name' => $term->name]
            );

            return $term;
        });
    }

    /**
     * Tutup semester dan kunci seluruh kelas di dalamnya (read-only permanen).
     */
    public function closeSemester(Term $term): Term
    {
        return DB::transaction(function () use ($term) {
            $term->update([
                'is_active' => false,
                'status' => 'selesai',
                'closed_at' => now(),
            ]);

            // Seluruh kelas pada semester ini otomatis berstatus selesai (read-only)
            $term->classes()->update([
                'status' => 'selesai',
            ]);

            ActivityLogger::log(
                aksi: 'tutup_semester',
                kategori: 'akademik',
                subjek: $term,
                deskripsi: "Menutup siklus perkuliahan semester {$term->name}. Seluruh kelas terkunci permanen (read-only)",
                properti: [
                    'term_id' => $term->id,
                    'name' => $term->name,
                    'classes_locked' => $term->classes()->count(),
                ]
            );

            return $term;
        });
    }

    /**
     * Hapus semester hanya jika belum memiliki kelas perkuliahan terdaftar.
     */
    public function deleteSemester(Term $term): bool
    {
        if ($term->classes()->exists()) {
            throw new InvalidArgumentException("Semester {$term->name} tidak dapat dihapus karena sudah memiliki {$term->classes()->count()} kelas perkuliahan terdaftar.");
        }

        if ($term->status === 'aktif' || $term->is_active) {
            throw new InvalidArgumentException("Semester yang sedang aktif tidak dapat dihapus. Silakan aktifkan semester lain terlebih dahulu.");
        }

        return DB::transaction(function () use ($term) {
            $termName = $term->name;
            $termId = $term->id;

            $term->delete();

            ActivityLogger::log(
                aksi: 'hapus_semester',
                kategori: 'akademik',
                subjek: null,
                deskripsi: "Menghapus semester kosong: {$termName}",
                properti: ['deleted_term_id' => $termId, 'name' => $termName]
            );

            return true;
        });
    }
}
