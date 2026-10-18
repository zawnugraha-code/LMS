<?php

namespace Tests\Feature;

use App\Models\AcademicClass;
use App\Models\Assignment;
use App\Models\Submission;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\LecturerBatch1Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LecturerBatch1EndToEndTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->seed(LecturerBatch1Seeder::class);
    }

    public function test_batch_1_complete_flow_and_navigation(): void
    {
        // 1. Instant Role Switcher Login as Lecturer
        $loginRes = $this->get('/demo/login/lecturer');
        $loginRes->assertRedirect('/dosen');
        $this->assertAuthenticated();

        $dosen = auth()->user();
        $this->assertEquals('dosen@arunika.test', $dosen->email);

        // 2. Beranda Dosen
        $dashboardRes = $this->actingAs($dosen)->get(route('dosen.dashboard'));
        $dashboardRes->assertStatus(200);
        $dashboardRes->assertSee('Dr. Budi Rahardjo');
        $dashboardRes->assertSee('Kelas Diampu');
        $dashboardRes->assertSee('Total Mahasiswa');
        $dashboardRes->assertSee('Tugas Aktif');
        $dashboardRes->assertSee('Perlu Dinilai');

        // Verify canonical stats: 3 classes, 90 students, 5 active tasks, 14 pending submissions
        $dashboardRes->assertViewHas('stats', function ($stats) {
            return $stats['kelas_diampu'] === 3
                && $stats['total_mahasiswa'] === 90
                && $stats['tugas_aktif'] === 5
                && $stats['perlu_dinilai'] === 14;
        });

        // 3. Kelas Saya
        $kelasIndexRes = $this->actingAs($dosen)->get(route('dosen.kelas.index'));
        $kelasIndexRes->assertStatus(200);
        $kelasIndexRes->assertSee('IF2101');
        $kelasIndexRes->assertSee('IF2103');
        $kelasIndexRes->assertSee('Kelas A');
        $kelasIndexRes->assertSee('Kelas B');

        // Detail Kelas IF2101 Kelas A
        $classA = AcademicClass::where('lecturer_id', $dosen->id)
            ->whereHas('course', fn ($q) => $q->where('code', 'IF2101'))
            ->where('section', 'Kelas A')
            ->firstOrFail();

        $kelasDetailRes = $this->actingAs($dosen)->get(route('dosen.kelas.show', $classA->id));
        $kelasDetailRes->assertStatus(200);
        $kelasDetailRes->assertSee('Silabus & Pertemuan');
        $kelasDetailRes->assertSee('Daftar Mahasiswa (32)');
        $kelasDetailRes->assertSee('Komponen Penilaian');

        // 4. Modul Tugas & Ujian
        $tugasRes = $this->actingAs($dosen)->get(route('dosen.tugas.index'));
        $tugasRes->assertStatus(200);
        $tugasRes->assertSee('Tugas & Ujian');
        $tugasRes->assertSee('Tugas 3 - Polimorfisme');

        // Canonical assessment summary: 12, 7, 5, 3
        $tugasRes->assertViewHas('summary', function ($summary) {
            return $summary['total'] === 12
                && $summary['tugas'] === 7
                && $summary['kuis_ujian'] === 5
                && $summary['menunggu_publikasi'] === 3;
        });

        // Canonical publication stats for donut chart: 7 published, 2 draft, 1 scheduled, 2 archived
        $tugasRes->assertViewHas('pubStats', function ($pub) {
            return $pub['published'] === 7
                && $pub['draft'] === 2
                && $pub['scheduled'] === 1
                && $pub['archived'] === 2
                && $pub['total'] === 12;
        });

        // 5. Modul Penilaian
        $penilaianRes = $this->actingAs($dosen)->get(route('dosen.penilaian.index'));
        $penilaianRes->assertStatus(200);
        $penilaianRes->assertSee('Penilaian');
        $penilaianRes->assertSee('Total Penilaian');
        $penilaianRes->assertSee('Ringkasan Penilaian');

        // Canonical grading summary: 12, 7, 5, 0 & 42%
        $penilaianRes->assertViewHas('summary', function ($summary) {
            return $summary['total_penilaian'] === 12
                && $summary['menunggu_penilaian'] === 7
                && $summary['sudah_dinilai'] === 5
                && $summary['terlambat'] === 0;
        });
        $penilaianRes->assertViewHas('progress', function ($progress) {
            return $progress['percentage'] === 42;
        });

        // Grading Workspace for an assignment with pending submissions
        $pendingSubmission = Submission::whereNull('score')
            ->whereHas('assignment.academicClass', fn ($q) => $q->where('lecturer_id', $dosen->id))
            ->firstOrFail();
        $assignment = $pendingSubmission->assignment;

        $workspaceRes = $this->actingAs($dosen)->get(route('dosen.penilaian.show', $assignment->id));
        $workspaceRes->assertStatus(200);
        $workspaceRes->assertSee($assignment->title);
        $workspaceRes->assertSee('Total Mahasiswa');
        $workspaceRes->assertSee('Mengumpulkan');

        $initialPendingBadge = Submission::whereNull('score')
            ->whereHas('assignment.academicClass', fn ($q) => $q->where('lecturer_id', $dosen->id))
            ->count();
        $this->assertEquals(14, $initialPendingBadge);

        $gradeRes = $this->actingAs($dosen)->post(route('dosen.penilaian.grade', $pendingSubmission->id), [
            'score' => 95,
            'feedback' => 'Implementasi polymorphism sangat bagus.',
        ]);
        $gradeRes->assertRedirect();

        $newPendingBadge = Submission::whereNull('score')
            ->whereHas('assignment.academicClass', fn ($q) => $q->where('lecturer_id', $dosen->id))
            ->count();
        $this->assertEquals(13, $newPendingBadge);

        // 6. Modul Materi Pembelajaran
        $materiRes = $this->actingAs($dosen)->get(route('dosen.materi.index', ['class_id' => $classA->id]));
        $materiRes->assertStatus(200);
        $materiRes->assertSee('Materi Pembelajaran');
        $materiRes->assertSee('Pengantar OOP dan Konsep Dasar');
        $materiRes->assertSee('Dataset Latihan');

        // Verify canonical 7 materials (6 published, 1 draft)
        $materiRes->assertViewHas('summary', function ($summary) {
            return $summary['total'] === 7
                && $summary['published'] === 6
                && $summary['draft'] === 1;
        });

        // 7. Verify All Batch 2 Modules (Presensi, Buku Nilai, Diskusi, Pengaturan) are Live
        $presensiRes = $this->actingAs($dosen)->get(route('dosen.presensi.index'));
        $presensiRes->assertStatus(200);
        $presensiRes->assertSee('Presensi');

        $bukuNilaiRes = $this->actingAs($dosen)->get(route('dosen.buku-nilai.index'));
        $bukuNilaiRes->assertStatus(200);
        $bukuNilaiRes->assertSee('Buku Nilai');

        $diskusiRes = $this->actingAs($dosen)->get(route('dosen.diskusi.index'));
        $diskusiRes->assertStatus(200);
        $diskusiRes->assertSee('Diskusi');

        $pengaturanRes = $this->actingAs($dosen)->get(route('dosen.pengaturan.edit'));
        $pengaturanRes->assertStatus(200);
        $pengaturanRes->assertSee('Pengaturan');
        $pengaturanRes->assertDontSee('Halaman ini sedang disiapkan');
    }
}
