<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicClass;
use App\Models\Material;
use App\Models\MaterialProgress;
use App\Models\User;
use App\Services\MaterialService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaterialFlowTest extends TestCase
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

    public function test_materials_page_renders_successfully_with_canonical_data(): void
    {
        $response = $this->actingAs($this->student)->get(route('app.materials'));

        $response->assertStatus(200);
        $response->assertSee('Materi Pembelajaran');
        $response->assertSee('IF2101');
        $response->assertSee('Pemrograman Berorientasi Objek &amp; Desain Pola', false);
        $response->assertSee('14');
        $response->assertSee('Total Materi');
        $response->assertSee('5');
        $response->assertSee('Selesai');
        $response->assertSee('6');
        $response->assertSee('Dalam Proses');
        $response->assertSee('3');
        $response->assertSee('Terkunci');
        $response->assertSee('35.7% Selesai');
        $response->assertSee('Pengenalan dan Konsep Dasar OOP');
        $response->assertSee('3 / 3 Materi');
    }

    public function test_material_service_summary_calculation(): void
    {
        $class = AcademicClass::whereHas('course', fn ($q) => $q->where('code', 'IF2101'))->first();
        $this->assertNotNull($class);

        $service = app(MaterialService::class);
        $summary = $service->getSummary($this->student, $class);

        $this->assertEquals(14, $summary['total_materials']);
        $this->assertEquals(5, $summary['completed_materials']);
        $this->assertEquals(6, $summary['in_progress_materials']);
        $this->assertEquals(3, $summary['locked_materials']);
        $this->assertEquals(35.7, $summary['progress_percentage']);
    }

    public function test_switching_course_in_materials_page(): void
    {
        $response = $this->actingAs($this->student)->get(route('app.materials', ['course' => 'IF2102']));

        $response->assertStatus(200);
        $response->assertSee('IF2102');
        $response->assertSee('Basis Data &amp; Sistem Terdistribusi', false);
    }

    public function test_student_can_view_material_on_dedicated_viewer_subpage(): void
    {
        $material = Material::whereHas('meeting.academicClass.course', fn ($q) => $q->where('code', 'IF2101'))->first();
        $this->assertNotNull($material);

        $response = $this->actingAs($this->student)->get(route('app.materials.view', $material));

        $response->assertStatus(200);
        $response->assertSee($material->title);
        $response->assertSee('Kembali ke Materi Pembelajaran');
        $response->assertSee('Informasi Pembelajaran');
    }

    public function test_server_strictly_rejects_material_view_for_locked_course_with_403(): void
    {
        $lockedMaterial = Material::whereHas('meeting.academicClass.course', fn ($q) => $q->where('code', 'IF2106'))->first();
        $this->assertNotNull($lockedMaterial);

        $response = $this->actingAs($this->student)->get(route('app.materials.view', $lockedMaterial));

        $response->assertStatus(403);
    }

    public function test_student_can_update_material_progress_and_earn_xp(): void
    {
        // Pick an in-progress material
        $material = Material::whereHas('meeting.academicClass.course', fn ($q) => $q->where('code', 'IF2101'))
            ->whereHas('progress', fn ($q) => $q->where('user_id', $this->student->id)->where('status', 'in_progress'))
            ->first();

        $this->assertNotNull($material);

        $initialXp = $this->student->totalXp();

        $response = $this->actingAs($this->student)->postJson(route('app.materials.progress', $material), [
            'watch_percentage' => 95,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 'completed',
            'is_completed' => true,
            'xp_awarded' => 25,
        ]);

        $progress = MaterialProgress::where('user_id', $this->student->id)
            ->where('material_id', $material->id)
            ->first();

        $this->assertEquals('completed', $progress->status);
        $this->assertEquals(95, $progress->watch_percentage);
        $this->assertNotNull($progress->completed_at);

        $this->assertEquals($initialXp + 25, $this->student->fresh()->totalXp());
    }
}
