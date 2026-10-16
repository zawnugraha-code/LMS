<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\CanonicalStudentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiCopilotTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CanonicalStudentSeeder::class);

        $this->student = User::where('email', 'arya.pratama@student.unus.ac.id')->first()
            ?? User::where('role', 'student')->first();
    }

    public function test_copilot_workspace_page_renders_successfully(): void
    {
        $response = $this->actingAs($this->student)->get('/app/copilot');

        $response->assertStatus(200);
        $response->assertSee('Arunika AI Copilot');
        $response->assertSee('Tutor Konsep');
        $response->assertSee('Flashcard');
        $response->assertSee('Latihan Kuis');
        $response->assertSee('Rangkuman');
        $response->assertSee('Pedagogi Sokrates');
    }

    public function test_ask_endpoint_with_socratic_mode(): void
    {
        $response = $this->actingAs($this->student)->postJson('/api/copilot/ask', [
            'prompt' => 'Apa itu Single Responsibility Principle dalam arsitektur OOP?',
            'socratic' => true,
            'context' => 'Pemrograman Berorientasi Objek & Desain Pola',
            'mode' => 'chat',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'provider', 'response', 'mode', 'socratic', 'xp_awarded', 'suggested_prompts']);
        $this->assertTrue($response->json('success'));
        $this->assertTrue($response->json('socratic'));
        $this->assertStringContainsString('Single Responsibility', $response->json('response'));
    }

    public function test_ask_endpoint_with_direct_answer_mode(): void
    {
        $response = $this->actingAs($this->student)->postJson('/api/copilot/ask', [
            'prompt' => 'Jelaskan konsep Polimorfisme dan Interface',
            'socratic' => false,
            'context' => 'Pemrograman Berorientasi Objek',
            'mode' => 'chat',
        ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        $this->assertFalse($response->json('socratic'));
        $this->assertStringContainsString('Polimorfisme', $response->json('response'));
    }

    public function test_ask_endpoint_with_flashcard_mode(): void
    {
        $response = $this->actingAs($this->student)->postJson('/api/copilot/ask', [
            'prompt' => 'Buatkan kartu belajar materi OOP',
            'mode' => 'flashcard',
        ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        $this->assertEquals('flashcard', $response->json('mode'));
        $this->assertStringContainsString('Flashcard', $response->json('response'));
    }

    public function test_ask_endpoint_with_quiz_mode(): void
    {
        $response = $this->actingAs($this->student)->postJson('/api/copilot/ask', [
            'prompt' => 'Buatkan latihan soal kuis tentang Open/Closed Principle',
            'mode' => 'quiz',
        ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        $this->assertEquals('quiz', $response->json('mode'));
        $this->assertStringContainsString('Kunci Jawaban', $response->json('response'));
    }

    public function test_ask_endpoint_with_summarize_mode(): void
    {
        $response = $this->actingAs($this->student)->postJson('/api/copilot/ask', [
            'prompt' => 'Rangkum materi pertemuan minggu ini',
            'mode' => 'summarize',
            'context' => 'Pemrograman Berorientasi Objek',
        ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        $this->assertEquals('summarize', $response->json('mode'));
        $this->assertStringContainsString('Ringkasan Materi', $response->json('response'));
    }

    public function test_copilot_awards_xp_to_student(): void
    {
        $initialXp = $this->student->totalXp();

        $response = $this->actingAs($this->student)->postJson('/api/copilot/ask', [
            'prompt' => 'Bagaimana mengoptimalkan query execution plan pada PostgreSQL?',
            'context' => 'Basis Data Lanjut',
        ]);

        $response->assertStatus(200);
        $this->assertEquals(10, $response->json('xp_awarded'));

        $this->student->refresh();
        $this->assertEquals($initialXp + 10, $this->student->totalXp());

        $this->assertDatabaseHas('xp_ledgers', [
            'user_id' => $this->student->id,
            'source_type' => 'ai_copilot',
            'amount' => 10,
        ]);
    }

    public function test_suggestions_endpoint_returns_contextual_chips(): void
    {
        $response = $this->actingAs($this->student)->getJson('/api/copilot/suggestions?context='.urlencode('Basis Data Lanjut'));

        $response->assertStatus(200);
        $response->assertJsonStructure(['success', 'suggestions']);
        $this->assertTrue($response->json('success'));
        $this->assertNotEmpty($response->json('suggestions'));
    }

    public function test_ask_endpoint_validation_fails_on_empty_prompt(): void
    {
        $response = $this->actingAs($this->student)->postJson('/api/copilot/ask', [
            'prompt' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['prompt']);
    }
}
