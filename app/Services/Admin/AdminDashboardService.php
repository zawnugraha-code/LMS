<?php

namespace App\Services\Admin;

use App\Enums\UserRole;
use App\Models\AcademicClass;
use App\Models\ActivityLog;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Term;
use App\Models\User;

class AdminDashboardService
{
    /**
     * Dapatkan data ringkasan metrik dan analitik untuk Beranda Administrator.
     */
    public function getDashboardData(?int $termId = null): array
    {
        $activeTerm = $termId
            ? Term::find($termId)
            : (Term::where('status', 'aktif')->first() ?? Term::where('is_active', true)->first() ?? Term::latest()->first());

        // 1. Total Dosen & Mahasiswa Aktif
        $totalDosen = User::where('role', UserRole::Lecturer)->where('is_active', true)->count();
        $totalMahasiswa = User::where('role', UserRole::Student)->where('is_active', true)->count();

        // 2. Kelas & Mata Kuliah
        $activeTermId = $activeTerm?->id;
        $activeClassesQuery = AcademicClass::query()
            ->when($activeTermId, fn ($q) => $q->where('term_id', $activeTermId));

        $kelasSemesterAktifCount = (clone $activeClassesQuery)->count();
        $totalMataKuliah = Course::count();

        // 3. Dosen Mengampu vs Belum Mengampu
        $dosenMengampuIds = (clone $activeClassesQuery)->pluck('lecturer_id')->unique()->filter();
        $dosenMengampuCount = $dosenMengampuIds->count();
        $dosenBelumMengampuCount = max(0, $totalDosen - $dosenMengampuCount);

        // 4. Mahasiswa Terdaftar vs Belum Terdaftar pada Semester Terpilih
        $terdaftarStudentIds = Enrollment::query()
            ->whereIn('status', ['aktif', 'enrolled'])
            ->when($activeTermId, function ($q) use ($activeTermId) {
                $q->whereHas('academicClass', fn ($cq) => $cq->where('term_id', $activeTermId));
            })
            ->pluck('user_id')
            ->unique();

        $mahasiswaTerdaftarCount = $terdaftarStudentIds->count();
        $mahasiswaBelumTerdaftarCount = max(0, $totalMahasiswa - $mahasiswaTerdaftarCount);

        // 5. Kelas Aktif dengan Relasi & Hitungan Keterisian
        $classes = (clone $activeClassesQuery)
            ->with(['course', 'lecturer', 'activeEnrollments'])
            ->orderBy('section')
            ->get();

        $kelasPenuhCount = 0;
        $kelasTanpaMahasiswaCount = 0;
        $totalKapasitas = 0;
        $totalTerisi = 0;

        $daftarKelasAktif = $classes->map(function ($class) use (&$kelasPenuhCount, &$kelasTanpaMahasiswaCount, &$totalKapasitas, &$totalTerisi) {
            $terisi = $class->activeEnrollments->count();
            $kapasitas = (int) $class->capacity;
            $persen = $kapasitas > 0 ? round(($terisi / $kapasitas) * 100) : 0;

            if ($terisi >= $kapasitas && $kapasitas > 0) {
                $kelasPenuhCount++;
            }
            if ($terisi === 0) {
                $kelasTanpaMahasiswaCount++;
            }

            $totalKapasitas += $kapasitas;
            $totalTerisi += $terisi;

            return [
                'id' => $class->id,
                'course_code' => $class->course?->code ?? '-',
                'course_name' => $class->course?->name ?? 'Mata Kuliah',
                'section' => $class->section,
                'lecturer_name' => $class->lecturer?->name ?? 'Belum Ditunjuk',
                'room' => $class->room ?? '-',
                'schedule_day' => $class->schedule_day ?? '-',
                'start_time' => $class->start_time ? substr($class->start_time, 0, 5) : '',
                'end_time' => $class->end_time ? substr($class->end_time, 0, 5) : '',
                'capacity' => $kapasitas,
                'terisi' => $terisi,
                'percentage' => $persen,
                'status' => $class->status,
            ];
        });

        $totalTersisa = max(0, $totalKapasitas - $totalTerisi);
        $rataRataKeterisian = $totalKapasitas > 0 ? round(($totalTerisi / $totalKapasitas) * 100) : 0;

        // 6. Data Perlu Perhatian
        $perluPerhatian = [
            [
                'judul' => 'Mahasiswa belum terdaftar',
                'keterangan' => "{$mahasiswaBelumTerdaftarCount} mahasiswa belum masuk ke kelas semester aktif",
                'status' => $mahasiswaBelumTerdaftarCount > 0 ? 'perlu_tindakan' : 'aman',
                'label' => $mahasiswaBelumTerdaftarCount > 0 ? 'Perlu tindakan' : 'Aman',
                'tautan' => route('admin.pendaftaran-mahasiswa.index', ['tab' => 'belum_terdaftar']),
            ],
            [
                'judul' => 'Dosen belum mengampu kelas',
                'keterangan' => "{$dosenBelumMengampuCount} dosen aktif belum memiliki kelas di semester ini",
                'status' => $dosenBelumMengampuCount > 0 ? 'perlu_tindakan' : 'aman',
                'label' => $dosenBelumMengampuCount > 0 ? 'Perlu tindakan' : 'Aman',
                'tautan' => '#', // Menu Pengguna di Batch 2
            ],
            [
                'judul' => 'Kelas tanpa mahasiswa',
                'keterangan' => "{$kelasTanpaMahasiswaCount} kelas aktif dengan keterisian 0 mahasiswa",
                'status' => $kelasTanpaMahasiswaCount > 0 ? 'perlu_tindakan' : 'aman',
                'label' => $kelasTanpaMahasiswaCount > 0 ? 'Perlu tindakan' : 'Aman',
                'tautan' => route('admin.kelas.index'),
            ],
            [
                'judul' => 'Kelas penuh (kapasitas 100%)',
                'keterangan' => "{$kelasPenuhCount} kelas telah mencapai kapasitas maksimum",
                'status' => $kelasPenuhCount > 0 ? 'perlu_tindakan' : 'aman',
                'label' => $kelasPenuhCount > 0 ? 'Perlu tindakan' : 'Aman',
                'tautan' => route('admin.kelas.index'),
            ],
        ];

        // 7. Aktivitas Terbaru (5 data teratas)
        $aktivitasTerbaru = ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return [
            'activeTerm' => $activeTerm,
            'totalDosen' => $totalDosen,
            'dosenMengampuCount' => $dosenMengampuCount,
            'dosenBelumMengampuCount' => $dosenBelumMengampuCount,
            'totalMahasiswa' => $totalMahasiswa,
            'mahasiswaTerdaftarCount' => $mahasiswaTerdaftarCount,
            'mahasiswaBelumTerdaftarCount' => $mahasiswaBelumTerdaftarCount,
            'kelasSemesterAktifCount' => $kelasSemesterAktifCount,
            'rataRataKeterisian' => $rataRataKeterisian,
            'totalMataKuliah' => $totalMataKuliah,
            'perluPerhatian' => $perluPerhatian,
            'daftarKelasAktif' => $daftarKelasAktif,
            'totalKapasitas' => $totalKapasitas,
            'totalTerisi' => $totalTerisi,
            'totalTersisa' => $totalTersisa,
            'aktivitasTerbaru' => $aktivitasTerbaru,
        ];
    }
}
