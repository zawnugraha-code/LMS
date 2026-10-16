<?php

namespace App\Services;

use App\Models\AcademicClass;
use App\Models\Assignment;
use App\Models\DiscussionThread;
use App\Models\Material;
use App\Models\User;

class SearchService
{
    /**
     * Search across courses, materials, assignments, and discussions.
     * Scoped strictly to courses the user is enrolled in.
     */
    public function search(string $query, User $user): array
    {
        $q = trim($query);
        if (strlen($q) < 2) {
            return [
                'courses' => [],
                'materials' => [],
                'assignments' => [],
                'discussions' => [],
                'total' => 0,
            ];
        }

        // Get enrolled class IDs
        $enrolledClassIds = AcademicClass::whereHas('enrollments', fn ($b) => $b->where('user_id', $user->id))
            ->pluck('id')
            ->toArray();

        // 1. Courses
        $coursesQuery = AcademicClass::with('course', 'lecturer')
            ->whereIn('id', $enrolledClassIds)
            ->whereHas('course', function ($b) use ($q) {
                $b->where('name', 'like', "%{$q}%")
                    ->orWhere('code', 'like', "%{$q}%");
            })
            ->take(4)
            ->get()
            ->map(fn ($c) => [
                'title' => $c->course->code.' — '.$c->course->name,
                'subtitle' => $c->lecturer?->name ?? 'Dosen Pengampu',
                'badge' => $c->course->credits.' SKS',
                'url' => route('app.courses'),
                'icon' => 'book-open',
            ]);

        $courses = $coursesQuery->toArray();

        if (str_contains(strtolower($q), 'jadwal') || str_contains(strtolower($q), 'kalender') || str_contains(strtolower($q), 'schedule')) {
            array_unshift($courses, [
                'title' => 'Jadwal Kuliah & Kalender Akademik',
                'subtitle' => 'Lihat matriks jadwal mingguan dan kalender semester',
                'badge' => 'Jadwal',
                'url' => route('app.schedule'),
                'icon' => 'calendar',
            ]);
        }

        // 2. Materials
        $materials = Material::with('meeting.academicClass.course')
            ->whereHas('meeting', fn ($b) => $b->whereIn('class_id', $enrolledClassIds))
            ->where('title', 'like', "%{$q}%")
            ->take(4)
            ->get()
            ->map(fn ($m) => [
                'title' => $m->title,
                'subtitle' => $m->meeting->academicClass->course->code.' • '.ucfirst($m->type),
                'badge' => 'Materi',
                'url' => route('app.materials'),
                'icon' => 'folder-open',
            ]);

        // 3. Assignments & Exams
        $assignments = Assignment::with('academicClass.course')
            ->whereIn('class_id', $enrolledClassIds)
            ->where('title', 'like', "%{$q}%")
            ->take(4)
            ->get()
            ->map(fn ($a) => [
                'title' => $a->title,
                'subtitle' => $a->academicClass->course->code.' • '.$a->academicClass->course->name,
                'badge' => 'Tugas',
                'url' => route('app.assignments'),
                'icon' => 'clipboard-list',
            ]);

        // 4. Discussions
        $discussions = DiscussionThread::with('category', 'academicClass.course')
            ->where(function ($b) use ($enrolledClassIds) {
                $b->whereIn('class_id', $enrolledClassIds)
                    ->orWhereNull('class_id');
            })
            ->where(function ($b) use ($q) {
                $b->where('title', 'like', "%{$q}%")
                    ->orWhere('content', 'like', "%{$q}%");
            })
            ->take(4)
            ->get()
            ->map(fn ($d) => [
                'title' => $d->title,
                'subtitle' => ($d->academicClass ? $d->academicClass->course->code.' • ' : '').$d->category->name,
                'badge' => 'Diskusi',
                'url' => route('app.discussions'),
                'icon' => 'message-circle',
            ]);

        $total = count($courses) + count($materials) + count($assignments) + count($discussions);

        return [
            'courses' => $courses,
            'materials' => $materials,
            'assignments' => $assignments,
            'discussions' => $discussions,
            'total' => $total,
        ];
    }
}
