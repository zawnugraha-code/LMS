<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Assignment;
use App\Models\Meeting;
use App\Models\Submission;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArunikaAppTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_marketing_landing_page_renders_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Arunika');
        $response->assertSee('Learning Operating System');
        $response->assertSee('Native OBE');
        $response->assertSee('Jadwalkan Presentasi Kampus');
    }

    public function test_institution_can_submit_demo_request(): void
    {
        $response = $this->post('/demo/request', [
            'name' => 'Prof. Dr. Ir. Anton Wijaya',
            'email' => 'anton@kampus-negeri.ac.id',
            'institution' => 'Institut Teknologi Nusantara',
            'phone' => '081234567890',
            'role' => 'Rektor / Wakil Rektor',
            'students_count' => '10.000 - 25.000 Mahasiswa',
            'message' => 'Kami memerlukan implementasi kurikulum OBE segera untuk akreditasi internasional.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('demo_requests', [
            'email' => 'anton@kampus-negeri.ac.id',
            'institution' => 'Institut Teknologi Nusantara',
        ]);
    }

    public function test_instant_role_switcher_authenticates_correct_user(): void
    {
        $response = $this->get('/demo/login/lecturer');
        $response->assertRedirect('/dosen');

        $this->assertAuthenticated();
        $this->assertEquals(UserRole::Lecturer, auth()->user()->role);

        $responseStudent = $this->get('/demo/login/student');
        $responseStudent->assertRedirect('/app');
        $this->assertEquals(UserRole::Student, auth()->user()->role);
    }

    public function test_dashboard_renders_for_each_user_role(): void
    {
        $roles = ['student', 'lecturer', 'campus_admin', 'auditor', 'industry_mentor'];

        foreach ($roles as $role) {
            $user = User::where('role', $role)->first();
            $response = $this->actingAs($user)->get('/app');

            $response->assertStatus(200);
            $response->assertSee($user->name);
        }
    }

    public function test_student_can_submit_assignment_and_earn_xp(): void
    {
        $student = User::where('role', UserRole::Student)->first();
        $assignment = Assignment::first();

        $initialXp = $student->totalXp();

        $response = $this->actingAs($student)->post("/app/assignments/{$assignment->id}/submit", [
            'content' => 'Jawaban implementasi tugas arsitektur OOP.',
            'file_url' => 'https://github.com/aryapratama/oop-test',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('submissions', [
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'status' => 'submitted',
        ]);

        $student->refresh();
        $this->assertGreaterThan($initialXp, $student->totalXp());
    }

    public function test_lecturer_can_grade_student_submission(): void
    {
        $lecturer = User::where('role', UserRole::Lecturer)->first();
        $submission = Submission::first();

        $response = $this->actingAs($lecturer)->post("/app/submissions/{$submission->id}/grade", [
            'score' => 96.5,
            'feedback' => 'Kode sangat elegan dan bersih. Pertahankan!',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $submission->refresh();
        $this->assertEquals(96.5, (float) $submission->score);
        $this->assertEquals('graded', $submission->status);
    }

    public function test_student_can_check_in_attendance(): void
    {
        $student = User::where('role', UserRole::Student)->first();
        $meeting = Meeting::first();

        $response = $this->actingAs($student)->post("/app/meetings/{$meeting->id}/attendance");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('attendance_logs', [
            'user_id' => $student->id,
            'status' => 'present',
        ]);
    }

    public function test_ai_copilot_endpoint_returns_json_response(): void
    {
        $student = User::where('role', UserRole::Student)->first();

        $response = $this->actingAs($student)->postJson('/api/copilot/ask', [
            'prompt' => 'Apa itu Single Responsibility Principle dalam arsitektur software?',
            'socratic' => true,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'provider', 'response']);
        $this->assertTrue($response->json('success'));
    }
}
