<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicClass;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\LecturerBatch1Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LecturerDashboardAndClassTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->seed(LecturerBatch1Seeder::class);
    }

    public function test_lecturer_can_view_dashboard_with_canonical_stats(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();

        $response = $this->actingAs($dosen)->get(route('dosen.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Selamat datang, Dr. Budi Rahardjo');
        $response->assertSee('Kelas Diampu');
        $response->assertSee('Total Mahasiswa');
        $response->assertSee('Tugas Aktif');
        $response->assertSee('Perlu Dinilai');

        // Exact canonical stats: 3 classes, 90 students, 5 active tasks, 14 pending submissions
        $response->assertViewHas('stats', function ($stats) {
            return $stats['kelas_diampu'] === 3
                && $stats['total_mahasiswa'] === 90
                && $stats['tugas_aktif'] === 5
                && $stats['perlu_dinilai'] === 14;
        });

        // Verify recent pending submissions include top canonical students
        $response->assertSee('Ahmad Fauzi');
        $response->assertSee('Siti Nurhaliza');
        $response->assertSee('Rizky Pratama');
    }

    public function test_student_cannot_access_lecturer_dashboard(): void
    {
        $student = User::where('role', UserRole::Student)->firstOrFail();

        $response = $this->actingAs($student)->get(route('dosen.dashboard'));

        $response->assertStatus(403);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('dosen.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_lecturer_can_view_classes_list_and_filter_status(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();

        $response = $this->actingAs($dosen)->get(route('dosen.kelas.index'));

        $response->assertStatus(200);
        $response->assertSee('Kelas Saya');
        $response->assertSee('IF2101 - Pemrograman Berorientasi Objek & Desain Pola');
        $response->assertSee('IF2103');
        $response->assertSee('Rancang Bangun Mini Core Banking System');
        $response->assertSee('Kelas A');
        $response->assertSee('Kelas B');

        // Test filter status
        $responseAktif = $this->actingAs($dosen)->get(route('dosen.kelas.index', ['status' => 'aktif']));
        $responseAktif->assertStatus(200);
        $responseAktif->assertSee('IF2101');

        $responseSelesai = $this->actingAs($dosen)->get(route('dosen.kelas.index', ['status' => 'selesai']));
        $responseSelesai->assertStatus(200);
    }

    public function test_lecturer_can_view_detail_of_their_class(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();
        $class = AcademicClass::where('lecturer_id', $dosen->id)->firstOrFail();

        $response = $this->actingAs($dosen)->get(route('dosen.kelas.show', $class->id));

        $response->assertStatus(200);
        $response->assertSee($class->course->name);
        $response->assertSee($class->section);
        $response->assertSee('Silabus & Pertemuan', false);
        $response->assertSee('Daftar Mahasiswa');
        $response->assertSee('Komponen Penilaian');
    }

    public function test_lecturer_cannot_view_detail_of_another_lecturer_class(): void
    {
        $dosenBudi = User::where('email', 'dosen@arunika.test')->firstOrFail();
        $dosenRudi = User::where('email', 'dosen2@arunika.test')->firstOrFail();

        $classOther = AcademicClass::where('lecturer_id', $dosenRudi->id)->firstOrFail();

        // Budi attempts to view Rudi's class
        $response = $this->actingAs($dosenBudi)->get(route('dosen.kelas.show', $classOther->id));

        $response->assertStatus(403);
    }
}
