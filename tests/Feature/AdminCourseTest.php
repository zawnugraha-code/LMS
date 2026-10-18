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

class AdminCourseTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $lecturer;
    protected Campus $campus;
    protected Program $program;
    protected Curriculum $curriculum;
    protected Term $term;
    protected Course $course;

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

        $this->program = Program::create([
            'faculty_id' => $faculty->id,
            'name' => 'S1 Teknik Informatika',
            'code' => 'IF',
            'degree_level' => 'S1',
        ]);

        $this->curriculum = Curriculum::create([
            'program_id' => $this->program->id,
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

        $this->course = Course::create([
            'curriculum_id' => $this->curriculum->id,
            'code' => 'IF2101',
            'name' => 'Algoritma dan Struktur Data',
            'credits' => 3,
            'semester' => 3,
            'description' => 'Mata kuliah dasar algoritma',
        ]);
    }

    public function test_admin_can_view_course_catalog_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.mata-kuliah.index'));

        $response->assertStatus(200);
        $response->assertSee('Katalog Mata Kuliah');
        $response->assertSee('IF2101');
        $response->assertSee('Algoritma dan Struktur Data');
        $response->assertSee('Tambah Mata Kuliah');
        $response->assertSee('Distribusi Program Studi');
    }

    public function test_admin_can_filter_and_search_courses(): void
    {
        Course::create([
            'curriculum_id' => $this->curriculum->id,
            'code' => 'IF2102',
            'name' => 'Basis Data Relasional',
            'credits' => 4,
            'semester' => 3,
        ]);

        // Search query
        $response = $this->actingAs($this->admin)->get(route('admin.mata-kuliah.index', ['q' => 'Relasional']));
        $response->assertStatus(200);
        $response->assertSee('Basis Data Relasional');
        $response->assertDontSee('Algoritma dan Struktur Data');

        // Filter by program
        $responseProg = $this->actingAs($this->admin)->get(route('admin.mata-kuliah.index', ['program_id' => $this->program->id]));
        $responseProg->assertStatus(200);
        $responseProg->assertSee('Algoritma dan Struktur Data');
        $responseProg->assertSee('Basis Data Relasional');
    }

    public function test_admin_can_create_new_course_successfully(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.mata-kuliah.store'), [
            'curriculum_id' => $this->curriculum->id,
            'code' => 'if2103',
            'name' => 'Pemrograman Berorientasi Objek',
            'credits' => 3,
            'semester' => 3,
            'description' => 'Konsep OOP dengan Java dan PHP',
        ]);

        $response->assertRedirect(route('admin.mata-kuliah.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('courses', [
            'code' => 'IF2103', // Automatically uppercased
            'name' => 'Pemrograman Berorientasi Objek',
            'credits' => 3,
            'semester' => 3,
        ]);
    }

    public function test_course_code_must_be_unique(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.mata-kuliah.store'), [
            'curriculum_id' => $this->curriculum->id,
            'code' => 'IF2101', // Duplicate code
            'name' => 'Algoritma Lanjut',
            'credits' => 3,
            'semester' => 4,
        ]);

        $response->assertSessionHasErrors(['code']);
    }

    public function test_admin_can_update_course_details(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.mata-kuliah.update', $this->course), [
            'curriculum_id' => $this->curriculum->id,
            'code' => 'IF2101',
            'name' => 'Struktur Data & Algoritma Tingkat Lanjut',
            'credits' => 4,
            'semester' => 3,
            'description' => 'Materi telah diperbarui dengan graph dan tree.',
        ]);

        $response->assertRedirect(route('admin.mata-kuliah.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('courses', [
            'id' => $this->course->id,
            'name' => 'Struktur Data & Algoritma Tingkat Lanjut',
            'credits' => 4,
        ]);
    }

    public function test_admin_can_delete_course_without_classes(): void
    {
        $freshCourse = Course::create([
            'curriculum_id' => $this->curriculum->id,
            'code' => 'IF9999',
            'name' => 'Mata Kuliah Uji Hapus',
            'credits' => 2,
            'semester' => 7,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.mata-kuliah.destroy', $freshCourse));

        $response->assertRedirect(route('admin.mata-kuliah.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('courses', ['id' => $freshCourse->id]);
    }

    public function test_cannot_delete_course_with_existing_classes(): void
    {
        // Hubungkan kelas ke $this->course
        AcademicClass::create([
            'course_id' => $this->course->id,
            'term_id' => $this->term->id,
            'section' => 'IF-201-A',
            'capacity' => 40,
            'status' => 'aktif',
            'lecturer_id' => $this->lecturer->id,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.mata-kuliah.destroy', $this->course));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('courses', ['id' => $this->course->id]);
    }

    public function test_non_admin_cannot_access_or_modify_course_routes(): void
    {
        $response = $this->actingAs($this->lecturer)->get(route('admin.mata-kuliah.index'));
        $response->assertStatus(403);

        $responsePost = $this->actingAs($this->lecturer)->post(route('admin.mata-kuliah.store'), [
            'code' => 'IF3000',
            'name' => 'Hacker MK',
            'credits' => 3,
            'semester' => 5,
        ]);
        $responsePost->assertStatus(403);
    }
}
