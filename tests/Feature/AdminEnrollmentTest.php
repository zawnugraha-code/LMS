<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicClass;
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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $lecturer;
    protected User $studentA;
    protected User $studentB;
    protected Campus $campus;
    protected Program $program;
    protected Curriculum $curriculum;
    protected Term $term;
    protected Course $course;
    protected AcademicClass $class;

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

        $this->studentA = User::create([
            'name' => 'Ahmad Fadillah',
            'identifier' => '24060122140001',
            'email' => 'ahmad@arunika.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Student,
            'campus_id' => $this->campus->id,
            'is_active' => true,
        ]);

        $this->studentB = User::create([
            'name' => 'Dewi Lestari',
            'identifier' => '24060122140002',
            'email' => 'dewi@arunika.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Student,
            'campus_id' => $this->campus->id,
            'is_active' => true,
        ]);

        $this->course = Course::create([
            'curriculum_id' => $this->curriculum->id,
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

    public function test_admin_can_view_enrollment_index_with_class(): void
    {
        $response = $this->actingAs($this->admin)->get(
            route('admin.pendaftaran-mahasiswa.index', ['class_id' => $this->class->id])
        );

        $response->assertStatus(200);
        $response->assertSee('Pendaftaran Mahasiswa');
        $response->assertSee('IF2101');
        $response->assertSee('Kelas IF-201-A');
        $response->assertSee('Terdaftar');
        $response->assertSee('Dikeluarkan');
        $response->assertSee('Belum Terdaftar');
        $response->assertSee('Kapasitas Kelas');
    }

    public function test_admin_can_enroll_student_to_class(): void
    {
        $response = $this->actingAs($this->admin)->post(
            route('admin.pendaftaran-mahasiswa.store', $this->class),
            ['user_id' => $this->studentA->id]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('enrollments', [
            'class_id' => $this->class->id,
            'user_id' => $this->studentA->id,
            'status' => 'aktif',
        ]);
    }

    public function test_cannot_enroll_student_when_class_capacity_full(): void
    {
        $this->class->update(['capacity' => 1]);

        // Daftarkan studentA sampai penuh
        Enrollment::create([
            'class_id' => $this->class->id,
            'user_id' => $this->studentA->id,
            'status' => 'aktif',
        ]);

        // Coba daftarkan studentB
        $response = $this->actingAs($this->admin)->post(
            route('admin.pendaftaran-mahasiswa.store', $this->class),
            ['user_id' => $this->studentB->id]
        );

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseMissing('enrollments', [
            'class_id' => $this->class->id,
            'user_id' => $this->studentB->id,
        ]);
    }

    public function test_cannot_enroll_already_active_student(): void
    {
        Enrollment::create([
            'class_id' => $this->class->id,
            'user_id' => $this->studentA->id,
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->admin)->post(
            route('admin.pendaftaran-mahasiswa.store', $this->class),
            ['user_id' => $this->studentA->id]
        );

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_admin_can_remove_student_without_deleting_history(): void
    {
        $enrollment = Enrollment::create([
            'class_id' => $this->class->id,
            'user_id' => $this->studentA->id,
            'status' => 'aktif',
            'final_score' => 88.5,
            'letter_grade' => 'A',
        ]);

        $response = $this->actingAs($this->admin)->delete(
            route('admin.pendaftaran-mahasiswa.destroy', [$this->class, $this->studentA])
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $enrollment->refresh();
        $this->assertEquals('dikeluarkan', $enrollment->status);
        $this->assertNotNull($enrollment->dikeluarkan_at);
        $this->assertEquals($this->admin->id, $enrollment->dikeluarkan_oleh);
        // Nilai tetap utuh
        $this->assertEquals(88.5, $enrollment->final_score);
        $this->assertEquals('A', $enrollment->letter_grade);
    }

    public function test_admin_can_reactivate_previously_removed_student(): void
    {
        $enrollment = Enrollment::create([
            'class_id' => $this->class->id,
            'user_id' => $this->studentA->id,
            'status' => 'dikeluarkan',
            'dikeluarkan_at' => Carbon::now()->subDays(5),
            'dikeluarkan_oleh' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)->post(
            route('admin.pendaftaran-mahasiswa.store', $this->class),
            ['user_id' => $this->studentA->id]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $enrollment->refresh();
        $this->assertEquals('aktif', $enrollment->status);
        $this->assertNull($enrollment->dikeluarkan_at);
        $this->assertNull($enrollment->dikeluarkan_oleh);
    }

    public function test_admin_can_bulk_enroll_students(): void
    {
        $response = $this->actingAs($this->admin)->post(
            route('admin.pendaftaran-mahasiswa.bulk-store', $this->class),
            ['user_ids' => [$this->studentA->id, $this->studentB->id]]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals(2, $this->class->activeEnrollments()->count());
    }

    public function test_admin_can_export_csv(): void
    {
        Enrollment::create([
            'class_id' => $this->class->id,
            'user_id' => $this->studentA->id,
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->admin)->get(
            route('admin.pendaftaran-mahasiswa.export', $this->class)
        );

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_admin_can_download_template_csv(): void
    {
        $response = $this->actingAs($this->admin)->get(
            route('admin.pendaftaran-mahasiswa.template')
        );

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_admin_can_import_csv(): void
    {
        $content = "nim,nama,email\n{$this->studentA->identifier},Ahmad,{$this->studentA->email}\n";
        $file = UploadedFile::fake()->createWithContent('peserta.csv', $content);

        $response = $this->actingAs($this->admin)->post(
            route('admin.pendaftaran-mahasiswa.import', $this->class),
            ['file' => $file]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('enrollments', [
            'class_id' => $this->class->id,
            'user_id' => $this->studentA->id,
            'status' => 'aktif',
        ]);
    }

    public function test_non_admin_cannot_access_or_manage_enrollment(): void
    {
        $response = $this->actingAs($this->lecturer)->get(
            route('admin.pendaftaran-mahasiswa.index')
        );
        $response->assertStatus(403);

        $responsePost = $this->actingAs($this->studentA)->post(
            route('admin.pendaftaran-mahasiswa.store', $this->class),
            ['user_id' => $this->studentB->id]
        );
        $responsePost->assertStatus(403);
    }
}
