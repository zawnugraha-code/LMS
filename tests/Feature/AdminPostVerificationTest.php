<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicClass;
use App\Models\Assignment;
use App\Models\Campus;
use App\Models\Course;
use App\Models\Curriculum;
use App\Models\DiscussionCategory;
use App\Models\DiscussionThread;
use App\Models\Enrollment;
use App\Models\Faculty;
use App\Models\Material;
use App\Models\Meeting;
use App\Models\Program;
use App\Models\Submission;
use App\Models\Term;
use App\Models\User;
use App\Services\Admin\EnrollmentAdminService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;
use Tests\TestCase;

class AdminPostVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $lecturerA;
    protected User $lecturerB;
    protected User $student;
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

        $this->course = Course::create([
            'curriculum_id' => $this->curriculum->id,
            'code' => 'IF2101',
            'name' => 'Pemrograman Berorientasi Objek',
            'credits' => 3,
            'semester' => 3,
        ]);

        $this->admin = User::create([
            'name' => 'Rina Kusumawati',
            'email' => 'admin@arunika.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
            'campus_id' => $this->campus->id,
            'is_active' => true,
        ]);

        $this->lecturerA = User::create([
            'name' => 'Dr. Budi Rahardjo',
            'email' => 'dosen@arunika.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Lecturer,
            'campus_id' => $this->campus->id,
            'is_active' => true,
        ]);

        $this->lecturerB = User::create([
            'name' => 'Dr. Hendra Wijaya',
            'email' => 'hendra@arunika.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Lecturer,
            'campus_id' => $this->campus->id,
            'is_active' => true,
        ]);

        $this->student = User::create([
            'name' => 'Arya Pratama',
            'email' => 'mahasiswa@arunika.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Student,
            'campus_id' => $this->campus->id,
            'identifier' => '24060122130001',
            'is_active' => true,
        ]);
    }

    /**
     * 1. Role Switcher Guard: Whitelist & Production Lockdown.
     */
    public function test_role_switcher_whitelist_and_production_lockdown(): void
    {
        // 1a. Peran dalam whitelist (admin, lecturer, student) berhasil di testing/local
        $resAdmin = $this->get(route('demo.login', ['role' => 'admin']));
        $resAdmin->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->admin);

        auth()->logout();

        $resDosen = $this->get(route('demo.login', ['role' => 'lecturer']));
        $resDosen->assertRedirect(route('dosen.dashboard'));
        $this->assertAuthenticatedAs($this->lecturerA);

        auth()->logout();

        $resStudent = $this->get(route('demo.login', ['role' => 'student']));
        $resStudent->assertRedirect(route('app.dashboard'));
        $this->assertAuthenticatedAs($this->student);

        auth()->logout();

        // 1b. Peran di luar whitelist (auditor, mentor, hacker) harus ditolak 404
        $resHacker = $this->get('/demo/login/hacker');
        $resHacker->assertStatus(404);

        $resAuditor = $this->get('/demo/login/auditor');
        $resAuditor->assertStatus(404);

        // 1c. Pada environment production, akses selalu 404 walaupun flag true
        $this->app->detectEnvironment(fn () => 'production');
        config(['arunika.demo_role_switcher_enabled' => true]);

        $resProd = $this->get('/demo/login/admin');
        $resProd->assertStatus(404);
    }

    /**
     * 3a. Kelas Selesai: Test level HTTP dosen ditolak POST mutasi & mahasiswa ditolak aksi.
     */
    public function test_finished_class_restricts_lecturer_and_student_mutations(): void
    {
        $class = AcademicClass::create([
            'course_id' => $this->course->id,
            'term_id' => $this->term->id,
            'lecturer_id' => $this->lecturerA->id,
            'section' => 'A',
            'capacity' => 40,
            'status' => 'selesai', // Kelas selesai
        ]);

        $meeting = Meeting::create([
            'class_id' => $class->id,
            'week' => 1,
            'title' => 'Pertemuan 1',
            'date' => now()->toDateString(),
            'start_time' => '08:00',
            'end_time' => '10:30',
            'mode' => 'offline',
            'is_published' => true,
        ]);

        $assignment = Assignment::create([
            'class_id' => $class->id,
            'meeting_id' => $meeting->id,
            'dosen_id' => $this->lecturerA->id,
            'title' => 'Tugas PBO',
            'status' => 'dipublikasikan',
            'max_score' => 100,
            'due_at' => now()->addDays(7),
        ]);

        $material = Material::create([
            'meeting_id' => $meeting->id,
            'title' => 'Slide Kuliah',
            'type' => 'slide',
            'uploaded_by' => $this->lecturerA->id,
        ]);

        $enrollment = Enrollment::create([
            'class_id' => $class->id,
            'user_id' => $this->student->id,
            'status' => 'aktif',
        ]);

        $submission = Submission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $this->student->id,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $category = DiscussionCategory::create([
            'name' => 'Tanya Jawab',
            'slug' => 'tanya-jawab',
            'icon' => 'help-circle',
            'color' => '#2563EB',
        ]);

        $thread = DiscussionThread::create([
            'class_id' => $class->id,
            'category_id' => $category->id,
            'user_id' => $this->student->id,
            'title' => 'Diskusi OOP Kelas Selesai',
            'content' => 'Bagaimana implementasi polimorfisme?',
        ]);

        // Verifikasi Policy level
        $this->assertFalse($this->lecturerA->can('update', $assignment));
        $this->assertFalse($this->lecturerA->can('delete', $assignment));
        $this->assertFalse($this->lecturerA->can('manage', $material));
        $this->assertFalse($this->lecturerA->can('grade', $submission));

        // HTTP Dosen: 1. Ditolak POST buat tugas baru pada kelas selesai
        $resStoreTugas = $this->actingAs($this->lecturerA)->post(route('dosen.tugas.store'), [
            'class_id' => $class->id,
            'title' => 'Tugas Baru Kelas Selesai',
            'jenis' => 'tugas',
            'mode' => 'individu',
            'metode' => 'unggah_berkas',
            'max_score' => 100,
            'due_at' => now()->addDays(5)->toDateTimeString(),
            'status' => 'draft',
        ]);
        $this->assertTrue(in_array($resStoreTugas->status(), [403, 404], true), 'Dosen harus ditolak membuat tugas di kelas selesai');

        // HTTP Dosen: 2. Ditolak POST buat materi pada kelas selesai
        $resStoreMateri = $this->actingAs($this->lecturerA)->post(route('dosen.materi.store'), [
            'class_id' => $class->id,
            'week' => 2,
            'title' => 'Materi Baru Kelas Selesai',
            'type' => 'slide',
            'status' => 'published',
        ]);
        $this->assertTrue(in_array($resStoreMateri->status(), [403, 404], true), 'Dosen harus ditolak membuat materi di kelas selesai');

        // HTTP Dosen: 3. Ditolak POST simpan presensi pada kelas selesai
        $resStorePresensi = $this->actingAs($this->lecturerA)->post(route('dosen.presensi.store'), [
            'meeting_id' => $meeting->id,
            'mahasiswa' => [
                $this->student->id => [
                    'status' => 'hadir',
                    'catatan' => 'Hadir tepat waktu',
                ],
            ],
        ]);
        $this->assertTrue(in_array($resStorePresensi->status(), [403, 404], true), 'Dosen harus ditolak menyimpan presensi di kelas selesai');

        // HTTP Dosen: 4. Ditolak POST simpan nilai pada kelas selesai
        $resGrade = $this->actingAs($this->lecturerA)->post(route('dosen.penilaian.grade', $submission), [
            'score' => 95,
            'feedback' => 'Sangat baik',
        ]);
        $this->assertTrue(in_array($resGrade->status(), [403, 404], true), 'Dosen harus ditolak menyimpan nilai di kelas selesai');

        // HTTP Mahasiswa: 5. Ditolak POST pengumpulan tugas pada kelas selesai
        $resSubmit = $this->actingAs($this->student)->post(route('assignments.submit', $assignment), [
            'content' => 'Pengumpulan tugas setelah kelas selesai',
        ]);
        $this->assertTrue(in_array($resSubmit->status(), [403, 404], true), 'Mahasiswa harus ditolak mengumpulkan tugas di kelas selesai');

        // HTTP Mahasiswa: 6. Ditolak POST balasan diskusi pada kelas selesai
        $resReply = $this->actingAs($this->student)->post(route('app.discussions.reply', $thread), [
            'content' => 'Tanggapan setelah kelas selesai ditutup',
        ]);
        $this->assertTrue(in_array($resReply->status(), [403, 404], true), 'Mahasiswa harus ditolak membalas diskusi di kelas selesai');
    }

    /**
     * 3b. Mahasiswa Dikeluarkan: Test level HTTP ditolak akses kelas, materi, tugas, dan dipulihkan.
     */
    public function test_removed_student_is_denied_access_and_regains_access_upon_restoration(): void
    {
        $class = AcademicClass::create([
            'course_id' => $this->course->id,
            'term_id' => $this->term->id,
            'lecturer_id' => $this->lecturerA->id,
            'section' => 'A',
            'capacity' => 40,
            'status' => 'aktif',
        ]);

        $meeting = Meeting::create([
            'class_id' => $class->id,
            'week' => 1,
            'title' => 'Pertemuan 1',
            'date' => now()->toDateString(),
            'start_time' => '08:00',
            'end_time' => '10:30',
            'mode' => 'offline',
            'is_published' => true,
        ]);

        $assignment = Assignment::create([
            'class_id' => $class->id,
            'meeting_id' => $meeting->id,
            'dosen_id' => $this->lecturerA->id,
            'title' => 'Tugas 1 Pemrograman',
            'status' => 'dipublikasikan',
            'max_score' => 100,
            'due_at' => now()->addDays(7),
        ]);

        $material = Material::create([
            'meeting_id' => $meeting->id,
            'title' => 'Modul 1 Pengenalan OOP',
            'type' => 'pdf',
            'uploaded_by' => $this->lecturerA->id,
            'status' => 'published',
        ]);

        $enrollService = app(EnrollmentAdminService::class);

        // 1. Daftarkan mahasiswa (status aktif)
        $enrollment = $enrollService->enrollStudent($class, $this->student->id);
        $this->assertEquals('aktif', $enrollment->status);

        // Mahasiswa aktif: akses 200 OK
        $this->actingAs($this->student)->get(route('app.courses.show', $class))->assertStatus(200);
        $this->actingAs($this->student)->get(route('app.courses.classroom', $class))->assertStatus(200);
        $this->actingAs($this->student)->get(route('app.assignments'))->assertSee('Tugas 1 Pemrograman');
        $this->actingAs($this->student)->get(route('app.materials', ['class' => $class->id]))->assertSee('Modul 1 Pengenalan OOP');
        $this->actingAs($this->student)->get(route('assignments.download', $assignment))->assertStatus(200);

        // 2. Admin mengeluarkan mahasiswa (soft-remove)
        $enrollService->removeStudent($class, $this->student->id, $this->admin->id);
        $enrollment->refresh();
        $this->assertEquals('dikeluarkan', $enrollment->status);

        // HTTP Mahasiswa Dikeluarkan: Semua harus ditolak (403/404) atau tidak tampil
        $resCourseShow = $this->actingAs($this->student)->get(route('app.courses.show', $class));
        $this->assertTrue(in_array($resCourseShow->status(), [403, 404], true), 'Mahasiswa dikeluarkan harus ditolak melihat detail mata kuliah (403/404)');

        $resClassroom = $this->actingAs($this->student)->get(route('app.courses.classroom', $class));
        $this->assertTrue(in_array($resClassroom->status(), [403, 404], true), 'Mahasiswa dikeluarkan harus ditolak masuk classroom (403/404)');

        $resAssignments = $this->actingAs($this->student)->get(route('app.assignments'));
        $resAssignments->assertDontSee('Tugas 1 Pemrograman');

        $resMaterials = $this->actingAs($this->student)->get(route('app.materials', ['class' => $class->id]));
        $resMaterials->assertDontSee('Modul 1 Pengenalan OOP');

        $resDownload = $this->actingAs($this->student)->get(route('assignments.download', $assignment));
        $this->assertTrue(in_array($resDownload->status(), [403, 404], true), 'Mahasiswa dikeluarkan harus ditolak mengunduh file tugas (403/404)');

        $resSubmit = $this->actingAs($this->student)->post(route('assignments.submit', $assignment), [
            'content' => 'Pengumpulan oleh mahasiswa yang dikeluarkan',
        ]);
        $this->assertTrue(in_array($resSubmit->status(), [403, 404], true), 'Mahasiswa dikeluarkan harus ditolak submit tugas (403/404)');

        // 3. Admin memulihkan pendaftaran mahasiswa
        $restored = $enrollService->enrollStudent($class, $this->student->id);
        $restored->refresh();
        $this->assertEquals('aktif', $restored->status);
        $this->assertNull($restored->dikeluarkan_at);

        // Setelah dipulihkan, semuanya kembali bisa
        $this->actingAs($this->student)->get(route('app.courses.show', $class))->assertStatus(200);
        $this->actingAs($this->student)->get(route('app.courses.classroom', $class))->assertStatus(200);
        $this->actingAs($this->student)->get(route('app.assignments'))->assertSee('Tugas 1 Pemrograman');
        $this->actingAs($this->student)->get(route('app.materials', ['class' => $class->id]))->assertSee('Modul 1 Pengenalan OOP');
        $this->actingAs($this->student)->get(route('assignments.download', $assignment))->assertStatus(200);

        $resSubmitRestored = $this->actingAs($this->student)->post(route('assignments.submit', $assignment), [
            'content' => 'Pengumpulan kembali setelah dipulihkan',
        ]);
        $resSubmitRestored->assertRedirect();
    }

    /**
     * 3c. Mahasiswa tidak boleh di dua kelas dari mata kuliah yang sama pada semester yang sama.
     */
    public function test_student_cannot_enroll_in_two_classes_of_same_course_in_same_term(): void
    {
        $classA = AcademicClass::create([
            'course_id' => $this->course->id,
            'term_id' => $this->term->id,
            'lecturer_id' => $this->lecturerA->id,
            'section' => 'A',
            'capacity' => 40,
            'status' => 'aktif',
        ]);

        $classB = AcademicClass::create([
            'course_id' => $this->course->id,
            'term_id' => $this->term->id,
            'lecturer_id' => $this->lecturerB->id,
            'section' => 'B',
            'capacity' => 40,
            'status' => 'aktif',
        ]);

        $enrollService = app(EnrollmentAdminService::class);

        // Daftarkan di Seksi A berhasil
        $enrollA = $enrollService->enrollStudent($classA, $this->student->id);
        $this->assertEquals('aktif', $enrollA->status);

        // Mendaftarkan mahasiswa yang sama ke Seksi B pada mata kuliah & semester yang sama harus melempar pengecualian
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('sudah terdaftar di seksi lain untuk mata kuliah');

        $enrollService->enrollStudent($classB, $this->student->id);
    }

    /**
     * 3d. Ganti Dosen: Dosen lama ditolak akses kelas dan badge "Perlu Dinilai" berpindah.
     */
    public function test_reassign_lecturer_denies_old_lecturer_and_transfers_pending_grading_badge(): void
    {
        $class = AcademicClass::create([
            'course_id' => $this->course->id,
            'term_id' => $this->term->id,
            'lecturer_id' => $this->lecturerA->id,
            'section' => 'A',
            'capacity' => 40,
            'status' => 'aktif',
        ]);

        $meeting = Meeting::create([
            'class_id' => $class->id,
            'week' => 1,
            'title' => 'Pertemuan 1',
            'date' => now()->toDateString(),
            'start_time' => '08:00',
            'end_time' => '10:30',
            'mode' => 'offline',
            'is_published' => true,
        ]);

        $assignment = Assignment::create([
            'class_id' => $class->id,
            'meeting_id' => $meeting->id,
            'dosen_id' => $this->lecturerA->id,
            'title' => 'Tugas 1',
            'status' => 'dipublikasikan',
            'max_score' => 100,
            'due_at' => now()->addDays(7),
        ]);

        Submission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $this->student->id,
            'status' => 'submitted',
            'score' => null,
            'submitted_at' => now(),
        ]);

        // Awal: Dosen A memiliki hak mengajar & 1 submission perlu dinilai
        $this->assertTrue($this->lecturerA->can('manage', $class));
        $this->assertFalse($this->lecturerB->can('manage', $class));

        $countPendingA = Submission::whereNull('score')
            ->where('status', 'submitted')
            ->whereHas('assignment.academicClass', fn ($q) => $q->where('lecturer_id', $this->lecturerA->id))
            ->count();
        $this->assertEquals(1, $countPendingA);

        $countPendingB = Submission::whereNull('score')
            ->where('status', 'submitted')
            ->whereHas('assignment.academicClass', fn ($q) => $q->where('lecturer_id', $this->lecturerB->id))
            ->count();
        $this->assertEquals(0, $countPendingB);

        // Admin mengganti dosen pengampu ke Dosen B via PUT /admin/kelas/{id}
        $response = $this->actingAs($this->admin)->put(route('admin.kelas.update', $class), [
            'course_id' => $class->course_id,
            'term_id' => $class->term_id,
            'section' => $class->section,
            'lecturer_id' => $this->lecturerB->id,
            'room' => 'Lab Baru',
            'schedule_day' => 'Kamis',
            'start_time' => '10:00',
            'end_time' => '12:30',
            'capacity' => 45,
            'status' => 'aktif',
        ]);
        $response->assertRedirect(route('admin.kelas.show', $class));

        $class->refresh();
        $assignment->refresh();

        // 1. Dosen lama (A) kehilangan hak kelola kelas; Dosen baru (B) memperoleh hak kelola
        $this->assertFalse($this->lecturerA->can('manage', $class));
        $this->assertTrue($this->lecturerB->can('manage', $class));

        // 2. Dosen lama (A) ditolak mengakses kelas di portal dosen
        $resOldDosen = $this->actingAs($this->lecturerA)->get(route('dosen.kelas.show', $class));
        $resOldDosen->assertStatus(403);

        // 3. Badge "Perlu Dinilai" berpindah: Dosen A = 0, Dosen B = 1
        $countPendingAfterA = Submission::whereNull('score')
            ->where('status', 'submitted')
            ->whereHas('assignment.academicClass', fn ($q) => $q->where('lecturer_id', $this->lecturerA->id))
            ->count();
        $this->assertEquals(0, $countPendingAfterA);

        $countPendingAfterB = Submission::whereNull('score')
            ->where('status', 'submitted')
            ->whereHas('assignment.academicClass', fn ($q) => $q->where('lecturer_id', $this->lecturerB->id))
            ->count();
        $this->assertEquals(1, $countPendingAfterB);
    }
}
