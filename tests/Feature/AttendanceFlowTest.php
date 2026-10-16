<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Meeting;
use App\Models\User;
use App\Services\AttendanceService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttendanceFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->student = User::where('role', UserRole::Student)->where('email', 'arya.pratama@student.unus.ac.id')->first()
            ?? User::where('role', UserRole::Student)->first();
    }

    public function test_attendance_page_renders_successfully_with_canonical_data(): void
    {
        $response = $this->actingAs($this->student)->get('/app/attendance');

        $response->assertStatus(200);
        $response->assertSee('Presensi');
        $response->assertSee('Riwayat Presensi');
        $response->assertSee('Tingkat Kehadiran');
        $response->assertSee('Hadir');
        $response->assertSee('Izin');
        $response->assertSee('Alfa');

        // Check canonical metrics
        $response->assertSee('92%');
        $response->assertSee('11');
        $response->assertSee('1');
        $response->assertSee('0');
        $response->assertSee('Sangat Baik');

        // Check canonical meeting topics
        $response->assertSee('Pengenalan Mata Kuliah dan RPS');
        $response->assertSee('Konsep Dasar Pemrograman Berorientasi Objek');
        $response->assertSee('Struktur Data dan Array');
        $response->assertSee('Fungsi dan Modularitas');
        $response->assertSee('Class dan Object');
        $response->assertSee('Database dan ORM');
        $response->assertSee('Aplikasi Web dengan Laravel');

        // Check Course breakdown
        $response->assertSeeText('Pemrograman Berorientasi Objek & Desain Pola');
        $response->assertSeeText('Basis Data & Sistem Terdistribusi');
    }

    public function test_attendance_service_summary_calculation(): void
    {
        $service = app(AttendanceService::class);
        $summary = $service->getSummary($this->student);

        $this->assertIsArray($summary);
        $this->assertEquals(92, $summary['rate']);
        $this->assertEquals('92%', $summary['rate_formatted']);
        $this->assertEquals(12, $summary['total_sessions']);
        $this->assertEquals(11, $summary['present_count']);
        $this->assertEquals(1, $summary['permit_count']);
        $this->assertEquals(0, $summary['absent_count']);
        $this->assertEquals('Sangat Baik', $summary['status_label']);

        $meetingLogs = $service->getMeetingLogs($this->student);
        $this->assertCount(12, $meetingLogs);
        $this->assertEquals('Pengenalan Mata Kuliah dan RPS', $meetingLogs->first()['topic']);
        $this->assertEquals('Hadir', $meetingLogs->first()['status']);
    }

    public function test_student_can_check_in_via_code_and_earn_xp(): void
    {
        $initialXp = $this->student->totalXp();

        $response = $this->actingAs($this->student)->post('/app/attendance/check-in/code', [
            'code' => '849201',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('attendance_logs', [
            'user_id' => $this->student->id,
            'status' => 'present',
            'method' => 'manual',
        ]);

        $this->assertDatabaseHas('xp_ledgers', [
            'user_id' => $this->student->id,
            'source_type' => 'attendance',
            'amount' => 5,
        ]);

        $this->student->refresh();
        $this->assertEquals($initialXp + 5, $this->student->totalXp());
    }

    public function test_student_can_check_in_via_qr_token_and_earn_xp(): void
    {
        $response = $this->actingAs($this->student)->post('/app/attendance/check-in/qr', [
            'token' => 'QR-IF2101-W13',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('attendance_logs', [
            'user_id' => $this->student->id,
            'status' => 'present',
            'method' => 'qr',
        ]);
    }

    public function test_student_can_submit_absence_permit_with_document(): void
    {
        Storage::fake('public');

        $meeting = Meeting::whereHas('academicClass.course', fn ($q) => $q->where('code', 'IF2101'))
            ->where('week', 13)
            ->first() ?? Meeting::first();

        $document = UploadedFile::fake()->create('surat_dokter.pdf', 200, 'application/pdf');

        $response = $this->actingAs($this->student)->post('/app/attendance/permit', [
            'meeting_id' => $meeting->id,
            'type' => 'sakit',
            'reason' => 'Demam tinggi dan dianjurkan istirahat oleh dokter',
            'document' => $document,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('attendance_logs', [
            'user_id' => $this->student->id,
            'status' => 'excused',
            'method' => 'manual',
        ]);
    }

    public function test_invalid_code_rejected(): void
    {
        $response = $this->actingAs($this->student)->post('/app/attendance/check-in/code', [
            'code' => 'abc', // Invalid: not 6 digits
        ]);

        $response->assertSessionHasErrors(['code']);
    }

    public function test_donut_chart_breakdown_calculation(): void
    {
        $service = app(AttendanceService::class);
        $donut = $service->getDonutBreakdown($this->student);

        $this->assertIsArray($donut);
        $this->assertEquals(92, $donut['rate']);
        $this->assertEquals(92, $donut['present_pct']);
        $this->assertEquals(8, $donut['permit_pct']);
        $this->assertEquals(0, $donut['absent_pct']);
        $this->assertStringContainsString('231.22', $donut['green_dasharray']);
        $this->assertStringContainsString('20.11', $donut['amber_dasharray']);
    }

    public function test_course_attendance_breakdown(): void
    {
        $service = app(AttendanceService::class);
        $courses = $service->getCourseAttendanceBreakdown($this->student);

        $this->assertGreaterThanOrEqual(4, $courses->count());

        $pbo = $courses->firstWhere('code', 'IF2101');
        $this->assertNotNull($pbo);
        $this->assertEquals(92, $pbo['percentage']);
        $this->assertEquals('92%', $pbo['percentage_formatted']);
    }
}
