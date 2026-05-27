<?php

namespace Tests\Unit;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\WebhookEvent;
use App\Services\WebhookProcessor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebhookProcessorTest extends TestCase
{
    use RefreshDatabase;

    private WebhookProcessor $processor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->processor = new WebhookProcessor();
    }

    private function makeEvent(string $type, array $payload): WebhookEvent
    {
        return WebhookEvent::create([
            'event_id'   => uniqid('test_', true),
            'source'     => 'dapodik',
            'event_type' => $type,
            'headers'    => [],
            'payload'    => $payload,
            'status'     => WebhookEvent::STATUS_PENDING,
        ]);
    }

    // -------------------------------------------------------------------------
    // Student events
    // -------------------------------------------------------------------------

    public function test_student_enrolled_creates_student(): void
    {
        $event = $this->makeEvent('student.enrolled', [
            'event_type' => 'student.enrolled',
            'data' => [
                'id'   => 'ext-1',
                'nisn' => '9876543210',
                'name' => 'Andi Budiman',
            ],
        ]);

        $this->processor->process($event);

        $this->assertDatabaseHas('students', [
            'external_id' => 'ext-1',
            'nisn'        => '9876543210',
            'name'        => 'Andi Budiman',
        ]);
    }

    public function test_student_enrolled_twice_updates_not_duplicates(): void
    {
        $payload = ['event_type' => 'student.enrolled', 'data' => ['id' => 'ext-2', 'name' => 'Siti']];

        $this->processor->process($this->makeEvent('student.enrolled', $payload));
        $this->processor->process($this->makeEvent('student.enrolled', $payload));

        $this->assertDatabaseCount('students', 1);
    }

    public function test_student_withdrawn_soft_deletes_student(): void
    {
        Student::create(['external_id' => 'ext-3', 'name' => 'Dewi', 'is_active' => true]);

        $event = $this->makeEvent('student.withdrawn', ['data' => ['id' => 'ext-3']]);
        $this->processor->process($event);

        $this->assertSoftDeleted('students', ['external_id' => 'ext-3']);
    }

    public function test_student_enrolled_assigns_class(): void
    {
        SchoolClass::create(['code' => 'X-IPA-1', 'name' => 'Kelas X IPA 1', 'is_active' => true]);

        $event = $this->makeEvent('student.enrolled', [
            'data' => ['id' => 'ext-4', 'name' => 'Rudi', 'class_code' => 'X-IPA-1'],
        ]);

        $this->processor->process($event);

        $student = Student::where('external_id', 'ext-4')->first();
        $this->assertNotNull($student->school_class_id);
    }

    // -------------------------------------------------------------------------
    // Teacher events
    // -------------------------------------------------------------------------

    public function test_teacher_created_upserts_teacher(): void
    {
        $event = $this->makeEvent('teacher.created', [
            'data' => ['id' => 't-1', 'nip' => '198001012000031001', 'name' => 'Pak Hendra'],
        ]);

        $this->processor->process($event);

        $this->assertDatabaseHas('teachers', [
            'external_id' => 't-1',
            'name'        => 'Pak Hendra',
        ]);
    }

    public function test_teacher_removed_soft_deletes(): void
    {
        Teacher::create(['external_id' => 't-2', 'name' => 'Bu Sari', 'is_active' => true]);

        $event = $this->makeEvent('teacher.removed', ['data' => ['id' => 't-2']]);
        $this->processor->process($event);

        $this->assertSoftDeleted('teachers', ['external_id' => 't-2']);
    }

    // -------------------------------------------------------------------------
    // School class events
    // -------------------------------------------------------------------------

    public function test_class_created_upserts_school_class(): void
    {
        $event = $this->makeEvent('class.created', [
            'data' => ['code' => 'XI-IPS-2', 'name' => 'Kelas XI IPS 2', 'grade_level' => '11'],
        ]);

        $this->processor->process($event);

        $this->assertDatabaseHas('school_classes', [
            'code' => 'XI-IPS-2',
            'name' => 'Kelas XI IPS 2',
        ]);
    }

    // -------------------------------------------------------------------------
    // Unknown event type
    // -------------------------------------------------------------------------

    public function test_unknown_event_type_does_not_throw(): void
    {
        $event = $this->makeEvent('unknown.event', ['data' => []]);

        // Should complete without exception.
        $this->processor->process($event);
        $this->assertTrue(true);
    }
}
