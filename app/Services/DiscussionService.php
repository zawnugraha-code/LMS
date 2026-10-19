<?php

namespace App\Services;

use App\Models\DiscussionCategory;
use App\Models\DiscussionReply;
use App\Models\DiscussionThread;
use App\Models\DiscussionView;
use App\Models\User;
use App\Models\XpLedger;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DiscussionService
{
    /**
     * Get filtered threads with author, category, class, and counts.
     */
    public function getThreads(?string $categorySlug = null, ?string $search = null, ?string $tab = 'all'): Collection
    {
        $query = DiscussionThread::with(['user', 'category', 'academicClass.course', 'replies.user'])
            ->withCount('replies');

        // Tab-specific filters
        if ($tab === 'forum') {
            $query->whereHas('category', fn ($q) => $q->where('slug', 'forum-kelas'));
        } elseif ($tab === 'tanya') {
            $query->whereHas('category', fn ($q) => $q->where('slug', 'tanya-jawab'));
        } elseif ($tab === 'pengumuman') {
            $query->whereHas('category', fn ($q) => $q->where('slug', 'pengumuman'));
        } elseif ($tab === 'populer') {
            $query->orderByDesc('views_count');
        }

        // Direct category slug filter
        if ($categorySlug && $categorySlug !== 'all') {
            $query->whereHas('category', fn ($q) => $q->where('slug', $categorySlug));
        }

        // Search query
        if ($search && trim($search) !== '') {
            $term = '%'.trim($search).'%';
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', $term)
                    ->orWhere('content', 'like', $term)
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', $term));
            });
        }

        // Sort: pinned first, then latest
        return $query->orderByDesc('is_pinned')
            ->latest('created_at')
            ->get();
    }

    /**
     * Get all categories with counts.
     */
    public function getCategories(): Collection
    {
        return DiscussionCategory::withCount('threads')->get();
    }

    /**
     * Get summary metrics for discussion forum.
     */
    public function getDiscussionStats(): array
    {
        $totalThreads = DiscussionThread::count();
        $totalReplies = DiscussionReply::count();
        $totalViews = (int) DiscussionThread::sum('views_count');

        $activeThreadUsers = DiscussionThread::distinct()->pluck('user_id');
        $activeReplyUsers = DiscussionReply::distinct()->pluck('user_id');
        $activeUsersCount = $activeThreadUsers->merge($activeReplyUsers)->unique()->count();

        // Format views for display (e.g. 8.4k)
        $formattedViews = $totalViews >= 1000
            ? number_format($totalViews / 1000, 1, '.', '').'k'
            : (string) $totalViews;

        return [
            'total_threads' => $totalThreads,
            'total_replies' => $totalReplies,
            'total_views_raw' => $totalViews,
            'total_views' => $formattedViews,
            'active_users' => max($activeUsersCount, 12),
        ];
    }

    /**
     * Get top popular threads.
     */
    public function getPopularThreads(int $limit = 5): Collection
    {
        return DiscussionThread::with(['user', 'category', 'academicClass.course'])
            ->withCount('replies')
            ->orderByDesc('views_count')
            ->limit($limit)
            ->get();
    }

    /**
     * Create a new discussion thread and award XP.
     */
    public function createThread(User $user, array $data, ?UploadedFile $attachment = null): DiscussionThread
    {
        return DB::transaction(function () use ($user, $data, $attachment) {
            $attachmentPath = null;
            if ($attachment) {
                $attachmentPath = $attachment->store('discussions', 'private');
            }

            $thread = DiscussionThread::create([
                'user_id' => $user->id,
                'class_id' => $data['class_id'] ?? null,
                'category_id' => $data['category_id'],
                'title' => $data['title'],
                'content' => $data['content'],
                'attachment_path' => $attachmentPath,
                'is_pinned' => $data['is_pinned'] ?? false,
                'views_count' => 1,
                'upvotes_count' => 0,
            ]);

            // Award +15 XP
            XpLedger::create([
                'user_id' => $user->id,
                'class_id' => $thread->class_id,
                'amount' => 15,
                'source_type' => 'discussion_thread',
                'source_id' => $thread->id,
                'description' => 'Membuat topik diskusi baru: '.$thread->title,
            ]);

            return $thread;
        });
    }

    /**
     * Create a reply to a thread and award XP.
     */
    public function createReply(User $user, DiscussionThread $thread, string $content, ?int $parentId = null): DiscussionReply
    {
        return DB::transaction(function () use ($user, $thread, $content, $parentId) {
            $reply = DiscussionReply::create([
                'thread_id' => $thread->id,
                'user_id' => $user->id,
                'parent_id' => $parentId,
                'content' => $content,
                'is_solution' => false,
                'upvotes_count' => 0,
            ]);

            // Award +10 XP
            XpLedger::create([
                'user_id' => $user->id,
                'class_id' => $thread->class_id,
                'amount' => 10,
                'source_type' => 'discussion_reply',
                'source_id' => $reply->id,
                'description' => 'Membalas topik diskusi: '.$thread->title,
            ]);

            // Create notification for thread author if different user
            if ($thread->user_id !== $user->id) {
                DB::table('notifications')->insert([
                    'id' => (string) Str::uuid(),
                    'type' => 'App\\Notifications\\DiscussionReplyReceived',
                    'notifiable_type' => User::class,
                    'notifiable_id' => $thread->user_id,
                    'data' => json_encode([
                        'title' => 'Tanggapan Diskusi Baru',
                        'message' => "{$user->name} membalas pertanyaan Anda di thread {$thread->title}",
                        'action_url' => route('app.discussions.show', $thread),
                        'category' => 'discussion',
                        'icon' => 'message-circle',
                    ]),
                    'read_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return $reply;
        });
    }

    /**
     * Toggle or add upvote to a discussion thread.
     */
    public function upvoteThread(User $user, DiscussionThread $thread): int
    {
        return DB::transaction(function () use ($user, $thread) {
            $existingXp = XpLedger::where('user_id', $user->id)
                ->where('source_type', 'discussion_upvote_thread')
                ->where('source_id', $thread->id)
                ->first();

            if (! $existingXp) {
                $thread->increment('upvotes_count');

                XpLedger::create([
                    'user_id' => $user->id,
                    'class_id' => $thread->class_id,
                    'amount' => 5,
                    'source_type' => 'discussion_upvote_thread',
                    'source_id' => $thread->id,
                    'description' => 'Memberikan apresiasi upvote pada thread: '.$thread->title,
                ]);
            }

            return $thread->fresh()->upvotes_count;
        });
    }

    /**
     * Toggle or add upvote to a discussion reply.
     */
    public function upvoteReply(User $user, DiscussionReply $reply): int
    {
        return DB::transaction(function () use ($user, $reply) {
            $existingXp = XpLedger::where('user_id', $user->id)
                ->where('source_type', 'discussion_upvote_reply')
                ->where('source_id', $reply->id)
                ->first();

            if (! $existingXp) {
                $reply->increment('upvotes_count');

                XpLedger::create([
                    'user_id' => $user->id,
                    'class_id' => $reply->thread?->class_id,
                    'amount' => 5,
                    'source_type' => 'discussion_upvote_reply',
                    'source_id' => $reply->id,
                    'description' => 'Memberikan apresiasi upvote pada balasan diskusi',
                ]);
            }

            return $reply->fresh()->upvotes_count;
        });
    }

    /**
     * Mark a reply as best answer / solution.
     */
    public function markAsSolution(User $user, DiscussionReply $reply): bool
    {
        $thread = $reply->thread;
        if (! $thread) {
            return false;
        }

        // Only thread author or lecturer can mark solution
        $isAuthor = $thread->user_id === $user->id;
        $isLecturer = $user->isLecturer() || ($thread->academicClass && $thread->academicClass->lecturer_id === $user->id);

        if (! $isAuthor && ! $isLecturer) {
            return false;
        }

        return DB::transaction(function () use ($reply, $thread) {
            // Unmark any previous solutions in this thread
            DiscussionReply::where('thread_id', $thread->id)->update(['is_solution' => false]);

            $reply->update(['is_solution' => true]);

            // Award +20 XP to the reply author for providing the solution
            XpLedger::create([
                'user_id' => $reply->user_id,
                'class_id' => $thread->class_id,
                'amount' => 20,
                'source_type' => 'discussion_solution_accepted',
                'source_id' => $reply->id,
                'description' => 'Jawaban Anda ditandai sebagai Jawaban Terbaik di: '.$thread->title,
            ]);

            return true;
        });
    }

    /**
     * Record a unique daily view for a thread.
     */
    public function recordView(DiscussionThread $thread, ?User $user, string $ipAddress = '127.0.0.1'): void
    {
        if (! $user) {
            return;
        }

        $today = now()->toDateString();
        $view = DiscussionView::firstOrCreate([
            'thread_id' => $thread->id,
            'user_id' => $user->id,
            'viewed_date' => $today,
        ]);

        if ($view->wasRecentlyCreated) {
            $thread->increment('views_count');
        }
    }
}
