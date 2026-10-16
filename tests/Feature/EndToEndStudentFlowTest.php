<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicClass;
use App\Models\Assignment;
use App\Models\AttendanceSession;
use App\Models\Enrollment;
use App\Models\Material;
use App\Models\Submission;
use App\Models\Term;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EndToEndStudentFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;

    protected User $lecturer;

    protected AcademicClass $activeClass;

    protected Term $activeTerm;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->student = User::where('email', 'arya.pratama@student.unus.ac.id')->first()
            ?? User::where('role', UserRole::Student)->first();

        $this->lecturer = User::where('email', 'rudi.hermawan@unus.ac.id')->first()
            ?? User::where('role', UserRole::Lecturer)->first();

        $this->activeTerm = Term::where('is_active', true)->first()
            ?? Term::latest()->first();

        $this->activeClass = AcademicClass::with(['course', 'lecturer'])
            ->where('term_id', $this->activeTerm->id)
            ->whereHas('enrollments', fn ($q) => $q->where('user_id', $this->student->id))
            ->first() ?? AcademicClass::first();

        $this->lecturer = $this->activeClass->lecturer
            ?? User::where('role', UserRole::Lecturer)->first();
    }

    /**
     * Test the complete academic lifecycle from class entry to graduation grading.
     * Selesaikan Materi -> Presensi -> Submit Tugas -> Dosen Menilai -> Nilai Terupdate -> AI Copilot -> Jadwal
     */
    public function test_complete_end_to_end_academic_student_flow(): void
    {
        // 1. MAHASISWA MEMBUKA BERANDA & MATA KULIAH
        $dashRes = $this->actingAs($this->student)->get(route('app.dashboard'));
        $dashRes->assertStatus(200);
        $dashRes->assertSee($this->student->name);
        $dashRes->assertSee('Ringkasan Akademik');

        $coursesRes = $this->actingAs($this->student)->get(route('app.courses'));
        $coursesRes->assertStatus(200);
        $coursesRes->assertSee($this->activeClass->course?->code);

        // 2. MASUK KE RUANG KELAS VIRTUAL
        $classroomRes = $this->actingAs($this->student)->get(route('app.courses.classroom', $this->activeClass));
        $classroomRes->assertStatus(200);
        $classroomRes->assertSee('Ruang Kelas Aktif');
        $classroomRes->assertSee($this->activeClass->course?->code);

        // 3. SELESAIKAN MATERI PEMBELAJARAN (EARN XP)
        $material = Material::whereHas('meeting.academicClass', fn ($q) => $q->where('id', $this->activeClass->id))->first();
        if ($material) {
            $initialXp = $this->student->fresh()->totalXp();

            $progressRes = $this->actingAs($this->student)->postJson(route('app.materials.progress', $material), [
                'watch_percentage' => 95,
            ]);

            $progressRes->assertStatus(200);
            $progressRes->assertJson(['success' => true]);

            // Verifikasi progres tersimpan
            $this->assertDatabaseHas('material_progress', [
                'user_id' => $this->student->id,
                'material_id' => $material->id,
                'status' => 'completed',
            ]);

            $this->assertGreaterThanOrEqual($initialXp, $this->student->fresh()->totalXp());
        }

        // 4. CHECK-IN PRESENSI DENGAN KODE
        $session = AttendanceSession::where('class_id', $this->activeClass->id)
            ->where('is_active', true)
            ->first();

        if ($session) {
            $codeRes = $this->actingAs($this->student)->postJson(route('attendance.check_in.code'), [
                'session_id' => $session->id,
                'code' => $session->code,
            ]);

            $codeRes->assertStatus(200);
            $this->assertTrue($codeRes->json('success'));

            $this->assertDatabaseHas('attendance_logs', [
                'session_id' => $session->id,
                'user_id' => $this->student->id,
                'status' => 'present',
            ]);
        }

        // 5. KERJAKAN & KUMPULKAN TUGAS AKADEMIK
        $assignment = Assignment::where('class_id', $this->activeClass->id)->first() ?? Assignment::create([
            'class_id' => $this->activeClass->id,
            'title' => 'Tugas 1 Pemrograman',
            'due_at' => now()->addDays(5),
            'max_score' => 100,
        ]);
        $this->assertNotNull($assignment);

        $submitRes = $this->actingAs($this->student)->post(route('assignments.submit', $assignment), [
            'submission_type' => 'text',
            'content' => 'Implementasi Clean Architecture & Repository Pattern pada modul pembayaran enterprise.',
            'status' => 'submitted',
        ]);

        $submitRes->assertStatus(302);
        $submission = Submission::where('assignment_id', $assignment->id)
            ->where('user_id', $this->student->id)
            ->first();

        $this->assertNotNull($submission);
        $this->assertEquals('submitted', $submission->status);

        // 6. DOSEN MEMERIKSA & MEMBERIKAN NILAI
        $gradeRes = $this->actingAs($this->lecturer)->post(route('submissions.grade', $submission), [
            'score' => 95.00,
            'feedback' => 'Arsitektur kelas sangat rapi, mematuhi prinsip Single Responsibility dan Interface Segregation.',
        ]);

        $gradeRes->assertStatus(302);
        $this->assertEquals(95.00, $submission->fresh()->score);
        $this->assertEquals('graded', $submission->fresh()->status);

        // 7. MAHASISWA MEMBUKA TRANSKRIP NILAI
        $gradesRes = $this->actingAs($this->student)->get(route('app.grades'));
        $gradesRes->assertStatus(200);
        $gradesRes->assertSee('IPK Semester Ini');

        $enrollment = Enrollment::where('class_id', $this->activeClass->id)
            ->where('user_id', $this->student->id)
            ->first();

        if ($enrollment) {
            $detailGradeRes = $this->actingAs($this->student)->get(route('app.grades.show', $enrollment));
            $detailGradeRes->assertStatus(200);
            $detailGradeRes->assertSee('Transkrip Nilai');
            $detailGradeRes->assertSee($this->activeClass->course?->code);
        }

        // 8. TANYA AI COPILOT DALAM MODE SOKRATES (DAPAT +10 XP)
        $copilotRes = $this->actingAs($this->student)->postJson(route('copilot.ask'), [
            'prompt' => 'Bagaimana menguji kelas Controller dengan Mocking Interface di PHPUnit?',
            'socratic' => true,
            'context' => $this->activeClass->course?->name,
            'mode' => 'chat',
        ]);

        $copilotRes->assertStatus(200);
        $this->assertTrue($copilotRes->json('success'));
        $this->assertTrue($copilotRes->json('socratic'));
        $this->assertEquals(10, $copilotRes->json('xp_awarded'));

        // Verifikasi audit log XP bertambah dari AI Copilot
        $this->assertDatabaseHas('xp_ledgers', [
            'user_id' => $this->student->id,
            'source_type' => 'ai_copilot',
            'amount' => 10,
        ]);

        // 9. MAHASISWA MEMBUKA JADWAL KULIAH & EKSPOR ICAL
        $scheduleRes = $this->actingAs($this->student)->get(route('app.schedule'));
        $scheduleRes->assertStatus(200);
        $scheduleRes->assertSee('Jadwal &amp; Kalender Akademik', false);
        $scheduleRes->assertSee('Matriks Mingguan');

        $icsRes = $this->actingAs($this->student)->get(route('app.schedule.export.ics'));
        $icsRes->assertStatus(200);
        $this->assertStringContainsString('BEGIN:VCALENDAR', $icsRes->getContent());
        $this->assertStringContainsString('RRULE:FREQ=WEEKLY', $icsRes->getContent());

        // 10. REKAPITULASI AKHIR: DATA BERANDA TERSINKRONISASI
        $finalDashRes = $this->actingAs($this->student)->get(route('app.dashboard'));
        $finalDashRes->assertStatus(200);
        $finalDashRes->assertSee(route('app.schedule'));
    }

    /**
     * Test role protection across endpoints: unauthorized roles cannot execute student actions.
     */
    public function test_role_protections_and_security_guardrails(): void
    {
        // Mahasiswa tidak boleh menilai tugas sendiri
        $assignment = Assignment::where('class_id', $this->activeClass->id)->first() ?? Assignment::create([
            'class_id' => $this->activeClass->id,
            'title' => 'Tugas 1 Pemrograman',
            'due_at' => now()->addDays(5),
            'max_score' => 100,
        ]);
        $submission = Submission::where('assignment_id', $assignment->id)->first() ?? Submission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $this->student->id,
            'submission_type' => 'text',
            'content' => 'Sample submission content',
            'status' => 'submitted',
        ]);

        $illegalGrade = $this->actingAs($this->student)->post(route('submissions.grade', $submission), [
            'score' => 100,
            'feedback' => 'Self grade',
        ]);

        // Strictly 403 Forbidden for non-lecturer grading
        $illegalGrade->assertStatus(403);

        // Mahasiswa tidak bisa mengakses transkrip mahasiswa lain
        $otherStudent = User::where('role', UserRole::Student)
            ->where('id', '!=', $this->student->id)
            ->first();

        if ($otherStudent) {
            $otherEnrollment = Enrollment::where('user_id', $otherStudent->id)->first();
            if ($otherEnrollment) {
                $forbiddenRes = $this->actingAs($this->student)->get(route('app.grades.show', $otherEnrollment));
                $forbiddenRes->assertStatus(403);
            }
        }
    }
}
