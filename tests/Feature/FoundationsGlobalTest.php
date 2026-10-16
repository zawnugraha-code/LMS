<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Term;
use App\Models\User;
use App\Services\NotificationService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class FoundationsGlobalTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->student = User::where('role', UserRole::Student)->where('email', 'arya.pratama@student.unus.ac.id')->first()
            ?? User::where('role', UserRole::Student)->first();
    }

    public function test_canonical_student_exists_with_expected_profile(): void
    {
        $this->assertNotNull($this->student);
        $this->assertEquals('Arya Pratama', $this->student->name);
        $this->assertEquals('23060101024', $this->student->identifier);
        $this->assertTrue($this->student->isStudent());
    }

    public function test_topbar_search_returns_scoped_results(): void
    {
        $response = $this->actingAs($this->student)->getJson('/app/search?q=Pemrograman');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'courses',
            'materials',
            'assignments',
            'discussions',
            'total',
        ]);

        $this->assertGreaterThan(0, $response->json('total'));
    }

    public function test_notification_api_returns_unread_count_and_items(): void
    {
        $response = $this->actingAs($this->student)->getJson('/api/notifications');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'unread',
            'items',
        ]);

        $this->assertEquals(2, $response->json('unread'));
        $this->assertCount(5, $response->json('items'));
    }

    public function test_student_can_mark_single_notification_as_read(): void
    {
        $notif = DB::table('notifications')
            ->where('notifiable_id', $this->student->id)
            ->whereNull('read_at')
            ->first();

        $this->assertNotNull($notif);

        $response = $this->actingAs($this->student)->postJson("/app/notifications/{$notif->id}/read");
        $response->assertStatus(200);

        $notifService = app(NotificationService::class);
        $this->assertEquals(1, $notifService->getUnreadCount($this->student));
    }

    public function test_student_can_mark_all_notifications_as_read(): void
    {
        $response = $this->actingAs($this->student)->postJson('/app/notifications/mark-all-read');
        $response->assertStatus(200);

        $notifService = app(NotificationService::class);
        $this->assertEquals(0, $notifService->getUnreadCount($this->student));
    }

    public function test_global_academic_term_switcher_persists(): void
    {
        $term1 = Term::where('name', 'like', '%2025/2026%')->first();
        $this->assertNotNull($term1);

        $response = $this->actingAs($this->student)->post('/app/term/switch', [
            'term_id' => $term1->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('active_term_id', $term1->id);
    }

    public function test_student_can_logout_safely(): void
    {
        $response = $this->actingAs($this->student)->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_notifications_page_renders_successfully(): void
    {
        $response = $this->actingAs($this->student)->get('/app/notifications');

        $response->assertStatus(200);
        $response->assertSee('Semua Notifikasi');
    }
}
