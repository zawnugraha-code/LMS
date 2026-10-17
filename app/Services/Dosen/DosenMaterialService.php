<?php

namespace App\Services\Dosen;

use App\Models\AcademicClass;
use App\Models\Announcement;
use App\Models\Material;
use App\Models\Meeting;
use App\Models\Term;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DosenMaterialService
{
    /**
     * Get all active classes taught by the lecturer.
     */
    public function getClassesForLecturer(User $dosen, ?Term $term = null): Collection
    {
        $query = AcademicClass::with(['course', 'term', 'enrollments'])
            ->where('lecturer_id', $dosen->id);

        if ($term) {
            $query->where('term_id', $term->id);
        }

        return $query->orderBy('course_id')->get();
    }

    /**
     * Resolve the active class from query param or fallback to the first active class.
     */
    public function resolveActiveClass(User $dosen, Collection $classes, ?string $classId = null): ?AcademicClass
    {
        if ($classId && $classId !== 'semua') {
            $found = $classes->firstWhere('id', (int) $classId) ?? $classes->firstWhere('ulid', $classId);
            if ($found) {
                return $found;
            }
        }

        return $classes->first();
    }

    /**
     * Ensure meetings for weeks 1 through 16 exist for the class.
     */
    public function ensureMeetingsExist(AcademicClass $class): Collection
    {
        $existing = $class->meetings()->orderBy('week')->get()->keyBy('week');

        for ($w = 1; $w <= 16; $w++) {
            if (! $existing->has($w)) {
                $isUts = ($w === 8);
                $isUas = ($w === 16);
                $title = $isUts
                    ? 'Pertemuan 8 - Ujian Tengah Semester (UTS)'
                    : ($isUas ? 'Pertemuan 16 - Ujian Akhir Semester (UAS)' : "Pertemuan {$w} - Topik Perkuliahan Minggu {$w}");

                $meeting = Meeting::create([
                    'class_id' => $class->id,
                    'week' => $w,
                    'title' => $title,
                    'date' => Carbon::parse('2026-09-28')->addWeeks($w - 1)->toDateString(),
                    'start_time' => $class->start_time ?? '08:00:00',
                    'end_time' => $class->end_time ?? '10:00:00',
                    'is_published' => true,
                ]);
                $existing->put($w, $meeting);
            }
        }

        return $existing->sortBy('week')->values();
    }

    /**
     * Get meetings with materials grouped and filtered.
     */
    public function getMaterialsByMeeting(AcademicClass $class, array $filters = []): Collection
    {
        $meetings = $this->ensureMeetingsExist($class);

        // Load all materials for this class
        $materialsQuery = Material::where(function ($q) use ($class) {
            $q->where('class_id', $class->id)
                ->orWhereHas('meeting', fn ($mq) => $mq->where('class_id', $class->id));
        });

        // Filter Type
        if (! empty($filters['type']) && strtolower($filters['type']) !== 'semua' && strtolower($filters['type']) !== 'all') {
            $materialsQuery->where('type', strtolower($filters['type']));
        }

        // Filter Status (published / draft)
        if (! empty($filters['status']) && strtolower($filters['status']) !== 'semua' && strtolower($filters['status']) !== 'all') {
            $materialsQuery->where('status', strtolower($filters['status']));
        }

        // Search Query
        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $materialsQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $allMaterials = $materialsQuery->orderBy('order')->orderBy('created_at', 'desc')->get();
        $materialsByMeetingId = $allMaterials->groupBy('meeting_id');

        return $meetings->map(function ($meeting) use ($materialsByMeetingId) {
            $meeting->meeting_materials = $materialsByMeetingId->get($meeting->id, collect());

            return $meeting;
        });
    }

    /**
     * Compute summary metrics for the given class.
     */
    public function getMaterialSummary(AcademicClass $class): array
    {
        $allMaterials = Material::where(function ($q) use ($class) {
            $q->where('class_id', $class->id)
                ->orWhereHas('meeting', fn ($mq) => $mq->where('class_id', $class->id));
        })->get();

        $total = $allMaterials->count();
        $published = $allMaterials->where('status', 'published')->count();
        $draft = $allMaterials->where('status', 'draft')->count();

        $meetingsWithMaterials = $allMaterials->pluck('meeting_id')->unique()->filter()->count();

        return [
            'total' => $total,
            'published' => $published,
            'draft' => $draft,
            'meetings_filled' => $meetingsWithMaterials,
            'total_meetings' => 16,
            'percent_published' => $total > 0 ? (int) round(($published / $total) * 100) : 0,
        ];
    }

    /**
     * Store new material for a class meeting.
     */
    public function storeMaterial(User $dosen, array $data, ?UploadedFile $file = null): Material
    {
        $class = AcademicClass::findOrFail($data['class_id']);
        $week = (int) ($data['week'] ?? 1);

        $meeting = Meeting::firstOrCreate(
            ['class_id' => $class->id, 'week' => $week],
            [
                'title' => "Pertemuan {$week} - Topik Perkuliahan Minggu {$week}",
                'date' => Carbon::parse('2026-09-28')->addWeeks($week - 1)->toDateString(),
                'start_time' => $class->start_time ?? '08:00:00',
                'end_time' => $class->end_time ?? '10:00:00',
                'is_published' => true,
            ]
        );

        $filePath = null;
        if ($file) {
            $fileName = Str::slug($data['title']).'-'.time().'.'.$file->getClientOriginalExtension();
            $filePath = $file->storeAs('materials', $fileName, 'public');
        } elseif (! empty($data['file_path'])) {
            $filePath = $data['file_path'];
        }

        $maxOrder = Material::where('meeting_id', $meeting->id)->max('order') ?? 0;

        return Material::create([
            'meeting_id' => $meeting->id,
            'class_id' => $class->id,
            'title' => $data['title'],
            'type' => strtolower($data['type'] ?? 'pdf'),
            'content' => $data['content'] ?? null,
            'file_path' => $filePath,
            'external_url' => $data['external_url'] ?? null,
            'is_downloadable' => isset($data['is_downloadable']) ? (bool) $data['is_downloadable'] : true,
            'status' => strtolower($data['status'] ?? 'published'),
            'uploaded_by' => $dosen->id,
            'order' => $maxOrder + 1,
        ]);
    }

    /**
     * Update an existing material.
     */
    public function updateMaterial(Material $material, array $data, ?UploadedFile $file = null): Material
    {
        $updates = [
            'title' => $data['title'] ?? $material->title,
            'type' => strtolower($data['type'] ?? $material->type),
            'content' => $data['content'] ?? $material->content,
            'external_url' => $data['external_url'] ?? $material->external_url,
            'status' => strtolower($data['status'] ?? $material->status),
            'is_downloadable' => isset($data['is_downloadable']) ? (bool) $data['is_downloadable'] : $material->is_downloadable,
        ];

        // Move to different meeting if week changed
        if (! empty($data['week'])) {
            $classId = $material->class_id ?? $material->meeting?->class_id;
            if ($classId) {
                $newMeeting = Meeting::firstOrCreate(
                    ['class_id' => $classId, 'week' => (int) $data['week']],
                    [
                        'title' => "Pertemuan {$data['week']} - Topik Perkuliahan Minggu {$data['week']}",
                        'date' => Carbon::parse('2026-09-28')->addWeeks((int) $data['week'] - 1)->toDateString(),
                        'start_time' => '08:00:00',
                        'end_time' => '10:00:00',
                        'is_published' => true,
                    ]
                );
                $updates['meeting_id'] = $newMeeting->id;
            }
        }

        // Handle replacement file
        if ($file) {
            if ($material->file_path && Storage::disk('public')->exists($material->file_path)) {
                Storage::disk('public')->delete($material->file_path);
            }
            $fileName = Str::slug($updates['title']).'-'.time().'.'.$file->getClientOriginalExtension();
            $updates['file_path'] = $file->storeAs('materials', $fileName, 'public');
        }

        $material->update($updates);

        return $material;
    }

    /**
     * Toggle status between published and draft.
     */
    public function togglePublish(Material $material): Material
    {
        $newStatus = $material->status === 'published' ? 'draft' : 'published';
        $material->update(['status' => $newStatus]);

        return $material;
    }

    /**
     * Delete material and its physical storage file.
     */
    public function deleteMaterial(Material $material): bool
    {
        if ($material->file_path && Storage::disk('public')->exists($material->file_path)) {
            Storage::disk('public')->delete($material->file_path);
        }

        return (bool) $material->delete();
    }

    /**
     * Get class and academic announcements for the lecturer view.
     */
    public function getAnnouncementsForClass(AcademicClass $class, User $dosen): Collection
    {
        return Announcement::with(['author', 'academicClass'])
            ->where(function ($q) use ($class, $dosen) {
                $q->where('class_id', $class->id)
                    ->orWhere(function ($sq) use ($dosen) {
                        $sq->whereNull('class_id')->where('author_id', $dosen->id);
                    })
                    ->orWhereNull('class_id');
            })
            ->latest('published_at')
            ->get();
    }

    /**
     * Create an announcement.
     */
    public function storeAnnouncement(User $dosen, array $data): Announcement
    {
        return Announcement::create([
            'class_id' => ! empty($data['class_id']) && $data['class_id'] !== 'semua' ? (int) $data['class_id'] : null,
            'author_id' => $dosen->id,
            'title' => $data['title'],
            'content' => $data['content'],
            'published_at' => now(),
        ]);
    }

    /**
     * Delete an announcement.
     */
    public function deleteAnnouncement(Announcement $announcement, User $dosen): bool
    {
        return (bool) $announcement->delete();
    }
}
