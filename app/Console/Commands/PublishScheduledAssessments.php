<?php

namespace App\Console\Commands;

use App\Models\Assignment;
use App\Notifications\AssessmentPublishedNotification;
use Illuminate\Console\Command;

class PublishScheduledAssessments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:publish-scheduled-assessments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mempublikasikan assessment terjadwal yang sudah memasuki waktu rilis';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = now();

        $scheduled = Assignment::with(['academicClass.students'])
            ->where('status', 'terjadwal')
            ->whereNotNull('publish_at')
            ->where('publish_at', '<=', $now)
            ->get();

        if ($scheduled->isEmpty()) {
            $this->info('Tidak ada assessment terjadwal yang perlu dipublikasikan.');

            return self::SUCCESS;
        }

        $count = 0;
        foreach ($scheduled as $assignment) {
            $assignment->update(['status' => 'dipublikasikan']);
            $count++;

            // Send notification to enrolled students
            $students = $assignment->academicClass?->students ?? collect();
            foreach ($students as $student) {
                if (shouldNotify($student, 'assessment_published')) {
                    $student->notify(new AssessmentPublishedNotification($assignment));
                }
            }
        }

        $this->info("Berhasil mempublikasikan {$count} assessment terjadwal.");

        return self::SUCCESS;
    }
}
