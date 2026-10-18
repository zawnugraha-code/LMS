<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicClass;
use App\Models\ActivityLog;
use App\Models\Campus;
use App\Models\Course;
use App\Models\Curriculum;
use App\Models\Enrollment;
use App\Models\Faculty;
use App\Models\Program;
use App\Models\Term;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $lecturer;
    protected User $student;
    protected Campus $campus;
    protected Term $activeTerm;
    protected Curriculum $curriculum;

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

        $this->curriculum = Curriculum::create([
            'program_id' => $program->id,
            'name' => 'Kurikulum 2024',
            'year' => 2024,
            'is_active' => true,
        ]);

        $this->activeTerm = Term::create([
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
            'email' => 'dosen@arunika.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Lecturer,
            'campus_id' => $this->campus->id,
            'is_active' => true,
        ]);

        $this->student = User::create([
            'name' => 'Arya Sena',
            'email' => 'arya@arunika.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Student,
            'campus_id' => $this->campus->id,
            'is_active' => true,
        ]);
    }

    public function test_admin_dashboard_renders_successfully_with_canonical_stats(): void
    {
        $course = Course::create([
            'curriculum_id' => $this->curriculum->id,
            'code' => 'IF2105',
            'name' => 'Pemrograman Web Lanjut',
            'credits' => 3,
            'semester' => 3,
        ]);

        $class = AcademicClass::create([
            'course_id' => $course->id,
            'term_id' => $this->activeTerm->id,
            'lecturer_id' => $this->lecturer->id,
            'section' => 'A',
            'room' => 'Lab Komputer 3',
            'schedule_day' => 'Selasa',
            'start_time' => '08:00:00',
            'end_time' => '10:30:00',
            'capacity' => 40,
            'status' => 'aktif',
        ]);

        Enrollment::create([
            'class_id' => $class->id,
            'user_id' => $this->student->id,
            'status' => 'aktif',
        ]);

        ActivityLog::create([
            'user_id' => $this->admin->id,
            'aksi' => 'login',
            'kategori' => 'autentikasi',
            'deskripsi' => 'Administrator masuk ke sistem',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Beranda Administrator');
        $response->assertSee('Rina Kusumawati');
        $response->assertSee('Semester Ganjil 2026/2027');
        $response->assertSee('Total Dosen');
        $response->assertSee('Total Mahasiswa');
        $response->assertSee('Kelas Semester Aktif');
        $response->assertSee('IF2105');
        $response->assertSee('Perlu Perhatian');
        $response->assertSee('Aksi Cepat');
        $response->assertSee('Kapasitas Kelas');
    }

    public function test_non_admin_cannot_access_admin_dashboard(): void
    {
        $this->actingAs($this->student)
            ->get(route('admin.dashboard'))
            ->assertStatus(403);

        $this->actingAs($this->lecturer)
            ->get(route('admin.dashboard'))
            ->assertStatus(403);
    }
}
