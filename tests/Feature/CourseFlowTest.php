<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicClass;
use App\Models\Term;
use App\Models\User;
use App\Services\CourseService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->student = User::where('role', UserRole::Student)->where('email', 'arya.pratama@student.unus.ac.id')->first()
            ?? User::where('role', UserRole::Student)->first();
    }

    public function test_courses_page_renders_successfully_with_canonical_data(): void
    {
        $response = $this->actingAs($this->student)->get(route('app.courses'));

        $response->assertStatus(200);
        $response->assertSee('Pemrograman Berorientasi Objek &amp; Desain Pola', false);
        $response->assertSee('Basis Data &amp; Sistem Terdistribusi', false);
        $response->assertSee('Jaringan Komputer');
        $response->assertSee('6 Mata Kuliah');
        $response->assertSee('18');
        $response->assertSee('SKS Diambil');
    }

    public function test_course_service_correctly_computes_summary_and_progress(): void
    {
        $term = Term::where('is_active', true)->first();
        $service = app(CourseService::class);

        $summary = $service->getSummary($this->student, $term);
        $this->assertEquals(6, $summary['total_courses']);
        $this->assertEquals(18, $summary['total_credits']);
        $this->assertEquals(5, $summary['active_courses']);

        $courses = $service->getCourses($this->student, $term);
        $this->assertCount(6, $courses);

        $if2101 = $courses->firstWhere('code', 'IF2101');
        $this->assertNotNull($if2101);
        $this->assertEquals(7, $if2101->progress_completed);
        $this->assertEquals(14, $if2101->progress_total);
        $this->assertEquals(50, $if2101->progress_percentage);
    }

    public function test_course_detail_page_displays_syllabus_cpmks_and_schedule(): void
    {
        $class = AcademicClass::whereHas('course', fn ($q) => $q->where('code', 'IF2101'))->first();
        $this->assertNotNull($class);

        $response = $this->actingAs($this->student)->get(route('app.courses.show', $class));

        $response->assertStatus(200);
        $response->assertSee('Deskripsi & Rencana Pembelajaran Semester', false);
        $response->assertSee('Capaian Pembelajaran Mata Kuliah (CPMK)');
        $response->assertSee('CPMK-1');
        $response->assertSee('Dr. Rudi Rahardjo, S.T., M.Kom.');
        $response->assertSee('Lab Pemrograman');
    }

    public function test_student_can_enter_active_classroom_and_view_timeline(): void
    {
        $class = AcademicClass::whereHas('course', fn ($q) => $q->where('code', 'IF2101'))->first();

        $response = $this->actingAs($this->student)->get(route('app.courses.classroom', $class));

        $response->assertStatus(200);
        $response->assertSee('Timeline & Materi Pertemuan (1 - 14)', false);
        $response->assertSee('Minggu ke-1');
        $response->assertSee('Minggu ke-14');
        $response->assertSee('Presensi Tercatat');
    }

    public function test_server_strictly_rejects_entry_to_upcoming_locked_course_with_403(): void
    {
        $lockedClass = AcademicClass::whereHas('course', fn ($q) => $q->where('code', 'IF2106'))->first();
        $this->assertNotNull($lockedClass);
        $this->assertTrue($lockedClass->isLocked());

        $response = $this->actingAs($this->student)->get(route('app.courses.classroom', $lockedClass));

        $response->assertStatus(403);
    }

    public function test_switching_academic_term_changes_courses_dynamically(): void
    {
        $term1 = Term::where('name', 'Semester Ganjil 2025/2026')->first();
        $this->assertNotNull($term1);

        $this->actingAs($this->student)
            ->post(route('app.term.switch'), ['term_id' => $term1->id])
            ->assertRedirect();

        $response = $this->actingAs($this->student)->get(route('app.courses'));
        $response->assertStatus(200);
        $response->assertSee('Semester Ganjil 2025/2026');
    }
}
