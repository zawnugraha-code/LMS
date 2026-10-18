<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicClass;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\LecturerBatch1Seeder;
use Database\Seeders\LecturerPresensiSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LecturerGradebookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->seed(LecturerBatch1Seeder::class);
        $this->seed(LecturerPresensiSeeder::class);
    }

    public function test_lecturer_can_view_buku_nilai_index_with_canonical_stats(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();

        $response = $this->actingAs($dosen)->get(route('dosen.buku-nilai.index'));

        $response->assertStatus(200);
        $response->assertSee('Buku Nilai');
        $response->assertSee('Total Mahasiswa');
        $response->assertSee('Total Mata Kuliah');
        $response->assertSee('Nilai Final');
        $response->assertSee('Belum Lengkap');
        $response->assertSee('Rekap Nilai');
        $response->assertSee('Komponen Penilaian (Default)');

        // Check canonical stats (Screenshot 2: 62, 2, 48, 14)
        $stats = $response->viewData('stats');
        $this->assertEquals(62, $stats['total_mahasiswa']);
        $this->assertEquals(2, $stats['total_mata_kuliah']);
        $this->assertEquals(48, $stats['nilai_final']);
        $this->assertEquals(14, $stats['belum_lengkap']);

        // Check donut distribution matching Screenshot 2:
        // A: 5 (8%), B: 34 (55%), C: 18 (29%), D: 4 (6%), E: 1 (2%)
        $donut = $response->viewData('donut');
        $this->assertEquals(2, $donut['total_courses']);
        $this->assertEquals(5, $donut['items']['A']['count']);
        $this->assertEquals(8, $donut['items']['A']['percent']);
        $this->assertEquals(34, $donut['items']['B']['count']);
        $this->assertEquals(55, $donut['items']['B']['percent']);
        $this->assertEquals(18, $donut['items']['C']['count']);
        $this->assertEquals(29, $donut['items']['C']['percent']);
        $this->assertEquals(4, $donut['items']['D']['count']);
        $this->assertEquals(6, $donut['items']['D']['percent']);
        $this->assertEquals(1, $donut['items']['E']['count']);
        $this->assertEquals(2, $donut['items']['E']['percent']);

        // Check default components matching Screenshot 2
        $components = $response->viewData('defaultComponents');
        $this->assertCount(4, $components);
        $this->assertEquals(30, $components[0]['weight']); // Tugas 30%
        $this->assertEquals(20, $components[1]['weight']); // Kuis 20%
        $this->assertEquals(25, $components[2]['weight']); // UTS 25%
        $this->assertEquals(25, $components[3]['weight']); // UAS 25%
    }

    public function test_lecturer_can_filter_buku_nilai_by_course_tab_and_search(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();

        // Filter tab IF2101
        $resIF2101 = $this->actingAs($dosen)->get(route('dosen.buku-nilai.index', ['tab' => 'IF2101']));
        $resIF2101->assertStatus(200);
        $resIF2101->assertSee('IF2101 - Pemrograman Berorientasi Objek & Desain Pola');
        $resIF2101->assertDontSee('IF2103 - Rancang Bangun Mini Core Banking System (OOP)');

        // Filter tab IF2103
        $resIF2103 = $this->actingAs($dosen)->get(route('dosen.buku-nilai.index', ['tab' => 'IF2103']));
        $resIF2103->assertStatus(200);
        $resIF2103->assertSee('IF2103');
        $resIF2103->assertSee('Rancang Bangun Mini Core Banking System');
        $resIF2103->assertDontSee('Pemrograman Berorientasi Objek & Desain Pola');

        // Search filter
        $resSearch = $this->actingAs($dosen)->get(route('dosen.buku-nilai.index', ['search' => 'Banking']));
        $resSearch->assertStatus(200);
        $resSearch->assertSee('IF2103');
        $resSearch->assertSee('Rancang Bangun Mini Core Banking System');
    }

    public function test_lecturer_can_view_class_grade_matrix_show_page(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();
        $class = AcademicClass::where('lecturer_id', $dosen->id)
            ->whereHas('course', fn ($q) => $q->where('code', 'IF2101'))
            ->where('section', 'Kelas A')
            ->firstOrFail();

        $response = $this->actingAs($dosen)->get(route('dosen.buku-nilai.show', $class->id));

        $response->assertStatus(200);
        $response->assertSee('Matriks Nilai Akhir OBE');
        $response->assertSee('IF2101');
        $response->assertSee('Kelas A');
        $response->assertSee('Rata-rata Kelas');
        $response->assertSee('Nilai Tertinggi');
        $response->assertSee('Nilai Terendah');
        $response->assertSee('Tingkat Kelulusan');

        // Verify summary
        $summary = $response->viewData('summary');
        $this->assertEquals(78.4, $summary['rata_rata']);
        $this->assertEquals('B', $summary['letter']);
        $this->assertEquals(32, $summary['total_mahasiswa']);

        // Verify students list contains 32 enrolled students
        $students = $response->viewData('students');
        $this->assertCount(32, $students);
    }

    public function test_lecturer_can_update_assessment_components_with_valid_100_percent(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();
        $class = AcademicClass::where('lecturer_id', $dosen->id)->firstOrFail();

        $response = $this->actingAs($dosen)->post(route('dosen.buku-nilai.components.update', $class->id), [
            'components' => [
                'tugas' => 35,
                'kuis' => 15,
                'uts' => 25,
                'uas' => 25,
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('course_assessment_components', [
            'class_id' => $class->id,
            'jenis' => 'tugas',
            'bobot' => 35,
        ]);
        $this->assertDatabaseHas('course_assessment_components', [
            'class_id' => $class->id,
            'jenis' => 'kuis',
            'bobot' => 15,
        ]);
    }

    public function test_component_weight_update_fails_if_sum_is_not_100(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();
        $class = AcademicClass::where('lecturer_id', $dosen->id)->firstOrFail();

        // 30 + 20 + 20 + 20 = 90 (Not 100!)
        $response = $this->actingAs($dosen)->post(route('dosen.buku-nilai.components.update', $class->id), [
            'components' => [
                'tugas' => 30,
                'kuis' => 20,
                'uts' => 20,
                'uas' => 20,
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_lecturer_can_export_grade_matrix_csv(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();
        $class = AcademicClass::where('lecturer_id', $dosen->id)
            ->whereHas('course', fn ($q) => $q->where('code', 'IF2101'))
            ->where('section', 'Kelas A')
            ->firstOrFail();

        $response = $this->actingAs($dosen)->get(route('dosen.buku-nilai.export', $class->id));

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('Content-Type'), 'text/csv'));
        $this->assertTrue(str_contains($response->headers->get('Content-Disposition'), 'attachment; filename='));

        $content = $response->streamedContent();
        // Check UTF-8 BOM
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        // Check CSV Header columns
        $this->assertStringContainsString('NIM', $content);
        $this->assertStringContainsString('Nama Mahasiswa', $content);
        $this->assertStringContainsString('Nilai Akhir', $content);
        $this->assertStringContainsString('Status Kelulusan', $content);
    }

    public function test_lecturer_cannot_view_or_update_another_lecturer_gradebook(): void
    {
        $dosen1 = User::where('email', 'dosen@arunika.test')->firstOrFail();
        $dosen2 = User::where('email', 'dosen2@arunika.test')->firstOrFail();

        $classDosen1 = AcademicClass::where('lecturer_id', $dosen1->id)->firstOrFail();

        // Dr. Rudi tries to view Dr. Budi's class grade matrix
        $resView = $this->actingAs($dosen2)->get(route('dosen.buku-nilai.show', $classDosen1->id));
        $resView->assertStatus(403);

        // Dr. Rudi tries to update Dr. Budi's class components
        $resUpdate = $this->actingAs($dosen2)->post(route('dosen.buku-nilai.components.update', $classDosen1->id), [
            'components' => ['tugas' => 25, 'kuis' => 25, 'uts' => 25, 'uas' => 25],
        ]);
        $resUpdate->assertStatus(403);

        // Dr. Rudi tries to export Dr. Budi's class CSV
        $resExport = $this->actingAs($dosen2)->get(route('dosen.buku-nilai.export', $classDosen1->id));
        $resExport->assertStatus(403);
    }

    public function test_student_cannot_access_lecturer_gradebook(): void
    {
        $student = User::where('role', UserRole::Student)->firstOrFail();
        $class = AcademicClass::firstOrFail();

        $resIndex = $this->actingAs($student)->get(route('dosen.buku-nilai.index'));
        $resIndex->assertStatus(403);

        $resShow = $this->actingAs($student)->get(route('dosen.buku-nilai.show', $class->id));
        $resShow->assertStatus(403);
    }
}
