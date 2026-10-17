<?php

namespace App\Services;

use App\Helpers\ArunikaDate;
use App\Models\AcademicClass;
use App\Models\Material;
use App\Models\MaterialProgress;
use App\Models\Term;
use App\Models\User;
use App\Models\XpLedger;
use Illuminate\Support\Collection;

class MaterialService
{
    /**
     * Get enrolled classes for user in the given term.
     */
    public function getEnrolledClasses(User $user, ?Term $term = null): Collection
    {
        $query = AcademicClass::with(['course.curriculum.program', 'lecturer', 'term'])
            ->whereHas('enrollments', fn ($q) => $q->where('user_id', $user->id));

        if ($term) {
            $query->where('term_id', $term->id);
        }

        return $query->get();
    }

    /**
     * Resolve the active class from query params or default to first enrolled class (e.g. IF2101).
     */
    public function resolveActiveClass(User $user, ?Term $term = null, ?string $courseCode = null, ?string $classUlid = null): ?AcademicClass
    {
        $enrolledClasses = $this->getEnrolledClasses($user, $term);

        if ($classUlid) {
            $found = $enrolledClasses->firstWhere('ulid', $classUlid);
            if ($found) {
                return $found;
            }
        }

        if ($courseCode) {
            $found = $enrolledClasses->first(fn ($c) => strcasecmp($c->course->code, $courseCode) === 0);
            if ($found) {
                return $found;
            }
        }

        // Default to IF2101 if enrolled, or the first active class
        $pbo = $enrolledClasses->first(fn ($c) => $c->course->code === 'IF2101');

        return $pbo ?? $enrolledClasses->first();
    }

    /**
     * Compute summary metrics for the given class and user.
     */
    public function getSummary(User $user, AcademicClass $class): array
    {
        $materials = Material::with('meeting')
            ->where('status', 'published')
            ->whereHas('meeting', fn ($q) => $q->where('class_id', $class->id))
            ->get();
        $total = $materials->count();

        if ($total === 0) {
            return [
                'total_materials' => 0,
                'completed_materials' => 0,
                'in_progress_materials' => 0,
                'locked_materials' => 0,
                'progress_percentage' => 0.0,
            ];
        }

        $progresses = MaterialProgress::where('user_id', $user->id)
            ->whereIn('material_id', $materials->pluck('id'))
            ->get()
            ->keyBy('material_id');

        $isClassLocked = $class->isLocked();
        $completed = 0;
        $inProgress = 0;
        $locked = 0;

        foreach ($materials as $m) {
            $p = $progresses->get($m->id);
            $status = $p?->status ?? 'not_started';

            if ($isClassLocked) {
                $locked++;
            } elseif ($status === 'completed') {
                $completed++;
            } elseif ($status === 'in_progress' || ($p && $p->watch_percentage > 0)) {
                $inProgress++;
            } elseif ($status === 'locked') {
                $locked++;
            } else {
                // If not started, check if it's considered in progress or locked based on meeting order
                if ($m->meeting?->week > 10) {
                    $locked++;
                } else {
                    $inProgress++;
                }
            }
        }

        $percentage = $total > 0 ? round(($completed / $total) * 100, 1) : 0.0;

        return [
            'total_materials' => $total,
            'completed_materials' => $completed,
            'in_progress_materials' => $inProgress,
            'locked_materials' => $locked,
            'progress_percentage' => $percentage,
        ];
    }

    /**
     * Get chapters and materials grouped by chapter for the class.
     */
    public function getChapters(User $user, AcademicClass $class, array $filters = []): array
    {
        $class->loadMissing(['course.curriculum.program', 'lecturer']);

        $meetings = $class->meetings()
            ->with([
                'materials' => fn ($q) => $q->where('status', 'published'),
                'materials.progress' => fn ($q) => $q->where('user_id', $user->id),
            ])
            ->orderBy('week')
            ->get();

        $isClassLocked = $class->isLocked();
        $courseCode = $class->course?->code ?? '';

        // Chapter definitions based on course or standard 14 meetings
        $chapterDefs = $this->getChapterDefinitions($courseCode);

        $chapters = [];
        foreach ($chapterDefs as $cIdx => $def) {
            $chapterMeetings = $meetings->filter(fn ($m) => $m->week >= $def['from_week'] && $m->week <= $def['to_week']);

            $chapterMaterials = [];
            $chapterCompleted = 0;
            $chapterTotal = 0;

            foreach ($chapterMeetings as $meeting) {
                foreach ($meeting->materials as $mat) {
                    $chapterTotal++;
                    $progress = $mat->progress->first();
                    $rawStatus = $progress?->status ?? 'not_started';
                    $watchPct = (int) ($progress?->watch_percentage ?? 0);

                    // Compute display status
                    if ($isClassLocked || $rawStatus === 'locked' || ($meeting->week > 11 && $chapterCompleted === 0)) {
                        $displayStatus = 'Terkunci';
                        $statusClass = 'locked';
                        $isItemLocked = true;
                    } elseif ($rawStatus === 'completed' || $watchPct >= 90) {
                        $displayStatus = 'Selesai';
                        $statusClass = 'completed';
                        $isItemLocked = false;
                        $chapterCompleted++;
                    } else {
                        $displayStatus = 'Dalam Proses';
                        $statusClass = 'in_progress';
                        $isItemLocked = false;
                    }

                    $typeKey = strtolower($mat->type);
                    $typeInfo = $this->mapTypeInfo($typeKey);

                    // Compute item code (e.g. 1.1, 1.2, 2.1)
                    $itemCode = ($cIdx + 1).'.'.count($chapterMaterials) + 1;

                    $itemData = [
                        'id' => $mat->id,
                        'ulid' => $mat->ulid,
                        'code' => $itemCode,
                        'title' => $mat->title,
                        'type' => $typeInfo['label'],
                        'type_key' => $typeInfo['key'],
                        'icon' => $typeInfo['icon'],
                        'duration' => $mat->content_duration ?? '45 menit',
                        'date' => ArunikaDate::format($mat->created_at, 'd M Y'),
                        'status' => $displayStatus,
                        'status_class' => $statusClass,
                        'is_locked' => $isItemLocked,
                        'watch_percentage' => $watchPct,
                        'description' => $mat->content ?? 'Materi perkuliahan resmi dari dosen pengampu.',
                        'file_url' => $mat->file_path ? asset($mat->file_path) : '#',
                        'lecturer' => $class->lecturer?->name ?? 'Dosen Pengampu',
                        'week' => $meeting->week,
                    ];

                    // Apply filters if provided
                    if (! empty($filters['type']) && $filters['type'] !== 'all') {
                        if ($filters['type'] !== $typeInfo['key']) {
                            continue;
                        }
                    }

                    if (! empty($filters['status']) && $filters['status'] !== 'all') {
                        if (strcasecmp($filters['status'], $statusClass) !== 0 && strcasecmp($filters['status'], $displayStatus) !== 0) {
                            continue;
                        }
                    }

                    if (! empty($filters['query'])) {
                        $q = strtolower(trim($filters['query']));
                        if (! str_contains(strtolower($mat->title), $q) && ! str_contains(strtolower($itemCode), $q)) {
                            continue;
                        }
                    }

                    $chapterMaterials[] = $itemData;
                }
            }

            $chapters[] = [
                'index' => $cIdx + 1,
                'title' => $def['title'],
                'description' => $def['description'],
                'total_count' => $chapterTotal,
                'completed_count' => $chapterCompleted,
                'is_open' => $cIdx < 2, // Bab 1 and Bab 2 open by default, Bab 3 collapsed
                'materials' => $chapterMaterials,
            ];
        }

        return $chapters;
    }

    /**
     * Chapter definitions per course or default academic grouping.
     */
    protected function getChapterDefinitions(string $courseCode): array
    {
        if ($courseCode === 'IF2101') {
            return [
                [
                    'from_week' => 1,
                    'to_week' => 3,
                    'title' => 'Pengenalan dan Konsep Dasar OOP',
                    'description' => 'Materi dasar mengenai konsep Object Oriented Programming dan paradigma pemrograman.',
                ],
                [
                    'from_week' => 4,
                    'to_week' => 11,
                    'title' => 'Desain Pola GoF & Arsitektur Perangkat Lunak',
                    'description' => 'Studi mendalam pola desain Creational, Structural, dan Behavioral untuk sistem modular.',
                ],
                [
                    'from_week' => 12,
                    'to_week' => 14,
                    'title' => 'Framework dan Studi Kasus Lanjutan',
                    'description' => 'Penerapan OOP dan desain pola dalam pengembangan aplikasi nyata.',
                ],
            ];
        }

        return [
            [
                'from_week' => 1,
                'to_week' => 4,
                'title' => 'Fondasi & Konsep Dasar Perkuliahan',
                'description' => 'Cakupan teori pengantar, arsitektur dasar, dan studi pustaka minggu 1-4.',
            ],
            [
                'from_week' => 5,
                'to_week' => 11,
                'title' => 'Implementasi Praktis & Studi Kasus Terapan',
                'description' => 'Eksplorasi modul lanjutan, praktikum mandiri, dan integrasi studi kasus.',
            ],
            [
                'from_week' => 12,
                'to_week' => 14,
                'title' => 'Pengembangan Sistem Lanjutan & Evaluasi',
                'description' => 'Integrasi menyeluruh, evaluasi kompetensi CPMK, dan mini proyek akhir.',
            ],
        ];
    }

    /**
     * Map material type to label, icon, and filter key.
     */
    protected function mapTypeInfo(string $type): array
    {
        return match ($type) {
            'document', 'modul' => ['label' => 'Modul', 'key' => 'modul', 'icon' => 'file-text'],
            'video' => ['label' => 'Video', 'key' => 'video', 'icon' => 'video'],
            'assignment', 'tugas' => ['label' => 'Tugas', 'key' => 'tugas', 'icon' => 'clipboard-list'],
            'quiz', 'kuis' => ['label' => 'Kuis', 'key' => 'kuis', 'icon' => 'help-circle'],
            'reference', 'referensi', 'link' => ['label' => 'Referensi', 'key' => 'referensi', 'icon' => 'link'],
            default => ['label' => 'Modul', 'key' => 'modul', 'icon' => 'file-text'],
        };
    }

    /**
     * Get recent learning activities for the user.
     */
    public function getRecentActivities(User $user, ?AcademicClass $class = null): array
    {
        $activities = [];

        // 1. Completed material progress
        $completedProg = MaterialProgress::with('material.meeting.academicClass.course')
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->latest('completed_at')
            ->take(2)
            ->get();

        foreach ($completedProg as $p) {
            $cCode = $p->material?->meeting?->academicClass?->course?->code ?? 'IF2101';
            $activities[] = [
                'icon' => 'check-circle',
                'icon_bg' => 'bg-emerald-50 text-emerald-600',
                'title' => 'Anda menyelesaikan materi '.($p->material?->title ?? 'Konsep Dasar OOP'),
                'meta' => $cCode.' • '.ArunikaDate::format($p->completed_at ?? $p->updated_at, 'd M Y, H:i'),
            ];
        }

        // 2. Default canonical activities if fewer than 4
        if (count($activities) < 4) {
            $activities[] = [
                'icon' => 'play',
                'icon_bg' => 'bg-blue-50 text-blue-600',
                'title' => 'Video baru tersedia: Desain Pola',
                'meta' => 'IF2101 • '.ArunikaDate::format(now()->subDays(4)->setTime(10, 15), 'd M Y, H:i'),
            ];
            $activities[] = [
                'icon' => 'calendar',
                'icon_bg' => 'bg-purple-50 text-purple-600',
                'title' => 'Tugas Praktikum 1 telah dibuka',
                'meta' => 'IF2101 • '.ArunikaDate::format(now()->subDays(5)->setTime(16, 45), 'd M Y, H:i'),
            ];
            $activities[] = [
                'icon' => 'link',
                'icon_bg' => 'bg-blue-50 text-blue-600',
                'title' => 'Materi referensi: Clean Code',
                'meta' => 'IF2101 • '.ArunikaDate::format(now()->subDays(6)->setTime(9, 20), 'd M Y, H:i'),
            ];
        }

        return array_slice($activities, 0, 4);
    }

    /**
     * Mark material progress and award XP upon completion.
     */
    public function updateProgress(User $user, Material $material, int $watchPercentage): array
    {
        $watchPercentage = max(0, min(100, $watchPercentage));
        $isCompleted = ($watchPercentage >= 90);

        $progress = MaterialProgress::firstOrNew([
            'user_id' => $user->id,
            'material_id' => $material->id,
        ]);

        $alreadyCompleted = ($progress->status === 'completed');

        $progress->watch_percentage = max($progress->watch_percentage ?? 0, $watchPercentage);
        if ($isCompleted && ! $alreadyCompleted) {
            $progress->status = 'completed';
            $progress->completed_at = now();
        } elseif (! $alreadyCompleted && $watchPercentage > 0) {
            $progress->status = 'in_progress';
        }
        $progress->save();

        $xpAwarded = 0;
        if ($isCompleted && ! $alreadyCompleted) {
            $classId = $material->meeting?->class_id;
            XpLedger::create([
                'user_id' => $user->id,
                'class_id' => $classId,
                'amount' => 25,
                'source_type' => 'material_completed',
                'source_id' => $material->id,
                'description' => 'Menyelesaikan modul pembelajaran: '.$material->title,
            ]);
            $xpAwarded = 25;
        }

        return [
            'status' => $progress->status,
            'watch_percentage' => $progress->watch_percentage,
            'is_completed' => ($progress->status === 'completed'),
            'xp_awarded' => $xpAwarded,
            'total_xp' => $user->totalXp(),
        ];
    }
}
