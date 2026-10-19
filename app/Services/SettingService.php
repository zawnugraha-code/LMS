<?php

namespace App\Services;

use App\Models\Campus;
use App\Models\Faculty;
use App\Models\Program;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use InvalidArgumentException;

class SettingService
{
    public function __construct(
        protected PreferenceService $preferenceService
    ) {}

    /**
     * Retrieve complete settings context for the active user.
     *
     * @return array<string, mixed>
     */
    public function getSettingData(User $user): array
    {
        $campus = $user->campus ?? Campus::first();
        $preference = $this->preferenceService->getForUser($user);

        // Resolve student program and faculty from active enrollment or fallbacks
        $enrollment = $user->enrollments()
            ->with('academicClass.course.curriculum.program.faculty')
            ->first();

        $program = $enrollment?->academicClass?->course?->curriculum?->program
            ?? Program::with('faculty')->first();

        $faculty = $program?->faculty ?? Faculty::first();

        return [
            'user' => $user,
            'campus' => $campus,
            'preference' => $preference,
            'program' => $program,
            'faculty' => $faculty,
            'currentRole' => $user->role,
        ];
    }

    /**
     * Update user profile information and optional avatar upload.
     */
    public function updateProfile(User $user, array $data, ?UploadedFile $avatar = null): User
    {
        if (isset($data['name'])) {
            $user->name = trim($data['name']);
        }

        if (isset($data['email'])) {
            $user->email = trim($data['email']);
        }

        if (isset($data['phone'])) {
            $user->phone = trim($data['phone']);
        }

        if ($avatar) {
            $filename = 'avatar_'.$user->id.'_'.time().'.'.$avatar->getClientOriginalExtension();
            $path = $avatar->storeAs('avatars', $filename, 'public');
            $user->avatar_path = '/storage/'.$path;
        }

        $user->save();

        return $user;
    }

    /**
     * Verify current password and update to new password.
     *
     * @throws InvalidArgumentException
     */
    public function updatePassword(User $user, string $currentPassword, string $newPassword): User
    {
        if (! Hash::check($currentPassword, $user->password)) {
            throw new InvalidArgumentException('Password saat ini tidak sesuai.');
        }

        $user->password = Hash::make($newPassword);
        $user->save();

        return $user;
    }

    /**
     * Update user preferences in both user_preferences table and user settings json.
     */
    public function updatePreferences(User $user, array $payload): UserPreference
    {
        $preference = $this->preferenceService->getForUser($user);
        $updateFields = [];

        // 1. Theme translation
        if (isset($payload['theme'])) {
            $themeMap = [
                'terang' => 'light',
                'gelap' => 'dark',
                'otomatis' => 'auto',
                'light' => 'light',
                'dark' => 'dark',
                'auto' => 'auto',
            ];
            $updateFields['theme'] = $themeMap[$payload['theme']] ?? 'light';
        }

        // 2. Font / text size translation
        if (isset($payload['textSize']) || isset($payload['font_size'])) {
            $rawSize = $payload['textSize'] ?? $payload['font_size'];
            $sizeMap = [
                'kecil' => 'small',
                'normal' => 'normal',
                'besar' => 'large',
                'small' => 'small',
                'large' => 'large',
            ];
            $updateFields['font_size'] = $sizeMap[$rawSize] ?? 'normal';
        }

        // 3. Localization
        if (isset($payload['language']) || isset($payload['locale'])) {
            $updateFields['locale'] = $payload['language'] ?? $payload['locale'];
            $user->locale = $updateFields['locale'];
        }

        if (isset($payload['timezone'])) {
            $updateFields['timezone'] = $payload['timezone'];
            $user->timezone = $updateFields['timezone'];
        }

        if (isset($payload['dateFormat']) || isset($payload['date_format'])) {
            $updateFields['date_format'] = $payload['dateFormat'] ?? $payload['date_format'];
        }

        if (isset($payload['timeFormat']) || isset($payload['time_format'])) {
            $updateFields['time_format'] = $payload['timeFormat'] ?? $payload['time_format'];
        }

        // 4. Two Factor Authentication
        if (array_key_exists('twoFactorEnabled', $payload) || array_key_exists('two_factor_enabled', $payload)) {
            $raw2fa = $payload['twoFactorEnabled'] ?? $payload['two_factor_enabled'];
            $updateFields['two_factor_enabled'] = filter_var($raw2fa, FILTER_VALIDATE_BOOLEAN);
        }

        // 5. Google SSO Connection
        if (array_key_exists('googleConnected', $payload) || array_key_exists('google_id', $payload)) {
            $rawGoogle = $payload['googleConnected'] ?? $payload['google_id'];
            $isConnected = filter_var($rawGoogle, FILTER_VALIDATE_BOOLEAN) || ! empty($payload['google_id']);
            $updateFields['google_id'] = $isConnected ? ($preference->google_id ?: 'google_'.Str::random(12)) : null;
        }

        // 6. Notification preferences
        $currentNotifs = $preference->notifications ?? [
            'class_announcements' => true,
            'assignments_deadlines' => true,
            'private_messages' => true,
            'discussion_activities' => false,
            'system_notifications' => true,
        ];

        $notifKeys = [
            'notifPengumuman' => 'class_announcements',
            'class_announcements' => 'class_announcements',
            'notifTugas' => 'assignments_deadlines',
            'assignments_deadlines' => 'assignments_deadlines',
            'notifPesan' => 'private_messages',
            'private_messages' => 'private_messages',
            'notifDiskusi' => 'discussion_activities',
            'discussion_activities' => 'discussion_activities',
            'notifSistem' => 'system_notifications',
            'system_notifications' => 'system_notifications',
        ];

        $hasNotifChange = false;
        foreach ($notifKeys as $inputKey => $dbKey) {
            if (array_key_exists($inputKey, $payload)) {
                $currentNotifs[$dbKey] = filter_var($payload[$inputKey], FILTER_VALIDATE_BOOLEAN);
                $hasNotifChange = true;
            }
        }

        if ($hasNotifChange) {
            $updateFields['notifications'] = $currentNotifs;
        }

        // Persist UserPreference model
        $preference->update($updateFields);

        // Also sync settings json column on User model for complete consistency
        $userSettings = $user->settings ?? [];
        $user->settings = array_merge($userSettings, [
            'theme' => $preference->theme,
            'font_size' => $preference->font_size,
            'locale' => $preference->locale,
            'timezone' => $preference->timezone,
            'date_format' => $preference->date_format,
            'time_format' => $preference->time_format,
            'two_factor_enabled' => $preference->two_factor_enabled,
            'google_connected' => ! empty($preference->google_id),
            'notifications' => $preference->notifications,
        ]);
        $user->save();

        return $preference;
    }

    /**
     * Create a support ticket from the settings or support form.
     */
    public function createSupportTicket(User $user, array $data, ?UploadedFile $attachment = null): SupportTicket
    {
        $attachmentPath = null;
        if ($attachment) {
            $filename = 'support_'.$user->id.'_'.time().'.'.$attachment->getClientOriginalExtension();
            $path = $attachment->storeAs('support', $filename, 'private');
            $attachmentPath = $path;
        }

        return SupportTicket::create([
            'ulid' => (string) Str::ulid(),
            'user_id' => $user->id,
            'subject' => $data['subject'] ?? 'Pertanyaan Bantuan Pengguna',
            'category' => $data['category'] ?? 'teknis',
            'message' => $data['message'] ?? '',
            'attachment_path' => $attachmentPath,
            'status' => 'open',
        ]);
    }

    /**
     * Prepare data for secondary settings sub-pages (Item 14 of docs/pages-without-design.md).
     *
     * @return array<string, mixed>
     */
    public function getSectionData(User $user, string $section): array
    {
        $baseData = $this->getSettingData($user);

        // Normalize section slug
        $normalized = Str::slug($section);

        $sections = [
            'aplikasi-terhubung' => [
                'title' => 'Aplikasi Terhubung',
                'subtitle' => 'Kelola integrasi dengan aplikasi pihak ketiga dan layanan eksternal kampus.',
                'icon' => 'share-2',
                'apps' => [
                    [
                        'name' => 'Google Workspace for Education',
                        'description' => 'Sinkronisasi email kampus, kalender perkuliahan, dan Google Meet.',
                        'icon' => 'google',
                        'connected' => ! empty($baseData['preference']->google_id),
                        'last_sync' => '10 menit yang lalu',
                        'scope' => 'Email, Kalender, Google Drive',
                    ],
                    [
                        'name' => 'Zoom Video Communications',
                        'description' => 'Akses otomatis ke ruang kelas tatap maya dan rekaman perkuliahan.',
                        'icon' => 'video',
                        'connected' => true,
                        'last_sync' => '2 jam yang lalu',
                        'scope' => 'Join Meeting, Host Lecture',
                    ],
                    [
                        'name' => 'GitHub Education Student Pack',
                        'description' => 'Pengumpulan tugas praktikum coding dan repositori tugas akhir.',
                        'icon' => 'github',
                        'connected' => true,
                        'last_sync' => 'Kemarin, 19:40',
                        'scope' => 'Repositori, Commit Sign-off',
                    ],
                    [
                        'name' => 'Mendeley Reference Manager',
                        'description' => 'Sinkronisasi sitasi jurnal ilmiah dan perpustakaan digital kampus.',
                        'icon' => 'book-open',
                        'connected' => false,
                        'last_sync' => null,
                        'scope' => 'Referensi, Koleksi Sitasi',
                    ],
                    [
                        'name' => 'Turnitin Similarity Checker',
                        'description' => 'Pemeriksaan orisinalitas otomatis pada berkas tugas dan laporan.',
                        'icon' => 'file-check',
                        'connected' => true,
                        'last_sync' => '3 hari yang lalu',
                        'scope' => 'Simpan Dokumen, Skor Plagiasi',
                    ],
                ],
            ],
            'integrasi' => 'aplikasi-terhubung', // alias
            'webhook-api' => [
                'title' => 'Webhook & API',
                'subtitle' => 'Kelola token API pribadi dan webhook untuk automasi kegiatan akademik Anda.',
                'icon' => 'link',
                'tokens' => [
                    [
                        'name' => 'Personal Academic CLI Token',
                        'prefix' => 'arn_pat_9a7d...',
                        'created_at' => '12 September 2026',
                        'last_used' => 'Hari ini, 09:15',
                        'expires_at' => '12 September 2027',
                        'scopes' => ['read:courses', 'read:assignments', 'write:submissions'],
                    ],
                    [
                        'name' => 'Home Assistant / Calendar Sync',
                        'prefix' => 'arn_pat_3f8b...',
                        'created_at' => '28 Agustus 2026',
                        'last_used' => 'Kemarin, 22:00',
                        'expires_at' => '28 Agustus 2027',
                        'scopes' => ['read:schedule', 'read:notifications'],
                    ],
                ],
                'endpoints' => [
                    'GET /api/v1/user/profile' => 'Mendapatkan profil dan metadata mahasiswa.',
                    'GET /api/v1/courses' => 'Daftar kelas dan mata kuliah yang diambil.',
                    'GET /api/v1/assignments' => 'Tugas, deadline, dan status pengumpulan.',
                    'POST /api/v1/attendance/check-in' => 'Melakukan presensi melalui token kode/QR.',
                ],
            ],
            'api' => 'webhook-api', // alias
            'bantuan-dukungan' => [
                'title' => 'Bantuan & Dukungan',
                'subtitle' => 'Pusat bantuan teknis, panduan penggunaan, dan kontak tim support Arunika.',
                'icon' => 'help-circle',
                'faqs' => [
                    [
                        'q' => 'Bagaimana jika saya terlambat melakukan presensi kuliah?',
                        'a' => 'Jika sesi presensi telah ditutup oleh dosen, Anda dapat mengajukan Surat Izin / Sakit melalui menu Presensi dengan melampirkan bukti surat keterangan resmi.',
                    ],
                    [
                        'q' => 'Mengapa file tugas saya gagal diunggah?',
                        'a' => 'Pastikan ukuran file di bawah 25 MB dan format file sesuai dengan ketentuan (PDF, ZIP, DOCX). Jika kendala berlanjut, hubungi admin melalui form tiket di bawah.',
                    ],
                    [
                        'q' => 'Bagaimana cara mengunduh Kartu Hasil Studi (KHS) resmi?',
                        'a' => 'Buka menu Nilai, lalu klik tombol "Unduh KHS (PDF)". Dokumen KHS dilengkapi QR Code validasi keaslian dari Biro Akademik.',
                    ],
                    [
                        'q' => 'Bagaimana sistem penilaian Outcome-Based Education (OBE) bekerja?',
                        'a' => 'Setiap nilai tugas, kuis, dan ujian dipetakan ke CPMK dan CPL program studi Anda. Anda dapat melihat radar capaian di menu Capaian CPL.',
                    ],
                ],
                'tickets' => SupportTicket::where('user_id', $user->id)->latest()->take(5)->get(),
            ],
            'bantuan' => 'bantuan-dukungan', // alias
            'jadwal' => [
                'title' => 'Jadwal & Pengingat',
                'subtitle' => 'Atur frekuensi pengingat jadwal kuliah dan tenggat pengumpulan tugas.',
                'icon' => 'calendar',
                'reminders' => [
                    ['label' => 'Pengingat Mulai Kuliah', 'desc' => 'Kirim notifikasi 15 menit sebelum perkuliahan dimulai.', 'enabled' => true],
                    ['label' => 'Pengingat Deadline Tugas (24 Jam)', 'desc' => 'Kirim email dan notifikasi push 1 hari sebelum batas waktu.', 'enabled' => true],
                    ['label' => 'Pengingat Deadline Mendesak (3 Jam)', 'desc' => 'Kirim alarm notifikasi mendesak sebelum submission ditutup.', 'enabled' => true],
                    ['label' => 'Ringkasan Akademik Mingguan', 'desc' => 'Kirim intisari agenda kuliah setiap hari Minggu malam.', 'enabled' => false],
                ],
            ],
            'preferensi' => [
                'title' => 'Preferensi Pembelajaran',
                'subtitle' => 'Sesuaikan gaya belajar, asisten AI Copilot, dan fitur aksesibilitas Anda.',
                'icon' => 'sparkles',
                'options' => [
                    ['label' => 'Bimbingan Rekomendasi AI Copilot', 'desc' => 'Tampilkan saran modul belajar berdasarkan ketercapaian CPMK.', 'enabled' => true],
                    ['label' => 'Gamifikasi & Animasi XP', 'desc' => 'Tampilkan efek perolehan XP dan kenaikan level akademik.', 'enabled' => true],
                    ['label' => 'Mode Kontras Tinggi', 'desc' => 'Optimalkan keterbacaan teks untuk layar dengan pencahayaan tinggi.', 'enabled' => false],
                    ['label' => 'Simpan Otomatis Jawaban Kuis', 'desc' => 'Auto-save draf jawaban ujian setiap 30 detik ke server.', 'enabled' => true],
                ],
            ],
        ];

        // Resolve alias if mapped
        if (isset($sections[$normalized]) && is_string($sections[$normalized])) {
            $normalized = $sections[$normalized];
        }

        $sectionConfig = $sections[$normalized] ?? $sections['aplikasi-terhubung'];

        return array_merge($baseData, [
            'sectionKey' => $normalized,
            'sectionConfig' => $sectionConfig,
        ]);
    }
}
