<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicClass;
use App\Models\Assignment;
use App\Models\Course;
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
use Tests\TestCase;

class RouteSecurityAuditTest extends TestCase
{
    use RefreshDatabase;

    protected User $studentA;
    protected User $studentB;
    protected User $lecturerA;
    protected User $lecturerB;
    protected AcademicClass $classA;
    protected AcademicClass $classB;
    protected Meeting $meetingA;
    protected Material $materialA;
    protected Assignment $assignmentA;
    protected Submission $submissionA;
    protected Enrollment $enrollmentA;
    protected DiscussionThread $threadA;

    protected function setUp(): void
    {
        parent::setUp();

        $campus = \App\Models\Campus::create([
            'name' => 'Universitas Nusantara',
            'code' => 'UNUS',
            'slug' => 'unus',
        ]);
        $faculty = Faculty::create(['campus_id' => $campus->id, 'name' => 'Fakultas Teknologi Informasi', 'code' => 'FTI']);
        $program = Program::create(['faculty_id' => $faculty->id, 'name' => 'Teknik Informatika', 'code' => 'IF']);
        $term = Term::create([
            'campus_id' => $campus->id,
            'name' => 'Semester Ganjil 2026/2027',
            'academic_year' => '2026/2027',
            'semester_type' => 'odd',
            'start_date' => now()->startOfYear(),
            'end_date' => now()->endOfYear(),
            'is_active' => true,
        ]);

        $this->studentA = User::factory()->create([
            'role' => UserRole::Student,
            'email' => 'student.a@unus.ac.id',
            'identifier' => '2301001',
        ]);

        $this->studentB = User::factory()->create([
            'role' => UserRole::Student,
            'email' => 'student.b@unus.ac.id',
            'identifier' => '2301002',
        ]);

        $this->lecturerA = User::factory()->create([
            'role' => UserRole::Lecturer,
            'email' => 'lecturer.a@unus.ac.id',
            'identifier' => '0401001',
        ]);

        $this->lecturerB = User::factory()->create([
            'role' => UserRole::Lecturer,
            'email' => 'lecturer.b@unus.ac.id',
            'identifier' => '0401002',
        ]);

        $curriculum = \App\Models\Curriculum::create([
            'program_id' => $program->id,
            'name' => 'Kurikulum 2024',
            'year' => 2024,
            'is_active' => true,
        ]);

        $courseA = Course::create([
            'curriculum_id' => $curriculum->id,
            'code' => 'IF101',
            'name' => 'Algoritma A',
            'credits' => 3,
            'semester' => 1,
        ]);

        $courseB = Course::create([
            'curriculum_id' => $curriculum->id,
            'code' => 'IF102',
            'name' => 'Basis Data B',
            'credits' => 3,
            'semester' => 1,
        ]);

        $this->classA = AcademicClass::create([
            'course_id' => $courseA->id,
            'lecturer_id' => $this->lecturerA->id,
            'term_id' => $term->id,
            'section' => 'Kelas A',
            'capacity' => 30,
            'status' => 'aktif',
        ]);

        $this->classB = AcademicClass::create([
            'course_id' => $courseB->id,
            'lecturer_id' => $this->lecturerB->id,
            'term_id' => $term->id,
            'section' => 'Kelas B',
            'capacity' => 30,
            'status' => 'aktif',
        ]);

        $this->enrollmentA = Enrollment::create([
            'user_id' => $this->studentA->id,
            'class_id' => $this->classA->id,
            'status' => 'aktif',
        ]);

        $this->meetingA = Meeting::create([
            'class_id' => $this->classA->id,
            'week' => 1,
            'title' => 'Pengenalan Algoritma',
            'start_at' => now(),
            'end_at' => now()->addHours(2),
        ]);

        $this->materialA = Material::create([
            'meeting_id' => $this->meetingA->id,
            'title' => 'Modul 1 Algoritma',
            'type' => 'pdf',
            'status' => 'published',
        ]);

        $this->assignmentA = Assignment::create([
            'class_id' => $this->classA->id,
            'title' => 'Tugas 1 Algoritma',
            'description' => 'Kerjakan modul 1',
            'due_at' => now()->addDays(7),
            'status' => 'dipublikasikan',
            'max_score' => 100,
        ]);

        $this->submissionA = Submission::create([
            'assignment_id' => $this->assignmentA->id,
            'user_id' => $this->studentA->id,
            'status' => 'submitted',
            'content' => 'Jawaban tugas 1',
            'submitted_at' => now(),
        ]);

        $category = DiscussionCategory::create([
            'name' => 'Akademik',
            'slug' => 'akademik',
        ]);

        $this->threadA = DiscussionThread::create([
            'user_id' => $this->lecturerA->id,
            'class_id' => $this->classA->id,
            'category_id' => $category->id,
            'title' => 'Diskusi Modul 1',
            'content' => 'Pertanyaan untuk modul 1',
        ]);
    }

    public function test_unauthenticated_guest_accessing_app_routes_is_redirected_to_login(): void
    {
        $response = $this->get('/app');
        $response->assertRedirect('/login');

        $responseCourses = $this->get('/app/courses');
        $responseCourses->assertRedirect('/login');

        $responseGrades = $this->get('/app/grades');
        $responseGrades->assertRedirect('/login');
    }

    public function test_student_cannot_view_material_when_enrollment_is_dikeluarkan(): void
    {
        $this->enrollmentA->update([
            'status' => 'dikeluarkan',
            'dikeluarkan_at' => now(),
            'dikeluarkan_oleh' => $this->lecturerA->id,
        ]);

        $response = $this->actingAs($this->studentA)->get(route('app.materials.view', $this->materialA));
        $response->assertStatus(403);
    }

    public function test_student_cannot_update_progress_of_material_in_unenrolled_class(): void
    {
        // studentB is not enrolled in classA
        $response = $this->actingAs($this->studentB)->postJson(route('app.materials.progress', $this->materialA), [
            'watch_percentage' => 100,
        ]);

        $response->assertStatus(403);
    }

    public function test_student_cannot_check_in_attendance_for_unenrolled_meeting(): void
    {
        // studentB is not enrolled in classA
        $response = $this->actingAs($this->studentB)->post(route('meetings.attendance', $this->meetingA));
        $response->assertStatus(403);
    }

    public function test_student_cannot_submit_absence_permit_for_unenrolled_meeting(): void
    {
        // studentB is not enrolled in classA
        $response = $this->actingAs($this->studentB)->post(route('attendance.permit'), [
            'meeting_id' => $this->meetingA->id,
            'type' => 'izin',
            'reason' => 'Ada urusan keluarga',
        ]);

        $response->assertSessionHas('error');
    }

    public function test_lecturer_cannot_grade_submission_of_another_lecturer_class(): void
    {
        // lecturerB does not teach classA
        $response = $this->actingAs($this->lecturerB)->post(route('submissions.grade', $this->submissionA), [
            'score' => 95,
            'feedback' => 'Bagus sekali',
        ]);

        $response->assertStatus(403);
    }

    public function test_lecturer_cannot_favorite_discussion_thread_of_another_lecturer_class(): void
    {
        // lecturerB does not teach classA
        $response = $this->actingAs($this->lecturerB)->post(route('dosen.diskusi.favorite', $this->threadA));
        $response->assertStatus(403);
    }

    public function test_lecturer_cannot_view_student_grade_detail_of_another_lecturer_class(): void
    {
        // lecturerB does not teach classA
        $response = $this->actingAs($this->lecturerB)->get(route('app.grades.show', $this->enrollmentA));
        $response->assertStatus(403);
    }
}
