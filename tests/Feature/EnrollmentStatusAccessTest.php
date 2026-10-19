<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicClass;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Curriculum;
use App\Models\DiscussionCategory;
use App\Models\DiscussionReply;
use App\Models\DiscussionThread;
use App\Models\Enrollment;
use App\Models\Faculty;
use App\Models\Material;
use App\Models\Meeting;
use App\Models\Program;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentStatusAccessTest extends TestCase
{
    use RefreshDatabase;

    protected User $lecturer;
    protected AcademicClass $activeClass;
    protected Material $publishedMaterial;
    protected Assignment $assignment;
    protected DiscussionThread $classThread;
    protected DiscussionReply $classReply;
    protected DiscussionCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $campus = \App\Models\Campus::create([
            'name' => 'Universitas Nusantara Indonesia',
            'code' => 'UNI',
            'domain' => 'unus.ac.id',
            'is_active' => true,
        ]);

        $faculty = Faculty::create(['name' => 'Fakultas Teknik', 'code' => 'FT', 'campus_id' => $campus->id]);
        $program = Program::create(['faculty_id' => $faculty->id, 'name' => 'Informatika', 'code' => 'IF']);
        $curriculum = Curriculum::create(['program_id' => $program->id, 'name' => 'Kurikulum 2026', 'year' => 2026]);
        $term = Term::create([
            'campus_id' => $campus->id,
            'name' => 'Semester Ganjil 2026/2027',
            'academic_year' => '2026/2027',
            'semester_type' => 'odd',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonths(5),
            'is_active' => true,
            'status' => 'aktif',
        ]);

        $course = Course::create([
            'curriculum_id' => $curriculum->id,
            'code' => 'IF301',
            'name' => 'Rekayasa Perangkat Lunak',
            'credits' => 3,
            'semester' => 3,
        ]);

        $this->lecturer = User::factory()->create([
            'role' => UserRole::Lecturer,
            'email' => 'lecturer.test@arunika.ac.id',
        ]);

        $this->activeClass = AcademicClass::create([
            'course_id' => $course->id,
            'term_id' => $term->id,
            'lecturer_id' => $this->lecturer->id,
            'section' => 'IF-3A',
            'capacity' => 40,
            'status' => 'aktif',
        ]);

        $meeting = Meeting::create([
            'class_id' => $this->activeClass->id,
            'week' => 1,
            'title' => 'Pengenalan RPL',
            'mode' => 'offline',
            'is_published' => true,
        ]);

        $this->publishedMaterial = Material::create([
            'class_id' => $this->activeClass->id,
            'meeting_id' => $meeting->id,
            'title' => 'Slide Pertemuan 1',
            'type' => 'document',
            'file_path' => 'materials/dummy.pdf',
            'status' => 'published',
            'uploaded_by' => $this->lecturer->id,
        ]);

        $this->assignment = Assignment::create([
            'class_id' => $this->activeClass->id,
            'title' => 'Tugas 1 - Pemodelan UML',
            'type' => 'tugas',
            'status' => 'published',
            'due_at' => now()->addDays(7),
            'max_score' => 100,
        ]);

        $this->category = DiscussionCategory::create([
            'name' => 'Diskusi Umum',
            'slug' => 'diskusi-umum',
        ]);

        $this->classThread = DiscussionThread::create([
            'user_id' => $this->lecturer->id,
            'category_id' => $this->category->id,
            'class_id' => $this->activeClass->id,
            'title' => 'Forum Pertemuan 1',
            'content' => 'Silakan bertanya materi pertemuan 1 di sini.',
        ]);

        $this->classReply = DiscussionReply::create([
            'thread_id' => $this->classThread->id,
            'user_id' => $this->lecturer->id,
            'content' => 'Contoh jawaban awal dari dosen.',
        ]);
    }

    protected function createStudentWithStatus(string $status): User
    {
        $student = User::factory()->create([
            'role' => UserRole::Student,
        ]);

        Enrollment::create([
            'class_id' => $this->activeClass->id,
            'user_id' => $student->id,
            'status' => $status,
        ]);

        return $student;
    }

    public function test_upcoming_student_can_read_class_and_published_material(): void
    {
        $student = $this->createStudentWithStatus('upcoming');

        $responseClass = $this->actingAs($student)->get("/app/courses/{$this->activeClass->id}");
        $responseClass->assertStatus(200);

        $responseMaterial = $this->actingAs($student)->get("/app/materials/{$this->publishedMaterial->id}/view");
        $responseMaterial->assertStatus(200);
    }

    public function test_upcoming_student_is_rejected_on_all_write_actions(): void
    {
        $student = $this->createStudentWithStatus('upcoming');

        // 1. Submit assignment rejected
        $this->actingAs($student)
            ->post("/app/assignments/{$this->assignment->id}/submit", ['content' => 'Submit attempt'])
            ->assertStatus(403);

        // 2. Submit permit rejected
        $this->actingAs($student)
            ->post('/app/attendance/permit', [
                'meeting_id' => $this->publishedMaterial->meeting_id,
                'type' => 'izin',
                'reason' => 'Izin keperluan keluarga',
            ])
            ->assertRedirect(); // Controller redirects with error flash if failed

        // 3. Create thread in class rejected
        $this->actingAs($student)
            ->post('/app/discussions', [
                'title' => 'Pertanyaan upcoming student',
                'category_id' => $this->category->id,
                'class_id' => $this->activeClass->id,
                'content' => 'Apakah boleh bertanya sekarang?',
            ])
            ->assertStatus(403);

        // 4. Reply to thread rejected
        $this->actingAs($student)
            ->post("/app/discussions/{$this->classThread->id}/replies", ['content' => 'Balasan upcoming student'])
            ->assertStatus(403);

        // 5. Upvote thread rejected
        $this->actingAs($student)
            ->post("/app/discussions/{$this->classThread->id}/upvote")
            ->assertStatus(403);

        // 6. Upvote reply rejected
        $this->actingAs($student)
            ->post("/app/discussions/replies/{$this->classReply->id}/upvote")
            ->assertStatus(403);

        // 7. Mark solution rejected
        $this->actingAs($student)
            ->post("/app/discussions/replies/{$this->classReply->id}/solution")
            ->assertStatus(403);

        // 8. Progress / Claim XP rejected
        $this->actingAs($student)
            ->postJson("/app/materials/{$this->publishedMaterial->id}/progress", ['watch_percentage' => 100])
            ->assertStatus(403);
    }

    public function test_completed_student_can_read_but_is_rejected_on_all_write_actions(): void
    {
        $student = $this->createStudentWithStatus('completed');

        // BACA: diizinkan
        $this->actingAs($student)->get("/app/courses/{$this->activeClass->id}")->assertStatus(200);
        $this->actingAs($student)->get("/app/materials/{$this->publishedMaterial->id}/view")->assertStatus(200);

        // TULIS: ditolak
        $this->actingAs($student)
            ->post("/app/assignments/{$this->assignment->id}/submit", ['content' => 'Submit attempt completed'])
            ->assertStatus(403);

        $this->actingAs($student)
            ->post('/app/discussions', [
                'title' => 'Pertanyaan alumni kelas',
                'category_id' => $this->category->id,
                'class_id' => $this->activeClass->id,
                'content' => 'Tanya materi tahun lalu',
            ])
            ->assertStatus(403);

        $this->actingAs($student)
            ->post("/app/discussions/{$this->classThread->id}/replies", ['content' => 'Balasan alumni kelas'])
            ->assertStatus(403);

        $this->actingAs($student)
            ->post("/app/discussions/{$this->classThread->id}/upvote")
            ->assertStatus(403);

        $this->actingAs($student)
            ->post("/app/discussions/replies/{$this->classReply->id}/upvote")
            ->assertStatus(403);

        $this->actingAs($student)
            ->post("/app/discussions/replies/{$this->classReply->id}/solution")
            ->assertStatus(403);

        $this->actingAs($student)
            ->postJson("/app/materials/{$this->publishedMaterial->id}/progress", ['watch_percentage' => 100])
            ->assertStatus(403);
    }

    public function test_dikeluarkan_student_is_rejected_on_all_read_and_write_actions(): void
    {
        $student = $this->createStudentWithStatus('dikeluarkan');

        // BACA: ditolak total
        $this->actingAs($student)->get("/app/courses/{$this->activeClass->id}")->assertStatus(403);
        $this->actingAs($student)->get("/app/materials/{$this->publishedMaterial->id}/view")->assertStatus(403);

        // TULIS: ditolak total
        $this->actingAs($student)
            ->post("/app/assignments/{$this->assignment->id}/submit", ['content' => 'Submit expelled'])
            ->assertStatus(403);

        $this->actingAs($student)
            ->post('/app/discussions', [
                'title' => 'Pertanyaan siswa expelled',
                'category_id' => $this->category->id,
                'class_id' => $this->activeClass->id,
                'content' => 'Tanya kelas',
            ])
            ->assertStatus(403);
    }

    public function test_aktif_student_can_read_and_write_normally(): void
    {
        $student = $this->createStudentWithStatus('aktif');

        // BACA: diizinkan
        $this->actingAs($student)->get("/app/courses/{$this->activeClass->id}")->assertStatus(200);
        $this->actingAs($student)->get("/app/materials/{$this->publishedMaterial->id}/view")->assertStatus(200);

        // TULIS: diizinkan
        $this->actingAs($student)
            ->post('/app/discussions', [
                'title' => 'Pertanyaan aktif student',
                'category_id' => $this->category->id,
                'class_id' => $this->activeClass->id,
                'content' => 'Tanya materi aktif',
            ])
            ->assertRedirect();

        $this->actingAs($student)
            ->postJson("/app/materials/{$this->publishedMaterial->id}/progress", ['watch_percentage' => 100])
            ->assertStatus(200);
    }
}
