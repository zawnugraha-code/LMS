<?php

namespace App\Services;

use App\Models\AcademicClass;
use App\Models\Term;
use App\Models\User;
use Illuminate\Support\Collection;

class CourseService
{
    /**
     * Get summary metrics for the courses page bar based on the active term.
     */
    public function getSummary(User $user, Term $term): array
    {
        $classes = AcademicClass::with(['course', 'enrollments'])
            ->where('term_id', $term->id)
            ->get();

        $enrolledClasses = $classes->filter(function ($class) use ($user) {
            return $class->enrollments->contains('user_id', $user->id);
        });

        $totalCourses = $classes->count();
        $totalCredits = $classes->sum(fn ($c) => $c->course?->credits ?? 3);

        // SKS Diambil: total credits from enrolled classes
        $creditsTaken = $enrolledClasses->sum(fn ($c) => $c->course?->credits ?? 3);

        // MK Aktif: enrolled and not locked
        $activeCoursesCount = $classes->filter(fn ($c) => ! $c->isLocked())->count();

        // MK Selesai: enrolled with completed status
        $completedCoursesCount = $enrolledClasses->filter(function ($c) use ($user) {
            $enrollment = $c->enrollments->firstWhere('user_id', $user->id);

            return $enrollment?->status === 'completed';
        })->count();

        return [
            'total_courses' => $totalCourses,
            'total_credits' => $totalCredits,
            'credits_taken' => $creditsTaken > 0 ? $creditsTaken : 12,
            'active_courses' => $activeCoursesCount,
            'completed_courses' => $completedCoursesCount,
        ];
    }

    /**
     * Get enriched course list for the term and student.
     */
    public function getCourses(User $user, Term $term, array $filters = []): Collection
    {
        $query = AcademicClass::with([
            'course.cpmks',
            'lecturer',
            'enrollments' => fn ($q) => $q->where('user_id', $user->id),
            'meetings.materials.progress' => fn ($q) => $q->where('user_id', $user->id),
            'meetings.attendanceSession.logs' => fn ($q) => $q->where('user_id', $user->id),
        ])
            ->where('term_id', $term->id)
            ->orderBy('id');

        $classes = $query->get();

        return $classes->map(function ($class) {
            $enrollment = $class->enrollments->first();
            $isLocked = $class->isLocked();

            // Total meetings (typically 14)
            $totalMeetings = $class->meetings->count() > 0 ? $class->meetings->count() : 14;

            // Completed meetings: from settings['progress_meetings'] or completed materials count
            if (isset($class->settings['progress_meetings']) && $class->settings['progress_meetings'] !== null) {
                $completedMeetings = (int) $class->settings['progress_meetings'];
            } else {
                $completedMeetings = 0;
                foreach ($class->meetings as $meeting) {
                    $hasCompleted = $meeting->materials->contains(function ($mat) {
                        $prog = $mat->progress->first();

                        return $prog && $prog->status === 'completed';
                    });
                    if ($hasCompleted) {
                        $completedMeetings++;
                    }
                }
            }

            $percentage = $totalMeetings > 0 ? (int) round(($completedMeetings / $totalMeetings) * 100) : 0;

            // Status label and badge
            if ($isLocked) {
                $status = 'upcoming';
                $badgeText = ($class->course?->credits ?? 3).' Akan Datang';
                $badgeVariant = 'accent';
            } elseif ($enrollment?->status === 'completed') {
                $status = 'completed';
                $badgeText = ($class->course?->credits ?? 3).' Selesai';
                $badgeVariant = 'success';
            } else {
                $status = 'active';
                // Follow design: either "3 Aktif" or "3 SKS"
                $badgeText = ($class->course?->credits ?? 3).' Aktif';
                $badgeVariant = 'success';
            }

            // Format schedule
            $scheduleText = '';
            if ($class->schedule_day && $class->start_time) {
                $start = substr($class->start_time, 0, 5);
                $end = substr($class->end_time ?? '', 0, 5);
                $scheduleText = "{$class->schedule_day}, {$start}".($end ? " - {$end}" : '').($class->room ? " • {$class->room}" : '');
            } else {
                $scheduleText = ($class->room ? $class->room : 'Belum Terjadwal').' • Jadwal menyusul';
            }

            return (object) [
                'id' => $class->id,
                'ulid' => $class->ulid,
                'model' => $class,
                'code' => $class->course?->code ?? 'IF2100',
                'name' => $class->course?->name ?? 'Mata Kuliah',
                'credits' => $class->course?->credits ?? 3,
                'lecturer_name' => $class->lecturer?->name ?? 'Dosen Pengampu',
                'schedule_text' => $scheduleText,
                'room' => $class->room,
                'schedule_day' => $class->schedule_day,
                'status' => $status,
                'is_locked' => $isLocked,
                'badge_text' => $badgeText,
                'badge_variant' => $badgeVariant,
                'progress_completed' => $completedMeetings,
                'progress_total' => $totalMeetings,
                'progress_percentage' => $percentage,
                'start_date' => $class->settings['start_date'] ?? null,
                'detail_url' => route('app.courses.show', $class),
                'classroom_url' => route('app.courses.classroom', $class),
            ];
        });
    }

    /**
     * Get distinct filter options for the course filter popover.
     */
    public function getFilterOptions(Term $term): array
    {
        $classes = AcademicClass::with(['lecturer'])->where('term_id', $term->id)->get();

        $lecturers = $classes->pluck('lecturer')->filter()->unique('id')->values();
        $days = $classes->pluck('schedule_day')->filter()->unique()->values();

        return [
            'lecturers' => $lecturers,
            'days' => $days,
            'statuses' => [
                ['value' => 'active', 'label' => 'Aktif'],
                ['value' => 'completed', 'label' => 'Selesai'],
                ['value' => 'upcoming', 'label' => 'Akan Datang'],
            ],
        ];
    }
}
