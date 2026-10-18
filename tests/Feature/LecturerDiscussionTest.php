<?php

namespace Tests\Feature;

use App\Models\AcademicClass;
use App\Models\DiscussionFavorite;
use App\Models\DiscussionRead;
use App\Models\DiscussionReply;
use App\Models\DiscussionThread;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\LecturerBatch1Seeder;
use Database\Seeders\LecturerDiscussionSeeder;
use Database\Seeders\LecturerPresensiSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LecturerDiscussionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->seed(LecturerBatch1Seeder::class);
        $this->seed(LecturerPresensiSeeder::class);
        $this->seed(LecturerDiscussionSeeder::class);
    }

    /**
     * Test split-pane discussion forum index loads with 3 columns, canonical stats, and class list.
     */
    public function test_lecturer_can_view_split_pane_discussion_index(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();

        $response = $this->actingAs($dosen)->get(route('dosen.diskusi.index'));

        $response->assertStatus(200);
        $response->assertSee('Diskusi');
        $response->assertSee('Daftar Kelas');
        $response->assertSee('Statistik Diskusi');
        $response->assertSee('Diskusi Terbaru');
        $response->assertSee('Pengumuman');

        // Check canonical classes in left pane
        $response->assertSee('IF2101');
        $response->assertSee('IF2103');

        // Check Widget 1 Discussion Stats: 5, 2, 1, 2
        $response->assertViewHas('stats', function ($stats) {
            return $stats['total'] === 5
                && $stats['tanya_jawab'] === 2
                && $stats['pengumuman'] === 1
                && $stats['diskusi_umum'] === 2;
        });

        // Check Widget 2: Recent Threads (5 items)
        $response->assertViewHas('recentThreads', function ($threads) {
            return $threads->count() <= 5;
        });

        // Check Widget 3: Announcements (3 items)
        $response->assertViewHas('announcements', function ($announcements) {
            return $announcements->count() === 3;
        });
    }

    /**
     * Test filtering discussion threads by category tabs and search keywords.
     */
    public function test_lecturer_can_filter_threads_by_category_and_search(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();
        $class = AcademicClass::where('lecturer_id', $dosen->id)
            ->whereHas('course', fn ($q) => $q->where('code', 'IF2101'))
            ->where('section', 'Kelas A')
            ->firstOrFail();

        // 1. Filter Tanya Jawab
        $tanyaJawabRes = $this->actingAs($dosen)->get(route('dosen.diskusi.index', [
            'class_id' => $class->id,
            'kategori' => 'tanya_jawab',
        ]));
        $tanyaJawabRes->assertStatus(200);
        $tanyaJawabRes->assertSee('Tanya Jawab');

        // 2. Filter Pengumuman
        $pengumumanRes = $this->actingAs($dosen)->get(route('dosen.diskusi.index', [
            'class_id' => $class->id,
            'kategori' => 'pengumuman',
        ]));
        $pengumumanRes->assertStatus(200);
        $pengumumanRes->assertSee('Pengumuman');

        // 3. Search query: "SOLID"
        $searchRes = $this->actingAs($dosen)->get(route('dosen.diskusi.index', [
            'class_id' => $class->id,
            'search' => 'SOLID',
        ]));
        $searchRes->assertStatus(200);
        $searchRes->assertSee('Pertanyaan tentang implementasi SOLID pada tugas 1');
    }

    /**
     * Test creating a new discussion thread by lecturer.
     */
    public function test_lecturer_can_create_new_discussion_thread(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();
        $class = AcademicClass::where('lecturer_id', $dosen->id)->firstOrFail();

        $postData = [
            'title' => 'Diskusi Persiapan Ujian Tengah Semester IF2101',
            'content' => 'Silakan gunakan thread ini untuk mendiskusikan kisi-kisi dan materi ujian UTS minggu depan.',
            'kategori' => 'tanya_jawab',
            'is_pinned' => 1,
            'is_locked' => 0,
        ];

        $response = $this->actingAs($dosen)->post(route('dosen.diskusi.store', $class->id), $postData);

        $response->assertRedirect(route('dosen.diskusi.index', ['class_id' => $class->id]));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('discussion_threads', [
            'class_id' => $class->id,
            'user_id' => $dosen->id,
            'title' => 'Diskusi Persiapan Ujian Tengah Semester IF2101',
            'kategori' => 'tanya_jawab',
            'is_pinned' => 1,
        ]);
    }

    /**
     * Test viewing thread details marks thread as read by lecturer.
     */
    public function test_lecturer_can_view_thread_detail_and_marks_as_read(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();
        $thread = DiscussionThread::whereHas('academicClass', fn ($q) => $q->where('lecturer_id', $dosen->id))->firstOrFail();

        // Clear existing read status for test purity
        DiscussionRead::where('user_id', $dosen->id)->where('thread_id', $thread->id)->delete();

        $response = $this->actingAs($dosen)->get(route('dosen.diskusi.show', $thread->id));

        $response->assertStatus(200);
        $response->assertSee($thread->title);
        $response->assertSee('Semua Balasan');

        // Verify mark-as-read in database
        $this->assertDatabaseHas('discussion_reads', [
            'user_id' => $dosen->id,
            'thread_id' => $thread->id,
        ]);
    }

    /**
     * Test posting replies to a thread and nested reply.
     */
    public function test_lecturer_can_post_reply_and_nested_reply(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();
        $thread = DiscussionThread::whereHas('academicClass', fn ($q) => $q->where('lecturer_id', $dosen->id))
            ->where('is_locked', false)
            ->firstOrFail();

        // 1. Direct reply to thread
        $replyRes = $this->actingAs($dosen)->post(route('dosen.diskusi.reply', $thread->id), [
            'content' => 'Terima kasih atas pertanyaannya. Pastikan menerapkan Single Responsibility Principle.',
        ]);

        $replyRes->assertRedirect();
        $replyRes->assertSessionHas('success');

        $reply = DiscussionReply::where('thread_id', $thread->id)
            ->where('user_id', $dosen->id)
            ->where('content', 'Terima kasih atas pertanyaannya. Pastikan menerapkan Single Responsibility Principle.')
            ->firstOrFail();

        // 2. Nested child reply
        $nestedRes = $this->actingAs($dosen)->post(route('dosen.diskusi.reply', $thread->id), [
            'content' => 'Tambahan: perhatikan juga pemisahan interface.',
            'parent_id' => $reply->id,
        ]);

        $nestedRes->assertRedirect();
        $this->assertDatabaseHas('discussion_replies', [
            'thread_id' => $thread->id,
            'parent_id' => $reply->id,
            'content' => 'Tambahan: perhatikan juga pemisahan interface.',
        ]);
    }

    /**
     * Test lock and unlock toggles and ensure locked thread rejects replies.
     */
    public function test_lecturer_can_toggle_lock_and_locked_thread_rejects_replies(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();
        $thread = DiscussionThread::whereHas('academicClass', fn ($q) => $q->where('lecturer_id', $dosen->id))->firstOrFail();

        // Ensure unlocked initially
        $thread->update(['is_locked' => false]);

        // Toggle to locked
        $lockRes = $this->actingAs($dosen)->post(route('dosen.diskusi.toggle_lock', $thread->id));
        $lockRes->assertRedirect();
        $this->assertTrue($thread->fresh()->is_locked);

        // Attempt to post reply while locked
        $failReplyRes = $this->actingAs($dosen)->post(route('dosen.diskusi.reply', $thread->id), [
            'content' => 'Balasan ini harus gagal karena diskusi terkunci.',
        ]);
        $failReplyRes->assertSessionHas('error');

        // Toggle to unlocked
        $unlockRes = $this->actingAs($dosen)->post(route('dosen.diskusi.toggle_lock', $thread->id));
        $unlockRes->assertRedirect();
        $this->assertFalse($thread->fresh()->is_locked);
    }

    /**
     * Test toggle pin on thread.
     */
    public function test_lecturer_can_toggle_pin(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();
        $thread = DiscussionThread::whereHas('academicClass', fn ($q) => $q->where('lecturer_id', $dosen->id))->firstOrFail();

        $initialPinned = $thread->is_pinned;

        $pinRes = $this->actingAs($dosen)->post(route('dosen.diskusi.toggle_pin', $thread->id));
        $pinRes->assertRedirect();
        $this->assertEquals(! $initialPinned, $thread->fresh()->is_pinned);

        // Toggle back
        $this->actingAs($dosen)->post(route('dosen.diskusi.toggle_pin', $thread->id));
        $this->assertEquals($initialPinned, $thread->fresh()->is_pinned);
    }

    /**
     * Test star favorite toggle via HTTP redirect and JSON AJAX.
     */
    public function test_lecturer_can_toggle_star_favorite(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();
        $thread = DiscussionThread::whereHas('academicClass', fn ($q) => $q->where('lecturer_id', $dosen->id))->firstOrFail();

        // Clear existing favorite
        DiscussionFavorite::where('user_id', $dosen->id)->where('thread_id', $thread->id)->delete();

        // 1. Add to favorite via form post
        $favRes = $this->actingAs($dosen)->post(route('dosen.diskusi.favorite', $thread->id));
        $favRes->assertRedirect();
        $this->assertDatabaseHas('discussion_favorites', [
            'user_id' => $dosen->id,
            'thread_id' => $thread->id,
        ]);

        // 2. Remove from favorite via JSON
        $unfavJson = $this->actingAs($dosen)->postJson(route('dosen.diskusi.favorite', $thread->id));
        $unfavJson->assertStatus(200);
        $unfavJson->assertJson(['favorited' => false]);
        $this->assertDatabaseMissing('discussion_favorites', [
            'user_id' => $dosen->id,
            'thread_id' => $thread->id,
        ]);
    }

    /**
     * Test security isolation: student cannot access lecturer discussion routes,
     * and lecturer cannot access threads from another lecturer's class.
     */
    public function test_security_isolation_for_discussion_module(): void
    {
        $student = User::where('role', 'student')->firstOrFail();
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();

        // 1. Student access to lecturer discussion index should be forbidden or redirected
        $studentRes = $this->actingAs($student)->get(route('dosen.diskusi.index'));
        $this->assertTrue(in_array($studentRes->status(), [302, 403]));

        // 2. Lecturer trying to access thread from another lecturer's class
        $otherClass = AcademicClass::where('lecturer_id', '!=', $dosen->id)->first();
        if ($otherClass) {
            $otherThread = DiscussionThread::where('class_id', $otherClass->id)->first();
            if ($otherThread) {
                $otherRes = $this->actingAs($dosen)->get(route('dosen.diskusi.show', $otherThread->id));
                $otherRes->assertStatus(403);
            }
        }
    }
}
