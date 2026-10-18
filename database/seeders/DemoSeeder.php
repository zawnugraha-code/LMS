<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\AcademicClass;
use App\Models\ActivityLog;
use App\Models\Campus;
use App\Models\Course;
use App\Models\CourseAssessmentComponent;
use App\Models\Curriculum;
use App\Models\Enrollment;
use App\Models\Meeting;
use App\Models\Program;
use App\Models\Term;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    /**
     * Seed demo data for Admin and Campus Management.
     * Guarded: Will throw exception if run in production.
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new \RuntimeException('DemoSeeder dilarang dijalankan di environment production!');
        }

        $password = Hash::make('password');
        $campus = Campus::first();
        $curriculum = Curriculum::first();
        $prodi = Program::first();

        // ==========================================
        // 1. AKUN ADMINISTRATOR UTAMA
        // ==========================================
        $admin = User::updateOrCreate(
            ['email' => 'admin@arunika.test'],
            [
                'name' => 'Rina Kusumawati',
                'gelar' => 'Rina Kusumawati, S.Kom., M.T.',
                'password' => $password,
                'campus_id' => $campus?->id,
                'role' => UserRole::Admin,
                'identifier' => 'ADM-2026-001',
                'prodi' => 'Biro Administrasi Akademik & Kemahasiswaan',
                'jabatan' => 'Kepala Bagian Akademik',
                'phone' => '081122334455',
                'locale' => 'id',
                'timezone' => 'Asia/Jakarta',
                'is_active' => true,
                'must_change_password' => false,
                'email_verified_at' => now(),
                'last_login_at' => now()->subMinutes(12),
                'password_changed_at' => now()->subDays(30),
            ]
        );

        // ==========================================
        // 2. DATA SEMESTER (AKTIF, BELUM AKTIF, SELESAI)
        // ==========================================
        $termAktif = Term::where('is_active', true)->first() ?? Term::first();
        if ($termAktif) {
            $termAktif->update([
                'status' => 'aktif',
                'is_active' => true,
            ]);
        }

        // Semester Genap Mendatang (Belum Aktif)
        $termGenapMendatang = Term::updateOrCreate(
            [
                'academic_year' => '2026/2027',
                'semester_type' => 'even',
            ],
            [
                'campus_id' => $campus?->id,
                'name' => 'Semester Genap 2026/2027',
                'start_date' => Carbon::create(2027, 2, 15),
                'end_date' => Carbon::create(2027, 7, 15),
                'is_active' => false,
                'status' => 'belum_aktif',
                'closed_at' => null,
            ]
        );

        // Semester Genap Lalu (Selesai)
        $termGenapLalu = Term::updateOrCreate(
            [
                'academic_year' => '2025/2026',
                'semester_type' => 'even',
            ],
            [
                'campus_id' => $campus?->id,
                'name' => 'Semester Genap 2025/2026',
                'start_date' => Carbon::create(2026, 2, 16),
                'end_date' => Carbon::create(2026, 7, 31),
                'is_active' => false,
                'status' => 'selesai',
                'closed_at' => Carbon::create(2026, 7, 31, 23, 59, 59),
            ]
        );

        // ==========================================
        // 3. DOSEN TAMBAHAN (SESUAI MOCKUP SCREENSHOT)
        // ==========================================
        $dosenSiti = User::updateOrCreate(
            ['email' => 'siti.aminah@uni.ac.id'],
            [
                'name' => 'Siti Aminah',
                'gelar' => 'Siti Aminah, S.Kom., M.Cs.',
                'password' => $password,
                'campus_id' => $campus?->id,
                'role' => UserRole::Lecturer,
                'identifier' => '0015088201',
                'prodi' => 'S1 Teknik Informatika',
                'jabatan' => 'Asisten Ahli',
                'phone' => '081234567890',
                'is_active' => true,
                'locale' => 'id',
                'timezone' => 'Asia/Jakarta',
            ]
        );

        $dosenHendra = User::updateOrCreate(
            ['email' => 'hendra.wijaya@uni.ac.id'],
            [
                'name' => 'Hendra Wijaya',
                'gelar' => 'Dr. Hendra Wijaya, M.Kom.',
                'password' => $password,
                'campus_id' => $campus?->id,
                'role' => UserRole::Lecturer,
                'identifier' => '0023048502',
                'prodi' => 'S1 Teknik Informatika',
                'jabatan' => 'Lektor',
                'phone' => '081234567891',
                'is_active' => true,
                'locale' => 'id',
                'timezone' => 'Asia/Jakarta',
            ]
        );

        // ==========================================
        // 4. MATA KULIAH IF2105 (PEMROGRAMAN WEB LANJUT)
        // ==========================================
        $courseWeb = Course::updateOrCreate(
            ['code' => 'IF2105'],
            [
                'curriculum_id' => $curriculum?->id,
                'name' => 'Pemrograman Web Lanjut',
                'credits' => 3,
                'semester' => 3,
                'description' => 'Mata kuliah pengembangan web modern berbasis framework Laravel, REST API, Tailwind CSS, dan arsitektur microservices.',
                'syllabus' => '1. Pengantar Web Modern; 2. Routing & Middleware; 3. ORM & Migrasi; 4. Blade & Tailwind; 5. REST API & Auth; 6. Pengujian Otomatis.',
            ]
        );

        // ==========================================
        // 5. KELAS A IF2105 (KAPASITAS 40, TERISI 32 = 80%)
        // ==========================================
        $classWebA = AcademicClass::updateOrCreate(
            [
                'course_id' => $courseWeb->id,
                'term_id' => $termAktif->id,
                'section' => 'A',
            ],
            [
                'lecturer_id' => $dosenSiti->id,
                'room' => 'Lab Komputer 3',
                'schedule_day' => 'Selasa',
                'start_time' => '08:00:00',
                'end_time' => '10:30:00',
                'capacity' => 40,
                'status' => 'aktif',
                'settings' => [
                    'is_locked' => false,
                ],
            ]
        );

        // Buat 16 Pertemuan untuk Kelas IF2105 A jika belum ada
        if ($classWebA->meetings()->count() === 0) {
            $startDate = Carbon::parse($termAktif->start_date ?? '2026-09-01');
            while ($startDate->dayOfWeek !== Carbon::TUESDAY) {
                $startDate->addDay();
            }

            for ($w = 1; $w <= 16; $w++) {
                Meeting::create([
                    'class_id' => $classWebA->id,
                    'week' => $w,
                    'title' => "Pertemuan {$w}",
                    'description' => $w === 8 ? 'Ujian Tengah Semester (UTS)' : ($w === 16 ? 'Ujian Akhir Semester (UAS)' : "Materi Pekan ke-{$w} Pemrograman Web Lanjut"),
                    'date' => $startDate->copy()->addWeeks($w - 1),
                    'start_time' => '08:00:00',
                    'end_time' => '10:30:00',
                    'mode' => 'offline',
                    'is_published' => true,
                ]);
            }
        }

        // Buat Komponen Penilaian Default jika belum ada
        if ($classWebA->assessmentComponents()->count() === 0) {
            $components = [
                ['jenis' => 'tugas', 'bobot' => 30],
                ['jenis' => 'kuis', 'bobot' => 20],
                ['jenis' => 'uts', 'bobot' => 25],
                ['jenis' => 'uas', 'bobot' => 25],
            ];

            foreach ($components as $comp) {
                CourseAssessmentComponent::create([
                    'class_id' => $classWebA->id,
                    'jenis' => $comp['jenis'],
                    'bobot' => $comp['bobot'],
                ]);
            }
        }

        // ==========================================
        // 6. MAHASISWA & PENDAFTARAN PESERTA (ENROLLMENT)
        // ==========================================
        // Ambil mahasiswa yang sudah ada di database
        $existingStudents = User::where('role', UserRole::Student)->orderBy('id')->get();

        // Daftarkan tepat 32 mahasiswa ke Kelas A IF2105 (80% terisi dari 40 kapasitas)
        $studentsToEnroll = $existingStudents->take(32);
        foreach ($studentsToEnroll as $student) {
            Enrollment::updateOrCreate(
                [
                    'class_id' => $classWebA->id,
                    'user_id' => $student->id,
                ],
                [
                    'status' => 'aktif',
                    'created_at' => Carbon::now()->subDays(10),
                ]
            );
        }

        // Buat 5 Mahasiswa "Belum Terdaftar" di kelas manapun (untuk widget screenshot)
        $unenrolledNames = [
            ['name' => 'Dimas Pratama', 'email' => 'dimas.pratama@mhs.uni.ac.id', 'nim' => '24060122130001'],
            ['name' => 'Farhan Kurniawan', 'email' => 'farhan.kurniawan@mhs.uni.ac.id', 'nim' => '24060122130002'],
            ['name' => 'Gita Anggraini', 'email' => 'gita.anggraini@mhs.uni.ac.id', 'nim' => '24060122130003'],
            ['name' => 'Hani Rahmawati', 'email' => 'hani.rahmawati@mhs.uni.ac.id', 'nim' => '24060122130004'],
            ['name' => 'Indra Wijaya', 'email' => 'indra.wijaya@mhs.uni.ac.id', 'nim' => '24060122130005'],
        ];

        foreach ($unenrolledNames as $mhs) {
            User::updateOrCreate(
                ['email' => $mhs['email']],
                [
                    'name' => $mhs['name'],
                    'password' => $password,
                    'campus_id' => $campus?->id,
                    'role' => UserRole::Student,
                    'identifier' => $mhs['nim'],
                    'prodi' => 'S1 Teknik Informatika',
                    'is_active' => true,
                    'locale' => 'id',
                    'timezone' => 'Asia/Jakarta',
                ]
            );
        }

        // ==========================================
        // 7. 6 DATA CONTOH LOG AKTIVITAS (activity_logs)
        // ==========================================
        if (ActivityLog::count() === 0) {
            $logs = [
                [
                    'aksi' => 'login',
                    'kategori' => 'autentikasi',
                    'deskripsi' => 'Administrator Rina Kusumawati berhasil masuk ke sistem',
                    'subjek_type' => User::class,
                    'subjek_id' => (string) $admin->id,
                    'properti' => ['browser' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', 'auth_method' => 'web_session'],
                    'created_at' => Carbon::now()->subMinutes(12),
                ],
                [
                    'aksi' => 'buat_mata_kuliah',
                    'kategori' => 'akademik',
                    'deskripsi' => 'Membuat master mata kuliah baru: IF2105 - Pemrograman Web Lanjut (3 SKS)',
                    'subjek_type' => Course::class,
                    'subjek_id' => (string) $courseWeb->id,
                    'properti' => ['code' => 'IF2105', 'credits' => 3, 'semester' => 3],
                    'created_at' => Carbon::now()->subHours(2),
                ],
                [
                    'aksi' => 'buka_kelas',
                    'kategori' => 'akademik',
                    'deskripsi' => 'Membuka kelas baru: IF2105 Seksi A di Lab Komputer 3 kapasitas 40 kursi',
                    'subjek_type' => AcademicClass::class,
                    'subjek_id' => (string) $classWebA->id,
                    'properti' => ['course_code' => 'IF2105', 'section' => 'A', 'capacity' => 40],
                    'created_at' => Carbon::now()->subHours(1)->subMinutes(45),
                ],
                [
                    'aksi' => 'tugaskan_dosen',
                    'kategori' => 'penugasan',
                    'deskripsi' => 'Menunjuk Siti Aminah, S.Kom., M.Cs. sebagai Dosen Pengampu IF2105 Seksi A',
                    'subjek_type' => AcademicClass::class,
                    'subjek_id' => (string) $classWebA->id,
                    'properti' => ['lecturer_id' => $dosenSiti->id, 'lecturer_name' => $dosenSiti->name],
                    'created_at' => Carbon::now()->subHours(1)->subMinutes(30),
                ],
                [
                    'aksi' => 'import_mahasiswa',
                    'kategori' => 'pendaftaran',
                    'deskripsi' => 'Mendaftarkan 32 mahasiswa ke kelas IF2105 Seksi A via import berkas CSV',
                    'subjek_type' => AcademicClass::class,
                    'subjek_id' => (string) $classWebA->id,
                    'properti' => ['enrolled_count' => 32, 'class_capacity' => 40, 'percent_filled' => 80],
                    'created_at' => Carbon::now()->subHours(1),
                ],
                [
                    'aksi' => 'perbarui_semester',
                    'kategori' => 'konfigurasi',
                    'deskripsi' => 'Mengatur status kalender akademik: Semester Ganjil 2026/2027 ditetapkan Aktif',
                    'subjek_type' => Term::class,
                    'subjek_id' => (string) $termAktif->id,
                    'properti' => ['term_name' => $termAktif->name, 'status' => 'aktif'],
                    'created_at' => Carbon::now()->subMinutes(40),
                ],
            ];

            foreach ($logs as $logData) {
                ActivityLog::create(array_merge($logData, [
                    'user_id' => $admin->id,
                    'ip_address' => '127.0.0.1',
                ]));
            }
        }
    }
}
