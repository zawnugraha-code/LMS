<?php

namespace Tests\Feature;

use App\Helpers\NotificationHelper;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\LecturerBatch1Seeder;
use Database\Seeders\LecturerDiscussionSeeder;
use Database\Seeders\LecturerPresensiSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LecturerSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->seed(LecturerBatch1Seeder::class);
        $this->seed(LecturerPresensiSeeder::class);
        $this->seed(LecturerDiscussionSeeder::class);
    }

    /**
     * Test lecturer can view the settings page with complete profile, teaching load, and system status data.
     */
    public function test_lecturer_can_view_pengaturan_page_with_complete_data(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();

        $response = $this->actingAs($dosen)->get(route('dosen.pengaturan.edit'));

        $response->assertStatus(200);
        $response->assertSee('Pengaturan Akun', false);
        $response->assertSee('Biodata', false);
        $response->assertSee('Preferensi Notifikasi');
        $response->assertSee('Keamanan', false);
        $response->assertSee('Status Sistem', false);

        // Check canonical lecturer data in form
        $response->assertSee($dosen->name);
        $response->assertSee($dosen->email);

        // Check teaching load
        $response->assertViewHas('classesCount', 3);
        $response->assertViewHas('studentsCount', 90);

        // Check honest system status
        $response->assertViewHas('systemStatus', function ($status) {
            return $status['db_connected'] === true
                && str_contains($status['app_version'], 'Batch 2');
        });
    }

    /**
     * Test lecturer can update profile information.
     */
    public function test_lecturer_can_update_profile_information(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();

        $payload = [
            'name' => 'Dr. Budi Rahardjo, Ph.D.',
            'gelar' => 'Dr. Budi Rahardjo, S.T., M.Kom., Ph.D.',
            'email' => 'budi.rahardjo.new@arunika.ac.id',
            'phone' => '081234567890',
            'identifier' => '0021037502',
            'jabatan' => 'Profesor / Guru Besar',
            'prodi' => 'S1 Teknik Informatika - FKOM',
        ];

        $response = $this->actingAs($dosen)->post(route('dosen.pengaturan.profile'), $payload);

        $response->assertRedirect(route('dosen.pengaturan.edit'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $dosen->id,
            'name' => 'Dr. Budi Rahardjo, Ph.D.',
            'email' => 'budi.rahardjo.new@arunika.ac.id',
            'jabatan' => 'Profesor / Guru Besar',
        ]);
    }

    /**
     * Test lecturer can update password with valid current password.
     */
    public function test_lecturer_can_update_password_with_valid_current_password(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();

        $payload = [
            'current_password' => 'password',
            'password' => 'newpassword1234',
            'password_confirmation' => 'newpassword1234',
        ];

        $response = $this->actingAs($dosen)->post(route('dosen.pengaturan.password'), $payload);

        $response->assertRedirect(route('dosen.pengaturan.edit'));
        $response->assertSessionHas('success');

        $this->assertTrue(Hash::check('newpassword1234', $dosen->fresh()->password));
    }

    /**
     * Test updating password fails if current password does not match.
     */
    public function test_update_password_fails_with_invalid_current_password(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();

        $payload = [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword1234',
            'password_confirmation' => 'newpassword1234',
        ];

        $response = $this->actingAs($dosen)->post(route('dosen.pengaturan.password'), $payload);

        $response->assertSessionHasErrors(['current_password']);
        $this->assertTrue(Hash::check('password', $dosen->fresh()->password));
    }

    /**
     * Test lecturer can update notification preferences and it directly controls NotificationHelper::shouldNotify().
     */
    public function test_lecturer_can_update_notification_preferences_and_affects_should_notify(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();

        // 1. Submit form with 'tugas' and 'ujian' enabled, 'presensi' and 'diskusi' disabled
        $payload = [
            'tugas' => '1',
            'ujian' => '1',
            // presensi and diskusi are unchecked (omitted from form submission)
        ];

        $response = $this->actingAs($dosen)->post(route('dosen.pengaturan.notifications'), $payload);

        $response->assertRedirect(route('dosen.pengaturan.edit'));
        $response->assertSessionHas('success');

        $freshDosen = $dosen->fresh();
        $this->assertTrue($freshDosen->settings['notifikasi']['tugas']);
        $this->assertTrue($freshDosen->settings['notifikasi']['ujian']);
        $this->assertFalse($freshDosen->settings['notifikasi']['presensi']);
        $this->assertFalse($freshDosen->settings['notifikasi']['diskusi']);

        // 2. Test NotificationHelper integration
        $this->assertTrue(NotificationHelper::shouldNotify($freshDosen, 'tugas'));
        $this->assertTrue(NotificationHelper::shouldNotify($freshDosen, 'submission_received'));
        $this->assertTrue(NotificationHelper::shouldNotify($freshDosen, 'ujian'));
        $this->assertFalse(NotificationHelper::shouldNotify($freshDosen, 'presensi'));
        $this->assertFalse(NotificationHelper::shouldNotify($freshDosen, 'diskusi'));

        // 3. Re-enable diskusi notification
        $rePayload = [
            'tugas' => '1',
            'diskusi' => '1',
        ];
        $this->actingAs($dosen)->post(route('dosen.pengaturan.notifications'), $rePayload);
        $freshDosen2 = $dosen->fresh();
        $this->assertTrue(NotificationHelper::shouldNotify($freshDosen2, 'diskusi'));
        $this->assertFalse(NotificationHelper::shouldNotify($freshDosen2, 'ujian'));
    }

    /**
     * Test lecturer can submit a support ticket to BAAK / IT support.
     */
    public function test_lecturer_can_submit_support_ticket(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();

        $payload = [
            'subject' => 'Kendala Integrasi Nilai Akhir ke SIAKAD',
            'category' => 'teknis',
            'message' => 'Mohon bantuan untuk verifikasi sinkronisasi nilai mata kuliah IF2101 Kelas A.',
        ];

        $response = $this->actingAs($dosen)->post(route('dosen.pengaturan.support_ticket'), $payload);

        $response->assertRedirect(route('dosen.pengaturan.edit'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('support_tickets', [
            'user_id' => $dosen->id,
            'subject' => 'Kendala Integrasi Nilai Akhir ke SIAKAD',
            'category' => 'teknis',
            'status' => 'open',
        ]);
    }

    /**
     * Test student cannot access lecturer settings route.
     */
    public function test_student_cannot_access_lecturer_settings(): void
    {
        $student = User::where('role', 'student')->firstOrFail();

        $response = $this->actingAs($student)->get(route('dosen.pengaturan.edit'));

        $this->assertTrue(in_array($response->status(), [302, 403]));
    }

    /**
     * Test all 9 lecturer portal menus are now 100% live with ZERO placeholders.
     */
    public function test_all_nine_lecturer_menus_are_live_with_zero_placeholders(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();

        $routes = [
            'Beranda' => route('dosen.dashboard'),
            'Kelas Saya' => route('dosen.kelas.index'),
            'Materi' => route('dosen.materi.index'),
            'Tugas & Ujian' => route('dosen.tugas.index'),
            'Penilaian' => route('dosen.penilaian.index'),
            'Presensi' => route('dosen.presensi.index'),
            'Buku Nilai' => route('dosen.buku-nilai.index'),
            'Diskusi' => route('dosen.diskusi.index'),
            'Pengaturan' => route('dosen.pengaturan.edit'),
        ];

        foreach ($routes as $name => $url) {
            $response = $this->actingAs($dosen)->get($url);
            $response->assertStatus(200);
            $response->assertDontSee('Halaman ini sedang disiapkan');
        }
    }
}
