<?php

namespace Database\Seeders;

use App\Models\AcademicClass;
use App\Models\DiscussionCategory;
use App\Models\DiscussionReply;
use App\Models\DiscussionThread;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LecturerDiscussionSeeder extends Seeder
{
    /**
     * Run the database seeds for Lecturer Discussion module.
     * Idempotent: Uses updateOrCreate to prevent duplication.
     */
    public function run(): void
    {
        $dosenBudi = User::where('email', 'dosen@arunika.test')->first()
            ?? User::where('email', 'budi.raharjo@unus.ac.id')->first();

        if (! $dosenBudi) {
            return;
        }

        // 1. Ensure Discussion Categories
        $catTanyaJawab = DiscussionCategory::firstOrCreate(
            ['slug' => 'tanya-jawab'],
            ['name' => 'Tanya Jawab', 'icon' => 'help-circle', 'color' => 'blue']
        );

        $catPengumuman = DiscussionCategory::firstOrCreate(
            ['slug' => 'pengumuman'],
            ['name' => 'Pengumuman', 'icon' => 'megaphone', 'color' => 'green']
        );

        $catDiskusiUmum = DiscussionCategory::firstOrCreate(
            ['slug' => 'diskusi-umum'],
            ['name' => 'Diskusi Umum', 'icon' => 'message-circle', 'color' => 'purple']
        );

        // 2. Identify Classes
        $classPboA = AcademicClass::where('lecturer_id', $dosenBudi->id)
            ->whereHas('course', fn ($q) => $q->where('code', 'IF2101'))
            ->where('section', 'Kelas A')
            ->first();

        $classBankingA = AcademicClass::where('lecturer_id', $dosenBudi->id)
            ->whereHas('course', fn ($q) => $q->where('code', 'IF2103'))
            ->where('section', 'Kelas A')
            ->first();

        if (! $classPboA) {
            return;
        }

        // 3. Students
        $userAhmad = User::where('email', 'ahmad.fauzi@student.unus.ac.id')->first() ?? $dosenBudi;
        $userSiti = User::where('email', 'siti.nurhaliza@student.unus.ac.id')->first() ?? $dosenBudi;
        $userRizky = User::where('email', 'rizky.pratama@student.unus.ac.id')->first() ?? $dosenBudi;
        $userDewi = User::where('email', 'dewi.lestari@student.unus.ac.id')->first() ?? $dosenBudi;
        $userFajar = User::where('email', 'fajar.nugroho@student.unus.ac.id')->first() ?? $dosenBudi;

        // 4. Seed the 7 Canonical Threads for IF2101 Kelas A (Screenshot 3)
        $threadsData = [
            // Thread 1: Rizky Pratama - Tanya Jawab
            [
                'title' => 'Pertanyaan tentang implementasi SOLID pada tugas 1',
                'content' => 'Halo Pak, saya masih bingung bagaimana cara menerapkan prinsip Dependency Inversion pada kasus yang diberikan, Apakah ada contoh sederhana yang bisa diterapkan pada arsitektur layer repository dan service?',
                'user' => $userRizky,
                'category' => $catTanyaJawab,
                'kategori' => 'tanya_jawab',
                'created_at' => Carbon::create(2026, 10, 12, 9, 24, 0),
                'replies_count' => 8,
                'views_count' => 38,
                'upvotes_count' => 5,
                'is_pinned' => false,
            ],
            // Thread 2: Dr. Budi Rahardjo - Pengumuman
            [
                'title' => 'Pengumuman deadline revisi tugas 1',
                'content' => 'Diberitahukan kepada seluruh mahasiswa, bahwa deadline revisi tugas 1 diperpanjang hingga 16 Oktober 2026 pukul 23.59. Terima kasih.',
                'user' => $dosenBudi,
                'category' => $catPengumuman,
                'kategori' => 'pengumuman',
                'created_at' => Carbon::create(2026, 10, 10, 14, 32, 0),
                'replies_count' => 12,
                'views_count' => 140,
                'upvotes_count' => 24,
                'is_pinned' => true,
            ],
            // Thread 3: Siti Nurhaliza - Diskusi Umum
            [
                'title' => 'Diskusi kelompok - Project Akhir',
                'content' => 'Untuk anggota kelompok yang belum bergabung, silakan join ke grup WhatsApp yang sudah dibuat. Terima kasih.',
                'user' => $userSiti,
                'category' => $catDiskusiUmum,
                'kategori' => 'diskusi_umum',
                'created_at' => Carbon::create(2026, 10, 9, 16, 20, 0),
                'replies_count' => 6,
                'views_count' => 45,
                'upvotes_count' => 3,
                'is_pinned' => false,
            ],
            // Thread 4: Ahmad Fauzi - Tanya Jawab
            [
                'title' => 'Cara menggunakan Git di tugas 2',
                'content' => 'Ada yang bisa jelaskan langkah-langkah push ke repository GitHub untuk tugas 2? Saya masih agak bingung dengan konfigurasi awalnya.',
                'user' => $userAhmad,
                'category' => $catTanyaJawab,
                'kategori' => 'tanya_jawab',
                'created_at' => Carbon::create(2026, 10, 8, 10, 15, 0),
                'replies_count' => 15,
                'views_count' => 78,
                'upvotes_count' => 9,
                'is_pinned' => false,
            ],
            // Thread 5: Dr. Budi Rahardjo - Pengumuman
            [
                'title' => 'Materi pertemuan 5 sudah tersedia',
                'content' => 'Materi pertemuan 5 tentang Design Pattern sudah saya upload di menu Materi Pembelajaran. Silakan dipelajari sebelum pertemuan berikutnya.',
                'user' => $dosenBudi,
                'category' => $catPengumuman,
                'kategori' => 'pengumuman',
                'created_at' => Carbon::create(2026, 10, 7, 17, 40, 0),
                'replies_count' => 3,
                'views_count' => 92,
                'upvotes_count' => 16,
                'is_pinned' => false,
            ],
            // Thread 6: Dewi Lestari - Diskusi Umum
            [
                'title' => 'Diskusi tentang arsitektur microservice',
                'content' => 'Menurut teman-teman, apakah microservice cocok untuk aplikasi skala kecil seperti tugas 2? Apa ada referensi yang bisa dibaca?',
                'user' => $userDewi,
                'category' => $catDiskusiUmum,
                'kategori' => 'diskusi_umum',
                'created_at' => Carbon::create(2026, 10, 6, 8, 55, 0),
                'replies_count' => 5,
                'views_count' => 34,
                'upvotes_count' => 4,
                'is_pinned' => false,
            ],
            // Thread 7: Fajar Nugroho - Tanya Jawab
            [
                'title' => 'Kesalahan saat menjalankan aplikasi',
                'content' => 'Saya mengalami error saat menjalankan aplikasi di local dengan Docker. Ada yang pernah mengalami hal serupa?',
                'user' => $userFajar,
                'category' => $catTanyaJawab,
                'kategori' => 'tanya_jawab',
                'created_at' => Carbon::create(2026, 10, 5, 20, 12, 0),
                'replies_count' => 4,
                'views_count' => 29,
                'upvotes_count' => 2,
                'is_pinned' => false,
            ],
        ];

        foreach ($threadsData as $data) {
            $thread = DiscussionThread::updateOrCreate(
                [
                    'class_id' => $classPboA->id,
                    'title' => $data['title'],
                ],
                [
                    'ulid' => (string) Str::ulid(),
                    'user_id' => $data['user']->id,
                    'category_id' => $data['category']->id,
                    'kategori' => $data['kategori'],
                    'content' => $data['content'],
                    'views_count' => $data['views_count'],
                    'upvotes_count' => $data['upvotes_count'],
                    'is_pinned' => $data['is_pinned'],
                    'is_locked' => false,
                    'is_private' => false,
                    'created_at' => $data['created_at'],
                    'updated_at' => $data['created_at'],
                ]
            );

            // Seed sample replies if empty
            if ($thread->replies()->count() < $data['replies_count']) {
                $needed = $data['replies_count'] - $thread->replies()->count();
                for ($i = 0; $i < $needed; $i++) {
                    DiscussionReply::create([
                        'ulid' => (string) Str::ulid(),
                        'thread_id' => $thread->id,
                        'user_id' => ($i % 2 === 0) ? $dosenBudi->id : $userAhmad->id,
                        'content' => 'Terima kasih atas diskusinya. Silakan cek dokumentasi panduan materi.',
                        'is_solution' => ($i === 0),
                        'upvotes_count' => rand(1, 5),
                        'created_at' => $data['created_at']->copy()->addHours($i + 1),
                        'updated_at' => $data['created_at']->copy()->addHours($i + 1),
                    ]);
                }
            }
        }

        // 5. Seed Threads for IF2103 Kelas A if available
        if ($classBankingA) {
            $bankingThreads = [
                [
                    'title' => 'Pertanyaan arsitektur ledger transaction perbankan',
                    'content' => 'Bagaimana skema idempotensi transaksi pada transfer antar rekening jika terjadi network timeout?',
                    'user' => $userRizky,
                    'category' => $catTanyaJawab,
                    'kategori' => 'tanya_jawab',
                    'created_at' => Carbon::create(2026, 10, 11, 10, 0, 0),
                ],
                [
                    'title' => 'Panduan instalasi database PostgreSQL lokal untuk modul 2',
                    'content' => 'Pastikan menggunakan PostgreSQL versi 16 ke atas dan aktifkan extension uuid-ossp.',
                    'user' => $dosenBudi,
                    'category' => $catPengumuman,
                    'kategori' => 'pengumuman',
                    'created_at' => Carbon::create(2026, 10, 9, 14, 0, 0),
                ],
            ];

            foreach ($bankingThreads as $bData) {
                DiscussionThread::updateOrCreate(
                    [
                        'class_id' => $classBankingA->id,
                        'title' => $bData['title'],
                    ],
                    [
                        'ulid' => (string) Str::ulid(),
                        'user_id' => $bData['user']->id,
                        'category_id' => $bData['category']->id,
                        'kategori' => $bData['kategori'],
                        'content' => $bData['content'],
                        'views_count' => 15,
                        'upvotes_count' => 3,
                        'is_pinned' => false,
                        'is_locked' => false,
                        'is_private' => false,
                        'created_at' => $bData['created_at'],
                        'updated_at' => $bData['created_at'],
                    ]
                );
            }
        }
    }
}
