<?php

namespace Tests\Feature;

use App\Models\AcademicClass;
use App\Models\Campus;
use App\Models\Course;
use App\Models\Curriculum;
use App\Models\Enrollment;
use App\Models\Faculty;
use App\Models\Meeting;
use App\Models\Program;
use App\Models\Term;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicStructureTest extends TestCase
{
    use RefreshDatabase;

    public function test_academic_hierarchy_can_be_created_and_queried(): void
    {
        $campus = Campus::create([
            'name' => 'Universitas Indonesia Hebat',
            'code' => 'UIH',
        ]);

        $faculty = Faculty::create([
            'campus_id' => $campus->id,
            'name' => 'Fakultas Teknik',
            'code' => 'FT',
        ]);

        $program = Program::create([
            'faculty_id' => $faculty->id,
            'name' => 'S1 Teknik Komputer',
            'code' => 'TK',
        ]);

        $term = Term::create([
            'campus_id' => $campus->id,
            'name' => 'Ganjil 2026/2027',
            'academic_year' => '2026/2027',
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->addMonths(5)->endOfMonth(),
        ]);

        $curriculum = Curriculum::create([
            'program_id' => $program->id,
            'year' => 2024,
            'name' => 'Kurikulum 2024',
        ]);

        $course = Course::create([
            'curriculum_id' => $curriculum->id,
            'code' => 'TK101',
            'name' => 'Sistem Digital',
            'credits' => 3,
            'semester' => 1,
        ]);

        $lecturer = User::factory()->lecturer()->create(['campus_id' => $campus->id]);
        $student = User::factory()->student()->create(['campus_id' => $campus->id]);

        $class = AcademicClass::create([
            'course_id' => $course->id,
            'term_id' => $term->id,
            'lecturer_id' => $lecturer->id,
            'section' => 'TK-A',
        ]);

        $enrollment = Enrollment::create([
            'class_id' => $class->id,
            'user_id' => $student->id,
            'status' => 'aktif',
            'final_score' => 90.00,
            'letter_grade' => 'A',
        ]);

        $meeting = Meeting::create([
            'class_id' => $class->id,
            'week' => 1,
            'title' => 'Pengantar Aljabar Boolean',
        ]);

        $this->assertEquals('Fakultas Teknik', $program->faculty->name);
        $this->assertEquals('S1 Teknik Komputer', $curriculum->program->name);
        $this->assertEquals(1, $class->students()->count());
        $this->assertEquals($student->id, $class->students()->first()->id);
        $this->assertEquals(1, $class->meetings()->count());
    }
}
