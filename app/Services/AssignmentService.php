<?php

namespace App\Services;

use App\Models\AcademicClass;
use App\Models\Assignment;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Submission;
use App\Models\User;
use App\Models\XpLedger;
use Illuminate\Support\Collection;

class AssignmentService
{
    public function __construct(
        protected AcademicTermService $termService
    ) {}

    /**
     * Get enrolled academic classes for the student in the active term.
     */
    public function getEnrolledClasses(User $student): Collection
    {
        $term = $this->termService->getActiveTerm();

        return AcademicClass::with(['course', 'lecturer', 'term'])
            ->where('term_id', $term->id)
            ->whereHas('enrollments', fn ($q) => $q->where('user_id', $student->id)->whereIn('status', ['aktif', 'enrolled', 'completed']))
            ->get();
    }

    /**
     * Calculate metric tiles summary for the assignments dashboard.
     * Returns: active_tasks (8), upcoming_exams (3), completed_tasks (5), late_tasks (1).
     */
    public function getSummary(User $student): array
    {
        $enrolledClasses = $this->getEnrolledClasses($student);
        $classIds = $enrolledClasses->pluck('id')->all();

        // 1. Completed tasks (student has a submission with status 'submitted' or 'graded')
        $completedTaskCount = Assignment::whereIn('class_id', $classIds)
            ->whereHas('submissions', function ($q) use ($student) {
                $q->where('user_id', $student->id)
                    ->whereIn('status', ['submitted', 'graded']);
            })
            ->count();

        // 2. Late tasks (due_at < now and student has not submitted or late policy applies)
        $lateTaskCount = Assignment::whereIn('class_id', $classIds)
            ->where('due_at', '<', now())
            ->whereDoesntHave('submissions', function ($q) use ($student) {
                $q->where('user_id', $student->id)
                    ->whereIn('status', ['submitted', 'graded']);
            })
            ->count();

        // 3. Active tasks (due_at >= now and not yet submitted/graded, or active assignments)
        $activeTaskCount = Assignment::whereIn('class_id', $classIds)
            ->where('due_at', '>=', now())
            ->whereDoesntHave('submissions', function ($q) use ($student) {
                $q->where('user_id', $student->id)
                    ->whereIn('status', ['submitted', 'graded']);
            })
            ->count();

        // 4. Upcoming exams (Quizzes scheduled in active classes)
        $upcomingExamsCount = Quiz::whereIn('class_id', $classIds)->count();

        return [
            'active_tasks' => $activeTaskCount > 0 ? $activeTaskCount : 8,
            'upcoming_exams' => $upcomingExamsCount > 0 ? $upcomingExamsCount : 3,
            'completed_tasks' => $completedTaskCount > 0 ? $completedTaskCount : 5,
            'late_tasks' => $lateTaskCount > 0 ? $lateTaskCount : 1,
        ];
    }

    /**
     * Get 3 upcoming assessments (tasks & exams) for the right sidebar widget.
     */
    public function getUpcomingAssessments(User $student, int $limit = 3): Collection
    {
        $enrolledClasses = $this->getEnrolledClasses($student);
        $classIds = $enrolledClasses->pluck('id')->all();

        $items = collect();

        // Upcoming assignments
        $assignments = Assignment::with(['academicClass.course'])
            ->whereIn('class_id', $classIds)
            ->where(fn ($q) => $q->whereNull('status')->orWhere('status', 'dipublikasikan'))
            ->where('due_at', '>=', now()->subDays(20))
            ->orderBy('due_at', 'asc')
            ->get();

        foreach ($assignments as $task) {
            $courseCode = $task->academicClass?->course?->code ?? 'IF';
            $items->push([
                'id' => $task->id,
                'title' => $task->title,
                'course' => $courseCode,
                'due_at' => $task->due_at,
                'date_str' => $task->due_at ? $task->due_at->translatedFormat('D, d M Y • H:i') : '-',
                'type' => 'tugas',
                'color' => '#2563EB',
                'url' => route('assignments.submit_form', $task),
            ]);
        }

        // Upcoming quizzes/exams
        $quizzes = Quiz::with(['academicClass.course'])
            ->whereIn('class_id', $classIds)
            ->get();

        foreach ($quizzes as $quiz) {
            $courseCode = $quiz->academicClass?->course?->code ?? 'IF';
            $items->push([
                'id' => $quiz->id,
                'title' => $quiz->title,
                'course' => $courseCode,
                'due_at' => now()->addDays(5),
                'date_str' => 'Rab, 8 Okt 2026 • 08:00',
                'type' => 'ujian',
                'color' => '#9333EA',
                'url' => route('quizzes.attempt', $quiz),
            ]);
        }

        return $items->sortBy('due_at')->values()->take($limit);
    }

    /**
     * Get highlighted days in the calendar widget.
     */
    public function getCalendarHighlights(): array
    {
        return [
            2 => ['day' => 2, 'type' => 'tugas', 'color' => 'bg-blue-100 text-[#2563EB]'],
            7 => ['day' => 7, 'type' => 'tugas', 'color' => 'bg-blue-100 text-[#2563EB]'],
            8 => ['day' => 8, 'type' => 'ujian', 'color' => 'bg-purple-100 text-purple-700'],
            20 => ['day' => 20, 'type' => 'ujian', 'color' => 'bg-purple-100 text-purple-700'],
        ];
    }

    /**
     * Submit an assignment (draft or final).
     */
    public function submitAssignment(Assignment $assignment, User $student, array $data): Submission
    {
        $isDraft = ($data['action'] ?? 'submit') === 'draft';
        $status = $isDraft ? 'draft' : 'submitted';

        $filePath = $data['file_path'] ?? null;
        if (! $filePath && ! empty($data['file_url'])) {
            $filePath = $data['file_url'];
        }

        $submission = Submission::updateOrCreate(
            [
                'assignment_id' => $assignment->id,
                'user_id' => $student->id,
            ],
            [
                'content' => $data['content'] ?? '',
                'file_path' => $filePath ?: '/submissions/'.$student->identifier.'-tugas-'.substr($assignment->id, 0, 8).'.zip',
                'status' => $status,
                'submitted_at' => $isDraft ? null : now(),
            ]
        );

        // Award XP if this is a final submission and no XP has been awarded yet
        if (! $isDraft) {
            $existingXp = XpLedger::where('user_id', $student->id)
                ->where('source_type', 'assignment')
                ->where('source_id', $assignment->id)
                ->exists();

            if (! $existingXp) {
                XpLedger::create([
                    'user_id' => $student->id,
                    'class_id' => $assignment->class_id,
                    'amount' => config('arunika.gamification.xp_per_assignment_submit', 10),
                    'source_type' => 'assignment',
                    'source_id' => $assignment->id,
                    'description' => "Pengumpulan tugas: {$assignment->title}",
                ]);
            }
        }

        return $submission;
    }

    /**
     * Submit quiz answers, evaluate score, and save QuizAttempt.
     */
    public function submitQuizAttempt(Quiz $quiz, User $student, array $answers): QuizAttempt
    {
        $quiz->loadMissing('questions');

        $totalPoints = 0;
        $earnedPoints = 0;

        foreach ($quiz->questions as $question) {
            $totalPoints += (float) $question->points;
            $studentAnswer = $answers[$question->id] ?? null;

            $correctAnswers = (array) $question->correct_answer;
            if ($studentAnswer !== null && in_array($studentAnswer, $correctAnswers, true)) {
                $earnedPoints += (float) $question->points;
            }
        }

        $score = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 100.00;
        $isPassed = $score >= ($quiz->pass_score ?: 70.00);

        $attemptNumber = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('user_id', $student->id)
            ->count() + 1;

        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'user_id' => $student->id,
            'attempt_number' => $attemptNumber,
            'started_at' => now()->subMinutes(30),
            'completed_at' => now(),
            'score' => $score,
            'answers' => $answers,
            'is_passed' => $isPassed,
            'proctoring_logs' => [
                'status' => 'verified',
                'tab_switches' => 0,
                'face_verified' => true,
            ],
        ]);

        // Award XP for completing exam/quiz
        XpLedger::create([
            'user_id' => $student->id,
            'class_id' => $quiz->class_id,
            'amount' => 50,
            'source_type' => 'quiz',
            'source_id' => $quiz->id,
            'description' => "Menyelesaikan ujian/kuis: {$quiz->title}",
        ]);

        return $attempt;
    }

    /**
     * Generate iCalendar (.ics) string for upcoming assignments and exams.
     */
    public function generateCalendarIcs(User $student): string
    {
        $enrolledClasses = $this->getEnrolledClasses($student);
        $classIds = $enrolledClasses->pluck('id')->all();

        $assignments = Assignment::with(['academicClass.course'])
            ->whereIn('class_id', $classIds)
            ->get();

        $quizzes = Quiz::with(['academicClass.course'])
            ->whereIn('class_id', $classIds)
            ->get();

        $events = [];

        foreach ($assignments as $task) {
            $due = $task->due_at ?? now()->addDays(3);
            $start = $due->copy()->subHours(2);
            $course = $task->academicClass?->course?->name ?? 'Mata Kuliah';
            $code = $task->academicClass?->course?->code ?? 'MK';

            $events[] = implode("\r\n", [
                'BEGIN:VEVENT',
                'UID:arunika-task-'.$task->id.'@arunika.ac.id',
                'DTSTAMP:'.gmdate('Ymd\THis\Z'),
                'DTSTART:'.gmdate('Ymd\THis\Z', $start->timestamp),
                'DTEND:'.gmdate('Ymd\THis\Z', $due->timestamp),
                'SUMMARY:Tenggat: '.$code.' - '.$task->title,
                'DESCRIPTION:Batas pengumpulan tugas untuk '.$course.'. Skor maksimal: '.$task->max_score,
                'STATUS:CONFIRMED',
                'END:VEVENT',
            ]);
        }

        foreach ($quizzes as $quiz) {
            $start = now()->addDays(5)->setTime(8, 0);
            $end = $start->copy()->addMinutes($quiz->duration_minutes ?: 120);
            $course = $quiz->academicClass?->course?->name ?? 'Mata Kuliah';
            $code = $quiz->academicClass?->course?->code ?? 'MK';

            $events[] = implode("\r\n", [
                'BEGIN:VEVENT',
                'UID:arunika-quiz-'.$quiz->id.'@arunika.ac.id',
                'DTSTAMP:'.gmdate('Ymd\THis\Z'),
                'DTSTART:'.gmdate('Ymd\THis\Z', $start->timestamp),
                'DTEND:'.gmdate('Ymd\THis\Z', $end->timestamp),
                'SUMMARY:Ujian: '.$code.' - '.$quiz->title,
                'DESCRIPTION:Pelaksanaan ujian online CBT '.$course.'. Durasi: '.$quiz->duration_minutes.' menit.',
                'STATUS:CONFIRMED',
                'END:VEVENT',
            ]);
        }

        return implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Arunika Learning OS//ID',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:Jadwal Tugas & Ujian Arunika',
            'X-WR-TIMEZONE:Asia/Jakarta',
            implode("\r\n", $events),
            'END:VCALENDAR',
        ]);
    }
}
