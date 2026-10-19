<?php

namespace Tests\Feature;

use App\Models\AcademicClass;
use App\Models\Announcement;
use App\Models\Material;
use App\Models\Meeting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\LecturerBatch1Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LecturerMaterialTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->seed(LecturerBatch1Seeder::class);
    }

    public function test_lecturer_can_view_materi_index_with_canonical_materials(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();

        $response = $this->actingAs($dosen)->get(route('dosen.materi.index'));

        $response->assertStatus(200);
        $response->assertSee('Materi Pembelajaran', false);
        $response->assertSee('Pengantar OOP dan Konsep Dasar');
        $response->assertSee('Slide Pertemuan 2 - Inheritance');
        $response->assertSee('Dataset Latihan');
        $response->assertSee('Total Materi');
        $response->assertSee('Terpublikasi');

        // IF2101 Kelas A has 7 materials (6 published, 1 draft)
        $response->assertViewHas('summary', function ($summary) {
            return $summary['total'] === 7
                && $summary['published'] === 6
                && $summary['draft'] === 1;
        });
    }

    public function test_lecturer_can_switch_classes_in_materi_page(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();
        $classBanking = AcademicClass::where('lecturer_id', $dosen->id)
            ->whereHas('course', fn ($q) => $q->where('code', 'IF2103'))
            ->firstOrFail();

        $response = $this->actingAs($dosen)->get(route('dosen.materi.index', ['class_id' => $classBanking->id]));

        $response->assertStatus(200);
        $response->assertViewHas('activeClass', function ($cls) use ($classBanking) {
            return $cls->id === $classBanking->id;
        });
    }

    public function test_lecturer_can_upload_new_material(): void
    {
        Storage::fake('private');
        Storage::fake('public');

        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();
        $class = AcademicClass::where('lecturer_id', $dosen->id)->firstOrFail();

        $file = UploadedFile::fake()->create('modul-design-pattern.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($dosen)->post(route('dosen.materi.store'), [
            'class_id' => $class->id,
            'week' => 7,
            'title' => 'Modul 7 - Factory & Observer Pattern',
            'type' => 'pdf',
            'content' => 'Implementasi pattern arsitektur sistem pada Java.',
            'status' => 'published',
            'file' => $file,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $material = Material::where('title', 'Modul 7 - Factory & Observer Pattern')->firstOrFail();
        $this->assertEquals('published', $material->status);
        $this->assertEquals($dosen->id, $material->uploaded_by);
        $this->assertNotNull($material->file_path);
        Storage::disk('private')->assertExists($material->file_path);
        Storage::disk('public')->assertMissing($material->file_path);
    }

    public function test_draft_material_is_hidden_from_student_and_blocked_on_direct_access(): void
    {
        $student = User::where('email', 'arya.pratama@student.unus.ac.id')->firstOrFail();

        // Dataset Latihan is draft in IF2101 Kelas A
        $draftMaterial = Material::where('title', 'Dataset Latihan')->firstOrFail();
        $this->assertEquals('draft', $draftMaterial->status);

        // Student visits materials page
        $res = $this->actingAs($student)->get(route('app.materials'));
        $res->assertStatus(200);
        $res->assertDontSee('Dataset Latihan');

        // Direct access to viewer subpage is forbidden
        $directRes = $this->actingAs($student)->get(route('app.materials.view', $draftMaterial->id));
        $directRes->assertStatus(403);
    }

    public function test_lecturer_can_toggle_publish_material_and_student_can_now_access_it(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();
        $student = User::where('email', 'arya.pratama@student.unus.ac.id')->firstOrFail();

        $material = Material::where('title', 'Dataset Latihan')->firstOrFail();
        $this->assertEquals('draft', $material->status);

        // Lecturer toggles publish
        $toggleRes = $this->actingAs($dosen)->post(route('dosen.materi.toggle_publish', $material->id));
        $toggleRes->assertRedirect();
        $toggleRes->assertSessionHas('success');

        $material->refresh();
        $this->assertEquals('published', $material->status);

        // Student can now view it
        $studentRes = $this->actingAs($student)->get(route('app.materials'));
        $studentRes->assertSee('Dataset Latihan');

        $viewerRes = $this->actingAs($student)->get(route('app.materials.view', $material->id));
        $viewerRes->assertStatus(200);
    }

    public function test_lecturer_can_update_material(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();
        $material = Material::where('uploaded_by', $dosen->id)->firstOrFail();

        $response = $this->actingAs($dosen)->put(route('dosen.materi.update', $material->id), [
            'week' => 2,
            'title' => 'Judul Materi Telah Diperbarui',
            'type' => 'slide',
            'content' => 'Deskripsi materi baru hasil revisi.',
            'status' => 'published',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $material->refresh();
        $this->assertEquals('Judul Materi Telah Diperbarui', $material->title);
        $this->assertEquals('slide', $material->type);
    }

    public function test_lecturer_can_delete_material(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();
        $material = Material::where('uploaded_by', $dosen->id)->firstOrFail();
        $materialId = $material->id;

        $response = $this->actingAs($dosen)->delete(route('dosen.materi.destroy', $materialId));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('materials', ['id' => $materialId]);
    }

    public function test_lecturer_cannot_manage_another_lecturer_material(): void
    {
        $dosenBudi = User::where('email', 'dosen@arunika.test')->firstOrFail();
        $dosenRudi = User::where('email', 'dosen2@arunika.test')->firstOrFail();
        $classRudi = AcademicClass::where('lecturer_id', $dosenRudi->id)->firstOrFail();

        $meetingRudi = Meeting::create([
            'class_id' => $classRudi->id,
            'week' => 1,
            'title' => 'Pertemuan 1 Rudi',
            'date' => '2026-10-01',
            'is_published' => true,
        ]);

        $materialRudi = Material::create([
            'class_id' => $classRudi->id,
            'meeting_id' => $meetingRudi->id,
            'title' => 'Materi Milik Rudi',
            'type' => 'pdf',
            'status' => 'published',
            'uploaded_by' => $dosenRudi->id,
        ]);

        // Budi attempts to delete Rudi's material
        $response = $this->actingAs($dosenBudi)->delete(route('dosen.materi.destroy', $materialRudi->id));
        $response->assertStatus(403);
    }

    public function test_lecturer_can_create_announcement_and_delete_it(): void
    {
        $dosen = User::where('email', 'dosen@arunika.test')->firstOrFail();
        $class = AcademicClass::where('lecturer_id', $dosen->id)->firstOrFail();

        // Create announcement
        $res = $this->actingAs($dosen)->post(route('dosen.pengumuman.store'), [
            'class_id' => $class->id,
            'title' => 'Kuliah Pengganti Hari Rabu',
            'content' => 'Kuliah pengganti diadakan pada hari Rabu pukul 10:00 WIB.',
        ]);

        $res->assertRedirect();
        $res->assertSessionHas('success');

        $announcement = Announcement::where('title', 'Kuliah Pengganti Hari Rabu')->firstOrFail();
        $this->assertEquals($class->id, $announcement->class_id);
        $this->assertEquals($dosen->id, $announcement->author_id);

        // Delete announcement
        $delRes = $this->actingAs($dosen)->delete(route('dosen.pengumuman.destroy', $announcement->id));
        $delRes->assertRedirect();
        $delRes->assertSessionHas('success');

        $this->assertDatabaseMissing('announcements', ['id' => $announcement->id]);
    }
}
