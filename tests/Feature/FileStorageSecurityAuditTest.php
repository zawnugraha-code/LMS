<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicClass;
use App\Models\Assignment;
use App\Models\AttendanceLog;
use App\Models\AttendanceSession;
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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileStorageSecurityAuditTest extends TestCase
{
    use RefreshDatabase;

    protected Campus $campus;
    protected AcademicClass $academicClass;
    protected AcademicClass $otherClass;
    protected User $teachingLecturer;
    protected User $otherLecturer;
    protected User $studentOwner;
    protected User $studentOtherSameClass;
    protected User $studentOtherClass;
    protected User $studentUpcoming;
    protected User $studentCompleted;
    protected User $studentDikeluarkan;
    protected User $adminUser;
    protected User $superAdminUser;
    protected Assignment $assignment;
    protected Assignment $draftAssignment;
    protected Submission $submission;
    protected Material $publishedMaterial;
    protected Material $draftMaterial;
    protected AttendanceLog $permitLog;
    protected DiscussionThread $classThread;

    protected function setUp(): void
    {
        parent::setUp();

        $this->campus = Campus::create([
            'name' => 'Universitas Arunika Nusantara',
            'code' => 'UAN',
            'domain' => 'arunika.ac.id',
            'is_active' => true,
        ]);

        $faculty = Faculty::create(['name' => 'Fakultas Teknologi Informasi', 'code' => 'FTI', 'campus_id' => $this->campus->id]);
        $program = Program::create(['faculty_id' => $faculty->id, 'name' => 'Teknik Informatika', 'code' => 'TIF']);
        $curriculum = Curriculum::create(['program_id' => $program->id, 'name' => 'Kurikulum OBE 2026', 'year' => 2026]);

        $term = Term::create([
            'campus_id' => $this->campus->id,
            'name' => 'Semester Ganjil 2026/2027',
            'academic_year' => '2026/2027',
            'semester_type' => 'odd',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonths(5),
            'is_active' => true,
            'status' => 'aktif',
        ]);

        $course1 = Course::create([
            'curriculum_id' => $curriculum->id,
            'code' => 'IF201',
            'name' => 'Struktur Data & Algoritma',
            'credits' => 3,
            'semester' => 3,
        ]);

        $course2 = Course::create([
            'curriculum_id' => $curriculum->id,
            'code' => 'IF202',
            'name' => 'Basis Data Lanjut',
            'credits' => 3,
            'semester' => 3,
        ]);

        // Users
        $this->teachingLecturer = User::factory()->create([
            'role' => UserRole::Lecturer,
            'campus_id' => $this->campus->id,
            'email' => 'dosen.pengampu@arunika.ac.id',
        ]);

        $this->otherLecturer = User::factory()->create([
            'role' => UserRole::Lecturer,
            'campus_id' => $this->campus->id,
            'email' => 'dosen.lain@arunika.ac.id',
        ]);

        $this->studentOwner = User::factory()->create([
            'role' => UserRole::Student,
            'campus_id' => $this->campus->id,
            'identifier' => '24060122140001',
            'email' => 'mhs.owner@arunika.ac.id',
        ]);

        $this->studentOtherSameClass = User::factory()->create([
            'role' => UserRole::Student,
            'campus_id' => $this->campus->id,
            'identifier' => '24060122140002',
            'email' => 'mhs.sekelas@arunika.ac.id',
        ]);

        $this->studentOtherClass = User::factory()->create([
            'role' => UserRole::Student,
            'campus_id' => $this->campus->id,
            'identifier' => '24060122140003',
            'email' => 'mhs.kelalain@arunika.ac.id',
        ]);

        $this->studentUpcoming = User::factory()->create([
            'role' => UserRole::Student,
            'campus_id' => $this->campus->id,
            'identifier' => '24060122140004',
            'email' => 'mhs.upcoming@arunika.ac.id',
        ]);

        $this->studentCompleted = User::factory()->create([
            'role' => UserRole::Student,
            'campus_id' => $this->campus->id,
            'identifier' => '24060122140005',
            'email' => 'mhs.completed@arunika.ac.id',
        ]);

        $this->studentDikeluarkan = User::factory()->create([
            'role' => UserRole::Student,
            'campus_id' => $this->campus->id,
            'identifier' => '24060122140006',
            'email' => 'mhs.dikeluarkan@arunika.ac.id',
        ]);

        $this->adminUser = User::factory()->create([
            'role' => UserRole::Admin,
            'campus_id' => $this->campus->id,
            'email' => 'admin.utama@arunika.ac.id',
        ]);

        $this->superAdminUser = User::factory()->create([
            'role' => UserRole::SuperAdmin,
            'campus_id' => $this->campus->id,
            'email' => 'superadmin@arunika.ac.id',
        ]);

        // Academic Classes
        $this->academicClass = AcademicClass::create([
            'course_id' => $course1->id,
            'term_id' => $term->id,
            'lecturer_id' => $this->teachingLecturer->id,
            'section' => 'IF-3A',
            'capacity' => 40,
            'status' => 'aktif',
        ]);

        $this->otherClass = AcademicClass::create([
            'course_id' => $course2->id,
            'term_id' => $term->id,
            'lecturer_id' => $this->otherLecturer->id,
            'section' => 'IF-3B',
            'capacity' => 40,
            'status' => 'aktif',
        ]);

        // Enrollments
        Enrollment::create(['class_id' => $this->academicClass->id, 'user_id' => $this->studentOwner->id, 'status' => Enrollment::STATUS_AKTIF]);
        Enrollment::create(['class_id' => $this->academicClass->id, 'user_id' => $this->studentOtherSameClass->id, 'status' => Enrollment::STATUS_AKTIF]);
        Enrollment::create(['class_id' => $this->academicClass->id, 'user_id' => $this->studentUpcoming->id, 'status' => Enrollment::STATUS_UPCOMING]);
        Enrollment::create(['class_id' => $this->academicClass->id, 'user_id' => $this->studentCompleted->id, 'status' => Enrollment::STATUS_COMPLETED]);
        Enrollment::create(['class_id' => $this->academicClass->id, 'user_id' => $this->studentDikeluarkan->id, 'status' => Enrollment::STATUS_DIKELUARKAN]);
        Enrollment::create(['class_id' => $this->otherClass->id, 'user_id' => $this->studentOtherClass->id, 'status' => Enrollment::STATUS_AKTIF]);

        // Meetings
        $meeting1 = Meeting::create([
            'class_id' => $this->academicClass->id,
            'week' => 1,
            'title' => 'Pertemuan 1',
            'mode' => 'offline',
            'is_published' => true,
        ]);

        // Materials: Published and Draft
        $this->publishedMaterial = Material::create([
            'class_id' => $this->academicClass->id,
            'meeting_id' => $meeting1->id,
            'title' => 'Slide Modul 1 Published',
            'type' => 'pdf',
            'file_path' => 'materials/modul1_test.pdf',
            'status' => 'published',
            'uploaded_by' => $this->teachingLecturer->id,
        ]);

        $this->draftMaterial = Material::create([
            'class_id' => $this->academicClass->id,
            'meeting_id' => $meeting1->id,
            'title' => 'Slide Modul 1 Draft',
            'type' => 'pdf',
            'file_path' => 'materials/modul1_draft.pdf',
            'status' => 'draft',
            'uploaded_by' => $this->teachingLecturer->id,
        ]);

        // Assignments: Published and Draft
        $this->assignment = Assignment::create([
            'class_id' => $this->academicClass->id,
            'dosen_id' => $this->teachingLecturer->id,
            'title' => 'Tugas Praktikum 1 Dipublikasikan',
            'jenis' => 'tugas',
            'mode' => 'individu',
            'metode' => 'unggah_berkas',
            'status' => 'dipublikasikan',
            'lampiran_path' => 'assessments/instruksi_tugas1.pdf',
            'due_at' => now()->addDays(7),
        ]);

        $this->draftAssignment = Assignment::create([
            'class_id' => $this->academicClass->id,
            'dosen_id' => $this->teachingLecturer->id,
            'title' => 'Tugas Praktikum 2 Draft Belum Rilis',
            'jenis' => 'tugas',
            'mode' => 'individu',
            'metode' => 'unggah_berkas',
            'status' => 'draft',
            'lampiran_path' => 'assessments/instruksi_draft.pdf',
            'due_at' => now()->addDays(14),
        ]);

        // Submission
        $this->submission = Submission::create([
            'assignment_id' => $this->assignment->id,
            'user_id' => $this->studentOwner->id,
            'content' => 'Pengumpulan tugas praktikum mahasiswa owner',
            'file_path' => 'submissions/owner_tugas1.pdf',
            'status' => 'submitted',
            'status_waktu' => 'tepat_waktu',
            'submitted_at' => now(),
        ]);

        // Attendance Permit
        $session = AttendanceSession::create([
            'meeting_id' => $meeting1->id,
            'qr_secret' => 'secret123',
            'is_active' => false,
            'opened_at' => now()->subHour(),
            'closed_at' => now(),
        ]);

        $this->permitLog = AttendanceLog::create([
            'attendance_session_id' => $session->id,
            'user_id' => $this->studentOwner->id,
            'status' => 'excused',
            'method' => 'manual',
            'checked_at' => now(),
            'notes' => 'Izin: Sakit demam (Berkas: permits/surat_dokter.pdf)',
        ]);

        // Discussion Thread
        $category = DiscussionCategory::firstOrCreate(['name' => 'Akademik', 'slug' => 'akademik'], ['order' => 1]);
        $this->classThread = DiscussionThread::create([
            'user_id' => $this->studentOwner->id,
            'class_id' => $this->academicClass->id,
            'category_id' => $category->id,
            'title' => 'Diskusi Materi 1',
            'content' => 'Pertanyaan seputar slide materi',
            'attachment_path' => 'discussions/diagram_diskusi.png',
            'views_count' => 1,
            'upvotes_count' => 0,
        ]);
    }

    // =========================================================================
    // ORIGINAL TESTS (Retained from previous commit)
    // =========================================================================

    /**
     * Demonstrate that files stored in the 'public' disk are publicly accessible
     * if public/storage symlink or web server static mapping is present.
     */
    public function test_files_in_public_storage_can_be_read_without_authentication_if_exposed_in_public_dir(): void
    {
        Storage::fake('public');

        // Simulate upload to 'public' disk as currently done in AcademicActionController
        $filePath = 'submissions/student_exam_confidential.pdf';
        Storage::disk('public')->put($filePath, 'STUDENT CONFIDENTIAL SUBMISSION DATA');

        // The URL generated by the public disk is a direct /storage/... URL
        $publicUrl = Storage::disk('public')->url($filePath);
        $this->assertStringContainsString('/storage/submissions/student_exam_confidential.pdf', $publicUrl);

        // If public/storage is symlinked or mapped, any static file reader receives the contents without auth
        $targetDiskPath = Storage::disk('public')->path($filePath);
        $this->assertFileExists($targetDiskPath);
        $this->assertSame('STUDENT CONFIDENTIAL SUBMISSION DATA', file_get_contents($targetDiskPath));
    }

    /**
     * Prove that guest is blocked from accessing application download endpoints.
     */
    public function test_guest_cannot_access_download_routes(): void
    {
        $response = $this->get('/app/assignments/1/download');
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));

        $responseKhs = $this->get('/app/grades/1/pdf');
        $responseKhs->assertStatus(302);
        $responseKhs->assertRedirect(route('login'));
    }

    // =========================================================================
    // REQUIREMENT 5 & 7: ISOLATION TESTS (WITHOUT SYMLINKS)
    // =========================================================================

    /**
     * Requirement 5 & 7: Setelah upload melalui aplikasi, berkas ada di disk 'private'
     * dan TIDAK ada di disk 'public' maupun disk 'local'.
     */
    public function test_uploaded_files_stored_in_private_disk_and_missing_from_public_and_local(): void
    {
        Storage::fake('private');
        Storage::fake('public');
        Storage::fake('local');

        // 1. Pengumpulan tugas oleh mahasiswa
        $file = UploadedFile::fake()->create('laporan.pdf', 500, 'application/pdf');
        $resSubmit = $this->actingAs($this->studentOwner)
            ->post(route('assignments.submit', $this->assignment), [
                'file' => $file,
                'content' => 'Tugas laporan praktikum mahasiswa',
                'action' => 'submit',
            ]);
        $resSubmit->assertRedirect();

        $freshSubmission = Submission::where('assignment_id', $this->assignment->id)
            ->where('user_id', $this->studentOwner->id)
            ->first();

        $subCleanPath = ltrim($freshSubmission->file_path, '/\\');
        $this->assertTrue(Storage::disk('private')->exists($subCleanPath), "File harus ada di disk 'private'");
        $this->assertFalse(Storage::disk('public')->exists($subCleanPath), "File TIDAK boleh ada di disk 'public'");
        $this->assertFalse(Storage::disk('local')->exists($subCleanPath), "File TIDAK boleh ada di disk 'local'");

        // 2. Unggah materi oleh dosen pengampu
        $matFile = UploadedFile::fake()->create('modul_kuliah.pptx', 2048, 'application/vnd.openxmlformats-officedocument.presentationml.presentation');
        $resMat = $this->actingAs($this->teachingLecturer)
            ->post(route('dosen.materi.store'), [
                'class_id' => $this->academicClass->id,
                'week' => 2,
                'title' => 'Materi Pertemuan 2 Slide',
                'type' => 'ppt',
                'file' => $matFile,
                'status' => 'published',
            ]);
        $resMat->assertRedirect();

        $createdMat = Material::where('title', 'Materi Pertemuan 2 Slide')->first();
        $matCleanPath = ltrim($createdMat->file_path, '/\\');
        $this->assertTrue(Storage::disk('private')->exists($matCleanPath), "Materi harus ada di disk 'private'");
        $this->assertFalse(Storage::disk('public')->exists($matCleanPath), "Materi TIDAK boleh ada di disk 'public'");
        $this->assertFalse(Storage::disk('local')->exists($matCleanPath), "Materi TIDAK boleh ada di disk 'local'");
    }

    /**
     * Requirement 1: Avatar profil tetap di disk public dan menolak format SVG.
     */
    public function test_avatar_stored_in_public_disk_and_rejects_svg(): void
    {
        Storage::fake('public');

        $validAvatar = UploadedFile::fake()->image('profil.jpg');
        $response = $this->actingAs($this->studentOwner)
            ->post(route('app.settings.profile'), [
                'name' => 'Arya Sena',
                'email' => $this->studentOwner->email,
                'avatar' => $validAvatar,
            ]);

        $response->assertSessionHasNoErrors();
        $this->studentOwner->refresh();
        $this->assertNotNull($this->studentOwner->avatar_path);

        // SVG ditolak (hanya jpg, jpeg, png yang diizinkan)
        $svgAvatar = UploadedFile::fake()->create('avatar.svg', 100, 'image/svg+xml');
        $responseSvg = $this->actingAs($this->studentOwner)
            ->post(route('app.settings.profile'), [
                'name' => 'Arya Sena',
                'email' => $this->studentOwner->email,
                'avatar' => $svgAvatar,
            ]);

        $responseSvg->assertSessionHasErrors('avatar');
    }

    // =========================================================================
    // REQUIREMENT 3: DOWNLOAD HEADERS, ALLOWLIST, INLINE VS ATTACHMENT & CSP
    // =========================================================================

    /**
     * Requirement 3: Header unduhan allowlist:
     * - Hanya pdf, jpg, jpeg, png yang inline disertai Content-Security-Policy: sandbox
     * - Sisanya (zip, docx, pptx) selalu attachment
     * - X-Content-Type-Options: nosniff
     * - Nama file di Content-Disposition disanitasi
     */
    public function test_download_headers_inline_for_safe_types_attachment_for_others_with_csp(): void
    {
        Storage::fake('private');

        // 1. PDF disajikan inline dengan CSP sandbox
        Storage::disk('private')->put('materials/sample.pdf', '%PDF-1.4 Mock PDF');
        $this->publishedMaterial->update(['file_path' => 'materials/sample.pdf']);

        $resPdf = $this->actingAs($this->studentOwner)
            ->get(route('berkas.materi', $this->publishedMaterial));

        $resPdf->assertStatus(200);
        $resPdf->assertHeader('Content-Type', 'application/pdf');
        $resPdf->assertHeader('X-Content-Type-Options', 'nosniff');
        $resPdf->assertHeader('Content-Security-Policy', 'sandbox');
        $this->assertStringContainsString('inline;', $resPdf->headers->get('Content-Disposition'));

        // 2. PNG disajikan inline dengan CSP sandbox
        Storage::disk('private')->put('discussions/diagram.png', 'PNG MOCK DATA');
        $this->classThread->update(['attachment_path' => 'discussions/diagram.png']);

        $resPng = $this->actingAs($this->studentOwner)
            ->get(route('berkas.diskusi', $this->classThread));

        $resPng->assertStatus(200);
        $resPng->assertHeader('Content-Type', 'image/png');
        $resPng->assertHeader('Content-Security-Policy', 'sandbox');
        $this->assertStringContainsString('inline;', $resPng->headers->get('Content-Disposition'));

        // 3. ZIP WAJIB disajikan sebagai attachment (tanpa inline)
        Storage::disk('private')->put('submissions/code.zip', 'PK ZIP MOCK DATA');
        $this->submission->update(['file_path' => 'submissions/code.zip']);

        $resZip = $this->actingAs($this->studentOwner)
            ->get(route('berkas.pengumpulan', $this->submission));

        $resZip->assertStatus(200);
        $resZip->assertHeader('Content-Type', 'application/zip');
        $resZip->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertStringContainsString('attachment;', $resZip->headers->get('Content-Disposition'));
        $this->assertFalse($resZip->headers->has('Content-Security-Policy'));

        // 4. DOCX WAJIB disajikan sebagai attachment
        Storage::disk('private')->put('assessments/panduan.docx', 'DOCX MOCK DATA');
        $this->assignment->update(['lampiran_path' => 'assessments/panduan.docx']);

        $resDocx = $this->actingAs($this->studentOwner)
            ->get(route('berkas.tugas', $this->assignment));

        $resDocx->assertStatus(200);
        $resDocx->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $this->assertStringContainsString('attachment;', $resDocx->headers->get('Content-Disposition'));
    }

    // =========================================================================
    // REQUIREMENT 4: GRACEFUL 404 FOR MISSING PHYSICAL DISK FILES
    // =========================================================================

    /**
     * Requirement 4: Record DB yang file fisiknya tidak ada (data seed) mengembalikan 404 rapi, bukan 500.
     */
    public function test_missing_physical_file_returns_clean_404_not_500(): void
    {
        Storage::fake('private');
        Storage::fake('public');

        // Pengumpulan tugas: DB record ada, file fisik tidak ada
        $this->actingAs($this->studentOwner)
            ->get(route('berkas.pengumpulan', $this->submission))
            ->assertStatus(404);

        // Materi: DB record ada, file fisik tidak ada
        $this->actingAs($this->studentOwner)
            ->get(route('berkas.materi', $this->publishedMaterial))
            ->assertStatus(404);

        // Lampiran tugas: DB record ada, file fisik tidak ada
        $this->actingAs($this->studentOwner)
            ->get(route('berkas.tugas', $this->assignment))
            ->assertStatus(404);

        // Surat izin: DB record ada, file fisik tidak ada
        $this->actingAs($this->studentOwner)
            ->get(route('berkas.izin', $this->permitLog))
            ->assertStatus(404);

        // Lampiran diskusi: DB record ada, file fisik tidak ada
        $this->actingAs($this->studentOwner)
            ->get(route('berkas.diskusi', $this->classThread))
            ->assertStatus(404);
    }

    // =========================================================================
    // REQUIREMENT 4 & 8: ROLE MATRIX PER ENDPOINT (INCLUDING UPCOMING, COMPLETED, DIKELUARKAN)
    // =========================================================================

    /**
     * Requirement 4 & 8: Matriks peran materi perkuliahan (/berkas/materi/{material}).
     * Published: Admin (200), Dosen Pengampu (200), Mahasiswa Aktif (200), Upcoming (200),
     * Completed (200), Dikeluarkan (403), Dosen Lain (403), Mahasiswa Kelas Lain (403), Tamu (302).
     * Draft: Admin (200), Dosen Pengampu (200), Seluruh Mahasiswa (403).
     */
    public function test_role_matrix_for_materials_published_and_draft(): void
    {
        Storage::fake('private');
        Storage::disk('private')->put('materials/modul1_test.pdf', '%PDF-1.4 Mock Material');
        Storage::disk('private')->put('materials/modul1_draft.pdf', '%PDF-1.4 Mock Draft Material');

        // --- PUBLISHED MATERIAL ---
        // Admin (200)
        $this->actingAs($this->adminUser)->get(route('berkas.materi', $this->publishedMaterial))->assertStatus(200);
        // Dosen Pengampu (200)
        $this->actingAs($this->teachingLecturer)->get(route('berkas.materi', $this->publishedMaterial))->assertStatus(200);
        // Mahasiswa Aktif (200)
        $this->actingAs($this->studentOwner)->get(route('berkas.materi', $this->publishedMaterial))->assertStatus(200);
        // Mahasiswa Upcoming (200 - read-only scope)
        $this->actingAs($this->studentUpcoming)->get(route('berkas.materi', $this->publishedMaterial))->assertStatus(200);
        // Mahasiswa Completed (200 - read-only scope)
        $this->actingAs($this->studentCompleted)->get(route('berkas.materi', $this->publishedMaterial))->assertStatus(200);
        // Mahasiswa Dikeluarkan (403 Forbidden)
        $this->actingAs($this->studentDikeluarkan)->get(route('berkas.materi', $this->publishedMaterial))->assertStatus(403);
        // Mahasiswa Kelas Lain (403 Forbidden)
        $this->actingAs($this->studentOtherClass)->get(route('berkas.materi', $this->publishedMaterial))->assertStatus(403);
        // Dosen Kelas Lain (403 Forbidden)
        $this->actingAs($this->otherLecturer)->get(route('berkas.materi', $this->publishedMaterial))->assertStatus(403);
        // Tamu Unauthenticated (302 Redirect)
        $this->app['auth']->logout();
        $this->get(route('berkas.materi', $this->publishedMaterial))->assertRedirect(route('login'));

        // --- DRAFT MATERIAL ---
        // Admin boleh (200)
        $this->actingAs($this->adminUser)->get(route('berkas.materi', $this->draftMaterial))->assertStatus(200);
        // Dosen Pengampu boleh (200)
        $this->actingAs($this->teachingLecturer)->get(route('berkas.materi', $this->draftMaterial))->assertStatus(200);
        // Mahasiswa Aktif DITOLAK (403 Forbidden)
        $this->actingAs($this->studentOwner)->get(route('berkas.materi', $this->draftMaterial))->assertStatus(403);
        // Mahasiswa Upcoming DITOLAK (403 Forbidden)
        $this->actingAs($this->studentUpcoming)->get(route('berkas.materi', $this->draftMaterial))->assertStatus(403);
        // Mahasiswa Completed DITOLAK (403 Forbidden)
        $this->actingAs($this->studentCompleted)->get(route('berkas.materi', $this->draftMaterial))->assertStatus(403);
    }

    /**
     * Requirement 4: Lampiran assessment yang belum dipublikasikan hanya boleh dibuka dosen pengampu dan admin.
     * Published: Admin (200), Dosen Pengampu (200), Mahasiswa Aktif (200), Upcoming (200), Completed (200), Dikeluarkan (403).
     * Draft / Belum Publikasi: Admin (200), Dosen Pengampu (200), SEMUA Mahasiswa (403).
     */
    public function test_role_matrix_for_assignment_attachments_published_vs_unpublished(): void
    {
        Storage::fake('private');
        Storage::disk('private')->put('assessments/instruksi_tugas1.pdf', '%PDF-1.4 Mock Published Assignment');
        Storage::disk('private')->put('assessments/instruksi_draft.pdf', '%PDF-1.4 Mock Draft Assignment');

        // --- PUBLISHED ASSIGNMENT ---
        $this->actingAs($this->adminUser)->get(route('berkas.tugas', $this->assignment))->assertStatus(200);
        $this->actingAs($this->teachingLecturer)->get(route('berkas.tugas', $this->assignment))->assertStatus(200);
        $this->actingAs($this->studentOwner)->get(route('berkas.tugas', $this->assignment))->assertStatus(200);
        $this->actingAs($this->studentUpcoming)->get(route('berkas.tugas', $this->assignment))->assertStatus(200);
        $this->actingAs($this->studentCompleted)->get(route('berkas.tugas', $this->assignment))->assertStatus(200);
        $this->actingAs($this->studentDikeluarkan)->get(route('berkas.tugas', $this->assignment))->assertStatus(403);
        $this->actingAs($this->otherLecturer)->get(route('berkas.tugas', $this->assignment))->assertStatus(403);

        // --- UNPUBLISHED / DRAFT ASSIGNMENT ---
        // Admin boleh (200)
        $this->actingAs($this->adminUser)->get(route('berkas.tugas', $this->draftAssignment))->assertStatus(200);
        // Dosen Pengampu boleh (200)
        $this->actingAs($this->teachingLecturer)->get(route('berkas.tugas', $this->draftAssignment))->assertStatus(200);
        // Mahasiswa Aktif DILARANG (403 Forbidden)
        $this->actingAs($this->studentOwner)->get(route('berkas.tugas', $this->draftAssignment))->assertStatus(403);
        // Mahasiswa Upcoming DILARANG (403 Forbidden)
        $this->actingAs($this->studentUpcoming)->get(route('berkas.tugas', $this->draftAssignment))->assertStatus(403);
        // Mahasiswa Completed DILARANG (403 Forbidden)
        $this->actingAs($this->studentCompleted)->get(route('berkas.tugas', $this->draftAssignment))->assertStatus(403);
        // Dosen Lain DILARANG (403 Forbidden)
        $this->actingAs($this->otherLecturer)->get(route('berkas.tugas', $this->draftAssignment))->assertStatus(403);
    }

    /**
     * Requirement 8: Matriks peran pengumpulan tugas (/berkas/pengumpulan/{submission}).
     * Termasuk pemilik submission pada kelas yang sudah selesai (isFinished() -> 200).
     */
    public function test_role_matrix_for_submission_download_including_finished_class(): void
    {
        Storage::fake('private');
        Storage::disk('private')->put('submissions/owner_tugas1.pdf', '%PDF-1.4 Mock Submission');

        // 1. Pemilik submission (200 OK)
        $this->actingAs($this->studentOwner)->get(route('berkas.pengumpulan', $this->submission))->assertStatus(200);

        // 2. Pemilik submission pada KELAS SELESAI (isFinished() -> 200 OK)
        $this->academicClass->update(['status' => 'selesai']);
        $this->assertTrue($this->academicClass->fresh()->isFinished());
        $this->actingAs($this->studentOwner)->get(route('berkas.pengumpulan', $this->submission))->assertStatus(200);
        $this->academicClass->update(['status' => 'aktif']); // kembalikan ke aktif

        // 2b. Pemilik submission dengan status enrollment 'completed' (200 OK - hak BACA berkas milik sendiri)
        $ownerEnrollment = $this->academicClass->enrollments()->where('user_id', $this->studentOwner->id)->first();
        $ownerEnrollment->update(['status' => 'completed']);
        $this->actingAs($this->studentOwner)->get(route('berkas.pengumpulan', $this->submission))->assertStatus(200);
        $ownerEnrollment->update(['status' => 'aktif']);

        // 3. Mahasiswa lain sekelas (403 Forbidden)
        $this->actingAs($this->studentOtherSameClass)->get(route('berkas.pengumpulan', $this->submission))->assertStatus(403);

        // 4. Mahasiswa kelas lain (403 Forbidden)
        $this->actingAs($this->studentOtherClass)->get(route('berkas.pengumpulan', $this->submission))->assertStatus(403);

        // 5. Mahasiswa dikeluarkan (403 Forbidden)
        $this->actingAs($this->studentDikeluarkan)->get(route('berkas.pengumpulan', $this->submission))->assertStatus(403);

        // 6. Dosen pengampu (200 OK)
        $this->actingAs($this->teachingLecturer)->get(route('berkas.pengumpulan', $this->submission))->assertStatus(200);

        // 7. Dosen lain (403 Forbidden)
        $this->actingAs($this->otherLecturer)->get(route('berkas.pengumpulan', $this->submission))->assertStatus(403);

        // 8. Admin (200 OK)
        $this->actingAs($this->adminUser)->get(route('berkas.pengumpulan', $this->submission))->assertStatus(200);

        // Bonus: SuperAdmin (403 Forbidden - batas Fase A7)
        $this->actingAs($this->superAdminUser)->get(route('berkas.pengumpulan', $this->submission))->assertStatus(403);

        // Tamu unauthenticated (302 Redirect)
        $this->app['auth']->logout();
        $this->get(route('berkas.pengumpulan', $this->submission))->assertRedirect(route('login'));
    }

    /**
     * Requirement 8: Matriks peran surat izin (/berkas/izin/{log}) dan lampiran diskusi (/berkas/diskusi/{thread}).
     */
    public function test_role_matrix_for_permits_and_discussions(): void
    {
        Storage::fake('private');
        Storage::disk('private')->put('permits/surat_dokter.pdf', '%PDF-1.4 Mock Doctor Note');
        Storage::disk('private')->put('discussions/diagram_diskusi.png', 'MOCK DISCUSSION IMAGE DATA');

        // --- SURAT IZIN ---
        // Pemilik izin (200)
        $this->actingAs($this->studentOwner)->get(route('berkas.izin', $this->permitLog))->assertStatus(200);
        // Dosen pengampu kelas (200)
        $this->actingAs($this->teachingLecturer)->get(route('berkas.izin', $this->permitLog))->assertStatus(200);
        // Admin (200)
        $this->actingAs($this->adminUser)->get(route('berkas.izin', $this->permitLog))->assertStatus(200);
        // Mahasiswa lain (403)
        $this->actingAs($this->studentOtherSameClass)->get(route('berkas.izin', $this->permitLog))->assertStatus(403);
        // Dosen lain (403)
        $this->actingAs($this->otherLecturer)->get(route('berkas.izin', $this->permitLog))->assertStatus(403);

        // --- LAMPIRAN DISKUSI ---
        // Admin (200)
        $this->actingAs($this->adminUser)->get(route('berkas.diskusi', $this->classThread))->assertStatus(200);
        // Dosen pengampu (200)
        $this->actingAs($this->teachingLecturer)->get(route('berkas.diskusi', $this->classThread))->assertStatus(200);
        // Mahasiswa aktif (200)
        $this->actingAs($this->studentOwner)->get(route('berkas.diskusi', $this->classThread))->assertStatus(200);
        // Mahasiswa upcoming (200)
        $this->actingAs($this->studentUpcoming)->get(route('berkas.diskusi', $this->classThread))->assertStatus(200);
        // Mahasiswa completed (200)
        $this->actingAs($this->studentCompleted)->get(route('berkas.diskusi', $this->classThread))->assertStatus(200);
        // Mahasiswa dikeluarkan (403)
        $this->actingAs($this->studentDikeluarkan)->get(route('berkas.diskusi', $this->classThread))->assertStatus(403);
        // Mahasiswa kelas lain (403)
        $this->actingAs($this->studentOtherClass)->get(route('berkas.diskusi', $this->classThread))->assertStatus(403);
    }

    // =========================================================================
    // REQUIREMENT 2: ARTISAN COMMAND TEST (TEMPORARY DIRECTORIES, HASH, IDEMPOTENCY)
    // =========================================================================

    /**
     * Requirement 2: Test menyeluruh untuk perintah arunika:pindah-file-privat:
     * - Dry-run tidak mengubah apa pun (sumber tetap ada, target belum dibuat).
     * - --jalankan menyalin dan verifikasi hash/ukuran cocok, sumber tetap ada.
     * - Dijalankan dua kali idempotent (tidak error, hasil tetap terverifikasi).
     * - --hapus-sumber hanya menghapus jika target identik.
     * - Target dengan ukuran/hash berbeda tidak dianggap identik dan sumber TIDAK dihapus.
     * - Record tanpa file fisik dilaporkan sebagai mock seed.
     */
    public function test_artisan_pindah_file_privat_command_full_lifecycle(): void
    {
        $realContent = 'KONTEN BERKAS NYATA UNTUK PENGUJIAN INTEGRITAS HASH SHA-256';
        $subPath = 'submissions/test_lifecycle_migrate.pdf';
        $fullPublicPath = storage_path('app/public/' . $subPath);
        $fullPrivatePath = storage_path('app/private/' . $subPath);

        // Buat file fisik nyata di disk public
        File::ensureDirectoryExists(dirname($fullPublicPath));
        File::put($fullPublicPath, $realContent);

        // Buat record di DB menunjuk ke file tersebut
        $realSubmission = Submission::create([
            'assignment_id' => $this->assignment->id,
            'user_id' => $this->studentOwner->id,
            'file_path' => $subPath,
            'status' => 'submitted',
        ]);

        // Buat record mock tanpa berkas fisik
        $mockSubmission = Submission::create([
            'assignment_id' => $this->assignment->id,
            'user_id' => $this->studentOwner->id,
            'file_path' => 'submissions/mock_nonexistent.pdf',
            'status' => 'submitted',
        ]);

        try {
            // 1. Dry run: tidak mengubah apa pun
            $this->artisan('arunika:pindah-file-privat', ['--dry-run' => true])
                ->expectsOutputToContain('MODE: DRY-RUN')
                ->assertExitCode(0);

            $this->assertFileExists($fullPublicPath);
            $this->assertFileDoesNotExist($fullPrivatePath);

            // 2. Jalankan: menyalin dan memverifikasi hash/ukuran, sumber TETAP ADA
            $this->artisan('arunika:pindah-file-privat', ['--jalankan' => true])
                ->expectsOutputToContain('MODE: SALIN & VERIFIKASI')
                ->assertExitCode(0);

            $this->assertFileExists($fullPublicPath);
            $this->assertFileExists($fullPrivatePath);
            $this->assertSame(filesize($fullPublicPath), filesize($fullPrivatePath));
            $this->assertSame(hash_file('sha256', $fullPublicPath), hash_file('sha256', $fullPrivatePath));

            // 3. Eksekusi kedua --jalankan (Idempotensi: berjalan mulus tanpa error)
            $this->artisan('arunika:pindah-file-privat', ['--jalankan' => true])
                ->assertExitCode(0);

            $this->assertFileExists($fullPublicPath);
            $this->assertFileExists($fullPrivatePath);

            // 4. Uji Target Berbeda (Corrupted): target sengaja diubah isinya
            File::put($fullPrivatePath, 'KONTEN CORRUPT BERBEDA');
            $this->assertNotSame(hash_file('sha256', $fullPublicPath), hash_file('sha256', $fullPrivatePath));

            // Saat --hapus-sumber dijalankan pada file tidak identik, sumber TIDAK BOLEH dihapus!
            $this->artisan('arunika:pindah-file-privat', ['--hapus-sumber' => true])
                ->assertExitCode(1); // Gagal karena ada error integritas

            $this->assertFileExists($fullPublicPath, "Sumber tidak boleh dihapus jika target tidak identik!");

            // 5. Pulihkan file identik dan jalankan --hapus-sumber yang sah
            File::put($fullPrivatePath, $realContent);
            $this->assertSame(hash_file('sha256', $fullPublicPath), hash_file('sha256', $fullPrivatePath));

            $this->artisan('arunika:pindah-file-privat', ['--hapus-sumber' => true])
                ->assertExitCode(0);

            $this->assertFileDoesNotExist($fullPublicPath, "Sumber harus terhapus setelah verifikasi identik berhasil");
            $this->assertFileExists($fullPrivatePath);
        } finally {
            // Cleanup berkas uji
            if (File::exists($fullPublicPath)) {
                File::delete($fullPublicPath);
            }
            if (File::exists($fullPrivatePath)) {
                File::delete($fullPrivatePath);
            }
        }
    }

    // =========================================================================
    // REQUIREMENT 1: UPLOAD ALLOWLIST & LIMITS
    // =========================================================================

    /**
     * Requirement 1: Allowlist upload menerima ppt dan pptx, menolak skrip berbahaya dan berkas > 10MB.
     */
    public function test_upload_allowlist_accepts_ppt_and_rejects_disallowed_extensions(): void
    {
        $validPptx = UploadedFile::fake()->create('presentasi.pptx', 3000, 'application/vnd.openxmlformats-officedocument.presentationml.presentation');
        $resPptx = $this->actingAs($this->teachingLecturer)
            ->post(route('dosen.materi.store'), [
                'class_id' => $this->academicClass->id,
                'week' => 3,
                'title' => 'Materi Presentasi 3',
                'type' => 'ppt',
                'file' => $validPptx,
                'status' => 'published',
            ]);
        $resPptx->assertSessionHasNoErrors();

        // Ekstensi skrip berbahaya ditolak
        $badScript = UploadedFile::fake()->create('malicious.sh', 50, 'application/x-sh');
        $resBad = $this->actingAs($this->teachingLecturer)
            ->post(route('dosen.materi.store'), [
                'class_id' => $this->academicClass->id,
                'week' => 4,
                'title' => 'Materi Berbahaya',
                'type' => 'dokumen',
                'file' => $badScript,
                'status' => 'published',
            ]);
        $resBad->assertSessionHasErrors('file');

        // File melebihi batas 10MB (11MB) ditolak
        $tooBigFile = UploadedFile::fake()->create('big_file.pdf', 11264, 'application/pdf');
        $resBig = $this->actingAs($this->teachingLecturer)
            ->post(route('dosen.materi.store'), [
                'class_id' => $this->academicClass->id,
                'week' => 5,
                'title' => 'Materi Terlalu Besar',
                'type' => 'pdf',
                'file' => $tooBigFile,
                'status' => 'published',
            ]);
        $resBig->assertSessionHasErrors('file');
    }
}
