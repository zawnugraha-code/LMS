<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicClass;
use App\Models\Material;
use App\Models\Submission;
use App\Models\Term;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\LecturerBatch1Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class LecturerFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->seed(LecturerBatch1Seeder::class);
    }

    /**
     * Test canonical lecturer profile and attributes exist.
     */
    public function test_canonical_lecturer_profile_exists(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->first();

        $this->assertNotNull($dosen, 'Dosen Dr. Budi Rahardjo harus ada.');
        $this->assertEquals(UserRole::Lecturer, $dosen->role);
        $this->assertEquals('Dr. Budi Rahardjo, S.T., M.Kom.', $dosen->gelar);
        $this->assertEquals('0021037502', $dosen->identifier);
        $this->assertEquals('S1 Teknik Informatika', $dosen->prodi);
    }

    /**
     * Test active term and exactly 3 classes taught by Budi.
     */
    public function test_active_term_and_three_classes_taught(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->first();
        $activeTerm = Term::where('is_active', true)->first();

        $this->assertNotNull($activeTerm);
        $this->assertEquals('Semester Ganjil 2026/2027', $activeTerm->name);

        $classes = AcademicClass::where('lecturer_id', $dosen->id)
            ->where('term_id', $activeTerm->id)
            ->where('status', 'aktif')
            ->get();

        $this->assertCount(3, $classes, 'Dosen Budi harus mengampu tepat 3 kelas aktif.');
    }

    /**
     * Test total unique students across Budi's classes is 90.
     */
    public function test_total_students_across_classes_is_ninety(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->first();
        $classIds = AcademicClass::where('lecturer_id', $dosen->id)->pluck('id');

        $totalStudents = User::whereHas('enrollments', fn ($q) => $q->whereIn('class_id', $classIds))
            ->distinct()
            ->count();

        $this->assertEquals(90, $totalStudents, 'Total mahasiswa unik di 3 kelas harus tepat 90.');
    }

    /**
     * Test materials in IF2101 Kelas A (6 published, 1 draft).
     */
    public function test_materials_in_if2101_kelas_a(): void
    {
        $classPboA = AcademicClass::whereHas('course', fn ($q) => $q->where('code', 'IF2101'))
            ->where('section', 'Kelas A')
            ->first();

        $materials = Material::where('class_id', $classPboA->id)->get();
        $this->assertCount(7, $materials, 'Harus ada tepat 7 materi di IF2101 Kelas A.');

        $publishedCount = $materials->where('status', 'published')->count();
        $draftCount = $materials->where('status', 'draft')->count();

        $this->assertEquals(6, $publishedCount, 'Harus ada 6 materi published.');
        $this->assertEquals(1, $draftCount, 'Harus ada 1 materi draft (Dataset Latihan).');
    }

    /**
     * Test exactly 14 pending submissions (ungraded, score IS NULL).
     */
    public function test_exactly_fourteen_pending_submissions(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->first();

        $pendingCount = Submission::whereNull('score')
            ->whereHas('assignment.academicClass', fn ($q) => $q->where('lecturer_id', $dosen->id))
            ->count();

        $this->assertEquals(14, $pendingCount, 'Badge dan antrean pengumpulan yang belum dinilai harus tepat 14.');
    }

    /**
     * Test top 5 submissions have correct status_waktu and student names.
     */
    public function test_top_submissions_status_waktu(): void
    {
        $subAhmad = Submission::whereHas('user', fn ($q) => $q->where('email', 'ahmad.fauzi@student.unus.ac.id'))->first();
        $this->assertNotNull($subAhmad);
        $this->assertEquals('terlambat', $subAhmad->status_waktu);

        $subSiti = Submission::whereHas('user', fn ($q) => $q->where('email', 'siti.nurhaliza@student.unus.ac.id'))->first();
        $this->assertNotNull($subSiti);
        $this->assertEquals('tepat_waktu', $subSiti->status_waktu);
    }

    /**
     * Test 2 unread notifications for lecturer.
     */
    public function test_two_unread_notifications_for_lecturer(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->first();
        $unreadCount = $dosen->unreadNotifications()->count();

        $this->assertEquals(2, $unreadCount, 'Lonceng notifikasi dosen harus memiliki badge 2.');
    }

    /**
     * Test scheduled assessments auto-publish command.
     */
    public function test_scheduled_assessments_publish_command(): void
    {
        $exitCode = Artisan::call('app:publish-scheduled-assessments');
        $this->assertEquals(0, $exitCode);
    }

    /**
     * Test Batch 2 placeholder routes accessible by lecturer.
     */
    public function test_batch_two_placeholders_accessible_by_lecturer(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->first();

        $routes = [
            'dosen.presensi.index',
            'dosen.buku-nilai.index',
            'dosen.diskusi.index',
            'dosen.pengaturan.edit',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($dosen)->get(route($route));
            $response->assertStatus(200);
            $response->assertSee('Batch 2');
        }
    }

    /**
     * Test student cannot access dosen portal (403 Forbidden).
     */
    public function test_student_cannot_access_dosen_portal(): void
    {
        $student = User::where('role', UserRole::Student)->first();

        $response = $this->actingAs($student)->get(route('dosen.dashboard'));
        $response->assertStatus(403);
    }
}
