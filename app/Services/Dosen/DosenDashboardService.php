<?php

namespace App\Services\Dosen;

use App\Models\AcademicClass;
use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Submission;
use App\Models\Term;
use App\Models\User;
use Illuminate\Support\Collection;

class DosenDashboardService
{
    /**
     * Compute 4 stat cards for the Dosen Dashboard.
     */
    public function getStats(User $dosen, Term $term): array
    {
        $classIds = AcademicClass::where('lecturer_id', $dosen->id)
            ->where('term_id', $term->id)
            ->where('status', 'aktif')
            ->pluck('id');

        // 1. Kelas Diampu
        $kelasCount = $classIds->count();

        // 2. Total Mahasiswa (Unik di kelas-kelas tsb)
        $totalMahasiswa = User::whereHas('enrollments', fn ($q) => $q->whereIn('class_id', $classIds)->aktif())
            ->distinct()
            ->count();

        // 3. Tugas Aktif: jenis 'tugas', status 'dipublikasikan', deadline >= now
        $now = now();
        $tugasAktif = Assignment::whereIn('class_id', $classIds)
            ->where('jenis', 'tugas')
            ->where('status', 'dipublikasikan')
            ->where('due_at', '>=', $now)
            ->count();

        // 4. Perlu Dinilai: submissions dengan score null pada kelas dosen
        $perluDinilai = Submission::whereNull('score')
            ->whereHas('assignment', fn ($q) => $q->whereIn('class_id', $classIds))
            ->count();

        return [
            'kelas_diampu' => $kelasCount,
            'total_mahasiswa' => $totalMahasiswa,
            'tugas_aktif' => $tugasAktif,
            'perlu_dinilai' => $perluDinilai,
        ];
    }

    /**
     * Get teaching schedule for today.
     */
    public function getTodaySchedule(User $dosen, Term $term): Collection
    {
        // Hari ini (misal: 'Senin')
        $dayName = now()->locale('id')->isoFormat('dddd');

        return AcademicClass::with(['course', 'meetings'])
            ->where('lecturer_id', $dosen->id)
            ->where('term_id', $term->id)
            ->where('status', 'aktif')
            ->where(function ($q) use ($dayName) {
                // Utamakan jadwal hari ini, atau fallback ke jadwal 'Senin' jika di luar hari kuliah
                $q->where('schedule_day', $dayName)
                    ->orWhere('schedule_day', 'Senin');
            })
            ->orderBy('start_time', 'asc')
            ->get()
            ->map(function ($class) {
                // Hitung pertemuan saat ini berdasarkan tanggal atau pertemuan terakhir
                $completedMeetings = $class->meetings
                    ->where('date', '<=', now()->toDateString())
                    ->count();
                $class->current_meeting_number = max(1, $completedMeetings);

                return $class;
            });
    }

    /**
     * Get recent submissions needing grading (score IS NULL).
     */
    public function getRecentPendingSubmissions(User $dosen, int $limit = 5): Collection
    {
        return Submission::with(['user', 'assignment.academicClass.course'])
            ->whereNull('score')
            ->whereHas('assignment.academicClass', fn ($q) => $q->where('lecturer_id', $dosen->id))
            ->orderBy('submitted_at', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Get assignments with closest upcoming deadlines.
     */
    public function getUpcomingDeadlines(User $dosen, int $limit = 3): Collection
    {
        $now = now();

        return Assignment::with(['academicClass.course'])
            ->whereHas('academicClass', fn ($q) => $q->where('lecturer_id', $dosen->id))
            ->where('status', 'dipublikasikan')
            ->where('due_at', '>=', $now)
            ->orderBy('due_at', 'asc')
            ->take($limit)
            ->get()
            ->map(function ($task) use ($now) {
                $diffDays = (int) ceil($now->diffInDays($task->due_at, false));
                if ($diffDays <= 0) {
                    $task->days_left_label = 'Hari ini';
                } else {
                    $task->days_left_label = "{$diffDays} hari lagi";
                }

                return $task;
            });
    }

    /**
     * Get recent class announcements.
     */
    public function getRecentAnnouncements(User $dosen, int $limit = 3): Collection
    {
        return Announcement::with('academicClass.course')
            ->whereNotNull('class_id')
            ->whereHas('academicClass', fn ($q) => $q->where('lecturer_id', $dosen->id))
            ->orderBy('published_at', 'desc')
            ->take($limit)
            ->get();
    }

    /**
     * Get global academic announcements (class_id IS NULL).
     */
    public function getAcademicAnnouncements(int $limit = 2): Collection
    {
        return Announcement::academic()
            ->orderBy('published_at', 'desc')
            ->take($limit)
            ->get();
    }
}
