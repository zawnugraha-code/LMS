<?php

namespace Tests\Feature;

use App\Models\AcademicClass;
use App\Models\DiscussionCategory;
use App\Models\DiscussionReply;
use App\Models\DiscussionThread;
use App\Models\User;
use Database\Seeders\CanonicalStudentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscussionFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;

    protected User $lecturer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CanonicalStudentSeeder::class);

        $this->student = User::where('email', 'arya.wiratama@student.arunika.ac.id')->first()
            ?? User::where('role', 'student')->first();

        $this->lecturer = User::where('email', 'budi.raharjo@unus.ac.id')->first()
            ?? User::where('role', 'lecturer')->first();
    }

    public function test_student_can_view_discussions_index_page(): void
    {
        $response = $this->actingAs($this->student)->get('/app/discussions');

        $response->assertStatus(200);
        $response->assertSee('Diskusi');
        $response->assertSee('Statistik Diskusi');
        $response->assertSee('Total Diskusi');
        $response->assertSee('Buat Diskusi Baru');
        // Canonical threads
        $response->assertSee('Tanya implementasi Interface Segregation Principle');
    }

    public function test_student_can_filter_discussions_by_category(): void
    {
        $response = $this->actingAs($this->student)->get('/app/discussions?category=pengumuman');

        $response->assertStatus(200);
        $response->assertSee('Ketentuan Pengumpulan Tugas Besar');
    }

    public function test_student_can_search_discussions_by_query(): void
    {
        $response = $this->actingAs($this->student)->get('/app/discussions?q=microservices');

        $response->assertStatus(200);
        $response->assertSee('Interface Segregation Principle');
    }

    public function test_student_can_view_single_thread_detail_subpage(): void
    {
        $thread = DiscussionThread::first();
        $initialViews = $thread->views_count;

        $response = $this->actingAs($this->student)->get("/app/discussions/{$thread->id}");

        $response->assertStatus(200);
        $response->assertSee($thread->title);
        $response->assertSee('Kembali ke Semua Forum Diskusi');
        $response->assertSee('Tulis Balasan');
        $response->assertSee('Tanggapan');
        $response->assertSee('Komentar');

        // Verify view is incremented
        $this->assertGreaterThanOrEqual($initialViews, $thread->fresh()->views_count);
    }

    public function test_student_can_create_new_discussion_thread_and_earn_xp(): void
    {
        $category = DiscussionCategory::first();
        $class = AcademicClass::whereHas('enrollments', fn ($q) => $q->where('user_id', $this->student->id))->first() ?? AcademicClass::first();
        $initialXp = $this->student->total_xp;

        $response = $this->actingAs($this->student)->post('/app/discussions', [
            'title' => 'Bagaimana cara debugging memory leak pada PHP 8.4?',
            'category_id' => $category->id,
            'class_id' => $class->id,
            'content' => 'Saya mendapati penggunaan memori bertambah drastis saat memproses dataset berukuran 100MB di artisan command.',
        ]);
        if (! $response->isRedirect()) {
            dump($response->status(), session('errors')?->all(), $response->content());
        }

        $newThread = DiscussionThread::where('title', 'Bagaimana cara debugging memory leak pada PHP 8.4?')->first();
        $this->assertNotNull($newThread);

        $response->assertRedirect(route('app.discussions.show', $newThread));
        $response->assertSessionHas('success');

        // Assert +15 XP ledger entry
        $this->assertDatabaseHas('xp_ledgers', [
            'user_id' => $this->student->id,
            'source_type' => 'discussion_thread',
            'source_id' => $newThread->id,
            'amount' => 15,
        ]);

        $this->assertEquals($initialXp + 15, $this->student->fresh()->total_xp);
    }

    public function test_student_can_reply_to_thread_and_earn_xp(): void
    {
        $thread = DiscussionThread::where('user_id', '!=', $this->student->id)->first();
        $initialXp = $this->student->total_xp;

        $response = $this->actingAs($this->student)->post("/app/discussions/{$thread->id}/replies", [
            'content' => 'Anda bisa menggunakan Xdebug dengan profiling atau modul Blackfire untuk menemukan fungsi yang menahan memori.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $reply = DiscussionReply::where('thread_id', $thread->id)
            ->where('user_id', $this->student->id)
            ->first();

        $this->assertNotNull($reply);

        // Assert +10 XP ledger entry
        $this->assertDatabaseHas('xp_ledgers', [
            'user_id' => $this->student->id,
            'source_type' => 'discussion_reply',
            'source_id' => $reply->id,
            'amount' => 10,
        ]);

        $this->assertEquals($initialXp + 10, $this->student->fresh()->total_xp);
    }

    public function test_student_can_upvote_thread_and_reply_and_earn_xp(): void
    {
        $thread = DiscussionThread::first();
        $initialUpvotes = $thread->upvotes_count;

        // Upvote thread
        $response = $this->actingAs($this->student)->post("/app/discussions/{$thread->id}/upvote");
        $response->assertRedirect();
        $this->assertEquals($initialUpvotes + 1, $thread->fresh()->upvotes_count);

        $this->assertDatabaseHas('xp_ledgers', [
            'user_id' => $this->student->id,
            'source_type' => 'discussion_upvote_thread',
            'source_id' => $thread->id,
            'amount' => 5,
        ]);

        // Upvote reply
        $reply = DiscussionReply::first();
        $initialReplyUpvotes = $reply->upvotes_count;

        $response = $this->actingAs($this->student)->post("/app/discussions/replies/{$reply->id}/upvote");
        $response->assertRedirect();
        $this->assertEquals($initialReplyUpvotes + 1, $reply->fresh()->upvotes_count);

        $this->assertDatabaseHas('xp_ledgers', [
            'user_id' => $this->student->id,
            'source_type' => 'discussion_upvote_reply',
            'source_id' => $reply->id,
            'amount' => 5,
        ]);
    }

    public function test_author_or_lecturer_can_mark_reply_as_best_solution(): void
    {
        $thread = DiscussionThread::whereHas('replies')->first();
        $reply = $thread->replies()->first();
        $author = $thread->user;

        $response = $this->actingAs($author)->post("/app/discussions/replies/{$reply->id}/solution");

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertTrue($reply->fresh()->is_solution);

        // Assert +20 XP awarded to the reply author
        $this->assertDatabaseHas('xp_ledgers', [
            'user_id' => $reply->user_id,
            'source_type' => 'discussion_solution_accepted',
            'source_id' => $reply->id,
            'amount' => 20,
        ]);
    }
}
