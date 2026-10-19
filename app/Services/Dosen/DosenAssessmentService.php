<?php

namespace App\Services\Dosen;

use App\Helpers\NotificationHelper;
use App\Models\AcademicClass;
use App\Models\Assignment;
use App\Models\Term;
use App\Models\User;
use App\Notifications\AssessmentPublishedNotification;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class DosenAssessmentService
{
    /**
     * Get paginated assessments for the lecturer with filters.
     */
    public function getAssessments(User $dosen, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Assignment::with(['academicClass.course', 'submissions'])
            ->whereHas('academicClass', fn ($q) => $q->where('lecturer_id', $dosen->id));

        // Filter: Jenis (tugas, kuis, uts, uas)
        if (! empty($filters['jenis']) && strtolower($filters['jenis']) !== 'semua') {
            $query->where('jenis', strtolower($filters['jenis']));
        }

        // Filter: Mata Kuliah / Course
        if (! empty($filters['course_id']) && $filters['course_id'] !== 'semua') {
            $query->whereHas('academicClass', fn ($q) => $q->where('course_id', $filters['course_id']));
        }

        // Filter: Kelas
        if (! empty($filters['class_id']) && $filters['class_id'] !== 'semua') {
            $query->where('class_id', $filters['class_id']);
        }

        // Filter: Status
        if (! empty($filters['status']) && $filters['status'] !== 'semua') {
            $query->where('status', strtolower($filters['status']));
        }

        // Search: Judul & Deskripsi
        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('academicClass.course', fn ($cq) => $cq->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
            });
        }

        return $query->orderBy('due_at', 'asc')
            ->orderBy('id', 'asc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Get 4 metric tiles for the assessment summary widget.
     */
    public function getSummary(User $dosen, Term $term): array
    {
        $base = Assignment::whereHas('academicClass', fn ($q) => $q->where('lecturer_id', $dosen->id)->where('term_id', $term->id));

        $total = (clone $base)->count();
        $tugas = (clone $base)->where('jenis', 'tugas')->count();
        $kuisUjian = (clone $base)->whereIn('jenis', ['kuis', 'uts', 'uas'])->count();
        $menungguPublikasi = (clone $base)->whereIn('status', ['draft', 'terjadwal'])->count();

        return [
            'total' => $total,
            'tugas' => $tugas,
            'kuis_ujian' => $kuisUjian,
            'menunggu_publikasi' => $menungguPublikasi,
        ];
    }

    /**
     * Get publication statistics for the donut chart.
     */
    public function getPublicationStats(User $dosen, Term $term): array
    {
        $base = Assignment::whereHas('academicClass', fn ($q) => $q->where('lecturer_id', $dosen->id)->where('term_id', $term->id));

        $published = (clone $base)->where('status', 'dipublikasikan')->count();
        $draft = (clone $base)->where('status', 'draft')->count();
        $scheduled = (clone $base)->where('status', 'terjadwal')->count();
        $archived = (clone $base)->where('status', 'arsip')->count();

        $total = $published + $draft + $scheduled + $archived;

        return [
            'total' => $total,
            'published' => $published,
            'draft' => $draft,
            'scheduled' => $scheduled,
            'archived' => $archived,
        ];
    }

    /**
     * Get closest upcoming assessments for the right widget.
     */
    public function getUpcomingAssessments(User $dosen, int $limit = 4): Collection
    {
        return Assignment::with(['academicClass.course'])
            ->whereHas('academicClass', fn ($q) => $q->where('lecturer_id', $dosen->id))
            ->orderBy('due_at', 'asc')
            ->take($limit)
            ->get();
    }

    /**
     * Create a new assessment.
     */
    public function storeAssessment(User $dosen, array $data): Assignment
    {
        // Verify class ownership
        $class = AcademicClass::where('id', $data['class_id'])
            ->where('lecturer_id', $dosen->id)
            ->firstOrFail();

        $status = $data['status'] ?? 'draft';
        $publishAt = null;

        if ($status === 'dipublikasikan') {
            $publishAt = now();
        } elseif ($status === 'terjadwal') {
            $publishAt = isset($data['publish_at']) ? Carbon::parse($data['publish_at']) : now()->addDay();
            // If publish_at is already in the past, auto-publish
            if ($publishAt->isPast()) {
                $status = 'dipublikasikan';
            }
        }

        $lampiranPath = null;
        if (isset($data['lampiran']) && $data['lampiran'] instanceof UploadedFile) {
            $lampiranPath = $data['lampiran']->store('assessments', 'private');
        }

        $assignment = Assignment::create([
            'class_id' => $class->id,
            'dosen_id' => $dosen->id,
            'title' => $data['title'],
            'jenis' => $data['jenis'] ?? 'tugas',
            'mode' => $data['mode'] ?? 'individu',
            'metode' => $data['metode'] ?? 'unggah_berkas',
            'description' => $data['description'] ?? '',
            'lampiran_path' => $lampiranPath,
            'max_score' => $data['max_score'] ?? 100,
            'mulai_at' => isset($data['mulai_at']) ? Carbon::parse($data['mulai_at']) : now(),
            'due_at' => Carbon::parse($data['due_at']),
            'publish_at' => $publishAt,
            'status' => $status,
        ]);

        // Send notification to students if published
        if ($status === 'dipublikasikan') {
            $this->notifyEnrolledStudents($assignment);
        }

        return $assignment;
    }

    /**
     * Update an existing assessment.
     */
    public function updateAssessment(Assignment $assignment, array $data): Assignment
    {
        $status = $data['status'] ?? $assignment->status;
        $publishAt = $assignment->publish_at;

        if ($status === 'dipublikasikan' && $assignment->status !== 'dipublikasikan') {
            $publishAt = now();
        } elseif ($status === 'terjadwal' && isset($data['publish_at'])) {
            $publishAt = Carbon::parse($data['publish_at']);
            if ($publishAt->isPast()) {
                $status = 'dipublikasikan';
            }
        }

        if (isset($data['lampiran']) && $data['lampiran'] instanceof UploadedFile) {
            if ($assignment->lampiran_path && Storage::disk('private')->exists($assignment->lampiran_path)) {
                Storage::disk('private')->delete($assignment->lampiran_path);
            } elseif ($assignment->lampiran_path && Storage::disk('public')->exists($assignment->lampiran_path)) {
                Storage::disk('public')->delete($assignment->lampiran_path);
            }
            $assignment->lampiran_path = $data['lampiran']->store('assessments', 'private');
        }

        $assignment->update([
            'title' => $data['title'] ?? $assignment->title,
            'jenis' => $data['jenis'] ?? $assignment->jenis,
            'mode' => $data['mode'] ?? $assignment->mode,
            'metode' => $data['metode'] ?? $assignment->metode,
            'description' => $data['description'] ?? $assignment->description,
            'max_score' => $data['max_score'] ?? $assignment->max_score,
            'mulai_at' => isset($data['mulai_at']) ? Carbon::parse($data['mulai_at']) : $assignment->mulai_at,
            'due_at' => isset($data['due_at']) ? Carbon::parse($data['due_at']) : $assignment->due_at,
            'publish_at' => $publishAt,
            'status' => $status,
        ]);

        if ($status === 'dipublikasikan' && $assignment->wasChanged('status')) {
            $this->notifyEnrolledStudents($assignment);
        }

        return $assignment;
    }

    /**
     * Publish an assessment immediately.
     */
    public function publishNow(Assignment $assignment): bool
    {
        $assignment->update([
            'status' => 'dipublikasikan',
            'publish_at' => now(),
        ]);

        $this->notifyEnrolledStudents($assignment);

        return true;
    }

    /**
     * Archive an assessment.
     */
    public function archive(Assignment $assignment): bool
    {
        return $assignment->update(['status' => 'arsip']);
    }

    /**
     * Delete an assessment.
     */
    public function delete(Assignment $assignment): bool
    {
        if ($assignment->submissions()->count() > 0) {
            return false;
        }

        if ($assignment->lampiran_path && Storage::disk('public')->exists($assignment->lampiran_path)) {
            Storage::disk('public')->delete($assignment->lampiran_path);
        }

        return (bool) $assignment->delete();
    }

    /**
     * Notify enrolled students about a published assessment.
     */
    protected function notifyEnrolledStudents(Assignment $assignment): void
    {
        $students = $assignment->academicClass?->students ?? collect();

        foreach ($students as $student) {
            if (NotificationHelper::shouldNotify($student, 'assessment_published')) {
                $student->notify(new AssessmentPublishedNotification($assignment));
            }
        }
    }
}
