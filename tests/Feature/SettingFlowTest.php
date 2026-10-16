<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserPreference;
use Database\Seeders\CanonicalStudentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CanonicalStudentSeeder::class);

        $this->student = User::where('email', 'arya.pratama@student.unus.ac.id')->first()
            ?? User::where('role', 'student')->first();
    }

    public function test_student_can_view_settings_index_page(): void
    {
        $response = $this->actingAs($this->student)->get('/app/settings');

        $response->assertStatus(200);
        $response->assertSee('Pengaturan');
        $response->assertSee('Informasi Profil');
        $response->assertSee('Status Akun');
        $response->assertSee('Keamanan Akun');
        $response->assertSee('Notifikasi');
        $response->assertSee('Tampilan');
        $response->assertSee('Bahasa &amp; Lokalisasi', false);
        $response->assertSee($this->student->name);
        $response->assertSee($this->student->email);
    }

    public function test_student_can_update_profile_information(): void
    {
        $payload = [
            'name' => 'Arya Pratama Updated',
            'email' => 'arya.updated@student.unus.ac.id',
            'phone' => '081987654321',
        ];

        $response = $this->actingAs($this->student)->post('/app/settings/profile', $payload);

        $response->assertRedirect('/app/settings');
        $this->student->refresh();

        $this->assertEquals('Arya Pratama Updated', $this->student->name);
        $this->assertEquals('arya.updated@student.unus.ac.id', $this->student->email);
        $this->assertEquals('081987654321', $this->student->phone);
    }

    public function test_student_can_upload_profile_avatar(): void
    {
        Storage::fake('public');

        $avatar = UploadedFile::fake()->image('my_avatar.jpg', 300, 300);

        $response = $this->actingAs($this->student)->post('/app/settings/profile', [
            'name' => $this->student->name,
            'email' => $this->student->email,
            'avatar' => $avatar,
        ]);

        $response->assertRedirect('/app/settings');
        $this->student->refresh();

        $this->assertNotNull($this->student->avatar_path);
        $this->assertStringContainsString('avatar_'.$this->student->id, $this->student->avatar_path);
    }

    public function test_student_can_update_password_with_valid_current_password(): void
    {
        $response = $this->actingAs($this->student)->post('/app/settings/password', [
            'current_password' => 'password',
            'password' => 'newSecretPass123',
            'password_confirmation' => 'newSecretPass123',
        ]);

        $response->assertRedirect('/app/settings');
        $this->student->refresh();

        $this->assertTrue(Hash::check('newSecretPass123', $this->student->password));
    }

    public function test_student_cannot_update_password_with_wrong_current_password(): void
    {
        $response = $this->actingAs($this->student)->post('/app/settings/password', [
            'current_password' => 'wrongOldPassword',
            'password' => 'newSecretPass123',
            'password_confirmation' => 'newSecretPass123',
        ]);

        $response->assertSessionHasErrors('current_password');
        $this->student->refresh();

        $this->assertTrue(Hash::check('password', $this->student->password));
    }

    public function test_student_can_update_preferences_and_sync_user_settings(): void
    {
        $payload = [
            'theme' => 'gelap',
            'textSize' => 'besar',
            'language' => 'en',
            'timezone' => 'Asia/Makassar',
            'dateFormat' => 'YYYY-MM-DD',
            'timeFormat' => 'hh:mm A',
            'twoFactorEnabled' => true,
            'googleConnected' => true,
            'notifPengumuman' => true,
            'notifTugas' => false,
            'notifPesan' => true,
            'notifDiskusi' => true,
            'notifSistem' => false,
        ];

        $response = $this->actingAs($this->student)->postJson('/app/settings/preferences', $payload);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'preferences' => [
                'theme' => 'dark',
                'font_size' => 'large',
                'locale' => 'en',
                'timezone' => 'Asia/Makassar',
                'date_format' => 'YYYY-MM-DD',
                'time_format' => 'hh:mm A',
                'two_factor_enabled' => true,
                'google_connected' => true,
            ],
        ]);

        $this->student->refresh();
        $pref = UserPreference::where('user_id', $this->student->id)->first();

        $this->assertEquals('dark', $pref->theme);
        $this->assertEquals('large', $pref->font_size);
        $this->assertEquals('en', $pref->locale);
        $this->assertTrue($pref->two_factor_enabled);
        $this->assertNotNull($pref->google_id);
        $this->assertFalse($pref->notifications['assignments_deadlines']);
        $this->assertTrue($pref->notifications['discussion_activities']);

        // Check user settings JSON column
        $this->assertEquals('dark', $this->student->settings['theme']);
        $this->assertEquals('large', $this->student->settings['font_size']);
        $this->assertEquals('en', $this->student->settings['locale']);
    }

    public function test_student_can_submit_support_ticket(): void
    {
        $payload = [
            'subject' => 'Kendala Presensi Kuliah PBO Pertemuan 8',
            'category' => 'presensi',
            'message' => 'QR Code presensi sempat tidak bisa dipindai saat di ruang kelas.',
        ];

        $response = $this->actingAs($this->student)->post('/app/settings/support-ticket', $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('support_tickets', [
            'user_id' => $this->student->id,
            'subject' => 'Kendala Presensi Kuliah PBO Pertemuan 8',
            'category' => 'presensi',
            'status' => 'open',
        ]);
    }

    public function test_student_can_view_secondary_subpages(): void
    {
        $sections = [
            'aplikasi-terhubung' => 'Aplikasi Terhubung',
            'webhook-api' => 'Webhook & API',
            'bantuan-dukungan' => 'Bantuan & Dukungan',
            'jadwal' => 'Jadwal & Pengingat',
            'preferensi' => 'Preferensi Pembelajaran',
        ];

        foreach ($sections as $slug => $expectedTitle) {
            $response = $this->actingAs($this->student)->get("/app/settings/{$slug}");
            $response->assertStatus(200);
            $response->assertSee($expectedTitle);
            $response->assertSee('Semua Pengaturan');
        }
    }
}
