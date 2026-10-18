<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicClass;
use App\Models\Meeting;
use App\Models\Presensi;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\LecturerBatch1Seeder;
use Database\Seeders\LecturerPresensiSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LecturerPresensiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->seed(LecturerBatch1Seeder::class);
        $this->seed(LecturerPresensiSeeder::class);
    }

    public function test_lecturer_can_view_presensi_index_with_canonical_stats(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();

        $response = $this->actingAs($dosen)->get(route('dosen.presensi.index'));

        $response->assertStatus(200);
        $response->assertSee('Presensi');
        $response->assertSee('Total Kelas');
        $response->assertSee('Pertemuan Hari Ini');
        $response->assertSee('Hadir Rata-rata');
        $response->assertSee('Belum Absen');

        // Verify stats in view: 3 classes, 2 meetings today, ~86% rate, 12 unrecorded
        $stats = $response->viewData('stats');
        $this->assertEquals(3, $stats['total_kelas']);
        $this->assertEquals(2, $stats['pertemuan_hari_ini']);
        $this->assertEquals(86, $stats['hadir_rata_rata']);
        $this->assertEquals(12, $stats['belum_absen']);

        // Verify Donut data
        $response->assertViewHas('donut', function ($donut) {
            return $donut['rate'] === 86
                && $donut['hadir_count'] > 0
                && $donut['izin_count'] > 0;
        });

        // Verify class list contains IF2101 and IF2103
        $response->assertSee('Pemrograman Berorientasi Objek & Desain Pola');
        $response->assertSee('Rancang Bangun Mini Core Banking System');
    }

    public function test_lecturer_can_filter_presensi_by_tab_and_search(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();

        // Filter tab Hadir
        $responseHadir = $this->actingAs($dosen)->get(route('dosen.presensi.index', ['tab' => 'hadir']));
        $responseHadir->assertStatus(200);

        // Search IF2103
        $responseSearch = $this->actingAs($dosen)->get(route('dosen.presensi.index', ['q' => 'IF2103']));
        $responseSearch->assertStatus(200);
        $responseSearch->assertSee('Rancang Bangun Mini Core Banking System');
    }

    public function test_lecturer_can_view_input_presensi_form(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();

        $response = $this->actingAs($dosen)->get(route('dosen.presensi.input'));

        $response->assertStatus(200);
        $response->assertSee('Input Presensi Perkuliahan');
        $response->assertSee('Hadir Semua');
        $response->assertSee('Simpan Presensi');
    }

    public function test_lecturer_can_store_presensi_and_belum_absen_decreases(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();

        $classPboA = AcademicClass::where('lecturer_id', $dosen->id)
            ->whereHas('course', fn ($q) => $q->where('code', 'IF2101'))
            ->where('section', 'Kelas A')
            ->firstOrFail();

        $meeting5 = Meeting::where('class_id', $classPboA->id)->where('week', 5)->firstOrFail();

        // Find students in Kelas A who were unrecorded today
        $recordedIds = Presensi::where('pertemuan_id', $meeting5->id)->pluck('mahasiswa_id');
        $unrecordedStudents = User::whereHas('enrollments', fn ($q) => $q->where('class_id', $classPboA->id))
            ->whereNotIn('id', $recordedIds)
            ->get();

        $this->assertNotEmpty($unrecordedStudents);

        // Submit presensi for these unrecorded students
        $payload = [
            'meeting_id' => $meeting5->id,
            'mahasiswa' => [],
        ];

        foreach ($unrecordedStudents as $s) {
            $payload['mahasiswa'][$s->id] = [
                'status' => 'hadir',
                'catatan' => 'Presensi terlambat dicatat dosen',
            ];
        }

        $response = $this->actingAs($dosen)->post(route('dosen.presensi.store'), $payload);

        $response->assertStatus(302);
        $response->assertRedirect(route('dosen.presensi.show', $classPboA->id));

        // Verify presensi records in DB
        foreach ($unrecordedStudents as $s) {
            $this->assertDatabaseHas('presensi', [
                'pertemuan_id' => $meeting5->id,
                'mahasiswa_id' => $s->id,
                'status' => 'hadir',
                'dicatat_oleh' => $dosen->id,
            ]);
        }

        // Verify Belum Absen stat decreases
        $indexResponse = $this->actingAs($dosen)->get(route('dosen.presensi.index'));
        $indexResponse->assertViewHas('stats', function ($stats) use ($unrecordedStudents) {
            return $stats['belum_absen'] === (12 - $unrecordedStudents->count());
        });
    }

    public function test_student_sees_attendance_recorded_by_lecturer(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();
        $student = User::where('role', UserRole::Student)->where('email', 'arya.pratama@student.unus.ac.id')->firstOrFail();

        $class = AcademicClass::where('lecturer_id', $dosen->id)
            ->whereHas('course', fn ($q) => $q->where('code', 'IF2101'))
            ->where('section', 'Kelas A')
            ->firstOrFail();

        $meeting3 = Meeting::where('class_id', $class->id)->where('week', 3)->firstOrFail();

        // Dosen marks student as Izin on Meeting 3
        Presensi::updateOrCreate(
            ['pertemuan_id' => $meeting3->id, 'mahasiswa_id' => $student->id],
            [
                'status' => 'izin',
                'catatan' => 'Dispensasi lomba nasional',
                'dicatat_oleh' => $dosen->id,
            ]
        );

        // Student opens attendance page
        $studentResponse = $this->actingAs($student)->get(route('app.attendance', ['class_id' => $class->id]));

        $studentResponse->assertStatus(200);
        $studentResponse->assertSee('Izin');
        $studentResponse->assertSee('Dispensasi lomba nasional');
    }

    public function test_lecturer_can_view_class_attendance_detail_and_matrix(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();

        $classPboA = AcademicClass::where('lecturer_id', $dosen->id)
            ->whereHas('course', fn ($q) => $q->where('code', 'IF2101'))
            ->where('section', 'Kelas A')
            ->firstOrFail();

        $response = $this->actingAs($dosen)->get(route('dosen.presensi.show', $classPboA->id));

        $response->assertStatus(200);
        $response->assertSee('Matriks Kehadiran Mahasiswa');
        $response->assertSee('Riwayat per Pertemuan');
        $response->assertSee('Memenuhi Syarat');
        $response->assertSee('Ekspor CSV');
    }

    public function test_lecturer_can_export_presensi_csv(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();

        $classPboA = AcademicClass::where('lecturer_id', $dosen->id)
            ->whereHas('course', fn ($q) => $q->where('code', 'IF2101'))
            ->where('section', 'Kelas A')
            ->firstOrFail();

        $response = $this->actingAs($dosen)->get(route('dosen.presensi.export', $classPboA->id));

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type'), 'text/csv'));
        $this->assertTrue(str_contains($response->headers->get('content-disposition'), 'attachment'));
    }

    public function test_lecturer_cannot_view_or_manage_another_lecturer_class_presensi(): void
    {
        $dosen1 = User::where('email', 'dosen@arunika.test')->firstOrFail();
        $dosen2 = User::where('email', 'dosen2@arunika.test')->firstOrFail();

        $classDosen2 = AcademicClass::where('lecturer_id', $dosen2->id)->firstOrFail();

        // Dosen 1 attempts to access Dosen 2's class presensi detail
        $response = $this->actingAs($dosen1)->get(route('dosen.presensi.show', $classDosen2->id));
        $response->assertStatus(403);
    }

    public function test_student_cannot_access_lecturer_presensi(): void
    {
        $student = User::where('role', UserRole::Student)->firstOrFail();

        $response = $this->actingAs($student)->get(route('dosen.presensi.index'));
        $response->assertStatus(403);
    }
}
