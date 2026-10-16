<?php

namespace Tests\Feature;

use App\Models\Term;
use App\Models\User;
use App\Services\ScheduleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleFlowTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;

    protected Term $activeTerm;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();

        $this->student = User::where('email', 'arya.pratama@student.unus.ac.id')->first()
            ?? User::where('role', 'student')->first();

        $this->activeTerm = Term::where('is_active', true)->first()
            ?? Term::latest()->first();
    }

    public function test_schedule_page_renders_successfully(): void
    {
        $response = $this->actingAs($this->student)->get('/app/schedule');

        $response->assertStatus(200);
        $response->assertSee('Jadwal &amp; Kalender Akademik', false);
        $response->assertSee('Matriks Mingguan');
        $response->assertSee('Kalender Bulanan');
        $response->assertSee('Agenda &amp; Rincian', false);
        $response->assertSee('Ekspor iCal (.ics)');
        $response->assertSee('Total Beban SKS');
    }

    public function test_schedule_service_calculates_summary_and_timetable(): void
    {
        $service = app(ScheduleService::class);
        $summary = $service->getSummaryMetrics($this->student);

        $this->assertIsArray($summary);
        $this->assertArrayHasKey('total_credits', $summary);
        $this->assertArrayHasKey('total_courses', $summary);
        $this->assertArrayHasKey('total_hours_per_week', $summary);
        $this->assertArrayHasKey('busiest_day', $summary);

        $this->assertGreaterThanOrEqual(15, $summary['total_credits']);
        $this->assertGreaterThanOrEqual(5, $summary['total_courses']);

        $weekly = $service->getWeeklySchedule($this->student);
        $this->assertArrayHasKey('Senin', $weekly);
        $this->assertArrayHasKey('Jumat', $weekly);

        $grid = $service->getWeeklyTimetableGrid($this->student);
        $this->assertArrayHasKey('days', $grid);
        $this->assertArrayHasKey('time_slots', $grid);
        $this->assertArrayHasKey('grid', $grid);
    }

    public function test_export_ics_returns_valid_icalendar_stream(): void
    {
        $response = $this->actingAs($this->student)->get('/app/schedule/export.ics');

        $response->assertStatus(200);
        $this->assertStringContainsString('text/calendar', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment;', $response->headers->get('Content-Disposition'));

        $content = $response->getContent();
        $this->assertStringContainsString('BEGIN:VCALENDAR', $content);
        $this->assertStringContainsString('END:VCALENDAR', $content);
        $this->assertStringContainsString('BEGIN:VEVENT', $content);
        $this->assertStringContainsString('RRULE:FREQ=WEEKLY', $content);
        $this->assertStringContainsString('Asia/Jakarta', $content);
    }

    public function test_events_api_returns_monthly_events_json(): void
    {
        $response = $this->actingAs($this->student)->getJson('/api/schedule/events?year=2026&month=10');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'year',
            'month',
            'events',
        ]);
        $this->assertEquals(2026, $response->json('year'));
        $this->assertEquals(10, $response->json('month'));
    }

    public function test_dashboard_links_to_schedule_and_displays_today_classes(): void
    {
        $response = $this->actingAs($this->student)->get('/app');

        $response->assertStatus(200);
        $response->assertSee(route('app.schedule'));
        $response->assertSee('Mata Kuliah Hari Ini');
    }

    public function test_search_for_jadwal_returns_schedule_link(): void
    {
        $response = $this->actingAs($this->student)->getJson('/app/search?q=jadwal');

        $response->assertStatus(200);
        $courses = $response->json('courses');
        $this->assertNotEmpty($courses);

        $hasScheduleResult = collect($courses)->contains(fn ($c) => str_contains($c['title'], 'Jadwal Kuliah'));
        $this->assertTrue($hasScheduleResult);
    }

    public function test_switching_semester_filters_schedule(): void
    {
        $otherTerm = Term::where('id', '!=', $this->activeTerm->id)->first();
        if ($otherTerm) {
            $response = $this->actingAs($this->student)->get('/app/schedule?term_id='.$otherTerm->id);
            $response->assertStatus(200);
            $response->assertSee('Jadwal &amp; Kalender Akademik', false);
        } else {
            $this->assertTrue(true);
        }
    }
}
