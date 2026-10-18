<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicClass;
use App\Models\Campus;
use App\Models\Course;
use App\Models\Curriculum;
use App\Models\Faculty;
use App\Models\Program;
use App\Models\Term;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminSearchTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $lecturer;
    protected User $student;
    protected Campus $campus;
    protected Course $course;
    protected AcademicClass $class;
    protected Term $term;

    protected function setUp(): void
    {
        parent::setUp();

        $this->campus = Campus::create([
            'name' => 'Universitas Nusantara Indonesia',
            'code' => 'UNI',
            'domain' => 'uni.ac.id',
            'is_active' => true,
        ]);

        $faculty = Faculty::create([
            'campus_id' => $this->campus->id,
            'name' => 'Fakultas Ilmu Komputer',
            'code' => 'FKOM',
        ]);

        $program = Program::create([
            'faculty_id' => $faculty->id,
            'name' => 'S1 Teknik Informatika',
            'code' => 'IF',
            'degree_level' => 'S1',
        ]);

        $curriculum = Curriculum::create([
            'program_id' => $program->id,
            'name' => 'Kurikulum 2024',
            'year' => 2024,
            'is_active' => true,
        ]);

        $this->term = Term::create([
            'campus_id' => $this->campus->id,
            'name' => 'Semester Ganjil 2026/2027',
            'academic_year' => '2026/2027',
            'semester_type' => 'odd',
            'start_date' => Carbon::create(2026, 9, 1),
            'end_date' => Carbon::create(2027, 1, 31),
            'is_active' => true,
            'status' => 'aktif',
        ]);

        $this->admin = User::create([
            'name' => 'Rina Kusumawati',
            'email' => 'admin@arunika.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
            'campus_id' => $this->campus->id,
            'is_active' => true,
        ]);

        $this->lecturer = User::create([
            'name' => 'Dr. Budi Rahardjo',
            'email' => 'budi@arunika.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Lecturer,
            'campus_id' => $this->campus->id,
            'is_active' => true,
        ]);

        $this->student = User::create([
            'name' => 'Ahmad Fadillah',
            'identifier' => '24060122140001',
            'email' => 'ahmad@arunika.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Student,
            'campus_id' => $this->campus->id,
            'is_active' => true,
        ]);

        $this->course = Course::create([
            'curriculum_id' => $curriculum->id,
            'code' => 'IF2101',
            'name' => 'Algoritma dan Pemrograman',
            'credits' => 3,
            'semester' => 3,
        ]);

        $this->class = AcademicClass::create([
            'course_id' => $this->course->id,
            'term_id' => $this->term->id,
            'lecturer_id' => $this->lecturer->id,
            'section' => 'IF-201-A',
            'capacity' => 40,
            'status' => 'aktif',
        ]);
    }

    public function test_admin_can_perform_global_search_with_results(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.cari', ['q' => 'Algoritma']));

        $response->assertStatus(200);
        $response->assertSee('Pencarian Global');
        $response->assertSee('Algoritma dan Pemrograman');
        $response->assertSee('IF2101');
    }

    public function test_admin_global_search_finds_users(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.cari', ['q' => 'Budi']));

        $response->assertStatus(200);
        $response->assertSee('Dr. Budi Rahardjo');
        $response->assertSee('Dosen Pengajar');
    }

    public function test_admin_global_search_short_query_returns_prompt(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.cari', ['q' => 'a']));

        $response->assertStatus(200);
        $response->assertSee('Pencarian Global');
    }

    public function test_non_admin_cannot_access_global_search(): void
    {
        $response = $this->actingAs($this->lecturer)->get(route('admin.cari', ['q' => 'Algoritma']));
        $response->assertStatus(403);
    }
}
