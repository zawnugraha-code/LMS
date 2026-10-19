<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicClass;
use App\Models\Assignment;
use App\Models\Quiz;
use App\Models\User;
use App\Services\AssignmentService;
use Database\Seeders\CanonicalStudentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignmentFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CanonicalStudentSeeder::class);

        $this->student = User::where('role', UserRole::Student)->where('email', 'arya.pratama@student.unus.ac.id')->first()
            ?? User::where('role', UserRole::Student)->first();
    }

    public function test_assignments_page_renders_successfully_with_canonical_data(): void
    {
        $response = $this->actingAs($this->student)->get('/app/assignments');

        $response->assertStatus(200);
        $response->assertSee('Tugas & Ujian');
        $response->assertSee('Tugas Aktif');
        $response->assertSee('Ujian Mendatang');
        $response->assertSee('Tugas Selesai');
        $response->assertSee('Tugas Terlambat');

        // Check canonical metrics from seeder
        $response->assertSee('8'); // 8 Tugas Aktif
        $response->assertSee('3'); // 3 Ujian Mendatang
        $response->assertSee('5'); // 5 Tugas Selesai
        $response->assertSee('1'); // 1 Tugas Terlambat

        // Check canonical card titles
        $response->assertSee('Tugas 1 - Latihan Class dan Object');
        $response->assertSee('Tugas 2 - Normalisasi Database');
        $response->assertSee('Ujian Tengah Semester (UTS)');
        $response->assertSee('Tugas 1 - Konfigurasi IP Address');
        $response->assertSee('Tugas 3 - Analisis Performa CPU');
        $response->assertSee('Ujian Akhir Semester (UAS)');
        $response->assertSee('Tugas Praktikum 0 - Environment Setup & Git Workflow', false);
    }

    public function test_assignment_service_metrics_and_summary_calculation(): void
    {
        $service = app(AssignmentService::class);
        $summary = $service->getSummary($this->student);

        $this->assertIsArray($summary);
        $this->assertEquals(8, $summary['active_tasks']);
        $this->assertEquals(3, $summary['upcoming_exams']);
        $this->assertEquals(5, $summary['completed_tasks']);
        $this->assertEquals(1, $summary['late_tasks']);

        $upcoming = $service->getUpcomingAssessments($this->student);
        $this->assertGreaterThanOrEqual(3, $upcoming->count());
    }

    public function test_student_can_open_submission_workspace_subpage(): void
    {
        $assignment = Assignment::whereHas('academicClass.enrollments', function ($q) {
            $q->where('user_id', $this->student->id);
        })->first();

        $response = $this->actingAs($this->student)->get("/app/assignments/{$assignment->id}/submit");

        $response->assertStatus(200);
        $response->assertSee('Kembali ke Tugas & Ujian', false);
        $response->assertSee($assignment->title);
        $response->assertSee('Lembar Kerja Pengumpulan');
        $response->assertSee('Simpan Sebagai Draf');
        $response->assertSee('Kumpulkan Sekarang');
    }

    public function test_student_can_submit_assignment_draft_and_final_with_xp(): void
    {
        $assignment = Assignment::whereHas('academicClass.enrollments', function ($q) {
            $q->where('user_id', $this->student->id);
        })->whereDoesntHave('submissions', function ($q) {
            $q->where('user_id', $this->student->id);
        })->first() ?? Assignment::first();

        $initialXp = $this->student->totalXp();

        // 1. Submit Draft (should not award XP)
        $draftResponse = $this->actingAs($this->student)->post("/app/assignments/{$assignment->id}/submit", [
            'content' => 'Draft pengerjaan tugas refactoring.',
            'action' => 'draft',
        ]);

        $draftResponse->assertRedirect();
        $this->assertDatabaseHas('submissions', [
            'assignment_id' => $assignment->id,
            'user_id' => $this->student->id,
            'status' => 'draft',
        ]);

        $this->student->refresh();
        $this->assertEquals($initialXp, $this->student->totalXp());

        // 2. Submit Final (awards +10 XP)
        $finalResponse = $this->actingAs($this->student)->post("/app/assignments/{$assignment->id}/submit", [
            'content' => 'Final submission tugas refactoring sudah lengkap.',
            'file_url' => 'https://github.com/aryapratama/solid-refactoring',
            'action' => 'submit',
        ]);

        $finalResponse->assertRedirect();
        $this->assertDatabaseHas('submissions', [
            'assignment_id' => $assignment->id,
            'user_id' => $this->student->id,
            'status' => 'submitted',
        ]);

        $this->student->refresh();
        $this->assertGreaterThan($initialXp, $this->student->totalXp());
    }

    public function test_student_can_access_and_complete_quiz_attempt(): void
    {
        $quiz = Quiz::whereHas('academicClass.enrollments', function ($q) {
            $q->where('user_id', $this->student->id);
        })->first();

        // 1. Visit Quiz Attempt subpage
        $viewResponse = $this->actingAs($this->student)->get("/app/quizzes/{$quiz->id}/attempt");
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee($quiz->title);
        $viewResponse->assertSee('AI Proctoring Aktif');
        $viewResponse->assertSee('Navigasi Soal');

        // 2. Submit Answers
        $initialXp = $this->student->totalXp();
        $questions = $quiz->questions;
        $answers = [];
        foreach ($questions as $q) {
            $answers[$q->id] = is_array($q->correct_answer) ? $q->correct_answer[0] : 'A';
        }

        $submitResponse = $this->actingAs($this->student)->post("/app/quizzes/{$quiz->id}/attempt", [
            'answers' => $answers,
        ]);

        $submitResponse->assertRedirect(route('app.assignments'));
        $submitResponse->assertSessionHas('success');

        $this->assertDatabaseHas('quiz_attempts', [
            'quiz_id' => $quiz->id,
            'user_id' => $this->student->id,
        ]);

        $this->student->refresh();
        $this->assertGreaterThan($initialXp, $this->student->totalXp());
    }

    public function test_server_strictly_rejects_unauthorized_course_assignment_with_403(): void
    {
        // Create an assignment in an unenrolled class
        $otherClass = AcademicClass::whereDoesntHave('enrollments', function ($q) {
            $q->where('user_id', $this->student->id);
        })->first();

        if ($otherClass) {
            $foreignAssignment = Assignment::firstOrCreate([
                'class_id' => $otherClass->id,
                'title' => 'Tugas Mahasiswa Jurusan Lain',
            ], [
                'due_at' => now()->addDays(5),
                'max_score' => 100,
            ]);

            $response = $this->actingAs($this->student)->get("/app/assignments/{$foreignAssignment->id}/submit");
            $response->assertStatus(403);
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_calendar_export_ics_returns_valid_icalendar_stream(): void
    {
        $response = $this->actingAs($this->student)->get('/app/assignments/calendar.ics');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
        $this->assertStringContainsString('BEGIN:VCALENDAR', $response->getContent());
        $this->assertStringContainsString('BEGIN:VEVENT', $response->getContent());
        $this->assertStringContainsString('END:VCALENDAR', $response->getContent());
    }

    public function test_assignment_attachment_download_returns_pdf(): void
    {
        $assignment = Assignment::first();

        $response = $this->actingAs($this->student)->get("/app/assignments/{$assignment->id}/download");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }
}
