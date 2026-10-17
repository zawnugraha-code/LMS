<?php

namespace App\Notifications;

use App\Models\Assignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AssessmentPublishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Assignment $assignment
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $course = $this->assignment->academicClass?->course;
        $jenisLabel = ucfirst($this->assignment->jenis ?? 'Tugas');

        return [
            'type' => 'assessment_published',
            'title' => "{$jenisLabel} Baru: {$this->assignment->title}",
            'message' => "Dosen telah mempublikasikan {$this->assignment->title} untuk mata kuliah ".($course?->name ?? 'perkuliahan').'.',
            'url' => route('app.assignments'),
            'icon' => 'clipboard-list',
            'color' => 'blue',
            'assignment_id' => $this->assignment->id,
            'class_id' => $this->assignment->class_id,
        ];
    }
}
