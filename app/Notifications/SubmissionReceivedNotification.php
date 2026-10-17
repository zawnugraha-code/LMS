<?php

namespace App\Notifications;

use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SubmissionReceivedNotification extends Notification implements ShouldQueue
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
        $studentName = $this->submission->user?->name ?? 'Mahasiswa';
        $assignmentTitle = $this->submission->assignment?->title ?? 'Tugas';
        $statusWaktu = $this->submission->status_waktu === 'terlambat' ? ' (Terlambat)' : '';

        return [
            'type' => 'submission_received',
            'title' => "Pengumpulan Baru: {$assignmentTitle}",
            'message' => "{$studentName} telah mengumpulkan {$assignmentTitle}{$statusWaktu} dan siap untuk dinilai.",
            'url' => route('dosen.penilaian.index'),
            'icon' => 'check-circle',
            'color' => $this->submission->status_waktu === 'terlambat' ? 'amber' : 'emerald',
            'submission_id' => $this->submission->id,
            'assignment_id' => $this->submission->assignment_id,
        ];
    }
}
