<?php

namespace Tests\Feature;

use App\Models\AcademicClass;
use App\Models\Assignment;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\LecturerBatch1Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class LecturerAssessmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->seed(LecturerBatch1Seeder::class);
    }

    public function test_lecturer_can_view_tugas_dan_ujian_index_with_canonical_stats(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();

        $response = $this->actingAs($dosen)->get(route('dosen.tugas.index'));

        $response->assertStatus(200);
        $response->assertSee('Tugas & Ujian', false);
        $response->assertSee('Ringkasan Assessment');
        $response->assertSee('Status Publikasi');
        $response->assertSee('Tugas 3 - Polimorfisme');
        $response->assertSee('Tugas 2 - OOP');

        // Check summary stats
        $response->assertViewHas('summary', function ($summary) {
            return $summary['total'] === 12
                && $summary['tugas'] === 7
                && $summary['kuis_ujian'] === 5
                && $summary['menunggu_publikasi'] === 3; // 2 draft + 1 terjadwal
        });

        // Check publication stats for donut chart (7 published, 2 draft, 1 scheduled, 2 archived)
        $response->assertViewHas('pubStats', function ($pub) {
            return $pub['published'] === 7
                && $pub['draft'] === 2
                && $pub['scheduled'] === 1
                && $pub['archived'] === 2
                && $pub['total'] === 12;
        });
    }

    public function test_lecturer_can_filter_assessments_by_jenis(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();

        // Filter Kuis
        $resKuis = $this->actingAs($dosen)->get(route('dosen.tugas.index', ['jenis' => 'kuis']));
        $resKuis->assertStatus(200);
        $resKuis->assertViewHas('assessments', function ($items) {
            return $items->contains('title', 'Kuis 1 - Rancang Bangun Mini Core Banking System (OOP)')
                && ! $items->contains('title', 'Tugas 3 - Polimorfisme');
        });

        // Filter UTS
        $resUts = $this->actingAs($dosen)->get(route('dosen.tugas.index', ['jenis' => 'uts']));
        $resUts->assertStatus(200);
        $resUts->assertViewHas('assessments', function ($items) {
            return $items->contains('title', 'UTS - Pemrograman Web');
        });
    }

    public function test_lecturer_can_view_create_form(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();

        $response = $this->actingAs($dosen)->get(route('dosen.tugas.create'));

        $response->assertStatus(200);
        $response->assertSee('Buat Tugas / Ujian Baru');
        $response->assertSee('Mata Kuliah & Kelas', false);
        $response->assertSee('Pilih Kelas yang Diampu');
    }

    public function test_lecturer_can_create_draft_and_student_cannot_see_it(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();
        $class = AcademicClass::where('lecturer_id', $dosen->id)->firstOrFail();

        // Dosen creates draft assignment
        $response = $this->actingAs($dosen)->post(route('dosen.tugas.store'), [
            'class_id' => $class->id,
            'title' => 'Tugas Khusus - Rahasia Dosen',
            'jenis' => 'tugas',
            'mode' => 'individu',
            'metode' => 'unggah_berkas',
            'description' => 'Tugas ini belum boleh dilihat mahasiswa.',
            'max_score' => 100,
            'mulai_at' => '2026-10-19 08:00:00',
            'due_at' => '2026-10-30 23:59:00',
            'status' => 'draft',
        ]);

        $response->assertRedirect(route('dosen.tugas.index'));

        $assignment = Assignment::where('title', 'Tugas Khusus - Rahasia Dosen')->firstOrFail();
        $this->assertEquals('draft', $assignment->status);

        // Student in that class attempts to see it
        $student = $class->students()->firstOrFail();
        $studentRes = $this->actingAs($student)->get(route('app.assignments'));
        $studentRes->assertDontSee('Tugas Khusus - Rahasia Dosen');

        // Student direct access to submit form is forbidden (Policy check)
        $submitRes = $this->actingAs($student)->get(route('assignments.submit_form', $assignment->id));
        $submitRes->assertStatus(403);
    }

    public function test_lecturer_can_publish_assessment_and_student_can_see_it(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();
        $class = AcademicClass::where('lecturer_id', $dosen->id)->firstOrFail();

        // Create draft first
        $assignment = Assignment::create([
            'class_id' => $class->id,
            'dosen_id' => $dosen->id,
            'title' => 'Tugas 6 - Testing SOLID Principles',
            'jenis' => 'tugas',
            'mode' => 'individu',
            'metode' => 'unggah_berkas',
            'description' => 'Implementasikan unit test untuk prinsip Single Responsibility.',
            'max_score' => 100,
            'mulai_at' => Carbon::now(),
            'due_at' => Carbon::now()->addDays(5),
            'status' => 'draft',
        ]);

        // Publish now
        $pubRes = $this->actingAs($dosen)->post(route('dosen.tugas.publish', $assignment->id));
        $pubRes->assertSessionHas('success');

        $assignment->refresh();
        $this->assertEquals('dipublikasikan', $assignment->status);

        // Student can now see and access it
        $student = $class->students()->firstOrFail();
        $studentRes = $this->actingAs($student)->get(route('app.assignments'));
        $studentRes->assertSee('Tugas 6 - Testing SOLID Principles');

        $submitRes = $this->actingAs($student)->get(route('assignments.submit_form', $assignment->id));
        $submitRes->assertStatus(200);
    }

    public function test_scheduled_assessment_auto_published_by_artisan_command(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();
        $class = AcademicClass::where('lecturer_id', $dosen->id)->firstOrFail();

        // Create scheduled assignment for 2026-10-20
        $assignment = Assignment::create([
            'class_id' => $class->id,
            'dosen_id' => $dosen->id,
            'title' => 'Kuis Terjadwal - Algoritma Lanjut',
            'jenis' => 'kuis',
            'mode' => 'individu',
            'metode' => 'input_manual',
            'max_score' => 100,
            'due_at' => Carbon::parse('2026-10-25 10:00:00'),
            'publish_at' => Carbon::parse('2026-10-20 08:00:00'),
            'status' => 'terjadwal',
        ]);

        // When time is 2026-10-19, command does NOT publish it
        Artisan::call('app:publish-scheduled-assessments');
        $assignment->refresh();
        $this->assertEquals('terjadwal', $assignment->status);

        // Advance test time to 2026-10-20 09:00:00
        Carbon::setTestNow(Carbon::parse('2026-10-20 09:00:00'));

        Artisan::call('app:publish-scheduled-assessments');
        $assignment->refresh();
        $this->assertEquals('dipublikasikan', $assignment->status);
    }

    public function test_lecturer_can_archive_assessment(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();
        $assignment = Assignment::where('dosen_id', $dosen->id)->where('status', 'dipublikasikan')->firstOrFail();

        $res = $this->actingAs($dosen)->post(route('dosen.tugas.archive', $assignment->id));
        $res->assertSessionHas('success');

        $assignment->refresh();
        $this->assertEquals('arsip', $assignment->status);
    }

    public function test_lecturer_cannot_update_another_lecturer_assessment(): void
    {
        $dosenBudi = User::where('email', 'dosen@arunika.test')->firstOrFail();
        $dosenRudi = User::where('email', 'dosen2@arunika.test')->firstOrFail();
        $classRudi = AcademicClass::where('lecturer_id', $dosenRudi->id)->firstOrFail();

        $assignmentRudi = Assignment::create([
            'class_id' => $classRudi->id,
            'dosen_id' => $dosenRudi->id,
            'title' => 'Tugas Rahasia Rudi',
            'jenis' => 'tugas',
            'mode' => 'individu',
            'metode' => 'unggah_berkas',
            'max_score' => 100,
            'due_at' => Carbon::parse('2026-10-30 23:59:00'),
            'status' => 'draft',
        ]);

        // Budi attempts to update Rudi's assignment
        $response = $this->actingAs($dosenBudi)->put(route('dosen.tugas.update', $assignmentRudi->id), [
            'title' => 'Tugas Telah Dibajak',
            'jenis' => 'tugas',
            'mode' => 'individu',
            'metode' => 'unggah_berkas',
            'max_score' => 100,
            'due_at' => '2026-10-30 23:59:00',
            'status' => 'draft',
        ]);

        $response->assertStatus(403);
    }
}
