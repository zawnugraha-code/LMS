<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\AcademicClass;
use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Campus;
use App\Models\Course;
use App\Models\CourseAssessmentComponent;
use App\Models\Curriculum;
use App\Models\Enrollment;
use App\Models\Faculty;
use App\Models\Material;
use App\Models\Meeting;
use App\Models\Program;
use App\Models\Submission;
use App\Models\Term;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LecturerBatch1Seeder extends Seeder
{
    /**
     * Run the database seeds for Lecturer Dashboard Batch 1.
     * Idempotent: Uses updateOrCreate / firstOrCreate to protect existing student data.
     */
    public function run(): void
    {
        $password = Hash::make('password');

        // ==========================================
        // 1. CAMPUS, FAKULTAS, PROGRAM & KURIKULUM
        // ==========================================
        $campus = Campus::firstOrCreate(
            ['code' => 'UNI'],
            [
                'name' => 'Universitas Nusantara Indonesia',
                'domain' => 'unus.ac.id',
                'tagline' => 'Maju Bersama Ilmu',
                'address' => 'Jl. Boulevard Kampus Merdeka No. 101, Jakarta',
                'website' => 'https://unus.ac.id',
                'is_active' => true,
                'settings' => [
                    'timezone' => 'Asia/Jakarta',
                    'primary_color' => '#1A2E5E',
                ],
            ]
        );

        $faculty = Faculty::firstOrCreate(
            ['code' => 'FKOM', 'campus_id' => $campus->id],
            [
                'name' => 'Fakultas Ilmu Komputer',
                'description' => 'Fakultas Ilmu Komputer dan Teknologi Informasi',
            ]
        );

        $program = Program::firstOrCreate(
            ['code' => 'IF', 'faculty_id' => $faculty->id],
            [
                'name' => 'S1 Teknik Informatika',
                'degree_level' => 'S1',
                'accreditation' => 'Unggul',
            ]
        );

        $curriculum = Curriculum::firstOrCreate(
            ['program_id' => $program->id, 'year' => 2024],
            [
                'name' => 'Kurikulum Berbasis OBE 2024',
                'is_active' => true,
            ]
        );

        // ==========================================
        // 2. SEMESTER AKTIF (GANJIL 2026/2027)
        // ==========================================
        $activeTerm = Term::firstOrCreate(
            ['name' => 'Semester Ganjil 2026/2027', 'campus_id' => $campus->id],
            [
                'academic_year' => '2026/2027',
                'semester_type' => 'odd',
                'start_date' => Carbon::create(2026, 9, 1),
                'end_date' => Carbon::create(2027, 1, 31),
                'is_active' => true,
            ]
        );

        // ==========================================
        // 3. DOSEN PENGAMPU UTAMA & DOSEN PEMBANDING
        // ==========================================
        // Dosen Utama (Dr. Budi Rahardjo)
        $dosenBudi = User::updateOrCreate(
            ['email' => 'dosen@arunika.test'],
            [
                'name' => 'Dr. Budi Rahardjo',
                'gelar' => 'Dr. Budi Rahardjo, S.T., M.Kom.',
                'password' => $password,
                'campus_id' => $campus->id,
                'role' => UserRole::Lecturer,
                'identifier' => '0021037502',
                'prodi' => 'S1 Teknik Informatika',
                'jabatan' => 'Lektor Kepala',
                'phone' => '081298765432',
                'avatar_path' => '/images/avatar-budi.jpg',
                'locale' => 'id',
                'timezone' => 'Asia/Jakarta',
                'is_active' => true,
            ]
        );

        // Alias akun dosen yang mungkin dipakai di seeders lama
        User::where('email', 'budi.raharjo@unus.ac.id')->update([
            'gelar' => 'Dr. Budi Rahardjo, S.T., M.Kom.',
            'prodi' => 'S1 Teknik Informatika',
            'jabatan' => 'Lektor Kepala',
        ]);

        // Dosen Pembanding (Dr. Rudi Rahardjo untuk pengujian isolasi data)
        $dosenRudi = User::updateOrCreate(
            ['email' => 'dosen2@arunika.test'],
            [
                'name' => 'Dr. Rudi Rahardjo',
                'gelar' => 'Dr. Rudi Rahardjo, S.T., M.Kom.',
                'password' => $password,
                'campus_id' => $campus->id,
                'role' => UserRole::Lecturer,
                'identifier' => '0012057801',
                'prodi' => 'S1 Teknik Informatika',
                'jabatan' => 'Lektor',
                'avatar_path' => '/images/avatar-budi.jpg',
                'locale' => 'id',
                'timezone' => 'Asia/Jakarta',
                'is_active' => true,
            ]
        );

        // ==========================================
        // 4. MATA KULIAH (IF2101 & IF2103)
        // ==========================================
        $coursePBO = Course::firstOrCreate(
            ['code' => 'IF2101', 'curriculum_id' => $curriculum->id],
            [
                'name' => 'Pemrograman Berorientasi Objek & Desain Pola',
                'credits' => 3,
                'semester' => 3,
                'description' => 'Mata kuliah ini memperdalam paradigma OOP lanjutan, prinsip SOLID, Clean Code, dan implementasi Design Pattern.',
                'cover_path' => '/images/course-pbo.jpg',
            ]
        );

        $courseCoreBanking = Course::firstOrCreate(
            ['code' => 'IF2103', 'curriculum_id' => $curriculum->id],
            [
                'name' => 'Rancang Bangun Mini Core Banking System (OOP)',
                'credits' => 3,
                'semester' => 3,
                'description' => 'Mempelajari rancang bangun arsitektur sistem perbankan inti berbasis OOP dan transaksi terdistribusi.',
                'cover_path' => '/images/course-banking.jpg',
            ]
        );

        // Mata kuliah pengujian untuk dosen pembanding
        $courseAudit = Course::firstOrCreate(
            ['code' => 'IF2199', 'curriculum_id' => $curriculum->id],
            [
                'name' => 'Audit Sistem Informasi',
                'credits' => 3,
                'semester' => 5,
                'description' => 'Mata kuliah audit khusus diampu oleh dosen lain untuk uji otorisasi.',
            ]
        );

        // ==========================================
        // 5. KELAS YANG DIAMPU DOSEN BUDI
        // ==========================================
        // 5.1 IF2101 Kelas A: 32 mhs, Senin 08:00 - 10:00, Lab 307
        $classPboA = AcademicClass::updateOrCreate(
            ['course_id' => $coursePBO->id, 'term_id' => $activeTerm->id, 'section' => 'Kelas A'],
            [
                'lecturer_id' => $dosenBudi->id,
                'room' => 'Lab 307',
                'schedule_day' => 'Senin',
                'start_time' => '08:00:00',
                'end_time' => '10:00:00',
                'capacity' => 40,
                'status' => 'aktif',
            ]
        );

        // 5.2 IF2101 Kelas B: 30 mhs, Senin 10:00 - 12:00, Lab 308
        $classPboB = AcademicClass::updateOrCreate(
            ['course_id' => $coursePBO->id, 'term_id' => $activeTerm->id, 'section' => 'Kelas B'],
            [
                'lecturer_id' => $dosenBudi->id,
                'room' => 'Lab 308',
                'schedule_day' => 'Senin',
                'start_time' => '10:00:00',
                'end_time' => '12:00:00',
                'capacity' => 40,
                'status' => 'aktif',
            ]
        );

        // 5.3 IF2103 Kelas A: 28 mhs, Senin 13:00 - 15:00, Lab 201
        $classBankingA = AcademicClass::updateOrCreate(
            ['course_id' => $courseCoreBanking->id, 'term_id' => $activeTerm->id, 'section' => 'Kelas A'],
            [
                'lecturer_id' => $dosenBudi->id,
                'room' => 'Lab 201',
                'schedule_day' => 'Senin',
                'start_time' => '13:00:00',
                'end_time' => '15:00:00',
                'capacity' => 35,
                'status' => 'aktif',
            ]
        );

        // Kelas milik Dosen Rudi (untuk uji otorisasi)
        $classOtherDosen = AcademicClass::updateOrCreate(
            ['course_id' => $courseAudit->id, 'term_id' => $activeTerm->id, 'section' => 'Kelas X'],
            [
                'lecturer_id' => $dosenRudi->id,
                'room' => 'Ruang 401',
                'schedule_day' => 'Jumat',
                'start_time' => '09:00:00',
                'end_time' => '11:00:00',
                'capacity' => 30,
                'status' => 'aktif',
            ]
        );

        // ==========================================
        // 6. KOMPONEN PENILAIAN PER KELAS
        // ==========================================
        // IF2101: Tugas 30%, Kuis 20%, UTS 25%, UAS 25%
        $componentsPbo = [
            ['jenis' => 'tugas', 'bobot' => 30],
            ['jenis' => 'kuis',  'bobot' => 20],
            ['jenis' => 'uts',   'bobot' => 25],
            ['jenis' => 'uas',   'bobot' => 25],
        ];
        foreach ([$classPboA, $classPboB] as $cls) {
            foreach ($componentsPbo as $cmp) {
                CourseAssessmentComponent::updateOrCreate(
                    ['class_id' => $cls->id, 'jenis' => $cmp['jenis']],
                    ['bobot' => $cmp['bobot']]
                );
            }
        }

        // IF2103: Tugas 40%, Kuis 10%, UTS 25%, UAS 25%
        $componentsBanking = [
            ['jenis' => 'tugas', 'bobot' => 40],
            ['jenis' => 'kuis',  'bobot' => 10],
            ['jenis' => 'uts',   'bobot' => 25],
            ['jenis' => 'uas',   'bobot' => 25],
        ];
        foreach ($componentsBanking as $cmp) {
            CourseAssessmentComponent::updateOrCreate(
                ['class_id' => $classBankingA->id, 'jenis' => $cmp['jenis']],
                ['bobot' => $cmp['bobot']]
            );
        }

        // ==========================================
        // 7. MAHASISWA & ENROLLMENT (TOTAL 90 MAHASISWA UNIK)
        // ==========================================
        // Pastikan mahasiswa utama eksisting 'Arya Pratama'
        $mhsArya = User::where('email', 'arya.pratama@student.unus.ac.id')->first()
            ?? User::firstOrCreate(
                ['email' => 'arya.pratama@student.unus.ac.id'],
                [
                    'name' => 'Arya Pratama',
                    'password' => $password,
                    'campus_id' => $campus->id,
                    'role' => UserRole::Student,
                    'identifier' => '23060101024',
                    'avatar_path' => '/images/avatar-arya.jpg',
                ]
            );

        // Daftar nama wajib sesuai desain
        $namedPboA = [
            ['name' => 'Ahmad Fauzi', 'nim' => '23060101001', 'email' => 'ahmad.fauzi@student.unus.ac.id'],
            ['name' => 'Siti Nurhaliza', 'nim' => '23060101002', 'email' => 'siti.nurhaliza@student.unus.ac.id'],
            ['name' => 'Fajar Nugroho', 'nim' => '23060101003', 'email' => 'fajar.nugroho@student.unus.ac.id'],
            ['name' => 'Arya Pratama', 'nim' => '23060101024', 'email' => 'arya.pratama@student.unus.ac.id'],
        ];

        $namedPboB = [
            ['name' => 'Dewi Lestari', 'nim' => '23060101040', 'email' => 'dewi.lestari@student.unus.ac.id'],
        ];

        $namedBankingA = [
            ['name' => 'Rizky Pratama', 'nim' => '23060101070', 'email' => 'rizky.pratama@student.unus.ac.id'],
        ];

        // Generator 90 mahasiswa unik
        $firstNames = ['Bagus', 'Dimas', 'Eko', 'Gilang', 'Hadi', 'Indra', 'Joko', 'Kevin', 'Lutfi', 'Mahendra',
            'Naufal', 'Oki', 'Panji', 'Qori', 'Rian', 'Surya', 'Teguh', 'Utama', 'Vino', 'Wahyu',
            'Yuda', 'Zaky', 'Anisa', 'Bella', 'Citra', 'Dina', 'Elisa', 'Fitri', 'Gita', 'Hesti',
            'Intan', 'Jihan', 'Kartika', 'Laras', 'Mega', 'Nadia', 'Putri', 'Rani', 'Sari', 'Tiara'];
        $lastNames = ['Saputra', 'Wijaya', 'Kusuma', 'Santoso', 'Pratama', 'Hidayat', 'Wibowo', 'Nugraha',
            'Utomo', 'Firmansyah', 'Setiawan', 'Ramadhan', 'Maulana', 'Gunawan', 'Permana', 'Suryadi'];

        $allStudents = collect();

        // 1. Tambahkan mahasiswa bernama
        foreach (array_merge($namedPboA, $namedPboB, $namedBankingA) as $n) {
            $student = User::updateOrCreate(
                ['email' => $n['email']],
                [
                    'name' => $n['name'],
                    'password' => $password,
                    'campus_id' => $campus->id,
                    'role' => UserRole::Student,
                    'identifier' => $n['nim'],
                    'is_active' => true,
                ]
            );
            $allStudents->push($student);
        }

        // 2. Generate sisanya hingga total 90
        $nimCounter = 23060101100;
        $nameIndex = 0;
        while ($allStudents->count() < 90) {
            $f = $firstNames[$nameIndex % count($firstNames)];
            $l = $lastNames[($nameIndex * 3) % count($lastNames)];
            $nimCounter++;
            $email = strtolower("{$f}.{$l}.{$nimCounter}@student.unus.ac.id");

            $student = User::firstOrCreate(
                ['identifier' => (string) $nimCounter],
                [
                    'name' => "{$f} {$l}",
                    'email' => $email,
                    'password' => $password,
                    'campus_id' => $campus->id,
                    'role' => UserRole::Student,
                    'is_active' => true,
                ]
            );
            $allStudents->push($student);
            $nameIndex++;
        }

        // Bagi rata tanpa overlapping:
        // IF2101 A = 32 mhs (termasuk Ahmad, Siti, Fajar, Arya)
        $studentsPboA = $allStudents->splice(0, 32);
        foreach ($studentsPboA as $s) {
            Enrollment::firstOrCreate(['class_id' => $classPboA->id, 'user_id' => $s->id], ['status' => 'enrolled']);
        }

        // IF2101 B = 30 mhs (termasuk Dewi Lestari)
        $studentsPboB = $allStudents->splice(0, 30);
        foreach ($studentsPboB as $s) {
            Enrollment::firstOrCreate(['class_id' => $classPboB->id, 'user_id' => $s->id], ['status' => 'enrolled']);
        }

        // IF2103 A = 28 mhs (termasuk Rizky Pratama)
        $studentsBankingA = $allStudents->splice(0, 28);
        foreach ($studentsBankingA as $s) {
            Enrollment::firstOrCreate(['class_id' => $classBankingA->id, 'user_id' => $s->id], ['status' => 'enrolled']);
        }

        // ==========================================
        // 8. PERTEMUAN MINGGUAN (Senin, 19 Okt 2026 = Pertemuan 5)
        // ==========================================
        // IF2101 Kelas A (Pertemuan 1 s/d 6)
        $pboTopics = [
            1 => ['title' => 'Pengenalan OOP & Konsep Dasar', 'date' => '2026-09-21'],
            2 => ['title' => 'Inheritance & Hierarchy', 'date' => '2026-09-28'],
            3 => ['title' => 'Polimorfisme & Dynamic Binding', 'date' => '2026-10-05'],
            4 => ['title' => 'Interface & Abstract Class', 'date' => '2026-10-12'],
            5 => ['title' => 'Studi Kasus Desain Pola', 'date' => '2026-10-19'], // Hari ini
            6 => ['title' => 'Review & Persiapan Ulangan Harian', 'date' => '2026-10-26'],
        ];

        $meetingsPboA = [];
        foreach ($pboTopics as $week => $info) {
            $meetingsPboA[$week] = Meeting::updateOrCreate(
                ['class_id' => $classPboA->id, 'week' => $week],
                [
                    'title' => "Pertemuan {$week} - {$info['title']}",
                    'description' => "Rincian materi dan panduan untuk pertemuan ke-{$week}.",
                    'date' => $info['date'],
                    'start_time' => '08:00:00',
                    'end_time' => '10:00:00',
                    'is_published' => true,
                ]
            );
        }

        // IF2101 Kelas B (Pertemuan 1 s/d 5)
        for ($w = 1; $w <= 5; $w++) {
            Meeting::updateOrCreate(
                ['class_id' => $classPboB->id, 'week' => $w],
                [
                    'title' => "Pertemuan {$w} - ".($pboTopics[$w]['title'] ?? 'Materi Perkuliahan'),
                    'date' => $pboTopics[$w]['date'] ?? '2026-10-19',
                    'start_time' => '10:00:00',
                    'end_time' => '12:00:00',
                    'is_published' => true,
                ]
            );
        }

        // IF2103 Kelas A (Pertemuan 1 s/d 5, Hari ini = Pertemuan 4)
        for ($w = 1; $w <= 5; $w++) {
            Meeting::updateOrCreate(
                ['class_id' => $classBankingA->id, 'week' => $w],
                [
                    'title' => "Pertemuan {$w} - Arsitektur Core Banking Modul {$w}",
                    'date' => Carbon::create(2026, 9, 28)->addWeeks($w - 1)->toDateString(),
                    'start_time' => '13:00:00',
                    'end_time' => '15:00:00',
                    'is_published' => true,
                ]
            );
        }

        // ==========================================
        // 9. MATERI PEMBELAJARAN (7 MATERI DI IF2101 KELAS A)
        // ==========================================
        $materialsData = [
            [
                'week' => 1,
                'title' => 'Pengantar OOP dan Konsep Dasar',
                'type' => 'pdf',
                'created_at' => Carbon::create(2026, 10, 12, 14, 23, 0),
                'status' => 'published',
            ],
            [
                'week' => 1,
                'title' => 'Video Pembelajaran - Class & Object',
                'type' => 'tautan',
                'created_at' => Carbon::create(2026, 10, 12, 15, 10, 0),
                'status' => 'published',
            ],
            [
                'week' => 2,
                'title' => 'Slide Pertemuan 2 - Inheritance',
                'type' => 'ppt',
                'created_at' => Carbon::create(2026, 10, 14, 9, 32, 0),
                'status' => 'published',
            ],
            [
                'week' => 3,
                'title' => 'Modul Praktikum - Polimorfisme',
                'type' => 'pdf',
                'created_at' => Carbon::create(2026, 10, 16, 11, 5, 0),
                'status' => 'published',
            ],
            [
                'week' => 4,
                'title' => 'Video - Interface dan Abstract Class',
                'type' => 'video',
                'created_at' => Carbon::create(2026, 10, 17, 13, 20, 0),
                'status' => 'published',
            ],
            [
                'week' => 5,
                'title' => 'Dataset Latihan',
                'type' => 'zip',
                'created_at' => Carbon::create(2026, 10, 18, 10, 45, 0),
                'status' => 'draft', // DRAFT sesuai screenshot
            ],
            [
                'week' => 6,
                'title' => 'Materi Ulangan Harian',
                'type' => 'pdf',
                'created_at' => Carbon::create(2026, 10, 19, 8, 15, 0),
                'status' => 'published',
            ],
        ];

        foreach ($materialsData as $idx => $m) {
            $meeting = $meetingsPboA[$m['week']] ?? $meetingsPboA[1];
            Material::updateOrCreate(
                ['meeting_id' => $meeting->id, 'title' => $m['title']],
                [
                    'class_id' => $classPboA->id,
                    'type' => $m['type'],
                    'content' => "Materi pembelajaran mengenai {$m['title']}.",
                    'file_path' => '/uploads/materials/'.Str::slug($m['title']).'.pdf',
                    'status' => $m['status'],
                    'uploaded_by' => $dosenBudi->id,
                    'order' => $idx + 1,
                    'created_at' => $m['created_at'],
                ]
            );
        }

        // ==========================================
        // 10. 12 ASSESSMENTS LINTAS 3 KELAS
        // ==========================================
        $assessments = [
            // 1. Tugas 3 - Polimorfisme (IF2101 A, Deadline: 21 Okt 2026 23:59 -> 3 hari lagi)
            'tugas3_pbo_a' => Assignment::updateOrCreate(
                ['class_id' => $classPboA->id, 'title' => 'Tugas 3 - Polimorfisme'],
                [
                    'dosen_id' => $dosenBudi->id,
                    'jenis' => 'tugas',
                    'mode' => 'individu',
                    'metode' => 'unggah_berkas',
                    'description' => 'Implementasikan dynamic polymorphism dan interface pada domain banking/retail.',
                    'max_score' => 100,
                    'mulai_at' => Carbon::create(2026, 10, 15, 8, 0, 0),
                    'due_at' => Carbon::create(2026, 10, 21, 23, 59, 0),
                    'publish_at' => Carbon::create(2026, 10, 15, 8, 0, 0),
                    'status' => 'dipublikasikan',
                ]
            ),

            // 2. Tugas 2 - OOP (IF2101 B, Deadline: 23 Okt 2026 23:59 -> 5 hari lagi)
            'tugas2_pbo_b' => Assignment::updateOrCreate(
                ['class_id' => $classPboB->id, 'title' => 'Tugas 2 - OOP'],
                [
                    'dosen_id' => $dosenBudi->id,
                    'jenis' => 'tugas',
                    'mode' => 'individu',
                    'metode' => 'unggah_berkas',
                    'description' => 'Latihan inheritance dan encapsulasi kelas objek.',
                    'max_score' => 100,
                    'mulai_at' => Carbon::create(2026, 10, 15, 10, 0, 0),
                    'due_at' => Carbon::create(2026, 10, 23, 23, 59, 0),
                    'publish_at' => Carbon::create(2026, 10, 15, 10, 0, 0),
                    'status' => 'dipublikasikan',
                ]
            ),

            // 3. Tugas 1 - Analisis Sistem (IF2103 A, Deadline: 26 Okt 2026 23:59 -> 8 hari lagi)
            'tugas1_banking_a' => Assignment::updateOrCreate(
                ['class_id' => $classBankingA->id, 'title' => 'Tugas 1 - Analisis Sistem'],
                [
                    'dosen_id' => $dosenBudi->id,
                    'jenis' => 'tugas',
                    'mode' => 'individu',
                    'metode' => 'unggah_berkas',
                    'description' => 'Analisis arsitektur sistem core banking dan entitas rekening nasabah.',
                    'max_score' => 100,
                    'mulai_at' => Carbon::create(2026, 10, 12, 13, 0, 0),
                    'due_at' => Carbon::create(2026, 10, 26, 23, 59, 0),
                    'publish_at' => Carbon::create(2026, 10, 12, 13, 0, 0),
                    'status' => 'dipublikasikan',
                ]
            ),

            // 4. Tugas 2 - OOP (IF2101 A, Deadline: 18 Okt 2026 20:00 -> sudah lewat)
            'tugas2_pbo_a' => Assignment::updateOrCreate(
                ['class_id' => $classPboA->id, 'title' => 'Tugas 2 - OOP'],
                [
                    'dosen_id' => $dosenBudi->id,
                    'jenis' => 'tugas',
                    'mode' => 'individu',
                    'metode' => 'unggah_berkas',
                    'description' => 'Pembuatan rancangan class diagram dan relasi asosiasi/komposisi.',
                    'max_score' => 100,
                    'mulai_at' => Carbon::create(2026, 10, 10, 8, 0, 0),
                    'due_at' => Carbon::create(2026, 10, 18, 20, 0, 0),
                    'publish_at' => Carbon::create(2026, 10, 10, 8, 0, 0),
                    'status' => 'dipublikasikan',
                ]
            ),

            // 5. Tugas 1 - Konfigurasi IP (IF2101 A, Deadline: 17 Okt 2026 20:00 -> sudah lewat)
            'tugas1_ip_a' => Assignment::updateOrCreate(
                ['class_id' => $classPboA->id, 'title' => 'Tugas 1 - Konfigurasi IP'],
                [
                    'dosen_id' => $dosenBudi->id,
                    'jenis' => 'tugas',
                    'mode' => 'individu',
                    'metode' => 'unggah_berkas',
                    'description' => 'Pengenalan socket programming dan konfigurasi IP address.',
                    'max_score' => 100,
                    'mulai_at' => Carbon::create(2026, 10, 5, 8, 0, 0),
                    'due_at' => Carbon::create(2026, 10, 17, 20, 0, 0),
                    'publish_at' => Carbon::create(2026, 10, 5, 8, 0, 0),
                    'status' => 'dipublikasikan',
                ]
            ),

            // 6. Kuis 1 - Rancang Bangun Mini Core Banking System (OOP) (IF2103 A, Status: Draft)
            'kuis1_banking_a' => Assignment::updateOrCreate(
                ['class_id' => $classBankingA->id, 'title' => 'Kuis 1 - Rancang Bangun Mini Core Banking System (OOP)'],
                [
                    'dosen_id' => $dosenBudi->id,
                    'jenis' => 'kuis',
                    'mode' => 'individu',
                    'metode' => 'input_manual',
                    'description' => 'Kuis pemahaman prinsip ACID dan abstraksi akun tabungan.',
                    'max_score' => 100,
                    'due_at' => Carbon::create(2026, 10, 18, 10, 45, 0),
                    'status' => 'draft',
                ]
            ),

            // 7. UTS - Pemrograman Web (IF2101 A, Status: Terjadwal)
            'uts_web' => Assignment::updateOrCreate(
                ['class_id' => $classPboA->id, 'title' => 'UTS - Pemrograman Web'],
                [
                    'dosen_id' => $dosenBudi->id,
                    'jenis' => 'uts',
                    'mode' => 'individu',
                    'metode' => 'input_manual',
                    'description' => 'Ujian Tengah Semester Pemrograman Aplikasi Terpadu.',
                    'max_score' => 100,
                    'due_at' => Carbon::create(2026, 10, 26, 8, 0, 0),
                    'publish_at' => Carbon::create(2026, 10, 24, 8, 0, 0), // Masa depan
                    'status' => 'terjadwal',
                ]
            ),

            // 8. Tugas 4 - Arsitektur & Organisasi Komputer (IF2101 B, Status: Dipublikasikan)
            'tugas4_arsitektur' => Assignment::updateOrCreate(
                ['class_id' => $classPboB->id, 'title' => 'Tugas 4 - Arsitektur & Organisasi Komputer'],
                [
                    'dosen_id' => $dosenBudi->id,
                    'jenis' => 'tugas',
                    'mode' => 'kelompok',
                    'metode' => 'unggah_berkas',
                    'description' => 'Simulasi alur instruksi pipeline prosesor.',
                    'max_score' => 100,
                    'due_at' => Carbon::create(2026, 10, 29, 14, 0, 0),
                    'publish_at' => Carbon::create(2026, 10, 16, 14, 0, 0),
                    'status' => 'dipublikasikan',
                ]
            ),

            // 9. UAS - Sistem Operasi (IF2101 A, Status: Draft)
            'uas_os' => Assignment::updateOrCreate(
                ['class_id' => $classPboA->id, 'title' => 'UAS - Sistem Operasi'],
                [
                    'dosen_id' => $dosenBudi->id,
                    'jenis' => 'uas',
                    'mode' => 'individu',
                    'metode' => 'input_manual',
                    'description' => 'Ujian Akhir Semester Manajemen Memori dan Virtualisasi.',
                    'max_score' => 100,
                    'due_at' => Carbon::create(2026, 12, 5, 8, 0, 0),
                    'status' => 'draft',
                ]
            ),

            // 10. Tugas 5 - Jaringan Komputer (IF2101 B, Status: Dipublikasikan)
            'tugas5_jaringan' => Assignment::updateOrCreate(
                ['class_id' => $classPboB->id, 'title' => 'Tugas 5 - Jaringan Komputer'],
                [
                    'dosen_id' => $dosenBudi->id,
                    'jenis' => 'tugas',
                    'mode' => 'individu',
                    'metode' => 'unggah_berkas',
                    'description' => 'Desain topologi VLAN dan routing OSPF.',
                    'max_score' => 100,
                    'due_at' => Carbon::create(2026, 11, 10, 10, 0, 0),
                    'publish_at' => Carbon::create(2026, 10, 17, 10, 0, 0),
                    'status' => 'dipublikasikan',
                ]
            ),

            // 11. Kuis 2 - Basis Data (IF2103 A, Status: Dipublikasikan)
            'kuis2_db' => Assignment::updateOrCreate(
                ['class_id' => $classBankingA->id, 'title' => 'Kuis 2 - Basis Data'],
                [
                    'dosen_id' => $dosenBudi->id,
                    'jenis' => 'kuis',
                    'mode' => 'individu',
                    'metode' => 'input_manual',
                    'description' => 'Kuis optimasi query relasional dan indeks composite.',
                    'max_score' => 100,
                    'due_at' => Carbon::create(2026, 11, 12, 13, 0, 0),
                    'publish_at' => Carbon::create(2026, 10, 17, 13, 0, 0),
                    'status' => 'dipublikasikan',
                ]
            ),

            // 12. UAS - Manajemen Proyek TI (IF2103 A, Status: Arsip)
            'uas_proyek' => Assignment::updateOrCreate(
                ['class_id' => $classBankingA->id, 'title' => 'UAS - Manajemen Proyek TI'],
                [
                    'dosen_id' => $dosenBudi->id,
                    'jenis' => 'uas',
                    'mode' => 'individu',
                    'metode' => 'input_manual',
                    'description' => 'Arsip evaluasi semester sebelumnya.',
                    'max_score' => 100,
                    'due_at' => Carbon::create(2026, 12, 15, 8, 0, 0),
                    'status' => 'arsip',
                ]
            ),
        ];

        // ==========================================
        // 11. PENGUMPULAN (TEPAT 14 BELUM DINILAI, BADGE = 14)
        // ==========================================
        $userAhmad = User::where('email', 'ahmad.fauzi@student.unus.ac.id')->first();
        $userSiti = User::where('email', 'siti.nurhaliza@student.unus.ac.id')->first();
        $userFajar = User::where('email', 'fajar.nugroho@student.unus.ac.id')->first();
        $userDewi = User::where('email', 'dewi.lestari@student.unus.ac.id')->first();
        $userRizky = User::where('email', 'rizky.pratama@student.unus.ac.id')->first();

        // 5 baris pengumpulan teratas persis screenshot Beranda
        $top5Submissions = [
            // 1. Ahmad Fauzi - IF2101 Kelas A - Tugas 2 - OOP - 18 Okt 2026, 21:34 - Terlambat
            [
                'assignment' => $assessments['tugas2_pbo_a'],
                'user' => $userAhmad,
                'time' => Carbon::create(2026, 10, 18, 21, 34, 0),
                'status_waktu' => 'terlambat',
            ],
            // 2. Siti Nurhaliza - IF2101 Kelas A - Tugas 2 - OOP - 18 Okt 2026, 19:12 - Tepat Waktu
            [
                'assignment' => $assessments['tugas2_pbo_a'],
                'user' => $userSiti,
                'time' => Carbon::create(2026, 10, 18, 19, 12, 0),
                'status_waktu' => 'tepat_waktu',
            ],
            // 3. Rizky Pratama - IF2103 Kelas A - Tugas 1 - Analisis Sistem - 18 Okt 2026, 16:20 - Tepat Waktu
            [
                'assignment' => $assessments['tugas1_banking_a'],
                'user' => $userRizky,
                'time' => Carbon::create(2026, 10, 18, 16, 20, 0),
                'status_waktu' => 'tepat_waktu',
            ],
            // 4. Dewi Lestari - IF2101 Kelas B - Tugas 2 - OOP - 17 Okt 2026, 23:17 - Terlambat
            [
                'assignment' => $assessments['tugas2_pbo_b'],
                'user' => $userDewi,
                'time' => Carbon::create(2026, 10, 17, 23, 17, 0),
                'status_waktu' => 'terlambat',
            ],
            // 5. Fajar Nugroho - IF2101 Kelas A - Tugas 1 - Konfigurasi IP - 17 Okt 2026, 20:05 - Tepat Waktu
            [
                'assignment' => $assessments['tugas1_ip_a'],
                'user' => $userFajar,
                'time' => Carbon::create(2026, 10, 17, 20, 05, 0),
                'status_waktu' => 'tepat_waktu',
            ],
        ];

        foreach ($top5Submissions as $row) {
            if ($row['user'] && $row['assignment']) {
                Submission::updateOrCreate(
                    ['assignment_id' => $row['assignment']->id, 'user_id' => $row['user']->id],
                    [
                        'content' => 'Pengumpulan berkas tugas resmi mahasiswa.',
                        'file_path' => '/uploads/submissions/submission-'.$row['user']->id.'.pdf',
                        'submitted_at' => $row['time'],
                        'status' => 'submitted',
                        'status_waktu' => $row['status_waktu'],
                        'score' => null, // Belum dinilai
                        'feedback' => null,
                        'graded_at' => null,
                        'graded_by' => null,
                    ]
                );
            }
        }

        // Tambahkan 9 submission lagi agar total yang belum dinilai TEPAT 14!
        // Ambil mahasiswa lain dari kelas A dan B
        $otherStudentsA = User::whereHas('enrollments', fn ($q) => $q->where('class_id', $classPboA->id))
            ->whereNotIn('id', [$userAhmad?->id, $userSiti?->id, $userFajar?->id, $userDewi?->id, $userRizky?->id])
            ->take(6)
            ->get();

        foreach ($otherStudentsA as $idx => $s) {
            Submission::updateOrCreate(
                ['assignment_id' => $assessments['tugas2_pbo_a']->id, 'user_id' => $s->id],
                [
                    'content' => 'Laporan tugas praktikum mandiri.',
                    'file_path' => '/uploads/submissions/submission-'.$s->id.'.pdf',
                    'submitted_at' => Carbon::create(2026, 10, 17, 14, 0, 0)->addHours($idx),
                    'status' => 'submitted',
                    'status_waktu' => 'tepat_waktu',
                    'score' => null,
                ]
            );
        }

        $otherStudentsB = User::whereHas('enrollments', fn ($q) => $q->where('class_id', $classPboB->id))
            ->whereNotIn('id', [$userDewi?->id])
            ->take(3)
            ->get();

        foreach ($otherStudentsB as $idx => $s) {
            Submission::updateOrCreate(
                ['assignment_id' => $assessments['tugas2_pbo_b']->id, 'user_id' => $s->id],
                [
                    'content' => 'Jawaban latihan OOP modul 2.',
                    'file_path' => '/uploads/submissions/submission-b-'.$s->id.'.pdf',
                    'submitted_at' => Carbon::create(2026, 10, 16, 11, 0, 0)->addHours($idx),
                    'status' => 'submitted',
                    'status_waktu' => 'tepat_waktu',
                    'score' => null,
                ]
            );
        }

        // Tambahkan sejumlah submission yang SUDAH dinilai (score != null)
        $gradedStudents = User::whereHas('enrollments', fn ($q) => $q->where('class_id', $classPboA->id))
            ->whereNotIn('id', [$userAhmad?->id, $userSiti?->id, $userFajar?->id])
            ->skip(6)
            ->take(5)
            ->get();

        foreach ($gradedStudents as $idx => $s) {
            Submission::updateOrCreate(
                ['assignment_id' => $assessments['tugas1_ip_a']->id, 'user_id' => $s->id],
                [
                    'content' => 'Hasil analisis konfigurasi IP Address lengkap.',
                    'file_path' => '/uploads/submissions/submission-graded-'.$s->id.'.pdf',
                    'submitted_at' => Carbon::create(2026, 10, 16, 10, 0, 0),
                    'status' => 'graded',
                    'status_waktu' => 'tepat_waktu',
                    'score' => 88.00 + ($idx * 2),
                    'feedback' => 'Penjelasan struktur topologi sangat baik dan runtut.',
                    'graded_at' => Carbon::create(2026, 10, 17, 9, 30, 0),
                    'graded_by' => $dosenBudi->id,
                ]
            );
        }

        // ==========================================
        // 12. PENGUMUMAN KELAS & AKADEMIK
        // ==========================================
        // Pengumuman Kelas
        Announcement::updateOrCreate(
            ['title' => 'Jadwal Ujian Tengah Semester (UTS)'],
            [
                'class_id' => $classPboA->id,
                'author_id' => $dosenBudi->id,
                'content' => 'UTS akan dilaksanakan pada tanggal 26 Oktober - 30 Oktober 2026 secara luring di Ruang Lab.',
                'published_at' => Carbon::create(2026, 10, 17, 9, 0, 0),
            ]
        );

        Announcement::updateOrCreate(
            ['title' => 'Materi Pertemuan 6 - IF2101 Kelas A'],
            [
                'class_id' => $classPboA->id,
                'author_id' => $dosenBudi->id,
                'content' => 'Materi dan slide pertemuan ke-6 sudah tersedia di menu Materi Pembelajaran.',
                'published_at' => Carbon::create(2026, 10, 15, 14, 0, 0),
            ]
        );

        Announcement::updateOrCreate(
            ['title' => 'Perubahan Ruang Kelas IF2103'],
            [
                'class_id' => $classBankingA->id,
                'author_id' => $dosenBudi->id,
                'content' => 'Kelas IF2103 Kelas A dipindah ke Ruang Lab 202 mulai minggu depan.',
                'published_at' => Carbon::create(2026, 10, 12, 10, 30, 0),
            ]
        );

        // Pengumuman Akademik (class_id = null)
        Announcement::updateOrCreate(
            ['title' => 'Pendaftaran KRS Semester Ganjil 2026/2027'],
            [
                'class_id' => null,
                'author_id' => $dosenBudi->id,
                'content' => 'Pendaftaran KRS dibuka mulai 10 - 20 Oktober 2026 melalui portal akademik.',
                'published_at' => Carbon::create(2026, 10, 10, 8, 0, 0),
            ]
        );

        Announcement::updateOrCreate(
            ['title' => 'Jadwal Wisuda Periode 2026'],
            [
                'class_id' => null,
                'author_id' => $dosenBudi->id,
                'content' => 'Informasi jadwal wisuda dapat diakses pada menu Akademik bagi calon wisudawan.',
                'published_at' => Carbon::create(2026, 10, 5, 10, 0, 0),
            ]
        );

        // ==========================================
        // 13. NOTIFIKASI UNTUK DOSEN BUDI (2 UNREAD)
        // ==========================================
        $dosenBudi->notifications()->delete(); // Bersihkan notif lama agar presisi 2 unread

        $dosenBudi->notifications()->create([
            'id' => Str::uuid()->toString(),
            'type' => 'App\Notifications\SubmissionReceivedNotification',
            'data' => [
                'type' => 'submission_received',
                'title' => 'Pengumpulan Baru: Tugas 2 - OOP',
                'message' => 'Ahmad Fauzi telah mengumpulkan Tugas 2 - OOP (Terlambat).',
                'url' => '/dosen/penilaian',
                'icon' => 'check-circle',
                'color' => 'amber',
            ],
            'read_at' => null,
            'created_at' => Carbon::create(2026, 10, 18, 21, 35, 0),
        ]);

        $dosenBudi->notifications()->create([
            'id' => Str::uuid()->toString(),
            'type' => 'App\Notifications\SubmissionReceivedNotification',
            'data' => [
                'type' => 'submission_received',
                'title' => 'Pengumpulan Baru: Tugas 1 - Analisis Sistem',
                'message' => 'Rizky Pratama telah mengumpulkan Tugas 1 - Analisis Sistem (Tepat Waktu).',
                'url' => '/dosen/penilaian',
                'icon' => 'check-circle',
                'color' => 'emerald',
            ],
            'read_at' => null,
            'created_at' => Carbon::create(2026, 10, 18, 16, 21, 0),
        ]);
    }
}
