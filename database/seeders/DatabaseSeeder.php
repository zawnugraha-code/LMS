<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\AcademicClass;
use App\Models\Assignment;
use App\Models\AttendanceLog;
use App\Models\AttendanceSession;
use App\Models\Badge;
use App\Models\Campus;
use App\Models\Course;
use App\Models\Cpl;
use App\Models\Cpmk;
use App\Models\Curriculum;
use App\Models\DemoRequest;
use App\Models\Enrollment;
use App\Models\Faculty;
use App\Models\Material;
use App\Models\Meeting;
use App\Models\OutcomeScore;
use App\Models\Program;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\SubCpmk;
use App\Models\Submission;
use App\Models\Term;
use App\Models\User;
use App\Models\XpLedger;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with realistic Indonesian higher education data.
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->call(ProductionSeeder::class);
            return;
        }

        $password = Hash::make('password');

        // ==========================================
        // 1. CAMPUS / PERGURUAN TINGGI
        // ==========================================
        $campus = Campus::create([
            'name' => 'Universitas Nusantara Indonesia',
            'code' => 'UNI',
            'domain' => 'uni.ac.id',
            'tagline' => 'Unggul dalam Riset, Inovasi, dan Berkarakter Nusantara',
            'address' => 'Jl. Boulevard Kampus Merdeka No. 101, Jakarta Selatan',
            'website' => 'https://uni.ac.id',
            'is_active' => true,
            'settings' => [
                'timezone' => 'Asia/Jakarta',
                'primary_color' => '#14513F',
                'obe_enabled' => true,
                'gamification_enabled' => true,
                'ai_copilot_enabled' => true,
            ],
        ]);

        // ==========================================
        // 2. USERS (MULTI-ROLE WITH REALISTIC PROFILES)
        // ==========================================

        // 2.1 Super Admin (Global System)
        $superAdmin = User::create([
            'name' => 'Aditya Pratama, S.Kom. (Super Admin)',
            'email' => 'superadmin@arunika.ac.id',
            'password' => $password,
            'campus_id' => null,
            'role' => UserRole::SuperAdmin,
            'identifier' => 'SYS-001',
            'phone' => '081234567890',
            'locale' => 'id',
            'timezone' => 'Asia/Jakarta',
        ]);

        // 2.2 Campus Admin (BAAK / Pengelola Sistem Kampus)
        $campusAdmin = User::create([
            'name' => 'Dr. Ir. Hendra Gunawan, M.T.',
            'email' => 'admin@uni.ac.id',
            'password' => $password,
            'campus_id' => $campus->id,
            'role' => UserRole::CampusAdmin,
            'identifier' => 'ADM-UNI-2021',
            'phone' => '081398765432',
            'locale' => 'id',
            'timezone' => 'Asia/Jakarta',
        ]);

        // 2.3 Dosen Pengampu (Lecturer)
        $lecturer = User::create([
            'name' => 'Prof. Dr. Budi Rahardjo, Ph.D.',
            'email' => 'dosen@uni.ac.id',
            'password' => $password,
            'campus_id' => $campus->id,
            'role' => UserRole::Lecturer,
            'identifier' => '0012057801', // NIDN
            'phone' => '081211223344',
            'locale' => 'id',
            'timezone' => 'Asia/Jakarta',
        ]);

        // 2.4 Mahasiswa 1 (Primary Demo Student)
        $studentArya = User::create([
            'name' => 'Arya Pratama',
            'email' => 'mahasiswa@uni.ac.id',
            'password' => $password,
            'campus_id' => $campus->id,
            'role' => UserRole::Student,
            'identifier' => '2201010045', // NIM
            'phone' => '085712345678',
            'locale' => 'id',
            'timezone' => 'Asia/Jakarta',
        ]);

        // 2.5 Mahasiswa 2 (Peer Student)
        $studentSiti = User::create([
            'name' => 'Siti Nurhaliza',
            'email' => 'siti@uni.ac.id',
            'password' => $password,
            'campus_id' => $campus->id,
            'role' => UserRole::Student,
            'identifier' => '2201010088', // NIM
            'phone' => '085812345678',
            'locale' => 'id',
            'timezone' => 'Asia/Jakarta',
        ]);

        // 2.6 Tenaga Kependidikan / Dosen PA (Staff)
        $staff = User::create([
            'name' => 'Dra. Endang Lestari, M.Si.',
            'email' => 'staff@uni.ac.id',
            'password' => $password,
            'campus_id' => $campus->id,
            'role' => UserRole::Staff,
            'identifier' => '197508151999032001', // NIP
            'phone' => '081344556677',
            'locale' => 'id',
            'timezone' => 'Asia/Jakarta',
        ]);

        // 2.7 Auditor / Reviewer Akreditasi LAM-INFOKOM
        $auditor = User::create([
            'name' => 'Dr. Ahmad Hidayat, M.Kom. (Auditor Akreditasi)',
            'email' => 'auditor@uni.ac.id',
            'password' => $password,
            'campus_id' => $campus->id,
            'role' => UserRole::Auditor,
            'identifier' => 'AUD-LAM-092',
            'phone' => '081122334455',
            'locale' => 'id',
            'timezone' => 'Asia/Jakarta',
        ]);

        // 2.8 Mentor Industri / MBKM Magang
        $mentor = User::create([
            'name' => 'Rian Permana (Senior Engineering Lead @ Gojek/Tokopedia)',
            'email' => 'mentor@gojek.com',
            'password' => $password,
            'campus_id' => $campus->id,
            'role' => UserRole::IndustryMentor,
            'identifier' => 'IND-GOTO-881',
            'phone' => '081299887766',
            'locale' => 'id',
            'timezone' => 'Asia/Jakarta',
        ]);

        // ==========================================
        // 3. ACADEMIC STRUCTURE (FAKULTAS, PRODI, TERM)
        // ==========================================
        $faculty = Faculty::create([
            'campus_id' => $campus->id,
            'name' => 'Fakultas Ilmu Komputer & Rekayasa Perangkat Lunak',
            'code' => 'FILKOM',
            'description' => 'Fakultas pelopor komputasi cerdas, kecerdasan buatan, dan arsitektur perangkat lunak skala enterprise.',
        ]);

        $program = Program::create([
            'faculty_id' => $faculty->id,
            'name' => 'S1 Teknik Informatika',
            'code' => 'TIF',
            'degree_level' => 'S1',
            'accreditation' => 'Unggul (LAM-INFOKOM)',
            'description' => 'Mencetak software engineer, architect, dan data scientist dengan pemahaman kurikulum OBE bertaraf internasional.',
        ]);

        $term = Term::create([
            'campus_id' => $campus->id,
            'name' => 'Semester Ganjil 2026/2027',
            'academic_year' => '2026/2027',
            'semester_type' => 'odd',
            'start_date' => Carbon::now()->subMonths(1)->startOfMonth(),
            'end_date' => Carbon::now()->addMonths(4)->endOfMonth(),
            'is_active' => true,
        ]);

        $curriculum = Curriculum::create([
            'program_id' => $program->id,
            'year' => 2024,
            'name' => 'Kurikulum Berbasis OBE Informatika 2024 (Revisi Akreditasi)',
            'is_active' => true,
        ]);

        // ==========================================
        // 4. COURSES & OBE OUTCOMES (CPL & CPMK)
        // ==========================================
        $courseOOP = Course::create([
            'curriculum_id' => $curriculum->id,
            'code' => 'IF2104',
            'name' => 'Pemrograman Berorientasi Objek & Desain Pola',
            'credits' => 3,
            'semester' => 3,
            'description' => 'Mata kuliah inti yang membahas paradigma OOP, SOLID principles, design patterns (GoF), clean code architecture, dan testing.',
            'syllabus' => [
                'prerequisites' => ['IF1101 Dasar Pemrograman'],
                'tools' => ['PHP 8.3 / Java 21', 'Docker', 'Git & GitHub'],
            ],
        ]);

        Course::create([
            'curriculum_id' => $curriculum->id,
            'code' => 'IF2201',
            'name' => 'Algoritma dan Struktur Data Tingkat Lanjut',
            'credits' => 4,
            'semester' => 3,
            'description' => 'Analisis kompleksitas asimtotik Big-O, graf, dynamic programming, dan struktur data tingkat tinggi.',
        ]);

        Course::create([
            'curriculum_id' => $curriculum->id,
            'code' => 'IF3102',
            'name' => 'Rekayasa Perangkat Lunak & CI/CD DevSecOps',
            'credits' => 3,
            'semester' => 5,
            'description' => 'Metodologi Agile/Scrum, requirement engineering, pipeline otomatisasi CI/CD, dan security testing.',
        ]);

        // CPL (Capaian Pembelajaran Lulusan)
        $cpl1 = Cpl::create([
            'program_id' => $program->id,
            'code' => 'CPL-01',
            'category' => 'knowledge',
            'description' => 'Mampu menerapkan prinsip logika komputasi, algoritma, dan teori perangkat lunak untuk menyelesaikan permasalahan rekayasa kompleks.',
            'target_score' => 65.00,
        ]);

        $cpl2 = Cpl::create([
            'program_id' => $program->id,
            'code' => 'CPL-02',
            'category' => 'special_skills',
            'description' => 'Mampu merancang, mengimplementasikan, dan mengevaluasi arsitektur perangkat lunak yang scalable, robust, dan memenuhi standar keamanan.',
            'target_score' => 70.00,
        ]);

        $cpl3 = Cpl::create([
            'program_id' => $program->id,
            'code' => 'CPL-03',
            'category' => 'general_skills',
            'description' => 'Mampu berkomunikasi efektif secara teknis dan bekerja kolaboratif dalam tim multidisiplin dengan menjunjung etika profesional.',
            'target_score' => 75.00,
        ]);

        // CPMK untuk Course OOP
        $cpmk1 = Cpmk::create([
            'course_id' => $courseOOP->id,
            'code' => 'CPMK-1',
            'description' => 'Menguasai 4 pilar OOP (Encapsulation, Inheritance, Polymorphism, Abstraction) dan menerapkannya dalam modular code.',
            'weight' => 30.00,
        ]);

        $cpmk2 = Cpmk::create([
            'course_id' => $courseOOP->id,
            'code' => 'CPMK-2',
            'description' => 'Mampu menerapkan prinsip SOLID dan Refactoring untuk menghasilkan kode yang maintainable dan scalable.',
            'weight' => 40.00,
        ]);

        $cpmk3 = Cpmk::create([
            'course_id' => $courseOOP->id,
            'code' => 'CPMK-3',
            'description' => 'Mampu mengimplementasikan Design Patterns dasar (Creational, Structural, Behavioral) pada studi kasus nyata.',
            'weight' => 30.00,
        ]);

        SubCpmk::create([
            'cpmk_id' => $cpmk1->id,
            'code' => 'Sub-CPMK-1.1',
            'description' => 'Menjelaskan perbedaan class, instance, dan memory layout.',
        ]);

        SubCpmk::create([
            'cpmk_id' => $cpmk2->id,
            'code' => 'Sub-CPMK-2.1',
            'description' => 'Mengidentifikasi code smell dan menerapkan Single Responsibility Principle.',
        ]);

        // ==========================================
        // 5. ACADEMIC CLASS & ENROLLMENTS
        // ==========================================
        $class = AcademicClass::create([
            'course_id' => $courseOOP->id,
            'term_id' => $term->id,
            'lecturer_id' => $lecturer->id,
            'section' => 'IF-3A (Reguler)',
            'room' => 'Lab Multimedia Gedung B Lt. 3',
            'schedule_day' => 'Senin',
            'start_time' => '08:00',
            'end_time' => '10:30',
            'capacity' => 40,
            'settings' => [
                'grading_weights' => [
                    'assignments' => 30,
                    'quizzes' => 20,
                    'midterm' => 25,
                    'final_project' => 25,
                ],
                'attendance_min_percent' => 75,
            ],
        ]);

        // Enroll students
        Enrollment::create([
            'class_id' => $class->id,
            'user_id' => $studentArya->id,
            'status' => 'aktif',
            'final_score' => 91.50,
            'letter_grade' => 'A',
        ]);

        Enrollment::create([
            'class_id' => $class->id,
            'user_id' => $studentSiti->id,
            'status' => 'aktif',
            'final_score' => 84.00,
            'letter_grade' => 'A-',
        ]);

        // ==========================================
        // 6. MEETINGS & MATERIALS (RPS SILABUS)
        // ==========================================
        $meetingsData = [
            [
                'week' => 1,
                'title' => 'Pengantar OOP & Filosofi Abstraksi Komputasi',
                'description' => 'Memahami pergeseran dari pemrograman prosedural ke paradigma berorientasi objek.',
                'mode' => 'offline',
            ],
            [
                'week' => 2,
                'title' => 'Enkapsulasi, Access Modifiers, dan Immutability',
                'description' => 'Melindungi state internal objek, getter/setter vs domain methods, dan defensive copying.',
                'mode' => 'offline',
            ],
            [
                'week' => 3,
                'title' => 'Pewarisan (Inheritance) & Polimorfisme Dinamis',
                'description' => 'Mekanisme virtual method table, method overriding, dan prinsip "favor composition over inheritance".',
                'mode' => 'hybrid',
            ],
            [
                'week' => 4,
                'title' => 'Interface, Abstract Class, dan Dependency Inversion',
                'description' => 'Mendesain kontrak API internal yang longgar dan mudah diuji dengan mock.',
                'mode' => 'offline',
            ],
            [
                'week' => 5,
                'title' => 'Prinsip SOLID: SRP, OCP, LSP',
                'description' => 'Bedah kode praktis menerapkan 3 prinsip pertama SOLID dengan studi kasus payment gateway.',
                'mode' => 'offline',
            ],
            [
                'week' => 6,
                'title' => 'Prinsip SOLID: ISP & DIP + Unit Testing Basics',
                'description' => 'Pemisahan interface granular dan injeksi ketergantungan otomatis via Service Container.',
                'mode' => 'offline',
            ],
            [
                'week' => 7,
                'title' => 'Design Patterns I: Creational (Factory, Builder, Singleton)',
                'description' => 'Pola pembuatan objek yang efisien, fleksibel, dan aman dari memory leak.',
                'mode' => 'online',
            ],
            [
                'week' => 8,
                'title' => 'Evaluasi Tengah Semester (UTS): Case Study Project Defense',
                'description' => 'Presentasi arsitektur dan live demo sistem mini perbankan berbasis OOP.',
                'mode' => 'offline',
            ],
        ];

        $meetings = [];
        foreach ($meetingsData as $mData) {
            $meetingDate = Carbon::now()->subWeeks(8 - $mData['week']);
            $meeting = Meeting::create([
                'class_id' => $class->id,
                'week' => $mData['week'],
                'title' => $mData['title'],
                'description' => $mData['description'],
                'date' => $meetingDate->toDateString(),
                'start_time' => '08:00',
                'end_time' => '10:30',
                'mode' => $mData['mode'],
                'is_published' => true,
            ]);

            $meetings[$mData['week']] = $meeting;

            // Seed Materials for each meeting
            Material::create([
                'meeting_id' => $meeting->id,
                'title' => "Slide Kuliah: {$mData['title']}",
                'type' => 'slide',
                'content' => "Ringkasan konsep dan diagram arsitektur materi minggu ke-{$mData['week']}.",
                'external_url' => 'https://speakerdeck.com/arunika/oop-week-'.$mData['week'],
                'is_downloadable' => true,
                'order' => 1,
            ]);

            Material::create([
                'meeting_id' => $meeting->id,
                'title' => 'Dokumen Panduan Praktikum & Source Code',
                'type' => 'pdf',
                'content' => 'Panduan langkah demi langkah implementasi kode di lingkungan lokal.',
                'file_path' => "/materials/week-{$mData['week']}-handout.pdf",
                'is_downloadable' => true,
                'order' => 2,
            ]);
        }

        // ==========================================
        // 7. ASSIGNMENTS & SUBMISSIONS
        // ==========================================
        $assignment1 = Assignment::create([
            'class_id' => $class->id,
            'meeting_id' => $meetings[2]->id,
            'title' => 'Tugas 1: Rancang Bangun Mini Core Banking System (OOP Principles)',
            'description' => 'Buatlah simulasi core banking dengan entitas Account, Customer, Transaction, dan Ledger. Wajib mengimplementasikan enkapsulasi ketat, validasi saldo, dan penanganan concurrency sederhana.',
            'max_score' => 100.00,
            'due_at' => Carbon::now()->subWeeks(5),
            'late_policy' => 'allow_penalty',
            'allow_resubmit' => true,
            'rubric' => [
                ['criteria' => 'Penerapan Enkapsulasi & Data Hiding', 'weight' => 30, 'max' => 30],
                ['criteria' => 'Pewarisan & Polimorfisme Account Types', 'weight' => 30, 'max' => 30],
                ['criteria' => 'Robust Exception Handling & Validasi', 'weight' => 20, 'max' => 20],
                ['criteria' => 'Kebersihan Kode & Dokumentasi PHPDoc', 'weight' => 20, 'max' => 20],
            ],
        ]);

        $assignment2 = Assignment::create([
            'class_id' => $class->id,
            'meeting_id' => $meetings[5]->id,
            'title' => 'Tugas 2: Refactoring Monolithic Checkout ke SOLID Architecture',
            'description' => 'Diberikan kode legacy sebuah sistem checkout e-commerce (500 baris dalam satu fungsi). Pecahlah menjadi classes modular yang mematuhi kelima prinsip SOLID.',
            'max_score' => 100.00,
            'due_at' => Carbon::now()->subWeeks(2),
            'late_policy' => 'allow_penalty',
            'allow_resubmit' => true,
            'rubric' => [
                ['criteria' => 'Kepatuhan Single Responsibility Principle', 'weight' => 25, 'max' => 25],
                ['criteria' => 'Open/Closed via Strategy Payment Method', 'weight' => 25, 'max' => 25],
                ['criteria' => 'Liskov Substitution & Interface Segregation', 'weight' => 25, 'max' => 25],
                ['criteria' => 'Dependency Inversion via Interface Injection', 'weight' => 25, 'max' => 25],
            ],
        ]);

        // Submission Arya Pratama on Assignment 1
        Submission::create([
            'assignment_id' => $assignment1->id,
            'user_id' => $studentArya->id,
            'content' => 'Berikut repository tugas saya: https://github.com/aryapratama/mini-banking-oop. Seluruh kriteria enkapsulasi dan immutability transaksi telah dipenuhi dengan unit testing PHPUnit.',
            'file_path' => '/submissions/arya-banking-v2.zip',
            'score' => 95.00,
            'status' => 'graded',
            'feedback' => 'Implementasi sangat rapi dan matang. Arsitektur objek sangat mencerminkan domain model dunia nyata. Pertahankan standar clean code ini!',
            'rubric_scores' => [
                'Penerapan Enkapsulasi & Data Hiding' => 30,
                'Pewarisan & Polimorfisme Account Types' => 28,
                'Robust Exception Handling & Validasi' => 19,
                'Kebersihan Kode & Dokumentasi PHPDoc' => 18,
            ],
            'submitted_at' => Carbon::now()->subWeeks(5)->subDays(1),
            'graded_at' => Carbon::now()->subWeeks(4),
            'graded_by' => $lecturer->id,
        ]);

        // Submission Siti on Assignment 1
        Submission::create([
            'assignment_id' => $assignment1->id,
            'user_id' => $studentSiti->id,
            'content' => 'Tugas 1 telah diunggah ke Google Drive dan GitHub. Mohon masukan terkait implementasi abstract class.',
            'file_path' => '/submissions/siti-banking-final.zip',
            'score' => 88.00,
            'status' => 'graded',
            'feedback' => 'Bagus sekali Siti. Sedikit catatan pada pengecekan saldo minus sebaiknya lempar InsufficientFundsException tersendiri.',
            'submitted_at' => Carbon::now()->subWeeks(5),
            'graded_at' => Carbon::now()->subWeeks(4),
            'graded_by' => $lecturer->id,
        ]);

        // ==========================================
        // 8. QUIZZES & QUESTIONS
        // ==========================================
        $quiz1 = Quiz::create([
            'class_id' => $class->id,
            'meeting_id' => $meetings[3]->id,
            'title' => 'Kuis 1: Uji Pemahaman Pilar OOP & Polimorfisme Dinamis',
            'description' => 'Kuis interaktif berdurasi 30 menit. Uji pemahaman mengenai mekanisme dynamic dispatch, abstraction, dan virtual table.',
            'duration_minutes' => 30,
            'max_attempts' => 1,
            'pass_score' => 70.00,
            'is_randomize_questions' => true,
            'is_randomize_options' => true,
        ]);

        // Question 1
        Question::create([
            'quiz_id' => $quiz1->id,
            'type' => 'multiple_choice',
            'prompt' => 'Manakah di antara pernyataan berikut yang paling tepat menjelaskan prinsip Enkapsulasi?',
            'options' => [
                'A' => 'Menyembunyikan detail implementasi internal dan hanya mengekspos antarmuka publik yang aman.',
                'B' => 'Kemampuan sebuah objek memiliki banyak bentuk saat runtime.',
                'C' => 'Mewariskan properti dari parent class ke child class tanpa modifikasi.',
                'D' => 'Menghubungkan kode dengan database secara langsung.',
            ],
            'correct_answer' => ['answer' => 'A'],
            'points' => 25.00,
            'bloom_level' => 'C2',
            'explanation' => 'Enkapsulasi bertujuan melindungi integritas state internal objek dari modifikasi tidak sah dari luar.',
        ]);

        // Question 2
        Question::create([
            'quiz_id' => $quiz1->id,
            'type' => 'multiple_choice',
            'prompt' => 'Mengapa prinsip "Favor Composition over Inheritance" sangat direkomendasikan dalam perancangan sistem enterprise?',
            'options' => [
                'A' => 'Karena inheritance tidak mendukung polymorphism.',
                'B' => 'Karena komposisi memberikan fleksibilitas mengubah perilaku objek pada saat runtime (loose coupling).',
                'C' => 'Karena composition lebih cepat dikompilasi oleh CPU.',
                'D' => 'Karena inheritance dilarang dalam standard PHP 8.',
            ],
            'correct_answer' => ['answer' => 'B'],
            'points' => 25.00,
            'bloom_level' => 'C4',
            'explanation' => 'Pewarisan menciptakan tight coupling ke parent class (fragile base class problem), sedangkan komposisi memungkinkan swap strategi saat runtime.',
        ]);

        // Question 3
        Question::create([
            'quiz_id' => $quiz1->id,
            'type' => 'multiple_choice',
            'prompt' => 'Pada prinsip Liskov Substitution Principle (LSP), apa yang terjadi jika sub-class melempar exception baru yang tidak pernah dideklarasikan oleh super-class?',
            'options' => [
                'A' => 'Kode tetap valid tanpa dampak.',
                'B' => 'Melanggar LSP karena client code yang mengonsumsi super-class tidak siap menangani exception tersebut.',
                'C' => 'Otomatis diabaikan oleh runtime engine.',
                'D' => 'Meningkatkan keamanan sistem secara otomatis.',
            ],
            'correct_answer' => ['answer' => 'B'],
            'points' => 25.00,
            'bloom_level' => 'C4',
            'explanation' => 'Sub-class harus dapat menggantikan super-class tanpa merusak ekspektasi perilaku client code.',
        ]);

        // Question 4
        Question::create([
            'quiz_id' => $quiz1->id,
            'type' => 'multiple_choice',
            'prompt' => 'Design pattern apakah yang paling tepat digunakan ketika sebuah sistem perlu membuat objek tanpa mengekspos logika instansiasi langsung ke client?',
            'options' => [
                'A' => 'Observer Pattern',
                'B' => 'Factory Method Pattern',
                'C' => 'Adapter Pattern',
                'D' => 'Decorator Pattern',
            ],
            'correct_answer' => ['answer' => 'B'],
            'points' => 25.00,
            'bloom_level' => 'C3',
            'explanation' => 'Factory Method mengisolasi pembuatan objek baru di bawah satu antarmuka kreasi khusus.',
        ]);

        // Quiz Attempt for Arya
        QuizAttempt::create([
            'quiz_id' => $quiz1->id,
            'user_id' => $studentArya->id,
            'attempt_number' => 1,
            'started_at' => Carbon::now()->subWeeks(4),
            'completed_at' => Carbon::now()->subWeeks(4)->addMinutes(18),
            'score' => 100.00,
            'answers' => [
                1 => 'A',
                2 => 'B',
                3 => 'B',
                4 => 'B',
            ],
            'is_passed' => true,
        ]);

        // ==========================================
        // 9. OBE SCORES & ATTAINMENT DATA
        // ==========================================
        OutcomeScore::create([
            'user_id' => $studentArya->id,
            'cpl_id' => $cpl1->id,
            'cpmk_id' => $cpmk1->id,
            'class_id' => $class->id,
            'score' => 95.00,
            'calculated_at' => Carbon::now()->subWeeks(3),
        ]);

        OutcomeScore::create([
            'user_id' => $studentArya->id,
            'cpl_id' => $cpl2->id,
            'cpmk_id' => $cpmk2->id,
            'class_id' => $class->id,
            'score' => 92.50,
            'calculated_at' => Carbon::now()->subWeeks(2),
        ]);

        OutcomeScore::create([
            'user_id' => $studentArya->id,
            'cpl_id' => $cpl3->id,
            'cpmk_id' => $cpmk3->id,
            'class_id' => $class->id,
            'score' => 88.00,
            'calculated_at' => Carbon::now()->subWeeks(1),
        ]);

        // ==========================================
        // 10. GAMIFICATION (XP & BADGES)
        // ==========================================
        $badge1 = Badge::create([
            'name' => 'First Blood (Tugas Pertama)',
            'slug' => 'first-blood',
            'description' => 'Mengunggah tugas perkuliahan pertama sebelum tenggat waktu.',
            'icon' => 'zap',
            'category' => 'academic',
            'xp_required' => 50,
        ]);

        $badge2 = Badge::create([
            'name' => 'Bug Hunter (Perfect Quiz)',
            'slug' => 'bug-hunter',
            'description' => 'Meraih nilai sempurna 100 pada kuis mata kuliah pilihan.',
            'icon' => 'target',
            'category' => 'mastery',
            'xp_required' => 100,
        ]);

        $badge3 = Badge::create([
            'name' => 'Architect Prodigy (OBE Star)',
            'slug' => 'architect-prodigy',
            'description' => 'Mencapai skor capaian CPL arsitektur di atas 90%.',
            'icon' => 'award',
            'category' => 'academic',
            'xp_required' => 200,
        ]);

        $badge4 = Badge::create([
            'name' => 'Consistency King (Presensi 100%)',
            'slug' => 'consistency-king',
            'description' => 'Hadir tepat waktu di setiap pertemuan selama setengah semester.',
            'icon' => 'clock',
            'category' => 'consistency',
            'xp_required' => 150,
        ]);

        // Award badges to Arya Pratama
        $studentArya->badges()->attach([
            $badge1->id => ['earned_at' => Carbon::now()->subWeeks(5)],
            $badge2->id => ['earned_at' => Carbon::now()->subWeeks(4)],
            $badge3->id => ['earned_at' => Carbon::now()->subWeeks(2)],
        ]);

        // XP Ledger entries for Arya
        XpLedger::create([
            'user_id' => $studentArya->id,
            'class_id' => $class->id,
            'amount' => 100,
            'source_type' => 'assignment',
            'source_id' => $assignment1->id,
            'description' => 'Menyelesaikan Tugas 1 Mini Banking System dengan nilai 95',
            'created_at' => Carbon::now()->subWeeks(4),
        ]);

        XpLedger::create([
            'user_id' => $studentArya->id,
            'class_id' => $class->id,
            'amount' => 150,
            'source_type' => 'quiz',
            'source_id' => $quiz1->id,
            'description' => 'Skor sempurna (100) pada Kuis 1 OOP & Polimorfisme',
            'created_at' => Carbon::now()->subWeeks(4),
        ]);

        XpLedger::create([
            'user_id' => $studentArya->id,
            'class_id' => $class->id,
            'amount' => 50,
            'source_type' => 'attendance',
            'description' => 'Presensi tepat waktu 5 minggu berturut-turut',
            'created_at' => Carbon::now()->subWeeks(3),
        ]);

        // ==========================================
        // 11. ATTENDANCE SESSION & LOGS
        // ==========================================
        $session = AttendanceSession::create([
            'meeting_id' => $meetings[1]->id,
            'qr_secret' => 'ARUNIKA-PRESENSI-W1-'.strtoupper(substr(md5(uniqid()), 0, 8)),
            'qr_expires_at' => Carbon::now()->subWeeks(7)->setTime(10, 30),
            'opened_at' => Carbon::now()->subWeeks(7)->setTime(8, 0),
            'closed_at' => Carbon::now()->subWeeks(7)->setTime(10, 30),
            'is_active' => false,
            'geofence' => [
                'latitude' => -6.200000,
                'longitude' => 106.816666,
                'radius_meters' => 100,
            ],
        ]);

        AttendanceLog::create([
            'attendance_session_id' => $session->id,
            'user_id' => $studentArya->id,
            'status' => 'present',
            'method' => 'qr',
            'ip_address' => '10.20.30.45',
            'checked_at' => Carbon::now()->subWeeks(7)->setTime(8, 5),
            'notes' => 'Presensi via Scan QR di kelas',
        ]);

        AttendanceLog::create([
            'attendance_session_id' => $session->id,
            'user_id' => $studentSiti->id,
            'status' => 'present',
            'method' => 'qr',
            'ip_address' => '10.20.30.50',
            'checked_at' => Carbon::now()->subWeeks(7)->setTime(8, 12),
            'notes' => 'Presensi via Scan QR di kelas',
        ]);

        // ==========================================
        // 12. DEMO REQUESTS (MARKETING WEBSITE)
        // ==========================================
        DemoRequest::create([
            'name' => 'Prof. Dr. Ir. Bambang Sujarwo, M.Sc.',
            'email' => 'bambang.sujarwo@universitas-merdeka.ac.id',
            'institution' => 'Universitas Merdeka Indonesia',
            'phone' => '081234567899',
            'role' => 'Wakil Rektor 1 (Bidang Akademik)',
            'students_count' => '15.000 - 25.000 Mahasiswa',
            'message' => 'Kami sedang mempersiapkan akreditasi institusi dan program studi internasional (ABET / ASIIN) serta LAM-PT. Sangat tertarik dengan native OBE engine dan visualisasi CPL otomatis di Arunika.',
            'status' => 'scheduled',
            'created_at' => Carbon::now()->subDays(2),
        ]);

        DemoRequest::create([
            'name' => 'Dr. Maya Anggraini, S.T., M.Kom.',
            'email' => 'maya.anggraini@politeknik-negeri.ac.id',
            'institution' => 'Politeknik Negeri Teknologi Terapan',
            'phone' => '081399881122',
            'role' => 'Kepala Badan Penjaminan Mutu & BAAK',
            'students_count' => '8.000 - 12.000 Mahasiswa',
            'message' => 'Ingin menjadwalkan demo untuk integrasi logbook magang MBKM, presensi QR geofencing, dan dashboard early warning risiko kelulusan mahasiswa.',
            'status' => 'pending',
            'created_at' => Carbon::now()->subHours(6),
        ]);

        // ==========================================
        // 13. CANONICAL STUDENT PORTAL DATASET
        // ==========================================
        $this->call(CanonicalStudentSeeder::class);

        // ==========================================
        // 14. DOSEN BATCH 1 & 2 PORTAL DATASET
        // ==========================================
        $this->call(LecturerBatch1Seeder::class);

        // ==========================================
        // 15. ADMINISTRATOR & MANAGEMENT DATASET
        // ==========================================
        $this->call(DemoSeeder::class);
    }
}
