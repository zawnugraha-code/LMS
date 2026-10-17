<?php

namespace Tests\Feature;

use App\Models\AcademicClass;
use App\Models\Assignment;
use App\Models\Submission;
use App\Models\User;
use App\Notifications\SubmissionGradedNotification;
use App\Notifications\SubmissionReceivedNotification;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\LecturerBatch1Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LecturerGradingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->seed(LecturerBatch1Seeder::class);
    }

    public function test_lecturer_can_view_penilaian_index_with_canonical_stats(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();

        $response = $this->actingAs($dosen)->get(route('dosen.penilaian.index'));

        $response->assertStatus(200);
        $response->assertSee('Penilaian', false);
        $response->assertSee('Total Penilaian');
        $response->assertSee('Menunggu Penilaian');
        $response->assertSee('Sudah Dinilai');
        $response->assertSee('Terlambat');
        $response->assertSee('Ringkasan Penilaian');
        $response->assertSee('Progress Penilaian');

        // Check canonical summary values: 12, 7, 5, 0 (Screenshot 5)
        $response->assertViewHas('summary', function ($summary) {
            return $summary['total_penilaian'] === 12
                && $summary['menunggu_penilaian'] === 7
                && $summary['sudah_dinilai'] === 5
                && $summary['terlambat'] === 0;
        });

        // Check progress 42% (Screenshot 5)
        $response->assertViewHas('progress', function ($progress) {
            return $progress['percentage'] === 42
                && $progress['sudah_dinilai'] === 5
                && $progress['menunggu'] === 7;
        });
    }

    public function test_lecturer_can_filter_penilaian_by_jenis(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();

        $resKuis = $this->actingAs($dosen)->get(route('dosen.penilaian.index', ['jenis' => 'kuis']));
        $resKuis->assertStatus(200);
        $resKuis->assertViewHas('assessments', function ($items) {
            return $items->contains('title', 'Kuis 1 - Rancang Bangun Mini Core Banking System (OOP)');
        });
    }

    public function test_lecturer_can_view_assessment_grading_workspace(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();
        $assignment = Assignment::where('dosen_id', $dosen->id)->firstOrFail();

        $response = $this->actingAs($dosen)->get(route('dosen.penilaian.show', $assignment->id));

        $response->assertStatus(200);
        $response->assertSee($assignment->title);
        $response->assertSee('Total Mahasiswa');
        $response->assertSee('Mengumpulkan');
        $response->assertSee('Sudah Dinilai');
        $response->assertSee('Isi Nilai 0 Tanpa Submission');

        $response->assertViewHas('rows');
        $response->assertViewHas('total_students');
        $response->assertViewHas('submitted_count');
        $response->assertViewHas('graded_count');
    }

    public function test_lecturer_can_grade_submission_and_student_receives_notification(): void
    {
        Notification::fake();

        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();

        // Find a pending submission
        $submission = Submission::whereHas('assignment', fn ($q) => $q->where('dosen_id', $dosen->id))
            ->whereNull('score')
            ->firstOrFail();

        $student = $submission->user;

        $response = $this->actingAs($dosen)->post(route('dosen.penilaian.grade', $submission->id), [
            'score' => 92.5,
            'feedback' => 'Analisis arsitektur kelas dan polimorfisme sangat rapi dan lengkap.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $submission->refresh();
        $this->assertEquals(92.5, (float) $submission->score);
        $this->assertEquals('Analisis arsitektur kelas dan polimorfisme sangat rapi dan lengkap.', $submission->feedback);
        $this->assertEquals('graded', $submission->status);

        Notification::assertSentTo($student, SubmissionGradedNotification::class);
    }

    public function test_lecturer_can_grade_student_without_prior_submission(): void
    {
        Notification::fake();

        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();
        $assignment = Assignment::where('dosen_id', $dosen->id)->firstOrFail();

        // Enrolled student who hasn't submitted yet
        $enrolledStudents = $assignment->academicClass->students;
        $submittedStudentIds = $assignment->submissions->pluck('user_id')->all();
        $student = $enrolledStudents->first(fn ($s) => ! in_array($s->id, $submittedStudentIds));

        $this->assertNotNull($student, 'Must have at least one unsubmitted student for testing');

        $response = $this->actingAs($dosen)->post(
            route('dosen.penilaian.grade_student', [$assignment->id, $student->id]),
            [
                'score' => 75,
                'feedback' => 'Penilaian manual untuk pengumpulan luring.',
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $submission = Submission::where('assignment_id', $assignment->id)
            ->where('user_id', $student->id)
            ->firstOrFail();

        $this->assertEquals(75, (float) $submission->score);
        $this->assertEquals('graded', $submission->status);

        Notification::assertSentTo($student, SubmissionGradedNotification::class);
    }

    public function test_lecturer_can_quick_assign_zeros_to_unsubmitted_students(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();
        $assignment = Assignment::where('dosen_id', $dosen->id)->firstOrFail();

        $response = $this->actingAs($dosen)->post(route('dosen.penilaian.quick_zeros', $assignment->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // All enrolled students should now have a submission with score = 0
        $enrolledCount = $assignment->academicClass->students()->count();
        $submissionsCount = Submission::where('assignment_id', $assignment->id)->count();

        $this->assertEquals($enrolledCount, $submissionsCount);
    }

    public function test_lecturer_cannot_grade_another_lecturer_submission(): void
    {
        $dosenBudi = User::where('email', 'dosen@arunika.test')->firstOrFail();
        $dosenRudi = User::where('email', 'dosen2@arunika.test')->firstOrFail();

        // Assignment belonging to Rudi
        $classRudi = AcademicClass::where('lecturer_id', $dosenRudi->id)->firstOrFail();
        $assignmentRudi = Assignment::create([
            'class_id' => $classRudi->id,
            'dosen_id' => $dosenRudi->id,
            'title' => 'Tugas Rudi',
            'jenis' => 'tugas',
            'mode' => 'individu',
            'metode' => 'unggah_berkas',
            'max_score' => 100,
            'due_at' => Carbon::now()->addDays(2),
            'status' => 'dipublikasikan',
        ]);

        $submissionRudi = Submission::create([
            'assignment_id' => $assignmentRudi->id,
            'user_id' => $dosenRudi->id, // just dummy user
            'content' => 'Jawaban',
            'status' => 'submitted',
            'submitted_at' => Carbon::now(),
        ]);

        // Dosen Budi tries to grade Rudi's submission
        $response = $this->actingAs($dosenBudi)->post(route('dosen.penilaian.grade', $submissionRudi->id), [
            'score' => 100,
            'feedback' => 'Illegal grade',
        ]);

        $response->assertStatus(403);
    }

    public function test_student_submitting_assignment_calculates_status_waktu_and_notifies_lecturer(): void
    {
        Notification::fake();
        Storage::fake('public');

        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();
        $class = AcademicClass::where('lecturer_id', $dosen->id)->firstOrFail();
        $student = $class->students()->firstOrFail();

        // Create assignment with due_at in the future (on-time test)
        $assignmentOnTime = Assignment::create([
            'class_id' => $class->id,
            'dosen_id' => $dosen->id,
            'title' => 'Tugas Tepat Waktu Test',
            'jenis' => 'tugas',
            'mode' => 'individu',
            'metode' => 'unggah_berkas',
            'max_score' => 100,
            'due_at' => Carbon::now()->addDays(2),
            'status' => 'dipublikasikan',
        ]);

        $file = UploadedFile::fake()->create('laporan.pdf', 500, 'application/pdf');

        $response = $this->actingAs($student)->post(route('assignments.submit', $assignmentOnTime->id), [
            'content' => 'Tugas saya lampirkan tepat waktu.',
            'file' => $file,
        ]);

        $response->assertRedirect();

        $submission = Submission::where('assignment_id', $assignmentOnTime->id)
            ->where('user_id', $student->id)
            ->firstOrFail();

        $this->assertEquals('tepat_waktu', $submission->status_waktu);

        Notification::assertSentTo($dosen, SubmissionReceivedNotification::class);
    }
}
