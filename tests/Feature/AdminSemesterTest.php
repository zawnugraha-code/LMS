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

class AdminSemesterTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
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
    }

    public function test_admin_can_view_semester_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.semester.index'));

        $response->assertStatus(200);
        $response->assertSee('Semester &amp; Kalender Akademik', false);
        $response->assertSee('Semester Ganjil 2026/2027');
        $response->assertSee('2026/2027');
        $response->assertSee('Tambah Semester Baru');
    }

    public function test_admin_can_create_new_semester_with_validation(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.semester.store'), [
            'name' => 'Semester Genap 2026/2027',
            'academic_year' => '2026/2027',
            'semester_type' => 'even',
            'start_date' => '2027-02-15',
            'end_date' => '2027-07-15',
            'is_active' => '0',
        ]);

        $response->assertRedirect(route('admin.semester.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('terms', [
            'name' => 'Semester Genap 2026/2027',
            'academic_year' => '2026/2027',
            'semester_type' => 'even',
            'status' => 'belum_aktif',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'aksi' => 'buat_semester',
            'kategori' => 'akademik',
        ]);
    }

    public function test_cannot_create_duplicate_academic_year_and_type(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.semester.store'), [
            'name' => 'Duplikat Semester Ganjil',
            'academic_year' => '2026/2027',
            'semester_type' => 'odd', // Already exists in setUp
            'start_date' => '2026-09-01',
            'end_date' => '2027-01-31',
        ]);

        $response->assertSessionHas('error');
    }

    public function test_admin_can_activate_semester_and_deactivates_previous(): void
    {
        $newTerm = Term::create([
            'campus_id' => $this->campus->id,
            'name' => 'Semester Genap 2026/2027',
            'academic_year' => '2026/2027',
            'semester_type' => 'even',
            'start_date' => Carbon::create(2027, 2, 15),
            'end_date' => Carbon::create(2027, 7, 15),
            'is_active' => false,
            'status' => 'belum_aktif',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.semester.activate', $newTerm));

        $response->assertRedirect(route('admin.semester.index'));
        $response->assertSessionHas('success');

        $newTerm->refresh();
        $this->activeTerm->refresh();

        $this->assertTrue($newTerm->is_active);
        $this->assertEquals('aktif', $newTerm->status);

        $this->assertFalse($this->activeTerm->is_active);
        $this->assertEquals('belum_aktif', $this->activeTerm->status);
    }

    public function test_admin_can_close_semester_and_locks_classes(): void
    {
        $lecturer = User::create([
            'name' => 'Dr. Budi',
            'email' => 'budi@arunika.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Lecturer,
        ]);

        $course = Course::create([
            'curriculum_id' => $this->curriculum->id,
            'code' => 'IF2101',
            'name' => 'PBO',
            'credits' => 3,
        ]);

        $class = AcademicClass::create([
            'course_id' => $course->id,
            'term_id' => $this->activeTerm->id,
            'lecturer_id' => $lecturer->id,
            'section' => 'A',
            'capacity' => 40,
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.semester.close', $this->activeTerm));

        $response->assertRedirect(route('admin.semester.index'));
        $response->assertSessionHas('success');

        $this->activeTerm->refresh();
        $class->refresh();

        $this->assertFalse($this->activeTerm->is_active);
        $this->assertEquals('selesai', $this->activeTerm->status);
        $this->assertNotNull($this->activeTerm->closed_at);

        $this->assertEquals('selesai', $class->status);
        $this->assertTrue($class->isFinished());
    }

    public function test_cannot_delete_semester_with_classes(): void
    {
        $lecturer = User::create([
            'name' => 'Dr. Budi',
            'email' => 'budi2@arunika.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Lecturer,
        ]);

        $course = Course::create([
            'curriculum_id' => $this->curriculum->id,
            'code' => 'IF2103',
            'name' => 'Struktur Data',
            'credits' => 3,
        ]);

        AcademicClass::create([
            'course_id' => $course->id,
            'term_id' => $this->activeTerm->id,
            'lecturer_id' => $lecturer->id,
            'section' => 'A',
            'capacity' => 40,
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.semester.destroy', $this->activeTerm));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('terms', ['id' => $this->activeTerm->id]);
    }
}
