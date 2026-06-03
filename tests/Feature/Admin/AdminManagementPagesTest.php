<?php

namespace Tests\Feature\Admin;

use App\Models\Agenda;
use App\Models\Student;
use App\Models\StudentScore;
use App\Models\Suggestion;
use App\Models\Todo;
use App\Models\WebhookEvent;
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

    public function test_webhook_event_manual_crud_pages_work(): void
    {
        $createResponse = $this->post(route('admin.events.store'), [
            'event_id' => 'evt-manual-1',
            'source' => 'manual',
            'event_type' => 'student.updated',
            'status' => WebhookEvent::STATUS_PENDING,
            'attempts' => 0,
            'sender_timestamp' => 1717000000,
            'payload_json' => '{"student_id":"stu-1"}',
            'headers_json' => '{"x-source":"manual"}',
        ]);

        $createResponse->assertRedirect(route('admin.events.index'));
        $event = WebhookEvent::firstOrFail();

        $this->put(route('admin.events.update', $event), [
            'event_id' => 'evt-manual-1',
            'source' => 'manual',
            'event_type' => 'student.synced',
            'status' => WebhookEvent::STATUS_PROCESSED,
            'attempts' => 1,
            'sender_timestamp' => 1717000001,
            'payload_json' => '{"student_id":"stu-2"}',
            'headers_json' => '{"x-source":"manual-updated"}',
            'processed_at' => '2026-06-01 10:00:00',
        ])->assertRedirect(route('admin.events.index'));

        $this->assertDatabaseHas('webhook_events', [
            'id' => $event->id,
            'event_type' => 'student.synced',
            'status' => WebhookEvent::STATUS_PROCESSED,
        ]);

        $this->delete(route('admin.events.destroy', $event))
            ->assertRedirect(route('admin.events.index'));

        $this->assertDatabaseMissing('webhook_events', ['id' => $event->id]);
    }

    public function test_suggestion_manual_crud_works(): void
    {
        $create = $this->post(route('admin.suggestions.store'), [
            'sender' => '628111',
            'sender_name' => 'Budi',
            'source' => 'whatsapp',
            'message' => 'Tambah jadwal tambahan.',
        ]);

        $create->assertRedirect(route('admin.suggestions.index'));
        $suggestion = Suggestion::firstOrFail();

        $this->put(route('admin.suggestions.update', $suggestion), [
            'sender' => '628111',
            'sender_name' => 'Budi Updated',
            'source' => 'whatsapp',
            'message' => 'Tambah jadwal tambahan sore.',
        ])->assertRedirect(route('admin.suggestions.index'));

        $this->assertDatabaseHas('suggestions', [
            'id' => $suggestion->id,
            'sender_name' => 'Budi Updated',
        ]);

        $this->delete(route('admin.suggestions.destroy', $suggestion))
            ->assertRedirect(route('admin.suggestions.index'));

        $this->assertDatabaseMissing('suggestions', ['id' => $suggestion->id]);
    }

    public function test_todo_manual_crud_works(): void
    {
        $create = $this->post(route('admin.todos.store'), [
            'title' => 'Review proposals',
            'description' => 'Review all incoming proposals',
            'due_date' => '2026-06-20',
            'is_completed' => 0,
        ]);

        $create->assertRedirect(route('admin.todos.index'));
        $todo = Todo::firstOrFail();

        $this->put(route('admin.todos.update', $todo), [
            'title' => 'Review proposals final',
            'description' => 'Done review',
            'due_date' => '2026-06-21',
            'is_completed' => 1,
        ])->assertRedirect(route('admin.todos.index'));

        $this->assertDatabaseHas('todos', [
            'id' => $todo->id,
            'title' => 'Review proposals final',
            'is_completed' => 1,
        ]);

        $this->delete(route('admin.todos.destroy', $todo))
            ->assertRedirect(route('admin.todos.index'));

        $this->assertDatabaseMissing('todos', ['id' => $todo->id]);
    }

    public function test_dashboard_and_admin_menu_pages_are_accessible(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Admin Dashboard');

        $this->get(route('admin.events.index'))->assertOk();
        $this->get(route('admin.agendas.index'))->assertOk();
        $this->get(route('admin.scores.index'))->assertOk();
        $this->get(route('admin.suggestions.index'))->assertOk();
        $this->get(route('admin.todos.index'))->assertOk();
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
