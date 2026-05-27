<?php

namespace App\Services;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\WebhookEvent;
use Illuminate\Support\Facades\Log;

/**
 * Processes a single WebhookEvent record and applies the appropriate
 * domain action based on the event's source and event_type.
 *
 * Supported event types:
 *   student.enrolled   – upsert student record and class assignment
 *   student.updated    – update student fields
 *   student.withdrawn  – soft-delete student
 *   teacher.created    – upsert teacher record
 *   teacher.updated    – update teacher fields
 *   teacher.removed    – soft-delete teacher
 *   class.created      – upsert school-class record
 *   class.updated      – update class fields
 *
 * Unknown event types are logged and silently accepted so the queue job
 * does not keep retrying for genuinely unrecognised events.
 */
class WebhookProcessor
{
    public function process(WebhookEvent $event): void
    {
        $type    = $event->event_type;
        $payload = $event->payload;

        Log::channel('webhook')->debug("Processing event", [
            'id'         => $event->id,
            'event_type' => $type,
            'source'     => $event->source,
        ]);

        match (true) {
            str_starts_with($type, 'student.') => $this->handleStudent($type, $payload),
            str_starts_with($type, 'teacher.') => $this->handleTeacher($type, $payload),
            str_starts_with($type, 'class.')   => $this->handleClass($type, $payload),
            default => Log::channel('webhook')->notice("Unhandled event type: {$type}", [
                'event_id' => $event->event_id,
            ]),
        };
    }

    // -------------------------------------------------------------------------
    // Student handlers
    // -------------------------------------------------------------------------

    private function handleStudent(string $type, array $payload): void
    {
        $data = data_get($payload, 'data', $payload);

        match ($type) {
            'student.enrolled',
            'student.updated' => $this->upsertStudent($data),
            'student.withdrawn' => $this->withdrawStudent($data),
            default => null,
        };
    }

    private function upsertStudent(array $data): void
    {
        $externalId = data_get($data, 'id') ?? data_get($data, 'external_id');
        if (! $externalId) {
            return;
        }

        $classCode = data_get($data, 'class_code');
        $classId   = $classCode
            ? SchoolClass::where('code', $classCode)->value('id')
            : null;

        Student::updateOrCreate(
            ['external_id' => (string) $externalId],
            array_filter([
                'nisn'            => data_get($data, 'nisn'),
                'nis'             => data_get($data, 'nis'),
                'name'            => data_get($data, 'name'),
                'email'           => data_get($data, 'email'),
                'phone'           => data_get($data, 'phone'),
                'birth_date'      => data_get($data, 'birth_date'),
                'gender'          => data_get($data, 'gender'),
                'school_class_id' => $classId,
                'is_active'       => true,
            ], fn ($v) => ! is_null($v))
        );
    }

    private function withdrawStudent(array $data): void
    {
        $externalId = data_get($data, 'id') ?? data_get($data, 'external_id');
        if (! $externalId) {
            return;
        }

        Student::where('external_id', (string) $externalId)
            ->update(['is_active' => false]);

        Student::where('external_id', (string) $externalId)->first()?->delete();
    }

    // -------------------------------------------------------------------------
    // Teacher handlers
    // -------------------------------------------------------------------------

    private function handleTeacher(string $type, array $payload): void
    {
        $data = data_get($payload, 'data', $payload);

        match ($type) {
            'teacher.created',
            'teacher.updated' => $this->upsertTeacher($data),
            'teacher.removed' => $this->removeTeacher($data),
            default => null,
        };
    }

    private function upsertTeacher(array $data): void
    {
        $externalId = data_get($data, 'id') ?? data_get($data, 'external_id');
        if (! $externalId) {
            return;
        }

        Teacher::updateOrCreate(
            ['external_id' => (string) $externalId],
            array_filter([
                'nip'       => data_get($data, 'nip'),
                'name'      => data_get($data, 'name'),
                'email'     => data_get($data, 'email'),
                'phone'     => data_get($data, 'phone'),
                'subject'   => data_get($data, 'subject'),
                'is_active' => true,
            ], fn ($v) => ! is_null($v))
        );
    }

    private function removeTeacher(array $data): void
    {
        $externalId = data_get($data, 'id') ?? data_get($data, 'external_id');
        if (! $externalId) {
            return;
        }

        Teacher::where('external_id', (string) $externalId)
            ->update(['is_active' => false]);

        Teacher::where('external_id', (string) $externalId)->first()?->delete();
    }

    // -------------------------------------------------------------------------
    // School class handlers
    // -------------------------------------------------------------------------

    private function handleClass(string $type, array $payload): void
    {
        $data = data_get($payload, 'data', $payload);

        match ($type) {
            'class.created',
            'class.updated' => $this->upsertClass($data),
            default => null,
        };
    }

    private function upsertClass(array $data): void
    {
        $code = data_get($data, 'code');
        if (! $code) {
            return;
        }

        SchoolClass::updateOrCreate(
            ['code' => $code],
            array_filter([
                'name'          => data_get($data, 'name'),
                'grade_level'   => data_get($data, 'grade_level'),
                'academic_year' => data_get($data, 'academic_year'),
                'is_active'     => true,
            ], fn ($v) => ! is_null($v))
        );
    }
}
