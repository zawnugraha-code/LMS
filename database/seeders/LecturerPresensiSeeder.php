<?php

namespace Database\Seeders;

use App\Models\AcademicClass;
use App\Models\Meeting;
use App\Models\Presensi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class LecturerPresensiSeeder extends Seeder
{
    /**
     * Run the database seeds for Lecturer Presensi.
     * Idempotent: Uses updateOrCreate to prevent duplicate records.
     */
    public function run(): void
    {
        $dosenBudi = User::where('email', 'dosen@arunika.test')->first()
            ?? User::where('email', 'budi.raharjo@unus.ac.id')->first();

        if (! $dosenBudi) {
            return;
        }

        // Dr. Budi's 3 classes
        $classPboA = AcademicClass::where('lecturer_id', $dosenBudi->id)
            ->whereHas('course', fn ($q) => $q->where('code', 'IF2101'))
            ->where('section', 'Kelas A')
            ->first();

        $classPboB = AcademicClass::where('lecturer_id', $dosenBudi->id)
            ->whereHas('course', fn ($q) => $q->where('code', 'IF2101'))
            ->where('section', 'Kelas B')
            ->first();

        $classBankingA = AcademicClass::where('lecturer_id', $dosenBudi->id)
            ->whereHas('course', fn ($q) => $q->where('code', 'IF2103'))
            ->where('section', 'Kelas A')
            ->first();

        if (! $classPboA || ! $classPboB || ! $classBankingA) {
            return;
        }

        // Align IF2103 schedule to Rabu matching Screenshot 1 (Rabu, 08:00 - 10:00 Lab 201)
        $classBankingA->update([
            'schedule_day' => 'Rabu',
            'room' => 'Ruang Lab 201',
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
        ]);

        // Meetings for IF2103 on Wednesdays
        for ($w = 1; $w <= 5; $w++) {
            Meeting::updateOrCreate(
                ['class_id' => $classBankingA->id, 'week' => $w],
                [
                    'date' => Carbon::create(2026, 9, 30)->addWeeks($w - 1)->toDateString(),
                    'start_time' => '08:00:00',
                    'end_time' => '10:00:00',
                ]
            );
        }

        // Reference date: Monday, October 19, 2026
        $refDate = Carbon::create(2026, 10, 19);

        // 1. Seed past meetings for IF2101 Kelas A (Weeks 1 to 4)
        $this->seedClassMeetingsPresensi($classPboA, $dosenBudi, [1, 2, 3, 4]);

        // 2. Seed past meetings for IF2101 Kelas B (Weeks 1 to 4)
        $this->seedClassMeetingsPresensi($classPboB, $dosenBudi, [1, 2, 3, 4]);

        // 3. Seed past meetings for IF2103 Kelas A (Weeks 1 to 3, before 19 Okt)
        $this->seedClassMeetingsPresensi($classBankingA, $dosenBudi, [1, 2, 3]);

        // 4. Seed today's meetings (Monday, 19 Oct 2026) partially so "Belum Absen" = 12
        // IF2101 A (Meeting 5, 32 students) & IF2101 B (Meeting 5, 30 students) = 62 total students today.
        // We record 50 students today (43 Hadir, 4 Izin, 2 Sakit, 1 Tidak Hadir), leaving exactly 12 unrecorded.
        $todayMeetingA = Meeting::where('class_id', $classPboA->id)
            ->where(function ($q) use ($refDate) {
                $q->whereDate('date', $refDate->toDateString())->orWhere('week', 5);
            })->first();

        $todayMeetingB = Meeting::where('class_id', $classPboB->id)
            ->where(function ($q) use ($refDate) {
                $q->whereDate('date', $refDate->toDateString())->orWhere('week', 5);
            })->first();

        if ($todayMeetingA) {
            $studentsA = User::whereHas('enrollments', fn ($q) => $q->where('class_id', $classPboA->id))->get();
            // Record 26 out of 32 students in Kelas A (leaving 6 unrecorded)
            $recordedA = $studentsA->take(26);
            foreach ($recordedA as $idx => $student) {
                $status = 'hadir';
                $catatan = 'Hadir tepat waktu di Lab 307';
                if ($idx === 22) {
                    $status = 'izin';
                    $catatan = 'Izin kegiatan organisasi kampus';
                } elseif ($idx === 23) {
                    $status = 'sakit';
                    $catatan = 'Sakit flu dengan surat keterangan';
                } elseif ($idx === 24) {
                    $status = 'tidak_hadir';
                    $catatan = 'Tanpa keterangan';
                }

                Presensi::updateOrCreate(
                    [
                        'pertemuan_id' => $todayMeetingA->id,
                        'mahasiswa_id' => $student->id,
                    ],
                    [
                        'status' => $status,
                        'catatan' => $catatan,
                        'dicatat_oleh' => $dosenBudi->id,
                        'created_at' => Carbon::create(2026, 10, 19, 8, 15, 0),
                        'updated_at' => Carbon::create(2026, 10, 19, 8, 15, 0),
                    ]
                );
            }
        }

        if ($todayMeetingB) {
            $studentsB = User::whereHas('enrollments', fn ($q) => $q->where('class_id', $classPboB->id))->get();
            // Record 24 out of 30 students in Kelas B (leaving 6 unrecorded)
            // Total unrecorded today = 6 (Kelas A) + 6 (Kelas B) = 12 "Belum Absen"
            $recordedB = $studentsB->take(24);
            foreach ($recordedB as $idx => $student) {
                $status = 'hadir';
                $catatan = 'Hadir di Lab 308';
                if ($idx === 20 || $idx === 21) {
                    $status = 'izin';
                    $catatan = 'Izin keperluan keluarga';
                } elseif ($idx === 22) {
                    $status = 'sakit';
                    $catatan = 'Sakit demam';
                }

                Presensi::updateOrCreate(
                    [
                        'pertemuan_id' => $todayMeetingB->id,
                        'mahasiswa_id' => $student->id,
                    ],
                    [
                        'status' => $status,
                        'catatan' => $catatan,
                        'dicatat_oleh' => $dosenBudi->id,
                        'created_at' => Carbon::create(2026, 10, 19, 10, 15, 0),
                        'updated_at' => Carbon::create(2026, 10, 19, 10, 15, 0),
                    ]
                );
            }
        }
    }

    /**
     * Helper to seed presensi for specific weeks of a class.
     *
     * @param  list<int>  $weeks
     */
    protected function seedClassMeetingsPresensi(AcademicClass $class, User $dosen, array $weeks): void
    {
        $students = User::whereHas('enrollments', fn ($q) => $q->where('class_id', $class->id))->get();
        if ($students->isEmpty()) {
            return;
        }

        $meetings = Meeting::where('class_id', $class->id)->whereIn('week', $weeks)->get();

        foreach ($meetings as $meeting) {
            foreach ($students as $idx => $student) {
                // Determine realistic status with ~86% hadir, 7% izin, 4% sakit, 3% tidak hadir
                // Pseudo-random based on student id and meeting week for determinism
                $hash = ($student->id * 17 + $meeting->week * 31) % 100;

                if ($hash < 85) {
                    $status = 'hadir';
                    $catatan = 'Hadir tepat waktu';
                } elseif ($hash < 93) {
                    $status = 'izin';
                    $catatan = 'Dispensasi kegiatan fakultas';
                } elseif ($hash < 97) {
                    $status = 'sakit';
                    $catatan = 'Surat dokter terlampir';
                } else {
                    $status = 'tidak_hadir';
                    $catatan = 'Alpa / tanpa konfirmasi';
                }

                Presensi::updateOrCreate(
                    [
                        'pertemuan_id' => $meeting->id,
                        'mahasiswa_id' => $student->id,
                    ],
                    [
                        'status' => $status,
                        'catatan' => $catatan,
                        'dicatat_oleh' => $dosen->id,
                        'created_at' => $meeting->date ? Carbon::parse($meeting->date)->setTime(8, 30) : now(),
                        'updated_at' => $meeting->date ? Carbon::parse($meeting->date)->setTime(8, 30) : now(),
                    ]
                );
            }
        }
    }
}
