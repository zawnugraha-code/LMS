<?php

namespace App\Notifications;

use App\Models\DiscussionReply;
use App\Models\DiscussionThread;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class DiscussionRepliedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public DiscussionThread $thread,
        public DiscussionReply $reply,
        public User $replier
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $threadTitle = $this->thread->title;
        $replierName = $this->replier->name;

        return [
            'type' => 'discussion_replied',
            'title' => "Balasan Baru: {$threadTitle}",
            'message' => "{$replierName} membalas diskusi Anda pada '{$threadTitle}'.",
            'url' => route('dosen.diskusi.show', $this->thread->id),
            'icon' => 'message-square',
            'color' => 'blue',
            'thread_id' => $this->thread->id,
            'reply_id' => $this->reply->id,
            'replier_id' => $this->replier->id,
        ];
    }
}
