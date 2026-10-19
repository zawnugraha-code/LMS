<?php

namespace App\Services;

use App\Models\AcademicClass;
use App\Models\Assignment;
use App\Models\Quiz;
use App\Models\Term;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class ScheduleService
{
    public function __construct(
        protected AcademicTermService $termService
    ) {}

    /**
     * Get active academic term.
     */
    public function getActiveTerm(): ?Term
    {
        return $this->termService->getActiveTerm();
    }

    /**
     * Get all classes enrolled by the student in the target or active term.
     */
    public function getEnrolledClasses(User $student, ?int $termId = null): Collection
    {
        $term = $termId ? Term::find($termId) : $this->getActiveTerm();

        $query = AcademicClass::with(['course', 'lecturer', 'term', 'meetings.materials'])
            ->whereHas('enrollments', fn ($q) => $q->where('user_id', $student->id)->accessible());

        if ($term) {
            $query->where('term_id', $term->id);
        }

        return $query->get()->map(function ($class) {
            $class->formatted_time = $this->formatClassTime($class->start_time, $class->end_time);
            $class->duration_minutes = $this->calculateDuration($class->start_time, $class->end_time);
            $class->color_theme = $this->getCourseColor($class->course?->code);

            return $class;
        });
    }

    /**
     * Get classes scheduled for today.
     */
    public function getTodayClasses(User $student): Collection
    {
        $classes = $this->getEnrolledClasses($student);
        $todayDayName = now()->locale('id')->isoFormat('dddd'); // e.g. "Senin", "Selasa", ...
        $currentTime = now()->format('H:i:s');

        $todayClasses = $classes->filter(function ($class) use ($todayDayName) {
            return strcasecmp(trim($class->schedule_day ?? ''), trim($todayDayName)) === 0;
        })->sortBy('start_time')->values();

        // Calculate active live status (ongoing, upcoming, completed)
        return $todayClasses->map(function ($class) use ($currentTime) {
            $startTime = $class->start_time ? Carbon::parse($class->start_time)->format('H:i:s') : '08:00:00';
            $endTime = $class->end_time ? Carbon::parse($class->end_time)->format('H:i:s') : '10:00:00';

            if ($currentTime >= $startTime && $currentTime <= $endTime) {
                $class->session_status = 'ongoing';
                $class->status_label = 'Sedang Berlangsung';
                $class->status_badge = 'bg-emerald-100 text-emerald-800 border-emerald-200';
            } elseif ($currentTime < $startTime) {
                $class->session_status = 'upcoming';
                $class->status_label = 'Akan Datang';
                $class->status_badge = 'bg-blue-100 text-blue-800 border-blue-200';
            } else {
                $class->session_status = 'completed';
                $class->status_label = 'Selesai';
                $class->status_badge = 'bg-slate-100 text-slate-700 border-slate-200';
            }

            return $class;
        });
    }

    /**
     * Compute summary metrics for schedule header.
     */
    public function getSummaryMetrics(User $student, ?int $termId = null): array
    {
        $classes = $this->getEnrolledClasses($student, $termId);

        $totalCredits = $classes->sum(fn ($c) => $c->course?->credits ?? 0);
        $totalCourses = $classes->count();

        // Total hours calculated from minutes
        $totalMinutes = $classes->sum(fn ($c) => $c->duration_minutes ?? 120);
        $totalHours = round($totalMinutes / 60, 1);

        // Group by day to find busiest day
        $dayCounts = [];
        foreach ($classes as $class) {
            $day = ucfirst(strtolower(trim($class->schedule_day ?? 'Senin')));
            $dayCounts[$day] = ($dayCounts[$day] ?? 0) + 1;
        }

        arsort($dayCounts);
        $busiestDay = ! empty($dayCounts) ? array_key_first($dayCounts).' ('.reset($dayCounts).' MK)' : 'Belum Ada';

        // Today's classes count
        $todayClasses = $this->getTodayClasses($student);

        return [
            'total_credits' => $totalCredits,
            'total_courses' => $totalCourses,
            'total_hours_per_week' => $totalHours,
            'busiest_day' => $busiestDay,
            'today_count' => $todayClasses->count(),
            'today_classes' => $todayClasses,
        ];
    }

    /**
     * Get weekly schedule grouped by standard academic days (Senin - Sabtu).
     */
    public function getWeeklySchedule(User $student, ?int $termId = null): array
    {
        $classes = $this->getEnrolledClasses($student, $termId);
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

        $schedule = [];
        foreach ($days as $day) {
            $schedule[$day] = $classes->filter(function ($class) use ($day) {
                return strcasecmp(trim($class->schedule_day ?? ''), $day) === 0;
            })->sortBy('start_time')->values();
        }

        return $schedule;
    }

    /**
     * Get weekly timetable grid data with time slots (07:00 to 18:00).
     */
    public function getWeeklyTimetableGrid(User $student, ?int $termId = null): array
    {
        $classes = $this->getEnrolledClasses($student, $termId);
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $timeSlots = [];

        // Slots from 07:00 to 17:00 (1-hour intervals)
        for ($hour = 7; $hour <= 17; $hour++) {
            $timeSlots[] = sprintf('%02d:00', $hour);
        }

        $grid = [];
        foreach ($days as $day) {
            $grid[$day] = [];
        }

        foreach ($classes as $class) {
            $day = ucfirst(strtolower(trim($class->schedule_day ?? 'Senin')));
            if (! isset($grid[$day])) {
                continue;
            }

            $start = Carbon::parse($class->start_time ?? '08:00');
            $end = Carbon::parse($class->end_time ?? '10:00');
            $startHour = $start->hour + ($start->minute / 60);
            $endHour = $end->hour + ($end->minute / 60);
            $duration = max(1, $endHour - $startHour);

            $grid[$day][] = [
                'class' => $class,
                'start_time' => $start->format('H:i'),
                'end_time' => $end->format('H:i'),
                'start_hour' => $startHour,
                'duration' => $duration,
                'top_offset' => max(0, ($startHour - 7) * 60), // pixels or grid units
                'height' => $duration * 60,
            ];
        }

        return [
            'days' => $days,
            'time_slots' => $timeSlots,
            'grid' => $grid,
        ];
    }

    /**
     * Get events for a specific month (recurring lectures, assignments, quizzes, academic events).
     */
    public function getMonthlyEvents(User $student, int $year, int $month): array
    {
        $classes = $this->getEnrolledClasses($student);
        $classIds = $classes->pluck('id')->all();

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $events = [];

        // 1. Weekly Class Lectures recurring across the month
        $dayMap = [
            'senin' => Carbon::MONDAY,
            'selasa' => Carbon::TUESDAY,
            'rabu' => Carbon::WEDNESDAY,
            'kamis' => Carbon::THURSDAY,
            'jumat' => Carbon::FRIDAY,
            'sabtu' => Carbon::SATURDAY,
            'minggu' => Carbon::SUNDAY,
        ];

        foreach ($classes as $class) {
            $dayName = strtolower(trim($class->schedule_day ?? ''));
            if (! isset($dayMap[$dayName])) {
                continue;
            }

            $targetCarbonDay = $dayMap[$dayName];
            $current = $startDate->copy();

            // Find first occurrence in month
            while ($current->dayOfWeek !== $targetCarbonDay && $current->lte($endDate)) {
                $current->addDay();
            }

            while ($current->lte($endDate)) {
                $dateStr = $current->format('Y-m-d');
                $events[$dateStr][] = [
                    'id' => 'lecture-'.$class->id.'-'.$dateStr,
                    'title' => $class->course?->code.' — '.$class->course?->name,
                    'short_title' => $class->course?->code,
                    'time' => $this->formatClassTime($class->start_time, $class->end_time),
                    'type' => 'lecture',
                    'color' => $class->color_theme['badge'],
                    'border_color' => $class->color_theme['border'],
                    'bg_color' => $class->color_theme['bg'],
                    'text_color' => $class->color_theme['text'],
                    'room' => $class->room ?? 'Lab Komputer',
                    'lecturer' => $class->lecturer?->name ?? 'Dosen Pengampu',
                    'url' => route('app.courses.classroom', $class),
                ];

                $current->addWeek();
            }
        }

        // 2. Assignment Deadlines
        $assignments = Assignment::with('academicClass.course')
            ->whereIn('class_id', $classIds)
            ->whereBetween('due_at', [$startDate, $endDate])
            ->get();

        foreach ($assignments as $task) {
            $dateStr = Carbon::parse($task->due_at)->format('Y-m-d');
            $events[$dateStr][] = [
                'id' => 'task-'.$task->id,
                'title' => 'Tenggat: '.$task->title,
                'short_title' => 'Tugas: '.($task->academicClass?->course?->code ?? 'MK'),
                'time' => Carbon::parse($task->due_at)->format('H:i').' WIB',
                'type' => 'assignment',
                'color' => 'bg-amber-100 text-amber-800',
                'border_color' => 'border-amber-300',
                'bg_color' => 'bg-amber-50',
                'text_color' => 'text-amber-800',
                'room' => 'Online Submission',
                'lecturer' => $task->academicClass?->course?->name ?? 'Mata Kuliah',
                'url' => route('assignments.submit_form', $task),
            ];
        }

        // 3. Quizzes / Exams
        $quizzes = Quiz::with('academicClass.course')
            ->whereIn('class_id', $classIds)
            ->get();

        foreach ($quizzes as $quiz) {
            // If quiz has dates or placeholder in current month
            $quizDate = $startDate->copy()->addDays(min(20, $quiz->id * 4));
            if ($quizDate->between($startDate, $endDate)) {
                $dateStr = $quizDate->format('Y-m-d');
                $events[$dateStr][] = [
                    'id' => 'quiz-'.$quiz->id,
                    'title' => 'Ujian: '.$quiz->title,
                    'short_title' => 'Kuis: '.($quiz->academicClass?->course?->code ?? 'CBT'),
                    'time' => '09:00 - 11:00 WIB',
                    'type' => 'quiz',
                    'color' => 'bg-purple-100 text-purple-800',
                    'border_color' => 'border-purple-300',
                    'bg_color' => 'bg-purple-50',
                    'text_color' => 'text-purple-800',
                    'room' => 'Ruang CBT Kampus',
                    'lecturer' => $quiz->academicClass?->course?->name ?? 'Ujian Online',
                    'url' => route('quizzes.attempt', $quiz),
                ];
            }
        }

        return $events;
    }

    /**
     * Generate standard RFC 5545 iCalendar stream for calendar apps (Google Calendar, Apple, Outlook).
     */
    public function generateIcs(User $student, ?int $classId = null): string
    {
        $classes = $this->getEnrolledClasses($student);
        if ($classId) {
            $classes = $classes->where('id', $classId);
        }

        $term = $this->getActiveTerm();
        $termEndDate = $term?->end_date ? Carbon::parse($term->end_date) : now()->addMonths(4);
        $untilStr = gmdate('Ymd\T235959\Z', $termEndDate->timestamp);

        $dayRruleMap = [
            'senin' => 'MO',
            'selasa' => 'TU',
            'rabu' => 'WE',
            'kamis' => 'TH',
            'jumat' => 'FR',
            'sabtu' => 'SA',
            'minggu' => 'SU',
        ];

        $vEvents = [];

        foreach ($classes as $class) {
            $dayName = strtolower(trim($class->schedule_day ?? 'senin'));
            $byDay = $dayRruleMap[$dayName] ?? 'MO';

            $start = Carbon::parse($class->start_time ?? '08:00:00');
            $end = Carbon::parse($class->end_time ?? '10:00:00');

            $dayCarbonMap = [
                'senin' => Carbon::MONDAY,
                'selasa' => Carbon::TUESDAY,
                'rabu' => Carbon::WEDNESDAY,
                'kamis' => Carbon::THURSDAY,
                'jumat' => Carbon::FRIDAY,
                'sabtu' => Carbon::SATURDAY,
                'minggu' => Carbon::SUNDAY,
            ];
            $targetDay = $dayCarbonMap[$dayName] ?? Carbon::MONDAY;
            $nextDate = now()->next($targetDay);
            $dtStart = $nextDate->copy()->setTime($start->hour, $start->minute);
            $dtEnd = $nextDate->copy()->setTime($end->hour, $end->minute);

            $course = $class->course?->name ?? 'Mata Kuliah';
            $code = $class->course?->code ?? 'MK';
            $lecturer = $class->lecturer?->name ?? 'Dosen Pengampu';
            $room = $class->room ?? 'Ruang Kuliah';
            $credits = $class->course?->credits ?? 3;

            $vEvents[] = implode("\r\n", [
                'BEGIN:VEVENT',
                'UID:arunika-schedule-'.$class->id.'-'.($class->ulid ?? $class->id).'@unus.ac.id',
                'DTSTAMP:'.gmdate('Ymd\THis\Z'),
                'DTSTART;TZID=Asia/Jakarta:'.$dtStart->format('Ymd\THis'),
                'DTEND;TZID=Asia/Jakarta:'.$dtEnd->format('Ymd\THis'),
                'RRULE:FREQ=WEEKLY;BYDAY='.$byDay.';UNTIL='.$untilStr,
                'SUMMARY:'.$code.' — '.$course,
                'DESCRIPTION:'.addcslashes("Mata Kuliah: {$course}\nKode: {$code}\nSKS: {$credits} SKS\nDosen: {$lecturer}\nRuang: {$room}\nKelas: {$class->section}", "\n"),
                'LOCATION:'.$room,
                'STATUS:CONFIRMED',
                'END:VEVENT',
            ]);
        }

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Arunika Learning OS//Jadwal Kuliah Mahasiswa//ID',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:Jadwal Kuliah Arunika — '.$student->name,
            'X-WR-TIMEZONE:Asia/Jakarta',
            implode("\r\n", $vEvents),
            'END:VCALENDAR',
        ];

        return implode("\r\n", $lines);
    }

    /**
     * Format start and end time to human-readable string.
     */
    protected function formatClassTime(?string $start, ?string $end): string
    {
        if (! $start || ! $end) {
            return '08:00 – 10:00 WIB';
        }

        $startTime = Carbon::parse($start)->format('H:i');
        $endTime = Carbon::parse($end)->format('H:i');

        return "{$startTime} – {$endTime} WIB";
    }

    /**
     * Calculate duration in minutes between start and end time.
     */
    protected function calculateDuration(?string $start, ?string $end): int
    {
        if (! $start || ! $end) {
            return 120;
        }

        $startCarbon = Carbon::parse($start);
        $endCarbon = Carbon::parse($end);

        return abs($endCarbon->diffInMinutes($startCarbon));
    }

    /**
     * Color theme mapped by course code.
     */
    protected function getCourseColor(?string $code): array
    {
        $map = [
            'IF2101' => [
                'bg' => 'bg-blue-50',
                'border' => 'border-blue-200',
                'text' => 'text-[#2563EB]',
                'badge' => 'bg-blue-100 text-blue-800',
                'dot' => 'bg-[#2563EB]',
            ],
            'IF2102' => [
                'bg' => 'bg-emerald-50',
                'border' => 'border-emerald-200',
                'text' => 'text-emerald-700',
                'badge' => 'bg-emerald-100 text-emerald-800',
                'dot' => 'bg-emerald-600',
            ],
            'IF2103' => [
                'bg' => 'bg-indigo-50',
                'border' => 'border-indigo-200',
                'text' => 'text-indigo-700',
                'badge' => 'bg-indigo-100 text-indigo-800',
                'dot' => 'bg-indigo-600',
            ],
            'IF2104' => [
                'bg' => 'bg-amber-50',
                'border' => 'border-amber-200',
                'text' => 'text-amber-700',
                'badge' => 'bg-amber-100 text-amber-800',
                'dot' => 'bg-amber-600',
            ],
            'IF2105' => [
                'bg' => 'bg-purple-50',
                'border' => 'border-purple-200',
                'text' => 'text-purple-700',
                'badge' => 'bg-purple-100 text-purple-800',
                'dot' => 'bg-purple-600',
            ],
            'IF2106' => [
                'bg' => 'bg-slate-50',
                'border' => 'border-slate-200',
                'text' => 'text-slate-600',
                'badge' => 'bg-slate-100 text-slate-700',
                'dot' => 'bg-slate-400',
            ],
        ];

        return $map[$code] ?? [
            'bg' => 'bg-blue-50',
            'border' => 'border-blue-200',
            'text' => 'text-[#2563EB]',
            'badge' => 'bg-blue-100 text-blue-800',
            'dot' => 'bg-[#2563EB]',
        ];
    }
}
