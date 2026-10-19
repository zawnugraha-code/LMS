<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicClass;
use App\Models\Campus;
use App\Models\Course;
use App\Models\Curriculum;
use App\Models\DiscussionCategory;
use App\Models\DiscussionReply;
use App\Models\DiscussionThread;
use App\Models\Enrollment;
use App\Models\Faculty;
use App\Models\Program;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscussionSecurityAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $studentEnrolled;
    protected User $studentUnrelated;
    protected User $studentRemoved;
    protected User $lecturer;
    protected AcademicClass $academicClass;
    protected DiscussionCategory $category;
    protected DiscussionThread $classThread;
    protected DiscussionReply $classReply;

    protected function setUp(): void
    {
        parent::setUp();

        $campus = Campus::create(['name' => 'Kampus Utama', 'code' => 'KU']);
        $faculty = Faculty::create(['campus_id' => $campus->id, 'name' => 'Fakultas TI', 'code' => 'FTI']);
        $program = Program::create(['faculty_id' => $faculty->id, 'name' => 'Teknik Informatika', 'code' => 'IF']);
        $curriculum = Curriculum::create([
            'program_id' => $program->id,
            'name' => 'Kurikulum 2024',
            'year' => 2024,
            'is_active' => true,
        ]);

        $term = Term::create([
            'campus_id' => $campus->id,
            'name' => 'Ganjil 2026/2027',
            'academic_year' => '2026/2027',
            'semester_type' => 'odd',
            'start_date' => now()->subMonth(),
            'end_date' => now()->addMonths(5),
            'is_active' => true,
            'status' => 'aktif',
        ]);

        $this->lecturer = User::create([
            'name' => 'Dosen Pengampu',
            'email' => 'dosen.pengampu@arunika.test',
            'role' => UserRole::Lecturer,
            'password' => bcrypt('password'),
        ]);

        $course = Course::create([
            'curriculum_id' => $curriculum->id,
            'code' => 'IF301',
            'name' => 'Keamanan Sistem',
            'credits' => 3,
            'semester' => 5,
        ]);

        $this->academicClass = AcademicClass::create([
            'course_id' => $course->id,
            'term_id' => $term->id,
            'lecturer_id' => $this->lecturer->id,
            'section' => 'A',
            'status' => 'aktif',
        ]);

        $this->studentEnrolled = User::create([
            'name' => 'Mahasiswa Terdaftar',
            'email' => 'mhs.terdaftar@arunika.test',
            'role' => UserRole::Student,
            'password' => bcrypt('password'),
        ]);

        $this->studentUnrelated = User::create([
            'name' => 'Mahasiswa Tidak Terdaftar',
            'email' => 'mhs.luar@arunika.test',
            'role' => UserRole::Student,
            'password' => bcrypt('password'),
        ]);

        $this->studentRemoved = User::create([
            'name' => 'Mahasiswa Dikeluarkan',
            'email' => 'mhs.keluar@arunika.test',
            'role' => UserRole::Student,
            'password' => bcrypt('password'),
        ]);

        Enrollment::create([
            'class_id' => $this->academicClass->id,
            'user_id' => $this->studentEnrolled->id,
            'status' => Enrollment::STATUS_AKTIF,
        ]);

        Enrollment::create([
            'class_id' => $this->academicClass->id,
            'user_id' => $this->studentRemoved->id,
            'status' => Enrollment::STATUS_DIKELUARKAN,
            'dikeluarkan_at' => now(),
        ]);

        $this->category = DiscussionCategory::create([
            'name' => 'Forum Kelas',
            'slug' => 'forum-kelas',
            'color' => '#1A2E5E',
        ]);

        $this->classThread = DiscussionThread::create([
            'user_id' => $this->studentEnrolled->id,
            'class_id' => $this->academicClass->id,
            'category_id' => $this->category->id,
            'title' => 'Diskusi Materi 1',
            'content' => 'Pertanyaan mengenai implementasi enkripsi.',
        ]);

        $this->classReply = DiscussionReply::create([
            'thread_id' => $this->classThread->id,
            'user_id' => $this->studentEnrolled->id,
            'content' => 'Jawaban awal.',
        ]);
    }

    public function test_unrelated_student_cannot_view_class_discussion_thread(): void
    {
        $response = $this->actingAs($this->studentUnrelated)->get("/app/discussions/{$this->classThread->id}");
        $response->assertStatus(403);
    }

    public function test_removed_student_cannot_view_class_discussion_thread(): void
    {
        $response = $this->actingAs($this->studentRemoved)->get("/app/discussions/{$this->classThread->id}");
        $response->assertStatus(403);
    }

    public function test_unrelated_student_cannot_create_thread_in_unauthorized_class(): void
    {
        $response = $this->actingAs($this->studentUnrelated)->post('/app/discussions', [
            'title' => 'Thread Tidak Sah',
            'category_id' => $this->category->id,
            'class_id' => $this->academicClass->id,
            'content' => 'Mencoba membuat thread di kelas lain.',
        ]);
        $response->assertStatus(403);
    }

    public function test_removed_student_cannot_create_thread_in_class(): void
    {
        $response = $this->actingAs($this->studentRemoved)->post('/app/discussions', [
            'title' => 'Thread Dikeluarkan',
            'category_id' => $this->category->id,
            'class_id' => $this->academicClass->id,
            'content' => 'Mencoba membuat thread setelah dikeluarkan.',
        ]);
        $response->assertStatus(403);
    }

    public function test_unrelated_student_cannot_reply_to_class_discussion_thread(): void
    {
        $response = $this->actingAs($this->studentUnrelated)->post("/app/discussions/{$this->classThread->id}/replies", [
            'content' => 'Balasan tidak sah dari mahasiswa luar kelas.',
        ]);
        $response->assertStatus(403);
    }

    public function test_removed_student_cannot_reply_to_class_discussion_thread(): void
    {
        $response = $this->actingAs($this->studentRemoved)->post("/app/discussions/{$this->classThread->id}/replies", [
            'content' => 'Balasan tidak sah setelah dikeluarkan.',
        ]);
        $response->assertStatus(403);
    }

    public function test_unrelated_student_cannot_upvote_class_thread(): void
    {
        $response = $this->actingAs($this->studentUnrelated)->post("/app/discussions/{$this->classThread->id}/upvote");
        $response->assertStatus(403);
    }

    public function test_removed_student_cannot_upvote_class_thread(): void
    {
        $response = $this->actingAs($this->studentRemoved)->post("/app/discussions/{$this->classThread->id}/upvote");
        $response->assertStatus(403);
    }

    public function test_unrelated_student_cannot_upvote_class_reply(): void
    {
        $response = $this->actingAs($this->studentUnrelated)->post("/app/discussions/replies/{$this->classReply->id}/upvote");
        $response->assertStatus(403);
    }

    public function test_removed_student_cannot_upvote_class_reply(): void
    {
        $response = $this->actingAs($this->studentRemoved)->post("/app/discussions/replies/{$this->classReply->id}/upvote");
        $response->assertStatus(403);
    }

    public function test_unrelated_student_cannot_mark_solution_on_class_reply(): void
    {
        $response = $this->actingAs($this->studentUnrelated)->post("/app/discussions/replies/{$this->classReply->id}/solution");
        $response->assertStatus(403);
    }

    public function test_removed_student_cannot_mark_solution_on_class_reply(): void
    {
        $response = $this->actingAs($this->studentRemoved)->post("/app/discussions/replies/{$this->classReply->id}/solution");
        $response->assertStatus(403);
    }

    public function test_enrolled_active_student_can_participate_in_class_discussion(): void
    {
        // 1. Can view
        $viewRes = $this->actingAs($this->studentEnrolled)->get("/app/discussions/{$this->classThread->id}");
        $viewRes->assertStatus(200);

        // 2. Can reply
        $replyRes = $this->actingAs($this->studentEnrolled)->post("/app/discussions/{$this->classThread->id}/replies", [
            'content' => 'Balasan sah dari mahasiswa terdaftar aktif.',
        ]);
        $replyRes->assertStatus(302);

        // 3. Can upvote thread
        $upvoteThreadRes = $this->actingAs($this->studentEnrolled)->post("/app/discussions/{$this->classThread->id}/upvote");
        $upvoteThreadRes->assertStatus(302);

        // 4. Can upvote reply
        $upvoteReplyRes = $this->actingAs($this->studentEnrolled)->post("/app/discussions/replies/{$this->classReply->id}/upvote");
        $upvoteReplyRes->assertStatus(302);
    }
}
