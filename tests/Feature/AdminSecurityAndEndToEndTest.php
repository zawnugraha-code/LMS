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
use App\Models\Meeting;
use App\Models\Program;
use App\Models\Term;
use App\Models\User;
use App\Services\Admin\ActivityLogger;
use App\Services\Admin\CsvImporterService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminSecurityAndEndToEndTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $inactiveAdmin;
    protected User $lecturer;
    protected User $student;
    protected Campus $campus;
    protected Program $program;
    protected Curriculum $curriculum;
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

        $this->inactiveAdmin = User::create([
            'name' => 'Admin Nonaktif',
            'email' => 'admin.nonaktif@arunika.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
            'campus_id' => $this->campus->id,
            'is_active' => false,
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
    }

    /**
     * Uji alur lengkap siklus administrasi akademik dari awal hingga penutupan semester.
     */
    public function test_complete_end_to_end_admin_academic_cycle(): void
    {
        // 1. Login via role switcher demo
        $loginRes = $this->get(route('demo.login', ['role' => 'admin']));
        $loginRes->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->admin);

        // 2. Akses Beranda Administrator & verifikasi render metrik
        $dashRes = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $dashRes->assertStatus(200);
        $dashRes->assertSee('Selamat Datang');
        $dashRes->assertSee('Rina Kusumawati');
        $dashRes->assertSee('Perlu Perhatian');

        // 3. Buat Semester Baru (Genap 2026/2027)
        $termStoreRes = $this->actingAs($this->admin)->post(route('admin.semester.store'), [
            'name' => 'Semester Genap 2026/2027',
            'academic_year' => '2026/2027',
            'semester_type' => 'even',
            'start_date' => '2027-02-15',
            'end_date' => '2027-07-15',
            'is_active' => '0',
        ]);
        $termStoreRes->assertRedirect(route('admin.semester.index'));

        $newTerm = Term::where('name', 'Semester Genap 2026/2027')->first();
        $this->assertNotNull($newTerm);

        // 4. Aktifkan Semester Baru (Semester lama otomatis nonaktif)
        $activateRes = $this->actingAs($this->admin)->post(route('admin.semester.activate', $newTerm));
        $activateRes->assertRedirect();
        
        $newTerm->refresh();
        $this->term->refresh();
        $this->assertTrue($newTerm->is_active);
        $this->assertEquals('aktif', $newTerm->status);
        $this->assertFalse($this->term->is_active);
        $this->assertEquals('belum_aktif', $this->term->status);

        // 5. Buat Mata Kuliah Baru (IF2105 - Pemrograman Web Lanjut, 3 SKS)
        $courseRes = $this->actingAs($this->admin)->post(route('admin.mata-kuliah.store'), [
            'curriculum_id' => $this->curriculum->id,
            'code' => 'if2105',
            'name' => 'Pemrograman Web Lanjut',
            'credits' => 3,
            'semester' => 3,
            'description' => 'Membangun aplikasi web modern dengan framework Laravel.',
        ]);
        $courseRes->assertRedirect(route('admin.mata-kuliah.index'));

        $course = Course::where('code', 'IF2105')->first();
        $this->assertNotNull($course);
        $this->assertEquals('Pemrograman Web Lanjut', $course->name);

        // 6. Buka Kelas Baru (IF2105 Seksi A, kapasitas 40, dosen Dr. Budi Rahardjo)
        $classRes = $this->actingAs($this->admin)->post(route('admin.kelas.store'), [
            'course_id' => $course->id,
            'term_id' => $newTerm->id,
            'lecturer_id' => $this->lecturer->id,
            'section' => 'IF-3A',
            'room' => 'Lab Multimedia 2',
            'schedule_day' => 'Senin',
            'start_time' => '08:00',
            'end_time' => '10:30',
            'capacity' => 40,
            'meetings_count' => 16,
        ]);

        $class = AcademicClass::where('section', 'IF-3A')->where('course_id', $course->id)->first();
        $this->assertNotNull($class);
        $classRes->assertRedirect(route('admin.kelas.show', $class));

        // Verifikasi otomatisasi: 16 pertemuan & 4 komponen penilaian OBE (bobot = 100)
        $this->assertEquals(16, $class->meetings()->count());
        $this->assertEquals(4, $class->assessmentComponents()->count());
        $this->assertEquals(100, $class->assessmentComponents()->sum('bobot'));

        // 7. Daftarkan 32 Mahasiswa ke kelas (mencapai tepat 80% kuota, sisa 8)
        $studentIds = [];
        for ($i = 1; $i <= 32; $i++) {
            $nim = '24060122140' . str_pad((string)$i, 3, '0', STR_PAD_LEFT);
            $st = User::create([
                'name' => "Mahasiswa Test {$i}",
                'identifier' => $nim,
                'email' => "mhs{$i}@arunika.test",
                'password' => Hash::make('password'),
                'role' => UserRole::Student,
                'campus_id' => $this->campus->id,
                'is_active' => true,
            ]);
            $studentIds[] = $st->id;
        }

        $bulkRes = $this->actingAs($this->admin)->post(
            route('admin.pendaftaran-mahasiswa.bulk-store', $class),
            ['user_ids' => $studentIds]
        );
        $bulkRes->assertRedirect();
        $this->assertEquals(32, $class->activeEnrollments()->count());
        $this->assertEquals(8, $class->remainingCapacity());
        $this->assertFalse($class->isFull());

        // 8. Uji Live Schedule Checker mendeteksi bentrok ruangan
        $conflictRes = $this->actingAs($this->admin)->postJson(route('admin.kelas.check-conflict'), [
            'term_id' => $newTerm->id,
            'schedule_day' => 'Senin',
            'start_time' => '09:00',
            'end_time' => '11:30',
            'room' => 'Lab Multimedia 2',
        ]);
        $conflictRes->assertStatus(200);
        $this->assertTrue($conflictRes->json('has_conflict'));
        $this->assertNotNull($conflictRes->json('room_conflict'));

        // 9. Ganti Dosen Pengampu ke Dosen Baru dan verifikasi transfer tugas
        $lecturerNew = User::create([
            'name' => 'Dr. Hendra Wijaya',
            'email' => 'hendra@arunika.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Lecturer,
            'campus_id' => $this->campus->id,
            'is_active' => true,
        ]);

        $assignment = Assignment::create([
            'class_id' => $class->id,
            'dosen_id' => $this->lecturer->id,
            'title' => 'Tugas 1: CRUD Laravel',
            'due_at' => Carbon::now()->addDays(7),
            'max_score' => 100,
            'status' => 'published',
            'jenis' => 'tugas',
        ]);

        $updateLecturerRes = $this->actingAs($this->admin)->put(route('admin.kelas.update', $class), [
            'course_id' => $course->id,
            'term_id' => $newTerm->id,
            'lecturer_id' => $lecturerNew->id,
            'section' => 'IF-3A',
            'room' => 'Lab Multimedia 2',
            'schedule_day' => 'Senin',
            'start_time' => '08:00',
            'end_time' => '10:30',
            'capacity' => 40,
        ]);
        $updateLecturerRes->assertRedirect();

        $class->refresh();
        $assignment->refresh();
        $this->assertEquals($lecturerNew->id, $class->lecturer_id);
        $this->assertEquals($lecturerNew->id, $assignment->dosen_id);

        // 10. Sesuaikan jadwal pertemuan ke-3 karena hari libur nasional
        $meeting3 = $class->meetings()->where('week', 3)->first();
        $meetingRes = $this->actingAs($this->admin)->put(
            route('admin.kelas.meeting.update', ['class' => $class, 'meeting' => $meeting3]),
            [
                'title' => 'Pertemuan 3: Pengganti Hari Libur',
                'date' => '2027-03-05',
                'mode' => 'online',
                'start_time' => '08:00',
                'end_time' => '10:30',
            ]
        );
        $meetingRes->assertRedirect();
        $meeting3->refresh();
        $this->assertEquals('Pertemuan 3: Pengganti Hari Libur', $meeting3->title);
        $this->assertEquals('online', $meeting3->mode);

        // 11. Keluarkan 1 mahasiswa (soft remove) dan verifikasi data presensi/nilai tetap aman
        $firstStudent = User::find($studentIds[0]);
        $enrollment = Enrollment::where('class_id', $class->id)->where('user_id', $firstStudent->id)->first();
        $enrollment->update(['final_score' => 85.0, 'letter_grade' => 'A']);

        $removeRes = $this->actingAs($this->admin)->delete(
            route('admin.pendaftaran-mahasiswa.destroy', ['class' => $class, 'user' => $firstStudent])
        );
        $removeRes->assertRedirect();

        $enrollment->refresh();
        $this->assertEquals('dikeluarkan', $enrollment->status);
        $this->assertNotNull($enrollment->dikeluarkan_at);
        $this->assertEquals(85.0, $enrollment->final_score);
        $this->assertEquals(31, $class->activeEnrollments()->count());

        // 12. Tutup Semester & Kunci Seluruh Kelas menjadi Read-Only
        $closeRes = $this->actingAs($this->admin)->post(route('admin.semester.close', $newTerm));
        $closeRes->assertRedirect();

        $newTerm->refresh();
        $class->refresh();
        $this->assertEquals('selesai', $newTerm->status);
        $this->assertNotNull($newTerm->closed_at);
        $this->assertEquals('selesai', $class->status);
        $this->assertTrue($class->isFinished());
    }

    /**
     * Audit Keamanan: Verifikasi pemblokiran peran non-admin dan pengalihan tamu.
     */
    public function test_security_audit_non_admin_roles_strictly_forbidden(): void
    {
        $adminRoutes = [
            route('admin.dashboard'),
            route('admin.semester.index'),
            route('admin.mata-kuliah.index'),
            route('admin.kelas.index'),
            route('admin.pendaftaran-mahasiswa.index'),
            route('admin.cari'),
            route('admin.pengguna.dosen.index'),
            route('admin.pengguna.mahasiswa.index'),
            route('admin.log-aktivitas.index'),
            route('admin.pengaturan.edit'),
        ];

        foreach ($adminRoutes as $url) {
            // Tamu harus dialihkan ke halaman login
            auth()->logout();
            $guestRes = $this->get($url);
            $guestRes->assertRedirect(route('login'));

            // Mahasiswa harus ditolak HTTP 403 Forbidden
            $studentRes = $this->actingAs($this->student)->get($url);
            $studentRes->assertStatus(403);

            // Dosen harus ditolak HTTP 403 Forbidden
            $lecturerRes = $this->actingAs($this->lecturer)->get($url);
            $lecturerRes->assertStatus(403);
        }
    }

    /**
     * Audit Keamanan: Admin nonaktif otomatis dilogout dan sesi dibatalkan.
     */
    public function test_security_audit_inactive_admin_instantly_logged_out(): void
    {
        $response = $this->actingAs($this->inactiveAdmin)->get(route('admin.dashboard'));
        $response->assertRedirect(route('marketing.home'));
        $this->assertGuest();
    }

    /**
     * Audit Keamanan: Log aktivitas otomatis menyamarkan kata sandi dan token rahasia.
     */
    public function test_security_audit_activity_log_sanitizes_passwords_and_tokens(): void
    {
        ActivityLogger::log(
            aksi: 'audit_keamanan_uji',
            kategori: 'keamanan',
            subjek: null,
            deskripsi: 'Pengujian penyamaran kata sandi dan token',
            properti: [
                'nama' => 'Rina Kusumawati',
                'password' => 'RahasiaNegara123!',
                'token' => 'jwt_secret_token_abc',
                'secret' => 'oauth_secret_key',
            ]
        );

        $log = ActivityLog::where('aksi', 'audit_keamanan_uji')->latest()->first();
        $this->assertNotNull($log);
        $this->assertEquals('[REDACTED]', $log->properti['password']);
        $this->assertEquals('[REDACTED]', $log->properti['token']);
        $this->assertEquals('[REDACTED]', $log->properti['secret']);
        $this->assertEquals('Rina Kusumawati', $log->properti['nama']);
    }

    /**
     * Audit Keamanan: Proteksi terhadap CSV Formula Injection.
     */
    public function test_security_audit_csv_formula_injection_prevention(): void
    {
        $importer = new CsvImporterService();

        $content = "nim,nama,email\n" .
            "=cmd|' /C calc'!A0,Payload Formula,hacker@arunika.test\n" .
            "@SUM(A1:A10),At Formula,sum@arunika.test\n" .
            "-10+5,Minus Formula,minus@arunika.test\n" .
            "+2*3,Plus Formula,plus@arunika.test\n";

        $file = UploadedFile::fake()->createWithContent('malicious.csv', $content);
        $parsed = $importer->parseAndValidate($file);

        $this->assertCount(4, $parsed['valid']);
        // Setiap nilai diawali tanda petik tunggal aman (')
        $this->assertStringStartsWith("'", $parsed['valid'][0]['nim']);
        $this->assertStringStartsWith("'", $parsed['valid'][1]['nim']);
        $this->assertStringStartsWith("'", $parsed['valid'][2]['nim']);
        $this->assertStringStartsWith("'", $parsed['valid'][3]['nim']);
    }

    /**
     * Audit Keamanan: Ketahanan terhadap SQL Injection pada seluruh parameter query.
     */
    public function test_security_audit_sql_injection_resilience_in_filters(): void
    {
        $sqlPayload = "' OR 1=1 -- '; DROP TABLE users; --";

        $res1 = $this->actingAs($this->admin)->get(route('admin.kelas.index', ['q' => $sqlPayload]));
        $res1->assertStatus(200);

        $res2 = $this->actingAs($this->admin)->get(route('admin.mata-kuliah.index', ['q' => $sqlPayload]));
        $res2->assertStatus(200);

        $res3 = $this->actingAs($this->admin)->get(route('admin.semester.index', ['q' => $sqlPayload]));
        $res3->assertStatus(200);

        $res4 = $this->actingAs($this->admin)->get(route('admin.cari', ['q' => $sqlPayload]));
        $res4->assertStatus(200);

        // Verifikasi tabel users tidak terhapus dan sistem tetap utuh
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    /**
     * Audit Keamanan: XSS Escaping pada seluruh keluaran Blade templating.
     */
    public function test_security_audit_xss_escaping_in_blade(): void
    {
        $xssPayload = "<script>alert('XSS-TEST')</script>";

        $response = $this->actingAs($this->admin)->get(route('admin.cari', ['q' => $xssPayload]));
        $response->assertStatus(200);
        $response->assertDontSee($xssPayload, false); // Tidak ada tag script yang belum diescape
        $response->assertSee(e($xssPayload), false);  // Harus dalam bentuk HTML entity terescape
    }
}
