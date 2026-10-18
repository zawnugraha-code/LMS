<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicClass;
use App\Models\ActivityLog;
use App\Models\Assignment;
use App\Models\Campus;
use App\Models\Course;
use App\Models\Curriculum;
use App\Models\Enrollment;
use App\Models\Faculty;
use App\Models\Program;
use App\Models\Submission;
use App\Models\Term;
use App\Models\User;
use App\Services\Admin\ActivityLogger;
use App\Services\Admin\CsvImporterService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $lecturer;
    protected User $student;
    protected Campus $campus;
    protected Term $term;
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

    public function test_guest_cannot_access_admin_dashboard_and_redirects(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_student_and_lecturer_are_forbidden_from_admin_portal(): void
    {
        $this->actingAs($this->student)
            ->get(route('admin.dashboard'))
            ->assertStatus(403);

        $this->actingAs($this->lecturer)
            ->get(route('admin.dashboard'))
            ->assertStatus(403);
    }

    public function test_inactive_admin_is_logged_out_and_redirected(): void
    {
        $this->admin->update(['is_active' => false]);

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $response->assertRedirect(route('marketing.home'));
        $this->assertGuest();
    }

    public function test_active_admin_can_access_admin_routes(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);

        // Test placeholder routes for Batch 2
        $this->actingAs($this->admin)
            ->get(route('admin.pengguna.dosen.index'))
            ->assertStatus(200);

        $this->actingAs($this->admin)
            ->get(route('admin.log-aktivitas.index'))
            ->assertStatus(200);
    }

    public function test_activity_logger_records_append_only_log_and_redacts_passwords(): void
    {
        $log = ActivityLogger::log(
            aksi: 'buat_pengguna',
            kategori: 'keamanan',
            subjek: $this->admin,
            deskripsi: 'Membuat akun pengujian',
            properti: [
                'email' => 'test@arunika.test',
                'password' => 'secret12345',
                'role' => 'admin',
            ],
            userId: $this->admin->id
        );

        $this->assertDatabaseHas('activity_logs', [
            'id' => $log->id,
            'aksi' => 'buat_pengguna',
            'kategori' => 'keamanan',
            'user_id' => $this->admin->id,
        ]);

        $this->assertEquals('[REDACTED]', $log->properti['password']);
        $this->assertEquals('test@arunika.test', $log->properti['email']);
    }

    public function test_csv_importer_parses_valid_csv_and_protects_against_formula_injection(): void
    {
        $csvContent = "nim,nama,email\n";
        $csvContent .= "240601,Budi Santoso,budi@mhs.uni.ac.id\n";
        $csvContent .= "=SUM(A1:A10),Malicious User,hacker@mhs.uni.ac.id\n";

        $tempFile = tempnam(sys_get_temp_dir(), 'csv_test_');
        file_put_contents($tempFile, $csvContent);

        $importer = new CsvImporterService();
        $result = $importer->parseAndValidate($tempFile, ['nim', 'nama', 'email']);

        unlink($tempFile);

        $this->assertEquals(2, $result['total_rows']);
        $this->assertEquals(2, $result['valid_count']);
        $this->assertEquals(0, $result['error_count']);

        // Assert formula injection is escaped with single quote
        $this->assertEquals("'=SUM(A1:A10)", $result['valid'][1]['nim']);
    }

    public function test_create_admin_command_creates_admin_user_via_cli(): void
    {
        $this->artisan('arunika:buat-admin', [
            '--name' => 'Super Admin CLI',
            '--email' => 'cli_admin@arunika.test',
            '--password' => 'password123',
        ])
            ->assertExitCode(0);

        $this->assertDatabaseHas('users', [
            'email' => 'cli_admin@arunika.test',
            'role' => UserRole::Admin->value,
            'is_active' => 1,
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'aksi' => 'buat_admin',
            'kategori' => 'keamanan',
        ]);
    }

    public function test_term_model_helpers_and_scopes(): void
    {
        $this->assertTrue($this->term->isCurrentActive());
        $this->assertFalse($this->term->isFinished());

        $termSelesai = Term::create([
            'campus_id' => $this->campus->id,
            'name' => 'Semester Lalu',
            'academic_year' => '2025/2026',
            'semester_type' => 'even',
            'start_date' => Carbon::create(2026, 2, 1),
            'end_date' => Carbon::create(2026, 7, 31),
            'is_active' => false,
            'status' => 'selesai',
        ]);

        $this->assertTrue($termSelesai->isFinished());
        $this->assertCount(1, Term::aktif()->get());
        $this->assertCount(1, Term::selesai()->get());
    }

    public function test_class_capacity_and_finished_status(): void
    {
        $course = Course::create([
            'curriculum_id' => $this->curriculum->id,
            'code' => 'IF2105',
            'name' => 'Pemrograman Web Lanjut',
            'credits' => 3,
        ]);

        $class = AcademicClass::create([
            'course_id' => $course->id,
            'term_id' => $this->term->id,
            'lecturer_id' => $this->lecturer->id,
            'section' => 'A',
            'capacity' => 2,
            'status' => 'aktif',
        ]);

        $this->assertFalse($class->isFull());
        $this->assertEquals(2, $class->remainingCapacity());

        Enrollment::create([
            'class_id' => $class->id,
            'user_id' => $this->student->id,
            'status' => 'aktif',
        ]);

        $this->assertFalse($class->isFull());
        $this->assertEquals(1, $class->remainingCapacity());

        $student2 = User::create([
            'name' => 'Siti Aminah Mhs',
            'email' => 'siti_mhs@arunika.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Student,
        ]);

        Enrollment::create([
            'class_id' => $class->id,
            'user_id' => $student2->id,
            'status' => 'aktif',
        ]);

        $this->assertTrue($class->isFull());
        $this->assertEquals(0, $class->remainingCapacity());
    }

    public function test_reassigning_lecturer_transfers_assignment_and_grading_access(): void
    {
        $course = Course::create([
            'curriculum_id' => $this->curriculum->id,
            'code' => 'IF2101',
            'name' => 'PBO',
            'credits' => 3,
        ]);

        $class = AcademicClass::create([
            'course_id' => $course->id,
            'term_id' => $this->term->id,
            'lecturer_id' => $this->lecturer->id,
            'section' => 'A',
            'capacity' => 40,
            'status' => 'aktif',
        ]);

        $assignment = Assignment::create([
            'class_id' => $class->id,
            'dosen_id' => $this->lecturer->id,
            'title' => 'Tugas 1 PBO',
            'status' => 'dipublikasikan',
            'max_score' => 100,
            'due_at' => Carbon::now()->addDays(7),
        ]);

        $newLobbyLecturer = User::create([
            'name' => 'Dr. Hendra Wijaya',
            'email' => 'hendra@arunika.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Lecturer,
            'is_active' => true,
        ]);

        // Dr. Budi can manage assignment initially
        $this->assertTrue($this->lecturer->can('update', $assignment));
        $this->assertFalse($newLobbyLecturer->can('update', $assignment));

        // Reassign class lecturer to Dr. Hendra
        $class->update(['lecturer_id' => $newLobbyLecturer->id]);
        $assignment->refresh();

        // Old lecturer loses management access; new lecturer gains it
        $this->assertFalse($this->lecturer->can('update', $assignment));
        $this->assertTrue($newLobbyLecturer->can('update', $assignment));
    }
}
