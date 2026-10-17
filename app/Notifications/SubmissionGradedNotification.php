<?php

namespace App\Notifications;

use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SubmissionGradedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Submission $submission
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $assignmentTitle = $this->submission->assignment?->title ?? 'Tugas';
        $score = $this->submission->score;

        return [
            'type' => 'submission_graded',
            'title' => "Nilai Dirilis: {$assignmentTitle}",
            'message' => "Tugas Anda '{$assignmentTitle}' telah dinilai dengan skor {$score}. Cek nilai dan feedback dosen sekarang.",
            'url' => route('app.assignments'),
            'icon' => 'award',
            'color' => 'blue',
            'submission_id' => $this->submission->id,
            'assignment_id' => $this->submission->assignment_id,
            'score' => $score,
        ];
    }
}
