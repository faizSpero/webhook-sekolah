<?php

namespace Tests\Feature\Admin;

use App\Models\Agenda;
use App\Models\Student;
use App\Models\StudentScore;
use App\Models\Suggestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AdminManagementPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();
    }

    public function test_agenda_crud_pages_work(): void
    {
        $createResponse = $this->post(route('admin.agendas.store'), [
            'title' => 'Tryout UTBK',
            'description' => 'Simulasi UTBK nasional',
            'starts_at' => '2026-06-10 08:00:00',
            'ends_at' => '2026-06-10 11:00:00',
            'is_active' => 1,
        ]);

        $createResponse->assertRedirect(route('admin.agendas.index'));
        $this->assertDatabaseHas('agendas', ['title' => 'Tryout UTBK']);

        $agenda = Agenda::firstOrFail();

        $this->put(route('admin.agendas.update', $agenda), [
            'title' => 'Tryout UTBK Updated',
            'description' => 'Updated',
            'starts_at' => '2026-06-11 08:00:00',
            'ends_at' => '2026-06-11 11:00:00',
            'is_active' => 0,
        ])->assertRedirect(route('admin.agendas.index'));

        $this->assertDatabaseHas('agendas', [
            'id' => $agenda->id,
            'title' => 'Tryout UTBK Updated',
            'is_active' => 0,
        ]);

        $this->delete(route('admin.agendas.destroy', $agenda))
            ->assertRedirect(route('admin.agendas.index'));

        $this->assertDatabaseMissing('agendas', ['id' => $agenda->id]);
    }

    public function test_student_score_csv_import_works(): void
    {
        $student = Student::create([
            'external_id' => 'stu-1',
            'name' => 'Siswa A',
            'nisn' => '1234567890',
        ]);

        $csv = "nisn,subject,score,score_date,notes\n1234567890,Matematika,90,2026-05-01,Nilai bagus\n";

        $file = UploadedFile::fake()->createWithContent('scores.csv', $csv);

        $response = $this->post(route('admin.scores.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('admin.scores.index'));

        $this->assertDatabaseHas('student_scores', [
            'student_id' => $student->id,
            'subject' => 'Matematika',
            'score' => 90,
        ]);
    }

    public function test_suggestions_pages_are_accessible(): void
    {
        $suggestion = Suggestion::create([
            'sender' => '628123',
            'sender_name' => 'Faizi',
            'message' => 'Saran menu kantin ditambah.',
            'source' => 'whatsapp',
        ]);

        $this->get(route('admin.suggestions.index'))
            ->assertOk()
            ->assertSee('Suggestions')
            ->assertSee('Faizi');

        $this->get(route('admin.suggestions.show', $suggestion))
            ->assertOk()
            ->assertSee('Saran menu kantin ditambah.');
    }

    public function test_student_score_manual_crud_works(): void
    {
        $student = Student::create([
            'external_id' => 'stu-2',
            'name' => 'Siswa B',
            'nisn' => '1112223334',
        ]);

        $create = $this->post(route('admin.scores.store'), [
            'student_id' => $student->id,
            'subject' => 'Bahasa Indonesia',
            'score' => 82.5,
            'score_date' => '2026-04-01',
            'notes' => 'Remedial selesai',
        ]);

        $create->assertRedirect(route('admin.scores.index'));
        $score = StudentScore::firstOrFail();

        $this->put(route('admin.scores.update', $score), [
            'student_id' => $student->id,
            'subject' => 'Bahasa Indonesia',
            'score' => 88,
            'score_date' => '2026-04-02',
            'notes' => 'Nilai diperbaiki',
        ])->assertRedirect(route('admin.scores.index'));

        $this->assertDatabaseHas('student_scores', [
            'id' => $score->id,
            'score' => 88,
        ]);

        $this->delete(route('admin.scores.destroy', $score))
            ->assertRedirect(route('admin.scores.index'));

        $this->assertDatabaseMissing('student_scores', ['id' => $score->id]);
    }
}
