<?php

namespace App\Services\Admin;

use App\Enums\UserRole;
use App\Models\AcademicClass;
use App\Models\Enrollment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EnrollmentAdminService
{
    public function __construct(
        protected CsvImporterService $csvImporter
    ) {}

    /**
     * Dapatkan data pendaftaran mahasiswa kelas terpilih beserta filter tab dan ringkasan kuota.
     */
    public function getEnrollmentData(?int $classId = null, string $tab = 'terdaftar', ?string $search = null): array
    {
        $allClasses = AcademicClass::with(['course', 'term', 'lecturer'])
            ->orderBy('term_id', 'desc')
            ->orderBy('section')
            ->get();

        $selectedClass = null;
        if ($classId) {
            $selectedClass = AcademicClass::with([
                'course.curriculum.program',
                'term',
                'lecturer',
                'activeEnrollments.user',
            ])->find($classId);
        }

        if (! $selectedClass) {
            $selectedClass = $allClasses->firstWhere('status', 'aktif') ?? $allClasses->first();
            if ($selectedClass) {
                $selectedClass->load(['course.curriculum.program', 'term', 'lecturer', 'activeEnrollments.user']);
            }
        }

        if (! $selectedClass) {
            return [
                'selectedClass' => null,
                'allClasses' => collect(),
                'tab' => $tab,
                'items' => collect(),
                'countTerdaftar' => 0,
                'countDikeluarkan' => 0,
                'countBelumTerdaftar' => 0,
                'capacity' => 0,
                'enrolledCount' => 0,
                'remainingCapacity' => 0,
                'capacityPercentage' => 0,
                'remainingPercentage' => 0,
                'quickAddStudents' => collect(),
            ];
        }

        // Hitung kuota & metrik tab
        $countTerdaftar = $selectedClass->enrollments()->aktif()->count();
        $countDikeluarkan = $selectedClass->enrollments()->dikeluarkan()->count();

        $enrolledUserIds = $selectedClass->enrollments()
            ->aktif()
            ->pluck('user_id');

        $countBelumTerdaftar = User::where('role', UserRole::Student)
            ->where('is_active', true)
            ->whereNotIn('id', $enrolledUserIds)
            ->count();

        $capacity = (int) $selectedClass->capacity;
        $enrolledCount = $countTerdaftar;
        $remainingCapacity = max(0, $capacity - $enrolledCount);
        $capacityPercentage = $capacity > 0 ? (int) min(100, round(($enrolledCount / $capacity) * 100)) : 0;
        $remainingPercentage = max(0, 100 - $capacityPercentage);

        // Ambil data sesuai tab aktif
        $items = match ($tab) {
            'dikeluarkan' => $selectedClass->enrollments()
                ->where('status', 'dikeluarkan')
                ->with(['user', 'dikeluarkanOleh'])
                ->when($search, function ($q) use ($search) {
                    $q->whereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('identifier', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                })
                ->orderBy('dikeluarkan_at', 'desc')
                ->get(),

            'belum_terdaftar' => User::where('role', UserRole::Student)
                ->where('is_active', true)
                ->whereNotIn('id', $enrolledUserIds)
                ->when($search, function ($q) use ($search) {
                    $q->where(function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('identifier', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                })
                ->orderBy('name')
                ->paginate(30),

            default => $selectedClass->enrollments()
                ->aktif()
                ->with(['user'])
                ->when($search, function ($q) use ($search) {
                    $q->whereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('identifier', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                })
                ->orderBy('created_at', 'desc')
                ->get(),
        };

        // 5 Mahasiswa belum terdaftar untuk widget aksi cepat
        $quickAddStudents = User::where('role', UserRole::Student)
            ->where('is_active', true)
            ->whereNotIn('id', $enrolledUserIds)
            ->orderBy('name')
            ->limit(5)
            ->get();

        return [
            'selectedClass' => $selectedClass,
            'allClasses' => $allClasses,
            'tab' => $tab,
            'items' => $items,
            'countTerdaftar' => $countTerdaftar,
            'countDikeluarkan' => $countDikeluarkan,
            'countBelumTerdaftar' => $countBelumTerdaftar,
            'capacity' => $capacity,
            'enrolledCount' => $enrolledCount,
            'remainingCapacity' => $remainingCapacity,
            'capacityPercentage' => $capacityPercentage,
            'remainingPercentage' => $remainingPercentage,
            'quickAddStudents' => $quickAddStudents,
        ];
    }

    /**
     * Daftarkan mahasiswa ke dalam kelas perkuliahan.
     */
    public function enrollStudent(AcademicClass $class, int $userId): Enrollment
    {
        return DB::transaction(function () use ($class, $userId) {
            $user = User::findOrFail($userId);

            if ($user->role !== UserRole::Student && $user->role !== 'student') {
                throw new InvalidArgumentException("Pengguna {$user->name} bukan mahasiswa.");
            }

            // Periksa kapasitas kelas
            if ($class->isFull()) {
                throw new InvalidArgumentException(
                    "Kapasitas kelas {$class->course->code} {$class->section} telah penuh ({$class->capacity} mahasiswa)."
                );
            }

            // Periksa apakah mahasiswa sudah terdaftar di seksi lain untuk mata kuliah yang sama pada semester yang sama
            $alreadyEnrolledInSameCourse = Enrollment::where('user_id', $userId)
                ->aktif()
                ->where('class_id', '!=', $class->id)
                ->whereHas('academicClass', function ($q) use ($class) {
                    $q->where('course_id', $class->course_id)
                      ->where('term_id', $class->term_id);
                })
                ->exists();

            if ($alreadyEnrolledInSameCourse) {
                $courseCode = $class->course?->code ?? 'Mata Kuliah';
                throw new InvalidArgumentException(
                    "Mahasiswa {$user->name} ({$user->identifier}) sudah terdaftar di seksi lain untuk mata kuliah {$courseCode} pada semester ini."
                );
            }

            $enrollment = Enrollment::where('class_id', $class->id)
                ->where('user_id', $userId)
                ->first();

            if ($enrollment) {
                if ($enrollment->isActive()) {
                    throw new InvalidArgumentException(
                        "Mahasiswa {$user->name} ({$user->identifier}) sudah terdaftar aktif di kelas ini."
                    );
                }

                // Aktifkan kembali mahasiswa yang sebelumnya berstatus dikeluarkan
                $enrollment->update([
                    'status' => Enrollment::STATUS_AKTIF,
                    'dikeluarkan_at' => null,
                    'dikeluarkan_oleh' => null,
                ]);
            } else {
                $enrollment = Enrollment::create([
                    'class_id' => $class->id,
                    'user_id' => $userId,
                    'status' => Enrollment::STATUS_AKTIF,
                ]);
            }

            ActivityLogger::log(
                aksi: 'daftarkan_mahasiswa',
                kategori: 'pendaftaran',
                subjek: $enrollment,
                deskripsi: "Mendaftarkan mahasiswa {$user->name} ({$user->identifier}) ke kelas {$class->course->code} {$class->section}",
                properti: [
                    'class_id' => $class->id,
                    'user_id' => $userId,
                    'student_name' => $user->name,
                    'student_nim' => $user->identifier,
                ]
            );

            return $enrollment;
        });
    }

    /**
     * Daftarkan sejumlah mahasiswa sekaligus (bulk enroll).
     */
    public function bulkEnrollStudents(AcademicClass $class, array $userIds): array
    {
        $enrolled = 0;
        $errors = [];

        foreach ($userIds as $uid) {
            try {
                $this->enrollStudent($class, (int) $uid);
                $enrolled++;
            } catch (InvalidArgumentException $e) {
                $errors[] = $e->getMessage();
            }
        }

        return [
            'enrolled_count' => $enrolled,
            'errors' => $errors,
        ];
    }

    /**
     * Keluarkan mahasiswa dari kelas (soft remove tanpa menghapus riwayat nilai dan presensi).
     */
    public function removeStudent(AcademicClass $class, int $userId, int $adminId): Enrollment
    {
        return DB::transaction(function () use ($class, $userId, $adminId) {
            $enrollment = Enrollment::where('class_id', $class->id)
                ->where('user_id', $userId)
                ->firstOrFail();

            if ($enrollment->isDikeluarkan()) {
                throw new InvalidArgumentException("Mahasiswa telah berstatus dikeluarkan dari kelas ini.");
            }

            $user = $enrollment->user;

            $enrollment->update([
                'status' => Enrollment::STATUS_DIKELUARKAN,
                'dikeluarkan_at' => Carbon::now(),
                'dikeluarkan_oleh' => $adminId,
            ]);

            ActivityLogger::log(
                aksi: 'keluarkan_mahasiswa',
                kategori: 'pendaftaran',
                subjek: $enrollment,
                deskripsi: "Mengeluarkan mahasiswa {$user->name} ({$user->identifier}) dari kelas {$class->course->code} {$class->section}",
                properti: [
                    'class_id' => $class->id,
                    'user_id' => $userId,
                    'student_name' => $user->name,
                    'student_nim' => $user->identifier,
                ]
            );

            return $enrollment;
        });
    }

    /**
     * Ekspor daftar mahasiswa terdaftar ke format CSV.
     */
    public function exportCsv(AcademicClass $class): StreamedResponse
    {
        $class->load(['course', 'activeEnrollments.user']);
        $filename = "peserta_kelas_{$class->course->code}_{$class->section}_" . date('Ymd_His') . ".csv";

        return new StreamedResponse(function () use ($class) {
            $handle = fopen('php://output', 'w');

            // Tambahkan BOM UTF-8
            fwrite($handle, "\xEF\xBB\xBF");

            // Header kolom
            fputcsv($handle, [
                'No',
                'NIM',
                'Nama Mahasiswa',
                'Email',
                'Program Studi',
                'Status Pendaftaran',
                'Nilai Akhir',
                'Nilai Huruf',
                'Tanggal Bergabung',
            ]);

            $no = 1;
            foreach ($class->activeEnrollments as $enr) {
                $u = $enr->user;
                fputcsv($handle, [
                    $no++,
                    $u->identifier ?? '-',
                    $u->name,
                    $u->email,
                    $u->prodi ?? ($class->course->curriculum?->program?->name ?? 'S1 Teknik Informatika'),
                    'Terdaftar',
                    $enr->final_score !== null ? $enr->final_score : '-',
                    $enr->letter_grade ?? '-',
                    $enr->created_at ? $enr->created_at->format('Y-m-d H:i') : '-',
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Unduh template file CSV untuk pendaftaran mahasiswa.
     */
    public function getTemplateCsv(): StreamedResponse
    {
        return new StreamedResponse(function () {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            // Header wajib: nim atau identifier
            fputcsv($handle, ['nim', 'nama', 'email']);

            // Baris contoh sampel
            fputcsv($handle, ['24060122140001', 'Ahmad Fadillah', 'ahmad@arunika.test']);
            fputcsv($handle, ['24060122140002', 'Dewi Lestari', 'dewi@arunika.test']);
            fputcsv($handle, ['24060122140003', 'Rizky Pratama', 'rizky@arunika.test']);

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template_import_mahasiswa_arunika.csv"',
        ]);
    }

    /**
     * Impor berkas CSV pendaftaran mahasiswa.
     */
    public function importCsv(AcademicClass $class, UploadedFile $file): array
    {
        $parsed = $this->csvImporter->parseAndValidate($file);

        $successCount = 0;
        $failedCount = 0;
        $errors = [];

        foreach ($parsed['valid'] as $row) {
            $nim = $row['nim'] ?? $row['identifier'] ?? null;
            $email = $row['email'] ?? null;

            if (! $nim && ! $email) {
                $failedCount++;
                $errors[] = "Baris tidak memiliki kolom NIM maupun Email.";
                continue;
            }

            $user = User::where('role', UserRole::Student)
                ->when($nim, fn ($q) => $q->where('identifier', $nim))
                ->when(! $nim && $email, fn ($q) => $q->where('email', $email))
                ->first();

            if (! $user) {
                $failedCount++;
                $errors[] = "Mahasiswa dengan NIM/Email '{$nim}' tidak ditemukan di sistem.";
                continue;
            }

            try {
                $this->enrollStudent($class, $user->id);
                $successCount++;
            } catch (InvalidArgumentException $e) {
                $failedCount++;
                $errors[] = "{$user->name}: " . $e->getMessage();
            }
        }

        ActivityLogger::log(
            aksi: 'impor_mahasiswa_csv',
            kategori: 'pendaftaran',
            subjek: $class,
            deskripsi: "Mengimpor pendaftaran mahasiswa via CSV ke kelas {$class->course->code} {$class->section}: {$successCount} berhasil, {$failedCount} gagal",
            properti: [
                'class_id' => $class->id,
                'success_count' => $successCount,
                'failed_count' => $failedCount,
            ]
        );

        return [
            'total_rows' => $parsed['total_rows'],
            'success_count' => $successCount,
            'failed_count' => $failedCount,
            'errors' => $errors,
        ];
    }
}
