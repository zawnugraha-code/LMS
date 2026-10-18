<?php

namespace App\Services\Admin;

use App\Models\AcademicClass;
use App\Models\Course;
use App\Models\CourseAssessmentComponent;
use App\Models\Meeting;
use App\Models\Term;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ClassService
{
    /**
     * Peta nama hari Indonesia ke konstanta Carbon.
     */
    protected const DAY_MAP = [
        'senin' => Carbon::MONDAY,
        'selasa' => Carbon::TUESDAY,
        'rabu' => Carbon::WEDNESDAY,
        'kamis' => Carbon::THURSDAY,
        'jumat' => Carbon::FRIDAY,
        'sabtu' => Carbon::SATURDAY,
        'minggu' => Carbon::SUNDAY,
    ];

    /**
     * Dapatkan daftar kelas dengan filter komprehensif.
     */
    public function getClassesList(
        ?string $status = null,
        ?int $termId = null,
        ?int $courseId = null,
        ?int $lecturerId = null,
        ?string $search = null
    ): Collection {
        return AcademicClass::query()
            ->with(['course.curriculum.program', 'term', 'lecturer', 'activeEnrollments'])
            ->withCount(['activeEnrollments', 'meetings', 'assignments'])
            ->when($status && $status !== 'semua', function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->when($termId, function ($q) use ($termId) {
                $q->where('term_id', $termId);
            })
            ->when($courseId, function ($q) use ($courseId) {
                $q->where('course_id', $courseId);
            })
            ->when($lecturerId, function ($q) use ($lecturerId) {
                $q->where('lecturer_id', $lecturerId);
            })
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('section', 'like', "%{$search}%")
                        ->orWhere('room', 'like', "%{$search}%")
                        ->orWhereHas('course', function ($cq) use ($search) {
                            $cq->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%");
                        })
                        ->orWhereHas('lecturer', function ($lq) use ($search) {
                            $lq->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('term_id', 'desc')
            ->orderBy('course_id')
            ->orderBy('section')
            ->get();
    }

    /**
     * Dapatkan metrik statistik ringkasan kelas dan kapasitas.
     */
    public function getClassSummary(?int $termId = null): array
    {
        $classes = AcademicClass::query()
            ->with(['activeEnrollments'])
            ->when($termId, fn ($q) => $q->where('term_id', $termId))
            ->get();

        $totalClasses = $classes->count();
        $activeClasses = $classes->where('status', 'aktif')->count();
        $finishedClasses = $classes->where('status', 'selesai')->count();

        $totalCapacity = (int) $classes->sum('capacity');
        $totalEnrolled = 0;

        foreach ($classes as $c) {
            $totalEnrolled += $c->activeEnrollments->count();
        }

        $capacityPercentage = $totalCapacity > 0 ? (int) round(($totalEnrolled / $totalCapacity) * 100) : 0;
        $remainingCapacity = max(0, $totalCapacity - $totalEnrolled);
        $remainingPercentage = max(0, 100 - $capacityPercentage);

        return [
            'totalClasses' => $totalClasses,
            'activeClasses' => $activeClasses,
            'finishedClasses' => $finishedClasses,
            'totalCapacity' => $totalCapacity,
            'totalEnrolled' => $totalEnrolled,
            'capacityPercentage' => $capacityPercentage,
            'remainingCapacity' => $remainingCapacity,
            'remainingPercentage' => $remainingPercentage,
        ];
    }

    /**
     * Periksa bentrok jadwal ruangan dan jadwal dosen pengampu.
     */
    public function checkScheduleConflict(
        ?int $ignoreClassId,
        int $termId,
        string $scheduleDay,
        string $startTime,
        string $endTime,
        ?string $room,
        ?int $lecturerId
    ): array {
        $startTime = substr($startTime, 0, 5);
        $endTime = substr($endTime, 0, 5);

        $roomConflict = null;
        $lecturerConflict = null;

        // 1. Cek bentrok ruangan
        if (! empty($room)) {
            $conflictingRoom = AcademicClass::query()
                ->with(['course', 'lecturer'])
                ->where('term_id', $termId)
                ->where('schedule_day', $scheduleDay)
                ->where('room', $room)
                ->where('status', 'aktif')
                ->when($ignoreClassId, fn ($q) => $q->where('id', '!=', $ignoreClassId))
                ->where(function ($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime)
                        ->where('end_time', '>', $startTime);
                })
                ->first();

            if ($conflictingRoom) {
                $roomConflict = "Ruang '{$room}' sudah digunakan oleh kelas {$conflictingRoom->course->code} {$conflictingRoom->section} " .
                    "({$conflictingRoom->schedule_day}, {$conflictingRoom->start_time} - {$conflictingRoom->end_time}).";
            }
        }

        // 2. Cek bentrok dosen pengampu
        if (! empty($lecturerId)) {
            $conflictingLecturer = AcademicClass::query()
                ->with(['course'])
                ->where('term_id', $termId)
                ->where('schedule_day', $scheduleDay)
                ->where('lecturer_id', $lecturerId)
                ->where('status', 'aktif')
                ->when($ignoreClassId, fn ($q) => $q->where('id', '!=', $ignoreClassId))
                ->where(function ($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime)
                        ->where('end_time', '>', $startTime);
                })
                ->first();

            if ($conflictingLecturer) {
                $lecturerUser = User::find($lecturerId);
                $lecturerName = $lecturerUser?->name ?? 'Dosen';
                $lecturerConflict = "{$lecturerName} telah memiliki jadwal mengajar kelas {$conflictingLecturer->course->code} {$conflictingLecturer->section} " .
                    "pada hari dan jam yang sama ({$conflictingLecturer->start_time} - {$conflictingLecturer->end_time}).";
            }
        }

        return [
            'has_conflict' => ($roomConflict !== null || $lecturerConflict !== null),
            'room_conflict' => $roomConflict,
            'lecturer_conflict' => $lecturerConflict,
        ];
    }

    /**
     * Buat kelas baru lengkap dengan generasi 16 pertemuan dan komponen penilaian default.
     */
    public function createClass(array $data): AcademicClass
    {
        return DB::transaction(function () use ($data) {
            $section = strtoupper(trim($data['section']));
            $courseId = (int) $data['course_id'];
            $termId = (int) $data['term_id'];
            $lecturerId = ! empty($data['lecturer_id']) ? (int) $data['lecturer_id'] : null;

            // 1. Validasi keunikan seksi dalam satu semester dan mata kuliah
            $exists = AcademicClass::where('course_id', $courseId)
                ->where('term_id', $termId)
                ->where('section', $section)
                ->exists();

            if ($exists) {
                throw new InvalidArgumentException("Kelas/Seksi '{$section}' untuk mata kuliah ini sudah terdaftar pada semester yang dipilih.");
            }

            // 2. Cek bentrok jadwal jika data hari dan jam disediakan
            if (! empty($data['schedule_day']) && ! empty($data['start_time']) && ! empty($data['end_time'])) {
                $conflict = $this->checkScheduleConflict(
                    ignoreClassId: null,
                    termId: $termId,
                    scheduleDay: $data['schedule_day'],
                    startTime: $data['start_time'],
                    endTime: $data['end_time'],
                    room: $data['room'] ?? null,
                    lecturerId: $lecturerId
                );

                if ($conflict['has_conflict']) {
                    $msg = $conflict['room_conflict'] ?? $conflict['lecturer_conflict'];
                    throw new InvalidArgumentException("Bentrok jadwal terdeteksi: {$msg}");
                }
            }

            // 3. Simpan data kelas
            $class = AcademicClass::create([
                'course_id' => $courseId,
                'term_id' => $termId,
                'lecturer_id' => $lecturerId,
                'section' => $section,
                'room' => $data['room'] ?? null,
                'schedule_day' => $data['schedule_day'] ?? null,
                'start_time' => $data['start_time'] ?? null,
                'end_time' => $data['end_time'] ?? null,
                'capacity' => (int) ($data['capacity'] ?? 40),
                'status' => 'aktif',
            ]);

            // 4. Inisialisasi 16 pertemuan mingguan default
            $meetingsCount = (int) ($data['meetings_count'] ?? config('arunika.default_meetings_count', 16));
            $this->generateMeetingsForClass($class, $meetingsCount);

            // 5. Inisialisasi komponen penilaian default (Tugas 30%, Kuis 20%, UTS 25%, UAS 25% = 100%)
            $defaultComponents = config('arunika.assessment_components', [
                'tugas' => 30,
                'kuis' => 20,
                'uts' => 25,
                'uas' => 25,
            ]);

            foreach ($defaultComponents as $jenis => $bobot) {
                CourseAssessmentComponent::create([
                    'class_id' => $class->id,
                    'jenis' => $jenis,
                    'bobot' => $bobot,
                ]);
            }

            // 6. Catat log aktivitas
            $course = Course::find($courseId);
            $lecturer = $lecturerId ? User::find($lecturerId) : null;
            $lecturerName = $lecturer ? $lecturer->name : 'Belum Ditunjuk';

            ActivityLogger::log(
                aksi: 'buat_kelas',
                kategori: 'akademik',
                subjek: $class,
                deskripsi: "Membuka kelas baru: {$course->code} {$class->section} (Dosen: {$lecturerName}, Kapasitas: {$class->capacity})",
                properti: [
                    'class_id' => $class->id,
                    'course_code' => $course->code,
                    'section' => $class->section,
                    'lecturer_id' => $lecturerId,
                    'capacity' => $class->capacity,
                    'meetings_count' => $meetingsCount,
                ]
            );

            return $class;
        });
    }

    /**
     * Perbarui konfigurasi kelas dan jadwal.
     */
    public function updateClass(AcademicClass $class, array $data): AcademicClass
    {
        return DB::transaction(function () use ($class, $data) {
            $section = strtoupper(trim($data['section'] ?? $class->section));
            $courseId = isset($data['course_id']) ? (int) $data['course_id'] : $class->course_id;
            $termId = isset($data['term_id']) ? (int) $data['term_id'] : $class->term_id;
            $newLecturerId = array_key_exists('lecturer_id', $data) ? ($data['lecturer_id'] ? (int) $data['lecturer_id'] : null) : $class->lecturer_id;
            $oldLecturerId = $class->lecturer_id;

            // 1. Validasi keunikan seksi
            $exists = AcademicClass::where('course_id', $courseId)
                ->where('term_id', $termId)
                ->where('section', $section)
                ->where('id', '!=', $class->id)
                ->exists();

            if ($exists) {
                throw new InvalidArgumentException("Kelas/Seksi '{$section}' untuk mata kuliah ini sudah terdaftar pada semester tersebut.");
            }

            // 2. Cek bentrok jadwal jika hari dan jam diubah
            $scheduleDay = $data['schedule_day'] ?? $class->schedule_day;
            $startTime = $data['start_time'] ?? $class->start_time;
            $endTime = $data['end_time'] ?? $class->end_time;
            $room = array_key_exists('room', $data) ? $data['room'] : $class->room;

            if (! empty($scheduleDay) && ! empty($startTime) && ! empty($endTime)) {
                $conflict = $this->checkScheduleConflict(
                    ignoreClassId: $class->id,
                    termId: $termId,
                    scheduleDay: $scheduleDay,
                    startTime: $startTime,
                    endTime: $endTime,
                    room: $room,
                    lecturerId: $newLecturerId
                );

                if ($conflict['has_conflict']) {
                    $msg = $conflict['room_conflict'] ?? $conflict['lecturer_conflict'];
                    throw new InvalidArgumentException("Bentrok jadwal terdeteksi: {$msg}");
                }
            }

            // 3. Update kelas
            $class->update([
                'course_id' => $courseId,
                'term_id' => $termId,
                'lecturer_id' => $newLecturerId,
                'section' => $section,
                'room' => $room,
                'schedule_day' => $scheduleDay,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'capacity' => isset($data['capacity']) ? (int) $data['capacity'] : $class->capacity,
            ]);

            // 4. Jika dosen pengampu diganti, selaraskan assignments dosen_id untuk menjaga konsistensi
            if ($oldLecturerId !== $newLecturerId && $newLecturerId !== null) {
                $class->assignments()->update(['dosen_id' => $newLecturerId]);

                $newL = User::find($newLecturerId);
                ActivityLogger::log(
                    aksi: 'ganti_dosen',
                    kategori: 'akademik',
                    subjek: $class,
                    deskripsi: "Menugaskan dosen pengampu baru ({$newL->name}) untuk kelas {$class->course->code} {$class->section}",
                    properti: [
                        'class_id' => $class->id,
                        'old_lecturer_id' => $oldLecturerId,
                        'new_lecturer_id' => $newLecturerId,
                    ]
                );
            } else {
                ActivityLogger::log(
                    aksi: 'perbarui_kelas',
                    kategori: 'akademik',
                    subjek: $class,
                    deskripsi: "Memperbarui konfigurasi kelas {$class->course->code} {$class->section}",
                    properti: [
                        'class_id' => $class->id,
                        'section' => $class->section,
                        'room' => $class->room,
                        'schedule_day' => $class->schedule_day,
                        'capacity' => $class->capacity,
                    ]
                );
            }

            return $class;
        });
    }

    /**
     * Selesaikan kelas perkuliahan (kunci status menjadi read-only).
     */
    public function finishClass(AcademicClass $class): AcademicClass
    {
        return DB::transaction(function () use ($class) {
            $class->update(['status' => 'selesai']);

            ActivityLogger::log(
                aksi: 'selesaikan_kelas',
                kategori: 'akademik',
                subjek: $class,
                deskripsi: "Menandai kelas perkuliahan {$class->course->code} {$class->section} telah selesai",
                properti: ['class_id' => $class->id, 'status' => 'selesai']
            );

            return $class;
        });
    }

    /**
     * Hapus kelas hanya jika belum memiliki mahasiswa terdaftar atau pengumpulan tugas.
     */
    public function deleteClass(AcademicClass $class): bool
    {
        if ($class->activeEnrollments()->exists() || $class->enrollments()->exists()) {
            $count = $class->enrollments()->count();
            throw new InvalidArgumentException(
                "Kelas {$class->course->code} {$class->section} tidak dapat dihapus karena telah memiliki {$count} mahasiswa yang terdaftar/memiliki riwayat."
            );
        }

        return DB::transaction(function () use ($class) {
            $courseCode = $class->course?->code ?? 'MK';
            $section = $class->section;
            $classId = $class->id;

            // Hapus meetings dan assessment components
            $class->meetings()->delete();
            $class->assessmentComponents()->delete();
            $class->delete();

            ActivityLogger::log(
                aksi: 'hapus_kelas',
                kategori: 'akademik',
                subjek: null,
                deskripsi: "Menghapus kelas perkuliahan: {$courseCode} {$section}",
                properti: ['deleted_class_id' => $classId, 'course_code' => $courseCode, 'section' => $section]
            );

            return true;
        });
    }

    /**
     * Perbarui jadwal tanggal atau topik pertemuan mingguan.
     */
    public function updateMeeting(Meeting $meeting, array $data): Meeting
    {
        return DB::transaction(function () use ($meeting, $data) {
            $meeting->update([
                'title' => trim($data['title'] ?? $meeting->title),
                'date' => $data['date'] ?? $meeting->date,
                'description' => $data['description'] ?? $meeting->description,
                'mode' => $data['mode'] ?? $meeting->mode,
                'start_time' => $data['start_time'] ?? $meeting->start_time,
                'end_time' => $data['end_time'] ?? $meeting->end_time,
            ]);

            ActivityLogger::log(
                aksi: 'perbarui_pertemuan',
                kategori: 'akademik',
                subjek: $meeting,
                deskripsi: "Menyesuaikan jadwal pertemuan ke-{$meeting->week} pada kelas {$meeting->academicClass->course->code} {$meeting->academicClass->section}",
                properti: [
                    'meeting_id' => $meeting->id,
                    'week' => $meeting->week,
                    'title' => $meeting->title,
                    'date' => $meeting->date?->format('Y-m-d'),
                ]
            );

            return $meeting;
        });
    }

    /**
     * Helper privat untuk membuat pertemuan berkala secara otomatis.
     */
    protected function generateMeetingsForClass(AcademicClass $class, int $count = 16): void
    {
        $term = $class->term;
        $baseDate = $term?->start_date ? Carbon::parse($term->start_date) : Carbon::now();

        // Cari tanggal pertama yang cocok dengan hari perkuliahan
        $dayName = strtolower(trim((string) $class->schedule_day));
        if (isset(self::DAY_MAP[$dayName])) {
            $targetDay = self::DAY_MAP[$dayName];
            if ($baseDate->dayOfWeek !== $targetDay) {
                $baseDate->next($targetDay);
            }
        }

        for ($week = 1; $week <= $count; $week++) {
            $meetingDate = (clone $baseDate)->addWeeks($week - 1);

            $title = match ($week) {
                8 => 'Ujian Tengah Semester (UTS)',
                16 => 'Ujian Akhir Semester (UAS)',
                default => "Pertemuan {$week}: Pendalaman Materi Perkuliahan",
            };

            Meeting::create([
                'class_id' => $class->id,
                'week' => $week,
                'title' => $title,
                'description' => "Rencana materi dan kegiatan pembelajaran untuk pertemuan pekan ke-{$week}.",
                'date' => $meetingDate->format('Y-m-d'),
                'start_time' => $class->start_time ?? '08:00:00',
                'end_time' => $class->end_time ?? '10:30:00',
                'mode' => 'offline',
                'is_published' => true,
            ]);
        }
    }
}
