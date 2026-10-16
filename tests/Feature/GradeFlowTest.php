<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Enrollment;
use App\Models\User;
use App\Services\GradeService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GradeFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;

    protected Enrollment $enrollment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->student = User::where('role', UserRole::Student)->where('email', 'arya.pratama@student.unus.ac.id')->first()
            ?? User::where('role', UserRole::Student)->first();

        $this->enrollment = Enrollment::where('user_id', $this->student->id)->firstOrFail();
    }

    public function test_grades_page_renders_successfully_with_canonical_data(): void
    {
        $response = $this->actingAs($this->student)->get(route('app.grades'));

        $response->assertStatus(200);
        $response->assertSee('Nilai');
        $response->assertSee('IPK Semester Ini');
        $response->assertSee('3.72');
        $response->assertSee('Total SKS');
        $response->assertSee('18');
        $response->assertSee('24 SKS');
        $response->assertSee('75%');
        $response->assertSee('Mata Kuliah Dinilai');
        $response->assertSee('10');
        $response->assertSee('Mata Kuliah Belum Selesai');
        $response->assertSee('2');
        $response->assertSee('Daftar Nilai Mata Kuliah');
        $response->assertSee('Distribusi Nilai');
        $response->assertSee('Performa Akademik');
        $response->assertSee('Pengumuman Nilai');
    }

    public function test_grade_service_summary_calculation(): void
    {
        $service = app(GradeService::class);
        $summary = $service->getSummary($this->student);

        $this->assertEquals('3.72', $summary['gpa']);
        $this->assertEquals('+0.18', $summary['gpa_change']);
        $this->assertEquals(18, $summary['credits_taken']);
        $this->assertEquals(24, $summary['credits_max']);
        $this->assertEquals(75, $summary['credits_percent']);
        $this->assertEquals(10, $summary['graded_count']);
        $this->assertEquals(2, $summary['unfinished_count']);
        $this->assertEquals(12, $summary['total_courses']);
    }

    public function test_grade_distribution_metrics(): void
    {
        $service = app(GradeService::class);
        $distribution = $service->getGradeDistribution($this->student);

        $this->assertEquals(10, $distribution['total']);
        $this->assertCount(6, $distribution['breakdown']);

        $groupA = collect($distribution['breakdown'])->firstWhere('label', 'A (85-100)');
        $this->assertNotNull($groupA);
        $this->assertEquals(5, $groupA['count']);
        $this->assertEquals(41.7, $groupA['percent']);

        $groupBPlus = collect($distribution['breakdown'])->firstWhere('label', 'B+ (75-84)');
        $this->assertNotNull($groupBPlus);
        $this->assertEquals(4, $groupBPlus['count']);
        $this->assertEquals(33.3, $groupBPlus['percent']);
    }

    public function test_semester_filter_returns_filtered_grades(): void
    {
        $service = app(GradeService::class);

        $allGrades = $service->getEnrollmentGrades($this->student, 'all');
        $this->assertCount(12, $allGrades);

        $sem3Grades = $service->getEnrollmentGrades($this->student, '3');
        $this->assertGreaterThanOrEqual(1, $sem3Grades->count());

        $sem2Grades = $service->getEnrollmentGrades($this->student, '2');
        $this->assertGreaterThanOrEqual(1, $sem2Grades->count());

        $sem1Grades = $service->getEnrollmentGrades($this->student, '1');
        $this->assertGreaterThanOrEqual(1, $sem1Grades->count());
    }

    public function test_student_can_open_dedicated_transcript_subpage(): void
    {
        $response = $this->actingAs($this->student)->get(route('app.grades.show', $this->enrollment->id));

        $response->assertStatus(200);
        $response->assertSee('Transkrip Nilai');
        $response->assertSee('Rincian Komponen Penilaian');
        $response->assertSee('Tugas &amp; Praktikum', false);
        $response->assertSee('Kuis &amp; Asesmen Mandiri', false);
        $response->assertSee('Presensi Kehadiran');
        $response->assertSee('Ujian Tengah Semester (UTS)');
        $response->assertSee('Ujian Akhir Semester (UAS)');
        $response->assertSee('Capaian Pembelajaran Mata Kuliah (CPMK)');
        $response->assertSee('Unduh KHS (PDF)');
    }

    public function test_server_strictly_rejects_unauthorized_student_transcript_with_403(): void
    {
        $otherStudent = User::factory()->create([
            'role' => UserRole::Student,
            'email' => 'other.student@student.unus.ac.id',
        ]);

        $response = $this->actingAs($otherStudent)->get(route('app.grades.show', $this->enrollment->id));

        $response->assertStatus(403);
    }

    public function test_student_can_download_course_khs_pdf(): void
    {
        $response = $this->actingAs($this->student)->get(route('app.grades.pdf', $this->enrollment->id));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('.pdf', $response->headers->get('Content-Disposition'));
    }

    public function test_student_can_download_semester_khs_pdf(): void
    {
        $response = $this->actingAs($this->student)->get(route('app.grades.khs.download'));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('KHS_Semester3_', $response->headers->get('Content-Disposition'));
    }

    public function test_academic_performance_trends_data(): void
    {
        $service = app(GradeService::class);
        $trends = $service->getAcademicPerformanceTrends($this->student);

        $this->assertArrayHasKey('points', $trends);
        $this->assertCount(6, $trends['points']);
        $this->assertArrayHasKey('path_line', $trends);
        $this->assertArrayHasKey('path_area', $trends);
    }
}
