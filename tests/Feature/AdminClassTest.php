<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicClass;
use App\Models\Assignment;
use App\Models\Campus;
use App\Models\Course;
use App\Models\CourseAssessmentComponent;
use App\Models\Curriculum;
use App\Models\Enrollment;
use App\Models\Faculty;
use App\Models\Meeting;
use App\Models\Program;
use App\Models\Term;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminClassTest extends TestCase
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

        $this->lecturerA = User::create([
            'name' => 'Dr. Budi Rahardjo',
            'email' => 'budi@arunika.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Lecturer,
            'campus_id' => $this->campus->id,
            'is_active' => true,
        ]);

        $this->lecturerB = User::create([
            'name' => 'Siti Aminah, M.Kom.',
            'email' => 'siti@arunika.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Lecturer,
            'campus_id' => $this->campus->id,
            'is_active' => true,
        ]);

        $this->student = User::create([
            'name' => 'Ahmad Fadillah',
            'email' => 'ahmad@arunika.test',
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
            'lecturer_id' => $this->lecturerA->id,
            'section' => 'IF-201-A',
            'room' => 'Lab Multimedia 1',
            'schedule_day' => 'Senin',
            'start_time' => '08:00:00',
            'end_time' => '10:30:00',
            'capacity' => 40,
            'status' => 'aktif',
        ]);
    }

    public function test_admin_can_view_classes_index(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.kelas.index'));

        $response->assertStatus(200);
        $response->assertSee('Kelas Perkuliahan');
        $response->assertSee('IF-201-A');
        $response->assertSee('Dr. Budi Rahardjo');
        $response->assertSee('Lab Multimedia 1');
        $response->assertSee('Kapasitas Kolektif Kelas');
    }

    public function test_admin_can_filter_classes_by_status_and_search(): void
    {
        $inactiveClass = AcademicClass::create([
            'course_id' => $this->course->id,
            'term_id' => $this->term->id,
            'lecturer_id' => $this->lecturerB->id,
            'section' => 'IF-201-B',
            'room' => 'R.302',
            'schedule_day' => 'Selasa',
            'start_time' => '13:00:00',
            'end_time' => '15:30:00',
            'capacity' => 35,
            'status' => 'selesai',
        ]);

        // Filter status selesai
        $resSelesai = $this->actingAs($this->admin)->get(route('admin.kelas.index', ['status' => 'selesai']));
        $resSelesai->assertStatus(200);
        $resSelesai->assertSee('IF-201-B');
        $resSelesai->assertDontSee('IF-201-A');

        // Search query
        $resSearch = $this->actingAs($this->admin)->get(route('admin.kelas.index', ['q' => 'Multimedia']));
        $resSearch->assertStatus(200);
        $resSearch->assertSee('IF-201-A');
        $resSearch->assertDontSee('IF-201-B');
    }

    public function test_admin_can_view_create_class_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.kelas.create'));

        $response->assertStatus(200);
        $response->assertSee('Buka Seksi / Kelas Perkuliahan');
        $response->assertSee('Live Schedule Checker');
        $response->assertSee('Algoritma dan Pemrograman');
    }

    public function test_admin_can_create_new_class_with_meetings_and_components(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.kelas.store'), [
            'course_id' => $this->course->id,
            'term_id' => $this->term->id,
            'lecturer_id' => $this->lecturerB->id,
            'section' => 'IF-201-C',
            'room' => 'Lab Jaringan 2',
            'schedule_day' => 'Rabu',
            'start_time' => '10:00',
            'end_time' => '12:30',
            'capacity' => 35,
            'meetings_count' => 16,
        ]);

        $createdClass = AcademicClass::where('section', 'IF-201-C')->first();
        $this->assertNotNull($createdClass);

        $response->assertRedirect(route('admin.kelas.show', $createdClass));
        $response->assertSessionHas('success');

        // 16 Pertemuan otomatis digenerasi
        $this->assertEquals(16, $createdClass->meetings()->count());
        $this->assertDatabaseHas('meetings', [
            'class_id' => $createdClass->id,
            'week' => 8,
            'title' => 'Ujian Tengah Semester (UTS)',
        ]);

        // Komponen penilaian OBE otomatis digenerasi (Tugas 30, Kuis 20, UTS 25, UAS 25 = 100)
        $this->assertEquals(4, $createdClass->assessmentComponents()->count());
        $this->assertEquals(100, $createdClass->assessmentComponents()->sum('bobot'));
    }

    public function test_conflict_checker_detects_room_conflict(): void
    {
        // $this->class menggunakan 'Lab Multimedia 1' pada 'Senin', '08:00:00' - '10:30:00'
        $response = $this->actingAs($this->admin)->postJson(route('admin.kelas.check-conflict'), [
            'term_id' => $this->term->id,
            'schedule_day' => 'Senin',
            'start_time' => '09:00',
            'end_time' => '11:00',
            'room' => 'Lab Multimedia 1',
            'lecturer_id' => $this->lecturerB->id, // Dosen berbeda, ruang sama
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertTrue($data['has_conflict']);
        $this->assertNotNull($data['room_conflict']);
    }

    public function test_conflict_checker_detects_lecturer_conflict(): void
    {
        // $this->class mengampu Dr. Budi Rahardjo pada 'Senin', '08:00:00' - '10:30:00'
        $response = $this->actingAs($this->admin)->postJson(route('admin.kelas.check-conflict'), [
            'term_id' => $this->term->id,
            'schedule_day' => 'Senin',
            'start_time' => '09:00',
            'end_time' => '11:00',
            'room' => 'Lab Jaringan 3', // Ruang berbeda, dosen sama
            'lecturer_id' => $this->lecturerA->id,
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertTrue($data['has_conflict']);
        $this->assertNotNull($data['lecturer_conflict']);
    }

    public function test_admin_can_view_class_detail_show_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.kelas.show', $this->class));

        $response->assertStatus(200);
        $response->assertSee('IF-201-A');
        $response->assertSee('Algoritma dan Pemrograman');
        $response->assertSee('Peserta Kelas');
        $response->assertSee('Jadwal & Pertemuan');
        $response->assertSee('Komponen Penilaian (OBE)');
        $response->assertSee('Ringkasan Aktivitas');
    }

    public function test_admin_can_reassign_lecturer_and_transfer_assignments(): void
    {
        // Buat tugas pada kelas ini dengan dosen pengampu awal ($this->lecturerA)
        $assignment = Assignment::create([
            'class_id' => $this->class->id,
            'dosen_id' => $this->lecturerA->id,
            'title' => 'Tugas 1: Pointer dan Memory',
            'description' => 'Kerjakan modul 1',
            'due_at' => Carbon::now()->addDays(7),
            'max_score' => 100,
            'status' => 'published',
            'jenis' => 'tugas',
        ]);

        // Ganti dosen pengampu ke $this->lecturerB
        $response = $this->actingAs($this->admin)->put(route('admin.kelas.update', $this->class), [
            'course_id' => $this->course->id,
            'term_id' => $this->term->id,
            'lecturer_id' => $this->lecturerB->id,
            'section' => 'IF-201-A',
            'room' => 'Lab Multimedia 1',
            'schedule_day' => 'Senin',
            'start_time' => '08:00',
            'end_time' => '10:30',
            'capacity' => 40,
        ]);

        $response->assertRedirect(route('admin.kelas.show', $this->class));

        $this->class->refresh();
        $this->assertEquals($this->lecturerB->id, $this->class->lecturer_id);

        $assignment->refresh();
        $this->assertEquals($this->lecturerB->id, $assignment->dosen_id);
    }

    public function test_admin_can_finish_class(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.kelas.finish', $this->class));

        $response->assertRedirect();
        $this->class->refresh();
        $this->assertEquals('selesai', $this->class->status);
        $this->assertTrue($this->class->isFinished());
    }

    public function test_admin_can_delete_empty_class(): void
    {
        $freshClass = AcademicClass::create([
            'course_id' => $this->course->id,
            'term_id' => $this->term->id,
            'section' => 'IF-999-Z',
            'capacity' => 30,
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.kelas.destroy', $freshClass));

        $response->assertRedirect(route('admin.kelas.index'));
        $this->assertDatabaseMissing('classes', ['id' => $freshClass->id]);
    }

    public function test_cannot_delete_class_with_enrolled_students(): void
    {
        Enrollment::create([
            'class_id' => $this->class->id,
            'user_id' => $this->student->id,
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.kelas.destroy', $this->class));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('classes', ['id' => $this->class->id]);
    }

    public function test_admin_can_update_meeting_schedule_and_topic(): void
    {
        $meeting = Meeting::create([
            'class_id' => $this->class->id,
            'week' => 1,
            'title' => 'Pertemuan 1 Awal',
            'date' => Carbon::create(2026, 9, 7),
            'start_time' => '08:00:00',
            'end_time' => '10:30:00',
            'mode' => 'offline',
            'is_published' => true,
        ]);

        $response = $this->actingAs($this->admin)->put(
            route('admin.kelas.meeting.update', ['class' => $this->class, 'meeting' => $meeting]),
            [
                'title' => 'Pertemuan 1: Kontrak Kuliah & Pengantar',
                'date' => '2026-09-08',
                'mode' => 'online',
                'start_time' => '08:30',
                'end_time' => '11:00',
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $meeting->refresh();
        $this->assertEquals('Pertemuan 1: Kontrak Kuliah & Pengantar', $meeting->title);
        $this->assertEquals('online', $meeting->mode);
    }

    public function test_non_admin_cannot_access_or_modify_classes(): void
    {
        $response = $this->actingAs($this->lecturerA)->get(route('admin.kelas.index'));
        $response->assertStatus(403);

        $responsePost = $this->actingAs($this->student)->post(route('admin.kelas.store'), [
            'course_id' => $this->course->id,
            'term_id' => $this->term->id,
            'section' => 'HACK-1',
            'capacity' => 30,
        ]);
        $responsePost->assertStatus(403);
    }
}
