<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\AcademicClass;
use App\Models\Assignment;
use App\Models\AttendanceLog;
use App\Models\AttendanceSession;
use App\Models\Campus;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Cpl;
use App\Models\Cpmk;
use App\Models\Curriculum;
use App\Models\DiscussionCategory;
use App\Models\DiscussionReply;
use App\Models\DiscussionThread;
use App\Models\Enrollment;
use App\Models\Faculty;
use App\Models\GradeScale;
use App\Models\Material;
use App\Models\MaterialProgress;
use App\Models\Meeting;
use App\Models\OutcomeScore;
use App\Models\Program;
use App\Models\Quiz;
use App\Models\Submission;
use App\Models\Term;
use App\Models\User;
use App\Models\UserPreference;
use App\Models\XpLedger;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CanonicalStudentSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        // ==========================================
        // 1. CAMPUS / PERGURUAN TINGGI
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
                    'obe_enabled' => true,
                    'gamification_enabled' => true,
                ],
            ]
        );

        // ==========================================
        // 2. FAKULTAS & PROGRAM STUDI
        // ==========================================
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
        // 3. SKALA PENILAIAN (grade_scales)
        // ==========================================
        GradeScale::query()->delete();
        $scales = [
            ['letter' => 'A',  'min_score' => 85.00, 'max_score' => 100.00, 'gpa_point' => 4.00, 'is_passing' => true,  'legend_group' => 'A',  'description' => 'Sangat Istimewa'],
            ['letter' => 'A-', 'min_score' => 80.00, 'max_score' => 84.99,  'gpa_point' => 3.75, 'is_passing' => true,  'legend_group' => 'A',  'description' => 'Istimewa'],
            ['letter' => 'B+', 'min_score' => 75.00, 'max_score' => 79.99,  'gpa_point' => 3.50, 'is_passing' => true,  'legend_group' => 'B',  'description' => 'Sangat Baik'],
            ['letter' => 'B',  'min_score' => 70.00, 'max_score' => 74.99,  'gpa_point' => 3.00, 'is_passing' => true,  'legend_group' => 'B',  'description' => 'Baik'],
            ['letter' => 'B-', 'min_score' => 65.00, 'max_score' => 69.99,  'gpa_point' => 2.75, 'is_passing' => true,  'legend_group' => 'B-', 'description' => 'Cukup Baik'],
            ['letter' => 'C+', 'min_score' => 60.00, 'max_score' => 64.99,  'gpa_point' => 2.50, 'is_passing' => true,  'legend_group' => 'C',  'description' => 'Lebih dari Cukup'],
            ['letter' => 'C',  'min_score' => 55.00, 'max_score' => 59.99,  'gpa_point' => 2.00, 'is_passing' => true,  'legend_group' => 'C',  'description' => 'Cukup'],
            ['letter' => 'D',  'min_score' => 40.00, 'max_score' => 54.99,  'gpa_point' => 1.00, 'is_passing' => false, 'legend_group' => 'D',  'description' => 'Kurang'],
            ['letter' => 'E',  'min_score' => 0.00,  'max_score' => 39.99,  'gpa_point' => 0.00, 'is_passing' => false, 'legend_group' => 'E',  'description' => 'Gagal'],
        ];
        foreach ($scales as $scale) {
            GradeScale::create(array_merge($scale, ['campus_id' => $campus->id]));
        }

        // ==========================================
        // 4. PENGGUNA (DOSEN & MAHASISWA UTAMA)
        // ==========================================

        // 4.1 Mahasiswa Utama: Arya Pratama
        $studentArya = User::updateOrCreate(
            ['email' => 'arya.pratama@student.unus.ac.id'],
            [
                'name' => 'Arya Pratama',
                'password' => $password,
                'campus_id' => $campus->id,
                'role' => UserRole::Student,
                'identifier' => '23060101024',
                'phone' => '081234567890',
                'avatar_path' => '/images/avatar-arya.jpg',
                'locale' => 'id',
                'timezone' => 'Asia/Jakarta',
                'created_at' => Carbon::create(2025, 8, 12, 9, 0, 0),
            ]
        );

        // Alias demo student jika login via /demo/login/student
        $legacyStudent = User::where('role', UserRole::Student)->where('email', '!=', 'arya.pratama@student.unus.ac.id')->first();
        if (! $legacyStudent) {
            User::firstOrCreate(
                ['email' => 'mahasiswa@uni.ac.id'],
                [
                    'name' => 'Arya Pratama',
                    'password' => $password,
                    'campus_id' => $campus->id,
                    'role' => UserRole::Student,
                    'identifier' => '23060101024',
                ]
            );
        }

        // 4.2 Dosen Pengampu Sesuai Desain
        $lecturers = [
            'rudi' => User::firstOrCreate(['email' => 'rudi.rahardjo@unus.ac.id'], [
                'name' => 'Dr. Rudi Rahardjo, S.T., M.Kom.',
                'password' => $password,
                'campus_id' => $campus->id,
                'role' => UserRole::Lecturer,
                'identifier' => '0012057801',
                'avatar_path' => '/images/avatar-budi.jpg',
            ]),
            'siti' => User::firstOrCreate(['email' => 'siti.aminah@unus.ac.id'], [
                'name' => 'Siti Aminah, M.Kom.',
                'password' => $password,
                'campus_id' => $campus->id,
                'role' => UserRole::Lecturer,
                'identifier' => '0015088203',
                'avatar_path' => '/images/avatar-siti.jpg',
            ]),
            'budi' => User::firstOrCreate(['email' => 'budi.raharjo@unus.ac.id'], [
                'name' => 'Budi Raharjo, S.Kom., M.T.',
                'password' => $password,
                'campus_id' => $campus->id,
                'role' => UserRole::Lecturer,
                'identifier' => '0021037502',
                'avatar_path' => '/images/avatar-budi.jpg',
            ]),
            'andi' => User::firstOrCreate(['email' => 'andi.saputra@unus.ac.id'], [
                'name' => 'Andi Saputra, S.T., M.Eng.',
                'password' => $password,
                'campus_id' => $campus->id,
                'role' => UserRole::Lecturer,
                'identifier' => '0018118601',
            ]),
            'dewi' => User::firstOrCreate(['email' => 'dewi.lestari@unus.ac.id'], [
                'name' => 'Dewi Lestari, S.Kom., M.T.',
                'password' => $password,
                'campus_id' => $campus->id,
                'role' => UserRole::Lecturer,
                'identifier' => '0029048902',
                'avatar_path' => '/images/avatar-dewi.jpg',
            ]),
            'fajar' => User::firstOrCreate(['email' => 'fajar.nugroho@unus.ac.id'], [
                'name' => 'Fajar Nugroho, S.Kom., M.Kom.',
                'password' => $password,
                'campus_id' => $campus->id,
                'role' => UserRole::Lecturer,
                'identifier' => '0008067301',
                'avatar_path' => '/images/avatar-fajar.jpg',
            ]),
        ];

        // 4.3 Mahasiswa Rekan (Peer Students)
        $peers = [
            User::firstOrCreate(['email' => 'andi@student.unus.ac.id'], ['name' => 'Andi Saputra', 'password' => $password, 'campus_id' => $campus->id, 'role' => UserRole::Student, 'identifier' => '23060101015']),
            User::firstOrCreate(['email' => 'siti.nurhaliza@student.unus.ac.id'], ['name' => 'Siti Nurhaliza', 'password' => $password, 'campus_id' => $campus->id, 'role' => UserRole::Student, 'identifier' => '23060101088', 'avatar_path' => '/images/avatar-siti.jpg']),
            User::firstOrCreate(['email' => 'raka@student.unus.ac.id'], ['name' => 'Raka Maulana', 'password' => $password, 'campus_id' => $campus->id, 'role' => UserRole::Student, 'identifier' => '23060101042']),
            User::firstOrCreate(['email' => 'dewi.lestari@student.unus.ac.id'], ['name' => 'Dewi Lestari', 'password' => $password, 'campus_id' => $campus->id, 'role' => UserRole::Student, 'identifier' => '23060101064', 'avatar_path' => '/images/avatar-dewi.jpg']),
            User::firstOrCreate(['email' => 'bayu@student.unus.ac.id'], ['name' => 'Bayu Pratama', 'password' => $password, 'campus_id' => $campus->id, 'role' => UserRole::Student, 'identifier' => '23060101077']),
            User::firstOrCreate(['email' => 'fajar@student.unus.ac.id'], ['name' => 'Fajar Nugroho', 'password' => $password, 'campus_id' => $campus->id, 'role' => UserRole::Student, 'identifier' => '23060101091', 'avatar_path' => '/images/avatar-fajar.jpg']),
        ];

        // ==========================================
        // 5. PERIODE SEMESTER
        // ==========================================
        $term1 = Term::firstOrCreate(
            ['name' => 'Semester Ganjil 2025/2026', 'campus_id' => $campus->id],
            [
                'academic_year' => '2025/2026',
                'semester_type' => 'odd',
                'start_date' => now()->subMonths(14)->startOfMonth(),
                'end_date' => now()->subMonths(8)->endOfMonth(),
                'is_active' => false,
            ]
        );

        $term2 = Term::firstOrCreate(
            ['name' => 'Semester Genap 2025/2026', 'campus_id' => $campus->id],
            [
                'academic_year' => '2025/2026',
                'semester_type' => 'even',
                'start_date' => now()->subMonths(8)->startOfMonth(),
                'end_date' => now()->subMonths(2)->endOfMonth(),
                'is_active' => false,
            ]
        );

        $term3 = Term::firstOrCreate(
            ['name' => 'Semester Ganjil 2026/2027', 'campus_id' => $campus->id],
            [
                'academic_year' => '2026/2027',
                'semester_type' => 'odd',
                'start_date' => now()->subMonths(2)->startOfMonth(),
                'end_date' => now()->addMonths(4)->endOfMonth(),
                'is_active' => true,
            ]
        );

        // User preference Arya Pratama
        UserPreference::updateOrCreate(
            ['user_id' => $studentArya->id],
            [
                'active_term_id' => $term3->id,
                'theme' => 'light',
                'font_size' => 'normal',
                'locale' => 'id',
                'timezone' => 'Asia/Jakarta',
                'date_format' => 'DD MMM YYYY',
                'time_format' => 'HH:mm',
                'notifications' => [
                    'class_announcements' => true,
                    'assignments_deadlines' => true,
                    'private_messages' => true,
                    'discussion_activities' => false,
                    'system_notifications' => true,
                ],
                'two_factor_enabled' => false,
            ]
        );

        // ==========================================
        // 6. MATA KULIAH RIWAYAT SEMESTER 1 & 2
        // ==========================================
        $historyS1 = [
            ['code' => 'IF1101', 'name' => 'Algoritma & Pemrograman Dasar', 'credits' => 4, 'score' => 92.00, 'letter' => 'A'],
            ['code' => 'IF1102', 'name' => 'Matematika Diskrit', 'credits' => 3, 'score' => 88.00, 'letter' => 'A'],
            ['code' => 'IF1103', 'name' => 'Pengantar Teknologi Informasi', 'credits' => 3, 'score' => 83.00, 'letter' => 'A-'],
            ['code' => 'UN1101', 'name' => 'Bahasa Indonesia', 'credits' => 2, 'score' => 87.00, 'letter' => 'A'],
            ['code' => 'UN1102', 'name' => 'Bahasa Inggris Akademik', 'credits' => 2, 'score' => 89.00, 'letter' => 'A'],
            ['code' => 'IF1104', 'name' => 'Kalkulus 1', 'credits' => 3, 'score' => 78.00, 'letter' => 'B+'],
            ['code' => 'UN1103', 'name' => 'Pendidikan Kewarganegaraan', 'credits' => 3, 'score' => 86.00, 'letter' => 'A'],
        ];

        foreach ($historyS1 as $item) {
            $course = Course::firstOrCreate(
                ['code' => $item['code'], 'curriculum_id' => $curriculum->id],
                ['name' => $item['name'], 'credits' => $item['credits'], 'semester' => 1]
            );
            $class = AcademicClass::firstOrCreate(
                ['course_id' => $course->id, 'term_id' => $term1->id, 'section' => 'IF-1A'],
                ['capacity' => 40, 'lecturer_id' => $lecturers['budi']->id]
            );
            Enrollment::firstOrCreate(
                ['class_id' => $class->id, 'user_id' => $studentArya->id],
                ['status' => 'completed', 'final_score' => $item['score'], 'letter_grade' => $item['letter']]
            );
        }

        $historyS2 = [
            ['code' => 'IF1201', 'name' => 'Arsitektur & Organisasi Komputer', 'credits' => 3, 'score' => 90.00, 'letter' => 'A'],
            ['code' => 'IF1202', 'name' => 'Aljabar Linier & Matriks', 'credits' => 3, 'score' => 86.00, 'letter' => 'A'],
            ['code' => 'IF1203', 'name' => 'Sistem Operasi', 'credits' => 3, 'score' => 82.00, 'letter' => 'A-'],
            ['code' => 'IF1204', 'name' => 'Pemrograman Berbasis Web', 'credits' => 3, 'score' => 94.00, 'letter' => 'A'],
            ['code' => 'IF1205', 'name' => 'Probabilitas & Statistika', 'credits' => 3, 'score' => 79.00, 'letter' => 'B+'],
            ['code' => 'UN1201', 'name' => 'Pendidikan Agama', 'credits' => 2, 'score' => 91.00, 'letter' => 'A'],
            ['code' => 'IF1206', 'name' => 'Kalkulus 2', 'credits' => 3, 'score' => 85.00, 'letter' => 'A'],
        ];

        foreach ($historyS2 as $item) {
            $course = Course::firstOrCreate(
                ['code' => $item['code'], 'curriculum_id' => $curriculum->id],
                ['name' => $item['name'], 'credits' => $item['credits'], 'semester' => 2]
            );
            $class = AcademicClass::firstOrCreate(
                ['course_id' => $course->id, 'term_id' => $term2->id, 'section' => 'IF-2A'],
                ['capacity' => 40, 'lecturer_id' => $lecturers['siti']->id]
            );
            Enrollment::firstOrCreate(
                ['class_id' => $class->id, 'user_id' => $studentArya->id],
                ['status' => 'completed', 'final_score' => $item['score'], 'letter_grade' => $item['letter']]
            );
        }

        // ==========================================
        // 7. PAKET 6 MATA KULIAH SEMESTER 3 (AKTIF)
        // ==========================================
        $activeCoursesData = [
            [
                'code' => 'IF2101',
                'name' => 'Pemrograman Berorientasi Objek & Desain Pola',
                'credits' => 3,
                'lecturer' => $lecturers['rudi'],
                'day' => 'Senin',
                'start_time' => '08:00',
                'end_time' => '10:00',
                'room' => 'Lab Pemrograman',
                'status' => 'enrolled',
                'score' => 88.50,
                'letter' => 'A',
                'completed_meetings' => 7,
                'description' => 'Mata kuliah ini memperdalam paradigma pemrograman berorientasi objek lanjutan, prinsip SOLID, Clean Code, dan implementasi berbagai Design Pattern (Creational, Structural, Behavioral) dalam pengembangan aplikasi enterprise.',
                'cpmks' => [
                    ['code' => 'CPMK-1', 'description' => 'Mampu menerapkan prinsip SOLID dan Clean Architecture dalam desain perangkat lunak.', 'weight' => 30],
                    ['code' => 'CPMK-2', 'description' => 'Mampu merancang arsitektur aplikasi berbasis GoF Design Patterns.', 'weight' => 40],
                    ['code' => 'CPMK-3', 'description' => 'Mampu mengimplementasikan refactoring dan automated unit testing.', 'weight' => 30],
                ],
            ],
            [
                'code' => 'IF2102',
                'name' => 'Basis Data & Sistem Terdistribusi',
                'credits' => 3,
                'lecturer' => $lecturers['siti'],
                'day' => 'Selasa',
                'start_time' => '10:00',
                'end_time' => '12:00',
                'room' => 'Lab Basis Data',
                'status' => 'enrolled',
                'score' => 84.00,
                'letter' => 'A-',
                'completed_meetings' => 5,
                'description' => 'Mempelajari pemodelan data relasional dan non-relasional, indeksing B-Tree, optimasi eksekusi query (EXPLAIN), transaksi ACID, konkurensi, replikasi data, sharding, dan arsitektur database terdistribusi.',
                'cpmks' => [
                    ['code' => 'CPMK-1', 'description' => 'Mampu merancang skema basis data terdistribusi dan normalisasi tingkat lanjut.', 'weight' => 35],
                    ['code' => 'CPMK-2', 'description' => 'Mampu menganalisis dan mengoptimalkan performa query kompleks.', 'weight' => 35],
                    ['code' => 'CPMK-3', 'description' => 'Mampu mengonfigurasi replikasi master-slave dan fault-tolerance.', 'weight' => 30],
                ],
            ],
            [
                'code' => 'IF2103',
                'name' => 'Rancang Bangun Mini Core Banking System (OOP Principles)',
                'credits' => 3,
                'lecturer' => $lecturers['budi'],
                'day' => 'Rabu',
                'start_time' => '08:00',
                'end_time' => '10:00',
                'room' => 'Lab Pemrograman',
                'status' => 'enrolled',
                'score' => 91.20,
                'letter' => 'A',
                'completed_meetings' => 6,
                'description' => 'Penerapan komprehensif prinsip OOP dalam membangun simulasi sistem core banking berstandar industri: ledger akuntansi, validasi mutasi, event-driven architecture, dan compliance audit trail.',
                'cpmks' => [
                    ['code' => 'CPMK-1', 'description' => 'Mampu memodelkan modul akuntansi buku besar (general ledger) berbasis OOP.', 'weight' => 35],
                    ['code' => 'CPMK-2', 'description' => 'Mampu menerapkan mekanisme locking dan konsistensi transaksi perbankan.', 'weight' => 35],
                    ['code' => 'CPMK-3', 'description' => 'Mampu mengintegrasikan audit logging dan verifikasi integritas data.', 'weight' => 30],
                ],
            ],
            [
                'code' => 'IF2104',
                'name' => 'Jaringan Komputer',
                'credits' => 3,
                'lecturer' => $lecturers['andi'],
                'day' => 'Kamis',
                'start_time' => '10:00',
                'end_time' => '12:00',
                'room' => 'Lab Jaringan',
                'status' => 'enrolled',
                'score' => 78.50,
                'letter' => 'B+',
                'completed_meetings' => 3,
                'description' => 'Konsep dan protokol jaringan komputer, model OSI dan TCP/IP, IP addressing dan VLSM/CIDR, protokol routing (OSPF, BGP), transport layer (TCP/UDP), dan analisis packet trace dengan Wireshark.',
                'cpmks' => [
                    ['code' => 'CPMK-1', 'description' => 'Mampu merancang skema pengalamatan IP dan subnetting jaringan.', 'weight' => 30],
                    ['code' => 'CPMK-2', 'description' => 'Mampu mengonfigurasi router, switch, dan protokol routing dinamis.', 'weight' => 40],
                    ['code' => 'CPMK-3', 'description' => 'Mampu menganalisis paket data dan mendiagnosa gangguan jaringan.', 'weight' => 30],
                ],
            ],
            [
                'code' => 'IF2105',
                'name' => 'Arsitektur & Organisasi Komputer',
                'credits' => 3,
                'lecturer' => $lecturers['dewi'],
                'day' => 'Jumat',
                'start_time' => '08:00',
                'end_time' => '10:00',
                'room' => 'Lab Komputer',
                'status' => 'enrolled',
                'score' => 82.00,
                'letter' => 'A-',
                'completed_meetings' => 4,
                'description' => 'Memahami struktur internal komputer digital: unit pengolah pusat (ALU & CU), hierarki memori (Cache, RAM, Virtual Memory), set instruksi mesin (ISA), pipelining processor, dan antarmuka I/O.',
                'cpmks' => [
                    ['code' => 'CPMK-1', 'description' => 'Mampu menjelaskan alur instruksi mesin fetch-decode-execute pada CPU.', 'weight' => 35],
                    ['code' => 'CPMK-2', 'description' => 'Mampu menganalisis efisiensi hierarki cache dan latensi memori.', 'weight' => 35],
                    ['code' => 'CPMK-3', 'description' => 'Mampu mengevaluasi performa throughput dengan teknik pipelining.', 'weight' => 30],
                ],
            ],
            [
                'code' => 'IF2106',
                'name' => 'Keamanan Sistem Informasi',
                'credits' => 3,
                'lecturer' => $lecturers['fajar'],
                'day' => 'Jumat',
                'start_time' => '10:00',
                'end_time' => '12:00',
                'room' => 'Lab Keamanan',
                'status' => 'locked', // Akan Datang (Terkunci)
                'score' => null,
                'letter' => null,
                'completed_meetings' => 0,
                'description' => 'Prinsip kerahasiaan, integritas, ketersediaan (CIA Triad), kriptografi simetris dan asimetris, otentikasi multi-faktor, OWASP Top 10 web vulnerabilities, ethical hacking, dan manajemen insiden keamanan.',
                'cpmks' => [
                    ['code' => 'CPMK-1', 'description' => 'Mampu menganalisis ancaman keamanan sistem dan vektor kerentanan.', 'weight' => 35],
                    ['code' => 'CPMK-2', 'description' => 'Mampu menerapkan teknik enkripsi dan hashing untuk proteksi data.', 'weight' => 35],
                    ['code' => 'CPMK-3', 'description' => 'Mampu melakukan security assessment dan mitigasi OWASP Top 10.', 'weight' => 30],
                ],
            ],
        ];

        $classes = [];
        foreach ($activeCoursesData as $data) {
            $course = Course::updateOrCreate(
                ['code' => $data['code'], 'curriculum_id' => $curriculum->id],
                [
                    'name' => $data['name'],
                    'credits' => $data['credits'],
                    'semester' => 3,
                    'description' => $data['description'],
                    'syllabus' => [
                        'bobot_penilaian' => [
                            'tugas' => 25,
                            'kuis' => 15,
                            'presensi' => 10,
                            'uts' => 25,
                            'uas' => 25,
                        ],
                    ],
                ]
            );

            // CPMKs
            Cpmk::where('course_id', $course->id)->delete();
            foreach ($data['cpmks'] as $cp) {
                Cpmk::create([
                    'course_id' => $course->id,
                    'code' => $cp['code'],
                    'description' => $cp['description'],
                    'weight' => $cp['weight'],
                ]);
            }

            $isLocked = ($data['status'] === 'locked');
            $classSettings = [
                'is_locked' => $isLocked,
                'progress_meetings' => $data['completed_meetings'],
                'start_date' => $isLocked ? now()->addDays(30)->toDateString() : now()->subMonths(2)->toDateString(),
            ];

            $class = AcademicClass::updateOrCreate(
                ['course_id' => $course->id, 'term_id' => $term3->id, 'section' => 'IF-3A'],
                [
                    'lecturer_id' => $data['lecturer']->id,
                    'schedule_day' => $data['day'],
                    'start_time' => $data['start_time'],
                    'end_time' => $data['end_time'],
                    'room' => $data['room'],
                    'capacity' => 40,
                    'settings' => $classSettings,
                ]
            );

            $classes[$data['code']] = $class;

            Enrollment::updateOrCreate(
                ['class_id' => $class->id, 'user_id' => $studentArya->id],
                [
                    'status' => $isLocked ? 'upcoming' : 'enrolled',
                    'final_score' => $data['score'],
                    'letter_grade' => $data['letter'],
                ]
            );

            // 14 Pertemuan per Kelas
            Meeting::where('class_id', $class->id)->delete();
            for ($w = 1; $w <= 14; $w++) {
                $meetingDate = now()->subWeeks(14 - $w)->startOfWeek()->addHours(8);
                $meeting = Meeting::create([
                    'class_id' => $class->id,
                    'week' => $w,
                    'title' => "Pertemuan {$w}: Silabus {$data['code']}",
                    'description' => "Materi perkuliahan minggu ke-{$w} untuk {$data['name']}.",
                    'date' => $meetingDate->toDateString(),
                    'start_time' => $data['start_time'] ?? '08:00',
                    'end_time' => $data['end_time'] ?? '10:00',
                    'mode' => 'offline',
                    'is_published' => true,
                ]);

                // Create a material for this meeting
                $isCompleted = ($w <= $data['completed_meetings']);
                $matTitle = "Modul Perkuliahan Minggu ke-{$w}";
                $matType = ($w % 2 === 0) ? 'video' : 'document';
                $matStatus = $isCompleted ? 'completed' : 'not_started';
                $watchPct = $isCompleted ? 100 : 0;

                if ($data['code'] === 'IF2101') {
                    $canonicalMaterials = [
                        1 => ['title' => 'Konsep Dasar OOP', 'type' => 'document', 'status' => 'completed', 'pct' => 100],
                        2 => ['title' => 'Setup Environment & Java/PHP Core', 'type' => 'document', 'status' => 'completed', 'pct' => 100],
                        3 => ['title' => 'Pengenalan Class, Object, dan Method', 'type' => 'video', 'status' => 'completed', 'pct' => 100],
                        4 => ['title' => 'Praktikum Enkapsulasi & Abstraksi', 'type' => 'document', 'status' => 'completed', 'pct' => 100],
                        5 => ['title' => 'Creational Patterns (Factory & Singleton)', 'type' => 'document', 'status' => 'completed', 'pct' => 100],
                        6 => ['title' => 'Structural Patterns (Adapter & Decorator)', 'type' => 'video', 'status' => 'in_progress', 'pct' => 75],
                        7 => ['title' => 'Behavioral Patterns (Observer & Strategy)', 'type' => 'video', 'status' => 'in_progress', 'pct' => 45],
                        8 => ['title' => 'Implementasi Design Pattern', 'type' => 'assignment', 'status' => 'in_progress', 'pct' => 20],
                        9 => ['title' => 'Prinsip SOLID & Clean Architecture', 'type' => 'document', 'status' => 'in_progress', 'pct' => 50],
                        10 => ['title' => 'Refactoring Monolithic Code', 'type' => 'video', 'status' => 'in_progress', 'pct' => 30],
                        11 => ['title' => 'Kuis Pemahaman Design Pattern', 'type' => 'quiz', 'status' => 'in_progress', 'pct' => 0],
                        12 => ['title' => 'Arsitektur Microservices & Spring Core', 'type' => 'document', 'status' => 'locked', 'pct' => 0],
                        13 => ['title' => 'Implementasi Core Banking API', 'type' => 'video', 'status' => 'locked', 'pct' => 0],
                        14 => ['title' => 'Evaluasi Capaian Pembelajaran & Mini Project', 'type' => 'reference', 'status' => 'locked', 'pct' => 0],
                    ];

                    if (isset($canonicalMaterials[$w])) {
                        $matTitle = $canonicalMaterials[$w]['title'];
                        $matType = $canonicalMaterials[$w]['type'];
                        $matStatus = $canonicalMaterials[$w]['status'];
                        $watchPct = $canonicalMaterials[$w]['pct'];
                        $isCompleted = ($matStatus === 'completed');
                    }
                }

                $material = Material::create([
                    'meeting_id' => $meeting->id,
                    'title' => $matTitle,
                    'type' => $matType,
                    'content' => "Ringkasan materi perkuliahan minggu ke-{$w} untuk mata kuliah {$data['name']}. Materi ini mencakup teori, panduan praktikum, dan implementasi kode standar industri.",
                    'file_path' => "/materials/modul-{$w}.pdf",
                    'order' => 1,
                    'is_downloadable' => true,
                ]);

                MaterialProgress::create([
                    'user_id' => $studentArya->id,
                    'material_id' => $material->id,
                    'status' => $matStatus,
                    'watch_percentage' => $watchPct,
                    'completed_at' => $isCompleted ? now()->subDays(15 - $w) : null,
                ]);

                // Attendance session per meeting
                $attSession = AttendanceSession::create([
                    'meeting_id' => $meeting->id,
                    'qr_secret' => Str::random(32),
                    'opened_at' => $meetingDate,
                    'closed_at' => $meetingDate->copy()->addHours(2),
                    'is_active' => $w <= 7 && ! $isLocked,
                ]);

                if ($w <= 7 && ! $isLocked) {
                    AttendanceLog::create([
                        'attendance_session_id' => $attSession->id,
                        'user_id' => $studentArya->id,
                        'status' => 'present',
                        'method' => 'qr',
                        'checked_at' => $meetingDate->copy()->addMinutes(15),
                    ]);
                }
            }
        }

        $activeClassIds = array_map(fn ($c) => $c->id, array_values($classes));
        AcademicClass::where('term_id', $term3->id)->whereNotIn('id', $activeClassIds)->delete();

        $classPbo = $classes['IF2101'];

        // ==========================================
        // 9. TUGAS & UJIAN
        // ==========================================
        $task1 = Assignment::firstOrCreate(
            ['class_id' => $classPbo->id, 'title' => 'Tugas 1: Desain Arsitektur Class Diagram'],
            [
                'description' => 'Rancang class diagram untuk sistem perpustakaan kampus berbasis SOLID.',
                'due_at' => now()->subDays(10),
                'max_score' => 100.00,
            ]
        );
        Submission::updateOrCreate(
            ['assignment_id' => $task1->id, 'user_id' => $studentArya->id],
            [
                'status' => 'graded',
                'score' => 95.00,
                'feedback' => 'Arsitektur class diagram sangat rapi dan memenuhi prinsip SRP.',
                'submitted_at' => now()->subDays(11),
                'graded_at' => now()->subDays(9),
                'graded_by' => $lecturers['budi']->id,
            ]
        );

        $task2 = Assignment::firstOrCreate(
            ['class_id' => $classPbo->id, 'title' => 'Tugas 2: Implementasi Design Pattern Factory'],
            [
                'description' => 'Implementasikan pola Factory Method untuk pembuatan objek pembayaran.',
                'due_at' => now()->addDays(2)->setTime(23, 59),
                'max_score' => 100.00,
            ]
        );

        $task3 = Assignment::firstOrCreate(
            ['class_id' => $classes['IF2102']->id, 'title' => 'Tugas Algoritma Greedy & Dynamic Programming'],
            [
                'description' => 'Pecahkan persoalan Knapsack Problem dan Coin Change.',
                'due_at' => now()->addDays(4)->setTime(23, 59),
                'max_score' => 100.00,
            ]
        );
        Submission::updateOrCreate(
            ['assignment_id' => $task3->id, 'user_id' => $studentArya->id],
            [
                'status' => 'draft',
                'content' => 'Draft solusi algoritma greedy...',
            ]
        );

        $task4 = Assignment::firstOrCreate(
            ['class_id' => $classes['IF2103']->id, 'title' => 'Tugas Query Optimization & Indexing B-Tree'],
            [
                'description' => 'Optimasi query laporan penjualan toko daring dengan EXPLAIN ANALYZE.',
                'due_at' => now()->subDays(2)->setTime(23, 59),
                'max_score' => 100.00,
            ]
        );
        Submission::updateOrCreate(
            ['assignment_id' => $task4->id, 'user_id' => $studentArya->id],
            [
                'status' => 'submitted',
                'content' => 'Hasil optimasi query indeks B-Tree dilampirkan.',
                'submitted_at' => now()->subDays(3),
            ]
        );

        // Ujian UTS & Kuis
        $quiz1 = Quiz::firstOrCreate(
            ['class_id' => $classes['IF2104']->id, 'title' => 'Ujian Tengah Semester (UTS): Rekayasa PL'],
            [
                'description' => 'Ujian tertulis materi kebutuhan perangkat lunak dan arsitektur.',
                'duration_minutes' => 120,
            ]
        );

        $quiz2 = Quiz::firstOrCreate(
            ['class_id' => $classes['IF2105']->id, 'title' => 'Kuis Konfigurasi Subnetting & Routing OSPF'],
            [
                'description' => 'Kuis pemahaman subnet IPv4 CIDR dan routing table.',
                'duration_minutes' => 45,
            ]
        );

        // ==========================================
        // 10. FORUM DISKUSI (7 THREAD KANONIK)
        // ==========================================
        DiscussionCategory::query()->delete();
        $cats = [
            ['name' => 'Forum Kelas', 'slug' => 'forum-kelas', 'icon' => 'message-square', 'color' => 'blue'],
            ['name' => 'Tanya Jawab', 'slug' => 'tanya-jawab', 'icon' => 'help-circle', 'color' => 'green'],
            ['name' => 'Pengumuman', 'slug' => 'pengumuman', 'icon' => 'bell', 'color' => 'blue'],
            ['name' => 'Diskusi', 'slug' => 'diskusi', 'icon' => 'message-circle', 'color' => 'purple'],
            ['name' => 'Berbagi', 'slug' => 'berbagi', 'icon' => 'share-2', 'color' => 'orange'],
        ];
        $catModels = [];
        foreach ($cats as $c) {
            $catModels[$c['slug']] = DiscussionCategory::create($c);
        }

        DiscussionThread::query()->delete();
        $threadsData = [
            [
                'user' => $lecturers['budi'],
                'category' => $catModels['pengumuman'],
                'class' => $classPbo,
                'title' => '[PENTING] Ketentuan Pengumpulan Tugas Besar PBO & Jadwal Presentasi',
                'content' => 'Pengumpulan tugas besar dilakukan melalui portal paling lambat akhir bulan ini.',
                'is_pinned' => true,
                'views' => 2400,
                'comments_count' => 142,
                'days_ago' => 3,
            ],
            [
                'user' => $peers[0], // Andi
                'category' => $catModels['tanya-jawab'],
                'class' => $classPbo,
                'title' => 'Tanya implementasi Interface Segregation Principle (ISP) di arsitektur microservices',
                'content' => 'Bagaimana memecah interface yang terlalu gemuk tanpa membuat terlalu banyak kelas?',
                'is_pinned' => false,
                'views' => 567,
                'comments_count' => 28,
                'hours_ago' => 5,
            ],
            [
                'user' => $peers[1], // Siti
                'category' => $catModels['diskusi'],
                'class' => $classes['IF2102'],
                'title' => 'Diskusi kelompok praktikum Struktur Data pertemuan ke-7 materi Binary Search Tree',
                'content' => 'Mari diskusikan implementasi AVL Tree rotation pada modul 7.',
                'is_pinned' => false,
                'views' => 892,
                'comments_count' => 45,
                'hours_ago' => 7,
            ],
            [
                'user' => $peers[2], // Raka
                'category' => $catModels['forum-kelas'],
                'class' => $classPbo,
                'title' => 'Rekomendasi extension VS Code terbaik untuk Docker dan Laravel devcontainer',
                'content' => 'Berikut extension yang sering saya gunakan untuk produktivitas backend.',
                'is_pinned' => false,
                'views' => 342,
                'comments_count' => 19,
                'hours_ago' => 10,
            ],
            [
                'user' => $peers[3], // Dewi
                'category' => $catModels['diskusi'],
                'class' => $classes['IF2103'],
                'title' => 'Catatan kuliah Basis Data Lanjut materi Query Execution Plan & Indeks Covering',
                'content' => 'Rangkuman cara membaca hasil explain query di database MySQL.',
                'is_pinned' => false,
                'views' => 201,
                'comments_count' => 12,
                'days_ago' => 1,
            ],
            [
                'user' => $peers[4], // Bayu
                'category' => $catModels['tanya-jawab'],
                'class' => $classes['IF2105'],
                'title' => 'Solusi error Connection refused pada konfigurasi PostgreSQL Docker di WSL 2',
                'content' => 'Bagi yang mengalami kendala port binding pada Docker WSL2, ini triknya.',
                'is_pinned' => false,
                'views' => 674,
                'comments_count' => 36,
                'days_ago' => 1,
            ],
            [
                'user' => $peers[5], // Fajar
                'category' => $catModels['berbagi'],
                'class' => $classPbo,
                'title' => 'Berbagi template starter kit REST API Clean Architecture dengan PHP 8.4',
                'content' => 'Starter kit boilerplate siap pakai untuk tugas akhir mata kuliah PBO.',
                'is_pinned' => false,
                'views' => 412,
                'comments_count' => 20,
                'days_ago' => 2,
            ],
        ];

        foreach ($threadsData as $td) {
            $createdTime = isset($td['days_ago']) ? now()->subDays($td['days_ago']) : now()->subHours($td['hours_ago']);
            $thread = DiscussionThread::create([
                'user_id' => $td['user']->id,
                'category_id' => $td['category']->id,
                'class_id' => $td['class']->id,
                'title' => $td['title'],
                'content' => $td['content'],
                'is_pinned' => $td['is_pinned'],
                'views_count' => $td['views'],
                'created_at' => $createdTime,
                'updated_at' => $createdTime,
            ]);

            // Seeding mock replies
            for ($r = 1; $r <= min(3, $td['comments_count']); $r++) {
                DiscussionReply::create([
                    'thread_id' => $thread->id,
                    'user_id' => $peers[($r - 1) % count($peers)]->id,
                    'content' => 'Terima kasih atas diskusinya! Sangat bermanfaat.',
                    'created_at' => $createdTime->copy()->addMinutes($r * 15),
                ]);
            }
        }

        // ==========================================
        // 11. NOTIFIKASI PENGGUNA (2 UNREAD, 3 READ)
        // ==========================================
        DB::table('notifications')->where('notifiable_id', $studentArya->id)->delete();
        $notifs = [
            [
                'title' => 'Tugas Baru Diterbitkan',
                'message' => 'Dr. Budi Rahardjo menerbitkan Tugas 2: Implementasi Factory Pattern pada IF2101.',
                'category' => 'assignment',
                'icon' => 'clipboard-list',
                'action_url' => '/app/assignments',
                'read' => false,
                'time' => now()->subHours(2),
            ],
            [
                'title' => 'Tanggapan Diskusi Baru',
                'message' => 'Andi Saputra membalas pertanyaan Anda di forum PBO.',
                'category' => 'discussion',
                'icon' => 'message-circle',
                'action_url' => '/app/discussions',
                'read' => false,
                'time' => now()->subHours(4),
            ],
            [
                'title' => 'Nilai Kuis Diumumkan',
                'message' => 'Nilai Kuis Subnetting Jaringan Komputer telah dipublikasikan (Skor: 88.00).',
                'category' => 'grade',
                'icon' => 'bar-chart-3',
                'action_url' => '/app/grades',
                'read' => true,
                'time' => now()->subDays(1),
            ],
            [
                'title' => 'Presensi Berhasil Dicatat',
                'message' => 'Check-in presensi Pertemuan 8 Pemrograman Berorientasi Objek sukses.',
                'category' => 'attendance',
                'icon' => 'calendar-check',
                'action_url' => '/app/attendance',
                'read' => true,
                'time' => now()->subDays(2),
            ],
            [
                'title' => 'Pengingat Batas Pengumpulan',
                'message' => 'Tugas 1 Basis Data Lanjut akan ditutup dalam 24 jam.',
                'category' => 'assignment',
                'icon' => 'clock',
                'action_url' => '/app/assignments',
                'read' => true,
                'time' => now()->subDays(3),
            ],
        ];

        foreach ($notifs as $n) {
            DB::table('notifications')->insert([
                'id' => (string) Str::uuid(),
                'type' => 'App\Notifications\StandardNotification',
                'notifiable_type' => User::class,
                'notifiable_id' => $studentArya->id,
                'data' => json_encode([
                    'title' => $n['title'],
                    'message' => $n['message'],
                    'category' => $n['category'],
                    'icon' => $n['icon'],
                    'action_url' => $n['action_url'],
                ]),
                'read_at' => $n['read'] ? $n['time']->copy()->addHours(1) : null,
                'created_at' => $n['time'],
                'updated_at' => $n['time'],
            ]);
        }

        // ==========================================
        // 12. SERTIFIKAT & BUKU BESAR XP
        // ==========================================
        Certificate::updateOrCreate(
            ['certificate_number' => 'CERT-UNI-IF1204-2026-081'],
            [
                'user_id' => $studentArya->id,
                'course_id' => Course::where('code', 'IF1204')->first()?->id,
                'title' => 'Sertifikat Kelulusan Pemrograman Web Lanjut',
                'final_score' => 94.00,
                'letter_grade' => 'A',
                'issued_date' => Carbon::create(2026, 7, 28),
                'verification_code' => 'VRF-WEB-20260728',
            ]
        );

        Certificate::updateOrCreate(
            ['certificate_number' => 'CERT-UNI-IF1101-2026-014'],
            [
                'user_id' => $studentArya->id,
                'course_id' => Course::where('code', 'IF1101')->first()?->id,
                'title' => 'Sertifikat Kompetensi Algoritma & Struktur Data Dasar',
                'final_score' => 92.00,
                'letter_grade' => 'A',
                'issued_date' => Carbon::create(2026, 1, 15),
                'verification_code' => 'VRF-ALGO-20260115',
            ]
        );

        // XP Ledgers (Total 7,450 XP -> Level 12)
        XpLedger::where('user_id', $studentArya->id)->delete();
        $xpEntries = [
            ['amount' => 1500, 'source_type' => 'assignment', 'desc' => 'Penyelesaian Tugas Besar Desain Pola OOP', 'days' => 5],
            ['amount' => 1200, 'source_type' => 'quiz', 'desc' => 'Skor Sempurna Kuis Struktur Data', 'days' => 12],
            ['amount' => 800,  'source_type' => 'attendance', 'desc' => 'Kehadiran 100% Minggu ke-8', 'days' => 2],
            ['amount' => 1000, 'source_type' => 'streak', 'desc' => 'Streak Belajar 5 Hari Berturut-turut', 'days' => 1],
            ['amount' => 2950, 'source_type' => 'academic', 'desc' => 'Prestasi Akademik Semester 1 & 2', 'days' => 60],
        ];
        foreach ($xpEntries as $entry) {
            XpLedger::create([
                'user_id' => $studentArya->id,
                'amount' => $entry['amount'],
                'source_type' => $entry['source_type'],
                'description' => $entry['desc'],
                'created_at' => now()->subDays($entry['days']),
            ]);
        }

        // OBE Outcome Scores (CPL)
        $cpl1 = Cpl::firstOrCreate(['code' => 'CPL-01', 'program_id' => $program->id], ['category' => 'knowledge', 'description' => 'Mampu menerapkan konsep dasar ilmu komputer.', 'target_score' => 75.00]);
        $cpl2 = Cpl::firstOrCreate(['code' => 'CPL-02', 'program_id' => $program->id], ['category' => 'special_skills', 'description' => 'Mampu merancang dan mengimplementasikan perangkat lunak.', 'target_score' => 80.00]);
        $cpl3 = Cpl::firstOrCreate(['code' => 'CPL-03', 'program_id' => $program->id], ['category' => 'general_skills', 'description' => 'Mampu berpikir kritis dan menyelesaikan masalah komputasi.', 'target_score' => 75.00]);

        OutcomeScore::updateOrCreate(
            ['user_id' => $studentArya->id, 'cpl_id' => $cpl1->id],
            ['score' => 88.50, 'calculated_at' => now()]
        );
        OutcomeScore::updateOrCreate(
            ['user_id' => $studentArya->id, 'cpl_id' => $cpl2->id],
            ['score' => 84.00, 'calculated_at' => now()]
        );
        OutcomeScore::updateOrCreate(
            ['user_id' => $studentArya->id, 'cpl_id' => $cpl3->id],
            ['score' => 91.20, 'calculated_at' => now()]
        );
    }
}
